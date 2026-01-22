<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\VisitmasterModel;
use App\Services\JwtService;

class VisitMaster extends ResourceController
{
    protected $visitModel;

    public function __construct()
    {
        $this->visitModel = new VisitmasterModel();
    }

    public function createVisit()
    {
        try {
            $tokenDecoded = $this->validateAuthorization();
            if (is_array($tokenDecoded) && isset($tokenDecoded['error'])) {
                return $this->respond($tokenDecoded, 401);
            }
            $emp_code = $tokenDecoded->emp_code;

            // Get POST data
            $input = $this->request->getPost();
            log_message('error', 'Create Visit Input: ' . json_encode($input));
            if (empty($input)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid input data'], 400);
            }

            // Extract branch ID from the string (format "PH-LOCATION - ID")
            // $branch_id  is '190,5,6' so add as it is
            $branch_id = $input['branch_id'] ?? null;

            if (is_array($branch_id)) {
                $branch_id = implode(',', $branch_id);
            } elseif (is_string($branch_id)) {
                $branch_id = trim($branch_id);
            }
            log_message('error', "Branch ID: {$branch_id}");
            if (empty($branch_id)) {
                return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
            }

            $data = [
                'visit_recurring' => $input['visit_recurring'],
                'visit_day' => $input['visit_day'],
                'branch_id' => $branch_id,
                'vendor_id' => $input['vendor_id'],
                'createdBy' => $emp_code,
                'createdDTM' => date('Y-m-d H:i:s')
            ];

            $insertId = $this->visitModel->insert($data);
            if (!$insertId) {
                return $this->respond(['status' => 'error', 'message' => 'Failed to insert data'], 500);
            }

            return $this->respond([
                'status' => 'success',
                'message' => 'Visit created successfully',
                'data' => array_merge(['id' => $insertId], $data)
            ], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateVisit($id)
    {
       

        log_message('error', 'Update Visit ID: ' . $id);

            // Validate that ID exists
            if (!$this->visitModel->find($id)) {
                return $this->respond(['status' => 'error', 'message' => 'Visit not found'], 404);
            }

            $tokenDecoded = $this->validateAuthorization();
            if (is_array($tokenDecoded) && isset($tokenDecoded['error'])) {
                return $this->respond($tokenDecoded, 401);
            }
            $emp_code = $tokenDecoded->emp_code;

            // Get POST data
            $input = $this->request->getPost();
            log_message('error', 'Update Visit Input: ' . json_encode($input));
            if (empty($input)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid input data'], 400);
            }

            // Validate required fields
            $requiredFields = ['visit_recurring', 'visit_day', 'branch_id', 'vendor_id'];
            foreach ($requiredFields as $field) {
                if (!isset($input[$field]) || empty($input[$field])) {
                    return $this->respond(['status' => 'error', 'message' => ucfirst($field) . ' is required'], 400);
                }
            }

            // Process branch_id
            $branch_id = $input['branch_id'];
            if (is_array($branch_id)) {
                $branch_id = implode(',', array_filter($branch_id));
            } elseif (is_string($branch_id)) {
                $branch_id = trim($branch_id);
            }

            if (empty($branch_id)) {
                return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
            }

            $data = [
                'visit_recurring' => $input['visit_recurring'],
                'visit_day' => $input['visit_day'],
                'branch_id' => $branch_id,
                'vendor_id' => $input['vendor_id'],
                'updatedBy' => $emp_code,
                'updatedDTM' => date('Y-m-d H:i:s')
            ];

            if (!$this->visitModel->update($id, $data)) {
                return $this->respond(['status' => 'error', 'message' => 'Failed to update visit'], 500);
            }

            return $this->respond([
                'status' => 'success', 
                'message' => 'Visit updated successfully',
                'data' => array_merge(['visit_id' => $id], $data)
            ], 200);


    }

    /// Get visit by ID or all visits
    public function getVisitById($id = null)
    {
        
        $tokenDecoded = $this->validateAuthorization();
        if ($id) {
            $builder = $this->visitModel->builder();
            $builder->select('visit_master.*, vendor.*, vendor.vendor_name, vendor.vendor_address, 
                    vendor.vendor_email, vendor.vendor_mobile, vendor.vendor_gst, 
                    vendor.branches, vendor.service_type, vendor.terms, vendor.status');
            $builder->join('vendor', 'visit_master.vendor_id = vendor.vendor_id', 'left');
            $builder->where('visit_master.visit_id', $id);
            $record = $builder->get()->getRow();

            if ($record) {
                // Get branch names from secondary database
                $branchIds = explode(',', $record->branch_id);
                $db2 = \Config\Database::connect('secondary');
                $branchNames = $db2->table('branches')
                    ->select('SysField as branch_name')
                    ->whereIn('id', $branchIds)
                    ->get()
                    ->getResultArray();

                $record->branch_name = array_column($branchNames, 'branch_name');
                return $this->respond(['status' => 'success', 'data' => $record], 200);
            } else {
                return $this->respond(['status' => 'error', 'message' => 'Record not found'], 404);
            }
        } else {
            return $this->getVisitList();
        }
    }

    public function getVisitList()
    {
        $tokenDecoded = $this->validateAuthorization();
        $builder = $this->visitModel->builder();
        $builder->select('visit_master.*, vendor.*, vendor.vendor_name, vendor.vendor_address, vendor.vendor_email, 
                         vendor.vendor_mobile, vendor.vendor_gst, vendor.branches, vendor.service_type, vendor.terms, vendor.status');

        // Join vendor table
        $builder->join('vendor', 'visit_master.vendor_id = vendor.vendor_id', 'left');

        $records = $builder->get()->getResult();

        // Process each record to get branch names
        foreach ($records as $record) {
            // Split branch_id string into array
            $branchIds = explode(',', $record->branch_id);

            // Get branch names from secondary database
            $db2 = \Config\Database::connect('secondary');
            $branchNames = $db2->table('branches')
                ->select('SysField as branch_name')
                ->whereIn('id', $branchIds)
                ->get()
                ->getResultArray();

            // Extract just the branch names into an array
            $record->branch_name = array_column($branchNames, 'branch_name');
            $record->Address = ""; // Adding empty address as per requirement
        }

        return $this->respond(['status' => 'success', 'data' => $records], 200);
    }

    // public function getVisitList()
    // {
    //     $tokenDecoded = $this->validateAuthorization();
    //     $emp_code = $tokenDecoded->emp_code;
    //     $builder = $this->visitModel->builder();        
    //     $builder->select('visit_master.*, branches.SysField as branch_name, branches.Address, vendor.*');
    //     // Join vendor table with better error handling
    //     $builder->join('vendor', 'visit_master.vendor_id = vendor.vendor_id', 'left');

    //     // Join with branches table from secondary database
    //     $db2 = \Config\Database::connect('secondary');
    //     $branchesTable = $db2->database . '.branches';
    //     $builder->join($branchesTable, 'visit_master.branch_id = branches.id', 'left');

    //     $records = $builder->get()->getResult();
    //     return $this->respond(['status' => 'success', 'data' => $records], 200);
    // }

    public function detailsOfVisitByBranchVenderServiceType()
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;
        $receivedData = $this->request->getPost() ?: $this->request->getJSON();
        $branch_id = $receivedData->branch_id ?? $receivedData['branch_id'] ?? null;
        $vendor_id = $receivedData->vendor_id ?? $receivedData['vendor_id'] ?? null;
        $service_type = $receivedData->service_type ?? $receivedData['service_type'] ?? null;

        if ($branch_id && $vendor_id && $service_type) {
            $records = $this->visitModel->where('branch_id', $branch_id)
                ->where('vendor_id', $vendor_id)
                ->where('service_type', $service_type)
                ->findAll();
            if ($records) {
                return $this->respond(['status' => 'success', 'data' => $records], 200);
            } else {
                return $this->respond(['status' => 'error', 'message' => 'No records found for this branch'], 404);
            }
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
        }
    }

    public function deleteVisit($id)
    {
        $tokenDecoded = $this->validateAuthorization();
        // Check if record exists before deleting
        $record = $this->visitModel->find($id);
        if (!$record) {
            return $this->respond(['status' => 'error', 'message' => 'Visit not found'], 404);
        }
        try {
            $this->visitModel->delete($id);
            return $this->respond(['status' => 'success', 'message' => 'Visit deleted successfully'], 200);
        } catch (\Exception $e) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to delete visit'], 500);
        }
    }



    private function validateAuthorization()
    {
        if (!class_exists('App\Services\JwtService')) {
            ////log_message( 'error', 'JwtService class not found' );
            return $this->respond(['error' => 'JwtService class not found'], 500);
        }
        // Get the Authorization header and log it
        $authorizationHeader = $this->request->header('Authorization')?->getValue();
        ////log_message( 'info', 'Authorization header: ' . $authorizationHeader );

        // Create an instance of JwtService and validate the token
        $jwtService = new JwtService();
        $result = $jwtService->validateToken($authorizationHeader);

        // Handle token validation errors
        if (isset($result['error'])) {
            ////log_message( 'error', $result[ 'error' ] );
            return $this->respond(['error' => $result['error']], $result['status']);
        }
        // Extract the decoded token and get the USER-ID
        $decodedToken = $result['data'];
        return $decodedToken;
        // Assuming JWT contains USER-ID
    }
}

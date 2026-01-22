<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\VendorModel;
use App\Models\VisitMasterModel;
use App\Models\ServicesMasterModel;
use App\Services\JwtService;

class VendorMaster extends ResourceController
{
    protected $vendorModel;

    protected $decodedToken;

    public function __construct()
    {
        $this->vendorModel = new VendorModel();
    }

    public function createVendor()
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;

        // Sanitize and validate input data
        $vendor_name =  $this->request->getPost('vendor_name');

        //check if gst number is exists in the database
        $gst_number = $this->request->getPost('vendor_gst');
        if ($gst_number) {
            $gst_number = trim($gst_number);
            $existingVendor = $this->vendorModel->where('vendor_gst', $gst_number)->first();
            if ($existingVendor) {
                return $this->respond([
                    'status' => 'error',
                    'message' =>  'The vendor with this GST is already exists.'
                ], 400);
            }
        }
        // check if email is exists in the database
        $email = $this->request->getPost('vendor_email');
        if ($email) {
            $email = trim($email);
            $existingVendor = $this->vendorModel->where('vendor_email', $email)->first();
            if ($existingVendor) {
                return $this->respond([
                    'status' => 'error',
                    'message' =>  'The vendor with this email is already exists.'
                ], 400);
            }
        }
        if (empty($vendor_name)) {
            return $this->respond([
                'status' => 'error',
                'message' => ['vendor_name' => 'The vendor_name field is required.']
            ], 400);
        }
        $branch_id = $this->request->getPost('branch_id');
        // Convert string to array if needed
        $branches = is_string($branch_id) ? explode(',', $branch_id) : $branch_id;

        $data = [
            'vendor_name' => $vendor_name,
            'vendor_address' =>  $this->request->getPost('vendor_address'),
            'vendor_email' => trim($this->request->getPost('vendor_email')),
            'vendor_mobile' => trim($this->request->getPost('vendor_mobile')),
            'vendor_gst' => trim($this->request->getPost('vendor_gst')),
            'service_type' => trim($this->request->getPost('service_type')),
            'terms' =>  $this->request->getPost('terms'),
            'branches' => is_array($branches) ? implode(',', $branches) : $branch_id,
            'createdDTM' => date('Y-m-d H:i:s'),
            'createdBy' => $emp_code
        ];

        if ($this->vendorModel->insert($data)) {
            return $this->respond(['status' => 'success', 'message' => 'Vendor created successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => $this->vendorModel->errors()], 400);
        }
    }

    public function updateVendor($id)
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;

        // Get JSON data from PUT request
        $json = $this->request->getJSON();
        if (!$json) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid JSON data'], 400);
        }

        // Check if vendor exists
        $vendor = $this->vendorModel->find($id);
        if (!$vendor) {
            return $this->respond(['status' => 'error', 'message' => 'Vendor not found'], 404);
        }

        // Check if GST number is unique when changed
        if (isset($json->vendor_gst) && $json->vendor_gst !== $vendor['vendor_gst']) {
            $existingVendor = $this->vendorModel->where('vendor_gst', $json->vendor_gst)
                ->where('vendor_id !=', $id)
                ->first();
            if ($existingVendor) {
                return $this->respond([
                    'status' => 'error',
                    'message' => 'The vendor with this GST already exists.'
                ], 400);
            }
        }

        // Handle branches array to string conversion
        $branch_data = isset($json->branch_id) ? $json->branch_id : $vendor['branches'];
        $branches = is_array($branch_data) ? implode(',', $branch_data) : $branch_data;

        $data = [
            'vendor_name' => $json->vendor_name ?? $vendor['vendor_name'],
            'vendor_address' => $json->vendor_address ?? $vendor['vendor_address'],
            'vendor_email' => $json->vendor_email ?? $vendor['vendor_email'],
            'vendor_mobile' => $json->vendor_mobile ?? $vendor['vendor_mobile'],
            'vendor_gst' => $json->vendor_gst ?? $vendor['vendor_gst'],
            'service_type' => $json->service_type ?? $vendor['service_type'],
            'terms' => $json->terms ?? $vendor['terms'],
            'status' => $json->vendor_status ?? $vendor['status'],
            'branches' => $branches,
            'updatedDTM' => date('Y-m-d H:i:s'),
            'updatedBy' => $emp_code
        ];

        if ($this->vendorModel->update($id, $data)) {
            return $this->respond(['status' => 'success', 'message' => 'Vendor updated successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => $this->vendorModel->errors()], 400);
        }
    }

    public function getVendorList()
    {
        $this->validateAuthorization();

        $builder = $this->vendorModel->builder();
        $builder->select('vendor.*');
        $vendors = $builder->get()->getResult();
        if (empty($vendors)) {
            return $this->respond(['status' => 'error', 'message' => 'No vendors found'], 404);
        }
        foreach ($vendors as &$vendor) {
            $vendor->branches = explode(',', $vendor->branches);
            // Get branch names from secondary database
            $db2 = \Config\Database::connect('secondary');
            $branchQuery = $db2->table('branches')
                ->select('SysNo, SysField,id') // Select relevant fields from branches table
                ->whereIn('id', $vendor->branches)
                ->where('status', 'A') // Only get active branches
                ->get();

            $branchDetails = [];
            foreach ($branchQuery->getResult() as $branch) {
                $branchDetails[] = [
                    'SysNo' => $branch->SysNo,
                    'SysField' => $branch->SysField,
                    'id' => $branch->id,
                ];
            }
            $vendor->branch_details = $branchDetails;
        }

        // Return the response with vendor data
        log_message('error', 'Vendors: ' . json_encode($vendors));

        return $this->respond(['status' => 'success', 'data' => $vendors], 200);
    }

    public function getVendorById($id = null)
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;

        if ($id) {
            $vendor = $this->vendorModel->find($id);
            if ($vendor) {
                return $this->respond(['status' => 'success', 'data' => $vendor], 200);
            } else {
                return $this->respond(['status' => 'error', 'message' => 'Vendor not found'], 404);
            }
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Vendor ID is required'], 400);
        }
    }

    public function getBranchListMappedWithVendor($vendor_id = null)
    {
        $this->validateAuthorization();

        if ($vendor_id) {
            $vendor = $this->vendorModel->find($vendor_id);
            if ($vendor) {
                $branches = explode(',', $vendor['branches']);
                // Get branch names from secondary database
                $db2 = \Config\Database::connect('secondary');
                $branchQuery = $db2->table('branches')
                    ->select('SysNo, SysField,id') // Select relevant fields from branches table
                    ->whereIn('id', $branches)
                    ->where('status', 'A') // Only get active branches
                    ->get();

                $branchDetails = [];
                foreach ($branchQuery->getResult() as $branch) {
                    $branchDetails[] = [
                        'SysNo' => $branch->SysNo,
                        'SysField' => $branch->SysField,
                        'id' => $branch->id,
                    ];
                }
                return $this->respond(['status' => 'success', 'data' => $branchDetails], 200);
            } else {
                return $this->respond(['status' => 'error', 'message' => 'Vendor not found'], 404);
            }
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Vendor ID is required'], 400);
        }
    }

    public function getVendorByBranchId($branch_id = null)
    {
        log_message('error', json_encode(db_connect()));

        $this->validateAuthorization();

        log_message('error', "Get Vendor by Branch ID: {$branch_id}");

        if (!$branch_id) {
            return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
        }

        try {
            $vendorModel = new VendorModel();
            $vendors = $vendorModel->where("FIND_IN_SET({$branch_id}, branches) > 0")->findAll();
            log_message('error', "Vendors: " . json_encode($vendors));

            if (empty($vendors)) {
                return $this->respond(['status' => 'error', 'message' => 'No vendors found for this branch'], 404);
            }

            $vendorId = $vendors[0]['vendor_id'] ?? null;
            log_message('error', "Vendor ID: {$vendorId}");

            if ($vendorId) {
                $visitModel = new VisitMasterModel();
                $visits = $visitModel->where('vendor_id', $vendorId)
                    ->where('branch_id', $branch_id)
                    ->findAll();
                log_message('error', "Visit Details: " . json_encode($visits));

                if (!empty($visits)) {
                    foreach ($vendors as &$vendor) {
                        $vendor['visit_details'] = $visits;
                    }
                }
            }

            return $this->respond(['status' => 'success', 'data' => $vendors], 200);
        } catch (\Exception $e) {
            log_message('error', 'Exception: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }

    public function getVendorForPestControlByBranchId($branch_id = null)
    {
        $this->validateAuthorization();
        if (!$branch_id) {
            return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
        }
        try {
            $vendorModel = new VendorModel();
            $vendors = $vendorModel
                ->where("FIND_IN_SET({$branch_id}, branches) > 0")
                ->where('service_type', 'Pest_Control_Service')
                ->findAll();
            if (empty($vendors)) {
                return $this->respond(['status' => 'error', 'message' => 'No vendors found for this branch'], 404);
            }
            // Get visit details for each vendor
            foreach ($vendors as &$vendor) {
                $visitModel = new VisitMasterModel();
                $visits = $visitModel
                    ->where('vendor_id', $vendor['vendor_id'])
                    ->where('branch_id', $branch_id)
                    ->findAll();

                $vendor['visit_details'] = $visits ?: [];

                // Get service details for last service single entry
                $serviceModel = new ServicesMasterModel();
                $serviceDetails = $serviceModel
                    ->where('vendor_id', $vendor['vendor_id'])
                    ->where('branch_id', $branch_id)
                    ->orderBy('service_date', 'DESC') // Order by service_id descending
                    ->findAll(1); // Limit to 1 result

                $vendor['service_details'] = $serviceDetails ?: [];
            }
            return $this->respond(['status' => 'success', 'data' => $vendors], 200);
        } catch (\Exception $e) {
            log_message('error', 'Exception: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }

    public function getVendorForElevationCleaningByBranchId($branch_id = null)
    {
        $this->validateAuthorization();

        if (!$branch_id) {
            return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
        }

        try {
            $vendorModel = new VendorModel();
            $vendors = $vendorModel
                ->where("FIND_IN_SET({$branch_id}, branches) > 0")
                ->where('service_type', 'Elevation_Cleaning_Service')
                ->findAll();

            if (empty($vendors)) {
                return $this->respond(['status' => 'error', 'message' => 'No vendors found for this branch'], 404);
            }

            // Get visit details for each vendor
            foreach ($vendors as &$vendor) {
                $visitModel = new VisitMasterModel();
                $visits = $visitModel
                    ->where('vendor_id', $vendor['vendor_id'])
                    ->where('branch_id', $branch_id)
                    ->findAll();

                $vendor['visit_details'] = $visits ?: [];

                // Get service details for last service single entry
                $serviceModel = new ServicesMasterModel();
                $serviceDetails = $serviceModel
                    ->where('vendor_id', $vendor['vendor_id'])
                    ->where('branch_id', $branch_id)
                    ->orderBy('service_date', 'DESC') // Order by service_id descending
                    ->findAll(1); // Limit to 1 result

                $vendor['service_details'] = $serviceDetails ?: [];
            }

            return $this->respond(['status' => 'success', 'data' => $vendors], 200);
        } catch (\Exception $e) {
            log_message('error', 'Exception: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }

    public function getVendorForWaterTankCleaningByBranchId($branch_id = null)
    {
        $this->validateAuthorization();

        if (!$branch_id) {
            return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
        }

        try {
            $vendorModel = new VendorModel();
            $vendors = $vendorModel
                ->where("FIND_IN_SET({$branch_id}, branches) > 0")
                ->where('service_type', 'Water_Tank_Cleaning_Service')
                ->findAll();

            if (empty($vendors)) {
                return $this->respond(['status' => 'error', 'message' => 'No vendors found for this branch'], 404);
            }

            // Get visit details for each vendor
            foreach ($vendors as &$vendor) {
                $visitModel = new VisitMasterModel();
                $visits = $visitModel
                    ->where('vendor_id', $vendor['vendor_id'])
                    ->where('branch_id', $branch_id)
                    ->findAll();

                $vendor['visit_details'] = $visits ?: [];

                // Get service details for last service single entry
                $serviceModel = new ServicesMasterModel();
                $serviceDetails = $serviceModel
                    ->where('vendor_id', $vendor['vendor_id'])
                    ->where('branch_id', $branch_id)
                    ->orderBy('service_date', 'DESC') // Order by service_id descending
                    ->findAll(1); // Limit to 1 result

                $vendor['service_details'] = $serviceDetails ?: [];
            }

            return $this->respond(['status' => 'success', 'data' => $vendors], 200);
        } catch (\Exception $e) {
            log_message('error', 'Exception: ' . $e->getMessage());
            return $this->respond(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }
    // public function getVendorByBranchId($branch_id = null)
    // {
    //     $this->validateAuthorization();


    //     $vendorModel = new VendorModel();

    //     log_message('error', "Get Vendor by Branch ID: {$branch_id}");

    //     if ($branch_id) {
    //         $vendors = $vendorModel->where("FIND_IN_SET({$branch_id}, branches) > 0")->findAll();
    //         log_message('error', "Branch ID: {$branch_id}");
    //         log_message('error', "Vendors: " . json_encode($vendors));
    //         if (!empty($vendors)) {
    //             $vendorId = $vendors[0]['vendor_id'] ?? null;
    //             log_message('error', "Vendor ID: {$vendorId}");
    //             if ($vendorId) {
    //                 $visitModel = new VisitMasterModel();
    //                 $visits = $visitModel->where('vendor_id', $vendorId)
    //                     ->where('branch_id', $branch_id)
    //                     ->findAll();

    //                 // Only add visit_details if there are any visits
    //                 if (!empty($visits)) {
    //                     foreach (array_keys($vendors) as $key) {
    //                         $vendors[$key]['visit_details'] = $visits;
    //                     }
    //                 }
    //                 log_message('error', "Visit Details: " . json_encode($visits));
    //             }
    //             return $this->respond(['status' => 'success', 'data' => $vendors], 200);
    //         } else {
    //             return $this->respond(['status' => 'error', 'message' => 'No vendors found for this branch'], 404);
    //         }
    //     } else {
    //         return $this->respond(['status' => 'error', 'message' => 'Branch ID is required'], 400);
    //     }
    // }

    public function deleteVendor($id)
    {
        $tokenDecoded = $this->validateAuthorization();
        $emp_code = $tokenDecoded->emp_code;
        log_message('error', 'Delete Vendor ID: ' . $id);

        if ($this->vendorModel->delete($id)) {
            return $this->respond(['status' => 'success', 'message' => 'Vendor deleted successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Vendor not found'], 404);
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

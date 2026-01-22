<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\PowerMeterModel;
use App\Models\PowerConsumptionModel;
use App\Models\PCModel;

use App\Services\JwtService;

class PowerMeterMaster extends BaseController
{
    use ResponseTrait;

    protected $powerMeterModel;
    protected $jwtService;

    public function __construct()
    {
        $this->powerMeterModel = new PowerMeterModel();
        $this->jwtService = new JwtService();
    }
 

    public function createPowerMeter(){
        $decodedToken = $this->validateAuthorization();
        if (is_array($decodedToken) && isset($decodedToken['error'])) {
            return $this->respond(['error' => $decodedToken['error']], $decodedToken['status']);
        }

        // Try to get JSON input first
        try {
            $json = $this->request->getJSON();
            if ($json) {
                $branchId = $json->branch_id ?? null;            
                $meterNumber = $json->meter_number ?? null;
                $load_kw = $json->load_kw ?? null;
            } else {
                // Fall back to POST/GET method
                $branchId = $this->request->getVar('branch_id');            
                $meterNumber = $this->request->getVar('meter_number');
                $load_kw = $this->request->getVar('load_kw') ?? null;
            }
        } catch (\Exception) {
            // Fall back to POST/GET method if JSON parsing fails
            $branchId = $this->request->getVar('branch_id');            
            $meterNumber = $this->request->getVar('meter_number');
            $load_kw = $this->request->getVar('load_kw') ?? null;
        }

        // Validate input data
        if (empty($branchId)   || empty($meterNumber)) {
            return $this->failValidationErrors('Branch ID, Meter Name, and Meter Number are required.');
        }

        //check if the meter number already exists for the given branch
        $existingMeter = $this->powerMeterModel->getDetailsByBranchIdAndMeterNumber($branchId, $meterNumber);
        if (!empty($existingMeter)) {
            return $this->failValidationErrors('Meter number already exists for this branch.');
        }

        // Get the next serial number for the meter
        $nextSerialNumber = $this->powerMeterModel->getNextSerialNumber($branchId);

        // Prepare data for insertion
        $data = [
            'branch_id' => $branchId,
            'meter_name' => $nextSerialNumber,
            'meter_number' => $meterNumber, 
            'load_kw' => $load_kw,
            
        ];

        // Insert data into the database
        if ($this->powerMeterModel->insert($data)) {
            return $this->respond(['status' => 'success', 'message' => 'Power meter created successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Failed to create power meter'], 500);
        }
    }

    public function getPowerMeterList(){
        $decodedToken = $this->validateAuthorization();
        if (is_array($decodedToken) && isset($decodedToken['error'])) {
            return $this->respond(['error' => $decodedToken['error']], $decodedToken['status']);
        }

        // Get the list of power meters
        $powerMeters = $this->powerMeterModel->findAll();

        // Check if any power meters were found
        if (empty($powerMeters)) {
            return $this->respond(['status' => 'error', 'message' => 'No power meters found'], 404);
        }

        return $this->respond(['status' => 'success', 'data' => $powerMeters], 200);
    }

    public function getPowerMeterById($id){
        $decodedToken = $this->validateAuthorization();
        if (is_array($decodedToken) && isset($decodedToken['error'])) {
            return $this->respond(['error' => $decodedToken['error']], $decodedToken['status']);
        }

        // Get the power meter by ID
        $powerMeter = $this->powerMeterModel->find($id);

        // Check if the power meter was found
        if (empty($powerMeter)) {
            return $this->respond(['status' => 'error', 'message' => 'Power meter not found'], 404);
        }

        return $this->respond(['status' => 'success', 'data' => $powerMeter], 200);
    }   

    public function updatePowerMeter($id){
        $decodedToken = $this->validateAuthorization();
        if (is_array($decodedToken) && isset($decodedToken['error'])) {
            return $this->respond(['error' => $decodedToken['error']], $decodedToken['status']);
        }

        // Try to get JSON input first
        try {
            $json = $this->request->getJSON();
            if ($json) {
                $branchId = $json->branch_id ?? null;
                $meterName = $json->meter_name ?? null;
                $meterNumber = $json->meter_number ?? null;
                $status = $json->status ?? 'A';
                $load_kw = $json->load_kw ?? null;
            } else {
                // Fall back to POST/GET method
                $branchId = $this->request->getVar('branch_id');
                $meterName = $this->request->getVar('meter_name');
                $meterNumber = $this->request->getVar('meter_number');
                $status = $this->request->getVar('status') ?? 'A';
                $load_kw = $this->request->getVar('load_kw') ?? null;
            }
        } catch (\Exception) {
            // Fall back to POST/GET method if JSON parsing fails
            $branchId = $this->request->getVar('branch_id');
            $meterName = $this->request->getVar('meter_name');
            $meterNumber = $this->request->getVar('meter_number');
            $status = $this->request->getVar('status') ?? 'A';
            $load_kw = $this->request->getVar('load_kw') ?? null;
        }

        // Validate input data
        if (empty($branchId) || empty($meterName) || empty($meterNumber)) {
            return $this->failValidationErrors('Branch ID, Meter Name, and Meter Number are required.');
        }

        // Prepare data for update
        $data = [
            'branch_id' => $branchId,
            'meter_name' => $meterName,
            'meter_number' => $meterNumber,
            'status' => $status,
            'load_kw' => $load_kw,
            
        ]; 

        // Update the power meter in the database
        if ($this->powerMeterModel->update($id, $data)) {
            return $this->respond(['status' => 'success', 'message' => 'Power meter updated successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Failed to update power meter'], 500);
        }
    }

    public function deletePowerMeter(){
        $decodedToken = $this->validateAuthorization();
        if (is_array($decodedToken) && isset($decodedToken['error'])) {
            return $this->respond(['error' => $decodedToken['error']], $decodedToken['status']);
        }

        // Try to get JSON input first
        try {
            $json = $this->request->getJSON();
            if ($json) {
                $id = $json->pm_id ?? null;
            } else {
                // Fall back to POST/GET method
                $id = $this->request->getVar('id');
            }
        } catch (\Exception) {
            // Fall back to POST/GET method if JSON parsing fails
            $id = $this->request->getVar('id');
        }

        if (empty($id)) {
            return $this->failValidationErrors('ID is required.');
        }

        // Update status to 'I' (inactive) instead of deleting
        $data = [
            'status' => 'I',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($this->powerMeterModel->update($id, $data)) {
            return $this->respond(['status' => 'success', 'message' => 'Power meter deactivated successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Failed to deactivate power meter'], 500);
        }
    }
 

    public function getPowerMeterByBranchId($branchId){
        $decodedToken = $this->validateAuthorization();
        if (is_array($decodedToken) && isset($decodedToken['error'])) {
            return $this->respond(['error' => $decodedToken['error']], $decodedToken['status']);
        }

        // Get the power meter by branch ID
        $powerMeter = $this->powerMeterModel->where('branch_id', $branchId)->findAll();

        // Check if the power meter was found
        if (empty($powerMeter)) {
            return $this->respond(['status' => 'error', 'message' => 'Power meter not found'], 404);
        }

        return $this->respond(['status' => 'success', 'data' => $powerMeter], 200);
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
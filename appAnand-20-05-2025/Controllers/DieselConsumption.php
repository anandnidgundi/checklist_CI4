<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\DieselConsumptionModel;
use App\Models\UserModel;
use App\Models\FileModel;
use App\Services\JwtService;

class DieselConsumption extends BaseController
{
    use ResponseTrait;

    public function __construct() {}

    public function getDieselConsumptionList($month = null)
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;

        log_message('error', 'DieselConsumption::getDieselConsumptionList called with month: {month}', ['month' => $month]);
        //fetching diesel consumption list
        $dieselConsumptionModel = new DieselConsumptionModel();
       
        $dieselConsumptionList = $dieselConsumptionModel->getDieselConsumptionList($role, $emp_code, $month);
        if ($dieselConsumptionList) {
            return $this->respond(['status' => 'success', 'data' => $dieselConsumptionList], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No data found...'], 404);
        }
    }

    public function getDieselConsumptionAdminList()
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;
        //fetching power consumption list
        $dieselConsumptionModel = new DieselConsumptionModel();
        // Get JSON input with error handling
        $jsonData = [];
        try {
            $jsonInput = $this->request->getJSON(true);
            if ($jsonInput !== null) {
                $jsonData = $jsonInput;
            }
        } catch (\Exception $e) {
            log_message('error', 'JSON parsing error: ' . $e->getMessage());
        }

        // Get parameters from POST, JSON or GET, in that order
        $zone_id = $this->request->getPost('zone_id') ?? ($jsonData['zone_id'] ?? $this->request->getVar('zone_id'));
        $selectedCluster = $this->request->getPost('selectedCluster') ?? ($jsonData['selectedCluster'] ?? $this->request->getVar('selectedCluster'));
        $selectedBranch = $this->request->getPost('selectedBranch') ?? ($jsonData['selectedBranch'] ?? $this->request->getVar('selectedBranch'));
        $selectedMonth = $this->request->getPost('selectedMonth') ?? ($jsonData['selectedMonth'] ?? $this->request->getVar('selectedMonth'));
        $selectedDate = $this->request->getPost('selectedDate') ?? ($jsonData['selectedDate'] ?? $this->request->getVar('selectedDate'));
        $ConsumptionList = $dieselConsumptionModel->getDieselConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate);

        if ($ConsumptionList) {
            return $this->respond(['status' => 'success', 'data' => $ConsumptionList], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
        }
    }

    public function getDieselConsumptionAdminListforbranch()
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;
        //fetching power consumption list
        $dieselConsumptionModel = new DieselConsumptionModel();
        // Get JSON input with error handling
        $jsonData = [];
        try {
            $jsonInput = $this->request->getJSON(true);
            if ($jsonInput !== null) {
                $jsonData = $jsonInput;
            }
        } catch (\Exception $e) {
            log_message('error', 'JSON parsing error: ' . $e->getMessage());
        }

        // Get parameters from POST, JSON or GET, in that order
        $zone_id = $this->request->getPost('zone_id') ?? ($jsonData['zone_id'] ?? $this->request->getVar('zone_id'));
        $selectedCluster = $this->request->getPost('selectedCluster') ?? ($jsonData['selectedCluster'] ?? $this->request->getVar('selectedCluster'));
        $selectedBranch = $jsonData['branch_id'] ?? '';
        $selectedMonth = $this->request->getPost('selectedMonth') ?? ($jsonData['selectedMonth'] ?? $this->request->getVar('selectedMonth'));
        $selectedDate = $this->request->getPost('selectedDate') ?? ($jsonData['selectedDate'] ?? $this->request->getVar('selectedDate'));
       
        $ConsumptionList = $dieselConsumptionModel->getDieselConsumptionAdminListforbranch($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate);

        if ($ConsumptionList) {
            return $this->respond(['status' => 'success', 'data' => $ConsumptionList], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
        }
    }

    //getDieselConsumptionById
    public function getDieselConsumptionById($id = null)
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;

        //fetching diesel consumption list
        $dieselConsumptionModel = new DieselConsumptionModel();
        $dieselConsumptionList = $dieselConsumptionModel->getDieselConsumptionById($id);
        if ($dieselConsumptionList) {
            return $this->respond(['status' => 'success', 'data' => $dieselConsumptionList], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
        }
    }

    //addDieselConsumption
    public function addDieselConsumption()
    {
        $userDetails = $this->validateAuthorization();
        $emp_code = $userDetails->emp_code;

        // Get form data
        // Try to get data from POST or JSON payload
        $branch_id = $this->request->getPost('branch_id') ?? $this->request->getVar('branch_id');
        $consumption_date = $this->request->getPost('consumption_date') ?? $this->request->getVar('consumption_date');
        $power_shutdown = $this->request->getPost('power_shutdown') ?? $this->request->getVar('power_shutdown');
        $is_power_shutdown = $this->request->getPost('is_power_shutdown') ?? $this->request->getVar('is_power_shutdown');
        $is_generator_testing = $this->request->getPost('is_generator_testing') ?? $this->request->getVar('is_generator_testing');
        $testing_time_duration = $this->request->getPost('testing_time_duration') ?? $this->request->getVar('testing_time_duration');
        $testing_diesel_consumed = $this->request->getPost('testing_diesel_consumed') ?? $this->request->getVar('testing_diesel_consumed');
        $diesel_consumed = $this->request->getPost('diesel_consumed') ?? $this->request->getVar('diesel_consumed');
        $avg_consumption = $this->request->getPost('avg_consumption') ?? $this->request->getVar('avg_consumption');
        $closing_stock = $this->request->getPost('closing_stock') ?? $this->request->getVar('closing_stock');
        $remarks = $this->request->getPost('remarks') ?? $this->request->getVar('remarks');
        $file = $this->request->getFile('file') ?? $this->request->getVar('file');
        $userModel = new UserModel();
        $result = $userModel->getclusterId($branch_id);
        // echo "<pre>";
        // print_r($result['cluster_id']);die();
        $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
    
        if (!$branchDetails) {
            return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
        }
        $cluster_id = $result['cluster_id'];
       
        $zone_id = $branchDetails['zone'];
        // Log the incoming request data for debugging
        $requestBody = $this->request->getBody();
        if ($requestBody) {
            log_message('error', 'Request data: ' . $requestBody);
        }

        log_message('error', 'DieselConsumption::addDieselConsumption called with branch_id: {branch_id}, consumption_date: {consumption_date}', ['branch_id' => $branch_id, 'consumption_date' => $consumption_date]);
        // Validate required fields
        if (empty($branch_id) || empty($consumption_date)) {
            return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
        }

        // Prepare data for insertion
        $data = [
            'branch_id' => $branch_id,
            'cluster_id' => $cluster_id,
            'zone_id' => $zone_id,
            'consumption_date' => $consumption_date,
            'power_shutdown' => $power_shutdown ?? '',
            'is_power_shutdown' => $is_power_shutdown ?? '',
            'is_generator_testing' => $is_generator_testing ?? '',
            'testing_time_duration' => $testing_time_duration ?? '',
            'testing_diesel_consumed' => $testing_diesel_consumed ?? '',
            'diesel_consumed' => $diesel_consumed ?? '',
            'avg_consumption' => $avg_consumption ?? '',
            'closing_stock' => $closing_stock ?? '',
            'remarks' => $remarks,
            'createdBy' => $emp_code,
            'createdDTM' => date('Y-m-d H:i:s')
        ];

        // Check for existing entry
        $dieselConsumptionModel = new DieselConsumptionModel();
        $existingEntry = $dieselConsumptionModel->where('consumption_date', $consumption_date)
            ->where('branch_id', $branch_id)
            ->first();

        if ($existingEntry) {
            return $this->respond(['status' => 'success', 'message' => 'Entry already exists for this date and branch'], 200);
        }
        $db = \Config\Database::connect();
        $db->table('diesel_consumption_logs')->insert($data);
        $insertId = $dieselConsumptionModel->insert($data);
        if (!$insertId) {
            return $this->respond(['status' => 'error', 'message' => 'Failed to add diesel consumption'], 500);
        }

        // Handle file upload
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            // Validate file type and size
            $allowedTypes = ['jpg', 'png', 'pdf', 'docx'];
            if (!in_array($file->getExtension(), $allowedTypes)) {
                return $this->respond(['status' => 'error', 'message' => 'Invalid file type'], 400);
            }

            if ($file->getSize() > 2097152) { // 2MB limit
                return $this->respond(['status' => 'error', 'message' => 'File size exceeds 2MB limit'], 400);
            }

            $uploadPath = WRITEPATH . 'uploads/secure_files';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $fileName = $file->getClientName();
            $file->move($uploadPath, $fileName);

            // Save file details
            // Verify $insertId before using it
            if (!$insertId) {
                return $this->respond(['status' => 'error', 'message' => 'Failed to get diesel consumption ID'], 500);
            }

            $fileData = [
                'file_name' => $fileName,
                'diesel_id' => $insertId, // Cast to integer to ensure proper type
                'emp_code' => $emp_code,
                'createdDTM' => date('Y-m-d H:i:s'),
            ];
            $fileModel = new FileModel();
            $fileModel->insert($fileData);
        }

        return $this->respond([
            'status' => 'success',
            'message' => 'Diesel consumption added successfully',
            'id' => $insertId
        ], 201);
    }

    //editDieselConsumption
    public function editDieselConsumption($id = null)
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;

        // Get the request body and decode it
        $requestBody = $this->request->getRawInput();

        // log_message('error', 'DieselConsumption::editDieselConsumption called with id: {id}', ['id' => $id]);


        // Validate required fields
        $branch_id = $this->request->getVar('branch_id');
        $consumption_date = $this->request->getVar('consumption_date');
        $is_power_shutdown = $this->request->getVar('is_power_shutdown');
        $is_generator_testing = $this->request->getVar('is_generator_testing');
        $power_shutdown = $this->request->getVar('power_shutdown') ?? null;
        $diesel_consumed = $this->request->getVar('diesel_consumed') ?? null;
        $testing_diesel_consumed = $this->request->getVar('testing_diesel_consumed') ?? null;
        $testing_time_duration = $this->request->getVar('testing_time_duration') ?? null;

        // if ($is_power_shutdown === 'No') {
        //     $power_shutdown = '00:00'; // Default time
        //     $diesel_consumed = 0;
        //     $testing_diesel_consumed = 0;
        //     $testing_time_duration = '00:00';
        // }

        // if (empty($branch_id) || empty($consumption_date)) {
        //     return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
        // }

        // Prepare data for update
        $data = [
            'branch_id' => $branch_id,
            'consumption_date' => $consumption_date,
            'is_power_shutdown' => $is_power_shutdown,
            'is_generator_testing' => $is_generator_testing,
            'power_shutdown' => $power_shutdown,
            'diesel_consumed' => $diesel_consumed,
            'testing_time_duration' => $testing_time_duration,
            'testing_diesel_consumed' => $testing_diesel_consumed,
            'avg_consumption' => $this->request->getVar('avg_consumption') ?? null,
            'closing_stock' => $this->request->getVar('closing_stock') ?? null,
            'createdBy' => $emp_code,
            'createdDTM' => date('Y-m-d H:i:s')
        ];

        // Update data in the database
        $dieselConsumptionModel = new DieselConsumptionModel();
        
        if ($dieselConsumptionModel->update($id, $data)) {
            $db = \Config\Database::connect();
            $db->table('diesel_consumption_logs')->insert($data);  
            $file = $this->request->getFile('file');
            if ($file && $file->isValid() && !$file->hasMoved()) {
                // Validate file type and size
                $allowedTypes = ['jpg', 'png', 'pdf', 'docx'];
                if (!in_array($file->getExtension(), $allowedTypes)) {
                    return $this->respond(['status' => 'error', 'message' => 'Invalid file type'], 400);
                }

                if ($file->getSize() > 2097152) { // 2MB limit
                    return $this->respond(['status' => 'error', 'message' => 'File size exceeds 2MB limit'], 400);
                }

                $uploadPath = WRITEPATH . 'uploads/secure_files';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                $fileName = $file->getClientName();
                $file->move($uploadPath, $fileName);

                // Save file details
                // Verify $insertId before using it
                if (!$id) {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to get diesel consumption ID'], 500);
                }

                $fileData = [
                    'file_name' => $fileName,
                    'diesel_id' => (int)$id, // Cast to integer to ensure proper type
                    'emp_code' => $emp_code,
                    'createdDTM' => date('Y-m-d H:i:s'),
                ];
                $fileModel = new FileModel();
                $fileModel->insert($fileData);
            }

            return $this->respond(['status' => 'success', 'message' => 'Diesel consumption updated successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Failed to update diesel consumption'], 500);
        }
    }

    //deleteDieselConsumption
    public function deleteDieselConsumption($id = null)
    {
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;

        // Delete data from the database
        $dieselConsumptionModel = new DieselConsumptionModel();
        if ($dieselConsumptionModel->delete($id)) {
            return $this->respond(['status' => 'success', 'message' => 'Diesel consumption deleted successfully'], 200);
        } else {
            return $this->respond(['status' => 'error', 'message' => 'Failed to delete diesel consumption'], 500);
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

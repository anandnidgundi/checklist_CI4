<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\DeptModel;
use App\Models\CM_NightModel;
use App\Services\JwtService;

class Cm_nighttask extends BaseController {
    use ResponseTrait;

    public function index(): string {
        return view( 'welcome_message' );
    }

    public function getCM_BranchNightTaskList(){
        $mtModel = new CM_NightModel(); 

        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Retrieve POST data (json)
          $requestData = $this->request->getJSON();

          if (isset($requestData->selectedMonth)) {
            $selectedMonth = $requestData->selectedMonth;            
        } else {
            $selectedMonth = date('Y-m');
        }

         if (isset($requestData->selectedBranch) && $requestData->selectedBranch > 0) {
            $selectedBranch = $requestData->selectedBranch;            
        } else {
            $selectedBranch = 'All';
        }

          // Pass the parameters to the model
          $mtDetails = $mtModel->getCM_BranchNightTaskList($user, $role, $selectedMonth, $selectedBranch); 

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
          }
    }

    public function  getCm_Z_NightTaskList(){
        $mtModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getCm_Z_NightTaskList($role, $user); 

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
          }
    }

    public function  getBm_Z_NightTaskList(){
        $mtModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getBm_Z_NightTaskList($role, $user); 

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
          }
    }

    public function getBmcNightTaskList(){
        $mtModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getBmcNightTaskList($role, $user);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
          }

    }

    public function getCmNightTaskList(){
        $mtModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 
          // Pass the parameters to the model
          $mtDetails = $mtModel->getCmNightTaskList($role, $user);
          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
          }
    }

    public function addCmNightTask() {

        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;
        // Get the JSON data from the POST request
        $jsonData = $this->request->getJSON();

        // Validate if the JSON data is present
        if (!$jsonData) {
            return $this->respond(['status' => false, 'message' => 'Invalid JSON data'], 400);
        }

        // Extract data from the received JSON
        $cluster_id = $jsonData->cluster_id;
        $createdDTM = $jsonData->createdDTM;  // This should be sent from the frontend

        // Assuming the data has the Night task values (e.g., mt0100, mt0101, etc.)
        $data = [

            'cm_nt0100' => $jsonData->cm_nt0100 ?? null,
            'cm_nt0101' => $jsonData->cm_nt0101 ?? null,
            'cm_nt0200' => $jsonData->cm_nt0200 ?? null,
            'cm_nt0201' => $jsonData->cm_nt0201 ?? null,
            'cm_nt0300' => $jsonData->cm_nt0300 ?? null,
            'cm_nt0301' => $jsonData->cm_nt0301 ?? null,
            'cm_nt0400' => $jsonData->cm_nt0400 ?? null,
            'cm_nt0401' => $jsonData->cm_nt0401 ?? null,
            'cm_nt0500' => $jsonData->cm_nt0500 ?? null,
            'cm_nt0501' => $jsonData->cm_nt0501 ?? null,
            'cm_nt0600' => $jsonData->cm_nt0600 ?? null,
            'cm_nt0601' => $jsonData->cm_nt0601 ?? null,
            'cm_nt0700' => $jsonData->cm_nt0700 ?? null,
            'cm_nt0701' => $jsonData->cm_nt0701 ?? null,
            'cm_nt0800' => $jsonData->cm_nt0800 ?? null,
            'cm_nt0801' => $jsonData->cm_nt0801 ?? null,
            'cm_nt0900' => $jsonData->cm_nt0900 ?? null,
            'cm_nt0901' => $jsonData->cm_nt0901 ?? null,
            'cm_nt1000' => $jsonData->cm_nt1000 ?? null,
            'cm_nt1001' => $jsonData->cm_nt1001 ?? null,

            'cluster_id' => $cluster_id,
            'emp_code' => $emp_code,
            'createdDTM' => $createdDTM,
            'created_by' => $emp_code,
        ];
        // Filter out null values to avoid an empty `$data` array
        $filteredData = array_filter($data, fn($value) => !is_null($value));

        // Check if the branch is valid
        if ($cluster_id > 0) {
            // Initialize the model
            $nightModel = new CM_NightModel();

            // Add the Night task using the model
            $mt = $nightModel->addCmNightTask($filteredData, $createdDTM, $emp_code);

            if ($mt) {
                return $this->respond(['status' => true, 'message' => 'Night Task added successfully.', 'data' => $mt], 200);
            } else {
                return $this->respond(['status' => false, 'message' => 'Failed to add Night Task'.json_encode($filteredData)], 500);
            }
        }

        // If branch is not valid, return an error
        return $this->respond(['status' => false, 'message' => 'Invalid branch ID'], 400);
    }


    public function getCm_nightTaskDetails(){
        $nightModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if selectedBranch and selectedDate are set in the request data
        if (isset($requestData->selectedCluster) && isset($requestData->selectedDate)) {
            $selectedCluster = $requestData->selectedCluster;
            $selectedDate = $requestData->selectedDate;
        } else {
            // Handle the case where the expected data is missing
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameters: selectedCluster or selectedDate.'
            ], 400);
        }

        // Pass the parameters to the model
        $mtDetails = $nightModel->getCm_nightTaskDetails($selectedCluster, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
        }

    }

    public function getCmNightTaskDetailsNew(){
        $nightModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;
        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();
        // Check if selectedBranch and selectedDate are set in the request data
        if (isset($requestData->cm_nid)) {
            $cm_nid = $requestData->cm_nid;           
        } else {
            // Handle the case where the expected data is missing
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameters: selectedCluster or selectedDate.'
            ], 400);
        }
        // Pass the parameters to the model
        $mtDetails = $nightModel->getCmNightTaskDetailsNew($cm_nid, $role, $user);
        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
        }
    }


    public function saveCm_nightTaskDetails()
    {
        $nightModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();

        // Retrieve JSON data from the POST request
        $jsonData = $this->request->getJSON();

        // Log received JSON data for debugging
        //log_message('error', 'Received JSON Data: ' . json_encode($jsonData));

        // Extract mid and data fields
        $nid = $jsonData->cm_nid ?? null;
        //log_message('error', 'Received JSON Databy suhas : ' . json_encode($jsonData));

        if (!$nid) {
            //log_message('error', 'Missing nid in request data.');
            return $this->respond([
                'status' => false,
                'message' => 'nid is required'
            ], 400)->setHeader('Access-Control-Allow-Origin', '*')
                    ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                    ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        $data = [
            'cm_nt0101' => $jsonData->cm_nt0101 ?? null, 'cm_nt1502' => $jsonData->cm_nt1502 ?? null, 'cm_nt1602' => $jsonData->cm_nt1602 ?? null,
            'cm_nt0100' => $jsonData->cm_nt0100 ?? null, 'cm_nt0200' => $jsonData->cm_nt0200 ?? null, 'cm_nt0201' => $jsonData->cm_nt0201 ?? null,
            'cm_nt0300' => $jsonData->cm_nt0300 ?? null, 'cm_nt0301' => $jsonData->cm_nt0301 ?? null, 'cm_nt0400' => $jsonData->cm_nt0400 ?? null,
            'cm_nt0401' => $jsonData->cm_nt0401 ?? null, 'cm_nt0500' => $jsonData->cm_nt0500 ?? null, 'cm_nt0501' => $jsonData->cm_nt0501 ?? null,
            'cm_nt0600' => $jsonData->cm_nt0600 ?? null, 'cm_nt0601' => $jsonData->cm_nt0601 ?? null, 'cm_nt0700' => $jsonData->cm_nt0700 ?? null,
            'cm_nt0701' => $jsonData->cm_nt0701 ?? null, 'cm_nt0800' => $jsonData->cm_nt0800 ?? null, 'cm_nt0801' => $jsonData->cm_nt0801 ?? null,
            'cm_nt0900' => $jsonData->cm_nt0900 ?? null, 'cm_nt0901' => $jsonData->cm_nt0901 ?? null, 'cm_nt1000' => $jsonData->cm_nt1000 ?? null,
            'cm_nt1001' => $jsonData->cm_nt1001 ?? null, 'cm_nt1002' => $jsonData->cm_nt1002 ?? null
        ];

        // Filter out null values to avoid an empty `$data` array
        $filteredData = array_filter($data, fn($value) => !is_null($value));

        // Check if `$filteredData` is empty after filtering
        if (empty($filteredData)) {
            //log_message('error', 'No valid data provided in request.');
            return $this->respond([
                'status' => false,
                'message' => 'No valid data provided for update'
            ], 400)->setHeader('Access-Control-Allow-Origin', '*')
                    ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                    ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }

        // Proceed with update if data is valid
        $updateResult = $nightModel->editCM_NightTask($filteredData, $nid);

        if ($updateResult === true) {
            return $this->respond([
                'status' => true,
                'message' => 'Night Task updated successfully.'
            ], 200)->setHeader('Access-Control-Allow-Origin', '*')
                    ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                    ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        } elseif ($updateResult === 'no_changes') {
            return $this->respond([
                'status' => true,
                'message' => 'No changes detected in the data.'
            ], 200)->setHeader('Access-Control-Allow-Origin', '*')
                    ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                    ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to update Night Task'
            ], 404)->setHeader('Access-Control-Allow-Origin', '*')
                    ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
                    ->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        }
    }



    public function saveCmNightTaskDetails()
    {
        $nightModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization(); 
        $jsonData = $this->request->getJSON();

        // Log received JSON data for debugging
        //log_message('error', 'Received JSON Data: ' . json_encode($jsonData));

        // Extract mid and data fields
        $nid = $jsonData->cm_nid ?? null;
        //log_message('error', 'Received JSON Databy suhas : ' . json_encode($jsonData));

        if (!$nid) {
            //log_message('error', 'Missing nid in request data.');
            return $this->respond([
                'status' => false,
                'message' => 'nid is required'
            ], 400);
        }

        $data = [
            'cm_nt0101' => $jsonData->cm_nt0101 ?? null, 'cm_nt1502' => $jsonData->cm_nt1502 ?? null, 'cm_nt1602' => $jsonData->cm_nt1602 ?? null,
            'cm_nt0100' => $jsonData->cm_nt0100 ?? null, 'cm_nt0200' => $jsonData->cm_nt0200 ?? null, 'cm_nt0201' => $jsonData->cm_nt0201 ?? null,'cm_nt0202' => $jsonData->cm_nt0202 ?? null,
            'cm_nt0300' => $jsonData->cm_nt0300 ?? null, 'cm_nt0301' => $jsonData->cm_nt0301 ?? null, 'cm_nt0400' => $jsonData->cm_nt0400 ?? null,'cm_nt0302' => $jsonData->cm_nt0302 ?? null,
            'cm_nt0401' => $jsonData->cm_nt0401 ?? null, 'cm_nt0500' => $jsonData->cm_nt0500 ?? null, 'cm_nt0501' => $jsonData->cm_nt0501 ?? null,'cm_nt0402' => $jsonData->cm_nt0402 ?? null,
            'cm_nt0600' => $jsonData->cm_nt0600 ?? null, 'cm_nt0601' => $jsonData->cm_nt0601 ?? null, 'cm_nt0700' => $jsonData->cm_nt0700 ?? null,'cm_nt0502' => $jsonData->cm_nt0502 ?? null,
            'cm_nt0701' => $jsonData->cm_nt0701 ?? null, 'cm_nt0800' => $jsonData->cm_nt0800 ?? null, 'cm_nt0801' => $jsonData->cm_nt0801 ?? null,'cm_nt0602' => $jsonData->cm_nt0602 ?? null,
            'cm_nt0900' => $jsonData->cm_nt0900 ?? null, 'cm_nt0901' => $jsonData->cm_nt0901 ?? null, 'cm_nt1000' => $jsonData->cm_nt1000 ?? null,'cm_nt0802' => $jsonData->cm_nt0802 ?? null,
            'cm_nt1001' => $jsonData->cm_nt1001 ?? null, 'cm_nt1002' => $jsonData->cm_nt1002 ?? null,'cm_nt0102' => $jsonData->cm_nt0102 ?? null,'cm_nt0702' => $jsonData->cm_nt0702 ?? null,
            'cm_nt0902' => $jsonData->cm_nt0902 ?? null,

        ];

        // Filter out null values to avoid an empty `$data` array
        $filteredData = array_filter($data, fn($value) => !is_null($value));

        // Check if `$filteredData` is empty after filtering
        if (empty($filteredData)) {
            //log_message('error', 'No valid data provided in request.');
            return $this->respond([
                'status' => false,
                'message' => 'No valid data provided for update'
            ], 400) ;
        }

        // Proceed with update if data is valid
        $updateResult = $nightModel->editCM_NightTask($filteredData, $nid);

        if ($updateResult === true) {
            return $this->respond([
                'status' => true,
                'message' => 'Night Task updated successfully.'
            ], 200);
        } elseif ($updateResult === 'no_changes') {
            return $this->respond([
                'status' => true,
                'message' => 'No changes detected in the data.'
            ]);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to update Night Task'
            ], 404);
        }
    }


    public function uploadedCm_nightlist(){

        $nightModel = new CM_NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if selectedBranch and selectedDate are set in the request data
        if (isset($requestData->selectedCluster) && isset($requestData->selectedDate)) {
            $selectedCluster = $requestData->selectedCluster;
            $selectedDate = $requestData->selectedDate;
        } else {
            // Handle the case where the expected data is missing
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameters: selectedBranch or selectedDate.'
            ], 400);
        }
        // Pass the parameters to the model
        $mtDetails = $nightModel->uploadedNightTlist($selectedCluster, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
        }
    }


    private function validateAuthorization() {
        if ( !class_exists( 'App\Services\JwtService' ) ) {
            //log_message( 'error', 'JwtService class not found' );
            return $this->respond( [ 'error' => 'JwtService class not found' ], 500 );
        }
        // Get the Authorization header and log it
        $authorizationHeader = $this->request->getHeader( 'Authorization' ) ? $this->request->getHeader( 'Authorization' )->getValue() : null;
        //log_message( 'info', 'Authorization header: ' . $authorizationHeader );

        // Create an instance of JwtService and validate the token
        $jwtService = new JwtService();
        $result = $jwtService->validateToken( $authorizationHeader );

        // Handle token validation errors
        if ( isset( $result[ 'error' ] ) ) {
            //log_message( 'error', $result[ 'error' ] );
            return $this->respond( [ 'error' => $result[ 'error' ] ], $result[ 'status' ] );
        }

        // Extract the decoded token and get the USER-ID
        $decodedToken = $result[ 'data' ];
        return $decodedToken;
        // Assuming JWT contains USER-ID

    }

}
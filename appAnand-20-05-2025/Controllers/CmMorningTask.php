<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait; 
use App\Models\DeptModel;
use App\Models\CM_MtModel;
use App\Services\JwtService;

class CmMorningTask extends BaseController {
    use ResponseTrait;

    public function index(): string { 
        return view( 'welcome_message' );
    }

    public function getBmcMorningTaskList(){
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getBmcMorningTaskList($role, $user);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
          }

    } 

    // public function getBmcWeeklyTaskList(){
    //     $mtModel = new CM_MtModel();
    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code; 
    //       // Pass the parameters to the model
    //       $mtDetails = $mtModel->getBmcWeeklyTaskList($role, $user);
    //       if ($mtDetails) {
    //           return $this->respond(['status' => true, 'message' => 'Weekly Task Details.', 'data' => $mtDetails], 200);
    //       } else {
    //           return $this->respond(['status' => false, 'message' => 'Weekly Task Details not found'], 404);
    //       }
    // } 


    public function getBmcWeeklyTaskList(){
        $mtModel = new CM_MtModel();
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

         if ($requestData->selectedBranch > 0) {
            $selectedBranch = $requestData->selectedBranch;            
        } else {
            $selectedBranch = 'All';
        }
 
          // Pass the parameters to the model
          $mtDetails = $mtModel->getBmcWeeklyTaskList($role, $user, $selectedMonth, $selectedBranch);
          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Weekly Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Weekly Task Details not found'], 404);
          }
    } 

    public function  getCm_Z_MorningTaskList(){
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getCm_Z_MorningTaskList($role, $user); 

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
          }
    }

    public function  getBm_Z_MorningTaskList(){
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getBm_Z_MorningTaskList($role, $user);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
          }

    }

    public function getCMUserBranchList(){
        $mtModel = new CM_MtModel(); 
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getCMUserBranchList($user, $role);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'CM Branch Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'CM Branch Details not found'], 404);
          }

    }

    public function getCMUserBranchListDetails(){
        $mtModel = new CM_MtModel(); 
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getCMUserBranchListDetails($user, $role);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'CM Branch Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'CM Branch Details not found'], 404);
          }

    }

    public function getCM_BranchMorningTaskList(){
        $mtModel = new CM_MtModel(); 
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
        if (isset($requestData->selectedCluster) && $requestData->selectedCluster > 0) {
            $selectedCluster = $requestData->selectedCluster;            
        } else {
            $selectedCluster = '0';
        }
       
          // Pass the parameters to the model
          $mtDetails = $mtModel->getCM_BranchMorningTaskList($user, $role, $selectedMonth, $selectedBranch, $selectedCluster);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'CM Branch Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'CM Branch Task Details not found'], 404);
          }
    }

    public function getZ_BranchWeeklyList(){
        $mtModel = new CM_MtModel(); 
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

         if ($requestData->selectedBranch > 0) {
            $selectedBranch = $requestData->selectedBranch;            
        } else {
            $selectedBranch = 'All';
        }

        if (isset($requestData->selectedCluster) && $requestData->selectedCluster > 0) {
            $selectedCluster = $requestData->selectedCluster;            
        } else {
            $selectedCluster = '0';
        } 
 
        //log_message('error', 'Selected Branch: ' . $selectedBranch);
       
          // Pass the parameters to the model
          $mtDetails = $mtModel->getZ_BranchWeeklyList($user, $role, $selectedMonth, $selectedBranch, $selectedCluster);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'CM Branch Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'CM Branch Task Details not found'], 404);
          }
    }


    public function getCMBranchComboTaskList(){
        $mtModel = new CM_MtModel(); 
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

        if (isset($requestData->selectedCluster) && $requestData->selectedCluster > 0) {
            $selectedCluster = $requestData->selectedCluster;            
        } else {
            $selectedCluster = '0';
        } 
 
        //log_message('error', 'Selected Branch: ' . $selectedBranch);
       
          // Pass the parameters to the model
          $mtDetails = $mtModel->getCMBranchComboTaskList($user, $role, $selectedMonth, $selectedBranch, $selectedCluster);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'CM Branch Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'CM Branch Task Details not found'], 404);
          }
    }

    public function getZonalManagerBranchList(){
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getZonalManagerBranchList($user, $role);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
          }
    }

    public function getCmMorningTaskList(){
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

          // Pass the parameters to the model
          $mtDetails = $mtModel->getCmMorningTaskList($role, $user);

          if ($mtDetails) {
              return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
          } else {
              return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
          }
 
    }

    public function uploadedCmMTtask(){
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if selectedCluster and selectedDate are set in the request data
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
        $mtDetails = $mtModel->uploadedCmMTtask($selectedCluster, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }

    }

    public function getCm_morningtaskDetails() {
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if selectedBranch and selectedDate are set in the request data
        if (isset($requestData->selectedBranch) && isset($requestData->selectedDate)) {
            $selectedBranch = $requestData->selectedBranch;
            $selectedDate = $requestData->selectedDate;
        } else {
            // Handle the case where the expected data is missing
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameters: selectedBranch or selectedDate.'
            ], 400);
        }

        // Pass the parameters to the model
        $mtDetails = $mtModel->getCm_morningtaskDetails($selectedBranch, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }
    }


    public function getCmMorningTaskDetails() {
        $mtModel = new CM_MtModel();
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
        $mtDetails = $mtModel->getCmMorningTaskDetails($selectedCluster, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }
    }

    public function getCmMorningTaskDetailsNew() {
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if selectedBranch and selectedDate are set in the request data
        if (isset($requestData->mid)) {
            $mid = $requestData->mid;
        } else {
            // Handle the case where the expected data is missing
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameters: mid.'
            ], 400);
        }

        // Pass the parameters to the model
        $mtDetails = $mtModel->getCmMorningTaskDetailsNew($mid, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }
    }

 

    public function addCmMorningTask() {
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;

        try {
            // Log the raw input data for debugging
            $rawInput = $this->request->getBody();
            //log_message('error', 'Raw input data: ' . $rawInput);

            // Get the JSON data from the POST request
            $jsonData = $this->request->getJSON();

            // Validate if the JSON data is present
            if (!$jsonData) {
                return $this->respond(['status' => false, 'message' => 'Invalid JSON data'], 400);
            }

            // Extract data from the received JSON
            $cluster_id = $jsonData->cluster_id;
            $createdDTM = $jsonData->createdDTM;  // This should be sent from the frontend

            // Assuming the data has the morning task values (e.g., mt0100, mt0101, etc.)
            $data = [
                'mt0100' => $jsonData->mt0100 ?? null,
                'mt0101' => $jsonData->mt0101 ?? null,
                'mt0102' => $jsonData->mt0102 ?? null,
                'mt0200' => $jsonData->mt0200 ?? null,
                'mt0201' => $jsonData->mt0201 ?? null,
                'mt0202' => $jsonData->mt0202 ?? null,
                'mt0300' => $jsonData->mt0300 ?? null,
                'mt0301' => $jsonData->mt0301 ?? null,
                'mt0302' => $jsonData->mt0302 ?? null,
                'mt0400' => $jsonData->mt0400 ?? null,
                'mt0401' => $jsonData->mt0401 ?? null,
                'mt0402' => $jsonData->mt0402 ?? null,
                'mt0500' => $jsonData->mt0500 ?? null,
                'mt0501' => $jsonData->mt0501 ?? null,
                'mt0502' => $jsonData->mt0502 ?? null,
                'mt0600' => $jsonData->mt0600 ?? null,
                'mt0601' => $jsonData->mt0601 ?? null,
                'mt0602' => $jsonData->mt0602 ?? null,
                'mt0700' => $jsonData->mt0700 ?? null,
                'mt0701' => $jsonData->mt0701 ?? null,
                'mt0702' => $jsonData->mt0702 ?? null,
                'mt0800' => $jsonData->mt0800 ?? null,
                'mt0801' => $jsonData->mt0801 ?? null,
                'mt0802' => $jsonData->mt0802 ?? null,
                'mt0900' => $jsonData->mt0900 ?? null,
                'mt0901' => $jsonData->mt0901 ?? null,
                'mt0902' => $jsonData->mt0902 ?? null,
                'mt1000' => $jsonData->mt1000 ?? null,
                'mt1001' => $jsonData->mt1001 ?? null,
                'mt1002' => $jsonData->mt1002 ?? null,
                'cluster_id' => $cluster_id,
                'emp_code' => $emp_code,
                'createdDTM' => $createdDTM,
                'created_by' => $emp_code,
            ];

            // Check if the branch is valid
            if ($cluster_id > 0) {
                // Add the morning task using the model
                $mt = $mtModel->addCmMorningTask($data, $createdDTM, $emp_code);

                if ($mt) {
                    return $this->respond(['status' => true, 'message' => 'Morning Task added successfully.', 'data' => $mt], 200);
                } else {
                    return $this->respond(['status' => false, 'message' => 'Failed to add Morning Task'], 500);
                }
            }

            // If branch is not valid, return an error
            return $this->respond(['status' => false, 'message' => 'Invalid branch ID'], 400);
        } catch (\Exception $e) {
            //log_message('error', 'JSON parsing error: ' . $e->getMessage());
            return $this->respond(['status' => false, 'message' => 'Failed to parse JSON string. Error: ' . $e->getMessage()], 400);
        }

    }

    public function saveCm_morningtaskDetails()
    {
        $mtModel = new CM_MtModel();
        $userDetails = $this->validateAuthorization();

        // Retrieve JSON data from the POST request
        $jsonData = $this->request->getJSON();

        // Log received JSON data for debugging
        //log_message('debug', 'Received JSON Data: ' . json_encode($jsonData));

        // Extract mid and data fields
        $mid = $jsonData->mid ?? null;

        if (!$mid) {
            //log_message('error', 'Missing mid in request data.');
            return $this->respond([
                'status' => false,
                'message' => 'mid is required'
            ], 400);
        }

        $data = [
            'mt0100' => $jsonData->mt0100 ?? null,
            'mt0101' => $jsonData->mt0101 ?? null,
            'mt0102' => $jsonData->mt0102 ?? null,
            'mt0200' => $jsonData->mt0200 ?? null,
            'mt0201' => $jsonData->mt0201 ?? null,
            'mt0202' => $jsonData->mt0202 ?? null,
            'mt0300' => $jsonData->mt0300 ?? null,
            'mt0301' => $jsonData->mt0301 ?? null,
            'mt0302' => $jsonData->mt0302 ?? null,
            'mt0400' => $jsonData->mt0400 ?? null,
            'mt0401' => $jsonData->mt0401 ?? null,
            'mt0402' => $jsonData->mt0402 ?? null,
            'mt0500' => $jsonData->mt0500 ?? null,
            'mt0501' => $jsonData->mt0501 ?? null,
            'mt0502' => $jsonData->mt0502 ?? null,
            'mt0600' => $jsonData->mt0600 ?? null,
            'mt0601' => $jsonData->mt0601 ?? null,
            'mt0602' => $jsonData->mt0602 ?? null,
            'mt0700' => $jsonData->mt0700 ?? null,
            'mt0701' => $jsonData->mt0701 ?? null,
            'mt0702' => $jsonData->mt0702 ?? null,
            'mt0800' => $jsonData->mt0800 ?? null,
            'mt0801' => $jsonData->mt0801 ?? null,
            'mt0802' => $jsonData->mt0802 ?? null,
            'mt0900' => $jsonData->mt0900 ?? null,
            'mt0901' => $jsonData->mt0901 ?? null,
            'mt0902' => $jsonData->mt0902 ?? null,
            'mt1000' => $jsonData->mt1000 ?? null,
            'mt1001' => $jsonData->mt1001 ?? null,
            'mt1002' => $jsonData->mt1002 ?? null,
        ];

        // Filter out null values to avoid an empty `$data` array
        $filteredData = array_filter($data, fn($value) => !is_null($value));
        //log_message('error', json_encode($filteredData));

        // Check if `$filteredData` is empty after filtering
        if (empty($filteredData)) {
            //log_message('error', 'No valid data provided in request.');
            return $this->respond([
                'status' => false,
                'message' => 'No valid data provided for update'
            ], 400);
        }

        // Proceed with update if data is valid
        $updateResult = $mtModel->editMoringTask($filteredData, $mid);

        if ($updateResult === true) {
            return $this->respond([
                'status' => true,
                'message' => 'Morning Task updated successfully.'
            ], 200);
        } elseif ($updateResult === 'no_changes') {
            return $this->respond([
                'status' => true,
                'message' => 'No changes detected in the data.'
            ], 200);
        } else {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to update Morning Task'
            ], 404);
        }
    }






    private function validateAuthorization() {
        if (!class_exists('App\Services\JwtService')) {
            //log_message('error', 'JwtService class not found');
            return $this->failServerError('Authorization service not available.');
        }

        // Get Authorization header
        $authorizationHeader = $this->request->getHeaderLine('Authorization');
        //log_message('info', 'Authorization header: ' . $authorizationHeader);

        // Token validation
        $jwtService = new JwtService();
        $result = $jwtService->validateToken($authorizationHeader);

        // Handle validation errors with structured responses
        if (isset($result['error'])) {
            //log_message('error', 'Token validation error: ' . $result['error']);
            return $this->failUnauthorized('Token validation failed');
        }

        // Return user details on success
        return $result['data'];
    }


}
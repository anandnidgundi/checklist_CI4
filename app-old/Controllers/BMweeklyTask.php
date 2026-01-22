<?php
namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BmWeeklyTaskModel;
use App\Models\UserModel;
 
use App\Services\JwtService;

class BMweeklyTask extends BaseController
 {
    use ResponseTrait;

    public function getBmWeeklyTaskList(){
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
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $bmWeeklyTaskList = $bmWeeklyTaskModel->getBmWeeklyTaskList($role, $user, $selectedMonth);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => [
                'success' => 'Weekly Task updated successfully'
            ],
            'data' => $bmWeeklyTaskList
        ];
        return $this->respond($response);        
    }

    public function getBmWeeklyTask(){ 
        
        $userDetails = $this->validateAuthorization(); 
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();       
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;
        // Get the JSON data from the POST request
        $jsonData = $this->request->getJSON();
        $bmw_id = $jsonData->bmw_id;
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $bmWeeklyTask = $bmWeeklyTaskModel->getBmWeeklyTask($bmw_id);
       // return $this->respond($bmWeeklyTask, 200);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => [
                'success' => 'Weekly Task updated successfully'
            ],
            'data' => $bmWeeklyTask
        ];
        return $this->respond($response);
    }
    

    public function createBmWeeklyTask(){
        $userDetails = $this->validateAuthorization(); 
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();       
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;
        // Get the JSON data from the POST request
        $jsonData = $this->request->getJSON();
      
        // Validate if the JSON data is present
        if (!$jsonData) {
            return $this->respond(['status' => false, 'message' => 'Invalid JSON data'], 400);
        }

        //$createdDTM = $jsonData->createdDTM;  // This should be sent from the frontend
        $createdDTM = date('Y-m-d H:i:s', strtotime($jsonData->createdDTM));

        // Check if a record already exists for the given branch_id and createdDTM
        $existedRecord = $bmWeeklyTaskModel->where('branch_id', $jsonData->branch_id)
                                           ->where('createdDTM', $createdDTM)
                                           ->first();

        if ($existedRecord) {
            return $this->respond(['status' => true, 'message' => 'Record already exists', 'data' => $existedRecord['bmw_id']], 200);
        }

        // Assuming the data has the morning task values (e.g., mt0100, mt0101, etc.)
        $data = [
            'branch_id' => $jsonData->branch_id,
            'w_0100' => $jsonData->w_0100,
            'w_0101' => $jsonData->w_0101,
            'w_0102' => $jsonData->w_0102,
            'w_0200' => $jsonData->w_0200,
            'w_0201' => $jsonData->w_0201,
            'w_0202' => $jsonData->w_0202,
            'w_0300' => $jsonData->w_0300,
            'w_0301' => $jsonData->w_0301,
            'w_0302' => $jsonData->w_0302,
            'w_0400' => $jsonData->w_0400,
            'w_0401' => $jsonData->w_0401,
            'w_0402' => $jsonData->w_0402,
            'w_0500' => $jsonData->w_0500,
            'w_0501' => $jsonData->w_0501,
            'w_0502' => $jsonData->w_0502,
            'w_0600' => $jsonData->w_0600,
            'w_0601' => $jsonData->w_0601,
            'w_0602' => $jsonData->w_0602,
            'w_0700' => $jsonData->w_0700,
            'w_0701' => $jsonData->w_0701,
            'w_0702' => $jsonData->w_0702,
            'w_0800' => $jsonData->w_0800,
            'w_0801' => $jsonData->w_0801,
            'w_0802' => $jsonData->w_0802,
            'w_0900' => $jsonData->w_0900,
            'w_0901' => $jsonData->w_0901,
            'w_0902' => $jsonData->w_0902,
            'w_1000' => $jsonData->w_1000,
            'w_1001' => $jsonData->w_1001,
            'w_1002' => $jsonData->w_1002,
            'w_1100' => $jsonData->w_1100,
            'w_1101' => $jsonData->w_1101,
            'w_1102' => $jsonData->w_1102,
            'w_1200' => $jsonData->w_1200,
            'w_1201' => $jsonData->w_1201,
            'w_1202' => $jsonData->w_1202,
            'w_1300' => $jsonData->w_1300,
            'w_1301' => $jsonData->w_1301,
            'w_1302' => $jsonData->w_1302,
            'w_1400' => $jsonData->w_1400,
            'emp_code' => $emp_code,
            'createdDTM' => $createdDTM,
            'createdBy' => $jsonData->createdBy,
        ];

        // Filter out null values to avoid an empty `$data` array
        $filteredData = array_filter($data, fn($value) => $value !== null);

        $bmWeeklyTaskModel->insert($filteredData);
        $insertId = $bmWeeklyTaskModel->insertID();
        $response = [
            'status' => true,
            'error' => null,
            'message' => [
                'success' => 'Weekly Task created successfully',
                
            ],
            'data' => $insertId,
        ];
        return $this->respondCreated($response);
    }

    public function updateBmWeeklyTask(){
        $userDetails = $this->validateAuthorization(); 
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $jsonData = $this->request->getJSON();

        // Validate if the JSON data is present
        if (!$jsonData) {
            return $this->respond(['status' => false, 'message' => 'Invalid JSON data'], 400);
        }

        $id = $jsonData->bmw_id;
        $data = [             
            'w_0100' => $jsonData->w_0100,
            'w_0101' => $jsonData->w_0101,
            'w_0102' => $jsonData->w_0102,
            'w_0200' => $jsonData->w_0200,
            'w_0201' => $jsonData->w_0201,
            'w_0202' => $jsonData->w_0202,
            'w_0300' => $jsonData->w_0300,
            'w_0301' => $jsonData->w_0301,
            'w_0302' => $jsonData->w_0302,
            'w_0400' => $jsonData->w_0400,
            'w_0401' => $jsonData->w_0401,
            'w_0402' => $jsonData->w_0402,
            'w_0500' => $jsonData->w_0500,
            'w_0501' => $jsonData->w_0501,
            'w_0502' => $jsonData->w_0502,
            'w_0600' => $jsonData->w_0600,
            'w_0601' => $jsonData->w_0601,
            'w_0602' => $jsonData->w_0602,
            'w_0700' => $jsonData->w_0700,
            'w_0701' => $jsonData->w_0701,
            'w_0702' => $jsonData->w_0702,
            'w_0800' => $jsonData->w_0800,
            'w_0801' => $jsonData->w_0801,
            'w_0802' => $jsonData->w_0802,
            'w_0900' => $jsonData->w_0900,
            'w_0901' => $jsonData->w_0901,
            'w_0902' => $jsonData->w_0902,
            'w_1000' => $jsonData->w_1000,
            'w_1001' => $jsonData->w_1001,
            'w_1002' => $jsonData->w_1002,
            'w_1100' => $jsonData->w_1100,
            'w_1101' => $jsonData->w_1101,
            'w_1102' => $jsonData->w_1102,
            'w_1200' => $jsonData->w_1200,
            'w_1201' => $jsonData->w_1201,
            'w_1202' => $jsonData->w_1202,
            'w_1300' => $jsonData->w_1300,
            'w_1301' => $jsonData->w_1301,
            'w_1302' => $jsonData->w_1302,
            'w_1400' => $jsonData->w_1400,
            'modifiedDTM' => date('Y-m-d H:i:s'),          
        ];

        // Filter out null values to avoid an empty `$data` array
        $filteredData = array_filter($data, fn($value) => $value !== null);

        $bmWeeklyTaskModel->updateBmWeeklyTask($id, $filteredData);
        $response = [
            'status' =>true,
            'error' => null,
            'message' => [
                'success' => 'Weekly Task updated successfully'
            ]
        ];
        return $this->respond($response);
    }

    public function deleteBmWeeklyTask($id){
        $userDetails = $this->validateAuthorization(); 
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $bmWeeklyTaskModel->delete($id);
        $response = [
            'status' => 200,
            'error' => null,
            'message' => [
                'success' => 'Weekly Task deleted successfully'
            ]
        ];
        return $this->respondDeleted($response);
    }

    public function getBmWeeklyTaskByBranch($branch_id){
        $userDetails = $this->validateAuthorization(); 
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $bmWeeklyTask = $bmWeeklyTaskModel->where('branch_id', $branch_id)->findAll();
        return $this->respond($bmWeeklyTask, 200);
    }

    public function getBmWeeklyTaskByCluster($cluster_id){
        $userDetails = $this->validateAuthorization(); 
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $bmWeeklyTask = $bmWeeklyTaskModel->where('cluster_id', $cluster_id)->findAll();
        return $this->respond($bmWeeklyTask, 200);
    }

    public function getBmWeeklyTaskByBranchAndCluster($branch_id, $cluster_id){
        $userDetails = $this->validateAuthorization(); 
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $bmWeeklyTask = $bmWeeklyTaskModel->where('branch_id', $branch_id)->where('cluster_id', $cluster_id)->findAll();
        return $this->respond($bmWeeklyTask, 200);
    }

    public function getBmWeeklyTaskByUser(){
        $decodedToken = $this->validateAuthorization();
        $userModel = new UserModel();
        $bmWeeklyTaskModel = new BmWeeklyTaskModel();
        $user = $userModel->find($decodedToken->USER_ID);
        $bmWeeklyTask = $bmWeeklyTaskModel->where('branch_id', $user['branch_id'])->where('cluster_id', $user['cluster_id'])->findAll();
        return $this->respond($bmWeeklyTask, 200);
    }



    

        private function validateAuthorization()
        {
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

            // Extract the decoded token and get the USER_ID
            $decodedToken = $result[ 'data' ];
            return $decodedToken;
            // Assuming JWT contains USER_ID

        }

}
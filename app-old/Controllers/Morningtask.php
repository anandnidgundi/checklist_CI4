<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\DeptModel;
use App\Models\MtModel;
use App\Services\JwtService;

class Morningtask extends BaseController
{
    use ResponseTrait;

    public function index(): string
    {
        return view('welcome_message');
    }

    public function getBranchComboTaskList()
    {
        $mtModel = new MtModel();
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

        // Pass the parameters to the model
        $mtDetails = $mtModel->getBranchComboTaskList($role, $user, $selectedMonth);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }


    }

    public function getBranchMorningTaskList()
    {
        $mtModel = new MtModel();
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

        // Pass the parameters to the model
        $mtDetails = $mtModel->getBranchMorningTaskList($role, $user, $selectedMonth);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }

    }

    public function uploadedMTlist()
    {
        $mtModel = new MtModel();
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
        $mtDetails = $mtModel->uploadedMTlist($selectedBranch, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }

    }

    public function getMorningTaskDetails()
    {
        $mtModel = new MtModel();
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
        $mtDetails = $mtModel->getMorningTaskDetails($selectedBranch, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }
    }

    public function getMorningTaskDetailsByMid()
    {
        $mtModel = new MtModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if midSelected is set in the request data
        if (isset($requestData->mid)) { 
            $midSelected = $requestData->mid;
        } else {
            // Handle the case where the expected data is missing
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameter: midSelected.id'
            ], 400);
        }

        // Pass the parameters to the model
        $mtDetails = $mtModel->getMorningTaskDetailsByMid($midSelected, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
        }
    }


    public function addMorningTask()
    {
        $mtModel = new MtModel();
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
        $branch = $jsonData->branch_id;
        $createdDTM = $jsonData->createdDTM;  // This should be sent from the frontend
        $taskDate = $jsonData->createdDTM;
        $time = date(' H:i:s');
        $createdDTM .= $time;

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
            'branch' => $branch,
            'emp_code' => $emp_code,
            'createdDTM' => $createdDTM,
            'taskDate' => $taskDate,
            'created_by' => $emp_code,
        ];

        log_message('error', 'Received JSON Data: ' . json_encode($jsonData));

        // Check if the branch is valid
        if ($branch > 0) {
            // Check if a record already exists for the given taskDate and branch
            $existedRecord = $mtModel->checkExistingRecord($taskDate, $branch);

            if ($existedRecord) {
                return $this->respond(['status' => true, 'message' => 'Record already exists.', 'data' => $existedRecord], 200);
            }

            // Add the morning task using the model
            $insertId = $mtModel->addMorningTask($data, $taskDate, $branch);
            if (!$insertId) {
                return $this->respond(['status' => false, 'message' => 'Failed to add task'], 500);
            }
            $subQuestions = [
                'mt0100' => ['House Keeping', 'Front Office', 'Admin', 'Phlebo Technician'],
                'mt0200' => ['House Keeping', 'Front Office', 'Admin', 'Phlebo Technician'],
                'mt0300' => ['Electrical', 'Plumbing', 'AC', 'Carpenter', 'Water Dispenser', 'Glass Related', 'Civil Work', 'Shutter', 'Others'],
                'mt0400' => ['Ultrasound', 'CT', 'MRI', 'X-Ray', 'Others'],
                'mt0500' => ['Total No. of doctors in the branch', 'Doctors Present'],
            ];

            $subData = [];
            foreach ($subQuestions as $sq_id => $questions) {
                foreach ($questions as $squestion) {
                    $subData[] = [
                        'task_id' => $insertId,
                        'sq_id' => $sq_id,
                        'sq' => 'NO',
                        'squestion' => $squestion,
                        'sqvalue' => '0',
                        'createdDTM' => $createdDTM,
                        'updatedBy' => $emp_code,
                    ];
                }
            }

            // Insert subData into the database
            if (!$mtModel->addSubquestions($subData)) {
                return $this->respond(['status' => false, 'message' => 'Failed to add sub-tasks'], 500);
            }

            return $this->respond(['status' => true, 'message' => 'Morning Task added successfully.', 'data' => $insertId], 201);
        }

        // If branch is not valid, return an error
        return $this->respond(['status' => false, 'message' => 'Invalid branch ID'], 400);
    }


    public function saveMorningTaskDetails()
    {
        $mtModel = new MtModel();
        $userDetails = $this->validateAuthorization();
        $modifiedDTM = date('Y-m-d H:i:s');
        // Retrieve JSON data from the POST request
        $jsonData = $this->request->getJSON();

        // Log received data for debugging
        log_message('debug', 'Received JSON Data: ' . json_encode($jsonData));

        // Extract `mid` and check if it exists
        $mid = $jsonData->mid->id ?? null;
        if (!$mid) {
            return $this->respond([
                'status' => false,
                'message' => 'Missing `mid` parameter.'
            ], 400);
        }

        // Prepare the task update data
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
            'modifiedDTM' => $modifiedDTM,
        ];

        // **Update the main Morning Task**
        $updateResult = $mtModel->editMoringTask($data, $mid);

        // If update failed, return an error
        if (!$updateResult) {
            return $this->respond([
                'status' => false,
                'message' => 'Failed to update Morning Task'
            ], 500);
        }

        // **Update subquestions if they exist**
        if (!empty($jsonData->subquestions) && is_array($jsonData->subquestions)) {
            $subQuestions = [];
            foreach ($jsonData->subquestions as $sub) {
                if (!isset($sub->id) || !isset($sub->sqvalue)) {
                    continue; // Skip invalid subquestions
                }
                $subQuestions[] = [
                    'id' => $sub->id,  // Ensure `id` is used for updating
                    'sqvalue' => $sub->sqvalue, // Updated value
                    'updatedDTM' => $modifiedDTM,
                    'updatedBy' => $userDetails->emp_code,
                ];
            }
            // Update subquestions in the database
            if (!empty($subQuestions)) {
                $subUpdateResult = $mtModel->updateSubquestions($subQuestions);

                if (!$subUpdateResult) {
                    return $this->respond([
                        'status' => false,
                        'message' => 'Failed to update subquestions'
                    ], 500);
                }
            }
        }
        // **Return success response**
        return $this->respond([
            'status' => true,
            'message' => 'Morning Task and subquestions updated successfully.'
        ], 200);
    }

    private function validateAuthorization()
    {
        if (!class_exists('App\Services\JwtService')) {
            ////log_message( 'error', 'JwtService class not found' );
            return $this->respond(['error' => 'JwtService class not found'], 500);
        }
        // Get the Authorization header and log it
        $authorizationHeader = $this->request->getHeader('Authorization') ? $this->request->getHeader('Authorization')->getValue() : null;
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
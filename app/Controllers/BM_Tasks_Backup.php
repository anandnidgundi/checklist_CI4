<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\DeptModel;
use App\Models\MtModel;
use App\Models\BM_TasksModel;
use App\Services\JwtService;

class BM_Tasks extends BaseController
{
    use ResponseTrait;

    public function addBM_Task()
    {
        $bmTasksModel = new BM_TasksModel();
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
            'mt0103' => $jsonData->mt0101 ?? null,
            'mt0104' => $jsonData->mt0102 ?? null,
            'mt0105' => $jsonData->mt0105 ?? null,
            'mt0200' => $jsonData->mt0200 ?? null,
            'mt0201' => $jsonData->mt0201 ?? null,
            'mt0202' => $jsonData->mt0202 ?? null,
            'mt0203' => $jsonData->mt0101 ?? null,
            'mt0204' => $jsonData->mt0102 ?? null,
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
            'mt0903' => $jsonData->mt0903 ?? null,
            'mt1000' => $jsonData->mt1000 ?? null,
            'mt1001' => $jsonData->mt1001 ?? null,
            'mt1002' => $jsonData->mt1002 ?? null,
            'mt1100' => $jsonData->mt1100 ?? null,
            'mt1101' => $jsonData->mt1101 ?? null,
            'mt1102' => $jsonData->mt1102 ?? null,
            'branch' => $branch,
            'emp_code' => $emp_code,
            'createdDTM' => $createdDTM,
            'taskDate' => $taskDate,
            'created_by' => $emp_code,
        ];

        //  log_message('error', 'Received JSON Data: ' . json_encode($jsonData));

        //Check if the branch is valid
        if ($branch > 0) {
        
            
            $existedRecord = $bmTasksModel->checkExistingRecord($taskDate, $branch);
           
            if ($existedRecord) {
                
                return $this->respond(['status' => false, 'message' => 'Record already exists for this date and branch.', 'data' => $existedRecord], 409);
            }
            
            // Add the morning task using the model
            $insertId = $bmTasksModel->addBM_Task($data, $taskDate, $branch);
            if (!$insertId) {
                return $this->respond(['status' => false, 'message' => 'Failed to add task'], 500);
            }

            

            return $this->respond(['status' => true, 'message' => 'Morning Task added successfully.', 'data' => $insertId], 201);
        }

        // If branch is not valid, return an error
        return $this->respond(['status' => false, 'message' => 'Invalid branch ID'], 400);
    }

    //editBM_Task
    public function editBM_Task($id)
    {
        $bmTasksModel = new BM_TasksModel();
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
        $branch = $jsonData->branch;
        $createdDTM = $jsonData->createdDTM;
        $taskDate = $jsonData->taskDate;

        // Prepare the data array for update
        $data = [
            'mt0100' => $jsonData->mt0100 ?? null,
            'mt0101' => $jsonData->mt0101 ?? null,
            'mt0102' => $jsonData->mt0102 ?? null,
            'mt0103' => $jsonData->mt0103 ?? null,
            'mt0104' => $jsonData->mt0104 ?? null,
            'mt0105' => $jsonData->mt0105 ?? null,
            'mt0200' => $jsonData->mt0200 ?? null,
            'mt0201' => $jsonData->mt0201 ?? null,
            'mt0202' => $jsonData->mt0202 ?? null,
            'mt0203' => $jsonData->mt0203 ?? null,
            'mt0204' => $jsonData->mt0204 ?? null,
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
            'mt0903' => $jsonData->mt0903 ?? null,
            'mt1000' => $jsonData->mt1000 ?? null,
            'mt1001' => $jsonData->mt1001 ?? null,
            'mt1002' => $jsonData->mt1002 ?? null,
            'mt1100' => $jsonData->mt1100 ?? null,
            'mt1101' => $jsonData->mt1101 ?? null,
            'mt1102' => $jsonData->mt1102 ?? null,
            'modifiedDTM' => date('Y-m-d H:i:s'),
            'updated_by' => $emp_code
        ];

        if ($branch > 0) {
            // Update the main task
            $updateResult = $bmTasksModel->editBM_Task($data, $id, $branch);
            if (!$updateResult) {
                return $this->respond(['status' => false, 'message' => 'Failed to update task'], 500);
            }

            // Update subquestions if present
            if (isset($jsonData->subquestions)) {
                foreach ($jsonData->subquestions as $subq) {
                    $subData = [
                        'sqvalue' => $subq->sqvalue,
                        'updatedBy' => $emp_code,
                        'updatedDTM	' => date('Y-m-d H:i:s')
                    ];
                    $bmTasksModel->updateSubquestion($subq->id, $subData);
                }
            }

            // Handle report amendments
            if (isset($jsonData->report_amendments) && is_array($jsonData->report_amendments)) {
                foreach ($jsonData->report_amendments as $amendment) {
                    try {
                        $report_amendment = [
                            'task_id' => $id, // Use the main task ID
                            'regd_number' => $amendment->regd_number ?? '',
                            'regd_remarks' => $amendment->regd_remarks ?? '',
                            'report_type' => $amendment->report_type ?? '',
                            'createdDTM' => date('Y-m-d H:i:s'),

                        ];

                        if (!empty($amendment->id)) {
                            // Update existing record
                            if (!$bmTasksModel->updateReport_amendments($amendment->id, $report_amendment)) {
                                log_message('error', 'Failed to update report amendment: ' . $amendment->id);
                            }
                        } else {
                            // Insert new record
                            if (!$bmTasksModel->insertReport_amendments($report_amendment)) {
                                log_message('error', 'Failed to insert report amendment');
                            }
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Report amendment error: ' . $e->getMessage());
                    }
                }
            }

            // same repeat for tables - repeat_punctures, repeat_samples, repeat_scan, report_escalation, sample_rejections
            if (isset($jsonData->repeat_punctures) && is_array($jsonData->repeat_punctures)) {
                foreach ($jsonData->repeat_punctures as $puncture) {
                    try {
                        $repeat_puncture = [
                            'task_id' => $id, // Use the main task ID
                            'regd_number' => $puncture->regd_number ?? '',
                            'regd_remarks' => $puncture->regd_remarks ?? '',
                            'report_type' => $puncture->report_type ?? '',
                            'createdDTM' => date('Y-m-d H:i:s'),

                        ];

                        if (!empty($puncture->id)) {
                            // Update existing record
                            if (!$bmTasksModel->updateRepeatPunctures($puncture->id, $repeat_puncture)) {
                                log_message('error', 'Failed to update repeat puncture: ' . $puncture->id);
                            }
                        } else {
                            // Insert new record
                            if (!$bmTasksModel->insertRepeatPunctures($repeat_puncture)) {
                                log_message('error', 'Failed to insert repeat puncture');
                            }
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Repeat puncture error: ' . $e->getMessage());
                    }
                }
            }

            if (isset($jsonData->repeat_samples) && is_array($jsonData->repeat_samples)) {
                foreach ($jsonData->repeat_samples as $sample) {
                    try {
                        $repeat_sample = [
                            'task_id' => $id, // Use the main task ID
                            'regd_number' => $sample->regd_number ?? '',
                            'regd_remarks' => $sample->regd_remarks ?? '',
                            'report_type' => $sample->report_type ?? '',
                            'createdDTM' => date('Y-m-d H:i:s'),

                        ];

                        if (!empty($sample->id)) {
                            // Update existing record
                            if (!$bmTasksModel->updateRepeatSamples($sample->id, $repeat_sample)) {
                                log_message('error', 'Failed to update repeat sample: ' . $sample->id);
                            }
                        } else {
                            // Insert new record
                            if (!$bmTasksModel->insertRepeatSamples($repeat_sample)) {
                                log_message('error', 'Failed to insert repeat sample');
                            }
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Repeat sample error: ' . $e->getMessage());
                    }
                }
            }

            if (isset($jsonData->report_escalation) && is_array($jsonData->report_escalation)) {
                foreach ($jsonData->report_escalation as $escalation) {
                    try {
                        $report_escalation = [
                            'task_id' => $id, // Use the main task ID
                            'regd_number' => $escalation->regd_number ?? '',
                            'regd_remarks' => $escalation->regd_remarks ?? '',
                            'report_type' => $escalation->report_type ?? '',
                            'createdDTM' => date('Y-m-d H:i:s'),

                        ];

                        if (!empty($escalation->id)) {
                            // Update existing record
                            if (!$bmTasksModel->updateReportEscalation($escalation->id, $report_escalation)) {
                                log_message('error', 'Failed to update report escalation: ' . $escalation->id);
                            }
                        } else {
                            // Insert new record
                            if (!$bmTasksModel->insertReportEscalation($report_escalation)) {
                                log_message('error', 'Failed to insert report escalation');
                            }
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Report escalation error: ' . $e->getMessage());
                    }
                }
            }

            if (isset($jsonData->sample_rejections) && is_array($jsonData->sample_rejections)) {
                foreach ($jsonData->sample_rejections as $rejection) {
                    try {
                        $sample_rejection = [
                            'task_id' => $id, // Use the main task ID
                            'regd_number' => $rejection->regd_number ?? '',
                            'regd_remarks' => $rejection->regd_remarks ?? '',
                            'report_type' => $rejection->report_type ?? '',
                            'createdDTM' => date('Y-m-d H:i:s'),

                        ];

                        if (!empty($rejection->id)) {
                            // Update existing record
                            if (!$bmTasksModel->updateSampleRejections($rejection->id, $sample_rejection)) {
                                log_message('error', 'Failed to update sample rejection: ' . $rejection->id);
                            }
                        } else {
                            // Insert new record
                            if (!$bmTasksModel->insertSampleRejections($sample_rejection)) {
                                log_message('error', 'Failed to insert sample rejection');
                            }
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Sample rejection error: ' . $e->getMessage());
                    }
                }
            }
            //repeat_scan
            if (isset($jsonData->repeat_scan) && is_array($jsonData->repeat_scan)) {
                foreach ($jsonData->repeat_scan as $scan) {
                    try {
                        $repeat_scan = [
                            'task_id' => $id, // Use the main task ID
                            'regd_number' => $scan->regd_number ?? '',
                            'regd_remarks' => $scan->regd_remarks ?? '',
                            'report_type' => $scan->report_type ?? '',
                            'createdDTM' => date('Y-m-d H:i:s'),

                        ];

                        if (!empty($scan->id)) {
                            // Update existing record
                            if (!$bmTasksModel->updateRepeatScan($scan->id, $repeat_scan)) {
                                log_message('error', 'Failed to update repeat scan: ' . $scan->id);
                            }
                        } else {
                            // Insert new record
                            if (!$bmTasksModel->insertRepeatScan($repeat_scan)) {
                                log_message('error', 'Failed to insert repeat scan');
                            }
                        }
                    } catch (\Exception $e) {
                        log_message('error', 'Repeat scan error: ' . $e->getMessage());
                    }
                }
            }


            return $this->respond([
                'status' => true,
                'message' => 'BM Task updated successfully.',
                'data' => $id
            ], 200);
        }

        return $this->respond(['status' => false, 'message' => 'Invalid branch ID'], 400);
    }



    //getBM_TaskDetails

    public function getBM_TaskDetails($id)
    {
        $bmTasksModel = new BM_TasksModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;

        //get id from url
        if ($id) {
            // Fetch the task details using the model
            $taskDetails = $bmTasksModel->getBM_TaskDetails($id);
            if ($taskDetails) {
                return $this->respond(['status' => true, 'message' => 'Task details fetched successfully.', 'data' => $taskDetails], 200);
            } else {
                return $this->respond(['status' => false, 'message' => 'No task found with the given ID'], 404);
            }
        } else {
            return $this->respond(['status' => false, 'message' => 'Invalid ID'], 400);
        }
    }

    //getBM_TaskList
    public function getBM_TaskList()
    {
        $bmTasksModel = new BM_TasksModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $emp_code = $userDetails->emp_code;

        // Fetch the task list using the model
        $taskList = $bmTasksModel->getBM_TaskList($emp_code, $role);
        return $this->respond([
            'status' => true,
            'message' => $taskList ? 'Task list fetched successfully.' : 'No tasks found',
            'data' => $taskList ?: []
        ], 200);
    }

    public function getBM_TaskDetailsByMid($mid)
    {
        $bmTasksModel = new BM_TasksModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if midSelected is set in the request data
        if ($mid === null) {
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameter: mid '
            ], 400);
        }

        // Pass the parameters to the model
        $mtDetails = $bmTasksModel->getBM_TaskDetailsByMid($mid, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'BM Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'BM Task Details not found'], 404);
        }
    }

    public function getBranchComboTaskListNew()
    {
        $bmTasksModel = new BM_TasksModel();
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
        $mtDetails = $bmTasksModel->getBranchComboTaskListNew($role, $user, $selectedMonth);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'BM Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'BM Task Details not found'], 404);
        }
    }

    public function getBM_TaskListForAdmin()
    {
        // First validate authorization
        $userDetails = $this->validateAuthorization();
        if (!$userDetails) {
            return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
        }

        $bmTasksModel = new BM_TasksModel();
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
            $selectedBranch = [$requestData->selectedBranch];
        } else {
            $selectedBranch = [];  // Empty array to fetch all branches
        }

        if (isset($requestData->selectedCluster) && $requestData->selectedCluster > 0) {
            $selectedCluster = $requestData->selectedCluster;
        } else {
            $selectedCluster = '0';
        }

        // Pass the parameters to the model
        $mtDetails = $bmTasksModel->getBM_TaskListForAdmin($role, $user, $selectedMonth, $selectedBranch, $selectedCluster);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'BM Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'BM Task Details not found'], 404);
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

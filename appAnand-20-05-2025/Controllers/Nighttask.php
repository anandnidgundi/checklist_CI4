<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\DeptModel;
use App\Models\MtModel;
use App\Models\NightModel;
use App\Models\DocModel;
 

use App\Services\JwtService;

class Nighttask extends BaseController {
    use ResponseTrait;

    public function index(): string {
        return view( 'welcome_message' );
    }

    public function addNightTask() {

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

            'nt0100' => $jsonData->nt0100 ?? null,
            'nt0101' => $jsonData->nt0101 ?? null,
            'nt0200' => $jsonData->nt0200 ?? null,
            'nt0201' => $jsonData->nt0201 ?? null,
            'nt0300' => $jsonData->nt0300 ?? null,
            'nt0301' => $jsonData->nt0301 ?? null,
            'nt0400' => $jsonData->nt0400 ?? null,
            'nt0401' => $jsonData->nt0401 ?? null,
            'nt0500' => $jsonData->nt0500 ?? null,
            'nt0501' => $jsonData->nt0501 ?? null,
            'nt0600' => $jsonData->nt0600 ?? null,
            'nt0601' => $jsonData->nt0601 ?? null,

            'nt0700' => $jsonData->nt0700 ?? null,
            'nt0701' => $jsonData->nt0701 ?? null,

            'nt0800' => $jsonData->nt0800 ?? null,
            'nt0801' => $jsonData->nt0801 ?? null,

            'nt0900' => $jsonData->nt0900 ?? null,
            'nt0901' => $jsonData->nt0901 ?? null,

            'nt1000' => $jsonData->nt1000 ?? null,
            'nt1001' => $jsonData->nt1001 ?? null,

            'nt1100' => $jsonData->nt1100 ?? null,
            'nt1101' => $jsonData->nt1101 ?? null,

            'nt1200' => $jsonData->nt1200 ?? null,
            'nt1201' => $jsonData->nt1201 ?? null,

            'nt1300' => $jsonData->nt1300 ?? null,
            'nt1301' => $jsonData->nt1301 ?? null,

            'nt1400' => $jsonData->nt1400 ?? null,
            'nt1401' => $jsonData->nt1401 ?? null,

            'nt1500' => $jsonData->nt1500 ?? null,
            'nt1501' => $jsonData->nt1501 ?? null,

            'nt1600' => $jsonData->nt1600 ?? null,
            'nt1601' => $jsonData->nt1601 ?? null,

            'nt1700' => $jsonData->nt1700 ?? null,
            'nt1701' => $jsonData->nt1701 ?? null,

            'nt1800' => $jsonData->nt1800 ?? null,
            'nt1801' => $jsonData->nt1801 ?? null,

            'nt1900' => $jsonData->nt1900 ?? null,
            'nt1901' => $jsonData->nt1901 ?? null,

            'nt2000' => $jsonData->nt2000 ?? null,
            'nt2001' => $jsonData->nt2001 ?? null,

            'nt2100' => $jsonData->nt2100 ?? null,
            'nt2101' => $jsonData->nt2101 ?? null,

            'nt2200' => $jsonData->nt2200 ?? null,
            'nt2201' => $jsonData->nt2201 ?? null,

            'nt2300' => $jsonData->nt2300 ?? null,
            'nt2301' => $jsonData->nt2301 ?? null,

            'nt2400' => $jsonData->nt2400 ?? null,
            'nt2401' => $jsonData->nt2401 ?? null,

            'nt2500' => $jsonData->nt2500 ?? null,
            'nt2501' => $jsonData->nt2501 ?? null,

            'nt2600' => $jsonData->nt2600 ?? null,
            'nt2601' => $jsonData->nt2601 ?? null,

            'nt2700' => $jsonData->nt2700 ?? null,
            'nt2701' => $jsonData->nt2701 ?? null,

            'nt2800' => $jsonData->nt2800 ?? null,
            'nt2801' => $jsonData->nt2801 ?? null,

            'nt2900' => $jsonData->nt2900 ?? null,
            'nt2901' => $jsonData->nt2901 ?? null,

            'nt3000' => $jsonData->nt3000 ?? null,
            'nt3001' => $jsonData->nt3001 ?? null,

            'nt3100' => $jsonData->nt3100 ?? null,
            'nt3101' => $jsonData->nt3101 ?? null,

            'nt3200' => $jsonData->nt3200 ?? null,
            'nt3201' => $jsonData->nt3201 ?? null,

            'nt3300' => $jsonData->nt3300 ?? null,
            'nt3301' => $jsonData->nt3301 ?? null,

            'nt3400' => $jsonData->nt3400 ?? null,
            'nt3401' => $jsonData->nt3401 ?? null,

            'nt3500' => $jsonData->nt3500 ?? null,
            'nt3501' => $jsonData->nt3501 ?? null,

            'nt3600' => $jsonData->nt3600 ?? null,
            'nt3601' => $jsonData->nt3601 ?? null,

            'nt3700' => $jsonData->nt3700 ?? null,
            'nt3701' => $jsonData->nt3701 ?? null,

            'nt3800' => $jsonData->nt3800 ?? null,
            'nt3801' => $jsonData->nt3801 ?? null,

            'nt3900' => $jsonData->nt3900 ?? null,
            'nt3901' => $jsonData->nt3901 ?? null,

            'nt4000' => $jsonData->nt4000 ?? null,
            'nt4001' => $jsonData->nt4001 ?? null,

            'nt4100' => $jsonData->nt4100 ?? null,
            'nt4101' => $jsonData->nt4101 ?? null,

            'branch' => $branch,
            'emp_code' => $emp_code,
            'createdDTM' => $createdDTM,
            'taskDate' => $taskDate,
            'created_by' => $emp_code,
        ];
 
        // Check if the branch is valid
        if ($branch > 0) {
            // Initialize the model
            $nightModel = new NightModel();
            $existedRecord = $nightModel->checkExistingRecord($taskDate, $branch);
            if ($existedRecord) {
                return $this->respond(['status' => true, 'message' => 'Record already exists.', 'data' => $existedRecord], 200);
            }
            // Add the morning task using the model
            $mt = $nightModel->addNightTask($data, $taskDate, $branch);

            if ($mt) {
                return $this->respond(['status' => true, 'message' => 'Night Task added successfully.', 'data' => $mt], 200);
            } else {
                return $this->respond(['status' => false, 'message' => 'Failed to add Night Task'], 500);
            }
        }

        // If branch is not valid, return an error
        return $this->respond(['status' => false, 'message' => 'Invalid branch ID'], 400);
    }

    public function getDocData(){
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code; 

        $requestData = $this->request->getJSON();
        $nid = $requestData->nid;

        $docModel = new DocModel();
        $docData = $docModel->getDocData($nid);
        if ($docData) {
            return $this->respond(['status' => true, 'message' => 'Doctor  Data.', 'data' => $docData], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Doctor Data not found'], 404);
        }
        
    }

    // public function getMriData(){
    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code; 

    //     $requestData = $this->request->getJSON();
    //     $nid = $requestData->nid;

    //     $mriModel = new MriModel();
    //     $mriData = $mriModel->getMriData($nid);
    //     if ($mriData) {
    //         return $this->respond(['status' => true, 'message' => 'MRI Data.', 'data' => $mriData], 200);
    //     } else {
    //         return $this->respond(['status' => false, 'message' => 'MRI Data not found'], 404);
    //     }
        
    // }

    // public function getXrayData(){
    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code; 

    //     $requestData = $this->request->getJSON();
    //     $nid = $requestData->nid;

    //     $xrayModel = new XrayModel();
    //     $xrayData = $xrayModel->getXrayData($nid);
    //     if ($xrayData) {
    //         return $this->respond(['status' => true, 'message' => 'Xray Data.', 'data' => $xrayData], 200);
    //     } else {
    //         return $this->respond(['status' => false, 'message' => 'Xray Data not found'], 404);
    //     }
    // }

    // public function getUsgData(){
    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code; 

    //     $requestData = $this->request->getJSON();
    //     $nid = $requestData->nid;

    //     $usgModel = new UsgModel();
    //     $usgData = $usgModel->getUsgData($nid);
    //     if ($usgData) {
    //         return $this->respond(['status' => true, 'message' => 'USG Data.', 'data' => $usgData], 200);
    //     } else {
    //         return $this->respond(['status' => false, 'message' => 'USG Data not found'], 404);
    //     }
    // }

    // public function getCtData(){
    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code; 

    //     $requestData = $this->request->getJSON();
    //     $nid = $requestData->nid;

    //     $ctModel = new CtModel();
    //     $ctData = $ctModel->getCtData($nid);
    //     if ($ctData) {
    //         return $this->respond(['status' => true, 'message' => 'CT Data.', 'data' => $ctData], 200);
    //     } else {
    //         return $this->respond(['status' => false, 'message' => 'CT Data not found'], 404);
    //     }
    // }
    // public function getCardioTmtData() {
    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code;

    //     $requestData = $this->request->getJSON();
    //     $nid = $requestData->nid;

    //     $cardioTmtModel = new CardioTmtModel();
    //     $cardioTmtData = $cardioTmtModel->getCardioTmtData($nid);
    //     if ($cardioTmtData) {
    //         return $this->respond(['status' => true, 'message' => 'Cardio TMT Data.', 'data' => $cardioTmtData], 200);
    //     } else {
    //         return $this->respond(['status' => false, 'message' => 'Cardio TMT Data not found'], 404);
    //     }
    // }

    // public function getCardioEcgData() {
    //     $userDetails = $this->validateAuthorization();
    //     $role = $userDetails->role;
    //     $user = $userDetails->emp_code;

    //     $requestData = $this->request->getJSON();
    //     $nid = $requestData->nid;

    //     $cardioEcgModel = new CardioEcgModel();
    //     $cardioEcgData = $cardioEcgModel->getCardioEcgData($nid);
    //     if ($cardioEcgData) {
    //         return $this->respond(['status' => true, 'message' => 'Cardio ECG Data.', 'data' => $cardioEcgData], 200);
    //     } else {
    //         return $this->respond(['status' => false, 'message' => 'Cardio ECG Data not found'], 404);
    //     }
    // }



public function getBranchNightTaskList(){

    $nightModel = new NightModel();
    
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
      $mtDetails = $nightModel->getBranchNightTaskList($role, $user, $selectedMonth);

      if ($mtDetails) {
          return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
      } else {
          return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
      }

}



    public function getNightTaskDetails(){
        $nightModel = new NightModel();
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
        $mtDetails = $nightModel->getNightTaskDetails($selectedBranch, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
        }

    }

    public function getNightTaskDetailsNew(){
        $nightModel = new NightModel();
        $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();

        // Check if selectedBranch and selectedDate are set in the request data
        if (isset($requestData->nid)) {
            $nid = $requestData->nid;            
        } else {
            // Handle the case where the expected data is missing
            return $this->respond([
                'status' => false,
                'message' => 'Missing required parameters: Nid.'
            ], 400);
        }
        // Pass the parameters to the model
        $mtDetails = $nightModel->getNightTaskDetailsNew($nid, $role, $user);
        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Night Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Night Task Details not found'], 404);
        }

    }

    public function saveMriData()
    {
        $MriModel = new \App\Models\MriModel();

        // Sample data
        $data = [
            'doctor_name'  => $this->request->getPost('doctor_name'),
            'nid'          => $this->request->getPost('nid'),
            'doctor_count' => $this->request->getPost('doctor_count'),
            'bmid'         => $this->request->getPost('bmid'),
        ];

        if ($MriModel->save($data)) {
            return $this->respond(['status' => true, 'message' => 'Data saved successfully.']);
        } else {
            return $this->respond(['status' => false, 'message' => $MriModel->errors()]);
        }
 }


 public function saveNightTaskDetails()
 {
     $nightModel = new NightModel();     
     $docModel = new DocModel();   

     $userDetails = $this->validateAuthorization();
        $role = $userDetails->role;
        $user = $userDetails->emp_code;

     // Retrieve JSON data from the POST request
     try {
         $jsonData = $this->request->getJSON();
         

     } catch (\Exception $e) {
         $jsonData = $this->request->getJSON();
         //log_message('error', 'Received JSON data: ' . json_encode($jsonData));

         //log_message('error', 'Failed to parse JSON string: ' . $e->getMessage());
         return $this->respond([
             'status' => false,
             'message' => 'Invalid JSON data'
         ], 400);
     }

     // Log received JSON data for debugging
      // Log received JSON data for debugging

     // Extract mid and data fields
     $nid = $jsonData->nid ?? null;
    // $mri = $jsonData->rows ?? null;
      
     // Save MRI data if present
     if (!empty($jsonData->rows) && is_array($jsonData->rows)) {
         foreach ($jsonData->rows as $docData) {
            $docRecord = [
             'doctor_name' => $docData->doctor_name ?? null,
             'nid' => $nid ?? null,
             'mri' => $docData->mri ?? null,
             'ct' => $docData->ct ?? null,
             'xray' => $docData->xray ?? null,
             'cardio_ecg' => $docData->cardio_ecg ?? null,
             'cardio_tmt' => $docData->cardio_tmt ?? null,
             'bmid' => $docData->bmid ?? null,
             'createdDTM' => date('Y-m-d H:i:s'),
             'createdBy' => $user,
         ];

         if (isset($docData->id)) {
             // Update existing record
             $docModel->update($docData->id, $docRecord);
             //log_message('error', 'Updated MRI Data: ' . json_encode($mriRecord));
         } else {
             // Insert new record
             $docModel->insert($docRecord);
             //log_message('error', 'Inserted MRI Data: ' . json_encode($mriRecord));
         }
         }
     }     
     
     if (!$nid) {
         //log_message('error', 'Missing nid in request data.');
         return $this->respond([
             'status' => false,
             'message' => 'nid is required'
         ], 400);
     }
     $data = [
         'nt0101' => $jsonData->nt0101 ?? null,'nt0102' => $jsonData->nt0102 ?? null,'nt1502' => $jsonData->nt1502 ?? null,'nt1602' => $jsonData->nt1602 ?? null,
         'nt0100' => $jsonData->nt0100 ?? null, 'nt0200' => $jsonData->nt0200 ?? null,  'nt0201' => $jsonData->nt0201 ?? null,
         'nt0300' => $jsonData->nt0300 ?? null, 'nt0301' => $jsonData->nt0301 ?? null,  'nt0400' => $jsonData->nt0400 ?? null,
         'nt0401' => $jsonData->nt0401 ?? null, 'nt0500' => $jsonData->nt0500 ?? null,   'nt0501' => $jsonData->nt0501 ?? null,
         'nt0600' => $jsonData->nt0600 ?? null, 'nt0601' => $jsonData->nt0601 ?? null,  'nt0700' => $jsonData->nt0700 ?? null,
         'nt0701' => $jsonData->nt0701 ?? null, 'nt0800' => $jsonData->nt0800 ?? null, 'nt0801' => $jsonData->nt0801 ?? null,
         'nt0900' => $jsonData->nt0900 ?? null, 'nt0901' => $jsonData->nt0901 ?? null,  'nt1000' => $jsonData->nt1000 ?? null,
         'nt1001' => $jsonData->nt1001 ?? null, 'nt1100' => $jsonData->nt1100 ?? null, 'nt1101' => $jsonData->nt1101 ?? null,
         'nt1200' => $jsonData->nt1200 ?? null,  'nt1201' => $jsonData->nt1201 ?? null, 'nt1300' => $jsonData->nt1300 ?? null,
         'nt1301' => $jsonData->nt1301 ?? null,  'nt1400' => $jsonData->nt1400 ?? null,  'nt1401' => $jsonData->nt1401 ?? null,
         'nt1500' => $jsonData->nt1500 ?? null, 'nt1501' => $jsonData->nt1501 ?? null,   'nt1600' => $jsonData->nt1600 ?? null,
         'nt1601' => $jsonData->nt1601 ?? null, 'nt1700' => $jsonData->nt1700 ?? null,  'nt1701' => $jsonData->nt1701 ?? null,
         'nt1800' => $jsonData->nt1800 ?? null, 'nt1801' => $jsonData->nt1801 ?? null, 'nt1900' => $jsonData->nt1900 ?? null,
         'nt1901' => $jsonData->nt1901 ?? null, 'nt2000' => $jsonData->nt2000 ?? null, 'nt2001' => $jsonData->nt2001 ?? null,
         'nt2100' => $jsonData->nt2100 ?? null,  'nt2101' => $jsonData->nt2101 ?? null,  'nt2200' => $jsonData->nt2200 ?? null,
         'nt2201' => $jsonData->nt2201 ?? null, 'nt2300' => $jsonData->nt2300 ?? null,   'nt2301' => $jsonData->nt2301 ?? null,
         'nt2400' => $jsonData->nt2400 ?? null, 'nt2401' => $jsonData->nt2401 ?? null, 'nt2500' => $jsonData->nt2500 ?? null,
         'nt2501' => $jsonData->nt2501 ?? null, 'nt2600' => $jsonData->nt2600 ?? null, 'nt2601' => $jsonData->nt2601 ?? null,
         'nt2700' => $jsonData->nt2700 ?? null, 'nt2701' => $jsonData->nt2701 ?? null, 'nt2800' => $jsonData->nt2800 ?? null,
         'nt2801' => $jsonData->nt2801 ?? null, 'nt2900' => $jsonData->nt2900 ?? null, 'nt2901' => $jsonData->nt2901 ?? null,
         'nt3000' => $jsonData->nt3000 ?? null,   'nt3001' => $jsonData->nt3001 ?? null, 'nt3100' => $jsonData->nt3100 ?? null,
         'nt3101' => $jsonData->nt3101 ?? null,  'nt3200' => $jsonData->nt3200 ?? null, 'nt3201' => $jsonData->nt3201 ?? null,
         'nt3300' => $jsonData->nt3300 ?? null,  'nt3301' => $jsonData->nt3301 ?? null, 'nt3400' => $jsonData->nt3400 ?? null,
         'nt3401' => $jsonData->nt3401 ?? null, 'nt3500' => $jsonData->nt3500 ?? null,  'nt3501' => $jsonData->nt3501 ?? null,
         'nt3600' => $jsonData->nt3600 ?? null,  'nt3601' => $jsonData->nt3601 ?? null, 'nt3700' => $jsonData->nt3700 ?? null,
         'nt3701' => $jsonData->nt3701 ?? null,  'nt3800' => $jsonData->nt3800 ?? null, 'nt3801' => $jsonData->nt3801 ?? null,
         'nt3900' => $jsonData->nt3900 ?? null,  'nt3901' => $jsonData->nt3901 ?? null,  'nt4000' => $jsonData->nt4000 ?? null,
         'nt4001' => $jsonData->nt4001 ?? null, 'nt4100' => $jsonData->nt4100 ?? null,  'nt4101' => $jsonData->nt4101 ?? null,
         'modifiedDTM' => date('Y-m-d H:i:s'),
     ];

     // Filter out null values to avoid an empty `$data` array
     $filteredData = array_filter($data, fn($value) => !is_null($value));

     // Check if `$filteredData` is empty after filtering
     if (empty($filteredData)) {
         //log_message('error', 'No valid data provided in request.');
         return $this->respond([
             'status' => false,
             'message' => 'No valid data provided for update'
         ], 400);
     }
         // Proceed with update if data is valid
     $updateResult = $nightModel->editNightTask($filteredData, $nid);
     
     if ($updateResult) {
         //log_message('error', 'Updated Night Task Data: ' . $updateResult);           
     } else {
         //log_message('error', 'Updated Night Task Data: ' . $updateResult);            
     }

     return $this->respond([
         'status' => true,
         'message' => 'Night Task updated successfully.'
     ], 200);
    }

    // public function saveNightTaskDetails()
    // {
    //     $nightModel = new NightModel();
    //     $cardioEcgModel = new CardioEcgModel();
    //     $cardioTmtModel = new CardioTmtModel();
    //     $mriModel = new MriModel();
    //     $ctModel = new CtModel();
    //     $usgModel = new UsgModel();
    //     $xrayModel = new XrayModel();

    //     $userDetails = $this->validateAuthorization();

    //     // Retrieve JSON data from the POST request
    //     try {
    //         $jsonData = $this->request->getJSON();
            

    //     } catch (\Exception $e) {
    //         $jsonData = $this->request->getJSON();
    //         //log_message('error', 'Received JSON data: ' . json_encode($jsonData));

    //         //log_message('error', 'Failed to parse JSON string: ' . $e->getMessage());
    //         return $this->respond([
    //             'status' => false,
    //             'message' => 'Invalid JSON data'
    //         ], 400);
    //     }

    //     // Log received JSON data for debugging
    //      // Log received JSON data for debugging
   
    //     // Extract mid and data fields
    //     $nid = $jsonData->nid ?? null;
    //     $mri = $jsonData->rows ?? null;
    //     $xray = $jsonData->rows1 ?? null;
    //     $ct = $jsonData->rows2 ?? null;
    //     $usg = $jsonData->rows3 ?? null;
    //     $cardioTmt = $jsonData->rows4 ?? null; 
    //     $cardioEcg = $jsonData->rows5 ?? null;

    //     // Save MRI data if present
    //     if (!empty($jsonData->rows) && is_array($jsonData->rows)) {
    //         foreach ($jsonData->rows as $mriData) {
    //         $mriRecord = [
    //             'doctor_name' => $mriData->doctor_name ?? null,
    //             'nid' => $nid ?? null,
    //             'doctor_count' => $mriData->doctor_count ?? null,
    //             'bmid' => $mriData->bmid ?? null,
    //         ];

    //         if (isset($mriData->id)) {
    //             // Update existing record
    //             $mriModel->update($mriData->id, $mriRecord);
    //             //log_message('error', 'Updated MRI Data: ' . json_encode($mriRecord));
    //         } else {
    //             // Insert new record
    //             $mriModel->insert($mriRecord);
    //             //log_message('error', 'Inserted MRI Data: ' . json_encode($mriRecord));
    //         }
    //         }
    //     }
         
    //     if (!empty($jsonData->rows1) && is_array($jsonData->rows1)) {
    //         foreach ($jsonData->rows1 as $xrayData) {
    //         $xrayRecord = [
    //             'doctor_name' => $xrayData->doctor_name ?? null,
    //             'nid' => $nid ?? null,
    //             'doctor_count' => $xrayData->doctor_count ?? null,
    //             'bmid' => $xrayData->bmid ?? null,
    //         ];

    //         if (isset($xrayData->id)) {
    //             // Update existing record
    //             $xrayModel->update($xrayData->id, $xrayRecord);
    //             //log_message('error', 'Updated Xray Data: ' . json_encode($xrayRecord));
    //         } else {
    //             // Insert new record
    //             $xrayModel->insert($xrayRecord);
    //             //log_message('error', 'Inserted Xray Data: ' . json_encode($xrayRecord));
    //         }
    //         }
    //     }
        
    //     if (!empty($jsonData->rows2) && is_array($jsonData->rows2)) {
    //         foreach ($jsonData->rows2 as $ctData) {
    //         $ctRecord = [
    //             'doctor_name' => $ctData->doctor_name ?? null,
    //             'nid' => $nid ?? null,
    //             'doctor_count' => $ctData->doctor_count ?? null,
    //             'bmid' => $ctData->bmid ?? null,
    //         ];

    //         if (isset($ctData->id)) {
    //             // Update existing record
    //             $ctModel->update($ctData->id, $ctRecord);
    //             //log_message('error', 'Updated CT Data: ' . json_encode($ctRecord));
    //         } else {
    //             // Insert new record
    //             $ctModel->insert($ctRecord);
    //             //log_message('error', 'Inserted CT Data: ' . json_encode($ctRecord));
    //         }
    //         }
    //     }
         
    //     if (!empty($jsonData->rows3) && is_array($jsonData->rows3)) {
    //         foreach ($jsonData->rows3 as $usgData) {
    //         $usgRecord = [
    //             'doctor_name' => $usgData->doctor_name ?? null,
    //             'nid' => $nid ?? null,
    //             'doctor_count' => $usgData->doctor_count ?? null,
    //             'bmid' => $usgData->bmid ?? null,
    //         ];

    //         if (isset($usgData->id)) {
    //             // Update existing record
    //             $usgModel->update($usgData->id, $usgRecord);
    //             //log_message('error', 'Updated USG Data: ' . json_encode($usgRecord));
    //         } else {
    //             // Insert new record
    //             $usgModel->insert($usgRecord);
    //             //log_message('error', 'Inserted USG Data: ' . json_encode($usgRecord));
    //         }
    //         }
    //     }
         
    //     if (!empty($jsonData->rows4) && is_array($jsonData->rows4)) {
    //         foreach ($jsonData->rows4 as $cardioTmtData) {
    //             $cardioTmtRecord = [
    //                 'doctor_name' => $cardioTmtData->doctor_name ?? null,
    //                 'nid' => $nid ?? null,
    //                 'doctor_count' => $cardioTmtData->doctor_count ?? null,
    //                 'bmid' => $cardioTmtData->bmid ?? null,
    //             ];

    //             if (isset($cardioTmtData->id)) {
    //                 // Update existing record
    //                 $cardioTmtModel->update($cardioTmtData->id, $cardioTmtRecord);
    //                 //log_message('error', 'Updated Cardio TMT Data: ' . json_encode($cardioTmtRecord));
    //             } else {
    //                 // Insert new record
    //                 $cardioTmtModel->insert($cardioTmtRecord);
    //                 //log_message('error', 'Inserted Cardio TMT Data: ' . json_encode($cardioTmtRecord));
    //             }
    //         }
    //     }

    //     if (!empty($jsonData->rows5) && is_array($jsonData->rows5)) {
    //         foreach ($jsonData->rows5 as $cardioEcgData) {
    //         $cardioEcgRecord = [
    //             'doctor_name' => $cardioEcgData->doctor_name ?? null,
    //             'nid' => $nid ?? null,
    //             'doctor_count' => $cardioEcgData->doctor_count ?? null,
    //             'bmid' => $cardioEcgData->bmid ?? null,
    //         ];

    //         if (isset($cardioEcgData->id)) {
    //             // Update existing record
    //             $cardioEcgModel->update($cardioEcgData->id, $cardioEcgRecord);
    //             //log_message('error', 'Updated Cardio ECG Data: ' . json_encode($cardioEcgRecord));
    //         } else {
    //             // Insert new record
    //             $cardioEcgModel->insert($cardioEcgRecord);
    //             //log_message('error', 'Inserted Cardio ECG Data: ' . json_encode($cardioEcgRecord));
    //         }
    //         }
    //     }
        
    //     if (!$nid) {
    //         //log_message('error', 'Missing nid in request data.');
    //         return $this->respond([
    //             'status' => false,
    //             'message' => 'nid is required'
    //         ], 400);
    //     }
    //     $data = [
    //         'nt0101' => $jsonData->nt0101 ?? null,'nt0102' => $jsonData->nt0102 ?? null,'nt1502' => $jsonData->nt1502 ?? null,'nt1602' => $jsonData->nt1602 ?? null,
    //         'nt0100' => $jsonData->nt0100 ?? null, 'nt0200' => $jsonData->nt0200 ?? null,  'nt0201' => $jsonData->nt0201 ?? null,
    //         'nt0300' => $jsonData->nt0300 ?? null, 'nt0301' => $jsonData->nt0301 ?? null,  'nt0400' => $jsonData->nt0400 ?? null,
    //         'nt0401' => $jsonData->nt0401 ?? null, 'nt0500' => $jsonData->nt0500 ?? null,   'nt0501' => $jsonData->nt0501 ?? null,
    //         'nt0600' => $jsonData->nt0600 ?? null, 'nt0601' => $jsonData->nt0601 ?? null,  'nt0700' => $jsonData->nt0700 ?? null,
    //         'nt0701' => $jsonData->nt0701 ?? null, 'nt0800' => $jsonData->nt0800 ?? null, 'nt0801' => $jsonData->nt0801 ?? null,
    //         'nt0900' => $jsonData->nt0900 ?? null, 'nt0901' => $jsonData->nt0901 ?? null,  'nt1000' => $jsonData->nt1000 ?? null,
    //         'nt1001' => $jsonData->nt1001 ?? null, 'nt1100' => $jsonData->nt1100 ?? null, 'nt1101' => $jsonData->nt1101 ?? null,
    //         'nt1200' => $jsonData->nt1200 ?? null,  'nt1201' => $jsonData->nt1201 ?? null, 'nt1300' => $jsonData->nt1300 ?? null,
    //         'nt1301' => $jsonData->nt1301 ?? null,  'nt1400' => $jsonData->nt1400 ?? null,  'nt1401' => $jsonData->nt1401 ?? null,
    //         'nt1500' => $jsonData->nt1500 ?? null, 'nt1501' => $jsonData->nt1501 ?? null,   'nt1600' => $jsonData->nt1600 ?? null,
    //         'nt1601' => $jsonData->nt1601 ?? null, 'nt1700' => $jsonData->nt1700 ?? null,  'nt1701' => $jsonData->nt1701 ?? null,
    //         'nt1800' => $jsonData->nt1800 ?? null, 'nt1801' => $jsonData->nt1801 ?? null, 'nt1900' => $jsonData->nt1900 ?? null,
    //         'nt1901' => $jsonData->nt1901 ?? null, 'nt2000' => $jsonData->nt2000 ?? null, 'nt2001' => $jsonData->nt2001 ?? null,
    //         'nt2100' => $jsonData->nt2100 ?? null,  'nt2101' => $jsonData->nt2101 ?? null,  'nt2200' => $jsonData->nt2200 ?? null,
    //         'nt2201' => $jsonData->nt2201 ?? null, 'nt2300' => $jsonData->nt2300 ?? null,   'nt2301' => $jsonData->nt2301 ?? null,
    //         'nt2400' => $jsonData->nt2400 ?? null, 'nt2401' => $jsonData->nt2401 ?? null, 'nt2500' => $jsonData->nt2500 ?? null,
    //         'nt2501' => $jsonData->nt2501 ?? null, 'nt2600' => $jsonData->nt2600 ?? null, 'nt2601' => $jsonData->nt2601 ?? null,
    //         'nt2700' => $jsonData->nt2700 ?? null, 'nt2701' => $jsonData->nt2701 ?? null, 'nt2800' => $jsonData->nt2800 ?? null,
    //         'nt2801' => $jsonData->nt2801 ?? null, 'nt2900' => $jsonData->nt2900 ?? null, 'nt2901' => $jsonData->nt2901 ?? null,
    //         'nt3000' => $jsonData->nt3000 ?? null,   'nt3001' => $jsonData->nt3001 ?? null, 'nt3100' => $jsonData->nt3100 ?? null,
    //         'nt3101' => $jsonData->nt3101 ?? null,  'nt3200' => $jsonData->nt3200 ?? null, 'nt3201' => $jsonData->nt3201 ?? null,
    //         'nt3300' => $jsonData->nt3300 ?? null,  'nt3301' => $jsonData->nt3301 ?? null, 'nt3400' => $jsonData->nt3400 ?? null,
    //         'nt3401' => $jsonData->nt3401 ?? null, 'nt3500' => $jsonData->nt3500 ?? null,  'nt3501' => $jsonData->nt3501 ?? null,
    //         'nt3600' => $jsonData->nt3600 ?? null,  'nt3601' => $jsonData->nt3601 ?? null, 'nt3700' => $jsonData->nt3700 ?? null,
    //         'nt3701' => $jsonData->nt3701 ?? null,  'nt3800' => $jsonData->nt3800 ?? null, 'nt3801' => $jsonData->nt3801 ?? null,
    //         'nt3900' => $jsonData->nt3900 ?? null,  'nt3901' => $jsonData->nt3901 ?? null,  'nt4000' => $jsonData->nt4000 ?? null,
    //         'nt4001' => $jsonData->nt4001 ?? null, 'nt4100' => $jsonData->nt4100 ?? null,  'nt4101' => $jsonData->nt4101 ?? null,
    //         'modifiedDTM' => date('Y-m-d H:i:s'),
    //     ];

    //     // Filter out null values to avoid an empty `$data` array
    //     $filteredData = array_filter($data, fn($value) => !is_null($value));

    //     // Check if `$filteredData` is empty after filtering
    //     if (empty($filteredData)) {
    //         //log_message('error', 'No valid data provided in request.');
    //         return $this->respond([
    //             'status' => false,
    //             'message' => 'No valid data provided for update'
    //         ], 400);
    //     }
    //         // Proceed with update if data is valid
    //     $updateResult = $nightModel->editNightTask($filteredData, $nid);
        
    //     if ($updateResult) {
    //         //log_message('error', 'Updated Night Task Data: ' . $updateResult);           
    //     } else {
    //         //log_message('error', 'Updated Night Task Data: ' . $updateResult);            
    //     }

    //     return $this->respond([
    //         'status' => true,
    //         'message' => 'Night Task updated successfully.'
    //     ], 200);
    // }

    public function uploadedNightTlist(){

        $nightModel = new NightModel();
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
        $mtDetails = $nightModel->uploadedNightTlist($selectedBranch, $selectedDate, $role, $user);

        if ($mtDetails) {
            return $this->respond(['status' => true, 'message' => 'Morning Task Details.', 'data' => $mtDetails], 200);
        } else {
            return $this->respond(['status' => false, 'message' => 'Morning Task Details not found'], 404);
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
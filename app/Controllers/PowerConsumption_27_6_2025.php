<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PowerConsumptionModel;
use App\Models\UserModel;
use App\Models\FileModel;
use App\Models\PCModel;
use App\Models\PowerMeterModel;
use App\Models\DeptModel;
use App\Services\JwtService;
class PowerConsumption extends BaseController
{
     use ResponseTrait;
     public function __construct() {}
     //getPowerConsumptionList
     public function getPowerConsumptionList($month = null)
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          // Step 2: Read filters from JSON request body
          $jsonData = $this->request->getJSON(true); // true → returns assoc array
          $selectedMonth     = $jsonData['month'] ?? $month ?? date('Y-m');
          $selectedDate      = $jsonData['selectedDate'] ?? '';
          $selectedToDate    = $jsonData['selectedToDate'] ?? '';
          $selectedBranchRaw = $jsonData['branch_id'] ?? '0';
          $selectedBranch = (!empty($selectedBranchRaw) && $selectedBranchRaw != '0')
               ? (is_array($selectedBranchRaw) ? $selectedBranchRaw : [$selectedBranchRaw])
               : [];
          // Step 3: Fetch power consumption list
          $powerConsumptionModel = new PowerConsumptionModel();
          $powerConsumptionList = $powerConsumptionModel->getPowerConsumptionList(
               $role,
               $emp_code,
               $selectedBranch,
               $selectedMonth,
               $selectedDate,
               $selectedToDate
          );
          // Step 4: Return result
          if ($powerConsumptionList) {
               return $this->respond(['status' => 'success', 'data' => $powerConsumptionList], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
          }
     }
     public function getPowerConsumptionAdminList($month)
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $user = $userDetails->emp_code;
          $powerConsumptionModel = new PowerConsumptionModel();
          $deptModel = new DeptModel();
          // Get allowed branches for this user
          $allowedBranchIds = []; // ← ALWAYS DEFINE EARLY
          $clusters = $deptModel->getClustersWithBranches($user);
          foreach ($clusters as $cluster) {
               $branchIds = explode(',', $cluster['branches']);
               foreach ($branchIds as $bid) {
                    $bid = trim($bid);
                    if (!in_array($bid, $allowedBranchIds)) {
                         $allowedBranchIds[] = $bid;
                    }
               }
          }
          // log_message('error', 'Allowed Branch IDs: ' . implode(', ', $allowedBranchIds));
          // Now this logic is safe
          // Get POST JSON
          $requestData = $this->request->getJSON();
          $selectedMonth = $requestData->month ?? date('Y-m');
          $searchText = $requestData->search ?? '';
          $selectedBranch = (!empty($requestData->branch_id) && $requestData->branch_id != '0') ? [$requestData->branch_id] : [];
          $finalBranchFilter = (!empty($selectedBranch)) ? $selectedBranch : $allowedBranchIds;
          $selectedCluster = $requestData->cluster_id ?? '0';
          $selectedZone = $requestData->zone_id ?? '0';
          $selectedDate = $requestData->selectedDate ?? '';
          $selectedToDate = $requestData->selectedToDate ?? '';
          $powerConsumptionList = $powerConsumptionModel->getPowerConsumptionAdminList(
               $role,
               $user,
               $selectedZone,
               $selectedCluster,
               $finalBranchFilter,
               $selectedMonth,
               $selectedDate,
               $selectedToDate
          );
          if ($powerConsumptionList) {
               return $this->respond(['status' => 'success', 'data' => $powerConsumptionList], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
          }
     }
     public function getPowerConsumptionAdminListforbranch($month)
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          //fetching power consumption list
          $powerConsumptionModel = new PowerConsumptionModel();
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
          $zone_id = $this->request->getPost('zone_id') ?? ($jsonData['zone_id'] ?? $this->request->getVar('zone_id')) ?? '1';
          $selectedCluster = $this->request->getPost('cluster_id') ?? ($jsonData['cluster_id'] ?? $this->request->getVar('cluster_id')) ?? '2';
          $selectedBranch = $this->request->getPost('branch_id') ?? ($jsonData['branch_id'] ?? $this->request->getVar('branch_id')) ?? '0';
          $selectedMonth = $this->request->getPost('month') ?? ($jsonData['month'] ?? $this->request->getVar('month')) ?? '2025-05';
          // $selectedMonth = $month;
          $selectedDate = $this->request->getPost('selectedDate') ?? ($jsonData['selectedDate'] ?? $this->request->getVar('selectedDate'));
          $powerConsumptionList = $powerConsumptionModel->getPowerConsumptionAdminListforbranch($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate);
          if ($powerConsumptionList) {
               return $this->respond(['status' => 'success', 'data' => $powerConsumptionList], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
          }
     }
     //getPowerConsumptionById
     public function getPowerConsumptionById($id = null)
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          //fetching power consumption list
          $powerConsumptionModel = new PowerConsumptionModel();
          $powerConsumptionList = $powerConsumptionModel->getPowerConsumptionById($id);
          if ($powerConsumptionList) {
               return $this->respond(['status' => 'success', 'data' => $powerConsumptionList], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
          }
     }
     public function addPowerConsumptionNew()
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          $powerConsumptionModel = new PowerConsumptionModel();
          $branch_id = $this->request->getPost('branch_id') ?? $this->request->getVar('branch_id');
          $userModel = new UserModel();
          $result = $userModel->getclusterId($branch_id);
          $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
          if (!$branchDetails) {
               return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
          }
          $cluster_id = $result['cluster_id'];
          $zone_id = $branchDetails['zone'];
          // Validate the request body
          if (empty($branch_id) || empty($consumption_date)) {
               return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
          }
          $consumption_date = $this->request->getPost('consumption_date') ?? $this->request->getVar('consumption_date');
          $consumption_date = date('Y-m-d', strtotime($consumption_date));
          $pcModel = new PCModel();
     }
     //addPowerConsumption
     public function addPowerConsumption()
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          $branch_id = $this->request->getPost('branch_id') ?? $this->request->getVar('branch_id');
          $consumption_date = $this->request->getPost('consumption_date') ?? $this->request->getVar('consumption_date');
          $consumption_date = date('Y-m-d', strtotime($consumption_date));
          $morning_units = $this->request->getPost('morning_units') ?? $this->request->getVar('morning_units');
          $night_units = $this->request->getPost('night_units') ?? $this->request->getVar('night_units');
          $total_consumption = $this->request->getPost('total_consumption') ?? $this->request->getVar('total_consumption');
          $remarks = $this->request->getPost('remarks') ?? $this->request->getVar('remarks');
          $file = $this->request->getFile('file') ?? $this->request->getVar('file');
          $userModel = new UserModel();
          $result = $userModel->getclusterId($branch_id);
          $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
          if (!$branchDetails) {
               return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
          }
          $cluster_id = $result['cluster_id'];
          $zone_id = $branchDetails['zone'];
          // Validate the request body
          if (empty($branch_id) || empty($consumption_date)) {
               return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
          }
          // Create a new instance of PowerConsumptionModel
          $powerConsumptionModel = new PowerConsumptionModel();
          $prevDate = date('Y-m-d', strtotime($consumption_date . ' -1 day'));
          $yesterdayNightUnits = $powerConsumptionModel
               ->select('night_units')
               ->where('createdBy', $emp_code)
               ->where('branch_id', $branch_id)
               ->where('consumption_date', $prevDate)
               ->get()
               ->getRowArray();
          $prevNightUnits = $yesterdayNightUnits['night_units'] ?? null;
          $nonbusinesshoursunits = $morning_units - $prevNightUnits;
          // Prepare data for insertion
          $data = [
               'branch_id' => $branch_id,
               'cluster_id' => $cluster_id,
               'zone_id' => $zone_id,
               'morning_units' =>  $morning_units ?? null,
               'night_units' => $night_units ?? null,
               'consumption_date' => $consumption_date ?? null,
               'total_consumption' =>  $total_consumption ?? null,
               'nonbusinesshours' => $nonbusinesshoursunits ?? null,
               'remarks' =>  $remarks ?? null,
               'createdBy' => $emp_code,
               'createdDTM' => date('Y-m-d H:i:s')
          ];
          // Check if an entry already exists for the given consumption_date and branch_id
          $existingEntry = $powerConsumptionModel->where([
               'branch_id' => $branch_id,
               'consumption_date' => $consumption_date
          ])->first();
          if ($existingEntry) {
               return $this->respond(['status' => 'success', 'message' => 'Entry already exists for this date and branch'], 200);
          }
          $db = \Config\Database::connect();
          $db->table('power_consumption_logs')->insert($data);
          $insertId = $powerConsumptionModel->insert($data);
          if (!$insertId) {
               return $this->respond(['status' => 'error', 'message' => 'Failed to add power consumption record'], 500);
          }
          // Handle file upload
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
               if (!$insertId) {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to get diesel consumption ID'], 500);
               }
               $fileData = [
                    'file_name' => $fileName,
                    'power_id' => (int)$insertId, // Cast to integer to ensure proper type
                    'em_code' => $emp_code,
                    'createdDTM' => date('Y-m-d H:i:s'),
               ];
               $fileModel = new FileModel();
               $fileModel->insert($fileData);
          }
          return $this->respond([
               'status' => 'success',
               'message' => 'Power consumption added successfully',
               'id' => $insertId
          ], 201);
     }
     //updatePowerConsumption
     public function editPowerConsumption($id = null)
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          $branch_id = $this->request->getPost('branch_id') ?? $this->request->getVar('branch_id');
          $consumption_date = $this->request->getPost('consumption_date') ?? $this->request->getVar('consumption_date');
          $consumption_date = date('Y-m-d', strtotime($consumption_date));
          $morning_units = $this->request->getPost('morning_units') ?? $this->request->getVar('morning_units');
          $night_units = $this->request->getPost('night_units') ?? $this->request->getVar('night_units');
          $total_consumption = $this->request->getPost('total_consumption') ?? $this->request->getVar('total_consumption');
          $remarks = $this->request->getPost('remarks') ?? $this->request->getVar('remarks');
          $file = $this->request->getFile('file') ?? $this->request->getVar('file');
          // Validate the request body
          if (empty($branch_id) || empty($consumption_date)) {
               return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
          }
          $userModel = new UserModel();
          $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
          if (!$branchDetails) {
               return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
          }
          $cluster_id = $branchDetails['cluster'];
          $zone_id = $branchDetails['zone'];
          // Create a new instance of PowerConsumptionModel
          $powerConsumptionModel = new PowerConsumptionModel();
          // Prepare data for update
          $data = [
               'branch_id' => $branch_id,
               'cluster_id' => $cluster_id,
               'zone_id' => $zone_id,
               'morning_units' => $morning_units ?? null,
               'night_units' => $night_units ?? null,
               'consumption_date' => $consumption_date,
               'total_consumption' => $total_consumption ?? null,
               'remarks' =>  $remarks  ?? null,
               'createdBy' => $emp_code,
               'createdDTM' => date('Y-m-d H:i:s')
          ];
          // Update data in the database
          if ($powerConsumptionModel->update($id, $data)) {
               $db = \Config\Database::connect();
               $db->table('power_consumption_logs')->insert($data);
               $file = $this->request->getFile('file');
               if ($file && $file->isValid() && !$file->hasMoved()) {
                    // Validate file type and size
                    $allowedTypes = ['jpg', 'png', 'pdf', 'docx'];
                    if (!in_array($file->getExtension(), $allowedTypes)) {
                         return $this->respond(['status' => 'error', 'message' => 'Invalid file type'], 400);
                    }
                    if ($file->getSize() > 5242880) { // 5MB
                         return $this->respond(['status' => 'error', 'message' => 'File size exceeds 5MB limit'], 400);
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
                         'power_id' => (int)$id, // Cast to integer to ensure proper type
                         'em_code' => $emp_code,
                         'createdDTM' => date('Y-m-d H:i:s'),
                    ];
                    $fileModel = new FileModel();
                    $fileModel->insert($fileData);
               }
               return $this->respond(['status' => 'success', 'message' => 'Power consumption record updated successfully'], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'Failed to update power consumption record'], 500);
          }
     }
     public function getPreviousLastDateMeterData($meterId)
     {
          $userDetails = $this->validateAuthorization();
          $pcModel = new PCModel();
          // get last entry of the meter
          $lastEntry = $pcModel->where('meter_id', $meterId)
               ->orderBy('createdDTM', 'DESC')
               ->first();
          if ($lastEntry) {
               return $this->respond(['status' => 'success', 'data' => $lastEntry], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
          }
     }
     //deletePowerConsumption
     public function deletePowerConsumption($id = null)
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          // Create a new instance of PowerConsumptionModel
          $powerConsumptionModel = new PowerConsumptionModel();
          // Delete data from the database
          if ($powerConsumptionModel->delete($id)) {
               return $this->respond(['status' => 'success', 'message' => 'Power consumption record deleted successfully'], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'Failed to delete power consumption record'], 500);
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

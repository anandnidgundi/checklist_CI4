<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PowerConsumptionModel;
use App\Models\UserModel;
use App\Models\FileModel;
use App\Models\PowerMeterModel;
use App\Models\PCModel;
use App\Models\PowerConsumptionLogsModel;
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
          $selectedCluster = $this->request->getPost('cluster_id') ?? ($jsonData['cluster_id'] ?? $this->request->getVar('cluster_id')) ?? '2';
          $selectedBranch = $this->request->getPost('branch_id') ?? ($jsonData['branch_id'] ?? $this->request->getVar('branch_id')) ?? '0';
          $selectedMonth = $this->request->getPost('month') ?? ($jsonData['month'] ?? $this->request->getVar('month')) ?? $month;
          $selectedDate = $this->request->getPost('selectedDate') ?? ($jsonData['selectedDate'] ?? $this->request->getVar('selectedDate')) ?? null;
          $selectedToDate = $this->request->getPost('selectedToDate') ?? ($jsonData['selectedToDate'] ?? $this->request->getVar('selectedToDate')) ?? null;
          //fetching power consumption list 
          $powerConsumptionModel = new PowerConsumptionModel();
          $powerConsumptionList = $powerConsumptionModel->getPowerConsumptionList($role, $emp_code, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate);
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
          $selectedMonth = $this->request->getPost('month') ?? ($jsonData['month'] ?? $this->request->getVar('month')) ?? $month;
          $selectedDate = $this->request->getPost('selectedDate') ?? ($jsonData['selectedDate'] ?? $this->request->getVar('selectedDate')) ?? null;
          $selectedToDate = $this->request->getPost('selectedToDate') ?? ($jsonData['selectedToDate'] ?? $this->request->getVar('selectedToDate')) ?? null;
          $powerConsumptionList = $powerConsumptionModel->getPowerConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate);
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
     //getPowerMeterDataByPcId
     public function getPowerMeterDataByPcId($id = null)
     {
          $userDetails = $this->validateAuthorization();
          //fetching power consumption list
          $pcModel = new PCModel();
          $powerConsumptionList = $pcModel->getPowerMeterDataByPcId($id);
          if ($powerConsumptionList) {
               return $this->respond(['status' => 'success', 'data' => $powerConsumptionList], 200);
          } else {
               return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
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
     public function addPowerConsumption()
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          $branch_id = $this->request->getPost('branch_id') ?? $this->request->getVar('branch_id');
          $consumption_date = $this->request->getPost('consumption_date') ?? $this->request->getVar('consumption_date');
          $consumption_date = date('Y-m-d', strtotime($consumption_date));
          $meters_data = $this->request->getPost('meters_data') ?? $this->request->getVar('meters_data');
          $remarks = $this->request->getPost('remarks') ?? $this->request->getVar('remarks');
          // Parse meters_data if it's a string
          if (is_string($meters_data)) {
               $meters_data = json_decode($meters_data, true);
          }
          // Validate branch_id and consumption_date
          if (empty($branch_id) || empty($consumption_date)) {
               return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
          }
          // Validate meters data
          if (empty($meters_data) || !is_array($meters_data)) {
               return $this->respond(['status' => 'error', 'message' => 'Meter data is required and must be an array'], 400);
          }
          $userModel = new UserModel();
          $result = $userModel->getclusterId($branch_id);
          $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
          if (!$branchDetails) {
               return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
          }
          $cluster_id = $result['cluster_id'];
          $zone_id = $branchDetails['zone'];
          $powerConsumptionModel = new PowerConsumptionModel();
          // Check for existing entry
          $existingEntry = $powerConsumptionModel->where([
               'branch_id' => $branch_id,
               'consumption_date' => $consumption_date
          ])->first();
          if ($existingEntry) {
               return $this->respond(['status' => 'success', 'message' => 'Entry already exists for this date and branch'], 200);
          }
          // Calculate totals from meter readings
          $total_consumption = 0;
          $nonbusinesshoursunits = 0;
          foreach ($meters_data as $meter) {
               // Calculate totals from meter readings
               if (isset($meter['total_units'])) {
                    $total_units = floatval($meter['total_units']);
                    $total_consumption += $total_units;
               } else if (isset($meter['morning_units']) && isset($meter['night_units'])) {
                    // If total_units not provided, calculate from morning and night units
                    $morning = floatval($meter['morning_units']);
                    $night = floatval($meter['night_units']);
                    $total_units = $night - $morning;
                    $total_consumption += $total_units;
               }
               if (isset($meter['non_business_units'])) {
                    $nonbusinesshoursunits += floatval($meter['non_business_units']);
               }
          }
          // Prepare data for power consumption
          $data = [
               'branch_id' => $branch_id,
               'cluster_id' => $cluster_id,
               'zone_id' => $zone_id,
               'consumption_date' => $consumption_date,
               'total_consumption' => $total_consumption,
               'nonbusinesshours' => $nonbusinesshoursunits,
               'remarks' => $remarks ?? null,
               'createdBy' => $emp_code,
               'createdDTM' => date('Y-m-d H:i:s')
          ];
          // Insert power consumption record
          $insertId = $powerConsumptionModel->insert($data);
          if (!$insertId) {
               return $this->respond(['status' => 'error', 'message' => 'Failed to add power consumption record'], 500);
          }
          // Process meter data and photos
          $pcModel = new PCModel();
          $fileModel = new FileModel();
          $uploadPath = WRITEPATH . 'uploads/secure_files';
          if (!is_dir($uploadPath)) {
               mkdir($uploadPath, 0777, true);
          }
          $allowedTypes = ['jpg', 'png', 'pdf', 'docx'];
          $maxFileSize = 5242880; // 5MB
          foreach ($meters_data as $meter) {
               // Cast morning_units and night_units to float for calculation
               $morning_units = isset($meter['morning_units']) ? floatval($meter['morning_units']) : 0;
               $night_units = isset($meter['night_units']) ? floatval($meter['night_units']) : 0;
               // Insert meter reading data
               $meterData = [
                    'power_consumption_id' => $insertId,
                    'meter_id' => $meter['meter_id'],
                    'meter_number' => $meter['meter_number'],
                    'meter_name' => $meter['meter_name'],
                    'morning_units' => $morning_units,
                    'morning_remarks' => $meter['morning_remarks'] ?? null,
                    'night_units' => $night_units,
                    'night_remarks' => $meter['night_remarks'] ?? null,
                    'non_business_units' => isset($meter['non_business_units']) ? floatval($meter['non_business_units']) : null,
                    'total_units' => $night_units - $morning_units,
                    'createdBy' => $emp_code,
                    'createdDTM' => date('Y-m-d H:i:s')
               ];
               $pcModel->insert($meterData);
               // Process morning photo for the meter
               $morningPhotoField = 'morning_photo_' . $meter['meter_id'];
               $morningPhoto = $this->request->getFile($morningPhotoField);
               if ($morningPhoto && $morningPhoto->isValid() && !$morningPhoto->hasMoved()) {
                    if (!in_array($morningPhoto->getExtension(), $allowedTypes)) {
                         continue; // Skip invalid file types
                    }
                    if ($morningPhoto->getSize() > $maxFileSize) {
                         continue; // Skip oversized files
                    }
                    $fileName = 'morning_' . $meter['meter_id'] . '_' . time() . '.' . $morningPhoto->getExtension();
                    $morningPhoto->move($uploadPath, $fileName);
                    // Save file metadata
                    $fileData = [
                         'file_name' => $fileName,
                         'power_id' => $insertId,
                         'meter_id' => $meter['meter_id'],
                         'file_type' => 'morning',
                         'emp_code' => $emp_code,
                         'createdDTM' => date('Y-m-d H:i:s'),
                    ];
                    $fileModel->insert($fileData);
               }
               // Process night photo for the meter
               $nightPhotoField = 'night_photo_' . $meter['meter_id'];
               $nightPhoto = $this->request->getFile($nightPhotoField);
               if ($nightPhoto && $nightPhoto->isValid() && !$nightPhoto->hasMoved()) {
                    if (!in_array($nightPhoto->getExtension(), $allowedTypes)) {
                         continue; // Skip invalid file types
                    }
                    if ($nightPhoto->getSize() > $maxFileSize) {
                         continue; // Skip oversized files
                    }
                    $fileName = 'night_' . $meter['meter_id'] . '_' . time() . '.' . $nightPhoto->getExtension();
                    $nightPhoto->move($uploadPath, $fileName);
                    // Save file metadata
                    $fileData = [
                         'file_name' => $fileName,
                         'power_id' => $insertId,
                         'meter_id' => $meter['meter_id'],
                         'file_type' => 'night',
                         'emp_code' => $emp_code,
                         'createdDTM' => date('Y-m-d H:i:s'),
                    ];
                    $fileModel->insert($fileData);
               }
          }
          return $this->respond([
               'status' => 'success',
               'message' => 'Power consumption added successfully',
               'id' => $insertId
          ], 201);
     }
     // public function addPowerConsumption()
     // {
     //     $userDetails = $this->validateAuthorization();
     //     $role = $userDetails->role;
     //     $emp_code = $userDetails->emp_code;
     //     $branch_id = $this->request->getPost('branch_id') ?? $this->request->getVar('branch_id');
     //     $consumption_date = $this->request->getPost('consumption_date') ?? $this->request->getVar('consumption_date');
     //     $consumption_date = date('Y-m-d', strtotime($consumption_date));
     //     $meters_data = $this->request->getPost('meters_data') ?? $this->request->getVar('meters_data');
     //     $remarks = $this->request->getPost('remarks') ?? $this->request->getVar('remarks');
     //     // Parse meters_data if it's a string
     //     if (is_string($meters_data)) {
     //         $meters_data = json_decode($meters_data, true);
     //     }
     //     // Validate branch_id and consumption_date
     //     if (empty($branch_id) || empty($consumption_date)) {
     //         return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
     //     }
     //     // Validate meters data
     //     if (empty($meters_data) || !is_array($meters_data)) {
     //         return $this->respond(['status' => 'error', 'message' => 'Meter data is required and must be an array'], 400);
     //     }
     //     $userModel = new UserModel();
     //     $result = $userModel->getclusterId($branch_id);
     //     $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
     //     if (!$branchDetails) {
     //         return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
     //     }
     //     $cluster_id = $result['cluster_id'];
     //     $zone_id = $branchDetails['zone'];
     //     $powerConsumptionModel = new PowerConsumptionModel();
     //     // Check for existing entry
     //     $existingEntry = $powerConsumptionModel->where([
     //         'branch_id' => $branch_id,
     //         'consumption_date' => $consumption_date
     //     ])->first();
     //     if ($existingEntry) {
     //         return $this->respond(['status' => 'success', 'message' => 'Entry already exists for this date and branch'], 200);
     //     }
     //     // Calculate totals from meter readings
     //     $total_consumption = 0;
     //     $nonbusinesshoursunits = 0;
     //     foreach ($meters_data as $meter) {
     //         // Calculate totals from meter readings
     //         if (isset($meter['total_units'])) {
     //             $total_units = $meter['total_units'];
     //             $total_consumption += $total_units;
     //         }
     //         if (isset($meter['non_business_units'])) {
     //             $nonbusinesshoursunits += $meter['non_business_units'];
     //         }
     //     }
     //     // Prepare data for power consumption
     //     $data = [
     //         'branch_id' => $branch_id,
     //         'cluster_id' => $cluster_id,
     //         'zone_id' => $zone_id,            
     //         'consumption_date' => $consumption_date,
     //         'total_consumption' => $total_consumption,
     //         'nonbusinesshours' => $nonbusinesshoursunits,
     //         'remarks' => $remarks ?? null,
     //         'createdBy' => $emp_code,
     //         'createdDTM' => date('Y-m-d H:i:s')
     //     ];
     //     // Insert power consumption record
     //     $insertId = $powerConsumptionModel->insert($data);
     //     if (!$insertId) {
     //         return $this->respond(['status' => 'error', 'message' => 'Failed to add power consumption record'], 500);
     //     }
     //     // Process meter data and photos
     //     $pcModel = new PCModel();
     //     $fileModel = new FileModel();
     //     $uploadPath = WRITEPATH . 'uploads/secure_files';
     //     if (!is_dir($uploadPath)) {
     //         mkdir($uploadPath, 0777, true);
     //     }
     //     $allowedTypes = ['jpg', 'png', 'pdf', 'docx'];
     //     $maxFileSize = 5242880; // 5MB
     //     foreach ($meters_data as $meter) {
     //         // Insert meter reading data
     //         $meterData = [
     //             'power_consumption_id' => $insertId,
     //             'meter_id' => $meter['meter_id'],
     //             'meter_number' => $meter['meter_number'],
     //             'meter_name' => $meter['meter_name'],
     //             'morning_units' => $meter['morning_units'] ?? null,
     //             'morning_remarks' => $meter['morning_remarks'] ?? null,
     //             'night_units' => $meter['night_units'] ?? null,
     //             'night_remarks' => $meter['night_remarks'] ?? null,
     //             'non_business_units' => $meter['non_business_units'] ?? null,
     //             'total_units' => $meter['night_units'] - $meter['morning_units'],
     //             'createdBy' => $emp_code,
     //             'createdDTM' => date('Y-m-d H:i:s')
     //         ];
     //         $pcModel->insert($meterData);
     //         // Process morning photo for the meter
     //         $morningPhotoField = 'morning_photo_' . $meter['meter_id'];
     //         $morningPhoto = $this->request->getFile($morningPhotoField);
     //         if ($morningPhoto && $morningPhoto->isValid() && !$morningPhoto->hasMoved()) {
     //             if (!in_array($morningPhoto->getExtension(), $allowedTypes)) {
     //                 continue; // Skip invalid file types
     //             }
     //             if ($morningPhoto->getSize() > $maxFileSize) {
     //                 continue; // Skip oversized files
     //             }
     //             $fileName = 'morning_' . $meter['meter_id'] . '_' . time() . '.' . $morningPhoto->getExtension();
     //             $morningPhoto->move($uploadPath, $fileName);
     //             // Save file metadata
     //             $fileData = [
     //                 'file_name' => $fileName,
     //                 'power_id' => $insertId,
     //                 'meter_id' => $meter['meter_id'],
     //                 'file_type' => 'morning',
     //                 'emp_code' => $emp_code,
     //                 'createdDTM' => date('Y-m-d H:i:s'),
     //             ];
     //             $fileModel->insert($fileData);
     //         }
     //         // Process night photo for the meter
     //         $nightPhotoField = 'night_photo_' . $meter['meter_id'];
     //         $nightPhoto = $this->request->getFile($nightPhotoField);
     //         if ($nightPhoto && $nightPhoto->isValid() && !$nightPhoto->hasMoved()) {
     //             if (!in_array($nightPhoto->getExtension(), $allowedTypes)) {
     //                 continue; // Skip invalid file types
     //             }
     //             if ($nightPhoto->getSize() > $maxFileSize) {
     //                 continue; // Skip oversized files
     //             }
     //             $fileName = 'night_' . $meter['meter_id'] . '_' . time() . '.' . $nightPhoto->getExtension();
     //             $nightPhoto->move($uploadPath, $fileName);
     //             // Save file metadata
     //             $fileData = [
     //                 'file_name' => $fileName,
     //                 'power_id' => $insertId,
     //                 'meter_id' => $meter['meter_id'],
     //                 'file_type' => 'night',
     //                 'emp_code' => $emp_code,
     //                 'createdDTM' => date('Y-m-d H:i:s'),
     //             ];
     //             $fileModel->insert($fileData);
     //         }
     //     }
     //     return $this->respond([
     //         'status' => 'success',
     //         'message' => 'Power consumption added successfully',
     //         'id' => $insertId
     //     ], 201);
     // }
     public function updatePowerConsumption($id)
     {
          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;
          $branch_id = $this->request->getPost('branch_id') ?? $this->request->getVar('branch_id');
          $consumption_date = $this->request->getPost('consumption_date') ?? $this->request->getVar('consumption_date');
          $consumption_date = date('Y-m-d', strtotime($consumption_date));
          $meters_data = $this->request->getPost('meters_data') ?? $this->request->getVar('meters_data');
          $remarks = $this->request->getPost('remarks') ?? $this->request->getVar('remarks');
          // Parse meters_data if it's a string
          if (is_string($meters_data)) {
               $meters_data = json_decode($meters_data, true);
          }
          // Validate inputs
          if (empty($branch_id) || empty($consumption_date)) {
               return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
          }
          if (empty($meters_data) || !is_array($meters_data)) {
               return $this->respond(['status' => 'error', 'message' => 'Meter data is required and must be an array'], 400);
          }
          $userModel = new UserModel();
          $result = $userModel->getclusterId($branch_id);
          $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
          if (!$branchDetails) {
               return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
          }
          $cluster_id = $result['cluster_id'];
          $zone_id = $branchDetails['zone'];
          // Calculate totals from meter readings
          $total_consumption = 0;
          $nonbusinesshoursunits = 0;
          foreach ($meters_data as $meter) {
               if (isset($meter['total_units'])) {
                    $total_units = $meter['total_units'];
                    $total_consumption += $total_units;
               }
               if (isset($meter['non_business_units'])) {
                    $nonbusinesshoursunits += $meter['non_business_units'];
               }
          }
          // Prepare update data
          $data = [
               'branch_id' => $branch_id,
               'cluster_id' => $cluster_id,
               'zone_id' => $zone_id,
               'consumption_date' => $consumption_date,
               'total_consumption' => $total_consumption,
               'nonbusinesshours' => $nonbusinesshoursunits,
               'remarks' => $remarks ?? null,
               'modifiedBy' => $emp_code,
               'modifiedDTM' => date('Y-m-d H:i:s')
          ];
          // Update power consumption record
          $powerConsumptionModel = new PowerConsumptionModel();
          if (!$powerConsumptionModel->update($id, $data)) {
               return $this->respond(['status' => 'error', 'message' => 'Failed to update power consumption record'], 500);
          }
          // Update meter readings
          // Update meter readings
          $pcModel = new PCModel();
          // Delete existing meter readings
          $pcModel->where('power_consumption_id', $id)->delete();
          foreach ($meters_data as $meter) {
               // Insert updated meter reading data
               $meterData = [
                    'power_consumption_id' => $id,
                    'meter_id' => $meter['meter_id'],
                    'meter_number' => $meter['meter_number'],
                    'meter_name' => $meter['meter_name'],
                    'morning_units' => $meter['morning_units'] ?? null,
                    'morning_remarks' => $meter['morning_remarks'] ?? null,
                    'night_units' => $meter['night_units'] ?? null,
                    'night_remarks' => $meter['night_remarks'] ?? null,
                    'non_business_units' => $meter['non_business_units'] ?? null,
                    'total_units' => $meter['night_units'] - $meter['morning_units'],
                    'modifiedBy' => $emp_code,
                    'modifiedDTM' => date('Y-m-d H:i:s')
               ];
               $pcModel->insert($meterData);
               // Handle photo updates if new photos are provided
               $this->handlePhotoUpdate($meter['meter_id'], $id, 'morning', $emp_code);
               $this->handlePhotoUpdate($meter['meter_id'], $id, 'night', $emp_code);
          }
          return $this->respond([
               'status' => 'success',
               'message' => 'Power consumption updated successfully'
          ], 200);
     }
     private function handlePhotoUpdate($meterId, $powerId, $type, $empCode)
     {
          $photoField = $type . '_photo_' . $meterId;
          $photo = $this->request->getFile($photoField);
          if ($photo && $photo->isValid() && !$photo->hasMoved()) {
               $allowedTypes = ['jpg', 'png', 'pdf'];
               $maxFileSize = 5242880; // 5MB
               if (!in_array($photo->getExtension(), $allowedTypes) || $photo->getSize() > $maxFileSize) {
                    return;
               }
               $uploadPath = WRITEPATH . 'uploads/secure_files';
               if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
               }
               // Create unique filename with timestamp
               $fileName = $type . '_' . $meterId . '_' . time() . '.' . $photo->getExtension();
               $photo->move($uploadPath, $fileName);
               $fileModel = new FileModel();
               // Instead of deleting the existing photo, just add a new record
               // This maintains history of all uploaded photos
               $fileData = [
                    'file_name' => $fileName,
                    'power_id' => $powerId,
                    'meter_id' => $meterId,
                    'emp_code' => $empCode,
                    'createdDTM' => date('Y-m-d H:i:s'),
               ];
               $fileModel->insert($fileData);
               // Log the addition of a new photo
               log_message('info', "Added new {$type} photo for meter ID: {$meterId}, power consumption ID: {$powerId}");
          }
     }
     //getPowerConsumptionLogs
     // public function getPowerConsumptionLogs($id = null)
     // {
     //     $this->validateAuthorization();
     //     $powerConsumptionLogsModel = new PowerConsumptionLogsModel();
     //     $powerConsumptionLogs = $powerConsumptionLogsModel->where('id', $id)
     //                                                      ->orderBy('createdDTM', 'DESC')
     //                                                      ->findAll();
     //     if ($powerConsumptionLogs) {
     //         return $this->respond(['status' => 'success', 'data' => $powerConsumptionLogs], 200);
     //     } else {
     //         return $this->respond(['status' => 'error', 'message' => 'No data found'], 404);
     //     }
     // }
     public function addPowerConsumption_Faiz()
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
          $file = $this->request->getFile('file');
          if (!$file || !$file->isValid() || $file->hasMoved()) {
               return $this->respond(['status' => 'error', 'message' => 'File upload is required and must be valid'], 400);
          }
          // Validate file type and size
          $allowedTypes = ['jpg', 'png', 'pdf', 'docx'];
          if (!in_array($file->getExtension(), $allowedTypes)) {
               return $this->respond(['status' => 'error', 'message' => 'Invalid file type'], 400);
          }
          if ($file->getSize() > 5242880) { // 5MB
               return $this->respond(['status' => 'error', 'message' => 'File size exceeds 5MB limit'], 400);
          }
          $userModel = new UserModel();
          $result = $userModel->getclusterId($branch_id);
          $branchDetails = $userModel->getBranchDetailsById_fz($branch_id);
          if (!$branchDetails) {
               return $this->respond(['status' => 'error', 'message' => 'Invalid branch ID'], 400);
          }
          $cluster_id = $result['cluster_id'];
          $zone_id = $branchDetails['zone'];
          // Validate required fields
          if (empty($branch_id) || empty($consumption_date)) {
               return $this->respond(['status' => 'error', 'message' => 'Branch ID and Consumption Date are required'], 400);
          }
          $powerConsumptionModel = new PowerConsumptionModel();
          // Check for existing entry
          $existingEntry = $powerConsumptionModel->where([
               'branch_id' => $branch_id,
               'consumption_date' => $consumption_date
          ])->first();
          if ($existingEntry) {
               return $this->respond(['status' => 'success', 'message' => 'Entry already exists for this date and branch'], 200);
          }
          // Calculate non-business hours units
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
          // Prepare data
          $data = [
               'branch_id' => $branch_id,
               'cluster_id' => $cluster_id,
               'zone_id' => $zone_id,
               'morning_units' => $morning_units ?? null,
               'night_units' => $night_units ?? null,
               'consumption_date' => $consumption_date ?? null,
               'total_consumption' => $total_consumption ?? null,
               'nonbusinesshours' => $nonbusinesshoursunits ?? null,
               'remarks' => $remarks ?? null,
               'createdBy' => $emp_code,
               'createdDTM' => date('Y-m-d H:i:s')
          ];
          // Insert power consumption
          $insertId = $powerConsumptionModel->insert($data);
          if (!$insertId) {
               return $this->respond(['status' => 'error', 'message' => 'Failed to add power consumption record'], 500);
          }
          // Upload file
          $uploadPath = WRITEPATH . 'uploads/secure_files';
          if (!is_dir($uploadPath)) {
               mkdir($uploadPath, 0777, true);
          }
          $fileName = $file->getClientName();
          $file->move($uploadPath, $fileName);
          // Save file metadata
          $fileData = [
               'file_name' => $fileName,
               'power_id' => (int)$insertId,
               'emp_code' => $emp_code,
               'createdDTM' => date('Y-m-d H:i:s'),
          ];
          $fileModel = new FileModel();
          $fileModel->insert($fileData);
          return $this->respond([
               'status' => 'success',
               'message' => 'Power consumption added successfully',
               'id' => $insertId
          ], 201);
     }
     //addPowerConsumption
     public function addPowerConsumption_old()
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
                    'emp_code' => $emp_code,
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
                         'emp_code' => $emp_code,
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

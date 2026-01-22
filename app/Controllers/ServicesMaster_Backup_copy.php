<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ServicesMasterModel;
use App\Services\JwtService;
use App\Models\FileModel;
use App\Models\DeptModel;
use CodeIgniter\HTTP\ResponseInterface;



class ServicesMaster extends ResourceController
{
     protected $modelName = 'App\Models\ServicesMasterModel';
     protected $format = 'json';
     protected $services_model;

     public function __construct()
     {
          $this->services_model = new ServicesMasterModel();
     }

     public function createServices()
     {
          $tokenDecoded = $this->validateAuthorization();
          $emp_code = $tokenDecoded->emp_code;

          // Get request data with better input handling
          $input = null;
          $contentType = $this->request->getHeaderLine('Content-Type');

          if (strpos($contentType, 'application/json') !== false) {
               $rawInput = $this->request->getBody();
               $input = json_decode($rawInput, true);
          } else if (strpos($contentType, 'multipart/form-data') !== false) {
               $input = $this->request->getPost();
          } else {
               $input = $this->request->getRawInput();
          }

          // Log the raw input for debugging
          // log_message('error', "Raw Input: " . print_r($this->request->getBody(), true));
          // log_message('error', "Content Type: $contentType");
          // log_message('error', "Parsed Input: " . print_r($input, true));

          if (empty($input) || $input === null) {
               return $this->respond(['status' => 'error', 'message' => 'No input data received'], 400);
          }

          // Validate required fields
          $requiredFields = ['vendor_id', 'branch_id'];
          foreach ($requiredFields as $field) {
               if (!isset($input[$field]) || empty($input[$field])) {
                    return $this->respond(['status' => 'error', 'message' => ucfirst($field) . ' is required'], 400);
               }
          }
          $service_type = $input['service_type'] ?? null;

          $data = [
               'service_date' => $input['service_date'] ?? null,
               'service_type' => $input['service_type'] ?? null,
               'visiter_name' => $input['visiter_name'] ?? null,
               'visiter_mobile' => $input['visiter_mobile'] ?? null,
               'remarks' => $input['remarks'] ?? null,
               'branch_id' => $input['branch_id'],
               'vendor_id' => $input['vendor_id'],
               'createdDTM' => date('Y-m-d H:i:s'),
               'createdBy' =>  $emp_code,
               'status' => 'A', // Default status
          ];

          $insertId =  $this->services_model->insert($data);
          log_message('error', "Insert ID: $insertId");
          if (!$insertId) {
               return $this->respond(['status' => 'error', 'message' => 'Failed to insert data'], 500);
          }

          // File handling remains the same
          // File handling: Support multiple files from 'files[]'
          $files = $this->request->getFiles();

          if (isset($files['files']) && is_array($files['files'])) {
               foreach ($files['files'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                         $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];

                         if (!in_array(strtolower($file->getExtension()), $allowedTypes)) {
                              log_message('error', "Invalid file type: " . $file->getClientName());
                              continue; // Skip this file, optionally collect errors
                         }

                         if ($file->getSize() > 5 * 1024 * 1024) { // 5MB
                              log_message('error', "File too large: " . $file->getClientName());
                              continue; // Skip this file
                         }

                         $uploadPath = WRITEPATH . 'uploads/secure_files';
                         if (!is_dir($uploadPath)) {
                              mkdir($uploadPath, 0777, true);
                         }

                         $fileName = $file->getRandomName();
                         $file->move($uploadPath, $fileName);

                         $fileData = [
                              'file_name' => $fileName,
                              'service_id' => (int)$insertId,
                              'emp_code' => $emp_code,
                              'createdDTM' => date('Y-m-d H:i:s'),
                         ];

                         log_message('error', "File Data: " . print_r($fileData, true));

                         $fileModel = new FileModel();
                         $fileModel->insert($fileData);
                    }
               }
          }

          return $this->respond(['status' => 'success', 'message' => $service_type . ' created successfully'], 200);
     }


     public function updateServices($sid)
     {
          $tokenDecoded = $this->validateAuthorization();
          $emp_code = $tokenDecoded->emp_code;

          // Get request data with better input handling
          $input = null;
          $contentType = $this->request->getHeaderLine('Content-Type');

          if (strpos($contentType, 'application/json') !== false) {
               $rawInput = $this->request->getBody();
               $input = json_decode($rawInput, true);
          } else if (strpos($contentType, 'multipart/form-data') !== false) {
               $input = $this->request->getPost();
          } else {
               $input = $this->request->getRawInput();
          }

          // Log received input
          log_message('debug', 'Update Input: ' . print_r($input, true));

          if (empty($input) || $input === null) {
               return $this->respond(['status' => 'error', 'message' => 'No input data received'], 400);
          }

          // First check if record exists
          $existingRecord = $this->services_model->find($sid);
          if (!$existingRecord) {
               return $this->respond(['status' => 'error', 'message' => 'Record not found'], 404);
          }

          $data = [
               'service_date' => $input['service_date'] ?? $existingRecord['service_date'],
               'service_type' => $input['service_type'] ?? $existingRecord['service_type'],
               'visiter_name' => $input['visiter_name'] ?? $existingRecord['visiter_name'],
               'visiter_mobile' => $input['visiter_mobile'] ?? $existingRecord['visiter_mobile'],
               'remarks' => $input['remarks'] ?? $existingRecord['remarks'],
               'branch_id' => $input['branch_id'] ?? $existingRecord['branch_id'],
               'vendor_id' => $input['vendor_id'] ?? $existingRecord['vendor_id'],
               'status' => $input['status'] ?? $existingRecord['status'],
               'updatedDTM' => date('Y-m-d H:i:s'),
               'updatedBy' => $emp_code
          ];

          // Log update data
          log_message('debug', 'Update Data: ' . print_r($data, true));

          try {
               $this->services_model->where('sid', $sid);
               $updated = $this->services_model->update($sid, $data);

               // Log the query for debugging
               log_message('debug', 'Last Query: ' . $this->services_model->getLastQuery());

               if ($updated === false) {
                    log_message('error', 'Update Error: ' . print_r($this->services_model->errors(), true));
                    return $this->respond(['status' => 'error', 'message' => 'Failed to update record: ' . implode(', ', $this->services_model->errors())], 500);
               }

               if ($this->services_model->affectedRows() === 0) {
                    return $this->respond(['status' => 'error', 'message' => 'No changes made to record'], 200);
               }

               // Handle file updates only if update was successful
               $fileModel = new FileModel();

               // Handle existing files
               if (isset($input['existing_files'])) {
                    $existingFiles = is_string($input['existing_files']) ?
                         json_decode($input['existing_files'], true) : $input['existing_files'];

                    if ($existingFiles) {
                         $existingFileIds = array_column($existingFiles, 'file_id');
                         $fileModel->where('service_id', $sid)
                              ->whereNotIn('file_id', $existingFileIds)
                              ->delete();
                    }
               }

               // Handle new files
               $files = $this->request->getFiles();
               if (isset($files['files']) && is_array($files['files'])) {
                    foreach ($files['files'] as $file) {
                         if ($file->isValid() && !$file->hasMoved()) {
                              $allowedTypes = ['jpg', 'jpeg', 'png', 'pdf'];
                              if (!in_array(strtolower($file->getExtension()), $allowedTypes)) {
                                   continue;
                              }
                              if ($file->getSize() > 5 * 1024 * 1024) {
                                   continue;
                              }

                              $uploadPath = WRITEPATH . 'uploads/secure_files';
                              if (!is_dir($uploadPath)) {
                                   mkdir($uploadPath, 0777, true);
                              }

                              $fileName = $file->getRandomName();
                              $file->move($uploadPath, $fileName);

                              $fileModel->insert([
                                   'file_name' => $fileName,
                                   'service_id' => (int)$sid,
                                   'emp_code' => $emp_code,
                                   'createdDTM' => date('Y-m-d H:i:s'),
                              ]);
                         }
                    }
               }

               return $this->respond(['status' => 'success', 'message' => 'Record updated successfully'], 200);
          } catch (\Exception $e) {
               log_message('error', 'Update Exception: ' . $e->getMessage());
               return $this->respond(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()], 500);
          }
     }

     public function deleteServices()
     {
          $tokenDecoded = $this->validateAuthorization();
          $emp_code = $tokenDecoded->emp_code;

          // Get sid from request body
          $input = json_decode($this->request->getBody(), true);
          $sid = $input['sid'] ?? null;

          if (empty($sid)) {
               return $this->respond(['status' => 'error', 'message' => 'Service ID is required'], 400);
          }

          // Check if record exists
          $existingRecord = $this->services_model->find($sid);
          if (!$existingRecord) {
               return $this->respond(['status' => 'error', 'message' => 'Record not found'], 404);
          }

          // Check if record is already inactive
          if ($existingRecord['status'] === 'I') {
               return $this->respond(['status' => 'error', 'message' => 'Record is already deleted'], 400);
          }

          // Soft delete the record
          $data = [
               'status' => 'I',
               'updatedDTM' => date('Y-m-d H:i:s'),
               'updatedBy' => $emp_code
          ];

          try {
               $updated = $this->services_model->update($sid, $data);

               if ($updated === false) {
                    log_message('error', 'Delete Error: ' . print_r($this->services_model->errors(), true));
                    return $this->respond(['status' => 'error', 'message' => 'Failed to delete record'], 500);
               }

               return $this->respond(['status' => 'success', 'message' => 'Record deleted successfully'], 200);
          } catch (\Exception $e) {
               log_message('error', 'Delete Exception: ' . $e->getMessage());
               return $this->respond(['status' => 'error', 'message' => 'Database error'], 500);
          }
     }

     public function getServicesList()
     {
          try {
               $this->validateAuthorization();

               // Get the pest control data with branch information
               $builder = $this->services_model->builder();
               $builder->select('services.*, Branches.SysField as branch_name, Branches.Address,services.createdBy as emp_code, new_emp_master.comp_name, vendor.*');
               //where status = A 
               $builder->where('services.status', 'A');

               // Join vendor table with better error handling
               $builder->join('vendor', 'services.vendor_id = vendor.vendor_id', 'left');

               // Join with branches table from secondary database
               $db2 = \Config\Database::connect('secondary');

               $branchesTable = $db2->database . '.Branches';
               $usersTable = $db2->database . '.new_emp_master';
               $builder->join($usersTable, 'services.createdBy = new_emp_master.emp_code', 'left');

               $builder->join($branchesTable, 'services.branch_id = Branches.id', 'left');

               $builder->orderBy('services.sid', 'desc');

               $query = $builder->get();
               if (!$query) {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to fetch data'], 500);
               }

               $result = $query->getResultArray();
               // send files list with pest control data where sid = services_id
               $fileModel = new FileModel();
               foreach ($result as &$row) {
                    $files = $fileModel->where('service_id', $row['sid'])->findAll();
                    $row['files'] = $files;
               }


               return $this->respond(['status' => 'success', 'data' => $result], 200);
          } catch (\Exception $e) {
               log_message('error', 'Error in getUsersServicesList: ' . $e->getMessage());
               return $this->respond(['status' => 'error', 'message' => 'An error occurred while fetching data'], 500);
          }
     }

     public function getUsersServicesList()
     {
          try {
               $this->validateAuthorization();

               $jsonData = $this->request->getJSON(true); // true → returns assoc array
               $selectedMonth     = $jsonData['month'] ?? $month ?? date('Y-m');
               $selectedDate      = $jsonData['selectedDate'] ?? '';
               $selectedToDate    = $jsonData['selectedToDate'] ?? '';
               $selectedBranchRaw = $jsonData['branch_id'] ?? '0';
               $selectedBranch = (!empty($selectedBranchRaw) && $selectedBranchRaw != '0')
                    ? (is_array($selectedBranchRaw) ? $selectedBranchRaw : [$selectedBranchRaw])
                    : [];

               $response = $this->getEmpBranches();
               if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                    if (isset($data['data']) && is_array($data['data'])) {
                         $empBranches = array_column($data['data'], 'id');
                    } else {
                         return $this->respond(['status' => 'error', 'message' => 'Invalid branch data format'], 400);
                    }
               } else {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to get employee branches'], $response->getStatusCode());
               }

               $builder = $this->services_model->builder();
               $builder->select('services.*, Branches.SysField as branch_name, services.createdBy as emp_code, new_emp_master.comp_name,  vendor.*');
               //where status = A 
               $builder->where('services.status', 'A');
               // Apply date filters
               if (!empty($selectedDate) && !empty($selectedToDate)) {
                    $builder->where('services.service_date >=', $selectedDate);
                    $builder->where('services.service_date <=', $selectedToDate);
               } elseif (!empty($selectedDate)) {
                    $builder->where('DATE(services.service_date)', $selectedDate);
               } elseif (!empty($selectedMonth)) {
                    $builder->where('DATE_FORMAT(services.service_date, "%Y-%m") =', $selectedMonth);
               }

               // Handle branch filtering
               if (!empty($selectedBranch)) {
                    $builder->whereIn('services.branch_id', $selectedBranch);
               }

               // Join vendor table with better error handling
               $builder->join('vendor', 'services.vendor_id = vendor.vendor_id', 'left');

               // Join with branches table from secondary database
               $db2 = \Config\Database::connect('secondary');
               $branchesTable = $db2->database . '.Branches';
               $usersTable = $db2->database . '.new_emp_master';
               $builder->join($usersTable, 'services.createdBy = new_emp_master.emp_code', 'left');
               $builder->join($branchesTable, 'services.branch_id = Branches.id', 'left');


               // get branches from empBranches
               if (isset($empBranches) && is_array($empBranches)) {
                    $builder->whereIn('services.branch_id', $empBranches);
               } else {
                    return $this->respond(['status' => 'error', 'message' => 'No branches found for the user'], 404);
               }

               $builder->orderBy('services.sid', 'desc');
               $query = $builder->get();
               if (!$query) {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to fetch data'], 500);
               }

               $result = $query->getResultArray();
               // send files list with pest control data where sid = services_id
               $fileModel = new FileModel();
               foreach ($result as &$row) {
                    $files = $fileModel->where('service_id', $row['sid'])->findAll();
                    $row['files'] = $files;
               }


               return $this->respond(['status' => 'success', 'data' => $result], 200);
          } catch (\Exception $e) {
               log_message('error', 'Error in getUsersServicesList: ' . $e->getMessage());
               return $this->respond(['status' => 'error', 'message' => 'An error occurred while fetching data'], 500);
          }
     }

     public function getUsersServicesList_Backup_28_06_2025()
     {
          try {
               $this->validateAuthorization();


               $response = $this->getEmpBranches();
               if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                    if (isset($data['data']) && is_array($data['data'])) {
                         $empBranches = array_column($data['data'], 'id');
                    } else {
                         return $this->respond(['status' => 'error', 'message' => 'Invalid branch data format'], 400);
                    }
               } else {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to get employee branches'], $response->getStatusCode());
               }

               $builder = $this->services_model->builder();
               $builder->select('services.*, Branches.SysField as branch_name, services.createdBy as emp_code, new_emp_master.comp_name,  vendor.*');
               //where status = A 
               $builder->where('services.status', 'A');

               // Join vendor table with better error handling
               $builder->join('vendor', 'services.vendor_id = vendor.vendor_id', 'left');

               // Join with branches table from secondary database
               $db2 = \Config\Database::connect('secondary');
               $branchesTable = $db2->database . '.Branches';
               $usersTable = $db2->database . '.new_emp_master';
               $builder->join($usersTable, 'services.createdBy = new_emp_master.emp_code', 'left');
               $builder->join($branchesTable, 'services.branch_id = Branches.id', 'left');


               // get branches from empBranches
               if (isset($empBranches) && is_array($empBranches)) {
                    $builder->whereIn('services.branch_id', $empBranches);
               } else {
                    return $this->respond(['status' => 'error', 'message' => 'No branches found for the user'], 404);
               }

               $builder->orderBy('services.sid', 'desc');
               $query = $builder->get();
               if (!$query) {
                    return $this->respond(['status' => 'error', 'message' => 'Failed to fetch data'], 500);
               }

               $result = $query->getResultArray();
               // send files list with pest control data where sid = services_id
               $fileModel = new FileModel();
               foreach ($result as &$row) {
                    $files = $fileModel->where('service_id', $row['sid'])->findAll();
                    $row['files'] = $files;
               }


               return $this->respond(['status' => 'success', 'data' => $result], 200);
          } catch (\Exception $e) {
               log_message('error', 'Error in getUsersServicesList: ' . $e->getMessage());
               return $this->respond(['status' => 'error', 'message' => 'An error occurred while fetching data'], 500);
          }
     }

     public function getServicesById($sid = null)
     {
          $tokenDecoded = $this->validateAuthorization();
          $builder =  $this->services_model->builder();
          $builder->select('services.*, branches.SysField as branch_name, branches.Address');

          // Join with branches table from secondary database
          $db2 = \Config\Database::connect('secondary');
          $branchesTable = $db2->database . '.branches';
          $builder->join($branchesTable, 'services.branch_id = branches.id', 'left');

          $fileModel = new FileModel();
          if ($sid) {
               $builder->where('services.sid', $sid);
               $query = $builder->get();
               $record = $query->getRowArray();

               if ($record) {
                    $files = $fileModel->where('service_id', $sid)->findAll();
                    $record['files'] = $files;
                    return $this->respond(['status' => 'success', 'data' => $record], 200);
               } else {
                    return $this->respond(['status' => 'error', 'message' => 'Record not found'], 404);
               }
          } else {
               $query = $builder->get();
               $records = $query->getResultArray();
               foreach ($records as &$row) {
                    $files = $fileModel->where('services_id', $row['sid'])->findAll();
                    $row['files'] = $files;
               }
               return $this->respond(['status' => 'success', 'data' => $records], 200);
          }
     }


     public function getServicesListForAdmin($month)
     {
          $userDetails = $this->validateAuthorization();
          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;
          // Setup DB connections
          $defaultDB = \Config\Database::connect('default');
          $db2 = \Config\Database::connect('secondary');
          $branchesTable = $db2->database . '.Branches';
          $usersTable = $db2->database . '.new_emp_master';
          // Fetch branches user has access to
          $deptModel = new \App\Models\DeptModel();
          $clusters = $deptModel->getClustersWithBranches($emp_code);
          $allowedBranchIds = [];
          foreach ($clusters as $cluster) {
               $branchIds = explode(',', $cluster['branches']);
               foreach ($branchIds as $bid) {
                    $bid = trim($bid);
                    if (!in_array($bid, $allowedBranchIds)) {
                         $allowedBranchIds[] = $bid;
                    }
               }
          }
          // Read and parse filters
          $jsonInput = $this->request->getJSON(true) ?? [];
          $zone_id         = $jsonInput['zone_id'] ?? null;
          $selectedCluster = $jsonInput['cluster_id'] ?? null;
          $selectedBranch  = $jsonInput['branch_id'] ?? '0';
          $selectedMonth   = $month;
          $selectedDate    = $jsonInput['selectedDate'] ?? null;
          $selectedToDate  = $jsonInput['selectedToDate'] ?? null;
          // Prepare base query
          $builder = $this->services_model->builder();
          $builder->select('services.*, vendor.*, Branches.SysField as branch_name, Branches.Address, new_emp_master.comp_name');
          $builder->where('services.status', 'A');
          $builder->join('vendor', 'services.vendor_id = vendor.vendor_id', 'left');
          $builder->join($usersTable, 'services.createdBy = new_emp_master.emp_code', 'left');
          $builder->join($branchesTable, 'services.branch_id = Branches.id', 'left');
          // Date filters
          if (!empty($selectedDate) && !empty($selectedToDate)) {
               $builder->where('services.service_date >=', $selectedDate);
               $builder->where('services.service_date <=', $selectedToDate);
          } elseif (!empty($selectedDate)) {
               $builder->where('DATE(services.service_date)', $selectedDate);
          } elseif (!empty($selectedMonth)) {
               $builder->where('DATE_FORMAT(services.service_date, "%Y-%m") =', $selectedMonth);
          }
          // Cluster filter
          if (!empty($selectedCluster) && $selectedCluster !== '0') {
               $clusterData = $deptModel->getClustersWithBranches($emp_code);
               $clusterMatch = array_filter($clusterData, fn($c) => $c['cluster_id'] == $selectedCluster);
               $branchIds = [];
               foreach ($clusterMatch as $cluster) {
                    $ids = explode(',', $cluster['branches']);
                    foreach ($ids as $bid) {
                         $bid = trim($bid);
                         if (!in_array($bid, $branchIds)) {
                              $branchIds[] = $bid;
                         }
                    }
               }
               if (!empty($branchIds)) {
                    $builder->whereIn('services.branch_id', $branchIds);
               }
          }
          // Branch filter
          if (!empty($selectedBranch) && $selectedBranch !== '0') {
               $branchArray = is_array($selectedBranch) ? $selectedBranch : explode(',', $selectedBranch);
               $builder->whereIn('services.branch_id', $branchArray);
          } else {
               $builder->whereIn('services.branch_id', $allowedBranchIds);
          }
          $builder->orderBy('services.service_date', 'asc');
          $query = $builder->get();
          $result = $query->getResultArray();
          // Load file attachments
          $serviceIds = array_column($result, 'sid');
          $fileMap = [];
          if (!empty($serviceIds)) {
               $fileModel = new \App\Models\FileModel();
               $allFiles = $fileModel->whereIn('service_id', $serviceIds)->findAll();
               foreach ($allFiles as $file) {
                    $fileMap[$file['service_id']][] = $file;
               }
          }
          // Add zone and cluster names
          foreach ($result as &$row) {
               $branchId = $row['branch_id'];
               // Zone lookup
               $zoneRow = $defaultDB->table('zones')
                    ->select('z_id, zone')
                    ->where("FIND_IN_SET('$branchId', branches)")
                    ->get()
                    ->getRowArray();
               $row['zone_id'] = $zoneRow['z_id'] ?? '';
               $row['zone'] = $zoneRow['zone'] ?? '';
               // Cluster lookup
               $clusterRow = $defaultDB->table('clusters')
                    ->select('cluster_id, cluster')
                    ->where("FIND_IN_SET('$branchId', branches)")
                    ->get()
                    ->getRowArray();
               $row['cluster_id'] = $clusterRow['cluster_id'] ?? '';
               $row['cluster'] = $clusterRow['cluster'] ?? '';
               // File attachments
               $row['files'] = $fileMap[$row['sid']] ?? [];
          }
          return $this->respond(['status' => 'success', 'data' => $result], 200);
     }



     public function getEmpBranches()
     {

          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;

          $deptModel = new DeptModel();
          $cluster = $deptModel->getUserMapBranches($user);
          $branches = $cluster['0']['branches'];

          $cluster = $deptModel->getUserBranches($branches);

          log_message('error', "Branches: " . print_r($cluster, true));
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
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

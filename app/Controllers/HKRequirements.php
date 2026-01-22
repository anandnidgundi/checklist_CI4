<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkRequirementsModel;
use App\Models\HkDetailsModel;
use App\Models\HkMaterialsModel;
use App\Models\HkBranchwiseBudgetModel;
use App\Models\DeptModel;
use App\Services\JwtService;


class HKRequirements extends BaseController
{
     use ResponseTrait;

     private $jwtService;

     public function __construct()
     {
          $this->jwtService = new JwtService();
     }

     // private function validateAuthorization()
     // {
     //      if (!class_exists('App\Services\JwtService')) {
     //           ////log_message( 'error', 'JwtService class not found' );
     //           return $this->respond(['error' => 'JwtService class not found'], 500);
     //      }
     //      // Get the Authorization header and log it
     //      $authorizationHeader = $this->request->header('Authorization')?->getValue();
     //      ////log_message( 'info', 'Authorization header: ' . $authorizationHeader );
     //      // Create an instance of JwtService and validate the token
     //      $jwtService = new JwtService();
     //      $result = $jwtService->validateToken($authorizationHeader);
     //      // Handle token validation errors
     //      if (isset($result['error'])) {
     //           ////log_message( 'error', $result[ 'error' ] );
     //           return $this->respond(['error' => $result['error']], $result['status']);
     //      }
     //      // Extract the decoded token and get the USER-ID
     //      $decodedToken = $result['data'];
     //      return $decodedToken;
     //      // Assuming JWT contains USER-ID
     // }
     /**
      * Get all housekeeping materials
      */
     public function getHkMaterials()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkMaterialsModel = new HkMaterialsModel();

          try {
               $materials = $hkMaterialsModel->getAllMaterials();

               return $this->respond([
                    'status' => true,
                    'message' => 'HK Materials retrieved successfully',
                    'data' => $materials
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve materials: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Create new HK requirement with details
      */




     // public function createHkRequirement()
     // {
     //      $userDetails = $this->validateAuthorization();
     //      if (!$userDetails) {
     //           return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
     //      }

     //      // Get the form-data fields
     //      $dataString = $this->request->getPost('data'); // Get the `data` field as a string
     //      $files = $this->request->getFiles(); // Get the uploaded files
     //      log_message('error', 'Files received: ' . print_r($files, true));

     //      // Decode the `data` field into a JSON object
     //      $dataString = preg_replace('/"file":\{\}/', '"file":null', $dataString);
     //      $requestData = json_decode($dataString);

     //      if (json_last_error() !== JSON_ERROR_NONE) {
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Invalid JSON format: ' . json_last_error_msg()
     //           ], 400);
     //      }

     //      // Validate required fields
     //      if (empty($requestData->branch_id) || empty($requestData->for_month) || empty($requestData->materials)) {
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Missing required fields: branch_id, for_month, materials'
     //           ], 400);
     //      }

     //      // Validate materials data
     //      if (!$this->validateMaterials($requestData->materials)) {
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Invalid materials data. Each material must have hk_material, quantity, and amount.'
     //           ], 400);
     //      }

     //      $hkRequirementsModel = new HkRequirementsModel();
     //      $hkDetailsModel = new HkDetailsModel();
     //      $filesModel = \Config\Database::connect()->table('files'); // Use the files table
     //      $hkBudgetModel = new HkBranchwiseBudgetModel();

     //      $db = \Config\Database::connect();
     //      $db->transStart();

     //      try {
     //           // Get cluster_id from branch
     //           $clusterInfo = $this->getClusterFromBranch($requestData->branch_id);
     //           $cluster_id = $clusterInfo['cluster_id'] ?? null;

     //           // Get budget for validation
     //           $budgetInfo = $hkBudgetModel->getBudgetByBranch($requestData->branch_id);
     //           $budget = $budgetInfo['budget'] ?? 0;

     //           // Calculate total amount from materials
     //           $total_amount = 0;
     //           foreach ($requestData->materials as $material) {
     //                $total_amount += floatval($material->amount ?? 0);
     //           }

     //           // Check if requirement already exists for the month and branch
     //           $existingRequirement = $hkRequirementsModel->where([
     //                'for_month' => $requestData->for_month,
     //                'branch_id' => $requestData->branch_id,
     //                'isDeleted' => 'N'
     //           ])->first();

     //           if ($existingRequirement) {
     //                return $this->respond([
     //                     'status' => false,
     //                     'message' => 'HK Requirement already exists for this month and branch'
     //                ], 409);
     //           }

     //           // Prepare requirement data
     //           $requirementData = [
     //                'for_month' => $requestData->for_month,
     //                'created_by' => $userDetails->emp_code,
     //                'remarks' => $requestData->remarks ?? '',
     //                'status' => 'Pending',
     //                'branch_id' => $requestData->branch_id,
     //                'cluster_id' => $cluster_id,
     //                'isDeleted' => 'N',
     //                'applied_amount' => $total_amount,
     //                'budget' => $budget
     //           ];

     //           // Insert requirement
     //           $hkr_id = $hkRequirementsModel->addRequirement($requirementData);

     //           if (!$hkr_id) {
     //                throw new \Exception('Failed to create HK requirement');
     //           }

     //           // Prepare materials data 
     //           $materialsData = [];
     //           foreach ($requestData->materials as $material) {
     //                if (!empty($material->hk_material) && !empty($material->quantity)) {
     //                     $materialsData[] = [
     //                          'hkr_id' => $hkr_id,
     //                          'hk_material' => $material->hk_material,
     //                          'quantity' => $material->quantity,
     //                          'amount' => floatval($material->amount ?? 0)
     //                     ];
     //                }
     //           }

     //           // Insert material details
     //           if (!empty($materialsData)) {
     //                $result = $hkDetailsModel->insertBatch($materialsData);
     //                if (!$result) {
     //                     throw new \Exception('Failed to add material details');
     //                }
     //           }

     //           // Handle file uploads

     //           if (isset($files['files']) && $files['files'] instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
     //                $file = $files['files']; // Single file object

     //                if ($file->isValid() && !$file->hasMoved()) {

     //                     // Rename and move the file to the secure_files directory
     //                     $newFileName = $file->getRandomName();
     //                     $uploadPath = WRITEPATH . 'uploads/secure_files';

     //                     // Ensure the directory exists
     //                     if (!is_dir($uploadPath)) {
     //                          mkdir($uploadPath, 0777, true);
     //                     }

     //                     try {
     //                          $file->move($uploadPath, $newFileName);

     //                          // Save file metadata in the database
     //                          $filesData[] = [
     //                               'file_name' => $newFileName,
     //                               'hkr_id' => $hkr_id,
     //                               'createdDTM' => date('Y-m-d H:i:s')
     //                          ];
     //                          $filesModel->insertBatch($filesData);
     //                     } catch (\Exception $e) {
     //                          log_message('error', 'Failed to move file: ' . $e->getMessage());
     //                          return $this->respond([
     //                               'status' => false,
     //                               'message' => 'Failed to upload file: ' . $e->getMessage()
     //                          ], 500);
     //                     }
     //                } else {
     //                     log_message('error', 'File upload failed: ' . $file->getErrorString());
     //                     return $this->respond([
     //                          'status' => false,
     //                          'message' => 'File upload failed: ' . $file->getErrorString()
     //                     ], 400);
     //                }
     //           } else {
     //                log_message('error', 'No files found in the request.');
     //                return $this->respond([
     //                     'status' => false,
     //                     'message' => 'No files uploaded.'
     //                ], 400);
     //           }
     //           $db->transComplete();

     //           if ($db->transStatus() === false) {
     //                throw new \Exception('Transaction failed');
     //           }

     //           return $this->respond([
     //                'status' => true,
     //                'message' => 'HK Requirement created successfully',
     //                'data' => ['hkr_id' => $hkr_id]
     //           ], 201);
     //      } catch (\Exception $e) {
     //           $db->transRollback();
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Failed to create HK requirement: ' . $e->getMessage()
     //           ], 500);
     //      }
     // }



     public function createHkRequirement()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          // Get the form-data fields
          $dataString = $this->request->getPost('data'); // Get the `data` field as a string
          log_message('error', 'Data string received: ' . $dataString);
          $files = $this->request->getFiles(); // Get the uploaded files
          log_message('error', 'Files received: ' . print_r($files, true));

          // Decode the `data` field into a JSON object
          $dataString = preg_replace('/"file":\{\}/', '"file":null', $dataString);
          $requestData = json_decode($dataString);
          $branch_id = $requestData->branch_id;
          if (json_last_error() !== JSON_ERROR_NONE) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Invalid JSON format: ' . json_last_error_msg()
               ], 400);
          }

          // Validate required fields
          if (empty($requestData->branch_id) || empty($requestData->for_month) || empty($requestData->materials)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Missing required fields: branch_id, for_month, materials'
               ], 400);
          }

          // Validate materials data
          if (!$this->validateMaterials($requestData->materials)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Invalid materials data. Each material must have hk_material, quantity, and amount.'
               ], 400);
          }

          $hkRequirementsModel = new HkRequirementsModel();
          $hkDetailsModel = new HkDetailsModel();
          $filesModel = \Config\Database::connect()->table('files'); // Use the files table
          $hkBudgetModel = new HkBranchwiseBudgetModel();

          $db = \Config\Database::connect();
          $db->transStart();

          try {
               // Get cluster_id from branch
               log_message('error', 'Fetching cluster info for branch_id: ' . $branch_id);
               $clusterInfo = $this->getClusterFromBranch($branch_id);
               $cluster_id = $clusterInfo['cluster_id'] ?? null;

               // Get budget for validation
               $budgetInfo = $hkBudgetModel->getBudgetByBranch($branch_id);
               $budget = $budgetInfo['budget'] ?? 0;

               // Calculate total amount from materials
               $total_amount = array_reduce($requestData->materials, function ($sum, $material) {
                    return $sum + floatval($material->amount ?? 0);
               }, 0);

               $forMonth = date('M-Y', strtotime($requestData->for_month));


               $branchData = $hkRequirementsModel->getBranchById($branch_id);
               $branchName = $branchData['branch_name'] ?? 'Unknown Branch';

               // Check if requirement already exists for the month and branch
               $existingRequirement = $hkRequirementsModel->where([
                    'for_month' => $requestData->for_month,
                    'branch_id' => $branch_id,
                    'isDeleted' => 'N'
               ])->first();

               if ($existingRequirement) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'HK Requirement already exists for this month and branch'
                    ], 409);
               }

               // Prepare requirement data
               $requirementData = [
                    'for_month' => $requestData->for_month,
                    'created_by' => $userDetails->emp_code,
                    'remarks' => $requestData->remarks ?? '',
                    'status' => 'Pending',
                    'branch_id' => $branch_id,
                    'cluster_id' => $cluster_id,
                    'isDeleted' => 'N',
                    'applied_amount' => $total_amount,
                    'budget_amount' => $budget
               ];

               // Insert requirement
               $hkr_id = $hkRequirementsModel->addRequirement($requirementData);

               if (!$hkr_id) {
                    throw new \Exception('Failed to create HK requirement');
               }

               // Prepare materials data
               $materialsData = [];
               foreach ($requestData->materials as $material) {
                    if (!empty($material->hk_material) && !empty($material->quantity)) {
                         $materialsData[] = [
                              'hkr_id' => $hkr_id,
                              'hk_material' => $material->hk_material,
                              'hk_make' => $material->hk_make ?? '',
                              'hk_price' => floatval($material->hk_price ?? 0),
                              'quantity' => $material->quantity,
                              'amount' => floatval($material->amount ?? 0)
                         ];
                    }
               }

               // Insert material details
               if (!empty($materialsData)) {
                    $result = $hkDetailsModel->insertBatch($materialsData);
                    if (!$result) {
                         throw new \Exception('Failed to add material details');
                    }
               }

               // Handle file uploads
               $filesData = [];
               foreach ($files as $fieldName => $file) {
                    // Check if $file is an array of UploadedFile objects
                    if (is_array($file)) {
                         foreach ($file as $subFile) {
                              $this->processFileUpload($subFile, $hkr_id, $filesData);
                         }
                    } else {
                         $this->processFileUpload($file, $hkr_id, $filesData);
                    }
               }

               if (!empty($filesData)) {
                    $result = $filesModel->insertBatch($filesData);
                    if (!$result) {
                         throw new \Exception('Failed to add files');
                    }
               }

               $db->transComplete();

               if ($db->transStatus() === false) {
                    throw new \Exception('Transaction failed');
               }

               // Call WhatsApp notification after successful transaction
               try {
                    log_message('error', "Attempting to send WhatsApp: Month={$forMonth}, Branch={$branchName}, Amount={$total_amount}");
                    $whatsappController = new \App\Controllers\WhatsappController();
                    $whatsappController->createWhatsappMessageForAdmin($forMonth, $branchName, $total_amount);
               } catch (\Exception $e) {
                    // Log error but don't fail the request since requirement was created successfully
                    log_message('error', 'Failed to send WhatsApp message: ' . $e->getMessage());
               }

               return $this->respond([
                    'status' => true,
                    'message' => 'HK Requirement created successfully',
                    'data' => ['hkr_id' => $hkr_id]
               ], 201);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to create HK requirement: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Process individual file upload
      */
     private function processFileUpload($file, $hkr_id, &$filesData)
     {
          log_message('error', 'Processing file: ' . $file->getName());
          log_message('error', 'Temporary file path: ' . $file->getTempName());

          if ($file->isValid() && !$file->hasMoved()) {
               log_message('error', 'File is valid and ready to move.');

               // Rename and move the file to the secure_files directory
               $newFileName = $file->getRandomName();
               $uploadPath = WRITEPATH . 'uploads/secure_files';
               log_message('error', 'Resolved upload path: ' . $uploadPath);

               // Ensure the directory exists
               if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                    log_message('error', 'Created upload directory: ' . $uploadPath);
               }

               try {
                    $file->move($uploadPath, $newFileName);
                    log_message('error', 'File moved successfully: ' . $uploadPath . '/' . $newFileName);

                    // Save file metadata in the database (extended metadata)
                    $userDetailsForFile = $this->validateAuthorization();
                    $uploadedBy = $userDetailsForFile->emp_code ?? null;

                    $filesData[] = [
                         'file_name' => $newFileName,
                         'original_name' => $file->getClientName(),
                         'mime_type' => $file->getClientMimeType(),
                         'file_size' => $file->getSize(),
                         'hkr_id' => $hkr_id,
                         'uploaded_by' => $uploadedBy,
                         'createdDTM' => date('Y-m-d H:i:s')
                    ];
               } catch (\Exception $e) {
                    log_message('error', 'Failed to move file: ' . $e->getMessage());
                    throw $e;
               }
          } else {
               log_message('error', 'File upload failed: ' . $file->getErrorString());
               throw new \Exception('File upload failed: ' . $file->getErrorString());
          }
     }


     public function updateHkRequirement($hkr_id)
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          // Get the form-data fields
          $dataString = $this->request->getPost('data'); // Get the `data` field as a string
          $files = $this->request->getFiles(); // Get the uploaded files
          log_message('error', 'Files received: ' . print_r($files, true));

          // Decode the `data` field into a JSON object
          $dataString = preg_replace('/"file":\{\}/', '"file":null', $dataString);
          $requestData = json_decode($dataString);

          if (json_last_error() !== JSON_ERROR_NONE) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Invalid JSON format: ' . json_last_error_msg()
               ], 400);
          }

          // Validate required fields
          if (empty($requestData->branch_id) || empty($requestData->for_month) || empty($requestData->materials)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Missing required fields: branch_id, for_month, materials'
               ], 400);
          }

          // Validate materials data
          if (!$this->validateMaterials($requestData->materials)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Invalid materials data. Each material must have hk_material, quantity, and amount.'
               ], 400);
          }

          $hkRequirementsModel = new HkRequirementsModel();
          $hkDetailsModel = new HkDetailsModel();
          $filesModel = \Config\Database::connect()->table('files'); // Use the files table

          $db = \Config\Database::connect();
          $db->transStart();

          try {
               // Check if the requirement exists
               $existingRequirement = $hkRequirementsModel->find($hkr_id);
               if (!$existingRequirement) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'HK Requirement not found'
                    ], 404);
               }

               // Update requirement data
               $requirementData = [
                    'remarks' => $requestData->remarks ?? $existingRequirement['remarks'],
                    'status' => $requestData->status ?? $existingRequirement['status'],
                    'applied_amount' => array_reduce($requestData->materials, function ($sum, $material) {
                         return $sum + floatval($material->amount ?? 0);
                    }, 0),
                    'updated_by' => $userDetails->emp_code,
                    'updated_at' => date('Y-m-d H:i:s')
               ];

               // Validate against branch budget
               $hkBudgetModel = new HkBranchwiseBudgetModel();
               $budgetInfo = $hkBudgetModel->getBudgetByBranch($requestData->branch_id);
               $budget = $budgetInfo['budget'] ?? 0;
               if ($requirementData['applied_amount'] > $budget) {
                    $db->transRollback();
                    return $this->respond([
                         'status' => false,
                         'message' => 'Total amount (₹' . number_format($requirementData['applied_amount'], 2) . ") exceeds branch budget of ₹" . number_format($budget, 2)
                    ], 400);
               }

               $hkRequirementsModel->update($hkr_id, $requirementData);

               // Delete existing materials and insert updated materials
               $hkDetailsModel->deleteByRequirement($hkr_id);

               $materialsData = [];
               foreach ($requestData->materials as $material) {
                    if (!empty($material->hk_material) && !empty($material->quantity)) {
                         $materialsData[] = [
                              'hkr_id' => $hkr_id,
                              'hk_material' => $material->hk_material,
                              'hk_make' => $material->hk_make ?? '',
                              'hk_price' => floatval($material->hk_price ?? 0),
                              'quantity' => $material->quantity,
                              'amount' => floatval($material->amount ?? 0)
                         ];
                    }
               }

               if (!empty($materialsData)) {
                    $hkDetailsModel->insertBatch($materialsData);
               }

               // Handle file uploads
               $filesData = [];
               foreach ($files as $fieldName => $file) {
                    // Check if $file is an array of UploadedFile objects
                    if (is_array($file)) {
                         foreach ($file as $subFile) {
                              $this->processFileUpload($subFile, $hkr_id, $filesData);
                         }
                    } else {
                         $this->processFileUpload($file, $hkr_id, $filesData);
                    }
               }

               if (!empty($filesData)) {
                    $result = $filesModel->insertBatch($filesData);
                    if (!$result) {
                         throw new \Exception('Failed to add files');
                    }
               }

               $db->transComplete();

               if ($db->transStatus() === false) {
                    throw new \Exception('Transaction failed');
               }

               return $this->respond([
                    'status' => true,
                    'message' => 'HK Requirement updated successfully',
                    'data' => ['hkr_id' => $hkr_id]
               ], 200);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to update HK requirement: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Process individual file upload
      */
     // private function processFileUpload($file, $hkr_id, &$filesData)
     // {
     //      log_message('error', 'Processing file: ' . $file->getName());
     //      log_message('error', 'Temporary file path: ' . $file->getTempName());

     //      if ($file->isValid() && !$file->hasMoved()) {
     //           log_message('error', 'File is valid and ready to move.');

     //           // Rename and move the file to the secure_files directory
     //           $newFileName = $file->getRandomName();
     //           $uploadPath = WRITEPATH . 'uploads/secure_files';
     //           log_message('error', 'Resolved upload path: ' . $uploadPath);

     //           // Ensure the directory exists
     //           if (!is_dir($uploadPath)) {
     //                mkdir($uploadPath, 0777, true);
     //                log_message('error', 'Created upload directory: ' . $uploadPath);
     //           }

     //           try {
     //                $file->move($uploadPath, $newFileName);
     //                log_message('error', 'File moved successfully: ' . $uploadPath . '/' . $newFileName);

     //                // Save file metadata in the database
     //                $filesData[] = [
     //                     'file_name' => $newFileName,
     //                     'hkr_id' => $hkr_id,
     //                     'createdDTM' => date('Y-m-d H:i:s')
     //                ];
     //           } catch (\Exception $e) {
     //                log_message('error', 'Failed to move file: ' . $e->getMessage());
     //                throw $e;
     //           }
     //      } else {
     //           log_message('error', 'File upload failed: ' . $file->getErrorString());
     //           throw new \Exception('File upload failed: ' . $file->getErrorString());
     //      }
     // }

     // public function updateHkRequirement($hkr_id)
     // {
     //      $userDetails = $this->validateAuthorization();
     //      if (!$userDetails) {
     //           return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
     //      }

     //      // Get the form-data fields
     //      $dataString = $this->request->getPost('data'); // Get the `data` field as a string
     //      $files = $this->request->getFiles(); // Get the uploaded files
     //      log_message('error', 'Files received: ' . print_r($files, true));

     //      // Decode the `data` field into a JSON object
     //      $dataString = preg_replace('/"file":\{\}/', '"file":null', $dataString);
     //      $requestData = json_decode($dataString);

     //      if (json_last_error() !== JSON_ERROR_NONE) {
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Invalid JSON format: ' . json_last_error_msg()
     //           ], 400);
     //      }

     //      // Validate required fields
     //      if (empty($requestData->branch_id) || empty($requestData->for_month) || empty($requestData->materials)) {
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Missing required fields: branch_id, for_month, materials'
     //           ], 400);
     //      }

     //      // Validate materials data
     //      if (!$this->validateMaterials($requestData->materials)) {
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Invalid materials data. Each material must have hk_material, quantity, and amount.'
     //           ], 400);
     //      }

     //      $hkRequirementsModel = new HkRequirementsModel();
     //      $hkDetailsModel = new HkDetailsModel();
     //      $filesModel = \Config\Database::connect()->table('files'); // Use the files table

     //      $db = \Config\Database::connect();
     //      $db->transStart();

     //      try {
     //           // Check if the requirement exists
     //           $existingRequirement = $hkRequirementsModel->find($hkr_id);
     //           if (!$existingRequirement) {
     //                return $this->respond([
     //                     'status' => false,
     //                     'message' => 'HK Requirement not found'
     //                ], 404);
     //           }

     //           // Update requirement data
     //           $requirementData = [
     //                'remarks' => $requestData->remarks ?? $existingRequirement['remarks'],
     //                'applied_amount' => array_reduce($requestData->materials, function ($sum, $material) {
     //                     return $sum + floatval($material->amount ?? 0);
     //                }, 0),
     //                'updated_by' => $userDetails->emp_code,
     //                'updated_at' => date('Y-m-d H:i:s')
     //           ];

     //           $hkRequirementsModel->update($hkr_id, $requirementData);

     //           // Delete existing materials and insert updated materials
     //           $hkDetailsModel->deleteByRequirement($hkr_id);

     //           $materialsData = [];
     //           foreach ($requestData->materials as $material) {
     //                if (!empty($material->hk_material) && !empty($material->quantity)) {
     //                     $materialsData[] = [
     //                          'hkr_id' => $hkr_id,
     //                          'hk_material' => $material->hk_material,
     //                          'quantity' => $material->quantity,
     //                          'amount' => floatval($material->amount ?? 0)
     //                     ];
     //                }
     //           }

     //           if (!empty($materialsData)) {
     //                $hkDetailsModel->insertBatch($materialsData);
     //           }

     //           // Handle file uploads
     //           if (isset($files['files']) && $files['files'] instanceof \CodeIgniter\HTTP\Files\UploadedFile) {
     //                $file = $files['files']; // Single file object

     //                if ($file->isValid() && !$file->hasMoved()) {
     //                     // Rename and move the file to the secure_files directory
     //                     $newFileName = $file->getRandomName();
     //                     $uploadPath = WRITEPATH . 'uploads/secure_files';

     //                     // Ensure the directory exists
     //                     if (!is_dir($uploadPath)) {
     //                          mkdir($uploadPath, 0777, true);
     //                     }

     //                     try {
     //                          $file->move($uploadPath, $newFileName);

     //                          // Save file metadata in the database
     //                          $filesData = [
     //                               'file_name' => $newFileName,
     //                               'hkr_id' => $hkr_id,
     //                               'createdDTM' => date('Y-m-d H:i:s')
     //                          ];
     //                          $filesModel->insert($filesData);
     //                     } catch (\Exception $e) {
     //                          log_message('error', 'Failed to move file: ' . $e->getMessage());
     //                          return $this->respond([
     //                               'status' => false,
     //                               'message' => 'Failed to upload file: ' . $e->getMessage()
     //                          ], 500);
     //                     }
     //                } else {
     //                     log_message('error', 'File upload failed: ' . $file->getErrorString());
     //                     return $this->respond([
     //                          'status' => false,
     //                          'message' => 'File upload failed: ' . $file->getErrorString()
     //                     ], 400);
     //                }
     //           }

     //           $db->transComplete();

     //           if ($db->transStatus() === false) {
     //                throw new \Exception('Transaction failed');
     //           }

     //           return $this->respond([
     //                'status' => true,
     //                'message' => 'HK Requirement updated successfully',
     //                'data' => ['hkr_id' => $hkr_id]
     //           ], 200);
     //      } catch (\Exception $e) {
     //           $db->transRollback();
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Failed to update HK requirement: ' . $e->getMessage()
     //           ], 500);
     //      }
     // }



     public function getHkRequirements()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $role = $userDetails->role;
          $emp_code = $userDetails->emp_code;

          $requestData = $this->request->getJSON();

          $filters = [
               'month' => $requestData->month ?? '',
               'branch_id' => $requestData->branch_id ?? '',
               'cluster_id' => $requestData->cluster_id ?? '',
               'status' => $requestData->status ?? ''
          ];

          log_message('error', 'User role: ' . $role);
          log_message('error', 'Filters: ' . print_r($filters, true));

          $hkRequirementsModel = new HkRequirementsModel();

          try {
               // For non-admin roles, filter by user's allowed branches
               if ($role === 'BM') {
                    $deptModel = new DeptModel();
                    $userMap = $deptModel->getUserMap($emp_code);

                    log_message('error', 'User map: ' . print_r($userMap, true));

                    if (!empty($userMap) && isset($userMap[0]['branches'])) {
                         $userBranchList = array_filter(array_map('trim', explode(',', $userMap[0]['branches'])));

                         log_message('error', 'User branch list: ' . print_r($userBranchList, true));

                         // If specific branch is not selected, use user's branches
                         if (empty($filters['branch_id']) || $filters['branch_id'] == '0') {
                              $filters['user_branches'] = $userBranchList;
                         }
                    }
               }

               log_message('error', 'Final filters: ' . print_r($filters, true));

               $requirements = $hkRequirementsModel->getRequirementsWithDetails($filters);

               log_message('error', 'Requirements found: ' . count($requirements));
               log_message('error', 'Requirements data: ' . print_r($requirements, true));

               return $this->respond([
                    'status' => true,
                    'message' => 'HK Requirements retrieved successfully',
                    'data' => $requirements
               ], 200);
          } catch (\Exception $e) {
               log_message('error', 'Error: ' . $e->getMessage());
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve requirements: ' . $e->getMessage()
               ], 500);
          }
     }



     /**
      * Get HK requirements list with filters
      */
     // public function getHkRequirements()
     // {
     //      $userDetails = $this->validateAuthorization();
     //      if (!$userDetails) {
     //           return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
     //      }

     //      $role = $userDetails->role;
     //      $emp_code = $userDetails->emp_code;

     //      $requestData = $this->request->getJSON();

     //      $filters = [
     //           'month' => $requestData->month ?? '',
     //           'branch_id' => $requestData->branch_id ?? '',
     //           'cluster_id' => $requestData->cluster_id ?? '',
     //           'status' => $requestData->status ?? ''
     //      ];

     //      $hkRequirementsModel = new HkRequirementsModel();

     //      try {
     //           // For non-admin roles, filter by user's allowed branches
     //           if ($role !== 'ADMIN') {
     //                $deptModel = new DeptModel();
     //                $userMap = $deptModel->getUserMap($emp_code);

     //                if (!empty($userMap) && isset($userMap[0]['branches'])) {
     //                     $userBranchList = array_filter(array_map('trim', explode(',', $userMap[0]['branches'])));

     //                     // If specific branch is not selected, use user's branches
     //                     if (empty($filters['branch_id'])) {
     //                          // We'll need to modify the model to handle multiple branch filtering
     //                          $filters['user_branches'] = $userBranchList;
     //                     }
     //                }
     //           }

     //           $requirements = $hkRequirementsModel->getRequirementsWithDetails($filters);

     //           return $this->respond([
     //                'status' => true,
     //                'message' => 'HK Requirements retrieved successfully',
     //                'data' => $requirements
     //           ], 200);
     //      } catch (\Exception $e) {
     //           return $this->respond([
     //                'status' => false,
     //                'message' => 'Failed to retrieve requirements: ' . $e->getMessage()
     //           ], 500);
     //      }
     // }

     /**
      * Get HK requirement details with materials
      */



     public function getHkRequirementDetails($hkr_id)
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkDetailsModel = new HkDetailsModel();
          $hkRequirementsModel = new HkRequirementsModel();
          $filesModel = \Config\Database::connect()->table('files'); // Use the files table

          try {
               // Get requirement info
               $requirement = $hkRequirementsModel->find($hkr_id);
               if (!$requirement) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'HK Requirement not found'
                    ], 404);
               }

               // Get requirement details with materials
               $details = $hkDetailsModel->getDetailsByRequirement($hkr_id);

               // Get associated files and format them with "file_name" key
               $files = $filesModel->select('file_name')->where('hkr_id', $hkr_id)->get()->getResultArray();
               $formattedFiles = array_map(function ($file) {
                    return ['file_name' => $file['file_name']];
               }, $files);

               // Add materials and files to the requirement
               $requirement['materials'] = $details;
               $requirement['files'] = $formattedFiles;

               return $this->respond([
                    'status' => true,
                    'message' => 'HK Requirement details retrieved successfully',
                    'data' => $requirement
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve requirement details: ' . $e->getMessage()
               ], 500);
          }
     }

     public function getHkRequirementDetailsByMonthAndBranch()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          if (empty($requestData->for_month) || empty($requestData->branch_id)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'for_month and branch_id are required'
               ], 400);
          }

          $hkRequirementsModel = new HkRequirementsModel();

          try {
               $requirement = $hkRequirementsModel->getRequirementByMonthAndBranch($requestData->for_month, $requestData->branch_id);

               return $this->respond([
                    'status' => true,
                    'message' => 'HK Requirement retrieved successfully',
                    'data' => $requirement
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve requirement: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Update HK requirement status
      */
     public function updateHkRequirementStatus($hkr_id)
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }
          $user = $userDetails->emp_code;

          $requestData = $this->request->getJSON();

          if (empty($requestData->status)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Status is required'
               ], 400);
          }

          $hkRequirementsModel = new HkRequirementsModel();

          try {
               $approved_by = $user;
               $admin_remarks = $requestData->admin_remarks ?? null;
               $result = $hkRequirementsModel->updateStatus($hkr_id, $requestData->status, $approved_by, $admin_remarks);
               if ($result) {
                    $whatsappController = new \App\Controllers\WhatsappController();
                    $requirement = (new HkRequirementsModel())->find($hkr_id);
                    $forMonth = $requirement['for_month'];
                    $created_by = $requirement['created_by'];
                    $status = $requestData->status;
                    $branchData = $hkRequirementsModel->getBranchById($requirement['branch_id']);
                    $branchName = $branchData['branch_name'] ?? 'Unknown Branch';
                    $whatsappController->createWhatsappMessageForBM($forMonth, $branchName, $status, $created_by);

                    return $this->respond([
                         'status' => true,
                         'message' => 'HK Requirement status updated successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to update status'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to update status: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Delete HK requirement (soft delete)
      */
     public function deleteHkRequirement($hkr_id)
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkRequirementsModel = new HkRequirementsModel();

          try {
               $result = $hkRequirementsModel->softDelete($hkr_id);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'HK Requirement deleted successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to delete requirement'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to delete requirement: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Get branch budget
      */
     public function getBranchBudget($branch_id)
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $budget = $hkBudgetModel->getBudgetByBranch($branch_id);

               return $this->respond([
                    'status' => true,
                    'message' => 'Branch budget retrieved successfully',
                    'data' => $budget
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve budget: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Run reconciliation job (admin only)
      */
     public function runReconciliation()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          // Only allow Admin / Super Admin
          if (!in_array($userDetails->role, ['ADMIN', 'SUPER_ADMIN'])) {
               return $this->respond(['status' => false, 'message' => 'Forbidden'], 403);
          }

          $db = \Config\Database::connect();
          try {
               $db->simpleQuery("CALL sp_reconcile_hk_balances()");
               return $this->respond(['status' => true, 'message' => 'Reconciliation completed'], 200);
          } catch (\Exception $e) {
               return $this->respond(['status' => false, 'message' => 'Reconciliation failed: ' . $e->getMessage()], 500);
          }
     }

     private function validateMaterials($materials)
     {
          foreach ($materials as $material) {
               if (empty($material->hk_material) || empty($material->quantity) || !isset($material->amount)) {
                    return false;
               }

               if (!is_numeric($material->quantity) || !is_numeric($material->amount)) {
                    return false;
               }
          }
          return true;
     }

     //     create new branch budget
     public function createBranchBudget()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (empty($requestData->branch_id) || !isset($requestData->budget)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'branch_id and budget are required'
               ], 400);
          }



          $hkBudgetModel = new HkBranchwiseBudgetModel();
          $defaultDB = \Config\Database::connect('default');

          try {
               // Fetch cluster_id from the clusters table based on the branch_id
               $clusterInfo = $defaultDB->table('clusters')
                    ->select('cluster_id')
                    ->where("FIND_IN_SET('{$requestData->branch_id}', branches) !=", 0)
                    ->get()
                    ->getRowArray();

               if (!$clusterInfo || empty($clusterInfo['cluster_id'])) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Cluster not found for the given branch_id'
                    ], 404);
               }

               $data = [
                    'branch_id' => $requestData->branch_id,
                    'cluster_id' => $clusterInfo['cluster_id'],
                    'budget' => $requestData->budget // Use budget here
               ];

               // Insert or update the budget in hk_branchwise_budget
               $result = $hkBudgetModel->insertOrUpdateBudget($data);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Branch budget created/updated successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to create/update budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to create/update budget: ' . $e->getMessage()
               ], 500);
          }
     }

     // update branch budget
     public function updateBranchBudget($branch_id)
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (!isset($requestData->budget)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'budget is required'
               ], 400);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $data = [
                    'budget' => $requestData->budget
               ];

               // Update the budget in hk_branchwise_budget
               $result = $hkBudgetModel->updateBudgetByBranch($branch_id, $data);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Branch budget updated successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to update budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to update budget: ' . $e->getMessage()
               ], 500);
          }
     }

     // delete branch budget
     public function deleteBranchBudget($branch_id)
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }
          $hkBudgetModel = new HkBranchwiseBudgetModel();
          try {
               // Delete the budget in hk_branchwise_budget
               $result = $hkBudgetModel->deleteBudgetByBranch($branch_id);
               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Branch budget deleted successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to delete budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to delete budget: ' . $e->getMessage()
               ], 500);
          }
     }

     // get budget for all branches
     public function getAllBranchBudgets()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $budgets = $hkBudgetModel->getAllBudgets();

               return $this->respond([
                    'status' => true,
                    'message' => 'All branch budgets retrieved successfully',
                    'data' => $budgets
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve budgets: ' . $e->getMessage()
               ], 500);
          }
     }



     /**
      * Get cluster from branch ID
      */
     private function getClusterFromBranch($branch_id)
     {
          $defaultDB = \Config\Database::connect('default');

          return $defaultDB->table('clusters')
               ->select('cluster_id, cluster')
               ->where("FIND_IN_SET('{$branch_id}', branches) !=", 0)
               ->get()
               ->getRowArray() ?? [];
     }

     /**
      * Get user's requirements (for BM role)
      */
     public function getMyHkRequirements()
     {
          $userDetails = $this->validateAuthorization();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          $filters = [
               'month' => $requestData->month ?? '',
               'status' => $requestData->status ?? ''
          ];

          $hkRequirementsModel = new HkRequirementsModel();

          try {
               $requirements = $hkRequirementsModel->getRequirementsByEmployee($userDetails->emp_code, $filters);

               return $this->respond([
                    'status' => true,
                    'message' => 'My HK Requirements retrieved successfully',
                    'data' => $requirements
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve requirements: ' . $e->getMessage()
               ], 500);
          }
     }
}

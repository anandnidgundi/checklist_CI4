<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PestControlModel;

class  PestControl extends BaseController
{
     use ResponseTrait;
     protected $pestControlModel;
     public function __construct()
     {
          $this->pestControlModel = new PestControlModel();
     }

     public function list()
     {
          $data = $this->pestControlModel
               ->orderBy('id', 'DESC')
               ->findAll();

          return $this->respond([
               'status' => true,
               'data' => $data
          ]);
     }


     public function show($id)
     {
          $record = $this->pestControlModel->find($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }


          $record['files'] = $this->pestControlModel->getFiles($id);


          return $this->respond([
               'status' => true,
               'data' => $record
          ]);
     }


     public function delete($id)
     {
          $record = $this->pestControlModel->find($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }


          $this->pestControlModel->delete($id);


          return $this->respond([
               'status' => true,
               'message' => 'Deleted successfully'
          ]);
     }



     public function create()
     {
          try {
               // -------------------------
               // 1. GET JSON DATA
               // -------------------------
               $jsonData = $this->request->getPost('data');
               $data = json_decode($jsonData, true);

               if (!$data) {
                    return $this->fail('Invalid JSON data');
               }


               // -------------------------
               // 2. INSERT BMW FORM
               // -------------------------

               if (!$this->pestControlModel->insert($data)) {

                    return $this->respond([
                         'status' => false,
                         'errors' => $this->pestControlModel->errors(),
                    ], 422);
               }
               $insertId = $this->pestControlModel->getInsertID();


               // -------------------------
               // 3. DATABASE CONNECTION
               // -------------------------

               $db = db_connect();


               // -------------------------
               // 4. UPLOAD FILES
               // -------------------------
               $uploadedFiles = 0;
               $files = $this->request->getFiles();
               if (isset($files['files'])) {
                    $fileArray = $files['files'];
                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }
                    $uploadPath = FCPATH . 'uploads/pest_control/';
                    if (!is_dir($uploadPath)) {
                         mkdir($uploadPath, 0777, true);
                    }

                    foreach ($fileArray as $file) {
                         if ($file->isValid() && !$file->hasMoved()) {
                              $newName = $file->getRandomName();
                              $file->move(
                                   $uploadPath,
                                   $newName
                              );
                              $db->table('files')->insert([
                                   'file_name' => $newName,
                                   'pest_control_id' => $insertId,
                                   'service_id' => 'PEST_CONTROL',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);
                              $uploadedFiles++;
                         }
                    }
               }

               // -------------------------
               // 5. CREATE LOG
               // -------------------------
               try {
                    $lm = new \App\Models\LogsModel();
                    $lm->insertLog([
                         'uri' => $this->request
                              ->getURI()
                              ->getPath(),
                         'method' =>
                         $this->request
                              ->getMethod(),
                         'params' => [
                              'pest_control_id' => $insertId,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'PEST_CONTROL',
                              'submission_type' => 'pest_control_create'
                         ],
                         'ip_address' =>
                         $this->request
                              ->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createPestControl',
                         'entity_type' => 'Facade_form',
                         'entity_id' => (string)$insertId,
                         'user_id' =>
                         $data['audited_by_emp_code'] ?? null
                    ]);
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'Facade_create log failed: ' . $e->getMessage()
                    );
               }

               // -------------------------
               // 6. RESPONSE
               // -------------------------

               return $this->respond([
                    'status' => true,
                    'message' => 'Pest Control created successfully',
                    'id' => $insertId,
                    'files_uploaded' => $uploadedFiles

               ]);
          } catch (\Exception $e) {
               log_message('error', $e);

               return $this->respond([
                    'status' => false,
                    'message' => $e->getMessage(),
                    'line' => $e->getLine(),
                    'file' => $e->getFile(),
               ], 500);
          }
     }




     public function update($id)
     {

          try {
               $jsonData = $this->request->getPost('data');
               $data = json_decode($jsonData, true);

               if (!$data) {
                    return $this->fail(
                         'Invalid JSON data'
                    );
               }

               $existing = $this->pestControlModel->find($id);

               if (!$existing) {
                    return $this->failNotFound(
                         'Pest Control   not found'
                    );
               }

               if (!$this->pestControlModel->update($id, $data)) {
                    return $this->fail(
                         $this->pestControlModel->errors()
                    );
               }


               $db = db_connect();

               /*
               DELETE OLD FILES
               */
               $deletedFiles = $data['deleted_files'] ?? [];

               if (!empty($deletedFiles)) {
                    foreach ($deletedFiles as $fileId) {
                         $fileRow = $db->table('files')
                              ->where('file_id', $fileId)
                              ->where(
                                   'pest_control_id',
                                   $id
                              )
                              ->where(
                                   'service_id',
                                   'PEST_CONTROL'
                              )
                              ->get()
                              ->getRowArray();

                         if ($fileRow) {
                              $filePath =
                                   FCPATH .
                                   'uploads/pest_control/' .
                                   $fileRow['file_name'];

                              if (file_exists($filePath)) {
                                   unlink($filePath);
                              }

                              $db->table('files')
                                   ->where(
                                        'file_id',
                                        $fileId
                                   )
                                   ->delete();
                         }
                    }
               }

               /*
               UPLOAD NEW FILES
               */
               $uploadedFiles = 0;
               $files = $this->request->getFiles();

               if (isset($files['files'])) {
                    $fileArray = $files['files'];
                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }
                    $uploadPath =
                         FCPATH .
                         'uploads/pest_control/';
                    if (!is_dir($uploadPath)) {
                         mkdir(
                              $uploadPath,
                              0777,
                              true
                         );
                    }
                    foreach ($fileArray as $file) {
                         if (
                              $file->isValid() &&
                              !$file->hasMoved()
                         ) {
                              $newName =
                                   $file->getRandomName();
                              $file->move(
                                   $uploadPath,
                                   $newName
                              );
                              $db->table('files')->insert([
                                   'file_name' => $newName,
                                   'pest_control_id' => $id,
                                   'service_id' => 'PEST_CONTROL',
                                   'createdDTM' => date(
                                        'Y-m-d H:i:s'
                                   )
                              ]);
                              $uploadedFiles++;
                         }
                    }
               }
               unset($data['deleted_files']);
               return $this->respond([
                    'status' => true,
                    'message' => 'Pest Control updated successfully',
                    'id' => $id,
                    'files_uploaded' => $uploadedFiles
               ]);
          } catch (\Exception $e) {
               return $this->fail(
                    $e->getMessage()
               );
          }
     }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\waterTankCleaningModel;

class WaterTankCleaning extends BaseController
{
     use ResponseTrait;
     protected $waterTankCleaningModel;
     protected $db;
     public function __construct()
     {
          $this->waterTankCleaningModel = new waterTankCleaningModel();
          $this->db = db_connect();
     }

     public function getFiles($id)
     {

          // get files data from files table with ac_form_id as $id
          $files = $this->db->table('files')->where('drinking_water_id', $id)->get()->getResultArray();
          return $files;
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
               if (!$this->waterTankCleaningModel->insert($data)) {
                    return $this->fail(
                         $this->waterTankCleaningModel->errors()
                    );
               }

               $insertId = $this->waterTankCleaningModel->getInsertID();


               // -------------------------
               // 3. DATABASE CONNECTION
               // -------------------------
               $db = db_connect();


               // -------------------------
               // 4. HANDLE FILE UPLOADS
               // -------------------------
               $uploadedFiles = 0;

               $files = $this->request->getFiles();


               if (isset($files['files'])) {
                    $fileArray = $files['files'];
                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }
                    $uploadPath = FCPATH . 'uploads/WaterTankCleaning/';
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
                                   'water_tank_cleaning_id' => $insertId,
                                   'service_id' => 'WATER_TANK_CLEANING',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);
                              $uploadedFiles++;
                         }
                    }
               }




               // -------------------------
               // 6. CREATE LOG
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
                              'water_tank_cleaning_id' => $insertId,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'WATER_TANK_CLEANING',
                              'submission_type' => 'water_tank_create'
                         ],
                         'ip_address' =>
                         $this->request
                              ->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createWaterTankCleaning',
                         'entity_type' => 'water_tank_form',
                         'entity_id' => (string)$insertId,
                         'user_id' =>
                         $data['audited_by_emp_code'] ?? null
                    ]);
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'water_tank_create log failed: ' . $e->getMessage()
                    );
               }



               // -------------------------
               // 7. RESPONSE
               // -------------------------

               return $this->respond([
                    'status' => true,
                    'message' => 'Water Tank Checklist created successfully',
                    'id' => $insertId,
                    'files_uploaded' => $uploadedFiles

               ]);
          } catch (\Exception $e) {

               return $this->fail(
                    $e->getMessage()
               );
          }
     }

     public function update($id)
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
               // 2. CHECK RECORD
               // -------------------------
               $existing = $this->waterTankCleaningModel->find($id);

               if (!$existing) {
                    return $this->failNotFound('Water Tank Cleaning Checklist not found');
               }

               /*
        |----------------------------------------------------------
        | REMOVE NON-TABLE FIELD
        |----------------------------------------------------------
        */
               $deletedFiles = $data['deleted_files'] ?? [];
               unset($data['deleted_files']);

               // -------------------------
               // 3. UPDATE FORM
               // -------------------------
               if (!$this->waterTankCleaningModel->update($id, $data)) {
                    return $this->fail(
                         $this->waterTankCleaningModel->errors()
                    );
               }

               // -------------------------
               // 4. DATABASE CONNECTION
               // -------------------------
               $db = db_connect();

               /*
        |----------------------------------------------------------
        | DELETE EXISTING FILES
        |----------------------------------------------------------
        */
               if (!empty($deletedFiles)) {

                    foreach ($deletedFiles as $fileId) {

                         $fileRow = $db->table('files')
                              ->where('file_id', $fileId)
                              ->where('water_tank_cleaning_id', $id)
                              ->where('service_id', 'WATER_TANK_CLEANING')
                              ->get()
                              ->getRowArray();

                         if ($fileRow) {

                              $filePath = FCPATH . 'uploads/WaterTankCleaning/' . $fileRow['file_name'];

                              if (file_exists($filePath)) {
                                   @unlink($filePath);
                              }

                              $db->table('files')
                                   ->where('file_id', $fileId)
                                   ->delete();
                         }
                    }
               }

               // -------------------------
               // 5. HANDLE NEW FILE UPLOADS
               // -------------------------
               $uploadedFiles = 0;

               $files = $this->request->getFiles();

               if (isset($files['files'])) {

                    $fileArray = $files['files'];

                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }

                    $uploadPath = FCPATH . 'uploads/WaterTankCleaning/';

                    if (!is_dir($uploadPath)) {
                         mkdir($uploadPath, 0777, true);
                    }

                    foreach ($fileArray as $file) {

                         if ($file->isValid() && !$file->hasMoved()) {

                              $newName = $file->getRandomName();

                              $file->move($uploadPath, $newName);

                              $db->table('files')->insert([
                                   'file_name' => $newName,
                                   'water_tank_cleaning_id' => $id,
                                   'service_id' => 'WATER_TANK_CLEANING',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);

                              $uploadedFiles++;
                         }
                    }
               }

               // -------------------------
               // 6. CREATE LOG
               // -------------------------
               try {

                    $lm = new \App\Models\LogsModel();

                    $lm->insertLog([
                         'uri' => $this->request->getURI()->getPath(),
                         'method' => $this->request->getMethod(),
                         'params' => [
                              'water_tank_cleaning_id' => $id,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'WATER_TANK_CLEANING',
                              'submission_type' => 'water_tank_update'
                         ],
                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 200,
                         'action' => 'updateWaterTankCleaning',
                         'entity_type' => 'water_tank_form',
                         'entity_id' => (string)$id,
                         'user_id' => $data['audited_by_emp_code'] ?? null
                    ]);
               } catch (\Throwable $e) {

                    log_message(
                         'error',
                         'Water Tank Cleaning update log failed: ' . $e->getMessage()
                    );
               }

               // -------------------------
               // 7. RESPONSE
               // -------------------------
               return $this->respond([
                    'status' => true,
                    'message' => 'Water Tank Checklist updated successfully',
                    'id' => $id,
                    'files_uploaded' => $uploadedFiles
               ]);
          } catch (\Exception $e) {

               return $this->fail($e->getMessage());
          }
     }

     public function delete($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          if (empty($id)) {
               return $this->respond([
                    'status'  => false,
                    'message' => 'Vendor ID is required'
               ], 400);
          }

          $vendor = $this->waterTankCleaningModel->find($id);

          if (!$vendor) {
               return $this->respond([
                    'status'  => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          if ($this->waterTankCleaningModel->delete($id)) {
               return $this->respond([
                    'status'  => true,
                    'message' => 'Vendor deleted successfully'
               ]);
          }

          return $this->respond([
               'status'  => false,
               'message' => 'Failed to delete vendor'
          ], 500);
     }



     /**
      * READ ALL
      */
     public function list()
     {
          $data = $this->waterTankCleaningModel
               ->orderBy('id', 'DESC')
               ->findAll();

          return $this->respond([
               'status' => true,
               'data' => $data
          ]);
     }

     public function show($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          if (empty($id)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor ID is required'
               ], 400);
          }

          $vendor = $this->waterTankCleaningModel->find($id);
          // files
          $vendor['files'] = $this->waterTankCleaningModel->getFiles($id);

          if (!$vendor) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          return $this->respond([
               'status' => true,
               'data' => $vendor
          ]);
     }
}

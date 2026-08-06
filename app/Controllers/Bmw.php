<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BmwModel;

class Bmw extends BaseController
{
     use ResponseTrait;

     protected $bmwModel;

     public function __construct()
     {
          $this->bmwModel = new BmwModel();
     }

     public function list()
     {
          $data = $this->bmwModel
               ->orderBy('id', 'DESC')
               ->findAll();
          return $this->respond([
               'status' => true,
               'data' => $data
          ]);
     }

     public function show($id)
     {
          $record = $this->bmwModel->find($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }
          $record['files'] = $this->bmwModel->getFiles($id);
          return $this->respond([
               'status' => true,
               'data' => $record
          ]);
     }

     public function delete($id)
     {
          $record = $this->bmwModel->find($id);
          if (!$record) {
               return $this->failNotFound('Record not found');
          }
          $this->bmwModel->delete($id);
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
               if (!$this->bmwModel->insert($data)) {
                    return $this->fail(
                         $this->bmwModel->errors()
                    );
               }

               $insertId = $this->bmwModel->getInsertID();


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
                    $uploadPath = FCPATH . 'uploads/bmw';
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
                                   'bmw_form_id' => $insertId,
                                   'service_id' => 'BMW',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);
                              $uploadedFiles++;
                         }
                    }
               }



               // -------------------------
               // 5. CREATE PDF (OPTIONAL)
               // -------------------------

               /*
            $pdfBinary =
                \App\Services\PdfService::buildBmwPdf($insertId);


            $pdfDir = WRITEPATH . 'uploads/secure_files/';


            if (!is_dir($pdfDir)) {
                mkdir($pdfDir,0777,true);
            }


            $pdfName = 'bmw_'.$insertId.'.pdf';

            $pdfPath = $pdfDir.$pdfName;


            file_put_contents(
                $pdfPath,
                $pdfBinary
            );


            $db->table('files')->insert([
                'file_name'=>$pdfName,
                'bmw_form_id'=>$insertId,
                'service_id'=>'BMW_PDF',
                'createdDTM'=>date('Y-m-d H:i:s')
            ]);
            */


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
                              'bmw_id' => $insertId,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'BMW',
                              'submission_type' => 'bmw_create'
                         ],
                         'ip_address' =>
                         $this->request
                              ->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createBMW',
                         'entity_type' => 'bmw_form',
                         'entity_id' => (string)$insertId,
                         'user_id' =>
                         $data['audited_by_emp_code'] ?? null
                    ]);
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'BMW log failed: ' . $e->getMessage()
                    );
               }



               // -------------------------
               // 7. RESPONSE
               // -------------------------

               return $this->respond([
                    'status' => true,
                    'message' => 'BMW Checklist created successfully',
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
               // 2. CHECK BMW RECORD
               // -------------------------
               $existing = $this->bmwModel->find($id);

               if (!$existing) {
                    return $this->failNotFound(
                         'BMW Checklist not found'
                    );
               }


               // -------------------------
               // 3. UPDATE BMW FORM
               // -------------------------
               if (!$this->bmwModel->update($id, $data)) {
                    return $this->fail(
                         $this->bmwModel->errors()
                    );
               }


               // -------------------------
               // 4. DATABASE CONNECTION
               // -------------------------
               $db = db_connect();


               /*
        |--------------------------------------------------------------------------
        | DELETE EXISTING FILES
        |--------------------------------------------------------------------------
        */
               $deletedFiles = $data['deleted_files'] ?? [];

               if (!empty($deletedFiles)) {

                    foreach ($deletedFiles as $fileId) {

                         $fileRow = $db->table('files')
                              ->where('file_id', $fileId)
                              ->where('bmw_form_id', $id)
                              ->where('service_id', 'BMW')
                              ->get()
                              ->getRowArray();

                         if ($fileRow) {

                              $filePath = FCPATH . 'uploads/ac_services/' . $fileRow['file_name'];

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
               // 5. HANDLE FILE UPLOADS
               // -------------------------
               $uploadedFiles = 0;

               $files = $this->request->getFiles();


               if (isset($files['files'])) {
                    $fileArray = $files['files'];
                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }
                    $uploadPath = FCPATH . 'uploads/bmw';
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
                                   'bmw_form_id' => $id,
                                   'service_id' => 'BMW',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);
                              $uploadedFiles++;
                         }
                    }
               }

               /*
        |--------------------------------------------------------------------------
        | REMOVE NON-TABLE FIELD
        |--------------------------------------------------------------------------
        */
               unset($data['deleted_files']);

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
                              'bmw_id' => $id,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'BMW',
                              'submission_type' => 'bmw_update'
                         ],
                         'ip_address' =>
                         $this->request
                              ->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 200,
                         'action' => 'updateBMW',
                         'entity_type' => 'bmw_form',
                         'entity_id' => (string)$id,
                         'user_id' =>
                         $data['audited_by_emp_code'] ?? null

                    ]);
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'BMW update log failed: ' . $e->getMessage()
                    );
               }

               // -------------------------
               // 7. RESPONSE
               // -------------------------
               return $this->respond([
                    'status' => true,
                    'message' => 'BMW Checklist updated successfully',
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

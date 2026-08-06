<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DrinkingWaterModel;
use CodeIgniter\API\ResponseTrait;

class DrinkingWaterController extends BaseController
{
     use ResponseTrait;

     protected $drinkingWaterModel;
     protected $db;

     public function __construct()
     {
          $this->drinkingWaterModel = new DrinkingWaterModel();
          $this->db = db_connect();
     }

     public function getFiles($id)
     {

          // get files data from files table with ac_form_id as $id
          $files = $this->db->table('files')->where('drinking_water_id', $id)->get()->getResultArray();
          return $files;
     }

     public function getTotalCanDetailsOfVendorOfBranch($branch_id, $vendor_id)
     {
          $lastEntry = $this->drinkingWaterModel
               ->getTotalCanDetailsOfVendorOfBranch($branch_id, $vendor_id);

          if (!$lastEntry) {
               return $this->respond([
                    'status' => true,
                    'data' => [
                         'total_water_cans_supplied' => 0,
                         'total_empty_water_cans_returned' => 0,
                         'created_dtm' => null
                    ]
               ]);
          }

          return $this->respond([
               'status' => true,
               'data'   => $lastEntry
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
               // 2. CALCULATE TOTAL WATER CANS
               // -------------------------

               $lastEntry = $this->drinkingWaterModel
                    ->getTotalCanDetailsOfVendorOfBranch(
                         $data['branch_id'],
                         $data['vendor_id']
                    );

               $previousSupplied = 0;
               $previousReturned = 0;

               if ($lastEntry) {
                    $previousSupplied = (int)$lastEntry['total_water_cans_supplied'];
                    $previousReturned = (int)$lastEntry['total_empty_water_cans_returned'];
               }

               // Current supplied/returned cans
               $currentSupplied = (int)($data['water_cans_supplied'] ?? 0);
               $currentReturned = (int)($data['water_cans_returned'] ?? 0);

               // Update running totals
               $data['total_water_cans_supplied'] = $previousSupplied + $currentSupplied;
               $data['total_empty_water_cans_returned'] = $previousReturned + $currentReturned;


               // -------------------------
               // 3. INSERT Drinking Water FORM
               // -------------------------
               if (!$this->drinkingWaterModel->insert($data)) {
                    return $this->fail(
                         $this->drinkingWaterModel->errors()
                    );
               }

               $insertId = $this->drinkingWaterModel->getInsertID();

               // -------------------------
               // 4. HANDLE FILE UPLOADS
               // -------------------------
               $uploadedFiles = 0;
               $db = db_connect();
               $files = $this->request->getFiles();


               if (isset($files['files'])) {
                    $fileArray = $files['files'];
                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }
                    $uploadPath = FCPATH . 'uploads/drinking_water/';
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
                                   'drinking_water_id' => $insertId,
                                   'service_id' => 'DRINKING_WATER',
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
                \App\Services\PdfService::buildDrinking_WaterPdf($insertId);


            $pdfDir = WRITEPATH . 'uploads/secure_files/';


            if (!is_dir($pdfDir)) {
                mkdir($pdfDir,0777,true);
            }


            $pdfName = 'Drinking_Water_'.$insertId.'.pdf';

            $pdfPath = $pdfDir.$pdfName;


            file_put_contents(
                $pdfPath,
                $pdfBinary
            );


            $db->table('files')->insert([
                'file_name'=>$pdfName,
                'drinking_water_id'=>$insertId,
                'service_id'=>'Drinking_Water_PDF',
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
                              'drinking_water_id' => $insertId,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'DRINKING_WATER',
                              'submission_type' => 'Drinking_Water_create'
                         ],
                         'ip_address' =>
                         $this->request
                              ->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createDrinking_Water',
                         'entity_type' => 'Drinking_Water_form',
                         'entity_id' => (string)$insertId,
                         'user_id' =>
                         $data['audited_by_emp_code'] ?? null
                    ]);
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'Drinking_Water log failed: ' . $e->getMessage()
                    );
               }



               // -------------------------
               // 7. RESPONSE
               // -------------------------

               return $this->respond([
                    'status' => true,
                    'message' => 'Drinking Water Checklist created successfully',
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
               // 2. CHECK RECORD EXISTS
               // -------------------------
               $existing = $this->drinkingWaterModel->find($id);

               if (!$existing) {
                    return $this->failNotFound('Drinking_Water Checklist not found');
               }

               // -------------------------
               // 3. GET PREVIOUS ENTRY
               // -------------------------
               $previous = $this->drinkingWaterModel
                    ->where('branch_id', $existing['branch_id'])
                    ->where('vendor_id', $existing['vendor_id'])
                    ->where('id <', $id)
                    ->orderBy('id', 'DESC')
                    ->first();

               $previousSupplied = $previous['total_water_cans_supplied'] ?? 0;
               $previousReturned = $previous['total_empty_water_cans_returned'] ?? 0;

               // -------------------------
               // 4. CALCULATE RUNNING TOTALS
               // -------------------------
               $data['total_water_cans_supplied'] =
                    $previousSupplied + (int)$data['water_cans_supplied'];

               $data['total_empty_water_cans_returned'] =
                    $previousReturned + (int)$data['water_cans_returned'];

               // -------------------------
               // 5. UPDATE RECORD
               // -------------------------
               if (!$this->drinkingWaterModel->update($id, $data)) {
                    return $this->fail($this->drinkingWaterModel->errors());
               }

               // -------------------------
               // 6. RECALCULATE ALL SUBSEQUENT TOTALS
               // -------------------------
               $this->drinkingWaterModel->recalculateTotals(
                    $existing['branch_id'],
                    $existing['vendor_id']
               );


               // -------------------------
               // 2. CHECK Drinking_Water RECORD
               // -------------------------
               $existing = $this->drinkingWaterModel->find($id);

               if (!$existing) {
                    return $this->failNotFound(
                         'Drinking_Water Checklist not found'
                    );
               }


               // -------------------------
               // 3. UPDATE Drinking_Water FORM
               // -------------------------
               if (!$this->drinkingWaterModel->update($id, $data)) {
                    return $this->fail(
                         $this->drinkingWaterModel->errors()
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
                              ->where('drinking_water_id', $id)
                              ->where('service_id', 'DRINKING_WATER')
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
                    $uploadPath = FCPATH . 'uploads/drinking_water';
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
                                   'drinking_water_id' => $id,
                                   'service_id' => 'DRINKING_WATER',
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
                              'Drinking_Water_id' => $id,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'DRINKING_WATER',
                              'submission_type' => 'Drinking_Water_update'
                         ],
                         'ip_address' =>
                         $this->request
                              ->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 200,
                         'action' => 'updateDrinking_Water',
                         'entity_type' => 'Drinking_Water_form',
                         'entity_id' => (string)$id,
                         'user_id' =>
                         $data['audited_by_emp_code'] ?? null

                    ]);
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'Drinking_Water update log failed: ' . $e->getMessage()
                    );
               }

               // -------------------------
               // 7. RESPONSE
               // -------------------------
               return $this->respond([
                    'status' => true,
                    'message' => 'Drinking_Water Checklist updated successfully',
                    'id' => $id,
                    'files_uploaded' => $uploadedFiles

               ]);
          } catch (\Exception $e) {

               return $this->fail(
                    $e->getMessage()
               );
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

          $vendor = $this->drinkingWaterModel->find($id);

          if (!$vendor) {
               return $this->respond([
                    'status'  => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          if ($this->drinkingWaterModel->delete($id)) {
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
          $data = $this->drinkingWaterModel
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

          $vendor = $this->drinkingWaterModel->find($id);
          // files
          $vendor['files'] = $this->drinkingWaterModel->getFiles($id);

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

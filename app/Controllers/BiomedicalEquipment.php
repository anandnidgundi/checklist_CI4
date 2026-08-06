<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BiomedicalEquipmentModel;

class BiomedicalEquipment extends BaseController
{
     use ResponseTrait;
     protected $biomedicalEquipmentModel;
     public function __construct()
     {
          $this->biomedicalEquipmentModel = new BiomedicalEquipmentModel();
     }

     /**
      * CREATE
      */
     public function create()
     {
          try {

               // -------------------------
               // 1. JSON DATA
               // -------------------------
               $jsonData = $this->request->getPost('data');
               $data = json_decode($jsonData, true);

               if (!$data) {
                    return $this->fail('Invalid JSON data');
               }

               // -------------------------
               // 2. INSERT MAIN TABLE
               // -------------------------
               $insertId = $this->biomedicalEquipmentModel->insert($data);

               // -------------------------
               // 3. DB CONNECTION (USES .env automatically)
               // -------------------------
               $db = db_connect(); // ✅ uses database.default.* from .env

               // -------------------------
               // 4. HANDLE FILE UPLOAD
               // -------------------------
               $files = $this->request->getFiles();

               if (isset($files['files'])) {
                    $fileArray = $files['files'];
                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }
                    $uploadPath = FCPATH . 'uploads/biomedical_equipments';
                    if (!is_dir($uploadPath)) {
                         mkdir($uploadPath, 0777, true);
                    }
                    foreach ($fileArray as $file) {
                         if ($file->isValid() && !$file->hasMoved()) {
                              $newName = $file->getRandomName();
                              $file->move($uploadPath, $newName);
                              // -------------------------
                              // 5. INSERT INTO files TABLE
                              // -------------------------
                              $db->table('files')->insert([
                                   'file_name'  => $newName,
                                   'biomedical_equip_form_id' => $insertId,
                                   'service_id' => 'BIOMEDICAL',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);
                         }
                    }
               }

               // -------------------------
               // 4. BUILD PDF FILE
               // -------------------------

               $pdfBinary = \App\Services\PdfService::buildBioMedicalEquipmentPdf($insertId);

               // -------------------------
               // 5. SAVE PDF FILE physically
               // -------------------------

               $pdfDir = WRITEPATH . 'uploads/secure_files/';

               if (!is_dir($pdfDir)) {
                    mkdir($pdfDir, 0777, true);
               }
               $pdfFileName = 'biomedical_equipment_' . $insertId . '.pdf';
               $pdfFilePath = $pdfDir . $pdfFileName;
               file_put_contents($pdfFilePath, $pdfBinary);

               // -------------------------
               // 5. INSERT INTO files TABLE
               // -------------------------

               $db->table('files')->insert([
                    'file_name'  => $pdfFileName,
                    'biomedical_equip_form_id' => $insertId,
                    'service_id' => 'BIOMEDICAL_PDF',
                    'createdDTM' => date('Y-m-d H:i:s')
               ]);

               $pdfFileId = $db->insertID();

               //  prepare email payload

               $emailPayload = [
                    'pdf_file_id'   => $pdfFileId,
                    'pdf_file_path' => $pdfFilePath,
               ];

               // -------------------------
               // 6. LOG SUBMISSION CREATE
               // -------------------------
               try {
                    $lm = new \App\Models\LogsModel();
                    $lm->insertLog([
                         'uri' => $this->request->getURI()->getPath(),
                         'method' => $this->request->getMethod(),
                         'params' => [
                              'form_id' => $data['form_id'] ?? null,
                              'biomedical_equip_form_id' => $insertId,
                              'files_uploaded' => isset($fileArray) ? count($fileArray) : 0,
                              'service_id' => 'BIOMEDICAL',
                              'submission_type' => 'BIOMEDICAL_service_create',
                         ],
                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createBiomedicalEquipment',
                         'entity_type' => 'biomedical_equipment_form',
                         'entity_id' => (string)$insertId,
                         'user_id' => $data['created_by'] ?? null, // adjust if you store empCode/session user
                    ]);
               } catch (\Throwable $e) {
                    log_message('error', 'BiomedicalEquipment::create log failed: ' . $e->getMessage());
               }

               // -------------------------
               // 7. TRIGGER EMAIL
               // -------------------------
               try {

                    $formId = 12; // email_templates.form_id
                    $data['biomedical_equip_form_id'] = $insertId;
                    $this->triggerEmailOnSave(
                         $formId,
                         $data,
                         date('Y-m-d H:i:s'),
                         $emailPayload
                    );
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'BiomedicalEquipment::create trigger email failed: ' . $e->getMessage()
                    );
               }
               return $this->respond([
                    'status' => true,
                    'message' => 'Biomedical Equipment created successfully',
                    'id' => $insertId,
                    'files_uploaded' => isset($fileArray) ? count($fileArray) : 0
               ]);
          } catch (\Exception $e) {
               return $this->fail($e->getMessage());
          }
     }

     /**
      * READ ALL
      */
     public function list()
     {
          $data = $this->biomedicalEquipmentModel
               ->orderBy('id', 'DESC')
               ->findAll();

          return $this->respond([
               'status' => true,
               'data' => $data
          ]);
     }

     /**
      * READ SINGLE
      */
     public function show($id)
     {
          $record = $this->biomedicalEquipmentModel->find($id);
          // get files data from files table with biomedical_equip_form_id as $id
          $record['files'] = $this->biomedicalEquipmentModel->getFiles($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }

          return $this->respond([
               'status' => true,
               'data' => $record
          ]);
     }

     /**
      * UPDATE
      */
     public function update($id)
     {
          try {

               $record = $this->biomedicalEquipmentModel->find($id);

               if (!$record) {
                    return $this->failNotFound('Record not found');
               }

               $jsonData = $this->request->getPost('data');
               $data = json_decode($jsonData, true);

               if (!is_array($data)) {
                    return $this->fail('Invalid JSON data');
               }

               $db = \Config\Database::connect();

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
                              ->where('biomedical_equip_form_id', $id)
                              ->where('service_id', 'BIOMEDICAL')
                              ->get()
                              ->getRowArray();

                         if ($fileRow) {

                              $filePath = FCPATH . 'uploads/biomedical_equipments/' . $fileRow['file_name'];

                              if (file_exists($filePath)) {
                                   @unlink($filePath);
                              }

                              $db->table('files')
                                   ->where('file_id', $fileId)
                                   ->delete();
                         }
                    }
               }

               /*
        |--------------------------------------------------------------------------
        | NEW FILE UPLOADS
        |--------------------------------------------------------------------------
        */
               $files = $this->request->getFiles();

               if (isset($files['files'])) {

                    $fileArray = $files['files'];

                    if (!is_array($fileArray)) {
                         $fileArray = [$fileArray];
                    }

                    foreach ($fileArray as $file) {

                         if ($file->isValid() && !$file->hasMoved()) {

                              $newName = $file->getRandomName();

                              $file->move(
                                   FCPATH . 'uploads/biomedical_equipments',
                                   $newName
                              );

                              $db->table('files')->insert([
                                   'file_name'   => $newName,
                                   'service_id'  => 'BIOMEDICAL',
                                   'biomedical_equip_form_id'  => $id,
                                   'createdDTM'  => date('Y-m-d H:i:s')
                              ]);
                         }
                    }
               }

               /*
        |--------------------------------------------------------------------------
        | REMOVE NON-TABLE FIELD
        |--------------------------------------------------------------------------
        */
               unset($data['deleted_files']);

               /*
        |--------------------------------------------------------------------------
        | UPDATE FORM DATA
        |--------------------------------------------------------------------------
        */
               $this->biomedicalEquipmentModel->update($id, $data);

               /*
        |--------------------------------------------------------------------------
        | RETURN UPDATED FILE LIST
        |--------------------------------------------------------------------------
        */
               $updatedFiles = $db->table('files')
                    ->where('service_id', 'BIOMEDICAL')
                    ->where('biomedical_equip_form_id', $id)
                    ->get()
                    ->getResultArray();

               return $this->respond([
                    'status' => true,
                    'message' => 'Updated successfully',
                    'files' => $updatedFiles
               ]);
          } catch (\Exception $e) {

               log_message('error', $e->getMessage());

               return $this->fail($e->getMessage());
          }
     }

     /**
      * DELETE
      */
     public function delete($id)
     {
          $record = $this->biomedicalEquipmentModel->find($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }

          $this->biomedicalEquipmentModel->delete($id);

          return $this->respond([
               'status' => true,
               'message' => 'Deleted successfully'
          ]);
     }


     private function triggerEmailOnSave(
          int $formId,
          array $header = [],
          $createdDtm = null,
          array $payload = []
     ) {
          try {

               $logFile = WRITEPATH . 'logs/biomedical_email_debug.log';

               @file_put_contents(
                    $logFile,
                    "[" . date('Y-m-d H:i:s') . "] START triggerEmailOnSave form_id={$formId}\n",
                    FILE_APPEND
               );

               if ($formId <= 0) {

                    $msg = "Invalid form_id supplied";

                    log_message('error', $msg);

                    @file_put_contents(
                         $logFile,
                         "[" . date('Y-m-d H:i:s') . "] FAIL: {$msg}\n",
                         FILE_APPEND
                    );

                    return;
               }

               $db = \Config\Database::connect();

               /*
          |--------------------------------------------------------------------------
          | Load Email Template
          |--------------------------------------------------------------------------
          */
               $tmpl = $db->table('email_templates')
                    ->where('form_id', $formId)
                    ->get()
                    ->getRowArray();

               if (!$tmpl) {

                    $msg = "Email template not found for form_id={$formId}";

                    log_message('error', $msg);

                    @file_put_contents(
                         $logFile,
                         "[" . date('Y-m-d H:i:s') . "] FAIL: {$msg}\n",
                         FILE_APPEND
                    );

                    return;
               }

               @file_put_contents(
                    $logFile,
                    "[" . date('Y-m-d H:i:s') . "] Template found event_key={$tmpl['event_key']}\n",
                    FILE_APPEND
               );

               /*
          |--------------------------------------------------------------------------
          | PDF Attachment
          |--------------------------------------------------------------------------
          */
               $pdfFilePath = $payload['pdf_file_path'] ?? null;

               if (empty($pdfFilePath)) {

                    $msg = "pdf_file_path missing in payload";

                    log_message('error', $msg);

                    @file_put_contents(
                         $logFile,
                         "[" . date('Y-m-d H:i:s') . "] FAIL: {$msg}\n",
                         FILE_APPEND
                    );

                    return;
               }

               if (!is_file($pdfFilePath)) {

                    $msg = "PDF file not found at {$pdfFilePath}";

                    log_message('error', $msg);

                    @file_put_contents(
                         $logFile,
                         "[" . date('Y-m-d H:i:s') . "] FAIL: {$msg}\n",
                         FILE_APPEND
                    );

                    return;
               }

               @file_put_contents(
                    $logFile,
                    "[" . date('Y-m-d H:i:s') . "] PDF located: {$pdfFilePath}\n",
                    FILE_APPEND
               );

               /*
          |--------------------------------------------------------------------------
          | Resolve Recipients
          |--------------------------------------------------------------------------
          */
               $recipients = [];

               $customTo = trim(
                    (string)(getenv('BIOMEDICAL_EQUIPMENT_TO_EMAIL') ?: '')
               );

               if ($customTo !== '') {

                    $emails = preg_split('/[,;]+/', $customTo);

                    foreach ($emails as $email) {

                         $email = trim($email);

                         if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                              $recipients[] = $email;
                         }
                    }

                    @file_put_contents(
                         $logFile,
                         "[" . date('Y-m-d H:i:s') . "] Using BIOMEDICAL_EQUIPMENT_TO_EMAIL={$customTo}\n",
                         FILE_APPEND
                    );
               }

               /*
          |--------------------------------------------------------------------------
          | Fallback Recipients
          |--------------------------------------------------------------------------
          */
               if (empty($recipients)) {

                    if (!empty($header['branch_manager_email'])) {
                         $recipients[] = trim($header['branch_manager_email']);
                    }

                    if (!empty($header['branch_email'])) {
                         $recipients[] = trim($header['branch_email']);
                    }

                    @file_put_contents(
                         $logFile,
                         "[" . date('Y-m-d H:i:s') . "] Using header recipients\n",
                         FILE_APPEND
                    );
               }

               $recipients = array_values(
                    array_unique(
                         array_filter(
                              $recipients,
                              fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
                         )
                    )
               );

               if (empty($recipients)) {

                    $msg = "No valid recipients found";

                    log_message('error', $msg);

                    @file_put_contents(
                         $logFile,
                         "[" . date('Y-m-d H:i:s') . "] FAIL: {$msg}\n",
                         FILE_APPEND
                    );

                    return;
               }

               $to = array_shift($recipients);

               $cc = !empty($recipients)
                    ? implode(',', $recipients)
                    : null;

               @file_put_contents(
                    $logFile,
                    "[" . date('Y-m-d H:i:s') . "] TO={$to} CC={$cc}\n",
                    FILE_APPEND
               );

               /*
          |--------------------------------------------------------------------------
          | Template Variables
          |--------------------------------------------------------------------------
          */
               $vars = [
                    'form_id' => $formId,
                    'created_dtm' => $createdDtm ?? date('Y-m-d H:i:s'),
                    'branch_manager_name' => $header['branch_manager_name'] ?? '',
                    'branch_name' => $header['branch_name'] ?? '',
                    'date' => $header['audited_date'] ?? date('d-m-Y'),

                    // ✅ FIXED audited_by mapping
                    'audited_by' =>
                    $header['audited_by_name']
                         ?? $header['audited_by']
                         ?? $header['employee_name']
                         ?? $header['created_by']
                         ?? '',
               ];

               @file_put_contents(
                    $logFile,
                    "[" . date('Y-m-d H:i:s') . "] Vars=" .
                         json_encode($vars) . "\n",
                    FILE_APPEND
               );

               /*
          |--------------------------------------------------------------------------
          | Send Email
          |--------------------------------------------------------------------------
          */
               $emailController = new \App\Controllers\EmailController();

               @file_put_contents(
                    $logFile,
                    "[" . date('Y-m-d H:i:s') . "] Sending template={$tmpl['event_key']}\n",
                    FILE_APPEND
               );

               $result = $emailController->sendTemplate(
                    $tmpl['event_key'],
                    $to,
                    null,
                    $vars,
                    null,          // use template subject
                    $cc,
                    [$pdfFilePath] // attach PDF
               );

               @file_put_contents(
                    $logFile,
                    "[" . date('Y-m-d H:i:s') . "] SEND RESULT=" .
                         print_r($result, true) . "\n",
                    FILE_APPEND
               );

               log_message(
                    'info',
                    'BIOMEDICAL Equipment Email Sent. Result=' .
                         print_r($result, true)
               );
          } catch (\Throwable $e) {

               $msg = $e->getMessage();

               log_message(
                    'error',
                    'triggerEmailOnSave failed: ' . $msg
               );

               @file_put_contents(
                    WRITEPATH . 'logs/biomedical_email_debug.log',
                    "[" . date('Y-m-d H:i:s') . "] EXCEPTION: " .
                         $msg .
                         "\nTRACE:\n" .
                         $e->getTraceAsString() .
                         "\n\n",
                    FILE_APPEND
               );
          }
     }
}

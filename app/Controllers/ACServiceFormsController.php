<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ACServicesFormsModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchesModel;
use App\Models\DeptModel;

class ACServiceFormsController extends BaseController
{
     use ResponseTrait;

     protected $acModel;
     protected $branchesModel;
     protected $deptModel;

     public function __construct()
     {
          $this->acModel = new ACServicesFormsModel();
          $this->branchesModel = new BranchesModel();
          $this->deptModel = new DeptModel();
          $db = \Config\Database::connect();
          $secondDB = \Config\Database::connect('secondary');
     }

     // getCompletedServicesInSelectedYear()

     public function getCompletedServicesInSelectedYear($branchId, $vendorId, $year)
     {
          $result = $this->acModel->getCompletedServicesInSelectedYear($branchId, $vendorId, $year);
          return $this->response->setJSON([
               'status' => true,
               'message' => 'Completed services fetched successfully.',
               'data' => $result
          ]);
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
               $insertId = $this->acModel->insert($data);

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
                    $uploadPath = FCPATH . 'uploads/ac_services';
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
                                   'file_name' => $newName,
                                   'ac_form_id' => $insertId,
                                   'service_id' => 'AC',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);
                         }
                    }
               }

               // -------------------------
               // 4. BUILD PDF FILE
               // -------------------------

               $pdfBinary = \App\Services\PdfService::buildAcServicePdf($insertId);

               // -------------------------
               // 5. SAVE PDF FILE physically
               // -------------------------

               $pdfDir = WRITEPATH . 'uploads/secure_files/';

               if (!is_dir($pdfDir)) {
                    mkdir($pdfDir, 0777, true);
               }
               $pdfFileName = 'ac_service_' . $insertId . '.pdf';
               $pdfFilePath = $pdfDir . $pdfFileName;
               file_put_contents($pdfFilePath, $pdfBinary);

               // -------------------------
               // 5. INSERT INTO files TABLE
               // -------------------------

               $db->table('files')->insert([
                    'file_name' => $pdfFileName,
                    'ac_form_id' => $insertId,
                    'service_id' => 'AC_PDF',
                    'createdDTM' => date('Y-m-d H:i:s')
               ]);

               $pdfFileId = $db->insertID();

               //  prepare email payload

               $emailPayload = [
                    'pdf_file_id' => $pdfFileId,
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
                              'ac_form_id' => $insertId,
                              'files_uploaded' => isset($fileArray) ? count($fileArray) : 0,
                              'service_id' => 'AC',
                              'submission_type' => 'ac_service_create',
                         ],
                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createACService',
                         'entity_type' => 'ac_service_form',
                         'entity_id' => (string) $insertId,
                         'user_id' => $data['created_by'] ?? null, // adjust if you store empCode/session user
                    ]);
               } catch (\Throwable $e) {
                    log_message('error', 'ACService::create log failed: ' . $e->getMessage());
               }

               // -------------------------
               // 7. TRIGGER EMAIL
               // -------------------------
               try {

                    $formId = 11; // email_templates.form_id
                    $data['ac_form_id'] = $insertId;
                    $this->triggerEmailOnSave(
                         $formId,
                         $data,
                         date('Y-m-d H:i:s'),
                         $emailPayload
                    );
               } catch (\Throwable $e) {
                    log_message(
                         'error',
                         'ACService::create trigger email failed: ' . $e->getMessage()
                    );
               }
               return $this->respond([
                    'status' => true,
                    'message' => 'AC Service created successfully',
                    'id' => $insertId,
                    'files_uploaded' => isset($fileArray) ? count($fileArray) : 0
               ]);
          } catch (\Exception $e) {
               return $this->fail($e->getMessage());
          }
     }



     public function list()
     {
          /*
     * =========================================================
     * 1. Authorization
     * =========================================================
     */
          $auth = $this->validateAuthorizationNew();

          if (empty($auth)) {
               return $this->respond([
                    'message' => 'Unauthorized'
               ], 401);
          }

          /*
     * Authorization can also be stdClass.
     */
          if (is_object($auth)) {
               $auth = (array) $auth;
          }

          $empCode = $auth['emp_code'] ?? null;

          if (empty($empCode)) {
               return $this->respond([
                    'message' => 'Employee code not found'
               ], 401);
          }


          /*
     * =========================================================
     * 2. Get branches mapped to logged-in employee
     * =========================================================
     */
          $deptModel = new DeptModel();

          $userBranchMap = $deptModel->getUserMapBranches($empCode);

          if (empty($userBranchMap)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }

          /*
     * Normalize result to array.
     *
     * getUserMapBranches() may return:
     *
     * [
     *     stdClass Object(...)
     * ]
     *
     * OR
     *
     * [
     *     [...]
     * ]
     */
          if (is_object($userBranchMap)) {
               $userBranchMap = [$userBranchMap];
          }

          /*
     * Normalize first row.
     */
          $firstBranchMap = $userBranchMap[0] ?? null;

          if (is_object($firstBranchMap)) {
               $firstBranchMap = (array) $firstBranchMap;
          }

          if (!is_array($firstBranchMap)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }

          /*
     * Get branch mapping.
     */
          $mappedBranches = $firstBranchMap['branches'] ?? '';

          if (empty($mappedBranches)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 3. Get actual branches assigned to employee
     * =========================================================
     */
          $userBranches = $deptModel->getUserBranches($mappedBranches);

          if (empty($userBranches)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }

          /*
     * If one stdClass is returned instead of an array
     * containing stdClass objects, normalize it.
     */
          if (is_object($userBranches)) {
               $userBranches = [$userBranches];
          }


          /*
     * =========================================================
     * 4. Extract allowed Branch SysNos
     * =========================================================
     */
          $allowedSysNos = [];

          foreach ($userBranches as $branch) {

               /*
         * Convert stdClass -> array.
         */
               if (is_object($branch)) {
                    $branch = (array) $branch;
               }

               if (!is_array($branch)) {
                    continue;
               }

               /*
         * IMPORTANT:
         *
         * Prefer SysNo because BMW branch_id contains
         * Branches.SysNo.
         */
               if (
                    isset($branch['SysNo']) &&
                    $branch['SysNo'] !== ''
               ) {
                    $allowedSysNos[] = (string) $branch['SysNo'];
               } elseif (
                    isset($branch['sysno']) &&
                    $branch['sysno'] !== ''
               ) {
                    $allowedSysNos[] = (string) $branch['sysno'];
               } elseif (
                    isset($branch['branch_id']) &&
                    $branch['branch_id'] !== ''
               ) {
                    $allowedSysNos[] = (string) $branch['branch_id'];
               }
          }

          /*
     * Remove duplicates.
     */
          $allowedSysNos = array_values(
               array_unique(
                    array_filter($allowedSysNos)
               )
          );

          /*
     * No branches assigned.
     */
          if (empty($allowedSysNos)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 5. Get BMW records
     * =========================================================
     */
          $bmwData = $this->acModel
               ->orderBy('id', 'DESC')
               ->findAll();

          if (empty($bmwData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }

          /*
     * Normalize BMW result.
     *
     * This is important because your acModel may have:
     *
     * protected $returnType = 'object';
     *
     * If so, findAll() returns stdClass objects.
     */
          foreach ($bmwData as &$bmwRow) {

               if (is_object($bmwRow)) {
                    $bmwRow = (array) $bmwRow;
               }
          }

          unset($bmwRow);


          /*
     * =========================================================
     * 6. Filter BMW records by user's mapped branches
     *
     * BMW branch_id = Branches.SysNo
     * =========================================================
     */
          $bmwData = array_values(
               array_filter(
                    $bmwData,
                    function ($row) use ($allowedSysNos) {

                         if (is_object($row)) {
                              $row = (array) $row;
                         }

                         if (!is_array($row)) {
                              return false;
                         }

                         if (!isset($row['branch_id'])) {
                              return false;
                         }

                         return in_array(
                              (string) $row['branch_id'],
                              $allowedSysNos,
                              true
                         );
                    }
               )
          );


          /*
     * No BMW records for user's branches.
     */
          if (empty($bmwData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 7. Get database connection
     * =========================================================
     */
          $db = $this->acModel->db;


          /*
     * =========================================================
     * 8. Get BMW branch SysNos
     * =========================================================
     */
          $sysNos = [];

          foreach ($bmwData as $row) {

               if (is_object($row)) {
                    $row = (array) $row;
               }

               if (
                    is_array($row) &&
                    isset($row['branch_id']) &&
                    $row['branch_id'] !== ''
               ) {
                    $sysNos[] = (string) $row['branch_id'];
               }
          }

          $sysNos = array_values(
               array_unique(
                    array_filter($sysNos)
               )
          );


          if (empty($sysNos)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 9. Get Branches
     * =========================================================
     */
          $branches = $this->branchesModel
               ->whereIn('SysNo', $sysNos)
               ->findAll();

          if (empty($branches)) {
               /*
         * BMW data exists but branch master data doesn't.
         *
         * Still return BMW records.
         */
               foreach ($bmwData as &$row) {

                    if (is_object($row)) {
                         $row = (array) $row;
                    }

                    $row['cluster_id'] = null;
                    $row['cluster']    = null;
                    $row['z_id']       = null;
                    $row['zone']       = null;
               }

               unset($row);

               return $this->respond([
                    'status' => true,
                    'data'   => $bmwData
               ]);
          }


          /*
     * =========================================================
     * 10. Normalize Branch records
     * =========================================================
     */
          foreach ($branches as &$branch) {

               if (is_object($branch)) {
                    $branch = (array) $branch;
               }
          }

          unset($branch);


          /*
     * =========================================================
     * 11. Branches.SysNo => Branches.id
     * =========================================================
     */
          $branchMap = [];

          foreach ($branches as $branch) {

               if (!is_array($branch)) {
                    continue;
               }

               if (
                    isset($branch['SysNo']) &&
                    isset($branch['id'])
               ) {
                    $branchMap[(string) $branch['SysNo']]
                         = (int) $branch['id'];
               }
          }

          $branchIds = array_values($branchMap);


          /*
     * =========================================================
     * 12. Get clusters
     * =========================================================
     */
          $clusterRows = $db->table('clusters')
               ->select('cluster_id, cluster, branches')
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          $clusters = [];

          foreach ($clusterRows as $cluster) {

               $clusterBranchIds = array_filter(
                    array_map(
                         'trim',
                         explode(',', $cluster['branches'] ?? '')
                    )
               );

               foreach ($branchIds as $branchId) {

                    if (
                         in_array(
                              (string) $branchId,
                              $clusterBranchIds,
                              true
                         )
                    ) {
                         $clusters[$branchId] = [
                              'cluster_id' => $cluster['cluster_id'],
                              'cluster'    => $cluster['cluster']
                         ];

                         break;
                    }
               }
          }


          /*
     * =========================================================
     * 13. Get zones
     * =========================================================
     */
          $zoneRows = $db->table('zones')
               ->select('z_id, zone, clusters')
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          $zones = [];

          foreach ($clusters as $branchId => $cluster) {

               foreach ($zoneRows as $zone) {

                    $zoneClusterIds = array_filter(
                         array_map(
                              'trim',
                              explode(',', $zone['clusters'] ?? '')
                         )
                    );

                    if (
                         in_array(
                              (string) $cluster['cluster_id'],
                              $zoneClusterIds,
                              true
                         )
                    ) {
                         $zones[$branchId] = [
                              'z_id' => $zone['z_id'],
                              'zone' => $zone['zone']
                         ];

                         break;
                    }
               }
          }


          /*
     * =========================================================
     * 14. Add cluster and zone information
     * =========================================================
     */
          foreach ($bmwData as &$row) {

               /*
         * Ensure row is an array.
         */
               if (is_object($row)) {
                    $row = (array) $row;
               }

               $sysNo = (string) ($row['branch_id'] ?? '');

               /*
         * Find Branches.id from Branches.SysNo.
         */
               $branchId = $branchMap[$sysNo] ?? null;

               /*
         * Default values.
         */
               $row['cluster_id'] = null;
               $row['cluster']    = null;
               $row['z_id']       = null;
               $row['zone']       = null;

               /*
         * Cluster.
         */
               if (
                    $branchId !== null &&
                    isset($clusters[$branchId])
               ) {
                    $row['cluster_id'] =
                         $clusters[$branchId]['cluster_id'];

                    $row['cluster'] =
                         $clusters[$branchId]['cluster'];
               }

               /*
         * Zone.
         */
               if (
                    $branchId !== null &&
                    isset($zones[$branchId])
               ) {
                    $row['z_id'] =
                         $zones[$branchId]['z_id'];

                    $row['zone'] =
                         $zones[$branchId]['zone'];
               }
          }

          unset($row);


          /*
     * =========================================================
     * 15. Return response
     * =========================================================
     */
          return $this->respond([
               'status' => true,
               'data'   => $bmwData
          ]);
     }



     /**
      * READ SINGLE
      */
     public function show($id)
     {
          $record = $this->acModel->find($id);
          // get files data from files table with ac_form_id as $id
          $record['files'] = $this->acModel->getFiles($id);

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

               $record = $this->acModel->find($id);

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
                              ->where('ac_form_id', $id)
                              ->where('service_id', 'AC')
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
                                   FCPATH . 'uploads/ac_services',
                                   $newName
                              );

                              $db->table('files')->insert([
                                   'file_name' => $newName,
                                   'service_id' => 'AC',
                                   'ac_form_id' => $id,
                                   'createdDTM' => date('Y-m-d H:i:s')
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
               $this->acModel->update($id, $data);

               /*
        |--------------------------------------------------------------------------
        | RETURN UPDATED FILE LIST
        |--------------------------------------------------------------------------
        */
               $updatedFiles = $db->table('files')
                    ->where('service_id', 'AC')
                    ->where('ac_form_id', $id)
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
          $record = $this->acModel->find($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }

          $this->acModel->delete($id);

          return $this->respond([
               'status' => true,
               'message' => 'Deleted successfully'
          ]);
     }

     public function viewAcAttachment($fileName)
     {


          // Sanitize filename and construct path for branding photos
          $fileName = basename(htmlspecialchars($fileName));
          $uploadPath = WRITEPATH . 'uploads/ac_services/';
          $filePath = $uploadPath . $fileName;

          if (!file_exists($filePath) || !is_readable($filePath)) {
               return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'File not found or not accessible'
               ]);
          }

          $fileSize = filesize($filePath);
          $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
          ob_clean();
          header('Cache-Control: private, no-transform, no-store, must-revalidate');
          header('Content-Length: ' . $fileSize);
          header('Accept-Ranges: bytes');
          switch ($extension) {
               case 'pdf':
                    header('Content-Type: application/pdf');
                    header('Content-Disposition: inline; filename="' . $fileName . '"');
                    break;
               case 'jpg':
               case 'jpeg':
                    header('Content-Type: image/jpeg');
                    header('Content-Disposition: inline; filename="' . $fileName . '"');
                    break;
               case 'png':
                    header('Content-Type: image/png');
                    header('Content-Disposition: inline; filename="' . $fileName . '"');
                    break;
               default:
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . $fileName . '"');
                    break;
          }
          readfile($filePath);
          exit();
     }

     public function viewAcAttachmentNewThumb($fileName)
     {


          $fileName = basename(htmlspecialchars($fileName));
          $uploadPath = WRITEPATH . 'uploads/ac_services/';
          $filePath = $uploadPath . $fileName;

          if (!file_exists($filePath) || !is_readable($filePath)) {
               return $this->response->setStatusCode(404)->setJSON([
                    'status' => 'error',
                    'message' => 'File not found or not accessible'
               ]);
          }

          $thumbDir = $uploadPath . 'thumbs/';
          if (!is_dir($thumbDir)) {
               mkdir($thumbDir, 0755, true);
          }
          $thumbName = 'thumb_50_50_' . $fileName;
          $thumbPath = $thumbDir . $thumbName;

          if (!file_exists($thumbPath)) {
               $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
               if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
                    list($w, $h) = getimagesize($filePath);
                    $thumbW = 50;
                    $thumbH = 50;
                    if ($ext === 'png') {
                         $src = imagecreatefrompng($filePath);
                    } else {
                         $src = imagecreatefromjpeg($filePath);
                    }
                    $thumb = imagecreatetruecolor($thumbW, $thumbH);
                    $bg = imagecolorallocate($thumb, 255, 255, 255);
                    imagefill($thumb, 0, 0, $bg);
                    $scale = min($thumbW / $w, $thumbH / $h);
                    $newW = (int) ($w * $scale);
                    $newH = (int) ($h * $scale);
                    $dstX = (int) (($thumbW - $newW) / 2);
                    $dstY = (int) (($thumbH - $newH) / 2);
                    imagecopyresampled($thumb, $src, $dstX, $dstY, 0, 0, $newW, $newH, $w, $h);
                    if ($ext === 'png') {
                         imagepng($thumb, $thumbPath);
                    } else {
                         imagejpeg($thumb, $thumbPath, 85);
                    }
                    imagedestroy($thumb);
                    imagedestroy($src);
               } elseif ($ext === 'pdf') {
                    $thumb = imagecreatetruecolor(50, 50);
                    $bg = imagecolorallocate($thumb, 240, 240, 240);
                    $textcol = imagecolorallocate($thumb, 60, 60, 60);
                    imagefilledrectangle($thumb, 0, 0, 49, 49, $bg);
                    imagestring($thumb, 5, 9, 15, 'PDF', $textcol);
                    imagepng($thumb, $thumbPath);
                    imagedestroy($thumb);
               } else {
                    $thumb = imagecreatetruecolor(50, 50);
                    $bg = imagecolorallocate($thumb, 230, 230, 230);
                    imagefilledrectangle($thumb, 0, 0, 49, 49, $bg);
                    imagepng($thumb, $thumbPath);
                    imagedestroy($thumb);
               }
          }

          header('Cache-Control: public, max-age=86400');
          header('Content-Type: image/png');
          header('Content-Length: ' . filesize($thumbPath));
          readfile($thumbPath);
          exit();
     }

     private function triggerEmailOnSave(
          int $formId,
          array $header = [],
          $createdDtm = null,
          array $payload = []
     ) {
          try {

               $logFile = WRITEPATH . 'logs/ac_email_debug.log';

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
                    (string) (getenv('AC_SERVICE_TO_EMAIL') ?: '')
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
                         "[" . date('Y-m-d H:i:s') . "] Using AC_SERVICE_TO_EMAIL={$customTo}\n",
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
                    'AC Service Email Sent. Result=' .
                         print_r($result, true)
               );
          } catch (\Throwable $e) {

               $msg = $e->getMessage();

               log_message(
                    'error',
                    'triggerEmailOnSave failed: ' . $msg
               );

               @file_put_contents(
                    WRITEPATH . 'logs/ac_email_debug.log',
                    "[" . date('Y-m-d H:i:s') . "] EXCEPTION: " .
                         $msg .
                         "\nTRACE:\n" .
                         $e->getTraceAsString() .
                         "\n\n",
                    FILE_APPEND
               );
          }
     }

     // private function triggerEmailOnSave(
     //      int $formId,
     //      $header = null,
     //      $createdDtm = null,
     //      array $payload = []
     // ) {
     //      $db = \Config\Database::connect();
     //      // send a single message; first address becomes the primary To recipient, others are CC'd
     //      // public function sendTemplate($eventKey = null, $toEmail = null, $toName = null, $data = [], $subjectOverride = null, $ccList = null, $attachments = null) 
     //      $emailController = new \App\Controllers\EmailController();
     //      $sendEmailConfirm = $emailController->sendTemplate('ac_service_form_submitted', $payload['email'], $payload['name'], $payload, null, null, null);
     //      // Find template bound to this form_id or event_key
     //      $event_key = trim($payload['event_key']) ??  'AC_SERVICING';
     //      $tmpl = $db->table('email_templates')->where('event_key', $event_key)->get()->getRowArray();
     //      if (!$tmpl) return; // nothing to do

     //      // Debug: record trigger invocation and template lookup
     //      try {
     //           $dbg = "[" . date('Y-m-d H:i:s') . "] triggerSubmissionEmailOnSave called: form_id={$formId}   template_event_key=" . ($tmpl['event_key'] ?? 'NULL') . "\n";
     //           @file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', $dbg, FILE_APPEND);
     //      } catch (\Throwable $__dbg) {
     //      }

     //      // UAT/dev helper: optionally bypass persisted dedupe checks.
     //      $ciEnv = strtolower(trim((string)(getenv('CI_ENVIRONMENT') ?: (defined('ENVIRONMENT') ? ENVIRONMENT : ''))));
     //      $appBase = strtolower(trim((string)(getenv('app.baseURL') ?: '')));
     //      $isPrivateHost = (bool) preg_match('#https?://(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)#', $appBase);
     //      $bypassRaw = strtolower(trim((string)(getenv('EMAIL_BYPASS_PERSISTED_DEDUPE') ?: '')));
     //      $bypassViaEnv = in_array($bypassRaw, ['1', 'true', 'yes', 'y', 'on'], true);
     //      $bypassPersistedDedupe = $bypassViaEnv || $ciEnv === 'development' || $isPrivateHost;

     //      if ($bypassPersistedDedupe) {
     //           try {
     //                @file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] persisted_dedupe_bypass_active: env={$ciEnv} app_base={$appBase}\n", FILE_APPEND);
     //           } catch (\Throwable $__dbg) {
     //           }
     //      }

     //      // Persistent idempotency: only send if updated_dtm is newer than last sent time for this event.
     //      try {

     //           $email = $emailController->sendTemplate($tmpl['event_key'], $tmpl['to_email'], $tmpl['to_name'], $payload, $tmpl['subject_override'], null, null);

     //           if (!$email->send()) {

     //                log_message(
     //                     'error',
     //                     'Email failed: ' . print_r($email->printDebugger(), true)
     //                );

     //                return false;
     //           }

     //           log_message(
     //                'info',
     //                "Submission email sent successfully for Form ID {$formId}"
     //           );

     //           return true;
     //      } catch (\Throwable $e) {

     //           log_message(
     //                'error',
     //                'Submission email error: ' . $e->getMessage()
     //           );

     //           return false;
     //      }
     // }
}

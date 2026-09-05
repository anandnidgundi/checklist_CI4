<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BiomedicalEquipmentModel;
use App\Models\BranchesModel;
use App\Models\ChakridataModel;
use App\Models\DeptModel;

class BiomedicalEquipment extends BaseController
{
     use ResponseTrait;
     protected $biomedicalEquipmentModel;
     protected $branchesModel;
     protected $chakridataModel;
     protected $deptModel;
     public function __construct()
     {
          $this->biomedicalEquipmentModel = new BiomedicalEquipmentModel();
          $this->branchesModel = new BranchesModel();
          $this->chakridataModel = new ChakridataModel();
          $this->deptModel = new DeptModel();
     }

     // getChakriDataByAssetId
     public function getChakriDataByAssetId($assetId)
     {
          $result = $this->chakridataModel->getChakriDataByAssetId($assetId);
          return $this->respond($result);
     }

     // getChakriDatalistByBranchName
     public function getChakriDatalistByBranchName($branchName)
     {
          $result = $this->chakridataModel->getChakriDatalistByBranchName($branchName);
          return $this->respond($result);
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

               if (empty($jsonData)) {
                    return $this->fail('data field is missing');
               }

               $data = json_decode($jsonData, true);

               if (json_last_error() !== JSON_ERROR_NONE) {
                    return $this->fail([
                         'message' => 'Invalid JSON data',
                         'json_error' => json_last_error_msg(),
                    ]);
               }

               if (empty($data)) {
                    return $this->fail('JSON data is empty');
               }

               log_message(
                    'debug',
                    'Biomedical create data: ' . json_encode($data)
               );


               // -------------------------
               // 2. INSERT MAIN TABLE
               // -------------------------
               $insertId = $this->biomedicalEquipmentModel->insert($data);

               if ($insertId === false) {
                    return $this->fail([
                         'message' => 'Failed to insert biomedical equipment data',
                         'errors' => $this->biomedicalEquipmentModel->errors(),
                    ]);
               }

               log_message(
                    'debug',
                    'Biomedical equipment inserted. ID: ' . $insertId
               );

               // -------------------------
               // 2.1 UPDATE CHAKRI PMS 2
               // -------------------------
               $assetDesc = trim($data['biomedical_equipment'] ?? '');
               $nextPMSDate = trim($data['next_scheduled_pms_date'] ?? '');

               log_message(
                    'debug',
                    'Attempting Chakri PMS 2 update | Asset_desc=' .
                         $assetDesc .
                         ' | next_scheduled_pms_date=' .
                         $nextPMSDate
               );

               if ($assetDesc !== '' && $nextPMSDate !== '') {

                    // Convert YYYY-MM-DD to DD-MM-YYYY
                    $formattedPMSDate = date(
                         'd-m-Y',
                         strtotime($nextPMSDate)
                    );

                    // Find Chakri record
                    $chakriRecord = $this->chakridataModel
                         ->where('Asset_desc', $assetDesc)
                         ->first();

                    if (!$chakriRecord) {

                         log_message(
                              'error',
                              'Chakri record NOT FOUND for Asset_desc: ' .
                                   $assetDesc
                         );
                    } else {

                         log_message(
                              'debug',
                              'Chakri record found: ' .
                                   json_encode($chakriRecord)
                         );

                         // -------------------------
                         // UPDATE PMS 2
                         // -------------------------
                         $db = db_connect();

                         $sql = "UPDATE `chakri_data`
                SET `PMS 2` = ?
                WHERE `id` = ?";

                         $updated = $db->query($sql, [
                              $formattedPMSDate,
                              $chakriRecord['id']
                         ]);

                         if (!$updated) {

                              log_message(
                                   'error',
                                   'Chakri PMS 2 update FAILED | Asset_desc=' .
                                        $assetDesc .
                                        ' | PMS 2=' .
                                        $formattedPMSDate
                              );
                         } else {

                              log_message(
                                   'debug',
                                   'Chakri PMS 2 update SUCCESS | ID=' .
                                        $chakriRecord['id'] .
                                        ' | Asset_desc=' .
                                        $assetDesc .
                                        ' | PMS 2=' .
                                        $formattedPMSDate
                              );
                         }
                    }
               }


               // -------------------------
               // 3. DB CONNECTION
               // -------------------------
               $db = db_connect();


               // -------------------------
               // 4. HANDLE FILE UPLOAD
               // -------------------------
               $fileArray = [];

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

                              $db->table('files')->insert([
                                   'file_name' => $newName,
                                   'biomedical_equip_form_id' => $insertId,
                                   'service_id' => 'BIOMEDICAL',
                                   'createdDTM' => date('Y-m-d H:i:s')
                              ]);
                         }
                    }
               }


               // -------------------------
               // 5. BUILD PDF
               // -------------------------
               $pdfBinary =
                    \App\Services\PdfService::buildBioMedicalEquipmentPdf($insertId);

               if (empty($pdfBinary)) {
                    throw new \RuntimeException(
                         'PDF generation returned empty content'
                    );
               }


               // -------------------------
               // 6. SAVE PDF
               // -------------------------
               $pdfDir = WRITEPATH . 'uploads/secure_files/';

               if (!is_dir($pdfDir)) {
                    mkdir($pdfDir, 0777, true);
               }

               $pdfFileName =
                    'biomedical_equipment_' . $insertId . '.pdf';

               $pdfFilePath =
                    $pdfDir . $pdfFileName;

               $written = file_put_contents(
                    $pdfFilePath,
                    $pdfBinary
               );

               if ($written === false) {
                    throw new \RuntimeException(
                         'Unable to save PDF file: ' . $pdfFilePath
                    );
               }


               // -------------------------
               // 7. INSERT PDF INTO FILES
               // -------------------------
               $db->table('files')->insert([
                    'file_name' => $pdfFileName,
                    'biomedical_equip_form_id' => $insertId,
                    'service_id' => 'BIOMEDICAL_PDF',
                    'createdDTM' => date('Y-m-d H:i:s')
               ]);

               $pdfFileId = $db->insertID();

               if (!$pdfFileId) {
                    throw new \RuntimeException(
                         'PDF file record was not inserted into files table'
                    );
               }


               // -------------------------
               // 8. EMAIL PAYLOAD
               // -------------------------
               $emailPayload = [
                    'pdf_file_id' => $pdfFileId,
                    'pdf_file_path' => $pdfFilePath,
               ];


               // -------------------------
               // 9. LOG SUBMISSION
               // -------------------------
               try {

                    $lm = new \App\Models\LogsModel();

                    $lm->insertLog([
                         'uri' => $this->request->getURI()->getPath(),
                         'method' => $this->request->getMethod(),

                         'params' => [
                              'form_id' => $data['form_id'] ?? null,
                              'biomedical_equip_form_id' => $insertId,
                              'files_uploaded' => count($fileArray),
                              'service_id' => 'BIOMEDICAL',
                              'submission_type' => 'BIOMEDICAL_service_create',
                         ],

                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createBiomedicalEquipment',
                         'entity_type' => 'biomedical_equipment_form',
                         'entity_id' => (string) $insertId,
                         'user_id' => $data['created_by'] ?? null,
                    ]);
               } catch (\Throwable $e) {

                    log_message(
                         'error',
                         'BiomedicalEquipment log failed: ' .
                              $e->getMessage()
                    );
               }


               // -------------------------
               // 10. TRIGGER EMAIL
               // -------------------------
               try {

                    $formId = 12;

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
                         'BiomedicalEquipment email failed: ' .
                              $e->getMessage()
                    );
               }


               // -------------------------
               // 11. SUCCESS
               // -------------------------
               return $this->respond([
                    'status' => true,
                    'message' => 'Biomedical Equipment created successfully',
                    'id' => $insertId,
                    'files_uploaded' => count($fileArray)
               ], 201);
          } catch (\Throwable $e) {

               log_message(
                    'error',
                    'BiomedicalEquipment::create FAILED: ' .
                         $e->getMessage() .
                         ' | File: ' .
                         $e->getFile() .
                         ' | Line: ' .
                         $e->getLine()
               );

               return $this->fail([
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
               ]);
          }
     }

     /**
      * READ ALL
      */
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

          // Authorization may be stdClass
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
     * Normalize result.
     *
     * It may be:
     * [
     *     stdClass Object(...)
     * ]
     *
     * OR:
     *
     * [
     *     [...]
     * ]
     *
     * OR a single stdClass.
     */
          if (is_object($userBranchMap)) {
               $userBranchMap = [$userBranchMap];
          }

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
     * Normalize single object result.
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

               if (is_object($branch)) {
                    $branch = (array) $branch;
               }

               if (!is_array($branch)) {
                    continue;
               }

               /*
         * BMW branch_id corresponds to Branches.SysNo.
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
               } elseif (
                    isset($branch['id']) &&
                    $branch['id'] !== ''
               ) {
                    /*
             * Last fallback.
             */
                    $allowedSysNos[] = (string) $branch['id'];
               }
          }

          $allowedSysNos = array_values(
               array_unique(
                    array_filter($allowedSysNos, function ($value) {
                         return $value !== '';
                    })
               )
          );

          if (empty($allowedSysNos)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 5. Get Biomedical / PMS records
     * =========================================================
     */
          $bmwData = $this->biomedicalEquipmentModel
               ->orderBy('id', 'DESC')
               ->findAll();

          if (empty($bmwData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 6. Normalize Biomedical records
     * =========================================================
     *
     * This is critical.
     *
     * If the model has:
     *
     * protected $returnType = 'object';
     *
     * findAll() returns stdClass objects.
     *
     * Convert every record to an array BEFORE
     * accessing $row['branch_id'].
     */
          $normalizedBmwData = [];

          foreach ($bmwData as $row) {

               if (is_object($row)) {
                    $row = (array) $row;
               }

               if (!is_array($row)) {
                    continue;
               }

               $normalizedBmwData[] = $row;
          }

          $bmwData = $normalizedBmwData;

          if (empty($bmwData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 7. Filter Biomedical records by user's branches
     * =========================================================
     *
     * biomedical_equipment.branch_id
     *              ↓
     *       Branches.SysNo
     */
          $bmwData = array_values(
               array_filter(
                    $bmwData,
                    function ($row) use ($allowedSysNos) {

                         if (!is_array($row)) {
                              return false;
                         }

                         if (
                              !isset($row['branch_id']) ||
                              $row['branch_id'] === ''
                         ) {
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

          if (empty($bmwData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 8. Database connection
     * =========================================================
     */
          $db = $this->biomedicalEquipmentModel->db;


          /*
     * =========================================================
     * 9. Get BMW branch SysNos
     * =========================================================
     */
          $sysNos = [];

          foreach ($bmwData as $row) {

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
                    array_filter($sysNos, function ($value) {
                         return $value !== '';
                    })
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
     * 10. Get Branches
     * =========================================================
     */
          $branches = $this->branchesModel
               ->whereIn('SysNo', $sysNos)
               ->findAll();

          /*
     * Normalize Branch records.
     */
          if (is_object($branches)) {
               $branches = [$branches];
          }

          $normalizedBranches = [];

          foreach ($branches as $branch) {

               if (is_object($branch)) {
                    $branch = (array) $branch;
               }

               if (!is_array($branch)) {
                    continue;
               }

               $normalizedBranches[] = $branch;
          }

          $branches = $normalizedBranches;


          /*
     * =========================================================
     * 11. Branches.SysNo => Branches.id
     * =========================================================
     */
          $branchMap = [];

          foreach ($branches as $branch) {

               if (
                    isset($branch['SysNo']) &&
                    isset($branch['id'])
               ) {
                    $branchMap[(string) $branch['SysNo']] =
                         (int) $branch['id'];
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
               ->getResult();

          /*
     * IMPORTANT:
     * getResult() returns objects.
     *
     * Convert them to arrays.
     */
          $normalizedClusterRows = [];

          foreach ($clusterRows as $cluster) {

               if (is_object($cluster)) {
                    $cluster = (array) $cluster;
               }

               if (!is_array($cluster)) {
                    continue;
               }

               $normalizedClusterRows[] = $cluster;
          }

          $clusterRows = $normalizedClusterRows;

          $clusters = [];

          foreach ($clusterRows as $cluster) {

               $clusterBranchIds = array_filter(
                    array_map(
                         'trim',
                         explode(
                              ',',
                              (string) ($cluster['branches'] ?? '')
                         )
                    ),
                    function ($value) {
                         return $value !== '';
                    }
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
                              'cluster_id' => $cluster['cluster_id'] ?? null,
                              'cluster'    => $cluster['cluster'] ?? null
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
               ->getResult();

          /*
     * getResult() returns stdClass objects.
     * Normalize them before array access.
     */
          $normalizedZoneRows = [];

          foreach ($zoneRows as $zone) {

               if (is_object($zone)) {
                    $zone = (array) $zone;
               }

               if (!is_array($zone)) {
                    continue;
               }

               $normalizedZoneRows[] = $zone;
          }

          $zoneRows = $normalizedZoneRows;

          $zones = [];

          foreach ($clusters as $branchId => $cluster) {

               foreach ($zoneRows as $zone) {

                    $zoneClusterIds = array_filter(
                         array_map(
                              'trim',
                              explode(
                                   ',',
                                   (string) ($zone['clusters'] ?? '')
                              )
                         ),
                         function ($value) {
                              return $value !== '';
                         }
                    );

                    if (
                         in_array(
                              (string) $cluster['cluster_id'],
                              $zoneClusterIds,
                              true
                         )
                    ) {
                         $zones[$branchId] = [
                              'z_id' => $zone['z_id'] ?? null,
                              'zone' => $zone['zone'] ?? null
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
         * At this point row should already be an array,
         * but keep this protection.
         */
               if (is_object($row)) {
                    $row = (array) $row;
               }

               if (!is_array($row)) {
                    continue;
               }

               $sysNo = (string) ($row['branch_id'] ?? '');

               /*
         * Find Branches.id using Branches.SysNo.
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
         * Cluster information.
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
         * Zone information.
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

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BmwModel;
use App\Models\BranchesModel;
use App\Models\DeptModel;

class Bmw extends BaseController
{
     use ResponseTrait;

     protected $bmwModel;
     protected $branchesModel;
     protected $deptModel;

     public function __construct()
     {
          $this->bmwModel = new BmwModel();
          $this->branchesModel = new BranchesModel();
          $this->deptModel = new DeptModel();
          $db = \Config\Database::connect();
          $secondDB = \Config\Database::connect('secondary');
     }

     public function total_visits_done($branch_id)
     {
          $totalVisits = $this->bmwModel
               ->where('branch_id', $branch_id)
               ->countAllResults();

          return $this->respond([
               'status' => true,
               'branch_id' => $branch_id,
               'total_visits_done' => $totalVisits
          ]);
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
     * Authorization can be array or stdClass.
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
     * getUserMapBranches() may return:
     *
     * [
     *     ['branches' => '15,62,7']
     * ]
     *
     * OR:
     *
     * [
     *     stdClass Object(...)
     * ]
     */
          if (is_object($userBranchMap)) {
               $userBranchMap = [$userBranchMap];
          }

          /*
     * Get first row.
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
     * Get mapped branches.
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
     * Normalize if a single stdClass is returned.
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
         * Convert stdClass to array.
         */
               if (is_object($branch)) {
                    $branch = (array) $branch;
               }

               if (!is_array($branch)) {
                    continue;
               }

               /*
         * BMW branch_id should contain Branches.SysNo.
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
     * Remove duplicate branch SysNos.
     */
          $allowedSysNos = array_values(
               array_unique(
                    array_filter($allowedSysNos)
               )
          );

          /*
     * No branches mapped to employee.
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
          $bmwData = $this->bmwModel
               ->orderBy('id', 'DESC')
               ->findAll();

          if (empty($bmwData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * Normalize BMW records.
     *
     * This handles both:
     *
     * protected $returnType = 'array';
     *
     * and:
     *
     * protected $returnType = 'object';
     */
          foreach ($bmwData as &$bmwRow) {

               if (is_object($bmwRow)) {
                    $bmwRow = (array) $bmwRow;
               }
          }

          unset($bmwRow);


          /*
     * =========================================================
     * 6. Filter BMW records by user's branches
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
     * 7. Database connection used by bmwModel
     * =========================================================
     */
          $db = $this->bmwModel->db;


          /*
     * =========================================================
     * 8. Get branch SysNos from filtered BMW records
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
     * 9. Get Branches from travelapp database
     *
     * branchesModel should have:
     *
     * protected $DBGroup = 'travelapp';
     * =========================================================
     */
          $branches = $this->branchesModel
               ->whereIn('SysNo', $sysNos)
               ->findAll();

          /*
     * Branch master data not found.
     */
          if (empty($branches)) {

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
     * 10. Normalize branch records
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
     * 11. Map:
     *
     * Branches.SysNo => Branches.id
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


          /*
     * Get all Branches.id values.
     */
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
     * 15. Return filtered response
     * =========================================================
     */
          return $this->respond([
               'status' => true,
               'data'   => $bmwData
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
               // log message reply
               log_message('info', 'Received JSON data: ' . $jsonData);

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

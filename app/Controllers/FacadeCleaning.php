<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\FacadeCleaningModel;
use App\Models\BranchesModel;
use App\Models\DeptModel;

class FacadeCleaning extends BaseController
{
     use ResponseTrait;

     protected $facadeCleaningModel;
     protected $branchesModel;
     protected $deptModel;

     public function __construct()
     {
          $this->facadeCleaningModel = new FacadeCleaningModel();
          $this->branchesModel = new BranchesModel();
          $this->deptModel = new DeptModel();
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
     * Authorization may return array or stdClass.
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
     * getUserMapBranches() may return array/stdClass.
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
     * Normalize single stdClass response.
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
     * 5. Get Facade Cleaning records
     * =========================================================
     */
          $facadeData = $this->facadeCleaningModel
               ->orderBy('id', 'DESC')
               ->findAll();

          if (empty($facadeData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 6. Normalize Facade Cleaning records
     *
     * Prevent:
     * Cannot use object of type stdClass as array
     * =========================================================
     */
          foreach ($facadeData as &$facadeRow) {

               if (is_object($facadeRow)) {
                    $facadeRow = (array) $facadeRow;
               }
          }

          unset($facadeRow);


          /*
     * =========================================================
     * 7. Filter Facade Cleaning records
     *    by user's mapped branches
     *
     * facade_cleaning.branch_id = Branches.SysNo
     * =========================================================
     */
          $facadeData = array_values(
               array_filter(
                    $facadeData,
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
     * No Facade Cleaning records for user's branches.
     */
          if (empty($facadeData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 8. Database connection used by facadeCleaningModel
     * =========================================================
     */
          $db = $this->facadeCleaningModel->db;


          /*
     * =========================================================
     * 9. Get branch SysNos from filtered records
     * =========================================================
     */
          $sysNos = [];

          foreach ($facadeData as $row) {

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
     * 10. Get Branches from travelapp database
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
     * =========================================================
     * 11. Normalize branch records
     * =========================================================
     */
          if (!empty($branches)) {

               foreach ($branches as &$branch) {

                    if (is_object($branch)) {
                         $branch = (array) $branch;
                    }
               }

               unset($branch);
          }


          /*
     * =========================================================
     * 12. Map:
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
     * 13. Get clusters
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
     * 14. Get zones
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
     * 15. Add cluster and zone information
     * =========================================================
     */
          foreach ($facadeData as &$row) {

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
     * 16. Return filtered response
     * =========================================================
     */
          return $this->respond([
               'status' => true,
               'data'   => $facadeData
          ]);
     }


     public function show($id)
     {
          $record = $this->facadeCleaningModel->find($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }


          $record['files'] = $this->facadeCleaningModel->getFiles($id);


          return $this->respond([
               'status' => true,
               'data' => $record
          ]);
     }


     public function delete($id)
     {
          $record = $this->facadeCleaningModel->find($id);

          if (!$record) {
               return $this->failNotFound('Record not found');
          }


          $this->facadeCleaningModel->delete($id);


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

               if (!$this->facadeCleaningModel->insert($data)) {

                    return $this->respond([
                         'status' => false,
                         'errors' => $this->facadeCleaningModel->errors(),
                    ], 422);
               }
               $insertId = $this->facadeCleaningModel->getInsertID();


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
                    $uploadPath = FCPATH . 'uploads/facade_cleaning';
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
                                   'facade_cleaning_id' => $insertId,
                                   'service_id' => 'FACADE_CLEANING',
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
                              'facade_cleaning_id' => $insertId,
                              'files_uploaded' => $uploadedFiles,
                              'service_id' => 'FACADE_CLEANING',
                              'submission_type' => 'facade_create'
                         ],
                         'ip_address' =>
                         $this->request
                              ->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createFacadeCleaning',
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
                    'message' => 'Facade Cleaning created successfully',
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

               $existing = $this->facadeCleaningModel->find($id);

               if (!$existing) {
                    return $this->failNotFound(
                         'Facade Cleaning not found'
                    );
               }

               if (!$this->facadeCleaningModel->update($id, $data)) {
                    return $this->fail(
                         $this->facadeCleaningModel->errors()
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
                                   'facade_cleaning_id',
                                   $id
                              )
                              ->where(
                                   'service_id',
                                   'FACADE_CLEANING'
                              )
                              ->get()
                              ->getRowArray();

                         if ($fileRow) {
                              $filePath =
                                   FCPATH .
                                   'uploads/facade_cleaning/' .
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
                         'uploads/facade_cleaning';

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

                              $db->table('files')->insert(['file_name' => $newName,                       'facade_cleaning_id' => $id,                       'service_id' => 'FACADE_CLEANING',                       'createdDTM' => date(
                                   'Y-m-d H:i:s'
                              )]);

                              $uploadedFiles++;
                         }
                    }
               }

               unset($data['deleted_files']);

               return $this->respond([
                    'status' => true,
                    'message' => 'Facade Cleaning updated successfully',
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

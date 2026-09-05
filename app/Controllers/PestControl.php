<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\PestControlModel;
use App\Models\BranchesModel;
use App\Models\DeptModel;

class  PestControl extends BaseController
{
     use ResponseTrait;
     protected $pestControlModel;
     protected $branchesModel;
     protected $deptModel;
     public function __construct()
     {
          $this->pestControlModel = new PestControlModel();
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

               if (is_object($branch)) {
                    $branch = (array) $branch;
               }

               if (!is_array($branch)) {
                    continue;
               }

               /*
          * Branches.SysNo is the preferred value.
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
     * 5. Get Pest Control records
     * =========================================================
     */
          $pestControlData = $this->pestControlModel
               ->orderBy('id', 'DESC')
               ->findAll();

          if (empty($pestControlData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 6. Normalize Pest Control records
     *
     * Prevent:
     * Cannot use object of type stdClass as array
     * =========================================================
     */
          foreach ($pestControlData as &$pestRow) {

               if (is_object($pestRow)) {
                    $pestRow = (array) $pestRow;
               }
          }

          unset($pestRow);


          /*
     * =========================================================
     * 7. Filter Pest Control records by user's branches
     *
     * pest_control.branch_id = Branches.SysNo
     * =========================================================
     */
          $pestControlData = array_values(
               array_filter(
                    $pestControlData,
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
     * No Pest Control records for user's branches.
     */
          if (empty($pestControlData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 8. Database connection used by pestControlModel
     * =========================================================
     */
          $db = $this->pestControlModel->db;


          /*
     * =========================================================
     * 9. Get branch SysNos from filtered Pest Control records
     * =========================================================
     */
          $sysNos = [];

          foreach ($pestControlData as $row) {

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
                         explode(
                              ',',
                              $cluster['branches'] ?? ''
                         )
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
                              explode(
                                   ',',
                                   $zone['clusters'] ?? ''
                              )
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
          foreach ($pestControlData as &$row) {

               /*
          * Make sure row is an array.
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
     * 16. Return filtered response
     * =========================================================
     */
          return $this->respond([
               'status' => true,
               'data'   => $pestControlData
          ]);
     }


     public function lastRecordByBranch($branchId)
     {
          // Get the latest pest control record for the selected branch
          $pestControlData = $this->pestControlModel
               ->where('branch_id', $branchId)
               ->orderBy('id', 'DESC')
               ->first();

          // No record found
          if (empty($pestControlData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }

          // Database connection used by pestControlModel
          $db = $this->pestControlModel->db;

          /*
     * Get Branch from travelapp database.
     *
     * branchesModel should use:
     * protected $DBGroup = 'travelapp';
     */
          $branch = $this->branchesModel
               ->where('SysNo', $branchId)
               ->first();

          // Default values
          $pestControlData['cluster_id'] = null;
          $pestControlData['cluster']    = null;
          $pestControlData['z_id']       = null;
          $pestControlData['zone']       = null;

          if (!empty($branch)) {

               // Branches.id
               $branchDbId = (int) $branch['id'];

               /*
         * Find cluster containing this Branches.id
         */
               $clusterRows = $db->table('clusters')
                    ->select('cluster_id, cluster, branches')
                    ->where('status', 'A')
                    ->get()
                    ->getResultArray();

               $clusterData = null;

               foreach ($clusterRows as $cluster) {

                    $clusterBranchIds = array_filter(
                         explode(',', $cluster['branches'])
                    );

                    if (in_array(
                         (string) $branchDbId,
                         $clusterBranchIds,
                         true
                    )) {
                         $clusterData = [
                              'cluster_id' => $cluster['cluster_id'],
                              'cluster'    => $cluster['cluster']
                         ];

                         break;
                    }
               }

               /*
         * Add cluster information
         */
               if ($clusterData !== null) {

                    $pestControlData['cluster_id'] =
                         $clusterData['cluster_id'];

                    $pestControlData['cluster'] =
                         $clusterData['cluster'];

                    /*
             * Find zone containing this cluster ID
             */
                    $zoneRows = $db->table('zones')
                         ->select('z_id, zone, clusters')
                         ->where('status', 'A')
                         ->get()
                         ->getResultArray();

                    foreach ($zoneRows as $zone) {

                         $zoneClusterIds = array_filter(
                              explode(',', $zone['clusters'])
                         );

                         if (in_array(
                              (string) $clusterData['cluster_id'],
                              $zoneClusterIds,
                              true
                         )) {

                              $pestControlData['z_id'] =
                                   $zone['z_id'];

                              $pestControlData['zone'] =
                                   $zone['zone'];

                              break;
                         }
                    }
               }
          }

          return $this->respond([
               'status' => true,
               'data'   => $pestControlData
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

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\waterTankCleaningModel;
use App\Models\BranchesModel;
use App\Models\DeptModel;

class WaterTankCleaning extends BaseController
{
     use ResponseTrait;
     protected $waterTankCleaningModel;
     protected $db;
     protected $branchesModel;
     protected $deptModel;
     public function __construct()
     {
          $this->waterTankCleaningModel = new waterTankCleaningModel();
          $this->db = db_connect();
          $this->branchesModel = new BranchesModel();
          $this->deptModel = new DeptModel();
     }

     public function getFiles($id)
     {

          // get files data from files table with water_tank_cleaning_id as $id
          $files = $this->db->table('files')->where('water_tank_cleaning_id', $id)->get()->getResultArray();
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
               // 2. INSERT WaterTank FORM
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
     * 5. Get Water Tank Cleaning records
     * =========================================================
     */
          $waterTank = $this->waterTankCleaningModel
               ->orderBy('id', 'DESC')
               ->findAll();

          if (empty($waterTank)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 6. Normalize Water Tank records
     *
     * Prevent:
     * Cannot use object of type stdClass as array
     * =========================================================
     */
          foreach ($waterTank as &$waterRow) {

               if (is_object($waterRow)) {
                    $waterRow = (array) $waterRow;
               }
          }

          unset($waterRow);


          /*
     * =========================================================
     * 7. Filter Water Tank records by user's branches
     *
     * water_tank_cleaning.branch_id = Branches.SysNo
     * =========================================================
     */
          $waterTank = array_values(
               array_filter(
                    $waterTank,
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
     * No Water Tank records for user's branches.
     */
          if (empty($waterTank)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 8. Database connection used by waterTankCleaningModel
     * =========================================================
     */
          $db = $this->waterTankCleaningModel->db;


          /*
     * =========================================================
     * 9. Get branch SysNos from filtered Water Tank records
     * =========================================================
     */
          $sysNos = [];

          foreach ($waterTank as $row) {

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
          foreach ($waterTank as &$row) {

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
               'data'   => $waterTank
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

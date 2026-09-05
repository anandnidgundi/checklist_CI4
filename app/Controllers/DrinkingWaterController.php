<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\DrinkingWaterModel;
use CodeIgniter\API\ResponseTrait;
use App\Models\BranchesModel;
use App\Models\DeptModel;

class DrinkingWaterController extends BaseController
{
     use ResponseTrait;

     protected $drinkingWaterModel;
     protected $db;

     protected $branchesModel;
     protected $deptModel;
     public function __construct()
     {
          $this->drinkingWaterModel = new DrinkingWaterModel();
          $this->db = db_connect();
          $this->branchesModel = new BranchesModel();
          $this->deptModel = new DeptModel();
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
         * Drinking Water branch_id should contain
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
     * 5. Get Drinking Water records
     * =========================================================
     */
          $drinkingWaterData = $this->drinkingWaterModel
               ->orderBy('id', 'DESC')
               ->findAll();

          if (empty($drinkingWaterData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * Normalize Drinking Water records.
     *
     * Handles both array and stdClass return types.
     */
          foreach ($drinkingWaterData as &$waterRow) {

               if (is_object($waterRow)) {
                    $waterRow = (array) $waterRow;
               }
          }

          unset($waterRow);


          /*
     * =========================================================
     * 6. Filter Drinking Water records
     *    by user's mapped branches
     *
     * Drinking Water branch_id = Branches.SysNo
     * =========================================================
     */
          $drinkingWaterData = array_values(
               array_filter(
                    $drinkingWaterData,
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
     * No Drinking Water records for user's branches.
     */
          if (empty($drinkingWaterData)) {
               return $this->respond([
                    'status' => true,
                    'data'   => []
               ]);
          }


          /*
     * =========================================================
     * 7. Database connection used by drinkingWaterModel
     * =========================================================
     */
          $db = $this->drinkingWaterModel->db;


          /*
     * =========================================================
     * 8. Get branch SysNos from filtered records
     * =========================================================
     */
          $sysNos = [];

          foreach ($drinkingWaterData as $row) {

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
     * If branch master data is not found,
     * return filtered Drinking Water records.
     */
          if (empty($branches)) {

               foreach ($drinkingWaterData as &$row) {

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
                    'data'   => $drinkingWaterData
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
          foreach ($drinkingWaterData as &$row) {

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
               'data'   => $drinkingWaterData
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

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Services\JwtService;

class DashboardController extends BaseController
{
     use ResponseTrait;

     public function CM_DashboardCount()
     {
          $userModel = new UserModel();
          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;
          $role = $userDetails->role;

          // Retrieve POST data (json) and guard against missing properties
          $requestData = $this->request->getJSON() ?? new \stdClass();

          $selectedMonth = isset($requestData->selectedMonth) ? $requestData->selectedMonth : date('Y-m');

          // Extract branch IDs if it's an array, otherwise use the single value or default '0'
          if (isset($requestData->selectedBranch) && is_array($requestData->selectedBranch)) {
               $selectedBranch = implode(',', array_column($requestData->selectedBranch, 'branch'));
          } else {
               $selectedBranch = isset($requestData->selectedBranch) ? (string)$requestData->selectedBranch : '0';
          }

          $selectedCluster = isset($requestData->selectedCluster) ? (string)$requestData->selectedCluster : '0';
          $selectedZone = isset($requestData->selectedZone) ? (string)$requestData->selectedZone : '0';

          // Ensure authorization returned a valid user object
          if (!is_object($userDetails) || empty($user)) {
               log_message('error', 'Invalid authorization data while fetching CM dashboard');
               return $this->respond(['STATUS' => false, 'message' => 'Unauthorized'], 401);
          }

          $CM_DashboardCount = $userModel->CM_DashboardCount($user, $role, $selectedMonth, $selectedBranch, $selectedCluster, $selectedZone);

          if ($CM_DashboardCount) {
               return $this->respond([
                    'STATUS' => true,
                    'message' => 'CM Dashboard Count.',
                    'data' => $CM_DashboardCount
               ], 200);
          } else {
               log_message('error', 'Failed to give CM Dashboard Count: ' . json_encode($CM_DashboardCount, JSON_PRETTY_PRINT));
               return $this->respond([
                    'STATUS' => false,
                    'message' => 'Failed to retrieve CM Dashboard Count.',
                    'data' => $CM_DashboardCount
               ], 500);
          }
     }

     // Returns latest task (last entry) per branch for the selected month and filters
     public function getLatestTasksByBranch()
     {
          $bmModel = new \App\Models\BM_TasksModel();
          $userDetails = $this->validateAuthorization();
          $user = is_object($userDetails) ? ($userDetails->emp_code ?? '') : '';
          $role = is_object($userDetails) ? ($userDetails->role ?? '') : '';
          // Retrieve POST data safely
          $requestData = $this->request->getJSON() ?? new \stdClass();
          $selectedMonth = isset($requestData->selectedMonth) ? $requestData->selectedMonth : date('Y-m');
          $selectedCluster = isset($requestData->selectedCluster) ? $requestData->selectedCluster : '0';
          $selectedBranch = isset($requestData->selectedBranch) ? $requestData->selectedBranch : '0';

          // Optional debug flag (set `debug: true` in request JSON to return resolved branch ids and cluster mapping)
          $debugFlag = isset($requestData->debug) && $requestData->debug;

          // Build branch list based on cluster or explicit branch
          $branchIds = [];
          $db2 = \Config\Database::connect('default');

          // Prefer clusters with branches accessible to this user (uses secondary DB branch names mapping)
          $deptModel = new \App\Models\DeptModel();
          $clustersForUser = $deptModel->getClustersWithBranches($user); // may return [] if user has no mapping

          // If a cluster(s) was selected, try to resolve branch IDs from the user's allowed cluster list first
          if (!empty($selectedCluster) && intval($selectedCluster) > 0) {
               // Handle multiple cluster IDs (comma-separated)
               $selectedClusterIds = array_map('trim', explode(',', (string)$selectedCluster));
               $collected = [];

               if (!empty($clustersForUser)) {
                    foreach ($clustersForUser as $c) {
                         if (in_array((string)$c['cluster_id'], $selectedClusterIds, true)) {
                              if (!empty($c['branches'])) {
                                   $branchList = array_filter(array_map('trim', explode(',', $c['branches'])));
                                   $collected = array_merge($collected, $branchList);
                              }
                         }
                    }
               }

               // If clustersForUser did not yield branch IDs for the selected cluster(s), fall back to cluster_branch_map / clusters table
               if (empty($collected)) {
                    foreach ($selectedClusterIds as $cid) {
                         if (intval($cid) > 0) {
                              $rows = $db2->table('cluster_branch_map')->select('branch_id')->where('cluster_id', $cid)->get()->getResultArray();
                              if (!empty($rows)) {
                                   $collected = array_merge($collected, array_column($rows, 'branch_id'));
                              } else {
                                   $clusterRow = $db2->table('clusters')->select('branches')->where('cluster_id', $cid)->get()->getRowArray();
                                   if (!empty($clusterRow) && !empty($clusterRow['branches'])) {
                                        $collected = array_merge($collected, array_filter(array_map('trim', explode(',', $clusterRow['branches']))));
                                   }
                              }
                         }
                    }
               }

               $branchIds = array_values(array_unique(array_map('strval', $collected)));
          }

          // If a specific branch was provided, prefer it (override cluster filter)
          if (!empty($selectedBranch) && intval($selectedBranch) > 0) {
               $branchIds = [(string)$selectedBranch];
          }

          // Log the cluster/branch filter used for debugging
          log_message('info', 'getLatestTasksByBranch - selectedCluster: ' . var_export($selectedCluster, true) . ', selectedBranch: ' . var_export($selectedBranch, true) . ', resolvedBranchIds: ' . var_export($branchIds, true));

          // If no explicit branch/cluster filter provided, restrict to branches mapped to the user
          if (empty($branchIds)) {
               $userModel = new UserModel();
               $userBranches = $userModel->getUserBranchList($user, $role);
               if (!empty($userBranches)) {
                    $branchIds = array_map('strval', array_column($userBranches, 'branch_id'));
               } else {
                    // User has no mapped branches; return empty list (include debug info if requested)
                    $debugData = $debugFlag ? [
                         'clustersForUser' => $clustersForUser,
                         'resolvedBranchIds' => $branchIds,
                    ] : null;
                    return $this->respond([
                         'STATUS' => true,
                         'message' => 'No branches mapped for user',
                         'data' => [],
                         'debug' => $debugData
                    ], 200);
               }
          }

          // Log final branchIds used for DB query
          log_message('info', 'getLatestTasksByBranch - final branchIds: ' . var_export($branchIds, true));
          $results = $bmModel->getLatestTasksByBranch($selectedMonth, $branchIds);

          // If requested, include power and diesel latest entries as well
          $includeTypes = isset($requestData->includeTypes) && ($requestData->includeTypes === true || $requestData->includeTypes === 'all' || strtolower($requestData->includeTypes) === 'all');

          $combined = [];
          // Normalize BM results into common shape
          foreach ($results as $r) {
               $combined[] = [
                    'type' => 'bm',
                    'id' => $r['mid'] ?? null,
                    'branch' => $r['branch'] ?? null,
                    'branch_name' => $r['branch_name'] ?? '',
                    'date' => $r['taskDate'] ?? null,
                    'created_by_name' => $r['created_by_name'] ?? ($r['created_by'] ?? ''),
                    'raw' => $r,
               ];
          }

          if ($includeTypes && !empty($branchIds)) {
               $defaultDB = \Config\Database::connect('secondary');

               // Power consumption latest per branch
               $powerSub = $db2->table('power_consumption')
                    ->select('branch_id, MAX(consumption_date) as maxDate')
                    ->where("DATE_FORMAT(consumption_date, '%Y-%m')", $selectedMonth)
                    ->groupBy('branch_id');
               if (!empty($branchIds)) {
                    $powerSub->whereIn('branch_id', $branchIds);
               }
               $powerSubSql = $powerSub->getCompiledSelect();
               $powerSql = "SELECT pc.id, pc.branch_id, pc.consumption_date as date_val, pc.createdBy as created_by FROM power_consumption pc JOIN ({$powerSubSql}) sub ON pc.branch_id = sub.branch_id AND pc.consumption_date = sub.maxDate ORDER BY pc.branch_id";
               $powerRows = $db2->query($powerSql, [$selectedMonth])->getResultArray();

               foreach ($powerRows as $p) {
                    $branchRow = $defaultDB->table('Branches')->select('SysField as branch_name')->where('id', $p['branch_id'])->get()->getRowArray();
                    $emp = $defaultDB->table('new_emp_master')->select('fname, lname')->where('emp_code', $p['created_by'])->get()->getRowArray();
                    $combined[] = [
                         'type' => 'power',
                         'id' => $p['id'] ?? null,
                         'branch' => $p['branch_id'] ?? null,
                         'branch_name' => $branchRow['branch_name'] ?? '',
                         'date' => $p['date_val'] ?? null,
                         'created_by_name' => trim(($emp['fname'] ?? '') . ' ' . ($emp['lname'] ?? '')),
                         'raw' => $p,
                    ];
               }

               // Diesel consumption latest per branch
               $dieselSub = $db2->table('diesel_consumption')
                    ->select('branch_id, MAX(consumption_date) as maxDate')
                    ->where("DATE_FORMAT(consumption_date, '%Y-%m')", $selectedMonth)
                    ->groupBy('branch_id');
               if (!empty($branchIds)) {
                    $dieselSub->whereIn('branch_id', $branchIds);
               }
               $dieselSubSql = $dieselSub->getCompiledSelect();
               $dieselSql = "SELECT dc.id, dc.branch_id, dc.consumption_date as date_val, dc.createdBy as created_by FROM diesel_consumption dc JOIN ({$dieselSubSql}) sub ON dc.branch_id = sub.branch_id AND dc.consumption_date = sub.maxDate ORDER BY dc.branch_id";
               $dieselRows = $db2->query($dieselSql, [$selectedMonth])->getResultArray();

               foreach ($dieselRows as $d) {
                    $branchRow = $defaultDB->table('Branches')->select('SysField as branch_name')->where('id', $d['branch_id'])->get()->getRowArray();
                    $emp = $defaultDB->table('new_emp_master')->select('fname, lname')->where('emp_code', $d['created_by'])->get()->getRowArray();
                    $combined[] = [
                         'type' => 'diesel',
                         'id' => $d['id'] ?? null,
                         'branch' => $d['branch_id'] ?? null,
                         'branch_name' => $branchRow['branch_name'] ?? '',
                         'date' => $d['date_val'] ?? null,
                         'created_by_name' => trim(($emp['fname'] ?? '') . ' ' . ($emp['lname'] ?? '')),
                         'raw' => $d,
                    ];
               }
          }

          // Optionally sort combined by branch_name then type
          usort($combined, function ($a, $b) {
               $cmp = strcmp($a['branch_name'] ?? '', $b['branch_name'] ?? '');
               if ($cmp !== 0) return $cmp;
               return strcmp($a['type'] ?? '', $b['type'] ?? '');
          });

          $responsePayload = [
               'STATUS' => true,
               'message' => 'Latest tasks per branch',
               'data' => $combined
          ];

          if ($debugFlag) {
               $responsePayload['debug'] = [
                    'clustersForUser' => $clustersForUser,
                    'resolvedBranchIds' => $branchIds,
               ];
          }

          return $this->respond($responsePayload, 200);
     }


     private function validateAuthorization()
     {
          if (!class_exists('App\Services\JwtService')) {
               log_message('error', 'JwtService class not found');
               return $this->respond(['error' => 'JwtService class not found'], 500);
          }

          // Get the Authorization header and log it using null coalescing operator
          $authorizationHeader = $this->request->getHeader('Authorization')?->getValue();
          log_message('info', "Authorization header: {$authorizationHeader}");

          try {
               // Create an instance of JwtService and validate the token
               $jwtService = new JwtService();
               $result = $jwtService->validateToken($authorizationHeader);

               // Handle token validation errors 
               if (isset($result['error'])) {
                    log_message('error', $result['error']);
                    return $this->respond(['error' => $result['error']], $result['status'] ?? 401);
               }

               // Extract the decoded token and get the USER-ID
               return $result['data'] ?? null;
          } catch (\Exception $e) {
               log_message('error', "JWT validation failed: {$e->getMessage()}");
               return $this->respond(['error' => 'Invalid or expired token'], 401);
          }
     }
}

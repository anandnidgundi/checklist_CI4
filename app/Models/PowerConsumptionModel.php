<?php

namespace App\Models;

use CodeIgniter\Model;

class PowerConsumptionModel extends Model
{
     protected $table = 'power_consumption';
     protected $primaryKey = 'id';
     protected $allowedFields = [
          'branch_id',
          'consumption_date',
          'morning_units',
          'night_units',
          'total_consumption',
          'nonbusinesshours',
          'remarks',
          'createdBy',
          'createdDTM',
          'files'
     ];

     public function getPowerConsumptionList($role, $emp_code, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate)
     {
          $defaultDB = \Config\Database::connect('default');
          $db2 = \Config\Database::connect('secondary');
          $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
          $branchMap = array_column($branchData, 'SysField', 'id');

          // Get allowed branches
          $deptModel = new \App\Models\DeptModel();
          $userMap = $deptModel->getUserMap($emp_code);
          log_message('error', 'User Map: ' . json_encode($userMap));

          // Extract branches list from User Map
          $userBranchList = [];
          if (!empty($userMap) && isset($userMap[0]['branches'])) {
               $userBranchList = array_filter(array_map('trim', explode(',', $userMap[0]['branches'])));
          }
          log_message('error', 'User Branch List: ' . json_encode($userBranchList));

          // Build query
          // Build query on local DB for power consumption; employee details are in secondary DB
          $builder = $this->db->table('power_consumption pc')
               ->select('pc.*');

          if (!empty($selectedMonth) && empty($selectedDate) && empty($selectedToDate)) {
               $builder->where("DATE_FORMAT(pc.consumption_date, '%Y-%m')", $selectedMonth);
          }
          if (!empty($selectedDate) && !empty($selectedToDate)) {
               $builder->where('pc.consumption_date >=', $selectedDate);
               $builder->where('pc.consumption_date <=', $selectedToDate);
          } elseif (!empty($selectedDate)) {
               $builder->where('pc.consumption_date', $selectedDate);
          }

          // Branch filtering logic
          if (!empty($selectedBranch) && $selectedBranch > '0') {
               $builder->where('pc.branch_id', $selectedBranch);
          } else {
               if (!empty($userBranchList)) {
                    $builder->whereIn('pc.branch_id', $userBranchList);
               } else {
                    $builder->where('pc.id', -1); // No branches assigned, return no results
               }
          }

          $results = $builder->orderBy('pc.consumption_date', 'desc')->get()->getResultArray();

          // Pull employee details from secondary DB (new_emp_master) by emp_code and map them
          $empCodes = array_unique(array_filter(array_column($results, 'createdBy')));
          $empMap = [];
          if (!empty($empCodes)) {
               $empRows = $db2->table('new_emp_master')
                    ->select('emp_code, comp_name, designation_name, dept_name')
                    ->whereIn('emp_code', $empCodes)
                    ->get()
                    ->getResultArray();
               foreach ($empRows as $er) {
                    $empMap[$er['emp_code']] = $er;
               }
          }

          // Collect power IDs for bulk file fetching
          $powerIds = array_column($results, 'id');
          $files = [];
          if (!empty($powerIds)) {
               $fileRows = $this->db->table('files')
                    ->select('power_id, file_name')
                    ->whereIn('power_id', $powerIds)
                    ->get()
                    ->getResultArray();
               foreach ($fileRows as $file) {
                    $files[$file['power_id']][] = $file['file_name'];
               }
          }

          // Attach employee, cluster, zone, branch_name, files, and ensure all string fields are not null
          foreach ($results as &$row) {
               // Attach employee information from secondary DB (if available)
               $empCode = $row['createdBy'] ?? '';
               $emp = $empMap[$empCode] ?? null;
               $row['employee'] = [
                    'emp_code' => $empCode ?? '',
                    'comp_name' => $emp['comp_name'] ?? '',
                    'designation_name' => $emp['designation_name'] ?? '',
                    'dept_name' => $emp['dept_name'] ?? '',
               ];

               // Cluster
               $clusterRow = $defaultDB->table('clusters')
                    ->select('cluster_id')
                    ->where("FIND_IN_SET('{$row['branch_id']}', branches) !=", 0)
                    ->get()->getRowArray();
               $row['cluster_id'] = $clusterRow['cluster_id'] ?? '';

               // Zone
               $zoneRow = $defaultDB->table('zones')
                    ->select('z_id')
                    ->where("FIND_IN_SET('{$row['branch_id']}', branches) !=", 0)
                    ->get()->getRowArray();
               $row['zone_id'] = $zoneRow['z_id'] ?? '';

               // Ensure string fields are never null or undefined
               $row['branch_name'] = isset($branchMap[$row['branch_id']]) && $branchMap[$row['branch_id']] !== null
                    ? $branchMap[$row['branch_id']]
                    : '';
               $row['remarks'] = isset($row['remarks']) && $row['remarks'] !== null
                    ? $row['remarks']
                    : '';
               $row['zone'] = isset($row['zone']) && $row['zone'] !== null
                    ? $row['zone']
                    : '';
               $row['cluster'] = isset($row['cluster']) && $row['cluster'] !== null
                    ? $row['cluster']
                    : '';

               $row['files'] = [];

               if (!empty($files[$row['id']]) && is_array($files[$row['id']])) {
                    foreach ($files[$row['id']] as $file) {
                         // Only include valid string file names
                         if (is_string($file)) {
                              $cleanFile = trim($file);
                              if ($cleanFile !== '') {
                                   $row['files'][] = $cleanFile;
                              }
                         }
                    }
               }

               // Ensure 'files' is always an array of strings
               if (!isset($row['files']) || !is_array($row['files'])) {
                    $row['files'] = [];
               }
          }

          return $results;
     }
     // public function getPowerConsumptionList($role, $emp_code,  $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate)
     // {
     //      $defaultDB = \Config\Database::connect('default');
     //      $db2 = \Config\Database::connect('secondary');
     //      $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
     //      $branchMap = array_column($branchData, 'SysField', 'id');
     //      // Get allowed branches
     //      $deptModel = new \App\Models\DeptModel();          // Fetch user map
     //      $userMap = $deptModel->getUserMap($emp_code);
     //      log_message('error', 'User Map: ' . json_encode($userMap));
     //      // Extract branches list from User Map
     //      $userBranchList = [];
     //      if (!empty($userMap) && isset($userMap[0]['branches'])) {
     //           $userBranchList = explode(',', $userMap[0]['branches']); // Convert comma-separated branches into an array
     //      }
     //      // Log the extracted branches for debugging
     //      log_message('error', 'User Branch List: ' . json_encode($userBranchList));
     //      // Step 2: Build query
     //      $builder = $this->db->table('power_consumption pc')
     //           ->select('pc.*, em.emp_code, em.comp_name, em.designation_name, em.dept_name')
     //           ->join('new_emp_master em', 'em.emp_code = pc.createdBy', 'left');
     //      if (!empty($selectedMonth) && empty($selectedDate) && empty($selectedToDate)) {
     //           $builder->where("DATE_FORMAT(pc.consumption_date, '%Y-%m')", $selectedMonth);
     //      }
     //      if (!empty($selectedDate) && !empty($selectedToDate)) {
     //           $builder->where('pc.consumption_date >=', $selectedDate);
     //           $builder->where('pc.consumption_date <=', $selectedToDate);
     //      } elseif (!empty($selectedDate)) {
     //           $builder->where('pc.consumption_date', $selectedDate);
     //      }
     //      // Apply branch filtering logic
     //      if (!empty($selectedBranch) && $selectedBranch > '0') {
     //           // Specific branch is selected
     //           $builder->where('pc.branch_id', $selectedBranch);
     //      } else {
     //           // No branch selected, use user's branch list
     //           if (!empty($userBranchList)) {
     //                $builder->whereIn('pc.branch_id', $userBranchList);
     //           } else {
     //                // If user has no branches assigned, return no results
     //                $builder->where('pc.id', -1); // This will ensure no records match
     //           }
     //      }
     //      $results = $builder->orderBy('pc.consumption_date', 'desc')->get()->getResultArray();
     //      // Collect power IDs for bulk file fetching
     //      $powerIds = array_column($results, 'id');
     //      // Fetch all files in bulk
     //      $files = [];
     //      if (!empty($powerIds)) {
     //           $fileRows = $this->db->table('files')
     //                ->select('power_id, file_name')
     //                ->whereIn('power_id', $powerIds)
     //                ->get()
     //                ->getResultArray();
     //           foreach ($fileRows as $file) {
     //                $files[$file['power_id']][] = $file['file_name'];
     //           }
     //      }
     //      // Step 3: Attach employee + cluster + zone
     //      foreach ($results as &$row) {
     //           $row['employee'] = [
     //                'emp_code' => $row['emp_code'],
     //                'comp_name' => $row['comp_name'],
     //                'designation_name' => $row['designation_name'],
     //                'dept_name' => $row['dept_name'],
     //           ];
     //           unset($row['emp_code'], $row['comp_name'], $row['designation_name'], $row['dept_name']);
     //           // Cluster
     //           $clusterRow = $defaultDB->table('clusters')
     //                ->select('cluster_id')
     //                ->where("FIND_IN_SET('{$row['branch_id']}', branches) !=", 0)
     //                ->get()->getRowArray();
     //           $row['cluster_id'] = $clusterRow['cluster_id'] ?? null;
     //           // Zone
     //           $zoneRow = $defaultDB->table('zones')
     //                ->select('z_id')
     //                ->where("FIND_IN_SET('{$row['branch_id']}', branches) !=", 0)
     //                ->get()->getRowArray();
     //           $row['zone_id'] = $zoneRow['z_id'] ?? null;
     //           $row['branch_name'] = $branchMap[$row['branch_id']] ?? '';
     //           $row['files'] = $files[$row['id']] ?? [];
     //      }
     //      return $results;
     // }
     // public function getPowerConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth)
     // {
     //     $db2 = \Config\Database::connect('secondary'); // secondary DB
     //     $builder = $this->db->table('power_consumption as pc')
     //         ->select('pc.*, bm.branches, bm.cluster as user_cluster, pc.createdBy') // include createdBy for mapping
     //         ->join('user_map as bm', 'FIND_IN_SET(pc.branch_id, bm.branches)', 'left')
     //         ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth);
     //     // Apply filters
     //     if ($selectedCluster > 0) {
     //         $builder->where('pc.cluster_id', $selectedCluster);
     //     } else if ($selectedBranch > 0) {
     //         $builder->where('pc.branch_id', $selectedBranch);
     //     } else if ($zone_id > 0) {
     //         $builder->where('pc.zone_id', $zone_id);
     //     }
     //     $query = $builder->get();
     //     $result = $query->getResultArray();
     //     // Step 1: Collect all unique createdBy emp_codes
     //     $empCodes = array_column($result, 'createdBy');
     //     $empCodes = array_filter(array_unique($empCodes));
     //     // Step 2: Fetch employee info from secondary DB
     //     $empData = [];
     //     if (!empty($empCodes)) {
     //         $empRows = $db2->table('new_emp_master')
     //             ->select('emp_code, comp_name, designation_name, dept_name') // Add more fields if needed
     //             ->whereIn('emp_code', $empCodes)
     //             ->get()
     //             ->getResultArray();
     //         foreach ($empRows as $emp) {
     //             $empData[$emp['emp_code']] = $emp;
     //         }
     //     }
     //     // Step 3: Attach files and emp info
     //     foreach ($result as $key => $value) {
     //         $powerId = $value['id'];
     //         // Attach files
     //         $filesQuery = $this->db->table('files')
     //             ->select('file_name')
     //             ->where('power_id', $powerId)
     //             ->get();
     //         $result[$key]['files'] = $filesQuery->getResultArray();
     //         // Attach emp info from db2
     //         $empCode = $value['createdBy'];
     //         $result[$key]['employee'] = $empData[$empCode] ?? null;
     //     }
     //     // Step 4: De-duplicate by diesel ID
     //     $uniqueResults = [];
     //     $seenIds = [];
     //     foreach ($result as $item) {
     //         if (!in_array($item['id'], $seenIds)) {
     //             $seenIds[] = $item['id'];
     //             $uniqueResults[] = $item;
     //         }
     //     }
     //     return $uniqueResults;
     // }
     private function getBranchData()
     {
          $db2 = \Config\Database::connect('secondary'); // Secondary database for branch data
          $defaultDB = \Config\Database::connect('default'); // Default database for cluster data
          // Query to fetch branch data with cluster mapping
          $builder = $db2->table('Branches b')
               ->select('b.id, b.SysField, c.cluster')
               ->join($defaultDB->database . '.clusters c', 'FIND_IN_SET(b.id, c.branches)', 'left');
          return $builder->get()->getResultArray();
     }
     public function getPowerConsumptionAdminList($role, $emp_code, $selectedZone, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate)
     {
          log_message('error', 'getPowerConsumptionAdminList called with params: ' . json_encode(func_get_args()));
          $db2 = \Config\Database::connect('secondary'); // For emp and branches data
          $defaultDB = \Config\Database::connect('default'); // Connect to the default database
          // Fetch branch data
          $branchData = $this->getBranchData();
          // Create employee and branch lookup maps
          $empData = $db2->table('new_emp_master')->select('emp_code, fname, lname')->get()->getResultArray();
          $empMap = [];
          foreach ($empData as $emp) {
               $empMap[$emp['emp_code']] = $emp;
          }
          $branchMap = [];
          foreach ($branchData as $branch) {
               $branchMap[$branch['id']] = $branch['SysField'];
          }
          $builder = $this->db->table('power_consumption as pc')
               ->select('pc.*, pc.createdBy');
          // Zone filter
          if (!empty($selectedZone) && $selectedZone !== '0') {
               $builder->where('pc.zone_id', $selectedZone);
          }
          // Cluster filter
          if (!empty($selectedCluster) && $selectedCluster !== '0') {
               $builder->where('pc.cluster_id', $selectedCluster);
          }
          // Date filtering
          if (!empty($selectedDate) && !empty($selectedToDate)) {
               $builder->where('pc.consumption_date >=', $selectedDate);
               $builder->where('pc.consumption_date <=', $selectedToDate);
          } elseif (!empty($selectedDate)) {
               $builder->where('pc.consumption_date', $selectedDate);
          } elseif (!empty($selectedMonth)) {
               $startDate = $selectedMonth . '-01';
               $endDate = date('Y-m-d', strtotime("$startDate +1 month"));
               $builder->where('pc.consumption_date >=', $startDate);
               $builder->where('pc.consumption_date <', $endDate);
          }
          // Branch filter
          // Branch filter
          if (!empty($selectedBranch) && $selectedBranch != '0') {
               $branchArray = is_array($selectedBranch) ? $selectedBranch : explode(',', $selectedBranch);
               $cleanBranches = array_filter($branchArray, fn($b) => is_numeric($b) && $b > 0);
               if (!empty($cleanBranches)) {
                    $builder->whereIn('pc.branch_id', $cleanBranches);
               }
          }
          // Else: No branch filter applied when both are 0
          //  log_message('error', 'Generated SQL Query: ' . $builder->getCompiledSelect());
          $result = $builder->get()->getResultArray();
          // Get employee data
          foreach ($result as &$task) {
               $taskId = $task['id'];
               $emp = $empMap[$task['createdBy']] ?? null;
               $task['fname'] = $emp['fname'] ?? '';
               $task['lname'] = $emp['lname'] ?? '';
               $task['branch_name'] = $branchMap[$task['branch_id']] ?? '';
               // Cluster info
               $clusterInfo = $defaultDB->table('clusters')
                    ->select('cluster_id, cluster')
                    ->where("FIND_IN_SET('" . $task['branch_id'] . "', branches)")
                    ->get()
                    ->getRowArray();
               $task['cluster_id'] = $clusterInfo['cluster_id'] ?? '';
               $task['cluster'] = $clusterInfo['cluster'] ?? '';
               // Zone info
               $zoneInfo = $defaultDB->table('zones')
                    ->select('z_id, zone')
                    ->where("FIND_IN_SET('" . $task['branch_id'] . "', branches)")
                    ->get()
                    ->getRowArray();
               $task['zone_id'] = $zoneInfo['z_id'] ?? '';
               $task['zone'] = $zoneInfo['zone'] ?? '';
          }
          // Get files
          $powerIds = array_column($result, 'id');
          $files = [];
          if (!empty($powerIds)) {
               $fileRows = $this->db->table('files')
                    ->select('power_id, file_name')
                    ->whereIn('power_id', $powerIds)
                    ->get()
                    ->getResultArray();
               foreach ($fileRows as $file) {
                    $files[$file['power_id']][] = $file['file_name'];
               }
          }
          // Merge data
          foreach ($result as $k => $row) {
               $result[$k]['files'] = array_map(function ($file_name) {
                    return ['file_name' => $file_name];
               }, $files[$row['id']] ?? []);
               $result[$k]['employee'] = $empData[$row['createdBy']] ?? null;
          }
          if (!empty($selectedBranch) && $selectedBranch != '0') {
               $allowedBranchIds = is_array($selectedBranch) ? $selectedBranch : explode(',', $selectedBranch);
               $allowedBranchIds = array_map('intval', $allowedBranchIds); // ensure numeric type
               $result = array_filter($result, function ($row) use ($allowedBranchIds) {
                    return in_array((int)$row['branch_id'], $allowedBranchIds, true);
               });
               // Reindex array to avoid gaps in keys
               $result = array_values($result);
          }
          // log_message('error', 'Returned Branch IDs: ' . implode(', ', array_column($result, 'branch_id')));
          return $result;
     }
     // public function getPowerConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate)
     // {
     //      $db2 = \Config\Database::connect('secondary'); // Secondary database connection
     //      $builder = $this->db->table('power_consumption as pc')
     //           ->select('pc.*, pc.createdBy');
     //      // Apply date filters
     //      if (!empty($selectedDate) && !empty($selectedToDate)) {
     //           $builder->where('pc.consumption_date >=', $selectedDate);
     //           $builder->where('pc.consumption_date <=', $selectedToDate);
     //      } elseif (!empty($selectedDate)) {
     //           $builder->where('pc.consumption_date', $selectedDate);
     //      } elseif (!empty($selectedMonth)) {
     //           $builder->where('pc.consumption_date >=', $selectedMonth . '-01');
     //           $builder->where('pc.consumption_date <', $selectedMonth . '-01' . ' + INTERVAL 1 MONTH');
     //      }
     //      // Apply zone filter if provided
     //      if (!empty($zone_id) && $zone_id > 0) {
     //           $builder->where('pc.zone_id', $zone_id);
     //      }
     //      // Apply cluster filter if provided
     //      if (!empty($selectedCluster) && $selectedCluster > 0) {
     //           $builder->where('pc.cluster_id', $selectedCluster);
     //      }
     //      // Apply branch filter if provided
     //      if (!empty($selectedBranch) && $selectedBranch > 0) {
     //           $builder->where('pc.branch_id', $selectedBranch);
     //      }
     //      // Log the generated SQL query for debugging
     //      log_message('error', 'Generated SQL Query: ' . $builder->getCompiledSelect());
     //      // Execute the query and fetch results
     //      $query = $builder->get(); 
     //      $result = $query->getResultArray();
     //      // Collect unique emp_codes
     //      $empCodes = array_column($result, 'createdBy');
     //      $empCodes = array_filter(array_unique($empCodes));
     //      // Fetch employee data in bulk
     //      $empData = [];
     //      if (!empty($empCodes)) {
     //           $empRows = $db2->table('new_emp_master')
     //                ->select('emp_code, comp_name, designation_name, dept_name')
     //                ->whereIn('emp_code', $empCodes)
     //                ->get()
     //                ->getResultArray();
     //           foreach ($empRows as $emp) {
     //                $empData[$emp['emp_code']] = $emp;
     //           }
     //      }
     //      // Fetch all files in bulk
     //      $powerIds = array_column($result, 'id');
     //      $files = [];
     //      if (!empty($powerIds)) {
     //           $fileRows = $this->db->table('files')
     //                ->select('power_id, file_name')
     //                ->whereIn('power_id', $powerIds)
     //                ->get()
     //                ->getResultArray();
     //           foreach ($fileRows as $file) {
     //                $files[$file['power_id']][] = $file['file_name'];
     //           }
     //      }
     //      // Attach files and employee info to the results
     //      foreach ($result as $key => $value) {
     //           $powerId = $value['id'];
     //           $empCode = $value['createdBy'];
     //           $result[$key]['files'] = $files[$powerId] ?? [];
     //           $result[$key]['employee'] = $empData[$empCode] ?? null;
     //      }
     //      return $result;
     // }
     // public function getPowerConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate)
     // {
     //      $db2 = \Config\Database::connect('secondary'); // secondary DB
     //      // Get employee and branch data
     //      $empData = $db2->table('new_emp_master')->select('emp_code, fname, lname')->get()->getResultArray();
     //      $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
     //      $empMap = [];
     //      foreach ($empData as $emp) {
     //           $empMap[$emp['emp_code']] = $emp;
     //      }
     //      $branchMap = [];
     //      foreach ($branchData as $branch) {
     //           $branchMap[$branch['id']] = $branch['SysField'];
     //      }
     //      $builder = $this->db->table('power_consumption as pc')
     //           ->select('pc.*, pc.createdBy');
     //      // Apply date range filter if both from and to dates are provided
     //      if (!empty($selectedDate) && !empty($selectedToDate)) {
     //           $builder->where('pc.consumption_date >=', $selectedDate);
     //           $builder->where('pc.consumption_date <=', $selectedToDate);
     //      }
     //      // Else apply single date filter
     //      elseif (!empty($selectedDate)) {
     //           $builder->where('DATE(pc.consumption_date)', $selectedDate);
     //      }
     //      // Else apply month filter
     //      elseif (!empty($selectedMonth)) {
     //           $builder->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth);
     //      }
     //      // Apply cluster filter if selected
     //      if (!empty($selectedCluster) && $selectedCluster > 0) {
     //           $builder->where('pc.cluster_id', $selectedCluster);
     //      }
     //      // Apply branch filter if selected
     //      if (!empty($selectedBranch) && $selectedBranch > 0) {
     //           $builder->where('pc.branch_id', $selectedBranch);
     //      }
     //      $query = $builder->get();
     //      $result = $query->getResultArray();
     //      // Add branch and employee data to each result
     //      foreach ($result as &$row) {
     //           $row['branch_name'] = $branchMap[$row['branch_id']] ?? '';
     //           $row['employee'] = $empMap[$row['createdBy']] ?? null;
     //      }
     //      // Attach files to each result
     //      foreach ($result as $key => $value) {
     //           $powerId = $value['id'];
     //           $filesQuery = $this->db->table('files')
     //                ->select('file_name')
     //                ->where('power_id', $powerId)
     //                ->get();
     //           $result[$key]['files'] = $filesQuery->getResultArray();
     //      }
     //      return $result;
     // }
     public function getPowerConsumptionAdminListforbranch($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate)
     {
          $db2 = \Config\Database::connect('secondary'); // secondary DB
          // Get employee and branch data
          $empData = $db2->table('new_emp_master')->select('emp_code, fname, lname')->get()->getResultArray();
          $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
          $empMap = [];
          foreach ($empData as $emp) {
               $empMap[$emp['emp_code']] = $emp;
          }
          $branchMap = [];
          foreach ($branchData as $branch) {
               $branchMap[$branch['id']] = $branch['SysField'];
          }
          $builder = $this->db->table('power_consumption as pc')
               ->select('pc.*, pc.createdBy')
               ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth)
               ->orderBy('pc.createdDTM', 'DESC');
          // Apply filters based on date
          if ($selectedDate) {
               $builder->where('DATE_FORMAT(pc.consumption_date, "%Y-%m-%d")', $selectedDate);
          }
          // Apply filters
          if ($selectedBranch > 0) {
               $builder->where('pc.branch_id', $selectedBranch);
          }
          $query = $builder->get();
          $result = $query->getResultArray();
          // Add branch and employee data to each result
          foreach ($result as &$row) {
               $row['branch_name'] = $branchMap[$row['branch_id']] ?? '';
               $row['employee'] = $empMap[$row['createdBy']] ?? null;
          }
          // Attach files to each result
          foreach ($result as $key => $value) {
               $powerId = $value['id'];
               $filesQuery = $this->db->table('files')
                    ->select('file_name')
                    ->where('power_id', $powerId)
                    ->get();
               $result[$key]['files'] = $filesQuery->getResultArray();
          }
          return $result;
     }
     public function getPowerConsumptionAdminList_backup($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth)
     {
          $db2 = \Config\Database::connect('secondary'); // secondary DB
          // Get employee and branch data
          $empData = $db2->table('new_emp_master')->select('emp_code, fname, lname')->get()->getResultArray();
          $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
          $empMap = [];
          foreach ($empData as $emp) {
               $empMap[$emp['emp_code']] = $emp;
          }
          $branchMap = [];
          foreach ($branchData as $branch) {
               $branchMap[$branch['id']] = $branch['SysField'];
          }
          $builder = $this->db->table('power_consumption as pc')
               ->select('pc.*,  pc.createdBy')
               ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth);
          // Apply filters
          if ($selectedCluster > 0) {
               $builder->where('pc.cluster_id', $selectedCluster);
          } else if ($selectedBranch > 0) {
               $builder->where('pc.branch_id', $selectedBranch);
          } else if ($zone_id > 0) {
               $builder->where('pc.zone_id', $zone_id);
          }
          $query = $builder->get();
          $result = $query->getResultArray();
          // Add branch and employee data to each result
          foreach ($result as &$row) {
               $row['branch_name'] = $branchMap[$row['branch_id']] ?? '';
               $row['employee'] = $empMap[$row['createdBy']] ?? null;
          }
          // Attach files to each result
          foreach ($result as $key => $value) {
               $powerId = $value['id'];
               $filesQuery = $this->db->table('files')
                    ->select('file_name')
                    ->where('power_id', $powerId)
                    ->get();
               $result[$key]['files'] = $filesQuery->getResultArray();
          }
          return $result;
     }
     //getPowerConsumptionById($id)
     //getPowerConsumptionById($id)
     public function getPowerConsumptionById($id)
     {
          $builder = $this->db->table('power_consumption as pc')
               ->select('pc.*, bm.branch, bm.cluster_id, a.area')  // Remove cl.cluster from the selection
               ->join('branchesmapped as bm', 'bm.branch_id = pc.branch_id', 'left')
               ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
               ->join('area as a', 'cl.area_id = a.id', 'left')
               ->where('pc.id', $id);
          $query = $builder->get();
          return $query->getRowArray();
     }
     public function getUserBranchList($user, $role)
     {
          $builder = $this->db->table('user_map as bm')
               ->select('bm.emp_code, bm.branch_id, bm.branch, bm.cluster_id, bm.cluster, cl.area_id, a.area')
               ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
               ->join('area as a', 'cl.area_id = a.id', 'left');
          if ($role != 'SUPER_ADMIN') {
               $builder->where('bm.emp_code', $user);
          }
          $query = $builder->get();
          return $query->getResultArray();
     }
}

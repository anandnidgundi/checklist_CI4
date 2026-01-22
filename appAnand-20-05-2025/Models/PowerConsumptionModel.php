<?php
namespace App\Models;
use CodeIgniter\Model;
class PowerConsumptionModel extends Model
{
     protected $table = 'power_consumption';
     protected $primaryKey = 'id';
     protected $allowedFields = [
          'branch_id',
          'cluster_id',
          'zone_id',
          'morning_units',
          'consumption_date',
          'night_units',
          'total_consumption',
          'nonbusinesshours',
          'remarks',
          'createdBy',
          'createdDTM'
     ];
     public function getPowerConsumptionList($role, $emp_code, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate)
     {
          $db2 = \Config\Database::connect('secondary'); // secondary DB
          $builder = $this->db->table('power_consumption as pc')
               ->select('pc.*, pc.createdBy');
          if (!empty($selectedDate) && !empty($selectedToDate)) {
               $builder->where('pc.consumption_date >=', $selectedDate);
               $builder->where('pc.consumption_date <=', $selectedToDate);
          } elseif (!empty($selectedDate)) {
               $builder->where('DATE(pc.consumption_date)', $selectedDate);
          } elseif (!empty($selectedMonth)) {
               $builder->where('DATE_FORMAT(pc.consumption_date, "%Y-%m") =', $selectedMonth);
          }
          // Apply cluster filter if selected
          if (!empty($selectedCluster) && $selectedCluster > 0) {
               $builder->where('pc.cluster_id', $selectedCluster);
          }
          // Apply branch filter if selected
          if (!empty($selectedBranch) && $selectedBranch > 0) {
               $builder->where('pc.branch_id', $selectedBranch);
          }
          $query = $builder->get();
          $result = $query->getResultArray();
          // Step 1: Collect unique emp_codes
          $empCodes = array_column($result, 'createdBy');
          $empCodes = array_filter(array_unique($empCodes));
          // Step 2: Fetch employee data from secondary DB
          $empData = [];
          if (!empty($empCodes)) {
               $empRows = $db2->table('new_emp_master')
                    ->select('emp_code, comp_name, designation_name, dept_name') // add more fields if needed
                    ->whereIn('emp_code', $empCodes)
                    ->get()
                    ->getResultArray();
               foreach ($empRows as $emp) {
                    $empData[$emp['emp_code']] = $emp;
               }
          }
          // Step 3: Attach files and employee info
          foreach ($result as $key => $value) {
               $powerId = $value['id'];
               // Attach files
               $filesQuery = $this->db->table('files')
                    ->select('file_name')
                    ->where('power_id', $powerId)
                    ->get();
               $result[$key]['files'] = $filesQuery->getResultArray();
               // Attach emp info from secondary DB
               $empCode = $value['createdBy'];
               $result[$key]['employee'] = $empData[$empCode] ?? null;
          }
          return $result;
     }
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
     public function getPowerConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate, $selectedToDate)
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
               ->select('pc.*, pc.createdBy');
          // Apply date range filter if both from and to dates are provided
          // if (!empty($selectedDate) && !empty($selectedToDate)) {
          //      $builder->where('pc.consumption_date >=', $selectedDate);
          //      $builder->where('pc.consumption_date <=', $selectedToDate);
          // }
          // // Else apply single date filter
          // elseif (!empty($selectedDate)) {
          //      $builder->where('DATE(pc.consumption_date)', $selectedDate);
          // }
          // // Else apply month filter
          // elseif (!empty($selectedMonth)) {
          //      $builder->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth);
          // }
          if (!empty($selectedDate) && !empty($selectedToDate)) {
               $builder->where('pc.consumption_date >=', $selectedDate);
               $builder->where('pc.consumption_date <=', $selectedToDate);
          } elseif (!empty($selectedDate)) {
               $builder->where('DATE(pc.consumption_date)', $selectedDate);
          } elseif (!empty($selectedMonth)) {
               $builder->where('DATE_FORMAT(pc.consumption_date, "%Y-%m") =', $selectedMonth);
          }
          // Apply cluster filter if selected
          if (!empty($selectedCluster) && $selectedCluster > 0) {
               $builder->where('pc.cluster_id', $selectedCluster);
          }
          // Apply branch filter if selected
          if (!empty($selectedBranch) && $selectedBranch > 0) {
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
     public function getPowerConsumptionById($id)
     {
          $builder = $this->db->table('power_consumption as pc')
               ->select('pc.*, bm.branch, bm.cluster_id, a.area')
               ->join('branchesmapped as bm', 'bm.branch_id = pc.branch_id', 'left')
               ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
               ->join('area as a', 'cl.area_id = a.id', 'left')
               ->where('pc.id', $id);
          $query = $builder->get();
          return $query->getRowArray();
     }
     public function getUserBranchList($user, $role)
     {
          $builder = $this->db->table('branchesmapped as bm')
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

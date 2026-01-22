<?php

namespace App\Models;

use CodeIgniter\Model;

class HkRequirementsModel extends Model
{
     protected $table = 'hk_requirements';
     protected $primaryKey = 'hkr_id';
     protected $allowedFields = [
          'for_month',
          'created_by',
          'remarks',
          'admin_remarks',
          'status',
          'created_dtm',
          'approved_by',
          'approved_dtm',
          'branch_id',
          'cluster_id',
          'isDeleted',
          'applied_amount',
          'budget_amount'
     ];

     protected $useTimestamps = false;
     protected $returnType = 'array';

     /**
      * Get requirements with employee details
      */


     public function getRequirementsWithDetails($filters = [])
     {
          $db2 = \Config\Database::connect('secondary');
          $defaultDB = \Config\Database::connect('default');

          // Build the main query from hk_requirements
          $builder = $defaultDB->table('hk_requirements hr')
               ->select('hr.*')
               ->where('hr.isDeleted', 'N');

          // Apply filters
          if (!empty($filters['month'])) {
               $builder->where('hr.for_month', $filters['month']);
          }

          if (!empty($filters['branch_id']) && $filters['branch_id'] != '0') {
               $builder->where('hr.branch_id', $filters['branch_id']);
          }

          if (!empty($filters['cluster_id']) && $filters['cluster_id'] != '0') {
               $builder->where('hr.cluster_id', $filters['cluster_id']);
          }

          if (!empty($filters['status'])) {
               $builder->where('hr.status', $filters['status']);
          }

          // Handle user branch filtering for non-admin users
          if (!empty($filters['user_branches']) && is_array($filters['user_branches'])) {
               $builder->whereIn('hr.branch_id', $filters['user_branches']);
          }

          $builder->orderBy('hr.created_dtm', 'DESC');


          $results = $builder->get()->getResultArray();


          // Get branch data from secondary database
          $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
          $branchMap = array_column($branchData, 'SysField', 'id');

          // Get employee data from secondary database
          $empCodes = array_unique(array_column($results, 'created_by'));
          $employeeData = [];
          if (!empty($empCodes)) {
               $employees = $db2->table('new_emp_master')
                    ->select('emp_code, comp_name, designation_name, dept_name')
                    ->whereIn('emp_code', $empCodes)
                    ->get()->getResultArray();
               $employeeData = array_column($employees, null, 'emp_code');
          }

          // Enhance results with branch names, employee info, and cluster/zone info
          foreach ($results as &$row) {
               // Add employee information
               $empCode = $row['created_by'] ?? '';
               $row['employee'] = [
                    'emp_code' => $empCode,
                    'comp_name' => $employeeData[$empCode]['comp_name'] ?? '',
                    'designation_name' => $employeeData[$empCode]['designation_name'] ?? '',
                    'dept_name' => $employeeData[$empCode]['dept_name'] ?? '',
               ];

               // Add branch name
               $row['branch_name'] = $branchMap[$row['branch_id']] ?? '';

               // Get cluster info
               $clusterRow = $defaultDB->table('clusters')
                    ->select('cluster_id, cluster')
                    ->where("FIND_IN_SET('{$row['branch_id']}', branches) !=", 0)
                    ->get()->getRowArray();
               $row['cluster_name'] = $clusterRow['cluster'] ?? '';

               // Get zone info
               $zoneRow = $defaultDB->table('zones')
                    ->select('z_id, zone')
                    ->where("FIND_IN_SET('{$row['branch_id']}', branches) !=", 0)
                    ->get()->getRowArray();
               $row['zone_name'] = $zoneRow['zone'] ?? '';
          }

          return $results;
     }
     public function getRequirementByMonthAndBranch($month, $branch_id)
     {
          return $this->where('for_month', $month)
               ->where('branch_id', $branch_id)
               ->where('isDeleted', 'N')
               ->first();
     }

     /**
      * Add new housekeeping requirement
      */
     public function addRequirement($data)
     {
          $data['created_dtm'] = date('Y-m-d H:i:s');
          return $this->insert($data);
     }

     /**
      * Update requirement status
      */
     public function updateStatus($hkr_id, $status, $approved_by, $admin_remarks = null)
     {
          $data = [
               'status' => $status,
               'approved_by' => $approved_by,
               'approved_dtm' => date('Y-m-d H:i:s')
          ];

          // Add admin_remarks if provided
          if ($admin_remarks !== null) {
               $data['admin_remarks'] = $admin_remarks;
          }

          return $this->update($hkr_id, $data);
     }

     /**
      * Soft delete requirement
      */
     public function softDelete($hkr_id)
     {
          return $this->update($hkr_id, ['isDeleted' => 'Y']);
     }

     /**
      * Get requirements by employee
      */
     public function getRequirementsByEmployee($emp_code, $filters = [])
     {
          $builder = $this->builder();
          $builder->where('created_by', $emp_code);
          $builder->where('isDeleted', 'N');

          if (!empty($filters['month'])) {
               $builder->where('for_month', $filters['month']);
          }

          if (!empty($filters['status'])) {
               $builder->where('status', $filters['status']);
          }

          return $builder->orderBy('created_dtm', 'DESC')->get()->getResultArray();
     }

     public function getBranchLists()
     {
          $db2 = \Config\Database::connect('secondary');
          return $db2->table('branches')
               ->select('*')
               ->where('Status', 'A')
               ->get()
               ->getResultArray();
     }

     public function getBranchById($branch_id)
     {
          $db2 = \Config\Database::connect('secondary');
          return $db2->table('branches')
               ->select('id as branch_id, SysField as branch_name')
               ->where('Status', 'A')
               ->where('id', $branch_id)
               ->get()
               ->getRowArray();
     }
}

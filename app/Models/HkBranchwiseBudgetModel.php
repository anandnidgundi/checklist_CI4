<?php

namespace App\Models;

use CodeIgniter\Model;

class HkBranchwiseBudgetModel extends Model
{
     protected $table = 'hk_branchwise_budget';
     protected $primaryKey = 'id';
     protected $allowedFields = ['branch_id', 'cluster_id', 'budget'];

     /**
      * Update budget by branch ID
      */
     public function updateBudgetByBranch($branch_id, $data)
     {
          return $this->where('branch_id', $branch_id)->set($data)->update();
     }

     // deleteBudgetByBranch($branch_id)
     public function deleteBudgetByBranch($branch_id)
     {
          return $this->where('branch_id', $branch_id)->delete();
     }
     /**
      * Insert or update budget (called by controller)
      */
     public function insertOrUpdateBudget($data)
     {
          $existing = $this->where('branch_id', $data['branch_id'])->first();

          if ($existing) {
               return $this->update($existing['id'], [
                    'budget' => $data['budget']
               ]);
          } else {
               // Get cluster_id from branch
               $defaultDB = \Config\Database::connect('default');
               $clusterInfo = $defaultDB->table('clusters')
                    ->select('cluster_id')
                    ->where("FIND_IN_SET('{$data['branch_id']}', branches) !=", 0)
                    ->get()
                    ->getRowArray();

               return $this->insert([
                    'branch_id' => $data['branch_id'],
                    'cluster_id' => $clusterInfo['cluster_id'] ?? null,
                    'budget' => $data['budget']
               ]);
          }
     }

     /**
      * Get all budgets (called by controller)
      */
     public function getAllBudgets()
     {
          return $this->getBudgetsWithBranchDetails();
     }


     /**
      * Get budget by branch ID
      */
     public function getBudgetByBranch($branch_id)
     {
          return $this->where('branch_id', $branch_id)->first();
     }

     /**
      * Get budget by cluster ID
      */
     public function getBudgetByCluster($cluster_id)
     {
          return $this->where('cluster_id', $cluster_id)->findAll();
     }

     /**
      * Update or insert budget
      */
     public function upsertBudget($branch_id, $cluster_id, $budget)
     {
          $existing = $this->where('branch_id', $branch_id)->first();

          if ($existing) {
               return $this->update($existing['id'], [
                    'cluster_id' => $cluster_id,
                    'budget' => $budget
               ]);
          } else {
               return $this->insert([
                    'branch_id' => $branch_id,
                    'cluster_id' => $cluster_id,
                    'budget' => $budget
               ]);
          }
     }

     /**
      * Get all budgets with branch details
      */
     public function getBudgetsWithBranchDetails()
     {
          $db2 = \Config\Database::connect('secondary');

          $builder = $this->db->table('hk_branchwise_budget hbb');

          $results = $builder->get()->getResultArray();

          // Get branch data
          $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
          $branchMap = array_column($branchData, 'SysField', 'id');

          foreach ($results as &$row) {
               $row['branch_name'] = $branchMap[$row['branch_id']] ?? '';
          }

          return $results;
     }
}

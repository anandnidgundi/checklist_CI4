<?php

namespace App\Models;

use CodeIgniter\Model;

class BmWeeklyTaskModel extends Model
{

        protected $table = 'bm_weekly_list'; // Primary table
        protected $primaryKey = 'bmw_id';
        protected $allowedFields = ['equipment', 
            'branch_id',
            'cluster_id',
            'w_0100',
            'w_0101',
            'w_0102',
            'w_0200',
            'w_0201',
            'w_0202',
            'w_0300',
            'w_0301',
            'w_0302',
            'w_0400',
            'w_0401',
            'w_0402',
            'w_0500',
            'w_0501',
            'w_0502',
            'w_0600',
            'w_0601',
            'w_0602',
            'w_0700',
            'w_0701',
            'w_0702',
            'w_0800',
            'w_0801',
            'w_0802',
            'w_0900',
            'w_0901',
            'w_0902',
            'w_1000',
            'w_1001',
            'w_1002',
            'w_1100',
            'w_1101',
            'w_1102',
            'w_1200',
            'w_1201',
            'w_1202',
            'w_1300',
            'w_1301',
            'w_1302',
            'w_1400',
            'w_1401',
            'w_1402',
            'w_1500',
            'w_1501',
            'w_1502',
            'w_1600',
            'w_1601',
            'w_1602',
            'w_1700',
            'w_1701',
            'w_1702',
            'createdBy',
            'createdDTM',

            ];

            public function getUserBranchList($user, $role)
            {
                $builder = $this->db->table('branchesmapped as bm')
                    ->select('bm.branch_id as branch_id ') // Select only branch_id
                    ->join('cluster_branch_map as cl', 'cl.branch_id = bm.branch_id', 'left'); // Use 'inner' join if needed
                
                // Apply condition only if the role is not 'SUPER_ADMIN'
                if ($role != 'SUPER_ADMIN') {
                    $builder->where('bm.emp_code', $user);
                }
            
                // Execute the query and return branch_ids
                $query = $builder->get();
                $branchIds = array_column($query->getResultArray(), 'branch_id');
            
                // Log the fetched branch IDs
              //  log_message('error', "Branch IDs fetched from getUserBranchList: " . print_r($branchIds, true));
            
                // If no branches are found, log it and return empty array
                if (empty($branchIds)) {
                    //log_message('error', "No branches found for user {$user} with role {$role}");
                }
                
                return $branchIds; // Return only the branch_ids
            }


            

   
     public function getBmWeeklyTask($bmw_id)
    {
       $builder = $this->db->table( 'bm_weekly_list  b')
          ->select('b.*, n.fname, n.lname, br.branch as branch_name')
          ->join('new_emp_master n', 'n.emp_code = b.createdBy', 'left')
          ->join('branches br', 'br.branch_id = b.branch_id', 'left')
          ->where($this->primaryKey, $bmw_id);
       
       $query = $builder->get();
       return $query->getRowArray();       
    }

    public function getBmWeeklyTaskList($role, $user, $selectedMonth){

         // Fetch the user's branch IDs
         $branch  = $this->getUserBranchList($user, $role);    
         // Convert the branch IDs to strings
         $branchIds = array_map('strval', $branch);

        $builder = $this->db->table('bm_weekly_list b')
            ->select('b.*, n.fname, n.lname, br.branch as branch_name')
            ->join('new_emp_master n', 'n.emp_code = b.createdBy', 'left')
            ->join('branches br', 'br.branch_id = b.branch_id', 'left')
            ->where('DATE_FORMAT(b.createdDTM, "%Y-%m")', $selectedMonth) // Filter by selected month
            ->orderBy('b.createdDTM', 'DESC');

            if ($role != 'SUPER_ADMIN') {
                $builder->whereIn('b.branch_id', $branchIds);
            }
        $query = $builder->get();
        return $query->getResultArray();
    } 


   public function updateBmWeeklyTask($id, $filteredData){
        $builder = $this->db->table($this->table);
        $builder->where($this->primaryKey, $id);
        $builder->update($filteredData);
        return $this->db->affectedRows();
   }

 }
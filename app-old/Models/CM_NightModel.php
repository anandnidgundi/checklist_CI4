<?php

namespace App\Models;

use CodeIgniter\Model;

class CM_NightModel extends Model
 {
    protected $table = 'cm_night_tasks';
    // Primary table
    protected $primaryKey = 'cm_nid';
    protected $allowedFields = [
            'cm_nt0100' ,
        'cm_nt0200' ,
        'cm_nt0201' ,
        'cm_nt0300' ,
        'cm_nt0301' ,
        'cm_nt0400' ,
        'cm_nt0401' ,
        'cm_nt0500' ,
        'cm_nt0501' ,
        'cm_nt0600' ,
        'cm_nt0601' ,
        'cm_nt0700' ,
        'cm_nt0701' ,
        'cm_nt0800' ,
        'cm_nt0801' ,
        'cm_nt0900' ,
        'cm_nt0901' ,
        'cm_nt1000' ,
        'cm_nt1001' ,
        'cm_nt1100' ,
        'cm_nt1101' ,
        'cm_nt1200' ,
        'cm_nt1201' ,
        'cm_nt1300' ,
        'cm_nt1301' ,
        'cm_nt1400' ,
        'cm_nt1401' ,
        'cm_nt1500' ,
        'cm_nt1501' ,
        'cm_nt1600' ,
        'cm_nt1601' ,
        'cm_nt1700' ,
        'cm_nt1701' ,
        'cm_nt1800' ,
        'cm_nt1801' ,
        'cm_nt1900' ,
        'cm_nt1901' ,
        'cm_nt2000' ,
        'cm_nt2001' ,
        'cm_nt2100' ,
        'cm_nt2101' ,
        'cm_nt2200' ,
        'cm_nt2201' ,
        'cm_nt2300' ,
        'cm_nt2301' ,
        'cm_nt2400' ,
        'cm_nt2401' ,
        'cm_nt2500' ,
        'cm_nt2501' ,
        'cm_nt2600' ,
        'cm_nt2601' ,
        'cm_nt2700' ,
        'cm_nt2701' ,
        'cm_nt2800' ,
        'cm_nt2801' ,
        'cm_nt2900' ,
        'cm_nt2901' ,
        'cm_nt3000' ,
        'cm_nt3001' ,
        'cm_nt3100' ,
        'cm_nt3101' ,
        'cm_nt3200' ,
        'cm_nt3201' ,

        'cm_nt3300' ,
        'cm_nt3301' ,

        'cm_nt3400' ,
        'cm_nt3401' ,

        'cm_nt3500' ,
        'cm_nt3501' ,

        'cm_nt3600' ,
        'cm_nt3601' ,

        'cm_nt3700' ,
        'cm_nt3701' ,

        'cm_nt3800' ,
        'cm_nt3801' ,

        'cm_nt3900' ,
        'cm_nt3901' ,

        'cm_nt4000' ,
        'cm_nt4001' ,

        'cm_nt4100' ,
        'cm_nt4101' ,

        'emp_code'  ,
        'branch' ,
        'createdDTM' ,
        'created_by' ,

    ];

    public function  getCM_BranchNightTaskList($user, $role, $selectedMonth, $selectedBranch)
    {
        $branch = $this->getCMUserBranchList($user, $role);

        //log_message('error', "Branche selected: " . print_r($selectedBranch, true));

        if (empty($branch)) {
            //log_message('error', "No branches mapped for user {$user} with role {$role}.");
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }

        $branchIds = array_map('strval', $branch);
        // Start building the query
        $builder = $this->db->table('nighttasks m');
        $builder->select('m.*, n.fname, n.lname, b.branch as branch_name, c.cluster as cluster_name, z.zone as zone_name')
            ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left') 
            ->join('branches b', 'm.branch = b.branch_id', 'left')
            ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth)
            ->orderBy('m.taskDate', 'desc');
             
            
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            if($selectedBranch ==='All'){
                $builder->whereIn('m.branch', $branchIds);
            }else{
                $builder->where('m.branch', $selectedBranch);
            }            
        }
    
        // Execute the query 
        $query = $builder->get();
        $results = $query->getResultArray();
    
        if (!empty($results)) {
            // Fetch the MRI details for each night task
            foreach ($results as $key => $value) {
                $b = $this->db->table('mri as r');
                $b->select('r.*'); 
                $b->where('r.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['mri'] = $query->getResultArray();
            }   
            foreach ($results as $key => $value) {
                $b = $this->db->table('xray as x');
                $b->select('x.*'); 
                $b->where('x.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['xray'] = $query->getResultArray();
            }    
            foreach ($results as $key => $value) {
                $b = $this->db->table('ct as c');
                $b->select('c.*'); 
                $b->where('c.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['ct'] = $query->getResultArray();
            }    
            foreach ($results as $key => $value) {
                $b = $this->db->table('usg as u');
                $b->select('u.*'); 
                $b->where('u.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['usg'] = $query->getResultArray();
            }    
            foreach ($results as $key => $value) {
                $b = $this->db->table('usg as u');
                $b->select('u.*'); 
                $b->where('u.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['usg'] = $query->getResultArray();
            }         
            foreach ($results as $key => $value) {
                $b = $this->db->table('cardiologist_ecg as u');
                $b->select('u.*'); 
                $b->where('u.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['cardiologist_ecg'] = $query->getResultArray();
            }
            foreach ($results as $key => $value) {
                $b = $this->db->table('cardiologist_tmt as u');
                $b->select('u.*'); 
                $b->where('u.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['cardiologist_tmt'] = $query->getResultArray();
            }  
        }  
        // Log the query results for debugging
        //log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];

    }


    public function getCm_Z_NightTaskList($role, $user){
            
        // Fetch the user's branch IDs
        $cluster  = $this->getZonalManagerClusterList($user, $role);

        // Convert the branch IDs to strings
        $clusterIds = array_map('strval', $cluster);
    
        // Check if branchIds are valid
        if (!is_array($clusterIds) || empty($clusterIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }
    
        // Start building the query
        $builder = $this->db->table('cm_night_tasks m');
        $builder->select('m.*, n.fname, n.lname,  c.cluster as cluster_name, z.zone as zone_name')
            ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')                
            ->join('cluster_zone_map cz', 'cz.cluster_id = m.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cz.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('m.cluster_id', $clusterIds);
        }
    
        // Execute the query
        $query = $builder->get();
        $results = $query->getResultArray();
    
        // Log the query results for debugging
        //log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];
    }


    public function getBm_Z_NightTaskList($role, $user){
            
        // Fetch the user's branch IDs
        $branch  = $this->getZonalManagerBranchList($user, $role);

        // Convert the branch IDs to strings
        $branchIds = array_map('strval', $branch);
    
        // Check if branchIds are valid
        if (!is_array($branchIds) || empty($branchIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }
    
        // Start building the query
        $builder = $this->db->table('nighttasks m');
        $builder->select('m.*, n.fname, n.lname, b.branch as branch_name, c.cluster as cluster_name, z.zone as zone_name')
            ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left') 
            ->join('branches b', 'm.branch = b.branch_id', 'left')
            ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('m.branch', $branchIds);
        }
    
        // Execute the query
        $query = $builder->get();
        $results = $query->getResultArray();
    
        // Log the query results for debugging
        //log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];
    }


    public function getZonalManagerClusterList($user, $role){
        $b = $this->db->table('branchesmapped as b')
        ->select('b.zone_id')
        ->where('emp_code', $user)
        ->get();
        $b2 = $b->getResultArray();
        // Check if $b2 is not empty and extract all zone_ids
        if (!empty($b2)) {
            $zone_ids = array_column($b2, 'zone_id');
        } else {
            //log_message('error', "No zone_id found for user {$user}");
            return [];
        }
        $clusterIds = $this->db->table('cluster_zone_map')
            ->select('cluster_id')
            ->whereIn('zone_id', $zone_ids)
            ->get()
            ->getResultArray();
        $clusterIds = array_column($clusterIds, 'cluster_id');     

        // Log the fetched branch IDs
        //log_message('error', "Branch IDs fetched from getZonalManagerBranchList: " . print_r($clusterIds, true));

        // If no branches are found, log it and return empty array
        if (empty($clusterIds)) {
            //log_message('error', "No branches found for user {$user} with role {$role}");
        }
        return $clusterIds;
    }


    public function getZonalManagerBranchList($user, $role){
        $b = $this->db->table('branchesmapped as b')
        ->select('b.zone_id')
        ->where('emp_code', $user)
        ->get();
        $b2 = $b->getResultArray();

        // Check if $b2 is not empty and extract all zone_ids
        if (!empty($b2)) {
            $zone_ids = array_column($b2, 'zone_id');
        } else {
            //log_message('error', "No zone_id found for user {$user}");
            return [];
        }

        $clusterIds = $this->db->table('cluster_zone_map')
            ->select('cluster_id')
            ->whereIn('zone_id', $zone_ids)
            ->get()
            ->getResultArray();
        $clusterIds = array_column($clusterIds, 'cluster_id');

        $builder = $this->db->table('cluster_branch_map as cl')
            ->select('cl.branch_id as branch_id')
            ->join('branches as b', 'cl.branch_id = b.branch_id', 'left');

        // Apply condition only if the role is not 'SUPER_ADMIN'
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('cl.cluster_id', $clusterIds);
        }

        // Execute the query and return branch_ids
        $query = $builder->get();
        $branchIds = array_column($query->getResultArray(), 'branch_id');

        // Log the fetched branch IDs
        //log_message('error', "Branch IDs fetched from getZonalManagerBranchList: " . print_r($branchIds, true));

        // If no branches are found, log it and return empty array
        if (empty($branchIds)) {
            //log_message('error', "No branches found for user {$user} with role {$role}");
        }

        return $branchIds;
    }


    public function getBmcNightTaskList($role, $user){

        // Fetch the user's branch IDs
        $branch  = $this->getCMUserBranchList($user, $role);
   
        // Convert the branch IDs to strings
        $branchIds = array_map('strval', $branch);
    
        // Check if branchIds are valid
        if (!is_array($branchIds) || empty($branchIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }
    
        // Start building the query
        $builder = $this->db->table('nighttasks m');
        $builder->select('m.*, n.fname, n.lname, b.branch as branch_name, c.cluster as cluster_name, z.zone as zone_name')
            ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left') 
            ->join('branches b', 'm.branch = b.branch_id', 'left')
            ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('m.branch', $branchIds);
        }
    
        // Execute the query
        $query = $builder->get();
        $results = $query->getResultArray();
    
        // Log the query results for debugging
        //log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];

   }

   public function getCMUserBranchList($user, $role)
   {
        $b=$this->db->table('branchesmapped as b')
           ->select('b.cluster_id') 
           ->where('emp_code', $user)
           ->get();
           $b2 = $b->getRowArray(); 
           $cluster_id = $b2['cluster_id'];

       $builder = $this->db->table('cluster_branch_map as cl')
           ->select('cl.branch_id as branch_id,  ') // Select only branch_id
           ->join('branches as b', 'cl.branch_id = b.branch_id', 'left'); // Use 'inner' join if needed
       
       // Apply condition only if the role is not 'SUPER_ADMIN'
       if ($role != 'SUPER_ADMIN') {
          $builder->where('cl.cluster_id', $cluster_id);
       }
   
       // Execute the query and return branch_ids
       $query = $builder->get();
       $branchIds = array_column($query->getResultArray(), 'branch_id');
   
       // Log the fetched branch IDs
       //log_message('error', "Branch IDs fetched from getUserBranchList: " . print_r($branchIds, true));
   
       // If no branches are found, log it and return empty array
       if (empty($branchIds)) {
           //log_message('error', "No branches found for user {$user} with role {$role}");
       }
       
       return $branchIds; // Return only the branch_ids
   }
   
    
    public function getCmNightTaskList($role, $user)
    {
        // Fetch the user's branch IDs
        $cluster   = $this->getUserClusterList($user, $role);
    
        // Convert the branch IDs to strings
        $clusterIds = array_map('strval', $cluster);
    
        // Check if branchIds are valid
        if (!is_array($clusterIds) || empty($clusterIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }
    
        // Start building the query
        $builder = $this->db->table('cm_night_tasks m');
        $builder->select('m.*, n.fname, n.lname,   c.cluster as cluster_name, z.zone as zone_name')
            ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')             
            ->join('cluster_branch_map cb', 'm.cluster_id = cb.cluster_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->groupBy('m.cm_nid') // Group by mid
            ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('m.cluster_id', $clusterIds);
        }
    
        // Execute the query
        $query = $builder->get();
        $results = $query->getResultArray();
    
        // Log the query results for debugging
        //log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];
    }

    public function getUserClusterList($user, $role)
    {
        $builder = $this->db->table('branchesmapped as bm')
            ->select('bm.cluster_id as cluster_id ') // Select only branch_id
            ->join('cluster_branch_map as cl', 'cl.cluster_id = bm.cluster_id', 'left'); // Use 'inner' join if needed
        
        // Apply condition only if the role is not 'SUPER_ADMIN'
        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $user);
        }    
        // Execute the query and return branch_ids
        $query = $builder->get();
        $clusterIds = array_column($query->getResultArray(), 'cluster_id');
    
        // Log the fetched branch IDs
        //log_message('error', "Branch IDs fetched from getUserBranchList: " . print_r($clusterIds, true));    
        // If no branches are found, log it and return empty array
        if (empty($clusterIds)) {
            //log_message('error', "No branches found for user {$user} with role {$role}");
        }        
        return $clusterIds; // Return only the branch_ids
    }

    public function addCmNightTask($data, $createdDTM, $emp_code)  {
        // Check if the record already exists
        $existingRecord = $this->db->table('cm_night_tasks')
            ->where('createdDTM', $createdDTM)
            ->where('created_by', $emp_code)
            ->get()
            ->getRowArray();

        if ($existingRecord) {
            //log_message('error', 'Record already exists for createdDTM: ' . $createdDTM . ' and emp_code: ' . $emp_code);
            return $existingRecord['cm_nid']; // Return the existing record ID
        }

        $this->db->transStart();
        // Start the transaction

        // Insert the data into the 'cm_night_tasks' table
        $this->db->table('cm_night_tasks')->insert($data);

        // Check if the transaction completed successfully
        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            // Rollback in case of an error
            //log_message('error', 'Failed to insert data into nighttasks table: ' . $this->db->error()['message']);
            return [
                'status' => false,
                'message' => 'Failed to update Night Task'
            ];
        }

        $insertId = $this->db->insertID(); // Get the insert ID
        $this->db->transComplete();
        // Commit the transaction
        return $insertId; // Return the insert ID
    }

    public function getCm_nightTaskDetails($selectedCluster, $selectedDate, $role, $user) {
         // Initialize results as an empty array
         $results = [];

         if (empty($selectedBranch)) {
             //log_message('error', 'selectedBranch is empty');
         }
         if (empty($selectedDate)) {
             //log_message('error', 'selectedDate is empty');
         }

         // Ensure selectedDate is in 'Y-m-d' format
         $selectedDate = date('Y-m-d', strtotime($selectedDate));

         if (!empty($selectedCluster) && !empty($selectedDate)) {
             $this->select('m.*, n.fname, n.lname')
                  ->from('cm_night_tasks m')
                  ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                  ->where('m.cluster_id', $selectedCluster)
                  ->where("DATE_FORMAT(m.createdDTM, '%Y-%m-%d') =", $selectedDate)
                  ->orderBy('m.createdDTM', 'desc')
                  ->groupBy('m.cm_nid')
                  ->limit(1);

             // Retrieve the results
             $results = $this->get()->getResultArray();

             //log_message('error', 'Query executed with selectedBranch: ' . $selectedCluster . ', selectedDate: ' . $selectedDate);
             //log_message('error', 'Query result: ' . print_r($results, true));
         } else {
             //log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
         }

         return !empty($results) ? $results : [];
    }


    public function getCmNightTaskDetailsNew($cm_nid, $role, $user) {
        // Initialize results as an empty array
        $results = [];

        if (empty($cm_nid)) {
            //log_message('error', 'cm_nid is empty');
        }
        if (!empty($cm_nid)) {
            $this->select('m.*, n.fname, n.lname, c.cluster as cluster_name')
                 ->from('cm_night_tasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->join('clusters c', 'm.cluster_id = c.cluster_id', 'left')
                 ->where('m.cm_nid', $cm_nid)                 
                 ->orderBy('m.createdDTM', 'desc')
                 ->groupBy('m.cm_nid')
                 ->limit(1);

            // Retrieve the results
            $results = $this->get()->getResultArray();
 
            //log_message('error', 'Query result: ' . print_r($results, true));
        } else {
            //log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }

        return !empty($results) ? $results : [];
   }


    public function getNightTaskDetails($selectedBranch, $selectedDate, $role, $user) {
        // Initialize results as an empty array
        $results = [];

        if (empty($selectedBranch)) {
            //log_message('error', 'selectedBranch is empty');
        }
        if (empty($selectedDate)) {
            //log_message('error', 'selectedDate is empty');
        }

        // Ensure selectedDate is in 'Y-m-d' format
        $selectedDate = date('Y-m-d', strtotime($selectedDate));

        if (!empty($selectedBranch) && !empty($selectedDate)) {
            $this->select('m.*, n.fname, n.lname')
                 ->from('cm_night_tasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->where('m.branch', $selectedBranch)
                 ->where("DATE_FORMAT(m.createdDTM, '%Y-%m-%d') =", $selectedDate)
                 ->orderBy('m.createdDTM', 'desc')
                 ->groupBy('m.cm_nid')
                 ->limit(1);

            // Retrieve the results
            $results = $this->get()->getResultArray();

            //log_message('error', 'Query executed with selectedBranch: ' . $selectedBranch . ', selectedDate: ' . $selectedDate);
            //log_message('error', 'Query result: ' . print_r($results, true));
        } else {
            //log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }

        return !empty($results) ? $results : [];
    }

    public function uploadedNightTlist($selectedCluster, $selectedDate, $role, $user)
    {
        $results = [];

        // Debugging: Check if selectedBranch and selectedDate are provided
        if (empty($selectedCluster)) {
            //log_message('error', 'selectedBranch is empty');
        }
        if (empty($selectedDate)) {
            //log_message('error', 'selectedDate is empty');
        }

        // Check if both selectedBranch and selectedDate are provided
        if (!empty($selectedCluster) && !empty($selectedDate)) {
            // Extract the year and month from the selected date
            $year = date('Y', strtotime($selectedDate));
            $month = date('m', strtotime($selectedDate));

            // Start building the query
            $this->select('m.createdDTM, n.fname, n.lname')
                 ->from('cm_night_tasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->groupBy('m.cm_nid');  // Group by 'mid'

            // Apply filters based on provided parameters
            $this->where('m.cluster_id', $selectedCluster);
            $this->where('YEAR(m.createdDTM)', $year);   // Filter by year
            $this->where('MONTH(m.createdDTM)', $month); // Filter by month

            // Retrieve the results
            $results = $this->get()->getResultArray();

            // Debugging: Check the query and result
            //log_message('debug', 'Query executed with selectedBranch: ' . $selectedCluster . ', selectedDate: ' . $selectedDate);
            //log_message('debug', 'Query result: ' . print_r($results, true));
        } else {
            // Debugging: Log message if either value is missing
            //log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }

        // Return results only if filters yield data, otherwise return an empty array
        return !empty($results) ? $results : [];
    }


    public function editCM_NightTask($data, $nid) {
        if ($nid > 0) {
            try {
                // Log the data and nid before attempting the update
                //log_message('debug', 'Updating nighttasks table with data: ' . json_encode($data) . ' for nid: ' . $nid);

                // Check if the nid exists in the table and get the existing data
                $existingRecord = $this->db->table('cm_night_tasks')
                    ->where('cm_nid', $nid)
                    ->get()
                    ->getRowArray();

                if (!$existingRecord) {
                    //log_message('error', 'No record found in nighttasks table for nid: ' . $nid);
                    return false;
                }

                // Log the existing data
                //log_message('debug', 'Existing data for nid ' . $nid . ': ' . json_encode($existingRecord));

                // Compare existing data with new data
                $dataToUpdate = array_diff_assoc($data, $existingRecord);

                if (empty($dataToUpdate)) {
                    //log_message('error', 'No changes detected in the data for nid: ' . $nid);
                    return 'no_changes';
                }

                $this->db->table('cm_night_tasks')
                    ->set($dataToUpdate)
                    ->where('cm_nid', $nid)
                    ->update();

                // Confirm if any rows were affected by the update
                if ($this->db->affectedRows() > 0) {
                    return true; // Update successful
                } else {
                    //log_message('error', 'No rows affected in nighttasks table for nid: ' . $nid);
                    return false;
                }
            } catch (\Exception $e) {
                //log_message('error', 'Failed to update nighttasks table: ' . $e->getMessage());
                return false;
            }
        } else {
            //log_message('error', 'Invalid nid provided for updating nighttasks: ' . $nid);
        }

        return false;
    }

 }
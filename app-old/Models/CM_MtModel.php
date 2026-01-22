<?php

namespace App\Models;

use CodeIgniter\Model;

class CM_MtModel extends Model
 {

    protected $table = 'cm_morning_tasks';
    // Primary table
    protected $primaryKey = 'mid';
    protected $allowedFields = [ 'mt0100',
    'mt0101',
    'mt0102',
    'mt0200',
    'mt0201',
    'mt0202',
    'mt0300',
    'mt0301',
    'mt0302',

    'mt0400',
    'mt0401',
    'mt0402',

    'mt0500',
    'mt0501',
    'mt0502',

    'mt0600',
    'mt0601',
    'mt0602',

    'mt0700',
    'mt0701',
    'mt0702',

    'mt0800',
    'mt0801',
    'mt0802',

    'mt0900',
    'mt0901',
    'mt0902',

    'mt1000',
    'mt1001',
    'mt1002' ];

    public function getCM_BranchMorningTaskList($user, $role, $selectedMonth, $selectedBranch, $selectedCluster){
        
        if($selectedCluster === '0'){
            $branch = $this->getCMUserBranchList($user, $role);
        }else{
            $branch = $this->getClusterBranchList($selectedCluster);
        }        

       // ////log_message('error', "Branche selected: " . print_r($selectedBranch, true));

        if (empty($branch)) {
           // ////log_message('error', "No branches mapped for user {$user} with role {$role}.");
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }

        $branchIds = array_map('strval', $branch);
        // Start building the query
        $builder = $this->db->table('morningtasks m');
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
    
        // Log the query results for debugging
        //////log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];

   }

    public function getCm_Z_MorningTaskList($role, $user){
            
        // Fetch the user's branch IDs
        $cluster  = $this->getZonalManagerClusterList($user, $role);

        // Convert the branch IDs to strings
        $clusterIds = array_map('strval', $cluster);
    
        // Check if branchIds are valid
        if (!is_array($clusterIds) || empty($clusterIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }
    
        // Start building the query
        $builder = $this->db->table('cm_morning_tasks m');
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
        ////log_message('error', 'Query Results: ' . print_r($results, true));
    
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
            ////log_message('error', "No zone_id found for user {$user}");
            return [];
        }
        $clusterIds = $this->db->table('cluster_zone_map')
            ->select('cluster_id')
            ->whereIn('zone_id', $zone_ids)
            ->get()
            ->getResultArray();
        $clusterIds = array_column($clusterIds, 'cluster_id');     

        // Log the fetched branch IDs
        ////log_message('error', "Branch IDs fetched from getZonalManagerBranchList: " . print_r($clusterIds, true));

        // If no branches are found, log it and return empty array
        if (empty($clusterIds)) {
            ////log_message('error', "No branches found for user {$user} with role {$role}");
        }
        return $clusterIds;
    }

    public function getZ_BranchWeeklyList($user, $role, $selectedMonth, $selectedBranch, $selectedCluster){
        if($selectedCluster === '0'){
            $branch = $this->getCMUserBranchList($user, $role);
        }else{
            $branch = $this->getClusterBranchList($selectedCluster);
        }  
        
        if (empty($branch)) {
            ////log_message('error', "No branches mapped for user {$user} with role {$role}.");
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        } 

        $branchIds = array_map('strval', $branch);

        $builder = $this->db->table('bm_weekly_list m')
            ->select('
                m.*,                
                b.branch as branch_name,
                c.cluster as cluster_name,
                z.zone as zone_name,
                n.fname, n.lname,
            ')
             
            ->join('new_emp_master n', 'n.emp_code = m.createdBy', 'left')            
            ->join('branches b', 'm.branch_id = b.branch_id', 'left')
            ->join('cluster_branch_map cb', 'm.branch_id = cb.branch_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->where('DATE_FORMAT(m.createdDTM, "%Y-%m")', $selectedMonth)
            ->orderBy('m.createdDTM', 'desc');
            

        if ($role != 'SUPER_ADMIN') {
            if($selectedBranch ==='All'){
                $builder->whereIn('m.branch_id', $branchIds);
            }else{
                $builder->where('m.branch_id', $selectedBranch);
            }
            
        }

        $query = $builder->get();
        $results = $query->getResultArray();

        if (empty($results)) {
            ////log_message('error', "No tasks found for user {$user}.");
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }

        return ['status' => true, 'message' => 'Combo Task Details.', 'data' => $results];


    }
     public function getCMBranchComboTaskList($user, $role, $selectedMonth ,$selectedBranch, $selectedCluster)
    {
        if($selectedCluster === '0'){
            $branch = $this->getCMUserBranchList($user, $role);
        }else{
            $branch = $this->getClusterBranchList($selectedCluster);
        }        

        //////log_message('error', "Branche selected: " . print_r($selectedBranch, true));
        if (empty($branch)) {
            ////log_message('error', "No branches mapped for user {$user} with role {$role}.");
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        } 

        $branchIds = array_map('strval', $branch);

        $builder = $this->db->table('morningtasks m')
            ->select('
                m.*,
                m.taskDate as mtaskDate,
                nt.taskDate as ntaskDate,
                nt.*,
                n.fname, n.lname,
                n2.fname as nt_fname, n2.lname as nt_lname,
                b.branch as branch_name,
                c.cluster as cluster_name,
                z.zone as zone_name
            ')
            ->join('nighttasks nt', 'm.branch = nt.branch AND m.taskDate = nt.taskDate', 'left')
            ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
            ->join('new_emp_master n2', 'n2.emp_code = nt.created_by', 'left')
            ->join('branches b', 'm.branch = b.branch_id', 'left')
            ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth)
            ->orderBy('m.taskDate', 'desc');

        if ($role != 'SUPER_ADMIN') {
            if($selectedBranch ==='All'){
                $builder->whereIn('m.branch', $branchIds);
            }else{
                $builder->where('m.branch', $selectedBranch);
            }
            
        }

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
       

        ////log_message('error', "Task list: " . print_r($results, true));

        if (empty($results)) {
            ////log_message('error', "No tasks found for user {$user}.");
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }

        return ['status' => true, 'message' => 'Combo Task Details.', 'data' => $results];
    }

    public function getClusterBranchList($cluster_id)
    {
        $builder = $this->db->table('cluster_branch_map as cb')
            ->select('cb.branch_id')  ;
        $builder->where('cb.cluster_id', $cluster_id);
    
        $query = $builder->get();
        $result = $query->getResultArray();
    
        // Extract only the branch_id values
        $branchIds = array_column($result, 'branch_id');  
        log_message('error', "Branch IDs fetched from getClusterBranchList: " . print_r($branchIds, true));   
    
        return $branchIds;
    }

     
    

    public function getBm_Z_MorningTaskList($role, $user){
            
            // Fetch the user's branch IDs
            $branch  = $this->getZonalManagerBranchList($user, $role);
    
            // Convert the branch IDs to strings
            $branchIds = array_map('strval', $branch);
        
            // Check if branchIds are valid
            if (!is_array($branchIds) || empty($branchIds)) {
                return ['status' => false, 'message' => 'No branches mapped to the user.'];
            }
        
            // Start building the query
            $builder = $this->db->table('morningtasks m');
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
            ////log_message('error', 'Query Results: ' . print_r($results, true));
        
            // Return data if results are found
            if (empty($results)) {
                return ['status' => false, 'message' => 'No tasks found for the user.'];
            }
        
            return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];
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
            ////log_message('error', "No zone_id found for user {$user}");
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
        ////log_message('error', "Branch IDs fetched from getZonalManagerBranchList: " . print_r($branchIds, true));

        // If no branches are found, log it and return empty array
        if (empty($branchIds)) {
            ////log_message('error', "No branches found for user {$user} with role {$role}");
        }

        return $branchIds;
    }

    public function getCmMorningTaskList($role, $user)
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
        $builder = $this->db->table('cm_morning_tasks m');
        $builder->select('m.*, n.fname, n.lname,   c.cluster as cluster_name, z.zone as zone_name')
            ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')             
            ->join('cluster_branch_map cb', 'm.cluster_id = cb.cluster_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->groupBy('m.mid') // Group by mid
            ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('m.cluster_id', $clusterIds);
        }
    
        // Execute the query
        $query = $builder->get();
        $results = $query->getResultArray();
    
        // Log the query results for debugging
        ////log_message('error', 'Query Results: ' . print_r($results, true));
    
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
        ////log_message('error', "Branch IDs fetched from getUserBranchList: " . print_r($clusterIds, true));    
        // If no branches are found, log it and return empty array
        if (empty($clusterIds)) {
            ////log_message('error', "No branches found for user {$user} with role {$role}");
        }        
        return $clusterIds; // Return only the branch_ids
    }
    

    public function getCmMorningTaskDetails($selectedCluster, $selectedDate, $role, $user) {
        // Initialize results as an empty array
        $results = [];

        // Debugging: Check if selectedBranch and selectedDate are provided
        if (empty($selectedCluster)) {
            ////log_message('error', 'selectedCluster is empty');
        }
        if (empty($selectedDate)) {
            ////log_message('error', 'selectedDate is empty');
        }

        // Check if both selectedBranch and selectedDate are provided
        if (!empty($selectedCluster) && !empty($selectedDate)) {
            // Start building the query
            $this->select('m.*, n.fname, n.lname')
                 ->from('cm_morning_tasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->groupBy('m.mid')  // Group by 'mid'
                 ->limit(1);  // Apply limit to get only one row

            // Apply filters based on provided parameters
            $this->where('m.cluster_id', $selectedCluster);
            $this->where('DATE(m.createdDTM)', $selectedDate);  // Filter by date

            // Retrieve the results
            $results = $this->get()->getResultArray();

            // Debugging: Check the query and result
            ////log_message('debug', 'Query executed with selectedBranch: ' . $selectedCluster . ', selectedDate: ' . $selectedDate);
            ////log_message('debug', 'Query result: ' . print_r($results, true));
        } else {
            // Debugging: Log message if either value is missing
            ////log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }

        // Return results only if filters yield data, otherwise return an empty array
        return !empty($results) ? $results : [];
    }

    public function getBmcWeeklyTaskList($role, $user, $selectedMonth, $selectedBranch){

        // Fetch the user's branch IDs
        $branch  = $this->getCMUserBranchList($user, $role);   
        // Convert the branch IDs to strings
        $branchIds = array_map('strval', $branch);    
        // Check if branchIds are valid
        if (!is_array($branchIds) || empty($branchIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }    
        // Start building the query
        $builder = $this->db->table('bm_weekly_list m');
        $builder->select('m.*, n.fname, n.lname, b.branch as branch_name, c.cluster as cluster_name, z.zone as zone_name')
            ->join('new_emp_master n', 'n.emp_code = m.createdBy', 'left') 
            ->join('branches b', 'm.branch_id = b.branch_id', 'left')
            ->join('cluster_branch_map cb', 'm.branch_id = cb.branch_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')           
            ->where('DATE_FORMAT(m.createdDTM, "%Y-%m")', $selectedMonth)
            ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            if($selectedBranch ==='All'){
                $builder->whereIn('m.branch_id', $branchIds);
            }else{
                $builder->where('m.branch_id', $selectedBranch);
            }            
        }
    
        // Execute the query 
        $query = $builder->get();
        $results = $query->getResultArray();
    
        // Log the query results for debugging
        ////log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];

   }


    public function getBmcMorningTaskList($role, $user){

         // Fetch the user's branch IDs
         $branch  = $this->getCMUserBranchList($user, $role);
    
         // Convert the branch IDs to strings
         $branchIds = array_map('strval', $branch);
     
         // Check if branchIds are valid
         if (!is_array($branchIds) || empty($branchIds)) {
             return ['status' => false, 'message' => 'No branches mapped to the user.'];
         }
     
         // Start building the query
         $builder = $this->db->table('morningtasks m');
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
         ////log_message('error', 'Query Results: ' . print_r($results, true));
     
         // Return data if results are found
         if (empty($results)) {
             return ['status' => false, 'message' => 'No tasks found for the user.'];
         }
     
         return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];

    }

    // public function getCMUserBranchList($user, $role)
    // {
    //      $b=$this->db->table('branchesmapped as b')
    //         ->select('b.cluster_id,') 
    //         ->where('emp_code', $user)
    //         ->get();
    //         $b2 = $b->getRowArray(); 
    //         $cluster_id = $b2['cluster_id'];

    //     $builder = $this->db->table('cluster_branch_map as cl')
    //         ->select('cl.branch_id as branch_id, b.branch ') // Select only branch_id
    //         ->join('branches as b', 'cl.branch_id = b.branch_id', 'left')// Use 'inner' join if needed
    //         ->orderBy('b.branch_id', 'asc'); // Order by branch name in ascending order
        
    //     // Apply condition only if the role is not 'SUPER_ADMIN'
    //     if ($role != 'SUPER_ADMIN') {
    //        $builder->where('cl.cluster_id', $cluster_id);
    //     }
    
    //     // Execute the query and return branch_ids
    //     $query = $builder->get();
    //     $branchIds = array_column($query->getResultArray(), 'branch_id');
     
        
    //     // If no branches are found, log it and return empty array
    //     if (empty($branchIds)) {
    //         ////log_message('error', "No branches found for user {$user} with role {$role}");
    //     }
        
    //     return $branchIds; // Return only the branch_ids
    // }
    
    public function getCMUserBranchList($user, $role)
    {
        ////log_message('debug', "Fetching cluster_id for emp_code: {$user}");
    
        $b = $this->db->table('branchesmapped as b')
            ->select('b.cluster_id')
            ->where('emp_code', $user)
            ->get();
    
        $b2 = $b->getRowArray();
        ////log_message('debug', "Cluster result: " . print_r($b2, true));
    
        if (!$b2 || !isset($b2['cluster_id'])) {
            ////log_message('error', "No cluster_id found for emp_code: {$user}");
            return [];
        }
    
        $cluster_id = $b2['cluster_id'];
    
        $builder = $this->db->table('cluster_branch_map as cl')
            ->select('cl.branch_id as branch_id, b.branch')
            ->join('branches as b', 'cl.branch_id = b.branch_id', 'left')
            ->orderBy('b.branch_id', 'asc');
    
        if ($role != 'SUPER_ADMIN') {
            $builder->where('cl.cluster_id', $cluster_id);
        }
    
        $query = $builder->get();
        $branchIds = array_column($query->getResultArray(), 'branch_id');
    
        ////log_message('debug', "Branch IDs: " . print_r($branchIds, true));
    
        if (empty($branchIds)) {
            ////log_message('error', "No branches found for user {$user} with role {$role}");
            return [];
        }
    
        return $branchIds;
    }
    

    public function getCMUserBranchListDetails($user, $role)
    {
         $b=$this->db->table('branchesmapped as b')
            ->select('b.cluster_id,') 
            ->where('emp_code', $user)
            ->get();
            $b2 = $b->getRowArray(); 
            $cluster_id = $b2['cluster_id'];

        $builder = $this->db->table('cluster_branch_map as cl')
            ->select('cl.branch_id as branch_id, b.branch ') // Select only branch_id
            ->join('branches as b', 'cl.branch_id = b.branch_id', 'left')// Use 'inner' join if needed
            ->orderBy('b.branch_id', 'asc'); // Order by branch name in ascending order
        
        // Apply condition only if the role is not 'SUPER_ADMIN'
        if ($role != 'SUPER_ADMIN') {
           $builder->where('cl.cluster_id', $cluster_id);
        }
    
        // Execute the query and return branch_ids
        $query = $builder->get();
        $result = $query->getResultArray();
      
        
        return $result; // Return only the branch_ids
    }

    public function getCmMorningTaskDetailsNew($mid, $role, $user) {
        // Initialize results as an empty array
        $results = [];

        

        // Check if both selectedBranch and selectedDate are provided
        if (!empty($mid)) {
            // Start building the query
            $this->select('m.*, n.fname, n.lname, c.cluster as cluster_name')
                 ->from('cm_morning_tasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->join('clusters c', 'c.cluster_id = m.cluster_id', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->groupBy('m.mid')  // Group by 'mid'
                 ->limit(1);  // Apply limit to get only one row

            // Apply filters based on provided parameters
            $this->where('m.mid', $mid);
          
            // Retrieve the results
            $results = $this->get()->getResultArray();
 
            ////log_message('debug', 'Query result: ' . print_r($results, true));
        } else {
            // Debugging: Log message if either value is missing
            ////log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }

        // Return results only if filters yield data, otherwise return an empty array
        return !empty($results) ? $results : [];
    }

 

    public function uploadedCmMTtask($selectedCluster, $selectedDate, $role, $user)
    {
        $results = [];

        // Debugging: Check if selectedBranch and selectedDate are provided
        if (empty($selectedCluster)) {
            ////log_message('error', 'selectedCluster is empty');
        }
        if (empty($selectedDate)) {
            ////log_message('error', 'selectedDate is empty');
        }

        // Check if both selectedBranch and selectedDate are provided
        if (!empty($selectedCluster) && !empty($selectedDate)) {
            // Extract the year and month from the selected date
            $year = date('Y', strtotime($selectedDate));
            $month = date('m', strtotime($selectedDate));

            // Start building the query
            $this->select('m.*, n.fname, n.lname')
                 ->from('cm_morning_tasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->groupBy('m.mid');  // Group by 'mid'

            // Apply filters based on provided parameters
            $this->where('m.cluster_id', $selectedCluster);
            $this->where('YEAR(m.createdDTM)', $year);   // Filter by year
            $this->where('MONTH(m.createdDTM)', $month); // Filter by month

            // Retrieve the results
            $results = $this->get()->getResultArray();

            // Debugging: Check the query and result
            ////log_message('debug', 'Query executed with selectedBranch: ' . $selectedCluster . ', selectedDate: ' . $selectedDate);
            ////log_message('debug', 'Query result: ' . print_r($results, true));
        } else {
            // Debugging: Log message if either value is missing
            ////log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }

        // Return results only if filters yield data, otherwise return an empty array
        return !empty($results) ? $results : [];
    }

    public function addCmMorningTask($data, $createdDTM, $emp_code) {
        // Check if a record with the same createdDTM and created_by already exists
        $existingRecord = $this->db->table('cm_morning_tasks')
            ->where('createdDTM', $createdDTM)
            ->where('created_by', $emp_code)
            ->get()
            ->getRowArray();

        if ($existingRecord) {
            ////log_message('error', 'Record with the same createdDTM and created_by already exists');
            return $existingRecord['mid']; // Return the existing record ID
        }

        $this->db->transStart();
        // Start the transaction

        // Insert the data into the 'cm_morning_tasks' table
        $this->db->table('cm_morning_tasks')->insert($data);

        // Check if the transaction completed successfully
        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            // Rollback in case of an error
            ////log_message('error', 'Failed to insert data into cm_morning_tasks table: ' . $this->db->error()['message']);
            return false;
        }

        $insertId = $this->db->insertID(); // Get the insert ID
        $this->db->transComplete();
        // Commit the transaction
        return $insertId; // Return the insert ID
    }

    public function editMoringTask($data, $mid) {

            if ($mid > 0) {
                try {
                    // Log the data and nid before attempting the update
                    ////log_message('debug', 'Updating nighttasks table with data: ' . json_encode($data) . ' for nid: ' . $mid);

                    // Check if the nid exists in the table and get the existing data
                    $existingRecord = $this->db->table('cm_morning_tasks')
                        ->where('mid', $mid)
                        ->get()
                        ->getRowArray();

                    if (!$existingRecord) {
                        ////log_message('error', 'No record found in nighttasks table for nid: ' . $mid);
                        return false;
                    }

                    // Log the existing data
                    ////log_message('debug', 'Existing data for nid ' . $mid . ': ' . json_encode($existingRecord));

                    // Compare existing data with new data
                    $dataToUpdate = array_diff_assoc($data, $existingRecord);

                    if (empty($dataToUpdate)) {
                        ////log_message('error', 'No changes detected in the data for nid: ' . $mid);
                        return 'no_changes';
                    }

                    $this->db->table('cm_morning_tasks')
                        ->set($dataToUpdate)
                        ->where('mid', $mid)
                        ->update();

                    // Confirm if any rows were affected by the update
                    if ($this->db->affectedRows() > 0) {
                        return true; // Update successful
                    } else {
                        ////log_message('error', 'No rows affected in nighttasks table for nid: ' . $mid);
                        return false;
                    }
                } catch (\Exception $e) {
                    ////log_message('error', 'Failed to update nighttasks table: ' . $e->getMessage());
                    return false;
                }
            } else {
                ////log_message('error', 'Invalid nid provided for updating nighttasks: ' . $mid);
            }

            return false;
        }

    //     public function getAssetDetails()
    // {
    //         return $this->select( 'a.*, b.branch as bname, c.name as asst_name1, d.number as num, d.name as supplier_name1' )
    //         ->from( 'asset a' ) // Alias for asset table
    //         ->join( 'bi_centres b', 'a.branch = b.id', 'inner' ) // Join bi_centres table
    //         ->join( 'asset_name c', 'a.asset_name = c.id', 'inner' ) // Join asset_name table
    //         ->join( 'service_manager d', 'a.supplier_name = d.id', 'inner' ) // Join service_manager table
    //         ->groupBy( 'a.id' ) // Order by descending asset id
    //         ->orderBy( 'a.id', 'desc' ) // Order by descending asset id
    //         ->findAll();
    //         // Retrieve all results
    //     }

}
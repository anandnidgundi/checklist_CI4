<?php

namespace App\Models;

use CodeIgniter\Model;

class NightModel extends Model
 {
    protected $table = 'nighttasks';
    // Primary table
    protected $primaryKey = 'nid';
    protected $allowedFields = [
            'nt0100' ,
        'nt0200' ,
        'nt0201' ,
        'nt0300' ,
        'nt0301' ,
        'nt0400' ,
        'nt0401' ,
        'nt0500' ,
        'nt0501' ,
        'nt0600' ,
        'nt0601' ,
        'nt0700' ,
        'nt0701' ,
        'nt0800' ,
        'nt0801' ,
        'nt0900' ,
        'nt0901' ,
        'nt1000' ,
        'nt1001' ,
        'nt1100' ,
        'nt1101' ,
        'nt1200' ,
        'nt1201' ,
        'nt1300' ,
        'nt1301' ,
        'nt1400' ,
        'nt1401' ,
        'nt1500' ,
        'nt1501' ,
        'nt1600' ,
        'nt1601' ,
        'nt1700' ,
        'nt1701' ,
        'nt1800' ,
        'nt1801' ,
        'nt1900' ,
        'nt1901' ,
        'nt2000' ,
        'nt2001' ,
        'nt2100' ,
        'nt2101' ,
        'nt2200' ,
        'nt2201' ,
        'nt2300' ,
        'nt2301' ,
        'nt2400' ,
        'nt2401' ,
        'nt2500' ,
        'nt2501' ,
        'nt2600' ,
        'nt2601' ,
        'nt2700' ,
        'nt2701' ,
        'nt2800' ,
        'nt2801' ,
        'nt2900' ,
        'nt2901' ,
        'nt3000' ,
        'nt3001' ,
        'nt3100' ,
        'nt3101' ,
        'nt3200' ,
        'nt3201' ,

        'nt3300' ,
        'nt3301' ,

        'nt3400' ,
        'nt3401' ,

        'nt3500' ,
        'nt3501' ,

        'nt3600' ,
        'nt3601' ,

        'nt3700' ,
        'nt3701' ,

        'nt3800' ,
        'nt3801' ,

        'nt3900' ,
        'nt3901' ,

        'nt4000' ,
        'nt4001' ,

        'nt4100' ,
        'nt4101' ,

        'emp_code'  ,
        'branch' ,
        'createdDTM' ,
        'created_by' ,

    ];

    public function checkExistingRecord($taskDate, $branch) {
        // Check if an entry with the same taskDate and branch already exists
        $existingEntry = $this->db->table('nighttasks')
                                  ->where('DATE(createdDTM)', $taskDate)
                                  ->where('branch', $branch)
                                  ->get()
                                  ->getRowArray();

        return $existingEntry ? $existingEntry['nid'] : null;
    }

    public function getBranchNightTaskList($role, $user, $selectedMonth)
    {
        // Fetch the user's branch IDs
        $branch  = $this->getUserBranchList($user, $role);

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
            ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth) // Filter by selected month
            ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('m.branch', $branchIds);
        }
    
        // Execute the query
        $query = $builder->get();
        $results = $query->getResultArray();

        if (!empty($results)) {
            // Fetch the MRI details for each night task
            foreach ($results as $key => $value) {
                $b = $this->db->table('doc_count as r');
                $b->select('r.*'); 
                $b->where('r.nid', $value['nid']);
                $query = $b->get();
                $results[$key]['doc_data'] = $query->getResultArray();
            }   
            
        }        
     
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Night Task Details.', 'data' => $results];
    }


    // public function getBranchNightTaskList($role, $user, $selectedMonth)
    // {
    //     // Fetch the user's branch IDs
    //     $branch  = $this->getUserBranchList($user, $role);

    //     // Convert the branch IDs to strings
    //     $branchIds = array_map('strval', $branch);
    
    //     // Check if branchIds are valid
    //     if (!is_array($branchIds) || empty($branchIds)) {
    //         return ['status' => false, 'message' => 'No branches mapped to the user.'];
    //     }
    
    //     // Start building the query
    //     $builder = $this->db->table('nighttasks m');
    //     $builder->select('m.*, n.fname, n.lname, b.branch as branch_name, c.cluster as cluster_name, z.zone as zone_name')
    //         ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left') 
    //         ->join('branches b', 'm.branch = b.branch_id', 'left')
    //         ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
    //         ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
    //         ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
    //         ->join('zones z', 'z.z_id = cz.zone_id', 'left')
    //         ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth) // Filter by selected month
    //         ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
    //     // Add branch filter conditionally based on role
    //     if ($role != 'SUPER_ADMIN') {
    //         $builder->whereIn('m.branch', $branchIds);
    //     }
    
    //     // Execute the query
    //     $query = $builder->get();
    //     $results = $query->getResultArray();

    //     if (!empty($results)) {
    //         // Fetch the MRI details for each night task
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('mri as r');
    //             $b->select('r.*'); 
    //             $b->where('r.nid', $value['nid']);
    //             $query = $b->get();
    //             $results[$key]['mri'] = $query->getResultArray();
    //         }   
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('xray as x');
    //             $b->select('x.*'); 
    //             $b->where('x.nid', $value['nid']);
    //             $query = $b->get();
    //             $results[$key]['xray'] = $query->getResultArray();
    //         }    
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('ct as c');
    //             $b->select('c.*'); 
    //             $b->where('c.nid', $value['nid']);
    //             $query = $b->get();
    //             $results[$key]['ct'] = $query->getResultArray();
    //         }    
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('usg as u');
    //             $b->select('u.*'); 
    //             $b->where('u.nid', $value['nid']);
    //             $query = $b->get();
    //             $results[$key]['usg'] = $query->getResultArray();
    //         }    
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('usg as u');
    //             $b->select('u.*'); 
    //             $b->where('u.nid', $value['nid']);
    //             $query = $b->get();
    //             $results[$key]['usg'] = $query->getResultArray();
    //         }         
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('cardiologist_ecg as u');
    //             $b->select('u.*'); 
    //             $b->where('u.nid', $value['nid']);
    //             $query = $b->get();
    //             $results[$key]['cardiologist_ecg'] = $query->getResultArray();
    //         }
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('cardiologist_tmt as u');
    //             $b->select('u.*'); 
    //             $b->where('u.nid', $value['nid']);
    //             $query = $b->get();
    //             $results[$key]['cardiologist_tmt'] = $query->getResultArray();
    //         }  
    //     }        
     
    //     if (empty($results)) {
    //         return ['status' => false, 'message' => 'No tasks found for the user.'];
    //     }
    
    //     return ['status' => true, 'message' => 'Night Task Details.', 'data' => $results];
    // }

 
    // public function getBranchNightTaskList($role, $user, $selectedMonth)
    // {
    //     // Fetch the user's branch IDs
    //     $branch  = $this->getUserBranchList($user, $role);
    
    //     // Convert the branch IDs to strings
    //     $branchIds = array_map('strval', $branch);
    
    //     // Check if branchIds are valid
    //     if (!is_array($branchIds) || empty($branchIds)) {
    //         return ['status' => false, 'message' => 'No branches mapped to the user.'];
    //     }
    
    //     // Start building the query
    //     $builder = $this->db->table('nighttasks m');
    //     $builder->select('m.*, n.fname, n.lname, b.branch as branch_name, c.cluster as cluster_name, z.zone as zone_name')
    //         ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left') 
    //         ->join('branches b', 'm.branch = b.branch_id', 'left')
    //         ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
    //         ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
    //         ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
    //         ->join('zones z', 'z.z_id = cz.zone_id', 'left')
    //         ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth) // Filter by selected month
    //         ->orderBy('m.createdDTM', 'desc'); // Order by createdDTM in descending order
            
    //     // Add branch filter conditionally based on role
    //     if ($role != 'SUPER_ADMIN') {
    //         $builder->whereIn('m.branch', $branchIds);
    //     }
    
    //     // Execute the query
    //     $query = $builder->get();
    //     $results = $query->getResultArray();
     
    //     if (empty($results)) {
    //         return ['status' => false, 'message' => 'No tasks found for the user.'];
    //     }
    
    //     return ['status' => true, 'message' => 'Night Task Details.', 'data' => $results];
    // }

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
        //log_message('error', "Branch IDs fetched from getUserBranchList: " . print_r($branchIds, true));
    
        // If no branches are found, log it and return empty array
        if (empty($branchIds)) {
            //log_message('error', "No branches found for user {$user} with role {$role}");
        }
        
        return $branchIds; // Return only the branch_ids
    }
    


    public function addNightTask($data, $taskDate, $branch) {
        // Check if a record with the same createdDTM and created_by already exists
        $existingRecord = $this->db->table('nighttasks')
                                   ->where('taskDate', $taskDate)
                                   ->where('branch', $branch)
                                   ->get()
                                   ->getRowArray();
    
        if ($existingRecord) {
            //log_message('error', 'Record already exists with taskdate: ' . $taskDate . ' and branch: ' . $branch);
            return $existingRecord['nid']; // Use array key since getRowArray() returns an array
        }    
        $this->db->transStart();
        // Start the transaction

        // Insert the data into the 'nighttasks' table
        $this->db->table('nighttasks')->insert($data);

        // Check if the transaction completed successfully
        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            // Rollback in case of an error
            //log_message('error', 'Failed to insert data into nighttasks table: ' . $this->db->error()['message']);
            return false;
        }

        // Get the ID of the newly inserted row
        $insertedID = $this->db->insertID();

        // Commit the transaction
        $this->db->transComplete();

        // Return the ID of the newly inserted row
        return $insertedID;
    }
    

    public function editNightTask($data, $nid) {
        if ($nid > 0) {
            try {
                $this->db->table('nighttasks')
                    ->set($data)
                    ->where('nid', $nid)
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
                 ->from('nighttasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->where('m.branch', $selectedBranch)
                 ->where("DATE_FORMAT(m.createdDTM, '%Y-%m-%d') =", $selectedDate)
                 ->orderBy('m.createdDTM', 'desc')
                 ->groupBy('m.nid')
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

    public function getNightTaskDetailsNew($nid, $role, $user){
        $results = [];
 
        if (!empty($nid) ) {
            $this->select('m.*, n.fname, n.lname, b.branch as branch_name')
                 ->from('nighttasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->join('branches b', 'b.branch_id = m.branch', 'left')
                 ->where('m.nid', $nid)                  
                 ->orderBy('m.createdDTM', 'desc')
                 ->groupBy('m.nid')
                 ->limit(1);
            // Retrieve the results
            $results = $this->get()->getResultArray();
 
            //log_message('error', 'Query result: ' . print_r($results, true));
        } else {
            //log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }
        return !empty($results) ? $results : [];
    }

    public function uploadedNightTlist($selectedBranch, $selectedDate, $role, $user)
    {
        $results = [];

        // Debugging: Check if selectedBranch and selectedDate are provided
        if (empty($selectedBranch)) {
            //log_message('error', 'selectedBranch is empty');
        }
        if (empty($selectedDate)) {
            //log_message('error', 'selectedDate is empty');
        }

        // Check if both selectedBranch and selectedDate are provided
        if (!empty($selectedBranch) && !empty($selectedDate)) {
            // Extract the year and month from the selected date
            $year = date('Y', strtotime($selectedDate));
            $month = date('m', strtotime($selectedDate));

            // Start building the query
            $this->select('m.createdDTM, n.fname, n.lname')
                 ->from('nighttasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->groupBy('m.nid');  // Group by 'mid'

            // Apply filters based on provided parameters
            $this->where('m.branch', $selectedBranch);
            $this->where('YEAR(m.createdDTM)', $year);   // Filter by year
            $this->where('MONTH(m.createdDTM)', $month); // Filter by month

            // Retrieve the results
            $results = $this->get()->getResultArray();

            // Debugging: Check the query and result
            //log_message('debug', 'Query executed with selectedBranch: ' . $selectedBranch . ', selectedDate: ' . $selectedDate);
            //log_message('debug', 'Query result: ' . print_r($results, true));
        } else {
            // Debugging: Log message if either value is missing
            //log_message('error', 'Either selectedBranch or selectedDate is missing, skipping the query');
        }

        // Return results only if filters yield data, otherwise return an empty array
        return !empty($results) ? $results : [];
    }


 }
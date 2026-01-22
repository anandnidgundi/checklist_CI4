<?php

namespace App\Models;

use CodeIgniter\Model;

class MtModel extends Model
 {

    protected $table = 'morningtasks';
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

    public function getMorningTaskDetails($selectedBranch, $selectedDate, $role, $user) {
        // Initialize results as an empty array
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
            // Start building the query
            $this->select('m.*, n.fname, n.lname')
                 ->from('morningtasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->groupBy('m.mid')  // Group by 'mid'
                 ->limit(1);  // Apply limit to get only one row

            // Apply filters based on provided parameters
            $this->where('m.branch', $selectedBranch);
            $this->where('DATE(m.createdDTM)', $selectedDate);  // Filter by date

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

    public function addSubquestions($subData) {
        return $this->db->table('subquestions')->insertBatch($subData);
    }
    
    public function getMorningTaskDetailsByMid($midSelected, $role, $user) {
        $results = [];

        // Debugging: Check if midSelected is provided
        if (empty($midSelected)) {
            //log_message('error', 'midSelected is empty');
        }

        // Check if midSelected is provided 
        if (!empty($midSelected)) {
            // Start building the query
            $this->select('m.*, n.fname, n.lname, b.branch as branch_name')
                 ->from('morningtasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->join('branches b', 'b.branch_id = m.branch', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->limit(1);  // Apply limit to get only one row

            // Apply filters based on provided parameters
            $this->where('m.mid', $midSelected);

            // Retrieve the results
            $taskResults = $this->get()->getResultArray();

            foreach ($taskResults as &$ts) {
                $mid = $ts['mid'];
                $subquestions = $this->db->table('subquestions')
                                         ->select('*')
                                         ->where('task_id', $mid)                                          
                                         ->get()
                                         ->getResultArray();
                $ts['subquestions'] = $subquestions;
                foreach ($subquestions as &$sq) {
                    $sq_id = $sq['sq_id'];
                    $subanswers = $this->db->table('subquestions')
                                           ->select('sq_id, squestion, sqvalue')
                                           ->where('sq_id', $sq_id)
                                           ->where('task_id', $mid)
                                           ->get()
                                           ->getResultArray();
                    $sq['subs'] = $subanswers;
                }
            }

            //log_message('error', 'Query result: ' . print_r($results, true));
        } else {
            // Debugging: Log message if midSelected is missing
            //log_message('error', 'midSelected is missing, skipping the query');
        }

        // Return results only if filters yield data, otherwise return an empty array
        return !empty($taskResults) ? $taskResults : [];
    }

    // public function getMorningTaskDetailsByMid($midSelected, $role, $user) {
    //     $results = [];

    //     // Debugging: Check if midSelected is provided
    //     if (empty($midSelected)) {
    //         //log_message('error', 'midSelected is empty');
    //     }

    //     // Check if midSelected is provided
    //     if (!empty($midSelected)) {
    //         // Start building the query
    //         $this->select('m.*, n.fname, n.lname, b.branch as branch_name')
    //              ->from('morningtasks m')
    //              ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
    //              ->join('branches b', 'b.branch_id = m.branch', 'left')
    //              ->orderBy('m.createdDTM', 'desc')  // Sort by created date
    //              ->groupBy('m.mid')  // Group by 'mid'
    //              ->limit(1);  // Apply limit to get only one row

    //         // Apply filters based on provided parameters
    //         $this->where('m.mid', $midSelected);

    //         // Retrieve the results
    //         $taskResults = $this->get()->getResultArray();

    //         foreach ($taskResults as &$ts) {
    //             $mid = $ts['mid'];
    //             $subquestions = $this->db->table('subquestions')
    //                                      ->select('*')
    //                                      ->where('task_id', $mid)                                          
    //                                      ->get()
    //                                      ->getResultArray();
    //             $ts['subquestions'] = $subquestions;
    //             foreach ($subquestions as &$sq) {
    //                 $sq_id = $sq['sq_id'];
    //                 $subanswers = $this->db->table('subquestions') // Change table name to 'subanswers'
    //                                        ->select('sq_id, squestion, sqvalue')
    //                                        ->where('sq_id', $sq_id)
    //                                        ->where('task_id', $mid)
    //                                        ->get()
    //                                        ->getResultArray();
    //                 $sq['subs'] = $subanswers;
    //             }
    //         }

    //         //log_message('error', 'Query result: ' . print_r($results, true));
    //     } else {
    //         // Debugging: Log message if midSelected is missing
    //         //log_message('error', 'midSelected is missing, skipping the query');
    //     }

    //     // Return results only if filters yield data, otherwise return an empty array
    //     return !empty($taskResults) ? $taskResults : [];
    // }

    public function updateSubquestions(array $subQuestions) {
        foreach ($subQuestions as $subQuestion) {
            $this->db->table('subquestions')
                     ->where('id', $subQuestion['id'])
                     ->update([
                         'sqvalue' => $subQuestion['sqvalue'],
                         'updatedDTM' => $subQuestion['updatedDTM'],
                         'updatedBy' => $subQuestion['updatedBy']
                     ]);
        }
        return true;
    }
    

    public function checkExistingRecord($taskDate, $branch) {
        // Check if an entry with the same taskDate and branch already exists
        $existingEntry = $this->db->table('morningtasks')
                                  ->where('DATE(createdDTM)', $taskDate)
                                  ->where('branch', $branch)
                                  ->get()
                                  ->getRowArray();

        return $existingEntry ? $existingEntry['mid'] : null;
    }

    // public function getBranchComboTaskList($role, $user, $selectedMonth) {
    //     // Fetch the user's branch IDs
    //     $branch = $this->getUserBranchList($user, $role);

    //     // Convert the branch IDs to strings
    //     $branchIds = array_map('strval', $branch);

    //     // Check if branchIds are valid
    //     if (!is_array($branchIds) || empty($branchIds)) {
    //         return ['status' => false, 'message' => 'No branches mapped to the user.'];
    //     }

         

    //     $builder = $this->db->table('morningtasks m');
    //     $builder->select('
    //         m.*,
    //          m.taskDate  as mtaskDate,
    //          nt.taskDate as ntaskDate,
    //         nt.*, 
            
    //         n.fname, n.lname, 
    //         n2.fname as nt_fname, n2.lname as nt_lname, 
    //         b.branch as branch_name, 
    //         c.cluster as cluster_name, 
    //         z.zone as zone_name
    //     ')
    //     ->join('nighttasks nt', 'm.branch = nt.branch AND m.taskDate = nt.taskDate', 'left')
    //     ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
    //     ->join('new_emp_master n2', 'n2.emp_code = nt.created_by', 'left')
    //     ->join('branches b', 'm.branch = b.branch_id', 'left')
    //     ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
    //     ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
    //     ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
    //     ->join('zones z', 'z.z_id = cz.zone_id', 'left')
    //     ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth)
    //     ->orderBy('m.taskDate', 'desc')
    //     ->groupBy('m.taskDate, nt.taskDate');

    //     // Add branch filter conditionally based on role
    //     if ($role != 'SUPER_ADMIN') {
    //         $builder->whereIn('m.branch', $branchIds);
    //     }

    //     // Execute the query
    //     $query = $builder->get();
    //     $results = $query->getResultArray();


        
    //     // Log the query results for debugging
    //   log_message('error', 'selectedMonth: ' . print_r($selectedMonth, true));
    // //   log_message('error', 'Combo Task Details: ' . print_r($results, true));   

    //     // Return data if results are found
    //     if (empty($results)) {
    //         return ['status' => false, 'message' => 'No tasks found for the user.'];
    //     }

    //     return ['status' => true, 'message' => 'Combo Task Details.', 'data' => $results];
    // }
     


    public function getBranchComboTaskList($role, $user, $selectedMonth) {
        // Fetch the user's branch IDs
        $branch = $this->getUserBranchList($user, $role);

        // Convert the branch IDs to strings
        $branchIds = array_map('strval', $branch);

        // Check if branchIds are valid
        if (!is_array($branchIds) || empty($branchIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }

        $builder = $this->db->table('morningtasks m');
        $builder->select('
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

        // Add branch filter conditionally based on role
        if ($role != 'SUPER_ADMIN') {
            $builder->whereIn('m.branch', $branchIds);
        }

        // Execute the query
        $query = $builder->get();
        $results = $query->getResultArray();
        
        // Log the query results for debugging
        log_message('error', 'selectedMonth: ' . print_r($selectedMonth, true));

        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }

        return ['status' => true, 'message' => 'Combo Task Details.', 'data' => $results];
    }

    public function getBranchMorningTaskList($role, $user, $selectedMonth)
    {
        // Fetch the user's branch IDs
        $branch  = $this->getUserBranchList($user, $role);
     
        // Convert the branch IDs to strings
        $branchIds = array_map('strval', $branch);
    
        // Check if branchIds are valid
        if (!is_array($branchIds) || empty($branchIds)) {
            return ['status' => false, 'message' => 'No branches mapped to the user.'];
        }
       // //log_message('error', 'Query Results: ' . print_r($selectedMonth, true));
        // Start building the query
        $builder = $this->db->table('morningtasks m');
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
    
        // Log the query results for debugging
        //log_message('error', 'Query Results: ' . print_r($results, true));
    
        // Return data if results are found
        if (empty($results)) {
            return ['status' => false, 'message' => 'No tasks found for the user.'];
        }
    
        return ['status' => true, 'message' => 'Morning Task Details.', 'data' => $results];
    }
    
    
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
    
    
    

    public function uploadedMTlist($selectedBranch, $selectedDate, $role, $user)
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
            $this->select('m.*, n.fname, n.lname')
                 ->from('morningtasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->orderBy('m.createdDTM', 'desc')  // Sort by created date
                 ->groupBy('m.mid');  // Group by 'mid'

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

    public function addMorningTask($data, $taskDate, $branch) {
        // Check if an entry with the same createdDTM and emp_code already exists
        $existingEntry = $this->db->table('morningtasks')
                                  ->where('taskDate', $taskDate)
                                  ->where('branch', $branch)
                                  ->get()
                                  ->getRowArray();

        if ($existingEntry) {
            // If an entry exists, return the existing entry's ID
            //log_message('error', 'Entry with taskdate: ' . $taskDate . ' and emp_code: ' . $branch . ' already exists.');
            return $existingEntry['mid'];
        }

        $this->db->transStart();
        // Start the transaction

        // Insert the data into the 'morningtasks' table
        $this->db->table('morningtasks')->insert($data);

        // Check if the transaction completed successfully
        if ($this->db->transStatus() === FALSE) {
            $this->db->transRollback();
            // Rollback in case of an error
            //log_message('error', 'Failed to insert data into morningtasks table: ' . $this->db->error()['message']);
            return false;
        }

        // Get the ID of the newly inserted row
        $insertedID = $this->db->insertID();

        // Commit the transaction
        $this->db->transComplete();

        // Return the ID of the newly inserted row
        return $insertedID;
    }
 

    public function editMoringTask($data, $mid) {
        if (is_array($data) && $mid > 0) {
            $this->db->table('morningtasks')
                ->set($data)
                ->where('mid', $mid)
                ->update();

            // Check if rows were affected to confirm update success
            return $this->db->affectedRows() > 0;
        }
        return false;
    }


   

}
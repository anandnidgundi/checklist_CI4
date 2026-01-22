<?php
namespace App\Models;

use CodeIgniter\Model;

class BM_TasksModel extends Model
{

    protected $table = 'bm_tasks';
    protected $primaryKey = 'mid';
    protected $allowedFields = [
        'mt0100',
        'mt0101',
        'mt0102',
        'mt0103',
        'mt0104',
        'mt0105',
        'mt0200',
        'mt0201',
        'mt0202',
        'mt0203',
        'mt0204',
        'mt0205',
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
        'mt0903',

        'mt1000',
        'mt1001',
        'mt1002',

        'mt1100',
        'mt1101',
        'mt1102',

        'emp_code',
        'branch',
        'createdDTM',
        'taskDate',
        'created_by',
        'modifiedDTM'
    ];

    //addBM_Task
    public function addBM_Task($data)
{
    // Insert into bm_tasks
    $this->insert($data);
    $insertId = $this->insertID();

    // Also insert into bm_tasks_logs
    $db = \Config\Database::connect();
    $db->table('bm_tasks_logs')->insert($data);

    return $insertId;
}
    //
    //editBM_Task($data, $id, $branch)
    public function editBM_Task($data, $id, $branch)
    {
        $this->where('mid', $id)
            ->where('branch', $branch)
            ->set($data)
            ->update();
            $db = \Config\Database::connect();
            $db->table('bm_tasks_logs')->insert($data);    
        return $this->affectedRows();
    }

    public function updateSubquestion($id, $subData)
    {

        $this->db->table('subquestions')
            ->where('id', $id)
            ->update($subData);

        return true;
    }

    public function updateReport_amendments($id, $report_amendments)
    {

        $this->db->table('report_amendments')
            ->where('id', $id)
            ->update($report_amendments);

        return true;
    }

    public function insertReport_amendments($report_amendment)
    {
        $this->db->table('report_amendments')->insert($report_amendment);
        return $this->insertID();
    }
    // same repeat for tables - repeat_punctures, repeat_samples, repeat_scan, report_escalations, sample_rejections
    public function insertRepeatPunctures($repeat_puncture)
    {
        $this->db->table('repeat_punctures')->insert($repeat_puncture);
        return $this->insertID();
    }
    public function insertRepeatSamples($repeat_sample)
    {
        $this->db->table('repeat_samples')->insert($repeat_sample);
        return $this->insertID();
    }
    public function insertRepeatScan($repeat_scan)
    {
        $this->db->table('repeat_scan')->insert($repeat_scan);
        return $this->insertID();
    }
    public function insertReportEscalation($report_escalations)
    {
        $this->db->table('report_escalations')->insert($report_escalations);
        return $this->insertID();
    }
    public function insertSampleRejections($sample_rejection)
    {
        $this->db->table('sample_rejections')->insert($sample_rejection);
        return $this->insertID();
    }
    public function updateRepeatPunctures($id, $repeat_puncture)
    {
        $this->db->table('repeat_punctures')
            ->where('id', $id)
            ->update($repeat_puncture);

        return true;
    }
    public function updateRepeatSamples($id, $repeat_sample)
    {
        $this->db->table('repeat_samples')
            ->where('id', $id)
            ->update($repeat_sample);

        return true;
    }
    public function updateRepeatScan($id, $repeat_scan)
    {
        $this->db->table('repeat_scan')
            ->where('id', $id)
            ->update($repeat_scan);

        return true;
    }
    public function updateReportEscalation($id, $report_escalations)
    {
        $this->db->table('report_escalations')
            ->where('id', $id)
            ->update($report_escalations);

        return true;
    }
    public function updateSampleRejections($id, $sample_rejection)
    {
        $this->db->table('sample_rejections')
            ->where('id', $id)
            ->update($sample_rejection);

        return true;
    }



    // checkExistingRecord
    public function checkExistingRecord($taskDate,$branch)
    {
        
        return $this->where('branch', $branch)
            ->where('taskDate', $taskDate)
            ->first();
    }

    //getBM_TaskDetails($id)
    public function getBM_TaskDetails($id)
    {
        return $this->where('mid', $id)->first();
    }

    //getBM_TaskList
    public function getBM_TaskList($user, $role)
    {
        $usersBranchList = $this->getUserBranchList($user, $role);
        $branchIds = array_column($usersBranchList, 'branch_id');
        return $this->whereIn('branch', $branchIds)
            ->findAll();
    }



    public function getBM_TaskDetailsByMid($mid, $role, $user)
    {
        $db2 = \Config\Database::connect('default'); // Secondary DB
        $defaultDB = \Config\Database::connect('secondary'); // Default DB (emp & branches)
    
        $results = [];
    
        if (!empty($mid)) {
            // Fetch task details from bm_tasks
            $taskResults = $db2->table('bm_tasks m')
                ->select('m.*, m.createdDTM as createdDTM, m.taskDate as mtaskDate')
                ->where('m.mid', $mid)
                ->limit(1)
                ->get()
                ->getResultArray();
    
            foreach ($taskResults as &$ts) {
                $mid = $ts['mid'];
    
                // Fetch employee details from default DB
                $emp = $defaultDB->table('new_emp_master')
                    ->select('fname, lname')
                    ->where('emp_code', $ts['created_by'])
                    ->get()
                    ->getRowArray();
    
                $ts['fname'] = $emp['fname'] ?? '';
                $ts['lname'] = $emp['lname'] ?? '';
    
                // Fetch branch name from default DB
                $branch = $defaultDB->table('Branches')
                    ->select('SysField as branch_name')
                    ->where('id', $ts['branch'])
                    ->get()
                    ->getRowArray();
    
                $ts['branch_name'] = $branch['branch_name'] ?? '';
    
                // Fetch subquestions from secondary DB
                $subquestions = $db2->table('subquestions')
                    ->select('*')
                    ->where('task_id', $mid)
                    ->get()
                    ->getResultArray();
    
                foreach ($subquestions as &$sq) {
                    $sq_id = $sq['sq_id'];
    
                    // Fetch subanswers
                    $subanswers = $db2->table('subquestions')
                        ->select('sq_id, squestion, sqvalue')
                        ->where('sq_id', $sq_id)
                        ->where('task_id', $mid)
                        ->get()
                        ->getResultArray();
    
                    $sq['subs'] = $subanswers;
                }
    
                $ts['subquestions'] = $subquestions;
    
                // Helper to fetch related data from other tables
                $relatedTables = [
                    'report_amendments' => 'report_amendments',
                    'repeat_punctures' => 'repeat_punctures',
                    'repeat_samples' => 'repeat_samples',
                    'repeat_scan' => 'repeat_scan',
                    'report_escalation' => 'report_escalations',
                    'sample_rejections' => 'sample_rejections',
                ];
    
                foreach ($relatedTables as $key => $tableName) {
                    $ts[$key] = $db2->table($tableName)
                        ->select('*')
                        ->where('task_id', $mid)
                        ->get()
                        ->getResultArray();
                }
            }
    
            return $taskResults;
        }
    
        return [];
    }
    

    // public function getBM_TaskDetailsByMid($mid, $role, $user)
    // {
    //     $db2 = \Config\Database::connect('secondary'); // Secondary DB for task-related data
    //     $defaultDB = \Config\Database::connect('default'); // Default DB for emp & branches
    //     $results = [];

    //     // Debugging: Check if midSelected is provided
    //     if (empty($mid)) {
    //         //log_message('error', 'midSelected is empty');
    //     }

    //     // Check if midSelected is provided 
    //     if (!empty($mid)) {
    //         // Start building the query
    //         $taskResults = $db2->table('bm_tasks m')
    //         ->select('m.*, m.createdDTM as createdDTM, m.taskDate as mtaskDate')
    //         ->where('m.mid', $mid)
    //         ->limit(1)
    //         ->get()
    //         ->getResultArray();

    //         // Apply filters based on provided parameters
    //         $this->where('m.mid', $mid);

    //         // Retrieve the results
    //         $taskResults = $this->get()->getResultArray();
    //         foreach ($taskResults as &$ts) {
    //             $mid = $ts['mid'];
    //             $subquestions = $this->db->table('subquestions')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['subquestions'] = $subquestions;
    //             $ts['report_amendments'] = [];

    //             foreach ($subquestions as &$sq) {
    //                 // Get sub-answers for each subquestion
    //                 $sq_id = $sq['sq_id'];
    //                 $subanswers = $this->db->table('subquestions')
    //                     ->select('sq_id, squestion, sqvalue')
    //                     ->where('sq_id', $sq_id)
    //                     ->where('task_id', $mid)
    //                     ->get()
    //                     ->getResultArray();
    //                 $sq['subs'] = $subanswers;
    //             }

    //             $report_amendments = $this->db->table('report_amendments')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['report_amendments'] = !empty($report_amendments) ? $report_amendments : [];

    //             // same repeat for tables - repeat_punctures, repeat_samples, repeat_scan, report_escalations, sample_rejections
    //             $repeat_punctures = $this->db->table('repeat_punctures')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['repeat_punctures'] = !empty($repeat_punctures) ? $repeat_punctures : [];
    //             $repeat_samples = $this->db->table('repeat_samples')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['repeat_samples'] = !empty($repeat_samples) ? $repeat_samples : [];
    //             $repeat_scan = $this->db->table('repeat_scan')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['repeat_scan'] = !empty($repeat_scan) ? $repeat_scan : [];
    //             $report_escalations = $this->db->table('report_escalations')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['report_escalation'] = !empty($report_escalations) ? $report_escalations : [];
    //             $sample_rejections = $this->db->table('sample_rejections')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['sample_rejections'] = !empty($sample_rejections) ? $sample_rejections : [];
    //         }

    //         //log_message('error', 'Query result: ' . print_r($results, true));
    //     } else {
    //         // Debugging: Log message if midSelected is missing
    //         //log_message('error', 'midSelected is missing, skipping the query');
    //     }

    //     // Return results only if filters yield data, otherwise return an empty array
    //     return !empty($taskResults) ? $taskResults : [];
    // }

    // public function getBM_TaskDetailsByMid($mid, $role, $user)
    // {
    //     $db2 = \Config\Database::connect('default');
    //     $results = [];
    
    //     // Check if midSelected is provided 
    //     if (!empty($mid)) {
    //         // Start building the query for bm_tasks table
    //         $this->select('m.*, n.fname, n.lname, b.SysField as branch_name')
    //             ->from('bm_tasks m')
    //             ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left') // This will use the default db
    //             ->join('Branches b', 'b.SysNo = m.branch', 'left') // This will use the default db
    //             ->orderBy('m.createdDTM', 'desc')  // Sort by created date
    //             ->limit(1);  // Apply limit to get only one row
    
    //         // Apply filters based on provided parameters
    //         $this->where('m.mid', $mid);
    
    //         // Retrieve the results for bm_tasks
    //         $taskResults = $this->get()->getResultArray();
            
    //         // Use $db2 for the new_emp_master and branches tables
           
    
    //         foreach ($taskResults as &$ts) {
    //             $mid = $ts['mid'];
                
    //             // Fetch subquestions related to the task
    //             $subquestions = $this->db->table('subquestions')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['subquestions'] = $subquestions;
    //             $ts['report_amendments'] = [];
    
    //             foreach ($subquestions as &$sq) {
    //                 // Get sub-answers for each subquestion
    //                 $sq_id = $sq['sq_id'];
    //                 $subanswers = $this->db->table('subquestions')
    //                     ->select('sq_id, squestion, sqvalue')
    //                     ->where('sq_id', $sq_id)
    //                     ->where('task_id', $mid)
    //                     ->get()
    //                     ->getResultArray();
    //                 $sq['subs'] = $subanswers;
    //             }
    
    //             // Fetch report amendments related to the task
    //             $report_amendments = $this->db->table('report_amendments')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['report_amendments'] = !empty($report_amendments) ? $report_amendments : [];
    
    //             // Same repeat for tables - repeat_punctures, repeat_samples, repeat_scan, report_escalations, sample_rejections
    //             $repeat_punctures = $this->db->table('repeat_punctures')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['repeat_punctures'] = !empty($repeat_punctures) ? $repeat_punctures : [];
                
    //             $repeat_samples = $this->db->table('repeat_samples')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['repeat_samples'] = !empty($repeat_samples) ? $repeat_samples : [];
    
    //             $repeat_scan = $this->db->table('repeat_scan')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['repeat_scan'] = !empty($repeat_scan) ? $repeat_scan : [];
    
    //             $report_escalations = $this->db->table('report_escalations')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['report_escalation'] = !empty($report_escalations) ? $report_escalations : [];
    
    //             $sample_rejections = $this->db->table('sample_rejections')
    //                 ->select('*')
    //                 ->where('task_id', $mid)
    //                 ->get()
    //                 ->getResultArray();
    //             $ts['sample_rejections'] = !empty($sample_rejections) ? $sample_rejections : [];
    
    //             // Now use $db2 for querying `new_emp_master` and `branches` tables
    //             // Fetching the employee details from `new_emp_master` using secondary DB ($db2)
    //             $empDetails = $db2->table('new_emp_master n')
    //                 ->select('n.fname, n.lname')
    //                 ->where('n.emp_code', $ts['created_by'])
    //                 ->get()
    //                 ->getRowArray();
    
    //             if ($empDetails) {
    //                 $ts['fname'] = $empDetails['fname'];
    //                 $ts['lname'] = $empDetails['lname'];
    //             }
    
    //             // Fetching the branch details from `branches` using secondary DB ($db2)
    //             $branchDetails = $db2->table('Branches b')
    //                 ->select('b.SysField')
    //                 ->where('b.SysNo', $ts['branch'])
    //                 ->get()
    //                 ->getRowArray();
    
    //             if ($branchDetails) {
    //                 $ts['branch_name'] = $branchDetails['branch'];
    //             }
    //         }
    //     } else {
    //         // Log message if mid is missing
    //         //log_message('error', 'midSelected is missing, skipping the query');
    //     }
    
    //     // Return results only if filters yield data, otherwise return an empty array
    //     return !empty($taskResults) ? $taskResults : [];
    // }


    public function getBranchComboTaskListNew($role, $user, $selectedMonth)
{
    // Connect to the databases
    $secondaryDB = \Config\Database::connect('secondary'); // For employee and branch data
    $defaultDB = \Config\Database::connect('default');     // For task data

    // Get user's mapped branches
    $userBranches = $this->getUserBranchList1($user, $role);
    $branchIds = [];

    foreach ($userBranches as $item) {
        if (!empty($item['branches'])) {
            $branchIds = array_merge($branchIds, explode(',', $item['branches']));
        }
    }

    // Clean branch IDs
    $branchIds = array_filter(array_unique($branchIds));

    if (empty($branchIds)) {
        return ['status' => false, 'message' => 'No branches mapped to the user.'];
    }

    // Get task list
    $builder = $defaultDB->table('bm_tasks m');
    $builder->select('
        m.*, 
        m.taskDate AS mtaskDate,
       
        cb.cluster,
        cb.zone
    ')
        ->join('user_map cb', 'FIND_IN_SET(m.branch, cb.branches)', 'left')
        // ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
        // ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
        // ->join('zones z', 'z.z_id = cz.zone_id', 'left')
        ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth)
        ->orderBy('m.taskDate', 'desc');

    if ($role != 'SUPER_ADMIN') {
        $builder->whereIn('m.branch', $branchIds);
    }

    $tasks = $builder->get()->getResultArray();

    if (empty($tasks)) {
        return ['status' => false, 'message' => 'No task data found.', 'data' => []];
    }

    // Get employee info from secondary DB
    $employees = $secondaryDB->table('new_emp_master')
        ->select('emp_code, fname, lname')
        ->get()
        ->getResultArray();

    $empMap = [];
    foreach ($employees as $emp) {
        $empMap[$emp['emp_code']] = $emp;
    }

    // Get branches from secondary DB
    $branches = $secondaryDB->table('Branches')
        ->select('id, SysField')
        ->get()
        ->getResultArray();

    $branchMap = [];
    foreach ($branches as $br) {
        $branchMap[$br['id']] = $br['SysField'];
    }

    // Merge emp and branch names into tasks
    $final = [];
    $seen = [];

    foreach ($tasks as $task) {
        $taskId = $task['mid'];

        if (isset($seen[$taskId])) {
            continue; // avoid duplicate task rows
        }

        $emp = $empMap[$task['created_by']] ?? ['fname' => '', 'lname' => ''];
        $task['fname'] = $emp['fname'];
        $task['lname'] = $emp['lname'];

        $task['branch_name'] = $branchMap[$task['branch']] ?? '';

        $seen[$taskId] = true;
        $final[] = $task;
    }

    return ['status' => true, 'message' => 'Task list fetched successfully.', 'data' => $final];
}

    









    public function getBM_TaskListForAdmin($role, $user, $selectedMonth, $selectedBranch, $selectedCluster)
{
    $db2 = \Config\Database::connect('secondary'); // For emp and branches
    $mainDB = \Config\Database::connect('default'); // For task-related tables

    // Step 1: Query tasks
    $builder = $mainDB->table('bm_tasks m');
    $builder->select('
        m.*,
        m.taskDate as mtaskDate,
        c.cluster as cluster_name,
        z.zone as zone_name
    ')
        ->join('user_map cb', 'FIND_IN_SET(m.branch, cb.branches)', 'left')
        ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster', 'left')
        ->join('clusters c', 'c.cluster_id = cb.cluster', 'left')
        ->join('zones z', 'z.z_id = cz.zone_id', 'left')
        ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth)
        ->orderBy('m.taskDate', 'desc');

    // Apply filters
    if ($role != 'SUPER_ADMIN' && !empty($selectedBranch)) {
        $builder->where('m.branch', $selectedBranch);
    }
    if ($role != 'SUPER_ADMIN' && !empty($selectedCluster)) {
        $builder->where('cb.cluster', $selectedCluster);
    }

    $results = $builder->get()->getResultArray();

    if (empty($results)) {
        return [];
    }

    // Step 2: Load employee & branch info from secondary DB
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

    // Step 3: Attach extra info
    foreach ($results as $key => &$task) {
        $taskId = $task['mid'];

        // Attach employee name
        $emp = $empMap[$task['created_by']] ?? null;
        $task['fname'] = $emp['fname'] ?? '';
        $task['lname'] = $emp['lname'] ?? '';

        // Attach branch name
        $task['branch_name'] = $branchMap[$task['branch']] ?? '';

        // Attach task-related extra data
        $task['report_amendments'] = $mainDB->table('report_amendments')->where('task_id', $taskId)->get()->getResultArray();
        $task['repeat_punctures'] = $mainDB->table('repeat_punctures')->where('task_id', $taskId)->get()->getResultArray();
        $task['repeat_samples'] = $mainDB->table('repeat_samples')->where('task_id', $taskId)->get()->getResultArray();
        $task['repeat_scan'] = $mainDB->table('repeat_scan')->where('task_id', $taskId)->get()->getResultArray();
        $task['report_escalation'] = $mainDB->table('report_escalations')->where('task_id', $taskId)->get()->getResultArray();
        $task['sample_rejections'] = $mainDB->table('sample_rejections')->where('task_id', $taskId)->get()->getResultArray();
    }
    

    $results = array_values(array_reduce($results, function($carry, $item) {
        $carry[$item['mid']] = $item; // overwrite if duplicate mid
        return $carry;
    }, []));

    return $results;
}


    

    // public function getBM_TaskListForAdmin($role, $user, $selectedMonth, $selectedBranch, $selectedCluster)
    // {
    //     //select all from bm_tasks table
    //     $builder = $this->db->table('bm_tasks m');
    //     $builder->select('
    //         m.*,
    //         m.taskDate as mtaskDate, 
    //         n.fname, n.lname,            
    //         b.branch as branch_name, 
    //         c.cluster as cluster_name, 
    //         z.zone as zone_name
    //     ')
    //         ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
    //         ->join('branches b', 'm.branch = b.branch_id', 'left')
    //         ->join('cluster_branch_map cb', 'm.branch = cb.branch_id', 'left')
    //         ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
    //         ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
    //         ->join('zones z', 'z.z_id = cz.zone_id', 'left')
    //         ->where('DATE_FORMAT(m.taskDate, "%Y-%m")', $selectedMonth)
    //         ->orderBy('m.taskDate', 'desc');
    //     // Add branch filter conditionally based on role
    //     if ($role != 'SUPER_ADMIN' && !empty($selectedBranch)) {
    //         $builder->where('m.branch', $selectedBranch);
    //     }
    //     if ($role != 'SUPER_ADMIN' && !empty($selectedCluster)) {
    //         $builder->where('cb.cluster_id', $selectedCluster);
    //     }
    //     // Execute the query
    //     $query = $builder->get();
    //     $results = $query->getResultArray();
    //     if (!empty($results)) {
            
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('report_amendments as r');
    //             $b->select('r.*');
    //             $b->where('r.task_id', $value['mid']);
    //             $query = $b->get();
    //             $results[$key]['report_amendments'] = $query->getResultArray();
    //         }

    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('repeat_punctures as r');
    //             $b->select('r.*');
    //             $b->where('r.task_id', $value['mid']);
    //             $query = $b->get();
    //             $results[$key]['repeat_punctures'] = $query->getResultArray();
    //         }

    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('repeat_samples as r');
    //             $b->select('r.*');
    //             $b->where('r.task_id', $value['mid']);
    //             $query = $b->get();
    //             $results[$key]['repeat_samples'] = $query->getResultArray();
    //         }

    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('repeat_scan as r');
    //             $b->select('r.*');
    //             $b->where('r.task_id', $value['mid']);
    //             $query = $b->get();
    //             $results[$key]['repeat_scan'] = $query->getResultArray();
    //         }
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('report_escalations as r');
    //             $b->select('r.*');
    //             $b->where('r.task_id', $value['mid']);
    //             $query = $b->get();
    //             $results[$key]['report_escalation'] = $query->getResultArray();
    //         }
    //         foreach ($results as $key => $value) {
    //             $b = $this->db->table('sample_rejections as r');
    //             $b->select('r.*');
    //             $b->where('r.task_id', $value['mid']);
    //             $query = $b->get();
    //             $results[$key]['sample_rejections'] = $query->getResultArray();
    //         }
           
    //     }
    //     return $results;
    // }


    public function addSubquestions($subData)
    {
        return $this->db->table('subquestions')->insertBatch($subData);
    }

    public function getUserBranchList($user, $role)
    {
        $db2 = \Config\Database::connect('secondary');
        $builder = $db2->table('branchesmapped as bm')
            ->select('bm.emp_code, bm.branch_id, bm.branch, bm.cluster_id, bm.cluster, cl.area_id, a.area')
            ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
            ->join('area as a', 'cl.area_id = a.id', 'left');

        // Apply condition only if the role is not 'SUPER_ADMIN'
        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $user);
        }

        $query = $builder->get();
        return $query->getResultArray();
    }


public function getUserBranchList1($user, $role)
    {
     
        $db2 = \Config\Database::connect('default');
        $builder = $db2->table(' user_map as bm')
            ->select('bm.emp_code, bm.zone, bm.cluster, bm.role, bm.branches');
           
           

        // Apply condition only if the role is not 'SUPER_ADMIN'
        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $user);
        }

        $query = $builder->get();
        return $query->getResultArray();
    }
}

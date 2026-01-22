<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup              = 'secondary';
    protected $table            = 'new_emp_master';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'emp_code',
        'fname',
        'lname',
        'mname', 
        'comp_name',
        'doj',
        'dob',
        'gender',
        'mail_id',
        'report_mngr',
        'function_mngr',
        'ou_name',
        'dept_name',
        'location_name',
        'designation_name',
        'grade',
        'region',
        'country',
        'city',
        'position',
        'cost_center',
        'pay_group',
        'emp_status',
        'active',
        'disabled',
        'effective_from',
        'created_on',
        'created_by',
        'modified_on',
        'modified_by',
        'mobile',
        'depend1',
        'depend2',
        'depend3',
        'depend4',
        'depend5',
        'depend6',
        'exit_date',
        'password',
        'validity',
        'is_admin',
        'is_super_admin',
        'is_manager_approval',
        'is_traveldesk',
        'is_hotelinfo',
        'is_audit_approval',
        'is_finance_approval',
        'is_travelmanager_approved',
        'is_hotelmanager_approved',
        'updated_at',
        'failed_attempts',
        'bank_name',
        'bank_acnum',
        'ifsc_code',
        'check_list'

    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_on';
    protected $updatedField  = 'modified_on';
    //protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];


    public function BM_DashboardCount($user, $role, $selectedMonth, $selectedBranch)
    {
        $db2 = \Config\Database::connect('default');
        // log_message('error', "selectedMonth: {$selectedMonth}");
        // log_message('error', "selectedBranch: {$selectedBranch}");
        // echo $selectedBranch;die();
        $morningTasksCount = $db2->table('bm_tasks')
            ->where('branch', $selectedBranch)
            ->where('DATE_FORMAT(taskDate, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        $powerConsumptionCount = $db2->table('power_consumption')
            ->where('branch_id', $selectedBranch)
            ->where('DATE_FORMAT(consumption_date, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        $dieselConsumptionCount = $db2->table('diesel_consumption')
            ->where('branch_id', $selectedBranch)
            ->where('DATE_FORMAT(consumption_date, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        $bmDashboardCount = [
            'morningTasksCount' => $morningTasksCount,
            'powerConsumptionCount' => $powerConsumptionCount,
            'dieselConsumptionCount' => $dieselConsumptionCount
        ];
        return $bmDashboardCount;
    }

    public function CM_DashboardCount($user, $role, $selectedMonth, $selectedBranch, $selectedCluster)
    {
        
        $db2 = \Config\Database::connect('default');
        // Get branch IDs for the selected cluster
        $branchIds = [];

        if ($selectedCluster > 0) {
            $branchIds = $db2->table('user_map as cb')
                ->select('cb.*')
                ->where('cb.branches', $selectedBranch)
                ->where('cb.cluster', $selectedCluster)
                // ->where('cb.zone', $selectedZone)
                ->get()
                ->getResultArray();

            //    $branchIds = array_column($branchIds, 'branches');
            

        } else if ($selectedBranch === 0  && $selectedCluster === 0) {
            $branchIds = [];
        }

        // Count BM Tasks
        $builder = $db2->table('bm_tasks');
        
        if ($selectedBranch > 0) {
            $builder->where('branch', $selectedBranch);
        } else if (!empty($branchIds)) {
            $builder->whereIn('branch', $branchIds);
        }
        $bmTasksCount = $builder->where('DATE_FORMAT(taskDate, "%Y-%m")', $selectedMonth)
            ->countAllResults();



        // Count Power Consumption
        $builder = $db2->table('power_consumption');
        if ($selectedBranch > 0) {
            $builder->where('branch_id', $selectedBranch);
        } else if (!empty($branchIds)) {
            $builder->whereIn('branch_id', $branchIds);
        }
        $powerConsumptionCount = $builder->where('DATE_FORMAT(consumption_date, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        // Count Diesel Consumption
        $builder = $db2->table('diesel_consumption');
        if ($selectedBranch > 0) {
            $builder->where('branch_id', $selectedBranch);
        } else if (!empty($branchIds)) {
            $builder->whereIn('branch_id', $branchIds);
        }
        $dieselConsumptionCount = $builder->where('DATE_FORMAT(consumption_date, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        return [
            'bmTasksCount' => $bmTasksCount,
            'powerConsumptionCount' => $powerConsumptionCount,
            'dieselConsumptionCount' => $dieselConsumptionCount
        ];
    }

    public function CM_DashboardCount_old($user, $role, $selectedMonth, $selectedBranch, $selectedCluster)
    {
        $db2 = \Config\Database::connect('default');
        // Get branch IDs for the selected cluster
        $branchIds = [];
        if ($selectedCluster > 0) {
            $branchIds = $db2->table('cluster_branch_map as cb')
                ->select('cb.branch_id')
                ->where('cb.cluster_id', $selectedCluster)
                ->get()
                ->getResultArray();
            $branchIds = array_column($branchIds, 'branch_id');
        } else if ($selectedBranch === 0  && $selectedCluster === 0) {
            $branchIds = [];
        }

        // Count BM Tasks
        $builder = $db2->table('bm_tasks');
        if ($selectedBranch > 0) {
            $builder->where('branch', $selectedBranch);
        } else if (!empty($branchIds)) {
            $builder->whereIn('branch', $branchIds);
        }
        $bmTasksCount = $builder->where('DATE_FORMAT(taskDate, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        // Count Power Consumption
        $builder = $db2->table('power_consumption');
        if ($selectedBranch > 0) {
            $builder->where('branch_id', $selectedBranch);
        } else if (!empty($branchIds)) {
            $builder->whereIn('branch_id', $branchIds);
        }
        $powerConsumptionCount = $builder->where('DATE_FORMAT(consumption_date, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        // Count Diesel Consumption
        $builder = $db2->table('diesel_consumption');
        if ($selectedBranch > 0) {
            $builder->where('branch_id', $selectedBranch);
        } else if (!empty($branchIds)) {
            $builder->whereIn('branch_id', $branchIds);
        }
        $dieselConsumptionCount = $builder->where('DATE_FORMAT(consumption_date, "%Y-%m")', $selectedMonth)
            ->countAllResults();

        return [
            'bmTasksCount' => $bmTasksCount,
            'powerConsumptionCount' => $powerConsumptionCount,
            'dieselConsumptionCount' => $dieselConsumptionCount
        ];
    }

    // public function CM_DashboardCount($user, $role, $selectedMonth)
    // {

    //     // Fetch the user's branch IDs
    //     $branch  = $this->getCMUserBranchList($user, $role);

    //     // Convert the branch IDs to strings
    //     $branchIds = array_map('strval', $branch);

    //     $morningTasksCount = $this->db->table('morningtasks')
    //         ->whereIn('branch', $branchIds)
    //         ->where('DATE_FORMAT(taskDate, "%Y-%m")', $selectedMonth)
    //         ->countAllResults();

    //     $nightTasksCount = $this->db->table('nighttasks')
    //         ->whereIn('branch', $branchIds)
    //         ->where('DATE_FORMAT(taskDate, "%Y-%m")', $selectedMonth)
    //         ->countAllResults();

    //     $cm_morningTasksCount = $this->db->table('cm_morning_tasks')
    //         ->where('created_by', $user)
    //         ->where('DATE_FORMAT(taskDate, "%Y-%m")', $selectedMonth)
    //         ->countAllResults();

    //     $cm_nightTasksCount = $this->db->table('cm_night_tasks')
    //         ->where('created_by', $user)
    //         ->where('DATE_FORMAT(taskDate, "%Y-%m")', $selectedMonth)
    //         ->countAllResults();

    //     $bmDashboardCount = [
    //         'bm_morningTasksCount' => $morningTasksCount,
    //         'bm_nightTasksCount' => $nightTasksCount,
    //         'cm_morningTasksCount' => $cm_morningTasksCount,
    //         'cm_nightTasksCount' => $cm_nightTasksCount
    //     ];
    //     return $bmDashboardCount;
    // }

    public function getCMUserBranchList($user, $role)
    {
        $db2 = \Config\Database::connect('default');
        $b = $db2->table('branchesmapped as b')
            ->select('b.cluster_id')
            ->where('emp_code', $user)
            ->get();
        $b2 = $b->getRowArray();
        $cluster_id = $b2['cluster_id'];

        $builder = $db2->table('cluster_branch_map as cl')
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
        log_message('error', "Branch IDs fetched from getUserBranchList: " . print_r($branchIds, true));

        // If no branches are found, log it and return empty array
        if (empty($branchIds)) {
            log_message('error', "No branches found for user {$user} with role {$role}");
        }

        return $branchIds; // Return only the branch_ids
    }


    public function getZoneClusterBranchesTree($zone_id)
    {
        $db2 = \Config\Database::connect('default');
        // Get Zone Information
        $zone = $db2->table('zones as z')
            ->where('z.z_id', $zone_id)
            ->get()
            ->getRowArray(); // Assuming a single zone is returned

        if (!$zone) {
            return []; // Return empty array if no zone is found
        }

        // Get Clusters in the Zone
        $clusterList = $db2->table('cluster_zone_map as cz')
            ->select('cz.cluster_id, c.cluster')
            ->join('clusters as c', 'c.cluster_id = cz.cluster_id', 'left')
            ->where('cz.zone_id', $zone_id)
            ->get()
            ->getResultArray();

        // Extract Cluster IDs
        $clustersID = array_column($clusterList, 'cluster_id');

        // Check if there are any cluster IDs to avoid empty WHERE IN clause
        if (empty($clustersID)) {
            // No clusters found, add clusters as an empty array and return the zone data
            $zone['clusters'] = [];
            return $zone;
        }

        // Get Branches in each Cluster
        $branchLists = $db2->table('cluster_branch_map as cb')
            ->select('cb.cluster_id, cb.branch_id, b.branch')
            ->join('branches as b', 'b.branch_id = cb.branch_id', 'left')
            ->whereIn('cb.cluster_id', $clustersID)
            ->get()
            ->getResultArray();

        // Organize branches by cluster_id
        $branchesByCluster = [];
        foreach ($branchLists as $branch) {
            $branchesByCluster[$branch['cluster_id']][] = [
                'branch_id' => $branch['branch_id'],
                'branch' => $branch['branch']
            ];
        }

        // Add branches to each cluster
        foreach ($clusterList as &$cluster) {
            $cluster['branches'] = $branchesByCluster[$cluster['cluster_id']] ?? [];
        }

        // Add clusters to the zone
        $zone['clusters'] = $clusterList;

        return $zone;
    }


    public function getUsersList()
    {

        try {
            // Fetch all employee details along with the role in one query
            $employees = $this->db->table('new_emp_master as a')
                ->select('a.*, b.role')
                ->join('bmcm as b', 'b.emp_code = a.emp_code', 'left')
                ->where('a.active', 'Active')
                ->orderBy('b.role', 'asc')
                ->get()
                ->getResultArray();

            // Gather employee codes for branch list query
            $empCodes = array_column($employees, 'emp_code');
            $db2 = \Config\Database::connect('default');
            // Get branch details for these employee codes in one query
            $branchLists = $db2->table('branchesmapped as bm')
                ->select('bm.emp_code, bm.branch_id, b.branch, bm.cluster_id, c.cluster, bm.zone_id,z.zone')
                ->join('branches as b', 'bm.branch_id = b.branch_id', 'left')
                ->join('clusters as c', 'c.cluster_id = bm.cluster_id', 'left')
                ->join('zones as z', 'z.z_id = bm.zone_id', 'left')
                ->whereIn('bm.emp_code', $empCodes)
                ->get()
                ->getResultArray();

            // Map branches by emp_code for easier assignment
            $branchesByEmpCode = [];
            foreach ($branchLists as $branch) {
                $branchesByEmpCode[$branch['emp_code']][] = $branch;
            }

            // Assign branch lists to corresponding employees
            foreach ($employees as &$employee) {
                $employee['userBranchList'] = $branchesByEmpCode[$employee['emp_code']] ?? [];
            }

            return $employees;
        } catch (\Exception $e) {
            log_message('error', 'Database query failed in getUsersList: ' . $e->getMessage());
            return [];
        }
    }

    public function addClusterToZone($data, $zone_id, $cluster_id)
    {

        $db2 = \Config\Database::connect('default');
        $db2->transStart();
        // Prepare query to check if cluster_id already exists in the table with optional zone filter
        $builder = $db2->table('cluster_zone_map')
            ->where('cluster_id', $cluster_id);

        if ($zone_id > 0) {
            $builder->where('zone_id', $zone_id);
        }

        $existingRecord = $builder->get()->getRowArray();

        if ($existingRecord) {
            // If cluster_id exists, perform an update
            $builder = $db2->table('cluster_zone_map')->where('cluster_id', $cluster_id);

            if ($zone_id > 0) {
                $builder->where('zone_id', $zone_id);
            }

            log_message('debug', 'Attempting to add cluster assignment in model for cluster_id: ' . $cluster_id);

            if (!$builder->update($data)) {
                $error = $db2->error();
                log_message('error', 'Database error during update in cluster_zone_map: ' . json_encode($error));
                $db2->transRollback();
                return false;
            }
        } else {

            log_message('debug', 'Entered addClusterToZone for cluster_id: ' . $cluster_id);

            // If cluster_id does not exist, perform an insert
            if (!$db2->table('cluster_zone_map')->insert($data)) {
                $error = $db2->error();
                log_message('error', 'Failed to insert data into cluster_zone_map table: ' . print_r($error, true));
                $db2->transRollback();
                return false;
            }
        }

        $db2->transComplete();
        if (!$db2->transStatus()) {
            log_message('error', 'Failed transaction for cluster_id: ' . $cluster_id);
        }

        return $db2->transStatus();
    }

    public function addBranchToCluster($data, $cluster_id, $branch_id)
    {

        $db2 = \Config\Database::connect('default');
        $db2->transStart();
        // Prepare query to check if branch_id already exists in the table with optional cluster filter
        $builder = $db2->table('cluster_branch_map')
            ->where('branch_id', $branch_id);

        if ($cluster_id > 0) {
            $builder->where('cluster_id', $cluster_id);
        }

        $existingRecord = $builder->get()->getRowArray();

        if ($existingRecord) {
            // If branch_id exists, perform an update
            $builder = $db2->table('cluster_branch_map')->where('branch_id', $branch_id);

            if ($cluster_id > 0) {
                $builder->where('cluster_id', $cluster_id);
            }

            log_message('error', 'Attempting to add branch assignment in model for branch_id: ' . $branch_id);

            if (!$builder->update($data)) {
                $error = $db2->error();
                log_message('error', 'Database error during update in cluster_branch_map: ' . json_encode($error));
                $db2->transRollback();
                return false;
            }
        } else {

            log_message('error', 'Entered addBranchToCluster for branch_id: ' . $branch_id);

            // If branch_id does not exist, perform an insert
            if (!$db2->table('cluster_branch_map')->insert($data)) {
                $error = $db2->error();
                log_message('error', 'Failed to insert data into cluster_branch_map table: ' . print_r($error, true));
                $db2->transRollback();
                return false;
            }
        }

        $db2->transComplete();
        if (!$db2->transStatus()) {
            log_message('error', 'Failed transaction for branch_id: ' . $branch_id);
        }

        return $db2->transStatus();
    }

    // public function getClusterBranchList($cluster_id)
    // {
    //     $db2 = \Config\Database::connect('default');
    //     $builder = $db2->table('cluster_branch_map as cb')
    //         ->select('b.branch, cb.branch_id')
    //         ->join('branches as b', 'b.branch_id = cb.branch_id', 'left');
    //     $builder->where('cb.cluster_id', $cluster_id);

    //     // Log the SQL query before executing it
    //     // log_message('error', 'SQL Query: ' . $this->db->getLastQuery());

    //     $query = $builder->get();
    //     $result = $query->getResultArray();

    //     // Log the result of the query
    //     // log_message('error', 'Cluster Branch List Result: ' . json_encode($result));

    //     return $result;
    // }

    public function getClusterBranchList($cluster_id)
    {
        try {
            $db2 = \Config\Database::connect('default');
            if (!$db2) {
                log_message('error', 'Failed to connect to default database.');
                return [];
            }

            $builder = $db2->table('cluster_branch_map as cb')
                ->select('b.branch, cb.branch_id')
                ->join('branches as b', 'b.branch_id = cb.branch_id', 'left')
                ->where('cb.cluster_id', $cluster_id);

            $query = $builder->get();
            $result = $query->getResultArray();

            log_message('debug', 'Cluster Branch List Result: ' . json_encode($result));
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'Database error in getClusterBranchList: ' . $e->getMessage());
            return [];
        }
    }

    // public function getZoneClusterList($zone_id)
    // {
    //     $db2 = \Config\Database::connect('default');

    //     $builder = $db2->table('cluster_zone_map as cz')
    //         ->select('c.cluster, cz.cluster_id')
    //         ->join('clusters as c', 'c.cluster_id = cz.cluster_id', 'left');
    //     $builder->where('cz.zone_id', $zone_id);

    //     // Log the SQL query before executing it
    //     log_message('error', 'SQL Query: ' . $db2->getLastQuery());

    //     $query = $builder->get();
    //     $result = $query->getResultArray();

    //     // Log the result of the query
    //     log_message('error', 'Zone Cluster List Result: ' . json_encode($result));

    //     return $result;
    // }

    // fetch zones list, clusters list, branches list from user_map table
    public function getUserwiseBranchClusterZoneList($user, $role) {
        $db2 = \Config\Database::connect('default');
        $secondaryDB = \Config\Database::connect('secondary');

        // For AVP or SUPER_ADMIN roles, fetch all data
        if ($role === 'AVP' || $role === 'SUPER_ADMIN') {
            $zones = $db2->table('zones')
                ->select('z_id, zone')
                ->where('status', 'A')
                ->get()
                ->getResultArray();

            $clusters = $db2->table('clusters') 
                ->select('cluster_id, cluster')
                ->where('status', 'A')
                ->get()
                ->getResultArray();

            $branches = [];
            try {
                $branches = $secondaryDB->table('Branches')
                    ->select('id, SysField as branch_name')
                    ->where('Status', 'A')
                    ->orderBy('id', 'ASC')
                    ->get()
                    ->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error fetching branches: ' . $e->getMessage());
            }

            return [
                'zones' => $zones,
                'clusters' => $clusters, 
                'branches' => $branches
            ];
        }

        // For other roles, fetch based on user mapping
        $userMap = $db2->table('user_map')
            ->where('emp_code', $user)
            ->get()
            ->getRowArray();

        if (!$userMap) {
            return [
                'zones' => [],
                'clusters' => [],
                'branches' => []
            ];
        }

        // Parse IDs from comma-separated strings
        $zoneIds = !empty($userMap['zone']) ? explode(',', $userMap['zone']) : [];
        $clusterIds = !empty($userMap['cluster']) ? explode(',', $userMap['cluster']) : [];
        $branchIds = !empty($userMap['branches']) ? explode(',', $userMap['branches']) : [];

        // Fetch zone details
        $zones = [];
        if (!empty($zoneIds)) {
            $zones = $db2->table('zones')
                ->select('z_id, zone')
                ->whereIn('z_id', $zoneIds)
                ->where('status', 'A')
                ->get()
                ->getResultArray();
        }

        // Fetch cluster details
        $clusters = [];
        if (!empty($clusterIds)) {
            $clusters = $db2->table('clusters')
                ->select('cluster_id, cluster')
                ->whereIn('cluster_id', $clusterIds)
                ->where('status', 'A')
                ->get()
                ->getResultArray();
        }

        // Fetch branch details
        $branches = [];
        if (!empty($branchIds)) {
            try {
                $branches = $secondaryDB->table('Branches')
                    ->select('id, SysField as branch_name')
                    ->whereIn('id', $branchIds)
                    ->where('Status', 'A')
                    ->get()
                    ->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error fetching branches: ' . $e->getMessage());
            }
        }

        return [
            'zones' => $zones,
            'clusters' => $clusters,
            'branches' => $branches
        ];
    }

    public function getCMclusterList($user, $role)
    {
        $db2 = \Config\Database::connect('default');
        $builder = $db2->table('branchesmapped as bm')
            ->select('bm.emp_code,  bm.cluster_id, bm.cluster');
        // ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
        // ->join('area as a', 'cl.area_id = a.id', 'left');

        // Apply condition only if the role is not 'SUPER_ADMIN'
        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $user);
        }

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getUserZones($user, $role)
{
    $db2 = \Config\Database::connect('default');

    if ($role !== 'BM') {
        // Step 1: Get user map data
        $userMapData = $db2->table('user_map')
            ->select('zone, cluster, branches')
            ->where('emp_code', $user)
            ->get()
            ->getResultArray();

        $allZoneIDs    = [];
        $allClusterIDs = [];
        $allBranchIDs  = [];

        foreach ($userMapData as $row) {
            // Zones (comma-separated zone IDs)
            if (!empty($row['zone'])) {
                $zoneIDs = array_map('trim', explode(',', $row['zone']));
                $allZoneIDs = array_merge($allZoneIDs, $zoneIDs);
            }

            // Clusters
            if (!empty($row['cluster'])) {
                $clusterIDs = array_map('trim', explode(',', $row['cluster']));
                $allClusterIDs = array_merge($allClusterIDs, $clusterIDs);
            }

            // Branches
            if (!empty($row['branches'])) {
                $branchIDs = array_map('trim', explode(',', $row['branches']));
                $allBranchIDs = array_merge($allBranchIDs, $branchIDs);
            }
        }



        
        // Deduplicate
        $allZoneIDs    = array_unique($allZoneIDs);
        $allClusterIDs = array_unique($allClusterIDs);
        $allBranchIDs  = array_unique($allBranchIDs);

        // Step 2: Fetch zones from IDs
        $zones = [];
        if (!empty($allZoneIDs)) {
            $zones = $db2->table('zones')
                ->select('z_id, zone as z_zone')
                ->whereIn('z_id', $allZoneIDs)
                ->get()
                ->getResultArray();
        }

        // Step 3: Fetch cluster details
        $clusters = [];
        if (!empty($allClusterIDs)) {
            $clusters = $db2->table('clusters')
                ->whereIn('cluster_id', $allClusterIDs)
                ->get()
                ->getResultArray();
        }

        // Step 4: Fetch branches and build a lookup
        $branchesList = [];
        $branchNames  = [];
        if (!empty($allBranchIDs)) {
            $branches = $this->db->table('Branches') // <- use default DB
    ->select('id, SysField')
    ->whereIn('id', $allBranchIDs)
    ->get()
    ->getResultArray();

            foreach ($branches as $branch) {
                $branchesList[] = [
                    'b_id'   => $branch['id'],
                    'b_name' => $branch['SysField'],
                ];
                $branchNames[$branch['id']] = $branch['SysField'];
            }
        }

        // Step 5: Replace branch IDs in clusters with names
        foreach ($clusters as &$cluster) {
            $clusterBranches = explode(',', $cluster['branches']);
            $branchNamesList = [];

            foreach ($clusterBranches as $branchID) {
                $branchID = trim($branchID);
                if (isset($branchNames[$branchID])) {
                    $branchNamesList[] = $branchNames[$branchID];
                }
            }

            $cluster['branches'] = implode(', ', $branchNamesList);
        }

        // Step 6: Return full response
        return [
            'STATUS'   => true,
            'message'  => 'Data fetched successfully',
            'zones'    => $zones,
            'clusters' => $clusters,
            'branches' => $branchesList
        ];
    } else {
        return $this->response->setJSON([
            'STATUS'  => false,
            'message' => 'Access denied for BM role',
        ]);
    }
}





    public function getUserZonesAVP($user, $role)
    {
        $db2 = \Config\Database::connect('default');
        $secondaryDB = \Config\Database::connect('secondary'); // For Branches table

        // If role is BM, deny access
        if ($role === 'BM') {
            return [
                'STATUS'  => false,
                'message' => 'Access denied for BM role'
            ];
        }

        // If role is AVP, return ALL zones, clusters, branches
        if ($role === 'AVP') {
            $zones = $db2->table('zones')
                ->select('z_id, zone')
                ->get()
                ->getResultArray();

            $clusters = $db2->table('clusters')
                ->get()
                ->getResultArray();

            $branches = $secondaryDB->table('Branches')
                ->select('id, SysField')
                ->get()
                ->getResultArray();

            $branchesList = [];
            $branchNames = [];

            foreach ($branches as $branch) {
                $branchesList[] = [
                    'b_id'   => $branch['id'],
                    'b_name' => $branch['SysField'],
                ];
                $branchNames[$branch['id']] = $branch['SysField'];
            }

            // Update cluster branches with names
            foreach ($clusters as &$cluster) {
                $branchIds = explode(',', $cluster['branches']);
                $branchLabels = [];

                foreach ($branchIds as $bid) {
                    $bid = trim($bid);
                    if (isset($branchNames[$bid])) {
                        $branchLabels[] = $branchNames[$bid];
                    }
                }

                $cluster['branches'] = implode(', ', $branchLabels);
            }

            return [
                'STATUS'   => true,
                'message'  => 'Full access data for AVP',
                'clusters' => $clusters,
                'branches' => $branchesList,
                'zones'    => $zones
            ];
        }

        // For all other roles (non-BM and non-AVP)
        $userMapData = $db2->table('user_map as b')
            ->select('b.zone as b_zone, z.zone as z_zone, z.z_id, b.cluster as b_cluster, b.branches as b_branches')
            ->join('zones as z', 'z.z_id = b.zone', 'left')
            ->where('b.emp_code', $user)
            ->get()
            ->getResultArray();

        if (empty($userMapData)) {
            return $this->response->setJSON([
                'STATUS'  => false,
                'message' => 'No mapping found for emp_code: ' . $user,
            ]);
        }

        $allClusterIDs = [];
        $allBranchIDs = [];
        $zones = [];

        foreach ($userMapData as $row) {
            if (!empty($row['b_cluster'])) {
                $clusterIDs = array_map('trim', explode(',', $row['b_cluster']));
                $allClusterIDs = array_merge($allClusterIDs, $clusterIDs);
            }

            if (!empty($row['b_branches'])) {
                $branchIDs = array_map('trim', explode(',', $row['b_branches']));
                $allBranchIDs = array_merge($allBranchIDs, $branchIDs);
            }

            if (!empty($row['z_zone'])) {
                $zones['z_zone'] = $row['z_zone'];
                $zones['z_id'] = $row['z_id'];
            }
        }

        $allClusterIDs = array_unique($allClusterIDs);
        $allBranchIDs = array_unique($allBranchIDs);
        $zones = array_unique($zones);

        $clusters = [];
        if (!empty($allClusterIDs)) {
            $clusters = $db2->table('clusters')
                ->whereIn('cluster_id', $allClusterIDs)
                ->get()
                ->getResultArray();
        }

        $branchesList = [];
        $branchNames = [];
        if (!empty($allBranchIDs)) {
            $branches = $secondaryDB->table('Branches')
                ->select('id, SysField')
                ->whereIn('id', $allBranchIDs)
                ->get()
                ->getResultArray();

            foreach ($branches as $branch) {
                $branchesList[] = [
                    'b_id'   => $branch['id'],
                    'b_name' => $branch['SysField'],
                ];
                $branchNames[$branch['id']] = $branch['SysField'];
            }
        }

        foreach ($clusters as &$cluster) {
            $clusterBranchNames = [];
            $clusterBranchIds = explode(',', $cluster['branches']);

            foreach ($clusterBranchIds as $branchID) {
                $branchID = trim($branchID);
                if (isset($branchNames[$branchID])) {
                    $clusterBranchNames[] = $branchNames[$branchID];
                }
            }

            $cluster['branches'] = implode(', ', $clusterBranchNames);
        }

        return [
            'STATUS'   => true,
            'message'  => 'Data fetched successfully',
            'clusters' => $clusters,
            'branches' => $branchesList,
            'zones'    => $zones
        ];
    }




    // $b2 = $b->getRowArray(); // Retrieve the first row of the result

    // // Debugging: Output the result to check it
    // // echo "<pre>";
    // // print_r($b2); // This will print the resulting array
    // // die(); 
    // return $b2;
    //     $cluster_id = $b2['cluster_id'];

    //     $builder = $db2->table('cluster_zone_map as cz')
    //         ->select('cz.zone_id, z.zone')
    //         ->join('zones as z', 'z.z_id = cz.zone_id', 'left')
    //         ->where('cz.cluster_id', $cluster_id);
    //     $query = $builder->get();
    //     return $query->getResultArray();
    // } else {
    //     $builder = $db2->table('branchesmapped as bm')
    //         ->select('bm.emp_code, bm.zone_id, z.zone')
    //         ->join('zones as z', 'z.z_id = bm.zone_id', 'left');
    //     // Apply condition only if the role is not 'SUPER_ADMIN'
    //     if ($role != 'SUPER_ADMIN') {
    //         $builder->where('bm.emp_code', $user);
    //     }
    //     $query = $builder->get();
    //     return $query->getResultArray();




    public function getUserBranchClusterZoneList($user, $role)
    {
        $db2 = \Config\Database::connect('default');
        log_message('error', 'Model - User: ' . $user . ', Role: ' . $role);
        if ($role === 'BM') {
            $uList = $db2->table('branchesmapped as bm')
                ->select('  bm.branch_id, b.branch')
                ->join('branches as b', 'b.branch_id = bm.branch_id', 'left')
                ->where('bm.emp_code', $user)
                ->get()
                ->getResultArray();
        } else if ($role === 'CM') {
            $uList = $db2->table('branchesmapped as bm')
                ->select('bm.cluster_id, c.cluster')
                ->join('clusters as c', 'c.cluster_id = bm.cluster_id', 'left')
                ->where('bm.emp_code', $user)
                ->get()
                ->getResultArray();
        } else if ($role === 'ZONAL_MANAGER' || $role === 'AVP') {
            $uList = $db2->table('branchesmapped as bm')
                ->select('bm.zone_id, z.zone')
                ->join('zones as z', 'z.z_id = bm.zone_id', 'left')
                ->where('bm.emp_code', $user)
                ->get()
                ->getResultArray();
        } else if ($role === 'SUPER_ADMIN') {
            $uList = $db2->table('branchesmapped as bm')
                ->select('bm.zone_id, z.zone')
                ->join('zones as z', 'z.z_id = bm.zone_id', 'left')
                ->get()
                ->getResultArray();
        }

        log_message('error', '  user id ' . $user);
        log_message('error', '  role 11' . $role);
        log_message('error', '  uList ' . json_encode($uList));

        return [
            'uList' => $uList,
        ];
    }

    public function getUsersAreaList($emp_code)
    {
        $user = $this->getUserDetails($emp_code);
        if (!$user) {
            return [];
        }
        $db2 = \Config\Database::connect('default');

        // Start building the query
        $areaLists = $db2->table('branchesmapped as bm')
            ->select('cl.area_id, a.area')
            ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
            ->join('area as a', 'cl.area_id = a.id', 'left')
            ->groupBy('cl.area_id');  // Ensure chaining is correct

        // Restrict access for non-admin roles
        if ($user->isAdmin == 'N') {
            $areaLists = $areaLists->where('bm.emp_code', $emp_code);
        }

        // Execute query and return result 
        return $areaLists->get()->getResultArray();
    }



    public function getUserDetails($bmid)
    {

        // log_message('error', '  user id ' . $bmid);
        if ($bmid > 0) {
            // Fetch user details from the 'new_emp_master' table where 'emp_code' matches the provided $bmid
            return $this->select('new_emp_master.*, bmcm.role') // Select fields you need
                ->join('bmcm', 'bmcm.emp_code = new_emp_master.emp_code', 'left') // Define the join condition
                ->where('new_emp_master.emp_code', $bmid)
                //->where( 'bmcm.role !=', NULL )
                ->get()
                ->getRow();
            // Fetch and return a single row as an object
        }
        return false;
        // Return false if the $bmid is invalid
    }
 
    public function getUserProfiles($bmid)
    {
        log_message('error', '  user id ' . $bmid);
        $db2 = \Config\Database::connect('secondary');
        if ($bmid > 0) {
            // Fetch user details from the 'new_emp_master' table where 'emp_code' matches the provided $bmid
            return $this->select('n.emp_code, n.fname, n.lname,n.comp_name,n.password,   bmcm.role') // Select fields you need
                ->from('new_emp_master as n') // Alias for new_emp_master table
                ->join('bmcm', 'bmcm.emp_code = n.emp_code', 'left') // Define the join condition
                ->where('n.emp_code', $bmid)
                //->where( 'bmcm.role !=', NULL )
                ->get()
                ->getRow();
            // Fetch and return a single row as an object
        }
        return false;
        // Return false if the $bmid is invalid
    }


    public function insertLoginData($data)
    {
        return $this->db->table('login_sessions')->insert($data);
    }

    public function addBranchOrClusterToEmp($data, $emp_code, $branch_id, $cluster_id, $zone_id)
    {
        $db2 = \Config\Database::connect('default');

        $db2->transStart();

        // Prepare query to check if emp_code already exists in the table with optional branch and cluster filters
        $builder = $db2->table('branchesmapped')
            ->where('emp_code', $emp_code);

        if ($branch_id > 0) {
            $builder->where('branch_id', $branch_id);
        }
        if ($cluster_id > 0) {
            $builder->where('cluster_id', $cluster_id);
        }
        if ($zone_id > 0) {
            $builder->where('zone_id', $zone_id);
        }

        $existingRecord = $builder->get()->getRowArray();

        if ($existingRecord) {
            // If emp_code exists, perform an update
            $builder = $db2->table('branchesmapped')->where('emp_code', $emp_code);

            if ($branch_id > 0) {
                $builder->where('branch_id', $branch_id);
            }
            if ($cluster_id > 0) {
                $builder->where('cluster_id', $cluster_id);
            }
            if ($zone_id > 0) {
                $builder->where('zone_id', $zone_id);
            }

            log_message('debug', 'Attempting to add branch/cluster assignment in model for emp_code: ' . $emp_code);

            if (!$builder->update($data)) {
                $error = $db2->error();
                log_message('error', 'Database error during update in branchesmapped: ' . json_encode($error));
                $db2->transRollback();
                return false;
            }
        } else {

            log_message('debug', 'Entered addBranchOrClusterToEmp for emp_code: ' . $emp_code);

            // If emp_code does not exist, perform an insert
            if (!$db2->table('branchesmapped')->insert($data)) {
                $error = $db2->error();
                log_message('error', 'Failed to insert data into branchesmapped table: ' . print_r($error, true));
                $db2->transRollback();
                return false;
            }
        }

        $db2->transComplete();
        if (!$db2->transStatus()) {
            log_message('error', 'Failed transaction for emp_code: ' . $emp_code);
        }

        return $db2->transStatus();
    }

    public function getAreaDetailsById($area_id)
    {
        $db2 = \Config\Database::connect('default');
        return $db2->table('area a')
            ->select('a.*')
            ->where('a.status', 'A')
            ->where('a.id', $area_id)
            ->get()
            ->getResultArray();
    }

    // public function getBranchDetailsById($branch_id){
    //     return $this->select('a.*, c.cluster, cb.cluster_id, z.zone, cz.zone_id')
    //     ->from('branches a') // Alias for asset table
    //     ->join('cluster_branch_map as cb', 'cb.branch_id = a.cluster_id', 'left')
    //     ->join('cluster_zone_map as cz', 'cz.cluster_id = cb.cluster_id', 'left')
    //     ->join('clusters as c', 'c.cluster_id = cb.cluster_id', 'left')
    //     ->join('zones as z', 'z.z_id = cz.zone_id', 'left')
    //     //->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
    //     ->where('a.status', 'A')
    //     ->where('a.branch_id', $branch_id) // Group by asset id
    //     ->get()
    //     ->getResultArray();
    // }

    public function getBranchDetailsById($branch_id)
    {
        $db2 = \Config\Database::connect('default');
        return $db2->table('branches a')
            ->select('a.*, c.cluster, cb.cluster_id, z.zone, cz.zone_id')
            ->join('cluster_branch_map cb', 'cb.branch_id = a.branch_id', 'left') // Fixed the join condition
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->where('a.status', 'A')
            ->where('a.branch_id', $branch_id)
            ->get()
            ->getRowArray(); // Use getRowArray() if expecting a single row
    }

    public function getBranchDetailsById_fz($branch_id)
    {


        $db2 = \Config\Database::connect('default');

        return $db2->table('user_map a')
            ->select('a.*')
            ->where("FIND_IN_SET('$branch_id', a.branches)", null, false) // ✅ Use FIND_IN_SET for CSV search
            //->where("FIND_IN_SET('$cluster_id', a.cluster)", null, false)

            ->get()
            ->getRowArray();
    }

    public function getclusterId($branch_id)
    {

        $db2 = \Config\Database::connect('default');

        return $db2->table('clusters a')
            ->select('a.cluster_id')
            ->where("FIND_IN_SET('$branch_id', a.branches)", null, false) // ✅ Use FIND_IN_SET for CSV search
            ->get()
            ->getRowArray();
    }
    public function getClusterDetailsById($cluster_id)
    {
        $db2 = \Config\Database::connect('default');
        return $db2->table('clusters c')
            ->select('c.cluster, c.cluster_id, z.zone, cz.zone_id')
            ->join('cluster_zone_map cz', 'cz.cluster_id = c.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->where('c.cluster_id', $cluster_id)
            ->where('c.status', 'A')
            ->get()
            ->getRowArray();
    }

    public function getZoneDetailsById($zone_id)
    {
        $db2 = \Config\Database::connect('default');
        return $db2->table('zones z')
            ->select('z.zone, z.z_id as zone_id,   ')
            ->where('z.z_id', $zone_id)
            ->where('z.status', 'A')
            ->get()
            ->getRowArray();
    }

    public function getUserBranchList($user, $role)
    {
        $db2 = \Config\Database::connect('default');
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

    public function addRoleToEmp($data, $emp_code)
    {
        $this->db->transStart();

        // Check if emp_code already exists in the table
        $existingRecord = $this->db->table('bmcm')
            ->where('emp_code', $emp_code)
            ->get()
            ->getRowArray();

        if ($existingRecord) {
            // If emp_code exists, perform an update
            if (!$this->db->table('bmcm')->where('emp_code', $emp_code)->update($data)) {
                $error = $this->db->error();
                log_message('error', 'Failed to update data in bmcm table: ' . print_r($error, true));
                $this->db->transRollback();
                return false;
            }
        } else {
            // If emp_code does not exist, perform an insert
            if (!$this->db->table('bmcm')->insert($data)) {
                $error = $this->db->error();
                log_message('error', 'Failed to insert data into bmcm table: ' . print_r($error, true));
                $this->db->transRollback();
                return false;
            }
        }

        $this->db->transComplete();
        return $this->db->transStatus(); // Returns true if transaction succeeds, false if not
    }

    public function addAreaToEmp($data, $emp_code, $area_id)
    {

        $db2 = \Config\Database::connect('default');
        $db2->transStart();
        // Check if emp_code already exists in the empareamap table
        $existingRecord = $db2->table('empareamap')
            ->where('emp_code', $emp_code)
            ->where('area_id', $area_id) // Check for area_id as well
            ->get()
            ->getRowArray();

        if ($existingRecord) {
            // If emp_code and area_id exist, perform an update
            if (!$db2->table('empareamap')->where('emp_code', $emp_code)->where('area_id', $area_id)->update($data)) {
                $error = $db2->error();
                log_message('error', 'Failed to update data in empareamap table: ' . print_r($error, true));
                $db2->transRollback();
                return false;
            }
        } else {
            // If emp_code does not exist, perform an insert into empareamap
            if (!$db2->table('empareamap')->insert($data)) {
                $error = $db2->error();
                log_message('error', 'Failed to insert data into empareamap table: ' . print_r($error, true));
                $db2->transRollback();
                return false;
            }
        }

        $db2->transComplete();
        return $db2->transStatus(); // Returns true if transaction succeeds, false if not
    }


    // public function addUser($data, $emp_code) {
    //     $this->db->transStart();

    //     // Test inserting without transaction to see if it works
    //     if (!$this->db->table('new_emp_master')->insert($data)) {
    //         $error = $this->db->error();
    //         log_message('error', 'Failed to insert data into new_emp_master table: ' . print_r($error, true));
    //         return false; // This will help identify if insert fails
    //     }

    //     $this->db->transComplete();
    //     return true;

    // }

    public function addUser($data, $emp_code)
    {
        try {
            $this->db->transStart(); // Start the transaction

            // Validate input data
            if (empty($data['emp_code'])) {
                log_message('error', 'Employee name or code is missing.');
                return ['status' => false, 'message' => 'Missing required fields'];
            }

            // Check if emp_code exists (optional if unique constraint exists in DB)
            $existingEmpCode = $this->db->table('new_emp_master')
                ->where('emp_code', $emp_code)
                ->get()
                ->getRowArray();

            if ($existingEmpCode) {
                log_message('error', 'Employee code already exists: ' . $emp_code);
                return ['status' => false, 'message' => 'Employee code already exists'];
            }

            // Insert new employee record
            if (!$this->db->table('new_emp_master')->insert($data)) {
                $error = $this->db->error();
                log_message('error', 'Failed to insert data into new_emp_master table: ' . print_r($error, true));
                return ['status' => false, 'message' => 'Insertion failed'];
            }

            $this->db->transComplete(); // Complete the transaction
            return ['status' => true, 'message' => 'User added successfully'];
        } catch (\Exception $e) {
            $this->db->transRollback(); // Rollback on error
            log_message('error', 'Error during user addition: ' . $e->getMessage());
            return ['status' => false, 'message' => 'An error occurred'];
        }
    }





    public function editUser($data, $bmid)
    {
        if (is_array($data) && $bmid > 0) {

            return $this->db->table('new_emp_master')
                ->set($data)
                ->where('emp_code', $bmid)
                ->update();
        }
        return false;
    }

    public function getUserById($id)
    {
        return $this->db->table('new_emp_master')
            ->where('id', $id)
            ->get()
            ->getRowArray();
        // Returns a single user as an associative array
    }

    public function removeBranchFromCluster($cluster_id, $branch_id)
    {
        $this->db->transStart();

        // Prepare query to check if branch_id already exists in the table with optional cluster filter
        $builder = $this->db->table('cluster_branch_map')
            ->where('branch_id', $branch_id);

        if ($cluster_id > 0) {
            $builder->where('cluster_id', $cluster_id);
        }

        // Perform delete operation
        if (!$builder->delete()) {
            $error = $this->db->error();
            log_message('error', 'Failed to delete data from cluster_branch_map table: ' . print_r($error, true));
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();
        return $this->db->transStatus(); // Returns true if transaction succeeds, false if not
    }


    public function deleteBranchOrClusterFromEmp($emp_code, $branch_id, $cluster_id, $zone_id)
    {
        $this->db->transStart();

        // Prepare query to check if emp_code already exists in the table with optional branch and cluster filters
        $builder = $this->db->table('branchesmapped')
            ->where('emp_code', $emp_code);

        if ($branch_id > 0) {
            $builder->where('branch_id', $branch_id);
        }
        if ($cluster_id > 0) {
            $builder->where('cluster_id', $cluster_id);
        }
        if ($zone_id > 0) {
            $builder->where('zone_id', $zone_id);
        }

        // Perform delete operation
        if (!$builder->delete()) {
            $error = $this->db->error();
            log_message('error', 'Failed to delete data from branchesmapped table: ' . print_r($error, true));
            $this->db->transRollback();
            return false;
        }

        $this->db->transComplete();
        return $this->db->transStatus(); // Returns true if transaction succeeds, false if not
    }
}

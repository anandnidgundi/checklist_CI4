<?php

namespace App\Models;

use CodeIgniter\Model;

class DieselConsumptionModel extends Model
{
    protected $table = 'diesel_consumption';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'branch_id',
        'cluster_id',
        'zone_id',
        'consumption_date',
        'power_shutdown',
        'is_power_shutdown',
        'is_generator_testing',
        'testing_time_duration',
        'testing_diesel_consumed',
        'diesel_consumed',
        'avg_consumption',
        'closing_stock',
        'remarks',
        'createdBy',
        'createdDTM'
    ];

    // public function getDieselConsumptionList($role, $emp_code, $month)
    // {
    //     log_message('info', 'DieselConsumptionModel::getDieselConsumptionList called with month: {month}', ['month' => $month]);

    //     $builder = $this->db->table('diesel_consumption as dc')
    //         ->select('dc.*, bm.branch, bm.cluster_id, c.cluster, z.zone')
    //         ->join('branchesmapped as bm', 'bm.branch_id = dc.branch_id', 'left')
    //         ->join('new_emp_master n', 'n.emp_code = dc.createdBy', 'left')
    //         ->join('cluster_branch_map cb', 'bm.branch = cb.branch_id', 'left')
    //         ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
    //         ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
    //         ->join('zones z', 'z.z_id = cz.zone_id', 'left')
    //         ->where('DATE_FORMAT(dc.consumption_date, "%Y-%m")', $month);

    //     // Apply condition only if the role is not 'SUPER_ADMIN'
    //     if ($role != 'SUPER_ADMIN') {
    //         $builder->where('bm.emp_code', $emp_code);
    //     }

    //     $query = $builder->get();
    //     return $query->getResultArray();
    // }

    public function getDieselConsumptionList1($role, $emp_code, $month)
    {
        $db2 = \Config\Database::connect('secondary'); // For emp and branches // travelapp

        $mainDB = \Config\Database::connect('default'); // For task-related tables

        $builder = $this->db->table('diesel_consumption as dc')
            ->select('dc.*, bm.branch, bm.cluster_id, c.cluster, z.zone')
            ->join('user_map as bm', 'bm.branch_id = dc.branch_id', 'left')
            ->join('new_emp_master n', 'n.emp_code = dc.createdBy', 'left')
            ->join('cluster_branch_map cb', 'bm.branch = cb.branch_id', 'left')
            ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
            ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
            ->join('zones z', 'z.z_id = cz.zone_id', 'left')
            ->where('DATE_FORMAT(dc.consumption_date, "%Y-%m")', $month);

        // Apply condition only if the role is not 'SUPER_ADMIN'
        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $emp_code);
        }
        $query = $builder->get();
        $result = $query->getResultArray();
        // final result should include attached files from files table where diesel_id = dc.id
        foreach ($result as $key => $value) {
            $dieselId = $value['id'];
            $filesQuery = $this->db->table('files')
                ->select('file_name')
                ->where('diesel_id', $dieselId)
                ->get();
            $result[$key]['files'] = $filesQuery->getResultArray();
        }
        return $result;
    }


    public function getDieselConsumptionList($role, $emp_code, $month)
    {
        $db2 = \Config\Database::connect('secondary'); // secondary DB
        $mainDB = \Config\Database::connect('default'); // main DB

        // Step 1: Get diesel consumption and related info (excluding emp details)
        $builder = $this->db->table('diesel_consumption as dc')
            ->select('dc.*, bm.branches, bm.cluster, bm.cluster, bm.zone, dc.createdBy')
            ->join('user_map as bm', 'FIND_IN_SET(dc.branch_id, bm.branches)', 'left')
            ->where('DATE_FORMAT(dc.consumption_date, "%Y-%m")', $month);

        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $emp_code);
        }
        $builder->groupBy('dc.id');
        $query = $builder->get();
        $result = $query->getResultArray();

        // Step 2: Collect all unique createdBy emp_codes
        $empCodes = array_column($result, 'createdBy');
        $empCodes = array_filter(array_unique($empCodes));

        // Step 3: Fetch employee info from secondary DB (new_emp_master)
        $empData = [];
        if (!empty($empCodes)) {
            $empRows = $db2->table('new_emp_master')
                ->select('emp_code, comp_name, designation_name, dept_name') // select whatever fields you need
                ->whereIn('emp_code', $empCodes)
                ->get()
                ->getResultArray();

            foreach ($empRows as $emp) {
                $empData[$emp['emp_code']] = $emp;
            }
        }

        // Step 4: Attach files and emp info
        foreach ($result as $key => $value) {
            $dieselId = $value['id'];

            // Attach files
            $filesQuery = $this->db->table('files')
                ->select('file_name')
                ->where('diesel_id', $dieselId)
                ->get();
            $result[$key]['files'] = $filesQuery->getResultArray();

            // Attach emp info from secondary DB
            $empCode = $value['createdBy'];
            $result[$key]['employee'] = $empData[$empCode] ?? null;
        }

        return $result;
    }

    public function getDieselConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate)
    {
 
        $db2 = \Config\Database::connect('secondary'); // secondary DB
 
        log_message('error', 'DieselConsumptionModel::getDieselConsumptionAdminList called with zone_id: {zone_id}, selectedCluster: {selectedCluster}, selectedBranch: {selectedBranch}, selectedMonth: {selectedMonth}', [
            'zone_id' => $zone_id,
            'selectedCluster' => $selectedCluster,
            'selectedBranch' => $selectedBranch,
            'selectedMonth' => $selectedMonth
        ]);
 
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
 
        $builder = $this->db->table('diesel_consumption as pc')
            ->select('pc.*,  pc.createdBy')
            ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth)
            ->orderBy('pc.createdDTM', 'DESC');
        // Apply date filter if provided
        if (!empty($selectedDate)) {
            $builder->where('DATE(pc.consumption_date)', $selectedDate);
        }
        // Apply filters
        if ($selectedCluster > 0) {
            $builder->where('pc.cluster_id', $selectedCluster);
        } else if ($selectedBranch > 0) {
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
            $dieselId = $value['id'];
            $filesQuery = $this->db->table('files')
                ->select('file_name')
                ->where('diesel_id', $dieselId)
                ->get();
            $result[$key]['files'] = $filesQuery->getResultArray();
        }
       
        return $result;
    }


    public function getDieselConsumptionAdminListforbranch($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth, $selectedDate)
    {
 
        $db2 = \Config\Database::connect('secondary'); // secondary DB
 
        log_message('error', 'DieselConsumptionModel::getDieselConsumptionAdminList called with zone_id: {zone_id}, selectedCluster: {selectedCluster}, selectedBranch: {selectedBranch}, selectedMonth: {selectedMonth}', [
            'zone_id' => $zone_id,
            'selectedCluster' => $selectedCluster,
            'selectedBranch' => $selectedBranch,
            'selectedMonth' => $selectedMonth
        ]);
 
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
 
        $builder = $this->db->table('diesel_consumption as pc')
            ->select('pc.*,  pc.createdBy')
            ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth);
        // Apply date filter if provided
        if (!empty($selectedDate)) {
            $builder->where('DATE(pc.consumption_date)', $selectedDate);
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
            $dieselId = $value['id'];
            $filesQuery = $this->db->table('files')
                ->select('file_name')
                ->where('diesel_id', $dieselId)
                ->get();
            $result[$key]['files'] = $filesQuery->getResultArray();
        }
       
        return $result;
    }
    public function getDieselConsumptionAdminList_backup($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth)
    {

        $db2 = \Config\Database::connect('secondary'); // secondary DB

        log_message('error', 'DieselConsumptionModel::getDieselConsumptionAdminList called with zone_id: {zone_id}, selectedCluster: {selectedCluster}, selectedBranch: {selectedBranch}, selectedMonth: {selectedMonth}', [
            'zone_id' => $zone_id,
            'selectedCluster' => $selectedCluster,
            'selectedBranch' => $selectedBranch,
            'selectedMonth' => $selectedMonth
        ]);

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

        $builder = $this->db->table('diesel_consumption as pc')
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
            $dieselId = $value['id'];
            $filesQuery = $this->db->table('files')
                ->select('file_name')
                ->where('diesel_id', $dieselId)
                ->get();
            $result[$key]['files'] = $filesQuery->getResultArray();
        }
        
        return $result;
    }


    // public function getDieselConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth)
    // {
    //     // echo $zone_id;
    //     // echo "\n";
    //     // echo $selectedCluster;
    //     // echo "\n";
    //     // echo $selectedBranch;
    //     // echo "\n";die();
    //     $db2 = \Config\Database::connect('secondary'); // Connect to secondary DB for new_emp_master

    //     $builder = $this->db->table('diesel_consumption as pc')
    //         ->select('pc.*, bm.branches, bm.cluster, pc.createdBy') // Include createdBy for mapping
    //         ->join('user_map as bm', 'FIND_IN_SET(pc.branch_id, bm.branches)', 'left') // CSV join
    //         ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth);

    //     // Apply filters
    //     if ($selectedCluster > 0) {
    //         $builder->where('pc.cluster_id', $selectedCluster);
    //     } elseif ($selectedBranch > 0) {
    //         $builder->where('pc.branch_id', $selectedBranch);
    //     } elseif ($zone_id > 0) {
    //         $builder->where('pc.zone_id', $zone_id);
    //     }

    //     $query = $builder->get();
    //     $result = $query->getResultArray();

    //     // Step 1: Collect all unique createdBy emp_codes
    //     $empCodes = array_column($result, 'createdBy');
    //     $empCodes = array_filter(array_unique($empCodes));

    //     // Step 2: Fetch employee data from secondary DB
    //     $empData = [];
    //     if (!empty($empCodes)) {
    //         $empRows = $db2->table('new_emp_master')
    //             ->select('emp_code, comp_name, designation_name, dept_name')
    //             ->whereIn('emp_code', $empCodes)
    //             ->get()
    //             ->getResultArray();

    //         foreach ($empRows as $emp) {
    //             $empData[$emp['emp_code']] = $emp;
    //         }
    //     }

    //     // Step 3: Attach files and employee info
    //     foreach ($result as $key => $value) {
    //         $dieselId = $value['id'];

    //         // Attach files
    //         $filesQuery = $this->db->table('files')
    //             ->select('file_name')
    //             ->where('diesel_id', $dieselId)
    //             ->get();
    //         $result[$key]['files'] = $filesQuery->getResultArray();

    //         // Attach employee info from db2
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





    //getDieselConsumptionById($id)
    public function getDieselConsumptionById($id)
    {
        $builder = $this->db->table('diesel_consumption as dc')
            ->select('dc.*, bm.branch, bm.cluster_id, cl.cluster, a.area')
            ->join('branchesmapped as bm', 'bm.branch_id = dc.branch_id', 'left')
            ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
            ->join('area as a', 'cl.area_id = a.id', 'left')
            ->where('dc.id', $id);

        $query = $builder->get();
        return $query->getRowArray();
    }




    public function getUserBranchList($user, $role)
    {
        $builder = $this->db->table('branchesmapped as bm')
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
}

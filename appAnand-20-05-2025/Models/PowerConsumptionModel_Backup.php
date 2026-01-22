<?php

namespace App\Models;

use CodeIgniter\Model;

class PowerConsumptionModel extends Model
{
    protected $table = 'power_consumption';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'branch_id',
        'cluster_id',
        'zone_id',
        'morning_units',
        'consumption_date',
        'night_units',
        'total_consumption',
        'remarks',
        'createdBy',
        'createdDTM'
    ];

    

    public function getPowerConsumptionList($role, $emp_code, $month)
{
    $db2 = \Config\Database::connect('secondary'); // secondary DB

    $builder = $this->db->table('power_consumption as pc')
        ->select('pc.*, bm.branches, bm.cluster, bm.zone, pc.createdBy') // include createdBy for mapping
        ->join('user_map as bm', 'FIND_IN_SET(pc.branch_id, bm.branches)', 'left')
        ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $month);

    // Apply condition only if the role is not 'SUPER_ADMIN'
    if ($role != 'SUPER_ADMIN') {
        $builder->where('bm.emp_code', $emp_code);
    }

    $query = $builder->get();
    $result = $query->getResultArray();

    // Step 1: Collect unique emp_codes
    $empCodes = array_column($result, 'createdBy');
    $empCodes = array_filter(array_unique($empCodes));

    // Step 2: Fetch employee data from secondary DB
    $empData = [];
    if (!empty($empCodes)) {
        $empRows = $db2->table('new_emp_master')
            ->select('emp_code, comp_name, designation_name, dept_name') // add more fields if needed
            ->whereIn('emp_code', $empCodes)
            ->get()
            ->getResultArray();

        foreach ($empRows as $emp) {
            $empData[$emp['emp_code']] = $emp;
        }
    }

    // Step 3: Attach files and employee info
    foreach ($result as $key => $value) {
        $powerId = $value['id'];

        // Attach files
        $filesQuery = $this->db->table('files')
            ->select('file_name')
            ->where('power_id', $powerId)
            ->get();
        $result[$key]['files'] = $filesQuery->getResultArray();

        // Attach emp info from secondary DB
        $empCode = $value['createdBy'];
        $result[$key]['employee'] = $empData[$empCode] ?? null;
    }

    return $result;
}


public function getPowerConsumptionAdminList($role, $emp_code, $zone_id, $selectedCluster, $selectedBranch, $selectedMonth)
{

    // Connect to secondary database
    $db2 = \Config\Database::connect('secondary');

    // Main query builder
    $builder = $this->db->table('power_consumption as pc')
        ->select('pc.*, bm.branches, bm.cluster as user_cluster, pc.createdBy')
        ->join('user_map as bm', 'FIND_IN_SET(pc.branch_id, bm.branches)', 'left')
        ->where('DATE_FORMAT(pc.consumption_date, "%Y-%m")', $selectedMonth);


    // Apply optional filters
    if ($selectedCluster > 0) {
        $builder->where('pc.cluster_id', $selectedCluster);
    }

    if ($selectedBranch > 0) {
        $builder->where('pc.branch_id', $selectedBranch);
    }

    if ($zone_id > 0) {
        $builder->where('pc.zone_id', $zone_id);
    }

    // Fetch data
    $query = $builder->get();
    $result = $query->getResultArray();

    // Collect unique emp_codes from createdBy field
    $empCodes = array_column($result, 'createdBy');
    $empCodes = array_filter(array_unique($empCodes));

    // Fetch employee data from secondary DB
    $empData = [];
    if (!empty($empCodes)) {
        $empRows = $db2->table('new_emp_master')
            ->select('emp_code, comp_name, designation_name, dept_name')
            ->whereIn('emp_code', $empCodes)
            ->get()
            ->getResultArray();

        foreach ($empRows as $emp) {
            $empData[$emp['emp_code']] = $emp;
        }
    }

    // Attach files and employee data
    foreach ($result as $key => $value) {
        $powerId = $value['id'];

        // Attach files
        $filesQuery = $this->db->table('files')
            ->select('file_name')
            ->where('power_id', $powerId)
            ->get();
        $result[$key]['files'] = $filesQuery->getResultArray();

        // Attach employee info
        $empCode = $value['createdBy'];
        $result[$key]['employee'] = $empData[$empCode] ?? null;
    }

    // De-duplicate entries based on power_consumption.id
    $uniqueResults = [];
    $seenIds = [];

    foreach ($result as $item) {
        if (!in_array($item['id'], $seenIds)) {
            $seenIds[] = $item['id'];
            $uniqueResults[] = $item;
        }
    }

    return $uniqueResults;
}



    //getPowerConsumptionById($id)
    public function getPowerConsumptionById($id)
    {
        $builder = $this->db->table('power_consumption as pc')
            ->select('pc.*, bm.branch, bm.cluster_id, cl.cluster, a.area')
            ->join('branchesmapped as bm', 'bm.branch_id = pc.branch_id', 'left')
            ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
            ->join('area as a', 'cl.area_id = a.id', 'left')
            ->where('pc.id', $id);

        $query = $builder->get();
        return $query->getRowArray();
    }

    public function getUserBranchList($user, $role)
    {
        $builder = $this->db->table('branchesmapped as bm')
            ->select('bm.emp_code, bm.branch_id, bm.branch, bm.cluster_id, bm.cluster, cl.area_id, a.area')
            ->join('clust_area_map as cl', 'cl.cluster_id = bm.cluster_id', 'left')
            ->join('area as a', 'cl.area_id = a.id', 'left');
        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $user);
        }
        $query = $builder->get();
        return $query->getResultArray();
    }
}

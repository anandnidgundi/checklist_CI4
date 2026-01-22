<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportsModel extends Model
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

    public function getMorningTaskList($selectedDate, $role, $user) {
        $results = [];
        if (empty($selectedDate)) {
            //log_message('error', 'selectedDate is empty');
            return $results;
        } else {
            //log_message('error', 'selectedDate: ' . $selectedDate);
        }

        $usersClusterList = $this->getCMclusterList($user, $role);
        $clusterList = array_column($usersClusterList, 'cluster_id');
        $branchList = [];
        foreach ($clusterList as $cluster_id) {
            $branchList = array_merge($branchList, $this->getClusterBranchList($cluster_id));
        }
        $branchList = array_column($branchList, 'branch_id');

        if (!empty($branchList)) {
            $this->select('m.*, b.branch as branchName, n.fname, n.lname')
                 ->from('morningtasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->join('branches b', 'b.branch_id = m.branch', 'left')
                 ->whereIn('m.branch', $branchList)
                 ->where('DATE(m.createdDTM)', $selectedDate)
                 ->orderBy('m.createdDTM', 'desc')
                 ->groupBy('m.mid');

            $results = $this->get()->getResultArray();
            //log_message('error', 'Query result: ' . print_r($results, true));
        } else {
            //log_message('error', 'No branches found for the user');
        }
        return $results;
    }

    public function getNightTaskList($selectedDate, $role, $user) {
        $results = [];
        if (empty($selectedDate)) {
            //log_message('error', 'selectedDate is empty');
            return $results;
        } else {
            //log_message('error', 'selectedDate: ' . $selectedDate);
        }

        $usersClusterList = $this->getCMclusterList($user, $role);
        $clusterList = array_column($usersClusterList, 'cluster_id');
        $branchList = [];
        foreach ($clusterList as $cluster_id) {
            $branchList = array_merge($branchList, $this->getClusterBranchList($cluster_id));
        }
        $branchList = array_column($branchList, 'branch_id');
        //log_message('error', 'Query result: ' . print_r($branchList, true));
        if (!empty($branchList)) {
            $this->select('m.*, b.branch as branchName, n.fname, n.lname')
                 ->from('nighttasks m')
                 ->join('new_emp_master n', 'n.emp_code = m.created_by', 'left')
                 ->join('branches b', 'b.branch_id = m.branch', 'left')
                 ->whereIn('m.branch', $branchList)
                 ->where('DATE(m.createdDTM)', $selectedDate)
                 ->orderBy('m.createdDTM', 'desc')
                 ->groupBy('m.nid');

            $results = $this->get()->getResultArray();
            //log_message('error', 'Query result: ' . print_r($results, true));
        } else {
            //log_message('error', 'No branches found for the user');
        }
        return $results;
    }

    public function getCMclusterList($user, $role) {
        $builder = $this->db->table('branchesmapped as bm')
            ->select('bm.cluster_id, bm.cluster');
        if ($role != 'SUPER_ADMIN') {
            $builder->where('bm.emp_code', $user);
        }

        $query = $builder->get();
        return $query->getResultArray();
    }

    public function getClusterBranchList($cluster_id){
        $builder = $this->db->table('cluster_branch_map as cb')
            ->select('b.branch, cb.branch_id')
            ->join('branches as b', 'b.branch_id = cb.branch_id', 'left');
        $builder->where('cb.cluster_id', $cluster_id);
        $query = $builder->get();
        $result = $query->getResultArray();
        return $result;
    }

}
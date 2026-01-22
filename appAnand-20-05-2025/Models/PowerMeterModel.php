<?php

namespace App\Models;

use CodeIgniter\Model;

class PowerMeterModel extends Model
{
    protected $table = 'power_meter_master';
    protected $primaryKey = 'pm_id';
    protected $allowedFields = ['branch_id', 'meter_name', 'meter_number', 'load_kw', 'status'];

    protected $useAutoIncrement = true;
    protected $returnType = 'array';


    public function getBranchById($branchId)
    {
        // fetch branches table from secondary database 
        $db2 = \Config\Database::connect('secondary');
        $builder = $db2->table('branches');
        $builder->where('branch_id', $branchId);
        $query = $builder->get();
        if ($query->getNumRows() > 0) {
            return $query->getRow();
        } else {
            return null; // or handle the case when the branch is not found
        }
    }

    public function getDetailsByBranchIdAndMeterNumber($branchId, $meterNumber)
    {
        $builder = $this->db->table($this->table);
        $builder->where('branch_id', $branchId);
        $builder->where('meter_number', $meterNumber);
        $query = $builder->get();
        return $query->getRow();
    }

    public function getNextSerialNumber($branchId)
    {
        // Get branch details from secondary database
        $db2 = \Config\Database::connect('secondary');
        $branchBuilder = $db2->table('branches');
        $branchBuilder->where('id', $branchId);
        $branchQuery = $branchBuilder->get();
        $branchSysField = $branchQuery->getRow()->SysField;

        // Get power meter entries for this branch
        $builder = $this->db->table($this->table);
        $builder->where('branch_id', $branchId);
        $builder->orderBy('pm_id', 'DESC');
        $query = $builder->get();

        $count = $query->getNumRows();
        $nextNumber = $count > 0 ? $count + 1 : 1;

        return $branchSysField . '_' . $nextNumber;
    }
}

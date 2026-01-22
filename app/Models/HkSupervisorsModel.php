<?php

namespace App\Models;

use CodeIgniter\Model;

class HkSupervisorsModel extends Model
{
     protected $table = 'user_map';
     protected $primaryKey = 'id';
     protected $returnType = 'array';
     protected $useSoftDeletes = false;
     protected $allowedFields = ['emp_code', 'zone', 'role', 'branches', 'created_on', 'created_by']; // removed 'cluster' — cluster is no longer set by supervisor endpoints

     protected $useTimestamps = false;

     public function findByEmpCode($empCode)
     {
          return $this->where('emp_code', $empCode)->findAll();
     }

     public function normalizeBranchesField($branches)
     {
          // Accept array or comma-separated string and return comma-separated string
          if (is_array($branches)) {
               $branches = array_map('trim', $branches);
               return implode(',', array_filter($branches, function ($v) {
                    return $v !== null && $v !== '';
               }));
          }
          return is_null($branches) ? '' : trim($branches);
     }
}

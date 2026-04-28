<?php

namespace App\Models;

use CodeIgniter\Model;

class ProfileModel extends Model
{
     protected $DBGroup          = 'secondary';
     protected $table            = 'new_emp_master';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';
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
          'check_list',
     ];

     protected bool $allowEmptyInserts = false;
     protected $useTimestamps = false;

     public function getUserProfiles($bmid)
     {
          if ($bmid > 0) {
               return $this->select('n.emp_code, n.fname, n.lname, n.comp_name, n.password, n.mobile, bmcm.role')
                    ->from('new_emp_master as n')
                    ->join('bmcm', 'bmcm.emp_code = n.emp_code', 'left')
                    ->where('n.emp_code', $bmid)
                    ->get()
                    ->getRow();
          }

          return false;
     }
}

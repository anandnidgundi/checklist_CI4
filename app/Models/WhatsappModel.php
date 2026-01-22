<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappModel extends Model
{
     protected $table = 'whatsapp_messages_log';
     protected $primaryKey = 'id';
     protected $useAutoIncrement = true;
     protected $returnType = 'array';
     protected $useSoftDeletes = false;
     protected $allowedFields = ['mobile', 'emp_code', 'message', 'sent_dtm', 'hkr_id', 'remark'];
     protected $useTimestamps = false;

     public function logMessage($data)
     {
          return $this->insert($data);
     }


     public function getMessagesByRequirementId($hkr_id)
     {
          return $this->where('hkr_id', $hkr_id)->findAll();
     }

     public function getAdminDetails()
     {
          $db2 = \Config\Database::connect('secondary');

          // Fetch admin details from the secondary database, find users with role 'ADMIN' from 'bmcm' table, and join with 'new_emp_master' table to get mobile numbers and details
          $builder = $db2->table('bmcm as b')
               ->select('b.emp_code, e.mobile, e.comp_name')
               ->join('new_emp_master as e', 'b.emp_code = e.emp_code', 'left')
               ->where('b.role', 'ADMIN')
               ->where('e.active', 'Active')
               ->where('e.disabled', 'N');
          return $builder->get()->getResultArray();
     }

     public function getBMDetails($emp_code)
     {
          $db2 = \Config\Database::connect('secondary');
          // Fetch BM details from the secondary database, find users with role 'BM' for the given branch_id from 'bmcm' table, and join with 'new_emp_master' table to get mobile numbers and details
          $builder = $db2->table('new_emp_master as e')
               ->select('b.emp_code, e.mobile, e.comp_name')
               ->where('e.emp_code', $emp_code)
               ->where('e.active', 'Active')
               ->where('e.disabled !=', 'N')
               ->where('e.mobile IS NOT NULL', null, false);
          return $builder->get()->getRowArray();
     }
}

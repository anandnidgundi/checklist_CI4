<?php

namespace App\Models;

use CodeIgniter\Model;

class PestControlModel extends Model
{
     protected $table            = 'pest_control';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;

     protected $returnType       = 'array';
     protected $useSoftDeletes   = false;

     protected $allowedFields = [
          'branch_id',
          'branch_name',
          'branch_manager_name',
          'branch_manager_emp_code',
          'branch_manager_mobile',
          'branch_manager_email',
          'vendor_name',
          'vendor_id',
          'date_of_visit',
          'visit_time',
          'chemical_spray',
          'rat_pads',
          'gel_placed',
          'updated_at',
          'audited_by_name',
          'audited_by_emp_code',
          'audited_date',
          'remarks',
     ];

     protected $useTimestamps = true;
     protected $createdField  = 'created_at';
     protected $updatedField  = 'updated_at';

     protected $dateFormat = 'datetime';

     protected $validationRules = [
          'branch_id'           => 'required|max_length[10]',
          'date_of_visit'       => 'required|valid_date',
          'visit_time'          => 'required',
          'chemical_spray'      => 'required|in_list[YES,NO]',
          'gel_placed'          => 'required|in_list[YES,NO]',
          'audited_by_name'     => 'required|max_length[200]',
          'audited_by_emp_code' => 'required|max_length[20]',
     ];

     protected $validationMessages = [];

     protected $skipValidation      = false;
     protected $cleanValidationRules = true;

     public function getFiles($id)
     {
          $db = \Config\Database::connect();
          $files = $db->table('files')
               ->where('pest_control_id', $id)
               ->where('service_id', 'PEST_CONTROL')
               ->get()
               ->getResultArray();
          return $files;
     }
}

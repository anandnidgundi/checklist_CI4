<?php

namespace App\Models;

use CodeIgniter\Model;

class BmwModel extends Model
{
     protected $table            = 'bmw';
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
          'bmw_certificate_from_branch',
          'bmw_certificate_expiry',
          'date_of_visit',
          'visit_time',
          'was_cleaning_done_bm',
          'audited_by_name',
          'audited_by_emp_code',
          'audited_date',
          'remarks',
          'created_at',
          'updated_at',
          'total_visits_done'
     ];

     protected $useTimestamps = true;
     protected $createdField  = 'created_at';
     protected $updatedField  = 'updated_at';

     protected $dateFormat = 'datetime';

     protected $validationRules = [
          'branch_id'             => 'required|max_length[10]',
          'date_of_visit'         => 'required|valid_date',
          'visit_time'            => 'required',
          'audited_by_name'       => 'required|max_length[200]',
          'audited_by_emp_code'   => 'required|max_length[20]',
     ];

     protected $validationMessages = [];
     protected $skipValidation = false;
     protected $cleanValidationRules = true;

     public function getFiles($id)
     {
          // get files data from files table with ac_form_id as $id
          $files = $this->db->table('files')->where('bmw_form_id', $id)->get()->getResultArray();
          return $files;
     }
}

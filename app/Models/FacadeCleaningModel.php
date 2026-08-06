<?php

namespace App\Models;

use CodeIgniter\Model;

class FacadeCleaningModel extends Model
{
     protected $table            = 'facade_cleaning';
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
          'date_of_cleaning',
          'next_date_of_cleaning',
          'was_cleaning_done_bm',
          'was_the_front_facade_cleaned',
          'was_the_side_facade_cleaned',
          'were_the_signage_board_cleaned',
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
          'branch_id'                     => 'required|max_length[10]',
          'date_of_cleaning'              => 'required|valid_date',
          'next_date_of_cleaning'         => 'required|valid_date',
          'was_cleaning_done_bm'          => 'required|in_list[YES,NO]',
          'was_the_side_facade_cleaned'   => 'required|in_list[YES,NO]',
          'audited_by_name'               => 'required|max_length[200]',
          'audited_by_emp_code'           => 'required|max_length[20]',
     ];

     protected $validationMessages = [];

     protected $skipValidation      = false;
     protected $cleanValidationRules = true;

     public function getFiles($id)
     {
          return $this->db->table('files')
               ->where('facade_cleaning_id', $id)
               ->where('service_id', 'FACADE_CLEANING')
               ->get()
               ->getResultArray();
     }
}

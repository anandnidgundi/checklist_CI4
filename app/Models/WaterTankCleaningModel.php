<?php

namespace App\Models;

use CodeIgniter\Model;

class WaterTankCleaningModel extends Model
{
     protected $table            = 'water_tank_cleaning';
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
          'visit_time',
          'was_cleaning_done_bm',
          'external_surface_cleaning_done',
          'sluge_removal_scrub_done',
          'high_pressure_cleaning',
          'vaccum_cleaning',
          'anti_bacteria_spray',
          'uv_radiation',
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
          'branch_id'                 => 'required|max_length[10]',
          'date_of_cleaning'          => 'required|valid_date',
          'visit_time'                => 'required',
          'was_cleaning_done_bm'      => 'required|in_list[YES,NO]',
          'sluge_removal_scrub_done'  => 'required|in_list[YES,NO]',
          'audited_by_name'           => 'required|max_length[200]',
          'audited_by_emp_code'       => 'required|max_length[20]',
     ];

     protected $validationMessages = [];

     protected $skipValidation = false;
     protected $cleanValidationRules = true;

     public function getFiles($id)
     {
          // get files data from files table with ac_form_id as $id
          $files = $this->db->table('files')->where('water_tank_cleaning_id', $id)->get()->getResultArray();
          return $files;
     }
}

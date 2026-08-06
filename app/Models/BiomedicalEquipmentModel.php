<?php

namespace App\Models;

use CodeIgniter\Model;

class BiomedicalEquipmentModel extends Model
{
     protected $table            = 'biomedical_equipment';
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
          'modality',
          'modality_id',
          'date_of_visit',
          'visit_time',
          'scheduled_pms_date',
          'actual_pms_date',
          'service_in_presense_radiographer',
          'radiographer_name',
          'radiographer_bmid',
          'next_scheduled_pms_date',
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
          'branch_id'                        => 'required|max_length[10]',
          'date_of_visit'                    => 'required|valid_date',
          'visit_time'                       => 'required',
          'scheduled_pms_date'               => 'required|valid_date',
          'audited_by_name'                  => 'required|max_length[200]',
          'audited_by_emp_code'              => 'required|max_length[20]',
     ];

     protected $validationMessages = [];

     protected $skipValidation = false;
     protected $cleanValidationRules = true;

     public function getFiles($id)
     {
          // get files data from files table with biomedical_equip_form_id as $id
          $files = $this->db->table('files')->where('biomedical_equip_form_id', $id)->get()->getResultArray();
          return $files;
     }
}

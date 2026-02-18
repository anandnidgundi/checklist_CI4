<?php

namespace App\Models;

use CodeIgniter\Model;

class FormSubSectionsModel extends Model
{
     protected $table            = 'form_sub_sections';
     protected $primaryKey       = 'sub_section_id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';
     protected $useSoftDeletes   = false;
     protected $protectFields    = true;

     protected $allowedFields = [
          'form_id',
          'section_id',
          'sub_section_name',
          'sub_section_icon',
          'status',
          'created_dtm',
     ];

     protected bool $allowEmptyInserts = false;

     protected $useTimestamps = false;
     protected $dateFormat    = 'datetime';

     protected $validationRules = [
          'form_id' => 'required|integer',
          'section_id' => 'required|integer',
          'sub_section_name' => 'required|max_length[100]',
          'sub_section_icon' => 'permit_empty|max_length[50]',
          'status' => 'required|in_list[A,I]',
     ];
     protected $validationMessages = [];
     protected $skipValidation = false;
     protected $cleanValidationRules = true;

     protected $allowCallbacks = true;
     protected $beforeInsert = ['setCreatedDtm'];
     protected $beforeUpdate = [];

     protected function setCreatedDtm(array $data): array
     {
          if (!isset($data['data'])) {
               return $data;
          }

          if (empty($data['data']['created_dtm'])) {
               $data['data']['created_dtm'] = date('Y-m-d H:i:s');
          }

          return $data;
     }
}

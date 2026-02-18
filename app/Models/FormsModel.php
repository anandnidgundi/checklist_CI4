<?php

namespace App\Models;

use CodeIgniter\Model;

class FormsModel extends Model
{
     protected $table            = 'vdc_forms';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';
     protected $useSoftDeletes   = false;
     protected $protectFields    = true;

     protected $allowedFields = [
          'form_name',
          'form_description',
          'created_dtm',
          'created_by',
          'status',
     ];

     protected bool $allowEmptyInserts = false;

     // Dates
     protected $useTimestamps = false;
     protected $dateFormat    = 'datetime';

     // Validation
     protected $validationRules = [
          'form_name' => 'required|max_length[50]',
          'form_description' => 'permit_empty',
          'created_dtm' => 'permit_empty|valid_date[Y-m-d H:i:s]',
          'created_by' => 'required|integer',
          'status' => 'required|in_list[A,I]',
     ];
     protected $validationMessages = [];
     protected $skipValidation = false;
     protected $cleanValidationRules = true;

     // Callbacks
     protected $allowCallbacks = true;
     protected $beforeInsert   = ['setCreatedDtm'];
     protected $beforeUpdate   = [];

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

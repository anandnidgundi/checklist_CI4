<?php

namespace App\Models;

use CodeIgniter\Model;

class NewDepartmentModel extends Model
{
     protected $table            = 'new_department';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';
     protected $useSoftDeletes   = false;
     protected $protectFields    = true;

     protected $allowedFields = [
          'dept_name',
          'status',
     ];

     protected bool $allowEmptyInserts = false;

     // Dates
     protected $useTimestamps = false;
     protected $dateFormat    = 'datetime';

     // Validation
     protected $validationRules = [
          'dept_name' => 'required|max_length[50]',
          'status' => 'permit_empty|in_list[A,I]',
     ];
     protected $validationMessages = [];
     protected $skipValidation = false;
     protected $cleanValidationRules = true;

     // Callbacks
     protected $allowCallbacks = true;
     protected $beforeInsert   = ['setDefaultStatus'];
     protected $beforeUpdate   = ['setDefaultStatus'];

     protected function setDefaultStatus(array $data): array
     {
          if (!isset($data['data']) || !is_array($data['data'])) {
               return $data;
          }

          if (!array_key_exists('status', $data['data']) || $data['data']['status'] === '' || $data['data']['status'] === null) {
               $data['data']['status'] = 'A';
          }

          return $data;
     }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class FormSectionsModel extends Model
{
     protected $table = 'form_sections';
     protected $primaryKey = 'section_id';
     protected $useAutoIncrement = true;
     protected $returnType = 'array';

     protected $useSoftDeletes = false;
     protected $protectFields = true;

     protected $allowedFields = [
          'section_name',
          'section_icon',
          'dept_id',
          'form_id',
          'status',
     ];

     protected $useTimestamps = false;

     protected $validationRules = [
          'section_name' => 'required|max_length[100]',
          'section_icon' => 'permit_empty|max_length[50]',
          'dept_id' => 'required|integer',
          'form_id' => 'required|integer',
          'status' => 'required|in_list[A,I]',
     ];

     protected $skipValidation = false;
     protected $cleanValidationRules = true;
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class FormRecordsModel extends Model
{
     protected $table            = 'form_records';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';
     protected $useSoftDeletes   = false;
     protected $protectFields    = true;

     protected $allowedFields = [
          'submission_id',
          'submission_uuid',
          'section_id',
          'sub_section_id',
          'input_id',
          'input_value',
          'dept_id',
          'created_dtm',
          'created_by',
          'updated_dtm',
          'updated_by',
          'form_id',
     ];

     protected bool $allowEmptyInserts = false;
}

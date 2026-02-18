<?php

namespace App\Models;

use CodeIgniter\Model;

class FormInputsModel extends Model
{
     protected $table            = 'form_inputs';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';
     protected $useSoftDeletes   = false;
     protected $protectFields    = true;

     protected $allowedFields = [
          'input_name',
          'input_label',
          'input_icon',
          'input_icon_color',
          'input_col',
          'input_type',
          'input_value',
          'data_source',
          'map_fields',
          'input_placeholder',
          'show_when_field',
          'show_when_value',
          'show_operator',
          'required_when_field',
          'required_when_value',
          'remarks_required_when',
          'photo_required_when',
          'custom_value',
          'input_class',
          'input_required',
          'input_readonly',
          'input_disabled',
          'input_min',
          'input_max',
          'input_step',
          'input_pattern',
          'input_maxlength',
          'input_options',
          'input_order',
          'file_accept',
          'max_file_size',
          'compress_image',
          'form_id',
          'section_id',
          'sub_section_id',
          'status',
     ];

     protected bool $allowEmptyInserts = false;

     // Dates
     protected $useTimestamps = false;
     protected $dateFormat    = 'datetime';

     // Validation
     protected $validationRules = [
          'input_name' => 'required|max_length[50]',
          'input_label' => 'permit_empty|max_length[150]',
          'input_icon' => 'permit_empty|max_length[50]',
          'input_icon_color' => 'permit_empty|max_length[20]',
          'input_col' => 'permit_empty|max_length[10]',
          'input_type' => 'required|max_length[50]',
          'input_value' => 'permit_empty',
          'data_source' => 'permit_empty',
          'map_fields' => 'permit_empty',
          'input_placeholder' => 'permit_empty|max_length[50]',
          'show_when_field' => 'permit_empty|max_length[150]',
          'show_when_value' => 'permit_empty|max_length[150]',
          'show_operator' => 'permit_empty|in_list[=,!=,>,<]',
          'required_when_field' => 'permit_empty|max_length[150]',
          'required_when_value' => 'permit_empty|max_length[150]',
          'remarks_required_when' => 'permit_empty|max_length[255]',
          'photo_required_when' => 'permit_empty|max_length[255]',
          'custom_value' => 'permit_empty',
          'input_class' => 'permit_empty|max_length[255]',
          'input_required' => 'permit_empty|in_list[0,1]',
          'input_readonly' => 'permit_empty|in_list[0,1]',
          'input_disabled' => 'permit_empty|in_list[0,1]',
          'input_min' => 'permit_empty|max_length[50]',
          'input_max' => 'permit_empty|max_length[50]',
          'input_step' => 'permit_empty|max_length[50]',
          'input_pattern' => 'permit_empty|max_length[255]',
          'input_maxlength' => 'permit_empty|integer',
          'input_options' => 'permit_empty',
          'input_order' => 'permit_empty|integer',
          'file_accept' => 'permit_empty|max_length[100]',
          'max_file_size' => 'permit_empty|integer',
          'compress_image' => 'permit_empty|in_list[0,1]',
          'form_id' => 'required|integer',
          'section_id' => 'permit_empty|integer',
          'sub_section_id' => 'permit_empty|integer',
          'status' => 'required|in_list[A,I]',
     ];
     protected $validationMessages = [];
     protected $skipValidation = false;
     protected $cleanValidationRules = true;
}

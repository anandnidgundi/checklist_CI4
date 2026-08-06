<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchACVendorMappingModel extends Model
{
    protected $table = 'branch_ac_vendor_map'; // Table name
    protected $primaryKey = 'id'; // Primary key

    // Fields that can be inserted/updated
    protected $allowedFields = ['branch_id', 'branch_name', 'vendor_id', 'sys_no', 'dry_service_yearly_count', 'wet_service_yearly_count', 'created_by', 'created_on'];

 

    // Validation rules (optional)
    protected $validationRules = [
        'branch_name' => 'required|string|max_length[255]',
        'branch_id'         => 'required|integer', 
        'vendor_id'         => 'required|integer', 
        'sys_no'         => 'required|integer', 

    ];

    protected $validationMessages = [
        'branch_name' => [
            'required' => 'Branch name is required.'
        ],
        'branch_id' => [
            'required' => 'Branch Id is required.',
            'integer'  => 'Branch Id must be a valid integer.',
        ],
        'vendor_id' => [
            'required' => 'Vendor Id is required.',
            'integer'  => 'Vendor Id must be a valid integer.',
        ],
        'sys_no' => [
            'required' => 'Sys No is required.',
            'integer'  => 'Sys No must be a valid integer.',
        ],
    ];

}

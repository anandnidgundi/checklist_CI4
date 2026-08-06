<?php

namespace App\Models;

use CodeIgniter\Model;

class ACServiceModel extends Model
{
    protected $table = 'ac_services'; // Table name
    protected $primaryKey = 'id'; // Primary key

    // Fields that can be inserted/updated
    protected $allowedFields = ['vendor_id','branch_id','branch_name','sys_no','service_type','dry_service_yearly_count','wet_service_yearly_count','dry_service_completed','wet_service_completed','dry_service_cleaning_date','next_dry_service_date','wet_service_cleaning_date','next_wet_service_date','manager_present','remarks','cleaning_attachment','service_report_attachment','vendor_name', 'created_by', 'created_on'];

 

    // Validation rules (optional)
    protected $validationRules = [
        'vendor_name' => 'required|string|max_length[255]',
    ];

    protected $validationMessages = [
        'vendor_name' => [
            'required' => 'Vendor name is required.'
        ]
    ];

}

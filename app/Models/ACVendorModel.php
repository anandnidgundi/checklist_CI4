<?php

namespace App\Models;

use CodeIgniter\Model;

class ACVendorModel extends Model
{
    protected $table = 'ac_vendors'; // Table name
    protected $primaryKey = 'id'; // Primary key

    // Fields that can be inserted/updated
    protected $allowedFields = ['vendor_name', 'created_by', 'created_on'];

 

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

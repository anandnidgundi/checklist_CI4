<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchVendorMapModel extends Model
{
    protected $table = 'branch_vendor_map';
    protected $primaryKey = 'id';
    
    protected $allowedFields = [
        'branch_id',
        'vendor_id',
        'visit_id',
        'createdDTM',
        'createdBy',
        'updatedDTM',
        'updatedBy'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    protected $validationRules = [
        'branch_id' => 'required|numeric',
        'vendor_id' => 'required|numeric',
        'visit_id' => 'required|numeric',
        'createdDTM' => 'required',
        'createdBy' => 'required|numeric'
    ];

    protected $validationMessages = [
        'branch_id' => [
            'required' => 'Branch ID is required',
            'numeric' => 'Branch ID must be numeric'
        ],
        'vendor_id' => [
            'required' => 'Vendor ID is required',
            'numeric' => 'Vendor ID must be numeric'
        ],
        'visit_id' => [
            'required' => 'Visit ID is required',
            'numeric' => 'Visit ID must be numeric'
        ],
        'createdBy' => [
            'required' => 'Creator ID is required',
            'numeric' => 'Creator ID must be numeric'
        ]
    ];
}
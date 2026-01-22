<?php

namespace App\Models;

use CodeIgniter\Model;

class VendorModel extends Model
{
    protected $table = 'vendor';
    protected $primaryKey = 'vendor_id';
    protected $allowedFields = [
        'vendor_name',
        'vendor_address', 
        'vendor_email',
        'vendor_mobile',
        'vendor_gst',
        'service_type',
        'branches',
        'terms',
        'createdDTM',
        'createdBy',
        'updatedDTM',
        'updatedBy',
        'status',
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    protected $validationRules = [
        'vendor_name' => 'required|max_length[100]',
        'vendor_address' => 'permit_empty|max_length[250]',
        'vendor_email' => 'permit_empty|max_length[50]|valid_email',
        'vendor_mobile' => 'permit_empty|max_length[15]',
        'vendor_gst' => 'permit_empty|max_length[20]',
        'service_type' => 'required|max_length[30]',
        'branches' => 'required|max_length[500]',
        'terms' => 'required',
        'status' => 'permit_empty|in_list[A,I]'
    ];
}
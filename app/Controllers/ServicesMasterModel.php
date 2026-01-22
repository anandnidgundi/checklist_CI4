<?php

namespace App\Models;

use CodeIgniter\Model;

class ServicesMasterModel extends Model
{
    protected $table = 'services';
    protected $primaryKey = 'sid';
    protected $allowedFields = [
        'service_date',
        'service_type',
        'visiter_name',
        'visiter_mobile',
        'remarks',
        'branch_id',
        'vendor_id',
        'createdDTM',
        'createdBy',
        'updatedDTM',
        'updatedBy',
        'status'
    ];

    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    protected $validationRules = [
        'vendor_id' => 'required|numeric',
        'createdBy' => 'required|numeric',
        'status' => 'required|in_list[A,I]'
    ];
}
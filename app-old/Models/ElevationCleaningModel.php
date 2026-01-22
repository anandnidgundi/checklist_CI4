<?php

 

namespace App\Models;

use CodeIgniter\Model;

class ElevationCleaningModel extends Model
{
    protected $table = 'elevation_cleaningl';
    protected $primaryKey = 'el_id';
    protected $allowedFields = [
        'service_date',
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
    protected $returnType = 'array';
}
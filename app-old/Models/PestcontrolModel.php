<?php

namespace App\Models;

use CodeIgniter\Model;

class PestcontrolModel extends Model
{
    protected $table = 'pest_control';
    protected $primaryKey = 'pid';
    protected $allowedFields = ['service_date', 'visiter_name', 'visiter_mobile', 'remarks', 'branch_id', 'vendor_id', 'status', 'createdDTM', 'createdBy'];
    protected $useTimestamps = false;
}
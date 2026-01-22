<?php

namespace App\Models;

use CodeIgniter\Model;

class VisitmasterModel extends Model
{
    protected $table = 'visit_master';
    protected $primaryKey = 'visit_id';
    protected $allowedFields = [
        'visit_recurring',
        'visit_day',
        'branch_id',
        'vendor_id',
        'createdDTM',
        'createdBy'
    ];

    // Validation rules
    protected $validationRules = [
        'visit_recurring' => 'permit_empty|max_length[10]',
        'visit_day' => 'permit_empty|numeric|max_length[5]',        
        'vendor_id' => 'permit_empty|numeric|max_length[5]',
        'createdBy' => 'required|max_length[10]'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'createdDTM';
    protected $updatedField = ''; // No updated timestamp field in this table
}

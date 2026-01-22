<?php

namespace App\Models;

use CodeIgniter\Model;

class HkMonthlyIndentsModel extends Model
{
     protected $table = 'hk_monthly_indents';
     protected $primaryKey = 'id';
     protected $allowedFields = ['branch_id', 'month', 'status', 'requested_by', 'approved_by', 'notes', 'total_amount', 'created_at', 'approved_at'];
     protected $returnType = 'array';
}

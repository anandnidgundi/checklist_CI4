<?php

namespace App\Models;

use CodeIgniter\Model;

class HkMonthlyIndentItemsModel extends Model
{
     protected $table = 'hk_monthly_indent_items';
     protected $primaryKey = 'id';
     protected $allowedFields = ['indent_id', 'hk_item_id', 'qty_requested', 'qty_approved', 'qty_received', 'price', 'total_amount', 'brand', 'unit', 'remarks'];
     protected $returnType = 'array';
}

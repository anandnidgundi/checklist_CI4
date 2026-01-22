<?php



namespace App\Models;

use CodeIgniter\Model;

class WaterTankCleaningModel extends Model
{
    protected $table = 'water_tank_cleaning';
    protected $primaryKey = 'wt_id';
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

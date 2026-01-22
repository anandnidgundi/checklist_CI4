<?php
namespace App\Models;
use CodeIgniter\Model;
class PowerConsumptionLogsModel extends Model
{
     protected $table = 'power_consumption_logs';
     protected $primaryKey = 'id';
     protected $allowedFields = [
          'branch_id',
          'cluster_id',
          'zone_id',
          'morning_units',
          'consumption_date',
          'night_units',
          'total_consumption',
          'nonbusinesshours',
          'remarks',
          'createdBy',
          'createdDTM'
     ];
     protected $useTimestamps = false;
     protected $returnType = 'array';
     public function __construct()
     {
          parent::__construct();
     }
     //getPowerConsumptionLogsById
     /**
      * Get power consumption logs by ID
      * @param int $id Power Consumption Log ID
      * @return array|null Returns array of logs or null if not found
      */
     public function getPowerConsumptionLogs($id)
     {
          if (!$id) {
               return null;
          }
          $builder = $this->db->table($this->table);
          $builder->where('id', $id);
          try {
               $query = $builder->get();
               return ($query->getNumRows() > 0) ? $query->getRowArray() : null;
          } catch (\Exception $e) {
               log_message('error', 'Failed to fetch power consumption logs: ' . $e->getMessage());
               return null;
          }
     }
}

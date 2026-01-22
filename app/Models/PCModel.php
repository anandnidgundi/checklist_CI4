<?php

namespace App\Models;

use CodeIgniter\Model;

class PCModel extends Model
{
    protected $table = 'power_consumption_new_2';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'power_consumption_id',
        'meter_id',
        'meter_number',
        'meter_name',
        'morning_units',
        'night_units',
        'morning_remarks',
        'night_remarks',
        'non_business_units',
        'createdBy',
        'createdDTM',
        'total_units'
    ];

    //getPowerMeterDataByPcId
    /**
     * Get power meter data by power consumption ID
     * @param int $pcId Power Consumption ID
     * @return array|null Returns array of meter data or null if not found
     */
    public function getPowerMeterDataByPcId($pcId)
    {
        if (!$pcId) {
            return null;
        }

        $builder = $this->db->table($this->table);
        $builder->where('power_consumption_id', $pcId);
        
        try {
            $query = $builder->get();
            //foreach meterId provide files data
            if ($query->getNumRows() > 0) {
                $result = $query->getResult();
                foreach ($result as $row) {
                    $fileModel = new FileModel();
                    $files = $fileModel->where('meter_id', $row->meter_id)->findAll();
                    $row->files = $files;
                }
            }
            return ($query->getNumRows() > 0) ? $query->getResult() : null;
        } catch (\Exception $e) {
            log_message('error', 'Failed to fetch power meter data: ' . $e->getMessage());
            return null;
        }
    }
}
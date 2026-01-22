<?php

namespace App\Models;

use CodeIgniter\Model;

class HkDetailsModel extends Model
{
     protected $table = 'hk_details';
     protected $primaryKey = 'id';
     protected $allowedFields = [
          'hkr_id',
          'hk_material',
          'hk_make',
          'hk_price',
          'quantity',
          'amount'
     ];

     protected $useTimestamps = false;
     protected $returnType = 'array';

     /**
      * Get details by requirement ID
      */
     public function getDetailsByRequirement($hkr_id)
     {
          return $this->where('hkr_id', $hkr_id)->findAll();
     }

     /**
      * Add multiple requirement details
      */
     public function addRequirementDetails($hkr_id, $details)
     {
          $insertData = [];
          foreach ($details as $detail) {
               $insertData[] = [
                    'hkr_id' => $hkr_id,
                    'hk_material' => $detail['hk_material'],
                    'hk_make' => $detail['hk_make'],
                    'hk_price' => $detail['hk_price'],
                    'quantity' => $detail['quantity'],
                    'amount' => $detail['amount']
               ];
          }

          return $this->insertBatch($insertData);
     }

     /**
      * Update requirement detail
      */
     public function updateDetail($id, $data)
     {
          return $this->update($id, $data);
     }

     /**
      * Delete details by requirement ID
      */
     public function deleteByRequirement($hkr_id)
     {
          return $this->where('hkr_id', $hkr_id)->delete();
     }

     /**
      * Get requirements with details
      */
     public function getRequirementWithDetails($hkr_id)
     {
          $requirement = $this->db->table('hk_requirements')
               ->where('hkr_id', $hkr_id)
               ->get()
               ->getRowArray();

          if ($requirement) {
               $details = $this->getDetailsByRequirement($hkr_id);
               $requirement['details'] = $details;
          }

          return $requirement;
     }
}

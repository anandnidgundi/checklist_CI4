<?php

namespace App\Models;

use CodeIgniter\Model;

class DrinkingWaterModel extends Model
{
     protected $table = 'drinking_water'; // Table name
     protected $primaryKey = 'id'; // Primary key

     // Fields that can be inserted/updated
     protected $allowedFields = [
          'total_water_cans_supplied',
          'total_empty_water_cans_returned',
          'water_cans_supplied',
          'water_cans_returned',
          'remarks',
          'created_by',
          'created_dtm',
          'updated_by',
          'updated_dtm',
          'branch_id',
          'branch_name',
          'branch_manager_mobile',
          'vendor_name',
          'vendor_id',
          'date_of_supply',
          'branch_manager_emp_code',
          'branch_manager_name'
     ];

     // Validation rules (optional)
     protected $validationRules = [
          'vendor_name' => 'required|max_length[100]',
          'total_water_cans_supplied' => 'required|integer',
          'total_empty_water_cans_returned' => 'required|integer',
          'date_of_supply' => 'required|valid_date',
     ];

     protected $validationMessages = [
          'vendor_name' => [
               'required' => 'Vendor name is required.'
          ],
          'total_water_cans_supplied' => [
               'required' => 'Total water cans supplied is required.',
               'integer' => 'Total water cans supplied must be a valid number.'
          ],
          'total_empty_water_cans_returned' => [
               'required' => 'Total empty water cans returned is required.',
               'integer' => 'Total empty water cans returned must be a valid number.'
          ],
          'date_of_supply' => [
               'required' => 'Date of supply is required.',
               'valid_date' => 'Date of supply must be a valid date.'
          ],
     ];

     public function getFiles($id)
     {
          // get files data from files table with ac_form_id as $id
          $files = $this->db->table('files')->where('drinking_water_id', $id)->get()->getResultArray();
          return $files;
     }

     public function getTotalCanDetailsOfVendorOfBranch($branch_id, $vendor_id)
     {
          // check last entry for this vendor_id and branch_id and return the total water cans supplied and total empty water cans returned.
          return $this->where(['branch_id' => $branch_id, 'vendor_id' => $vendor_id])->orderBy('id', 'DESC')->first();
     }

     public function getPreviousEntry($branchId, $vendorId, $id)
     {
          return $this->where('branch_id', $branchId)
               ->where('vendor_id', $vendorId)
               ->where('id <', $id)
               ->orderBy('id', 'DESC')
               ->first();
     }

     public function recalculateTotals($branchId, $vendorId)
     {
          $rows = $this->where('branch_id', $branchId)
               ->where('vendor_id', $vendorId)
               ->orderBy('id', 'ASC')
               ->findAll();

          $supplied = 0;
          $returned = 0;

          foreach ($rows as $row) {
               $supplied += (int)$row['water_cans_supplied'];
               $returned += (int)$row['water_cans_returned'];

               $this->update($row['id'], [
                    'total_water_cans_supplied' => $supplied,
                    'total_empty_water_cans_returned' => $returned,
               ]);
          }
     }
}

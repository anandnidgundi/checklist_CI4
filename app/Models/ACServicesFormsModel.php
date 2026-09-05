<?php

namespace App\Models;

use CodeIgniter\Model;

class ACServicesFormsModel extends Model
{
     protected $table = 'ac_services_forms';

     protected $primaryKey = 'id';

     protected $allowedFields = [
          'branch_id',
          'branch_name',

          'branch_manager_name',
          'branch_manager_emp_code',
          'branch_manager_mobile',
          'branch_manager_email',

          'vendor_name',
          'vendor_id',

          'dry_service_yearly_count',
          'wet_service_yearly_count',
          'agreement_for_year',
          'dry_service_completed',
          'wet_service_completed',

          'date_of_cleaning',
          'next_date_of_cleaning',

          'audited_by_name',
          'audited_by_emp_code',
          'audited_date',

          'remarks',
          'was_cleaning_done_bm',
     ];

     protected $useTimestamps = false;

     //getFiles
     public function getFiles($id)
     {
          // get files data from files table with ac_form_id as $id
          $files = $this->db->table('files')->where('ac_form_id', $id)->get()->getResultArray();
          return $files;
     }

     public function getCompletedServicesInSelectedYear($branchId, $vendorId, $year)
     {
          $result = $this->select("
            COALESCE(SUM(dry_service_completed), 0) AS dry_service_completed_count,
            COALESCE(SUM(wet_service_completed), 0) AS wet_service_completed_count
        ")
               ->where('branch_id', $branchId)
               ->where('vendor_id', $vendorId)
               ->where('agreement_for_year', $year)
               ->first();

          return [
               'dry_service_completed_count' => (int) ($result['dry_service_completed_count'] ?? 0),
               'wet_service_completed_count' => (int) ($result['wet_service_completed_count'] ?? 0),
          ];
     }
}

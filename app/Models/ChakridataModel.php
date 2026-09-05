<?php

namespace App\Models;

use CodeIgniter\Model;

class ChakridataModel extends Model
{
     protected $table            = 'chakri_data';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';

     protected $allowedFields = [
          'Branch Name',
          'Asset Name',
          'Make',
          'Model',
          'Serial No',
          'Asset_desc',
          'DOI',
          'Warranty',
          'Contract Type',
          'Start Date',
          'End Date',
          'PMS 1',
          'PMS 2',
          'QA Start',
          'QA Due',
          'AERB Licence due',
          'IP Address',
          'AE Title',
          'Remarks',
     ];

     public function getChakriDatalistByBranchName($branchName)
     {
          $builder = $this->builder();

          $builder->where(
               "REPLACE(TRIM(`Branch Name`), ' ', '') = REPLACE(TRIM(" .
                    $this->db->escape($branchName) .
                    "), ' ', '')",
               null,
               false
          );

          return $builder->get()->getResultArray();
     }

     public function getChakriDataByAssetId($assetId)
     {
          return $this->where(['id' => $assetId])->first();
     }
}

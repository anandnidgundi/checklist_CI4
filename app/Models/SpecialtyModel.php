<?php

namespace App\Models;

use CodeIgniter\Model;

class SpecialtyModel extends Model
{
     protected $table      = 'specialty';
     protected $primaryKey = 'id';

     protected $returnType     = 'array';
     protected $useSoftDeletes = false;

     protected $allowedFields = ['specialty'];

     // Dates
     protected $useTimestamps = false;

     /**
      * Get all specialties optionally keyed by id.
      *
      * @param bool $keyById
      * @return array
      */
     public function getAllSpecialties(bool $keyById = false): array
     {
          $rows = $this->orderBy('specialty', 'ASC')->findAll();
          if (! $keyById) {
               return $rows;
          }

          $result = [];
          foreach ($rows as $r) {
               $result[$r['id']] = $r['specialty'];
          }

          return $result;
     }
}

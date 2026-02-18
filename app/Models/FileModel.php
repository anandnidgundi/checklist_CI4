<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
     protected $table = 'files'; // Name of your files table
     protected $primaryKey = 'file_id'; // Primary key of the table (matches DB schema)
     protected $allowedFields = [
          'file_name',
          'tour_id',
          'em_code',
          'mid',
          'nid',
          'cm_mid',
          'cm_nid',
          'service_id',
          'bmw_id',
          'diesel_id',
          'power_id',
          'hkr_id',
          'createdDTM'
     ]; // Fields that can be inserted or updated
     protected $useTimestamps = false; // Disable automatic timestamps
}

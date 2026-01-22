<?php

namespace App\Models;

use CodeIgniter\Model;

class FileModel extends Model
{
     protected $table = 'files'; // Name of your files table
     protected $primaryKey = 'file_id'; // Primary key of the table (matches DB schema)
     protected $allowedFields = ['file_name', 'original_name', 'mime_type', 'file_size', 'hkr_id', 'mid', 'nid', 'cm_mid', 'cm_nid', 'bmw_id', 'emp_code', 'createdDTM', 'uploaded_by']; // Fields that can be inserted or updated
     protected $useTimestamps = false; // Disable automatic timestamps
}

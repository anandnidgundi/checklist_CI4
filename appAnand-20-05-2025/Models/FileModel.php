<?php

namespace App\Models;
 
use CodeIgniter\Model;

class FileModel extends Model
{
    protected $table = 'files'; // Name of your files table
    protected $primaryKey = 'f_id'; // Primary key of the table
    protected $allowedFields = ['file_name', 'mid', 'createdDTM', 'em_code','nid', 'cm_mid', 'cm_nid', 'bmw_id', 'diesel_id', 'power_id', 'meter_id']; // Fields that can be inserted or updated
    protected $useTimestamps = false; // Disable automatic timestamps
}

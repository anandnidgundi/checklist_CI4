<?php

namespace App\Models;

use CodeIgniter\Model;

class NewVendorsModel extends Model
{
     protected $table = 'new_vendors';

     protected $primaryKey = 'id';

     protected $allowedFields = [
          'vendor_name',
          'checklist_form',
          'vendor_details',
          'branches_mapped',
          'created_by',
          'updated_by',
     ];

     protected $useTimestamps = true;

     protected $createdField = 'created_dtm';
     protected $updatedField = 'updated_dtm';

     protected $dateFormat = 'datetime';
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class NewChecklistFormsModel extends Model
{
     protected $table = 'new_checklist_forms';

     protected $primaryKey = 'id';

     protected $allowedFields = [
          'checklist_form',
     ];

     protected $useTimestamps = true;
     protected $createdField = 'created_dtm';
     protected $dateFormat = 'datetime';
}

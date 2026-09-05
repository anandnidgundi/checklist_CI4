<?php

namespace App\Models;

use CodeIgniter\Model;

class BranchesModel extends Model
{
     protected $table      = 'Branches';
     protected $primaryKey = 'id';

     protected $returnType = 'array';

     protected $allowedFields = [
          'SysField',
          'SysNo',
          'area',
          'AreaId',
          'Address',
          'ContactNo',
          'EmailId',
          'status'
     ];

     protected $DBGroup = 'secondary';
}

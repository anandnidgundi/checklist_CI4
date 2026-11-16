<?php

namespace App\Models;

use CodeIgniter\Model;

class Logs_sessionsModel extends Model
{
     protected $table      = 'login_sessions';
     protected $primaryKey = 'session_id';

     protected $useAutoIncrement = true;

     protected $returnType = 'array';

     protected $allowedFields = [
          'ip_address',
          'user_agent',
          'user_name',
          'logged_in_time',
          'emp_code',
          'token',
     ];

     protected $useTimestamps = false;
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailTemplateModel extends Model
{
     protected $table = 'email_templates';
     protected $primaryKey = 'id';
     protected $allowedFields = [
          'event_key',
          'subject',
          'html_template',
          'variables',
          'created_at',
          'updated_at'
     ];
     protected $useTimestamps = true;
     protected $createdField  = 'created_at';
     protected $updatedField  = 'updated_at';

     public function getByEventKey($eventKey)
     {
          return $this->where('event_key', $eventKey)->first();
     }
}

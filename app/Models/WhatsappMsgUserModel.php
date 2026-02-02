<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappMsgUserModel extends Model
{
     protected $table = 'watsapp_msg_user';
     protected $primaryKey = 'id';
     protected $allowedFields = ['type'];
     protected $returnType = 'array';
     protected $useTimestamps = false;

     /**
      * Get all rows
      * @return array
      */
     public function getAll(): array
     {
          return $this->findAll();
     }

     /**
      * Get a row by id
      */
     public function getById(int $id): ?array
     {
          return $this->find($id);
     }

     /**
      * Insert new type
      */
     public function createType(array $data)
     {
          return $this->insert($data);
     }

     /**
      * Update an existing type
      */
     public function updateType(int $id, array $data)
     {
          return $this->update($id, $data);
     }

     /**
      * Delete a type
      */
     public function deleteType(int $id)
     {
          return $this->delete($id);
     }

     /**
      * Get types together with their users (comma separated)
      */
     public function getWithUserList(): array
     {
          return $this->select('watsapp_msg_user.*, GROUP_CONCAT(watsapp_msg_user_list.user) AS users')
               ->join('watsapp_msg_user_list', 'watsapp_msg_user_list.type_id = watsapp_msg_user.id', 'left')
               ->groupBy('watsapp_msg_user.id')
               ->findAll();
     }
}

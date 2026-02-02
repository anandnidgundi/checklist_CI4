<?php

namespace App\Models;

use CodeIgniter\Model;

class WhatsappMsgUserListModel extends Model
{
     protected $table = 'watsapp_msg_user_list';
     protected $primaryKey = 'id';
     protected $allowedFields = ['type_id', 'user'];
     protected $returnType = 'array';
     protected $useTimestamps = false;

     /**
      * Get all users for a given type
      */
     public function getByType(int $typeId): array
     {
          return $this->where('type_id', $typeId)->findAll();
     }

     /**
      * Add a user to a type
      */
     public function addUserToType(array $data)
     {
          return $this->insert($data);
     }

     /**
      * Remove by id
      */
     public function removeById(int $id)
     {
          return $this->delete($id);
     }

     /**
      * Remove a user from a type (by type_id and user)
      */
     public function removeByTypeAndUser(int $typeId, int $user)
     {
          return $this->where(['type_id' => $typeId, 'user' => $user])->delete();
     }
}

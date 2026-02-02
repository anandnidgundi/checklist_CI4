<?php

namespace App\Models;

use App\Models\WhatsappMsgUserModel;
use App\Models\WhatsappMsgUserListModel;

/**
 * Wrapper service-model for WhatsApp user types and their user lists.
 */
class WhatsappUsersModel
{
     protected WhatsappMsgUserModel $userTypeModel;
     protected WhatsappMsgUserListModel $userListModel;

     public function __construct()
     {
          $this->userTypeModel = new WhatsappMsgUserModel();
          $this->userListModel = new WhatsappMsgUserListModel();
     }

     /**
      * Return all types with an array of user ids in `users` key.
      */
     public function getTypesWithUsers(): array
     {
          $types = $this->userTypeModel->getWithUserList();

          return array_map(function ($row) {
               if (!empty($row['users'])) {
                    $row['users'] = array_map('intval', explode(',', $row['users']));
               } else {
                    $row['users'] = [];
               }

               return $row;
          }, $types);
     }

     public function getType(int $id): ?array
     {
          return $this->userTypeModel->getById($id);
     }

     public function createType(string $type): int
     {
          return (int) $this->userTypeModel->createType(['type' => $type]);
     }

     public function updateType(int $id, array $data): bool
     {
          return (bool) $this->userTypeModel->updateType($id, $data);
     }

     public function deleteType(int $id): bool
     {
          // delete any associated user list entries
          $this->userListModel->where('type_id', $id)->delete();
          return (bool) $this->userTypeModel->deleteType($id);
     }

     public function getUsersForType(int $typeId): array
     {
          $rows = $this->userListModel->getByType($typeId);
          return array_map(function ($r) {
               return (int) $r['user'];
          }, $rows);
     }

     public function addUserToType(int $typeId, int $user): int
     {
          return (int) $this->userListModel->addUserToType(['type_id' => $typeId, 'user' => $user]);
     }

     public function removeUserFromType(int $typeId, int $user): bool
     {
          return (bool) $this->userListModel->removeByTypeAndUser($typeId, $user);
     }

     public function removeUserById(int $id): bool
     {
          return (bool) $this->userListModel->removeById($id);
     }
}

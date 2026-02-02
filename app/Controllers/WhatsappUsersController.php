<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\WhatsappUsersModel;
use App\Models\WhatsappMsgUserModel;

class WhatsappUsersController extends BaseController
{
     use ResponseTrait;

     public function index()
     {
          $this->validateAuthorizationNew();
          $m = new WhatsappUsersModel();
          return $this->respond(['data' => $m->getTypesWithUsers()], 200);
     }

     public function show($id)
     {
          $this->validateAuthorizationNew();
          $m = new WhatsappUsersModel();
          $type = $m->getType((int)$id);
          if (!$type) return $this->respond(['message' => 'Not found'], 404);
          $type['users'] = $m->getUsersForType((int)$id);
          return $this->respond(['data' => $type], 200);
     }

     public function create()
     {
          $this->validateAuthorizationNew();
          $input = $this->request->getJSON(true) ?: [];

          $type = isset($input['type']) ? trim(strip_tags((string)$input['type'])) : '';
          if ($type === '') return $this->respond(['message' => 'Type is required'], 400);
          if (strlen($type) > 100) $type = substr($type, 0, 100);

          $check = (new WhatsappMsgUserModel())->where('type', $type)->first();
          if ($check) return $this->respond(['message' => 'Type already exists'], 409);

          $m = new WhatsappUsersModel();
          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $id = $m->createType($type);
               $db->transComplete();
               return $this->respondCreated(['message' => 'Created', 'id' => $id]);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function update($id)
     {
          $this->validateAuthorizationNew();

          $wm = new WhatsappMsgUserModel();
          $existing = $wm->find($id);
          if (!$existing) return $this->respond(['message' => 'Not found'], 404);

          $input = $this->request->getJSON(true) ?: [];
          $type = array_key_exists('type', $input) ? (is_null($input['type']) ? null : trim(strip_tags((string)$input['type']))) : $existing['type'];
          if ($type === '' || $type === null) return $this->respond(['message' => 'Type is required'], 400);
          if (strlen($type) > 100) $type = substr($type, 0, 100);

          $conflict = $wm->where('type', $type)->where('id !=', $id)->first();
          if ($conflict) return $this->respond(['message' => 'Another type with this name exists'], 409);

          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $updated = $wm->update($id, ['type' => $type]);
               $db->transComplete();
               $new = $wm->find($id);
               return $this->respond(['message' => 'Updated', 'updated' => (bool) $updated, 'type' => $new], 200);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function delete($id)
     {
          $this->validateAuthorizationNew();
          $wm = new WhatsappMsgUserModel();
          $item = $wm->find($id);
          if (!$item) return $this->respond(['message' => 'Not found'], 404);

          $db = \Config\Database::connect();
          $db->transStart();
          try {
               // delete associated users
               $db->table('watsapp_msg_user_list')->where('type_id', $id)->delete();
               $wm->delete($id);
               $db->transComplete();
               return $this->respondDeleted(['message' => 'Deleted']);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function getUsersForType($typeId)
     {
          $this->validateAuthorizationNew();
          $m = new WhatsappUsersModel();
          return $this->respond(['data' => $m->getUsersForType((int) $typeId)], 200);
     }

     public function addUserToType()
     {
          $this->validateAuthorizationNew();
          $input = $this->request->getJSON(true) ?: [];

          $typeId = isset($input['type_id']) ? (int) $input['type_id'] : 0;
          $user = isset($input['user']) ? (int) $input['user'] : 0;
          if ($typeId <= 0 || $user <= 0) return $this->respond(['message' => 'type_id and user required'], 400);

          $db = \Config\Database::connect();
          $exists = $db->table('watsapp_msg_user_list')->where(['type_id' => $typeId, 'user' => $user])->get()->getRowArray();
          if ($exists) return $this->respond(['message' => 'User already assigned to this type'], 409);

          $m = new WhatsappUsersModel();
          try {
               $id = $m->addUserToType($typeId, $user);
               return $this->respondCreated(['message' => 'Created', 'id' => $id]);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function removeUserFromType()
     {
          $this->validateAuthorizationNew();
          $input = $this->request->getJSON(true) ?: [];

          $typeId = isset($input['type_id']) ? (int) $input['type_id'] : 0;
          $user = isset($input['user']) ? (int) $input['user'] : 0;
          if ($typeId <= 0 || $user <= 0) return $this->respond(['message' => 'type_id and user required'], 400);

          $m = new WhatsappUsersModel();
          try {
               $ok = $m->removeUserFromType($typeId, $user);
               return $this->respond(['message' => 'Removed', 'removed' => (bool) $ok], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function removeUserById($id)
     {
          $this->validateAuthorizationNew();
          $m = new WhatsappUsersModel();
          try {
               $ok = $m->removeUserById((int) $id);
               return $this->respondDeleted(['message' => 'Deleted', 'removed' => (bool) $ok]);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }
}

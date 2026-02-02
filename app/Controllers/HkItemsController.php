<?php


namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkItemsModel;

class HkItemsController extends BaseController
{
     use ResponseTrait;

     public function index()
     {
          $this->validateAuthorizationNew();
          $m = new HkItemsModel();
          // Return items ordered by name ascending
          return $this->respond(['data' => $m->getAllItems()], 200);
     }

     public function getByType($type)
     {
          $this->validateAuthorizationNew();
          $m = new HkItemsModel();
          $rows = $m->where('item_type', $type)->orderBy('name', 'ASC')->findAll();
          return $this->respond(['data' => $rows], 200);
     }

     public function show($id)
     {
          $this->validateAuthorizationNew();
          $m = new HkItemsModel();
          $item = $m->find($id);
          return $this->respond($item ? ['data' => $item] : ['message' => 'Not found'], $item ? 200 : 404);
     }

     public function create()
     {
          $this->validateAuthorizationNew();
          $input = $this->request->getJSON(true) ?: [];

          // sanitize + validation
          $name = isset($input['name']) ? trim(strip_tags($input['name'])) : '';
          $brand = isset($input['brand']) ? trim(strip_tags($input['brand'])) : null;
          $unit = isset($input['unit']) ? trim(strip_tags($input['unit'])) : null;

          if ($name === '') {
               return $this->respond(['message' => 'Name is required'], 400);
          }
          if (strlen($name) > 150) $name = substr($name, 0, 150);
          if ($brand !== null && strlen($brand) > 100) $brand = substr($brand, 0, 100);
          if ($unit !== null && strlen($unit) > 50) $unit = substr($unit, 0, 50);

          $m = new HkItemsModel();
          // prevent duplicate name (simple check; DB collation governs case-sensitivity)
          if ($m->where('name', $name)->first()) {
               return $this->respond(['message' => 'Item with this name already exists'], 409);
          }

          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $id = $m->insert([
                    'name' => $name,
                    'brand' => $brand,
                    'unit' => $unit,
                    'item_type' => $input['item_type'] ?? 'Consumables'
               ]);

               // insert notification payload (for downstream processing)
               $payload = json_encode(['name' => $name, 'brand' => $brand, 'unit' => $unit]);
               $db->table('hk_notifications')->insert([
                    'type' => 'hk_item_created',
                    'payload' => $payload,
                    'status' => 'pending'
               ]);

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
          $m = new HkItemsModel();
          $existing = $m->find($id);
          if (!$existing) return $this->respond(['message' => 'Not found'], 404);

          $input = $this->request->getJSON(true) ?: [];

          // sanitize + validation
          $name = isset($input['name']) ? trim(strip_tags($input['name'])) : $existing['name'];
          $brand = array_key_exists('brand', $input) ? (is_null($input['brand']) ? null : trim(strip_tags($input['brand']))) : $existing['brand'];
          $unit = array_key_exists('unit', $input) ? (is_null($input['unit']) ? null : trim(strip_tags($input['unit']))) : $existing['unit'];

          // handle item_type: if client sends empty string, keep existing
          $item_type = array_key_exists('item_type', $input) ? trim(strip_tags((string)$input['item_type'])) : $existing['item_type'];
          if ($item_type === '') $item_type = $existing['item_type'];

          if ($name === '') {
               return $this->respond(['message' => 'Name is required'], 400);
          }
          if (strlen($name) > 150) $name = substr($name, 0, 150);
          if ($brand !== null && strlen($brand) > 100) $brand = substr($brand, 0, 100);
          if ($unit !== null && strlen($unit) > 50) $unit = substr($unit, 0, 50);
          if ($item_type !== null && strlen($item_type) > 50) $item_type = substr($item_type, 0, 50);

          // prevent duplicate name (other record)
          $conflict = $m->where('name', $name)->where('id !=', $id)->first();
          if ($conflict) {
               return $this->respond(['message' => 'Another item with this name exists'], 409);
          }

          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $updated = $m->update($id, ['name' => $name, 'brand' => $brand, 'unit' => $unit, 'item_type' => $item_type]);

               // notification for update
               $payload = json_encode(['id' => (int)$id, 'name' => $name, 'brand' => $brand, 'unit' => $unit, 'item_type' => $item_type]);
               $db->table('hk_notifications')->insert([
                    'type' => 'hk_item_updated',
                    'payload' => $payload,
                    'status' => 'pending'
               ]);

               $db->transComplete();

               // return updated row for verification
               $newItem = $m->find($id);

               return $this->respond(['message' => 'Updated', 'updated' => (bool)$updated, 'affected' => $db->affectedRows(), 'item' => $newItem], 200);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function delete($id)
     {
          $this->validateAuthorizationNew();
          $m = new HkItemsModel();
          $item = $m->find($id);
          if (!$item) return $this->respond(['message' => 'Not found'], 404);

          $db = \Config\Database::connect();

          // check references in other tables before deleting
          $tablesToCheck = [
               'hk_branch_ideal_qty' => 'hk_item_id',
               'hk_opening_stock' => 'hk_item_id',
               'hk_stock_received' => 'hk_item_id',
               'hk_consumptions' => 'hk_item_id',
               'hk_stock_balances' => 'hk_item_id'
          ];

          foreach ($tablesToCheck as $table => $col) {
               $count = (int) $db->table($table)->where($col, $id)->countAllResults(false);
               if ($count > 0) {
                    return $this->respond(['message' => "Cannot delete: referenced in {$table} ({$count})"], 409);
               }
          }

          try {
               $m->delete($id);
               return $this->respond(['message' => 'Deleted'], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }
}

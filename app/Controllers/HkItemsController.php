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
          $this->validateAuthorization();
          $m = new HkItemsModel();
          return $this->respond(['data' => $m->findAll()], 200);
     }

     public function getByType($type)
     {
          $this->validateAuthorization();
          $m = new HkItemsModel();
          $rows = $m->where('item_type', $type)->orderBy('name', 'ASC')->findAll();
          return $this->respond(['data' => $rows], 200);
     }

     public function show($id)
     {
          $this->validateAuthorization();
          $m = new HkItemsModel();
          $item = $m->find($id);
          return $this->respond($item ? ['data' => $item] : ['message' => 'Not found'], $item ? 200 : 404);
     }

     public function create()
     {
          $this->validateAuthorization();
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
          $this->validateAuthorization();
          $m = new HkItemsModel();
          $existing = $m->find($id);
          if (!$existing) return $this->respond(['message' => 'Not found'], 404);

          $input = $this->request->getJSON(true) ?: [];

          // sanitize + validation
          $name = isset($input['name']) ? trim(strip_tags($input['name'])) : $existing['name'];
          $brand = array_key_exists('brand', $input) ? trim(strip_tags($input['brand'])) : $existing['brand'];
          $unit = array_key_exists('unit', $input) ? trim(strip_tags($input['unit'])) : $existing['unit'];

          if ($name === '') {
               return $this->respond(['message' => 'Name is required'], 400);
          }
          if (strlen($name) > 150) $name = substr($name, 0, 150);
          if ($brand !== null && strlen($brand) > 100) $brand = substr($brand, 0, 100);
          if ($unit !== null && strlen($unit) > 50) $unit = substr($unit, 0, 50);

          // prevent duplicate name (other record)
          $conflict = $m->where('name', $name)->where('id !=', $id)->first();
          if ($conflict) {
               return $this->respond(['message' => 'Another item with this name exists'], 409);
          }

          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $m->update($id, ['name' => $name, 'brand' => $brand, 'unit' => $unit, 'item_type' => $input['item_type'] ?? $existing['item_type']]);

               // notification for update
               $payload = json_encode(['id' => (int)$id, 'name' => $name, 'brand' => $brand, 'unit' => $unit]);
               $db->table('hk_notifications')->insert([
                    'type' => 'hk_item_updated',
                    'payload' => $payload,
                    'status' => 'pending'
               ]);

               $db->transComplete();
               return $this->respond(['message' => 'Updated'], 200);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function delete($id)
     {
          $this->validateAuthorization();
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

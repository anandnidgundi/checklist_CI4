<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class StockController extends BaseController
{
     use ResponseTrait;

     public function addOpeningStock()
     {
          $user = $this->validateAuthorizationNew();
          $data = $this->request->getJSON(true); // branch_id, items: [{hk_item_id, opening_qty}]
          $db = \Config\Database::connect();
          $db->transStart();
          try {
               foreach ($data['items'] as $it) {
                    // Ensure single row per (branch_id, hk_item_id) by removing existing rows first
                    $db->table('hk_opening_stock')->where(['branch_id' => $data['branch_id'], 'hk_item_id' => $it['hk_item_id']])->delete();
                    $db->table('hk_opening_stock')->insert([
                         'branch_id' => $data['branch_id'],
                         'hk_item_id' => $it['hk_item_id'],
                         'opening_qty' => $it['opening_qty'],
                         'created_by' => $user->emp_code,
                         'created_at' => date('Y-m-d H:i:s')
                    ]);
                    $this->recalcBalance($data['branch_id'], $it['hk_item_id']);
               }
               $db->transComplete();
               return $this->respond(['message' => 'Opening stock saved'], 201);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function getOpeningStock($branch_id = null)
     {
          // Accept branch id from URL segment (route) or query string (for compatibility)
          $branch_id = $branch_id ?? $this->request->getGet('branch_id');
          if (!$branch_id) {
               // return empty list if not specified to avoid SQL where IS NULL
               return $this->respond([], 200);
          }

          $db = \Config\Database::connect();
          $data = $db->table('hk_opening_stock as os')
               ->select("os.hk_item_id, it.name as item_name, it.item_type, it.brand, it.unit, COALESCE((SELECT ideal_qty FROM hk_branch_ideal_qty q WHERE q.branch_id = os.branch_id AND q.hk_item_id = os.hk_item_id), 0) as ideal_qty, SUM(os.opening_qty) as opening_qty")
               ->join('hk_items as it', 'it.id = os.hk_item_id', 'left')
               ->where('os.branch_id', $branch_id)
               ->groupBy('os.hk_item_id, it.name, it.item_type, it.brand, it.unit')
               ->orderBy('it.name')
               ->get()->getResultArray();
          return $this->respond($data, 200);
     }

     public function recordReceipt()
     {
          $user = $this->validateAuthorizationNew();
          $data = $this->request->getJSON(true); // branch_id, hk_item_id, received_qty
          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $db->table('hk_stock_received')->insert([
                    'branch_id' => $data['branch_id'],
                    'hk_item_id' => $data['hk_item_id'],
                    'received_qty' => $data['received_qty'],
                    'created_by' => $user->emp_code
               ]);
               $this->recalcBalance($data['branch_id'], $data['hk_item_id']);
               $db->transComplete();
               return $this->respond(['message' => 'Receipt recorded'], 201);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function getBalance($branch_id = null, $hk_item_id = null)
     {
          // Accept branch/item from URL segments or query params
          $branch_id = $branch_id ?? $this->request->getGet('branch_id');
          $hk_item_id = $hk_item_id ?? $this->request->getGet('hk_item_id');

          if (!$branch_id) {
               // return empty result for missing branch
               return $this->respond([], 200);
          }

          $db = \Config\Database::connect();

          if ($hk_item_id) {
               // Ensure balance is up to date by recalculating for this item
               try {
                    $model = new \App\Models\HkStockBalancesModel();
                    $model->recalcBalance((int)$branch_id, (int)$hk_item_id);
               } catch (\Throwable $e) {
                    // ignore recalc failure but continue to return what we have
                    log_message('error', 'recalcBalance failed: ' . $e->getMessage());
               }

               $row = $db->table('hk_stock_balances')->select('current_balance')->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->get()->getRow();
               $bal = $row ? (float)$row->current_balance : null;
               return $this->respond($bal, 200);
          }

          // Branch-level: return all balances for branch
          $rows = $db->table('hk_stock_balances as sb')
               ->select('sb.hk_item_id, it.name as item_name, it.brand as brand, it.unit, COALESCE((SELECT ideal_qty FROM hk_branch_ideal_qty q WHERE q.branch_id = sb.branch_id AND q.hk_item_id = sb.hk_item_id), 0) as ideal_qty, sb.opening_qty, sb.total_received, sb.total_consumed, sb.current_balance, sb.last_updated')
               ->join('hk_items it', 'it.id = sb.hk_item_id', 'left')
               ->where('sb.branch_id', $branch_id)
               ->orderBy('it.name', 'ASC')
               ->get()->getResultArray();

          return $this->respond($rows, 200);
     }

     private function recalcBalance($branch_id, $hk_item_id)
     {
          $db = \Config\Database::connect();
          $opening = (float)$db->table('hk_opening_stock')
               ->select('COALESCE(SUM(opening_qty),0) as s')->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->get()->getRow()->s;
          // Prefer the receipts stored in hk_stock_receipt_items joined to hk_stock_receipts (branch on receipts). Fallback to legacy hk_stock_received table if needed.
          try {
               $received = (float)$db->table('hk_stock_receipt_items as sri')
                    ->select('COALESCE(SUM(sri.received_qty),0) as s')
                    ->join('hk_stock_receipts sr', 'sr.id = sri.receipt_id', 'left')
                    ->where(['sr.branch_id' => $branch_id, 'sri.hk_item_id' => $hk_item_id])
                    ->get()->getRow()->s;
          } catch (\Exception $e) {
               $received = (float)$db->table('hk_stock_received')
                    ->select('COALESCE(SUM(received_qty),0) as s')->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->get()->getRow()->s;
          }
          $consumed = (float)$db->table('hk_consumptions')
               ->select('COALESCE(SUM(consumed_qty),0) as s')->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->get()->getRow()->s;
          $balance = $opening + $received - $consumed;
          $exists = $db->table('hk_stock_balances')->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->get()->getRow();
          if ($exists) {
               $db->table('hk_stock_balances')->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->update([
                    'opening_qty' => $opening,
                    'total_received' => $received,
                    'total_consumed' => $consumed,
                    'current_balance' => $balance,
                    'last_updated' => date('Y-m-d H:i:s')
               ]);
          } else {
               $db->table('hk_stock_balances')->insert([
                    'branch_id' => $branch_id,
                    'hk_item_id' => $hk_item_id,
                    'opening_qty' => $opening,
                    'total_received' => $received,
                    'total_consumed' => $consumed,
                    'current_balance' => $balance,
                    'last_updated' => date('Y-m-d H:i:s')
               ]);
          }
     }
}

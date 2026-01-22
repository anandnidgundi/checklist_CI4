<?php

namespace App\Models;

use CodeIgniter\Model;

class HkStockBalancesModel extends Model
{
     protected $table = 'hk_stock_balances';
     protected $primaryKey = 'id';
     protected $allowedFields = [
          'branch_id',
          'hk_item_id',
          'opening_qty',
          'total_received',
          'total_consumed',
          'current_balance',
          'last_updated'
     ];

     /**
      * Recalculate and update/insert balance for branch + item.
      */
     public function recalcBalance(int $branchId, int $hkItemId): bool
     {
          $db = \Config\Database::connect();

          $opening = (float) $db->table('hk_opening_stock')
               ->select('COALESCE(SUM(opening_qty),0) as s')
               ->where(['branch_id' => $branchId, 'hk_item_id' => $hkItemId])
               ->get()
               ->getRow()
               ->s;

          // Prefer receipts stored in the newer receipt_items + receipts tables; fall back to legacy hk_stock_received if necessary
          try {
               $received = (float) $db->table('hk_stock_receipt_items as sri')
                    ->select('COALESCE(SUM(sri.received_qty),0) as s')
                    ->join('hk_stock_receipts sr', 'sr.id = sri.receipt_id', 'left')
                    ->where(['sr.branch_id' => $branchId, 'sri.hk_item_id' => $hkItemId])
                    ->get()
                    ->getRow()
                    ->s;
          } catch (\Exception $e) {
               // If the newer tables do not exist in older deployments, fall back to legacy table
               $received = (float) $db->table('hk_stock_received')
                    ->select('COALESCE(SUM(received_qty),0) as s')
                    ->where(['branch_id' => $branchId, 'hk_item_id' => $hkItemId])
                    ->get()
                    ->getRow()
                    ->s;
          }

          $consumed = (float) $db->table('hk_consumptions')
               ->select('COALESCE(SUM(consumed_qty),0) as s')
               ->where(['branch_id' => $branchId, 'hk_item_id' => $hkItemId])
               ->get()
               ->getRow()
               ->s;

          $balance = $opening + $received - $consumed;

          $exists = $db->table($this->table)
               ->where(['branch_id' => $branchId, 'hk_item_id' => $hkItemId])
               ->get()
               ->getRow();

          $data = [
               'branch_id' => $branchId,
               'hk_item_id' => $hkItemId,
               'opening_qty' => $opening,
               'total_received' => $received,
               'total_consumed' => $consumed,
               'current_balance' => $balance,
               'last_updated' => date('Y-m-d H:i:s')
          ];

          if ($exists) {
               return (bool) $db->table($this->table)
                    ->where(['branch_id' => $branchId, 'hk_item_id' => $hkItemId])
                    ->update($data);
          }

          return (bool) $db->table($this->table)->insert($data);
     }
}

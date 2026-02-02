<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class StockReceiptController extends BaseController
{
     use ResponseTrait;

     public function create()
     {
          $user = $this->validateAuthorizationNew();
          $data = $this->request->getJSON(true);

          log_message('debug', 'StockReceipt.create - User: ' . json_encode($user));
          log_message('debug', 'StockReceipt.create - Payload: ' . json_encode($data));

          if (empty($data['branch_id']) || empty($data['items']) || !is_array($data['items'])) {
               return $this->respond(['message' => 'Invalid payload'], 400);
          }

          $db = \Config\Database::connect();

          // Start transaction explicitly and check results
          $db->transStart();
          try {
               $received_by = $user->emp_code ?? $data['created_by'] ?? null;

               // Prevent duplicate full-receipt for the same indent (block repeat receives)
               if (!empty($data['indent_id'])) {
                    try {
                         $existingReceipt = $db->table('hk_stock_receipts')->select('id')->where('indent_id', $data['indent_id'])->get()->getRowArray();
                    } catch (\Exception $e) {
                         $existingReceipt = null;
                    }
                    if (!empty($existingReceipt)) {
                         log_message('warning', 'StockReceipt.create - Duplicate attempt for indent ' . $data['indent_id'] . ', existing receipt ' . ($existingReceipt['id'] ?? 'unknown'));
                         $db->transRollback();
                         return $this->respond(['message' => 'Indent already has a receipt', 'receipt_id' => $existingReceipt['id'] ?? null], 409);
                    }
               }

               // Insert receipt header
               $createdAt = date('Y-m-d H:i:s');
               $receiptData = [
                    'indent_id' => $data['indent_id'] ?? null,
                    'branch_id' => $data['branch_id'],
                    'invoice_no' => $data['invoice_no'] ?? null,
                    'received_by' => $received_by,
                    'notes' => $data['notes'] ?? null,
                    'created_at' => $createdAt
               ];

               log_message('debug', 'StockReceipt.create - Receipt data: ' . json_encode($receiptData));

               $res = $db->table('hk_stock_receipts')->insert($receiptData);
               if ($res === false) {
                    $err = $db->error();
                    log_message('error', 'StockReceipt.create - Failed to insert receipt header: ' . json_encode($err));
                    $db->transRollback();
                    return $this->respond(['message' => 'Failed to insert receipt header', 'error' => $err], 500);
               }

               // Obtain insert id; if 0, try to find inserted row by unique combination as a fallback
               $receiptId = (int)$db->insertID();
               if ($receiptId <= 0) {
                    log_message('warning', 'StockReceipt.create - insertID returned 0, attempting lookup');
                    $found = $db->table('hk_stock_receipts')
                         ->select('id')
                         ->where(['branch_id' => $data['branch_id'], 'created_at' => $createdAt])
                         ->orderBy('id', 'DESC')->get()->getRowArray();
                    $receiptId = $found['id'] ?? 0;
                    log_message('debug', 'StockReceipt.create - Fallback lookup gave id: ' . $receiptId);
               }

               if ($receiptId <= 0) {
                    $err = $db->error();
                    log_message('error', 'StockReceipt.create - Could not determine receipt id: ' . json_encode($err));
                    $db->transRollback();
                    return $this->respond(['message' => 'Failed to determine receipt id', 'error' => $err], 500);
               }

               log_message('debug', 'StockReceipt.create inserted receipt id: ' . $receiptId);

               // Validate items: ensure at least one positive received qty
               $hasPositive = false;
               foreach ($data['items'] as $it) {
                    if (!empty($it) && (float)($it['qty_received'] ?? 0) > 0) {
                         $hasPositive = true;
                         break;
                    }
               }
               if (!$hasPositive) {
                    log_message('warning', 'StockReceipt.create - No positive qty_received in payload items');
                    $db->transRollback();
                    return $this->respond(['message' => 'No positive received quantities provided'], 400);
               }

               // Insert items and recalc balances
               foreach ($data['items'] as $it) {
                    log_message('debug', 'StockReceipt.create - Inserting item: ' . json_encode($it));

                    $itemInsert = $db->table('hk_stock_receipt_items')->insert([
                         'receipt_id' => $receiptId,
                         'hk_item_id' => $it['hk_item_id'],
                         'received_qty' => $it['qty_received'] ?? 0,
                         'indent_item_id' => $it['indent_item_id'] ?? null
                    ]);

                    if ($itemInsert === false) {
                         $err = $db->error();
                         log_message('error', 'StockReceipt.create - Failed to insert receipt item: ' . json_encode($err));
                         $db->transRollback();
                         return $this->respond(['message' => 'Failed to insert receipt item', 'error' => $err], 500);
                    }

                    // Recalculate balances using receipts join (branch is on receipts table)
                    $branch_id = $data['branch_id'];
                    $hk_item_id = $it['hk_item_id'];

                    $opening = (float)$db->table('hk_opening_stock')
                         ->select('COALESCE(SUM(opening_qty),0) as s')
                         ->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->get()->getRow()->s;

                    $received = (float)$db->table('hk_stock_receipt_items as sri')
                         ->select('COALESCE(SUM(sri.received_qty),0) as s')
                         ->join('hk_stock_receipts sr', 'sr.id = sri.receipt_id', 'left')
                         ->where(['sr.branch_id' => $branch_id, 'sri.hk_item_id' => $hk_item_id])
                         ->get()->getRow()->s;

                    $consumed = (float)$db->table('hk_consumptions')
                         ->select('COALESCE(SUM(consumed_qty),0) as s')
                         ->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])->get()->getRow()->s;

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

               // debug: count inserted items for this receipt
               $insertedItemsCount = (int)$db->table('hk_stock_receipt_items')->where('receipt_id', $receiptId)->countAllResults();
               log_message('debug', 'StockReceipt.create inserted items count: ' . $insertedItemsCount);

               // Update qty_received in hk_monthly_indent_items by aggregating all receipts for each indent item
               if (!empty($data['indent_id'])) {
                    // Ensure the column exists; if not, create it (safe for dev environment)
                    try {
                         $fields = $db->getFieldNames('hk_monthly_indent_items');
                    } catch (\Exception $e) {
                         $fields = [];
                    }

                    if (!in_array('qty_received', $fields)) {
                         try {
                              $db->query("ALTER TABLE `hk_monthly_indent_items` ADD COLUMN `qty_received` DECIMAL(10,2) NOT NULL DEFAULT 0.00");
                              log_message('info', 'StockReceipt.create - Added column qty_received to hk_monthly_indent_items');
                         } catch (\Exception $e) {
                              log_message('error', 'StockReceipt.create - Failed to add qty_received column: ' . $e->getMessage());
                              // continue without failing the whole request; skip updating indent items
                              $fields[] = 'qty_received'; // avoid reattempts below
                         }
                    }

                    $affectedIndentItems = array_unique(array_filter(array_column($data['items'], 'indent_item_id')));
                    foreach ($affectedIndentItems as $indentItemId) {
                         if (!$indentItemId) continue;

                         // Sum all received_qty from hk_stock_receipt_items for this indent item
                         $totalReceived = (float)$db->table('hk_stock_receipt_items')
                              ->select('COALESCE(SUM(received_qty), 0) as total')
                              ->where('indent_item_id', $indentItemId)
                              ->get()->getRow()->total;

                         // Update the indent item with aggregated qty_received
                         if (in_array('qty_received', $fields)) {
                              try {
                                   $db->table('hk_monthly_indent_items')
                                        ->where('id', $indentItemId)
                                        ->update(['qty_received' => $totalReceived]);
                              } catch (\Exception $e) {
                                   log_message('error', 'StockReceipt.create - Failed to update indent item qty_received: ' . $e->getMessage());
                              }
                         }
                    }
               }

               $db->transComplete();

               return $this->respond(['message' => 'Receipt recorded', 'id' => $receiptId, 'items_inserted' => $insertedItemsCount], 201);
          } catch (\Exception $e) {
               $db->transRollback();
               log_message('error', 'StockReceipt.create - Exception: ' . $e->getMessage());
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function list()
     {
          $branch_id = $this->request->getGet('branch_id');
          $indent_id = $this->request->getGet('indent_id');
          $db = \Config\Database::connect();
          $builder = $db->table('hk_stock_receipts as r')->select('r.*')->orderBy('r.created_at', 'DESC');
          if ($branch_id) $builder->where('r.branch_id', $branch_id);
          if ($indent_id) $builder->where('r.indent_id', $indent_id);
          $rows = $builder->get()->getResultArray();

          // Enrich rows with requested_by_name (from indent) and received_by_name (from secondary DB new_emp_master)
          try {
               // collect received_by codes
               $receivedCodes = array_values(array_unique(array_filter(array_map(function ($r) {
                    return isset($r['received_by']) ? (string)$r['received_by'] : null;
               }, $rows))));

               // collect indent ids and map to requested_by
               $indentIds = array_values(array_unique(array_filter(array_map(function ($r) {
                    return isset($r['indent_id']) ? $r['indent_id'] : null;
               }, $rows))));
               $indentMap = [];
               if (!empty($indentIds)) {
                    $indentRows = $db->table('hk_monthly_indents')->select('id, requested_by')->whereIn('id', $indentIds)->get()->getResultArray();
                    foreach ($indentRows as $ir) {
                         $indentMap[$ir['id']] = isset($ir['requested_by']) ? (string)$ir['requested_by'] : null;
                    }
               }

               $requestedCodes = array_values(array_unique(array_filter(array_values($indentMap))));
               $codesToLookup = array_values(array_unique(array_merge($receivedCodes, $requestedCodes)));

               $empMap = [];
               if (!empty($codesToLookup)) {
                    try {
                         $db2 = \Config\Database::connect('secondary');
                         $employees = $db2->table('new_emp_master')->select('emp_code, comp_name')->whereIn('emp_code', $codesToLookup)->get()->getResultArray();
                         foreach ($employees as $e) $empMap[(string)$e['emp_code']] = $e['comp_name'];
                    } catch (\Exception $e) {
                         log_message('error', 'StockReceipt.list - Failed to lookup employee names: ' . $e->getMessage());
                    }
               }

               foreach ($rows as &$r) {
                    // received_by name
                    $rb = isset($r['received_by']) ? (string)$r['received_by'] : null;
                    if ($rb && isset($empMap[$rb])) $r['received_by_name'] = $empMap[$rb];
                    else $r['received_by_name'] = $r['received_by'] ?? null;

                    // requested_by derived from indent
                    $iid = $r['indent_id'] ?? null;
                    $req = $iid && isset($indentMap[$iid]) ? $indentMap[$iid] : null;
                    if ($req && isset($empMap[$req])) $r['requested_by_name'] = $empMap[$req];
                    else $r['requested_by_name'] = null; // leave as null to allow frontend fallback to indent details
               }
               unset($r);
          } catch (\Exception $e) {
               log_message('error', 'StockReceipt.list - Enrichment failed: ' . $e->getMessage());
          }

          return $this->respond($rows, 200);
     }

     public function get($id = null)
     {
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);
          $db = \Config\Database::connect();
          $receipt = $db->table('hk_stock_receipts')->where('id', $id)->get()->getRowArray();
          if (!$receipt) return $this->respond(['message' => 'Not found'], 404);
          $items = $db->table('hk_stock_receipt_items as it')
               ->select('it.*, i.name as item_name, i.unit, i.brand')
               ->join('hk_items i', 'i.id = it.hk_item_id', 'left')
               ->where('it.receipt_id', $id)
               ->get()->getResultArray();
          $receipt['items'] = $items;

          // Enrich receipt with received_by_name and requested_by_name (from indent -> requested_by)
          try {
               $db2 = \Config\Database::connect('secondary');
               // received_by name
               $rb = isset($receipt['received_by']) ? (string)$receipt['received_by'] : null;
               if ($rb) {
                    try {
                         $emp = $db2->table('new_emp_master')->select('emp_code, comp_name')->where('emp_code', $rb)->get()->getRowArray();
                         $receipt['received_by_name'] = ($emp && !empty($emp['comp_name'])) ? $emp['comp_name'] : $rb;
                    } catch (\Exception $e) {
                         $receipt['received_by_name'] = $rb;
                    }
               } else {
                    $receipt['received_by_name'] = null;
               }

               // requested_by via indent (if exists)
               if (!empty($receipt['indent_id'])) {
                    $indent = $db->table('hk_monthly_indents')->select('requested_by')->where('id', $receipt['indent_id'])->get()->getRowArray();
                    $req = $indent['requested_by'] ?? null;
                    if ($req) {
                         try {
                              $emp2 = $db2->table('new_emp_master')->select('emp_code, comp_name')->where('emp_code', $req)->get()->getRowArray();
                              $receipt['requested_by_name'] = ($emp2 && !empty($emp2['comp_name'])) ? $emp2['comp_name'] : $req;
                         } catch (\Exception $e) {
                              $receipt['requested_by_name'] = $req;
                         }
                    } else {
                         $receipt['requested_by_name'] = null;
                    }
               }
          } catch (\Exception $e) {
               log_message('error', 'StockReceipt.get - Enrichment failed: ' . $e->getMessage());
               // fallbacks already applied above; proceed
          }

          return $this->respond($receipt, 200);
     }
}

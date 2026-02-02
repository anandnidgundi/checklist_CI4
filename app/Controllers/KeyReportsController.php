<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class KeyReportsController extends BaseController
{
     use ResponseTrait;

     // Roles allowed to access reports
     protected $allowedRoles = ['SUPER_ADMIN', 'ADMIN', 'AUDIT', 'CM', 'ZONAL_MANAGER', 'AVP'];

     private function isAllowed($user)
     {
          return isset($user->role) && in_array($user->role, $this->allowedRoles);
     }

     private function detectDateColumn($table, $db)
     {
          // Prefer a real date/datetime column; fall back to a stored YYYY-MM month column if present.
          // Different installs used different names, so try a list.
          $candidates = [
               'recorded_at',
               'consumption_date',
               'consumed_at',
               'entry_date',
               'date',
               'created_at',
               'updated_at',
               'submitted_at',
               'month',
          ];

          foreach ($candidates as $col) {
               $has = (bool)$db->query("SHOW COLUMNS FROM {$table} LIKE '{$col}'")->getNumRows();
               if ($has) return $col;
          }

          return null;
     }

     private function applyMonthFilter($builder, $alias, $dateCol, $month)
     {
          if (empty($month) || empty($dateCol)) return;
          $qualified = $alias ? "{$alias}.{$dateCol}" : $dateCol;

          // If the table stores month as a YYYY-MM string in a column literally named `month`.
          if ($dateCol === 'month') {
               $builder->where($qualified, $month);
               return;
          }

          $builder->where("DATE_FORMAT({$qualified}, '%Y-%m') =", $month);
     }

     public function branchConsumption()
     {
          $user = $this->validateAuthorizationNew();
          if (!$this->isAllowed($user)) return $this->respond(['message' => 'Forbidden'], 403);

          $month = $this->request->getGet('month'); // expected YYYY-MM
          $db = \Config\Database::connect();

          $dateCol = $this->detectDateColumn('hk_consumptions', $db) ?? 'recorded_at';

          try {
               $builder = $db->table('hk_consumptions as c')
                    ->select('c.branch_id, COALESCE(SUM(c.consumed_qty),0) as total_consumed_qty, COUNT(DISTINCT c.hk_item_id) as items_count')
                    ->groupBy('c.branch_id')
                    ->orderBy('total_consumed_qty', 'DESC');

               $this->applyMonthFilter($builder, 'c', $dateCol, $month);

               $rows = $builder->get()->getResultArray();

               // total received quantities per branch for the same month
               $receivedMap = [];
               try {
                    $dateColReceipts = $this->detectDateColumn('hk_stock_receipts', $db) ?? 'created_at';
                    $recQ = $db->table('hk_stock_receipt_items as sri')
                         ->select('sr.branch_id, COALESCE(SUM(sri.received_qty),0) as total_received_qty')
                         ->join('hk_stock_receipts sr', 'sr.id = sri.receipt_id', 'left')
                         ->groupBy('sr.branch_id');
                    $this->applyMonthFilter($recQ, 'sr', $dateColReceipts, $month);
                    $recRows = $recQ->get()->getResultArray();
                    foreach ($recRows as $rr) $receivedMap[(string)$rr['branch_id']] = (float)$rr['total_received_qty'];
               } catch (\Exception $e) {
                    // legacy fallback: hk_stock_received table
                    try {
                         $recQ2 = $db->table('hk_stock_received as r')
                              ->select('r.branch_id, COALESCE(SUM(r.received_qty),0) as total_received_qty')
                              ->groupBy('r.branch_id');
                         $this->applyMonthFilter($recQ2, 'r', $this->detectDateColumn('hk_stock_received', $db) ?? 'created_at', $month);
                         $recRows2 = $recQ2->get()->getResultArray();
                         foreach ($recRows2 as $rr) $receivedMap[(string)$rr['branch_id']] = (float)$rr['total_received_qty'];
                    } catch (\Exception $e2) {
                         // ignore
                    }
               }

               // attach received_qty (for UI) default 0
               foreach ($rows as &$r) {
                    $r['received_qty'] = $receivedMap[(string)$r['branch_id']] ?? 0.0;
               }
               unset($r);

               // enrich branch names from secondary DB
               $db2 = \Config\Database::connect('secondary');
               $branchIds = array_filter(array_map('strval', array_column($rows, 'branch_id')));
               if (!empty($branchIds)) {
                    $branchData = $db2->table('Branches')->select('id, SysField')->whereIn('id', $branchIds)->get()->getResultArray();
                    $branchMap = [];
                    foreach ($branchData as $b) $branchMap[(string)$b['id']] = trim($b['SysField'] ?? '');
                    foreach ($rows as &$r) {
                         $r['branch_name'] = $branchMap[(string)$r['branch_id']] ?? null;
                    }
                    unset($r);
               }

               return $this->respond($rows, 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function itemMonthlyConsumption()
     {
          $user = $this->validateAuthorizationNew();
          if (!$this->isAllowed($user)) return $this->respond(['message' => 'Forbidden'], 403);

          $month = $this->request->getGet('month'); // YYYY-MM
          $branch_id = $this->request->getGet('branch_id');
          $item_id = $this->request->getGet('item_id');
          $db = \Config\Database::connect();
          $dateCol = $this->detectDateColumn('hk_consumptions', $db) ?? 'recorded_at';

          try {
               // If item_id provided, return branch-wise consumption for that item (with ideal qty)
               if (!empty($item_id)) {
                    // ideal quantities per branch for this item
                    $idealRows = $db->table('hk_branch_ideal_qty as q')
                         ->select('q.branch_id, COALESCE(q.ideal_qty,0) as ideal_qty')
                         ->where('q.hk_item_id', $item_id)
                         ->get()->getResultArray();

                    $idealMap = [];
                    foreach ($idealRows as $r) $idealMap[(string)$r['branch_id']] = (float)$r['ideal_qty'];

                    // consumed sums grouped by branch for this item
                    $consQ = $db->table('hk_consumptions as c')
                         ->select('c.branch_id, COALESCE(SUM(c.consumed_qty),0) as consumed_qty')
                         ->where('c.hk_item_id', $item_id)
                         ->groupBy('c.branch_id');
                    $this->applyMonthFilter($consQ, 'c', $dateCol, $month);
                    if ($branch_id) $consQ->where('c.branch_id', $branch_id);
                    $consRows = $consQ->get()->getResultArray();
                    $consMap = [];
                    foreach ($consRows as $r) $consMap[(string)$r['branch_id']] = (float)$r['consumed_qty'];

                    // Union of branch ids
                    $branchIds = array_values(array_unique(array_merge(array_keys($idealMap), array_keys($consMap))));

                    $rows = [];
                    foreach ($branchIds as $bid) {
                         $rows[] = [
                              'branch_id' => $bid,
                              'ideal_qty' => $idealMap[$bid] ?? 0.0,
                              'consumed_qty' => $consMap[$bid] ?? 0.0,
                              'received_qty' => 0.0
                         ];
                    }

                    // attach received qty per branch for this item and month
                    $receivedMap = [];
                    try {
                         $dateColReceipts = $this->detectDateColumn('hk_stock_receipts', $db) ?? 'created_at';
                         $recQ = $db->table('hk_stock_receipt_items as sri')
                              ->select('sr.branch_id, COALESCE(SUM(sri.received_qty),0) as total_received_qty')
                              ->join('hk_stock_receipts sr', 'sr.id = sri.receipt_id', 'left')
                              ->where('sri.hk_item_id', $item_id)
                              ->groupBy('sr.branch_id');
                         $this->applyMonthFilter($recQ, 'sr', $dateColReceipts, $month);
                         $recRows = $recQ->get()->getResultArray();
                         foreach ($recRows as $rr) $receivedMap[(string)$rr['branch_id']] = (float)$rr['total_received_qty'];
                    } catch (\Exception $e) {
                         // fallback to legacy table
                         try {
                              $recQ2 = $db->table('hk_stock_received as r')
                                   ->select('r.branch_id, COALESCE(SUM(r.received_qty),0) as total_received_qty')
                                   ->where(['r.hk_item_id' => $item_id])
                                   ->groupBy('r.branch_id');
                              $this->applyMonthFilter($recQ2, 'r', $this->detectDateColumn('hk_stock_received', $db) ?? 'created_at', $month);
                              $recRows2 = $recQ2->get()->getResultArray();
                              foreach ($recRows2 as $rr) $receivedMap[(string)$rr['branch_id']] = (float)$rr['total_received_qty'];
                         } catch (\Exception $e2) {
                              // ignore
                         }
                    }

                    // attach received_qty to rows
                    foreach ($rows as &$r) {
                         $r['received_qty'] = $receivedMap[(string)$r['branch_id']] ?? 0.0;
                    }
                    unset($r);

                    // enrich branch names from secondary DB
                    if (!empty($rows)) {
                         $db2 = \Config\Database::connect('secondary');
                         $branchData = $db2->table('Branches')->select('id, SysField')->whereIn('id', array_column($rows, 'branch_id'))->get()->getResultArray();
                         $branchMap = [];
                         foreach ($branchData as $b) $branchMap[(string)$b['id']] = trim($b['SysField'] ?? '');
                         foreach ($rows as &$r) {
                              $r['branch_name'] = $branchMap[(string)$r['branch_id']] ?? null;
                         }
                         unset($r);
                    }

                    return $this->respond($rows, 200);
               }

               // Fallback: item list with aggregated consumption across branches
               $qb = $db->table('hk_items as i')
                    ->select('i.id as hk_item_id, i.name as item_name, COALESCE(SUM(c.consumed_qty),0) as consumed_qty')
                    ->join('hk_consumptions c', 'c.hk_item_id = i.id', 'left')
                    ->groupBy('i.id')
                    ->orderBy('consumed_qty', 'DESC');

               if ($month) {
                    $this->applyMonthFilter($qb, 'c', $dateCol, $month);
               }
               if ($branch_id) {
                    $qb->where('c.branch_id', $branch_id);
               }

               $rows = $qb->get()->getResultArray();
               return $this->respond($rows, 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function idealVsActual()
     {
          $user = $this->validateAuthorizationNew();
          if (!$this->isAllowed($user)) return $this->respond(['message' => 'Forbidden'], 403);

          $branch_id = $this->request->getGet('branch_id');
          if (empty($branch_id)) return $this->respond(['message' => 'branch_id is required'], 400);
          $month = $this->request->getGet('month');
          $db = \Config\Database::connect();
          $dateCol = $this->detectDateColumn('hk_consumptions', $db) ?? 'recorded_at';

          try {
               $qb = $db->table('hk_items as i')
                    ->select('i.id as hk_item_id, i.name as item_name, i.item_type, i.brand, i.unit, COALESCE(q.ideal_qty,0) as ideal_qty, COALESCE(SUM(c.consumed_qty),0) as consumed_qty')
                    ->join('hk_branch_ideal_qty q', "q.hk_item_id = i.id AND q.branch_id = {$branch_id}", 'left')
                    ->join('hk_consumptions c', 'c.hk_item_id = i.id', 'left')
                    ->groupBy('i.id')
                    ->orderBy('consumed_qty', 'DESC')
                    ->orderBy('i.name', 'ASC');

               // Restrict consumption sums to the selected branch (important)
               if ($branch_id) {
                    $qb->where('c.branch_id', $branch_id);
               }

               if ($month) {
                    $this->applyMonthFilter($qb, 'c', $dateCol, $month);
               }

               $rows = $qb->get()->getResultArray();

               // compute delta
               foreach ($rows as &$r) {
                    $r['delta'] = (float)$r['consumed_qty'] - (float)$r['ideal_qty'];
                    $r['received_qty'] = 0.0; // default
               }
               unset($r);

               // attach received quantities per item for this branch and month
               try {
                    $dateColReceipts = $this->detectDateColumn('hk_stock_receipts', $db) ?? 'created_at';
                    $recQ = $db->table('hk_stock_receipt_items as sri')
                         ->select('sri.hk_item_id, COALESCE(SUM(sri.received_qty),0) as total_received_qty')
                         ->join('hk_stock_receipts sr', 'sr.id = sri.receipt_id', 'left')
                         ->where('sr.branch_id', $branch_id)
                         ->groupBy('sri.hk_item_id');
                    $this->applyMonthFilter($recQ, 'sr', $dateColReceipts, $month);
                    $recRows = $recQ->get()->getResultArray();
                    $recMap = [];
                    foreach ($recRows as $rr) $recMap[(string)$rr['hk_item_id']] = (float)$rr['total_received_qty'];
                    if (!empty($recMap)) {
                         foreach ($rows as &$r) {
                              $r['received_qty'] = $recMap[(string)$r['hk_item_id']] ?? 0.0;
                         }
                         unset($r);
                    }
               } catch (\Exception $e) {
                    // fallback to legacy table
                    try {
                         $recQ2 = $db->table('hk_stock_received as r')
                              ->select('r.hk_item_id, COALESCE(SUM(r.received_qty),0) as total_received_qty')
                              ->where('r.branch_id', $branch_id)
                              ->groupBy('r.hk_item_id');
                         $this->applyMonthFilter($recQ2, 'r', $this->detectDateColumn('hk_stock_received', $db) ?? 'created_at', $month);
                         $recRows2 = $recQ2->get()->getResultArray();
                         $recMap2 = [];
                         foreach ($recRows2 as $rr) $recMap2[(string)$rr['hk_item_id']] = (float)$rr['total_received_qty'];
                         if (!empty($recMap2)) {
                              foreach ($rows as &$r) {
                                   $r['received_qty'] = $recMap2[(string)$r['hk_item_id']] ?? 0.0;
                              }
                              unset($r);
                         }
                    } catch (\Exception $e2) {
                         // ignore
                    }
               }

               return $this->respond($rows, 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function stockBalance()
     {
          $user = $this->validateAuthorizationNew();
          if (!$this->isAllowed($user)) return $this->respond(['message' => 'Forbidden'], 403);

          $branch_id = $this->request->getGet('branch_id');
          $hk_item_id = $this->request->getGet('hk_item_id');

          if (!$branch_id) {
               return $this->respond(['message' => 'branch_id is required'], 400);
          }

          $db = \Config\Database::connect();

          try {
               // Item-level: return a single current_balance value
               if ($hk_item_id) {
                    try {
                         $model = new \App\Models\HkStockBalancesModel();
                         $model->recalcBalance((int)$branch_id, (int)$hk_item_id);
                    } catch (\Throwable $e) {
                         log_message('error', 'recalcBalance failed: ' . $e->getMessage());
                    }

                    $row = $db->table('hk_stock_balances')
                         ->select('current_balance')
                         ->where(['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id])
                         ->get()
                         ->getRow();

                    $bal = $row ? (float)$row->current_balance : null;
                    return $this->respond($bal, 200);
               }

               // Branch-level: return all balances for the branch
               $rows = $db->table('hk_stock_balances as sb')
                    ->select('sb.hk_item_id, it.name as item_name, it.item_type, it.brand as brand, it.unit, COALESCE((SELECT ideal_qty FROM hk_branch_ideal_qty q WHERE q.branch_id = sb.branch_id AND q.hk_item_id = sb.hk_item_id), 0) as ideal_qty, sb.opening_qty, sb.total_received, sb.total_consumed, sb.current_balance, sb.last_updated')
                    ->join('hk_items it', 'it.id = sb.hk_item_id', 'left')
                    ->where('sb.branch_id', $branch_id)
                    ->orderBy('it.name', 'ASC')
                    ->get()
                    ->getResultArray();

               return $this->respond($rows, 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function indentsStatus()
     {
          $user = $this->validateAuthorizationNew();
          if (!$this->isAllowed($user)) return $this->respond(['message' => 'Forbidden'], 403);

          $branch_id = $this->request->getGet('branch_id');
          $month = $this->request->getGet('month');
          $db = \Config\Database::connect();
          $dateCol = $this->detectDateColumn('hk_monthly_indents', $db);

          try {
               $qb = $db->table('hk_monthly_indents')
                    ->select('status, COUNT(*) as cnt')
                    ->groupBy('status')
                    ->orderBy('cnt', 'DESC');
               if ($branch_id) $qb->where('branch_id', $branch_id);
               $this->applyMonthFilter($qb, null, $dateCol, $month);
               $rows = $qb->get()->getResultArray();

               return $this->respond($rows, 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function branchVisitCycle()
     {
          $user = $this->validateAuthorizationNew();
          if (!$this->isAllowed($user)) return $this->respond(['message' => 'Forbidden'], 403);

          $filter_branch_id = $this->request->getGet('branch_id');
          $month = $this->request->getGet('month'); // YYYY-MM (required)
          $db = \Config\Database::connect();
          try {
               if (empty($month) || !preg_match('/^\d{4}-\d{2}$/', $month)) {
                    return $this->respond(['message' => 'month is required (format YYYY-MM)'], 400);
               }

               // Use the Cycle Consumption submission data (hk_consumptions) instead of Visit Master schedules.
               // A cycle is considered "submitted" for the month when any row exists with locked = 'Y'.
               $dateCol = $this->detectDateColumn('hk_consumptions', $db);
               if (!$dateCol) {
                    return $this->respond(['message' => 'hk_consumptions has no date column to filter by month'], 400);
               }

               $qb = $db->table('hk_consumptions c')
                    ->select(
                         "c.branch_id, " .
                              "MAX(CASE WHEN c.cycle_no = 1 THEN 1 ELSE 0 END) as cycle_1, " .
                              "MAX(CASE WHEN c.cycle_no = 2 THEN 1 ELSE 0 END) as cycle_2, " .
                              "MAX(CASE WHEN c.cycle_no = 3 THEN 1 ELSE 0 END) as cycle_3",
                         false
                    )
                    ->where('c.locked', 'Y')
                    ->groupBy('c.branch_id');

               $this->applyMonthFilter($qb, 'c', $dateCol, $month);

               if (!empty($filter_branch_id)) {
                    $qb->where('c.branch_id', $filter_branch_id);
               }

               $rows = $qb->get()->getResultArray();

               foreach ($rows as &$r) {
                    $r['cycle_1'] = (int)($r['cycle_1'] ?? 0);
                    $r['cycle_2'] = (int)($r['cycle_2'] ?? 0);
                    $r['cycle_3'] = (int)($r['cycle_3'] ?? 0);
                    $r['total_visits'] = $r['cycle_1'] + $r['cycle_2'] + $r['cycle_3'];
               }
               unset($r);

               $db2 = \Config\Database::connect('secondary');
               $branchIds = array_column($rows, 'branch_id');
               if (!empty($branchIds)) {
                    $branchData = $db2->table('Branches')->select('id, SysField')->whereIn('id', $branchIds)->get()->getResultArray();
                    $branchMap = [];
                    foreach ($branchData as $b) $branchMap[(string)$b['id']] = trim($b['SysField'] ?? '');
                    foreach ($rows as &$r) {
                         $r['branch_name'] = $branchMap[(string)$r['branch_id']] ?? null;
                    }
                    unset($r);
               }

               usort($rows, function ($a, $b) {
                    $d = ((int)($b['total_visits'] ?? 0)) <=> ((int)($a['total_visits'] ?? 0));
                    if ($d !== 0) return $d;
                    return strcmp((string)($a['branch_name'] ?? ''), (string)($b['branch_name'] ?? ''));
               });

               return $this->respond(array_values($rows), 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }
}

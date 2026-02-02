<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkMonthlyIndentsModel;
use App\Models\HkMonthlyIndentItemsModel;

class MonthlyIndentController extends BaseController
{
     use ResponseTrait;

     public function create()
     {
          $user = $this->validateAuthorizationNew();
          $data = $this->request->getJSON(true);
          if (empty($data['branch_id']) || empty($data['month']) || empty($data['items']) || !is_array($data['items'])) {
               return $this->respond(['message' => 'Invalid payload'], 400);
          }

          $db = \Config\Database::connect();

          // Prevent duplicate indent for same branch + month (accept both YYYY-MM and Mon-YYYY formats)
          try {
               $m = $data['month'];
               $dupQ = $db->table('hk_monthly_indents')->where('branch_id', $data['branch_id']);
               $monthAlt = null;
               if (preg_match('/^\d{4}-\d{2}$/', $m)) {
                    $dt = \DateTime::createFromFormat('Y-m', $m);
                    if ($dt) $monthAlt = $dt->format('M-Y');
               } elseif (\DateTime::createFromFormat('M-Y', $m)) {
                    $dt = \DateTime::createFromFormat('M-Y', $m);
                    if ($dt) $monthAlt = $dt->format('Y-m');
               }
               if ($monthAlt) {
                    $dupQ->groupStart()->where('month', $m)->orWhere('month', $monthAlt)->groupEnd();
               } else {
                    $dupQ->where('month', $m);
               }
               $existing = $dupQ->get()->getRowArray();
               if ($existing) {
                    return $this->respond(['message' => 'An indent for this branch and month already exists', 'id' => $existing['id'] ?? null, 'status' => $existing['status'] ?? null], 409);
               }
          } catch (\Exception $e) {
               // If duplicate-check fails for any reason, log and continue to allow create (fail-open behavior)
               log_message('error', 'Duplicate check failed: ' . $e->getMessage());
          }

          // calculate total amount for the indent (require price per item in payload or assume 0)
          $totalAmount = 0.0;
          foreach ($data['items'] as $it) {
               $qty = floatval($it['qty_requested'] ?? 0);
               $price = isset($it['price']) ? floatval($it['price']) : 0.0;
               $totalAmount += ($qty * $price);
          }

          // check branch budget (if exists)
          $budgetRow = $db->table('hk_branchwise_budget')->select('budget')->where('branch_id', $data['branch_id'])->get()->getRowArray();
          if ($budgetRow && $totalAmount > floatval($budgetRow['budget'])) {
               return $this->respond(['message' => 'Total amount exceeds branch budget', 'budget' => $budgetRow['budget'], 'total' => number_format($totalAmount, 2, '.', '')], 400);
          }

          $db->transStart();
          try {
               $indents = new HkMonthlyIndentsModel();
               $itemsModel = new HkMonthlyIndentItemsModel();

               $id = $indents->insert([
                    'branch_id' => $data['branch_id'],
                    'month' => $data['month'],
                    'status' => 'pending',
                    'requested_by' => $user->emp_code,
                    'notes' => $data['notes'] ?? null,
                    'total_amount' => number_format($totalAmount, 2, '.', ''),
               ]);

               // fetch item metadata (brand/unit) to use as fallback
               $itemIds = array_values(array_unique(array_map(function ($i) {
                    return intval($i['hk_item_id']);
               }, $data['items'])));
               $itemMeta = [];
               if (!empty($itemIds)) {
                    $rows = $db->table('hk_items')->select('id, brand, unit')->whereIn('id', $itemIds)->get()->getResultArray();
                    foreach ($rows as $r) $itemMeta[$r['id']] = $r;
               }

               foreach ($data['items'] as $it) {
                    $hk_item_id = intval($it['hk_item_id']);
                    $qty = floatval($it['qty_requested'] ?? 0);
                    $price = isset($it['price']) ? floatval($it['price']) : 0.0;
                    $total = $qty * $price;
                    $brand = $it['brand'] ?? ($itemMeta[$hk_item_id]['brand'] ?? null);
                    $unit = $it['unit'] ?? ($itemMeta[$hk_item_id]['unit'] ?? null);

                    $itemsModel->insert([
                         'indent_id' => $id,
                         'hk_item_id' => $hk_item_id,
                         'qty_requested' => $qty,
                         'price' => $price,
                         'total_amount' => $total,
                         'brand' => $brand,
                         'unit' => $unit,
                         'remarks' => $it['remarks'] ?? null,
                    ]);
               }

               $db->transComplete();
               return $this->respond(['message' => 'Indent created', 'id' => $id], 201);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function update($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);
          $data = $this->request->getJSON(true);
          if (empty($data['branch_id']) || empty($data['month']) || empty($data['items']) || !is_array($data['items'])) {
               return $this->respond(['message' => 'Invalid payload'], 400);
          }

          $db = \Config\Database::connect();

          $indent = $db->table('hk_monthly_indents')->where('id', $id)->get()->getRowArray();
          if (!$indent) return $this->respond(['message' => 'Not found'], 404);

          // If indent is approved, no edits allowed
          if ($indent['status'] === 'approved') return $this->respond(['message' => 'Only pending or rejected indents can be edited'], 400);

          // If indent is rejected, only HK_SUPERVISOR may edit (this will re-submit as pending)
          if ($indent['status'] === 'rejected') {
               if (!isset($user->role) || $user->role !== 'HK_SUPERVISOR') {
                    return $this->respond(['message' => 'Only HK_SUPERVISOR can edit a rejected indent'], 403);
               }
          }

          // If this indent already has receipts recorded, disallow edits
          try {
               $receiptCount = $db->table('hk_stock_receipts as sr')
                    ->join('hk_stock_receipt_items as sri', 'sri.receipt_id = sr.id', 'inner')
                    ->join('hk_monthly_indent_items as mii', 'mii.id = sri.indent_item_id', 'left')
                    ->where('mii.indent_id', $id)
                    ->countAllResults();
               if ($receiptCount > 0) {
                    return $this->respond(['message' => 'Cannot edit an indent that has been received'], 400);
               }
          } catch (\Exception $e) {
               log_message('error', 'Duplicate check for receipts failed: ' . $e->getMessage());
          }

          // Prevent changing to a branch+month that already exists on another indent
          try {
               $m = $data['month'];
               $dupQ = $db->table('hk_monthly_indents')->where('branch_id', $data['branch_id'])->where('id <>', $id);
               $monthAlt = null;
               if (preg_match('/^\d{4}-\d{2}$/', $m)) {
                    $dt = \DateTime::createFromFormat('Y-m', $m);
                    if ($dt) $monthAlt = $dt->format('M-Y');
               } elseif (\DateTime::createFromFormat('M-Y', $m)) {
                    $dt = \DateTime::createFromFormat('M-Y', $m);
                    if ($dt) $monthAlt = $dt->format('Y-m');
               }
               if ($monthAlt) {
                    $dupQ->groupStart()->where('month', $m)->orWhere('month', $monthAlt)->groupEnd();
               } else {
                    $dupQ->where('month', $m);
               }
               $existing = $dupQ->get()->getRowArray();
               if ($existing) {
                    return $this->respond(['message' => 'Another indent for this branch and month already exists', 'id' => $existing['id'] ?? null, 'status' => $existing['status'] ?? null], 409);
               }
          } catch (\Exception $e) {
               log_message('error', 'Duplicate check (update) failed: ' . $e->getMessage());
          }

          // calculate total amount for the indent (require price per item in payload or assume 0)
          $totalAmount = 0.0;
          foreach ($data['items'] as $it) {
               $qty = floatval($it['qty_requested'] ?? 0);
               $price = isset($it['price']) ? floatval($it['price']) : 0.0;
               $totalAmount += ($qty * $price);
          }

          // check branch budget (if exists)
          $budgetRow = $db->table('hk_branchwise_budget')->select('budget')->where('branch_id', $data['branch_id'])->get()->getRowArray();
          if ($budgetRow && $totalAmount > floatval($budgetRow['budget'])) {
               return $this->respond(['message' => 'Total amount exceeds branch budget', 'budget' => $budgetRow['budget'], 'total' => number_format($totalAmount, 2, '.', '')], 400);
          }

          $db->transStart();
          try {
               $indents = new HkMonthlyIndentsModel();
               $itemsModel = new HkMonthlyIndentItemsModel();

               // update indent header
               $header = [
                    'branch_id' => $data['branch_id'],
                    'month' => $data['month'],
                    'notes' => $data['notes'] ?? null,
                    'total_amount' => number_format($totalAmount, 2, '.', ''),
               ];

               // If this indent was rejected and is being edited by HK_SUPERVISOR, mark it back to pending and clear rejection remarks
               if ($indent['status'] === 'rejected' && isset($user->role) && $user->role === 'HK_SUPERVISOR') {
                    $header['status'] = 'pending';
                    $header['rejection_remarks'] = null;
               }

               try {
                    $indents->update($id, $header);
               } catch (\Exception $e) {
                    log_message('error', 'Update failed: ' . $e->getMessage());
                    return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
               }

               // remove existing items and re-insert
               $db->table('hk_monthly_indent_items')->where('indent_id', $id)->delete();

               // fetch item metadata (brand/unit) to use as fallback
               $itemIds = array_values(array_unique(array_map(function ($i) {
                    return intval($i['hk_item_id']);
               }, $data['items'])));

               $itemMeta = [];
               if (!empty($itemIds)) {
                    $rows = $db->table('hk_items')->select('id, brand, unit')->whereIn('id', $itemIds)->get()->getResultArray();
                    foreach ($rows as $r) $itemMeta[$r['id']] = $r;
               }

               foreach ($data['items'] as $it) {
                    $hk_item_id = intval($it['hk_item_id']);
                    $qty = floatval($it['qty_requested'] ?? 0);
                    $price = isset($it['price']) ? floatval($it['price']) : 0.0;
                    $total = $qty * $price;
                    $brand = $it['brand'] ?? ($itemMeta[$hk_item_id]['brand'] ?? null);
                    $unit = $it['unit'] ?? ($itemMeta[$hk_item_id]['unit'] ?? null);

                    $itemsModel->insert([
                         'indent_id' => $id,
                         'hk_item_id' => $hk_item_id,
                         'qty_requested' => $qty,
                         'price' => $price,
                         'total_amount' => $total,
                         'brand' => $brand,
                         'unit' => $unit,
                         'remarks' => $it['remarks'] ?? null,
                    ]);
               }

               $db->transComplete();
               return $this->respond(['message' => 'Indent updated', 'id' => $id], 200);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function list()
     {
          $user = $this->validateAuthorizationNew();
          $branch_id = $this->request->getGet('branch_id');
          $month = $this->request->getGet('month');
          $status = $this->request->getGet('status');

          $db = \Config\Database::connect();

          // build base query selecting indents
          $builder = $db->table('hk_monthly_indents as ind')
               ->select('ind.*')
               ->orderBy('ind.created_at', 'DESC');

          // Role-based filtering: HK_SUPERVISOR can only see indents for their assigned branches
          if (isset($user->role) && $user->role === 'HK_SUPERVISOR') {
               // Fetch assigned branches for this supervisor from user_map table
               $supervisorData = $db->table('user_map')
                    ->select('branches')
                    ->where('emp_code', $user->emp_code)
                    ->where('role', 'HK_SUPERVISOR')
                    ->get()
                    ->getRowArray();

               if (!$supervisorData || empty($supervisorData['branches'])) {
                    // If supervisor has no assigned branches, return empty result
                    return $this->respond([], 200);
               }

               // branches field is comma-separated string like "23,35,44"
               $assignedBranchIds = array_filter(array_map('trim', explode(',', $supervisorData['branches'])));

               if (empty($assignedBranchIds)) {
                    // If supervisor has no branches after normalization, return empty result
                    return $this->respond([], 200);
               }

               $builder->whereIn('ind.branch_id', $assignedBranchIds);
          }

          if ($branch_id) $builder->where('ind.branch_id', $branch_id);
          if ($month) {
               // Accept both YYYY-MM and Mon-YYYY (e.g. Jan-2026) formats when filtering
               $monthNormalized = $month;
               $monthAlt = null;
               if (preg_match('/^\d{4}-\d{2}$/', $month)) {
                    $dt = \DateTime::createFromFormat('Y-m', $month);
                    if ($dt) $monthAlt = $dt->format('M-Y');
               } elseif (\DateTime::createFromFormat('M-Y', $month)) {
                    $dt = \DateTime::createFromFormat('M-Y', $month);
                    if ($dt) $monthAlt = $dt->format('Y-m');
               }

               if ($monthAlt) {
                    $builder->groupStart()->where('ind.month', $monthNormalized)->orWhere('ind.month', $monthAlt)->groupEnd();
               } else {
                    $builder->where('ind.month', $month);
               }
          }
          if ($status) $builder->where('ind.status', $status);

          $rows = $builder->get()->getResultArray();

          // Check if each indent has receipts in hk_stock_receipts table
          foreach ($rows as &$r) {
               $receiptCount = $db->table('hk_stock_receipts as sr')
                    ->join('hk_stock_receipt_items as sri', 'sri.receipt_id = sr.id', 'inner')
                    ->join('hk_monthly_indent_items as mii', 'mii.id = sri.indent_item_id', 'left')
                    ->where('mii.indent_id', $r['id'])
                    ->countAllResults();
               $r['has_receipts'] = ($receiptCount > 0);
          }
          unset($r);

          // try to resolve branch names from secondary DB (preferred source)
          try {
               $db2 = \Config\Database::connect('secondary');
               $branchData = $db2->table('Branches')->select('id, SysField')->get()->getResultArray();
               $branchMap = [];
               foreach ($branchData as $b) {
                    $branchMap[(string)($b['id'] ?? $b['branch_id'] ?? '')] = trim($b['SysField'] ?? '');
               }

               // attach branch_name when available from secondary DB map
               foreach ($rows as &$r) {
                    $bid = (string)($r['branch_id'] ?? '');
                    if (isset($branchMap[$bid]) && $branchMap[$bid] !== '') {
                         $r['branch_name'] = $branchMap[$bid];
                    } else {
                         // fallback: keep whatever branch_name exists or null
                         $r['branch_name'] = $r['branch_name'] ?? null;
                    }
               }
               unset($r);

               // collect requested_by emp_codes and fetch names from secondary DB new_emp_master
               $empCodes = array_values(array_unique(array_filter(array_map(function ($x) {
                    return isset($x['requested_by']) ? (string)$x['requested_by'] : null;
               }, $rows))));

               $employeeData = [];
               if (!empty($empCodes)) {
                    try {
                         $employees = $db2->table('new_emp_master')
                              ->select('emp_code, comp_name, designation_name, dept_name')
                              ->whereIn('emp_code', $empCodes)
                              ->get()->getResultArray();
                         $employeeData = array_column($employees, null, 'emp_code');
                    } catch (\Exception $e) {
                         log_message('error', 'Failed to load employee data from secondary DB: ' . $e->getMessage());
                    }
               }

               foreach ($rows as &$r) {
                    $req = (string)($r['requested_by'] ?? '');
                    if ($req && isset($employeeData[$req])) {
                         $r['requested_by_name'] = $employeeData[$req]['comp_name'] ?? $req;
                    } else {
                         $r['requested_by_name'] = $r['requested_by'] ?? null;
                    }
               }
               unset($r);
          } catch (\Exception $e) {
               // if secondary DB fails, leave $rows as-is; caller will still get indents
               // log for debugging
               log_message('error', 'Failed to load branch map from secondary DB: ' . $e->getMessage());
          }

          return $this->respond($rows, 200);
     }

     public function get($id = null)
     {
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);
          $db = \Config\Database::connect();
          $indent = $db->table('hk_monthly_indents')->where('id', $id)->get()->getRowArray();
          if (!$indent) return $this->respond(['message' => 'Not found'], 404);

          // prefer item snapshot fields (brand/unit) from indent items, fallback to master item fields
          $items = $db->table('hk_monthly_indent_items as it')
               ->select('it.*, i.name as item_name, COALESCE(it.unit, i.unit) as unit, COALESCE(it.brand, i.brand) as brand')
               ->join('hk_items i', 'i.id = it.hk_item_id', 'left')
               ->where('it.indent_id', $id)
               ->get()->getResultArray();

          // For each item, compute aggregate qty_received from receipts (in case column is missing or stale)
          foreach ($items as &$it) {
               try {
                    $receivedSum = (float) $db->table('hk_stock_receipt_items')
                         ->select('COALESCE(SUM(received_qty),0) as total')
                         ->where('indent_item_id', $it['id'])
                         ->get()->getRow()->total;
               } catch (\Exception $e) {
                    $receivedSum = 0.0;
               }

               // prefer stored qty_received if present, otherwise use computed sum
               if (!isset($it['qty_received']) || $it['qty_received'] === null) {
                    $it['qty_received'] = $receivedSum;
               } else {
                    // cast to float for consistency
                    $it['qty_received'] = (float)$it['qty_received'];
               }
          }
          unset($it);

          $indent['items'] = $items;

          // mark whether this indent has any received items (use receipts table as authoritative)
          try {
               $receiptCount = $db->table('hk_stock_receipts as sr')
                    ->join('hk_stock_receipt_items as sri', 'sri.receipt_id = sr.id', 'inner')
                    ->join('hk_monthly_indent_items as mii', 'mii.id = sri.indent_item_id', 'left')
                    ->where('mii.indent_id', $id)
                    ->countAllResults();
               $indent['has_receipts'] = ($receiptCount > 0);
          } catch (\Exception $e) {
               $indent['has_receipts'] = false;
          }

          // Attempt to enrich with branch name and requester name from secondary DB
          try {
               $db2 = \Config\Database::connect('secondary');
               // Branch name
               if (!empty($indent['branch_id'])) {
                    $b = $db2->table('Branches')->select('SysField, id')->where('id', $indent['branch_id'])->get()->getRowArray();
                    if ($b && !empty($b['SysField'])) {
                         $indent['branch_name'] = trim($b['SysField']);
                    } else {
                         $indent['branch_name'] = $indent['branch_name'] ?? null;
                    }
               }

               // Requested by name
               $req = (string)($indent['requested_by'] ?? '');
               if ($req !== '') {
                    try {
                         $emp = $db2->table('new_emp_master')->select('emp_code, comp_name')->where('emp_code', $req)->get()->getRowArray();
                         if ($emp && !empty($emp['comp_name'])) {
                              $indent['requested_by_name'] = $emp['comp_name'];
                         } else {
                              $indent['requested_by_name'] = $indent['requested_by'] ?? null;
                         }
                    } catch (\Exception $e) {
                         // ignore employee lookup failure
                         $indent['requested_by_name'] = $indent['requested_by'] ?? null;
                    }
               }

               // Approved / Rejected by name (if actioned)
               $actionBy = (string)($indent['approved_by'] ?? '');
               if ($actionBy !== '') {
                    try {
                         $emp2 = $db2->table('new_emp_master')->select('emp_code, comp_name')->where('emp_code', $actionBy)->get()->getRowArray();
                         if ($emp2 && !empty($emp2['comp_name'])) {
                              $indent['approved_by_name'] = $emp2['comp_name'];
                         } else {
                              $indent['approved_by_name'] = $indent['approved_by'] ?? null;
                         }
                    } catch (\Exception $e) {
                         $indent['approved_by_name'] = $indent['approved_by'] ?? null;
                    }
               }
          } catch (\Exception $e) {
               // if secondary db fails, ignore and return raw indent
               log_message('error', 'Failed to enrich indent details from secondary DB: ' . $e->getMessage());
          }

          return $this->respond($indent, 200);
     }

     public function approve($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);
          $data = $this->request->getJSON(true);
          $db = \Config\Database::connect();
          $db->transStart();
          try {
               // $data expected: items => [{id, qty_approved}]
               if (!empty($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $it) {
                         $db->table('hk_monthly_indent_items')->where('id', $it['id'])->update(['qty_approved' => $it['qty_approved']]);
                    }
               }
               $db->table('hk_monthly_indents')->where('id', $id)->update([
                    'status' => 'approved',
                    'approved_by' => $user->emp_code,
                    'approved_at' => date('Y-m-d H:i:s')
               ]);
               $db->transComplete();
               return $this->respond(['message' => 'Indent approved'], 200);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function reject($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);

          $data = $this->request->getJSON(true) ?? [];
          $remarks = $data['remarks'] ?? null;

          try {
               $db = \Config\Database::connect();
               $update = [
                    'status' => 'rejected',
                    'approved_by' => $user->emp_code,
                    'approved_at' => date('Y-m-d H:i:s')
               ];
               if (!empty($remarks)) $update['rejection_remarks'] = $remarks;

               try {
                    $db->table('hk_monthly_indents')->where('id', $id)->update($update);
               } catch (\Exception $e) {
                    log_message('error', 'Reject update failed: ' . $e->getMessage());
                    return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
               }
               return $this->respond(['message' => 'Indent rejected', 'id' => $id], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }
}

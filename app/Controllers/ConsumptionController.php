<?php


namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkStockBalancesModel; // added

class ConsumptionController extends BaseController
{
     use ResponseTrait;
     public function recordCycleConsumption()
     {
          $user = $this->validateAuthorization();
          $data = $this->request->getJSON(true);
          $db = \Config\Database::connect();

          // basic validation
          $branchId = isset($data['branch_id']) ? (int)$data['branch_id'] : 0;
          $cycleNo = isset($data['cycle_no']) ? (int)$data['cycle_no'] : 0;
          $submit = !empty($data['submit']);
          $month = isset($data['month']) ? trim($data['month']) : null; // optional YYYY-MM

          if ($month && !preg_match('/^\d{4}-\d{2}$/', $month)) {
               return $this->respond(['message' => 'Invalid month format (expected YYYY-MM)'], 400);
          }

          if (!$branchId || $cycleNo < 1 || $cycleNo > 3 || empty($data['items']) || !is_array($data['items'])) {
               return $this->respond(['message' => 'Invalid payload'], 400);
          }

          // Prevent adding if cycle already submitted/locked (for the same month if provided)
          try {
               $qb = $db->table('hk_consumptions')->where(['branch_id' => $branchId, 'cycle_no' => $cycleNo, 'locked' => 'Y']);
               if ($month) {
                    // prefer recorded_at if exists, otherwise created_at
                    try {
                         $hasRecordedAt = (bool)$db->query("SHOW COLUMNS FROM hk_consumptions LIKE 'recorded_at'")->getNumRows();
                    } catch (\Exception $e) {
                         $hasRecordedAt = false;
                    }
                    $dateCol = $hasRecordedAt ? 'recorded_at' : 'created_at';
                    if ($dateCol) $qb->where("DATE_FORMAT({$dateCol}, '%Y-%m') = '" . $db->escapeString($month) . "'");
               }
               $lockedCount = $qb->countAllResults();
               if ($lockedCount > 0) {
                    return $this->respond(['message' => 'This cycle has already been submitted and locked'], 409);
               }
          } catch (\Exception $e) {
               // proceed, but log
               log_message('error', 'recordCycleConsumption - lock check failed: ' . $e->getMessage());
          }

          $db->transStart();
          try {
               $balancesModel = new HkStockBalancesModel(); // use model

               // Remove any previous draft (unlocked) entries for this branch+cycle so we replace them with new values
               try {
                    $qbDel = $db->table('hk_consumptions')->where(['branch_id' => $branchId, 'cycle_no' => $cycleNo, 'locked' => 'N']);
                    if ($month) {
                         try {
                              $hasRecordedAt = (bool)$db->query("SHOW COLUMNS FROM hk_consumptions LIKE 'recorded_at'")->getNumRows();
                         } catch (\Exception $e) {
                              $hasRecordedAt = false;
                         }
                         $dateCol = $hasRecordedAt ? 'recorded_at' : 'created_at';
                         if ($dateCol) $qbDel->where("DATE_FORMAT({$dateCol}, '%Y-%m') = '" . $db->escapeString($month) . "'");
                    }
                    $qbDel->delete();
               } catch (\Exception $e) {
                    log_message('error', 'recordCycleConsumption - Failed to remove previous drafts: ' . $e->getMessage());
               }

               // determine recorded_at value for inserts when month provided
               $recordedAtValue = null;
               if ($month) {
                    // map cycle start days: 1 -> 01, 2 -> 11, 3 -> 21
                    $startDay = $cycleNo === 2 ? '11' : ($cycleNo === 3 ? '21' : '01');
                    $recordedAtValue = $month . '-' . $startDay . ' 00:00:00';
               }

               foreach ($data['items'] as $it) {
                    $insertData = [
                         'branch_id' => $branchId,
                         'hk_item_id' => $it['hk_item_id'],
                         'cycle_no' => $cycleNo,
                         'consumed_qty' => $it['consumed_qty'],
                         'recorded_by' => $user->emp_code,
                         'locked' => $submit ? 'Y' : 'N'
                    ];
                    if ($recordedAtValue) $insertData['recorded_at'] = $recordedAtValue;

                    $db->table('hk_consumptions')->insert($insertData);
                    $balancesModel->recalcBalance((int)$branchId, (int)$it['hk_item_id']);
               }

               $db->transComplete();
               return $this->respond(['message' => 'Consumption recorded', 'locked' => $submit ? true : false], 201);
          } catch (\Exception $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function getCycle($branchId = null, $cycleNo = null)
     {
          if (!$branchId || !$cycleNo) return $this->respond(['message' => 'Missing parameters'], 400);
          $branchId = (int)$branchId;
          $cycleNo = (int)$cycleNo;
          if ($cycleNo < 1 || $cycleNo > 3) return $this->respond(['message' => 'Invalid cycle number'], 400);

          $db = \Config\Database::connect();
          try {
               // determine if cycle is locked (any locked rows)
               $cycleLocked = (bool)$db->table('hk_consumptions')->where(['branch_id' => $branchId, 'cycle_no' => $cycleNo, 'locked' => 'Y'])->countAllResults();

               // fetch item list with existing consumed qty, last recorder and current balance
               // Detect if hk_consumptions has a timestamp column to support month filtering
               $hasRecordedAt = false;
               $hasCreatedAt = false;
               try {
                    $r1 = $db->query("SHOW COLUMNS FROM hk_consumptions LIKE 'recorded_at'");
                    $hasRecordedAt = ($r1 && $r1->getNumRows() > 0);
               } catch (\Exception $e) {
                    $hasRecordedAt = false;
               }
               try {
                    $r2 = $db->query("SHOW COLUMNS FROM hk_consumptions LIKE 'created_at'");
                    $hasCreatedAt = ($r2 && $r2->getNumRows() > 0);
               } catch (\Exception $e) {
                    $hasCreatedAt = false;
               }

               $dateCol = $hasRecordedAt ? 'recorded_at' : ($hasCreatedAt ? 'created_at' : null);
               $month = $this->request->getGet('month');
               if ($month && !preg_match('/^\d{4}-\d{2}$/', $month)) $month = null; // ignore invalid

               if ($dateCol && $month) {
                    // filter for the requested month
                    $sql = "SELECT i.id as hk_item_id, i.name, i.brand, i.unit,
                         COALESCE((SELECT SUM(c.consumed_qty) FROM hk_consumptions c WHERE c.branch_id = ? AND c.cycle_no = ? AND c.hk_item_id = i.id AND DATE_FORMAT(c.{$dateCol}, '%Y-%m') = ?),0) as consumed_qty,
                         (SELECT c1.recorded_by FROM hk_consumptions c1 WHERE c1.branch_id = ? AND c1.cycle_no = ? AND c1.hk_item_id = i.id AND DATE_FORMAT(c1.{$dateCol}, '%Y-%m') = ? ORDER BY c1.{$dateCol} DESC, c1.id DESC LIMIT 1) as last_recorded_by,
                         (SELECT c1.{$dateCol} FROM hk_consumptions c1 WHERE c1.branch_id = ? AND c1.cycle_no = ? AND c1.hk_item_id = i.id AND DATE_FORMAT(c1.{$dateCol}, '%Y-%m') = ? ORDER BY c1.{$dateCol} DESC, c1.id DESC LIMIT 1) as last_recorded_at,
                         COALESCE((SELECT ideal_qty FROM hk_branch_ideal_qty q WHERE q.branch_id = ? AND q.hk_item_id = i.id), 0) as ideal_qty,
                         COALESCE(sb.current_balance, 0) as balance
                         FROM hk_items i
                         LEFT JOIN hk_stock_balances sb ON sb.hk_item_id = i.id AND sb.branch_id = ?
                         ORDER BY i.name ASC";

                    $params = [$branchId, $cycleNo, $month, $branchId, $cycleNo, $month, $branchId, $cycleNo, $month, $branchId, $branchId];
               } elseif ($dateCol) {
                    $sql = "SELECT i.id as hk_item_id, i.name, i.brand, i.unit,
                         COALESCE((SELECT SUM(c.consumed_qty) FROM hk_consumptions c WHERE c.branch_id = ? AND c.cycle_no = ? AND c.hk_item_id = i.id),0) as consumed_qty,
                         (SELECT c1.recorded_by FROM hk_consumptions c1 WHERE c1.branch_id = ? AND c1.cycle_no = ? AND c1.hk_item_id = i.id ORDER BY c1.{$dateCol} DESC, c1.id DESC LIMIT 1) as last_recorded_by,
                         (SELECT c1.{$dateCol} FROM hk_consumptions c1 WHERE c1.branch_id = ? AND c1.cycle_no = ? AND c1.hk_item_id = i.id ORDER BY c1.{$dateCol} DESC, c1.id DESC LIMIT 1) as last_recorded_at,
                         COALESCE((SELECT ideal_qty FROM hk_branch_ideal_qty q WHERE q.branch_id = ? AND q.hk_item_id = i.id), 0) as ideal_qty,
                         COALESCE(sb.current_balance, 0) as balance
                         FROM hk_items i
                         LEFT JOIN hk_stock_balances sb ON sb.hk_item_id = i.id AND sb.branch_id = ?
                         ORDER BY i.name ASC";

                    $params = [$branchId, $cycleNo, $branchId, $cycleNo, $branchId, $cycleNo, $branchId, $branchId];
               } else {
                    // No timestamp columns we can use; fall back to id ordering and null timestamps
                    $sql = "SELECT i.id as hk_item_id, i.name, i.brand, i.unit,
                         COALESCE((SELECT SUM(c.consumed_qty) FROM hk_consumptions c WHERE c.branch_id = ? AND c.cycle_no = ? AND c.hk_item_id = i.id),0) as consumed_qty,
                         (SELECT c1.recorded_by FROM hk_consumptions c1 WHERE c1.branch_id = ? AND c1.cycle_no = ? AND c1.hk_item_id = i.id ORDER BY c1.id DESC LIMIT 1) as last_recorded_by,
                         NULL as last_recorded_at,
                         COALESCE((SELECT ideal_qty FROM hk_branch_ideal_qty q WHERE q.branch_id = ? AND q.hk_item_id = i.id), 0) as ideal_qty,
                         COALESCE(sb.current_balance, 0) as balance
                         FROM hk_items i
                         LEFT JOIN hk_stock_balances sb ON sb.hk_item_id = i.id AND sb.branch_id = ?
                         ORDER BY i.name ASC";

                    $params = [$branchId, $cycleNo, $branchId, $cycleNo, $branchId, $branchId];
               }

               $items = $db->query($sql, $params)->getResultArray();

               // get last draft save (unlocked) and last locked submission info; honor month filter when provided
               if ($dateCol) {
                    $ldq = $db->table('hk_consumptions')->select("recorded_by, {$dateCol} as created_at")->where(['branch_id' => $branchId, 'cycle_no' => $cycleNo, 'locked' => 'N']);
                    $lkq = $db->table('hk_consumptions')->select("recorded_by, {$dateCol} as created_at")->where(['branch_id' => $branchId, 'cycle_no' => $cycleNo, 'locked' => 'Y']);
                    if ($month) {
                         $ldq->where("DATE_FORMAT({$dateCol}, '%Y-%m') = '" . $db->escapeString($month) . "'");
                         $lkq->where("DATE_FORMAT({$dateCol}, '%Y-%m') = '" . $db->escapeString($month) . "'");
                    }
                    $lastDraft = $ldq->orderBy($dateCol, 'DESC')->get()->getRowArray();
                    $lastLocked = $lkq->orderBy($dateCol, 'DESC')->get()->getRowArray();
               } else {
                    // Use id ordering and set created_at to null for compatibility
                    $lastDraft = $db->table('hk_consumptions')->select('recorded_by, id')->where(['branch_id' => $branchId, 'cycle_no' => $cycleNo, 'locked' => 'N'])->orderBy('id', 'DESC')->get()->getRowArray();
                    $lastLocked = $db->table('hk_consumptions')->select('recorded_by, id')->where(['branch_id' => $branchId, 'cycle_no' => $cycleNo, 'locked' => 'Y'])->orderBy('id', 'DESC')->get()->getRowArray();
                    if (!empty($lastDraft)) $lastDraft['created_at'] = null;
                    if (!empty($lastLocked)) $lastLocked['created_at'] = null;
               }

               // attempt to enrich emp names from secondary DB (new_emp_master)
               try {
                    $db2 = \Config\Database::connect('secondary');
                    $codes = [];
                    foreach ($items as $it) {
                         if (!empty($it['last_recorded_by'])) $codes[] = (string)$it['last_recorded_by'];
                    }
                    if (!empty($lastDraft['recorded_by'])) $codes[] = (string)$lastDraft['recorded_by'];
                    if (!empty($lastLocked['recorded_by'])) $codes[] = (string)$lastLocked['recorded_by'];
                    $codes = array_values(array_unique(array_filter($codes)));

                    $empMap = [];
                    if (!empty($codes)) {
                         try {
                              $employees = $db2->table('new_emp_master')->select('emp_code, comp_name')->whereIn('emp_code', $codes)->get()->getResultArray();
                              foreach ($employees as $e) $empMap[(string)$e['emp_code']] = $e['comp_name'];
                         } catch (\Exception $e) {
                              log_message('error', 'getCycle - Failed to load employee names: ' . $e->getMessage());
                         }
                    }

                    // attach names
                    foreach ($items as &$it) {
                         $rb = isset($it['last_recorded_by']) ? (string)$it['last_recorded_by'] : null;
                         $it['last_recorded_name'] = $rb && isset($empMap[$rb]) ? $empMap[$rb] : ($rb ?? null);
                    }
                    unset($it);

                    $cycleLastSavedBy = $lastDraft['recorded_by'] ?? null;
                    $cycleLockedBy = $lastLocked['recorded_by'] ?? null;
                    $cycleLastSavedAt = $lastDraft['created_at'] ?? null;
                    $cycleLockedAt = $lastLocked['created_at'] ?? null;

                    $cycleLastSavedName = $cycleLastSavedBy && isset($empMap[$cycleLastSavedBy]) ? $empMap[$cycleLastSavedBy] : ($cycleLastSavedBy ?? null);
                    $cycleLockedName = $cycleLockedBy && isset($empMap[$cycleLockedBy]) ? $empMap[$cycleLockedBy] : ($cycleLockedBy ?? null);
               } catch (\Exception $e) {
                    log_message('error', 'getCycle - Enrichment failed: ' . $e->getMessage());
                    // fall back
                    $cycleLastSavedBy = $lastDraft['recorded_by'] ?? null;
                    $cycleLockedBy = $lastLocked['recorded_by'] ?? null;
                    $cycleLastSavedAt = $lastDraft['created_at'] ?? null;
                    $cycleLockedAt = $lastLocked['created_at'] ?? null;
                    $cycleLastSavedName = $cycleLastSavedBy;
                    $cycleLockedName = $cycleLockedBy;
               }

               return $this->respond([
                    'branch_id' => $branchId,
                    'cycle_no' => $cycleNo,
                    'month' => $month ?? null,
                    'cycle_locked' => $cycleLocked,
                    'cycle_last_saved_by' => $cycleLastSavedBy ?? null,
                    'cycle_last_saved_name' => $cycleLastSavedName ?? null,
                    'cycle_last_saved_at' => $cycleLastSavedAt ?? null,
                    'cycle_locked_by' => $cycleLockedBy ?? null,
                    'cycle_locked_name' => $cycleLockedName ?? null,
                    'cycle_locked_at' => $cycleLockedAt ?? null,
                    'items' => $items
               ], 200);
          } catch (\Exception $e) {
               log_message('error', 'getCycle failed: ' . $e->getMessage());
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }
}

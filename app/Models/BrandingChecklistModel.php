<?php

namespace App\Models;

use CodeIgniter\Model;

class BrandingChecklistModel extends Model
{
     protected $table = 'branding_checklists';
     protected $primaryKey = 'id';
     protected $returnType = 'array';
     protected $allowedFields = [
          'branch_id',
          'centre_name',
          'location',
          'date_of_visit',
          'visit_time',
          'audited_by',
          'branch_manager',
          'cluster_manager',
          'contact',
          'notes',
          'status',
          'created_by'
     ];
     // Server-side datatable listing
     public function datatableList($start, $length, $search = '', $orderCol = null, $orderDir = 'desc', $columns = [])
     {
          $db = \Config\Database::connect();
          $builder = $db->table('branding_checklists as c');

          // basic search
          if ($search) {
               $builder->groupStart()
                    ->like('c.centre_name', $search)
                    ->orLike('c.location', $search)
                    ->orLike('c.created_by', $search)
                    ->groupEnd();
          }

          $total = $db->table('branding_checklists')->countAllResults(false); // don't reset builder
          $filtered = $builder->countAllResults(false);

          // order
          if ($orderCol !== null && isset($columns[$orderCol])) {
               $orderName = $columns[$orderCol]['data'] ?? 'c.date_of_visit';
               $builder->orderBy($orderName, $orderDir);
          } else {
               $builder->orderBy('c.date_of_visit', 'desc');
          }

          $rows = $builder->select('c.*')->limit($length, $start)->get()->getResultArray();

          // Attach creator name from secondary employee master so UI can show
          // "Created By" as a human-readable name instead of just emp_code.
          if (!empty($rows)) {
               $codes = array_values(array_unique(array_filter(array_column($rows, 'created_by'))));
               if (!empty($codes)) {
                    $db2 = \Config\Database::connect('secondary');
                    $empRows = $db2->table('new_emp_master')
                         ->select('emp_code, fname, lname')
                         ->whereIn('emp_code', $codes)
                         ->get()
                         ->getResultArray();

                    $empMap = [];
                    foreach ($empRows as $e) {
                         $empMap[$e['emp_code']] = $e;
                    }

                    foreach ($rows as &$r) {
                         $code = $r['created_by'] ?? null;
                         if ($code && isset($empMap[$code])) {
                              $fname = $empMap[$code]['fname'] ?? '';
                              $lname = $empMap[$code]['lname'] ?? '';
                              $full = trim($fname . ' ' . $lname);
                              $r['created_by_name'] = $full ? ($full . ' (' . $code . ')') : $code;
                         } else {
                              $r['created_by_name'] = $code;
                         }
                    }
                    unset($r);
               }
          }

          return ['total' => $total, 'filtered' => $filtered, 'rows' => $rows];
     }

     public function getChecklistWithRelations(int $id)
     {
          $db = \Config\Database::connect();
          $chk = $db->table('branding_checklists')->where('id', $id)->get()->getRowArray();
          if (!$chk) return null;

          // Backwards compatibility: include old items for any existing integrations
          $chk['items'] = $db->table('branding_checklist_items')->where('checklist_id', $id)->get()->getResultArray();

          // Preferred new structure: records with optional section/sub section references
          $chk['records'] = $db->table('branding_checklist_records')->where('branding_checklist_id', $id)->orderBy('id', 'asc')->get()->getResultArray();

          $chk['photos'] = $db->table('branding_photos')->where('checklist_id', $id)->get()->getResultArray();
          $chk['actions'] = $db->table('branding_actions')->where('checklist_id', $id)->get()->getResultArray();
          return $chk;
     }

     public function createChecklist(array $payload, $user)
     {
          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $data = [
                    'branch_id' => $payload['branch_id'] ?? null,
                    'centre_name' => $payload['centre_name'] ?? null,
                    'location' => $payload['location'] ?? null,
                    'date_of_visit' => $payload['date_of_visit'] ?? null,
                    'visit_time' => $payload['visit_time'] ?? null,
                    'audited_by' => $payload['audited_by'] ?? null,
                    'branch_manager' => $payload['branch_manager'] ?? null,
                    'cluster_manager' => $payload['cluster_manager'] ?? null,
                    'contact' => $payload['contact'] ?? null,
                    'notes' => $payload['notes'] ?? null,
                    'created_by' => $user->emp_code ?? $user->username ?? null,
                    'status' => $payload['status'] ?? 'draft'
               ];


               $db->table('branding_checklists')->insert($data);
               $id = (int)$db->insertID();

               // records (preferred)
               if (!empty($payload['records']) && is_array($payload['records'])) {
                    foreach ($payload['records'] as $rec) {
                         $db->table('branding_checklist_records')->insert([
                              'branding_checklist_id' => $id,
                              'section_id' => $rec['section_id'] ?? null,
                              'sub_section_id' => $rec['sub_section_id'] ?? null,
                              'input_name' => $rec['input_name'] ?? null,
                              'input_value' => $rec['input_value'] ?? null,
                              'input_remark' => $rec['input_remark'] ?? null,
                              'created_by' => $rec['created_by'] ?? null,
                              'created_dtm' => $rec['created_dtm'] ?? null,
                         ]);
                    }
               } elseif (!empty($payload['items']) && is_array($payload['items'])) {
                    // Backwards compatibility: translate old items -> records
                    foreach ($payload['items'] as $it) {
                         $db->table('branding_checklist_records')->insert([
                              'branding_checklist_id' => $id,
                              'section_id' => null,
                              'sub_section_id' => null,
                              'input_name' => $it['label'] ?? $it['item_label'] ?? null,
                              'input_value' => $it['response'] ?? null,
                              'input_remark' => $it['remarks'] ?? null,
                              'created_by' => null,
                              'created_dtm' => date('Y-m-d H:i:s')
                         ]);
                    }
               }

               // actions
               if (!empty($payload['actions']) && is_array($payload['actions'])) {
                    foreach ($payload['actions'] as $a) {
                         $db->table('branding_actions')->insert([
                              'checklist_id' => $id,
                              'action_text' => $a['text'] ?? null,
                              'priority' => $a['priority'] ?? 'low',
                              'target_date' => $a['target_date'] ?? null,
                              'assigned_to' => $a['assigned_to'] ?? null
                         ]);
                    }
               }

               $db->transComplete();
               return $id;
          } catch (\Exception $e) {
               $db->transRollback();
               throw $e;
          }
     }

     public function updateChecklist(int $id, array $payload)
     {
          $db = \Config\Database::connect();
          $db->transStart();
          try {
               $db->table('branding_checklists')->where('id', $id)->update([
                    'branch_id' => $payload['branch_id'] ?? null,
                    'centre_name' => $payload['centre_name'] ?? null,
                    'location' => $payload['location'] ?? null,
                    'date_of_visit' => $payload['date_of_visit'] ?? null,
                    'visit_time' => $payload['visit_time'] ?? null,
                    'audited_by' => $payload['audited_by'] ?? null,
                    'branch_manager' => $payload['branch_manager'] ?? null,
                    'cluster_manager' => $payload['cluster_manager'] ?? null,
                    'contact' => $payload['contact'] ?? null,
                    'notes' => $payload['notes'] ?? null,
                    'status' => $payload['status'] ?? 'draft'
               ]);

               // optional: replace records/actions (simple approach: delete existing, insert new)
               if (isset($payload['records'])) {
                    $db->table('branding_checklist_records')->where('branding_checklist_id', $id)->delete();
                    foreach ($payload['records'] as $rec) {
                         $db->table('branding_checklist_records')->insert([
                              'branding_checklist_id' => $id,
                              'section_id' => $rec['section_id'] ?? null,
                              'sub_section_id' => $rec['sub_section_id'] ?? null,
                              'input_name' => $rec['input_name'] ?? null,
                              'input_value' => $rec['input_value'] ?? null,
                              'input_remark' => $rec['input_remark'] ?? null,
                              'created_by' => $rec['created_by'] ?? null,
                              'created_dtm' => $rec['created_dtm'] ?? null,
                         ]);
                    }
               } elseif (isset($payload['items'])) {
                    // Backwards compatibility: replace old items -> records
                    $db->table('branding_checklist_records')->where('branding_checklist_id', $id)->delete();
                    foreach ($payload['items'] as $it) {
                         $db->table('branding_checklist_records')->insert([
                              'branding_checklist_id' => $id,
                              'section_id' => null,
                              'sub_section_id' => null,
                              'input_name' => $it['label'] ?? $it['item_label'] ?? null,
                              'input_value' => $it['response'] ?? null,
                              'input_remark' => $it['remarks'] ?? null,
                              'created_by' => null,
                              'created_dtm' => date('Y-m-d H:i:s')
                         ]);
                    }
               }

               if (isset($payload['actions'])) {
                    $db->table('branding_actions')->where('checklist_id', $id)->delete();
                    foreach ($payload['actions'] as $a) {
                         $db->table('branding_actions')->insert([
                              'checklist_id' => $id,
                              'action_text' => $a['text'] ?? null,
                              'priority' => $a['priority'] ?? 'low',
                              'target_date' => $a['target_date'] ?? null,
                              'assigned_to' => $a['assigned_to'] ?? null
                         ]);
                    }
               }

               $db->transComplete();
               return true;
          } catch (\Exception $e) {
               $db->transRollback();
               throw $e;
          }
     }

     public function deleteChecklist(int $id)
     {
          return (bool)$this->delete($id); // uses Model::delete -> cascades will remove items/phots/actions
     }

     // Photos helpers
     public function addPhoto(int $checklistId, string $filename, ?string $caption = null, ?float $latitude = null, ?float $longitude = null, ?int $accuracy = null, ?string $geo_dtm = null)
     {
          $db = \Config\Database::connect();
          $data = ['checklist_id' => $checklistId, 'filename' => $filename, 'caption' => $caption];
          if (!is_null($latitude)) $data['latitude'] = $latitude;
          if (!is_null($longitude)) $data['longitude'] = $longitude;
          if (!is_null($accuracy)) $data['accuracy'] = $accuracy;
          if (!is_null($geo_dtm)) $data['geo_dtm'] = $geo_dtm;

          // Only insert fields that actually exist in the table (backwards compatible)
          try {
               $fields = $db->getFieldNames('branding_photos');
               $filtered = [];
               foreach ($data as $k => $v) {
                    if (in_array($k, $fields)) $filtered[$k] = $v;
               }
               if (empty($filtered)) return false;
               return $db->table('branding_photos')->insert($filtered);
          } catch (\Throwable $e) {
               // In case getting field names fails, fall back to original insert attempt (best-effort)
               try {
                    return $db->table('branding_photos')->insert($data);
               } catch (\Throwable $e2) {
                    log_message('error', 'addPhoto insert failed: ' . $e2->getMessage());
                    return false;
               }
          }
     }

     public function getPhotos(int $checklistId)
     {
          $db = \Config\Database::connect();
          return $db->table('branding_photos')->where('checklist_id', $checklistId)->orderBy('id', 'asc')->get()->getResultArray();
     }

     /**
      * Delete a photo row by filename for a checklist and remove the file from disk when possible.
      * Returns true if at least one record was deleted.
      */
     public function deletePhotos(int $checklistId, array $filenames = [])
     {
          if (empty($filenames)) return false;
          $db = \Config\Database::connect();

          $rows = $db->table('branding_photos')->where('checklist_id', $checklistId)->whereIn('filename', $filenames)->get()->getResultArray();
          if (empty($rows)) return false;

          // delete DB rows
          $db->table('branding_photos')->where('checklist_id', $checklistId)->whereIn('filename', $filenames)->delete();

          // remove files from disk if present
          $uploadPath = WRITEPATH . 'uploads/branding_photos/';
          foreach ($rows as $r) {
               $fn = $r['filename'] ?? null;
               if ($fn) {
                    $path = $uploadPath . $fn;
                    try {
                         if (is_file($path)) @unlink($path);
                    } catch (\Throwable $e) {
                         // ignore file deletion failures
                    }
               }
          }

          return true;
     }

     /**
      * Aggregate dashboard counts for the given branch and date range.
      * Returns keys expected by the front-end: total_visits, photos_count, actions_total, actions_high, actions_medium, actions_low, checklists, recent
      */
     public function getDashboardCounts($branch = '', $startDate = null, $endDate = null)
     {
          $db = \Config\Database::connect();

          if (empty($startDate)) $startDate = date('Y-m-01');
          if (empty($endDate)) $endDate = date('Y-m-t');

          // Total visits / checklists
          $totalQb = $db->table('branding_checklists as c')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate);
          if ($branch !== '' && $branch !== null) $totalQb->where('c.branch_id', $branch);
          $total_visits = (int)$totalQb->countAllResults(false);

          // Photos attached (join photos -> checklists filtered)
          $photosQb = $db->table('branding_photos as p')
               ->join('branding_checklists as c', 'c.id = p.checklist_id')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate);
          if ($branch !== '' && $branch !== null) $photosQb->where('c.branch_id', $branch);
          $photos = (int)$photosQb->countAllResults(false);

          // Actions total and by priority
          $actions_total = (int)$db->table('branding_actions as a')
               ->join('branding_checklists as c', 'c.id = a.checklist_id')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          $actions_high = (int)$db->table('branding_actions as a')
               ->join('branding_checklists as c', 'c.id = a.checklist_id')
               ->where('a.priority', 'high')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          $actions_medium = (int)$db->table('branding_actions as a')
               ->join('branding_checklists as c', 'c.id = a.checklist_id')
               ->where('a.priority', 'medium')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          $actions_low = (int)$db->table('branding_actions as a')
               ->join('branding_checklists as c', 'c.id = a.checklist_id')
               ->where('a.priority', 'low')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          // Some older checklists stored "action-like" responses as checklist records rather than
          // explicit rows in `branding_actions`. To help diagnose and correct missing action counts,
          // compute a conservative "inferred actions" count from checklist records where the
          // input name/value appears action-like (e.g. contains 'action' or 'priority' or has value
          // of high/medium/low).
          $inferred_actions_qb = $db->table('branding_checklist_records as r')
               ->join('branding_checklists as c', 'c.id = r.branding_checklist_id')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : []);

          // heuristic matching (case-insensitive): input_name contains 'action' OR contains 'priority' OR input_value in (high, medium, low)
          // Use a conservative approach and a small raw clause for value matching to ensure compatibility across DB drivers
          $inferred_actions_qb->groupStart()
               ->like('r.input_name', 'action')
               ->orLike('r.input_name', 'priority')
               ->orWhere("LOWER(r.input_value) IN ('high','medium','low')")
               ->groupEnd();

          $inferred_actions = (int)$inferred_actions_qb->countAllResults(false);

          // Also compute inferred counts by priority where possible (value is high/medium/low)
          $inferred_high = (int)$db->table('branding_checklist_records as r')
               ->join('branding_checklists as c', 'c.id = r.branding_checklist_id')
               ->where("LOWER(r.input_value) = 'high'")
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          $inferred_medium = (int)$db->table('branding_checklist_records as r')
               ->join('branding_checklists as c', 'c.id = r.branding_checklist_id')
               ->where("LOWER(r.input_value) = 'medium'")
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          $inferred_low = (int)$db->table('branding_checklist_records as r')
               ->join('branding_checklists as c', 'c.id = r.branding_checklist_id')
               ->where("LOWER(r.input_value) = 'low'")
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          // Additional heuristic: certain subsection input names are used to store action texts
          // e.g. 'immediate_action*' -> high priority, 'short_term_action*' -> medium, 'long_term_action*' -> low.
          // Count records with those name patterns and attribute them to inferred priority buckets.
          $extra_high = (int)$db->table('branding_checklist_records as r')
               ->join('branding_checklists as c', 'c.id = r.branding_checklist_id')
               ->like('r.input_name', 'immediate')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          $extra_medium = (int)$db->table('branding_checklist_records as r')
               ->join('branding_checklists as c', 'c.id = r.branding_checklist_id')
               ->groupStart()
               ->like('r.input_name', 'short')
               ->orLike('r.input_name', 'short_term')
               ->groupEnd()
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          $extra_low = (int)$db->table('branding_checklist_records as r')
               ->join('branding_checklists as c', 'c.id = r.branding_checklist_id')
               ->like('r.input_name', 'long')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->where($branch !== '' ? ['c.branch_id' => $branch] : [])
               ->countAllResults(false);

          // Add the extra heuristic counts into the inferred priority counts (avoid double counting if both conditions already matched)
          $inferred_high += $extra_high;
          $inferred_medium += $extra_medium;
          $inferred_low += $extra_low;

          // Add inferred priority counts to totals (non-destructive) and provide original DB-only counts too
          $actions_total_db = $actions_total;
          $actions_high_db = $actions_high;
          $actions_medium_db = $actions_medium;
          $actions_low_db = $actions_low;

          $actions_high = $actions_high + $inferred_high;
          $actions_medium = $actions_medium + $inferred_medium;
          $actions_low = $actions_low + $inferred_low;

          $actions_total = $actions_total + $inferred_actions;

          // Recent activity: latest checklists (most recent date_of_visit)
          $recent = $db->table('branding_checklists as c')
               ->select('c.id, c.centre_name as title, c.date_of_visit as date')
               ->where('c.date_of_visit >=', $startDate)
               ->where('c.date_of_visit <=', $endDate)
               ->orderBy('c.date_of_visit', 'desc')
               ->limit(10)
               ->get()->getResultArray();

          return [
               'total_visits' => $total_visits,
               'photos_count' => $photos,
               // keep DB-only counts available under *_db and set the top-level totals to include inferred items so UI shows corrected numbers
               'actions_total_db' => $actions_total_db,
               'actions_total' => $actions_total,
               'actions_high_db' => $actions_high_db,
               'actions_high' => $actions_high,
               'actions_medium_db' => $actions_medium_db,
               'actions_medium' => $actions_medium,
               'actions_low_db' => $actions_low_db,
               'actions_low' => $actions_low,
               'actions_inferred' => $inferred_actions,
               'actions_inferred_high' => $inferred_high,
               'actions_inferred_medium' => $inferred_medium,
               'actions_inferred_low' => $inferred_low,
               'checklists' => $total_visits,
               'recent' => $recent
          ];
     }
     // Sections/Subsections helpers
     public function getSections()
     {
          $db = \Config\Database::connect();
          return $db->table('branding_sections')->orderBy('section_id', 'asc')->get()->getResultArray();
     }

     public function createSection(string $name)
     {
          $db = \Config\Database::connect();
          return $db->table('branding_sections')->insert(['section_name' => $name]);
     }

     public function getSubSections($sectionId = null)
     {
          $db = \Config\Database::connect();
          $qb = $db->table('branding_sub_sections');
          if (!is_null($sectionId)) $qb->where('section_id', (int)$sectionId);
          return $qb->orderBy('sub_section_id', 'asc')->get()->getResultArray();
     }

     public function createSubSection($sectionId, string $name)
     {
          $db = \Config\Database::connect();
          return $db->table('branding_sub_sections')->insert(['sub_section_name' => $name, 'section_id' => $sectionId]);
     }

     /**
      * Find branch manager mapping in user_map for a given branch id.
      * Returns null if not found or an array with emp_code, fname, lname, mobile, role
      */
     public function getBranchManagerByBranchId($branchId)
     {
          $db = \Config\Database::connect();

          // 1) Try to find a user mapped to this branch with an explicit branch role (avoid matching ZONAL_MANAGER etc.)
          $sql = "SELECT * FROM user_map WHERE FIND_IN_SET(?, branches) AND (role = 'BRANCH_MANAGER' OR role = 'BM' OR role LIKE '%BRANCH%') LIMIT 1";
          $row = $db->query($sql, [(int)$branchId])->getRowArray();

          // 2) If not found, try a broader match but avoid matching generic 'MANAGER' entries that are not branch-specific
          if (!$row) {
               $sql = "SELECT * FROM user_map WHERE FIND_IN_SET(?, branches) LIMIT 1";
               $row = $db->query($sql, [(int)$branchId])->getRowArray();
          }

          if (!$row) return null;

          // fetch employee details from secondary DB (new_emp_master)
          $db2 = \Config\Database::connect('secondary');
          $emp = $db2->table('new_emp_master')->select('emp_code, fname, lname, mobile')->where('emp_code', $row['emp_code'])->get()->getRowArray();

          return [
               'emp_code' => $row['emp_code'],
               'role' => $row['role'] ?? null,
               'fname' => $emp['fname'] ?? null,
               'lname' => $emp['lname'] ?? null,
               'mobile' => $emp['mobile'] ?? null
          ];
     }

     /**
      * Get employee details from secondary DB by emp_code
      */
     public function getEmployeeByEmpCode($empCode)
     {
          $db2 = \Config\Database::connect('secondary');
          $emp = $db2->table('new_emp_master')->select('emp_code, fname, lname, mobile')->where('emp_code', $empCode)->get()->getRowArray();
          if (!$emp) return null;
          return [
               'emp_code' => $emp['emp_code'],
               'fname' => $emp['fname'] ?? null,
               'lname' => $emp['lname'] ?? null,
               'mobile' => $emp['mobile'] ?? null
          ];
     }

     /**
      * Find cluster manager mapping in user_map for a given branch id.
      * Returns null if not found or an array with emp_code, fname, lname, mobile, role
      */
     public function getClusterManagerByBranchId($branchId)
     {
          $db = \Config\Database::connect();

          // 1) Try to find a user mapped to this branch with an explicit cluster role
          $sql = "SELECT * FROM user_map WHERE FIND_IN_SET(?, branches) AND (role = 'CLUSTER_MANAGER' OR role = 'CM' OR role LIKE '%CLUSTER%') LIMIT 1";
          $row = $db->query($sql, [(int)$branchId])->getRowArray();

          // 2) If not found, try a broader match for any user mapped to this branch
          if (!$row) {
               $sql = "SELECT * FROM user_map WHERE FIND_IN_SET(?, branches) LIMIT 1";
               $row = $db->query($sql, [(int)$branchId])->getRowArray();
          }

          if (!$row) return null;

          // fetch employee details from secondary DB (new_emp_master)
          $db2 = \Config\Database::connect('secondary');
          $emp = $db2->table('new_emp_master')->select('emp_code, fname, lname, mobile')->where('emp_code', $row['emp_code'])->get()->getRowArray();

          return [
               'emp_code' => $row['emp_code'],
               'role' => $row['role'] ?? null,
               'fname' => $emp['fname'] ?? null,
               'lname' => $emp['lname'] ?? null,
               'mobile' => $emp['mobile'] ?? null
          ];
     }
}

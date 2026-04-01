<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormRecordsModel;
use App\Models\FormSubmissionsModel;
use App\Models\BrandingChecklistModel;
use App\Models\LogsModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class DynamicFormController extends BaseController
{
     use ResponseTrait;

     protected $formRecordsModel;
     protected $formSubmissionsModel;

     public function __construct()
     {
          $this->formRecordsModel = model(FormRecordsModel::class);
          $this->formSubmissionsModel = model(FormSubmissionsModel::class);
     }

     private function uuidV4(): string
     {
          $data = random_bytes(16);
          $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
          $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);

          $hex = bin2hex($data);
          return sprintf(
               '%s-%s-%s-%s-%s',
               substr($hex, 0, 8),
               substr($hex, 8, 4),
               substr($hex, 12, 4),
               substr($hex, 16, 4),
               substr($hex, 20, 12)
          );
     }

     private function readPayload(): array
     {
          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }
          if (!is_array($payload)) {
               $payload = [];
          }
          return $payload;
     }

     private function tableHasColumn(string $table, string $column): bool
     {
          try {
               $db = \Config\Database::connect();
               $cols = $db->getFieldNames($table);
               return in_array($column, $cols, true);
          } catch (\Throwable $e) {
               return false;
          }
     }

     private function toStringValue($value): string
     {
          if ($value === null || $value === false) {
               return '';
          }
          if (is_array($value) || is_object($value)) {
               return (string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
          }
          return (string) $value;
     }

     private function usesSubmissionTable(): bool
     {
          // New schema mode ONLY when all of these are true:
          // - parent table exists
          // - form_records has numeric submission_id
          // - form_records also has submission_uuid (so we can still store the UUID)
          return $this->tableHasColumn('form_submissions', 'id')
               && $this->tableHasColumn('form_records', 'submission_id')
               && $this->tableHasColumn('form_records', 'submission_uuid');
     }

     // Backwards-compatible endpoint
     public function save()
     {
          return $this->create();
     }

     // DataTable / listing (server-side)
     public function list()
     {
          $this->validateAuthorizationNew();
          $request = $this->request;
          $start = (int) $request->getGet('start') ?: 0;
          $length = (int) $request->getGet('length') ?: 25;
          $search = $request->getGet('search')['value'] ?? '';
          $formId = (int) ($request->getGet('form_id') ?? 0);

          $orderCol = $request->getGet('order')[0]['column'] ?? null;
          $orderDir = $request->getGet('order')[0]['dir'] ?? 'desc';
          $columns = $request->getGet('columns') ?? [];

          // Optional: include detailed `form_records` for each submission when explicitly requested
          $includeRecordsParam = $request->getGet('include_records') ?? $request->getGet('includeRecords') ?? null;
          $includeRecords = false;
          if ($includeRecordsParam !== null) {
               $val = strtolower(trim((string)$includeRecordsParam));
               $includeRecords = ($val === '1' || $val === 'true' || $val === 'yes');
          }

          try {
               if ($this->usesSubmissionTable()) {
                    $result = $this->formSubmissionsModel->datatableListFiltered($start, $length, (string) $search, $orderCol, (string) $orderDir, (array) $columns, $formId > 0 ? $formId : null);

                    // Attach `form_records` only when explicitly requested (reduces payload)
                    if (!empty($includeRecords)) {
                         $rows = $result['rows'] ?? [];
                         $submissionIds = [];
                         foreach ($rows as $r) {
                              if (isset($r['id']) && ctype_digit((string)$r['id'])) $submissionIds[] = (int)$r['id'];
                         }

                         if (!empty($submissionIds)) {
                              $db = \Config\Database::connect();
                              try {
                                   $recs = $db->table('form_records')
                                        ->whereIn('submission_id', $submissionIds)
                                        ->orderBy('id', 'asc')
                                        ->get()
                                        ->getResultArray();

                                   // Enrich records with input metadata (input_name, input_label)
                                   $inputIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['input_id']) ? (int)$r['input_id'] : 0;
                                   }, $recs))));
                                   $metaByInputId = [];
                                   if (!empty($inputIds)) {
                                        try {
                                             $inputs = $db->table('form_inputs')->select('id,input_name,input_label')->whereIn('id', $inputIds)->get()->getResultArray();
                                             foreach ($inputs as $i) {
                                                  $metaByInputId[(int)$i['id']] = $i;
                                             }
                                        } catch (\Throwable $__m) {
                                             // non-fatal: continue without labels
                                        }
                                   }

                                   // Enrich with section/sub-section names for readability
                                   $sectionIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['section_id']) ? (int)$r['section_id'] : 0;
                                   }, $recs))));
                                   $subSectionIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                                   }, $recs))));

                                   $sectionMap = [];
                                   if (!empty($sectionIds)) {
                                        try {
                                             $secs = $db->table('form_sections')->select('section_id,section_name')->whereIn('section_id', $sectionIds)->get()->getResultArray();
                                             foreach ($secs as $s) $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                                        } catch (\Throwable $__s) { /* non-fatal */
                                        }
                                   }

                                   $subSectionMap = [];
                                   if (!empty($subSectionIds)) {
                                        try {
                                             $subs = $db->table('form_sub_sections')->select('sub_section_id,sub_section_name')->whereIn('sub_section_id', $subSectionIds)->get()->getResultArray();
                                             foreach ($subs as $ss) $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                                        } catch (\Throwable $__ss) { /* non-fatal */
                                        }
                                   }

                                   $grouped = [];
                                   foreach ($recs as $rec) {
                                        $sid = (int) ($rec['submission_id'] ?? 0);
                                        if ($sid <= 0) continue;

                                        $iid = isset($rec['input_id']) ? (int)$rec['input_id'] : 0;
                                        if ($iid && isset($metaByInputId[$iid])) {
                                             $rec['input_name'] = $metaByInputId[$iid]['input_name'] ?? null;
                                             $rec['input_label'] = $metaByInputId[$iid]['input_label'] ?? null;
                                        } else {
                                             $rec['input_name'] = $rec['input_name'] ?? null;
                                             $rec['input_label'] = $rec['input_label'] ?? null;
                                        }

                                        $secId = isset($rec['section_id']) ? (int)$rec['section_id'] : 0;
                                        $subId = isset($rec['sub_section_id']) ? (int)$rec['sub_section_id'] : 0;
                                        $rec['section_name'] = $secId && isset($sectionMap[$secId]) ? $sectionMap[$secId] : null;
                                        $rec['sub_section_name'] = $subId && isset($subSectionMap[$subId]) ? $subSectionMap[$subId] : null;

                                        if (!isset($grouped[$sid])) $grouped[$sid] = [];
                                        $grouped[$sid][] = $rec;
                                   }

                                   foreach ($rows as &$row) {
                                        $sid = isset($row['id']) ? (int)$row['id'] : 0;
                                        $row['records'] = $grouped[$sid] ?? [];
                                   }
                                   unset($row);
                                   $result['rows'] = $rows;
                              } catch (\Throwable $e) {
                                   // keep response shape even on error and log for debugging
                                   foreach ($rows as &$row) {
                                        $row['records'] = [];
                                   }
                                   unset($row);
                                   $result['rows'] = $rows;
                                   log_message('error', 'DynamicForm::list failed to attach form_records: ' . $e->getMessage());
                              }
                         } else {
                              // ensure key exists for consistency
                              foreach (($result['rows'] ?? []) as &$row) {
                                   $row['records'] = [];
                              }
                              unset($row);
                         }
                    }


                    return $this->respond([
                         'draw' => (int) $request->getGet('draw'),
                         'recordsTotal' => $result['total'],
                         'recordsFiltered' => $result['filtered'],
                         'data' => $result['rows'],
                    ], 200);
               }

               // Legacy fallback: group by submission_uuid/submission_id from form_records
               $db = \Config\Database::connect();
               $qb = $db->table('form_records as r');

               $hasUuid = $this->tableHasColumn('form_records', 'submission_uuid');
               $groupKey = $hasUuid ? 'r.submission_uuid' : 'r.submission_id';

               if ($formId > 0) {
                    $qb->where('r.form_id', $formId);
               }

               if (trim((string) $search) !== '') {
                    $s = trim((string) $search);
                    $qb->groupStart()
                         ->like($groupKey, $s)
                         ->orLike('r.created_by', $s)
                         ->orLike('r.form_id', $s)
                         ->orLike('r.dept_id', $s)
                         ->groupEnd();
               }

               // total submissions count (approx)
               $totalQb = $db->table('form_records as rr')->select($groupKey)->groupBy($groupKey);
               if ($formId > 0) {
                    $totalQb->where('rr.form_id', $formId);
               }
               $total = (int) $totalQb->countAllResults();

               // filtered count
               $qb->select($groupKey . ' as submission_key, MAX(r.created_dtm) as created_dtm, MAX(r.created_by) as created_by, MAX(r.form_id) as form_id, MAX(r.dept_id) as dept_id')
                    ->groupBy($groupKey);
               $filtered = (int) $qb->countAllResults(false);

               $qb->orderBy('created_dtm', 'desc');
               if ($length > 0) {
                    $qb->limit($length, max(0, $start));
               }
               $rows = $qb->get()->getResultArray();

               // If requested, attach full record rows for each legacy submission_key
               if (!empty($includeRecords) && !empty($rows)) {
                    try {
                         $submissionKeys = array_values(array_unique(array_map(function ($r) {
                              return (string) ($r['submission_key'] ?? '');
                         }, $rows)));
                         if (!empty($submissionKeys)) {
                              $grouped = [];
                              if ($hasUuid) {
                                   $recs = $db->table('form_records')->whereIn('submission_uuid', $submissionKeys)->orderBy('id', 'asc')->get()->getResultArray();
                                   // enrich input metadata
                                   $inputIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['input_id']) ? (int)$r['input_id'] : 0;
                                   }, $recs))));
                                   $metaByInputId = [];
                                   if (!empty($inputIds)) {
                                        try {
                                             $inputs = $db->table('form_inputs')->select('id,input_name,input_label')->whereIn('id', $inputIds)->get()->getResultArray();
                                             foreach ($inputs as $i) $metaByInputId[(int)$i['id']] = $i;
                                        } catch (\Throwable $__m) { /* ignore */
                                        }
                                   }
                                   // build section/sub-section name maps
                                   $sectionIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['section_id']) ? (int)$r['section_id'] : 0;
                                   }, $recs))));
                                   $subSectionIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                                   }, $recs))));
                                   $sectionMap = [];
                                   $subSectionMap = [];
                                   if (!empty($sectionIds)) {
                                        try {
                                             $secs = $db->table('form_sections')->select('section_id,section_name')->whereIn('section_id', $sectionIds)->get()->getResultArray();
                                             foreach ($secs as $s) $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                                        } catch (\Throwable $__s) { /* ignore */
                                        }
                                   }
                                   if (!empty($subSectionIds)) {
                                        try {
                                             $subs = $db->table('form_sub_sections')->select('sub_section_id,sub_section_name')->whereIn('sub_section_id', $subSectionIds)->get()->getResultArray();
                                             foreach ($subs as $ss) $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                                        } catch (\Throwable $__ss) { /* ignore */
                                        }
                                   }

                                   foreach ($recs as $rec) {
                                        $k = (string) ($rec['submission_uuid'] ?? '');
                                        if ($k === '') continue;

                                        $iid = isset($rec['input_id']) ? (int)$rec['input_id'] : 0;
                                        if ($iid && isset($metaByInputId[$iid])) {
                                             $rec['input_name'] = $metaByInputId[$iid]['input_name'] ?? null;
                                             $rec['input_label'] = $metaByInputId[$iid]['input_label'] ?? null;
                                        } else {
                                             $rec['input_name'] = $rec['input_name'] ?? null;
                                             $rec['input_label'] = $rec['input_label'] ?? null;
                                        }

                                        $secId = isset($rec['section_id']) ? (int)$rec['section_id'] : 0;
                                        $subId = isset($rec['sub_section_id']) ? (int)$rec['sub_section_id'] : 0;
                                        $rec['section_name'] = $secId && isset($sectionMap[$secId]) ? $sectionMap[$secId] : null;
                                        $rec['sub_section_name'] = $subId && isset($subSectionMap[$subId]) ? $subSectionMap[$subId] : null;

                                        if (!isset($grouped[$k])) $grouped[$k] = [];
                                        $grouped[$k][] = $rec;
                                   }
                              } else {
                                   // submission_id is numeric in legacy grouping
                                   $ids = array_map('intval', $submissionKeys);
                                   $recs = $db->table('form_records')->whereIn('submission_id', $ids)->orderBy('id', 'asc')->get()->getResultArray();

                                   // enrich input metadata
                                   $inputIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['input_id']) ? (int)$r['input_id'] : 0;
                                   }, $recs))));
                                   $metaByInputId = [];
                                   if (!empty($inputIds)) {
                                        try {
                                             $inputs = $db->table('form_inputs')->select('id,input_name,input_label')->whereIn('id', $inputIds)->get()->getResultArray();
                                             foreach ($inputs as $i) $metaByInputId[(int)$i['id']] = $i;
                                        } catch (\Throwable $__m) { /* ignore */
                                        }
                                   }

                                   // build section/sub-section name maps
                                   $sectionIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['section_id']) ? (int)$r['section_id'] : 0;
                                   }, $recs))));
                                   $subSectionIds = array_values(array_unique(array_filter(array_map(function ($r) {
                                        return isset($r['sub_section_id']) ? (int)$r['sub_section_id'] : 0;
                                   }, $recs))));
                                   $sectionMap = [];
                                   $subSectionMap = [];
                                   if (!empty($sectionIds)) {
                                        try {
                                             $secs = $db->table('form_sections')->select('section_id,section_name')->whereIn('section_id', $sectionIds)->get()->getResultArray();
                                             foreach ($secs as $s) $sectionMap[(int)$s['section_id']] = $s['section_name'] ?? null;
                                        } catch (\Throwable $__s) { /* ignore */
                                        }
                                   }
                                   if (!empty($subSectionIds)) {
                                        try {
                                             $subs = $db->table('form_sub_sections')->select('sub_section_id,sub_section_name')->whereIn('sub_section_id', $subSectionIds)->get()->getResultArray();
                                             foreach ($subs as $ss) $subSectionMap[(int)$ss['sub_section_id']] = $ss['sub_section_name'] ?? null;
                                        } catch (\Throwable $__ss) { /* ignore */
                                        }
                                   }

                                   foreach ($recs as $rec) {
                                        $k = (string) ($rec['submission_id'] ?? '');
                                        if ($k === '') continue;

                                        $iid = isset($rec['input_id']) ? (int)$rec['input_id'] : 0;
                                        if ($iid && isset($metaByInputId[$iid])) {
                                             $rec['input_name'] = $metaByInputId[$iid]['input_name'] ?? null;
                                             $rec['input_label'] = $metaByInputId[$iid]['input_label'] ?? null;
                                        } else {
                                             $rec['input_name'] = $rec['input_name'] ?? null;
                                             $rec['input_label'] = $rec['input_label'] ?? null;
                                        }

                                        $secId = isset($rec['section_id']) ? (int)$rec['section_id'] : 0;
                                        $subId = isset($rec['sub_section_id']) ? (int)$rec['sub_section_id'] : 0;
                                        $rec['section_name'] = $secId && isset($sectionMap[$secId]) ? $sectionMap[$secId] : null;
                                        $rec['sub_section_name'] = $subId && isset($subSectionMap[$subId]) ? $subSectionMap[$subId] : null;

                                        if (!isset($grouped[$k])) $grouped[$k] = [];
                                        $grouped[$k][] = $rec;
                                   }
                              }

                              foreach ($rows as &$row) {
                                   $key = (string) ($row['submission_key'] ?? '');
                                   $row['records'] = $grouped[$key] ?? [];
                              }
                              unset($row);
                         }
                    } catch (\Throwable $e) {
                         log_message('error', 'DynamicForm::list (legacy) failed to attach records: ' . $e->getMessage());
                         // leave rows as-is on error
                    }
               }

               return $this->respond([
                    'draw' => (int) $request->getGet('draw'),
                    'recordsTotal' => $total,
                    'recordsFiltered' => $filtered,
                    'data' => $rows,
               ], 200);
          } catch (\Throwable $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // Create a submission + insert records
     public function create()
     {
          $auth = $this->validateAuthorizationNew();
          if ($auth instanceof ResponseInterface) {
               return $auth;
          }

          $payload = $this->readPayload();

          $formId = (int) ($payload['form_id'] ?? 0);
          $deptId = (int) ($payload['dept_id'] ?? 0);
          $records = $payload['records'] ?? ($payload['inputs'] ?? null);

          if ($formId <= 0) {
               return $this->respond(['message' => 'form_id is required'], 400);
          }
          if ($deptId <= 0) {
               return $this->respond(['message' => 'dept_id is required'], 400);
          }
          if (!is_array($records) || empty($records)) {
               return $this->respond(['message' => 'records array is required'], 400);
          }

          $empCode = isset($auth->emp_code) ? trim((string) $auth->emp_code) : '';
          if ($empCode === '') {
               return $this->respond(['message' => 'Invalid token payload (emp_code missing)'], 401);
          }

          $createdDtm = isset($payload['created_dtm']) ? trim((string) $payload['created_dtm']) : '';
          if ($createdDtm === '') {
               $createdDtm = date('Y-m-d H:i:s');
          }

          $header = $payload['header'] ?? null;
          $status = isset($payload['status']) ? trim((string) $payload['status']) : 'draft';
          if ($status === '') {
               $status = 'draft';
          }

          $db = \Config\Database::connect();
          $db->transBegin();

          try {
               $useSubmissionTable = $this->usesSubmissionTable();

               $numericSubmissionId = null;
               $submissionUuid = null;
               $isUpdateFromCreate = false; // server-side safeguard flag

               if ($useSubmissionTable) {
                    // Server-side dedupe: if header includes branch_id + date_of_visit, try to find an existing
                    // submission for the same form_id + dept_id and treat this create as an update (defense-in-depth).
                    $foundExisting = false;
                    $existingRow = null;
                    if (is_array($header)) {
                         $matchBranch = isset($header['branch_id']) ? trim((string)$header['branch_id']) : '';
                         $matchDate = isset($header['date_of_visit']) ? trim((string)$header['date_of_visit']) : '';
                         if ($matchBranch !== '' && $matchDate !== '') {
                              try {
                                   $qb = $db->table('form_submissions')->select('*')->where('form_id', (int)$formId);
                                   if ($deptId > 0) $qb->where('dept_id', (int)$deptId);
                                   $qb->orderBy('id', 'DESC')->limit(50);
                                   $cands = $qb->get()->getResultArray();
                                   foreach ($cands as $cand) {
                                        $candHdr = [];
                                        if (!empty($cand['header'])) {
                                             $candHdr = is_array($cand['header']) ? $cand['header'] : (json_decode($cand['header'], true) ?: []);
                                        }
                                        if (
                                             isset($candHdr['branch_id']) && isset($candHdr['date_of_visit'])
                                             && (string)$candHdr['branch_id'] === (string)$matchBranch
                                             && (string)$candHdr['date_of_visit'] === (string)$matchDate
                                        ) {
                                             $foundExisting = true;
                                             $existingRow = $cand;
                                             break;
                                        }
                                   }
                              } catch (\Throwable $e) {
                                   log_message('debug', 'DynamicForm::create dedupe search failed: ' . $e->getMessage());
                              }
                         }
                    }

                    if ($foundExisting && $existingRow) {
                         // Convert to update: reuse existing numeric id/uuid and merge incoming header
                         $numericSubmissionId = (int)$existingRow['id'];
                         $submissionUuid = isset($existingRow['submission_uuid']) ? (string)$existingRow['submission_uuid'] : (string)$numericSubmissionId;

                         // Merge/preserve email_sent_events as in update()
                         $existingHdr = !empty($existingRow['header']) ? (is_array($existingRow['header']) ? $existingRow['header'] : (json_decode($existingRow['header'], true) ?: [])) : [];
                         $incomingHdr = is_array($header) ? $header : (json_decode((string)$header, true) ?: []);

                         $existingEvents = isset($existingHdr['email_sent_events']) && is_array($existingHdr['email_sent_events']) ? $existingHdr['email_sent_events'] : [];
                         $incomingEvents = isset($incomingHdr['email_sent_events']) && is_array($incomingHdr['email_sent_events']) ? $incomingHdr['email_sent_events'] : [];
                         $mergedEvents = array_values(array_unique(array_merge($existingEvents, $incomingEvents)));
                         if (!empty($mergedEvents)) $incomingHdr['email_sent_events'] = $mergedEvents;

                         $mergedHdr = array_merge($existingHdr, $incomingHdr);

                         $subUpdate = ['header' => $this->toStringValue($mergedHdr)];
                         if ($status !== '' && (!isset($existingRow['status']) || $existingRow['status'] !== $status)) {
                              $subUpdate['status'] = $status;
                         }
                         if (!empty($subUpdate)) {
                              $subUpdate['updated_dtm'] = date('Y-m-d H:i:s');
                              $subUpdate['updated_by'] = $empCode;
                              $db->table('form_submissions')->where('id', (int)$numericSubmissionId)->update($subUpdate);
                         }

                         // diagnostic log
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] create_detected_existing_submission: form_id={$formId} submission={$numericSubmissionId}\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }

                         $isUpdateFromCreate = true;
                    } else {
                         // No existing submission — create as before
                         $submissionUuid = $this->uuidV4();

                         $submissionRow = [
                              'submission_uuid' => $submissionUuid,
                              'form_id' => $formId,
                              'dept_id' => $deptId,
                              'header' => $header === null ? null : $this->toStringValue($header),
                              'status' => $status,
                              'created_by' => $empCode,
                              'created_dtm' => $createdDtm,
                         ];
                         if (!$this->tableHasColumn('form_submissions', 'submission_uuid')) {
                              unset($submissionRow['submission_uuid']);
                         }

                         $this->formSubmissionsModel->insert($submissionRow);
                         $numericSubmissionId = (int) $this->formSubmissionsModel->getInsertID();
                         if ($numericSubmissionId <= 0) {
                              throw new \RuntimeException('Failed to create submission');
                         }
                    }
               } else {
                    $submissionUuid = isset($payload['submission_id']) ? trim((string) $payload['submission_id']) : '';
                    if ($submissionUuid === '') {
                         $submissionUuid = $this->uuidV4();
                    }
               }

               $rows = [];
               $errors = [];

               foreach ($records as $i => $r) {
                    if (!is_array($r)) {
                         $errors[] = "records[$i] must be an object";
                         continue;
                    }

                    $sectionId = (int) ($r['section_id'] ?? 0);
                    $subSectionIdRaw = $r['sub_section_id'] ?? null;
                    $subSectionId = ($subSectionIdRaw === null || $subSectionIdRaw === '' || (int) $subSectionIdRaw <= 0)
                         ? null
                         : (int) $subSectionIdRaw;
                    $inputId = (int) ($r['input_id'] ?? 0);

                    if ($sectionId <= 0) {
                         $errors[] = "records[$i].section_id is required";
                    }
                    if ($inputId <= 0) {
                         $errors[] = "records[$i].input_id is required";
                    }

                    $row = [
                         'section_id' => $sectionId,
                         'sub_section_id' => $subSectionId,
                         'input_id' => $inputId,
                         'input_value' => $this->toStringValue($r['input_value'] ?? ''),
                         'dept_id' => $deptId,
                         'created_dtm' => $createdDtm,
                         'created_by' => $empCode,
                         'form_id' => $formId,
                    ];

                    if ($useSubmissionTable) {
                         $row['submission_id'] = $numericSubmissionId;
                         $row['submission_uuid'] = $submissionUuid;
                    } else {
                         if ($this->tableHasColumn('form_records', 'submission_uuid')) {
                              $row['submission_uuid'] = $submissionUuid;
                         } else {
                              $row['submission_id'] = $submissionUuid;
                         }
                    }

                    $rows[] = $row;
               }

               if (!empty($errors)) {
                    $db->transRollback();
                    return $this->respond(['message' => $errors], 400);
               }

               // Server-side required-field validation (mirror front-end isInputRequired logic)
               try {
                    $fim = model(\App\Models\FormInputsModel::class);
                    $inputsMeta = $fim->where('form_id', $formId)->findAll();

                    $metaById = [];
                    $metaByName = [];
                    foreach ($inputsMeta as $m) {
                         $metaById[(int)$m['id']] = $m;
                         $metaByName[(string)$m['input_name']] = $m;
                    }

                    // Build submitted values map by input_id (prefer last non-empty)
                    $submitted = [];
                    foreach ($records as $rec) {
                         $iid = (int) ($rec['input_id'] ?? 0);
                         if ($iid <= 0) continue;
                         $val = isset($rec['input_value']) ? (string) $rec['input_value'] : '';
                         if (!isset($submitted[$iid]) || trim((string)$val) !== '') {
                              $submitted[$iid] = $val;
                         }
                    }

                    $requiredErrors = [];
                    foreach ($inputsMeta as $m) {
                         if ((string)($m['status'] ?? 'A') !== 'A') continue;

                         $inputId = (int)$m['id'];

                         // Evaluate visibility using show_when_field/operator/value
                         $visible = true;
                         $whenField = trim((string) ($m['show_when_field'] ?? ''));
                         if ($whenField !== '') {
                              $ctrlMeta = $metaByName[$whenField] ?? null;
                              $ctrlVal = $ctrlMeta ? (string) ($submitted[(int)$ctrlMeta['id']] ?? '') : '';
                              $whenValue = trim((string) ($m['show_when_value'] ?? ''));
                              $op = trim((string) ($m['show_operator'] ?? '=')) ?: '=';

                              if ($whenValue === '') {
                                   $visible = $ctrlVal !== '';
                              } else {
                                   if ($op === '!=') $visible = $ctrlVal !== $whenValue;
                                   elseif ($op === '>') $visible = floatval($ctrlVal) > floatval($whenValue);
                                   elseif ($op === '<') $visible = floatval($ctrlVal) < floatval($whenValue);
                                   else $visible = $ctrlVal === $whenValue;
                              }
                         }

                         if (!$visible) continue;

                         // Determine if field is required
                         $isRequired = false;
                         if (!empty($m['input_required']) && (int)$m['input_required']) {
                              $isRequired = true;
                         } else {
                              $reqField = trim((string) ($m['required_when_field'] ?? ''));
                              if ($reqField !== '') {
                                   $ctrlMeta = $metaByName[$reqField] ?? null;
                                   $ctrlVal = $ctrlMeta ? (string) ($submitted[(int)$ctrlMeta['id']] ?? '') : '';
                                   $reqWhenValue = trim((string) ($m['required_when_value'] ?? ''));
                                   if ($reqWhenValue === '') {
                                        $isRequired = $ctrlVal !== '';
                                   } else {
                                        $isRequired = $ctrlVal === $reqWhenValue;
                                   }
                              }
                         }

                         if ($isRequired) {
                              $val = isset($submitted[$inputId]) ? trim((string)$submitted[$inputId]) : '';
                              if ($val === '') {
                                   $requiredErrors[] = sprintf("%s is required", $m['input_label'] ?: $m['input_name']);
                              }
                         }
                    }

                    if (!empty($requiredErrors)) {
                         $db->transRollback();
                         return $this->respond(['message' => $requiredErrors], 400);
                    }
               } catch (\Throwable $e) {
                    // Best-effort validation - log and continue to avoid blocking on unexpected schema issues
                    log_message('error', 'DynamicForm::create required-field validation failed: ' . $e->getMessage());
               }

               // If create() was converted into an update (server-side), purge existing records for this submission
               if (!empty($isUpdateFromCreate) && $useSubmissionTable && ctype_digit((string)$numericSubmissionId)) {
                    try {
                         $del = $db->table('form_records');
                         $del->where('submission_id', (int)$numericSubmissionId);
                         $del->delete();
                    } catch (\Throwable $__delEx) {
                         log_message('error', 'DynamicForm::create failed to purge existing records for submission ' . $numericSubmissionId . ': ' . $__delEx->getMessage());
                         $db->transRollback();
                         return $this->respond(['message' => 'Failed to replace existing records'], 500);
                    }
               }

               $ok = $this->formRecordsModel->insertBatch($rows);
               if ($ok === false) {
                    $db->transRollback();
                    return $this->respond([
                         'message' => 'Failed to save records',
                         'errors' => $this->formRecordsModel->errors(),
                    ], 500);
               }

               $db->transCommit();

               // Log submission create
               try {
                    $lm = new LogsModel();
                    $lm->insertLog([
                         'uri' => $this->request->getURI()->getPath(),
                         'method' => $this->request->getMethod(),
                         'params' => [
                              'form_id' => $formId,
                              'dept_id' => $deptId,
                              'inserted' => count($rows),
                              'submission_uuid' => $submissionUuid,
                              'submission_id' => $useSubmissionTable ? $numericSubmissionId : $submissionUuid,
                              'header' => $header,
                              'status' => $status,
                              'user_role' => $auth->role ?? null,
                         ],
                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => ($isUpdateFromCreate ? 'updateSubmission' : 'createSubmission'),
                         'entity_type' => 'form_submission',
                         'entity_id' => $useSubmissionTable ? (string)$numericSubmissionId : (string)$submissionUuid,
                         'user_id' => $empCode,
                    ]);
               } catch (\Throwable $e) {
                    log_message('error', 'DynamicForm::create log failed: ' . $e->getMessage());
               }

               $responsePayload = [
                    'status' => true,
                    'message' => 'Saved',
                    'inserted' => count($rows),
                    'created_dtm' => $createdDtm,
                    'submission_id' => $useSubmissionTable ? $numericSubmissionId : $submissionUuid,
                    'submission_uuid' => $submissionUuid,
               ];

               // Send response immediately, then attempt background email trigger (best-effort)
               $response = $this->respondCreated($responsePayload);
               try {
                    if (function_exists('fastcgi_finish_request')) {
                         fastcgi_finish_request();
                    }
               } catch (\Throwable $e) {
                    // ignore - continue to best-effort email send
               }

               // Attempt email trigger on CREATE if recipient context exists (best-effort)
               try {
                    $formIdForTrigger = (int)$formId;
                    $submissionIdForTrigger = $useSubmissionTable ? $numericSubmissionId : $submissionUuid;
                    $forceTrigger = !empty($payload['force']);
                    $skipTrigger = !empty($payload['skip_email_trigger']);

                    $hasRecipientContext = is_array($header) && (
                         !empty($header['branch_manager_email'])
                         || !empty($header['branch_email'])
                         || !empty($header['branch_id'])
                    );
                    $shouldTrigger = !$skipTrigger && ($forceTrigger || $hasRecipientContext);

                    if (!$shouldTrigger) {
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] create_skip_trigger_no_recipients: form_id={$formIdForTrigger} submission={$submissionIdForTrigger}\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }
                    } else {
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] create_invoking_trigger: form_id={$formIdForTrigger} submission={$submissionIdForTrigger} header=" . json_encode($header ?? null) . "\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }
                         $this->triggerSubmissionEmailOnSave($formIdForTrigger, $submissionIdForTrigger, $header ?? null, $createdDtm, $payload ?? []);
                    }
               } catch (\Throwable $e) {
                    log_message('error', 'DynamicForm::create trigger email failed: ' . $e->getMessage());
               }

               return $response;
          } catch (\Throwable $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // View a submission with its records
     public function show($submissionId)
     {
          $auth = $this->validateAuthorizationNew();
          if ($auth instanceof ResponseInterface) {
               return $auth;
          }

          $sid = is_string($submissionId) ? trim($submissionId) : (string) $submissionId;
          if ($sid === '') {
               return $this->respond(['message' => 'submission_id required'], 400);
          }

          $useSubmissionTable = $this->usesSubmissionTable();
          $db = \Config\Database::connect();

          $submission = null;
          if ($useSubmissionTable && ctype_digit($sid)) {
               $submission = $db->table('form_submissions')->where('id', (int) $sid)->get()->getRowArray();
          }

          $qb = $db->table('form_records')->orderBy('id', 'asc');
          if ($useSubmissionTable && ctype_digit($sid)) {
               $qb->where('submission_id', (int) $sid);
          } else {
               if ($this->tableHasColumn('form_records', 'submission_uuid')) {
                    $qb->where('submission_uuid', $sid);
               } else {
                    $qb->where('submission_id', $sid);
               }
          }

          $records = $qb->get()->getResultArray();
          if (!$submission && empty($records)) {
               return $this->respond(['message' => 'Not found'], 404);
          }

          // Fetch photos and group by caption to attach to records
          $photoModel = model(BrandingChecklistModel::class);
          $photosByCaption = [];
          try {
               $submissionIdForPhotos = null;
               if ($useSubmissionTable && ctype_digit($sid)) {
                    $submissionIdForPhotos = (int) $sid;
               } elseif (!empty($records)) {
                    // For legacy mode, try to get submission_id from first record
                    $submissionIdForPhotos = isset($records[0]['submission_id']) ? (int) $records[0]['submission_id'] : null;
               }

               if ($submissionIdForPhotos) {
                    $photos = $photoModel->getPhotos($submissionIdForPhotos);

                    // Group photos by caption (field key)
                    foreach ($photos as $photo) {
                         $caption = trim((string) ($photo['caption'] ?? ''));
                         if ($caption === '') continue;
                         if (!isset($photosByCaption[$caption])) {
                              $photosByCaption[$caption] = [];
                         }
                         $photosByCaption[$caption][] = $photo['filename'];
                    }
               }
          } catch (\Throwable $e) {
               log_message('error', 'DynamicForm::show failed to fetch photos: ' . $e->getMessage());
          }

          // Fetch form inputs metadata to build field keys
          $inputMetaMap = []; // input_id => field_key_slug
          if (!empty($records)) {
               try {
                    $formId = null;
                    if ($submission && isset($submission['form_id'])) {
                         $formId = (int) $submission['form_id'];
                    } elseif (isset($records[0]['form_id'])) {
                         $formId = (int) $records[0]['form_id'];
                    }

                    if ($formId) {
                         $inputs = $db->table('form_inputs')
                              ->where('form_id', $formId)
                              ->where('status', 'A')
                              ->get()
                              ->getResultArray();

                         foreach ($inputs as $inp) {
                              $inputId = (int) $inp['id'];
                              // Generate field key slug from input_name (matching frontend logic)
                              $name = trim((string) ($inp['input_name'] ?? ''));
                              if ($name !== '') {
                                   // Slugify: lowercase, replace non-alphanumeric with underscore
                                   $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $name));
                                   $slug = trim($slug, '_');
                                   $inputMetaMap[$inputId] = $slug;
                              }
                         }
                    }
               } catch (\Throwable $e) {
                    log_message('error', 'DynamicForm::show failed to fetch input metadata: ' . $e->getMessage());
               }
          }

          // Attach photos to records by matching field key patterns
          foreach ($records as &$record) {
               $record['attachments'] = '';

               $inputId = isset($record['input_id']) ? (int) $record['input_id'] : 0;
               $sectionId = isset($record['section_id']) ? (int) $record['section_id'] : 0;
               $subSectionId = isset($record['sub_section_id']) ? (int) $record['sub_section_id'] : 0;

               // Build possible field key patterns
               $possibleKeys = [];

               // Pattern 1: s{sectionId}__ss{subSectionId}__{fieldSlug}
               if ($sectionId && $subSectionId && isset($inputMetaMap[$inputId])) {
                    $possibleKeys[] = "s{$sectionId}__ss{$subSectionId}__{$inputMetaMap[$inputId]}";
               }

               // Pattern 2: {fieldSlug} (for ungrouped inputs)
               if (isset($inputMetaMap[$inputId])) {
                    $possibleKeys[] = $inputMetaMap[$inputId];
               }

               // Pattern 3: Check if input_value contains filenames (for file-type inputs)
               $inputValue = trim((string) ($record['input_value'] ?? ''));
               if ($inputValue !== '') {
                    $valueFiles = array_filter(array_map('trim', explode(',', $inputValue)));
                    // Try to match these filenames against any caption's photos
                    foreach ($photosByCaption as $caption => $filenames) {
                         foreach ($filenames as $filename) {
                              if (in_array($filename, $valueFiles, true)) {
                                   $record['attachments'] = $record['attachments']
                                        ? $record['attachments'] . ',' . $filename
                                        : $filename;
                              }
                         }
                    }
               }

               // Match against photos_by_caption using constructed field keys
               foreach ($possibleKeys as $key) {
                    if (isset($photosByCaption[$key]) && !empty($photosByCaption[$key])) {
                         $photos = implode(',', $photosByCaption[$key]);
                         $record['attachments'] = $record['attachments']
                              ? $record['attachments'] . ',' . $photos
                              : $photos;
                    }
               }

               // Remove duplicates
               if ($record['attachments']) {
                    $unique = array_unique(array_filter(explode(',', $record['attachments'])));
                    $record['attachments'] = implode(',', $unique);
               }
          }
          unset($record);

          return $this->respond([
               'data' => [
                    'submission' => $submission,
                    'records' => $records,
                    'photos_by_caption' => $photosByCaption, // Include for debugging/frontend use
               ],
          ], 200);
     }

     // Update header/status and (optionally) replace records
     public function update($submissionId)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof ResponseInterface) {
               return $user;
          }

          $sid = is_string($submissionId) ? trim($submissionId) : (string) $submissionId;
          if ($sid === '') {
               return $this->respond(['message' => 'submission_id required'], 400);
          }

          $payload = $this->readPayload();
          $records = $payload['records'] ?? null;
          $header = $payload['header'] ?? null;
          $status = isset($payload['status']) ? trim((string) $payload['status']) : null;

          $empCode = $user->emp_code ?? $user->username ?? '';
          $empCode = is_string($empCode) ? trim($empCode) : '';
          if ($empCode === '') {
               return $this->respond(['message' => 'Invalid token payload (emp_code missing)'], 401);
          }

          $role = $user->role ?? '';
          $isAdmin = in_array($role, ['SUPER_ADMIN', 'ADMIN'], true);

          $useSubmissionTable = $this->usesSubmissionTable();

          $db = \Config\Database::connect();
          $db->transBegin();

          try {
               if ($useSubmissionTable && ctype_digit($sid)) {
                    $sub = $db->table('form_submissions')->where('id', (int) $sid)->get()->getRowArray();
                    if (!$sub) {
                         $db->transRollback();
                         return $this->respond(['message' => 'Not found'], 404);
                    }
                    if (!$isAdmin && (string) ($sub['created_by'] ?? '') !== $empCode) {
                         $db->transRollback();
                         return $this->respond(['message' => 'Forbidden'], 403);
                    }

                    $stableSubmissionUuid = isset($sub['submission_uuid']) ? trim((string) $sub['submission_uuid']) : '';

                    $subUpdate = [];
                    $hasRealChange = false; // used below to decide whether to trigger
                    if ($header !== null) {
                         // Merge incoming header with existing DB header for submissions to avoid
                         // overwriting system-managed keys (notably `email_sent_events`) that
                         // are persisted by triggerSubmissionEmailOnSave() as durable idempotency.
                         if ($useSubmissionTable && isset($sub) && !empty($sub['header'])) {
                              $existingHdr = is_array($sub['header']) ? $sub['header'] : (json_decode($sub['header'], true) ?: []);
                              $incomingHdr = is_array($header) ? $header : (json_decode((string)$header, true) ?: []);

                              // Merge/preserve email_sent_events specifically
                              $existingEvents = isset($existingHdr['email_sent_events']) && is_array($existingHdr['email_sent_events']) ? $existingHdr['email_sent_events'] : [];
                              $incomingEvents = isset($incomingHdr['email_sent_events']) && is_array($incomingHdr['email_sent_events']) ? $incomingHdr['email_sent_events'] : [];
                              $mergedEvents = array_values(array_unique(array_merge($existingEvents, $incomingEvents)));
                              if (!empty($mergedEvents)) {
                                   $incomingHdr['email_sent_events'] = $mergedEvents;
                              }

                              // Merge, giving precedence to incoming values for user-supplied keys
                              $mergedHdr = array_merge($existingHdr, $incomingHdr);
                              $subUpdate['header'] = $this->toStringValue($mergedHdr);
                              // Check if header actually changed
                              if (json_encode($mergedHdr) !== json_encode($existingHdr)) {
                                   $hasRealChange = true;
                              }
                         } else {
                              $subUpdate['header'] = $this->toStringValue($header);
                              // Check if header actually changed
                              $existingHdr = is_array($sub['header']) ? $sub['header'] : (json_decode($sub['header'], true) ?: []);
                              $incomingHdr = is_array($header) ? $header : (json_decode((string)$header, true) ?: []);
                              if (json_encode($incomingHdr) !== json_encode($existingHdr)) {
                                   $hasRealChange = true;
                              }
                         }
                    }
                    if ($status !== null && $status !== '') {
                         if (!isset($sub['status']) || $sub['status'] !== $status) {
                              $subUpdate['status'] = $status;
                              $hasRealChange = true;
                         }
                    }
                    if ($hasRealChange) {
                         $subUpdate['updated_dtm'] = date('Y-m-d H:i:s');
                         $subUpdate['updated_by'] = $empCode;
                         $db->table('form_submissions')->where('id', (int) $sid)->update($subUpdate);
                    }
               }

               if ($records !== null) {
                    if (!is_array($records)) {
                         $db->transRollback();
                         return $this->respond(['message' => 'records must be an array'], 400);
                    }

                    $formId = (int) ($payload['form_id'] ?? 0);
                    $deptId = (int) ($payload['dept_id'] ?? 0);
                    if ($formId <= 0 || $deptId <= 0) {
                         $db->transRollback();
                         return $this->respond(['message' => 'form_id and dept_id are required when updating records'], 400);
                    }

                    $del = $db->table('form_records');
                    if ($useSubmissionTable && ctype_digit($sid)) {
                         $del->where('submission_id', (int) $sid);
                    } else {
                         if ($this->tableHasColumn('form_records', 'submission_uuid')) {
                              $del->where('submission_uuid', $sid);
                         } else {
                              $del->where('submission_id', $sid);
                         }
                    }
                    $del->delete();

                    $now = date('Y-m-d H:i:s');
                    $rows = [];
                    $errors = [];

                    foreach ($records as $i => $r) {
                         if (!is_array($r)) {
                              $errors[] = "records[$i] must be an object";
                              continue;
                         }

                         $sectionId = (int) ($r['section_id'] ?? 0);
                         $subSectionIdRaw = $r['sub_section_id'] ?? null;
                         $subSectionId = ($subSectionIdRaw === null || $subSectionIdRaw === '' || (int) $subSectionIdRaw <= 0)
                              ? null
                              : (int) $subSectionIdRaw;
                         $inputId = (int) ($r['input_id'] ?? 0);

                         if ($sectionId <= 0) {
                              $errors[] = "records[$i].section_id is required";
                         }
                         if ($inputId <= 0) {
                              $errors[] = "records[$i].input_id is required";
                         }

                         $row = [
                              'section_id' => $sectionId,
                              'sub_section_id' => $subSectionId,
                              'input_id' => $inputId,
                              'input_value' => $this->toStringValue($r['input_value'] ?? ''),
                              'dept_id' => $deptId,
                              'created_dtm' => $now,
                              'created_by' => $empCode,
                              'updated_dtm' => $now,
                              'updated_by' => $empCode,
                              'form_id' => $formId,
                         ];

                         if ($useSubmissionTable && ctype_digit($sid)) {
                              $row['submission_id'] = (int) $sid;
                              if ($this->tableHasColumn('form_records', 'submission_uuid') && $stableSubmissionUuid !== '') {
                                   $row['submission_uuid'] = $stableSubmissionUuid;
                              }
                         } else {
                              if ($this->tableHasColumn('form_records', 'submission_uuid')) {
                                   $row['submission_uuid'] = $sid;
                              } else {
                                   $row['submission_id'] = $sid;
                              }
                         }

                         $rows[] = $row;
                    }

                    if (!empty($errors)) {
                         $db->transRollback();
                         return $this->respond(['message' => $errors], 400);
                    }

                    $ok = $this->formRecordsModel->insertBatch($rows);
                    if ($ok === false) {
                         $db->transRollback();
                         return $this->respond(['message' => 'Failed to update records', 'errors' => $this->formRecordsModel->errors()], 500);
                    }
               }

               // Process deleted_photos if provided (array of filenames or mapping)
               if (!empty($payload['deleted_photos'])) {
                    try {
                         $filenames = [];
                         foreach ($payload['deleted_photos'] as $k => $v) {
                              if (is_array($v)) {
                                   foreach ($v as $fn) {
                                        $filenames[] = (string)$fn;
                                   }
                              } else {
                                   $filenames[] = (string)$v;
                              }
                         }
                         $filenames = array_values(array_filter(array_map('trim', $filenames)));
                         if (!empty($filenames) && ctype_digit((string)$sid)) {
                              $photoModel = model(\App\Models\BrandingChecklistModel::class);
                              $photoModel->deletePhotos((int)$sid, $filenames);
                         }
                    } catch (\Throwable $e) {
                         // non-fatal — log for diagnostics
                         log_message('error', 'Failed to delete photos for submission ' . $sid . ': ' . $e->getMessage());
                    }
               }

               $db->transCommit();

               // Log submission update
               try {
                    $lm = new LogsModel();
                    $lm->insertLog([
                         'uri' => $this->request->getURI()->getPath(),
                         'method' => $this->request->getMethod(),
                         'params' => ['submission_id' => $sid, 'header' => $header, 'status' => $status, 'user_role' => $user->role ?? null],
                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 200,
                         'action' => 'updateSubmission',
                         'entity_type' => 'form_submission',
                         'entity_id' => (string)$sid,
                         'user_id' => $empCode,
                    ]);
               } catch (\Throwable $e) {
                    log_message('error', 'DynamicForm::update log failed: ' . $e->getMessage());
               }

               $respPayload = ['message' => 'Updated', 'submission_id' => $sid];

               // Respond immediately then try background email trigger (best-effort)
               $response = $this->respond($respPayload, 200);
               try {
                    if (function_exists('fastcgi_finish_request')) fastcgi_finish_request();
               } catch (\Throwable $e) {
               }

               try {
                    $formIdForTrigger = (int) ($payload['form_id'] ?? 0);

                    // decide whether to run the trigger at all -- avoid spurious emails when
                    // the front-end fires update() without any real data change (e.g. retrying a
                    // save or writing the same header twice).
                    $newPdfUploaded = !empty($payload['pdf_file_id']) && (!is_array($header) || empty($header['pdf_file_id']));
                    // $hasRealChange was set earlier when header/status changed; default false
                    $shouldTrigger = ($hasRealChange ?? false) || ($records !== null) || $newPdfUploaded;

                    if (!$shouldTrigger) {
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] update_skip_trigger_no_changes: form_id={$formIdForTrigger} submission={$sid}\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                              // ignore
                         }
                    } else {
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] update_invoking_trigger: form_id={$formIdForTrigger} submission={$sid} header=" . json_encode($header ?? null) . "\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                              // ignore logging failures
                         }
                         $this->triggerSubmissionEmailOnSave($formIdForTrigger, $sid, $header ?? null, date('Y-m-d H:i:s'), $payload ?? []);
                    }
               } catch (\Throwable $e) {
                    log_message('error', 'DynamicForm::update trigger email failed: ' . $e->getMessage());
               }

               return $response;
          } catch (\Throwable $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     /**
      * Best-effort background email trigger for submissions that are bound to an email template.
      * - Finds email template by `form_id` (if any)
      * - Builds a small variables map for template replacement
      * - Attaches an uploaded frontend-generated PDF when `pdf_file_id` is provided in payload/header
      * - Sends to branch emails (branch_manager_email + branch_email) and lets the template's CCs apply
      * NOTE: failures are logged and do not affect submission success.
      */
     private function triggerSubmissionEmailOnSave(int $formId, $submissionId, $header = null, $createdDtm = null, array $payload = [])
     {
          try {
               if ($formId <= 0) return;

               // Track send outcome and lock artifacts across the whole flow.
               $emailSentOk = false;
               $cache = null;
               $cacheKey = '';
               $lockFile = '';

               $db = \Config\Database::connect();

               // Find template bound to this form_id
               $tmpl = $db->table('email_templates')->where('form_id', $formId)->get()->getRowArray();
               if (!$tmpl) return; // nothing to do

               // Debug: record trigger invocation and template lookup
               try {
                    $dbg = "[" . date('Y-m-d H:i:s') . "] triggerSubmissionEmailOnSave called: form_id={$formId} submission={$submissionId} template_event_key=" . ($tmpl['event_key'] ?? 'NULL') . "\n";
                    file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', $dbg, FILE_APPEND);
               } catch (\Throwable $__dbg) {
               }

               // UAT/dev helper: optionally bypass persisted dedupe checks.
               // Useful when UAT DB is cloned from live with historical sent markers.
               $ciEnv = strtolower(trim((string)(getenv('CI_ENVIRONMENT') ?: (defined('ENVIRONMENT') ? ENVIRONMENT : ''))));
               $appBase = strtolower(trim((string)(getenv('app.baseURL') ?: '')));
               $isPrivateHost = (bool) preg_match('#https?://(192\.168\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.)#', $appBase);
               $bypassRaw = strtolower(trim((string)(getenv('EMAIL_BYPASS_PERSISTED_DEDUPE') ?: '')));
               $bypassViaEnv = in_array($bypassRaw, ['1', 'true', 'yes', 'y', 'on'], true);
               $bypassPersistedDedupe = $bypassViaEnv || $ciEnv === 'development' || $isPrivateHost;

               if ($bypassPersistedDedupe) {
                    try {
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] persisted_dedupe_bypass_active: env={$ciEnv} app_base={$appBase}\n", FILE_APPEND);
                    } catch (\Throwable $__dbg) {
                    }
               }

               // Persistent idempotency: only send if updated_dtm is newer than last sent time for this event.
               // also track last pdf_file_id that was sent; if the header still contains the same
               // pdf id we can skip even if the row has been updated for unrelated reasons.
               try {
                    if ($this->usesSubmissionTable() && ctype_digit((string)$submissionId)) {
                         $subRow = $db->table('form_submissions')->select('header, updated_dtm')->where('id', (int)$submissionId)->get()->getRowArray();
                         $persistHeader = [];
                         if (!empty($subRow['header'])) {
                              $persistHeader = is_array($subRow['header']) ? $subRow['header'] : (json_decode($subRow['header'], true) ?: []);
                         }
                         $sentEvents = isset($persistHeader['email_sent_events']) && is_array($persistHeader['email_sent_events']) ? $persistHeader['email_sent_events'] : [];
                         $evt = (string)($tmpl['event_key'] ?? '');
                         $updatedDtm = $subRow['updated_dtm'] ?? null;
                         $lastSentTime = '';
                         if (isset($sentEvents[$evt])) {
                              $lastSentTime = $sentEvents[$evt];
                         } elseif (is_array($sentEvents) && in_array($evt, $sentEvents, true)) {
                              // legacy array format, treat as sent but no timestamp
                              $lastSentTime = '';
                         }

                         // check for PDF de-duplication: skip if the header already held the same file id
                         $currentPdf = null;
                         if (is_array($header) && !empty($header['pdf_file_id'])) {
                              $currentPdf = (string)$header['pdf_file_id'];
                         }
                         $sentPdf = isset($persistHeader['last_pdf_id']) ? (string)$persistHeader['last_pdf_id'] : null;
                         if (!$bypassPersistedDedupe && $currentPdf && $sentPdf && $currentPdf === $sentPdf) {
                              try {
                                   file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] persisted_skip_same_pdf: form_id={$formId} submission={$submissionId} pdf={$currentPdf}\n", FILE_APPEND);
                              } catch (\Throwable $__dbg) {
                              }
                              return;
                         }

                         // If lastSentTime exists and updated_dtm is not newer, skip sending
                         if (!$bypassPersistedDedupe && $evt !== '' && $lastSentTime && $updatedDtm && strtotime($updatedDtm) <= strtotime($lastSentTime)) {
                              try {
                                   file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] persisted_skip (not modified): form_id={$formId} submission={$submissionId} template={$evt} updated_dtm={$updatedDtm} last_sent={$lastSentTime}\n", FILE_APPEND);
                              } catch (\Throwable $__dbg) {
                              }
                              return;
                         }
                    }
               } catch (\Throwable $__persistErr) {
                    // non-fatal — continue to existing dedupe/cache logic
                    log_message('error', 'triggerSubmissionEmailOnSave: persistent-skip check failed: ' . $__persistErr->getMessage());
               }

               // Short-lived dedupe + PDF-aware defer: avoid duplicate sends and defer when template expects a PDF but none present
               $forceSend = !empty($payload['force']);
               // if this invocation is an update that just added a pdf_file_id when header had none,
               // we want to force the email send (so the updated header PDF is attached) even if recently
               // triggered. look at $header (persisted) vs $payload.
               if (!$forceSend && is_array($header) && !empty($payload['pdf_file_id']) && empty($header['pdf_file_id'])) {
                    $forceSend = true;
                    try {
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] forcing_send_due_to_new_pdf header_before=" . json_encode($header) . " payload_pdf=" . json_encode($payload['pdf_file_id']) . "\n", FILE_APPEND);
                    } catch (\Throwable $__dbg) {
                    }
               }

               // Heuristic: treat template as requiring PDF when template variables/html reference pdf_file_name
               $templateRefsPdf = (stripos($tmpl['html_template'] ?? '', '{{pdf_file_name') !== false)
                    || (stripos((string)($tmpl['variables'] ?? ''), 'pdf_file_name') !== false);

               // short-term dedupe for non-PDF templates: if we sent within the last minute and
               // the template doesn't need a PDF, skip to avoid create->update double send.
               $recentInterval = 60; // seconds
               if (!$forceSend && !$templateRefsPdf && !empty($evt) && $lastSentTime) {
                    $elapsed = time() - strtotime($lastSentTime);
                    if ($elapsed >= 0 && $elapsed < $recentInterval) {
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] short_term_skip: form_id={$formId} submission={$submissionId} template={$evt} elapsed={$elapsed}s\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }
                         return;
                    }
               }

               // Quick check whether the frontend already supplied a PDF id/name in payload/header
               $pdfInPayload = !empty($payload['pdf_file_id'])
                    || (is_array($header) && (!empty($header['pdf_file_id']) || !empty($header['pdf_file_name'])));

               // If template expects a PDF but none is present, try a best-effort fallback to find a file
               // in the `files` table for this form/submission before deferring. This prevents missed
               // attachments when the file exists but header.pdf_file_id/name was not set by the client.
               if ($templateRefsPdf && !$forceSend && !$pdfInPayload) {
                    try {
                         // Try to locate a recent PDF in `files` linked to this form/submission
                         $cols = [];
                         try {
                              $cols = $db->getFieldNames('files');
                         } catch (\Throwable $__cols) {
                              $cols = [];
                         }

                         $qb = $db->table('files')->select('*');
                         $usedCond = false;
                         if (in_array('form_id', $cols, true) && $formId > 0) {
                              $qb->where('form_id', (int)$formId);
                              $usedCond = true;
                         }
                         if (in_array('submission_id', $cols, true) && ctype_digit((string)$submissionId)) {
                              $qb->where('submission_id', (int)$submissionId);
                              $usedCond = true;
                         }
                         if (in_array('file_name', $cols, true)) {
                              $qb->like('file_name', 'submission_'); // prefer client-generated submission PDFs
                         }

                         if ($usedCond) {
                              if (in_array('createdDTM', $cols, true)) $qb->orderBy('createdDTM', 'DESC');
                              elseif (in_array('created_at', $cols, true)) $qb->orderBy('created_at', 'DESC');
                              elseif (in_array('file_id', $cols, true)) $qb->orderBy('file_id', 'DESC');
                              $rowCandidate = $qb->limit(1)->get()->getRowArray();
                              if (!empty($rowCandidate)) {
                                   // Pretend this PDF was supplied in payload so the normal attach-path runs below
                                   $pdfInPayload = true;
                                   // set payload so later logic that checks $payload['pdf_file_id'] will pick it up
                                   if (isset($rowCandidate['file_id'])) {
                                        $payload['pdf_file_id'] = (int)$rowCandidate['file_id'];
                                   } elseif (isset($rowCandidate['f_id'])) {
                                        $payload['pdf_file_id'] = (int)$rowCandidate['f_id'];
                                   } elseif (isset($rowCandidate['id'])) {
                                        $payload['pdf_file_id'] = (int)$rowCandidate['id'];
                                   }
                                   try {
                                        file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] found_pdf_in_files_table: form_id={$formId} submission={$submissionId} candidate=" . json_encode([$rowCandidate['file_name'] ?? null]) . "\n", FILE_APPEND);
                                   } catch (\Throwable $__dbg) {
                                   }
                              }
                         }

                         // If still not found, defer as before
                         if (! $pdfInPayload) {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] deferred_for_missing_pdf: form_id={$formId} submission={$submissionId} template=" . ($tmpl['event_key'] ?? 'NULL') . " header_pdf_present=" . ($pdfInPayload ? 'Y' : 'N') . "\n", FILE_APPEND);
                              return;
                         }
                    } catch (\Throwable $__dbg) {
                         // If any fallback check fails, fall back to deferring
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] deferred_for_missing_pdf(fallback-exception): form_id={$formId} submission={$submissionId}\n", FILE_APPEND);
                         } catch (\Throwable $__e) {
                         }
                         return;
                    }
               }

               // Dedupe lock: check cache and file-lock fallback so duplicate sends are reliably suppressed
               try {
                    $cacheKey = 'email_send_lock_' . (int)$formId . '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', (string)$submissionId) . '_' . ($tmpl['event_key'] ?? 'generic');
                    $cache = \Config\Services::cache();

                    $locked = false;
                    try {
                         if ($cache->get($cacheKey)) $locked = true;
                    } catch (\Throwable $__c) {
                         $locked = false;
                    }

                    // file-lock fallback
                    $lockDir = WRITEPATH . 'email_locks';
                    $lockFile = $lockDir . '/' . $cacheKey . '.lock';
                    if (! $locked && is_dir($lockDir) && file_exists($lockFile) && (filemtime($lockFile) + 120) > time()) {
                         $locked = true;
                    }

                    if (! $forceSend && $locked) {
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] dedupe_skip: form_id={$formId} submission={$submissionId} template=" . ($tmpl['event_key'] ?? 'NULL') . " (locked)\n", FILE_APPEND);
                         return;
                    }

                    // persist cache lock + create file-lock
                    try {
                         $cache->save($cacheKey, 1, 120);
                    } catch (\Throwable $__ignore) {
                    }
                    try {
                         if (!is_dir($lockDir)) @mkdir($lockDir, 0755, true);
                         @file_put_contents($lockFile, (string)time());
                    } catch (\Throwable $__ignore) {
                    }
               } catch (\Throwable $__cacheErr) {
                    // ignore cache/storage failures — best-effort only
               }

               // Prepare variables for template replacement
               $vars = [];
               $vars['submission_id'] = (string)$submissionId;
               $vars['submission_uuid'] = is_string($submissionId) ? $submissionId : (string)$submissionId;
               $vars['form_id'] = (int)$formId;
               $vars['created_dtm'] = $createdDtm ?? date('Y-m-d H:i:s');

               $formName = '';
               try {
                    $formRow = $db->table('forms')->select('form_name')->where('id', (int)$formId)->get()->getRowArray();
                    $formName = trim((string)($formRow['form_name'] ?? ''));
               } catch (\Throwable $__formEx) {
                    $formName = '';
               }
               $eventKeyLc = strtolower(trim((string)($tmpl['event_key'] ?? '')));
               $isMaintenanceForm = (stripos($formName, 'maintenance') !== false);
               if (!$isMaintenanceForm) {
                    if (strpos($eventKeyLc, 'branding') !== false) {
                         $isMaintenanceForm = false;
                    } elseif (strpos($eventKeyLc, 'maintenance') !== false) {
                         $isMaintenanceForm = true;
                    }
               }
               $vars['form_name'] = $formName;
               $vars['pdf_type'] = $isMaintenanceForm ? 'maintenance' : 'checklist';
               $vars['is_maintenance_form'] = $isMaintenanceForm ? '1' : '0';

               $sidForRoute = rawurlencode((string)$submissionId);
               $vars['pdf_download_url'] = $isMaintenanceForm
                    ? base_url('pdf/maintenance_download/' . $sidForRoute)
                    : base_url('pdf/download/' . $sidForRoute);
               $vars['pdf_backend_download_url'] = $isMaintenanceForm
                    ? base_url('backend/pdf/maintenance_download/' . $sidForRoute)
                    : base_url('backend/pdf/download/' . $sidForRoute);

               if (is_array($header)) {
                    // common header fields we pass through
                    $vars['branch_id'] = $header['branch_id'] ?? null;
                    $vars['branch_manager'] = $header['branch_manager'] ?? null;
                    // expose emails from header (if client included them) so templates/EmailController can use them
                    $vars['branch_manager_email'] = $header['branch_manager_email'] ?? null;
                    $vars['branch_email'] = $header['branch_email'] ?? null;
                    $vars['contact'] = $header['contact'] ?? null;
                    $vars['centre_name'] = $header['centre_name'] ?? $header['branch_name'] ?? null;

                    // provide template-friendly names / aliases
                    $bmRaw = $header['branch_manager'] ?? null;
                    $vars['branch_manager_name'] = $bmRaw ? preg_replace('/\s*\([^)]*\)$/', '', trim((string)$bmRaw)) : null;
                    $vars['branch_name'] = $vars['centre_name'] ?? ($header['branch_name'] ?? null);
                    // 'name' alias used in some templates
                    $vars['name'] = $vars['branch_manager_name'] ?? $vars['branch_manager'] ?? null;

                    // include auditor and pdf filename so templates can reference them
                    $vars['audited_by'] = isset($header['audited_by']) ? trim((string)$header['audited_by']) : null;
                    $vars['pdf_file_name'] = $header['pdf_file_name'] ?? null;
               }

               // Check for frontend-uploaded PDF file id (frontend should upload PDF via FileUpload::uploadFile)
               $pdfFileId = null;
               if (!empty($payload['pdf_file_id'])) {
                    $pdfFileId = (int)$payload['pdf_file_id'];
               } elseif (is_array($header) && !empty($header['pdf_file_id'])) {
                    $pdfFileId = (int)$header['pdf_file_id'];
               }

               $attachmentPath = null;
               if ($pdfFileId && $pdfFileId > 0) {
                    try {
                         // Robust lookup: check which identifier columns exist on `files` before querying
                         $db = \Config\Database::connect();

                         $hasFileId = $this->tableHasColumn('files', 'file_id');
                         $hasFid = $this->tableHasColumn('files', 'f_id');
                         $hasId = $this->tableHasColumn('files', 'id');

                         $f = null;
                         if ($hasFileId || $hasFid || $hasId) {
                              $qb = $db->table('files')->select('*');
                              $qb->groupStart();
                              if ($hasFileId) {
                                   $qb->where('file_id', $pdfFileId);
                              }
                              if ($hasFid) {
                                   // use OR only if a previous condition was added
                                   if ($hasFileId) $qb->orWhere('f_id', $pdfFileId);
                                   else $qb->where('f_id', $pdfFileId);
                              }
                              if ($hasId) {
                                   if ($hasFileId || $hasFid) $qb->orWhere('id', $pdfFileId);
                                   else $qb->where('id', $pdfFileId);
                              }
                              $qb->groupEnd()->limit(1);
                              $f = $qb->get()->getRowArray();
                         } else {
                              // defensive: files table doesn't have expected id columns
                              log_message('warning', 'triggerSubmissionEmailOnSave: files table missing file_id/f_id/id columns');
                         }

                         // Debug: record resolved file row (if any)
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] resolved_file_row_for_pdf_file_id={$pdfFileId} row=" . json_encode($f) . "\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }

                         if ($f) {
                              // If DB row exists but isn't linked to this form/submission, link it (best-effort)
                              try {
                                   $pkCol = null;
                                   if (isset($f['file_id'])) $pkCol = 'file_id';
                                   elseif (isset($f['f_id'])) $pkCol = 'f_id';
                                   elseif (isset($f['id'])) $pkCol = 'id';

                                   $toUpdate = [];
                                   if ($pkCol && empty($f['form_id']) && !empty($formId)) $toUpdate['form_id'] = (int)$formId;
                                   // submissionId may be string (uuid) or numeric; prefer numeric when possible
                                   if ($pkCol && empty($f['submission_id']) && ctype_digit((string)$submissionId)) $toUpdate['submission_id'] = (int)$submissionId;

                                   if (!empty($toUpdate) && $pkCol) {
                                        try {
                                             $db->table('files')->where($pkCol, $f[$pkCol])->update($toUpdate);
                                        } catch (\Throwable $__upd) {/* ignore */
                                        }
                                   }
                              } catch (\Throwable $__link) {
                                   // ignore linking failures — non-critical
                              }

                              $fileName = $f['file_name'] ?? $f['filename'] ?? $f['name'] ?? null;
                              $pathCol = $f['path'] ?? $f['full_path'] ?? null;

                              if ($fileName) {
                                   $candidate = WRITEPATH . 'uploads/secure_files/' . $fileName;
                                   if (is_file($candidate)) {
                                        $attachmentPath = $candidate;
                                   }
                              }

                              // Fallback: if file stored with absolute path in DB
                              if (!$attachmentPath && $pathCol && is_file($pathCol)) {
                                   $attachmentPath = $pathCol;
                              }
                         }

                         // Secondary fallback: frontend may include `pdf_file_name` in submission header — prefer that when DB row is missing
                         if (!$attachmentPath && is_array($header) && !empty($header['pdf_file_name'])) {
                              $candidateHeader = WRITEPATH . 'uploads/secure_files/' . basename($header['pdf_file_name']);
                              if (is_file($candidateHeader)) {
                                   $attachmentPath = $candidateHeader;
                                   try {
                                        file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] attached_via_header_pdf_file_name={$candidateHeader}\n", FILE_APPEND);
                                   } catch (\Throwable $__dbg) {
                                   }
                              }
                         }
                    } catch (\Throwable $e) {
                         log_message('error', 'triggerSubmissionEmailOnSave: failed to resolve pdf file: ' . $e->getMessage());
                    }
               }

               // If a pdf_file_id was provided but we couldn't find a file on disk, log diagnostic details to help debugging
               if (!empty($pdfFileId) && !$attachmentPath) {
                    try {
                         $diag = [
                              'pdf_file_id' => $pdfFileId,
                              'header_pdf_file_name' => $header['pdf_file_name'] ?? null,
                              'resolved_db_row' => isset($f) ? $f : null,
                              'candidate_header_path_exists' => (!empty($header['pdf_file_name']) && is_file(WRITEPATH . 'uploads/secure_files/' . basename($header['pdf_file_name']))) ? 'Y' : 'N',
                              'secure_dir' => WRITEPATH . 'uploads/secure_files/',
                         ];
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] pdf_attach_missing: " . json_encode($diag) . "\n", FILE_APPEND);
                    } catch (\Throwable $__dbg) {
                    }

                    // FALLBACK: try to find most-recent PDF for this form/submission (helps when header.pdf_file_name was null)
                    try {
                         $cols = [];
                         try {
                              $cols = $db->getFieldNames('files');
                         } catch (\Throwable $__c) {
                              $cols = [];
                         }
                         $qb = $db->table('files')->select('*');
                         $usedCond = false;
                         if (in_array('form_id', $cols, true) && $formId > 0) {
                              $qb->where('form_id', (int)$formId);
                              $usedCond = true;
                         }
                         if (in_array('submission_id', $cols, true) && ctype_digit((string)$submissionId)) {
                              $qb->where('submission_id', (int)$submissionId);
                              $usedCond = true;
                         }
                         // prefer filenames created by our frontend ('submission_*.pdf') when present
                         if (in_array('file_name', $cols, true)) {
                              $qb->like('file_name', 'submission_');
                         }
                         if ($usedCond) {
                              // order by createdDTM or primary key as available
                              if (in_array('createdDTM', $cols, true)) $qb->orderBy('createdDTM', 'DESC');
                              elseif (in_array('created_at', $cols, true)) $qb->orderBy('created_at', 'DESC');
                              elseif (in_array('file_id', $cols, true)) $qb->orderBy('file_id', 'DESC');
                              $rowCandidate = $qb->limit(1)->get()->getRowArray();
                              if ($rowCandidate) {
                                   $candidateName = $rowCandidate['file_name'] ?? $rowCandidate['filename'] ?? null;
                                   if ($candidateName) {
                                        $candidatePath = WRITEPATH . 'uploads/secure_files/' . basename($candidateName);
                                        if (is_file($candidatePath)) {
                                             $attachmentPath = $candidatePath;
                                             try {
                                                  file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] pdf_attach_fallback_used candidate=" . basename($candidatePath) . "\n", FILE_APPEND);
                                             } catch (\Throwable $__dbg) {
                                             }
                                        }
                                   }
                              }
                         }
                    } catch (\Throwable $__fb) {
                         log_message('error', 'triggerSubmissionEmailOnSave fallback file lookup failed: ' . $__fb->getMessage());
                    }
               }

               // Resolve branch emails (prefer branch lookup when branch_id present in header)
               $recipients = [];
               if (is_array($header) && !empty($header['branch_id'])) {
                    try {
                         $branchModel = model(\App\Models\BranchModel::class);
                         $b = $branchModel->getBranchDetails($header['branch_id']);

                         // Debug: record branch lookup result
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] branch_lookup: " . json_encode([$header['branch_id'], $b]) . "\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }

                         // expose branch emails into template variables (so sendTemplate can pick them up from $data)
                         if (empty($vars['branch_manager_email']) && !empty($b['branch_manager_email'])) {
                              $vars['branch_manager_email'] = $b['branch_manager_email'];
                         }
                         if (empty($vars['branch_email']) && !empty($b['branch_email'])) {
                              $vars['branch_email'] = $b['branch_email'];
                         }

                         if (!empty($b['branch_manager_email'])) $recipients[] = ['email' => $b['branch_manager_email'], 'name' => $b['branch_manager_name'] ?? ''];
                         if (!empty($b['branch_email'])) $recipients[] = ['email' => $b['branch_email'], 'name' => $b['branch_name'] ?? ''];
                    } catch (\Throwable $e) {
                         log_message('error', 'triggerSubmissionEmailOnSave: branch lookup failed: ' . $e->getMessage());
                    }
               }

               // If no branch recipients found, attempt to use header 'to' like fields if provided
               if (empty($recipients) && is_array($header)) {
                    if (!empty($header['branch_manager_email']) && filter_var($header['branch_manager_email'], FILTER_VALIDATE_EMAIL)) {
                         $recipients[] = ['email' => $header['branch_manager_email'], 'name' => $header['branch_manager'] ?? ''];
                    }
                    if (!empty($header['branch_email']) && filter_var($header['branch_email'], FILTER_VALIDATE_EMAIL)) {
                         $recipients[] = ['email' => $header['branch_email'], 'name' => $header['centre_name'] ?? ''];
                    }
               }

               // ALWAYS include explicit header-provided branch emails (frontend-supplied) in recipients so
               // the frontend can force delivery to branch manager even when branch lookup returns other addresses.
               if (is_array($header)) {
                    $explicit = [];
                    if (!empty($header['branch_manager_email']) && filter_var($header['branch_manager_email'], FILTER_VALIDATE_EMAIL)) {
                         $explicit[] = ['email' => $header['branch_manager_email'], 'name' => $header['branch_manager'] ?? ''];
                    }
                    if (!empty($header['branch_email']) && filter_var($header['branch_email'], FILTER_VALIDATE_EMAIL)) {
                         $explicit[] = ['email' => $header['branch_email'], 'name' => $header['centre_name'] ?? ''];
                    }
                    if (!empty($explicit)) {
                         // merge and dedupe by email
                         $all = array_merge($recipients, $explicit);
                         $seen = [];
                         $recipients = [];
                         foreach ($all as $row) {
                              $em = trim((string)($row['email'] ?? ''));
                              if (! $em || ! filter_var($em, FILTER_VALIDATE_EMAIL)) continue;
                              if (in_array($em, $seen, true)) continue;
                              $seen[] = $em;
                              $recipients[] = ['email' => $em, 'name' => $row['name'] ?? ''];
                         }
                    }
               }

               // Fallback: if still no recipients, try template's cc_emails (treat as primary recipients)
               if (empty($recipients) && !empty($tmpl['cc_emails'])) {
                    $parts = is_array($tmpl['cc_emails']) ? $tmpl['cc_emails'] : preg_split('/[,;\s]+/', (string) $tmpl['cc_emails']);
                    foreach ($parts as $p) {
                         $p = trim((string)$p);
                         if ($p && filter_var($p, FILTER_VALIDATE_EMAIL)) {
                              $recipients[] = ['email' => $p, 'name' => ''];
                         }
                    }
                    try {
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] fallback_to_template_ccs: " . json_encode($parts) . "\n", FILE_APPEND);
                    } catch (\Throwable $__dbg) {
                    }
               }

               // Nothing to send to
               if (empty($recipients)) {
                    log_message('debug', "triggerSubmissionEmailOnSave: no recipients for form_id={$formId}, submission={$submissionId}");
                    try {
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] no_recipients_found template_ccs=" . ($tmpl['cc_emails'] ?? 'NULL') . " header=" . json_encode($header) . "\n", FILE_APPEND);
                    } catch (\Throwable $__dbg) {
                    }
                    return;
               }

               // send a single message; first address becomes the primary To recipient, others are CC'd
               $emailController = new \App\Controllers\EmailController();
               $valid = [];
               foreach ($recipients as $r) {
                    $em = trim((string)($r['email'] ?? ''));
                    if ($em && filter_var($em, FILTER_VALIDATE_EMAIL)) {
                         $valid[] = $em;
                    }
               }
               if (!empty($valid)) {
                    $toEmail = array_shift($valid);
                    $toName = null; // names not tracked anymore for group sends
                    $ccList = !empty($valid) ? implode(',', $valid) : null;
                    try {
                         // ask the mailer to generate a form-appropriate PDF from submission data
                         $vars['checklist_id'] = $submissionId;
                         $res = $emailController->sendTemplate($tmpl['event_key'], $toEmail, $toName, $vars, null, $ccList, null);
                         $resText = strtolower(trim((string)$res));
                         $emailSentOk = ($resText === 'email sent' || strpos($resText, 'email sent') !== false);
                         log_message('info', "triggerSubmissionEmailOnSave: template={$tmpl['event_key']} to={$toEmail} cc={$ccList} result={$res}");
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] send_attempt: to={$toEmail} cc={$ccList} res=" . substr((string)$res, 0, 200) . "\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }
                    } catch (\Throwable $e) {
                         $emailSentOk = false;
                         log_message('error', 'triggerSubmissionEmailOnSave: sendTemplate failed: ' . $e->getMessage());
                         try {
                              file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] send_failed: to={$toEmail} cc={$ccList} ex=" . $e->getMessage() . "\n", FILE_APPEND);
                         } catch (\Throwable $__dbg) {
                         }
                    }
               }

               // If send failed (or no valid send happened), release short-term lock and stop.
               if (!$emailSentOk) {
                    try {
                         if ($cache && $cacheKey !== '') {
                              $cache->delete($cacheKey);
                         }
                    } catch (\Throwable $__unlockCache) {
                    }
                    try {
                         if ($lockFile !== '' && is_file($lockFile)) {
                              @unlink($lockFile);
                         }
                    } catch (\Throwable $__unlockFile) {
                    }
                    try {
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] persisted_skip_send_failed: form_id={$formId} submission={$submissionId} template=" . ($tmpl['event_key'] ?? 'NULL') . "\n", FILE_APPEND);
                    } catch (\Throwable $__dbg) {
                    }
                    return;
               }

               // Persist a sent-marker into `form_submissions.header.email_sent_events` as associative array with timestamp
               try {
                    if ($this->usesSubmissionTable() && ctype_digit((string)$submissionId)) {
                         $row = $db->table('form_submissions')->select('header')->where('id', (int)$submissionId)->get()->getRowArray();
                         $hdr = [];
                         if (!empty($row['header'])) {
                              $hdr = is_array($row['header']) ? $row['header'] : (json_decode($row['header'], true) ?: []);
                         }

                         $sent = isset($hdr['email_sent_events']) && is_array($hdr['email_sent_events']) ? $hdr['email_sent_events'] : [];
                         $evt = (string)($tmpl['event_key'] ?? '');
                         if ($evt !== '') {
                              // Store as associative array: event_key => sent_time
                              $sent[$evt] = date('Y-m-d H:i:s');
                              $hdr['email_sent_events'] = $sent;

                              // also remember which PDF id was sent so we can suppress
                              // repeat sends for the same attachment
                              if (is_array($header) && !empty($header['pdf_file_id'])) {
                                   $hdr['last_pdf_id'] = $header['pdf_file_id'];
                              }

                              $upd = ['header' => json_encode($hdr)];
                              if ($this->tableHasColumn('form_submissions', 'updated_dtm')) $upd['updated_dtm'] = date('Y-m-d H:i:s');
                              $db->table('form_submissions')->where('id', (int)$submissionId)->update($upd);
                              try {
                                   file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] persisted_mark: form_id={$formId} submission={$submissionId} template={$evt}\n", FILE_APPEND);
                              } catch (\Throwable $__dbg) {
                              }
                         }
                    }
               } catch (\Throwable $__persistEx) {
                    log_message('error', 'triggerSubmissionEmailOnSave: failed to persist sent-marker: ' . $__persistEx->getMessage());
               }
          } catch (\Throwable $e) {
               log_message('error', 'triggerSubmissionEmailOnSave: unexpected error: ' . $e->getMessage());
          }
     }

     // Quick admin API: re-run the email trigger for an existing submission (best-effort)
     // POST /api/dynamic-form/{id}/resend-email
     public function resendEmail($submissionId)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) return $user;

          $sid = is_string($submissionId) ? trim($submissionId) : (string)$submissionId;
          if ($sid === '') return $this->respond(['message' => 'submission_id required'], 400);

          $db = \Config\Database::connect();
          $useSubmissionTable = $this->usesSubmissionTable();

          try {
               $submission = null;
               if ($useSubmissionTable && ctype_digit($sid)) {
                    $submission = $db->table('form_submissions')->where('id', (int)$sid)->get()->getRowArray();
               }
               if (!$submission) {
                    return $this->respond(['message' => 'Submission not found'], 404);
               }

               $formId = isset($submission['form_id']) ? (int)$submission['form_id'] : 0;
               $header = null;
               if (!empty($submission['header'])) {
                    $h = $submission['header'];
                    if (is_string($h)) $header = json_decode($h, true) ?: null;
                    elseif (is_array($h)) $header = $h;
               }

               // Trigger (best-effort, non-blocking)
               try {
                    try {
                         file_put_contents(APPPATH . '../writable/logs/email_trigger_debug.log', "[" . date('Y-m-d H:i:s') . "] resendEmail_invoking_trigger: form_id={$formId} submission={$sid} header=" . json_encode($header) . "\n", FILE_APPEND);
                    } catch (\Throwable $__dbg) {
                         // ignore logging failures
                    }
                    // Admin-initiated resend should bypass short TTL dedupe
                    $this->triggerSubmissionEmailOnSave((int)$formId, $sid, $header, $submission['created_dtm'] ?? null, ['force' => true]);
               } catch (\Throwable $e) {
                    log_message('error', 'resendEmail trigger failed: ' . $e->getMessage());
                    return $this->respond(['message' => 'Trigger failed', 'error' => $e->getMessage()], 500);
               }

               return $this->respond(['message' => 'Trigger scheduled'], 200);
          } catch (\Throwable $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // GET /api/dynamic-form/{id}/debug - admin helper to inspect a submission's header, attached pdf and branch lookup
     public function debugSubmission($submissionId)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) return $user;

          $sid = is_string($submissionId) ? trim($submissionId) : (string)$submissionId;
          if ($sid === '') return $this->respond(['message' => 'submission_id required'], 400);

          $db = \Config\Database::connect();
          $useSubmissionTable = $this->usesSubmissionTable();

          try {
               $submission = null;
               if ($useSubmissionTable && ctype_digit($sid)) {
                    $submission = $db->table('form_submissions')->where('id', (int)$sid)->get()->getRowArray();
               }
               if (!$submission) return $this->respond(['message' => 'Submission not found'], 404);

               $header = null;
               if (!empty($submission['header'])) {
                    $h = $submission['header'];
                    if (is_string($h)) $header = json_decode($h, true) ?: null;
                    elseif (is_array($h)) $header = $h;
               }

               $pdfFileId = $header['pdf_file_id'] ?? null;
               $pdfFileName = $header['pdf_file_name'] ?? null;

               $fileRow = null;
               $fileExistsOnDisk = false;
               if ($pdfFileId) {
                    try {
                         $cols = [];
                         try {
                              $cols = $db->getFieldNames('files');
                         } catch (\Throwable $__cols) {
                              $cols = [];
                         }
                         $qb = $db->table('files')->select('*');
                         $added = false;
                         $qb->groupStart();
                         if (in_array('file_id', $cols, true)) {
                              $qb->where('file_id', (int)$pdfFileId);
                              $added = true;
                         }
                         if (in_array('f_id', $cols, true)) {
                              $added ? $qb->orWhere('f_id', (int)$pdfFileId) : ($qb->where('f_id', (int)$pdfFileId) && $added = true);
                         }
                         if (in_array('id', $cols, true)) {
                              $added ? $qb->orWhere('id', (int)$pdfFileId) : ($qb->where('id', (int)$pdfFileId) && $added = true);
                         }
                         $qb->groupEnd();
                         if ($added) $fileRow = $qb->limit(1)->get()->getRowArray();
                    } catch (\Throwable $e) {
                         // ignore
                    }
               }

               // check disk for filename from DB-row or header
               $candidateName = $fileRow['file_name'] ?? $fileRow['filename'] ?? $pdfFileName ?? null;
               if ($candidateName) {
                    $candidatePath = WRITEPATH . 'uploads/secure_files/' . basename($candidateName);
                    if (is_file($candidatePath)) $fileExistsOnDisk = true;
               }

               $branchInfo = null;
               if (is_array($header) && !empty($header['branch_id'])) {
                    try {
                         $branchModel = model(\App\Models\BranchModel::class);
                         $branchInfo = $branchModel->getBranchDetails($header['branch_id']);
                    } catch (\Throwable $e) {
                         // ignore
                    }
               }

               return $this->respond([
                    'submission' => $submission,
                    'header' => $header,
                    'pdf_file_id' => $pdfFileId,
                    'pdf_file_name' => $pdfFileName,
                    'file_row' => $fileRow,
                    'file_exists_on_disk' => $fileExistsOnDisk,
                    'branch_lookup' => $branchInfo,
               ], 200);
          } catch (\Throwable $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // Upload photo(s) for a given dynamic-form submission id (multipart/form-data)
     public function uploadPhoto($submissionId)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) return $user;

          $sid = is_string($submissionId) ? trim($submissionId) : (string)$submissionId;
          if ($sid === '') return $this->respond(['message' => 'submission_id required'], 400);

          $useSubmissionTable = $this->usesSubmissionTable();
          $db = \Config\Database::connect();
          $uploadStart = microtime(true); // track upload start time for diagnostics
          if ($useSubmissionTable && ctype_digit($sid)) {
               $sub = $db->table('form_submissions')->where('id', (int)$sid)->get()->getRowArray();
               if (!$sub) return $this->respond(['message' => 'Not found'], 404);
          }

          // Ensure the target exists in branding_checklists otherwise FK will fail when inserting photos
          $chkExists = $db->table('branding_checklists')->where('id', (int)$sid)->get()->getRowArray();
          if (!$chkExists) {
               // Try to see if this id refers to a form_submissions row (dynamic-form flow).
               $sub = $db->table('form_submissions')->where('id', (int)$sid)->get()->getRowArray();
               if ($sub) {
                    // Create a minimal branding_checklists row so photos can be associated with a checklist.
                    try {
                         // Prefer to create the checklist with the same numeric id as the submission when possible
                         $targetId = (int)$sid;
                         $insertData = [
                              'centre_name' => 'Imported from dynamic-form ' . $sid,
                              'date_of_visit' => date('Y-m-d'),
                              'visit_time' => date('H:i:s'),
                              'created_by' => $sub['created_by'] ?? null,
                              'status' => 'draft',
                              'created_at' => date('Y-m-d H:i:s'),
                         ];

                         // Try to reserve the exact id if it's not already taken
                         $newChkId = 0;
                         try {
                              $tryData = $insertData;
                              if ($targetId > 0) $tryData['id'] = $targetId;
                              $db->table('branding_checklists')->insert($tryData);
                              // If we explicitly set id, insertID() may be 0; fall back to targetId
                              $newChkId = (int)$db->insertID();
                              if ($newChkId <= 0 && $targetId > 0) $newChkId = $targetId;
                              log_message('debug', 'DynamicForm::uploadPhoto attempted create branding_checklist ' . $newChkId . ' (preferred id ' . $targetId . ') for submission ' . $sid);
                         } catch (\Throwable $e) {
                              // If inserting with explicit id failed (duplicate or otherwise), fall back to normal insert
                              try {
                                   $db->table('branding_checklists')->insert($insertData);
                                   $newChkId = (int)$db->insertID();
                                   log_message('debug', 'DynamicForm::uploadPhoto fallback created branding_checklist ' . $newChkId . ' for submission ' . $sid);
                              } catch (\Throwable $e2) {
                                   log_message('error', 'DynamicForm::uploadPhoto failed to create branding_checklist for submission ' . $sid . ': ' . $e2->getMessage());
                                   return $this->respond(['message' => 'Failed to create checklist for submission: ' . $sid], 500);
                              }
                         }

                         if ($newChkId > 0) {
                              // Use the newly created checklist id
                              $sid = (string)$newChkId;
                         } else {
                              log_message('error', 'DynamicForm::uploadPhoto failed to determine new checklist id for submission ' . $sid);
                              return $this->respond(['message' => 'Failed to create checklist for submission: ' . $sid], 500);
                         }
                    } catch (\Throwable $e) {
                         log_message('error', 'DynamicForm::uploadPhoto exception creating branding_checklist for submission ' . $sid . ': ' . $e->getMessage());
                         return $this->respond(['message' => 'Failed to create checklist for submission: ' . $sid], 500);
                    }
               } else {
                    log_message('error', 'DynamicForm::uploadPhoto attempted for nonexistent checklist_id: ' . $sid);
                    return $this->respond(['message' => 'Checklist not found for id: ' . $sid], 404);
               }
          }

          $files = array_values($this->request->getFileMultiple('photos') ?? []);
          $metas = $this->request->getPost('photos_meta') ?? [];
          if (empty($files)) return $this->respond(['message' => 'No files uploaded'], 400);

          $uploadPath = WRITEPATH . 'uploads/branding_photos';
          if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

          $saved = [];
          $errors = [];
          $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
          $maxBytes = 2 * 1024 * 1024; // 2 MB

          $photoModel = model(BrandingChecklistModel::class);

          foreach ($files as $i => $file) {
               if (!$file || !$file->isValid() || $file->hasMoved()) {
                    $errors[] = "File #{$i} invalid or already moved";
                    continue;
               }

               // basic server-side validation
               if (!in_array($file->getClientMimeType(), $allowedTypes)) {
                    $errors[] = "File #{$i} type not allowed";
                    continue;
               }
               if ($file->getSize() > $maxBytes) {
                    $errors[] = "File #{$i} exceeds size limit";
                    continue;
               }

               $newName = $file->getRandomName();
               try {
                    $file->move($uploadPath, $newName);
               } catch (\Throwable $e) {
                    $errors[] = "Failed to move file #{$i}";
                    continue;
               }

               // parse metadata safely
               $meta = [];
               $metaJson = $metas[$i] ?? '{}';
               $m = json_decode($metaJson, true);
               if (json_last_error() === JSON_ERROR_NONE && is_array($m)) $meta = $m;

               // normalize/validate geo & timestamp
               $lat = isset($meta['latitude']) ? (float)$meta['latitude'] : null;
               $lon = isset($meta['longitude']) ? (float)$meta['longitude'] : null;
               $accuracy = isset($meta['accuracy']) ? (int)$meta['accuracy'] : null;
               $geo_dtm = null;
               if (isset($meta['timestamp'])) {
                    $ts = strtotime($meta['timestamp']);
                    if ($ts !== false) $geo_dtm = date('Y-m-d H:i:s', $ts);
               }
               if (!is_null($lat) && ($lat < -90 || $lat > 90)) $lat = null;
               if (!is_null($lon) && ($lon < -180 || $lon > 180)) $lon = null;
               if (!is_null($accuracy) && ($accuracy < 0 || $accuracy > 100000)) $accuracy = null;

               $caption = $meta['caption'] ?? $this->request->getPost('caption') ?? null;
               $caption = $caption ? trim(strip_tags((string)$caption)) : null;

               try {
                    // Reuse BrandingChecklistModel so existing listing and delete logic stays consistent
                    $inserted = $photoModel->addPhoto((int)$sid, $newName, $caption, $lat, $lon, $accuracy, $geo_dtm);
                    if ($inserted) {
                         $saved[] = $newName;
                    } else {
                         $errors[] = "DB save failed for file #{$i}";
                         log_message('error', 'DynamicForm::uploadPhoto addPhoto returned false for checklist ' . $sid . ' file ' . $newName);
                         // cleanup moved file to avoid leaving orphan files on disk
                         try {
                              if (is_file($uploadPath . DIRECTORY_SEPARATOR . $newName)) {
                                   @unlink($uploadPath . DIRECTORY_SEPARATOR . $newName);
                              }
                         } catch (\Throwable $e2) {
                         }
                    }
               } catch (\Throwable $e) {
                    $errors[] = "DB save exception for file #{$i}";
                    log_message('error', 'DynamicForm::uploadPhoto addPhoto exception for checklist ' . $sid . ' file ' . $newName . ': ' . $e->getMessage());
                    // cleanup moved file to avoid leaving orphan files on disk
                    try {
                         if (is_file($uploadPath . DIRECTORY_SEPARATOR . $newName)) {
                              @unlink($uploadPath . DIRECTORY_SEPARATOR . $newName);
                         }
                    } catch (\Throwable $e2) {
                    }
               }
          }

          $status = empty($errors) ? 201 : 207; // partial success

          // Debug: fetch and return saved DB rows so client can observe what is stored (helps find mismatches)
          try {
               $rows = $photoModel->getPhotos((int)$sid);
               log_message('debug', 'DynamicForm::uploadPhoto saved ' . count($saved) . ' files, DB rows for ' . $sid . ': ' . json_encode(array_column($rows, 'filename')));
          } catch (\Throwable $e) {
               log_message('error', 'DynamicForm::uploadPhoto failed to fetch photos after save: ' . $e->getMessage());
               $rows = [];
          }

          // Log duration and summary for diagnostics
          try {
               $duration = round(microtime(true) - $uploadStart, 3);
               log_message('debug', 'DynamicForm::uploadPhoto summary for ' . $sid . ': files=' . count($files) . ', saved=' . count($saved) . ', errors=' . count($errors) . ', duration_s=' . $duration);
          } catch (\Throwable $e) {
          }

          // Log photo upload activity
          try {
               $lm = new LogsModel();
               $lm->insertLog([
                    'uri' => $this->request->getURI()->getPath(),
                    'method' => $this->request->getMethod(),
                    'params' => ['saved' => $saved, 'errors' => $errors, 'user_role' => $user->role ?? null],
                    'entity_id' => (string)$sid,
                    'user_id' => $user->emp_code ?? $user->username ?? null,
               ]);
          } catch (\Throwable $e) {
               log_message('error', 'DynamicForm::uploadPhoto log failed: ' . $e->getMessage());
          }

          return $this->respondCreated(['saved' => $saved, 'errors' => $errors, 'rows' => $rows], $status);
     }

     // List photos for a dynamic-form submission
     public function photos($id)
     {
          $this->validateAuthorizationNew();
          $photoModel = model(BrandingChecklistModel::class);
          $photos = $photoModel->getPhotos((int)$id);
          log_message('debug', 'DynamicForm::photos called for ' . $id . ', found ' . count($photos) . ' rows');

          foreach ($photos as &$p) {
               $file = ($p['filename'] ?? '');
               $p['url'] = base_url('viewAttachmentNew/' . $file . '?size=thumb');
               $p['full_url'] = base_url('viewAttachmentNew/' . $file);
          }

          // Optional debug output when ?debug=1 is provided (helps diagnose empty-list issues without adding noise to normal responses)
          if ($this->request->getGet('debug')) {
               return $this->respond(['data' => $photos, 'debug' => ['count' => count($photos), 'filenames' => array_column($photos, 'filename')]], 200);
          }

          return $this->respond(['data' => $photos], 200);
     }
}

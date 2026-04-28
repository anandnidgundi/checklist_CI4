<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\FormRecordsModel;
use App\Models\FormSubmissionsModel;
use App\Models\BrandingChecklistModel;
use App\Models\LogsModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class NewDFormController extends BaseController
{
     use ResponseTrait;

     protected $formRecordsModel;
     protected $formSubmissionsModel;

     public function __construct()
     {
          $this->formRecordsModel = model(FormRecordsModel::class);
          $this->formSubmissionsModel = model(FormSubmissionsModel::class);
     }

     public function createNewSubmission()
     {
          $auth = $this->validateAuthorizationNew();

          // Short, maintainable create: return any auth failure response early
          if (($auth instanceof ResponseInterface)) return $auth;

          $payload = $this->readPayload();
          $formId = (int) ($payload['form_id'] ?? 0);
          $deptId = (int) ($payload['dept_id'] ?? 0);
          $records = $payload['records'] ?? ($payload['inputs'] ?? null);

          if ($formId <= 0 || $deptId <= 0 || !is_array($records) || empty($records)) {
               return $this->respond(['message' => 'form_id, dept_id, and records array are required'], 400);
          }

          $createdDtm = trim((string) ($payload['created_dtm'] ?? '')) ?: date('Y-m-d H:i:s');
          $empCode = $this->resolveAuthEmpCode($auth);
          if ($empCode === '') {
               return $this->respond(['message' => 'Invalid token payload (emp_code missing)'], 401);
          }

          $header = $payload['header'] ?? null;
          $status = trim((string) ($payload['status'] ?? 'draft')) ?: 'draft';

          $db = \Config\Database::connect();
          $db->transBegin();

          try {
               $useSubmissionTable = $this->usesSubmissionTable();
               $numericSubmissionId = null;
               $submissionUuid = null;

               if ($useSubmissionTable) {
                    $submissionUuid = $this->uuidV4();
                    $row = [
                         'submission_uuid' => $submissionUuid,
                         'form_id' => $formId,
                         'dept_id' => $deptId,
                         'header' => $header === null ? null : $this->toStringValue($header),
                         'status' => $status,
                         'created_by' => $empCode,
                         'created_dtm' => $createdDtm,
                    ];
                    if (! $this->tableHasColumn('form_submissions', 'submission_uuid')) unset($row['submission_uuid']);

                    $this->formSubmissionsModel->insert($row);
                    $numericSubmissionId = (int) $this->formSubmissionsModel->getInsertID();
                    if ($numericSubmissionId <= 0) throw new \RuntimeException('Failed to create submission');
               } else {
                    $submissionUuid = isset($payload['submission_id']) ? trim((string) $payload['submission_id']) : $this->uuidV4();
               }

               $rows = [];
               foreach ($records as $i => $r) {
                    if (!is_array($r)) {
                         $db->transRollback();
                         return $this->respond(['message' => "records[$i] must be an object"], 400);
                    }
                    $sectionId = (int) ($r['section_id'] ?? 0);
                    $subSectionIdRaw = $r['sub_section_id'] ?? null;
                    $subSectionId = ($subSectionIdRaw === null || $subSectionIdRaw === '' || (int) $subSectionIdRaw <= 0) ? null : (int) $subSectionIdRaw;
                    $inputId = (int) ($r['input_id'] ?? 0);
                    if ($sectionId <= 0 || $inputId <= 0) {
                         $db->transRollback();
                         return $this->respond(['message' => 'Each record requires section_id and input_id'], 400);
                    }

                    $rec = [
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
                         $rec['submission_id'] = $numericSubmissionId;
                         $rec['submission_uuid'] = $submissionUuid;
                    } else {
                         if ($this->tableHasColumn('form_records', 'submission_uuid')) $rec['submission_uuid'] = $submissionUuid;
                         else $rec['submission_id'] = $submissionUuid;
                    }

                    $rows[] = $rec;
               }

               if (empty($rows)) {
                    $db->transRollback();
                    return $this->respond(['message' => 'No valid records to insert'], 400);
               }

               $ok = $this->formRecordsModel->insertBatch($rows);
               if ($ok === false) {
                    $db->transRollback();
                    return $this->respond(['message' => 'Failed to save records', 'errors' => $this->formRecordsModel->errors()], 500);
               }

               $db->transCommit();

               // Log
               try {
                    $lm = new LogsModel();
                    $lm->insertLog([
                         'uri' => $this->request->getURI()->getPath(),
                         'method' => $this->request->getMethod(),
                         'params' => ['form_id' => $formId, 'dept_id' => $deptId, 'rows_inserted' => count($rows)],
                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 201,
                         'action' => 'createSubmission',
                         'entity_type' => 'form_submission',
                         'entity_id' => $useSubmissionTable ? (string)$numericSubmissionId : (string)$submissionUuid,
                         'user_id' => $empCode,
                    ]);
               } catch (\Throwable $__e) {
                    // ignore logging errors
               }

               $responsePayload = [
                    'status' => true,
                    'message' => 'Saved',
                    'inserted' => count($rows),
                    'created_dtm' => $createdDtm,
                    'submission_id' => $useSubmissionTable ? $numericSubmissionId : $submissionUuid,
                    'submission_uuid' => $submissionUuid,
               ];

               // Generate PDF (if using submission table), save reference in header, then trigger email once
               try {
                    if ($useSubmissionTable && ctype_digit((string)$numericSubmissionId)) {
                         try {
                              $pdfFileId = $this->generatePdfWithImages($numericSubmissionId);
                              if ($pdfFileId) {
                                   try {
                                        $subRow = $db->table('form_submissions')->where('id', (int)$numericSubmissionId)->get()->getRowArray();
                                        $existingHeader = is_array($subRow) ? (json_decode($subRow['header'] ?? '{}', true) ?: []) : [];
                                        $incomingHdr = is_array($header) ? $header : (json_decode((string)$header, true) ?: []);
                                        $merged = array_merge($existingHeader, $incomingHdr);
                                        $merged['pdf_file_id'] = $pdfFileId;
                                        $db->table('form_submissions')
                                             ->where('id', (int)$numericSubmissionId)
                                             ->update([
                                                  'header' => json_encode($merged),
                                                  'updated_dtm' => date('Y-m-d H:i:s')
                                             ]);
                                   } catch (\Throwable $__up) {
                                        // ignore header update errors
                                   }

                                   // Now invoke email trigger once
                                   try {
                                        $this->triggerEmailOnSave($formId, $numericSubmissionId);
                                   } catch (\Throwable $__tr) {
                                        // ignore
                                   }
                              }
                         } catch (\Throwable $__pdf) {
                              // ignore PDF generation errors
                         }
                    }
               } catch (\Throwable $__t) {
                    // ignore
               }

               return $this->respondCreated($responsePayload);
          } catch (\Throwable $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function showSubmission($submissionId)
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
                    if (isset($records[0]['submission_id']) && ctype_digit((string)$records[0]['submission_id'])) {
                         $submissionIdForPhotos = (int) $records[0]['submission_id'];
                    } elseif (!empty($records[0]['submission_uuid'])) {
                         $row = $db->table('form_submissions')->select('id')->where('submission_uuid', $records[0]['submission_uuid'])->get()->getRowArray();
                         $submissionIdForPhotos = isset($row['id']) ? (int) $row['id'] : null;
                    }
               }
               if ($submissionIdForPhotos) {
                    $photoRows = $photoModel->getPhotos((int)$submissionIdForPhotos);
                    foreach ($photoRows as $pr) {
                         $caption = trim((string)($pr['caption'] ?? ''));
                         if ($caption === '') continue;
                         $photosByCaption[strtolower($caption)][] = $pr;
                    }
               }
          } catch (\Throwable $e) {
               log_message('error', 'NewDFormController::showSubmission failed to fetch photos: ' . $e->getMessage());
          }

          // Fetch form inputs metadata to build field keys
          $inputMetaMap = []; // input_id => field_key_slug
          if (!empty($records)) {
               try {
                    $fim = model(\App\Models\FormInputsModel::class);
                    $inputIds = array_values(array_unique(array_map(function ($r) {
                         return (int)($r['input_id'] ?? 0);
                    }, $records)));
                    if (!empty($inputIds)) {
                         $metas = $fim->whereIn('id', $inputIds)->findAll();
                         foreach ($metas as $m) {
                              $k = '';
                              if (!empty($m['input_name'])) $k = trim((string)$m['input_name']);
                              if ($k === '' && !empty($m['input_label'])) $k = trim((string)$m['input_label']);
                              $slug = strtolower(preg_replace('/[^a-z0-9]+/', '_', trim($k)));
                              $inputMetaMap[(int)$m['id']] = $slug;
                         }
                    }
               } catch (\Throwable $e) {
                    // best-effort
               }
          }

          // Attach photos to records by matching field key patterns
          foreach ($records as &$record) {
               $record['attachments'] = '';

               $inputId = isset($record['input_id']) ? (int) $record['input_id'] : 0;
               $sectionId = isset($record['section_id']) ? (int) $record['section_id'] : 0;
               $subSectionId = isset($record['sub_section_id']) ? (int) $record['sub_section_id'] : 0;

               $possibleKeys = [];

               if ($sectionId && $subSectionId && isset($inputMetaMap[$inputId])) {
                    $possibleKeys[] = 's' . $sectionId . '__ss' . $subSectionId . '__' . $inputMetaMap[$inputId];
               }

               if (isset($inputMetaMap[$inputId])) {
                    $possibleKeys[] = $inputMetaMap[$inputId];
               }

               $inputValue = trim((string) ($record['input_value'] ?? ''));
               if ($inputValue !== '') {
                    if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $inputValue) || preg_match('/\//', $inputValue)) {
                         $record['attachments'] = $inputValue;
                    }
               }

               foreach ($possibleKeys as $key) {
                    $lk = strtolower($key);
                    if (isset($photosByCaption[$lk])) {
                         $arr = array_map(function ($p) {
                              return $p['filename'] ?? $p['file_name'] ?? '';
                         }, $photosByCaption[$lk]);
                         $arr = array_values(array_unique(array_filter($arr)));
                         if (!empty($arr)) {
                              $record['attachments'] = implode(',', $arr);
                              break;
                         }
                    }
               }

               if ($record['attachments']) {
                    $record['attachments'] = trim($record['attachments'], ', ');
               }
          }
          unset($record);

          return $this->respond([
               'data' => [
                    'submission' => $submission,
                    'records' => $records,
                    'photos_by_caption' => $photosByCaption,
               ],
          ], 200);
     }

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

          try {
               if ($this->usesSubmissionTable()) {
                    $result = $this->formSubmissionsModel->datatableListFiltered($start, $length, (string) $search, $orderCol, (string) $orderDir, (array) $columns, $formId > 0 ? $formId : null);
                    return $this->respond([
                         'draw' => (int) $request->getGet('draw'),
                         'recordsTotal' => $result['total'] ?? 0,
                         'recordsFiltered' => $result['filtered'] ?? 0,
                         'data' => $result['rows'] ?? [],
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
                         ->like('r.input_value', $s)
                         ->orLike('r.created_by', $s)
                         ->groupEnd();
               }

               // total submissions count (approx)
               $totalQb = $db->table('form_records as rr')->select($groupKey)->groupBy($groupKey);
               if ($formId > 0) {
                    $totalQb->where('rr.form_id', $formId);
               }
               $total = (int) $totalQb->countAllResults();

               $qb->select($groupKey . ' as submission_key, MAX(r.created_dtm) as created_dtm, MAX(r.created_by) as created_by, MAX(r.form_id) as form_id, MAX(r.dept_id) as dept_id')
                    ->groupBy($groupKey);
               $filtered = (int) $qb->countAllResults(false);

               $qb->orderBy('created_dtm', 'desc');
               if ($length > 0) {
                    $qb->limit($length, max(0, $start));
               }
               $rows = $qb->get()->getResultArray();

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

     public function update($submissionId)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof ResponseInterface) return $user;

          $sid = is_string($submissionId) ? trim($submissionId) : (string) $submissionId;
          if ($sid === '') return $this->respond(['message' => 'submission_id required'], 400);

          $payload = $this->readPayload();
          $records = $payload['records'] ?? null;
          $header = $payload['header'] ?? null;
          $status = isset($payload['status']) ? trim((string) $payload['status']) : null;

          $empCode = $this->resolveAuthEmpCode($user);
          if ($empCode === '') {
               return $this->respond(['message' => 'Invalid token payload (emp_code missing)'], 401);
          }

          $useSubmissionTable = $this->usesSubmissionTable();
          $db = \Config\Database::connect();
          $db->transBegin();

          try {
               $submission = null;
               if ($useSubmissionTable && ctype_digit($sid)) {
                    $submission = $db->table('form_submissions')->where('id', (int) $sid)->get()->getRowArray();
                    if (!$submission) {
                         $db->transRollback();
                         return $this->respond(['message' => 'Not found'], 404);
                    }

                    if (!$this->canEditSubmission($submission, $user)) {
                         $db->transRollback();
                         return $this->respond(['message' => 'Forbidden'], 403);
                    }
               }

               // Update header/status on submission row when using new table
               if ($useSubmissionTable && ctype_digit($sid)) {
                    $update = [];
                    if ($header !== null) {
                         $existing = is_array($submission['header'] ?? null) ? $submission['header'] : (json_decode((string) ($submission['header'] ?? '{}'), true) ?: []);
                         $incoming = is_array($header) ? $header : (json_decode((string) $header, true) ?: []);
                         $merged = array_merge($existing, $incoming);
                         $update['header'] = json_encode($merged);
                    }
                    if ($status !== null) $update['status'] = $status;
                    if (!empty($update)) {
                         $update['updated_dtm'] = date('Y-m-d H:i:s');
                         $db->table('form_submissions')->where('id', (int) $sid)->update($update);
                    }
               }

               // Replace records if provided
               if ($records !== null) {
                    if (!is_array($records)) {
                         $db->transRollback();
                         return $this->respond(['message' => 'records must be an array'], 400);
                    }

                    // Remove existing records for this submission
                    if ($useSubmissionTable && ctype_digit($sid)) {
                         $db->table('form_records')->where('submission_id', (int) $sid)->delete();
                         $submissionIdForInsert = (int) $sid;
                         $submissionUuid = $submission['submission_uuid'] ?? null;
                         $formId = (int) ($submission['form_id'] ?? ($payload['form_id'] ?? 0));
                         $deptId = (int) ($submission['dept_id'] ?? ($payload['dept_id'] ?? 0));
                    } else {
                         // legacy: submission UUID or id stored in submission_id column
                         $submissionUuid = $sid;
                         $submissionIdForInsert = null;
                         $formId = (int) ($payload['form_id'] ?? 0);
                         $deptId = (int) ($payload['dept_id'] ?? 0);
                         if ($this->tableHasColumn('form_records', 'submission_uuid')) {
                              $db->table('form_records')->where('submission_uuid', $submissionUuid)->delete();
                         } else {
                              $db->table('form_records')->where('submission_id', $submissionUuid)->delete();
                         }
                    }

                    $rows = [];
                    $createdDtm = date('Y-m-d H:i:s');
                    foreach ($records as $i => $r) {
                         if (!is_array($r)) {
                              $db->transRollback();
                              return $this->respond(['message' => "records[$i] must be an object"], 400);
                         }
                         $sectionId = (int) ($r['section_id'] ?? 0);
                         $subSectionIdRaw = $r['sub_section_id'] ?? null;
                         $subSectionId = ($subSectionIdRaw === null || $subSectionIdRaw === '' || (int) $subSectionIdRaw <= 0) ? null : (int) $subSectionIdRaw;
                         $inputId = (int) ($r['input_id'] ?? 0);
                         if ($sectionId <= 0 || $inputId <= 0) {
                              $db->transRollback();
                              return $this->respond(['message' => 'Each record requires section_id and input_id'], 400);
                         }

                         $rec = [
                              'section_id' => $sectionId,
                              'sub_section_id' => $subSectionId,
                              'input_id' => $inputId,
                              'input_value' => $this->toStringValue($r['input_value'] ?? ''),
                              'dept_id' => $deptId,
                              'created_dtm' => $createdDtm,
                              'created_by' => $empCode,
                              'form_id' => $formId,
                         ];

                         if ($useSubmissionTable && $submissionIdForInsert) {
                              $rec['submission_id'] = $submissionIdForInsert;
                              if ($submissionUuid) $rec['submission_uuid'] = $submissionUuid;
                         } else {
                              if ($this->tableHasColumn('form_records', 'submission_uuid')) $rec['submission_uuid'] = $submissionUuid;
                              else $rec['submission_id'] = $submissionUuid;
                         }

                         $rows[] = $rec;
                    }

                    if (!empty($rows)) {
                         $ok = $this->formRecordsModel->insertBatch($rows);
                         if ($ok === false) {
                              $db->transRollback();
                              return $this->respond(['message' => 'Failed to save records', 'errors' => $this->formRecordsModel->errors()], 500);
                         }
                    }
               }

               $db->transCommit();

               // Log update
               try {
                    $lm = new LogsModel();
                    $lm->insertLog([
                         'uri' => $this->request->getURI()->getPath(),
                         'method' => $this->request->getMethod(),
                         'params' => ['submission_id' => $sid, 'updated_records' => is_array($records) ? count($records) : 0],
                         'ip_address' => $this->request->getIPAddress(),
                         'time' => time(),
                         'authorized' => 'Y',
                         'response_code' => 200,
                         'action' => 'updateSubmission',
                         'entity_type' => 'form_submission',
                         'entity_id' => $sid,
                         'user_id' => $empCode,
                    ]);
               } catch (\Throwable $__e) {
               }

               return $this->respond(['message' => 'Updated', 'submission_id' => $sid], 200);
          } catch (\Throwable $e) {
               $db->transRollback();
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     private function triggerEmailOnSave($formId, $submissionId)
     {
          try {

               $db = \Config\Database::connect();

               if (!$formId || !$submissionId) return;

               // 1. Get submission (latest data)
               $submission = $db->table('form_submissions')
                    ->where('id', $submissionId)
                    ->get()
                    ->getRowArray();

               if (!$submission) return;

               $header = json_decode($submission['header'] ?? '{}', true);

               // ✅ 2. CHECK: Only proceed if PDF is generated
               if (empty($header['pdf_file_id'])) {
                    log_message('info', "⏳ PDF not ready yet, skipping email (submission: $submissionId)");
                    return;
               }

               // ✅ 3. Prevent duplicate email
               if (!empty($header['email_sent']) && $header['email_sent'] == 1) {
                    log_message('info', "⚠️ Email already sent, skipping (submission: $submissionId)");
                    return;
               }

               $pdfFileId = $header['pdf_file_id'];

               // 4. Get PDF file
               $file = $db->table('files')
                    ->where('file_id', $pdfFileId)
                    ->get()
                    ->getRowArray();

               if (!$file) {
                    log_message('error', "❌ PDF record not found");
                    return;
               }

               $filePath = WRITEPATH . 'uploads/secure_files/' . $file['file_name'];

               if (!is_file($filePath)) {
                    log_message('error', "❌ PDF file missing: $filePath");
                    return;
               }

               // ✅ 5. Get template
               $tmpl = $db->table('email_templates')
                    ->where('form_id', $formId)
                    ->get()
                    ->getRowArray();

               if (!$tmpl) return;

               // ✅ 6. Prepare variables (ALL DATA goes into PDF already)
               $vars = [
                    'submission_id' => $submissionId,
                    'form_id' => $formId,
                    'branch_name' => $header['branch_name'] ?? '',
                    'audited_by' => $header['audited_by'] ?? '',
               ];

               // ✅ 7. Recipient selection: allow environment overrides for special flows
               $details = json_decode($submission['details'] ?? '{}', true);
               $nameSource = trim((string) ($submission['form_name'] ?? ($header['form_name'] ?? ($details['form_name'] ?? ''))));
               $normalized = strtolower($nameSource);
               $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized);

               $isItChecklistFlow = (strpos($normalized, 'it checklist') !== false)
                    || (strpos($normalized, 'information technology') !== false)
                    || preg_match('/\bit\b/i', $normalized);
               $isLabDailyChecklistFlow = (strpos($normalized, 'phlebotomy checklist') !== false)
                    || (strpos($normalized, 'lab daily') !== false);
               $isLabWeeklyChecklistFlow = (strpos($normalized, 'lab weekly checklist') !== false)
                    || (strpos($normalized, 'lab weekly') !== false);

               $customTo = '';
               if ($isItChecklistFlow) {
                    $customTo = trim((string) (getenv('IT_CHECKLIST_TO_EMAIL') ?: ''));
               } elseif ($isLabDailyChecklistFlow) {
                    $customTo = trim((string) (getenv('LAB_MANAGER_DAILY_CHECKLIST_TO_EMAIL') ?: ''));
               } elseif ($isLabWeeklyChecklistFlow) {
                    $customTo = trim((string) (getenv('LAB_MANAGER_WEEKLY_CHECKLIST_TO_EMAIL') ?: ''));
               }

               // Prefer environment override when present, otherwise use branch_email from header
               $to = $customTo !== '' ? $customTo : ($header['branch_email'] ?? null);

               if (!$to || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                    log_message('error', "❌ Invalid email (to={$to}) for submission: {$submissionId}");
                    return;
               }

               // ✅ 8. Send email with PDF
               $emailController = new \App\Controllers\EmailController();

               // Pass the PDF file path as the `attachments` argument (7th param)
               $res = $emailController->sendTemplate(
                    $tmpl['event_key'],
                    $to,
                    null,
                    $vars,
                    null,
                    null,
                    $filePath
               );

               // ✅ 9. Mark as sent (VERY IMPORTANT)
               if (strtolower($res) === 'email sent') {
                    $header['email_sent'] = 1;

                    $db->table('form_submissions')
                         ->where('id', $submissionId)
                         ->update([
                              'header' => json_encode($header),
                              'updated_dtm' => date('Y-m-d H:i:s')
                         ]);

                    log_message('info', "✅ Email sent with PDF for submission: $submissionId");
               }
          } catch (\Throwable $e) {
               log_message('error', 'Email error: ' . $e->getMessage());
          }
     }

     private function generatePdfWithImages($submissionId)
     {
          // Get submission
          $db = \Config\Database::connect();

          // Get submission
          $submission = $db->table('form_submissions')
               ->where('id', $submissionId)
               ->get()
               ->getRowArray();

          if (!$submission) return null;

          $details = json_decode($submission['details'] ?? '{}', true);

          // Determine form name and PDF type to choose the appropriate PdfService builder
          // Prefer the form name present on the submission row or in the header
          $resolvedFormName = '';
          $hdr = [];
          if (!empty($submission['header']) && is_string($submission['header'])) {
               $hdr = json_decode($submission['header'], true) ?: [];
          } elseif (!empty($submission['header']) && is_array($submission['header'])) {
               $hdr = $submission['header'];
          }

          // Prefer submission-level form_name, then header.form_name, then fall back to DB lookup by form_id
          $resolvedFormName = trim((string) ($submission['form_name'] ?? ($hdr['form_name'] ?? '')));
          try {
               $formId = (int) ($submission['form_id'] ?? 0);
               if ($resolvedFormName === '' && $formId > 0) {
                    $fr = $db->table('vdc_forms')->select('form_name')->where('id', $formId)->get()->getRowArray();
                    $resolvedFormName = trim((string) ($fr['form_name'] ?? ''));
               }
          } catch (\Throwable $__) {
               // leave resolvedFormName as-is (possibly empty)
          }

          $pdfBytes = null;
          $isMaintenance = false;
          $isItChecklist = false;
          $isLabDailyChecklist = false;
          $isLabWeeklyChecklist = false;

          try {
               // Consolidate name source: prefer resolvedFormName, otherwise details.form_name
               $nameSource = $resolvedFormName !== '' ? $resolvedFormName : ($details['form_name'] ?? '');
               $pdfType = strtolower(trim((string)($details['pdf_type'] ?? '')));
               $flagRaw = $details['is_maintenance_form'] ?? null;

               // Normalize: lowercase and replace any non-alphanumeric with space so phrases like
               // "it_checklist" become "it checklist" and word-boundary checks work reliably.
               $normalized = strtolower(trim((string)$nameSource));
               $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized);

               // Maintenance detection: explicit pdf_type or keywords or a truthy flag in details
               $isMaintenance = ($pdfType === 'maintenance') || (strpos($normalized, 'maintenance') !== false);
               if (!$isMaintenance && $flagRaw !== null) {
                    $flag = strtolower(trim((string)$flagRaw));
                    $isMaintenance = in_array($flag, ['1', 'true', 'yes', 'y'], true);
               }

               // IT checklist: match explicit phrases or standalone 'it' token
               $isItChecklist = (strpos($normalized, 'it checklist') !== false)
                    || (strpos($normalized, 'information technology') !== false)
                    || preg_match('/\bit\b/i', $normalized);

               // Lab checklists
               $isLabDailyChecklist = (strpos($normalized, 'phlebotomy checklist') !== false)
                    || (strpos($normalized, 'lab daily') !== false);
               $isLabWeeklyChecklist = (strpos($normalized, 'lab weekly checklist') !== false)
                    || (strpos($normalized, 'lab weekly') !== false);

               // Prefer specialized builders when available
               if ($isMaintenance) {
                    $pdfBytes = \App\Services\PdfService::buildMaintenancePdf($submissionId);
               } elseif ($isItChecklist) {
                    $pdfBytes = \App\Services\PdfService::buildItPdf($submissionId);
               } elseif ($isLabDailyChecklist) {
                    $pdfBytes = \App\Services\PdfService::buildLabDailyPdf($submissionId);
               } elseif ($isLabWeeklyChecklist) {
                    $pdfBytes = \App\Services\PdfService::buildLabWeeklyPdf($submissionId);
               } else {
                    // Default: try the PdfService checklist builder (preferred)
                    try {
                         $pdfBytes = \App\Services\PdfService::buildChecklistPdf($submissionId);
                    } catch (\Throwable $__chk) {
                         $pdfBytes = null;
                    }
               }
          } catch (\Throwable) {
               // fall through to DOMPDF fallback below
               $pdfBytes = null;
          }

          // If a specialized PdfService produced bytes, save and return
          if (!empty($pdfBytes)) {
               try {
                    $pdfName = "submission_{$submissionId}.pdf";
                    $pdfPath = WRITEPATH . 'uploads/secure_files/' . $pdfName;
                    file_put_contents($pdfPath, $pdfBytes);
                    $db->table('files')->insert([
                         'file_name' => $pdfName,
                         'submission_id' => $submissionId,
                    ]);
                    return $db->insertID();
               } catch (\Throwable $__saveEx) {
                    // if saving fails, fall through to generic generator
               }
          }

          // Do not generate custom HTML PDFs here — require PdfService templates.
          // If PdfService did not produce bytes above, return null so caller knows
          // no PDF was created and can decide how to proceed.
          return null;
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

     private function resolveAuthEmpCode($user): string
     {
          if (!is_object($user)) {
               return '';
          }

          $candidates = [
               $user->emp_code ?? null,
               $user->sub ?? null,
               $user->username ?? null,
          ];

          foreach ($candidates as $candidate) {
               $value = is_scalar($candidate) ? trim((string) $candidate) : '';
               if ($value !== '') {
                    return $value;
               }
          }

          return '';
     }

     private function resolveAuthRole($user): string
     {
          if (!is_object($user)) {
               return '';
          }

          $role = is_scalar($user->role ?? null) ? trim((string) $user->role) : '';
          return $role === '' ? '' : strtoupper($role);
     }

     private function canEditSubmission(array $submission, $user): bool
     {
          $role = $this->resolveAuthRole($user);
          $empCode = $this->resolveAuthEmpCode($user);
          $createdBy = trim((string) ($submission['created_by'] ?? ''));

          if ($empCode !== '' && $createdBy !== '' && $createdBy === $empCode) {
               return true;
          }

          return in_array($role, ['SUPER_ADMIN', 'ADMIN', 'AGM_BRANDING'], true);
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
}

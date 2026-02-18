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

          try {
               if ($this->usesSubmissionTable()) {
                    $result = $this->formSubmissionsModel->datatableListFiltered($start, $length, (string) $search, $orderCol, (string) $orderDir, (array) $columns, $formId > 0 ? $formId : null);
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

               if ($useSubmissionTable) {
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
                         'action' => 'createSubmission',
                         'entity_type' => 'form_submission',
                         'entity_id' => $useSubmissionTable ? (string)$numericSubmissionId : (string)$submissionUuid,
                         'user_id' => $empCode,
                    ]);
               } catch (\Throwable $e) {
                    log_message('error', 'DynamicForm::create log failed: ' . $e->getMessage());
               }

               return $this->respondCreated([
                    'status' => true,
                    'message' => 'Saved',
                    'inserted' => count($rows),
                    'created_dtm' => $createdDtm,
                    'submission_id' => $useSubmissionTable ? $numericSubmissionId : $submissionUuid,
                    'submission_uuid' => $submissionUuid,
               ]);
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
                    if ($header !== null) {
                         $subUpdate['header'] = $this->toStringValue($header);
                    }
                    if ($status !== null && $status !== '') {
                         $subUpdate['status'] = $status;
                    }
                    $subUpdate['updated_dtm'] = date('Y-m-d H:i:s');
                    $subUpdate['updated_by'] = $empCode;

                    $db->table('form_submissions')->where('id', (int) $sid)->update($subUpdate);
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

               return $this->respond(['message' => 'Updated', 'submission_id' => $sid], 200);
          } catch (\Throwable $e) {
               $db->transRollback();
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

          // Track upload start time for diagnostics
          $uploadStart = microtime(true);

          // If using submission table and numeric id, ensure submission exists
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

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\JwtService;
use App\Models\LogsModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class FormInputsController extends BaseController
{
     use ResponseTrait;

     protected $formInputsModel;

     public function __construct()
     {
          $this->formInputsModel = model('App\\Models\\FormInputsModel');
     }

     public function getFormInputs()
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $q = trim((string) $this->request->getGet('q'));
          $formId = (int) $this->request->getGet('form_id');
          $status = strtoupper(trim((string) $this->request->getGet('status')));
          $builder = $this->formInputsModel->builder();
          $builder->select('id, input_name, input_label, input_icon, input_icon_color, input_col, input_type, input_value, data_source, map_fields, input_placeholder, show_when_field, show_when_value, show_operator, required_when_field, required_when_value, remarks_required_when, photo_required_when, custom_value, input_class, input_required, input_readonly, input_disabled, input_min, input_max, input_step, input_pattern, input_maxlength, input_options, input_order, file_accept, max_file_size, compress_image, form_id, section_id, sub_section_id, status');

          if ($formId > 0) {
               $builder->where('form_id', $formId);
          }
          if (in_array($status, ['A', 'I'], true)) {
               $builder->where('status', $status);
          }
          if ($q !== '') {
               $builder->groupStart()
                    ->like('input_name', $q)
                    ->orLike('input_type', $q)
                    ->groupEnd();
          }
          $builder->orderBy('id', 'DESC');
          $rows = $builder->get()->getResultArray();

          return $this->respond([
               'status' => true,
               'message' => 'Form inputs list',
               'data' => $rows,
          ], 200);
     }

     public function getFormInputById($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $inputId = is_numeric($id) ? (int) $id : 0;
          if ($inputId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $row = $this->formInputsModel->find($inputId);
          if (!$row) {
               return $this->respond(['status' => false, 'message' => 'Form input not found'], 404);
          }

          return $this->respond(['status' => true, 'data' => $row], 200);
     }

     public function addFormInput()
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }
          if (!is_array($payload)) {
               $payload = [];
          }

          $normalizeBool = static function ($value): int {
               if (is_bool($value)) return $value ? 1 : 0;
               if (is_int($value)) return $value ? 1 : 0;
               $s = strtolower(trim((string) $value));
               return in_array($s, ['1', 'true', 'yes', 'y', 'on'], true) ? 1 : 0;
          };

          $normalizeNullableInt = static function ($value) {
               if ($value === null) return null;
               $s = trim((string) $value);
               if ($s === '') return null;
               return (int) $s;
          };

          $normalizeNullableString = static function ($value) {
               if ($value === null) return null;
               $s = trim((string) $value);
               return $s === '' ? null : $s;
          };

          $status = strtoupper(trim((string) ($payload['status'] ?? 'A')));
          if (!in_array($status, ['A', 'I'], true)) {
               $status = 'A';
          }

          $rawShowOperator = $payload['show_operator'] ?? null;
          $showOperator = null;
          if ($rawShowOperator !== null) {
               $s = trim((string) $rawShowOperator);
               if ($s !== '') {
                    if (!in_array($s, ['=', '!=', '>', '<'], true)) {
                         return $this->respond(['status' => false, 'message' => 'Invalid show operator'], 400);
                    }
                    $showOperator = $s;
               }
          }

          $data = [
               'input_name' => trim((string) ($payload['input_name'] ?? '')),
               'input_label' => $normalizeNullableString($payload['input_label'] ?? null),
               'input_icon' => $normalizeNullableString($payload['input_icon'] ?? null),
               'input_icon_color' => $normalizeNullableString($payload['input_icon_color'] ?? null),
               'input_col' => $normalizeNullableString($payload['input_col'] ?? null) ?? 'col-md-3',
               'input_type' => trim((string) ($payload['input_type'] ?? '')),
               'input_value' => isset($payload['input_value']) ? (string) $payload['input_value'] : null,
               'data_source' => $normalizeNullableString($payload['data_source'] ?? null),
               'map_fields' => $normalizeNullableString($payload['map_fields'] ?? null),
               'input_placeholder' => trim((string) ($payload['input_placeholder'] ?? '')),
               'show_when_field' => trim((string) ($payload['show_when_field'] ?? '')),
               'show_when_value' => trim((string) ($payload['show_when_value'] ?? '')),
               'show_operator' => $showOperator,
               'required_when_field' => $normalizeNullableString($payload['required_when_field'] ?? null),
               'required_when_value' => $normalizeNullableString($payload['required_when_value'] ?? null),
               'remarks_required_when' => $normalizeNullableString(is_array($payload['remarks_required_when'] ?? null) ? implode(', ', $payload['remarks_required_when']) : ($payload['remarks_required_when'] ?? null)),
               'photo_required_when' => $normalizeNullableString(is_array($payload['photo_required_when'] ?? null) ? implode(', ', $payload['photo_required_when']) : ($payload['photo_required_when'] ?? null)),
               'custom_value' => $normalizeNullableString($payload['custom_value'] ?? null),
               'input_class' => trim((string) ($payload['input_class'] ?? '')),
               'input_required' => $normalizeBool($payload['input_required'] ?? 0),
               'input_readonly' => $normalizeBool($payload['input_readonly'] ?? 0),
               'input_disabled' => $normalizeBool($payload['input_disabled'] ?? 0),
               'input_min' => trim((string) ($payload['input_min'] ?? '')),
               'input_max' => trim((string) ($payload['input_max'] ?? '')),
               'input_step' => trim((string) ($payload['input_step'] ?? '')),
               'input_pattern' => trim((string) ($payload['input_pattern'] ?? '')),
               'input_maxlength' => $normalizeNullableInt($payload['input_maxlength'] ?? null),
               'input_options' => isset($payload['input_options']) ? (string) $payload['input_options'] : null,
               'input_order' => (int) ($payload['input_order'] ?? 0),
               'file_accept' => $normalizeNullableString($payload['file_accept'] ?? null),
               'max_file_size' => $normalizeNullableInt($payload['max_file_size'] ?? null) ?? 2048,
               'compress_image' => $normalizeBool($payload['compress_image'] ?? 0),
               'form_id' => (int) ($payload['form_id'] ?? 0),
               'section_id' => $normalizeNullableInt($payload['section_id'] ?? null),
               'sub_section_id' => $normalizeNullableInt($payload['sub_section_id'] ?? null),
               'status' => $status,
          ];

          $renamed = false;
          if ($data['form_id'] > 0 && $data['input_name'] !== '') {
               $unique = $this->makeUniqueInputName($data['input_name'], (int) $data['form_id'], null);
               $data['input_name'] = $unique['name'];
               $renamed = $unique['renamed'];
          }

          // Enforce DB constraint: input_name max length 50
          if (strlen((string) $data['input_name']) > 50) {
               return $this->respond(['status' => false, 'message' => 'input_name must be 50 characters or fewer'], 400);
          }

          $id = $this->formInputsModel->insert($data);
          if (!$id) {
               return $this->respond([
                    'status' => false,
                    'message' => $this->formInputsModel->errors(),
               ], 400);
          }

          // Log create form input
          try {
               $lm = new LogsModel();
               $lm->insertLog([
                    'uri' => $this->request->getURI()->getPath(),
                    'method' => $this->request->getMethod(),
                    'params' => $data,
                    'ip_address' => $this->request->getIPAddress(),
                    'time' => time(),
                    'authorized' => 'Y',
                    'response_code' => 201,
                    'action' => 'addFormInput',
                    'entity_type' => 'form_input',
                    'entity_id' => (string) $id,
                    'user_id' => $this->request->header('X-User') ? $this->request->header('X-User')->getValue() : null,
               ]);
          } catch (\Exception $e) {
               log_message('error', 'Form input create log failed: ' . $e->getMessage());
          }

          return $this->respond([
               'status' => true,
               'message' => 'Form input created',
               'id' => $id,
               'input_name' => $data['input_name'],
               'renamed' => $renamed,
          ], 201);
     }

     public function editFormInput($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $inputId = is_numeric($id) ? (int) $id : 0;
          if ($inputId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formInputsModel->find($inputId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Form input not found'], 404);
          }

          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }
          if (!is_array($payload)) {
               $payload = [];
          }

          $normalizeBool = static function ($value): int {
               if (is_bool($value)) return $value ? 1 : 0;
               if (is_int($value)) return $value ? 1 : 0;
               $s = strtolower(trim((string) $value));
               return in_array($s, ['1', 'true', 'yes', 'y', 'on'], true) ? 1 : 0;
          };

          $normalizeNullableInt = static function ($value) {
               if ($value === null) return null;
               $s = trim((string) $value);
               if ($s === '') return null;
               return (int) $s;
          };

          $normalizeNullableString = static function ($value) {
               if ($value === null) return null;
               $s = trim((string) $value);
               return $s === '' ? null : $s;
          };

          $data = [];
          if (array_key_exists('input_name', $payload)) {
               $data['input_name'] = trim((string) $payload['input_name']);
          }
          if (array_key_exists('input_label', $payload)) {
               $data['input_label'] = $normalizeNullableString($payload['input_label']);
          }
          if (array_key_exists('input_icon', $payload)) {
               $data['input_icon'] = $normalizeNullableString($payload['input_icon']);
          }
          if (array_key_exists('input_icon_color', $payload)) {
               $data['input_icon_color'] = $normalizeNullableString($payload['input_icon_color']);
          }
          if (array_key_exists('input_col', $payload)) {
               $data['input_col'] = $normalizeNullableString($payload['input_col']) ?? 'col-md-3';
          }
          if (array_key_exists('input_type', $payload)) {
               $data['input_type'] = trim((string) $payload['input_type']);
          }
          if (array_key_exists('input_value', $payload)) {
               $data['input_value'] = $payload['input_value'];
          }
          if (array_key_exists('data_source', $payload)) {
               $data['data_source'] = $normalizeNullableString($payload['data_source']);
          }
          if (array_key_exists('map_fields', $payload)) {
               $data['map_fields'] = $normalizeNullableString($payload['map_fields']);
          }
          if (array_key_exists('input_placeholder', $payload)) {
               $data['input_placeholder'] = trim((string) $payload['input_placeholder']);
          }
          if (array_key_exists('show_when_field', $payload)) {
               $data['show_when_field'] = trim((string) $payload['show_when_field']);
          }
          if (array_key_exists('show_when_value', $payload)) {
               $data['show_when_value'] = trim((string) $payload['show_when_value']);
          }
          if (array_key_exists('show_operator', $payload)) {
               $s = trim((string) $payload['show_operator']);
               if ($s === '') {
                    $data['show_operator'] = null;
               } else {
                    if (!in_array($s, ['=', '!=', '>', '<'], true)) {
                         return $this->respond(['status' => false, 'message' => 'Invalid show operator'], 400);
                    }
                    $data['show_operator'] = $s;
               }
          }
          if (array_key_exists('required_when_field', $payload)) {
               $data['required_when_field'] = $normalizeNullableString($payload['required_when_field']);
          }
          if (array_key_exists('required_when_value', $payload)) {
               $data['required_when_value'] = $normalizeNullableString($payload['required_when_value']);
          }
          if (array_key_exists('remarks_required_when', $payload)) {
               $val = $payload['remarks_required_when'];
               if (is_array($val)) $val = implode(', ', $val);
               $data['remarks_required_when'] = $normalizeNullableString($val);
          }
          if (array_key_exists('photo_required_when', $payload)) {
               $val = $payload['photo_required_when'];
               if (is_array($val)) $val = implode(', ', $val);
               $data['photo_required_when'] = $normalizeNullableString($val);
          }
          if (array_key_exists('custom_value', $payload)) {
               $data['custom_value'] = $normalizeNullableString($payload['custom_value']);
          }
          if (array_key_exists('input_class', $payload)) {
               $data['input_class'] = trim((string) $payload['input_class']);
          }
          if (array_key_exists('input_required', $payload)) {
               $data['input_required'] = $normalizeBool($payload['input_required']);
          }
          if (array_key_exists('input_readonly', $payload)) {
               $data['input_readonly'] = $normalizeBool($payload['input_readonly']);
          }
          if (array_key_exists('input_disabled', $payload)) {
               $data['input_disabled'] = $normalizeBool($payload['input_disabled']);
          }
          if (array_key_exists('input_min', $payload)) {
               $data['input_min'] = trim((string) $payload['input_min']);
          }
          if (array_key_exists('input_max', $payload)) {
               $data['input_max'] = trim((string) $payload['input_max']);
          }
          if (array_key_exists('input_step', $payload)) {
               $data['input_step'] = trim((string) $payload['input_step']);
          }
          if (array_key_exists('input_pattern', $payload)) {
               $data['input_pattern'] = trim((string) $payload['input_pattern']);
          }
          if (array_key_exists('input_maxlength', $payload)) {
               $data['input_maxlength'] = $normalizeNullableInt($payload['input_maxlength']);
          }
          if (array_key_exists('input_options', $payload)) {
               $data['input_options'] = $payload['input_options'];
          }
          if (array_key_exists('input_order', $payload)) {
               $data['input_order'] = (int) $payload['input_order'];
          }
          if (array_key_exists('file_accept', $payload)) {
               $data['file_accept'] = $normalizeNullableString($payload['file_accept']);
          }
          if (array_key_exists('max_file_size', $payload)) {
               $data['max_file_size'] = $normalizeNullableInt($payload['max_file_size']) ?? 2048;
          }
          if (array_key_exists('compress_image', $payload)) {
               $data['compress_image'] = $normalizeBool($payload['compress_image']);
          }
          if (array_key_exists('form_id', $payload)) {
               $data['form_id'] = (int) $payload['form_id'];
          }
          if (array_key_exists('section_id', $payload)) {
               $data['section_id'] = $normalizeNullableInt($payload['section_id']);
          }
          if (array_key_exists('sub_section_id', $payload)) {
               $data['sub_section_id'] = $normalizeNullableInt($payload['sub_section_id']);
          }
          if (array_key_exists('status', $payload)) {
               $status = strtoupper(trim((string) $payload['status']));
               if (!in_array($status, ['A', 'I'], true)) {
                    return $this->respond(['status' => false, 'message' => 'Invalid status'], 400);
               }
               $data['status'] = $status;
          }

          if ($data === []) {
               return $this->respond(['status' => false, 'message' => 'No fields to update'], 400);
          }

          $renamed = false;
          $isRelevantForUniqueness = array_key_exists('input_name', $data) || array_key_exists('form_id', $data);
          if ($isRelevantForUniqueness) {
               $formId = array_key_exists('form_id', $data) ? (int) $data['form_id'] : (int) ($existing['form_id'] ?? 0);
               $desired = array_key_exists('input_name', $data) ? (string) $data['input_name'] : (string) ($existing['input_name'] ?? '');
               $desired = trim($desired);

               if ($formId > 0 && $desired !== '') {
                    $unique = $this->makeUniqueInputName($desired, $formId, $inputId);
                    if ($unique['name'] !== $desired) {
                         $data['input_name'] = $unique['name'];
                    }
                    $renamed = $unique['renamed'];
               }
          }

          // Enforce DB constraint on update as well
          if (array_key_exists('input_name', $data) && strlen((string) $data['input_name']) > 50) {
               return $this->respond(['status' => false, 'message' => 'input_name must be 50 characters or fewer'], 400);
          }

          $ok = $this->formInputsModel->update($inputId, $data);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => $this->formInputsModel->errors()], 400);
          }

          // Log update
          try {
               $lm = new LogsModel();
               $lm->insertLog([
                    'uri' => $this->request->getURI()->getPath(),
                    'method' => $this->request->getMethod(),
                    'params' => $data,
                    'ip_address' => $this->request->getIPAddress(),
                    'time' => time(),
                    'authorized' => 'Y',
                    'response_code' => 200,
                    'action' => 'editFormInput',
                    'entity_type' => 'form_input',
                    'entity_id' => (string) $inputId,
                    'user_id' => $this->request->header('X-User') ? $this->request->header('X-User')->getValue() : null,
               ]);
          } catch (\Exception $e) {
               log_message('error', 'Form input edit log failed: ' . $e->getMessage());
          }

          $finalName = array_key_exists('input_name', $data) ? (string) $data['input_name'] : (string) ($existing['input_name'] ?? '');
          return $this->respond([
               'status' => true,
               'message' => 'Form input updated',
               'input_name' => $finalName,
               'renamed' => $renamed,
          ], 200);
     }

     private function inputNameExists(string $candidate, int $formId, ?int $excludeId = null): bool
     {
          if ($formId <= 0) return false;

          $builder = $this->formInputsModel->builder();
          $builder->select('id');
          $builder->where('form_id', $formId);
          $builder->where('LOWER(input_name)', strtolower($candidate));
          if ($excludeId !== null && $excludeId > 0) {
               $builder->where('id !=', $excludeId);
          }

          $row = $builder->get(1)->getRowArray();
          return (bool) $row;
     }

     private function makeUniqueInputName(string $desired, int $formId, ?int $excludeId = null): array
     {
          $name = trim($desired);
          if ($name === '' || $formId <= 0) {
               return ['name' => $name, 'renamed' => false];
          }

          $maxLen = 50;

          // If already unique, keep as-is.
          if (!$this->inputNameExists($name, $formId, $excludeId)) {
               return ['name' => $name, 'renamed' => false];
          }

          $base = preg_replace('/_\d+$/', '', $name);
          if (!is_string($base) || $base === '') {
               $base = $name;
          }

          $counter = 2;
          while ($counter < 10000) {
               $suffix = '_' . $counter;
               $allowedBaseLen = $maxLen - strlen($suffix);
               $basePart = $allowedBaseLen > 0 ? substr($base, 0, $allowedBaseLen) : '';
               $candidate = $basePart . $suffix;

               if (!$this->inputNameExists($candidate, $formId, $excludeId)) {
                    return ['name' => $candidate, 'renamed' => true];
               }
               $counter += 1;
          }

          // Fallback (should never hit in real-world usage).
          return ['name' => substr($base, 0, $maxLen), 'renamed' => true];
     }

     public function deleteFormInput($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $inputId = is_numeric($id) ? (int) $id : (int) $this->request->getPost('id');
          if ($inputId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formInputsModel->find($inputId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Form input not found'], 404);
          }

          $ok = $this->formInputsModel->delete($inputId);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => 'Delete failed'], 500);
          }

          return $this->respond(['status' => true, 'message' => 'Form input deleted'], 200);
     }

     private function validateAuthorization()
     {
          if (!class_exists('App\\Services\\JwtService')) {
               log_message('error', 'JwtService class not found');
               return $this->respond(['error' => 'JwtService class not found'], 500);
          }

          $authHeaderObj = $this->request->header('Authorization');
          $authorizationHeader = $authHeaderObj ? $authHeaderObj->getValue() : null;

          try {
               $jwtService = new JwtService();
               $result = $jwtService->validateToken($authorizationHeader);
               if (isset($result['error'])) {
                    log_message('error', $result['error']);
                    return $this->respond(['error' => $result['error']], $result['status'] ?? 401);
               }
               return $result['data'] ?? null;
          } catch (\Exception $e) {
               log_message('error', "JWT validation failed: {$e->getMessage()}");
               return $this->respond(['error' => 'Invalid or expired token'], 401);
          }
     }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\JwtService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\FormSectionsModel;

class FormSectionsController extends BaseController
{
     use ResponseTrait;

     protected $formSectionsModel;

     public function __construct()
     {
          $this->formSectionsModel = model('App\\Models\\FormSectionsModel');
     }

     public function getFormSections()
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $deptId = $this->request->getGet('dept_id');
          $formId = $this->request->getGet('form_id');

          $builder = $this->formSectionsModel->builder();
          $builder->select('section_id, section_name, section_icon, dept_id, form_id, status');

          if ($deptId !== null && $deptId !== '' && is_numeric($deptId)) {
               $builder->where('dept_id', (int) $deptId);
          }
          if ($formId !== null && $formId !== '' && is_numeric($formId)) {
               $builder->where('form_id', (int) $formId);
          }

          $builder->orderBy('section_id', 'DESC');
          $rows = $builder->get()->getResultArray();

          return $this->respond([
               'status' => true,
               'message' => 'Form sections list',
               'data' => $rows,
          ], 200);
     }

     public function getFormSectionById($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $sectionId = is_numeric($id) ? (int) $id : 0;
          if ($sectionId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $row = $this->formSectionsModel->find($sectionId);
          if (!$row) {
               return $this->respond(['status' => false, 'message' => 'Section not found'], 404);
          }

          return $this->respond(['status' => true, 'data' => $row], 200);
     }

     public function addFormSection()
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

          $status = strtoupper(trim((string) ($payload['status'] ?? 'A')));
          if (!in_array($status, ['A', 'I'], true)) {
               $status = 'A';
          }

          $data = [
               'section_name' => trim((string) ($payload['section_name'] ?? '')),
               'section_icon' => isset($payload['section_icon']) ? trim((string) $payload['section_icon']) : null,
               'dept_id' => $payload['dept_id'] ?? null,
               'form_id' => $payload['form_id'] ?? null,
               'status' => $status,
          ];

          $id = $this->formSectionsModel->insert($data);
          if (!$id) {
               return $this->respond(['status' => false, 'message' => $this->formSectionsModel->errors()], 400);
          }

          return $this->respond(['status' => true, 'message' => 'Section created', 'id' => $id], 201);
     }

     public function editFormSection($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $sectionId = is_numeric($id) ? (int) $id : 0;
          if ($sectionId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formSectionsModel->find($sectionId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Section not found'], 404);
          }

          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }
          if (!is_array($payload)) {
               $payload = [];
          }

          $data = [];
          if (array_key_exists('section_name', $payload)) {
               $data['section_name'] = trim((string) $payload['section_name']);
          }
          if (array_key_exists('section_icon', $payload)) {
               $icon = trim((string) $payload['section_icon']);
               $data['section_icon'] = $icon === '' ? null : $icon;
          }
          if (array_key_exists('dept_id', $payload)) {
               $data['dept_id'] = $payload['dept_id'];
          }
          if (array_key_exists('form_id', $payload)) {
               $data['form_id'] = $payload['form_id'];
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

          $ok = $this->formSectionsModel->update($sectionId, $data);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => $this->formSectionsModel->errors()], 400);
          }

          return $this->respond(['status' => true, 'message' => 'Section updated'], 200);
     }

     public function deleteFormSection($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $sectionId = is_numeric($id) ? (int) $id : (int) $this->request->getPost('section_id');
          if ($sectionId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formSectionsModel->find($sectionId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Section not found'], 404);
          }

          $ok = $this->formSectionsModel->delete($sectionId);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => 'Delete failed'], 500);
          }

          return $this->respond(['status' => true, 'message' => 'Section deleted'], 200);
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

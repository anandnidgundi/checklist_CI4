<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\JwtService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class FormSubSectionsController extends BaseController
{
     use ResponseTrait;

     protected $formSubSectionsModel;

     public function __construct()
     {
          $this->formSubSectionsModel = model('App\\Models\\FormSubSectionsModel');
     }

     public function getFormSubSections()
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $sectionId = $this->request->getGet('section_id');
          $formId = $this->request->getGet('form_id');
          $builder = $this->formSubSectionsModel->builder();
          $builder->select('sub_section_id, form_id, section_id, sub_section_name, sub_section_icon, status, created_dtm');

          if ($formId !== null && $formId !== '' && is_numeric($formId)) {
               $builder->where('form_id', (int) $formId);
          }
          if ($sectionId !== null && $sectionId !== '' && is_numeric($sectionId)) {
               $builder->where('section_id', (int) $sectionId);
          }
          $builder->orderBy('sub_section_id', 'DESC');
          $rows = $builder->get()->getResultArray();

          return $this->respond([
               'status' => true,
               'message' => 'Form sub sections list',
               'data' => $rows,
          ], 200);
     }

     public function getFormSubSectionById($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $subSectionId = is_numeric($id) ? (int) $id : 0;
          if ($subSectionId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $row = $this->formSubSectionsModel->find($subSectionId);
          if (!$row) {
               return $this->respond(['status' => false, 'message' => 'Sub section not found'], 404);
          }

          return $this->respond(['status' => true, 'data' => $row], 200);
     }

     public function addFormSubSection()
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
               'form_id' => $payload['form_id'] ?? null,
               'section_id' => $payload['section_id'] ?? null,
               'sub_section_name' => trim((string) ($payload['sub_section_name'] ?? '')),
               'sub_section_icon' => isset($payload['sub_section_icon']) ? trim((string) $payload['sub_section_icon']) : null,
               'status' => $status,
          ];

          $id = $this->formSubSectionsModel->insert($data);
          if (!$id) {
               return $this->respond(['status' => false, 'message' => $this->formSubSectionsModel->errors()], 400);
          }

          return $this->respond(['status' => true, 'message' => 'Sub section created', 'id' => $id], 201);
     }

     public function editFormSubSection($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $subSectionId = is_numeric($id) ? (int) $id : 0;
          if ($subSectionId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formSubSectionsModel->find($subSectionId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Sub section not found'], 404);
          }

          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }
          if (!is_array($payload)) {
               $payload = [];
          }

          $data = [];
          if (array_key_exists('form_id', $payload)) {
               $data['form_id'] = $payload['form_id'];
          }
          if (array_key_exists('section_id', $payload)) {
               $data['section_id'] = $payload['section_id'];
          }
          if (array_key_exists('sub_section_name', $payload)) {
               $data['sub_section_name'] = trim((string) $payload['sub_section_name']);
          }
          if (array_key_exists('sub_section_icon', $payload)) {
               $icon = trim((string) $payload['sub_section_icon']);
               $data['sub_section_icon'] = $icon === '' ? null : $icon;
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

          $ok = $this->formSubSectionsModel->update($subSectionId, $data);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => $this->formSubSectionsModel->errors()], 400);
          }

          return $this->respond(['status' => true, 'message' => 'Sub section updated'], 200);
     }

     public function deleteFormSubSection($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $subSectionId = is_numeric($id) ? (int) $id : (int) $this->request->getPost('sub_section_id');
          if ($subSectionId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formSubSectionsModel->find($subSectionId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Sub section not found'], 404);
          }

          $ok = $this->formSubSectionsModel->delete($subSectionId);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => 'Delete failed'], 500);
          }

          return $this->respond(['status' => true, 'message' => 'Sub section deleted'], 200);
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

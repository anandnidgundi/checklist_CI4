<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\JwtService;
use App\Models\LogsModel;

class FormsController extends BaseController
{
     use ResponseTrait;

     protected $formsModel;

     public function __construct()
     {
          $this->formsModel = model('App\\Models\\FormsModel');
     }

     public function getFormsList()
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $q = trim((string) $this->request->getGet('q'));
          $builder = $this->formsModel->builder();
          $builder->select('id, form_name, form_description, created_dtm, created_by, status');
          if ($q !== '') {
               $builder->like('form_name', $q);
          }
          $builder->orderBy('id', 'DESC');
          $rows = $builder->get()->getResultArray();

          return $this->respond([
               'status' => true,
               'message' => 'Forms list',
               'data' => $rows,
          ], 200);
     }

     public function getFormById($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $formId = is_numeric($id) ? (int) $id : 0;
          if ($formId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $row = $this->formsModel->find($formId);
          if (!$row) {
               return $this->respond(['status' => false, 'message' => 'Form not found'], 404);
          }

          return $this->respond(['status' => true, 'data' => $row], 200);
     }

     public function createForm()
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
          $formName = trim((string) ($payload['form_name'] ?? ''));
          if ($formName === '') {
               return $this->respond([
                    'status' => false,
                    'message' => ['form_name' => 'The form_name field is required.'],
               ], 400);
          }

          $createdBy = $payload['created_by'] ?? null;
          if ($createdBy === null && isset($auth->emp_code)) {
               $createdBy = $auth->emp_code;
          }

          if ($createdBy === null || $createdBy === '' || !is_numeric($createdBy)) {
               return $this->respond([
                    'status' => false,
                    'message' => ['created_by' => 'The created_by field is required and must be numeric.'],
               ], 400);
          }

          $status = strtoupper(trim((string) ($payload['status'] ?? 'A')));
          if (!in_array($status, ['A', 'I'], true)) {
               $status = 'A';
          }

          $data = [
               'form_name' => $formName,
               'form_description' => $payload['form_description'] ?? null,
               'created_by' => (int) $createdBy,
               'status' => $status,
               // created_dtm set by model callback if missing
          ];

          $id = $this->formsModel->insert($data);
          if (!$id) {
               return $this->respond([
                    'status' => false,
                    'message' => $this->formsModel->errors(),
               ], 400);
          }

          // Log form creation
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
                    'action' => 'createForm',
                    'entity_type' => 'form',
                    'entity_id' => (string) $id,
                    'user_id' => $createdBy,
               ]);
          } catch (\Exception $e) {
               log_message('error', 'Form create log failed: ' . $e->getMessage());
          }

          return $this->respond([
               'status' => true,
               'message' => 'Form created',
               'id' => $id,
          ], 201);
     }

     public function updateForm($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $formId = is_numeric($id) ? (int) $id : 0;
          if ($formId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formsModel->find($formId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Form not found'], 404);
          }

          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }
          if (!is_array($payload)) {
               $payload = [];
          }
          $data = [];
          if (array_key_exists('form_name', $payload)) {
               $data['form_name'] = trim((string) $payload['form_name']);
          }
          if (array_key_exists('form_description', $payload)) {
               $data['form_description'] = $payload['form_description'];
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

          $ok = $this->formsModel->update($formId, $data);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => $this->formsModel->errors()], 400);
          }

          // Log form update
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
                    'action' => 'updateForm',
                    'entity_type' => 'form',
                    'entity_id' => (string) $formId,
                    'user_id' => $auth->emp_code ?? null,
               ]);
          } catch (\Exception $e) {
               log_message('error', 'Form update log failed: ' . $e->getMessage());
          }

          return $this->respond(['status' => true, 'message' => 'Form updated'], 200);
     }

     public function deleteForm($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $formId = is_numeric($id) ? (int) $id : (int) $this->request->getPost('id');
          if ($formId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->formsModel->find($formId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Form not found'], 404);
          }

          $ok = $this->formsModel->delete($formId);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => 'Delete failed'], 500);
          }

          return $this->respond(['status' => true, 'message' => 'Form deleted'], 200);
     }

     private function validateAuthorization()
     {
          if (!class_exists('App\Services\JwtService')) {
               log_message('error', 'JwtService class not found');
               return $this->respond(['error' => 'JwtService class not found'], 500);
          }

          // Get the Authorization header (avoid deprecated getHeader + avoid PHP <8 nullsafe)
          $authHeaderObj = $this->request->header('Authorization');
          $authorizationHeader = $authHeaderObj ? $authHeaderObj->getValue() : null;
          log_message('info', "Authorization header: {$authorizationHeader}");

          try {
               // Create an instance of JwtService and validate the token
               $jwtService = new JwtService();
               $result = $jwtService->validateToken($authorizationHeader);

               // Handle token validation errors 
               if (isset($result['error'])) {
                    log_message('error', $result['error']);
                    return $this->respond(['error' => $result['error']], $result['status'] ?? 401);
               }

               // Extract the decoded token and get the USER-ID
               return $result['data'] ?? null;
          } catch (\Exception $e) {
               log_message('error', "JWT validation failed: {$e->getMessage()}");
               return $this->respond(['error' => 'Invalid or expired token'], 401);
          }
     }
}

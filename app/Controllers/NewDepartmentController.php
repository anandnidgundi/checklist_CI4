<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Services\JwtService;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class NewDepartmentController extends BaseController
{
     use ResponseTrait;

     protected $newDepartmentModel;

     public function __construct()
     {
          $this->newDepartmentModel = model('App\\Models\\NewDepartmentModel');
     }

     public function getNewDepartments()
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $status = strtoupper(trim((string) $this->request->getGet('status')));
          if ($status === '') {
               $status = 'A';
          }

          $builder = $this->newDepartmentModel->builder();
          $builder->select('id, dept_name, status');
          if ($status !== 'ALL') {
               $builder->where('status', $status);
          }
          $builder->orderBy('dept_name', 'ASC');
          $rows = $builder->get()->getResultArray();

          return $this->respond([
               'status' => true,
               'message' => 'New department list',
               'data' => $rows,
          ], 200);
     }

     public function addNewDepartment()
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

          $deptName = trim((string) ($payload['dept_name'] ?? ''));
          if ($deptName === '') {
               return $this->respond([
                    'status' => false,
                    'message' => ['dept_name' => 'The dept_name field is required.'],
               ], 400);
          }

          $data = [
               'dept_name' => $deptName,
               'status' => $payload['status'] ?? 'A',
          ];

          $id = $this->newDepartmentModel->insert($data);
          if (!$id) {
               return $this->respond([
                    'status' => false,
                    'message' => $this->newDepartmentModel->errors(),
               ], 400);
          }

          return $this->respond([
               'status' => true,
               'message' => 'New department created',
               'id' => $id,
          ], 201);
     }

     public function editNewDepartment($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $deptId = is_numeric($id) ? (int) $id : 0;
          if ($deptId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->newDepartmentModel->find($deptId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Department not found'], 404);
          }

          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }
          if (!is_array($payload)) {
               $payload = [];
          }

          $data = [];
          if (array_key_exists('dept_name', $payload)) {
               $data['dept_name'] = trim((string) $payload['dept_name']);
          }
          if (array_key_exists('status', $payload)) {
               $data['status'] = $payload['status'];
          }

          if ($data === []) {
               return $this->respond(['status' => false, 'message' => 'No fields to update'], 400);
          }

          $ok = $this->newDepartmentModel->update($deptId, $data);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => $this->newDepartmentModel->errors()], 400);
          }

          return $this->respond(['status' => true, 'message' => 'Department updated'], 200);
     }

     public function deleteNewDepartment($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $deptId = is_numeric($id) ? (int) $id : (int) $this->request->getPost('id');
          if ($deptId <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid id'], 400);
          }

          $existing = $this->newDepartmentModel->find($deptId);
          if (!$existing) {
               return $this->respond(['status' => false, 'message' => 'Department not found'], 404);
          }

          // Prefer soft delete via status flag instead of hard delete
          $ok = $this->newDepartmentModel->update($deptId, ['status' => 'I']);
          if (!$ok) {
               return $this->respond(['status' => false, 'message' => $this->newDepartmentModel->errors()], 400);
          }

          return $this->respond(['status' => true, 'message' => 'Department deactivated'], 200);
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

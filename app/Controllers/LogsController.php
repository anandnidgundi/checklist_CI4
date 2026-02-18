<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\LogsModel;
use App\Services\JwtService;

class LogsController extends BaseController
{
     use ResponseTrait;

     public function addLog()
     {
          $payload = $this->request->getJSON(true);
          if (!is_array($payload)) {
               $payload = $this->request->getPost();
          }

          // Best-effort: if Authorization header provided, attach user info and mark authorized
          $authHeaderObj = $this->request->header('Authorization');
          $authHeader = $authHeaderObj ? $authHeaderObj->getValue() : null;
          if ($authHeader) {
               try {
                    $jwtService = new JwtService();
                    $res = $jwtService->validateToken($authHeader);
                    if (empty($res['error']) && !empty($res['data'])) {
                         $user = $res['data'];
                         if (empty($payload['user_id'])) {
                              $payload['user_id'] = $user->emp_code ?? $user->username ?? null;
                         }
                         $payload['authorized'] = 'Y';

                         // ensure params is an array and include user_role
                         $params = [];
                         if (!empty($payload['params'])) {
                              $params = is_string($payload['params']) ? json_decode($payload['params'], true) : (array)$payload['params'];
                              if (!is_array($params)) $params = [];
                         }
                         $params['user_role'] = $user->role ?? null;
                         $payload['params'] = $params;
                    }
               } catch (\Throwable $e) {
                    // ignore - allow unauthenticated logs
               }
          }

          $logs = new LogsModel();
          $id = $logs->insertLog($payload ?: []);
          if ($id) {
               return $this->respond(['status' => true, 'message' => 'Log recorded', 'id' => $id], 201);
          }
          return $this->respond(['status' => false, 'message' => 'Failed to record log'], 500);
     }

     public function list(int $limit = 100, int $offset = 0)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) {
               return $auth;
          }

          $role = $auth->role ?? '';
          // restrict to admin/audit roles only
          if (!in_array($role, ['SUPER_ADMIN', 'ADMIN', 'AUDIT', 'BRANDING_AUDITOR'], true)) {
               return $this->respond(['status' => false, 'message' => 'Forbidden'], 403);
          }

          $logs = new LogsModel();

          $filters = [
               'entity_type' => trim((string) $this->request->getGet('entity_type')) ?: null,
               'entity_id' => trim((string) $this->request->getGet('entity_id')) ?: null,
               'action' => trim((string) $this->request->getGet('action')) ?: null,
               'user_id' => trim((string) $this->request->getGet('user_id')) ?: null,
               'response_code' => $this->request->getGet('response_code') !== null ? (int)$this->request->getGet('response_code') : null,
               'date_from' => trim((string) $this->request->getGet('date_from')) ?: null,
               'date_to' => trim((string) $this->request->getGet('date_to')) ?: null,
               'q' => trim((string) $this->request->getGet('q')) ?: null,
          ];

          // prefer fast entity lookup when only entity filters specified
          if (!empty($filters['entity_type']) && !empty($filters['entity_id']) && empty($filters['action']) && empty($filters['q']) && empty($filters['user_id']) && empty($filters['date_from']) && empty($filters['date_to'])) {
               $rows = $logs->getByEntity($filters['entity_type'], $filters['entity_id'], $limit, $offset);
               $total = null;
          } else {
               $res = $logs->getFiltered($filters, $limit, $offset);
               $rows = $res['data'];
               $total = $res['total'];
          }

          return $this->respond([
               'status' => true,
               'data' => $rows,
               'meta' => [
                    'total' => $total,
                    'limit' => (int)$limit,
                    'offset' => (int)$offset,
               ]
          ], 200);
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

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BranchModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;
use App\Services\JwtService;

class BranchController extends BaseController
{
     use ResponseTrait;

     /**
      * GET /branch/{id}
      * Return detailed branch information.
      */
     public function getBranchDetails($id)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;

          $id = is_numeric($id) ? (int) $id : 0;
          if ($id <= 0) {
               return $this->respond(['status' => false, 'message' => 'Invalid branch id'], 400);
          }

          $bm = new BranchModel();
          $data = $bm->getBranchDetails($id);
          if ($data) {
               return $this->respond(['status' => true, 'data' => $data], 200);
          }

          return $this->respond(['status' => false, 'message' => 'Branch Detail not found'], 404);
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

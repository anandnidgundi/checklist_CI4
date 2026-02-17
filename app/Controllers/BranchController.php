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
          // debug: record arrival (will show in writable/logs)
          $method = $this->request->getMethod();
          $uri = (string) $this->request->getURI();
          log_message('error', "BranchController::getBranchDetails - incoming request {$method} {$uri} id={$id} | Authorization present=" . (
               $this->request->header('Authorization') ? 'yes' : 'no'
          ));

          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) {
               log_message('error', 'BranchController::getBranchDetails - authorization failed or short-circuited');
               return $auth;
          }

          $id = is_numeric($id) ? (int) $id : 0;
          if ($id <= 0) {
               log_message('error', 'BranchController::getBranchDetails - invalid id');
               return $this->respond(['status' => false, 'message' => 'Invalid branch id'], 400);
          }

          $bm = new BranchModel();
          $data = $bm->getBranchDetails($id);

          if ($data) {
               // mark response so client can detect controller hit even if body gets transformed
               $this->response->setHeader('X-Branch-Found', '1');
               log_message('error', 'BranchController::getBranchDetails - returning data for id=' . $id);
               return $this->respond(['status' => true, 'data' => $data], 200);
          }

          $this->response->setHeader('X-Branch-Found', '0');
          log_message('error', 'BranchController::getBranchDetails - branch not found id=' . $id);
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

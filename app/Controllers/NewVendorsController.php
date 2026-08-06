<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\NewVendorsModel;
use App\Models\NewChecklistFormsModel;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

use App\Services\JwtService;

class NewVendorsController extends BaseController
{
     use ResponseTrait;
     protected $newVendorsModel;
     protected $newChecklistFormsModel;

     public function __construct()
     {
          $this->newVendorsModel = new NewVendorsModel();
          $this->newChecklistFormsModel = new NewChecklistFormsModel();
     }

     public function newChecklistForms()
     {
          $forms = $this->newChecklistFormsModel->findAll();
          return $this->respond([
               'status' => true,
               'data' => $forms
          ]);
     }

     public function list($filter = null)
     {
          // if filter is provided, then filter the vendors based on the filter
          if ($filter) {
               $vendors = $this->newVendorsModel->like('vendor_name', $filter)->findAll();
          } else {
               $vendors = $this->newVendorsModel->findAll();
          }

          // if no vendors found, return error
          if (!$vendors) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          return $this->respond([
               'status' => true,
               'data' => $vendors
          ]);
     }



     public function show($id = null)
     {
          $data = $this->newVendorsModel->find($id);
          if (!$data) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          return $this->respond([
               'status' => true,
               'data' => $data
          ]);
     }

     // fetch vendors based on checklist form
     public function fetchVendorByForm($form, $branch_id)
     {
          $vendor = $this->newVendorsModel
               ->where('checklist_form', $form)
               ->where("JSON_CONTAINS(branches_mapped, '\"{$branch_id}\"')")
               ->orderBy('id', 'DESC')
               ->first();

          return $this->respond([
               'status' => true,
               'data' => $vendor
          ]);
     }

     public function create()
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;
          $user = $auth->emp_code;
          $data = $this->request->getJSON(true);
          $data['created_by'] = $user;

          if (isset($data['vendor_details']) && is_array($data['vendor_details'])) {
               $data['vendor_details'] = json_encode($data['vendor_details']);
          }

          if (isset($data['branches_mapped']) && is_array($data['branches_mapped'])) {
               $data['branches_mapped'] = json_encode($data['branches_mapped']);
          }

          $insertId = $this->newVendorsModel->insert($data);
          if (!$insertId) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to create vendor'
               ], 500);
          }

          return $this->respond([
               'status' => true,
               'data' => $this->newVendorsModel->find($insertId)
          ]);
     }

     public function update($id = null)
     {
          $auth = $this->validateAuthorization();
          if ($auth instanceof ResponseInterface) return $auth;
          $user = $auth->emp_code;

          $data = $this->request->getJSON(true);
          $data['updated_by'] = $user;
          if (isset($data['vendor_details']) && is_array($data['vendor_details'])) {
               $data['vendor_details'] = json_encode($data['vendor_details']);
          }

          if (isset($data['branches_mapped']) && is_array($data['branches_mapped'])) {
               $data['branches_mapped'] = json_encode($data['branches_mapped']);
          }

          $updateId = $this->newVendorsModel->update($id, $data);
          if (!$updateId) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to update vendor'
               ], 500);
          }

          return $this->respond([
               'status' => true,
               'data' => $this->newVendorsModel->find($id)
          ]);
     }

     public function delete($id = null)
     {
          $this->newVendorsModel->delete($id);
          return $this->respondDeleted(['id' => $id]);
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

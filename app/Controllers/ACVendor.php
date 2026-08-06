<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ACVendorModel;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class ACVendor extends BaseController
{
     use ResponseTrait;
     protected $acVendorModel;

     public function __construct()
     {
          $this->acVendorModel = new ACVendorModel();
     }

     public function list()
     {
          $vendors = $this->acVendorModel
               ->orderBy('id', 'DESC')
               ->findAll();

          return $this->response->setJSON([
               'status' => true,
               'data'   => $vendors
          ]);
     }

     public function show($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          if (empty($id)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor ID is required'
               ], 400);
          }

          $vendor = $this->acVendorModel->find($id);

          if (!$vendor) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          return $this->respond([
               'status' => true,
               'data' => $vendor
          ]);
     }

     public function update($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          $empCode = $this->resolveAuthEmpCode($user);
          if ($empCode === '') {
               return $this->respond([
                    'status' => false,
                    'message' => 'Invalid token payload'
               ], 401);
          }

          if (empty($id)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor ID is required'
               ], 400);
          }

          $vendor = $this->acVendorModel->find($id);

          if (!$vendor) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          $input = $this->request->getJSON(true);

          $vendor_name = trim($input['vendor_name'] ?? '');

          if (empty($vendor_name)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor name is required'
               ], 400);
          }

          $data = [
               'vendor_name' => $vendor_name
          ];

          if ($this->acVendorModel->update($id, $data)) {
               return $this->respond([
                    'status' => true,
                    'message' => 'Vendor updated successfully'
               ]);
          }

          return $this->respond([
               'status' => false,
               'message' => 'Failed to update vendor'
          ], 500);
     }

     public function create()
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) return $user;

          $empCode = $this->resolveAuthEmpCode($user);
          if ($empCode === '') {
               return $this->respond(['message' => 'Invalid token payload (emp_code missing)'], 401);
          }
          $input = $this->request->getJSON(true);

          $vendor_name = trim($input['vendor_name'] ?? '');

          if (empty($vendor_name)) {
               return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Vendor name is required'
               ]);
          }

          $data = [
               'vendor_name' => $vendor_name,
               'created_by'  => $empCode ?? 'SYSTEM',
               'created_on'  => date('Y-m-d H:i:s')
          ];

          $insertId = $this->acVendorModel->insert($data);

          if ($insertId) {
               return $this->response->setJSON([
                    'status'  => true,
                    'message' => 'Vendor created successfully',
                    'id'      => $insertId
               ]);
          }

          return $this->response->setJSON([
               'status'  => false,
               'message' => 'Failed to create vendor'
          ]);
     }

     public function delete($id = null)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          if (empty($id)) {
               return $this->respond([
                    'status'  => false,
                    'message' => 'Vendor ID is required'
               ], 400);
          }

          $vendor = $this->acVendorModel->find($id);

          if (!$vendor) {
               return $this->respond([
                    'status'  => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          if ($this->acVendorModel->delete($id)) {
               return $this->respond([
                    'status'  => true,
                    'message' => 'Vendor deleted successfully'
               ]);
          }

          return $this->respond([
               'status'  => false,
               'message' => 'Failed to delete vendor'
          ], 500);
     }

     private function resolveAuthEmpCode($user): string
     {
          if (!is_object($user)) {
               return '';
          }

          $candidates = [
               $user->emp_code ?? null,
               $user->sub ?? null,
               $user->username ?? null,
          ];

          foreach ($candidates as $candidate) {
               $value = is_scalar($candidate) ? trim((string) $candidate) : '';
               if ($value !== '') {
                    return $value;
               }
          }

          return '';
     }
}

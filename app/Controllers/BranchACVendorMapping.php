<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BranchACVendorMappingModel;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class BranchACVendorMapping extends BaseController
{
     use ResponseTrait;
     protected $branchACVendorMappingModel;

     public function __construct()
     {
          $this->branchACVendorMappingModel = new BranchACVendorMappingModel();
     }

     public function list()
     {
          $data = $this->branchACVendorMappingModel
               ->select('
            branch_ac_vendor_map.*,
            ac_vendors.vendor_name
        ')
               ->join(
                    'ac_vendors',
                    'ac_vendors.id = branch_ac_vendor_map.vendor_id',
                    'left'
               )
               ->orderBy('branch_ac_vendor_map.id', 'DESC')
               ->findAll();

          return $this->response->setJSON([
               'status' => true,
               'data'   => $data
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

          $vendor = $this->branchACVendorMappingModel->find($id);

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

     public function mapping_details($vendor_id = null, $sys_no = null)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          $vendor_id =  (int) ($vendor_id ?? 0);
          $sys_no =  (int) ($sys_no ?? 0);

          if (empty($vendor_id) || empty($sys_no)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Vendor ID and Sys No are required'
               ], 400);
          }

          // $mapping = $this->branchACVendorMappingModel
          //      ->where('vendor_id', $vendor_id)
          //      ->where('sys_no', $sys_no)
          //      ->first();

          $mapping = $this->branchACVendorMappingModel
               ->select('branch_ac_vendor_map.*')
               ->join(
                    'ac_vendors',
                    'ac_vendors.id = branch_ac_vendor_map.vendor_id',
                    'left'
               )
               ->where('branch_ac_vendor_map.vendor_id', $vendor_id)
               ->where('branch_ac_vendor_map.sys_no', $sys_no)
               ->first();

          if (!$mapping) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Record not found'
               ], 404);
          }

          return $this->respond([
               'status' => true,
               'data' => $mapping
          ]);
     }

     public function getMappedDetailsByBranchId($sys_no = null)
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          $sys_no =  (int) ($sys_no ?? 0);

          if (empty($sys_no)) {
               return $this->respond([
                    'status' => false,
                    'message' => ' Sys No are required'
               ], 400);
          }

          $mapping = $this->branchACVendorMappingModel
               ->select('branch_ac_vendor_map.*, ac_vendors.*')
               ->join(
                    'ac_vendors',
                    'ac_vendors.id = branch_ac_vendor_map.vendor_id',
                    'left'
               )
               ->where('branch_ac_vendor_map.sys_no', $sys_no)
               ->first();

          if (!$mapping) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Record not found'
               ], 404);
          }

          return $this->respond([
               'status' => true,
               'data' => $mapping
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

          $vendor = $this->branchACVendorMappingModel->find($id);

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

          if ($this->branchACVendorMappingModel->update($id, $data)) {
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

          $vendor_id   = (int) ($input['vendor_id'] ?? 0);
          $branch_id   = (int) ($input['branch_id'] ?? 0);

          $sys_no      = (int) ($input['sys_no'] ?? '');
          $branch_name = trim($input['branch_name'] ?? '');

          $dry_service = (int) ($input['dry_service'] ?? 0);
          $wet_service = (int) ($input['wet_service'] ?? 0);

          if (empty($vendor_id)) {
               return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Vendor name is required'
               ]);
          }

          $data = [
               'vendor_id' => $vendor_id,
               'branch_id' => $branch_id,
               'sys_no'    => $sys_no,
               'branch_name' => $branch_name,
               'dry_service_yearly_count' => $dry_service,
               'wet_service_yearly_count' => $wet_service,
               'created_by'  => $empCode ?? 'SYSTEM',
               'created_on'  => date('Y-m-d H:i:s')
          ];



          $insertId = $this->branchACVendorMappingModel->insert($data);

          if (!$insertId) {
               return $this->response->setJSON([
                    'status' => false,
                    'errors' => $this->branchACVendorMappingModel->errors()
               ]);
          }

          return $this->response->setJSON([
               'status' => true,
               'message' => 'Record created successfully',
               'id' => $insertId
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

          $vendor = $this->branchACVendorMappingModel->find($id);

          if (!$vendor) {
               return $this->respond([
                    'status'  => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          if ($this->branchACVendorMappingModel->delete($id)) {
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

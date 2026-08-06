<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ACServiceModel;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\HTTP\ResponseInterface;

class ACServiceController extends BaseController
{
     use ResponseTrait;
     protected $acServiceModel;

     public function __construct()
     {
          $this->acServiceModel = new ACServiceModel();
     }

     public function list()
     {
          $vendors = $this->acServiceModel
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

          $vendor = $this->acServiceModel->find($id);

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

          if (!$id) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Service ID is required'
               ], 400);
          }

          $service = $this->acServiceModel->find($id);

          if (!$service) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Service record not found'
               ], 404);
          }

          /*
    |--------------------------------------------------------------------------
    | FormData Payload
    | data => JSON String
    | cleaning_attachment => File
    | service_report_attachment => File
    |--------------------------------------------------------------------------
    */

          $jsonData = $this->request->getPost('data');

          if (empty($jsonData)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Data field is required'
               ], 400);
          }

          $input = json_decode($jsonData, true);

          if (json_last_error() !== JSON_ERROR_NONE) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Invalid JSON payload',
                    'error' => json_last_error_msg()
               ], 400);
          }

          $data = [
               'vendor_id'                 => $input['vendor_id'] ?? '',
               'vendor_name'               => $input['vendor_name'] ?? '',
               'branch_id'                 => $input['branch_id'] ?? '',
               'branch_name'               => $input['branch_name'] ?? '',
               'sys_no'                    => $input['sys_no'] ?? '',
               'service_type'              => $input['service_type'] ?? '',

               'dry_service_yearly_count'  => $input['dry_service_yearly_count'] ?? '',
               'wet_service_yearly_count'  => $input['wet_service_yearly_count'] ?? '',

               'dry_service_completed'     => $input['dry_service_completed'] ?? '',
               'wet_service_completed'     => $input['wet_service_completed'] ?? '',

               'dry_service_cleaning_date' => !empty($input['dry_service_cleaning_date'])
                    ? $input['dry_service_cleaning_date']
                    : null,

               'next_dry_service_date'     => !empty($input['next_dry_service_date'])
                    ? $input['next_dry_service_date']
                    : null,

               'wet_service_cleaning_date' => !empty($input['wet_service_cleaning_date'])
                    ? $input['wet_service_cleaning_date']
                    : null,

               'next_wet_service_date'     => !empty($input['next_wet_service_date'])
                    ? $input['next_wet_service_date']
                    : null,

               'manager_present'           => $input['manager_present'] ?? '',
               'remarks'                   => $input['remarks'] ?? '',

               'updated_by'                => $empCode,
               'updated_on'                => date('Y-m-d H:i:s')
          ];

          /*
    |--------------------------------------------------------------------------
    | Upload Folder
    |--------------------------------------------------------------------------
    */

          $uploadPath = FCPATH . 'uploads/ac_services/';

          if (!is_dir($uploadPath)) {
               mkdir($uploadPath, 0777, true);
          }

          /*
|--------------------------------------------------------------------------
| Cleaning Attachment
|--------------------------------------------------------------------------
*/

          $cleaningAttachment = $this->request->getFile('cleaning_attachment');

          if (
               $cleaningAttachment &&
               $cleaningAttachment->isValid() &&
               !$cleaningAttachment->hasMoved()
          ) {

               $cleaningAttachmentName = $cleaningAttachment->getRandomName();

               $cleaningAttachment->move(
                    WRITEPATH . 'uploads/ac_services/',
                    $cleaningAttachmentName
               );

               // Delete old file if exists
               if (
                    !empty($service['cleaning_attachment']) &&
                    file_exists(
                         WRITEPATH .
                              'uploads/ac_services/' .
                              $service['cleaning_attachment']
                    )
               ) {
                    unlink(
                         WRITEPATH .
                              'uploads/ac_services/' .
                              $service['cleaning_attachment']
                    );
               }

               $data['cleaning_attachment'] = $cleaningAttachmentName;
          }

          /*
|--------------------------------------------------------------------------
| Service Report Attachment
|--------------------------------------------------------------------------
*/

          $serviceReportAttachment = $this->request->getFile('service_report_attachment');

          if (
               $serviceReportAttachment &&
               $serviceReportAttachment->isValid() &&
               !$serviceReportAttachment->hasMoved()
          ) {

               $serviceReportAttachmentName =
                    $serviceReportAttachment->getRandomName();

               $serviceReportAttachment->move(
                    WRITEPATH . 'uploads/ac_services/',
                    $serviceReportAttachmentName
               );

               // Delete old file if exists
               if (
                    !empty($service['service_report_attachment']) &&
                    file_exists(
                         WRITEPATH .
                              'uploads/ac_services/' .
                              $service['service_report_attachment']
                    )
               ) {
                    unlink(
                         WRITEPATH .
                              'uploads/ac_services/' .
                              $service['service_report_attachment']
                    );
               }

               $data['service_report_attachment'] =
                    $serviceReportAttachmentName;
          }

          /*
    |--------------------------------------------------------------------------
    | Update Record
    |--------------------------------------------------------------------------
    */

          if ($this->acServiceModel->update($id, $data)) {

               return $this->respond([
                    'status'  => true,
                    'message' => 'AC Service updated successfully',
                    'data'    => $data
               ]);
          }

          return $this->respond([
               'status' => false,
               'message' => 'Failed to update AC Service'
          ], 500);
     }

     public function create()
     {
          $user = $this->validateAuthorizationNew();
          if ($user instanceof \CodeIgniter\HTTP\ResponseInterface) {
               return $user;
          }

          $empCode = $this->resolveAuthEmpCode($user);
          if ($empCode === '') {
               return $this->respond([
                    'message' => 'Invalid token payload (emp_code missing)'
               ], 401);
          }

          // Get JSON data from FormData
          $jsonData = $this->request->getPost('data');
          $input = json_decode($jsonData, true);

          if (!$input) {
               return $this->response->setJSON([
                    'status' => false,
                    'message' => 'Invalid request data'
               ]);
          }

          // Files
          $cleaningAttachment = $this->request->getFile('cleaning_attachment');
          $serviceReportAttachment = $this->request->getFile('service_report_attachment');

          $cleaningAttachmentName = null;
          $serviceReportAttachmentName = null;

          // Upload Cleaning Attachment
          if ($cleaningAttachment && $cleaningAttachment->isValid()) {
               $cleaningAttachmentName = $cleaningAttachment->getRandomName();
               $cleaningAttachment->move(
                    WRITEPATH . 'uploads/ac_services/',
                    $cleaningAttachmentName
               );
          }

          // Upload Service Report
          if ($serviceReportAttachment && $serviceReportAttachment->isValid()) {
               $serviceReportAttachmentName = $serviceReportAttachment->getRandomName();
               $serviceReportAttachment->move(
                    WRITEPATH . 'uploads/ac_services/',
                    $serviceReportAttachmentName
               );
          }

          $data = [
               'vendor_id'                 => $input['vendor_id'] ?? null,
               'vendor_name'               => $input['vendor_name'] ?? '',
               'branch_id'                 => $input['branch_id'] ?? null,
               'branch_name'               => $input['branch_name'] ?? '',
               'sys_no'                    => $input['sys_no'] ?? '',
               'service_type'              => $input['service_type'] ?? '',
               'dry_service_yearly_count'  => $input['dry_service_count'] ?? '',
               'wet_service_yearly_count'  => $input['wet_service_count'] ?? '',

               'dry_service_completed'     => $input['dry_service_completed'] ?? null,
               'wet_service_completed'     => $input['wet_service_completed'] ?? null,
               'dry_service_cleaning_date' => $input['dry_service_cleaning_date'] ?? null,
               'next_dry_service_date'     => $input['next_dry_service_date'] ?? null,
               'wet_service_cleaning_date' => $input['wet_service_cleaning_date'] ?? null,
               'next_wet_service_date'     => $input['next_wet_service_date'] ?? null,
               'manager_present'           => $input['manager_present'] ?? '',
               'remarks'                   => $input['remarks'] ?? '',

               'cleaning_attachment'       => $cleaningAttachmentName,
               'service_report_attachment' => $serviceReportAttachmentName,

               'created_by'                => $empCode,
               'created_on'                => date('Y-m-d H:i:s')
          ];

          $insertId = $this->acServiceModel->insert($data);

          if ($insertId) {
               return $this->response->setJSON([
                    'status'  => true,
                    'message' => 'AC Service created successfully',
                    'id'      => $insertId
               ]);
          }

          return $this->response->setJSON([
               'status'  => false,
               'message' => 'Failed to create AC Service'
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

          $vendor = $this->acServiceModel->find($id);

          if (!$vendor) {
               return $this->respond([
                    'status'  => false,
                    'message' => 'Vendor not found'
               ], 404);
          }

          if ($this->acServiceModel->delete($id)) {
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

     public function download($filename)
     {
          $path = WRITEPATH . 'uploads/ac_services/' . $filename;

          if (!file_exists($path)) {
               return $this->response->setStatusCode(404)->setBody('File not found');
          }

          return $this->response->download($path, null);
     }

     public function file($fileName)
     {
          $path = WRITEPATH . 'uploads/ac_services/' . $fileName;

          if (!file_exists($path)) {
               return $this->response->setStatusCode(404);
          }

          $mime = mime_content_type($path);

          return $this->response
               ->setHeader('Content-Type', $mime)
               ->setHeader('Content-Disposition', 'inline; filename="' . $fileName . '"')
               ->setBody(file_get_contents($path));
     }
}

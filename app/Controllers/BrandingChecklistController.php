<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\BrandingChecklistModel;

class BrandingChecklistController extends BaseController
{
     use ResponseTrait;

     protected $model;

     public function __construct()
     {
          $this->model = new BrandingChecklistModel();
     }

     // DataTable / listing (server-side)
     public function list()
     {
          $this->validateAuthorizationNew();
          $request = $this->request;
          $start = (int)$request->getGet('start') ?: 0;
          $length = (int)$request->getGet('length') ?: 25;
          $search = $request->getGet('search')['value'] ?? '';

          $orderCol = $request->getGet('order')[0]['column'] ?? null;
          $orderDir = $request->getGet('order')[0]['dir'] ?? 'desc';
          $columns = $request->getGet('columns') ?? [];

          $result = $this->model->datatableList($start, $length, $search, $orderCol, $orderDir, $columns);

          return $this->respond([
               'draw' => (int)$request->getGet('draw'),
               'recordsTotal' => $result['total'],
               'recordsFiltered' => $result['filtered'],
               'data' => $result['rows']
          ], 200);
     }

     // create new checklist (payload includes items, actions, optional photos as meta)
     public function create()
     {
          $user = $this->validateAuthorizationNew();
          // Only SUPER_ADMIN and BRANDING_AUDITOR are allowed to create new branding checklists
          $role = $user->role ?? ($user->role ?? '');
          if (!in_array($role, ['SUPER_ADMIN', 'BRANDING_AUDITOR'])) {
               return $this->respond(['message' => 'Forbidden'], 403);
          }

          $input = $this->request->getJSON(true) ?: [];

          // Basic server-side validation and sanitization
          $input['centre_name'] = isset($input['centre_name']) ? trim(strip_tags((string)$input['centre_name'])) : null;
          $input['date_of_visit'] = isset($input['date_of_visit']) ? trim((string)$input['date_of_visit']) : null;
          if (empty($input['date_of_visit'])) return $this->respond(['message' => 'date_of_visit is required'], 400);

          // visit_time required (DB column is NOT NULL)
          $input['visit_time'] = isset($input['visit_time']) ? trim((string)$input['visit_time']) : null;
          if (empty($input['visit_time'])) return $this->respond(['message' => 'visit_time is required'], 400);

          // audited_by must be the authenticated user's emp_code (do not trust client)
          $input['audited_by'] = $user->emp_code ?? $user->username ?? null;
          try {
               $id = $this->model->createChecklist($input, $user);
               return $this->respondCreated(['message' => 'Created', 'id' => $id]);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function show($id)
     {
          $this->validateAuthorizationNew();
          $res = $this->model->getChecklistWithRelations((int)$id);
          if (!$res) return $this->respond(['message' => 'Not found'], 404);
          return $this->respond(['data' => $res], 200);
     }

     public function update($id)
     {
          $user = $this->validateAuthorizationNew();
          $role = $user->role ?? ($user->role ?? '');
          if (!in_array($role, ['SUPER_ADMIN', 'ADMIN', 'AUDIT', 'HK_SUPERVISOR', 'BRANDING_AUDITOR'])) {
               return $this->respond(['message' => 'Forbidden'], 403);
          }
          // If BRANDING_AUDITOR, allow updates only on checklists they created
          if ($role === 'BRANDING_AUDITOR') {
               $chk = $this->model->find((int)$id);
               if (!$chk) return $this->respond(['message' => 'Not found'], 404);
               $creator = $chk['created_by'] ?? null;
               $empCode = $user->emp_code ?? $user->username ?? null;
               if ($creator !== $empCode) {
                    return $this->respond(['message' => 'Forbidden'], 403);
               }
          }

          $input = $this->request->getJSON(true) ?: [];
          // Basic validation/sanitization
          if (isset($input['date_of_visit'])) $input['date_of_visit'] = trim((string)$input['date_of_visit']);
          if (isset($input['centre_name'])) $input['centre_name'] = trim(strip_tags((string)$input['centre_name']));
          if (isset($input['visit_time'])) $input['visit_time'] = trim((string)$input['visit_time']);
          // ensure audited_by is always the current user's emp_code on update too
          $input['audited_by'] = $user->emp_code ?? $user->username ?? null;
          if (isset($input['visit_time'])) $input['visit_time'] = trim((string)$input['visit_time']);

          try {
               $ok = $this->model->updateChecklist((int)$id, $input);
               return $this->respond(['message' => 'Updated', 'updated' => (bool)$ok], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function delete($id)
     {
          $user = $this->validateAuthorizationNew();
          $role = $user->role ?? ($user->role ?? '');
          // If BRANDING_AUDITOR, allow delete only on checklists they created
          if ($role === 'BRANDING_AUDITOR') {
               $chk = $this->model->find((int)$id);
               if (!$chk) return $this->respond(['message' => 'Not found'], 404);
               $creator = $chk['created_by'] ?? null;
               $empCode = $user->emp_code ?? $user->username ?? null;
               if ($creator !== $empCode) {
                    return $this->respond(['message' => 'Forbidden'], 403);
               }
          }
          try {
               $ok = $this->model->deleteChecklist((int)$id);
               return $this->respondDeleted(['message' => 'Deleted', 'deleted' => (bool)$ok]);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // Upload photo(s) for a given checklist id (multipart/form-data file inputs)
     // public function uploadPhoto($id)
     // {
     //      $user = $this->validateAuthorizationNew();
     //      // Only allow certain roles to upload photos (admins, branding auditors). Allow BRANDING_AUDITOR for checklists they created.
     //      $role = $user->role ?? ($user->role ?? '');
     //      if (!in_array($role, ['SUPER_ADMIN', 'ADMIN',   'BRANDING_AUDITOR'])) {
     //           return $this->respond(['message' => 'Forbidden'], 403);
     //      }

     //      // Ensure checklist exists
     //      $chk = $this->model->find((int)$id);
     //      if (!$chk) return $this->respond(['message' => 'Checklist not found'], 404);

     //      // If user is BRANDING_AUDITOR, allow upload only for checklists they created
     //      if ($role === 'BRANDING_AUDITOR') {
     //           $creator = $chk['created_by'] ?? null;
     //           $empCode = $user->emp_code ?? $user->username ?? null;
     //           if ($creator !== $empCode) {
     //                return $this->respond(['message' => 'Forbidden'], 403);
     //           }
     //      }

     //      // handle uploaded files under input name 'photos[]' or single 'photo'
     //      $files = $this->request->getFiles();
     //      if (empty($files)) return $this->respond(['message' => 'No files uploaded'], 400);

     //      $saved = [];
     //      $uploadPath = WRITEPATH . 'uploads/branding_photos/';
     //      if (!is_dir($uploadPath)) {
     //           mkdir($uploadPath, 0755, true);
     //      }

     //      // Post metadata if provided, expected as photos_meta[] JSON entries in the same order as photos[]
     //      $metaList = $this->request->getPost('photos_meta') ?? [];
     //      $metaIndex = 0;

     //      foreach ($files as $key => $file) {
     //           if (is_array($file)) {
     //                foreach ($file as $f) {
     //                     if ($f->isValid() && !$f->hasMoved()) {
     //                          $newName = $f->getRandomName();
     //                          $f->move($uploadPath, $newName);

     //                          $meta = null;
     //                          if (isset($metaList[$metaIndex])) {
     //                               $m = json_decode($metaList[$metaIndex], true);
     //                               if (json_last_error() === JSON_ERROR_NONE) $meta = $m;
     //                          }

     //                          $this->model->addPhoto(
     //                               (int)$id,
     //                               $newName,
     //                               $this->request->getPost('caption') ?? null,
     //                               isset($meta['latitude']) ? (float)$meta['latitude'] : null,
     //                               isset($meta['longitude']) ? (float)$meta['longitude'] : null,
     //                               isset($meta['accuracy']) ? (int)$meta['accuracy'] : null,
     //                               isset($meta['timestamp']) ? (string)$meta['timestamp'] : null
     //                          );
     //                          $saved[] = $newName;
     //                          $metaIndex++;
     //                     }
     //                }
     //           } else {
     //                if ($file->isValid() && !$file->hasMoved()) {
     //                     $newName = $file->getRandomName();
     //                     $file->move($uploadPath, $newName);

     //                     $meta = null;
     //                     if (isset($metaList[$metaIndex])) {
     //                          $m = json_decode($metaList[$metaIndex], true);
     //                          if (json_last_error() === JSON_ERROR_NONE) $meta = $m;
     //                     }

     //                     $this->model->addPhoto(
     //                          (int)$id,
     //                          $newName,
     //                          $this->request->getPost('caption') ?? null,
     //                          isset($meta['latitude']) ? (float)$meta['latitude'] : null,
     //                          isset($meta['longitude']) ? (float)$meta['longitude'] : null,
     //                          isset($meta['accuracy']) ? (int)$meta['accuracy'] : null,
     //                          isset($meta['timestamp']) ? (string)$meta['timestamp'] : null
     //                     );
     //                     $saved[] = $newName;
     //                     $metaIndex++;
     //                }
     //           }
     //      }

     //      return $this->respondCreated(['message' => 'Uploaded', 'files' => $saved, 'path' => $uploadPath]);
     // }


     public function uploadPhoto($checklistId)
     {
          // Auth + permission checks (same rules as other endpoints)
          $user = $this->validateAuthorizationNew();
          $role = $user->role ?? ($user->role ?? '');
          if (!in_array($role, ['SUPER_ADMIN', 'ADMIN', 'BRANDING_AUDITOR'])) {
               return $this->respond(['message' => 'Forbidden'], 403);
          }

          // Ensure checklist exists and enforce BRANDING_AUDITOR ownership
          $chk = $this->model->find((int)$checklistId);
          if (!$chk) return $this->respond(['message' => 'Checklist not found'], 404);
          if ($role === 'BRANDING_AUDITOR') {
               $creator = $chk['created_by'] ?? null;
               $empCode = $user->emp_code ?? $user->username ?? null;
               if ($creator !== $empCode) return $this->respond(['message' => 'Forbidden'], 403);
          }

          $files = array_values($this->request->getFileMultiple('photos') ?? []);
          $metas = $this->request->getPost('photos_meta') ?? [];
          if (empty($files)) return $this->respond(['message' => 'No files uploaded'], 400);

          $uploadPath = WRITEPATH . 'uploads/branding_photos';
          if (!is_dir($uploadPath)) mkdir($uploadPath, 0755, true);

          $saved = [];
          $errors = [];
          $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg', 'application/pdf'];
          $maxBytes = 2 * 1024 * 1024; // 2 MB

          foreach ($files as $i => $file) {
               if (!$file || !$file->isValid() || $file->hasMoved()) {
                    $errors[] = "File #{$i} invalid or already moved";
                    continue;
               }

               // basic server-side validation
               if (!in_array($file->getClientMimeType(), $allowedTypes)) {
                    $errors[] = "File #{$i} type not allowed";
                    continue;
               }
               if ($file->getSize() > $maxBytes) {
                    $errors[] = "File #{$i} exceeds size limit";
                    continue;
               }

               $newName = $file->getRandomName();
               try {
                    $file->move($uploadPath, $newName);
               } catch (\Throwable $e) {
                    $errors[] = "Failed to move file #{$i}";
                    continue;
               }

               // parse metadata safely
               $meta = [];
               $metaJson = $metas[$i] ?? '{}';
               $m = json_decode($metaJson, true);
               if (json_last_error() === JSON_ERROR_NONE && is_array($m)) $meta = $m;

               // normalize/validate geo & timestamp
               $lat = isset($meta['latitude']) ? (float)$meta['latitude'] : null;
               $lon = isset($meta['longitude']) ? (float)$meta['longitude'] : null;
               $accuracy = isset($meta['accuracy']) ? (int)$meta['accuracy'] : null;
               $geo_dtm = null;
               if (isset($meta['timestamp'])) {
                    $ts = strtotime($meta['timestamp']);
                    if ($ts !== false) $geo_dtm = date('Y-m-d H:i:s', $ts);
               }
               if (!is_null($lat) && ($lat < -90 || $lat > 90)) $lat = null;
               if (!is_null($lon) && ($lon < -180 || $lon > 180)) $lon = null;
               if (!is_null($accuracy) && ($accuracy < 0 || $accuracy > 100000)) $accuracy = null;

               $caption = $meta['caption'] ?? $this->request->getPost('caption') ?? null;
               $caption = $caption ? trim(strip_tags((string)$caption)) : null;

               try {
                    $this->model->addPhoto((int)$checklistId, $newName, $caption, $lat, $lon, $accuracy, $geo_dtm);
                    $saved[] = $newName;
               } catch (\Throwable $e) {
                    $errors[] = "DB save failed for file #{$i}";
               }
          }

          $status = empty($errors) ? 201 : 207; // partial success
          return $this->respondCreated(['saved' => $saved, 'errors' => $errors], $status);
     }

     // List photos for a checklist
     public function photos($id)
     {
          $this->validateAuthorizationNew();
          $photos = $this->model->getPhotos((int)$id);

          // Add a public URL so clients don't have to construct paths themselves
          foreach ($photos as &$p) {
               $file = ($p['filename'] ?? '');
               $p['url'] = base_url('viewAttachmentNew/' . $file . '?size=thumb');
               $p['full_url'] = base_url('viewAttachmentNew/' . $file);
          }

          return $this->respond(['data' => $photos], 200);
     }

     // Sections and subsections APIs
     public function sections()
     {
          $this->validateAuthorizationNew();
          $rows = $this->model->getSections();

          // Attach subsections for each section so clients can fetch a single payload
          foreach ($rows as &$r) {
               $r['sub_sections'] = $this->model->getSubSections((int)$r['section_id']);
          }

          return $this->respond(['data' => $rows], 200);
     }

     public function createSection()
     {
          $user = $this->validateAuthorizationNew();
          $role = $user->role ?? ($user->role ?? '');
          if (!in_array($role, ['SUPER_ADMIN', 'ADMIN'])) return $this->respond(['message' => 'Forbidden'], 403);
          $input = $this->request->getJSON(true) ?: [];
          $name = isset($input['section_name']) ? trim(strip_tags((string)$input['section_name'])) : '';
          if ($name === '') return $this->respond(['message' => 'section_name required'], 400);
          try {
               $this->model->createSection($name);
               return $this->respondCreated(['message' => 'Created']);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // GET branch manager and cluster manager by branch id
     public function branchManager($branchId)
     {
          $this->validateAuthorizationNew();

          $bm = $this->model->getBranchManagerByBranchId((int)$branchId);
          $cm = $this->model->getClusterManagerByBranchId((int)$branchId);

          if (!$bm && !$cm) return $this->respond(['message' => 'Not found'], 404);

          return $this->respond(['data' => ['branch_manager' => $bm, 'cluster_manager' => $cm]], 200);
     }

     // GET employee details by emp_code
     public function employee($empCode)
     {
          $this->validateAuthorizationNew();
          $res = $this->model->getEmployeeByEmpCode($empCode);
          if (!$res) return $this->respond(['message' => 'Not found'], 404);
          return $this->respond(['data' => $res], 200);
     }

     public function subSections()
     {
          $this->validateAuthorizationNew();
          $sectionId = $this->request->getGet('section_id') ?: null;
          $rows = $this->model->getSubSections($sectionId);
          return $this->respond(['data' => $rows], 200);
     }

     public function createSubSection()
     {
          $user = $this->validateAuthorizationNew();
          $role = $user->role ?? ($user->role ?? '');
          if (!in_array($role, ['SUPER_ADMIN', 'ADMIN'])) return $this->respond(['message' => 'Forbidden'], 403);
          $input = $this->request->getJSON(true) ?: [];
          $sectionId = isset($input['section_id']) ? (int)$input['section_id'] : 0;
          $name = isset($input['sub_section_name']) ? trim(strip_tags((string)$input['sub_section_name'])) : '';
          if ($name === '' || $sectionId <= 0) return $this->respond(['message' => 'section_id and sub_section_name required'], 400);
          try {
               $this->model->createSubSection($sectionId, $name);
               return $this->respondCreated(['message' => 'Created']);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // Additional endpoints: mark action done, etc.
}

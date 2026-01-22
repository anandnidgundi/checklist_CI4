<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkSupervisorsModel;

class HkSupervisors extends BaseController
{
     use ResponseTrait;

     public function index()
     {
          $this->validateAuthorization();
          $m = new HkSupervisorsModel();

          $role = $this->request->getGet('role');
          $emp_code = $this->request->getGet('emp_code');

          $builder = $m->builder();
          if ($role) $builder->where('role', $role);
          if ($emp_code) $builder->where('emp_code', $emp_code);

          $rows = $builder->get()->getResultArray();

          // attach employee names by looking up emp_code in the secondary DB (new_emp_master.comp_name)
          if (!empty($rows)) {
               $codes = array_values(array_filter(array_unique(array_map(function ($r) {
                    return $r['emp_code'] ?? null;
               }, $rows))));
               if (!empty($codes)) {
                    try {
                         $db2 = \Config\Database::connect('secondary');
                         $emps = $db2->table('new_emp_master')->select('emp_code, comp_name')->whereIn('emp_code', $codes)->get()->getResultArray();
                         $empMap = [];
                         foreach ($emps as $e) $empMap[(string)$e['emp_code']] = $e['comp_name'];
                         foreach ($rows as &$r) {
                              $r['emp_name'] = $empMap[(string)($r['emp_code'] ?? '')] ?? null;
                         }
                         unset($r);
                    } catch (\Exception $e) {
                         // If lookup fails, continue without names (avoid breaking the endpoint)
                         // Optionally, you could add a warning flag to the response in future
                    }
               }
          }

          return $this->respond(['data' => $rows], 200);
     }

     public function show($id = null)
     {
          $this->validateAuthorization();
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);
          $m = new HkSupervisorsModel();
          $rec = $m->find($id);
          if (!$rec) return $this->respond(['message' => 'Not found'], 404);
          // return branches as array too for convenience
          $rec['branches_array'] = $rec['branches'] !== null && $rec['branches'] !== '' ? explode(',', $rec['branches']) : [];

          // Attach employee name if available by looking up emp_code in the secondary DB (new_emp_master.comp_name)
          if (!empty($rec['emp_code'])) {
               try {
                    $db2 = \Config\Database::connect('secondary');
                    $emp = $db2->table('new_emp_master')->select('emp_code, comp_name')->where('emp_code', $rec['emp_code'])->get()->getRowArray();
                    if ($emp) $rec['emp_name'] = $emp['comp_name'];
               } catch (\Exception $e) {
                    // ignore lookup failures to avoid breaking the endpoint
               }
          }

          return $this->respond($rec, 200);
     }

     public function create()
     {
          $user = $this->validateAuthorization();
          $data = $this->request->getJSON(true);

          if (empty($data['emp_code']) || empty($data['role'])) {
               return $this->respond(['message' => 'emp_code and role are required'], 400);
          }

          $m = new HkSupervisorsModel();
          $branches = $m->normalizeBranchesField($data['branches'] ?? '');

          $payload = [
               'emp_code' => $data['emp_code'],
               'role' => $data['role'],
               'branches' => $branches,
               'zone' => $data['zone'] ?? null,
               'created_on' => date('Y-m-d H:i:s'),
               'created_by' => $user->emp_code ?? null,
          ];
          // do not store cluster from frontend — cluster is removed from UI and should not be written here.

          try {
               $id = $m->insert($payload);
               return $this->respond(['message' => 'Created', 'id' => $id], 201);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function update($id = null)
     {
          $user = $this->validateAuthorization();
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);

          $data = $this->request->getJSON(true);
          if (empty($data)) return $this->respond(['message' => 'Missing payload'], 400);

          $m = new HkSupervisorsModel();
          $rec = $m->find($id);
          if (!$rec) return $this->respond(['message' => 'Not found'], 404);

          $update = [];
          if (isset($data['emp_code'])) $update['emp_code'] = $data['emp_code'];
          if (isset($data['role'])) $update['role'] = $data['role'];
          if (isset($data['zone'])) $update['zone'] = $data['zone'];
          // cluster is intentionally not accepted from frontend anymore
          if (isset($data['branches'])) $update['branches'] = $m->normalizeBranchesField($data['branches']);

          try {
               $m->update($id, $update);
               return $this->respond(['message' => 'Updated'], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function delete($id = null)
     {
          $this->validateAuthorization();
          if (!$id) return $this->respond(['message' => 'Missing id'], 400);

          $m = new HkSupervisorsModel();
          $rec = $m->find($id);
          if (!$rec) return $this->respond(['message' => 'Not found'], 404);

          try {
               $m->delete($id);
               return $this->respond(['message' => 'Deleted'], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // get supervisors branches by emp_code
     public function getHkBranchesByEmpCode($emp_code = null)
     {
          $this->validateAuthorization();
          if (!$emp_code) return $this->respond(['message' => 'Missing emp_code'], 400);

          $m = new HkSupervisorsModel();
          $records = $m->findByEmpCode($emp_code);
          if (empty($records)) {
               return $this->respond(['branches' => []], 200);
          }
          // use secondary database 
          $db2 = \Config\Database::connect('secondary');
          // use branches table from secondary database and get 

          // aggregate branches from all records
          $allBranches = [];
          foreach ($records as $rec) {
               if (!empty($rec['branches'])) {
                    $branchesArray = explode(',', $rec['branches']);
                    $allBranches = array_merge($allBranches, $branchesArray);
               }
          }
          // remove duplicates and empty values
          $allBranches = array_filter(array_unique($allBranches), function ($v) {
               return $v !== null && $v !== '';
          });

          $allBranches = array_values($allBranches);
          if (empty($allBranches)) {
               return $this->respond(['branches' => []], 200);
          }

          // make sure IDs are integers
          $ids = array_map('intval', $allBranches);

          try {
               // query branch details from the secondary DB
               $builder2 = $db2->table('branches')
                    ->select('id, SysField, SysNo, area, AreaId, Address, ContactNo, EmailId, status, vdc_new_status');

               $rows = $builder2->whereIn('id', $ids)->get()->getResultArray();

               return $this->respond(['branches' => $rows], 200);
          } catch (\Exception $e) {
               // if branch lookup fails, return IDs as fallback and provide a warning
               return $this->respond(['branches' => $ids, 'warning' => 'Failed to fetch branch details: ' . $e->getMessage()], 200);
          }
     }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class DeptModel extends Model
{

     protected $table = 'departments';
     // Primary table
     protected $primaryKey = 'id';
     protected $allowedFields = ['cat_id', 'dept_name', 'created_on', 'created_by', 'status'];

     public function getAssetDetails()
     {
          return $this->select('a.*, b.branch as bname, c.name as asst_name1, d.number as num, d.name as supplier_name1')
               ->from('asset a') // Alias for asset table
               ->join('bi_centres b', 'a.branch = b.id', 'inner') // Join bi_centres table
               ->join('asset_name c', 'a.asset_name = c.id', 'inner') // Join asset_name table
               ->join('service_manager d', 'a.supplier_name = d.id', 'inner') // Join service_manager table
               ->groupBy('a.id') // Order by descending asset id
               ->orderBy('a.id', 'desc') // Order by descending asset id
               ->findAll();
          // Retrieve all results 
     }
     public function getClusterDetailsById($cluster_id)
     {
          return $this->db->table('clusters c')
               ->select('c.cluster, c.cluster_id, z.zone, cz.zone_id')
               ->join('cluster_zone_map cz', 'cz.cluster_id = c.cluster_id', 'left')
               ->join('zones z', 'z.z_id = cz.zone_id', 'left')
               ->where('c.cluster_id', $cluster_id)
               ->get()
               ->getResultArray();
     }

     public function getBranchDetailsById($branch_id)
     {
          return $this->db->table('branches as b')
               ->select('b.branch_id, b.branch, c.cluster, cb.cluster_id, z.zone, cz.zone_id')
               ->join('cluster_branch_map cb', 'cb.branch_id = b.branch_id', 'left')
               ->join('cluster_zone_map cz', 'cz.cluster_id = cb.cluster_id', 'left')
               ->join('clusters c', 'c.cluster_id = cb.cluster_id', 'left')
               ->join('zones z', 'z.z_id = cz.zone_id', 'left')
               ->where('b.branch_id', $branch_id)
               ->get()
               ->getResultArray();
     }

     public function getZoneDetailsById($zone_id)
     {
          return $this->db->table('zones as z')
               ->select(' z.zone,  z.z_id as zone_id')
               ->where('z.z_id', $zone_id)
               ->where('z.status', 'A')
               ->get()
               ->getResultArray();
     }

     public function getAreaDetailsById($area_id)
     {
          return $this->select('a.*')
               ->from('area a') // Alias for asset table
               ->where('a.status', 'A')
               ->where('a.id', $area_id) // Group by asset id
               ->get()
               ->getResultArray();
     }

     public function getArea()
     {
          return $this->select('a.*')
               ->from('area a') // Alias for asset table
               ->where('a.status', 'A')
               ->groupBy('a.id') // Group by asset id
               ->findAll();
     }


     public function getTicketTotalCount($role, $user_id, $branch = null)
     {
          $this->select('COUNT(DISTINCT t.id) as totalCount')
               ->from('tickets as t')
               ->join('bi_centres As b', 'b.id = t.branch', 'left')
               ->join('departments AS d', 'd.id = t.department', 'left')
               ->join('managers', 'managers.id = b.manager', 'left');

          // $this->where( 't.status', 'Closed' );
          // Common condition

          // Role-specific conditions
          $roleConditions = [
               'MARKETING MANAGER' => ['managers.bmid' => $user_id, 'excludeCategories' => [2, 3]],
               'BRANDING' => ['includeCategories' => [2]],
               'BIO MEDICAL' => ['includeCategories' => [3]],
               'RMG' => ['includeCategories' => [5]],
               'Quality control' => ['includeCategories' => [6]],
               'LAB' => ['includeCategories' => [7]],
               'LAB-Others' => ['includeCategories' => [7], 'branch' => $branch],
          ];

          if ($role === 'SUPER ADMIN') {
               // No additional conditions for SUPER ADMIN
          } elseif (isset($roleConditions[$role])) {
               foreach ($roleConditions[$role] as $key => $value) {
                    if ($key === 'includeCategories') {
                         $this->whereIn('t.category', $value);
                    } elseif ($key === 'excludeCategories') {
                         $this->whereNotIn('t.category', $value);
                    } else {
                         $this->where($key, $value);
                    }
               }
          } elseif (in_array($role, ['USER', 'CLUSTER'])) {
               $this->where('t.createdby', $user_id);
          }

          $query = $this->get();
          return $query->getRow()->totalCount;
     }

     // Function to get ticket count based on role

     public function getTicketCompletedCount($role, $user_id, $branch = null)
     {
          $this->select('COUNT(DISTINCT t.id) as cnt')
               ->from('tickets as t')
               ->join('bi_centres as b', 'b.id = t.branch', 'left')
               ->join('departments AS d1', 'd1.id = t.department', 'left')
               ->join('managers', 'managers.id = b.manager', 'left')
               ->where('t.status', 'Closed');
          // Common condition

          // Role-specific conditions
          $roleConditions = [
               'MARKETING MANAGER' => ['managers.bmid' => $user_id, 'excludeCategories' => [2, 3]],
               'BRANDING' => ['includeCategories' => [2]],
               'BIO MEDICAL' => ['includeCategories' => [3]],
               'RMG' => ['includeCategories' => [5]],
               'Quality control' => ['includeCategories' => [6]],
               'LAB' => ['includeCategories' => [7]],
               'LAB-Others' => ['includeCategories' => [7], 'branch' => $branch],
          ];

          if ($role === 'SUPER ADMIN') {
               // No additional conditions for SUPER ADMIN
          } elseif (isset($roleConditions[$role])) {
               foreach ($roleConditions[$role] as $key => $value) {
                    if ($key === 'includeCategories') {
                         $this->whereIn('t.category', $value);
                    } elseif ($key === 'excludeCategories') {
                         $this->whereNotIn('t.category', $value);
                    } else {
                         $this->where($key, $value);
                    }
               }
          } elseif (in_array($role, ['USER', 'CLUSTER'])) {
               $this->where('t.createdby', $user_id);
          }

          $query = $this->get();
          return $query->getRow()->cnt;
     }

     public function getTicketPendingCount($role, $user_id, $branch = null)
     {
          $this->select('COUNT(DISTINCT t.id) as cnt')
               ->from('tickets as t')
               ->join('bi_centres as b', 'b.id = t.branch', 'left')
               ->join('departments AS d1', 'd1.id = t.department', 'left')
               ->join('managers', 'managers.id = b.manager', 'left');

          // Common condition for pending status
          $this->where('t.status !=', 'Closed')
               ->where('t.status !=', 'Deleted');

          // Role-specific conditions
          $roleConditions = [
               'MARKETING MANAGER' => ['excludeCategories' => [2, 3]],
               'BRANDING' => ['includeCategories' => [2]],
               'BIO MEDICAL' => ['includeCategories' => [3]],
               'RMG' => ['includeCategories' => [5]],
               'Quality control' => ['includeCategories' => [6]],
               'LAB' => ['includeCategories' => [7]],
               'LAB-Others' => ['includeCategories' => [7], 'branch' => $branch],
          ];

          if ($role === 'SUPER ADMIN') {
               // No additional conditions for SUPER ADMIN
          } elseif (isset($roleConditions[$role])) {
               foreach ($roleConditions[$role] as $key => $value) {
                    if ($key === 'includeCategories') {
                         $this->whereIn('t.category', $value);
                    } elseif ($key === 'excludeCategories') {
                         $this->whereNotIn('t.category', $value);
                    }
               }
          } elseif (in_array($role, ['USER', 'CLUSTER'])) {
               $this->where('t.createdby', $user_id);
          }

          $query = $this->get();
          return $query->getRow()->cnt;
     }

     public function getTicketRolewisePendingCount($roles, $user_id, $branch = null)
     {
          $results = [];
          foreach ($roles as $role) {
               $this->select('COUNT(DISTINCT t.id) as cnt')
                    ->from('tickets as t')
                    ->join('bi_centres as b', 'b.id = t.branch', 'left')
                    ->join('departments AS d1', 'd1.id = t.department', 'left')
                    ->join('managers', 'managers.id = b.manager', 'left');

               // Common condition for pending status
               $this->where('t.status !=', 'Closed')
                    ->where('t.status !=', 'Deleted');

               // Role-specific conditions
               $roleConditions = [
                    'MARKETING MANAGER' => ['excludeCategories' => [2, 3]],
                    'BRANDING' => ['includeCategories' => [2]],
                    'BIO_MEDICAL' => ['includeCategories' => [3]],
                    'RMG' => ['includeCategories' => [5]],
                    'Quality_Control' => ['includeCategories' => [6]],
                    'LAB' => ['includeCategories' => [7]],
                    'LAB-Others' => ['includeCategories' => [7], 'branch' => $branch],
               ];

               // Handle SUPER ADMIN separately
               if ($role === 'SUPER_ADMIN') {
                    $this->whereIn('t.category', [1]);
                    // Include category 1
               } elseif (isset($roleConditions[$role])) {
                    foreach ($roleConditions[$role] as $key => $value) {
                         if ($key === 'includeCategories') {
                              $this->whereIn('t.category', $value);
                         } elseif ($key === 'excludeCategories') {
                              $this->whereNotIn('t.category', $value);
                         }
                    }
               } elseif (in_array($role, ['USER', 'CLUSTER'])) {
                    $this->where('t.createdby', $user_id);
               }

               $query = $this->get();
               $count = $query->getRow()->cnt;

               // Store the count in the results array with the role as the key
               $results[$role] = $count;
          }

          return $results;
     }

     public function deleteCluster($id)
     {
          return $this->db->table('clusters')->where('cluster_id', $id)->delete();
     }

     public function deleteManager($id)
     {
          return $this->db->table('managers')->where('id', $id)->delete();
     }

     public function deleteTechnician($id)
     {
          return $this->db->table('staff')->where('id', $id)->delete();
     }

     public function deleteAssets($id)
     {
          return $this->db->table('asset_name')->where('id', $id)->delete();
     }

     public function deleteBranch($id)
     {
          return $this->db->table('branches')->where('branch_id', $id)->delete();
     }

     // Fetch cities with corresponding state names using a JOIN

     public function getDeptWithCat()
     {
          return $this->db->table('departments as d')
               ->select('d.dept_name, c.name as category_name, d.cat_id, d.id as id')
               ->join('category as c', 'c.id = d.cat_id')
               ->orderBy('dept_name', 'ASC')
               ->get()
               ->getResultArray();
     }

     public function getAssetsList()
     {
          return $this->db->table('asset a')
               ->select('a.*, b.branch as bname, c.name as asst_name1, d.number as num, d.name as supplier_name1')
               ->join('bi_centres b', 'a.branch = b.id', 'left')
               ->join('asset_name c', 'a.asset_name = c.id', 'left')
               ->join('service_manager d', 'a.supplier_name = d.id', 'left')
               ->orderBy('a.id', 'desc')
               ->get()
               ->getResultArray();
     }

     public function getTechnicians()
     {
          return $this->db->table('staff as a')
               ->select('a.name,a.id,a.bmid,a.roll,b.dept_name')
               ->join('departments as b', 'a.roll = b.id')
               ->where('a.status', 'A')
               ->orderBy('name', 'ASC')
               ->get()
               ->getResultArray();
     }

     // Fetch all states ( from another table )

     public function getAllCategory()
     {
          return $this->db->table('category')
               ->select('*')
               ->get()
               ->getResultArray();
     }

     public function addNewManager($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('managers')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into managers table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }

     public function addNewTechnician($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('staff')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into staff table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }

     public function addArea($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('area')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into area table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }

     public function addZone($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('zones')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into Zone table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }




     public function addNewBranch($data)
     {
          $this->db->transStart();

          $this->db->table('branches')->insert($data);

          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();

               log_message('error', 'Failed to insert data into branches table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();

          return true;
     }

     public function addDept($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('departments')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into departments table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }

     public function addCluster($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'cluster' table
          $this->db->table('clusters')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into Cluster table: ' . $this->db->error()['message']);
               return false;
          }

          // Get the insert ID
          $insertId = $this->db->insertID();

          $this->db->transComplete();
          // Commit the transaction
          return $insertId;
     }


     public function addClusterTo_bi_centres($data, $branch_id)
     {
          $this->db->transStart();

          // Check if emp_code already exists in the table
          $existingRecord = $this->db->table('bi_centres')
               ->where('id', $branch_id)
               ->get()
               ->getRowArray();

          if ($existingRecord) {
               // If emp_code exists, perform an update
               if (!$this->db->table('bi_centres')->where('id', $branch_id)->update($data)) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to update data in bi_centres table: ' . print_r($error, true));
                    $this->db->transRollback();
                    return false;
               }
          } else {
               // If emp_code does not exist, perform an insert
               if (!$this->db->table('bi_centres')->insert($data)) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to insert data into bi_centres table: ' . print_r($error, true));
                    $this->db->transRollback();
                    return false;
               }
          }

          $this->db->transComplete();
          return $this->db->transStatus(); // Returns true if transaction succeeds, false if not
     }

     public function addBranchToCluster($data, $branch_id)
     {
          $this->db->transStart();

          // Check if emp_code already exists in the table
          $existingRecord = $this->db->table('cluster_branch_map')
               ->where('branch_id', $branch_id)
               ->get()
               ->getRowArray();

          if ($existingRecord) {
               // If emp_code exists, perform an update
               if (!$this->db->table('cluster_branch_map')->where('branch_id', $branch_id)->update($data)) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to update data in cluster_branch_map table: ' . print_r($error, true));
                    $this->db->transRollback();
                    return false;
               }
          } else {
               // If emp_code does not exist, perform an insert
               if (!$this->db->table('cluster_branch_map')->insert($data)) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to insert data into cluster_branch_map table: ' . print_r($error, true));
                    $this->db->transRollback();
                    return false;
               }
          }

          $this->db->transComplete();
          return $this->db->transStatus(); // Returns true if transaction succeeds, false if not
     }


     public function addAreaToCluster($data, $cluster_id)
     {
          $this->db->transStart();

          // Check if emp_code already exists in the table
          $existingRecord = $this->db->table('clust_area_map')
               ->where('cluster_id', $cluster_id)
               ->get()
               ->getRowArray();

          if ($existingRecord) {
               // If emp_code exists, perform an update
               if (!$this->db->table('clust_area_map')->where('cluster_id', $cluster_id)->update($data)) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to update data in clust_area_map table: ' . print_r($error, true));
                    $this->db->transRollback();
                    return false;
               }
          } else {
               // If emp_code does not exist, perform an insert
               if (!$this->db->table('clust_area_map')->insert($data)) {
                    $error = $this->db->error();
                    log_message('error', 'Failed to insert data into clust_area_map table: ' . print_r($error, true));
                    $this->db->transRollback();
                    return false;
               }
          }

          $this->db->transComplete();
          return $this->db->transStatus(); // Returns true if transaction succeeds, false if not
     }

     public function addNewAssets($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('asset_name')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into Asset table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }

     public function deleteDept($id)
     {
          return $this->delete($id);
          // This uses the delete method from the Model
     }

     public function getAllCluster()
     {
          try {
               // Fetch clusters with zone information
               $clusters = $this->db->table('clusters as c')
                    ->select('c.*, z.zone, cz.zone_id')
                    ->join('cluster_zone_map as cz', 'cz.cluster_id = c.cluster_id', 'left')
                    ->join('zones as z', 'z.z_id = cz.zone_id', 'left')
                    ->get()
                    ->getResultArray();

               // For each cluster, fetch the associated branch list
               foreach ($clusters as &$c) {
                    $branchList = $this->db->table('cluster_branch_map as cb')
                         ->select('cb.branch_id, bm.branch')
                         ->join('branches as bm', 'cb.branch_id = bm.branch_id', 'left') // Removed 'c.cluster_id' reference
                         ->where('cb.cluster_id', $c['cluster_id'])
                         ->get()
                         ->getResultArray();

                    // Append branch list to the current cluster
                    $c['branchList'] = $branchList;
               }

               return $clusters;
          } catch (\Exception $e) {
               log_message('error', 'Database query failed: ' . $e->getMessage());
               return [];
          }
     }




     public function getAssets()
     {
          return $this->db->table('asset_name')
               ->select('*')
               ->where('status', 'A')
               ->get()
               ->getResultArray();
     }

     public function getManagers()
     {
          return $this->db->table('managers')
               ->select('*')
               ->where('status', 'A')
               ->get()
               ->getResultArray();
     }

     public function getRoles()
     {
          return $this->db->table('roles')
               ->select('*')
               ->get()
               ->getResultArray();
     }






     public function getServiceManager()
     {
          return $this->db->table('service_manager')
               ->select('*')
               ->where('status', 'A')
               ->get()
               ->getResultArray();
     }

     public function editServiceManager($data, $id)
     {
          if (is_array($data) && $id > 0) {
               // Ensure 'branch' exists in 'bi_centres' table, not in 'departments'
               return $this->db->table('service_manager')  // Correct table name
                    ->set($data)
                    ->where('id', $id) // Make sure 'branch' is a valid column in 'bi_centres'
                    ->update();
          }
          return false;
          // Return false if data is not valid
     }

     public function editArea($data, $id)
     {
          if (is_array($data) && $id > 0) {
               // Ensure 'branch' exists in 'bi_centres' table, not in 'departments'
               return $this->db->table('area')  // Correct table name
                    ->set($data)
                    ->where('id', $id) // Make sure 'branch' is a valid column in 'bi_centres'
                    ->update();
          }
          return false;
          // Return false if data is not valid
     }

     public function editZone($data, $z_id)
     {
          if (is_array($data) && $z_id > 0) {
               // Ensure 'branch' exists in 'bi_centres' table, not in 'departments'
               return $this->db->table('zones')  // Correct table name
                    ->set($data)
                    ->where('z_id', $z_id) // Make sure 'branch' is a valid column in 'bi_centres'
                    ->update();
          }
          return false;
     }

     public function addServiceManager($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('service_manager')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into service_manager table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }

     public function deleteServiceManager($id)
     {
          return $this->db->table('service_manager')->where('id', $id)->delete();
     }

     public function getCategory()
     {
          return $this->db->table('category')
               ->select('*')
               ->orderBy('name', 'ASC')
               ->get()
               ->getResultArray();
     }

     // public function getBranchDetails() {
     //     return $this->db->table( 'bi_centres as a' )
     //     ->select( 'a.branch, a.id as id, a.`cluster` as cl_id, ar.area as area,
     //     a.manager as manager_id, c.name as manager_name, b.name as cluster_name, a.mobile ' )
     //      ->join( 'managers as c', 'c.id = a.manager', 'left' )
     //      ->join( 'cluster as b', 'b.id = a.cluster', 'left' )
     //      ->join( 'clust_area_map as cl', 'cl.cluster_id = a.cluster', 'left' )
     //     ->join( 'area as ar', 'ar.id = cl.area_id', 'left' )
     //     ->orderBy( 'a.branch', 'ASC' )
     //     ->get()
     //     ->getResultArray();
     // }

     public function getBranchDetails()
     {
          return $this->db->table('branches as a')
               ->select('a.*, c.cluster, z.zone  ')
               ->join('cluster_branch_map as cb', 'cb.branch_id = a.branch_id', 'left')
               ->join('cluster_zone_map as cz', 'cz.cluster_id = cb.cluster_id', 'left')
               ->join('zones as z', 'z.z_id = cz.zone_id', 'left')
               ->join('clusters as c', 'c.cluster_id = cb.cluster_id', 'left')
               ->orderBy('a.branch', 'ASC')
               ->get()
               ->getResultArray();
     }

     public function editBranchDetails($data, $id)
     {
          if (is_array($data) && !empty($id)) {
               return $this->db->table('branches')
                    ->set($data)
                    ->where('branch_id', $id)
                    ->update();
          }
          return false;
     }

     public function editDeptDetails($data, $id)
     {
          if (is_array($data) && $id > 0) {
               // Ensure 'branch' exists in 'bi_centres' table, not in 'departments'
               return $this->db->table('departments')  // Correct table name
                    ->set($data)
                    ->where('id', $id) // Make sure 'branch' is a valid column in 'bi_centres'
                    ->update();
          }
          return false;
          // Return false if data is not valid
     }

     public function editManagerDetails($data, $id)
     {
          if (is_array($data) && $id > 0) {
               // Ensure 'branch' exists in 'bi_centres' table, not in 'departments'
               return $this->db->table('managers')  // Correct table name
                    ->set($data)
                    ->where('id', $id) // Make sure 'branch' is a valid column in 'bi_centres'
                    ->update();
          }
          return false;
          // Return false if data is not valid
     }

     public function editClusterDetails($data, $id)
     {
          if (is_array($data) && $id > 0) {

               return $this->db->table('clusters')
                    ->set($data)
                    ->where('cluster_id', $id)
                    ->update();
          }
          return false;
     }

     public function editTechnician($data, $id)
     {
          if (is_array($data) && $id > 0) {

               return $this->db->table('staff')
                    ->set($data)
                    ->where('id', $id)
                    ->update();
          }
          return false;
     }

     public function editAssets($data, $id)
     {
          if (is_array($data) && $id > 0) {

               return $this->db->table('asset_name')
                    ->set($data)
                    ->where('id', $id)
                    ->update();
          }
          return false;
     }

     public function getEquipments()
     {
          return $this->db->table('equipment as e')
               ->select('e.*, d.dept_name')
               ->join('departments as d', 'd.id = e.dept_id')
               ->get()
               ->getResultArray();
     }

     public function editEquipments($data, $id)
     {
          if (is_array($data) && $id > 0) {
               // Ensure 'branch' exists in 'bi_centres' table, not in 'departments'
               return $this->db->table('equipment')  // Correct table name
                    ->set($data)
                    ->where('id', $id) // Make sure 'branch' is a valid column in 'bi_centres'
                    ->update();
          }
          return false;
          // Return false if data is not valid
     }

     public function addEquipments($data)
     {
          $this->db->transStart();
          // Start the transaction

          // Insert the data into the 'diem' table
          $this->db->table('equipment')->insert($data);

          // Check if the transaction completed successfully
          if ($this->db->transStatus() === FALSE) {
               $this->db->transRollback();
               // Rollback in case of an error
               log_message('error', 'Failed to insert data into Equipment table: ' . $this->db->error()['message']);
               return false;
          }

          $this->db->transComplete();
          // Commit the transaction
          return true;
     }

     public function deleteEquipments($id)
     {
          return $this->db->table('equipment')->where('id', $id)->delete();
     }


     public function getClusters()
     {
          $db2 = \Config\Database::connect('secondary');

          // Step 1: Fetch all active branches from secondary DB
          $branchList = $db2->table('Branches')
               ->select('id, SysField')
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          // Step 2: Map branch ID => name
          $branchMap = [];
          foreach ($branchList as $branch) {
               $branchMap[$branch['id']] = $branch['SysField'];
          }

          // Step 3: Get clusters from main DB
          $clusters = $this->db->table('clusters')->get()->getResultArray();

          // Step 4: Attach branch names to each cluster
          foreach ($clusters as &$cluster) {
               $branchIds = explode(',', $cluster['branches']);
               $branchNames = [];

               foreach ($branchIds as $branchId) {
                    $branchId = trim($branchId);
                    if (isset($branchMap[$branchId])) {
                         $branchNames[] = $branchMap[$branchId];
                    }
               }

               $cluster['branch_names'] = implode(', ', $branchNames);
          }

          return $clusters;
     }

     public function getZones()
     {
          $db2 = \Config\Database::connect('secondary');
          $primaryDb = \Config\Database::connect(); // Main DB (default)

          // Step 1: Fetch all active branches from secondary DB
          $branchList = $db2->table('Branches')
               ->select('id, SysField')
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          // Step 2: Map branch ID => name
          $branchMap = [];
          foreach ($branchList as $branch) {
               $branchMap[$branch['id']] = $branch['SysField'];
          }

          // Step 3: Fetch all active clusters from main DB
          $clusterList = $primaryDb->table('clusters')
               ->select('cluster_id, cluster')
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          // Step 4: Map cluster ID => name
          $clusterMap = [];
          foreach ($clusterList as $cluster) {
               $clusterMap[$cluster['cluster_id']] = $cluster['cluster'];
          }

          // Step 5: Fetch all zones from main DB
          $zones = $primaryDb->table('zones')
               ->select('*')
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          // Step 6: Attach branch and cluster names to each zone
          foreach ($zones as &$zone) {
               // --- Process Branch Names ---
               $branchIds = explode(',', $zone['branches']);
               $branchNames = [];

               foreach ($branchIds as $branchId) {
                    $branchId = trim($branchId);
                    if (isset($branchMap[$branchId])) {
                         $branchNames[] = $branchMap[$branchId];
                    }
               }

               $zone['branch_names'] = implode(', ', $branchNames);

               // --- Process Cluster Names ---
               $clusterIds = explode(',', $zone['clusters']);
               $clusterNames = [];

               foreach ($clusterIds as $clusterId) {
                    $clusterId = trim($clusterId);
                    if (isset($clusterMap[$clusterId])) {
                         $clusterNames[] = $clusterMap[$clusterId];
                    }
               }

               $zone['cluster_names'] = implode(', ', $clusterNames);
          }

          return $zones;
     }




     public function getBranches()
     {
          $db2 = \Config\Database::connect('secondary');
          return $db2->table('Branches')
               ->select('*')
               ->where('Status', 'A')
               // ->orderBy( 'branch', 'ASC' )
               ->orderBy('SysField', 'ASC')
               ->get()
               ->getResultArray();
     }


     public function getClusterByid($id)
     {
          return $this->db->table('clusters')
               ->select('*')
               ->where('cluster_id', $id)
               ->where('status', 'A')
               ->get()
               ->getResultArray();
     }


     public function updateCluster($id, $data)
     {
          //    return $this->db->table( 'clusters' )

          return $this->db->table('clusters')  // Correct table name
               ->set($data)
               ->where('cluster_id', $id) // Make sure 'branch' is a valid column in 'bi_centres'
               ->update();
     }

     public function deleteClusterById($id)
     {
          return $this->db->table('clusters')
               ->where('cluster_id', $id)
               ->delete();
     }



     public function saveCluster($data)
     {
          //    return $this->db->table( 'clusters' )

          return $this->db->table('clusters')  // Correct table name
               ->insert($data);
     }


     public function getZonalByid($id)
     {
          return $this->db->table('zones')
               ->select('*')
               ->where('z_id', $id)
               ->where('status', 'A')
               ->get()
               ->getResultArray();
     }


     public function updateZonal($id, $data)
     {
          //    return $this->db->table( 'clusters' )

          return $this->db->table('zones')  // Correct table name
               ->set($data)
               ->where('z_id', $id) // Make sure 'branch' is a valid column in 'bi_centres'
               ->update();
     }

     public function getZonals()
     {

          return $this->db->table('zones')
               ->select('*')
               ->where('Status', 'A')
               ->get()
               ->getResultArray();
     }

     public function assignZoneToEmployee($data)
     {

          $branches = is_array($data['branches'])
               ? implode(',', $data['branches'])
               : $data['branches']; // Ensure it's CSV

          $data['branches'] = $branches;

          // Check if emp_code already exists in user_map
          $existing = $this->db->table('user_map')
               ->where('emp_code', $data['emp_code'])
               ->get()
               ->getRow();

          if ($existing) {
               // Update the existing record
               return $this->db->table('user_map')
                    ->where('emp_code', $data['emp_code'])
                    ->update($data);
          } else {

               // Insert a new record
               $this->db->table('user_map')
                    ->insert($data);
          }
     }


     public function getUserBranchList_new($emp_code)
     {
          // Fetch user data from the primary database
          $userData = $this->db->table('user_map')
               ->select('*')
               ->where('emp_code', $emp_code)
               ->get()
               ->getResultArray();

          if (empty($userData)) {
               return ['status' => false, 'message' => 'No user found.'];
          }

          // Get the branch IDs from the user data (assuming the branches are stored as a comma-separated string)
          $branchIds = explode(',', $userData[0]['branches']);

          // Connect to the secondary database for fetching branch names
          $db2 = \Config\Database::connect('secondary');

          // Fetch the branch details from the secondary DB
          $branchList = $db2->table('Branches')
               ->select('id, SysField')
               ->whereIn('id', $branchIds)
               ->where('status', 'A')  // Only active branches
               ->get()
               ->getResultArray();

          // Map the branch IDs to their names
          $branchNames = [];
          foreach ($branchList as $branch) {
               $branchNames[$branch['id']] = $branch['SysField'];
          }

          // Map the branch names back to the user data
          $userData[0]['branch_names'] = array_map(function ($branchId) use ($branchNames) {
               return $branchNames[$branchId] ?? 'Unknown';
          }, $branchIds);

          // Return the user data along with branch names
          return $userData;
     }




     public function getUserMap($id)
     {
          return $this->db->table('user_map')
               ->select('*')
               ->where('emp_code', $id)
               // ->where( 'status', 'A')
               ->get()
               ->getResultArray();
     }

     public function getUserMapBranches($emp_code)
     {
          return $this->db->table('user_map')
               ->select('branches')
               ->where('emp_code', $emp_code)
               // ->where( 'status', 'A')
               ->get()
               ->getResultArray();
     }

     public function getUserBranches($branches)
     {
          $db2 = \Config\Database::connect('secondary');

          // Convert CSV to array
          $branchArray = array_map('trim', explode(',', $branches));

          return $db2->table('Branches')
               ->select('*')
               ->where('Status', 'A')
               ->whereIn('id', $branchArray)
               ->get()
               ->getResultArray();
     }

     public function getMatchedBranches($clusterId)
     {
          // 1) Fetch the comma‑list of branch IDs for this cluster
          $cluster = $this->db->table('clusters')
               ->select('branches')
               ->where('cluster_id', $clusterId)
               ->get()
               ->getRowArray();

          if (empty($cluster) || empty($cluster['branches'])) {
               return []; // no branches found
          }

          // 2) Turn it into a clean array of IDs
          $branchIds = array_filter(array_map('trim', explode(',', $cluster['branches'])));
          if (empty($branchIds)) {
               return []; // nothing to look up
          }

          // 3) Query only those IDs from the secondary DB, selecting both id and SysField
          $db2 = \Config\Database::connect('secondary');
          $branchRows = $db2->table('Branches')
               ->select('id, SysField')
               ->whereIn('id', $branchIds)
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          // 4) Return each row as ['id' => ..., 'SysField' => ...]
          return $branchRows;
     }

     public function getClustersWithBranches($user)
     {
          $db2 = \Config\Database::connect('secondary'); // For branches
          $primaryDb = \Config\Database::connect();      // For clusters and user_map

          // Step 1: Fetch all active branches from secondary DB
          $branchList = $db2->table('Branches')
               ->select('id, SysField')
               ->where('status', 'A')
               ->get()
               ->getResultArray();

          // Step 2: Map branch ID => name
          $branchMap = [];
          foreach ($branchList as $branch) {
               $branchMap[$branch['id']] = $branch['SysField'];
          }

          // Step 3: Fetch allowed cluster IDs for the user from user_map
          $userMap = $primaryDb->table('user_map')
               ->select('cluster')
               ->where('emp_code', $user)
               ->get()
               ->getRow();

          // Handle case where no mapping exists
          if (!$userMap || empty($userMap->cluster)) {
               return []; // Or return an error response
          }

          // Parse comma-separated cluster IDs
          $allowedClusterIds = array_map('trim', explode(',', $userMap->cluster));

          // Step 4: Fetch only allowed clusters from clusters table
          $clusterList = $primaryDb->table('clusters')
               ->select('*')
               ->where('status', 'A')
               ->whereIn('cluster_id', $allowedClusterIds)
               ->get()
               ->getResultArray();

          // Step 5: Attach branch names to each cluster
          foreach ($clusterList as &$cluster) {
               $branchNames = [];

               if (!empty($cluster['branches'])) {
                    $branchIds = explode(',', $cluster['branches']);
                    foreach ($branchIds as $branchId) {
                         $branchId = trim($branchId);
                         if (isset($branchMap[$branchId])) {
                              $branchNames[] = $branchMap[$branchId];
                         }
                    }
               }

               $cluster['branch_names'] = implode(', ', $branchNames);
          }

          return $clusterList;
     }


     public function getYesterdaysReading($user)
     {
          return $this->db->table('power_consumption')
               ->select('nonbusinesshours')
               ->where('createdBy', $user)
               ->orderBy('createdDTM', 'DESC')
               ->limit(1)
               ->get()
               ->getRowArray(); // get only one row
     }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\DeptModel;
use App\Services\JwtService;

class Home extends BaseController
{
     use ResponseTrait;

     public function index(): string
     {
          return view('welcome_message');
     }

     public function getArea()
     {

          //  $userDetails = $this->validateAuthorization();


          $deptModel = new DeptModel();
          $area = $deptModel->getArea();

          if ($area) {
               return $this->respond(['status' => true, 'data' => $area], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area not found'], 404);
          }
     }



     public function clusterMapping()
     {
          $deptModel = new DeptModel();
          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;
          $branches = $this->request->getPost('branches');
          $areas = $this->request->getPost('areas');
          $cluster_id = $this->request->getPost('cluster_id');

          if (is_array($branches)) {
               foreach ($branches as $branch_id) {

                    $data = [
                         'cluster_id' =>  $cluster_id, // Assuming $clusterId is available
                         'branch_id' => $branch_id,
                         'createdDTM' => date('Y-m-d'),
                         'created_by' => $user
                    ];

                    $data2 = [
                         'cluster' =>  $cluster_id,
                         'createdon' => date('Y-m-d'),
                         'createdby' => $user
                    ];

                    $insertBranch = $deptModel->addBranchToCluster($data, $branch_id);
                    $insertBranchTobi_centres = $deptModel->addClusterTo_bi_centres($data2, $branch_id);
               }
          } else {
               return redirect()->back()->with('error', 'Invalid branches selection.');
          }

          if (is_array($areas)) {
               foreach ($areas as $a) {

                    $data2 = [
                         'cluster_id' =>  $cluster_id, // Assuming $clusterId is available
                         'area_id' => $a,
                         'createdDTM' => date('Y-m-d'),
                         'created_by' => $user
                    ];

                    $insertCluster = $deptModel->addAreaToCluster($data2, $cluster_id);
               }
          } else {
               // Handle case when 'branches' is not an array (if needed)
               return redirect()->back()->with('error', 'Invalid area selection.');
          }

          return $this->respond(['status' => true, 'message' => 'Area added successfully.', 'data' => $data, 'data2' => $data2,], 200);
     }

     public function getRoles()
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $roles = $deptModel->getRoles();

          if ($roles) {
               return $this->respond(['status' => true, 'data' => $roles], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area not found'], 404);
          }
     }

     public function getZones()
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $zones = $deptModel->getZones();

          if ($zones) {
               return $this->respond(['status' => true, 'data' => $zones], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area not found'], 404);
          }
     }


     public function addArea()
     {
          // Retrieve the authorization header
          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;
          $area = $this->request->getPost('area');
          $data = [
               'area' => $area,
               'created_on' => date('Y-m-d'),
               'created_by' => $emp_code,
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $area = $deptModel->addArea($data);

          if ($area) {
               return $this->respond(['status' => true, 'message' => 'Area added successfully.', 'data' => $area], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area Details not found'], 404);
          }
     }

     public function addZone()
     {
          // Retrieve the authorization header
          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;
          $zone = $this->request->getPost('zone');
          $data = [
               'zone' => $zone,
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $zone = $deptModel->addZone($data);

          if ($zone) {
               return $this->respond(['status' => true, 'message' => 'Area added successfully.', 'data' => $zone], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area Details not found'], 404);
          }
     }


     public function getAssetDetails()
     {
          $deptModel = new DeptModel();
          $userDetails = $this->validateAuthorization();
          // Adjusted for array access
          $role = $userDetails->AC_TYPE;
          $USERID = $userDetails->{'USER-ID'};
          if (!empty($userDetails) && !empty($userDetails->branch)) {
               $branch = $userDetails->branch;
          } else {
               $branch = 0;
          }

          // Call the model function to get the ticket count
          $data =  $deptModel->getAssetDetails();
          if ($data) {
               return $this->respond(['status' => true, 'data' => $data], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Dashboard Count not found'], 404);
          }
     }

     public function DashboardCount()
     {

          $deptModel = new DeptModel();
          $userDetails = $this->validateAuthorization();
          // Adjusted for array access
          $role = $userDetails->AC_TYPE;
          $USERID = $userDetails->{'USER-ID'};

          if (!empty($userDetails) && !empty($userDetails->branch)) {
               $branch = $userDetails->branch;
          } else {
               $branch = 0;
          }

          $deptModel = new DeptModel();
          // Call the model function to get the ticket count
          $ticketTotalCount = $deptModel->getTicketTotalCount($role, $USERID, $branch);
          $ticketCompletedCount = $deptModel->getTicketCompletedCount($role, $USERID, $branch = null);
          $ticketPendingCount = $deptModel->getTicketPendingCount($role, $USERID, $branch = null);
          $roles = ['SUPER_ADMIN', 'MARKETING_MANAGER', 'BRANDING', 'BIO_MEDICAL', 'RMG', 'Quality_Control', 'LAB', 'LAB_Others', 'USER', 'CLUSTER'];
          $ticketRolewisePendingCount = $deptModel->getTicketRolewisePendingCount($roles, $USERID, $branch = null);
          $d1 = $ticketRolewisePendingCount;
          $d2 = [
               'ticketTotalCount' => $ticketTotalCount,
               'ticketCompletedCount' => $ticketCompletedCount,
               'ticketPendingCount' => $ticketPendingCount,
          ];
          $data = $d1 + $d2;
          if ($data) {
               return $this->respond(['status' => true, 'data' => $data], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Dashboard Count not found'], 404);
          }
     }

     public function getDeptWithCat()
     {

          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          } else {
               // Check if the token starts with 'Bearer ' and remove it
               if (strpos($authorizationHeader, 'Bearer ') === 0) {
                    $token = substr($authorizationHeader, 7);
                    // Remove 'Bearer ' prefix
               } else {
                    $token = $authorizationHeader;
               }
          }
          $deptModel = new DeptModel();
          $depts = $deptModel->getDeptWithCat();

          if ($depts) {
               return $this->respond(['status' => true, 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function addDept()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');
          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }
          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $dept_name = $this->request->getPost('dept_name');
          $cat_id = $this->request->getPost('cat_id');
          //$id = $this->request->getPost( 'id' );
          $data = [
               'dept_name' => $dept_name,
               'cat_id' => $cat_id,
               'created_on' => date('Y-m-d H:i:s'),
               'created_by' => 'Admin',
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->addDept($data);

          if ($depts) {
               return $this->respond(['status' => true, 'message' => 'Dept added successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Dept Details not found'], 404);
          }
     }

     public function addNewManager()
     {
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $name = $this->request->getPost('name');
          $bmid = $this->request->getPost('bmid');
          $mobile = $this->request->getPost('mobile');
          $email = $this->request->getPost('email');

          $data = [
               'name' => $name,
               'mobile' => $mobile,
               'email' => $email,
               'bmid' => $bmid,
               'created_on' => date('Y-m-d H:i:s'),
               'created_by' => '106475',
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->addNewManager($data);

          if ($depts) {
               return $this->respond(['status' => true, 'message' => 'Manager added successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Manager Details not found'], 404);
          }
     }

     public function addNewCluster()
     {
          $userDetails = $this->validateAuthorization();
          $USER  = $userDetails->emp_code;
          $name = $this->request->getPost('cluster');
          //$area_id = $this->request->getPost( 'area_id' );
          $data = [
               'cluster' => $name,
               'createdDTM' => date('Y-m-d'),
               'created_by' => $USER,
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->addCluster($data);

          if ($depts > 0) {
               // $info = [
               //     'cluster_id' => $depts,
               //     'area_id' => $area_id,
               //     'created_by' => $USER,
               //     'createdDTM'=> date( 'Y-m-d')
               // ];
               // $update =  $deptModel->addAreaToCluster($info, $depts);
               return $this->respond(['status' => true, 'message' => 'Cluster added successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Cluster Details not found'], 404);
          }
     }

     public function deleteDept()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $id = $this->request->getPost('id');

          $deptModel = new \App\Models\DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteDept($id);

          if ($delete) {
               return $this->respond(['status' => true, 'message' => 'Dept deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Dept Details not found'], 404);
          }
     }

     public function deleteCluster()
     {
          $userDetails = $this->validateAuthorization();

          $USERID = $userDetails->emp_code;
          // Retrieve posted data
          $id = $this->request->getPost('cluster_id');

          $deptModel = new DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteCluster($id);

          if ($delete && $id > 0) {
               return $this->respond(['status' => true, 'message' => 'Cluster deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Cluster Details not found'], 404);
          }
     }

     public function deleteManager()
     {
          // Retrieve the authorization header
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $id = $this->request->getPost('id');

          $deptModel = new \App\Models\DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteManager($id);

          if ($delete) {
               return $this->respond(['status' => true, 'message' => 'Manager deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Manager Details not found'], 404);
          }
     }

     public function deleteTechnician()
     {
          // Retrieve the authorization header
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $id = $this->request->getPost('id');

          $deptModel = new \App\Models\DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteTechnician($id);

          if ($delete) {
               return $this->respond(['status' => true, 'message' => 'Technician deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Technician Details not found'], 404);
          }
     }

     private function validateToken($token)
     {
          // Implement your token validation logic here.
          // For example, decode the token and verify its signature and expiration.
          // Return true if valid, otherwise false.
     }

     public function editDept()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $dept_name = $this->request->getPost('dept_name');
          $cat_id = $this->request->getPost('cat_id');
          $id = $this->request->getPost('id');
          $data = [
               'dept_name' => $dept_name,
               'cat_id' => $cat_id,
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->editDeptDetails($data, $id);

          if ($depts) {
               return $this->respond(['status' => true, 'message' => 'Dept Details updated successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Dept Details not found'], 404);
          }
     }

     public function editArea()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $area = $this->request->getPost('area');

          $id = $this->request->getPost('id');
          $data = [
               'area' => $area,

          ];

          $deptModel = new DeptModel();
          $area = $deptModel->editArea($data, $id);

          if ($area) {
               return $this->respond(['status' => true, 'message' => 'Area Details updated successfully.', 'data' => $area], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area Details not found'], 404);
          }
     }


     public function editZone()
     {
          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;

          $zone = $this->request->getPost('zone');
          $z_id = $this->request->getPost('z_id');
          $data = [
               'zone' => $zone,
          ];

          $deptModel = new DeptModel();
          $zone = $deptModel->editZone($data, $z_id);

          if ($zone) {
               return $this->respond(['status' => true, 'message' => 'Zone Details updated successfully.', 'data' => $zone], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Zone Details not found'], 404);
          }
     }

     public function deleteZone()
     {
          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;

          $data = [
               'status' => 'I'
          ];

          $id = $this->request->getPost('z_id');
          $deptModel = new DeptModel();
          $area = $deptModel->editZone($data, $id);

          if ($area) {
               return $this->respond(['status' => true, 'message' => 'Zone Details deleted successfully.', 'data' => $area], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Zone Details not found'], 404);
          }
     }


     public function deleteArea()
     {
          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;

          $data = [
               'status' => 'I'
          ];

          $id = $this->request->getPost('id');


          $deptModel = new DeptModel();
          $area = $deptModel->editArea($data, $id);

          if ($area) {
               return $this->respond(['status' => true, 'message' => 'Area Details updated successfully.', 'data' => $area], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area Details not found'], 404);
          }
     }

     public function editManager()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $name = $this->request->getPost('name');
          $bmid = $this->request->getPost('bmid');
          $mobile = $this->request->getPost('mobile');
          $email = $this->request->getPost('email');
          $id = $this->request->getPost('id');
          $data = [
               'name' => $name,
               'mobile' => $mobile,
               'email' => $email,
               'bmid' => $bmid,
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->editManagerDetails($data, $id);

          if ($depts) {
               return $this->respond(['status' => true, 'message' => 'Manager Details updated successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Manager Details not found'], 404);
          }
     }

     public function editCluster()
     {
          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;

          // Retrieve posted data
          $cluster = $this->request->getPost('cluster');
          $cluster_id = $this->request->getPost('cluster_id');
          $data = [
               'cluster' => $cluster,
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->editClusterDetails($data, $cluster_id);

          if ($depts) {
               // $info = [
               //     'cluster_id' => $id,
               //     'area_id' => $area_id,
               //     'created_by' => $emp_code,
               //     'createdDTM'=> date( 'Y-m-d')
               // ];
               // $update =  $deptModel->addAreaToCluster($info, $id);

               return $this->respond(['status' => true, 'message' => 'Cluster Details updated successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Cluster Details not found'], 404);
          }
     }

     public function getAllCluster()
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $cluster = $deptModel->getAllCluster();
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }





     public function getBranchDetails()
     {

          $userDetails = $this->validateAuthorization();
          $emp_code = $userDetails->emp_code;

          $deptModel = new DeptModel();
          $branches = $deptModel->getBranchDetails();

          if ($branches) {
               return $this->respond(['status' => true, 'data' => $branches], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Branch Details not found'], 404);
          }
     }

     public function addNewBranch()
     {

          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;

          $branch = $this->request->getPost('branch');
          $branch_code = $this->request->getPost('branch_code');

          $b = $branch . ' -  ' . $branch_code;
          $data = [
               'branch' => $b,
               'status' => 'A',
               'created_by' => $user,
               'createdDTM' => date('Y-m-d H:i:s'),
          ];

          $deptModel = new DeptModel();
          $branches = $deptModel->addNewBranch($data);

          if ($branches) {
               return $this->respond(['status' => true, 'message' => 'New Branch added Successfully', 'data' => $branches],  200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Branch Details not found'], 404);
          }
     }

     public function editBranchDetails()
     {
          // Step 1: Validate authorization and get user details
          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;

          // Step 2: Get POST data
          $branch = $this->request->getPost('branch');
          $status = $this->request->getPost('status');
          $branch_id = $this->request->getPost('branch_id');

          // Step 3: Retrieve previous data from the database using branch_id
          $deptModel = new DeptModel();
          $prevData = $deptModel->getBranchDetailsById($branch_id); // Assuming this method returns the branch data by id

          // Step 4: Check if branch exists
          if (empty($prevData)) {
               return $this->respond(['status' => false, 'message' => 'Branch not found'], 404);
          }

          // Step 5: Access 'status' depending on the type of $prevData
          if (is_array($prevData)) {
               // If $prevData is an array (e.g., returned by getResultArray)
               $prevStatus = $prevData['status'] ?? null;
          } elseif (is_object($prevData)) {
               // If $prevData is an object (e.g., returned by getRow)
               $prevStatus = $prevData->status ?? null;
          } else {
               $prevStatus = null;
          }

          // If no status provided in the request, fallback to previous status
          if (empty($status)) {
               $status = $prevStatus; // Use the previous status if not provided
          }

          // Step 6: Prepare data for update
          $data = [
               'branch' => $branch,
               'status' => $status,
               'createdDTM' => date('Y-m-d H:i:s'),
               'created_by' => $user,
          ];

          // Step 7: Update branch details
          $updatedBranch = $deptModel->editBranchDetails($data, $branch_id);

          // Step 8: Respond based on update success or failure
          if ($updatedBranch) {
               return $this->respond(['status' => true, 'data' => $updatedBranch], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Failed to update branch details'], 500);
          }
     }


     public function getManagers()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          } else {
               // Check if the token starts with 'Bearer ' and remove it
               if (strpos($authorizationHeader, 'Bearer ') === 0) {
                    $token = substr($authorizationHeader, 7);
                    // Remove 'Bearer ' prefix
               } else {
                    $token = $authorizationHeader;
               }
          }
          $deptModel = new DeptModel();
          $managers = $deptModel->getManagers();

          if ($managers) {
               return $this->respond(['status' => true, 'data' => $managers], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function getCategory()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          } else {
               // Check if the token starts with 'Bearer ' and remove it
               if (strpos($authorizationHeader, 'Bearer ') === 0) {
                    $token = substr($authorizationHeader, 7);
                    // Remove 'Bearer ' prefix
               } else {
                    $token = $authorizationHeader;
               }
          }
          $deptModel = new DeptModel();
          $managers = $deptModel->getCategory();

          if ($managers) {
               return $this->respond(['status' => true, 'data' => $managers], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function getTechnicians()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          } else {
               // Check if the token starts with 'Bearer ' and remove it
               if (strpos($authorizationHeader, 'Bearer ') === 0) {
                    $token = substr($authorizationHeader, 7);
                    // Remove 'Bearer ' prefix
               } else {
                    $token = $authorizationHeader;
               }
          }
          $deptModel = new DeptModel();
          $technicians = $deptModel->getTechnicians();

          if ($technicians) {
               return $this->respond(['status' => true, 'data' => $technicians], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Technicians not found'], 404);
          }
     }

     public function addNewTechnician()
     {
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $name = $this->request->getPost('name');
          $bmid = $this->request->getPost('bmid');
          $roll = $this->request->getPost('roll');

          $data = [
               'name' => $name,
               'roll' => $roll,
               'bmid' => $bmid,
               'created_on' => date('Y-m-d H:i:s'),
               'created_by' => '106475',
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->addNewTechnician($data);

          if ($depts) {
               return $this->respond(['status' => true, 'message' => 'Technician added successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Technician Details not found'], 404);
          }
     }

     public function editTechnician()
     {

          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $name = $this->request->getPost('name');
          $bmid = $this->request->getPost('bmid');
          $roll = $this->request->getPost('roll');
          $id = $this->request->getPost('id');

          $data = [
               'name' => $name,
               'roll' => $roll,
               'bmid' => $bmid,
          ];

          $deptModel = new DeptModel();
          $technicians = $deptModel->editTechnician($data, $id);

          if ($technicians) {
               return $this->respond(['status' => true, 'data' => $technicians], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Technician Details not found'], 404);
          }
     }

     public function getAssets()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          } else {
               // Check if the token starts with 'Bearer ' and remove it
               if (strpos($authorizationHeader, 'Bearer ') === 0) {
                    $token = substr($authorizationHeader, 7);
                    // Remove 'Bearer ' prefix
               } else {
                    $token = $authorizationHeader;
               }
          }
          $deptModel = new DeptModel();
          $technicians = $deptModel->getAssets();

          if ($technicians) {
               return $this->respond(['status' => true, 'data' => $technicians], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Assets not found'], 404);
          }
     }

     public function editAssets()
     {

          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');
          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }
          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);
          // Retrieve posted data
          $name = $this->request->getPost('name');
          $id = $this->request->getPost('id');
          $data = [
               'name' => $name,
          ];
          $deptModel = new DeptModel();
          $assets = $deptModel->editAssets($data, $id);
          if ($assets) {
               return $this->respond(['status' => true, 'data' => $assets], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Asset Details not found'], 404);
          }
     }

     public function addNewAssets()
     {
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $name = $this->request->getPost('name');

          $data = [
               'name' => $name,
               'created_on' => date('Y-m-d H:i:s'),
               'created_by' => '106475',
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->addNewAssets($data);

          if ($depts) {
               return $this->respond(['status' => true, 'message' => 'Assets added successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Assets Details not found'], 404);
          }
     }

     public function deleteAssets()
     {
          // Retrieve the authorization header
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $id = $this->request->getPost('id');

          $deptModel = new \App\Models\DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteAssets($id);

          if ($delete) {
               return $this->respond(['status' => true, 'message' => 'Assets deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Assets Details not found'], 404);
          }
     }

     public function deleteBranch()
     {
          // Retrieve the authorization header
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $branch_id = $this->request->getPost('branch_id');

          $deptModel = new \App\Models\DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteBranch($branch_id);

          if ($delete) {
               return $this->respond(['status' => true, 'message' => 'Branch deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Branch Details not found'], 404);
          }
     }

     public function getServiceManager()
     {
          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $managers = $deptModel->getServiceManager();

          if ($managers) {
               return $this->respond(['status' => true, 'data' => $managers], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function addServiceManager()
     {
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $name = $this->request->getPost('name');
          $number = $this->request->getPost('number');

          $data = [
               'name' => $name,
               'number' => $number,
               'created_on' => date('Y-m-d H:i:s'),
               'created_by' => '106475',
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $depts = $deptModel->addServiceManager($data);

          if ($depts) {
               return $this->respond(['status' => true, 'message' => 'Assets added successfully.', 'data' => $depts], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Assets Details not found'], 404);
          }
     }

     public function deleteServiceManager()
     {
          // Retrieve the authorization header
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $id = $this->request->getPost('id');

          $deptModel = new \App\Models\DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteServiceManager($id);

          if ($delete) {
               return $this->respond(['status' => true, 'message' => 'Service Manager deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Service Manager Details not found'], 404);
          }
     }

     public function editServiceManager()
     {

          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');
          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }
          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);
          // Retrieve posted data
          $name = $this->request->getPost('name');
          $number = $this->request->getPost('number');
          $id = $this->request->getPost('id');
          $data = [
               'name' => $name,
               'number' => $number,
          ];
          $deptModel = new DeptModel();
          $assets = $deptModel->editServiceManager($data, $id);
          if ($assets) {
               return $this->respond(['status' => true, 'data' => $assets], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Service Manager Details not found'], 404);
          }
     }

     public function getEquipments()
     {
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          } else {
               // Check if the token starts with 'Bearer ' and remove it
               if (strpos($authorizationHeader, 'Bearer ') === 0) {
                    $token = substr($authorizationHeader, 7);
                    // Remove 'Bearer ' prefix
               } else {
                    $token = $authorizationHeader;
               }
          }
          $deptModel = new DeptModel();
          $s = $deptModel->getEquipments();

          if ($s) {
               return $this->respond(['status' => true, 'data' => $s], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function addEquipments()
     {
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $name = $this->request->getPost('name');
          $dept_id = $this->request->getPost('dept_id');

          $data = [
               'name' => $name,
               'dept_id' => $dept_id,
               'created_on' => date('Y-m-d H:i:s'),
               'created_by' => '106475',
               'status' => 'A'
          ];

          $deptModel = new DeptModel();
          $s = $deptModel->addEquipments($data);

          if ($s) {
               return $this->respond(['status' => true, 'message' => 'Assets added successfully.', 'data' => $s], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Assets Details not found'], 404);
          }
     }

     public function deleteEquipments()
     {
          // Retrieve the authorization header
          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');

          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }

          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);

          // Retrieve posted data
          $id = $this->request->getPost('id');

          $deptModel = new \App\Models\DeptModel();
          // Make sure to use the correct namespace
          $delete = $deptModel->deleteEquipments($id);

          if ($delete) {
               return $this->respond(['status' => true, 'message' => 'Equipments deleted successfully.'], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Equipments Details not found'], 404);
          }
     }

     public function editEquipments()
     {

          // Retrieve the authorization header
          $authorizationHeader = $this->request->getHeader('Authorization');
          if (!$authorizationHeader) {
               return $this->failUnauthorized('Unauthorized');
          }
          // Extract the token
          $token = preg_replace('/^Bearer\s/', '', $authorizationHeader);
          // Retrieve posted data
          $name = $this->request->getPost('name');
          $dept_id = $this->request->getPost('dept_id');
          $id = $this->request->getPost('id');
          $data = [
               'name' => $name,
               'dept_id' => $dept_id,
          ];
          $deptModel = new DeptModel();
          $assets = $deptModel->editEquipments($data, $id);
          if ($assets) {
               return $this->respond(['status' => true, 'data' => $assets], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Equipments Details not found'], 404);
          }
     }

     private function validateAuthorization()
     {
          if (!class_exists('App\Services\JwtService')) {
               //log_message( 'error', 'JwtService class not found' );
               return $this->respond(['error' => 'JwtService class not found'], 500);
          }
          // Get the Authorization header and log it
          $authorizationHeader = $this->request->getHeader('Authorization') ? $this->request->getHeader('Authorization')->getValue() : null;
          //log_message( 'info', 'Authorization header: ' . $authorizationHeader );

          // Create an instance of JwtService and validate the token
          $jwtService = new JwtService();
          $result = $jwtService->validateToken($authorizationHeader);

          // Handle token validation errors
          if (isset($result['error'])) {
               //log_message( 'error', $result[ 'error' ] );
               return $this->respond(['error' => $result['error']], $result['status']);
          }

          // Extract the decoded token and get the USER-ID
          $decodedToken = $result['data'];
          return $decodedToken;
          // Assuming JWT contains USER-ID

     }

     public function getClusters()
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $cluster = $deptModel->getClusters();
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function getClusterByid($id)
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $cluster = $deptModel->getClusterByid($id);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function getBranches()
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $cluster = $deptModel->getBranches();
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }


     public function updateCluster($id)
     {

          $userDetails = $this->validateAuthorization();
          $cluster = $this->request->getVar('cluster');
          $branches = $this->request->getVar('branches');
          $data = [
               'cluster' => $cluster,
               'branches' => $branches
          ];
          // echo "<pre>";
          // echo $id;
          // print_r($data);die();
          $deptModel = new DeptModel();
          $cluster = $deptModel->updateCluster($id, $data);

          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function deleteClusterbYiD($id)
     {

          $userDetails = $this->validateAuthorization();



          $deptModel = new DeptModel();
          $cluster = $deptModel->deleteClusterbYiD($id);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function saveCluster()
     {
          $userDetails = $this->validateAuthorization();
          $cluster = $this->request->getVar('cluster');
          $branches = $this->request->getVar('branches');
          $data = [
               'cluster' => $cluster,
               'branches' => $branches
          ];

          $deptModel = new DeptModel();
          $cluster = $deptModel->saveCluster($data);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function getZonalByid($id)
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $cluster = $deptModel->getZonalByid($id);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function updateZonal($id)
     {
          $deptModel = new DeptModel();
          $userDetails = $this->validateAuthorization();

          // Get zone and cluster IDs from the request
          $zone = $this->request->getVar('zone');
          $clusters = $this->request->getVar('clusters'); // comma-separated cluster IDs
          $clusterIds = explode(',', $clusters);

          $allBranches = [];
          $validClusters = [];
          $invalidClusters = [];

          foreach ($clusterIds as $clusterId) {
               $clusterData = $deptModel->getClusterByid(trim($clusterId));

               // Check if data exists and is formatted correctly
               if (!empty($clusterData) && isset($clusterData[0])) {
                    $cluster = $clusterData[0];
                    $validClusters[] = $cluster;

                    // Explode branches and merge into final array
                    if (!empty($cluster['branches'])) {
                         $branches = explode(',', $cluster['branches']);
                         $allBranches = array_merge($allBranches, $branches);
                    }
               } else {
                    $invalidClusters[] = $clusterId;
               }
          }

          // Return error if any invalid clusters
          if (!empty($invalidClusters)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Invalid cluster IDs: ' . implode(', ', $invalidClusters)
               ], 400);
          }

          // Remove duplicate branches and re-index
          $uniqueBranches = array_values(array_unique($allBranches));
          $branchesString = implode(',', $uniqueBranches); // Final string for DB update

          // Prepare data for update
          $updateData = [
               'zone'     => $zone,
               'clusters' => $clusters,          // original cluster ID string
               'branches' => $branchesString     // combined branches from all clusters
          ];

          // Attempt update
          $updateResult = $deptModel->updateZonal($id, $updateData);

          if ($updateResult) {
               return $this->respond([
                    'status' => true,
                    'message' => 'Zone updated successfully.',
                    'data' => [
                         'zone'     => $zone,
                         'clusters' => $validClusters,
                         'branches' => $uniqueBranches
                    ]
               ], 200);
          } else {
               return $this->respond([
                    'status' => false,
                    'message' => 'Department not found or update failed.'
               ], 404);
          }
     }

     public function getZonals()
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $cluster = $deptModel->getZonals();
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }
     public function assignZoneToEmployee()
     {
          date_default_timezone_set('Asia/Kolkata'); // Ensure the timezone is set

          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;

          $emp_code = $this->request->getVar('emp_code');
          $role = $this->request->getVar('role');

          $zone_ids = $this->request->getVar('zone_ids');
          $cluster_ids = $this->request->getVar('cluster_ids') ?? '';
          $cluster_idsk = $this->request->getVar('cluster_ids') ?? '';
          $branch_ids = $this->request->getVar('branch_ids') ?? '';

          $deptModel = new DeptModel();


          $cluster_ids = [];
          $allBranches = [];

          if ($role === 'CM') {





               foreach ($cluster_idsk as $clusterId) {

                    // $zonalDetails = $deptModel->getZonalByid(trim($cluster_idsk));
                    $clusterDetails = $deptModel->getClusterByid(trim($clusterId));

                    $allBranches1[] = $clusterDetails[0]['branches'];
               }

               $cluster_Ids = implode(',', $cluster_ids);
               $branch_Ids = $allBranches1;

               $data = [
                    'role'       => $role,
                    'emp_code'   => $emp_code,
                    'zone'       => implode(',', $zone_ids),
                    'cluster'    => implode(',', $cluster_idsk),
                    'branches'   => $branch_Ids,

                    'created_on' => date('Y-m-d'),
                    'created_by' => $user
               ];

               $result = $deptModel->assignZoneToEmployee($data);
               if ($result) {
                    return $this->respond(['status' => true, 'data' => $result], 200);
               } else {
                    return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
               }
          }

          if (($role === 'ZONAL_MANAGER' || 'AVP')) {

               foreach ($zone_ids as $zone_id) {

                    $zonalDetails = $deptModel->getZonalByid(trim($zone_id));

                    if (!empty($zonalDetails) && isset($zonalDetails[0]['clusters']) && isset($zonalDetails[0]['branches'])) {
                         $zonalData[] = $zonalDetails;

                         // Explode and merge all branches
                         $branches = explode(',', $zonalDetails[0]['branches']);
                         $clusters = explode(',', $zonalDetails[0]['clusters']);
                         $allBranches = array_merge($allBranches, $branches);
                         $cluster_ids = array_merge($cluster_ids, $clusters);
                    }
               }
               $cluster_Ids = implode(',', $cluster_ids);
               $branch_Ids = implode(',', array_unique($allBranches));
               $data = [
                    'role'       => $role,
                    'emp_code'   => $emp_code,
                    'zone'       => implode(',', $zone_ids),
                    'cluster'    => implode(',', $cluster_ids),
                    'branches'   => !empty($branch_ids) ? $branch_ids : $branch_Ids,
                    'created_on' => date('Y-m-d'),
                    'created_by' => $user
               ];

               $result = $deptModel->assignZoneToEmployee($data);
               if ($result) {
                    return $this->respond(['status' => true, 'data' => $result], 200);
               }
          }

          if ($role === 'BM') {
               $cluster_Ids = implode(',', $cluster_ids);
               $branch_Ids = implode(',', array_unique($allBranches));
               $data = [
                    'role'       => $role,
                    'emp_code'   => $emp_code,
                    'zone'       => implode(',', $zone_ids),
                    'cluster'    => implode(',', $cluster_ids),
                    'branches'   => !empty($branch_ids) ? $branch_ids : $branch_Ids,
                    'created_on' => date('Y-m-d'),
                    'created_by' => $user
               ];

               $result = $deptModel->assignZoneToEmployee($data);
               if ($result) {
                    return $this->respond(['status' => true, 'data' => $result], 200);
               } else {
                    return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
               }
          }

          //     if ($role === 'CM' && !$branch_ids){



          //     foreach ($cluster_ids as $clusterId) {


          //         $clusterDetails = $deptModel->getClusterByid(trim($clusterId));

          //         if (!empty($clusterDetails) && isset($clusterDetails[0]['branches'])) {
          //             $clusterData[] = $clusterDetails;

          //             // Explode and merge all branches
          //             $branches = explode(',', $clusterDetails[0]['branches']);
          //             $allBranches = array_merge($allBranches, $branches);
          //         }
          //     }

          // }

          // Remove duplicates




     }



     public function getUserBranchList_new()
     {

          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;
          $deptModel = new DeptModel();
          $cluster = $deptModel->getUserBranchList_new($user);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }



     public function getUserMap($emp_code)
     {

          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;
          $deptModel = new DeptModel();
          $cluster = $deptModel->getUserMap($emp_code);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }


     public function getEmpBranches()
     {

          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;

          $deptModel = new DeptModel();
          $cluster = $deptModel->getUserMapBranches($user);
          $branches = $cluster['0']['branches'];

          $cluster = $deptModel->getUserBranches($branches);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function getMatchedBranches($id)
     {

          $userDetails = $this->validateAuthorization();
          $deptModel = new DeptModel();
          $matchedBranches = $deptModel->getMatchedBranches($id);


          if ($matchedBranches) {
               return $this->respond(['status' => true, 'data' => $matchedBranches], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Area not found'], 404);
          }
     }

     public function getClustersWithBranches()
     {
          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;

          $deptModel = new DeptModel();
          $cluster = $deptModel->getClustersWithBranches($user);
          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }

     public function getYesterdaysReading()
     {
          $userDetails = $this->validateAuthorization();
          $user = $userDetails->emp_code;

          $deptModel = new DeptModel();
          $cluster = $deptModel->getYesterdaysReading($user);

          if ($cluster) {
               return $this->respond(['status' => true, 'data' => $cluster], 200);
          } else {
               return $this->respond(['status' => false, 'message' => 'Departments not found'], 404);
          }
     }
}

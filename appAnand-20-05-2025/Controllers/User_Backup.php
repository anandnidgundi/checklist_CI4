<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\DeptModel;
use App\Models\MtModel;
use App\Models\NightModel;
use App\Models\CM_MtModel;
use App\Models\CM_NightModel;
use App\Models\BranchModel;
use App\Models\ClusterModel;
use App\Models\ZoneModel;


use App\Services\JwtService;

class User extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $users = new UserModel;

        // Check if emp_code is provided in the request
        $empCode = $this->request->getVar('emp_code');

        if ($empCode) {
            // Fetch a single user by emp_code
            $user = $users->where('emp_code', $empCode)->first();

            if (!$user) {
                return $this->respond(['error' => 'User not found'], 404);
            }

            return $this->respond(['user' => $user, 'STATUS' => true], 200);
        }

        // If emp_code is not provided, return all users
        return $this->respond(['users' => $users->findAll(), 'STATUS' => true], 200);
    }
    public function checkToken()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $userDetails->role;
        $users = new UserModel();
        $user = $users->getUserDetails($user);
        if ($user) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User details.',
                'data' => $user
            ], 200);
        } else {
            log_message('error', 'Failed to give user details: ' . json_encode($user, JSON_PRETTY_PRINT));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to retrieve user details.',
                'data' => $user
            ], 500);
        }
    }

    public function BM_DashboardCount()
    {
        $userModel = new UserModel();
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $userDetails->role;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();
        if (isset($requestData->selectedMonth)) {
            $selectedMonth = $requestData->selectedMonth;
        } else {
            $selectedMonth = date('Y-m');
        }

        if (isset($requestData->selectedBranch)) {
            $selectedBranch = $requestData->selectedBranch;
        } else {
            $selectedBranch = '0';
        }


        $BM_DashboardCount = $userModel->BM_DashboardCount($user, $role, $selectedMonth, $selectedBranch);
        if ($BM_DashboardCount) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'BM Dashboard Count.',
                'data' => $BM_DashboardCount
            ], 200);
        } else {
            log_message('error', 'Failed to give BM Dashboard Count: ' . json_encode($BM_DashboardCount, JSON_PRETTY_PRINT));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to retrieve BM Dashboard Count.',
                'data' => $BM_DashboardCount
            ], 500);
        }
    }

    public function CM_DashboardCount()
    {
        $userModel = new UserModel();
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $userDetails->role;

        // Retrieve POST data (json)
        $requestData = $this->request->getJSON();
       
        $selectedMonth = $requestData->selectedMonth ?? date('Y-m');
        // Extract branch IDs if it's an array, otherwise use the single value
        $selectedBranch = is_array($requestData->selectedBranch ?? '0') 
            ? implode(',', array_column($requestData->selectedBranch, 'branch'))
            : (string)($requestData->selectedBranch ?? '0');
            
        $selectedCluster = is_null($requestData->selectedCluster) ? '0' : (string)$requestData->selectedCluster;
        $selectedZone = is_null($requestData->zone_id) ? '0' : (string)$requestData->zone_id;
        
        $CM_DashboardCount = $userModel->CM_DashboardCount($user, $role, $selectedMonth, $selectedBranch, $selectedCluster, $selectedZone);
       
        if ($CM_DashboardCount) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'CM Dashboard Count.',
                'data' => $CM_DashboardCount
            ], 200);
        } else {
            log_message('error', 'Failed to give CM Dashboard Count: ' . json_encode($CM_DashboardCount, JSON_PRETTY_PRINT));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to retrieve CM Dashboard Count.',
                'data' => $CM_DashboardCount
            ], 500);
        }
    }

    public function getZoneClusterBranchesTree()
    {
        $userModel = new UserModel();
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $zone_id = $this->request->getPost('zone_id');

        $zonalTree = $userModel->getZoneClusterBranchesTree($zone_id);
        if ($zonalTree) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'Zonal Tree.',
                'data' => $zonalTree
            ], 200);
        } else {
            log_message('error', 'Failed to give zonal Tree: ' . json_encode($zonalTree, JSON_PRETTY_PRINT));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to retrieve zonal tree.',
                'data' => $zonalTree
            ], 500);
        }
    }

    public function addBranchToCluster()
    {
        $userModel = new UserModel();
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $cluster_id = $this->request->getPost('cluster_id');
        $branch_id = $this->request->getPost('branch_id');
        $data = [
            'cluster_id' => $cluster_id,
            'branch_id' => $branch_id,
            'created_by' => $user, 
            'createdDTM' => date('Y-m-d')
        ];

        $zonalTree = $userModel->addBranchToCluster($data, $cluster_id, $branch_id);

        if ($zonalTree) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'Branch added to cluster.',
                'data' => $zonalTree
            ], 200);
        } else {
            log_message('error', 'Failed to add branch to cluster: ' . json_encode($zonalTree, JSON_PRETTY_PRINT));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to add branch to cluster.',
                'data' => $zonalTree
            ], 500);
        }
    }

    public function getUserZones()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $userDetails->role;
      
        $users = new UserModel();
        
        $zoneList = $users->getUserZones($user, $role);
        // echo "<pre>";
        // print_r($zoneList);die();
        if ($zoneList) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User zone list.',
                'data' => $zoneList
            ], 200);
        } else {
            log_message('error', 'Failed to  give users cluster list ' . json_encode($zoneList));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.' . $user
            ], 500);
        }
    }
    public function getUserBranchClusterZoneList()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $userDetails->role;
        $users = new UserModel();
        log_message('error', 'Controller - User: ' . $user . ', Role: ' . $role);
        $zoneList = $users->getUserBranchClusterZoneList($user, $userDetails->role);

        log_message('error', 'User Details: ' . json_encode($userDetails));  // Log user details for debugging
        log_message('error', 'role 22 : ' . $role);  // Log the role to confirm it's received correctly
        if ($zoneList) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User zone list.',
                'data' => $zoneList
            ], 200);
        } else {
            log_message('error', 'Failed to  give users cluster list ' . json_encode($zoneList));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.' . $user
            ], 500);
        }
    }

    public function addClusterToZone()
    {
        $userDetails = $this->validateAuthorization();
        log_message('error', 'User Details: ' . json_encode($userDetails));  // Log user details for debugging

        $user = $userDetails->emp_code;
        $role = $userDetails->role;
        $users = new UserModel();

        // Corrected the parameter name to 'cluster_id'
        $cluster_id = $this->request->getPost('cluster_id');
        log_message('error', 'Cluster ID: ' . $cluster_id);

        $zone_id = $this->request->getPost('zone_id');

        $data = [
            'zone_id' => $zone_id,
            'cluster_id' => $cluster_id,
            'createdDTM' => date('Y-m-d')
        ];

        $addCluster = $users->addClusterToZone($data, $zone_id, $cluster_id);
        if ($addCluster) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'Cluster Mapped successfully.',
                'data' => $addCluster
            ], 200);
        } else {
            log_message('error', 'Failed to add cluster to zone ' . json_encode($addCluster));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.' . $user
            ], 500);
        }
    }

    public function getClusterBranchList()
    {
        $userDetails = $this->validateAuthorization();
        log_message('error', 'User Details: ' . json_encode($userDetails));  // Log user details for debugging

        $user = $userDetails->emp_code;
        $role = $userDetails->role;
        $users = new UserModel();

        // Corrected the parameter name to 'cluster_id'
        $cluster_id = $this->request->getPost('cluster_id');
        log_message('error', 'Cluster ID: ' . $cluster_id);  // Log the cluster_id to confirm it's received correctly

        $branchList = $users->getClusterBranchList($cluster_id);
        log_message('error', 'Branch List: ' . json_encode($branchList));  // Log the result of the query

        if ($branchList) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User Branch list.',
                'data' => $branchList
            ], 200);
        } else {
            log_message('error', 'Failed to give users cluster list ' . json_encode($branchList));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.' . $user
            ], 500);
        }
    }

    public function getZoneClusterList()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $userDetails->role;
        $users = new UserModel();

        $zone_id = $this->request->getPost('zone_id');

        $clusterList = $users->getZoneClusterList($zone_id);
        log_message('error', 'Cluster List: ' . json_encode($clusterList));  // Log the result of the query

        if ($clusterList) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User Cluster list.',
                'data' => $clusterList
            ], 200);
        } else {
            log_message('error', 'Failed to give users cluster list ' . json_encode($clusterList));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.' . $user
            ], 500);
        }
    }

    public function getCMclusterList()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $userDetails->role;
        $users = new UserModel();
        $clusterList = $users->getCMclusterList($user, $role);

        if ($clusterList) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User clusterlist.',
                'data' => $clusterList
            ], 200);
        } else {
            log_message('error', 'Failed to  give users cluster list ' . json_encode($clusterList));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed.' . $user
            ], 500);
        }
    } 

    public function addRoleToEmp()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $role = $this->request->getPost('role');
        $emp_code = $this->request->getPost('emp_code');

        $users = new UserModel();
        $data = [
            'emp_code' => $emp_code,
            'role' => $role,
            'createdDTM' => date('Y-m-d')
        ];

        $addRole = $users->addRoleToEmp($data, $emp_code);

        if ($addRole) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User role added or updated successfully.',
                'data' => $data
            ], 200);
        } else {
            log_message('error', 'Failed to add or update user role: ' . json_encode($data));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to add or update user role.'
            ], 500);
        }
    }


    public function addBranchOrClusterToEmp()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;

        $userModel = new UserModel();
        $deptModel = new DeptModel();

        $cluster_id = $this->request->getPost('cluster');
        $emp_code = $this->request->getPost('emp_code');
        $branch_id = $this->request->getPost('branch_id');
        $zone_id = $this->request->getPost('zone_id');
        $branch = $area_id = $area = $cluster = $zone = '';

        if ($cluster_id > 0) {
            $clusterDetails = $deptModel->getClusterDetailsById($cluster_id);
            if (!empty($clusterDetails)) {
                $cluster = $clusterDetails[0]['cluster'];
            }
        }

        if ($branch_id > 0) {
            $branchDetails = $deptModel->getBranchDetailsById($branch_id);
            if (!empty($branchDetails)) {
                $branch = $branchDetails[0]['branch'];
            }
        }

        if ($zone_id > 0) {
            $zoneDetails = $deptModel->getZoneDetailsById($zone_id);
            if (!empty($zoneDetails)) {
                $zone = $zoneDetails[0]['zone'];
            }
        }

        $data = [
            'emp_code' => $emp_code,
            'cluster_id' => $cluster_id,
            'cluster' => $cluster,
            'branch_id' => $branch_id,
            'branch' => $branch,
            'zone_id' => $zone_id,
            'zone' => $zone,
            'created_by' => $user,
            'createdDTM' => date('Y-m-d')
        ];

        $update = $userModel->addBranchOrClusterToEmp($data, $emp_code, $branch_id, $cluster_id, $zone_id);

        if ($update) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User data added or updated successfully: ' . $branch .  ' - ' . $cluster,
            ], 200);
        } else {
            log_message('error', 'Failed to add or update user area: ' . json_encode($data));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to add or update user area.'
            ], 500);
        }
    }


    public function addAreaToEmp()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        $area = $this->request->getPost('area');
        $emp_code = $this->request->getPost('emp_code');

        $users = new UserModel();
        $data = [
            'emp_code' => $emp_code,
            'area_id' => $area,
            'createdDTM' => date('Y-m-d')
        ];

        $addArea = $users->addAreaToEmp($data, $emp_code, $area);

        if ($addArea) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'User area added or updated successfully.',
                'data' => $data
            ], 200);
        } else {
            log_message('error', 'Failed to add or update user area: ' . json_encode($data));
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to add or update user area.'
            ], 500);
        }
    }

    public function getEmpCodes()
    {
        $users = new UserModel;

        // Retrieve only the emp_code column
        $empCodes = $users->select('emp_code')->findAll();

        // Check if any emp_codes were found
        if (empty($empCodes)) {
            return $this->respond(['error' => 'No emp_codes found'], 404);
        }

        return $this->respond(['emp_codes' => $empCodes, 'STATUS' => true], 200);
    }

    public function changeEmpPass()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        // Retrieve posted data
        $bmid = $this->request->getPost('bmid');
        $pass = $this->request->getPost('pass');
        $conf_pass = $this->request->getPost('conf_pass');

        // Validate if the user ID is empty
        if (empty($bmid)) {
            return $this->respond(['STATUS' => false, 'message' => 'User ID is required'], 400);
        }

        // Validate password match
        if ($pass !== $conf_pass) {
            return $this->respond(['STATUS' => false, 'message' => 'Passwords do not match'], 400);
        }

        // Prepare data for update
        $data = [
            'password' => md5($pass) // Consider using password_hash() for better security
        ];

        // Load the model
        $users = new UserModel();

        // Ensure data is not empty before calling update
        if (!empty($data)) {
            // Update the password for the user
            $updateStatus = $users->set($data)->where('emp_code', $bmid)->update();

            // Check if update was successful
            if ($updateStatus) {
                return $this->respond(['STATUS' => true, 'message' => 'Password updated successfully.'], 200);
            } else {
                return $this->respond(['STATUS' => false, 'message' => 'Password update failed.'], 500);
            }
        } else {
            return $this->respond(['STATUS' => false, 'message' => 'No data to update.'], 400);
        }
    }

    public function changeMyPass()
    {
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
        // Retrieve posted data
        $bmid = $this->request->getPost('bmid');
        $pass = $this->request->getPost('pass');
        $conf_pass = $this->request->getPost('conf_pass');

        // Validate if the user ID is empty
        if (empty($bmid) && $bmid != $user) {
            return $this->respond(['STATUS' => false, 'message' => 'User ID is required'], 400);
        }

        // Validate password match
        if ($pass !== $conf_pass) {
            return $this->respond(['STATUS' => false, 'message' => 'Passwords do not match'], 400);
        }

        // Prepare data for update
        $data = [
            'password' => md5($pass) // Consider using password_hash() for better security
        ];

        // Load the model
       //$users = new UserModel();
        $db2 = \Config\Database::connect('secondary');
        $builder = $db2->table('new_emp_master'); 

        // Ensure data is not empty before calling update
        if (!empty($data)) {
            // Update the password for the user
               $updateStatus = $builder->where('emp_code', $bmid)
                            ->update($data);

            // Check if update was successful
            if ($updateStatus) {
                return $this->respond(['STATUS' => true, 'message' => 'Password updated successfully.'], 200);
            } else {
                return $this->respond(['STATUS' => false, 'message' => 'Password update failed.'], 500);
            }
        } else {
            return $this->respond(['STATUS' => false, 'message' => 'No data to update.'], 400);
        }
    }


    public function changeMyPass1()
    {
        // $userDetails = $this->validateAuthorization();
        // $user = $userDetails->emp_code;
        // // Retrieve posted data
        $user = $this->request->getPost('bmid');
         $bmid = $user;
        $pass = $this->request->getPost('pass');
        $conf_pass = $this->request->getPost('conf_pass');
        
        

        // Validate if the user ID is empty
        if (empty($bmid) && $bmid != $user) {
            return $this->respond(['STATUS' => false, 'message' => 'User ID is required'], 400);
        }

        // Validate password match
        if ($pass !== $conf_pass) {
            return $this->respond(['STATUS' => false, 'message' => 'Passwords do not match'], 400);
        }

        // Prepare data for update
        $data = [
            'password' => md5($pass) // Consider using password_hash() for better security
        ];

        // Load the model
       //$users = new UserModel();
        $db2 = \Config\Database::connect('secondary');
        $builder = $db2->table('new_emp_master'); 

        // Ensure data is not empty before calling update
        if (!empty($data)) {
            // Update the password for the user
               $updateStatus = $builder->where('emp_code', $bmid)
                            ->update($data);

            // Check if update was successful
            if ($updateStatus) {
                return $this->respond(['status' => true, 'message' => 'Password updated successfully.'], 200);
            } else {
                return $this->respond(['status' => false, 'message' => 'Password update failed.'], 500);
            }
        } else {
            return $this->respond(['status' => false, 'message' => 'No data to update.'], 400);
        }
    }


    public function getpassword()
{
    $user = $this->request->getPost('user_id');

    if (empty($user)) {
        return $this->respond([
            'STATUS' => false,
            'message' => 'User ID is required'
        ], 400);
    }

    $db2 = \Config\Database::connect('secondary');
    $builder = $db2->table('new_emp_master');

    $result = $builder->select('password')
                      ->where('emp_code', $user)
                      ->get()
                      ->getRow();

    if ($result) {
        return $this->respond([
            'status' => true,
            'password' => $result->password
        ], 200);
    } else {
        return $this->respond([
            'status' => false,
            'message' => 'User not found or password not available.'
        ], 404);
    }
}

    
    ////Anand/////////

//     public function getUsersList()
//     {
//         $authorizationHeader = $this->request->getHeader('Authorization') ? $this->request->getHeader('Authorization')->getValue() : null;

//         //log_message( 'info', 'Authorization header: ' . $authorizationHeader );

//         // Create an instance of JwtService
//         $jwtService = new JwtService();

//         // Validate the token
//         $result = $jwtService->validateToken($authorizationHeader);

//         // Check if there is an error
//         if (isset($result['error'])) {
//             log_message('error', $result['error']);
//             return $this->respond(['error' => $result['error']], $result['STATUS']);
//         }
// $db2 = \Config\Database::connect('secondary');
//         // Get user details from the database
//         $userModel = new UserModel();
//         //$user = $userModel->getUsersList();
        
        
//         $employees = $db2->table('new_emp_master as a')
//                 ->select('a.*, b.role')
//                 ->join('bmcm as b', 'b.emp_code = a.emp_code', 'left')
//                 ->where('a.active', 'Active')
//                 ->orderBy('b.role', 'asc')
//                 ->get()
//                 ->getResultArray();

//             // Gather employee codes for branch list query
//             $empCodes = array_column($employees, 'emp_code');

//             // Get branch details for these employee codes in one query
//             $branchLists = $this->db->table('branchesmapped as bm')
//                 ->select('bm.emp_code, bm.branch_id, b.branch, bm.cluster_id, c.cluster, bm.zone_id,z.zone')
//                 ->join('branches as b', 'bm.branch_id = b.branch_id', 'left')
//                 ->join('clusters as c', 'c.cluster_id = bm.cluster_id', 'left')
//                 ->join('zones as z', 'z.z_id = bm.zone_id', 'left')
//                 ->whereIn('bm.emp_code', $empCodes)
//                 ->get()
//                 ->getResultArray();

//             // Map branches by emp_code for easier assignment
//             $branchesByEmpCode = [];
//             foreach ($branchLists as $branch) {
//                 $branchesByEmpCode[$branch['emp_code']][] = $branch;
//             }

//             // Assign branch lists to corresponding employees
//             foreach ($employees as &$employee) {
//                 $employee['userBranchList'] = $branchesByEmpCode[$employee['emp_code']] ?? [];
//             }
        
        

//         if (!$user) {
//             log_message('error', 'User not found');
//             return $this->respond(['error' => 'User not found'], 404);
//         }

//         // Return the user profile details ( omit sensitive information )
//         unset($user['password']);
//         // Remove password if present
//         return $this->respond(['data' => $user, 'STATUS' => true], 200);
//     }


//////Ram//////////



public function getUsersList()
{
    // Get Authorization header
    $authorizationHeader = $this->request->getHeader('Authorization') 
        ? $this->request->getHeader('Authorization')->getValue() 
        : null;

    // Validate JWT token
    $jwtService = new JwtService();
    $result = $jwtService->validateToken($authorizationHeader);

    if (isset($result['error'])) {
        log_message('error', $result['error']);
        return $this->respond(['error' => $result['error']], $result['STATUS']);
    }

    // Connect to secondary database
    $db2 = \Config\Database::connect('secondary');
    $db = \Config\Database::connect('default');

    // Get employees from new_emp_master
    $employees = $db2->table('new_emp_master as a')
        ->select('a.*, b.role')
        ->join('bmcm as b', 'b.emp_code = a.emp_code', 'left')
        ->where('a.active', 'Active')
        ->orderBy('b.role', 'asc')
        ->get()
        ->getResultArray();

    if (empty($employees)) {
        log_message('error', 'No active users found');
        return $this->respond(['error' => 'No active users found'], 404);
    }

    // Extract emp_codes
    $empCodes = array_column($employees, 'emp_code');

    // Get branches mapped to these employees
    $branchLists = $db->table('branchesmapped as bm')
        ->select('bm.emp_code, bm.branch_id, b.branch, bm.cluster_id, c.cluster, bm.zone_id, z.zone')
        ->join('branches as b', 'bm.branch_id = b.branch_id', 'left')
        ->join('clusters as c', 'c.cluster_id = bm.cluster_id', 'left')
        ->join('zones as z', 'z.z_id = bm.zone_id', 'left')
        ->whereIn('bm.emp_code', $empCodes)
        ->get()
        ->getResultArray();

    // Group branches by emp_code
    $branchesByEmpCode = [];
    foreach ($branchLists as $branch) {
        $branchesByEmpCode[$branch['emp_code']][] = $branch;
    }

    // Attach branch data to each employee
    foreach ($employees as &$employee) {
        $employee['userBranchList'] = $branchesByEmpCode[$employee['emp_code']] ?? [];
        unset($employee['password']); // Just in case
    }

    // Return the employee list with branches
    return $this->respond(['data' => $employees, 'STATUS' => true], 200);
}

    // public function addUser()
    // {
    //     $userDetails = $this->validateAuthorization();
    //     $USER = $userDetails->emp_code;
    //     $userModel = new UserModel();

    //     // Retrieve JSON data
    //     $input = $this->request->getJSON();

    //     // Extract data from JSON input
    //     $fname = $input->fname;
    //     $lname = $input->lname;
    //     $mobile = $input->mobile;
    //     $email = $input->email;
    //     $pass = password_hash('adnet2008', PASSWORD_DEFAULT); // Secure password
    //     $emp_code = $input->emp_code;
    //     $role = $input->role;
    //     $zone_id = $input->zone_id;
    //     $isAdmin = $input->isAdmin;
    //     $branch_ids = $input->branch_id;
    //     $cluster_ids = $input->cluster;

    //     // Ensure branch_ids and cluster_ids are arrays
    //     if (!is_array($branch_ids)) {
    //         $branch_ids = (array) $branch_ids;
    //     }
    //     if (!is_array($cluster_ids)) {
    //         $cluster_ids = (array) $cluster_ids;
    //     }

    //     // Validate input data
    //     if (empty($emp_code)) {
    //         return $this->respond(['STATUS' => false, 'message' => 'emp_code cannot be empty'], 400);
    //     }
    //     log_message('error', ' addBranchOrClusterToEmp  : ' . json_encode($branch_ids));
    //     // Data for user creation
    //     $data = [
    //         'emp_code' => $emp_code,
    //         'fname' => $fname,
    //         'lname' => $lname,
    //         'mobile' => $mobile,
    //         'password' => $pass,
    //         'created_on' => date('Y-m-d H:i:s'),
    //         'created_by' => $USER,
    //         'mail_id' => $email,
    //         'active' => 'Active',
    //         'isAdmin' => $isAdmin
    //     ];

    //     // Proceed with user creation
    //     $s = $userModel->addUser($data, $emp_code);

    //     if ($s) {
    //         // Add role to employee
    //         $info2 = [
    //             'emp_code' => $emp_code,
    //             'role' => $role,
    //             'createdDTM' => date('Y-m-d')
    //         ];
    //         $userModel->addRoleToEmp($info2, $emp_code);
    //         log_message('debug', 'Attempting to add branch/cluster assignment for emp_code: ' . $emp_code);

    //         // Process each branch and cluster combination
    //         foreach ($branch_ids as $branch_id) {
    //             // If no cluster_ids are provided, set cluster_id to null and add the entry
    //             if (empty($cluster_ids)) {
    //                 $branchDetails = $userModel->getBranchDetailsById($branch_id);
    //                 $branchname = $branchDetails->branch;
    //                 $cluster_id = $branchDetails->cluster;
    //                 $info = [
    //                     'emp_code' => $emp_code,
    //                     'zone_id' => !empty($zone_id) ? $zone_id[0]['area'] : null,
    //                     'branch_id' => $branch_id,
    //                     'branch' => !empty($branchname) ? $branchname : null,
    //                     'cluster_id' => $cluster_id, // Set to null if no cluster_ids are provided
    //                     'created_by' => $USER,
    //                     'createdDTM' => date('Y-m-d')
    //                 ];
    //                 $userModel->addBranchOrClusterToEmp($info, $emp_code, $branch_id, null);
    //             } else {
    //                 // If cluster_ids are provided, add an entry for each branch-cluster combination
    //                 foreach ($cluster_ids as $cluster_id) {
    //                     $info = [
    //                         'emp_code' => $emp_code,
    //                         'zone_id' => !empty($zone_id) ? $zone_id[0]['area'] : null,
    //                         'branch_id' => $branch_id,
    //                         'branch' => !empty($branch) ? $branch[0]['branch'] : null,
    //                         'cluster_id' => $cluster_id,
    //                         'created_by' => $USER,
    //                         'createdDTM' => date('Y-m-d')
    //                     ];
    //                     $userModel->addBranchOrClusterToEmp($info, $emp_code, null, $cluster_id);
    //                 }
    //             }
    //         }


    //         return $this->respond(['STATUS' => true, 'message' => 'User added successfully.', 'data' => $s], 200);
    //     } else {
    //         log_message('error', 'Failed to add user: ' . json_encode($data));
    //         return $this->respond(['STATUS' => false, 'message' => 'Failed to add user'], 401);
    //     }
    // }

    public function addUser()
    {
        $userDetails = $this->validateAuthorization();
        $USER = $userDetails->emp_code;
        $userModel = new UserModel();
        // Retrieve JSON data
        $input = $this->request->getJSON();
        // Extract data from JSON input
        $fname = $input->fname;
        $lname = $input->lname;
        $mobile = $input->mobile;
        $email = $input->email;
        $pass = md5('adnet2008'); // MD5 hashing// Secure password
        $emp_code = $input->emp_code;
        $role = $input->role;
        $zone_ids = $input->zone_id;
        $branch_ids = $input->branch_id;
        $cluster_ids = $input->cluster_id;
        // Ensure branch_ids, cluster_ids, and zone_ids are arrays
        $branch_ids = (array) $branch_ids;
        $cluster_ids = (array) $cluster_ids;
        $zone_ids = (array) $zone_ids;
        // Validate input data
        if (empty($emp_code)) {
            return $this->respond(['STATUS' => false, 'message' => 'emp_code cannot be empty'], 400);
        }
        // Data for user creation
        $data = [
            'emp_code' => $emp_code,
            'fname' => $fname,
            'lname' => $lname,
            'mobile' => $mobile,
            'password' => $pass,
            'created_on' => date('Y-m-d H:i:s'),
            'created_by' => $USER,
            'mail_id' => $email,
            'validity' => date('Y-m-d', strtotime('+90 day')), // Set validity to 1 year from now
            'active' => 'Active',
        ];
        // Proceed with user creation
        $s = $userModel->addUser($data, $emp_code);

        if ($s) {
            // Add role to employee
            $info2 = [
                'emp_code' => $emp_code,
                'role' => $role,
                'createdDTM' => date('Y-m-d')
            ];
            $userModel->addRoleToEmp($info2, $emp_code);
            log_message('debug', 'Attempting to add branch/cluster assignment for emp_code: ' . $emp_code);

            // Add branches
            foreach ($branch_ids as $branch_id) {
                $branchDetails = $userModel->getBranchDetailsById($branch_id);

                if ($branchDetails) {
                    $branch = $branchDetails['branch']; // Access data from the result array
                    $info = [
                        'emp_code' => $emp_code,
                        'branch_id' => $branch_id,
                        'branch' => $branch,
                        'created_by' => $USER,
                        'createdDTM' => date('Y-m-d')
                    ];
                    $userModel->addBranchOrClusterToEmp($info, $emp_code, $branch_id, null, null);
                } else {
                    log_message('error', "No details found for branch_id: $branch_id");
                }
            }

            // Add clusters
            foreach ($cluster_ids as $cluster_id) {
                $clusterDetails = $userModel->getClusterDetailsById($cluster_id);

                if ($clusterDetails) {
                    $cluster = $clusterDetails['cluster']; // Access data from the result array
                    $info = [
                        'emp_code' => $emp_code,
                        'cluster_id' => $cluster_id,
                        'cluster' => $cluster,
                        'created_by' => $USER,
                        'createdDTM' => date('Y-m-d')
                    ];
                    $userModel->addBranchOrClusterToEmp($info, $emp_code, null, $cluster_id,  null);
                } else {
                    log_message('error', "No details found for cluster_id: $cluster_id");
                }
            }

            foreach ($zone_ids as $zone_id) {
                $zoneDetails = $userModel->getZoneDetailsById($zone_id);

                if ($zoneDetails) {
                    $zone = $zoneDetails['zone']; // Access data from the result array
                    $info = [
                        'emp_code' => $emp_code,
                        'zone_id' => $zone_id,
                        'zone' => $zone,
                        'created_by' => $USER,
                        'createdDTM' => date('Y-m-d')
                    ];
                    $userModel->addBranchOrClusterToEmp($info, $emp_code, null,  null, $zone_id);
                } else {
                    log_message('error', "No details found for , zone_id: , $zone_id");
                }
            }

            // Respond with success
            return $this->respond(['STATUS' => true, 'message' => 'User added successfully.', 'data' => $s], 200);
        } else {
            log_message('error', 'Failed to add user: ' . json_encode($data));
            return $this->respond(['STATUS' => false, 'message' => 'Failed to add user'], 401);
        }
    }




    // public function addUser()
    // {
    //     $userDetails = $this->validateAuthorization();
    //     $USER = $userDetails->emp_code ;
    //     $userModel = new UserModel();
    //     $fname = $this->request->getPost( 'fname' );
    //     $lname = $this->request->getPost( 'lname' );
    //     $mobile = $this->request->getPost( 'mobile' );
    //     $email = $this->request->getPost( 'email' );
    //     $pass = md5('adnet2008');
    //     $emp_code = $this->request->getPost( 'emp_code' );
    //      $role = $this->request->getPost( 'role' ) ;
    //     $area_id = $this->request->getPost( 'area_id' ) ;
    //     $branch_id = $this->request->getPost( 'BRANCH' ) ;
    //     $cluster_id = $this->request->getPost( 'cluster_id' ) ;
    //     $isAdmin = $this->request->getPost( 'isAdmin' ) ;
    //     if(empty($cluster_id)){
    //         $cluster_id = 0;
    //     }
    //     // Validate USER_ID
    //     if ( empty( $emp_code ) ) {
    //         return $this->respond( [ 'STATUS' => false, 'message' => 'emp_code cannot be empty' ], 400 );
    //     }

    //     $data = [
    //         'emp_code' => $emp_code, // Ensure this is correctly set
    //         'fname' => $fname,
    //         'lname' => $lname,
    //         'mobile'=> $mobile,
    //         'password' => $pass,
    //         'created_on' => date( 'Y-m-d H:i:s' ),
    //         'created_by' => $USER,
    //         'mail_id' => $email,
    //         'active' => 'Active', // Added comma here
    //         'isAdmin' => $isAdmin
    //     ];


    //     $info2 = [
    //         'emp_code' => $emp_code,
    //         'role' => $role,
    //         'createdDTM' => date('Y-m-d')
    //     ];
    //     $a = $userModel->getAreaDetailsById($area_id);
    //     $b = $userModel->getBranchDetailsById($branch_id);
    //     $area = !empty($a) ? $a[0]['area'] : null;
    //     $branch = !empty($b) ? $b[0]['branch'] : null;
    //     $info = [
    //         'emp_code' => $emp_code,
    //         'area_id' => $area_id,
    //         'area'=> $area,
    //         'branch_id' => $branch_id,
    //         'branch' => $branch,
    //         'cluster_id' => $cluster_id,
    //         'created_by' => $USER,
    //         'createdDTM' => date('Y-m-d')
    //     ];

    //     if ( empty( $emp_code ) ) {
    //         return $this->respond( [ 'STATUS' => false, 'message' => 'emp_code cannot be empty' ], 400 );
    //     }

    //      $s = $userModel->addUser($data, $emp_code);

    //     if ( $s ) {
    //          $addRole = $userModel->addRoleToEmp($info2, $emp_code);
    //        $addBranchAraaToEmp = $userModel->addBranchOrClusterToEmp($info, $emp_code, $branch_id, $cluster_id);
    //         return $this->respond( [ 'STATUS' => true, 'message' => 'User added successfully.', 'data' => $s ], 200 );
    //     } else {
    //         // Log any additional error information if necessary
    //         log_message( 'error', 'Failed to add user: ' . json_encode( $data ) );
    //         return $this->respond( [ 'STATUS' => false, 'message' => 'Failed to add user: ' . json_encode( $data ) ], 401 );
    //     }
    // }

    public function getUserBranchList()
    {
        $userDetails = $this->validateAuthorization();

        if (!is_object($userDetails) || !isset($userDetails->emp_code) || !isset($userDetails->role)) {
            return $this->respond(['STATUS' => false, 'message' => 'Invalid authorization'], 401);
        }

        $user = $userDetails->emp_code;
        $role = $userDetails->role;
        $userModel = new UserModel();
        $branchList = $userModel->getUserBranchList($user, $role);

        if ($branchList) {
            return $this->respond(['STATUS' => true, 'message' => 'User Branch List.', 'data' => $branchList], 200);
        } else {
            log_message('error', 'Failed to fetch branch list for user: ' . $user);
            return $this->respond(['STATUS' => false, 'message' => 'Failed to fetch Branch list'], 401);
        }
    }

    public function editUser()
    {
        $userDetails = $this->validateAuthorization();
        $USER = $userDetails->emp_code;

        $userModel = new UserModel();

        // Retrieve form data and fetch the existing user details
        $bmid = $this->request->getPost('emp_code');
       $details = $userModel->getUserDetails($bmid);

        if (!$details) {
            return $this->respond(['error' => 'User not found'], 404);
        }

        $fname = $this->request->getPost('fname') ?: $details->fname;
        $lname = $this->request->getPost('lname') ?: $details->lname;
        $email = $this->request->getPost('email') ?: $details->email;
        $mobile = $this->request->getPost('mobile') ?: $details->mobile;
        $bmid = $this->request->getPost('emp_code') ?: $details->emp_code;
        $role = $this->request->getPost('role') ?: $details->role;
        log_message('error', '  user details ' . $details->emp_code);

        $active = $this->request->getPost('active') ?: $details->active;
        // Validate USER_ID
        if (empty($bmid)) {
            return $this->respond(['STATUS' => false, 'message' => 'emp_code cannot be empty'], 400);
        }

        $data = [
            'emp_code' => $bmid, // Ensure this is correctly set
            'fname' => $fname,
            'lname' => $lname,
            'mobile' => $mobile,
            'active' => $active,
            'mail_id' => $email,
        ];
        $info2 = [
            'role' => $role,
            'emp_code' => $bmid,
            'createdDTM' => date('Y-m-d')
        ];

        // Call the UserModel to update the user
        $s = $userModel->editUser($data, $bmid);


        if ($s) {
            $updateRole = $userModel->addRoleToEmp($info2, $bmid);
            return $this->respond(['STATUS' => true, 'message' => 'User updated successfully.', 'data' => $s], 200);
        } else {
            log_message('error', 'Failed to update user. ' . $bmid);

            return $this->respond(['STATUS' => false, 'message' => 'Failed to update user.'], 404);
        }
    }

    public function resetPass()
    {
        $userDetails = $this->validateAuthorization();
        $USER = $userDetails->emp_code;


        // Retrieve posted data
        $bmid = $this->request->getPost('emp_code');
        $pass = 'Pass@1981#';
        // Validate if the user ID is empty
        if (empty($bmid)) {
            return $this->respond(['STATUS' => false, 'message' => 'User ID is required'], 400);
        }

        // Prepare data for update
        $data = [
            'password' => md5($pass), // Consider using password_hash() for better security
            'validity' => date('Y-m-d', strtotime('+90 days')),
            'modified_on' => date('Y-m-d H:i:s'),
            'modified_by' => $USER
        ];

        // Load the model
        $users = new UserModel();

        // Ensure data is not empty before calling update
        if (!empty($data)) {
            // Update the password for the user
            $updateStatus = $users->set($data)->where('emp_code', $bmid)->update();

            // Check if update was successful
            if ($updateStatus) {
                return $this->respond(['STATUS' => true, 'message' => 'Password reset successfully.'], 200);
            } else {
                return $this->respond(['STATUS' => false, 'message' => 'Password reset failed.'], 500);
            }
        } else {
            return $this->respond(['STATUS' => false, 'message' => 'No data to update.'], 400);
        }
    }

    private function validateAuthorization()
    {
        if (!class_exists('App\Services\JwtService')) {
            log_message('error', 'JwtService class not found');
            return $this->respond(['error' => 'JwtService class not found'], 500);
        }

        // Get the Authorization header and log it using null coalescing operator
        $authorizationHeader = $this->request->getHeader('Authorization')?->getValue();
        log_message('info', "Authorization header: {$authorizationHeader}");

        try {
            // Create an instance of JwtService and validate the token
            $jwtService = new JwtService();
            $result = $jwtService->validateToken($authorizationHeader);

            // Handle token validation errors 
            if (isset($result['error'])) {
                log_message('error', $result['error']);
                return $this->respond(['error' => $result['error']], $result['status'] ?? 401);
            }

            // Extract the decoded token and get the USER-ID
            return $result['data'] ?? null;
        } catch (\Exception $e) {
            log_message('error', "JWT validation failed: {$e->getMessage()}");
            return $this->respond(['error' => 'Invalid or expired token'], 401);
        }
    }

    public function deleteBranchOrClusterFromEmp(){
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
 
        $emp_code = $this->request->getPost('emp_code');
        $branch_id = $this->request->getPost('branch_id');
        $cluster_id = $this->request->getPost('cluster');
        $zone_id = $this->request->getPost('zone_id');
 
        $users = new UserModel();
       
        $deleteBranchOrCluster = $users->deleteBranchOrClusterFromEmp($emp_code, $branch_id, $cluster_id, $zone_id);
 
        if ($deleteBranchOrCluster) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'Branch or Cluster deleted successfully.',
                'data' => null
            ], 200);
        } else {
           
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to delete branch or cluster.'
            ], 500);
        }
    }


    public function removeBranchFromCluster(){
 
        $userDetails = $this->validateAuthorization();
        $user = $userDetails->emp_code;
 
        $cluster_id = $this->request->getPost('cluster_id');
        $branch_id = $this->request->getPost('branch_id');
 
        $users = new UserModel();
       
        $removeBranchFromCluster = $users->removeBranchFromCluster($cluster_id, $branch_id);
 
        if ($removeBranchFromCluster) {
            return $this->respond([
                'STATUS' => true,
                'message' => 'Branch removed from cluster successfully.',
                'data' => null
            ], 200);
        } else {
           
            return $this->respond([
                'STATUS' => false,
                'message' => 'Failed to remove branch from cluster.'
            ], 500);
        }
 
 
    }


    public function getUsers()
{
    // Get Authorization header
    $authorizationHeader = $this->request->getHeader('Authorization') 
        ? $this->request->getHeader('Authorization')->getValue() 
        : null;

    // Validate JWT token
    $jwtService = new JwtService();
    $result = $jwtService->validateToken($authorizationHeader);

    if (isset($result['error'])) {
        log_message('error', $result['error']);
        return $this->respond(['error' => $result['error']], $result['STATUS']);
    }

    // Connect to secondary database
    $db2 = \Config\Database::connect('secondary');
    $db = \Config\Database::connect('default');

    // Get employees from new_emp_master
    $employees = $db2->table('new_emp_master as a')
        ->select('a.*, b.role')
        ->join('bmcm as b', 'b.emp_code = a.emp_code', 'left')
        ->where('a.active', 'Active')
        ->orderBy('b.role', 'asc')
        ->get()
        ->getResultArray();

    if (empty($employees)) {
        log_message('error', 'No active users found');
        return $this->respond(['error' => 'No active users found'], 404);
    }

    // Extract emp_codes
    $empCodes = array_column($employees, 'emp_code');

    // Get branches mapped to these employees
    $branchLists = $db->table('branchesmapped as bm')
        ->select('bm.emp_code, bm.branch_id, b.branch, bm.cluster_id, c.cluster, bm.zone_id, z.zone')
        ->join('branches as b', 'bm.branch_id = b.branch_id', 'left')
        ->join('clusters as c', 'c.cluster_id = bm.cluster_id', 'left')
        ->join('zones as z', 'z.z_id = bm.zone_id', 'left')
        ->whereIn('bm.emp_code', $empCodes)
        ->get()
        ->getResultArray();

    // Group branches by emp_code
    $branchesByEmpCode = [];
    foreach ($branchLists as $branch) {
        $branchesByEmpCode[$branch['emp_code']][] = $branch;
    }

    // Attach branch data to each employee
    foreach ($employees as &$employee) {
        $employee['userBranchList'] = $branchesByEmpCode[$employee['emp_code']] ?? [];
        unset($employee['password']); // Just in case
    }

    // Return the employee list with branches
    return $this->respond(['data' => $employees, 'STATUS' => true], 200);
}
 

    
   
}

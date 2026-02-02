<?php


namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkBranchwiseBudgetModel;
use App\Services\JwtService;

class HkBudgets extends BaseController
{
     use ResponseTrait;


     private $jwtService;



     /**
      * Get all branch budgets
      */
     public function getAllBranchBudgets()
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $budgets = $hkBudgetModel->getAllBudgets();

               return $this->respond([
                    'status' => true,
                    'message' => 'Branch budgets retrieved successfully',
                    'data' => $budgets
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve budgets: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Get budget by branch ID
      */
     public function getBranchBudget($branch_id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $budget = $hkBudgetModel->getBudgetByBranch($branch_id);

               if (!$budget) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Budget not found for this branch'
                    ], 404);
               }

               return $this->respond([
                    'status' => true,
                    'message' => 'Branch budget retrieved successfully',
                    'data' => $budget
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve budget: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Get budgets by cluster ID
      */
     public function getBudgetsByCluster($cluster_id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $budgets = $hkBudgetModel->getBudgetByCluster($cluster_id);

               return $this->respond([
                    'status' => true,
                    'message' => 'Cluster budgets retrieved successfully',
                    'data' => $budgets
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve budgets: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Create new branch budget
      */
     public function createBranchBudget()
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (empty($requestData->branch_id) || !isset($requestData->budget)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Branch ID and budget are required'
               ], 400);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               // Check if budget already exists for this branch
               $existingBudget = $hkBudgetModel->getBudgetByBranch($requestData->branch_id);

               if ($existingBudget) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Budget already exists for this branch. Use update instead.'
                    ], 409);
               }

               // Get cluster_id from branch
               $defaultDB = \Config\Database::connect('default');
               $clusterInfo = $defaultDB->table('clusters')
                    ->select('cluster_id')
                    ->where("FIND_IN_SET('{$requestData->branch_id}', branches) !=", 0)
                    ->get()
                    ->getRowArray();

               $budgetData = [
                    'branch_id' => $requestData->branch_id,
                    'cluster_id' => $clusterInfo['cluster_id'] ?? null,
                    'budget' => $requestData->budget
               ];

               $budgetId = $hkBudgetModel->insert($budgetData);

               if ($budgetId) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Budget created successfully',
                         'data' => ['id' => $budgetId]
                    ], 201);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to create budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to create budget: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Update branch budget
      */
     public function updateBranchBudget($budget_id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (!isset($requestData->budget)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Budget amount is required'
               ], 400);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               // Check if budget exists
               $existingBudget = $hkBudgetModel->find($budget_id);

               if (!$existingBudget) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Budget not found'
                    ], 404);
               }

               $updateData = [
                    'budget' => $requestData->budget
               ];

               $result = $hkBudgetModel->update($budget_id, $updateData);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Budget updated successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to update budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to update budget: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Update budget by branch ID
      */
     public function updateBudgetByBranch($branch_id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (!isset($requestData->budget)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Budget amount is required'
               ], 400);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $updateData = [
                    'budget' => $requestData->budget
               ];

               $result = $hkBudgetModel->updateBudgetByBranch($branch_id, $updateData);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Budget updated successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to update budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to update budget: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Delete branch budget
      */
     public function deleteBranchBudget($budget_id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               // Check if budget exists
               $existingBudget = $hkBudgetModel->find($budget_id);

               if (!$existingBudget) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Budget not found'
                    ], 404);
               }

               $result = $hkBudgetModel->delete($budget_id);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Budget deleted successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to delete budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to delete budget: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Delete budget by branch ID
      */
     public function deleteBudgetByBranch($branch_id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               // Check if budget exists
               $existingBudget = $hkBudgetModel->getBudgetByBranch($branch_id);

               if (!$existingBudget) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Budget not found for this branch'
                    ], 404);
               }

               $result = $hkBudgetModel->deleteBudgetByBranch($branch_id);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Budget deleted successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to delete budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to delete budget: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Insert or update budget (upsert operation)
      */
     public function upsertBranchBudget()
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (empty($requestData->branch_id) || !isset($requestData->budget)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Branch ID and budget are required'
               ], 400);
          }

          $hkBudgetModel = new HkBranchwiseBudgetModel();

          try {
               $budgetData = [
                    'branch_id' => $requestData->branch_id,
                    'budget' => $requestData->budget
               ];

               $result = $hkBudgetModel->insertOrUpdateBudget($budgetData);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Budget saved successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to save budget'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to save budget: ' . $e->getMessage()
               ], 500);
          }
     }

     // private function validateAuthorization()
     // {
     //      if (!class_exists('App\Services\JwtService')) {
     //           ////log_message( 'error', 'JwtService class not found' );
     //           return $this->respond(['error' => 'JwtService class not found'], 500);
     //      }
     //      // Get the Authorization header and log it
     //      $authorizationHeader = $this->request->header('Authorization')?->getValue();
     //      ////log_message( 'info', 'Authorization header: ' . $authorizationHeader );
     //      // Create an instance of JwtService and validate the token
     //      $jwtService = new JwtService();
     //      $result = $jwtService->validateToken($authorizationHeader);
     //      // Handle token validation errors
     //      if (isset($result['error'])) {
     //           ////log_message( 'error', $result[ 'error' ] );
     //           return $this->respond(['error' => $result['error']], $result['status']);
     //      }
     //      // Extract the decoded token and get the USER-ID
     //      $decodedToken = $result['data'];
     //      return $decodedToken;
     //      // Assuming JWT contains USER-ID
     // }
}

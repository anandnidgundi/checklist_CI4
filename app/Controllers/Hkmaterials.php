<?php


namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkMaterialsModel;
use App\Services\JwtService;

class Hkmaterials extends BaseController
{
     use ResponseTrait;
     private $jwtService;

     /**
      * Get all housekeeping materials
      */
     public function getHkMaterials()
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkMaterialsModel = new HkMaterialsModel();

          try {
               $materials = $hkMaterialsModel->getAllMaterials();

               return $this->respond([
                    'status' => true,
                    'message' => 'HK Materials retrieved successfully',
                    'data' => $materials
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve materials: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Get material by ID
      */
     public function getHkMaterialById($id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkMaterialsModel = new HkMaterialsModel();

          try {
               $material = $hkMaterialsModel->getMaterialById($id);

               if (!$material) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Material not found'
                    ], 404);
               }

               return $this->respond([
                    'status' => true,
                    'message' => 'Material retrieved successfully',
                    'data' => $material
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to retrieve material: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Create new housekeeping material
      */
     public function createHkMaterial()
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (empty($requestData->hk_material_name)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Material name is required'
               ], 400);
          }

          $hkMaterialsModel = new HkMaterialsModel();

          try {
               // Check if material already exists
               // check existence by 'name' column in the underlying `hk_items` table
               $existingMaterial = $hkMaterialsModel->where('name', $requestData->hk_material_name)->first();

               if ($existingMaterial) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Material with this name already exists'
                    ], 409);
               }

               // keep legacy payload key; model maps `hk_material_name` -> `name` on insert
               $materialData = [
                    'hk_material_name' => $requestData->hk_material_name
               ];

               $materialId = $hkMaterialsModel->insert($materialData);

               if ($materialId) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Material created successfully',
                         'data' => ['id' => $materialId]
                    ], 201);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to create material'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to create material: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Update housekeeping material
      */
     public function updateHkMaterial($id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          // Validate required fields
          if (empty($requestData->hk_material_name)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Material name is required'
               ], 400);
          }

          $hkMaterialsModel = new HkMaterialsModel();

          try {
               // Check if material exists
               $existingMaterial = $hkMaterialsModel->getMaterialById($id);

               if (!$existingMaterial) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Material not found'
                    ], 404);
               }

               // Check if another material with the same name exists
               $duplicateMaterial = $hkMaterialsModel
                    ->where('name', $requestData->hk_material_name)
                    ->where('id !=', $id)
                    ->first();

               if ($duplicateMaterial) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Another material with this name already exists'
                    ], 409);
               }

               $materialData = [
                    'hk_material_name' => $requestData->hk_material_name
               ];

               $result = $hkMaterialsModel->update($id, $materialData);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Material updated successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to update material'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to update material: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Delete housekeeping material
      */
     public function deleteHkMaterial($id)
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $hkMaterialsModel = new HkMaterialsModel();

          try {
               // Check if material exists
               $existingMaterial = $hkMaterialsModel->getMaterialById($id);

               if (!$existingMaterial) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Material not found'
                    ], 404);
               }

               // Check if material is being used in any requirements (legacy hk_details stores material name)
               $db = \Config\Database::connect();
               $usageCount = $db->table('hk_details')
                    ->where('hk_material', $existingMaterial['hk_material_name'])
                    ->countAllResults();

               if ($usageCount > 0) {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Cannot delete material. It is being used in ' . $usageCount . ' requirement(s)'
                    ], 409);
               }

               // Also check new-style references that use hk_item_id
               $tablesToCheck = [
                    'hk_branch_ideal_qty' => 'hk_item_id',
                    'hk_opening_stock' => 'hk_item_id',
                    'hk_stock_received' => 'hk_item_id',
                    'hk_consumptions' => 'hk_item_id',
                    'hk_stock_balances' => 'hk_item_id'
               ];

               foreach ($tablesToCheck as $table => $col) {
                    $count = (int) $db->table($table)->where($col, $id)->countAllResults(false);
                    if ($count > 0) {
                         return $this->respond([
                              'status' => false,
                              'message' => 'Cannot delete material. It is referenced in ' . $table . ' (' . $count . ')'
                         ], 409);
                    }
               }

               $result = $hkMaterialsModel->delete($id);

               if ($result) {
                    return $this->respond([
                         'status' => true,
                         'message' => 'Material deleted successfully'
                    ], 200);
               } else {
                    return $this->respond([
                         'status' => false,
                         'message' => 'Failed to delete material'
                    ], 500);
               }
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to delete material: ' . $e->getMessage()
               ], 500);
          }
     }

     /**
      * Search housekeeping materials
      */
     public function searchHkMaterials()
     {
          $userDetails = $this->validateAuthorizationNew();
          if (!$userDetails) {
               return $this->respond(['status' => false, 'message' => 'Unauthorized access'], 401);
          }

          $requestData = $this->request->getJSON();

          if (empty($requestData->search)) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Search term is required'
               ], 400);
          }

          $hkMaterialsModel = new HkMaterialsModel();

          try {
               $materials = $hkMaterialsModel->searchMaterials($requestData->search);

               return $this->respond([
                    'status' => true,
                    'message' => 'Materials retrieved successfully',
                    'data' => $materials
               ], 200);
          } catch (\Exception $e) {
               return $this->respond([
                    'status' => false,
                    'message' => 'Failed to search materials: ' . $e->getMessage()
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

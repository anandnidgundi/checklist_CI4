<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\HkBranchIdealQtyModel;

class HkBranchIdealQtyController extends BaseController
{
     use ResponseTrait;

     public function getByBranch($branch_id)
     {
          $this->validateAuthorizationNew();
          $m = new HkBranchIdealQtyModel();
          $rows = $m->getByBranch((int)$branch_id);
          return $this->respond(['data' => $rows], 200);
     }

     public function getByItem($hk_item_id)
     {
          $this->validateAuthorizationNew();
          $m = new HkBranchIdealQtyModel();
          $rows = $m->getByItem((int)$hk_item_id);
          return $this->respond(['data' => $rows], 200);
     }

     public function setIdealQty()
     {
          $this->validateAuthorizationNew();
          $input = $this->request->getJSON(true) ?: [];

          $branch_id = isset($input['branch_id']) ? (int)$input['branch_id'] : null;
          $hk_item_id = isset($input['hk_item_id']) ? (int)$input['hk_item_id'] : null;
          $ideal_qty = isset($input['ideal_qty']) ? floatval($input['ideal_qty']) : null;

          if ($branch_id <= 0 || $hk_item_id <= 0 || $ideal_qty === null) {
               return $this->respond(['message' => 'branch_id, hk_item_id and ideal_qty are required'], 400);
          }

          $db = \Config\Database::connect();

          // Validate branch exists in branches table (prevent FK errors)
          $travelappDb = \Config\Database::connect('secondary');
          $branchExists = (bool)$travelappDb->table('branches')->where('id', $branch_id)->get()->getRow();
          if (!$branchExists) {
               return $this->respond(['message' => "branch_id {$branch_id} not found"], 400);
          }

          // Validate item exists
          $itemExists = (bool)$db->table('hk_items')->where('id', $hk_item_id)->get()->getRow();
          if (!$itemExists) {
               return $this->respond(['message' => "hk_item_id {$hk_item_id} not found"], 400);
          }

          $m = new HkBranchIdealQtyModel();
          try {
               $result = $m->upsert($branch_id, $hk_item_id, $ideal_qty);
               return $this->respond(['message' => 'Saved', 'result' => $result], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     public function delete($id)
     {
          $this->validateAuthorizationNew();
          $m = new HkBranchIdealQtyModel();
          $existing = $m->find((int)$id);
          if (!$existing) return $this->respond(['message' => 'Not found'], 404);

          try {
               $m->deleteById((int)$id);
               return $this->respond(['message' => 'Deleted'], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }

     // Admin: list orphaned ideal qty records whose branch_id does not exist in branches table
     public function listOrphans()
     {
          $this->validateAuthorizationNew();
          $db = \Config\Database::connect();
          $sql = "SELECT h.id, h.branch_id, h.hk_item_id, h.ideal_qty FROM hk_branch_ideal_qty h LEFT JOIN branches b ON b.branch_id = h.branch_id WHERE b.branch_id IS NULL";
          try {
               $rows = $db->query($sql)->getResultArray();
               return $this->respond(['data' => $rows], 200);
          } catch (\Exception $e) {
               return $this->respond(['message' => 'Failed: ' . $e->getMessage()], 500);
          }
     }
}

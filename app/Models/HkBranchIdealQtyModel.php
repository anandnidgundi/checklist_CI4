<?php

namespace App\Models;

use CodeIgniter\Model;

class HkBranchIdealQtyModel extends Model
{
     protected $table = 'hk_branch_ideal_qty';
     protected $primaryKey = 'id';
     protected $allowedFields = ['branch_id', 'hk_item_id', 'ideal_qty', 'created_at'];
     protected $returnType = 'array';
     protected $useTimestamps = false;

     public function getByBranch(int $branch_id): array
     {
          return $this->where('branch_id', $branch_id)->orderBy('hk_item_id', 'ASC')->findAll();
     }

     public function getByItem(int $hk_item_id): array
     {
          return $this->where('hk_item_id', $hk_item_id)->orderBy('branch_id', 'ASC')->findAll();
     }

     public function findForBranchAndItem(int $branch_id, int $hk_item_id)
     {
          return $this->where('branch_id', $branch_id)->where('hk_item_id', $hk_item_id)->first();
     }

     // upsert: create or update by branch+item
     public function upsert(int $branch_id, int $hk_item_id, float $ideal_qty)
     {
          $existing = $this->findForBranchAndItem($branch_id, $hk_item_id);
          $data = ['branch_id' => $branch_id, 'hk_item_id' => $hk_item_id, 'ideal_qty' => $ideal_qty];
          if ($existing) {
               return (bool)$this->update($existing['id'], $data);
          }
          return $this->insert($data);
     }

     public function deleteById(int $id): bool
     {
          return (bool)$this->delete($id);
     }
}

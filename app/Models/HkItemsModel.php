<?php

namespace App\Models;

use CodeIgniter\Model;

class HkItemsModel extends Model
{
     protected $table      = 'hk_items';
     protected $primaryKey = 'id';
     protected $returnType = 'array';
     protected $allowedFields = [
          'name',
          'brand',
          'unit',
          'item_type',
          'created_at',
          'updated_at'
     ];

     // Use CodeIgniter timestamps (if you prefer DB-managed timestamps, set to false)
     protected $useTimestamps = true;
     protected $createdField  = 'created_at';
     protected $updatedField  = 'updated_at';

     // Basic retrieval methods
     public function getAllItems(): array
     {
          return $this->orderBy('name', 'ASC')->findAll();
     }

     public function getItemById(int $id): ?array
     {
          return $this->find($id) ?: null;
     }

     public function findByName(string $name): ?array
     {
          return $this->where('name', $name)->first() ?: null;
     }

     public function search(string $term, int $limit = 50, int $offset = 0): array
     {
          return $this->like('name', $term)
               ->orLike('brand', $term)
               ->orderBy('name', 'ASC')
               ->findAll($limit, $offset);
     }

     // Create and update helpers (return inserted id or boolean)
     public function createItem(array $data)
     {
          return $this->insert($data);
     }

     public function updateItem(int $id, array $data): bool
     {
          return (bool) $this->update($id, $data);
     }

     public function deleteItem(int $id): bool
     {
          return (bool) $this->delete($id);
     }

     // Backwards-compatible method names (if controllers expect "materials" terminology)
     public function getAllMaterials(): array
     {
          return $this->getAllItems();
     }

     public function getMaterialById(int $id): ?array
     {
          return $this->getItemById($id);
     }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class HkMaterialsModel extends Model
{
     // Backwards-compatible adapter that uses the `hk_items` table under the hood
     protected $table = 'hk_items';
     protected $primaryKey = 'id';
     // accept both formats (legacy `hk_material_name` and new `name`) and map internally
     protected $allowedFields = [
          'name',
          'hk_material_name'
     ];

     protected $useTimestamps = false;
     protected $returnType = 'array';

     /**
      * Insert mapping: accept hk_material_name => store in `name`
      */
     public function insert($data = null, bool $returnID = true)
     {
          if (\is_array($data) && isset($data['hk_material_name'])) {
               $data['name'] = $data['hk_material_name'];
               unset($data['hk_material_name']);
          }
          if (\is_object($data) && isset($data->hk_material_name)) {
               $data = (array)$data;
               $data['name'] = $data['hk_material_name'];
               unset($data['hk_material_name']);
          }
          return parent::insert($data, $returnID);
     }

     /**
      * Update mapping: accept hk_material_name => update `name`
      */
     public function update($id = null, $row = null): bool
     {
          if (\is_array($row) && isset($row['hk_material_name'])) {
               $row['name'] = $row['hk_material_name'];
               unset($row['hk_material_name']);
          }
          if (\is_object($row) && isset($row->hk_material_name)) {
               $row = (array)$row;
               $row['name'] = $row['hk_material_name'];
               unset($row['hk_material_name']);
          }
          return parent::update($id, $row);
     }

     /**
      * Get all items and expose legacy key `hk_material_name` for compatibility
      */
     public function getAllMaterials()
     {
          $items = $this->orderBy('name', 'ASC')->findAll();
          foreach ($items as &$item) {
               $item['hk_material_name'] = $item['name'];
          }
          return $items;
     }

     /**
      * Get material by id and add legacy key
      */
     public function getMaterialById($id)
     {
          $item = $this->find($id);
          if ($item) {
               $item['hk_material_name'] = $item['name'];
          }
          return $item;
     }

     /**
      * Search by name (maps legacy search to new name column)
      */
     public function searchMaterials($searchTerm)
     {
          $items = $this->like('name', $searchTerm)->findAll();
          foreach ($items as &$item) {
               $item['hk_material_name'] = $item['name'];
          }
          return $items;
     }
}

<?php

namespace App\Models;

use CodeIgniter\Model;

class FormSubmissionsModel extends Model
{
     protected $table            = 'form_submissions';
     protected $primaryKey       = 'id';
     protected $useAutoIncrement = true;
     protected $returnType       = 'array';
     protected $useSoftDeletes   = false;
     protected $protectFields    = true;

     protected $allowedFields = [
          'submission_uuid',
          'form_id',
          'dept_id',
          'header',
          'status',
          'created_by',
          'created_dtm',
          'updated_dtm',
          'updated_by',
     ];

     protected bool $allowEmptyInserts = false;

     /**
      * Server-side DataTables compatible listing.
      * Returns: ['total' => int, 'filtered' => int, 'rows' => array]
      */
     public function datatableList(int $start, int $length, string $search = '', $orderCol = null, string $orderDir = 'desc', array $columns = []): array
     {
          return $this->datatableListFiltered($start, $length, $search, $orderCol, $orderDir, $columns, null);
     }

     /**
      * DataTables listing with an optional fixed form_id filter.
      */
     public function datatableListFiltered(int $start, int $length, string $search = '', $orderCol = null, string $orderDir = 'desc', array $columns = [], ?int $formId = null): array
     {
          $db = \Config\Database::connect();
          $builder = $db->table($this->table . ' as s');

          if ($formId !== null && $formId > 0) {
               $builder->where('s.form_id', $formId);
          }

          $search = trim((string) $search);
          if ($search !== '') {
               $builder->groupStart()
                    ->like('s.id', $search)
                    ->orLike('s.submission_uuid', $search)
                    ->orLike('s.created_by', $search)
                    ->orLike('s.status', $search)
                    ->orLike('s.form_id', $search)
                    ->orLike('s.dept_id', $search)
                    ->groupEnd();
          }

          $totalBuilder = $db->table($this->table);
          if ($formId !== null && $formId > 0) {
               $totalBuilder->where('form_id', $formId);
          }
          $total = (int) $totalBuilder->countAllResults();

          $filtered = (int) $builder->countAllResults(false);

          $safeDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';
          $orderBy = 's.created_dtm';
          if ($orderCol !== null && isset($columns[(int) $orderCol])) {
               $colName = $columns[(int) $orderCol]['data'] ?? null;
               $allowed = ['id', 'submission_uuid', 'form_id', 'dept_id', 'status', 'created_by', 'created_dtm', 'updated_dtm'];
               if ($colName && in_array($colName, $allowed, true)) {
                    $orderBy = 's.' . $colName;
               }
          }
          $builder->orderBy($orderBy, $safeDir);
          if ($length > 0) {
               $builder->limit($length, max(0, $start));
          }

          $rows = $builder->select('s.*')->get()->getResultArray();
          return ['total' => $total, 'filtered' => $filtered, 'rows' => $rows];
     }
}

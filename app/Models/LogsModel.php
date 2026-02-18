<?php

namespace App\Models;

use CodeIgniter\Model;

class LogsModel extends Model
{
     protected $table = 'logs';
     protected $primaryKey = 'id';
     protected $allowedFields = [
          'uri',
          'method',
          'params',
          'api_key',
          'ip_address',
          'time',
          'rtime',
          'authorized',
          'response_code',
          'action',
          'entity_type',
          'entity_id',
          'user_id'
     ];

     /**
      * Insert a log entry.
      * Accepts partial data; fills sensible defaults where possible.
      */
     public function insertLog(array $data): ?int
     {
          $now = time();
          $payload = [
               // ensure NOT NULL DB columns receive safe defaults when missing
               'uri' => $data['uri'] ?? ($_SERVER['REQUEST_URI'] ?? '/'),
               'method' => $data['method'] ?? ($_SERVER['REQUEST_METHOD'] ?? 'CLI'),
               'params' => isset($data['params']) ? (is_string($data['params']) ? $data['params'] : json_encode($data['params'])) : null,
               'api_key' => $data['api_key'] ?? ($data['apiKey'] ?? ''),
               'ip_address' => $data['ip_address'] ?? ($data['ip'] ?? $_SERVER['REMOTE_ADDR'] ?? '0'),
               'time' => $data['time'] ?? $now,
               'rtime' => isset($data['rtime']) ? (float) $data['rtime'] : (isset($data['rtime_ms']) ? (float) $data['rtime_ms'] : null),
               'authorized' => $data['authorized'] ?? ($data['auth'] ?? 'N'),
               'response_code' => isset($data['response_code']) ? (int) $data['response_code'] : 0,
               'action' => $data['action'] ?? null,
               'entity_type' => $data['entity_type'] ?? null,
               'entity_id' => isset($data['entity_id']) ? (string) $data['entity_id'] : null,
               'user_id' => $data['user_id'] ?? null,
          ];

          $this->insert($payload);
          $id = $this->getInsertID();
          return $id ? (int) $id : null;
     }

     /**
      * Get logs for a specific entity (e.g. form / form_input / bm_task)
      */
     public function getByEntity(string $entityType, string $entityId, int $limit = 100, int $offset = 0)
     {
          return $this->where('entity_type', $entityType)
               ->where('entity_id', (string) $entityId)
               ->orderBy('time', 'DESC')
               ->findAll($limit, $offset);
     }

     /**
      * Get filtered logs with pagination + total count.
      * Supported filters: entity_type, entity_id, action, user_id, response_code, date_from, date_to, q
      */
     public function getFiltered(array $filters = [], int $limit = 100, int $offset = 0)
     {
          $db = \Config\Database::connect();
          $builder = $db->table($this->table);

          if (!empty($filters['entity_type'])) $builder->where('entity_type', $filters['entity_type']);
          if (!empty($filters['entity_id'])) $builder->where('entity_id', (string)$filters['entity_id']);
          if (!empty($filters['action'])) $builder->where('action', $filters['action']);
          if (!empty($filters['user_id'])) $builder->where('user_id', $filters['user_id']);
          if (!empty($filters['response_code'])) $builder->where('response_code', (int)$filters['response_code']);

          if (!empty($filters['date_from'])) {
               $fromTs = is_numeric($filters['date_from']) ? (int)$filters['date_from'] : strtotime($filters['date_from']);
               if ($fromTs !== false) $builder->where('time >=', (int)$fromTs);
          }
          if (!empty($filters['date_to'])) {
               $toTs = is_numeric($filters['date_to']) ? (int)$filters['date_to'] : strtotime($filters['date_to']);
               if ($toTs !== false) {
                    if (strlen((string)$filters['date_to']) === 10) $toTs = strtotime($filters['date_to'] . ' 23:59:59');
                    $builder->where('time <=', (int)$toTs);
               }
          }

          if (!empty($filters['q'])) {
               $q = $filters['q'];
               $builder->groupStart();
               $builder->like('uri', $q);
               $builder->orLike('params', $q);
               $builder->groupEnd();
          }

          $total = (int) $builder->countAllResults(false);
          $rows = $builder->orderBy('time', 'DESC')->limit($limit, $offset)->get()->getResultArray();

          return ['data' => $rows, 'total' => $total];
     }

     public function getRecent(int $limit = 100, int $offset = 0)
     {
          return $this->orderBy('id', 'DESC')->findAll($limit, $offset);
     }
}

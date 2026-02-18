<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddLogsIndexes extends Migration
{
     public function up()
     {
          $db = \Config\Database::connect();
          $forge = \Config\Database::forge();

          // Add optional column to support retention/archival workflows
          if (! $db->fieldExists('archived_at', 'logs')) {
               $forge->addColumn('logs', [
                    'archived_at' => [
                         'type' => 'DATETIME',
                         'null' => true,
                         'comment' => 'When the log was archived (null = active)'
                    ]
               ]);
          }

          // Create indexes (best-effort, ignore if already present)
          try {
               $db->query("CREATE INDEX idx_logs_entity ON `logs` (`entity_type`, `entity_id`)");
          } catch (\Throwable $e) {
               // ignore - index may already exist
               log_message('debug', 'AddLogsIndexes::up idx_logs_entity: ' . $e->getMessage());
          }

          try {
               $db->query("CREATE INDEX idx_logs_time ON `logs` (`time`)");
          } catch (\Throwable $e) {
               log_message('debug', 'AddLogsIndexes::up idx_logs_time: ' . $e->getMessage());
          }

          try {
               $db->query("CREATE INDEX idx_logs_user ON `logs` (`user_id`)");
          } catch (\Throwable $e) {
               log_message('debug', 'AddLogsIndexes::up idx_logs_user: ' . $e->getMessage());
          }
     }

     public function down()
     {
          $db = \Config\Database::connect();
          $forge = \Config\Database::forge();

          try {
               $db->query('DROP INDEX idx_logs_entity ON `logs`');
          } catch (\Throwable $e) {
               log_message('debug', 'AddLogsIndexes::down idx_logs_entity: ' . $e->getMessage());
          }

          try {
               $db->query('DROP INDEX idx_logs_time ON `logs`');
          } catch (\Throwable $e) {
               log_message('debug', 'AddLogsIndexes::down idx_logs_time: ' . $e->getMessage());
          }

          try {
               $db->query('DROP INDEX idx_logs_user ON `logs`');
          } catch (\Throwable $e) {
               log_message('debug', 'AddLogsIndexes::down idx_logs_user: ' . $e->getMessage());
          }

          if ($db->fieldExists('archived_at', 'logs')) {
               $forge->dropColumn('logs', 'archived_at');
          }
     }
}

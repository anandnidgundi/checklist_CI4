<?php

namespace App\Controllers;

use App\Controllers\BaseController;

/**
 * Temporary debugging controller — shows masked DB connection info.
 * Remove this file after verification.
 */
class Debug extends BaseController
{
     public function db()
     {
          $dbConfig = config('Database');
          $group = $dbConfig->defaultGroup ?? 'default';
          $settings = $dbConfig->{$group} ?? $dbConfig->default ?? [];

          $hostname = $settings['hostname'] ?? ($dbConfig->default['hostname'] ?? '');
          $username = $settings['username'] ?? ($dbConfig->default['username'] ?? '');
          $database = $settings['database'] ?? ($dbConfig->default['database'] ?? '');

          // Return masked JSON (do NOT include passwords)
          return $this->response->setJSON([
               'environment' => ENVIRONMENT,
               'db_group'    => $group,
               'hostname'    => $hostname,
               'username'    => $username,
               'database'    => $database,
          ]);
     }
}

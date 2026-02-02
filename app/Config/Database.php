<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
     /**
      * The directory that holds the Migrations and Seeds directories.
      */
     public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;
     /**
      * Lets you choose which connection group to use if no other is specified.
      */
     public string $defaultGroup = 'default'; // Define the default group name
     // Default and Secondary DB group properties
     public array $default = [];
     public array $secondary = [];
     public function __construct()
     {
          parent::__construct();
          // Get the environment variable (development, production, etc.)
          $environment = ENVIRONMENT;
          if ($environment === 'development') {
               // Helper to safely read environment variables with defaults
               $e = function ($name, $default = '') {
                    $val = getenv($name);
                    return $val !== false ? $val : $default;
               };

               // Default database group with safe fallbacks
               $this->default = [
                    'DSN'      => '',
                    'hostname' => $e('database.default.hostname', '127.0.0.1'),
                    'username' => $e('database.default.username', 'root'),
                    'password' => $e('database.default.password', ''),
                    'database' => $e('database.default.database', ''),
                    'DBDriver' => $e('database.default.DBDriver', 'MySQLi'),
                    'DBPrefix' => '',
                    'pConnect' => false,
                    'DBDebug'  => true,
                    'charset'  => 'utf8',
                    'DBCollat' => 'utf8_general_ci',
                    'swapPre'  => '',
                    'encrypt'  => false,
                    'compress' => false,
                    'strictOn' => false,
                    'failover' => [],
                    'port'     => (int) $e('database.default.port', 3306),
               ];

               // ➕ Secondary database group with safe fallbacks
               $this->secondary = [
                    'DSN'      => '',
                    'hostname' => $e('database.secondary.hostname', '127.0.0.1'),
                    'username' => $e('database.secondary.username', 'root'),
                    'password' => $e('database.secondary.password', ''),
                    'database' => $e('database.secondary.database', ''),
                    'DBDriver' => $e('database.secondary.DBDriver', 'MySQLi'),
                    'DBPrefix' => '',
                    'pConnect' => true,
                    'DBDebug'  => true,
                    'charset'  => 'utf8',
                    'DBCollat' => 'utf8_general_ci',
                    'swapPre'  => '',
                    'encrypt'  => false,
                    'compress' => false,
                    'strictOn' => false,
                    'failover' => [],
                    'port'     => (int) $e('database.secondary.port', 3306),
               ];
          }
          // You can add more logic for production or other environments here
     }
}

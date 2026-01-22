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
            // Default database group
            $this->default = [
                'DSN'      => '',
                'hostname' => getenv('database.default.hostname'),
                'username' => getenv('database.default.username'),
                'password' => getenv('database.default.password'),
                'database' => getenv('database.default.database'),
                'DBDriver' => getenv('database.default.DBDriver'),
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
                'port'     => (int) getenv('database.default.port'),
            ];

            // ➕ Secondary database group
            $this->secondary = [
                'DSN'      => '',
                'hostname' => getenv('database.secondary.hostname'),
                'username' => getenv('database.secondary.username'),
                'password' => getenv('database.secondary.password'),
                'database' => getenv('database.secondary.database'),
                'DBDriver' => getenv('database.secondary.DBDriver'),
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
                'port'     => (int) getenv('database.secondary.port'),
            ];
        }

        // You can add more logic for production or other environments here
    }
}

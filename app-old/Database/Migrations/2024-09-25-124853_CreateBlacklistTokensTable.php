<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlacklistTokensTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'token' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'unique' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'expires_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
			'updated_at' => [
'type' => 'DATETIME',
'null' => true
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('blacklisted_tokens');
    }

    public function down()
    {
        $this->forge->dropTable('blacklisted_tokens');
    }
}

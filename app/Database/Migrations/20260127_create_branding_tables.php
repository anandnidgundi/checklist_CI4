<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBrandingTables extends Migration
{
     public function up()
     {
          // branding_checklists
          $this->forge->addField([
               'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'centre_name' => ['type' => 'VARCHAR', 'constraint' => 255],
               'location' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
               'date_of_visit' => ['type' => 'DATE'],
               'audited_by' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
               'branch_manager' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
               'contact' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
               'notes' => ['type' => 'TEXT', 'null' => true],
               'status' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'draft'],
               'created_by' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
               'created_at' => ['type' => 'DATETIME', 'null' => true],
               'updated_at' => ['type' => 'DATETIME', 'null' => true],
          ]);
          $this->forge->addKey('id', true);
          $this->forge->createTable('branding_checklists', true);

          // items
          $this->forge->addField([
               'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'checklist_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
               'section' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
               'item_label' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
               'response' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
               'remarks' => ['type' => 'TEXT', 'null' => true],
               'priority' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
               'created_at' => ['type' => 'DATETIME', 'null' => true],
               'updated_at' => ['type' => 'DATETIME', 'null' => true],
          ]);
          $this->forge->addKey('id', true);
          $this->forge->addKey('checklist_id');
          $this->forge->createTable('branding_checklist_items', true);

          // photos
          $this->forge->addField([
               'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'checklist_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
               'filename' => ['type' => 'VARCHAR', 'constraint' => 255],
               'caption' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
               'created_at' => ['type' => 'DATETIME', 'null' => true],
          ]);
          $this->forge->addKey('id', true);
          $this->forge->addKey('checklist_id');
          $this->forge->createTable('branding_photos', true);

          // actions
          $this->forge->addField([
               'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'checklist_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
               'action_text' => ['type' => 'TEXT'],
               'priority' => ['type' => 'VARCHAR', 'constraint' => 16, 'default' => 'low'],
               'target_date' => ['type' => 'DATE', 'null' => true],
               'assigned_to' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => true],
               'status' => ['type' => 'VARCHAR', 'constraint' => 32, 'default' => 'open'],
               'created_at' => ['type' => 'DATETIME', 'null' => true],
               'updated_at' => ['type' => 'DATETIME', 'null' => true],
          ]);
          $this->forge->addKey('id', true);
          $this->forge->addKey('checklist_id');
          $this->forge->createTable('branding_actions', true);
     }

     public function down()
     {
          $this->forge->dropTable('branding_actions', true);
          $this->forge->dropTable('branding_photos', true);
          $this->forge->dropTable('branding_checklist_items', true);
          $this->forge->dropTable('branding_checklists', true);
     }
}

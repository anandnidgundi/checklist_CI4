<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddBrandingRecordsSections extends Migration
{
     public function up()
     {
          // branding_sections
          $this->forge->addField([
               'section_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'section_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
          ]);
          $this->forge->addKey('section_id', true);
          $this->forge->createTable('branding_sections', true);

          // branding_sub_sections (linked to sections)
          $this->forge->addField([
               'sub_section_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'section_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
               'sub_section_name' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
          ]);
          $this->forge->addKey('sub_section_id', true);
          $this->forge->addKey('section_id');
          $this->forge->createTable('branding_sub_sections', true);

          // branding_checklist_records
          $this->forge->addField([
               'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
               'branding_checklist_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
               'section_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
               'sub_section_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
               'input_name' => ['type' => 'VARCHAR', 'constraint' => 200],
               'input_value' => ['type' => 'VARCHAR', 'constraint' => 200],
               'input_remark' => ['type' => 'TEXT', 'null' => true],
               'created_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
               'created_dtm' => ['type' => 'DATETIME', 'null' => true],
               'updated_by' => ['type' => 'INT', 'constraint' => 11, 'null' => true],
               'updated_dtm' => ['type' => 'DATETIME', 'null' => true],
          ]);
          $this->forge->addKey('id', true);
          $this->forge->addKey('branding_checklist_id');
          $this->forge->createTable('branding_checklist_records', true);

          // Add foreign keys where possible
          $db = \Config\Database::connect();
          try {
               $db->query("ALTER TABLE branding_checklist_records ADD CONSTRAINT fk_bcr_chk FOREIGN KEY (branding_checklist_id) REFERENCES branding_checklists(id) ON DELETE CASCADE");
               $db->query("ALTER TABLE branding_checklist_records ADD CONSTRAINT fk_bcr_section FOREIGN KEY (section_id) REFERENCES branding_sections(section_id) ON DELETE SET NULL");
               $db->query("ALTER TABLE branding_sub_sections ADD CONSTRAINT fk_bsub_section FOREIGN KEY (section_id) REFERENCES branding_sections(section_id) ON DELETE CASCADE");
               $db->query("ALTER TABLE branding_checklist_records ADD CONSTRAINT fk_bcr_sub_section FOREIGN KEY (sub_section_id) REFERENCES branding_sub_sections(sub_section_id) ON DELETE SET NULL");
          } catch (\Exception $e) {
               // ignore if constraints already present
          }
     }

     public function down()
     {
          $this->forge->dropTable('branding_checklist_records', true);
          $this->forge->dropTable('branding_sub_sections', true);
          $this->forge->dropTable('branding_sections', true);
     }
}

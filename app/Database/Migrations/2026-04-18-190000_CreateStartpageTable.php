<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStartpageTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('startpage')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => false,
                'auto_increment' => true,
            ],
            'text' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'image_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 0,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->createTable('startpage', true);
    }

    public function down()
    {
        $this->forge->dropTable('startpage', true);
    }
}


<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSwedishTextToProjects extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('text_sv', 'projects')) {
            return;
        }

        $this->forge->addColumn('projects', [
            'text_sv' => [
                'type' => 'TEXT',
                'null' => true,
                'after' => 'text',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('text_sv', 'projects')) {
            $this->forge->dropColumn('projects', 'text_sv');
        }
    }
}


<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSeoDescriptionToProjects extends Migration
{
    public function up()
    {
        if ($this->db->fieldExists('seo_description', 'projects')) {
            return;
        }

        $this->forge->addColumn('projects', [
            'seo_description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
                'default'    => null,
                'after'      => 'description',
            ],
        ]);
    }

    public function down()
    {
        if ($this->db->fieldExists('seo_description', 'projects')) {
            $this->forge->dropColumn('projects', 'seo_description');
        }
    }
}


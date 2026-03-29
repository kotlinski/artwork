<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddProjectIdToNews extends Migration
{
    public function up()
    {
        $this->forge->addColumn('news_modern', [
            'project_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
                'after'      => 'created_at',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('news_modern', 'project_id');
    }
}


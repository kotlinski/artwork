<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageDimensionsToNews extends Migration
{
    public function up()
    {
        $this->forge->addColumn('news_modern', [
            'width_px' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
            'height_px' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'default'    => null,
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('news_modern', ['width_px', 'height_px']);
    }
}

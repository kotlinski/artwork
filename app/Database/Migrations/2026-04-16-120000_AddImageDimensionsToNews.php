<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImageDimensionsToNews extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('news_modern')) {
            return;
        }

        if (!$this->db->fieldExists('width_px', 'news_modern')) {
            $this->forge->addColumn('news_modern', [
                'width_px' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
            ]);
        }

        if (!$this->db->fieldExists('height_px', 'news_modern')) {
            $this->forge->addColumn('news_modern', [
                'height_px' => [
                    'type'       => 'INT',
                    'constraint' => 11,
                    'null'       => true,
                    'default'    => null,
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('news_modern')) {
            return;
        }

        if ($this->db->fieldExists('width_px', 'news_modern')) {
            $this->forge->dropColumn('news_modern', 'width_px');
        }

        if ($this->db->fieldExists('height_px', 'news_modern')) {
            $this->forge->dropColumn('news_modern', 'height_px');
        }
    }
}

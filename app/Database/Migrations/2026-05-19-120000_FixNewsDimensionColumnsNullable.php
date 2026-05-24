<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class FixNewsDimensionColumnsNullable extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('news_modern')) {
            return;
        }

        // Ensure width_px and height_px exist and are nullable.
        // They may have been added manually as NOT NULL before the migration
        // system tracked them, causing inserts to fail when no image is provided.
        foreach (['width_px', 'height_px'] as $column) {
            if ($this->db->fieldExists($column, 'news_modern')) {
                $this->forge->modifyColumn('news_modern', [
                    $column => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'null'       => true,
                        'default'    => null,
                    ],
                ]);
            } else {
                $this->forge->addColumn('news_modern', [
                    $column => [
                        'type'       => 'INT',
                        'constraint' => 11,
                        'null'       => true,
                        'default'    => null,
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        // Intentionally left as no-op: reverting to NOT NULL would break data
        // that already has NULL values stored after this migration ran.
    }
}


<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddEventFieldsToNews extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('news_modern')) {
            return;
        }

        if (!$this->db->fieldExists('event_location', 'news_modern')) {
            $this->forge->addColumn('news_modern', [
                'event_location' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 255,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'main_image',
                ],
            ]);
        }

        if (!$this->db->fieldExists('event_start_date', 'news_modern')) {
            $this->forge->addColumn('news_modern', [
                'event_start_date' => [
                    'type'    => 'DATE',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'event_location',
                ],
            ]);
        }

        if (!$this->db->fieldExists('event_end_date', 'news_modern')) {
            $this->forge->addColumn('news_modern', [
                'event_end_date' => [
                    'type'    => 'DATE',
                    'null'    => true,
                    'default' => null,
                    'after'   => 'event_start_date',
                ],
            ]);
        }

        if (!$this->db->fieldExists('external_link', 'news_modern')) {
            $this->forge->addColumn('news_modern', [
                'external_link' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 2048,
                    'null'       => true,
                    'default'    => null,
                    'after'      => 'event_end_date',
                ],
            ]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('news_modern')) {
            return;
        }

        foreach (['external_link', 'event_end_date', 'event_start_date', 'event_location'] as $column) {
            if ($this->db->fieldExists($column, 'news_modern')) {
                $this->forge->dropColumn('news_modern', $column);
            }
        }
    }
}


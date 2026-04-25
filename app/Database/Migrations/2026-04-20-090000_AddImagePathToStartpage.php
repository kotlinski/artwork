<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddImagePathToStartpage extends Migration
{
    public function up()
    {
        if (!$this->db->tableExists('startpage')) {
            return;
        }

        if (!$this->db->fieldExists('image_path', 'startpage')) {
            $this->forge->addColumn('startpage', [
                'image_path' => [
                    'type' => 'VARCHAR',
                    'constraint' => 255,
                    'null' => true,
                    'after' => 'text',
                ],
            ]);
        }

        if (!$this->db->fieldExists('image_id', 'startpage') || !$this->db->tableExists('images')) {
            return;
        }

        $rows = $this->db->table('startpage s')
            ->select('s.id, s.image_path, i.file_name')
            ->join('images i', 'i.id = s.image_id', 'left')
            ->where('s.image_id >', 0)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $currentPath = trim((string) ($row['image_path'] ?? ''));
            $fileName = trim((string) ($row['file_name'] ?? ''));
            if ($currentPath !== '' || $fileName === '') {
                continue;
            }

            $this->db->table('startpage')
                ->where('id', (int) $row['id'])
                ->update(['image_path' => $fileName]);
        }
    }

    public function down()
    {
        if (!$this->db->tableExists('startpage') || !$this->db->fieldExists('image_path', 'startpage')) {
            return;
        }

        $this->forge->dropColumn('startpage', 'image_path');
    }
}


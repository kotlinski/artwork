<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSortOrderToProjects extends Migration
{
    public function up()
    {
        $this->forge->addColumn('projects', [
            'sort_order' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'after' => 'image_right'
            ]
        ]);
        
        // Initialize sort_order based on current order (by start_year DESC)
        $db = \Config\Database::connect();
        $projects = $db->table('projects')->orderBy('start_year', 'DESC')->get()->getResultArray();
        
        foreach ($projects as $index => $project) {
            $db->table('projects')->where('id', $project['id'])->update(['sort_order' => $index]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('projects', 'sort_order');
    }
}


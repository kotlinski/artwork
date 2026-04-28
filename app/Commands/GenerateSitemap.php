<?php

namespace App\Commands;

use App\Libraries\SitemapGenerator;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateSitemap extends BaseCommand
{
    protected $group       = 'App';
    protected $name        = 'sitemap:generate';
    protected $description = 'Generates the public/sitemap.xml from live database content.';

    public function run(array $params)
    {
        CLI::write('Generating sitemap...', 'yellow');
        try {
            (new SitemapGenerator())->generate();
            CLI::write('Sitemap generated successfully.', 'green');
        } catch (\Throwable $e) {
            CLI::error('Sitemap generation failed: ' . $e->getMessage());
        }
    }
}


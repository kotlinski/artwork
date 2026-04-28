<?php

namespace App\Libraries;

use App\Models\Image;
use App\Models\NewsModel;
use App\Models\Project;

class SitemapGenerator
{
    protected string $baseUrl;
    protected string $outputPath;

    public function __construct()
    {
        // Always use the production URL so the sitemap is correct on all environments.
        $this->baseUrl    = 'https://www.annesimonsson.se';
        $this->outputPath = FCPATH . 'sitemap.xml';
    }

    public function generate(): void
    {
        $urls = [];

        // --- Static pages with DB-derived dates ---
        $projectModel = new Project();
        $newsModel    = new NewsModel();
        $imageModel   = new Image();

        // Latest overall modification date (used for startpage)
        $latestProject = $projectModel
            ->where('is_published', 1)
            ->orderBy('updated_at', 'DESC')
            ->first();
        $latestNews = $newsModel
            ->where('is_published', 1)
            ->orderBy('created_at', 'DESC')
            ->first();
        $latestImage = $imageModel
            ->select('updated_at')
            ->orderBy('updated_at', 'DESC')
            ->first();

        $startpageDate = $this->latestDate([
            $latestProject['updated_at'] ?? null,
            $latestNews['created_at']    ?? null,
            $latestImage['updated_at']   ?? null,
        ]);

        $urls[] = $this->url('/', '1.0', 'weekly', $startpageDate);

        // --- News page ---
        $newsPageDate = $latestNews['created_at'] ?? $startpageDate;
        $urls[] = $this->url('/news', '0.8', 'weekly', $newsPageDate);

        // --- Projects overview ---
        $overviewDate = $latestProject['updated_at'] ?? $startpageDate;
        $urls[] = $this->url('/artwork', '0.9', 'monthly', $overviewDate);

        // --- Individual projects and their images ---
        $projects = $projectModel
            ->where('is_published', 1)
            ->orderBy('sort_order', 'ASC')
            ->findAll();

        foreach ($projects as $project) {
            $slug = $project['slug'] ?? null;
            if (!$slug) {
                continue;
            }
            $urls[] = $this->url('/' . $slug, '0.8', 'monthly', $project['updated_at'] ?? null);

            // Images for this project
            $images = $imageModel
                ->where('project', $project['id'])
                ->orderBy('`order`', 'ASC')
                ->findAll();

            foreach ($images as $image) {
                $fileId = $image['file_id'] ?? null;
                if (!$fileId) {
                    continue;
                }
                $imageDate = $image['updated_at'] ?? $image['datum'] ?? $project['updated_at'] ?? null;
                $urls[] = $this->url('/' . $slug . '/' . $fileId, '0.5', 'yearly', $imageDate);
            }
        }

        // --- About ---
        $urls[] = $this->url('/about', '0.7', 'monthly', null);

        // --- Contact ---
        $urls[] = $this->url('/contact', '0.5', 'yearly', null);

        // --- Build XML ---
        $xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9' . "\n";
        $xml .= '          http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        foreach ($urls as $entry) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($entry['loc'], ENT_XML1 | ENT_COMPAT, 'UTF-8') . "</loc>\n";
            if (!empty($entry['lastmod'])) {
                $xml .= "    <lastmod>" . $entry['lastmod'] . "</lastmod>\n";
            }
            $xml .= "    <changefreq>" . $entry['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $entry['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        file_put_contents($this->outputPath, $xml);
        log_message('info', 'Sitemap generated at ' . $this->outputPath);
    }

    // -----------------------------------------------------------------------

    protected function url(string $path, string $priority, string $changefreq, ?string $date): array
    {
        return [
            'loc'        => $this->baseUrl . $path,
            'lastmod'    => $date ? $this->formatDate($date) : '',
            'changefreq' => $changefreq,
            'priority'   => $priority,
        ];
    }

    protected function formatDate(?string $date): string
    {
        if (!$date) {
            return '';
        }
        try {
            $dt = new \DateTime($date);
            return $dt->format('Y-m-d');
        } catch (\Exception $e) {
            return '';
        }
    }

    protected function latestDate(array $dates): ?string
    {
        $timestamps = [];
        foreach ($dates as $d) {
            if ($d) {
                try {
                    $timestamps[] = (new \DateTime($d))->getTimestamp();
                } catch (\Exception $e) {
                    // ignore
                }
            }
        }
        if (empty($timestamps)) {
            return null;
        }
        return (new \DateTime('@' . max($timestamps)))->format('Y-m-d H:i:s');
    }
}



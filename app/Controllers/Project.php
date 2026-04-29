<?php

namespace App\Controllers;

use App\Libraries\ParsedownWithLinkTargets;
use App\Models\Image;

class Project extends BaseController
{
  public function detail($slug)
  {
    $model = new \App\Models\Project();
    $isLoggedIn = (bool)session()->get('isLoggedIn');
    $projectQuery = $model->where('slug', $slug);
    if (!$isLoggedIn) {
      $projectQuery = $projectQuery->where('is_published', 1);
    }
    $project = $projectQuery->first();
    if (!$project) {
      // Redirect to artwork page if slug does not match a project
      return redirect()->to('/artwork');
    }
    // Fetch all images connected to the project (by project id or slug)
    $imageModel = new Image();
    $images = $imageModel->where('project', $project['id'])->orderBy('`order`', 'ASC')->findAll();

    // Fetch next project by sort_order (wrap to first if at end)
    $projectModel = new \App\Models\Project();
    $currentSortOrder = $project['sort_order'] ?? null;
    $nextProject = null;
    if ($currentSortOrder !== null) {
      $nextProjectQuery = $projectModel->where('sort_order >', $currentSortOrder);
      if (!$isLoggedIn) {
        $nextProjectQuery = $nextProjectQuery->where('is_published', 1);
      }
      $nextProject = $nextProjectQuery
        ->orderBy('sort_order', 'ASC')
        ->first();
      if (!$nextProject) {
        // Wrap to first project if at end
        $wrapQuery = (new \App\Models\Project())->orderBy('sort_order', 'ASC');
        if (!$isLoggedIn) {
          $wrapQuery = $wrapQuery->where('is_published', 1);
        }
        $nextProject = $wrapQuery->first();
      }
    }
    $next_project_slug = $nextProject['slug'] ?? null;
    $next_project_title = $nextProject['title'] ?? null;
    $required = [
      'title' => $project['title'] .
        (isset($project['start_year']) ? ' (' . $project['start_year'] . (isset($project['end_year']) && $project['end_year'] ? '–' . $project['end_year'] : '') . ')' : ''),
      'selected_menu_item' => 'artwork',
      'description' => $project['description'] ?? '',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
    ];
    // Ensure all fields are set with safe defaults
    $project = array_merge([
      'id' => '',
      'slug' => '',
      'title' => '',
      'alternate_name' => '',
      'description' => '',
      'text' => '',
      'text_sv' => '',
      'start_year' => '',
      'end_year' => '',
      'location' => '',
      'map_url' => '',
      'external_links' => '',
      'image_left' => 0,
      'image_mid' => 0,
      'image_right' => 0,
      'sort_order' => 0
    ], $project);

    $hasEnglishText = trim((string) ($project['text'] ?? '')) !== '';
    $hasSwedishText = trim((string) ($project['text_sv'] ?? '')) !== '';
    $requestedLang = strtolower((string) ($this->request->getGet('lang') ?? 'en'));
    $selectedLang = ($requestedLang === 'sv' && $hasSwedishText) ? 'sv' : 'en';
    $projectText = $selectedLang === 'sv'
      ? (string) ($project['text_sv'] ?? '')
      : (string) ($project['text'] ?? '');

    // If the selected language is empty, fall back to whichever translation exists.
    if (trim($projectText) === '' && $hasEnglishText) {
      $selectedLang = 'en';
      $projectText = (string) ($project['text'] ?? '');
    } elseif (trim($projectText) === '' && $hasSwedishText) {
      $selectedLang = 'sv';
      $projectText = (string) ($project['text_sv'] ?? '');
    }
    if (!isset($images) || !is_array($images)) $images = [];
    $next_project_slug = $next_project_slug ?? '';
    $next_project_title = $next_project_title ?? '';

    // Fetch news items linked to this project
    $newsModel = new \App\Models\NewsModel();
    $projectNews = $newsModel
      ->where('project_id', $project['id'])
      ->orderBy('created_at', 'DESC')
      ->findAll();

    if (!empty($projectNews)) {
      $projectNews = $this->normalizeMainImagePaths($projectNews);
      $parser = new ParsedownWithLinkTargets();
      $parser->setSafeMode(true);
      $parser->setBreaksEnabled(true);
      $projectNews = $this->addParsedNewsContent($projectNews, $parser);
    }

    $allProjectsQuery = $projectModel->orderBy('sort_order', 'ASC');
    if (!$isLoggedIn) {
      $allProjectsQuery = $allProjectsQuery->where('is_published', 1);
    }
    $allProjects = $allProjectsQuery->findAll();

    return $this->renderView('artwork/project-view', $required, [
      'project'            => $project,
      'project_text'       => $projectText,
      'project_text_lang'  => $selectedLang,
      'has_text_en'        => $hasEnglishText,
      'has_text_sv'        => $hasSwedishText,
      'images'             => $images,
      'all_projects'       => $allProjects,
      'next_project_slug'  => $next_project_slug,
      'next_project_title' => $next_project_title,
      'project_news'       => $projectNews,
    ]);
  }
  
  public function imageDetail($project_slug, $image_slug)
  {
    $model = new \App\Models\Project();
    $isLoggedIn = (bool)session()->get('isLoggedIn');
    $projectQuery = $model->where('slug', $project_slug);
    if (!$isLoggedIn) {
      $projectQuery = $projectQuery->where('is_published', 1);
    }
    $project = $projectQuery->first();
    if (!$project) {
      log_message('debug', 'no project found ' . $project_slug . ' / ' . $image_slug);
      return redirect()->to('/artwork');
    }
    $imageModel = new \App\Models\Image();
    $images = $imageModel->where('project', $project['id'])->orderBy('`order`', 'ASC')->findAll();
    $image = null;
    $current_index = null;
    foreach ($images as $i => $img) {
      if ($img['file_id'] === $image_slug) {
        $image = $img;
        $current_index = $i;
        break;
      }
    }
    if (!$image) {
      log_message('debug', 'no image found ' . $project_slug . ' / ' . $image_slug);
      return redirect()->to(base_url($project_slug));
    }
    // Carousel wrap-around logic
    $images_count = count($images);
    $prev_index = $images_count > 0 ? (($current_index - 1 + $images_count) % $images_count) : null;
    $next_index = $images_count > 0 ? (($current_index + 1) % $images_count) : null;
    $prev_slug = $prev_index !== null && isset($images[$prev_index]) ? (isset($images[$prev_index]['file_id']) ? $images[$prev_index]['file_id'] : (isset($images[$prev_index]['file_name']) ? pathinfo($images[$prev_index]['file_name'], PATHINFO_FILENAME) : '')) : null;
    $next_slug = $next_index !== null && isset($images[$next_index]) ? (isset($images[$next_index]['file_id']) ? $images[$next_index]['file_id'] : (isset($images[$next_index]['file_name']) ? pathinfo($images[$next_index]['file_name'], PATHINFO_FILENAME) : '')) : null;
    $required = [
      'title' => $image['title'] . ' | Anne Hamrin Simonsson',
      'selected_menu_item' => 'artwork',
      'description' => !empty($image['caption']) ? $image['caption'] : 'Artwork by Anne Hamrin Simonsson',
      'og_image' => base_url('konst/' . $image['file_name']),
      'og_image_width' => $image['width_px'],
      'og_image_height' => $image['height_px'],
    ];
    return $this->renderView('artwork/image-view', $required, [
      'project' => $project,
      'image' => $image,
      'current_index' => $current_index,
      'prev_slug' => $prev_slug,
      'next_slug' => $next_slug,
      'images_count' => $images_count,
      'hide_main_header' => true,
      'jsonld' => generateImageJsonLd($image, '/' . $project['slug'])
    ]);
  }
  
  public function update()
  {
    $request = service('request');
    $id = $request->getPost('id');
    $text = $request->getPost('text');
    $textSv = $request->getPost('text_sv');
    if (!$id) {
      if ($request->isAJAX()) {
        return $this->response->setStatusCode(422)->setJSON([
          'success' => false,
          'error' => 'Missing project id',
        ]);
      }
      return redirect()->back()->with('error', 'Missing project id');
    }
    if ($text === null && $textSv === null) {
      if ($request->isAJAX()) {
        return $this->response->setStatusCode(422)->setJSON([
          'success' => false,
          'error' => 'Missing project text payload',
        ]);
      }
      return redirect()->back()->with('error', 'Missing project text payload');
    }
    $model = new \App\Models\Project();
    $project = $model->find($id);
    if (!$project) {
      if ($request->isAJAX()) {
        return $this->response->setStatusCode(404)->setJSON([
          'success' => false,
          'error' => 'Project not found',
        ]);
      }
      return redirect()->back()->with('error', 'Project not found');
    }
    $payload = [];
    if ($text !== null) {
      $payload['text'] = $text;
    }
    if ($textSv !== null) {
      $payload['text_sv'] = $textSv;
    }

    $ok = $model->update($id, $payload);

    if ($request->isAJAX()) {
      if (!$ok) {
        return $this->response->setStatusCode(500)->setJSON([
          'success' => false,
          'error' => 'Failed to save project text',
        ]);
      }

      return $this->response->setJSON([
        'success' => true,
        'message' => 'Project texts saved',
      ]);
    }
    // Redirect to project detail page
    return redirect()->to(base_url('/' . $project['slug']))->with('success', 'Project info updated');
  }
  
  // Fetch all projects for overview (for artwork page, admin, etc.)
  public function index()
  {
      $model = new \App\Models\Project();
      $projectsQuery = $model->orderBy('sort_order', 'DESC');
      if (!session()->get('isLoggedIn')) {
          $projectsQuery = $projectsQuery->where('is_published', 1);
      }
      $projects = $projectsQuery->findAll();
      return $this->renderView('artwork/project_overview', [
          'projects' => $projects,
          'selected_menu_item' => 'artwork',
      ]);
  }

  protected function addParsedNewsContent(array $newsItems, \Parsedown $parser): array
  {
    return array_map(static function (array $item) use ($parser): array {
      $item['content_parsed'] = $parser->text($item['content'] ?? '');
      return $item;
    }, $newsItems);
  }

  protected function normalizeMainImagePaths(array $newsItems): array
  {
    return array_map(static function (array $item): array {
      $mainImage = $item['main_image'] ?? null;
      if (is_string($mainImage) && str_starts_with($mainImage, 'news/')) {
        $item['main_image'] = 'media/news/' . ltrim(substr($mainImage, 5), '/');
      }

      $mainImage = $item['main_image'] ?? null;
      if (is_string($mainImage) && str_starts_with($mainImage, 'media/news/')) {
        $basename = basename($mainImage);
        $thumbPath = 'media/news/thumb/' . $basename;
        $thumb2xPath = 'media/news/thumb2x/' . $basename;
        $smallPath = 'media/news/small/' . $basename;
        $mediumPath = 'media/news/medium/' . $basename;
        $largePath = 'media/news/large/' . $basename;
        $xLargePath = 'media/news/x-large/' . $basename;

        $hasThumb = is_file(FCPATH . $thumbPath);
        $hasThumb2x = is_file(FCPATH . $thumb2xPath);
        $hasSmall = is_file(FCPATH . $smallPath);
        $hasMedium = is_file(FCPATH . $mediumPath);
        $hasLarge = is_file(FCPATH . $largePath);
        $hasXLarge = is_file(FCPATH . $xLargePath);

        $item['main_image_thumb'] = $hasThumb ? $thumbPath : $mainImage;
        $item['main_image_thumb2x'] = $hasThumb2x ? $thumb2xPath : $item['main_image_thumb'];
        $item['main_image_small'] = $hasSmall ? $smallPath : $mainImage;
        $item['main_image_medium'] = $hasMedium ? $mediumPath : $item['main_image_small'];
        $item['main_image_large'] = $hasLarge ? $largePath : $item['main_image_medium'];
        $item['main_image_x_large'] = $hasXLarge ? $xLargePath : $item['main_image_large'];

        $displayFilePath = FCPATH . $item['main_image_thumb'];
        $dims = @getimagesize($displayFilePath);
        if ($dims && $dims[0] > 0 && $dims[1] > 0) {
          $item['main_image_width'] = (int)$dims[0];
          $item['main_image_height'] = (int)$dims[1];
        } else {
          $storedWidth = isset($item['width_px']) ? (int)$item['width_px'] : 0;
          $storedHeight = isset($item['height_px']) ? (int)$item['height_px'] : 0;
          if ($storedWidth > 0 && $storedHeight > 0) {
            $thumbMax = 122;
            $scale = min($thumbMax / $storedWidth, $thumbMax / $storedHeight, 1.0);
            $item['main_image_width'] = (int)round($storedWidth * $scale);
            $item['main_image_height'] = (int)round($storedHeight * $scale);
          }
        }
      }

      return $item;
    }, $newsItems);
  }
}


function generateImageJsonLd($image, $projectPath)
{
  $year_created = !empty($image['date_created']) ? $image['date_created'] : null;
  
  $jsonLd = [
    "@context" => "https://schema.org",
    "@graph" => [
      [
        "@type" => "VisualArtwork",
        "@id" => "https://www.annesimonsson.se" . $projectPath . "/" . $image['file_id'] . "#artwork",
        "name" => $image['title'],
        "alternateName" => $image['alternate_name'],
        "license" => "https://www.annesimonsson.se/license.html",
        "description" => $image['caption'],
        "dateCreated" => $year_created,
        "artform" => $image['artform'] ?? "Visual Artwork",
        "artMedium" => $image['art_medium'],
        "artworkSurface" => $image['artwork_surface'],
        "artEdition" => $image['art_edition'],
        "creator" => [
          "@type" => "Person",
          "@id" => "https://www.annesimonsson.se/#person",
          "name" => "Anne Hamrin Simonsson",
          "sameAs" => ["https://www.wikidata.org/wiki/Q137808007"]
        ]
      ],
      [
        "@type" => "ImageObject",
        "@id" => "https://www.annesimonsson.se" . $projectPath . "/" . $image['file_id'] . "#image",
        "url" => "https://www.annesimonsson.se/konst/" . $image['file_name'],
        "contentUrl" => "https://www.annesimonsson.se/konst/" . $image['file_name'],
        "thumbnailUrl" => "https://www.annesimonsson.se/konst/thumb/" . $image['file_name'],
        "license" => "https://www.annesimonsson.se/license.html",
        "acquireLicensePage" => "https://www.annesimonsson.se/license.html",
        "creditText" => "Anne Hamrin Simonsson",
        "copyrightNotice" => "© 2009-2026 Anne Hamrin Simonsson. All rights reserved.",
        "width" => $image['width_px'],
        "height" => $image['height_px'],
        "encodingFormat" => "image/webp",
        "creator" => [
          "@type" => "Person",
          "name" => $image['photographer_name'] ?? "Anne Hamrin Simonsson"
        ]
      ]
    ]
  ];
  
  if (!empty($image['height_cm'])) {
    $jsonLd["@graph"][0]["height"] = ["@type" => "Distance", "name" => $image['height_cm'] . " cm"];
    $jsonLd["@graph"][0]["width"] = ["@type" => "Distance", "name" => $image['width_cm'] . " cm"];
    if (!empty($image['depth_cm'])) {
      $jsonLd["@graph"][0]["depth"] = ["@type" => "Distance", "name" => $image['depth_cm'] . " cm"];
    }
  }
  
  if (!empty($image['geo_location'])) {
    $jsonLd["@graph"][0]["locationCreated"] = [
      "@type" => "Place",
      "name" => $image['geo_location'],
      "hasMap" => $image['map_url'] ?? null,
      "address" => [
        "@type" => "PostalAddress",
        "addressLocality" => $image['address_locality'],
        "addressRegion" => $image['address_region'],
        "addressCountry" => $image['address_country']
      ]
    ];
  }
  
  if (!empty($image['project'])) {
    $jsonLd["@graph"][0]["isPartOf"] = [
      "@type" => "CreativeWorkSeries",
      "name" => $image['project'],
      "creator" => ["@id" => "https://www.annesimonsson.se/#person"]
    ];
  }
  
  return json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}


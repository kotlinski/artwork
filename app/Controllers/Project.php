<?php

namespace App\Controllers;

use App\Libraries\ParsedownWithLinkTargets;
use App\Models\Image;

class Project extends BaseController
{
  public function detail($slug)
  {
    $model = new \App\Models\Project();
    $is_logged_in = (bool)session()->get('is_logged_in');
    $projectQuery = $model->where('slug', $slug);
    if (!$is_logged_in) {
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
      if (!$is_logged_in) {
        $nextProjectQuery = $nextProjectQuery->where('is_published', 1);
      }
      $nextProject = $nextProjectQuery
        ->orderBy('sort_order', 'ASC')
        ->first();
      if (!$nextProject) {
        // Wrap to first project if at end
        $wrapQuery = (new \App\Models\Project())->orderBy('sort_order', 'ASC');
        if (!$is_logged_in) {
          $wrapQuery = $wrapQuery->where('is_published', 1);
        }
        $nextProject = $wrapQuery->first();
      }
    }
    $next_project_slug = $nextProject['slug'] ?? null;
    $next_project_title = $nextProject['title'] ?? null;
    // Ensure all fields are set with safe defaults
    $project = array_merge([
      'id' => '',
      'slug' => '',
      'title' => '',
      'alternate_name' => '',
      'description' => '',
      'seo_description' => '',
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

    $project_meta_description = $this->build_project_meta_description($project, $projectText);
    $project_meta_keywords = $this->build_project_meta_keywords($project, $projectText);
    $project_og_image = base_url('anne-hamrin-simonsson-portrait.jpg');
    $project_og_image_width = '320';
    $project_og_image_height = '320';
    foreach ($images as $project_image) {
      $project_image_file = trim((string)($project_image['file_name'] ?? ''));
      if ($project_image_file === '') {
        continue;
      }
      $project_og_image = base_url('konst/' . $project_image_file);
      $project_og_image_width = (string)((int)($project_image['width_px'] ?? 0) ?: 320);
      $project_og_image_height = (string)((int)($project_image['height_px'] ?? 0) ?: 320);
      break;
    }
    $required = [
      'title' => $project['title'] . ' | Anne Hamrin Simonsson',
      'selected_menu_item' => 'artwork',
      'description' => $project_meta_description,
      'meta_description' => $project_meta_description,
      'meta_keywords' => $project_meta_keywords,
      'og_image' => $project_og_image,
      'og_image_width' => $project_og_image_width,
      'og_image_height' => $project_og_image_height,
    ];

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
    if (!$is_logged_in) {
      $allProjectsQuery = $allProjectsQuery->where('is_published', 1);
    }
    $allProjects = $allProjectsQuery->findAll();
    $projectJsonLd = generateProjectJsonLd($project, $images, $projectText, $selectedLang, $projectNews);

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
      'project_jsonld'     => $projectJsonLd,
    ]);
  }
  
  public function imageDetail($project_slug, $image_slug)
  {
    $model = new \App\Models\Project();
    $is_logged_in = (bool)session()->get('is_logged_in');
    $projectQuery = $model->where('slug', $project_slug);
    if (!$is_logged_in) {
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
    $seo_meta = $this->build_image_seo_meta($image, $project);

    $required = [
      'title' => $image['title'] . ' | Anne Hamrin Simonsson',
      'selected_menu_item' => 'artwork',
      'description' => $seo_meta['description'],
      'meta_description' => $seo_meta['description'],
      'meta_keywords' => $seo_meta['keywords'],
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
      'jsonld' => generateImageJsonLd($image, $project)
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
      if (!session()->get('is_logged_in')) {
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

  protected function build_project_meta_description(array $project, string $project_text): string
  {
    $artist_name = 'Anne Hamrin Simonsson';
    $project_title = trim((string)($project['title'] ?? ''));
    $seo_description = trim((string)($project['seo_description'] ?? ''));
    $project_description = trim((string)($project['description'] ?? ''));
    $project_text_plain = $this->clean_meta_text((string)$project_text);

    $description = $seo_description !== ''
      ? $seo_description
      : ($project_description !== '' ? $project_description : $project_text_plain);
    $description = $this->clean_meta_text($description);

    $start_year = isset($project['start_year']) ? (int)$project['start_year'] : 0;
    $end_year = isset($project['end_year']) ? (int)$project['end_year'] : 0;
    $year_text = '';
    if ($start_year > 0 && $end_year > 0) {
      $year_text = $start_year . '-' . $end_year;
    } elseif ($start_year > 0) {
      $year_text = (string)$start_year;
    } elseif ($end_year > 0) {
      $year_text = (string)$end_year;
    }

    $filler_sentences = [];
    if ($project_title !== '') {
      $title_sentence = 'The project ' . $project_title . ' by ' . $artist_name;
      if ($year_text !== '') {
        $title_sentence .= ' (' . $year_text . ')';
      }
      $filler_sentences[] = $title_sentence . '.';
    }
    $filler_sentences[] = 'Explore artworks, materials, and context from this project by ' . $artist_name . '.';
    $filler_sentences[] = 'Discover visual art and project details from ' . $artist_name . '.';

    if ($description !== '' && !preg_match('/[.!?]$/', $description)) {
      $description .= '.';
    }

    foreach ($filler_sentences as $sentence) {
      if ($this->meta_length($description) >= 150) {
        break;
      }
      $description = trim($description);
      $description .= ($description !== '' ? ' ' : '') . trim($sentence);
      $description = $this->clean_meta_text($description);
    }

    while ($this->meta_length($description) < 150) {
      $tail = ' More about ' . ($project_title !== '' ? $project_title . ' and ' : '') . $artist_name . '.';
      $description .= $tail;
      $description = $this->clean_meta_text($description);
      if ($this->meta_length($description) > 280) {
        break;
      }
    }

    $description = $this->truncate_meta_text($description, 220);
    if ($description === '') {
      $description = 'Project by ' . $artist_name . '.';
    }

    return $description;
  }

  protected function meta_length(string $text): int
  {
    return function_exists('mb_strlen') ? (int)mb_strlen($text) : strlen($text);
  }

  protected function clean_meta_text(string $text): string
  {
    $text = strip_tags($text);
    $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1', $text) ?? $text;
    $text = str_replace(['*', '_', '`'], '', $text);
    $text = preg_replace('/\s+/', ' ', $text) ?? $text;
    return trim($text);
  }

  protected function build_project_meta_keywords(array $project, string $project_text): string
  {
    $artist_name = 'Anne Hamrin Simonsson';
    $project_title = trim((string)($project['title'] ?? ''));
    $alternate_name = trim((string)($project['alternate_name'] ?? ''));
    $description = trim((string)($project['description'] ?? ''));
    $seo_description = trim((string)($project['seo_description'] ?? ''));
    $text_plain = trim(strip_tags((string)$project_text));

    $start_year = isset($project['start_year']) ? (int)$project['start_year'] : 0;
    $end_year = isset($project['end_year']) ? (int)$project['end_year'] : 0;
    $year_keyword = '';
    if ($start_year > 0 && $end_year > 0) {
      $year_keyword = $start_year . '-' . $end_year;
    } elseif ($start_year > 0) {
      $year_keyword = (string)$start_year;
    }

    $keywords = [
      $artist_name,
      'Anne Hamrin Simonsson artist',
      'contemporary art',
      'visual art',
      'conceptual art',
      $project_title,
      $alternate_name,
      $year_keyword,
      trim((string)($project['slug'] ?? '')),
    ];

    foreach ([$seo_description, $description, $text_plain] as $source) {
      if ($source === '') {
        continue;
      }
      if (preg_match_all('/\p{L}[\p{L}\p{N}\-]{2,}/u', $source, $matches)) {
        foreach ($matches[0] as $token) {
          $token = trim((string)$token);
          if ($token === '') {
            continue;
          }
          $keywords[] = $token;
          if (count($keywords) > 80) {
            break 2;
          }
        }
      }
    }

    $stopwords = [
      'and', 'the', 'for', 'with', 'from', 'this', 'that', 'about', 'into', 'over', 'under',
      'som', 'och', 'det', 'den', 'att', 'med', 'om', 'ett', 'en', 'the',
    ];
    $filtered = [];
    foreach ($keywords as $keyword) {
      $keyword = trim((string)$keyword, ",.;:()[]{}\"'");
      if ($keyword === '') {
        continue;
      }
      $key = strtolower($keyword);
      if (in_array($key, $stopwords, true)) {
        continue;
      }
      if (isset($filtered[$key])) {
        continue;
      }
      $filtered[$key] = $keyword;
      if (count($filtered) >= 12) {
        break;
      }
    }

    return implode(', ', array_values($filtered));
  }

  protected function build_image_seo_meta(array $image, array $project): array
  {
    $artist_name = 'Anne Hamrin Simonsson';
    $image_title = trim((string)($image['title'] ?? ''));
    $project_title = trim((string)($project['title'] ?? ''));
    $caption = trim((string)($image['caption'] ?? ''));
    $date_created = trim((string)($image['date_created'] ?? ''));
    $artform = trim((string)($image['artform'] ?? ''));
    $art_medium = trim((string)($image['art_medium'] ?? ''));
    $artwork_surface = trim((string)($image['artwork_surface'] ?? ''));
    $geo_location = trim((string)($image['geo_location'] ?? ''));
    $address_locality = trim((string)($image['address_locality'] ?? ''));
    $address_region = trim((string)($image['address_region'] ?? ''));

    $dimensions = [];
    $height_cm = trim((string)($image['height_cm'] ?? ''));
    $width_cm = trim((string)($image['width_cm'] ?? ''));
    $depth_cm = trim((string)($image['depth_cm'] ?? ''));
    if ($height_cm !== '' && $width_cm !== '') {
      $dimension_text = $height_cm . ' x ' . $width_cm . ' cm';
      if ($depth_cm !== '') {
        $dimension_text .= ', depth ' . $depth_cm . ' cm';
      }
      $dimensions[] = $dimension_text;
    }

    $description_parts = [];
    if ($image_title !== '') {
      $description_parts[] = $image_title . ' by ' . $artist_name;
    } else {
      $description_parts[] = 'Artwork by ' . $artist_name;
    }
    if ($project_title !== '') {
      $description_parts[] = 'from the project ' . $project_title;
    }
    if ($date_created !== '') {
      $description_parts[] = 'created ' . $date_created;
    }
    if ($artform !== '') {
      $description_parts[] = 'art form: ' . $artform;
    }
    if ($art_medium !== '') {
      $description_parts[] = 'medium: ' . $art_medium;
    }
    if ($artwork_surface !== '') {
      $description_parts[] = 'surface: ' . $artwork_surface;
    }
    if (!empty($dimensions)) {
      $description_parts[] = 'dimensions: ' . implode(', ', $dimensions);
    }

    $location_bits = array_values(array_filter([$geo_location, $address_locality, $address_region], static fn($v) => $v !== ''));
    if (!empty($location_bits)) {
      $description_parts[] = 'location: ' . implode(', ', array_unique($location_bits));
    }

    if ($caption !== '') {
      $description_parts[] = $caption;
    }

    $description = preg_replace('/\s+/', ' ', implode('. ', array_filter($description_parts)));
    $description = trim((string)$description, " \t\n\r\0\x0B.");
    if ($description !== '') {
      $description .= '.';
    }

    // Keep description sufficiently descriptive while avoiding very long snippets.
    $description = $this->truncate_meta_text($description, 220);
    if ($description === '') {
      $description = 'Artwork by ' . $artist_name . '.';
    }

    $keyword_candidates = [
      $artist_name,
      'Anne Hamrin Simonsson artist',
      'contemporary art',
      'visual art',
      'Swedish artist',
      $image_title,
      $project_title,
      $artform,
      $art_medium,
      $artwork_surface,
      $address_locality,
      $address_region,
      $geo_location,
      trim((string)($image['file_id'] ?? '')),
    ];

    $keywords = [];
    foreach ($keyword_candidates as $keyword) {
      $keyword = trim((string)$keyword);
      if ($keyword === '') {
        continue;
      }
      $key = strtolower($keyword);
      if (isset($keywords[$key])) {
        continue;
      }
      $keywords[$key] = $keyword;
      if (count($keywords) >= 12) {
        break;
      }
    }

    return [
      'description' => $description,
      'keywords' => implode(', ', array_values($keywords)),
    ];
  }

  protected function truncate_meta_text(string $text, int $max_len): string
  {
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if ($text === '' || strlen($text) <= $max_len) {
      return $text;
    }

    $cut = substr($text, 0, $max_len + 1);
    $last_space = strrpos($cut, ' ');
    if ($last_space !== false && $last_space > (int)($max_len * 0.6)) {
      $cut = substr($cut, 0, $last_space);
    } else {
      $cut = substr($cut, 0, $max_len);
    }

    return rtrim($cut, ",;:. ") . '.';
  }
}


function generateProjectJsonLd(array $project, array $images, string $projectText = '', string $language = 'en', array $projectNews = []): string
{
  $baseUrl = rtrim((string) base_url('/'), '/');
  $organizationId = $baseUrl . '/#organization';
  $logoId = $baseUrl . '/#publisher-logo';
  $publisherForArticles = [
    '@type' => 'Organization',
    '@id' => $organizationId,
    'name' => 'Anne Hamrin Simonsson',
    'url' => $baseUrl . '/',
    'sameAs' => [
      'https://www.wikidata.org/wiki/Q137808007',
      'https://www.instagram.com/ahamrinsimonsson/',
      'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
    ],
    'contactPoint' => [
      [
        '@type' => 'ContactPoint',
        'contactType' => 'artwork inquiries',
        'url' => $baseUrl . '/contact',
        'availableLanguage' => ['en', 'sv'],
      ],
    ],
    'logo' => [
      '@type' => 'ImageObject',
      '@id' => $logoId,
      'url' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'width' => 320,
      'height' => 320,
    ],
  ];
  $slug = trim((string) ($project['slug'] ?? ''));
  $projectPath = $slug !== '' ? '/' . rawurlencode($slug) : '/artwork';
  $projectUrl = $baseUrl . $projectPath;

  $imagesById = [];
  foreach ($images as $image) {
    if (!is_array($image)) {
      continue;
    }
    $imageId = isset($image['id']) ? (int) $image['id'] : 0;
    if ($imageId > 0) {
      $imagesById[$imageId] = $image;
    }
  }

  $highlightIds = [];
  foreach (['image_left', 'image_mid', 'image_right'] as $field) {
    $candidate = isset($project[$field]) ? (int) $project[$field] : 0;
    if ($candidate > 0 && !in_array($candidate, $highlightIds, true)) {
      $highlightIds[] = $candidate;
    }
  }

  $highlightedImages = [];
  foreach ($highlightIds as $imageId) {
    if (isset($imagesById[$imageId])) {
      $highlightedImages[] = $imagesById[$imageId];
    }
  }
  if (empty($highlightedImages)) {
    $highlightedImages = array_slice($images, 0, 3);
  }

  $allImageNodes = [];
  $allImageRefs = [];
  $imageRefByDbId = [];
  $imageNodeByDbId = [];
  foreach ($images as $index => $image) {
    if (!is_array($image)) {
      continue;
    }

    $fileName = trim((string) ($image['file_name'] ?? ''));
    if ($fileName === '') {
      continue;
    }

    $fileId = trim((string) ($image['file_id'] ?? ''));
    $imageKey = $fileId !== '' ? rawurlencode($fileId) : 'image-' . ((int) ($image['id'] ?? ($index + 1)));
    $imageId = $projectUrl . '#image-' . $imageKey;

    $imageNode = [
      '@type' => 'ImageObject',
      '@id' => $imageId,
      'url' => $baseUrl . '/konst/' . $fileName,
      'contentUrl' => $baseUrl . '/konst/' . $fileName,
      'thumbnailUrl' => $baseUrl . '/konst/thumb/' . $fileName,
      'name' => (string) ($image['title'] ?? ($project['title'] ?? 'Artwork image')),
      'caption' => (string) ($image['caption'] ?? ''),
      'inLanguage' => $language,
    ];

    $width = isset($image['width_px']) ? (int) $image['width_px'] : 0;
    $height = isset($image['height_px']) ? (int) $image['height_px'] : 0;
    if ($width > 0) {
      $imageNode['width'] = $width;
    }
    if ($height > 0) {
      $imageNode['height'] = $height;
    }

    $allImageNodes[] = $imageNode;
    $allImageRefs[] = ['@id' => $imageId];

    $dbImageId = isset($image['id']) ? (int) $image['id'] : 0;
    if ($dbImageId > 0) {
      $imageRefByDbId[$dbImageId] = ['@id' => $imageId];
      $imageNodeByDbId[$dbImageId] = $imageNode;
    }
  }

  $highlightListItems = [];
  $highlightRefs = [];
  foreach ($highlightedImages as $index => $image) {
    if (!is_array($image)) {
      continue;
    }

    $dbImageId = isset($image['id']) ? (int) $image['id'] : 0;
    if ($dbImageId > 0 && isset($imageRefByDbId[$dbImageId])) {
      $highlightRef = $imageRefByDbId[$dbImageId];
    } else {
      $fileId = trim((string) ($image['file_id'] ?? ''));
      $imageKey = $fileId !== '' ? rawurlencode($fileId) : 'image-' . ((int) ($image['id'] ?? ($index + 1)));
      $highlightRef = ['@id' => $projectUrl . '#image-' . $imageKey];
    }

    $highlightRefs[] = $highlightRef;
    $highlightListItems[] = [
      '@type' => 'ListItem',
      'position' => $index + 1,
      'item' => $highlightRef,
      'name' => (string) ($image['title'] ?? ('Highlighted image ' . ($index + 1))),
    ];
  }

  $projectName = (string) ($project['title'] ?? 'Artwork project');
  $projectDescription = trim((string) ($project['description'] ?? ''));
  if ($projectDescription === '') {
    $projectDescription = trim(strip_tags($projectText));
  }

  $projectPrimaryImageUrl = '';
  foreach ($highlightedImages as $highlightedImage) {
    if (!is_array($highlightedImage)) {
      continue;
    }
    $highlightFileName = trim((string) ($highlightedImage['file_name'] ?? ''));
    if ($highlightFileName !== '') {
      $projectPrimaryImageUrl = $baseUrl . '/konst/' . $highlightFileName;
      break;
    }
  }
  if ($projectPrimaryImageUrl === '') {
    foreach ($images as $imageCandidate) {
      if (!is_array($imageCandidate)) {
        continue;
      }
      $candidateFileName = trim((string) ($imageCandidate['file_name'] ?? ''));
      if ($candidateFileName !== '') {
        $projectPrimaryImageUrl = $baseUrl . '/konst/' . $candidateFileName;
        break;
      }
    }
  }

  $projectPrimaryImageObject = null;
  foreach ($highlightedImages as $highlightedImage) {
    if (!is_array($highlightedImage)) {
      continue;
    }
    $highlightedDbId = isset($highlightedImage['id']) ? (int) $highlightedImage['id'] : 0;
    if ($highlightedDbId > 0 && isset($imageNodeByDbId[$highlightedDbId])) {
      $projectPrimaryImageObject = $imageNodeByDbId[$highlightedDbId];
      break;
    }
  }
  if ($projectPrimaryImageObject === null && $projectPrimaryImageUrl !== '') {
    $projectPrimaryImageObject = [
      '@type' => 'ImageObject',
      'url' => $projectPrimaryImageUrl,
      'contentUrl' => $projectPrimaryImageUrl,
      'name' => $projectName,
    ];
  }

  $projectNode = [
    '@type' => 'VisualArtwork',
    '@id' => $projectUrl . '#project',
    'name' => $projectName,
    'alternateName' => (string) ($project['alternate_name'] ?? ''),
    'description' => $projectDescription,
    'url' => $projectUrl,
    'creator' => ['@id' => $baseUrl . '/#person'],
    'genre' => 'Conceptual art',
    'inLanguage' => $language,
    'associatedMedia' => $highlightRefs,
    'isPartOf' => ['@id' => $baseUrl . '/artwork#webpage'],
  ];
  if ($projectPrimaryImageObject !== null) {
    $projectNode['image'] = $projectPrimaryImageObject;
  }

  $startYear = isset($project['start_year']) ? (int) $project['start_year'] : 0;
  $endYear = isset($project['end_year']) ? (int) $project['end_year'] : 0;
  if ($startYear > 0) {
    $projectNode['dateCreated'] = (string) $startYear;
  }
  if ($startYear > 0 && $endYear > 0) {
    $projectNode['temporalCoverage'] = $startYear . '/' . $endYear;
  }

  $newsNodes = [];
  $subjectOfRefs = [];
  foreach ($projectNews as $newsItem) {
    if (!is_array($newsItem)) {
      continue;
    }

    $newsSlug = trim((string) ($newsItem['slug'] ?? ''));
    if ($newsSlug === '') {
      continue;
    }

    $newsId = $baseUrl . '/news#news-' . rawurlencode($newsSlug);
    $subjectOfRefs[] = ['@id' => $newsId];
    $newsNodes[] = [
      '@type' => 'BlogPosting',
      '@id' => $newsId,
      'url' => $newsId,
      'headline' => (string) ($newsItem['title'] ?? $newsSlug),
      'isPartOf' => ['@id' => $baseUrl . '/#website'],
      'about' => ['@id' => $projectUrl . '#project'],
      'author' => ['@id' => $baseUrl . '/#person'],
      'publisher' => $publisherForArticles,
      'mainEntityOfPage' => ['@id' => $projectUrl . '#webpage'],
    ];

    if ($projectPrimaryImageObject !== null) {
      $newsNodes[count($newsNodes) - 1]['image'] = $projectPrimaryImageObject;
    }

    $schemaImagePath = '';
    foreach (['main_image_x_large', 'main_image_large', 'main_image_medium', 'main_image', 'main_image_thumb'] as $candidateField) {
      $candidatePath = trim((string) ($newsItem[$candidateField] ?? ''));
      if ($candidatePath !== '') {
        $schemaImagePath = $candidatePath;
        break;
      }
    }

    if ($schemaImagePath !== '') {
      $imageNode = [
        '@type' => 'ImageObject',
        'url' => base_url($schemaImagePath),
      ];

      $imageWidth = 0;
      $imageHeight = 0;
      $schemaDims = @getimagesize(FCPATH . ltrim($schemaImagePath, '/'));
      if (is_array($schemaDims) && isset($schemaDims[0], $schemaDims[1])) {
        $imageWidth = (int) $schemaDims[0];
        $imageHeight = (int) $schemaDims[1];
      }
      if ($imageWidth <= 0 || $imageHeight <= 0) {
        $imageWidth = isset($newsItem['width_px']) ? (int) $newsItem['width_px'] : 0;
        $imageHeight = isset($newsItem['height_px']) ? (int) $newsItem['height_px'] : 0;
      }
      if ($imageWidth > 0) {
        $imageNode['width'] = $imageWidth;
      }
      if ($imageHeight > 0) {
        $imageNode['height'] = $imageHeight;
      }

      $newsNodes[count($newsNodes) - 1]['image'] = $imageNode;
    }

    $createdAt = trim((string) ($newsItem['created_at'] ?? ''));
    if ($createdAt !== '') {
      $timestamp = strtotime($createdAt);
      if ($timestamp !== false) {
        $newsNodes[count($newsNodes) - 1]['datePublished'] = date('c', $timestamp);
      }
    }

    $updatedAt = trim((string) ($newsItem['updated_at'] ?? ''));
    if ($updatedAt !== '') {
      $updatedTimestamp = strtotime($updatedAt);
      if ($updatedTimestamp !== false) {
        $newsNodes[count($newsNodes) - 1]['dateModified'] = date('c', $updatedTimestamp);
      }
    }
  }
  if (!empty($subjectOfRefs)) {
    $projectNode['subjectOf'] = $subjectOfRefs;
  }

  $projectWebPageNode = [
    '@type' => 'CollectionPage',
    '@id' => $projectUrl . '#webpage',
    'url' => $projectUrl,
    'name' => $projectName,
    'description' => $projectDescription,
    'isPartOf' => ['@id' => $baseUrl . '/#website'],
    'about' => ['@id' => $projectUrl . '#project'],
    'mainEntity' => ['@id' => $projectUrl . '#project'],
    'breadcrumb' => ['@id' => $projectUrl . '#breadcrumb'],
  ];
  if ($projectPrimaryImageObject !== null) {
    $projectWebPageNode['primaryImageOfPage'] = $projectPrimaryImageObject;
  } elseif (!empty($highlightRefs)) {
    $projectWebPageNode['primaryImageOfPage'] = $highlightRefs[0];
  }

  $personImageObject = [
    '@type' => 'ImageObject',
    '@id' => $logoId,
    'url' => base_url('anne-hamrin-simonsson-portrait.jpg'),
    'contentUrl' => base_url('anne-hamrin-simonsson-portrait.jpg'),
    'width' => 320,
    'height' => 320,
  ];

  $graph = [
    [
      '@type' => 'WebSite',
      '@id' => $baseUrl . '/#website',
      'url' => $baseUrl . '/',
      'name' => 'Anne Hamrin Simonsson',
      'publisher' => ['@id' => $baseUrl . '/#person'],
    ],
    [
      '@type' => 'Organization',
      '@id' => $organizationId,
      'name' => 'Anne Hamrin Simonsson',
      'url' => $baseUrl . '/',
      'sameAs' => [
        'https://www.wikidata.org/wiki/Q137808007',
        'https://www.instagram.com/ahamrinsimonsson/',
        'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
      ],
      'contactPoint' => [
        [
          '@type' => 'ContactPoint',
          'contactType' => 'artwork inquiries',
          'url' => $baseUrl . '/contact',
          'availableLanguage' => ['en', 'sv'],
        ],
      ],
      'logo' => ['@id' => $logoId],
    ],
    [
      '@type' => 'ImageObject',
      '@id' => $logoId,
      'url' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'contentUrl' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'width' => 320,
      'height' => 320,
    ],
    [
      '@type' => 'Person',
      '@id' => $baseUrl . '/#person',
      'name' => 'Anne Hamrin Simonsson',
      'url' => $baseUrl . '/about',
      'image' => $personImageObject,
      'jobTitle' => 'Visual Artist',
      'description' => 'Anne Hamrin Simonsson is a Swedish conceptual and visual artist known for site-specific installations and objects.',
      'sameAs' => [
        'https://www.wikidata.org/wiki/Q137808007',
        'https://www.instagram.com/ahamrinsimonsson/',
        'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
      ],
    ],
    $projectWebPageNode,
    $projectNode,
    [
      '@type' => 'ImageGallery',
      '@id' => $projectUrl . '#gallery',
      'name' => $projectName . ' gallery',
      'url' => $projectUrl,
      'about' => ['@id' => $projectUrl . '#project'],
      'hasPart' => $allImageRefs,
      'numberOfItems' => count($allImageRefs),
    ],
    [
      '@type' => 'ItemList',
      '@id' => $projectUrl . '#highlights',
      'name' => 'Highlighted items',
      'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
      'numberOfItems' => count($highlightListItems),
      'itemListElement' => $highlightListItems,
    ],
    [
      '@type' => 'BreadcrumbList',
      '@id' => $projectUrl . '#breadcrumb',
      'itemListElement' => [
        [
          '@type' => 'ListItem',
          'position' => 1,
          'name' => 'Home',
          'item' => $baseUrl . '/'
        ],
        [
          '@type' => 'ListItem',
          'position' => 2,
          'name' => 'Artwork',
          'item' => $baseUrl . '/artwork'
        ],
        [
          '@type' => 'ListItem',
          'position' => 3,
          'name' => $projectName,
          'item' => $projectUrl
        ]
      ],
    ],
  ];

  foreach ($allImageNodes as $node) {
    $graph[] = $node;
  }
  foreach ($newsNodes as $node) {
    $graph[] = $node;
  }

  $jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => $graph,
  ];

  return json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}


function generateImageJsonLd(array $image, array $project): string
{
  $baseUrl = rtrim((string) base_url('/'), '/');
  $artistName = 'Anne Hamrin Simonsson';
  $organizationId = $baseUrl . '/#organization';
  $logoId = $baseUrl . '/#publisher-logo';
  $projectSlug = trim((string) ($project['slug'] ?? ''));
  $projectTitle = trim((string) ($project['title'] ?? 'Artwork project'));
  $fileId = trim((string) ($image['file_id'] ?? ''));
  $fileName = trim((string) ($image['file_name'] ?? ''));
  $imageSlug = $fileId !== '' ? rawurlencode($fileId) : rawurlencode((string) pathinfo($fileName, PATHINFO_FILENAME));
  $projectUrl = $projectSlug !== '' ? $baseUrl . '/' . rawurlencode($projectSlug) : $baseUrl . '/artwork';
  $pageUrl = $projectUrl . '/' . $imageSlug;
  $imageNodeId = $pageUrl . '#image';

  $normalizeText = static function (string $value): string {
    $value = strip_tags($value);
    $value = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1', $value) ?? $value;
    $value = str_replace(['*', '_', '`'], '', $value);
    $value = preg_replace('/\s+/', ' ', $value) ?? $value;
    return trim($value);
  };

  $truncateMeta = static function (string $text, int $maxLen): string {
    $text = trim($text);
    if ($text === '' || strlen($text) <= $maxLen) {
      return $text;
    }
    $cut = substr($text, 0, $maxLen + 1);
    $lastSpace = strrpos($cut, ' ');
    if ($lastSpace !== false && $lastSpace > (int)($maxLen * 0.6)) {
      $cut = substr($cut, 0, $lastSpace);
    } else {
      $cut = substr($cut, 0, $maxLen);
    }
    return rtrim($cut, ",;:. ") . '.';
  };

  $captionText = $normalizeText((string)($image['caption'] ?? ''));
  $titleText = $normalizeText((string)($image['title'] ?? ''));
  $altText = $normalizeText((string)($image['alternate_name'] ?? ''));
  $projectTitleText = $normalizeText($projectTitle);
  $dateText = $normalizeText((string)($image['date_created'] ?? ''));
  $artformText = $normalizeText((string)($image['artform'] ?? ''));
  $mediumText = $normalizeText((string)($image['art_medium'] ?? ''));
  $surfaceText = $normalizeText((string)($image['artwork_surface'] ?? ''));
  $locationText = $normalizeText((string)($image['geo_location'] ?? ''));

  $descriptionParts = [];
  if ($captionText !== '') {
    $descriptionParts[] = $captionText;
  }
  $identity = $titleText !== '' ? $titleText : 'Artwork';
  if ($altText !== '' && strcasecmp($altText, $identity) !== 0) {
    $identity .= ' (' . $altText . ')';
  }
  $identitySentence = $identity . ' by ' . $artistName;
  if ($projectTitleText !== '') {
    $identitySentence .= ' from the project ' . $projectTitleText;
  }
  $descriptionParts[] = $identitySentence;

  $extraBits = [];
  if ($dateText !== '') {
    $extraBits[] = 'created ' . $dateText;
  }
  if ($artformText !== '') {
    $extraBits[] = 'art form ' . $artformText;
  }
  if ($mediumText !== '') {
    $extraBits[] = 'medium ' . $mediumText;
  }
  if ($surfaceText !== '') {
    $extraBits[] = 'surface ' . $surfaceText;
  }
  if ($locationText !== '') {
    $extraBits[] = 'location ' . $locationText;
  }
  if (!empty($extraBits)) {
    $descriptionParts[] = implode(', ', $extraBits);
  }

  $jsonLdDescription = $normalizeText(implode('. ', array_filter($descriptionParts)));
  if ($jsonLdDescription !== '' && !preg_match('/[.!?]$/', $jsonLdDescription)) {
    $jsonLdDescription .= '.';
  }
  if (strlen($jsonLdDescription) < 50) {
    $jsonLdDescription .= ' Explore this artwork and related project details by ' . $artistName . '.';
  }
  $jsonLdDescription = $truncateMeta($normalizeText($jsonLdDescription), 240);

  $artworkNode = [
    '@type' => 'VisualArtwork',
    '@id' => $pageUrl . '#artwork',
    'name' => (string) ($image['title'] ?? 'Artwork'),
    'license' => $baseUrl . '/license.html',
    'creator' => [
      '@type' => 'Person',
      '@id' => $baseUrl . '/#person',
      'name' => 'Anne Hamrin Simonsson',
      'sameAs' => ['https://www.wikidata.org/wiki/Q137808007'],
    ],
    'isPartOf' => [
      '@type' => 'CreativeWorkSeries',
      '@id' => $projectUrl . '#project',
      'name' => $projectTitle,
      'url' => $projectUrl,
      'creator' => ['@id' => $baseUrl . '/#person'],
    ],
  ];

  $alternateName = trim((string) ($image['alternate_name'] ?? ''));
  if ($alternateName !== '') {
    $artworkNode['alternateName'] = $alternateName;
  }
  $caption = trim((string) ($image['caption'] ?? ''));
  if ($jsonLdDescription !== '') {
    $artworkNode['description'] = $jsonLdDescription;
  } elseif ($caption !== '') {
    $artworkNode['description'] = $caption;
  }
  $yearCreated = trim((string) ($image['date_created'] ?? ''));
  if ($yearCreated !== '') {
    $artworkNode['dateCreated'] = $yearCreated;
  }
  $artform = trim((string) ($image['artform'] ?? ''));
  $artworkNode['artform'] = $artform !== '' ? $artform : 'Visual Artwork';
  $artMedium = trim((string) ($image['art_medium'] ?? ''));
  if ($artMedium !== '') {
    $artworkNode['artMedium'] = $artMedium;
  }
  $artworkSurface = trim((string) ($image['artwork_surface'] ?? ''));
  if ($artworkSurface !== '') {
    $artworkNode['artworkSurface'] = $artworkSurface;
  }
  $artEdition = trim((string) ($image['art_edition'] ?? ''));
  if ($artEdition !== '') {
    $artworkNode['artEdition'] = $artEdition;
  }

  $heightCm = trim((string) ($image['height_cm'] ?? ''));
  $widthCm = trim((string) ($image['width_cm'] ?? ''));
  $depthCm = trim((string) ($image['depth_cm'] ?? ''));
  if ($heightCm !== '' && $widthCm !== '') {
    $artworkNode['height'] = ['@type' => 'Distance', 'name' => $heightCm . ' cm'];
    $artworkNode['width'] = ['@type' => 'Distance', 'name' => $widthCm . ' cm'];
    if ($depthCm !== '') {
      $artworkNode['depth'] = ['@type' => 'Distance', 'name' => $depthCm . ' cm'];
    }
  }

  $geoLocation = trim((string) ($image['geo_location'] ?? ''));
  if ($geoLocation !== '') {
    $placeNode = [
      '@type' => 'Place',
      'name' => $geoLocation,
    ];
    $mapUrl = trim((string) ($image['map_url'] ?? ''));
    if ($mapUrl !== '') {
      $placeNode['hasMap'] = $mapUrl;
    }
    $addressLocality = trim((string) ($image['address_locality'] ?? ''));
    $addressRegion = trim((string) ($image['address_region'] ?? ''));
    $addressCountry = trim((string) ($image['address_country'] ?? ''));
    if ($addressLocality !== '' || $addressRegion !== '' || $addressCountry !== '') {
      $addressNode = ['@type' => 'PostalAddress'];
      if ($addressLocality !== '') {
        $addressNode['addressLocality'] = $addressLocality;
      }
      if ($addressRegion !== '') {
        $addressNode['addressRegion'] = $addressRegion;
      }
      if ($addressCountry !== '') {
        $addressNode['addressCountry'] = $addressCountry;
      }
      $placeNode['address'] = $addressNode;
    }
    $artworkNode['locationCreated'] = $placeNode;
  }

  $imageObjectNode = [
    '@type' => 'ImageObject',
    '@id' => $imageNodeId,
    'url' => $baseUrl . '/konst/' . $fileName,
    'contentUrl' => $baseUrl . '/konst/' . $fileName,
    'thumbnailUrl' => $baseUrl . '/konst/thumb/' . $fileName,
    'name' => (string) ($image['title'] ?? 'Artwork image'),
    'license' => $baseUrl . '/license.html',
    'acquireLicensePage' => $baseUrl . '/license.html',
    'creditText' => 'Anne Hamrin Simonsson',
    'copyrightNotice' => '© 2009-2026 Anne Hamrin Simonsson. All rights reserved.',
    'creator' => [
      '@type' => 'Person',
      'name' => trim((string) ($image['photographer_name'] ?? '')) !== ''
        ? trim((string) ($image['photographer_name'] ?? ''))
        : $artistName,
    ],
  ];
  if ($jsonLdDescription !== '') {
    $imageObjectNode['description'] = $jsonLdDescription;
  }

  $widthPx = isset($image['width_px']) ? (int) $image['width_px'] : 0;
  $heightPx = isset($image['height_px']) ? (int) $image['height_px'] : 0;
  if ($widthPx > 0) {
    $imageObjectNode['width'] = $widthPx;
  }
  if ($heightPx > 0) {
    $imageObjectNode['height'] = $heightPx;
  }
  $extension = strtolower((string) pathinfo($fileName, PATHINFO_EXTENSION));
  if ($extension !== '') {
    $imageObjectNode['encodingFormat'] = 'image/' . $extension;
  }

  $imageObjectInline = [
    '@type' => 'ImageObject',
    '@id' => $imageNodeId,
    'url' => $imageObjectNode['url'],
    'contentUrl' => $imageObjectNode['contentUrl'],
    'thumbnailUrl' => $imageObjectNode['thumbnailUrl'],
    'name' => $imageObjectNode['name'],
  ];
  if ($widthPx > 0) {
    $imageObjectInline['width'] = $widthPx;
  }
  if ($heightPx > 0) {
    $imageObjectInline['height'] = $heightPx;
  }

  $portraitImageInline = [
    '@type' => 'ImageObject',
    '@id' => $logoId,
    'url' => base_url('anne-hamrin-simonsson-portrait.jpg'),
    'contentUrl' => base_url('anne-hamrin-simonsson-portrait.jpg'),
    'width' => 320,
    'height' => 320,
  ];

  $artworkNode['image'] = $imageObjectInline;

  $webPageNode = [
    '@type' => 'WebPage',
    '@id' => $pageUrl . '#webpage',
    'url' => $pageUrl,
    'name' => (string) ($image['title'] ?? 'Artwork image'),
    'description' => $jsonLdDescription !== '' ? $jsonLdDescription : ($captionText !== '' ? $captionText : ('Artwork by ' . $artistName . '.')),
    'image' => $imageObjectInline,
    'isPartOf' => ['@id' => $baseUrl . '/#website'],
    'mainEntity' => ['@id' => $pageUrl . '#artwork'],
    'primaryImageOfPage' => $imageObjectInline,
    'breadcrumb' => ['@id' => $pageUrl . '#breadcrumb'],
  ];

  $jsonLd = [
    '@context' => 'https://schema.org',
    '@graph' => [
      [
        '@type' => 'WebSite',
        '@id' => $baseUrl . '/#website',
        'url' => $baseUrl . '/',
        'name' => 'Anne Hamrin Simonsson',
        'publisher' => ['@id' => $baseUrl . '/#person'],
      ],
      [
        '@type' => 'Organization',
        '@id' => $organizationId,
        'name' => 'Anne Hamrin Simonsson',
        'url' => $baseUrl . '/',
        'sameAs' => [
          'https://www.wikidata.org/wiki/Q137808007',
          'https://www.instagram.com/ahamrinsimonsson/',
          'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
        ],
        'contactPoint' => [
          [
            '@type' => 'ContactPoint',
            'contactType' => 'artwork inquiries',
            'url' => $baseUrl . '/contact',
            'availableLanguage' => ['en', 'sv'],
          ],
        ],
        'logo' => ['@id' => $logoId],
      ],
      [
        '@type' => 'ImageObject',
        '@id' => $logoId,
        'url' => base_url('anne-hamrin-simonsson-portrait.jpg'),
        'contentUrl' => base_url('anne-hamrin-simonsson-portrait.jpg'),
        'width' => 320,
        'height' => 320,
      ],
      [
        '@type' => 'Person',
        '@id' => $baseUrl . '/#person',
        'name' => 'Anne Hamrin Simonsson',
        'url' => $baseUrl . '/about',
        'image' => $portraitImageInline,
        'jobTitle' => 'Visual Artist',
        'description' => 'Anne Hamrin Simonsson is a Swedish conceptual and visual artist known for site-specific installations and objects.',
        'sameAs' => [
          'https://www.wikidata.org/wiki/Q137808007',
          'https://www.instagram.com/ahamrinsimonsson/',
          'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
        ],
      ],
      $webPageNode,
      $artworkNode,
      $imageObjectNode,
      [
        '@type' => 'BreadcrumbList',
        '@id' => $pageUrl . '#breadcrumb',
        'itemListElement' => [
          ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => $baseUrl . '/'],
          ['@type' => 'ListItem', 'position' => 2, 'name' => 'Artwork', 'item' => $baseUrl . '/artwork'],
          ['@type' => 'ListItem', 'position' => 3, 'name' => $projectTitle, 'item' => $projectUrl],
          ['@type' => 'ListItem', 'position' => 4, 'name' => (string) ($image['title'] ?? 'Image'), 'item' => $pageUrl],
        ],
      ],
    ],
  ];

  return json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}


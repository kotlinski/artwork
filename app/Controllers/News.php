<?php

namespace App\Controllers;

use App\Libraries\ParsedownWithLinkTargets;
use CodeIgniter\HTTP\Files\UploadedFile;

class News extends BaseController
{
  protected const NEWS_CATEGORIES = ['exhibition', 'talk', 'workshop', 'general'];

  public function index(){
    $model = new \App\Models\NewsModel();
    $news_items = $model->getLatestNews();
    $news_items = $this->normalizeMainImagePaths($news_items);
    $lcpImageUrl = '';
    foreach (array_slice($news_items, 0, 3) as $candidate) {
      if (!empty($candidate['main_image'])) {
        $lcpImageUrl = base_url($candidate['main_image_medium'] ?? $candidate['main_image']);
        break;
      }
    }
    
    $parser = new ParsedownWithLinkTargets();
    $parser->setSafeMode(true);
    $parser->setBreaksEnabled(true);

    $news_items = $this->addParsedContent($news_items, $parser);

    $projectModel = new \App\Models\Project();
    $projects = $projectModel->orderBy('sort_order', 'ASC')->findAll();

    $required = [
      'title' => 'News | Anne Hamrin Simonsson',
      'selected_menu_item' => 'news',
      'description' => 'Keep up with Anne Hamrin Simonsson’s latest news, from Swedish Arts Grants Committee awards to current exhibitions at Kalmar Konstmuseum and more.',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
      'lcp_image_url' => $lcpImageUrl,
    ];
    
    $page_specific = [
      'news_items' => $news_items,
      'projects'   => $projects,
    ];
    
    return $this->renderView('news/news_page', $required, $page_specific);

  }

  protected function addParsedContent(array $news_items, \Parsedown $parser): array
  {
    return array_map(static function (array $item) use ($parser): array {
      $item['content_parsed'] = $parser->text($item['content'] ?? '');

      return $item;
    }, $news_items);
  }

  protected function normalizeMainImagePaths(array $newsItems): array
  {
    return array_map(function (array $item): array {
      $mainImage = $item['main_image'] ?? null;
      if (is_string($mainImage) && str_starts_with($mainImage, 'news/')) {
        $item['main_image'] = 'media/news/' . ltrim(substr($mainImage, 5), '/');
      }

      $mainImage = $item['main_image'] ?? null;
      if (is_string($mainImage) && str_starts_with($mainImage, 'media/news/')) {
        $basename = basename($mainImage);
        $mediumPath = 'media/news/medium/' . $basename;
        $largePath  = 'media/news/large/'  . $basename;

        $item['main_image_mini']   = null;
        $item['main_image_thumb']  = null;
        $item['main_image_medium'] = is_file(FCPATH . $mediumPath) ? $mediumPath : $mainImage;
        $item['main_image_large']  = is_file(FCPATH . $largePath)  ? $largePath  : $item['main_image_medium'];

        // Always derive display dimensions from the medium file — it is already
        // reoriented and resized, so its pixel dimensions are the ground truth.
        $mediumFilePath = FCPATH . $item['main_image_medium'];
        $dims = @getimagesize($mediumFilePath);
        if ($dims && $dims[0] > 0 && $dims[1] > 0) {
          $item['main_image_width']  = (int) $dims[0];
          $item['main_image_height'] = (int) $dims[1];
        } else {
          // Fallback to stored DB values if medium file can't be read.
          $storedWidth  = isset($item['width_px'])  ? (int) $item['width_px']  : 0;
          $storedHeight = isset($item['height_px']) ? (int) $item['height_px'] : 0;
          if ($storedWidth > 0 && $storedHeight > 0) {
            $item['main_image_width']  = $storedWidth;
            $item['main_image_height'] = $storedHeight;
          }
        }
      }

      return $item;
    }, $newsItems);
  }

  protected function getStoredImageDimensions(string $relativePath): array
  {
    $dimensions = @getimagesize(FCPATH . ltrim($relativePath, '/'));

    if (is_array($dimensions) && isset($dimensions[0], $dimensions[1]) && $dimensions[0] > 0 && $dimensions[1] > 0) {
      return [
        'width_px' => (int) $dimensions[0],
        'height_px' => (int) $dimensions[1],
      ];
    }

    return [
      'width_px' => null,
      'height_px' => null,
    ];
  }
  
  public function store()
  {
    if (!session()->get('isLoggedIn')) {
      return redirect()->to('/login');
    }

    $title = trim($this->request->getPost('title') ?? '');
    $slug = $this->normalizeSlug($title);
    $content = $this->request->getPost('content') ?? '';
    $projectId = $this->request->getPost('project_id');
    $category = $this->normalizeCategory($this->request->getPost('category'));
    $eventLocation = trim($this->request->getPost('event_location') ?? '');
    $eventStartDate = $this->normalizeOptionalDate($this->request->getPost('event_start_date'));
    $eventEndDate = $this->normalizeOptionalDate($this->request->getPost('event_end_date'));
    $externalLink = trim($this->request->getPost('external_link') ?? '');
    $mainImageFile = $this->request->getFile('main_image_file');
    $hasMainImageUpload = $this->hasUploadedFile($mainImageFile);

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if ($slug === '') $errors[] = 'Title must produce a valid slug.';
    if (!preg_match('/^[a-z0-9\-]+$/', $slug)) $errors[] = 'Slug may only contain lowercase letters, numbers and hyphens.';
    if (!in_array($category, self::NEWS_CATEGORIES, true)) $errors[] = 'Invalid category.';
    if ($externalLink !== '' && filter_var($externalLink, FILTER_VALIDATE_URL) === false) $errors[] = 'External link must be a valid URL.';
    if ($eventStartDate !== null && $eventEndDate !== null && $eventEndDate < $eventStartDate) $errors[] = 'Event end date cannot be earlier than event start date.';
    if ($hasMainImageUpload) {
      $uploadError = $this->validateMainImageFile($mainImageFile);
      if ($uploadError !== null) {
        $errors[] = $uploadError;
      }
    }

    $model = new \App\Models\NewsModel();
    if (empty($errors)) {
      $baseSlug = $slug;
      $counter = 2;
      while ($model->where('slug', $slug)->first()) {
        $slug = $baseSlug . '-' . $counter;
        $counter++;
      }
    }

    if (!empty($errors)) {
      return redirect()->to('/news')
        ->with('create_errors', $errors)
        ->with('create_title', $title)
        ->with('create_slug', $slug)
        ->with('create_content', $content)
        ->with('create_project_id', $projectId ?? '')
        ->with('create_category', $category)
        ->with('create_event_location', $eventLocation)
        ->with('create_event_start_date', $eventStartDate ?? '')
        ->with('create_event_end_date', $eventEndDate ?? '')
        ->with('create_external_link', $externalLink);
    }

    $mainImagePath = null;
    $mainImageWidth = null;
    $mainImageHeight = null;
    if ($hasMainImageUpload) {
      try {
        $savedMainImage = $this->saveNewsMainImageVariants($mainImageFile, $slug);
        $mainImagePath = $savedMainImage['path'];
        $mainImageWidth = $savedMainImage['width_px'];
        $mainImageHeight = $savedMainImage['height_px'];
      } catch (\Throwable $e) {
        return redirect()->to('/news')
          ->with('create_errors', ['Failed to process main image upload.'])
          ->with('create_title', $title)
          ->with('create_slug', $slug)
          ->with('create_content', $content)
          ->with('create_project_id', $projectId ?? '')
          ->with('create_category', $category)
          ->with('create_event_location', $eventLocation)
          ->with('create_event_start_date', $eventStartDate ?? '')
          ->with('create_event_end_date', $eventEndDate ?? '')
          ->with('create_external_link', $externalLink);
      }
    }

    $data = [
      'title'      => $title,
      'slug'       => $slug,
      'content'    => $content,
      'category'   => $category,
      'created_at' => date('Y-m-d H:i:s'),
    ];
    if (!empty($projectId)) {
      $data['project_id'] = (int) $projectId;
    }
    $data['main_image'] = $mainImagePath;
    $data['width_px'] = $mainImageWidth;
    $data['height_px'] = $mainImageHeight;
    $data['event_location'] = $eventLocation !== '' ? $eventLocation : null;
    $data['event_start_date'] = $eventStartDate;
    $data['event_end_date'] = $eventEndDate;
    $data['external_link'] = $externalLink !== '' ? $externalLink : null;

    $model->insert($data);
    $id = $model->getInsertID();

    return redirect()->to('/news#news-' . $slug)->with('success', 'Article created.');
  }

  protected function normalizeSlug(string $value): string
  {
    $slug = str_replace(
      ['Å', 'Ä', 'Ö', 'å', 'ä', 'ö'],
      ['a', 'a', 'o', 'a', 'a', 'o'],
      $value
    );

    $slug = strtolower($slug);
    $slug = preg_replace('/\s+/', '-', $slug) ?? '';
    $slug = preg_replace('/[^a-z0-9\-]/', '', $slug) ?? '';
    $slug = preg_replace('/-+/', '-', $slug) ?? '';

    return trim($slug, '-');
  }

  protected function normalizeCategory($value): string
  {
    $category = strtolower(trim((string) ($value ?? 'general')));

    return in_array($category, self::NEWS_CATEGORIES, true) ? $category : 'general';
  }

  protected function normalizeOptionalDate($value): ?string
  {
    $value = trim((string) ($value ?? ''));

    return $value !== '' ? $value : null;
  }

  protected function hasUploadedFile(?UploadedFile $file): bool
  {
    return $file !== null && $file->getError() !== UPLOAD_ERR_NO_FILE;
  }

  protected function validateMainImageFile(?UploadedFile $file): ?string
  {
    if ($file === null || !$file->isValid()) {
      return 'Main image upload failed.';
    }

    if ($file->getSize() > 20 * 1024 * 1024) {
      return 'Main image may not be larger than 20 MB.';
    }

    $ext = strtolower($file->getClientExtension());
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
      return 'Main image must be jpg, jpeg, png, or webp.';
    }

    return null;
  }

  protected function saveNewsMainImageVariants(UploadedFile $file, string $slug): array
  {
    $baseName = $this->resolveNewsMainImageBaseName($slug);
    $origExt = strtolower($file->getClientExtension());
    $origName = $baseName . '.' . $origExt;
    $webpName = $baseName . '.webp';

    $newsDir = FCPATH . 'media/news/';
    $originalDir = $newsDir . 'original/';
    if (!is_dir($originalDir) && !mkdir($originalDir, 0775, true) && !is_dir($originalDir)) {
      throw new \RuntimeException('Failed to create news original directory.');
    }

    $file->move($originalDir, $origName, true);
    $origPath = $originalDir . $origName;

    $filesize = @filesize($origPath) ?: 0;
    if ($filesize > 3145728) {
      $quality = 43;
    } elseif ($filesize > 2097152) {
      $quality = 63;
    } elseif ($filesize > 1048576) {
      $quality = 73;
    } else {
      $quality = 87;
    }

    helper('webp');

    // Root: full reoriented image used for fullscreen mode.
    generate_webp_variant($origPath, $newsDir . $webpName, $quality);

    // Two variants: medium (1x) and large (2x), both fit within their bounding box.
    $variants = [
      'medium/' => ['maxW' => 380, 'maxH' => 280, 'quality' => min($quality, 65)],
      'large/'  => ['maxW' => 760, 'maxH' => 560, 'quality' => min($quality, 72)],
    ];

    foreach ($variants as $subdir => $opts) {
      $targetDir = $newsDir . $subdir;
      if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new \RuntimeException('Failed to create news variant directory.');
      }
      $outPath = $targetDir . $webpName;
      generate_webp_fit($origPath, $outPath, $opts['maxW'], $opts['maxH'], $opts['quality']);
    }

    $relativePath = 'media/news/' . $webpName;
    $mediumPath = 'media/news/medium/' . $webpName;
    $dimensions = $this->getStoredImageDimensions($mediumPath);

    return [
      'path' => $relativePath,
      'width_px' => $dimensions['width_px'],
      'height_px' => $dimensions['height_px'],
    ];
  }

  protected function resolveNewsMainImageBaseName(string $slug): string
  {
    $cleanSlug = trim($slug) !== '' ? trim($slug) : 'item';
    $base = 'anne-hamrin-simonsson-news-' . $cleanSlug;
    $newsDir = FCPATH . 'media/news/';

    // Keep names readable: only add a numeric suffix when a file already exists.
    $candidate = $base;
    $suffix = 2;
    while ($this->newsMainImageBasenameExists($candidate, $newsDir)) {
      $candidate = $base . '-' . $suffix;
      $suffix++;
    }

    return $candidate;
  }

  protected function newsMainImageBasenameExists(string $basename, string $newsDir): bool
  {
    $webpPaths = [
      $newsDir . $basename . '.webp',
      $newsDir . 'medium/' . $basename . '.webp',
      $newsDir . 'large/' . $basename . '.webp',
    ];

    foreach ($webpPaths as $path) {
      if (is_file($path)) {
        return true;
      }
    }

    return !empty(glob($newsDir . 'original/' . $basename . '.*') ?: []);
  }

  protected function deleteNewsMainImageVariants(string $storedPath): void
  {
    $path = trim($storedPath);
    if ($path === '') {
      return;
    }

    if (str_starts_with($path, 'media/news/')) {
      $basename = basename(substr($path, strlen('media/news/')));
    } elseif (str_starts_with($path, 'news/')) {
      $basename = basename(substr($path, strlen('news/')));
    } else {
      $basename = basename($path);
    }

    if ($basename === '' || $basename === '.' || $basename === '..') {
      return;
    }

    $newsDir = FCPATH . 'media/news/';
    $variantDirs = ['', 'mini/', 'thumb/', 'medium/', 'large/'];
    foreach ($variantDirs as $subdir) {
      $candidate = $newsDir . $subdir . $basename;
      if (is_file($candidate)) {
        @unlink($candidate);
      }
    }

    $nameNoExt = pathinfo($basename, PATHINFO_FILENAME);
    if ($nameNoExt !== '') {
      foreach (glob($newsDir . 'original/' . $nameNoExt . '.*') ?: [] as $original) {
        if (is_file($original)) {
          @unlink($original);
        }
      }
    }
  }

  public function update()
  {
    if (!session()->get('isLoggedIn')) {
      return redirect()->to('/login');
    }

    $id = (int) $this->request->getPost('id');
    if ($id <= 0) {
      return redirect()->to('/news')->with('error', 'Invalid news item.')->withInput();
    }

    $model = new \App\Models\NewsModel();
    $existing = $model->find($id);
    if (!$existing) {
      return redirect()->to('/news')->with('error', 'News item not found.')->withInput();
    }

    $data = [];
    $title = $this->request->getPost('title');
    $content = $this->request->getPost('content');
    $projectId = $this->request->getPost('project_id');
    $category = $this->request->getPost('category');
    $eventLocation = $this->request->getPost('event_location');
    $eventStartDate = $this->request->getPost('event_start_date');
    $eventEndDate = $this->request->getPost('event_end_date');
    $externalLink = $this->request->getPost('external_link');
    $removeMainImage = in_array($this->request->getPost('remove_main_image'), ['1', 1, true, 'true', 'on'], true);
    $mainImageFile = $this->request->getFile('main_image_file');
    $hasMainImageUpload = $this->hasUploadedFile($mainImageFile);

    if ($title !== null) {
      $data['title'] = $title;
    }
    if ($content !== null) {
      $data['content'] = $content;
    }
    if ($projectId !== null) {
      $data['project_id'] = $projectId !== '' ? (int) $projectId : null;
    }
    if ($category !== null) {
      $data['category'] = $this->normalizeCategory($category);
    }
    if ($removeMainImage) {
      $data['main_image'] = null;
      $data['width_px'] = null;
      $data['height_px'] = null;
    }
    if ($hasMainImageUpload) {
      $uploadError = $this->validateMainImageFile($mainImageFile);
      if ($uploadError !== null) {
        return redirect()->to('/news')->with('error', $uploadError)->withInput();
      }

      try {
        $savedMainImage = $this->saveNewsMainImageVariants($mainImageFile, $this->normalizeSlug((string) ($title ?? ('news-item-' . $id))));
        $data['main_image'] = $savedMainImage['path'];
        $data['width_px'] = $savedMainImage['width_px'];
        $data['height_px'] = $savedMainImage['height_px'];
      } catch (\Throwable $e) {
        return redirect()->to('/news')->with('error', 'Failed to process main image upload.')->withInput();
      }
    }
    if ($eventLocation !== null) {
      $eventLocation = trim((string) $eventLocation);
      $data['event_location'] = $eventLocation !== '' ? $eventLocation : null;
    }
    if ($eventStartDate !== null) {
      $data['event_start_date'] = $this->normalizeOptionalDate($eventStartDate);
    }
    if ($eventEndDate !== null) {
      $data['event_end_date'] = $this->normalizeOptionalDate($eventEndDate);
    }
    if ($externalLink !== null) {
      $externalLink = trim((string) $externalLink);
      $data['external_link'] = $externalLink !== '' ? $externalLink : null;
    }

    if (
      isset($data['event_start_date'], $data['event_end_date'])
      && $data['event_start_date'] !== null
      && $data['event_end_date'] !== null
      && $data['event_end_date'] < $data['event_start_date']
    ) {
      return redirect()->to('/news')->with('error', 'Event end date cannot be earlier than event start date.')->withInput();
    }

    if (isset($data['external_link']) && $data['external_link'] !== null && filter_var($data['external_link'], FILTER_VALIDATE_URL) === false) {
      return redirect()->to('/news')->with('error', 'External link must be a valid URL.')->withInput();
    }

    $oldMainImage = (string) ($existing['main_image'] ?? '');
    $newMainImage = array_key_exists('main_image', $data) ? (string) ($data['main_image'] ?? '') : $oldMainImage;
    if ($oldMainImage !== '' && $oldMainImage !== $newMainImage) {
      $this->deleteNewsMainImageVariants($oldMainImage);
    }

    if (!empty($data)) {
      $model->update($id, $data);
    }

    $item = $model->find($id);
    $slug = $item['slug'] ?? $id;

    return redirect()->to('/news#news-' . $slug)->with('success', 'News updated.');
  }

  public function delete()
  {
    if (!session()->get('isLoggedIn')) {
      return redirect()->to('/login');
    }

    $id = (int) $this->request->getPost('id');
    if ($id <= 0) {
      return redirect()->to('/news')->with('error', 'Invalid news item.');
    }

    $model = new \App\Models\NewsModel();
    $item = $model->find($id);

    if (!$item) {
      return redirect()->to('/news')->with('error', 'News item not found.');
    }

    $model->delete($id);

    return redirect()->to('/news')->with('success', 'News deleted.');
  }
}


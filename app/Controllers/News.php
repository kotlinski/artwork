<?php

namespace App\Controllers;

use CodeIgniter\HTTP\Files\UploadedFile;

class News extends BaseController
{
  protected const NEWS_CATEGORIES = ['exhibition', 'talk', 'workshop', 'general'];

  public function index(){
    $model = new \App\Models\NewsModel();
    $news_items = $model->getLatestNews();
    $news_items = $this->normalizeMainImagePaths($news_items);
    
    $parser = new \Parsedown();
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

      return $item;
    }, $newsItems);
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
    if (empty($errors) && $model->where('slug', $slug)->first()) {
      $errors[] = 'A news item with that slug already exists.';
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
    if ($hasMainImageUpload) {
      try {
        $mainImagePath = $this->saveNewsMainImageVariants($mainImageFile, $slug);
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
    $data['event_location'] = $eventLocation !== '' ? $eventLocation : null;
    $data['event_start_date'] = $eventStartDate;
    $data['event_end_date'] = $eventEndDate;
    $data['external_link'] = $externalLink !== '' ? $externalLink : null;

    $model->insert($data);
    $id = $model->getInsertID();

    return redirect()->to('/news#news-admin-item-' . $id)->with('success', 'Article created.');
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

  protected function saveNewsMainImageVariants(UploadedFile $file, string $slug): string
  {
    $baseName = 'anne-hamrin-simonsson-news-' . ($slug !== '' ? $slug : 'item') . '-' . date('YmdHis') . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
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

    $variants = [
      '' => '',
      'mini/' => 'x70',
      'thumb/' => 'x140',
      'medium/' => 'x280',
      'large/' => 'x560',
    ];

    foreach ($variants as $subdir => $resize) {
      $targetDir = $newsDir . $subdir;
      if (!is_dir($targetDir) && !mkdir($targetDir, 0775, true) && !is_dir($targetDir)) {
        throw new \RuntimeException('Failed to create news variant directory.');
      }

      $outPath = $targetDir . $webpName;
      $image = \Config\Services::image('imagick');
      $image->withFile($origPath);
      $image->reorient();
      $image->convert(IMAGETYPE_WEBP);
      $image->quality($quality);

      if ($resize !== '') {
        $height = (int) str_replace('x', '', $resize);
        $image->resize(0, $height, true);
      }

      $image->save($outPath);
    }

    return 'media/news/' . $webpName;
  }

  public function update()
  {
    if (!session()->get('isLoggedIn')) {
      return redirect()->to('/login');
    }

    $id = (int) $this->request->getPost('id');
    if ($id <= 0) {
      return redirect()->to('/news')->with('error', 'Invalid news item.');
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
    if ($hasMainImageUpload) {
      $uploadError = $this->validateMainImageFile($mainImageFile);
      if ($uploadError !== null) {
        return redirect()->to('/news')->with('error', $uploadError);
      }

      try {
        $data['main_image'] = $this->saveNewsMainImageVariants($mainImageFile, $this->normalizeSlug((string) ($title ?? ('news-item-' . $id))));
      } catch (\Throwable $e) {
        return redirect()->to('/news')->with('error', 'Failed to process main image upload.');
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
      return redirect()->to('/news')->with('error', 'Event end date cannot be earlier than event start date.');
    }

    if (isset($data['external_link']) && $data['external_link'] !== null && filter_var($data['external_link'], FILTER_VALIDATE_URL) === false) {
      return redirect()->to('/news')->with('error', 'External link must be a valid URL.');
    }

    if (!empty($data)) {
      $model = new \App\Models\NewsModel();
      $model->update($id, $data);
    }

    return redirect()->to('/news#news-admin-item-' . $id)->with('success', 'News updated.');
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


<?php

namespace App\Controllers;

class News extends BaseController
{
  protected const NEWS_CATEGORIES = ['exhibition', 'talk', 'workshop', 'general'];

  public function index(){
    $model = new \App\Models\NewsModel();
    $news_items = $model->getLatestNews();
    
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

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if ($slug === '') $errors[] = 'Title must produce a valid slug.';
    if (!preg_match('/^[a-z0-9\-]+$/', $slug)) $errors[] = 'Slug may only contain lowercase letters, numbers and hyphens.';
    if (!in_array($category, self::NEWS_CATEGORIES, true)) $errors[] = 'Invalid category.';
    if ($externalLink !== '' && filter_var($externalLink, FILTER_VALIDATE_URL) === false) $errors[] = 'External link must be a valid URL.';
    if ($eventStartDate !== null && $eventEndDate !== null && $eventEndDate < $eventStartDate) $errors[] = 'Event end date cannot be earlier than event start date.';

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


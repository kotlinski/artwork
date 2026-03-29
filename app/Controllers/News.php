<?php

namespace App\Controllers;

class News extends BaseController
{
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

    $title     = trim($this->request->getPost('title') ?? '');
    $slug      = trim($this->request->getPost('slug') ?? '');
    $content   = $this->request->getPost('content') ?? '';
    $projectId = $this->request->getPost('project_id');

    $errors = [];
    if ($title === '') $errors[] = 'Title is required.';
    if ($slug  === '') $errors[] = 'Slug is required.';
    if (!preg_match('/^[a-z0-9\-]+$/', $slug)) $errors[] = 'Slug may only contain lowercase letters, numbers and hyphens.';

    $model = new \App\Models\NewsModel();
    if (empty($errors) && $model->where('slug', $slug)->first()) {
      $errors[] = 'A news item with that slug already exists.';
    }

    if (!empty($errors)) {
      return redirect()->to('/news')
        ->with('create_errors', $errors)
        ->with('create_title', $title)
        ->with('create_slug', $slug)
        ->with('create_content', $content);
    }

    $data = [
      'title'      => $title,
      'slug'       => $slug,
      'content'    => $content,
      'created_at' => date('Y-m-d H:i:s'),
    ];
    if (!empty($projectId)) {
      $data['project_id'] = (int) $projectId;
    }

    $model->insert($data);
    $id = $model->getInsertID();

    return redirect()->to('/news#news-admin-item-' . $id)->with('success', 'Article created.');
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

    if ($title !== null) {
      $data['title'] = $title;
    }
    if ($content !== null) {
      $data['content'] = $content;
    }
    if ($projectId !== null) {
      $data['project_id'] = $projectId !== '' ? (int) $projectId : null;
    }

    if (!empty($data)) {
      $model = new \App\Models\NewsModel();
      $model->update($id, $data);
    }

    return redirect()->to('/news#news-admin-item-' . $id)->with('success', 'News updated.');
  }
}
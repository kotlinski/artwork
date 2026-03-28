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

    if ($title !== null) {
      $data['title'] = $title;
    }
    if ($content !== null) {
      $data['content'] = $content;
    }

    if (!empty($data)) {
      $model = new \App\Models\NewsModel();
      $model->update($id, $data);
    }

    return redirect()->to('/news#news-admin-item-' . $id)->with('success', 'News updated.');
  }
}
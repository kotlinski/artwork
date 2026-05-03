<?php

namespace App\Controllers;

use App\Libraries\ParsedownWithLinkTargets;

class About extends BaseController
{
  
  public function index()
  {
    $model = new \App\Models\About();
    $about = $model->orderBy('id', 'DESC')->first();;
    
    $parser = new ParsedownWithLinkTargets();
    $parser->setSafeMode(true);
    $parser->setBreaksEnabled(true);
    
    $required = [
      'title' => 'About | Anne Hamrin Simonsson',
      'selected_menu_item' => 'about',
      'description' => 'Biography and artist statement of Anne Hamrin Simonsson, a Swedish conceptual artist based on Öland, known for her site-specific installations and objects.',
      'meta_keywords' => 'Anne Hamrin Simonsson, about, biography, CV, artist statement, conceptual art, visual artist, Swedish artist, installation art',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
    ];
    
    $page_specific = [
      'about' => $about,
      'about_text' => $parser->text($about['text'] ?? ''),
    ];
    
    return $this->renderView('about_view', $required, $page_specific);
  }
  
  public function update()
  {
    // Security check: ensure only logged in users can post here
    if (!session()->get('is_logged_in')) {
      return redirect()->to('/login');
    }
    
    $model = new \App\Models\About();
    $id = $this->request->getPost('id');
    
    $model->update($id, [
      'text' => $this->request->getPost('about_text')
    ]);
    
    return redirect()->to('/about')->with('success', 'Updated!');
  }
}

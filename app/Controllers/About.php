<?php

namespace App\Controllers;

class About extends BaseController
{
  
  public function index()
  {
    $model = new \App\Models\About();
    $about = $model->orderBy('id', 'DESC')->first();;
    
    $parser = new \Parsedown();
    
    $required = [
      'title' => 'About | Anne Hamrin Simonsson',
      'selected_menu_item' => 'about',
      'description' => 'Biography and artist statement of Anne Hamrin Simonsson, a Swedish conceptual artist based on Öland, known for her site-specific installations and objects.',
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
    if (!session()->get('isLoggedIn')) {
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

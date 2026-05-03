<?php

namespace App\Controllers;

class Contact extends BaseController
{
  
  public function index()
  {
    $model = new \App\Models\Contact();
    $contact = $model->orderBy('id', 'DESC')->first();;
    
    $parser = new \Parsedown();
    
    $required = [
      'title' => 'Contact | Anne Hamrin Simonsson',
      'selected_menu_item' => 'contact',
      'description' => 'Get in touch with Anne Hamrin Simonsson for inquiries, collaborations, or information regarding her conceptual art installations and paintings in Sweden.',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
    ];
    
    $page_specific = [
      'contact' => $contact,
      'contact_text' => $parser->text($contact['text'] ?? ''),
    ];
    
    return $this->renderView('contact_view', $required, $page_specific);
  }
  public function update()
  {
    // Security check: ensure only logged in users can post here
    if (!session()->get('is_logged_in')) {
      return redirect()->to('/login');
    }
    
    $model = new \App\Models\Contact();
    $id = $this->request->getPost('id');
    
    $model->update($id, [
      'text' => $this->request->getPost('contact_text')
    ]);
    
    return redirect()->to('/contact')->with('success', 'Updated!');
  }
}

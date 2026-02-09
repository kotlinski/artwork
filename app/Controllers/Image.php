<?php

namespace App\Controllers;

use App\Models\Project;

class Image extends BaseController
{
  public function admin()
  {
    $imageModel = new Image();
    $projectModel = new Project();
    $images = $imageModel->orderBy('project', 'ASC')->orderBy('order', 'ASC')->findAll();
    $projects = $projectModel->orderBy('title', 'ASC')->findAll();
    
    // Group images by project
    $grouped = [];
    foreach ($images as $img) {
      $grouped[$img['project']][] = $img;
    }
    $required = [
      'title' => 'Artwork | Anne Hamrin Simonsson',
      'selected_menu_item' => 'artwork',
      'description' => 'Biography and artist statement of Anne Hamrin Simonsson, a Swedish conceptual artist based on Öland, known for her site-specific installations and objects.',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
    ];
    
    return $this->renderView('image_admin', $required, [
      'groupedImages' => $grouped,
      'projects' => $projects
    ]);
  }
  
  public function update($id)
  {
    $imageModel = new Image();
    $data = $this->request->getPost();
    $imageModel->update($id, $data);
    return redirect()->to('/image/admin')->with('success', 'Image updated.');
  }
}


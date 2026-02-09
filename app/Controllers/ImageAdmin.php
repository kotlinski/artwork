<?php

namespace App\Controllers;

use App\Models\Image;
use App\Models\Project;

class ImageAdmin extends BaseController
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
    
    return $this->renderNonPublicView('artwork/image_admin', [
      'title' => 'Image Admin',
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


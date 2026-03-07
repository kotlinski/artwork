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
    foreach ($projects as &$project) {
      $project['images'] = [];
      foreach ($images as $img) {
        if ($img['project'] == $project['id']) {
          $project['images'][] = $img;
        }
      }
    }
    return $this->renderNonPublicView('artwork/manage_images', [
      'title' => 'Image Admin',
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

  public function moveUp($id)
  {
    $imageModel = new Image();
    $image = $imageModel->find($id);

    if (!$image) {
      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(404)->setJSON(['success' => false, 'moved' => false]);
      }
      return redirect()->to('/image/admin');
    }

    $imageAbove = $imageModel
      ->where('project', $image['project'])
      ->where('order <', $image['order'])
      ->orderBy('order', 'DESC')
      ->first();

    $moved = false;
    if ($imageAbove) {
      $imageModel->update($id, ['order' => $imageAbove['order']]);
      $imageModel->update($imageAbove['id'], ['order' => $image['order']]);
      $moved = true;
    }

    if ($this->request->isAJAX()) {
      return $this->response->setJSON(['success' => true, 'moved' => $moved]);
    }

    return redirect()->to('/image/admin');
  }

  public function moveDown($id)
  {
    $imageModel = new Image();
    $image = $imageModel->find($id);

    if (!$image) {
      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(404)->setJSON(['success' => false, 'moved' => false]);
      }
      return redirect()->to('/image/admin');
    }

    $imageBelow = $imageModel
      ->where('project', $image['project'])
      ->where('order >', $image['order'])
      ->orderBy('order', 'ASC')
      ->first();

    $moved = false;
    if ($imageBelow) {
      $imageModel->update($id, ['order' => $imageBelow['order']]);
      $imageModel->update($imageBelow['id'], ['order' => $image['order']]);
      $moved = true;
    }

    if ($this->request->isAJAX()) {
      return $this->response->setJSON(['success' => true, 'moved' => $moved]);
    }

    return redirect()->to('/image/admin');
  }
}

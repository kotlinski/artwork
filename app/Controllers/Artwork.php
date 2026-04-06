<?php

namespace App\Controllers;

use App\Models\Project;

class Artwork extends BaseController
{
  protected $projectModel;
  
  public function __construct()
  {
    $this->projectModel = new Project();
  }
  
  public function index()
  {
    $projectsQuery = $this->projectModel->orderBy('sort_order', 'ASC');
    if (!session()->get('isLoggedIn')) {
      $projectsQuery = $projectsQuery->where('is_published', 1);
    }
    $projects = $projectsQuery->findAll();
    $image_ids = array_unique(array_filter(array_merge(
      array_column($projects, 'image_left'),
      array_column($projects, 'image_mid'),
      array_column($projects, 'image_right')
    )));
    $image_model = new \App\Models\Image();
    
    $clean_ids = array_map('intval', array_filter($image_ids));
    $images_data = !empty($clean_ids) ? $image_model->whereIn('id', $clean_ids)->findAll() : [];
    $indexed_images = array_column($images_data, null, 'id');
    
    foreach ($projects as &$project) {
      if (session()->get('isLoggedIn')) {
        $project['images'] = $image_model->where('project', $project['id'])->orderBy('`order`', 'ASC')->findAll();
      }
      // 3. Cast the project values to (int) when looking them up
      $project['preview'] = [
        'left' => $indexed_images[(int)$project['image_left']] ?? null,
        'mid' => $indexed_images[(int)$project['image_mid']] ?? null,
        'right' => $indexed_images[(int)$project['image_right']] ?? null
      ];
    }
    $data['projects'] = $projects;
    $required = [
      'title' => 'Artwork | Anne Hamrin Simonsson',
      'selected_menu_item' => 'artwork',
      'description' => 'Biography and artist statement of Anne Hamrin Simonsson, a Swedish conceptual artist based on Öland, known for her site-specific installations and objects.',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
    ];
    return $this->renderView('artwork/projects.php', $required, $data);
  }
  
  public function admin()
  {
    $data['projects'] = $this->projectModel->orderBy('sort_order', 'DESC')->findAll(); // Newest first
    $data['title'] = 'Artwork Admin';
    return $this->renderNonPublicView('artwork/manage-projects', $data);
  }
  
  public function store()
  {
    try {
      $rules = [
        'title' => 'required|max_length[255]',
        'slug' => [
          'label' => 'Slug',
          'rules' => 'required|max_length[110]|regex_match[/^[a-z0-9\-]+$/]|is_unique[projects.slug]'
        ]
      ];
      $messages = [
        'slug' => [
          'regex_match' => 'The slug may only contain lowercase letters, numbers, and hyphens.'
        ]
      ];
      if (!$this->validate($rules, $messages)) {
        if ($this->request->isAJAX()) {
          return $this->response->setStatusCode(422)->setJSON([
            'success' => false,
            'errors' => $this->validator->getErrors()
          ]);
        }
        return redirect()->to('/artwork')->withInput()->with('errors', $this->validator->getErrors());
      }

      // Move all existing projects down by incrementing sort_order
      $this->projectModel->builder()->set('sort_order', 'sort_order + 1', false)->update();
      $data = [
        'title' => $this->request->getPost('title'),
        'slug' => $this->request->getPost('slug'),
      ];

      if ($this->projectModel->insert($data)) {
        if ($this->request->isAJAX()) {
          return $this->response->setJSON([
            'success' => true,
            'project' => [
              'id' => $this->projectModel->getInsertID(),
              'title' => $data['title'],
              'slug' => $data['slug'],
              'order' => 1
            ]
          ]);
        }
        return redirect()->to('/artwork')->with('success', 'Project created successfully.');
      }

      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'success' => false,
          'error' => 'Failed to create project.'
        ]);
      }
      return redirect()->to('/artwork')->withInput()->with('error', 'Failed to create project.');
    } catch (\Throwable $e) {
      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(500)->setJSON([
          'success' => false,
          'error' => 'Exception: ' . $e->getMessage()
        ]);
      }
      throw $e;
    }
  }
  
  public function edit($id)
  {
    $project = $this->projectModel->find($id);
    
    if (!$project) {
      return redirect()->to('/artwork')->with('error', 'Project not found.');
    }
    
    return $this->renderNonPublicView('artwork/artwork_form', ['project' => $project, 'title' => 'Edit Project']);
  }
  
  public function update($id)
  {
    $data = $this->request->getPost();
    $data['id'] = $id;
    
    if (!$this->validateData($data, $this->projectModel->getValidationRules())) {
      return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
    }
    
    if ($this->projectModel->update($id, $data)) {
      return redirect()->to('/artwork')->with('success', 'Project updated successfully.');
    }
    
    return redirect()->back()->withInput()->with('error', 'Failed to update project.');
  }
  
  public function delete($id)
  {
    $success = $this->projectModel->delete($id);
    if ($this->request->isAJAX()) {
      return $this->response->setJSON([
        'success' => $success,
        'error' => $success ? null : 'Failed to delete project.'
      ]);
    }
    if ($success) {
      return redirect()->to('/artwork')->with('success', 'Project deleted successfully.');
    }
    return redirect()->back()->with('error', 'Failed to delete project.');
  }
  
  public function moveUp($id)
  {
    $project = $this->projectModel->find($id);
    if (!$project) {
      if ($this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'error' => 'Project not found']);
      }
      return redirect()->to('/artwork')->with('error', 'Project not found.');
    }
    $projectAbove = $this->projectModel
      ->where('sort_order <', $project['sort_order'])
      ->orderBy('sort_order', 'DESC')
      ->first();
    $moved = false;
    if ($projectAbove) {
      $this->projectModel->update($id, ['sort_order' => $projectAbove['sort_order']]);
      $this->projectModel->update($projectAbove['id'], ['sort_order' => $project['sort_order']]);
      $moved = true;
    }
    if ($this->request->isAJAX()) {
      return $this->response->setJSON(['success' => true, 'moved' => $moved]);
    }
    return redirect()->to('/artwork');
  }

  public function moveDown($id)
  {
    $project = $this->projectModel->find($id);
    if (!$project) {
      if ($this->request->isAJAX()) {
        return $this->response->setJSON(['success' => false, 'error' => 'Project not found']);
      }
      return redirect()->to('/artwork')->with('error', 'Project not found.');
    }
    $projectBelow = $this->projectModel
      ->where('sort_order >', $project['sort_order'])
      ->orderBy('sort_order', 'ASC')
      ->first();
    $moved = false;
    if ($projectBelow) {
      $this->projectModel->update($id, ['sort_order' => $projectBelow['sort_order']]);
      $this->projectModel->update($projectBelow['id'], ['sort_order' => $project['sort_order']]);
      $moved = true;
    }
    if ($this->request->isAJAX()) {
      return $this->response->setJSON(['success' => true, 'moved' => $moved]);
    }
    return redirect()->to('/artwork');
  }

  public function setPublished($id)
  {
    $project = $this->projectModel->find($id);
    if (!$project) {
      return $this->response->setStatusCode(404)->setJSON([
        'success' => false,
        'error' => 'Project not found.'
      ]);
    }

    $payload = $this->request->getJSON(true);
    if (!is_array($payload)) {
      $payload = $this->request->getPost();
    }
    $rawValue = $payload['is_published'] ?? null;

    if ($rawValue === 1 || $rawValue === '1' || $rawValue === true || $rawValue === 'true') {
      $isPublished = 1;
    } elseif ($rawValue === 0 || $rawValue === '0' || $rawValue === false || $rawValue === 'false') {
      $isPublished = 0;
    } else {
      return $this->response->setStatusCode(422)->setJSON([
        'success' => false,
        'error' => 'Invalid publish value.'
      ]);
    }

    $success = $this->projectModel->update($id, ['is_published' => $isPublished]);
    if (!$success) {
      return $this->response->setStatusCode(500)->setJSON([
        'success' => false,
        'error' => 'Failed to update publish state.'
      ]);
    }

    return $this->response->setJSON([
      'success' => true,
      'id' => (int)$id,
      'is_published' => $isPublished
    ]);
  }
}

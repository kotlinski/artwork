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
    $projects = $this->projectModel->orderBy('sort_order', 'ASC')->findAll();
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
    /*
    foreach ($projects as &$project) {
      $project_images = $image_model->where('project', $project['id'])->orderBy('`order`', 'ASC')->findAll();
      $project['images'] = $project_images;
    }
    */
    $data['projects'] = $projects;
    $required = [
      'title' => 'Artwork | Anne Hamrin Simonsson',
      'selected_menu_item' => 'artwork',
      'description' => 'Biography and artist statement of Anne Hamrin Simonsson, a Swedish conceptual artist based on Öland, known for her site-specific installations and objects.',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
    ];
    return $this->renderView('artwork/artwork_view', $required, $data);
  }
  
  public function admin()
  {
    $data['projects'] = $this->projectModel->orderBy('sort_order', 'ASC')->findAll();
    $data['title'] = 'Artwork Admin';
    return $this->renderNonPublicView('artwork/manage_projects', $data);
  }
  
  public function store()
  {
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
      return redirect()->to('/artwork')->withInput()->with('errors', $this->validator->getErrors());
    }
    
    // Get the highest sort_order and add 1 for new project
    $maxOrder = $this->projectModel->selectMax('sort_order')->first();
    $newOrder = ($maxOrder['sort_order'] ?? 0) + 1;
    
    $data = [
      'title' => $this->request->getPost('title'),
      'slug' => $this->request->getPost('slug'),
      'image_mid' => 'placeholder.jpg',
      'image_right' => 'placeholder.jpg',
      'sort_order' => $newOrder
    ];
    
    if ($this->projectModel->insert($data)) {
      return redirect()->to('/artwork')->with('success', 'Project created successfully.');
    }
    
    return redirect()->to('/artwork')->withInput()->with('error', 'Failed to create project.');
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
    if ($this->projectModel->delete($id)) {
      return redirect()->to('/artwork')->with('success', 'Project deleted successfully.');
    }
    
    return redirect()->back()->with('error', 'Failed to delete project.');
  }
  
  public function moveUp($id)
  {
    $project = $this->projectModel->find($id);
    
    if (!$project) {
      return redirect()->to('/artwork')->with('error', 'Project not found.');
    }
    
    // Find the project with the next lower sort_order (the one above)
    $projectAbove = $this->projectModel
      ->where('sort_order <', $project['sort_order'])
      ->orderBy('sort_order', 'DESC')
      ->first();
    
    if ($projectAbove) {
      // Swap sort_order values
      $this->projectModel->update($id, ['sort_order' => $projectAbove['sort_order']]);
      $this->projectModel->update($projectAbove['id'], ['sort_order' => $project['sort_order']]);
    }
    
    return redirect()->to('/artwork');
  }
  
  public function moveDown($id)
  {
    $project = $this->projectModel->find($id);
    
    if (!$project) {
      return redirect()->to('/artwork')->with('error', 'Project not found.');
    }
    
    // Find the project with the next higher sort_order (the one below)
    $projectBelow = $this->projectModel
      ->where('sort_order >', $project['sort_order'])
      ->orderBy('sort_order', 'ASC')
      ->first();
    
    if ($projectBelow) {
      // Swap sort_order values
      $this->projectModel->update($id, ['sort_order' => $projectBelow['sort_order']]);
      $this->projectModel->update($projectBelow['id'], ['sort_order' => $project['sort_order']]);
    }
    
    return redirect()->to('/artwork');
  }
}

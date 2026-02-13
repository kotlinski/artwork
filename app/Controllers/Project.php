<?php

namespace App\Controllers;

use App\Models\Image;

class Project extends BaseController
{
  public function detail($slug)
  {
    $model = new \App\Models\Project();
    $project = $model->where('slug', $slug)->first();
    if (!$project) {
      // Redirect to artwork page if slug does not match a project
      return redirect()->to('/artwork');
    }
    // Fetch all images connected to the project (by project id or slug)
    $imageModel = new Image();
    $images = $imageModel->where('project', $project['slug'])->orderBy('`order`', 'ASC')->findAll();
    // TODO: Fetch news connected to the project when model is available
    $required = [
      'title' => $project['title'] .
        (isset($project['start_year']) ? ' (' . $project['start_year'] . (isset($project['end_year']) && $project['end_year'] ? '–' . $project['end_year'] : '') . ')' : ''),
      'selected_menu_item' => 'artwork',
      'description' => $project['description'] ?? '',
      'og_image' => base_url('anne-hamrin-simonsson-portrait.jpg'),
      'og_image_width' => '320',
      'og_image_height' => '320',
    ];
    return $this->renderView('artwork/project_detail', $required, [
      'project' => $project,
      'images' => $images,
      // 'news' => $news // Add when news model is available
    ]);
  }

  public function imageDetail($projectSlug, $imageSlug)
  {
    $model = new \App\Models\Project();
    $project = $model->where('slug', $projectSlug)->first();
    if (!$project) {
      log_message('debug', 'no project found ' . $projectSlug . ' / ' . $imageSlug);
      return redirect()->to('/artwork');
    }
    $imageModel = new \App\Models\Image();
    $images = $imageModel->where('project', $project['slug'])->orderBy('`order`', 'ASC')->findAll();
    $image = null;
    $currentIndex = null;
    foreach ($images as $i => $img) {
      if ($img['file_id'] === $imageSlug) {
        $image = $img;
        $currentIndex = $i;
        break;
      }
    }
    if (!$image) {
      log_message('debug', 'no image found ' . $projectSlug . ' / ' . $imageSlug);
      return redirect()->to(base_url($projectSlug));
    }
    // Carousel wrap-around logic
    $imagesCount = count($images);
    $prevIndex = $imagesCount > 0 ? (($currentIndex - 1 + $imagesCount) % $imagesCount) : null;
    $nextIndex = $imagesCount > 0 ? (($currentIndex + 1) % $imagesCount) : null;
    $prevSlug = $prevIndex !== null && isset($images[$prevIndex]) ? (isset($images[$prevIndex]['file_id']) ? $images[$prevIndex]['file_id'] : (isset($images[$prevIndex]['file_name']) ? pathinfo($images[$prevIndex]['file_name'], PATHINFO_FILENAME) : '')) : null;
    $nextSlug = $nextIndex !== null && isset($images[$nextIndex]) ? (isset($images[$nextIndex]['file_id']) ? $images[$nextIndex]['file_id'] : (isset($images[$nextIndex]['file_name']) ? pathinfo($images[$nextIndex]['file_name'], PATHINFO_FILENAME) : '')) : null;
    $required = [
      'title' => $image['title'] . ' | Anne Hamrin Simonsson',
      'selected_menu_item' => 'artwork',
      'description' => $image['caption'],
      'og_image' => base_url('konst/' . $image['file_name']),
      'og_image_width' => $image['width_px'],
      'og_image_height' => $image['height_px'],
    ];
    return $this->renderView('artwork/image_detail', $required, [
      'project' => $project,
      'image' => $image,
      'currentIndex' => $currentIndex,
      'prevSlug' => $prevSlug,
      'nextSlug' => $nextSlug,
      'imagesCount' => $imagesCount,
      'hide_main_header' => true
    ]);
  }
}

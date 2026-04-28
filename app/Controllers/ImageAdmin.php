<?php

namespace App\Controllers;

use App\Libraries\HtaccessRedirectManager;
use App\Models\Image;
use App\Models\Project;

class ImageAdmin extends BaseController
{
  public function delete($id)
  {
    $imageModel = new Image();
    $image = $imageModel->find($id);
    if (!$image) {
      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(404)->setJSON(['success' => false, 'error' => 'Image not found.']);
      }
      return redirect()->to('/image/admin')->with('error', 'Image not found.');
    }
    $success = $imageModel->delete($id);
    if ($this->request->isAJAX()) {
      if ($success) {
        return $this->response->setJSON(['success' => true]);
      } else {
        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Delete failed.']);
      }
    }
    if ($success) {
      return redirect()->to('/image/admin')->with('success', 'Image deleted.');
    } else {
      return redirect()->to('/image/admin')->with('error', 'Delete failed.');
    }
  }
  public function admin()
  {
    $imageModel = new Image();
    $projectModel = new Project();
    $images = $imageModel->orderBy('project', 'ASC')->orderBy('order', 'ASC')->findAll();
    $projects = $projectModel->orderBy('sort_order', 'ASC')->findAll();
    
    // Group images by project
    foreach ($projects as &$project) {
      $project['images'] = [];
      foreach ($images as $img) {
        if ($img['project'] == $project['id']) {
          $project['images'][] = $img;
        }
      }
    }
    return $this->renderNonPublicView('artwork/manage-images', [
      'title' => 'Image Admin',
      'projects' => $projects
    ]);
  }
  
  public function update($id)
  {
    $imageModel = new Image();
    $projectModel = new Project();
    $existingImage = $imageModel->find($id);
    if (!$existingImage) {
      if ($this->request->isAJAX()) {
        return $this->response->setStatusCode(404)->setJSON(['success' => false, 'error' => 'Image not found.']);
      }
      return redirect()->to('/image/admin')->with('error', 'Image not found.');
    }

    $data = $this->request->getPost();
    // Ensure blank dimensions are saved as NULL, not 0.00, and integers are stored as integer strings
    foreach (['height_cm', 'width_cm', 'depth_cm'] as $dim) {
      if (isset($data[$dim])) {
        $val = trim($data[$dim]);
        if ($val === '') {
          $data[$dim] = null;
        } elseif (is_numeric($val)) {
          // If integer, store as int string; if decimal, store as trimmed string
          if ((float)$val == (int)$val) {
            $data[$dim] = (string)(int)$val;
          } else {
            $data[$dim] = rtrim(rtrim($val, '0'), '.');
          }
        } else {
          $data[$dim] = null;
        }
      }
    }
    $success = $imageModel->update($id, $data);

    if ($success) {
      $updatedImage = $imageModel->find($id);
      $oldProject = $projectModel->find($existingImage['project'] ?? null);
      $newProject = $projectModel->find($updatedImage['project'] ?? null);

      $oldProjectSlug = (string)($oldProject['slug'] ?? '');
      $newProjectSlug = (string)($newProject['slug'] ?? '');
      $oldFileId = (string)($existingImage['file_id'] ?? '');
      $newFileId = (string)($updatedImage['file_id'] ?? '');

      if ($oldProjectSlug !== '' && $newProjectSlug !== '' && $oldFileId !== '' && $newFileId !== '') {
        $oldPath = '/' . $oldProjectSlug . '/' . $oldFileId;
        $newPath = '/' . $newProjectSlug . '/' . $newFileId;
        if ($oldPath !== $newPath) {
          try {
            (new HtaccessRedirectManager())->addRedirects([
              ['from' => $oldPath, 'to' => $newPath],
            ]);
          } catch (\Throwable $e) {
            log_message('error', 'Failed writing image rename redirect: ' . $e->getMessage());
          }
        }
      }
    }

    if ($this->request->isAJAX()) {
      if ($success) {
        // Fetch the updated image from the database
        $updatedImage = $imageModel->find($id);
        return $this->response->setJSON(['success' => true, 'image' => $updatedImage]);
      } else {
        return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Update failed.']);
      }
    }
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

  public function reorder($id)
  {
    $imageModel = new Image();
    $image = $imageModel->find($id);

    if (!$image) {
      return $this->response->setStatusCode(404)->setJSON(['success' => false, 'error' => 'Image not found.']);
    }

    $body     = $this->request->getJSON(true);
    $newOrder = isset($body['order']) ? (int) $body['order'] : null;

    if ($newOrder === null || $newOrder < 1) {
      return $this->response->setStatusCode(400)->setJSON(['success' => false, 'error' => 'Invalid order value.']);
    }

    // Fetch all images for this project sorted by current order
    $siblings = $imageModel
      ->where('project', $image['project'])
      ->orderBy('`order`', 'ASC')
      ->findAll();

    $count = count($siblings);
    $newOrder = min($newOrder, $count);

    // Remove the target from the list, then re-insert at the new position
    $reordered = array_values(array_filter($siblings, fn($s) => $s['id'] != $id));
    array_splice($reordered, $newOrder - 1, 0, [$image]);

    // Write sequential order values back
    foreach ($reordered as $position => $img) {
      $imageModel->update($img['id'], ['order' => $position + 1]);
    }

    return $this->response->setJSON(['success' => true]);
  }

  public function upload()
  {
    $projectModel = new Project();
    $imageModel = new Image();
    
    // Validate input
    $rules = [
      'project_id' => 'required|integer',
      'file_id' => 'required|max_length[110]|regex_match[/^[a-z0-9\-]+$/]',
      'image' => 'uploaded[image]|max_size[image,20480]|ext_in[image,jpg,jpeg,png,webp]',
    ];
    $messages = [
      'file_id' => [
        'regex_match' => 'The file ID may only contain lowercase letters, numbers, and hyphens.',
      ],
    ];
    
    $returnTo = $this->request->getPost('return_to') ?: '/image/admin';

    if (!$this->validate($rules, $messages)) {
      return redirect()->to($returnTo)
        ->with('upload_errors', $this->validator->getErrors())
        ->with('upload_project_id', $this->request->getPost('project_id'))
        ->with('upload_file_id', $this->request->getPost('file_id'));
    }
    
    $projectId = (int)$this->request->getPost('project_id');
    $fileId = $this->request->getPost('file_id');
    $project = $projectModel->find($projectId);
    
    if (!$project) {
      return redirect()->to($returnTo)->with('upload_error', 'Project not found.');
    }
    
    $file = $this->request->getFile('image');
    
    // Build base filename: anne-hamrin-simonsson-<project.slug>-<file_id>
    $baseName = 'anne-hamrin-simonsson-' . $project['slug'] . '-' . $fileId;
    $origExt = strtolower($file->getClientExtension());
    $origName = $baseName . '.' . $origExt;
    
    $konstDir = FCPATH . 'konst/';
    $originalDir = $konstDir . 'original/';
    
    // Save original file
    if (!is_dir($originalDir)) {
      mkdir($originalDir, 0775, true);
    }
    $file->move($originalDir, $origName, true);
    $origPath = $originalDir . $origName;
    
    // Determine WebP quality based on file size (mirrors script.sh logic)
    $filesize = filesize($origPath);
    if ($filesize > 3145728) {
      $quality = 43;
    } elseif ($filesize > 2097152) {
      $quality = 63;
    } elseif ($filesize > 1048576) {
      $quality = 73;
    } else {
      $quality = 87;
    }
    
    $webpName = $baseName . '.webp';

    helper('webp');

    // Artwork variants:
    // square/    — 122×122 center crop  (uniform grid thumbnail)
    // square2x/  — 244×244 center crop  (2× retina)
    // thumb/     — fits within 122×122  (full image, no crop)
    // thumb2x/   — fits within 244×244  (2× retina)
    // Root       — full reoriented image (fullscreen)
    $squareVariants = [
      'square/'  => ['size' => 122, 'quality' => min($quality, 65)],
      'square2x/' => ['size' => 244, 'quality' => min($quality, 65)],
    ];
    $fitVariants = [
      'thumb/'   => ['maxW' => 122, 'maxH' => 122, 'quality' => min($quality, 65)],
      'thumb2x/' => ['maxW' => 244, 'maxH' => 244, 'quality' => min($quality, 65)],
      'small/'   => ['maxW' => 800,  'maxH' => 600, 'quality' => min($quality, 75)],
      'medium/'  => ['maxW' => 1280, 'maxH' => 960, 'quality' => min($quality, 80)],
      'large/'   => ['maxW' => 1920, 'maxH' => 1440, 'quality' => min($quality, 85)],
      'x-large/' => ['maxW' => 2560, 'maxH' => 1920, 'quality' => min($quality, 87)],
    ];
    foreach ($squareVariants as $subdir => $opts) {
      $targetDir = $konstDir . $subdir;
      if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
      generate_webp_square($origPath, $targetDir . $webpName, $opts['size'], $opts['quality']);
    }
    foreach ($fitVariants as $subdir => $opts) {
      $targetDir = $konstDir . $subdir;
      if (!is_dir($targetDir)) mkdir($targetDir, 0775, true);
      generate_webp_fit($origPath, $targetDir . $webpName, $opts['maxW'], $opts['maxH'], $opts['quality']);
    }
    // Root: full reoriented image
    generate_webp_variant($origPath, $konstDir . $webpName, $quality);
    // Get dimensions from the root webp
    $rootWebp = $konstDir . $webpName;
    $dimensions = @getimagesize($rootWebp);
    $widthPx = $dimensions ? $dimensions[0] : null;
    $heightPx = $dimensions ? $dimensions[1] : null;
    
    // Determine next order for this project
    $maxOrder = $imageModel->where('project', $projectId)->selectMax('order')->first();
    $newOrder = isset($maxOrder['order']) ? (int)$maxOrder['order'] + 1 : 1;
    
    // Insert into DB
    $imageModel->insert([
      'file_id' => $fileId,
      'file_name' => $webpName,
      'project' => $projectId,
      'order' => $newOrder,
      'width_px' => $widthPx,
      'height_px' => $heightPx,
    ]);
    
    return redirect()->to($returnTo)->with('success', 'Image "' . $webpName . '" uploaded successfully.');
  }
}

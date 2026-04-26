<?php

namespace App\Models;

use CodeIgniter\Model;

class Project extends Model
{
  protected $table = 'projects';
  protected $primaryKey = 'id';
  protected $useAutoIncrement = true;
  protected $returnType = 'array';
  protected $useSoftDeletes = false;
  protected $protectFields = true;
  protected $allowedFields = [
    'slug',
    'title',
    'alternate_name',
    'description',
    'text',
    'text_sv',
    'start_year',
    'end_year',
    'location',
    'map_url',
    'external_links',
    'image_left',
    'image_mid',
    'image_right',
    'sort_order',
    'is_published'
  ];
  
  protected $useTimestamps = true;
  protected $createdField = '';
  protected $updatedField = 'updated_at';
  
  protected $validationRules = [
    // Add this line so the placeholder {id} becomes valid
    'id'          => 'permit_empty|integer',
    'slug'        => 'required|max_length[110]|is_unique[projects.slug,id,{id}]',
    'title'       => 'required|max_length[255]',
    'image_left'  => 'permit_empty|integer',
    'image_mid'   => 'permit_empty|integer',
    'image_right' => 'permit_empty|integer',
    'is_published' => 'permit_empty|integer'
  ];
  
  protected $validationMessages = [
    'slug' => [
      'is_unique' => 'This slug is already in use.'
    ]
  ];
  
  protected $skipValidation = false;

  public function findOrCreateSystemProject(string $slug, string $title): int
  {
    $slug = trim(strtolower($slug));
    if ($slug === '') {
      throw new \InvalidArgumentException('System project slug cannot be empty.');
    }

    $existing = $this->where('slug', $slug)->first();
    if (is_array($existing) && isset($existing['id'])) {
      return (int) $existing['id'];
    }

    $maxSort = $this->selectMax('sort_order', 'max_sort')->first();
    $nextSort = isset($maxSort['max_sort']) ? ((int) $maxSort['max_sort'] + 1) : 1;

    $this->insert([
      'slug' => $slug,
      'title' => $title,
      'description' => null,
      'text' => null,
      'text_sv' => null,
      'sort_order' => $nextSort,
      'is_published' => 0,
    ]);

    return (int) $this->getInsertID();
  }
}

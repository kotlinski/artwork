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
    'start_year',
    'end_year',
    'location',
    'map_url',
    'external_links',
    'hero_left',
    'hero_mid',
    'hero_right',
    'sort_order'
  ];
  
  protected $useTimestamps = true;
  protected $createdField = '';
  protected $updatedField = 'updated_at';
  
  protected $validationRules = [
    'slug' => 'required|max_length[110]|is_unique[projects.slug,id,{id}]',
    'title' => 'required|max_length[255]',
    'hero_mid' => 'required|max_length[150]',
    'hero_right' => 'required|max_length[150]'
  ];
  
  protected $validationMessages = [
    'slug' => [
      'is_unique' => 'This slug is already in use.'
    ]
  ];
  
  protected $skipValidation = false;
}


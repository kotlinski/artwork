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
    'image_left',
    'image_mid',
    'image_right',
    'sort_order'
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
    'image_mid'   => 'required|integer',
    'image_right' => 'required|integer'
  ];
  
  protected $validationMessages = [
    'slug' => [
      'is_unique' => 'This slug is already in use.'
    ]
  ];
  
  protected $skipValidation = false;
}


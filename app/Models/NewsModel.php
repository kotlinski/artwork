<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
  protected $table      = 'news_modern';
  protected $primaryKey = 'id';
  protected $allowedFields = [
    'title', 'slug', 'content', 'excerpt', 'category',
    'event_location', 'event_start_date', 'event_end_date',
    'external_link', 'main_image', 'width_px', 'height_px', 'created_at', 'project_id', 'is_published'
  ];
  
  /**
   * Fetch all news ordered by date
   */
  public function getLatestNews()
  {
    return $this->orderBy('created_at', 'DESC')->findAll();
  }
}
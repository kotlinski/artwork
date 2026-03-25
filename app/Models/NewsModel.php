<?php

namespace App\Models;

use CodeIgniter\Model;

class NewsModel extends Model
{
  protected $table      = 'news_modern';
  protected $primaryKey = 'id';
  protected $allowedFields = [
    'title', 'slug', 'content', 'excerpt',
    'event_location', 'event_start_date', 'event_end_date',
    'main_image', 'created_at'
  ];
  
  /**
   * Fetch all news ordered by date
   */
  public function getLatestNews()
  {
    return $this->orderBy('created_at', 'DESC')->findAll();
  }
}
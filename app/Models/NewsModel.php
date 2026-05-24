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
   * Fetch published news ordered by date (public-facing).
   */
  public function getLatestNews(): array
  {
    return $this->where('is_published', 1)->orderBy('created_at', 'DESC')->findAll();
  }

  /**
   * Fetch all news ordered by date, including drafts (admin-facing).
   */
  public function getAllNews(): array
  {
    return $this->orderBy('created_at', 'DESC')->findAll();
  }
}
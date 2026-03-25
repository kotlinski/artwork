<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class MigrateNews extends BaseCommand
{
  protected $group       = 'Migration';
  protected $name        = 'migrate:news'; // This is what spark looks for
  protected $description = 'Migrates old news data to the new structured news_modern table.';
  
  public function run(array $params)
  {
    $db = \Config\Database::connect();
    
    // 1. Fetch old news
    $query = $db->query("SELECT * FROM news");
    $results = $query->getResultArray();
    
    if (empty($results)) {
      CLI::error("No news found in the old table.");
      return;
    }
    
    CLI::write("Starting migration of " . count($results) . " items...", 'yellow');
    
    foreach ($results as $row) {
      $full_text = $row['text'];
      
      // --- Regex for Dates ---
      preg_match_all('/(\d{1,2}\/\d{1,2}(\/\d{2,4})?)/', $full_text, $date_matches);
      $start_date = !empty($date_matches[0]) ? $this->parseToSQLDate($date_matches[0][0], $row['created']) : null;
      $end_date = (isset($date_matches[0][1])) ? $this->parseToSQLDate($date_matches[0][1], $row['created']) : null;
      
      // --- Regex for Location (First line) ---
      $lines = explode("\n", trim($full_text));
      $location = $lines[0];
      $year = date('Y', strtotime($row['created']));
      $clean_slug = $this->slugify($row['slug']);
      $unique_slug = $row['slug'] . '-' . $year;
      // --- Prepare New Data ---
      $data = [
        'title'            => $row['title'],
        'slug'             => $unique_slug,
        'content'          => $full_text,
        'excerpt'          => mb_strimwidth(strip_tags($full_text), 0, 155, "..."),
        'event_location'   => $location,
        'event_start_date' => $start_date,
        'event_end_date'   => $end_date,
        'created_at'       => $row['created']
      ];
      
      if ($db->table('news_modern')->insert($data)) {
        CLI::write("✔ Migrated: " . $row['title'], 'green');
      } else {
        CLI::error("✘ Failed: " . $row['title']);
      }
    }
    
    CLI::write("Migration Complete!", 'cyan');
  }
  
  private function slugify($text)
  {
    // Convert å, ä to a and ö to o
    $search  = ['å', 'ä', 'ö', 'Å', 'Ä', 'Ö'];
    $replace = ['a', 'a', 'o', 'a', 'a', 'o'];
    $text = str_replace($search, $replace, $text);
    
    // Remove any remaining non-alphanumeric characters except hyphens
    $text = preg_replace('~[^-\w]+~', '', $text);
    
    // Trim and lowercase
    return strtolower(trim($text, '-'));
  }
  
  private function parseToSQLDate($date_str, $fallback)
  {
    $parts = explode('/', $date_str);
    $year = (isset($parts[2])) ? $parts[2] : date('Y', strtotime($fallback));
    if (strlen($year) == 2) $year = "20" . $year;
    
    $month = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
    $day = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
    
    return "$year-$month-$day";
  }
}

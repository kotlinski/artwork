<?php
class Sitemap extends CI_Controller {
  public $form_validation;
  public $session;
  public $images_model;

  public function __construct() {
    parent::__construct();
    $this->load->library('session');
    $this->load->model('Images_model', 'images_model');
  }

  public function generate() {
    if (!$this->session->userdata('logged_in')) {
      show_error('Unauthorized', 401);
    }

    $images = $this->images_model->get_all_images();
    $base_url = "https://www.annesimonsson.se/";
    $current_date = date('Y-m-d');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>';
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">';

    // 1. Updated Static Pages & Categories
    // Added index pages for installations, objects, and paintings to match original structure
    $static_pages = [
      '' => ['priority' => 1.0, 'freq' => 'monthly'],
      'news' => ['priority' => 0.8, 'freq' => 'weekly'],
      'album' => ['priority' => 0.8, 'freq' => 'monthly'],
      'about' => ['priority' => 0.7, 'freq' => 'monthly'],
      'contact' => ['priority' => 0.7, 'freq' => 'monthly'],
      'album/installations' => ['priority' => 0.9, 'freq' => 'monthly'],
      'album/objects' => ['priority' => 0.9, 'freq' => 'monthly'],
      'album/paintings' => ['priority' => 0.9, 'freq' => 'monthly'],
      'license' => ['priority' => 0.5, 'freq' => 'yearly']
    ];

    foreach ($static_pages as $path => $data) {
      $xml .= '<url>';
      $xml .= '<loc>' . $base_url . $path . '</loc>';
      $xml .= '<lastmod>' . $current_date . '</lastmod>';
      $xml .= '<changefreq>' . $data['freq'] . '</changefreq>';
      $xml .= '<priority>' . $data['priority'] . '</priority>';

      // Feature: Add the "Under_Liv" hero image to the homepage entry as seen in your original
      if ($path === '') {
        $xml .= '<image:image>';
        $xml .= '<image:loc>' . $base_url . 'konst/anne-hamrin-simonsson-konstverk-smalandstrienalen-rotvalta.jpg</image:loc>';
        $xml .= '<image:title>Installation Under_Liv by Anne Hamrin Simonsson</image:title>';
        $xml .= '<image:caption>Conceptual Artist Anne Hamrin Simonsson, part of Under_Liv root roll with 24 objects installation Kalmar konstmuseum 2023</image:caption>';
        $xml .= '<image:license>' . $base_url . 'license</image:license>';
        $xml .= '<image:geo_location>Kalmar Konstmuseum, Kalmar, Sweden</image:geo_location>';
        $xml .= '<image:author>Anne Hamrin Simonsson</image:author>';
        $xml .= '</image:image>';
      }
      $xml .= '</url>';
    }

    // 2. Dynamic Artwork Pages
    $category_map = [1 => 'paintings', 2 => 'installations', 3 => 'objects'];

    foreach ($images as $img) {
      $category = $category_map[$img['artwork_filter']] ?? 'paintings';
      $page_url = $base_url . 'album/' . $category . '/' . $img['file_id'];

      $xml .= '<url>';
      $xml .= '<loc>' . htmlspecialchars($page_url) . '</loc>';
      $xml .= '<lastmod>' . date('Y-m-d', strtotime($img['datum'])) . '</lastmod>';
      $xml .= '<priority>0.6</priority>';

      $xml .= '<image:image>';
      $xml .= '<image:loc>' . $base_url . 'konst/' . htmlspecialchars($img['file_name']) . '</image:loc>';
      $xml .= '<image:title>' . htmlspecialchars($img['title']) . '</image:title>';
      $xml .= '<image:caption>' . htmlspecialchars($img['caption']) . '</image:caption>';
      $xml .= '<image:license>' . $base_url . 'license</image:license>';
      if (!empty($img['geo_location'])) {
        $xml .= '<image:geo_location>' . htmlspecialchars($img['geo_location']) . '</image:geo_location>';
      }
      $xml .= '<image:author>Anne Hamrin Simonsson</image:author>';
      $xml .= '</image:image>';
      $xml .= '</url>';
    }

    $xml .= '</urlset>';
    file_put_contents(FCPATH . 'sitemap.xml', $xml);
    echo json_encode(['success' => true]);
  }
}
?>
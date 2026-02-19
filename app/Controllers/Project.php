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
    $current_index = null;
    foreach ($images as $i => $img) {
      if ($img['file_id'] === $imageSlug) {
        $image = $img;
        $current_index = $i;
        break;
      }
    }
    if (!$image) {
      log_message('debug', 'no image found ' . $projectSlug . ' / ' . $imageSlug);
      return redirect()->to(base_url($projectSlug));
    }
    // Carousel wrap-around logic
    $images_count = count($images);
    $prev_index = $images_count > 0 ? (($current_index - 1 + $images_count) % $images_count) : null;
    $next_index = $images_count > 0 ? (($current_index + 1) % $images_count) : null;
    $prev_slug = $prev_index !== null && isset($images[$prev_index]) ? (isset($images[$prev_index]['file_id']) ? $images[$prev_index]['file_id'] : (isset($images[$prev_index]['file_name']) ? pathinfo($images[$prev_index]['file_name'], PATHINFO_FILENAME) : '')) : null;
    $next_slug = $next_index !== null && isset($images[$next_index]) ? (isset($images[$next_index]['file_id']) ? $images[$next_index]['file_id'] : (isset($images[$next_index]['file_name']) ? pathinfo($images[$next_index]['file_name'], PATHINFO_FILENAME) : '')) : null;
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
      'current_index' => $current_index,
      'prev_slug' => $prev_slug,
      'next_slug' => $next_slug,
      'images_count' => $images_count,
      'hide_main_header' => true,
      'jsonld' => generateImageJsonLd($image, '/' . $project['slug'])
    ]);
  }
}


function generateImageJsonLd($image, $album_path)
{
  $year_created = !empty($image['date_created']) ? $image['date_created'] : null;
  
  $jsonLd = [
    "@context" => "https://schema.org",
    "@graph" => [
      [
        "@type" => "VisualArtwork",
        "@id" => "https://www.annesimonsson.se" . $album_path . "/" . $image['file_id'] . "#artwork",
        "name" => $image['title'],
        "alternateName" => $image['alternate_name'],
        "license" => "https://www.annesimonsson.se/license.html",
        "description" => $image['caption'],
        "dateCreated" => $year_created,
        "artform" => $image['artform'] ?? "Visual Artwork",
        "artMedium" => $image['art_medium'],
        "artworkSurface" => $image['artwork_surface'],
        "artEdition" => $image['art_edition'],
        "creator" => [
          "@type" => "Person",
          "@id" => "https://www.annesimonsson.se/#person",
          "name" => "Anne Hamrin Simonsson",
          "sameAs" => ["https://www.wikidata.org/wiki/Q137808007"]
        ]
      ],
      [
        "@type" => "ImageObject",
        "@id" => "https://www.annesimonsson.se" . $album_path . "/" . $image['file_id'] . "#image",
        "url" => "https://www.annesimonsson.se/konst/" . $image['file_name'],
        "contentUrl" => "https://www.annesimonsson.se/konst/" . $image['file_name'],
        "thumbnailUrl" => "https://www.annesimonsson.se/konst/thumb/" . $image['file_name'],
        "license" => "https://www.annesimonsson.se/license.html",
        "acquireLicensePage" => "https://www.annesimonsson.se/license.html",
        "creditText" => "Anne Hamrin Simonsson",
        "copyrightNotice" => "© 2009-2026 Anne Hamrin Simonsson. All rights reserved.",
        "width" => $image['width_px'],
        "height" => $image['height_px'],
        "encodingFormat" => "image/webp",
        "creator" => [
          "@type" => "Person",
          "name" => $image['photographer_name'] ?? "Anne Hamrin Simonsson"
        ]
      ]
    ]
  ];
  
  if (!empty($image['height_cm'])) {
    $jsonLd["@graph"][0]["height"] = ["@type" => "Distance", "name" => $image['height_cm'] . " cm"];
    $jsonLd["@graph"][0]["width"] = ["@type" => "Distance", "name" => $image['width_cm'] . " cm"];
    if (!empty($image['depth_cm'])) {
      $jsonLd["@graph"][0]["depth"] = ["@type" => "Distance", "name" => $image['depth_cm'] . " cm"];
    }
  }
  
  if (!empty($image['geo_location'])) {
    $jsonLd["@graph"][0]["locationCreated"] = [
      "@type" => "Place",
      "name" => $image['geo_location'],
      "hasMap" => $image['map_url'] ?? null,
      "address" => [
        "@type" => "PostalAddress",
        "addressLocality" => $image['address_locality'],
        "addressRegion" => $image['address_region'],
        "addressCountry" => $image['address_country']
      ]
    ];
  }
  
  if (!empty($image['project'])) {
    $jsonLd["@graph"][0]["isPartOf"] = [
      "@type" => "CreativeWorkSeries",
      "name" => $image['project'],
      "creator" => ["@id" => "https://www.annesimonsson.se/#person"]
    ];
  }
  
  return json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}




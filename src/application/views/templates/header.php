<!DOCTYPE html>
<html class="no-js" lang="en" >

<?php
function generateEnhancedJsonLd($row, $album_path) {
  // Vi använder nu databasens 'date_created' men faller tillbaka på regex om det saknas
  $yearCreated = !empty($row['date_created']) ? $row['date_created'] : null;
  if (!$yearCreated && preg_match('/\b(19|20)\d{2}\b/', $row['caption'], $matches)) {
    $yearCreated = $matches[0];
  }

  $jsonLd = [
    "@context" => "https://schema.org",
    "@graph" => [
      [
        "@type" => "VisualArtwork",
        "@id" => "https://www.annesimonsson.se" . $album_path . "/" . $row['file_id'] . "#artwork",
        "name" => $row['title'],
        "alternateName" => $row['alternate_name'],
        "description" => $row['caption'],
        "dateCreated" => $yearCreated,
        "artform" => $row['artform'] ?? "Visual Artwork",
        "artMedium" => $row['art_medium'],
        "artworkSurface" => $row['artwork_surface'],
        "artEdition" => $row['art_edition'],
        "creator" => [
          "@type" => "Person",
          "@id" => "https://www.annesimonsson.se/#person",
          "name" => "Anne Hamrin Simonsson",
          "sameAs" => ["https://www.wikidata.org/wiki/Q137808007"]
        ]
      ],
      [
        "@type" => "ImageObject",
        "@id" => "https://www.annesimonsson.se" . $album_path . "/" . $row['file_id'] . "#image",
        "url" => "https://www.annesimonsson.se/konst/" . $row['file_name'],
        "contentUrl" => "https://www.annesimonsson.se/konst/" . $row['file_name'],
        "thumbnail" => "https://www.annesimonsson.se/konst/thumb/" . $row['file_name'],
        "width" => $row['width_px'],
        "height" => $row['height_px'],
        "encodingFormat" => "image/webp",
        "creator" => [
          "@type" => "Person",
          "name" => $row['photographer_name'] ?? "Anne Hamrin Simonsson"
        ]
      ]
    ]
  ];

  // Lägg till fysiska mått om de finns
  if (!empty($row['height_cm'])) {
    $jsonLd["@graph"][0]["height"] = ["@type" => "Distance", "name" => $row['height_cm'] . " cm"];
    $jsonLd["@graph"][0]["width"] = ["@type" => "Distance", "name" => $row['width_cm'] . " cm"];
    if (!empty($row['depth_cm'])) {
      $jsonLd["@graph"][0]["depth"] = ["@type" => "Distance", "name" => $row['depth_cm'] . " cm"];
    }
  }

  // Avancerad platsinformation (din nya data + din hasMap/geo logik)
  if (!empty($row['geo_location'])) {
    $jsonLd["@graph"][0]["locationCreated"] = [
      "@type" => "Place",
      "name" => $row['geo_location'],
      "hasMap" => $row['map_url'] ?? null,
      "address" => [
        "@type" => "PostalAddress",
        "addressLocality" => $row['address_locality'],
        "addressRegion" => $row['address_region'],
        "addressCountry" => $row['address_country']
      ]
    ];
  }

  // Koppling till projekt
  if (!empty($row['project'])) {
    $jsonLd["@graph"][0]["isPartOf"] = [
      "@type" => "CreativeWorkSeries",
      "name" => $row['project'],
      "creator" => ["@id" => "https://www.annesimonsson.se/#person"]
    ];
  }

  return json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
?>

<?php
$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$parts = explode('/', $uri);
if (count($parts) === 3 && $parts[0] === 'album' && in_array($parts[1], ['installations', 'paintings', 'objects'])) {
    $category = $parts[1];
    $file_id = $parts[2];
    $image = null;
    foreach ($images as $img) {
      if ($img->file_id == $file_id) {
        $image = $img;
        break;
      }
    }
}
?>

<head>
  <meta charset="utf-8">
  <?php
  function get_ld_json($slug) {
    $ldjson_dir = FCPATH . 'statics/ldjson/';
    $ld_json = [
      'installations' => file_get_contents($ldjson_dir . 'installations.json'),
      'objects' => file_get_contents($ldjson_dir . 'objects.json'),
      'paintings' => file_get_contents($ldjson_dir . 'paintings.json'),
      'startpage' => file_get_contents($ldjson_dir . 'landingpage.json'),
      'about' => file_get_contents($ldjson_dir . 'about.json'),
      'contact' => file_get_contents($ldjson_dir . 'contact.json'),
      'news' => file_get_contents($ldjson_dir . 'news.json'),
      'default' => file_get_contents($ldjson_dir . 'default.json')
    ];
    return $ld_json[$slug] ?? $ld_json['default'];
  }

  $page_title = strtolower($title);
  switch ($page_title) {
    case 'objects':
    case 'installations':
    case 'paintings':
      $ldslug = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
      // album/installations
      $ldjson = get_ld_json($page_title);
      break;
    case 'startpage':
    case 'about':
    case 'contact':
    case 'news':
      $ldjson = get_ld_json($page_title);
      break;
    default:
      $ldjson = get_ld_json('default');
      break;
  }
  ?>

  <?php
  switch (strtolower($title)) {
    case 'news':
      $page_title = 'News | Anne Hamrin Simonsson';
      $page_description = 'Keep up with Anne Hamrin Simonsson’s latest news, from Swedish Arts Grants Committee awards to current exhibitions at Kalmar Konstmuseum and more.';
      $og_image = "https://www.annesimonsson.se/konst/anne-hamrin-simonsson-liv-no-8-performance.webp";
      $og_image_width = "2971";
      $og_image_height = "1964";
      break;
    case 'about':
      $page_title = 'About | Anne Hamrin Simonsson';
      $page_description = 'Biography and artist statement of Anne Hamrin Simonsson, a Swedish conceptual artist based on Öland, known for her site-specific installations and objects.';
      $og_image = "https://www.annesimonsson.se/anne-hamrin-simonsson-portrait.jpg";
      $og_image_width = "320";
      $og_image_height = "320";
      break;
    case 'contact':
      $page_title = 'Contact | Anne Hamrin Simonsson';
      $page_description = 'Get in touch with Anne Hamrin Simonsson for inquiries, collaborations, or information regarding her conceptual art installations and paintings in Sweden.';
      $og_image = "https://www.annesimonsson.se/anne-hamrin-simonsson-portrait.jpg";
      $og_image_width = "320";
      $og_image_height = "320";
      break;
    case 'installations':
      $page_title = 'Installations | Anne Hamrin Simonsson';
      $page_description = 'Discover conceptual installation art by Anne Hamrin Simonsson. Explore site-specific works like Under_Liv at Kalmar Konstmuseum and Avfällningar at Undantaget.';
      $og_image = "https://www.annesimonsson.se/konst/anne-hamrin-simonsson-under-liv-rotvalta.webp";
      $og_image_width = "5472";
      $og_image_height = "3648";
      break;
    case 'objects':
      $page_title = 'Objects | Anne Hamrin Simonsson';
      $page_description = 'View contemporary objects and sculptures by Anne Hamrin Simonsson. Unique conceptual art created with precision using diverse materials and techniques.';
      $og_image = "https://www.annesimonsson.se/konst/anne-hamrin-simonsson-bacteria-fly.webp";
      $og_image_width = "2592";
      $og_image_height = "1944";
      break;
    case 'paintings':
      $page_title = 'Paintings | Anne Hamrin Simonsson';
      $page_description = 'Browse acrylic and oil paintings on masonite by Anne Hamrin Simonsson. Conceptual works inspired by the landscape of Öland and the themes of life and growth.';
      $og_image = "https://www.annesimonsson.se/album/paintings/sparris-no-2";
      $og_image_width = "2648";
      $og_image_height = "2640";
      break;
    default:
      $og_image = "https://www.annesimonsson.se/konst/anne-hamrin-simonsson-under-liv-rotvalta.webp";
      $og_image_width = "5472";
      $og_image_height = "3648";
      $page_title = empty($title)
        ? 'Anne Hamrin Simonsson – Swedish Conceptual Artist, Paintings, Installations'
        : 'Anne Hamrin Simonsson';
      if (empty($title)) {
        $page_description = 'Official website of Anne Hamrin Simonsson, a Swedish conceptual artist. Explore her paintings, installations, objects, and news. Discover her unique work today.';
      } else {
        $page_description = 'Official website of Swedish artist Anne Hamrin Simonsson; ' . $title . '. Discover conceptual art, paintings, and installations with unique artist insights.';
      }
      break;
  }
  if (isset($image) && !is_null($image) && $title !== "Startpage") {
    $page_title = $image->title . " | Anne Hamrin Simonsson";
    $page_description = $image->caption;
    $og_image = "https://www.annesimonsson.se/konst/" . $image->file_name;
    $og_image_width = $image->width_px;
    $og_image_height = $image->height_px;
    $ldjson = generateEnhancedJsonLd(
      (array)$image,
      $album_path ?? ''
    );
  }
  ?>
  
  <title><?= $page_title ?></title>
  <meta name="description" content="<?= $page_description ?>">
  <meta name="format-detection" content="telephone=no, date=no">
  <meta property="og:title" content="<?= $page_title ?>">
  <meta property="og:description"
        content="<?= $page_description ?>">
  <meta property="og:image"
        content="<?= $og_image ?>">
  <meta property="og:image:width" content="<?= $og_image_width ?>">
  <meta property="og:image:height" content="<?= $og_image_height ?>">

  <meta property="og:url" content="https://www.annesimonsson.se<?= $_SERVER['REQUEST_URI'] ?>">
  <link rel="canonical" href="https://www.annesimonsson.se<?= $_SERVER['REQUEST_URI'] ?>"/>
  <?php if (isset($ldjson) && $ldjson): ?>
    <script type="application/ld+json">
      <?= $ldjson ?>
    </script>
  <?php endif; ?>

  <meta name="author" content="The website is made by Simon Kotlinski">
  <meta name="robots" content="index,follow">



  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->
  <link rel="icon" href="<?= base_url('favicon.ico') ?>" type="image/x-icon"/>

  <!-- CSS: implied media=all -->
  <!-- CSS concatenated and minified via ant build script-->

  <!-- Add fancyBox -->
  <link rel="stylesheet" href="<?= base_url('statics/fancybox/source/jquery.fancybox.css?v=2.0.4') ?>" media="print" onload="this.media='all'"/>

  <link rel="stylesheet" href="<?= base_url('statics/fancybox/source/helpers/jquery.fancybox-thumbs.css?v=2.0.4') ?>" media="print" onload="this.media='all'"/>


  <link rel="stylesheet" href="<?= base_url('statics/fancybox/source/helpers/jquery.fancybox-buttons.css?v=2.0.4') ?>"
        media="print" onload="this.media='all'"/>
  <style>
    <?php readfile('statics/css/styles.css'); ?>
  </style>

<!--  <link rel="stylesheet" href="<?php /*= base_url('statics/css/styles.css?v=20251203-3') */?>" />-->
  <!-- end CSS-->

  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except for Modernizr / Respond.
  Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
  For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
  <script src="<?= base_url('statics/js/libs/modernizr-2.0.6.min.js') ?>" defer></script>

  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-Q81HEN1V5E"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){ dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', 'G-Q81HEN1V5E');
  </script>

  <script>
    function updateJsonLdForImage(
      title, description, filename, file_id, album_path, project, geo_location,
      width_px, height_px, alternate_name, artform, art_medium, artwork_surface, art_edition,
      height_cm, width_cm, depth_cm, map_url, address_locality, address_region, address_country, photographer_name
    ) {
      // Extract year from description if not provided
      var yearMatch = description?.match(/\b(19|20)\d{2}\b/);
      var yearCreated = yearMatch ? yearMatch[0] : null;

      var jsonLd = {
        "@context": "https://schema.org",
        "@graph": [
          {
            "@type": "VisualArtwork",
            "@id": "https://www.annesimonsson.se" + album_path + "/" + file_id + "#artwork",
            "name": title,
            "alternateName": alternate_name || "",
            "description": description,
            "dateCreated": yearCreated,
            "artform": artform || "Visual Artwork",
            "artMedium": art_medium || "",
            "artworkSurface": artwork_surface || "",
            "artEdition": art_edition || "",
            "creator": {
              "@type": "Person",
              "@id": "https://www.annesimonsson.se/#person",
              "name": "Anne Hamrin Simonsson",
              "sameAs": ["https://www.wikidata.org/wiki/Q137808007"]
            }
          },
          {
            "@type": "ImageObject",
            "@id": "https://www.annesimonsson.se" + album_path + "/" + file_id + "#image",
            "url": "https://www.annesimonsson.se/konst/" + filename,
            "contentUrl": "https://www.annesimonsson.se/konst/" + filename,
            "thumbnail": "https://www.annesimonsson.se/konst/thumb/" + filename,
            "width": width_px,
            "height": height_px,
            "encodingFormat": "image/webp",
            "creator": {
              "@type": "Person",
              "name": photographer_name || "Anne Hamrin Simonsson"
            }
          }
        ]
      };

      // Add physical dimensions if available
      if (height_cm) {
        jsonLd["@graph"][0]["height"] = { "@type": "Distance", "name": height_cm + " cm" };
        jsonLd["@graph"][0]["width"] = { "@type": "Distance", "name": width_cm + " cm" };
        if (depth_cm) {
          jsonLd["@graph"][0]["depth"] = { "@type": "Distance", "name": depth_cm + " cm" };
        }
      }

      // Add advanced location info if available
      if (geo_location) {
        jsonLd["@graph"][0]["locationCreated"] = {
          "@type": "Place",
          "name": geo_location,
          "hasMap": map_url || null,
          "address": {
            "@type": "PostalAddress",
            "addressLocality": address_locality || "",
            "addressRegion": address_region || "",
            "addressCountry": address_country || ""
          }
        };
      }

      // Add project info if available
      if (project) {
        jsonLd["@graph"][0]["isPartOf"] = {
          "@type": "CreativeWorkSeries",
          "name": project,
          "creator": { "@id": "https://www.annesimonsson.se/#person" }
        };
      }

      // Inject into script tag
      var $jsonLdScript = $('script[type="application/ld+json"]');
      if ($jsonLdScript.length) {
        $jsonLdScript.text(JSON.stringify(jsonLd, null, 2));
      }
    }
  </script>
</head>

<body>

<div id="container">
  <div id="sHeader">
    <div id="headspan">
      <div id="header" style='margin: 33px 0;'>
        <a href="<?= base_url('startpage') ?>" style="color:inherit;text-decoration:none;">ANNE HAMRIN SIMONSSON</a>
      </div>
      <?
      $list = array("news" => "NEWS",
        "album" => "ARTWORK",
        "about" => "ABOUT",
        "contact" => "CONTACT"
      );

      if (!isset($menu_item)) {
        $menu_item = '';
      }
      ?>
      <nav class="menu" aria-label="Main menu">
        <ul>
          <?php
          $i = 0;
          foreach ($list as $key => $item) {
            // Behåll specialstilen för första elementet (om det behövs för din layout)
            $special_attribute = ($i == 0) ? 'style="float:left;"' : '';

            // Fixa länken
            $href = ($key === 'album') ? base_url($key . '/installations') : base_url($key);

            // Kolla om sidan är aktiv
            $is_active = ($key == $menu_item);
            $active_class = $is_active ? 'class="current"' : '';
            $aria_current = $is_active ? 'aria-current="page"' : '';
            ?>

            <li>
              <a <?= $special_attribute ?>
                href="<?= $href ?>"
                <?= $active_class ?>
                <?= $aria_current ?>
                id="<?= $i == 0 ? 'spec' : '' ?>">
                <?= $item ?>
              </a>
            </li>

            <?php $i++; ?>
          <?php } ?>
        </ul>
      </nav>

      <?php if ($menu_item == 'album') { ?>
        <nav class="submenu menu" aria-label="Art categories" style="margin-top:10px;">
          <ul>
            <?php foreach ($submenu as $key => $submenu_item) {
              $is_sub_active = ($selected_filter == $submenu_item['name']);
              $sub_active_class = $is_sub_active ? 'class="current"' : '';
              $sub_aria_current = $is_sub_active ? 'aria-current="page"' : '';
              ?>

              <li>
                <a href="<?= base_url('album/' . $submenu_item['name']) ?>"
                  <?= $sub_active_class ?>
                  <?= $sub_aria_current ?>>
                  <?= strtoupper($submenu_item['name']) ?>
                </a>
              </li>

            <?php } ?>
          </ul>
        </nav>
      <?php } ?>
      <hr/>
    </div>
  </div>

  <div id="sMain" role="main">
    <div id="bodyspan">
      <? if ($this->session->userdata('logged_in')) { ?>

        <div class="aboutHeader">Administration</div>
        <p>
          Du är inloggad som <?= $this->session->userdata('username'); ?>. Du kan använda den vanliga menyn
          för att komma till motsvarande admin-sidan.
        </p>

        <ul>
          <li>
            <a href="<?= base_url('startpage'); ?>">Edit startpage</a>
          </li>
          <li>
            <a href="<?= base_url('image_admin'); ?>">Image administration</a>
          </li>
          <li>
            <a href="<?= base_url('news'); ?>">News administration</a>
          </li>
          <li>
            <a href="<?= base_url('about'); ?>">About administration</a>
          </li>
          <li>
            <a href="<?= base_url('contact'); ?>">Contact administration</a>
          </li>
        </ul>
        <br/>
        <button id="generate-sitemap">Generate Sitemap</button>
        <p>Generate a new Sitemap after images has been added or modified.</p>
        <script>
          document.getElementById('generate-sitemap').onclick = function() {
            fetch('<?= base_url('sitemap/generate') ?>', { method: 'POST' })
              .then(r => {
                r.text().then( data => {
                  const { success } = JSON.parse(data);
                  try {
                    if (success) alert('Sitemap generated!');
                    else alert('Failed to generate sitemap.');
                  } catch (e) {
                    throw new Error('Invalid JSON response: ' + data);
                  }
                });
              })
              .catch(err => {
                alert('Error: ' + err.message);
              });
          };
        </script>
        <form action="<?= site_url('/login/logout/') ?>" method="post" style="display:inline;">
          <button type="submit">Logout</button>
        </form>
        <br/>
        <hr/>
      <? } ?>
      <?php
      // Definiera H1 baserat på sidan
      $h1_text = "";
      switch (strtolower($title)) {
        case 'news': $h1_text = "News"; break;
        case 'about': $h1_text = "About Anne Hamrin Simonsson"; break;
        case 'contact': $h1_text = "Contact"; break;
        case 'installations': $h1_text = "Installations"; break;
        case 'objects': $h1_text = "Sculptures & Objects"; break;
        case 'paintings': $h1_text = "Paintings"; break;
        default:
          // På startsidan kan namnet få vara H1
          $h1_text = empty($title) ? "Anne Hamrin Simonsson" : $title;
          break;
      }
      ?>
      <h1 class="visually-hidden"><?= $h1_text ?></h1>



<!DOCTYPE html>
<html class="no-js" lang="en" >

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
      $slug = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
      // album/installations
      $json = get_ld_json($page_title);
      break;
    case 'startpage':
    case 'about':
    case 'contact':
    case 'news':
      $json = get_ld_json($page_title);
      break;
    default:
      $json = get_ld_json('default');
      break;
  }
  ?>
  <?php if (isset($json) && $json): ?>
    <script type="application/ld+json">
      <?= $json ?>
    </script>
  <?php endif; ?>

  <?php
  switch (strtolower($title)) {
    case 'news':
      $page_title = 'News | Anne Hamrin Simonsson';
      $page_description = 'Latest news and updates from Swedish artist Anne Hamrin Simonsson.';
      break;
    case 'about':
      $page_title = 'About | Anne Hamrin Simonsson';
      $page_description = 'Learn more about Swedish conceptual artist Anne Hamrin Simonsson.';
      break;
    case 'contact':
      $page_title = 'Contact | Anne Hamrin Simonsson';
      $page_description = 'Contact Anne Hamrin Simonsson, Swedish conceptual artist, for inquiries and collaborations.';
      break;
    case 'installations':
      $page_title = 'Installations | Anne Hamrin Simonsson';
      $page_description = 'Explore installations by Anne Hamrin Simonsson, Swedish conceptual artist.';
      break;
    case 'objects':
      $page_title = 'Objects | Anne Hamrin Simonsson';
      $page_description = 'Discover objects created by Anne Hamrin Simonsson, Swedish conceptual artist.';
      break;
    case 'paintings':
      $page_title = 'Paintings | Anne Hamrin Simonsson';
      $page_description = 'View paintings by Anne Hamrin Simonsson, Swedish conceptual artist.';
      break;
    default:
      $page_title = empty($title)
        ? 'Anne Hamrin Simonsson – Swedish Conceptual Artist, Paintings, Installations, Objects'
        : 'Anne Hamrin Simonsson';
      if (empty($title)) {
        $page_description = 'Discover the official website of Anne Hamrin Simonsson, a Swedish conceptual artist. Explore her paintings, installations, objects, news, and contact information. All images and texts belong to Anne Hamrin Simonsson.';
      } else {
        $page_description = 'Official website of Swedish artist Anne Hamrin Simonsson; ' . $title . '. All images and texts belong to Anne Hamrin Simonsson.';
      }
      break;
  }
  ?>
  <title><?= $page_title ?></title>
  <meta name="description" content="<?= $page_description ?>">
  <meta name="format-detection" content="telephone=no, date=no">
  <meta property="og:title" content="Anne Hamrin Simonsson - Artwork">
  <meta property="og:description"
        content="Official website of Swedish artist Anne Hamrin Simonsson. View artwork, news, and contact information.">
  <meta property="og:image"
        content="https://www.annesimonsson.se/konst/medium/anne-simonsson-konstverk-smalandstrienalen-rotvalta.jpg">
  <meta property="og:url" content="https://www.annesimonsson.se/">

  <meta name="author" content="The website is made by Simon Kotlinski">
  <meta name="robots" content="index,follow">


  <link rel="canonical" href="https://www.annesimonsson.se<?= $_SERVER['REQUEST_URI'] ?>"/>

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

</head>

<body>

<div id="container">
  <div id="sHeader">
    <div id="headspan">
      <div id="header">
        <h1 style="margin: 33px 0;">
          <a href="<?= base_url('startpage') ?>" style="color:inherit;text-decoration:none;">ANNE HAMRIN SIMONSSON</a>
        </h1>
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

      <div class="menu">
        <ul>
          <?
          $i = 0;
          foreach ($list as $key => $item) {
            $special_attribute = "";
            if ($i == 0) {
              $special_attribute = 'style="float:left;"';
            } ?>
            <li>
              <h2>
                <?php
                $href = ($key === 'album') ? base_url($key . '/installations') : base_url($key);
                ?>
                <a <?= $special_attribute ?>
                  href="<?= $href ?>" <?= ($key == $menu_item ? 'class="current"' : '') ?>
                  id="<?= $i == 0 ? 'spec' : '' ?>"><?= $item ?></a>
              </h2>
            </li>
            <? $i++; ?>
          <? } ?>
        </ul>

      </div>
      <? if ($menu_item == 'album') { ?>
        <div class="submenu menu" style="margin-top:10px;">
          <ul>
            <? foreach ($submenu as $key => $submenu_item) {
              if ($selected_filter == $submenu_item['name']) {
                echo '<li><h3><a href="' . base_url('album/' . $submenu_item['name']) . '" class="current">' . strtoupper($submenu_item['name']) . '</a></h3></li>';
              } else {
                echo '<li><h3><a href="' . base_url('album/' . $submenu_item['name']) . '">' . strtoupper($submenu_item['name']) . '</a></h3></li>';
              }
            } ?>
          </ul>
        </div>
      <? } ?>
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
        <a href="<?= site_url('/login/logout/') ?>">Click here to logout.</a><br/>

        <br/>
        <hr/>
      <? } ?>



<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="preload" fetchpriority="high" as="image" href="<?= base_url('konst/' . $image['file_name']) ?>" type="image/webp">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no, date=no">
  <title><?= $title ?></title>

  <meta name="robots" content="<?= $robots ?>">

  <?php if ($robots !== 'noindex,nofollow'): ?>
    <link rel="canonical" href="<?= current_url() ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:title" content="<?= $title ?>">
    <meta name="description" content="<?= $description ?>">
    <meta property="og:description" content="<?= $description ?>">
    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= $og_image ?>">
    <meta property="og:image:width" content="<?= $og_image_width ?>">
    <meta property="og:image:height" content="<?= $og_image_height ?>">
  <?php endif; ?>

  <?= $this->renderSection('ldjson') ?>

  <meta name="author" content="The website is made by Simon Kotlinski">
  <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
  <!--<link rel="stylesheet" href="<?php /*= base_url('css/style.css') */?>">
  <link rel="stylesheet" href="<?php /*= base_url('css/fullscreen.css') */?>">-->
  <style>
    <?= file_get_contents(FCPATH . 'css/style.css'); ?>
    <?= file_get_contents(FCPATH . 'css/fullscreen.css'); ?>
  </style>
</head>
<body>
<main class="fullscreen-content">
  <?= $this->renderSection('content') ?>
</main>
</body>
</html>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="format-detection" content="telephone=no, date=no">
  <title><?= $title ?></title>

  <meta name="robots" content="<?= $robots ?>">

  <?php if ($robots !== 'noindex,nofollow'): ?>
    <?php $meta_description_content = $meta_description ?? ($description ?? ''); ?>
    <?php $meta_keywords_content = $meta_keywords ?? ($keywords ?? ''); ?>
    <link rel="canonical" href="<?= current_url() ?>">
    <meta property="og:url" content="<?= current_url() ?>">
    <meta property="og:title" content="<?= $title ?>">
    <meta name="description" content="<?= esc($meta_description_content, 'attr') ?>">
    <meta property="og:description" content="<?= esc($meta_description_content, 'attr') ?>">
    <?php if (!empty($meta_keywords_content)): ?>
      <meta name="keywords" content="<?= esc($meta_keywords_content, 'attr') ?>">
    <?php endif; ?>

    <meta property="og:type" content="website">
    <meta property="og:image" content="<?= $og_image ?>">
    <meta property="og:image:width" content="<?= $og_image_width ?>">
    <meta property="og:image:height" content="<?= $og_image_height ?>">
  <?php endif; ?>


  <?= $this->renderSection('ldjson') ?>

  <meta name="author" content="Anne Hamrin Simonsson">
  <meta name="publisher" content="Anne Hamrin Simonsson">
  <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
  <?php if (isset($lcp_image_url) && $lcp_image_url !== ''): ?>
    <link rel="preload" as="image" href="<?= $lcp_image_url ?>" fetchpriority="high">
  <?php endif; ?>
  <style>
    <?= file_get_contents(FCPATH . 'css/layout.css') ?>
    <?= file_get_contents(FCPATH . 'css/style.css') ?>
  </style>
  <?php if (session()->get('is_logged_in')): ?>
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="<?= base_url('css/admin.css') ?>"></noscript>
  <?php endif; ?>

  <?= view('partials/google_analytics', ['googleAnalyticsId' => config('App')->googleAnalyticsId]) ?>
  <?= view('partials/microsoft_clarity', ['clarityProjectId' => config('App')->clarityProjectId]) ?>

</head>
<?php
$bodyClassParts = [];
if (!empty($selected_menu_item)) {
  $bodyClassParts[] = 'page-' . preg_replace('/[^a-z0-9\-]/', '', strtolower((string)$selected_menu_item));
}
if (!empty($body_class)) {
  $bodyClassParts[] = preg_replace('/[^a-z0-9\-\s]/', '', strtolower((string)$body_class));
}
$bodyClass = trim(implode(' ', $bodyClassParts));
?>
<body<?= $bodyClass !== '' ? ' class="' . esc($bodyClass, 'attr') . '"' : '' ?>>

<div class="site-wrapper">
<?php if (!isset($hide_main_header) || !$hide_main_header): ?>
  <header class="main-header">
    <div class="site-branding"><a href="<?= base_url('/') ?>" title="Anne Hamrin Simonsson home">ANNE HAMRIN SIMONSSON</a></div>
  </header>
  <nav class="main-nav">
    <div class="main-nav-inner">
    <?php
    $menu_items = ['news', 'artwork', 'about', 'contact'];
    foreach ($menu_items as $item): ?>
      <a href="<?= base_url($item) ?>" title="<?= esc(ucfirst($item), 'attr') ?>"<?= $item === $selected_menu_item ? ' class="current"' : '' ?>><?= $item ?></a>
    <?php endforeach; ?>
    </div>
  </nav>
<?php endif; ?>


<main class="content">
  <?php if (session()->get('is_logged_in')): ?>
    <div class="contained">
      <h2>Administration</h2><br/>
      <p>You are signed in. You can update each page, use the menu to navigate.</p>
      <div class="admin-notice">
        <button class="admin-action-btn" onclick="window.location.href='<?= base_url('logout') ?>'">Log out</button>
      </div>
      <hr class="light admin-divider"/>
    </div>
    <?= $this->renderSection('admin_content') ?>
  <?php endif; ?>

  <?= $this->renderSection('content') ?>
</main>

<footer>
  <hr/>
  <div>Copyright &copy; Anne Hamrin Simonsson 2012-<?= date('Y') ?></div>
</footer>
</div>

<?php if (session()->get('is_logged_in')): ?>
  <script src="<?= base_url('js/markdown-editor.js') ?>" defer></script>
<?php endif; ?>
</body>
</html>
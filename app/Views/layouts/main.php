<!DOCTYPE html>
<html lang="en">
<head>
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

  <meta name="author" content="Simon Kotlinski">
  <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
  <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/layout.css') ?>">
  <?php if (session()->get('isLoggedIn')): ?>
    <link rel="stylesheet" href="<?= base_url('css/admin.css') ?>">
  <?php endif; ?>

</head>
<body>

<?php if (!isset($hide_main_header) || !$hide_main_header): ?>
<header class="main-header">
  <div class="site-branding">ANNE HAMRIN SIMONSSON</div>
  <nav class="main-nav">
    <?php
    $menu_items = ['news', 'artwork', 'about', 'contact'];
    foreach ($menu_items as $item): ?>
      <a href="<?= base_url($item) ?>"<?= $item === $selected_menu_item ? ' class="current"' : '' ?>><?= $item ?></a>
    <?php endforeach; ?>
  </nav>
</header>
<?php endif; ?>


<main class="content">
  <?php if (session()->get('isLoggedIn')): ?>
    <div class="contained">
      <h2>Administration</h2><br/>
      <p>You are signed in. You can update each page, use the menu to navigate.</p>
      <div class="admin-notice">
        <button onclick="window.location.href='<?= base_url('logout') ?>'">Log out</button>
      </div>
    <hr class="light admin-divider"/>
    </div>
    <?= $this->renderSection('adminContent') ?>
  <?php endif; ?>

  <?= $this->renderSection('content') ?>
</main>

<footer>
  <hr/>
  <div>Copyright &copy; Anne Hamrin Simonsson 2012-<?= date('Y') ?></div>
</footer>

<?php if (isset($lcp_image_url)): ?>
  <link rel="preload" as="image" href="<?= $lcp_image_url ?>">
<?php endif; ?>
<?php if (session()->get('isLoggedIn')): ?>
  <script src="<?= base_url('js/markdown-editor.js') ?>" defer></script>
<?php endif; ?>
</body>
</html>
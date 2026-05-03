<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
// Build responsive srcset for preload so the browser only fetches one size.
$_fn   = $image['file_name'] ?? '';
$_origW = isset($image['width_px'])  ? (int)$image['width_px']  : 0;
$_origH = isset($image['height_px']) ? (int)$image['height_px'] : 0;
$_variantDefs = [
  ['dir' => 'small',   'maxW' => 800,  'maxH' => 600],
  ['dir' => 'medium',  'maxW' => 1280, 'maxH' => 960],
  ['dir' => 'large',   'maxW' => 1920, 'maxH' => 1440],
  ['dir' => 'x-large', 'maxW' => 2560, 'maxH' => 1920],
];
$_preloadEntries = [];
$_variantWidths = []; // widths of generated variants (excluding original)
if ($_fn !== '' && $_origW > 0 && $_origH > 0) {
  foreach ($_variantDefs as $_def) {
    if (is_file(FCPATH . 'konst/' . $_def['dir'] . '/' . $_fn)) {
      $_scale = min($_def['maxW'] / $_origW, $_def['maxH'] / $_origH, 1.0);
      $_w     = max(1, (int)round($_origW * $_scale));
      $_preloadEntries[] = base_url('konst/' . $_def['dir'] . '/' . $_fn) . ' ' . $_w . 'w';
      $_variantWidths[] = $_w;
    }
  }
  $_preloadEntries[] = base_url('konst/' . $_fn) . ' ' . $_origW . 'w';
  $_variantWidths[] = $_origW;
}
$_preloadSrcset = implode(', ', $_preloadEntries);
$_preloadSizes  = '96vw';
if ($_origW > 0 && $_origH > 0) {
  $_ratio = $_origW / $_origH;
  $_ratioStr = rtrim(rtrim(number_format($_ratio, 6, '.', ''), '0'), '.');
  // Cap CSS-pixel target width to the SECOND-largest available variant width.
  // This prevents the browser from picking the largest (often original/x-large)
  // variant for DPR=1 just to cover a tiny CSS-pixel overshoot
  // (e.g. 1923 CSS px requesting 2560w when 1920w would be visually fine).
  // HiDPI displays will still naturally upgrade to the largest variant via DPR scaling.
  sort($_variantWidths);
  $_uniqW = array_values(array_unique($_variantWidths));
  $_capPx = count($_uniqW) >= 2 ? $_uniqW[count($_uniqW) - 2] : end($_uniqW);
  if (!$_capPx) { $_capPx = $_origW; }
  $_preloadSizes = 'min(90vw, calc((90vh - 40px) * ' . $_ratioStr . '), ' . $_capPx . 'px)';
}
// href: fallback for browsers that don't support imagesrcset; use a mid-size
// variant (large or smaller) rather than the biggest, so non-srcset clients
// don't pay for an oversized download. Modern browsers will use imagesrcset.
$_preloadHref = base_url('konst/' . $_fn);
if (count($_preloadEntries) > 0) {
  // Prefer 'large' if available, else the largest non-original entry
  $_largeUrl = base_url('konst/large/' . $_fn);
  foreach ($_preloadEntries as $_entry) {
    $_url = rtrim(preg_replace('/\s+\d+w$/', '', $_entry));
    if ($_url === $_largeUrl) {
      $_preloadHref = $_url;
      break;
    }
  }
  if ($_preloadHref === base_url('konst/' . $_fn)) {
    // Fall back to the second-to-last entry (largest variant before original)
    $_idx = max(0, count($_preloadEntries) - 2);
    $_preloadHref = rtrim(preg_replace('/\s+\d+w$/', '', $_preloadEntries[$_idx]));
  }
}
// href: fallback for browsers that don't support imagesrcset; use a mid-size
// variant (large or smaller) rather than the biggest, so non-srcset clients
// don't pay for an oversized download. Modern browsers will use imagesrcset.
$_preloadHref = base_url('konst/' . $_fn);
if (count($_preloadEntries) > 0) {
  // Prefer 'large' if available, else the largest non-original entry
  $_largeUrl = base_url('konst/large/' . $_fn);
  foreach ($_preloadEntries as $_entry) {
    $_url = rtrim(preg_replace('/\s+\d+w$/', '', $_entry));
    if ($_url === $_largeUrl) {
      $_preloadHref = $_url;
      break;
    }
  }
  if ($_preloadHref === base_url('konst/' . $_fn)) {
    // Fall back to the second-to-last entry (largest variant before original)
    $_idx = max(0, count($_preloadEntries) - 2);
    $_preloadHref = rtrim(preg_replace('/\s+\d+w$/', '', $_preloadEntries[$_idx]));
  }
}
?>
  <link rel="preload" fetchpriority="high" as="image"
    href="<?= $_preloadHref ?>"
    <?php if ($_preloadSrcset !== ''): ?>
    imagesrcset="<?= esc($_preloadSrcset, 'attr') ?>"
    imagesizes="<?= esc($_preloadSizes, 'attr') ?>"
    <?php endif; ?>
    type="image/webp">
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

  <meta name="author" content="Simon Kotlinski">
  <link rel="icon" type="image/x-icon" href="<?= base_url('favicon.ico') ?>">
  <style>
    <?= file_get_contents(FCPATH . 'css/image-fullscreen.css'); ?>
  </style>
</head>
<body>
<main class="fullscreen-content">
  <?= $this->renderSection('content') ?>
</main>
</body>
</html>


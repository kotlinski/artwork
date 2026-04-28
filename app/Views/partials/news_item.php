<?php
$item = $item ?? [];
$idx = isset($idx) ? (int)$idx : 0;
$total = isset($total) ? (int)$total : 0;
$showAdmin = !empty($showAdmin);
$includeDataAttrs = !empty($includeDataAttrs);
$articleIdPrefix = isset($articleIdPrefix) ? (string)$articleIdPrefix : 'news-';
$projectTitleById = $projectTitleById ?? [];
$headingLevel = isset($headingLevel) ? (int)$headingLevel : 2;
$headingTag = in_array($headingLevel, [1, 2, 3, 4, 5, 6], true) ? 'h' . $headingLevel : 'h2';

$slug = (string)($item['slug'] ?? '');
$articleId = $slug !== '' ? $articleIdPrefix . $slug : '';
$showDivider = $total > 0 ? ($idx < $total - 1) : false;

$mainImage = $item['main_image'] ?? '';
if (is_string($mainImage) && str_starts_with($mainImage, 'news/')) {
  $mainImage = 'media/news/' . ltrim(substr($mainImage, 5), '/');
}

$mainImageThumb = $item['main_image_thumb'] ?? $mainImage;
$mainImageThumb2x = $item['main_image_thumb2x'] ?? $mainImageThumb;
$mainImageFull = $mainImage !== '' ? $mainImage : $mainImageThumb;
$mainImageSmall = $item['main_image_small'] ?? $mainImageFull;
$mainImageMedium = $item['main_image_medium'] ?? $mainImageSmall;
$mainImageLarge = $item['main_image_large'] ?? $mainImageMedium;
$mainImageXLarge = $item['main_image_x_large'] ?? $mainImageLarge;

$expandedSrcset = '';
$expandedSizes = '96vw';
$srcsetByUrl = [];
$origW = isset($item['width_px']) ? (int)$item['width_px'] : 0;
$origH = isset($item['height_px']) ? (int)$item['height_px'] : 0;
if ($origW > 0 && $origH > 0) {
  $ratio = $origW / $origH;
  $ratioStr = rtrim(rtrim(number_format($ratio, 6, '.', ''), '0'), '.');
  $expandedSizes = 'min(calc(100vw - 40px), calc(92vh * ' . $ratioStr . '), 1400px, ' . $origW . 'px)';

  $expandedDefs = [
    ['url' => $mainImageSmall,  'maxW' => 800,  'maxH' => 600],
    ['url' => $mainImageMedium, 'maxW' => 1280, 'maxH' => 960],
    ['url' => $mainImageLarge, 'maxW' => 1920, 'maxH' => 1440],
    ['url' => $mainImageXLarge, 'maxW' => 2560, 'maxH' => 1920],
    ['url' => $mainImageFull, 'maxW' => $origW, 'maxH' => $origH],
  ];

  foreach ($expandedDefs as $def) {
    $url = (string)($def['url'] ?? '');
    if ($url === '') {
      continue;
    }
    $scale = min($def['maxW'] / $origW, $def['maxH'] / $origH, 1.0);
    $variantW = max(1, (int)round($origW * $scale));
    if (!isset($srcsetByUrl[$url]) || $variantW > $srcsetByUrl[$url]) {
      $srcsetByUrl[$url] = $variantW;
    }
  }
}

if (!empty($srcsetByUrl)) {
  asort($srcsetByUrl);
  $parts = [];
  foreach ($srcsetByUrl as $url => $w) {
    $parts[] = base_url($url) . ' ' . $w . 'w';
  }
  $expandedSrcset = implode(', ', $parts);
}

$thumbW = isset($item['main_image_width']) && (int)$item['main_image_width'] > 0 ? (int)$item['main_image_width'] : 0;
$thumbH = isset($item['main_image_height']) && (int)$item['main_image_height'] > 0 ? (int)$item['main_image_height'] : 0;
if ($thumbW <= 0 || $thumbH <= 0) {
  $origW = isset($item['width_px']) ? (int)$item['width_px'] : 0;
  $origH = isset($item['height_px']) ? (int)$item['height_px'] : 0;
  if ($origW > 0 && $origH > 0) {
    $scale = min(122 / $origW, 122 / $origH, 1);
    $thumbW = max(1, (int)round($origW * $scale));
    $thumbH = max(1, (int)round($origH * $scale));
  } else {
    $thumbW = 122;
    $thumbH = 122;
  }
}

$isFirst = $idx === 0;
$lazyLoad = !$isFirst;
$linkedProjectId = (string)($item['project_id'] ?? '');
$linkedProjectTitle = $linkedProjectId !== '' ? ($projectTitleById[$linkedProjectId] ?? null) : null;
?>
<article<?= $articleId !== '' ? ' id="' . esc($articleId) . '"' : '' ?> class="news-item"<?= $includeDataAttrs ? ' data-project-id="' . esc($item['project_id'] ?? '') . '" data-slug="' . esc($item['slug'] ?? '') . '" data-content="' . htmlspecialchars($item['content'] ?? '', ENT_QUOTES) . '"' : '' ?>>
  <<?= $headingTag ?>><?= esc($item['title'] ?? '') ?></<?= $headingTag ?>>
  <div class="body">
    <?= $item['content_parsed'] ?? nl2br(esc($item['content'] ?? '')) ?>
  </div>

  <?php if (!empty($mainImageThumb)): ?>
    <div class="news-main-image" style="min-height:<?= $thumbH ?>px;">
      <button type="button" class="news-main-image-trigger"
              style="width:<?= $thumbW ?>px;height:<?= $thumbH ?>px;"
              data-full-image="<?= base_url($mainImageFull) ?>"
              <?php if ($expandedSrcset !== ''): ?>data-full-image-srcset="<?= esc($expandedSrcset, 'attr') ?>" data-full-image-sizes="<?= esc($expandedSizes, 'attr') ?>"<?php endif; ?>
              data-alt="<?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES) ?>"
              data-news-title="<?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES) ?>"
              aria-label="Open image in fullscreen">
        <img
          src="<?= base_url($mainImageThumb) ?>"
          srcset="<?= base_url($mainImageThumb) ?> 1x, <?= base_url($mainImageThumb2x) ?> 2x"
          width="<?= $thumbW ?>"
          height="<?= $thumbH ?>"
          style="width:<?= $thumbW ?>px;height:<?= $thumbH ?>px;display:block;"
          alt="<?= esc($item['title'] ?? '') ?>"
          loading="<?= $lazyLoad ? 'lazy' : 'eager' ?>"
          fetchpriority="<?= $isFirst ? 'high' : 'auto' ?>"
          decoding="async">
      </button>
    </div>
  <?php endif; ?>

  <?php if ($showAdmin && session()->get('isLoggedIn')): ?>
    <div class="news-admin-meta">
      Linked project:
      <strong><?= $linkedProjectTitle !== null ? esc($linkedProjectTitle) : 'None' ?></strong>
    </div>
    <div class="news-item-admin-actions">
      <button type="button" class="news-edit-btn"
              data-id="<?= esc($item['id'] ?? '') ?>"
              data-title="<?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES) ?>"
              data-content="<?= htmlspecialchars($item['content'] ?? '', ENT_QUOTES) ?>"
              data-project-id="<?= esc($item['project_id'] ?? '') ?>"
              data-category="<?= esc($item['category'] ?? 'general') ?>"
              data-main-image="<?= htmlspecialchars($item['main_image'] ?? '', ENT_QUOTES) ?>"
              data-event-location="<?= htmlspecialchars($item['event_location'] ?? '', ENT_QUOTES) ?>"
              data-event-start-date="<?= esc($item['event_start_date'] ?? '') ?>"
              data-event-end-date="<?= esc($item['event_end_date'] ?? '') ?>"
              data-external-link="<?= htmlspecialchars($item['external_link'] ?? '', ENT_QUOTES) ?>">Edit
      </button>
      <form method="post" action="<?= base_url('news/delete') ?>" class="news-delete-form"
            onsubmit="return confirm('Are you sure?');">
        <?= csrf_field() ?>
        <input type="hidden" name="id" value="<?= esc($item['id'] ?? '') ?>">
        <button type="submit" class="news-delete-link">delete</button>
      </form>
    </div>
  <?php endif; ?>

  <?php if ($showDivider): ?>
    <hr>
  <?php endif; ?>
</article>


<?php
$item = $item ?? [];
$idx = isset($idx) ? (int)$idx : 0;
$total = isset($total) ? (int)$total : 0;
$showAdmin = !empty($showAdmin);
$includeDataAttrs = !empty($includeDataAttrs);
$articleIdPrefix = isset($articleIdPrefix) ? (string)$articleIdPrefix : 'news-';
$projectTitleById = $projectTitleById ?? [];

$slug = (string)($item['slug'] ?? '');
$articleId = $slug !== '' ? $articleIdPrefix . $slug : '';
$showDivider = $total > 0 ? ($idx < $total - 1) : false;

$mainImage = $item['main_image'] ?? '';
if (is_string($mainImage) && str_starts_with($mainImage, 'news/')) {
  $mainImage = 'media/news/' . ltrim(substr($mainImage, 5), '/');
}

$mainImageThumb = $item['main_image_thumb'] ?? ($item['main_image_medium'] ?? $mainImage);
$mainImageThumb2x = $item['main_image_thumb2x'] ?? ($item['main_image_large'] ?? $mainImageThumb);
$mainImageFull = $mainImage !== '' ? $mainImage : $mainImageThumb;

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
  <h2><?= esc($item['title'] ?? '') ?></h2>
  <div class="body">
    <?= $item['content_parsed'] ?? nl2br(esc($item['content'] ?? '')) ?>
  </div>

  <?php if (!empty($mainImageThumb)): ?>
    <div class="news-main-image" style="min-height:<?= $thumbH ?>px;">
      <button type="button" class="news-main-image-trigger"
              style="width:<?= $thumbW ?>px;height:<?= $thumbH ?>px;"
              data-full-image="<?= base_url($mainImageFull) ?>"
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


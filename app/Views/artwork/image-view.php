<?= $this->extend('layouts/image-fullscreen') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= $jsonld ?>
</script>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
// Ensure all variables are available
$project = $project ?? null;
$image = $image ?? null;
$prev_slug = $prev_slug ?? null;
$next_slug = $next_slug ?? null;

// Default src: prefer 'large' over the original/x-large so non-srcset
// browsers don't pay for an oversized download. Modern browsers will
// still use srcset/sizes to pick the optimal variant.
$expandedSrc = base_url('konst/' . ($image['file_name'] ?? ''));
foreach (['large', 'medium', 'small', 'x-large'] as $_fallbackDir) {
  if (isset($image['file_name']) && is_file(FCPATH . 'konst/' . $_fallbackDir . '/' . $image['file_name'])) {
    $expandedSrc = base_url('konst/' . $_fallbackDir . '/' . $image['file_name']);
    break;
  }
}
$expandedSizes = '96vw';
$expandedSrcsetEntries = [];
$expandedVariantWidths = [];

$fileName = $image['file_name'] ?? '';
$origW = isset($image['width_px']) ? (int)$image['width_px'] : 0;
$origH = isset($image['height_px']) ? (int)$image['height_px'] : 0;
$variantDefs = [
  ['dir' => 'small',   'maxW' => 800,  'maxH' => 600],
  ['dir' => 'medium',  'maxW' => 1280, 'maxH' => 960],
  ['dir' => 'large', 'maxW' => 1920, 'maxH' => 1440],
  ['dir' => 'x-large', 'maxW' => 2560, 'maxH' => 1920],
];

if ($fileName !== '' && $origW > 0 && $origH > 0) {
  $ratio = $origW / $origH;
  $ratioStr = rtrim(rtrim(number_format($ratio, 6, '.', ''), '0'), '.');

  foreach ($variantDefs as $def) {
    $absPath = FCPATH . 'konst/' . $def['dir'] . '/' . $fileName;
    if (!is_file($absPath)) {
      continue;
    }
    $scale = min($def['maxW'] / $origW, $def['maxH'] / $origH, 1.0);
    $w = max(1, (int)round($origW * $scale));
    $expandedSrcsetEntries[] = base_url('konst/' . $def['dir'] . '/' . $fileName) . ' ' . $w . 'w';
    $expandedVariantWidths[] = $w;
  }
  $expandedSrcsetEntries[] = base_url('konst/' . $fileName) . ' ' . $origW . 'w';
  $expandedVariantWidths[] = $origW;

  // Cap CSS-pixel target width to the second-largest variant so the browser
  // doesn't pick the largest variant (e.g. x-large/2560w) just to cover a
  // small CSS-pixel overshoot. HiDPI naturally upgrades via DPR scaling.
  sort($expandedVariantWidths);
  $uniqW = array_values(array_unique($expandedVariantWidths));
  $capPx = count($uniqW) >= 2 ? $uniqW[count($uniqW) - 2] : end($uniqW);
  if (!$capPx) { $capPx = $origW; }
  $expandedSizes = 'min(90vw, calc((90vh - 40px) * ' . $ratioStr . '), ' . $capPx . 'px)';
}

$expandedSrcset = implode(', ', $expandedSrcsetEntries);
?>
<h1 class="visually-hidden"><?= $image['title'] ?></h1>
<div class="container">
  <div class="carousel-overlay">
    <button
      class="close-btn"
      onclick="handleClose(); return false;">
      &times;
    </button>

    <div class="image-counter">
      <?= $current_index+1 ?>/<?= $images_count ?>
    </div>

    <?php if ($prev_slug): ?>
      <a
        href="<?= base_url($project['slug'] . '/' . $prev_slug) ?>"
        onclick="window.location.replace(this.href); return false;"
        class="nav-btn prev-btn">
        <span>&#10094;</span>
      </a>
    <?php endif ?>
    <?php if ($next_slug): ?>
      <a
        href="<?= base_url($project['slug'] . '/' . $next_slug) ?>"
        onclick="window.location.replace(this.href); return false;"
        class="nav-btn next-btn">
        <span>&#10095;</span>
      </a>
    <?php endif ?>
    <figure
      class="media-wrapper"
      style="--w: <?= $image['width_px'] ?>; --h: <?= $image['height_px'] ?>; --img-w: <?= $image['width_px'] ?>px;"
    >
      <img
        src="<?= $expandedSrc ?>"
        <?php if ($expandedSrcset !== ''): ?>srcset="<?= esc($expandedSrcset, 'attr') ?>" sizes="<?= esc($expandedSizes, 'attr') ?>"<?php endif; ?>
        alt="<?= esc($image['caption'] ?? '') ?>"
        width="<?= $image['width_px'] ?>"
        height="<?= $image['height_px'] ?>"
        fetchpriority="high"
        decoding="sync"
        loading="eager"
        onload="this.classList.add('loaded')"
      >

      <figcaption>
        <span class="caption-text"><?= esc($image['caption'] ?? '') ?></span>
        <a href="#" onclick="handleClose(); return false;" id="close-btn">close</a>
        <span class="copyright-line">Copyright © Anne Hamrin Simonsson</span>
      </figcaption>
    </figure>
  </div>
</div>
<script>
  function handleClose() {
    var referrer = document.referrer;
    var same_origin = referrer && referrer.indexOf(window.location.origin) === 0;
    if (window.history.length > 1 && same_origin && referrer !== '' && referrer !== 'about:blank') {
      window.history.back();
    } else {
      window.location.replace('<?= base_url($project["slug"]) ?>');
    }
  }

  document.addEventListener('keydown', function (e) {
    var active = document.activeElement;
    if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA' || active.isContentEditable)) {
      return;
    }

    if (e.key === 'ArrowLeft') {
      e.preventDefault();
      var left_arrow = document.querySelector('.nav-btn.prev-btn');
      if (left_arrow && left_arrow.href) {
        window.location.replace(left_arrow.href);
      }
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      var right_arrow = document.querySelector('.nav-btn.next-btn');
      if (right_arrow && right_arrow.href) {
        window.location.replace(right_arrow.href);
      }
    } else if (e.key === 'Escape') {
      e.preventDefault();
      handleClose();
    }
  });
</script>
<?= $this->endSection() ?>

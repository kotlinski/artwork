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
        src="<?= base_url('konst/' . $image['file_name']) ?>"
        alt="<?= esc($image['caption'] ?? '') ?>"
        width="<?= $image['width_px'] ?>"
        height="<?= $image['height_px'] ?>"
        fetchpriority="high"
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

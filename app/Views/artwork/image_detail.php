<?= $this->extend('layouts/fullscreen') ?>

<?php $hide_main_header = true; ?>

<?= $this->section('content') ?>
<?php
// Ensure all variables are available
$project = $project ?? null;
$image = $image ?? null;
$prevSlug = $prevSlug ?? null;
$nextSlug = $nextSlug ?? null;
?>
<div class="container">
  <div class="carousel-overlay">
    <button
      class="close-btn"
      onclick="handleClose(); return false;">
      &times;
    </button>

    <?php if ($prevSlug): ?>
      <a
        href="<?= base_url($project['slug'] . '/' . $prevSlug) ?>"
        onclick="window.location.replace(this.href); return false;"
        class="nav-btn prev-btn">
        <span>&#10094;</span>
      </a>
    <?php endif ?>
    <?php if ($nextSlug): ?>
      <a
        href="<?= base_url($project['slug'] . '/' . $nextSlug) ?>"
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
        alt="<?= esc($image['title'] ?? '') ?>"
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
    // If there is a referrer and it is from the same origin, go back. Otherwise, go to project page.
    var referrer = document.referrer;
    var same_origin = referrer && referrer.indexOf(window.location.origin) === 0;
    if (window.history.length > 1 && same_origin) {
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
      var leftArrow = document.querySelector('.nav-btn.prev-btn');
      if (leftArrow && leftArrow.href) {
        window.location.replace(leftArrow.href);
      }
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      var rightArrow = document.querySelector('.nav-btn.next-btn');
      if (rightArrow && rightArrow.href) {
        window.location.replace(rightArrow.href);
      }
    } else if (e.key === 'Escape') {
      e.preventDefault();
      handleClose();
    }
  });
</script>
<?= $this->endSection() ?>

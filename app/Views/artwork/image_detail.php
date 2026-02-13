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
<div class="fullscreen-center-container">
  <?php if ($prevSlug): ?>
    <a
      href="<?= base_url($project['slug'] . '/' . $prevSlug) ?>"
      onclick="window.location.replace(this.href); return false;"
      class="carousel-arrow left-arrow">
      <span>&#x2039;</span>
    </a>
  <?php endif; ?>
  <div>
    <div class="image-caption-container">
      <div class="image-arrow-wrapper">
        <img id="carousel-image" src="<?= base_url('konst/' . $image['file_name']) ?>"
             alt="<?= esc($image['title'] ?? '') ?>"
             width="<?= $image['width_px'] ?>"
             height="<?= $image['height_px'] ?>"
             fetchpriority="high"
             loading="eager"/>
      </div>
      <div id="caption-row">
        <div id="caption-text">
          <?= esc($image['caption'] ?? '') ?>
        </div>
        <a
          href="#"
          onclick="handleClose(); return false;"
          id="close-btn">close</a>
      </div>
    </div>
  </div>
  <?php if ($nextSlug): ?>
    <a
      href="<?= base_url($project['slug'] . '/' . $nextSlug) ?>"
      onclick="window.location.replace(this.href); return false;"
      class="carousel-arrow right-arrow">
      <span>&#x203A;</span>
    </a>
  <?php endif; ?>
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
      var leftArrow = document.querySelector('.carousel-arrow.left-arrow');
      if (leftArrow && leftArrow.href) {
        window.location.replace(leftArrow.href);
      }
    } else if (e.key === 'ArrowRight') {
      e.preventDefault();
      var rightArrow = document.querySelector('.carousel-arrow.right-arrow');
      if (rightArrow && rightArrow.href) {
        window.location.replace(rightArrow.href);
      }
    } else if (e.key === 'Escape') {
      e.preventDefault();
      handleClose();
    }
  });
  document.getElementById('carousel-image').addEventListener('load', function () {
    document.getElementById('caption-row').style.display = 'flex';
  });
</script>
<?= $this->endSection() ?>

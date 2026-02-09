<?= $this->extend('layouts/main') ?>

<?php $hide_main_header = true; ?>

<?= $this->section('content') ?>
<?php
// Ensure all variables are available
$project = $project ?? null;
$image = $image ?? null;
$prevSlug = $prevSlug ?? null;
$nextSlug = $nextSlug ?? null;
?>
<div id="image-detail-container"
     style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 80vh;">
  <div style="position: relative; width: 100%; max-width: 600px;">
    <?php if ($prevSlug): ?>
      <a href="<?= base_url($project['slug'] . '/' . $prevSlug) ?>" class="carousel-arrow left-arrow"
         style="position: absolute; left: 0; top: 50%; transform: translateY(-50%); font-size: 2em; text-decoration: none; background: rgba(255,255,255,0.7); border-radius: 50%; padding: 0 12px;">&#8592;</a>
    <?php endif; ?>
    <img id="carousel-image" src="<?= base_url('konst/medium/' . $image['file_name']) ?>"
         alt="<?= esc($image['title'] ?? '') ?>"
         style="width: 100%; max-height: 60vh; object-fit: contain; border-radius: 8px; transition: opacity 0.4s;"/>
    <?php if ($nextSlug): ?>
      <a href="<?= base_url($project['slug'] . '/' . $nextSlug) ?>" class="carousel-arrow right-arrow"
         style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); font-size: 2em; text-decoration: none; background: rgba(255,255,255,0.7); border-radius: 50%; padding: 0 12px;">&#8594;</a>
    <?php endif; ?>
  </div>
  <div style="margin-top: 18px; display: flex; align-items: center;">
    <div style="flex: 1;">
      <div style="font-size: 1.1em; color: #333; margin-bottom: 8px;">
        <?= esc($image['title'] ?? '') ?>
      </div>
      <div style="font-size: 1em; color: #666;">
        <?= esc($image['caption'] ?? '') ?>
      </div>
    </div>
    <a href="<?= base_url($project['slug']) ?>"
       style="margin-left: 24px; background: #eee; border-radius: 4px; padding: 8px 16px; font-size: 1em; text-decoration: none; color: #333;">Stäng</a>
  </div>
</div>
<script>
  document.querySelectorAll('.carousel-arrow').forEach(function (arrow) {
    arrow.addEventListener('click', function (e) {
      var img = document.getElementById('carousel-image');
      img.style.opacity = 0;
      setTimeout(function () {
        window.location = arrow.href;
      }, 400);
      e.preventDefault();
    });
  });
</script>
<?= $this->endSection() ?>

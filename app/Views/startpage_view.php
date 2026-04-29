<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<?php if (!empty($startpage_jsonld)): ?>
<script type="application/ld+json">
<?= $startpage_jsonld ?>
</script>
<?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<?php
$actionError = session()->getFlashdata('error') ?? '';
$actionSuccess = session()->getFlashdata('success') ?? '';
?>
<div class="contained startpage-admin">
  <h2>Startpage Administration</h2>
  <p>Update the short text and upload a new startpage image.</p>
  <?php if ($actionError !== ''): ?>
    <div class="alert error"><?= esc($actionError) ?></div>
  <?php endif; ?>
  <?php if ($actionSuccess !== ''): ?>
    <div class="alert success"><?= esc($actionSuccess) ?></div>
  <?php endif; ?>

  <form method="post" action="<?= base_url('startpage/update') ?>" enctype="multipart/form-data" class="startpage-admin-form">
    <?= csrf_field() ?>
    <input type="hidden" name="id" value="<?= esc($startpage['id'] ?? '') ?>">
    <label>
      Description text
      <textarea name="startpage_text" rows="4" required><?= esc($startpage['text'] ?? '') ?></textarea>
    </label>
    <label>
      Image file
      <input type="file" name="startpage_image_file" accept=".jpg,.jpeg,.png,.webp">
      <small>Display image is generated at 380px (+2x), with small/mobile/medium/large fullscreen variants.</small>
    </label>
    <button type="submit" class="admin-action-btn">Save startpage</button>
  </form>
  <hr class="light admin-divider"/>
</div>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$text = trim((string) ($startpage['text'] ?? ''));
$caption = $text !== '' ? $text : 'Official website of Swedish artist Anne Hamrin Simonsson.';
$image = $startpage_image ?? [];
?>
<h1 class="visually-hidden">Startpage</h1>
<div class="contained startpage-content">
  <?php if (!empty($image)): ?>
    <div class="startpage-main-image">
      <button type="button"
              class="news-main-image-trigger startpage-main-image-trigger"
              data-full-image="<?= esc($image['full_url'] ?? '', 'attr') ?>"
              <?php if (!empty($image['expanded_srcset'])): ?>data-full-image-srcset="<?= esc($image['expanded_srcset'], 'attr') ?>" data-full-image-sizes="<?= esc($image['expanded_sizes'] ?? '96vw', 'attr') ?>"<?php endif; ?>
              data-alt="<?= esc($caption, 'attr') ?>"
              data-news-title="<?= esc($caption, 'attr') ?>"
              aria-label="Open startpage image in fullscreen">
        <img src="<?= esc($image['display_url'] ?? '', 'attr') ?>"
             srcset="<?= esc($image['display_url'] ?? '', 'attr') ?> 1x, <?= esc($image['display_2x_url'] ?? ($image['display_url'] ?? ''), 'attr') ?> 2x"
             width="<?= (int) ($image['display_width'] ?? 380) ?>"
             height="<?= (int) ($image['display_height'] ?? 280) ?>"
             alt="<?= esc($caption) ?>"
             fetchpriority="high"
             decoding="async">
      </button>
    </div>
  <?php endif; ?>

  <p class="startpage-caption"><?= nl2br(esc($caption)) ?></p>
</div>

<div id="news-image-fullscreen-modal" class="news-image-fullscreen-modal" style="display:none;" aria-hidden="true">
  <button type="button" class="news-image-fullscreen-close-top" data-news-image-close aria-label="Close image">&times;</button>
  <figure class="news-image-fullscreen-figure">
    <img id="news-image-fullscreen-img" src="" alt="">
    <figcaption class="news-image-fullscreen-caption">
      <span id="news-image-fullscreen-title" class="news-image-fullscreen-title"></span>
      <button type="button" id="news-image-fullscreen-close" class="news-image-fullscreen-close" data-news-image-close aria-label="Close image">close</button>
    </figcaption>
  </figure>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('news-image-fullscreen-modal');
    var modalImg = document.getElementById('news-image-fullscreen-img');
    var modalTitle = document.getElementById('news-image-fullscreen-title');
    var closeButtons = modal ? modal.querySelectorAll('[data-news-image-close]') : [];
    var triggers = document.querySelectorAll('.news-main-image-trigger');
    var lockedScrollY = 0;
    var isBodyLocked = false;

    if (!modal || !modalImg || !modalTitle || closeButtons.length === 0 || triggers.length === 0) {
      return;
    }

    function openModal(src, alt, title, srcset, sizes) {
      modalImg.srcset = srcset || '';
      modalImg.sizes = sizes || '';
      modalImg.src = src;
      modalImg.alt = alt || '';
      modalTitle.textContent = title || '';
      modal.style.display = 'flex';
      modal.setAttribute('aria-hidden', 'false');
      if (!isBodyLocked) {
        lockedScrollY = window.scrollY || window.pageYOffset || 0;
        document.body.style.position = 'fixed';
        document.body.style.top = '-' + lockedScrollY + 'px';
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
        document.body.style.overflow = 'hidden';
        isBodyLocked = true;
      }
    }

    function closeModal() {
      modal.style.display = 'none';
      modal.setAttribute('aria-hidden', 'true');
      modalImg.src = '';
      modalImg.srcset = '';
      modalImg.sizes = '';
      modalTitle.textContent = '';
      if (isBodyLocked) {
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        document.body.style.overflow = '';
        window.scrollTo(0, lockedScrollY);
        isBodyLocked = false;
      }
    }

    triggers.forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        openModal(
          trigger.dataset.fullImage || '',
          trigger.dataset.alt || '',
          trigger.dataset.newsTitle || trigger.dataset.alt || '',
          trigger.dataset.fullImageSrcset || '',
          trigger.dataset.fullImageSizes || ''
        );
      });
    });

    closeButtons.forEach(function (btn) {
      btn.addEventListener('click', closeModal);
    });

    modalImg.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (e) {
      if ((e.key === 'Escape' || e.key === 'Esc') && modal.style.display === 'flex') {
        closeModal();
      }
    });
  });
</script>
<?= $this->endSection() ?>


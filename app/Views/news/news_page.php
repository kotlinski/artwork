<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= file_get_contents(APPPATH . 'Data/LdJson/about.json') ?>


</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<div class="contained news-admin">
  <h2>News Administration</h2>
  <p>Expand a news title to update its markdown content.</p>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <?php foreach (($news_items ?? []) as $item): ?>
    <?php $newsId = (int) ($item['id'] ?? 0); ?>
    <div class="news-edit-expandable" id="news-admin-item-<?= $newsId ?>" data-news-id="<?= $newsId ?>">
      <button type="button"
              class="news-expand-toggle"
              aria-expanded="false"
              aria-controls="news-form-<?= $newsId ?>">
        <span class="news-chevron">▶</span>
        <span><?= esc($item['title'] ?? 'Untitled news item') ?></span>
      </button>
      <div class="news-edit-form" id="news-form-<?= $newsId ?>" style="display:none;">
        <?= view('partials/markdown_editor', [
          'formAction' => base_url('news/update'),
          'id' => $newsId,
          'fieldName' => 'content',
          'fieldValue' => $item['content'] ?? '',
          'title' => '',
          'editorId' => 'news-md-editor-' . $newsId,
          'fixed_width' => true,
          'titleField' => [
            'name'  => 'title',
            'label' => 'Title',
            'value' => $item['title'] ?? '',
          ],
        ]) ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const expandToggles = document.querySelectorAll('.news-expand-toggle');

    expandToggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        const parent = toggle.closest('.news-edit-expandable');
        const form = parent.querySelector('.news-edit-form');
        const chevron = toggle.querySelector('.news-chevron');
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

        document.querySelectorAll('.news-edit-form').forEach(function (section) {
          section.style.display = 'none';
        });
        document.querySelectorAll('.news-expand-toggle').forEach(function (button) {
          button.setAttribute('aria-expanded', 'false');
          const icon = button.querySelector('.news-chevron');
          if (icon) icon.style.transform = '';
        });

        if (!isExpanded) {
          form.style.display = 'block';
          toggle.setAttribute('aria-expanded', 'true');
          if (chevron) chevron.style.transform = 'rotate(90deg)';
        }
      });

      toggle.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          toggle.click();
        }
      });
    });

    // Re-expand the item that was just saved, based on URL hash
    const hash = window.location.hash;
    if (hash && hash.startsWith('#news-admin-item-')) {
      const expandable = document.querySelector(hash);
      if (expandable) {
        const toggle = expandable.querySelector('.news-expand-toggle');
        if (toggle) toggle.click();
      }
    }
  });
</script>
<?= $this->endSection() ?>


<?= $this->section('content') ?>
<h1 class="visually-hidden">About</h1>

<?php $news_items = $news_items ?? []; ?>
<div class='contained'>
  <?php foreach ($news_items as $item): ?>
    <?php
      $createdTs = strtotime($item['created_at']);
      if ($createdTs !== false) {
        $svMonths = [
          1 => 'januari', 2 => 'februari', 3 => 'mars', 4 => 'april',
          5 => 'maj', 6 => 'juni', 7 => 'juli', 8 => 'augusti',
          9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'december',
        ];
        $createdLabel = date('j', $createdTs) . ' ' . $svMonths[(int) date('n', $createdTs)] . ' ' . date('Y', $createdTs);
      } else {
        $createdLabel = esc($item['created_at']);
      }
    ?>
    <article id="<?= $item['slug'] ?>" class="news-item">
      <h2><?= esc($item['title']) ?></h2>
      <div class="date">
        <?= $createdLabel ?>
      </div>
      <div class="body">
        <?= $item['content_parsed'] ?: nl2br(esc($item['content'] ?? '')) ?>
      </div>
      <hr>
    </article>
  <?php endforeach; ?>
</div>
<?= $this->endSection() ?>


<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Blog",
        "@id": "<?= current_url() ?>#news-feed",
      "name": "Anne Simonsson - News & Updates",
      "blogPost": [
  <?php foreach ($news_items as $index => $item): ?>
        {
          "@type": "BlogPosting",
          "@id": "<?= current_url() ?>#<?= $item['slug'] ?>",
          "headline": "<?= esc($item['title']) ?>",
          "datePublished": "<?= date('c', strtotime($item['created_at'])) ?>",
          "description": "<?= esc($item['excerpt']) ?>",
          "image": "<?= base_url($item['main_image'] ?? 'assets/img/fallback-art.jpg') ?>",
          "author": {
            "@type": "Person",
            "name": "Anne Hamrin Simonsson",
            "url": "<?= base_url('about') ?>"
          }
          <?php if ($item['event_start_date']): ?>,
          "about": {
            "@type": "Event",
            "name": "<?= esc($item['title']) ?>",
            "startDate": "<?= $item['event_start_date'] ?>",
            "endDate": "<?= $item['event_end_date'] ?? $item['event_start_date'] ?>",
            "location": {
              "@type": "Place",
              "name": "<?= esc($item['event_location']) ?>"
            }
          }
          <?php endif; ?>
        }<?= ($index < count($news_items) - 1) ? ',' : '' ?>
  <?php endforeach; ?>
  ]
}
]
}
</script>

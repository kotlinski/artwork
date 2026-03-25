<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= file_get_contents(APPPATH . 'Data/LdJson/about.json') ?>


</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<?php /*= view('partials/markdown_editor', [
  'formAction' => base_url('about/update'),
  'id' => $about['id'],
  'fieldName' => 'about_text',
  'fieldValue' => $about['text'],
  'title' => 'Edit About Info (Markdown)'
]) */?>
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

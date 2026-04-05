<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= file_get_contents(APPPATH . 'Data/LdJson/about.json') ?>


</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<div class="contained news-admin">

</div>
<?= $this->endSection() ?>


<?= $this->section('content') ?>
<h1 class="visually-hidden">News</h1>

<?php $news_items = $news_items ?? []; ?>
<div class='contained'>
  <?php foreach ($news_items as $item): ?>
    <article id="<?= $item['slug'] ?>" class="news-item" data-project-id="<?= esc($item['project_id'] ?? '') ?>" data-slug="<?= esc($item['slug'] ?? '') ?>" data-content="<?= htmlspecialchars($item['content'] ?? '', ENT_QUOTES) ?>">
      <h2><?= esc($item['title']) ?></h2>
      <div class="body">
        <?= $item['content_parsed'] ?: nl2br(esc($item['content'] ?? '')) ?>
      </div>
      <?php if (session()->get('isLoggedIn')): ?>
        <div class="news-item-admin-actions">
          <button type="button" class="news-edit-btn"
            data-id="<?= esc($item['id']) ?>"
            data-title="<?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES) ?>"
            data-content="<?= htmlspecialchars($item['content'] ?? '', ENT_QUOTES) ?>"
            data-project-id="<?= esc($item['project_id'] ?? '') ?>">Edit</button>
        </div>
      <?php endif; ?>
      <hr>
    </article>
  <?php endforeach; ?>
</div>

<?php if (session()->get('isLoggedIn')): ?>
<!-- News Edit Modal -->
<div id="news-edit-modal" class="news-edit-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="news-edit-modal-heading">
  <div class="news-edit-modal-box">
    <button type="button" id="news-edit-modal-close" class="news-edit-modal-close" aria-label="Close">&times;</button>
    <h3 id="news-edit-modal-heading">Edit News Item</h3>

    <!-- Preview sub-modal -->
    <div id="news-edit-preview-modal" class="preview-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.5);z-index:3000;align-items:center;justify-content:center;">
      <div class="preview-modal-content news-edit-preview-modal-content" style="position:relative;">
        <button type="button" id="news-edit-preview-close" style="position:absolute;top:8px;right:12px;font-size:22px;background:none;border:none;cursor:pointer;">&times;</button>
        <div class="contained news-edit-preview-contained">
          <article class="news-item news-edit-preview-item">
            <div id="news-edit-preview-content" class="body"></div>
          </article>
        </div>
      </div>
    </div>

    <form id="news-edit-form" action="<?= base_url('news/update') ?>" method="post">
      <input type="hidden" name="id" id="news-edit-id">
      <label class="md-title-field">
        Title
        <input type="text" name="title" id="news-edit-title">
      </label>
      <label class="md-extra-field">
        Project
        <select name="project_id" id="news-edit-project">
          <option value="">— none —</option>
          <?php foreach ($projects ?? [] as $proj): ?>
            <option value="<?= esc($proj['id']) ?>"><?= esc($proj['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <div class="md-toolbar">
        <button type="button" onclick="mdWrap('news-edit-content', '**', '**')" title="Bold">B</button>
        <button type="button" onclick="mdWrap('news-edit-content', '*', '*')" title="Italic"><em>I</em></button>
        <button type="button" onclick="mdInsert('news-edit-content', '## ')" title="Heading">H</button>
        <button type="button" onclick="mdWrap('news-edit-content', '[', '](url)')" title="Link">🔗</button>
        <button type="button" onclick="mdInsert('news-edit-content', '- ')" title="Bullet List">• List</button>
        <button type="button" onclick="mdInsert('news-edit-content', '1. ')" title="Numbered List">1. List</button>
        <button type="button" onclick="mdInsert('news-edit-content', '  \n')" title="Line Break">↵</button>
        <button type="button" onclick="mdWrap('news-edit-content', '`', '`')" title="Code">&lt;/&gt;</button>
      </div>
      <textarea id="news-edit-content" name="content" class="news-edit-modal-textarea"></textarea>
      <div style="padding: 0 4px">
        <div class="form-actions">
          <button type="button" id="news-edit-preview-btn">Preview</button>
          <button type="submit">Save</button>
          <button type="button" id="news-edit-cancel-btn">Cancel</button>
        </div>
        <div>💡 Tip: "↵" adds a "soft line break"</div>
        <div>Soft line break: 2 spaces + new line</div>
        <div>New Paragraph: Use a blank line</div>
      </div>
    </form>
  </div>
</div>

<script src="<?= base_url('js/marked.min.js') ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var modal        = document.getElementById('news-edit-modal');
  var closeBtn     = document.getElementById('news-edit-modal-close');
  var cancelBtn    = document.getElementById('news-edit-cancel-btn');
  var idInput      = document.getElementById('news-edit-id');
  var titleInput   = document.getElementById('news-edit-title');
  var contentInput = document.getElementById('news-edit-content');
  var projectSel   = document.getElementById('news-edit-project');
  var previewBtn   = document.getElementById('news-edit-preview-btn');
  var previewModal = document.getElementById('news-edit-preview-modal');
  var previewBody  = document.getElementById('news-edit-preview-content');
  var previewClose = document.getElementById('news-edit-preview-close');

  // Open modal and populate fields
  document.querySelectorAll('.news-edit-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      idInput.value      = btn.dataset.id      || '';
      titleInput.value   = btn.dataset.title   || '';
      contentInput.value = btn.dataset.content || '';
      var pid = btn.dataset.projectId || '';
      for (var i = 0; i < projectSel.options.length; i++) {
        projectSel.options[i].selected = (projectSel.options[i].value === pid);
      }
      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
      setTimeout(function () { contentInput.focus(); }, 50);
    });
  });

  function closeModal() {
    modal.style.display = 'none';
    document.body.style.overflow = '';
  }

  closeBtn.addEventListener('click', closeModal);
  cancelBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) {
    if (e.target === modal) closeModal();
  });
  document.addEventListener('keydown', function (e) {
    if (modal.style.display === 'flex' && (e.key === 'Escape' || e.key === 'Esc')) {
      if (previewModal.style.display === 'flex') {
        previewModal.style.display = 'none';
      } else {
        closeModal();
      }
    }
  });

  // Preview
  previewBtn.addEventListener('click', function () {
    previewBody.innerHTML = window.marked
      ? (window.marked.parse ? window.marked.parse(contentInput.value) : window.marked(contentInput.value))
      : contentInput.value;
    previewModal.style.display = 'flex';
  });
  previewClose.addEventListener('click', function () {
    previewModal.style.display = 'none';
  });
  previewModal.addEventListener('click', function (e) {
    if (e.target === previewModal) previewModal.style.display = 'none';
  });
});
</script>
<?php endif; ?>

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

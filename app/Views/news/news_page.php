<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= file_get_contents(APPPATH . 'Data/LdJson/about.json') ?>


</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<?php
$createErrors = session()->getFlashdata('create_errors') ?? [];
$createTitle = session()->getFlashdata('create_title') ?? '';
$createSlug = session()->getFlashdata('create_slug') ?? '';
$createContent = session()->getFlashdata('create_content') ?? '';
$createProjectId = session()->getFlashdata('create_project_id') ?? '';
$createCategory = session()->getFlashdata('create_category') ?? 'general';
$createEventLocation = session()->getFlashdata('create_event_location') ?? '';
$createEventStartDate = session()->getFlashdata('create_event_start_date') ?? '';
$createEventEndDate = session()->getFlashdata('create_event_end_date') ?? '';
$createExternalLink = session()->getFlashdata('create_external_link') ?? '';
$openCreateModal = !empty($createErrors) || $createTitle !== '' || $createSlug !== '' || $createContent !== '';
$newsCategories = [
  'general' => 'General',
  'exhibition' => 'Exhibition',
  'talk' => 'Talk',
  'workshop' => 'Workshop',
];
?>
<div class="contained news-admin">
  <h2>News Administration</h2>
  <p>Create, edit, and manage news posts shown on the news page.</p>
    <div class="news-admin-top-actions">
      <button type="button" id="news-add-open-btn" class="news-add-btn">Add news</button>
  </div>
  <hr class='light admin-divider'/>
</div>



<div id="news-add-modal" class="news-edit-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="news-add-modal-heading">
  <div class="news-edit-modal-box">
    <button type="button" id="news-add-modal-close" class="news-edit-modal-close" aria-label="Close">&times;</button>
    <h3 id="news-add-modal-heading">Add News Item</h3>

    <?php if (!empty($createErrors)): ?>
      <div class="alert error">
        <ul style="margin:0;padding-left:18px;">
          <?php foreach ($createErrors as $err): ?>
            <li><?= esc($err) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <div id="news-add-preview-modal" class="preview-modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.5);z-index:3000;align-items:center;justify-content:center;">
      <div class="preview-modal-content news-edit-preview-modal-content" style="position:relative;">
        <button type="button" id="news-add-preview-close" style="position:absolute;top:8px;right:12px;font-size:22px;background:none;border:none;cursor:pointer;">&times;</button>
        <div class="contained news-edit-preview-contained">
          <article class="news-item news-edit-preview-item">
            <div id="news-add-preview-content" class="body"></div>
          </article>
        </div>
      </div>
    </div>

    <form id="news-add-form" action="<?= base_url('news/store') ?>" method="post" enctype="multipart/form-data">
      <?= csrf_field() ?>
      <label class="md-title-field">
        Title
        <input type="text" name="title" id="news-add-title" value="<?= esc($createTitle) ?>" required>
      </label>
      <input type="hidden" name="slug" id="news-add-slug" value="<?= esc($createSlug) ?>">
      <label class="md-extra-field">
        Project
        <select name="project_id" id="news-add-project">
          <option value="">- none -</option>
          <?php foreach ($projects ?? [] as $proj): ?>
            <option value="<?= esc($proj['id']) ?>"<?= (string)$createProjectId === (string)$proj['id'] ? ' selected' : '' ?>>
              <?= esc($proj['title']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="md-extra-field">
        Main image
        <input type="file" name="main_image_file" id="news-add-main-image-file" accept=".jpg,.jpeg,.png,.webp">
      </label>
      <label class="md-extra-field">
        Category
        <select name="category" id="news-add-category">
          <?php foreach ($newsCategories as $value => $label): ?>
            <option value="<?= esc($value) ?>"<?= $createCategory === $value ? ' selected' : '' ?>><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="md-extra-field">
        Event location
        <input type="text" name="event_location" id="news-add-event-location" value="<?= esc($createEventLocation) ?>">
      </label>
      <div class="news-modal-date-row">
        <label class="md-extra-field news-modal-date-field">
          Event start date
          <input type="date" name="event_start_date" id="news-add-event-start-date" value="<?= esc($createEventStartDate) ?>">
        </label>
        <label class="md-extra-field news-modal-date-field">
          Event end date
          <input type="date" name="event_end_date" id="news-add-event-end-date" value="<?= esc($createEventEndDate) ?>">
        </label>
      </div>
      <label class="md-extra-field">
        External link
        <input type="url" name="external_link" id="news-add-external-link" value="<?= esc($createExternalLink) ?>" placeholder="https://...">
      </label>
      <div class="md-toolbar">
        <button type="button" onclick="mdWrap('news-add-content', '**', '**')" title="Bold">B</button>
        <button type="button" onclick="mdWrap('news-add-content', '*', '*')" title="Italic"><em>I</em></button>
        <button type="button" onclick="mdInsert('news-add-content', '## ')" title="Heading">H</button>
        <button type="button" onclick="mdWrap('news-add-content', '[', '](url)')" title="Link">🔗</button>
        <button type="button" onclick="mdInsert('news-add-content', '- ')" title="Bullet List">• List</button>
        <button type="button" onclick="mdInsert('news-add-content', '1. ')" title="Numbered List">1. List</button>
        <button type="button" onclick="mdInsert('news-add-content', '  \n')" title="Line Break">↵</button>
        <button type="button" onclick="mdWrap('news-add-content', '`', '`')" title="Code">&lt;/&gt;</button>
      </div>
      <textarea id="news-add-content" name="content" class="news-edit-modal-textarea"><?= esc($createContent) ?></textarea>
      <div style="padding: 0 4px">
        <div class="form-actions">
          <button type="button" id="news-add-preview-btn">Preview</button>
          <button type="submit">Create</button>
          <button type="button" id="news-add-cancel-btn">Cancel</button>
        </div>
        <div>💡 Tip: "↵" adds a "soft line break"</div>
      </div>
    </form>
  </div>
</div>

<?= $this->endSection() ?>


<?= $this->section('content') ?>
<h1 class="visually-hidden">News</h1>

<?php $news_items = $news_items ?? []; ?>
<div class='contained'>
  <?php foreach ($news_items as $idx => $item): ?>
    <article id="news-<?= $item['slug'] ?>" class="news-item" data-project-id="<?= esc($item['project_id'] ?? '') ?>" data-slug="<?= esc($item['slug'] ?? '') ?>" data-content="<?= htmlspecialchars($item['content'] ?? '', ENT_QUOTES) ?>">
      <h2><?= esc($item['title']) ?></h2>
      <div class="body">
        <?= $item['content_parsed'] ?: nl2br(esc($item['content'] ?? '')) ?>
      </div>
      <?php if (!empty($item['main_image'])): ?>
        <?php
        $mainImageDisplay = $item['main_image_medium'] ?? $item['main_image'];
        $mainImageLarge = $item['main_image_large'] ?? $mainImageDisplay;
        $mainImageWidth = isset($item['main_image_width']) ? (int) $item['main_image_width'] : 560;
        $mainImageHeight = isset($item['main_image_height']) ? (int) $item['main_image_height'] : 315;
        ?>
        <div class="news-main-image">
          <button type="button" class="news-main-image-trigger"
                  data-full-image="<?= base_url($item['main_image']) ?>"
                  data-alt="<?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES) ?>"
                  aria-label="Open image in fullscreen">
            <img
              src="<?= base_url($mainImageDisplay) ?>"
              srcset="<?= base_url($mainImageDisplay) ?> 1x, <?= base_url($mainImageLarge) ?> 2x"
              sizes="(max-width: 768px) 100vw, 560px"
              width="<?= $mainImageWidth ?>"
              height="<?= $mainImageHeight ?>"
              alt="<?= esc($item['title']) ?>"
              loading="<?= $idx === 0 ? 'eager' : 'lazy' ?>"
              fetchpriority="<?= $idx === 0 ? 'high' : 'auto' ?>"
              decoding="async">
          </button>
        </div>
      <?php endif; ?>
      <?php if (session()->get('isLoggedIn')): ?>
        <div class="news-item-admin-actions">
          <button type="button" class="news-edit-btn"
            data-id="<?= esc($item['id']) ?>"
            data-title="<?= htmlspecialchars($item['title'] ?? '', ENT_QUOTES) ?>"
            data-content="<?= htmlspecialchars($item['content'] ?? '', ENT_QUOTES) ?>"
            data-project-id="<?= esc($item['project_id'] ?? '') ?>"
            data-category="<?= esc($item['category'] ?? 'general') ?>"
            data-main-image="<?= htmlspecialchars($item['main_image'] ?? '', ENT_QUOTES) ?>"
            data-event-location="<?= htmlspecialchars($item['event_location'] ?? '', ENT_QUOTES) ?>"
            data-event-start-date="<?= esc($item['event_start_date'] ?? '') ?>"
            data-event-end-date="<?= esc($item['event_end_date'] ?? '') ?>"
            data-external-link="<?= htmlspecialchars($item['external_link'] ?? '', ENT_QUOTES) ?>">Edit</button>
          <form method="post" action="<?= base_url('news/delete') ?>" class="news-delete-form"
                onsubmit="return confirm('Are you sure?');">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc($item['id']) ?>">
            <button type="submit" class="news-delete-link">delete</button>
          </form>
        </div>
      <?php endif; ?>
      <hr>
    </article>
  <?php endforeach; ?>
</div>

<div id="news-image-fullscreen-modal" class="news-image-fullscreen-modal" style="display:none;" aria-hidden="true">
  <button type="button" id="news-image-fullscreen-close" class="news-image-fullscreen-close" aria-label="Close">&times;</button>
  <img id="news-image-fullscreen-img" src="" alt="">
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

    <form id="news-edit-form" action="<?= base_url('news/update') ?>" method="post" enctype="multipart/form-data">
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
      <label class="md-extra-field">
        Main image
        <input type="file" name="main_image_file" id="news-edit-main-image-file" accept=".jpg,.jpeg,.png,.webp">
        <small id="news-edit-main-image-current" class="news-field-hint"></small>
        <label class="news-edit-remove-image-row">
          <input type="checkbox" name="remove_main_image" id="news-edit-remove-main-image" value="1">
          Remove current image
        </label>
      </label>
      <label class="md-extra-field">
        Category
        <select name="category" id="news-edit-category">
          <?php foreach ($newsCategories as $value => $label): ?>
            <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
          <?php endforeach; ?>
        </select>
      </label>
      <label class="md-extra-field">
        Event location
        <input type="text" name="event_location" id="news-edit-event-location">
      </label>
      <div class="news-modal-date-row">
        <label class="md-extra-field news-modal-date-field">
          Event start date
          <input type="date" name="event_start_date" id="news-edit-event-start-date">
        </label>
        <label class="md-extra-field news-modal-date-field">
          Event end date
          <input type="date" name="event_end_date" id="news-edit-event-end-date">
        </label>
      </div>
      <label class="md-extra-field">
        External link
        <input type="url" name="external_link" id="news-edit-external-link" placeholder="https://...">
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
  var editModal        = document.getElementById('news-edit-modal');
  var editCloseBtn     = document.getElementById('news-edit-modal-close');
  var editCancelBtn    = document.getElementById('news-edit-cancel-btn');
  var editIdInput      = document.getElementById('news-edit-id');
  var editTitleInput   = document.getElementById('news-edit-title');
  var editContentInput = document.getElementById('news-edit-content');
  var editProjectSel   = document.getElementById('news-edit-project');
  var editCategorySel  = document.getElementById('news-edit-category');
  var editMainImageCurrent = document.getElementById('news-edit-main-image-current');
  var editRemoveMainImage = document.getElementById('news-edit-remove-main-image');
  var editEventLocationInput = document.getElementById('news-edit-event-location');
  var editEventStartDateInput = document.getElementById('news-edit-event-start-date');
  var editEventEndDateInput = document.getElementById('news-edit-event-end-date');
  var editExternalLinkInput = document.getElementById('news-edit-external-link');
  var editPreviewBtn   = document.getElementById('news-edit-preview-btn');
  var editPreviewModal = document.getElementById('news-edit-preview-modal');
  var editPreviewBody  = document.getElementById('news-edit-preview-content');
  var editPreviewClose = document.getElementById('news-edit-preview-close');

  var addOpenBtn       = document.getElementById('news-add-open-btn');
  var addModal         = document.getElementById('news-add-modal');
  var addCloseBtn      = document.getElementById('news-add-modal-close');
  var addCancelBtn     = document.getElementById('news-add-cancel-btn');
  var addTitleInput    = document.getElementById('news-add-title');
  var addSlugInput     = document.getElementById('news-add-slug');
  var addCategorySel   = document.getElementById('news-add-category');
  var addEventLocationInput = document.getElementById('news-add-event-location');
  var addEventStartDateInput = document.getElementById('news-add-event-start-date');
  var addEventEndDateInput = document.getElementById('news-add-event-end-date');
  var addExternalLinkInput = document.getElementById('news-add-external-link');
  var addContentInput  = document.getElementById('news-add-content');
  var addPreviewBtn    = document.getElementById('news-add-preview-btn');
  var addPreviewModal  = document.getElementById('news-add-preview-modal');
  var addPreviewBody   = document.getElementById('news-add-preview-content');
  var addPreviewClose  = document.getElementById('news-add-preview-close');

  var shouldOpenCreateModal = <?= $openCreateModal ? 'true' : 'false' ?>;

  function refreshBodyScrollLock() {
    var anyOpen = editModal.style.display === 'flex'
      || addModal.style.display === 'flex'
      || editPreviewModal.style.display === 'flex'
      || addPreviewModal.style.display === 'flex';

    document.body.style.overflow = anyOpen ? 'hidden' : '';
  }

  function slugifyNewsTitle(input) {
    return (input || '')
      .replace(/[ÅÄåä]/g, 'a')
      .replace(/[Öö]/g, 'o')
      .toLowerCase()
      .replace(/\s+/g, '-')
      .replace(/[^a-z0-9-]/g, '')
      .replace(/-+/g, '-')
      .replace(/^-+|-+$/g, '');
  }

  function openEditModal(btn) {
    function escapeHtml(value) {
      return String(value || '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    editIdInput.value = btn.dataset.id || '';
    editTitleInput.value = btn.dataset.title || '';
    editContentInput.value = btn.dataset.content || '';

    var pid = btn.dataset.projectId || '';
    for (var i = 0; i < editProjectSel.options.length; i++) {
      editProjectSel.options[i].selected = (editProjectSel.options[i].value === pid);
    }
    editCategorySel.value = btn.dataset.category || 'general';
    var currentMainImage = btn.dataset.mainImage || '';
    if (currentMainImage !== '') {
      var href = '<?= base_url() ?>' + currentMainImage.replace(/^\/+/, '');
      editMainImageCurrent.innerHTML = 'Current image: <br /><code>' + escapeHtml(currentMainImage) + '</code> - <a href="' + escapeHtml(href) + '" target="_blank" rel="noopener">open</a>';
      if (editRemoveMainImage) {
        editRemoveMainImage.disabled = false;
        editRemoveMainImage.checked = false;
      }
    } else {
      editMainImageCurrent.textContent = 'No main image uploaded yet.';
      if (editRemoveMainImage) {
        editRemoveMainImage.checked = false;
        editRemoveMainImage.disabled = true;
      }
    }
    editEventLocationInput.value = btn.dataset.eventLocation || '';
    editEventStartDateInput.value = btn.dataset.eventStartDate || '';
    editEventEndDateInput.value = btn.dataset.eventEndDate || '';
    editExternalLinkInput.value = btn.dataset.externalLink || '';

    editModal.style.display = 'flex';
    refreshBodyScrollLock();
    setTimeout(function () { editContentInput.focus(); }, 50);
  }

  function closeEditModal() {
    editModal.style.display = 'none';
    refreshBodyScrollLock();
  }

  function openAddModal() {
    addModal.style.display = 'flex';
    refreshBodyScrollLock();
    addSlugInput.value = slugifyNewsTitle(addTitleInput.value);
    setTimeout(function () { addTitleInput.focus(); }, 50);
  }

  function closeAddModal() {
    addModal.style.display = 'none';
    refreshBodyScrollLock();
  }

  document.querySelectorAll('.news-edit-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      openEditModal(btn);
    });
  });

  if (addOpenBtn) {
    addOpenBtn.addEventListener('click', function () {
      openAddModal();
    });
  }

  editCloseBtn.addEventListener('click', closeEditModal);
  editCancelBtn.addEventListener('click', closeEditModal);
  editModal.addEventListener('click', function (e) {
    if (e.target === editModal) closeEditModal();
  });

  addCloseBtn.addEventListener('click', closeAddModal);
  addCancelBtn.addEventListener('click', closeAddModal);
  addModal.addEventListener('click', function (e) {
    if (e.target === addModal) closeAddModal();
  });

  addTitleInput.addEventListener('input', function () {
    addSlugInput.value = slugifyNewsTitle(addTitleInput.value);
  });

  editPreviewBtn.addEventListener('click', function () {
    editPreviewBody.innerHTML = window.marked
      ? (window.marked.parse ? window.marked.parse(editContentInput.value) : window.marked(editContentInput.value))
      : editContentInput.value;
    editPreviewModal.style.display = 'flex';
    refreshBodyScrollLock();
  });

  editPreviewClose.addEventListener('click', function () {
    editPreviewModal.style.display = 'none';
    refreshBodyScrollLock();
  });

  editPreviewModal.addEventListener('click', function (e) {
    if (e.target === editPreviewModal) {
      editPreviewModal.style.display = 'none';
      refreshBodyScrollLock();
    }
  });

  addPreviewBtn.addEventListener('click', function () {
    addPreviewBody.innerHTML = window.marked
      ? (window.marked.parse ? window.marked.parse(addContentInput.value) : window.marked(addContentInput.value))
      : addContentInput.value;
    addPreviewModal.style.display = 'flex';
    refreshBodyScrollLock();
  });

  addPreviewClose.addEventListener('click', function () {
    addPreviewModal.style.display = 'none';
    refreshBodyScrollLock();
  });

  addPreviewModal.addEventListener('click', function (e) {
    if (e.target === addPreviewModal) {
      addPreviewModal.style.display = 'none';
      refreshBodyScrollLock();
    }
  });

  document.addEventListener('keydown', function (e) {
    if (e.key !== 'Escape' && e.key !== 'Esc') {
      return;
    }

    if (addPreviewModal.style.display === 'flex') {
      addPreviewModal.style.display = 'none';
      refreshBodyScrollLock();
      return;
    }

    if (editPreviewModal.style.display === 'flex') {
      editPreviewModal.style.display = 'none';
      refreshBodyScrollLock();
      return;
    }

    if (addModal.style.display === 'flex') {
      closeAddModal();
      return;
    }

    if (editModal.style.display === 'flex') {
      closeEditModal();
    }
  });

  if (shouldOpenCreateModal) {
    openAddModal();
  }
});
</script>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var modal = document.getElementById('news-image-fullscreen-modal');
  var modalImg = document.getElementById('news-image-fullscreen-img');
  var closeBtn = document.getElementById('news-image-fullscreen-close');
  var triggers = document.querySelectorAll('.news-main-image-trigger');

  if (!modal || !modalImg || !closeBtn || triggers.length === 0) {
    return;
  }

  function openModal(src, alt) {
    modalImg.src = src;
    modalImg.alt = alt || '';
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal() {
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
    modalImg.src = '';
    document.body.style.overflow = '';
  }

  triggers.forEach(function (trigger) {
    trigger.addEventListener('click', function () {
      openModal(trigger.dataset.fullImage || '', trigger.dataset.alt || '');
    });
  });

  closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', function (e) {
    if (e.target === modal) {
      closeModal();
    }
  });

  document.addEventListener('keydown', function (e) {
    if ((e.key === 'Escape' || e.key === 'Esc') && modal.style.display === 'flex') {
      closeModal();
    }
  });
});
</script>

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
          "@id": "<?= current_url() ?>#news-<?= $item['slug'] ?>",
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

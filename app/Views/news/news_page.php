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
$actionError = session()->getFlashdata('error') ?? '';
$actionSuccess = session()->getFlashdata('success') ?? '';
$editOldId = (string)(old('id') ?? '');
$showEditFormError = $editOldId !== '' && $actionError !== '';
$globalActionError = $showEditFormError ? '' : $actionError;
$openEditModal = $showEditFormError;
$editOldTitle = (string)(old('title') ?? '');
$editOldContent = (string)(old('content') ?? '');
$editOldProjectId = (string)(old('project_id') ?? '');
$editOldCategory = (string)(old('category') ?? 'general');
$editOldEventLocation = (string)(old('event_location') ?? '');
$editOldEventStartDate = (string)(old('event_start_date') ?? '');
$editOldEventEndDate = (string)(old('event_end_date') ?? '');
$editOldExternalLink = (string)(old('external_link') ?? '');
$editOldRemoveMainImage = in_array(old('remove_main_image'), ['1', 1, true, 'true', 'on'], true);
$openCreateModal = !empty($createErrors) || $createTitle !== '' || $createSlug !== '' || $createContent !== '';
$newsCategories = [
  'general' => 'General',
  'exhibition' => 'Exhibition',
  'talk' => 'Talk',
  'workshop' => 'Workshop',
];
$projectTitleById = [];
foreach ($projects ?? [] as $project) {
  if (isset($project['id'])) {
    $projectTitleById[(string)$project['id']] = (string)($project['title'] ?? 'Untitled project');
  }
}
?>
<div class="contained news-admin">
  <h2>News Administration</h2>
  <p>Create, edit, and manage news posts shown on the news page.</p>
  <?php if ($globalActionError !== ''): ?>
    <div class="alert error"><?= esc($globalActionError) ?></div>
  <?php endif; ?>
  <?php if ($actionSuccess !== ''): ?>
    <div class="alert success"><?= esc($actionSuccess) ?></div>
  <?php endif; ?>
  <div class="news-admin-top-actions">
    <button type="button" id="news-add-open-btn" class="news-add-btn">Add news</button>
  </div>
  <hr class='light admin-divider'/>
</div>


<div id="news-add-modal" class="news-edit-modal-overlay" style="display:none;" role="dialog" aria-modal="true"
     aria-labelledby="news-add-modal-heading">
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

    <div id="news-add-preview-modal" class="preview-modal"
         style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.5);z-index:3000;align-items:center;justify-content:center;">
      <div class="preview-modal-content news-edit-preview-modal-content" style="position:relative;">
        <button type="button" id="news-add-preview-close"
                style="position:absolute;top:8px;right:12px;font-size:22px;background:none;border:none;cursor:pointer;">
          &times;
        </button>
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
            <option
              value="<?= esc($proj['id']) ?>"<?= (string)$createProjectId === (string)$proj['id'] ? ' selected' : '' ?>>
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
            <option
              value="<?= esc($value) ?>"<?= $createCategory === $value ? ' selected' : '' ?>><?= esc($label) ?></option>
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
          <input type="date" name="event_start_date" id="news-add-event-start-date"
                 value="<?= esc($createEventStartDate) ?>">
        </label>
        <label class="md-extra-field news-modal-date-field">
          Event end date
          <input type="date" name="event_end_date" id="news-add-event-end-date" value="<?= esc($createEventEndDate) ?>">
        </label>
      </div>
      <label class="md-extra-field">
        External link
        <input type="url" name="external_link" id="news-add-external-link" value="<?= esc($createExternalLink) ?>"
               placeholder="https://...">
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
      <textarea id="news-add-content" name="content"
                class="news-edit-modal-textarea"><?= esc($createContent) ?></textarea>
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
    <?= view('partials/news_item', [
      'item' => $item,
      'idx' => $idx,
      'total' => count($news_items),
      'showAdmin' => true,
      'includeDataAttrs' => true,
      'articleIdPrefix' => 'news-',
      'projectTitleById' => $projectTitleById,
    ]) ?>
  <?php endforeach; ?>
</div>

<div id="news-image-fullscreen-modal" class="news-image-fullscreen-modal" style="display:none;" aria-hidden="true">
  <button type="button" id="news-image-fullscreen-close" class="news-image-fullscreen-close" aria-label="Close">
    &times;
  </button>
  <img id="news-image-fullscreen-img" src="" alt="">
</div>

<?php if (session()->get('isLoggedIn')): ?>
  <!-- News Edit Modal -->
  <div id="news-edit-modal" class="news-edit-modal-overlay" style="display:none;" role="dialog" aria-modal="true"
       aria-labelledby="news-edit-modal-heading">
    <div class="news-edit-modal-box">
      <button type="button" id="news-edit-modal-close" class="news-edit-modal-close" aria-label="Close">&times;</button>
      <h3 id="news-edit-modal-heading">Edit News Item</h3>

      <?php if ($showEditFormError): ?>
        <div class="alert error"><?= esc($actionError) ?></div>
      <?php endif; ?>

      <!-- Preview sub-modal -->
      <div id="news-edit-preview-modal" class="preview-modal"
           style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.5);z-index:3000;align-items:center;justify-content:center;">
        <div class="preview-modal-content news-edit-preview-modal-content" style="position:relative;">
          <button type="button" id="news-edit-preview-close"
                  style="position:absolute;top:8px;right:12px;font-size:22px;background:none;border:none;cursor:pointer;">
            &times;
          </button>
          <div class="contained news-edit-preview-contained">
            <article class="news-item news-edit-preview-item">
              <div id="news-edit-preview-content" class="body"></div>
            </article>
          </div>
        </div>
      </div>

      <form id="news-edit-form" action="<?= base_url('news/update') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <input type="hidden" name="id" id="news-edit-id">
        <label class="md-title-field">
          Title
          <input type="text" name="title" id="news-edit-title" value="<?= esc($editOldTitle) ?>">
        </label>
        <label class="md-extra-field">
          Project
          <select name="project_id" id="news-edit-project">
            <option value="">— none —</option>
            <?php foreach ($projects ?? [] as $proj): ?>
              <option
                value="<?= esc($proj['id']) ?>"<?= $editOldProjectId !== '' && $editOldProjectId === (string)$proj['id'] ? ' selected' : '' ?>><?= esc($proj['title']) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="md-extra-field">
          Main image
          <input type="file" name="main_image_file" id="news-edit-main-image-file" accept=".jpg,.jpeg,.png,.webp">
          <small id="news-edit-main-image-current" class="news-field-hint"></small>
          <label class="news-edit-remove-image-row">
            <input type="checkbox" name="remove_main_image" id="news-edit-remove-main-image"
                   value="1"<?= $editOldRemoveMainImage ? ' checked' : '' ?>>
            Remove current image
          </label>
        </label>
        <label class="md-extra-field">
          Category
          <select name="category" id="news-edit-category">
            <?php foreach ($newsCategories as $value => $label): ?>
              <option
                value="<?= esc($value) ?>"<?= $editOldCategory === $value ? ' selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="md-extra-field">
          Event location
          <input type="text" name="event_location" id="news-edit-event-location"
                 value="<?= esc($editOldEventLocation) ?>">
        </label>
        <div class="news-modal-date-row">
          <label class="md-extra-field news-modal-date-field">
            Event start date
            <input type="date" name="event_start_date" id="news-edit-event-start-date"
                   value="<?= esc($editOldEventStartDate) ?>">
          </label>
          <label class="md-extra-field news-modal-date-field">
            Event end date
            <input type="date" name="event_end_date" id="news-edit-event-end-date"
                   value="<?= esc($editOldEventEndDate) ?>">
          </label>
        </div>
        <label class="md-extra-field">
          External link
          <input type="text" inputmode="url" name="external_link" id="news-edit-external-link"
                 value="<?= esc($editOldExternalLink) ?>" placeholder="https://...">
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
        <textarea id="news-edit-content" name="content"
                  class="news-edit-modal-textarea"><?= esc($editOldContent) ?></textarea>
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
      var editModal = document.getElementById('news-edit-modal');
      var editCloseBtn = document.getElementById('news-edit-modal-close');
      var editCancelBtn = document.getElementById('news-edit-cancel-btn');
      var editIdInput = document.getElementById('news-edit-id');
      var editTitleInput = document.getElementById('news-edit-title');
      var editContentInput = document.getElementById('news-edit-content');
      var editProjectSel = document.getElementById('news-edit-project');
      var editCategorySel = document.getElementById('news-edit-category');
      var editMainImageCurrent = document.getElementById('news-edit-main-image-current');
      var editRemoveMainImage = document.getElementById('news-edit-remove-main-image');
      var editEventLocationInput = document.getElementById('news-edit-event-location');
      var editEventStartDateInput = document.getElementById('news-edit-event-start-date');
      var editEventEndDateInput = document.getElementById('news-edit-event-end-date');
      var editExternalLinkInput = document.getElementById('news-edit-external-link');
      var editPreviewBtn = document.getElementById('news-edit-preview-btn');
      var editPreviewModal = document.getElementById('news-edit-preview-modal');
      var editPreviewBody = document.getElementById('news-edit-preview-content');
      var editPreviewClose = document.getElementById('news-edit-preview-close');

      var addOpenBtn = document.getElementById('news-add-open-btn');
      var addModal = document.getElementById('news-add-modal');
      var addCloseBtn = document.getElementById('news-add-modal-close');
      var addCancelBtn = document.getElementById('news-add-cancel-btn');
      var addTitleInput = document.getElementById('news-add-title');
      var addSlugInput = document.getElementById('news-add-slug');
      var addCategorySel = document.getElementById('news-add-category');
      var addEventLocationInput = document.getElementById('news-add-event-location');
      var addEventStartDateInput = document.getElementById('news-add-event-start-date');
      var addEventEndDateInput = document.getElementById('news-add-event-end-date');
      var addExternalLinkInput = document.getElementById('news-add-external-link');
      var addContentInput = document.getElementById('news-add-content');
      var addPreviewBtn = document.getElementById('news-add-preview-btn');
      var addPreviewModal = document.getElementById('news-add-preview-modal');
      var addPreviewBody = document.getElementById('news-add-preview-content');
      var addPreviewClose = document.getElementById('news-add-preview-close');
      var editForm = document.getElementById('news-edit-form');
      var addForm = document.getElementById('news-add-form');

      var shouldOpenCreateModal = <?= $openCreateModal ? 'true' : 'false' ?>;
      var shouldOpenEditModal = <?= $openEditModal ? 'true' : 'false' ?>;
      var editOldValues = <?= json_encode([
        'id' => $editOldId,
        'title' => $editOldTitle,
        'content' => $editOldContent,
        'project_id' => $editOldProjectId,
        'category' => $editOldCategory,
        'event_location' => $editOldEventLocation,
        'event_start_date' => $editOldEventStartDate,
        'event_end_date' => $editOldEventEndDate,
        'external_link' => $editOldExternalLink,
        'remove_main_image' => $editOldRemoveMainImage,
      ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      var scrollRestoreKey = 'news-admin-scroll-y';
      var lockedScrollY = 0;
      var isScrollLocked = false;

      function getCurrentScrollY() {
        return isScrollLocked ? lockedScrollY : (window.pageYOffset || window.scrollY || 0);
      }

      function persistScrollForNextLoad() {
        try {
          sessionStorage.setItem(scrollRestoreKey, String(getCurrentScrollY()));
        } catch (e) {
          // Ignore storage failures (private mode/storage limits).
        }
      }

      function restoreScrollFromPreviousLoad() {
        var raw;
        var y;

        try {
          raw = sessionStorage.getItem(scrollRestoreKey);
          if (raw === null) {
            return;
          }
          sessionStorage.removeItem(scrollRestoreKey);
        } catch (e) {
          return;
        }

        y = parseInt(raw, 10);
        if (!isNaN(y) && y >= 0) {
          window.scrollTo(0, y);
        }
      }

      function focusWithoutScroll(el) {
        if (!el || typeof el.focus !== 'function') {
          return;
        }

        try {
          el.focus({ preventScroll: true });
        } catch (e) {
          el.focus();
        }
      }

      function refreshBodyScrollLock() {
        var anyOpen = editModal.style.display === 'flex'
          || addModal.style.display === 'flex'
          || editPreviewModal.style.display === 'flex'
          || addPreviewModal.style.display === 'flex';

        if (anyOpen && !isScrollLocked) {
          lockedScrollY = window.pageYOffset || window.scrollY || 0;
          isScrollLocked = true;
          document.body.style.position = 'fixed';
          document.body.style.top = '-' + lockedScrollY + 'px';
          document.body.style.left = '0';
          document.body.style.right = '0';
          document.body.style.width = '100%';
          document.body.style.overflow = 'hidden';
          return;
        }

        if (!anyOpen && isScrollLocked) {
          document.body.style.position = '';
          document.body.style.top = '';
          document.body.style.left = '';
          document.body.style.right = '';
          document.body.style.width = '';
          document.body.style.overflow = '';
          isScrollLocked = false;
          window.scrollTo(0, lockedScrollY);
          return;
        }

        if (!anyOpen) {
          document.body.style.overflow = '';
        }
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
        editExternalLinkInput.value = (btn.dataset.externalLink || '').trim();

        editModal.style.display = 'flex';
        refreshBodyScrollLock();
        setTimeout(function () {
          focusWithoutScroll(editContentInput);
        }, 50);
      }

      function closeEditModal() {
        editModal.style.display = 'none';
        refreshBodyScrollLock();
      }

      function openAddModal() {
        addModal.style.display = 'flex';
        refreshBodyScrollLock();
        addSlugInput.value = slugifyNewsTitle(addTitleInput.value);
        setTimeout(function () {
          focusWithoutScroll(addTitleInput);
        }, 50);
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

      addCloseBtn.addEventListener('click', closeAddModal);
      addCancelBtn.addEventListener('click', closeAddModal);

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

      if (editForm) {
        editForm.addEventListener('submit', function () {
          if (editExternalLinkInput) {
            editExternalLinkInput.value = (editExternalLinkInput.value || '').trim();
          }
          persistScrollForNextLoad();
        });
      }

      if (addForm) {
        addForm.addEventListener('submit', persistScrollForNextLoad);
      }

      document.querySelectorAll('.news-delete-form').forEach(function (form) {
        form.addEventListener('submit', persistScrollForNextLoad);
      });

      restoreScrollFromPreviousLoad();

      if (shouldOpenEditModal) {
        var matchingEditButton = null;
        var oldEditId = String(editOldValues.id || '');
        document.querySelectorAll('.news-edit-btn').forEach(function (btn) {
          if ((btn.dataset.id || '') === oldEditId) {
            matchingEditButton = btn;
          }
        });

        if (matchingEditButton) {
          openEditModal(matchingEditButton);
        } else {
          editModal.style.display = 'flex';
          refreshBodyScrollLock();
        }

        editIdInput.value = oldEditId;
        editTitleInput.value = String(editOldValues.title || '');
        editContentInput.value = String(editOldValues.content || '');
        editEventLocationInput.value = String(editOldValues.event_location || '');
        editEventStartDateInput.value = String(editOldValues.event_start_date || '');
        editEventEndDateInput.value = String(editOldValues.event_end_date || '');
        editExternalLinkInput.value = String(editOldValues.external_link || '');

        if (editProjectSel) {
          var oldProjectId = String(editOldValues.project_id || '');
          for (var j = 0; j < editProjectSel.options.length; j++) {
            editProjectSel.options[j].selected = (editProjectSel.options[j].value === oldProjectId);
          }
        }
        if (editCategorySel) {
          editCategorySel.value = String(editOldValues.category || 'general');
        }
        if (editRemoveMainImage) {
          editRemoveMainImage.checked = !!editOldValues.remove_main_image;
        }
        focusWithoutScroll(editContentInput);
      } else if (shouldOpenCreateModal) {
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

    modalImg.addEventListener('click', closeModal);

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

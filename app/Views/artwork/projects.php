<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?php
$baseUrl = rtrim((string) base_url('/'), '/');
$itemListElements = [];
$position = 1;

if (isset($projects) && is_array($projects)) {
  foreach ($projects as $project) {
    if (!is_array($project)) {
      continue;
    }

    $slug = trim((string) ($project['slug'] ?? ''));
    if ($slug === '') {
      continue;
    }

    $itemListElements[] = [
      '@type' => 'ListItem',
      'position' => $position++,
      'name' => (string) ($project['title'] ?? $slug),
      'item' => $baseUrl . '/' . rawurlencode($slug),
    ];
  }
}

$artworkLdJson = [
  '@context' => 'https://schema.org',
  '@graph' => [
    [
      '@type' => 'WebSite',
      '@id' => $baseUrl . '/#website',
      'url' => $baseUrl . '/',
      'name' => 'Anne Hamrin Simonsson',
      'publisher' => ['@id' => $baseUrl . '/#person'],
    ],
    [
      '@type' => 'Person',
      '@id' => $baseUrl . '/#person',
      'name' => 'Anne Hamrin Simonsson',
      'url' => $baseUrl . '/about',
      'sameAs' => [
        'https://www.wikidata.org/wiki/Q137808007',
        'https://www.instagram.com/ahamrinsimonsson/',
        'https://www.linkedin.com/in/anne-hamrin-simonsson-1948aba5/',
        'https://www.konstikalmarlan.se/verksamhet/anne-hamrin-simonsson/',
        'https://www.smalandstriennalen.se/medverkande/anne-hamrin-simonsson',
        'https://www.kalmarkonstmuseum.se/exhibition/med-orat-mot-marken-och-blicken-utat/'
      ],
    ],
    [
      '@type' => 'CollectionPage',
      '@id' => $baseUrl . '/artwork#webpage',
      'url' => $baseUrl . '/artwork',
      'name' => 'Artwork by Anne Hamrin Simonsson',
      'description' => 'Overview of artwork projects by Swedish conceptual artist Anne Hamrin Simonsson.',
      'isPartOf' => ['@id' => $baseUrl . '/#website'],
      'about' => ['@id' => $baseUrl . '/#person'],
      'mainEntity' => ['@id' => $baseUrl . '/artwork#itemlist'],
      'breadcrumb' => ['@id' => $baseUrl . '/artwork#breadcrumb'],
    ],
    [
      '@type' => 'ItemList',
      '@id' => $baseUrl . '/artwork#itemlist',
      'name' => 'Artwork Projects',
      'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
      'itemListElement' => $itemListElements,
    ],
    [
      '@type' => 'BreadcrumbList',
      '@id' => $baseUrl . '/artwork#breadcrumb',
      'itemListElement' => [
        [
          '@type' => 'ListItem',
          'position' => 1,
          'name' => 'Home',
          'item' => $baseUrl . '/'
        ],
        [
          '@type' => 'ListItem',
          'position' => 2,
          'name' => 'Artwork',
          'item' => $baseUrl . '/artwork'
        ]
      ],
    ],
    [
      '@type' => 'SiteNavigationElement',
      '@id' => $baseUrl . '/#navigation',
      'name' => 'Main Navigation',
      'hasPart' => [
        ['@type' => 'WebPage', 'name' => 'News', 'url' => $baseUrl . '/news'],
        ['@type' => 'WebPage', 'name' => 'Artwork', 'url' => $baseUrl . '/artwork'],
        ['@type' => 'WebPage', 'name' => 'About', 'url' => $baseUrl . '/about'],
        ['@type' => 'WebPage', 'name' => 'Contact', 'url' => $baseUrl . '/contact']
      ],
    ],
  ],
];

echo json_encode($artworkLdJson, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>

</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<?php if (session()->get('isLoggedIn')): ?>
<?php $allFormErrors = session('errors') ?? []; ?>
<?php $overviewProjectId = (string) (old('overview_project_id') ?? ''); ?>
<?php $isOverviewValidation = $overviewProjectId !== ''; ?>
<?php $projectFormErrors = $isOverviewValidation ? [] : $allFormErrors; ?>
<?php $overviewFormErrors = $isOverviewValidation ? $allFormErrors : []; ?>

<?php if (session()->has('success')): ?>
  <div class="contained"><div class="alert success"><?= session('success') ?></div></div>
<?php endif; ?>

<div class="manage-projects contained">
  <div class="projects-table-wrapper">
    <table class="projects-table">
      <thead>
        <tr><th class="project-publish-col">Order</th><th>Title</th><th class="project-publish-col">Published</th><th style="text-align:right;"></th></tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $index => $project): ?>
          <tr>
            <td class="order-controls project-publish-col">
              <?php if ($index > 0): ?>
                <a href="/artwork/move-up/<?= $project['id'] ?>" class="order-btn js-project-move" data-method="PATCH" data-direction="up" title="Move up">▲</a>
              <?php else: ?>
                <span class="order-btn disabled">▲</span>
              <?php endif; ?>
              <?php if ($index < count($projects) - 1): ?>
                <a href="/artwork/move-down/<?= $project['id'] ?>" class="order-btn js-project-move" data-method="PATCH" data-direction="down" title="Move down">▼</a>
              <?php else: ?>
                <span class="order-btn disabled">▼</span>
              <?php endif; ?>
            </td>
            <td><a href="/<?= $project['slug'] ?>"><?= esc($project['title']) ?></a></td>
            <td class="project-publish-col">
              <label class="project-publish-toggle-label">
                <input
                  type="checkbox"
                  class="js-project-publish-toggle project-publish-toggle"
                  data-id="<?= (int)$project['id'] ?>"
                  data-published="<?= !empty($project['is_published']) ? '1' : '0' ?>"
                  <?= !empty($project['is_published']) ? 'checked' : '' ?>>
              </label>
            </td>
            <td style="text-align: right">
              <a href="#" class="delete-link" data-id="<?= $project['id'] ?>">delete</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="project-create-actions">
    <button type="button" class="admin-action-btn" onclick="openProjectCreateModal()">Add new Project</button>
  </div>

  <div id="project-overview-modal" class="news-edit-modal-overlay" style="display:none;" role="dialog" aria-modal="true" aria-labelledby="project-overview-title">
    <div class="news-edit-modal-box project-overview-modal-box">
      <button type="button" id="project-overview-modal-close" class="news-edit-modal-close" aria-label="Close">&times;</button>
      <h3 id="project-overview-title">Edit Project Overview</h3>
      <div class="project-modal-error-list" id="project-overview-error-list">
        <?php if (!empty($overviewFormErrors)): ?>
          <?php foreach ($overviewFormErrors as $error): ?><p><?= esc($error) ?></p><?php endforeach; ?>
        <?php endif; ?>
      </div>
      <form method="post" id="project-overview-form" class="project-overview-form">
        <?= csrf_field() ?>
        <input type="hidden" name="overview_project_id" id="project-overview-project-id-field" value="<?= esc(old('overview_project_id') ?? '') ?>">

        <div class="project-overview-main-fields">
          <label class="md-title-field">
            Title
            <input type="text" name="title" id="project-overview-title-input" required value="<?= esc(old('title') ?? '') ?>">
          </label>
          <label class="md-title-field">
            Slug
            <input type="text" name="slug" id="project-overview-slug-input" required value="<?= esc(old('slug') ?? '') ?>">
          </label>
        </div>

        <div class="news-modal-date-row project-overview-year-row">
          <label class="md-extra-field news-modal-date-field">
            Start year
            <input type="number" name="start_year" id="project-overview-start-year" min="1900" max="2100" value="<?= esc(old('start_year') ?? '') ?>">
          </label>
          <label class="md-extra-field news-modal-date-field">
            End year
            <input type="number" name="end_year" id="project-overview-end-year" min="1900" max="2100" value="<?= esc(old('end_year') ?? '') ?>">
          </label>
        </div>

        <div class="project-overview-image-selects">
          <label class="md-extra-field">
            Image Left
            <select name="image_left" id="project-overview-select-image_left"></select>
          </label>
          <label class="md-extra-field">
            Image Mid
            <select name="image_mid" id="project-overview-select-image_mid"></select>
          </label>
          <label class="md-extra-field">
            Image Right
            <select name="image_right" id="project-overview-select-image_right"></select>
          </label>
        </div>

        <div class="project-image-preview-row">
          <div class="project-image-preview-box">
            <div class="project-image-preview-square"><img id="project-overview-preview-image_left" src="" alt="Preview left" style="display:none;"></div>
          </div>
          <div class="project-image-preview-box">
            <div class="project-image-preview-square"><img id="project-overview-preview-image_mid" src="" alt="Preview middle" style="display:none;"></div>
          </div>
          <div class="project-image-preview-box">
            <div class="project-image-preview-square"><img id="project-overview-preview-image_right" src="" alt="Preview right" style="display:none;"></div>
          </div>
        </div>

        <label class="md-extra-field project-overview-description-field">
          Description
          <div class="md-toolbar">
            <button type="button" onclick="mdWrap('project-overview-description', '**', '**')" title="Bold">B</button>
            <button type="button" onclick="mdWrap('project-overview-description', '*', '*')" title="Italic"><em>I</em></button>
            <button type="button" onclick="mdInsert('project-overview-description', '## ')" title="Heading">H</button>
            <button type="button" onclick="mdWrap('project-overview-description', '[', '](url)')" title="Link">🔗</button>
            <button type="button" onclick="mdInsert('project-overview-description', '- ')" title="Bullet List">• List</button>
            <button type="button" onclick="mdInsert('project-overview-description', '1. ')" title="Numbered List">1. List</button>
            <button type="button" onclick="mdInsert('project-overview-description', '  \n')" title="Line Break">↵</button>
            <button type="button" onclick="mdWrap('project-overview-description', '`', '`')" title="Code">&lt;/&gt;</button>
          </div>
          <textarea name="description" id="project-overview-description" rows="4" maxlength="300" class="news-edit-modal-textarea project-overview-description-input"><?= esc(old('description') ?? '') ?></textarea>
          <small class="news-field-hint">Tip: &quot;↵&quot; adds a soft line break.</small>
        </label>

        <div class="form-actions project-overview-form-actions">
          <button type="submit">Update</button>
          <button type="button" id="project-overview-cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <div class="project-create-modal" id="project-create-modal" style="display:none;"
       onclick="closeProjectCreateModalOnBackdrop(event)">
    <div class="project-create-modal-content" role="dialog" aria-modal="true" aria-labelledby="project-create-title">
      <button type="button" class="project-create-modal-close" onclick="closeProjectCreateModal()">&times;</button>
      <h3 id="project-create-title">Add New Project</h3>
      <form action="/artwork/store" method="post" class="new-project-form" id="project-create-form">
        <?= csrf_field() ?>
        <div class="project-modal-error-list">
          <?php if (!empty($projectFormErrors)): ?>
            <?php foreach ($projectFormErrors as $error): ?><p><?= esc($error) ?></p><?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div>
          <label for="project-create-title-input">Title</label>
          <input type="text" name="title" id="project-create-title-input" required value="<?= old('title') ?>">
          <?php if (!empty($projectFormErrors['title'])): ?>
            <small class="project-form-field-error"><?= esc($projectFormErrors['title']) ?></small>
          <?php endif; ?>
        </div>
        <input type="hidden" name="slug" id="project-create-slug-input" value="<?= old('slug') ?>">
        <!-- Slug field removed, now auto-generated from title -->
        <div class="form-actions">
          <button type="submit" class="admin-action-btn">Create</button>
        </div>
      </form>
    </div>
  </div>
  <hr class="light">
</div>

<script>
  let overviewModalScrollY = 0;
  let overviewModalScrollLocked = false;

  function openProjectCreateModal() {
    document.getElementById('project-create-modal').style.display = 'block';
  }
  function closeProjectCreateModal() {
    document.getElementById('project-create-modal').style.display = 'none';
  }
  function closeProjectCreateModalOnBackdrop(event) {
    if (event.target.id === 'project-create-modal') closeProjectCreateModal();
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeProjectCreateModal();
  });
  <?php if ((!empty($projectFormErrors) || old('title') || old('slug')) && !$isOverviewValidation): ?>
  openProjectCreateModal();
  <?php endif; ?>

  const projectOverviewData = {};
  <?php foreach ($projects as $project): ?>
  projectOverviewData[<?= (int)$project['id'] ?>] = {
    id: <?= (int)$project['id'] ?>,
    title: <?= json_encode($project['title'] ?? '') ?>,
    slug: <?= json_encode($project['slug'] ?? '') ?>,
    start_year: <?= json_encode(($project['start_year'] ?? 0) != 0 ? (string)$project['start_year'] : '') ?>,
    end_year: <?= json_encode(($project['end_year'] ?? 0) != 0 ? (string)$project['end_year'] : '') ?>,
    description: <?= json_encode($project['description'] ?? '') ?>,
    image_left: <?= json_encode((string)($project['image_left'] ?? '')) ?>,
    image_mid: <?= json_encode((string)($project['image_mid'] ?? '')) ?>,
    image_right: <?= json_encode((string)($project['image_right'] ?? '')) ?>,
    images: <?= json_encode(array_map(static function ($image) {
      return [
        'id' => (string)($image['id'] ?? ''),
        'file_id' => (string)($image['file_id'] ?? ''),
        'file_name' => (string)($image['file_name'] ?? ''),
      ];
    }, $project['images'] ?? [])) ?>
  };
  <?php endforeach; ?>

  const overviewOldValues = {
    overview_project_id: <?= json_encode((string)(old('overview_project_id') ?? '')) ?>,
    title: <?= json_encode((string)(old('title') ?? '')) ?>,
    slug: <?= json_encode((string)(old('slug') ?? '')) ?>,
    start_year: <?= json_encode((string)(old('start_year') ?? '')) ?>,
    end_year: <?= json_encode((string)(old('end_year') ?? '')) ?>,
    description: <?= json_encode((string)(old('description') ?? '')) ?>,
    image_left: <?= json_encode((string)(old('image_left') ?? '')) ?>,
    image_mid: <?= json_encode((string)(old('image_mid') ?? '')) ?>,
    image_right: <?= json_encode((string)(old('image_right') ?? '')) ?>
  };

  let projectOverviewImageMap = {};

  function setProjectOverviewPreview(field) {
    const select = document.getElementById('project-overview-select-' + field);
    const image = document.getElementById('project-overview-preview-' + field);
    if (!select || !image) return;
    const selectedId = select.value;
    const fileName = projectOverviewImageMap[selectedId] || '';
    if (fileName) {
      image.src = '/konst/thumb/' + fileName;
      image.style.display = '';
    } else {
      image.src = '';
      image.style.display = 'none';
    }
  }

  function fillProjectOverviewSelect(field, images, selectedValue) {
    const select = document.getElementById('project-overview-select-' + field);
    if (!select) return;
    let html = '<option value="">-- Select --</option>';
    images.forEach(function (img) {
      const selected = String(selectedValue || '') === String(img.id) ? ' selected' : '';
      html += '<option value="' + img.id + '"' + selected + '>' + img.file_id + '</option>';
    });
    select.innerHTML = html;
  }

  function openProjectOverviewModal(projectId, overrides) {
    const project = projectOverviewData[String(projectId)] || projectOverviewData[Number(projectId)];
    if (!project) return;

    const values = overrides || project;
    const modal = document.getElementById('project-overview-modal');
    const form = document.getElementById('project-overview-form');
    if (!modal || !form) return;

    document.getElementById('project-overview-project-id-field').value = String(project.id);
    form.action = '/artwork/update/' + project.id;
    document.getElementById('project-overview-title-input').value = values.title || '';
    document.getElementById('project-overview-slug-input').value = values.slug || '';
    document.getElementById('project-overview-start-year').value = values.start_year || '';
    document.getElementById('project-overview-end-year').value = values.end_year || '';
    document.getElementById('project-overview-description').value = values.description || '';

    projectOverviewImageMap = {};
    (project.images || []).forEach(function (img) {
      projectOverviewImageMap[String(img.id)] = img.file_name || '';
    });

    fillProjectOverviewSelect('image_left', project.images || [], values.image_left || '');
    fillProjectOverviewSelect('image_mid', project.images || [], values.image_mid || '');
    fillProjectOverviewSelect('image_right', project.images || [], values.image_right || '');

    setProjectOverviewPreview('image_left');
    setProjectOverviewPreview('image_mid');
    setProjectOverviewPreview('image_right');

    modal.style.display = 'flex';
    if (!overviewModalScrollLocked) {
      const scroller = document.scrollingElement || document.documentElement;
      overviewModalScrollY = scroller ? scroller.scrollTop : (window.scrollY || window.pageYOffset || 0);
      document.documentElement.style.overflow = 'hidden';
      document.body.style.overflow = 'hidden';
      overviewModalScrollLocked = true;
    }
  }

  function closeProjectOverviewModal() {
    const modal = document.getElementById('project-overview-modal');
    if (!modal) return;
    modal.style.display = 'none';
    if (overviewModalScrollLocked) {
      const targetY = overviewModalScrollY;
      document.documentElement.style.overflow = '';
      document.body.style.overflow = '';
      window.scrollTo(0, targetY);
      document.documentElement.scrollTop = targetY;
      document.body.scrollTop = targetY;
      requestAnimationFrame(function () {
        window.scrollTo(0, targetY);
      });
      overviewModalScrollLocked = false;
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    const createForm = document.getElementById('project-create-form');
    if (createForm) {
      createForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const modal = document.getElementById('project-create-modal');
        const errorBlock = modal.querySelector('.project-modal-error-list');
        if (errorBlock) errorBlock.innerHTML = '';
        try {
          const resp = await fetch(createForm.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
            body: new FormData(createForm)
          });
          const result = await resp.json();
          if (result.success) { closeProjectCreateModal(); window.location.reload(); }
          else {
            let html = '';
            if (result.errors) for (const k in result.errors) html += `<p>${result.errors[k]}</p>`;
            else if (result.error) html += `<p>${result.error}</p>`;
            if (errorBlock) errorBlock.innerHTML = html;
          }
        } catch { if (errorBlock) errorBlock.innerHTML = '<p>Server error. Please try again.</p>'; }
      });
    }

    document.querySelectorAll('.js-open-overview-modal').forEach(function (btn) {
      btn.addEventListener('click', function () {
        openProjectOverviewModal(btn.dataset.projectId || '');
      });
    });

    ['image_left', 'image_mid', 'image_right'].forEach(function (field) {
      const select = document.getElementById('project-overview-select-' + field);
      if (!select) return;
      select.addEventListener('change', function () {
        setProjectOverviewPreview(field);
      });
    });


    const overviewClose = document.getElementById('project-overview-modal-close');
    const overviewCancel = document.getElementById('project-overview-cancel-btn');
    if (overviewClose) {
      overviewClose.addEventListener('click', function (e) {
        e.preventDefault();
        closeProjectOverviewModal();
      });
    }
    if (overviewCancel) {
      overviewCancel.addEventListener('click', function (e) {
        e.preventDefault();
        closeProjectOverviewModal();
      });
    }

    const overviewModal = document.getElementById('project-overview-modal');
    if (overviewModal) {
      overviewModal.addEventListener('click', function (e) {
        if (e.target === overviewModal) {
          closeProjectOverviewModal();
        }
      });
    }

    const shouldOpenOverviewModal = <?= $isOverviewValidation ? 'true' : 'false' ?>;
    if (shouldOpenOverviewModal && overviewOldValues.overview_project_id) {
      openProjectOverviewModal(overviewOldValues.overview_project_id, overviewOldValues);
    }
  });

  document.addEventListener('click', async function (e) {
    const moveBtn = e.target.closest('.js-project-move');
    if (!moveBtn) return;
    e.preventDefault();
    if (moveBtn.classList.contains('disabled') || moveBtn.classList.contains('is-busy')) return;
    moveBtn.classList.add('is-busy');
    try {
      const resp = await fetch(moveBtn.getAttribute('href'), {
        method: moveBtn.dataset.method || 'PATCH',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!resp.ok) throw new Error();
      const payload = await resp.json();
      if (payload.success && payload.moved) window.location.reload();
    } catch { window.location.href = moveBtn.getAttribute('href'); }
    finally { moveBtn.classList.remove('is-busy'); }
  });

  document.addEventListener('click', async function (e) {
    const deleteBtn = e.target.closest('.delete-link');
    if (!deleteBtn) return;
    e.preventDefault();
    if (!confirm('Are you sure you want to delete this project?')) return;
    try {
      const resp = await fetch(`/artwork/delete/${deleteBtn.dataset.id}`, {
        method: 'DELETE',
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
      });
      if (!resp.ok) throw new Error();
      const result = await resp.json();
      if (result.success) window.location.reload();
      else alert(result.error || 'Delete failed');
    } catch { alert('Delete failed.'); }
  });

  document.addEventListener('change', async function (e) {
    const toggle = e.target.closest('.js-project-publish-toggle');
    if (!toggle) return;

    const projectId = toggle.dataset.id;
    const previousValue = toggle.dataset.published === '1';
    const nextValue = toggle.checked;

    toggle.disabled = true;
    try {
      const resp = await fetch(`/artwork/publish/${projectId}`, {
        method: 'PATCH',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({ is_published: nextValue ? 1 : 0 })
      });

      if (!resp.ok) throw new Error('Failed to update publish state');
      const result = await resp.json();
      if (!result.success) throw new Error(result.error || 'Failed to update publish state');

      toggle.dataset.published = result.is_published ? '1' : '0';
      toggle.checked = !!result.is_published;
    } catch {
      toggle.checked = previousValue;
      alert('Failed to update publish state.');
    } finally {
      toggle.disabled = false;
    }
  });

  // Slug auto-generation logic
  function generateSlug(str) {
    return str
      .toLowerCase()
      .replace(/[åä]/g, 'a')
      .replace(/ö/g, 'o')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/^-+|-+$/g, '')
      .replace(/--+/g, '-');
  }
  const titleInput = document.getElementById('project-create-title-input');
  const slugInput = document.getElementById('project-create-slug-input');
  if (titleInput && slugInput) {
    function updateSlug() {
      slugInput.value = generateSlug(titleInput.value);
    }
    titleInput.addEventListener('input', updateSlug);
    // Initial fill
    updateSlug();
  }

</script>

<?php endif; ?>
<?= $this->endSection() ?>


<?= $this->section('content') ?>
<div class='contained'>
  <h1 class='visually-hidden'>Artwork</h1>
  <?php if (!isset($projects) || !is_array($projects)): ?>
    <div style="color:red;">Error: Projects data not available.</div>
  <?php else: ?>
    <?php foreach ($projects as $project): ?>
      <div class="project-card" id="<?= esc($project['slug'] ?? '') ?>" style="margin-bottom: 30px;">
        <?php
        // Build year range string for project
        $start_year = isset($project->start_year) ? $project->start_year : ($project['start_year'] ?? null);
        $end_year = isset($project->end_year) ? $project->end_year : ($project['end_year'] ?? null);
        $show_start = !empty($start_year) && $start_year != 0;
        $show_end = !empty($end_year) && $end_year != 0;
        $year_range = '';
        if ($show_start && $show_end) {
          $year_range = esc($start_year) . '–' . esc($end_year);
        } elseif ($show_start) {
          $year_range = esc($start_year);
        } elseif ($show_end) {
          $year_range = esc($end_year);
        }
        // Ensure all project links use base-url/slug
        $slug = isset($project->slug) ? $project->slug : ($project['slug'] ?? null);
        $projectUrl = $slug ? base_url($slug) : '#';
        // Count valid preview images
        $validImages = [];
        if (isset($project['preview']) && is_array($project['preview'])) {
          foreach ($project['preview'] as $image) {
            if ($image && isset($image['file_name']) && $image['file_name'] !== '' && $image['file_name'] !== '0') {
              $validImages[] = $image;
            }
          }
        }
        $numValidImages = count($validImages);
        ?>

        <?php if ($numValidImages > 0): ?>
          <?php
          $numCols = min($numValidImages, 3);
          $gap = 7;
          $thumbHeight = 122;
          $containerStyle = "display: grid; grid-template-columns: repeat({$numCols}, 1fr); gap: {$gap}px; width: 100%; max-width: 380px; margin: 6px 0 6px 0;";
          $imageStyle = "display: block; width: 100%; height: {$thumbHeight}px; max-width: none; max-height: {$thumbHeight}px; object-fit: cover; object-position: center; border: 0;";
          ?>
          <div class="hero-container" style="<?= $containerStyle ?>">
            <?php foreach ($validImages as $image):
              $image_file_name = $image['file_name'];
              $image_title = $image['title'] ?? '';
              ?>
              <a href="<?= $projectUrl ?>" style="display: block; width: 100%;">
                <img
                  src="<?= base_url('konst/thumb/' . $image_file_name) ?>"
                  srcset="<?= base_url('konst/thumb/' . $image_file_name) ?> 1x,
                          <?= base_url('konst/thumb2x/' . $image_file_name) ?> 2x"
                  alt="<?= esc($image_title) ?>"
                  loading="lazy"
                  style="<?= $imageStyle ?>"/>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <h2>
          <a href="<?= $projectUrl ?>">
            <?= isset($project->title) ? esc($project->title) : esc($project['title'] ?? '') ?>
            <!--            <?php /*if ($year_range !== ''): */?>
              (<?php /*= $year_range */?>)
            --><?php /*endif; */?>
          </a>
        </h2>
        <p style="margin:4px 0 4px 0; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
          <?= $project['description_parsed'] ?? nl2br(esc($project['description'] ?? '')) ?>
        </p>
        <div style="text-align: right;">
          <a href="<?= $projectUrl ?>" aria-label='read more about <?= esc($project['title'] ) ?>'>read more<span class="visually-hidden"> about <?= esc($project['title'] )?></span></a>
        </div>
        <?php if (session()->get('isLoggedIn')): ?>
          <div style="margin-top: 0; text-align: center;">
            <button
              type="button"
              class="admin-action-btn js-open-overview-modal"
              data-project-id="<?= (int)($project['id'] ?? ($project->id ?? 0)) ?>">
              Edit
            </button>
          </div>
        <?php endif; ?>
<!--        <?php /*if ($project !== end($projects)): */?>
          <hr class="light" style="margin: 16px 0 22px 0;">
        --><?php /*endif; */?>
      </div>

    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>

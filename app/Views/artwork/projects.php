<?= $this->extend('layouts/main') ?>

<?= $this->section('adminContent') ?>
<?php if (session()->get('isLoggedIn')): ?>
<?php $projectFormErrors = session('errors') ?? []; ?>

<?php if (session()->has('success')): ?>
  <div class="contained"><div class="alert success"><?= session('success') ?></div></div>
<?php endif; ?>

<div class="manage-projects contained">
  <div class="projects-table-wrapper">
    <table class="projects-table">
      <thead>
        <tr><th>Order</th><th>Title</th><th>Slug</th><th>Actions</th></tr>
      </thead>
      <tbody>
        <?php foreach ($projects as $index => $project): ?>
          <tr>
            <td class="order-controls">
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
            <td><?= esc($project['slug']) ?></td>
            <td><a href="#" class="delete-link" data-id="<?= $project['id'] ?>">delete</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="project-create-actions">
    <button type="button" class="button" onclick="openProjectCreateModal()">Add new Project</button>
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
          <button type="submit" class="button">Create</button>
        </div>
      </form>
    </div>
  </div>
  <hr class="light">
</div>

<script>
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
  <?php if (!empty($projectFormErrors) || old('title') || old('slug')): ?>
  openProjectCreateModal();
  <?php endif; ?>

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
  <?php if (!isset($projects) || !is_array($projects)): ?>
    <div style="color:red;">Error: Projects data not available.</div>
  <?php else: ?>
    <?php foreach ($projects as $project): ?>
      <div class="project-card" id="<?= esc($project['slug'] ?? '') ?>" style="margin-bottom: 20px;">
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
        <h2>
          <a href="<?= $projectUrl ?>">
            <?= isset($project->title) ? esc($project->title) : esc($project['title'] ?? '') ?>
            <?php if ($year_range !== ''): ?>
              (<?= $year_range ?>)
            <?php endif; ?>
          </a>
        </h2>
        <?php if ($numValidImages > 0): ?>
          <?php
          // Determine grid style based on image count
          if ($numValidImages === 1) {
            $containerStyle = 'display: block; width: 100%; height: 280px;';
            $imgStyle = 'width: 100%; height: 280px; object-fit: cover; display: block;';
          } elseif ($numValidImages === 2) {
            $containerStyle = 'display: grid; grid-template-columns: 1fr 1fr; gap: 2px; margin-top: 5px; width: 100%; height: 280px;';
            $imgStyle = 'width: 100%; height: 280px; object-fit: cover; display: block;';
          } else {
            $containerStyle = 'display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; margin-top: 5px; width: 100%; height: 280px;';
            $imgStyle = 'width: 100%; height: 280px; object-fit: cover; display: block;';
          }
          ?>
          <div class="hero-container" style="<?= $containerStyle ?>">
            <?php foreach ($validImages as $image):
              $image_file_name = $image['file_name'];
              $image_title = $image['title'] ?? '';
              ?>
              <a href="<?= $projectUrl ?>" style="display: block; width: 100%; height: 280px;">
                <img
                  src="<?= base_url('konst/medium/' . $image_file_name) ?>"
                  srcset="<?= base_url('konst/medium/' . $image_file_name) ?> 1x,
                          <?= base_url('konst/large/' . $image_file_name) ?> 2x"
                  alt="<?= esc($image_title) ?>"
                  height="280"
                  loading="lazy"
                  style="<?= $imgStyle ?>"/>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <p style="margin:7px 0 4px 0; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
          <?= esc($project['description'] ?? '') ?>
        </p>
        <div style="text-align: right;">
          <a href="<?= $projectUrl ?>">read more</a>
        </div>
<!--        <?php /*if ($project !== end($projects)): */?>
          <hr class="light" style="margin: 16px 0 22px 0;">
        --><?php /*endif; */?>
      </div>

    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>

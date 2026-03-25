<?php $projectFormErrors = session('errors') ?? []; ?>
<div class="manage-projects contained">
  <h2>Manage Projects</h2>

  <div class="projects-table-wrapper">
    <table class="projects-table">
      <thead>
      <tr>
        <th>Order</th>
        <th>Title</th>
        <th>Slug</th>
        <th>Actions</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($projects as $index => $project): ?>
        <tr>
          <td class="order-controls">
            <?php if ($index > 0): ?>
              <a href="/artwork/move-up/<?= $project['id'] ?>"
                 class="order-btn js-project-move"
                 data-method="PATCH"
                 data-direction="up"
                 title="Move up">▲</a>
            <?php else: ?>
              <span class="order-btn disabled">▲</span>
            <?php endif; ?>
            <?php if ($index < count($projects) - 1): ?>
              <a href="/artwork/move-down/<?= $project['id'] ?>"
                 data-method="PATCH"
                 class="order-btn js-project-move"
                 data-direction="down"
                 title="Move down">▼</a>
            <?php else: ?>
              <span class="order-btn disabled">▼</span>
            <?php endif; ?>
          </td>
          <td><a href="/<?= $project['slug'] ?>"><?= esc($project['title']) ?></a></td>
          <td><?= esc($project['slug']) ?></td>
          <td>
            <a href="#" class="delete-link" data-id="<?= $project['id'] ?>"
               onclick="return confirm('Are you sure you want to delete this project?')">delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (session()->has('success')): ?>
    <div class="alert success"><?= session('success') ?></div>
  <?php endif; ?>

  <div class="project-create-actions">
    <button type="button" class="button" onclick="openProjectCreateModal()">Add new Project</button>
  </div>

  <div class="project-create-modal" id="project-create-modal" style="display:none;"
       onclick="closeProjectCreateModalOnBackdrop(event)">
    <div class="project-create-modal-content" role="dialog" aria-modal="true" aria-labelledby="project-create-title">
      <button type="button" class="project-create-modal-close" onclick="closeProjectCreateModal()" aria-label="Close">
        &times;
      </button>
      <h3 id="project-create-title">Add New Project</h3>
      <form action="/artwork/store" method="post" class="new-project-form" id="project-create-form">
        <?= csrf_field() ?>
        <div class="project-modal-error-list">
          <?php if (!empty($projectFormErrors)): ?>
            <?php foreach ($projectFormErrors as $error): ?>
              <p><?= esc($error) ?></p>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <div>
          <label for="project-create-title-input">Title</label>
          <input type="text" name="title" id="project-create-title-input" required value="<?= old('title') ?>">
          <?php if (!empty($projectFormErrors['title'])): ?>
            <small class="project-form-field-error"><?= esc($projectFormErrors['title']) ?></small>
          <?php endif; ?>
        </div>
        <div>
          <label for="project-create-slug-input">Slug</label>
          <input type="text"
                 name="slug"
                 id="project-create-slug-input"
                 required
                 value="<?= old('slug') ?>"
                 pattern="[a-z0-9\-]+"
                 title="Use lowercase letters, numbers, and hyphens only.">
          <small class="project-form-hint">Use lowercase letters, numbers, and hyphens (example:
            <code>my-new-project</code>).</small>
          <?php if (!empty($projectFormErrors['slug'])): ?>
            <small class="project-form-field-error"><?= esc($projectFormErrors['slug']) ?></small>
          <?php endif; ?>
        </div>
        <div class="form-actions">
          <button type="submit" class="button">Create</button>
        </div>
      </form>
    </div>
  </div>

  <hr class='light'>
</div>
<div class='contained'>
  <h2 style='margin:10px 0'>Project Overviews</h2>
  <p>This is the overview for the projects. Pick three images, write a short summary per project.</p>
  <?php foreach ($projects as $project): ?>
  <div class="project-edit-expandable" data-project-id="<?= $project['id'] ?>"
       style="border:1px solid #ddd; border-radius:6px; margin:10px 0 10px 0;">
    <button type="button" class="project-expand-toggle" aria-expanded="false"
            aria-controls="project-form-<?= $project['slug'] ?>"
            style="width:100%; text-align:left; background:none; border:none; padding:12px 16px; font-family:'Courier New',sans-serif; font-size:12px; color:#767676; white-space:nowrap; letter-spacing:-0.5px; line-height:1.3; cursor:pointer; display:flex; align-items:center; gap:0.7em;">
      <span class="chevron" style="display:inline-block; transition:transform 0.2s;">▶</span>
      <span><?= esc($project['title'] ?? '') ?></span>
    </button>
    <form method="post" action="<?= base_url('artwork/update/' . $project['id']) ?>"
          class="project-card project-edit-form" id="project-form-<?= $project['slug'] ?>"
          style="display:none; padding:0 12px 8px 12px;">
      <div style="display: flex; gap: 1em; align-items: center;">
        <label>Title <input type="text" name="title" value="<?= esc($project['title'] ?? '') ?>" required
                             style="width: 180px;"></label>
        <label>Slug <input type="text" name="slug" value="<?= esc($project['slug'] ?? '') ?>" required
                            style="width: 140px;"></label>
      </div>
      <div style="display: flex; gap: 1em; align-items: center; margin-top: 8px;">
        <label>Year span
          <div>
            <input type="number" name="start_year"
                   value="<?= esc((isset($project['start_year']) && $project['start_year'] != 0) ? $project['start_year'] : '') ?>"
                   min="1900" max="2100" style="width: 80px;"> -
            <input type="number" name="end_year"
                   value="<?= esc((isset($project['end_year']) && $project['end_year'] != 0) ? $project['end_year'] : '') ?>"
                   min="1900" max="2100" style="width: 80px;">
        </label>
      </div>
  </div>
  <div style="display: flex; gap: 1em; align-items: center; margin-top: 8px;">
    <label>Image Left
      <select name="image_left" style="width: 109px;">
        <option value="">-- Select --</option>
        <?php if (isset($project['images']) && is_array($project['images'])): ?>
          <?php foreach ($project['images'] as $image): ?>
            <option
              value="<?= esc($image['id'] ?? '') ?>" <?= (isset($project['image_left']) && $project['image_left'] == ($image['id'] ?? null)) ? 'selected' : '' ?>><?= esc($image['file_id'] ?? '') ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </label>
    <label>Image Mid
      <select name="image_mid" style="width: 109px;">
        <option value="">-- Select --</option>
        <?php if (isset($project['images']) && is_array($project['images'])): ?>
          <?php foreach ($project['images'] as $image): ?>
            <option
              value="<?= esc($image['id'] ?? '') ?>" <?= (isset($project['image_mid']) && $project['image_mid'] == ($image['id'] ?? null)) ? 'selected' : '' ?>><?= esc($image['file_id'] ?? '') ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </label>
    <label>Image Right
      <select name="image_right" style="width: 109px;">
        <option value="">-- Select --</option>
        <?php if (isset($project['images']) && is_array($project['images'])): ?>
          <?php foreach ($project['images'] as $image): ?>
            <option
              value="<?= esc($image['id'] ?? '') ?>" <?= (isset($project['image_right']) && $project['image_right'] == ($image['id'] ?? null)) ? 'selected' : '' ?>><?= esc($image['file_id'] ?? '') ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
      </select>
    </label>
  </div>
  <div style="margin-top: 8px;">
    <label>Description<br>
      <textarea name="description" rows="5"
                style="width: 100%; box-sizing: border-box;"><?= esc($project['description'] ?? '') ?></textarea>
    </label>
  </div>
  <div style="margin-top: 10px; text-align: right;">
    <button type="submit">Update</button>
  </div>
  </form>
</div>
<?php if ($project !== end($projects)): ?>
<?php endif; ?>
<?php endforeach; ?>
<hr style="margin: 30px 0">
</div>

<script>
  // ...existing code for modal, move, delete...
  function openProjectCreateModal() {
    document.getElementById('project-create-modal').style.display = 'block';
  }

  function closeProjectCreateModal() {
    document.getElementById('project-create-modal').style.display = 'none';
  }

  function closeProjectCreateModalOnBackdrop(event) {
    if (event.target.id === 'project-create-modal') {
      closeProjectCreateModal();
    }
  }

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeProjectCreateModal();
    }
  });
  <?php if (!empty($projectFormErrors) || old('title') || old('slug')): ?>
  openProjectCreateModal();
  <?php endif; ?>
  document.addEventListener('DOMContentLoaded', function () {
    // ...existing code for project create modal submit...
    const form = document.getElementById('project-create-form');
    if (form) {
      form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const modal = document.getElementById('project-create-modal');
        const errorBlock = modal.querySelector('.project-modal-error-list');
        if (errorBlock) errorBlock.innerHTML = '';
        const formData = new FormData(form);
        try {
          const response = await fetch(form.action, {
            method: 'POST',
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            body: formData
          });
          const result = await response.json();
          if (result.success) {
            closeProjectCreateModal();
            window.location.reload();
          } else {
            let html = '';
            if (result.errors) {
              for (const key in result.errors) {
                html += `<p>${result.errors[key]}</p>`;
              }
            } else if (result.error) {
              html += `<p>${result.error}</p>`;
            }
            if (errorBlock) errorBlock.innerHTML = html;
          }
        } catch (err) {
          if (errorBlock) errorBlock.innerHTML = '<p>Server error. Please try again.</p>';
        }
      });
    }

    // Expand/collapse logic for project edit forms
    const expandToggles = document.querySelectorAll('.project-expand-toggle');
    expandToggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        const parent = toggle.closest('.project-edit-expandable');
        const form = parent.querySelector('.project-edit-form');
        const chevron = toggle.querySelector('.chevron');
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';
        // Collapse all others
        document.querySelectorAll('.project-edit-expandable .project-edit-form').forEach(f => {
          f.style.display = 'none';
        });
        document.querySelectorAll('.project-expand-toggle').forEach(btn => {
          btn.setAttribute('aria-expanded', 'false');
          const chev = btn.querySelector('.chevron');
          if (chev) chev.style.transform = '';
        });
        // Expand this one if it was not already expanded
        if (!isExpanded) {
          form.style.display = 'block';
          toggle.setAttribute('aria-expanded', 'true');
          if (chevron) chevron.style.transform = 'rotate(90deg)';
        }
      });
      // Keyboard accessibility: open on Enter/Space
      toggle.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          toggle.click();
        }
      });
    });
  });
  // ...existing code for move and delete...
  document.addEventListener('click', async function (event) {
    const moveBtn = event.target.closest('.js-project-move');
    if (!moveBtn) return;
    event.preventDefault();
    if (moveBtn.classList.contains('disabled') || moveBtn.classList.contains('is-busy')) {
      return;
    }
    moveBtn.classList.add('is-busy');
    const moveUrl = moveBtn.getAttribute('href');
    const method = moveBtn.dataset.method || 'PATCH';
    try {
      const response = await fetch(moveUrl, {
        method: method,
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      if (!response.ok) {
        throw new Error('Request failed');
      }
      const payload = await response.json();
      if (payload.success && payload.moved) {
        window.location.reload();
      }
    } catch (err) {
      window.location.href = moveUrl;
    } finally {
      moveBtn.classList.remove('is-busy');
    }
  });

  document.addEventListener('click', async function (event) {
    const deleteBtn = event.target.closest('.delete-link');
    if (!deleteBtn) return;
    event.preventDefault();
    if (!confirm('Are you sure you want to delete this project?')) return;
    const projectId = deleteBtn.dataset.id;
    const url = `/artwork/delete/${projectId}`;
    try {
      const response = await fetch(url, {
        method: 'DELETE',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        }
      });
      if (!response.ok) throw new Error('Delete failed');
      const result = await response.json();
      if (result.success) {
        window.location.reload();
      } else {
        alert(result.error || 'Delete failed');
      }
    } catch (err) {
      alert('Delete failed.');
    }
  });
</script>


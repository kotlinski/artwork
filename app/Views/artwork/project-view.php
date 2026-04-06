<?= $this->extend('layouts/main') ?>

<?= $this->section('adminContent') ?>
<?php if (session()->get('isLoggedIn')): ?>

  <?php $uploadErrors = session()->getFlashdata('upload_errors'); ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="contained">
      <div class="alert success"><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('upload_error')): ?>
    <div class="contained">
      <div class="alert error"><?= esc(session()->getFlashdata('upload_error')) ?></div>
    </div>
  <?php endif; ?>
  <?php if (!empty($uploadErrors)): ?>
    <div class="contained">
      <div class="alert error">
        <ul style="margin:0;padding-left:18px;">
          <?php foreach ($uploadErrors as $err): ?>
            <li><?= esc($err) ?></li><?php endforeach; ?>
        </ul>
      </div>
    </div>
  <?php endif; ?>


  <div class="contained">
    <div class="project-edit-expandable" style="margin:10px 0;">
      <button type="button" class="project-expand-toggle" aria-expanded="false"
              aria-controls="project-overview-form"
              style="width:100%;text-align:left;background:none;border:0;padding:12px 16px;font-family:'Courier New',sans-serif;font-size:12px;color:#767676;letter-spacing:-0.5px;line-height:1.3;cursor:pointer;display:flex;align-items:center;gap:0.7em;">
        <span class="chevron" style="display:inline-block;transition:transform 0.2s;">▶</span>
        <span>Project Overview — <?= esc($project['title'] ?? '') ?></span>
      </button>
      <form method="post" action="<?= base_url('artwork/update/' . ($project['id'] ?? '')) ?>"
            class="project-edit-form" id="project-overview-form"
            style="display:none;padding:0 12px 12px 12px;">
        <?= csrf_field() ?>
        <div style="display:flex;gap:1em;align-items:center;flex-wrap:wrap;">
          <label>Title <input type="text" name="title" value="<?= esc($project['title'] ?? '') ?>" required
                              style="width:180px;"></label>
          <label>Slug <input type="text" name="slug" value="<?= esc($project['slug'] ?? '') ?>" required
                             style="width:140px;"></label>
        </div>
        <div style="display:flex;gap:1em;align-items:center;margin-top:8px;flex-wrap:wrap;">
          <label>Year span
            <div>
              <input type="number" name="start_year"
                     value="<?= esc(($project['start_year'] ?? 0) != 0 ? $project['start_year'] : '') ?>"
                     min="1900" max="2100" style="width:80px;"> –
              <input type="number" name="end_year"
                     value="<?= esc(($project['end_year'] ?? 0) != 0 ? $project['end_year'] : '') ?>"
                     min="1900" max="2100" style="width:80px;">
            </div>
          </label>
        </div>
        <!-- Image preview row -->
        <!-- Image select row above previews, no labels -->
        <div style="display:flex;gap:1em;align-items:center;margin-top:8px;flex-wrap:wrap;justify-content:left;">
          <?php foreach (["image_left", "image_mid", "image_right"] as $field): ?>
            <select name="<?= $field ?>" id="select-<?= $field ?>" style="width:109px;">
              <option value="">-- Select --</option>
              <?php foreach ($images ?? [] as $image): ?>
                <option value="<?= esc($image['id'] ?? '') ?>"
                  <?= (isset($project[$field]) && $project[$field] == ($image['id'] ?? null)) ? 'selected' : '' ?>>
                  <?= esc($image['file_id'] ?? '') ?>
                </option>
              <?php endforeach; ?>
            </select>
          <?php endforeach; ?>
        </div>
        <!-- Image preview row, no labels, no rounded corners -->
        <div id="project-image-preview-row"
             style="display:flex;gap:1em;align-items:center;margin-top:8px;flex-wrap:wrap;">
          <div class="project-image-preview-box">
            <div class="project-image-preview-square"><img id="preview-image_left" src="" alt="Preview Left"
                                                           style="display:none;"></div>
          </div>
          <div class="project-image-preview-box">
            <div class="project-image-preview-square"><img id="preview-image_mid" src="" alt="Preview Mid"
                                                           style="display:none;"></div>
          </div>
          <div class="project-image-preview-box">
            <div class="project-image-preview-square"><img id="preview-image_right" src="" alt="Preview Right"
                                                           style="display:none;"></div>
          </div>
        </div>
        <style>
            .project-image-preview-box {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 110px;
            }

            .project-image-preview-square {
                width: 110px;
                height: 110px;
                background: #f4f4f4;
                overflow: hidden;
                display: flex;
                align-items: center;
                justify-content: center;
                position: relative;
            }

            .project-image-preview-square img {
                max-width: 100%;
                max-height: 100%;
                width: 110px;
                height: 110px;
                object-fit: cover;
                object-position: center;
                display: block;
                margin: auto;
                background: #e0e0ff; /* DEBUG: light blue background for image */
            }
        </style>
        <script>
          // Map image id to file_name for preview
          const projectImageIdToFile = {};
          <?php foreach ($images ?? [] as $image): ?>
          projectImageIdToFile[<?= json_encode($image['id']) ?>] = <?= json_encode($image['file_name']) ?>;
          <?php endforeach; ?>

          function updateProjectImagePreview(field) {
            const select = document.getElementById('select-' + field);
            const img = document.getElementById('preview-' + field);
            const val = select.value;
            if (val && projectImageIdToFile[val]) {
              img.src = '/konst/thumb/' + projectImageIdToFile[val];
              img.style.display = '';
            } else {
              img.src = '';
              img.style.display = 'none';
            }
          }

          ['image_left', 'image_mid', 'image_right'].forEach(function (field) {
            const select = document.getElementById('select-' + field);
            if (select) {
              select.addEventListener('change', function () {
                updateProjectImagePreview(field);
              });
              // Initial preview
              updateProjectImagePreview(field);
            }
          });
        </script>
        <div style="margin-top:8px;">
          <label>Description
            <span
              style="display:block;font-size:11px;color:#888;margin-bottom:2px;">Should be 150–160 characters long.</span>
            <textarea name="description" id="project-description-textarea" rows="4"
                      style="width:100%;box-sizing:border-box;"
                      maxlength="300"><?= esc($project['description'] ?? '') ?></textarea>
            <span id="desc-char-count" style="font-size:11px;color:#888;float:right;margin-top:2px;">0/160</span>
          </label>
          <script>
            // Live character counter for project description
            const descTextarea = document.getElementById('project-description-textarea');
            const descCharCount = document.getElementById('desc-char-count');
            if (descTextarea && descCharCount) {
              function updateDescCharCount() {
                const len = descTextarea.value.length;
                descCharCount.textContent = `150 < ${len} < 160`;
                descCharCount.style.color = (len <= 150 || len >= 160) ? '#b00' : '#080';
              }

              descTextarea.addEventListener('input', updateDescCharCount);
              updateDescCharCount();
            }
          </script>
        </div>
<div style="margin-top:12px;display:flex;justify-content:center;align-items:center;width:100%;">
  <button type="submit" style="margin:0 auto;display:block;">Update</button>
</div>
      </form>
    </div>
  </div>

  <div class="contained">
    <div class="project-edit-expandable" style="margin:10px 0">
      <button type="button" class="project-expand-toggle" aria-expanded="false"
              aria-controls="image-admin-section"
              style="width:100%;text-align:left;background:none;border:0;padding:12px 16px;font-family:'Courier New',sans-serif;font-size:12px;color:#767676;letter-spacing:-0.5px;line-height:1.3;cursor:pointer;display:flex;align-items:center;gap:0.7em;">
        <span class="chevron" style="display:inline-block;transition:transform 0.2s;">▶</span>
        <span>Image Administration</span>
      </button>
      <div id="image-admin-section" style="display:none; padding: 2px 12px">
        <div style="text-align:center; margin-bottom:10px;">
          <button type="button" onclick="openUploadModal()">Upload Image To Project</button>
        </div>
        <!-- Upload modal -->
        <div id="upload-image-modal"
             style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;">
          <div style="background:#fff;padding:28px 32px;border-radius:6px;min-width:320px;max-width:460px;width:100%;position:relative;">
            <span onclick="closeUploadModal()" style="position:absolute;top:10px;right:16px;font-size:22px;cursor:pointer;">&times;</span>
            <h3 style="margin-top:0;">Upload Image</h3>
            <form method="post" action="<?= base_url('image/upload') ?>" enctype="multipart/form-data">
              <?= csrf_field() ?>
              <input type="hidden" name="project_id" value="<?= esc($project['id']) ?>">
              <input type="hidden" name="return_to" value="<?= esc(current_url()) ?>">
              <label style="display:block;margin:12px;">
                Image file <span style="color:#888;font-size:12px;">(jpg, jpeg, png, webp — max 20 MB)</span>
                <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required style="display:block;margin-top:4px;">
              </label>
              <label style="display:block;margin:12px;">
                File ID <span style="color:#888;font-size:12px;">(slug, e.g. <em>bla-traktor</em>)</span>
                <input type="text" name="file_id" required
                       placeholder="e.g. bla-traktor"
                       pattern="[a-z0-9\-]+"
                       title="Lowercase letters, numbers and hyphens only"
                       value="<?= esc(session()->getFlashdata('upload_file_id') ?? '') ?>"
                       style="display:block;width:100%;margin-top:4px;box-sizing:border-box;">
              </label>
              <div style="text-align:right;">
                <button type="button" onclick="closeUploadModal()" style="margin-right:8px;">Cancel</button>
                <button type="submit">Upload</button>
              </div>
            </form>
          </div>
        </div>

        <!-- JS image data for this project -->
        <script>
          window.projectImages = {};
          window.projectImages[<?= json_encode($project['id']) ?>] = [
            <?php foreach ($images as $img): ?>
            {
              id: <?= json_encode($img['id']) ?>,
              file_name: <?= json_encode($img['file_name']) ?>,
              title: <?= json_encode($img['title']) ?>,
              alternate_name: <?= json_encode($img['alternate_name']) ?>,
              file_id: <?= json_encode($img['file_id']) ?>,
              project: <?= json_encode($img['project']) ?>,
              caption: <?= json_encode($img['caption']) ?>,
              artform: <?= json_encode($img['artform']) ?>,
              date_created: <?= json_encode($img['date_created']) ?>,
              art_medium: <?= json_encode($img['art_medium']) ?>,
              artwork_surface: <?= json_encode($img['artwork_surface']) ?>,
              height_cm: <?= json_encode($img['height_cm']) ?>,
              width_cm: <?= json_encode($img['width_cm']) ?>,
              depth_cm: <?= json_encode($img['depth_cm']) ?>,
              geo_location: <?= json_encode($img['geo_location']) ?>,
              address_locality: <?= json_encode($img['address_locality']) ?>,
              address_region: <?= json_encode($img['address_region']) ?>,
              photographer_name: <?= json_encode($img['photographer_name']) ?>,
              map_url: <?= json_encode($img['map_url']) ?>
            },
            <?php endforeach; ?>
          ];
        </script>

        <!-- Image list -->
        <ul class="image-list" id="project-image-list">
          <?php foreach ($images as $idx => $img): ?>
            <?php $isFirst = $idx === 0; $isLast = $idx === count($images) - 1; ?>
            <li class="image-list-item" data-image-id="<?= esc($img['id']) ?>" style="display: flex; align-items: center;">
              <span class="order-controls image-list-order-controls">
                <a href="/image/move-up/<?= $img['id'] ?>"
                   class="order-btn js-image-move<?= $isFirst ? ' disabled' : '' ?>"
                   data-direction="up" data-method="PATCH"
                   aria-disabled="<?= $isFirst ? 'true' : 'false' ?>" title="Move up">▲</a>
                <input type="number" class="order-input js-order-input"
                       value="<?= (int)$img['order'] ?>" min="1"
                       data-image-id="<?= $img['id'] ?>" title="Edit order">
                <a href="/image/move-down/<?= $img['id'] ?>"
                   class="order-btn js-image-move<?= $isLast ? ' disabled' : '' ?>"
                   data-direction="down" data-method="PATCH"
                   aria-disabled="<?= $isLast ? 'true' : 'false' ?>" title="Move down">▼</a>
              </span>
              <a href="#" class="image-edit-link"
                 onclick="openImageEditModal(<?= $img['id'] ?>, <?= $project['id'] ?>); return false;">
                <div class="image-list-thumb">
                  <img src="/konst/thumb/<?= esc($img['file_name']) ?>" alt="<?= esc($img['title']) ?>"
                       style="max-width:90px;max-height:60px;">
                </div>
              </a>
              <div class="image-list-main">
                <div class="image-list-title">
                  <span class="image-list-id">#<?= esc($img['id']) ?>.</span>
                  <a href="#" class="image-edit-link"
                     onclick="openImageEditModal(<?= $img['id'] ?>, <?= $project['id'] ?>); return false;">
                    <?= esc($img['file_id']) ?>
                  </a>
                </div>
                <?php if (!empty($img['caption'])): ?>
                  <div class="image-list-caption">
                    <span class="image-list-caption-text"><?= esc($img['caption']) ?></span>
                  </div>
                <?php endif; ?>
              </div>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>
  </div>

  <div class="contained">
    <div class="project-edit-expandable" style="margin:10px 0;">
      <button type="button" class="project-expand-toggle" aria-expanded="false"
              aria-controls="project-text-form"
              style="width:100%;text-align:left;background:none;border:0;padding:12px 16px;font-family:'Courier New',sans-serif;font-size:12px;color:#767676;letter-spacing:-0.5px;line-height:1.3;cursor:pointer;display:flex;align-items:center;gap:0.7em;">
        <span class="chevron" style="display:inline-block;transition:transform 0.2s;">▶</span>
        <span>Edit text about <?= esc($project['title'] ?? 'Projekt') ?></span>
      </button>
      <div class="project-edit-form" id="project-text-form" style="display:none;padding:0 12px 12px 12px;">
        <?= view('partials/markdown_editor', [
          'formAction' => base_url('project/update'),
          'id' => $project['id'] ?? '',
          'fieldName' => 'text',
          'fieldValue' => $project['text'] ?? '',
          'editor_title' => 'Edit text about ' . ($project['title'] ?? 'Projekt'),
          'fixed_width' => true,
        ]) ?>
      </div>
    </div>
  </div>



  <!-- Shared image edit modal -->
  <div class="image-edit-modal" id="image-edit-modal-shared" style="display:none;">
    <div class="image-edit-modal-content" style="max-width:900px;width:95vw;min-width:320px;position:relative;">
      <span class="image-edit-modal-close" onclick="closeImageEditModal()" style="z-index:2;">&times;</span>
      <div class="image-edit-modal-body" style="padding:24px 18px 0 18px;">
        <div class="image-edit-modal-img-wrapper"
             style="position:relative;display:flex;align-items:center;justify-content:center;margin-bottom:18px;">
          <button type="button" id="modal-btn-prev"
                  style="position:absolute;left:0;top:50%;transform:translateY(-50%);background:#f8f8f8;border:1px solid #bbb;border-radius:4px;min-width:60px;padding:8px 12px;font-size:14px;cursor:pointer;z-index:3;">
            Prev
          </button>
          <div style="text-align:center;">
            <img id="modal-img-preview" src="" alt="" style="max-width:500px;max-height:500px;height:auto;">
          </div>
          <button type="button" id="modal-btn-next"
                  style="position:absolute;right:0;top:50%;transform:translateY(-50%);background:#f8f8f8;border:1px solid #bbb;border-radius:4px;min-width:60px;padding:8px 12px;font-size:14px;cursor:pointer;z-index:3;">
            Next
          </button>
        </div>
        <form id="image-edit-modal-form" method="post" action="" style="width:100%;">
          <?= csrf_field() ?>
          <input type="hidden" name="modal_project_id" id="modal-project-id-hidden" value="">
          <input type="hidden" name="modal_image_index" id="modal-image-index-hidden" value="">
          <div class="image-edit-modal-fields">
            <div class="image-edit-modal-col">
              <h3>Basic Information</h3>
              <label>Title<input type="text" name="title" id="modal-title" value=""
                                 placeholder="e.g. Blå Traktor"></label>
              <label>Alternate Name<input type="text" name="alternate_name" id="modal-alternate-name" value=""
                                          placeholder="e.g. Blue Tractor"></label>
              <label>File ID<input type="text" name="file_id" id="modal-file-id" value="" placeholder="basket-closeup"></label>
              <label>Project
                <select name="project" id="modal-project">
                  <option value="">--</option>
                  <?php foreach ($all_projects ?? [] as $proj): ?>
                    <option value="<?= esc($proj['id']) ?>"><?= esc($proj['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Caption<textarea name="caption" id="modal-caption" rows="5"
                                      placeholder="Caption..."></textarea></label>
            </div>
            <div class="image-edit-modal-col">
              <h3>Artistic Data & Dimensions</h3>
              <label>Artform<input type="text" name="artform" id="modal-artform" value=""
                                   placeholder="e.g. Painting"></label>
              <label>Date Created<input type="text" name="date_created" id="modal-date-created" value=""
                                        placeholder="e.g. 2022"></label>
              <label>Medium<input type="text" name="art_medium" id="modal-art-medium" value=""
                                  placeholder="e.g. Oil, Acrylic"></label>
              <label>Surface<input type="text" name="artwork_surface" id="modal-artwork-surface" value=""
                                   placeholder="e.g. Canvas"></label>
              <label>Height (cm)<input type="text" name="height_cm" id="modal-height-cm" value=""></label>
              <label>Width (cm)<input type="text" name="width_cm" id="modal-width-cm" value=""></label>
              <label>Depth (cm)<input type="text" name="depth_cm" id="modal-depth-cm" value=""></label>
            </div>
            <div class="image-edit-modal-col">
              <h3>Location & Photographer</h3>
              <label>Geo Location<input type="text" name="geo_location" id="modal-geo-location" value=""
                                        placeholder="Kalmar Konstmuseum"></label>
              <label>Location Name<input type="text" name="address_locality" id="modal-address-locality" value=""
                                         placeholder="Kalmar"></label>
              <label>City / Region<input type="text" name="address_region" id="modal-address-region" value=""
                                         placeholder="Småland"></label>
              <label>Photographer<input type="text" name="photographer_name" id="modal-photographer-name"
                                        value=""></label>
              <label>Map URL<input type="text" name="map_url" id="modal-map-url" value=""
                                   placeholder="https://maps.app/..."></label>
            </div>
          </div>
          <div class="image-edit-modal-actions" style="display:flex;justify-content:space-between;align-items:center;">
            <span>
              <button type="button" onclick="closeImageEditModal()" style="margin-right:8px;">Cancel</button>
              <button type="button" class="image-delete-link" id="modal-btn-delete">delete</button>
            </span>
            <button type="submit" style="margin-right:0;">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script>
    function openUploadModal() {
      const m = document.getElementById('upload-image-modal');
      m.style.display = 'flex';
    }

    function closeUploadModal() {
      document.getElementById('upload-image-modal').style.display = 'none';
    }

    document.getElementById('upload-image-modal').addEventListener('click', function (e) {
      if (e.target === this) closeUploadModal();
    });
    <?php if (!empty($uploadErrors) || session()->getFlashdata('upload_error')): ?>
    openUploadModal();
    <?php endif; ?>

    let currentProjectId = null;
    let currentImageIndex = null;

    function populateImageEditModal(projectId, imageIndex) {
      const images = window.projectImages[projectId];
      if (!images || imageIndex < 0 || imageIndex >= images.length) return;
      const img = images[imageIndex];
      window.currentProjectId = String(projectId);
      window.currentImageIndex = imageIndex;
      document.getElementById('modal-project-id-hidden').value = String(projectId);
      document.getElementById('modal-image-index-hidden').value = imageIndex;
      document.getElementById('modal-img-preview').src = '/konst/medium/' + img.file_name;
      document.getElementById('modal-img-preview').alt = img.title;
      document.getElementById('modal-title').value = img.title || '';
      document.getElementById('modal-alternate-name').value = img.alternate_name || '';
      document.getElementById('modal-file-id').value = img.file_id || '';
      document.getElementById('modal-project').value = img.project || '';
      document.getElementById('modal-caption').value = img.caption || '';
      document.getElementById('modal-artform').value = img.artform || '';
      document.getElementById('modal-date-created').value = img.date_created || '';
      document.getElementById('modal-art-medium').value = img.art_medium || '';
      document.getElementById('modal-artwork-surface').value = img.artwork_surface || '';
      document.getElementById('modal-height-cm').value = img.height_cm || '';
      document.getElementById('modal-width-cm').value = img.width_cm || '';
      document.getElementById('modal-depth-cm').value = img.depth_cm || '';
      document.getElementById('modal-geo-location').value = img.geo_location || '';
      document.getElementById('modal-address-locality').value = img.address_locality || '';
      document.getElementById('modal-address-region').value = img.address_region || '';
      document.getElementById('modal-photographer-name').value = img.photographer_name || '';
      document.getElementById('modal-map-url').value = img.map_url || '';
      document.getElementById('image-edit-modal-form').action = '/image/update/' + img.id;
      document.getElementById('modal-btn-prev').disabled = (imageIndex === 0);
      document.getElementById('modal-btn-next').disabled = (imageIndex === images.length - 1);
    }

    function openImageEditModal(id, projectId) {
      const images = window.projectImages[projectId];
      const index = images.findIndex(img => img.id == id);
      if (index === -1) return;
      populateImageEditModal(projectId, index);
      document.getElementById('image-edit-modal-shared').style.display = 'block';
    }

    function closeImageEditModal() {
      document.getElementById('image-edit-modal-shared').style.display = 'none';
    }

    document.getElementById('modal-btn-prev').onclick = function () {
      const pid = String(window.currentProjectId);
      let idx = Number(window.currentImageIndex);
      if (pid === 'null' || isNaN(idx)) return;
      if (idx > 0) {
        populateImageEditModal(pid, idx - 1);
        window.currentImageIndex = idx - 1;
      }
    };
    document.getElementById('modal-btn-next').onclick = function () {
      const pid = String(window.currentProjectId);
      let idx = Number(window.currentImageIndex);
      const images = window.projectImages[pid];
      if (pid === 'null' || isNaN(idx) || !images) return;
      if (idx < images.length - 1) {
        populateImageEditModal(pid, idx + 1);
        window.currentImageIndex = idx + 1;
      }
    };

    function setMoveButtonState(btn, isDisabled) {
      if (!btn) return;
      btn.classList.toggle('disabled', isDisabled);
      btn.setAttribute('aria-disabled', isDisabled ? 'true' : 'false');
    }

    function refreshListMoveButtons(listEl) {
      Array.from(listEl.querySelectorAll(':scope > .image-list-item')).forEach((item, index, arr) => {
        setMoveButtonState(item.querySelector('.js-image-move[data-direction="up"]'), index === 0);
        setMoveButtonState(item.querySelector('.js-image-move[data-direction="down"]'), index === arr.length - 1);
      });
    }

    function refreshOrderInputs(listEl) {
      Array.from(listEl.querySelectorAll(':scope > .image-list-item')).forEach((el, idx) => {
        const inp = el.querySelector('.js-order-input');
        if (inp) inp.value = idx + 1;
      });
    }

    document.addEventListener('click', async function (e) {
      const moveBtn = e.target.closest('.js-image-move');
      if (!moveBtn) return;
      e.preventDefault();
      if (moveBtn.classList.contains('disabled') || moveBtn.classList.contains('is-busy')) return;
      const itemEl = moveBtn.closest('.image-list-item');
      const listEl = itemEl?.parentElement;
      const moveUrl = moveBtn.getAttribute('href');
      const direction = moveBtn.dataset.direction;
      if (!itemEl || !listEl || !moveUrl || !direction) return;
      moveBtn.classList.add('is-busy');
      try {
        const resp = await fetch(moveUrl, {
          method: moveBtn.dataset.method || 'PATCH',
          headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        });
        if (!resp.ok) throw new Error();
        const payload = await resp.json();
        if (!payload.success || !payload.moved) {
          refreshListMoveButtons(listEl);
          return;
        }
        const scrollTop = window.scrollY;
        const ordered = Array.from(listEl.querySelectorAll(':scope > .image-list-item'));
        const ci = ordered.indexOf(itemEl);
        if (direction === 'up' && ci > 0) listEl.insertBefore(itemEl, ordered[ci - 1]);
        else if (direction === 'down' && ci < ordered.length - 1) listEl.insertBefore(ordered[ci + 1], itemEl);
        refreshListMoveButtons(listEl);
        refreshOrderInputs(listEl);
        window.scrollTo(0, scrollTop);
      } catch {
        window.location.href = moveUrl;
      } finally {
        moveBtn.classList.remove('is-busy');
      }
    });

    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.project-expand-toggle').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
          const parent = toggle.closest('.project-edit-expandable');
          let content = parent.querySelector('.project-edit-form');
          if (!content) {
            content = parent.querySelector('#image-admin-section');
          }
          const chevron = toggle.querySelector('.chevron');
          const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

          // Always close all sections first
          document.querySelectorAll('.project-edit-form, #image-admin-section').forEach(function (section) {
            section.style.display = 'none';
          });
          document.querySelectorAll('.project-expand-toggle').forEach(function (btn) {
            btn.setAttribute('aria-expanded', 'false');
            const icon = btn.querySelector('.chevron');
            if (icon) icon.style.transform = '';
          });

          // If the clicked section was not open, open it
          if (!isExpanded) {
            if (content) content.style.display = 'block';
            toggle.setAttribute('aria-expanded', 'true');
            if (chevron) chevron.style.transform = 'rotate(90deg)';
          }
          // If it was open, do nothing (all are now closed)
        });
      });

      if (window.location.hash === '#project-overview-form') {
        const overviewToggle = document.querySelector('.project-expand-toggle[aria-controls="project-overview-form"]');
        if (overviewToggle) {
          overviewToggle.click();
          const target = document.getElementById('project-overview-form');
          if (target) target.scrollIntoView({ behavior: 'auto', block: 'start' });
        }
      }
    });
  </script>

<?php endif; ?>

<div class="contained">
  <hr class='light'  style="margin:20px 0;"/>
</div>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class='contained'>
  <?php if (isset($error)): ?>
    <p><?= esc($error) ?></p>
  <?php else: ?>
    <?php if (!empty($images) && is_array($images)): ?>
      <div
        style="display: grid;--thumb-size: calc((min(100vw, 400px) - 14px - 20px) / 3);grid-template-columns: repeat(3, var(--thumb-size));gap: 7px;width: calc(min(100vw, 380px) - 14px);margin: 6px 0 12px 0;">
        <?php foreach ($images as $img): ?>
          <?php if (!isset($img['file_name'])) continue; ?>
          <a href="<?= base_url(($project['slug'] ?? '') . '/' . ($img['file_id'] ?? '')) ?>" style="display: block;">
            <img
              src="<?= base_url('konst/thumb/' . $img['file_name']) ?>"
              srcset="<?= base_url('konst/thumb/' . $img['file_name']) ?> 1x,
                <?= base_url('konst/medium/' . $img['file_name']) ?> 2x"
              alt="<?= esc($img['title'] ?? '') ?>"
              loading="lazy"
              style="
              display: block;
              width: var(--thumb-size);
              height: var(--thumb-size);
              object-fit: contain;
              object-position: center;
              background: #fff;
              border: 0;
              "
            />
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <h1>
      <?= esc($project['title'] ?? $project->title ?? 'Projekt') ?>
    </h1>
    <div class="text">
      <?php
      // Render project text as markdown if available
      $text = $project['text'] ?? $project->text ?? '';
      if (!empty($text)) {
        // Use Parsedown if available, else fallback to nl2br
        if (class_exists('Parsedown')) {
          $parsedown = new Parsedown();
          echo $parsedown->text($text);
        } else {
          echo nl2br(esc($text));
        }
      }
      ?>
    </div>
    <hr class="light" style='margin: 14px 0'/>


    <!--    <hr class="light"/>
    -->    <?php if (!empty($project_news)): ?>
      <div class="project-news">
        <?php foreach ($project_news as $item):
          $createdTs = strtotime($item['created_at']);
          if ($createdTs !== false) {
            $svMonths = [
              1 => 'januari', 2 => 'februari', 3 => 'mars', 4 => 'april',
              5 => 'maj', 6 => 'juni', 7 => 'juli', 8 => 'augusti',
              9 => 'september', 10 => 'oktober', 11 => 'november', 12 => 'december',
            ];
            $createdLabel = date('j', $createdTs) . ' ' . $svMonths[(int)date('n', $createdTs)] . ' ' . date('Y', $createdTs);
          } else {
            $createdLabel = esc($item['created_at']);
          }
          ?>
          <article id="<?= esc($item['slug']) ?>" class="news-item">
            <h2><?= esc($item['title']) ?></h2>
            <!--<div class="date"><?php /*= $createdLabel */
            ?></div>-->
            <div class="body">
              <?= $item['content_parsed'] ?: nl2br(esc($item['content'] ?? '')) ?>
            </div>
            <?php if ($item !== end($project_news)): ?>
              <hr>
            <?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
      <hr class="light"/>
    <?php endif; ?>
    <div class="back-to-overview-row"
         style="display: flex; justify-content: space-between; align-items: center; margin: 1em 0;">
      <a href="<?= base_url('artwork') . '#' . ($project['slug'] ?? '') ?>">Back to Artworks</a>
    </div>
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

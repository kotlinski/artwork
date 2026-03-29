<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="contained">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('upload_error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('upload_error')) ?></div>
  <?php endif; ?>
  <?php $uploadErrors = session()->getFlashdata('upload_errors'); ?>
  <?php if (!empty($uploadErrors)): ?>
    <div class="alert error">
      <ul style="margin:0;padding-left:18px;">
        <?php foreach ($uploadErrors as $err): ?>
          <li><?= esc($err) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div style="margin-bottom: 18px;">
    <h2>Image Admin</h2>
    <p>Images are grouped by project, to re-order or edit an image, expand the group or upload a new image to a
      project.</p>
    <div style="text-align: center; margin-bottom: 10px;">
      <button type="button" onclick="openUploadModal()">Upload Image</button>
    </div>
  </div>

  <!-- Upload Image Modal -->
  <div id="upload-image-modal"
       style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
    <div
      style="background:#fff; padding:28px 32px; border-radius:6px; min-width:360px; max-width:480px; width:100%; position:relative;">
      <span onclick="closeUploadModal()"
            style="position:absolute; top:10px; right:16px; font-size:22px; cursor:pointer; line-height:1;">&times;</span>
      <h3 style="margin-top:0;">Upload Image</h3>
      <form method="post" action="/image/upload" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label style="display:block; margin-bottom:12px;">
          Project
          <select name="project_id" required style="display:block; width:100%; margin-top:4px;">
            <option value="">-- Select project --</option>
            <?php foreach ($projects as $proj): ?>
              <option value="<?= esc($proj['id']) ?>"
                <?= (string)(session()->getFlashdata('upload_project_id') ?? '') === (string)$proj['id'] ? ' selected' : '' ?>>
                <?= esc($proj['title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label style="display:block; margin-bottom:12px;">
          File ID <span style="color:#888; font-size:12px;">(slug, e.g. <em>basket-closeup</em>)</span>
          <input type="text" name="file_id" required
                 placeholder="e.g. basket-closeup"
                 pattern="[a-z0-9\-]+"
                 title="Lowercase letters, numbers and hyphens only"
                 value="<?= esc(session()->getFlashdata('upload_file_id') ?? '') ?>"
                 style="display:block; width:100%; margin-top:4px; box-sizing:border-box;">
        </label>
        <label style="display:block; margin-bottom:18px;">
          Image file <span style="color:#888; font-size:12px;">(jpg, jpeg, png, webp — max 20 MB)</span>
          <input type="file" name="image" accept=".jpg,.jpeg,.png,.webp" required
                 style="display:block; margin-top:4px;">
        </label>
        <div style="text-align:right;">
          <button type="button" onclick="closeUploadModal()" style="margin-right:8px;">Cancel</button>
          <button type="submit">Upload</button>
        </div>
      </form>
    </div>
  </div>
  <script>
    // Build a JS object of all images by project for modal navigation
    window.projectImages = {};
    <?php foreach ($projects as $project): ?>
    window.projectImages[<?= json_encode($project['id']) ?>] = [
      <?php foreach (($project['images'] ?? []) as $img): ?>
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
    <?php endforeach; ?>
  </script>
  <?php foreach ($projects as $project): ?>
    <?php $images = $project['images'] ?? []; ?>
    <div class="image-project-group">
      <h3 style='font-size: 12px; cursor: pointer; user-select: none;'
          class="project-toggle"
          onclick="toggleProject(<?= $project['id'] ?>)">
        <span class="toggle-icon" id="toggle-icon-<?= $project['id'] ?>">▶</span>
        <?= esc($project['title']) ?> (<?= count($images) ?> images)
      </h3>
      <ul class="image-list" id="project-list-<?= $project['id'] ?>" style="display: none;">
        <?php foreach ($images as $img): ?>
          <li class="image-list-item" data-image-id="<?= esc($img['id']) ?>">
            <?php $isFirst = ((int)$img['order'] <= 1); ?>
            <?php $isLast = ($img === end($images)); ?>
            <span class="order-controls image-list-order-controls">
              <a href="/image/move-up/<?= $img['id'] ?>"
                 class="order-btn js-image-move<?= $isFirst ? ' disabled' : '' ?>"
                 data-direction="up"
                 data-method="PATCH"
                 aria-disabled="<?= $isFirst ? 'true' : 'false' ?>"
                 title="Move up">▲</a>
              <input type="number"
                     class="order-input js-order-input"
                     value="<?= (int)$img['order'] ?>"
                     min="1"
                     data-image-id="<?= $img['id'] ?>"
                     title="Edit order">
              <a href="/image/move-down/<?= $img['id'] ?>"
                 class="order-btn js-image-move<?= $isLast ? ' disabled' : '' ?>"
                 data-direction="down"
                 data-method="PATCH"
                 aria-disabled="<?= $isLast ? 'true' : 'false' ?>"
                 title="Move down">▼</a>
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
              <div class="image-list-caption">
                <?php if (!empty($img['caption'])): ?><span
                  class="image-list-caption-text"> <?= esc($img['caption']) ?> </span>
                <?php endif; ?>
              </div>
              <div class="image-list-meta">
              </div>
            </div>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>

  <!-- Single shared image edit modal -->
  <div class="image-edit-modal" id="image-edit-modal-shared" style="display:none;">
    <div class="image-edit-modal-content" style="max-width:900px; width:95vw; min-width:320px; position:relative;">
      <span class="image-edit-modal-close" onclick="closeImageEditModal()" style="z-index:2;">&times;</span>
      <div class="image-edit-modal-body" style="padding: 24px 18px 0 18px;">
        <style>
            .image-edit-modal-img-wrapper {
                position: relative;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 18px;
            }

            .image-edit-modal-img-nav-btn {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                background: #f8f8f8;
                border: 1px solid #bbb;
                border-radius: 4px;
                min-width: 60px;
                padding: 8px 12px;
                font-size: 14px;
                cursor: pointer;
                transition: background 0.2s;
                z-index: 3;
            }

            .image-edit-modal-img-nav-btn.prev {
                left: 0;
            }

            .image-edit-modal-img-nav-btn.next {
                right: 0;
            }

            .image-edit-modal-img-nav-btn:disabled,
            .image-edit-modal-img-nav-btn.disabled {
                opacity: 0.5;
                cursor: not-allowed;
            }

            .image-edit-modal-img-preview {
                text-align: center;
            }

            @media (max-width: 700px) {
                .image-edit-modal-img-wrapper {
                    margin-bottom: 12px;
                }

                .image-edit-modal-img-nav-btn.prev {
                    left: 2px;
                    top: 50%;
                    bottom: auto;
                    transform: translateY(-50%);
                }

                .image-edit-modal-img-nav-btn.next {
                    right: 2px;
                    top: 50%;
                    bottom: auto;
                    transform: translateY(-50%);
                }
            }
        </style>
        <div class="image-edit-modal-img-wrapper">
          <button type="button" class="image-edit-modal-img-nav-btn prev" id="modal-btn-prev">Prev</button>
          <div class="image-edit-modal-img-preview">
            <img id="modal-img-preview" src="" alt="" style="max-width:500px;max-height:500px; height:auto;">
          </div>
          <button type="button" class="image-edit-modal-img-nav-btn next" id="modal-btn-next">Next</button>
        </div>
        <form id="image-edit-modal-form" method="post" action="" style="width:100%;">
          <?= csrf_field() ?>
          <input type="hidden" name="modal_project_id" id="modal-project-id-hidden" value="">
          <input type="hidden" name="modal_image_index" id="modal-image-index-hidden" value="">
          <div class="image-edit-modal-fields">
            <div class="image-edit-modal-col">
              <h3>Basic Information</h3>
              <label>Title
                <input type="text" name="title" id="modal-title" value="" placeholder="e.g. Blå Traktor">
              </label>
              <label>Alternate Name
                <input type="text" name="alternate_name" id="modal-alternate-name" value=""
                       placeholder="e.g. Blue Tractor">
              </label>
              <label>File ID
                <input type="text" name="file_id" id="modal-file-id" value="" placeholder="basket-closeup">
              </label>
              <label>Project
                <select name="project" id="modal-project">
                  <option value="">--</option>
                  <?php foreach ($projects as $proj): ?>
                    <option value="<?= esc($proj['id']) ?>"><?= esc($proj['title']) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
              <label>Description / Caption
                <textarea name="caption" id="modal-caption" rows="5"
                          placeholder='Caption below image, e.g. "Scener ur ett ... 3 (Scenes from a ... 3) acrylic on masonite 10x10cm 2001"'></textarea>
              </label>
            </div>
            <div class="image-edit-modal-col">
              <h3>Artistic Data & Dimensions</h3>
              <label>Artform
                <input type="text" name="artform" id="modal-artform" value=""
                       placeholder="e.g. Painting, Sculpture, etc.">
              </label>
              <label>Date Created
                <input type="text" name="date_created" id="modal-date-created" value=""
                       placeholder="Date created (e.g. 2022)">
              </label>
              <label>Medium
                <input type="text" name="art_medium" id="modal-art-medium" value=""
                       placeholder="e.g. Oil, Acrylic, Digital, etc.">
              </label>
              <label>Surface
                <input type="text" name="artwork_surface" id="modal-artwork-surface" value=""
                       placeholder="e.g. Canvas, Paper, Wood, Board, etc.">
              </label>
              <label>Height (cm)
                <input type="text" name="height_cm" id="modal-height-cm" value="" placeholder="The height of the item.">
              </label>
              <label>Width (cm)
                <input type="text" name="width_cm" id="modal-width-cm" value="" placeholder="The width of the item.">
              </label>
              <label>Depth (cm)
                <input type="text" name="depth_cm" id="modal-depth-cm" value="" placeholder="The depth of the item.">
              </label>
            </div>
            <div class="image-edit-modal-col">
              <h3>Location & Photographer</h3>
              <label>Geo Location
                <input type="text" name="geo_location" id="modal-geo-location" value=""
                       placeholder="Kalmar Konstmuseum, Undantaget, etc.">
              </label>
              <label>Location Name
                <input type="text" name="address_locality" id="modal-address-locality" value=""
                       placeholder="Kalmar, Kläppinge, etc.">
              </label>
              <label>City / Region
                <input type="text" name="address_region" id="modal-address-region" value=""
                       placeholder="Småland, Öland, etc. ">
              </label>
              <label>Photographer
                <input type="text" name="photographer_name" id="modal-photographer-name" value=""
                       placeholder="Per Hamrin, Kalmar Konstmuseum, etc.">
              </label>
              <label>Map URL
                <input type="text" name="map_url" id="modal-map-url" value="" placeholder="https://maps.app/...">
              </label>
            </div>
          </div>
          <div class="image-edit-modal-actions"
               style="display: flex; justify-content: space-between; align-items: center;">
            <span>
              <button type="button" onclick="closeImageEditModal()" style="margin-right: 8px;">Cancel</button>
              <button type="button" class="image-delete-link" id="modal-btn-delete" style="margin-left: 0;">delete
            </button>
            </span>
            <button type="submit" style="margin-right: 0;">Update</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
<script>
  function openUploadModal() {
    const modal = document.getElementById('upload-image-modal');
    modal.style.display = 'flex';
  }

  function closeUploadModal() {
    const modal = document.getElementById('upload-image-modal');
    modal.style.display = 'none';
  }

  // Close modal when clicking the backdrop
  document.getElementById('upload-image-modal').addEventListener('click', function (e) {
    if (e.target === this) closeUploadModal();
  });

  <?php if (!empty($uploadErrors) || session()->getFlashdata('upload_error')): ?>
  openUploadModal();
  <?php endif; ?>

  let expandedProjectId = null;

  function setProjectExpanded(projectId, isExpanded) {
    const list = document.getElementById('project-list-' + projectId);
    const icon = document.getElementById('toggle-icon-' + projectId);
    if (!list || !icon) return;

    list.style.display = isExpanded ? 'block' : 'none';
    icon.textContent = isExpanded ? '▼' : '▶';
  }

  function toggleProject(projectId) {
    const targetProjectId = String(projectId);
    const isSameProject = String(expandedProjectId) === targetProjectId;

    if (expandedProjectId !== null && !isSameProject) {
      setProjectExpanded(expandedProjectId, false);
    }

    if (isSameProject) {
      setProjectExpanded(targetProjectId, false);
      expandedProjectId = null;
      return;
    }

    setProjectExpanded(targetProjectId, true);
    expandedProjectId = targetProjectId;
  }

  let currentProjectId = null;
  let currentImageIndex = null;

  function populateImageEditModal(projectId, imageIndex) {
    const images = window.projectImages[projectId];
    if (!images || imageIndex < 0 || imageIndex >= images.length) return;
    const img = images[imageIndex];
    console.log('Populating modal with:', img);
    window.currentProjectId = String(projectId);
    window.currentImageIndex = imageIndex;
    // Set hidden fields for fallback
    document.getElementById('modal-project-id-hidden').value = String(projectId);
    document.getElementById('modal-image-index-hidden').value = imageIndex;

    // Set image preview
    document.getElementById('modal-img-preview').src = '/konst/medium/' + img.file_name;
    document.getElementById('modal-img-preview').alt = img.title;

    // Set form fields
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

    // Set form action
    const form = document.getElementById('image-edit-modal-form');
    form.action = '/image/update/' + img.id;

    // Enable/disable prev/next buttons
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

  document.getElementById('modal-btn-prev').onclick = function () {
    const projectId = String(window.currentProjectId);
    let imageIndex = Number(window.currentImageIndex);
    console.log('Prev clicked. projectId:', projectId, 'imageIndex:', imageIndex);
    if (projectId === 'null' || isNaN(imageIndex)) return;
    if (imageIndex > 0) {
      populateImageEditModal(projectId, imageIndex - 1);
      window.currentProjectId = projectId;
      window.currentImageIndex = imageIndex - 1;
      console.log('After prev, projectId:', window.currentProjectId, 'imageIndex:', window.currentImageIndex);
    }
  };
  document.getElementById('modal-btn-next').onclick = function () {
    const projectId = String(window.currentProjectId);
    let imageIndex = Number(window.currentImageIndex);
    const images = window.projectImages[projectId];
    console.log('Next clicked. projectId:', projectId, 'imageIndex:', imageIndex);
    if (projectId === 'null' || isNaN(imageIndex) || !images) return;
    if (imageIndex < images.length - 1) {
      populateImageEditModal(projectId, imageIndex + 1);
      window.currentProjectId = projectId;
      window.currentImageIndex = imageIndex + 1;
      console.log('After next, projectId:', window.currentProjectId, 'imageIndex:', window.currentImageIndex);
    }
  };

  function closeImageEditModal() {
    const modal = document.getElementById('image-edit-modal-shared');
    modal.style.display = 'none';
  }

  function setMoveButtonState(button, isDisabled) {
    if (!button) return;
    button.classList.toggle('disabled', isDisabled);
    button.setAttribute('aria-disabled', isDisabled ? 'true' : 'false');
  }

  function refreshListMoveButtons(listEl) {
    const items = Array.from(listEl.querySelectorAll(':scope > .image-list-item'));
    items.forEach((item, index) => {
      const upBtn = item.querySelector('.js-image-move[data-direction="up"]');
      const downBtn = item.querySelector('.js-image-move[data-direction="down"]');
      setMoveButtonState(upBtn, index === 0);
      setMoveButtonState(downBtn, index === items.length - 1);
    });
  }

  function refreshOrderInputs(listEl) {
    Array.from(listEl.querySelectorAll(':scope > .image-list-item')).forEach((el, idx) => {
      const inp = el.querySelector('.js-order-input');
      if (inp) inp.value = idx + 1;
    });
  }

  document.addEventListener('click', async function (event) {
    const moveBtn = event.target.closest('.js-image-move');
    if (!moveBtn) return;

    event.preventDefault();
    if (moveBtn.classList.contains('disabled') || moveBtn.classList.contains('is-busy')) {
      return;
    }

    const itemEl = moveBtn.closest('.image-list-item');
    const listEl = itemEl ? itemEl.parentElement : null;
    const moveUrl = moveBtn.getAttribute('href');
    const direction = moveBtn.dataset.direction;

    if (!itemEl || !listEl || !moveUrl || !direction) {
      return;
    }

    moveBtn.classList.add('is-busy');

    try {
      const method = moveBtn.dataset.method || 'PATCH';
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
      if (!payload.success || !payload.moved) {
        refreshListMoveButtons(listEl);
        return;
      }

      const scrollTop = window.scrollY;
      const orderedItems = Array.from(listEl.querySelectorAll(':scope > .image-list-item'));
      const currentIndex = orderedItems.indexOf(itemEl);

      if (direction === 'up' && currentIndex > 0) {
        const prevItem = orderedItems[currentIndex - 1];
        listEl.insertBefore(itemEl, prevItem);
      } else if (direction === 'down' && currentIndex !== -1 && currentIndex < orderedItems.length - 1) {
        const nextItem = orderedItems[currentIndex + 1];
        listEl.insertBefore(nextItem, itemEl);
      }

      refreshListMoveButtons(listEl);
      refreshOrderInputs(listEl);
      window.scrollTo(0, scrollTop);
    } catch (err) {
      // Fallback to normal navigation if async request fails.
      window.location.href = moveUrl;
    } finally {
      moveBtn.classList.remove('is-busy');
    }
  });

  // AJAX update for image edit modal
  function setupImageEditAjax() {
    document.querySelectorAll('.image-edit-modal form').forEach(function (form) {
      form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const modal = form.closest('.image-edit-modal');
        const id = modal.id.replace('image-edit-modal-', '');
        const url = form.action;
        const formData = new FormData(form);
        // Remove any previous error
        let errorDiv = form.querySelector('.ajax-error');
        if (errorDiv) errorDiv.remove();
        try {
          const resp = await fetch(url, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
          });
          const data = await resp.json();
          // Get projectId and imageIndex from window or fallback to hidden fields
          let projectId = window.currentProjectId;
          let imageIndex = window.currentImageIndex;
          if (!projectId) projectId = form.querySelector('#modal-project-id-hidden').value;
          if (imageIndex === undefined || imageIndex === null || imageIndex === '') imageIndex = form.querySelector('#modal-image-index-hidden').value;
          projectId = String(projectId);
          imageIndex = Number(imageIndex);
          console.log('Current projectId:', projectId, 'imageIndex:', imageIndex);
          if (!resp.ok || !data.success) {
            showAjaxError(form, data.error || 'Update failed.');
            return;
          }
          // Use returned image data to update JS array in place
          if (projectId && imageIndex !== null && data.image) {
            const images = window.projectImages[projectId];
            if (images && images[imageIndex]) {
              Object.assign(images[imageIndex], data.image);
              console.log('Updated JS array entry:', images[imageIndex]);
              // If modal is still open, repopulate fields from updated JS array
              const modalEl = document.getElementById('image-edit-modal-shared');
              if (modalEl && modalEl.style.display !== 'none') {
                populateImageEditModal(projectId, imageIndex);
                console.log('Modal repopulated with:', images[imageIndex]);
              }
            } else {
              console.warn('Could not find image in JS array for update:', projectId, imageIndex, images);
            }
          } else {
            console.warn('No projectId/imageIndex/data.image for update:', projectId, imageIndex, data.image);
          }
          // Success: close modal and show toast
          closeImageEditModal();
          showToast('Image updated!');
        } catch (err) {
          showAjaxError(form, 'Update failed.');
        }
      });
    });
  }

  function showAjaxError(form, msg) {
    let div = document.createElement('div');
    div.className = 'ajax-error';
    div.style.color = '#b00';
    div.style.margin = '8px 0';
    div.textContent = msg;
    form.insertBefore(div, form.firstChild);
  }

  function showToast(msg) {
    let toast = document.createElement('div');
    toast.textContent = msg;
    toast.style.position = 'fixed';
    toast.style.bottom = '32px';
    toast.style.left = '50%';
    toast.style.transform = 'translateX(-50%)';
    toast.style.background = '#222';
    toast.style.color = '#fff';
    toast.style.padding = '12px 28px';
    toast.style.borderRadius = '6px';
    toast.style.fontSize = '16px';
    toast.style.zIndex = 2000;
    document.body.appendChild(toast);
    setTimeout(() => {
      toast.remove();
    }, 2000);
  }

  document.addEventListener('DOMContentLoaded', setupImageEditAjax);

  document.getElementById('modal-btn-delete').onclick = async function() {
    const projectId = String(window.currentProjectId);
    const imageIndex = Number(window.currentImageIndex);
    const images = window.projectImages[projectId];
    if (!images || isNaN(imageIndex) || !images[imageIndex]) return;
    const img = images[imageIndex];
    if (!img || !img.id) return;
    if (!window.confirm('Are you sure you want to delete this image?')) return;
    try {
      const resp = await fetch('/image/delete/' + img.id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
      });
      const data = await resp.json();
      if (!resp.ok || !data.success) {
        showToast(data.error || 'Delete failed.');
        return;
      }
      // Remove from JS array
      images.splice(imageIndex, 1);
      // Optionally, remove from DOM list
      const listItem = document.querySelector('.image-list-item[data-image-id="' + img.id + '"]');
      if (listItem) listItem.remove();
      closeImageEditModal();
      showToast('Image deleted!');
    } catch (err) {
      showToast('Delete failed.');
    }
  };
  document.addEventListener('DOMContentLoaded', function () {
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && e.target.classList.contains('js-order-input')) {
        e.preventDefault();
        e.target.blur();
      }
    });

    document.addEventListener('change', async function (e) {
      const input = e.target;
      if (!input.classList.contains('js-order-input')) return;

      const imageId = input.dataset.imageId;
      const newOrder = parseInt(input.value, 10);
      if (!imageId || isNaN(newOrder) || newOrder < 1) return;

      const itemEl = input.closest('.image-list-item');
      const listEl = itemEl ? itemEl.parentElement : null;
      if (!listEl) return;

      input.disabled = true;
      try {
        const resp = await fetch('/image/reorder/' + imageId, {
          method: 'PATCH',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ order: newOrder })
        });

        if (!resp.ok) throw new Error('Request failed');
        const payload = await resp.json();
        if (!payload.success) throw new Error('Reorder failed');

        // Reposition item in the DOM
        const scrollTop = window.scrollY;
        const items = Array.from(listEl.querySelectorAll(':scope > .image-list-item'));
        listEl.removeChild(itemEl);
        const clampedIndex = Math.min(Math.max(newOrder - 1, 0), items.length - 1);
        const sibling = items[clampedIndex] !== itemEl ? items[clampedIndex] : null;
        if (sibling && listEl.contains(sibling)) {
          listEl.insertBefore(itemEl, sibling);
        } else {
          listEl.appendChild(itemEl);
        }

        refreshListMoveButtons(listEl);
        refreshOrderInputs(listEl);
        window.scrollTo(0, scrollTop);
      } catch (err) {
        // Restore original value on failure
        const items = Array.from(listEl.querySelectorAll(':scope > .image-list-item'));
        input.value = items.indexOf(itemEl) + 1;
      } finally {
        input.disabled = false;
      }
    });
  });
</script>
<?= $this->endSection() ?>

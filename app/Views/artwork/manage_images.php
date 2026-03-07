<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="contained">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert success">Image updated.</div>
  <?php endif; ?>
  <?php foreach ($projects as $project): ?>
    <?php $images = $project['images'] ?? []; ?>
    <div class="image-project-group">
      <h3 style='font-size: 14px; cursor: pointer; user-select: none;'
          class="project-toggle"
          onclick="toggleProject(<?= $project['id'] ?>)">
        <span class="toggle-icon" id="toggle-icon-<?= $project['id'] ?>">▶</span>
        <?= esc($project['title']) ?> (<?= count($images) ?> images)
      </h3>
      <ul class="image-list" id="project-list-<?= $project['id'] ?>" style="display: none;">
        <?php foreach ($images as $img): ?>
          <li class="image-list-item" data-image-id="<?= esc($img['id']) ?>">
            <?php $isFirst = ((int) $img['order'] <= 1); ?>
            <?php $isLast = ($img === end($images)); ?>
            <span class="order-controls image-list-order-controls">
              <a href="/image/move-up/<?= $img['id'] ?>"
                 class="order-btn js-image-move<?= $isFirst ? ' disabled' : '' ?>"
                 data-direction="up"
                 aria-disabled="<?= $isFirst ? 'true' : 'false' ?>"
                 title="Move up">▲</a>
              <a href="/image/move-down/<?= $img['id'] ?>"
                 class="order-btn js-image-move<?= $isLast ? ' disabled' : '' ?>"
                 data-direction="down"
                 aria-disabled="<?= $isLast ? 'true' : 'false' ?>"
                 title="Move down">▼</a>
            </span>
            <a href="#" class="image-edit-link" onclick="openImageEditModal(<?= $img['id'] ?>); return false;">
              <div class="image-list-thumb">
                <img src="/konst/thumb/<?= esc($img['file_name']) ?>" alt="<?= esc($img['title']) ?>"
                     style="max-width:90px;max-height:60px;">
              </div>
            </a>

            <div class="image-list-main">
              <div class="image-list-title">
                <span class="image-list-id">#<?= esc($img['id']) ?>.</span>
                <a href="#" class="image-edit-link" onclick="openImageEditModal(<?= $img['id'] ?>); return false;">
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
          <!-- Modal for editing image -->
          <div class="image-edit-modal" id="image-edit-modal-<?= $img['id'] ?>" style="display:none;">
            <div class="image-edit-modal-content">
              <span class="image-edit-modal-close" onclick="closeImageEditModal(<?= $img['id'] ?>)">&times;</span>
              <div class="image-edit-modal-body">
                <div class="image-edit-modal-img">
                  <img src="/konst/medium/<?= esc($img['file_name']) ?>" alt="<?= esc($img['title']) ?>"
                       style="max-width:500px;max-height:500px;">
                </div>
                <form method="post" action="/image/update/<?= $img['id'] ?>">
                  <?= csrf_field() ?>
                  <div class="image-edit-modal-fields">
                    <div class="image-edit-modal-col">
                      <h4>Grundläggande information</h4>
                      <label>Titel
                        <input type="text" name="title" value="<?= esc($img['title']) ?>">
                      </label>
                      <label>Alternativ titel
                        <input type="text" name="alternate_name" value="<?= esc($img['alternate_name']) ?>">
                      </label>
                      <label>Bild ID (URL-vänlig)
                        <input type="text" name="file_id" value="<?= esc($img['file_id']) ?>">
                      </label>
                      <label>Projekt
                        <select name="project">
                          <option value="">--</option>
                          <?php foreach ($projects as $proj): ?>
                            <option
                              value="<?= esc($proj['id']) ?>"<?= (string)$img['project'] === (string)$proj['id'] ? ' selected' : '' ?>><?= esc($proj['title']) ?></option>
                          <?php endforeach; ?>
                        </select>
                      </label>
                      <label>Beskrivning / Bildtext
                        <textarea name="caption" rows="2"><?= esc($img['caption']) ?></textarea>
                      </label>
                    </div>
                    <div class="image-edit-modal-col">
                      <h4>Konstnärlig data & Mått</h4>
                      <label>Konstform
                        <input type="text" name="artform" value="<?= esc($img['artform']) ?>">
                      </label>
                      <label>År
                        <input type="text" name="date_created" value="<?= esc($img['date_created']) ?>">
                      </label>
                      <label>Medium
                        <input type="text" name="art_medium" value="<?= esc($img['art_medium']) ?>">
                      </label>
                      <label>Underlag
                        <input type="text" name="artwork_surface" value="<?= esc($img['artwork_surface']) ?>">
                      </label>
                      <label>Höjd (cm)
                        <input type="text" name="height_cm" value="<?= esc($img['height_cm']) ?>">
                      </label>
                      <label>Bredd (cm)
                        <input type="text" name="width_cm" value="<?= esc($img['width_cm']) ?>">
                      </label>
                      <label>Djup (cm)
                        <input type="text" name="depth_cm" value="<?= esc($img['depth_cm']) ?>">
                      </label>
                    </div>
                    <div class="image-edit-modal-col">
                      <h4>Plats & Fotograf</h4>
                      <label>Platsnamn
                        <input type="text" name="address_locality" value="<?= esc($img['address_locality']) ?>">
                      </label>
                      <label>Stad
                        <input type="text" name="address_region" value="<?= esc($img['address_region']) ?>">
                      </label>
                      <label>Fotograf
                        <input type="text" name="photographer_name" value="<?= esc($img['photographer_name']) ?>">
                      </label>
                      <label>Karta (URL)
                        <input type="text" name="map_url" value="<?= esc($img['map_url']) ?>">
                      </label>
                    </div>
                  </div>
                  <div class="image-edit-modal-actions">
                    <button type="button" class="image-delete-link" onclick="return confirm('Delete image?')">DELETE</button>
                    <button type="button" onclick="closeImageEditModal(<?= $img['id'] ?>)">← Prev</button>
                    <button type="submit">Uppdatera</button>
                    <button type="button" onclick="closeImageEditModal(<?= $img['id'] ?>);">Next →</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endforeach; ?>
</div>
<script>
  function toggleProject(projectId) {
    const list = document.getElementById('project-list-' + projectId);
    const icon = document.getElementById('toggle-icon-' + projectId);

    if (list.style.display === 'none') {
      list.style.display = 'block';
      icon.textContent = '▼';
    } else {
      list.style.display = 'none';
      icon.textContent = '▶';
    }
  }

  function openImageEditModal(id) {
    document.getElementById('image-edit-modal-' + id).style.display = 'block';
  }

  function closeImageEditModal(id) {
    document.getElementById('image-edit-modal-' + id).style.display = 'none';
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
      const response = await fetch(moveUrl, {
        method: 'GET',
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
      window.scrollTo(0, scrollTop);
    } catch (err) {
      // Fallback to normal navigation if async request fails.
      window.location.href = moveUrl;
    } finally {
      moveBtn.classList.remove('is-busy');
    }
  });
</script>
<?= $this->endSection() ?>

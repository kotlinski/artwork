<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div class="image-admin-list">
    <h2>Image Administration</h2>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert success">Image updated.</div>
    <?php endif; ?>
    <?php foreach ($groupedImages as $project => $images): ?>
        <div class="image-project-group">
            <h3><?= esc($project ?: 'No Project') ?></h3>
            <ul class="image-list">
            <?php foreach ($images as $img): ?>
                <li class="image-list-item">
                    <div class="image-list-thumb">
                        <img src="/konst/thumb/<?= esc($img['file_name']) ?>" alt="<?= esc($img['title']) ?>" style="max-width:90px;max-height:60px;">
                    </div>
                    <div class="image-list-main">
                        <div class="image-list-title">
                            <span class="image-list-id">#<?= esc($img['id']) ?>.</span>
                            <a href="#" class="image-edit-link" onclick="openImageEditModal(<?= $img['id'] ?>); return false;">
                                <?= esc($img['file_id']) ?>
                            </a>
                        </div>
                        <div class="image-list-caption">
                            <?= esc($img['title']) ?>
                            <?php if (!empty($img['caption'])): ?>
                                <br><span class="image-list-caption-text"> <?= esc($img['caption']) ?> </span>
                            <?php endif; ?>
                        </div>
                        <div class="image-list-meta">
                            • <span class="image-list-filter">FILTER</span>
                            • <span class="image-list-order">ORDER (<?= esc($img['order']) ?>)</span>
                            • <a href="#" class="image-delete-link" onclick="return confirm('Delete image?')">DELETE</a>
                        </div>
                    </div>
                </li>
                <!-- Modal for editing image -->
                <div class="image-edit-modal" id="image-edit-modal-<?= $img['id'] ?>" style="display:none;">
                    <div class="image-edit-modal-content">
                        <span class="image-edit-modal-close" onclick="closeImageEditModal(<?= $img['id'] ?>)">&times;</span>
                        <div class="image-edit-modal-body">
                            <div class="image-edit-modal-img">
                                <img src="/konst/medium/<?= esc($img['file_name']) ?>" alt="<?= esc($img['title']) ?>" style="max-width:220px;max-height:220px;">
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
                                                    <option value="<?= esc($proj['slug']) ?>"<?= $img['project'] === $proj['slug'] ? ' selected' : '' ?>><?= esc($proj['title']) ?></option>
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
function openImageEditModal(id) {
    document.getElementById('image-edit-modal-' + id).style.display = 'block';
}
function closeImageEditModal(id) {
    document.getElementById('image-edit-modal-' + id).style.display = 'none';
}
</script>
<?= $this->endSection() ?>

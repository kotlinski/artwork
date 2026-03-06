<?= $this->extend('layouts/main') ?>

<?= $this->section('adminContent') ?>
<h2>Image Administration</h2>
<p>Add new, reorder or edit images.</p>
<ul>
  <li><a href='<?= base_url('image/admin') ?>'>Image Admin</a></li>
</ul>

<hr/ class="light admin-divider"/>
<?= view('artwork/artwork_admin') ?>

<div class='contained'>
  <?php foreach ($projects as $project): ?>
    <form method="post" action="<?= base_url('artwork/update/' . $project['id']) ?>" class="project-card"
          id="project-form-<?= $project['slug'] ?>" style="margin-bottom: 20px;">
      <div style="display: flex; gap: 1em; align-items: center;">
        <label>Title: <input type="text" name="title" value="<?= esc($project['title']) ?>" required
                             style="width: 220px;"></label>
        <label>Slug: <input type="text" name="slug" value="<?= esc($project['slug']) ?>" required style="width: 140px;"></label>
      </div>
      <div style="display: flex; gap: 1em; align-items: center; margin-top: 8px;">
        <label>From Year: <input type="number" name="start_year" value="<?= esc($project['start_year']) ?>" min="1900"
                                 max="2100" style="width: 80px;"></label>
        <label>To Year: <input type="number" name="end_year" value="<?= esc($project['end_year']) ?>" min="1900"
                               max="2100" style="width: 80px;"></label>
      </div>
      <div style="display: flex; gap: 1em; align-items: center; margin-top: 8px;">
        <label>Image Left:
          <select name="image_left" style="width: 122px;">
            <option value="">-- Select --</option>
            <?php foreach ($project['images'] as $image): ?>
              <option
                value="<?= esc($image['id']) ?>" <?= ($project['image_left'] == $image['id']) ? 'selected' : '' ?>>
                <?= esc($image['file_id']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Image Mid:
          <select name="image_mid" style="width: 122px;">
            <option value="">-- Select --</option>
            <?php foreach ($project['images'] as $image): ?>
              <option value="<?= esc($image['id']) ?>" <?= ($project['image_mid'] == $image['id']) ? 'selected' : '' ?>>
                <?= esc($image['file_id']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
        <label>Image Right:
          <select name="image_right" style="width: 122px;">
            <option value="">-- Select --</option>
            <?php foreach ($project['images'] as $image): ?>
              <option
                value="<?= esc($image['id']) ?>"
                <?= ($project['image_right'] == $image['id']) ? 'selected' : '' ?>>
                <?= esc($image['file_id']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </label>
      </div>
      <div style="margin-top: 8px;">
        <label>Description:<br>
          <textarea name="description" rows="5"
                    style="width: 100%; max-width: 600px;"><?= esc($project['description']) ?></textarea>
        </label>
      </div>
      <div style="margin-top: 10px; text-align: right;">
        <button type="submit">Update Project</button>
      </div>
    </form>
    <?php if ($project !== end($projects)): ?>
      <hr class="light" style="margin: 16px 0 22px 0;">
    <?php endif; ?>
  <?php endforeach; ?>
</div>
<?= $this->endSection() ?>



<?= $this->section('content') ?>
<div class='contained'>
  <?php foreach ($projects as $project): ?>
    <div class="project-card" id="<?= $project['slug'] ?>" style="margin-bottom: 20px;">
      <?php
      // Build year range string for project
      $startYear = isset($project->start_year) ? $project->start_year : $project['start_year'];
      $endYear = $project->end_year ?? $project['end_year'] ?? null;
      if (!empty($endYear)) {
        $yearRange = esc($startYear) . '–' . esc($endYear);
      } else {
        $yearRange = esc($startYear);
      }
      // Ensure all project links use base-url/slug
      $slug = isset($project->slug) ? $project->slug : ($project['slug'] ?? null);
      $projectUrl = $slug ? base_url($slug) : '#';
      ?>
      <h2>
        <a href="<?= $projectUrl ?>">
          <?= isset($project->title) ? esc($project->title) : esc($project['title']) ?>
          (<?= $yearRange ?>)
        </a>
      </h2>
      <div class="hero-container"
           style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2px; margin-top: 5px; width: 100%;">
        <?php
        foreach ($project['preview'] as $image):
          $image_file_name = $image['file_name'];
          $image_title = $image['title'];
          ?>
          <a href="<?= $projectUrl ?>" style="display: block; width: 100%; height: 280px;">
            <img
              src="<?= base_url('konst/medium/' . $image_file_name) ?>"
              srcset="<?= base_url('konst/medium/' . $image_file_name) ?> 1x,
                  <?= base_url('konst/large/' . $image_file_name) ?> 2x"
              alt="<?= esc($image_title) ?>"
              height="280"
              loading="lazy"
              style="width: 100%; height: 280px; object-fit: cover; display: block;"/>
          </a>
        <?php endforeach; ?>
      </div>
      <p style="margin:7px 0 4px 0; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
        <?= isset($project->description) ? esc($project->description) : esc($project['description']) ?>
      </p>
      <div style="text-align: right;">
        <a href="<?= $projectUrl ?>">read more</a>
      </div>
      <?php if ($project !== end($projects)): ?>
        <hr class="light" style="margin: 16px 0 22px 0;">
      <?php endif; ?>
    </div>

  <?php endforeach; ?>
</div>

<?= $this->endSection() ?>

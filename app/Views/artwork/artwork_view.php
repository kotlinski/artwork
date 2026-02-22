<?= $this->extend('layouts/main') ?>

<?= $this->section('adminContent') ?>
<h2>Image Administration</h2>
<p>Add new, reorder or edit images.</p>
<ul>
  <li><a href='<?= base_url('image/admin') ?>'>Image Admin</a></li>
</ul>

<hr/ class="light admin-divider"/>
<?= view('artwork/artwork_admin') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class='contained'>

  <?php foreach ($projects as $project): ?>
    <div class="project-card" id="<?= $project['slug'] ?>" distyle="margin-bottom: 20px;">
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
        $heroImgs = [
          $project->hero_left ?? $project['hero_left'] ?? null,
          $project->hero_mid ?? $project['hero_mid'] ?? null,
          $project->hero_right ?? $project['hero_right'] ?? null
        ];
        foreach ($heroImgs as $img):
          if (is_array($img) && isset($img['file_name'])) {
            $imgFile = $img['file_name'];
            $imgTitle = $img['title'] ?? '';
          } elseif (is_object($img) && isset($img->file_name)) {
            $imgFile = $img->file_name;
            $imgTitle = $img->title ?? '';
          } elseif (is_string($img)) {
            $imgFile = $img;
            $imgTitle = '';
          } else {
            $imgFile = null;
            $imgTitle = '';
          }
          if ($imgFile):
            ?>
            <a href="<?= $projectUrl ?>" style="display: block; width: 100%; height: 280px;">
              <img
                src="<?= base_url('konst/medium/anne-hamrin-simonsson-' . $imgFile . '.webp') ?>"
                srcset="<?= base_url('konst/medium/anne-hamrin-simonsson-' . $imgFile . '.webp') ?> 1x,
                  <?= base_url('konst/large/anne-hamrin-simonsson-' . $imgFile . '.webp') ?> 2x"
                alt="<?= esc($imgTitle) ?>"
                height="280"
                loading="lazy"
                style="width: 100%; height: 280px; object-fit: cover; display: block;"/>
            </a>
          <?php endif;
        endforeach; ?>
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

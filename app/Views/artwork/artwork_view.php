<?= $this->extend('layouts/main') ?>

<?php if (session()->get('isLoggedIn')): ?>
  <?= $this->section('adminContent') ?>
  <p>Add new images and edit the existing artwork.</p>
  <div class='admin-notice'>
    <button onclick="window.location.href='<?= base_url('image/admin') ?>'">Image Administration</button>
  </div>
  <hr/ class="light admin-divider"/>
  <?= view('artwork/artwork_admin') ?>
  <?= $this->endSection() ?>
<?php endif; ?>

<?= $this->section('content') ?>
<div id="bodyspan" class='contained'>
  <?php if (empty($projects)): ?>
    <p>No projects available yet.</p>
  <?php else: ?>
    <?php foreach ($projects as $project): ?>
      <div class="project-card" style="margin-bottom: 20px;">
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
        <h2 class="aboutHeader">
          <a href="<?= $projectUrl ?>">
            <?= isset($project->title) ? esc($project->title) : esc($project['title']) ?>
            (<?= $yearRange ?>)
          </a>
        </h2>
        <div class="hero-container" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 12px; width: 100%;">
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
        <p style="margin:3px 0; word-break: break-word; overflow-wrap: break-word; max-width: 100%;">
          <?= isset($project->description) ? esc($project->description) : esc($project['description']) ?>
        </p>
        <div style="text-align: right;">
          <a href="<?= $projectUrl ?>">Läs mer
            om <?= isset($project->title) ? esc($project->title) : esc($project['title']) ?> </a>
        </div>
      </div>

    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>

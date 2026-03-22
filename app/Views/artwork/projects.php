<?= $this->extend('layouts/main') ?>

<?= $this->section('adminContent') ?>
<?php if (session()->get('isLoggedIn')): ?>

<h2>Image Administration</h2>
<p>Add new, reorder or edit images.</p>
<ul>
  <li><a href='<?= base_url('image/admin') ?>'>Image Admin</a></li>
</ul>

<hr/ class="light admin-divider"/>
<?= view('artwork/manage-projects') ?>

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

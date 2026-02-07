<?= $this->extend('layouts/main') ?>

<?php if (session()->get('isLoggedIn')): ?>
<?= $this->section('adminContent') ?>
  <?= view('artwork_admin') ?>
  <hr/ class="light admin-divider"/>
<?= $this->endSection() ?>
<?php endif; ?>

<?= $this->section('content') ?>
<h1>Artwork</h1>

<div class="projects-grid contained">
  <?php if (empty($projects)): ?>
    <p>No projects available yet.</p>
  <?php else: ?>
    <?php foreach ($projects as $project): ?>
      <div class="project-card">
        <h2><?= esc($project['title']) ?></h2>
        <?php if ($project['description']): ?>
          <p><?= esc($project['description']) ?></p>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
User Directory
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<h1>My Imported Users</h1>
<ul>
  <?php foreach ($users as $user): ?>
    <li><?= esc($user['username']) ?></li>
  <?php endforeach; ?>
</ul>
<?= $this->endSection() ?>


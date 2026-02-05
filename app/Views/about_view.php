<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= file_get_contents(APPPATH . 'Data/LdJson/about.json') ?>
</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<?php if (session()->get('isLoggedIn')): ?>
  <?= view('partials/markdown_editor', [
    'formAction' => base_url('about/update'),
    'id' => $about['id'],
    'fieldName' => 'about_text',
    'fieldValue' => $about['text'],
    'title' => 'Edit About Info (Markdown)'
  ]) ?>
<?php endif; ?>
<?= $this->endSection() ?>


<?= $this->section('content') ?>
<h1 class="visually-hidden">About</h1>
<div>
  <?= $about_text ?>
</div>
<?= $this->endSection() ?>



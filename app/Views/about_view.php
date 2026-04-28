<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= file_get_contents(APPPATH . 'Data/LdJson/about.json') ?>

</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<?= view('partials/markdown_editor', [
  'formAction' => base_url('about/update'),
  'id' => $about['id'],
  'fieldName' => 'about_text',
  'fieldValue' => $about['text'],
  'editor_title' => 'Edit About Info (Markdown)'
]) ?>
<hr class="light admin-divider"/>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="text">
  <h1>About</h1>
  <!--class="visually-hidden"-->
  <div>
    <?= $about_text ?>
  </div>
</div>
<?= $this->endSection() ?>



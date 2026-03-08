<?= $this->extend('layouts/main') ?>

<?= $this->section('adminContent') ?>
<?php if (session()->get('isLoggedIn')): ?>
  <h2>Image Administration</h2>
  <p>Add new, reorder or edit images.</p>
  <ul>
    <li><a href='<?= base_url('image/admin') ?>'>Image Admin</a></li>
  </ul>

  <?= view('partials/markdown_editor', [
    'formAction' => base_url('project/update'),
    'id' => $project['id'] ?? '',
    'fieldName' => 'text',
    'fieldValue' => $project['text'] ?? '',
    'title' => 'Edit text about ' . ($project['title'] ?? 'Projekt'),
    'fixed_width' => true
  ]) ?>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class='contained'>
  <?php if (isset($error)): ?>
    <p><?= esc($error) ?></p>
  <?php else: ?>
    <h1>
      <?= esc($project['title'] ?? $project->title ?? 'Projekt') ?>
    </h1>
    <hr class="light"/>
    <div class="text">
      <?php
      // Render project text as markdown if available
      $text = $project['text'] ?? $project->text ?? '';
      if (!empty($text)) {
        // Use Parsedown if available, else fallback to nl2br
        if (class_exists('Parsedown')) {
          $parsedown = new Parsedown();
          echo $parsedown->text($text);
        } else {
          echo nl2br(esc($text));
        }
      }
      ?>
    </div>
    <?php if (!empty($images) && is_array($images)): ?>
      <div style="display: grid;grid-template-columns: repeat(3, 122px);gap: 7px;width: 380px;margin: 12px auto;">
        <?php foreach ($images as $img): ?>
          <?php if (!isset($img['file_name'])) continue; ?>
          <a href="<?= base_url(($project['slug'] ?? '') . '/' . ($img['file_id'] ?? '')) ?>" style="display: block;">
            <img
              src="<?= base_url('konst/thumb/' . $img['file_name']) ?>"
              srcset="<?= base_url('konst/thumb/' . $img['file_name']) ?> 1x,
                <?= base_url('konst/medium/' . $img['file_name']) ?> 2x"
              alt="<?= esc($img['title'] ?? '') ?>"
              loading="lazy"
              style="
              display: block;
              width: 122px;
              height: 122px;
              object-fit: cover;
              object-position: center;
              border: 0;
              "
            />
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
    <div class="back-to-overview-row"
         style="display: flex; justify-content: space-between; align-items: center; margin: 1em 0;">
      <a href="<?= base_url('artwork') . '#' . ($project['slug'] ?? '') ?>">back to artworks</a>
      <?php if (!empty($next_project_slug) && !empty($next_project_title)): ?>
        <a href="<?= base_url('/' . $next_project_slug) ?>"
           class="btn btn-primary d-inline-flex align-items-center">next: <?= esc($next_project_title) ?>
        </a>
      <?php else: ?>
        <span></span>
      <?php endif; ?>
    </div>
    <hr class="light"/>
    <div class="project-news">
      <!-- TODO: Render news connected to the project when available -->
      <em>Nyheter kopplade till projektet kommer här.</em>
    </div>
    <hr class="light"/>
  <?php endif; ?>
</div>
<script src="/js/marked.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var textarea = document.querySelector('textarea.admin-editor');
  if (!textarea) return;
  var previewId = textarea.id + '-preview';
  var preview = document.getElementById(previewId);
  if (!preview) return;

  function updatePreview() {
    if (window.marked) {
      preview.innerHTML = window.marked(textarea.value);
    } else {
      preview.textContent = textarea.value;
    }
  }
  textarea.addEventListener('input', updatePreview);
  updatePreview(); // Initial render
});
</script>
<?= $this->endSection() ?>

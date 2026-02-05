<?php
/**
 * @var string $formAction - The form submission URL
 * @var int $id - The record ID
 * @var string $fieldName - The textarea field name
 * @var string $fieldValue - The current markdown content
 * @var string $title - The editor title (optional)
 */
$title = $title ?? 'Edit Content (Markdown)';
$editorId = $editorId ?? 'md-editor-' . uniqid();
?>
<section class="admin-editor">
  <h2><?= esc($title) ?></h2>
  <form action="<?= $formAction ?>" method="post">
    <input type="hidden" name="id" value="<?= esc($id) ?>">
    <div class="md-toolbar">
      <button type="button" onclick="mdWrap('<?= $editorId ?>', '**', '**')" title="Bold">B</button>
      <button type="button" onclick="mdWrap('<?= $editorId ?>', '*', '*')" title="Italic"><em>I</em></button>
      <button type="button" onclick="mdInsert('<?= $editorId ?>', '## ')" title="Heading">H</button>
      <button type="button" onclick="mdWrap('<?= $editorId ?>', '[', '](url)')" title="Link">🔗</button>
      <button type="button" onclick="mdInsert('<?= $editorId ?>', '- ')" title="Bullet List">• List</button>
      <button type="button" onclick="mdInsert('<?= $editorId ?>', '1. ')" title="Numbered List">1. List</button>
      <button type="button" onclick="mdInsert('<?= $editorId ?>', '  \n')" title="Line Break">↵</button>
      <button type="button" onclick="mdWrap('<?= $editorId ?>', '`', '`')" title="Code">&lt;/&gt;</button>
    </div>
    <textarea id="<?= $editorId ?>" name="<?= esc($fieldName) ?>" class="admin-editor"><?= esc($fieldValue) ?></textarea>
    <div>💡 Tip: "↵" adds a soft line break (2 spaces + enter). Use a blank line for a new paragraph.</div>
    <div class="form-actions">
      <button type="submit">Save</button>
    </div>
  </form>
</section>


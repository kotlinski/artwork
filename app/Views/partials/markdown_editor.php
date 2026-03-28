<?php
/**
 * @var string $formAction - The form submission URL
 * @var int $id - The record ID
 * @var string $fieldName - The textarea field name
 * @var string $fieldValue - The current markdown content
 * @var string $title - The editor title (optional)
 * @var string $fixed_width - If the text is allowed to overflow or not.
 * @var array|null $titleField - Optional title field: ['name' => '...', 'value' => '...', 'label' => '...']
 */
$title = $title ?? 'Edit Content (Markdown)';
$editorId = $editorId ?? 'md-editor-' . uniqid();
$fixed_width = $fixed_width ?? false;
$titleField = $titleField ?? null;
?>
<!-- Modal for preview -->
<div id="<?= $editorId ?>-preview-modal" class="preview-modal"
     style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.5);z-index:2000;align-items:center;justify-content:center;">
  <div class="preview-modal-content"
       style="background:#fff;padding:24px 18px 18px 18px;border-radius:8px;max-width:416px;width:90vw;max-height:80vh;overflow-y:auto;box-shadow:0 4px 32px rgba(0,0,0,0.18);position:relative;">
    <button type="button" id="<?= $editorId ?>-preview-close"
            style="position:absolute;top:8px;right:12px;font-size:22px;background:none;border:none;cursor:pointer;">
      &times;
    </button>
    <div id="<?= $editorId ?>-preview-content" class="<?= $fixed_width ? 'contained':'' ?>"
         style="word-break:break-word;overflow-wrap:anywhere;"></div>
  </div>
</div>

<section class="admin-editor contained">
  <div>
    <h2><?= esc($title) ?></h2>
  </div>
  <form class="contained" action="<?= $formAction ?>" method="post">
    <input type="hidden" name="id" value="<?= esc($id) ?>">
    <?php if ($titleField): ?>
    <label class="md-title-field">
      <?= esc($titleField['label'] ?? 'Title') ?>
      <input type="text" name="<?= esc($titleField['name']) ?>" value="<?= esc($titleField['value'] ?? '') ?>">
    </label>
    <?php endif; ?>
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
    <textarea id="<?= $editorId ?>" name="<?= esc($fieldName) ?>"
              class="admin-editor<?= $fixed_width ? ' fixed-width' : '' ?>"><?= esc($fieldValue) ?></textarea>
    <div style='padding: 0 4px'>
      <div>💡 Tip: "↵" adds a "soft line break"</div>
      <div>Soft line break: 2 spaces + new line</div>
      <div>New Paragraph: Use a blank line</div>
      <div style='margin-top:3px'>To add a link: Type the text you want to display in [square brackets], then
        immediately after, put the web address in (parentheses).<br>Example: <code>[My
          Website](https://www.annesimonsson.se)</code></div>
      <div class="form-actions">
        <button
          id="<?= $editorId ?>-preview-btn"
          type="button"
        >Preview</button>
        <button type="submit">Save</button>
      </div>
    </div>

  </form>
</section>


<script src="/js/marked.min.js"></script>
<script>
  document.addEventListener('DOMContentLoaded', function () {
    var editorId = '<?= $editorId ?>';
    var textarea = document.getElementById(editorId);
    var previewBtn = document.getElementById(editorId + '-preview-btn');
    var previewModal = document.getElementById(editorId + '-preview-modal');
    var previewContent = document.getElementById(editorId + '-preview-content');
    var previewClose = document.getElementById(editorId + '-preview-close');

    if (previewBtn && previewModal && previewContent && textarea) {
      previewBtn.addEventListener('click', function () {
        if (window.marked) {
          previewContent.innerHTML = window.marked(textarea.value);
        } else {
          previewContent.textContent = textarea.value;
        }
        previewModal.style.display = 'flex';
      });
      previewClose.addEventListener('click', function () {
        previewModal.style.display = 'none';
      });
      previewModal.addEventListener('click', function (e) {
        if (e.target === previewModal) {
          previewModal.style.display = 'none';
        }
      });
      document.addEventListener('keydown', function (e) {
        if (previewModal.style.display === 'flex' && (e.key === 'Escape' || e.key === 'Esc')) {
          previewModal.style.display = 'none';
        }
      });
    }
  });
</script>

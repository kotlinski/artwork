<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div id="bodyspan" class='contained'>
  <?php if (isset($error)): ?>
    <p><?= esc($error) ?></p>
  <?php else: ?>
    <h2 class="aboutHeader">
      <?= esc($project['title'] ?? $project->title ?? 'Projekt') ?>
      <?php if (!empty($project['start_year'])): ?>
        (<?= esc($project['start_year']) ?><?php if (!empty($project['end_year'])): ?>–<?= esc($project['end_year']) ?><?php endif; ?>)
      <?php endif; ?>
    </h2>
    <hr class="light" />
    <div class="project-text">
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
    <hr class="light" />
    <?php if (!empty($images)): ?>
      <table class="tableview">
        <tr>
          <?php foreach ($images as $i => $img): ?>
            <?php if ($i > 0 && $i % 3 == 0): ?>
              </tr><tr>
            <?php endif; ?>
            <td valign="middle" align="center" style="padding-bottom:10px;margin:0;min-width: 120px;">
              <a href="<?= base_url(($project['slug'] ?? '') . '/' . $img['file_id']) ?>">
                <img
                  src="<?= base_url('konst/thumb/' . $img['file_name']) ?>"
                  alt="<?= esc($img['title'] ?? '') ?>"
                  style="padding:0;margin:0;border:0;"
                />
              </a>
            </td>
          <?php endforeach; ?>
        </tr>
      </table>
    <?php endif; ?>
    <hr class="light" />
    <div class="project-news">
      <!-- TODO: Render news connected to the project when available -->
      <em>Nyheter kopplade till projektet kommer här.</em>
    </div>
    <hr class="light" />
  <?php endif; ?>
</div>
<?= $this->endSection() ?>

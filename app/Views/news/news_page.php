<?= $this->extend('layouts/main') ?>

<?= $this->section('ldjson') ?>
<script type="application/ld+json">
<?= file_get_contents(APPPATH . 'Data/LdJson/about.json') ?>


</script>
<?= $this->endSection() ?>

<?= $this->section('adminContent') ?>
<div class="contained news-admin">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert error"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <button type="button" id="open-news-create-modal" class="button" style="margin-bottom:16px;">Add News</button>

  <!-- Edit News Modal -->
  <div id="news-edit-modal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.5);z-index:3000;align-items:center;justify-content:center;">
    <div class="modal-content" style="background:#fff;padding:24px 18px 18px 18px;border-radius:8px;max-width:520px;width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 4px 32px rgba(0,0,0,0.18);position:relative;">
      <button type="button" id="close-news-edit-modal" style="position:absolute;top:8px;right:12px;font-size:22px;background:none;border:none;cursor:pointer;">&times;</button>
      <div class="news-edit-form" id="news-admin-edit-modal-form">
        <?php
        // Use the same partial as the create modal for consistent style
        $editExtraFields = [
          [
            'type'  => 'hidden',
            'name'  => 'slug',
            'label' => 'Slug',
            'value' => '', // Will be filled by JS
          ],
          [
            'type'         => 'select',
            'name'         => 'project_id',
            'label'        => 'Project',
            'value'        => '', // Will be filled by JS
            'empty_option' => '— No project —',
            'options'      => array_map(fn($p) => [
              'value' => $p['id'],
              'label' => $p['title'],
            ], $projects ?? []),
          ],
        ];
        ?>
        <?= view('partials/markdown_editor', [
          'formAction'   => base_url('news/update'),
          'id'           => '', // Will be filled by JS
          'fieldName'    => 'content',
          'fieldValue'   => '', // Will be filled by JS
          'editor_title' => 'Edit Article',
          'editorId'     => 'news-md-editor-edit',
          'fixed_width'  => true,
          'titleField'   => [
            'name'  => 'title',
            'label' => 'Title',
            'value' => '', // Will be filled by JS
          ],
          'extraFields'  => $editExtraFields,
        ]) ?>
      </div>
    </div>
  </div>

  <!-- News Create Modal -->
  <div id="news-create-modal" class="modal" style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;background:rgba(40,40,40,0.5);z-index:3000;align-items:center;justify-content:center;">
    <div class="modal-content" style="background:#fff;padding:24px 18px 18px 18px;border-radius:8px;max-width:520px;width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 4px 32px rgba(0,0,0,0.18);position:relative;">
      <button type="button" id="close-news-create-modal" style="position:absolute;top:8px;right:12px;font-size:22px;background:none;border:none;cursor:pointer;">&times;</button>
      <div class="news-edit-form" id="news-admin-new-modal-form">
        <?php if (session()->getFlashdata('create_errors')): ?>
          <div class="alert error" style="margin: 8px 0 0 0;">
            <?php foreach ((array) session()->getFlashdata('create_errors') as $err): ?>
              <div><?= esc($err) ?></div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
        <?php
        // Patch the extraFields array to make slug hidden
        $extraFields = [
          [
            'type'  => 'hidden',
            'name'  => 'slug',
            'label' => 'Slug',
            'value' => session()->getFlashdata('create_slug') ?? '',
          ],
          [
            'type'         => 'select',
            'name'         => 'project_id',
            'label'        => 'Project',
            'value'        => '',
            'empty_option' => '— No project —',
            'options'      => array_map(fn($p) => [
              'value' => $p['id'],
              'label' => $p['title'],
            ], $projects ?? []),
          ],
        ];
        ?>
        <?= view('partials/markdown_editor', [
          'formAction'   => base_url('news/store'),
          'id'           => 0,
          'fieldName'    => 'content',
          'fieldValue'   => session()->getFlashdata('create_content') ?? '',
          'editor_title' => 'New Article',
          'editorId'     => 'news-md-editor-new',
          'fixed_width'  => true,
          'titleField'   => [
            'name'  => 'title',
            'label' => 'Title',
            'value' => session()->getFlashdata('create_title') ?? '',
          ],
          'extraFields'  => $extraFields,
        ]) ?>
      </div>
    </div>
  </div>

  <hr class='light'/>


  <h2>News Administration</h2>
  <p>Expand a news title to update its markdown content.</p>

  <?php foreach (($news_items ?? []) as $item): ?>
    <?php $newsId = (int) ($item['id'] ?? 0); ?>
    <?php
    $extraFields = [
      [
        'type'         => 'select',
        'name'         => 'project_id',
        'label'        => 'Project',
        'value'        => $item['project_id'] ?? '',
        'empty_option' => '— No project —',
        'options'      => array_map(fn($p) => [
          'value' => $p['id'],
          'label' => $p['title'],
        ], $projects ?? []),
      ],
      [
        'type'  => 'hidden',
        'name'  => 'slug',
        'label' => 'Slug',
        'value' => $item['slug'] ?? '',
      ],
    ];
    ?>
    <div class="news-edit-expandable" id="news-admin-item-<?= $newsId ?>" data-news-id="<?= $newsId ?>">
      <button type="button"
              class="news-expand-toggle"
              aria-expanded="false"
              aria-controls="news-form-<?= $newsId ?>">
        <span class="news-chevron">▶</span>
        <span><?= esc($item['title'] ?? 'Untitled news item') ?></span>
      </button>
      <div class="news-edit-form" id="news-form-<?= $newsId ?>" style="display:none;">
        <?= view('partials/markdown_editor', [
          'formAction' => base_url('news/update'),
          'id' => $newsId,
          'fieldName' => 'content',
          'fieldValue' => $item['content'] ?? '',
          'editor_title' => '',
          'editorId' => 'news-md-editor-' . $newsId,
          'fixed_width' => true,
          'titleField' => [
            'name'  => 'title',
            'label' => 'Title',
            'value' => $item['title'] ?? '',
          ],
          'extraFields' => $extraFields,
        ]) ?>
      </div>
    </div>
  <?php endforeach; ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const expandToggles = document.querySelectorAll('.news-expand-toggle');

    expandToggles.forEach(function (toggle) {
      toggle.addEventListener('click', function () {
        const parent = toggle.closest('.news-edit-expandable');
        const form = parent.querySelector('.news-edit-form');
        const chevron = toggle.querySelector('.news-chevron');
        const isExpanded = toggle.getAttribute('aria-expanded') === 'true';

        document.querySelectorAll('.news-edit-form').forEach(function (section) {
          section.style.display = 'none';
        });
        document.querySelectorAll('.news-expand-toggle').forEach(function (button) {
          button.setAttribute('aria-expanded', 'false');
          const icon = button.querySelector('.news-chevron');
          if (icon) icon.style.transform = '';
        });

        if (!isExpanded) {
          form.style.display = 'block';
          toggle.setAttribute('aria-expanded', 'true');
          if (chevron) chevron.style.transform = 'rotate(90deg)';
        }
      });

      toggle.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
          event.preventDefault();
          toggle.click();
        }
      });
    });

    // Re-expand the item that was just saved, based on URL hash
    const hash = window.location.hash;
    if (hash && hash.startsWith('#news-admin-item-')) {
      const expandable = document.querySelector(hash);
      if (expandable) {
        const toggle = expandable.querySelector('.news-expand-toggle');
        if (toggle) toggle.click();
      }
    }

    // Auto-generate slug from title in the New Article form
    function generateSlug(str) {
      return str.toLowerCase()
        .replace(/[åä]/g, 'a').replace(/ö/g, 'o')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
        .replace(/--+/g, '-');
    }
    // New Article form
    const createTitleInput = document.querySelector('#news-form-new input[name="title"]');
    const createSlugInput  = document.querySelector('#news-form-new input[name="slug"]');
    if (createTitleInput && createSlugInput) {
      createTitleInput.addEventListener('input', function () {
        createSlugInput.value = generateSlug(this.value);
      });
      // Initial fill
      createSlugInput.value = generateSlug(createTitleInput.value);
    }
    // Edit forms
    document.querySelectorAll('.news-edit-form').forEach(function(form) {
      const titleInput = form.querySelector('input[name="title"]');
      const slugInput = form.querySelector('input[name="slug"]');
      if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
          slugInput.value = generateSlug(this.value);
        });
        // Initial fill
        slugInput.value = generateSlug(titleInput.value);
      } else {
        console.warn('Missing title or slug input in form:', form);
      }
    });

    // Modal logic for news create
    const openNewsCreateBtn = document.getElementById('open-news-create-modal');
    const newsCreateModal = document.getElementById('news-create-modal');
    const closeNewsCreateBtn = document.getElementById('close-news-create-modal');
    if (openNewsCreateBtn && newsCreateModal && closeNewsCreateBtn) {
      openNewsCreateBtn.addEventListener('click', function() {
        newsCreateModal.style.display = 'flex';
        // Focus the title field
        setTimeout(function() {
          const titleInput = newsCreateModal.querySelector('input[name="title"]');
          if (titleInput) titleInput.focus();
        }, 100);
      });
      closeNewsCreateBtn.addEventListener('click', function() {
        newsCreateModal.style.display = 'none';
      });
      newsCreateModal.addEventListener('click', function(e) {
        if (e.target === newsCreateModal) newsCreateModal.style.display = 'none';
      });
      document.addEventListener('keydown', function(e) {
        if (newsCreateModal.style.display === 'flex' && e.key === 'Escape') {
          newsCreateModal.style.display = 'none';
        }
      });
    }
    // If there were create errors, open the modal automatically
    <?php if (session()->getFlashdata('create_errors')): ?>
    newsCreateModal.style.display = 'flex';
    <?php endif; ?>
  });
</script>
<?= $this->endSection() ?>


<?= $this->section('content') ?>
<h1 class="visually-hidden">News</h1>

<?php $news_items = $news_items ?? []; ?>
<div class='contained'>
  <?php foreach ($news_items as $item): ?>
    <article id="<?= $item['slug'] ?>" class="news-item" data-project-id="<?= esc($item['project_id'] ?? '') ?>" data-slug="<?= esc($item['slug'] ?? '') ?>" data-content="<?= htmlspecialchars($item['content'] ?? '', ENT_QUOTES) ?>">
      <h2><?= esc($item['title']) ?></h2>
      <div class="body">
        <?= $item['content_parsed'] ?: nl2br(esc($item['content'] ?? '')) ?>
      </div>
      <?php
      $session = service('session');
      $isAdmin = $session->get('isLoggedIn');
      ?>
      <?php if ($isAdmin): ?>
        <button type="button" class="edit-news-button" data-news-id="<?= $item['id'] ?>">Edit</button>
      <?php endif; ?>
      <hr>
    </article>
  <?php endforeach; ?>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.edit-news-button');
    const editModal = document.getElementById('news-edit-modal');
    const closeEditModal = document.getElementById('close-news-edit-modal');
    const editForm = document.getElementById('news-edit-form');
    // Use correct IDs generated by markdown_editor partial
    const editNewsId = editForm ? editForm.querySelector('input[name="id"]') : null;
    const editNewsTitle = document.getElementById('news-md-editor-edit-title');
    const editNewsContent = document.getElementById('news-md-editor-edit-content');
    const editNewsProject = document.getElementById('news-md-editor-edit-project_id');
    const editNewsSlug = document.getElementById('news-md-editor-edit-slug');

    editButtons.forEach(button => {
      button.addEventListener('click', function () {
        const newsId = this.getAttribute('data-news-id');
        const newsItem = this.closest('.news-item');
        const newsTitle = newsItem.querySelector('h2').textContent;
        // Get the raw markdown content from data-content attribute
        let newsContent = newsItem.dataset.content || newsItem.querySelector('.body').textContent;
        // Find the project id and slug from a data attribute or hidden field if available
        let projectId = '';
        let slug = '';
        if (newsItem.dataset.projectId) projectId = newsItem.dataset.projectId;
        if (newsItem.dataset.slug) slug = newsItem.dataset.slug;
        // fallback: try to find hidden fields if present
        if (!slug) {
          const hiddenSlug = newsItem.querySelector('input[name="slug"]');
          if (hiddenSlug) slug = hiddenSlug.value;
        }
        if (!projectId) {
          const hiddenProject = newsItem.querySelector('input[name="project_id"]');
          if (hiddenProject) projectId = hiddenProject.value;
        }

        if (editNewsId) editNewsId.value = newsId;
        if (editNewsTitle) editNewsTitle.value = newsTitle;
        if (editNewsContent) {
          editNewsContent.value = newsContent;
          // Trigger input event for markdown editor JS
          editNewsContent.dispatchEvent(new Event('input', { bubbles: true }));
        }
        if (editNewsSlug) editNewsSlug.value = slug;
        if (editNewsProject) editNewsProject.value = projectId;

        if (editModal) editModal.style.display = 'flex';
        // Focus the title field
        setTimeout(function() {
          if (editNewsTitle) editNewsTitle.focus();
        }, 100);
      });
    });

    if (closeEditModal) {
      closeEditModal.addEventListener('click', function () {
        editModal.style.display = 'none';
      });
    }

    if (editModal) {
      editModal.addEventListener('click', function (e) {
        if (e.target === editModal) {
          editModal.style.display = 'none';
        }
      });
    }

    document.addEventListener('keydown', function (e) {
      if (editModal && editModal.style.display === 'flex' && e.key === 'Escape') {
        editModal.style.display = 'none';
      }
    });

    if (editForm) {
      editForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(editForm);
        fetch(editForm.action, {
          method: 'POST',
          body: formData,
        })
          .then(response => {
            if (!response.ok) {
              throw new Error('Network response was not ok');
            }
            return response.json();
          })
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert('Failed to update news item.');
            }
          })
          .catch(error => {
            console.error('There was a problem with the fetch operation:', error);
          });
      });
    }
  });
</script>
<?= $this->endSection() ?>


<script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@graph": [
      {
        "@type": "Blog",
        "@id": "<?= current_url() ?>#news-feed",
      "name": "Anne Simonsson - News & Updates",
      "blogPost": [
  <?php foreach ($news_items as $index => $item): ?>
        {
          "@type": "BlogPosting",
          "@id": "<?= current_url() ?>#<?= $item['slug'] ?>",
          "headline": "<?= esc($item['title']) ?>",
          "datePublished": "<?= date('c', strtotime($item['created_at'])) ?>",
          "description": "<?= esc($item['excerpt']) ?>",
          "image": "<?= base_url($item['main_image'] ?? 'assets/img/fallback-art.jpg') ?>",
          "author": {
            "@type": "Person",
            "name": "Anne Hamrin Simonsson",
            "url": "<?= base_url('about') ?>"
          }
          <?php if ($item['event_start_date']): ?>,
          "about": {
            "@type": "Event",
            "name": "<?= esc($item['title']) ?>",
            "startDate": "<?= $item['event_start_date'] ?>",
            "endDate": "<?= $item['event_end_date'] ?? $item['event_start_date'] ?>",
            "location": {
              "@type": "Place",
              "name": "<?= esc($item['event_location']) ?>"
            }
          }
          <?php endif; ?>
        }<?= ($index < count($news_items) - 1) ? ',' : '' ?>
  <?php endforeach; ?>
  ]
}
]
}
</script>


<div class="manage-projects contained">
  <h2>Manage Projects</h2>

  <div class="projects-table-wrapper">
    <table class="projects-table">
      <thead>
      <tr>
        <th>Order</th>
        <th>Title</th>
        <th>Slug</th>
        <th colspan="2">Actions</th>
      </tr>
      </thead>
      <tbody>
      <?php foreach ($projects as $index => $project): ?>
        <tr>
          <td class="order-controls">
            <?php if ($index > 0): ?>
              <a href="/artwork/move-up/<?= $project['id'] ?>" class="order-btn" title="Move up">▲</a>
            <?php else: ?>
              <span class="order-btn disabled">▲</span>
            <?php endif; ?>
            <?php if ($index < count($projects) - 1): ?>
              <a href="/artwork/move-down/<?= $project['id'] ?>" class="order-btn" title="Move down">▼</a>
            <?php else: ?>
              <span class="order-btn disabled">▼</span>
            <?php endif; ?>
          </td>
          <td><?= esc($project['title']) ?></td>
          <td><?= esc($project['slug']) ?></td>
          <td>
            <a href="/<?= $project['slug'] ?>">edit</a></td>
          <td>
            <a href="/artwork/delete/<?= $project['id'] ?>" class="delete-link"
               onclick="return confirm('Are you sure you want to delete this project?')">delete</a>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <?php if (session()->has('success')): ?>
    <div class="alert success"><?= session('success') ?></div>
  <?php endif; ?>

  <?php if (session()->has('errors')): ?>
    <div class="alert error">
      <?php foreach (session('errors') as $error): ?>
        <p style="color: red"><?= esc($error) ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <h1>Add New Project</h1>
  <div style="width:100%; display:flex; justify-content:center;">
    <div style="width:50%; min-width:180px; max-width:350px;">
      <form action="/artwork/store" method="post" class="new-project-form">
        <?= csrf_field() ?>
        <div>
          <label for="title">Title</label>
          <input type="text" name="title" id="title" required value="<?= old('title') ?>">
        </div>
        <div>
          <label for="slug">Slug</label>
          <input type="text" name="slug" id="slug" required value="<?= old('slug') ?>">
        </div>
        <div class="form-actions">
          <button type="submit" class="button">Add new Project</button>
        </div>
      </form>
    </div>
  </div>
  <hr class='light'
</div>


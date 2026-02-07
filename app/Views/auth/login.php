<?= $this->extend('layouts/main') ?>
<?= $this->section('content') ?>
<div style="justify-items: center;padding-top: 20px">
  <div style="width: 50%;">
    <form class="login-form" action="<?= base_url('login/auth') ?>" method="post"
          style="display:flex; flex-direction:column; gap:10px;">
      <div style="display:flex; flex-direction:column;">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" required>
      </div>
      <div style="display:flex; flex-direction:column;">
        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" style="margin-top: 10px; align-self: center; width: auto; min-width: 100px;">Log in</button>
    </form>
  </div>
</div>
<?= $this->endSection() ?>

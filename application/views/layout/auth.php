<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <meta name="robots" content="noindex, nofollow, noarchive">
  <meta name="googlebot" content="noindex, nofollow, noarchive">
  <title>Masuk - <?= html_escape($this->config->item('nama_instansi')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
</head>
<body class="auth-page" style="--login-bg-image: url('<?= html_escape($this->config->item('login_background_url')) ?>');">
  <main class="auth-card">
    <div class="text-center mb-4">
      <img class="auth-logo"
           src="<?= html_escape($this->config->item('logo_url')) ?>"
           alt="Logo Sekretariat DPRD Kabupaten Bangka">
      <h1 class="h4 mt-3 mb-1">SIPEDE</h1>
      <p class="text-muted mb-0">Sekretariat DPRD Kabupaten Bangka</p>
    </div>

    <?php if($this->session->flashdata('login_error')): ?>
      <div class="alert alert-danger" role="alert">
        <?= html_escape($this->session->flashdata('login_error')) ?>
      </div>
    <?php endif; ?>
    <div id="loginError" class="alert alert-danger d-none" role="alert"></div>

    <form id="loginForm" action="<?= site_url('auth/login') ?>" method="post">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input id="username" class="form-control form-control-lg" name="username" required autocomplete="username" autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <div class="input-group input-group-lg">
          <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
          <button class="btn btn-outline-secondary" type="button" id="togglePassword" aria-label="Tampilkan password" title="Tampilkan password">
            <svg id="passwordEye" aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </button>
        </div>
      </div>
      <button class="btn btn-primary btn-lg w-100" type="submit">Masuk</button>
    </form>
  </main>
  <script>
  (function () {
    var form = document.getElementById('loginForm');
    var errorBox = document.getElementById('loginError');
    var password = document.getElementById('password');
    var togglePassword = document.getElementById('togglePassword');
    if (!form) return;
    if (togglePassword && password) {
      togglePassword.addEventListener('click', function () {
        var visible = password.type === 'text';
        password.type = visible ? 'password' : 'text';
        togglePassword.setAttribute('aria-label', visible ? 'Tampilkan password' : 'Sembunyikan password');
        togglePassword.setAttribute('title', visible ? 'Tampilkan password' : 'Sembunyikan password');
      });
    }
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      var button = form.querySelector('button[type="submit"]');
      if (button) button.disabled = true;
      fetch(form.action, {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json'
        },
        body: new URLSearchParams(new FormData(form))
      }).then(function (response) {
        return response.json().then(function (data) {
          return { ok: response.ok, data: data };
        });
      }).then(function (result) {
        var data = result.data || {};
        if (data.status && data.data && data.data.redirect) {
          window.location.replace(data.data.redirect);
          return;
        }
        throw new Error(data.message || 'Username atau password salah.');
      }).catch(function (error) {
        errorBox.textContent = error.message || 'Login gagal.';
        errorBox.classList.remove('d-none');
        if (button) button.disabled = false;
      });
    });
  }());
  </script>
</body>
</html>

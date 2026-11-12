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

    <form action="<?= site_url('auth/login') ?>" method="post">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
      <div class="mb-3">
        <label class="form-label" for="username">Username</label>
        <input id="username" class="form-control form-control-lg" name="username" required autocomplete="username" autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label" for="password">Password</label>
        <input id="password" type="password" class="form-control form-control-lg" name="password" required autocomplete="current-password">
      </div>
      <button class="btn btn-primary btn-lg w-100" type="submit">Masuk</button>
    </form>
  </main>
</body>
</html>

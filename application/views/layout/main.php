<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= html_escape($title) ?> - <?= html_escape($this->config->item('nama_instansi')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/2.1.8/css/dataTables.bootstrap5.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
  <link href="<?= base_url('assets/css/app.css') ?>" rel="stylesheet">
  <!-- Global Libraries & Config dimuat di head agar inline <script> di dalam view selalu aman -->
  <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.datatables.net/2.1.8/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/2.1.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
  <script>
    window.APP = {
      baseUrl: '<?= base_url() ?>',
      csrfName: '<?= $this->security->get_csrf_token_name() ?>',
      csrfHash: '<?= $this->security->get_csrf_hash() ?>'
    };
  </script>
  <script src="<?= base_url('assets/js/app.js') ?>"></script>
</head>
<body>
<div class="app-shell">
  <!-- Sidebar -->
  <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar">
    <div class="offcanvas-header d-lg-none p-3 border-bottom border-secondary border-opacity-25">
      <span class="fw-bold text-white">Menu Navigasi</span>
      <button class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="sidebar-inner">
      <!-- Brand Logo -->
      <a class="brand" href="<?= site_url('dashboard') ?>">
        <img class="brand-logo"
             src="<?= html_escape($this->config->item('logo_url')) ?>"
             alt="Logo Sekretariat DPRD Kabupaten Bangka">
        <div>
          <span class="brand-title">SIPEDE</span>
          <span class="brand-subtitle">Sekretariat DPRD Kabupaten Bangka</span>
        </div>
      </a>

      <!-- Navigation Menu -->
      <nav class="nav flex-column sidebar-nav mt-3">
        <?php 
          $menuNo = 1;
          $cClass = $this->router->fetch_class();
          $cMethod = $this->router->fetch_method();
        ?>

        <!-- MENU UTAMA -->
        <?php if(can('dashboard.view_all') || can('dashboard.view_own')): ?>
        <a class="nav-link <?= ($cClass === 'dashboard') ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-speedometer2"></i>
          <span class="menu-title">Dashboard</span>
        </a>
        <?php endif; ?>

        <?php if(can('kegiatan.view_all') || can('kegiatan.view_own')): ?>
        <a class="nav-link <?= ($cClass === 'kegiatan' && $cMethod !== 'antrian') ? 'active' : '' ?>" href="<?= site_url('kegiatan') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-calendar2-event"></i>
          <span class="menu-title">Kegiatan</span>
        </a>
        <?php endif; ?>

        <?php if(can('kegiatan.verify')): ?>
        <a class="nav-link <?= ($cClass === 'kegiatan' && $cMethod === 'antrian') ? 'active' : '' ?>" href="<?= site_url('kegiatan/antrian') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-check2-square"></i>
          <span class="menu-title">Antrian Verifikasi</span>
        </a>
        <?php endif; ?>

        <?php if(can('kegiatan.view_all') || can('kegiatan.view_own') || can('laporan.view_all') || can('laporan.view_own')): ?>
        <a class="nav-link <?= ($cClass === 'rekapitulasi') ? 'active' : '' ?>" href="<?= site_url('rekapitulasi') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-receipt-cutoff"></i>
          <span class="menu-title">Rekapitulasi</span>
        </a>
        <?php endif; ?>

        <?php if(can('laporan.view_all') || can('laporan.view_own')): ?>
        <a class="nav-link <?= ($cClass === 'laporan') ? 'active' : '' ?>" href="<?= site_url('laporan') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-file-earmark-bar-graph"></i>
          <span class="menu-title">Laporan</span>
        </a>
        <?php endif; ?>

        <!-- MASTER DATA -->
        <?php if(can('tahun.view') || can('penerima.view') || can('jenis_biaya.view')): ?>
        <div class="nav-section">MASTER DATA</div>
        <?php endif; ?>

        <?php if(can('penerima.view')): ?>
        <a class="nav-link <?= ($cClass === 'penerima') ? 'active' : '' ?>" href="<?= site_url('penerima') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-people"></i>
          <span class="menu-title">Penerima</span>
        </a>
        <?php endif; ?>

        <?php if(can('tahun.view')): ?>
        <a class="nav-link <?= ($cClass === 'tahun') ? 'active' : '' ?>" href="<?= site_url('tahun') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-calendar3"></i>
          <span class="menu-title">Tahun Anggaran</span>
        </a>
        <a class="nav-link <?= ($cClass === 'pos') ? 'active' : '' ?>" href="<?= site_url('pos/index/' . encrypt_id($this->session->userdata('tahun_aktif_id'))) ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-grid-3x3-gap"></i>
          <span class="menu-title">Pos Anggaran</span>
        </a>
        <?php endif; ?>

        <?php if(can('jenis_biaya.view')): ?>
        <a class="nav-link <?= ($cClass === 'jenis_biaya') ? 'active' : '' ?>" href="<?= site_url('jenis_biaya') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-tags"></i>
          <span class="menu-title">Jenis Biaya</span>
        </a>
        <?php endif; ?>

        <!-- PENGATURAN & SISTEM -->
        <?php if(can('user.manage') || can('role.manage') || can('audit.view')): ?>
        <div class="nav-section">PENGATURAN</div>
        <?php endif; ?>

        <?php if(can('user.manage')): ?>
        <a class="nav-link <?= ($cClass === 'user') ? 'active' : '' ?>" href="<?= site_url('user') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-person-gear"></i>
          <span class="menu-title">Pengguna</span>
        </a>
        <?php endif; ?>

        <?php if(can('role.manage')): ?>
        <a class="nav-link <?= ($cClass === 'role') ? 'active' : '' ?>" href="<?= site_url('role') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-shield-lock"></i>
          <span class="menu-title">Role & Izin</span>
        </a>
        <?php endif; ?>

        <?php if(can('user.manage') || can('role.manage') || $this->rbac->is_super_admin()): ?>
        <a class="nav-link <?= ($cClass === 'pengaturan') ? 'active' : '' ?>" href="<?= site_url('pengaturan/dokumen') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-file-earmark-person"></i>
          <span class="menu-title">KOP &amp; Penandatangan</span>
        </a>
        <?php endif; ?>

        <?php if(can('audit.view')): ?>
        <a class="nav-link <?= ($cClass === 'audit') ? 'active' : '' ?>" href="<?= site_url('audit') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-journal-text"></i>
          <span class="menu-title">Audit Log</span>
        </a>
        <?php endif; ?>

        <div class="nav-section">AKUN SAYA</div>
        <a class="nav-link <?= ($cClass === 'profil') ? 'active' : '' ?>" href="<?= site_url('profil') ?>">
          <span class="menu-num"><?= sprintf('%02d', $menuNo++) ?></span>
          <i class="bi bi-person-circle"></i>
          <span class="menu-title">Profil Pengguna</span>
        </a>
      </nav>

      <!-- Sidebar Footer (User Info Pill) -->
      <div class="sidebar-footer">
        <div class="sidebar-user-pill">
          <div class="sidebar-avatar">
            <?= strtoupper(substr($currentUser['nama'] ?? 'U', 0, 1)) ?>
          </div>
          <div class="overflow-hidden">
            <div class="sidebar-user-name text-truncate"><?= html_escape($currentUser['nama'] ?? 'Pengguna') ?></div>
            <div class="sidebar-user-role"><?= html_escape($currentUser['role'] ?? '-') ?></div>
          </div>
        </div>
      </div>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="main-content">
    <header class="topbar">
      <button class="btn btn-outline-secondary d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar">
        <i class="bi bi-list"></i>
      </button>
      <h1 class="h5 mb-0 ms-2 fw-bold text-dark"><?= html_escape($title) ?></h1>
      <div class="ms-auto d-flex align-items-center gap-3">
        <span class="small text-muted d-none d-md-inline">
          <i class="bi bi-shield-check text-primary me-1"></i>
          <?= html_escape($currentUser['nama'] ?? '') ?> · <span class="badge text-bg-light border"><?= html_escape($currentUser['role'] ?? '') ?></span>
        </span>
        <a href="<?= site_url('auth/logout') ?>" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1">
          <i class="bi bi-box-arrow-right"></i>
          <span>Keluar</span>
        </a>
      </div>
    </header>

    <div class="container-fluid py-4">
      <?php if($this->session->flashdata('info')): ?>
        <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2" role="alert">
          <i class="bi bi-info-circle-fill fs-5"></i>
          <div><?= html_escape($this->session->flashdata('info')) ?></div>
          <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php $this->load->view($view, $data ?? array()); ?>
    </div>
  </main>
</div>

<!-- Page Specific Scripts -->
<?php if($view === 'kegiatan/index'): ?>
  <script src="<?= base_url('assets/js/kegiatan_form.js') ?>"></script>
<?php endif; ?>
<?php if($view === 'laporan/index'): ?>
  <script src="<?= base_url('assets/js/laporan.js') ?>"></script>
<?php endif; ?>
</body>
</html>

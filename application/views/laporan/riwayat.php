<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h1 class="h5 fw-bold text-dark mb-1">History Verifikasi &amp; Audit</h1>
    <p class="text-muted small mb-0">Pusat penelusuran untuk melihat keputusan verifikasi kegiatan dan jejak perubahan data di dalam sistem.</p>
  </div>
</div>

<div class="row g-4">
  <?php if (can('kegiatan.verify')): ?>
  <div class="col-12 col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-3"><span class="rounded-circle bg-primary-subtle text-primary p-3"><i class="bi bi-check2-square fs-4"></i></span><div><h2 class="h6 fw-bold mb-1">History Verifikasi</h2><p class="small text-muted mb-0">Riwayat kegiatan yang telah disetujui atau ditolak oleh verifikator.</p></div></div>
        <a href="<?= site_url('kegiatan/antrian') ?>" class="btn btn-outline-primary btn-sm">Buka History Verifikasi <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <?php endif; ?>
  <?php if (can('audit.view')): ?>
  <div class="col-12 col-md-6">
    <div class="card shadow-sm h-100">
      <div class="card-body p-4">
        <div class="d-flex align-items-center gap-3 mb-3"><span class="rounded-circle bg-warning-subtle text-warning p-3"><i class="bi bi-journal-text fs-4"></i></span><div><h2 class="h6 fw-bold mb-1">Audit Log</h2><p class="small text-muted mb-0">Jejak waktu, pengguna, entitas, aksi, serta perubahan data sebelum dan sesudah transaksi.</p></div></div>
        <a href="<?= site_url('audit') ?>" class="btn btn-outline-warning btn-sm">Buka Audit Log <i class="bi bi-arrow-right ms-1"></i></a>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

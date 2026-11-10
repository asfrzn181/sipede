<!-- Header Menu -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 d-print-none">
  <div>
    <h1 class="h5 fw-bold text-dark mb-0">Rekapitulasi Perjalanan Dinas</h1>
    <p class="text-muted small mb-0">Rekap data perjalanan dinas per rentang periode tanggal (date range) beserta rincian anggarannya.</p>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1" onclick="window.print()">
      <i class="bi bi-printer"></i>
      <span>Cetak Rekapitulasi</span>
    </button>
  </div>
</div>

<!-- Card Filter Date Range & Parameter (Hidden on Print) -->
<div class="card shadow-sm mb-4 d-print-none">
  <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
    <i class="bi bi-funnel text-primary"></i>
    <span>Filter Periode Tanggal &amp; Kriteria</span>
  </div>
  <div class="card-body p-4">
    <form id="filterRekapForm" method="get" action="<?= site_url('rekapitulasi') ?>">
      <div class="row g-3">
        <!-- Date Range: Dari Tanggal -->
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Dari Tanggal (Mulai)</label>
          <input type="date" class="form-control" name="tanggal_mulai" value="<?= html_escape($filter['tanggal_mulai']) ?>" required>
        </div>

        <!-- Date Range: Sampai Tanggal -->
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Sampai Tanggal (Selesai)</label>
          <input type="date" class="form-control" name="tanggal_selesai" value="<?= html_escape($filter['tanggal_selesai']) ?>" required>
        </div>

        <!-- Filter Status -->
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Status Kegiatan</label>
          <select class="form-select" name="status">
            <option value="">Semua Status</option>
            <option value="AKTIF" <?= $filter['status'] === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
            <option value="DIAJUKAN" <?= $filter['status'] === 'DIAJUKAN' ? 'selected' : '' ?>>DIAJUKAN</option>
            <option value="DRAFT" <?= $filter['status'] === 'DRAFT' ? 'selected' : '' ?>>DRAFT</option>
            <option value="BATAL" <?= $filter['status'] === 'BATAL' ? 'selected' : '' ?>>BATAL</option>
          </select>
        </div>

        <!-- Filter Kategori Peserta -->
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Kategori Pelaksana</label>
          <select class="form-select" name="tipe">
            <option value="">Semua (DPRD &amp; Pendamping)</option>
            <option value="DPRD" <?= $filter['tipe'] === 'DPRD' ? 'selected' : '' ?>>Hanya DPRD</option>
            <option value="PENDAMPING" <?= $filter['tipe'] === 'PENDAMPING' ? 'selected' : '' ?>>Hanya Pendamping</option>
          </select>
        </div>

        <!-- Filter Tempat / Kota -->
        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold">Tempat / Kota Tujuan</label>
          <input type="text" class="form-control" name="tempat" value="<?= html_escape($filter['tempat']) ?>" placeholder="Cari kota tujuan (misal: Jakarta, Surabaya)...">
        </div>

        <!-- Filter Penerima Tertentu -->
        <div class="col-12 col-md-6">
          <label class="form-label small fw-semibold">Personel Tertentu</label>
          <select class="form-select" name="penerima_id">
            <option value="">Semua Personel</option>
            <?php foreach($penerimaList as $p): ?>
              <?php $encP = encrypt_id($p['id']); ?>
              <option value="<?= $encP ?>" <?= ($filter['penerima_id'] && (int)$filter['penerima_id'] === (int)$p['id']) ? 'selected' : '' ?>>
                <?= html_escape($p['nama']) ?> (<?= html_escape($p['tipe']) ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Tombol Aksi -->
        <div class="col-12 d-flex justify-content-end gap-2 pt-2">
          <a href="<?= site_url('rekapitulasi') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-counterclockwise"></i> Reset
          </a>
          <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-search"></i>
            <span>Tampilkan Rekapitulasi</span>
          </button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- 3 Ringkasan Metrik Rekapitulasi Periode -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2">
        <i class="bi bi-calendar2-range text-primary"></i> Total Kegiatan Perjalanan Dinas
      </span>
      <strong class="text-primary"><?= (int)$totalKegiatan ?> Kegiatan</strong>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2">
        <i class="bi bi-people text-success"></i> Total Personel Berangkat
      </span>
      <strong class="text-dark">
        <?= (int)$totalPeserta ?> Orang
        <small class="fs-6 text-muted fw-normal">(<?= (int)$totalDprd ?> DPRD &bull; <?= (int)$totalPendamping ?> Pendamping)</small>
      </strong>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2">
        <i class="bi bi-wallet2 text-warning"></i> Total Biaya Terserap
      </span>
      <strong class="text-success"><?= rupiah($totalBiaya) ?></strong>
    </div>
  </div>
</div>

<!-- Header Cetak Resmi (Hanya Muncul saat Print) -->
<div class="d-none d-print-block mb-4">
  <?php $this->load->view('layout/kop_dokumen'); ?>
  <div class="text-center my-3">
    <h5 class="fw-bold text-uppercase mb-1" style="letter-spacing: 1px;">REKAPITULASI BIAYA PERJALANAN DINAS</h5>
    <p class="mb-0 small text-muted">
      Periode: <strong><?= tgl_indo($filter['tanggal_mulai']) ?></strong> s/d <strong><?= tgl_indo($filter['tanggal_selesai']) ?></strong>
    </p>
  </div>
</div>

<!-- Tabel Data Rekapitulasi -->
<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-table text-primary"></i>
      <span>Data Rekapitulasi Perjalanan Dinas</span>
    </div>
    <span class="badge text-bg-light border text-muted small">
      Periode: <?= tgl_indo($filter['tanggal_mulai']) ?> s/d <?= tgl_indo($filter['tanggal_selesai']) ?>
    </span>
  </div>
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="rekapTable">
        <thead class="table-light">
          <tr>
            <th style="width: 40px;" class="text-center">No</th>
            <th style="width: 140px;">No. Surat Tugas</th>
            <th style="width: 100px;">Tanggal</th>
            <th>Tema / Maksud Kegiatan</th>
            <th>Tempat Tujuan</th>
            <th style="width: 90px;" class="text-center">Peserta</th>
            <th class="text-end" style="width: 150px;">Total Biaya (Rp)</th>
            <th style="width: 90px;" class="text-center">Status</th>
            <th class="no-sort text-end d-print-none" style="width: 150px;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr class="fw-bold table-light">
            <td colspan="6" class="text-end text-uppercase">Total Rekapitulasi Periode:</td>
            <td class="text-end text-primary fs-6"><?= rupiah($totalBiaya) ?></td>
            <td colspan="2"></td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<!-- Tanda Tangan Cetak Resmi (Hanya Muncul saat Print) -->
<div class="d-none d-print-block mt-4">
  <?php $this->load->view('layout/tanda_tangan', array('tanggal' => date('Y-m-d'))); ?>
</div>

<script>
$(function(){
  function getRekapParams(){
    var o = {};
    $('#filterRekapForm').serializeArray().forEach(function(x){
      o[x.name] = x.value;
    });
    return o;
  }

  var rekapDT = window.createDataTableServerSide('#rekapTable', {
    ajax: {
      url: window.APP.baseUrl + 'rekapitulasi/data',
      data: function(d) {
        return $.extend({}, d, getRekapParams());
      }
    },
    order: [[2, 'desc']],
    columnDefs: [
      { targets: [0, 5, 7], className: 'text-center' },
      { targets: 6, className: 'text-end' },
      { targets: 8, className: 'text-end', orderable: false }
    ]
  });

  $('#filterRekapForm').on('submit', function(e){
    e.preventDefault();
    if (rekapDT) {
      rekapDT.ajax.reload();
    }
  });
});
</script>

<style>
@media print {
  body {
    background: #fff !important;
  }
  .sidebar, .topbar, .d-print-none {
    display: none !important;
  }
  .app-shell, .main-content, .container-fluid {
    padding: 0 !important;
    margin: 0 !important;
    width: 100% !important;
  }
  .card {
    border: none !important;
    box-shadow: none !important;
  }
}
</style>


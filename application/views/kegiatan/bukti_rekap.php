<?php
  $encId = encrypt_id($kegiatan['id']);
?>

<!-- Action Bar (Hidden on Print) -->
<div class="d-flex justify-content-between align-items-center mb-4 d-print-none">
  <div class="d-flex align-items-center gap-2">
    <a href="<?= site_url('kegiatan/rincian/' . $encId) ?>" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
      <i class="bi bi-arrow-left"></i> Kembali ke Rincian Kegiatan
    </a>
    <span class="fs-6 text-muted">|</span>
    <span class="fw-semibold text-dark">Bukti Rekapitulasi Perjalanan Dinas</span>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-primary d-inline-flex align-items-center gap-1 shadow-sm" onclick="window.print()">
      <i class="bi bi-printer-fill"></i>
      <span>Cetak / Simpan PDF</span>
    </button>
  </div>
</div>

<!-- Dokumen Resmi Rekapitulasi (Print Sheet) -->
<div class="print-sheet card border-0 shadow-sm mx-auto">
  <div class="card-body p-4 p-md-5">

    <!-- KOP Surat Resmi Dinamis -->
    <?php $this->load->view('layout/kop_dokumen'); ?>

    <!-- Judul Dokumen -->
    <div class="text-center mb-4">
      <h5 class="fw-bold text-uppercase text-decoration-underline mb-1" style="letter-spacing: 1px;">
        BUKTI REKAPITULASI PERJALANAN DINAS
      </h5>
      <span class="small fw-semibold text-muted">
        Nomor: <?= html_escape($kegiatan['no_surat_tugas'] ?: 'DRAFT/ST/' . date('Y')) ?>
      </span>
    </div>

    <!-- Informasi Dasar Surat Tugas -->
    <table class="table table-sm table-borderless mb-4 small" style="font-size: 0.88rem;">
      <tbody>
        <tr>
          <td style="width: 25%;" class="fw-semibold text-muted">Maksud / Tema</td>
          <td style="width: 2%;">:</td>
          <td class="fw-bold text-dark"><?= html_escape($kegiatan['tema'] ?: $kegiatan['nama_kegiatan']) ?></td>
        </tr>
        <tr>
          <td class="fw-semibold text-muted">Tempat Tujuan</td>
          <td>:</td>
          <td class="fw-bold text-dark"><?= html_escape($kegiatan['tempat'] ?: '-') ?></td>
        </tr>
        <tr>
          <td class="fw-semibold text-muted">Waktu Pelaksanaan</td>
          <td>:</td>
          <td>
            <?= tgl_indo($kegiatan['tanggal_mulai']) ?> s/d <?= tgl_indo($kegiatan['tanggal_selesai']) ?> 
            <span class="text-muted">(<?= (int)$durasiHari ?> Hari)</span>
          </td>
        </tr>
        <tr>
          <td class="fw-semibold text-muted">Tanggal Surat Tugas</td>
          <td>:</td>
          <td><?= tgl_indo($kegiatan['tanggal_surat_tugas']) ?></td>
        </tr>
        <tr>
          <td class="fw-semibold text-muted">Tahun Anggaran</td>
          <td>:</td>
          <td><?= html_escape($kegiatan['tahun_row']['tahun'] ?? date('Y')) ?></td>
        </tr>
      </tbody>
    </table>

    <!-- Tabel Rekapitulasi Peserta & Rincian Biaya -->
    <div class="table-responsive mb-3">
      <table class="table table-bordered align-middle small table-sm" style="font-size: 0.84rem; border-color: #000;">
        <thead class="table-light text-center align-middle" style="border-color: #000;">
          <tr>
            <th style="width: 35px;">No</th>
            <th>Nama Pelaksana / NIP</th>
            <th style="width: 90px;">Kategori</th>
            <th>Jabatan</th>
            <th>Beban Pos Anggaran</th>
            <th>Rincian Biaya</th>
            <th style="width: 140px;" class="text-end">Jumlah (Rp)</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($kegiatan['peserta'])): ?>
            <tr>
              <td colspan="7" class="text-center text-muted py-3">Belum ada data peserta perjalanan dinas.</td>
            </tr>
          <?php else: ?>
            <?php foreach($kegiatan['peserta'] as $i => $p): ?>
            <tr>
              <td class="text-center"><?= $i + 1 ?></td>
              <td class="fw-bold text-dark">
                <?= html_escape($p['nama']) ?>
              </td>
              <td class="text-center">
                <span class="badge border <?= $p['tipe'] === 'DPRD' ? 'text-bg-primary bg-opacity-10 text-primary border-primary' : 'text-bg-secondary bg-opacity-10 text-secondary border-secondary' ?>">
                  <?= html_escape($p['tipe']) ?>
                </span>
              </td>
              <td><?= html_escape($p['jabatan'] ?: '-') ?></td>
              <td class="small">
                <strong><?= html_escape($p['pos_nama'] ?: ($p['tipe'] === 'DPRD' ? 'Pos DPRD: ' . $p['nama'] : 'Master Anggaran Aktif')) ?></strong>
                <?php if(!empty($p['alasan_pengalihan'])): ?>
                  <div class="text-danger small fst-italic mt-1" style="font-size: 0.72rem;">
                    &bull; Pengalihan: <?= html_escape($p['alasan_pengalihan']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td>
                <?php if(!empty($p['biaya'])): ?>
                  <ul class="list-unstyled mb-0 small">
                    <?php foreach($p['biaya'] as $b): ?>
                      <li>
                        &bull; <?= html_escape($b['jenis_biaya_nama']) ?>
                        (<?= (float)$b['volume'] ?> &times; <?= rupiah($b['tarif']) ?>)
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <span class="text-muted italic">-</span>
                <?php endif; ?>
              </td>
              <td class="text-end fw-bold">
                <?= rupiah($p['total_biaya']) ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot style="border-top: 2px solid #000;">
          <tr class="fw-bold table-light">
            <td colspan="6" class="text-end text-uppercase">Total Biaya Perjalanan Dinas:</td>
            <td class="text-end fs-6 text-primary"><?= rupiah($kegiatan['grand_total']) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Kalimat Terbilang -->
    <div class="p-3 border rounded mb-4 bg-light small">
      <strong>Terbilang:</strong>
      <span class="fst-italic text-dark fw-semibold ms-1">"<?= html_escape($terbilang) ?>"</span>
    </div>

    <!-- Kolom Tanda Tangan Pengesahan Dinamis -->
    <?php $this->load->view('layout/tanda_tangan', array(
      'tanggal' => $kegiatan['tanggal_input'] ?? date('Y-m-d'),
      'kolom' => 2,
    )); ?>

  </div>
</div>

<style>
.print-sheet {
  max-width: 900px;
  background: #ffffff;
  border-radius: 12px;
}

@media print {
  body {
    background: #ffffff !important;
    color: #000000 !important;
    font-size: 12pt;
  }
  .app-shell, .main-content, .container-fluid {
    padding: 0 !important;
    margin: 0 !important;
    display: block !important;
  }
  .sidebar, .topbar, .d-print-none, .btn {
    display: none !important;
  }
  .print-sheet {
    box-shadow: none !important;
    border: none !important;
    max-width: 100% !important;
    width: 100% !important;
    padding: 0 !important;
  }
  .card-body {
    padding: 10mm 15mm !important;
  }
  table {
    border-color: #000000 !important;
  }
  th, td {
    border-color: #000000 !important;
  }
}
</style>


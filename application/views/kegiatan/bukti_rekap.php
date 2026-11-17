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
            <th>Nama</th>
            <th>Jabatan</th>
            <th>Beban Pos Anggaran</th>
            <th style="width: 125px;" class="text-end">Dana Awal</th>
            <th style="width: 125px;" class="text-end">Realisasi</th>
            <th>Rincian Biaya</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($kegiatan['peserta'])): ?>
            <tr>
              <td colspan="10" class="text-center text-muted py-3">Belum ada data peserta perjalanan dinas.</td>
            </tr>
          <?php else: ?>
            <?php foreach($kegiatan['peserta'] as $i => $p): ?>
            <tr>
              <td class="text-center"><?= $i + 1 ?></td>
              <td class="fw-bold text-dark">
                <?= html_escape($p['nama']) ?>
              </td>
              <td><?= html_escape($p['jabatan'] ?: '-') ?></td>
              <td class="small">
                <strong><?= html_escape($p['pos_nama'] ?: ($p['tipe'] === 'DPRD' ? 'Pos DPRD: ' . $p['nama'] : 'Master Anggaran Aktif')) ?></strong>
                <?php if(!empty($p['alasan_pengalihan'])): ?>
                  <div class="text-muted small fst-italic mt-1" style="font-size: 0.72rem;">
                    &bull; Keterangan: <?= html_escape($p['alasan_pengalihan']) ?>
                  </div>
                <?php endif; ?>
              </td>
              <td class="text-end fw-bold text-primary">
                <?= rupiah($p['dana_awal'] ?? 0) ?>
              </td>
              <td class="text-end fw-bold text-success">
                <?= rupiah($p['realisasi_nota'] ?? $p['total_biaya']) ?>
              </td>
              <td>
                <?php if(!empty($p['biaya'])): ?>
                  <ul class="list-unstyled mb-0 small">
                    <?php foreach($p['biaya'] as $b): ?>
                      <li>
                        &bull; <?= html_escape($b['jenis_biaya_nama']) ?>
                        (<?= (float)$b['volume'] ?> &times; <?= rupiah($b['tarif']) ?>)
                        <?php if(!empty($b['keterangan'])): ?><div class="text-muted ms-3">— <?= html_escape($b['keterangan']) ?></div><?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php else: ?>
                  <span class="text-muted italic">-</span>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
        <tfoot style="border-top: 2px solid #000;">
          <tr class="fw-bold table-light">
            <td colspan="7" class="text-end text-uppercase">Total Dana Awal:</td>
            <td class="text-end fs-6 text-primary"><?= rupiah($kegiatan['total_dana_awal'] ?? 0) ?></td>
            <td class="text-end fs-6 text-success"><?= rupiah($kegiatan['grand_total']) ?></td>
            <td class="text-end fs-6 text-dark"><?= rupiah(($kegiatan['total_dana_awal'] ?? 0) - $kegiatan['grand_total']) ?></td>
          </tr>
        </tfoot>
      </table>
    </div>

    <!-- Kalimat Terbilang -->
    <div class="p-3 border rounded mb-4 bg-light small">
      <strong>Terbilang:</strong>
      <span class="fst-italic text-dark fw-semibold ms-1">"<?= html_escape($terbilang) ?>"</span>
    </div>

    <!-- Input Verifikator (tidak ikut cetak) -->
    <div class="d-print-none mb-3">
      <label class="form-label small fw-semibold">Nama Verifikator (boleh dikosongkan)</label>
      <input type="text" class="form-control form-control-sm" id="inpVerifikatorNama" value="<?= html_escape($setting['verifikator_nama'] ?? '') ?>" placeholder="Kosongkan jika tidak ingin menampilkan">
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
  @page { size: 215mm 330mm; margin: 15mm; }
  table { table-layout: fixed; width: 100%; }
  th, td { word-wrap: break-word; overflow-wrap: break-word; }
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

<script>
$(function(){
  $('#inpVerifikatorNama').on('input', function(){
    var val = $(this).val().trim();
    $('#ttdVerifikatorNama').text(val !== '' ? val : '________________________');
  });
});
</script>


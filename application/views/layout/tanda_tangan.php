<?php
  if (!isset($setting) || empty($setting)) {
    $CI =& get_instance();
    $CI->load->model('Pengaturan_model');
    $setting = $CI->Pengaturan_model->get();
  }

  $tanggalStr = !empty($tanggal) ? tgl_indo($tanggal) : tgl_indo(date('Y-m-d'));
  $kota = !empty($setting['kota_penetapan']) ? $setting['kota_penetapan'] : 'Kota';
  $jumlahKolom = isset($kolom) ? (int)$kolom : 3;
?>

<div class="tanda-tangan-block mt-5 pt-3" style="page-break-inside: avoid; font-size: 0.88rem;">
  <div class="row text-center g-3">
    <!-- Kolom 1: Pengguna Anggaran (PA) -->
    <div class="<?= $jumlahKolom === 2 ? 'col-6' : 'col-4' ?>">
      <div class="ttd-header" style="min-height: 48px;">
        <?= nl2br(html_escape($setting['pa_label'])) ?><br>
        <span class="fw-semibold"><?= html_escape($setting['pa_jabatan']) ?></span>
      </div>
      <div class="ttd-space" style="height: 75px;"></div>
      <div class="ttd-body">
        <strong class="text-decoration-underline d-block fw-bold text-dark">
          <?= html_escape($setting['pa_nama']) ?>
        </strong>
        <?php if (!empty($setting['pa_pangkat'])): ?>
          <div class="text-muted small"><?= html_escape($setting['pa_pangkat']) ?></div>
        <?php endif; ?>
        <span class="text-muted small">NIP. <?= html_escape($setting['pa_nip']) ?></span>
      </div>
    </div>

    <!-- Kolom 2: PPTK -->
    <div class="<?= $jumlahKolom === 2 ? 'col-6' : 'col-4' ?>">
      <div class="ttd-header" style="min-height: 48px;">
        <?= nl2br(html_escape($setting['pptk_label'])) ?><br>
        <span class="fw-semibold"><?= html_escape($setting['pptk_jabatan']) ?></span>
      </div>
      <div class="ttd-space" style="height: 75px;"></div>
      <div class="ttd-body">
        <strong class="text-decoration-underline d-block fw-bold text-dark">
          <?= html_escape($setting['pptk_nama']) ?>
        </strong>
        <?php if (!empty($setting['pptk_pangkat'])): ?>
          <div class="text-muted small"><?= html_escape($setting['pptk_pangkat']) ?></div>
        <?php endif; ?>
        <span class="text-muted small">NIP. <?= html_escape($setting['pptk_nip']) ?></span>
      </div>
    </div>

    <!-- Kolom 3 tetap dipakai pada laporan yang membutuhkan tiga tanda tangan. -->
    <?php if ($jumlahKolom !== 2): ?>
    <div class="col-4">
      <div class="ttd-header" style="min-height: 48px;">
        <span><?= html_escape($kota) ?>, <?= $tanggalStr ?></span><br>
        <strong><?= html_escape($setting['bendahara_label']) ?></strong>
      </div>
      <div class="ttd-space" style="height: 75px;"></div>
      <div class="ttd-body">
        <strong class="text-decoration-underline d-block fw-bold text-dark">
          <?= html_escape($setting['bendahara_nama']) ?>
        </strong>
        <?php if (!empty($setting['bendahara_pangkat'])): ?>
          <div class="text-muted small"><?= html_escape($setting['bendahara_pangkat']) ?></div>
        <?php endif; ?>
        <span class="text-muted small">NIP. <?= html_escape($setting['bendahara_nip']) ?></span>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<style>
@media print {
  .tanda-tangan-block {
    margin-top: 30px !important;
    color: #000000 !important;
  }
  .tanda-tangan-block .text-muted {
    color: #000000 !important;
  }
  .tanda-tangan-block strong {
    color: #000000 !important;
  }
}
</style>


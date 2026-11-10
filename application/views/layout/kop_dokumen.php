<?php
  if (!isset($setting) || empty($setting)) {
    $CI =& get_instance();
    $CI->load->model('Pengaturan_model');
    $setting = $CI->Pengaturan_model->get();
  }

  $logoUrl = base_url($setting['logo_path'] ?? 'assets/img/logo_default.svg');
  $showLogo = $show_logo ?? true;
?>

<div class="kop-dokumen mb-4 pb-2 text-center" style="border-bottom: 3px double #000000; page-break-inside: avoid;">
  <div class="d-flex align-items-center justify-content-center gap-3">
    <?php if ($showLogo): ?>
    <div class="kop-logo-wrapper" style="flex: 0 0 95px; max-width: 95px; text-align: center;">
      <img src="<?= $logoUrl ?>" alt="Logo Daerah" class="kop-logo img-fluid" style="max-height: 95px; object-fit: contain;">
    </div>
    <?php endif; ?>

    <div class="kop-text flex-grow-1 text-center" style="line-height: 1.25;">
      <?php if (!empty($setting['nama_pemda'])): ?>
      <div class="kop-pemda fw-bold text-uppercase" style="font-size: 1.05rem; letter-spacing: 1.2px; color: #1e293b;">
        <?= html_escape($setting['nama_pemda']) ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($setting['nama_lembaga'])): ?>
      <div class="kop-lembaga fw-bold text-uppercase" style="font-size: 1.2rem; letter-spacing: 1px; color: #0f172a;">
        <?= html_escape($setting['nama_lembaga']) ?>
      </div>
      <?php endif; ?>

      <?php if (!empty($setting['nama_skpd'])): ?>
      <div class="kop-skpd fw-bolder text-uppercase" style="font-size: 1.35rem; letter-spacing: 0.8px; color: #000000;">
        <?= html_escape($setting['nama_skpd']) ?>
      </div>
      <?php endif; ?>

      <div class="kop-kontak small text-muted mt-1" style="font-size: 0.8rem; line-height: 1.35; color: #475569 !important;">
        <?= html_escape($setting['alamat'] ?? '') ?>
        <?php if (!empty($setting['kode_pos'])): ?>
          &bull; Kode Pos: <?= html_escape($setting['kode_pos']) ?>
        <?php endif; ?>
        <br>
        <?php
          $kontakParts = [];
          if (!empty($setting['telepon'])) $kontakParts[] = 'Telp: ' . html_escape($setting['telepon']);
          if (!empty($setting['fax']))     $kontakParts[] = 'Fax: ' . html_escape($setting['fax']);
          if (!empty($setting['email']))   $kontakParts[] = 'Email: ' . html_escape($setting['email']);
          if (!empty($setting['website'])) $kontakParts[] = 'Website: ' . html_escape($setting['website']);
          echo implode(' &bull; ', $kontakParts);
        ?>
      </div>
    </div>
    <?php if ($showLogo): ?>
    <!-- Dummy spacer to balance header center alignment -->
    <div class="d-none d-md-block" style="flex: 0 0 95px; max-width: 95px;"></div>
    <?php endif; ?>
  </div>
</div>

<style>
@media print {
  .kop-dokumen {
    border-bottom: 3px double #000000 !important;
  }
  .kop-pemda, .kop-lembaga, .kop-skpd, .kop-kontak {
    color: #000000 !important;
  }
  .kop-logo {
    max-height: 90px !important;
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>


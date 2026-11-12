<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Laporan Realisasi Anggaran Perjalanan Dinas</title>
  <style>
    @page {
      size: A4 landscape;
      margin: 12mm 15mm;
    }
    body {
      font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
      font-size: 9pt;
      line-height: 1.3;
      color: #000;
      background: #fff;
      margin: 0;
      padding: 0;
    }
    .kop {
      text-align: center;
      border-bottom: 2.5px double #000;
      padding-bottom: 8px;
      margin-bottom: 15px;
    }
    .kop-header {
      display: table;
      width: 100%;
    }
    .kop-logo-cell {
      display: table-cell;
      width: 72px;
      vertical-align: middle;
      text-align: left;
    }
    .kop-logo {
      width: 62px;
      height: 62px;
      object-fit: contain;
    }
    .kop-text {
      display: table-cell;
      vertical-align: middle;
      text-align: center;
    }
    .kop h4 {
      font-size: 11pt;
      font-weight: bold;
      margin: 0 0 2px 0;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .kop h3 {
      font-size: 13pt;
      font-weight: 900;
      margin: 0 0 2px 0;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }
    .kop p {
      font-size: 7.5pt;
      color: #333;
      margin: 2px 0 0 0;
      line-height: 1.25;
    }
    .doc-title {
      text-align: center;
      margin-bottom: 14px;
    }
    .doc-title h2 {
      font-size: 11pt;
      font-weight: bold;
      text-transform: uppercase;
      text-decoration: underline;
      margin: 0 0 3px 0;
      letter-spacing: 0.5px;
    }
    .doc-title span {
      font-size: 8pt;
      color: #444;
    }
    table.data-table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 18px;
    }
    table.data-table th, table.data-table td {
      border: 1px solid #444;
      padding: 4px 6px;
      font-size: 8pt;
    }
    table.data-table th {
      background-color: #f1f5f9;
      font-weight: bold;
      text-align: center;
    }
    .text-center { text-align: center; }
    .text-end { text-align: right; }
    .text-nowrap { white-space: nowrap; }
    .fw-bold { font-weight: bold; }
    
    /* Signatures */
    .ttd-table {
      width: 100%;
      margin-top: 25px;
      page-break-inside: avoid;
    }
    .ttd-table td {
      width: 50%;
      text-align: center;
      vertical-align: top;
      font-size: 8.5pt;
      padding: 0 10px;
    }
    .ttd-space {
      height: 60px;
    }
  </style>
</head>
<body>

  <!-- KOP SURAT RESMI -->
  <div class="kop">
    <div class="kop-header">
      <div class="kop-logo-cell">
        <img class="kop-logo" src="<?= html_escape($setting['logo_path'] ?? config_item('logo_url')) ?>" alt="Logo Sekretariat DPRD Kabupaten Bangka">
      </div>
      <div class="kop-text">
        <h4><?= html_escape($setting['nama_pemda'] ?? 'PEMERINTAH DAERAH') ?></h4>
        <h3><?= html_escape($setting['nama_skpd'] ?? 'SEKRETARIAT DPRD') ?></h3>
        <p>
          <?= html_escape($setting['alamat'] ?? '') ?>
          <?php if (!empty($setting['kode_pos'])): ?> &bull; Kode Pos: <?= html_escape($setting['kode_pos']) ?><?php endif; ?>
          <br>
          <?php
            $parts = array();
            if (!empty($setting['telepon'])) $parts[] = 'Telp: ' . html_escape($setting['telepon']);
            if (!empty($setting['fax']))     $parts[] = 'Fax: ' . html_escape($setting['fax']);
            if (!empty($setting['email']))   $parts[] = 'Email: ' . html_escape($setting['email']);
            if (!empty($setting['website'])) $parts[] = 'Website: ' . html_escape($setting['website']);
            echo implode(' &bull; ', $parts);
          ?>
        </p>
      </div>
    </div>
  </div>

  <!-- JUDUL DOKUMEN -->
  <div class="doc-title">
    <h2>LAPORAN REALISASI BIAYA PERJALANAN DINAS</h2>
    <span>
      <?php if (!empty($filter['tanggal_mulai']) && !empty($filter['tanggal_selesai'])): ?>
        Periode: <?= tgl_indo($filter['tanggal_mulai']) ?> s/d <?= tgl_indo($filter['tanggal_selesai']) ?>
      <?php else: ?>
        Tahun Anggaran <?= date('Y') ?>
      <?php endif; ?>
    </span>
  </div>

  <!-- TABEL DATA -->
  <table class="data-table">
    <thead>
      <tr>
        <th style="width: 25px;">No</th>
        <th style="width: 100px;">No. Surat Tugas</th>
        <th style="width: 75px;">Tanggal</th>
        <th>Maksud / Tema Kegiatan</th>
        <th style="width: 90px;">Tujuan</th>
        <th style="width: 110px;">Pelaksana</th>
        <th style="width: 90px;">Jabatan</th>
        <th style="width: 95px;">Jenis Biaya</th>
        <th style="width: 85px;" class="text-end">Jumlah (Rp)</th>
      </tr>
    </thead>
    <tbody>
      <?php 
        $grandTotal = 0.0;
        foreach($rows as $idx => $r): 
          $subtotal = (float)($r['subtotal'] ?? 0);
          $grandTotal += $subtotal;
      ?>
      <tr>
        <td class="text-center"><?= $idx + 1 ?></td>
        <td><code><?= html_escape($r['no_surat_tugas'] ?? '-') ?></code></td>
        <td class="text-nowrap text-center"><?= !empty($r['tanggal_mulai']) ? tgl_indo($r['tanggal_mulai']) : '-' ?></td>
        <td><?= html_escape($r['tema'] ?? $r['nama_kegiatan'] ?? '-') ?></td>
        <td><?= html_escape($r['tempat'] ?? '-') ?></td>
        <td><?= html_escape($r['penerima_nama'] ?? '-') ?></td>
        <td><?= html_escape($r['jabatan'] ?? '-') ?></td>
        <td><?= html_escape($r['jenis_biaya_nama'] ?? '-') ?></td>
        <td class="text-end fw-bold"><?= number_format($subtotal, 0, ',', '.') ?></td>
      </tr>
      <?php endforeach; ?>
      <?php if (empty($rows)): ?>
      <tr>
        <td colspan="9" class="text-center" style="padding: 15px;">Tidak ada data realisasi yang sesuai dengan filter.</td>
      </tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr style="background-color: #f1f5f9; font-weight: bold;">
        <td colspan="8" class="text-end" style="text-transform: uppercase;">TOTAL REALISASI:</td>
        <td class="text-end"><?= number_format($grandTotal, 0, ',', '.') ?></td>
      </tr>
    </tfoot>
  </table>

  <!-- TANDA TANGAN 2 PEJABAT -->
  <table class="ttd-table">
    <tr>
      <td>
        <?= nl2br(html_escape($setting['pptk_label'])) ?><br>
        <strong><?= html_escape($setting['pptk_jabatan']) ?></strong>
        <div class="ttd-space"></div>
        <strong style="text-decoration: underline; display: block;"><?= html_escape($setting['pptk_nama']) ?></strong>
        <?php if (!empty($setting['pptk_pangkat'])): ?>
          <div><?= html_escape($setting['pptk_pangkat']) ?></div>
        <?php endif; ?>
        <span>NIP. <?= html_escape($setting['pptk_nip']) ?></span>
      </td>
      <td>
        <span><?= html_escape($setting['kota_penetapan'] ?? 'Kota') ?>, <?= tgl_indo(date('Y-m-d')) ?></span><br>
        <strong><?= html_escape($setting['bendahara_label']) ?></strong>
        <div class="ttd-space"></div>
        <strong style="text-decoration: underline; display: block;"><?= html_escape($setting['bendahara_nama']) ?></strong>
        <?php if (!empty($setting['bendahara_pangkat'])): ?>
          <div><?= html_escape($setting['bendahara_pangkat']) ?></div>
        <?php endif; ?>
        <span>NIP. <?= html_escape($setting['bendahara_nip']) ?></span>
      </td>
    </tr>
  </table>

</body>
</html>
<?php
  if (class_exists('Dompdf\\Dompdf')) {
    $dompdf = new Dompdf\Dompdf(array('isRemoteEnabled' => true));
    $dompdf->loadHtml($this->output->get_output());
    $dompdf->setPaper('A4', 'landscape');
    $dompdf->render();
    $dompdf->stream('laporan_realisasi_' . date('Ymd_His') . '.pdf', array('Attachment' => false));
  }
?>

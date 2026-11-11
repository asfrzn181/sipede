<?php
$totalPagu = 0.0;
$totalRealisasi = 0.0;
foreach ($rows as $row) {
    $totalPagu += (float)$row['pagu'];
    $totalRealisasi += (float)$row['realisasi'];
}
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h1 class="h5 fw-bold text-dark mb-1">Laporan per Pos Anggaran</h1>
    <p class="text-muted small mb-0">Menampilkan pagu, realisasi, sisa, dan kegiatan yang membebani setiap pos anggaran.</p>
  </div>
  <form method="get" action="<?= site_url('laporan/pos') ?>" class="d-flex align-items-center gap-2">
    <label class="small text-muted text-nowrap">Tahun</label>
    <select name="tahun_anggaran_id" class="form-select form-select-sm" onchange="this.form.submit()">
      <?php foreach ($tahunList as $tahun): ?>
        <option value="<?= (int)$tahun['id'] ?>" <?= (int)$tahun['id'] === (int)$tahunId ? 'selected' : '' ?>><?= (int)$tahun['tahun'] ?></option>
      <?php endforeach; ?>
    </select>
  </form>
</div>

<div class="row g-3 mb-4">
  <div class="col-12 col-md-4"><div class="stat-card"><span>Total Pagu Pos</span><strong class="text-primary"><?= rupiah($totalPagu) ?></strong></div></div>
  <div class="col-12 col-md-4"><div class="stat-card"><span>Total Realisasi Pos</span><strong class="text-success"><?= rupiah($totalRealisasi) ?></strong></div></div>
  <div class="col-12 col-md-4"><div class="stat-card"><span>Total Sisa Pos</span><strong class="text-warning"><?= rupiah($totalPagu - $totalRealisasi) ?></strong></div></div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div class="fw-bold"><i class="bi bi-wallet2 text-primary me-1"></i>Ringkasan Pos Tahun <?= html_escape($tahunLabel) ?></div>
    <span class="badge text-bg-light border text-muted"><?= count($rows) ?> pos</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th class="ps-3">Kode / Nama Pos</th><th>Jenis</th><th>Pemegang</th><th class="text-center">Kegiatan</th><th class="text-end">Pagu</th><th class="text-end">Realisasi</th><th class="text-end">Sisa</th><th class="text-end pe-3">Serapan</th></tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?><tr><td colspan="8" class="text-center text-muted py-4">Belum ada pos anggaran pada tahun ini.</td></tr><?php endif; ?>
          <?php foreach ($rows as $row): ?>
            <?php $pct = max(0, min(100, (float)$row['persentase'])); ?>
            <tr>
              <td class="ps-3"><div class="fw-semibold"><?= html_escape($row['pos_nama']) ?></div><code><?= html_escape($row['kode']) ?></code></td>
              <td><span class="badge text-bg-light border"><?= html_escape($row['jenis_anggaran_nama'] ?: $row['tipe']) ?></span><div class="small text-muted"><?= html_escape($row['tipe']) ?></div></td>
              <td><?= html_escape($row['penerima_nama'] ?: 'Tidak melekat') ?></td>
              <td class="text-center"><?= (int)$row['jumlah_kegiatan'] ?></td>
              <td class="text-end"><?= rupiah($row['pagu']) ?></td>
              <td class="text-end text-success fw-semibold"><?= rupiah($row['realisasi']) ?></td>
              <td class="text-end text-primary"><?= rupiah($row['sisa']) ?></td>
              <td class="text-end pe-3" style="min-width: 150px"><div class="small fw-semibold mb-1"><?= number_format((float)$row['persentase'], 2, ',', '.') ?>%</div><div class="progress" style="height: 6px"><div class="progress-bar bg-success" style="width: <?= $pct ?>%"></div></div></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

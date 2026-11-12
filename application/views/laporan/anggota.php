<?php
$totalPagu = 0.0;
$totalRealisasi = 0.0;
foreach ($rows as $row) {
    $totalPagu += (float)$row['pagu'];
    $totalRealisasi += (float)$row['realisasi'];
}
$totalSisa = $totalPagu - $totalRealisasi;
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h1 class="h5 fw-bold text-dark mb-1">Laporan per Anggota DPRD</h1>
    <p class="text-muted small mb-0">Membandingkan pagu, realisasi, sisa anggaran, dan jumlah kegiatan untuk setiap anggota DPRD.</p>
  </div>
    <div class="d-flex align-items-center gap-2">
    <?php if(can('laporan.export')): ?><a class="btn btn-outline-success btn-sm" href="<?= site_url('laporan/anggota_excel?tahun_anggaran_id=' . (int)$tahunId) ?>"><i class="bi bi-file-earmark-excel me-1"></i>Excel</a><?php endif; ?>
    <form method="get" action="<?= site_url('laporan/anggota') ?>" class="d-flex align-items-center gap-2">
    <label class="small text-muted text-nowrap">Tahun</label>
    <select name="tahun_anggaran_id" class="form-select form-select-sm" onchange="this.form.submit()">
      <?php foreach ($tahunList as $tahun): ?>
        <option value="<?= (int)$tahun['id'] ?>" <?= (int)$tahun['id'] === (int)$tahunId ? 'selected' : '' ?>><?= (int)$tahun['tahun'] ?></option>
      <?php endforeach; ?>
    </select>
    </form>
    </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-12 col-md-4"><div class="stat-card"><span>Total Pagu DPRD</span><strong class="text-primary"><?= rupiah($totalPagu) ?></strong></div></div>
  <div class="col-12 col-md-4"><div class="stat-card"><span>Total Realisasi</span><strong class="text-success"><?= rupiah($totalRealisasi) ?></strong></div></div>
  <div class="col-12 col-md-4"><div class="stat-card"><span>Total Sisa</span><strong class="text-warning"><?= rupiah($totalSisa) ?></strong></div></div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white d-flex justify-content-between align-items-center">
    <div class="fw-bold"><i class="bi bi-person-vcard text-primary me-1"></i>Ringkasan Anggota Tahun <?= html_escape($tahunLabel) ?></div>
    <span class="badge text-bg-light border text-muted"><?= count($rows) ?> anggota</span>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
          <tr><th class="text-center">No</th><th class="ps-3">Nama</th><th class="text-end">Anggaran</th><th class="text-end">Realisasi</th><th class="text-end">Sisa</th><th>Keterangan</th><th class="text-end pe-3">Aksi</th></tr>
        </thead>
        <tbody>
          <?php if (empty($rows)): ?><tr><td colspan="7" class="text-center text-muted py-4">Belum ada data anggota atau pos anggaran pada tahun ini.</td></tr><?php endif; ?>
          <?php foreach ($rows as $idx => $row): ?>
            <tr>
              <td class="text-center"><?= $idx + 1 ?></td>
              <td class="ps-3 fw-semibold"><?= html_escape($row['nama']) ?></td>
              <td class="text-end"><?= rupiah($row['pagu']) ?></td>
              <td class="text-end text-success fw-semibold"><?= rupiah($row['realisasi']) ?></td>
              <td class="text-end text-primary"><?= rupiah($row['sisa']) ?></td>
              <td><?= html_escape($row['jabatan'] ?: '-') ?></td>
              <td class="text-end pe-3"><a class="btn btn-sm btn-outline-primary" href="<?= site_url('laporan/perorang?tahun_anggaran_id=' . (int)$tahunId . '&penerima_id=' . (int)$row['id']) ?>"><i class="bi bi-person-lines-fill me-1"></i>Detail</a></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <?php if (!empty($rows)): ?><tfoot class="table-light fw-bold"><tr><td></td><td class="text-end">JUMLAH</td><td class="text-end"><?= rupiah($totalPagu) ?></td><td class="text-end"><?= rupiah($totalRealisasi) ?></td><td class="text-end"><?= rupiah($totalSisa) ?></td><td></td><td></td></tr></tfoot><?php endif; ?>
      </table>
    </div>
  </div>
</div>

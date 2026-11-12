<?php
$detail = $detail ?? array();
$person = $detail['person'] ?? array();
$components = $detail['components'] ?? array();
$rows = $detail['rows'] ?? array();
?>
<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h1 class="h5 fw-bold text-dark mb-1">Laporan Per Orang</h1>
    <p class="text-muted small mb-0">Rekap belanja perjalanan dinas per anggota, komponen biaya, total, dan sisa anggaran.</p>
  </div>
  <?php if(!empty($can_export) && !empty($person)): ?>
    <a class="btn btn-outline-success btn-sm" href="<?= site_url('laporan/perorang_excel?tahun_anggaran_id=' . (int)$tahunId . '&penerima_id=' . (int)$penerimaId) ?>">
      <i class="bi bi-file-earmark-excel me-1"></i>Excel
    </a>
  <?php endif; ?>
</div>

<div class="card shadow-sm mb-3">
  <div class="card-body">
    <form method="get" action="<?= site_url('laporan/perorang') ?>" class="row g-3 align-items-end">
      <div class="col-12 col-md-3">
        <label class="form-label small fw-semibold">Tahun Anggaran</label>
        <select name="tahun_anggaran_id" class="form-select">
          <?php foreach($tahunList as $tahun): ?>
            <option value="<?= (int)$tahun['id'] ?>" <?= (int)$tahun['id'] === (int)$tahunId ? 'selected' : '' ?>><?= (int)$tahun['tahun'] ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-6">
        <label class="form-label small fw-semibold">Nama Anggota / Notulen / Pendamping</label>
        <select name="penerima_id" class="form-select">
          <?php foreach($penerima as $row): ?>
            <option value="<?= (int)$row['id'] ?>" <?= (int)$row['id'] === (int)$penerimaId ? 'selected' : '' ?>><?= html_escape($row['nama']) ?> — <?= html_escape($row['jabatan'] ?: ($row['tipe'] === 'DPRD' ? 'Anggota DPRD' : 'Pendamping')) ?> (<?= html_escape($row['tipe']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-12 col-md-3"><button class="btn btn-primary w-100" type="submit"><i class="bi bi-search me-1"></i>Tampilkan</button></div>
    </form>
  </div>
</div>

<?php if(empty($person)): ?>
  <div class="alert alert-info">Belum ada anggota DPRD yang tersedia pada tahun anggaran ini.</div>
<?php else: ?>
  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="fw-bold text-uppercase">Rekapitulasi Belanja Perjalanan Dinas Per Orang Tahun <?= html_escape($tahunLabel) ?></div>
      <div class="mt-2"><strong>NAMA:</strong> <?= html_escape($person['nama']) ?> <span class="text-muted">— <?= html_escape($person['jabatan'] ?: '-') ?></span></div>
      <div class="row g-3 mt-1">
        <?php foreach(($detail['pagu_breakdown'] ?? array()) as $pagu): ?>
          <div class="col-12 col-md-4"><div class="border rounded p-2"><span class="small text-muted d-block"><?= html_escape($pagu['nama']) ?></span><strong><?= rupiah($pagu['pagu']) ?></strong></div></div>
        <?php endforeach; ?>
        <div class="col-12 col-md-4"><div class="border rounded p-2 bg-light"><span class="small text-muted d-block">Total Pagu</span><strong><?= rupiah($detail['total_pagu'] ?? 0) ?></strong></div></div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle mb-0">
          <thead class="table-light small">
            <tr>
              <th class="text-center">No</th>
              <th>Keterangan</th>
              <th>Tujuan</th>
              <th>Tanggal Pelaksanaan</th>
              <?php foreach($components as $component): ?><th class="text-end"><?= html_escape($component['nama']) ?></th><?php endforeach; ?>
              <th class="text-end">Total</th>
              <th class="text-end">Sisa Anggaran</th>
              <th>Sumber Dana</th>
              <th>Keterangan Detail</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($rows)): ?><tr><td colspan="<?= 8 + count($components) ?>" class="text-center text-muted py-4">Belum ada realisasi perjalanan dinas yang aktif.</td></tr><?php endif; ?>
            <?php foreach($rows as $idx => $row): ?>
              <?php $tanggal = tgl_indo($row['tanggal_mulai']); if(!empty($row['tanggal_selesai']) && $row['tanggal_selesai'] !== $row['tanggal_mulai']) $tanggal .= ' s/d ' . tgl_indo($row['tanggal_selesai']); ?>
              <tr>
                <td class="text-center"><?= $idx + 1 ?></td>
                <td><?= html_escape($row['tema'] ?: $row['nama_kegiatan'] ?: '-') ?></td>
                <td><?= html_escape($row['tempat'] ?: '-') ?></td>
                <td class="text-nowrap"><?= html_escape($tanggal) ?></td>
                <?php foreach($components as $component): ?><td class="text-end"><?= rupiah($row['komponen'][(int)$component['id']] ?? 0) ?></td><?php endforeach; ?>
                <td class="text-end fw-semibold"><?= rupiah($row['total']) ?></td>
                <td class="text-end text-primary fw-semibold"><?= rupiah($row['sisa']) ?></td>
                <td><?= html_escape($row['sumber_dana'] ?: '-') ?></td>
                <td><?= html_escape($row['keterangan'] ?: '-') ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
          <?php if(!empty($rows)): ?><tfoot class="table-light fw-bold"><tr><td colspan="4" class="text-end">JUMLAH</td><?php foreach($components as $component): ?><td class="text-end"><?= rupiah($component['total']) ?></td><?php endforeach; ?><td class="text-end"><?= rupiah($detail['total_realisasi'] ?? 0) ?></td><td class="text-end"><?= rupiah($detail['sisa'] ?? 0) ?></td><td></td><td></td></tr></tfoot><?php endif; ?>
        </table>
      </div>
    </div>
  </div>
<?php endif; ?>

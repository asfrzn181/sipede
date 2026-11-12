<?php
  $encId = encrypt_id($kegiatan['id']);
  $status = $kegiatan['status'];
  $badgeClass = $status === 'BATAL' ? 'danger' : ($status === 'AKTIF' ? 'success' : ($status === 'DIAJUKAN' ? 'warning' : 'secondary'));
?>

<!-- Breadcrumb & Top Bar -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="d-flex align-items-center gap-2">
    <a href="<?= site_url('kegiatan') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
      <i class="bi bi-arrow-left"></i> Daftar Kegiatan
    </a>
    <h1 class="h5 fw-bold text-dark mb-0">Rincian Kegiatan &amp; Peserta</h1>
    <span class="badge text-bg-<?= $badgeClass ?> fs-6"><?= html_escape($status) ?></span>
  </div>
  <div class="d-flex align-items-center gap-2">
    <!-- Tombol Bukti Rekapitulasi Perjalanan Dinas -->
    <a href="<?= site_url('kegiatan/bukti_rekap/' . $encId) ?>" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1">
      <i class="bi bi-receipt"></i>
      <span>Bukti Rekapitulasi</span>
    </a>

    <!-- Tombol Edit Surat Tugas (Parent Data) -->
    <?php if($can_update && $status !== 'BATAL'): ?>
    <button class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#editHeaderModal">
      <i class="bi bi-pencil-square"></i>
      <span>Edit Surat Tugas</span>
    </button>
    <?php endif; ?>

    <!-- Tombol Tambah Peserta -->
    <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK')) && empty($is_own_scope)): ?>
    <button class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" id="btnTambahPeserta">
      <i class="bi bi-person-plus-fill"></i>
      <span>Tambah Peserta / Pendamping</span>
    </button>
    <?php endif; ?>

    <!-- Tombol Verifikasi (Untuk Verifikator saat status DIAJUKAN) -->
    <?php if(can('kegiatan.verify') && $status === 'DIAJUKAN'): ?>
    <button class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1 js-verif-tolak" data-id="<?= $encId ?>">
      <i class="bi bi-x-circle"></i>
      <span>Tolak</span>
    </button>
    <button class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 js-verif-setujui" data-id="<?= $encId ?>">
      <i class="bi bi-check-circle-fill"></i>
      <span>Setujui</span>
    </button>
    <?php endif; ?>

    <!-- Tombol Ajukan -->
    <?php if($can_submit && in_array($status, array('DRAFT', 'DITOLAK')) && !empty($kegiatan['peserta'])): ?>
    <button class="btn btn-sm btn-success d-inline-flex align-items-center gap-1 js-ajukan-kegiatan" data-id="<?= $encId ?>">
      <i class="bi bi-send-check"></i>
      <span>Ajukan Verifikasi</span>
    </button>
    <?php endif; ?>
  </div>
</div>

<!-- Banner Verifikasi Bagi Verifikator (Wajib Masuk Rincian) -->
<?php if(can('kegiatan.verify') && $status === 'DIAJUKAN'): ?>
<div class="card border-warning shadow-sm mb-4 bg-warning bg-opacity-10">
  <div class="card-body p-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-shield-check text-warning fs-2"></i>
      <div>
        <h6 class="fw-bold mb-0 text-dark">Pemeriksaan &amp; Verifikasi Kegiatan</h6>
        <small class="text-muted">Kegiatan ini menunggu verifikasi Anda. Teliti daftar peserta, pos anggaran, dan rincian biaya di bawah sebelum menyetujui.</small>
      </div>
    </div>
    <div class="d-flex gap-2">
      <button class="btn btn-outline-danger d-flex align-items-center gap-1 js-verif-tolak" data-id="<?= $encId ?>">
        <i class="bi bi-x-circle"></i>
        <span>Tolak Kegiatan</span>
      </button>
      <button class="btn btn-success d-flex align-items-center gap-1 js-verif-setujui" data-id="<?= $encId ?>">
        <i class="bi bi-check-circle-fill"></i>
        <span>Setujui Kegiatan</span>
      </button>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Banner Jika Kegiatan Ditolak -->
<?php if($status === 'DITOLAK' && !empty($kegiatan['catatan_verifikasi'])): ?>
<div class="alert alert-danger d-flex align-items-center gap-2 shadow-sm mb-4">
  <i class="bi bi-exclamation-octagon-fill fs-4"></i>
  <div>
    <strong>Kegiatan Ditolak:</strong> <?= html_escape($kegiatan['catatan_verifikasi']) ?>
    <div class="small text-muted">Silakan perbaiki data kegiatan atau rincian biaya di bawah ini, lalu klik tombol <strong>"Ajukan Verifikasi"</strong> kembali.</div>
  </div>
</div>
<?php endif; ?>

<!-- Parent Card: Identitas Surat Tugas -->
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-file-earmark-text text-primary fs-5"></i>
      <span>Surat Tugas (Parent Data)</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <span class="badge text-bg-primary">Tahun Anggaran <?= (int)($kegiatan['tahun_row']['tahun'] ?? 0) ?></span>
      <code>#<?= html_escape($kegiatan['no_surat_tugas'] ?: 'DRAFT') ?></code>
    </div>
  </div>
  <div class="card-body p-4">
    <div class="row g-3">
      <div class="col-12 col-md-4">
        <small class="text-muted d-block">Nomor Surat Tugas</small>
        <span class="fw-bold fs-6 text-dark"><?= html_escape($kegiatan['no_surat_tugas'] ?: '-') ?></span>
      </div>
      <div class="col-12 col-md-4">
        <small class="text-muted d-block">Tanggal Surat Tugas</small>
        <span class="fw-semibold text-dark"><?= tgl_indo($kegiatan['tanggal_surat_tugas']) ?></span>
      </div>
      <div class="col-12 col-md-4">
        <small class="text-muted d-block">Tempat Pelaksanaan</small>
        <span class="fw-semibold text-dark"><i class="bi bi-geo-alt text-danger me-1"></i><?= html_escape($kegiatan['tempat'] ?: '-') ?></span>
      </div>
      <div class="col-12 col-md-8">
        <small class="text-muted d-block">Tema / Maksud Perjalanan Dinas</small>
        <span class="fw-bold fs-6 text-primary"><?= html_escape($kegiatan['tema'] ?: $kegiatan['nama_kegiatan']) ?></span>
      </div>
      <div class="col-12 col-md-4">
        <small class="text-muted d-block">Waktu Pelaksanaan</small>
        <span class="fw-semibold text-dark">
          <i class="bi bi-calendar-event me-1 text-primary"></i>
          <?= tgl_indo($kegiatan['tanggal_mulai']) ?> s/d <?= tgl_indo($kegiatan['tanggal_selesai']) ?>
        </span>
      </div>
      <div class="col-12 col-md-4">
        <small class="text-muted d-block">Tahun Anggaran yang Digunakan</small>
        <span class="fw-bold text-primary"><i class="bi bi-calendar3 me-1"></i><?= (int)($kegiatan['tahun_row']['tahun'] ?? 0) ?></span>
      </div>
      <div class="col-12 col-md-4">
        <small class="text-muted d-block">Jenis Kegiatan</small>
        <span class="fw-bold text-primary"><i class="bi bi-diagram-3 me-1"></i><?= html_escape($kegiatan['jenis_kegiatan_nama'] ?? 'Perjalanan Dinas') ?></span>
        <?php if (!empty($kegiatan['subjenis_kegiatan'])): ?><span class="small text-muted d-block"><?= html_escape($kegiatan['subjenis_kegiatan']) ?></span><?php endif; ?>
      </div>
    </div>

    <!-- Ringkasan Dana Awal, Realisasi Nota, dan Selisih -->
    <div class="p-3 bg-light rounded-3 mt-3 border d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <span class="text-muted small d-block">Total Dana Awal:</span>
        <h4 class="fw-bold text-primary mb-0"><?= rupiah($kegiatan['total_dana_awal'] ?? 0) ?></h4>
      </div>
      <div>
        <span class="text-muted small d-block">Total Realisasi Nota:</span>
        <h4 class="fw-bold text-success mb-0"><?= rupiah($kegiatan['grand_total']) ?></h4>
      </div>
      <div class="text-md-end">
        <span class="text-muted small d-block">Total Selisih:</span>
        <h4 class="fw-bold text-dark mb-0"><?= rupiah(($kegiatan['total_dana_awal'] ?? 0) - $kegiatan['grand_total']) ?></h4>
      </div>
    </div>
  </div>
</div>

<!-- Section: Alokasi Pagu Kegiatan -->
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-wallet2 text-primary fs-5"></i>
      <span>Alokasi Pagu Kegiatan</span>
      <span class="badge text-bg-light border"><?= count($kegiatan['alokasi_anggaran'] ?? array()) ?> Pos</span>
    </div>
    <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?>
    <button class="btn btn-sm btn-primary" id="btnTambahAlokasi"><i class="bi bi-plus-lg me-1"></i>Tambah Pos</button>
    <?php endif; ?>
  </div>
  <div class="card-body p-3">
    <p class="small text-muted mb-3">Satu kegiatan dapat menggunakan beberapa pos anggaran. Nilai alokasi adalah batas dana kegiatan; realisasi berasal dari biaya umum atau rincian peserta.</p>
    <div class="table-responsive">
      <table class="table table-hover table-sm align-middle mb-0">
        <thead class="table-light">
          <tr><th>Pos Anggaran</th><th>Jenis</th><th class="text-end">Alokasi</th><th class="text-end">Realisasi</th><th class="text-end">Sisa Alokasi</th><th class="text-end">Aksi</th></tr>
        </thead>
        <tbody>
        <?php if(empty($kegiatan['alokasi_anggaran'])): ?>
          <tr><td colspan="6" class="text-center text-muted py-3">Belum ada pos anggaran yang dialokasikan.</td></tr>
        <?php else: foreach($kegiatan['alokasi_anggaran'] as $alokasi): ?>
          <?php $sisaAlokasi = (float)$alokasi['nilai_alokasi'] - (float)$alokasi['realisasi']; ?>
          <tr>
            <td><strong><?= html_escape($alokasi['pos_nama']) ?></strong><div><code><?= html_escape($alokasi['pos_kode']) ?></code></div><?php if($alokasi['keterangan']): ?><small class="text-muted"><?= html_escape($alokasi['keterangan']) ?></small><?php endif; ?></td>
            <td><span class="badge text-bg-light border"><?= html_escape($alokasi['jenis_anggaran_nama'] ?: $alokasi['pos_tipe']) ?></span></td>
            <td class="text-end fw-semibold"><?= rupiah($alokasi['nilai_alokasi']) ?></td>
            <td class="text-end text-success"><?= rupiah($alokasi['realisasi']) ?></td>
            <td class="text-end <?= $sisaAlokasi < 0 ? 'text-danger' : 'text-primary' ?>"><?= rupiah($sisaAlokasi) ?></td>
            <td class="text-end text-nowrap">
              <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?>
              <button type="button" class="btn btn-sm btn-outline-primary js-edit-alokasi" data-alokasi='<?= json_encode(array(
                'id' => encrypt_id($alokasi['id']), 'pos_anggaran_id' => encrypt_id($alokasi['pos_anggaran_id']),
                'nilai_alokasi' => (float)$alokasi['nilai_alokasi'], 'keterangan' => $alokasi['keterangan']
              ), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>'><i class="bi bi-pencil"></i></button>
              <?php endif; ?>
              <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?><button type="button" class="btn btn-sm btn-outline-success js-tambah-biaya-umum" data-id="<?= encrypt_id($alokasi['id']) ?>" data-nama="<?= html_escape($alokasi['pos_nama']) ?>"><i class="bi bi-receipt"></i></button><?php endif; ?>
              <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?><button type="button" class="btn btn-sm btn-outline-danger js-hapus-alokasi" data-id="<?= encrypt_id($alokasi['id']) ?>"><i class="bi bi-trash"></i></button><?php endif; ?>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Section: Daftar Peserta & Pendamping -->
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-people text-primary fs-5"></i>
        <span>Peserta / Rincian Pelaksana Kegiatan</span>
      <span class="badge text-bg-light border"><?= count($kegiatan['peserta']) ?> Orang</span>
    </div>
    <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK')) && empty($is_own_scope)): ?>
    <button class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1" id="btnTambahPeserta2">
      <i class="bi bi-plus-lg"></i>
      <span>Tambah Peserta</span>
    </button>
    <?php endif; ?>
  </div>
  <div class="card-body p-3">
    <?php if(empty($kegiatan['peserta'])): ?>
      <div class="text-center py-5">
        <i class="bi bi-person-x text-muted" style="font-size: 3rem;"></i>
        <h6 class="fw-bold text-secondary mt-2">Belum ada peserta yang ditambahkan</h6>
        <p class="text-muted small">Klik tombol <strong>"Tambah Peserta / Pendamping"</strong> di atas untuk menambahkan anggota DPRD atau pendamping beserta rincian anggarannya.</p>
      </div>
    <?php else: ?>
      <?php foreach($kegiatan['peserta'] as $idx => $p): ?>
      <?php 
        $encKpId = encrypt_id($p['id']); 
        $encPenerimaId = encrypt_id($p['penerima_id']);
        $isDprd = $p['tipe'] === 'DPRD';
        $danaAwalPeserta = (float)($p['dana_awal'] ?? 0);
        $realisasiNotaPeserta = (float)($p['realisasi_nota'] ?? $p['total_biaya'] ?? 0);
        $selisihDanaAwalPeserta = (float)($p['selisih_dana_awal'] ?? ($danaAwalPeserta - $realisasiNotaPeserta));
      ?>
      <div class="border rounded-3 p-3 mb-3 bg-white shadow-sm participant-item" id="peserta_<?= $encKpId ?>">
        <!-- Header Peserta -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pb-2 mb-2 border-bottom">
          <div class="d-flex align-items-center gap-2">
            <span class="badge rounded-pill bg-light text-dark border"><?= $idx + 1 ?></span>
            <h6 class="fw-bold text-dark mb-0"><?= html_escape($p['nama']) ?></h6>
            <span class="badge <?= $isDprd ? 'text-bg-primary' : 'text-bg-secondary' ?>"><?= html_escape($p['tipe']) ?></span>
            <span class="text-muted small"><?= html_escape($p['jabatan'] ?: '-') ?></span>
          </div>
          <div class="d-flex align-items-center gap-3">
            <div class="text-end">
              <span class="small text-muted d-block">
                Pos Anggaran: <strong class="text-dark"><?= html_escape($p['pos_nama'] ?: ($isDprd ? 'Pos DPRD: ' . $p['nama'] : 'Master Anggaran Aktif')) ?></strong>
              </span>
              <?php if(!empty($p['alasan_pengalihan'])): ?>
                <span class="badge text-bg-light border text-dark" title="Keterangan anggaran: <?= html_escape($p['alasan_pengalihan']) ?>">
                  <i class="bi bi-chat-left-text me-1"></i>Keterangan tersedia
                </span>
              <?php endif; ?>
            </div>
            <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?>
            <div class="btn-group btn-group-sm">
              <?php
                $pData = array(
                  'id'                => $encKpId,
                  'penerima_id'       => $encPenerimaId,
                  'pos_anggaran_id'   => !empty($p['pos_anggaran_id']) ? encrypt_id($p['pos_anggaran_id']) : '',
                  'alasan_pengalihan' => $p['alasan_pengalihan'] ?? '',
                  'dana_awal'         => $danaAwalPeserta,
                  'nama'              => $p['nama'],
                  'tipe'              => $p['tipe'],
                  'sumber_dana_id'    => $p['sumber_dana_id'],
                  'biaya'             => array_map(function($b){
                    return array(
                      'jenis_biaya_id' => $b['jenis_biaya_id'],
                      'keterangan'     => $b['keterangan'] ?? '',
                      'volume'         => (float)$b['volume'],
                      'tarif'          => (float)$b['tarif']
                    );
                  }, $p['biaya'])
                );
              ?>
              <button class="btn btn-outline-primary js-edit-peserta" data-peserta='<?= json_encode($pData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>' title="Edit Peserta &amp; Biaya">
                <i class="bi bi-pencil"></i> Edit
              </button>
              <button class="btn btn-outline-danger js-hapus-peserta" data-id="<?= $encKpId ?>" data-nama="<?= html_escape($p['nama']) ?>" title="Hapus Peserta">
                <i class="bi bi-trash"></i> Hapus
              </button>
            </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-12 col-md-4">
            <div class="p-2 rounded border bg-primary bg-opacity-10">
              <span class="small text-muted d-block">Dana Awal</span>
              <strong class="text-primary"><?= rupiah($danaAwalPeserta) ?></strong>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-2 rounded border bg-success bg-opacity-10">
              <span class="small text-muted d-block">Realisasi Nota</span>
              <strong class="text-success"><?= rupiah($realisasiNotaPeserta) ?></strong>
            </div>
          </div>
          <div class="col-12 col-md-4">
            <div class="p-2 rounded border <?= $selisihDanaAwalPeserta < 0 ? 'bg-danger bg-opacity-10' : 'bg-warning bg-opacity-10' ?>">
              <span class="small text-muted d-block">Selisih / Pengembalian</span>
              <strong class="<?= $selisihDanaAwalPeserta < 0 ? 'text-danger' : 'text-dark' ?>"><?= rupiah($selisihDanaAwalPeserta) ?></strong>
            </div>
          </div>
        </div>

        <!-- Tabel Rincian Biaya Peserta -->
        <div class="table-responsive">
          <table class="table table-sm table-bordered align-middle mb-0">
            <thead class="table-light">
              <tr class="small">
                <th style="width: 40px;" class="text-center">No</th>
                <th>Komponen / Jenis Biaya</th>
                <th style="width: 120px;" class="text-center">Volume</th>
                <th style="width: 170px;" class="text-end">Tarif Satuan</th>
                <th style="width: 180px;" class="text-end">Subtotal</th>
              </tr>
            </thead>
            <tbody>
              <?php if(empty($p['biaya'])): ?>
                <tr>
                  <td colspan="5" class="text-center text-muted small py-2">Belum ada rincian komponen biaya.</td>
                </tr>
              <?php else: ?>
                <?php foreach($p['biaya'] as $bIdx => $b): ?>
                <tr>
                  <td class="text-center text-muted small"><?= $bIdx + 1 ?></td>
                  <td class="fw-medium">
                    <?= html_escape($b['jenis_biaya_nama']) ?>
                    <?php if(!empty($b['keterangan'])): ?><div class="small text-primary"><i class="bi bi-info-circle me-1"></i><?= html_escape($b['keterangan']) ?></div><?php endif; ?>
                    <?php if($b['satuan']): ?>
                      <span class="text-muted small">(<?= html_escape($b['satuan']) ?>)</span>
                    <?php endif; ?>
                    <?php if(!empty($b['jenis_biaya_keterangan'])): ?>
                      <div class="small text-muted fst-italic"><?= html_escape($b['jenis_biaya_keterangan']) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="text-center"><?= (float)$b['volume'] ?></td>
                  <td class="text-end"><?= rupiah($b['tarif']) ?></td>
                  <td class="text-end fw-semibold text-primary"><?= rupiah($b['volume'] * $b['tarif']) ?></td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<!-- Modal 1: Edit Surat Tugas (Parent Data) -->
<div class="modal fade" id="editHeaderModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form class="js-ajax-form" action="<?= site_url('kegiatan/save_header') ?>">
        <input type="hidden" name="id" value="<?= $encId ?>">
        <input type="hidden" name="tahun_anggaran_id" value="<?= (int)$kegiatan['tahun_anggaran_id'] ?>">

        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold">
            <i class="bi bi-pencil-square text-primary me-1"></i> Edit Surat Tugas (Parent Data)
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">No. Surat Tugas <span class="text-danger">*</span></label>
              <input class="form-control" name="no_surat_tugas" value="<?= html_escape($kegiatan['no_surat_tugas']) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tanggal Surat Tugas</label>
              <input class="form-control" type="date" name="tanggal_surat_tugas" value="<?= html_escape($kegiatan['tanggal_surat_tugas']) ?>">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tanggal Pelaksanaan Mulai <span class="text-danger">*</span></label>
              <input class="form-control" type="date" name="tanggal_mulai" value="<?= html_escape($kegiatan['tanggal_mulai']) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tanggal Pelaksanaan Selesai <span class="text-danger">*</span></label>
              <input class="form-control" type="date" name="tanggal_selesai" value="<?= html_escape($kegiatan['tanggal_selesai']) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tempat / Kota Pelaksanaan <span class="text-danger">*</span></label>
              <input class="form-control" name="tempat" value="<?= html_escape($kegiatan['tempat']) ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Jenis Kegiatan <span class="text-danger">*</span></label>
              <select class="form-select" name="jenis_kegiatan_id" required>
                <?php foreach (($jenis_kegiatan ?? array()) as $jk): ?>
                  <option value="<?= (int)$jk['id'] ?>" <?= (int)$jk['id'] === (int)($kegiatan['jenis_kegiatan_id'] ?? 0) ? 'selected' : '' ?>><?= html_escape($jk['nama']) ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Subjenis / Kategori</label>
              <input class="form-control" name="subjenis_kegiatan" value="<?= html_escape($kegiatan['subjenis_kegiatan'] ?? '') ?>" placeholder="Contoh: Rapat Komisi I">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Status</label>
              <select class="form-select" name="status">
                <option value="DRAFT" <?= $status === 'DRAFT' ? 'selected' : '' ?>>DRAFT</option>
                <option value="AKTIF" <?= $status === 'AKTIF' ? 'selected' : '' ?>>AKTIF</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Tema / Maksud Perjalanan Dinas <span class="text-danger">*</span></label>
              <textarea class="form-control" name="tema" rows="2" required><?= html_escape($kegiatan['tema']) ?></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-save"></i>
            <span>Simpan Perubahan</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Alokasi Pagu Kegiatan -->
<div class="modal fade" id="modalAlokasi" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formAlokasi">
        <input type="hidden" name="kegiatan_id" value="<?= $encId ?>">
        <input type="hidden" name="id" id="alokasiIdInput">
        <div class="modal-header bg-light"><h5 class="modal-title fw-bold" id="modalAlokasiTitle"><i class="bi bi-wallet2 text-primary me-1"></i> Alokasi Pagu Kegiatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
          <div class="mb-3">
            <label class="form-label small fw-semibold">Pos Anggaran <span class="text-danger">*</span></label>
            <select class="form-select" name="pos_anggaran_id" id="alokasiPosSelect" required>
              <option value="">-- Pilih Pos Anggaran --</option>
              <?php foreach($pos_anggaran as $pp): ?>
              <option value="<?= encrypt_id($pp['id']) ?>"><?= html_escape($pp['nama']) ?> (Sisa: <?= rupiah($pp['sisa']) ?>)</option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-semibold">Nilai Alokasi / Pagu Kegiatan <span class="text-danger">*</span></label>
            <input type="text" class="form-control input-rupiah" name="nilai_alokasi" id="inpNilaiAlokasi" required placeholder="Contoh: 100.000.000">
          </div>
          <div>
            <label class="form-label small fw-semibold">Keterangan</label>
            <textarea class="form-control" name="keterangan" id="inpKeteranganAlokasi" rows="3" placeholder="Contoh: Alokasi Rapat Komisi I bulan Januari"></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary"><i class="bi bi-save me-1"></i>Simpan Alokasi</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Biaya Umum Non-Peserta -->
<div class="modal fade" id="modalBiayaUmum" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formBiayaUmum">
        <input type="hidden" name="kegiatan_id" value="<?= $encId ?>">
        <input type="hidden" name="kegiatan_anggaran_id" id="biayaUmumAlokasiId">
        <div class="modal-header bg-light"><h5 class="modal-title fw-bold"><i class="bi bi-receipt text-success me-1"></i> Tambah Biaya Kegiatan</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body p-4">
          <p class="small text-muted">Dipakai untuk rapat/fasilitas yang tidak dibebankan kepada peserta perjalanan dinas.</p>
          <div class="mb-3"><label class="form-label small fw-semibold">Alokasi Anggaran</label><input class="form-control" id="biayaUmumAlokasiLabel" readonly></div>
          <div class="mb-3"><label class="form-label small fw-semibold">Komponen Biaya <span class="text-danger">*</span></label><select class="form-select" name="jenis_biaya_id" required><option value="">-- Pilih Komponen --</option><?php foreach($jenis_biaya as $jb): if(!$jb['is_active']) continue; ?><option value="<?= (int)$jb['id'] ?>"><?= html_escape($jb['nama']) ?> (<?= html_escape($jb['satuan'] ?: '-') ?>)</option><?php endforeach; ?></select></div>
          <div class="row g-3"><div class="col-5"><label class="form-label small fw-semibold">Volume</label><input type="number" min="0.01" step="0.01" class="form-control" name="volume" value="1" required></div><div class="col-7"><label class="form-label small fw-semibold">Tarif Satuan</label><input type="text" class="form-control input-rupiah" name="tarif" required placeholder="0"></div></div>
          <div class="mt-3"><label class="form-label small fw-semibold">Keterangan Detail</label><textarea class="form-control" name="keterangan" rows="2" placeholder="Contoh: Uang Harian 2x untuk keberangkatan dan kepulangan"></textarea></div>
        </div>
        <div class="modal-footer bg-light"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-success"><i class="bi bi-save me-1"></i>Simpan Biaya</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Modal 2: Input Peserta / Pendamping & Rincian Biaya -->
<div class="modal fade" id="modalPeserta" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <form id="formPeserta">
        <input type="hidden" name="kegiatan_id" value="<?= $encId ?>">
        <input type="hidden" name="id" id="pesertaIdInput">

        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold" id="modalPesertaTitle">
            <i class="bi bi-person-plus-fill text-primary me-1"></i> Tambah Peserta / Pendamping
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <!-- Pilih Penerima & Pos Anggaran -->
          <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold">Pilih Peserta (Anggota DPRD / ASN Pendamping) <span class="text-danger">*</span></label>
              <select class="form-select select2-peserta" name="penerima_id" id="penerimaSelect" required <?= !empty($is_own_scope) ? 'data-own-only="1"' : '' ?>>
                <option value="">-- Pilih Anggota DPRD / ASN Pendamping --</option>
                <optgroup label="Anggota DPRD">
                  <?php foreach($penerima as $p): ?>
                    <?php if($p['tipe'] === 'DPRD' && empty($is_own_scope)): ?>
                    <option value="<?= encrypt_id($p['id']) ?>" data-tipe="DPRD">
                      <?= html_escape($p['nama']) ?> — <?= html_escape($p['jabatan'] ?: 'Anggota') ?> (DPRD)
                    </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </optgroup>
                <optgroup label="Pendamping / ASN">
                  <?php foreach($penerima as $p): ?>
                    <?php if($p['tipe'] !== 'DPRD' && (!$is_own_scope || (int)$p['id'] === (int)($currentUser['penerima_id'] ?? 0))): ?>
                    <option value="<?= encrypt_id($p['id']) ?>" data-tipe="PENDAMPING">
                      <?= html_escape($p['nama']) ?> — <?= html_escape($p['jabatan'] ?: 'ASN Pendamping') ?> (PENDAMPING)
                    </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </optgroup>
              </select>
            </div>

            <!-- Anggaran yang digunakan peserta -->
            <div class="col-12 col-md-6" id="boxPosAnggaran" style="display: none;">
              <label class="form-label small fw-bold">Anggaran yang Dipakai <span class="text-danger">*</span></label>
              <select class="form-select" name="pos_anggaran_id" id="posAnggaranSelect">
                <option value="">-- Pilih Pos Anggaran --</option>
                <?php foreach(array('DPRD', 'PENDAMPING') as $tipePos): ?>
                  <optgroup label="<?= $tipePos === 'DPRD' ? 'Anggaran DPRD' : 'Anggaran Pendamping / Jabatan' ?>">
                    <?php foreach($pos_anggaran as $pp): if($pp['tipe'] !== $tipePos) continue; ?>
                    <option value="<?= encrypt_id($pp['id']) ?>"
                            data-pemegang-id="<?= $pp['penugasan_penerima_id'] ?>"
                            data-pemegang-nama="<?= html_escape($pp['pemegang_nama'] ?? '-') ?>"
                            data-pos-nama="<?= html_escape($pp['nama']) ?>"
                            data-pos-tipe="<?= html_escape($pp['tipe']) ?>"
                            data-sisa="<?= (float)$pp['sisa'] ?>">
                      <?= html_escape($pp['nama']) ?> (Sisa: <?= rupiah($pp['sisa']) ?>, Pemegang: <?= html_escape($pp['pemegang_nama'] ?? 'Melekat') ?>)
                    </option>
                    <?php endforeach; ?>
                  </optgroup>
                <?php endforeach; ?>
              </select>
              <div class="form-text small text-muted" id="hintPosAnggaran">
                Default mengikuti anggaran bawaan peserta. Pendamping dapat memakai anggaran DPRD jika dipilih.
              </div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label small fw-bold">Dana Awal / Panjar (Rp) <span class="text-danger">*</span></label>
              <input type="text" class="form-control input-rupiah" name="dana_awal" id="inpDanaAwal"
                     placeholder="Contoh: 100.000.000" inputmode="numeric" required>
              <div class="form-text small text-muted">Dana yang diberikan atau dialokasikan di muka untuk peserta ini.</div>
            </div>

            <!-- Keterangan anggaran selalu wajib, baik pos sendiri maupun pos lain -->
            <div class="col-12" id="boxAlasanPengalihan">
              <div class="p-3 border border-info rounded-3 bg-info bg-opacity-10">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-chat-left-text-fill text-info fs-5"></i>
                  <strong class="text-dark">Keterangan Anggaran <span class="text-danger">*</span></strong>
                </div>
                <p class="small text-muted mb-2">
                  Jelaskan sumber penggunaan anggaran atau catatan pembebanan dana. Keterangan wajib diisi untuk semua peserta.
                </p>
                <textarea class="form-control form-control-sm" name="alasan_pengalihan" id="inpAlasanPengalihan" rows="2" required
                          placeholder="Contoh: Anggaran kegiatan melekat pada pos anggota yang bersangkutan."></textarea>
              </div>
            </div>
          </div>

          <!-- Section Rincian Komponen Biaya -->
          <div class="border rounded-3 p-3 bg-light">
            <div class="d-flex justify-content-between align-items-center mb-3">
              <h6 class="fw-bold text-dark mb-0">
                <i class="bi bi-wallet2 text-primary me-1"></i> Rincian Komponen Biaya Perjalanan Dinas
              </h6>
              <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahBarisBiaya">
                <i class="bi bi-plus-circle me-1"></i> Tambah Komponen
              </button>
            </div>

            <div class="table-responsive">
              <table class="table table-bordered bg-white align-middle" id="tableRincianBiaya">
                <thead class="table-light small">
                  <tr>
                    <th>Komponen / Jenis Biaya</th>
                    <th style="width: 110px;">Volume</th>
                    <th style="min-width: 220px;">Tarif Satuan (Rp) &amp; Pembilang</th>
                    <th style="width: 170px;" class="text-end">Subtotal (Rp)</th>
                    <th style="width: 50px;"></th>
                  </tr>
                </thead>
                <tbody id="tbodyBiayaRows"></tbody>
              </table>
            </div>

            <!-- Total Per Peserta -->
            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
              <div>
                <span class="fw-semibold text-muted d-block">Realisasi Nota / Rincian:</span>
                <h5 class="fw-bold text-success mb-0" id="totalPesertaModal">Rp 0</h5>
              </div>
              <div class="text-end">
                <span class="small text-muted d-block">Selisih Dana Awal</span>
                <strong class="fw-bold text-dark" id="selisihDanaModal">Rp 0</strong>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-check-circle"></i>
            <span>Simpan Peserta</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
window.MasterJenisBiaya = <?= json_encode($jenis_biaya, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;

$(function(){
  // Template baris biaya baru
  function createBiayaRow(jenisId, volume, tarif, keterangan, defaultOnly) {
    var opts = '<option value="">— Tanpa jenis biaya (Biaya Umum) —</option>';
    (window.MasterJenisBiaya || []).forEach(function(j){
      if (defaultOnly && String(j.tampil_di_kegiatan) !== '1' && String(j.id) !== String(jenisId)) return;
      opts += '<option value="' + j.id + '" data-keterangan="' + escapeHtml(j.keterangan || '') + '" ' + (String(j.id) === String(jenisId) ? 'selected' : '') + '>' + escapeHtml(j.nama) + '</option>';
    });

    var vol = volume || 1;
    var tar = tarif || 0;
    var detailKeterangan = keterangan || '';
    var sub = vol * tar;

    var rowHtml = $(
      '<tr>' +
        '<td>' +
          '<select class="form-select form-select-sm row-jenis">' + opts + '</select>' +
          '<div class="form-text small text-muted" style="font-size:0.7rem">Opsional</div>' +
          '<div class="row-jenis-keterangan small text-muted fst-italic mt-1"></div>' +
          '<textarea class="form-control form-control-sm row-keterangan mt-1" rows="1" placeholder="Keterangan detail, contoh: 2x perjalanan"></textarea>' +
        '</td>' +
        '<td>' +
          '<input type="number" step="0.01" min="0" class="form-control form-control-sm text-center row-volume" value="' + vol + '">' +
        '</td>' +
        '<td>' +
          '<div class="input-group input-group-sm">' +
            '<span class="input-group-text bg-light">Rp</span>' +
            '<input type="text" class="form-control form-control-sm row-tarif-display input-rupiah" value="' + (tar ? tar.toLocaleString('id-ID') : '') + '" placeholder="0" autocomplete="off">' +
            '<input type="hidden" class="row-tarif" value="' + tar + '">' +
          '</div>' +
          '<div class="row-terbilang text-success fst-italic mt-1" style="font-size:0.7rem; line-height: 1.25;">' + (tar ? terbilang(tar) : '') + '</div>' +
        '</td>' +
        '<td class="text-end fw-bold text-primary row-subtotal">' + formatRupiah(sub) + '</td>' +
        '<td class="text-center">' +
          '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row">&times;</button>' +
        '</td>' +
      '</tr>'
    );
    var selectedOpt = rowHtml.find('.row-jenis option:selected');
    rowHtml.find('.row-jenis-keterangan').text(selectedOpt.data('keterangan') || '');
    rowHtml.find('.row-keterangan').val(detailKeterangan);
    return rowHtml;
  }

  function appendDefaultBiayaRows() {
    var defaults = (window.MasterJenisBiaya || []).filter(function(j){
      return String(j.tampil_di_kegiatan) === '1';
    });
    if (!defaults.length) {
      $('#tbodyBiayaRows').append(createBiayaRow('', 1, 0, '', false));
      return;
    }
    defaults.forEach(function(j){
      $('#tbodyBiayaRows').append(createBiayaRow(j.id, 1, 0, '', true));
    });
  }

  $(document).on('change', '.row-jenis', function(){
    var keterangan = $(this).find('option:selected').data('keterangan') || '';
    $(this).closest('td').find('.row-jenis-keterangan').text(keterangan);
  });

  function hitungTotalModal() {
    var total = 0;
    $('#tbodyBiayaRows tr').each(function(){
      var vol = parseFloat($(this).find('.row-volume').val()) || 0;
      var tar = parseFloat($(this).find('.row-tarif').val()) || 0;
      var sub = vol * tar;
      $(this).find('.row-subtotal').text(formatRupiah(sub));
      total += sub;
    });
    $('#totalPesertaModal').text(formatRupiah(total));
    var danaAwal = parseNominal($('#inpDanaAwal').val());
    var selisih = danaAwal - total;
    $('#selisihDanaModal').text(formatRupiah(selisih));
    $('#selisihDanaModal').toggleClass('text-danger', selisih < 0).toggleClass('text-dark', selisih >= 0);
  }

  function parseNominal(value) {
    var raw = String(value || '').replace(/\D/g, '');
    return raw ? parseFloat(raw) : 0;
  }

  function formatNominalInput(value) {
    var nominal = parseNominal(value);
    return nominal > 0 ? nominal.toLocaleString('id-ID') : '';
  }

  var defaultPosId = '';
  var defaultPosNama = '';
  var currentTipePeserta = '';

  function normalizePosName(value) {
    return String(value || '').trim().replace(/\s+/g, ' ').toLowerCase();
  }

  function hideAlasanPengalihan(clearValue) {
    $('#boxAlasanPengalihan').show();
    $('#inpAlasanPengalihan').prop('required', true);
    if (clearValue !== false) $('#inpAlasanPengalihan').val('');
  }

  function showAlasanPengalihan() {
    $('#boxAlasanPengalihan').show();
    $('#inpAlasanPengalihan').prop('required', true);
  }

  function handlePenerimaChange() {
    var opt = $('#penerimaSelect').find(':selected');
    var tipe = opt.data('tipe');
    var penerimaId = $('#penerimaSelect').val();
    currentTipePeserta = tipe || '';

    if (!penerimaId) {
      $('#boxPosAnggaran').hide();
      showAlasanPengalihan();
      return;
    }

    $('#boxPosAnggaran').show();
    App.ajax({
      url: window.APP.baseUrl + 'pos/pos_default?penerima_id=' + penerimaId + '&tahun_id=<?= (int)$kegiatan['tahun_anggaran_id'] ?>',
      method: 'GET'
    }).done(function(res){
      if (res.data && res.data.pos) {
        defaultPosId = res.data.pos.id_enc;
        defaultPosNama = normalizePosName(res.data.pos.nama);
        if (!$('#posAnggaranSelect').val() || $('#posAnggaranSelect').val() === defaultPosId) {
          $('#posAnggaranSelect').val(defaultPosId).trigger('change');
        } else {
          checkPengalihanPos();
        }
      } else {
        defaultPosId = '';
        defaultPosNama = '';
        checkPengalihanPos();
      }
    });
  }

  function checkPengalihanPos() {
    // Keterangan selalu wajib, baik menggunakan anggaran bawaan maupun pos lain.
    showAlasanPengalihan();
  }

  $('#penerimaSelect').on('change', handlePenerimaChange);
  $('#posAnggaranSelect').on('change', checkPengalihanPos);

  // Inisialisasi Select2 pada dropdown modal saat ditampilkan
  $('#modalPeserta').on('shown.bs.modal', function(){
    if ($.fn.select2) {
      $('#penerimaSelect').select2({
        dropdownParent: $('#modalPeserta'),
        width: '100%',
        placeholder: '-- Pilih Anggota DPRD / ASN Pendamping --'
      });
      $('#posAnggaranSelect').select2({
        dropdownParent: $('#modalPeserta'),
        width: '100%',
        placeholder: '-- Pilih Pos Anggaran --'
      });
    }
  });

  // Buka Modal Tambah Peserta
  $('#btnTambahPeserta, #btnTambahPeserta2').on('click', function(){
    $('#formPeserta')[0].reset();
    $('#pesertaIdInput').val('');
    defaultPosId = '';
    defaultPosNama = '';
    currentTipePeserta = '';
    $('#boxPosAnggaran').hide();
    hideAlasanPengalihan();
    if ($.fn.select2 && $('#penerimaSelect').hasClass("select2-hidden-accessible")) {
      $('#penerimaSelect').val('').trigger('change');
    }
    if ($.fn.select2 && $('#posAnggaranSelect').hasClass("select2-hidden-accessible")) {
      $('#posAnggaranSelect').val('').trigger('change');
    }
    $('#modalPesertaTitle').html('<i class="bi bi-person-plus-fill text-primary me-1"></i> Tambah Peserta / Pendamping');
    $('#tbodyBiayaRows').empty();
    appendDefaultBiayaRows();
    $('#inpDanaAwal').val('');
    hitungTotalModal();
    bootstrap.Modal.getOrCreateInstance('#modalPeserta').show();
  });

  // Edit Peserta & Rincian Biaya
  $(document).on('click', '.js-edit-peserta', function(){
    var p = $(this).data('peserta');
    $('#formPeserta')[0].reset();
    hideAlasanPengalihan();
    $('#pesertaIdInput').val(p.id);
    $('#inpDanaAwal').val(formatNominalInput(p.dana_awal || 0));
    currentTipePeserta = p.tipe || '';
    $('#penerimaSelect').val(p.penerima_id);
    if ($.fn.select2 && $('#penerimaSelect').hasClass("select2-hidden-accessible")) {
      $('#penerimaSelect').trigger('change');
    }

    $('#boxPosAnggaran').show();
    if (p.pos_anggaran_id) {
      $('#posAnggaranSelect').val(p.pos_anggaran_id);
      if ($.fn.select2 && $('#posAnggaranSelect').hasClass("select2-hidden-accessible")) {
        $('#posAnggaranSelect').trigger('change');
      }
    }
    if (p.alasan_pengalihan) $('#inpAlasanPengalihan').val(p.alasan_pengalihan);
    showAlasanPengalihan();

    $('#modalPesertaTitle').html('<i class="bi bi-pencil-square text-primary me-1"></i> Edit Peserta: ' + escapeHtml(p.nama));

    $('#tbodyBiayaRows').empty();
    if (p.biaya && p.biaya.length > 0) {
      p.biaya.forEach(function(b){
        $('#tbodyBiayaRows').append(createBiayaRow(b.jenis_biaya_id, b.volume, b.tarif, b.keterangan || ''));
      });
    } else {
      appendDefaultBiayaRows();
    }
    hitungTotalModal();
    bootstrap.Modal.getOrCreateInstance('#modalPeserta').show();
  });

  // Tambah baris biaya
  $('#btnTambahBarisBiaya').on('click', function(){
    $('#tbodyBiayaRows').append(createBiayaRow('', 1, 0, '', false));
  });

  // Hapus baris biaya
  $(document).on('click', '.btn-remove-row', function(){
    if ($('#tbodyBiayaRows tr').length > 1) {
      $(this).closest('tr').remove();
    } else {
      $(this).closest('tr').find('.row-tarif-display').val('').trigger('input');
    }
    hitungTotalModal();
  });

  // Input volume & tarif live calculation & pembilang text
  $(document).on('input', '.row-volume', hitungTotalModal);
  $(document).on('input', '#inpDanaAwal', function(){
    var raw = $(this).val().replace(/\D/g, '');
    $(this).val(raw ? parseInt(raw, 10).toLocaleString('id-ID') : '');
    hitungTotalModal();
  });
  $(document).on('input', '.row-tarif-display', function(){
    var raw = $(this).val().replace(/\D/g, '');
    var num = raw ? parseInt(raw, 10) : 0;
    $(this).val(raw ? num.toLocaleString('id-ID') : '');
    $(this).siblings('.row-tarif').val(num);
    var kata = raw ? terbilang(num) : '';
    $(this).closest('td').find('.row-terbilang').text(kata);
    hitungTotalModal();
  });

  // Submit Simpan Peserta
  $('#formPeserta').on('submit', function(e){
    e.preventDefault();
    var penerimaId = $('#penerimaSelect').val();
    if (!penerimaId) {
      App.toast('Silakan pilih penerima (Anggota DPRD / Pendamping).', 'warning');
      return;
    }

    var biayaList = [];
    $('#tbodyBiayaRows tr').each(function(){
      var jenisId = $(this).find('.row-jenis').val() || null;
      var vol = parseFloat($(this).find('.row-volume').val()) || 0;
      var tar = parseFloat($(this).find('.row-tarif').val()) || 0;
      if (vol > 0 && tar > 0) {
        biayaList.push({
          jenis_biaya_id: jenisId,
          keterangan: $.trim($(this).find('.row-keterangan').val() || ''),
          volume: vol,
          tarif: tar
        });
      }
    });

    var posId = $('#posAnggaranSelect').val() || null;
    var alasan = $.trim($('#inpAlasanPengalihan').val() || '');
    var danaAwal = parseNominal($('#inpDanaAwal').val());
    if (!danaAwal && $('#inpDanaAwal').val().trim() !== '0') {
      App.toast('Dana awal wajib diisi.', 'warning');
      $('#inpDanaAwal').focus();
      return;
    }
    if (!alasan) {
      App.toast('Keterangan anggaran wajib diisi.', 'warning');
      $('#inpAlasanPengalihan').focus();
      return;
    }

    var payload = {
      kegiatan_id:       $('[name="kegiatan_id"]').val(),
      id:                $('#pesertaIdInput').val() || null,
      penerima_id:       penerimaId,
      pos_anggaran_id:   posId,
      alasan_pengalihan: alasan,
      dana_awal:         danaAwal,
      sumber_dana_id:    null,
      biaya:             biayaList
    };

    App.ajax({
      url: window.APP.baseUrl + 'kegiatan/save_peserta',
      data: JSON.stringify(payload),
      contentType: 'application/json'
    }).done(function(r){
      if (r.status) {
        bootstrap.Modal.getOrCreateInstance('#modalPeserta').hide();
        setTimeout(function(){ location.reload(); }, 500);
      }
    });
  });

  // Alokasi pagu kegiatan multi-pos
  $('#btnTambahAlokasi').on('click', function(){
    $('#formAlokasi')[0].reset();
    $('#alokasiIdInput').val('');
    $('#modalAlokasiTitle').html('<i class="bi bi-wallet2 text-primary me-1"></i> Alokasi Pagu Kegiatan');
    bootstrap.Modal.getOrCreateInstance('#modalAlokasi').show();
  });

  $(document).on('click', '.js-edit-alokasi', function(){
    var a = $(this).data('alokasi');
    $('#alokasiIdInput').val(a.id);
    $('#alokasiPosSelect').val(a.pos_anggaran_id);
    $('#inpNilaiAlokasi').val(formatNominalInput(a.nilai_alokasi || 0));
    $('#inpKeteranganAlokasi').val(a.keterangan || '');
    $('#modalAlokasiTitle').html('<i class="bi bi-pencil-square text-primary me-1"></i> Edit Alokasi Pagu');
    bootstrap.Modal.getOrCreateInstance('#modalAlokasi').show();
  });

  $('#formAlokasi').on('submit', function(e){
    e.preventDefault();
    var nilai = parseNominal($('#inpNilaiAlokasi').val());
    if (!$('#alokasiPosSelect').val() || nilai < 0) {
      App.toast('Pos dan nilai alokasi wajib diisi.', 'warning');
      return;
    }
    App.ajax({
      url: window.APP.baseUrl + 'kegiatan/save_alokasi',
      data: JSON.stringify({
        kegiatan_id: $('[name="kegiatan_id"]').first().val(),
        id: $('#alokasiIdInput').val() || null,
        pos_anggaran_id: $('#alokasiPosSelect').val(),
        nilai_alokasi: nilai,
        keterangan: $.trim($('#inpKeteranganAlokasi').val() || '')
      }),
      contentType: 'application/json'
    }).done(function(r){
      if (r.status) {
        bootstrap.Modal.getOrCreateInstance('#modalAlokasi').hide();
        setTimeout(function(){ location.reload(); }, 400);
      }
    });
  });

  $(document).on('click', '.js-hapus-alokasi', function(){
    var id = $(this).data('id');
    Swal.fire({
      title: 'Hapus alokasi?',
      text: 'Alokasi hanya dapat dihapus jika belum dipakai peserta atau biaya kegiatan.',
      icon: 'warning', showCancelButton: true,
      confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626'
    }).then(function(r){
      if (!r.isConfirmed) return;
      App.ajax({url: window.APP.baseUrl + 'kegiatan/delete_alokasi/' + id}).done(function(res){
        if (res.status) setTimeout(function(){ location.reload(); }, 400);
      });
    });
  });

  $(document).on('click', '.js-tambah-biaya-umum', function(){
    $('#formBiayaUmum')[0].reset();
    $('#biayaUmumAlokasiId').val($(this).data('id'));
    $('#biayaUmumAlokasiLabel').val($(this).data('nama') || 'Alokasi terpilih');
    bootstrap.Modal.getOrCreateInstance('#modalBiayaUmum').show();
  });

  $('#formBiayaUmum').on('submit', function(e){
    e.preventDefault();
    var tarif = parseNominal($('#formBiayaUmum [name="tarif"]').val());
    var volume = parseFloat($('#formBiayaUmum [name="volume"]').val()) || 0;
    if (!$('#biayaUmumAlokasiId').val() || !$('#formBiayaUmum [name="jenis_biaya_id"]').val() || volume <= 0 || tarif < 0) {
      App.toast('Lengkapi alokasi, komponen, volume, dan tarif.', 'warning');
      return;
    }
    App.ajax({
      url: window.APP.baseUrl + 'kegiatan/save_biaya_umum',
      data: JSON.stringify({
        kegiatan_id: $('[name="kegiatan_id"]').first().val(),
        kegiatan_anggaran_id: $('#biayaUmumAlokasiId').val(),
        jenis_biaya_id: $('#formBiayaUmum [name="jenis_biaya_id"]').val(),
        volume: volume, tarif: tarif,
        keterangan: $.trim($('#formBiayaUmum [name="keterangan"]').val() || '')
      }),
      contentType: 'application/json'
    }).done(function(r){
      if (r.status) {
        bootstrap.Modal.getOrCreateInstance('#modalBiayaUmum').hide();
        setTimeout(function(){ location.reload(); }, 400);
      }
    });
  });

  // Hapus peserta dari kegiatan
  $('.js-hapus-peserta').on('click', function(){
    var id = $(this).data('id');
    var nama = $(this).data('nama');
    Swal.fire({
      title: 'Hapus Peserta?',
      text: 'Peserta "' + nama + '" dan seluruh rincian biayanya akan dihapus dari kegiatan ini.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, Hapus',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#dc2626'
    }).then(function(r){
      if (r.isConfirmed) {
        App.ajax({
          url: window.APP.baseUrl + 'kegiatan/delete_peserta/' + id
        }).done(function(res){
          if (res.status) {
            setTimeout(function(){ location.reload(); }, 500);
          }
        });
      }
    });
  });

  // Ajukan verifikasi kegiatan
  $('.js-ajukan-kegiatan').on('click', function(){
    var id = $(this).data('id');
    Swal.fire({
      title: 'Ajukan Kegiatan?',
      text: 'Kegiatan ini akan dikirim ke antrian verifikasi pimpinan/verifikator.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ajukan Sekarang',
      cancelButtonText: 'Batal'
    }).then(function(r){
      if (r.isConfirmed) {
        App.ajax({
          url: window.APP.baseUrl + 'kegiatan/ajukan/' + id
        }).done(function(res){
          if (res.status) {
            setTimeout(function(){ location.reload(); }, 600);
          }
        });
      }
    });
  });
  // Verifikator: Setujui Kegiatan (SweetAlert)
  $('.js-verif-setujui').on('click', function(){
    var id = $(this).data('id');
    Swal.fire({
      title: 'Setujui Kegiatan Ini?',
      text: 'Anda telah memeriksa rincian peserta dan beban pos anggaran. Status kegiatan akan berubah menjadi AKTIF dan anggaran akan terpotong.',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-check-lg me-1"></i> Ya, Setujui',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#16a34a'
    }).then(function(r){
      if(r.isConfirmed){
        App.ajax({
          url: window.APP.baseUrl + 'kegiatan/verify/' + id,
          data: {action: 'setujui'}
        }).done(function(res){
          if(res.status) {
            Swal.fire('Disetujui!', 'Kegiatan telah disetujui.', 'success').then(function(){
              window.location.href = window.APP.baseUrl + 'kegiatan/antrian';
            });
          }
        });
      }
    });
  });

  // Verifikator: Tolak Kegiatan (SweetAlert dengan Input Alasan Wajib)
  $('.js-verif-tolak').on('click', function(){
    var id = $(this).data('id');
    Swal.fire({
      title: 'Tolak Kegiatan Ini?',
      input: 'textarea',
      inputLabel: 'Catatan / Alasan Penolakan (Wajib Diisi):',
      inputPlaceholder: 'Tuliskan catatan perbaikan agar pengaju tahu bagian mana yang harus diperbaiki...',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Kirim Penolakan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#dc2626',
      inputValidator: function(value) {
        if (!value || !value.trim()) {
          return 'Alasan penolakan wajib diisi!';
        }
      }
    }).then(function(x){
      if(x.isConfirmed) {
        App.ajax({
          url: window.APP.baseUrl + 'kegiatan/verify/' + id,
          data: {action: 'tolak', catatan_verifikasi: x.value.trim()}
        }).done(function(res){
          if(res.status) {
            Swal.fire('Ditolak!', 'Kegiatan ditolak dan dikembalikan ke pengaju.', 'info').then(function(){
              window.location.href = window.APP.baseUrl + 'kegiatan/antrian';
            });
          }
        });
      }
    });
  });
});
</script>

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
    <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?>
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
    <code>#<?= html_escape($kegiatan['no_surat_tugas'] ?: 'DRAFT') ?></code>
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
    </div>

    <!-- Ringkasan Total & Pembilang -->
    <div class="p-3 bg-light rounded-3 mt-3 border d-flex flex-wrap justify-content-between align-items-center gap-3">
      <div>
        <span class="text-muted small d-block">Total Anggaran Surat Tugas:</span>
        <h4 class="fw-bold text-primary mb-0"><?= rupiah($kegiatan['grand_total']) ?></h4>
      </div>
      <div class="text-md-end">
        <span class="text-muted small d-block">Terbilang:</span>
        <span class="fw-semibold text-success fst-italic">"<?= terbilang_indo($kegiatan['grand_total']) ?>"</span>
      </div>
    </div>
  </div>
</div>

<!-- Section: Daftar Peserta & Pendamping -->
<div class="card shadow-sm mb-4">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-people text-primary fs-5"></i>
      <span>Daftar Peserta &amp; Pendamping Perjalanan Dinas</span>
      <span class="badge text-bg-light border"><?= count($kegiatan['peserta']) ?> Orang</span>
    </div>
    <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?>
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
                <span class="badge text-bg-warning" title="Alasan: <?= html_escape($p['alasan_pengalihan']) ?>">
                  <i class="bi bi-arrow-left-right me-1"></i>Dialihkan: <?= html_escape($p['alasan_pengalihan']) ?>
                </span>
              <?php endif; ?>
            </div>
            <span class="fw-bold text-primary fs-6">
              <?= rupiah($p['total_biaya']) ?>
            </span>
            <?php if($can_update && in_array($status, array('DRAFT', 'DITOLAK'))): ?>
            <div class="btn-group btn-group-sm">
              <?php
                $pData = array(
                  'id'                => $encKpId,
                  'penerima_id'       => $encPenerimaId,
                  'pos_anggaran_id'   => !empty($p['pos_anggaran_id']) ? encrypt_id($p['pos_anggaran_id']) : '',
                  'alasan_pengalihan' => $p['alasan_pengalihan'] ?? '',
                  'nama'              => $p['nama'],
                  'tipe'              => $p['tipe'],
                  'sumber_dana_id'    => $p['sumber_dana_id'],
                  'biaya'             => array_map(function($b){
                    return array(
                      'jenis_biaya_id' => $b['jenis_biaya_id'],
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
                    <?php if($b['satuan']): ?>
                      <span class="text-muted small">(<?= html_escape($b['satuan']) ?>)</span>
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
              <label class="form-label small fw-bold">Pilih Peserta (Anggota DPRD / Pendamping) <span class="text-danger">*</span></label>
              <select class="form-select select2-peserta" name="penerima_id" id="penerimaSelect" required>
                <option value="">-- Pilih Anggota DPRD / Pendamping --</option>
                <optgroup label="Anggota DPRD">
                  <?php foreach($penerima as $p): ?>
                    <?php if($p['tipe'] === 'DPRD'): ?>
                    <option value="<?= encrypt_id($p['id']) ?>" data-tipe="DPRD">
                      <?= html_escape($p['nama']) ?> — <?= html_escape($p['jabatan'] ?: 'Anggota') ?> (DPRD)
                    </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </optgroup>
                <optgroup label="Pendamping / Pegawai">
                  <?php foreach($penerima as $p): ?>
                    <?php if($p['tipe'] !== 'DPRD'): ?>
                    <option value="<?= encrypt_id($p['id']) ?>" data-tipe="PENDAMPING">
                      <?= html_escape($p['nama']) ?> — <?= html_escape($p['jabatan'] ?: 'Pendamping') ?> (PENDAMPING)
                    </option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </optgroup>
              </select>
            </div>

            <!-- Pos Anggaran DPRD (Terkunci ke Orang) -->
            <div class="col-12 col-md-6" id="boxPosDprd" style="display: none;">
              <label class="form-label small fw-bold">Pos Anggaran yang Dibebani</label>
              <div class="p-2 border rounded bg-white">
                <div class="d-flex align-items-center gap-2">
                  <span class="badge text-bg-primary">DPRD</span>
                  <strong class="text-dark" id="txtNamaPosDprd">-</strong>
                  <span class="badge text-bg-light border text-muted ms-auto" title="Anggota DPRD selalu memakai pos miliknya sendiri dan tidak bisa dialihkan">
                    <i class="bi bi-lock-fill me-1"></i>Pos Melekat
                  </span>
                </div>
                <div class="small text-muted mt-1" id="txtSaldoPosDprd">Memuat saldo...</div>
              </div>
            </div>

            <!-- Pos Anggaran PENDAMPING (Bisa Dipilih / Dialihkan) -->
            <div class="col-12 col-md-6" id="boxPosPendamping" style="display: none;">
              <label class="form-label small fw-bold">Pos Anggaran yang Dibebani <span class="text-danger">*</span></label>
              <select class="form-select" name="pos_anggaran_id" id="posPendampingSelect">
                <option value="">-- Pilih Pos Jabatan Pendamping --</option>
                <?php foreach($pos_pendamping as $pp): ?>
                <option value="<?= encrypt_id($pp['id']) ?>"
                        data-pemegang-id="<?= $pp['penugasan_penerima_id'] ?>"
                        data-pemegang-nama="<?= html_escape($pp['pemegang_nama'] ?? '-') ?>"
                        data-sisa="<?= (float)$pp['sisa'] ?>">
                  <?= html_escape($pp['nama']) ?> (Sisa: <?= rupiah($pp['sisa']) ?>, Pemegang: <?= html_escape($pp['pemegang_nama'] ?? 'Kosong') ?>)
                </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text small text-muted" id="hintPosPendamping">
                Secara default membebankan ke pos jabatan aktif pemegang. Boleh dialihkan ke pos lain jika berhalangan.
              </div>
            </div>

            <!-- Input Alasan Pengalihan Pos -->
            <div class="col-12" id="boxAlasanPengalihan" style="display: none;">
              <div class="p-3 border border-warning rounded-3 bg-warning bg-opacity-10">
                <div class="d-flex align-items-center gap-2 mb-1">
                  <i class="bi bi-exclamation-triangle-fill text-warning fs-5"></i>
                  <strong class="text-dark">Pengalihan Pos Anggaran Terdeteksi</strong>
                </div>
                <p class="small text-muted mb-2">
                  Anda membebankan kegiatan ini ke pos yang berbeda dari pos bawaan personel. Berikan alasan pengalihan pos (wajib diisi):
                </p>
                <input type="text" class="form-control form-control-sm" name="alasan_pengalihan" id="inpAlasanPengalihan"
                       placeholder="Contoh: Pemegang pos berhalangan dinas, sehingga digantikan oleh personel ini.">
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
              <span class="fw-semibold text-muted">Total Anggaran Peserta Ini:</span>
              <h5 class="fw-bold text-primary mb-0" id="totalPesertaModal">Rp 0</h5>
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
  function createBiayaRow(jenisId, volume, tarif) {
    var opts = '<option value="">— Tanpa jenis biaya (Biaya Umum) —</option>';
    (window.MasterJenisBiaya || []).forEach(function(j){
      opts += '<option value="' + j.id + '" ' + (String(j.id) === String(jenisId) ? 'selected' : '') + '>' + escapeHtml(j.nama) + '</option>';
    });

    var vol = volume || 1;
    var tar = tarif || 0;
    var sub = vol * tar;

    var rowHtml = $(
      '<tr>' +
        '<td>' +
          '<select class="form-select form-select-sm row-jenis">' + opts + '</select>' +
          '<div class="form-text small text-muted" style="font-size:0.7rem">Opsional</div>' +
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
    return rowHtml;
  }

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
  }

  var defaultPosId = '';
  var currentTipePeserta = '';

  function handlePenerimaChange() {
    var opt = $('#penerimaSelect').find(':selected');
    var tipe = opt.data('tipe');
    var penerimaId = $('#penerimaSelect').val();
    currentTipePeserta = tipe || '';

    if (!penerimaId) {
      $('#boxPosDprd').hide();
      $('#boxPosPendamping').hide();
      $('#boxAlasanPengalihan').hide();
      return;
    }

    if (tipe === 'DPRD') {
      $('#boxPosPendamping').hide();
      $('#boxAlasanPengalihan').hide();
      $('#boxPosDprd').show();
      $('#txtNamaPosDprd').text(opt.text().split('—')[0].trim());
      $('#txtSaldoPosDprd').text('Memuat saldo pos...');

      App.ajax({
        url: window.APP.baseUrl + 'pos/pos_default?penerima_id=' + penerimaId + '&tahun_id=<?= (int)$kegiatan['tahun_anggaran_id'] ?>',
        method: 'GET'
      }).done(function(res){
        if (res.data && res.data.saldo) {
          $('#txtSaldoPosDprd').html('Pagu: <strong>' + formatRupiah(res.data.saldo.pagu) + '</strong> &bull; Sisa: <strong class="text-success">' + formatRupiah(res.data.saldo.sisa) + '</strong>');
        } else {
          $('#txtSaldoPosDprd').text('Pos terdaftar');
        }
      });
    } else {
      $('#boxPosDprd').hide();
      $('#boxPosPendamping').show();

      App.ajax({
        url: window.APP.baseUrl + 'pos/pos_default?penerima_id=' + penerimaId + '&tahun_id=<?= (int)$kegiatan['tahun_anggaran_id'] ?>',
        method: 'GET'
      }).done(function(res){
        if (res.data && res.data.pos) {
          defaultPosId = res.data.pos.id_enc;
          if (!$('#posPendampingSelect').val() || $('#posPendampingSelect').val() === defaultPosId) {
            $('#posPendampingSelect').val(defaultPosId).trigger('change');
          }
          checkPengalihanPos();
        } else {
          defaultPosId = '';
          checkPengalihanPos();
        }
      });
    }
  }

  function checkPengalihanPos() {
    if (currentTipePeserta !== 'PENDAMPING') {
      $('#boxAlasanPengalihan').hide();
      return;
    }
    var selectedPos = $('#posPendampingSelect').val();
    if (defaultPosId && selectedPos && selectedPos !== defaultPosId) {
      $('#boxAlasanPengalihan').slideDown(200);
      $('#inpAlasanPengalihan').prop('required', true);
    } else {
      $('#boxAlasanPengalihan').slideUp(200);
      $('#inpAlasanPengalihan').prop('required', false);
    }
  }

  $('#penerimaSelect').on('change', handlePenerimaChange);
  $('#posPendampingSelect').on('change', checkPengalihanPos);

  // Inisialisasi Select2 pada dropdown modal saat ditampilkan
  $('#modalPeserta').on('shown.bs.modal', function(){
    if ($.fn.select2) {
      $('#penerimaSelect').select2({
        dropdownParent: $('#modalPeserta'),
        width: '100%',
        placeholder: '-- Pilih Anggota DPRD / Pendamping --'
      });
      $('#posPendampingSelect').select2({
        dropdownParent: $('#modalPeserta'),
        width: '100%',
        placeholder: '-- Pilih Pos Jabatan Pendamping --'
      });
    }
  });

  // Buka Modal Tambah Peserta
  $('#btnTambahPeserta, #btnTambahPeserta2').on('click', function(){
    $('#formPeserta')[0].reset();
    $('#pesertaIdInput').val('');
    defaultPosId = '';
    currentTipePeserta = '';
    $('#boxPosDprd').hide();
    $('#boxPosPendamping').hide();
    $('#boxAlasanPengalihan').hide();
    if ($.fn.select2 && $('#penerimaSelect').hasClass("select2-hidden-accessible")) {
      $('#penerimaSelect').val('').trigger('change');
    }
    if ($.fn.select2 && $('#posPendampingSelect').hasClass("select2-hidden-accessible")) {
      $('#posPendampingSelect').val('').trigger('change');
    }
    $('#modalPesertaTitle').html('<i class="bi bi-person-plus-fill text-primary me-1"></i> Tambah Peserta / Pendamping');
    $('#tbodyBiayaRows').empty();
    $('#tbodyBiayaRows').append(createBiayaRow('', 1, 0));
    hitungTotalModal();
    bootstrap.Modal.getOrCreateInstance('#modalPeserta').show();
  });

  // Edit Peserta & Rincian Biaya
  $(document).on('click', '.js-edit-peserta', function(){
    var p = $(this).data('peserta');
    $('#formPeserta')[0].reset();
    $('#pesertaIdInput').val(p.id);
    currentTipePeserta = p.tipe || '';
    $('#penerimaSelect').val(p.penerima_id);
    if ($.fn.select2 && $('#penerimaSelect').hasClass("select2-hidden-accessible")) {
      $('#penerimaSelect').trigger('change');
    }

    if (p.tipe === 'PENDAMPING') {
      $('#boxPosDprd').hide();
      $('#boxPosPendamping').show();
      if (p.pos_anggaran_id) {
        $('#posPendampingSelect').val(p.pos_anggaran_id);
        if ($.fn.select2 && $('#posPendampingSelect').hasClass("select2-hidden-accessible")) {
          $('#posPendampingSelect').trigger('change');
        }
      }
      if (p.alasan_pengalihan) {
        $('#inpAlasanPengalihan').val(p.alasan_pengalihan);
        $('#boxAlasanPengalihan').show();
      }
    } else {
      $('#boxPosPendamping').hide();
      $('#boxAlasanPengalihan').hide();
      $('#boxPosDprd').show();
      $('#txtNamaPosDprd').text(p.nama);
    }

    $('#modalPesertaTitle').html('<i class="bi bi-pencil-square text-primary me-1"></i> Edit Peserta: ' + escapeHtml(p.nama));

    $('#tbodyBiayaRows').empty();
    if (p.biaya && p.biaya.length > 0) {
      p.biaya.forEach(function(b){
        $('#tbodyBiayaRows').append(createBiayaRow(b.jenis_biaya_id, b.volume, b.tarif));
      });
    } else {
      $('#tbodyBiayaRows').append(createBiayaRow('', 1, 0));
    }
    hitungTotalModal();
    bootstrap.Modal.getOrCreateInstance('#modalPeserta').show();
  });

  // Tambah baris biaya
  $('#btnTambahBarisBiaya').on('click', function(){
    $('#tbodyBiayaRows').append(createBiayaRow('', 1, 0));
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
          volume: vol,
          tarif: tar
        });
      }
    });

    var isPendamping = currentTipePeserta === 'PENDAMPING';
    var posId = isPendamping ? ($('#posPendampingSelect').val() || null) : null;
    var alasan = isPendamping ? ($('#inpAlasanPengalihan').val() || null) : null;

    if (isPendamping && defaultPosId && posId && posId !== defaultPosId && !alasan) {
      App.toast('Alasan pengalihan pos anggaran wajib diisi saat memilih pos lain.', 'warning');
      $('#inpAlasanPengalihan').focus();
      return;
    }

    var payload = {
      kegiatan_id:       $('[name="kegiatan_id"]').val(),
      id:                $('#pesertaIdInput').val() || null,
      penerima_id:       penerimaId,
      pos_anggaran_id:   posId,
      alasan_pengalihan: alasan,
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

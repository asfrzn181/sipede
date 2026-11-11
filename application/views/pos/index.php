<?php defined('BASEPATH') OR exit('No direct script access allowed');
?>
<?php
  $encTahunId = encrypt_id($tahun['id']);
  $isLocked   = !empty($tahun['is_locked']);
?>

<!-- Breadcrumb & Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="d-flex align-items-center gap-2 flex-wrap">
    <a href="<?= site_url('tahun') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <h1 class="h5 fw-bold text-dark mb-0">Pos Anggaran &mdash; Tahun <?= (int)$tahun['tahun'] ?></h1>
    <span class="badge text-bg-<?= $isLocked ? 'secondary' : 'success' ?>">
      <?= $isLocked ? '<i class="bi bi-lock-fill me-1"></i>Dikunci' : '<i class="bi bi-unlock-fill me-1"></i>Terbuka' ?>
    </span>
  </div>
  <?php if($can_manage && !$isLocked): ?>
  <div class="d-flex gap-2 flex-wrap">
    <!-- Buat Pos Otomatis -->
    <button class="btn btn-sm btn-outline-info d-flex align-items-center gap-1" id="btnBuatPosOtomatis"
            data-tahun-id="<?= $encTahunId ?>">
      <i class="bi bi-magic"></i> Buat Pos Otomatis
    </button>
    <!-- Pagu Standar DPRD & Pendamping -->
    <button class="btn btn-sm btn-outline-warning d-flex align-items-center gap-1"
            data-bs-toggle="modal" data-bs-target="#modalPaguStandar">
      <i class="bi bi-sliders"></i> Terapkan Pagu Standar
    </button>
  </div>
  <?php endif; ?>
</div>

<!-- 4 Kartu KPI -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2"><i class="bi bi-wallet2 text-primary"></i> Pagu DPRD</span>
      <strong class="text-primary"><?= rupiah($totalPaguDprd) ?></strong>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2"><i class="bi bi-wallet text-secondary"></i> Pagu Pendamping</span>
      <strong class="text-secondary"><?= rupiah($totalPaguPend) ?></strong>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2"><i class="bi bi-cash-stack text-warning"></i> Total Realisasi</span>
      <strong class="text-dark"><?= rupiah($totalRealisasi) ?></strong>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2"><i class="bi bi-pie-chart text-success"></i> Total Sisa</span>
      <strong class="<?= $totalSisa < 0 ? 'text-danger' : 'text-success' ?>"><?= rupiah($totalSisa) ?></strong>
    </div>
  </div>
</div>

<!-- Tab DPRD / PENDAMPING -->
<ul class="nav nav-tabs mb-3" id="posTab" role="tablist">
  <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tabDprd">
    <i class="bi bi-person-badge me-1"></i>Pos DPRD (<?= count(array_filter($rows, fn($r) => $r['tipe']==='DPRD')) ?>)
  </button></li>
  <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tabPendamping">
    <i class="bi bi-people me-1"></i>Pos Pendamping (<?= count(array_filter($rows, fn($r) => $r['tipe']==='PENDAMPING')) ?>)
  </button></li>
</ul>

<div class="tab-content">

  <!-- ── Tab DPRD ── -->
  <div class="tab-pane fade show active" id="tabDprd">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
        <i class="bi bi-person-badge text-primary"></i>
        <span>Pos Anggaran Anggota DPRD</span>
        <span class="badge text-bg-light border small text-muted">Pagu melekat pada orangnya</span>
      </div>
      <div class="card-body p-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable" id="tblDprd">
            <thead class="table-light small">
              <tr>
                <th>Nama Anggota DPRD</th>
                <th style="width:110px">Kelompok</th>
                <th>Jabatan</th>
                <th class="text-end" style="width:155px">Pagu</th>
                <th class="text-end" style="width:140px">Realisasi</th>
                <th class="text-end" style="width:140px">Sisa</th>
                <?php if($can_manage && !$isLocked): ?>
                <th class="no-sort text-end" style="width:95px">Aksi</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach($rows as $r): if($r['tipe']!=='DPRD') continue; ?>
              <?php
                $encPosId = encrypt_id($r['id']);
                $sisa     = (float)$r['sisa'];
                $pagu     = (float)$r['pagu'];
                $realisasi= (float)$r['realisasi'];
                $kel      = $r['kelompok_jabatan'] ?? 'ANGGOTA';
                $kelClass = $kel === 'KETUA' ? 'danger' : ($kel === 'WAKIL_KETUA' ? 'warning' : 'secondary');
              ?>
              <tr>
                <td class="fw-semibold"><?= html_escape($r['nama']) ?></td>
                <td><span class="badge text-bg-<?= $kelClass ?>"><?= html_escape($kel) ?></span></td>
                <td class="small text-muted"><?= html_escape($r['pemegang_jabatan'] ?? '-') ?></td>
                <td class="text-end fw-bold text-primary"><?= rupiah($pagu) ?></td>
                <td class="text-end text-muted"><?= rupiah($realisasi) ?></td>
                <td class="text-end fw-semibold <?= $sisa < 0 ? 'text-danger' : ($sisa == 0 && $pagu > 0 ? 'text-warning' : 'text-success') ?>">
                  <?= rupiah($sisa) ?>
                </td>
                <?php if($can_manage && !$isLocked): ?>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-warning js-revisi-pos"
                    data-pos-id="<?= $encPosId ?>"
                    data-pos-nama="<?= html_escape($r['nama']) ?>"
                    data-pos-tipe="DPRD"
                    data-pagu="<?= $pagu ?>"
                    data-realisasi="<?= $realisasi ?>"
                    data-sisa="<?= $sisa ?>">
                    <i class="bi bi-pencil-square"></i> Revisi
                  </button>
                </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- ── Tab PENDAMPING ── -->
  <div class="tab-pane fade" id="tabPendamping">
    <div class="card shadow-sm">
      <div class="card-header bg-white fw-bold d-flex align-items-center gap-2 justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <i class="bi bi-people text-secondary"></i>
          <span>Pos Jabatan Pendamping</span>
          <span class="badge text-bg-light border small text-muted">Pagu melekat pada jabatan pos</span>
        </div>
      </div>
      <div class="card-body p-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable" id="tblPendamping">
            <thead class="table-light small">
              <tr>
                <th>Nama Pos Jabatan</th>
                <th>Pemegang Saat Ini</th>
                <th>Sejak</th>
                <th class="text-end" style="width:155px">Pagu</th>
                <th class="text-end" style="width:140px">Realisasi</th>
                <th class="text-end" style="width:140px">Sisa</th>
                <?php if($can_manage && !$isLocked): ?>
                <th class="no-sort text-end" style="width:170px">Aksi</th>
                <?php endif; ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach($rows as $r): if($r['tipe']!=='PENDAMPING') continue; ?>
              <?php
                $encPosId = encrypt_id($r['id']);
                $sisa     = (float)$r['sisa'];
                $pagu     = (float)$r['pagu'];
                $realisasi= (float)$r['realisasi'];
              ?>
              <tr>
                <td>
                  <div class="fw-semibold"><?= html_escape($r['nama']) ?></div>
                  <code class="small text-muted"><?= html_escape($r['kode']) ?></code>
                </td>
                <td>
                  <?php if(!empty($r['pemegang_nama']) && $r['pemegang_nama'] !== '-'): ?>
                    <span class="fw-semibold"><?= html_escape($r['pemegang_nama']) ?></span>
                    <div class="small text-muted"><?= html_escape($r['pemegang_jabatan'] ?? '') ?></div>
                  <?php else: ?>
                    <span class="badge text-bg-light border text-muted">— Kosong —</span>
                  <?php endif; ?>
                </td>
                <td class="small text-muted">
                  <?= !empty($r['penugasan_mulai']) ? tgl_indo($r['penugasan_mulai']) : '—' ?>
                </td>
                <td class="text-end fw-bold text-secondary"><?= rupiah($pagu) ?></td>
                <td class="text-end text-muted"><?= rupiah($realisasi) ?></td>
                <td class="text-end fw-semibold <?= $sisa < 0 ? 'text-danger' : ($sisa == 0 && $pagu > 0 ? 'text-warning' : 'text-success') ?>">
                  <?= rupiah($sisa) ?>
                </td>
                <?php if($can_manage && !$isLocked): ?>
                <td class="text-end text-nowrap">
                  <!-- Revisi Pagu -->
                  <button class="btn btn-sm btn-outline-warning js-revisi-pos"
                    data-pos-id="<?= $encPosId ?>"
                    data-pos-nama="<?= html_escape($r['nama']) ?>"
                    data-pos-tipe="PENDAMPING"
                    data-pagu="<?= $pagu ?>"
                    data-realisasi="<?= $realisasi ?>"
                    data-sisa="<?= $sisa ?>">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <!-- Tugaskan Pemegang -->
                  <button class="btn btn-sm btn-outline-primary js-tugaskan-pos"
                    data-pos-id="<?= $encPosId ?>"
                    data-pos-nama="<?= html_escape($r['nama']) ?>"
                    data-pemegang="<?= html_escape($r['pemegang_nama'] ?? '') ?>">
                    <i class="bi bi-person-check"></i> Tugaskan
                  </button>
                  <!-- Riwayat -->
                  <button class="btn btn-sm btn-outline-secondary js-riwayat-pos"
                    data-pos-id="<?= $encPosId ?>"
                    data-pos-nama="<?= html_escape($r['nama']) ?>">
                    <i class="bi bi-clock-history"></i>
                  </button>
                </td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- ── Modal Revisi Pagu ── -->
<div class="modal fade" id="modalRevisiPagu" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="formRevisiPagu">
        <input type="hidden" name="pos_id" id="revPosId">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-pencil-square text-warning"></i>
            Revisi Pagu &mdash; <span id="revPosNama">-</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <!-- Metrik Saat Ini -->
          <div class="row g-2 mb-3">
            <div class="col-4">
              <div class="p-2 border rounded bg-white text-center">
                <span class="small text-muted d-block">Pagu Saat Ini</span>
                <strong class="text-primary" id="revPaguSaatIni">Rp 0</strong>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 border rounded bg-white text-center">
                <span class="small text-muted d-block">Realisasi Terpakai</span>
                <strong class="text-dark" id="revRealisasiVal">Rp 0</strong>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 border rounded bg-white text-center">
                <span class="small text-muted d-block">Sisa Saat Ini</span>
                <strong class="text-success" id="revSisaVal">Rp 0</strong>
              </div>
            </div>
          </div>
          <!-- Input Pagu Baru -->
          <div class="mb-3">
            <label class="form-label small fw-bold">Pagu Baru (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light">Rp</span>
              <input type="text" class="form-control form-control-lg fw-bold input-rupiah"
                     id="revPaguBaruDisplay"
                     data-target-hidden="#revPaguBaruVal"
                     data-target-terbilang="#revTerbilangText"
                     placeholder="0" autocomplete="off">
              <input type="hidden" name="pagu_baru" id="revPaguBaruVal" value="0">
            </div>
            <div class="pembilang-box d-none mt-2" id="revPembilangBox">
              <i class="bi bi-chat-left-quote-fill pembilang-icon"></i>
              <div>
                <span class="d-block text-muted text-uppercase fw-bold" style="font-size:0.68rem">Terbilang:</span>
                <span class="pembilang-text" id="revTerbilangText"></span>
              </div>
            </div>
          </div>
          <!-- Kalkulasi Real-Time -->
          <div class="p-3 border rounded-3 mb-3 bg-light" id="calcBox">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-muted">Selisih:</span>
              <span class="badge bg-secondary" id="revBadgeSelisih">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="small text-muted">Estimasi Sisa Baru:</span>
              <strong id="revEstimasiSisa">Rp 0</strong>
            </div>
            <div class="alert alert-danger d-none mt-2 mb-0 py-2 small" id="alertDefisit">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              Pagu baru tidak boleh lebih kecil dari realisasi terpakai (<span id="alertMinReal">Rp 0</span>).
            </div>
          </div>
          <!-- Alasan -->
          <div>
            <label class="form-label small fw-bold">Alasan Revisi <span class="text-danger">*</span></label>
            <textarea class="form-control" name="alasan" id="revAlasan" rows="2"
                      placeholder="Contoh: Penyesuaian APBD Perubahan, Pergeseran Antar Pos, dll." required></textarea>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary" id="btnSubmitRevisi">
            <i class="bi bi-check-circle me-1"></i>Simpan Revisi
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Modal Tugaskan Pemegang Pos Pendamping ── -->
<div class="modal fade" id="modalTugaskan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formTugaskan">
        <input type="hidden" name="pos_id" id="tugasPosId">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-person-check text-primary"></i>
            Tugaskan Pemegang &mdash; <span id="tugasPosNama">-</span>
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="alert alert-info small py-2 mb-3">
            <i class="bi bi-info-circle me-1"></i>
            Pemegang lama akan otomatis ditutup penugasannya (per tanggal mulai &minus; 1 hari). Riwayat tersimpan lengkap.
          </div>
          <!-- Pemegang Lama -->
          <div class="mb-3">
            <label class="form-label small fw-semibold text-muted">Pemegang Saat Ini</label>
            <input type="text" class="form-control" id="tugasPemegangLama" readonly disabled>
          </div>
          <!-- Pilih Pemegang Baru -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">Pemegang Baru (Pendamping) <span class="text-danger">*</span></label>
            <select class="form-select" name="penerima_id" id="tugasPenerimaId" required>
              <option value="">— Pilih Pendamping —</option>
              <?php foreach($pendamping as $pend): ?>
              <option value="<?= encrypt_id($pend['id']) ?>">
                <?= html_escape($pend['nama']) ?> — <?= html_escape($pend['jabatan'] ?: 'Pendamping') ?>
              </option>
              <?php endforeach; ?>
            </select>
          </div>
          <!-- Tanggal Mulai -->
          <div class="mb-3">
            <label class="form-label small fw-semibold">Tanggal Mulai Penugasan <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="tanggal_mulai" id="tugasTglMulai" required>
          </div>
          <!-- Keterangan -->
          <div>
            <label class="form-label small fw-semibold">Keterangan <span class="text-muted">(opsional)</span></label>
            <input type="text" class="form-control" name="keterangan"
                   placeholder="Contoh: Penggantian karena promosi jabatan, dll.">
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle me-1"></i>Simpan Penugasan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ── Modal Riwayat Penugasan ── -->
<div class="modal fade" id="modalRiwayat" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
          <i class="bi bi-clock-history text-secondary"></i>
          Riwayat Penugasan &mdash; <span id="riwayatPosNama">-</span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-3">
        <div class="table-responsive">
          <table class="table table-sm align-middle" id="tblRiwayat">
            <thead class="table-light"><tr>
              <th>Pemegang</th>
              <th>Jabatan</th>
              <th>Mulai</th>
              <th>Selesai</th>
              <th>Keterangan</th>
            </tr></thead>
            <tbody id="tbodyRiwayat"></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ── Modal Pagu Standar DPRD & Pendamping ── -->
<div class="modal fade" id="modalPaguStandar" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="formPaguStandar">
        <input type="hidden" name="tahun_id" value="<?= $encTahunId ?>">
        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
            <i class="bi bi-sliders text-warning"></i> Terapkan Pagu Standar
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <p class="text-muted small">Isi pagu standar per kelompok jabatan DPRD dan satu pagu untuk seluruh pos pendamping. Hanya pos yang pagu-nya masih 0 yang diperbarui (kecuali centang "Timpa Semua").</p>
          <div class="mb-3">
            <label class="form-label small fw-bold">Pagu Ketua DPRD <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text bg-light">Rp</span>
              <input type="text" class="form-control input-rupiah" name="pagu_ketua" data-target-hidden="#hidPaguKetua" placeholder="0">
              <input type="hidden" id="hidPaguKetua" name="pagu_ketua">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold">Pagu Wakil Ketua</label>
            <div class="input-group">
              <span class="input-group-text bg-light">Rp</span>
              <input type="text" class="form-control input-rupiah" name="pagu_wakil_ketua" data-target-hidden="#hidPaguWakil" placeholder="0">
              <input type="hidden" id="hidPaguWakil" name="pagu_wakil_ketua">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold">Pagu Anggota</label>
            <div class="input-group">
              <span class="input-group-text bg-light">Rp</span>
              <input type="text" class="form-control input-rupiah" name="pagu_anggota" data-target-hidden="#hidPaguAnggota" placeholder="0">
              <input type="hidden" id="hidPaguAnggota" name="pagu_anggota">
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold">Pagu Pendamping</label>
            <div class="input-group">
              <span class="input-group-text bg-light">Rp</span>
              <input type="text" class="form-control input-rupiah" name="pagu_pendamping" data-target-hidden="#hidPaguPendamping" placeholder="0">
              <input type="hidden" id="hidPaguPendamping" name="pagu_pendamping">
            </div>
            <div class="form-text">Nilai ini diterapkan ke seluruh pos jabatan pendamping.</div>
          </div>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="overwrite" value="1" id="chkOverwrite">
            <label class="form-check-label small" for="chkOverwrite">Timpa semua pagu yang sudah ada</label>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-warning fw-bold"><i class="bi bi-check-circle me-1"></i>Terapkan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  var currentPagu = 0, currentRealisasi = 0;

  // Selalu baca nilai dari input yang terlihat agar angka tampilan dan angka
  // yang dihitung tidak berbeda ketika hidden input belum tersinkron.
  function readPaguBaru() {
    var displayValue = $('#revPaguBaruDisplay').val();
    var paguBaru = window.cleanRupiah
      ? window.cleanRupiah(displayValue)
      : (parseFloat($('#revPaguBaruVal').val()) || 0);
    $('#revPaguBaruVal').val(paguBaru);
    return paguBaru;
  }

  function hitungRevisi() {
    var paguBaru = readPaguBaru();
    var selisih  = paguBaru - currentPagu;
    var sisaBaru = paguBaru - currentRealisasi;

    $('#revBadgeSelisih').text(selisih >= 0 ? '+ ' + formatRupiah(selisih) : '- ' + formatRupiah(Math.abs(selisih)))
      .attr('class', 'badge ' + (selisih > 0 ? 'text-bg-success' : (selisih < 0 ? 'text-bg-warning' : 'bg-secondary')));
    $('#revEstimasiSisa').text(formatRupiah(sisaBaru))
      .attr('class', sisaBaru < 0 ? 'fw-bold text-danger' : 'fw-bold text-success');

    if (paguBaru < currentRealisasi) {
      $('#alertMinReal').text(formatRupiah(currentRealisasi));
      $('#alertDefisit').removeClass('d-none');
      $('#btnSubmitRevisi').prop('disabled', true);
    } else {
      $('#alertDefisit').addClass('d-none');
      $('#btnSubmitRevisi').prop('disabled', false);
    }
  }

  // Buka Modal Revisi Pagu
  $(document).on('click', '.js-revisi-pos', function(){
    var btn = $(this);
    currentPagu       = parseFloat(btn.data('pagu')) || 0;
    currentRealisasi  = parseFloat(btn.data('realisasi')) || 0;
    var sisa          = parseFloat(btn.data('sisa')) || 0;

    $('#revPosId').val(btn.data('pos-id'));
    $('#revPosNama').text(btn.data('pos-nama'));
    $('#revPaguSaatIni').text(formatRupiah(currentPagu));
    $('#revRealisasiVal').text(formatRupiah(currentRealisasi));
    $('#revSisaVal').text(formatRupiah(sisa)).attr('class', sisa < 0 ? 'text-danger fw-bold' : 'text-success fw-bold');

    $('#revPaguBaruVal').val(currentPagu);
    $('#revPaguBaruDisplay').val(currentPagu ? Number(currentPagu).toLocaleString('id-ID') : '').trigger('input');
    $('#revAlasan').val('');
    hitungRevisi();
    bootstrap.Modal.getOrCreateInstance('#modalRevisiPagu').show();
  });

  $('#revPaguBaruDisplay').on('input', hitungRevisi);

  // Submit Revisi Pagu
  $('#formRevisiPagu').on('submit', function(e){
    e.preventDefault();
    var paguBaru = readPaguBaru();
    if (paguBaru < currentRealisasi) { App.toast('Pagu baru di bawah realisasi terpakai!', 'error'); return; }
    if (Math.abs(paguBaru - currentPagu) < 0.01) { App.toast('Nilai pagu sama dengan saat ini.', 'warning'); return; }
    App.ajax({ url: window.APP.baseUrl + 'pos/revisi_pagu', data: $(this).serialize() })
      .done(function(r){ if(r.status){ bootstrap.Modal.getOrCreateInstance('#modalRevisiPagu').hide(); setTimeout(()=>location.reload(), 500); } });
  });

  // Buka Modal Tugaskan
  $(document).on('click', '.js-tugaskan-pos', function(){
    var btn = $(this);
    $('#tugasPosId').val(btn.data('pos-id'));
    $('#tugasPosNama').text(btn.data('pos-nama'));
    $('#tugasPemegangLama').val(btn.data('pemegang') || '— Kosong —');
    $('#tugasPenerimaId').val('');
    $('#tugasTglMulai').val(new Date().toISOString().split('T')[0]);
    bootstrap.Modal.getOrCreateInstance('#modalTugaskan').show();
  });

  // Submit Penugasan
  $('#formTugaskan').on('submit', function(e){
    e.preventDefault();
    App.ajax({ url: window.APP.baseUrl + 'pos/simpan_penugasan', data: $(this).serialize() })
      .done(function(r){ if(r.status){ bootstrap.Modal.getOrCreateInstance('#modalTugaskan').hide(); setTimeout(()=>location.reload(), 500); } });
  });

  // Buka Modal Riwayat
  $(document).on('click', '.js-riwayat-pos', function(){
    var btn = $(this);
    $('#riwayatPosNama').text(btn.data('pos-nama'));
    $('#tbodyRiwayat').html('<tr><td colspan="5" class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm me-2"></div>Memuat riwayat...</td></tr>');
    bootstrap.Modal.getOrCreateInstance('#modalRiwayat').show();
    App.ajax({ url: window.APP.baseUrl + 'pos/riwayat_penugasan/' + btn.data('pos-id'), method: 'GET' })
      .done(function(r){
        var html = '';
        if (!r.data || !r.data.riwayat || !r.data.riwayat.length) {
          html = '<tr><td colspan="5" class="text-center text-muted py-3">Belum ada riwayat penugasan.</td></tr>';
        } else {
          r.data.riwayat.forEach(function(x){
            var aktif = !x.tanggal_selesai ? '<span class="badge text-bg-success">Aktif</span>' : x.tanggal_selesai;
            html += '<tr>'
              + '<td class="fw-semibold">' + escapeHtml(x.penerima_nama) + '</td>'
              + '<td class="small text-muted">' + escapeHtml(x.jabatan || '-') + '</td>'
              + '<td>' + escapeHtml(x.tanggal_mulai) + '</td>'
              + '<td>' + aktif + '</td>'
              + '<td class="small text-muted">' + escapeHtml(x.keterangan || '-') + '</td>'
              + '</tr>';
          });
        }
        $('#tbodyRiwayat').html(html);
      });
  });

  // Buat Pos Otomatis
  $('#btnBuatPosOtomatis').on('click', function(){
    var tahunId = $(this).data('tahun-id');
    Swal.fire({
      title: 'Buat Pos Otomatis?',
      html: 'Sistem akan membuat <strong>pos DPRD</strong> untuk setiap anggota aktif, dan <strong>8 pos baku pendamping</strong> untuk tahun ini.<br><small class="text-muted">Pos yang sudah ada tidak akan digandakan.</small>',
      icon: 'question',
      showCancelButton: true,
      confirmButtonText: 'Ya, Buat Sekarang',
      cancelButtonText: 'Batal'
    }).then(function(r){
      if (r.isConfirmed) {
        App.ajax({ url: window.APP.baseUrl + 'pos/buat_pos/' + tahunId })
          .done(function(res){ if(res.status) setTimeout(()=>location.reload(), 500); });
      }
    });
  });

  // Submit Pagu Standar
  $('#formPaguStandar').on('submit', function(e){
    e.preventDefault();
    App.ajax({ url: window.APP.baseUrl + 'pos/terapkan_standar', data: $(this).serialize() })
      .done(function(r){ if(r.status){ bootstrap.Modal.getOrCreateInstance('#modalPaguStandar').hide(); setTimeout(()=>location.reload(), 500); } });
  });
});
</script>


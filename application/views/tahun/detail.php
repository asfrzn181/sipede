<!-- Breadcrumb & Status Header -->
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div class="d-flex align-items-center gap-2">
    <a href="<?= site_url('tahun') ?>" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center gap-1">
      <i class="bi bi-arrow-left"></i> Kembali
    </a>
    <span class="fs-5 fw-bold text-dark mb-0">
      Pagu Anggaran Tahun <?= (int)$tahun['tahun'] ?>
    </span>
    <span class="badge text-bg-<?= $tahun['is_locked'] ? 'secondary' : 'success' ?>">
      <?= $tahun['is_locked'] ? '<i class="bi bi-lock-fill me-1"></i>Dikunci' : '<i class="bi bi-unlock-fill me-1"></i>Terbuka' ?>
    </span>
  </div>
  <div class="d-flex align-items-center gap-2">
    <!-- Link ke halaman Pos Anggaran -->
    <?php $encTahunLink = encrypt_id($tahun['id']); ?>
    <a href="<?= site_url('pos/index/' . $encTahunLink) ?>"
       class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1">
      <i class="bi bi-grid-3x3-gap"></i>
      <span>Kelola Pos Anggaran</span>
    </a>
    <small class="text-muted">
      <i class="bi bi-shield-check text-primary me-1"></i> Enkripsi ID &amp; Audit Log Aktif
    </small>
  </div>
</div>

<!-- 3 Ringkasan KPI Anggaran -->
<div class="row g-3 mb-4">
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2">
        <i class="bi bi-wallet2 text-primary"></i> Total Pagu <?= (int)$tahun['tahun'] ?>
      </span>
      <strong class="text-primary"><?= rupiah($totalPagu) ?></strong>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2">
        <i class="bi bi-cash-stack text-warning"></i> Total Realisasi (Terpakai)
      </span>
      <strong class="text-dark"><?= rupiah($totalRealisasi) ?></strong>
    </div>
  </div>
  <div class="col-12 col-md-4">
    <div class="stat-card">
      <span class="d-flex align-items-center gap-2">
        <i class="bi bi-pie-chart text-success"></i> Total Sisa Anggaran
      </span>
      <strong class="<?= $totalSisa < 0 ? 'text-danger' : 'text-success' ?>"><?= rupiah($totalSisa) ?></strong>
    </div>
  </div>
</div>

<!-- Tabel Rincian Pagu per Penerima -->
<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-table text-primary"></i>
      <span>Daftar Pagu Anggaran per Penerima</span>
    </div>
    <?php if(!$tahun['is_locked'] && $can_manage_anggaran): ?>
    <span class="badge text-bg-light border small text-muted">
      Klik tombol <strong>Revisi</strong> untuk mengubah pagu
    </span>
    <?php endif; ?>
  </div>
  <div class="card-body p-3">
    <div class="alert alert-info d-flex align-items-center justify-content-between py-2 mb-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-info-circle fs-5"></i>
        <div>
          Pagu sekarang dikelola melalui <strong>Pos Anggaran</strong> (DPRD per orang, Pendamping per jabatan).
          Pagu pendamping dapat dialihkan dan memiliki riwayat penugasan.
        </div>
      </div>
      <a href="<?= site_url('pos/index/' . $encTahunLink) ?>" class="btn btn-sm btn-info text-white text-nowrap ms-2">
        <i class="bi bi-arrow-right-circle me-1"></i>Buka Pos Anggaran
      </a>
    </div>

    <?php if($tahun['is_locked']): ?>
    <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3">
      <i class="bi bi-lock-fill fs-5"></i>
      <div>Tahun anggaran ini dalam status <strong>dikunci</strong>. Revisi pagu anggaran dinonaktifkan.</div>
    </div>
    <?php endif; ?>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 datatable" id="paguTable">
        <thead class="table-light">
          <tr>
            <th>Nama Penerima</th>
            <th style="width: 85px;">Tipe</th>
            <th>Jabatan</th>
            <th style="width: 120px;">Periode</th>
            <th class="text-end" style="width: 150px;">Pagu Anggaran</th>
            <th class="text-end" style="width: 140px;">Realisasi</th>
            <th class="text-end" style="width: 140px;">Sisa Anggaran</th>
            <?php if($can_manage_anggaran && !$tahun['is_locked']): ?>
            <th class="no-sort text-end" style="width: 110px;">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $row): ?>
          <?php 
            $sisa = (float)$row['sisa'];
            $pagu = (float)$row['pagu'];
            $realisasi = (float)$row['realisasi'];
            $encPenerimaId = encrypt_id($row['penerima_id']);
            $encTahunId = encrypt_id($tahun['id']);
          ?>
          <tr>
            <td class="fw-semibold"><?= html_escape($row['nama']) ?></td>
            <td>
              <span class="badge <?= $row['tipe'] === 'DPRD' ? 'text-bg-primary bg-opacity-10 text-primary border' : 'text-bg-secondary bg-opacity-10 text-secondary border' ?>">
                <?= html_escape($row['tipe']) ?>
              </span>
            </td>
            <td><?= html_escape($row['jabatan'] ?? '-') ?></td>
            <td class="text-nowrap small text-muted">
              <?php if(!empty($row['periode_mulai']) && !empty($row['periode_akhir'])): ?>
                <?= (int)$row['periode_mulai'] ?> &ndash; <?= (int)$row['periode_akhir'] ?>
              <?php else: ?>
                &mdash;
              <?php endif; ?>
            </td>
            <td class="text-end fw-bold text-primary"><?= rupiah($pagu) ?></td>
            <td class="text-end text-muted"><?= rupiah($realisasi) ?></td>
            <td class="text-end fw-semibold <?= $sisa < 0 ? 'text-danger' : ($sisa == 0 && $pagu > 0 ? 'text-warning' : 'text-success') ?>">
              <?= rupiah($sisa) ?>
            </td>
            <?php if($can_manage_anggaran && !$tahun['is_locked']): ?>
            <td class="text-end text-nowrap">
              <a class="btn btn-sm btn-outline-primary d-inline-flex align-items-center gap-1"
                 href="<?= site_url('pos/index/' . $encTahunLink) ?>">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Kelola Pos</span>
              </a>
            </td>
            <?php endif; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Revisi Anggaran -->
<div class="modal fade" id="revisiModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form id="revisiForm" action="<?= site_url('tahun/revisi_anggaran') ?>">
        <input type="hidden" name="tahun_id" id="revTahunId">
        <input type="hidden" name="penerima_id" id="revPenerimaId">

        <div class="modal-header bg-light">
          <div>
            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
              <i class="bi bi-pencil-square text-warning"></i>
              <span>Revisi Pagu Anggaran</span>
              <span class="badge text-bg-info bg-opacity-10 text-info border small" style="font-size: 0.72rem;">
                <i class="bi bi-journal-text me-1"></i>Audit Log
              </span>
            </h5>
            <small class="text-muted">Setiap perubahan pagu akan divalidasi dengan realisasi dan dicatat permanen di Audit Log sistem.</small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Info Identitas Penerima -->
          <div class="p-3 bg-light rounded-3 mb-3 border">
            <div class="row align-items-center">
              <div class="col-md-7">
                <span class="text-muted small d-block">Penerima Anggaran</span>
                <h5 class="fw-bold mb-1 text-dark" id="revPenerimaNama">-</h5>
                <span class="badge text-bg-primary me-2" id="revPenerimaTipe">DPRD</span>
                <span class="text-muted small" id="revPenerimaJabatan">-</span>
              </div>
              <div class="col-md-5 text-md-end mt-2 mt-md-0">
                <span class="badge text-bg-light border text-muted">
                  <i class="bi bi-calendar3 me-1"></i>Tahun Anggaran <?= (int)$tahun['tahun'] ?>
                </span>
              </div>
            </div>
          </div>

          <!-- Metrik Anggaran Saat Ini -->
          <div class="row g-2 mb-3">
            <div class="col-4">
              <div class="p-2 border rounded bg-white text-center">
                <span class="small text-muted d-block">Pagu Saat Ini</span>
                <strong class="text-primary fs-6" id="revPaguSaatIni">Rp 0</strong>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 border rounded bg-white text-center">
                <span class="small text-muted d-block">Realisasi Terpakai</span>
                <strong class="text-dark fs-6" id="revRealisasiSaatIni">Rp 0</strong>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 border rounded bg-white text-center">
                <span class="small text-muted d-block">Sisa Saat Ini</span>
                <strong class="text-success fs-6" id="revSisaSaatIni">Rp 0</strong>
              </div>
            </div>
          </div>

          <!-- Input Pagu Baru Otomatis Rupiah & Pembilang -->
          <div class="mb-3">
            <label class="form-label small fw-bold">Pagu Anggaran Baru (Rp) <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-text fw-semibold bg-light">Rp</span>
              <input type="text" class="form-control form-control-lg fw-bold text-primary input-rupiah" 
                     id="revPaguBaruDisplay" 
                     data-target-hidden="#revPaguBaru" 
                     data-target-terbilang="#revTerbilangText" 
                     placeholder="0" required autocomplete="off">
              <input type="hidden" name="pagu_baru" id="revPaguBaru" value="0">
            </div>

            <!-- Box Teks Pembilang (Terbilang Rupiah) -->
            <div class="pembilang-box d-none mt-2" id="revPembilangBox">
              <i class="bi bi-chat-left-quote-fill pembilang-icon"></i>
              <div>
                <span class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.68rem; letter-spacing: 0.5px;">Terbilang:</span>
                <span class="pembilang-text" id="revTerbilangText">Nol Rupiah</span>
              </div>
            </div>
            <div class="form-text text-muted">Ketik angka langsung diformat rupiah (misal <code>500000000</code> &rarr; <code>500.000.000</code>), tersimpan otomatis dalam format angka murni.</div>
          </div>

          <!-- Box Kalkulasi Real-Time -->
          <div class="p-3 border rounded-3 mb-3" id="calcBox" style="background: #f8fafc;">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <span class="small text-muted">Selisih Perubahan (Pagu Baru &minus; Pagu Lama):</span>
              <span class="badge bg-secondary fs-6" id="revBadgeSelisih">Rp 0</span>
            </div>
            <div class="d-flex justify-content-between align-items-center">
              <span class="small text-muted">Estimasi Sisa Anggaran Baru:</span>
              <strong class="fs-6 text-dark" id="revEstimasiSisaBaru">Rp 0</strong>
            </div>

            <!-- Error Banner jika Pagu Baru < Realisasi -->
            <div class="alert alert-danger d-none mt-3 mb-0 py-2 small" id="alertDefisit">
              <i class="bi bi-exclamation-triangle-fill me-1"></i>
              <strong>Peringatan Defisit:</strong> Pagu baru tidak boleh lebih kecil dari dana yang sudah terealisasi (<span id="alertMinRealisasi">Rp 0</span>). Sisa anggaran tidak boleh minus.
            </div>
          </div>

          <!-- Input Alasan Revisi (Wajib Audit) -->
          <div class="mb-1">
            <label class="form-label small fw-bold">
              Alasan / Dasar Perubahan Anggaran <span class="text-danger">*</span>
            </label>
            <textarea class="form-control" name="alasan" id="revAlasan" rows="2" 
                      placeholder="Contoh: Penyesuaian APBD Perubahan, Pergeseran Anggaran Rapat Koordinasi, dll." required></textarea>
            <div class="form-text small text-muted">Alasan ini akan disimpan secara permanen pada log audit bersama identitas akun Anda.</div>
          </div>
        </div>

        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary d-flex align-items-center gap-1" id="btnSubmitRevisi">
            <i class="bi bi-check-circle"></i>
            <span>Simpan Revisi Anggaran</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  var currentPagu = 0;
  var currentRealisasi = 0;

  // Gunakan angka yang terlihat sebagai sumber utama agar hidden input tidak
  // menyebabkan selisih/estimasi berbeda dari nilai yang diketik pengguna.
  function readPaguBaru() {
    var displayValue = $('#revPaguBaruDisplay').val();
    var paguBaru = window.cleanRupiah
      ? window.cleanRupiah(displayValue)
      : (parseFloat($('#revPaguBaru').val()) || 0);
    $('#revPaguBaru').val(paguBaru);
    return paguBaru;
  }

  function hitungKalkulasi() {
    var paguBaru = readPaguBaru();
    var selisih = paguBaru - currentPagu;
    var sisaBaru = paguBaru - currentRealisasi;

    // Tampilkan selisih
    if (selisih > 0) {
      $('#revBadgeSelisih')
        .text('+ ' + formatRupiah(selisih) + ' (Penambahan)')
        .attr('class', 'badge text-bg-success fs-6');
    } else if (selisih < 0) {
      $('#revBadgeSelisih')
        .text('- ' + formatRupiah(Math.abs(selisih)) + ' (Pengurangan)')
        .attr('class', 'badge text-bg-warning fs-6');
    } else {
      $('#revBadgeSelisih')
        .text('Rp 0 (Tidak ada perubahan)')
        .attr('class', 'badge bg-secondary fs-6');
    }

    // Tampilkan estimasi sisa baru
    $('#revEstimasiSisaBaru').text(formatRupiah(sisaBaru));
    if (sisaBaru < 0) {
      $('#revEstimasiSisaBaru').attr('class', 'fs-6 fw-bold text-danger');
    } else {
      $('#revEstimasiSisaBaru').attr('class', 'fs-6 fw-bold text-success');
    }

    // Validasi aturan: Pagu Baru tidak boleh < Realisasi
    if (paguBaru < currentRealisasi) {
      $('#alertMinRealisasi').text(formatRupiah(currentRealisasi));
      $('#alertDefisit').removeClass('d-none');
      $('#btnSubmitRevisi').prop('disabled', true);
    } else {
      $('#alertDefisit').addClass('d-none');
      $('#btnSubmitRevisi').prop('disabled', false);
    }
  }

  // Buka Modal Revisi
  $('.js-revisi-btn').on('click', function(){
    var btn = $(this);
    var tahunId = btn.data('tahun-id');
    var penerimaId = btn.data('penerima-id');
    var nama = btn.data('penerima-nama');
    var tipe = btn.data('penerima-tipe');
    var jabatan = btn.data('penerima-jabatan');
    currentPagu = parseFloat(btn.data('pagu')) || 0;
    currentRealisasi = parseFloat(btn.data('realisasi')) || 0;
    var currentSisa = parseFloat(btn.data('sisa')) || 0;

    $('#revTahunId').val(tahunId);
    $('#revPenerimaId').val(penerimaId);
    $('#revPenerimaNama').text(nama);
    $('#revPenerimaTipe').text(tipe).attr('class', 'badge ' + (tipe === 'DPRD' ? 'text-bg-primary' : 'text-bg-secondary'));
    $('#revPenerimaJabatan').text(jabatan);

    $('#revPaguSaatIni').text(formatRupiah(currentPagu));
    $('#revRealisasiSaatIni').text(formatRupiah(currentRealisasi));
    $('#revSisaSaatIni').text(formatRupiah(currentSisa));

    $('#revPaguBaru').val(currentPagu);
    $('#revPaguBaruDisplay').val(currentPagu ? Number(currentPagu).toLocaleString('id-ID') : '');
    var kataAwal = currentPagu ? window.terbilang(currentPagu) : '';
    $('#revTerbilangText').text(kataAwal);
    if (kataAwal) {
      $('#revPembilangBox').removeClass('d-none');
    } else {
      $('#revPembilangBox').addClass('d-none');
    }

    $('#revAlasan').val('');
    hitungKalkulasi();

    bootstrap.Modal.getOrCreateInstance('#revisiModal').show();
  });

  // Event hitung kalkulasi saat user mengetik pagu baru display
  $('#revPaguBaruDisplay').on('input', hitungKalkulasi);

  // Submit form revisi
  $('#revisiForm').on('submit', function(e){
    e.preventDefault();
    var form = $(this);
    var paguBaru = readPaguBaru();
    var alasan = $('#revAlasan').val().trim();

    if (paguBaru < currentRealisasi) {
      App.toast('Pagu baru tidak boleh lebih kecil dari dana yang sudah terpakai (' + formatRupiah(currentRealisasi) + ')', 'error');
      return;
    }
    if (Math.abs(paguBaru - currentPagu) < 0.01) {
      App.toast('Nilai pagu baru sama dengan nilai saat ini. Tidak ada perubahan.', 'warning');
      return;
    }
    if (!alasan) {
      App.toast('Alasan revisi anggaran wajib diisi.', 'warning');
      $('#revAlasan').focus();
      return;
    }

    App.ajax({
      url: form.attr('action'),
      data: form.serialize()
    }).done(function(res){
      if (res.status) {
        bootstrap.Modal.getOrCreateInstance('#revisiModal').hide();
        setTimeout(function(){
          location.reload();
        }, 600);
      }
    });
  });
});
</script>

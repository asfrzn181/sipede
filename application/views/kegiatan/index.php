<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
  <div>
    <h1 class="h5 fw-bold text-dark mb-0">Daftar Kegiatan &amp; Surat Tugas</h1>
    <p class="text-muted small mb-0">Input kegiatan sampai bukti siap dicetak. Setelah bukti dibuka, kegiatan tersimpan di History.</p>
  </div>
  <?php if($can_create): ?>
  <button class="btn btn-primary d-flex align-items-center gap-1 shadow-sm" id="btnSuratTugasBaru">
    <i class="bi bi-plus-lg"></i>
    <span>Surat Tugas Baru</span>
  </button>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-calendar2-event text-primary"></i>
      <span>Data Surat Tugas Perjalanan Dinas</span>
    </div>
    <div class="d-flex align-items-center gap-2">
      <label for="filterTahunKegiatan" class="small text-muted mb-0">Tahun Anggaran</label>
      <select class="form-select form-select-sm" id="filterTahunKegiatan" style="width: 150px;">
        <?php foreach (($tahun_anggaran ?? array()) as $ta): ?>
          <option value="<?= (int)$ta['id'] ?>" <?= (int)$ta['id'] === (int)$tahun_aktif_id ? 'selected' : '' ?>>
            <?= (int)$ta['tahun'] ?><?= !empty($ta['is_locked']) ? ' (Terkunci)' : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="card-body p-3">
    <ul class="nav nav-tabs mb-3" id="kegiatanTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" type="button" data-tab="unfinished">
          <i class="bi bi-pencil-square me-1"></i> Belum Selesai
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" type="button" data-tab="history">
          <i class="bi bi-clock-history me-1"></i> History Kegiatan
        </button>
      </li>
    </ul>
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="kegiatanTable">
        <thead class="table-light">
          <tr>
            <th style="width: 150px;">No. Surat Tugas</th>
            <th>Tema Kegiatan</th>
            <th style="width: 130px;">Pelaksanaan</th>
            <th>Tempat</th>
            <th style="width: 80px;" class="text-center">Peserta</th>
            <th style="width: 140px;" class="text-end">Total Biaya</th>
            <th style="width: 95px;" class="text-center">Status</th>
            <th class="no-sort text-end" style="width: 170px;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Input / Edit Surat Tugas (Parent Data) -->
<div class="modal fade" id="modalSuratTugas" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form class="js-ajax-form" action="<?= site_url('kegiatan/save_header') ?>" id="formSuratTugas">
        <input type="hidden" name="id" id="headerIdInput">
        <input type="hidden" name="tahun_anggaran_id" id="headerTahunHidden" value="<?= (int)$tahun_aktif_id ?>">

        <div class="modal-header bg-light">
          <h5 class="modal-title fw-bold" id="modalHeaderTitle">
            <i class="bi bi-file-earmark-plus text-primary me-1"></i> Surat Tugas Baru (Parent Data)
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body p-4">
          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">No. Surat Tugas <span class="text-danger">*</span></label>
              <input class="form-control" name="no_surat_tugas" id="inpNoSurat" placeholder="Contoh: 090/ST/DPRD/2026" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tanggal Surat Tugas <span class="text-danger">*</span></label>
              <input class="form-control" type="date" name="tanggal_surat_tugas" id="inpTglSurat" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tahun Anggaran <span class="text-danger">*</span></label>
              <select class="form-select" id="inpTahunAnggaran" required>
                <?php foreach (($tahun_anggaran ?? array()) as $ta): ?>
                  <option value="<?= (int)$ta['id'] ?>" <?= (int)$ta['id'] === (int)$tahun_aktif_id ? 'selected' : '' ?> <?= !empty($ta['is_locked']) ? 'disabled' : '' ?>>
                    <?= (int)$ta['tahun'] ?><?= !empty($ta['is_locked']) ? ' (Terkunci)' : '' ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="form-text small">Tahun ini menjadi acuan pagu, pos anggaran, dan rincian biaya kegiatan.</div>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tanggal Pelaksanaan Mulai <span class="text-danger">*</span></label>
              <input class="form-control" type="date" name="tanggal_mulai" id="inpTglMulai" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tanggal Pelaksanaan Selesai <span class="text-danger">*</span></label>
              <input class="form-control" type="date" name="tanggal_selesai" id="inpTglSelesai" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Tempat / Kota Tujuan <span class="text-danger">*</span></label>
              <input class="form-control" name="tempat" id="inpTempat" placeholder="Contoh: Jakarta Pusat / Bandung" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label small fw-semibold">Status Awal</label>
              <select class="form-select" name="status" id="inpStatus">
                <option value="DRAFT">DRAFT</option>
                <option value="AKTIF">AKTIF</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label small fw-semibold">Tema / Maksud Perjalanan Dinas <span class="text-danger">*</span></label>
              <textarea class="form-control" name="tema" id="inpTema" rows="2" placeholder="Contoh: Kunjungan Kerja Komisi III Terkait Pengelolaan Tata Ruang Wilayah" required></textarea>
              <div class="form-text small text-muted">Setelah menyimpan Surat Tugas (parent), Anda akan langsung diarahkan ke menu <strong>Rincian Kegiatan</strong> untuk mengisi peserta dan pendamping.</div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
            <i class="bi bi-arrow-right-circle"></i>
            <span>Simpan &amp; Lanjut ke Rincian Peserta</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  // Inisialisasi DataTable Server-Side
  var kegiatanDT = window.createDataTableServerSide('#kegiatanTable', {
    ajax: {
      url: window.APP.baseUrl + 'kegiatan/data',
      data: function(d){
        d.tahun_anggaran_id = $('#filterTahunKegiatan').val();
        d.tab = $('#kegiatanTabs .nav-link.active').data('tab') || 'unfinished';
      }
    },
    order: [[2, 'desc']],
    columnDefs: [
      { targets: [4, 6], className: 'text-center' },
      { targets: 5, className: 'text-end' },
      { targets: 7, className: 'text-end', orderable: false }
    ]
  });

  $('#filterTahunKegiatan').on('change', function(){
    kegiatanDT.ajax.reload();
  });

  $('#kegiatanTabs .nav-link').on('click', function(){
    $('#kegiatanTabs .nav-link').removeClass('active');
    $(this).addClass('active');
    kegiatanDT.ajax.reload();
  });

  $('#inpTahunAnggaran').on('change', function(){
    if (!$(this).prop('disabled')) $('#headerTahunHidden').val($(this).val());
  });

  // Buka Modal Surat Tugas Baru
  $('#btnSuratTugasBaru').on('click', function(){
    $('#formSuratTugas')[0].reset();
    $('#headerIdInput').val('');
    $('#inpTahunAnggaran').prop('disabled', false).val($('#filterTahunKegiatan').val() || '<?= (int)$tahun_aktif_id ?>');
    $('#headerTahunHidden').val($('#inpTahunAnggaran').val());
    $('#inpTglSurat').val(new Date().toISOString().split('T')[0]);
    $('#modalHeaderTitle').html('<i class="bi bi-file-earmark-plus text-primary me-1"></i> Surat Tugas Baru (Parent Data)');
    bootstrap.Modal.getOrCreateInstance('#modalSuratTugas').show();
  });

  // Edit Surat Tugas (Parent Data) - event delegation untuk semua pagination
  $(document).on('click', '.js-edit-header', function(){
    var r = $(this).data('row');
    if (typeof r === 'string') {
      try { r = JSON.parse(r); } catch(e){}
    }
    $('#formSuratTugas')[0].reset();
    $('#headerIdInput').val(r.id);
    $('#inpTahunAnggaran').val(r.tahun_anggaran_id).prop('disabled', true);
    $('#headerTahunHidden').val(r.tahun_anggaran_id);
    $('#inpNoSurat').val(r.no_surat_tugas);
    $('#inpTglSurat').val(r.tanggal_surat_tugas);
    $('#inpTglMulai').val(r.tanggal_mulai);
    $('#inpTglSelesai').val(r.tanggal_selesai);
    $('#inpTempat').val(r.tempat);
    $('#inpTema').val(r.tema || r.nama_kegiatan);
    $('#inpStatus').val(r.status);
    $('#modalHeaderTitle').html('<i class="bi bi-pencil-square text-primary me-1"></i> Edit Surat Tugas: ' + (r.no_surat_tugas || ''));
    bootstrap.Modal.getOrCreateInstance('#modalSuratTugas').show();
  });
});
</script>

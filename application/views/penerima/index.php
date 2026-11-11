<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">
    Daftar Anggota DPRD dan jabatan/periode keanggotaan.
  </p>
  <?php if($can_manage): ?>
  <button class="btn btn-primary d-flex align-items-center gap-1" id="btnTambahPenerima">
    <i class="bi bi-plus-lg"></i>
    <span>Tambah Anggota DPRD</span>
  </button>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="penerimaTable">
        <thead class="table-light">
          <tr>
            <th>Nama Lengkap</th>
            <?php if(empty($filter_tipe)): ?><th style="width: 90px;">Tipe</th><?php endif; ?>
            <th>Jabatan</th>
            <th style="width: 140px;">Periode</th>
            <th style="width: 90px;">Status</th>
            <?php if($can_manage): ?>
            <th class="no-sort text-end" style="width: 90px;">Aksi</th>
            <?php endif; ?>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah / Edit Penerima -->
<div class="modal fade" id="dataModal">
  <div class="modal-dialog modal-fullscreen-sm-down">
    <div class="modal-content">
      <form class="js-ajax-form" action="<?= site_url('penerima/save') ?>">
        <input type="hidden" name="id">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalPenerimaTitle">Tambah Penerima</h5>
          <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
        </div>
        <div class="modal-body">

          <label class="form-label small fw-semibold">Nama Lengkap</label>
          <input class="form-control mb-3" name="nama" placeholder="Nama Lengkap &amp; Gelar" required>

          <input type="hidden" name="tipe" id="tipePenerima" value="DPRD">

          <label class="form-label small fw-semibold">Jabatan</label>
          <input class="form-control mb-3" name="jabatan" placeholder="Contoh: Ketua / Anggota / Pendamping Ahli">

          <!-- Periode — tampil hanya jika tipe = DPRD -->
          <div id="periodeSection">
            <div class="d-flex align-items-center gap-2 mb-1">
              <label class="form-label small fw-semibold mb-0">Periode Jabatan</label>
              <span class="badge text-bg-light border small">Opsional</span>
            </div>
            <div class="row g-2 mb-3">
              <div class="col-6">
                <input class="form-control" type="number" name="periode_mulai" placeholder="Mulai, cth: 2025" min="2000" max="2100">
              </div>
              <div class="col-auto d-flex align-items-center px-0">
                <span class="text-muted">&ndash;</span>
              </div>
              <div class="col">
                <input class="form-control" type="number" name="periode_akhir" placeholder="Akhir, cth: 2029" min="2000" max="2100">
              </div>
            </div>
          </div>

          <label class="form-label small fw-semibold">Status Keaktifan</label>
          <select class="form-select" name="is_active">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  var penerimaDT = window.createDataTableServerSide('#penerimaTable', {
    ajax: {
      url: window.APP.baseUrl + 'penerima/data<?= !empty($filter_tipe) ? '?tipe=' . rawurlencode($filter_tipe) : '' ?>'
    },
    order: [[0, 'asc']],
    columnDefs: [
      { targets: [<?= empty($filter_tipe) ? '1, ' : '' ?><?= empty($filter_tipe) ? '4' : '3' ?>], className: 'text-center' },
      <?php if($can_manage): ?>
      { targets: <?= empty($filter_tipe) ? '5' : '4' ?>, className: 'text-end', orderable: false }
      <?php endif; ?>
    ]
  });

  // Master ini khusus anggota DPRD; periode selalu ditampilkan.
  function togglePeriode() {
    $('#periodeSection').show();
  }
  togglePeriode();

  // Open modal for new entry
  $('#btnTambahPenerima').on('click', function(){
    var f = $('#dataModal form');
    f[0].reset();
    f.find('[name=id]').val('');
    $('#modalPenerimaTitle').text('Tambah Penerima');
    togglePeriode();
    bootstrap.Modal.getOrCreateInstance('#dataModal').show();
  });

  // Open modal to edit (delegated event agar berfungsi di seluruh halaman pagination)
  $(document).on('click', '.js-edit-penerima', function(){
    var r = $(this).data('row');
    if (typeof r === 'string') {
      try { r = JSON.parse(r); } catch(e){}
    }
    var f = $('#dataModal form');
    f[0].reset();
    Object.keys(r).forEach(function(k){
      f.find('[name="'+k+'"]').val(r[k] === null ? '' : r[k]);
    });
    $('#modalPenerimaTitle').text('Edit Penerima');
    togglePeriode();
    bootstrap.Modal.getOrCreateInstance('#dataModal').show();
  });
});
</script>

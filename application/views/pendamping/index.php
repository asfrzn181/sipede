<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Daftar master pendamping / notulen / ajudan.</p>
  <?php if($can_manage): ?>
  <button class="btn btn-primary d-flex align-items-center gap-1" id="btnTambahPendamping">
    <i class="bi bi-plus-lg"></i>
    <span>Tambah Pendamping</span>
  </button>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="pendampingTable">
        <thead class="table-light">
          <tr>
            <th>Nama Lengkap</th>
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

<div class="modal fade" id="dataModal">
  <div class="modal-dialog modal-fullscreen-sm-down">
    <div class="modal-content">
      <form class="js-ajax-form" action="<?= site_url('pendamping/save') ?>">
        <input type="hidden" name="id">
        <div class="modal-header">
          <h5 class="modal-title fw-bold" id="modalPendampingTitle">Tambah Pendamping</h5>
          <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
        </div>
        <div class="modal-body">
          <label class="form-label small fw-semibold">Nama Lengkap</label>
          <input class="form-control mb-3" name="nama" placeholder="Nama Lengkap &amp; Gelar" required>

          <input type="hidden" name="jabatan" value="Pendamping">

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
  var pendampingDT = window.createDataTableServerSide('#pendampingTable', {
    ajax: { url: window.APP.baseUrl + 'pendamping/data' },
    order: [[0, 'asc']],
    columnDefs: [
      { targets: [1], className: 'text-center' }
      <?php if($can_manage): ?>
      , { targets: [2], className: 'text-end', orderable: false }
      <?php endif; ?>
    ]
  });

  $('#btnTambahPendamping').on('click', function(){
    var f = $('#dataModal form');
    f[0].reset();
    f.find('[name=id]').val('');
    $('#modalPendampingTitle').text('Tambah Pendamping');
    bootstrap.Modal.getOrCreateInstance('#dataModal').show();
  });

  $(document).on('click', '.js-edit-pendamping', function(){
    var r = $(this).data('row');
    if (typeof r === 'string') { try { r = JSON.parse(r); } catch(e){} }
    var f = $('#dataModal form');
    f[0].reset();
    f.find('[name=id]').val(r.id);
    f.find('[name=nama]').val(r.nama);
    f.find('[name=jabatan]').val(r.jabatan);
    f.find('[name=is_active]').val(r.is_active);
    $('#modalPendampingTitle').text('Edit Pendamping');
    bootstrap.Modal.getOrCreateInstance('#dataModal').show();
  });
});
</script>

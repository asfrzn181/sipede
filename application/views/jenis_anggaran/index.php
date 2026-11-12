<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Master kelompok sumber pagu seperti Fasilitas DPRD, Perjalanan Dinas, Rapat, Honorarium, dan jenis lainnya.</p>
  <?php if(!empty($can_manage)): ?>
  <button class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#jenisAnggaranModal">
    <i class="bi bi-plus-lg"></i><span>Tambah Jenis Anggaran</span>
  </button>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="jenisAnggaranTable">
        <thead class="table-light"><tr>
          <th style="width:180px">Kode</th><th>Nama Jenis Anggaran</th><th>Keterangan</th>
          <th style="width:90px">Urutan</th><th style="width:110px">Status</th>
          <?php if(!empty($can_manage)): ?><th class="no-sort text-end" style="width:80px">Aksi</th><?php endif; ?>
        </tr></thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<?php if(!empty($can_manage)): ?>
<div class="modal fade" id="jenisAnggaranModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <form class="js-ajax-form" action="<?= site_url('jenis_anggaran/save') ?>">
      <input type="hidden" name="id">
      <div class="modal-header"><h5 class="modal-title fw-bold">Tambah Jenis Anggaran</h5><button class="btn-close" data-bs-dismiss="modal" type="button"></button></div>
      <div class="modal-body">
        <label class="form-label small fw-semibold">Kode</label>
        <input class="form-control mb-3" name="kode" placeholder="Contoh: RAPAT_KHUSUS" required>
        <label class="form-label small fw-semibold">Nama</label>
        <input class="form-control mb-3" name="nama" placeholder="Contoh: Rapat Khusus" required>
        <label class="form-label small fw-semibold">Keterangan</label>
        <textarea class="form-control mb-3" name="keterangan" rows="2" placeholder="Peruntukan pagu ini..."></textarea>
        <label class="form-label small fw-semibold">Urutan</label>
        <input class="form-control mb-3" name="urutan" type="number" min="0" value="99">
        <label class="form-label small fw-semibold">Status</label>
        <select class="form-select" name="is_active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select>
      </div>
      <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal" type="button">Batal</button><button class="btn btn-primary" type="submit">Simpan</button></div>
    </form>
  </div></div>
</div>
<?php endif; ?>

<script>
$(function(){
  window.createDataTableServerSide('#jenisAnggaranTable', {
    ajax: { url: window.APP.baseUrl + 'jenis_anggaran/data' },
    order: [[3, 'asc']],
    columnDefs: [
      { targets: 3, className: 'text-center' },
      <?php if(!empty($can_manage)): ?>{ targets: 5, className: 'text-end', orderable: false }<?php endif; ?>
    ]
  });
  $(document).on('click', '.js-edit-jenis-anggaran', function(){
    var row = $(this).data('row');
    if (typeof row === 'string') { try { row = JSON.parse(row); } catch(e){} }
    var f = $('#jenisAnggaranModal form'); f[0].reset();
    f.find('[name="id"]').val(row.id || '');
    f.find('[name="kode"]').val(row.kode || '');
    f.find('[name="nama"]').val(row.nama || '');
    f.find('[name="keterangan"]').val(row.keterangan || '');
    f.find('[name="urutan"]').val(row.urutan || 0);
    f.find('[name="is_active"]').val(String(row.is_active));
    $('#jenisAnggaranModal .modal-title').text('Edit Jenis Anggaran');
    bootstrap.Modal.getOrCreateInstance('#jenisAnggaranModal').show();
  });
  $('#jenisAnggaranModal').on('hidden.bs.modal', function(){
    var f = $('#jenisAnggaranModal form'); f[0].reset(); f.find('[name="id"]').val('');
    $('#jenisAnggaranModal .modal-title').text('Tambah Jenis Anggaran');
  });
});
</script>

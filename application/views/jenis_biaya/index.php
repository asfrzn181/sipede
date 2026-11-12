<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Master komponen biaya kegiatan dinas.</p>
  <?php if(!empty($can_manage)): ?>
  <button class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#dataModal">
    <i class="bi bi-plus-lg"></i>
    <span>Tambah Jenis Biaya</span>
  </button>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="jenisBiayaTable">
        <thead class="table-light">
          <tr>
            <th style="width: 140px;">Kode</th>
            <th>Nama Jenis Biaya</th>
            <th style="width: 180px;">Satuan</th>
            <th style="width: 180px;">Baku Otomatis</th>
            <th>Keterangan</th>
            <?php if(!empty($can_manage)): ?><th class="no-sort text-end" style="width: 80px;">Aksi</th><?php endif; ?>
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
      <form class="js-ajax-form" action="<?= site_url('jenis_biaya/save') ?>">
        <input type="hidden" name="id">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Tambah Jenis Biaya</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label small fw-semibold">Kode Biaya</label>
          <input class="form-control mb-3" name="kode" placeholder="Contoh: TRANSPORT" required>
          <label class="form-label small fw-semibold">Nama Jenis Biaya</label>
          <input class="form-control mb-3" name="nama" placeholder="Contoh: Transportasi Luar Daerah" required>
          <label class="form-label small fw-semibold">Satuan</label>
          <input class="form-control mb-3" name="satuan" placeholder="Contoh: orang / hari / tiket">
          <label class="form-label small fw-semibold">Keterangan Tambahan</label>
          <textarea class="form-control mb-3" name="keterangan" rows="2" placeholder="Contoh: Dibayarkan sesuai standar perjalanan dinas."></textarea>
          <div class="form-check">
            <input class="form-check-input" type="checkbox" name="tampil_di_kegiatan" value="1" id="tampilDiKegiatan" checked>
            <label class="form-check-label small" for="tampilDiKegiatan">Jadikan komponen baku otomatis saat tambah peserta</label>
          </div>
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
  window.createDataTableServerSide('#jenisBiayaTable', {
    ajax: {
      url: window.APP.baseUrl + 'jenis_biaya/data'
    },
    order: [[1, 'asc']],
    columnDefs: [
      { targets: 3, className: 'text-center' },
      <?php if(!empty($can_manage)): ?>
      { targets: 5, className: 'text-end', orderable: false }
      <?php endif; ?>
    ]
  });

  $(document).on('click', '.js-edit-jenis', function(){
    var r = $(this).data('row');
    if (typeof r === 'string') { try { r = JSON.parse(r); } catch(e){} }
    var f = $('#dataModal form');
    f[0].reset();
    f.find('[name="id"]').val(r.id || '');
    f.find('[name="kode"]').val(r.kode || '');
    f.find('[name="nama"]').val(r.nama || '');
    f.find('[name="satuan"]').val(r.satuan || '');
    f.find('[name="keterangan"]').val(r.keterangan || '');
    f.find('[name="tampil_di_kegiatan"]').prop('checked', String(r.tampil_di_kegiatan) === '1');
    $('#dataModal .modal-title').text('Edit Jenis Biaya');
    bootstrap.Modal.getOrCreateInstance('#dataModal').show();
  });

  $('#dataModal').on('hidden.bs.modal', function(){
    $('#dataModal form')[0].reset();
    $('#dataModal form [name="id"]').val('');
    $('#dataModal .modal-title').text('Tambah Jenis Biaya');
  });
});
</script>

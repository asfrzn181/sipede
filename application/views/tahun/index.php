<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Kelola tahun anggaran dan status penguncian transaksi. Gunakan tombol <strong>Pos Anggaran</strong> untuk mengatur pagu sesuai tahun.</p>
  <?php if($can_manage): ?>
  <button class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#dataModal">
    <i class="bi bi-plus-lg"></i>
    <span>Tambah Tahun</span>
  </button>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="tahunTable">
        <thead class="table-light">
          <tr>
            <th>Tahun Anggaran</th>
            <th style="width: 160px;" class="text-center">Status Penguncian</th>
            <th class="no-sort text-end" style="width: 200px;">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah Tahun -->
<div class="modal fade" id="dataModal">
  <div class="modal-dialog modal-fullscreen-sm-down">
    <div class="modal-content">
      <form class="js-ajax-form" action="<?= site_url('tahun/save') ?>">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Tambah Tahun Anggaran</h5>
          <button class="btn-close" data-bs-dismiss="modal" type="button"></button>
        </div>
        <div class="modal-body">
          <label class="form-label small fw-semibold">Tahun</label>
          <input class="form-control" type="number" name="tahun" value="<?= date('Y') ?>" min="2000" max="2100" required>
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
  window.createDataTableServerSide('#tahunTable', {
    ajax: {
      url: window.APP.baseUrl + 'tahun/data'
    },
    order: [[0, 'desc']],
    columnDefs: [
      { targets: 1, className: 'text-center' },
      { targets: 2, className: 'text-end', orderable: false }
    ]
  });
});
</script>

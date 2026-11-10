<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold d-flex align-items-center justify-content-between">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-journal-text text-primary"></i>
      <span>Audit Log Sistem</span>
    </div>
    <span class="badge text-bg-light border text-muted small">Mencatat seluruh transaksi &amp; revisi</span>
  </div>
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover table-sm align-middle mb-0" id="auditTable">
        <thead class="table-light">
          <tr>
            <th style="width: 150px;">Waktu</th>
            <th>Pengguna</th>
            <th>Entitas</th>
            <th style="width: 100px;">Aksi</th>
            <th style="width: 100px;">Entity ID</th>
            <th class="no-sort text-end" style="width: 90px;">Rincian</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Rincian Audit Log -->
<div class="modal fade" id="auditDetailModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-light">
        <h5 class="modal-title fw-bold">
          <i class="bi bi-journal-check text-primary me-1"></i> Rincian Audit Log
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="row g-2 mb-3 pb-3 border-bottom">
          <div class="col-6 col-md-3">
            <small class="text-muted d-block">Waktu</small>
            <strong id="auditModalWaktu">-</strong>
          </div>
          <div class="col-6 col-md-3">
            <small class="text-muted d-block">Pengguna</small>
            <strong id="auditModalUser">-</strong>
          </div>
          <div class="col-6 col-md-3">
            <small class="text-muted d-block">Entitas</small>
            <span class="badge text-bg-light border" id="auditModalEntity">-</span>
          </div>
          <div class="col-6 col-md-3">
            <small class="text-muted d-block">Aksi</small>
            <span class="badge text-bg-primary" id="auditModalAction">-</span>
          </div>
        </div>

        <div class="row g-3">
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-muted">Data Sebelum (Old Data)</label>
            <pre class="bg-light p-3 rounded border small" id="auditModalOld" style="max-height: 260px; overflow-y: auto;"></pre>
          </div>
          <div class="col-12 col-md-6">
            <label class="form-label small fw-bold text-primary">Data Sesudah (New Data)</label>
            <pre class="bg-light p-3 rounded border small" id="auditModalNew" style="max-height: 260px; overflow-y: auto;"></pre>
          </div>
        </div>
      </div>
      <div class="modal-footer bg-light">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  var auditDT = window.createDataTableServerSide('#auditTable', {
    ajax: {
      url: window.APP.baseUrl + 'audit/data'
    },
    order: [[0, 'desc']],
    columnDefs: [
      { targets: [2, 3, 4], className: 'text-center' },
      { targets: 5, className: 'text-end', orderable: false }
    ]
  });

  $(document).on('click', '.js-view-audit', function(){
    var btn = $(this);
    $('#auditModalWaktu').text(btn.data('waktu'));
    $('#auditModalUser').text(btn.data('user'));
    $('#auditModalEntity').text(btn.data('entity'));
    $('#auditModalAction').text(btn.data('action'));

    try {
      var oldObj = typeof btn.data('old') === 'object' ? btn.data('old') : JSON.parse(btn.data('old') || '{}');
      $('#auditModalOld').text(JSON.stringify(oldObj, null, 2));
    } catch(e) {
      $('#auditModalOld').text(btn.data('old') || '-');
    }

    try {
      var newObj = typeof btn.data('new') === 'object' ? btn.data('new') : JSON.parse(btn.data('new') || '{}');
      $('#auditModalNew').text(JSON.stringify(newObj, null, 2));
    } catch(e) {
      $('#auditModalNew').text(btn.data('new') || '-');
    }

    bootstrap.Modal.getOrCreateInstance('#auditDetailModal').show();
  });
});
</script>

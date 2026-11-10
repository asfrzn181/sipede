<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center gap-2">
      <i class="bi bi-check2-square text-primary fs-5"></i>
      <span>Antrian Verifikasi Kegiatan Perjalanan Dinas</span>
    </div>
    <span class="badge text-bg-warning">Verifikator diwajibkan memeriksa rincian kegiatan &amp; pagu sebelum menyetujui</span>
  </div>
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="antrianTable">
        <thead class="table-light">
          <tr>
            <th>Tema Kegiatan &amp; No. Surat</th>
            <th>Tanggal Pelaksanaan</th>
            <th>Pengaju</th>
            <th class="no-sort text-end" style="width: 250px;">Aksi Verifikasi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(function(){
  var antrianDT = window.createDataTableServerSide('#antrianTable', {
    ajax: {
      url: window.APP.baseUrl + 'kegiatan/antrian_data'
    },
    order: [[1, 'asc']],
    columnDefs: [
      { targets: 3, className: 'text-end', orderable: false }
    ]
  });

  // Konfirmasi Setujui dengan SweetAlert (Delegated Event)
  $(document).on('click', '.js-verify', function(){
    var id = $(this).data('id');
    var nama = $(this).data('nama') || 'kegiatan ini';
    Swal.fire({
      title: 'Setujui Kegiatan Ini?',
      html: 'Apakah Anda yakin ingin <strong>menyetujui</strong> kegiatan:<br><span class="text-primary fw-bold">"' + escapeHtml(nama) + '"</span>?<br><small class="text-muted">Pastikan Anda telah memeriksa rincian peserta dan beban pos anggaran.</small>',
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
            Swal.fire('Disetujui!', 'Kegiatan telah disetujui dan berstatus AKTIF.', 'success').then(function(){
              if (antrianDT) antrianDT.ajax.reload();
              else location.reload();
            });
          }
        });
      }
    });
  });

  // Konfirmasi Tolak dengan SweetAlert (Wajib Alasan) (Delegated Event)
  $(document).on('click', '.js-reject', function(){
    var id = $(this).data('id');
    var nama = $(this).data('nama') || 'kegiatan ini';
    Swal.fire({
      title: 'Tolak Kegiatan?',
      html: 'Menolak kegiatan: <span class="text-danger fw-bold">"' + escapeHtml(nama) + '"</span>',
      input: 'textarea',
      inputLabel: 'Catatan / Alasan Penolakan (Wajib Diisi):',
      inputPlaceholder: 'Tuliskan catatan perbaikan untuk pengaju kegiatan...',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: '<i class="bi bi-x-circle me-1"></i> Kirim Penolakan',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#dc2626',
      inputValidator: function(value) {
        if (!value || !value.trim()) {
          return 'Catatan penolakan wajib diisi agar pengaju dapat memperbaiki datanya!';
        }
      }
    }).then(function(r){
      if(r.isConfirmed && r.value){
        App.ajax({
          url: window.APP.baseUrl + 'kegiatan/verify/' + id,
          data: {
            action: 'tolak',
            catatan_verifikasi: r.value
          }
        }).done(function(res){
          if(res.status) {
            Swal.fire('Kegiatan Ditolak', 'Status kegiatan telah diubah menjadi DITOLAK.', 'info').then(function(){
              if (antrianDT) antrianDT.ajax.reload();
              else location.reload();
            });
          }
        });
      }
    });
  });
});
</script>

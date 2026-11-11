<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Kelola akun pengguna sistem dan tautan penerima pendamping.</p>
  <div class="d-flex gap-2">
    <button class="btn btn-outline-primary d-flex align-items-center gap-1" id="massPendamping">
      <i class="bi bi-people"></i>
      <span>Buat Akun Pendamping</span>
    </button>
    <button class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#dataModal">
      <i class="bi bi-person-plus"></i>
      <span>Tambah Pengguna</span>
    </button>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0" id="userTable">
        <thead class="table-light">
          <tr>
            <th>Nama Lengkap</th>
            <th>Username</th>
            <th>Role</th>
            <th>Tautan Penerima</th>
            <th style="width: 120px;">Status</th>
            <?php if($can_reset_password): ?>
            <th style="width: 170px;" class="text-end">Aksi</th>
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
      <form class="js-ajax-form" action="<?= site_url('user/save') ?>">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Tambah Pengguna</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <label class="form-label small fw-semibold">Nama Lengkap</label>
          <input class="form-control mb-3" name="nama" placeholder="Nama Lengkap" required>
          <label class="form-label small fw-semibold">Username</label>
          <input class="form-control mb-3" name="username" placeholder="Username" required>
          <label class="form-label small fw-semibold">Password</label>
          <input class="form-control mb-3" type="password" name="password" placeholder="Password awal (kosongkan untuk acak)">
          <label class="form-label small fw-semibold">Pilih Role</label>
          <select class="form-select mb-3" name="role_id" required>
            <?php foreach($roles as $role): ?>
            <option value="<?= encrypt_id($role['id']) ?>"><?= html_escape($role['nama']) ?></option>
            <?php endforeach; ?>
          </select>
          <label class="form-label small fw-semibold">Tautan Penerima (Khusus Pendamping)</label>
          <select class="form-select mb-3" name="penerima_id">
            <option value="">Tanpa penerima</option>
            <?php foreach($pendamping as $person): ?>
            <option value="<?= encrypt_id($person['id']) ?>"><?= html_escape($person['nama']) ?></option>
            <?php endforeach; ?>
          </select>
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
  var userDT = window.createDataTableServerSide('#userTable', {
    ajax: {
      url: window.APP.baseUrl + 'user/data'
    },
    order: [[0, 'asc']],
    columnDefs: [
      { targets: 4, className: 'text-center' },
      <?php if($can_reset_password): ?>
      { targets: 5, className: 'text-end', orderable: false }
      <?php endif; ?>
    ]
  });

  $(document).on('click', '.js-reset-password', function(){
    var id = $(this).data('id');
    var name = $(this).data('name');

    Swal.fire({
      title: 'Reset password?',
      text: 'Password baru untuk ' + name + ' akan dibuat secara acak.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, reset',
      cancelButtonText: 'Batal',
      confirmButtonColor: '#d97706'
    }).then(function(result){
      if (!result.isConfirmed) return;

      App.ajax({url: window.APP.baseUrl + 'user/reset_password/' + id}).done(function(r){
        if (r.status && r.data && r.data.password_awal) {
          Swal.fire({
            title: 'Password baru',
            html: '<p class="mb-2">Simpan dan sampaikan password ini kepada pengguna:</p>'
              + '<code class="fs-4 user-select-all">' + escapeHtml(r.data.password_awal) + '</code>',
            icon: 'success',
            confirmButtonText: 'Selesai'
          });
        }
      });
    });
  });

  $('#massPendamping').on('click', function(){
    App.ajax({url: window.APP.baseUrl + 'user/mass_pendamping'}).done(function(r){
      if(r.data && r.data.accounts) {
        Swal.fire({
          title: 'Daftar Akun Pendamping Baru',
          html: '<pre class="text-start bg-light p-3 rounded" style="font-size: 0.85rem;">' + r.data.accounts.map(function(x){ return x.username + ' : ' + x.password; }).join('\n') + '</pre>',
          width: 600
        });
      }
    });
  });
});
</script>

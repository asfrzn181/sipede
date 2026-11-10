<?php 
  $groups = array();
  foreach($permissions as $permission) {
    $groups[$permission['modul']][] = $permission;
  }
?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <p class="text-muted mb-0">Kelola role, status, dan hak akses per modul.</p>
  <button class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#roleModal" id="newRole">
    <i class="bi bi-plus-lg"></i>
    <span>Tambah Role</span>
  </button>
</div>

<div class="row g-4">
  <!-- Role List Table -->
  <div class="col-12 col-xl-5">
    <div class="card shadow-sm h-100">
      <div class="card-header bg-white fw-bold d-flex align-items-center gap-2">
        <i class="bi bi-shield-check text-primary"></i>
        <span>Daftar Role</span>
      </div>
      <div class="card-body p-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0 datatable">
            <thead class="table-light">
              <tr>
                <th>Kode</th>
                <th>Role</th>
                <th style="width: 60px;">User</th>
                <th style="width: 80px;">Status</th>
                <th class="no-sort text-end" style="width: 110px;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach($rows as $role): ?>
              <tr>
                <td><code><?= html_escape($role['kode']) ?></code></td>
                <td class="fw-semibold"><?= html_escape($role['nama']) ?></td>
                <td><span class="badge text-bg-light border"><?= (int)$role['users_count'] ?></span></td>
                <td>
                  <span class="badge text-bg-<?= $role['aktif'] ? 'success' : 'secondary' ?>">
                    <?= $role['aktif'] ? 'Aktif' : 'Nonaktif' ?>
                  </span>
                </td>
                <td class="text-nowrap text-end">
                  <?php if($role['kode'] !== 'SUPERADMIN'): ?>
                    <button class="btn btn-sm btn-outline-primary js-edit" data-role='<?= json_encode($role, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>'>
                      Ubah
                    </button>
                    <button class="btn btn-sm btn-outline-secondary js-perm" data-id="<?= (int)$role['id'] ?>">
                      Izin
                    </button>
                  <?php else: ?>
                    <span class="text-muted small">Terkunci</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <!-- Permission Matrix Accordion -->
  <div class="col-12 col-xl-7">
    <div class="card shadow-sm">
      <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-bold"><i class="bi bi-key text-primary me-1"></i> Matriks Permission</span>
        <button class="btn btn-sm btn-outline-secondary" id="checkAllModules" type="button">Pilih Semua Modul</button>
      </div>
      <form id="permissionForm">
        <input type="hidden" name="role_id">
        <div class="accordion accordion-flush" id="permissionAccordion">
          <?php foreach($groups as $module => $items): ?>
          <div class="accordion-item">
            <h2 class="accordion-header">
              <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#mod<?= html_escape($module) ?>">
                <?= html_escape(strtoupper($module)) ?>
                <span class="badge text-bg-light border ms-2"><?= count($items) ?> izin</span>
              </button>
            </h2>
            <div id="mod<?= html_escape($module) ?>" class="accordion-collapse collapse" data-bs-parent="#permissionAccordion">
              <div class="accordion-body">
                <?php foreach($items as $permission): ?>
                <div class="form-check mb-2">
                  <input class="form-check-input perm" type="checkbox" name="permission_ids[]" value="<?= (int)$permission['id'] ?>" data-module="<?= html_escape($module) ?>" data-code="<?= html_escape($permission['kode']) ?>" id="perm_<?= (int)$permission['id'] ?>">
                  <label class="form-check-label" for="perm_<?= (int)$permission['id'] ?>">
                    <code><?= html_escape($permission['kode']) ?></code> — <?= html_escape($permission['nama']) ?>
                  </label>
                </div>
                <?php endforeach; ?>
                <button class="btn btn-sm btn-link px-0 js-module-all" type="button" data-module="<?= html_escape($module) ?>">
                  Pilih semua di modul ini
                </button>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <div class="card-footer bg-white text-end">
          <button class="btn btn-primary" type="submit">
            <i class="bi bi-save me-1"></i> Simpan Permission
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Tambah/Edit Role -->
<div class="modal fade" id="roleModal">
  <div class="modal-dialog modal-fullscreen-sm-down">
    <div class="modal-content">
      <form class="js-ajax-form" action="<?= site_url('role/save') ?>">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Role</h5>
          <button class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id">
          <label class="form-label small fw-semibold">Kode Role</label>
          <input class="form-control mb-3" name="kode" required>
          <label class="form-label small fw-semibold">Nama Role</label>
          <input class="form-control mb-3" name="nama" required>
          <label class="form-label small fw-semibold">Keterangan</label>
          <textarea class="form-control mb-3" name="keterangan" rows="2"></textarea>
          <label class="form-label small fw-semibold">Status</label>
          <select class="form-select" name="aktif">
            <option value="1">Aktif</option>
            <option value="0">Nonaktif</option>
          </select>
        </div>
        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(function(){
  var rolePermissions = <?= json_encode($role_permissions, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>;
  
  $('#newRole').on('click', function(){
    $('#roleModal form')[0].reset();
    $('#roleModal [name=id]').val('');
  });

  $('.js-edit').on('click', function(){
    var r = $(this).data('role');
    var f = $('#roleModal form');
    f[0].reset();
    Object.keys(r).forEach(function(k){
      f.find('[name="'+k+'"]').val(r[k]);
    });
    bootstrap.Modal.getOrCreateInstance('#roleModal').show();
  });

  $('.js-perm').on('click', function(){
    var id = $(this).data('id');
    $('#permissionForm [name=role_id]').val(id);
    var selected = rolePermissions[id] || [];
    $('.perm').each(function(){
      $(this).prop('checked', selected.indexOf($(this).data('code')) >= 0);
    });
  });

  $('.js-module-all').on('click', function(){
    $('.perm[data-module="'+$(this).data('module')+'"]').prop('checked', true);
  });

  $('#checkAllModules').on('click', function(){
    $('.perm').prop('checked', true);
  });

  $('#permissionForm').on('submit', function(e){
    e.preventDefault();
    var id = $('#permissionForm [name=role_id]').val();
    if(!id) return App.toast('Pilih role terlebih dahulu.', 'warning');
    App.ajax({
      url: window.APP.baseUrl + 'role/matrix/' + id,
      data: $(this).serialize()
    });
  });
});
</script>

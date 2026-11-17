<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold">Master Pendamping</div>
  <div class="card-body">
    <table class="table table-sm">
      <thead><tr><th>Nama</th><th>Jabatan</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach (($rows ?? array()) as $r): ?>
          <tr>
            <td><?= html_escape($r['nama']) ?></td>
            <td><?= html_escape($r['jabatan'] ?: '-') ?></td>
            <td><?= !empty($r['is_active']) ? 'Aktif' : 'Nonaktif' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

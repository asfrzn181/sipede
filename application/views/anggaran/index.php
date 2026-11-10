<p class="text-muted">Pagu penerima pada tahun aktif. Sisa dihitung otomatis dari view database.</p>
<div class="card shadow-sm">
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 datatable">
        <thead class="table-light">
          <tr>
            <th>Penerima</th>
            <th>Tipe</th>
            <th>Pagu Anggaran</th>
            <th class="no-sort text-end" style="width: 280px;">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rows as $row): ?>
          <tr>
            <td class="fw-medium"><?= html_escape($row['nama']) ?></td>
            <td><span class="badge text-bg-secondary bg-opacity-10 text-secondary border"><?= html_escape($row['tipe']) ?></span></td>
            <td class="fw-semibold text-primary"><?= rupiah($row['pagu']) ?></td>
            <td class="text-end">
              <form class="d-flex gap-2 justify-content-end js-ajax-form" action="<?= site_url('anggaran/save') ?>">
                <input type="hidden" name="penerima_id" value="<?= encrypt_id($row['penerima_id']) ?>">
                <div class="input-group input-group-sm" style="max-width: 190px;">
                  <span class="input-group-text bg-light">Rp</span>
                  <input class="form-control form-control-sm input-rupiah" type="text" value="<?= number_format((float)$row['pagu'], 0, ',', '.') ?>" data-target-hidden="#pagu_<?= (int)$row['penerima_id'] ?>">
                  <input type="hidden" name="pagu" id="pagu_<?= (int)$row['penerima_id'] ?>" value="<?= (float)$row['pagu'] ?>">
                </div>
                <button class="btn btn-sm btn-primary d-flex align-items-center gap-1">
                  <i class="bi bi-check-lg"></i>
                  <span>Simpan</span>
                </button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

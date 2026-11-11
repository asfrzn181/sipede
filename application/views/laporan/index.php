<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
  <div>
    <h1 class="h5 fw-bold text-dark mb-1">Laporan per Komponen Biaya</h1>
    <p class="text-muted small mb-0">Melihat penyerapan realisasi berdasarkan jenis komponen biaya pada setiap kegiatan.</p>
  </div>
  <span class="badge text-bg-light border text-muted">Detail transaksi &amp; ekspor</span>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-header bg-white fw-bold">
    <i class="bi bi-funnel me-1 text-primary"></i> Filter Laporan
  </div>
  <div class="card-body">
    <form id="reportForm">
      <div class="row g-3">
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Tahun Anggaran</label>
          <select class="form-select" name="tahun_anggaran_id">
            <?php foreach($tahunList as $tahun): ?>
            <option value="<?= (int)$tahun['id'] ?>" <?= (int)$tahun['id'] === (int)$this->session->userdata('tahun_aktif_id') ? 'selected' : '' ?>><?= (int)$tahun['tahun'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Dari tanggal</label>
          <input class="form-control" type="date" name="tanggal_mulai">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Sampai tanggal</label>
          <input class="form-control" type="date" name="tanggal_selesai">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Penerima</label>
          <select class="form-select" name="penerima_id[]" multiple>
            <?php foreach($penerima as $row): ?>
            <option value="<?= (int)$row['id'] ?>"><?= html_escape($row['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label small fw-semibold">Status Kegiatan</label>
          <select class="form-select" name="status">
            <option value="">Semua Status</option>
            <option value="AKTIF">AKTIF</option>
            <option value="DRAFT">DRAFT</option>
            <option value="DIAJUKAN">DIAJUKAN</option>
            <option value="DITOLAK">DITOLAK</option>
            <option value="BATAL">BATAL</option>
          </select>
        </div>
        <div class="col-12 col-md-8">
          <label class="form-label small fw-semibold">Tempat / Kota</label>
          <input class="form-control" name="tempat" placeholder="Ketik kata kunci nama tempat...">
        </div>
        <div class="col-12 col-md-4 d-flex align-items-end gap-2">
          <button class="btn btn-primary flex-grow-1 d-flex align-items-center justify-content-center gap-1" type="submit">
            <i class="bi bi-search"></i>
            <span>Tampilkan</span>
          </button>
          <?php if($can_export): ?>
          <button class="btn btn-outline-success d-flex align-items-center gap-1" id="exportExcel" type="button">
            <i class="bi bi-file-earmark-excel"></i>
            <span>Excel</span>
          </button>
          <button class="btn btn-outline-danger d-flex align-items-center gap-1" id="exportPdf" type="button">
            <i class="bi bi-file-earmark-pdf"></i>
            <span>PDF</span>
          </button>
          <?php endif; ?>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-header bg-white fw-bold">
    <i class="bi bi-table me-1 text-primary"></i> Data Realisasi Laporan
  </div>
  <div class="card-body p-3">
    <div class="table-responsive">
      <table class="table table-hover table-sm align-middle mb-0 datatable" id="reportTable">
        <thead class="table-light">
          <tr>
            <th>No. Surat</th>
            <th>Tanggal</th>
            <th>Tema Kegiatan</th>
            <th>Peserta</th>
            <th>Jabatan</th>
            <th>Sumber Dana</th>
            <th>Jenis Biaya</th>
            <th class="text-end">Jumlah Anggaran</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

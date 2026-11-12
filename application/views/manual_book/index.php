<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
  <div>
    <h1 class="h5 fw-bold text-dark mb-1">Manual Book SIPEDE</h1>
    <p class="text-muted small mb-0">Panduan penggunaan Sistem Informasi Perjalanan Dinas dan pengelolaan anggaran Sekretariat DPRD Kabupaten Bangka.</p>
  </div>
  <button type="button" class="btn btn-sm btn-outline-primary d-print-none" onclick="window.print()">
    <i class="bi bi-printer me-1"></i>Cetak Panduan
  </button>
</div>

<div class="alert alert-primary d-flex gap-2 align-items-start shadow-sm">
  <i class="bi bi-info-circle-fill fs-5"></i>
  <div><strong>Tujuan aplikasi</strong><br><span class="small">SIPEDE membantu mencatat kegiatan, mengelola sumber pagu, memproses verifikasi, mencatat realisasi nota, dan menyajikan laporan secara terintegrasi.</span></div>
</div>

<div class="row g-4">
  <div class="col-12 col-lg-8">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold"><i class="bi bi-signpost-split text-primary me-1"></i>Alur Penggunaan Utama</div>
      <div class="card-body">
        <div class="d-flex gap-3 mb-4">
          <span class="badge rounded-pill text-bg-primary align-self-start">1</span>
          <div><strong>Pilih tahun anggaran</strong><p class="small text-muted mb-0">Pastikan kegiatan dan pos anggaran menggunakan tahun yang sama.</p></div>
        </div>
        <div class="d-flex gap-3 mb-4">
          <span class="badge rounded-pill text-bg-primary align-self-start">2</span>
          <div><strong>Siapkan pos anggaran</strong><p class="small text-muted mb-0">Atur pagu, jenis anggaran, pemegang pos, dan penugasan pendamping bila diperlukan.</p></div>
        </div>
        <div class="d-flex gap-3 mb-4">
          <span class="badge rounded-pill text-bg-primary align-self-start">3</span>
          <div><strong>Buat kegiatan</strong><p class="small text-muted mb-0">Pilih jenis kegiatan seperti Perjalanan Dinas, Rapat, atau Fasilitas. Untuk perjalanan dinas, pilih peserta dan pos anggaran.</p></div>
        </div>
        <div class="d-flex gap-3 mb-4">
          <span class="badge rounded-pill text-bg-primary align-self-start">4</span>
          <div><strong>Input rincian dan nota</strong><p class="small text-muted mb-0">Masukkan dana awal, komponen biaya, volume, tarif, dan keterangan anggaran.</p></div>
        </div>
        <div class="d-flex gap-3">
          <span class="badge rounded-pill text-bg-primary align-self-start">5</span>
          <div><strong>Ajukan, verifikasi, dan cetak</strong><p class="small text-muted mb-0">Setelah disetujui, realisasi mengurangi saldo pos. Cetak bukti untuk menyimpan dokumen kegiatan.</p></div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold"><i class="bi bi-wallet2 text-primary me-1"></i>Istilah Anggaran</div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <tbody>
              <tr><th class="ps-3" style="width: 180px;">Pagu Pos</th><td>Batas anggaran utama yang tersedia pada suatu pos anggaran.</td></tr>
              <tr><th class="ps-3">Alokasi Kegiatan</th><td>Bagian pagu yang direncanakan untuk kegiatan tertentu. Satu kegiatan dapat menggunakan beberapa pos.</td></tr>
              <tr><th class="ps-3">Dana Awal</th><td>Dana yang diberikan atau dialokasikan di muka kepada peserta kegiatan.</td></tr>
              <tr><th class="ps-3">Realisasi</th><td>Total biaya dari nota atau rincian yang sudah dicatat.</td></tr>
              <tr><th class="ps-3">Sisa</th><td>Dana awal dikurangi realisasi untuk peserta, atau alokasi dikurangi realisasi untuk kegiatan umum.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white fw-bold"><i class="bi bi-question-circle text-primary me-1"></i>Panduan Berdasarkan Jenis Kegiatan</div>
      <div class="card-body">
        <h2 class="h6 fw-bold">Perjalanan Dinas</h2>
        <p class="small text-muted">Pilih peserta, pilih pos anggaran, isi dana awal, lalu masukkan komponen biaya perjalanan. Untuk satu pos, alokasi kegiatan dapat terbentuk otomatis dari pilihan pos peserta.</p>
        <h2 class="h6 fw-bold">Rapat</h2>
        <p class="small text-muted">Pilih subjenis rapat, tambahkan satu atau beberapa alokasi pos, kemudian masukkan biaya umum seperti konsumsi, ATK, atau dokumentasi tanpa wajib menambahkan peserta perjalanan dinas.</p>
        <h2 class="h6 fw-bold">Fasilitas</h2>
        <p class="small text-muted mb-0">Gunakan alokasi pos untuk mencatat kebutuhan fasilitas dan masukkan biaya umum berdasarkan komponen yang tersedia.</p>
      </div>
    </div>
  </div>

  <div class="col-12 col-lg-4">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold"><i class="bi bi-grid text-primary me-1"></i>Fungsi Menu</div>
      <div class="list-group list-group-flush small">
        <div class="list-group-item"><strong>Dashboard Penyerapan</strong><br><span class="text-muted">Ringkasan pagu, realisasi, sisa, dan tren.</span></div>
        <div class="list-group-item"><strong>Kegiatan</strong><br><span class="text-muted">Input dan kelola kegiatan sampai bukti cetak.</span></div>
        <div class="list-group-item"><strong>Rekap &amp; Laporan</strong><br><span class="text-muted">Melihat realisasi per periode, anggota, pos, dan komponen.</span></div>
        <div class="list-group-item"><strong>Antrian Verifikasi</strong><br><span class="text-muted">Memeriksa, menyetujui, atau menolak kegiatan.</span></div>
        <div class="list-group-item"><strong>History Verifikasi &amp; Audit</strong><br><span class="text-muted">Menelusuri keputusan dan perubahan data.</span></div>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white fw-bold"><i class="bi bi-shield-check text-primary me-1"></i>Hak Akses Singkat</div>
      <div class="card-body small">
        <p><strong>Admin</strong> mengelola master, pos, pagu, pengguna, dan pengaturan.</p>
        <p><strong>Operator/Pendamping</strong> menginput kegiatan sesuai kewenangan.</p>
        <p><strong>PPTK/Verifikator</strong> memeriksa dan memproses pengajuan.</p>
        <p><strong>Pimpinan/Viewer</strong> memantau dashboard dan laporan.</p>
        <p class="text-muted mb-0">Menu yang tampil dapat berbeda sesuai role pengguna.</p>
      </div>
    </div>
  </div>
</div>

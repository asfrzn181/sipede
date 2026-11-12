<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-4">
  <div>
    <h1 class="h5 fw-bold text-dark mb-1">Manual Book SIPEDE</h1>
    <p class="text-muted small mb-0">Panduan operasional pengelolaan kegiatan, pagu anggaran, verifikasi, dan laporan Sekretariat DPRD Kabupaten Bangka.</p>
  </div>
  <button type="button" class="btn btn-sm btn-outline-primary d-print-none" onclick="window.print()">
    <i class="bi bi-printer me-1"></i>Cetak Panduan
  </button>
</div>

<div class="alert alert-primary d-flex gap-2 align-items-start shadow-sm">
  <i class="bi bi-info-circle-fill fs-5"></i>
  <div>
    <strong>Gambaran singkat</strong>
    <div class="small mt-1">SIPEDE menghubungkan data master, pos anggaran, pagu, kegiatan, rincian biaya, verifikasi, realisasi, dan laporan. Data laporan tidak diketik ulang; laporan dihitung dari transaksi yang sudah tersimpan pada modul kegiatan dan pos anggaran.</div>
  </div>
</div>

<div class="row g-4">
  <div class="col-12 col-xl-8">

    <div class="card shadow-sm mb-4" id="alur">
      <div class="card-header bg-white fw-bold"><i class="bi bi-signpost-split text-primary me-1"></i>Alur Sistem dari Awal sampai Laporan</div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light"><tr><th style="width:45px">No.</th><th>Tahap</th><th>Yang dilakukan</th><th>Data utama</th></tr></thead>
            <tbody>
              <tr><td>1</td><td><strong>Siapkan tahun</strong></td><td>Buat atau pilih tahun anggaran yang masih terbuka.</td><td><code>tahun_anggaran</code></td></tr>
              <tr><td>2</td><td><strong>Siapkan master</strong></td><td>Pastikan anggota DPRD, jenis anggaran, jenis kegiatan, dan jenis biaya tersedia.</td><td><code>penerima</code>, <code>jenis_anggaran</code>, <code>jenis_kegiatan</code>, <code>jenis_biaya</code></td></tr>
              <tr><td>3</td><td><strong>Buat pos</strong></td><td>Buat pos DPRD otomatis dan pos pendamping baku/custom untuk tahun tersebut.</td><td><code>pos_anggaran</code>, <code>penugasan_pos</code></td></tr>
              <tr><td>4</td><td><strong>Tentukan pagu</strong></td><td>Gunakan Terapkan Pagu Standar atau edit satu pos. Import anggota hanya membuat pagu Rp 0.</td><td><code>pagu</code>, <code>pagu_awal</code>, <code>riwayat_pagu_pos</code></td></tr>
              <tr><td>5</td><td><strong>Input kegiatan</strong></td><td>Isi surat tugas, jenis kegiatan, tahun, peserta, pos pembebanan, dana awal, dan rincian biaya.</td><td><code>kegiatan</code>, <code>kegiatan_peserta</code>, <code>kegiatan_anggaran</code>, <code>kegiatan_biaya</code></td></tr>
              <tr><td>6</td><td><strong>Ajukan</strong></td><td>Penginput mengirim kegiatan untuk diperiksa. Status berubah dari DRAFT menjadi DIAJUKAN.</td><td><code>kegiatan.status</code></td></tr>
              <tr><td>7</td><td><strong>Verifikasi</strong></td><td>PPTK memeriksa data, menyetujui atau menolak, serta memberi catatan bila perlu.</td><td><code>diverifikasi_oleh</code>, <code>diverifikasi_at</code>, <code>catatan_verifikasi</code></td></tr>
              <tr><td>8</td><td><strong>Realisasi &amp; cetak</strong></td><td>Biaya yang tersimpan menjadi realisasi. Bukti kegiatan dapat dicetak dan histori tetap tersimpan.</td><td><code>v_realisasi</code>, <code>v_sisa_pos</code>, <code>audit_log</code></td></tr>
              <tr><td>9</td><td><strong>Laporan</strong></td><td>Gunakan filter tahun, tanggal, anggota, pos, jenis biaya, dan status untuk membaca atau mengekspor data.</td><td>View laporan dan query agregasi</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="sumber-data">
      <div class="card-header bg-white fw-bold"><i class="bi bi-diagram-3 text-primary me-1"></i>Dari Mana Data Sistem Berasal?</div>
      <div class="card-body">
        <p class="small text-muted">Setiap tampilan mengambil data dari sumber berikut. Dengan memahami sumbernya, pengguna dapat mengetahui tempat memperbaiki data ketika laporan tidak sesuai.</p>
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Tampilan</th><th>Sumber data</th><th>Keterangan</th></tr></thead>
            <tbody>
              <tr><td>Daftar Anggota DPRD</td><td><code>penerima</code> dengan <code>tipe = DPRD</code></td><td>Berasal dari inject anggota atau input manual pada menu Anggota DPRD.</td></tr>
              <tr><td>Akun Pendamping</td><td><code>users</code> + <code>roles</code> + <code>penerima</code></td><td>User ber-role PENDAMPING memiliki identitas penerima sendiri.</td></tr>
              <tr><td>Tahun Anggaran</td><td><code>tahun_anggaran</code></td><td>Menentukan tahun yang boleh dipakai pada pos dan kegiatan.</td></tr>
              <tr><td>Jenis Anggaran</td><td><code>jenis_anggaran</code></td><td>Kelompok pagu, misalnya Fasilitas DPRD, Rapat, Perjalanan Dinas, atau kategori baru.</td></tr>
              <tr><td>Jenis Kegiatan</td><td><code>jenis_kegiatan</code></td><td>Klasifikasi kegiatan, misalnya Perjalanan Dinas, Rapat, Fasilitas, dan Lainnya.</td></tr>
              <tr><td>Komponen biaya</td><td><code>jenis_biaya</code></td><td>Komponen yang muncul pada rincian kegiatan, mengikuti atribut Tampil di Kegiatan.</td></tr>
              <tr><td>Pos dan pagu</td><td><code>pos_anggaran</code></td><td>Unit pemilik pagu per tahun; pos DPRD melekat ke anggota, pos pendamping melekat ke jabatan.</td></tr>
              <tr><td>Pemegang pos pendamping</td><td><code>penugasan_pos</code></td><td>Menghubungkan user/pendamping dengan pos jabatan tertentu.</td></tr>
              <tr><td>Saldo dan realisasi</td><td><code>v_sisa_pos</code>, <code>v_realisasi</code>, <code>kegiatan_biaya</code></td><td>Saldo dihitung dari pagu dikurangi biaya kegiatan yang sudah berstatus AKTIF.</td></tr>
              <tr><td>Audit</td><td><code>audit_log</code></td><td>Mencatat siapa melakukan perubahan, objeknya, waktu, data lama, dan data baru.</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="menu-master">
      <div class="card-header bg-white fw-bold"><i class="bi bi-database-gear text-primary me-1"></i>Penjelasan Menu Master Data</div>
      <div class="card-body">
        <div class="accordion" id="masterAccordion">
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#masterAnggota">1. Anggota DPRD</button></h2>
            <div id="masterAnggota" class="accordion-collapse collapse show" data-bs-parent="#masterAccordion"><div class="accordion-body small">
              <p>Menu ini berisi <strong>penerima tipe DPRD</strong>. Pendamping tidak dikelola dari halaman ini karena identitas pendamping dibuat dari akun user ber-role PENDAMPING.</p>
              <ol><li>Tambah atau edit nama anggota.</li><li>Isi jabatan serta periode jabatan bila tersedia.</li><li>Pastikan anggota aktif sebelum membuat pos otomatis.</li></ol>
              <div class="alert alert-light border mb-0">Sumber data: tabel <code>penerima</code>. Import anggota DPRD juga menulis ke tabel ini.</div>
            </div></div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#masterTahun">2. Tahun Anggaran</button></h2>
            <div id="masterTahun" class="accordion-collapse collapse" data-bs-parent="#masterAccordion"><div class="accordion-body small">
              <p>Tahun adalah induk seluruh data anggaran. Satu pos dan satu kegiatan harus mengacu pada tahun yang sama.</p>
              <ul><li>Tahun <strong>terbuka</strong> dapat digunakan untuk input dan perubahan.</li><li>Tahun <strong>terkunci</strong> tidak boleh dipakai untuk kegiatan baru, edit pagu, atau perubahan rincian.</li><li>Pilih tahun pada halaman detail untuk membuka Pos Anggaran tahun tersebut.</li></ul>
              <div class="alert alert-light border mb-0">Sumber data: <code>tahun_anggaran</code>. Penguncian adalah kontrol periode, bukan penghapusan data.</div>
            </div></div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#masterJenisAnggaran">3. Jenis Anggaran</button></h2>
            <div id="masterJenisAnggaran" class="accordion-collapse collapse" data-bs-parent="#masterAccordion"><div class="accordion-body small">
              <p>Jenis Anggaran adalah kelompok peruntukan pagu, bukan komponen nota. Contoh bawaan: <strong>Fasilitas DPRD</strong>, <strong>Rapat</strong>, <strong>Perjalanan Dinas</strong>, <strong>Honorarium / Notulen</strong>, dan <strong>Lainnya</strong>.</p>
              <ol><li>Tambah kode dan nama jenis anggaran.</li><li>Isi keterangan agar pengguna memahami peruntukannya.</li><li>Setelah itu buka Pos Anggaran dan pilih jenis tersebut pada tombol tag.</li></ol>
              <div class="alert alert-light border mb-0">Sumber data: <code>jenis_anggaran</code>. Menambah kategori di sini tidak otomatis membuat pagu; pagu tetap melekat pada Pos Anggaran.</div>
            </div></div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#masterJenisKegiatan">4. Jenis Kegiatan</button></h2>
            <div id="masterJenisKegiatan" class="accordion-collapse collapse" data-bs-parent="#masterAccordion"><div class="accordion-body small">
              <p>Jenis kegiatan menjelaskan bentuk aktivitas yang dilakukan. Rapat dapat memiliki subjenis seperti Paripurna, Komisi, Banggar, Bamus, dan Rapat Khusus.</p>
              <p class="mb-0">Jenis kegiatan menentukan cara pengguna mengisi kegiatan, sedangkan Jenis Anggaran menentukan sumber pagu yang dibebani. Keduanya boleh memiliki nama serupa tetapi fungsinya berbeda.</p>
            </div></div>
          </div>
          <div class="accordion-item">
            <h2 class="accordion-header"><button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#masterBiaya">5. Jenis Biaya</button></h2>
            <div id="masterBiaya" class="accordion-collapse collapse" data-bs-parent="#masterAccordion"><div class="accordion-body small">
              <p>Jenis Biaya adalah item yang muncul ketika rincian biaya kegiatan diisi, misalnya uang harian, penginapan, tiket, sewa kendaraan, konsumsi, ATK, atau dokumentasi.</p>
              <ul><li><strong>Tampil di Kegiatan</strong> aktif: muncul sebagai pilihan input.</li><li>Tidak aktif: tetap menjadi referensi lama tetapi tidak ditawarkan untuk input baru.</li><li>Keterangan tambahan membantu menjelaskan standar atau syarat pembayarannya.</li></ul>
              <div class="alert alert-light border mb-0">Sumber data: <code>jenis_biaya</code>. Nilai transaksi tetap disimpan di <code>kegiatan_biaya</code>.</div>
            </div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="menu-anggaran">
      <div class="card-header bg-white fw-bold"><i class="bi bi-wallet2 text-primary me-1"></i>Pos Anggaran, Pagu, dan Pendamping</div>
      <div class="card-body small">
        <h2 class="h6 fw-bold">Cara membuka Pos Anggaran</h2>
        <p>Buka menu <strong>Tahun Anggaran</strong>, pilih satu tahun, lalu masuk ke detail <strong>Pos Anggaran</strong>. Pos dibaca berdasarkan tahun yang dipilih, sehingga pos 2024 tidak tercampur dengan pos 2025 atau 2026.</p>
        <h2 class="h6 fw-bold">Pos DPRD</h2>
        <p>Pos DPRD dibuat satu untuk setiap anggota aktif. Pagu melekat pada anggota tersebut dan secara bawaan jenisnya adalah Fasilitas DPRD. Jenisnya tetap dapat diganti melalui tombol tag.</p>
        <h2 class="h6 fw-bold">Pos Pendamping</h2>
        <p>Pos pendamping melekat pada jabatan/kelompok pos, bukan langsung pada satu orang. Satu pos dapat memiliki beberapa pemegang melalui penugasan.</p>
        <ol><li>Gunakan pos baku yang tersedia atau klik <strong>Tambah Pos Pendamping</strong> bila ada jabatan baru.</li><li>Pilih user pendamping melalui tombol <strong>Tugaskan</strong>.</li><li>Jika pemegang berubah, tutup penugasan lama dan buat penugasan baru agar histori tetap ada.</li></ol>
        <h2 class="h6 fw-bold">Pagu awal dan revisi</h2>
        <table class="table table-sm table-bordered align-middle">
          <thead class="table-light"><tr><th>Kondisi</th><th>Perlakuan sistem</th></tr></thead>
          <tbody>
            <tr><td>Import anggota atau pos baru</td><td>Pagu dan pagu awal = Rp 0.</td></tr>
            <tr><td>Terapkan Pagu Standar pada pos Rp 0</td><td>Nilai menjadi pagu dan sekaligus pagu awal pertama.</td></tr>
            <tr><td>Edit pos yang masih Rp 0</td><td>Wajib centang “Jadikan sebagai pagu awal / pertama”.</td></tr>
            <tr><td>Pagu awal sudah ada</td><td>Perubahan dicatat sebagai revisi dan wajib memakai alasan.</td></tr>
            <tr><td>Realisasi sudah ada</td><td>Pagu baru tidak boleh lebih kecil dari realisasi.</td></tr>
            <tr><td>Timpa Semua pada standar</td><td>Mengubah pagu berjalan, tetapi tidak menghapus pagu awal dan histori.</td></tr>
          </tbody>
        </table>
        <div class="alert alert-warning mb-0"><strong>Penting:</strong> Pagu pos adalah batas anggaran. Dana awal peserta adalah uang yang dialokasikan di tingkat kegiatan. Realisasi dihitung dari rincian biaya/nota, bukan dari angka pagu awal.</div>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="menu-kegiatan">
      <div class="card-header bg-white fw-bold"><i class="bi bi-calendar2-event text-primary me-1"></i>Menu Kegiatan dan Rincian</div>
      <div class="card-body small">
        <h2 class="h6 fw-bold">A. Membuat header kegiatan</h2>
        <ol><li>Pilih tahun anggaran yang masih terbuka.</li><li>Isi nomor surat tugas, tanggal, periode kegiatan, tempat, dan tema.</li><li>Pilih Jenis Kegiatan: Perjalanan Dinas, Rapat, Fasilitas, atau Lainnya.</li><li>Simpan untuk masuk ke halaman rincian.</li></ol>
        <h2 class="h6 fw-bold">B. Menambahkan peserta atau pendamping</h2>
        <p>Pilih penerima dari master DPRD atau identitas pendamping. Sistem menyediakan pos bawaan sesuai identitas, tetapi pengguna dapat memilih pos anggaran lain yang masih berada pada tahun kegiatan.</p>
        <ul><li><strong>Dana awal</strong> wajib diisi sebagai alokasi/panjar peserta.</li><li><strong>Keterangan anggaran</strong> wajib diisi untuk setiap peserta, baik memakai anggaran sendiri maupun pos lain.</li><li>Jika satu orang memakai pos lain, pilih pos tersebut secara eksplisit. Sistem menyimpan <code>pos_anggaran_id</code> dan keterangannya.</li><li>Peserta yang sama tidak boleh ditambahkan dua kali dalam satu kegiatan.</li></ul>
        <h2 class="h6 fw-bold">C. Menambahkan alokasi kegiatan umum</h2>
        <p>Untuk rapat, fasilitas, atau kegiatan yang tidak berbasis peserta perjalanan, tambahkan alokasi pos pada bagian Alokasi Anggaran. Satu kegiatan boleh memakai beberapa pos dengan nilai alokasi berbeda.</p>
        <h2 class="h6 fw-bold">D. Mengisi rincian biaya</h2>
        <p>Pilih Jenis Biaya, isi volume dan tarif. Total baris dihitung sebagai <strong>volume × tarif</strong>. Untuk peserta, biaya dikaitkan ke peserta; untuk kegiatan umum, biaya dikaitkan ke alokasi kegiatan.</p>
        <h2 class="h6 fw-bold">E. Status kegiatan</h2>
        <table class="table table-sm table-bordered align-middle mb-0">
          <thead class="table-light"><tr><th>Status</th><th>Arti</th><th>Dampak ke laporan</th></tr></thead>
          <tbody>
            <tr><td><span class="badge text-bg-secondary">DRAFT</span></td><td>Masih disusun atau diedit.</td><td>Belum menjadi realisasi aktif.</td></tr>
            <tr><td><span class="badge text-bg-warning">DIAJUKAN</span></td><td>Menunggu pemeriksaan PPTK.</td><td>Belum menjadi realisasi aktif.</td></tr>
            <tr><td><span class="badge text-bg-success">AKTIF</span></td><td>Disetujui/verifikasi berhasil.</td><td>Biaya masuk perhitungan realisasi.</td></tr>
            <tr><td><span class="badge text-bg-danger">DITOLAK</span></td><td>Perlu diperbaiki atau tidak disetujui.</td><td>Tidak dihitung sebagai realisasi aktif.</td></tr>
            <tr><td><span class="badge text-bg-dark">BATAL</span></td><td>Kegiatan dibatalkan.</td><td>Tidak dihitung sebagai realisasi aktif.</td></tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="menu-laporan">
      <div class="card-header bg-white fw-bold"><i class="bi bi-bar-chart-line text-primary me-1"></i>Menu Laporan dan Analitik</div>
      <div class="card-body small">
        <div class="table-responsive">
          <table class="table table-bordered table-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Menu</th><th>Fungsi</th><th>Sumber/perhitungan</th></tr></thead>
            <tbody>
              <tr><td><strong>Dashboard Penyerapan</strong></td><td>Ringkasan pagu, realisasi, sisa, jumlah kegiatan, dan tren triwulan.</td><td>Agregasi pos dan kegiatan berdasarkan tahun aktif.</td></tr>
              <tr><td><strong>Rekap Realisasi Anggaran</strong></td><td>Rekap transaksi dengan filter tahun, status, periode, anggota, pos, dan komponen.</td><td><code>kegiatan</code> + <code>kegiatan_peserta</code>/<code>kegiatan_anggaran</code> + <code>kegiatan_biaya</code>.</td></tr>
              <tr><td><strong>Per Komponen Biaya</strong></td><td>Menjawab biaya terbesar digunakan untuk apa: tiket, uang harian, penginapan, konsumsi, dan sebagainya.</td><td>Pengelompokan berdasarkan <code>jenis_biaya_id</code>.</td></tr>
              <tr><td><strong>Per Anggota DPRD</strong></td><td>Melihat pagu, realisasi, sisa, dan jumlah kegiatan per anggota.</td><td>Pos DPRD dan peserta kegiatan yang terhubung ke <code>penerima_id</code>.</td></tr>
              <tr><td><strong>Per Pos Anggaran</strong></td><td>Membandingkan pagu, realisasi, sisa, tipe pos, dan jenis anggaran.</td><td><code>pos_anggaran</code> dan view <code>v_sisa_pos</code>.</td></tr>
              <tr><td><strong>History Verifikasi &amp; Audit</strong></td><td>Menelusuri keputusan verifikasi, perubahan pagu, user, waktu, dan alasan.</td><td>Status kegiatan, kolom verifikasi, <code>audit_log</code>, dan <code>riwayat_pagu_pos</code>.</td></tr>
            </tbody>
          </table>
        </div>
        <div class="alert alert-light border mt-3 mb-0">Jika angka laporan belum berubah, periksa status kegiatan. Kegiatan DRAFT atau DIAJUKAN belum dianggap realisasi aktif.</div>
      </div>
    </div>

    <div class="card shadow-sm mb-4" id="menu-verifikasi">
      <div class="card-header bg-white fw-bold"><i class="bi bi-check2-square text-primary me-1"></i>Alur Verifikasi dan Cetak</div>
      <div class="card-body small">
        <ol><li>Penginput menyelesaikan header, peserta/alokasi, dana awal, dan rincian biaya.</li><li>Penginput menekan <strong>Ajukan</strong>; status menjadi <strong>DIAJUKAN</strong>.</li><li>PPTK membuka <strong>Antrian Verifikasi</strong> dan memeriksa surat, peserta, pos, dana awal, serta rincian biaya.</li><li>PPTK menyetujui atau menolak. Penolakan harus diikuti catatan perbaikan.</li><li>Jika disetujui, status menjadi <strong>AKTIF</strong> dan realisasi masuk ke saldo pos.</li><li>Dokumen dapat dicetak dari kegiatan. Bukti yang sudah dicetak tetap tersedia pada history.</li></ol>
        <div class="alert alert-info mb-0"><strong>Aturan saldo:</strong> pada mode BLOKIR, sistem menolak biaya jika melebihi sisa pos. Pada mode PERINGATAN, sistem memberi peringatan namun keputusan tetap mengikuti kewenangan pengguna.</div>
      </div>
    </div>

    <div class="card shadow-sm" id="menu-pengaturan">
      <div class="card-header bg-white fw-bold"><i class="bi bi-gear text-primary me-1"></i>Pengaturan dan Administrasi</div>
      <div class="card-body small">
        <dl class="row mb-0">
          <dt class="col-sm-4">Pengguna</dt><dd class="col-sm-8">Membuat akun, memilih role, menghubungkan akun pendamping, mengaktifkan/nonaktifkan user, dan mengatur password.</dd>
          <dt class="col-sm-4">Role &amp; Izin</dt><dd class="col-sm-8">Menentukan modul apa yang dapat dilihat atau diubah oleh role tertentu.</dd>
          <dt class="col-sm-4">KOP &amp; Penandatangan</dt><dd class="col-sm-8">Mengatur identitas KOP, alamat, logo aplikasi, PPTK, dan Bendahara Pengeluaran.</dd>
          <dt class="col-sm-4">Audit Log</dt><dd class="col-sm-8">Menampilkan jejak perubahan data penting, termasuk user dan waktu perubahan.</dd>
          <dt class="col-sm-4">Profil Pengguna</dt><dd class="col-sm-8">Mengubah password akun yang sedang login.</dd>
          <dt class="col-sm-4">Manual Book</dt><dd class="col-sm-8">Membaca atau mencetak panduan ini.</dd>
        </dl>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-4">
    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold"><i class="bi bi-list-check text-primary me-1"></i>Peta Menu</div>
      <div class="list-group list-group-flush small">
        <a class="list-group-item list-group-item-action" href="#alur">Alur Sistem</a>
        <a class="list-group-item list-group-item-action" href="#sumber-data">Sumber Data</a>
        <a class="list-group-item list-group-item-action" href="#menu-master">Master Data</a>
        <a class="list-group-item list-group-item-action" href="#menu-anggaran">Pos &amp; Pagu</a>
        <a class="list-group-item list-group-item-action" href="#menu-kegiatan">Kegiatan</a>
        <a class="list-group-item list-group-item-action" href="#menu-verifikasi">Verifikasi &amp; Cetak</a>
        <a class="list-group-item list-group-item-action" href="#menu-laporan">Laporan &amp; Analitik</a>
        <a class="list-group-item list-group-item-action" href="#menu-pengaturan">Pengaturan</a>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold"><i class="bi bi-shield-check text-primary me-1"></i>Hak Akses Ringkas</div>
      <div class="card-body small">
        <p><strong>Super Admin/Admin</strong><br><span class="text-muted">Master, tahun, jenis anggaran/biaya, pos, pagu, pengguna, role, pengaturan, dan audit.</span></p>
        <p><strong>Operator</strong><br><span class="text-muted">Input dan ajukan kegiatan sesuai kewenangan.</span></p>
        <p><strong>Pendamping</strong><br><span class="text-muted">Input kegiatan sendiri, rincian biaya, pengajuan, dan cetak dokumen.</span></p>
        <p><strong>PPTK</strong><br><span class="text-muted">Memeriksa dan memverifikasi kegiatan serta memantau laporan.</span></p>
        <p class="mb-0"><strong>Pimpinan/Viewer</strong><br><span class="text-muted">Memantau dashboard dan laporan sesuai izin.</span></p>
      </div>
    </div>

    <div class="card shadow-sm mb-4">
      <div class="card-header bg-white fw-bold"><i class="bi bi-calculator text-primary me-1"></i>Rumus Penting</div>
      <div class="card-body small">
        <p class="mb-2"><strong>Total rincian</strong><br><code>volume × tarif</code></p>
        <p class="mb-2"><strong>Sisa pos</strong><br><code>pagu berjalan − realisasi aktif</code></p>
        <p class="mb-2"><strong>Selisih dana peserta</strong><br><code>dana awal − total rincian peserta</code></p>
        <p class="mb-0"><strong>Realisasi laporan</strong><br>Hanya biaya dari kegiatan yang telah berstatus <code>AKTIF</code>.</p>
      </div>
    </div>

    <div class="card shadow-sm">
      <div class="card-header bg-white fw-bold"><i class="bi bi-wrench-adjustable text-primary me-1"></i>Masalah Umum</div>
      <div class="card-body small">
        <p><strong>Pagu tidak muncul?</strong><br>Pastikan pos sudah dibuat untuk tahun yang dipilih dan pagu sudah diterapkan.</p>
        <p><strong>Jenis anggaran tidak ada?</strong><br>Tambahkan dari menu Jenis Anggaran, lalu pilih pada Pos Anggaran.</p>
        <p><strong>Pendamping tidak dapat membuat kegiatan?</strong><br>Pastikan user ber-role PENDAMPING memiliki penerima_id dan penugasan pos aktif.</p>
        <p><strong>Realisasi belum bertambah?</strong><br>Periksa apakah kegiatan sudah diverifikasi menjadi AKTIF.</p>
        <p class="mb-0"><strong>Tahun tidak bisa diedit?</strong><br>Periksa apakah tahun sudah dikunci.</p>
      </div>
    </div>
  </div>
</div>

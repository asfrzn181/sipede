# Skenario UAT Pengguna SIPEDE

Dokumen ini digunakan untuk pengujian manual oleh admin, operator, PPTK,
pimpinan, anggota DPRD, pendamping, dan viewer.

## Data uji

- Tahun anggaran: 2024, 2025, dan 2026.
- Satu anggota DPRD: `JUMADI`.
- Satu pendamping: `TEJO`.
- Pagu DPRD: Rp500.000.000.
- Pagu pendamping: gunakan pagu standar yang ditetapkan admin.
- Contoh surat tugas: `090/ST/DPRD/2026`.
- Contoh kegiatan: `Kunjungan Kerja Komisi III`.

## Status hasil

Isi kolom hasil dengan `LULUS`, `GAGAL`, atau `BLOKIR` dan tambahkan catatan
untuk setiap skenario.

| ID | Skenario | Langkah uji | Hasil yang diharapkan | Hasil |
|---|---|---|---|---|
| AUTH-01 | Login valid | Masuk dengan username dan password aktif | Pengguna masuk ke dashboard sesuai role | |
| AUTH-02 | Login invalid | Masukkan password salah | Login ditolak dan tidak ada sesi terbentuk | |
| AUTH-03 | Logout | Klik Logout lalu buka halaman sebelumnya | Sesi berakhir dan halaman meminta login kembali | |
| AUTH-04 | Ganti password | Ubah password sendiri, logout, lalu login dengan password baru | Password baru dapat digunakan | |
| YEAR-01 | Daftar tahun | Buka menu Tahun Anggaran | Tahun 2024–2026 tampil sesuai data | |
| YEAR-02 | Pilih tahun kegiatan | Buka Kegiatan lalu ubah filter tahun | Daftar kegiatan berubah sesuai tahun yang dipilih | |
| YEAR-03 | Tahun pada rincian | Buka rincian kegiatan | Tahun anggaran terlihat pada header dan detail surat tugas | |
| YEAR-04 | Tahun terkunci | Kunci satu tahun lalu coba membuat/edit kegiatan pada tahun tersebut | Sistem menolak penyimpanan dan menampilkan pesan tahun terkunci | |
| MASTER-01 | Master pendamping / ASN | Buka menu Master Pendamping / ASN | Hanya data pendamping/ASN yang tampil dan jabatan dapat dikelola | |
| POS-01 | Pos inject | Buka Pos Anggaran pada 2024, 2025, dan 2026 | Pos inject tampil sesuai tahun | |
| POS-02 | Cek pos ganda | Cari nama seperti Ajudan Wakil Ketua I | Tidak ada pos template lama yang tidak digunakan; setiap pos memiliki kode yang jelas | |
| POS-03 | Pagu DPRD | Buka pos anggota DPRD | Pagu awal dan sisa tampil Rp500.000.000 sebelum realisasi | |
| POS-04 | Pagu pendamping | Terapkan pagu standar pendamping | Pagu pos pendamping terisi sesuai nilai standar | |
| POS-05 | Penerima pos | Pastikan TEJO terpasang pada pos Ajudan Wakil Ketua I | Nama, jabatan, dan pos sesuai data inject | |
| KGT-01 | Buat surat tugas | Operator memilih tahun 2026, mengisi nomor, tanggal, tempat, dan tema | Header surat tugas tersimpan dan diarahkan ke rincian | |
| KGT-02 | Peserta DPRD | Tambahkan JUMADI ke kegiatan | Peserta tersimpan dan otomatis memakai pos DPRD miliknya | |
| KGT-03 | Peserta pendamping | Tambahkan TEJO ke kegiatan | Pos pendamping TEJO terpilih dan saldo pos tampil | |
| KGT-03A | Pilih anggaran peserta | Pilih ASN lalu pilih pos anggaran DPRD | Pos DPRD yang dipilih tampil sebagai sumber anggaran peserta | |
| KGT-03B | Default anggaran DPRD | Pilih JUMADI sebagai peserta | Sistem otomatis memilih pos DPRD milik JUMADI, tetapi pos lain dapat dipilih dengan alasan | |
| KGT-04 | Rincian biaya | Tambahkan jenis biaya, volume, dan tarif | Subtotal dan total kegiatan dihitung benar | |
| KGT-05 | Pagu tidak cukup | Masukkan rincian melebihi sisa pagu | Sistem menolak penyimpanan atau pengajuan | |
| KGT-06 | Peserta ganda | Tambahkan JUMADI dua kali pada satu kegiatan | Sistem menolak peserta yang sama | |
| KGT-07 | Keterangan anggaran | Pilih pos bawaan peserta lalu kosongkan keterangan | Sistem meminta keterangan anggaran | |
| KGT-08 | Keterangan anggaran valid | Isi dana awal dan keterangan anggaran | Data tersimpan untuk anggaran sendiri maupun anggaran lain | |
| KGT-08A | Dana awal dan nota | Isi dana awal Rp100.000.000 lalu input nota Rp5.000.000 | Realisasi Rp5.000.000 dan selisih Rp95.000.000 | |
| KGT-09 | Tahun kegiatan | Simpan kegiatan 2026 lalu buka rincian | Rincian menampilkan Tahun Anggaran 2026 | |
| KGT-10 | Ajukan kegiatan | Operator melengkapi peserta lalu klik Ajukan Verifikasi | Status berubah dari DRAFT menjadi DIAJUKAN | |
| KGT-11 | Saldo sebelum verifikasi | Simpan DRAFT dan ajukan kegiatan | Pagu/realisasi belum berkurang sebelum kegiatan disetujui | |
| KGT-12 | Jenis kegiatan rapat | Buat kegiatan dengan jenis Rapat dan subjenis Rapat Komisi I | Jenis kegiatan tampil pada rincian dan dapat dibedakan dari perjalanan dinas | |
| KGT-13 | Alokasi multi-pos | Pada satu kegiatan tambahkan Pos Rapat Rp100.000.000 dan Pos Fasilitas Rp30.000.000 | Dua pos tampil sebagai alokasi kegiatan dengan total pagu Rp130.000.000 | |
| KGT-14 | Biaya umum tanpa peserta | Pada alokasi rapat tambahkan biaya konsumsi tanpa menambahkan peserta | Biaya tersimpan, total kegiatan dan realisasi pos menghitung biaya tersebut | |
| KGT-15 | Batas pagu alokasi | Buat alokasi yang totalnya melebihi pagu pos | Sistem menolak dan menampilkan pagu yang tersedia | |
| KGT-16 | Sisa alokasi kegiatan | Alokasi Rp100.000.000 lalu input biaya Rp5.000.000 | Sisa alokasi tampil Rp95.000.000 | |
| VER-01 | Verifikasi setuju | PPTK membuka kegiatan DIAJUKAN lalu menyetujui | Status berubah menjadi aktif/diverifikasi | |
| VER-01A | Saldo setelah verifikasi | Setelah VER-01, buka Pos Anggaran | Realisasi bertambah dan sisa pagu berkurang sesuai rincian biaya | |
| VER-02 | Verifikasi tolak | PPTK menolak tanpa catatan | Sistem menolak aksi dan meminta catatan | |
| VER-03 | Verifikasi tolak valid | PPTK menolak dengan catatan | Status menjadi DITOLAK dan catatan tampil | |
| VER-04 | Pembatalan | PPTK membatalkan kegiatan aktif dengan alasan | Status menjadi BATAL dan saldo kembali | |
| LAP-01 | Rekap tahun | Buka Rekapitulasi dan pilih tahun 2026 | Hanya data tahun 2026 yang tampil | |
| LAP-02 | Filter penerima | Filter JUMADI atau TEJO | Hanya data penerima yang dipilih tampil | |
| LAP-03 | Export PDF | Export bukti/rekap ke PDF | File PDF dapat dibuka dan nominal sesuai | |
| LAP-04 | Export Excel | Export rekap ke Excel | File Excel dapat dibuka dan data sesuai filter | |
| ROLE-01 | Hak operator | Login sebagai Operator lalu buka menu verifikasi | Operator tidak dapat menyetujui/menolak kegiatan | |
| ROLE-02 | Hak PPTK | Login sebagai PPTK | PPTK dapat melihat dan memverifikasi seluruh kegiatan | |
| ROLE-03 | Hak DPRD | Login sebagai anggota DPRD | Hanya kegiatan, pagu, dan laporan milik sendiri yang tampil | |
| ROLE-04 | Hak pendamping | Login sebagai pendamping | Hanya data yang ditugaskan kepada pendamping yang tampil | |
| ROLE-05 | Hak viewer | Login sebagai Viewer | Viewer hanya dapat melihat laporan, tanpa input/edit data | |
| ADM-01 | Reset password user | Superadmin membuka menu User dan reset password user uji | Password sementara dibuat dan user dapat login | |
| ADM-02 | Nonaktifkan user | Superadmin menonaktifkan user uji | User tersebut tidak dapat login | |
| ADM-03 | Audit aktivitas | Lakukan perubahan pagu, kegiatan, dan verifikasi | Aktivitas tercatat pada Audit | |
| BIAYA-01 | Tampil di input kegiatan | Pada Jenis Biaya, aktifkan/nonaktifkan "Tampil di Kegiatan" | Jenis biaya hanya muncul jika atribut aktif | |
| BIAYA-02 | Keterangan jenis biaya | Isi keterangan tambahan pada Jenis Biaya lalu buka rincian kegiatan | Keterangan tampil di bawah komponen biaya | |

## Skenario regresi setelah perbaikan pos pendamping

1. Jalankan `tests/simulate_pos_pendamping.sql` pada database uji.
2. Pastikan pos inject berjumlah 8 kelompok jabatan per tahun 2024–2026.
3. Pastikan pos template lama yang tidak digunakan berjumlah 0 setelah cleanup.
4. Buka halaman rincian kegiatan beberapa kali.
5. Pastikan jumlah pos tidak bertambah setelah refresh.
6. Tambahkan pendamping ke kegiatan dan pastikan pos yang digunakan adalah pos inject.
7. Pastikan saldo pagu pendamping berkurang hanya satu kali sesuai realisasi.

## Kriteria penerimaan

- Tidak ada data pos ganda yang tidak diperlukan.
- Tahun anggaran pada kegiatan selalu terlihat pada rincian.
- Pagu, realisasi, dan sisa konsisten.
- Satu kegiatan dapat memiliki beberapa alokasi pos tanpa menggandakan realisasi.
- Kegiatan rapat/fasilitas dapat mencatat biaya umum tanpa peserta perjalanan dinas.
- Pengguna hanya dapat menjalankan fungsi sesuai role.
- Kegiatan tidak dapat diajukan jika data wajib atau rincian biaya belum lengkap.
- Export menampilkan tahun dan data yang sama dengan layar.

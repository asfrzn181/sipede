# Checklist Uji RBAC dan Workflow

1. Login sebagai `viewer`, buka `/kegiatan`, `/user`, `/role`, endpoint AJAX, dan export; seluruh endpoint harus 403.
2. Login sebagai `pendamping<ID>`, ubah parameter `penerima_id` pada `/laporan/data` atau ID kegiatan; respons tetap hanya memuat penerima sendiri atau 403/404.
3. Pendamping membuat DRAFT → Ajukan → PPTK menyetujui → pastikan `v_sisa_anggaran.sisa` berkurang → PPTK membatalkan → saldo kembali.
4. PPTK menolak tanpa `catatan_verifikasi`; server harus mengembalikan 422.
5. Pengaju mencoba menyetujui kegiatannya sendiri; server harus mengembalikan 403.
6. Hapus role system, hapus role yang masih dipakai, ubah role sendiri, nonaktifkan diri sendiri, dan membuat pendamping tanpa `penerima_id`; semua harus ditolak.
7. Uji UI pada lebar 360px, 768px, dan 1280px; sidebar harus menjadi offcanvas pada layar kecil.

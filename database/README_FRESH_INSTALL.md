# Instalasi Fresh SIPEDE

Paket fresh terdiri dari tiga file SQL utama dan dua file inject lokal:

1. `schema.sql` — reset dan membuat ulang seluruh tabel, foreign key, dan view aplikasi.
2. `seed.sql` — role, permission, master referensi, pengaturan dokumen, tahun 2026, serta akun administrator.
3. `inject_anggota_dprd_2024_2029.sql` — 34 anggota DPRD, periode 2024–2029, tahun anggaran 2024–2026, dan pos DPRD dengan pagu awal `0`.
4. `inject_akun_pendamping.sql` — 113 akun pendamping tanpa gelar, sekaligus membuat identitas penerima dan tautan akun.

## Urutan menjalankan

Backup database lama terlebih dahulu. Di phpMyAdmin/cPanel, pilih database aplikasi kemudian import berurutan:

```text
database/schema.sql
database/seed.sql
database/inject_anggota_dprd_2024_2029.sql
database/inject_akun_pendamping.sql
```

`schema.sql` sudah memiliki `DROP VIEW`, `DROP TABLE`, dan `FOREIGN_KEY_CHECKS = 0`, sehingga data/tabel aplikasi lama akan dibersihkan sebelum dibuat ulang. File ini tidak menjalankan `DROP DATABASE`, agar tidak berisiko menghapus database lain pada akun cPanel.

## Akun awal

- Superadmin: username `superadmin`, password `setwan2026sipede`
- Admin: username `admin`, password `admin123`
- Seluruh akun pendamping: password awal `setwan-bangka-2026`

Password akun pendamping ditandai wajib diganti saat login pertama.

## Validasi hasil inject

Setelah import, hasil yang diharapkan:

```sql
SELECT COUNT(*) AS total_dprd
FROM penerima
WHERE tipe = 'DPRD' AND periode_mulai = 2024 AND periode_akhir = 2029;
-- 34

SELECT COUNT(*) AS total_akun_pendamping
FROM users u
JOIN roles r ON r.id = u.role_id
WHERE r.kode = 'PENDAMPING' AND u.is_active = 1;
-- 113
```

Semua file dengan nama `inject` atau `anggota` di folder `database/` diabaikan oleh Git melalui `.gitignore`, karena berisi data operasional.

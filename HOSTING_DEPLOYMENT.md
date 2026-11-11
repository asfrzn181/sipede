# Deployment Hosting / cPanel

## Prasyarat

- PHP 8.2 atau lebih baru
- MySQL 8 atau MariaDB kompatibel
- Apache dengan `mod_rewrite` dan `AllowOverride All`
- Composer tersedia di server

## Database baru

1. Buat database dan user MySQL di cPanel. Gunakan nama lengkap dengan prefix akun.
2. Buka phpMyAdmin dan klik database tersebut di panel kiri.
3. Import `database/schema_full.sql`.
4. Import `database/seed_full.sql` pada database yang sama.
5. Jangan menaruh folder `database/` di document root publik.

## Konfigurasi aplikasi

1. Salin `.env.example` menjadi `.env` di server.
2. Isi URL dan database:

```dotenv
CI_ENV=production
APP_BASE_URL=https://domain-anda.tld/
APP_INDEX_PAGE=
APP_COOKIE_SECURE=1
DB_HOST=localhost
DB_NAME=usfd6ds0_sipede
DB_USER=nama_user
DB_PASS=password_database
ENCRYPTION_KEY=kunci-acak-minimal-32-karakter
MODE_SALDO=PERINGATAN
```

3. Jalankan:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
```

4. Pastikan `application/logs/`, `application/cache/`, dan `uploads/` dapat ditulis PHP.
5. Pastikan `.htaccess` dan `index.php` berada di document root.

## cPanel Git Version Control

Sesuaikan `DEPLOYPATH` pada `.cpanel.yml`. Deployment tidak menyalin SQL ke public root. Composer dipasang di server melalui `composer install`.

## Verifikasi

- Buka `APP_BASE_URL`.
- Uji login akun seed dan ganti password awal `admin123`.
- Pastikan `/application`, `/vendor`, `/database`, dan `/tests` mengembalikan 403.
- Uji dashboard, kegiatan, laporan Excel, dan PDF.

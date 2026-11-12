-- Migrasi kredensial Super Admin pada database yang sudah terpasang.
-- Jalankan sekali melalui phpMyAdmin atau MySQL CLI.
-- Username: superadmin
-- Password baru: setwan2026sipede

START TRANSACTION;

SET @password_superadmin = '$2y$10$5vum7xHiamMexIOhXWONtuDDWHpOThIwQbZvWUP1ejISW5lPIH7.q';

-- Menormalisasi akun lama yang mungkin sudah tersimpan sebagai setwan2026sipede.
-- Jika hasilnya 0 baris, periksa terlebih dahulu apakah akun SUPERADMIN sudah ada.
UPDATE `users` u
JOIN `roles` r ON r.`id` = u.`role_id` AND r.`kode` = 'SUPERADMIN'
LEFT JOIN `users` target ON target.`username` = 'superadmin'
  AND target.`id` <> u.`id`
SET u.`username` = 'superadmin',
    u.`password` = @password_superadmin,
    u.`is_active` = 1,
    u.`wajib_ganti_password` = 0
WHERE u.`username` IN ('superadmin', 'setwan2026sipede')
  AND target.`id` IS NULL;

COMMIT;

-- Verifikasi akun setelah migrasi.
SELECT u.`id`, u.`username`, u.`nama`, r.`kode` AS `role`, u.`is_active`
FROM `users` u
JOIN `roles` r ON r.`id` = u.`role_id`
WHERE u.`username` = 'superadmin';

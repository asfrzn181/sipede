USE anggaran_dprd;

-- Jalankan setelah rbac_tambahan_sql.txt berhasil.
-- Password semua akun berikut: admin123
INSERT INTO users (username,password,nama,role_id,is_active,wajib_ganti_password)
SELECT 'superadmin','$2y$10$nZPUGsK/.d9Z1bafYrSlFOgeMlEGx9Yvk/bMD0YT7.3QAw/g96KYK','Super Admin',r.id,1,0
FROM roles r WHERE r.kode='SUPERADMIN'
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id),is_active=1,wajib_ganti_password=0;

INSERT INTO users (username,password,nama,role_id,is_active,wajib_ganti_password)
SELECT 'admin','$2y$10$nZPUGsK/.d9Z1bafYrSlFOgeMlEGx9Yvk/bMD0YT7.3QAw/g96KYK','Administrator',r.id,1,0
FROM roles r WHERE r.kode='ADMIN'
ON DUPLICATE KEY UPDATE role_id=VALUES(role_id),is_active=1,wajib_ganti_password=0;

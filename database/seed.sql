-- =============================================================================
-- SIPEDE (Sistem Informasi Perjalanan Dinas DPRD)
-- DATA SEED AWAL BERSIH (CLEAN SEED - HANYA AKUN ADMINISTRATOR)
-- =============================================================================

-- Pilih database tujuan terlebih dahulu di phpMyAdmin/cPanel.

-- Password default akun admin: admin123
SET @password_default = '$2y$10$nZPUGsK/.d9Z1bafYrSlFOgeMlEGx9Yvk/bMD0YT7.3QAw/g96KYK';
-- Password default akun superadmin: setwan2026sipede
SET @password_superadmin = '$2y$10$5vum7xHiamMexIOhXWONtuDDWHpOThIwQbZvWUP1ejISW5lPIH7.q';

-- -----------------------------------------------------------------------------
-- 1. Inisialisasi Role Sistem
-- -----------------------------------------------------------------------------
INSERT INTO `roles` (`kode`, `nama`, `keterangan`, `is_system`, `aktif`) VALUES
('SUPERADMIN', 'Super Admin', 'Akses penuh tanpa batasan ke seluruh modul sistem', 1, 1),
('ADMIN',      'Administrator', 'Kelola master data, anggaran pos, user, dan pengaturan sistem', 1, 1),
('PPTK',       'PPTK', 'Verifikator kegiatan dan pengendalian realisasi anggaran', 1, 1),
('OPERATOR',   'Operator', 'Input data kegiatan dan pengajuan verifikasi', 1, 1),
('PIMPINAN',   'Pimpinan DPRD', 'Monitoring dashboard realisasi dan laporan eksekutif', 1, 1),
('PENDAMPING', 'Pendamping', 'Input kegiatan sendiri sampai mencetak bukti perjalanan dinas', 1, 1),
('DPRD',       'Anggota DPRD', 'Akses ke kegiatan dan pagu anggaran milik sendiri', 1, 1),
('VIEWER',     'Viewer', 'Akses hanya melihat data laporan', 1, 1)
ON DUPLICATE KEY UPDATE 
  `nama` = VALUES(`nama`), 
  `keterangan` = VALUES(`keterangan`), 
  `is_system` = VALUES(`is_system`), 
  `aktif` = 1;

-- -----------------------------------------------------------------------------
-- 2. Inisialisasi Permissions (Hak Akses Modul)
-- -----------------------------------------------------------------------------
INSERT INTO `permissions` (`kode`, `modul`, `nama`) VALUES
-- Dashboard
('dashboard.view_all', 'dashboard', 'Lihat dashboard seluruh dinas'),
('dashboard.view_own', 'dashboard', 'Lihat dashboard data milik sendiri'),

-- Tahun Anggaran
('tahun.view',         'tahun',     'Lihat daftar tahun anggaran'),
('tahun.manage',       'tahun',     'Tambah dan kelola tahun anggaran'),
('tahun.lock',         'tahun',     'Kunci / buka kunci transaksi tahun'),

-- Penerima (DPRD & Pendamping)
('penerima.view',      'penerima',  'Lihat data master penerima'),
('penerima.manage',    'penerima',  'Kelola master penerima dan periode'),

-- Jenis Biaya
('jenis_biaya.view',   'jenis_biaya', 'Lihat daftar jenis biaya per dinas'),
('jenis_biaya.manage', 'jenis_biaya', 'Kelola jenis biaya dan standar satuan'),

-- Anggaran & Pos Anggaran
('anggaran.view_all',  'anggaran',  'Lihat seluruh pos & pagu anggaran'),
('anggaran.view_own',  'anggaran',  'Lihat pos & pagu anggaran milik sendiri'),
('anggaran.manage',    'anggaran',  'Kelola pagu, revisi, pergeseran, dan penugasan pos'),

-- Kegiatan Perjalanan Dinas
('kegiatan.view_all',  'kegiatan',  'Lihat seluruh kegiatan perjalanan dinas'),
('kegiatan.view_own',  'kegiatan',  'Lihat kegiatan yang diikuti sendiri'),
('kegiatan.create',    'kegiatan',  'Input rincian kegiatan baru'),
('kegiatan.update',    'kegiatan',  'Ubah data rincian kegiatan'),
('kegiatan.ajukan',    'kegiatan',  'Ajukan kegiatan untuk verifikasi'),
('kegiatan.verify',    'kegiatan',  'Verifikasi / tolak pengajuan kegiatan'),
('kegiatan.cancel',    'kegiatan',  'Batalkan kegiatan yang telah aktif'),

-- Laporan & Rekapitulasi
('laporan.view_all',   'laporan',   'Lihat semua laporan dan rekapitulasi dinas'),
('laporan.view_own',   'laporan',   'Lihat laporan riwayat sendiri'),
('laporan.export',     'laporan',   'Export laporan ke Excel dan PDF'),

-- Pengguna & Pengaturan Sistem
('user.view',          'user',      'Lihat daftar pengguna sistem'),
('user.manage',        'user',      'Kelola pengguna dan reset password'),
('role.manage',        'role',      'Kelola role dan matriks permission'),
('setting.manage',     'setting',   'Kelola KOP surat dan pejabat penandatangan'),
('audit.view',         'audit',     'Lihat log audit aktivitas sistem')
ON DUPLICATE KEY UPDATE 
  `modul` = VALUES(`modul`), 
  `nama` = VALUES(`nama`);

-- -----------------------------------------------------------------------------
-- 3. Mapping Role Permissions
-- -----------------------------------------------------------------------------
-- SUPERADMIN: Seluruh Permission
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r CROSS JOIN `permissions` p WHERE r.kode = 'SUPERADMIN';

-- ADMIN: Seluruh modul operasional & administratif
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all',
  'tahun.view', 'tahun.manage', 'tahun.lock',
  'penerima.view', 'penerima.manage',
  'jenis_biaya.view', 'jenis_biaya.manage',
  'anggaran.view_all', 'anggaran.manage',
  'kegiatan.view_all', 'kegiatan.create', 'kegiatan.update', 'kegiatan.cancel',
  'laporan.view_all', 'laporan.export',
  'user.view', 'user.manage',
  'role.manage',
  'setting.manage',
  'audit.view'
) WHERE r.kode = 'ADMIN';

-- PPTK: Verifikasi dan pemantauan
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all',
  'tahun.view',
  'penerima.view',
  'jenis_biaya.view',
  'anggaran.view_all',
  'kegiatan.view_all', 'kegiatan.create', 'kegiatan.update', 'kegiatan.verify', 'kegiatan.cancel',
  'laporan.view_all', 'laporan.export'
) WHERE r.kode = 'PPTK';

-- OPERATOR: Input dan ajukan
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all',
  'tahun.view',
  'penerima.view',
  'jenis_biaya.view',
  'anggaran.view_all',
  'kegiatan.view_all', 'kegiatan.create', 'kegiatan.update', 'kegiatan.ajukan',
  'laporan.view_all', 'laporan.export'
) WHERE r.kode = 'OPERATOR';

-- PIMPINAN: Monitoring dashboard & laporan
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all',
  'anggaran.view_all',
  'kegiatan.view_all',
  'laporan.view_all', 'laporan.export'
) WHERE r.kode = 'PIMPINAN';

-- PENDAMPING: Self-service
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_own',
  'anggaran.view_own',
  'kegiatan.view_own', 'kegiatan.create', 'kegiatan.update', 'kegiatan.ajukan',
  'laporan.view_own', 'laporan.export'
) WHERE r.kode = 'PENDAMPING';

-- DPRD: Self-service
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_own',
  'anggaran.view_own',
  'kegiatan.view_own',
  'laporan.view_own', 'laporan.export'
) WHERE r.kode = 'DPRD';

-- VIEWER: Read-only
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'laporan.view_all'
) WHERE r.kode = 'VIEWER';

-- -----------------------------------------------------------------------------
-- 4. Akun Administrator (HANYA INI YANG DI-SEED)
-- -----------------------------------------------------------------------------
-- Akun 1: superadmin (Username: superadmin; Password: setwan2026sipede)
INSERT INTO `users` (`username`, `password`, `nama`, `role_id`, `is_active`, `wajib_ganti_password`)
SELECT 'superadmin', @password_superadmin, 'Super Administrator', r.id, 1, 0
FROM `roles` r WHERE r.kode = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE 
  `role_id` = VALUES(`role_id`), 
  `is_active` = 1, 
  `wajib_ganti_password` = 0;

-- Akun 2: admin (Password: admin123)
INSERT INTO `users` (`username`, `password`, `nama`, `role_id`, `is_active`, `wajib_ganti_password`)
SELECT 'admin', @password_default, 'Administrator Utama', r.id, 1, 0
FROM `roles` r WHERE r.kode = 'ADMIN'
ON DUPLICATE KEY UPDATE 
  `role_id` = VALUES(`role_id`), 
  `is_active` = 1, 
  `wajib_ganti_password` = 0;

-- -----------------------------------------------------------------------------
-- 5. Master Referensi Baku (Fondasi Sistem)
-- -----------------------------------------------------------------------------

-- Sumber Dana
INSERT INTO `sumber_dana` (`kode`, `nama`, `is_active`) VALUES
('APBD',    'APBD Sekretariat DPRD', 1),
('DAU',     'Dana Alokasi Umum', 1),
('DAK',     'Dana Alokasi Khusus', 1),
('BOK',     'Bantuan Operasional Kegiatan', 1),
('HIBAH',   'Dana Hibah', 1),
('LAINNYA', 'Sumber Dana Lainnya', 1)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `is_active` = 1;

-- Jenis Biaya Perjalanan Dinas
INSERT INTO `jenis_biaya` (`kode`, `nama`, `satuan`, `is_active`) VALUES
('TRANSPORT',      'Transportasi',        'orang',       1),
('UANG_HARIAN',    'Uang Harian',         'orang/hari',  1),
('PENGINAPAN',     'Penginapan',          'malam',       1),
('TIKET',          'Tiket Perjalanan',    'tiket',       1),
('SEWA_KENDARAAN', 'Sewa Kendaraan',      'hari',        1),
('KONSUMSI',       'Konsumsi',            'orang',       1),
('ATK',            'Alat Tulis Kantor',   'paket',       1),
('DOKUMENTASI',    'Dokumentasi Kegiatan','kegiatan',    1)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `satuan` = VALUES(`satuan`), `is_active` = 1;

-- Jenis Kegiatan dan Kelompok Anggaran (dipakai bersama oleh seluruh modul kegiatan)
INSERT INTO `jenis_kegiatan` (`kode`, `nama`, `subjenis_json`, `urutan`) VALUES
('PERJALANAN_DINAS', 'Perjalanan Dinas', JSON_ARRAY(), 1),
('RAPAT', 'Rapat', JSON_ARRAY('Rapat Paripurna', 'Rapat Komisi', 'Rapat Banggar', 'Rapat Bamus', 'Rapat Khusus'), 2),
('FASILITAS', 'Fasilitas', JSON_ARRAY(), 3),
('LAINNYA', 'Kegiatan Lainnya', JSON_ARRAY(), 99)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `subjenis_json` = VALUES(`subjenis_json`), `is_active` = 1;

INSERT INTO `jenis_anggaran` (`kode`, `nama`, `keterangan`, `urutan`) VALUES
('FASILITAS', 'Fasilitas DPRD', 'Pagu untuk fasilitas dan kebutuhan pendukung DPRD.', 1),
('RAPAT', 'Rapat', 'Pagu untuk rapat paripurna, komisi, Banggar, Bamus, dan rapat khusus.', 2),
('PERJALANAN_DINAS', 'Perjalanan Dinas', 'Pagu perjalanan dinas anggota DPRD dan pendamping.', 3),
('HONORARIUM', 'Honorarium / Notulen', 'Pagu honorarium, notulen, dan tenaga pendukung.', 4),
('LAINNYA', 'Anggaran Lainnya', 'Kelompok anggaran tambahan sesuai kebutuhan.', 99)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `keterangan` = VALUES(`keterangan`), `is_active` = 1;

-- Pengaturan KOP Dokumen & Pejabat Penandatangan Resmi (Default)
INSERT INTO `pengaturan_dokumen` (
  `id`, `nama_pemda`, `nama_lembaga`, `nama_skpd`, `alamat`, 
  `telepon`, `fax`, `email`, `website`, `kode_pos`, `logo_path`, `kota_penetapan`,
  `pptk_label`, `pptk_jabatan`, `pptk_nama`, `pptk_nip`, `pptk_pangkat`,
  `bendahara_label`, `bendahara_jabatan`, `bendahara_nama`, `bendahara_nip`, `bendahara_pangkat`
) VALUES (
  1,
  'PEMERINTAH DAERAH PROVINSI / KABUPATEN',
  'DEWAN PERWAKILAN RAKYAT DAERAH',
  'SEKRETARIAT DPRD',
  'Jalan Protokol Pemerintahan No. 01, Kompleks Perkantoran Pemerintah Daerah',
  '(021) 1234567',
  '(021) 1234568',
  'setwan@daerah.go.id',
  'dprd.daerah.go.id',
  '12345',
  'https://bangka.go.id/assets/images.png',
  'Ibukota',
  'Pejabat Pelaksana Teknis Kegiatan',
  'PPTK Perjalanan Dinas',
  'BAMBANG SUSANTO, S.E., M.M.',
  '19820415 200501 1 008',
  'Penata Tk. I (III/d)',
  'Bendahara Pengeluaran',
  'Bendahara Pengeluaran',
  'SITI NURHALIZA, S.Ak.',
  '19881120 201101 2 004',
  'Penata Muda Tk. I (III/b)'
) ON DUPLICATE KEY UPDATE `id` = 1;

-- Tahun Anggaran Berjalan (Tahun Aktif Pertama: 2026)
INSERT INTO `tahun_anggaran` (`id`, `tahun`, `is_locked`) 
VALUES (1, 2026, 0)
ON DUPLICATE KEY UPDATE `tahun` = 2026, `is_locked` = 0;

-- CATATAN:
-- Tabel 'penerima' (DPRD & Pendamping) KOSONG
-- Tabel 'kegiatan' KOSONG
-- Tabel 'kegiatan_peserta' KOSONG
-- Tabel 'kegiatan_biaya' KOSONG
-- Tabel 'anggaran' KOSONG
-- Tabel 'penugasan_pos' KOSONG
-- Administrator dapat mulai testing dari awal.
-- Jalankan database/inject_anggota_dprd_2024_2029.sql bila ingin memasukkan
-- 34 anggota DPRD beserta pagu dan pos anggarannya.


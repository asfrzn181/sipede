-- =============================================================================
-- SIPEDE (Sistem Informasi Perjalanan Dinas DPRD)
-- ALL-IN-ONE CLEAN INSTALLATION SCRIPT (SCHEMA + SEED ADMIN ONLY)
-- =============================================================================

CREATE DATABASE IF NOT EXISTS `anggaran_dprd` 
  CHARACTER SET utf8mb4 
  COLLATE utf8mb4_unicode_ci;

USE `anggaran_dprd`;

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. Tabel Session
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `ci_sessions`;
CREATE TABLE `ci_sessions` (
  `id` VARCHAR(128) NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `timestamp` INT UNSIGNED NOT NULL DEFAULT 0,
  `data` BLOB NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 2. RBAC (Roles, Permissions, Role_Permissions)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;

CREATE TABLE `roles` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(30) NOT NULL UNIQUE,
  `nama` VARCHAR(100) NOT NULL,
  `keterangan` VARCHAR(255) NULL,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0,
  `aktif` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `permissions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(60) NOT NULL UNIQUE,
  `modul` VARCHAR(30) NOT NULL,
  `nama` VARCHAR(120) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `role_permissions` (
  `role_id` INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_rp_permission` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 3. Master Data Dasar (Tahun Anggaran, Penerima, Sumber Dana, Jenis Biaya)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `tahun_anggaran`;
CREATE TABLE `tahun_anggaran` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun` SMALLINT UNSIGNED NOT NULL UNIQUE,
  `is_locked` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `penerima`;
CREATE TABLE `penerima` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama` VARCHAR(150) NOT NULL,
  `tipe` VARCHAR(30) NOT NULL,
  `jabatan` VARCHAR(80) NULL,
  `periode_mulai` SMALLINT UNSIGNED NULL,
  `periode_akhir` SMALLINT UNSIGNED NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_penerima_tipe` (`tipe`),
  INDEX `idx_penerima_aktif` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `sumber_dana`;
CREATE TABLE `sumber_dana` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(30) NOT NULL UNIQUE,
  `nama` VARCHAR(150) NOT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `jenis_biaya`;
CREATE TABLE `jenis_biaya` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(30) NOT NULL UNIQUE,
  `nama` VARCHAR(120) NOT NULL,
  `satuan` VARCHAR(30) NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 4. Pos Anggaran & Penugasan Pos
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `penugasan_pos`;
DROP TABLE IF EXISTS `pos_anggaran`;

CREATE TABLE `pos_anggaran` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun_anggaran_id` INT UNSIGNED NOT NULL,
  `kode` VARCHAR(50) NOT NULL,
  `nama` VARCHAR(200) NOT NULL,
  `tipe` ENUM('DPRD','PENDAMPING') NOT NULL,
  `penerima_id` INT UNSIGNED NULL,
  `pagu` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `kelompok_jabatan` ENUM('KETUA','WAKIL_KETUA','ANGGOTA') NULL,
  `urutan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_pos_tahun_kode` (`tahun_anggaran_id`, `kode`),
  INDEX `idx_pos_tahun` (`tahun_anggaran_id`),
  INDEX `idx_pos_penerima` (`penerima_id`),
  INDEX `idx_pos_tipe` (`tipe`),
  CONSTRAINT `fk_pos_tahun` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `tahun_anggaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pos_penerima` FOREIGN KEY (`penerima_id`) REFERENCES `penerima` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `penugasan_pos` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `pos_anggaran_id` INT UNSIGNED NOT NULL,
  `penerima_id` INT UNSIGNED NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NULL,
  `keterangan` TEXT NULL,
  `dibuat_oleh` INT UNSIGNED NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_penugasan_pos` (`pos_anggaran_id`),
  INDEX `idx_penugasan_penerima` (`penerima_id`),
  INDEX `idx_penugasan_aktif` (`pos_anggaran_id`, `tanggal_selesai`),
  CONSTRAINT `fk_penugasan_pos` FOREIGN KEY (`pos_anggaran_id`) REFERENCES `pos_anggaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_penugasan_penerima` FOREIGN KEY (`penerima_id`) REFERENCES `penerima` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 5. Tabel Pengguna (Users)
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(60) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `role_id` INT UNSIGNED NOT NULL,
  `penerima_id` INT UNSIGNED NULL UNIQUE,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `wajib_ganti_password` TINYINT(1) NOT NULL DEFAULT 0,
  `terakhir_login` DATETIME NULL,
  `login_failures` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until` DATETIME NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_user_role` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`),
  CONSTRAINT `fk_user_penerima` FOREIGN KEY (`penerima_id`) REFERENCES `penerima` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 6. Tabel Pagu Anggaran Konvensional
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `anggaran_rincian`;
DROP TABLE IF EXISTS `anggaran`;

CREATE TABLE `anggaran` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun_anggaran_id` INT UNSIGNED NOT NULL,
  `penerima_id` INT UNSIGNED NOT NULL,
  `sumber_dana_id` INT UNSIGNED NULL,
  `pagu` DECIMAL(18,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_anggaran` (`tahun_anggaran_id`, `penerima_id`),
  CONSTRAINT `fk_anggaran_tahun` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `tahun_anggaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anggaran_penerima` FOREIGN KEY (`penerima_id`) REFERENCES `penerima` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anggaran_sumber` FOREIGN KEY (`sumber_dana_id`) REFERENCES `sumber_dana` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `anggaran_rincian` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `anggaran_id` INT UNSIGNED NOT NULL,
  `jenis_biaya_id` INT UNSIGNED NOT NULL,
  `pagu` DECIMAL(18,2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_anggaran_rincian` (`anggaran_id`, `jenis_biaya_id`),
  CONSTRAINT `fk_ar_anggaran` FOREIGN KEY (`anggaran_id`) REFERENCES `anggaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ar_jenis` FOREIGN KEY (`jenis_biaya_id`) REFERENCES `jenis_biaya` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 7. Transaksi Kegiatan Perjalanan Dinas
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `kegiatan_biaya`;
DROP TABLE IF EXISTS `kegiatan_peserta`;
DROP TABLE IF EXISTS `kegiatan`;

CREATE TABLE `kegiatan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tahun_anggaran_id` INT UNSIGNED NOT NULL,
  `nama_kegiatan` VARCHAR(200) NOT NULL,
  `no_surat_tugas` VARCHAR(100) NULL,
  `tanggal_surat_tugas` DATE NULL,
  `tema` VARCHAR(255) NULL,
  `tanggal_input` DATE NOT NULL,
  `tanggal_mulai` DATE NOT NULL,
  `tanggal_selesai` DATE NOT NULL,
  `tempat` VARCHAR(200) NULL,
  `status` ENUM('DRAFT','DIAJUKAN','DITOLAK','AKTIF','BATAL') NOT NULL DEFAULT 'DRAFT',
  `total` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `alasan_batal` TEXT NULL,
  `diajukan_oleh` INT UNSIGNED NULL,
  `diajukan_at` DATETIME NULL,
  `diverifikasi_oleh` INT UNSIGNED NULL,
  `diverifikasi_at` DATETIME NULL,
  `catatan_verifikasi` VARCHAR(255) NULL,
  `created_by` INT UNSIGNED NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_kegiatan_tanggal` (`tanggal_mulai`),
  INDEX `idx_kegiatan_status` (`status`),
  CONSTRAINT `fk_kegiatan_tahun` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `tahun_anggaran` (`id`),
  CONSTRAINT `fk_kegiatan_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  CONSTRAINT `fk_kegiatan_submitter` FOREIGN KEY (`diajukan_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_kegiatan_verifier` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kegiatan_peserta` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kegiatan_id` INT UNSIGNED NOT NULL,
  `penerima_id` INT UNSIGNED NOT NULL,
  `sumber_dana_id` INT UNSIGNED NULL,
  `pos_anggaran_id` INT UNSIGNED NULL,
  `alasan_pengalihan` TEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kegiatan_peserta` (`kegiatan_id`, `penerima_id`),
  INDEX `idx_kp_pos_anggaran` (`pos_anggaran_id`),
  CONSTRAINT `fk_kp_kegiatan` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kp_penerima` FOREIGN KEY (`penerima_id`) REFERENCES `penerima` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kp_sumber` FOREIGN KEY (`sumber_dana_id`) REFERENCES `sumber_dana` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_kp_pos` FOREIGN KEY (`pos_anggaran_id`) REFERENCES `pos_anggaran` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `kegiatan_biaya` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kegiatan_peserta_id` INT UNSIGNED NOT NULL,
  `jenis_biaya_id` INT UNSIGNED NOT NULL,
  `volume` DECIMAL(14,2) NOT NULL DEFAULT 0,
  `tarif` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `subtotal` DECIMAL(18,2) AS (`volume` * `tarif`) STORED,
  PRIMARY KEY (`id`),
  INDEX `idx_kb_peserta` (`kegiatan_peserta_id`),
  INDEX `idx_kb_jenis` (`jenis_biaya_id`),
  CONSTRAINT `fk_kb_peserta` FOREIGN KEY (`kegiatan_peserta_id`) REFERENCES `kegiatan_peserta` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_kb_jenis` FOREIGN KEY (`jenis_biaya_id`) REFERENCES `jenis_biaya` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 8. Laporan Tersimpan, Audit Log, dan Pengaturan Dokumen
-- -----------------------------------------------------------------------------
DROP TABLE IF EXISTS `laporan_tersimpan`;
CREATE TABLE `laporan_tersimpan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `nama` VARCHAR(150) NOT NULL,
  `config` JSON NOT NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_laporan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NULL,
  `entity` VARCHAR(50) NOT NULL,
  `entity_id` INT UNSIGNED NULL,
  `action` VARCHAR(30) NOT NULL,
  `old_data` JSON NULL,
  `new_data` JSON NULL,
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_audit_entity` (`entity`, `entity_id`),
  CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `pengaturan_dokumen`;
CREATE TABLE `pengaturan_dokumen` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `nama_pemda` VARCHAR(200) NOT NULL DEFAULT 'PEMERINTAH DAERAH',
  `nama_lembaga` VARCHAR(200) NOT NULL DEFAULT 'DEWAN PERWAKILAN RAKYAT DAERAH',
  `nama_skpd` VARCHAR(200) NOT NULL DEFAULT 'SEKRETARIAT DPRD',
  `alamat` TEXT NOT NULL,
  `telepon` VARCHAR(100) NULL DEFAULT '(021) 1234567',
  `fax` VARCHAR(100) NULL DEFAULT '(021) 1234568',
  `email` VARCHAR(100) NULL DEFAULT 'setwan@daerah.go.id',
  `website` VARCHAR(100) NULL DEFAULT 'dprd.daerah.go.id',
  `kode_pos` VARCHAR(20) NULL DEFAULT '12345',
  `logo_path` VARCHAR(255) NULL,
  `kota_penetapan` VARCHAR(100) NOT NULL DEFAULT 'Ibukota',

  `pa_label` VARCHAR(100) NOT NULL DEFAULT 'Mengetahui,\nPengguna Anggaran',
  `pa_jabatan` VARCHAR(150) NOT NULL DEFAULT 'Sekretaris DPRD',
  `pa_nama` VARCHAR(150) NOT NULL DEFAULT 'Drs. H. AHMAD FAUZI, M.Si',
  `pa_nip` VARCHAR(50) NOT NULL DEFAULT '19750812 199803 1 002',
  `pa_pangkat` VARCHAR(100) NULL DEFAULT 'Pembina Utama Muda (IV/c)',

  `pptk_label` VARCHAR(100) NOT NULL DEFAULT 'Pejabat Pelaksana Teknis Kegiatan',
  `pptk_jabatan` VARCHAR(150) NOT NULL DEFAULT 'PPTK Perjalanan Dinas',
  `pptk_nama` VARCHAR(150) NOT NULL DEFAULT 'BAMBANG SUSANTO, S.E., M.M.',
  `pptk_nip` VARCHAR(50) NOT NULL DEFAULT '19820415 200501 1 008',
  `pptk_pangkat` VARCHAR(100) NULL DEFAULT 'Penata Tk. I (III/d)',

  `bendahara_label` VARCHAR(100) NOT NULL DEFAULT 'Bendahara Pengeluaran',
  `bendahara_jabatan` VARCHAR(150) NOT NULL DEFAULT 'Bendahara Pengeluaran',
  `bendahara_nama` VARCHAR(150) NOT NULL DEFAULT 'SITI NURHALIZA, S.Ak.',
  `bendahara_nip` VARCHAR(50) NOT NULL DEFAULT '19881120 201101 2 004',
  `bendahara_pangkat` VARCHAR(100) NULL DEFAULT 'Penata Muda Tk. I (III/b)',

  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------------------------------
-- 9. Database Views
-- -----------------------------------------------------------------------------
CREATE OR REPLACE VIEW `v_realisasi` AS
SELECT 
  k.tahun_anggaran_id, 
  kp.penerima_id, 
  SUM(kb.volume * kb.tarif) AS realisasi
FROM `kegiatan` k 
JOIN `kegiatan_peserta` kp ON kp.kegiatan_id = k.id
JOIN `kegiatan_biaya` kb   ON kb.kegiatan_peserta_id = kp.id
WHERE k.status = 'AKTIF'
GROUP BY k.tahun_anggaran_id, kp.penerima_id;

CREATE OR REPLACE VIEW `v_sisa_pos` AS
SELECT
  pa.id AS pos_anggaran_id,
  pa.tahun_anggaran_id,
  pa.kode,
  pa.nama AS pos_nama,
  pa.tipe,
  pa.penerima_id,
  pa.pagu,
  pa.kelompok_jabatan,
  COALESCE(SUM(kb.volume * kb.tarif), 0) AS realisasi,
  pa.pagu - COALESCE(SUM(kb.volume * kb.tarif), 0) AS sisa
FROM `pos_anggaran` pa
LEFT JOIN `kegiatan_peserta` kp ON kp.pos_anggaran_id = pa.id
LEFT JOIN `kegiatan_biaya` kb   ON kb.kegiatan_peserta_id = kp.id
LEFT JOIN `kegiatan` k          ON k.id = kp.kegiatan_id AND k.status NOT IN ('BATAL')
GROUP BY pa.id;

CREATE OR REPLACE VIEW `v_sisa_anggaran` AS
SELECT 
  a.tahun_anggaran_id,
  a.penerima_id,
  p.nama,
  p.tipe,
  p.jabatan,
  a.sumber_dana_id,
  sd.kode AS sumber_dana_kode,
  sd.nama AS sumber_dana_nama,
  a.pagu,
  COALESCE(r.realisasi, 0) AS realisasi,
  (a.pagu - COALESCE(r.realisasi, 0)) AS sisa
FROM `anggaran` a 
JOIN `penerima` p ON p.id = a.penerima_id
LEFT JOIN `sumber_dana` sd ON sd.id = a.sumber_dana_id
LEFT JOIN `v_realisasi` r ON r.tahun_anggaran_id = a.tahun_anggaran_id AND r.penerima_id = a.penerima_id;

CREATE OR REPLACE VIEW `v_detail_biaya` AS
SELECT 
  k.id AS kegiatan_id,
  k.tahun_anggaran_id,
  k.nama_kegiatan,
  k.no_surat_tugas,
  k.tanggal_surat_tugas,
  k.tema,
  k.tanggal_input,
  k.tanggal_mulai,
  k.tanggal_selesai,
  k.tempat,
  k.status,
  kp.penerima_id,
  p.nama AS penerima_nama,
  p.tipe,
  p.jabatan,
  kp.pos_anggaran_id,
  COALESCE(pa.nama, sd.nama, 'APBD') AS sumber_dana_nama,
  kp.sumber_dana_id,
  sd.kode AS sumber_dana_kode,
  j.id AS jenis_biaya_id,
  j.kode AS jenis_biaya_kode,
  j.nama AS jenis_biaya_nama,
  kb.volume,
  kb.tarif,
  (kb.volume * kb.tarif) AS subtotal
FROM `kegiatan` k 
JOIN `kegiatan_peserta` kp ON kp.kegiatan_id = k.id
JOIN `penerima` p         ON p.id = kp.penerima_id
LEFT JOIN `pos_anggaran` pa ON pa.id = kp.pos_anggaran_id
LEFT JOIN `sumber_dana` sd  ON sd.id = kp.sumber_dana_id
JOIN `kegiatan_biaya` kb   ON kb.kegiatan_peserta_id = kp.id
JOIN `jenis_biaya` j       ON j.id = kb.jenis_biaya_id;

SET FOREIGN_KEY_CHECKS = 1;

-- =============================================================================
-- DATA SEED AWAL (HANYA AKUN ADMINISTRATOR & MASTER BAKU)
-- =============================================================================
SET @password_default = '$2y$10$nZPUGsK/.d9Z1bafYrSlFOgeMlEGx9Yvk/bMD0YT7.3QAw/g96KYK';

INSERT INTO `roles` (`kode`, `nama`, `keterangan`, `is_system`, `aktif`) VALUES
('SUPERADMIN', 'Super Admin', 'Akses penuh tanpa batasan ke seluruh modul sistem', 1, 1),
('ADMIN',      'Administrator', 'Kelola master data, anggaran pos, user, dan pengaturan sistem', 1, 1),
('PPTK',       'PPTK', 'Verifikator kegiatan dan pengendalian realisasi anggaran', 1, 1),
('OPERATOR',   'Operator', 'Input data kegiatan dan pengajuan verifikasi', 1, 1),
('PIMPINAN',   'Pimpinan DPRD', 'Monitoring dashboard realisasi dan laporan eksekutif', 1, 1),
('PENDAMPING', 'Pendamping', 'Akses ke kegiatan dan pos anggaran yang ditugaskan', 1, 1),
('DPRD',       'Anggota DPRD', 'Akses ke kegiatan dan pagu anggaran milik sendiri', 1, 1),
('VIEWER',     'Viewer', 'Akses hanya melihat data laporan', 1, 1)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `is_system` = 1, `aktif` = 1;

INSERT INTO `permissions` (`kode`, `modul`, `nama`) VALUES
('dashboard.view_all', 'dashboard', 'Lihat dashboard seluruh dinas'),
('dashboard.view_own', 'dashboard', 'Lihat dashboard data milik sendiri'),
('tahun.view',         'tahun',     'Lihat daftar tahun anggaran'),
('tahun.manage',       'tahun',     'Tambah dan kelola tahun anggaran'),
('tahun.lock',         'tahun',     'Kunci / buka kunci transaksi tahun'),
('penerima.view',      'penerima',  'Lihat data master penerima'),
('penerima.manage',    'penerima',  'Kelola master penerima dan periode'),
('jenis_biaya.view',   'jenis_biaya', 'Lihat daftar jenis biaya per dinas'),
('jenis_biaya.manage', 'jenis_biaya', 'Kelola jenis biaya dan standar satuan'),
('anggaran.view_all',  'anggaran',  'Lihat seluruh pos & pagu anggaran'),
('anggaran.view_own',  'anggaran',  'Lihat pos & pagu anggaran milik sendiri'),
('anggaran.manage',    'anggaran',  'Kelola pagu, revisi, pergeseran, dan penugasan pos'),
('kegiatan.view_all',  'kegiatan',  'Lihat seluruh kegiatan perjalanan dinas'),
('kegiatan.view_own',  'kegiatan',  'Lihat kegiatan yang diikuti sendiri'),
('kegiatan.create',    'kegiatan',  'Input rincian kegiatan baru'),
('kegiatan.update',    'kegiatan',  'Ubah data rincian kegiatan'),
('kegiatan.ajukan',    'kegiatan',  'Ajukan kegiatan untuk verifikasi'),
('kegiatan.verify',    'kegiatan',  'Verifikasi / tolak pengajuan kegiatan'),
('kegiatan.cancel',    'kegiatan',  'Batalkan kegiatan yang telah aktif'),
('laporan.view_all',   'laporan',   'Lihat semua laporan dan rekapitulasi dinas'),
('laporan.view_own',   'laporan',   'Lihat laporan riwayat sendiri'),
('laporan.export',     'laporan',   'Export laporan ke Excel dan PDF'),
('user.view',          'user',      'Lihat daftar pengguna sistem'),
('user.manage',        'user',      'Kelola pengguna dan reset password'),
('role.manage',        'role',      'Kelola role dan matriks permission'),
('setting.manage',     'setting',   'Kelola KOP surat dan pejabat penandatangan'),
('audit.view',         'audit',     'Lihat log audit aktivitas sistem')
ON DUPLICATE KEY UPDATE `modul` = VALUES(`modul`), `nama` = VALUES(`nama`);

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r CROSS JOIN `permissions` p WHERE r.kode = 'SUPERADMIN';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all', 'tahun.view', 'tahun.manage', 'tahun.lock',
  'penerima.view', 'penerima.manage', 'jenis_biaya.view', 'jenis_biaya.manage',
  'anggaran.view_all', 'anggaran.manage', 'kegiatan.view_all', 'kegiatan.create',
  'kegiatan.update', 'kegiatan.cancel', 'laporan.view_all', 'laporan.export',
  'user.view', 'user.manage', 'role.manage', 'setting.manage', 'audit.view'
) WHERE r.kode = 'ADMIN';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all', 'tahun.view', 'penerima.view', 'jenis_biaya.view',
  'anggaran.view_all', 'kegiatan.view_all', 'kegiatan.create', 'kegiatan.update',
  'kegiatan.verify', 'kegiatan.cancel', 'laporan.view_all', 'laporan.export'
) WHERE r.kode = 'PPTK';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all', 'tahun.view', 'penerima.view', 'jenis_biaya.view',
  'anggaran.view_all', 'kegiatan.view_all', 'kegiatan.create', 'kegiatan.update',
  'kegiatan.ajukan', 'laporan.view_all', 'laporan.export'
) WHERE r.kode = 'OPERATOR';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_all', 'anggaran.view_all', 'kegiatan.view_all', 'laporan.view_all', 'laporan.export'
) WHERE r.kode = 'PIMPINAN';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_own', 'anggaran.view_own', 'kegiatan.view_own', 'kegiatan.ajukan', 'laporan.view_own', 'laporan.export'
) WHERE r.kode = 'PENDAMPING';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode IN (
  'dashboard.view_own', 'anggaran.view_own', 'kegiatan.view_own', 'laporan.view_own', 'laporan.export'
) WHERE r.kode = 'DPRD';

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id FROM `roles` r JOIN `permissions` p ON p.kode = 'laporan.view_all' WHERE r.kode = 'VIEWER';

-- Akun Administrator Utama
INSERT INTO `users` (`username`, `password`, `nama`, `role_id`, `is_active`, `wajib_ganti_password`)
SELECT 'superadmin', @password_default, 'Super Administrator', r.id, 1, 0
FROM `roles` r WHERE r.kode = 'SUPERADMIN'
ON DUPLICATE KEY UPDATE `role_id` = VALUES(`role_id`), `is_active` = 1, `wajib_ganti_password` = 0;

INSERT INTO `users` (`username`, `password`, `nama`, `role_id`, `is_active`, `wajib_ganti_password`)
SELECT 'admin', @password_default, 'Administrator Utama', r.id, 1, 0
FROM `roles` r WHERE r.kode = 'ADMIN'
ON DUPLICATE KEY UPDATE `role_id` = VALUES(`role_id`), `is_active` = 1, `wajib_ganti_password` = 0;

-- Master Referensi
INSERT INTO `sumber_dana` (`kode`, `nama`, `is_active`) VALUES
('APBD',    'APBD Sekretariat DPRD', 1),
('DAU',     'Dana Alokasi Umum', 1),
('DAK',     'Dana Alokasi Khusus', 1),
('BOK',     'Bantuan Operasional Kegiatan', 1),
('HIBAH',   'Dana Hibah', 1),
('LAINNYA', 'Sumber Dana Lainnya', 1)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `is_active` = 1;

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

INSERT INTO `pengaturan_dokumen` (
  `id`, `nama_pemda`, `nama_lembaga`, `nama_skpd`, `alamat`, 
  `telepon`, `fax`, `email`, `website`, `kode_pos`, `logo_path`, `kota_penetapan`,
  `pa_label`, `pa_jabatan`, `pa_nama`, `pa_nip`, `pa_pangkat`,
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
  'assets/img/logo_default.svg',
  'Ibukota',
  'Mengetahui,\nPengguna Anggaran',
  'Sekretaris DPRD',
  'Drs. H. AHMAD FAUZI, M.Si',
  '19750812 199803 1 002',
  'Pembina Utama Muda (IV/c)',
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

INSERT INTO `tahun_anggaran` (`id`, `tahun`, `is_locked`) 
VALUES (1, 2026, 0)
ON DUPLICATE KEY UPDATE `tahun` = 2026, `is_locked` = 0;

INSERT INTO `pos_anggaran` (`tahun_anggaran_id`, `kode`, `nama`, `tipe`, `penerima_id`, `pagu`, `kelompok_jabatan`, `urutan`, `is_active`) VALUES
(1, 'POS-AJUDAN-KTU', 'Ajudan Ketua DPRD',                  'PENDAMPING', NULL, 0, NULL, 1, 1),
(1, 'POS-AJUDAN-WK1', 'Ajudan Wakil Ketua I',               'PENDAMPING', NULL, 0, NULL, 2, 1),
(1, 'POS-AJUDAN-WK2', 'Ajudan Wakil Ketua II',              'PENDAMPING', NULL, 0, NULL, 3, 1),
(1, 'POS-AJUDAN-WK3', 'Ajudan Wakil Ketua III',             'PENDAMPING', NULL, 0, NULL, 4, 1),
(1, 'POS-SEKRETARIS', 'Sekretaris DPRD',                    'PENDAMPING', NULL, 0, NULL, 5, 1),
(1, 'POS-KABAG',      'Kepala Bagian Umum',                 'PENDAMPING', NULL, 0, NULL, 6, 1),
(1, 'POS-KASUBAG',    'Ka. Sub Bag Perjalanan Dinas',        'PENDAMPING', NULL, 0, NULL, 7, 1),
(1, 'POS-STAF-ADM',   'Staf Administrasi Perjalanan Dinas', 'PENDAMPING', NULL, 0, NULL, 8, 1)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`), `tipe` = 'PENDAMPING';


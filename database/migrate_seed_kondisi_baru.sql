-- =============================================================================
-- SIPEDE - MIGRASI SEED KONDISI BARU
-- =============================================================================
-- Jalankan setelah migrate_schema_kondisi_baru.sql.
-- Mencakup:
--   1. Upsert 4 jenis biaya baku
--   2. Upsert jenis anggaran Perjalanan Dinas
--   3. Tambah permission kegiatan.verify untuk role ADMIN
--   4. Default penandatangan Verifikator pada pengaturan dokumen

SET NAMES utf8mb4;
START TRANSACTION;

-- 1. Upsert 4 jenis biaya baku
INSERT INTO `jenis_biaya` (`kode`, `nama`, `satuan`, `tampil_di_kegiatan`, `is_active`) VALUES
('UANG_HARIAN',        'Uang Harian',          'orang/hari', 1, 1),
('PENGINAPAN',         'Uang Penginapan',      'malam',      1, 1),
('UANG_REPRESENTATIF', 'Uang Representatif',   'orang/hari', 1, 1),
('TRANSPORT',          'Uang Transport',       'orang',      1, 1)
ON DUPLICATE KEY UPDATE
  `nama` = VALUES(`nama`),
  `satuan` = VALUES(`satuan`),
  `tampil_di_kegiatan` = VALUES(`tampil_di_kegiatan`),
  `is_active` = 1;

-- 2. Upsert jenis anggaran Perjalanan Dinas
INSERT INTO `jenis_anggaran` (`kode`, `nama`, `keterangan`, `urutan`, `is_active`) VALUES
('PERJALANAN_DINAS', 'Perjalanan Dinas', 'Pagu perjalanan dinas anggota DPRD dan pendamping.', 1, 1)
ON DUPLICATE KEY UPDATE
  `nama` = VALUES(`nama`),
  `keterangan` = VALUES(`keterangan`),
  `is_active` = 1;

-- 3. Tambah permission kegiatan.verify untuk role ADMIN
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`)
SELECT r.id, p.id
FROM `roles` r
JOIN `permissions` p ON p.kode = 'kegiatan.verify'
WHERE r.kode = 'ADMIN';

-- 4. Default penandatangan Verifikator (jika kolom sudah direname)
UPDATE `pengaturan_dokumen`
SET
  `verifikator_label` = COALESCE(NULLIF(`verifikator_label`, ''), 'Verifikator'),
  `verifikator_jabatan` = COALESCE(NULLIF(`verifikator_jabatan`, ''), 'Verifikator Kegiatan'),
  `verifikator_nama` = COALESCE(NULLIF(`verifikator_nama`, ''), 'VERIFIKATOR'),
  `verifikator_nip` = COALESCE(NULLIF(`verifikator_nip`, ''), '-'),
  `verifikator_pangkat` = COALESCE(NULLIF(`verifikator_pangkat`, ''), '-')
WHERE `id` = 1;

COMMIT;

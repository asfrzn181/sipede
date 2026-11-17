-- =============================================================================
-- SIPEDE - MIGRASI SCHEMA KONDISI BARU
-- =============================================================================
-- Jalankan pada database yang sudah berjalan (tanpa instal ulang).
-- Mencakup:
--   1. Rename penandatangan Bendahara Pengeluaran -> Verifikator
--   2. Nonaktifkan jenis biaya di luar 4 komponen baku
--   3. Nonaktifkan jenis anggaran di luar Perjalanan Dinas
--   4. Sesuaikan pos yang masih memakai jenis anggaran lama

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
START TRANSACTION;

-- 1. Rename kolom penandatangan (jika kolom lama masih ada)
ALTER TABLE `pengaturan_dokumen`
  RENAME COLUMN `bendahara_label` TO `verifikator_label`,
  RENAME COLUMN `bendahara_jabatan` TO `verifikator_jabatan`,
  RENAME COLUMN `bendahara_nama` TO `verifikator_nama`,
  RENAME COLUMN `bendahara_nip` TO `verifikator_nip`,
  RENAME COLUMN `bendahara_pangkat` TO `verifikator_pangkat`;

-- 2. Nonaktifkan jenis biaya selain 4 komponen baku
UPDATE `jenis_biaya`
SET `is_active` = 0
WHERE `kode` NOT IN ('UANG_HARIAN', 'PENGINAPAN', 'UANG_REPRESENTATIF', 'TRANSPORT');

UPDATE `jenis_biaya`
SET `tampil_di_kegiatan` = 1
WHERE `kode` IN ('UANG_HARIAN', 'PENGINAPAN', 'UANG_REPRESENTATIF', 'TRANSPORT');

-- 3. Nonaktifkan jenis anggaran selain Perjalanan Dinas
UPDATE `jenis_anggaran`
SET `is_active` = 0
WHERE `kode` <> 'PERJALANAN_DINAS';

-- 4. Sesuaikan pos yang memakai jenis anggaran lama ke Perjalanan Dinas
UPDATE `pos_anggaran` pa
JOIN `jenis_anggaran` ja_lama ON ja_lama.id = pa.jenis_anggaran_id AND ja_lama.kode <> 'PERJALANAN_DINAS'
JOIN `jenis_anggaran` ja_baru ON ja_baru.kode = 'PERJALANAN_DINAS'
SET pa.jenis_anggaran_id = ja_baru.id;

COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

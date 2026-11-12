-- Migrasi komponen biaya: default otomatis dan keterangan per rincian.
-- Jalankan sekali pada database lama jika tidak menggunakan schema.sql fresh.

SET @db_name = DATABASE();

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE kegiatan_biaya ADD COLUMN keterangan TEXT NULL AFTER jenis_biaya_id',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = @db_name
    AND table_name = 'kegiatan_biaya'
    AND column_name = 'keterangan'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

INSERT INTO jenis_biaya (kode, nama, satuan, tampil_di_kegiatan, is_active)
VALUES
  ('UANG_REPRESENTATIF', 'Uang Representatif', 'orang/hari', 1, 1)
ON DUPLICATE KEY UPDATE
  nama = VALUES(nama), satuan = VALUES(satuan), is_active = 1;

UPDATE jenis_biaya
SET tampil_di_kegiatan = CASE kode
  WHEN 'UANG_HARIAN' THEN 1
  WHEN 'TRANSPORT' THEN 1
  WHEN 'UANG_REPRESENTATIF' THEN 1
  WHEN 'PENGINAPAN' THEN 1
  ELSE 0
END
WHERE kode IN (
  'UANG_HARIAN', 'TRANSPORT', 'UANG_REPRESENTATIF', 'PENGINAPAN',
  'TIKET', 'SEWA_KENDARAAN', 'KONSUMSI', 'ATK', 'DOKUMENTASI'
);

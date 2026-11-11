-- Tambahkan Dana Awal pada peserta kegiatan untuk database yang sudah berjalan.
-- Jalankan sekali setelah memilih database tujuan di phpMyAdmin.

SET @db_name = DATABASE();

SET @sql = (
  SELECT IF(COUNT(*) = 0,
    'ALTER TABLE kegiatan_peserta ADD COLUMN dana_awal DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER alasan_pengalihan',
    'SELECT 1'
  )
  FROM information_schema.columns
  WHERE table_schema = @db_name
    AND table_name = 'kegiatan_peserta'
    AND column_name = 'dana_awal'
);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Kolom dana_awal siap digunakan.' AS hasil;

USE anggaran_dprd;

-- Migrasi idempoten: aman dijalankan jika sebagian kolom sudah dibuat schema utama.
CREATE TABLE IF NOT EXISTS sumber_dana (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(30) NOT NULL UNIQUE,
  nama VARCHAR(150) NOT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='anggaran' AND COLUMN_NAME='sumber_dana_id');
SET @sql = IF(@has_col=0, 'ALTER TABLE anggaran ADD COLUMN sumber_dana_id INT UNSIGNED NULL AFTER penerima_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_fk = (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='anggaran' AND COLUMN_NAME='sumber_dana_id' AND REFERENCED_TABLE_NAME='sumber_dana');
SET @sql = IF(@has_fk=0, 'ALTER TABLE anggaran ADD CONSTRAINT fk_anggaran_sumber_dana FOREIGN KEY (sumber_dana_id) REFERENCES sumber_dana(id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kegiatan' AND COLUMN_NAME='no_surat_tugas');
SET @sql = IF(@has_col=0, 'ALTER TABLE kegiatan ADD COLUMN no_surat_tugas VARCHAR(100) NULL AFTER nama_kegiatan', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kegiatan' AND COLUMN_NAME='tanggal_surat_tugas');
SET @sql = IF(@has_col=0, 'ALTER TABLE kegiatan ADD COLUMN tanggal_surat_tugas DATE NULL AFTER no_surat_tugas', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kegiatan' AND COLUMN_NAME='tema');
SET @sql = IF(@has_col=0, 'ALTER TABLE kegiatan ADD COLUMN tema VARCHAR(255) NULL AFTER tanggal_surat_tugas', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col = (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='kegiatan_peserta' AND COLUMN_NAME='sumber_dana_id');
SET @sql = IF(@has_col=0, 'ALTER TABLE kegiatan_peserta ADD COLUMN sumber_dana_id INT UNSIGNED NULL AFTER penerima_id', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @has_fk = (SELECT COUNT(*) FROM information_schema.KEY_COLUMN_USAGE WHERE CONSTRAINT_SCHEMA=DATABASE() AND TABLE_NAME='kegiatan_peserta' AND COLUMN_NAME='sumber_dana_id' AND REFERENCED_TABLE_NAME='sumber_dana');
SET @sql = IF(@has_fk=0, 'ALTER TABLE kegiatan_peserta ADD CONSTRAINT fk_kegiatan_peserta_sumber_dana FOREIGN KEY (sumber_dana_id) REFERENCES sumber_dana(id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE OR REPLACE VIEW v_detail_biaya AS
SELECT k.id kegiatan_id,k.tahun_anggaran_id,k.nama_kegiatan,k.no_surat_tugas,k.tanggal_surat_tugas,k.tema,
 k.tanggal_input,k.tanggal_mulai,k.tanggal_selesai,k.tempat,k.status,kp.penerima_id,
 p.nama penerima_nama,p.tipe,p.jabatan,kp.sumber_dana_id,sd.kode sumber_dana_kode,sd.nama sumber_dana_nama,
 j.id jenis_biaya_id,j.kode jenis_biaya_kode,j.nama jenis_biaya_nama,kb.volume,kb.tarif,
 (kb.volume*kb.tarif) subtotal
FROM kegiatan k JOIN kegiatan_peserta kp ON kp.kegiatan_id=k.id
JOIN penerima p ON p.id=kp.penerima_id LEFT JOIN sumber_dana sd ON sd.id=kp.sumber_dana_id
JOIN kegiatan_biaya kb ON kb.kegiatan_peserta_id=kp.id JOIN jenis_biaya j ON j.id=kb.jenis_biaya_id;

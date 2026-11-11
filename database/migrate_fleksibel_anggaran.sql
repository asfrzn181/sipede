-- Migrasi struktur anggaran fleksibel untuk database SIPEDE yang sudah berjalan.
-- Jalankan sekali setelah memilih database tujuan di phpMyAdmin/cPanel.

SET @db_name = DATABASE();

CREATE TABLE IF NOT EXISTS `jenis_kegiatan` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(40) NOT NULL UNIQUE,
  `nama` VARCHAR(120) NOT NULL,
  `subjenis_json` JSON NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `urutan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `jenis_anggaran` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kode` VARCHAR(40) NOT NULL UNIQUE,
  `nama` VARCHAR(120) NOT NULL,
  `keterangan` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `urutan` SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO jenis_kegiatan (kode, nama, subjenis_json, urutan) VALUES
('PERJALANAN_DINAS', 'Perjalanan Dinas', JSON_ARRAY(), 1),
('RAPAT', 'Rapat', JSON_ARRAY('Rapat Paripurna', 'Rapat Komisi', 'Rapat Banggar', 'Rapat Bamus', 'Rapat Khusus'), 2),
('FASILITAS', 'Fasilitas', JSON_ARRAY(), 3),
('LAINNYA', 'Kegiatan Lainnya', JSON_ARRAY(), 99)
ON DUPLICATE KEY UPDATE nama = VALUES(nama), subjenis_json = VALUES(subjenis_json), is_active = 1;

INSERT INTO jenis_anggaran (kode, nama, keterangan, urutan) VALUES
('FASILITAS', 'Fasilitas DPRD', 'Pagu untuk fasilitas dan kebutuhan pendukung DPRD.', 1),
('RAPAT', 'Rapat', 'Pagu untuk rapat paripurna, komisi, Banggar, Bamus, dan rapat khusus.', 2),
('PERJALANAN_DINAS', 'Perjalanan Dinas', 'Pagu perjalanan dinas anggota DPRD dan pendamping.', 3),
('HONORARIUM', 'Honorarium / Notulen', 'Pagu honorarium, notulen, dan tenaga pendukung.', 4),
('LAINNYA', 'Anggaran Lainnya', 'Kelompok anggaran tambahan sesuai kebutuhan.', 99)
ON DUPLICATE KEY UPDATE nama = VALUES(nama), keterangan = VALUES(keterangan), is_active = 1;

SET @sql = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE pos_anggaran ADD COLUMN jenis_anggaran_id INT UNSIGNED NULL AFTER penerima_id, ADD KEY idx_pos_jenis_anggaran (jenis_anggaran_id)',
  'SELECT 1') FROM information_schema.columns WHERE table_schema = @db_name AND table_name = 'pos_anggaran' AND column_name = 'jenis_anggaran_id');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE kegiatan ADD COLUMN jenis_kegiatan_id INT UNSIGNED NULL AFTER nama_kegiatan, ADD COLUMN subjenis_kegiatan VARCHAR(120) NULL AFTER jenis_kegiatan_id, ADD KEY idx_kegiatan_jenis (jenis_kegiatan_id)',
  'SELECT 1') FROM information_schema.columns WHERE table_schema = @db_name AND table_name = 'kegiatan' AND column_name = 'jenis_kegiatan_id');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `kegiatan_anggaran` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `kegiatan_id` INT UNSIGNED NOT NULL,
  `pos_anggaran_id` INT UNSIGNED NOT NULL,
  `nilai_alokasi` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `keterangan` TEXT NULL,
  `status` ENUM('DRAFT','DISETUJUI','DITOLAK') NOT NULL DEFAULT 'DRAFT',
  `disetujui_oleh` INT UNSIGNED NULL,
  `disetujui_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kegiatan_pos` (`kegiatan_id`, `pos_anggaran_id`),
  KEY `idx_ka_pos` (`pos_anggaran_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @sql = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE kegiatan_peserta ADD COLUMN kegiatan_anggaran_id INT UNSIGNED NULL AFTER id, ADD KEY idx_kp_kegiatan_anggaran (kegiatan_anggaran_id)',
  'SELECT 1') FROM information_schema.columns WHERE table_schema = @db_name AND table_name = 'kegiatan_peserta' AND column_name = 'kegiatan_anggaran_id');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(COUNT(*) = 0,
  'ALTER TABLE kegiatan_biaya ADD COLUMN kegiatan_anggaran_id INT UNSIGNED NULL AFTER kegiatan_peserta_id, ADD KEY idx_kb_kegiatan_anggaran (kegiatan_anggaran_id)',
  'SELECT 1') FROM information_schema.columns WHERE table_schema = @db_name AND table_name = 'kegiatan_biaya' AND column_name = 'kegiatan_anggaran_id');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

ALTER TABLE kegiatan_biaya MODIFY COLUMN kegiatan_peserta_id INT UNSIGNED NULL;

UPDATE kegiatan k
JOIN jenis_kegiatan jk ON jk.kode = 'PERJALANAN_DINAS'
SET k.jenis_kegiatan_id = jk.id
WHERE k.jenis_kegiatan_id IS NULL;

UPDATE pos_anggaran pa
JOIN jenis_anggaran ja ON ja.kode = 'PERJALANAN_DINAS'
SET pa.jenis_anggaran_id = ja.id
WHERE pa.jenis_anggaran_id IS NULL;

INSERT INTO kegiatan_anggaran (kegiatan_id, pos_anggaran_id, nilai_alokasi, keterangan)
SELECT kp.kegiatan_id, kp.pos_anggaran_id,
       GREATEST(
         COALESCE((SELECT SUM(kp2.dana_awal)
                   FROM kegiatan_peserta kp2
                   WHERE kp2.kegiatan_id = kp.kegiatan_id
                     AND kp2.pos_anggaran_id = kp.pos_anggaran_id), 0),
         COALESCE((SELECT SUM(kb2.volume * kb2.tarif)
                   FROM kegiatan_peserta kp2
                   JOIN kegiatan_biaya kb2 ON kb2.kegiatan_peserta_id = kp2.id
                   WHERE kp2.kegiatan_id = kp.kegiatan_id
                     AND kp2.pos_anggaran_id = kp.pos_anggaran_id), 0)
       ),
       'Migrasi otomatis dari pembebanan peserta lama'
FROM kegiatan_peserta kp
WHERE kp.pos_anggaran_id IS NOT NULL
GROUP BY kp.kegiatan_id, kp.pos_anggaran_id
ON DUPLICATE KEY UPDATE nilai_alokasi = GREATEST(nilai_alokasi, VALUES(nilai_alokasi));

UPDATE kegiatan_peserta kp
JOIN kegiatan_anggaran ka ON ka.kegiatan_id = kp.kegiatan_id AND ka.pos_anggaran_id = kp.pos_anggaran_id
SET kp.kegiatan_anggaran_id = ka.id
WHERE kp.pos_anggaran_id IS NOT NULL;

UPDATE kegiatan_biaya kb
JOIN kegiatan_peserta kp ON kp.id = kb.kegiatan_peserta_id
SET kb.kegiatan_anggaran_id = kp.kegiatan_anggaran_id
WHERE kb.kegiatan_anggaran_id IS NULL AND kp.kegiatan_anggaran_id IS NOT NULL;

CREATE OR REPLACE VIEW v_sisa_pos AS
SELECT pa.id AS pos_anggaran_id, pa.tahun_anggaran_id, pa.kode, pa.nama AS pos_nama,
       pa.tipe, pa.penerima_id, ja.nama AS jenis_anggaran_nama, pa.pagu, pa.kelompok_jabatan,
       COALESCE(r.realisasi, 0) AS realisasi,
       pa.pagu - COALESCE(r.realisasi, 0) AS sisa
FROM pos_anggaran pa
LEFT JOIN jenis_anggaran ja ON ja.id = pa.jenis_anggaran_id
LEFT JOIN (
  SELECT x.pos_anggaran_id, SUM(x.nilai) AS realisasi
  FROM (
    SELECT ka.pos_anggaran_id, kb.volume * kb.tarif AS nilai
    FROM kegiatan_anggaran ka
    JOIN kegiatan_biaya kb ON kb.kegiatan_anggaran_id = ka.id
    JOIN kegiatan k ON k.id = ka.kegiatan_id AND k.status = 'AKTIF'
    UNION ALL
    SELECT kp.pos_anggaran_id, kb.volume * kb.tarif AS nilai
    FROM kegiatan_peserta kp
    JOIN kegiatan_biaya kb ON kb.kegiatan_peserta_id = kp.id AND kb.kegiatan_anggaran_id IS NULL
    JOIN kegiatan k ON k.id = kp.kegiatan_id AND k.status = 'AKTIF'
    WHERE kp.pos_anggaran_id IS NOT NULL
  ) x GROUP BY x.pos_anggaran_id
) r ON r.pos_anggaran_id = pa.id;

CREATE OR REPLACE VIEW v_detail_biaya AS
SELECT k.id AS kegiatan_id, k.tahun_anggaran_id, k.nama_kegiatan, k.no_surat_tugas,
       k.tanggal_surat_tugas, k.tema, k.tanggal_input, k.tanggal_mulai, k.tanggal_selesai,
       k.tempat, k.status, kp.penerima_id, p.nama AS penerima_nama, p.tipe, p.jabatan,
       COALESCE(ka.pos_anggaran_id, kp.pos_anggaran_id) AS pos_anggaran_id,
       COALESCE(pa.nama, sd.nama, 'APBD') AS sumber_dana_nama,
       kp.sumber_dana_id, sd.kode AS sumber_dana_kode,
       j.id AS jenis_biaya_id, j.kode AS jenis_biaya_kode, j.nama AS jenis_biaya_nama,
       kb.volume, kb.tarif, kb.volume * kb.tarif AS subtotal
FROM kegiatan k
JOIN kegiatan_biaya kb ON kb.kegiatan_peserta_id IS NOT NULL
JOIN kegiatan_peserta kp ON kp.id = kb.kegiatan_peserta_id AND kp.kegiatan_id = k.id
JOIN penerima p ON p.id = kp.penerima_id
LEFT JOIN kegiatan_anggaran ka ON ka.id = kb.kegiatan_anggaran_id
LEFT JOIN pos_anggaran pa ON pa.id = COALESCE(ka.pos_anggaran_id, kp.pos_anggaran_id)
LEFT JOIN sumber_dana sd ON sd.id = kp.sumber_dana_id
JOIN jenis_biaya j ON j.id = kb.jenis_biaya_id
UNION ALL
SELECT k.id, k.tahun_anggaran_id, k.nama_kegiatan, k.no_surat_tugas,
       k.tanggal_surat_tugas, k.tema, k.tanggal_input, k.tanggal_mulai, k.tanggal_selesai,
       k.tempat, k.status, NULL, 'Kegiatan Umum', NULL, NULL, ka.pos_anggaran_id,
       pa.nama, NULL, NULL, j.id, j.kode, j.nama, kb.volume, kb.tarif, kb.volume * kb.tarif
FROM kegiatan k
JOIN kegiatan_anggaran ka ON ka.kegiatan_id = k.id
JOIN kegiatan_biaya kb ON kb.kegiatan_anggaran_id = ka.id AND kb.kegiatan_peserta_id IS NULL
JOIN pos_anggaran pa ON pa.id = ka.pos_anggaran_id
JOIN jenis_biaya j ON j.id = kb.jenis_biaya_id;

SELECT 'Struktur anggaran fleksibel siap digunakan.' AS hasil;

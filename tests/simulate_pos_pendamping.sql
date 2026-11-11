-- Simulasi pemeriksaan Pos Pendamping SIPEDE
--
-- Tujuan:
-- 1. Memastikan pos inject yang diharapkan tersedia pada 2024-2026.
-- 2. Menemukan pos template lama yang menyebabkan tampilan ganda.
-- 3. Mensimulasikan penonaktifan pos lama TANPA menyimpan perubahan.
--
-- Skrip ini read-only terhadap data permanen. Bagian simulasi memakai
-- START TRANSACTION lalu ROLLBACK.

SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

SET @tahun_mulai = 2024;
SET @tahun_akhir = 2026;

DROP TEMPORARY TABLE IF EXISTS tmp_expected_pos_pendamping;
CREATE TEMPORARY TABLE tmp_expected_pos_pendamping (
  kode VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY,
  nama VARCHAR(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_expected_pos_pendamping (kode, nama) VALUES
('POS-AJUDAN-KTU', 'Ajudan Ketua DPRD'),
('POS-AJUDAN-WK1', 'Ajudan Wakil Ketua I'),
('POS-AJUDAN-WK2', 'Ajudan Wakil Ketua II'),
('POS-NOTULEN-KOM1', 'Notulen Komisi I'),
('POS-NOTULEN-KOM2', 'Notulen Komisi II'),
('POS-NOTULEN-KOM3', 'Notulen Komisi III'),
('POS-NOTULEN-SEK', 'Notulen Sekretariat'),
('POS-SEKRETARIAT', 'Sekretariat');

DROP TEMPORARY TABLE IF EXISTS tmp_legacy_pos_pendamping;
CREATE TEMPORARY TABLE tmp_legacy_pos_pendamping (
  kode VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO tmp_legacy_pos_pendamping (kode) VALUES
('POS-AJUDAN-KTU-01'),
('POS-AJUDAN-KTU-02'),
('POS-AJUDAN-WK1-01'),
('POS-AJUDAN-WK1-02'),
('POS-AJUDAN-WK2-01'),
('POS-AJUDAN-WK2-02'),
('POS-NOTULEN-KOM1-01'),
('POS-NOTULEN-KOM1-02'),
('POS-NOTULEN-KOM2-01'),
('POS-NOTULEN-KOM2-02'),
('POS-NOTULEN-KOM3-01'),
('POS-NOTULEN-KOM3-02'),
('POS-NOTULEN-SEK-01'),
('POS-SEKRETARIAT-01'),
('POS-AJUDAN-WK3'),
('POS-SEKRETARIS'),
('POS-KABAG'),
('POS-KASUBAG'),
('POS-STAF-ADM');

-- 1) Ringkasan jumlah pos per tahun.
SELECT
  ta.tahun,
  COUNT(pa.id) AS total_pos_pendamping,
  SUM(CASE WHEN ep.kode IS NOT NULL THEN 1 ELSE 0 END) AS pos_inject,
  SUM(CASE WHEN lp.kode IS NOT NULL THEN 1 ELSE 0 END) AS pos_template_lama,
  CASE
    WHEN SUM(CASE WHEN ep.kode IS NOT NULL THEN 1 ELSE 0 END) = 8
     AND SUM(CASE WHEN lp.kode IS NOT NULL THEN 1 ELSE 0 END) = 0
    THEN 'OK'
    WHEN SUM(CASE WHEN ep.kode IS NOT NULL THEN 1 ELSE 0 END) = 8
     AND SUM(CASE WHEN lp.kode IS NOT NULL THEN 1 ELSE 0 END) > 0
    THEN 'ADA TEMPLATE LAMA'
    ELSE 'DATA INJECT BELUM LENGKAP'
  END AS status_simulasi
FROM tahun_anggaran ta
LEFT JOIN pos_anggaran pa
  ON pa.tahun_anggaran_id = ta.id
 AND pa.tipe = 'PENDAMPING'
 AND pa.is_active = 1
LEFT JOIN tmp_expected_pos_pendamping ep
  ON ep.kode COLLATE utf8mb4_unicode_ci = pa.kode COLLATE utf8mb4_unicode_ci
LEFT JOIN tmp_legacy_pos_pendamping lp
  ON lp.kode COLLATE utf8mb4_unicode_ci = pa.kode COLLATE utf8mb4_unicode_ci
WHERE ta.tahun BETWEEN @tahun_mulai AND @tahun_akhir
GROUP BY ta.id, ta.tahun
ORDER BY ta.tahun;

-- 2) Detail pos yang tampil ganda karena nama sama tetapi kode berbeda.
SELECT
  ta.tahun,
  pa.nama,
  COUNT(*) AS jumlah_baris,
  GROUP_CONCAT(pa.kode ORDER BY pa.kode SEPARATOR ', ') AS kode_pos
FROM tahun_anggaran ta
JOIN pos_anggaran pa ON pa.tahun_anggaran_id = ta.id
WHERE ta.tahun BETWEEN @tahun_mulai AND @tahun_akhir
  AND pa.tipe = 'PENDAMPING'
  AND pa.is_active = 1
GROUP BY ta.tahun, pa.nama
HAVING COUNT(*) > 1
ORDER BY ta.tahun, pa.nama;

-- 3) Pos template lama dan apakah sudah dipakai transaksi.
SELECT
  ta.tahun,
  pa.id,
  pa.kode,
  pa.nama,
  pa.is_active,
  COUNT(kp.id) AS jumlah_pemakaian
FROM tahun_anggaran ta
JOIN pos_anggaran pa ON pa.tahun_anggaran_id = ta.id
JOIN tmp_legacy_pos_pendamping lp
  ON lp.kode COLLATE utf8mb4_unicode_ci = pa.kode COLLATE utf8mb4_unicode_ci
LEFT JOIN kegiatan_peserta kp ON kp.pos_anggaran_id = pa.id
WHERE ta.tahun BETWEEN @tahun_mulai AND @tahun_akhir
GROUP BY ta.tahun, pa.id, pa.kode, pa.nama, pa.is_active
ORDER BY ta.tahun, pa.urutan, pa.kode;

-- 4) Simulasi cleanup. Tidak ada perubahan permanen karena diakhiri ROLLBACK.
START TRANSACTION;

UPDATE pos_anggaran pa
JOIN tahun_anggaran ta ON ta.id = pa.tahun_anggaran_id
JOIN tmp_legacy_pos_pendamping lp
  ON lp.kode COLLATE utf8mb4_unicode_ci = pa.kode COLLATE utf8mb4_unicode_ci
SET pa.is_active = 0,
    pa.updated_at = CURRENT_TIMESTAMP
WHERE ta.tahun BETWEEN @tahun_mulai AND @tahun_akhir
  AND pa.tipe = 'PENDAMPING'
  AND pa.is_active = 1
  AND NOT EXISTS (
    SELECT 1
    FROM kegiatan_peserta kp
    WHERE kp.pos_anggaran_id = pa.id
  );

SELECT ROW_COUNT() AS jumlah_pos_lama_yang_akan_dinonaktifkan;

SELECT
  ta.tahun,
  pa.kode,
  pa.nama,
  pa.is_active AS status_setelah_simulasi
FROM tahun_anggaran ta
JOIN pos_anggaran pa ON pa.tahun_anggaran_id = ta.id
JOIN tmp_legacy_pos_pendamping lp
  ON lp.kode COLLATE utf8mb4_unicode_ci = pa.kode COLLATE utf8mb4_unicode_ci
WHERE ta.tahun BETWEEN @tahun_mulai AND @tahun_akhir
ORDER BY ta.tahun, pa.kode;

ROLLBACK;

SELECT 'SIMULASI SELESAI - DATA PERMANEN TIDAK DIUBAH' AS hasil;

DROP TEMPORARY TABLE IF EXISTS tmp_legacy_pos_pendamping;
DROP TEMPORARY TABLE IF EXISTS tmp_expected_pos_pendamping;

-- Migration: 2026-11-10 — Penerima periode + tipe DPRD + merge Tahun-Pagu
-- Jalankan sekali pada database anggaran_dprd

-- 1. Tambah kolom periode ke tabel penerima
ALTER TABLE penerima
  ADD COLUMN IF NOT EXISTS periode_mulai SMALLINT UNSIGNED NULL AFTER jabatan,
  ADD COLUMN IF NOT EXISTS periode_akhir SMALLINT UNSIGNED NULL AFTER periode_mulai;

-- 2. Migrasi nilai tipe ANGGOTA_DPRD → DPRD
UPDATE penerima SET tipe = 'DPRD' WHERE tipe = 'ANGGOTA_DPRD';


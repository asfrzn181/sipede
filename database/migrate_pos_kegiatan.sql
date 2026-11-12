-- Tambahkan tipe pos KEGIATAN untuk pagu khusus Rapat/Fasilitas/Lainnya.
-- Jalankan sekali pada database lama jika tidak menggunakan schema.sql fresh.

ALTER TABLE pos_anggaran
  MODIFY COLUMN tipe ENUM('DPRD', 'PENDAMPING', 'KEGIATAN') NOT NULL;

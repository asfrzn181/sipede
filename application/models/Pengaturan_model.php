<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan_model extends CI_Model
{
    private string $table = 'pengaturan_dokumen';

    public function __construct()
    {
        parent::__construct();
        $this->ensure_schema();
    }

    /**
     * Memastikan skema tabel pengaturan_dokumen tersedia di database
     */
    public function ensure_schema(): void
    {
        if (!$this->db->table_exists($this->table)) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `{$this->table}` (
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

                    -- Penandatangan 1: Pengguna Anggaran (Sekretaris DPRD)
                    `pa_label` VARCHAR(100) NOT NULL DEFAULT 'Mengetahui,\nPengguna Anggaran',
                    `pa_jabatan` VARCHAR(150) NOT NULL DEFAULT 'Sekretaris DPRD',
                    `pa_nama` VARCHAR(150) NOT NULL DEFAULT 'Drs. H. AHMAD FAUZI, M.Si',
                    `pa_nip` VARCHAR(50) NOT NULL DEFAULT '19750812 199803 1 002',
                    `pa_pangkat` VARCHAR(100) NULL DEFAULT 'Pembina Utama Muda (IV/c)',

                    -- Penandatangan 2: PPTK
                    `pptk_label` VARCHAR(100) NOT NULL DEFAULT 'Pejabat Pelaksana Teknis Kegiatan',
                    `pptk_jabatan` VARCHAR(150) NOT NULL DEFAULT 'PPTK Perjalanan Dinas',
                    `pptk_nama` VARCHAR(150) NOT NULL DEFAULT 'BAMBANG SUSANTO, S.E., M.M.',
                    `pptk_nip` VARCHAR(50) NOT NULL DEFAULT '19820415 200501 1 008',
                    `pptk_pangkat` VARCHAR(100) NULL DEFAULT 'Penata Tk. I (III/d)',

                    -- Penandatangan 3: Bendahara Pengeluaran
                    `bendahara_label` VARCHAR(100) NOT NULL DEFAULT 'Bendahara Pengeluaran',
                    `bendahara_jabatan` VARCHAR(150) NOT NULL DEFAULT 'Bendahara Pengeluaran',
                    `bendahara_nama` VARCHAR(150) NOT NULL DEFAULT 'SITI NURHALIZA, S.Ak.',
                    `bendahara_nip` VARCHAR(50) NOT NULL DEFAULT '19881120 201101 2 004',
                    `bendahara_pangkat` VARCHAR(100) NULL DEFAULT 'Penata Muda Tk. I (III/b)',

                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }

        // Pastikan row default dengan ID 1 tersedia
        $count = (int)$this->db->count_all($this->table);
        if ($count === 0) {
            $this->db->insert($this->table, array(
                'id'                => 1,
                'nama_pemda'        => 'PEMERINTAH DAERAH PROVINSI / KABUPATEN',
                'nama_lembaga'      => 'DEWAN PERWAKILAN RAKYAT DAERAH',
                'nama_skpd'         => 'SEKRETARIAT DPRD',
                'alamat'            => 'Jalan Protokol Pemerintahan No. 01, Kompleks Perkantoran Pemerintah Daerah',
                'telepon'           => '(021) 1234567',
                'fax'               => '(021) 1234568',
                'email'             => 'setwan@daerah.go.id',
                'website'           => 'dprd.daerah.go.id',
                'kode_pos'          => '12345',
                'logo_path'         => 'assets/img/logo_default.svg',
                'kota_penetapan'    => 'Ibukota',

                'pa_label'          => "Mengetahui,\nPengguna Anggaran",
                'pa_jabatan'        => 'Sekretaris DPRD',
                'pa_nama'           => 'Drs. H. AHMAD FAUZI, M.Si',
                'pa_nip'            => '19750812 199803 1 002',
                'pa_pangkat'        => 'Pembina Utama Muda (IV/c)',

                'pptk_label'        => 'Pejabat Pelaksana Teknis Kegiatan',
                'pptk_jabatan'      => 'PPTK Perjalanan Dinas',
                'pptk_nama'         => 'BAMBANG SUSANTO, S.E., M.M.',
                'pptk_nip'          => '19820415 200501 1 008',
                'pptk_pangkat'      => 'Penata Tk. I (III/d)',

                'bendahara_label'   => 'Bendahara Pengeluaran',
                'bendahara_jabatan' => 'Bendahara Pengeluaran',
                'bendahara_nama'    => 'SITI NURHALIZA, S.Ak.',
                'bendahara_nip'     => '19881120 201101 2 004',
                'bendahara_pangkat' => 'Penata Muda Tk. I (III/b)',
            ));
        }
    }

    /**
     * Mengambil konfigurasi aktif KOP dan Penandatangan
     */
    public function get(): array
    {
        $row = $this->db->get_where($this->table, array('id' => 1))->row_array();
        if (!$row) {
            $this->ensure_schema();
            $row = $this->db->get_where($this->table, array('id' => 1))->row_array() ?: array();
        }

        // Fallback logo jika belum diset atau berkas tidak ditemukan
        if (empty($row['logo_path']) || (!file_exists(FCPATH . $row['logo_path']) && !file_exists($row['logo_path']))) {
            $row['logo_path'] = 'assets/img/logo_default.svg';
        }

        return $row;
    }

    /**
     * Memperbarui konfigurasi KOP dan Penandatangan
     */
    public function save(array $data): bool
    {
        $current = $this->get();
        if (!empty($current['id'])) {
            return (bool)$this->db->where('id', $current['id'])->update($this->table, $data);
        } else {
            $data['id'] = 1;
            return (bool)$this->db->insert($this->table, $data);
        }
    }
}


<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pos_model - Model Pos Anggaran
 *
 * Mengelola "Pos Anggaran" – unit pemilik pagu, dipisah dari orang.
 * - DPRD    : 1 penerima = 1 pos (penerima_id tetap, tidak bisa dialihkan)
 * - PENDAMPING: pos jabatan baku (8 pos), satu pos dapat memiliki beberapa pemegang via penugasan_pos
 */
class Pos_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->ensure_schema();
    }

    /**
     * Memastikan tabel, kolom, dan view untuk Pos Anggaran sudah siap secara otomatis
     */
    public function ensure_schema(): void {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        if (!$this->db->table_exists('pos_anggaran')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS pos_anggaran (
                    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    tahun_anggaran_id INT UNSIGNED NOT NULL,
                    kode              VARCHAR(50)  NOT NULL,
                    nama              VARCHAR(200) NOT NULL,
                    tipe              ENUM('DPRD','PENDAMPING') NOT NULL,
                    penerima_id       INT UNSIGNED NULL,
                    pagu              DECIMAL(18,2) NOT NULL DEFAULT 0,
                    kelompok_jabatan  ENUM('KETUA','WAKIL_KETUA','ANGGOTA') NULL,
                    urutan            SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                    is_active         TINYINT(1) NOT NULL DEFAULT 1,
                    created_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    updated_at        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    UNIQUE KEY uq_pos_tahun_kode (tahun_anggaran_id, kode),
                    KEY idx_tahun (tahun_anggaran_id),
                    KEY idx_penerima (penerima_id),
                    KEY idx_tipe (tipe)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if (!$this->db->table_exists('penugasan_pos')) {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS penugasan_pos (
                    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    pos_anggaran_id INT UNSIGNED NOT NULL,
                    penerima_id     INT UNSIGNED NOT NULL,
                    tanggal_mulai   DATE NOT NULL,
                    tanggal_selesai DATE NULL,
                    keterangan      TEXT NULL,
                    dibuat_oleh     INT UNSIGNED NULL,
                    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_pos (pos_anggaran_id),
                    KEY idx_penerima (penerima_id),
                    KEY idx_aktif (pos_anggaran_id, tanggal_selesai)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");
        }

        if ($this->db->table_exists('kegiatan_peserta')) {
            $fields = $this->db->list_fields('kegiatan_peserta');
            if (!in_array('pos_anggaran_id', $fields, true)) {
                $this->db->query("ALTER TABLE kegiatan_peserta ADD COLUMN pos_anggaran_id INT UNSIGNED NULL AFTER sumber_dana_id, ADD COLUMN alasan_pengalihan TEXT NULL AFTER pos_anggaran_id, ADD KEY idx_pos_anggaran (pos_anggaran_id);");
            }
        }

        // View v_sisa_pos (menghitung sisa per pos anggaran)
        $this->db->query("
            CREATE OR REPLACE VIEW v_sisa_pos AS
            SELECT
                pa.id              AS pos_anggaran_id,
                pa.tahun_anggaran_id,
                pa.kode,
                pa.nama            AS pos_nama,
                pa.tipe,
                pa.penerima_id,
                pa.pagu,
                pa.kelompok_jabatan,
                COALESCE(SUM(CASE WHEN k.status = 'AKTIF' THEN kb.volume * kb.tarif ELSE 0 END), 0) AS realisasi,
                pa.pagu - COALESCE(SUM(CASE WHEN k.status = 'AKTIF' THEN kb.volume * kb.tarif ELSE 0 END), 0) AS sisa
            FROM pos_anggaran pa
            LEFT JOIN kegiatan_peserta kp ON kp.pos_anggaran_id = pa.id
            LEFT JOIN kegiatan_biaya kb   ON kb.kegiatan_peserta_id = kp.id
            LEFT JOIN kegiatan k          ON k.id = kp.kegiatan_id
                                          AND k.status = 'AKTIF'
            GROUP BY pa.id;
        ");
    }

    // ─── Pos Anggaran ───────────────────────────────────────────────

    /**
     * Semua pos anggaran untuk satu tahun, dilengkapi saldo realisasi & sisa
     */
    public function all_by_tahun(int $tahunId, ?string $tipe = null): array {
        $this->db
            ->select('sp.*, ta.tahun,
                      COALESCE(GROUP_CONCAT(DISTINCT pen.nama ORDER BY pen.nama SEPARATOR ", "), "-") AS pemegang_nama,
                      COALESCE(GROUP_CONCAT(DISTINCT pen.jabatan ORDER BY pen.jabatan SEPARATOR ", "), "-") AS pemegang_jabatan,
                      MIN(pp.id) AS penugasan_id,
                      MIN(pp.penerima_id) AS penugasan_penerima_id,
                      MIN(pp.tanggal_mulai) AS penugasan_mulai', FALSE)
            ->from('pos_anggaran sp')
            ->join('tahun_anggaran ta', 'ta.id = sp.tahun_anggaran_id')
            // Untuk DPRD: langsung join penerima via penerima_id
            // Untuk PENDAMPING: join via penugasan aktif
            ->join('penugasan_pos pp', "pp.pos_anggaran_id = sp.id AND sp.tipe = 'PENDAMPING' AND pp.tanggal_selesai IS NULL", 'left')
            ->join('penerima pen',
                "(pen.id = sp.penerima_id AND sp.tipe = 'DPRD') OR (pen.id = pp.penerima_id AND sp.tipe = 'PENDAMPING')",
                'left')
            ->where('sp.tahun_anggaran_id', $tahunId)
            ->where('sp.is_active', 1);

        if ($tipe) $this->db->where('sp.tipe', $tipe);

        return $this->db
            ->group_by('sp.id')
            ->order_by('sp.tipe', 'ASC')
            ->order_by('sp.urutan', 'ASC')
            ->order_by('sp.nama', 'ASC')
            ->get()->result_array();
    }

    /**
     * Ambil pos beserta saldo dari view v_sisa_pos
     */
    public function saldo_by_tahun(int $tahunId, ?string $tipe = null): array {
        $this->db
            ->from('v_sisa_pos vsp')
            ->join('pos_anggaran pa', 'pa.id = vsp.pos_anggaran_id')
            ->where('vsp.tahun_anggaran_id', $tahunId)
            ->where('pa.is_active', 1);

        if ($tipe) $this->db->where('vsp.tipe', $tipe);

        return $this->db
            ->order_by('vsp.tipe')
            ->order_by('pa.urutan')
            ->get()->result_array();
    }

    /**
     * Cari satu pos berdasarkan ID
     */
    public function find(int $id): ?array {
        return $this->db->get_where('pos_anggaran', ['id' => $id])->row_array() ?: null;
    }

    /**
     * Simpan / update pos anggaran
     */
    public function save(array $data, ?int $id = null): int {
        $data['updated_at'] = date('Y-m-d H:i:s');
        if ($id) {
            $this->db->where('id', $id)->update('pos_anggaran', $data);
            return $id;
        }
        $data['created_at'] = date('Y-m-d H:i:s');
        $this->db->insert('pos_anggaran', $data);
        return (int)$this->db->insert_id();
    }

    /**
     * Update pagu satu pos (untuk revisi anggaran)
     */
    public function update_pagu(int $posId, float $pagu): bool {
        return (bool)$this->db->where('id', $posId)->update('pos_anggaran', [
            'pagu'       => $pagu,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Buat pos DPRD otomatis dari daftar penerima DPRD untuk satu tahun.
     * Idempotent – aman dijalankan berulang.
     */
    public function buat_pos_dprd_otomatis(int $tahunId): int {
        $penerimaList = $this->db
            ->where('tipe', 'DPRD')
            ->where('is_active', 1)
            ->order_by('nama')
            ->get('penerima')->result_array();

        $created = 0;
        foreach ($penerimaList as $i => $p) {
            $kode = 'DPRD-' . str_pad($p['id'], 4, '0', STR_PAD_LEFT);
            $existing = $this->db->get_where('pos_anggaran', [
                'tahun_anggaran_id' => $tahunId,
                'kode'              => $kode,
            ])->row_array();

            if (!$existing) {
                $jabatan   = strtolower($p['jabatan'] ?? '');
                $kelompok  = strpos($jabatan, 'wakil ketua') !== false ? 'WAKIL_KETUA'
                           : (strpos($jabatan, 'ketua') !== false ? 'KETUA' : 'ANGGOTA');

                $this->db->insert('pos_anggaran', [
                    'tahun_anggaran_id' => $tahunId,
                    'kode'              => $kode,
                    'nama'              => $p['nama'],
                    'tipe'              => 'DPRD',
                    'penerima_id'       => (int)$p['id'],
                    'pagu'              => 0,
                    'kelompok_jabatan'  => $kelompok,
                    'urutan'            => (int)$p['id'],
                    'is_active'         => 1,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ]);
                $created++;
            }
        }
        return $created;
    }

    /**
     * Buat 8 pos baku PENDAMPING untuk satu tahun.
     * Satu pos mewakili satu kelompok jabatan dan dapat memiliki beberapa
     * pemegang aktif dengan satu pagu bersama.
     * Idempotent – aman dijalankan berulang.
     */
    public function buat_pos_pendamping_baku(int $tahunId): int {
        $posBaku = [
            ['kode' => 'POS-AJUDAN-KTU',    'nama' => 'Ajudan Ketua DPRD',     'urutan' => 1],
            ['kode' => 'POS-AJUDAN-WK1',    'nama' => 'Ajudan Wakil Ketua I',  'urutan' => 2],
            ['kode' => 'POS-AJUDAN-WK2',    'nama' => 'Ajudan Wakil Ketua II', 'urutan' => 3],
            ['kode' => 'POS-NOTULEN-KOM1',  'nama' => 'Notulen Komisi I',      'urutan' => 4],
            ['kode' => 'POS-NOTULEN-KOM2',  'nama' => 'Notulen Komisi II',     'urutan' => 5],
            ['kode' => 'POS-NOTULEN-KOM3',  'nama' => 'Notulen Komisi III',    'urutan' => 6],
            ['kode' => 'POS-NOTULEN-SEK',   'nama' => 'Notulen Sekretariat',   'urutan' => 7],
            ['kode' => 'POS-SEKRETARIAT',   'nama' => 'Sekretariat',            'urutan' => 8],
        ];

        $created = 0;
        foreach ($posBaku as $pos) {
            $existing = $this->db->get_where('pos_anggaran', [
                'tahun_anggaran_id' => $tahunId,
                'kode'              => $pos['kode'],
            ])->row_array();

            if (!$existing) {
                $this->db->insert('pos_anggaran', [
                    'tahun_anggaran_id' => $tahunId,
                    'kode'              => $pos['kode'],
                    'nama'              => $pos['nama'],
                    'tipe'              => 'PENDAMPING',
                    'penerima_id'       => null,
                    'pagu'              => 0,
                    'kelompok_jabatan'  => null,
                    'urutan'            => $pos['urutan'],
                    'is_active'         => 1,
                    'created_at'        => date('Y-m-d H:i:s'),
                    'updated_at'        => date('Y-m-d H:i:s'),
                ]);
                $created++;
            }
        }
        return $created;
    }

    /**
     * Terapkan pagu standar per kelompok jabatan DPRD untuk satu tahun.
     * Hanya memperbarui pagu yang masih 0.
     */
    public function terapkan_pagu_standar(int $tahunId, array $standar, bool $overwrite = false): int {
        // $standar = ['KETUA' => 150000000, 'WAKIL_KETUA' => 120000000, 'ANGGOTA' => 80000000]
        $updated = 0;
        $rows = $this->db
            ->where('tahun_anggaran_id', $tahunId)
            ->where('tipe', 'DPRD')
            ->where('is_active', 1)
            ->get('pos_anggaran')->result_array();

        foreach ($rows as $row) {
            $kelompok = $row['kelompok_jabatan'] ?? 'ANGGOTA';
            $paguBaru = (float)($standar[$kelompok] ?? 0);
            if ($paguBaru <= 0) continue;
            if (!$overwrite && (float)$row['pagu'] > 0) continue; // Skip jika sudah ada pagu

            $this->db->where('id', (int)$row['id'])
                     ->update('pos_anggaran', ['pagu' => $paguBaru, 'updated_at' => date('Y-m-d H:i:s')]);
            $updated++;
        }
        return $updated;
    }

    /**
     * Terapkan satu pagu standar ke seluruh pos PENDAMPING pada satu tahun.
     * Hanya memperbarui pagu yang masih 0 kecuali overwrite diaktifkan.
     */
    public function terapkan_pagu_pendamping(int $tahunId, float $pagu, bool $overwrite = false): int {
        if ($pagu <= 0) return 0;

        $this->db
            ->where('tahun_anggaran_id', $tahunId)
            ->where('tipe', 'PENDAMPING')
            ->where('is_active', 1);

        if (!$overwrite) {
            $this->db->where('pagu', 0);
        }

        $rows = $this->db->get('pos_anggaran')->result_array();
        $updated = 0;

        foreach ($rows as $row) {
            $this->db->where('id', (int)$row['id'])
                     ->update('pos_anggaran', [
                         'pagu' => $pagu,
                         'updated_at' => date('Y-m-d H:i:s'),
                     ]);
            $updated++;
        }

        return $updated;
    }

    // ─── Penugasan Pos (hanya PENDAMPING) ───────────────────────────

    /**
     * Penugasan aktif semua pos pendamping satu tahun
     */
    public function penugasan_aktif(int $tahunId): array {
        return $this->db
            ->select('pp.*, pa.kode, pa.nama AS pos_nama, p.nama AS penerima_nama, p.jabatan AS penerima_jabatan')
            ->from('penugasan_pos pp')
            ->join('pos_anggaran pa', 'pa.id = pp.pos_anggaran_id')
            ->join('penerima p', 'p.id = pp.penerima_id')
            ->where('pa.tahun_anggaran_id', $tahunId)
            ->where('pa.tipe', 'PENDAMPING')
            ->where('pp.tanggal_selesai IS NULL', null, false)
            ->order_by('pa.urutan')
            ->get()->result_array();
    }

    /**
     * Riwayat penugasan satu pos
     */
    public function riwayat_penugasan(int $posId): array {
        return $this->db
            ->select('pp.*, p.nama AS penerima_nama, p.jabatan')
            ->from('penugasan_pos pp')
            ->join('penerima p', 'p.id = pp.penerima_id')
            ->where('pp.pos_anggaran_id', $posId)
            ->order_by('pp.tanggal_mulai', 'DESC')
            ->get()->result_array();
    }

    /**
     * Tutup semua penugasan aktif untuk satu pos (sebelum assign baru)
     */
    public function tutup_penugasan(int $posId, string $tanggalSelesai): void {
        $this->db->where('pos_anggaran_id', $posId)
                 ->where('tanggal_selesai IS NULL', null, false)
                 ->update('penugasan_pos', ['tanggal_selesai' => $tanggalSelesai]);
    }

    /**
     * Simpan pemegang baru pada pos pendamping bersama.
     * Pemegang aktif lain tetap dipertahankan karena satu pos dapat
     * digunakan oleh beberapa orang dengan satu pagu bersama.
     */
    public function simpan_penugasan(int $posId, int $penerimaId, string $tanggalMulai, ?string $keterangan = null, ?int $dibuatOleh = null): int {
        $existing = $this->db
            ->where('pos_anggaran_id', $posId)
            ->where('penerima_id', $penerimaId)
            ->where('tanggal_selesai IS NULL', null, false)
            ->order_by('id', 'DESC')
            ->limit(1)
            ->get('penugasan_pos')->row_array();
        if ($existing) return (int)$existing['id'];

        // Tambahkan pemegang baru tanpa menutup pemegang lain.
        $this->db->insert('penugasan_pos', [
            'pos_anggaran_id' => $posId,
            'penerima_id'     => $penerimaId,
            'tanggal_mulai'   => $tanggalMulai,
            'tanggal_selesai' => null,
            'keterangan'      => $keterangan,
            'dibuat_oleh'     => $dibuatOleh,
            'created_at'      => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    /**
     * Cari pos default untuk seorang penerima pada suatu tahun.
     * - DPRD: pos yang penerima_id = $penerimaId
     * - PENDAMPING: pos yang penugasan aktifnya adalah $penerimaId
     */
    public function pos_default_penerima(int $penerimaId, int $tahunId): ?array {
        // Cek tipe penerima
        $penerima = $this->db->get_where('penerima', ['id' => $penerimaId])->row_array();
        if (!$penerima) return null;

        if ($penerima['tipe'] === 'DPRD') {
            return $this->db->get_where('pos_anggaran', [
                'penerima_id'       => $penerimaId,
                'tahun_anggaran_id' => $tahunId,
                'tipe'              => 'DPRD',
                'is_active'         => 1,
            ])->row_array() ?: null;
        }

        // PENDAMPING: cari pos yang saat ini ditugaskan ke orang ini
        $result = $this->db
            ->select('pa.*')
            ->from('pos_anggaran pa')
            ->join('penugasan_pos pp', 'pp.pos_anggaran_id = pa.id')
            ->where('pp.penerima_id', $penerimaId)
            ->where('pa.tahun_anggaran_id', $tahunId)
            ->where('pa.tipe', 'PENDAMPING')
            ->where('pa.is_active', 1)
            ->where('pp.tanggal_selesai IS NULL', null, false)
            ->limit(1)
            ->get()->row_array();

        return $result ?: null;
    }

    /**
     * Dapatkan saldo (pagu & realisasi) satu pos dari view v_sisa_pos
     */
    public function saldo_pos(int $posId): ?array {
        return $this->db->get_where('v_sisa_pos', ['pos_anggaran_id' => $posId])->row_array() ?: null;
    }
}


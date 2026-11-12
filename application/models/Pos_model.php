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
                    pagu_awal         DECIMAL(18,2) NOT NULL DEFAULT 0,
                    pagu_awal_at      DATETIME NULL,
                    pagu_awal_oleh    INT UNSIGNED NULL,
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

        if (!$this->db->table_exists('jenis_kegiatan')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS jenis_kegiatan (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT, kode VARCHAR(40) NOT NULL UNIQUE,
                nama VARCHAR(120) NOT NULL, subjenis_json JSON NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1, urutan SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
        if (!$this->db->table_exists('jenis_anggaran')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS jenis_anggaran (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT, kode VARCHAR(40) NOT NULL UNIQUE,
                nama VARCHAR(120) NOT NULL, keterangan TEXT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1, urutan SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        if (!$this->db->table_exists('kegiatan_anggaran')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS kegiatan_anggaran (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT, kegiatan_id INT UNSIGNED NOT NULL,
                pos_anggaran_id INT UNSIGNED NOT NULL, nilai_alokasi DECIMAL(18,2) NOT NULL DEFAULT 0,
                keterangan TEXT NULL, status ENUM('DRAFT','DISETUJUI','DITOLAK') NOT NULL DEFAULT 'DRAFT',
                disetujui_oleh INT UNSIGNED NULL, disetujui_at DATETIME NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (id), UNIQUE KEY uq_kegiatan_pos (kegiatan_id, pos_anggaran_id), KEY idx_ka_pos (pos_anggaran_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }

        if ($this->db->table_exists('pos_anggaran') && !in_array('jenis_anggaran_id', $this->db->list_fields('pos_anggaran'), true)) {
            $this->db->query('ALTER TABLE pos_anggaran ADD COLUMN jenis_anggaran_id INT UNSIGNED NULL AFTER penerima_id, ADD KEY idx_pos_jenis_anggaran (jenis_anggaran_id)');
        }
        if ($this->db->table_exists('pos_anggaran')) {
            $posFields = $this->db->list_fields('pos_anggaran');
            if (!in_array('pagu_awal', $posFields, true)) {
                $this->db->query('ALTER TABLE pos_anggaran ADD COLUMN pagu_awal DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER pagu');
            }
            if (!in_array('pagu_awal_at', $posFields, true)) {
                $this->db->query('ALTER TABLE pos_anggaran ADD COLUMN pagu_awal_at DATETIME NULL AFTER pagu_awal');
            }
            if (!in_array('pagu_awal_oleh', $posFields, true)) {
                $this->db->query('ALTER TABLE pos_anggaran ADD COLUMN pagu_awal_oleh INT UNSIGNED NULL AFTER pagu_awal_at');
            }
            // Data lama yang sudah memiliki pagu diperlakukan sebagai pagu awal
            // karena riwayat sebelum fitur ini belum tersedia.
            $this->db->query("UPDATE pos_anggaran SET pagu_awal = pagu, pagu_awal_at = COALESCE(pagu_awal_at, updated_at) WHERE pagu > 0 AND pagu_awal = 0");
        }
        if (!$this->db->table_exists('riwayat_pagu_pos')) {
            $this->db->query("CREATE TABLE IF NOT EXISTS riwayat_pagu_pos (
                id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                pos_anggaran_id INT UNSIGNED NOT NULL,
                jenis_perubahan ENUM('AWAL','STANDAR','REVISI','KOREKSI') NOT NULL,
                pagu_sebelum DECIMAL(18,2) NOT NULL DEFAULT 0,
                pagu_sesudah DECIMAL(18,2) NOT NULL DEFAULT 0,
                realisasi_saat_itu DECIMAL(18,2) NOT NULL DEFAULT 0,
                ditandai_pagu_awal TINYINT(1) NOT NULL DEFAULT 0,
                alasan TEXT NULL,
                dibuat_oleh INT UNSIGNED NULL,
                created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (id), KEY idx_riwayat_pagu_pos (pos_anggaran_id), KEY idx_riwayat_pagu_tanggal (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        }
        if ($this->db->table_exists('kegiatan') && !in_array('jenis_kegiatan_id', $this->db->list_fields('kegiatan'), true)) {
            $this->db->query('ALTER TABLE kegiatan ADD COLUMN jenis_kegiatan_id INT UNSIGNED NULL AFTER nama_kegiatan, ADD COLUMN subjenis_kegiatan VARCHAR(120) NULL AFTER jenis_kegiatan_id, ADD KEY idx_kegiatan_jenis (jenis_kegiatan_id)');
        }
        if ($this->db->table_exists('kegiatan_peserta') && !in_array('kegiatan_anggaran_id', $this->db->list_fields('kegiatan_peserta'), true)) {
            $this->db->query('ALTER TABLE kegiatan_peserta ADD COLUMN kegiatan_anggaran_id INT UNSIGNED NULL AFTER id, ADD KEY idx_kp_kegiatan_anggaran (kegiatan_anggaran_id)');
        }
        if ($this->db->table_exists('kegiatan_biaya')) {
            $costFields = $this->db->list_fields('kegiatan_biaya');
            if (!in_array('kegiatan_anggaran_id', $costFields, true)) {
                $this->db->query('ALTER TABLE kegiatan_biaya ADD COLUMN kegiatan_anggaran_id INT UNSIGNED NULL AFTER kegiatan_peserta_id, ADD KEY idx_kb_kegiatan_anggaran (kegiatan_anggaran_id)');
            }
            $this->db->query('ALTER TABLE kegiatan_biaya MODIFY COLUMN kegiatan_peserta_id INT UNSIGNED NULL');
        }

        if ($this->db->table_exists('kegiatan_peserta')) {
            $fields = $this->db->list_fields('kegiatan_peserta');
            if (!in_array('pos_anggaran_id', $fields, true)) {
                $this->db->query("ALTER TABLE kegiatan_peserta ADD COLUMN pos_anggaran_id INT UNSIGNED NULL AFTER sumber_dana_id, ADD COLUMN alasan_pengalihan TEXT NULL AFTER pos_anggaran_id, ADD KEY idx_pos_anggaran (pos_anggaran_id);");
            }
            if (!in_array('dana_awal', $fields, true)) {
                $this->db->query("ALTER TABLE kegiatan_peserta ADD COLUMN dana_awal DECIMAL(18,2) NOT NULL DEFAULT 0 AFTER alasan_pengalihan;");
            }
        }

        // View v_sisa_pos menghitung biaya dari alokasi kegiatan dan data lama.
        $this->db->query("
            CREATE OR REPLACE VIEW v_sisa_pos AS
            SELECT
                pa.id              AS pos_anggaran_id,
                pa.tahun_anggaran_id,
                pa.kode,
                pa.nama            AS pos_nama,
                pa.tipe,
                pa.penerima_id,
                ja.nama AS jenis_anggaran_nama,
                pa.pagu,
                pa.kelompok_jabatan,
                COALESCE(r.realisasi, 0) AS realisasi,
                pa.pagu - COALESCE(r.realisasi, 0) AS sisa
            FROM pos_anggaran pa
            LEFT JOIN jenis_anggaran ja ON ja.id = pa.jenis_anggaran_id
            LEFT JOIN (
                SELECT x.pos_anggaran_id, SUM(x.nilai) AS realisasi
                FROM (
                    SELECT ka.pos_anggaran_id, kb.id AS biaya_id, kb.volume * kb.tarif AS nilai
                    FROM kegiatan_anggaran ka
                    JOIN kegiatan_biaya kb ON kb.kegiatan_anggaran_id = ka.id
                    JOIN kegiatan k ON k.id = ka.kegiatan_id AND k.status = 'AKTIF'
                    UNION ALL
                    SELECT kp.pos_anggaran_id, kb.id AS biaya_id, kb.volume * kb.tarif AS nilai
                    FROM kegiatan_peserta kp
                    JOIN kegiatan_biaya kb ON kb.kegiatan_peserta_id = kp.id AND kb.kegiatan_anggaran_id IS NULL
                    JOIN kegiatan k ON k.id = kp.kegiatan_id AND k.status = 'AKTIF'
                    WHERE kp.pos_anggaran_id IS NOT NULL
                ) x GROUP BY x.pos_anggaran_id
            ) r ON r.pos_anggaran_id = pa.id;
        ");
        $this->db->query("INSERT IGNORE INTO jenis_kegiatan (kode, nama, subjenis_json, urutan) VALUES
            ('PERJALANAN_DINAS', 'Perjalanan Dinas', JSON_ARRAY(), 1),
            ('RAPAT', 'Rapat', JSON_ARRAY('Rapat Paripurna', 'Rapat Komisi', 'Rapat Banggar', 'Rapat Bamus', 'Rapat Khusus'), 2),
            ('FASILITAS', 'Fasilitas', JSON_ARRAY(), 3),
            ('LAINNYA', 'Kegiatan Lainnya', JSON_ARRAY(), 99)");
        $this->db->query("INSERT IGNORE INTO jenis_anggaran (kode, nama, keterangan, urutan) VALUES
            ('FASILITAS', 'Fasilitas DPRD', 'Pagu untuk fasilitas dan kebutuhan pendukung DPRD.', 1),
            ('RAPAT', 'Rapat', 'Pagu untuk rapat paripurna, komisi, Banggar, Bamus, dan rapat khusus.', 2),
            ('PERJALANAN_DINAS', 'Perjalanan Dinas', 'Pagu perjalanan dinas anggota DPRD dan pendamping.', 3),
            ('HONORARIUM', 'Honorarium / Notulen', 'Pagu honorarium, notulen, dan tenaga pendukung.', 4),
            ('LAINNYA', 'Anggaran Lainnya', 'Kelompok anggaran tambahan sesuai kebutuhan.', 99)");
        $this->db->query("UPDATE pos_anggaran pa
            JOIN jenis_anggaran ja ON ja.kode = 'PERJALANAN_DINAS'
            SET pa.jenis_anggaran_id = ja.id
            WHERE pa.jenis_anggaran_id IS NULL");
        $this->db->query("UPDATE kegiatan k
            JOIN jenis_kegiatan jk ON jk.kode = 'PERJALANAN_DINAS'
            SET k.jenis_kegiatan_id = jk.id
            WHERE k.jenis_kegiatan_id IS NULL");
    }

    // ─── Pos Anggaran ───────────────────────────────────────────────

    /**
     * Semua pos anggaran untuk satu tahun, dilengkapi saldo realisasi & sisa
     */
    public function all_by_tahun(int $tahunId, ?string $tipe = null): array {
        $this->db
            ->select('sp.*, ta.tahun,
                      ja.nama AS jenis_anggaran_nama,
                      COALESCE(GROUP_CONCAT(DISTINCT pen.nama ORDER BY pen.nama SEPARATOR ", "), "-") AS pemegang_nama,
                      COALESCE(GROUP_CONCAT(DISTINCT pen.jabatan ORDER BY pen.jabatan SEPARATOR ", "), "-") AS pemegang_jabatan,
                      MIN(pp.id) AS penugasan_id,
                      MIN(pp.penerima_id) AS penugasan_penerima_id,
                      MIN(pp.tanggal_mulai) AS penugasan_mulai', FALSE)
            ->from('pos_anggaran sp')
            ->join('tahun_anggaran ta', 'ta.id = sp.tahun_anggaran_id')
            ->join('jenis_anggaran ja', 'ja.id = sp.jenis_anggaran_id', 'left')
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
        // Pos bawaan anggota DPRD dimulai dari kelompok Fasilitas DPRD.
        // Kelompoknya tetap dapat diganti dari menu Pos Anggaran.
        $jenisAnggaran = $this->db->get_where('jenis_anggaran', ['kode' => 'FASILITAS'])->row_array();
        if (!$jenisAnggaran) $jenisAnggaran = $this->db->get_where('jenis_anggaran', ['kode' => 'PERJALANAN_DINAS'])->row_array();
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
                    'jenis_anggaran_id' => $jenisAnggaran ? (int)$jenisAnggaran['id'] : null,
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
        $jenisAnggaran = $this->db->get_where('jenis_anggaran', ['kode' => 'PERJALANAN_DINAS'])->row_array();
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
                    'jenis_anggaran_id' => $jenisAnggaran ? (int)$jenisAnggaran['id'] : null,
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
    public function terapkan_pagu_standar(int $tahunId, array $standar, bool $overwrite = false, ?int $oleh = null): int {
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

            $sebelum = (float)$row['pagu'];
            $isAwal = (float)($row['pagu_awal'] ?? 0) <= 0 && $sebelum <= 0;
            $update = ['pagu' => $paguBaru, 'updated_at' => date('Y-m-d H:i:s')];
            if ($isAwal) {
                $update['pagu_awal'] = $paguBaru;
                $update['pagu_awal_at'] = date('Y-m-d H:i:s');
                $update['pagu_awal_oleh'] = $oleh;
            }
            $this->db->where('id', (int)$row['id'])->update('pos_anggaran', $update);
            $this->catat_riwayat_pagu((int)$row['id'], $isAwal ? 'AWAL' : 'STANDAR', $sebelum, $paguBaru, 0, $isAwal, 'Terapkan pagu standar', $oleh);
            $updated++;
        }
        return $updated;
    }

    /**
     * Terapkan satu pagu standar ke seluruh pos PENDAMPING pada satu tahun.
     * Hanya memperbarui pagu yang masih 0 kecuali overwrite diaktifkan.
     */
    public function terapkan_pagu_pendamping(int $tahunId, float $pagu, bool $overwrite = false, ?int $oleh = null): int {
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
            $sebelum = (float)$row['pagu'];
            $isAwal = (float)($row['pagu_awal'] ?? 0) <= 0 && $sebelum <= 0;
            $update = ['pagu' => $pagu, 'updated_at' => date('Y-m-d H:i:s')];
            if ($isAwal) {
                $update['pagu_awal'] = $pagu;
                $update['pagu_awal_at'] = date('Y-m-d H:i:s');
                $update['pagu_awal_oleh'] = $oleh;
            }
            $this->db->where('id', (int)$row['id'])->update('pos_anggaran', $update);
            $this->catat_riwayat_pagu((int)$row['id'], $isAwal ? 'AWAL' : 'STANDAR', $sebelum, $pagu, 0, $isAwal, 'Terapkan pagu standar pendamping', $oleh);
            $updated++;
        }

        return $updated;
    }

    /**
     * Menyimpan jejak perubahan pagu agar pagu awal dapat dibedakan dari revisi.
     */
    public function catat_riwayat_pagu(int $posId, string $jenis, float $sebelum, float $sesudah, float $realisasi = 0, bool $ditandaiAwal = false, ?string $alasan = null, ?int $oleh = null): int {
        $this->db->insert('riwayat_pagu_pos', [
            'pos_anggaran_id' => $posId,
            'jenis_perubahan' => $jenis,
            'pagu_sebelum' => $sebelum,
            'pagu_sesudah' => $sesudah,
            'realisasi_saat_itu' => $realisasi,
            'ditandai_pagu_awal' => $ditandaiAwal ? 1 : 0,
            'alasan' => $alasan,
            'dibuat_oleh' => $oleh,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
    }

    public function sudah_memiliki_pagu_awal(int $posId): bool {
        $row = $this->db->select('pagu_awal')->get_where('pos_anggaran', ['id' => $posId])->row_array();
        if (!$row) return false;
        if ((float)$row['pagu_awal'] > 0) return true;
        return (int)$this->db->where('pos_anggaran_id', $posId)->where('ditandai_pagu_awal', 1)->count_all_results('riwayat_pagu_pos') > 0;
    }

    public function buat_pos_pendamping_custom(int $tahunId, string $kode, string $nama, ?int $jenisId = null, int $urutan = 99): int {
        $this->db->insert('pos_anggaran', [
            'tahun_anggaran_id' => $tahunId,
            'kode' => $kode,
            'nama' => $nama,
            'tipe' => 'PENDAMPING',
            'penerima_id' => null,
            'jenis_anggaran_id' => $jenisId ?: null,
            'pagu' => 0,
            'pagu_awal' => 0,
            'kelompok_jabatan' => null,
            'urutan' => $urutan,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        return (int)$this->db->insert_id();
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


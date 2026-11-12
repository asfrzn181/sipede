<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_model extends CI_Model
{
    private static bool $schemaChecked = false;

    public function __construct()
    {
        parent::__construct();
        if (!self::$schemaChecked && $this->db->table_exists('kegiatan_biaya')) {
            self::$schemaChecked = true;
            if (!in_array('keterangan', $this->db->list_fields('kegiatan_biaya'), true)) {
                $this->db->query('ALTER TABLE kegiatan_biaya ADD COLUMN keterangan TEXT NULL AFTER jenis_biaya_id');
            }
        }
    }

    private array $columns = array(
        'tanggal_mulai', 'nama_kegiatan', 'tempat', 'penerima_nama',
        'tipe', 'jabatan', 'jenis_biaya_nama', 'volume', 'tarif', 'subtotal'
    );

    private function apply_filter(array $filter, ?string $scope = null, ?int $ownId = null): void
    {
        if (!empty($filter['tahun_anggaran_id'])) {
            $this->db->where('d.tahun_anggaran_id', (int)$filter['tahun_anggaran_id']);
        }
        if ($scope === 'own' && $ownId) {
            $this->db->where('d.penerima_id', $ownId);
        }
        if (!empty($filter['status'])) {
            $this->db->where('d.status', $filter['status']);
        }
        if (!empty($filter['penerima_id'])) {
            $pIds = array_filter(array_map('intval', (array)$filter['penerima_id']));
            if (!empty($pIds)) {
                $this->db->where_in('d.penerima_id', $pIds);
            }
        }
        if (!empty($filter['jenis_biaya_id'])) {
            $jIds = array_filter(array_map('intval', (array)$filter['jenis_biaya_id']));
            if (!empty($jIds)) {
                $this->db->where_in('d.jenis_biaya_id', $jIds);
            }
        }
        if (!empty($filter['tanggal_mulai'])) {
            $this->db->where('d.tanggal_mulai >=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $this->db->where('d.tanggal_mulai <=', $filter['tanggal_selesai']);
        }
        if (!empty($filter['tempat'])) {
            $this->db->like('d.tempat', $filter['tempat']);
        }
    }

    private function apply_search(string $search): void
    {
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('d.no_surat_tugas', $search);
            $this->db->or_like('d.nama_kegiatan', $search);
            $this->db->or_like('d.tema', $search);
            $this->db->or_like('d.penerima_nama', $search);
            $this->db->or_like('d.jabatan', $search);
            $this->db->or_like('d.tempat', $search);
            $this->db->or_like('d.jenis_biaya_nama', $search);
            $this->db->or_like('d.sumber_dana_nama', $search);
            $this->db->group_end();
        }
    }

    public function count_all(array $filter, ?string $scope = null, ?int $ownId = null): int
    {
        $this->db->from('v_detail_biaya d');
        $this->apply_filter($filter, $scope, $ownId);
        return (int)$this->db->count_all_results();
    }

    public function count_filtered(array $filter, ?string $scope = null, ?int $ownId = null, string $search = ''): int
    {
        $this->db->from('v_detail_biaya d');
        $this->apply_filter($filter, $scope, $ownId);
        $this->apply_search($search);
        return (int)$this->db->count_all_results();
    }

    public function get_datatable(
        array $filter,
        ?string $scope = null,
        ?int $ownId = null,
        int $start = 0,
        int $length = 10,
        string $search = '',
        string $orderCol = 'd.tanggal_mulai',
        string $orderDir = 'DESC'
    ): array {
        $this->db->select('d.*')->from('v_detail_biaya d');
        $this->apply_filter($filter, $scope, $ownId);
        $this->apply_search($search);

        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('d.tanggal_mulai', 'DESC');
        }

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result_array();
    }

    public function detail(array $filter, ?string $scope = null, ?int $ownId = null): array
    {
        $this->db->select('d.*')->from('v_detail_biaya d');
        $this->apply_filter($filter, $scope, $ownId);
        return $this->db->order_by('d.tanggal_mulai', 'DESC')->get()->result_array();
    }

    public function allowed_columns(): array
    {
        return $this->columns;
    }

    /** Ringkasan pagu dan realisasi per anggota DPRD pada tahun tertentu. */
    public function ringkasan_anggota(int $tahunId, ?string $scope = null, ?int $ownId = null): array
    {
        $sql = '
            SELECT
                p.id,
                p.nama,
                p.jabatan,
                COALESCE(pagu_anggota.pagu, 0) AS pagu,
                COALESCE(realisasi_anggota.realisasi, 0) AS realisasi,
                COALESCE(realisasi_anggota.jumlah_kegiatan, 0) AS jumlah_kegiatan
            FROM penerima p
            LEFT JOIN (
                SELECT penerima_id, SUM(pagu) AS pagu
                FROM pos_anggaran
                WHERE tahun_anggaran_id = ? AND tipe = "DPRD" AND is_active = 1
                GROUP BY penerima_id
            ) pagu_anggota ON pagu_anggota.penerima_id = p.id
            LEFT JOIN (
                SELECT pa.penerima_id,
                       SUM(CASE WHEN k.status = "AKTIF" THEN kb.volume * kb.tarif ELSE 0 END) AS realisasi,
                       COUNT(DISTINCT CASE WHEN k.status = "AKTIF" THEN k.id END) AS jumlah_kegiatan
                FROM pos_anggaran pa
                LEFT JOIN kegiatan_peserta kp ON kp.pos_anggaran_id = pa.id
                LEFT JOIN kegiatan k ON k.id = kp.kegiatan_id
                LEFT JOIN kegiatan_biaya kb ON kb.kegiatan_peserta_id = kp.id
                WHERE pa.tahun_anggaran_id = ? AND pa.tipe = "DPRD" AND pa.is_active = 1
                GROUP BY pa.penerima_id
            ) realisasi_anggota ON realisasi_anggota.penerima_id = p.id
            WHERE p.tipe = "DPRD" AND p.is_active = 1
        ';
        $params = array($tahunId, $tahunId);

        if ($scope === 'own' && $ownId) {
            $sql .= ' AND p.id = ? ';
            $params[] = $ownId;
        }

        $sql .= ' GROUP BY p.id, p.nama, p.jabatan ORDER BY p.nama ASC';
        $rows = $this->db->query($sql, $params)->result_array();

        foreach ($rows as &$row) {
            $row['pagu'] = (float)$row['pagu'];
            $row['realisasi'] = (float)$row['realisasi'];
            $row['sisa'] = $row['pagu'] - $row['realisasi'];
            $row['persentase'] = $row['pagu'] > 0 ? ($row['realisasi'] / $row['pagu']) * 100 : 0;
        }
        unset($row);

        return $rows;
    }

    /** Ringkasan pagu dan realisasi per pos anggaran pada tahun tertentu. */
    public function ringkasan_pos(int $tahunId, ?string $scope = null, ?int $ownId = null): array
    {
        $sql = '
            SELECT
                v.pos_anggaran_id,
                v.kode,
                v.pos_nama,
                v.tipe,
                v.jenis_anggaran_nama,
                v.penerima_id,
                p.nama AS penerima_nama,
                v.pagu,
                v.realisasi,
                v.sisa,
                COUNT(DISTINCT CASE WHEN k.status = "AKTIF" THEN k.id END) AS jumlah_kegiatan
            FROM v_sisa_pos v
            LEFT JOIN penerima p ON p.id = v.penerima_id
            LEFT JOIN kegiatan_peserta kp ON kp.pos_anggaran_id = v.pos_anggaran_id
            LEFT JOIN kegiatan k ON k.id = kp.kegiatan_id
            WHERE v.tahun_anggaran_id = ?
        ';
        $params = array($tahunId);

        if ($scope === 'own' && $ownId) {
            $sql .= ' AND (v.penerima_id = ? OR kp.penerima_id = ?) ';
            $params[] = $ownId;
            $params[] = $ownId;
        }

        $sql .= ' GROUP BY v.pos_anggaran_id, v.kode, v.pos_nama, v.tipe, v.penerima_id, p.nama, v.pagu, v.realisasi, v.sisa
                  ORDER BY v.tipe ASC, v.pos_nama ASC';

        $rows = $this->db->query($sql, $params)->result_array();
        foreach ($rows as &$row) {
            $row['pagu'] = (float)$row['pagu'];
            $row['realisasi'] = (float)$row['realisasi'];
            $row['sisa'] = (float)$row['sisa'];
            $row['persentase'] = $row['pagu'] > 0 ? ($row['realisasi'] / $row['pagu']) * 100 : 0;
        }
        unset($row);

        return $rows;
    }

    /** Ringkasan penyerapan per komponen biaya pada tahun tertentu. */
    public function ringkasan_komponen(int $tahunId, ?string $scope = null, ?int $ownId = null): array
    {
        $sql = '
            SELECT
                d.jenis_biaya_id,
                d.jenis_biaya_nama,
                COALESCE(SUM(d.subtotal), 0) AS realisasi,
                COUNT(DISTINCT d.kegiatan_id) AS jumlah_kegiatan,
                COUNT(*) AS jumlah_transaksi
            FROM v_detail_biaya d
            WHERE d.tahun_anggaran_id = ? AND d.status = "AKTIF"
        ';
        $params = array($tahunId);

        if ($scope === 'own' && $ownId) {
            $sql .= ' AND d.penerima_id = ? ';
            $params[] = $ownId;
        }

        $sql .= ' GROUP BY d.jenis_biaya_id, d.jenis_biaya_nama ORDER BY realisasi DESC';
        $rows = $this->db->query($sql, $params)->result_array();
        foreach ($rows as &$row) {
            $row['realisasi'] = (float)$row['realisasi'];
        }
        unset($row);

        return $rows;
    }

    /**
     * Detail belanja perjalanan dinas satu orang dalam satu tahun.
     * Baris dikembalikan per kegiatan dengan nilai setiap komponen biaya.
     */
    public function detail_perorang(int $tahunId, int $penerimaId): array
    {
        $person = $this->db
            ->select('id, nama, jabatan, tipe')
            ->where('id', $penerimaId)
            ->get('penerima')->row_array();

        if (!$person) return array();

        $paguRows = $this->db
            ->select('COALESCE(ja.nama, "Belum dikategorikan") AS nama, COALESCE(SUM(pa.pagu), 0) AS pagu', FALSE)
            ->from('pos_anggaran pa')
            ->join('jenis_anggaran ja', 'ja.id = pa.jenis_anggaran_id', 'left')
            ->where('pa.tahun_anggaran_id', $tahunId)
            ->where('pa.is_active', 1)
            ->group_start()
                ->group_start()
                    ->where('pa.tipe', 'DPRD')
                    ->where('pa.penerima_id', $penerimaId)
                ->group_end()
                ->or_group_start()
                    ->where('pa.tipe', 'PENDAMPING')
                    ->where("EXISTS (SELECT 1 FROM penugasan_pos pp WHERE pp.pos_anggaran_id = pa.id AND pp.penerima_id = {$penerimaId})", null, false)
                ->group_end()
            ->group_end()
            ->group_by('pa.jenis_anggaran_id, ja.nama')
            ->order_by('ja.urutan', 'ASC')
            ->get()->result_array();

        $componentRows = $this->db
            ->select('id, kode, nama, satuan')
            ->where('is_active', 1)
            ->order_by("FIELD(kode, 'UANG_HARIAN', 'UANG_REPRESENTATIF', 'PENGINAPAN', 'TRANSPORT')", '', FALSE)
            ->order_by('nama', 'ASC')
            ->get('jenis_biaya')->result_array();

        $raw = $this->db
            ->select('k.id AS kegiatan_id, k.nama_kegiatan, k.no_surat_tugas, k.tanggal_mulai, k.tanggal_selesai,
                      k.tema, k.tempat, k.status, kb.jenis_biaya_id, jb.kode AS jenis_biaya_kode,
                      jb.nama AS jenis_biaya_nama, kb.keterangan, kb.volume, kb.tarif,
                      COALESCE(sd.nama, ja.nama, "-") AS sumber_dana_nama,
                      (kb.volume * kb.tarif) AS subtotal', FALSE)
            ->from('kegiatan k')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id')
            ->join('kegiatan_biaya kb', 'kb.kegiatan_peserta_id = kp.id')
            ->join('jenis_biaya jb', 'jb.id = kb.jenis_biaya_id')
            ->join('sumber_dana sd', 'sd.id = kp.sumber_dana_id', 'left')
            ->join('pos_anggaran pa', 'pa.id = kp.pos_anggaran_id', 'left')
            ->join('jenis_anggaran ja', 'ja.id = pa.jenis_anggaran_id', 'left')
            ->where('k.tahun_anggaran_id', $tahunId)
            ->where('kp.penerima_id', $penerimaId)
            ->where('k.status', 'AKTIF')
            ->order_by('k.tanggal_mulai', 'ASC')
            ->order_by('k.id', 'ASC')
            ->get()->result_array();

        $componentMap = array();
        foreach ($componentRows as $component) {
            $component['total'] = 0.0;
            $componentMap[(int)$component['id']] = $component;
        }

        $groups = array();
        foreach ($raw as $row) {
            $key = (int)$row['kegiatan_id'];
            if (!isset($groups[$key])) {
                $groups[$key] = array(
                    'kegiatan_id' => $key,
                    'no_surat_tugas' => $row['no_surat_tugas'],
                    'nama_kegiatan' => $row['nama_kegiatan'],
                    'tema' => $row['tema'],
                    'tempat' => $row['tempat'],
                    'tanggal_mulai' => $row['tanggal_mulai'],
                    'tanggal_selesai' => $row['tanggal_selesai'],
                    'status' => $row['status'],
                    'sumber_dana' => array(),
                    'komponen' => array(),
                    'keterangan' => array(),
                    'total' => 0.0,
                );
            }

            $jenisId = (int)$row['jenis_biaya_id'];
            $subtotal = (float)$row['subtotal'];
            $groups[$key]['komponen'][$jenisId] = ($groups[$key]['komponen'][$jenisId] ?? 0) + $subtotal;
            $groups[$key]['total'] += $subtotal;
            if (!empty($row['keterangan'])) $groups[$key]['keterangan'][] = trim((string)$row['keterangan']);
            if (!empty($row['sumber_dana_nama']) && $row['sumber_dana_nama'] !== '-') $groups[$key]['sumber_dana'][] = trim((string)$row['sumber_dana_nama']);
            if (isset($componentMap[$jenisId])) $componentMap[$jenisId]['total'] += $subtotal;
        }

        $komponenBaku = array('UANG_HARIAN', 'UANG_REPRESENTATIF', 'PENGINAPAN', 'TRANSPORT');
        $componentMap = array_filter($componentMap, function ($component) use ($komponenBaku) {
            return in_array($component['kode'], $komponenBaku, true) || (float)$component['total'] > 0;
        });

        $totalPagu = 0.0;
        foreach ($paguRows as &$pagu) {
            $pagu['pagu'] = (float)$pagu['pagu'];
            $totalPagu += $pagu['pagu'];
        }
        unset($pagu);

        $cumulative = 0.0;
        $detail = array_values($groups);
        foreach ($detail as &$row) {
            $cumulative += (float)$row['total'];
            $row['sisa'] = $totalPagu - $cumulative;
            $row['keterangan'] = implode(' | ', array_unique($row['keterangan']));
            $row['sumber_dana'] = implode(' | ', array_unique($row['sumber_dana']));
        }
        unset($row);

        return array(
            'person' => $person,
            'pagu_breakdown' => $paguRows,
            'total_pagu' => $totalPagu,
            'components' => array_values($componentMap),
            'rows' => $detail,
            'total_realisasi' => $cumulative,
            'sisa' => $totalPagu - $cumulative,
        );
    }
}

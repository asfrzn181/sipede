<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Laporan_model extends CI_Model
{
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
}

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
}

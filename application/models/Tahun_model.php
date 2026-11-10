<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tahun_model extends CI_Model {
    public function all(): array {
        return $this->db->order_by('tahun', 'DESC')->get('tahun_anggaran')->result_array();
    }

    public function count_all(): int {
        return (int)$this->db->count_all('tahun_anggaran');
    }

    public function count_filtered(string $search = ''): int {
        if ($search !== '') {
            $this->db->like('tahun', $search);
        }
        return (int)$this->db->count_all_results('tahun_anggaran');
    }

    public function get_datatable(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'tahun', string $orderDir = 'DESC'): array {
        if ($search !== '') {
            $this->db->like('tahun', $search);
        }
        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('tahun', 'DESC');
        }
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        return $this->db->get('tahun_anggaran')->result_array();
    }

    public function find(int $id): ?array {
        return $this->db->get_where('tahun_anggaran', array('id' => $id))->row_array() ?: NULL;
    }

    public function save(array $data, ?int $id = NULL): int {
        if ($id) {
            $this->db->where('id', $id)->update('tahun_anggaran', $data);
            return $id;
        }
        $this->db->insert('tahun_anggaran', $data);
        return (int)$this->db->insert_id();
    }

    /** Pagu anggaran per penerima untuk satu tahun, dilengkapi data realisasi dan sisa */
    public function pagu_by_tahun(int $tahun_id): array {
        return $this->db
            ->select('a.id anggaran_id, COALESCE(a.pagu, 0) pagu, p.id penerima_id, p.nama, p.tipe, p.jabatan, p.periode_mulai, p.periode_akhir, COALESCE(r.realisasi, 0) realisasi, (COALESCE(a.pagu, 0) - COALESCE(r.realisasi, 0)) sisa', FALSE)
            ->from('penerima p')
            ->join('anggaran a', 'a.penerima_id = p.id AND a.tahun_anggaran_id = ' . (int)$tahun_id, 'left')
            ->join('v_realisasi r', 'r.tahun_anggaran_id = ' . (int)$tahun_id . ' AND r.penerima_id = p.id', 'left')
            ->where('p.is_active', 1)
            ->order_by('p.tipe')
            ->order_by('p.nama')
            ->get()->result_array();
    }
}

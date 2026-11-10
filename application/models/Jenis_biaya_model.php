<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis_biaya_model extends CI_Model {
    public function all(): array {
        return $this->db->order_by('nama')->get('jenis_biaya')->result_array();
    }

    public function count_all(): int {
        return (int)$this->db->count_all('jenis_biaya');
    }

    public function count_filtered(string $search = ''): int {
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('kode', $search);
            $this->db->or_like('nama', $search);
            $this->db->or_like('satuan', $search);
            $this->db->group_end();
        }
        return (int)$this->db->count_all_results('jenis_biaya');
    }

    public function get_datatable(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'nama', string $orderDir = 'ASC'): array {
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('kode', $search);
            $this->db->or_like('nama', $search);
            $this->db->or_like('satuan', $search);
            $this->db->group_end();
        }
        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('nama', 'ASC');
        }
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        return $this->db->get('jenis_biaya')->result_array();
    }

    public function save(array $data, ?int $id = NULL): int {
        if ($id) {
            $this->db->where('id', $id)->update('jenis_biaya', $data);
            return $id;
        }
        $this->db->insert('jenis_biaya', $data);
        return (int)$this->db->insert_id();
    }
}

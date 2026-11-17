<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pendamping_model extends CI_Model
{
    public function all(): array
    {
        return $this->db->where('is_active', 1)->order_by('nama')->get('pendamping')->result_array();
    }

    public function find(int $id): ?array
    {
        return $this->db->get_where('pendamping', array('id' => $id))->row_array() ?: null;
    }

    public function count_all(): int
    {
        return (int)$this->db->count_all('pendamping');
    }

    public function count_filtered(string $search = ''): int
    {
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('nama', $search);
            $this->db->or_like('jabatan', $search);
            $this->db->group_end();
        }
        return (int)$this->db->count_all_results('pendamping');
    }

    public function get_datatable(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'nama', string $orderDir = 'ASC'): array
    {
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('nama', $search);
            $this->db->or_like('jabatan', $search);
            $this->db->group_end();
        }
        if ($orderCol) $this->db->order_by($orderCol, $orderDir);
        if ($length > 0) $this->db->limit($length, $start);
        return $this->db->get('pendamping')->result_array();
    }

    public function save(array $data, ?int $id = null): int
    {
        if ($id) {
            $this->db->where('id', $id)->update('pendamping', $data);
            return $id;
        }
        $this->db->insert('pendamping', $data);
        return (int)$this->db->insert_id();
    }

    public function deactivate(int $id): bool
    {
        return (bool)$this->db->where('id', $id)->update('pendamping', array('is_active' => 0));
    }
}

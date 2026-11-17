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

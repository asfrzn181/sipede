<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Audit_model extends CI_Model {
    public function write(string $entity, int $entityId, string $action, array $old, array $new, int $userId): bool {
        return $this->db->insert('audit_log', array(
            'user_id'    => $userId,
            'entity'     => $entity,
            'entity_id'  => $entityId,
            'action'     => $action,
            'old_data'   => json_encode($old),
            'new_data'   => json_encode($new),
            'created_at' => date('Y-m-d H:i:s')
        ));
    }

    public function count_all(): int {
        return (int)$this->db->count_all('audit_log');
    }

    public function count_filtered(string $search = ''): int {
        $this->db->from('audit_log a')->join('users u', 'u.id=a.user_id', 'left');
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.entity', $search);
            $this->db->or_like('a.action', $search);
            $this->db->or_like('u.nama', $search);
            $this->db->or_like('a.created_at', $search);
            $this->db->group_end();
        }
        return (int)$this->db->count_all_results();
    }

    public function get_datatable(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'a.created_at', string $orderDir = 'DESC'): array {
        $this->db->select('a.*, u.nama')
            ->from('audit_log a')
            ->join('users u', 'u.id=a.user_id', 'left');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('a.entity', $search);
            $this->db->or_like('a.action', $search);
            $this->db->or_like('u.nama', $search);
            $this->db->or_like('a.created_at', $search);
            $this->db->group_end();
        }

        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('a.created_at', 'DESC');
        }

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result_array();
    }
}

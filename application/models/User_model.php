<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {
    public function has_column(string $column): bool {
        return in_array($column, $this->db->list_fields('users'), true);
    }

    public function by_username(string $username): ?array {
        if ($this->has_column('role_id') && $this->db->table_exists('roles')) {
            return $this->db->select('u.*,r.kode role,r.nama role_nama')->from('users u')->join('roles r','r.id=u.role_id','left')->where(array('u.username'=>$username,'u.is_active'=>1))->get()->row_array() ?: NULL;
        }
        return $this->db->select('u.*,u.role role_nama')->from('users u')->where(array('u.username'=>$username,'u.is_active'=>1))->get()->row_array() ?: NULL;
    }

    public function all(): array {
        return $this->db->select('u.id,u.username,u.nama,u.role_id,u.penerima_id,u.is_active,u.wajib_ganti_password,r.kode role, r.nama role_nama,p.nama penerima_nama')->from('users u')->join('roles r','r.id=u.role_id','left')->join('penerima p','p.id=u.penerima_id','left')->order_by('u.nama')->get()->result_array();
    }

    public function count_all(): int {
        return (int)$this->db->count_all('users');
    }

    public function count_filtered(string $search = ''): int {
        $this->db->from('users u')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->join('penerima p', 'p.id=u.penerima_id', 'left');
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('u.nama', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('r.nama', $search);
            $this->db->or_like('p.nama', $search);
            $this->db->group_end();
        }
        return (int)$this->db->count_all_results();
    }

    public function get_datatable(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'u.nama', string $orderDir = 'ASC'): array {
        $this->db->select('u.id,u.username,u.nama,u.role_id,u.penerima_id,u.is_active,u.wajib_ganti_password,r.kode role, r.nama role_nama,p.nama penerima_nama')
            ->from('users u')
            ->join('roles r', 'r.id=u.role_id', 'left')
            ->join('penerima p', 'p.id=u.penerima_id', 'left');

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('u.nama', $search);
            $this->db->or_like('u.username', $search);
            $this->db->or_like('r.nama', $search);
            $this->db->or_like('p.nama', $search);
            $this->db->group_end();
        }

        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('u.nama', 'ASC');
        }

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result_array();
    }

    public function roles(): array {
        return $this->db->where('aktif',1)->order_by('nama')->get('roles')->result_array();
    }

    public function find(int $id): ?array {
        return $this->db->get_where('users',array('id'=>$id))->row_array() ?: NULL;
    }

    public function save(array $data, ?int $id = NULL): int {
        if ($id) {
            if (empty($data['password'])) unset($data['password']);
            else $data['password']=password_hash($data['password'],PASSWORD_DEFAULT);
            $this->db->where('id',$id)->update('users',$data);
            return $id;
        }
        $data['password']=password_hash($data['password'],PASSWORD_DEFAULT);
        $this->db->insert('users',$data);
        return (int)$this->db->insert_id();
    }
}

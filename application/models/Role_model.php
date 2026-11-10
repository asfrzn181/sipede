<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Role_model extends CI_Model {
    public function all(): array { return $this->db->order_by('is_system','DESC')->order_by('nama')->get('roles')->result_array(); }
    public function permissions(): array { return $this->db->order_by('modul')->order_by('kode')->get('permissions')->result_array(); }
    public function find(int $id): ?array { return $this->db->get_where('roles',array('id'=>$id))->row_array() ?: NULL; }
    public function permission_codes(int $roleId): array { return array_column($this->db->select('p.kode')->from('role_permissions rp')->join('permissions p','p.id=rp.permission_id')->where('rp.role_id',$roleId)->get()->result_array(),'kode'); }
    public function users_count(int $roleId): int { return (int)$this->db->where('role_id',$roleId)->count_all_results('users'); }
    public function active_superadmins(): int { $row=$this->db->select('COUNT(*) total')->from('users u')->join('roles r','r.id=u.role_id')->where(array('r.kode'=>'SUPERADMIN','u.is_active'=>1))->get()->row_array(); return (int)($row['total']??0); }
    public function save(array $data, ?int $id=null): int { if($id){$this->db->where('id',$id)->update('roles',$data);return $id;} $this->db->insert('roles',$data);return (int)$this->db->insert_id(); }
    public function sync_permissions(int $roleId,array $permissionIds): bool { $this->db->where('role_id',$roleId)->delete('role_permissions');foreach(array_unique(array_map('intval',$permissionIds)) as $permissionId)$this->db->insert('role_permissions',array('role_id'=>$roleId,'permission_id'=>$permissionId));return true; }
}

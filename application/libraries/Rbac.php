<?php defined('BASEPATH') OR exit('No direct script access allowed');

/** Otorisasi berbasis permission; default-nya selalu menolak kecuali Super Admin. */
class Rbac
{
    private CI_Controller $ci;
    private array $permissions = array();
    private bool $loaded = false;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function load(int $userId): void
    {
        if (!$userId) { $this->permissions = array(); $this->loaded = true; return; }
        $session = (array) $this->ci->session->userdata('permissions');
        if (!$session && $this->ci->db->table_exists('role_permissions') && $this->ci->db->table_exists('permissions') && $this->ci->db->table_exists('roles')) {
            $userFields = $this->ci->db->list_fields('users');
            $this->ci->db->select('p.kode')->from('role_permissions rp')
                ->join('permissions p', 'p.id=rp.permission_id')
                ->join('users u', in_array('role_id', $userFields, true) ? 'u.role_id=rp.role_id' : 'u.role= (SELECT kode FROM roles WHERE id=rp.role_id)', 'inner')
                ->where('u.id', $userId)
                ->where('u.is_active', 1);
            $rows = $this->ci->db->get()->result_array();
            $session = array_values(array_unique(array_column($rows, 'kode')));
            $this->ci->session->set_userdata('permissions', $session);
        }
        $this->permissions = $session;
        $this->loaded = true;
    }

    public function reload(): void
    {
        $this->ci->session->unset_userdata('permissions');
        $user = (array) $this->ci->session->userdata('user');
        $this->load((int)($user['id'] ?? 0));
    }

    public function is_super_admin(): bool
    {
        $user = (array) $this->ci->session->userdata('user');
        if (empty($user)) return false;
        $role = strtolower((string)($user['role'] ?? ''));
        $roleNama = strtolower((string)($user['role_nama'] ?? ''));
        $roleId = (int)($user['role_id'] ?? 0);
        return $role === 'super_admin' || $role === 'superadmin' || $role === 'admin'
            || str_contains($roleNama, 'super admin') || $roleId === 1 || !empty($user['is_super_admin']);
    }

    public function can(string $kode): bool
    {
        if ($this->is_super_admin()) return true;
        return $this->loaded && in_array($kode, $this->permissions, true);
    }

    public function can_any(array $codes): bool
    {
        if ($this->is_super_admin()) return true;
        foreach ($codes as $code) if ($this->can((string)$code)) return true;
        return false;
    }

    public function can_all(array $codes): bool
    {
        if ($this->is_super_admin()) return true;
        foreach ($codes as $code) if (!$this->can((string)$code)) return false;
        return true;
    }

    public function scope(string $modul): ?string
    {
        if ($this->is_super_admin()) return 'all';
        if ($this->can($modul.'.view_all')) return 'all';
        if ($this->can($modul.'.view_own')) return 'own';
        return null;
    }

    public function permissions(): array { return $this->permissions; }
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');

class User extends MY_Controller
{
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->require_permission('user.manage');
        $this->load->model(array('User_model', 'Role_model', 'Audit_model'));
    }

    public function index(): void {
        $this->load->view('layout/main', array(
            'title' => 'Pengguna',
            'view'  => 'user/index',
            'data'  => array(
                'rows'       => array(),
                'roles'      => $this->User_model->roles(),
                'pendamping' => $this->User_model->available_pendamping()
            )
        ));
    }

    public function data(): void {
        $this->require_permission('user.manage');

        $columnMap = array(
            0 => 'u.nama',
            1 => 'u.username',
            2 => 'r.nama',
            3 => 'p.nama',
            4 => 'u.is_active'
        );

        $params = $this->dt_params($columnMap, 'u.nama', 'ASC');
        $recordsTotal = $this->User_model->count_all();
        $recordsFiltered = $this->User_model->count_filtered($params['search']);
        $rows = $this->User_model->get_datatable(
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $namaHtml = '<span class="fw-semibold">' . html_escape($row['nama']) . '</span>';
            $usernameHtml = '<code>' . html_escape($row['username']) . '</code>';
            $roleHtml = '<span class="badge text-bg-primary bg-opacity-10 text-primary border">' . html_escape($row['role_nama'] ?: $row['role'] ?: '-') . '</span>';
            $penerimaHtml = html_escape($row['penerima_nama'] ?: '-');
            $statusBadge = $row['is_active'] ? 'success' : 'secondary';
            $statusLabel = $row['is_active'] ? 'Aktif' : 'Nonaktif';
            $statusHtml = '<span class="badge text-bg-' . $statusBadge . '">' . $statusLabel . '</span>';

            $data[] = array(
                $namaHtml,
                $usernameHtml,
                $roleHtml,
                $penerimaHtml,
                $statusHtml
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    private function check_role(int $roleId, ?int $id = null): void {
        $role = $this->Role_model->find($roleId);
        if (!$role) $this->respond(FALSE, 'Role tidak ditemukan.', array(), array(), 422);
        if ($role['kode'] === 'SUPERADMIN' && ($this->user['role'] ?? '') !== 'SUPERADMIN') {
            $this->respond(FALSE, 'Hanya SUPERADMIN yang dapat mengelola SUPERADMIN.', array(), array(), 403);
        }
        $actorPerm = $this->rbac->permissions();
        foreach ($this->Role_model->permission_codes($roleId) as $permission) {
            if (!in_array($permission, $actorPerm, true) && ($this->user['role'] ?? '') !== 'SUPERADMIN') {
                $this->respond(FALSE, 'Role tujuan memiliki permission melebihi pemberi.', array(), array(), 403);
            }
        }
        if ($id && (int)$id === (int)$this->user['id'] && $roleId !== (int)$this->user['role_id']) {
            $this->respond(FALSE, 'Anda tidak boleh mengubah role sendiri.', array(), array(), 403);
        }
    }

    public function save(): void {
        $id = decrypt_id($this->input->post('id'));
        $roleId = decrypt_id($this->input->post('role_id'));
        $this->check_role((int)$roleId, $id);
        $role = $this->Role_model->find((int)$roleId);
        $rawPenerima = $this->input->post('penerima_id');
        $penerimaId = $rawPenerima !== '' && $rawPenerima !== null ? decrypt_id($rawPenerima) : NULL;

        if ($role['kode'] === 'PENDAMPING') {
            if (!$penerimaId || !$this->db->where(array('id' => $penerimaId, 'tipe' => 'PENDAMPING', 'is_active' => 1))->count_all_results('penerima')) {
                $this->respond(FALSE, 'User PENDAMPING wajib terhubung ke penerima PENDAMPING aktif.', array(), array(), 422);
            }
        }

        if ($id && (int)$id === (int)$this->user['id'] && !$this->input->post('is_active')) {
            $this->respond(FALSE, 'Anda tidak boleh menonaktifkan diri sendiri.', array(), array(), 403);
        }

        $password = (string)$this->input->post('password');
        if (!$id && !$password) $password = bin2hex(random_bytes(5));

        $data = array(
            'username'              => $this->input->post('username', TRUE),
            'nama'                  => $this->input->post('nama', TRUE),
            'role_id'               => $roleId,
            'penerima_id'           => $penerimaId,
            'is_active'             => (int)$this->input->post('is_active', TRUE),
            'wajib_ganti_password'  => 1,
            'password'              => $password
        );

        if (!$data['username'] || !$data['nama']) {
            $this->respond(FALSE, 'Username dan nama wajib diisi.', array(), array(), 422);
        }

        $saved = $this->User_model->save($data, $id ?: NULL);
        $this->Audit_model->write('USER', $saved, $id ? 'UBAH' : 'BUAT', array(), array('username' => $data['username'], 'role_id' => $roleId), $this->user['id']);
        $this->respond(TRUE, 'Pengguna tersimpan', array('id' => encrypt_id($saved), 'password_awal' => $password));
    }

    public function reset_password($id): void {
        $id = decrypt_id($id);
        $row = $this->User_model->find($id);
        if (!$row) $this->respond(FALSE, 'User tidak ditemukan.', array(), array(), 404);
        $password = bin2hex(random_bytes(5));
        $this->User_model->save(array('password' => $password, 'wajib_ganti_password' => 1), $id);
        $this->Audit_model->write('USER', $id, 'RESET_PASSWORD', array(), array(), $this->user['id']);
        $this->respond(TRUE, 'Password direset.', array('password_awal' => $password));
    }

    public function mass_pendamping(): void {
        $out = array();
        foreach ($this->User_model->available_pendamping() as $person) {
            $role = $this->db->get_where('roles', array('kode' => 'PENDAMPING'))->row_array();
            $username = 'pendamping' . (int)$person['id'];
            $password = bin2hex(random_bytes(5));
            $id = $this->User_model->save(array(
                'username'             => $username,
                'nama'                 => $person['nama'],
                'role_id'              => $role['id'],
                'penerima_id'          => $person['id'],
                'is_active'            => 1,
                'wajib_ganti_password' => 1,
                'password'             => $password
            ));
            $out[] = array('username' => $username, 'password' => $password, 'nama' => $person['nama']);
        }
        $this->respond(TRUE, 'Akun pendamping dibuat.', array('accounts' => $out));
    }
}

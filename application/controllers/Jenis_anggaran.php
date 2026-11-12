<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis_anggaran extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_login();
        $this->load->model('Audit_model');
        $this->ensure_schema();
    }

    private function ensure_schema(): void
    {
        if (!$this->db->table_exists('jenis_anggaran')) {
            $this->db->query("CREATE TABLE jenis_anggaran (
                id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                kode VARCHAR(40) NOT NULL UNIQUE,
                nama VARCHAR(120) NOT NULL,
                keterangan TEXT NULL,
                is_active TINYINT(1) NOT NULL DEFAULT 1,
                urutan SMALLINT UNSIGNED NOT NULL DEFAULT 0,
                PRIMARY KEY (id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        }
        $this->db->query("INSERT IGNORE INTO jenis_anggaran (kode, nama, keterangan, urutan) VALUES
            ('FASILITAS', 'Fasilitas DPRD', 'Pagu untuk fasilitas dan kebutuhan pendukung DPRD.', 1),
            ('RAPAT', 'Rapat', 'Pagu untuk rapat paripurna, komisi, Banggar, Bamus, dan rapat khusus.', 2),
            ('PERJALANAN_DINAS', 'Perjalanan Dinas', 'Pagu perjalanan dinas anggota DPRD dan pendamping.', 3),
            ('HONORARIUM', 'Honorarium / Notulen', 'Pagu honorarium, notulen, dan tenaga pendukung.', 4),
            ('LAINNYA', 'Anggaran Lainnya', 'Kelompok anggaran tambahan sesuai kebutuhan.', 99)");
    }

    public function index(): void
    {
        $this->require_permission('anggaran.view_all');
        $this->load->view('layout/main', [
            'title' => 'Jenis Anggaran',
            'view' => 'jenis_anggaran/index',
            'data' => ['can_manage' => can('anggaran.manage')],
        ]);
    }

    public function data(): void
    {
        $this->require_permission('anggaran.view_all');
        $canManage = can('anggaran.manage');
        $params = $this->dt_params([0 => 'kode', 1 => 'nama', 2 => 'urutan', 3 => 'is_active'], 'urutan', 'ASC');

        $apply = function() use ($params): void {
            if ($params['search'] !== '') {
                $this->db->group_start();
                $this->db->like('kode', $params['search']);
                $this->db->or_like('nama', $params['search']);
                $this->db->or_like('keterangan', $params['search']);
                $this->db->group_end();
            }
        };
        $recordsTotal = (int)$this->db->count_all('jenis_anggaran');
        $apply();
        $recordsFiltered = (int)$this->db->count_all_results('jenis_anggaran');
        $apply();
        $rows = $this->db->order_by($params['order_column'], $params['order_dir'])
            ->limit($params['length'], $params['start'])->get('jenis_anggaran')->result_array();

        $data = [];
        foreach ($rows as $row) {
            $item = [
                '<code>' . html_escape($row['kode']) . '</code>',
                '<span class="fw-semibold">' . html_escape($row['nama']) . '</span>',
                '<span class="small text-muted">' . html_escape($row['keterangan'] ?: '-') . '</span>',
                '<span class="badge text-bg-light border">' . (int)$row['urutan'] . '</span>',
                $row['is_active'] ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-secondary">Nonaktif</span>',
            ];
            if ($canManage) {
                $copy = $row;
                $copy['id'] = encrypt_id($row['id']);
                $item[] = '<div class="text-end"><button class="btn btn-sm btn-outline-primary js-edit-jenis-anggaran" data-row=\'' . htmlspecialchars(json_encode($copy), ENT_QUOTES, 'UTF-8') . '\'><i class="bi bi-pencil"></i></button></div>';
            }
            $data[] = $item;
        }
        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    public function save(): void
    {
        $this->require_permission('anggaran.manage');
        $id = decrypt_id($this->input->post('id'));
        $kode = strtoupper(trim((string)$this->input->post('kode', TRUE)));
        $kode = preg_replace('/[^A-Z0-9_-]+/', '_', $kode);
        $data = [
            'kode' => $kode,
            'nama' => trim((string)$this->input->post('nama', TRUE)),
            'keterangan' => trim((string)$this->input->post('keterangan', TRUE)),
            'urutan' => max(0, (int)$this->input->post('urutan')),
            'is_active' => (int)$this->input->post('is_active') === 1 ? 1 : 0,
        ];
        if (!$data['kode'] || !$data['nama']) $this->respond(false, 'Kode dan nama jenis anggaran wajib diisi.', [], [], 422);
        $this->db->where('kode', $data['kode']);
        if ($id) $this->db->where('id !=', $id);
        if ($this->db->count_all_results('jenis_anggaran')) $this->respond(false, 'Kode jenis anggaran sudah digunakan.', [], [], 422);

        if ($id) $this->db->where('id', $id)->update('jenis_anggaran', $data);
        else { $this->db->insert('jenis_anggaran', $data); $id = (int)$this->db->insert_id(); }
        $this->Audit_model->write('JENIS_ANGGARAN', $id, 'SIMPAN', [], $data, (int)$this->user['id']);
        $this->respond(true, 'Jenis anggaran berhasil disimpan.', ['id' => encrypt_id($id)]);
    }
}

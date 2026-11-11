<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis_biaya extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Jenis_biaya_model');
    }

    public function index(): void {
        $this->require_permission('jenis_biaya.view');
        $this->load->view('layout/main', array(
            'title' => 'Jenis Biaya',
            'view'  => 'jenis_biaya/index',
            'data'  => array(
                'rows'       => array(),
                'can_manage' => can('jenis_biaya.manage')
            )
        ));
    }

    public function data(): void {
        $this->require_permission('jenis_biaya.view');
        $canManage = can('jenis_biaya.manage');

        $columnMap = array(
            0 => 'kode',
            1 => 'nama',
            2 => 'satuan',
            3 => 'tampil_di_kegiatan'
        );

        $params = $this->dt_params($columnMap, 'nama', 'ASC');
        $recordsTotal = $this->Jenis_biaya_model->count_all();
        $recordsFiltered = $this->Jenis_biaya_model->count_filtered($params['search']);
        $rows = $this->Jenis_biaya_model->get_datatable(
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $data[] = array(
                '<code>' . html_escape($row['kode']) . '</code>',
                '<span class="fw-medium">' . html_escape($row['nama']) . '</span>',
                '<span class="badge text-bg-light border">' . html_escape($row['satuan'] ?: '-') . '</span>',
                !empty($row['tampil_di_kegiatan'])
                    ? '<span class="badge text-bg-success">Tampil</span>'
                    : '<span class="badge text-bg-secondary">Tidak tampil</span>',
                '<span class="small text-muted">' . html_escape($row['keterangan'] ?: '-') . '</span>'
            );
            if ($canManage) {
                $rowEnc = $row;
                $rowEnc['id'] = encrypt_id($row['id']);
                $rowJson = htmlspecialchars(json_encode($rowEnc), ENT_QUOTES, 'UTF-8');
                $data[count($data) - 1][] = '<div class="text-end"><button class="btn btn-sm btn-outline-primary js-edit-jenis" data-row=\'' . $rowJson . '\'><i class="bi bi-pencil"></i></button></div>';
            }
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    public function save(): void {
        $this->require_permission('jenis_biaya.manage');
        $data = array(
            'kode'               => $this->input->post('kode', TRUE),
            'nama'               => $this->input->post('nama', TRUE),
            'satuan'             => $this->input->post('satuan', TRUE),
            'tampil_di_kegiatan' => (int)$this->input->post('tampil_di_kegiatan') === 1 ? 1 : 0,
            'keterangan'         => trim((string)$this->input->post('keterangan', TRUE)),
        );
        if (!$data['kode'] || !$data['nama']) {
            $this->respond(FALSE, 'Kode dan nama wajib diisi.', array(), array(), 422);
        }
        $saved = $this->Jenis_biaya_model->save($data, (int)$this->input->post('id'));
        $this->respond(TRUE, 'Jenis biaya tersimpan', array('id' => $saved));
    }
}

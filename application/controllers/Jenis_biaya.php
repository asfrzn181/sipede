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

        $columnMap = array(
            0 => 'kode',
            1 => 'nama',
            2 => 'satuan'
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
                '<span class="badge text-bg-light border">' . html_escape($row['satuan']) . '</span>'
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    public function save(): void {
        $this->require_permission('jenis_biaya.manage');
        $data = array(
            'kode'   => $this->input->post('kode', TRUE),
            'nama'   => $this->input->post('nama', TRUE),
            'satuan' => $this->input->post('satuan', TRUE)
        );
        if (!$data['kode'] || !$data['nama']) {
            $this->respond(FALSE, 'Kode dan nama wajib diisi.', array(), array(), 422);
        }
        $saved = $this->Jenis_biaya_model->save($data, (int)$this->input->post('id'));
        $this->respond(TRUE, 'Jenis biaya tersimpan', array('id' => $saved));
    }
}

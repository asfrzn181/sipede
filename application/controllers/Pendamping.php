<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pendamping extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Pendamping_model');
    }

    public function index(): void {
        $this->require_permission('penerima.view');
        $this->load->view('layout/main', array(
            'title' => 'Master Pendamping',
            'view'  => 'pendamping/index',
            'data'  => array(
                'rows'       => array(),
                'can_manage' => can('penerima.manage'),
            )
        ));
    }

    public function data(): void {
        $this->require_permission('penerima.view');
        $canManage = can('penerima.manage');

        $columnMap = array(0 => 'nama', 1 => 'jabatan', 2 => 'is_active');
        $params = $this->dt_params($columnMap, 'nama', 'ASC');

        $recordsTotal = $this->Pendamping_model->count_all();
        $recordsFiltered = $this->Pendamping_model->count_filtered($params['search']);
        $rows = $this->Pendamping_model->get_datatable(
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $rowEnc = $row;
            $rowEnc['id'] = encrypt_id($row['id']);
            $rowEncJson = htmlspecialchars(json_encode($rowEnc), ENT_QUOTES, 'UTF-8');

            $namaHtml = '<span class="fw-semibold">' . html_escape($row['nama']) . '</span>';
            $statusBadge = !empty($row['is_active']) ? 'success' : 'secondary';
            $statusLabel = !empty($row['is_active']) ? 'Aktif' : 'Nonaktif';
            $statusHtml = '<span class="badge text-bg-' . $statusBadge . '">' . $statusLabel . '</span>';

            $rowArr = array($namaHtml, $statusHtml);
            if ($canManage) {
                $rowArr[] = '<div class="text-end">'
                    . '<button class="btn btn-sm btn-outline-primary js-edit-pendamping" data-row=\'' . $rowEncJson . '\'>'
                    . '<i class="bi bi-pencil"></i></button></div>';
            }

            $data[] = $rowArr;
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    public function save(): void {
        $this->require_permission('penerima.manage');
        $id = decrypt_id($this->input->post('id'));
        $data = array(
            'nama'      => $this->input->post('nama', TRUE),
            'jabatan'   => $this->input->post('jabatan', TRUE),
            'is_active' => (int)$this->input->post('is_active'),
        );
        if (!$data['nama']) {
            $this->respond(FALSE, 'Nama wajib diisi.', array(), array('nama' => 'Nama wajib diisi.'), 422);
        }
        $savedId = $this->Pendamping_model->save($data, $id);
        $this->respond(TRUE, 'Pendamping tersimpan', array('id' => encrypt_id($savedId)));
    }
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Penerima extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Penerima_model');
    }

    public function index(): void {
        // Pendamping dikelola sebagai user ber-role PENDAMPING. Menu ini
        // khusus untuk master anggota DPRD.
        $this->render_index('DPRD');
    }

    private function render_index(?string $filterTipe): void {
        $this->require_permission('penerima.view');
        $this->load->view('layout/main', array(
            'title' => 'Anggota DPRD',
            'view'  => 'penerima/index',
            'data'  => array(
                'rows'         => array(),
                'can_manage'   => can('penerima.manage'),
                'filter_tipe'  => $filterTipe,
            )
        ));
    }

    public function data(): void {
        $this->require_permission('penerima.view');
        $canManage = can('penerima.manage');
        $filterTipe = 'DPRD';

        $columnMap = $filterTipe
            ? array(0 => 'nama', 1 => 'jabatan', 2 => 'periode_mulai', 3 => 'is_active')
            : array(0 => 'nama', 1 => 'tipe', 2 => 'jabatan', 3 => 'periode_mulai', 4 => 'is_active');

        $params = $this->dt_params($columnMap, 'nama', 'ASC');
        $recordsTotal = $this->Penerima_model->count_all($filterTipe);
        $recordsFiltered = $this->Penerima_model->count_filtered($params['search'], $filterTipe);
        $rows = $this->Penerima_model->get_datatable(
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir'],
            $filterTipe
        );

        $data = array();
        foreach ($rows as $row) {
            $rowEnc = $row;
            $rowEnc['id'] = encrypt_id($row['id']);
            $rowEncJson = htmlspecialchars(json_encode($rowEnc), ENT_QUOTES, 'UTF-8');

            $namaHtml = '<span class="fw-semibold">' . html_escape($row['nama']) . '</span>';
            $tipeBadgeClass = $row['tipe'] === 'DPRD' ? 'text-bg-primary bg-opacity-10 text-primary border' : 'text-bg-secondary bg-opacity-10 text-secondary border';
            $tipeHtml = '<span class="badge ' . $tipeBadgeClass . '">' . html_escape($row['tipe']) . '</span>';
            $jabatanHtml = html_escape($row['jabatan'] ?: '-');

            $periodeHtml = '&mdash;';
            if (!empty($row['periode_mulai']) && !empty($row['periode_akhir'])) {
                $periodeHtml = (int)$row['periode_mulai'] . ' &ndash; ' . (int)$row['periode_akhir'];
            } elseif (!empty($row['periode_mulai'])) {
                $periodeHtml = 'ab ' . (int)$row['periode_mulai'];
            }
            $periodeHtml = '<span class="text-nowrap small text-muted">' . $periodeHtml . '</span>';

            $statusBadge = $row['is_active'] ? 'success' : 'secondary';
            $statusLabel = $row['is_active'] ? 'Aktif' : 'Nonaktif';
            $statusHtml = '<span class="badge text-bg-' . $statusBadge . '">' . $statusLabel . '</span>';

            $rowArr = array($namaHtml);
            if (!$filterTipe) $rowArr[] = $tipeHtml;
            $rowArr[] = $jabatanHtml;
            $rowArr[] = $periodeHtml;
            $rowArr[] = $statusHtml;

            if ($canManage) {
                $rowArr[] = '<div class="text-end">'
                    . '<button class="btn btn-sm btn-outline-primary js-edit-penerima" data-row=\'' . $rowEncJson . '\'>'
                    . '<i class="bi bi-pencil"></i></button></div>';
            }

            $data[] = $rowArr;
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    public function save(): void {
        $this->require_permission('penerima.manage');
        $id   = decrypt_id($this->input->post('id'));
        $data = array(
            'nama'          => $this->input->post('nama', TRUE),
            'tipe'          => 'DPRD',
            'jabatan'       => $this->input->post('jabatan', TRUE),
            'periode_mulai' => $this->input->post('periode_mulai') ? (int)$this->input->post('periode_mulai') : NULL,
            'periode_akhir' => $this->input->post('periode_akhir') ? (int)$this->input->post('periode_akhir') : NULL,
            'is_active'     => (int)$this->input->post('is_active'),
        );
        if (!$data['nama']) {
            $this->respond(FALSE, 'Nama wajib diisi.', array(), array('nama' => 'Nama wajib diisi.'), 422);
        }
        $savedId = $this->Penerima_model->save($data, $id);
        $this->respond(TRUE, 'Penerima tersimpan', array('id' => encrypt_id($savedId)));
    }
}

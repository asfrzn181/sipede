<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Audit extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->require_permission('audit.view');
        $this->load->model('Audit_model');
    }

    public function index(): void {
        $this->load->view('layout/main', array(
            'title' => 'History Verifikasi & Audit',
            'view'  => 'audit/index',
            'data'  => array('rows' => array())
        ));
    }

    public function data(): void {
        $this->require_permission('audit.view');

        $columnMap = array(
            0 => 'a.created_at',
            1 => 'u.nama',
            2 => 'a.entity',
            3 => 'a.action',
            4 => 'a.entity_id',
        );

        $params = $this->dt_params($columnMap, 'a.created_at', 'DESC');
        $recordsTotal = $this->Audit_model->count_all();
        $recordsFiltered = $this->Audit_model->count_filtered($params['search']);
        $rows = $this->Audit_model->get_datatable(
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $actionBadge = 'secondary';
            if ($row['action'] === 'REVISI') $actionBadge = 'warning';
            elseif ($row['action'] === 'SIMPAN') $actionBadge = 'success';
            elseif ($row['action'] === 'KUNCI') $actionBadge = 'danger';
            elseif ($row['action'] === 'BUKA') $actionBadge = 'info';

            $waktuHtml = '<span class="text-nowrap small text-muted">' . html_escape($row['created_at']) . '</span>';
            $userHtml = '<span class="fw-medium">' . html_escape($row['nama'] ?: 'System') . '</span>';
            $entityHtml = '<span class="badge text-bg-light border">' . html_escape($row['entity']) . '</span>';
            $actionHtml = '<span class="badge text-bg-' . $actionBadge . '">' . html_escape($row['action']) . '</span>';
            $idHtml = '<code>#' . (int)$row['entity_id'] . '</code>';

            $oldEsc = htmlspecialchars($row['old_data'] ?: '{}', ENT_QUOTES, 'UTF-8');
            $newEsc = htmlspecialchars($row['new_data'] ?: '{}', ENT_QUOTES, 'UTF-8');

            $aksiHtml = '<div class="text-end">'
                . '<button class="btn btn-sm btn-outline-secondary js-view-audit"'
                . ' data-waktu="' . html_escape($row['created_at']) . '"'
                . ' data-user="' . html_escape($row['nama'] ?: 'System') . '"'
                . ' data-entity="' . html_escape($row['entity']) . '"'
                . ' data-action="' . html_escape($row['action']) . '"'
                . ' data-old=\'' . $oldEsc . '\''
                . ' data-new=\'' . $newEsc . '\'>'
                . '<i class="bi bi-eye"></i>'
                . '</button></div>';

            $data[] = array(
                $waktuHtml,
                $userHtml,
                $entityHtml,
                $actionHtml,
                $idHtml,
                $aksiHtml
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }
}

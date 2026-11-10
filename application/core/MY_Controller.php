<?php defined('BASEPATH') OR exit('No direct script access allowed');

class MY_Controller extends CI_Controller
{
    public array $user = array();
    public function __construct()
    {
        parent::__construct();
        $this->load->library('rbac');
        $this->user = (array) $this->session->userdata('user');
        if (!$this->user) {
            $this->user = array();
        }
        $this->load->vars(array('currentUser' => $this->user));
    }

    protected function require_login(): void
    {
        if (!$this->user) {
            if ($this->input->is_ajax_request()) {
                json_response(FALSE, 'Sesi berakhir. Silakan masuk kembali.', array(), array(), 401);
            }
            redirect('auth');
        }
        $this->rbac->load((int)($this->user['id'] ?? 0));
    }

    protected function require_permission(string $permission): void
    {
        if (!$this->rbac->can($permission)) $this->deny();
    }

    protected function require_any(array $permissions): void
    {
        if (!$this->rbac->can_any($permissions)) $this->deny();
    }

    protected function deny(): void
    {
        if ($this->input->is_ajax_request()) json_response(FALSE, 'Anda tidak memiliki hak akses.', array(), array(), 403);
        show_error('Anda tidak memiliki hak akses.', 403);
    }

    protected function own_penerima_id(): ?int
    {
        $id = $this->user['penerima_id'] ?? null;
        return $id ? (int)$id : null;
    }

    protected function respond(bool $status, string $message, array $data = array(), array $errors = array(), int $code = 200): void
    {
        json_response($status, $message, $data, $errors, $code);
    }

    /**
     * Helper untuk mengambil parameter DataTables Server-Side Processing
     */
    protected function dt_params(array $columnMap = array(), string $defaultCol = 'id', string $defaultDir = 'DESC'): array
    {
        $input = array_merge((array)$this->input->post(), (array)$this->input->get());
        if (empty($input['draw'])) {
            $json = input_json();
            if (is_array($json) && !empty($json)) {
                $input = array_merge($input, $json);
            }
        }

        $draw   = (int)($input['draw'] ?? 1);
        $start  = max(0, (int)($input['start'] ?? 0));
        $length = (int)($input['length'] ?? 10);
        if ($length < 0) $length = 1000;

        $search = '';
        if (isset($input['search'])) {
            if (is_array($input['search'])) {
                $search = trim((string)($input['search']['value'] ?? ''));
            } else {
                $search = trim((string)$input['search']);
            }
        }

        $orderCol = $defaultCol;
        $orderDir = strtoupper($defaultDir) === 'ASC' ? 'ASC' : 'DESC';

        if (!empty($input['order']) && is_array($input['order'])) {
            $firstOrder = $input['order'][0] ?? null;
            if (is_array($firstOrder)) {
                $colIdx = (int)($firstOrder['column'] ?? 0);
                $dir    = strtoupper((string)($firstOrder['dir'] ?? ''));
                if (isset($columnMap[$colIdx]) && !empty($columnMap[$colIdx])) {
                    $orderCol = $columnMap[$colIdx];
                    $orderDir = ($dir === 'ASC') ? 'ASC' : 'DESC';
                }
            }
        }

        return array(
            'draw'         => $draw,
            'start'        => $start,
            'length'       => $length,
            'search'       => $search,
            'order_column' => $orderCol,
            'order_dir'    => $orderDir,
            'raw_input'    => $input
        );
    }

    /**
     * Helper untuk mengembalikan response JSON DataTables Server-Side Processing
     */
    protected function respond_dt(int $draw, int $recordsTotal, int $recordsFiltered, array $data, array $extra = array()): void
    {
        $response = array_merge(array(
            'draw'            => $draw,
            'recordsTotal'    => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data'            => $data,
            'csrf_name'       => $this->security->get_csrf_token_name(),
            'csrf_hash'       => $this->security->get_csrf_hash()
        ), $extra);

        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($response));
    }
}

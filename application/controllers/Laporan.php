<?php defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Laporan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_login();
        $this->load->model(array('Laporan_model', 'Penerima_model', 'Jenis_biaya_model', 'Tahun_model'));
    }

    public function index(): void
    {
        $this->require_any(array('laporan.view_all', 'laporan.view_own'));
        $this->load->view('layout/main', array(
            'title' => 'Laporan per Komponen Biaya',
            'view'  => 'laporan/index',
            'data'  => array(
                'penerima'   => $this->Penerima_model->all(),
                'jenis'      => $this->Jenis_biaya_model->all(),
                'tahunList'  => $this->Tahun_model->all(),
                'can_export' => can('laporan.export')
            )
        ));
    }

    private function tahun_laporan(): array
    {
        $tahunList = $this->Tahun_model->all();
        $requested = (int)($this->input->get('tahun_anggaran_id') ?: $this->session->userdata('tahun_aktif_id'));
        $selected = 0;
        foreach ($tahunList as $tahun) {
            if ((int)$tahun['id'] === $requested) {
                $selected = (int)$tahun['id'];
                break;
            }
        }
        if (!$selected && !empty($tahunList)) {
            $selected = (int)$tahunList[0]['id'];
        }
        $label = '-';
        foreach ($tahunList as $tahun) {
            if ((int)$tahun['id'] === $selected) {
                $label = (string)$tahun['tahun'];
                break;
            }
        }
        return array($tahunList, $selected, $label);
    }

    public function anggota(): void
    {
        $this->require_any(array('laporan.view_all', 'laporan.view_own'));
        list($tahunList, $tahunId, $tahunLabel) = $this->tahun_laporan();
        $this->load->view('layout/main', array(
            'title' => 'Laporan per Anggota DPRD',
            'view'  => 'laporan/anggota',
            'data'  => array(
                'tahunList' => $tahunList,
                'tahunId'   => $tahunId,
                'tahunLabel'=> $tahunLabel,
                'rows'      => $this->Laporan_model->ringkasan_anggota($tahunId, $this->rbac->scope('laporan'), $this->own_penerima_id())
            )
        ));
    }

    public function pos(): void
    {
        $this->require_any(array('laporan.view_all', 'laporan.view_own'));
        list($tahunList, $tahunId, $tahunLabel) = $this->tahun_laporan();
        $this->load->view('layout/main', array(
            'title' => 'Laporan per Pos Anggaran',
            'view'  => 'laporan/pos',
            'data'  => array(
                'tahunList' => $tahunList,
                'tahunId'   => $tahunId,
                'tahunLabel'=> $tahunLabel,
                'rows'      => $this->Laporan_model->ringkasan_pos($tahunId, $this->rbac->scope('laporan'), $this->own_penerima_id())
            )
        ));
    }

    public function riwayat(): void
    {
        $this->require_any(array('kegiatan.verify', 'audit.view'));
        $this->load->view('layout/main', array(
            'title' => 'History Verifikasi & Audit',
            'view'  => 'laporan/riwayat',
            'data'  => array()
        ));
    }

    private function get_filter(): array
    {
        $input = array_merge((array)$this->input->get(), (array)$this->input->post());
        $json = input_json();
        if (is_array($json) && !empty($json)) {
            $input = array_merge($input, $json);
        }

        $filter = array(
            'tahun_anggaran_id' => (int)($input['tahun_anggaran_id'] ?? $this->session->userdata('tahun_aktif_id')),
            'status'            => trim((string)($input['status'] ?? '')),
            'tanggal_mulai'     => trim((string)($input['tanggal_mulai'] ?? '')),
            'tanggal_selesai'   => trim((string)($input['tanggal_selesai'] ?? '')),
            'tempat'            => trim((string)($input['tempat'] ?? '')),
            'penerima_id'       => $input['penerima_id'] ?? array(),
            'jenis_biaya_id'    => $input['jenis_biaya_id'] ?? array(),
        );

        if ($this->rbac->scope('laporan') === 'own') {
            $filter['penerima_id'] = array($this->own_penerima_id());
        }

        return $filter;
    }

    /**
     * DataTables Server-Side Processing Endpoint
     */
    public function data(): void
    {
        $this->require_any(array('laporan.view_all', 'laporan.view_own'));

        $scope = $this->rbac->scope('laporan');
        $ownId = $this->own_penerima_id();
        $filter = $this->get_filter();

        $columnMap = array(
            0 => 'd.no_surat_tugas',
            1 => 'd.tanggal_mulai',
            2 => 'd.tema',
            3 => 'd.penerima_nama',
            4 => 'd.jabatan',
            5 => 'd.sumber_dana_nama',
            6 => 'd.jenis_biaya_nama',
            7 => 'd.subtotal',
        );

        $params = $this->dt_params($columnMap, 'd.tanggal_mulai', 'DESC');

        $recordsTotal = $this->Laporan_model->count_all($filter, $scope, $ownId);
        $recordsFiltered = $this->Laporan_model->count_filtered($filter, $scope, $ownId, $params['search']);
        $rows = $this->Laporan_model->get_datatable(
            $filter,
            $scope,
            $ownId,
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $data[] = array(
                '<code>' . html_escape($row['no_surat_tugas'] ?: '-') . '</code>',
                html_escape(tgl_indo($row['tanggal_mulai']) ?: '-'),
                '<span class="fw-semibold">' . html_escape($row['tema'] ?: $row['nama_kegiatan'] ?: '-') . '</span>',
                html_escape($row['penerima_nama'] ?: '-'),
                html_escape($row['jabatan'] ?: '-'),
                '<span class="badge text-bg-light border">' . html_escape($row['sumber_dana_nama'] ?: '-') . '</span>',
                html_escape($row['jenis_biaya_nama'] ?: '-'),
                '<div class="text-end fw-bold text-primary">' . rupiah($row['subtotal']) . '</div>',
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    public function export(string $format = 'excel'): void
    {
        $this->require_permission('laporan.export');
        $filter = $this->get_filter();
        $rows = $this->Laporan_model->detail($filter, $this->rbac->scope('laporan'), $this->own_penerima_id());

        if ($format === 'pdf') {
            $this->load->model('Pengaturan_model');
            $this->load->view('laporan/export_pdf', array(
                'rows'    => $rows,
                'setting' => $this->Pengaturan_model->get(),
                'filter'  => $filter
            ));
            return;
        }

        $sheet = (new Spreadsheet())->getActiveSheet();
        $headers = array('No. Surat Tugas', 'Tanggal Mulai', 'Tanggal Selesai', 'Tema / Kegiatan', 'Tempat', 'Penerima', 'Tipe', 'Jabatan', 'Sumber Dana', 'Jenis Biaya', 'Volume', 'Tarif', 'Subtotal', 'Status');
        
        foreach ($headers as $colIdx => $header) {
            $sheet->setCellValueByColumnAndRow($colIdx + 1, 1, $header);
        }

        foreach ($rows as $r => $row) {
            $rowNum = $r + 2;
            $sheet->setCellValueByColumnAndRow(1, $rowNum, $row['no_surat_tugas'] ?? '-');
            $sheet->setCellValueByColumnAndRow(2, $rowNum, $row['tanggal_mulai'] ?? '-');
            $sheet->setCellValueByColumnAndRow(3, $rowNum, $row['tanggal_selesai'] ?? '-');
            $sheet->setCellValueByColumnAndRow(4, $rowNum, $row['tema'] ?? $row['nama_kegiatan'] ?? '-');
            $sheet->setCellValueByColumnAndRow(5, $rowNum, $row['tempat'] ?? '-');
            $sheet->setCellValueByColumnAndRow(6, $rowNum, $row['penerima_nama'] ?? '-');
            $sheet->setCellValueByColumnAndRow(7, $rowNum, $row['tipe'] ?? '-');
            $sheet->setCellValueByColumnAndRow(8, $rowNum, $row['jabatan'] ?? '-');
            $sheet->setCellValueByColumnAndRow(9, $rowNum, $row['sumber_dana_nama'] ?? '-');
            $sheet->setCellValueByColumnAndRow(10, $rowNum, $row['jenis_biaya_nama'] ?? '-');
            $sheet->setCellValueByColumnAndRow(11, $rowNum, (float)($row['volume'] ?? 0));
            $sheet->setCellValueByColumnAndRow(12, $rowNum, (float)($row['tarif'] ?? 0));
            $sheet->setCellValueByColumnAndRow(13, $rowNum, (float)($row['subtotal'] ?? 0));
            $sheet->setCellValueByColumnAndRow(14, $rowNum, $row['status'] ?? '-');
        }

        $writer = new Xlsx($sheet->getParent());
        $this->output
            ->set_content_type('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->set_header('Content-Disposition: attachment; filename="laporan_realisasi_' . date('Ymd_His') . '.xlsx"');
        ob_start();
        $writer->save('php://output');
        $this->output->set_output(ob_get_clean());
    }
}

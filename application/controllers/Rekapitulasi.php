<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Rekapitulasi extends MY_Controller
{
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(array('Kegiatan_model', 'Penerima_model', 'Tahun_model', 'Pengaturan_model'));
    }

    private function get_filter(): array {
        $input = array_merge((array)$this->input->get(), (array)$this->input->post());
        $json = input_json();
        if (is_array($json) && !empty($json)) {
            $input = array_merge($input, $json);
        }

        $rawPenerima = $input['penerima_id'] ?? null;
        $penerimaId = null;
        if (!empty($rawPenerima)) {
            $penerimaId = is_numeric($rawPenerima) ? (int)$rawPenerima : decrypt_id($rawPenerima);
        }

        return array(
            'tanggal_mulai'   => $input['tanggal_mulai'] ?? date('Y-01-01'),
            'tanggal_selesai' => $input['tanggal_selesai'] ?? date('Y-m-d'),
            'status'          => trim((string)($input['status'] ?? '')),
            'tipe'            => trim((string)($input['tipe'] ?? '')),
            'tempat'          => trim((string)($input['tempat'] ?? '')),
            'penerima_id'     => $penerimaId,
        );
    }

    /**
     * Menu Khusus Rekapitulasi Perjalanan Dinas per Periode Date Range
     */
    public function index(): void {
        $this->require_any(array('kegiatan.view_all', 'kegiatan.view_own', 'laporan.view_all', 'laporan.view_own'));

        $filter = $this->get_filter();
        $rows = $this->Kegiatan_model->rekapitulasi_periode($filter);

        // Hitung metrik ringkasan
        $totalKegiatan   = count($rows);
        $totalPeserta    = 0;
        $totalDprd       = 0;
        $totalPendamping = 0;
        $totalBiaya      = 0.0;

        foreach ($rows as $r) {
            $totalPeserta    += (int)$r['jumlah_peserta'];
            $totalDprd       += (int)$r['jumlah_dprd'];
            $totalPendamping += (int)$r['jumlah_pendamping'];
            $totalBiaya      += (float)$r['total'];
        }

        $this->load->view('layout/main', array(
            'title' => 'Rekapitulasi Perjalanan Dinas',
            'view'  => 'rekapitulasi/index',
            'data'  => array(
                'filter'          => $filter,
                'rows'            => $rows,
                'totalKegiatan'   => $totalKegiatan,
                'totalPeserta'    => $totalPeserta,
                'totalDprd'       => $totalDprd,
                'totalPendamping' => $totalPendamping,
                'totalBiaya'      => $totalBiaya,
                'penerimaList'    => $this->Penerima_model->all(),
                'setting'         => $this->Pengaturan_model->get(),
            )
        ));
    }

    /**
     * DataTables Server-Side Processing Endpoint untuk Rekapitulasi
     */
    public function data(): void {
        $this->require_any(array('kegiatan.view_all', 'kegiatan.view_own', 'laporan.view_all', 'laporan.view_own'));

        $filter = $this->get_filter();
        $columnMap = array(
            0 => 'k.id',
            1 => 'k.no_surat_tugas',
            2 => 'k.tanggal_mulai',
            3 => 'k.tema',
            4 => 'k.tempat',
            5 => 'jumlah_peserta',
            6 => 'k.total',
            7 => 'k.status'
        );

        $params = $this->dt_params($columnMap, 'k.tanggal_mulai', 'DESC');

        $recordsTotal = $this->Kegiatan_model->rekapitulasi_count_all($filter);
        $recordsFiltered = $this->Kegiatan_model->rekapitulasi_count_filtered($filter, $params['search']);
        $rows = $this->Kegiatan_model->rekapitulasi_datatable(
            $filter,
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        $no = $params['start'] + 1;
        foreach ($rows as $row) {
            $encId = encrypt_id($row['id']);
            $bClass = $row['status'] === 'BATAL' ? 'danger' : ($row['status'] === 'AKTIF' ? 'success' : ($row['status'] === 'DIAJUKAN' ? 'warning' : 'secondary'));

            $noSuratHtml = '<code>' . html_escape($row['no_surat_tugas'] ?: '-') . '</code>';
            if (!empty($row['tanggal_surat_tugas'])) {
                $noSuratHtml .= '<div class="small text-muted">' . tgl_indo($row['tanggal_surat_tugas']) . '</div>';
            }

            $temaHtml = '<div class="fw-semibold"><a href="' . site_url('kegiatan/rincian/' . $encId) . '" class="text-dark text-decoration-none">'
                . html_escape($row['tema']) . '</a>';
            if (!empty($row['daftar_peserta'])) {
                $temaHtml .= '<div class="text-muted small fw-normal text-truncate" style="max-width: 250px;" title="' . html_escape($row['daftar_peserta']) . '">'
                    . '<i class="bi bi-people me-1"></i>' . html_escape($row['daftar_peserta']) . '</div>';
            }
            $temaHtml .= '</div>';

            $tglHtml = '<div class="small text-nowrap">' . tgl_indo($row['tanggal_mulai']) . '<br><span class="text-muted">s/d ' . tgl_indo($row['tanggal_selesai']) . '</span></div>';
            $tempatHtml = '<i class="bi bi-geo-alt text-danger me-1"></i>' . html_escape($row['tempat'] ?: '-');

            $pesertaHtml = '<div class="text-center">'
                . '<span class="badge text-bg-light border fw-bold">' . (int)$row['jumlah_peserta'] . ' Org</span>'
                . '<div class="text-muted" style="font-size: 0.72rem;">'
                . '<span class="text-primary">' . (int)$row['jumlah_dprd'] . ' DPRD</span> &bull; '
                . '<span class="text-secondary">' . (int)$row['jumlah_pendamping'] . ' Pend</span>'
                . '</div></div>';

            $totalHtml = '<div class="text-end fw-bold text-primary">' . rupiah($row['total']) . '</div>';
            $statusHtml = '<div class="text-center"><span class="badge text-bg-' . $bClass . '">' . html_escape($row['status']) . '</span></div>';

            $aksiHtml = '<div class="text-end text-nowrap d-print-none"><div class="btn-group btn-group-sm">'
                . '<a href="' . site_url('kegiatan/bukti_rekap/' . $encId) . '" class="btn btn-outline-info" title="Bukti Rekapitulasi"><i class="bi bi-receipt"></i> Bukti</a>'
                . '<a href="' . site_url('kegiatan/rincian/' . $encId) . '" class="btn btn-outline-primary" title="Rincian Kegiatan"><i class="bi bi-arrow-right-short"></i></a>'
                . '</div></div>';

            $data[] = array(
                '<div class="text-center">' . ($no++) . '</div>',
                $noSuratHtml,
                $tglHtml,
                $temaHtml,
                $tempatHtml,
                $pesertaHtml,
                $totalHtml,
                $statusHtml,
                $aksiHtml
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }
}

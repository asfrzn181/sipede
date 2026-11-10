<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(array('Kegiatan_model', 'Pos_model', 'Tahun_model'));
    }

    public function index(): void {
        $this->require_any(array('dashboard.view_all', 'dashboard.view_own'));
        $year  = (int)$this->session->userdata('tahun_aktif_id');
        $scope = $this->rbac->scope('dashboard');
        $pos   = null;

        if ($scope === 'own') {
            $pos = $this->Pos_model->pos_default_penerima($this->own_penerima_id(), $year);
        }

        $this->load->view('layout/main', array(
            'title' => 'Dashboard',
            'view'  => 'dashboard/index',
            'data'  => array('pos' => $pos, 'scope' => $scope)
        ));
    }

    public function data(): void {
        $this->require_any(array('dashboard.view_all', 'dashboard.view_own'));
        $year  = (int)$this->session->userdata('tahun_aktif_id');
        $scope = $this->rbac->scope('dashboard');

        if ($scope === 'own') {
            $ownId = $this->own_penerima_id();
            $pos = $this->Pos_model->pos_default_penerima($ownId, $year);

            if ($pos) {
                $saldo = $this->Pos_model->saldo_pos((int)$pos['id']);
                $summary = array(
                    'pagu'      => (float)($saldo['pagu'] ?? 0),
                    'realisasi' => (float)($saldo['realisasi'] ?? 0),
                    'sisa'      => (float)($saldo['sisa'] ?? 0),
                    'pos_nama'  => $pos['nama']
                );
            } else {
                $summary = array('pagu' => 0, 'realisasi' => 0, 'sisa' => 0, 'pos_nama' => '-');
            }

            $monthly = $this->db->query('
                SELECT MONTH(k.tanggal_mulai) bulan, SUM(kb.volume*kb.tarif) total
                FROM kegiatan k
                JOIN kegiatan_peserta kp ON kp.kegiatan_id=k.id
                JOIN kegiatan_biaya kb ON kb.kegiatan_peserta_id=kp.id
                WHERE k.tahun_anggaran_id=? AND k.status="AKTIF" AND kp.penerima_id=?
                GROUP BY MONTH(k.tanggal_mulai) ORDER BY bulan
            ', array($year, $ownId))->result_array();

            $terkecil = array();
        } else {
            $summaryRow = $this->db->query('
                SELECT COALESCE(SUM(pagu),0) pagu, COALESCE(SUM(realisasi),0) realisasi, COALESCE(SUM(sisa),0) sisa
                FROM v_sisa_pos
                WHERE tahun_anggaran_id=?
            ', array($year))->row_array();

            $summary = array(
                'pagu'      => (float)($summaryRow['pagu'] ?? 0),
                'realisasi' => (float)($summaryRow['realisasi'] ?? 0),
                'sisa'      => (float)($summaryRow['sisa'] ?? 0),
                'pos_nama'  => 'Seluruh Pos Anggaran'
            );

            $monthly = $this->db->query('
                SELECT MONTH(tanggal_mulai) bulan, SUM(total) total
                FROM kegiatan
                WHERE tahun_anggaran_id=? AND status="AKTIF"
                GROUP BY MONTH(tanggal_mulai) ORDER BY bulan
            ', array($year))->result_array();

            $terkecil = $this->db->query('
                SELECT pos_nama AS nama, sisa, pagu, realisasi
                FROM v_sisa_pos
                WHERE tahun_anggaran_id=?
                ORDER BY sisa ASC
                LIMIT 5
            ', array($year))->result_array();
        }

        $this->respond(TRUE, 'OK', array(
            'summary'  => $summary,
            'monthly'  => $monthly,
            'terkecil' => $terkecil,
            'latest'   => $this->Kegiatan_model->latest(10, $scope, $this->own_penerima_id())
        ));
    }
}

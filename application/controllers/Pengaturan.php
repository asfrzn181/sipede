<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pengaturan extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_login();
        $this->load->model(array('Pengaturan_model', 'Audit_model'));
    }

    private function check_access(): void
    {
        if (!$this->rbac->is_super_admin() && !can('user.manage') && !can('setting.manage')) {
            $this->deny();
        }
    }

    /**
     * Halaman Pengaturan KOP Dokumen & Penandatangan
     */
    public function dokumen(): void
    {
        $this->check_access();
        $setting = $this->Pengaturan_model->get();

        $this->load->view('layout/main', array(
            'title' => 'Pengaturan KOP & Penandatangan Dokumen',
            'view'  => 'pengaturan/dokumen',
            'data'  => array(
                'setting' => $setting
            )
        ));
    }

    /**
     * Simpan Pengaturan KOP Dokumen & Pejabat Penandatangan
     */
    public function save_dokumen(): void
    {
        $this->check_access();

        $current = $this->Pengaturan_model->get();

        $data = array(
            'nama_pemda'        => trim((string)$this->input->post('nama_pemda', TRUE)),
            'nama_lembaga'      => trim((string)$this->input->post('nama_lembaga', TRUE)),
            'nama_skpd'         => trim((string)$this->input->post('nama_skpd', TRUE)),
            'alamat'            => trim((string)$this->input->post('alamat', TRUE)),
            'telepon'           => trim((string)$this->input->post('telepon', TRUE)),
            'fax'               => trim((string)$this->input->post('fax', TRUE)),
            'email'             => trim((string)$this->input->post('email', TRUE)),
            'website'           => trim((string)$this->input->post('website', TRUE)),
            'kode_pos'          => trim((string)$this->input->post('kode_pos', TRUE)),
            'kota_penetapan'    => trim((string)$this->input->post('kota_penetapan', TRUE)) ?: 'Kota',

            // Penandatangan 1: PPTK
            'pptk_label'        => trim((string)$this->input->post('pptk_label', TRUE)),
            'pptk_jabatan'      => trim((string)$this->input->post('pptk_jabatan', TRUE)),
            'pptk_nama'         => trim((string)$this->input->post('pptk_nama', TRUE)),
            'pptk_nip'          => trim((string)$this->input->post('pptk_nip', TRUE)),
            'pptk_pangkat'      => trim((string)$this->input->post('pptk_pangkat', TRUE)),

            // Penandatangan 2: Bendahara Pengeluaran
            'bendahara_label'   => trim((string)$this->input->post('bendahara_label', TRUE)),
            'bendahara_jabatan' => trim((string)$this->input->post('bendahara_jabatan', TRUE)),
            'bendahara_nama'    => trim((string)$this->input->post('bendahara_nama', TRUE)),
            'bendahara_nip'     => trim((string)$this->input->post('bendahara_nip', TRUE)),
            'bendahara_pangkat' => trim((string)$this->input->post('bendahara_pangkat', TRUE)),
        );

        if (empty($data['nama_skpd'])) {
            $this->respond(FALSE, 'Nama Perangkat Daerah (SKPD) wajib diisi.', array(), array('nama_skpd' => 'Wajib diisi.'), 422);
        }

        $this->Pengaturan_model->save($data);
        $this->Audit_model->write('PENGATURAN_DOKUMEN', 1, 'UPDATE', $current, $data, (int)($this->user['id'] ?? 0));

        $updatedSetting = $this->Pengaturan_model->get();

        $this->respond(TRUE, 'Pengaturan KOP Dokumen dan Penandatangan berhasil disimpan.', array(
            'setting' => $updatedSetting,
            'logoUrl' => $updatedSetting['logo_path']
        ));
    }
}


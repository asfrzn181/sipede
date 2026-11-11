<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Anggaran extends MY_Controller {
    public function __construct(){parent::__construct();$this->require_login();$this->load->model('Anggaran_model');}
    public function index():void{$this->require_any(array('anggaran.view_all','anggaran.view_own'));$year=(int)$this->session->userdata('tahun_aktif_id');redirect('pos/index/'.encrypt_id($year));}
    public function save():void{$this->require_permission('anggaran.manage');$year=(int)$this->session->userdata('tahun_aktif_id');$locked=$this->db->get_where('tahun_anggaran',array('id'=>$year,'is_locked'=>1))->row();if($locked)$this->respond(FALSE,'Tahun sudah dikunci.',array(),array(),422);$ok=$this->Anggaran_model->save($year,decrypt_id($this->input->post('penerima_id'))?:(int)$this->input->post('penerima_id'),clean_number($this->input->post('pagu')));$this->respond((bool)$ok,$ok?'Anggaran tersimpan':'Gagal menyimpan anggaran.');}
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Profil extends MY_Controller {
    public function __construct(){parent::__construct();$this->require_login();}
    public function index():void{$this->load->view('layout/main',array('title'=>'Profil','view'=>'profil/index','data'=>array('permissions'=>$this->rbac->permissions())));}
    public function password():void{$old=(string)$this->input->post('password_lama');$new=(string)$this->input->post('password_baru');$row=$this->db->get_where('users',array('id'=>(int)$this->user['id']))->row_array();if(!$row||!password_verify($old,$row['password']))$this->respond(FALSE,'Password lama salah.',array(),array(),422);if(strlen($new)<8)$this->respond(FALSE,'Password baru minimal 8 karakter.',array(),array(),422);$this->db->where('id',$row['id'])->update('users',array('password'=>password_hash($new,PASSWORD_DEFAULT),'wajib_ganti_password'=>0));$this->session->unset_userdata('user');$this->respond(TRUE,'Password berhasil diubah.',array('redirect'=>site_url('auth/logout')));}
}

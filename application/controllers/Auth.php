<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Auth extends MY_Controller
{
    public function __construct(){parent::__construct();$this->load->model('User_model');}

    public function index(): void
    {
        if ($this->user) {
            $dest = $this->rbac->can_any(array('dashboard.view_all', 'dashboard.view_own')) ? 'dashboard' : 'laporan';
            redirect($dest);
        }
        $this->load->view('layout/auth');
    }

    private function fail_login(string $message, int $code=422): void
    {
        if($this->input->is_ajax_request()){$this->respond(FALSE,$message,array(),array(),$code);}
        $this->session->set_flashdata('login_error',$message);
        session_write_close();
        redirect('auth');
    }

    public function login(): void
    {
        $this->form_validation->set_rules('username','Username','required|trim');
        $this->form_validation->set_rules('password','Password','required');
        if(!$this->form_validation->run())$this->fail_login('Username dan password wajib diisi.');
        $username=(string)$this->input->post('username',TRUE);
        $user=$this->User_model->by_username($username);
        if($user&&!empty($user['locked_until'])&&strtotime($user['locked_until'])>time())$this->fail_login('Akun terkunci sementara. Coba lagi nanti.',403);
        if(!$user||!password_verify((string)$this->input->post('password'),$user['password'])){
            if($user){$failures=(int)$user['login_failures']+1;$lock=$failures>=5?date('Y-m-d H:i:s',time()+900):NULL;$this->db->where('id',$user['id'])->update('users',array('login_failures'=>$failures,'locked_until'=>$lock));}
            $this->fail_login('Username atau password salah.',401);
        }
        $update=array('login_failures'=>0,'locked_until'=>NULL);if($this->User_model->has_column('terakhir_login'))$update['terakhir_login']=date('Y-m-d H:i:s');$this->db->where('id',$user['id'])->update('users',$update);
        unset($user['password']);$this->session->set_userdata('user',$user);$this->rbac->reload();
        $year=$this->db->order_by('tahun','DESC')->get_where('tahun_anggaran',array('is_locked'=>0))->row_array();if($year)$this->session->set_userdata('tahun_aktif_id',$year['id']);
        $target = $this->rbac->can_any(array('dashboard.view_all', 'dashboard.view_own')) ? 'dashboard' : 'laporan';
        if (!empty($user['wajib_ganti_password'])) {
            $this->session->set_flashdata('info', 'Anda menggunakan password default. Anda dapat menggantinya melalui menu Profil.');
        }
        if($this->input->is_ajax_request())$this->respond(TRUE,'Selamat datang.',array('redirect'=>site_url($target)));
        session_write_close();
        redirect($target);
    }

    public function logout(): void{$this->session->sess_destroy();session_write_close();redirect('auth');}
}

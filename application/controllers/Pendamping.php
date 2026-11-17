<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Pendamping extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_login();
        $this->load->model('Pendamping_model');
    }

    public function index(): void
    {
        $this->require_permission('penerima.view');
        $rows = $this->Pendamping_model->all();
        $this->load->view('layout/main', array(
            'title' => 'Master Pendamping',
            'view'  => 'pendamping/index',
            'data'  => array('rows' => $rows)
        ));
    }
}

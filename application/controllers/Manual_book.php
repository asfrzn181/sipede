<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Manual_book extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->require_login();
    }

    public function index(): void
    {
        $this->load->view('layout/main', array(
            'title' => 'Manual Book SIPEDE',
            'view'  => 'manual_book/index',
            'data'  => array()
        ));
    }
}

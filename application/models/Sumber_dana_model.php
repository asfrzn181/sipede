<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Sumber_dana_model extends CI_Model {
    public function all(): array { return $this->db->where('is_active',1)->order_by('nama')->get('sumber_dana')->result_array(); }
}

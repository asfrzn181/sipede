<?php defined('BASEPATH') OR exit('No direct script access allowed');
class Anggaran_model extends CI_Model {
    public function list_by_year(int $year, ?string $scope = 'all', ?int $ownId = null): array { $this->db->select('a.*, p.nama, p.tipe, p.jabatan')->from('anggaran a')->join('penerima p','p.id=a.penerima_id')->where('a.tahun_anggaran_id',$year); if($scope==='own' && $ownId)$this->db->where('a.penerima_id',$ownId); return $this->db->order_by('p.nama')->get()->result_array(); }
    public function save(int $year, int $penerima, float $pagu): bool { $row = $this->db->get_where('anggaran',array('tahun_anggaran_id'=>$year,'penerima_id'=>$penerima))->row_array(); if ($row) return $this->db->where('id',$row['id'])->update('anggaran',array('pagu'=>$pagu)); return $this->db->insert('anggaran',array('tahun_anggaran_id'=>$year,'penerima_id'=>$penerima,'pagu'=>$pagu)); }
}

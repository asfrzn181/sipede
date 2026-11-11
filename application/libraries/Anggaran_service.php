<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Anggaran_service
{
    private CI_Controller $ci;
    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function validate_and_save(array $payload, int $user_id): array
    {
        $this->ci->load->model(array('Kegiatan_model', 'Audit_model'));
        $this->ci->db->trans_begin();
        $year = (int) ($payload['tahun_anggaran_id'] ?? $this->ci->session->userdata('tahun_aktif_id'));
        $yearRow = $this->ci->db->get_where('tahun_anggaran', array('id' => $year))->row_array();
        if (!$yearRow) return $this->fail('Tahun anggaran tidak ditemukan.');
        if ((int) $yearRow['is_locked'] === 1) return $this->fail('Tahun anggaran sudah dikunci.');
        if (empty($payload['nama_kegiatan']) || empty($payload['tanggal_mulai']) || empty($payload['tanggal_selesai'])) {
            return $this->fail('Nama dan tanggal kegiatan wajib diisi.');
        }
        $peserta = $payload['peserta'] ?? array();
        if (!$peserta) return $this->fail('Minimal satu peserta wajib dipilih.');
        $ids = array_map('intval', array_column($peserta, 'penerima_id'));
        if (count($ids) !== count(array_unique($ids))) return $this->fail('Satu penerima hanya boleh sekali dalam kegiatan.');
        foreach ($peserta as $person) {
            $penerimaId = (int) ($person['penerima_id'] ?? 0);
            if (!empty($person['sumber_dana_id']) && !$this->ci->db->where(array('id'=>(int)$person['sumber_dana_id'],'is_active'=>1))->count_all_results('sumber_dana')) return $this->fail('Sumber dana peserta tidak ditemukan atau tidak aktif.');
            // Pos Anggaran adalah sumber pagu utama. Tabel anggaran hanya
            // menjadi fallback untuk data lama yang belum memiliki pos.
            $budget = $this->find_pos_budget($year, $penerimaId);
            if (!$budget) {
                $budget = $this->ci->db->query(
                    'SELECT * FROM v_sisa_anggaran WHERE tahun_anggaran_id = ? AND penerima_id = ?',
                    array($year, $penerimaId)
                )->row_array();
            }
            if (!$budget) return $this->fail('Pos Anggaran peserta belum tersedia untuk tahun kegiatan.');
            $total = 0;
            foreach (($person['biaya'] ?? array()) as $cost) {
                $total += (float) ($cost['volume'] ?? 0) * (float) ($cost['tarif'] ?? 0);
            }
            if ($this->ci->config->item('mode_saldo') === 'BLOKIR' && $total > (float) $budget['sisa']) {
                return $this->fail('Saldo anggaran peserta tidak mencukupi.');
            }
        }
        $id = $this->ci->Kegiatan_model->save_graph($payload, $user_id);
        if ($id === FALSE || !$this->ci->db->trans_status()) return $this->fail('Gagal menyimpan kegiatan.');
        $this->ci->Audit_model->write('KEGIATAN', $id, 'SIMPAN', array(), $payload, $user_id);
        $this->ci->db->trans_commit();
        return array('id' => $id, 'warnings' => array());
    }

    public function validate_existing(int $id, int $user_id): array
    {
        $row=$this->ci->db->get_where('kegiatan',array('id'=>$id))->row_array();
        if(!$row || $row['status']!=='DIAJUKAN') return array('error'=>'Kegiatan tidak berada dalam antrian verifikasi.');
        $year=$this->ci->db->get_where('tahun_anggaran',array('id'=>$row['tahun_anggaran_id']))->row_array();
        if(!$year || (int)$year['is_locked']===1) return array('error'=>'Tahun anggaran tidak ditemukan atau sudah dikunci.');
        $rows=$this->ci->db->select('kp.penerima_id, kp.pos_anggaran_id, SUM(kb.volume*kb.tarif) total')->from('kegiatan_peserta kp')->join('kegiatan_biaya kb','kb.kegiatan_peserta_id=kp.id')->where('kp.kegiatan_id',$id)->group_by('kp.id')->get()->result_array();
        foreach($rows as $item){
            $budget = null;
            if (!empty($item['pos_anggaran_id'])) {
                $budget = $this->ci->db->query('SELECT sisa FROM v_sisa_pos WHERE pos_anggaran_id = ?', array($item['pos_anggaran_id']))->row_array();
            }
            if (!$budget) {
                $budget = $this->ci->db->query('SELECT sisa FROM v_sisa_anggaran WHERE tahun_anggaran_id = ? AND penerima_id = ?', array($row['tahun_anggaran_id'], $item['penerima_id']))->row_array();
            }
            if(!$budget) return array('error'=>'Anggaran / Pos Anggaran peserta belum tersedia.');
            if($this->ci->config->item('mode_saldo')==='BLOKIR' && (float)$item['total'] > (float)$budget['sisa']) {
                return array('error'=>'Saldo Pos Anggaran tidak mencukupi untuk membiayai peserta.');
            }
        }
        return array('ok'=>true);
    }

    private function fail(string $message): array
    {
        $this->ci->db->trans_rollback();
        return array('error' => $message);
    }

    /** Ambil pos default aktif milik penerima untuk tahun berjalan. */
    private function find_pos_budget(int $year, int $penerimaId): ?array
    {
        $row = $this->ci->db->query(
            "SELECT vsp.*
             FROM v_sisa_pos vsp
             WHERE vsp.tahun_anggaran_id = ?
               AND (
                 (vsp.tipe = 'DPRD' AND vsp.penerima_id = ?)
                 OR (
                   vsp.tipe = 'PENDAMPING'
                   AND EXISTS (
                     SELECT 1 FROM penugasan_pos pp
                     WHERE pp.pos_anggaran_id = vsp.pos_anggaran_id
                       AND pp.penerima_id = ?
                       AND pp.tanggal_selesai IS NULL
                   )
                 )
               )
             ORDER BY CASE WHEN vsp.tipe = 'DPRD' THEN 0 ELSE 1 END
             LIMIT 1",
            array($year, $penerimaId, $penerimaId)
        )->row_array();

        return $row ?: null;
    }
}

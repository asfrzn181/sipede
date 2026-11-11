<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Anggaran_model extends CI_Model {
    public function list_by_year(int $year, ?string $scope = 'all', ?int $ownId = null): array {
        $this->db->select('a.*, p.nama, p.tipe, p.jabatan')
            ->from('anggaran a')
            ->join('penerima p', 'p.id=a.penerima_id')
            ->where('a.tahun_anggaran_id', $year);

        if ($scope === 'own' && $ownId) {
            $this->db->where('a.penerima_id', $ownId);
        }

        return $this->db->order_by('p.nama')->get()->result_array();
    }

    /**
     * Menyimpan pagu ke Pos Anggaran sebagai sumber utama.
     * Tabel anggaran tetap disinkronkan untuk kompatibilitas laporan lama.
     */
    public function save(int $year, int $penerima, float $pagu): bool {
        $pos = $this->db->query(
            "SELECT pa.id
             FROM pos_anggaran pa
             WHERE pa.tahun_anggaran_id = ?
               AND pa.tipe = 'DPRD'
               AND pa.penerima_id = ?
               AND pa.is_active = 1
             UNION ALL
             SELECT pa.id
             FROM pos_anggaran pa
             JOIN penugasan_pos pp ON pp.pos_anggaran_id = pa.id
             WHERE pa.tahun_anggaran_id = ?
               AND pa.tipe = 'PENDAMPING'
               AND pa.is_active = 1
               AND pp.penerima_id = ?
               AND pp.tanggal_selesai IS NULL
             LIMIT 1",
            [$year, $penerima, $year, $penerima]
        )->row_array();

        if ($pos) {
            $this->db->where('id', (int)$pos['id'])->update('pos_anggaran', [
                'pagu'       => $pagu,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }

        $row = $this->db->get_where('anggaran', [
            'tahun_anggaran_id' => $year,
            'penerima_id'       => $penerima,
        ])->row_array();

        if ($row) {
            return (bool)$this->db->where('id', $row['id'])->update('anggaran', ['pagu' => $pagu]);
        }

        return (bool)$this->db->insert('anggaran', [
            'tahun_anggaran_id' => $year,
            'penerima_id'       => $penerima,
            'pagu'              => $pagu,
        ]);
    }
}

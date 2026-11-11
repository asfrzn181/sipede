<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Kegiatan_model extends CI_Model {

    public function find(int $id): ?array {
        return $this->db->get_where('kegiatan', array('id' => $id))->row_array() ?: NULL;
    }

    public function belongs_to_penerima(int $id, ?int $penerimaId): bool {
        return (bool)$penerimaId && $this->db->where(array('kegiatan_id' => $id, 'penerima_id' => $penerimaId))->count_all_results('kegiatan_peserta');
    }

    public function latest(int $limit = 10, ?string $scope = 'all', ?int $ownId = null): array {
        $this->db->select('k.*, GROUP_CONCAT(p.nama SEPARATOR ", ") AS peserta', FALSE)
            ->from('kegiatan k')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id', 'left')
            ->join('penerima p', 'p.id = kp.penerima_id', 'left');
        if ($scope === 'own' && $ownId) $this->db->where('kp.penerima_id', $ownId);
        return $this->db->group_by('k.id')->order_by('k.created_at', 'DESC')->limit($limit)->get()->result_array();
    }

    private function build_kegiatan_filter(array $filter = array(), ?string $scope = 'all', ?int $ownId = null, string $search = ''): void {
        if ($scope === 'own' && $ownId) {
            $this->db->join('kegiatan_peserta kp_own', 'kp_own.kegiatan_id = k.id', 'inner');
            $this->db->where('kp_own.penerima_id', $ownId);
        }
        if (!empty($filter['status'])) {
            $this->db->where('k.status', $filter['status']);
        }
        if (!empty($filter['tahun_anggaran_id'])) {
            $this->db->where('k.tahun_anggaran_id', (int)$filter['tahun_anggaran_id']);
        }
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('k.nama_kegiatan', $search);
            $this->db->or_like('k.no_surat_tugas', $search);
            $this->db->or_like('k.tempat', $search);
            $this->db->or_like('k.tema', $search);
            $this->db->group_end();
        }
    }

    public function count_all(array $filter = array(), ?string $scope = 'all', ?int $ownId = null): int {
        $this->db->from('kegiatan k');
        $this->build_kegiatan_filter($filter, $scope, $ownId, '');
        $row = $this->db->select('COUNT(DISTINCT k.id) AS cnt')->get()->row_array();
        return (int)($row['cnt'] ?? 0);
    }

    public function count_filtered(array $filter = array(), ?string $scope = 'all', ?int $ownId = null, string $search = ''): int {
        $this->db->from('kegiatan k');
        $this->build_kegiatan_filter($filter, $scope, $ownId, $search);
        $row = $this->db->select('COUNT(DISTINCT k.id) AS cnt')->get()->row_array();
        return (int)($row['cnt'] ?? 0);
    }

    public function get_datatable(
        array $filter = array(),
        ?string $scope = 'all',
        ?int $ownId = null,
        int $start = 0,
        int $length = 10,
        string $search = '',
        string $orderCol = 'k.tanggal_mulai',
        string $orderDir = 'DESC'
    ): array {
        $this->db->select('k.*, ta.tahun AS tahun_anggaran, COUNT(DISTINCT kp.id) AS total_peserta, GROUP_CONCAT(DISTINCT p.nama ORDER BY p.tipe, p.nama SEPARATOR ", ") AS peserta', FALSE)
            ->from('kegiatan k')
            ->join('tahun_anggaran ta', 'ta.id = k.tahun_anggaran_id', 'left')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id', 'left')
            ->join('penerima p', 'p.id = kp.penerima_id', 'left');

        if ($scope === 'own' && $ownId) {
            $this->db->join('kegiatan_peserta kp_own', 'kp_own.kegiatan_id = k.id', 'inner');
            $this->db->where('kp_own.penerima_id', $ownId);
        }

        if (!empty($filter['status'])) {
            $this->db->where('k.status', $filter['status']);
        }
        if (!empty($filter['tahun_anggaran_id'])) {
            $this->db->where('k.tahun_anggaran_id', (int)$filter['tahun_anggaran_id']);
        }

        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('k.nama_kegiatan', $search);
            $this->db->or_like('k.no_surat_tugas', $search);
            $this->db->or_like('k.tempat', $search);
            $this->db->or_like('k.tema', $search);
            $this->db->or_like('p.nama', $search);
            $this->db->group_end();
        }

        $this->db->group_by('k.id');

        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('k.tanggal_mulai', 'DESC');
        }

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result_array();
    }

    public function all(array $filter = array(), ?string $scope = 'all', ?int $ownId = null): array {
        $this->db->select('k.*, COUNT(DISTINCT kp.id) AS total_peserta, GROUP_CONCAT(DISTINCT p.nama ORDER BY p.tipe, p.nama SEPARATOR ", ") AS peserta', FALSE)
            ->from('kegiatan k')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id', 'left')
            ->join('penerima p', 'p.id = kp.penerima_id', 'left')
            ->group_by('k.id')
            ->order_by('k.tanggal_mulai', 'DESC');
        if ($scope === 'own' && $ownId) $this->db->where('kp.penerima_id', $ownId);
        if (!empty($filter['status'])) $this->db->where('k.status', $filter['status']);
        if (!empty($filter['tahun_anggaran_id'])) $this->db->where('k.tahun_anggaran_id', (int)$filter['tahun_anggaran_id']);
        if (!empty($filter['q'])) {
            $this->db->group_start()
                ->like('k.nama_kegiatan', $filter['q'])
                ->or_like('k.no_surat_tugas', $filter['q'])
                ->or_like('k.tempat', $filter['q'])
                ->or_like('k.tema', $filter['q'])
                ->group_end();
        }
        return $this->db->get()->result_array();
    }

    /**
     * Simpan / Update data Header Surat Tugas (Parent Data)
     */
    public function save_header(array $data, ?int $id = null, int $userId = 0): int {
        $record = array(
            'tahun_anggaran_id'   => (int)$data['tahun_anggaran_id'],
            'nama_kegiatan'       => !empty($data['nama_kegiatan']) ? $data['nama_kegiatan'] : ($data['tema'] ?? 'Surat Tugas'),
            'no_surat_tugas'      => $data['no_surat_tugas'] ?? NULL,
            'tanggal_surat_tugas' => $data['tanggal_surat_tugas'] ?? NULL,
            'tema'                => $data['tema'] ?? NULL,
            'tanggal_input'       => $data['tanggal_input'] ?? date('Y-m-d'),
            'tanggal_mulai'       => $data['tanggal_mulai'],
            'tanggal_selesai'     => $data['tanggal_selesai'],
            'tempat'              => $data['tempat'] ?? NULL,
            'status'              => $data['status'] ?? 'DRAFT',
            'updated_at'          => date('Y-m-d H:i:s'),
        );

        if ($id) {
            $this->db->where('id', $id)->update('kegiatan', $record);
            return $id;
        }

        $record['created_by'] = $userId;
        $record['created_at'] = date('Y-m-d H:i:s');
        $record['total']      = 0.0;
        $this->db->insert('kegiatan', $record);
        return (int)$this->db->insert_id();
    }

    /**
     * Ambil detail komprehensif satu kegiatan beserta seluruh peserta & rincian biayanya
     */
    public function get_full_detail(int $id): ?array {
        $kegiatan = $this->find($id);
        if (!$kegiatan) return null;

        // Ambil tahun anggaran
        $kegiatan['tahun_row'] = $this->db->get_where('tahun_anggaran', array('id' => $kegiatan['tahun_anggaran_id']))->row_array();

        // Ambil daftar peserta
        $pesertaRows = $this->db->select('kp.*, p.nama, p.tipe, p.jabatan, p.periode_mulai, p.periode_akhir,
                                          sd.kode AS sumber_dana_kode, sd.nama AS sumber_dana_nama,
                                          pa.kode AS pos_kode, pa.nama AS pos_nama, pa.pagu AS pos_pagu', FALSE)
            ->from('kegiatan_peserta kp')
            ->join('penerima p', 'p.id = kp.penerima_id')
            ->join('sumber_dana sd', 'sd.id = kp.sumber_dana_id', 'left')
            ->join('pos_anggaran pa', 'pa.id = kp.pos_anggaran_id', 'left')
            ->where('kp.kegiatan_id', $id)
            ->order_by('p.tipe', 'ASC') // DPRD dulu baru Pendamping
            ->order_by('p.nama', 'ASC')
            ->get()->result_array();

        $peserta = array();
        $grandTotal = 0.0;

        foreach ($pesertaRows as $p) {
            // Rincian biaya peserta
            $biayaList = $this->db->select('kb.*, jb.kode AS jenis_biaya_kode, COALESCE(jb.nama, "Biaya Perjalanan") AS jenis_biaya_nama, jb.satuan', FALSE)
                ->from('kegiatan_biaya kb')
                ->join('jenis_biaya jb', 'jb.id = kb.jenis_biaya_id', 'left')
                ->where('kb.kegiatan_peserta_id', $p['id'])
                ->get()->result_array();

            $subtotalPeserta = 0.0;
            foreach ($biayaList as $b) {
                $subtotalPeserta += (float)$b['volume'] * (float)$b['tarif'];
            }

            $p['biaya'] = $biayaList;
            $p['total_biaya'] = $subtotalPeserta;
            $grandTotal += $subtotalPeserta;
            $peserta[] = $p;
        }

        $kegiatan['peserta'] = $peserta;
        $kegiatan['grand_total'] = $grandTotal;

        return $kegiatan;
    }

    /**
     * Simpan / Tambah / Update Peserta beserta Rincian Biayanya
     */
    public function save_peserta(int $kegiatanId, array $data): int {
        $kegiatanPesertaId = !empty($data['id']) ? (int)$data['id'] : 0;
        $penerimaId        = (int)$data['penerima_id'];
        $posAnggaranId     = !empty($data['pos_anggaran_id']) ? (int)$data['pos_anggaran_id'] : null;
        $alasanPengalihan  = !empty($data['alasan_pengalihan']) ? trim((string)$data['alasan_pengalihan']) : null;
        $sumberDanaId      = !empty($data['sumber_dana_id']) ? (int)$data['sumber_dana_id'] : null;

        $kpData = array(
            'kegiatan_id'       => $kegiatanId,
            'penerima_id'       => $penerimaId,
            'pos_anggaran_id'   => $posAnggaranId,
            'alasan_pengalihan' => $alasanPengalihan,
            'sumber_dana_id'    => $sumberDanaId,
        );

        if ($kegiatanPesertaId) {
            $this->db->where('id', $kegiatanPesertaId)->update('kegiatan_peserta', $kpData);
            // Hapus biaya lama untuk diganti rincian baru
            $this->db->where('kegiatan_peserta_id', $kegiatanPesertaId)->delete('kegiatan_biaya');
        } else {
            $this->db->insert('kegiatan_peserta', $kpData);
            $kegiatanPesertaId = (int)$this->db->insert_id();
        }

        // Insert rincian biaya
        foreach (($data['biaya'] ?? array()) as $cost) {
            $volume = (float)($cost['volume'] ?? 0);
            $tarif = clean_number($cost['tarif'] ?? 0);
            if ($volume <= 0 && $tarif <= 0) continue;

            $jenisBiayaId = !empty($cost['jenis_biaya_id']) ? (int)$cost['jenis_biaya_id'] : NULL;
            $this->db->insert('kegiatan_biaya', array(
                'kegiatan_peserta_id' => $kegiatanPesertaId,
                'jenis_biaya_id'      => $jenisBiayaId,
                'volume'              => $volume,
                'tarif'               => $tarif,
            ));
        }

        // Hitung ulang total kegiatan
        $this->recalculate_total($kegiatanId);

        return $kegiatanPesertaId;
    }

    /**
     * Hapus Peserta dari Kegiatan
     */
    public function delete_peserta(int $kegiatanPesertaId): bool {
        $row = $this->db->get_where('kegiatan_peserta', array('id' => $kegiatanPesertaId))->row_array();
        if (!$row) return false;

        $kegiatanId = (int)$row['kegiatan_id'];
        $this->db->where('kegiatan_peserta_id', $kegiatanPesertaId)->delete('kegiatan_biaya');
        $this->db->where('id', $kegiatanPesertaId)->delete('kegiatan_peserta');

        $this->recalculate_total($kegiatanId);
        return true;
    }

    /**
     * Hitung ulang total kegiatan dari seluruh biaya peserta
     */
    public function recalculate_total(int $kegiatanId): float {
        $sql = "SELECT COALESCE(SUM(kb.volume * kb.tarif), 0) AS grand_total
                FROM kegiatan_peserta kp
                JOIN kegiatan_biaya kb ON kb.kegiatan_peserta_id = kp.id
                WHERE kp.kegiatan_id = ?";
        $res = $this->db->query($sql, array($kegiatanId))->row_array();
        $total = (float)($res['grand_total'] ?? 0);

        $this->db->where('id', $kegiatanId)->update('kegiatan', array(
            'total'      => $total,
            'updated_at' => date('Y-m-d H:i:s')
        ));
        return $total;
    }

    private function apply_rekap_filter(array $filter, string $search = ''): void {
        if (!empty($filter['tanggal_mulai'])) {
            $this->db->where('k.tanggal_mulai >=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $this->db->where('k.tanggal_selesai <=', $filter['tanggal_selesai']);
        }
        if (!empty($filter['status'])) {
            $this->db->where('k.status', $filter['status']);
        }
        if (!empty($filter['tempat'])) {
            $this->db->like('k.tempat', $filter['tempat']);
        }
        if (!empty($filter['tipe'])) {
            $this->db->where('p.tipe', $filter['tipe']);
        }
        if (!empty($filter['penerima_id'])) {
            $this->db->where('kp.penerima_id', (int)$filter['penerima_id']);
        }
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('k.no_surat_tugas', $search);
            $this->db->or_like('k.tema', $search);
            $this->db->or_like('k.nama_kegiatan', $search);
            $this->db->or_like('k.tempat', $search);
            $this->db->or_like('p.nama', $search);
            $this->db->group_end();
        }
    }

    public function rekapitulasi_count_all(array $filter): int {
        $this->db->from('kegiatan k')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id', 'left')
            ->join('penerima p', 'p.id = kp.penerima_id', 'left');
        $this->apply_rekap_filter($filter, '');
        $row = $this->db->select('COUNT(DISTINCT k.id) AS cnt')->get()->row_array();
        return (int)($row['cnt'] ?? 0);
    }

    public function rekapitulasi_count_filtered(array $filter, string $search = ''): int {
        $this->db->from('kegiatan k')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id', 'left')
            ->join('penerima p', 'p.id = kp.penerima_id', 'left');
        $this->apply_rekap_filter($filter, $search);
        $row = $this->db->select('COUNT(DISTINCT k.id) AS cnt')->get()->row_array();
        return (int)($row['cnt'] ?? 0);
    }

    public function rekapitulasi_datatable(
        array $filter,
        int $start = 0,
        int $length = 10,
        string $search = '',
        string $orderCol = 'k.tanggal_mulai',
        string $orderDir = 'DESC'
    ): array {
        $this->db->select('k.id, k.no_surat_tugas, k.tanggal_surat_tugas, k.tanggal_mulai, k.tanggal_selesai, k.tempat, k.tema, k.status, k.total,
                           COUNT(DISTINCT kp.id) AS jumlah_peserta,
                           COUNT(DISTINCT CASE WHEN p.tipe = "DPRD" THEN kp.id END) AS jumlah_dprd,
                           COUNT(DISTINCT CASE WHEN p.tipe = "PENDAMPING" THEN kp.id END) AS jumlah_pendamping,
                           GROUP_CONCAT(DISTINCT p.nama ORDER BY p.tipe, p.nama SEPARATOR "; ") AS daftar_peserta', FALSE)
            ->from('kegiatan k')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id', 'left')
            ->join('penerima p', 'p.id = kp.penerima_id', 'left');

        $this->apply_rekap_filter($filter, $search);

        $this->db->group_by('k.id');

        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('k.tanggal_mulai', 'DESC');
        }

        if ($length > 0) {
            $this->db->limit($length, $start);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Rekapitulasi Perjalanan Dinas per Periode Date Range
     */
    public function rekapitulasi_periode(array $filter): array {
        $this->db->select('k.id, k.no_surat_tugas, k.tanggal_surat_tugas, k.tanggal_mulai, k.tanggal_selesai, k.tempat, k.tema, k.status, k.total,
                           COUNT(DISTINCT kp.id) AS jumlah_peserta,
                           COUNT(DISTINCT CASE WHEN p.tipe = "DPRD" THEN kp.id END) AS jumlah_dprd,
                           COUNT(DISTINCT CASE WHEN p.tipe = "PENDAMPING" THEN kp.id END) AS jumlah_pendamping,
                           GROUP_CONCAT(DISTINCT p.nama ORDER BY p.tipe, p.nama SEPARATOR "; ") AS daftar_peserta', FALSE)
            ->from('kegiatan k')
            ->join('kegiatan_peserta kp', 'kp.kegiatan_id = k.id', 'left')
            ->join('penerima p', 'p.id = kp.penerima_id', 'left');

        if (!empty($filter['tanggal_mulai'])) {
            $this->db->where('k.tanggal_mulai >=', $filter['tanggal_mulai']);
        }
        if (!empty($filter['tanggal_selesai'])) {
            $this->db->where('k.tanggal_selesai <=', $filter['tanggal_selesai']);
        }
        if (!empty($filter['status'])) {
            $this->db->where('k.status', $filter['status']);
        }
        if (!empty($filter['tempat'])) {
            $this->db->like('k.tempat', $filter['tempat']);
        }
        if (!empty($filter['tipe'])) {
            $this->db->where('p.tipe', $filter['tipe']);
        }
        if (!empty($filter['penerima_id'])) {
            $this->db->where('kp.penerima_id', (int)$filter['penerima_id']);
        }

        return $this->db->group_by('k.id')
            ->order_by('k.tanggal_mulai', 'DESC')
            ->get()->result_array();
    }

    public function save_graph(array $data, int $userId): int|false {
        $id = $this->save_header($data, !empty($data['id']) ? (int)$data['id'] : null, $userId);
        if (!$id) return false;

        $grandTotal = 0.0;
        foreach (($data['peserta'] ?? array()) as $person) {
            $this->save_peserta($id, $person);
        }
        return $id;
    }
}

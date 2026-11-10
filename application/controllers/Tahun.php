<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tahun extends MY_Controller {
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model('Tahun_model');
        $this->load->model('Anggaran_model');
        $this->load->model('Audit_model');
    }

    /** Daftar Tahun Anggaran */
    public function index(): void {
        $this->require_permission('tahun.view');
        $this->load->view('layout/main', array(
            'title' => 'Tahun & Pagu Anggaran',
            'view'  => 'tahun/index',
            'data'  => array(
                'rows'       => array(),
                'can_manage' => can('tahun.manage'),
                'can_lock'   => can('tahun.lock'),
            )
        ));
    }

    public function data(): void {
        $this->require_permission('tahun.view');
        $canLock = can('tahun.lock');

        $columnMap = array(
            0 => 'tahun',
            1 => 'is_locked'
        );

        $params = $this->dt_params($columnMap, 'tahun', 'DESC');

        $recordsTotal = $this->Tahun_model->count_all();
        $recordsFiltered = $this->Tahun_model->count_filtered($params['search']);
        $rows = $this->Tahun_model->get_datatable(
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $encId = encrypt_id($row['id']);
            $tahunHtml = '<span class="fw-bold fs-6">' . (int)$row['tahun'] . '</span>';
            $badgeBg = $row['is_locked'] ? 'secondary' : 'success';
            $badgeText = $row['is_locked']
                ? '<i class="bi bi-lock-fill me-1"></i>Dikunci'
                : '<i class="bi bi-unlock-fill me-1"></i>Terbuka';
            $lockHtml = '<span class="badge text-bg-' . $badgeBg . '">' . $badgeText . '</span>';

            $aksiHtml = '<div class="text-end d-flex gap-2 justify-content-end">'
                . '<a class="btn btn-sm btn-outline-primary" href="' . site_url('tahun/detail/' . $encId) . '"><i class="bi bi-eye me-1"></i>Detail Pagu</a>';
            if ($canLock) {
                $lockBtnText = $row['is_locked'] ? '<i class="bi bi-unlock"></i> Buka' : '<i class="bi bi-lock"></i> Kunci';
                $aksiHtml .= '<button class="btn btn-sm btn-outline-secondary js-lock" data-id="' . $encId . '">' . $lockBtnText . '</button>';
            }
            $aksiHtml .= '</div>';

            $data[] = array(
                $tahunHtml,
                $lockHtml,
                $aksiHtml
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    /** Detail: tampilkan pagu penerima untuk 1 tahun dengan enkripsi primary key */
    public function detail($id): void {
        $this->require_permission('tahun.view');
        $rawId = decrypt_id($id);
        if (!$rawId) show_404();

        $tahun = $this->Tahun_model->find($rawId);
        if (!$tahun) show_404();

        $can_manage_anggaran = can('anggaran.manage');
        $rows = $this->Tahun_model->pagu_by_tahun($rawId);

        // Hitung total summary
        $totalPagu = 0.0;
        $totalRealisasi = 0.0;
        $totalSisa = 0.0;
        foreach ($rows as $r) {
            $totalPagu += (float)$r['pagu'];
            $totalRealisasi += (float)$r['realisasi'];
            $totalSisa += (float)$r['sisa'];
        }

        $this->load->view('layout/main', array(
            'title' => 'Pagu Anggaran ' . $tahun['tahun'],
            'view'  => 'tahun/detail',
            'data'  => array(
                'tahun'               => $tahun,
                'rows'                => $rows,
                'totalPagu'           => $totalPagu,
                'totalRealisasi'      => $totalRealisasi,
                'totalSisa'           => $totalSisa,
                'can_manage_anggaran' => $can_manage_anggaran,
            )
        ));
    }

    /** Simpan tahun baru */
    public function save(): void {
        $this->require_permission('tahun.manage');
        $id    = decrypt_id($this->input->post('id'));
        $tahun = (int)$this->input->post('tahun');
        if ($tahun < 2000) {
            $this->respond(FALSE, 'Tahun tidak valid.', array(), array('tahun' => 'Tahun wajib diisi.'), 422);
        }
        $newId = $this->Tahun_model->save(array('tahun' => $tahun, 'is_locked' => 0), $id);
        $this->respond(TRUE, 'Tahun tersimpan', array('id' => encrypt_id($newId)));
    }

    /** Kunci / buka kunci tahun */
    public function lock($id): void {
        $this->require_permission('tahun.lock');
        $rawId = decrypt_id($id);
        if (!$rawId) $this->respond(FALSE, 'Data tidak ditemukan.', array(), array(), 404);

        $row = $this->Tahun_model->find($rawId);
        if (!$row) $this->respond(FALSE, 'Data tidak ditemukan.', array(), array(), 404);

        $newLock = $row['is_locked'] ? 0 : 1;
        $this->db->where('id', $rawId)->update('tahun_anggaran', array('is_locked' => $newLock));

        // Catat audit log untuk penguncian/pembukaan tahun
        $this->Audit_model->write(
            'TAHUN_ANGGARAN',
            $rawId,
            $newLock ? 'KUNCI' : 'BUKA',
            array('is_locked' => $row['is_locked']),
            array('is_locked' => $newLock),
            (int)$this->user['id']
        );

        $this->respond(TRUE, 'Status kunci diperbarui.');
    }

    /** Simpan pagu awal satu penerima */
    public function save_pagu(): void {
        $this->require_permission('anggaran.manage');
        $tahun_id    = decrypt_id($this->input->post('tahun_id'));
        $penerima_id = decrypt_id($this->input->post('penerima_id'));
        $pagu        = clean_number($this->input->post('pagu'));

        if (!$tahun_id || !$penerima_id) {
            $this->respond(FALSE, 'Parameter ID tidak valid.', array(), array(), 422);
        }

        // Cek kunci tahun
        $locked = $this->db->get_where('tahun_anggaran', array('id' => $tahun_id, 'is_locked' => 1))->row();
        if ($locked) $this->respond(FALSE, 'Tahun sudah dikunci.', array(), array(), 422);

        $ok = $this->Anggaran_model->save($tahun_id, $penerima_id, $pagu);
        $this->respond((bool)$ok, $ok ? 'Pagu tersimpan' : 'Gagal menyimpan pagu.');
    }

    /**
     * Revisi Anggaran dengan kalkulasi realisasi & audit log akuntabel
     */
    public function revisi_anggaran(): void {
        $this->require_permission('anggaran.manage');

        $tahun_id    = decrypt_id($this->input->post('tahun_id'));
        $penerima_id = decrypt_id($this->input->post('penerima_id'));
        $pagu_baru   = clean_number($this->input->post('pagu_baru'));
        $alasan      = trim((string)$this->input->post('alasan', TRUE));

        if (!$tahun_id || !$penerima_id) {
            $this->respond(FALSE, 'Parameter ID tidak valid.', array(), array(), 422);
        }
        if (empty($alasan)) {
            $this->respond(FALSE, 'Alasan revisi anggaran wajib diisi untuk pencatatan audit log.', array(), array('alasan' => 'Alasan revisi wajib diisi.'), 422);
        }
        if ($pagu_baru < 0) {
            $this->respond(FALSE, 'Pagu baru tidak boleh bernilai negatif.', array(), array('pagu_baru' => 'Pagu tidak valid.'), 422);
        }

        // Cek apakah tahun anggaran dikunci
        $tahun = $this->Tahun_model->find($tahun_id);
        if (!$tahun) {
            $this->respond(FALSE, 'Tahun anggaran tidak ditemukan.', array(), array(), 404);
        }
        if (!empty($tahun['is_locked'])) {
            $this->respond(FALSE, 'Tahun anggaran ini telah dikunci. Revisi tidak diizinkan.', array(), array(), 422);
        }

        // Data penerima
        $penerima = $this->db->get_where('penerima', array('id' => $penerima_id))->row_array();
        if (!$penerima) {
            $this->respond(FALSE, 'Data penerima tidak ditemukan.', array(), array(), 404);
        }

        // Ambil data pagu saat ini dan total realisasi yang telah terpakai
        $current = $this->db->query("
            SELECT a.id AS anggaran_id, COALESCE(a.pagu, 0) AS pagu,
                   COALESCE(r.realisasi, 0) AS realisasi
            FROM anggaran a
            LEFT JOIN v_realisasi r ON r.tahun_anggaran_id = a.tahun_anggaran_id AND r.penerima_id = a.penerima_id
            WHERE a.tahun_anggaran_id = ? AND a.penerima_id = ?
        ", array($tahun_id, $penerima_id))->row_array();

        $pagu_lama   = $current ? (float)$current['pagu'] : 0.0;
        $realisasi   = $current ? (float)$current['realisasi'] : 0.0;
        $anggaran_id = $current ? (int)$current['anggaran_id'] : 0;

        // KALKULASI & VALIDASI ATURAN ANGGARAN:
        // 1. Pagu baru tidak boleh lebih kecil dari realisasi yang sudah terpakai
        if ($pagu_baru < $realisasi) {
            $this->respond(
                FALSE,
                'Pagu baru (' . rupiah($pagu_baru) . ') tidak boleh lebih kecil dari realisasi yang telah terpakai (' . rupiah($realisasi) . '). Sisa anggaran tidak boleh minus/defisit.',
                array(),
                array('pagu_baru' => 'Harus minimal ' . rupiah($realisasi)),
                422
            );
        }

        // 2. Nilai pagu harus ada perubahan
        if (abs($pagu_baru - $pagu_lama) < 0.01) {
            $this->respond(
                FALSE,
                'Nilai pagu baru sama dengan pagu saat ini (' . rupiah($pagu_lama) . '). Tidak ada perubahan untuk direvisi.',
                array(),
                array('pagu_baru' => 'Nilai pagu sama dengan pagu saat ini.'),
                422
            );
        }

        // Kalkulasi matematis revisi
        $selisih   = $pagu_baru - $pagu_lama;
        $sisa_lama = $pagu_lama - $realisasi;
        $sisa_baru = $pagu_baru - $realisasi;

        $this->db->trans_begin();

        // Update pagu di database
        $this->Anggaran_model->save($tahun_id, $penerima_id, $pagu_baru);
        if (!$anggaran_id) {
            $savedRow = $this->db->get_where('anggaran', array('tahun_anggaran_id' => $tahun_id, 'penerima_id' => $penerima_id))->row_array();
            $anggaran_id = $savedRow ? (int)$savedRow['id'] : 0;
        }

        // Catat Audit Log lengkap
        $oldData = array(
            'tahun'             => (int)$tahun['tahun'],
            'tahun_anggaran_id' => $tahun_id,
            'penerima_id'       => $penerima_id,
            'penerima_nama'     => $penerima['nama'],
            'tipe'              => $penerima['tipe'],
            'pagu_lama'         => $pagu_lama,
            'realisasi'         => $realisasi,
            'sisa_lama'         => $sisa_lama,
        );

        $newData = array(
            'pagu_baru'         => $pagu_baru,
            'selisih'           => $selisih,
            'sisa_baru'         => $sisa_baru,
            'alasan'            => $alasan,
            'direvisi_oleh'     => $this->user['nama'] ?? 'User #' . $this->user['id'],
            'waktu_revisi'      => date('Y-m-d H:i:s'),
        );

        $this->Audit_model->write('ANGGARAN', $anggaran_id, 'REVISI', $oldData, $newData, (int)$this->user['id']);

        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            $this->respond(FALSE, 'Gagal menyimpan revisi anggaran.', array(), array(), 500);
        }

        $this->db->trans_commit();

        $tipeSelisih = $selisih > 0 ? 'Penambahan' : 'Pengurangan';
        $pesan = "Revisi anggaran berhasil disimpan! ({$tipeSelisih} " . rupiah(abs($selisih)) . "). Perubahan telah dicatat di Audit Log.";
        $this->respond(TRUE, $pesan, array(
            'pagu_baru' => $pagu_baru,
            'selisih'   => $selisih,
            'sisa_baru' => $sisa_baru
        ));
    }
}

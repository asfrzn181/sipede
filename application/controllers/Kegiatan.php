<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Kegiatan extends MY_Controller
{
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(array('Kegiatan_model', 'Penerima_model', 'Jenis_biaya_model', 'Sumber_dana_model', 'Audit_model', 'Pos_model', 'Pengaturan_model'));
        $this->load->library('Anggaran_service');
    }

    /**
     * Daftar Kegiatan / Surat Tugas (Parent List)
     */
    public function index(): void {
        $this->require_any(array('kegiatan.view_all', 'kegiatan.view_own'));
        $tahunAnggaran = $this->db
            ->order_by('tahun', 'DESC')
            ->get('tahun_anggaran')
            ->result_array();
        $year = (int)$this->session->userdata('tahun_aktif_id');
        $openYears = array_values(array_filter($tahunAnggaran, function($row) {
            return empty($row['is_locked']);
        }));
        $openYearIds = array_map('intval', array_column($openYears, 'id'));
        if (!$year || !in_array($year, $openYearIds, TRUE)) {
            $year = !empty($openYears)
                ? (int)$openYears[0]['id']
                : (!empty($tahunAnggaran) ? (int)$tahunAnggaran[0]['id'] : 0);
        }

        $this->load->view('layout/main', array(
            'title' => 'Kegiatan & Surat Tugas',
            'view'  => 'kegiatan/index',
            'data'  => array(
                'rows'        => array(),
                'penerima'    => $this->Penerima_model->with_budget_source($year),
                'jenis'       => $this->Jenis_biaya_model->all(),
                'jenis_kegiatan' => $this->db->where('is_active', 1)->order_by('urutan')->get('jenis_kegiatan')->result_array(),
                'sumber_dana' => $this->Sumber_dana_model->all(),
                'tahun_anggaran' => $tahunAnggaran,
                'tahun_aktif_id' => $year,
                'can_create'  => can('kegiatan.create'),
                'can_submit'  => can('kegiatan.ajukan'),
            )
        ));
    }

    /**
     * DataTables Server-Side Processing untuk Kegiatan
     */
    public function data(): void {
        $this->require_any(array('kegiatan.view_all', 'kegiatan.view_own'));
        $scope = $this->rbac->scope('kegiatan');
        $ownId = $this->own_penerima_id();
        $year  = (int)($this->input->post('tahun_anggaran_id') ?: $this->input->get('tahun_anggaran_id'));
        if (!$year) $year = (int)$this->session->userdata('tahun_aktif_id');
        if (!$year || !$this->db->where('id', $year)->count_all_results('tahun_anggaran')) {
            $this->respond_dt((int)($this->input->post('draw') ?: $this->input->get('draw')), 0, 0, array());
            return;
        }
        $canCreate = can('kegiatan.create');

        $columnMap = array(
            0 => 'k.no_surat_tugas',
            1 => 'k.tema',
            2 => 'k.tanggal_mulai',
            3 => 'k.tempat',
            4 => 'total_peserta',
            5 => 'k.total',
            6 => 'k.status'
        );

        $params = $this->dt_params($columnMap, 'k.tanggal_mulai', 'DESC');
        $tab = (string)($params['raw_input']['tab'] ?? 'unfinished');
        $filter = array(
            'tahun_anggaran_id' => $year,
            'tab' => $tab === 'history' ? 'history' : 'unfinished'
        );

        $recordsTotal = $this->Kegiatan_model->count_all($filter, $scope, $ownId);
        $recordsFiltered = $this->Kegiatan_model->count_filtered($filter, $scope, $ownId, $params['search']);
        $rows = $this->Kegiatan_model->get_datatable(
            $filter,
            $scope,
            $ownId,
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $encId = encrypt_id($row['id']);
            $bClass = $row['status'] === 'BATAL' ? 'danger' : ($row['status'] === 'AKTIF' ? 'success' : ($row['status'] === 'DIAJUKAN' ? 'warning' : 'secondary'));

            $noSuratHtml = '<div class="fw-medium"><code>' . html_escape($row['no_surat_tugas'] ?: '-') . '</code>';
            if (!empty($row['tanggal_surat_tugas'])) {
                $noSuratHtml .= '<div class="small text-muted">' . tgl_indo($row['tanggal_surat_tugas']) . '</div>';
            }
            $noSuratHtml .= '</div>';

            $temaHtml = '<div class="fw-semibold"><a href="' . site_url('kegiatan/rincian/' . $encId) . '" class="text-dark text-decoration-none">'
                . html_escape($row['tema'] ?: $row['nama_kegiatan']) . '</a>';
            if (!empty($row['peserta'])) {
                $temaHtml .= '<div class="text-muted small fw-normal text-truncate" style="max-width: 260px;" title="' . html_escape($row['peserta']) . '">'
                    . '<i class="bi bi-people me-1"></i>' . html_escape($row['peserta']) . '</div>';
            }
            $temaHtml .= '</div>';

            $pelaksanaanHtml = '<div class="small text-nowrap">' . tgl_indo($row['tanggal_mulai']) . '<br><span class="text-muted">s/d ' . tgl_indo($row['tanggal_selesai']) . '</span></div>';
            $tempatHtml = '<div><i class="bi bi-geo-alt text-danger me-1"></i>' . html_escape($row['tempat'] ?: '-') . '</div>';
            $pesertaHtml = '<div class="text-center"><span class="badge text-bg-light border">' . (int)($row['total_peserta'] ?? 0) . ' Org</span></div>';
            $totalHtml = '<div class="text-end fw-bold text-primary">' . rupiah($row['total']) . '</div>';
            $displayStatus = !empty($row['dicetak_at']) ? 'SELESAI DICETAK' : $row['status'];
            $displayClass = !empty($row['dicetak_at']) ? 'info' : $bClass;
            $statusHtml = '<div class="text-center"><span class="badge text-bg-' . $displayClass . '">' . html_escape($displayStatus) . '</span></div>';

            $rowEnc = $row;
            $rowEnc['id'] = $encId;
            $rowEncJson = htmlspecialchars(json_encode($rowEnc), ENT_QUOTES, 'UTF-8');

            $aksiHtml = '<div class="text-end text-nowrap"><div class="btn-group btn-group-sm">'
                . '<a href="' . site_url('kegiatan/rincian/' . $encId) . '" class="btn btn-outline-primary" title="Buka Rincian Kegiatan & Peserta"><i class="bi bi-list-check"></i> Rincian</a>'
                . '<a href="' . site_url('kegiatan/bukti_rekap/' . $encId) . '" class="btn btn-outline-info" title="Bukti Rekapitulasi"><i class="bi bi-receipt"></i> Bukti</a>';
            if ($canCreate && $row['status'] !== 'BATAL') {
                $aksiHtml .= '<button class="btn btn-outline-secondary js-edit-header" data-row=\'' . $rowEncJson . '\' title="Edit Surat Tugas"><i class="bi bi-pencil"></i></button>';
            }
            $aksiHtml .= '</div></div>';

            $data[] = array(
                $noSuratHtml,
                $temaHtml,
                $pelaksanaanHtml,
                $tempatHtml,
                $pesertaHtml,
                $totalHtml,
                $statusHtml,
                $aksiHtml
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    /**
     * Halaman Rincian Kegiatan:
     * Menampilkan Header Surat Tugas + Tempat Input Siapa Saja Peserta & Pendampingnya
     */
    public function rincian($id): void {
        $this->require_any(array('kegiatan.view_all', 'kegiatan.view_own'));
        $kegiatanId = decrypt_id($id);
        if (!$kegiatanId) show_404();

        $kegiatan = $this->Kegiatan_model->get_full_detail($kegiatanId);
        if (!$kegiatan) show_404();

        if ($this->rbac->scope('kegiatan') === 'own' && !$this->Kegiatan_model->belongs_to_penerima($kegiatanId, $this->own_penerima_id())) {
            $this->deny();
        }

        $tahunAnggaranId = (int)$kegiatan['tahun_anggaran_id'];
        $tahunRow = $this->db->get_where('tahun_anggaran', array('id' => $tahunAnggaranId))->row_array();
        $tahun = (int)($tahunRow['tahun'] ?? 0);
        $this->Pos_model->buat_pos_dprd_otomatis($tahunAnggaranId);
        $this->Pos_model->buat_pos_pendamping_baku($tahunAnggaranId);

        $posAnggaran = $this->Pos_model->all_by_tahun($tahunAnggaranId);
        $saldoList = $this->Pos_model->saldo_by_tahun($tahunAnggaranId);
        $saldoMap  = array_column($saldoList, null, 'pos_anggaran_id');

        foreach ($posAnggaran as &$pos) {
            $s = $saldoMap[$pos['id']] ?? [];
            $pos['sisa'] = (float)($s['sisa'] ?? $pos['pagu']);
            $pos['realisasi'] = (float)($s['realisasi'] ?? 0);
        }
        unset($pos);

        $this->load->view('layout/main', array(
            'title' => 'Rincian Kegiatan: ' . ($kegiatan['no_surat_tugas'] ?: $kegiatan['nama_kegiatan']),
            'view'  => 'kegiatan/rincian',
            'data'  => array(
                'kegiatan'       => $kegiatan,
                'penerima'       => $this->Penerima_model->all_active($tahun),
                'sumber_dana'    => $this->Sumber_dana_model->all(),
                'jenis_kegiatan' => $this->db->where('is_active', 1)->order_by('urutan')->get('jenis_kegiatan')->result_array(),
                'jenis_biaya'    => $this->Jenis_biaya_model->all(),
                'pos_anggaran'   => $posAnggaran,
                'can_update'     => (can('kegiatan.update') || can('kegiatan.create'))
                    && ($this->rbac->scope('kegiatan') !== 'own' || in_array($kegiatan['status'], array('DRAFT', 'DITOLAK'), TRUE)),
                'can_submit'     => can('kegiatan.ajukan'),
                'is_own_scope'   => $this->rbac->scope('kegiatan') === 'own',
            )
        ));
    }

    /**
     * Simpan / Edit Header Surat Tugas (Parent Data)
     */
    public function save_header(): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.update'));

        $id = decrypt_id($this->input->post('id'));
        $existing = $id ? $this->Kegiatan_model->find($id) : NULL;
        if ($id && !$existing) {
            $this->respond(FALSE, 'Kegiatan tidak ditemukan.', array(), array(), 404);
        }
        if ($existing && $this->rbac->scope('kegiatan') === 'own'
            && !$this->Kegiatan_model->belongs_to_penerima($id, $this->own_penerima_id())) {
            $this->deny();
        }

        // Tahun dipilih saat membuat kegiatan. Saat mengedit, tahun parent
        // dipertahankan agar rincian biaya dan pos tidak berpindah tahun.
        $tahunAnggaranId = $existing
            ? (int)$existing['tahun_anggaran_id']
            : (int)($this->input->post('tahun_anggaran_id') ?: $this->session->userdata('tahun_aktif_id'));
        $tahunRow = $this->db->get_where('tahun_anggaran', array('id' => $tahunAnggaranId))->row_array();
        if (!$tahunRow) {
            $this->respond(FALSE, 'Tahun anggaran tidak valid.', array(), array('tahun_anggaran_id' => 'Pilih tahun anggaran yang tersedia.'), 422);
        }
        if (!empty($tahunRow['is_locked'])) {
            $this->respond(FALSE, 'Tahun anggaran telah dikunci dan tidak dapat digunakan untuk kegiatan baru/edit.', array(), array(), 422);
        }

        $data = array(
            'tahun_anggaran_id'   => $tahunAnggaranId,
            'no_surat_tugas'      => trim((string)$this->input->post('no_surat_tugas', TRUE)),
            'tanggal_surat_tugas' => $this->input->post('tanggal_surat_tugas', TRUE),
            'tanggal_input'       => $this->input->post('tanggal_input', TRUE) ?: date('Y-m-d'),
            'tanggal_mulai'       => $this->input->post('tanggal_mulai', TRUE),
            'tanggal_selesai'     => $this->input->post('tanggal_selesai', TRUE),
            'tempat'              => trim((string)$this->input->post('tempat', TRUE)),
            'tema'                => trim((string)$this->input->post('tema', TRUE)),
            'nama_kegiatan'       => trim((string)$this->input->post('tema', TRUE)),
            'jenis_kegiatan_id'   => (int)$this->input->post('jenis_kegiatan_id'),
            'subjenis_kegiatan'   => trim((string)$this->input->post('subjenis_kegiatan', TRUE)),
            'status'              => $this->rbac->scope('kegiatan') === 'own'
                ? 'DRAFT'
                : ($this->input->post('status', TRUE) ?: 'DRAFT'),
        );

        if (empty($data['no_surat_tugas'])) {
            $this->respond(FALSE, 'Nomor Surat Tugas wajib diisi.', array(), array('no_surat_tugas' => 'Wajib diisi.'), 422);
        }
        if (empty($data['tanggal_mulai']) || empty($data['tanggal_selesai'])) {
            $this->respond(FALSE, 'Tanggal pelaksanaan wajib diisi.', array(), array(), 422);
        }

        $savedId = $this->Kegiatan_model->save_header($data, $id ?: NULL, (int)$this->user['id']);

        if ($this->rbac->scope('kegiatan') === 'own') {
            $ownPenerimaId = $this->own_penerima_id();
            if (!$ownPenerimaId) {
                $this->respond(FALSE, 'Akun belum memiliki identitas pendamping. Silakan hubungi administrator.', array(), array(), 422);
            }
            $alreadyOwn = $this->db->where(array('kegiatan_id' => $savedId, 'penerima_id' => $ownPenerimaId))
                ->count_all_results('kegiatan_peserta');
            if (!$alreadyOwn) {
                $this->Pos_model->buat_pos_pendamping_baku($tahunAnggaranId);
                $defaultPos = $this->Pos_model->pos_default_penerima($ownPenerimaId, $tahunAnggaranId);
                $this->db->insert('kegiatan_peserta', array(
                    'kegiatan_id' => $savedId,
                    'penerima_id' => $ownPenerimaId,
                    'pos_anggaran_id' => $defaultPos ? (int)$defaultPos['id'] : NULL,
                ));
            }
        }

        $this->Audit_model->write(
            'KEGIATAN',
            $savedId,
            $id ? 'EDIT_HEADER_SURAT_TUGAS' : 'BUAT_HEADER_SURAT_TUGAS',
            array(),
            $data,
            (int)$this->user['id']
        );

        $encId = encrypt_id($savedId);
        $this->respond(TRUE, 'Surat Tugas berhasil disimpan.', array(
            'id'       => $encId,
            'redirect' => site_url('kegiatan/rincian/' . $encId)
        ));
    }

    /**
     * Simpan / Tambah / Ubah Peserta & Pendamping pada Rincian Kegiatan
     */
    public function save_peserta(): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.update'));

        $payload = input_json();
        $kegiatanId = decrypt_id($payload['kegiatan_id'] ?? $this->input->post('kegiatan_id'));
        $penerimaId = decrypt_id($payload['penerima_id'] ?? $this->input->post('penerima_id'));
        $kegiatanPesertaId = decrypt_id($payload['id'] ?? $this->input->post('id'));
        $posAnggaranId = decrypt_id($payload['pos_anggaran_id'] ?? $this->input->post('pos_anggaran_id'));
        $alasanPengalihan = trim((string)($payload['alasan_pengalihan'] ?? $this->input->post('alasan_pengalihan', TRUE)));
        $rawDanaAwal = $payload['dana_awal'] ?? $this->input->post('dana_awal', TRUE);
        $danaAwal = clean_number($rawDanaAwal);

        if (!$kegiatanId || !$penerimaId) {
            $this->respond(FALSE, 'ID Kegiatan atau Penerima tidak valid.', array(), array(), 422);
        }
        if ($rawDanaAwal === null || $rawDanaAwal === '' || $danaAwal < 0) {
            $this->respond(FALSE, 'Dana awal wajib diisi dengan nominal yang valid.', array(), array('dana_awal' => 'Wajib diisi.'), 422);
        }
        if ($alasanPengalihan === '') {
            $this->respond(FALSE, 'Keterangan anggaran wajib diisi untuk setiap peserta.', array(), array('alasan_pengalihan' => 'Wajib diisi.'), 422);
        }

        $kegiatan = $this->Kegiatan_model->find($kegiatanId);
        if (!$kegiatan) {
            $this->respond(FALSE, 'Kegiatan tidak ditemukan.', array(), array(), 404);
        }

        if ($this->rbac->scope('kegiatan') === 'own') {
            $ownPenerimaId = $this->own_penerima_id();
            if (!$ownPenerimaId || $penerimaId !== $ownPenerimaId
                || !$this->Kegiatan_model->belongs_to_penerima($kegiatanId, $ownPenerimaId)) {
                $this->deny();
            }
            if ($kegiatanPesertaId) {
                $ownedParticipant = $this->db->where(array(
                    'id' => $kegiatanPesertaId,
                    'kegiatan_id' => $kegiatanId,
                    'penerima_id' => $ownPenerimaId
                ))->count_all_results('kegiatan_peserta');
                if (!$ownedParticipant) $this->deny();
            }
        }

        $year = (int)$kegiatan['tahun_anggaran_id'];

        // Cek kunci tahun
        $tahunRow = $this->db->get_where('tahun_anggaran', array('id' => $year))->row_array();
        if ($tahunRow && !empty($tahunRow['is_locked'])) {
            $this->respond(FALSE, 'Tahun anggaran telah dikunci.', array(), array(), 422);
        }
        $tahunKalender = (int)($tahunRow['tahun'] ?? 0);

        // Cek duplikasi peserta dalam satu kegiatan
        $existing = $this->db->get_where('kegiatan_peserta', array('kegiatan_id' => $kegiatanId, 'penerima_id' => $penerimaId))->row_array();
        if ($existing && (!$kegiatanPesertaId || (int)$existing['id'] !== (int)$kegiatanPesertaId)) {
            $this->respond(FALSE, 'Penerima / Peserta ini sudah terdaftar dalam kegiatan ini.', array(), array(), 422);
        }

        $penerima = $this->Penerima_model->find($penerimaId);
        if (!$penerima) {
            $this->respond(FALSE, 'Data penerima tidak ditemukan.', array(), array(), 404);
        }
        if (!$this->Penerima_model->berlaku_pada_tahun($penerima, $tahunKalender)) {
            $this->respond(FALSE, 'Anggota DPRD tidak berada dalam periode jabatan pada tahun kegiatan.', array(), array('penerima_id' => 'Periode jabatan tidak sesuai tahun kegiatan.'), 422);
        }

        // Semua peserta memiliki anggaran bawaan, tetapi sumber anggaran dapat
        // dipilih eksplisit. Pendamping dapat memakai pos DPRD dan sebaliknya.
        $posDefault = $this->Pos_model->pos_default_penerima($penerimaId, $year);
        if (!$posDefault && $penerima['tipe'] === 'DPRD') {
            $this->Pos_model->buat_pos_dprd_otomatis($year);
            $posDefault = $this->Pos_model->pos_default_penerima($penerimaId, $year);
        }
        if (!$posAnggaranId) {
            $posAnggaranId = $posDefault ? (int)$posDefault['id'] : NULL;
        }

        $posDipilih = $posAnggaranId ? $this->Pos_model->find((int)$posAnggaranId) : NULL;
        if ($posAnggaranId && !$posDipilih) {
            $this->respond(FALSE, 'Pos anggaran tidak ditemukan.', array(), array('pos_anggaran_id' => 'Tidak valid.'), 422);
        }
        if ($posDipilih && ((int)$posDipilih['tahun_anggaran_id'] !== $year || empty($posDipilih['is_active']))) {
            $this->respond(FALSE, 'Pos anggaran tidak tersedia pada tahun kegiatan.', array(), array(), 422);
        }

        if (!$posAnggaranId) {
            $this->respond(FALSE, 'Pos anggaran wajib dipilih.', array(), array('pos_anggaran_id' => 'Wajib dipilih.'), 422);
        }

        // VALIDASI SALDO POS YANG DIBEBANI:
        if ($posAnggaranId) {
            $saldoPos = $this->Pos_model->saldo_pos($posAnggaranId);
            if ($saldoPos) {
                $totalBiayaPeserta = 0.0;
                foreach (($payload['biaya'] ?? array()) as $cost) {
                    $totalBiayaPeserta += (float)($cost['volume'] ?? 0) * clean_number($cost['tarif'] ?? 0);
                }

                $biayaSebelumnya = 0.0;
                if ($kegiatanPesertaId) {
                    $prevCost = $this->db->query("
                        SELECT COALESCE(SUM(volume * tarif), 0) AS total
                        FROM kegiatan_biaya
                        WHERE kegiatan_peserta_id = ?
                    ", array($kegiatanPesertaId))->row_array();
                    $biayaSebelumnya = (float)($prevCost['total'] ?? 0);
                }

                $sisaTersedia = (float)$saldoPos['sisa'] + $biayaSebelumnya;
                if ($this->config->item('mode_saldo') === 'BLOKIR' && $totalBiayaPeserta > $sisaTersedia) {
                    $this->respond(FALSE, 'Saldo Pos Anggaran (' . $saldoPos['pos_nama'] . ') tidak mencukupi! Sisa pagu: ' . rupiah($sisaTersedia) . ', biaya diajukan: ' . rupiah($totalBiayaPeserta), array(), array(), 422);
                }
            }
        }

        // Siapkan data simpan
        $pesertaData = array(
            'id'                => $kegiatanPesertaId,
            'penerima_id'       => $penerimaId,
            'pos_anggaran_id'   => $posAnggaranId,
            'alasan_pengalihan' => $alasanPengalihan,
            'dana_awal'         => $danaAwal,
            'sumber_dana_id'    => !empty($payload['sumber_dana_id']) ? (int)$payload['sumber_dana_id'] : NULL,
            'biaya'             => $payload['biaya'] ?? array(),
        );

        $savedKpId = $this->Kegiatan_model->save_peserta($kegiatanId, $pesertaData);

        $this->Audit_model->write(
            'KEGIATAN_PESERTA',
            $savedKpId,
            $kegiatanPesertaId ? 'EDIT_PESERTA' : 'TAMBAH_PESERTA',
            array('kegiatan_id' => $kegiatanId),
            $pesertaData,
            (int)$this->user['id']
        );

        $this->respond(TRUE, 'Peserta dan rincian biaya berhasil disimpan.', array(
            'kegiatan_peserta_id' => encrypt_id($savedKpId)
        ));
    }

    /**
     * Simpan alokasi pagu kegiatan. Satu kegiatan boleh memakai beberapa pos.
     */
    public function save_alokasi(): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.update'));
        $payload = input_json();
        $kegiatanId = decrypt_id($payload['kegiatan_id'] ?? $this->input->post('kegiatan_id'));
        $posId = decrypt_id($payload['pos_anggaran_id'] ?? $this->input->post('pos_anggaran_id'));
        $allocationId = decrypt_id($payload['id'] ?? $this->input->post('id'));
        $nilai = clean_number($payload['nilai_alokasi'] ?? $this->input->post('nilai_alokasi', TRUE));
        $keterangan = trim((string)($payload['keterangan'] ?? $this->input->post('keterangan', TRUE)));

        if (!$kegiatanId || !$posId || $nilai < 0) {
            $this->respond(FALSE, 'Kegiatan, pos anggaran, dan nilai alokasi wajib valid.', array(), array(), 422);
        }
        $kegiatan = $this->Kegiatan_model->find($kegiatanId);
        $pos = $this->Pos_model->find($posId);
        if (!$kegiatan || !$pos) $this->respond(FALSE, 'Kegiatan atau pos anggaran tidak ditemukan.', array(), array(), 404);
        if ((int)$pos['tahun_anggaran_id'] !== (int)$kegiatan['tahun_anggaran_id'] || empty($pos['is_active'])) {
            $this->respond(FALSE, 'Pos anggaran harus berasal dari tahun kegiatan dan masih aktif.', array(), array(), 422);
        }
        if ($this->rbac->scope('kegiatan') === 'own' && !$this->Kegiatan_model->belongs_to_penerima($kegiatanId, $this->own_penerima_id())) {
            $this->deny();
        }
        $this->db->select_sum('nilai_alokasi')->where('pos_anggaran_id', $posId);
        if ($allocationId) $this->db->where('id !=', $allocationId);
        $allocatedElsewhere = (float)(($this->db->get('kegiatan_anggaran')->row_array()['nilai_alokasi'] ?? 0));
        if ((float)$pos['pagu'] > 0 && ($allocatedElsewhere + (float)$nilai) > (float)$pos['pagu']) {
            $this->respond(FALSE, 'Total alokasi pada pos melebihi pagu pos anggaran. Pagu: ' . rupiah($pos['pagu']) . ', sudah dialokasikan: ' . rupiah($allocatedElsewhere), array(), array(), 422);
        }
        $savedId = $this->Kegiatan_model->save_alokasi_anggaran($kegiatanId, $posId, (float)$nilai, $keterangan ?: null, $allocationId ?: null);
        $this->Audit_model->write('KEGIATAN_ANGGARAN', $savedId, $allocationId ? 'EDIT_ALOKASI' : 'TAMBAH_ALOKASI', array(), array(
            'kegiatan_id' => $kegiatanId, 'pos_anggaran_id' => $posId, 'nilai_alokasi' => $nilai, 'keterangan' => $keterangan
        ), (int)$this->user['id']);
        $this->respond(TRUE, 'Alokasi anggaran kegiatan berhasil disimpan.');
    }

    public function delete_alokasi($id): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.update'));
        $allocationId = decrypt_id($id);
        if (!$allocationId) $this->respond(FALSE, 'ID alokasi tidak valid.', array(), array(), 422);
        $allocation = $this->db->get_where('kegiatan_anggaran', array('id' => $allocationId))->row_array();
        if (!$allocation) $this->respond(FALSE, 'Alokasi tidak ditemukan.', array(), array(), 404);
        if ($this->rbac->scope('kegiatan') === 'own' && !$this->Kegiatan_model->belongs_to_penerima((int)$allocation['kegiatan_id'], $this->own_penerima_id())) {
            $this->deny();
        }
        if (!$this->Kegiatan_model->delete_alokasi_anggaran($allocationId)) {
            $this->respond(FALSE, 'Alokasi tidak dapat dihapus karena sudah dipakai peserta atau memiliki realisasi biaya.', array(), array(), 422);
        }
        $this->respond(TRUE, 'Alokasi anggaran dihapus.');
    }

    /**
     * Tambah biaya umum langsung ke alokasi, untuk rapat/fasilitas yang tidak
     * menggunakan peserta perjalanan dinas.
     */
    public function save_biaya_umum(): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.update'));
        $payload = input_json();
        $kegiatanId = decrypt_id($payload['kegiatan_id'] ?? $this->input->post('kegiatan_id'));
        $allocationId = decrypt_id($payload['kegiatan_anggaran_id'] ?? $this->input->post('kegiatan_anggaran_id'));
        $jenisId = (int)($payload['jenis_biaya_id'] ?? $this->input->post('jenis_biaya_id'));
        $volume = (float)($payload['volume'] ?? $this->input->post('volume'));
        $tarif = clean_number($payload['tarif'] ?? $this->input->post('tarif'));
        if (!$kegiatanId || !$allocationId || !$jenisId || $volume <= 0 || $tarif < 0) {
            $this->respond(FALSE, 'Alokasi, komponen biaya, volume, dan tarif wajib diisi dengan benar.', array(), array(), 422);
        }
        $allocation = $this->db->get_where('kegiatan_anggaran', array('id' => $allocationId, 'kegiatan_id' => $kegiatanId))->row_array();
        if (!$allocation) $this->respond(FALSE, 'Alokasi anggaran tidak ditemukan.', array(), array(), 404);
        if ($this->rbac->scope('kegiatan') === 'own' && !$this->Kegiatan_model->belongs_to_penerima($kegiatanId, $this->own_penerima_id())) $this->deny();
        $currentCost = $this->db->query('SELECT COALESCE(SUM(volume * tarif), 0) AS total FROM kegiatan_biaya WHERE kegiatan_anggaran_id = ?', array($allocationId))->row_array();
        $newTotal = (float)($currentCost['total'] ?? 0) + ($volume * (float)$tarif);
        if ($newTotal > (float)$allocation['nilai_alokasi']) {
            $this->respond(FALSE, 'Realisasi melebihi alokasi kegiatan. Alokasi: ' . rupiah($allocation['nilai_alokasi']), array(), array(), 422);
        }
        $costId = $this->Kegiatan_model->save_biaya_umum($kegiatanId, $allocationId, $jenisId, $volume, (float)$tarif);
        if (!$costId) $this->respond(FALSE, 'Biaya umum gagal disimpan.', array(), array(), 422);
        $this->Audit_model->write('KEGIATAN_BIAYA', $costId, 'TAMBAH_BIAYA_UMUM', array(), $payload, (int)$this->user['id']);
        $this->respond(TRUE, 'Biaya kegiatan berhasil disimpan.');
    }

    /**
     * Hapus Peserta dari Rincian Kegiatan
     */
    public function delete_peserta($id): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.update'));
        $rawId = decrypt_id($id);
        if (!$rawId) $this->respond(FALSE, 'ID tidak valid.', array(), array(), 422);

        if ($this->rbac->scope('kegiatan') === 'own') {
            $ownedParticipant = $this->db->from('kegiatan_peserta')
                ->where('id', $rawId)
                ->where('penerima_id', $this->own_penerima_id())
                ->count_all_results();
            if (!$ownedParticipant) $this->deny();
        }

        $ok = $this->Kegiatan_model->delete_peserta($rawId);
        if ($ok) {
            $this->Audit_model->write('KEGIATAN_PESERTA', $rawId, 'HAPUS_PESERTA', array(), array(), (int)$this->user['id']);
            $this->respond(TRUE, 'Peserta berhasil dihapus dari kegiatan.');
        } else {
            $this->respond(FALSE, 'Gagal menghapus peserta.', array(), array(), 500);
        }
    }

    /**
     * Bukti Rekapitulasi Perjalanan Dinas per Kegiatan
     */
    public function bukti_rekap($id): void {
        $this->require_any(array('kegiatan.view_all', 'kegiatan.view_own'));
        $kegiatanId = decrypt_id($id);
        if (!$kegiatanId) show_404();

        $kegiatan = $this->Kegiatan_model->get_full_detail($kegiatanId);
        if (!$kegiatan) show_404();

        if ($this->rbac->scope('kegiatan') === 'own'
            && !$this->Kegiatan_model->belongs_to_penerima($kegiatanId, $this->own_penerima_id())) {
            $this->deny();
        }

        if (empty($kegiatan['dicetak_at'])) {
            $printedAt = date('Y-m-d H:i:s');
            $this->db->where('id', $kegiatanId)->update('kegiatan', array(
                'dicetak_at' => $printedAt,
                'dicetak_oleh' => (int)$this->user['id'],
            ));
            $this->Audit_model->write('KEGIATAN', $kegiatanId, 'CETAK_BUKTI', array(), array(), (int)$this->user['id']);
            $kegiatan['dicetak_at'] = $printedAt;
        }

        // Hitung durasi hari pelaksanaan
        $start = strtotime($kegiatan['tanggal_mulai']);
        $end   = strtotime($kegiatan['tanggal_selesai']);
        $durasiHari = max(1, round(($end - $start) / 86400) + 1);

        $this->load->view('layout/main', array(
            'title' => 'Bukti Rekapitulasi: ' . ($kegiatan['no_surat_tugas'] ?: 'Kegiatan #' . $kegiatanId),
            'view'  => 'kegiatan/bukti_rekap',
            'data'  => array(
                'kegiatan'    => $kegiatan,
                'durasiHari'  => $durasiHari,
                'terbilang'   => terbilang_indo($kegiatan['grand_total']),
                'setting'     => $this->Pengaturan_model->get(),
            )
        ));
    }

    /**
     * Simpan Kegiatan Lengkap (Graph/Legacy support)
     */
    public function save(): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.ajukan'));
        $payload = input_json();
        $payload['tahun_anggaran_id'] = (int)($payload['tahun_anggaran_id'] ?? $this->session->userdata('tahun_aktif_id'));
        $payload['status'] = ($this->rbac->can('kegiatan.create') && $this->rbac->can('kegiatan.verify') && ($payload['status'] ?? 'DRAFT') === 'AKTIF') ? 'AKTIF' : 'DRAFT';

        if ($this->rbac->scope('kegiatan') === 'own') {
            $own = $this->own_penerima_id();
            $found = false;
            foreach (($payload['peserta'] ?? array()) as &$person) {
                if ((int)$person['penerima_id'] === $own) $found = true;
            }
            unset($person);
            if (!$found) $payload['peserta'][] = array('penerima_id' => $own, 'biaya' => array());
        }

        $result = $this->anggaran_service->validate_and_save($payload, (int)$this->user['id']);
        if (isset($result['error'])) $this->respond(FALSE, $result['error'], array(), array(), 422);
        $this->respond(TRUE, 'Kegiatan tersimpan', $result);
    }

    public function ajukan($id): void {
        $id = decrypt_id($id);
        $this->require_permission('kegiatan.ajukan');
        $row = $this->Kegiatan_model->find($id);
        if (!$row) $this->respond(FALSE, 'Kegiatan tidak ditemukan.', array(), array(), 404);
        if ($this->rbac->scope('kegiatan') === 'own' && !$this->Kegiatan_model->belongs_to_penerima($id, $this->own_penerima_id())) $this->deny();
        if (!in_array($row['status'], array('DRAFT', 'DITOLAK'), true)) $this->respond(FALSE, 'Hanya DRAFT atau DITOLAK yang dapat diajukan.', array(), array(), 422);

        $this->db->where('id', $id)->update('kegiatan', array(
            'status'        => 'DIAJUKAN',
            'diajukan_oleh' => (int)$this->user['id'],
            'diajukan_at'   => date('Y-m-d H:i:s')
        ));
        $this->respond(TRUE, 'Kegiatan berhasil diajukan untuk verifikasi.');
    }

    public function antrian(): void {
        $this->require_permission('kegiatan.verify');
        $this->load->view('layout/main', array(
            'title' => 'Antrian Verifikasi',
            'view'  => 'kegiatan/antrian',
            'data'  => array('rows' => array())
        ));
    }

    public function antrian_data(): void {
        $this->require_permission('kegiatan.verify');
        $columnMap = array(
            0 => 'k.tema',
            1 => 'k.tanggal_mulai',
            2 => 'k.diajukan_oleh'
        );
        $params = $this->dt_params($columnMap, 'k.created_at', 'ASC');
        $tab = (string)($params['raw_input']['tab'] ?? 'queue');
        $isHistory = $tab === 'history';
        $filter = array('verification_tab' => $isHistory ? 'history' : 'queue');

        $recordsTotal = $this->Kegiatan_model->count_all($filter, 'all');
        $recordsFiltered = $this->Kegiatan_model->count_filtered($filter, 'all', null, $params['search']);
        $rows = $this->Kegiatan_model->get_datatable(
            $filter,
            'all',
            null,
            $params['start'],
            $params['length'],
            $params['search'],
            $params['order_column'],
            $params['order_dir']
        );

        $data = array();
        foreach ($rows as $row) {
            $encId = encrypt_id($row['id']);
            $nama = html_escape($row['tema'] ?: $row['nama_kegiatan']);

            $temaHtml = '<div class="fw-semibold"><a href="' . site_url('kegiatan/rincian/' . $encId) . '" class="text-primary text-decoration-none fw-bold" title="Buka Rincian Kegiatan untuk Verifikasi">'
                . $nama . '<i class="bi bi-box-arrow-up-right ms-1 small"></i></a>'
                . '<div class="small text-muted">No: <code>' . html_escape($row['no_surat_tugas'] ?: '-') . '</code></div></div>';

            $tglHtml = '<div><div>' . tgl_indo($row['tanggal_mulai']) . '</div><small class="text-muted">s/d ' . tgl_indo($row['tanggal_selesai']) . '</small></div>';
            if ($isHistory) {
                $statusClass = $row['status'] === 'AKTIF' ? 'success' : ($row['status'] === 'DITOLAK' ? 'danger' : 'secondary');
                $pengajuHtml = '<div><span class="badge text-bg-' . $statusClass . '">' . html_escape($row['status']) . '</span>'
                    . '<div class="small text-muted mt-1">Diverifikasi: ' . ($row['diverifikasi_at'] ? tgl_indo(substr($row['diverifikasi_at'], 0, 10)) : '-') . '</div></div>';
                $aksiHtml = '<div class="text-end text-nowrap">'
                    . '<a href="' . site_url('kegiatan/bukti_rekap/' . $encId) . '" class="btn btn-sm btn-outline-info me-1" title="Cetak bukti ulang"><i class="bi bi-printer"></i> Cetak Bukti</a>'
                    . '<a href="' . site_url('kegiatan/rincian/' . $encId) . '" class="btn btn-sm btn-outline-primary" title="Lihat rincian"><i class="bi bi-eye"></i></a>'
                    . '</div>';
            } else {
                $pengajuHtml = '<div><span class="badge text-bg-light border">User #' . (int)$row['diajukan_oleh'] . '</span></div>';
                $aksiHtml = '<div class="text-end text-nowrap">'
                    . '<a href="' . site_url('kegiatan/rincian/' . $encId) . '" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 shadow-sm me-1" title="Periksa Rincian & Verifikasi"><i class="bi bi-search"></i> <span>Periksa Rincian</span></a>'
                    . '<button class="btn btn-sm btn-success js-verify me-1" data-id="' . $encId . '" data-nama="' . $nama . '" title="Setujui Langsung"><i class="bi bi-check-lg"></i></button>'
                    . '<button class="btn btn-sm btn-outline-danger js-reject" data-id="' . $encId . '" data-nama="' . $nama . '" title="Tolak"><i class="bi bi-x-lg"></i></button>'
                    . '</div>';
            }

            $data[] = array(
                $temaHtml,
                $tglHtml,
                $pengajuHtml,
                $aksiHtml
            );
        }

        $this->respond_dt($params['draw'], $recordsTotal, $recordsFiltered, $data);
    }

    public function verify($id): void {
        $id = decrypt_id($id);
        $this->require_permission('kegiatan.verify');
        $row = $this->Kegiatan_model->find($id);
        if (!$row) $this->respond(FALSE, 'Kegiatan tidak ditemukan.', array(), array(), 404);
        if ((int)$row['diajukan_oleh'] === (int)$this->user['id']) $this->respond(FALSE, 'Pengaju tidak boleh memverifikasi kegiatannya sendiri.', array(), array(), 403);

        $action = $this->input->post('action', TRUE);
        if ($action === 'tolak') {
            $note = trim((string)$this->input->post('catatan_verifikasi', TRUE));
            if (!$note) $this->respond(FALSE, 'Catatan penolakan wajib diisi.', array(), array('catatan_verifikasi' => 'Wajib diisi.'), 422);
            $this->db->where('id', $id)->update('kegiatan', array(
                'status'             => 'DITOLAK',
                'catatan_verifikasi' => $note,
                'diverifikasi_oleh'  => (int)$this->user['id'],
                'diverifikasi_at'    => date('Y-m-d H:i:s')
            ));
            $this->respond(TRUE, 'Kegiatan ditolak.');
        }

        $result = $this->anggaran_service->validate_existing($id, (int)$this->user['id']);
        if (isset($result['error'])) $this->respond(FALSE, $result['error'], array(), array(), 422);

        $this->db->where('id', $id)->update('kegiatan', array(
            'status'            => 'AKTIF',
            'diverifikasi_oleh' => (int)$this->user['id'],
            'diverifikasi_at'   => date('Y-m-d H:i:s')
        ));
        $this->respond(TRUE, 'Kegiatan disetujui.');
    }

    public function saldo(): void {
        $this->require_any(array('kegiatan.create', 'kegiatan.update'));
        $year = (int)$this->session->userdata('tahun_aktif_id');
        $ids = array_map(function($x){ return decrypt_id($x) ?: (int)$x; }, (array)$this->input->post('penerima_ids'));
        if ($this->rbac->scope('kegiatan') === 'own') $ids = array($this->own_penerima_id());
        $this->respond(TRUE, 'OK', array(
            'rows' => $this->db->where('tahun_anggaran_id', $year)->where_in('penerima_id', $ids)->get('v_sisa_anggaran')->result_array()
        ));
    }

    public function batal($id): void {
        $id = decrypt_id($id);
        $this->require_permission('kegiatan.cancel');
        $reason = trim((string)$this->input->post('alasan', TRUE));
        if (!$reason) $this->respond(FALSE, 'Alasan pembatalan wajib diisi.', array(), array('alasan' => 'Wajib diisi.'), 422);

        $row = $this->Kegiatan_model->find($id);
        if (!$row) $this->respond(FALSE, 'Kegiatan tidak ditemukan.', array(), array(), 404);
        if ($row['status'] !== 'AKTIF') $this->respond(FALSE, 'Hanya kegiatan AKTIF yang dapat dibatalkan.', array(), array(), 422);

        $this->db->where('id', $id)->update('kegiatan', array(
            'status'       => 'BATAL',
            'alasan_batal' => $reason,
            'updated_at'   => date('Y-m-d H:i:s')
        ));
        $this->respond(TRUE, 'Kegiatan dibatalkan.');
    }
}

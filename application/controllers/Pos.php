<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pos Controller – Halaman Master Pos Anggaran
 *
 * Mengelola:
 * - Daftar pos anggaran per tahun (DPRD & PENDAMPING)
 * - Revisi pagu pos
 * - Penugasan pemegang pos (khusus PENDAMPING)
 * - Riwayat penugasan
 * - Terapkan pagu standar (1 klik)
 * - Buat pos otomatis (dari daftar penerima DPRD + 8 pos baku PENDAMPING)
 */
class Pos extends MY_Controller
{
    public function __construct() {
        parent::__construct();
        $this->require_login();
        $this->load->model(['Pos_model', 'Tahun_model', 'Penerima_model', 'Audit_model']);
    }

    // ─── Daftar Pos per Tahun ────────────────────────────────────────

    public function index($tahunEnc): void {
        $this->require_permission('tahun.view');
        $tahunId = decrypt_id($tahunEnc);
        if (!$tahunId) show_404();

        $tahun = $this->Tahun_model->find($tahunId);
        if (!$tahun) show_404();

        $rows = $this->Pos_model->all_by_tahun($tahunId);

        // Hitung total KPI
        $totalPaguDprd = $totalPaguPend = $totalRealisasi = $totalSisa = 0.0;
        $saldoList = $this->Pos_model->saldo_by_tahun($tahunId);
        $saldoMap  = array_column($saldoList, null, 'pos_anggaran_id');

        foreach ($rows as &$row) {
            $s = $saldoMap[$row['id']] ?? [];
            $row['pagu']       = (float)($s['pagu'] ?? $row['pagu']);
            $row['realisasi']  = (float)($s['realisasi'] ?? 0);
            $row['sisa']       = (float)($s['sisa'] ?? $row['pagu']);
            if ($row['tipe'] === 'DPRD')       $totalPaguDprd += $row['pagu'];
            if ($row['tipe'] === 'PENDAMPING') $totalPaguPend += $row['pagu'];
            $totalRealisasi += $row['realisasi'];
            $totalSisa      += $row['sisa'];
        }
        unset($row);

        // Daftar pendamping aktif (untuk form penugasan)
        $pendamping = $this->Penerima_model->all_by_tipe('PENDAMPING');

        $this->load->view('layout/main', [
            'title' => 'Pos Anggaran ' . $tahun['tahun'],
            'view'  => 'pos/index',
            'data'  => [
                'tahun'          => $tahun,
                'rows'           => $rows,
                'totalPaguDprd'  => $totalPaguDprd,
                'totalPaguPend'  => $totalPaguPend,
                'totalRealisasi' => $totalRealisasi,
                'totalSisa'      => $totalSisa,
                'pendamping'     => $pendamping,
                'can_manage'     => can('anggaran.manage'),
            ]
        ]);
    }

    // ─── Buat Pos Otomatis ───────────────────────────────────────────

    public function buat_pos($tahunEnc): void {
        $this->require_permission('anggaran.manage');
        $tahunId = decrypt_id($tahunEnc);
        if (!$tahunId) $this->respond(false, 'Tahun tidak valid.', [], [], 422);

        $tahun = $this->Tahun_model->find($tahunId);
        if (!$tahun) $this->respond(false, 'Tahun tidak ditemukan.', [], [], 404);
        if (!empty($tahun['is_locked'])) $this->respond(false, 'Tahun sudah dikunci.', [], [], 422);

        $cDprd = $this->Pos_model->buat_pos_dprd_otomatis($tahunId);
        $cPend = $this->Pos_model->buat_pos_pendamping_baku($tahunId);

        $this->Audit_model->write('POS_ANGGARAN', $tahunId, 'BUAT_OTOMATIS',
            [], ['dprd_created' => $cDprd, 'pendamping_created' => $cPend],
            (int)$this->user['id']);

        $this->respond(true, "Berhasil membuat {$cDprd} pos DPRD dan {$cPend} pos pendamping baru.");
    }

    // ─── Terapkan Pagu Standar DPRD & Pendamping ───────────────────

    public function terapkan_standar(): void {
        $this->require_permission('anggaran.manage');
        $tahunId   = decrypt_id($this->input->post('tahun_id'));
        $overwrite = (bool)$this->input->post('overwrite');

        $standar = [
            'KETUA'      => clean_number($this->input->post('pagu_ketua')),
            'WAKIL_KETUA'=> clean_number($this->input->post('pagu_wakil_ketua')),
            'ANGGOTA'    => clean_number($this->input->post('pagu_anggota')),
        ];
        $paguPendamping = clean_number($this->input->post('pagu_pendamping'));

        if (!$tahunId) $this->respond(false, 'Tahun tidak valid.', [], [], 422);

        $tahun = $this->Tahun_model->find($tahunId);
        if (!$tahun || !empty($tahun['is_locked'])) {
            $this->respond(false, 'Tahun tidak ditemukan atau sudah dikunci.', [], [], 422);
        }

        $n = $this->Pos_model->terapkan_pagu_standar($tahunId, $standar, $overwrite);
        $nPendamping = $this->Pos_model->terapkan_pagu_pendamping($tahunId, $paguPendamping, $overwrite);

        $this->Audit_model->write('POS_ANGGARAN', $tahunId, 'TERAPKAN_STANDAR',
            [], array_merge($standar, [
                'pagu_pendamping' => $paguPendamping,
                'overwrite' => $overwrite,
                'diperbarui_dprd' => $n,
                'diperbarui_pendamping' => $nPendamping,
            ]),
            (int)$this->user['id']);

        $this->respond(true, "{$n} pos DPRD dan {$nPendamping} pos pendamping berhasil diperbarui pagu standarnya.");
    }

    // ─── Revisi Pagu Pos ─────────────────────────────────────────────

    public function revisi_pagu(): void {
        $this->require_permission('anggaran.manage');

        $posId   = decrypt_id($this->input->post('pos_id'));
        $paguBaru = clean_number($this->input->post('pagu_baru'));
        $alasan  = trim((string)$this->input->post('alasan', true));

        if (!$posId) $this->respond(false, 'ID Pos tidak valid.', [], [], 422);
        if (empty($alasan)) $this->respond(false, 'Alasan revisi wajib diisi.', [], ['alasan' => 'Wajib diisi.'], 422);
        if ($paguBaru < 0) $this->respond(false, 'Pagu tidak boleh negatif.', [], [], 422);

        $pos = $this->Pos_model->find($posId);
        if (!$pos) $this->respond(false, 'Pos tidak ditemukan.', [], [], 404);

        // Cek kunci tahun
        $tahun = $this->Tahun_model->find((int)$pos['tahun_anggaran_id']);
        if (!$tahun || !empty($tahun['is_locked'])) {
            $this->respond(false, 'Tahun anggaran sudah dikunci.', [], [], 422);
        }

        // Cek realisasi – pagu baru tidak boleh < realisasi
        $saldo = $this->Pos_model->saldo_pos($posId);
        $realisasi = (float)($saldo['realisasi'] ?? 0);
        $paguLama  = (float)$pos['pagu'];

        if ($paguBaru < $realisasi) {
            $this->respond(false,
                'Pagu baru (' . rupiah($paguBaru) . ') tidak boleh lebih kecil dari realisasi terpakai (' . rupiah($realisasi) . ').',
                [], ['pagu_baru' => 'Minimal ' . rupiah($realisasi)], 422
            );
        }
        if (abs($paguBaru - $paguLama) < 0.01) {
            $this->respond(false, 'Nilai pagu sama dengan saat ini. Tidak ada perubahan.', [], [], 422);
        }

        $this->db->trans_begin();
        $this->Pos_model->update_pagu($posId, $paguBaru);

        $selisih  = $paguBaru - $paguLama;
        $sisaBaru = $paguBaru - $realisasi;

        $this->Audit_model->write('POS_ANGGARAN', $posId, 'REVISI_PAGU',
            ['pagu_lama' => $paguLama, 'realisasi' => $realisasi, 'sisa_lama' => $paguLama - $realisasi],
            ['pagu_baru' => $paguBaru, 'selisih' => $selisih, 'sisa_baru' => $sisaBaru, 'alasan' => $alasan],
            (int)$this->user['id']);

        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->respond(false, 'Gagal menyimpan revisi pagu.', [], [], 500);
        }
        $this->db->trans_commit();

        $tipe = $selisih > 0 ? 'Penambahan' : 'Pengurangan';
        $this->respond(true, "Revisi pagu berhasil! ({$tipe} " . rupiah(abs($selisih)) . '). Dicatat di Audit Log.', [
            'pagu_baru' => $paguBaru,
            'selisih'   => $selisih,
            'sisa_baru' => $sisaBaru,
        ]);
    }

    // ─── Penugasan Pos (PENDAMPING) ──────────────────────────────────

    /**
     * Simpan penugasan baru: ganti orang yang memegang pos pendamping
     */
    public function simpan_penugasan(): void {
        $this->require_permission('anggaran.manage');

        $posId      = decrypt_id($this->input->post('pos_id'));
        $penerimaId = decrypt_id($this->input->post('penerima_id'));
        $mulai      = $this->input->post('tanggal_mulai', true);
        $keterangan = trim((string)$this->input->post('keterangan', true));

        if (!$posId || !$penerimaId) $this->respond(false, 'ID Pos atau Penerima tidak valid.', [], [], 422);
        if (empty($mulai)) $this->respond(false, 'Tanggal mulai penugasan wajib diisi.', [], [], 422);

        $pos = $this->Pos_model->find($posId);
        if (!$pos || $pos['tipe'] !== 'PENDAMPING') {
            $this->respond(false, 'Hanya pos PENDAMPING yang bisa ditugaskan.', [], [], 422);
        }

        $tahun = $this->Tahun_model->find((int)$pos['tahun_anggaran_id']);
        if (!$tahun || !empty($tahun['is_locked'])) {
            $this->respond(false, 'Tahun anggaran sudah dikunci.', [], [], 422);
        }

        $tugasId = $this->Pos_model->simpan_penugasan($posId, $penerimaId, $mulai, $keterangan, (int)$this->user['id']);

        $this->Audit_model->write('PENUGASAN_POS', $tugasId, 'TUGASKAN',
            [], ['pos_id' => $posId, 'penerima_id' => $penerimaId, 'mulai' => $mulai, 'keterangan' => $keterangan],
            (int)$this->user['id']);

        $this->respond(true, 'Penugasan berhasil disimpan. Pemegang pos diperbarui.', [
            'penugasan_id' => encrypt_id($tugasId)
        ]);
    }

    /**
     * Tutup penugasan aktif suatu pos (pos menjadi kosong)
     */
    public function tutup_penugasan(): void {
        $this->require_permission('anggaran.manage');

        $posId   = decrypt_id($this->input->post('pos_id'));
        $selesai = $this->input->post('tanggal_selesai', true) ?: date('Y-m-d');

        if (!$posId) $this->respond(false, 'ID Pos tidak valid.', [], [], 422);

        $pos = $this->Pos_model->find($posId);
        if (!$pos || $pos['tipe'] !== 'PENDAMPING') {
            $this->respond(false, 'Hanya pos PENDAMPING yang dapat ditutup penugasannya.', [], [], 422);
        }

        $this->Pos_model->tutup_penugasan($posId, $selesai);

        $this->Audit_model->write('PENUGASAN_POS', $posId, 'TUTUP_PENUGASAN',
            [], ['tanggal_selesai' => $selesai], (int)$this->user['id']);

        $this->respond(true, 'Penugasan pos ditutup. Pos kini kosong.');
    }

    /**
     * Riwayat penugasan satu pos (AJAX)
     */
    public function riwayat_penugasan($posEnc): void {
        $this->require_permission('tahun.view');
        $posId = decrypt_id($posEnc);
        if (!$posId) $this->respond(false, 'ID tidak valid.', [], [], 422);

        $riwayat = $this->Pos_model->riwayat_penugasan($posId);
        $this->respond(true, 'OK', ['riwayat' => $riwayat]);
    }

    /**
     * Cari pos default penerima (AJAX – dipakai di form input peserta kegiatan)
     */
    public function pos_default(): void {
        $this->require_any(['kegiatan.create', 'kegiatan.update']);
        $penerimaId = decrypt_id($this->input->get('penerima_id'));
        $tahunId    = (int)($this->input->get('tahun_id') ?: $this->session->userdata('tahun_aktif_id'));

        if (!$penerimaId) $this->respond(false, 'ID Penerima tidak valid.', [], [], 422);

        $pos   = $this->Pos_model->pos_default_penerima($penerimaId, $tahunId);
        $saldo = $pos ? $this->Pos_model->saldo_pos((int)$pos['id']) : null;

        $this->respond(true, 'OK', [
            'pos'   => $pos   ? array_merge($pos,   ['id_enc' => encrypt_id($pos['id'])]) : null,
            'saldo' => $saldo ?: ['pagu' => 0, 'realisasi' => 0, 'sisa' => 0],
        ]);
    }

    /**
     * Semua pos pendamping aktif satu tahun (AJAX – untuk dropdown pemilihan pos di kegiatan)
     */
    public function pos_pendamping($tahunEnc): void {
        $this->require_any(['kegiatan.create', 'kegiatan.update']);
        $tahunId = decrypt_id($tahunEnc) ?: (int)$this->session->userdata('tahun_aktif_id');

        $rows = $this->Pos_model->all_by_tahun($tahunId, 'PENDAMPING');
        $result = [];
        foreach ($rows as $r) {
            $saldo = $this->Pos_model->saldo_pos((int)$r['id']);
            $result[] = [
                'id'           => encrypt_id((int)$r['id']),
                'nama'         => $r['nama'],
                'kode'         => $r['kode'],
                'pemegang'     => $r['pemegang_nama'] ?? '-',
                'pagu'         => (float)($saldo['pagu'] ?? 0),
                'sisa'         => (float)($saldo['sisa'] ?? 0),
            ];
        }
        $this->respond(true, 'OK', ['rows' => $result]);
    }
}


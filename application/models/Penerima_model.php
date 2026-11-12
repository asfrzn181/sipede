<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Penerima_model extends CI_Model {
    public function all(): array {
        return $this->db->order_by('tipe')->order_by('nama')->get('penerima')->result_array();
    }

    public function all_active(?int $tahun = null): array {
        $this->db->from('penerima p')->where('p.is_active', 1);
        if ($tahun) $this->apply_period_filter($tahun);
        return $this->db->order_by('p.tipe')->order_by('p.nama')->get()->result_array();
    }

    public function all_by_tipe(string $tipe): array {
        return $this->db->where('tipe', $tipe)->where('is_active', 1)
            ->order_by('nama')->get('penerima')->result_array();
    }

    public function with_budget_source(int $year): array {
        $tahunRow = $this->db->get_where('tahun_anggaran', ['id' => $year])->row_array();
        $tahun = (int)($tahunRow['tahun'] ?? 0);
        $this->db
            ->select('p.*,a.sumber_dana_id,sd.kode sumber_dana_kode,sd.nama sumber_dana_nama')
            ->from('penerima p')
            ->join('anggaran a','a.penerima_id=p.id AND a.tahun_anggaran_id='.(int)$year,'left')
            ->join('sumber_dana sd','sd.id=a.sumber_dana_id','left')
            ->where('p.is_active',1);
        if ($tahun) $this->apply_period_filter($tahun);
        return $this->db->order_by('p.tipe')->order_by('p.nama')->get()->result_array();
    }

    /** Anggota DPRD hanya berlaku pada rentang periode jabatannya. */
    public function berlaku_pada_tahun(array $penerima, int $tahun): bool {
        if (($penerima['tipe'] ?? '') !== 'DPRD') return true;
        $mulai = !empty($penerima['periode_mulai']) ? (int)$penerima['periode_mulai'] : null;
        $akhir = !empty($penerima['periode_akhir']) ? (int)$penerima['periode_akhir'] : null;
        return (!$mulai || $mulai <= $tahun) && (!$akhir || $akhir >= $tahun);
    }

    private function apply_period_filter(int $tahun): void {
        $this->db->group_start();
        $this->db->where('p.tipe !=', 'DPRD');
        $this->db->or_group_start();
        $this->db->group_start();
        $this->db->where('p.periode_mulai IS NULL', null, false);
        $this->db->or_where('p.periode_mulai <=', $tahun);
        $this->db->group_end();
        $this->db->group_start();
        $this->db->where('p.periode_akhir IS NULL', null, false);
        $this->db->or_where('p.periode_akhir >=', $tahun);
        $this->db->group_end();
        $this->db->group_end();
        $this->db->group_end();
    }

    public function count_all(?string $tipe = null): int {
        if ($tipe) $this->db->where('tipe', $tipe);
        return (int)$this->db->count_all_results('penerima');
    }

    public function count_filtered(string $search = '', ?string $tipe = null): int {
        if ($tipe) $this->db->where('tipe', $tipe);
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('nama', $search);
            $this->db->or_like('tipe', $search);
            $this->db->or_like('jabatan', $search);
            $this->db->group_end();
        }
        return (int)$this->db->count_all_results('penerima');
    }

    public function get_datatable(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'nama', string $orderDir = 'ASC', ?string $tipe = null): array {
        if ($tipe) $this->db->where('tipe', $tipe);
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('nama', $search);
            $this->db->or_like('tipe', $search);
            $this->db->or_like('jabatan', $search);
            $this->db->group_end();
        }
        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('tipe', 'ASC')->order_by('nama', 'ASC');
        }
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        return $this->db->get('penerima')->result_array();
    }

    public function find(int $id): ?array {
        return $this->db->get_where('penerima', array('id' => $id))->row_array() ?: NULL;
    }

    public function save(array $data, ?int $id = NULL): int {
        if ($id) {
            $this->db->where('id', $id)->update('penerima', $data);
            return $id;
        }
        $this->db->insert('penerima', $data);
        return (int)$this->db->insert_id();
    }
}

<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Jenis_biaya_model extends CI_Model {
    private static bool $schemaChecked = false;

    public function __construct() {
        parent::__construct();
        $this->ensure_schema();
    }

    private function ensure_schema(): void {
        if (self::$schemaChecked || !$this->db->table_exists('jenis_biaya')) return;
        self::$schemaChecked = true;
        $fields = $this->db->list_fields('jenis_biaya');
        if (!in_array('tampil_di_kegiatan', $fields, true)) {
            $this->db->query("ALTER TABLE jenis_biaya ADD COLUMN tampil_di_kegiatan TINYINT(1) NOT NULL DEFAULT 1 AFTER satuan");
        }
        if (!in_array('keterangan', $fields, true)) {
            $this->db->query("ALTER TABLE jenis_biaya ADD COLUMN keterangan TEXT NULL AFTER tampil_di_kegiatan");
        }
    }

    public function all(bool $onlyVisible = false): array {
        if ($onlyVisible) $this->db->where('tampil_di_kegiatan', 1);
        return $this->db->where('is_active', 1)->order_by('nama')->get('jenis_biaya')->result_array();
    }

    public function count_all(): int {
        return (int)$this->db->count_all('jenis_biaya');
    }

    public function count_filtered(string $search = ''): int {
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('kode', $search);
            $this->db->or_like('nama', $search);
            $this->db->or_like('satuan', $search);
            $this->db->group_end();
        }
        return (int)$this->db->count_all_results('jenis_biaya');
    }

    public function get_datatable(int $start = 0, int $length = 10, string $search = '', string $orderCol = 'nama', string $orderDir = 'ASC'): array {
        if ($search !== '') {
            $this->db->group_start();
            $this->db->like('kode', $search);
            $this->db->or_like('nama', $search);
            $this->db->or_like('satuan', $search);
            $this->db->group_end();
        }
        if (!empty($orderCol)) {
            $this->db->order_by($orderCol, $orderDir);
        } else {
            $this->db->order_by('nama', 'ASC');
        }
        if ($length > 0) {
            $this->db->limit($length, $start);
        }
        return $this->db->get('jenis_biaya')->result_array();
    }

    public function save(array $data, ?int $id = NULL): int {
        if ($id) {
            $this->db->where('id', $id)->update('jenis_biaya', $data);
            return $id;
        }
        $this->db->insert('jenis_biaya', $data);
        return (int)$this->db->insert_id();
    }
}

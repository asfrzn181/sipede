<?php defined('BASEPATH') OR exit('No direct script access allowed');

function rupiah($value): string
{
    return 'Rp ' . number_format((float) $value, 0, ',', '.');
}

/**
 * Konversi input nominal rupiah (bisa berisi titik/koma pemisah ribuan) menjadi float murni.
 */
function clean_number($value): float
{
    if ($value === null || $value === '') return 0.0;
    if (is_numeric($value)) return (float)$value;
    $cleaned = str_replace(array('Rp', 'rp', ' ', '.'), '', (string)$value);
    $cleaned = str_replace(',', '.', $cleaned);
    return (float)$cleaned;
}

/**
 * Konversi nominal angka ke pembilang terbilang Bahasa Indonesia.
 */
function terbilang_indo($n): string
{
    $bilangan = array('', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas');
    $n = floor(abs((float)$n));
    if ($n == 0) return 'Nol Rupiah';
    
    $toKata = function($val) use (&$toKata, $bilangan) {
        if ($val < 12) return $bilangan[$val];
        if ($val < 20) return $toKata($val - 10) . ' Belas';
        if ($val < 100) return $toKata(floor($val / 10)) . ' Puluh' . ($val % 10 ? ' ' . $bilangan[$val % 10] : '');
        if ($val < 200) return 'Seratus' . ($val - 100 ? ' ' . $toKata($val - 100) : '');
        if ($val < 1000) return $toKata(floor($val / 100)) . ' Ratus' . ($val % 100 ? ' ' . $toKata($val % 100) : '');
        if ($val < 2000) return 'Seribu' . ($val - 1000 ? ' ' . $toKata($val - 1000) : '');
        if ($val < 1000000) return $toKata(floor($val / 1000)) . ' Ribu' . ($val % 1000 ? ' ' . $toKata($val % 1000) : '');
        if ($val < 1000000000) return $toKata(floor($val / 1000000)) . ' Juta' . ($val % 1000000 ? ' ' . $toKata($val % 1000000) : '');
        if ($val < 1000000000000) return $toKata(floor($val / 1000000000)) . ' Miliar' . ($val % 1000000000 ? ' ' . $toKata($val % 1000000000) : '');
        if ($val < 1000000000000000) return $toKata(floor($val / 1000000000000)) . ' Triliun' . ($val % 1000000000000 ? ' ' . $toKata($val % 1000000000000) : '');
        return '';
    };
    
    $res = preg_replace('/\s+/', ' ', trim($toKata($n)));
    return $res ? $res . ' Rupiah' : '';
}

function tgl_indo(?string $date): string
{
    if (!$date || $date === '0000-00-00') return '-';
    $time = strtotime($date);
    return $time ? date('d-m-Y', $time) : '-';
}

function json_response(bool $status, string $message, array $data = array(), array $errors = array(), int $code = 200): void
{
    $CI =& get_instance();
    $payload = array('status' => $status, 'message' => $message, 'data' => $data, 'errors' => $errors);
    $payload['csrf_hash'] = $CI->security->get_csrf_hash();
    $CI->output->set_status_header($code)->set_content_type('application/json')->set_header('X-CSRF-TOKEN: '.$payload['csrf_hash'])->_display(json_encode($payload));
    exit;
}

function can(string $kode): bool
{
    $CI =& get_instance();
    return isset($CI->rbac) && $CI->rbac->can($kode);
}

function input_json(): array
{
    $raw = file_get_contents('php://input');
    $json = json_decode($raw ?: '', TRUE);
    $CI =& get_instance();
    return is_array($json) ? $json : (array) $CI->input->post(NULL, TRUE);
}

/**
 * Enkripsi primary key integer menjadi token URL-safe acak untuk keamanan (anti ID-enumeration).
 */
function encrypt_id($id): string
{
    if ($id === null || $id === '' || !is_numeric($id)) return '';
    $id = (int)$id;
    $rawKey = function_exists('config_item') ? config_item('encryption_key') : getenv('ENCRYPTION_KEY');
    $key = hash('sha256', $rawKey ?: 'sipede-secret-key-2026', true);
    $iv = openssl_random_pseudo_bytes(8);
    $encrypted = openssl_encrypt((string)$id, 'AES-128-CTR', substr($key, 0, 16), OPENSSL_RAW_DATA, $iv . $iv);
    return rtrim(strtr(base64_encode($iv . $encrypted), '+/', '-_'), '=');
}

/**
 * Dekripsi token kembali menjadi ID integer database.
 * Mendukung graceful fallback bila raw integer diterima.
 */
function decrypt_id($token): ?int
{
    if ($token === null || $token === '') return null;
    $tokenStr = (string)$token;
    // Cek apakah token merupakan string base64url terenkripsi
    $raw = base64_decode(strtr($tokenStr, '-_', '+/'), true);
    if ($raw !== false && strlen($raw) > 8) {
        $iv = substr($raw, 0, 8);
        $cipher = substr($raw, 8);
        $rawKey = function_exists('config_item') ? config_item('encryption_key') : getenv('ENCRYPTION_KEY');
        $key = hash('sha256', $rawKey ?: 'sipede-secret-key-2026', true);
        $decrypted = openssl_decrypt($cipher, 'AES-128-CTR', substr($key, 0, 16), OPENSSL_RAW_DATA, $iv . $iv);
        if ($decrypted !== false && is_numeric($decrypted)) {
            return (int)$decrypted;
        }
    }
    // Fallback: bila token murni angka integer
    return is_numeric($token) ? (int)$token : null;
}


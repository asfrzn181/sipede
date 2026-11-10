<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * MY_Security - Extended CSRF Protection
 *
 * Mendukung verifikasi CSRF untuk request JSON, header X-CSRF-TOKEN / X-XSRF-TOKEN,
 * dan DataTables Server-Side Processing.
 */
class MY_Security extends CI_Security {

    public function csrf_verify() {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
            // Ambil token dari POST jika belum ada
            if (empty($_POST[$this->_csrf_token_name])) {
                // 1. Cek dari HTTP Header
                $headerToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_SERVER['HTTP_X_XSRF_TOKEN'] ?? null;
                if ($headerToken) {
                    $_POST[$this->_csrf_token_name] = $headerToken;
                } else {
                    // 2. Cek dari JSON Payload
                    $contentType = $_SERVER['CONTENT_TYPE'] ?? $_SERVER['HTTP_CONTENT_TYPE'] ?? '';
                    if (stripos($contentType, 'application/json') !== false) {
                        $raw = file_get_contents('php://input');
                        if ($raw) {
                            $json = json_decode($raw, true);
                            if (is_array($json) && !empty($json[$this->_csrf_token_name])) {
                                $_POST[$this->_csrf_token_name] = $json[$this->_csrf_token_name];
                            }
                        }
                    }
                }
            }

            // Jika cookie CSRF belum diset di client atau hilang tapi token dikirim, sinkronkan cookie
            if (!empty($_POST[$this->_csrf_token_name]) && empty($_COOKIE[$this->_csrf_cookie_name])) {
                $_COOKIE[$this->_csrf_cookie_name] = $_POST[$this->_csrf_token_name];
            }
        }

        return parent::csrf_verify();
    }

    public function csrf_show_error() {
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (stripos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false)
            || (stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false);

        if ($isAjax) {
            set_status_header(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(array(
                'status' => false,
                'message' => 'Sesi CSRF telah diperbarui. Silakan coba kembali.',
                'csrf_hash' => $this->get_csrf_hash()
            ));
            exit(5);
        }

        parent::csrf_show_error();
    }
}

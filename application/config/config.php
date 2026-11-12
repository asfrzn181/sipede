<?php defined('BASEPATH') OR exit('No direct script access allowed');
// Base URL dibentuk otomatis dari request yang sedang berjalan.
// Dengan demikian aplikasi tetap bekerja saat dipindahkan dari localhost,
// subfolder, domain utama, subdomain, atau HTTPS tanpa mengubah .env.
$forwardedProto = trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? ''));
$scheme = $forwardedProto !== ''
    ? strtolower(trim(explode(',', $forwardedProto)[0]))
    : ((!empty($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off') ? 'https' : 'http');
$scheme = in_array($scheme, array('http', 'https'), true) ? $scheme : 'http';

$directHost = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
$forwardedHost = trim((string)($_SERVER['HTTP_X_FORWARDED_HOST'] ?? ''));
// Di cPanel/LiteSpeed, HTTP_HOST adalah host publik yang harus dipakai
// untuk cookie dan redirect. Header forwarded dapat berisi host internal.
$host = $directHost !== ''
    ? $directHost
    : ($forwardedHost !== '' ? trim(explode(',', $forwardedHost)[0]) : trim((string)($_SERVER['SERVER_NAME'] ?? 'localhost')));
$host = preg_replace('/[^a-zA-Z0-9.:\-\[\]]/', '', $host);

$scriptName = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$scriptDir = str_replace('\\', '/', dirname($scriptName));
$basePath = ($scriptDir === '.' || $scriptDir === '/' || $scriptDir === '') ? '/' : rtrim($scriptDir, '/') . '/';

$config['base_url'] = $scheme . '://' . $host . $basePath;
$config['index_page'] = getenv('APP_INDEX_PAGE') !== false ? (string)getenv('APP_INDEX_PAGE') : 'index.php';
$config['enable_query_strings'] = FALSE;
$config['controller_trigger'] = 'c';
$config['function_trigger'] = 'm';
$config['charset'] = 'UTF-8';
$config['subclass_prefix'] = 'MY_';
$config['enable_hooks'] = FALSE;
$config['uri_protocol'] = 'REQUEST_URI';
$config['permitted_uri_chars'] = 'a-z A-Z 0-9~%.:_\-';
$config['encryption_key'] = getenv('ENCRYPTION_KEY') ?: 'ganti-kunci-enkripsi-anggaran-dprd-2026';
$config['sess_driver'] = 'database';
$config['sess_cookie_name'] = 'sipede_session';
$config['sess_save_path'] = 'ci_sessions';
$config['sess_time_to_update'] = 300;
$config['sess_use_database'] = TRUE;
$config['csrf_protection'] = TRUE;
$config['csrf_token_name'] = 'csrf_token';
$config['csrf_cookie_name'] = 'sipede_csrf_v2';
$config['csrf_expire'] = 7200;
$config['csrf_regenerate'] = FALSE;
$config['cookie_path'] = '/';
$config['cookie_domain'] = '';
$cookieSecureEnv = getenv('APP_COOKIE_SECURE');
$config['cookie_secure'] = $cookieSecureEnv !== false
    ? (string)$cookieSecureEnv === '1'
    : $scheme === 'https';
$config['cookie_samesite'] = 'Lax';
$config['cookie_httponly'] = FALSE;
$config['csrf_exclude_uris'] = array('auth/login');
$config['global_xss_filtering'] = TRUE;
$config['log_threshold'] = (ENVIRONMENT === 'development') ? 1 : 0;
$config['log_path'] = APPPATH . 'logs/';
$config['composer_autoload'] = __DIR__ . '/../../vendor/autoload.php';

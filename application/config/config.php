<?php defined('BASEPATH') OR exit('No direct script access allowed');
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
if (!empty($_SERVER['HTTP_HOST'])) {
    $scriptDir = str_replace(basename($_SERVER['SCRIPT_NAME'] ?? ''), '', $_SERVER['SCRIPT_NAME'] ?? '');
    $config['base_url'] = $protocol . $_SERVER['HTTP_HOST'] . $scriptDir;
} else {
    $config['base_url'] = 'http://localhost/sipede/';
}
$config['index_page'] = '';
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
$config['cookie_secure'] = FALSE;
$config['cookie_httponly'] = FALSE;
$config['csrf_exclude_uris'] = array('auth/login');
$config['global_xss_filtering'] = TRUE;
$config['log_threshold'] = (ENVIRONMENT === 'development') ? 1 : 0;
$config['log_path'] = APPPATH . 'logs/';
$config['composer_autoload'] = __DIR__ . '/../../vendor/autoload.php';

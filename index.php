<?php

// Otomatis memuat konfigurasi dari file .env jika tersedia (untuk kemudahan setup di cPanel)
if (is_file(__DIR__ . '/.env')) {
    $envLines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!isset($_SERVER[$key])) {
                $_SERVER[$key] = $val;
            }
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $val;
            }
            putenv("{$key}={$val}");
        }
    }
}

define('ENVIRONMENT', $_SERVER['CI_ENV'] ?? getenv('CI_ENV') ?: 'development');
if (ENVIRONMENT === 'development') {
    error_reporting(-1);
    ini_set('display_errors', '1');
} else {
    ini_set('display_errors', '0');
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_DEPRECATED);
}

$path = __DIR__ . '/vendor/autoload.php';
if (is_file($path)) {
    require_once $path;
}
$system_path = is_dir(__DIR__ . '/vendor/pocketarc/codeigniter/system')
    ? realpath(__DIR__ . '/vendor/pocketarc/codeigniter/system') : realpath(__DIR__ . '/system');
if (!$system_path) {
    http_response_code(503);
    exit('CodeIgniter system directory ontbreekt. Voer composer install uit.');
}
define('SELF', basename(__FILE__));
define('BASEPATH', rtrim($system_path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
define('SYSDIR', basename(BASEPATH));
define('APPPATH', realpath(__DIR__ . '/application') . DIRECTORY_SEPARATOR);
define('VIEWPATH', APPPATH . 'views' . DIRECTORY_SEPARATOR);
$assign_to_config['composer_autoload'] = is_file($path) ? $path : FALSE;
require_once BASEPATH . 'core/CodeIgniter.php';

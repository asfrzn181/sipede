<?php defined('BASEPATH') OR exit('No direct script access allowed');
$active_group = 'default';
$query_builder = TRUE;
$db['default'] = array(
    'dsn' => '', 
    'hostname' => $_SERVER['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1',
    'username' => $_SERVER['DB_USER'] ?? getenv('DB_USER') ?: 'root', 
    'password' => $_SERVER['DB_PASS'] ?? getenv('DB_PASS') ?: '',
    'database' => $_SERVER['DB_NAME'] ?? getenv('DB_NAME') ?: 'anggaran_dprd', 
    'dbdriver' => 'mysqli',
    'dbprefix' => '', 'pconnect' => FALSE, 'db_debug' => (ENVIRONMENT !== 'production'),
    'cache_on' => FALSE, 'cachedir' => '', 'char_set' => 'utf8mb4', 'dbcollat' => 'utf8mb4_unicode_ci',
    'swap_pre' => '', 'encrypt' => FALSE, 'compress' => FALSE, 'stricton' => FALSE,
    'failover' => array(), 'save_queries' => TRUE
);

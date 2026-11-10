<?php defined('BASEPATH') OR exit('No direct script access allowed');
$config['nama_instansi'] = 'Sekretariat DPRD';
$config['mode_saldo'] = getenv('MODE_SALDO') ?: 'PERINGATAN';
$config['app_roles'] = array('ADMIN', 'OPERATOR', 'PIMPINAN', 'VIEWER');

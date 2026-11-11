<?php defined('BASEPATH') OR exit('No direct script access allowed');
$config['nama_instansi'] = 'Sekretariat DPRD Kabupaten Bangka';
$config['logo_url'] = 'https://bangka.go.id/assets/images.png';
$config['login_background_url'] = 'https://asset.tribunnews.com/jxdiuszX0ocsk6jmEGejvmuDGcM=/1200x675/filters:upscale():quality(30):format(webp):focal(0.5x0.5:0.5x0.5)/bangka/foto/bank/originals/20251231-Kantor-DPRD-Kabupaten-Bangka.jpg';
$config['mode_saldo'] = getenv('MODE_SALDO') ?: 'PERINGATAN';
$config['app_roles'] = array('ADMIN', 'OPERATOR', 'PIMPINAN', 'VIEWER');

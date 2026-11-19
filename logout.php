<?php
/**
 * logout.php — mengakhiri sesi lalu kembali ke login.
 *
 * Sesi dikosongkan DAN id-nya diganti (ct_keluar): hanya mengosongkan
 * isi membuat cookie lama tetap sah dipakai ulang.
 */
require __DIR__ . '/inc/init.php';
ct_keluar();

<?php
/**
 * menu-populer.php — data blok "Menu Rekomendasi" di laci Buka Menu.
 *
 * Padanan /menu-populer DMP. Membaca data/kunjungan.json (diisi
 * ct_catat_kunjungan() setiap kali halaman bertata letak utama dibuka)
 * dan menghitung BANYAK ORANG berbeda per halaman, 30 hari terakhir.
 *
 * KENAPA ORANG, BUKAN JUMLAH KUNJUNGAN
 *   Satu orang yang memuat ulang halaman 200 kali tidak boleh membuat
 *   halaman itu "paling populer" bagi semua orang.
 *
 * coretax.js mencocokkan hasilnya dengan tautan yang ADA di menu
 * pengguna - halaman yang tidak boleh ia buka tidak akan muncul.
 */
require __DIR__ . '/inc/init.php';
header('Content-Type: application/json');
if (ct_pengguna() === null) {
    http_response_code(401);
    exit(json_encode(['ok' => false]));
}

$hari = 30;
$orang = [];
foreach (ct_data_baca('kunjungan') as $tgl => $halaman) {
    foreach ($halaman as $jalur => $siapa) {
        $orang[$jalur] = array_merge($orang[$jalur] ?? [], array_keys($siapa));
    }
}
$menu = [];
$dasar = rtrim(str_replace(chr(92), '/', dirname($_SERVER['SCRIPT_NAME'])), '/') . '/';
foreach ($orang as $jalur => $daftar) {
    $menu[] = ['url' => $dasar . $jalur, 'orang' => count(array_unique($daftar))];
}
usort($menu, static fn ($a, $b) => $b['orang'] <=> $a['orang']);

echo json_encode(['ok' => true, 'hari' => $hari, 'tampil' => 5, 'menu' => array_slice($menu, 0, 15)]);

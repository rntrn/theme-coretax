<?php
/**
 * tema-simpan.php — menyimpan pilihan terang/gelap (dan tema/palet) ke sesi.
 *
 * Dipanggil dmp-theme.js lewat fetch() saat tombol matahari/bulan
 * ditekan (alamatnya dari window.DMP_TEMA di inc/awal.php). Halaman
 * berikutnya membaca nilai ini di ct_atribut_tema(), sehingga mode
 * tidak "berkedip" kembali ke terang saat pindah halaman.
 *
 * Disimpan di SESI, bukan basis data: cukup untuk starter. Untuk
 * mengingat pilihan lintas perangkat, simpan ke tabel pengguna di sini.
 */
require __DIR__ . '/inc/init.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ct_pengguna() === null || ! ct_csrf_cek()) {
    http_response_code(403);
    exit(json_encode(['ok' => false, 'pesan' => 'Ditolak.']));
}

// Hanya nilai yang dikenal: isinya dicetak ke atribut <html>.
$pola = ['tema' => '/^[a-z]{2,12}$/', 'mode' => '/^(light|dark|auto)$/', 'palet' => '/^[a-z]{2,12}$/'];
foreach ($pola as $k => $re) {
    $v = (string) ($_POST[$k] ?? '');
    if (preg_match($re, $v)) {
        $_SESSION['tema'][$k] = $v;
    }
}
echo json_encode(['ok' => true, 'csrf' => ct_csrf()]);

<?php
/**
 * tools/ekspor-html.php — membuat versi HTML STATIS di folder html/.
 *
 * Pakai:   php tools/ekspor-html.php
 * Hasil:   html/dashboard.html, html/login.html, html/admin/index.html, ...
 *
 * KENAPA ADA
 *   1. Pratinjau tanpa PHP (dibuka langsung, atau di GitHub Pages).
 *   2. Untuk aplikasi yang BUKAN PHP (Java, .NET, Node): salin markup
 *      dari berkas .html ini - strukturnya sama persis dengan versi PHP.
 *
 * KENAPA MENJALANKAN HALAMAN ASLINYA, BUKAN MENULIS HTML TERPISAH
 *   Setiap halaman dijalankan sebagai proses PHP tersendiri dengan
 *   CT_EKSPOR=1 (inc/init.php lalu memakai pengguna contoh, tanpa sesi,
 *   dan mengubah tautan .php -> .html). HTML statis yang ditulis tangan
 *   akan menyimpang dari versi PHP pada perubahan pertama.
 *
 * Yang tidak ikut: proses POST (login, simpan pintu), sso.php,
 * tema-simpan.php, menu-populer.php - semuanya butuh server.
 */
declare(strict_types=1);

$akar = dirname(__DIR__);
$halaman = ['dashboard', 'grafik', 'komponen', 'formulir', 'tabel', 'kosong', 'profil', '404',
            'dokumentasi', 'login', 'admin/index', 'admin/pintu-masuk', 'admin/pengaturan'];

putenv('CT_EKSPOR=1');
putenv('REQUEST_METHOD=GET');   // PHP CLI menyalin variabel lingkungan ke $_SERVER
@mkdir($akar . '/html/admin', 0777, true);

$gagal = 0;
foreach ($halaman as $h) {
    $perintah = escapeshellarg(PHP_BINARY) . ' -d display_errors=stderr ' . escapeshellarg($akar . '/' . $h . '.php');
    $proses = proc_open($perintah, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipa, $akar);
    $html = stream_get_contents($pipa[1]);
    $galat = stream_get_contents($pipa[2]);
    $kode = proc_close($proses);

    // Galat PHP sekecil apa pun (Warning) dianggap gagal: HTML yang
    // memuat "Warning: ..." di tengah halaman tidak boleh terkirim diam-diam.
    if ($kode !== 0 || trim($galat) !== '' || stripos($html, '</html>') === false) {
        fwrite(STDERR, "GAGAL  $h\n" . $galat . "\n");
        $gagal++;
        continue;
    }
    file_put_contents($akar . '/html/' . $h . '.html', $html);
    echo 'ok     html/' . $h . '.html (' . number_format(strlen($html) / 1024, 1) . " KB)\n";
}

file_put_contents($akar . '/html/index.html',
    "<!doctype html><meta charset=\"utf-8\"><meta http-equiv=\"refresh\" content=\"0;url=login.html\">"
    . "<a href=\"login.html\">Buka pratinjau</a>\n");

echo $gagal ? "\n$gagal halaman gagal.\n" : "\nSelesai. Buka html/index.html\n";
exit($gagal ? 1 : 0);

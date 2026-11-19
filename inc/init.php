<?php
/**
 * inc/init.php — dimuat PALING AWAL oleh setiap halaman.
 *
 * APA
 *   Membaca config/, menyalakan sesi, dan menyediakan fungsi bantu kecil:
 *   alamat (ct_url, ct_aset), keluaran aman (e), pengguna (ct_pengguna,
 *   ct_wajib_masuk, ct_wajib_peran), CSRF, pesan kilat, dan penyimpanan
 *   berkas JSON di data/.
 *
 * KENAPA FUNGSI BIASA, BUKAN KELAS / FRAMEWORK
 *   Tema ini harus bisa dipakai di aplikasi PHP apa pun - polos,
 *   CodeIgniter, Laravel - cukup dengan menyalin markup-nya. Fungsi
 *   berawalan ct_ mudah dicari dan mudah diganti satu per satu dengan
 *   padanan framework (base_url(), session(), csrf_field(), dst.).
 *   Lihat docs/PANDUAN.md bagian "Memindahkan ke CodeIgniter 4".
 *
 * KENAPA ALAMAT RELATIF ("../aset.css"), BUKAN ABSOLUT ("/folder/aset.css")
 *   Folder tema bisa diletakkan di mana saja (htdocs/x, htdocs/a/b/x,
 *   di balik proxy) tanpa mengubah satu baris pun, dan alat ekspor HTML
 *   statis (tools/ekspor-html.php) bisa memakai keluaran yang sama.
 *   Bila tetap butuh absolut, isi 'base_url' di config/app.php.
 */
declare(strict_types=1);

define('CT_AKAR', str_replace('\\', '/', dirname(__DIR__)));
define('CT_INC',  CT_AKAR . '/inc');
define('CT_DATA', CT_AKAR . '/data');
define('CT_EKSPOR', getenv('CT_EKSPOR') === '1');   // dijalankan tools/ekspor-html.php

$GLOBALS['CT'] = require CT_AKAR . '/config/app.php';
date_default_timezone_set($GLOBALS['CT']['zona'] ?? 'Asia/Jakarta');

/** Ambil satu pengaturan dari config/app.php. */
function ct(string $kunci, $bawaan = null)
{
    return $GLOBALS['CT'][$kunci] ?? $bawaan;
}

/** htmlspecialchars pendek. SELALU dipakai untuk data yang dicetak ke HTML. */
function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// ----------------------------------------------------------------------
// Alamat
// ----------------------------------------------------------------------

/**
 * Awalan dari halaman yang sedang berjalan ke akar folder tema:
 * '' untuk dashboard.php, '../' untuk admin/pintu-masuk.php.
 */
function ct_awalan(): string
{
    static $awalan = null;
    if ($awalan !== null) {
        return $awalan;
    }
    if (ct('base_url') !== '') {
        return $awalan = rtrim((string) ct('base_url'), '/') . '/';
    }
    $skrip = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $relatif = ltrim(substr(dirname($skrip), strlen(CT_AKAR)), '/');
    $awalan = $relatif === '' ? '' : str_repeat('../', substr_count($relatif, '/') + 1);
    return $awalan;
}

/** Alamat halaman di dalam tema. Alamat http(s):// dikembalikan apa adanya. */
function ct_url(string $jalur = ''): string
{
    if (preg_match('#^(https?:)?//#i', $jalur)) {
        return $jalur;
    }
    $url = ct_awalan() . ltrim($jalur, '/');
    if (CT_EKSPOR) {
        // Versi HTML statis ditulis ke html/ (satu tingkat lebih dalam):
        // halaman tetap bertetangga (dashboard.php -> dashboard.html),
        // tetapi aset harus naik satu folder (assets/ -> ../assets/).
        $halaman = $jalur === '' || preg_match('#(\.php|/)([?\#].*)?$#', $jalur);
        if ($halaman) {
            $url = preg_replace('#\.php(?=$|[?\#])#', '.html', $url);
            $url = preg_replace('#(^|/)admin/$#', '$1admin/index.html', $url);
            $url = preg_replace('#\?[^\#]*#', '', $url);   // tanpa server, ?hal=2 tidak berarti apa-apa
            $url = str_replace('logout.html', 'login.html', $url);   // keluar = kembali ke login
        } else {
            $url = '../' . $url;
        }
    }
    return $url === '' ? './' : $url;
}

/**
 * Alamat aset + ?v=<waktu ubah berkas>.
 * Tanpa penanda versi, peramban terus memakai CSS lama dari cache
 * sesudah berkasnya diganti - perubahan "tidak terlihat" padahal sudah ada.
 */
function ct_aset(string $jalur): string
{
    $berkas = CT_AKAR . '/' . ltrim($jalur, '/');
    $v = is_file($berkas) ? filemtime($berkas) : 0;
    // Ekspor statis tanpa ?v=: kalau tidak, setiap ekspor mengubah semua
    // berkas html/ di git walau isinya sama.
    return CT_EKSPOR ? ct_url($jalur) : ct_url($jalur) . '?v=' . $v;
}

/** Jalur halaman sekarang relatif ke akar tema, mis. "admin/pintu-masuk.php". */
function ct_jalur_kini(): string
{
    $skrip = str_replace('\\', '/', (string) realpath($_SERVER['SCRIPT_FILENAME'] ?? ''));
    return ltrim(substr($skrip, strlen(CT_AKAR)), '/');
}

function ct_alihkan(string $jalur): void
{
    header('Location: ' . ct_url($jalur));
    exit;
}

// ----------------------------------------------------------------------
// Penyimpanan JSON di data/
//
// Dipakai untuk saklar pintu masuk dan catatan kunjungan. Bukan basis
// data: cukup untuk pengaturan kecil, dan tidak menuntut aplikasi baru
// langsung punya MySQL/Oracle. Ganti dua fungsi ini bila pindah ke DB.
// ----------------------------------------------------------------------

function ct_data_baca(string $nama, array $bawaan = []): array
{
    $f = CT_DATA . '/' . basename($nama) . '.json';
    if (! is_file($f)) {
        return $bawaan;
    }
    $isi = json_decode((string) file_get_contents($f), true);
    return is_array($isi) ? $isi : $bawaan;
}

function ct_data_tulis(string $nama, array $isi): bool
{
    if (CT_EKSPOR) {
        return true;
    }
    $f = CT_DATA . '/' . basename($nama) . '.json';
    // Tulis ke berkas sementara lalu ganti nama: dua permintaan yang
    // menulis bersamaan tidak akan meninggalkan JSON setengah jadi.
    $tmp = $f . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($tmp, json_encode($isi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX) === false) {
        return false;
    }
    return rename($tmp, $f);
}

// ----------------------------------------------------------------------
// Sesi & pengguna
// ----------------------------------------------------------------------

if (CT_EKSPOR) {
    // Ekspor HTML statis: tidak ada peramban, tidak ada cookie.
    $_SESSION = ['pengguna' => ['id' => 'admin', 'nama' => 'ADMIN CONTOH', 'peran' => 'admin', 'via' => 'mandiri'],
                 'csrf' => 'statis', 'aktif' => time()];
} elseif (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('CTSESI');
    session_set_cookie_params([
        'httponly' => true,      // tidak bisa dibaca JavaScript
        'samesite' => 'Lax',     // tidak ikut terkirim dari formulir situs lain
        'secure'   => ! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}

require CT_INC . '/pintu.php';
require CT_INC . '/auth.php';

/** Pengguna yang sedang masuk, atau null. */
function ct_pengguna(): ?array
{
    return $_SESSION['pengguna'] ?? null;
}

function ct_peran(): string
{
    return (string) (ct_pengguna()['peran'] ?? '');
}

/** Bolehkah pengguna sekarang melihat butir dengan daftar peran ini? */
function ct_boleh(array $peran): bool
{
    return $peran === [] || in_array(ct_peran(), $peran, true);
}

/**
 * Penjaga halaman. Dipanggil di baris kedua setiap halaman yang perlu login.
 *
 * Selain "sudah login atau belum", juga memeriksa dua hal yang sering
 * terlupa: batas waktu tanpa aktivitas, dan pintu masuk yang DITUTUP
 * admin sesudah orang itu masuk (lihat CtPintu::putusanSesi()).
 */
function ct_wajib_masuk(): void
{
    if (CT_EKSPOR) {
        return;
    }
    $p = ct_pengguna();
    if ($p === null) {
        ct_alihkan('login.php');
    }

    $batas = (int) ct('batas_menit', 120) * 60;
    if (isset($_SESSION['aktif']) && time() - (int) $_SESSION['aktif'] > $batas) {
        ct_keluar('Sesi berakhir karena tidak ada aktivitas selama ' . (int) ct('batas_menit') . ' menit.');
    }
    $_SESSION['aktif'] = time();

    $putusan = CtPintu::putusanSesi($p, CtPintu::status());
    if ($putusan !== null) {
        ct_keluar($putusan);
    }
}

/** Penjaga peran. Menyembunyikan tautan di menu BUKAN pengaman - ini pengamannya. */
function ct_wajib_peran(string ...$peran): void
{
    ct_wajib_masuk();
    if (! in_array(ct_peran(), $peran, true)) {
        http_response_code(403);
        $GLOBALS['ct_403'] = true;
        require CT_AKAR . '/404.php';
        exit;
    }
}

function ct_keluar(string $pesan = ''): void
{
    $_SESSION = [];
    if (! CT_EKSPOR) {
        session_regenerate_id(true);
    }
    if ($pesan !== '') {
        ct_kilat('gagal', $pesan);
    }
    ct_alihkan('login.php');
}

// ----------------------------------------------------------------------
// CSRF - setiap formulir POST wajib membawa token ini.
// Satu token per sesi (bukan per permintaan) supaya tombol tema yang
// menyimpan lewat fetch() tidak ditolak pada klik kedua.
// ----------------------------------------------------------------------

function ct_csrf(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function ct_csrf_field(): string
{
    return '<input type="hidden" name="_ct" value="' . e(ct_csrf()) . '">';
}

function ct_csrf_cek(): bool
{
    return is_string($_POST['_ct'] ?? null) && hash_equals(ct_csrf(), $_POST['_ct']);
}

// ----------------------------------------------------------------------
// Pesan kilat - tampil SEKALI di halaman berikutnya (pola Post/Redirect/Get)
// ----------------------------------------------------------------------

/** @param string $jenis sukses | gagal | info */
function ct_kilat(string $jenis, string $pesan): void
{
    $_SESSION['kilat'][] = [$jenis, $pesan];
}

function ct_kilat_ambil(): array
{
    $isi = $_SESSION['kilat'] ?? [];
    unset($_SESSION['kilat']);
    return $isi;
}

// ----------------------------------------------------------------------
// Tema per pengguna (tombol matahari/bulan di header)
// ----------------------------------------------------------------------

/** Atribut <html>: data-dmp-theme, data-dmp-mode, data-dmp-palette. */
function ct_atribut_tema(): string
{
    $t = $_SESSION['tema'] ?? [];
    $tema  = $t['tema']  ?? ct('tema', 'biru');
    $mode  = $t['mode']  ?? ct('mode', 'light');
    $palet = $t['palet'] ?? ct('palet', 'laut');
    return sprintf('data-dmp-theme="%s" data-dmp-mode="%s" data-dmp-palette="%s"', e($tema), e($mode), e($palet));
}

// ----------------------------------------------------------------------
// Catatan kunjungan -> "Menu Rekomendasi" di laci Buka Menu
// (padanan /menu-populer di DMP). Disimpan per hari, 30 hari.
// ----------------------------------------------------------------------

function ct_catat_kunjungan(): void
{
    $p = ct_pengguna();
    if ($p === null || CT_EKSPOR) {
        return;
    }
    $hari = date('Y-m-d');
    $log = ct_data_baca('kunjungan');
    $log[$hari][ct_jalur_kini()][$p['id']] = 1;
    // Buang hari yang lebih tua dari 30 hari agar berkasnya tidak membengkak.
    $batas = date('Y-m-d', strtotime('-30 days'));
    foreach (array_keys($log) as $h) {
        if ($h < $batas) {
            unset($log[$h]);
        }
    }
    ct_data_tulis('kunjungan', $log);
}

<?php
/**
 * 404.php — halaman tidak ditemukan, sekaligus 403 (tidak berhak).
 *
 * Dipakai ct_wajib_peran() dengan $GLOBALS['ct_403'] = true. Untuk 404
 * sungguhan di Apache, tambahkan ke .htaccess:  ErrorDocument 404 /folder/404.php
 */
if (! defined('CT_AKAR')) {
    require __DIR__ . '/inc/init.php';
}
$terlarang = ! empty($GLOBALS['ct_403']);
if (ct_pengguna() === null) {   // belum login: tampilkan versi polos tanpa menu
    http_response_code(404);
    exit('Halaman tidak ditemukan.');
}
if (! $terlarang && basename($_SERVER['SCRIPT_NAME']) !== '404.php') {
    http_response_code(404);
}
$judul = $terlarang ? 'Tidak Berhak' : 'Halaman Tidak Ditemukan';
$remah = [['Halaman Contoh'], [$judul]];
require CT_INC . '/awal.php';
?>
<div class="card"><div class="card-body ct-galat">
    <div class="ct-galat__kode"><?= $terlarang ? '403' : '404' ?></div>
    <h2 class="mt-3"><?= e($judul) ?></h2>
    <p><?= $terlarang ? 'Halaman ini hanya untuk peran tertentu. Hubungi admin bila Anda memerlukannya.'
                      : 'Alamat yang dibuka tidak ada atau sudah dipindah. Gunakan Ctrl+K untuk mencari menu.' ?></p>
    <a href="<?= e(ct_url('dashboard.php')) ?>" class="dmp-btn dmp-btn--utama"><i class="ik ik-home"></i> Ke halaman awal</a>
</div></div>
<?php require CT_INC . '/akhir.php'; ?>

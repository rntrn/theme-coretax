<?php
/**
 * dokumentasi.php — menampilkan docs/PANDUAN.md di dalam aplikasi.
 *
 * Satu sumber: berkas Markdown yang sama dibaca di GitHub dan di sini.
 * Mengubah panduan = mengubah docs/PANDUAN.md saja.
 */
require __DIR__ . '/inc/init.php';
require CT_INC . '/markdown.php';
ct_wajib_masuk();

[$html, $daftar] = ct_markdown((string) file_get_contents(CT_AKAR . '/docs/PANDUAN.md'));

$judul    = 'Dokumentasi';
$subjudul = 'Panduan memakai tema ini untuk aplikasi baru (sumber: docs/PANDUAN.md).';
$remah    = [['Alat Bantu'], ['Dokumentasi']];
require CT_INC . '/awal.php';
?>
<div class="row">
    <div class="col-lg-3 order-lg-2">
        <div class="card" style="position:sticky;top:130px">
            <div class="card-header"><h3>Daftar Isi</h3></div>
            <div class="card-body small" style="max-height:60vh;overflow:auto">
                <?php foreach ($daftar as [$tk, $id, $t]) : ?>
                    <div style="padding-left:<?= ($tk - 2) * 14 ?>px;margin-bottom:4px"><a href="#<?= e($id) ?>"><?= e(strip_tags($t)) ?></a></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-lg-9 order-lg-1">
        <div class="card"><div class="card-body ct-dok"><?= $html ?></div></div>
    </div>
</div>
<?php require CT_INC . '/akhir.php'; ?>

<?php
/**
 * kosong.php — HALAMAN AWAL untuk menu baru. Salin berkas ini, ganti
 * nama, isi bagian "ISI HALAMAN", lalu daftarkan di config/menu.php.
 */
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();
// ct_wajib_peran('admin');   // <- pakai ini bila halaman hanya untuk peran tertentu

$judul    = 'Halaman Kosong';
$subjudul = 'Titik awal halaman baru.';
$remah    = [['Halaman Contoh'], ['Halaman Kosong']];
// $pakai_grafik = true;     // <- nyalakan bila halaman memakai ECharts / DmpChart
require CT_INC . '/awal.php';
?>
<div class="card">
    <div class="card-header"><h3>Judul Kartu</h3></div>
    <div class="card-body">
        <!-- ========== ISI HALAMAN ========== -->
        Mulai dari sini. Salin <code>kosong.php</code> menjadi halaman baru, lalu tambahkan satu baris di
        <code>config/menu.php</code>.
        <?php if (($_GET['contoh'] ?? '') === 'tunggal') : ?>
            <p class="mt-3 mb-0">Halaman ini dibuka dari <b>menu tunggal</b> (butir tanpa submenu) - lihat butir
            &ldquo;Tautan Tunggal&rdquo; di <code>config/menu.php</code>.</p>
        <?php endif; ?>
    </div>
</div>
<?php ob_start(); ?>
<script>
// Skrip khusus halaman. jQuery tersedia; skrip tema (coretax.js) berjalan sesudahnya (defer).
</script>
<?php $skrip = ob_get_clean(); require CT_INC . '/akhir.php'; ?>

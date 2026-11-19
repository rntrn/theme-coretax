<?php
/**
 * inc/akhir.php — BAGIAN BAWAH tata letak utama: footer + seluruh skrip.
 *
 * URUTAN SKRIP
 *   1. jQuery + Bootstrap (dropdown header butuh keduanya)
 *   2. ECharts + DmpChart (hanya bila $pakai_grafik = true - 1 MB lebih,
 *      jangan dibebankan ke halaman yang tidak punya grafik)
 *   3. coretax-bahasa.js -> coretax.js -> dmp-theme.js (defer, urutan dijaga peramban)
 *   4. ct-app.js (layar penuh, cetak, pengurut tabel)
 *   5. $skrip - skrip khusus halaman, dijalankan paling akhir
 *
 * KENAPA coretax-bahasa.js SEBELUM coretax.js
 *   Bahasa menerjemahkan teks menu lebih dulu, baru coretax.js menyalin
 *   menu itu ke panel/laci/palet. Terbalik = panel tetap berbahasa
 *   Indonesia walau pengguna memilih English.
 */
?>
</div></div><?php // .container-fluid .main-content ?>

<footer class="footer dmp-footer">
    <div class="w-100 clearfix dmp-footer__baris">
        <span class="dmp-footer__kiri">&copy;<?= date('Y') ?> <?= e(ct('pemilik')) ?>. Hak cipta dilindungi.</span>
        <span class="dmp-footer__versi" title="Versi aplikasi"><span class="dmp-footer__titik" aria-hidden="true"></span><?= e(ct('versi')) ?></span>
        <span class="dmp-footer__kanan"><?= e(ct('subjudul')) ?></span>
    </div>
</footer>
</div><?php // .page-wrap ?>
</div><?php // .wrapper ?>

<script src="<?= e(ct_aset('assets/vendor/jquery/jquery.min.js')) ?>"></script>
<script src="<?= e(ct_aset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')) ?>"></script>
<?php if (! empty($pakai_grafik)) : ?>
<script src="<?= e(ct_aset('assets/vendor/echarts/echarts.min.js')) ?>"></script>
<script src="<?= e(ct_aset('assets/ct/dmp-echart.js')) ?>"></script>
<?php endif; ?>
<script src="<?= e(ct_aset('assets/ct/coretax-bahasa.js')) ?>" defer></script>
<script src="<?= e(ct_aset('assets/ct/coretax.js')) ?>" defer></script>
<script src="<?= e(ct_aset('assets/ct/dmp-theme.js')) ?>" defer></script>
<script src="<?= e(ct_aset('assets/ct/dmp-urut.js')) ?>" defer></script>
<script src="<?= e(ct_aset('assets/ct/ct-app.js')) ?>" defer></script>
<?= $skrip ?? '' ?>
</body>
</html>

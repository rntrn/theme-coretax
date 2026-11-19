<?php
/**
 * inc/admin-akhir.php — penutup tata letak admin. Tanpa jQuery/Bootstrap JS:
 * layar admin cukup dengan formulir biasa + dmp-urut.js (urut tabel).
 * Skrip khusus halaman: isi $skrip sebelum require, sama seperti akhir.php.
 */
?>
        <footer class="dmp-adm__versi"><?= e(ct('singkat') . ' ' . ct('versi')) ?></footer>
    </main>
</div>
<script src="<?= e(ct_aset('assets/ct/dmp-urut.js')) ?>" defer></script>
<script src="<?= e(ct_aset('assets/ct/ct-app.js')) ?>" defer></script>
<?= $skrip ?? '' ?>
</body>
</html>

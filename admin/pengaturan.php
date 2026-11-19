<?php
/**
 * admin/pengaturan.php — pratinjau tema warna & palet grafik.
 *
 * KENAPA HANYA PRATINJAU (tidak menyimpan bawaan organisasi)
 *   Bawaan untuk semua pengguna ada di config/app.php ('tema', 'mode',
 *   'palet'). Menulis ke berkas konfigurasi dari peramban berarti
 *   server web harus boleh menulis ke folder config/ - celah yang tidak
 *   sepadan untuk starter. Tombol di sini menyimpan pilihan untuk SESI
 *   admin sendiri (tema-simpan.php), cukup untuk mencoba.
 */
require __DIR__ . '/../inc/init.php';
ct_wajib_peran('admin');

$tema  = ['biru', 'hijau', 'teal', 'nila', 'ungu', 'anggrek', 'oranye', 'bata'];
$palet = ['laut', 'bumi', 'hutan', 'senja', 'cerah', 'lembut', 'permen', 'kontras', 'aman', 'abu', 'tunggal'];
$kini  = $_SESSION['tema'] ?? [];

$judul    = 'Pengaturan Tema';
$subjudul = 'Coba tema warna dan palet grafik. Bawaan seluruh pengguna diatur di config/app.php.';
require CT_INC . '/admin-awal.php';
?>
<div class="dmp-card dmp-mb-4">
    <div class="dmp-card__head"><h3 class="dmp-card__title">Tema warna</h3>
        <p class="dmp-card__sub">Atribut <code>data-dmp-theme</code> pada &lt;html&gt;. Klik untuk mencoba.</p></div>
    <div class="dmp-card__body dmp-flex dmp-flex-wrap dmp-gap-3">
        <?php foreach ($tema as $t) : ?>
            <button type="button" class="dmp-btn <?= ($kini['tema'] ?? ct('tema')) === $t ? 'dmp-btn--utama' : 'dmp-btn--garis' ?>" data-coba="tema" data-nilai="<?= e($t) ?>"><?= e(ucfirst($t)) ?></button>
        <?php endforeach; ?>
    </div>
</div>
<div class="dmp-card dmp-mb-4">
    <div class="dmp-card__head"><h3 class="dmp-card__title">Mode</h3></div>
    <div class="dmp-card__body dmp-flex dmp-gap-3">
        <?php foreach (['light' => 'Terang', 'dark' => 'Gelap', 'auto' => 'Ikut sistem'] as $m => $n) : ?>
            <button type="button" class="dmp-btn <?= ($kini['mode'] ?? ct('mode')) === $m ? 'dmp-btn--utama' : 'dmp-btn--garis' ?>" data-coba="mode" data-nilai="<?= e($m) ?>"><?= e($n) ?></button>
        <?php endforeach; ?>
    </div>
</div>
<div class="dmp-card dmp-mb-4">
    <div class="dmp-card__head"><h3 class="dmp-card__title">Palet grafik</h3>
        <p class="dmp-card__sub">Warna seri pada grafik DmpChart (<code>data-dmp-palette</code>).</p></div>
    <div class="dmp-card__body dmp-flex dmp-flex-wrap dmp-gap-3">
        <?php foreach ($palet as $p) : ?>
            <button type="button" class="dmp-btn <?= ($kini['palet'] ?? ct('palet')) === $p ? 'dmp-btn--utama' : 'dmp-btn--garis' ?>" data-coba="palet" data-nilai="<?= e($p) ?>"><?= e(ucfirst($p)) ?></button>
        <?php endforeach; ?>
    </div>
</div>
<div class="dmp-card dmp-mb-4">
    <div class="dmp-card__head"><h3 class="dmp-card__title">Jadikan bawaan</h3></div>
    <div class="dmp-card__body"><pre class="ct-kode" id="kodeBawaan"></pre>
        <p class="dmp-help" style="margin:0">Salin ke <code>config/app.php</code> supaya berlaku untuk semua pengguna.</p></div>
</div>
<?php ob_start(); ?>
<script>
(function () {
    var html = document.documentElement;
    var csrf = <?= json_encode(ct_csrf()) ?>, url = <?= json_encode(ct_url('tema-simpan.php')) ?>;
    function tulisKode() {
        document.getElementById('kodeBawaan').textContent =
            "'tema'  => '" + html.getAttribute('data-dmp-theme') + "',\n" +
            "'mode'  => '" + (sessionStorage.getItem('ct_mode_pilih') || html.getAttribute('data-dmp-mode')) + "',\n" +
            "'palet' => '" + html.getAttribute('data-dmp-palette') + "',";
    }
    Array.prototype.forEach.call(document.querySelectorAll('[data-coba]'), function (b) {
        b.addEventListener('click', function () {
            var k = b.getAttribute('data-coba'), v = b.getAttribute('data-nilai');
            var nyata = v === 'auto' ? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light') : v;
            html.setAttribute(k === 'tema' ? 'data-dmp-theme' : k === 'mode' ? 'data-dmp-mode' : 'data-dmp-palette', nyata);
            if (k === 'mode') { try { sessionStorage.setItem('ct_mode_pilih', v); } catch (e) {} }
            Array.prototype.forEach.call(document.querySelectorAll('[data-coba="' + k + '"]'), function (x) {
                x.className = 'dmp-btn ' + (x === b ? 'dmp-btn--utama' : 'dmp-btn--garis');
            });
            var d = new FormData(); d.append(k, v); d.append('_ct', csrf);
            fetch(url, { method: 'POST', body: d, credentials: 'same-origin' });
            tulisKode();
        });
    });
    tulisKode();
})();
</script>
<?php $skrip = ob_get_clean(); require CT_INC . '/admin-akhir.php'; ?>

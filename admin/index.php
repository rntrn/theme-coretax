<?php
/**
 * admin/index.php — beranda Administrator: ringkasan + pintasan.
 */
require __DIR__ . '/../inc/init.php';
ct_wajib_peran('admin');

$status = CtPintu::status();
$jejak  = CtPintu::jejak();
$judul    = 'Beranda Admin';
$subjudul = 'Ringkasan pengaturan yang berlaku untuk seluruh pengguna.';
require CT_INC . '/admin-awal.php';
?>
<div class="dmp-kpi-grid dmp-mb-4">
    <div class="dmp-kpi dmp-kpi--aksen"><span class="dmp-kpi__label">Login mandiri</span>
        <span class="dmp-kpi__value"><?= $status['mandiri'] ? 'Dibuka' : 'Ditutup' ?></span></div>
    <div class="dmp-kpi"><span class="dmp-kpi__label">Masuk lewat <?= e(ct('sso_nama')) ?></span>
        <span class="dmp-kpi__value"><?= $status['sso'] ? 'Dibuka' : 'Ditutup' ?></span></div>
    <div class="dmp-kpi"><span class="dmp-kpi__label">Login <?= (int) ct('hari_bukti') ?> hari terakhir</span>
        <span class="dmp-kpi__value"><?= (int) ($jejak['mandiri']['hari'] + $jejak['sso']['hari']) ?></span></div>
    <div class="dmp-kpi"><span class="dmp-kpi__label">Mode demo</span>
        <span class="dmp-kpi__value"><?= ct('demo') ? 'NYALA' : 'Mati' ?></span></div>
</div>

<div class="dmp-card dmp-mb-4">
    <div class="dmp-card__head"><h3 class="dmp-card__title">Pintasan</h3>
        <p class="dmp-card__sub">Menu admin dibaca dari config/menu-admin.php.</p></div>
    <div class="dmp-card__body dmp-card__body--flush">
        <table class="dmp-table">
            <thead><tr><th>Menu</th><th>Kegunaan</th></tr></thead>
            <tbody>
            <tr><td><a href="<?= e(ct_url('admin/pintu-masuk.php')) ?>"><i class="ik ik-log-in"></i> Pintu Masuk</a></td>
                <td>Buka/tutup login mandiri dan masuk lewat <?= e(ct('sso_nama')) ?>, dengan tiga pengaman.</td></tr>
            <tr><td><a href="<?= e(ct_url('admin/pengaturan.php')) ?>"><i class="ik ik-sliders"></i> Pengaturan Tema</a></td>
                <td>Pratinjau delapan tema warna dan palet grafik; bawaan diatur di config/app.php.</td></tr>
            </tbody>
        </table>
    </div>
</div>
<?php if (ct('demo')) : ?>
<div class="dmp-adm__ingat" role="alert"><strong>Mode demo menyala.</strong> Akun contoh di <code>config/app.php</code> bisa dipakai siapa saja.
    Set <code>'demo' =&gt; false</code> dan sambungkan login asli di <code>inc/auth.php</code> sebelum dipakai sungguhan.</div>
<?php endif; ?>
<?php require CT_INC . '/admin-akhir.php'; ?>

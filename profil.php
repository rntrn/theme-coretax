<?php
/**
 * profil.php — contoh halaman profil pengguna: data sesi + cara masuk.
 */
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();
$p = ct_pengguna();

$judul = 'Profil Pengguna';
$remah = [['Halaman Contoh'], ['Profil Pengguna']];
require CT_INC . '/awal.php';
?>
<div class="row">
    <div class="col-lg-4">
        <div class="card"><div class="card-body text-center">
            <span class="dmp-nav__avatar" style="width:72px;height:72px;font-size:28px;margin:8px auto 12px;display:flex"><?= e(mb_substr($p['nama'], 0, 1)) ?></span>
            <h4 class="mb-1"><?= e($p['nama']) ?></h4>
            <div class="text-muted small mb-3"><?= e($p['id']) ?></div>
            <span class="dmp-badge dmp-badge--sukses"><?= e(ct('peran')[$p['peran']] ?? $p['peran']) ?></span>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-header"><h3>Rincian Sesi</h3></div><div class="card-body p-0">
            <table class="table mb-0" data-urut="tidak">
                <tbody>
                <tr><th style="width:200px">Nama pengguna</th><td><?= e($p['id']) ?></td></tr>
                <tr><th>Peran</th><td><?= e($p['peran']) ?></td></tr>
                <tr><th>Masuk lewat</th><td><?= ($p['via'] ?? '') === 'sso' ? e(ct('sso_nama')) . ' (SSO)' : 'Halaman login (mandiri)' ?></td></tr>
                <tr><th>Batas tanpa aktivitas</th><td><?= (int) ct('batas_menit') ?> menit</td></tr>
                </tbody>
            </table>
        </div></div>
    </div>
</div>
<?php require CT_INC . '/akhir.php'; ?>

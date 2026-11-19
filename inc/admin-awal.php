<?php
/**
 * inc/admin-awal.php — BAGIAN ATAS tata letak ADMIN (sidebar kiri tetap).
 * Pasangannya inc/admin-akhir.php. Pola pakai:
 *
 *   require __DIR__ . '/../inc/init.php';
 *   ct_wajib_peran('admin');
 *   $judul = 'Pintu Masuk'; $subjudul = '...';
 *   require CT_INC . '/admin-awal.php';
 *   ... isi (kartu dmp-card) ...
 *   require CT_INC . '/admin-akhir.php';
 *
 * KENAPA TATA LETAK KEDUA (meniru tata letak admin DMP)
 *   Layar admin berisi tindakan yang berakibat ke SEMUA pengguna.
 *   Tampilan yang jelas berbeda (sidebar kiri, tanpa baris menu
 *   dashboard) membuat orang sadar sedang di area itu.
 *
 * Menu dari config/menu-admin.php. Butir aktif = alamat yang cocok
 * PALING PANJANG, supaya "admin/" tidak ikut menyala di setiap halaman.
 */
$ctMenuAdmin = require CT_AKAR . '/config/menu-admin.php';
$kini = ct_jalur_kini();
$juara = null; $panjang = -1;
foreach ($ctMenuAdmin as $i => $m) {
    foreach (array_merge([$m['url']], $m['juga'] ?? []) as $u) {
        $u2 = preg_replace('#/$#', '/index.php', $u);
        if (($kini === $u2 || str_starts_with($kini, rtrim($u, '/') . '/') && $u !== 'admin/') && strlen($u2) > $panjang) {
            $juara = $i; $panjang = strlen($u2);
        }
    }
}
$p = ct_pengguna();
$tanpa_themekit = true;
?>
<!doctype html>
<html class="ct" lang="id" <?= ct_atribut_tema() ?>>
<?php require CT_INC . '/kepala.php'; ?>
<body class="dmp-body dmp-scope">
<?php if (ct('demo')) : ?><div class="ct-pita-demo">MODE DEMO</div><?php endif; ?>
<div class="dmp-adm">
    <aside class="dmp-adm__nav">
        <div class="dmp-adm__merek">
            <span class="dmp-adm__logo dmp-adm__logo--gambar"><img src="<?= e(ct_aset(ct('logo'))) ?>" alt="" width="32" height="32"></span>
            <div><b>Administrator</b><small><?= e(ct('subjudul')) ?></small></div>
        </div>
        <nav class="dmp-adm__menu">
            <?php foreach ($ctMenuAdmin as $i => $m) : ?>
                <a href="<?= e(ct_url($m['url'])) ?>" class="dmp-adm__butir<?= $i === $juara ? ' aktif' : '' ?>">
                    <i class="ik <?= e($m['ikon']) ?>"></i><span><?= e($m['teks']) ?></span>
                </a>
            <?php endforeach; ?>
        </nav>
        <div class="dmp-adm__kaki">
            <a href="<?= e(ct_url('dashboard.php')) ?>" class="dmp-adm__kembali"><i class="ik ik-arrow-left"></i><span>Kembali ke Dashboard</span></a>
            <div class="dmp-adm__user">
                <span class="dmp-nav__avatar"><?= e(mb_substr($p['nama'], 0, 1)) ?></span>
                <span class="dmp-adm__user-teks"><b><?= e($p['nama']) ?></b><small><?= e(ct('peran')[$p['peran']] ?? $p['peran']) ?></small></span>
            </div>
        </div>
    </aside>
    <main class="dmp-adm__isi">
        <header class="dmp-adm__kepala">
            <h1><?= e($judul ?? 'Administrator') ?></h1>
            <?php if (! empty($subjudul)) : ?><p><?= e($subjudul) ?></p><?php endif; ?>
        </header>
        <?php foreach (ct_kilat_ambil() as [$jenis, $pesan]) : ?>
            <div class="dmp-adm__pesan dmp-adm__pesan--<?= $jenis === 'gagal' ? 'gagal' : 'sukses' ?>" role="<?= $jenis === 'gagal' ? 'alert' : 'status' ?>">
                <i class="ik <?= $jenis === 'gagal' ? 'ik-alert-circle' : 'ik-check-circle' ?>"></i><span><?= e($pesan) ?></span>
            </div>
        <?php endforeach; ?>

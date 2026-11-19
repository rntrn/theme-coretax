<?php
/**
 * inc/awal.php — BAGIAN ATAS tata letak utama (header + menu + pembuka isi).
 * Pasangannya inc/akhir.php. Pola pakai di setiap halaman:
 *
 *   <?php
 *   require __DIR__ . '/inc/init.php';
 *   ct_wajib_masuk();
 *   $judul    = 'Nama Halaman';
 *   $subjudul = 'Kalimat penjelas (opsional)';
 *   $remah    = [['Dashboard', 'dashboard.php'], ['Nama Halaman']];
 *   require CT_INC . '/awal.php';
 *   ?>
 *       ... isi halaman (kartu, tabel, grafik) ...
 *   <?php ob_start(); ?>
 *   <script> ... skrip khusus halaman, jQuery & DmpChart sudah tersedia ... </script>
 *   <?php $skrip = ob_get_clean(); require CT_INC . '/akhir.php'; ?>
 *
 * STRUKTUR (sama dengan DMP, supaya coretax.css berlaku apa adanya):
 *   html.ct > body > #ctMuat (layar memuat)
 *     .wrapper > header.header-top.ct-kepala
 *              > .page-wrap > aside#dmpSidebar + .main-content > .container-fluid
 *                           > footer.dmp-footer   (di akhir.php)
 */
$p = ct_pengguna() ?? ['nama' => 'TAMU', 'id' => '-', 'peran' => ''];
ct_catat_kunjungan();
?>
<!doctype html>
<html class="no-js ct" lang="id" <?= ct_atribut_tema() ?>>
<?php require CT_INC . '/kepala.php'; ?>
<body>
<?php if (ct('demo')) : ?><div class="ct-pita-demo" title="config/app.php: 'demo' => true">MODE DEMO</div><?php endif; ?>

<?php // Layar memuat: disingkirkan coretax.js saat halaman siap, dan muncul lagi saat pindah menu. ?>
<div class="ct-muat" id="ctMuat" role="status" aria-live="polite">
    <div class="ct-muat__kartu">
        <div class="ct-muat__merek">
            <img src="<?= e(ct_aset(ct('logo'))) ?>" alt="" width="34" height="34">
            <span><b><?= e(ct('singkat')) ?></b></span>
        </div>
        <span class="ct-muat__putar" aria-hidden="true"></span>
        <strong>Memuat halaman</strong>
        <small>Mohon tunggu&hellip;</small>
    </div>
</div>

<div class="wrapper">
<header class="header-top ct-kepala" header-theme="light">
    <div class="container-fluid">
        <div class="ct-kepala__baris">
            <div class="ct-kepala__kiri">
                <a class="ct-merek" href="<?= e(ct_url('dashboard.php')) ?>" title="Ke halaman awal">
                    <img class="ct-merek__logo" src="<?= e(ct_aset(ct('logo'))) ?>" alt="" width="30" height="30">
                    <span class="ct-merek__garis" aria-hidden="true"></span>
                </a>
                <div class="judul ct-merek__judul" id="judul">
                    <h3><b><strong><?= e(ct('nama')) ?></strong></b></h3>
                </div>
            </div>

            <div class="ct-kepala__tengah">
                <button type="button" class="ct-cari" id="ctCariBuka" aria-haspopup="dialog" title="Cari menu (Ctrl+K)">
                    <i class="ik ik-search" aria-hidden="true"></i>
                    <span class="ct-cari__teks">Cari Menu atau Laporan</span>
                    <span class="ct-cari__kbd" aria-hidden="true"><kbd>Ctrl</kbd><kbd>K</kbd></span>
                </button>
            </div>

            <div class="top-menu ct-kepala__kanan d-flex align-items-center">
                <button type="button" id="navbar-fullscreen" class="nav-link" title="Layar penuh" aria-label="Layar penuh"><i class="ik ik-maximize"></i></button>
                <button type="button" id="navbar-print" class="nav-link" title="Cetak halaman" aria-label="Cetak halaman"><i class="ik ik-printer"></i></button>

                <div class="dropdown ct-bahasa">
                    <button type="button" class="ct-bahasa__tombol" id="ctBahasaToggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false" title="Bahasa" aria-label="Bahasa">
                        <span class="ct-bendera ct-bendera--id" aria-hidden="true"></span><b>ID</b>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right p-0" aria-labelledby="ctBahasaToggle">
                        <div class="ct-bahasa__daftar" role="group">
                            <button type="button" data-ct-bahasa="id" aria-pressed="true"><span class="ct-bendera ct-bendera--id" aria-hidden="true"></span>Bahasa Indonesia</button>
                            <button type="button" data-ct-bahasa="en" aria-pressed="false"><span class="ct-bendera ct-bendera--en" aria-hidden="true"></span>English</button>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <button type="button" class="nav-link" id="dmpTemaToggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false" title="Mode terang / gelap" aria-label="Mode terang atau gelap">
                        <i class="ik ik-sun"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-right p-0" aria-labelledby="dmpTemaToggle">
                        <div class="dmp-tema">
                            <div class="dmp-tema__mode" role="group" aria-label="Mode terang atau gelap">
                                <button type="button" data-mode="light" aria-pressed="false"><i class="ik ik-sun" aria-hidden="true"></i> Terang</button>
                                <button type="button" data-mode="dark" aria-pressed="false"><i class="ik ik-moon" aria-hidden="true"></i> Gelap</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dropdown">
                    <a class="dropdown-toggle ct-pengguna" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="ik ik-user ct-pengguna__ikon" aria-hidden="true"></i>
                        <span class="ct-pengguna__teks">
                            <small><?= e($p['id']) ?></small>
                            <b><?= e($p['nama']) ?></b>
                        </span>
                        <span class="ct-pengguna__lencana"><?= e(strtoupper(substr((string) $p['peran'], 0, 3))) ?></span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                        <a class="dropdown-item" href="<?= e(ct_url('profil.php')) ?>"><i class="ik ik-user dropdown-icon"></i> Profil</a>
                        <?php if (ct_peran() === 'admin') : ?>
                        <a class="dropdown-item" href="<?= e(ct_url('admin/')) ?>"><i class="ik ik-settings dropdown-icon"></i> Menu Admin</a>
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?= e(ct_url('logout.php')) ?>"><i class="ik ik-power dropdown-icon"></i> Keluar</a>
                    </div>
                </div>

                <a class="ct-keluar" href="<?= e(ct_url('logout.php')) ?>" title="Keluar" aria-label="Keluar">
                    <i class="ik ik-log-out" aria-hidden="true"></i>
                </a>
            </div>
        </div>
    </div>
</header>

<script>
// Konfigurasi untuk skrip tema - diberikan server, bukan ditebak di
// peramban, karena aplikasi bisa dilayani di subfolder mana saja.
window.DMP_TEMA = {   // dibaca dmp-theme.js: tombol terang/gelap disimpan ke sesi
    url: <?= json_encode(CT_EKSPOR ? '' : ct_url('tema-simpan.php')) ?>,
    csrfNama: '_ct', csrfNilai: <?= json_encode(ct_csrf()) ?>
};
window.DMP_CT = {     // dibaca coretax.js: blok "Menu Rekomendasi" di laci Buka Menu
    menuPopuler: <?= json_encode(CT_EKSPOR ? '' : ct_url('menu-populer.php')) ?>
};
</script>

<div class="page-wrap">
<?php require CT_INC . '/sidebar.php'; ?>

<div class="main-content"><div class="container-fluid">
    <div class="ct-judul">
        <div>
            <h1><?= e($judul ?? '') ?></h1>
            <?php if (! empty($subjudul)) : ?><p><?= e($subjudul) ?></p><?php endif; ?>
        </div>
        <?php if (! empty($remah)) : ?>
        <nav aria-label="Remah roti">
            <ol class="ct-remah">
                <?php foreach ($remah as $i => $r) : ?>
                    <?php if ($i === count($remah) - 1 || empty($r[1])) : ?>
                        <li aria-current="page"><?= e($r[0]) ?></li>
                    <?php else : ?>
                        <li><a href="<?= e(ct_url($r[1])) ?>"><?= e($r[0]) ?></a></li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
        <?php endif; ?>
    </div>

    <?php foreach (ct_kilat_ambil() as [$jenis, $pesan]) : ?>
        <div class="ct-kilat ct-kilat--<?= e($jenis) ?>" role="<?= $jenis === 'gagal' ? 'alert' : 'status' ?>">
            <i class="ik <?= $jenis === 'gagal' ? 'ik-alert-circle' : 'ik-check-circle' ?>"></i><span><?= e($pesan) ?></span>
        </div>
    <?php endforeach; ?>

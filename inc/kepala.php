<?php
/**
 * inc/kepala.php — <head> bersama: dipakai tata letak utama, admin, dan login.
 *
 * URUTAN CSS PENTING (sama dengan DMP):
 *   bootstrap -> iconkit -> themekit -> tokens -> theme -> coretax -> ct-starter
 * Yang belakangan menang. Memindahkan tokens/theme/coretax ke atas
 * membuat CSS lama ThemeKit menimpa warna tema tanpa galat apa pun.
 *
 * Variabel: $judul (string). $tanpa_themekit = true untuk halaman yang
 * tidak memakai kerangka .wrapper (login, admin) - ThemeKit tidak dibutuhkan.
 */
$judul = $judul ?? '';
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($judul !== '' ? $judul . ' | ' : '') . ct('singkat')) ?></title>
    <link rel="icon" href="<?= e(ct_aset(ct('logo'))) ?>">

    <?php // Huruf: Plus Jakarta Sans (gaya Coretax). Tanpa internet, peramban memakai huruf sistem - tampilan tetap utuh. ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= e(ct_aset('assets/vendor/bootstrap/css/bootstrap.min.css')) ?>">
    <link rel="stylesheet" href="<?= e(ct_aset('assets/vendor/iconkit/css/iconkit.min.css')) ?>">
    <?php if (empty($tanpa_themekit)) : ?>
    <link rel="stylesheet" href="<?= e(ct_aset('assets/vendor/themekit/theme.min.css')) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(ct_aset('assets/ct/tokens.css')) ?>">
    <link rel="stylesheet" href="<?= e(ct_aset('assets/ct/theme.css')) ?>">
    <link rel="stylesheet" href="<?= e(ct_aset('assets/ct/coretax.css')) ?>">
    <link rel="stylesheet" href="<?= e(ct_aset('assets/ct/ct-starter.css')) ?>">
    <?= $kepala_tambahan ?? '' ?>
</head>

<?php
/**
 * config/menu-admin.php — menu area Administrator (tata letak kedua).
 *
 * KENAPA DAFTAR TERPISAH DARI config/menu.php
 *   Sama dengan DMP: layar admin BERDIRI SENDIRI dengan sidebar kiri
 *   tetap (inc/admin-awal.php), bukan baris menu mendatar. Menu
 *   dashboard tidak berguna di layar admin, dan tata letak yang berbeda
 *   membuat pengguna sadar sedang berada di area yang berakibat luas.
 *
 *   'url' relatif ke folder tema. 'juga' = awalan alamat lain yang tetap
 *   menandai butir ini aktif.
 */
return [
    ['url' => 'admin/',                'ikon' => 'ik-grid',        'teks' => 'Beranda Admin'],
    ['url' => 'admin/pintu-masuk.php', 'ikon' => 'ik-log-in',      'teks' => 'Pintu Masuk'],
    ['url' => 'admin/pengaturan.php',  'ikon' => 'ik-sliders',     'teks' => 'Pengaturan Tema'],
];

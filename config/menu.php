<?php
/**
 * config/menu.php — SATU-SATUNYA daftar menu aplikasi.
 *
 * APA
 *   Dari larik ini inc/sidebar.php merender <aside id="dmpSidebar">.
 *   coretax.js lalu MENYALIN markup itu menjadi tiga tampilan:
 *     1. baris menu mendatar di bawah header (panel "Jelajahi ...")
 *     2. laci "Buka Menu" di sisi kiri
 *     3. palet pencarian Ctrl+K
 *   Jadi menambah menu = menambah satu baris di sini. Tidak ada daftar kedua.
 *
 * BENTUK BUTIR
 *   ['label' => 'Teks']                       judul kelompok (huruf kecil di sidebar)
 *   ['label' => 'Alat Bantu', 'kumpul' => true]
 *        tautan tunggal sesudah label ini digabung jadi SATU grup
 *        "Alat Bantu" di baris mendatar (hemat lebar layar)
 *
 *   [                                          menu bergrup (punya submenu)
 *     'teks'  => 'Dashboard',
 *     'ikon'  => 'ik-bar-chart-2',             nama ikon iconkit (lihat komponen.php#ikon)
 *     'en'    => 'Dashboard',                  terjemahan Inggris (opsional)
 *     'peran' => ['admin'],                    hanya untuk peran ini (opsional)
 *     'anak'  => [ butir tautan, ... ],
 *   ]
 *
 *   [                                          tautan (tunggal atau anak)
 *     'teks'    => 'Tabel Data',
 *     'url'     => 'tabel.php',                relatif ke folder tema, atau http(s)://...
 *     'ikon'    => 'ik-grid',                  hanya dipakai pada tautan tunggal
 *     'lencana' => ['BARU', 'sukses'],         label kecil: sukses|awas|bahaya|netral
 *     'target'  => '_blank',
 *     'en'      => 'Data Table',
 *     'peran'   => ['admin'],
 *   ]
 *
 * KENAPA HANYA DUA TINGKAT (grup > tautan)
 *   Panel "Jelajahi" di baris mendatar menampilkan anak sebuah grup
 *   sebagai kartu sejajar. Tingkat ketiga tidak punya tempat di panel itu
 *   dan di palet Ctrl+K - menu bertingkat tiga di AdminLTE memang lazim,
 *   tetapi di pola Coretax justru disulitkan. Pecah menjadi dua grup.
 *
 * KENAPA PERAN DISARING DI SERVER, BUKAN DISEMBUNYIKAN CSS
 *   Menu yang hanya disembunyikan tetap muncul di sumber halaman dan di
 *   palet pencarian. Disaring di inc/sidebar.php = tidak pernah dikirim.
 *   Halamannya sendiri tetap dijaga ct_wajib_peran() - menyembunyikan
 *   tautan BUKAN pengaman.
 */
return [
    ['label' => 'Utama', 'en' => 'Main'],

    [
        'teks' => 'Dashboard', 'ikon' => 'ik-bar-chart-2', 'en' => 'Dashboard',
        'anak' => [
            ['teks' => 'Ringkasan',      'url' => 'dashboard.php', 'en' => 'Overview'],
            ['teks' => 'Grafik ECharts', 'url' => 'grafik.php',    'en' => 'ECharts', 'lencana' => ['BARU', 'sukses']],
        ],
    ],

    [
        'teks' => 'Antarmuka', 'ikon' => 'ik-layers', 'en' => 'Interface',
        'anak' => [
            ['teks' => 'Komponen',         'url' => 'komponen.php', 'en' => 'Components'],
            ['teks' => 'Ikon',             'url' => 'komponen.php#ikon', 'en' => 'Icons'],
            ['teks' => 'Formulir',         'url' => 'formulir.php', 'en' => 'Forms'],
            ['teks' => 'Tabel Data',       'url' => 'tabel.php',    'en' => 'Data Table'],
        ],
    ],

    [
        'teks' => 'Halaman Contoh', 'ikon' => 'ik-file-text', 'en' => 'Sample Pages',
        'anak' => [
            ['teks' => 'Halaman Kosong',   'url' => 'kosong.php',  'en' => 'Blank Page'],
            ['teks' => 'Profil Pengguna',  'url' => 'profil.php',  'en' => 'User Profile'],
            ['teks' => 'Halaman 404',      'url' => '404.php',     'en' => '404 Page'],
            ['teks' => 'Halaman Login',    'url' => 'login.php?pratinjau=1', 'en' => 'Login Page', 'target' => '_blank'],
        ],
    ],

    ['teks' => 'Tautan Tunggal', 'ikon' => 'ik-link', 'url' => 'kosong.php?contoh=tunggal', 'en' => 'Single Link'],

    ['label' => 'Alat Bantu', 'kumpul' => true, 'en' => 'Tools'],
    ['teks' => 'Dokumentasi', 'ikon' => 'ik-book-open', 'url' => 'dokumentasi.php', 'en' => 'Documentation'],
    ['teks' => 'Menu Admin',  'ikon' => 'ik-settings',  'url' => 'admin/',          'en' => 'Admin Menu', 'peran' => ['admin']],
];

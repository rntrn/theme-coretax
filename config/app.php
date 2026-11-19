<?php
/**
 * config/app.php — pengaturan aplikasi (satu-satunya tempat yang biasanya
 * perlu diubah saat tema ini dipakai untuk aplikasi baru).
 *
 * APA
 *   Nama aplikasi, logo, tema bawaan, peran pengguna, dan pintu masuk.
 *
 * KENAPA BERKAS PHP, BUKAN .env / JSON
 *   Tema ini sengaja tanpa composer dan tanpa framework: cukup salin
 *   foldernya ke htdocs. Berkas PHP yang `return [...]` bisa diberi
 *   komentar (JSON tidak bisa) dan tidak butuh pustaka pembaca.
 */
return [
    // ------------------------------------------------------------------
    // Identitas
    // ------------------------------------------------------------------
    'nama'       => 'APLIKASI CORETAX STARTER',   // judul besar di header (huruf kapital seperti DMP)
    'singkat'    => 'CTX',                        // nama pendek: sidebar, layar memuat, tab peramban
    'subjudul'   => 'Tema Dasar Coretax',          // baris kecil di bawah nama pendek
    'logo'       => 'assets/ct/logo.svg',          // ganti dengan logo aplikasi Anda (PNG/SVG, persegi)
    'pemilik'    => 'Nama Organisasi',             // footer kanan
    'versi'      => 'v1.0.0',

    // ------------------------------------------------------------------
    // Alamat dasar. Kosongkan = dihitung otomatis dari letak folder di
    // bawah DOCUMENT_ROOT (mis. /aplikasi-saya/). Isi bila
    // aplikasi dilayani di balik proxy dengan awalan alamat berbeda.
    // ------------------------------------------------------------------
    'base_url'   => '',

    // ------------------------------------------------------------------
    // Tema bawaan. Pengguna bisa menggantinya dari tombol matahari/bulan;
    // pilihannya disimpan di sesi (tema-simpan.php).
    //   tema  : biru | hijau | teal | nila | ungu | anggrek | oranye | bata (tokens.css)
    //   mode  : light | dark | auto (ikut sistem operasi)
    //   palet : warna seri grafik - laut | bumi | hutan | senja | cerah | lembut |
    //           permen | kontras | aman | abu | tunggal (tokens.css)
    // ------------------------------------------------------------------
    'tema'       => 'biru',
    'mode'       => 'light',
    'palet'      => 'laut',

    // ------------------------------------------------------------------
    // Pintu masuk — padanan menu Administrator > Pintu Masuk di DMP.
    //   mandiri : formulir login di login.php
    //   sso     : tombol "Masuk lewat <sso_nama>" (aplikasi induk)
    // Nilai di sini hanya BAWAAN; admin mengubahnya dari admin/pintu-masuk.php
    // dan hasilnya disimpan di data/pintu.json.
    // ------------------------------------------------------------------
    'pintu_bawaan' => ['mandiri' => true, 'sso' => false],
    'sso_nama'     => 'Coretax',
    // Berapa hari ke belakang sebuah login SSO dianggap "bukti jalan".
    // Login mandiri baru boleh ditutup bila ada bukti itu.
    'hari_bukti'   => 7,

    // ------------------------------------------------------------------
    // MODE DEMO — WAJIB dimatikan sebelum dipakai sungguhan.
    //
    // true  : login.php menerima akun di 'akun_demo' di bawah, dan
    //         sso.php mensimulasikan kiriman token dari aplikasi induk.
    // false : keduanya memanggil ct_autentikasi() / ct_sso_verifikasi()
    //         di inc/auth.php - sambungkan ke LDAP/database/SSO Anda di sana.
    // ------------------------------------------------------------------
    'demo'       => true,
    'akun_demo'  => [
        // nama pengguna => [kata sandi, nama tampil, peran]
        'admin' => ['admin', 'ADMIN CONTOH', 'admin'],
        'user'  => ['user',  'PENGGUNA CONTOH', 'pengguna'],
    ],

    // Peran yang dikenal. Butir menu dengan 'peran' => ['admin'] hanya
    // tampil (dan hanya bisa dibuka) oleh peran itu.
    'peran'      => [
        'admin'    => 'Administrator',
        'pengguna' => 'Pengguna',
    ],

    // Zona waktu untuk jam di catatan login, footer, dsb. Tanpa ini PHP
    // sering memakai UTC - jam tampil 7 jam lebih awal di WIB.
    'zona'        => 'Asia/Jakarta',

    // Sesi berakhir sesudah sekian menit tanpa aktivitas.
    'batas_menit' => 120,
];

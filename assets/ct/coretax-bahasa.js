/* =============================================================
 * coretax-bahasa.js — Pilihan bahasa bingkai aplikasi (Fase 113.6)
 *
 * APA
 * ---
 * Tombol bahasa di header (ID / EN / JV, seperti pemilih bahasa
 * Coretax). Yang diterjemahkan: BINGKAI aplikasi - header, nama menu
 * dan submenu, sidebar "Buka Menu", panel "Jelajahi", palet Ctrl+K,
 * layar memuat, footer.
 *
 * YANG TIDAK DITERJEMAHKAN
 * ------------------------
 * Isi dashboard (judul kartu, kolom tabel, grafik, pesan). Teks itu
 * tertulis langsung di ratusan berkas view - menerjemahkannya adalah
 * pekerjaan terpisah yang jauh lebih besar dan belum diputuskan.
 *
 * KENAPA BEGINI
 * -------------
 * - Kamus ditulis di sini, BUKAN layanan terjemahan otomatis (Google
 *   Translate dsb). Layanan semacam itu mengirim isi halaman - data
 *   pengguna - ke server luar.
 * - Nama menu diterjemahkan dengan mencocokkan TEKS UTUH yang sudah
 *   dirender inc/sidebar.php (dari config/menu.php). Markup sidebar tidak diubah; menu baru
 *   yang belum ada di kamus tetap tampil dalam bahasa Indonesia -
 *   tidak kosong, tidak galat.
 * - Pilihan disimpan di peramban (localStorage), bukan di Oracle:
 *   tidak perlu tabel baru untuk sebuah kenyamanan tampilan.
 * - Berganti bahasa = muat ulang halaman. Sidebar, panel, dan palet
 *   dibangun coretax.js SEKALI dari teks yang ada; membangunnya ulang
 *   di tempat berarti menulis ulang separuh coretax.js. Layar memuat
 *   menutupi pergantiannya.
 *
 * URUTAN MUAT
 * -----------
 * Berkas ini dimuat SEBELUM coretax.js (keduanya defer, urutan
 * dijaga peramban). Ia menerjemahkan header dan menu lebih dulu,
 * lalu coretax.js membangun sidebar/panel dari teks yang SUDAH
 * diterjemahkan, dan memakai CtBahasa.t() untuk teks buatannya
 * sendiri.
 *
 * Menambah terjemahan menu: isi kunci 'en' pada butir di config/menu.php.
 * Teks bingkai lain: tambahkan di MENU di bawah, kuncinya PERSIS teks
 * yang tampil.
 * ============================================================= */
(function () {
    'use strict';

    var KUNCI = 'ct_bahasa';
    // tampil: false = tidak bisa dipilih, dan yang sudah terlanjur
    // memilihnya dikembalikan ke Bahasa Indonesia (lihat di bawah).
    // Basa Jawa disembunyikan sejak Fase 113.7 atas permintaan pemilik
    // proyek; kamusnya DIBIARKAN supaya bisa dinyalakan lagi - cukup
    // tampil: true di sini + tombolnya di inc/navbar.php.
    var BAHASA = {
        id: { kode: 'ID', nama: 'Bahasa Indonesia', lang: 'id', tampil: true },
        en: { kode: 'EN', nama: 'English',          lang: 'en', tampil: true },
        jv: { kode: 'JV', nama: 'Basa Jawa',        lang: 'jv', tampil: false }
    };

    // ---------------------------------------------------------
    // Teks buatan coretax.js - dengan tempat isian {x}
    // ---------------------------------------------------------
    var UI = {
        jelajahi:   { id: 'Jelajahi {x}', en: 'Explore {x}', jv: 'Jlajahi {x}' },
        tersedia:   { id: '{n} menu tersedia. Pilih menu atau gunakan pencarian.',
                      en: '{n} menus available. Pick a menu or use search.',
                      jv: '{n} menu cumawis. Pilih menu utawi ginakaken pados.' },
        cariSemua:  { id: 'Cari semua menu', en: 'Search all menus', jv: 'Madosi sedaya menu' },
        labelMenu:  { id: 'Menu', en: 'Menu', jv: 'Menu' },
        terakhir:   { id: 'Terakhir Dibuka', en: 'Recently Opened', jv: 'Pungkasan Dipunbikak' },
        semuaMenu:  { id: 'Semua Menu', en: 'All Menus', jv: 'Sedaya Menu' },
        saring:     { id: 'Saring menu…', en: 'Filter menus…', jv: 'Saring menu…' },
        kosong:     { id: 'Tidak ada menu yang cocok.', en: 'No matching menu.', jv: 'Boten wonten menu ingkang cocog.' },
        bukaMenu:   { id: 'Buka Menu', en: 'Open Menu', jv: 'Bikak Menu' },
        tutupMenu:  { id: 'Tutup menu', en: 'Close menu', jv: 'Tutup menu' },
        nMenu:      { id: '{n} menu', en: '{n} menus', jv: '{n} menu' },
        rekom:      { id: 'Menu Rekomendasi', en: 'Recommended Menus', jv: 'Menu Pilihan' },
        rekomKet:   { id: 'Paling banyak dibuka {h} hari terakhir', en: 'Most opened in the last {h} days',
                      jv: 'Paling kathah dipunbikak {h} dinten pungkasan' },
        rekomOrang: { id: '{o} orang membukanya dalam {h} hari terakhir', en: '{o} people opened it in the last {h} days',
                      jv: '{o} tiyang mbikak ing {h} dinten pungkasan' },
        paletPh:    { id: 'Ketik nama menu atau laporan…', en: 'Type a menu or report name…',
                      jv: 'Serat nami menu utawi laporan…' },
        paletLabel: { id: 'Cari menu atau laporan', en: 'Search menus or reports', jv: 'Madosi menu utawi laporan' },
        pilih:      { id: 'pilih', en: 'select', jv: 'pilih' },
        buka:       { id: 'buka', en: 'open', jv: 'bikak' },
        tutup:      { id: 'tutup', en: 'close', jv: 'tutup' },
        geserKiri:  { id: 'Geser menu ke kiri', en: 'Scroll menu left', jv: 'Geser menu mangiwa' },
        geserKanan: { id: 'Geser menu ke kanan', en: 'Scroll menu right', jv: 'Geser menu manengen' },
        bahasa:     { id: 'Bahasa', en: 'Language', jv: 'Basa' }
    };

    // ---------------------------------------------------------
    // Teks yang SUDAH ADA di halaman (header, menu, footer) -
    // dicocokkan utuh. Kunci = teks Indonesia persis.
    // ---------------------------------------------------------
    var MENU = {
        // Bingkai: header, layar memuat, footer. Nama menu TIDAK ditulis
        // di sini - ia datang dari config/menu.php (kunci 'en') lewat
        // window.CT_KAMUS, supaya daftar menu tetap SATU.
        'Cari Menu atau Laporan':          ['Search Menus or Reports', 'Madosi Menu utawi Laporan'],
        'Cari menu (Ctrl+K)':              ['Search menus (Ctrl+K)', 'Madosi menu (Ctrl+K)'],
        'Layar penuh':                     ['Full screen', 'Layar kebak'],
        'Cetak halaman':                   ['Print page', 'Cithak kaca'],
        'Mode terang / gelap':             ['Light / dark mode', 'Mode padhang / peteng'],
        'Mode terang atau gelap':          ['Light or dark mode', 'Mode padhang utawi peteng'],
        'Terang':                          ['Light', 'Padhang'],
        'Gelap':                           ['Dark', 'Peteng'],
        'Keluar':                          ['Sign out', 'Medal'],
        'Profil':                          ['Profile', 'Profil'],
        'Ke halaman awal':                 ['Go to start page', 'Dhateng kaca wiwitan'],
        'Memuat halaman':                  ['Loading page', 'Ngemot kaca'],
        'Mohon tunggu…':              ['Please wait…', 'Nyuwun ngentosi…'],
        'Cari menu…':                 ['Search menu…', 'Madosi menu…'],
        'Ciutkan menu':                    ['Collapse menu', 'Ciutaken menu'],
        'Tidak ada menu yang cocok.':      ['No matching menu.', 'Boten wonten menu ingkang cocog.'],
        'Alat Bantu':                      ['Tools', 'Piranti'],
        'Hak cipta dilindungi.':           ['All rights reserved.', 'Hak cipta dipunreksa.']
    };

    // Kamus tambahan dari halaman (config/menu.php, kunci 'en').
    // Bentuknya sama: { 'Teks Indonesia': ['English', 'Jawa'] }.
    if (window.CT_KAMUS && typeof window.CT_KAMUS === 'object') {
        Object.keys(window.CT_KAMUS).forEach(function (k) { MENU[k] = window.CT_KAMUS[k]; });
    }

    // ---------------------------------------------------------
    // Bahasa aktif
    // ---------------------------------------------------------
    var kode = 'id';
    try { kode = localStorage.getItem(KUNCI) || 'id'; } catch (e) { /* mode privat */ }
    // Kode tak dikenal, ATAU bahasa yang kini disembunyikan: pengguna
    // yang dulu memilih Basa Jawa tidak boleh terjebak di bahasa yang
    // tombolnya sudah tidak ada - kembali ke Indonesia.
    if (!BAHASA[kode] || !BAHASA[kode].tampil) { kode = 'id'; }

    var kolom = kode === 'en' ? 0 : (kode === 'jv' ? 1 : -1);

    // Terjemahan -> teks Indonesia, supaya pencarian tetap mengenali
    // nama aslinya ("pembayaran" tetap menemukan "Periodic Payments").
    var asliDari = {};
    if (kolom >= 0) {
        Object.keys(MENU).forEach(function (id) { asliDari[MENU[id][kolom]] = id; });
    }

    function t(kunci, isian) {
        var e = UI[kunci];
        var s = e ? (e[kode] || e.id) : kunci;
        if (isian) {
            Object.keys(isian).forEach(function (k) { s = s.split('{' + k + '}').join(String(isian[k])); });
        }
        return s;
    }

    function nama(teksId) {
        var k = String(teksId).replace(/\s+/g, ' ').trim();
        return kolom >= 0 && MENU[k] ? MENU[k][kolom] : teksId;
    }

    function asli(teks) {
        var k = String(teks).replace(/\s+/g, ' ').trim();
        return asliDari[k] || k;
    }

    // Terjemahkan teks & atribut di dalam satu elemen. Hanya node teks
    // yang ISINYA UTUH cocok dengan kamus - bukan cari-ganti potongan,
    // supaya "Lapisan" tidak ikut mengubah "IKU Lapisan" jadi setengah
    // Inggris.
    function terapkan(akar) {
        if (kolom < 0 || !akar) { return; }

        var jalan = document.createTreeWalker(akar, NodeFilter.SHOW_TEXT, null);
        var n;
        while ((n = jalan.nextNode())) {
            var bersih = n.nodeValue.replace(/\s+/g, ' ').trim();
            if (bersih && MENU[bersih]) {
                n.nodeValue = n.nodeValue.replace(n.nodeValue.trim(), MENU[bersih][kolom]);
            }
        }

        var atr = ['placeholder', 'title', 'aria-label'];
        var el = akar.querySelectorAll ? akar.querySelectorAll('[placeholder],[title],[aria-label]') : [];
        Array.prototype.forEach.call(el, function (x) {
            atr.forEach(function (a) {
                var v = x.getAttribute(a);
                if (v && MENU[v.trim()]) { x.setAttribute(a, MENU[v.trim()][kolom]); }
            });
        });
    }

    function ganti(kodeBaru) {
        if (!BAHASA[kodeBaru] || !BAHASA[kodeBaru].tampil || kodeBaru === kode) { return; }
        try { localStorage.setItem(KUNCI, kodeBaru); } catch (e) { return; }
        var m = document.getElementById('ctMuat');
        if (m) { m.classList.toggle('lagi'); m.classList.remove('selesai'); }
        window.location.reload();
    }

    window.CtBahasa = { kode: kode, t: t, nama: nama, asli: asli, terapkan: terapkan, ganti: ganti };

    // ---------------------------------------------------------
    // Terapkan ke bingkai yang sudah dirender server
    // ---------------------------------------------------------
    document.documentElement.setAttribute('lang', BAHASA[kode].lang);

    ['.ct-kepala', '#dmpSidebar', '#ctMuat', '.dmp-footer'].forEach(function (sel) {
        Array.prototype.forEach.call(document.querySelectorAll(sel), terapkan);
    });

    // Tombol bahasa di header
    var tombol = document.getElementById('ctBahasaToggle');
    if (tombol) {
        tombol.querySelector('.ct-bendera').className = 'ct-bendera ct-bendera--' + kode;
        tombol.querySelector('b').textContent = BAHASA[kode].kode;
        tombol.setAttribute('title', t('bahasa') + ': ' + BAHASA[kode].nama);
        tombol.setAttribute('aria-label', t('bahasa') + ': ' + BAHASA[kode].nama);
    }
    Array.prototype.forEach.call(document.querySelectorAll('[data-ct-bahasa]'), function (b) {
        var k = b.getAttribute('data-ct-bahasa');
        b.setAttribute('aria-pressed', k === kode ? 'true' : 'false');
        b.addEventListener('click', function () { ganti(k); });
    });
})();

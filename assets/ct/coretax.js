/* =============================================================
 * coretax.js — Perilaku tampilan gaya Coretax (Fase 113)
 *
 * APA
 * ---
 * Empat hal, semuanya membaca markup yang SUDAH ADA:
 *   1. Layar memuat   - disingkirkan begitu halaman siap
 *   2. Menu mendatar  - judul grup membuka kartu "Jelajahi ..." di bawahnya
 *   3. Sidebar        - seluruh menu, tersimpan sebagai tab "Buka Menu"
 *   4. Palet cari     - Ctrl+K / klik kotak "Cari Menu atau Laporan"
 *
 * KENAPA BEGINI
 * -------------
 * Seluruh daftar menu tetap satu: <aside id="dmpSidebar"> di
 * inc/sidebar.php (dibangun dari config/menu.php), lengkap dengan
 * penyaring perannya. Berkas ini
 * tidak punya daftar menu sendiri - sidebar dan hasil pencarian
 * adalah SALINAN dari tautan yang lolos gerbang. Menu yang disembunyikan
 * dari seseorang otomatis juga tidak muncul di pencariannya.
 *
 * Skrip sebaris di inc/sidebar.php tetap berjalan lebih dulu
 * (berkas ini dimuat dengan defer). Darinya kita memakai hasil
 * penandaan menu aktif (.aktif dan .aktif-induk) - logika itu tidak
 * ditulis ulang di sini.
 *
 * KENAPA BUKAN CARA LAIN
 * ----------------------
 * - Membangun menu dari JSON di server: berarti daftar menu kedua.
 * - Membuka panel saat kursor lewat (hover): di Coretax panel dibuka
 *   dengan KLIK. Hover juga membuat panel lebar ini muncul-hilang
 *   setiap kali kursor melintas menuju tombol di bawahnya.
 *
 * Tanpa jQuery - berkas ini ikut dimuat di halaman yang tidak
 * memuat jQuery lebih dulu.
 * ============================================================= */
(function () {
    'use strict';

    var d = document;

    function simpan(kunci, nilai) {
        try { localStorage.setItem(kunci, nilai); } catch (e) { /* mode privat */ }
    }
    function baca(kunci) {
        try { return localStorage.getItem(kunci); } catch (e) { return null; }
    }
    function teks(el) {
        return (el && el.textContent ? el.textContent : '').replace(/\s+/g, ' ').trim();
    }

    // Terjemahan teks buatan berkas ini (Fase 113.6). Kamusnya di
    // coretax-bahasa.js; teks Indonesia di tiap pemanggilan adalah
    // CADANGAN bila berkas kamus gagal dimuat - bukan kamus kedua.
    function T(kunci, isian, cadangan) {
        var s = window.CtBahasa ? window.CtBahasa.t(kunci, isian) : cadangan;
        if (!window.CtBahasa && isian) {
            Object.keys(isian).forEach(function (k) { s = s.split('{' + k + '}').join(String(isian[k])); });
        }
        return s;
    }
    // Teks yang dicari = nama tampil + nama Indonesianya, supaya
    // pengguna berbahasa Inggris/Jawa yang mengetik "pembayaran" tetap
    // menemukan menunya.
    function teksCari(t) {
        return window.CtBahasa ? t + ' ' + window.CtBahasa.asli(t) : t;
    }
    // Untuk teks yang disisipkan lewat innerHTML.
    function esc(s) {
        return String(s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // ---------------------------------------------------------
    // 1. LAYAR MEMUAT
    //
    // Disingkirkan saat 'load', atau paling lambat 1,5 detik sesudah
    // DOM siap - halaman dengan gambar/iframe lambat tidak boleh
    // tertutup lama hanya karena satu berkas belum datang. CSS-nya
    // sendiri juga menghilang sesudah 12 detik bila skrip ini gagal.
    // ---------------------------------------------------------
    var muat = d.getElementById('ctMuat');

    function muatSelesai() {
        if (muat) { muat.classList.add('selesai'); }
    }

    function muatMulai() {
        if (!muat) { return; }
        // Ganti nama animasi pengaman supaya hitung mundur 12 detiknya
        // mulai lagi dari nol (animasi yang sudah selesai tidak
        // berjalan ulang hanya karena kelasnya dilepas).
        muat.classList.toggle('lagi');
        muat.classList.remove('selesai');
    }

    if (d.readyState === 'complete') {
        muatSelesai();
    } else {
        window.addEventListener('load', muatSelesai);
        d.addEventListener('DOMContentLoaded', function () { setTimeout(muatSelesai, 1500); });
    }
    // Kembali lewat tombol Back: halaman dipulihkan dari cache
    // peramban tanpa 'load' - overlay yang dinyalakan sebelum pergi
    // harus dimatikan di sini.
    window.addEventListener('pageshow', function (e) { if (e.persisted) { muatSelesai(); } });

    // Tampilkan lagi saat berpindah halaman lewat menu. HANYA lewat
    // menu: tautan lain bisa berupa unduhan CSV yang tidak pernah
    // meninggalkan halaman, dan overlay-nya akan menggantung.
    function pasangMuatPadaTautan(a) {
        a.addEventListener('click', function (e) {
            if (e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey) { return; }
            if (a.target && a.target !== '_self') { return; }
            var h = a.getAttribute('href') || '';
            if (!h || h.charAt(0) === '#' || /^javascript:/i.test(h)) { return; }
            muatMulai();
        });
    }

    // Ikon tombol mode di header (matahari/bulan) mengikuti pilihan
    // saat itu juga. Penyimpanannya tetap dikerjakan dmp-theme.js -
    // di sini HANYA ikonnya, tanpa menyentuh logika simpan.
    (function ikonMode() {
        var ikon = d.querySelector('#dmpTemaToggle i');
        if (!ikon) { return; }
        Array.prototype.forEach.call(d.querySelectorAll('.dmp-tema__mode [data-mode]'), function (b) {
            b.addEventListener('click', function () {
                var gelap = b.getAttribute('data-mode') === 'dark';
                ikon.className = 'ik ' + (gelap ? 'ik-moon' : 'ik-sun');
            });
        });
    })();

    // ---------------------------------------------------------
    // 2. MENU MENDATAR
    // ---------------------------------------------------------
    var nav = d.getElementById('dmpSidebar');
    var isi = d.getElementById('dmpNavIsi');

    if (!nav || !isi) { return; }   // halaman tanpa sidebar (modal, login)

    // Sidebar lama bisa tersimpan dalam keadaan "ciut" (ikon saja).
    // Di baris mendatar keadaan itu tidak berarti - dan CSS lamanya
    // menyembunyikan nama menu. Pilihannya di localStorage TIDAK
    // dihapus, supaya tetap berlaku bila tema lama dipakai lagi.
    nav.setAttribute('data-ciut', '0');
    d.body.classList.remove('dmp-nav-ciut');

    // Tautan tunggal di bawah label bertanda data-ct-kumpul (mis.
    // "Alat Bantu") dikumpulkan jadi SATU grup di baris mendatar.
    // Diukur di layar 1600 px: baris menu yang berisi banyak tautan
    // tunggal cepat melebihi lebar layar; digabung, ia muat. Elemen <a>
    // yang sama DIPINDAH, bukan disalin - tujuan dan hak aksesnya tetap
    // dari inc/sidebar.php. Nama grup = isi atribut data-ct-kumpul.
    (function kumpulkanAlatBantu() {
        var label = Array.prototype.filter.call(isi.querySelectorAll(':scope > .dmp-nav__label'), function (l) {
            return l.hasAttribute('data-ct-kumpul');
        });
        if (!label.length) { return; }
        var namaGrup = label[0].getAttribute('data-ct-kumpul') || 'Alat Bantu';

        var tautan = [];
        label.forEach(function (l) {
            var el = l.nextElementSibling;
            while (el && !el.classList.contains('dmp-nav__label')) {
                var lanjut = el.nextElementSibling;
                if (el.matches('a.dmp-nav__butir')) { tautan.push(el); }
                el = lanjut;
            }
        });
        if (tautan.length < 2) { return; }

        var g = d.createElement('div');
        g.className = 'dmp-nav__grup ct-grup-buatan';
        g.innerHTML =
            '<button type="button" class="dmp-nav__butir dmp-nav__butir--grup">' +
                '<i class="ik ik-briefcase"></i><span>' +
                    esc(window.CtBahasa ? window.CtBahasa.nama(namaGrup) : namaGrup) + '</span>' +
            '</button>' +
            '<div class="dmp-nav__sub"><div class="dmp-nav__sub-isi"></div></div>';
        var wadahAnak = g.querySelector('.dmp-nav__sub-isi');

        tautan.forEach(function (a) {
            var aktif = a.classList.contains('aktif');
            a.className = 'dmp-nav__anak ct-anak-ikon' + (aktif ? ' aktif' : '');
            wadahAnak.appendChild(a);
            if (aktif) { g.querySelector('.dmp-nav__butir--grup').classList.add('aktif-induk'); }
        });

        label[0].parentNode.insertBefore(g, label[0]);
    })();

    var grupSemua = Array.prototype.slice.call(isi.querySelectorAll('.dmp-nav__grup'));

    // Diisi bagian 3 (sidebar semua menu). Panel atas dan sidebar
    // tidak boleh terbuka bersamaan - keduanya menutupi halaman.
    var aturSamping = null;

    // ---------------------------------------------------------
    // RIWAYAT KUNJUNGAN - untuk bagian "Terakhir Dibuka" di panel
    // (padanan "Laporan Tersemat" Coretax). Disimpan di peramban
    // masing-masing: ini kenyamanan pribadi, bukan data aplikasi,
    // dan tidak perlu tabel baru di Oracle.
    // ---------------------------------------------------------
    var KUNCI_RIWAYAT = 'ct_riwayat_menu';
    var riwayat = [];
    try { riwayat = JSON.parse(baca(KUNCI_RIWAYAT) || '[]'); } catch (e) { riwayat = []; }
    if (!Array.isArray(riwayat)) { riwayat = []; }

    (function catatKunjungan() {
        var kini = isi.querySelector('a.aktif[href]');
        if (!kini) { return; }
        riwayat = [kini.href].concat(riwayat.filter(function (h) { return h !== kini.href; })).slice(0, 20);
        simpan(KUNCI_RIWAYAT, JSON.stringify(riwayat));
    })();

    function tutupSemua(kecuali) {
        d.body.classList.toggle('ct-menu-buka', !!kecuali);
        grupSemua.forEach(function (g) {
            if (g === kecuali) { return; }
            g.classList.remove('ct-buka');
            var t = g.querySelector('.dmp-nav__butir--grup');
            if (t) { t.setAttribute('aria-expanded', 'false'); }
        });
    }

    // Kepala panel ala Coretax: "MENU / Jelajahi <grup> / N menu
    // tersedia" + tombol "Cari semua menu", lalu "Terakhir Dibuka".
    // Tautannya sendiri tetap elemen asli dari inc/sidebar.php.
    function bangunKepalaPanel(tbl, anak) {
        var sub    = anak.parentNode;
        var tautan = Array.prototype.filter.call(anak.querySelectorAll('a[href]'), function (a) {
            return a.style.display !== 'none';
        });

        var kepala = d.createElement('div');
        kepala.className = 'ct-panel__kepala';
        kepala.innerHTML =
            '<div class="ct-panel__judul"><small>' + esc(T('labelMenu', null, 'Menu')) + '</small><h4></h4><p></p></div>' +
            '<button type="button" class="ct-panel__cari">' +
                '<i class="ik ik-search" aria-hidden="true"></i><span>' + esc(T('cariSemua', null, 'Cari semua menu')) + '</span><kbd>Ctrl+K</kbd>' +
            '</button>';
        kepala.querySelector('h4').textContent = T('jelajahi', { x: teks(tbl) }, 'Jelajahi {x}');
        kepala.querySelector('p').textContent  = T('tersedia', { n: tautan.length }, '{n} menu tersedia. Pilih menu atau gunakan pencarian.');
        kepala.querySelector('.ct-panel__cari').addEventListener('click', function (e) {
            e.stopPropagation();
            tutupSemua(null);
            bukaPalet();
        });
        sub.insertBefore(kepala, anak);

        var terakhir = [];
        riwayat.forEach(function (h) {
            tautan.forEach(function (a) { if (a.href === h && terakhir.length < 2) { terakhir.push(a); } });
        });
        if (!terakhir.length) { return; }

        var blok = d.createElement('div');
        blok.className = 'ct-panel__terakhir';
        blok.innerHTML = '<h5>' + esc(T('terakhir', null, 'Terakhir Dibuka')) + '</h5><div class="ct-panel__terakhir-isi"></div>';
        var wadah = blok.querySelector('.ct-panel__terakhir-isi');
        terakhir.forEach(function (a) {
            var s = a.cloneNode(true);
            s.className = 'ct-panel__sorot';
            pasangMuatPadaTautan(s);
            wadah.appendChild(s);
        });
        sub.insertBefore(blok, anak);
    }

    // Panel diletakkan di bawah judul grupnya, bukan selebar layar.
    // Dihitung saat dibuka karena baris menu bisa digulir mendatar.
    function aturLetakPanel(g, tbl) {
        var sub   = g.querySelector('.dmp-nav__sub');
        var r     = tbl.getBoundingClientRect();
        var lebar = Math.min(580, window.innerWidth - 24);
        var kiri  = r.left + r.width / 2 - lebar / 2;
        kiri = Math.max(12, Math.min(kiri, window.innerWidth - lebar - 12));
        sub.style.left  = kiri + 'px';
        sub.style.width = lebar + 'px';
    }

    grupSemua.forEach(function (g) {
        var tbl  = g.querySelector('.dmp-nav__butir--grup');
        var anak = g.querySelector('.dmp-nav__sub-isi');

        // Skrip sidebar lama membuka grup halaman aktif (.buka). Di
        // baris mendatar itu berarti panel terbuka sendiri saat halaman
        // dimuat - jadi dilepas. .aktif-induk tetap, sebagai pil kuning.
        g.classList.remove('buka', 'paksa-buka');

        if (!tbl || !anak) { return; }

        // Grup yang seluruh tautannya tertahan gerbang dev/prod akan
        // kosong - tidak ditampilkan sama sekali.
        if (!anak.querySelector('a[href]')) { g.hidden = true; return; }

        bangunKepalaPanel(tbl, anak);
        tbl.setAttribute('aria-haspopup', 'true');
        tbl.setAttribute('aria-expanded', 'false');

        // Handler lama (toggle .buka) tetap jalan lebih dulu; handler
        // ini yang menentukan panel mana yang terbuka.
        tbl.addEventListener('click', function (e) {
            e.stopPropagation();
            var akanBuka = !g.classList.contains('ct-buka');
            if (aturSamping) { aturSamping(false); }
            tutupSemua(g);
            if (akanBuka) { aturLetakPanel(g, tbl); }
            g.classList.toggle('ct-buka', akanBuka);
            d.body.classList.toggle('ct-menu-buka', akanBuka);
            tbl.setAttribute('aria-expanded', akanBuka ? 'true' : 'false');
        });
    });

    // Klik di luar panel, Esc, atau ukuran jendela berubah: tutup.
    d.addEventListener('click', function (e) {
        if (!e.target.closest || !e.target.closest('.dmp-nav__sub')) { tutupSemua(null); }
    });
    window.addEventListener('resize', function () { tutupSemua(null); });
    d.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { tutupSemua(null); }
    });
    // Baris menu bisa digulir mendatar di layar sempit; panel yang
    // terbuka jadi tidak lagi berada di bawah judulnya.
    isi.addEventListener('scroll', function () { tutupSemua(null); }, { passive: true });

    // Tautan menu ASLI saja. Salinan di bagian "Terakhir Dibuka" (panel
    // atas) juga berada di dalam #dmpNavIsi; tanpa penyaring ini ia
    // terbaca dua kali - muncul ganda di palet Ctrl+K dan merebut tempat
    // tautan aslinya di Menu Rekomendasi (ditemukan Fase 113.5).
    function tautanMenu() {
        return Array.prototype.filter.call(isi.querySelectorAll('a[href]'), function (a) {
            return !a.closest('.ct-panel__terakhir');
        });
    }

    tautanMenu().forEach(pasangMuatPadaTautan);

    // Layar yang masih terlalu sempit (mis. 1366 px): baris menu bisa
    // digulir, dan tombol ‹ › muncul di ujung yang masih menyimpan
    // menu - tanpa itu tidak ada tanda bahwa ada menu di luar layar.
    (function panahGulir() {
        function buat(arah) {
            var b = d.createElement('button');
            b.type = 'button';
            b.className = 'ct-geser ct-geser--' + arah;
            b.setAttribute('aria-label', arah === 'kiri' ? T('geserKiri', null, 'Geser menu ke kiri') : T('geserKanan', null, 'Geser menu ke kanan'));
            b.innerHTML = arah === 'kiri' ? '&lsaquo;' : '&rsaquo;';
            b.addEventListener('click', function (e) {
                e.stopPropagation();
                isi.scrollBy({ left: (arah === 'kiri' ? -1 : 1) * isi.clientWidth * 0.6, behavior: 'smooth' });
            });
            nav.appendChild(b);
            return b;
        }
        var kiri = buat('kiri'), kanan = buat('kanan');

        function perbarui() {
            var sisa = isi.scrollWidth - isi.clientWidth;
            kiri.hidden  = sisa <= 1 || isi.scrollLeft <= 1;
            kanan.hidden = sisa <= 1 || isi.scrollLeft >= sisa - 1;
        }
        isi.addEventListener('scroll', perbarui, { passive: true });
        window.addEventListener('resize', perbarui);
        // Font Plus Jakarta Sans datang belakangan dan mengubah lebar
        // butir; ukur ulang sesudah halaman selesai dimuat.
        window.addEventListener('load', perbarui);
        perbarui();
    })();

    // Gulir baris menu supaya pil kuning (menu aktif) terlihat.
    var pilAktif = isi.querySelector('.dmp-nav__butir--grup.aktif-induk, :scope > .dmp-nav__butir.aktif');
    if (pilAktif && isi.scrollWidth > isi.clientWidth) {
        var kotakIsi = isi.getBoundingClientRect(), kotakPil = pilAktif.getBoundingClientRect();
        // scrollIntoView() tidak dipakai: ia juga menggulir HALAMAN.
        isi.scrollLeft += (kotakPil.left + kotakPil.width / 2) - (kotakIsi.left + kotakIsi.width / 2);
    }

    // ---------------------------------------------------------
    // 3. SIDEBAR SEMUA MENU + TAB "BUKA MENU"
    //
    // Seperti Coretax saat membuka sebuah laporan: sidebar tidak
    // memakan lebar halaman, melainkan tersimpan sebagai tab tegak
    // "Buka Menu" di kiri-tengah. Diklik -> sidebar muncul DI ATAS
    // halaman berisi SELURUH menu dan submenu (bukan hanya grup
    // halaman ini), grup halaman aktif sudah terbuka.
    //
    // Menggantikan panel kiri Fase 113 yang selalu terbuka dan
    // menggeser isi 260 px - dashboard lebar jadi sempit karenanya.
    // ---------------------------------------------------------
    (function sampingMenu() {
        var wadah = nav.parentNode;   // .page-wrap
        if (!wadah) { return; }

        var logo = '<span class="ct-logo4" aria-hidden="true"><i></i><i></i><i></i><i></i></span>';

        var samping = d.createElement('aside');
        samping.className = 'ct-samping';
        samping.id = 'ctSamping';
        samping.setAttribute('aria-label', T('semuaMenu', null, 'Semua Menu'));
        samping.setAttribute('aria-hidden', 'true');
        samping.innerHTML =
            '<div class="ct-samping__kepala">' + logo + '<b>' + esc(T('semuaMenu', null, 'Semua Menu')) + '</b>' +
                '<button type="button" class="ct-samping__tutup" title="' + esc(T('tutupMenu', null, 'Tutup menu')) +
                    '" aria-label="' + esc(T('tutupMenu', null, 'Tutup menu')) + '">&laquo;</button>' +
            '</div>' +
            '<div class="ct-samping__cari"><i class="ik ik-search" aria-hidden="true"></i>' +
                '<input type="text" placeholder="' + esc(T('saring', null, 'Saring menu\u2026')) +
                    '" aria-label="' + esc(T('saring', null, 'Saring menu\u2026')) + '" autocomplete="off"></div>' +
            '<nav class="ct-samping__isi"></nav>' +
            '<p class="ct-samping__kosong" hidden>' + esc(T('kosong', null, 'Tidak ada menu yang cocok.')) + '</p>';

        var daftar = samping.querySelector('.ct-samping__isi');
        var grupS  = [];

        function salinTautan(a, kelas) {
            var s = a.cloneNode(true);
            s.removeAttribute('id');
            s.className = kelas
                + (a.classList.contains('ct-anak-ikon') ? ' ct-anak-ikon' : '')
                + (a.classList.contains('aktif') ? ' aktif' : '');
            pasangMuatPadaTautan(s);
            return s;
        }

        // Urutan sama dengan baris menu atas.
        Array.prototype.forEach.call(isi.children, function (el) {
            if (el.hidden) { return; }

            if (el.classList.contains('dmp-nav__grup')) {
                var tbl    = el.querySelector('.dmp-nav__butir--grup');
                var tautan = el.querySelectorAll('.dmp-nav__sub-isi a[href]');
                if (!tbl || !tautan.length) { return; }

                var g = d.createElement('div');
                g.className = 'ct-samping__grup';

                var judul = d.createElement('button');
                judul.type = 'button';
                judul.className = 'ct-samping__judul';
                var ik = tbl.querySelector('i');
                if (ik) { judul.appendChild(ik.cloneNode(true)); }
                var anak = d.createElement('div');
                anak.className = 'ct-samping__anak';
                Array.prototype.forEach.call(tautan, function (a) {
                    if (a.style.display === 'none') { return; }
                    anak.appendChild(salinTautan(a, 'ct-samping__tautan'));
                });

                // Nama grup + "N menu" di bawahnya, seperti kartu
                // kategori Coretax (Fase 113.4). N dihitung dari tautan
                // yang BENAR-BENAR disalin - yang tertahan gerbang
                // dev/prod atau disembunyikan inline tidak ikut terhitung.
                var nm = d.createElement('span');
                nm.className = 'ct-samping__nama';
                var nmTeks = d.createElement('b');
                nmTeks.textContent = teks(tbl);
                var nmJumlah = d.createElement('small');
                nmJumlah.textContent = T('nMenu', { n: anak.children.length }, '{n} menu');
                nm.appendChild(nmTeks);
                nm.appendChild(nmJumlah);
                judul.appendChild(nm);

                var terbuka = tbl.classList.contains('aktif-induk');
                g.classList.toggle('buka', terbuka);
                judul.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
                judul.addEventListener('click', function () {
                    var b = g.classList.toggle('buka');
                    judul.setAttribute('aria-expanded', b ? 'true' : 'false');
                });

                g.appendChild(judul);
                g.appendChild(anak);
                daftar.appendChild(g);
                grupS.push(g);
            } else if (el.matches('a.dmp-nav__butir[href]')) {
                daftar.appendChild(salinTautan(el, 'ct-samping__tunggal'));
            }
        });

        // ----- Menu Rekomendasi (Fase 113.5) -----------------------
        // Menu yang paling sering dibuka SELURUH pengguna, diambil dari
        // window.DMP_CT.menuPopuler (JSON: {ok:true, hari, tampil, menu:[{url, orang}]}).
        // Tidak diisi -> blok ini tidak pernah muncul. Contoh server:
        // menu-populer.php (membaca data/kunjungan.json).
        //
        // Diminta saat sidebar PERTAMA kali dibuka, bukan saat halaman
        // dimuat: kebanyakan kunjungan tidak pernah membuka sidebar, dan
        // tidak perlu membayar satu permintaan untuk itu.
        //
        // Server mengirim 15 kandidat; di sini dicocokkan dengan tautan
        // yang ADA di sidebar pengguna ini. Menu yang tertahan gerbang
        // dev/prod untuknya tidak ada tautannya -> terbuang sendiri.
        // Gagal atau kosong -> bloknya tetap tersembunyi, tanpa pesan.
        var rekom = d.createElement('div');
        rekom.className = 'ct-rekom';
        rekom.hidden = true;
        rekom.innerHTML = '<div class="ct-rekom__judul">' + esc(T('rekom', null, 'Menu Rekomendasi')) + '</div>' +
                          '<small class="ct-rekom__ket"></small><ol class="ct-rekom__daftar"></ol>';
        daftar.insertBefore(rekom, daftar.firstChild);

        var rekomDiminta = false, rekomAda = false;

        function jalurNormal(u) {
            try { return new URL(u, window.location.href).pathname.toLowerCase().replace(/\/+$/, ''); }
            catch (e) { return ''; }
        }

        function muatRekom() {
            var konf = window.DMP_CT || {};
            if (rekomDiminta || !konf.menuPopuler || !window.fetch) { return; }
            rekomDiminta = true;

            fetch(konf.menuPopuler, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            }).then(function (r) {
                return r.ok ? r.json() : null;
            }).then(function (j) {
                if (!j || j.ok !== true || !Array.isArray(j.menu)) { return; }

                var ada = {};
                tautanMenu().forEach(function (a) {
                    if (a.style.display === 'none') { return; }
                    var k = jalurNormal(a.href);
                    if (k && !ada[k]) { ada[k] = a; }
                });

                var ol = rekom.querySelector('.ct-rekom__daftar');
                var batas = j.tampil || 5;
                j.menu.forEach(function (m) {
                    if (ol.children.length >= batas) { return; }
                    var a = ada[jalurNormal(m.url)];
                    if (!a) { return; }
                    var s = salinTautan(a, 'ct-rekom__tautan');
                    Array.prototype.forEach.call(s.querySelectorAll('i'), function (i) { i.remove(); });
                    s.title = T('rekomOrang', { o: m.orang, h: j.hari }, '{o} orang membukanya dalam {h} hari terakhir');
                    var li = d.createElement('li');
                    li.appendChild(s);
                    ol.appendChild(li);
                });

                if (ol.children.length) {
                    rekom.querySelector('.ct-rekom__ket').textContent =
                        T('rekomKet', { h: j.hari }, 'Paling banyak dibuka {h} hari terakhir');
                    rekomAda = true;
                    rekom.hidden = saring.value.trim() !== '';
                }
            }).catch(function () { /* diam - rekomendasi hanya pelengkap */ });
        }

        var tab = d.createElement('button');
        tab.type = 'button';
        tab.className = 'ct-tab-menu';
        tab.id = 'ctTabMenu';
        tab.setAttribute('aria-controls', 'ctSamping');
        tab.setAttribute('aria-expanded', 'false');
        tab.title = T('bukaMenu', null, 'Buka Menu');
        tab.innerHTML = logo + '<span class="ct-tab-menu__teks">' + esc(T('bukaMenu', null, 'Buka Menu')) + '</span>' +
                        '<span class="ct-tab-menu__panah" aria-hidden="true">&rsaquo;</span>';

        wadah.insertBefore(tab, nav.nextSibling);
        wadah.insertBefore(samping, nav.nextSibling);

        var saring = samping.querySelector('input');
        var kosong = samping.querySelector('.ct-samping__kosong');

        aturSamping = function (buka) {
            if (buka === d.body.classList.contains('ct-samping-buka')) { return; }
            d.body.classList.toggle('ct-samping-buka', buka);
            tab.setAttribute('aria-expanded', buka ? 'true' : 'false');
            samping.setAttribute('aria-hidden', buka ? 'false' : 'true');

            if (buka) {
                tutupSemua(null);
                muatRekom();
                // Gulir daftar ke menu halaman ini. scrollIntoView() tidak
                // dipakai: ia ikut menggulir halaman di belakangnya.
                var aktif = daftar.querySelector('.aktif');
                if (aktif) { daftar.scrollTop = aktif.offsetTop - daftar.clientHeight / 2; }
                samping.querySelector('.ct-samping__tutup').focus();
            } else if (samping.contains(d.activeElement)) {
                tab.focus();
            }
        };

        tab.addEventListener('click', function (e) { e.stopPropagation(); aturSamping(true); });
        samping.querySelector('.ct-samping__tutup').addEventListener('click', function () { aturSamping(false); });
        samping.addEventListener('click', function (e) { e.stopPropagation(); });
        d.addEventListener('click', function () { aturSamping(false); });
        d.addEventListener('keydown', function (e) { if (e.key === 'Escape') { aturSamping(false); } });

        // Saring: grup tampil bila judulnya cocok atau ada anak yang
        // cocok; grup yang tampil dipaksa terbuka selama menyaring.
        saring.addEventListener('input', function () {
            var kata = saring.value.toLowerCase().split(/\s+/).filter(Boolean);
            var cocok = function (t) {
                t = t.toLowerCase();
                return kata.every(function (k) { return t.indexOf(k) !== -1; });
            };
            var ada = 0;

            // Rekomendasi disembunyikan selama menyaring - hasil saring
            // di bawahnya sudah menjawab yang dicari.
            rekom.hidden = !rekomAda || kata.length > 0;

            grupS.forEach(function (g) {
                // Hanya nama grup yang dicocokkan, bukan "8 menu" di
                // bawahnya - kalau tidak, kata "menu" cocok ke semua grup.
                var judulCocok = !kata.length || cocok(teksCari(teks(g.querySelector('.ct-samping__nama b'))));
                var n = 0;
                Array.prototype.forEach.call(g.querySelectorAll('.ct-samping__tautan'), function (a) {
                    var c = judulCocok || cocok(teksCari(teks(a)));
                    a.hidden = !c;
                    if (c) { n++; }
                });
                g.hidden = n === 0;
                g.classList.toggle('paksa', kata.length > 0 && n > 0);
                ada += n;
            });
            Array.prototype.forEach.call(daftar.querySelectorAll(':scope > .ct-samping__tunggal'), function (a) {
                var c = !kata.length || cocok(teksCari(teks(a)));
                a.hidden = !c;
                if (c) { ada++; }
            });
            kosong.hidden = ada > 0;
        });
    })();

    // ---------------------------------------------------------
    // 4. PALET PENCARIAN (Ctrl+K)
    // ---------------------------------------------------------
    var tombolCari = d.getElementById('ctCariBuka');
    var palet = null, masukan = null, hasil = null;
    var semua = [], tampil = [], pilih = 0;

    function kumpulkanMenu() {
        semua = [];
        tautanMenu().forEach(function (a) {
            if (a.style.display === 'none') { return; }
            var g = a.closest('.dmp-nav__grup');
            if (g && g.hidden) { return; }
            var tGrup = g ? teks(g.querySelector('.dmp-nav__butir--grup')) : '';
            var ikon  = (g ? g.querySelector('.dmp-nav__butir--grup i') : a.querySelector('i'));
            semua.push({
                nama:  teks(a),
                grup:  tGrup,
                ikon:  ikon ? ikon.className : 'ik ik-file',
                href:  a.href,
                baru:  a.target === '_blank',
                cari:  (teksCari(teks(a)) + ' ' + teksCari(tGrup)).toLowerCase()
            });
        });
    }

    function bangunPalet() {
        palet = d.createElement('div');
        palet.className = 'ct-palet';
        palet.hidden = true;
        palet.setAttribute('role', 'dialog');
        palet.setAttribute('aria-modal', 'true');
        palet.setAttribute('aria-label', T('paletLabel', null, 'Cari menu atau laporan'));
        palet.innerHTML =
            '<div class="ct-palet__kotak">' +
                '<div class="ct-palet__masukan">' +
                    '<i class="ik ik-search" aria-hidden="true"></i>' +
                    '<input type="text" autocomplete="off" spellcheck="false" ' +
                        'placeholder="' + esc(T('paletPh', null, 'Ketik nama menu atau laporan\u2026')) + '" aria-label="' +
                        esc(T('paletLabel', null, 'Cari menu atau laporan')) + '">' +
                    '<kbd>Esc</kbd>' +
                '</div>' +
                '<ul class="ct-palet__hasil" role="listbox"></ul>' +
                '<div class="ct-palet__kaki">' +
                    '<span><kbd>&uarr;</kbd> <kbd>&darr;</kbd> ' + esc(T('pilih', null, 'pilih')) + '</span>' +
                    '<span><kbd>Enter</kbd> ' + esc(T('buka', null, 'buka')) + '</span>' +
                    '<span><kbd>Esc</kbd> ' + esc(T('tutup', null, 'tutup')) + '</span>' +
                '</div>' +
            '</div>';
        d.body.appendChild(palet);

        masukan = palet.querySelector('input');
        hasil   = palet.querySelector('.ct-palet__hasil');

        masukan.addEventListener('input', saring);
        masukan.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); sorot(pilih + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); sorot(pilih - 1); }
            else if (e.key === 'Enter') { e.preventDefault(); buka(tampil[pilih]); }
            else if (e.key === 'Escape') { e.preventDefault(); tutupPalet(); }
        });
        // Klik di luar kotak menutup palet.
        palet.addEventListener('mousedown', function (e) {
            if (e.target === palet) { tutupPalet(); }
        });
    }

    function saring() {
        var kata = masukan.value.toLowerCase().split(/\s+/).filter(Boolean);
        tampil = semua.filter(function (m) {
            return kata.every(function (k) { return m.cari.indexOf(k) !== -1; });
        }).slice(0, 60);

        hasil.innerHTML = '';
        if (!tampil.length) {
            var kosong = d.createElement('li');
            kosong.className = 'ct-palet__kosong';
            kosong.textContent = T('kosong', null, 'Tidak ada menu yang cocok.');
            hasil.appendChild(kosong);
            return;
        }

        tampil.forEach(function (m, i) {
            var li = d.createElement('li');
            li.className = 'ct-palet__butir';
            li.setAttribute('role', 'option');

            var ik = d.createElement('i');
            ik.className = m.ikon;
            ik.setAttribute('aria-hidden', 'true');

            var nm = d.createElement('span');
            nm.textContent = m.nama;

            var gr = d.createElement('small');
            gr.textContent = m.grup;

            li.appendChild(ik);
            li.appendChild(nm);
            li.appendChild(gr);
            li.addEventListener('mousemove', function () { if (pilih !== i) { sorot(i); } });
            li.addEventListener('click', function () { buka(m); });
            hasil.appendChild(li);
        });
        sorot(0);
    }

    function sorot(i) {
        var butir = hasil.querySelectorAll('.ct-palet__butir');
        if (!butir.length) { return; }
        pilih = (i + butir.length) % butir.length;
        Array.prototype.forEach.call(butir, function (b, j) {
            b.classList.toggle('pilih', j === pilih);
            b.setAttribute('aria-selected', j === pilih ? 'true' : 'false');
        });
        if (butir[pilih].scrollIntoView) { butir[pilih].scrollIntoView({ block: 'nearest' }); }
    }

    function buka(m) {
        if (!m) { return; }
        tutupPalet();
        if (m.baru) {
            window.open(m.href, '_blank', 'noopener');
        } else {
            muatMulai();
            window.location.href = m.href;
        }
    }

    function bukaPalet() {
        if (!palet) { bangunPalet(); }
        kumpulkanMenu();
        tutupSemua(null);
        if (aturSamping) { aturSamping(false); }
        palet.hidden = false;
        masukan.value = '';
        saring();
        masukan.focus();
    }

    function tutupPalet() {
        if (palet) { palet.hidden = true; }
        if (tombolCari) { tombolCari.focus(); }
    }

    if (tombolCari) {
        tombolCari.addEventListener('click', function (e) { e.stopPropagation(); bukaPalet(); });
    }

    d.addEventListener('keydown', function (e) {
        if ((e.ctrlKey || e.metaKey) && !e.altKey && (e.key === 'k' || e.key === 'K')) {
            e.preventDefault();
            if (palet && !palet.hidden) { tutupPalet(); } else { bukaPalet(); }
        }
    });
})();

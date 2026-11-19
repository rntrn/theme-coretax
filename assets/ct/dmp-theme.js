/* =============================================================
 * dmp-theme.js — pemilih tema
 * Fase 1.4 · Dibuat 2026-07-31
 *
 * CARA KERJA
 * ----------
 * Mengganti dua atribut pada tag <html>:
 *     data-dmp-theme  8 preset warna
 *     data-dmp-mode   light | dark | auto
 *
 * Warna berubah SEKETIKA karena seluruh tampilan memakai variabel CSS.
 * Tidak ada berkas CSS yang dimuat ulang, tidak ada halaman yang
 * disegarkan.
 *
 * Penyimpanan ke server dilakukan SETELAH warna berubah, di latar
 * belakang. Kalau gagal, pengguna tidak diganggu - warnanya sudah
 * berubah, hanya belum terbawa ke komputer lain. Cukup dicatat di
 * Console.
 *
 * KENAPA localStorage JUGA DIPAKAI
 * --------------------------------
 * Server mengirim tema yang benar di dalam HTML, jadi tidak ada
 * kedipan. localStorage hanya cadangan bila permintaan penyimpanan
 * gagal, agar pilihan tetap bertahan sampai koneksi pulih.
 * ============================================================= */

(function () {
    'use strict';

    var html = document.documentElement;

    // Diisi dari view lewat window.DMP_TEMA (URL simpan + token CSRF)
    var konfig = window.DMP_TEMA || {};

    // ---------------------------------------------------------
    // Menerapkan tema ke halaman
    // ---------------------------------------------------------

    function terapkan(tema, mode, palet) {
        if (tema)  { html.setAttribute('data-dmp-theme', tema); }
        if (mode)  { html.setAttribute('data-dmp-mode', modeNyata(mode)); }
        if (palet) { html.setAttribute('data-dmp-palette', palet); }
        tandaiTombol(tema, mode, palet);
    }

    function paletKini() {
        return konfig.palet || html.getAttribute('data-dmp-palette') || 'bawaan';
    }

    /**
     * 'auto' berarti mengikuti pengaturan terang/gelap sistem operasi.
     * CSS hanya mengenal light dan dark, jadi 'auto' diterjemahkan.
     */
    function modeNyata(mode) {
        if (mode !== 'auto') { return mode; }

        var gelap = window.matchMedia &&
                    window.matchMedia('(prefers-color-scheme: dark)').matches;

        return gelap ? 'dark' : 'light';
    }

    function tandaiTombol(tema, mode, palet) {
        var i, t;

        var warna = document.querySelectorAll('.dmp-tema__sw');
        for (i = 0; i < warna.length; i++) {
            t = warna[i].getAttribute('data-tema');
            warna[i].setAttribute('aria-pressed', t === tema ? 'true' : 'false');
        }

        var tombolMode = document.querySelectorAll('.dmp-tema__mode button');
        for (i = 0; i < tombolMode.length; i++) {
            t = tombolMode[i].getAttribute('data-mode');
            tombolMode[i].setAttribute('aria-pressed', t === mode ? 'true' : 'false');
        }

        if (palet) {
            var baris = document.querySelectorAll('.dmp-palet__baris');
            for (i = 0; i < baris.length; i++) {
                t = baris[i].getAttribute('data-palet');
                baris[i].setAttribute('aria-pressed', t === palet ? 'true' : 'false');
            }
        }
    }

    // ---------------------------------------------------------
    // Menyimpan ke server
    // ---------------------------------------------------------

    function simpan(tema, mode, palet) {
        palet = palet || paletKini();
        konfig.mode  = mode;
        konfig.palet = palet;

        // Cadangan lokal dulu, supaya pilihan tidak hilang bila
        // permintaan ke server gagal.
        try {
            window.localStorage.setItem('dmp_tema', tema);
            window.localStorage.setItem('dmp_mode', mode);
            window.localStorage.setItem('dmp_palet', palet);
        } catch (e) {
            // Mode penyamaran atau penyimpanan penuh. Bukan masalah.
        }

        if (!konfig.url || !window.fetch) { return; }

        var data = new FormData();
        data.append('tema', tema);
        data.append('mode', mode);
        data.append('palet', palet);

        // CodeIgniter menolak POST tanpa token CSRF bila filter csrf aktif
        if (konfig.csrfNama && konfig.csrfNilai) {
            data.append(konfig.csrfNama, konfig.csrfNilai);
        }

        fetch(konfig.url, {
            method: 'POST',
            body: data,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                if (!j.ok) {
                    console.warn('[dmp-tema] tidak tersimpan:', j.pesan);
                }
                // Token CSRF berganti setiap permintaan. Tanpa
                // memperbaruinya, klik kedua akan ditolak.
                if (j.csrf) { konfig.csrfNilai = j.csrf; }
            })
            .catch(function (e) {
                console.warn('[dmp-tema] gagal menghubungi server:', e);
            });
    }

    // ---------------------------------------------------------
    // Memasang
    // ---------------------------------------------------------

    function pasang() {
        var i;

        var temaKini = html.getAttribute('data-dmp-theme') || 'biru';
        var modeKini = konfig.mode || html.getAttribute('data-dmp-mode') || 'light';

        tandaiTombol(temaKini, modeKini, paletKini());


        // Klik warna aksen
        var warna = document.querySelectorAll('.dmp-tema__sw');
        for (i = 0; i < warna.length; i++) {
            warna[i].addEventListener('click', function () {
                var t = this.getAttribute('data-tema');
                var m = html.getAttribute('data-dmp-mode') || 'light';
                terapkan(t, m, paletKini());
                simpan(t, m, paletKini());

            });
        }

        // Klik terang / gelap
        var tombolMode = document.querySelectorAll('.dmp-tema__mode button');
        for (i = 0; i < tombolMode.length; i++) {
            tombolMode[i].addEventListener('click', function () {
                var m = this.getAttribute('data-mode');
                var t = html.getAttribute('data-dmp-theme') || 'biru';
                terapkan(t, m, paletKini());
                simpan(t, m, paletKini());

            });
        }

        // Klik palet grafik
        var barisPalet = document.querySelectorAll('.dmp-palet__baris');
        for (i = 0; i < barisPalet.length; i++) {
            barisPalet[i].addEventListener('click', function () {
                var p = this.getAttribute('data-palet');
                var t = html.getAttribute('data-dmp-theme') || 'biru';
                var m = html.getAttribute('data-dmp-mode') || 'light';
                terapkan(t, m, p);
                simpan(t, m, p);
            });
        }

        // Bila pengguna memilih 'auto', ikuti perubahan pengaturan
        // sistem tanpa perlu memuat ulang halaman.
        if (window.matchMedia) {
            var kueri = window.matchMedia('(prefers-color-scheme: dark)');
            var dengar = function () {
                if ((konfig.mode || '') === 'auto') {
                    html.setAttribute('data-dmp-mode', modeNyata('auto'));
                }
            };
            if (kueri.addEventListener) {
                kueri.addEventListener('change', dengar);
            } else if (kueri.addListener) {
                kueri.addListener(dengar);   // Safari lama
            }
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', pasang);
    } else {
        pasang();
    }
})();

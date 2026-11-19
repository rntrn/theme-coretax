/* =============================================================
 * ct-app.js — perilaku kecil yang di DMP dikerjakan ThemeKit theme.js
 *
 * APA
 *   1. Tombol layar penuh (#navbar-fullscreen)
 *   2. Tombol cetak (#navbar-print)
 *   3. Kotak saring tabel: <input data-ct-saring="#idTabel">
 *   4. Salin teks: <button data-ct-salin="teks yang disalin">
 *
 * KENAPA TIDAK MEMAKAI theme.js ThemeKit
 *   theme.js membawa perilaku sidebar lama (nav-collapsed, perfect-
 *   scrollbar, screenfull) yang di pola Coretax sudah diambil alih
 *   coretax.js. Memuat keduanya = dua skrip mengatur sidebar yang sama.
 *   Yang benar-benar masih dipakai hanya dua tombol - ditulis ulang di
 *   sini dengan Fullscreen API bawaan peramban, tanpa pustaka.
 *
 * Tanpa jQuery, supaya bisa dipakai juga di tata letak admin.
 * ============================================================= */
(function () {
    'use strict';
    var d = document;

    // 1. Layar penuh -------------------------------------------------
    var penuh = d.getElementById('navbar-fullscreen');
    if (penuh && d.documentElement.requestFullscreen) {
        penuh.addEventListener('click', function () {
            if (d.fullscreenElement) { d.exitFullscreen(); } else { d.documentElement.requestFullscreen(); }
        });
        d.addEventListener('fullscreenchange', function () {
            var i = penuh.querySelector('i');
            if (i) { i.className = 'ik ' + (d.fullscreenElement ? 'ik-minimize' : 'ik-maximize'); }
        });
    } else if (penuh) {
        penuh.hidden = true;   // peramban tanpa Fullscreen API: jangan tampilkan tombol mati
    }

    // 2. Cetak -------------------------------------------------------
    var cetak = d.getElementById('navbar-print');
    if (cetak) { cetak.addEventListener('click', function () { window.print(); }); }

    // 3. Saring tabel ------------------------------------------------
    // Menyembunyikan baris <tbody> yang tidak memuat SEMUA kata yang
    // diketik (urutan kata bebas: "budi jakarta" = "jakarta budi").
    Array.prototype.forEach.call(d.querySelectorAll('[data-ct-saring]'), function (masukan) {
        var tabel = d.querySelector(masukan.getAttribute('data-ct-saring'));
        if (!tabel) { return; }
        var info = masukan.getAttribute('data-ct-info') ? d.querySelector(masukan.getAttribute('data-ct-info')) : null;
        masukan.addEventListener('input', function () {
            var kata = masukan.value.toLowerCase().split(/\s+/).filter(Boolean);
            var tampil = 0, baris = tabel.tBodies[0] ? tabel.tBodies[0].rows : [];
            Array.prototype.forEach.call(baris, function (tr) {
                var t = tr.textContent.toLowerCase();
                var cocok = kata.every(function (k) { return t.indexOf(k) !== -1; });
                tr.hidden = !cocok;
                if (cocok) { tampil++; }
            });
            if (info) { info.textContent = tampil + ' dari ' + baris.length + ' baris'; }
        });
    });

    // 4. Salin -------------------------------------------------------
    d.addEventListener('click', function (e) {
        var b = e.target.closest ? e.target.closest('[data-ct-salin]') : null;
        if (!b || !navigator.clipboard) { return; }
        navigator.clipboard.writeText(b.getAttribute('data-ct-salin')).then(function () {
            var lama = b.getAttribute('title') || '';
            b.setAttribute('title', 'Tersalin!');
            b.classList.add('dmp-salin--ok');
            setTimeout(function () { b.setAttribute('title', lama); b.classList.remove('dmp-salin--ok'); }, 1200);
        });
    });
})();

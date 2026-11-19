<?php
/**
 * inc/sidebar.php — merender config/menu.php menjadi <aside id="dmpSidebar">.
 *
 * APA
 *   Markup di sini SAMA PERSIS dengan sidebar DMP (kelas dmp-nav__*).
 *   coretax.js membacanya dan membangun baris menu mendatar, laci
 *   "Buka Menu", dan palet Ctrl+K. Aside-nya sendiri disembunyikan CSS
 *   coretax - ia "sumber", bukan tampilan.
 *
 * KENAPA TETAP DIRENDER LENGKAP WALAU DISEMBUNYIKAN
 *   Tanpa JavaScript (atau bila coretax.js gagal dimuat), CSS lama
 *   theme.css masih menampilkan aside ini sebagai sidebar kiri biasa.
 *   Menu tetap bisa dipakai - tidak ada halaman yang buntu.
 *
 * Skrip sebaris di bawah (buka-tutup grup, tanda aktif, ciut, saring)
 * disalin dari DMP. Ia berjalan SEBELUM coretax.js (yang defer),
 * dan coretax.js memakai hasil penandaan .aktif/.aktif-induk-nya.
 */
$ctMenu = require CT_AKAR . '/config/menu.php';
$ctKamus = [];
$p = ct_pengguna() ?? ['nama' => 'TAMU', 'id' => '-', 'peran' => ''];

/** Satu tautan: tunggal (dmp-nav__butir) atau anak (dmp-nav__anak). */
$ctTautan = static function (array $b, bool $anak) use (&$ctKamus): string {
    if (! empty($b['en'])) {
        $ctKamus[$b['teks']] = [$b['en'], $b['teks']];
    }
    $kelas  = $anak ? 'dmp-nav__anak' : 'dmp-nav__butir';
    $target = ! empty($b['target']) ? ' target="' . e($b['target']) . '" rel="noopener"' : '';
    $ikon   = (! $anak && ! empty($b['ikon'])) ? '<i class="ik ' . e($b['ikon']) . '"></i>' : '';
    $teks   = $anak ? e($b['teks']) : '<span>' . e($b['teks']) . '</span>';
    $lencana = '';
    if (! empty($b['lencana'])) {
        [$isi, $jenis] = array_pad((array) $b['lencana'], 2, 'netral');
        $lencana = ' <span class="dmp-badge dmp-badge--' . e($jenis) . '" style="margin-left:6px">' . e($isi) . '</span>';
    }
    return '<a href="' . e(ct_url($b['url'] ?? '#')) . '" class="' . $kelas . '"' . $target . '>' . $ikon . $teks . $lencana . "</a>\n";
};
?>
<aside class="app-sidebar dmp-nav" id="dmpSidebar" data-ciut="0">
    <div class="dmp-nav__kepala">
        <a class="dmp-nav__merek" href="<?= e(ct_url('dashboard.php')) ?>">
            <span class="dmp-nav__logo"><img src="<?= e(ct_aset(ct('logo'))) ?>" alt="" width="32" height="32"></span>
            <span class="dmp-nav__merek-teks">
                <b><?= e(ct('singkat')) ?></b>
                <small><?= e(ct('subjudul')) ?></small>
            </span>
        </a>
        <button type="button" class="dmp-nav__ciut" id="dmpNavCiut" title="Ciutkan menu" aria-label="Ciutkan menu">
            <i class="ik ik-chevrons-left"></i>
        </button>
    </div>

    <div class="dmp-nav__cari">
        <i class="ik ik-search"></i>
        <input type="text" id="dmpNavCari" placeholder="Cari menu&hellip;" autocomplete="off" aria-label="Cari menu">
    </div>

    <nav class="dmp-nav__isi" id="dmpNavIsi">
<?php foreach ($ctMenu as $b) :
    if (! ct_boleh($b['peran'] ?? [])) { continue; }

    if (isset($b['label'])) :
        if (! empty($b['en'])) { $ctKamus[$b['label']] = [$b['en'], $b['label']]; }
        $kumpul = ! empty($b['kumpul']) ? ' data-ct-kumpul="' . e($b['label']) . '"' : ''; ?>
        <div class="dmp-nav__label"<?= $kumpul ?>><?= e($b['label']) ?></div>
<?php
    elseif (isset($b['anak'])) :
        $anak = array_filter($b['anak'], static fn ($a) => ct_boleh($a['peran'] ?? []));
        if ($anak === []) { continue; }   // grup tanpa anak yang boleh dilihat: jangan tampil kosong
        if (! empty($b['en'])) { $ctKamus[$b['teks']] = [$b['en'], $b['teks']]; } ?>
        <div class="dmp-nav__grup">
            <button type="button" class="dmp-nav__butir dmp-nav__butir--grup">
                <i class="ik <?= e($b['ikon'] ?? 'ik-folder') ?>"></i><span><?= e($b['teks']) ?></span>
            </button>
            <div class="dmp-nav__sub"><div class="dmp-nav__sub-isi">
                <?php foreach ($anak as $a) { echo $ctTautan($a, true); } ?>
            </div></div>
        </div>
<?php
    else :
        echo '        ' . $ctTautan($b, false);
    endif;
endforeach; ?>
        <p class="dmp-nav__kosong" id="dmpNavKosong" hidden>Tidak ada menu yang cocok.</p>
    </nav>

    <div class="dmp-nav__kaki">
        <div class="dmp-nav__user">
            <span class="dmp-nav__avatar"><?= e(mb_substr($p['nama'], 0, 1)) ?></span>
            <span class="dmp-nav__user-teks">
                <b><?= e($p['nama']) ?></b>
                <small><?= e(ct('peran')[$p['peran']] ?? $p['peran']) ?> &middot; <?= e($p['id']) ?></small>
            </span>
        </div>
        <a href="<?= e(ct_url('logout.php')) ?>" class="dmp-nav__keluar" title="Keluar">
            <i class="ik ik-power"></i><span>Keluar</span>
        </a>
    </div>
</aside>

<script>
// Kamus terjemahan menu dari config/menu.php (kunci 'en'), dibaca
// coretax-bahasa.js. Bentuk: { 'Teks Indonesia': ['English', 'Jawa'] }.
window.CT_KAMUS = <?= json_encode($ctKamus, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

(function () {
    'use strict';
    var sb = document.getElementById('dmpSidebar'), isi = document.getElementById('dmpNavIsi');
    var cari = document.getElementById('dmpNavCari'), ciut = document.getElementById('dmpNavCiut');
    var kosong = document.getElementById('dmpNavKosong');
    if (!sb || !isi) { return; }

    // ---------- Buka-tutup submenu ----------
    var grup = isi.querySelectorAll('.dmp-nav__grup');
    Array.prototype.forEach.call(grup, function (g) {
        var tbl = g.querySelector('.dmp-nav__butir--grup');
        if (!tbl) { return; }
        tbl.addEventListener('click', function () {
            var terbuka = g.classList.toggle('buka');
            tbl.setAttribute('aria-expanded', terbuka ? 'true' : 'false');
        });
    });

    // ---------- Tandai menu aktif dari alamat ----------
    // Dicocokkan dari yang TERPANJANG lebih dulu, supaya "admin/pintu"
    // tidak kalah oleh "admin". Bagian #... dan ?... diabaikan.
    (function () {
        var kini = window.location.pathname.toLowerCase().replace(/\/+$/, '');
        var juara = null, panjang = -1;
        Array.prototype.forEach.call(isi.querySelectorAll('a[href]'), function (a) {
            var h;
            try { h = new URL(a.getAttribute('href'), window.location.href).pathname; } catch (e) { return; }
            h = h.toLowerCase().replace(/\/+$/, '');
            if (h && h.length > panjang && (kini === h || kini.indexOf(h + '/') === 0)) { juara = a; panjang = h.length; }
        });
        if (juara) {
            juara.classList.add('aktif');
            var g = juara.closest('.dmp-nav__grup');
            if (g) {
                g.classList.add('buka');
                var t = g.querySelector('.dmp-nav__butir--grup');
                if (t) { t.classList.add('aktif-induk'); }
            }
        }
    })();

    // ---------- Ciutkan (hanya berarti bila coretax.js tidak berjalan) ----------
    function terapkanCiut(nilai) {
        sb.setAttribute('data-ciut', nilai ? '1' : '0');
        document.body.classList.toggle('dmp-nav-ciut', !!nilai);
        try { localStorage.setItem('dmp_nav_ciut', nilai ? '1' : '0'); } catch (e) {}
    }
    if (ciut) { ciut.addEventListener('click', function () { terapkanCiut(sb.getAttribute('data-ciut') !== '1'); }); }
    try { if (localStorage.getItem('dmp_nav_ciut') === '1') { terapkanCiut(true); } } catch (e) {}

    // ---------- Pencarian di sidebar ----------
    if (cari) {
        cari.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var semua = isi.querySelectorAll('.dmp-nav__butir, .dmp-nav__anak, .dmp-nav__grup, .dmp-nav__label');
            if (!q) {
                Array.prototype.forEach.call(semua, function (el) { el.hidden = false; });
                Array.prototype.forEach.call(grup, function (g) { g.classList.remove('paksa-buka'); });
                if (kosong) { kosong.hidden = true; }
                return;
            }
            var ketemu = 0;
            Array.prototype.forEach.call(isi.querySelectorAll(':scope > .dmp-nav__butir'), function (a) {
                var c = a.textContent.toLowerCase().indexOf(q) !== -1;
                a.hidden = !c; if (c) { ketemu++; }
            });
            Array.prototype.forEach.call(grup, function (g) {
                var judul = g.querySelector('.dmp-nav__butir--grup');
                var cj = judul && judul.textContent.toLowerCase().indexOf(q) !== -1, n = 0;
                Array.prototype.forEach.call(g.querySelectorAll('.dmp-nav__anak'), function (a) {
                    var c = cj || a.textContent.toLowerCase().indexOf(q) !== -1;
                    a.hidden = !c; if (c) { n++; }
                });
                g.hidden = !(cj || n); g.classList.toggle('paksa-buka', !!(cj || n)); ketemu += n;
            });
            Array.prototype.forEach.call(isi.querySelectorAll('.dmp-nav__label'), function (l) { l.hidden = true; });
            if (kosong) { kosong.hidden = ketemu > 0; }
        });
        cari.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { this.value = ''; this.dispatchEvent(new Event('input')); }
        });
    }
})();
</script>

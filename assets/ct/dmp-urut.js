/*
 * dmp-urut.js — pengurut kolom untuk tabel di Menu Admin.
 *
 * APA
 * Klik judul kolom untuk mengurutkan: naik -> turun -> urutan asli.
 * Tidak ada pustaka, tidak ada jQuery, tidak ada permintaan ke server.
 *
 * KENAPA BEGINI
 * Halaman admin memuat tabel yang panjang (daftar tabel ClickHouse,
 * catatan query, pengguna) tanpa satu pun cara mengurutkannya —
 * app/Views/admin/layout.php bahkan tidak memuat <script> sama sekali.
 * Menambah jQuery + tablesorter ke sana berarti dua berkas ~100 KB
 * untuk pekerjaan yang muat dalam satu berkas kecil.
 *
 * KENAPA BUKAN CARA LAIN
 * - Bukan tablesorter yang sudah dipakai halaman dashboard: ia butuh
 *   jQuery, dan tiap tabel harus didaftarkan satu per satu lewat
 *   $('#id').tablesorter(). Di sini tabelnya banyak dan sebagian tidak
 *   punya id.
 *   Berkas ini TIDAK menyentuh halaman dashboard sama sekali; kedua
 *   mekanisme tidak pernah bertemu.
 * - Bukan DataTables: ia mengganti seluruh tampilan tabel (kotak cari,
 *   penomoran halaman, gaya sendiri) — perubahan tampilan yang tidak
 *   diminta.
 * - Bukan mengurutkan di server: seluruh isi tabel ini sudah ada di
 *   layar. Bolak-balik ke server untuk mengurutkan yang sudah di tangan
 *   berarti satu query Oracle lagi untuk pekerjaan yang selesai dalam
 *   satu milidetik di peramban.
 *
 * URUTAN ASLI IKUT DISIMPAN, dan itu bukan kemewahan: banyak tabel di
 * sini sudah diurutkan server dengan alasan (mis. query terlambat lebih
 * dulu). Sekali diurutkan pengguna, urutan itu hilang selamanya kalau
 * tidak disimpan — dan satu-satunya jalan kembali adalah memuat ulang
 * halaman.
 *
 * Dipasang: Fase 67 (2026-09-10).
 */
(function () {
    'use strict';

    /* ----------------------------------------------------------------
     * Tabel yang SENGAJA dilewati.
     *
     * Tabel yang sudah punya pengurut lain tidak boleh dipasangi yang
     * kedua: dua mekanisme yang menyusun ulang <tbody> yang sama akan
     * saling menimpa, dan gejalanya (baris ganda, urutan yang tidak
     * masuk akal) sangat sulit dilacak balik ke sebabnya.
     * ---------------------------------------------------------------- */
    var KELAS_PUNYA_PENGURUT = ['tablesorter', 'dataTable', 'mantine-Table-root'];

    function bolehDiurut(tabel) {
        if (tabel.getAttribute('data-urut') === 'tidak') {
            return false;
        }

        for (var i = 0; i < KELAS_PUNYA_PENGURUT.length; i++) {
            if (tabel.classList.contains(KELAS_PUNYA_PENGURUT[i])) {
                return false;
            }
        }

        var thead = tabel.tHead;
        var tbody = tabel.tBodies[0];

        if (!thead || !tbody || thead.rows.length !== 1) {
            // Tanpa thead tidak ada yang bisa diklik. Header BERTINGKAT
            // (thead lebih dari satu baris) dilewati dengan sengaja:
            // kolom yang terlihat tidak selalu sejajar dengan sel di
            // baris header mana pun, dan tombol urut yang mengurutkan
            // kolom yang salah lebih buruk daripada tidak ada tombol.
            return false;
        }

        var sel = thead.rows[0].cells;

        for (var j = 0; j < sel.length; j++) {
            if (sel[j].colSpan > 1 || sel[j].rowSpan > 1) {
                return false;
            }
        }

        // Satu baris tidak perlu diurutkan.
        return tbody.rows.length > 1;
    }

    /* ----------------------------------------------------------------
     * Membaca nilai sebuah sel.
     *
     * data-urut-nilai menang atas apa pun yang tertulis. Itu jalan
     * keluar untuk kolom yang tampilannya bukan nilainya — lencana
     * "3 hari lalu", ukuran "2,2 GB", atau tanggal yang ditulis dengan
     * nama bulan.
     * ---------------------------------------------------------------- */
    function nilaiSel(sel) {
        if (sel === undefined || sel === null) {
            return '';
        }

        var eksplisit = sel.getAttribute('data-urut-nilai');

        return eksplisit !== null ? eksplisit : sel.textContent.trim();
    }

    /*
     * SATU pengertian "kosong" untuk seluruh berkas.
     *
     * Ditemukan oleh uji otomatis: kolom angka yang memakai "—" sebagai
     * penanda tidak-ada terbaca sebagai KOLOM TEKS, karena "—" dihitung
     * sebagai isi tapi bukan angka sehingga rasio angkanya jatuh di
     * bawah ambang. Akibatnya 9 diurutkan sesudah 101.578.549.
     *
     * Kalau pengertian ini ditulis dua kali - sekali di tipeKolom(),
     * sekali di pembanding - cepat atau lambat keduanya berbeda, dan
     * gejalanya persis seperti bug tadi: urutan yang salah tanpa galat.
     */
    var KOSONG = ['', '-', '–', '—', 'n/a', 'N/A'];

    function kosong(teks) {
        return KOSONG.indexOf(teks) !== -1;
    }

    /*
     * Angka format Indonesia: titik ribuan, koma desimal.
     *
     * parseFloat('1.234,56') menghasilkan 1.234 — salah, dan salahnya
     * diam-diam. Karena itu bentuknya diperiksa dulu dengan pola yang
     * ketat, baru dibersihkan.
     */
    var POLA_ANGKA = /^-?\s*(?:Rp\s*)?\d{1,3}(?:\.\d{3})*(?:,\d+)?\s*%?$/;
    var POLA_ANGKA_POLOS = /^-?\d+(?:\.\d+)?$/;

    function keAngka(teks) {
        if (kosong(teks)) {
            return null;
        }

        if (POLA_ANGKA.test(teks)) {
            return parseFloat(teks.replace(/[^\d,\-]/g, '').replace(',', '.'));
        }

        if (POLA_ANGKA_POLOS.test(teks)) {
            return parseFloat(teks);
        }

        return null;
    }

    /* Tanggal yang dipakai aplikasi ini: 2026-09-10 [HH:MM[:SS]] dan 10-09-2026. */
    var POLA_ISO = /^(\d{4})-(\d{2})-(\d{2})(?:[ T](\d{2}):(\d{2})(?::(\d{2}))?)?$/;
    var POLA_LOKAL = /^(\d{2})[-/](\d{2})[-/](\d{4})(?:\s+(\d{2}):(\d{2})(?::(\d{2}))?)?$/;

    function keTanggal(teks) {
        var m = POLA_ISO.exec(teks);

        if (m) {
            return Number(m[1] + m[2] + m[3] + (m[4] || '00') + (m[5] || '00') + (m[6] || '00'));
        }

        m = POLA_LOKAL.exec(teks);

        if (m) {
            return Number(m[3] + m[2] + m[1] + (m[4] || '00') + (m[5] || '00') + (m[6] || '00'));
        }

        return null;
    }

    /*
     * Tipe kolom ditentukan dari ISINYA, bukan dari nama judulnya.
     *
     * Diperiksa paling banyak 50 baris pertama: tabel di sini bisa
     * ratusan baris, dan lima puluh sudah cukup untuk membedakan kolom
     * angka dari kolom teks. Sel kosong tidak dihitung — kolom yang
     * separuhnya "—" tetap kolom angka.
     */
    function tipeKolom(baris, indeks) {
        var angka = 0;
        var tanggal = 0;
        var isi = 0;
        var batas = Math.min(baris.length, 50);

        for (var i = 0; i < batas; i++) {
            var teks = nilaiSel(baris[i].cells[indeks]);

            if (kosong(teks)) {
                continue;
            }

            isi++;

            if (keTanggal(teks) !== null) {
                tanggal++;
            } else if (keAngka(teks) !== null) {
                angka++;
            }
        }

        if (isi === 0) {
            return 'teks';
        }

        // Ambang 80%, bukan 100%: satu sel ganjil di tengah kolom angka
        // tidak boleh mengubah seluruh kolom jadi teks — kalau itu
        // terjadi, 9 diurutkan sesudah 10. Penanda tidak-ada ("—",
        // "n/a") sudah tidak ikut dihitung sama sekali di atas.
        if (tanggal / isi >= 0.8) {
            return 'tanggal';
        }

        if (angka / isi >= 0.8) {
            return 'angka';
        }

        return 'teks';
    }

    /* Pembanding teks yang paham huruf Indonesia dan angka di dalam teks. */
    var pembandingTeks = new Intl.Collator('id', { numeric: true, sensitivity: 'base' });

    function urutkan(tabel, indeks, arah) {
        var tbody = tabel.tBodies[0];
        var semua = Array.prototype.slice.call(tbody.rows);

        // Baris bertanda data-urut="lewat" TIDAK ikut diurutkan dan
        // tetap di posisi paling bawah. Ini untuk baris TOTAL: sebuah
        // jumlah yang menyelip di tengah daftar bukan cuma jelek, ia
        // terbaca sebagai salah satu data.
        var ikut = [];
        var tetap = [];

        semua.forEach(function (tr) {
            if (tr.getAttribute('data-urut') === 'lewat') {
                tetap.push(tr);
            } else {
                ikut.push(tr);
            }
        });

        if (arah === 0) {
            // Kembali ke urutan asli - dipulihkan dari nomor yang
            // disimpan saat pemasangan.
            ikut.sort(function (a, b) {
                return Number(a.dataset.urutAsli) - Number(b.dataset.urutAsli);
            });
        } else {
            var tipe = tipeKolom(ikut, indeks);

            ikut.sort(function (a, b) {
                var ta = nilaiSel(a.cells[indeks]);
                var tb = nilaiSel(b.cells[indeks]);

                // Sel kosong SELALU di bawah, arah apa pun. Kalau ikut
                // dibalik, mengurutkan turun berarti melihat sederet sel
                // kosong lebih dulu - dan itu bukan yang dicari orang.
                var ka = kosong(ta);
                var kb = kosong(tb);

                if (ka && kb) { return 0; }
                if (ka) { return 1; }
                if (kb) { return -1; }

                var hasil;

                if (tipe === 'tanggal') {
                    hasil = (keTanggal(ta) || 0) - (keTanggal(tb) || 0);
                } else if (tipe === 'angka') {
                    hasil = (keAngka(ta) || 0) - (keAngka(tb) || 0);
                } else {
                    hasil = pembandingTeks.compare(ta, tb);
                }

                return arah === 1 ? hasil : -hasil;
            });
        }

        // Satu kali tempel, bukan appendChild per baris: DocumentFragment
        // menyusun ulang di luar dokumen sehingga peramban menghitung
        // tata letak sekali saja, bukan sekali per baris.
        var kantong = document.createDocumentFragment();

        ikut.forEach(function (tr) { kantong.appendChild(tr); });
        tetap.forEach(function (tr) { kantong.appendChild(tr); });

        tbody.appendChild(kantong);
    }

    function pasang(tabel) {
        var thead = tabel.tHead;
        var tbody = tabel.tBodies[0];

        Array.prototype.slice.call(tbody.rows).forEach(function (tr, i) {
            tr.dataset.urutAsli = String(i);
        });

        var kepala = Array.prototype.slice.call(thead.rows[0].cells);
        var aktif = -1;
        var arah = 0;   // 0 = asli, 1 = naik, -1 = turun

        kepala.forEach(function (th, indeks) {
            if (th.getAttribute('data-urut') === 'tidak' || th.textContent.trim() === '') {
                return;
            }

            th.classList.add('dmp-urut__kepala');
            th.setAttribute('role', 'button');
            th.setAttribute('tabindex', '0');
            th.setAttribute('aria-sort', 'none');
            th.title = 'Klik untuk mengurutkan';

            // Panah dipasang sebagai elemen sendiri, bukan ::after dari
            // CSS: berkas ini harus bisa dipasang tanpa menyunting CSS
            // tema sama sekali.
            var panah = document.createElement('span');
            panah.className = 'dmp-urut__panah';
            panah.setAttribute('aria-hidden', 'true');
            panah.style.cssText = 'margin-left:.35em; opacity:.35; font-size:.85em';
            panah.textContent = '↕';
            th.appendChild(panah);

            function klik() {
                if (aktif === indeks) {
                    arah = arah === 1 ? -1 : (arah === -1 ? 0 : 1);
                } else {
                    aktif = indeks;
                    arah = 1;
                }

                urutkan(tabel, indeks, arah);

                kepala.forEach(function (lain) {
                    var p = lain.querySelector('.dmp-urut__panah');

                    if (!p) { return; }

                    if (lain === th && arah !== 0) {
                        p.textContent = arah === 1 ? '▲' : '▼';
                        p.style.opacity = '1';
                        th.setAttribute('aria-sort', arah === 1 ? 'ascending' : 'descending');
                    } else {
                        p.textContent = '↕';
                        p.style.opacity = '.35';
                        lain.setAttribute('aria-sort', 'none');
                    }
                });

                if (arah === 0) {
                    aktif = -1;
                }
            }

            th.addEventListener('click', klik);
            th.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    klik();
                }
            });
        });

        tabel.classList.add('dmp-urut-siap');
    }

    function pasangSemua() {
        Array.prototype.slice.call(document.querySelectorAll('table')).forEach(function (tabel) {
            if (tabel.classList.contains('dmp-urut-siap')) {
                return;
            }

            if (bolehDiurut(tabel)) {
                pasang(tabel);
            }
        });
    }

    // Gaya minimum disuntik dari sini, bukan dari CSS tema: berkas ini
    // harus bisa dipasang/dicabut dengan satu baris <script> saja.
    var gaya = document.createElement('style');
    gaya.textContent =
        '.dmp-urut__kepala{cursor:pointer;user-select:none}' +
        '.dmp-urut__kepala:hover{background:rgba(127,127,127,.10)}' +
        '.dmp-urut__kepala:focus-visible{outline:2px solid currentColor;outline-offset:-2px}';
    document.head.appendChild(gaya);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', pasangSemua);
    } else {
        pasangSemua();
    }

    // Dibuka supaya halaman yang menambah baris/tabel sesudah muat
    // (mis. hasil AJAX) bisa memasangnya lagi tanpa memuat ulang.
    window.dmpUrutPasang = pasangSemua;
}());

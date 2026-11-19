/* =============================================================
 * dmp-echart.js — pembungkus bersama untuk ECharts
 * Fase 5.1 · Dibuat 2026-07-31
 *
 * KENAPA ADA PEMBUNGKUS
 * ---------------------
 * Tanpa ini, setiap grafik ditulis dengan gayanya sendiri: warna
 * berbeda, format angka berbeda, penanganan data kosong berbeda -
 * atau tidak ditangani sama sekali. Dengan 54 grafik yang akan
 * dimigrasikan, itu berarti 54 gaya yang harus dirawat satu per satu.
 *
 * Semua grafik DMP harus lewat berkas ini. Jangan memanggil
 * echarts.init() langsung di view.
 *
 * YANG DIURUS DI SINI
 *   - Warna mengikuti tema aplikasi, dan ikut berubah saat pengguna
 *     mengganti preset - tanpa memuat ulang halaman
 *   - Format angka Indonesia (ribuan titik, desimal koma)
 *   - Singkatan Rb / Jt / M / T pada label sumbu dan tooltip
 *   - Keadaan "sedang memuat" dan "tidak ada data"
 *   - Ukuran menyesuaikan saat jendela atau sidebar berubah
 *   - Menghormati "kurangi gerakan" pada pengaturan sistem
 *
 * CARA PAKAI
 *
 *   var g = DmpChart.buat('idWadah', {
 *       tooltip: { trigger: 'item' },
 *       series : [{ type: 'pie', data: [...] }]
 *   });
 *
 *   DmpChart.muat('idWadah');                  // animasi memuat
 *   DmpChart.perbarui('idWadah', opsiBaru);    // ganti data
 *   DmpChart.kosong('idWadah', 'Tidak ada data untuk filter ini');
 *
 * CATATAN SATUAN
 * Aplikasi lama memakai R/J/M/T pada label sumbu Highcharts. Di sini
 * dipakai Rb/Jt/M/T agar sama persis dengan dmp_angka_helper.php di
 * sisi PHP - satu konvensi untuk seluruh aplikasi. Bila ingin kembali
 * ke gaya lama, ubah larik SATUAN di bawah.
 * ============================================================= */

window.DmpChart = (function () {
    'use strict';

    var daftar = {};   // idWadah -> instance echarts

    // ---------------------------------------------------------
    // Angka
    // ---------------------------------------------------------

    var SATUAN = [
        [1e12, ' T'],
        [1e9,  ' M'],
        [1e6,  ' Jt'],
    ];

    /** 1234567.89 -> "1.234.567,89" */
    function angka(n, desimal) {
        if (n === null || n === undefined || isNaN(n)) { return '-'; }
        desimal = (desimal === undefined) ? 0 : desimal;

        var bagian = Number(n).toFixed(desimal).split('.');
        bagian[0] = bagian[0].replace(/\B(?=(\d{3})+(?!\d))/g, '.');

        return bagian.length > 1 ? bagian[0] + ',' + bagian[1] : bagian[0];
    }

    /**
     * 479511892602758 -> "479,51 T"
     *
     * Penyingkatan dimulai dari JUTA, bukan ribu - sama seperti sisi
     * PHP. Menyingkat 1.157 menjadi "1,16 Rb" justru lebih panjang dan
     * kehilangan ketepatan.
     */
    function ringkas(n, desimal) {
        if (n === null || n === undefined || isNaN(n)) { return '-'; }
        desimal = (desimal === undefined) ? 2 : desimal;

        var tanda = n < 0 ? '-' : '';
        var a = Math.abs(Number(n));

        for (var i = 0; i < SATUAN.length; i++) {
            if (a >= SATUAN[i][0]) {
                return tanda + angka(a / SATUAN[i][0], desimal) + SATUAN[i][1];
            }
        }

        return tanda + angka(a, Number.isInteger(a) ? 0 : desimal);
    }

    function persen(n, desimal) {
        if (n === null || n === undefined || isNaN(n)) { return '-'; }
        return angka(n, desimal === undefined ? 2 : desimal) + '%';
    }

    // ---------------------------------------------------------
    // Warna dari tema aplikasi
    // ---------------------------------------------------------

    /**
     * Membaca nilai variabel CSS yang sedang berlaku.
     * Inilah yang membuat grafik ikut berubah saat pengguna mengganti
     * preset warna - nilainya dibaca ulang, bukan ditulis mati.
     */
    function css(nama, cadangan) {
        try {
            var v = getComputedStyle(document.documentElement)
                        .getPropertyValue(nama).trim();

            if (!v) { return cadangan; }

            // PENTING - variabel CSS TIDAK diselesaikan oleh getPropertyValue.
            //
            // tokens.css berisi:  --dmp-chart-1: var(--dmp-accent);
            // getPropertyValue mengembalikan teks "var(--dmp-accent)",
            // BUKAN "#2563eb". ECharts menerima nilai tak sah lalu diam-diam
            // memakai warna bawaannya sendiri - itu sebabnya tema tidak
            // berpengaruh pada grafik.
            //
            // Penyelesaiannya lewat elemen uji: browser yang menghitung
            // rantai var() sampai tuntas.
            if (v.indexOf('var(') !== -1) { return resolusiWarna(nama, cadangan); }

            return v;
        } catch (e) {
            return cadangan;
        }
    }

    /**
     * Menyelesaikan rantai var() dengan meminta browser menghitungnya.
     * Hanya untuk nilai warna - dikembalikan dalam bentuk rgb().
     */
    function resolusiWarna(nama, cadangan) {
        try {
            var uji = document.createElement('span');
            uji.style.cssText = 'position:absolute;left:-9999px;top:-9999px;' +
                                'width:0;height:0;color:var(' + nama + ');';
            document.body.appendChild(uji);

            var warna = getComputedStyle(uji).color;
            document.body.removeChild(uji);

            // Bila var() tetap tak terselesaikan, browser memberi warna
            // bawaan elemen - biasanya hitam pekat. Anggap gagal.
            if (!warna || warna === 'rgba(0, 0, 0, 0)') { return cadangan; }

            return warna;
        } catch (e) {
            return cadangan;
        }
    }

    function palet() {
        return [
            css('--dmp-chart-1', '#2563eb'),
            css('--dmp-chart-2', '#f59e0b'),
            css('--dmp-chart-3', '#10b981'),
            css('--dmp-chart-4', '#8b5cf6'),
            css('--dmp-chart-5', '#ef4444'),
            css('--dmp-chart-6', '#06b6d4'),
            css('--dmp-chart-7', '#ec4899'),
            css('--dmp-chart-8', '#84cc16')
        ];
    }

    function gelap() {
        return document.documentElement.getAttribute('data-dmp-mode') === 'dark';
    }

    function kurangiGerakan() {
        return window.matchMedia &&
               window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    /** Opsi dasar yang ditumpuk di bawah opsi dari pemanggil. */
    function dasar() {
        var teks  = css('--dmp-chart-label', gelap() ? '#b8c2ce' : '#4b5563');
        var sumbu = css('--dmp-chart-axis',  gelap() ? '#7d8794' : '#97a1ae');
        var garis = css('--dmp-chart-grid',  gelap() ? '#262d36' : '#eaeef2');

        return {
            color: palet(),
            animation: !kurangiGerakan(),
            animationDuration: 420,
            textStyle: {
                fontFamily: css('--dmp-font', 'Nunito Sans, sans-serif'),
                color: teks
            },
            tooltip: {
                backgroundColor: gelap() ? '#1d222a' : '#ffffff',
                borderColor: css('--dmp-border', '#e2e6eb'),
                borderWidth: 1,
                textStyle: { color: css('--dmp-text', gelap() ? '#e9eef4' : '#131820'), fontSize: 12 },
                extraCssText: 'box-shadow:0 4px 16px rgba(0,0,0,.14); border-radius:8px;'
            },
            legend: {
                textStyle: { color: teks, fontSize: 11 },
                itemWidth: 12,
                itemHeight: 12,
                icon: 'roundRect'
            },
            grid: { left: 8, right: 16, bottom: 8, top: 32, containLabel: true },
            categoryAxis: {
                axisLine:  { lineStyle: { color: sumbu } },
                axisTick:  { show: false },
                axisLabel: { color: sumbu, fontSize: 11 },
                splitLine: { show: false }
            },
            valueAxis: {
                axisLine:  { show: false },
                axisTick:  { show: false },
                axisLabel: { color: sumbu, fontSize: 11 },
                splitLine: { lineStyle: { color: garis, type: 'dashed' } }
            }
        };
    }

    // ---------------------------------------------------------
    // Membuat dan memperbarui
    // ---------------------------------------------------------

    function elemen(id) {
        return (typeof id === 'string') ? document.getElementById(id) : id;
    }

    /**
     * Membuat grafik. Bila wadah dengan id itu sudah punya grafik,
     * yang lama dibuang dulu supaya tidak ada dua grafik menumpuk -
     * penyebab kebocoran memori yang lazim pada halaman dengan filter.
     */
    function buat(id, opsi, pilihan) {
        var el = elemen(id);
        if (!el) {
            console.warn('[DmpChart] wadah tidak ditemukan:', id);
            return null;
        }
        if (typeof echarts === 'undefined') {
            console.error('[DmpChart] pustaka echarts belum dimuat. ' +
                          'Sertakan public/theme/plugins/echarts/echarts.min.js');
            return null;
        }

        var kunci = el.id || id;
        if (daftar[kunci]) {
            daftar[kunci].dispose();
            delete daftar[kunci];
        }

        // =============================================================
        // TINGGI WADAH — PENYEBAB "GRAFIK TIDAK MUNCUL" YANG PALING
        // SULIT DITEBAK
        //
        // Highcharts memberi tinggi bawaan 400px pada wadahnya sendiri.
        // ECharts TIDAK: ia memakai tinggi elemen apa adanya. Wadah
        // <div id="x"></div> tanpa tinggi menghasilkan kanvas setinggi
        // NOL - tidak ada galat, tidak ada peringatan, hanya kotak
        // kosong.
        //
        // Itu terjadi di KAP KJS: kedua wadahnya ditulis tanpa gaya
        // apa pun, sementara wadah di Kanwil punya style="height:400px"
        // yang diwarisi dari kode lama. Gejalanya sama persis dengan
        // "pustaka gagal dimuat" dan "data kosong" - tiga sebab
        // berbeda, satu tampilan.
        //
        // Tinggi cadangan diberikan HANYA bila wadahnya benar-benar
        // nol. Wadah yang sudah punya tinggi tidak disentuh, jadi
        // tata letak yang sudah diatur tidak berubah.
        // =============================================================
        if (el.clientHeight < 20) {
            el.style.minHeight = '320px';
            console.info('[DmpChart] wadah "' + kunci + '" tidak punya tinggi; '
                       + 'dipakai 320px. Beri class="dmp-chart" atau tinggi '
                       + 'sendiri agar tata letaknya disengaja.');
        }

        // pilihan.renderer 'svg' (Fase 112.27): untuk halaman yang DICETAK.
        // Canvas dicetak sebagai gambar seresolusi layar (buram di PDF);
        // SVG tetap tajam. Bawaannya tetap canvas - lebih ringan di dashboard.
        var g = echarts.init(el, null, { renderer: (pilihan && pilihan.renderer === 'svg') ? 'svg' : 'canvas' });
        daftar[kunci] = g;

        g.setOption(gabung(dasar(), opsi || {}));

        // Simpan opsi asli agar bisa digambar ulang saat tema berubah
        g.__dmpOpsi = opsi;

        if (!pilihan || pilihan.pantauUkuran !== false) { pantau(g, el); }

        // Tombol perbesar. Dipasang otomatis - grafik di dalam kartu
        // dashboard hampir selalu terlalu sempit untuk label lengkap.
        if (!pilihan || pilihan.alat !== false) {
            pasangAlat(el, kunci, (pilihan && pilihan.judul) || judulKartu(el));
        }

        return g;
    }

    /**
     * Menebak judul grafik dari kepala kartu tempat ia berada, supaya
     * jendela besar tidak perlu diberi judul satu per satu.
     */
    function judulKartu(el) {
        var kartu = el.closest ? el.closest('.card') : null;
        if (!kartu) { return 'Grafik'; }

        var j = kartu.querySelector('.card-title, .card-header h5, .card-header h3');
        return j ? j.textContent.trim() : 'Grafik';
    }

    function perbarui(id, opsi, gantiTotal) {
        var g = daftar[elemen(id) ? (elemen(id).id || id) : id];
        if (!g) { return buat(id, opsi); }

        g.hideLoading();
        g.__dmpOpsi = opsi;
        g.setOption(gabung(dasar(), opsi || {}), gantiTotal === true);

        return g;
    }

    /** Penggabungan dangkal - cukup, karena opsi ECharts satu tingkat. */
    function gabung(a, b) {
        var hasil = {}, k;
        for (k in a) { if (Object.prototype.hasOwnProperty.call(a, k)) { hasil[k] = a[k]; } }
        for (k in b) { if (Object.prototype.hasOwnProperty.call(b, k)) { hasil[k] = b[k]; } }
        return hasil;
    }

    // ---------------------------------------------------------
    // Keadaan memuat dan data kosong
    // ---------------------------------------------------------

    function muat(id, pesan) {
        var el = elemen(id);
        if (!el) { return; }

        var kunci = el.id || id;
        var g = daftar[kunci] || (typeof echarts !== 'undefined'
                                  ? (daftar[kunci] = echarts.init(el)) : null);
        if (!g) { return; }

        g.showLoading({
            text: pesan || 'Memuat data...',
            color: css('--dmp-accent', '#2563eb'),
            textColor: css('--dmp-text-muted', '#5a6472'),
            maskColor: gelap() ? 'rgba(22,26,32,.65)' : 'rgba(255,255,255,.7)',
            fontSize: 12,
            spinnerRadius: 12,
            lineWidth: 2
        });
    }

    /**
     * Menampilkan keadaan "tidak ada data".
     *
     * Grafik kosong tanpa keterangan adalah penyebab keluhan "aplikasi
     * rusak" yang paling sering - padahal filternya saja yang tidak
     * menghasilkan baris.
     */
    function kosong(id, pesan) {
        var el = elemen(id);
        if (!el) { return; }

        var kunci = el.id || id;
        if (daftar[kunci]) {
            daftar[kunci].dispose();
            delete daftar[kunci];
        }

        el.innerHTML =
            '<div class="dmp-kosong">' +
                '<div class="dmp-kosong__judul">Tidak ada data</div>' +
                '<div class="dmp-kosong__pesan">' +
                    (pesan || 'Tidak ada data untuk pilihan filter saat ini. ' +
                              'Coba ubah periode atau unit kerja.') +
                '</div>' +
            '</div>';
    }

    /**
     * Apakah data benar-benar kosong? Dipakai sebelum menggambar.
     *
     * MENGENALI EMPAT BENTUK, bukan satu.
     *
     * Versi pertama hanya membaca kunci `value`. Data warisan Highcharts
     * di modul Rapor memakai `y` (dan `x` untuk scatter), sehingga
     * seluruh angkanya terbaca sebagai nol - grafik yang datanya lengkap
     * dinyatakan "Tidak ada data", DAN tabel di bawahnya ikut dikosongkan
     * karena disinkronkan dari sini.
     *
     * Gejalanya menyesatkan: baris Total di kaki tabel tetap menampilkan
     * angka yang benar, karena itu dirender di sisi server dan tidak
     * lewat jalur ini sama sekali. Jadi terlihat seolah datanya ada
     * separuh.
     *
     * Bentuk yang dikenali:
     *   42                      angka telanjang
     *   { value: 42 }           bentuk ECharts
     *   { y: 42 }               bentuk warisan Highcharts
     *   { value: [x, y] }       scatter
     */
    function adaData(data) {
        if (!data) { return false; }
        if (!data.length) { return false; }

        var jumlah = 0;

        function tambah(v) {
            if (typeof v === 'number' && !isNaN(v)) { jumlah += Math.abs(v); }
        }

        for (var i = 0; i < data.length; i++) {
            var d = data[i];

            if (d === null || d === undefined) { continue; }

            if (typeof d === 'number') { tambah(d); continue; }

            if (Array.isArray(d.value)) {
                for (var k = 0; k < d.value.length; k++) { tambah(d.value[k]); }
                continue;
            }

            if (d.value !== undefined) { tambah(d.value); continue; }

            // Bentuk warisan: y, atau x+y untuk scatter
            if (d.y !== undefined) { tambah(d.y); }
            if (d.x !== undefined) { tambah(d.x); }
        }

        return jumlah > 0;
    }

    // ---------------------------------------------------------
    // Ukuran dan pergantian tema
    // ---------------------------------------------------------

    function pantau(g, el) {
        // Jendela diubah ukurannya
        var tunda;
        window.addEventListener('resize', function () {
            clearTimeout(tunda);
            tunda = setTimeout(function () { try { g.resize(); } catch (e) {} }, 120);
        });

        // Sidebar diciutkan, tab dibuka - lebar wadah berubah tanpa
        // jendela ikut berubah. ResizeObserver menangkap ini.
        if (window.ResizeObserver) {
            try {
                new ResizeObserver(function () {
                    try { g.resize(); } catch (e) {}
                }).observe(el);
            } catch (e) {}
        }
    }

    /**
     * Menggambar ulang semua grafik saat pengguna mengganti tema.
     * Warna diambil ulang dari variabel CSS yang baru.
     */
    function pantauTema() {
        if (!window.MutationObserver) { return; }

        var tunda;
        new MutationObserver(function (perubahan) {
            var relevan = perubahan.some(function (p) {
                return p.attributeName === 'data-dmp-theme' ||
                       p.attributeName === 'data-dmp-mode';
            });
            if (!relevan) { return; }

            clearTimeout(tunda);
            tunda = setTimeout(function () {
                for (var k in daftar) {
                    if (!Object.prototype.hasOwnProperty.call(daftar, k)) { continue; }
                    try {
                        daftar[k].setOption(gabung(dasar(), daftar[k].__dmpOpsi || {}), true);
                    } catch (e) {}
                }
            }, 60);
        }).observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-dmp-theme', 'data-dmp-mode']
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', pantauTema);
    } else {
        pantauTema();
    }

    // ---------------------------------------------------------
    // Tombol perbesar + jendela besar
    //
    // Grafik di dalam kartu dashboard kerap terlalu sempit: label
    // bertumpuk, dan pada grafik lingkaran nama irisan tenggelam saat
    // diklik. Tombol ini membuka salinan grafik yang sama di jendela
    // besar, tempat semuanya muat.
    //
    // Grafik aslinya TIDAK dipindah - yang dibuat adalah instance baru
    // dengan opsi yang sama. Menutup jendela membuangnya kembali.
    // ---------------------------------------------------------

    var jendela = null;   // instance echarts di dalam jendela besar

    function pasangAlat(el, id, judul) {
        if (el.querySelector('.dmp-chart__alat')) { return; }

        var alat = document.createElement('div');
        alat.className = 'dmp-chart__alat';

        var tbl = document.createElement('button');
        tbl.type = 'button';
        tbl.className = 'dmp-chart__perbesar';
        tbl.title = 'Perbesar grafik';
        tbl.setAttribute('aria-label', 'Perbesar grafik');
        tbl.innerHTML = '<i class="ik ik-maximize-2"></i>';
        tbl.onclick = function (e) {
            e.stopPropagation();
            perbesar(id, judul);
        };

        alat.appendChild(tbl);
        el.appendChild(alat);

        // Wadah harus position:relative agar tombolnya menempel di
        // pojok grafik, bukan di pojok halaman.
        if (getComputedStyle(el).position === 'static') {
            el.style.position = 'relative';
        }
    }

    function bangunJendela() {
        if (document.getElementById('dmpChartJendela')) { return; }

        var d = document.createElement('div');
        d.id = 'dmpChartJendela';
        d.className = 'dmp-chart-jendela';
        d.setAttribute('aria-hidden', 'true');
        d.setAttribute('role', 'dialog');
        d.setAttribute('aria-modal', 'true');
        d.innerHTML =
            '<div class="dmp-chart-jendela__kotak">' +
                '<div class="dmp-chart-jendela__kepala">' +
                    '<h4 class="dmp-chart-jendela__judul"></h4>' +
                    '<button type="button" class="dmp-chart-jendela__tutup" aria-label="Tutup">&times;</button>' +
                '</div>' +
                '<div class="dmp-chart-jendela__isi"></div>' +
            '</div>';

        document.body.appendChild(d);

        d.querySelector('.dmp-chart-jendela__tutup').onclick = tutupJendela;
        d.onclick = function (e) { if (e.target === d) { tutupJendela(); } };

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') { tutupJendela(); }
        });
    }

    function perbesar(id, judul) {
        var asal = daftar[id];
        if (!asal) { return; }

        bangunJendela();

        var d   = document.getElementById('dmpChartJendela');
        var isi = d.querySelector('.dmp-chart-jendela__isi');

        d.querySelector('.dmp-chart-jendela__judul').textContent = judul || 'Grafik';
        d.setAttribute('aria-hidden', 'false');
        document.body.style.overflow = 'hidden';   // cegah halaman ikut tergulir

        if (jendela) { jendela.dispose(); jendela = null; }

        // Digambar setelah jendela tampil, supaya ECharts mengukur
        // wadah pada ukuran akhirnya - bukan nol.
        setTimeout(function () {
            jendela = echarts.init(isi, null, { renderer: 'canvas' });

            var opsi = gabung(dasar(), asal.__dmpOpsi || {});

            // Di ruang yang lega, keterangan dan label boleh ditampilkan
            // seluruhnya - itulah gunanya diperbesar.
            opsi.legend = gabung(opsi.legend || {}, {
                show: true, type: 'scroll', bottom: 0, itemGap: 14
            });
            opsi.grid = gabung(opsi.grid || {}, { bottom: 42 });

            jendela.setOption(opsi);
            jendela.resize();
        }, 60);
    }

    function tutupJendela() {
        var d = document.getElementById('dmpChartJendela');
        if (!d) { return; }

        d.setAttribute('aria-hidden', 'true');
        document.body.style.overflow = '';

        if (jendela) { jendela.dispose(); jendela = null; }
    }

    // ---------------------------------------------------------


    // =================================================================
    // DRILLDOWN — pengganti fitur bawaan Highcharts
    //
    // ECharts tidak punya drilldown. Highcharts punya, dan modul Rapor
    // memakainya empat tingkat: Kanwil > KPP > Seksi > Pegawai.
    //
    // Dibuat sebagai penolong BERSAMA, bukan disalin ke tiga view.
    // Rumus yang disalin membawa bug yang disalin juga - itu sudah
    // terbukti dua kali di proyek ini (lihat docs/91 bagian 7).
    //
    // BENTUK DATA
    // Sama persis dengan yang sudah disiapkan controller untuk
    // Highcharts, jadi TIDAK ADA perubahan di sisi PHP:
    //
    //   akar   : [{ name, y|value, drilldown?, ...field lain }]
    //
    // bentuk() dan saatGanti() menerima (data, judul, tingkat, induk).
    // 'induk' adalah titik yang diklik untuk masuk ke tingkat ini,
    // atau null di tingkat akar.
    //   cabang : [{ id, name, data: [ ...sama seperti akar... } ]
    //
    // Titik yang punya 'drilldown' dapat diklik; nilainya cocok dengan
    // 'id' salah satu cabang.
    //
    // KENAPA ADA REMAH JEJAK (BREADCRUMB)
    // Highcharts hanya menyediakan tombol "Back" satu tingkat. Pada
    // empat tingkat, pengguna yang sudah di Pegawai harus menekan tiga
    // kali dan tidak tahu sedang berada di mana. Remah jejak
    // menunjukkan posisi DAN memungkinkan lompat langsung ke tingkat
    // mana pun.
    //
    // Ditambahkan pada: Fase 9.3b
    // =================================================================
    function drill(id, opsi) {
        var akar    = opsi.akar || [];
        var cabang  = opsi.cabang || [];
        var bentuk  = opsi.bentuk;                 // fn(data, judul) -> option ECharts
        var namaAkar = opsi.namaAkar || 'Nasional';
        var saatGanti = opsi.saatGanti || function () {};

        // Cabang dicari lewat peta, bukan dengan menyusuri larik setiap
        // kali diklik. Pada Rapor jumlah cabangnya ratusan.
        var peta = {};
        for (var i = 0; i < cabang.length; i++) {
            peta[String(cabang[i].id)] = cabang[i];
        }

        // Tumpukan posisi. Indeks 0 selalu akar.
        // Setiap entri menyimpan juga TITIK INDUK - titik yang diklik
        // untuk masuk ke tingkat ini. Rapor WP Bayar memerlukannya:
        // garis bantu kuadrannya berpusat pada nilai induk, bukan pada
        // rata-rata nasional, begitu pengguna turun satu tingkat.
        var jejak = [{ nama: namaAkar, data: akar, induk: null }];

        var wadah = document.getElementById(id);
        if (!wadah) { return null; }

        // Baris remah jejak disisipkan TEPAT DI ATAS wadah grafik,
        // bukan di dalamnya - ECharts menguasai isi wadahnya sendiri
        // dan akan menghapus apa pun yang ditaruh di sana.
        var bar = document.createElement('div');
        bar.className = 'dmp-drill';
        wadah.parentNode.insertBefore(bar, wadah);

        var ec = null;

        function gambarBar() {
            bar.innerHTML = '';
            for (var i = 0; i < jejak.length; i++) {
                if (i > 0) {
                    var sep = document.createElement('span');
                    sep.className = 'dmp-drill__pisah';
                    sep.textContent = '›';
                    bar.appendChild(sep);
                }
                var t = document.createElement(i === jejak.length - 1 ? 'span' : 'button');
                t.className = 'dmp-drill__butir'
                            + (i === jejak.length - 1 ? ' dmp-drill__butir--kini' : '');
                t.textContent = jejak[i].nama;
                if (i !== jejak.length - 1) {
                    t.type = 'button';
                    (function (ke) {
                        t.addEventListener('click', function () { naikKe(ke); });
                    })(i);
                }
                bar.appendChild(t);
            }
            // Pada tingkat akar tidak ada yang perlu ditampilkan.
            bar.style.display = jejak.length > 1 ? '' : 'none';
        }

        function tampilkan() {
            var kini = jejak[jejak.length - 1];
            var opt  = bentuk(kini.data, kini.nama, jejak.length - 1, kini.induk);
            ec = buat(id, opt);
            pasangKlik();
            gambarBar();
            saatGanti(kini.data, kini.nama, jejak.length - 1, kini.induk);
        }

        function pasangKlik() {
            if (!ec) { return; }
            ec.off('click');
            ec.on('click', function (p) {
                var d = p.data;
                if (!d || d.drilldown === undefined || d.drilldown === null) { return; }
                var anak = peta[String(d.drilldown)];
                // Titik menyebut cabang yang tidak ada. Terjadi bila
                // data tingkat bawah kosong untuk filter tertentu.
                // Didiamkan - lebih baik tidak terjadi apa-apa daripada
                // grafik kosong tanpa jalan kembali.
                if (!anak || !anak.data || !anak.data.length) { return; }
                jejak.push({ nama: d.name || anak.name, data: anak.data, induk: d });
                tampilkan();
            });
        }

        function naikKe(tingkat) {
            if (tingkat < 0 || tingkat >= jejak.length - 1) { return; }
            jejak.length = tingkat + 1;
            tampilkan();
        }

        tampilkan();

        return {
            instance: function () { return ec; },
            tingkat:  function () { return jejak.length - 1; },
            akar:     function () { naikKe(0); }
        };
    }

    return {
        buat:     buat,
        drill:    drill,
        perbarui: perbarui,
        muat:     muat,
        kosong:   kosong,
        adaData:  adaData,
        angka:    angka,
        ringkas:  ringkas,
        persen:   persen,
        perbesar: perbesar,
        tutup:    tutupJendela,
        instance: function (id) { return daftar[id] || null; },
        semua:    function () { return daftar; }
    };
})();

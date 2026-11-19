# Panduan Tema Coretax Starter

Panduan ini ditulis untuk programmer yang baru memakai tema ini. Isinya:
cara memasang, cara kerja tata letak, dan langkah demi langkah membuat
aplikasi baru di atasnya. Berkas yang sama tampil di menu
**Alat Bantu > Dokumentasi** di dalam aplikasi.

---

## 1. Apa ini

Kerangka aplikasi (seperti AdminLTE) dengan tampilan gaya **Coretax**:

- header navy dengan logo, judul, kotak **Cari Menu atau Laporan** (Ctrl+K), pilihan bahasa, terang/gelap, dan pengguna
- **baris menu mendatar**: klik judul grup untuk membuka panel "Jelajahi ..."
- tab **Buka Menu** di sisi kiri: laci berisi seluruh menu, menu terakhir dibuka, dan menu rekomendasi
- **halaman login** yang terhubung dengan saklar **Pintu Masuk** (login mandiri dan masuk lewat aplikasi induk)
- **tata letak admin** terpisah dengan sidebar kiri
- halaman contoh: dashboard, grafik ECharts, komponen, formulir, tabel, halaman kosong, profil, 404

Semuanya PHP polos (8.1+), tanpa composer dan tanpa framework. CSS dan
JavaScript temanya disalin utuh dari aplikasi asalnya, jadi tampilannya sama persis.

---

## 2. Memasang

### Dengan XAMPP / Apache

1. Salin folder ini ke `htdocs`, misalnya `htdocs/aplikasi-saya/`.
2. Pastikan folder `data/` bisa ditulis oleh server web (di Windows biasanya sudah).
3. Buka `http://localhost/aplikasi-saya/` di peramban.
4. Masuk dengan akun demo `admin` / `admin` atau `user` / `user`.

### Tanpa Apache (server bawaan PHP)

```
cd aplikasi-saya
php -S 127.0.0.1:8080
```

Lalu buka `http://127.0.0.1:8080/`.

### Tanpa PHP sama sekali

Buka `html/index.html`. Ini versi statis yang dibuat `tools/ekspor-html.php`:
tampilan dan menu berfungsi, tetapi login dan penyimpanan tidak.

> Sebelum dipakai sungguhan, matikan mode demo: `'demo' => false` di
> `config/app.php`, lalu sambungkan login asli di `inc/auth.php`
> (lihat bagian 7). Selama mode demo menyala, pita merah **MODE DEMO**
> tampil di setiap halaman.

---

## 3. Isi folder

| Lokasi | Isi |
|---|---|
| `config/app.php` | nama aplikasi, logo, tema bawaan, akun demo, pintu masuk |
| `config/menu.php` | **satu-satunya** daftar menu |
| `config/menu-admin.php` | menu area admin |
| `inc/init.php` | dimuat pertama oleh setiap halaman: sesi, fungsi bantu `ct_*` |
| `inc/awal.php` + `inc/akhir.php` | tata letak utama (header, menu, footer, skrip) |
| `inc/admin-awal.php` + `inc/admin-akhir.php` | tata letak admin |
| `inc/sidebar.php` | merender `config/menu.php` menjadi markup menu |
| `inc/auth.php` | **tempat menyambungkan login asli** dan SSO |
| `inc/pintu.php` | aturan Pintu Masuk |
| `assets/ct/` | CSS & JS tema (lihat bagian 10) |
| `assets/vendor/` | Bootstrap 4.6, jQuery 3.3, iconkit, ThemeKit, ECharts |
| `admin/` | halaman admin: beranda, Pintu Masuk, Pengaturan Tema |
| `data/` | berkas JSON runtime (saklar pintu, catatan login). Tidak ikut git |
| `docs/` | dokumentasi |
| `html/` | hasil ekspor HTML statis |
| `tools/` | alat baris perintah: ekspor HTML, uji |

---

## 4. Cara kerja tata letak

Ini bagian terpenting untuk dipahami.

**Menu hanya ditulis sekali**, di `config/menu.php`. `inc/sidebar.php`
merendernya menjadi `<aside id="dmpSidebar">` dengan kelas `dmp-nav__*`.
Aside itu disembunyikan CSS - ia hanya **sumber**. Begitu halaman siap,
`assets/ct/coretax.js` menyalin isinya menjadi tiga tampilan:

1. baris menu mendatar di bawah header
2. laci **Buka Menu** di sisi kiri
3. palet pencarian **Ctrl+K**

Akibatnya:

- menambah menu = menambah satu baris di `config/menu.php`, ketiga tampilan ikut berubah
- menu yang disaring karena peran tidak dirender, jadi tidak muncul di mana pun, termasuk di pencarian
- bila JavaScript gagal, aside tampil sebagai sidebar kiri biasa: menu tetap bisa dipakai

Struktur HTML setiap halaman:

```
html.ct
└─ body
   ├─ #ctMuat                      layar "Memuat halaman"
   └─ .wrapper
      ├─ header.header-top.ct-kepala
      └─ .page-wrap
         ├─ aside#dmpSidebar.dmp-nav     sumber menu (disembunyikan)
         ├─ .main-content > .container-fluid   ISI HALAMAN
         └─ footer.dmp-footer
```

Jangan mengganti nama kelas atau id di atas: `coretax.css` dan `coretax.js`
bergantung padanya.

---

## 5. Membuat halaman baru

1. Salin `kosong.php` menjadi, misalnya, `laporan.php`.
2. Ubah `$judul`, `$subjudul`, dan `$remah` (remah roti).
3. Tulis isi di antara `require CT_INC . '/awal.php';` dan bagian skrip.
4. Daftarkan di `config/menu.php`.

Kerangka lengkap sebuah halaman:

```
<?php
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();                    // wajib login
// ct_wajib_peran('admin');          // atau: hanya peran tertentu

$judul        = 'Laporan Bulanan';
$subjudul     = 'Kalimat penjelas singkat';
$remah        = [['Dashboard', 'dashboard.php'], ['Laporan Bulanan']];
$pakai_grafik = true;                // hanya bila memakai ECharts
require CT_INC . '/awal.php';
?>
<div class="card">
    <div class="card-header"><h3>Judul Kartu</h3></div>
    <div class="card-body"> ... </div>
</div>
<?php ob_start(); ?>
<script>
    // skrip khusus halaman - jQuery dan DmpChart sudah tersedia
</script>
<?php $skrip = ob_get_clean(); require CT_INC . '/akhir.php'; ?>
```

Kenapa skrip ditulis lewat `ob_start()`? Skrip halaman harus dijalankan
**sesudah** jQuery dan ECharts dimuat, yaitu di dalam `inc/akhir.php`.
`ob_start()` menampung teksnya dulu, lalu `akhir.php` mencetaknya di tempat yang benar.

Untuk halaman admin, pakai `inc/admin-awal.php` dan `inc/admin-akhir.php`
(contoh: `admin/index.php`), lalu daftarkan di `config/menu-admin.php`.

---

## 6. Menu

### Bentuk butir

```
['label' => 'Laporan'],                                  // judul kelompok
['label' => 'Alat Bantu', 'kumpul' => true],             // tautan tunggal sesudahnya digabung jadi satu grup

[                                                        // grup dengan submenu
    'teks' => 'Dashboard', 'ikon' => 'ik-bar-chart-2', 'en' => 'Dashboard',
    'anak' => [
        ['teks' => 'Ringkasan', 'url' => 'dashboard.php', 'en' => 'Overview'],
        ['teks' => 'Grafik',    'url' => 'grafik.php', 'lencana' => ['BARU', 'sukses']],
    ],
],

['teks' => 'Cari Data', 'ikon' => 'ik-search', 'url' => 'cari.php'],   // tautan tunggal
```

| Kunci | Arti |
|---|---|
| `teks` | nama menu |
| `url` | relatif ke folder tema (`laporan.php`), atau alamat lengkap `https://...` |
| `ikon` | kelas iconkit, mis. `ik-grid`. Daftar lengkap di **Antarmuka > Ikon** |
| `anak` | daftar submenu. Grup tanpa anak yang boleh dilihat tidak ditampilkan |
| `lencana` | label kecil: `['BARU', 'sukses']`; jenis: `sukses`, `awas`, `bahaya`, `netral` |
| `target` | `'_blank'` untuk membuka di tab baru |
| `peran` | `['admin']`: hanya tampil untuk peran itu |
| `en` | terjemahan bahasa Inggris untuk tombol bahasa di header |

### Kenapa hanya dua tingkat

Panel "Jelajahi" menampilkan anak sebuah grup sebagai kartu sejajar,
dan palet Ctrl+K menampilkan "Grup > Menu". Tingkat ketiga tidak punya
tempat di keduanya. Bila menu terlalu banyak, pecah menjadi dua grup.

### Menu aktif

Menu yang sedang dibuka ditandai otomatis dari alamat halaman, dengan
mencocokkan alamat **terpanjang** lebih dulu. Tidak perlu menulis kelas `active`.

### Menyembunyikan bukan pengaman

`'peran' => ['admin']` hanya menyembunyikan tautan. Halamannya sendiri
**wajib** dijaga dengan `ct_wajib_peran('admin')` di baris atas berkasnya.
Tanpa itu, orang yang tahu alamatnya tetap bisa membuka.

---

## 7. Login, peran, dan Pintu Masuk

### Menyambungkan login asli

Buka `inc/auth.php`. Ada dua fungsi:

| Fungsi | Dipanggil oleh | Tugas |
|---|---|---|
| `ct_autentikasi($id, $sandi)` | `login.php` | periksa nama pengguna + kata sandi (LDAP / database) |
| `ct_sso_verifikasi($token)` | `sso.php` | periksa token dari aplikasi induk |

Keduanya mengembalikan `['id' => ..., 'nama' => ..., 'peran' => ...]` atau `null`.
Isi contoh database (PDO + `password_verify`) dan JWT sudah tertulis di
komentar fungsi masing-masing. Sesudah tersambung, set `'demo' => false`.

### Peran

Daftar peran di `config/app.php` (`'peran'`). Pakai di dua tempat:

- menu: `'peran' => ['admin']`
- halaman: `ct_wajib_peran('admin')` — pengguna lain mendapat halaman 403

### Pintu Masuk

Menu **Admin > Pintu Masuk** membuka dan menutup dua jalan masuk:

- **Login mandiri**: formulir di `login.php`
- **Masuk lewat aplikasi induk** (namanya diatur `'sso_nama'`, bawaan "Coretax")

Tiga pengaman, ditulis di `inc/pintu.php`:

1. Kedua pintu tidak boleh ditutup bersamaan.
2. Login mandiri baru boleh ditutup bila sudah ada yang berhasil masuk
   lewat aplikasi induk dalam `'hari_bukti'` hari terakhir.
3. Admin tetap bisa login mandiri lewat `login.php?darurat=1`.

Sesi yang sedang berjalan ikut diakhiri pada permintaan berikutnya
sesudah pintunya ditutup. Bila terkunci di luar sama sekali, hapus
`data/pintu.json` di server: semua kembali ke `'pintu_bawaan'`.

### Alur SSO

Aplikasi induk mengirim formulir `POST` berisi `token` ke `sso.php`.
`ct_sso_verifikasi()` memeriksa tanda tangan, masa berlaku, dan bahwa
token belum pernah dipakai. Jangan menerima nama pengguna mentah
(`?user=admin`): siapa pun bisa mengetik alamat seperti itu.

Dalam mode demo, `sso.php` menampilkan "aplikasi induk tiruan" yang
membuat token bertanda tangan lalu memeriksanya dengan fungsi yang sama.

---

## 8. Tema, warna, dan bahasa

### Tema bawaan

Di `config/app.php`:

| Kunci | Pilihan |
|---|---|
| `tema` | `biru`, `hijau`, `teal`, `nila`, `ungu`, `anggrek`, `oranye`, `bata` |
| `mode` | `light`, `dark`, `auto` (ikut sistem operasi) |
| `palet` | warna grafik: `laut`, `bumi`, `hutan`, `senja`, `cerah`, `lembut`, `permen`, `kontras`, `aman`, `abu`, `tunggal` |

Pengguna mengganti mode dari tombol matahari/bulan di header; pilihannya
disimpan di sesi oleh `tema-simpan.php`. Coba semua kombinasi di
**Admin > Pengaturan Tema**.

### Mengganti logo dan nama

`config/app.php`: `nama` (judul header), `singkat`, `subjudul`, `logo`
(PNG atau SVG persegi), `pemilik`, `versi`. Ganti juga `assets/ct/logo.svg`
bila ingin tetap memakai nama berkas itu.

### Menulis CSS sendiri

Tambahkan di `assets/ct/ct-starter.css` atau berkas baru yang dimuat
sesudahnya. **Jangan menyunting** `tokens.css`, `theme.css`, dan
`coretax.css`: ketiganya disalin utuh dari aplikasi asal, sehingga
pembaruan cukup disalin ulang. Pakai variabel warna, bukan kode warna,
supaya mode gelap ikut berlaku:

```
.kotak-saya { background: var(--ct-panel); color: var(--ct-teks); border: 1px solid var(--ct-garis); }
```

### Bahasa

Tombol ID/EN di header menerjemahkan **bingkai** aplikasi (header, menu,
laci, palet, footer), bukan isi halaman. Nama menu diterjemahkan dari
kunci `'en'` di `config/menu.php`. Teks bingkai lain ada di
`assets/ct/coretax-bahasa.js` (larik `MENU`). Terjemahan disimpan di
peramban, bukan dikirim ke layanan terjemahan luar.

---

## 9. Grafik

Semua grafik lewat `DmpChart` (`assets/ct/dmp-echart.js`), jangan
memanggil `echarts.init()` langsung. DmpChart mengurus warna yang ikut
tema dan mode gelap, angka berformat Indonesia (1.234,5 dan Rb/Jt/M/T),
keadaan memuat dan kosong, ukuran yang ikut layar, serta tombol perbesar.

```
$pakai_grafik = true;   // di PHP, sebelum require awal.php

DmpChart.buat('idWadah', { xAxis: {...}, yAxis: {...}, series: [...] });
DmpChart.muat('idWadah', 'Mengambil data...');
DmpChart.kosong('idWadah', 'Tidak ada data untuk penyaring ini');
DmpChart.perbarui('idWadah', opsiBaru);
```

Wadahnya: `<div id="idWadah" class="dmp-chart"></div>`
(`dmp-chart--pendek` / `dmp-chart--tinggi` untuk mengatur tinggi).
Contoh lengkap di `grafik.php`.

---

## 10. Berkas CSS dan JavaScript tema

Urutan muat penting. Yang belakangan menang.

| Berkas | Peran |
|---|---|
| `tokens.css` | seluruh variabel warna: delapan tema, mode gelap, palet grafik |
| `theme.css` | komponen `dmp-*` (kartu, tombol, KPI, tabel, login, admin) |
| `coretax.css` | gaya Coretax: header navy, menu mendatar, panel, laci, palet |
| `ct-starter.css` | tambahan khusus starter (judul halaman, pita demo, login) |
| `coretax-bahasa.js` | pilihan bahasa. Dimuat **sebelum** coretax.js |
| `coretax.js` | layar memuat, menu mendatar, laci Buka Menu, palet Ctrl+K |
| `dmp-theme.js` | tombol terang/gelap |
| `dmp-echart.js` | DmpChart |
| `dmp-urut.js` | klik judul kolom tabel untuk mengurutkan (semua tabel, otomatis) |
| `ct-app.js` | layar penuh, cetak, saring tabel, salin teks |

### Atribut bantu

| Atribut | Pada | Fungsi |
|---|---|---|
| `data-ct-saring="#idTabel"` | `<input>` | menyaring baris tabel saat mengetik |
| `data-ct-info="#idTeks"` | `<input>` saring | menulis "n dari m baris" |
| `data-ct-salin="teks"` | tombol | menyalin teks ke papan klip |
| `data-urut="tidak"` | `<table>` / `<th>` | jangan diurutkan |
| `data-urut-nilai="123"` | `<td>` | nilai pengurutan (mis. angka asli dari teks "1.234") |

---

## 11. Memindahkan ke CodeIgniter 4 (atau framework lain)

Tema ini sengaja memakai fungsi biasa berawalan `ct_` supaya mudah
diganti satu per satu:

| Starter | CodeIgniter 4 |
|---|---|
| `ct_url('laporan.php')` | `base_url('laporan')` |
| `ct_aset('assets/ct/theme.css')` | `base_url('public/theme/ct/theme.css')` |
| `e($teks)` | `esc($teks)` |
| `ct_pengguna()` | `session('pengguna')` |
| `ct_csrf_field()` | `csrf_field()` |
| `ct_kilat()` / `ct_kilat_ambil()` | `session()->setFlashdata()` / `getFlashdata()` |
| `require CT_INC . '/awal.php'` | `$this->extend('layout/utama')` + `renderSection('isi')` |
| `ct_wajib_masuk()` / `ct_wajib_peran()` | Filter di `app/Config/Filters.php` |
| `data/*.json` | tabel basis data |

Langkahnya:

1. Salin `assets/` ke `public/theme/`.
2. Gabungkan `inc/awal.php` + `inc/akhir.php` menjadi satu view tata letak
   dengan `<?= $this->renderSection('isi') ?>` di tengah.
3. `inc/sidebar.php` menjadi view parsial; daftar menunya boleh tetap
   berkas PHP (`app/Config/Menu.php`).
4. Ganti fungsi `ct_*` sesuai tabel di atas.

Untuk aplikasi bukan PHP, salin markup dari berkas di `html/`.

---

## 12. Alat baris perintah

```
php tools/uji-pintu.php       # uji aturan Pintu Masuk (keluar 0 = lulus)
php tools/ekspor-html.php     # buat ulang versi statis di html/
```

`uji-pintu.php` memanggil `inc/pintu.php` yang asli, bukan tiruannya.
Jalankan ekspor ulang setiap kali tampilan diubah, supaya `html/` tidak basi.

---

## 13. Keamanan yang sudah ditangani

- Setiap formulir POST membawa token CSRF (`ct_csrf_field()` / `ct_csrf_cek()`).
- Seluruh keluaran ke HTML melewati `e()`.
- Id sesi diganti saat login dan keluar (mencegah *session fixation*).
- Cookie sesi `HttpOnly` dan `SameSite=Lax`; `Secure` otomatis di HTTPS.
- Sesi berakhir sesudah `'batas_menit'` menit tanpa aktivitas.
- Folder `data/`, `inc/`, `config/`, `tools/` ditolak dari peramban (`.htaccess`).
  Di Nginx, tulis aturan padanannya sendiri.
- Pesan login yang sama untuk "akun tidak ada" dan "sandi salah".

Yang **belum** ada dan perlu ditambahkan sesuai kebutuhan: pembatasan
percobaan login (rate limit), captcha, dan pencatatan audit ke basis data.

---

## 14. Masalah yang sering terjadi

| Gejala | Penyebab | Perbaikan |
|---|---|---|
| Menu mendatar tidak muncul, yang tampil sidebar kiri lama | `coretax.js` gagal dimuat atau ada galat JS lebih dulu | buka Konsol peramban (F12), perbaiki galat pertama |
| Perubahan CSS tidak terlihat | cache peramban | tidak terjadi bila memakai `ct_aset()` (menambah `?v=`); muat ulang paksa Ctrl+F5 |
| Saklar Pintu Masuk "Gagal menulis" | folder `data/` tidak bisa ditulis | beri izin tulis ke server web |
| Terkunci di luar | kedua jalan masuk bermasalah | hapus `data/pintu.json` |
| Grafik kosong, Konsol: "echarts belum dimuat" | lupa `$pakai_grafik = true` | tambahkan sebelum `require awal.php` |
| Menu bahasa Inggris tidak berubah | kunci `'en'` belum diisi | isi di `config/menu.php` |

---

## 15. Lisensi pihak ketiga

| Pustaka | Lisensi |
|---|---|
| Bootstrap 4.6.1 | MIT |
| jQuery 3.3.1 | MIT |
| ThemeKit v1 (Lavalite) + fon iconkit bawaannya | MIT menurut sumbernya |
| Apache ECharts | Apache 2.0 |
| Plus Jakarta Sans (Google Fonts) | SIL Open Font License |

Periksa ulang lisensi ThemeKit/iconkit di situs Lavalite sebelum dipakai
untuk produk komersial. Kode tema sendiri (berkas `ct-*`, `inc/`,
`config/`, halaman) belum diberi lisensi: tentukan oleh pemilik repo.

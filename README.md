# Coretax Starter Theme

Kerangka aplikasi web bergaya **Coretax** - seperti AdminLTE, tetapi dengan
tata letak header navy, menu mendatar berpanel, laci "Buka Menu", dan
pencarian Ctrl+K. PHP polos, tanpa composer, tanpa framework: salin ke
`htdocs`, buka di peramban, selesai.

Dipakai sebagai **tema dasar** untuk aplikasi baru.

## Isi

- **Halaman login** terhubung dengan saklar **Pintu Masuk**: login mandiri dan/atau masuk lewat aplikasi induk (SSO), dengan tiga pengaman dan pintu darurat admin
- **Tata letak utama**: header, menu mendatar dengan panel "Jelajahi", laci Buka Menu (menu terakhir dibuka + rekomendasi), palet Ctrl+K, remah roti, layar memuat, footer
- **Tata letak admin**: sidebar kiri, beranda admin, Pintu Masuk, Pengaturan Tema
- **Satu daftar menu** (`config/menu.php`) untuk semua tampilan menu, dengan submenu, ikon, lencana, penyaring peran, dan terjemahan
- Terang/gelap, **8 tema warna**, **11 palet grafik**, bahasa ID/EN
- Halaman contoh: dashboard, grafik ECharts, komponen + galeri 270+ ikon, formulir dengan validasi server, tabel (saring, urut, halaman, unduh CSV), halaman kosong, profil, 404/403, dokumentasi
- Versi **HTML statis** di `html/` untuk pratinjau atau aplikasi bukan PHP

## Mulai cepat

```
# salin folder ke htdocs, lalu buka
http://localhost/<nama-folder>/

# atau tanpa Apache
php -S 127.0.0.1:8080
```

Akun demo: `admin` / `admin` atau `user` / `user`.

> **Mode demo menyala secara bawaan.** Sebelum dipakai sungguhan, set
> `'demo' => false` di `config/app.php` dan sambungkan login asli di
> `inc/auth.php`.

Tanpa PHP: buka `html/index.html`.

## Membuat halaman baru

1. Salin `kosong.php` menjadi `laporan.php`, ubah `$judul` dan isinya.
2. Tambahkan satu baris di `config/menu.php`:

   ```php
   ['teks' => 'Laporan', 'ikon' => 'ik-file-text', 'url' => 'laporan.php'],
   ```

Menu mendatar, laci, dan pencarian Ctrl+K ikut berubah sendiri.

## Struktur

```
config/      app.php (pengaturan) · menu.php (daftar menu) · menu-admin.php
inc/         init.php · awal.php/akhir.php (tata letak) · sidebar.php · auth.php · pintu.php
admin/       beranda · pintu-masuk · pengaturan
assets/ct/   CSS & JS tema
assets/vendor/  Bootstrap 4.6 · jQuery 3.3 · iconkit · ThemeKit · ECharts
data/        JSON runtime (tidak ikut git)
docs/        PANDUAN.md · CHANGELOG.md
html/        hasil php tools/ekspor-html.php
tools/       ekspor-html.php · uji-pintu.php
```

## Dokumentasi

Panduan lengkap: **[docs/PANDUAN.md](docs/PANDUAN.md)** - cara kerja tata
letak, menu, login & SSO, Pintu Masuk, tema, grafik, atribut bantu, memindahkan ke
CodeIgniter 4, keamanan, dan masalah yang sering terjadi. Panduan yang
sama tampil di aplikasi lewat menu **Alat Bantu > Dokumentasi**.

Riwayat perubahan: [docs/CHANGELOG.md](docs/CHANGELOG.md).

## Uji

```
php tools/uji-pintu.php       # aturan Pintu Masuk
php tools/ekspor-html.php     # buat ulang html/ (gagal bila ada Warning PHP)
```

## Syarat

PHP 8.1 atau lebih baru. Peramban modern (Chrome, Edge, Firefox, Safari).
Huruf Plus Jakarta Sans dimuat dari Google Fonts; tanpa internet, huruf
sistem dipakai dan tata letak tetap utuh.

## Lisensi pihak ketiga

Bootstrap (MIT), jQuery (MIT), ThemeKit + iconkit (MIT menurut Lavalite),
Apache ECharts (Apache 2.0). Rincian di bagian 15 panduan.

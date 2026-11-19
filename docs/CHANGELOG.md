# Riwayat Perubahan

## v1.0.0 · 19 Nov 2026 — Starter theme lengkap

Repo diubah dari "preset warna coretax untuk DMP" (berkas `dist/coretax.css`
+ `preview/`) menjadi **kerangka aplikasi** siap pakai.

**Ditambahkan**
- Tata letak utama gaya Coretax: header navy, menu mendatar dengan panel
  "Jelajahi", laci Buka Menu, palet Ctrl+K, layar memuat, remah roti
- Tata letak admin dengan sidebar kiri
- `config/menu.php` sebagai satu-satunya daftar menu (submenu, ikon,
  lencana, peran, terjemahan `en`)
- Halaman login + Pintu Masuk (login mandiri / SSO aplikasi induk) dengan
  tiga pengaman dan pintu darurat admin; `sso.php` dengan simulasi demo
- Tombol terang/gelap disimpan ke sesi (`tema-simpan.php`); Menu
  Rekomendasi dari catatan kunjungan (`menu-populer.php`)
- Halaman contoh: dashboard, grafik, komponen + galeri ikon, formulir,
  tabel, kosong, profil, 404/403, dokumentasi
- `tools/ekspor-html.php` (versi statis di `html/`), `tools/uji-pintu.php`
- `docs/PANDUAN.md`, ditampilkan juga di dalam aplikasi

**Diubah dari salinan aslinya**
- `coretax.js`: grup "Alat Bantu" ditandai atribut `data-ct-kumpul`, bukan
  dicocokkan dari nama menu; pengecualian menu khusus aplikasi asal dibuang
- `coretax-bahasa.js`: kamus nama menu dipindah ke `config/menu.php`
  (`window.CT_KAMUS`); hanya teks bingkai yang tersisa di berkas

**Dihapus**
- `dist/coretax.css` dan `preview/` versi lama - digantikan `assets/ct/`
  dan halaman-halaman di atas (tetap ada di riwayat git)

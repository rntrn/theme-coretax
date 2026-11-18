# theme-coretax

Tema "Coretax" untuk DMP. Layout, menu, dan markup DMP **tidak berubah** - hanya warna dan detail tampilan (sidebar navy, penanda menu oranye, kartu bergaris biru, tabel berkepala huruf kapital). Mendukung mode terang dan gelap.

## Isi

| Berkas | Fungsi |
|---|---|
| `dist/coretax.css` | Tema: preset warna `coretax` + skin untuk kelas DMP |
| `preview/index.html` | Halaman contoh dengan markup DMP (buka lewat server lokal) |
| `preview/assets/` | Salinan CSS dasar DMP agar preview berdiri sendiri |

## Pasang di DMP

1. Salin `dist/coretax.css` ke `public/theme/dmp/coretax.css`.
2. Di `app/Views/inc/head.php`, muat **setelah** `tokens.css` dan `theme.css`:

   ```php
   <link rel="stylesheet" href="<?= dmp_asset('public/theme/dmp/coretax.css') ?>">
   ```
3. Daftarkan `coretax` sebagai pilihan warna di `ThemeService` (`presetTersedia()`), lalu pilih dari tombol tetes di header. Tema aktif lewat `<html data-dmp-theme="coretax">`.

## Preview lokal

```bash
python -m http.server 8765
```

Buka `http://127.0.0.1:8765/preview/index.html`. Tombol bulan di header mengganti terang/gelap.

## Catatan

- Warna hanya ditulis di blok token (bagian 1); komponen memakai `var(--dmp-*)` dengan warna cadangan untuk IE11, sesuai aturan `tokens.css`.
- Warna makna (sukses/bahaya/peringatan) bawaan DMP sengaja tidak diubah.

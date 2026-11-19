<?php
/**
 * inc/pintu.php — aturan "Pintu Masuk" (padanan Administrator > Pintu Masuk
 * dan PintuMasukAturan di DMP).
 *
 * APA
 *   Dua pintu yang bisa dibuka/ditutup admin:
 *     mandiri : formulir login di login.php
 *     sso     : masuk dari aplikasi induk (mis. Coretax) lewat sso.php
 *
 * TIGA PENGAMAN (sama persis dengan DMP)
 *   1. Kedua pintu tidak boleh ditutup bersamaan - tidak akan ada yang
 *      bisa masuk, termasuk admin.
 *   2. Login mandiri baru boleh ditutup bila jalur SSO TERBUKTI jalan:
 *      ada yang berhasil masuk lewat SSO dalam 'hari_bukti' hari terakhir.
 *   3. Admin tetap boleh login mandiri walau pintunya ditutup, lewat
 *      login.php?darurat=1 - pintu darurat bila SSO rusak.
 *
 * KENAPA ATURAN DI KELAS TERSENDIRI, BUKAN DI HALAMAN ADMIN
 *   Aturan yang sama dipakai di tiga tempat: halaman admin (boleh
 *   mengubah?), login.php (boleh masuk?), dan ct_wajib_masuk() (sesi
 *   yang sedang berjalan harus diakhiri?). Ditulis di halaman admin
 *   saja berarti dua tempat lainnya cepat atau lambat menyimpang.
 *
 * KENAPA BUKAN SATU SAKLAR "MODE LOGIN: mandiri | sso"
 *   Masa peralihan butuh KEDUA pintu terbuka bersamaan, dan pintu
 *   darurat butuh mandiri bisa dibuka tanpa menutup SSO.
 *
 * Terkunci di luar sama sekali? Hapus berkas data/pintu.json - semua
 * kembali ke 'pintu_bawaan' di config/app.php.
 */
declare(strict_types=1);

final class CtPintu
{
    public const NAMA = ['mandiri' => 'Login lewat halaman ini', 'sso' => 'Masuk lewat aplikasi induk'];

    /** @return array{mandiri:bool,sso:bool,diubah:array,oleh:array} */
    public static function status(): array
    {
        $bawaan = ct('pintu_bawaan', ['mandiri' => true, 'sso' => false]);
        $simpan = ct_data_baca('pintu');
        return [
            'mandiri' => (bool) ($simpan['mandiri'] ?? $bawaan['mandiri']),
            'sso'     => (bool) ($simpan['sso'] ?? $bawaan['sso']),
            'diubah'  => $simpan['diubah'] ?? [],
            'oleh'    => $simpan['oleh'] ?? [],
        ];
    }

    /**
     * Bolehkah pintu $jalur diubah menjadi $nyala?
     *
     * @return array{0:bool,1:string} [boleh, alasan bila tidak]
     */
    public static function bolehUbah(array $kini, string $jalur, bool $nyala, int $ssoBerhasil): array
    {
        if (! isset(self::NAMA[$jalur])) {
            return [false, 'Pintu tidak dikenal.'];
        }
        $baru = $kini;
        $baru[$jalur] = $nyala;

        if (! $baru['mandiri'] && ! $baru['sso']) {
            return [false, 'Kedua pintu tidak boleh ditutup bersamaan: tidak akan ada yang bisa masuk, termasuk admin.'];
        }
        if ($jalur === 'mandiri' && ! $nyala && $ssoBerhasil < 1) {
            return [false, 'Login mandiri baru boleh ditutup bila jalur ' . ct('sso_nama') . ' terbukti jalan: '
                . 'belum ada yang berhasil masuk lewat ' . ct('sso_nama') . ' dalam ' . (int) ct('hari_bukti') . ' hari terakhir.'];
        }
        return [true, ''];
    }

    public static function simpan(string $jalur, bool $nyala, string $oleh): bool
    {
        $isi = ct_data_baca('pintu');
        $isi[$jalur] = $nyala;
        $isi['diubah'][$jalur] = date('Y-m-d H:i');
        $isi['oleh'][$jalur] = $oleh;
        return ct_data_tulis('pintu', $isi);
    }

    /** Bolehkah login lewat formulir saat ini? Admin selalu boleh (pintu darurat). */
    public static function bolehMasukMandiri(array $status, bool $admin): bool
    {
        return $status['mandiri'] || $admin;
    }

    /**
     * Apakah sesi yang SEDANG berjalan harus diakhiri?
     * Tanpa ini, menutup pintu hanya berlaku bagi orang yang BARU akan
     * masuk - yang sudah di dalam tetap di dalam sampai sesinya habis.
     *
     * @return string|null null = lanjut; selain itu pesan untuk layar login
     */
    public static function putusanSesi(array $pengguna, array $status): ?string
    {
        $via = $pengguna['via'] ?? 'mandiri';
        if ($via === 'sso' && ! $status['sso']) {
            return 'Pintu masuk lewat ' . ct('sso_nama') . ' sedang ditutup admin.';
        }
        if ($via === 'mandiri' && ! $status['mandiri'] && ($pengguna['peran'] ?? '') !== 'admin') {
            return 'Login lewat halaman ini sedang ditutup admin. Silakan masuk lewat ' . ct('sso_nama') . '.';
        }
        return null;
    }

    // ------------------------------------------------------------------
    // Jejak masuk - bukti bahwa sebuah pintu benar-benar dipakai.
    // ------------------------------------------------------------------

    public static function catatMasuk(string $jalur, string $id): void
    {
        $log = ct_data_baca('masuk');
        $log[$jalur][] = ['waktu' => date('Y-m-d H:i:s'), 'id' => $id];
        $log[$jalur] = array_slice($log[$jalur], -200);   // cukup 200 terakhir per pintu
        ct_data_tulis('masuk', $log);
    }

    /** @return array<string,array{terakhir:?string,hari:int}> */
    public static function jejak(): array
    {
        $log = ct_data_baca('masuk');
        $batas = date('Y-m-d H:i:s', strtotime('-' . (int) ct('hari_bukti', 7) . ' days'));
        $hasil = [];
        foreach (array_keys(self::NAMA) as $j) {
            $baris = $log[$j] ?? [];
            $akhir = end($baris);
            $hasil[$j] = [
                'terakhir' => $akhir ? $akhir['waktu'] . ' (' . $akhir['id'] . ')' : null,
                'hari'     => count(array_filter($baris, static fn ($b) => $b['waktu'] >= $batas)),
            ];
        }
        return $hasil;
    }
}

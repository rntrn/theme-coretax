<?php
/**
 * tools/uji-pintu.php — uji aturan Pintu Masuk (inc/pintu.php) dan ct_url().
 *
 * Pakai:   php tools/uji-pintu.php      (keluar 0 = semua lulus)
 *
 * ATURAN: berkas yang diuji di-`require`, BUKAN ditulis ulang di sini.
 * Tiruan logika di dalam berkas uji bisa benar sementara kode aslinya
 * salah - dan uji tetap menyatakan lulus. Yang disediakan di sini hanya
 * pengganti seadanya untuk ct() dan penyimpanan data, supaya tidak
 * perlu sesi atau folder data/ sungguhan.
 */
declare(strict_types=1);

// ---- pengganti seadanya untuk fungsi dari inc/init.php ---------------
$GLOBALS['CT'] = ['pintu_bawaan' => ['mandiri' => true, 'sso' => false], 'sso_nama' => 'Induk', 'hari_bukti' => 7];
$GLOBALS['DATA'] = [];
function ct(string $k, $b = null) { return $GLOBALS['CT'][$k] ?? $b; }
function ct_data_baca(string $n, array $b = []): array { return $GLOBALS['DATA'][$n] ?? $b; }
function ct_data_tulis(string $n, array $isi): bool { $GLOBALS['DATA'][$n] = $isi; return true; }

require __DIR__ . '/../inc/pintu.php';

$lulus = 0; $gagal = 0;
function periksa(string $nama, $hasil, $harap): void
{
    global $lulus, $gagal;
    if ($hasil === $harap) { $lulus++; echo "  ok    $nama\n"; return; }
    $gagal++;
    echo "  GAGAL $nama\n        dapat: " . var_export($hasil, true) . "\n        harap: " . var_export($harap, true) . "\n";
}

echo "Status bawaan\n";
$s = CtPintu::status();
periksa('mandiri dibuka bila belum pernah diubah', $s['mandiri'], true);
periksa('sso ditutup bila belum pernah diubah', $s['sso'], false);

echo "Pengaman 1: tidak boleh keduanya ditutup\n";
periksa('tutup mandiri saat sso ditutup -> ditolak',
    CtPintu::bolehUbah(['mandiri' => true, 'sso' => false], 'mandiri', false, 5)[0], false);
periksa('tutup sso saat mandiri ditutup -> ditolak',
    CtPintu::bolehUbah(['mandiri' => false, 'sso' => true], 'sso', false, 5)[0], false);

echo "Pengaman 2: mandiri baru boleh ditutup bila SSO terbukti jalan\n";
periksa('tanpa login SSO -> ditolak',
    CtPintu::bolehUbah(['mandiri' => true, 'sso' => true], 'mandiri', false, 0)[0], false);
periksa('ada 1 login SSO -> boleh',
    CtPintu::bolehUbah(['mandiri' => true, 'sso' => true], 'mandiri', false, 1)[0], true);
periksa('membuka pintu selalu boleh',
    CtPintu::bolehUbah(['mandiri' => true, 'sso' => false], 'sso', true, 0)[0], true);
periksa('pintu tak dikenal -> ditolak',
    CtPintu::bolehUbah(['mandiri' => true, 'sso' => true], 'pintu-belakang', true, 9)[0], false);

echo "Pengaman 3: admin tetap boleh login mandiri\n";
periksa('mandiri ditutup, bukan admin -> tidak boleh', CtPintu::bolehMasukMandiri(['mandiri' => false, 'sso' => true], false), false);
periksa('mandiri ditutup, admin -> boleh', CtPintu::bolehMasukMandiri(['mandiri' => false, 'sso' => true], true), true);

echo "Sesi berjalan\n";
$tutupSso = ['mandiri' => true, 'sso' => false];
$tutupMandiri = ['mandiri' => false, 'sso' => true];
periksa('sesi SSO saat sso ditutup -> diakhiri', CtPintu::putusanSesi(['via' => 'sso', 'peran' => 'admin'], $tutupSso) !== null, true);
periksa('sesi mandiri pengguna saat mandiri ditutup -> diakhiri', CtPintu::putusanSesi(['via' => 'mandiri', 'peran' => 'pengguna'], $tutupMandiri) !== null, true);
periksa('sesi mandiri admin saat mandiri ditutup -> lanjut', CtPintu::putusanSesi(['via' => 'mandiri', 'peran' => 'admin'], $tutupMandiri), null);

echo "Simpan & jejak\n";
CtPintu::simpan('sso', true, 'admin');
periksa('status sso berubah sesudah simpan', CtPintu::status()['sso'], true);
CtPintu::catatMasuk('sso', 'user');
periksa('jejak menghitung login sso', CtPintu::jejak()['sso']['hari'], 1);
periksa('jejak mandiri tetap 0', CtPintu::jejak()['mandiri']['hari'], 0);

echo "\n$lulus lulus, $gagal gagal\n";
exit($gagal ? 1 : 0);

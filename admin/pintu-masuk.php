<?php
/**
 * admin/pintu-masuk.php — padanan Administrator > Pintu Masuk DMP.
 *
 * Dua kartu, satu per pintu: status, jejak masuk terakhir, dan satu
 * tombol. Aturan pengaman ditulis terang di layar - admin harus tahu
 * KENAPA tombolnya ditolak sebelum menekannya, bukan sesudahnya.
 * Aturannya sendiri di inc/pintu.php (CtPintu::bolehUbah), dipakai juga
 * oleh login.php dan ct_wajib_masuk().
 */
require __DIR__ . '/../inc/init.php';
ct_wajib_peran('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $jalur = (string) ($_POST['jalur'] ?? '');
    $nyala = ($_POST['nilai'] ?? '') === 'on';
    if (! ct_csrf_cek()) {
        ct_kilat('gagal', 'Formulir kedaluwarsa. Tidak ada yang diubah.');
    } else {
        $jejak = CtPintu::jejak();
        [$boleh, $alasan] = CtPintu::bolehUbah(CtPintu::status(), $jalur, $nyala, $jejak['sso']['hari']);
        if (! $boleh) {
            ct_kilat('gagal', $alasan . ' Tidak ada yang diubah.');
        } elseif (! CtPintu::simpan($jalur, $nyala, ct_pengguna()['id'])) {
            ct_kilat('gagal', 'Gagal menulis data/pintu.json (izin folder?). Tidak ada yang diubah.');
        } else {
            $nama = $jalur === 'sso' ? 'Masuk lewat ' . ct('sso_nama') : 'Login mandiri';
            ct_kilat('sukses', 'Pintu "' . $nama . '" kini ' . ($nyala ? 'DIBUKA' : 'DITUTUP') . '.');
        }
    }
    ct_alihkan('admin/pintu-masuk.php');
}

$status = CtPintu::status();
$jejak  = CtPintu::jejak();
$hari   = (int) ct('hari_bukti');
$pintu  = [
    'mandiri' => ['judul' => 'Login lewat halaman ini', 'sub' => 'Formulir login.php (nama pengguna + kata sandi).',
                  'ikon' => 'ik-log-in', 'bawaan' => 'dibuka'],
    'sso'     => ['judul' => 'Masuk lewat ' . ct('sso_nama'), 'sub' => 'Aplikasi induk mengirim token bertanda tangan ke sso.php.',
                  'ikon' => 'ik-layers', 'bawaan' => 'ditutup'],
];

// Alamat lengkap sso.php untuk ditempel di konfigurasi aplikasi induk.
$skema  = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$folder = rtrim(str_replace(chr(92), '/', dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/a/b'))), '/');
$urlSso = CT_EKSPOR ? 'https://alamat-aplikasi-anda/sso.php'   // ekspor statis: jangan bocorkan jalur disk
                    : $skema . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $folder . '/sso.php';

$judul    = 'Pintu Masuk';
$subjudul = 'Login lewat halaman ini dan masuk dari aplikasi induk: buka, tutup, pantau.';
require CT_INC . '/admin-awal.php';
?>
<div class="dmp-flex dmp-gap-3" style="flex-wrap:wrap;align-items:stretch">
<?php foreach ($pintu as $j => $pt) : ?>
    <?php $nyala = $status[$j]; ?>
    <div class="dmp-card dmp-mb-4" style="flex:1 1 360px">
        <div class="dmp-card__head">
            <h3 class="dmp-card__title"><i class="ik <?= e($pt['ikon']) ?>"></i> <?= e($pt['judul']) ?>
                <span class="dmp-badge <?= $nyala ? 'dmp-badge--sukses' : 'dmp-badge--netral' ?>" style="margin-left:8px"><?= $nyala ? 'DIBUKA' : 'DITUTUP' ?></span></h3>
            <p class="dmp-card__sub"><?= e($pt['sub']) ?></p>
        </div>
        <div class="dmp-card__body">
            <p class="dmp-help" style="margin:0 0 6px">Masuk terakhir lewat pintu ini:
                <b><?= e($jejak[$j]['terakhir'] ?? 'belum pernah') ?></b>
                &middot; <?= (int) $jejak[$j]['hari'] ?> kali dalam <?= $hari ?> hari terakhir.</p>
            <p class="dmp-help" style="margin:0 0 12px">
                <?php if (! empty($status['diubah'][$j])) : ?>
                    Terakhir diubah <?= e($status['diubah'][$j]) ?> oleh <?= e($status['oleh'][$j] ?? '-') ?>.
                <?php else : ?>
                    Belum pernah diubah: memakai bawaan (<?= e($pt['bawaan']) ?>, config/app.php).
                <?php endif; ?>
            </p>
            <?php if ($j === 'sso') : ?>
                <div class="dmp-field" style="margin-bottom:12px">
                    <label class="dmp-label" for="urlSso">Alamat tujuan POST untuk aplikasi induk</label>
                    <input class="dmp-input" id="urlSso" readonly onclick="this.select()" value="<?= e($urlSso) ?>">
                    <div class="dmp-help">Aplikasi induk mengirim <code>token</code> ke alamat ini.
                        Verifikasinya di <code>inc/auth.php</code> &rarr; <code>ct_sso_verifikasi()</code>.</div>
                </div>
            <?php endif; ?>
            <form method="post" action="<?= e(ct_url('admin/pintu-masuk.php')) ?>"
                  data-konfirmasi="<?= e(($nyala ? 'TUTUP' : 'BUKA') . ' pintu "' . $pt['judul'] . '" untuk semua pengguna?') ?>">
                <?= ct_csrf_field() ?>
                <input type="hidden" name="jalur" value="<?= e($j) ?>">
                <input type="hidden" name="nilai" value="<?= $nyala ? 'off' : 'on' ?>">
                <button type="submit" class="dmp-btn <?= $nyala ? 'dmp-btn--garis' : 'dmp-btn--utama' ?>">
                    <?= $nyala ? 'Tutup pintu ini' : 'Buka pintu ini' ?>
                </button>
            </form>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="dmp-card dmp-mb-4">
    <div class="dmp-card__head"><h3 class="dmp-card__title">Aturan pengaman</h3></div>
    <div class="dmp-card__body">
        <ul class="dmp-help" style="margin:0 0 0 18px">
            <li>Kedua pintu <b>tidak boleh ditutup bersamaan</b>.</li>
            <li>Login mandiri <b>baru boleh ditutup</b> bila sudah ada yang berhasil masuk lewat <?= e(ct('sso_nama')) ?> dalam <?= $hari ?> hari terakhir.</li>
            <li>Saat login mandiri ditutup, <b>admin tetap bisa masuk</b> lewat <code>login.php?darurat=1</code>.</li>
            <li>Sesi yang sedang berjalan ikut diakhiri pada permintaan berikutnya setelah pintunya ditutup
                (untuk login mandiri, admin dikecualikan).</li>
            <li>Terkunci di luar sama sekali? Hapus berkas <code>data/pintu.json</code> di server - semua kembali ke bawaan di <code>config/app.php</code>.</li>
        </ul>
    </div>
</div>
<?php ob_start(); ?>
<script>
// Konfirmasi sebelum mengubah pintu. Teksnya di atribut data-, bukan
// disusun di onsubmit, supaya tanda kutip pada nama pintu tidak merusak skrip.
Array.prototype.forEach.call(document.querySelectorAll('form[data-konfirmasi]'), function (f) {
    f.addEventListener('submit', function (e) { if (!confirm(f.getAttribute('data-konfirmasi'))) { e.preventDefault(); } });
});
</script>
<?php $skrip = ob_get_clean(); require CT_INC . '/admin-akhir.php'; ?>

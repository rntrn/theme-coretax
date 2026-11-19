<?php
/**
 * sso.php — pintu "Masuk lewat aplikasi induk" (padanan /masuk/coretax DMP).
 *
 * ALUR SUNGGUHAN ('demo' => false)
 *   Aplikasi induk mem-POST 'token' ke alamat ini (biasanya dari dalam
 *   iframe atau tautan bertanda tangan). ct_sso_verifikasi() di
 *   inc/auth.php memeriksa token; berhasil -> sesi dibuat dengan
 *   via = 'sso' -> dashboard.
 *
 * ALUR DEMO ('demo' => true)
 *   GET  : menampilkan "aplikasi induk tiruan" - pilih akun lalu kirim.
 *   POST : token demo ditandatangani kunci acak per sesi, lalu
 *          diverifikasi lewat fungsi yang SAMA dengan alur sungguhan.
 *   Jadi yang diperagakan memang jalur verifikasi aslinya, bukan jalan pintas.
 *
 * KENAPA TOKEN, BUKAN "?user=admin"
 *   Alamat dengan nama pengguna mentah bisa diketik siapa saja.
 */
require __DIR__ . '/inc/init.php';

$status = CtPintu::status();
if (! $status['sso']) {
    ct_kilat('gagal', 'Pintu masuk lewat ' . ct('sso_nama') . ' sedang ditutup admin.');
    ct_alihkan('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string) ($_POST['token'] ?? '');

    // Demo: "aplikasi induk tiruan" di bawah mengirim pilihan akun;
    // token dibuat di sini lalu diperiksa seperti token dari luar.
    if (ct('demo') && isset($_POST['akun_demo']) && ct_csrf_cek()) {
        $_SESSION['sso_demo_kunci'] = bin2hex(random_bytes(16));
        $isi = base64_encode(json_encode(['id' => (string) $_POST['akun_demo'], 'exp' => time() + 60]));
        $token = $isi . '.' . hash_hmac('sha256', $isi, $_SESSION['sso_demo_kunci']);
    }

    $u = $token !== '' ? ct_sso_verifikasi($token) : null;
    if ($u === null) {
        ct_kilat('gagal', 'Token dari ' . ct('sso_nama') . ' tidak sah atau sudah kedaluwarsa.');
        ct_alihkan('login.php');
    }
    ct_masukkan($u, 'sso');
    ct_alihkan('dashboard.php');
}

if (! ct('demo')) {
    // Alur sungguhan tidak punya tampilan: arahkan ke aplikasi induk.
    http_response_code(400);
    exit('Halaman ini dibuka dari ' . e(ct('sso_nama')) . ', bukan langsung.');
}

$judul = 'Simulasi ' . ct('sso_nama');
$tanpa_themekit = true;
?>
<!doctype html>
<html class="no-js ct" lang="id" <?= ct_atribut_tema() ?>>
<?php require CT_INC . '/kepala.php'; ?>
<body class="dmp-body">
<div class="ct-pita-demo">MODE DEMO</div>
<main class="dmp-login" style="grid-template-columns:1fr">
    <section class="dmp-login__kanan">
        <div class="dmp-login__form" style="max-width:420px">
            <h1><span class="ct-logo4" aria-hidden="true"><i></i><i></i><i></i><i></i></span> <?= e(ct('sso_nama')) ?> (tiruan)</h1>
            <p>Di aplikasi sungguhan, layar ini adalah aplikasi induk yang mengirim token
               bertanda tangan ke <code>sso.php</code>. Pilih akun yang &ldquo;sedang login&rdquo; di sana:</p>
            <form method="post" action="<?= e(ct_url('sso.php')) ?>">
                <?= ct_csrf_field() ?>
                <div class="dmp-field">
                    <label class="dmp-label" for="akun_demo">Akun di <?= e(ct('sso_nama')) ?></label>
                    <select class="dmp-input dmp-select" id="akun_demo" name="akun_demo">
                        <?php foreach (ct('akun_demo') as $id => $a) : ?>
                            <option value="<?= e($id) ?>"><?= e($a[1]) ?> (<?= e($id) ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="dmp-btn dmp-btn--utama dmp-login__tombol">Kirim token &amp; masuk</button>
            </form>
            <p style="margin-top:14px"><a href="<?= e(ct_url('login.php')) ?>">&larr; Kembali ke halaman login</a></p>
        </div>
    </section>
</main>
</body>
</html>

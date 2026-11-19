<?php
/**
 * login.php — halaman masuk, terhubung dengan saklar Pintu Masuk.
 *
 * APA
 *   Tata letak sama dengan login DMP: panel kiri identitas (navy),
 *   panel kanan formulir. Isinya mengikuti status pintu (inc/pintu.php):
 *     - pintu mandiri dibuka  -> formulir nama pengguna + kata sandi
 *     - pintu SSO dibuka      -> tombol "Masuk lewat <sso_nama>"
 *     - mandiri ditutup       -> formulir disembunyikan, kecuali ?darurat=1
 *                                (hanya admin yang akan diterima)
 *
 * KENAPA POST KE BERKAS INI SENDIRI
 *   Satu berkas untuk tampil + proses membuat aturan pintu hanya ditulis
 *   sekali di halaman ini. Sesudah berhasil -> alihkan (Post/Redirect/Get),
 *   supaya tombol Muat Ulang tidak mengirim ulang kata sandi.
 *
 * ?pratinjau=1 menampilkan halaman ini walau sudah login (untuk menu
 * "Halaman Contoh > Halaman Login").
 */
require __DIR__ . '/inc/init.php';

if (ct_pengguna() !== null && empty($_GET['pratinjau']) && ! CT_EKSPOR) {
    ct_alihkan('dashboard.php');
}

$status  = CtPintu::status();
$darurat = ! empty($_GET['darurat']);
$galat   = '';
$isianId = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isianId = trim((string) ($_POST['username'] ?? ''));
    $sandi   = (string) ($_POST['password'] ?? '');

    if (! ct_csrf_cek()) {
        $galat = 'Formulir kedaluwarsa. Muat ulang halaman lalu coba lagi.';
    } elseif ($isianId === '' || $sandi === '') {
        $galat = 'Nama pengguna dan kata sandi wajib diisi.';
    } else {
        $u = ct_autentikasi($isianId, $sandi);
        if ($u === null) {
            // Satu pesan untuk "akun tidak ada" dan "sandi salah": pesan
            // yang berbeda memberi tahu penyerang akun mana yang ada.
            $galat = 'Nama pengguna atau kata sandi salah.';
        } elseif (! CtPintu::bolehMasukMandiri($status, $u['peran'] === 'admin')) {
            $galat = 'Login lewat halaman ini sedang ditutup. Silakan masuk lewat ' . ct('sso_nama') . '.';
        } else {
            ct_masukkan($u, 'mandiri');
            ct_alihkan('dashboard.php');
        }
    }
}

$tampilForm = $status['mandiri'] || $darurat;
$judul = 'Masuk';
$tanpa_themekit = true;
?>
<!doctype html>
<html class="no-js ct" lang="id" <?= ct_atribut_tema() ?>>
<?php require CT_INC . '/kepala.php'; ?>
<body class="dmp-body">
<?php if (ct('demo')) : ?><div class="ct-pita-demo">MODE DEMO</div><?php endif; ?>

<main class="dmp-login">
    <?php // ============ PANEL KIRI: identitas ============ ?>
    <section class="dmp-login__kiri">
        <div class="ct-login-kiri">
            <div class="ct-login-kiri__merek">
                <img src="<?= e(ct_aset(ct('logo'))) ?>" alt="">
                <span><b><?= e(ct('singkat')) ?></b><small><?= e(ct('subjudul')) ?></small></span>
            </div>
            <div>
                <h2><?= e(ucwords(strtolower(ct('nama')))) ?></h2>
                <p>Kerangka aplikasi bergaya Coretax: header navy, menu mendatar dengan panel
                   jelajah, laci menu, dan pencarian Ctrl+K - siap dipakai untuk aplikasi baru.</p>
                <ul>
                    <li><i class="ik ik-check-circle"></i>Satu daftar menu di <code style="color:inherit">config/menu.php</code></li>
                    <li><i class="ik ik-check-circle"></i>Mode terang &amp; gelap, delapan tema warna</li>
                    <li><i class="ik ik-check-circle"></i>Login mandiri &amp; lewat aplikasi induk</li>
                </ul>
            </div>
            <div class="ct-login-kiri__kaki">&copy;<?= date('Y') ?> <?= e(ct('pemilik')) ?> &middot; <?= e(ct('versi')) ?></div>
        </div>
    </section>

    <?php // ============ PANEL KANAN: isian ============ ?>
    <section class="dmp-login__kanan">
        <div class="dmp-login__form">
            <h1>Masuk</h1>
            <p><?= $tampilForm ? 'Gunakan nama pengguna dan kata sandi Anda.' : 'Silakan masuk lewat ' . e(ct('sso_nama')) . '.' ?></p>

            <?php foreach (ct_kilat_ambil() as [$jenis, $pesan]) : ?>
                <div class="dmp-login__pesan" role="alert"><i class="ik ik-info"></i><span><?= e($pesan) ?></span></div>
            <?php endforeach; ?>
            <?php if ($galat !== '') : ?>
                <div class="dmp-login__pesan" role="alert"><i class="ik ik-alert-circle"></i><span><?= e($galat) ?></span></div>
            <?php endif; ?>

            <?php if ($tampilForm) : ?>
                <?php if (! $status['mandiri']) : ?>
                    <div class="dmp-login__pesan" role="status"><i class="ik ik-shield"></i>
                        <span>Pintu darurat: login mandiri sedang ditutup, hanya admin yang diterima.</span></div>
                <?php endif; ?>
                <form action="<?= e(ct_url('login.php' . ($darurat ? '?darurat=1' : ''))) ?>" method="post" autocomplete="on">
                    <?= ct_csrf_field() ?>
                    <div class="dmp-field">
                        <label class="dmp-label" for="username">Nama Pengguna</label>
                        <div class="dmp-login__isian">
                            <i class="ik ik-user"></i>
                            <input type="text" class="dmp-input" id="username" name="username" value="<?= e($isianId) ?>"
                                   autocomplete="username" autofocus required>
                        </div>
                    </div>
                    <div class="dmp-field">
                        <label class="dmp-label" for="password">Kata Sandi</label>
                        <div class="dmp-login__isian dmp-login__isian--sandi">
                            <i class="ik ik-lock"></i>
                            <input type="password" class="dmp-input" id="password" name="password" autocomplete="current-password" required>
                            <button type="button" class="dmp-login__intip" id="ctIntip" aria-pressed="false" aria-label="Tampilkan kata sandi">
                                <i class="ik ik-eye" aria-hidden="true"></i><i class="ik ik-eye-off" aria-hidden="true"></i>
                            </button>
                        </div>
                    </div>
                    <button type="submit" class="dmp-btn dmp-btn--utama dmp-login__tombol">Masuk</button>
                </form>
            <?php else : ?>
                <div class="ct-login-tutup"><i class="ik ik-lock"></i>
                    Login lewat halaman ini sedang ditutup admin.
                    <a href="<?= e(ct_url('login.php?darurat=1')) ?>">Admin: pintu darurat</a></div>
            <?php endif; ?>

            <?php if ($status['sso']) : ?>
                <?php if ($tampilForm) : ?><div class="ct-atau">atau</div><?php endif; ?>
                <a class="ct-sso" href="<?= e(ct_url('sso.php')) ?>">
                    <span class="ct-logo4" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                    Masuk lewat <?= e(ct('sso_nama')) ?>
                </a>
            <?php endif; ?>

            <?php if (ct('demo')) : ?>
                <div class="ct-akun-demo">
                    Akun demo: <code>admin</code>/<code>admin</code> atau <code>user</code>/<code>user</code>.
                    Matikan dengan <code>'demo' =&gt; false</code> di <code>config/app.php</code>.
                </div>
            <?php endif; ?>

            <div class="dmp-login__kaki"><?= e(ct('pemilik')) ?><br><span style="opacity:.7"><?= e(ct('versi')) ?></span></div>
        </div>
    </section>
</main>

<script>
// Tombol intip kata sandi: password <-> text. Fokus & posisi kursor
// dikembalikan supaya pengguna bisa langsung lanjut mengetik.
(function () {
    var s = document.getElementById('password'), b = document.getElementById('ctIntip');
    if (!s || !b) { return; }
    b.addEventListener('click', function () {
        var tampil = s.type === 'text';
        s.type = tampil ? 'password' : 'text';
        b.setAttribute('aria-pressed', String(!tampil));
        b.setAttribute('aria-label', tampil ? 'Tampilkan kata sandi' : 'Sembunyikan kata sandi');
        s.focus(); s.setSelectionRange(s.value.length, s.value.length);
    });
})();
</script>
</body>
</html>

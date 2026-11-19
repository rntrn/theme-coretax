<?php
/**
 * inc/auth.php — TEMPAT MENYAMBUNGKAN LOGIN ASLI.
 *
 * Dua fungsi, dua pintu. Selama 'demo' => true di config/app.php,
 * keduanya memakai data contoh. Untuk aplikasi sungguhan:
 *   1. ganti isi ct_autentikasi() dengan pemeriksaan ke LDAP / database
 *   2. ganti isi ct_sso_verifikasi() dengan verifikasi token aplikasi induk
 *   3. set 'demo' => false
 *
 * Keduanya mengembalikan larik pengguna yang SAMA bentuknya:
 *   ['id' => '...', 'nama' => '...', 'peran' => 'admin|pengguna']
 * atau null bila gagal. Halaman lain hanya mengenal bentuk ini, jadi
 * mengganti sumber login tidak menyentuh berkas lain.
 *
 * KENAPA MODE DEMO DIBUAT GAMBLANG, BUKAN "SEMUA ISIAN DITERIMA"
 *   "Asal tidak kosong boleh masuk" sering lupa dimatikan dan menjadi
 *   pintu belakang di produksi. Akun demo yang tertulis jelas, ditambah
 *   pita peringatan MODE DEMO di setiap halaman, membuatnya mustahil
 *   tidak terlihat.
 */
declare(strict_types=1);

/** Pintu "mandiri": nama pengguna + kata sandi dari formulir login.php. */
function ct_autentikasi(string $id, string $sandi): ?array
{
    if (ct('demo')) {
        $akun = ct('akun_demo', [])[$id] ?? null;
        if ($akun !== null && hash_equals($akun[0], $sandi)) {
            return ['id' => $id, 'nama' => $akun[1], 'peran' => $akun[2]];
        }
        return null;
    }

    // ---- SAMBUNGKAN DI SINI ----------------------------------------
    // Contoh database (PDO), kata sandi disimpan dengan password_hash():
    //
    //   $st = ct_db()->prepare('SELECT id, nama, peran, sandi FROM pengguna WHERE id = ?');
    //   $st->execute([$id]);
    //   $u = $st->fetch(PDO::FETCH_ASSOC);
    //   if ($u && password_verify($sandi, $u['sandi'])) {
    //       return ['id' => $u['id'], 'nama' => $u['nama'], 'peran' => $u['peran']];
    //   }
    //
    // Contoh LDAP: ldap_bind($sambungan, "uid=$id,ou=people,dc=contoh", $sandi)
    // ----------------------------------------------------------------
    return null;
}

/**
 * Pintu "sso": aplikasi induk mengirim token (POST 'token') ke sso.php.
 *
 * Token WAJIB diverifikasi - tanda tangan, masa berlaku, dan bahwa ia
 * belum pernah dipakai. Jangan pernah menerima "nip=..." mentah dari
 * aplikasi lain: siapa pun bisa mengirim formulir yang sama.
 */
function ct_sso_verifikasi(string $token): ?array
{
    if (ct('demo')) {
        // Token demo dibuat sso.php sendiri: base64(json) + HMAC dengan
        // kunci acak per sesi. Cukup untuk memperagakan alurnya.
        [$isi, $tanda] = array_pad(explode('.', $token, 2), 2, '');
        $kunci = $_SESSION['sso_demo_kunci'] ?? '';
        if ($kunci === '' || ! hash_equals(hash_hmac('sha256', $isi, $kunci), $tanda)) {
            return null;
        }
        $data = json_decode((string) base64_decode($isi), true);
        if (! is_array($data) || ($data['exp'] ?? 0) < time()) {
            return null;
        }
        unset($_SESSION['sso_demo_kunci']);   // sekali pakai
        $akun = ct('akun_demo', [])[$data['id'] ?? ''] ?? null;
        return $akun ? ['id' => $data['id'], 'nama' => $akun[1], 'peran' => $akun[2]] : null;
    }

    // ---- SAMBUNGKAN DI SINI ----------------------------------------
    // Contoh token JWT HS256 dari aplikasi induk:
    //   - pecah header.payload.signature
    //   - hitung hash_hmac('sha256', "$header.$payload", KUNCI_BERSAMA, true)
    //   - bandingkan dengan hash_equals(), periksa 'exp' dan 'jti'
    //   - petakan klaim (sub, name, role) ke ['id','nama','peran']
    // Simpan KUNCI_BERSAMA di variabel lingkungan, BUKAN di berkas ini.
    // ----------------------------------------------------------------
    return null;
}

/** Dipanggil kedua pintu sesudah verifikasi berhasil. */
function ct_masukkan(array $pengguna, string $via): void
{
    session_regenerate_id(true);   // cegah session fixation
    $pengguna['via'] = $via;
    $_SESSION['pengguna'] = $pengguna;
    $_SESSION['aktif'] = time();
    CtPintu::catatMasuk($via, $pengguna['id']);
}

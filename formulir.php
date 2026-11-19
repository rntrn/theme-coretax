<?php
/**
 * formulir.php — contoh formulir lengkap dengan pola Post/Redirect/Get.
 *
 * ALUR
 *   POST -> periksa CSRF & isian -> gagal: tampilkan lagi beserta pesan
 *   per kolom dan isian lama | berhasil: pesan kilat + alihkan (GET).
 *   Alihkan sesudah berhasil supaya Muat Ulang tidak mengirim dua kali.
 *
 * Validasi dilakukan di SERVER. Atribut required/pattern di HTML hanya
 * kenyamanan - mudah dilewati siapa pun.
 */
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();

$isian = ['nama' => '', 'surel' => '', 'unit' => '', 'tanggal' => '', 'jenis' => 'biasa', 'catatan' => '', 'setuju' => ''];
$galat = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($isian as $k => $v) {
        $isian[$k] = trim((string) ($_POST[$k] ?? ''));
    }
    if (! ct_csrf_cek())                                   { $galat['_'] = 'Formulir kedaluwarsa, muat ulang halaman.'; }
    if (mb_strlen($isian['nama']) < 3)                      { $galat['nama'] = 'Nama minimal 3 huruf.'; }
    if (! filter_var($isian['surel'], FILTER_VALIDATE_EMAIL)) { $galat['surel'] = 'Alamat surel tidak sah.'; }
    if ($isian['unit'] === '')                               { $galat['unit'] = 'Pilih salah satu unit.'; }
    if ($isian['setuju'] !== '1')                            { $galat['setuju'] = 'Wajib dicentang.'; }

    if ($galat === []) {
        // Di sini data disimpan ke basis data.
        ct_kilat('sukses', 'Formulir atas nama ' . $isian['nama'] . ' berhasil dikirim (contoh - tidak disimpan).');
        ct_alihkan('formulir.php');
    }
}

$judul    = 'Formulir';
$subjudul = 'Isian, pilihan, tanggal, saklar, validasi server, dan pesan kesalahan per kolom.';
$remah    = [['Antarmuka'], ['Formulir']];
require CT_INC . '/awal.php';

/** Kelas + pesan galat untuk satu kolom. */
$cek = static fn (string $k) => isset($galat[$k]) ? ' is-invalid' : '';
$pesan = static fn (string $k) => isset($galat[$k]) ? '<div class="invalid-feedback d-block">' . e($galat[$k]) . '</div>' : '';
?>
<?php if (isset($galat['_'])) : ?><div class="ct-kilat ct-kilat--gagal"><i class="ik ik-alert-circle"></i><span><?= e($galat['_']) ?></span></div><?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card"><div class="card-header"><h3>Formulir Pengajuan</h3></div><div class="card-body">
            <form method="post" action="<?= e(ct_url('formulir.php')) ?>" novalidate>
                <?= ct_csrf_field() ?>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="nama">Nama lengkap</label>
                        <input class="form-control<?= $cek('nama') ?>" id="nama" name="nama" value="<?= e($isian['nama']) ?>" required>
                        <?= $pesan('nama') ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="surel">Surel</label>
                        <input type="email" class="form-control<?= $cek('surel') ?>" id="surel" name="surel" value="<?= e($isian['surel']) ?>" placeholder="nama@contoh.id">
                        <?= $pesan('surel') ?>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="unit">Unit</label>
                        <select class="form-control<?= $cek('unit') ?>" id="unit" name="unit">
                            <option value="">- pilih -</option>
                            <?php foreach (['Unit A', 'Unit B', 'Unit C'] as $u) : ?>
                                <option<?= $isian['unit'] === $u ? ' selected' : '' ?>><?= e($u) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?= $pesan('unit') ?>
                    </div>
                    <div class="form-group col-md-6">
                        <label for="tanggal">Tanggal</label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= e($isian['tanggal']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label class="d-block">Jenis pengajuan</label>
                    <?php foreach (['biasa' => 'Biasa', 'segera' => 'Segera', 'rahasia' => 'Rahasia'] as $v => $t) : ?>
                        <div class="custom-control custom-radio custom-control-inline">
                            <input type="radio" class="custom-control-input" id="jenis_<?= $v ?>" name="jenis" value="<?= $v ?>"<?= $isian['jenis'] === $v ? ' checked' : '' ?>>
                            <label class="custom-control-label" for="jenis_<?= $v ?>"><?= $t ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="form-group">
                    <label for="catatan">Catatan</label>
                    <textarea class="form-control" id="catatan" name="catatan" rows="3"><?= e($isian['catatan']) ?></textarea>
                    <small class="form-text text-muted">Opsional. Maksimal 500 huruf.</small>
                </div>
                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="kabar" name="kabar" value="1">
                        <label class="custom-control-label" for="kabar">Kirim kabar lewat surel</label>
                    </div>
                    <div class="custom-control custom-checkbox mt-2">
                        <input type="checkbox" class="custom-control-input<?= $cek('setuju') ?>" id="setuju" name="setuju" value="1"<?= $isian['setuju'] === '1' ? ' checked' : '' ?>>
                        <label class="custom-control-label" for="setuju">Data yang saya isi benar</label>
                        <?= $pesan('setuju') ?>
                    </div>
                </div>
                <button type="submit" class="dmp-btn dmp-btn--utama"><i class="ik ik-send"></i> Kirim</button>
                <a href="<?= e(ct_url('formulir.php')) ?>" class="dmp-btn dmp-btn--garis">Batal</a>
            </form>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-header"><h3>Gaya dmp-input</h3></div><div class="card-body">
            <div class="dmp-field"><label class="dmp-label" for="a1">Isian</label><input class="dmp-input" id="a1" placeholder="Ketik di sini"><div class="dmp-help">Teks bantuan di bawah isian.</div></div>
            <div class="dmp-field"><label class="dmp-label" for="a2">Pilihan</label><select class="dmp-input dmp-select" id="a2"><option>Satu</option><option>Dua</option></select></div>
            <div class="dmp-field"><label class="dmp-label" for="a3">Hanya baca</label><input class="dmp-input" id="a3" readonly value="Tidak bisa diubah"></div>
        </div></div>
    </div>
</div>
<?php require CT_INC . '/akhir.php'; ?>

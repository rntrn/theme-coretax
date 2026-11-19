<?php
/**
 * komponen.php — katalog komponen: tombol, lencana, peringatan, kartu,
 * tab, modal, dropdown, progres, keadaan kosong, kerangka memuat, ikon.
 *
 * Dua keluarga kelas hidup berdampingan, sama seperti di DMP:
 *   - Bootstrap 4 (btn, badge, alert, card, nav-tabs, modal) - sudah
 *     diberi gaya Coretax oleh coretax.css di dalam .wrapper
 *   - Kelas dmp-* (dmp-btn, dmp-badge, dmp-card, dmp-kpi) dari theme.css -
 *     dipakai halaman baru dan tata letak admin
 * Untuk halaman baru, pilih SATU keluarga per halaman supaya konsisten.
 *
 * Daftar ikon dibaca langsung dari iconkit.min.css, bukan ditulis tangan:
 * daftar tangan akan menyimpang begitu berkas ikonnya diperbarui.
 */
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();

$judul    = 'Komponen';
$subjudul = 'Katalog elemen antarmuka. Klik nama ikon untuk menyalin kelasnya.';
$remah    = [['Antarmuka'], ['Komponen']];

preg_match_all('/\.ik-([a-z0-9-]+):before/', (string) file_get_contents(CT_AKAR . '/assets/vendor/iconkit/css/iconkit.min.css'), $m);
$ikon = array_unique($m[1]);
sort($ikon);
require CT_INC . '/awal.php';
?>

<div class="row">
    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h3>Tombol</h3></div><div class="card-body">
            <p class="small text-muted mb-2">Kelas dmp-btn</p>
            <div class="d-flex flex-wrap" style="gap:8px">
                <button class="dmp-btn dmp-btn--utama"><i class="ik ik-save"></i> Utama</button>
                <button class="dmp-btn dmp-btn--garis">Garis</button>
                <button class="dmp-btn dmp-btn--lembut">Lembut</button>
                <button class="dmp-btn dmp-btn--bahaya"><i class="ik ik-trash-2"></i> Bahaya</button>
                <button class="dmp-btn dmp-btn--utama dmp-btn--kecil">Kecil</button>
                <button class="dmp-btn dmp-btn--garis" disabled>Nonaktif</button>
            </div>
            <p class="small text-muted mt-3 mb-2">Bootstrap</p>
            <div class="d-flex flex-wrap" style="gap:8px">
                <button class="btn btn-primary">Primary</button>
                <button class="btn btn-outline-primary">Outline</button>
                <button class="btn btn-success">Success</button>
                <button class="btn btn-warning">Warning</button>
                <button class="btn btn-danger">Danger</button>
                <button class="btn btn-light">Light</button>
            </div>
        </div></div>

        <div class="card"><div class="card-header"><h3>Lencana &amp; Status</h3></div><div class="card-body">
            <div class="d-flex flex-wrap align-items-center" style="gap:8px">
                <span class="dmp-badge dmp-badge--sukses">Sukses</span>
                <span class="dmp-badge dmp-badge--awas">Awas</span>
                <span class="dmp-badge dmp-badge--bahaya">Bahaya</span>
                <span class="dmp-badge dmp-badge--netral">Netral</span>
                <span class="dmp-lencana dmp-lencana--biru">Biru</span>
                <span class="dmp-lencana dmp-lencana--hijau">Hijau</span>
                <span class="dmp-lencana dmp-lencana--abu">Abu</span>
                <span class="badge badge-primary">badge</span>
                <span class="badge badge-pill badge-success">pill</span>
            </div>
        </div></div>

        <div class="card"><div class="card-header"><h3>Peringatan</h3></div><div class="card-body">
            <div class="ct-kilat ct-kilat--sukses"><i class="ik ik-check-circle"></i><span>Data berhasil disimpan (ct-kilat).</span></div>
            <div class="ct-kilat ct-kilat--gagal"><i class="ik ik-alert-circle"></i><span>Gagal menyimpan. Tidak ada yang diubah.</span></div>
            <div class="alert alert-info mb-2">Alert Bootstrap: info.</div>
            <div class="alert alert-warning mb-0">Alert Bootstrap: warning.</div>
        </div></div>
    </div>

    <div class="col-lg-6">
        <div class="card"><div class="card-header"><h3>Tab</h3></div><div class="card-body">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><a class="nav-link active" data-toggle="tab" href="#tab1" role="tab">Unit Kerja</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab2" role="tab">Jenis Layanan</a></li>
                <li class="nav-item"><a class="nav-link" data-toggle="tab" href="#tab3" role="tab">Periode</a></li>
            </ul>
            <div class="tab-content pt-3">
                <div class="tab-pane fade show active" id="tab1" role="tabpanel">Isi tab pertama. Tab aktif berwarna navy seperti Coretax.</div>
                <div class="tab-pane fade" id="tab2" role="tabpanel">Isi tab kedua.</div>
                <div class="tab-pane fade" id="tab3" role="tabpanel">Isi tab ketiga.</div>
            </div>
        </div></div>

        <div class="card"><div class="card-header"><h3>Modal, Dropdown, Tooltip</h3></div><div class="card-body d-flex flex-wrap" style="gap:8px">
            <button class="dmp-btn dmp-btn--utama" data-toggle="modal" data-target="#contohModal">Buka modal</button>
            <div class="dropdown">
                <button class="dmp-btn dmp-btn--garis dropdown-toggle" data-toggle="dropdown">Aksi</button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#"><i class="ik ik-download dropdown-icon"></i> Unduh Excel</a>
                    <a class="dropdown-item" href="#"><i class="ik ik-printer dropdown-icon"></i> Cetak</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item text-danger" href="#"><i class="ik ik-trash-2 dropdown-icon"></i> Hapus</a>
                </div>
            </div>
            <button class="dmp-btn dmp-btn--lembut" data-toggle="tooltip" title="Teks bantuan singkat">Arahkan kursor</button>
        </div></div>

        <div class="card"><div class="card-header"><h3>Progres &amp; Memuat</h3></div><div class="card-body">
            <div class="progress mb-3" style="height:8px"><div class="progress-bar" style="width:65%"></div></div>
            <div class="dmp-skeleton dmp-skeleton--judul mb-2"></div>
            <div class="dmp-skeleton dmp-skeleton--baris mb-2"></div>
            <div class="dmp-skeleton dmp-skeleton--baris" style="width:70%"></div>
        </div></div>

        <div class="card"><div class="card-header"><h3>Keadaan Kosong</h3></div><div class="card-body">
            <div class="dmp-kosong">
                <i class="ik ik-inbox" style="font-size:32px"></i>
                <div class="dmp-kosong__judul">Belum ada data</div>
                <div class="dmp-kosong__pesan">Ubah penyaring atau pilih periode lain.</div>
            </div>
        </div></div>
    </div>
</div>

<div class="dmp-card dmp-mb-4">
    <div class="dmp-card__head">
        <h3 class="dmp-card__title"><i class="ik ik-box"></i> Kartu dmp-card</h3>
        <p class="dmp-card__sub">Kartu gaya admin: judul, subjudul, isi. Dipakai di tata letak admin.</p>
    </div>
    <div class="dmp-card__body">Isi kartu. Gunakan <code>.dmp-card__body--flush</code> untuk tabel tanpa jarak tepi.</div>
</div>

<div class="card" id="ikon">
    <div class="card-header"><h3>Ikon iconkit (<?= count($ikon) ?>)</h3></div>
    <div class="card-body">
        <input type="search" class="dmp-input mb-3" id="cariIkon" placeholder="Saring ikon..." style="max-width:280px">
        <div class="ct-ikon-galeri" id="galeriIkon">
            <?php foreach ($ikon as $n) : ?>
                <button type="button" data-ct-salin="ik ik-<?= e($n) ?>" title="Salin kelas"><i class="ik ik-<?= e($n) ?>"></i><span><?= e($n) ?></span></button>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="contohModal" tabindex="-1" role="dialog" aria-labelledby="contohModalJudul" aria-hidden="true">
    <div class="modal-dialog" role="document"><div class="modal-content">
        <div class="modal-header"><h5 class="modal-title" id="contohModalJudul">Judul Modal</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
        <div class="modal-body">Isi modal Bootstrap 4, sudah bergaya Coretax.</div>
        <div class="modal-footer">
            <button type="button" class="dmp-btn dmp-btn--garis" data-dismiss="modal">Batal</button>
            <button type="button" class="dmp-btn dmp-btn--utama" data-dismiss="modal">Simpan</button>
        </div>
    </div></div>
</div>

<?php ob_start(); ?>
<script>
$(function () { $('[data-toggle="tooltip"]').tooltip(); });
document.getElementById('cariIkon').addEventListener('input', function () {
    var q = this.value.trim().toLowerCase();
    Array.prototype.forEach.call(document.querySelectorAll('#galeriIkon button'), function (b) {
        b.hidden = q !== '' && b.textContent.indexOf(q) === -1;
    });
});
</script>
<?php $skrip = ob_get_clean(); require CT_INC . '/akhir.php'; ?>

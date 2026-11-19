<?php
/**
 * tabel.php — tabel data: saring cepat, urut per kolom, halaman, unduh CSV.
 *
 * KENAPA TANPA DataTables
 *   DataTables mengganti tampilan tabel (kotak cari, penomoran, gaya
 *   sendiri) dan menambah ~90 KB. Pengurutan sudah otomatis dari
 *   dmp-urut.js (klik judul kolom), saringan dari ct-app.js
 *   (data-ct-saring). Untuk ribuan baris, lakukan penomoran halaman di
 *   SERVER seperti contoh di bawah, bukan memuat semuanya ke peramban.
 *
 * ?unduh=csv mengirim SELURUH data (bukan hanya halaman yang tampil).
 */
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();

// Data contoh yang stabil (benih tetap) - di aplikasi sungguhan: query.
mt_srand(42);
$unit = ['Unit A', 'Unit B', 'Unit C', 'Unit D'];
$st   = [['Aktif', 'sukses'], ['Ditinjau', 'awas'], ['Ditolak', 'bahaya'], ['Draf', 'netral']];
$data = [];
for ($i = 1; $i <= 87; $i++) {
    $data[] = ['no' => sprintf('PGJ-%04d', $i), 'nama' => 'Pemohon ' . $i, 'unit' => $unit[mt_rand(0, 3)],
               'nilai' => mt_rand(1, 999) * 125000, 'tanggal' => date('Y-m-d', strtotime('-' . mt_rand(0, 200) . ' days')),
               'status' => $st[mt_rand(0, 3)]];
}

if (($_GET['unduh'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="contoh-data.csv"');
    $f = fopen('php://output', 'w');
    fwrite($f, "\xEF\xBB\xBF");   // BOM: tanpa ini Excel membaca huruf non-ASCII berantakan
    fputcsv($f, ['No', 'Nama', 'Unit', 'Nilai', 'Tanggal', 'Status'], ';');   // ';' = pemisah Excel berlokal Indonesia
    foreach ($data as $r) {
        fputcsv($f, [$r['no'], $r['nama'], $r['unit'], $r['nilai'], $r['tanggal'], $r['status'][0]], ';');
    }
    exit;
}

$perHal = 25;
$hal    = max(1, (int) ($_GET['hal'] ?? 1));
$jmlHal = (int) ceil(count($data) / $perHal);
$hal    = min($hal, $jmlHal);
$tampil = array_slice($data, ($hal - 1) * $perHal, $perHal);

$judul    = 'Tabel Data';
$subjudul = 'Klik judul kolom untuk mengurutkan. Saringan hanya berlaku untuk halaman yang tampil.';
$remah    = [['Antarmuka'], ['Tabel Data']];
require CT_INC . '/awal.php';
?>
<div class="card">
    <div class="card-header d-flex flex-wrap align-items-center justify-content-between" style="gap:8px">
        <h3>Daftar Pengajuan</h3>
        <div class="d-flex flex-wrap align-items-center" style="gap:8px">
            <input type="search" class="dmp-input" style="width:220px" placeholder="Saring baris..." data-ct-saring="#tblData" data-ct-info="#infoSaring">
            <a class="dmp-btn dmp-btn--garis dmp-btn--kecil" href="<?= e(ct_url('tabel.php?unduh=csv')) ?>"><i class="ik ik-download"></i> CSV</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0" id="tblData">
                <thead><tr><th>No</th><th>Nama</th><th>Unit</th><th class="text-right">Nilai (Rp)</th><th>Tanggal</th><th>Status</th></tr></thead>
                <tbody>
                <?php foreach ($tampil as $r) : ?>
                    <tr>
                        <td><code><?= e($r['no']) ?></code></td>
                        <td><?= e($r['nama']) ?></td>
                        <td><?= e($r['unit']) ?></td>
                        <td class="text-right" data-urut-nilai="<?= (int) $r['nilai'] ?>"><?= number_format($r['nilai'], 0, ',', '.') ?></td>
                        <td><?= e(date('d/m/Y', strtotime($r['tanggal']))) ?></td>
                        <td><span class="dmp-badge dmp-badge--<?= e($r['status'][1]) ?>"><?= e($r['status'][0]) ?></span></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer d-flex flex-wrap align-items-center justify-content-between" style="gap:8px">
        <small class="text-muted"><span id="infoSaring"><?= count($tampil) ?> dari <?= count($tampil) ?> baris</span> &middot; total <?= count($data) ?> data</small>
        <nav aria-label="Halaman tabel"><ul class="pagination pagination-sm mb-0">
            <li class="page-item<?= $hal <= 1 ? ' disabled' : '' ?>"><a class="page-link" href="<?= e(ct_url('tabel.php?hal=' . ($hal - 1))) ?>">&lsaquo;</a></li>
            <?php for ($i = 1; $i <= $jmlHal; $i++) : ?>
                <li class="page-item<?= $i === $hal ? ' active' : '' ?>"><a class="page-link" href="<?= e(ct_url('tabel.php?hal=' . $i)) ?>"><?= $i ?></a></li>
            <?php endfor; ?>
            <li class="page-item<?= $hal >= $jmlHal ? ' disabled' : '' ?>"><a class="page-link" href="<?= e(ct_url('tabel.php?hal=' . ($hal + 1))) ?>">&rsaquo;</a></li>
        </ul></nav>
    </div>
</div>
<?php require CT_INC . '/akhir.php'; ?>

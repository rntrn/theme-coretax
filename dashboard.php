<?php
/**
 * dashboard.php — contoh halaman dashboard: kartu angka, grafik, tabel ringkas.
 *
 * Data di halaman ini ANGKA CONTOH yang dibuat di sini. Di aplikasi
 * sungguhan, ganti larik $bulanan / $kategori dengan hasil query.
 * Bentuk datanya sengaja larik PHP biasa lalu json_encode() ke skrip,
 * supaya jelas batas antara "mengambil data" (PHP) dan "menggambar" (JS).
 */
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();

$judul        = 'Ringkasan';
$subjudul     = 'Contoh dashboard: kartu angka, grafik ECharts, dan tabel ringkas.';
$remah        = [['Dashboard', 'dashboard.php'], ['Ringkasan']];
$pakai_grafik = true;

$bulan    = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
$tahunIni = [120, 132, 101, 134, 190, 230, 210, 182, 191, 234, 260, 280];
$tahunLalu = [98, 110, 95, 120, 150, 180, 175, 160, 170, 190, 210, 240];
$kategori = ['Layanan A' => 335, 'Layanan B' => 234, 'Layanan C' => 154, 'Layanan D' => 98];
$kpi = [
    ['Total Transaksi', '2.464', '+12,4%', 'naik', true],
    ['Pengguna Aktif',  '1.208', '+3,1%',  'naik', false],
    ['Rata-rata Waktu', '4,2 mnt', '-0,6 mnt', 'naik', false],
    ['Tiket Terbuka',   '37',    '+5',     'turun', false],
];
require CT_INC . '/awal.php';
?>

<div class="dmp-kpi-grid dmp-mb-4">
    <?php foreach ($kpi as [$label, $nilai, $delta, $arah, $aksen]) : ?>
        <div class="dmp-kpi<?= $aksen ? ' dmp-kpi--aksen' : '' ?>">
            <span class="dmp-kpi__label"><?= e($label) ?></span>
            <span class="dmp-kpi__value"><?= e($nilai) ?><span class="dmp-kpi__delta dmp-kpi__delta--<?= e($arah) ?>"><?= e($delta) ?></span></span>
        </div>
    <?php endforeach; ?>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header"><h3>Tren Bulanan</h3></div>
            <div class="card-body"><div id="grafikTren" class="dmp-chart"></div></div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h3>Komposisi Layanan</h3></div>
            <div class="card-body"><div id="grafikKomposisi" class="dmp-chart"></div></div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h3>Aktivitas Terbaru</h3></div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead><tr><th>Waktu</th><th>Pengguna</th><th>Kegiatan</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php
                        $aktivitas = [
                            ['08:12', 'Budi',  'Mengunggah dokumen', ['Selesai', 'sukses']],
                            ['08:40', 'Sari',  'Memperbarui profil', ['Selesai', 'sukses']],
                            ['09:05', 'Andi',  'Mengajukan tiket',   ['Menunggu', 'awas']],
                            ['09:31', 'Rina',  'Ekspor laporan',     ['Gagal', 'bahaya']],
                            ['10:02', 'Dewi',  'Masuk aplikasi',     ['Info', 'netral']],
                        ];
                        foreach ($aktivitas as [$w, $u, $k, [$st, $jn]]) : ?>
                            <tr><td><?= e($w) ?></td><td><?= e($u) ?></td><td><?= e($k) ?></td>
                                <td><span class="dmp-badge dmp-badge--<?= e($jn) ?>"><?= e($st) ?></span></td></tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h3>Capaian Target</h3></div>
            <div class="card-body">
                <?php foreach (['Penyelesaian tiket' => 82, 'Kepuasan pengguna' => 91, 'Ketepatan waktu' => 64, 'Kelengkapan data' => 47] as $n => $v) : ?>
                    <div class="d-flex justify-content-between small mb-1"><span><?= e($n) ?></span><b><?= $v ?>%</b></div>
                    <div class="progress mb-3" style="height:8px">
                        <div class="progress-bar <?= $v >= 80 ? 'bg-success' : ($v >= 60 ? 'bg-primary' : 'bg-warning') ?>" style="width:<?= $v ?>%"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
// Semua grafik lewat DmpChart (assets/ct/dmp-echart.js): warna ikut
// tema + mode gelap, angka berformat Indonesia, ukuran ikut layar.
DmpChart.buat('grafikTren', {
    tooltip: { trigger: 'axis' },
    legend:  { data: ['<?= date('Y') ?>', '<?= date('Y') - 1 ?>'] },
    xAxis:   { type: 'category', data: <?= json_encode($bulan) ?> },
    yAxis:   { type: 'value' },
    series: [
        { name: '<?= date('Y') ?>',     type: 'bar',  data: <?= json_encode($tahunIni) ?> },
        { name: '<?= date('Y') - 1 ?>', type: 'line', smooth: true, data: <?= json_encode($tahunLalu) ?> }
    ]
});
DmpChart.buat('grafikKomposisi', {
    tooltip: { trigger: 'item' },
    legend:  { bottom: 0 },
    series: [{ type: 'pie', radius: ['38%', '60%'], center: ['50%', '42%'], label: { show: false },
               data: <?= json_encode(array_map(static fn ($k, $v) => ['name' => $k, 'value' => $v], array_keys($kategori), $kategori)) ?> }]
});
</script>
<?php $skrip = ob_get_clean(); require CT_INC . '/akhir.php'; ?>

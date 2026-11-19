<?php
/**
 * grafik.php — galeri grafik ECharts lewat DmpChart.
 *
 * KENAPA DmpChart, BUKAN echarts.init() LANGSUNG
 *   DmpChart (assets/ct/dmp-echart.js, dari DMP) mengurus hal yang
 *   selalu terlupa bila tiap grafik ditulis sendiri: warna ikut tema dan
 *   mode gelap, format angka Indonesia (1.234,5 / Rb / Jt / M / T),
 *   keadaan memuat & kosong, ukuran ikut layar, tombol perbesar.
 */
require __DIR__ . '/inc/init.php';
ct_wajib_masuk();

$judul        = 'Grafik ECharts';
$subjudul     = 'Batang, garis, area, donat, keadaan memuat dan kosong - semuanya lewat DmpChart.';
$remah        = [['Dashboard', 'dashboard.php'], ['Grafik ECharts']];
$pakai_grafik = true;
require CT_INC . '/awal.php';
?>
<div class="row">
    <div class="col-lg-6"><div class="card"><div class="card-header"><h3>Batang Bertumpuk</h3></div>
        <div class="card-body"><div id="gBatang" class="dmp-chart"></div></div></div></div>
    <div class="col-lg-6"><div class="card"><div class="card-header"><h3>Garis &amp; Area</h3></div>
        <div class="card-body"><div id="gGaris" class="dmp-chart"></div></div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-header"><h3>Batang Mendatar</h3></div>
        <div class="card-body"><div id="gMendatar" class="dmp-chart"></div></div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-header"><h3>Keadaan Memuat</h3></div>
        <div class="card-body"><div id="gMuat" class="dmp-chart"></div>
            <button type="button" class="dmp-btn dmp-btn--garis dmp-btn--kecil" id="tblIsi">Isi data</button></div></div></div>
    <div class="col-lg-4"><div class="card"><div class="card-header"><h3>Tidak Ada Data</h3></div>
        <div class="card-body"><div id="gKosong" class="dmp-chart"></div></div></div></div>
</div>

<?php ob_start(); ?>
<script>
var bln = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
function acak(n, a, b) { var r = []; for (var i = 0; i < n; i++) { r.push(Math.round(a + Math.random() * (b - a))); } return r; }

DmpChart.buat('gBatang', {
    tooltip: { trigger: 'axis' }, legend: {},
    xAxis: { type: 'category', data: bln.slice(0, 6) }, yAxis: { type: 'value' },
    series: ['Wilayah 1', 'Wilayah 2', 'Wilayah 3'].map(function (n) {
        return { name: n, type: 'bar', stack: 'jml', data: acak(6, 2e8, 9e8) };   // angka besar -> label sumbu "Jt"
    })
});
DmpChart.buat('gGaris', {
    tooltip: { trigger: 'axis' }, legend: {},
    xAxis: { type: 'category', boundaryGap: false, data: bln }, yAxis: { type: 'value' },
    series: [{ name: 'Realisasi', type: 'line', areaStyle: { opacity: .15 }, smooth: true, data: acak(12, 40, 100) },
             { name: 'Target', type: 'line', lineStyle: { type: 'dashed' }, data: acak(12, 60, 90) }]
});
DmpChart.buat('gMendatar', {
    tooltip: { trigger: 'axis' }, grid: { left: 90 },
    xAxis: { type: 'value' }, yAxis: { type: 'category', data: ['Unit A', 'Unit B', 'Unit C', 'Unit D', 'Unit E'] },
    series: [{ type: 'bar', data: acak(5, 10, 90) }]
});

DmpChart.muat('gMuat', 'Mengambil data...');
document.getElementById('tblIsi').addEventListener('click', function () {
    DmpChart.buat('gMuat', { tooltip: {}, xAxis: { type: 'category', data: bln.slice(0, 5) }, yAxis: {},
                             series: [{ type: 'bar', data: acak(5, 5, 50) }] });
});
DmpChart.kosong('gKosong', 'Tidak ada data untuk penyaring ini');
</script>
<?php $skrip = ob_get_clean(); require CT_INC . '/akhir.php'; ?>

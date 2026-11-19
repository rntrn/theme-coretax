<?php
/**
 * inc/markdown.php — pengubah Markdown -> HTML yang SANGAT kecil.
 *
 * APA
 *   Dipakai dokumentasi.php untuk menampilkan docs/PANDUAN.md di dalam
 *   aplikasi. Mendukung yang dipakai panduan itu saja: judul (#..####),
 *   paragraf, daftar (- dan 1.), blok kode ```, tabel |..|, kutipan >,
 *   garis ---, serta `kode`, **tebal**, *miring*, [tautan](url).
 *
 * KENAPA MENULIS SENDIRI, BUKAN Parsedown/CommonMark
 *   Tema ini tanpa composer. Dan yang penting: panduan cukup ditulis
 *   SEKALI (docs/PANDUAN.md) lalu dibaca di GitHub maupun di aplikasi.
 *   Halaman dokumentasi yang ditulis ulang dalam HTML akan menyimpang
 *   dari berkas .md-nya pada pembaruan pertama.
 *
 * Seluruh teks di-escape lebih dulu; hanya tag buatan berkas ini yang
 * lolos. Jangan pakai untuk Markdown kiriman pengguna tanpa ditinjau.
 */
declare(strict_types=1);

function ct_md_baris(string $s): string
{
    $s = e($s);
    $kode = [];
    // Kode sebaris disimpan dulu supaya isinya tidak ikut diubah (mis. **).
    $s = preg_replace_callback('/`([^`]+)`/', static function ($m) use (&$kode) {
        $kode[] = '<code>' . $m[1] . '</code>';
        return "\x01" . (count($kode) - 1) . "\x01";
    }, $s);
    $s = preg_replace('/\*\*(.+?)\*\*/', '<b>$1</b>', $s);
    $s = preg_replace('/(?<![\w*])\*(?!\s)(.+?)(?<!\s)\*(?![\w*])/', '<i>$1</i>', $s);
    $s = preg_replace_callback('/\[([^\]]+)\]\(([^)\s]+)\)/', static function ($m) {
        $url = html_entity_decode($m[2], ENT_QUOTES);
        if (preg_match('/^\s*javascript:/i', $url)) {
            return $m[1];
        }
        $luar = preg_match('#^https?://#i', $url) ? ' target="_blank" rel="noopener"' : '';
        return '<a href="' . e($url) . '"' . $luar . '>' . $m[1] . '</a>';
    }, $s);
    return preg_replace_callback("/\x01(\d+)\x01/", static fn ($m) => $kode[(int) $m[1]], $s);
}

/** @return array{0:string,1:array<int,array{0:int,1:string,2:string}>} [html, daftar isi] */
function ct_markdown(string $md): array
{
    $baris = preg_split('/\r?\n/', $md);
    $html = '';
    $isi = [];
    $n = count($baris);
    for ($i = 0; $i < $n; $i++) {
        $b = $baris[$i];

        if (preg_match('/^```/', $b)) {                     // blok kode
            $buf = [];
            for ($i++; $i < $n && ! preg_match('/^```/', $baris[$i]); $i++) {
                $buf[] = $baris[$i];
            }
            $html .= '<pre class="ct-kode"><code>' . e(implode("\n", $buf)) . "</code></pre>\n";
            continue;
        }
        if (preg_match('/^(#{1,4})\s+(.*)$/', $b, $m)) {      // judul
            $tk = strlen($m[1]);
            $id = trim(preg_replace('/[^a-z0-9]+/', '-', strtolower($m[2])), '-');
            if ($tk >= 2 && $tk <= 3) {
                $isi[] = [$tk, $id, $m[2]];
            }
            $html .= sprintf("<h%d id=\"%s\">%s</h%d>\n", $tk + 1, e($id), ct_md_baris($m[2]), $tk + 1);
            continue;
        }
        if (preg_match('/^\s*---+\s*$/', $b)) {
            $html .= "<hr>\n";
            continue;
        }
        if (preg_match('/^\|/', $b)) {                        // tabel
            $rows = [];
            for (; $i < $n && preg_match('/^\|/', $baris[$i]); $i++) {
                $rows[] = $baris[$i];
            }
            $i--;
            $sel = static fn ($r) => array_map('trim', explode('|', trim(trim($r), '|')));
            $html .= '<div class="table-responsive"><table class="table table-sm" data-urut="tidak"><thead><tr>';
            foreach ($sel($rows[0]) as $c) { $html .= '<th>' . ct_md_baris($c) . '</th>'; }
            $html .= '</tr></thead><tbody>';
            foreach (array_slice($rows, 2) as $r) {
                $html .= '<tr>';
                foreach ($sel($r) as $c) { $html .= '<td>' . ct_md_baris($c) . '</td>'; }
                $html .= '</tr>';
            }
            $html .= "</tbody></table></div>\n";
            continue;
        }
        if (preg_match('/^\s*([-*]|\d+\.)\s+/', $b, $m)) {     // daftar
            $tag = ctype_digit($m[1][0]) ? 'ol' : 'ul';
            $html .= "<$tag>";
            for (; $i < $n && preg_match('/^\s*([-*]|\d+\.)\s+(.*)$/', $baris[$i], $mm); $i++) {
                $teks = $mm[2];
                while ($i + 1 < $n && preg_match('/^\s{2,}\S/', $baris[$i + 1]) && ! preg_match('/^\s*([-*]|\d+\.)\s+/', $baris[$i + 1])) {
                    $teks .= ' ' . trim($baris[++$i]);
                }
                $html .= '<li>' . ct_md_baris($teks) . '</li>';
            }
            $i--;
            $html .= "</$tag>\n";
            continue;
        }
        if (preg_match('/^>\s?(.*)$/', $b)) {                 // kutipan
            $buf = [];
            for (; $i < $n && preg_match('/^>\s?(.*)$/', $baris[$i], $mm); $i++) {
                $buf[] = $mm[1];
            }
            $i--;
            $html .= '<blockquote class="dmp-adm__ingat">' . ct_md_baris(implode(' ', $buf)) . "</blockquote>\n";
            continue;
        }
        if (trim($b) === '') {
            continue;
        }
        $buf = [$b];                                           // paragraf
        while ($i + 1 < $n && trim($baris[$i + 1]) !== '' && ! preg_match('/^(#|```|\||>|\s*([-*]|\d+\.)\s|\s*---)/', $baris[$i + 1])) {
            $buf[] = $baris[++$i];
        }
        $html .= '<p>' . ct_md_baris(implode(' ', $buf)) . "</p>\n";
    }
    return [$html, $isi];
}

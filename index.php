<?php
/**
 * index.php — pintu depan: sudah login -> dashboard, belum -> login.
 */
require __DIR__ . '/inc/init.php';
ct_alihkan(ct_pengguna() !== null ? 'dashboard.php' : 'login.php');

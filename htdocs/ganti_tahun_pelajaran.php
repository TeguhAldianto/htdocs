<?php
session_start();

// Set default tahun pelajaran jika belum ada
if (!isset($_SESSION['tahun_pelajaran'])) {
    $_SESSION['tahun_pelajaran'] = '2025/2026';
}

// Jika ada input dari form (POST)
if (isset($_POST['tahun_pelajaran'])) {
    $_SESSION['tahun_pelajaran'] = $_POST['tahun_pelajaran'];
}

// Kembali ke halaman sebelumnya
header("Location: " . $_SERVER['HTTP_REFERER']);
exit;

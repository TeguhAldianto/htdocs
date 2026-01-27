<?php
include '../config.php';

// Kosongkan semua setoran_id di tabel pembayaran
$db->query("UPDATE pembayaran SET setoran_id = NULL");

// Hapus semua setoran
$db->query("DELETE FROM setoran");

echo "<script>alert('Semua data setoran berhasil dihapus.'); window.location='listsetoran.php';</script>";
?>

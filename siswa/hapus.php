<?php
include '../config.php';

// Cek apakah ada data ID yang dikirim via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = $_POST['id'];

    try {
        $stmt = $db->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->execute([$id]);

        // Berhasil: Lempar kembali ke list dengan parameter notif
        header("Location: list.php?notif=hapus");
        exit;
    } catch (PDOException $e) {
        // Gagal: Lempar dengan pesan error
        header("Location: list.php?notif=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    // Jika diakses secara ilegal (misal via URL langsung), balikkan ke list
    header("Location: list.php");
    exit;
}
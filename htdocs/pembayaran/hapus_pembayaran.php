<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = $_POST['id'];
    $stmt = $db->prepare("DELETE FROM pembayaran WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect ke halaman list
    header("Location: listpembayaran.php?notif=hapus");
    exit;
}

// Jika tidak valid, redirect juga
header("Location: listpembayaran.php");
exit;

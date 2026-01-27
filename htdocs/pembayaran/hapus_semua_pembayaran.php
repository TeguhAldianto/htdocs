<?php
include '../config.php';

try {
    $db->exec("DELETE FROM pembayaran");
    header('Location: listpembayaran.php?notif=hapussemua');
} catch (PDOException $e) {
    echo "Gagal menghapus semua pembayaran: " . $e->getMessage();
}
?>

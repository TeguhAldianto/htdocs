<?php
include dirname(__DIR__) . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $db->query("DELETE FROM siswa");
        // Redirect dengan notif hapus agar memicu SweetAlert di list.php
        header("Location: list.php?notif=hapus");
        exit;
    } catch (PDOException $e) {
        header("Location: list.php?notif=error&msg=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: list.php");
    exit;
}
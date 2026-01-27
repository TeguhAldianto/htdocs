<?php
include '../config.php';

if (isset($_GET['jenjang'])) {
    $jenjang = $_GET['jenjang'];

    $stmt = $db->prepare("SELECT id, nama, kelas, nominal, donatur FROM siswa WHERE jenjang = ? ORDER BY nama");
    $stmt->execute([$jenjang]);
    $siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($siswa);
}
?>

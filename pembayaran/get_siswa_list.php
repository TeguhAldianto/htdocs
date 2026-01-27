<?php
include '../config.php';

$siswa = $db->query("SELECT id, nama, kelas FROM siswa ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($siswa);
?>

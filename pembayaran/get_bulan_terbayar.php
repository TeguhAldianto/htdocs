<?php
include '../config.php';

$id = $_GET['siswa_id'] ?? null;
if (!$id) {
    echo json_encode([]);
    exit;
}

$query = $db->prepare("
    SELECT bulan 
    FROM pembayaran 
    WHERE siswa_id = ?
");
$query->execute([$id]);

$hasil = [];

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    if (!$row['bulan']) continue;

    $arr = explode(',', $row['bulan']);
    foreach ($arr as $b) {
        $b = trim($b);
        if ($b !== '') {
            $hasil[] = $b;
        }
    }
}

echo json_encode(array_values(array_unique($hasil)));

<?php
include '../config.php';

header('Content-Type: application/json');

try {
    // Ambil data dari form
    $tanggal   = $_POST['tanggal'] ?? '';
    $siswa_val = $_POST['siswa_id'] ?? '';
    $kelas     = $_POST['kelas'] ?? '';
    $bulan     = $_POST['bulan'] ?? [];
    $mamin     = $_POST['total_mamin'] ?? 0;
    $jumlah    = $_POST['jumlah_manual'] ?? '';

    // 🔹 Validasi: hanya anggap kosong jika string kosong, bukan 0
    if ($tanggal === '' || $siswa_val === '' || empty($bulan) || $jumlah === '') {
        echo json_encode([
            'status' => 'error',
            'message' => 'Data belum lengkap!'
        ]);
        exit;
    }

    // siswa_id dikirim dengan format "id|kelas"
    $parts = explode('|', $siswa_val);
    $siswa_id = $parts[0] ?? null;

    if (!$siswa_id) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Data siswa tidak valid!'
        ]);
        exit;
    }

    // Query insert sesuai struktur tabel pembayaran
    $stmt = $db->prepare("INSERT INTO pembayaran 
        (siswa_id, tanggal, kelas, bulan, jumlah, mamin, setoran_id) 
        VALUES 
        (:siswa_id, :tanggal, :kelas, :bulan, :jumlah, :mamin, NULL)");

    foreach ($bulan as $b) {
        $stmt->execute([
            ':siswa_id' => $siswa_id,
            ':tanggal'  => $tanggal,
            ':kelas'    => $kelas,
            ':bulan'    => $b,
            ':jumlah'   => $jumlah,
            ':mamin'    => $mamin
        ]);
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Pembayaran manual berhasil disimpan.'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

<?php
include '../config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Metode tidak valid.']);
    exit;
}

// 1. Validasi Input Dasar
$siswa_raw = $_POST['siswa_id'] ?? '';
if (empty($siswa_raw) || strpos($siswa_raw, '|') === false) {
    echo json_encode(['status' => 'error', 'message' => 'Data siswa tidak valid.']);
    exit;
}

$siswa_value = explode('|', $siswa_raw);
$siswa_id = (int)$siswa_value[0];
$tanggal = $_POST['tanggal'] ?? date('Y-m-d'); 
$bulan_input = $_POST['bulan'] ?? [];
$total_mamin = (int)($_POST['total_mamin'] ?? 0);
$setoran_id = $_POST['setoran_id'] ?? null; // Tambahan variabel setoran_id

if (empty($bulan_input) || !is_array($bulan_input)) {
    echo json_encode(['status' => 'error', 'message' => 'Pilih minimal satu bulan pembayaran.']);
    exit;
}

try {
    // Mulai Transaksi Database
    $db->beginTransaction();

    // 2. Ambil Data Siswa dengan Lock (FOR UPDATE) untuk keamanan data
    $stmt = $db->prepare("SELECT nominal, donatur, kelas FROM siswa WHERE id = ? FOR UPDATE");
    $stmt->execute([$siswa_id]);
    $siswa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$siswa) {
        throw new Exception("Data siswa tidak ditemukan.");
    }

    $kelas_fix = (!empty($_POST['kelas'])) ? $_POST['kelas'] : $siswa['kelas'];
    $spp_per_bulan = max((int)$siswa['nominal'] - (int)$siswa['donatur'], 0);
    $total_spp = $spp_per_bulan * count($bulan_input);
    $total_bayar = $total_spp + $total_mamin;

    // 3. Cek Duplikasi Bulan secara Efisien
    // Kita ambil semua riwayat bulan yang sudah dibayar oleh siswa ini
    $stmtCek = $db->prepare("SELECT bulan FROM pembayaran WHERE siswa_id = ?");
    $stmtCek->execute([$siswa_id]);
    $existing_rows = $stmtCek->fetchAll(PDO::FETCH_COLUMN);

    $already_paid = [];
    foreach ($existing_rows as $row) {
        $clean_row = str_replace(' ', '', $row);
        $parts = explode(',', $clean_row);
        $already_paid = array_merge($already_paid, $parts);
    }

    foreach ($bulan_input as $b) {
        if (in_array($b, $already_paid)) {
            throw new Exception("Bulan $b sudah pernah dibayar.");
        }
    }

    // 4. Simpan Data
    $bulan_str = implode(', ', $bulan_input);
    $stmtSimpan = $db->prepare("
        INSERT INTO pembayaran (siswa_id, tanggal, kelas, bulan, jumlah, mamin, setoran_id)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    $stmtSimpan->execute([
        $siswa_id, 
        $tanggal, 
        $kelas_fix, 
        $bulan_str, 
        $total_bayar, 
        $total_mamin,
        $setoran_id
    ]);

    // Jika sampai sini tidak ada error, simpan permanen
    $db->commit();

    echo json_encode([
        'status' => 'success',
        'message' => 'Pembayaran berhasil disimpan!',
        'total' => number_format($total_bayar, 0, ',', '.')
    ]);

} catch (Exception $e) {
    // Jika ada error, batalkan semua perubahan di database
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
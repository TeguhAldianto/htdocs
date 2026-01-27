<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $nama = $_POST['nama'];
    $jenjang = $_POST['jenjang'];
    $kelas = $_POST['kelas'];
    $nominal = $_POST['nominal'];
    $donatur = $_POST['donatur'];
    $mamin = $_POST['mamin'];
    $status = $_POST['status'];
    $tanggal_keluar = $_POST['tanggal_keluar'] ?: null;

    /* ================= LOGIKA PEMBERSIHAN NO HP ================= */
    // 1. Ambil input no_hp
    $no_hp_input = $_POST['no_hp']; 
    
    // 2. Hapus semua karakter non-digit (spasi, strip, dll)
    $no_hp_clean = preg_replace('/[^0-9]/', '', $no_hp_input);

    // 3. Pastikan format yang disimpan ke DB selalu diawali '62'
    // Karena di tampilan form user hanya mengetik sisa nomor setelah +62
    if (!empty($no_hp_clean)) {
        // Jika user tidak sengaja mengetik 08... ubah jadi 628...
        if (substr($no_hp_clean, 0, 1) === '0') {
            $no_hp = '62' . substr($no_hp_clean, 1);
        } 
        // Jika input sudah diawali 62, gunakan langsung
        elseif (substr($no_hp_clean, 0, 2) === '62') {
            $no_hp = $no_hp_clean;
        } 
        // Jika hanya angka sisanya (misal: 812...), tambahkan 62 di depan
        else {
            $no_hp = '62' . $no_hp_clean;
        }
    } else {
        $no_hp = '';
    }
    /* ============================================================ */

    $stmt = $db->prepare("
        UPDATE siswa 
        SET nama=?, jenjang=?, kelas=?, nominal=?, donatur=?, mamin=?, status=?, no_hp=?, tanggal_keluar=? 
        WHERE id=?
    ");
    
    $stmt->execute([
        $nama, 
        $jenjang, 
        $kelas, 
        $nominal, 
        $donatur, 
        $mamin, 
        $status,
        $no_hp, // Menggunakan variabel yang sudah dibersihkan
        $tanggal_keluar, 
        $id
    ]);

    header("Location: list.php?notif=edit");
    exit;
}
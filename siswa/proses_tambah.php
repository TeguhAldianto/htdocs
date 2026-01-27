<?php
include '../config.php';

if (
    isset($_POST['nama']) &&
    isset($_POST['kelas']) &&
    isset($_POST['jenjang']) &&
    isset($_POST['nominal']) &&
    isset($_POST['donatur']) &&
    isset($_POST['tanggal_masuk']) // tambah validasi tanggal_masuk
) {
    $nama = $_POST['nama'];
    $kelas = $_POST['kelas'];
    $jenjang = $_POST['jenjang'];
    $nominal = $_POST['nominal'];
    $donatur = $_POST['donatur'];
    $tanggal_masuk = $_POST['tanggal_masuk'];

    // Jika jenjang TK, maka mamin 5000, selain itu 0
    $mamin = ($jenjang == 'TK') ? 5000 : 0;

    // Simpan data siswa termasuk tanggal_masuk
    $stmt = $db->prepare("INSERT INTO siswa (nama, kelas, jenjang, nominal, donatur, mamin, tanggal_masuk) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nama, $kelas, $jenjang, $nominal, $donatur, $mamin, $tanggal_masuk]);

    header("Location: list.php?notif=tambah");
    exit();
} else {
    echo "Data tidak lengkap.";
}
?>

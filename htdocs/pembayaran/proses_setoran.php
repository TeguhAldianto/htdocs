// Proses simpan setoran
<?php
if (isset($_POST['simpan_setoran'])) {
    $tanggal_setoran = $_POST['tanggal_setoran'];
    $total_setoran = $_POST['total_setoran'];
    $pembayaran_ids = isset($_POST['pembayaran_id']) ? $_POST['pembayaran_id'] : [];

    if (count($pembayaran_ids) > 0) {
        $stmt = $db->prepare("INSERT INTO setoran (tanggal, jumlah) VALUES (?, ?)");
        $stmt->execute([$tanggal_setoran, $total_setoran]);
        $setoran_id = $db->lastInsertId();

        foreach ($pembayaran_ids as $p_id) {
            $stmt = $db->prepare("UPDATE pembayaran SET setoran_id = ? WHERE id = ?");
            $stmt->execute([$setoran_id, $p_id]);
        }
        echo "<script>window.location='listsetoran.php?notif=sukses';</script>";
    } else {
        echo "<script>alert('Silakan pilih pembayaran yang ingin disetorkan.');</script>";
    }
}
?>
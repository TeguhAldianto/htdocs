<?php
include '../config.php';

if (!isset($_GET['id'])) {
    echo "<script>alert('ID Setoran tidak ditemukan.'); window.location='listsetoran.php';</script>";
    exit;
}

$id = $_GET['id'];

// Ambil data setoran
$setoran = $db->query("SELECT * FROM setoran WHERE id = $id")->fetch(PDO::FETCH_ASSOC);

if (!$setoran) {
    echo "<script>alert('Data setoran tidak ditemukan.'); window.location='listsetoran.php';</script>";
    exit;
}

// Ambil semua pembayaran yang masuk ke setoran ini
$pembayaran_raw = $db->query("SELECT * FROM pembayaran WHERE setoran_id = $id")->fetchAll(PDO::FETCH_ASSOC);

// Daftar kelas resmi
$kelas_list = ['KB', 'OA', 'OB', 'I', 'II', 'III', 'IV', 'V', 'VI'];

// Inisialisasi data rekap agar tidak ada key yang kosong
$rekap = [];
foreach ($kelas_list as $kls) {
    $rekap[$kls] = ['pembayaran' => 0, 'mamin' => 0];
}

// Tambahkan kategori 'LAINNYA' untuk menampung data yang tidak sesuai list
$rekap['LAINNYA'] = ['pembayaran' => 0, 'mamin' => 0];

foreach ($pembayaran_raw as $p) {
    // Trim dan Uppercase untuk mencocokkan data
    $kls_input = strtoupper(trim($p['kelas']));
    
    // Cek apakah kelas ada di list, jika tidak masukkan ke 'LAINNYA'
    if (array_key_exists($kls_input, $rekap)) {
        $rekap[$kls_input]['pembayaran'] += (int)$p['jumlah'];
        $rekap[$kls_input]['mamin'] += (int)$p['mamin'];
    } else {
        $rekap['LAINNYA']['pembayaran'] += (int)$p['jumlah'];
        $rekap['LAINNYA']['mamin'] += (int)$p['mamin'];
    }
}

// Hitung total keseluruhan
$grand_total = 0;
$grand_mamin = 0;

foreach ($rekap as $data) {
    $grand_total += $data['pembayaran'];
    $grand_mamin += $data['mamin'];
}

$grand_setoran = $grand_total - $grand_mamin;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Cetak Setoran #<?= $id ?></title>
    <style>
        @media print {
            @page { size: A5 portrait; margin: 30px; }
            body { font-size: 11pt; }
            .no-print { display: none; }
        }
        body { font-family: 'Arial', sans-serif; margin: 20px; line-height: 1.4; }
        h1 { font-size: 18pt; margin-bottom: 5px; }
        table { border-collapse: collapse; width: 100%; margin: 15px 0; }
        th, td { border: 1px solid black; padding: 6px; text-align: center; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; padding-right: 10px; }
        .ttd { margin-top: 30px; float: right; text-align: center; width: 180px; }
        .info-header { margin-bottom: 10px; }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.location.href='listsetoran.php'" style="padding: 8px 16px; cursor:pointer;">← Kembali ke List</button>
    </div>

    <?php 
    $stmt = $db->prepare("SELECT id FROM setoran ORDER BY id ASC");
    $stmt->execute();
    $setoran_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $no_setoran = array_search($id, $setoran_list) + 1;
    ?>

    <div class="info-header">
        <h1>SETORAN #<?= $no_setoran ?></h1>
        <strong>Tanggal:</strong> <?= date('d-m-Y', strtotime($setoran['tanggal'])) ?>
    </div>

    <table>
        <thead>
            <tr>
                <th>Kelas</th>
                <th>Total Pembayaran</th>
                <th>Mamin</th>
                <th>Setoran</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            // Gabungkan list kelas untuk looping tampilan
            $tampil_kelas = $kelas_list;
            if ($rekap['LAINNYA']['pembayaran'] > 0) $tampil_kelas[] = 'LAINNYA';

            foreach ($tampil_kelas as $kelas) :
                $pembayaran_val = $rekap[$kelas]['pembayaran'];
                $mamin_val = $rekap[$kelas]['mamin'];
                $setoran_kelas = $pembayaran_val - $mamin_val;
            ?>
                <tr>
                    <td><strong><?= $kelas ?></strong></td>
                    <td class="text-right">Rp <?= number_format($pembayaran_val, 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($mamin_val, 0, ',', '.') ?></td>
                    <td class="text-right">Rp <?= number_format($setoran_kelas, 0, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="font-weight: bold; background: #eee;">
                <td>TOTAL</td>
                <td class="text-right">Rp <?= number_format($grand_total, 0, ',', '.') ?></td>
                <td class="text-right">Rp <?= number_format($grand_mamin, 0, ',', '.') ?></td>
                <td class="text-right">Rp <?= number_format($grand_setoran, 0, ',', '.') ?></td>
            </tr>
        </tbody>
    </table>

    <p style="font-size: 12pt;"><strong>Total Setoran: Rp <?= number_format($grand_setoran, 0, ',', '.') ?></strong></p>

    <div class="ttd">
        <p>Mengetahui,</p>
        <div style="margin-top: 60px;"></div>
        <p>( ___________________ )</p>
    </div>
</body>
</html>
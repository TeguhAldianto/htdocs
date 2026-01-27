<?php
// detail_mamin.php

include '../config.php';
include '../templates/sidebar.php';

// Ambil id setoran dari URL
$id_setoran = isset($_GET['id']) ? intval($_GET['id']) : 0;
$filter_kelas = $_GET['kelas'] ?? ''; // filter kelas

// Ambil data setoran
$stmt = $db->prepare("
    SELECT id, tanggal, total_mamin, jumlah_transaksi
    FROM setoran_mamin
    WHERE id = ?
");
$stmt->execute([$id_setoran]);
$setoran = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$setoran) {
    die("<h3 class='text-center text-danger' style='margin-top:100px;'>Data setoran tidak ditemukan.</h3>");
}

$kelasList = ['KB','OA','OB'];

// Ambil detail + nama siswa
$query = "
    SELECT dm.bulan, dm.nominal, s.nama, s.kelas
    FROM detail_mamin dm
    LEFT JOIN siswa s ON dm.id_siswa = s.id
    WHERE dm.id_setoran = ?
";

$params = [$id_setoran];

if ($filter_kelas !== '') {
    $query .= " AND s.kelas = ? ";
    $params[] = $filter_kelas;
}

$query .= " ORDER BY dm.id ASC";

$stmt2 = $db->prepare($query);
$stmt2->execute($params);
$detail = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// Hitung subtotal
$subtotal = 0;
foreach ($detail as $d) {
    $subtotal += $d['nominal'];
}

// Ambil total per kelas untuk ringkasan
$stmt3 = $db->prepare("
    SELECT s.kelas, SUM(dm.nominal) AS total_kelas
    FROM detail_mamin dm
    LEFT JOIN siswa s ON dm.id_siswa = s.id
    WHERE dm.id_setoran = ?
    GROUP BY s.kelas
    ORDER BY s.kelas ASC
");
$stmt3->execute([$id_setoran]);
$total_kelas_data = $stmt3->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Setoran Mamin #<?= $id_setoran ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        :root {
            --primary: #4f46e5;
            --dark: #0f172a;
            --bg-light: #f8fafc;
        }
        
        /* Card Info Styling */
        .info-card {
            background: white;
            border: none;
            border-radius: 20px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .info-card:hover { transform: translateY(-5px); }
        .icon-box {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 15px;
        }

        
        .subtotal-row {
            position: sticky;
    bottom: 0;
    z-index: 2;
    background-color: #f8fafc; /* Warna background agar tidak tembus */
    box-shadow: 0 -2px 5px rgba(0,0,0,0.05); /* Efek bayangan agar terlihat terpisah */

        }

        .filter-section {
            background: white;
            padding: 15px;
            border-radius: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .table-responsive {
             max-height: 70vh; /* Agar ada scroll internal jika data banyak */
    overflow-x: auto;
        }
    </style>
</head>
<body>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-800 mb-1">Detail Setoran Mamin</h2>
            <p class="text-muted"><i class="fas fa-receipt me-2"></i>ID Setoran: #<?= $id_setoran ?></p>
        </div>
        <a href="mamin.php" class="btn btn-outline-dark fw-bold px-4" style="border-radius: 12px;">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="info-card card h-100 p-4">
                <div class="icon-box bg-primary text-white">
                    <i class="fas fa-info-circle"></i>
                </div>
                <h5 class="fw-700 mb-3">Informasi Setoran</h5>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Tanggal:</span>
                    <span class="fw-bold"><?= date('d F Y', strtotime($setoran['tanggal'])) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Keseluruhan:</span>
                    <span class="fw-bold text-primary" style="font-size: 1.1rem;">Rp <?= number_format($setoran['total_mamin'], 0, ',', '.') ?></span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">Jumlah Transaksi:</span>
                    <span class="badge bg-dark px-3 py-2" style="border-radius: 8px;"><?= $setoran['jumlah_transaksi'] ?> Data</span>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="info-card card h-100 p-4 border-start border-4 border-warning">
                <div class="icon-box bg-warning text-dark">
                    <i class="fas fa-chart-pie"></i>
                </div>
                <h5 class="fw-700 mb-3">Total per Kelas</h5>
                <div class="row">
                    <?php if (!empty($total_kelas_data)): ?>
                        <?php foreach ($total_kelas_data as $tk): ?>
                            <div class="col-6 mb-2">
                                <small class="text-muted d-block"><?= htmlspecialchars($tk['kelas']) ?></small>
                                <span class="fw-bold text-dark">Rp <?= number_format($tk['total_kelas'], 0, ',', '.') ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="text-muted">Data kelas tidak tersedia.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="filter-section d-flex align-items-center">
        <i class="fas fa-filter text-muted me-3"></i>
        <form method="GET" class="flex-grow-1">
            <input type="hidden" name="id" value="<?= $id_setoran ?>">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <select name="kelas" class="form-select border-0 bg-light" onchange="this.form.submit()" style="border-radius: 10px;">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k ?>" <?= ($filter_kelas == $k ? 'selected' : '') ?>>Kelas <?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php if ($filter_kelas): ?>
                <div class="col-md-2">
                    <a href="detail_mamin.php?id=<?= $id_setoran ?>" class="text-danger small fw-bold text-decoration-none">
                        <i class="fas fa-times me-1"></i>Reset Filter
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="card-table mt-4">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="text-center">
                        <th class="text-start" style="width: 40%;">Nama Siswa</th>
                        <th style="width: 15%;">Kelas</th>
                        <th style="width: 20%;">Bulan</th>
                        <th class="text-end" style="width: 25%;">Nominal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($detail)): ?>
                        <?php foreach ($detail as $d): ?>
                        <tr>
                            <td class="fw-bold"><?= htmlspecialchars($d['nama']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-3 py-2" style="border-radius: 8px;">
                                    <?= htmlspecialchars($d['kelas']) ?>
                                </span>
                            </td>
                            <td class="text-center text-muted"><?= htmlspecialchars($d['bulan']) ?></td>
                            <td class="text-end fw-bold text-primary">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Tidak ada data pembayaran untuk kriteria ini.</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr class="subtotal-row">
                        <td colspan="3" class="text-end fw-800 py-3">SUBTOTAL <?= $filter_kelas ? "KELAS $filter_kelas" : "KESELURUHAN" ?></td>
                        <td class="text-end fw-800 py-3" style="font-size: 1.1rem;">
                            Rp <?= number_format($subtotal, 0, ',', '.') ?>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>

</body>
</html>
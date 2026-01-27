<?php
include '../config.php';
include '../templates/sidebar.php';

// Ambil data setoran berdasarkan ID
if (isset($_GET['id'])) {
    $id = (int)$_GET['id']; // Pastikan ID adalah integer

    $stmt = $db->prepare("SELECT * FROM setoran WHERE id = ?");
    $stmt->execute([$id]);
    $setoran = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$setoran) {
        echo "<script>alert('Data setoran tidak ditemukan.'); window.location='listsetoran.php';</script>";
        exit;
    }

    $kelas_list = ['KB', 'OA', 'OB', 'I', 'II', 'III', 'IV', 'V', 'VI'];

    // Inisialisasi rekap dengan nol
    $rekap = [];
    foreach ($kelas_list as $kls) {
        $rekap[$kls] = [
            'total_pembayaran' => 0,
            'total_mamin' => 0,
            'setoran_bersih' => 0
        ];
    }

    /* PERBAIKAN UTAMA: 
       Kita ambil semua pembayaran milik setoran_id ini tanpa GROUP BY terlebih dahulu 
       untuk memastikan datanya benar-benar "tertangkap" oleh PHP.
    */
    $stmt = $db->prepare("SELECT kelas, jumlah, mamin FROM pembayaran WHERE setoran_id = ?");
    $stmt->execute([$id]);
    $semua_pembayaran = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Proses perhitungan di sisi PHP (lebih akurat jika database punya karakter aneh)
    foreach ($semua_pembayaran as $p) {
        $kls_db = strtoupper(trim($p['kelas']));
        if (isset($rekap[$kls_db])) {
            $rekap[$kls_db]['total_pembayaran'] += (int)$p['jumlah'];
            $rekap[$kls_db]['total_mamin'] += (int)$p['mamin'];
            $rekap[$kls_db]['setoran_bersih'] += ((int)$p['jumlah'] - (int)$p['mamin']);
        }
    }
} else {
    echo "<script>alert('ID tidak valid.'); window.location='listsetoran.php';</script>";
    exit;
}

// Ambil daftar siswa untuk setoran ini
$stmt = $db->prepare("
    SELECT s.nama, p.kelas, p.bulan, p.jumlah, p.mamin
    FROM pembayaran p
    LEFT JOIN siswa s ON p.siswa_id = s.id
    WHERE p.setoran_id = ?
    ORDER BY p.kelas, s.nama
");
$stmt->execute([$id]);
$daftar_siswa = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil urutan setoran
$stmt = $db->prepare("SELECT id FROM setoran ORDER BY id ASC");
$stmt->execute();
$setoran_list = $stmt->fetchAll(PDO::FETCH_COLUMN);
$no_setoran = array_search($id, $setoran_list) + 1;
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Detail Setoran #<?= $no_setoran ?></title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        /* Header & Cards */
        .page-header {
            margin-bottom: 30px;
        }

        .summary-card {
            background: white;
            border: none;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-color: #f1f5f9;
        }

        /* Highlight Total */
        .total-row {
            background-color: #0f172a !important;
            color: white !important;
        }

        .total-row td {
            border: none !important;
        }

        .amount-highlight {
            color: #fbbf24;
            font-weight: 800;
            font-size: 1.25rem;
        }

        /* Modal Customization */
        .modal-content {
            border-radius: 24px;
            border: none;
        }

        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 25px;
        }
    </style>
</head>

<body>

    <div class="main-content">
        <div class="page-header d-flex justify-content-between align-items-end">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-2">
                        <li class="breadcrumb-item"><a href="listsetoran.php" class="text-decoration-none">Daftar Setoran</a></li>
                        <li class="breadcrumb-item active">Detail #<?= $no_setoran ?></li>
                    </ol>
                </nav>
                <h2 class="fw-800 mb-0">Setoran Ke-<?= $no_setoran ?></h2>
                <p class="text-muted"><i class="far fa-calendar-alt me-2"></i>Tanggal: <strong><?= date('d F Y', strtotime($setoran['tanggal'])) ?></strong></p>
            </div>
            <div class="actions">
                <button class="btn btn-dark fw-bold px-4 py-2 me-2" style="border-radius: 12px;" data-bs-toggle="modal" data-bs-target="#modalDaftarSiswa">
                    <i class="fas fa-users me-2"></i>Daftar Siswa
                </button>
                <a href="cetak_laporan.php?id=<?= $setoran['id'] ?>" class="btn btn-primary fw-bold px-4 py-2" style="border-radius: 12px;">
                    <i class="fas fa-print me-2"></i>Cetak Laporan
                </a>
            </div>
        </div>

        <div class="card-table">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 80px;">No</th>
                            <th>Kelas</th>
                            <th class="text-end">Total Pembayaran</th>
                            <th class="text-end">Total Mamin</th>
                            <th class="text-end">Setoran Bersih</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        $grand_total_pembayaran = 0;
                        $grand_total_mamin = 0;
                        $grand_total_bersih = 0;

                        foreach ($kelas_list as $kls) {
                            $total_pembayaran = $rekap[$kls]['total_pembayaran'];
                            $total_mamin = $rekap[$kls]['total_mamin'];
                            $setoran_bersih = $rekap[$kls]['setoran_bersih'];

                            $grand_total_pembayaran += $total_pembayaran;
                            $grand_total_mamin += $total_mamin;
                            $grand_total_bersih += $setoran_bersih;
                        ?>
                            <tr>
                                <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                                <td><span class="badge bg-light text-dark px-3 py-2 fw-bold" style="font-size: 0.9rem; border-radius: 8px;"><?= $kls ?></span></td>
                                <td class="text-end">Rp <?= number_format($total_pembayaran, 0, ',', '.') ?></td>
                                <td class="text-end text-danger">Rp <?= number_format($total_mamin, 0, ',', '.') ?></td>
                                <td class="text-end fw-bold text-primary">Rp <?= number_format($setoran_bersih, 0, ',', '.') ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2" class="text-center fw-800 py-4">GRAND TOTAL</td>
                            <td class="text-end py-4">Rp <?= number_format($grand_total_pembayaran, 0, ',', '.') ?></td>
                            <td class="text-end py-4">Rp <?= number_format($grand_total_mamin, 0, ',', '.') ?></td>
                            <td class="text-end py-4"><span class="amount-highlight">Rp <?= number_format($grand_total_bersih, 0, ',', '.') ?></span></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalDaftarSiswa" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl modal-dialog-centered">
                <div class="modal-content shadow-lg">
                    <div class="modal-header">
                        <div>
                            <h5 class="modal-title fw-800">Rincian Siswa</h5>
                            <p class="text-muted small mb-0">Daftar siswa yang termasuk dalam setoran #<?= $no_setoran ?></p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="table-responsive" style="max-height: 65vh;">
                            <table class="table table-striped table-hover mb-0">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Nama Lengkap</th>
                                        <th class="text-center">Kelas</th>
                                        <th class="text-center">Bulan</th>
                                        <th class="text-end">Pembayaran</th>
                                        <th class="text-end">Mamin</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($daftar_siswa): $no_s = 1; ?>
                                        <?php foreach ($daftar_siswa as $s): ?>
                                            <tr>
                                                <td class="text-center fw-bold text-muted"><?= $no_s++ ?></td>
                                                <td class="fw-bold"><?= htmlspecialchars($s['nama']) ?></td>
                                                <td class="text-center"><span class="badge bg-secondary"><?= $s['kelas'] ?></span></td>
                                                <td class="text-center"><?= $s['bulan'] ?></td>
                                                <td class="text-end fw-bold">Rp <?= number_format($s['jumlah'], 0, ',', '.') ?></td>
                                                <td class="text-end text-danger">Rp <?= number_format($s['mamin'], 0, ',', '.') ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-5">Tidak ada data rincian siswa.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary fw-bold px-4" data-bs-dismiss="modal" style="border-radius: 10px;">Tutup</button>
                    </div>
                </div>
            </div>
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
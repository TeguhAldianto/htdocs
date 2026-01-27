<?php
session_start();
date_default_timezone_set('Asia/Jakarta');
function bulanIndo($date) {
    $bulan = [
        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
        '04' => 'April', '05' => 'Mei', '06' => 'Juni',
        '07' => 'Juli', '08' => 'Agustus', '09' => 'September', 
        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
    ];
    $m = date('m', strtotime($date));
    $y = date('Y', strtotime($date));
    return $bulan[$m] . " " . $y;
}
// Jika user belum login, tendang kembali ke login.php
if (!isset($_SESSION['login']) || $_SESSION['login'] !== true) {
    header("Location: login.php");
    exit;
}

include 'config.php';
include 'templates/sidebar.php';

// --- 1. LOGIKA DATA REKAP ---
$tanggalHariIni = date('Y-m-d');
$bulanDipilih   = isset($_GET['filter_bulan']) ? $_GET['filter_bulan'] : date('Y-m');

// Rekap Hari Ini
$rekapHariIni = $db->prepare("SELECT COUNT(*) as jml, SUM(jumlah) as total FROM pembayaran WHERE tanggal = ?");
$rekapHariIni->execute([$tanggalHariIni]);
$rekapH = $rekapHariIni->fetch();

// Rekap Bulan Terpilih
$rekapBulanIni = $db->prepare("SELECT COUNT(*) as jml, SUM(jumlah) as total FROM pembayaran WHERE tanggal LIKE ?");
$rekapBulanIni->execute(["$bulanDipilih%"]);
$rekapB = $rekapBulanIni->fetch();


// --- 2. RINGKASAN DATA (BOX ATAS) ---
$jumlahTK          = $db->query("SELECT COUNT(*) FROM siswa WHERE LOWER(kelas) IN ('kb', 'oa', 'ob')")->fetchColumn();
$jumlahSD          = $db->query("SELECT COUNT(*) FROM siswa WHERE LOWER(kelas) NOT IN ('kb', 'oa', 'ob')")->fetchColumn();
$totalPembayaran   = $db->query("SELECT SUM(jumlah) FROM pembayaran")->fetchColumn() ?? 0;
$totalMamin        = $db->query("SELECT SUM(mamin) FROM pembayaran")->fetchColumn() ?? 0;
$totalSetoran      = $db->query("SELECT SUM(jumlah) FROM setoran")->fetchColumn() ?? 0;
$totalSetoranMamin = $db->query("SELECT SUM(total_mamin) FROM setoran_mamin")->fetchColumn() ?? 0;
$saldoKas          = ($totalPembayaran) - ($totalSetoran + $totalSetoranMamin);


// --- 3. GRAFIK BULANAN (TAHUN AKADEMIK JUL-JUN) ---
$bulanLabel = ['Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
$grafikData = [];
$tahunSekarang = date('Y');
$bulanSekarang = date('m');

if ($bulanSekarang >= 7) {
    $thAwal  = $tahunSekarang;
    $thAkhir = $tahunSekarang + 1;
} else {
    $thAwal  = $tahunSekarang - 1;
    $thAkhir = $tahunSekarang;
}

$periode = [
    ['m' => '07', 'y' => $thAwal], ['m' => '08', 'y' => $thAwal], ['m' => '09', 'y' => $thAwal],
    ['m' => '10', 'y' => $thAwal], ['m' => '11', 'y' => $thAwal], ['m' => '12', 'y' => $thAwal],
    ['m' => '01', 'y' => $thAkhir], ['m' => '02', 'y' => $thAkhir], ['m' => '03', 'y' => $thAkhir],
    ['m' => '04', 'y' => $thAkhir], ['m' => '05', 'y' => $thAkhir], ['m' => '06', 'y' => $thAkhir]
];

foreach ($periode as $p) {
    $stmt = $db->prepare("SELECT SUM(jumlah) FROM pembayaran WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ?");
    $stmt->execute([$p['m'], $p['y']]);
    $nilai = $stmt->fetchColumn();
    $grafikData[] = $nilai ? (int)$nilai : 0;
}
// Siapkan daftar opsi untuk dropdown
$opsiBulan = [];
foreach ($periode as $p) {
    $val = $p['y'] . '-' . $p['m']; // Format: 2024-07
    $namaBulan = date('F Y', strtotime($val . "-01")); // Contoh: July 2024
    $opsiBulan[] = [
        'value' => $val,
        'label' => $namaBulan
    ];
}

// --- 4. PERFORMA & TRANSAKSI TERBARU ---
$queryTarget    = $db->query("SELECT SUM(nominal-donatur) as total_target FROM siswa WHERE status = 'Aktif'")->fetch();
$targetBulanan  = $queryTarget['total_target'] ?? 0;
$realisasiBulanIni = $rekapB['total'] ?? 0;
$persenPerforma = ($targetBulanan > 0) ? ($realisasiBulanIni / $targetBulanan) * 100 : 0;
$widthBar       = ($persenPerforma > 100) ? 100 : $persenPerforma;

// Ambil tanggal hari ini dalam format YYYY-MM-DD
$hari_ini = date('Y-m-d');

$terbaru = $db->prepare("SELECT p.*, s.nama, s.kelas 
                        FROM pembayaran p 
                        JOIN siswa s ON p.siswa_id = s.id 
                        WHERE DATE(p.tanggal) = ? 
                        ORDER BY p.id DESC");
$terbaru->execute([$hari_ini]);
$terbaru = $terbaru->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySPP - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/style.css">
   <style>
        :root { --primary-grad: linear-gradient(45deg, #0d6efd, #0043a8); }
        body { background-color: #f4f7f6; font-family: 'Inter', sans-serif; }
        .card { border: none; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .card-stats { transition: 0.3s; color: white; }
        .card-stats:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        .icon-circle { width: 50px; height: 50px; background: rgba(255,255,255,0.2); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .table-modern thead th { background: #f8f9fa; border: none; color: #888; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; }
        .bg-indigo { background-color: #6610f2; }
        .progress { border-radius: 10px; background-color: rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="main-content">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="fw-bold text-dark"><i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard</h2>
            <p class="text-muted">Ringkasan statistik dan performa keuangan sekolah</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card card-stats bg-primary p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-75 small">Total Siswa</p>
                        <h3 class="fw-bold mb-0"><?= $jumlahTK + $jumlahSD ?></h3>
                    </div>
                    <div class="icon-circle"><i class="bi bi-people"></i></div>
                </div>
                <div class="mt-3 small opacity-75">TK: <?= $jumlahTK ?> | SD: <?= $jumlahSD ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats bg-success p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-75 small">Total SPP</p>
                        <h4 class="fw-bold mb-0">Rp<?= number_format($totalPembayaran, 0, ',', '.') ?></h4>
                    </div>
                    <div class="icon-circle"><i class="bi bi-wallet2"></i></div>
                </div>
                <div class="mt-3 small opacity-75">Akumulasi Seluruh Siswa</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats bg-warning text-dark p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-75 small font-weight-bold">Total Mamin</p>
                        <h4 class="fw-bold mb-0">Rp<?= number_format($totalMamin, 0, ',', '.') ?></h4>
                    </div>
                    <div class="icon-circle bg-dark bg-opacity-10 text-dark"><i class="bi bi-cup-hot"></i></div>
                </div>
                <div class="mt-3 small opacity-75">Khusus Jenjang TK</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats bg-dark p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="mb-1 opacity-75 small">Kas Bendahara</p>
                        <h4 class="fw-bold mb-0 text-info">Rp<?= number_format($saldoKas, 0, ',', '.') ?></h4>
                    </div>
                    <div class="icon-circle text-info"><i class="bi bi-safe2"></i></div>
                </div>
                <div class="mt-3 small opacity-75">Dana Belum Disetorkan</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="fw-bold m-0" style="letter-spacing: 1px;">Grafik Pembayaran Per Bulan</h6>
                        <span class="badge bg-light text-primary border px-3">Tahun Ajaran <?= $thAwal ?>/<?= $thAkhir ?></span>
                    </div>
                    <canvas id="grafikSetoran" style="max-height: 315px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100 overflow-hidden">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold m-0" style="letter-spacing: 1px;">Analisis Bulan:</h6>
                       <form action="" method="GET" id="filterForm">
                            <select name="filter_bulan" class="form-select form-select-sm border-0 bg-light fw-bold" onchange="this.form.submit()">
                                <?php foreach ($periode as $p): 
                                    $val = $p['y'] . '-' . $p['m']; 
                                ?>
                                    <option value="<?= $val ?>" <?= ($bulanDipilih == $val) ? 'selected' : '' ?>>
                                        <?= bulanIndo($val) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <p class="text-muted small mb-1">Total Realisasi Pembayaran</p>
                        <h2 class="fw-bold text-primary">Rp <?= number_format($rekapB['total'] ?? 0, 0, ',', '.') ?></h2>
                        <span class="badge bg-success bg-opacity-10 text-success rounded-pill">
                            <i class="bi bi-check-circle-fill me-1"></i> <?= $rekapB['jml'] ?> Transaksi Sukses
                        </span>
                    </div>

                    <hr class="my-4 opacity-50">

                    <div class="mb-2 d-flex justify-content-between align-items-end">
                        <p class="text-muted small mb-0 font-weight-bold">PERFORMA TARGET</p>
                        <h4 class="fw-bold mb-0 <?= ($persenPerforma >= 100) ? 'text-success' : 'text-primary' ?>"><?= number_format($persenPerforma, 1) ?>%</h4>
                    </div>
                    
                    <div class="progress mb-3" style="height: 12px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated <?= ($persenPerforma >= 100) ? 'bg-success' : 'bg-primary' ?>" 
                             role="progressbar" style="width: <?= $widthBar ?>%;"></div>
                    </div>

                    <div class="bg-light p-3 rounded-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Target SPP:</span>
                            <span class="small fw-bold">Rp <?= number_format($targetBulanan, 0, ',', '.') ?></span>
                        </div>
                        <?php if($realisasiBulanIni < $targetBulanan): ?>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Kekurangan:</span>
                            <span class="small text-danger fw-bold">-Rp <?= number_format($targetBulanan - $realisasiBulanIni, 0, ',', '.') ?></span>
                        </div>
                        <?php else: ?>
                        <div class="text-center py-1">
                            <span class="badge bg-success w-100">Target Tercapai! <i class="bi bi-trophy-fill ms-1"></i></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <div class="p-4 d-flex justify-content-between align-items-center border-bottom">
                        <h6 class="fw-bold m-0">Aktivitas Transaksi Terbaru</h6>
                        <a href="../pembayaran/listpembayaran.php" class="btn btn-sm btn-outline-primary px-3 rounded-pill">Lihat Semua</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-modern table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Bulan Tagihan</th>
                                    <th>Jumlah Bayar</th>
                                    <th class="text-center">Waktu Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($terbaru)): ?>
                                    <tr><td colspan="5" class="text-center p-5 text-muted">Belum ada aktivitas transaksi</td></tr>
                                <?php else: ?>
                                    <?php foreach ($terbaru as $t): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold"><?= $t['nama'] ?></td>
                                        <td><span class="badge bg-light text-dark border-0"><?= $t['kelas'] ?></span></td>
                                        <td><span class="text-primary"><?= $t['bulan'] ?></span></td>
                                        <td class="fw-bold text-success">Rp <?= number_format($t['jumlah'], 0, ',', '.') ?></td>
                                        <td class="text-center text-muted small"><?= date('d/m/Y', strtotime($t['tanggal'])) ?></td>
                                    </tr>
                                    <?php endforeach ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
window.onload = function () {
    const ctx = document.getElementById('grafikSetoran').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(13, 110, 253, 0.2)');
    gradient.addColorStop(1, 'rgba(13, 110, 253, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($bulanLabel) ?>,
            datasets: [{
                label: 'Pembayaran SPP',
                data: <?= json_encode($grafikData, JSON_NUMERIC_CHECK) ?>,
                backgroundColor: gradient,
                borderColor: '#0d6efd',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { 
                    beginAtZero: true, 
                    grid: { color: '#f0f0f0' },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + (value >= 1000000 ? (value/1000000) + 'jt' : (value/1000) + 'rb');
                        }
                    }
                },
                x: { grid: { display: false } }
            }
        }
    });
};
</script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');

    if (status === 'restore_success') {
        Swal.fire('Restorasi Berhasil!', 'Data sistem telah dipulihkan.', 'success');
    } else if (status === 'reset_success') {
        Swal.fire('Reset Berhasil!', 'Sistem telah dikosongkan untuk tahun ajaran baru.', 'success');
    } else if (status === 'error') {
        Swal.fire('Gagal!', 'Terjadi kesalahan saat memproses data.', 'error');
    }

    // Membersihkan URL dari parameter setelah alert muncul
    window.history.replaceState({}, document.title, window.location.pathname);
</script>
</body>
</html>
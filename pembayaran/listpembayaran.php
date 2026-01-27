<?php
include '../config.php';
include '../templates/sidebar.php';
date_default_timezone_set('Asia/Jakarta');
// 1. Ambil data dengan Join
$query = $db->query("
    SELECT p.*, s.nama, s.kelas 
    FROM pembayaran p
    JOIN siswa s ON p.siswa_id = s.id 
    ORDER BY p.id DESC
");
$pembayaran = $query->fetchAll(PDO::FETCH_ASSOC);

// 2. Hitung statistik ringkas
$total_hari_ini = 0;
$total_pending = 0;
$hari_ini = date('Y-m-d');

foreach ($pembayaran as $p) {
    if (date('Y-m-d', strtotime($p['tanggal'])) == $hari_ini) {
        $total_hari_ini += $p['jumlah'];
    }
    if (!$p['setoran_id']) {
        $total_pending++;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySPP - List Pembayaran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        /* Loading Screen Overlay */
#loader-wrapper {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #ffffff;
    z-index: 9999;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    transition: opacity 0.5s ease, visibility 0.5s ease;
}

.loader-spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #0d6efd;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.loader-text {
    margin-top: 15px;
    font-family: 'Inter', sans-serif;
    font-weight: 600;
    color: #475569;
}

/* Sembunyikan content utama sementara agar tidak berantakan saat load */
body.loading {
    overflow: hidden;
}
        .table-responsive {
    max-height: 70vh; /* Agar ada scroll internal jika data banyak */
    overflow-x: auto;
}

        /* Cards & Components */
        .card-stats { border: none; border-radius: 16px; transition: transform 0.2s; }
        .card-stats:hover { transform: translateY(-5px); }
        
        
        .row-pending { background-color: var(--pending-warn) !important; }
        .row-pending:hover { background-color: #fdf2d0 !important; }
        
        /* Navigation Tabs */
        .nav-custom { background: #e2e8f0; padding: 5px; border-radius: 12px; display: inline-flex; }
        .nav-custom .nav-link { border-radius: 10px; color: #64748b; font-weight: 500; border: none; padding: 8px 20px; }
        .nav-custom .nav-link.active { background: white; color: #0d6dfd; shadow: 0 2px 4px rgba(0,0,0,0.05); }

    </style>
</head>
<body style="background-color: #f1f4f9;">
    <body style="background-color: #f1f4f9;" class="loading">

<div id="loader-wrapper">
    <div class="loader-spinner"></div>
    <div class="loader-text">Menyiapkan Data...</div>
</div>
<div class="main-content">
    <div class="row align-items-center mb-4">
        <div class="col-md-6 text-center text-md-start mb-3 mb-md-0">
        <h2 class="fw-bold text-dark"><i class="fas fa-credit-card me-2 text-primary"></i> Riwayat Pembayaran</h2>
            <p class="text-muted">Catat transaksi masuk dan pantau riwayat pembayaran siswa</p>
            <p class="text-muted mb-0">Total <span class="badge bg-primary rounded-pill"><?= count($pembayaran) ?></span> transaksi tercatat</p>
        </div>
        <div class="col-md-3">
            <div class="card card-stats shadow-sm mb-2 mb-md-0">
                <div class="card-body py-3 d-flex align-items-center">
                    <div class="bg-primary-subtle p-2 rounded-3 me-3"><i class="bi bi-cash-stack text-primary fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Hari Ini</small>
                        <span class="fw-bold">Rp <?= number_format($total_hari_ini, 0, ',', '.') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card card-stats shadow-sm">
                <div class="card-body py-3 d-flex align-items-center">
                    <div class="bg-warning-subtle p-2 rounded-3 me-3"><i class="bi bi-clock-history text-warning fs-4"></i></div>
                    <div>
                        <small class="text-muted d-block">Belum Setor</small>
                        <span class="fw-bold text-warning"><?= $total_pending ?> Transaksi</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4" style="border-radius: 15px;">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-lg-4">
                    <div class="nav nav-custom w-100">
                        <button class="nav-link active w-100" onclick="filterTab('semua', this)">Semua</button>
                        <button class="nav-link w-100" onclick="filterTab('TK', this)">Unit TK</button>
                        <button class="nav-link w-100" onclick="filterTab('SD', this)">Unit SD</button>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search"></i></span>
                        <input type="text" id="searchInput" onkeyup="filterData()" class="form-control border-0 bg-light" placeholder="Cari nama atau kelas...">
                    </div>
                </div>
                <div class="col-lg-4 text-lg-end d-flex gap-2 justify-content-lg-end">
                  <button class="btn btn-primary px-3" data-bs-toggle="modal" data-bs-target="#modalInputPembayaran">
                        <i class="bi bi-keyboard me-1"></i> Input Pembayaran
                    </button>
                    <button class="btn btn-warning px-3" data-bs-toggle="modal" data-bs-target="#modalInputPembayaranManual">
                        <i class="bi bi-keyboard me-1"></i> Input Manual
                    </button>
                    <a href="export_pembayaran.php" class="btn btn-success px-3">
                        <i class="bi bi-file-earmark-excel me-1"></i> Excel
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="card-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="pembayaranTable">
                <thead>
                    <tr class="text-center">
                        <th width="5%">No</th>
                        <th class="text-start">Siswa & Unit</th>
                        <th>Kelas</th>
                        <th>Bulan</th>
                        <th class="text-end">Jumlah</th>
                        <th>Status</th>
                        <th width="10%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1; 
                    foreach ($pembayaran as $p): 
                        $isSetor = $p['setoran_id'];
                        $jenjang = in_array(strtolower($p['kelas']), ['kb', 'oa', 'ob']) ? 'TK' : 'SD';
                    ?>
                    <tr data-jenjang="<?= $jenjang ?>" class="<?= !$isSetor ? 'row-pending' : '' ?>">
                        <td class="text-center text-muted small"><?= $no++ ?></td>
                        <td>
                            <div class="fw-bold text-dark mb-0"><?= strtoupper(htmlspecialchars($p['nama'])) ?></div>
                            <small class="text-muted"><?= date('d/m/Y', strtotime($p['tanggal'])) ?> • Unit <?= $jenjang ?></small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-white text-dark border px-2 py-1"><?= htmlspecialchars($p['kelas']) ?></span>
                        </td>
                        <td class="text-center text-secondary fw-medium"><?= htmlspecialchars($p['bulan']) ?></td>
                        <td class="text-end pe-3 fw-bold text-primary">
                            Rp <?= number_format($p['jumlah'], 0, ',', '.') ?>
                        </td>
                        <td class="text-center">
                            <?php if($isSetor): ?>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-check2-circle me-1"></i>Tersetor
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-3 py-2 rounded-pill">
                                    <i class="bi bi-clock me-1"></i>Tertunda
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                <button title="Hapus" class="btn btn-sm btn-white border btn-hapus" 
                                        data-id="<?= $p['id'] ?>" data-nama="<?= htmlspecialchars($p['nama']) ?>">
                                    <i class="bi bi-trash text-danger"></i>
                                </button>
                               
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div id="emptyState" class="text-center py-5 d-none">
            <h5 class="text-muted">Data tidak ditemukan</h5>
        </div>
    </div>
</div>

<div class="modal fade" id="modalKonfirmasiHapus" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="hapus_pembayaran.php" class="modal-content border-0 shadow">
            <div class="modal-body text-center p-4">
                <i class="bi bi-exclamation-circle text-danger display-3 mb-3"></i>
                <h4 class="fw-bold">Hapus Pembayaran?</h4>
                <p class="text-muted">Anda akan menghapus data pembayaran milik <br><strong id="textNamaSiswa" class="text-dark"></strong>.</p>
                <input type="hidden" name="id" id="inputIdHapus">
            </div>
            <div class="modal-footer border-0 bg-light justify-content-center">
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger px-4">Hapus Permanen</button>
            </div>
        </form>
    </div>
</div>
<div style="position: absolute; top: 0; left: 0; width: 0; height: 0; overflow: hidden;">
    <?php 
    include 'modal_input_pembayaran.php'; 
    include 'modal_input_manual.php'; 
    ?>
</div>

<button onclick="scrollToTop()" id="btnTop"><i class="bi bi-arrow-up"></i></button>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
let currentUnit = 'semua';

function filterTab(unit, btn) {
    currentUnit = unit;
    // Update UI Tab
    document.querySelectorAll('.nav-custom .nav-link').forEach(el => el.classList.remove('active'));
    btn.classList.add('active');
    filterData();
}

function filterData() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#pembayaranTable tbody tr');
    let visibleCount = 0;

    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const unit = row.dataset.jenjang;
        
        const matchSearch = text.includes(search);
        const matchUnit = (currentUnit === 'semua' || unit === currentUnit);

        if (matchSearch && matchUnit) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    document.getElementById('emptyState').classList.toggle('d-none', visibleCount > 0);
}

// Modal Handling
document.addEventListener('click', function (e) {
    const btn = e.target.closest('.btn-hapus');
    if (btn) {
        document.getElementById('inputIdHapus').value = btn.dataset.id;
        document.getElementById('textNamaSiswa').innerText = btn.dataset.nama;
        new bootstrap.Modal(document.getElementById('modalKonfirmasiHapus')).show();
    }
});

// Scroll Function
window.onscroll = function () {
    const btn = document.getElementById("btnTop");
    btn.style.display = (window.scrollY > 300) ? "block" : "none";
};
function scrollToTop() { window.scrollTo({ top: 0, behavior: 'smooth' }); }
    // Menghilangkan loader setelah seluruh halaman (DOM + Script + Image) selesai dimuat
window.addEventListener('load', function() {
    const loader = document.getElementById('loader-wrapper');
    const body = document.body;

    // Tambahkan delay sedikit (opsional) agar transisi halus
    setTimeout(() => {
        loader.style.opacity = '0';
        loader.style.visibility = 'hidden';
        body.classList.remove('loading');
    }, 500); 
});
</script>

</body>
</html>
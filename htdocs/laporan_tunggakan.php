<?php
include 'config.php';
include 'templates/sidebar.php';

// 1. PENGATURAN BULAN ACUAN
$bulanSekarang = date('M'); 
$bulanUrut = ['Jul','Agu','Sep','Okt','Nov','Des','Jan','Feb','Mar','Apr','Mei','Jun'];
$idxSekarang = array_search($bulanSekarang, $bulanUrut);

// 2. LOGIKA FILTER
$filter_kelas = $_GET['f_kelas'] ?? '';
$filter_jenjang = $_GET['f_jenjang'] ?? '';

$where = "WHERE s.status = 'aktif'";
if ($filter_jenjang == 'TK') {
    $where .= " AND LOWER(s.kelas) IN ('kb','oa','ob')";
} elseif ($filter_jenjang == 'SD') {
    $where .= " AND LOWER(s.kelas) NOT IN ('kb','oa','ob')";
}

if ($filter_kelas != '') {
    $where .= " AND s.kelas = " . $db->quote($filter_kelas);
}

// 3. AMBIL DATA SISWA & DAFTAR KELAS UNTUK FILTER
$list_kelas = $db->query("SELECT DISTINCT kelas FROM siswa ORDER BY kelas ASC")->fetchAll(PDO::FETCH_COLUMN);

$query = "
    SELECT 
        s.id, s.nama, s.kelas, s.nominal, s.donatur,
        CASE 
            WHEN LOWER(s.kelas) IN ('kb','oa','ob') THEN 'TK'
            ELSE 'SD'
        END AS jenjang,
        (SELECT COUNT(*) FROM pembayaran WHERE siswa_id = s.id) as bulan_terbayar,
        (SELECT GROUP_CONCAT(bulan) FROM pembayaran WHERE siswa_id = s.id) as list_bulan
    FROM siswa s
    $where
    ORDER BY s.kelas ASC, s.nama ASC
";
$siswa = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Monitoring Tunggakan Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        @media print { .no-print { display: none; } }
        .table-laporan { font-size: 0.85rem; vertical-align: middle; }
        .sticky-header thead { position: sticky; top: 0; z-index: 100; }
    </style>
</head>
<body class="bg-light">

<div class="main-content p-4">
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h3 class="fw-bold m-0"><i class="bi bi-person-exclamation text-danger"></i> Laporan Tunggakan</h3>
        <div>
            <button onclick="exportToExcel('tabel-tunggakan')" class="btn btn-outline-success me-2">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </button>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="small fw-bold">Jenjang</label>
                    <select name="f_jenjang" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Jenjang --</option>
                        <option value="TK" <?= $filter_jenjang == 'TK' ? 'selected' : '' ?>>TK (KB/OA/OB)</option>
                        <option value="SD" <?= $filter_jenjang == 'SD' ? 'selected' : '' ?>>SD</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold">Kelas</label>
                    <select name="f_kelas" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Kelas --</option>
                        <?php foreach($list_kelas as $k): ?>
                            <option value="<?= $k ?>" <?= $filter_kelas == $k ? 'selected' : '' ?>><?= $k ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <a href="laporan_tunggakan.php" class="btn btn-sm btn-secondary w-100">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3">
            <h6 class="m-0 fw-bold">Data Tunggakan s/d Bulan: <span class="text-danger"><?= $bulanSekarang ?></span></h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-laporan mb-0" id="tabel-tunggakan">
                    <thead class="table-light">
                        <tr class="text-center">
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>SPP+Mamin</th>
                            <th>Bayar</th>
                            <th>Status</th>
                            <th class="bg-light text-danger">Tunggakan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $no = 1;
                        $grandTotalTunggakan = 0;
                        foreach ($siswa as $s): 
                            $sppNetto = max(($s['nominal'] - $s['donatur']), 0);
                            $mamin = ($s['jenjang'] == 'TK') ? 5000 : 0;
                            $tagihanTotal = $sppNetto + $mamin;
                            
                            // Hitung bulan berjalan (Juli = 1)
                            $targetBulan = $idxSekarang + 1;
                            $selisih = $targetBulan - $s['bulan_terbayar'];
                            $jmlNunggak = max($selisih, 0);
                            $nilaiTunggakan = $jmlNunggak * $tagihanTotal;
                            $grandTotalTunggakan += $nilaiTunggakan;
                        ?>
                        <tr class="<?= $nilaiTunggakan > 0 ? '' : 'table-success opacity-75' ?>">
                            <td class="text-center"><?= $no++ ?></td>
                            <td class="fw-bold ps-3"><?= strtoupper($s['nama']) ?></td>
                            <td class="text-center"><?= $s['kelas'] ?></td>
                            <td class="text-end pe-3">Rp <?= number_format($tagihanTotal, 0, ',', '.') ?></td>
                            <td class="text-center">
                                <span class="badge bg-primary"><?= $s['bulan_terbayar'] ?> Bln</span>
                            </td>
                            <td class="text-center">
                                <?php if($jmlNunggak > 0): ?>
                                    <span class="text-danger small fw-bold"><?= $jmlNunggak ?> Bln Lagi</span>
                                <?php else: ?>
                                    <span class="text-success small"><i class="bi bi-check-all"></i> Lunas</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end pe-3 fw-bold <?= $nilaiTunggakan > 0 ? 'text-danger' : 'text-muted' ?>">
                                <?= $nilaiTunggakan > 0 ? 'Rp ' . number_format($nilaiTunggakan, 0, ',', '.') : '-' ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($siswa)): ?>
                            <tr><td colspan="7" class="text-center py-4 text-muted">Data tidak ditemukan.</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="6" class="text-center fw-bold">TOTAL SELURUH TUNGGAKAN</td>
                            <td class="text-end pe-3 fw-bold">Rp <?= number_format($grandTotalTunggakan, 0, ',', '.') ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function exportToExcel(tableID) {
    let table = document.getElementById(tableID);
    let html = table.outerHTML;
    
    // Gunakan Blob untuk mendownload file Excel sederhana
    let url = 'data:application/vnd.ms-excel,' + encodeURIComponent(html);
    let link = document.createElement('a');
    link.download = 'Laporan_Tunggakan_<?= date('Y-m-d') ?>.xls';
    link.href = url;
    link.click();
}
</script>

</body>
</html>
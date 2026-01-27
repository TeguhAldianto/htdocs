<?php
include '../config.php';
include '../templates/sidebar.php';

$tahunSekarang = date('Y');
$bulanSekarang = (int)date('m');
$tahunAwal = ($bulanSekarang >= 7) ? $tahunSekarang : $tahunSekarang - 1;
$tahunAkhir = $tahunAwal + 1;

$awalAjaran = new DateTime("$tahunAwal-07-01");
$akhirAjaran = new DateTime("$tahunAkhir-06-30");

$querySiswa = "SELECT s.*, (SELECT SUM(p.jumlah) FROM pembayaran p WHERE p.siswa_id = s.id) as total_dibayar FROM siswa s";
$siswa = $db->query($querySiswa)->fetchAll(PDO::FETCH_ASSOC);

$totalTarget = 0;
$rekapKelas = [];

foreach ($siswa as $s) {
    $kelas = strtoupper($s['kelas']);
    $tglMasukRaw = !empty($s['tanggal_masuk']) ? $s['tanggal_masuk'] : "$tahunAwal-07-01";
    $tglMasukSiswa = new DateTime($tglMasukRaw);
    $tglKeluarSiswa = !empty($s['tanggal_keluar']) ? new DateTime($s['tanggal_keluar']) : clone $akhirAjaran;

    $start = ($tglMasukSiswa > $awalAjaran) ? $tglMasukSiswa : $awalAjaran;
    $end = ($tglKeluarSiswa < $akhirAjaran) ? $tglKeluarSiswa : $akhirAjaran;

    $bulanAktif = ($start > $end) ? 0 : ($start->diff($end)->y * 12) + $start->diff($end)->m + 1;

    $nominalSPP = (int)$s['nominal'] - (int)$s['donatur'];
    if (in_array($kelas, ['KB', 'OA', 'OB'])) { $nominalSPP += 5000; }

    $targetSiswa = $bulanAktif * $nominalSPP;
    $totalTarget += $targetSiswa;

    if (!isset($rekapKelas[$kelas])) {
        $rekapKelas[$kelas] = ['jumlah_siswa' => 0, 'target_kelas' => 0, 'realisasi_kelas' => 0, 'siswa_list' => []];
    }

    $dibayar = (float)($s['total_dibayar'] ?? 0);
    $rekapKelas[$kelas]['jumlah_siswa']++;
    $rekapKelas[$kelas]['target_kelas'] += $targetSiswa;
    $rekapKelas[$kelas]['realisasi_kelas'] += $dibayar;
    $rekapKelas[$kelas]['siswa_list'][] = [
        'nama' => $s['nama'], 'target' => $targetSiswa, 'dibayar' => $dibayar, 'sisa' => $targetSiswa - $dibayar
    ];
}

// --- LOGIKA PENGURUTAN JENJANG ---
$urutanJenjang = ['KB' => 1, 'OA' => 2, 'OB' => 3, 'I' => 4, 'II' => 5, 'III' => 6, 'IV' => 7, 'V' => 8, 'VI' => 9];
uksort($rekapKelas, function($a, $b) use ($urutanJenjang) {
    return ($urutanJenjang[$a] ?? 99) <=> ($urutanJenjang[$b] ?? 99);
});
// ---------------------------------

$totaldisetor = $db->query("SELECT SUM(jumlah) as total FROM setoran")->fetch()['total'] ?? 0;
$sisaGlobal = $totalTarget - $totaldisetor;
$persen = $totalTarget > 0 ? round(($totaldisetor / $totalTarget) * 100, 2) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySPP - Target Setoran</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .stat-card { border: none; border-radius: 20px; transition: transform 0.3s ease; overflow: hidden; position: relative; }
        .stat-card:hover { transform: translateY(-5px); }
        .stat-icon { opacity: 0.2; position: absolute; right: 20px; bottom: 10px; font-size: 3rem; }
        .card-label { font-size: 0.85rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.9; }
        .card-value { font-weight: 800; font-size: 1.5rem; }
        .progress-container { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .progress { border-radius: 50px; background-color: #e2e8f0; overflow: visible; }
        .progress-bar { border-radius: 50px; font-weight: 700; position: relative; }
        .badge-kelas { background-color: #0d6dfd; color: white; padding: 5px 12px; border-radius: 8px; font-weight: 700; }
        .clickable-row { cursor: pointer; transition: background 0.2s; }
        .clickable-row:hover { background-color: #f8f9fa !important; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-bullseye me-2 text-primary"></i> Target Setoran</h2>
            <p class="text-muted">Monitoring pencapaian target anggaran dan estimasi pendapatan</p>
        </div>
        <button class="btn btn-white shadow-sm border" onclick="window.print()">
            <i class="fas fa-print me-2"></i>Cetak Laporan
        </button>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card stat-card bg-primary text-white shadow-sm p-3">
                <div class="card-body">
                    <div class="card-label">Total Target</div>
                    <div class="card-value mt-1">Rp <?= number_format($totalTarget, 0, ',', '.') ?></div>
                    <i class="fas fa-bullseye stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-success text-white shadow-sm p-3">
                <div class="card-body">
                    <div class="card-label">Sudah Disetor</div>
                    <div class="card-value mt-1">Rp <?= number_format($totaldisetor, 0, ',', '.') ?></div>
                    <i class="fas fa-check-double stat-icon"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stat-card bg-warning text-white shadow-sm p-3">
                <div class="card-body">
                    <div class="card-label">Sisa Kekurangan</div>
                    <div class="card-value mt-1">Rp <?= number_format($sisaGlobal, 0, ',', '.') ?></div>
                    <i class="fas fa-clock stat-icon"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="progress-container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Persentase Capaian Global</h5>
            <span class="fw-bold text-success" style="font-size: 1.2rem;"><?= $persen ?>%</span>
        </div>
        <div class="progress" style="height: 15px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                 role="progressbar" style="width: <?= $persen ?>%">
            </div>
        </div>
    </div>

    <div class="card-table mt-4">
        <div class="card-body p-0">
            <div class="p-4 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0">Rekap Pembayaran per Kelas <small class="text-muted fw-normal" style="font-size: 12px;">(Klik baris untuk detail)</small></h5>
                <i class="fas fa-table text-muted"></i>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 text-center">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Jumlah Siswa</th>
                            <th>Total Target</th>
                            <th>Realisasi</th>
                            <th>Sisa</th>
                            <th>Capaian (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rekapKelas as $kelas => $r): 
                            $target = $r['target_kelas'];
                            $realisasi = $r['realisasi_kelas'];
                            $sisa = $target - $realisasi;
                            $persenKelas = $target > 0 ? round(($realisasi / $target) * 100, 2) : 0;
                            $textSisa = ($sisa > 0) ? 'text-danger' : 'text-success';
                        ?>
                        <tr class="clickable-row" onclick="showDetailKelas('<?= $kelas ?>')">
                            <td><span class="badge-kelas"><?= htmlspecialchars($kelas) ?></span></td>
                            <td><span class="fw-bold"><?= $r['jumlah_siswa'] ?></span> <small class="text-muted">Siswa</small></td>
                            <td class="text-dark fw-bold">Rp <?= number_format($target, 0, ',', '.') ?></td>
                            <td class="text-success fw-bold">Rp <?= number_format($realisasi, 0, ',', '.') ?></td>
                            <td class="<?= $textSisa ?> fw-bold">Rp <?= number_format($sisa, 0, ',', '.') ?></td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center">
                                    <div class="progress flex-grow-1 me-2" style="height: 6px; width: 60px;">
                                        <div class="progress-bar bg-primary" style="width: <?= $persenKelas ?>%"></div>
                                    </div>
                                    <span class="fw-bold"><?= $persenKelas ?>%</span>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetailKelas" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold">Detail Siswa Kelas <span id="namaKelasDetail" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4 pt-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead class="table-light">
                            <tr class="small text-uppercase fw-bold text-muted">
                                <th>Nama Siswa</th>
                                <th class="text-end">Target</th>
                                <th class="text-end">Terbayar</th>
                                <th class="text-end">Sisa</th>
                            </tr>
                        </thead>
                        <tbody id="isiDetailSiswa"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Data dikirim dari PHP ke JS
    const dataRekap = <?= json_encode($rekapKelas) ?>;

    function showDetailKelas(namaKelas) {
        const detail = dataRekap[namaKelas];
        const tbody = document.getElementById('isiDetailSiswa');
        document.getElementById('namaKelasDetail').innerText = namaKelas;
        
        let html = '';
        detail.siswa_list.forEach(s => {
            const statusWarna = s.sisa > 0 ? 'text-danger' : 'text-success';
            html += `
                <tr>
                    <td class="fw-bold text-dark">${s.nama}</td>
                    <td class="text-end text-muted small">Rp ${s.target.toLocaleString('id-ID')}</td>
                    <td class="text-end text-success fw-bold">Rp ${s.dibayar.toLocaleString('id-ID')}</td>
                    <td class="text-end fw-bold ${statusWarna}">Rp ${s.sisa.toLocaleString('id-ID')}</td>
                </tr>
            `;
        });
        
        tbody.innerHTML = html;
        const modal = new bootstrap.Modal(document.getElementById('modalDetailKelas'));
        modal.show();
    }
</script>
</body>
</html>
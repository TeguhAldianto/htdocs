<?php
// get_detail_mamin.php
include '../config.php';

$id_setoran = isset($_GET['id']) ? intval($_GET['id']) : 0;

$stmt = $db->prepare("SELECT * FROM setoran_mamin WHERE id = ?");
$stmt->execute([$id_setoran]);
$setoran = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$setoran) {
    echo "<div class='text-center py-4 text-danger'>Data tidak ditemukan.</div>";
    exit;
}

// Ambil total per kelas untuk ringkasan di modal
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

// Ambil rincian siswa
$stmt2 = $db->prepare("
    SELECT dm.bulan, dm.nominal, s.nama, s.kelas
    FROM detail_mamin dm
    LEFT JOIN siswa s ON dm.id_siswa = s.id
    WHERE dm.id_setoran = ?
    ORDER BY s.kelas, s.nama ASC
");
$stmt2->execute([$id_setoran]);
$details = $stmt2->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="row g-2 mb-4">
    <?php foreach ($total_kelas_data as $tk): ?>
    <div class="col-4">
        <div class="p-3 bg-light rounded-3 border-start border-4 border-warning">
            <small class="text-muted d-block">Kelas <?= htmlspecialchars($tk['kelas']) ?></small>
            <span class="fw-bold">Rp <?= number_format($tk['total_kelas'], 0, ',', '.') ?></span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="table-responsive rounded-3 border" style="max-height: 400px; overflow-y: auto;">
    <table class="table table-hover mb-0">
        <thead class="sticky-top bg-white shadow-sm">
            <tr>
                <th class="border-0">Nama Siswa</th>
                <th class="border-0 text-center">Kelas</th>
                <th class="border-0 text-center">Bulan</th>
                <th class="border-0 text-end">Nominal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($details as $d): ?>
            <tr>
                <td class="fw-bold small"><?= htmlspecialchars($d['nama']) ?></td>
                <td class="text-center"><span class="badge bg-light text-dark"><?= $d['kelas'] ?></span></td>
                <td class="text-center small text-muted"><?= $d['bulan'] ?></td>
                <td class="text-end fw-bold text-primary">Rp <?= number_format($d['nominal'], 0, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="d-flex justify-content-between align-items-center mt-4 p-3 bg-dark text-white rounded-3">
    <span class="fw-bold">TOTAL KESELURUHAN</span>
    <span class="fw-800 fs-5 text-warning">Rp <?= number_format($setoran['total_mamin'], 0, ',', '.') ?></span>
</div>
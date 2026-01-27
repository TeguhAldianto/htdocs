<?php
include '../config.php';
include '../templates/sidebar.php';

// --- FUNGSI PEMBULATAN CUSTOM ---
function bulatkanSPP($nominal) {
    $sisa = $nominal % 10000; 
    $dasar = floor($nominal / 10000) * 10000; 
    
    if ($sisa < 3000) {
        return $dasar;
    } elseif ($sisa < 8000) {
        return $dasar + 5000;
    } else {
        return $dasar + 10000;
    }
}

// --- FUNGSI OTOMATIS NAIK KELAS & DETEKSI LULUS ---
function prosesKenaikan($kelas) {
    $kelasUpper = strtoupper($kelas);
    
    // Jika kelas VI atau OB, tandai sebagai LULUS
    if (strpos($kelasUpper, 'VI') !== false || strpos($kelasUpper, 'OB') !== false) {
        return 'LULUS';
    }

    // Logika Naik Kelas: OA -> OB, KB -> OA, atau Angka 1 -> 2
    if (strpos($kelasUpper, 'OA') !== false) return str_replace(['OA', 'oa'], 'OB', $kelas);
    if (strpos($kelasUpper, 'KB') !== false) return str_replace(['KB', 'kb'], 'OA', $kelas);
    
    // Untuk kelas angka (1-5)
    return preg_replace_callback('/\d+/', function($m) {
        return $m[0] + 1;
    }, $kelas);
}

// --- AMBIL DATA SISWA ---
// Kita ambil semua siswa AKTIF, tapi nanti di tabel kita pisahkan yang lanjut dan yang lulus
$siswa = $db->query("SELECT id, nama, kelas, (nominal - donatur) as net_nominal FROM siswa WHERE status = 'Aktif' ORDER BY kelas, nama")->fetchAll(PDO::FETCH_ASSOC);

// --- PROSES UPDATE KE DATABASE ---
if (isset($_POST['simpan_kenaikan'])) {
    try {
        $db->beginTransaction();
        
        // 1. Proses Siswa yang Naik Kelas atau Lulus
        foreach ($_POST['data'] as $id => $val) {
            if ($val['kelas_baru'] === 'LULUS') {
                // Jika lulus: Ubah status jadi 'Keluar' dan isi tanggal keluar hari ini
                $stmt = $db->prepare("UPDATE siswa SET status = 'Keluar', tanggal_keluar = CURDATE() WHERE id = ?");
                $stmt->execute([$id]);
            } else {
                // Jika naik kelas: Update kelas dan nominal baru
                $stmt = $db->prepare("UPDATE siswa SET kelas = ?, nominal = ? WHERE id = ?");
                $stmt->execute([$val['kelas_baru'], $val['nominal_baru'], $id]);
            }
        }
        
        $db->commit();
        echo "<script>alert('Berhasil! Siswa kelas akhir telah diluluskan, dan siswa lainnya telah naik kelas.'); window.location='rekap_pembayaran.php';</script>";
    } catch (Exception $e) {
        $db->rollBack();
        die("Gagal memperbarui: " . $e->getMessage());
    }
}
?>

<tbody>
    <?php foreach ($siswa as $s): 
        $kelasBaru = prosesKenaikan($s['kelas']);
        $estimasi = $s['net_nominal'] * 1.1;
        $nominalBulat = bulatkanSPP($estimasi);
    ?>
    <tr class="<?= ($kelasBaru === 'LULUS') ? 'table-warning' : '' ?>">
        <td class="ps-4">
            <span class="fw-bold"><?= htmlspecialchars($s['nama']) ?></span>
            <?php if($kelasBaru === 'LULUS'): ?>
                <span class="badge bg-danger ms-2">LULUS / KELUAR</span>
            <?php endif; ?>
        </td>
        <td>
            <div class="d-flex align-items-center">
                <span class="badge bg-light text-dark border me-2"><?= $s['kelas'] ?></span>
                <i class="fas fa-arrow-right text-muted me-2 small"></i>
                <input type="text" name="data[<?= $s['id'] ?>][kelas_baru]" 
                       value="<?= $kelasBaru ?>" 
                       class="form-control form-control-sm w-50 <?= ($kelasBaru === 'LULUS') ? 'bg-light fw-bold text-danger' : '' ?>" 
                       <?= ($kelasBaru === 'LULUS') ? 'readonly' : '' ?>>
            </div>
        </td>
        <td class="text-muted">Rp <?= number_format($s['net_nominal'], 0, ',', '.') ?></td>
        <td>
            <?php if($kelasBaru !== 'LULUS'): ?>
                <div class="input-group input-group-sm input-nominal">
                    <span class="input-group-text bg-white">Rp</span>
                    <input type="number" name="data[<?= $s['id'] ?>][nominal_baru]" value="<?= $nominalBulat ?>" class="form-control fw-bold border-start-0">
                </div>
            <?php else: ?>
                <input type="hidden" name="data[<?= $s['id'] ?>][nominal_baru]" value="0">
                <span class="text-muted small">-</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</tbody>
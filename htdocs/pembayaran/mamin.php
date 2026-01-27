<?php
// mamin.php
include '../config.php';

$notif = $_GET['notif'] ?? '';

/* =====================================================
   AMBIL RIWAYAT SETORAN MAMIN
   ===================================================== */
$setoran = $db->query("
    SELECT * FROM setoran_mamin ORDER BY tanggal DESC
")->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   AMBIL PEMBAYARAN TK YANG BELUM DISETORKAN
   ===================================================== */
$pembayaran = $db->query("
    SELECT p.id, p.siswa_id, s.nama, p.tanggal, p.kelas, p.bulan, p.mamin
    FROM pembayaran p
    LEFT JOIN siswa s ON p.siswa_id = s.id
    WHERE p.kelas IN ('KB','OA','OB')
      AND p.mamin > 0
      AND (p.setoran_mamin_id IS NULL OR p.setoran_mamin_id = '')
    ORDER BY p.tanggal ASC
")->fetchAll(PDO::FETCH_ASSOC);

/* =====================================================
   SIMPAN SETORAN MAMIN
   ===================================================== */
if (isset($_POST['simpan_mamin'])) {
    $tanggal = $_POST['tanggal_mamin'] ?? date('Y-m-d');
    $ids = $_POST['pembayaran_id'] ?? [];

    if (count($ids) == 0) {
        header("Location: mamin.php?notif=pilihkosong");
        exit;
    }

    try {
        $total = 0;
        $stmtGet = $db->prepare("SELECT mamin FROM pembayaran WHERE id = ?");
        foreach ($ids as $pid) {
            $stmtGet->execute([$pid]);
            $row = $stmtGet->fetch(PDO::FETCH_ASSOC);
            if ($row) { $total += (int)$row['mamin']; }
        }

        $stmtIns = $db->prepare("INSERT INTO setoran_mamin (tanggal, total_mamin, jumlah_transaksi) VALUES (?, ?, ?)");
        $stmtIns->execute([$tanggal, $total, count($ids)]);
        $setoran_id = $db->lastInsertId();

        $stmtDetail = $db->prepare("INSERT INTO detail_mamin (id_setoran, id_siswa, bulan, nominal) VALUES (?, ?, ?, ?)");
        $stmtUpdate = $db->prepare("UPDATE pembayaran SET setoran_mamin_id = ? WHERE id = ?");
        $stmtGetData = $db->prepare("SELECT siswa_id, bulan, mamin FROM pembayaran WHERE id = ?");

        foreach ($ids as $pid) {
            $stmtGetData->execute([$pid]);
            $r = $stmtGetData->fetch(PDO::FETCH_ASSOC);
            if ($r) {
                $stmtDetail->execute([$setoran_id, $r['siswa_id'], $r['bulan'], $r['mamin']]);
                $stmtUpdate->execute([$setoran_id, $pid]);
            }
        }
        header("Location: mamin.php?notif=sukses");
        exit;
    } catch (Exception $e) {
        die("Error saat menyimpan setoran: " . $e->getMessage());
    }
}

/* =====================================================
   HAPUS SETORAN MAMIN
   ===================================================== */
if (isset($_POST['hapus_mamin'])) {
    $id = (int)($_POST['hapus_mamin_id'] ?? 0);
    if ($id > 0) {
        try {
            $db->prepare("UPDATE pembayaran SET setoran_mamin_id = NULL WHERE setoran_mamin_id = ?")->execute([$id]);
            $db->prepare("DELETE FROM detail_mamin WHERE id_setoran = ?")->execute([$id]);
            $db->prepare("DELETE FROM setoran_mamin WHERE id = ?")->execute([$id]);
            header("Location: mamin.php?notif=hapus");
            exit;
        } catch (Exception $e) {
            echo "<h3>Error hapus setoran: " . $e->getMessage() . "</h3>";
            exit;
        }
    }
}

include '../templates/sidebar.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MySPP - List Mamin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .summary-box { background: var(--dark); color: white; padding: 20px; border-radius: 15px; display: flex; justify-content: space-between; align-items: center; margin-top: 20px; }
        #total_setoran { color: #fbbf24; font-weight: 800; font-size: 1.6rem; }
        .modal-content { border-radius: 24px; border: none; }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 25px; }
        .form-control-lg { border-radius: 12px; border: 2px solid #e2e8f0; }
    </style>
</head>
<body>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-utensils me-2 text-primary"></i> List Setoran Mamin</h2>
            <p class="text-muted mb-0">Manajemen setoran uang makan & minum siswa TK</p>
        </div>
        <div>
          <button class="btn btn-primary btn-action shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahMamin">
                <i class="fas fa-plus-circle me-2"></i>Tambah Setoran
            </button>
        </div>
    </div>

    <div class="card-table">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center">Total Mamin</th>
                        <th class="text-center">Jumlah Transaksi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($setoran): $no=1; foreach ($setoran as $r): ?>
                    <tr>
                        <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                        <td><span class="fw-600"><?= date('d M Y', strtotime($r['tanggal'])) ?></span></td>
                        <td class="text-end fw-bold text-primary">Rp <?= number_format($r['total_mamin'],0,',','.') ?></td>
                        <td class="text-center"><span class="badge bg-light text-dark px-3 py-2" style="border-radius: 8px;"><?= $r['jumlah_transaksi'] ?> Data</span></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info text-white fw-bold me-1 btn-view-detail" 
        style="border-radius: 8px;" 
        data-id="<?= $r['id'] ?>">
    Detail
</button>
                            <button class="btn btn-sm btn-outline-danger fw-bold" style="border-radius: 8px;" 
                                    onclick="confirmDelete(<?= $r['id'] ?>)">Hapus</button>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada riwayat setoran mamin.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalTambahMamin" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="fw-800 mb-0">Tambah Setoran Mamin</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold small text-muted text-uppercase">Tanggal Setoran</label>
                                <input type="date" name="tanggal_mamin" class="form-control form-control-lg" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>

                        <div class="table-responsive border rounded-3" style="max-height:50vh; overflow:auto;">
                            <table class="table table-hover mb-0">
                                <thead class="sticky-top bg-white">
                                    <tr>
                                        <th class="text-center"><input type="checkbox" id="checkAllMamin" class="form-check-input"></th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Tanggal Bayar</th>
                                        <th>Bulan</th>
                                        <th class="text-end">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if ($pembayaran): foreach ($pembayaran as $p): ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input cb-mamin" name="pembayaran_id[]" value="<?= $p['id'] ?>" data-mamin="<?= $p['mamin'] ?>">
                                        </td>
                                        <td class="fw-bold"><?= $p['nama'] ?></td>
                                        <td><span class="badge bg-light text-dark"><?= $p['kelas'] ?></span></td>
                                        <td class="small"><?= date('d/m/y', strtotime($p['tanggal'])) ?></td>
                                        <td><?= $p['bulan'] ?></td>
                                        <td class="text-end fw-bold">Rp <?= number_format($p['mamin'],0,',','.') ?></td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Tidak ada pembayaran TK yang tersedia untuk disetor.</td></tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="summary-box">
                            <span class="fw-bold text-uppercase small" style="letter-spacing: 1px;">Estimasi Total Setoran:</span>
                            <div>
                                <span id="total_setoran">Rp 0</span>
                                <input type="hidden" name="total_setoran" id="setoran_input" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-link text-muted fw-bold text-decoration-none" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success px-5 fw-bold" name="simpan_mamin" style="border-radius: 12px;">Simpan Setoran</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalViewDetail" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-800">Rincian Setoran #<span id="label-id-setoran"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="content-detail-mamin">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="mt-2 text-muted">Memuat data...</p>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal" style="border-radius: 10px;">Tutup</button>
            </div>
        </div>
    </div>
</div>
</div>

<script src="../assets/js/bootstrap.bundle.min.js"></script>
<script>
    document.querySelectorAll('.btn-view-detail').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.getAttribute('data-id');
        const modal = new bootstrap.Modal(document.getElementById('modalViewDetail'));
        
        // Reset tampilan & Set label ID
        document.getElementById('label-id-setoran').innerText = id;
        document.getElementById('content-detail-mamin').innerHTML = `
            <div class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
                <p class="mt-2 text-muted">Memuat data...</p>
            </div>`;
        
        modal.show();

        // Ambil data via AJAX
        fetch(`get_detail_mamin.php?id=${id}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('content-detail-mamin').innerHTML = html;
            })
            .catch(err => {
                document.getElementById('content-detail-mamin').innerHTML = 
                    `<div class="alert alert-danger">Gagal memuat data. Silakan coba lagi.</div>`;
            });
    });
});
/**
 * NOTIFIKASI SWEETALERT2
 */
<?php if ($notif == 'sukses'): ?>
    Swal.fire({ icon: 'success', title: 'Berhasil!', text: 'Setoran mamin berhasil disimpan.', timer: 2500, showConfirmButton: false });
<?php elseif ($notif == 'hapus'): ?>
    Swal.fire({ icon: 'success', title: 'Dihapus!', text: 'Data setoran telah berhasil dihapus.', timer: 2500, showConfirmButton: false });
<?php elseif ($notif == 'pilihkosong'): ?>
    Swal.fire({ icon: 'warning', title: 'Peringatan', text: 'Pilih minimal satu data sebelum menyimpan.' });
<?php endif; ?>

/**
 * HITUNG TOTAL OTOMATIS
 */
function hitungTotalMamin() {
    let total = 0;
    document.querySelectorAll('.cb-mamin:checked').forEach(cb => {
        total += parseInt(cb.getAttribute('data-mamin')) || 0;
    });
    document.getElementById('total_setoran').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
    document.getElementById('setoran_input').value = total;
}

document.getElementById('checkAllMamin')?.addEventListener('change', function () {
    const isChecked = this.checked;
    document.querySelectorAll('.cb-mamin').forEach(cb => {
        cb.checked = isChecked;
    });
    hitungTotalMamin();
});

document.querySelectorAll('.cb-mamin').forEach(cb => {
    cb.addEventListener('change', hitungTotalMamin);
});

/**
 * KONFIRMASI HAPUS (GANTI MODAL BOOTSTRAP)
 */
function confirmDelete(id) {
    Swal.fire({
        title: 'Hapus Setoran?',
        text: "Data pembayaran terkait akan dikembalikan ke status 'Belum Setor'.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus Data',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="hapus_mamin_id" value="${id}">
                <input type="hidden" name="hapus_mamin" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    })
}
</script>
    
</body>
</html>
<?php
include '../config.php';
include '../templates/sidebar.php';
$notif = $_GET['notif'] ?? '';

// Ambil semua setoran
$setoran = $db->query("SELECT * FROM setoran ORDER BY tanggal DESC")->fetchAll(PDO::FETCH_ASSOC);

// Ambil pembayaran yang belum disetorkan
$pembayaran = $db->query("SELECT pembayaran.*, siswa.nama, siswa.kelas FROM pembayaran 
                          JOIN siswa ON pembayaran.siswa_id = siswa.id 
                          WHERE pembayaran.setoran_id IS NULL 
                          ORDER BY pembayaran.tanggal")->fetchAll(PDO::FETCH_ASSOC);


// Proses hapus setoran
if (isset($_POST['hapus_setoran'])) {
    $id = $_POST['hapus_setoran_id'];
    $stmt = $db->prepare("UPDATE pembayaran SET setoran_id = NULL WHERE setoran_id = ?");
    $stmt->execute([$id]);
    $stmt = $db->prepare("DELETE FROM setoran WHERE id = ?");
    $stmt->execute([$id]);
    echo "<script>window.location='listsetoran.php?notif=hapus';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MySPP - List Setoran</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    
    <style>
        .table tbody td { padding: 15px; border-color: #f1f5f9; }
        .modal-content { border-radius: 20px; border: none; }
        .modal-header { border-bottom: 1px solid #f1f5f9; padding: 25px; }
        .modal-footer { border-top: 1px solid #f1f5f9; padding: 20px; }
        .summary-box {
            background: var(--dark);
            color: white;
            padding: 20px;
            border-radius: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        #total_setoran { color: #fbbf24; font-weight: 800; font-size: 1.5rem; }
    </style>
</head>

<body>
<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark"><i class="fas fa-box-archive me-2 text-primary"></i> List Setoran</h2>
            <p class="text-muted mb-0">Pantau rekonsiliasi dana masuk dari Admin ke bendahara Yayasan</p>
        </div>
        <button type="button" class="btn btn-primary px-4 py-2 fw-bold shadow-sm" style="border-radius: 10px;" data-bs-toggle="modal" data-bs-target="#modalInputSetoran">
            <i class="fas fa-plus-circle me-2"></i>Setoran Baru
        </button>
    </div>

    <div class="card-table">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Tanggal Setoran</th>
                            <th>Jumlah Setoran</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; foreach ($setoran as $s) { ?>
                        <tr>
                            <td class="text-center fw-bold text-muted"><?= $no++ ?></td>
                            <td class="fw-600"><?= date('d M Y', strtotime($s['tanggal'])) ?></td>
                            <td class="fw-bold text-primary">Rp <?= number_format($s['jumlah'], 0, ',', '.') ?></td>
                            <td class="text-center">
                                <a href='detailsetoran.php?id=<?= $s['id'] ?>' class='btn btn-sm btn-light text-primary me-1 fw-bold'>
                                    <i class="fas fa-eye me-1"></i>Detail
                                </a>
                                <button type='button' class='btn btn-sm btn-light text-danger fw-bold' data-bs-toggle='modal' data-bs-target='#modalHapusSetoran' data-id='<?= $s['id'] ?>'>
                                    <i class="fas fa-trash me-1"></i>Hapus
                                </button>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php if(empty($setoran)): ?>
                        <tr><td colspan="4" class="text-center py-5 text-muted">Belum ada data setoran.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

<div class="modal fade" id="modalInputSetoran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content shadow-lg">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="fw-800 mb-0">Input Setoran Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-muted text-uppercase">Tanggal Setoran</label>
                            <input type="date" name="tanggal_setoran" id="tanggal_setoran" class="form-control form-control-lg border-2" required>
                        </div>
                    </div>

                    <label class="form-label fw-bold small text-muted text-uppercase mb-3">Pilih Pembayaran yang Belum Disetorkan</label>
                    <div class="table-responsive border rounded-3" style="max-height:45vh; overflow:auto;">
                        <table class="table table-hover mb-0">
                            <thead class="sticky-top bg-white border-bottom">
                                <tr>
                                    <th class="text-center"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                    <th>Siswa</th>
                                    <th>Tanggal</th>
                                    <th>Kelas</th>
                                    <th>Bulan</th>
                                    <th class="text-end">Jumlah</th>
                                    <th class="text-end">Mamin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pembayaran as $p) { ?>
                                <tr>
                                    <td class="text-center">
                                        <input type="checkbox" name="pembayaran_id[]" value="<?= $p['id'] ?>" class="form-check-input bayar"
                                               data-jumlah="<?= $p['jumlah'] ?>" data-mamin="<?= $p['mamin'] ?>" onchange="updateTotal()">
                                    </td>
                                    <td class="fw-bold"><?= $p['nama'] ?></td>
                                    <td class="small"><?= date('d/m/y', strtotime($p['tanggal'])) ?></td>
                                    <td class="small"><?= $p['kelas'] ?></td>
                                    <td class="small text-muted"><?= $p['bulan'] ?></td>
                                    <td class="text-end fw-bold">Rp <?= number_format($p['jumlah'], 0, ',', '.') ?></td>
                                    <td class="text-end text-danger">Rp <?= number_format($p['mamin'], 0, ',', '.') ?></td>
                                </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="summary-box mt-4">
                        <span class="fw-bold text-uppercase small">Total Setoran Bersih (Tanpa Mamin):</span>
                        <div class="d-flex align-items-center">
                            <span id="total_setoran">Rp 0</span>
                            <input type="hidden" name="total_setoran" id="setoran_input" value="0">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="simpan_setoran" class="btn btn-success px-4 fw-bold">Simpan Setoran</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalHapusSetoran" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST">
            <div class="modal-content">
                <div class="modal-body p-5 text-center">
                    <div class="mb-4">
                        <i class="fas fa-trash-can fa-4x text-danger opacity-25"></i>
                    </div>
                    <h4 class="fw-800">Hapus Setoran?</h4>
                    <p class="text-muted">Yakin ingin menghapus setoran ini? Pembayaran di dalamnya akan kembali berstatus "Belum Setor".</p>
                    <input type="hidden" name="hapus_setoran_id" id="hapus_setoran_id">
                    
                    <div class="d-flex gap-2 justify-content-center mt-4">
                        <button type="button" class="btn btn-light fw-bold px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="hapus_setoran" class="btn btn-danger fw-bold px-4 shadow-sm">Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

</div>

<div style="display:none;">
<?php include 'proses_setoran.php';?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Set tanggal hari ini
        const today = new Date().toISOString().split('T')[0];
        const inputTgl = document.getElementById('tanggal_setoran');
        if(inputTgl) inputTgl.value = today;

        // Logika Notifikasi SweetAlert di Tengah
        const params = new URLSearchParams(window.location.search);
        const notif = params.get('notif');

        if (notif === 'sukses') {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: 'Data setoran telah berhasil disimpan.',
                showConfirmButton: false,
                timer: 2000,
                position: 'center'
            });
        } else if (notif === 'hapus') {
            Swal.fire({
                icon: 'warning',
                title: 'Dihapus!',
                text: 'Data setoran telah berhasil dihapus.',
                showConfirmButton: false,
                timer: 2000,
                position: 'center'
            });
        }

        // Check All
        const checkAll = document.getElementById('checkAll');
        if(checkAll){
            checkAll.addEventListener('change', function () {
                const checkboxes = document.querySelectorAll('.bayar');
                checkboxes.forEach(cb => cb.checked = this.checked);
                updateTotal();
            });
        }
    });

    function updateTotal() {
        let checkboxes = document.querySelectorAll('.bayar:checked');
        let totalPembayaran = 0;
        let totalMamin = 0;

        checkboxes.forEach(function(checkbox) {
            totalPembayaran += parseInt(checkbox.dataset.jumlah);
            totalMamin += parseInt(checkbox.dataset.mamin);
        });

        let totalSetoran = totalPembayaran - totalMamin;
        if (totalSetoran < 0) totalSetoran = 0;

        document.getElementById('total_setoran').innerText = 'Rp ' + totalSetoran.toLocaleString('id-ID');
        document.getElementById('setoran_input').value = totalSetoran;
    }

    // Modal Hapus ID Transfer
    const modalHapus = document.getElementById('modalHapusSetoran');
    if(modalHapus){
        modalHapus.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            document.getElementById('hapus_setoran_id').value = button.getAttribute('data-id');
        });
    }
</script>
</body>
</html>
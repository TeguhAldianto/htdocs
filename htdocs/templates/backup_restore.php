<?php
ob_start(); 
include '../config.php';
include '../templates/sidebar.php';

$status = $_GET['status'] ?? '';
$pesan = $_GET['pesan'] ?? '';

// --- LOGIKA EXCEL MULTI-SHEET ---
if (isset($_GET['action']) && $_GET['action'] == 'export_excel') {
    if (ob_get_length()) ob_end_clean();
    try {
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $filename = "Arsip_SPP_Lengkap_" . date('Y-m-d_H-i') . ".xls";

        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: public");

        echo '<?xml version="1.0" encoding="utf-8"?>' . "\n";
        echo '<?mso-application progid="Excel.Sheet"?>' . "\n";
        echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        echo '<Styles>
                <Style ss:ID="sHeader"><Font ss:Bold="1" ss:Color="#FFFFFF"/><Interior ss:Color="#2E75B6" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center" ss:Vertical="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>
                <Style ss:ID="sData"><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>
                <Style ss:ID="sAngka"><NumberFormat ss:Format="#,##0"/><Alignment ss:Horizontal="Right"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>
              </Styles>' . "\n";

        foreach ($tables as $table) {
            $sheetName = substr(htmlspecialchars(ucfirst($table)), 0, 31);
            echo '<Worksheet ss:Name="' . $sheetName . '"><Table>' . "\n";
            $res = $db->query("SELECT * FROM `$table` ");
            $rows = $res->fetchAll(PDO::FETCH_ASSOC);
            if (!empty($rows)) {
                echo '<Row ss:Height="20">';
                foreach (array_keys($rows[0]) as $col) {
                    echo '<Cell ss:StyleID="sHeader"><Data ss:Type="String">' . htmlspecialchars(strtoupper($col)) . '</Data></Cell>';
                }
                echo '</Row>';
                foreach ($rows as $row) {
                    echo '<Row>';
                    foreach ($row as $val) {
                        if (is_numeric($val) && strlen($val) < 11 && substr($val, 0, 1) !== '0') {
                            echo '<Cell ss:StyleID="sAngka"><Data ss:Type="Number">' . $val . '</Data></Cell>';
                        } else {
                            echo '<Cell ss:StyleID="sData"><Data ss:Type="String">' . htmlspecialchars($val) . '</Data></Cell>';
                        }
                    }
                    echo '</Row>';
                }
            }
            echo '</Table></Worksheet>' . "\n";
        }
        echo '</Workbook>';
        exit;
    } catch (Exception $e) { die("Kesalahan Export: " . $e->getMessage()); }
}

// --- LOGIKA RESTORE ---
if (isset($_POST['restore'])) {
    if (isset($_FILES['backup_file']) && $_FILES['backup_file']['error'] == 0) {
        $sql_content = file_get_contents($_FILES['backup_file']['tmp_name']);
        try {
            $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $stmt = $db->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($tables as $table) { $db->exec("DROP TABLE IF EXISTS `$table` "); }
            $db->exec($sql_content);
            $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
            
            // Diarahkan ke dashboard dengan notif
            header('Location: ../index.php?status=restore_success'); 
            exit;
        } catch (PDOException $e) { 
            header('Location: ../index.php?status=error&pesan=' . urlencode($e->getMessage())); 
            exit; 
        }
    }
}

// --- LOGIKA RESET ---
if (isset($_POST['reset_app'])) {
    try {
        $db->exec("SET FOREIGN_KEY_CHECKS = 0;");
        $stmt = $db->query("SHOW TABLES");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        foreach ($tables as $table) {
            if ($table !== 'users' && $table !== 'admin') { $db->exec("TRUNCATE TABLE `$table` "); }
        }
        $db->exec("SET FOREIGN_KEY_CHECKS = 1;");
        
        // Diarahkan ke dashboard dengan notif
        header('Location: ../index.php?status=reset_success');
        exit;
    } catch (PDOException $e) { 
        header('Location: ../index.php?status=error&pesan=' . urlencode($e->getMessage())); 
        exit; 
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MySPP - Maintenance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .card-maint { border: none; border-radius: 20px; transition: 0.3s; height: 100%; background: #fff; }
        .card-maint:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .icon-circle { width: 65px; height: 65px; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px; border-radius: 50%; }
        .btn-maint { border-radius: 12px; font-weight: 600; padding: 10px; }
    </style>
</head>
<body class="bg-light">
<div class="main-content">
    <div class="row mb-4">
        <div class="col-md-12">
           <h2 class="fw-bold text-dark"><i class="fa fa-tools me-2 text-primary"></i> Pemeliharaan Sistem</h2>
            <p class="text-muted">Kelola cadangan data, ekspor laporan, dan persiapan tahun ajaran baru.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card card-maint shadow-sm p-4 text-center">
                <div class="icon-circle bg-primary bg-opacity-10 text-primary"><i class="fas fa-database fa-lg"></i></div>
                <h6 class="fw-bold">1. Cadangan SQL</h6>
                <p class="small text-muted mb-4">Unduh seluruh struktur & data database (Format .sql).</p>
                <a href="proses_backup.php" class="btn btn-primary btn-maint w-100 mt-auto shadow-sm">
                    <i class="fas fa-download me-1"></i> Download SQL
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-maint shadow-sm p-4 text-center">
                <div class="icon-circle bg-success bg-opacity-10 text-success"><i class="fas fa-file-excel fa-lg"></i></div>
                <h6 class="fw-bold">2. Cetak Arsip</h6>
                <p class="small text-muted mb-4">Simpan data dalam format Excel untuk laporan fisik/arsip.</p>
                <a href="?action=export_excel" class="btn btn-success btn-maint w-100 mt-auto shadow-sm">
                    <i class="fas fa-file-export me-1"></i> Export Excel
                </a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-maint shadow-sm p-4 text-center">
                <div class="icon-circle bg-warning bg-opacity-10 text-warning"><i class="fas fa-history fa-lg"></i></div>
                <h6 class="fw-bold">3. Pulihkan Data</h6>
                <p class="small text-muted mb-3">Kembalikan data dari file cadangan SQL.</p>
                <form method="POST" enctype="multipart/form-data" id="formRestore">
                    <input type="file" name="backup_file" id="fileInput" class="form-control form-control-sm mb-2" accept=".sql" required>
                    <input type="hidden" name="restore" value="1">
                    <button type="button" onclick="handleRestore()" class="btn btn-warning btn-maint w-100 text-white shadow-sm">
                        <i class="fas fa-upload me-1"></i> Restore SQL
                    </button>
                </form>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card card-maint shadow-sm p-4 text-center border-0">
                <div class="icon-circle bg-danger bg-opacity-10 text-danger"><i class="fas fa-calendar-check fa-lg"></i></div>
                <h6 class="fw-bold text-danger">4. Tutup Buku</h6>
                <p class="small text-muted mb-4">Kosongkan semua transaksi untuk tahun ajaran baru.</p>
                <form method="POST" id="formReset">
                    <input type="hidden" name="reset_app" value="1">
                    <button type="button" onclick="handleReset()" class="btn btn-outline-danger btn-maint w-100 mt-auto">
                        <i class="fas fa-sync-alt me-1"></i> Reset Sistem
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
   
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Menangkap Status dari URL
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const msg = urlParams.get('pesan');

    if (status === 'restore_success') {
        Swal.fire({ title: 'Berhasil!', text: 'Database telah dipulihkan ke kondisi cadangan.', icon: 'success', confirmButtonColor: '#0d6efd' });
    } else if (status === 'reset_success') {
        Swal.fire({ title: 'Sistem Bersih!', text: 'Seluruh data transaksi telah dikosongkan.', icon: 'success', confirmButtonColor: '#198754' });
    } else if (status === 'error') {
        Swal.fire({ title: 'Terjadi Kesalahan', text: msg || 'Gagal memproses permintaan.', icon: 'error' });
    }

    // Fungsi Restore dengan Loading
    function handleRestore() {
        const file = document.getElementById('fileInput').files[0];
        if (!file) return Swal.fire('Pilih File', 'Silakan pilih file .sql hasil backup terlebih dahulu.', 'info');
        
        Swal.fire({
            title: 'Konfirmasi Restore',
            text: "Data saat ini akan dihapus dan digantikan oleh data dari file backup. Lanjutkan?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Timpa Data!',
            cancelButtonText: 'Batal'
        }).then((res) => { 
            if(res.isConfirmed) {
                Swal.fire({
                    title: 'Sedang Memulihkan...',
                    text: 'Mohon tunggu, jangan tutup halaman ini.',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                document.getElementById('formRestore').submit(); 
            }
        });
    }

    // Fungsi Reset dengan Verifikasi Teks
    function handleReset() {
        Swal.fire({
            title: 'Bahaya! Tutup Buku',
            html: "Tindakan ini akan menghapus <b>Seluruh Data Transaksi</b>.<br><br>Ketik <b>KOSONGKAN DATA</b> di bawah untuk konfirmasi:",
            input: 'text',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Reset Sekarang',
            preConfirm: (val) => {
                if (val !== 'KOSONGKAN DATA') {
                    Swal.showValidationMessage('Konfirmasi teks salah!');
                }
                return val;
            }
        }).then((res) => { 
            if(res.isConfirmed) {
                Swal.fire({
                    title: 'Memproses Reset...',
                    didOpen: () => { Swal.showLoading(); }
                });
                document.getElementById('formReset').submit(); 
            }
        });
    }
</script>
</body>
</html>
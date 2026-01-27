<?php
include '../config.php';

$alertStatus = '';
$alertTitle = '';
$alertText = '';

if (isset($_POST['upload'])) {
    if (isset($_FILES['file_excel']['tmp_name']) && !empty($_POST['tanggal_masuk'])) {
        require '../vendor/autoload.php';

        $tanggalMasukInput = trim($_POST['tanggal_masuk']); 
        $file = $_FILES['file_excel']['tmp_name'];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        
        // Mengambil sheet aktif dan mengubahnya menjadi array
        // Kita gunakan range agar lebih efisien jika data banyak
        $sheet = $spreadsheet->getActiveSheet()->toArray();

        $sukses = 0;
        $gagal = 0;
        $detail_gagal = [];

        /** * PENYESUAIAN BARIS:
         * Baris 1: Judul "IMPORT DATA SISWA" (Index 0)
         * Baris 2: Header Kolom (Index 1)
         * Baris 3: DATA AWAL (Index 2) -> Maka loop dimulai dari $i = 2
         */
        for ($i = 2; $i < count($sheet); $i++) {
            $nama    = trim($sheet[$i][0] ?? '');
            $kelas   = trim(strtoupper($sheet[$i][1] ?? '')); // Memastikan huruf besar agar in_array akurat
            $nominal = intval($sheet[$i][2] ?? 0);
            $donatur = intval($sheet[$i][3] ?? 0);
            $mamin   = intval($sheet[$i][4] ?? 0);
            
            /** * PENYESUAIAN NO HP:
             * Gunakan trim() bukannya intval() agar angka '0' di depan tidak hilang.
             * Misal: 08123... tetap terbaca 08123..., bukan 8123...
             */
            $no_hp   = trim($sheet[$i][5] ?? '');

            // Tentukan jenjang otomatis (Sesuai dengan filter dropdown di Excel)
            $jenjang = in_array($kelas, ['KB', 'OA', 'OB']) ? 'TK' : 'SD';

            // Validasi: Minimal Nama, Kelas, dan Nominal terisi
            if ($nama != '' && $kelas != '' && $nominal >= 0) {
                try {
                    $stmt = $db->prepare("INSERT INTO siswa (nama, jenjang, kelas, nominal, donatur, mamin, tanggal_masuk, no_hp) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    
                    $insert = $stmt->execute([
                        strtoupper($nama), // Simpan nama dengan huruf kapital agar rapi di database
                        $jenjang, 
                        $kelas, 
                        $nominal, 
                        $donatur, 
                        $mamin, 
                        $tanggalMasukInput,
                        $no_hp
                    ]);
                    
                    if ($insert) {
                        $sukses++;
                    } else {
                        $gagal++;
                        $detail_gagal[] = "Baris " . ($i+1) . ": Gagal simpan database";
                    }
                } catch (PDOException $e) {
                    $gagal++;
                    $detail_gagal[] = "Baris " . ($i+1) . ": Error SQL (" . $e->getMessage() . ")";
                }
            } else {
                // Lewati jika baris benar-benar kosong
                if (empty($nama) && empty($kelas)) continue;

                $gagal++;
                $detail_gagal[] = "Baris " . ($i+1) . ": Data tidak lengkap";
            }
        }

        // Pengaturan pesan SweetAlert
        $alertStatus = ($gagal == 0) ? 'success' : 'warning';
        $alertTitle  = ($gagal == 0) ? 'Import Berhasil!' : 'Import Selesai dengan Catatan';
        $pesan       = "Berhasil: <b>$sukses</b> data.<br>Gagal: <b>$gagal</b> data.";
        
        if ($gagal > 0) {
            $pesan .= "<br><br><b>Detail Masalah:</b><br><small>" . implode("<br>", array_slice($detail_gagal, 0, 5)) . (count($detail_gagal) > 5 ? "<br>..." : "") . "</small>";
        }
        $alertText = $pesan;

    } else {
        $alertStatus = 'error';
        $alertTitle  = 'Gagal!';
        $alertText   = 'Silakan pilih file dan tanggal terlebih dahulu.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Data Siswa</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    </style>
</head>
<body>

<a href="TEMPLATE_DATA_SISWA.xlsx" class="btn btn-outline-primary btn-sm" download>
            <i class="fas fa-download me-1"></i> Unduh Template Excel
        </a>

        <form method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label class="form-label fw-bold">Tanggal Masuk:</label>
                <input type="date" name="tanggal_masuk" class="form-control form-control-lg" value="<?= date('Y-m-d') ?>" required>
                <div class="form-text">Semua siswa di file ini akan tercatat masuk pada tanggal ini.</div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-bold">Pilih File Excel (.xlsx):</label>
                <input type="file" name="file_excel" class="form-control" accept=".xlsx" required>
            </div>
            
            <button type="submit" name="upload" class="btn btn-primary w-100 fw-bold py-3" style="border-radius: 12px;">
                Mulai Proses Import
            </button>
        </form>

<?php if ($alertStatus !== ''): ?>
<script>
    Swal.fire({
        icon: '<?= $alertStatus ?>',
        title: '<?= $alertTitle ?>',
        html: '<?= $alertText ?>',
        confirmButtonText: 'Tutup',
        confirmButtonColor: '#0d6efd'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location = 'list.php'; // Arahkan ke daftar siswa setelah sukses
        }
    });
</script>
<?php endif; ?>

</body>
</html>
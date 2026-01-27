<?php
include '../config.php';
include '../templates/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Siswa Baru</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .card-custom { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .form-label { font-weight: 600; color: #495057; font-size: 0.9rem; }
        .input-group-text { background-color: #f1f3f5; font-weight: bold; }
        .section-title { border-left: 4px solid #198754; padding-left: 10px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
<div class="main-content">
    
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="fw-bold text-dark"><i class="fas fa-user-plus text-success me-2"></i>Tambah Siswa Baru</h2>
            <a href="list.php" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Kembali ke Daftar</a>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card card-custom p-4">
                    <form action="proses_tambah.php" method="POST">
                        
                        <div class="section-title">Informasi Akademik</div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Masuk</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    <input type="date" name="tanggal_masuk" class="form-control" value="<?= date('Y-m-d') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nama Lengkap Siswa</label>
                                <input type="text" name="nama" class="form-control" placeholder="Contoh: Ahmad Subarjo" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Kelas</label>
                                <select name="kelas" id="kelasSelect" class="form-select" required onchange="updateInfo(this.value)">
                                    <option value="">-- Pilih Kelas --</option>
                                    <optgroup label="Taman Kanak-Kanak (TK)">
                                        <option value="KB">KB (Kelompok Bermain)</option>
                                        <option value="OA">OA</option>
                                        <option value="OB">OB</option>
                                    </optgroup>
                                    <optgroup label="Sekolah Dasar (SD)">
                                        <option value="I">I</option>
                                        <option value="II">II</option>
                                        <option value="III">III</option>
                                        <option value="IV">IV</option>
                                        <option value="V">V</option>
                                        <option value="VI">VI</option>
                                    </optgroup>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Jenjang</label>
                                <input type="text" name="jenjang" id="jenjangInput" class="form-control bg-light" readonly placeholder="-">
                            </div>
                        </div>

                        <div class="section-title mt-3">Konfigurasi Pembayaran (SPP)</div>
                        <div class="row g-3">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Uang Sekolah (SPP)</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="nominal" class="form-control" placeholder="0" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Subsidi Donatur</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" name="donatur" class="form-control" value="0" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Biaya Mamin (Otomatis)</label>
                                <div class="input-group">
                                    <span class="input-group-text text-success">Rp</span>
                                    <input type="number" name="mamin" id="maminInput" class="form-control bg-light" value="0" readonly>
                                </div>
                                <small class="text-muted" style="font-size: 0.7rem;">*Khusus TK otomatis Rp 5.000</small>
                            </div>
                        </div>

                        <div class="mt-4 border-top pt-3">
                            <button type="submit" class="btn btn-success px-5 py-2 fw-bold">
                                <i class="fas fa-save me-1"></i> Simpan Data Siswa
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card card-custom bg-white p-4">
                    <div class="section-title text-primary" style="border-left-color: #0d6efd;">Cepat dengan Import</div>
                    <p class="small text-muted">Gunakan fitur ini jika Anda ingin memasukkan data siswa dalam jumlah banyak melalui file Excel.</p>
                    <hr>
                    <?php include 'import_siswa.php';?>
                </div>
                
                <div class="alert alert-info mt-4 shadow-sm border-0" style="border-radius: 12px;">
                    <h6 class="fw-bold"><i class="fas fa-info-circle me-1"></i> Tips Pengisian</h6>
                    <ul class="mb-0 small ps-3">
                        <li>Pastikan nama siswa sesuai akta.</li>
                        <li><b>Mamin</b> otomatis terisi Rp 5.000 jika Anda memilih kelas TK.</li>
                        <li>Gunakan angka saja pada kolom nominal (tanpa titik/koma).</li>
                    </ul>
                </div>
            </div>
        </div>
    
</div>

<script>
function updateInfo(kelas) {
    let jenjangInput = document.getElementById('jenjangInput');
    let maminInput = document.getElementById('maminInput');
    
    // Logika Otomatisasi Jenjang & Mamin
    if (['KB', 'OA', 'OB'].includes(kelas)) {
        jenjangInput.value = 'TK';
        maminInput.value = 5000;
    } else if (kelas === "") {
        jenjangInput.value = "";
        maminInput.value = 0;
    } else {
        jenjangInput.value = 'SD';
        maminInput.value = 0;
    }
}
</script>

</body>
</html>
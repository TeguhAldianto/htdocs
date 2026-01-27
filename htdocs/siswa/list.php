<?php
include '../config.php';
include '../templates/sidebar.php';
include '../functions.php';

// Ambil SEMUA data siswa tanpa LIMIT agar filter instan (client-side) berfungsi maksimal
$sql = "SELECT id, nama, jenjang, kelas, nominal, donatur, mamin, status, no_hp, tanggal_keluar 
        FROM siswa 
        ORDER BY jenjang DESC, kelas ASC, nama ASC";
$stmt = $db->prepare($sql);
$stmt->execute();
$siswaList = $stmt->fetchAll(PDO::FETCH_ASSOC);
$totalSiswa = count($siswaList);

// Ambil daftar kelas unik untuk tombol filter
$kelasList = $db->query("SELECT DISTINCT kelas FROM siswa ORDER BY jenjang DESC, kelas ASC")->fetchAll(PDO::FETCH_COLUMN);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MySPP - Data Siswa</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        .card-filter { border: none; border-radius: 10px; box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); }
        .table-responsive { max-height: 70vh; overflow-x: auto; }
        .badge-status { font-size: 0.8rem; padding: 5px 10px; }
        .btn-filter { font-size: 0.85rem; font-weight: 500; border-radius: 8px; transition: all 0.2s; }
        .btn-filter.active { background-color: #0d6efd !important; color: white !important; border-color: #0d6efd !important; box-shadow: 0 4px 6px rgba(13, 110, 253, 0.25); }
    </style>
</head>
<body>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="col-md-6">
            <h2 class="fw-bold text-dark"><i class="fas fa-users me-2 text-primary"></i> Data Siswa</h2>
            <p class="text-muted">Kelola informasi biodata, status aktif, dan rombel siswa</p>
        </div>        
        <div class="text-muted small">Menampilkan: <strong id="countDisplay"><?= $totalSiswa ?></strong> Siswa</div>
    </div>

    <div class="card card-filter mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-lg-12 mb-2">
                    <label class="small fw-bold text-muted text-uppercase mb-2 d-block">Filter Kelas:</label>
                    <div class="d-flex flex-wrap gap-2" id="btnGroupKelas">
                        <button class="btn btn-outline-secondary btn-sm btn-filter active" onclick="applyFilter('all', this)">Semua</button>
                        <?php foreach ($kelasList as $k): ?>
                            <button class="btn btn-outline-secondary btn-sm btn-filter" onclick="applyFilter('<?= $k ?>', this)"><?= $k ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="inputNama" class="form-control border-start-0 ps-0" placeholder="Cari nama siswa" oninput="applyFilter()">
                        <button type="button" class="btn btn-outline-secondary" onclick="resetFilter()" title="Reset Filter"><i class="fas fa-sync-alt"></i></button>
                    </div>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <a href="tambah.php" class="btn btn-success"><i class="fas fa-plus-circle me-1"></i> Tambah</a>
                    <button class="btn btn-danger" onclick="konfirmasiHapusSemua()"><i class="fas fa-trash-alt me-1"></i> Hapus Semua</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card-table">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center" id="siswaTable">
                <thead>
                    <tr>
                        <th class="py-3">Nama Siswa</th>
                        <th>Jenjang</th>
                        <th>Kelas</th>
                        <th>Uang Sekolah</th>
                        <th>Donatur</th>
                        <th>Mamin</th>
                        <th class="text-primary">Total Tagihan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($siswaList)): ?>
                        <tr id="noDataRow"><td colspan="9" class="py-5 text-muted">Data siswa tidak ditemukan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($siswaList as $s): 
                            $nominal = (int)($s['nominal'] ?? 0);
                            $donatur = (int)($s['donatur'] ?? 0);
                            $mamin   = (int)($s['mamin'] ?? 0);
                            $total   = max(0, $nominal - $donatur + $mamin);
                        ?>
                            <tr class="siswa-row" data-kelas="<?= $s['kelas'] ?>" data-nama="<?= strtolower($s['nama']) ?>">
                                <td class="text-start ps-4 fw-bold text-dark"><?= htmlspecialchars($s['nama']) ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $s['jenjang'] ?></span></td>
                                <td class="fw-bold"><?= $s['kelas'] ?></td>
                                <td><?= rupiah($nominal) ?></td>
                                <td class="text-danger">-<?= rupiah($donatur) ?></td>
                                <td><?= rupiah($mamin) ?></td>
                                <td class="fw-bold text-primary"><?= rupiah($total) ?></td>
                                <td>
                                    <?php 
                                    $statusClass = [
                                        'aktif' => 'bg-success',
                                        'pindah' => 'bg-warning text-dark',
                                        'lulus' => 'bg-secondary'
                                    ];
                                    $currentClass = $statusClass[$s['status']] ?? 'bg-dark';
                                    ?>
                                    <span class="badge badge-status <?= $currentClass ?>"><?= ucfirst($s['status']) ?></span>
                                </td>
                                <td>
                                    <div class="btn-group shadow-sm">
                                        <button class="btn btn-sm btn-white border" 
                                                data-bs-toggle="modal" data-bs-target="#modalEditSiswa"
                                                data-id="<?= $s['id'] ?>" data-nama="<?= htmlspecialchars($s['nama']) ?>"
                                                data-jenjang="<?= $s['jenjang'] ?>" data-kelas="<?= $s['kelas'] ?>"
                                                data-nominal="<?= $s['nominal'] ?>" data-donatur="<?= $s['donatur'] ?>"
                                                data-mamin="<?= $s['mamin'] ?>" data-status="<?= $s['status'] ?>"
                                                data-no_hp="<?= $s['no_hp'] ?>"
                                                data-keluar="<?= $s['tanggal_keluar'] ?>">
                                            <i class="fas fa-edit text-warning"></i>
                                        </button>
                                      <button class="btn btn-sm btn-white border" 
        onclick="konfirmasiHapus('<?= $s['id'] ?>', '<?= addslashes(htmlspecialchars($s['nama'])) ?>')">
    <i class="fas fa-trash text-danger"></i>
</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'modal_edit_hapus.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
let currentKelas = 'all';

function applyFilter(kelas = null, btn = null) {
    if (kelas !== null) {
        currentKelas = kelas;
        const buttons = document.querySelectorAll('.btn-filter');
        buttons.forEach(b => b.classList.remove('active', 'btn-primary'));
        btn.classList.add('active');
    }
    const searchNama = document.getElementById('inputNama').value.toLowerCase();
    const rows = document.querySelectorAll('.siswa-row');
    let visibleCount = 0;
    rows.forEach(row => {
        const rowKelas = row.getAttribute('data-kelas');
        const rowNama = row.getAttribute('data-nama');
        const matchKelas = (currentKelas === 'all' || rowKelas === currentKelas);
        const matchNama = rowNama.includes(searchNama);
        if (matchKelas && matchNama) {
            row.style.display = "";
            visibleCount++;
        } else {
            row.style.display = "none";
        }
    });
    document.getElementById('countDisplay').innerText = visibleCount;
}

function resetFilter() {
    document.getElementById('inputNama').value = '';
    const allBtn = document.querySelector('.btn-filter[onclick*="all"]');
    applyFilter('all', allBtn);
}

// 1. Fungsi Konfirmasi Hapus Satuan (DIREVISI)
function konfirmasiHapus(id, nama) {
    Swal.fire({
        title: 'Apakah Anda yakin?',
        html: "Data siswa <b>" + nama + "</b> akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Karena hapus.php Anda menggunakan $_POST['id'], 
            // kita buat form dummy agar data terkirim via POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'hapus.php'; // Sesuaikan dengan nama file Anda

            const inputId = document.createElement('input');
            inputId.type = 'hidden';
            inputId.name = 'id';
            inputId.value = id;

            form.appendChild(inputId);
            document.body.appendChild(form);
            form.submit();
        }
    })
}

// 2. // Fungsi Konfirmasi Hapus Semua (Disesuaikan untuk POST)
function konfirmasiHapusSemua() {
    Swal.fire({
        title: 'Hapus SEMUA Data?',
        text: "Seluruh data siswa akan dihapus permanen dari sistem!",
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Ya, Hapus Semua!',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            // Membuat form dinamis untuk mengirim POST
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'hapus_semua_siswa.php';
            document.body.appendChild(form);
            form.submit();
        }
    })
}
// 3. Fungsi Notifikasi Toast (SweetAlert2)
function showSwal(pesan, ikon = 'success') {
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });
    Toast.fire({
        icon: ikon,
        title: pesan
    });
}

// Handler Modal Edit (Tetap dipertahankan untuk form)
// A. Daftar data kelas berdasarkan jenjang
const daftarKelas = {
    'TK': ['KB', 'OA', 'OB'],
    'SD': ['I', 'II', 'III', 'IV', 'V', 'VI']
};

// B. Fungsi untuk mengubah isi dropdown kelas & Nominal Mamin Otomatis
function updateDropdownKelas(selectedValue = null) {
    const jenjang = document.getElementById('edit-jenjang').value;
    const kelasSelect = document.getElementById('edit-kelas');
    const maminInput = document.getElementById('edit-mamin');
    
    // --- LOGIKA OTOMATIS MAMIN ---
    // Jika Jenjang TK, set nominal (misal 150000), jika bukan (SD) set 0
    if (jenjang === "TK") {
        maminInput.value = 5000; // Silakan sesuaikan angka nominalnya
    } else {
        maminInput.value = 0;
    }
    // -----------------------------

    // Kosongkan dropdown kelas
    kelasSelect.innerHTML = '';

    if (daftarKelas[jenjang]) {
        daftarKelas[jenjang].forEach(kls => {
            const option = document.createElement('option');
            option.value = kls;
            option.text = kls;
            if (kls === selectedValue) option.selected = true;
            kelasSelect.appendChild(option);
        });
    }
}

// C. Handler Modal Edit (DIREVISI)
document.getElementById('modalEditSiswa').addEventListener('show.bs.modal', function (event) {
    const btn = event.relatedTarget;
    const data = btn.dataset;
    
    // 1. Set data identitas & tagihan
    document.getElementById('edit-id').value = data.id;
    document.getElementById('edit-nama').value = data.nama;
    
    /* ================= LOGIKA POTONG NO HP ================= */
    let rawHp = data.no_hp || "";
    let cleanHp = rawHp;
    
    // Jika data dari DB diawali 62, potong agar tidak double dengan label +62 di modal
    if (rawHp.startsWith('62')) {
        cleanHp = rawHp.substring(2);
    } 
    // Jika data dari DB diawali 0, potong angka 0 nya
    else if (rawHp.startsWith('0')) {
        cleanHp = rawHp.substring(1);
    }
    
    document.getElementById('edit-no_hp').value = cleanHp;
    /* ======================================================= */

    document.getElementById('edit-nominal').value = data.nominal;
    document.getElementById('edit-donatur').value = data.donatur;
    document.getElementById('edit-mamin').value = data.mamin;
    document.getElementById('edit-status').value = data.status;
    document.getElementById('edit-keluar').value = data.keluar;

    // 2. Logika Dropdown Dinamis
    document.getElementById('edit-jenjang').value = data.jenjang;
    updateDropdownKelas(data.kelas);
});

// Cek Notifikasi URL
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.has('notif')) {
    const type = urlParams.get('notif');
    if (type === 'edit') showSwal('Data siswa berhasil diperbarui!');
    if (type === 'hapus') showSwal('Data siswa berhasil dihapus!', 'success');
    if (type === 'tambah') showSwal('Data siswa berhasil ditambahkan!', 'success');
    window.history.replaceState({}, document.title, window.location.pathname);
}
</script>
</body>
</html>
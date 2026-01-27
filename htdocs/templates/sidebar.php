<?php 
include 'header.php'; 

// Tangkap halaman yang sedang aktif untuk penanda menu
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/stylesidebar.css">
</head>
    <style>
        .sidebar-brand {
             height: 55px;
            margin-left: -20px;
            font-size: 30px;
            font-weight: 700;
        }
        .sidebar-brand img {
             height: 55px;
           
        }
        @media print {
    /* Sembunyikan sidebar dan tombol toggle */
    .sidebar, 
    .toggle-btn {
        display: none !important;
    }

    /* Pastikan konten utama menggunakan seluruh lebar kertas */
    /* Ganti .main-content dengan class pembungkus konten utama Anda */
    .main-content, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }
}
    </style>
<body>

<div class="toggle-btn" id="sidebarToggle">
    <i class="fas fa-chevron-left"></i>
</div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
       <img src="../assets/logo/iconapk.png" alt="Logo">
        <span></span>
        <span>MySPP</span>
    </div>

    <div class="menu-label">Menu Utama</div>
    
    <a href="../index.php" class="<?= in_array($current, ['index.php', '']) ? 'active' : '' ?>">
        <i class="fas fa-home"></i> <span>Dashboard</span>
    </a>
    
    <a href="../siswa/list.php" class="<?= $current == 'list.php' ? 'active' : '' ?>">
        <i class="fas fa-users"></i> <span>Data Siswa</span>
    </a>

    <div class="menu-label">Transaksi</div>
    
    <a href="../pembayaran/listpembayaran.php" class="<?= $current == 'listpembayaran.php' ? 'active' : '' ?>">
        <i class="fas fa-credit-card"></i> <span>List Pembayaran</span>
    </a>
    
    <a href="../pembayaran/listsetoran.php" class="<?= $current == 'listsetoran.php' ? 'active' : '' ?>">
        <i class="fas fa-box-archive"></i> <span>List Setoran</span>
    </a>

    <a href="../pembayaran/mamin.php" class="<?= $current == 'mamin.php' ? 'active' : '' ?>">
        <i class="fa-solid fa-utensils"></i> <span>List Mamin</span>
    </a>

    <div class="menu-label">Laporan</div>
    
    <a href="../pembayaran/laporan.php" class="<?= $current == 'laporan.php' ? 'active' : '' ?>">
        <i class="fas fa-chart-line"></i> <span>Rekap Pembayaran</span>
    </a>

    <a href="../pembayaran/targetsetoran.php" class="<?= $current == 'targetsetoran.php' ? 'active' : '' ?>">
        <i class="fa fa-bullseye"></i> <span>Target Setoran</span>
    </a>
    
    <a href="../templates/backup_restore.php" class="<?= $current == 'backup_restore.php' ? 'active' : '' ?>">
        <i class="fa fa-cloud-upload-alt"></i> <span>Backup & Restore</span>
    </a>
</div>

<script>
    const toggleBtn = document.getElementById('sidebarToggle');
    const body = document.body;

    // Load status sidebar dari localStorage saat halaman dimuat
    const sidebarStatus = localStorage.getItem('sidebarStatus');
    if (sidebarStatus === 'collapsed') {
        body.classList.add('collapsed');
    }

    toggleBtn.addEventListener('click', () => {
        body.classList.toggle('collapsed');
        
        // Simpan status ke localStorage agar awet saat refresh/pindah halaman
        if (body.classList.contains('collapsed')) {
            localStorage.setItem('sidebarStatus', 'collapsed');
        } else {
            localStorage.setItem('sidebarStatus', 'expanded');
        }
    });
</script>

</body>
</html>
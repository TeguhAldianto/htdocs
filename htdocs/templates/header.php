<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --header-bg: #4f46e5;
            --header-height: 70px;
            --glass-bg: rgba(255, 255, 255, 0.15);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }

        .header-app {
            height: var(--header-height);
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 30px;
            position: fixed;
            top: 0; left: 0; right: 0;
            z-index: 1050;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-title {
            display: flex;
            align-items: center;
            font-size: 30px;
            font-weight: 700;
            text-decoration: none;
            color: white;
          
        }

        .header-title img {
            height: 40px;
            margin-right: 12px;
        }

        /* Profil Dropdown Styling */
        .profile-menu {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 5px 15px;
            border-radius: 50px;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            text-decoration: none;
        }

        .profile-menu:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }

        .avatar-circle {
            width: 35px;
            height: 35px;
            background-color: #a5f3fc;
            color: #3730a3;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }

        .user-info {
            text-align: left;
            line-height: 1.2;
        }

        .user-name {
            display: block;
            font-size: 14px;
            font-weight: 600;
        }

        .user-role {
            display: block;
            font-size: 11px;
            color: rgba(255, 255, 255, 0.8);
        }

        /* Dropdown Menu Customization */
        .dropdown-menu {
            border: none;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            border-radius: 12px;
            margin-top: 10px !important;
            padding: 8px;
        }

        .dropdown-item {
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .dropdown-item i {
            width: 20px;
            color: #64748b;
        }

        .dropdown-item:hover {
            background-color: #f1f5f9;
            color: #4f46e5;
        }

        .dropdown-item:hover i {
            color: #4f46e5;
        }

        .dropdown-divider {
            margin: 8px 0;
            opacity: 0.1;
        }

        @media (max-width: 576px) {
            .header-title span, .user-info { display: none; }
            .header-app { padding: 0 15px; }
        }
    </style>
</head>
<body>

<header class="header-app">
    <a href="index.php" class="header-title">
        <img src="../assets/img/logo.png" alt="Logo">
        <span>MySPP</span>
    </a>

    <div class="header-right">
        <div class="dropdown">
            <div class="profile-menu" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-circle">
                    <?php echo strtoupper(substr($_SESSION['username'] ?? 'U', 0, 1)); ?>
                </div>
                <div class="user-info">
                    <span class="user-name"><?php echo $_SESSION['username'] ?? 'Pengguna'; ?></span>
                    <span class="user-role">Admin</span>
                </div>
                <i class="fa-solid fa-chevron-down" style="font-size: 12px; opacity: 0.7;"></i>
            </div>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <h6 class="dropdown-header">Kelola Akun</h6>
                </li>
                <li>
                    <a class="dropdown-item" href="ganti-password.php">
                        <i class="fa-solid fa-key"></i> Ganti Password
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="profil.php">
                        <i class="fa-solid fa-user-gear"></i> Pengaturan Profil
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="logout.php">
                        <i class="fa-solid fa-right-from-bracket"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
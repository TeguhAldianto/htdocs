<?php
session_start();
require 'config.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Ambil data user terbaru dari database
$user_id = $_SESSION['user_id'];
$stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - DorkasPay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
        }
        .profile-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        .profile-card {
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: none;
        }
        .profile-header {
            background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);
            height: 120px;
            position: relative;
        }
        .avatar-wrapper {
            position: absolute;
            bottom: -50px;
            left: 50%;
            transform: translateX(-50%);
        }
        .avatar-main {
            width: 100px;
            height: 100px;
            background: #a5f3fc;
            border: 5px solid white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: #3730a3;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .profile-body {
            padding-top: 60px;
            padding-bottom: 30px;
            text-align: center;
        }
        .info-list {
            text-align: left;
            margin-top: 30px;
            padding: 0 30px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .info-label {
            color: #64748b;
            font-size: 14px;
        }
        .info-value {
            font-weight: 600;
            color: #1e293b;
        }
        .btn-edit {
            background-color: #f1f5f9;
            color: #475569;
            border: none;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .btn-edit:hover {
            background-color: #e2e8f0;
            color: #1e293b;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <div class="mb-4">
        <a href="index.php" class="text-decoration-none text-muted small">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="profile-card">
        <div class="profile-header">
            <div class="avatar-wrapper">
                <div class="avatar-main">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
            </div>
        </div>
        
        <div class="profile-body">
            <h4 class="fw-bold mb-1"><?php echo htmlspecialchars($user['username']); ?></h4>
            <span class="badge bg-light text-primary border px-3 py-2 rounded-pill">Siswa Aktif</span>

            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">User ID</span>
                    <span class="info-value">#<?php echo $user['id']; ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Username</span>
                    <span class="info-value"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status Akun</span>
                    <span class="info-value text-success">Verifikasi</span>
                </div>
            </div>

            <div class="px-4 mt-4 d-grid gap-2">
                <a href="ganti-password.php" class="btn btn-edit py-2">
                    <i class="fa-solid fa-lock me-2"></i> Keamanan Password
                </a>
                <a href="logout.php" class="btn btn-outline-danger border-0 py-2 small">
                    Keluar dari Akun
                </a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
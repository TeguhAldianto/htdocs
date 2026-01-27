<?php
session_start();
require 'config.php'; // Pastikan koneksi $db Anda benar

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$success = "";
$error = "";

if (isset($_POST['update_password'])) {
    $user_id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    try {
        // 1. Ambil password lama dari database
        $stmt = $db->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->execute(['id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Validasi
        if (!password_verify($old_password, $user['password'])) {
            $error = "Password lama Anda salah!";
        } elseif ($new_password !== $confirm_password) {
            $error = "Konfirmasi password baru tidak cocok!";
        } elseif (strlen($new_password) < 6) {
            $error = "Password baru minimal 6 karakter!";
        } else {
            // 3. Update Password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update = $db->prepare("UPDATE users SET password = :pass WHERE id = :id");
            $update->execute([
                'pass' => $hashed_password,
                'id' => $user_id
            ]);
            $success = "Password berhasil diperbarui!";
        }
    } catch(PDOException $e) {
        $error = "Terjadi kesalahan sistem.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - DorkasPay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f8fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card-custom {
            width: 100%;
            max-width: 450px;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: none;
        }
        .btn-primary {
            background-color: #4f46e5;
            border: none;
            padding: 12px;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background-color: #3730a3;
        }
        .form-control {
            padding: 12px;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }
        .back-link {
            text-decoration: none;
            color: #64748b;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="card-custom">
    <a href="index.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard
    </a>
    
    <h4 class="fw-bold mb-1">Ganti Password</h4>
    <p class="text-muted small mb-4">Pastikan password baru Anda kuat dan mudah diingat.</p>

    <?php if($success): ?>
        <div class="alert alert-success border-0 small"><?php echo $success; ?></div>
    <?php endif; ?>

    <?php if($error): ?>
        <div class="alert alert-danger border-0 small"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="" method="post">
        <div class="mb-3">
            <label class="form-label small fw-semibold">Password Lama</label>
            <input type="password" name="old_password" class="form-control" placeholder="••••••••" required>
        </div>
        
        <hr class="my-4 opacity-50">

        <div class="mb-3">
            <label class="form-label small fw-semibold">Password Baru</label>
            <input type="password" name="new_password" class="form-control" placeholder="Minimal 6 karakter" required>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-semibold">Konfirmasi Password Baru</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="Ulangi password baru" required>
        </div>

        <button type="submit" name="update_password" class="btn btn-primary w-100">
            Simpan Perubahan
        </button>
    </form>
</div>

</body>
</html>

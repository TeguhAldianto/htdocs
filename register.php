<?php
ob_start();
session_start();
require 'config.php';

// AKTIFKAN INI UNTUK MELIHAT ERROR
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['register'])) {
    $username = strtolower(stripslashes(trim($_POST['username'])));
    $password = $_POST['password'];
    $confirm  = $_POST['confirm'];

    if (empty($username) || empty($password)) {
        echo "<script>alert('Data tidak boleh kosong!');</script>";
    } elseif ($password !== $confirm) {
        echo "<script>alert('Konfirmasi password salah!');</script>";
    } else {
        try {
            // Cek Username
            $stmt = $db->prepare("SELECT username FROM users WHERE username = :user");
            $stmt->execute(['user' => $username]);
            
            if ($stmt->fetch()) {
                echo "<script>alert('Username sudah ada!'); window.location.href='register.php';</script>";
            } else {
                // Hash Password
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                
                // Simpan User
                $insert = $db->prepare("INSERT INTO users (username, password) VALUES (:user, :pass)");
                $insert->execute([
                    'user' => $username,
                    'pass' => $hashed
                ]);

                echo "<script>alert('Berhasil Daftar!'); window.location.href='login.php';</script>";
                exit;
            }
        } catch (PDOException $e) {
            // JIKA ERROR, TAMPILKAN DI SINI
            die("Gagal menyimpan ke database: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daftar DorkasPay</title>
    <style>
        body { font-family: sans-serif; display: flex; justify-content: center; padding: 50px; background: #f0f0f0; }
        .box { background: white; padding: 20px; border-radius: 10px; width: 300px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input { width: 100%; padding: 10px; margin-bottom: 10px; border: 1px solid #ccc; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #007bff; color: white; border: none; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h2>Registrasi</h2>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm" placeholder="Ulangi Password" required>
            <button type="submit" name="register">Daftar Akun</button>
        </form>
        <p><a href="login.php">Sudah punya akun? Login</a></p>
    </div>
</body>
</html>
<?php
session_start();
ob_start();
require 'config.php'; 

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['login'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header("Location: index.php");
            exit;
        }
        $error = "Username atau Password salah!";
    } catch(PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - MySPP</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f8fafc;
            --text-dark: #1e293b;
            --error-bg: #fee2e2;
            --error-text: #b91c1c;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-color: var(--bg-color); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0;
            color: var(--text-dark);
        }

        .login-box { 
            background: white; 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); 
            width: 100%; 
            max-width: 400px; 
        }

        .header { text-align: center; margin-bottom: 30px; }
        .header h2 { margin: 0; font-size: 24px; font-weight: 700; color: #111827; }
        .header p { color: #6b7280; font-size: 14px; margin-top: 8px; }

        label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; }

        input { 
            width: 100%; 
            padding: 12px 16px; 
            margin-bottom: 20px; 
            border: 1.5px solid #e5e7eb; 
            border-radius: 8px; 
            box-sizing: border-box; 
            font-size: 15px;
            transition: all 0.2s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        button { 
            width: 100%; 
            padding: 12px; 
            background-color: var(--primary-color); 
            color: white; 
            border: none; 
            border-radius: 8px; 
            cursor: pointer; 
            font-size: 16px; 
            font-weight: 600;
            transition: background 0.2s;
        }

        button:hover { background-color: var(--primary-hover); }

        .error { 
            color: var(--error-text); 
            background: var(--error-bg); 
            padding: 12px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            font-size: 14px; 
            text-align: center;
            border: 1px solid #fecaca;
        }

        .footer-link { 
            text-align: center; 
            margin-top: 25px; 
            font-size: 14px; 
            color: #6b7280;
        }

        .footer-link a { 
            color: var(--primary-color); 
            text-decoration: none; 
            font-weight: 600; 
        }
        
        .header img {
            height: 70px;
        }

        .footer-link a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="header">
             <img src="assets/logo/iconapk.png" alt="Logo">
            <h2>MySPP</h2>
            <p>Silakan masuk ke akun Anda</p>
        </div>
        
        <?php if(isset($error)) : ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="" method="post">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required>
            
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="••••••••" required>
            
            <button type="submit" name="login">Masuk Sekarang</button>
        </form>

        <div class="footer-link">
            Belum punya akun? <a href="register.php">Daftar Gratis</a>
        </div>
    </div>
</body>
</html>
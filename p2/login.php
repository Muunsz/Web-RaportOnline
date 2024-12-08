<?php
session_start();
require_once 'db_connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'siswa') {
            header("Location: siswa_dashboard.php");
        } elseif ($user['role'] == 'guru' || $user['role'] == 'admin') {
            header("Location: guru_dashboard.php");
        }
        exit();
    } else {
        $error = "Username atau password salah";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Raport Online SMKN 1 Katapang</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #1c1c1c, #444);
            font-family: Arial, sans-serif;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
            color: #fff;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .container {
            background-color: #222;
            border-radius: 20px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            padding: 30px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            margin: auto;
            animation: fadeIn 1s ease-in-out;
        }

        .container h1 {
            color: #ffc107;
            font-size: 24px;
            margin-bottom: 10px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin: 10px 0;
            border: 1px solid #333;
            border-radius: 10px;
            background-color: #1a1a1a;
            color: #fff;
            box-sizing: border-box;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder {
            color: #777;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            outline: 2px solid #ffc107;
        }

        input[type="text"]:hover,
        input[type="password"]:hover {
            border-color: #ffc107;
        }

        .options button {
            width: 100%;
            padding: 12px;
            margin: 5px 0;
            border: none;
            border-radius: 10px;
            background-color: #ffc107;
            color: #000;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .options button:hover {
            background-color: #e0a806;
            box-shadow: 0px 4px 8px rgba(255, 193, 7, 0.4);
        }

        .additional-options {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .additional-options a {
            color: #ffc107;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .additional-options a:hover {
            color: #e0a806;
            text-decoration: underline;
        }

        .error {
            color: #ff6b6b;
            margin-bottom: 10px;
        }

        footer {
            background: #222;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            margin-top: auto;
            box-shadow: 0px -4px 10px rgba(0, 0, 0, 1);
        }

        footer h1 {
            margin-bottom: 10px;
            font-size: 24px;
        }

        .footer-nav a {
            color: #ffc107;
            margin: 0 10px;
            text-decoration: none;
        }

        .footer-nav a:hover {
            text-decoration: underline;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }

        .social-icons a {
            text-decoration: none;
            color: #fff;
            font-size: 24px;
            margin: 0 15px;
            transition: color 0.3s ease, transform 0.3s ease;
        }

        .social-icons a:hover {
            color: #ffc107;
            transform: scale(1.2);
        }

        footer p {
            font-size: 14px;
            color: #bbbbbb;
        }

        @media (max-width: 480px) {
            .container {
                padding: 20px;
            }

            .social-icons a {
                font-size: 20px;
                margin: 0 10px;
            }

            footer {
                padding: 20px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="../poto/115-SMKN_1_KATAPANG.png" alt="SMK 1 KATAPANG">
        <h1>Login Raport Online SMKN 1 Katapang</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="post">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <div class="options">
                <button type="submit">Login</button>
            </div>
        </form>
        <div class="additional-options">
            <a href="../p2/html/dashboard.html">Kembali</a>
            <a href="register.php">Buat Akun</a>
        </div>
    </div>

    <footer>
        <h1>SMK Negeri 1 Katapang</h1>
        <div class="footer-nav">
            <a href="../html/saran dan pengaduan.html">Saran</a>
            <a href="https://www.smkn1katapang.sch.id">Sekolah</a>
            <a href="https://www.instagram.com/syann_n/">Admin</a>
        </div>
        <h3>Connect with us</h3>
        <div class="social-icons">
            <a href="https://instagram.com/smkn1katapang/" target="_blank" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://www.youtube.com/@smkn1katapang242" target="_blank" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            <a href="https://www.tiktok.com/@smkn1katapang" target="_blank" aria-label="TikTok"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.facebook.com/groups/smkn1katapang" target="_blank" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
            <a href="https://whatsapp.com" target="_blank" aria-label="Whatsapp"><i class="fab fa-whatsapp"></i></a>
        </div>
        <p>Copyright 2024, SMK Negeri 1 Katapang. Hak cipta dilindungi undang-undang.</p>
    </footer>
</body>
</html>
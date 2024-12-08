<?php
session_start();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if username already exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->rowCount() > 0) {
        $error = "Username sudah digunakan. Silakan pilih username lain.";
    } else {
        // Insert new user
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $password, $role])) {
            $_SESSION['success'] = "Akun berhasil dibuat. Silakan login.";
            header("Location: login.php");
            exit();
        } else {
            $error = "Terjadi kesalahan saat membuat akun.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8"><meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Raport Online SMKN 1 Katapang</title>
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

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .container h1 {
            color: #ffc107;
            font-size: 24px;
            margin-bottom: 20px;
        }

        input[type="text"],
        input[type="password"],
        select {
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
        input[type="password"]:focus,
        select:focus {
            outline: 2px solid #ffc107;
        }

        button {
            width: 100%;
            padding: 12px;
            margin: 20px 0 10px;
            border: none;
            border-radius: 10px;
            background-color: #ffc107;
            color: #000;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        button:hover {
            background-color: #e0a806;
            box-shadow: 0px 4px 8px rgba(255, 193, 7, 0.4);
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

        p {
            margin-top: 20px;
        }

        a {
            color: #ffc107;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #e0a806;
            text-decoration: underline;
        }

        footer {
            background: #222;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            margin-top: auto;
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
            margin: 15px 0;
        }

        .social-icons a {
            color: #fff;
            font-size: 24px;
            margin: 0 15px;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #ffc107;
        }

        footer p {
            font-size: 14px;
            color: #bbb;
        }

        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
    <img src="../poto/115-SMKN_1_KATAPANG.png" alt="SMK 1 KATAPANG">
        <h1>Daftar Akun Baru</h1>
        <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
        <form action="" method="post">
            <input type="text" id="username" name="username" placeholder="Username" required>
            <input type="password" id="password" name="password" placeholder="Password" required>
            <select id="role" name="role" required>
                <option value="">Pilih Peran</option>
                <option value="siswa">Siswa</option>
                <option value="guru">Guru</option>
            </select>
            <button type="submit">Daftar</button>
        </form>
        <div class="additional-options">
            <a href="../p2/html/dashboard.html">Kembali</a>
            <a href="login.php">Login di sini</a>
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
            <a href="https://instagram.com/smkn1katapang/" target="_blank"><i class="fab fa-instagram"></i></a>
            <a href="https://www.youtube.com/@smkn1katapang242" target="_blank"><i class="fab fa-youtube"></i></a>
            <a href="https://www.tiktok.com/@smkn1katapang" target="_blank"><i class="fab fa-tiktok"></i></a>
            <a href="https://www.facebook.com/groups/smkn1katapang" target="_blank"><i class="fab fa-facebook"></i></a>
            <a href="https://whatsapp.com" target="_blank"><i class="fab fa-whatsapp"></i></a>
        </div>
        <p>&copy; 2024, SMK Negeri 1 Katapang. Hak cipta dilindungi undang-undang.</p>
    </footer>
</body>
</html>


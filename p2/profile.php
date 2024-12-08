<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$dashboard_url = ($role == 'siswa') ? 'siswa_dashboard.php' : 'guru_dashboard.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $new_username = $_POST['username'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Password baru dan konfirmasi password tidak cocok.";
    } else {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
            $stmt->execute([$new_username, $user_id]);

            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
            }

            $pdo->commit();
            $success = "Profil berhasil diperbarui.";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna</title>
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
            max-width: 800px;
            margin: 20px auto;
            animation: fadeIn 1s ease-in-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        h1, h2 {
            color: #ffc107;
            text-align: center;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-top: 10px;
            color: #ffc107;
        }

        input[type="text"],
        input[type="password"] {
            width: 97%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #444;
            background-color: #333;
            color: #fff;
            border-radius: 5px;
        }

        button {
            background-color: #ffc107;
            color: #000;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }

        button:hover {
            background-color: #e0a800;
        }

        .success {
            color: #4CAF50;
            text-align: center;
            margin-bottom: 20px;
        }

        .error {
            color: #f44336;
            text-align: center;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            background-color: #ffc107;
            color: #000;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin-top: 20px;
            text-align: center;
        }

        .button:hover {
            background-color: #e0a800;
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
        <h1>Profil Pengguna</h1>
        <?php if(isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>

            <label for="new_password">Password Baru (kosongkan jika tidak ingin mengubah):</label>
            <input type="password" id="new_password" name="new_password">

            <label for="confirm_password">Konfirmasi Password Baru:</label>
            <input type="password" id="confirm_password" name="confirm_password">

            <button type="submit">Simpan Perubahan</button>
        </form>
        <a href="<?php echo $dashboard_url; ?>" class="button">Kembali ke Dashboard</a>
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
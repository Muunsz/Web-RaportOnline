<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'siswa') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch student data
$stmt = $pdo->prepare("SELECT s.*, u.username FROM siswa s JOIN users u ON s.user_id = u.id WHERE u.id = ?");
$stmt->execute([$user_id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    $error = "Data siswa tidak ditemukan. Silakan hubungi admin untuk mengaitkan akun Anda dengan data siswa.";
} else {
    // Fetch available classes
    $stmt = $pdo->query("SELECT DISTINCT diterima_di_kelas FROM siswa");
    $kelas_tersedia = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Siswa - Raport Online SMKN 1 Katapang</title>
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

        nav {
            position: sticky;
            top: 0;
            z-index: 1000;
            background: linear-gradient(135deg, #444, #000);
            padding: 10px 20px;
            border-bottom: 2px solid #fff;
        }

        .nav-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        nav li {
            margin: 0 15px;
            position: relative;
        }

        nav a {
            text-decoration: none;
            color: #fff;
            font-size: 16px;
            padding: 10px 5px;
            position: relative;
        }

        nav a:hover {
            color: #ffc107;
        }

        nav a.active {
            color: #ffc107;
        }

        nav a.active::after {
            content: "";
            display: block;
            width: 50%;
            height: 2px;
            background-color: #fff;
            margin: 0 auto;
            position: absolute;
            bottom: -5px;
            left: 0;
            right: 0;
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
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h1, h2 {
            color: #ffc107;
            text-align: center;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 20px;
            margin-bottom: 15px;
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

        @media (max-width: 768px) {
            nav ul {
                flex-direction: column;
                display: none;
                background-color: #000;
                position: absolute;
                top: 50px;
                right: 20px;
                padding: 10px;
                border-radius: 10px;
            }

            nav ul.show {
                display: flex;
            }

            nav ul .dropdown-menu {
                position: static;
                box-shadow: none;
            }

            nav ul li {
                width: 100%;
            }

            .burger-menu {
                display: block;
                cursor: pointer;
            }

            .burger-menu div {
                width: 25px;
                height: 3px;
                background-color: #fff;
                margin: 5px 0;
            }
        }

        @media (max-width: 600px) {
            .container {
                width: 95%;
                padding: 20px;
            }
        }

        #pilih-kelas {
            margin-top: 20px;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
        }

        #pilih-kelas select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: none;
            background-color: #444;
            color: #fff;
        }

        #pilih-kelas button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background-color: #ffc107;
            color: #000;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        #pilih-kelas button:hover {
            background-color: #e0a800;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <div class="logo-section">
                <span class="logo-text">SMK Negeri 1 Katapang</span>
            </div>
            <ul>
                <li><a href="siswa_dashboard.php" class="active">Dashboard</a></li>
                <?php if (!isset($error)): ?>
                    <li><a href="#" id="lihat-raport">Lihat Raport</a></li>
                <?php endif; ?>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <p>Silakan hubungi administrator untuk menyelesaikan masalah ini.</p>
        <?php else: ?>
            <h1>Selamat datang, <?php echo htmlspecialchars($siswa['nama']); ?></h1>
            
            <h3>Informasi Siswa</h3>
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($siswa['nama']); ?></p>
            <p><strong>NIS:</strong> <?php echo htmlspecialchars($siswa['nis']); ?></p>
            <p><strong>Kelas:</strong> <?php echo htmlspecialchars($siswa['diterima_di_kelas']); ?></p>

            <div id="pilih-kelas" style="display: none;">
                <h2>Pilih Kelas untuk Melihat Raport</h2>
                <form action="lihat_raport.php" method="get">
                    <input type="hidden" name="siswa_id" value="<?php echo $siswa['id']; ?>">
                    <select name="kelas" required>
                        <?php foreach ($kelas_tersedia as $kelas): ?>
                            <option value="<?php echo htmlspecialchars($kelas); ?>"><?php echo htmlspecialchars($kelas); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Lihat Raport</button>
                </form>
            </div>

            <div class="button-container">
                <a href="#" class="button" id="lihat-raport-button">Lihat Raport</a>
                <a href="profile.php" class="button">Edit Profil</a>
                <a href="logout.php" class="button">Logout</a>
            </div>
        <?php endif; ?>
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

    <script>
        document.getElementById('lihat-raport').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('pilih-kelas').style.display = 'block';
        });

        document.getElementById('lihat-raport-button').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('pilih-kelas').style.display = 'block';
        });
    </script>
</body>
</html>


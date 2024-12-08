<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['siswa_id']) && isset($_GET['confirm']) && $_GET['confirm'] == 'yes') {
    $siswa_id = $_GET['siswa_id'];
    
    try {
        $pdo->beginTransaction();

        // Delete related records from other tables
        $tables = ['nilai_akademik', 'ekstrakurikuler', 'ketidakhadiran', 'laporan_project', 'perkembangan_karakter', 'wali', 'orang_tua'];
        foreach ($tables as $table) {
            $stmt = $pdo->prepare("DELETE FROM $table WHERE siswa_id = ?");
            $stmt->execute([$siswa_id]);
        }

        // Get user_id associated with the student
        $stmt = $pdo->prepare("SELECT user_id FROM siswa WHERE id = ?");
        $stmt->execute([$siswa_id]);
        $user_id = $stmt->fetchColumn();

        // Delete the student record
        $stmt = $pdo->prepare("DELETE FROM siswa WHERE id = ?");
        $stmt->execute([$siswa_id]);

        // Delete the user record if it exists
        if ($user_id) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
        }

        $pdo->commit();
        $_SESSION['success_message'] = "Raport siswa dan semua data terkait berhasil dihapus.";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error_message'] = "Gagal menghapus raport: " . $e->getMessage();
    }

    header("Location: guru_dashboard.php");
    exit();
}

if (!isset($_GET['siswa_id'])) {
    header("Location: guru_dashboard.php");
    exit();
}

$siswa_id = $_GET['siswa_id'];
$stmt = $pdo->prepare("SELECT nama, nis FROM siswa WHERE id = ?");
$stmt->execute([$siswa_id]);
$siswa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$siswa) {
    $_SESSION['error_message'] = "Siswa tidak ditemukan.";
    header("Location: guru_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hapus Raport</title>
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

        h1 {
            color: #ffc107;
            text-align: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        p {
            margin-bottom: 15px;
        }

        .warning {
            color: #ff6b6b;
            font-weight: bold;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 30px;
        }

        .button {
            display: inline-block;
            background-color: #ffc107;
            color: #000;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .button:hover {
            background-color: #e0a800;
        }

        .delete-button {
            background-color: #ff6b6b;
        }

        .delete-button:hover {
            background-color: #ff4757;
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
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <div class="logo-section">
                <span class="logo-text">SMK Negeri 1 Katapang</span>
            </div>
            <ul>
                <li><a href="guru_dashboard.php">Dashboard</a></li>
                <li><a href="tambah_raport.php">Tambah Raport</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Konfirmasi Penghapusan Raport</h1>
        <p>Anda yakin ingin menghapus raport untuk siswa berikut?</p>
        <p><strong>Nama:</strong> <?php echo htmlspecialchars($siswa['nama']); ?></p>
        <p><strong>NIS:</strong> <?php echo htmlspecialchars($siswa['nis']); ?></p>
        <p class="warning">Peringatan: Tindakan ini akan menghapus semua data terkait siswa ini, termasuk nilai akademik, ekstrakurikuler, ketidakhadiran, laporan project, perkembangan karakter, data wali, dan data orang tua. Tindakan ini tidak dapat dibatalkan!</p>
        <div class="action-buttons">
            <a href="hapus_raport.php?siswa_id=<?php echo $siswa_id; ?>&confirm=yes" class="button delete-button">Ya, Hapus Semua Data</a>
            <a href="guru_dashboard.php" class="button">Batal</a>
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
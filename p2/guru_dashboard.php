<?php
session_start();
require_once 'db_connect.php';
require_once 'Student.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

$student = new Student($pdo);

// Fetch the current user's information
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle search
$keyword = isset($_GET['search']) ? trim($_GET['search']) : '';
$siswa_list = $keyword ? $student->searchStudents($keyword) : $student->getAllStudents();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru</title>
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

        h1, h2, h3 {
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

        h3 {
            font-size: 18px;
            margin-top: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #444;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #333;
            color: #ffc107;
        }

        tr:nth-child(even) {
            background-color: #2a2a2a;
        }

        .button {
            display: inline-block;
            background-color: #ffc107;
            color: #000;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            margin: 5px;
            font-size: 14px;
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
            margin-bottom: 25px;
            font-size: 30px;
            color: white;
        }

        .footer-nav a {
            color: #fff;
            margin: 0 10px;
            text-decoration: none;
        }

        .footer-nav a:hover {
            color: #ffc107;
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

        .search-form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .search-form input[type="text"] {
            padding: 10px;
            font-size: 16px;
            border: none;
            border-radius: 5px 0 0 5px;
            width: 60%;
            max-width: 300px;
        }

        .search-form button {
            padding: 10px 20px;
            background-color: #ffc107;
            color: #000;
            border: none;
            border-radius: 0 5px 5px 0;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .search-form button:hover {
            background-color: #e0a800;
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

            nav li {
                margin: 10px 0;
            }

            .container {
                width: 95%;
                padding: 20px;
            }

            .search-form {
                flex-direction: column;
                align-items: center;
            }

            .search-form input[type="text"],
            .search-form button {
                width: 100%;
                max-width: none;
                border-radius: 5px;
                margin-bottom: 10px;
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
                <li><a href="guru_dashboard.php" class="active">Dashboard</a></li>
                <li><a href="tambah_raport.php">Tambah Raport</a></li>
                <li><a href="profile.php">Profil</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <h1>Dashboard Guru</h1>
        <h2>Selamat datang, <?php echo htmlspecialchars($user['username']); ?>!</h2>
        
        <form class="search-form" action="" method="GET">
            <input type="text" name="search" placeholder="Cari siswa..." value="<?php echo htmlspecialchars($keyword); ?>">
            <button type="submit">Cari</button>
        </form>

        <h3>Daftar Siswa</h3>
        <table>
            <tr>
                <th>NIS</th>
                <th>Nama</th>
                <th>Kelas</th>
                <th>Aksi</th>
            </tr>
            <?php if (!empty($siswa_list)): ?>
                <?php foreach ($siswa_list as $siswa): ?>
                <tr>
                    <td><?php echo htmlspecialchars($siswa['nis']); ?></td>
                    <td><?php echo htmlspecialchars($siswa['nama']); ?></td>
                    <td><?php echo htmlspecialchars($siswa['diterima_di_kelas']); ?></td>
                    <td>
                        <a href="lihat_raport.php?siswa_id=<?php echo $siswa['id']; ?>" class="button">Lihat Raport</a>
                        <a href="edit_raport.php?siswa_id=<?php echo $siswa['id']; ?>" class="button">Edit Raport</a>
                        <a href="hapus_raport.php?siswa_id=<?php echo $siswa['id']; ?>" class="button">Hapus</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4">Tidak ada data siswa.</td>
                </tr>
            <?php endif; ?>
        </table>
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
        document.addEventListener('DOMContentLoaded', (event) => {
            const burger = document.querySelector('.burger-menu');
            const nav = document.querySelector('nav ul');
            
            burger.addEventListener('click', () => {
                nav.classList.toggle('show');
            });
        });
    </script>
</body>
</html>
<?php
require_once 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saran dan Pengaduan</title>
    <link rel="stylesheet" href="../css/dashboard.css">
</head>
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            color: #ffffff;
            background-color: #333;
        }
        .main-content {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        h1, h2, h3 {
            color: #ffc107;
            margin: 20px 0 10px;
        }
        p, ul {
            color: #fff;
            line-height: 1.6;
        }
        a {
            text-decoration: none;
            color: inherit;
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
        .logo-section {
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
        .logo-text {
            font-size: 18px;
            font-weight: bold;
            color: #fff;
            margin-right: 10px;
        }
        .logo img {
            height: 40px;
            width: auto;
            border-radius: 5px;
        }
        .feedback-section {
            background-color: #222;
            color: #fff;
            width: 80%;
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            text-align: center;
        }
        .feedback-form {
            display: flex;
            flex-direction: column;
        }
        .feedback-form input,
        .feedback-form textarea {
            background-color: #333;
            color: #fff;
            border: 1px solid #444;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            font-size: 16px;
            outline: none;
        }
        .feedback-form button {
            background-color: #ffc107;
            color: #000;
            border: none;
            padding: 12px 25px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: inline-block;
        }
        .feedback-form button:hover {
            background-color: #e0a800;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #ffc107;
            color: #000;
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.5);
            font-size: 16px;
            font-weight: bold;
            opacity: 0;
            transform: translateY(-20px);
            transition: all 0.5s ease;
            z-index: 1000;
        }
        .notification.show {
            opacity: 1;
            transform: translateY(0);
        }
        .selesai-button {
            background: linear-gradient(145deg, #4CAF50, #45a049);
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            font-size: 18px;
            font-weight: bold;
            margin-top: 50px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
            position: relative;
            overflow: hidden;
            display: inline-block;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .selesai-button:before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(120deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: all 0.5s;
        }

        .selesai-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
        }

        .selesai-button:hover:before {
            left: 100%;
        }

        .selesai-button:active {
            transform: translateY(1px);
            box-shadow: 0 2px 10px rgba(76, 175, 80, 0.4);
        }
        footer {
            background: #222;
            margin-top: 5%;
            color: #fff;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0px -4px 10px rgba(0, 0, 0, 1);
            width: 100%;
        }
        .social-icons {
            display: flex;
            justify-content: center;
            margin-bottom: 15px;
        }
        .social-icons a {
            color: #ffffff;
            font-size: 24px;
            margin: 0 15px;
            transition: color 0.3s ease, transform 0.3s ease;
        }
        .social-icons a:hover {
            color: #ffc107;
            transform: scale(1.2);
        }
        .dropdown {
            position: relative;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            background-color: #333;
            min-width: 160px;
            box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2);
            z-index: 1;
        }

        .dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu li {
            margin: 0;
        }

        .dropdown-menu a {
            color: white;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
        }

        .dropdown-menu a:hover {
            background-color: #444;
        }

        .footer-nav {
            margin: 20px 0;
        }

        .footer-nav a {
            margin: 0 10px;
            color: #fff;
            text-decoration: none;
        }

        .footer-nav a.active {
            color: #ffc107;
        }

        footer h1, footer h3 {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-container">
            <ul>
                <li><a href="../html/dashboard.html">Beranda</a></li>
                <li class="dropdown">
                    <a href="#">Sekolah</a>
                    <ul class="dropdown-menu">
                        <li><a href="https://www.smkn1katapang.sch.id/about/">Profil Sekolah</a></li>
                        <li><a href="https://smkn1katapang.id/">Informasi</a></li>
                        <li><a href="https://smkn1katapang-bdg.sch.id/home">Berita</a></li>
                        <li><a href="https://www.smkn1katapang.sch.id/">Update PPDB</a></li>
                    </ul>
                </li>
                <li><a href="../html/Pelajaran.html">Pelajaran</a></li>
                <li><a href="../p2/siswa_dashboard.php">Raport</a></li>
                <li><a href="../p2/guru_dashboard.php">Guru</a></li>
            </ul>
            <div class="logo-section">
                <span class="logo-text">Rapot Online</span>
                <div class="logo">
                    <img src="../poto/115-SMKN_1_KATAPANG.png" alt="Logo">
                </div>
            </div>
        </div>
    </nav>

    <div class="main-content">
        <div class="feedback-section" id="feedback">
            <h2>Saran & Pengaduan</h2>
            <form class="feedback-form" id="feedbackForm">
                <input type="text" name="nama" placeholder="Nama Anda" required>
                <input type="email" name="email" placeholder="Email Anda" required>
                <textarea name="pesan" placeholder="Tulis saran atau pengaduan Anda di sini" required rows="6"></textarea>
                <button type="submit">Kirim</button>
            </form>
            <a href="../html/dashboard.html" class="selesai-button">Selesai</a>
        </div>
    </div>


<div id="notification" class="notification"></div>

<footer>
    <h1>SMK Negeri 1 Katapang</h1>
    <div class="footer-nav">
        <a href="#" class="active">Saran</a>
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

<script src="https://kit.fontawesome.com/your-fontawesome-kit.js" crossorigin="anonymous"></script>
    <script>
        document.getElementById('feedbackForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('submit_Saran_Pengaduan.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                showNotification(data.message);
                if (data.success) {
                    this.reset();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Terjadi kesalahan. Silakan coba lagi nanti.');
            });
        });

        function showNotification(message) {
            const notification = document.getElementById('notification');
            notification.textContent = message;
            notification.classList.add('show');
            setTimeout(() => {
                notification.classList.remove('show');
            }, 3000);
        }
    </script>
</body>
</html>


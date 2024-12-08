<?php
session_start();
require_once 'db_connect.php';

// Allow access for logged in users or direct access with siswa_id
if (isset($_GET['siswa_id'])) {
    $siswa_id = $_GET['siswa_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_role = $_SESSION['role'];
    
    // Determine siswa_id based on user role
    if ($user_role == 'siswa') {
        $stmt = $pdo->prepare("SELECT id FROM siswa WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $siswa = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$siswa) {
            echo "Error: Data siswa tidak ditemukan.";
            exit();
        }
        $siswa_id = $siswa['id'];
    } elseif ($user_role == 'guru' || $user_role == 'admin') {
        if (!isset($_GET['siswa_id'])) {
            echo "Error: ID siswa tidak diberikan.";
            exit();
        }
        $siswa_id = $_GET['siswa_id'];
    }
} else {
    echo "Error: Akses tidak sah.";
    exit();
}

// Fetch raport data
try {
    $stmt = $pdo->prepare("
    SELECT s.*, na.*, e.nama AS nama_ekstrakurikuler, e.nilai AS nilai_ekstrakurikuler, 
       k.sakit, k.izin, k.tanpa_keterangan, 
       lp.kemampuan_kolaborasi, lp.bernalar_kritis AS lp_bernalar_kritis, lp.kreativitas, lp.kemandirian,
       pk.beriman_bertakwa, pk.berkebinekaan_global, pk.bernalar_kritis AS pk_bernalar_kritis, pk.catatan_proses,
       o.nama_ayah, o.pekerjaan_ayah, o.nama_ibu, o.pekerjaan_ibu, o.alamat AS alamat_orang_tua, o.nomor_telepon AS nomor_telepon_orang_tua,
       w.nama AS nama_wali, w.pekerjaan AS pekerjaan_wali, w.alamat AS alamat_wali, w.nomor_telepon AS nomor_telepon_wali
    FROM siswa s
    LEFT JOIN nilai_akademik na ON s.id = na.siswa_id
    LEFT JOIN ekstrakurikuler e ON s.id = e.siswa_id
    LEFT JOIN ketidakhadiran k ON s.id = k.siswa_id
    LEFT JOIN laporan_project lp ON s.id = lp.siswa_id
    LEFT JOIN perkembangan_karakter pk ON s.id = pk.siswa_id
    LEFT JOIN orang_tua o ON s.id = o.siswa_id
    LEFT JOIN wali w ON s.id = w.siswa_id
    WHERE s.id = ?
");
    $stmt->execute([$siswa_id]);
    $raport_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ekstrak data siswa dan nilai akademik (yang sama untuk semua baris)
    $raport = $raport_data[0];

    // Pisahkan data ekstrakurikuler
    $ekstrakurikuler = array_filter($raport_data, function($row) {
        return !empty($row['nama_ekstrakurikuler']);
    });

    if (!$raport) {
        throw new Exception("Data raport tidak ditemukan.");
    }
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo "Terjadi kesalahan saat mengambil data. Silakan coba lagi nanti.";
    exit();
} catch (Exception $e) {
    echo $e->getMessage();
    exit();
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Raport Siswa - <?php echo htmlspecialchars($raport['nama']); ?></title>
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
            border-bottom: 2px solid #ffc107;
            padding-bottom: 10px;
            margin-top: 30px;
        }

        p {
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            border: 1px solid #444;
            padding: 12px;
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
            padding: 10px 20px;
            border-radius: 10px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            margin-top: 20px;
        }

        .button:hover {
            background-color: #e0a806;
            box-shadow: 0px 4px 8px rgba(255, 193, 7, 0.4);
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
            color: #fff;
            margin-bottom: 15px;
            font-size: 24px;
        } 

        footer h3 {
            color: #fff;
            margin-bottom: 10px;
            font-size: 20px;
            border-bottom: 0px;
            margin-top: 15px;
        }

        .footer-nav a {
            color: #fff;
            margin: 0 10px;
            text-decoration: none;
        }

        .footer-nav a:hover {
            text-decoration: underline;
            color: #ffc107;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            margin: 15px 0;
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

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }

            h1 {
                font-size: 20px;
            }

            h2 {
                font-size: 18px;
            }

            h3 {
                font-size: 16px;
            }

            .social-icons a {
                font-size: 20px;
                margin: 0 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Raport Siswa</h1>
        <h2><?php echo htmlspecialchars($raport['nama']); ?> (NIS: <?php echo htmlspecialchars($raport['nis']); ?>)</h2>
        
        <h3>Data Siswa</h3>
        <p><strong>Tempat Lahir:</strong> <?php echo htmlspecialchars($raport['tempat_lahir']); ?></p>
        <p><strong>Tanggal Lahir:</strong> <?php echo htmlspecialchars($raport['tanggal_lahir']); ?></p>
        <p><strong>Jenis Kelamin:</strong> <?php echo htmlspecialchars($raport['jenis_kelamin']); ?></p>
        <p><strong>Agama:</strong> <?php echo htmlspecialchars($raport['agama']); ?></p>
        <p><strong>Status dalam Keluarga:</strong> <?php echo htmlspecialchars($raport['status_keluarga']); ?></p>
        <p><strong>Anak ke-:</strong> <?php echo htmlspecialchars($raport['anak_ke']); ?></p>
        <p><strong>Alamat:</strong> <?php echo htmlspecialchars($raport['alamat']); ?></p>
        <p><strong>Nomor Telepon:</strong> <?php echo htmlspecialchars($raport['nomor_telepon']); ?></p>
        <p><strong>Asal Sekolah:</strong> <?php echo htmlspecialchars($raport['asal_sekolah']); ?></p>
        <p><strong>Diterima di Kelas:</strong> <?php echo htmlspecialchars($raport['diterima_di_kelas']); ?></p>
        <p><strong>Tanggal Diterima:</strong> <?php echo htmlspecialchars($raport['tanggal_diterima']); ?></p>

        <h3>Data Orang Tua</h3>
        <p><strong>Nama Ayah:</strong> <?php echo htmlspecialchars($raport['nama_ayah'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Pekerjaan Ayah:</strong> <?php echo htmlspecialchars($raport['pekerjaan_ayah'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Nama Ibu:</strong> <?php echo htmlspecialchars($raport['nama_ibu'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Pekerjaan Ibu:</strong> <?php echo htmlspecialchars($raport['pekerjaan_ibu'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Alamat Orang Tua:</strong> <?php echo htmlspecialchars($raport['alamat_orang_tua'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Nomor Telepon Orang Tua:</strong> <?php echo htmlspecialchars($raport['nomor_telepon_orang_tua'] ?? 'Tidak ada data'); ?></p>

        <h3>Data Wali</h3>
        <p><strong>Nama Wali:</strong> <?php echo htmlspecialchars($raport['nama_wali'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Pekerjaan Wali:</strong> <?php echo htmlspecialchars($raport['pekerjaan_wali'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Alamat Wali:</strong> <?php echo htmlspecialchars($raport['alamat_wali'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Nomor Telepon Wali:</strong> <?php echo htmlspecialchars($raport['nomor_telepon_wali'] ?? 'Tidak ada data'); ?></p>
        <h3>Nilai Akademik</h3>
        <table>
            <tr>
                <th>Mata Pelajaran</th>
                <th>Nilai</th>
            </tr>
            <?php
            $mata_pelajaran = ['matematika', 'bahasa_indonesia', 'bahasa_inggris', 'ppkn', 'pendidikan_agama', 'pendidikan_jasmani', 'ipas', 'bahasa_sunda', 'seni_musik', 'sejarah', 'bahasa_jepang', 'informatika', 'mata_pelajaran_pilihan', 'pkk', 'bimbingan_konseling', 'produktif'];
            foreach ($mata_pelajaran as $mp) {
                echo "<tr>";
                echo "<td>" . ucwords(str_replace('_', ' ', $mp)) . "</td>";
                echo "<td>" . htmlspecialchars($raport[$mp]) . "</td>";
                echo "</tr>";
            }
            ?>
        </table>

        <h3>Ekstrakurikuler</h3>
        <?php if (empty($ekstrakurikuler)): ?>
            <p>Tidak ada data ekstrakurikuler.</p>
        <?php else: ?>
            <?php foreach ($ekstrakurikuler as $ekskul): ?>
                <p><strong><?php echo htmlspecialchars($ekskul['nama_ekstrakurikuler'] ?? 'Tidak ada nama'); ?>:</strong> <?php echo htmlspecialchars($ekskul['nilai_ekstrakurikuler'] ?? 'Tidak ada nilai'); ?></p>
            <?php endforeach; ?>
        <?php endif; ?>

        <h3>Ketidakhadiran</h3>
        <p><strong>Sakit:</strong> <?php echo htmlspecialchars($raport['sakit'] ?? '0'); ?> hari</p>
        <p><strong>Izin:</strong> <?php echo htmlspecialchars($raport['izin'] ?? '0'); ?> hari</p>
        <p><strong>Tanpa Keterangan:</strong> <?php echo htmlspecialchars($raport['tanpa_keterangan'] ?? '0'); ?> hari</p>

        <h3>Laporan Project Penguatan Profil Pelajar Pancasila</h3>
        <p><strong>Kemampuan Kolaborasi:</strong> <?php echo htmlspecialchars($raport['kemampuan_kolaborasi'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Bernalar Kritis:</strong> <?php echo htmlspecialchars($raport['lp_bernalar_kritis'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Kreativitas:</strong> <?php echo htmlspecialchars($raport['kreativitas'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Kemandirian:</strong> <?php echo htmlspecialchars($raport['kemandirian'] ?? 'Tidak ada data'); ?></p>

        <h3>Perkembangan Karakter</h3>
        <p><strong>Beriman dan Bertakwa:</strong> <?php echo htmlspecialchars($raport['beriman_bertakwa'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Berkebinekaan Global:</strong> <?php echo htmlspecialchars($raport['berkebinekaan_global'] ?? 'Tidak ada data'); ?></p>
        <p><strong>Bernalar Kritis:</strong> <?php echo htmlspecialchars($raport['pk_bernalar_kritis'] ?? 'Tidak ada data'); ?></p>
        
        <h3>Catatan Dari Guru</h3>
        <p><strong>Catatan Proses:</strong> <?php echo nl2br(htmlspecialchars($raport['catatan_proses'] ?? 'Tidak ada data')); ?></p>

        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'guru' || $_SESSION['role'] == 'admin')): ?>
            <a href="edit_raport.php?siswa_id=<?php echo $siswa_id; ?>" class="button">Edit Raport</a>
        <?php endif; ?>

        <a href="<?php echo isset($_SESSION['role']) ? 
            ($_SESSION['role'] == 'siswa' ? 'siswa_dashboard.php' : 
            ($_SESSION['role'] == 'guru' ? 'guru_dashboard.php' : 'admin_dashboard.php')) 
            : 'index.php'; ?>" class="button">Kembali ke Dashboard</a>
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
        <p>&copy; 2024, SMK Negeri 1 Katapang. Hak cipta dilindungi undang-undang.</p>
    </footer>
</body>
</html>


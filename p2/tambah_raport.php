<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Insert siswa data
        $stmt = $pdo->prepare("INSERT INTO siswa (nama, nis, tempat_lahir, tanggal_lahir, jenis_kelamin, agama, status_keluarga, anak_ke, alamat, nomor_telepon, asal_sekolah, diterima_di_kelas, tanggal_diterima) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['nama'],
            $_POST['nis'],
            $_POST['tempat_lahir'],
            $_POST['tanggal_lahir'],
            $_POST['jenis_kelamin'],
            $_POST['agama'],
            $_POST['status_keluarga'],
            $_POST['anak_ke'],
            $_POST['alamat'],
            $_POST['nomor_telepon'],
            $_POST['asal_sekolah'],
            $_POST['diterima_di_kelas'],
            $_POST['tanggal_diterima']
        ]);

        $siswa_id = $pdo->lastInsertId();

        // Insert orang tua data
        $stmt = $pdo->prepare("INSERT INTO orang_tua (siswa_id, nama_ayah, nama_ibu, alamat, nomor_telepon, pekerjaan_ayah, pekerjaan_ibu) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $siswa_id,
            $_POST['nama_ayah'],
            $_POST['nama_ibu'],
            $_POST['alamat_ortu'],
            $_POST['nomor_telepon_ortu'],
            $_POST['pekerjaan_ayah'],
            $_POST['pekerjaan_ibu']
        ]);

        // Insert wali data if provided
        if (!empty($_POST['nama_wali'])) {
            $stmt = $pdo->prepare("INSERT INTO wali (siswa_id, nama, alamat, nomor_telepon, pekerjaan) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $siswa_id,
                $_POST['nama_wali'],
                $_POST['alamat_wali'],
                $_POST['nomor_telepon_wali'],
                $_POST['pekerjaan_wali']
            ]);
        }

        // Insert nilai akademik
        $stmt = $pdo->prepare("INSERT INTO nilai_akademik (siswa_id, matematika, bahasa_indonesia, bahasa_inggris, ppkn, pendidikan_agama, pendidikan_jasmani, ipas, bahasa_sunda, seni_musik, sejarah, bahasa_jepang, informatika, mata_pelajaran_pilihan, pkk, bimbingan_konseling, produktif, fase, semester, tahun_pelajaran) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $siswa_id,
            $_POST['matematika'],
            $_POST['bahasa_indonesia'],
            $_POST['bahasa_inggris'],
            $_POST['ppkn'],
            $_POST['pendidikan_agama'],
            $_POST['pendidikan_jasmani'],
            $_POST['ipas'],
            $_POST['bahasa_sunda'],
            $_POST['seni_musik'],
            $_POST['sejarah'],
            $_POST['bahasa_jepang'],
            $_POST['informatika'],
            $_POST['mata_pelajaran_pilihan'],
            $_POST['pkk'],
            $_POST['bimbingan_konseling'],
            $_POST['produktif'],
            $_POST['fase'],
            $_POST['semester'],
            $_POST['tahun_pelajaran']
        ]);

        // Insert ekstrakurikuler
        foreach ($_POST['ekstrakurikuler'] as $index => $nama_ekstrakurikuler) {
            if (!empty($nama_ekstrakurikuler)) {
                $stmt = $pdo->prepare("INSERT INTO ekstrakurikuler (siswa_id, nama, nilai) VALUES (?, ?, ?)");
                $stmt->execute([
                    $siswa_id,
                    $nama_ekstrakurikuler,
                    $_POST['nilai_ekstrakurikuler'][$index]
                ]);
            }
        }

        // Insert ketidakhadiran
        $stmt = $pdo->prepare("INSERT INTO ketidakhadiran (siswa_id, sakit, izin, tanpa_keterangan) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $siswa_id,
            $_POST['sakit'],
            $_POST['izin'],
            $_POST['tanpa_keterangan']
        ]);

        // Insert laporan project
        $stmt = $pdo->prepare("INSERT INTO laporan_project (siswa_id, kemampuan_kolaborasi, bernalar_kritis, kreativitas, kemandirian) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $siswa_id,
            $_POST['kemampuan_kolaborasi'],
            $_POST['bernalar_kritis'],
            $_POST['kreativitas'],
            $_POST['kemandirian']
        ]);

        // Insert perkembangan karakter
        $stmt = $pdo->prepare("INSERT INTO perkembangan_karakter (siswa_id, beriman_bertakwa, berkebinekaan_global, bernalar_kritis, catatan_proses) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $siswa_id,
            $_POST['beriman_bertakwa'],
            $_POST['berkebinekaan_global'],
            $_POST['bernalar_kritis_karakter'],
            $_POST['catatan_proses']
        ]);

        $pdo->commit();
        $success = "Raport berhasil ditambahkan";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Raport</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to bottom right, #1c1c1c, #1c1c1c, #444);
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

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        label {
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"],
        input[type="number"],
        textarea {
            width: 97%;
            padding: 10px;
            border: 1px solid #444;
            border-radius: 5px;
            background-color: #333;
            color: #fff;
        }

        input[type="submit"] {
            background-color: #ffc107;
            color: #000;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #e0a800;
        }

        .error {
            color: #ff6b6b;
            margin-top: 10px;
        }

        .success {
            color: #51cf66;
            margin-top: 10px;
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
        <h1>Tambah Raport</h1>
        <?php if(isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
<form action="" method="post" enctype="multipart/form-data">
            <h2>Data Siswa</h2>
            <label for="foto">Foto Siswa:</label>
            <input type="file" id="foto" name="foto" accept="image/*">

            <label for="nama">Nama Peserta Didik:</label>
            <input type="text" id="nama" name="nama" required>
                        <label for="nis">Nomor Induk Siswa:</label>
            <input type="text" id="nis" name="nis" required>

            <label for="tempat_lahir">Tempat Lahir:</label>
            <input type="text" id="tempat_lahir" name="tempat_lahir" required>

            <label for="tanggal_lahir">Tanggal Lahir:</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" required>

            <label for="jenis_kelamin">Jenis Kelamin:</label>
            <select id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="">Pilih</option>
                <option value="Laki-laki">Laki-laki</option>
                <option value="Perempuan">Perempuan</option>
            </select>

            <label for="agama">Agama:</label>
            <input type="text" id="agama" name="agama" required>

            <label for="status_keluarga">Status dalam Keluarga:</label>
            <input type="text" id="status_keluarga" name="status_keluarga" required>

            <label for="anak_ke">Anak ke-:</label>
            <input type="number" id="anak_ke" name="anak_ke" required>

            <label for="alamat">Alamat Peserta Didik:</label>
            <textarea id="alamat" name="alamat" required></textarea>

            <label for="nomor_telepon">Nomor Telepon:</label>
            <input type="tel" id="nomor_telepon" name="nomor_telepon" required>

            <label for="asal_sekolah">Asal Sekolah:</label>
            <input type="text" id="asal_sekolah" name="asal_sekolah" required>

            <label for="diterima_di_kelas">Diterima di Kelas:</label>
            <input type="text" id="diterima_di_kelas" name="diterima_di_kelas" required>

            <label for="tanggal_diterima">Tanggal Diterima:</label>
            <input type="date" id="tanggal_diterima" name="tanggal_diterima" required>

            <h2>Data Orang Tua</h2>
            <label for="nama_ayah">Nama Ayah:</label>
            <input type="text" id="nama_ayah" name="nama_ayah" required>

            <label for="nama_ibu">Nama Ibu:</label>
            <input type="text" id="nama_ibu" name="nama_ibu" required>

            <label for="alamat_ortu">Alamat Orang Tua:</label>
            <textarea id="alamat_ortu" name="alamat_ortu" required></textarea>

            <label for="nomor_telepon_ortu">Nomor Telepon Orang Tua:</label>
            <input type="tel" id="nomor_telepon_ortu" name="nomor_telepon_ortu" required>

            <label for="pekerjaan_ayah">Pekerjaan Ayah:</label>
            <input type="text" id="pekerjaan_ayah" name="pekerjaan_ayah" required>

            <label for="pekerjaan_ibu">Pekerjaan Ibu:</label>
            <input type="text" id="pekerjaan_ibu" name="pekerjaan_ibu" required>

            <h2>Data Wali (Jika Ada)</h2>
            <label for="nama_wali">Nama Wali:</label>
            <input type="text" id="nama_wali" name="nama_wali">

            <label for="alamat_wali">Alamat Wali:</label>
            <textarea id="alamat_wali" name="alamat_wali"></textarea>

            <label for="nomor_telepon_wali">Nomor Telepon Wali:</label>
            <input type="tel" id="nomor_telepon_wali" name="nomor_telepon_wali">

            <label for="pekerjaan_wali">Pekerjaan Wali:</label>
            <input type="text" id="pekerjaan_wali" name="pekerjaan_wali">

            <h2>Nilai Akademik</h2>
            <label for="matematika">Matematika:</label>
            <input type="number" id="matematika" name="matematika" min="0" max="100" required>

            <label for="bahasa_indonesia">Bahasa Indonesia:</label>
            <input type="number" id="bahasa_indonesia" name="bahasa_indonesia" min="0" max="100" required>

            <label for="bahasa_inggris">Bahasa Inggris:</label>
            <input type="number" id="bahasa_inggris" name="bahasa_inggris" min="0" max="100" required>

            <label for="ppkn">PPKN:</label>
            <input type="number" id="ppkn" name="ppkn" min="0" max="100" required>

            <label for="pendidikan_agama">Pendidikan Agama:</label>
            <input type="number" id="pendidikan_agama" name="pendidikan_agama" min="0" max="100" required>

            <label for="pendidikan_jasmani">Pendidikan Jasmani:</label>
            <input type="number" id="pendidikan_jasmani" name="pendidikan_jasmani" min="0" max="100" required>

            <label for="ipas">IPAS:</label>
            <input type="number" id="ipas" name="ipas" min="0" max="100" required>

            <label for="bahasa_sunda">Bahasa Sunda:</label>
            <input type="number" id="bahasa_sunda" name="bahasa_sunda" min="0" max="100" required>

            <label for="seni_musik">Seni Musik:</label>
            <input type="number" id="seni_musik" name="seni_musik" min="0" max="100" required>

            <label for="sejarah">Sejarah:</label>
            <input type="number" id="sejarah" name="sejarah" min="0" max="100" required>

            <label for="bahasa_jepang">Bahasa Jepang:</label>
            <input type="number" id="bahasa_jepang" name="bahasa_jepang" min="0" max="100" required>

            <label for="informatika">Informatika:</label>
            <input type="number" id="informatika" name="informatika" min="0" max="100" required>

            <label for="mata_pelajaran_pilihan">Mata Pelajaran Pilihan:</label>
            <input type="number" id="mata_pelajaran_pilihan" name="mata_pelajaran_pilihan" min="0" max="100" required>

            <label for="pkk">PKK:</label>
            <input type="number" id="pkk" name="pkk" min="0" max="100" required>

            <label for="bimbingan_konseling">Bimbingan Konseling:</label>
            <input type="number" id="bimbingan_konseling" name="bimbingan_konseling" min="0" max="100" required>

            <label for="produktif">Produktif:</label>
            <input type="number" id="produktif" name="produktif" min="0" max="100" required>

            <h2>Informasi Tambahan</h2>
            <label for="fase">Fase:</label>
            <input type="text" id="fase" name="fase" required>

            <label for="semester">Semester:</label>
            <select id="semester" name="semester" required>
                <option value="">Pilih</option>
                <option value="Ganjil">Ganjil</option>
                <option value="Genap">Genap</option>
            </select>

            <label for="tahun_pelajaran">Tahun Pelajaran:</label>
            <input type="text" id="tahun_pelajaran" name="tahun_pelajaran" required>

            <h2>Ekstrakurikuler</h2>
            <div id="ekstrakurikuler-container">
                <div class="ekstrakurikuler-item">
                    <label for="ekstrakurikuler1">Ekstrakurikuler 1:</label>
                    <input type="text" id="ekstrakurikuler1" name="ekstrakurikuler[]" placeholder="Nama Ekstrakurikuler">
                    <select name="nilai_ekstrakurikuler[]">
                        <option value="">Pilih Nilai</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>
            <button type="button" id="tambah-ekstrakurikuler">Tambah Ekstrakurikuler</button>

            <h2>Ketidakhadiran</h2>
            <label for="sakit">Sakit (hari):</label>
            <input type="number" id="sakit" name="sakit" min="0" required>

            <label for="izin">Izin (hari):</label>
            <input type="number" id="izin" name="izin" min="0" required>

            <label for="tanpa_keterangan">Tanpa Keterangan (hari):</label>
            <input type="number" id="tanpa_keterangan" name="tanpa_keterangan" min="0" required>

            <h2>Laporan Project Penguatan Profil Pelajar Pancasila</h2>
            <label for="kemampuan_kolaborasi">Kemampuan Kolaborasi:</label>
            <select id="kemampuan_kolaborasi" name="kemampuan_kolaborasi" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
                <option value="Cukup">Cukup</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>

            <label for="bernalar_kritis">Bernalar Kritis:</label>
            <select id="bernalar_kritis" name="bernalar_kritis" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
                <option value="Cukup">Cukup</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>

            <label for="kreativitas">Kreativitas:</label>
            <select id="kreativitas" name="kreativitas" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
                <option value="Cukup">Cukup</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>

            <label for="kemandirian">Kemandirian:</label>
            <select id="kemandirian" name="kemandirian" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
                <option value="Cukup">Cukup</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>

            <h2>Perkembangan Karakter</h2>
            <label for="beriman_bertakwa">Beriman dan Bertakwa:</label>
            <select id="beriman_bertakwa" name="beriman_bertakwa" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
                <option value="Cukup">Cukup</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>

            <label for="berkebinekaan_global">Berkebinekaan Global:</label>
            <select id="berkebinekaan_global" name="berkebinekaan_global" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
                <option value="Cukup">Cukup</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>

            <label for="bernalar_kritis_karakter">Bernalar Kritis:</label>
            <select id="bernalar_kritis_karakter" name="bernalar_kritis_karakter" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik">Sangat Baik</option>
                <option value="Baik">Baik</option>
                <option value="Cukup">Cukup</option>
                <option value="Perlu Perbaikan">Perlu Perbaikan</option>
            </select>

            <label for="catatan_proses">Catatan Proses:</label>
            <textarea id="catatan_proses" name="catatan_proses" rows="4"></textarea>

            <input type="submit" value="Tambah Raport">
        </form>
        <a href="guru_dashboard.php" class="button">Kembali ke Dashboard</a>
    </div>
    
    <script>
        document.getElementById('tambah-ekstrakurikuler').addEventListener('click', function() {
            var container = document.getElementById('ekstrakurikuler-container');
            var newItem = document.createElement('div');
            newItem.className = 'ekstrakurikuler-item';
            var index = container.children.length + 1;
            newItem.innerHTML = `
                <label for="ekstrakurikuler${index}">Ekstrakurikuler ${index}:</label>
                <input type="text" id="ekstrakurikuler${index}" name="ekstrakurikuler[]" placeholder="Nama Ekstrakurikuler">
                <select name="nilai_ekstrakurikuler[]">
                    <option value="">Pilih Nilai</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            `;
            container.appendChild(newItem);
        });
    </script>

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


<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'guru' && $_SESSION['role'] != 'admin')) {
    header("Location: login.php");
    exit();
}

$siswa_id = isset($_GET['siswa_id']) ? $_GET['siswa_id'] : null;

if (!$siswa_id) {
    die("ID siswa tidak diberikan.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $pdo->beginTransaction();

        // Update siswa data
        $stmt = $pdo->prepare("UPDATE siswa SET nama = ?, nis = ?, tempat_lahir = ?, tanggal_lahir = ?, jenis_kelamin = ?, agama = ?, status_keluarga = ?, anak_ke = ?, alamat = ?, nomor_telepon = ?, asal_sekolah = ?, diterima_di_kelas = ?, tanggal_diterima = ? WHERE id = ?");
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
            $_POST['tanggal_diterima'],
            $siswa_id
        ]);

        // Update orang tua data
        $stmt = $pdo->prepare("UPDATE orang_tua SET nama_ayah = ?, nama_ibu = ?, alamat = ?, nomor_telepon = ?, pekerjaan_ayah = ?, pekerjaan_ibu = ? WHERE siswa_id = ?");
        $stmt->execute([
            $_POST['nama_ayah'],
            $_POST['nama_ibu'],
            $_POST['alamat_ortu'],
            $_POST['nomor_telepon_ortu'],
            $_POST['pekerjaan_ayah'],
            $_POST['pekerjaan_ibu'],
            $siswa_id
        ]);

        // Update or insert wali data
        $stmt = $pdo->prepare("SELECT id FROM wali WHERE siswa_id = ?");
        $stmt->execute([$siswa_id]);
        $wali = $stmt->fetch();

        if ($wali) {
            $stmt = $pdo->prepare("UPDATE wali SET nama = ?, alamat = ?, nomor_telepon = ?, pekerjaan = ? WHERE siswa_id = ?");
        } else {
            $stmt = $pdo->prepare("INSERT INTO wali (nama, alamat, nomor_telepon, pekerjaan, siswa_id) VALUES (?, ?, ?, ?, ?)");
        }
        $stmt->execute([
            $_POST['nama_wali'],
            $_POST['alamat_wali'],
            $_POST['nomor_telepon_wali'],
            $_POST['pekerjaan_wali'],
            $siswa_id
        ]);

        // Update nilai akademik
        $stmt = $pdo->prepare("UPDATE nilai_akademik SET matematika = ?, bahasa_indonesia = ?, bahasa_inggris = ?, ppkn = ?, pendidikan_agama = ?, pendidikan_jasmani = ?, ipas = ?, bahasa_sunda = ?, seni_musik = ?, sejarah = ?, bahasa_jepang = ?, informatika = ?, mata_pelajaran_pilihan = ?, pkk = ?, bimbingan_konseling = ?, produktif = ?, fase = ?, semester = ?, tahun_pelajaran = ? WHERE siswa_id = ?");
        $stmt->execute([
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
            $_POST['tahun_pelajaran'],
            $siswa_id
        ]);

        // Update ekstrakurikuler
        $stmt = $pdo->prepare("DELETE FROM ekstrakurikuler WHERE siswa_id = ?");
        $stmt->execute([$siswa_id]);

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

        // Update ketidakhadiran
        $stmt = $pdo->prepare("UPDATE ketidakhadiran SET sakit = ?, izin = ?, tanpa_keterangan = ? WHERE siswa_id = ?");
        $stmt->execute([
            $_POST['sakit'],
            $_POST['izin'],
            $_POST['tanpa_keterangan'],
            $siswa_id
        ]);

        // Update laporan project
        $stmt = $pdo->prepare("UPDATE laporan_project SET kemampuan_kolaborasi = ?, bernalar_kritis = ?, kreativitas = ?, kemandirian = ? WHERE siswa_id = ?");
        $stmt->execute([
            $_POST['kemampuan_kolaborasi'],
            $_POST['bernalar_kritis'],
            $_POST['kreativitas'],
            $_POST['kemandirian'],
            $siswa_id
        ]);

        // Update perkembangan karakter
        $stmt = $pdo->prepare("UPDATE perkembangan_karakter SET beriman_bertakwa = ?, berkebinekaan_global = ?, bernalar_kritis = ?, catatan_proses = ? WHERE siswa_id = ?");
        $stmt->execute([
            $_POST['beriman_bertakwa'],
            $_POST['berkebinekaan_global'],
            $_POST['bernalar_kritis_karakter'],
            $_POST['catatan_proses'],
            $siswa_id
        ]);

        $pdo->commit();
        $success = "Raport berhasil diperbarui";
    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch existing data
$stmt = $pdo->prepare("
    SELECT s.*, na.*, e.nama AS nama_ekstrakurikuler, e.nilai AS nilai_ekstrakurikuler, 
           k.sakit, k.izin, k.tanpa_keterangan, 
           lp.kemampuan_kolaborasi, lp.bernalar_kritis AS lp_bernalar_kritis, lp.kreativitas, lp.kemandirian,
           pk.beriman_bertakwa, pk.berkebinekaan_global, pk.bernalar_kritis AS pk_bernalar_kritis, pk.catatan_proses,
           o.nama_ayah, o.pekerjaan_ayah, o.nama_ibu, o.pekerjaan_ibu, o.alamat AS alamat_ortu, o.nomor_telepon AS nomor_telepon_ortu,
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
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($data)) {
    die("Data siswa tidak ditemukan.");
}

$siswa = $data[0];
$ekstrakurikuler = array_filter($data, function($row) {
    return !empty($row['nama_ekstrakurikuler']);
});
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Raport</title>
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
        input[type="date"],
        input[type="tel"],
        select,
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
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        #ekstrakurikuler-container {
            display: grid;
            gap: 15px;
        }

        .ekstrakurikuler-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #333;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .ekstrakurikuler-item:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }

        .ekstrakurikuler-item input[type="text"],
        .ekstrakurikuler-item select {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 14px;
        }

        .ekstrakurikuler-item select {
            background-color: gray;
        }

        .remove-ekstrakurikuler {
            background-color: #ff6b6b;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .remove-ekstrakurikuler:hover {
            background-color: #ff4757;
        }

        #tambah-ekstrakurikuler {
            background-color: #ffc107;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        #tambah-ekstrakurikuler:hover {
            background-color: #45a049;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .form-section {
                padding: 15px;
            }

            .ekstrakurikuler-item {
                grid-template-columns: 1fr;
            }
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
            margin-bottom: 15px;
            font-size: 24px;
            color: #fff;
        }

        .footer-nav a {
            color: #ffc107;
            margin: 0 10px;
            text-decoration: none;
            color: #fff;
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
        <h1>Edit Raport</h1>
        <?php if(isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <?php if(isset($success)): ?>
            <p class="success"><?php echo $success; ?></p>
        <?php endif; ?>
        <form action="" method="post">
            <h2>Data Siswa</h2>
            <label for="nama">Nama Peserta Didik:</label>
            <input type="text" id="nama" name="nama" value="<?php echo htmlspecialchars($siswa['nama']); ?>" required>

            <label for="nis">Nomor Induk Siswa:</label>
            <input type="text" id="nis" name="nis" value="<?php echo htmlspecialchars($siswa['nis']); ?>" required>

            <label for="tempat_lahir">Tempat Lahir:</label>
            <input type="text" id="tempat_lahir" name="tempat_lahir" value="<?php echo htmlspecialchars($siswa['tempat_lahir']); ?>" required>

            <label for="tanggal_lahir">Tanggal Lahir:</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($siswa['tanggal_lahir']); ?>" required>

            <label for="jenis_kelamin">Jenis Kelamin:</label>
            <select id="jenis_kelamin" name="jenis_kelamin" required>
                <option value="Laki-laki" <?php echo $siswa['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="Perempuan" <?php echo $siswa['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
            </select>

            <label for="agama">Agama:</label>
            <input type="text" id="agama" name="agama" value="<?php echo htmlspecialchars($siswa['agama']); ?>" required>

            <label for="status_keluarga">Status dalam Keluarga:</label>
            <input type="text" id="status_keluarga" name="status_keluarga" value="<?php echo htmlspecialchars($siswa['status_keluarga']); ?>" required>

            <label for="anak_ke">Anak ke-:</label>
            <input type="number" id="anak_ke" name="anak_ke" value="<?php echo htmlspecialchars($siswa['anak_ke']); ?>" required>

            <label for="alamat">Alamat Peserta Didik:</label>
            <textarea id="alamat" name="alamat" required><?php echo htmlspecialchars($siswa['alamat']); ?></textarea>

            <label for="nomor_telepon">Nomor Telepon:</label>
            <input type="tel" id="nomor_telepon" name="nomor_telepon" value="<?php echo htmlspecialchars($siswa['nomor_telepon']); ?>" required>

            <label for="asal_sekolah">Asal Sekolah:</label>
            <input type="text" id="asal_sekolah" name="asal_sekolah" value="<?php echo htmlspecialchars($siswa['asal_sekolah']); ?>" required>

            <label for="diterima_di_kelas">Diterima di Kelas:</label>
            <input type="text" id="diterima_di_kelas" name="diterima_di_kelas" value="<?php echo htmlspecialchars($siswa['diterima_di_kelas']); ?>" required>

            <label for="tanggal_diterima">Tanggal Diterima:</label>
            <input type="date" id="tanggal_diterima" name="tanggal_diterima" value="<?php echo htmlspecialchars($siswa['tanggal_diterima']); ?>" required>

            <h2>Data Orang Tua</h2>
            <label for="nama_ayah">Nama Ayah:</label>
            <input type="text" id="nama_ayah" name="nama_ayah" value="<?php echo htmlspecialchars($siswa['nama_ayah']); ?>" required>

            <label for="nama_ibu">Nama Ibu:</label>
            <input type="text" id="nama_ibu" name="nama_ibu" value="<?php echo htmlspecialchars($siswa['nama_ibu']); ?>" required>

            <label for="alamat_ortu">Alamat Orang Tua:</label>
            <textarea id="alamat_ortu" name="alamat_ortu" required><?php echo htmlspecialchars($siswa['alamat_ortu']); ?></textarea>

            <label for="nomor_telepon_ortu">Nomor Telepon Orang Tua:</label>
            <input type="tel" id="nomor_telepon_ortu" name="nomor_telepon_ortu" value="<?php echo htmlspecialchars($siswa['nomor_telepon_ortu']); ?>" required>

            <label for="pekerjaan_ayah">Pekerjaan Ayah:</label>
            <input type="text" id="pekerjaan_ayah" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($siswa['pekerjaan_ayah']); ?>" required>

            <label for="pekerjaan_ibu">Pekerjaan Ibu:</label>
            <input type="text" id="pekerjaan_ibu" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($siswa['pekerjaan_ibu']); ?>" required>

            <h2>Data Wali (Jika Ada)</h2>
            <label for="nama_wali">Nama Wali:</label>
            <input type="text" id="nama_wali" name="nama_wali" value="<?php echo htmlspecialchars($siswa['nama_wali']); ?>">

            <label for="alamat_wali">Alamat Wali:</label>
            <textarea id="alamat_wali" name="alamat_wali"><?php echo htmlspecialchars($siswa['alamat_wali']); ?></textarea>

            <label for="nomor_telepon_wali">Nomor Telepon Wali:</label>
            <input type="tel" id="nomor_telepon_wali" name="nomor_telepon_wali" value="<?php echo htmlspecialchars($siswa['nomor_telepon_wali']); ?>">

            <label for="pekerjaan_wali">Pekerjaan Wali:</label>
            <input type="text" id="pekerjaan_wali" name="pekerjaan_wali" value="<?php echo htmlspecialchars($siswa['pekerjaan_wali']); ?>">

            <h2>Nilai Akademik</h2>
            <label for="matematika">Matematika:</label>
            <input type="number" id="matematika" name="matematika" min="0" max="100" value="<?php echo htmlspecialchars($siswa['matematika']); ?>" required>

            <label for="bahasa_indonesia">Bahasa Indonesia:</label>
            <input type="number" id="bahasa_indonesia" name="bahasa_indonesia" min="0" max="100" value="<?php echo htmlspecialchars($siswa['bahasa_indonesia']); ?>" required>

            <label for="bahasa_inggris">Bahasa Inggris:</label>
            <input type="number" id="bahasa_inggris" name="bahasa_inggris" min="0" max="100" value="<?php echo htmlspecialchars($siswa['bahasa_inggris']); ?>" required>

            <label for="ppkn">PPKN:</label>
            <input type="number" id="ppkn" name="ppkn" min="0" max="100" value="<?php echo htmlspecialchars($siswa['ppkn']); ?>" required>

            <label for="pendidikan_agama">Pendidikan Agama:</label>
            <input type="number" id="pendidikan_agama" name="pendidikan_agama" min="0" max="100" value="<?php echo htmlspecialchars($siswa['pendidikan_agama']); ?>" required>

            <label for="pendidikan_jasmani">Pendidikan Jasmani:</label>
            <input type="number" id="pendidikan_jasmani" name="pendidikan_jasmani" min="0" max="100" value="<?php echo htmlspecialchars($siswa['pendidikan_jasmani']); ?>" required>

            <label for="ipas">IPAS:</label>
            <input type="number" id="ipas" name="ipas" min="0" max="100" value="<?php echo htmlspecialchars($siswa['ipas']); ?>" required>

            <label for="bahasa_sunda">Bahasa Sunda:</label>
            <input type="number" id="bahasa_sunda" name="bahasa_sunda" min="0" max="100" value="<?php echo htmlspecialchars($siswa['bahasa_sunda']); ?>" required>

            <label for="seni_musik">Seni Musik:</label>
            <input type="number" id="seni_musik" name="seni_musik" min="0" max="100" value="<?php echo htmlspecialchars($siswa['seni_musik']); ?>" required>

            <label for="sejarah">Sejarah:</label>
            <input type="number" id="sejarah" name="sejarah" min="0" max="100" value="<?php echo htmlspecialchars($siswa['sejarah']); ?>" required>

            <label for="bahasa_jepang">Bahasa Jepang:</label>
            <input type="number" id="bahasa_jepang" name="bahasa_jepang" min="0" max="100" value="<?php echo htmlspecialchars($siswa['bahasa_jepang']); ?>" required>

            <label for="informatika">Informatika:</label>
            <input type="number" id="informatika" name="informatika" min="0" max="100" value="<?php echo htmlspecialchars($siswa['informatika']); ?>" required>

            <label for="mata_pelajaran_pilihan">Mata Pelajaran Pilihan:</label>
            <input type="number" id="mata_pelajaran_pilihan" name="mata_pelajaran_pilihan" min="0" max="100" value="<?php echo htmlspecialchars($siswa['mata_pelajaran_pilihan']); ?>" required>

            <label for="pkk">PKK:</label>
            <input type="number" id="pkk" name="pkk" min="0" max="100" value="<?php echo htmlspecialchars($siswa['pkk']); ?>" required>

            <label for="bimbingan_konseling">Bimbingan Konseling:</label>
            <input type="number" id="bimbingan_konseling" name="bimbingan_konseling" min="0" max="100" value="<?php echo htmlspecialchars($siswa['bimbingan_konseling']); ?>" required>

            <label for="produktif">Produktif:</label>
            <input type="number" id="produktif" name="produktif" min="0" max="100" value="<?php echo htmlspecialchars($siswa['produktif']); ?>" required>

            <h2>Informasi Tambahan</h2>
            <label for="fase">Fase:</label>
            <input type="text" id="fase" name="fase" value="<?php echo htmlspecialchars($siswa['fase']); ?>" required>

            <label for="semester">Semester:</label>
            <select id="semester" name="semester" required>
                <option value="Ganjil" <?php echo $siswa['semester'] == 'Ganjil' ? 'selected' : ''; ?>>Ganjil</option>
                <option value="Genap" <?php echo $siswa['semester'] == 'Genap' ? 'selected' : ''; ?>>Genap</option>
            </select>

            <label for="tahun_pelajaran">Tahun Pelajaran:</label>
            <input type="text" id="tahun_pelajaran" name="tahun_pelajaran" value="<?php echo htmlspecialchars($siswa['tahun_pelajaran']); ?>" required>

            <div class="form-section">
                <h2><i class="fas fa-trophy"></i> Ekstrakurikuler</h2>
                <div id="ekstrakurikuler-container">
                    <?php foreach ($ekstrakurikuler as $index => $ekskul): ?>
                        <div class="ekstrakurikuler-item">
                            <input type="text" name="ekstrakurikuler[]" value="<?php echo htmlspecialchars($ekskul['nama_ekstrakurikuler']); ?>" placeholder="Nama Ekstrakurikuler">
                            <select name="nilai_ekstrakurikuler[]">
                                <option value="">Pilih Nilai</option>
                                <option value="A" <?php echo $ekskul['nilai_ekstrakurikuler'] == 'A' ? 'selected' : ''; ?>>A</option>
                                <option value="B" <?php echo $ekskul['nilai_ekstrakurikuler'] == 'B' ? 'selected' : ''; ?>>B</option>
                                <option value="C" <?php echo $ekskul['nilai_ekstrakurikuler'] == 'C' ? 'selected' : ''; ?>>C</option>
                                <option value="D" <?php echo $ekskul['nilai_ekstrakurikuler'] == 'D' ? 'selected' : ''; ?>>D</option>
                            </select>
                            <button type="button" class="remove-ekstrakurikuler"><i class="fas fa-trash-alt"></i></button>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" id="tambah-ekstrakurikuler"><i class="fas fa-plus"></i> Tambah Ekstrakurikuler</button>
            </div>

            <h2>Ketidakhadiran</h2>
            <label for="sakit">Sakit (hari):</label>
            <input type="number" id="sakit" name="sakit" min="0" value="<?php echo htmlspecialchars($siswa['sakit']); ?>" required>

            <label for="izin">Izin (hari):</label>
            <input type="number" id="izin" name="izin" min="0" value="<?php echo htmlspecialchars($siswa['izin']); ?>" required>

            <label for="tanpa_keterangan">Tanpa Keterangan (hari):</label>
            <input type="number" id="tanpa_keterangan" name="tanpa_keterangan" min="0" value="<?php echo htmlspecialchars($siswa['tanpa_keterangan']); ?>" required>

            <h2>Laporan Project Penguatan Profil Pelajar Pancasila</h2>
            <label for="kemampuan_kolaborasi">Kemampuan Kolaborasi:</label>
            <select id="kemampuan_kolaborasi" name="kemampuan_kolaborasi" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik" <?php echo $siswa['kemampuan_kolaborasi'] == 'Sangat Baik' ? 'selected' : ''; ?>>Sangat Baik</option>
                <option value="Baik" <?php echo $siswa['kemampuan_kolaborasi'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="Cukup" <?php echo $siswa['kemampuan_kolaborasi'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                <option value="Perlu Perbaikan" <?php echo $siswa['kemampuan_kolaborasi'] == 'Perlu Perbaikan' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
            </select>

            <label for="bernalar_kritis">Bernalar Kritis:</label>
            <select id="bernalar_kritis" name="bernalar_kritis" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik" <?php echo $siswa['lp_bernalar_kritis'] == 'Sangat Baik' ? 'selected' : ''; ?>>Sangat Baik</option>
                <option value="Baik" <?php echo $siswa['lp_bernalar_kritis'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="Cukup" <?php echo $siswa['lp_bernalar_kritis'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                <option value="Perlu Perbaikan" <?php echo $siswa['lp_bernalar_kritis'] == 'Perlu Perbaikan' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
            </select>

            <label for="kreativitas">Kreativitas:</label>
            <select id="kreativitas" name="kreativitas" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik" <?php echo $siswa['kreativitas'] == 'Sangat Baik' ? 'selected' : ''; ?>>Sangat Baik</option>
                <option value="Baik" <?php echo $siswa['kreativitas'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="Cukup" <?php echo $siswa['kreativitas'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                <option value="Perlu Perbaikan" <?php echo $siswa['kreativitas'] == 'Perlu Perbaikan' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
            </select>

            <label for="kemandirian">Kemandirian:</label>
            <select id="kemandirian" name="kemandirian" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik" <?php echo $siswa['kemandirian'] == 'Sangat Baik' ? 'selected' : ''; ?>>Sangat Baik</option>
                <option value="Baik" <?php echo $siswa['kemandirian'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="Cukup" <?php echo $siswa['kemandirian'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                <option value="Perlu Perbaikan" <?php echo $siswa['kemandirian'] == 'Perlu Perbaikan' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
            </select>

            <h2>Perkembangan Karakter</h2>
            <label for="beriman_bertakwa">Beriman dan Bertakwa:</label>
            <select id="beriman_bertakwa" name="beriman_bertakwa" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik" <?php echo $siswa['beriman_bertakwa'] == 'Sangat Baik' ? 'selected' : ''; ?>>Sangat Baik</option>
                <option value="Baik" <?php echo $siswa['beriman_bertakwa'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="Cukup" <?php echo $siswa['beriman_bertakwa'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                <option value="Perlu Perbaikan" <?php echo $siswa['beriman_bertakwa'] == 'Perlu Perbaikan' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
            </select>

            <label for="berkebinekaan_global">Berkebinekaan Global:</label>
            <select id="berkebinekaan_global" name="berkebinekaan_global" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik" <?php echo $siswa['berkebinekaan_global'] == 'Sangat Baik' ? 'selected' : ''; ?>>Sangat Baik</option>
                <option value="Baik" <?php echo $siswa['berkebinekaan_global'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="Cukup" <?php echo $siswa['berkebinekaan_global'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                <option value="Perlu Perbaikan" <?php echo $siswa['berkebinekaan_global'] == 'Perlu Perbaikan' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
            </select>

            <label for="bernalar_kritis_karakter">Bernalar Kritis:</label>
            <select id="bernalar_kritis_karakter" name="bernalar_kritis_karakter" required>
                <option value="">Pilih</option>
                <option value="Sangat Baik" <?php echo $siswa['pk_bernalar_kritis'] == 'Sangat Baik' ? 'selected' : ''; ?>>Sangat Baik</option>
                <option value="Baik" <?php echo $siswa['pk_bernalar_kritis'] == 'Baik' ? 'selected' : ''; ?>>Baik</option>
                <option value="Cukup" <?php echo $siswa['pk_bernalar_kritis'] == 'Cukup' ? 'selected' : ''; ?>>Cukup</option>
                <option value="Perlu Perbaikan" <?php echo $siswa['pk_bernalar_kritis'] == 'Perlu Perbaikan' ? 'selected' : ''; ?>>Perlu Perbaikan</option>
            </select>

            <label for="catatan_proses">Catatan Proses:</label>
            <textarea id="catatan_proses" name="catatan_proses" rows="4"><?php echo htmlspecialchars($siswa['catatan_proses']); ?></textarea>

            <input type="submit" value="Simpan Perubahan">
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
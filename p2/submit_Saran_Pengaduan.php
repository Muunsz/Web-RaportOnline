<?php
require_once 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = $_POST['nama'] ?? '';
    $email = $_POST['email'] ?? '';
    $pesan = $_POST['pesan'] ?? '';

    if (empty($nama) || empty($email) || empty($pesan)) {
        echo json_encode(['success' => false, 'message' => 'Semua field harus diisi.']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO saran_pengaduan (nama, email, pesan) VALUES (?, ?, ?)");
        $result = $stmt->execute([$nama, $email, $pesan]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Terima kasih atas Saran Anda!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menyimpan Saran Silakan coba lagi.']);
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan. Silakan coba lagi nanti.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Metode request tidak valid.']);
}


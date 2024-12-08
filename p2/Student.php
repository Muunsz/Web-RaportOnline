<?php

class Student {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getAllStudents() {
        $stmt = $this->pdo->query("SELECT * FROM siswa");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchStudents($keyword) {
        $sql = "SELECT * FROM siswa WHERE nama LIKE :keyword1 OR nis LIKE :keyword2 OR diterima_di_kelas LIKE :keyword3";
        $stmt = $this->pdo->prepare($sql);
        $param = "%{$keyword}%";
        $stmt->bindParam(':keyword1', $param, PDO::PARAM_STR);
        $stmt->bindParam(':keyword2', $param, PDO::PARAM_STR);
        $stmt->bindParam(':keyword3', $param, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
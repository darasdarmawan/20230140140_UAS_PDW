<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit;
}

$mahasiswa_id = $_SESSION['user_id'];

// Validasi data POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_laporan'], $_POST['modul_id'], $_POST['praktikum_id'])) {
    $modul_id = (int) $_POST['modul_id'];
    $praktikum_id = (int) $_POST['praktikum_id']; // Variabel ini sudah ada, tinggal dipakai
    $upload_dir = __DIR__ . '/../uploads/laporan/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $file_name = basename($_FILES['file_laporan']['name']);
    $file_tmp = $_FILES['file_laporan']['tmp_name'];
    $file_size = $_FILES['file_laporan']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_ext = ['pdf', 'doc', 'docx', 'zip', 'rar'];

    if (!in_array($file_ext, $allowed_ext)) {
        die("Format file tidak diperbolehkan.");
    }

    if ($file_size > 10 * 1024 * 1024) {
        die("Ukuran file terlalu besar. Maksimum 10MB.");
    }

    $new_file_name = 'laporan_' . $mahasiswa_id . '_mod' . $modul_id . '_' . time() . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;

    if (move_uploaded_file($file_tmp, $destination)) {
        // ======================= BAGIAN YANG DIPERBAIKI =======================
        // Tambahkan 'praktikum_id' ke dalam query INSERT
        $stmt = $conn->prepare("INSERT INTO laporan_praktikum (praktikum_id, modul_id, mahasiswa_id, file_laporan, status) VALUES (?, ?, ?, ?, 'dikumpulkan')");
        // Tambahkan '$praktikum_id' ke dalam bind_param dengan tipe 'i' (integer)
        $stmt->bind_param("iiis", $praktikum_id, $modul_id, $mahasiswa_id, $new_file_name);
        // ======================================================================

        if ($stmt->execute()) {
            header("Location: detail_praktikum.php?upload=sukses");
            exit;
        } else {
            // Tampilkan error yang lebih spesifik untuk debugging
            echo "Gagal menyimpan ke database: " . $stmt->error;
        }
        $stmt->close();
    } else {
        echo "Gagal mengunggah file.";
    }
} else {
    echo "Permintaan tidak valid.";
}
?>
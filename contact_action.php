<?php
// ayelectronic_store/contact_action.php

session_start();
include_once 'config/database.php';

// Hanya proses jika metode request adalah POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validasi sederhana
    if (empty($name) || empty($email) || empty($subject) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['notification'] = [
            'message' => 'Harap isi semua kolom dengan benar.',
            'type' => 'danger'
        ];
        header("Location: contact.php");
        exit();
    }
    
    // Simpan ke database menggunakan prepared statement
    $conn = new mysqli($host, $user, $pass, $db_name);
    if ($conn->connect_error) {
        die("Koneksi Gagal: " . $conn->connect_error);
    }

    $sql = "INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        $_SESSION['notification'] = [
            'message' => 'Pesan Anda telah berhasil terkirim. Terima kasih!',
            'type' => 'success'
        ];
    } else {
        $_SESSION['notification'] = [
            'message' => 'Terjadi kesalahan. Gagal mengirim pesan.',
            'type' => 'danger'
        ];
    }
    
    $stmt->close();
    $conn->close();

}

// Redirect kembali ke halaman kontak
header("Location: contact.php");
exit();
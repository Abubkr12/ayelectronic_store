<?php
// ayelectronic_store/logout_customer.php

session_start(); // Mulai sesi untuk bisa mengaksesnya

// 1. Siapkan pesan notifikasi sebelum sesi dihancurkan
$_SESSION['notification'] = [
    'message' => 'Anda telah berhasil logout.',
    'type' => 'info' // Anda bisa ganti ke 'success' jika mau
];

// 2. Hapus semua data sesi spesifik pelanggan
unset($_SESSION['customer_id']);
unset($_SESSION['customer_name']);

// CATATAN: Kita tidak menggunakan session_destroy() agar jika admin dan customer
// login di browser yang sama (jarang terjadi), sesi admin tidak ikut terhapus.
// Ini lebih aman.

// 3. Arahkan kembali ke halaman utama
header("Location: index.php");
exit();

?>
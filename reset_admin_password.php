<?php
// File sementara untuk mereset password admin.
// HAPUS FILE INI SETELAH SELESAI DIGUNAKAN!

// 1. Sertakan konfigurasi database Anda
include_once 'config/database.php';

echo "<h1>Proses Reset Password Admin</h1>";

// 2. Buat koneksi ke database
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) {
    die("<p style='color:red;'>Koneksi Gagal: " . $conn->connect_error . "</p>");
}
echo "<p style='color:green;'>Koneksi database berhasil.</p>";

// 3. Tentukan password baru yang mudah diingat
$new_password = '123456'; // Kita akan set password baru menjadi '123456'
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
echo "<p>Password baru akan di-set menjadi: <strong>" . $new_password . "</strong></p>";

// 4. Siapkan dan jalankan query UPDATE menggunakan prepared statement
$username_to_update = 'admin';
$sql = "UPDATE users SET password = ? WHERE username = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("<p style='color:red;'>Gagal mempersiapkan statement: " . $conn->error . "</p>");
}

// Bind parameter dan eksekusi
$stmt->bind_param("ss", $hashed_password, $username_to_update);

if ($stmt->execute()) {
    echo "<h2 style='color:green;'>BERHASIL! Password untuk username 'admin' telah berhasil direset.</h2>";
} else {
    echo "<h2 style='color:red;'>GAGAL! Tidak bisa mereset password: " . $stmt->error . "</h2>";
}

// 5. Tutup koneksi
$stmt->close();
$conn->close();

echo "<hr>";
echo "<p style='color:blue; font-weight:bold;'>LANGKAH SELANJUTNYA:</p>";
echo "<ol>";
echo "<li>Sekarang coba login kembali di halaman <a href='login.php'>login.php</a> menggunakan username 'admin' dan password '" . $new_password . "'.</li>";
echo "<li>Jika sudah berhasil login, **SEGERA HAPUS file 'reset_admin_password.php' ini dari folder Anda demi keamanan.**</li>";
echo "</ol>";
?>
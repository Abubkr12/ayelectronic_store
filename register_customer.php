<?php
// ayelectronic_store/register_customer.php

session_start();
ob_start();

include_once 'config/database.php';

// Jika pelanggan sudah login, arahkan ke halaman utama
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $nomor_hp = trim($_POST['nomor_hp'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');

    // 1. Validasi Input Sederhana
    if (empty($nama_lengkap) || empty($email) || empty($password) || empty($nomor_hp)) {
        $error_message = 'Nama Lengkap, Email, Password, dan Nomor HP wajib diisi.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Password dan Konfirmasi Password tidak cocok.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid.';
    } else {
        // 2. Cek apakah email sudah terdaftar
        $sql_check_email = "SELECT id FROM customers WHERE email = ?";
        $stmt_check = $conn->prepare($sql_check_email);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $error_message = 'Email ini sudah terdaftar. Silakan gunakan email lain atau login.';
        } else {
            // 3. Hash Password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // 4. Masukkan data ke database
            $sql_insert = "INSERT INTO customers (nama_lengkap, email, password, nomor_hp, alamat) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sssss", $nama_lengkap, $email, $hashed_password, $nomor_hp, $alamat);

            if ($stmt_insert->execute()) {
                // Set notifikasi sukses dan redirect ke halaman login
                $_SESSION['notification'] = [
                    'message' => 'Registrasi berhasil! Silakan login dengan akun Anda.',
                    'type' => 'success'
                ];
                header("Location: customer_login.php");
                exit();
            } else {
                $error_message = 'Terjadi kesalahan pada server. Silakan coba lagi.';
            }
            $stmt_insert->close();
        }
        $stmt_check->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Akun - AYelectronic Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/frontend.css?v=<?php echo time(); ?>">
</head>
<body class="body-auth">  <div class="auth-container">
        <h2>Buat Akun AYelectronic</h2>
        <p class="text-secondary mb-4">Daftar untuk mulai berbelanja gadget futuristik.</p>
        
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="register_customer.php" method="POST">
            <div class="mb-3 text-start">
                <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div class="mb-3 text-start">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3 text-start">
                <label for="nomor_hp" class="form-label">Nomor HP / WhatsApp</label>
                <input type="tel" class="form-control" id="nomor_hp" name="nomor_hp" required>
            </div>
             <div class="mb-3 text-start">
                <label for="alamat" class="form-label">Alamat Lengkap (Opsional)</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="2"></textarea>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3 text-start">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="col-md-6 mb-3 text-start">
                    <label for="confirm_password" class="form-label">Konfirmasi Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
            </div>
            <button type="submit" class="btn btn-auth-action w-100 mt-3">Daftar Sekarang</button>
        </form>

        <div class="mt-4">
            <p class="text-muted">Sudah punya akun? <a href="customer_login.php" class="text-primary-futuristic fw-bold">Login di sini</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
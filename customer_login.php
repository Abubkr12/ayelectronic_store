<?php
// ayelectronic_store/customer_login.php

session_start();
ob_start();

include_once 'config/database.php';

// Jika pelanggan sudah login, arahkan ke halaman utama
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$error_message = '';

// Ambil pesan notifikasi dari sesi (misalnya setelah registrasi)
$notification_message = '';
$notification_type = '';
if (isset($_SESSION['notification'])) {
    $notification_message = $_SESSION['notification']['message'];
    $notification_type = $_SESSION['notification']['type'];
    unset($_SESSION['notification']); // Hapus setelah ditampilkan
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error_message = 'Email dan Password wajib diisi.';
    } else {
        // 1. Ambil data customer berdasarkan email
        $sql = "SELECT id, nama_lengkap, password FROM customers WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $customer = $result->fetch_assoc();

            // 2. Verifikasi password
            if (password_verify($password, $customer['password'])) {
                // 3. Jika berhasil, buat sesi dan redirect
                $_SESSION['customer_id'] = $customer['id'];
                $_SESSION['customer_name'] = $customer['nama_lengkap'];
                
                // Set notifikasi selamat datang
                $_SESSION['notification'] = [
                    'message' => 'Login berhasil! Selamat datang kembali, ' . htmlspecialchars($customer['nama_lengkap']) . '.',
                    'type' => 'success'
                ];

                header("Location: index.php");
                exit();
            } else {
                $error_message = 'Email atau password salah.';
            }
        } else {
            $error_message = 'Email atau password salah.';
        }
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pelanggan - AYelectronic Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/frontend.css?v=<?php echo time(); ?>">
</head>
<body class="body-auth">

    <div class="auth-container">
        <h2>Login Pelanggan</h2>
        <p class="text-secondary mb-4">Masuk untuk melanjutkan belanja Anda.</p>
        
        <?php if ($notification_message): ?>
            <div class="alert alert-<?= $notification_type ?>" role="alert">
                <?= htmlspecialchars($notification_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <form action="customer_login.php" method="POST">
            <div class="mb-3 text-start">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3 text-start">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <a href="#" class="form-text text-muted text-decoration-none">Lupa Password?</a>
            </div>
            
            <button type="submit" class="btn btn-auth-action w-100">Login</button>
        </form>

        <div class="mt-4">
            <p class="text-muted">Belum punya akun? <a href="register_customer.php" class="text-primary-futuristic fw-bold">Daftar di sini</a></p>
        </div>
         <div class="mt-2 text-center">
            <a href="index.php" class="text-muted small">Kembali ke Beranda</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
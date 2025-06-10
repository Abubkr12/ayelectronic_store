<?php
// login.php
session_start();
include_once 'config/database.php';

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; // Password plainteks dari form

    // Ambil data user dari database
    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Verifikasi password yang di-hash
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['foto_profil'] = $user['foto_profil'];

            // Redirect ke halaman admin/dashboard sesuai role
            header("Location: admin/");
            exit();
        } else {
            $error_message = "Username atau password salah.";
        }
    } else {
        $error_message = "Username atau password salah.";
    }
}

// Untuk admin pertama, tambahkan secara manual jika belum ada
// CATATAN: Ini hanya untuk setup awal. Di produksi, jangan biarkan ini aktif terus!
// Hapus atau komen kode ini setelah admin pertama berhasil dibuat.
$sql_check_admin = "SELECT * FROM users WHERE username = 'admin'";
$result_check_admin = $conn->query($sql_check_admin);
if ($result_check_admin->num_rows == 0) {
    $hashed_password = password_hash('123456', PASSWORD_DEFAULT);
    $sql_insert_admin = "INSERT INTO users (nama_lengkap, username, password, role) VALUES ('Administrator', 'admin', '$hashed_password', 'admin')";
    if ($conn->query($sql_insert_admin) === TRUE) {
        // echo "Admin pertama berhasil dibuat!"; // Untuk debugging
    } else {
        // echo "Error membuat admin: " . $conn->error; // Untuk debugging
    }
}
// END CATATAN

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - AYelectronic Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #FFFBDE; /* Warna pastel */
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .login-container h2 {
            color: #096B68; /* Warna teal gelap */
            margin-bottom: 30px;
            font-weight: 600;
        }
        .form-control:focus {
            border-color: #90D1CA; /* Warna tosca muda */
            box-shadow: 0 0 0 0.25rem rgba(144, 209, 202, 0.25);
        }
        .btn-primary {
            background-color: #129990; /* Warna tosca sedang */
            border-color: #129990;
            font-weight: 500;
            padding: 10px 20px;
            border-radius: 8px;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #096B68; /* Warna teal gelap */
            border-color: #096B68;
        }
        .alert {
            margin-top: 20px;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login AYelectronic Store</h2>
        <?php if ($error_message): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error_message ?>
            </div>
        <?php endif; ?>
        <form action="" method="POST">
            <div class="mb-3">
                <input type="text" class="form-control" name="username" placeholder="Username" required>
            </div>
            <div class="mb-4">
                <input type="password" class="form-control" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
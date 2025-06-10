<?php
// ayelectronic_store/order_success.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

// Ambil kode pesanan dari URL
$kode_pesanan = $_GET['kode'] ?? '';
if (empty($kode_pesanan)) {
    header("Location: index.php");
    exit();
}

// Ambil detail pesanan dari database berdasarkan kodenya
$stmt = $conn->prepare("SELECT * FROM orders WHERE kode_pesanan = ?");
$stmt->bind_param("s", $kode_pesanan);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: index.php");
    exit();
}

$body_class = 'bg-light';
include_once 'includes/header.php'; // Kita tetap butuh header untuk tampilan
?>
<title>Pesanan Berhasil - AYelectronic Store</title>
<meta http-equiv="refresh" content="5;url=https://wa.me/6289667915756?text=Halo AYelectronic Store, Saya mau konfirmasi pesanan dengan KODE: *<?= htmlspecialchars($order['kode_pesanan']) ?>*">

<main style="padding-top: 100px;">
    <div class="container my-5 text-center">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-5">
                        <i class="bi bi-check-circle-fill text-success" style="font-size: 5rem;"></i>
                        <h1 class="mt-3">Pesanan Anda Berhasil Dibuat!</h1>
                        <p class="lead">Terima kasih telah berbelanja di AYelectronic Store.</p>
                        <hr>
                        <p class="text-muted">Kode Pesanan Anda adalah:</p>
                        <h3 class="badge bg-secondary fs-5 mb-4"><?= htmlspecialchars($order['kode_pesanan']) ?></h3>
                        <p>Total pembayaran Anda adalah sebesar **Rp<?= number_format($order['total_harga'], 0, ',', '.') ?>**.</p>
                        <div class="alert alert-info">
                            <p class="mb-0">Anda akan diarahkan ke WhatsApp untuk konfirmasi dalam <strong id="countdown">5</strong> detik.</p>
                            <p class="mb-0">Jika tidak diarahkan, silakan klik tombol di bawah ini.</p>
                        </div>
                        <a href="https://wa.me/6289667915756?text=Halo AYelectronic Store, Saya mau konfirmasi pesanan dengan KODE: *<?= htmlspecialchars($order['kode_pesanan']) ?>*" class="btn btn-success btn-lg mt-3" target="_blank">
                            <i class="bi bi-whatsapp me-2"></i>Konfirmasi via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
include_once 'includes/footer.php';
$conn->close();
ob_end_flush();
?>

<script>
// Skrip hitung mundur sederhana
let timeLeft = 5;
const countdownElement = document.getElementById('countdown');
const timer = setInterval(function() {
    if (timeLeft <= 1) {
        clearInterval(timer);
    }
    timeLeft -= 1;
    if (countdownElement) {
        countdownElement.textContent = timeLeft;
    }
}, 1000);
</script>
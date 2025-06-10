<?php
// apply_discount.php (Versi yang sudah diperbaiki)

session_start();
header('Content-Type: application/json');
include_once 'config/database.php';

$response = ['success' => false, 'message' => 'Aksi tidak valid.'];

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['kode_diskon'])) {
    $kode_diskon = trim($_POST['kode_diskon']);
    
    // Logika untuk menghapus diskon jika input dikosongkan
    if (empty($kode_diskon)) {
        unset($_SESSION['discount']); 
        $totals = [];
        if (function_exists('calculate_totals')) { // Memanggil fungsi dari cart_action jika ada
             include_once 'cart_action.php';
             $totals = calculate_totals();
        }
        // Kirim balik grand total yang asli
        $response = ['success' => true, 'cleared' => true, 'grand_total' => $totals['grand_total'] ?? 0];
        echo json_encode($response);
        exit();
    }

    $conn = new mysqli($host, $user, $pass, $db_name);
    if ($conn->connect_error) {
        $response['message'] = 'Koneksi database gagal.';
        echo json_encode($response);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM discounts WHERE kode_diskon = ?");
    $stmt->bind_param("s", $kode_diskon);
    $stmt->execute();
    $discount = $stmt->get_result()->fetch_assoc();

    if (!$discount) {
        $response['message'] = 'Kode diskon tidak ditemukan.';
        echo json_encode($response);
        exit();
    }

    if ($discount['aktif'] != 1) {
        $response['message'] = 'Kode diskon sudah tidak aktif.';
        echo json_encode($response);
        exit();
    }

    $today = date('Y-m-d');
    if ($today < $discount['tanggal_mulai'] || $today > $discount['tanggal_berakhir']) {
        $response['message'] = 'Kode diskon tidak berlaku pada tanggal ini.';
        echo json_encode($response);
        exit();
    }

    $subtotal = 0;
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
    }

    if ($subtotal < $discount['min_pembelian']) {
        $response['message'] = 'Minimal pembelian untuk diskon ini adalah Rp' . number_format($discount['min_pembelian']) . '.';
        echo json_encode($response);
        exit();
    }

    $discount_amount = 0;
    if ($discount['tipe_diskon'] === 'prsn') {
        $discount_amount = ($subtotal * $discount['nilai_persentase']) / 100;
    } else {
        $discount_amount = $discount['nilai_nominal'];
    }

    $_SESSION['discount'] = [
        'code' => $discount['kode_diskon'],
        'amount' => $discount_amount
    ];
    
    $new_grand_total = $subtotal - $discount_amount;

    $response = [
        'success' => true,
        'message' => 'Diskon berhasil diterapkan!',
        'discount_amount' => $discount_amount,
        'new_grand_total' => $new_grand_total
    ];

    $stmt->close();
    $conn->close();
}

echo json_encode($response);
// PERHATIAN: Kurung kurawal '}' yang salah di sini sudah dihapus.
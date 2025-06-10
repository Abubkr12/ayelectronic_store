<?php
// ayelectronic_store/get_customer_order_details.php (Versi Final Diperbaiki)

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['error' => 'Akses ditolak. Silakan login.']);
    exit();
}

include_once 'config/database.php';

$response = [];
$order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$customer_id = $_SESSION['customer_id'];

if ($order_id > 0) {
    $conn = new mysqli($host, $user, $pass, $db_name);
    if ($conn->connect_error) {
        $response = ['error' => 'Koneksi database gagal.'];
    } else {
        // --- RINCIAN PERUBAHAN UTAMA ADA DI QUERY DI BAWAH INI ---
        $sql = "SELECT 
                    od.nama_produk, 
                    od.harga_saat_pesan, 
                    od.jumlah_beli, 
                    od.subtotal,
                    pv.nama_varian 
                FROM order_details od
                JOIN orders o ON od.order_id = o.id
                LEFT JOIN product_variants pv ON od.variant_id = pv.id
                WHERE od.order_id = ? AND o.customer_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $order_id, $customer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            // Ganti nama_varian dengan nama produk jika varian tidak ada namanya
            $row['variant_name'] = $row['nama_varian'] ?? $row['nama_produk'];
            $response[] = $row;
        }
        
        // Jika setelah loop response masih kosong, berarti pesanan tidak ditemukan
        if (empty($response)) {
            $response = ['error' => 'Detail pesanan tidak ditemukan atau Anda tidak memiliki akses.'];
        }

        $stmt->close();
        $conn->close();
    }
} else {
    $response = ['error' => 'ID Pesanan tidak valid.'];
}

echo json_encode($response);
exit();
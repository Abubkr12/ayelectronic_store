<?php
// ayelectronic_store/cart_action.php (Versi Final dengan Integrasi Diskon)

session_start();
header('Content-Type: application/json');

// --- FUNGSI BANTUAN ---
function calculate_totals() {
    $totals = ['total_items' => 0, 'grand_total' => 0];
    if (!empty($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $totals['total_items'] += $item['quantity'];
            $totals['grand_total'] += $item['price'] * $item['quantity'];
        }
    }
    return $totals;
}

// --- INISIALISASI ---
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}
$action = $_POST['action'] ?? '';
$response = ['success' => false, 'message' => 'Aksi tidak valid.'];

// --- PROSES AKSI ---
if ($action) {
    include_once 'config/database.php';
    $conn = new mysqli($host, $user, $pass, $db_name);
    if ($conn->connect_error) {
        $response['message'] = 'Koneksi database gagal.';
        echo json_encode($response);
        exit();
    }

    switch ($action) {
        
        // --- FUNGSI 'add' (TIDAK ADA PERUBAHAN) ---
        case 'add':
            $product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : null;
            $variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;
            $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
            
            $stmt = null;
            if ($variant_id) {
                $sql = "SELECT p.id as product_id, p.nama_produk, p.gambar_utama, pv.id as variant_id, pv.nama_varian, pv.harga_varian FROM product_variants pv JOIN products p ON pv.product_id = p.id WHERE pv.id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $variant_id);
            } elseif ($product_id) {
                $sql = "SELECT p.id as product_id, p.nama_produk, p.gambar_utama, pv.id as variant_id, pv.nama_varian, pv.harga_varian FROM products p JOIN product_variants pv ON p.id = pv.product_id WHERE p.id = ? ORDER BY pv.id ASC LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $product_id);
            }

            if ($stmt) {
                $stmt->execute();
                $result = $stmt->get_result();
                if ($product = $result->fetch_assoc()) {
                    $cart_key = $product['variant_id'];
                    if (isset($_SESSION['cart'][$cart_key])) {
                        $_SESSION['cart'][$cart_key]['quantity'] += $quantity;
                    } else {
                        $_SESSION['cart'][$cart_key] = ['product_id' => $product['product_id'], 'variant_id' => $product['variant_id'], 'name' => $product['nama_produk'], 'variant_name' => $product['nama_varian'], 'price' => $product['harga_varian'], 'image' => $product['gambar_utama'], 'quantity' => (int)$quantity];
                    }
                    $totals = calculate_totals();
                    $response = ['success' => true, 'message' => '"' . htmlspecialchars($product['nama_produk']) . '" berhasil ditambahkan!', 'total_items' => $totals['total_items']];
                } else { $response['message'] = 'Produk atau varian tidak ditemukan.'; }
                $stmt->close();
            }
            break;

        // --- FUNGSI 'update' (TIDAK ADA PERUBAHAN) ---
        case 'update':
            if (isset($_POST['variant_id']) && isset($_POST['quantity'])) {
                $variant_id = $_POST['variant_id'];
                $quantity = (int)$_POST['quantity'];
                if (isset($_SESSION['cart'][$variant_id])) {
                    if ($quantity > 0) {
                        $_SESSION['cart'][$variant_id]['quantity'] = $quantity;
                        $totals = calculate_totals();
                        $item = $_SESSION['cart'][$variant_id];
                        $subtotal = $item['price'] * $item['quantity'];
                        $response = ['success' => true, 'item_removed' => false, 'total_items' => $totals['total_items'], 'grand_total' => $totals['grand_total'], 'subtotal' => $subtotal];
                    } else {
                        unset($_SESSION['cart'][$variant_id]);
                        $totals = calculate_totals();
                        $response = ['success' => true, 'item_removed' => true, 'total_items' => $totals['total_items'], 'grand_total' => $totals['grand_total']];
                    }
                }
            }
            break;

        // --- FUNGSI 'remove' (TIDAK ADA PERUBAHAN) ---
        case 'remove':
            if (isset($_POST['variant_id'])) {
                $variant_id = $_POST['variant_id'];
                if (isset($_SESSION['cart'][$variant_id])) {
                    unset($_SESSION['cart'][$variant_id]);
                    $totals = calculate_totals();
                    $response = ['success' => true, 'total_items' => $totals['total_items'], 'grand_total' => $totals['grand_total']];
                }
            }
            break;
        
        // --- FUNGSI 'submit_order' (DENGAN PERUBAHAN) ---
        case 'submit_order':
            if (!isset($_SESSION['customer_id'])) {
                $response['message'] = 'Anda harus login terlebih dahulu.';
                break;
            }
            if (empty($_SESSION['cart'])) {
                $response['message'] = 'Keranjang belanja Anda kosong.';
                break;
            }

            $conn->begin_transaction();
            try {
                $customer_id = $_SESSION['customer_id'];
                $stmt_customer = $conn->prepare("SELECT nama_lengkap, nomor_hp FROM customers WHERE id = ?");
                $stmt_customer->bind_param("i", $customer_id);
                $stmt_customer->execute();
                $customer = $stmt_customer->get_result()->fetch_assoc();
                
                if (!$customer) throw new Exception("Data pelanggan tidak ditemukan.");
                
                $totals = calculate_totals();
                $kode_pesanan = 'AYE-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

                // -- TAMBAHAN --: Ambil data diskon dari sesi
                $discount_code = $_SESSION['discount']['code'] ?? null;
                $discount_amount = $_SESSION['discount']['amount'] ?? 0;
                $final_total = $totals['grand_total'] - $discount_amount;
                // Pastikan total tidak menjadi negatif
                if ($final_total < 0) {
                    $final_total = 0;
                }

                // -- MODIFIKASI --: Query INSERT disesuaikan dengan kolom baru untuk diskon
                $stmt_order = $conn->prepare("INSERT INTO orders (customer_id, kode_pesanan, nama_pembeli, nomor_whatsapp, total_harga, kode_diskon_dipakai, jumlah_diskon, status_pesanan) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt_order->bind_param("isssdids", $customer_id, $kode_pesanan, $customer['nama_lengkap'], $customer['nomor_hp'], $final_total, $discount_code, $discount_amount);
                $stmt_order->execute();
                $order_id = $conn->insert_id;

                $stmt_details = $conn->prepare("INSERT INTO order_details (order_id, product_id, variant_id, nama_produk, harga_saat_pesan, jumlah_beli, subtotal) VALUES (?, ?, ?, ?, ?, ?, ?)");
                
                foreach ($_SESSION['cart'] as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $stmt_details->bind_param("iiisdid", $order_id, $item['product_id'], $item['variant_id'], $item['name'], $item['price'], $item['quantity'], $subtotal);
                    $stmt_details->execute();
                }

                $conn->commit(); 
                unset($_SESSION['cart']);
                unset($_SESSION['discount']); // -- TAMBAHAN --: Hapus sesi diskon setelah checkout

                // Alihkan ke halaman sukses sambil membawa kode pesanan
                header("Location: order_success.php?kode=" . $kode_pesanan);
                exit(); // Hentikan eksekusi setelah redirect
            } catch (Exception $e) {
                $conn->rollback();
                $response['message'] = 'Gagal menyimpan pesanan: ' . $e->getMessage();
            }
            break;
    }
    $conn->close();
}

echo json_encode($response);
exit();
<?php
// ayelectronic_store/my_orders.php (Versi Final Lengkap dan Terverifikasi)

session_start();
ob_start();

// Keamanan: Pastikan pelanggan sudah login untuk mengakses halaman ini.
if (!isset($_SESSION['customer_id'])) {
    // Simpan halaman tujuan agar bisa kembali ke sini setelah login berhasil
    $_SESSION['redirect_url'] = 'my_orders.php'; 
    header("Location: customer_login.php");
    exit();
}

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php'; // Menggunakan header frontend
include_once 'includes/helpers.php'; // Menggunakan helper untuk breadcrumb

// Ambil semua data pesanan milik pelanggan yang sedang login dari sesi
$customer_id = $_SESSION['customer_id'];
$sql = "SELECT * FROM orders WHERE customer_id = ? ORDER BY tanggal_pesanan DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>
<title>Pesanan Saya - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Pesanan Saya']
    ]);
    ?>

    <div class="container my-5">
        <h1 class="text-center mb-5 section-heading">Riwayat Pesanan Saya</h1>

        <div class="card shadow-sm border-0 fade-in-on-scroll">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th class="ps-3">Kode Pesanan</th>
                                <th>Tanggal</th>
                                <th class="text-end">Total Harga</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($orders)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <p class="text-muted mb-1">Anda belum memiliki riwayat pesanan.</p>
                                        <a href="products_page.php" class="btn btn-primary btn-sm">Mulai Belanja</a>
                                    </td>
                                </tr>
                            <?php else: foreach ($orders as $order): ?>
                                <tr>
                                    <td class="ps-3"><span class="badge bg-secondary fw-normal"><?= htmlspecialchars($order['kode_pesanan']) ?></span></td>
                                    <td><?= date('d F Y, H:i', strtotime($order['tanggal_pesanan'])) ?></td>
                                    <td class="text-end fw-bold">Rp<?= number_format($order['total_harga'], 0, ',', '.') ?></td>
                                    <td class="text-center">
                                        <?php
                                            $status = $order['status_pesanan'];
                                            $status_class = 'bg-secondary';
                                            if ($status == 'pending') $status_class = 'bg-warning text-dark';
                                            else if ($status == 'diproses') $status_class = 'bg-info text-dark';
                                            else if ($status == 'dikirim') $status_class = 'bg-primary';
                                            else if ($status == 'selesai') $status_class = 'bg-success';
                                            else if ($status == 'dibatalkan') $status_class = 'bg-danger';
                                        ?>
                                        <span class="badge <?= $status_class ?>"><?= ucfirst($status) ?></span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-primary view-order-details" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#orderDetailsModal"
                                                data-order-id="<?= $order['id'] ?>"
                                                data-order-code="<?= htmlspecialchars($order['kode_pesanan']) ?>">
                                            <i class="bi bi-eye-fill"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="orderDetailsModalLabel">Detail Pesanan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center my-5" id="modal-loading-spinner">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <div id="modal-order-content" style="display: none;">
                    </div>
            </div>
        </div>
    </div>
</div>

<?php
include 'includes/footer.php';
$conn->close();
ob_end_flush();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const orderDetailsModal = document.getElementById('orderDetailsModal');
    if (orderDetailsModal) {
        orderDetailsModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const orderId = button.dataset.orderId;
            const orderCode = button.dataset.orderCode;

            const modalTitle = orderDetailsModal.querySelector('.modal-title');
            const modalContent = orderDetailsModal.querySelector('#modal-order-content');
            const modalSpinner = orderDetailsModal.querySelector('#modal-loading-spinner');
            
            modalTitle.textContent = `Detail Pesanan: ${orderCode}`;
            modalContent.innerHTML = ''; 
            modalContent.style.display = 'none';
            modalSpinner.style.display = 'block';

            fetch(`get_customer_order_details.php?order_id=${orderId}`)
                .then(response => {
                    if (!response.ok) { throw new Error('Network response was not ok'); }
                    return response.json();
                })
                .then(data => {
                    modalSpinner.style.display = 'none';
                    modalContent.style.display = 'block';

                    if (data.error) {
                        modalContent.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                        return;
                    }
                    
                    let tableHtml = '<table class="table table-sm"><thead><tr><th>Produk</th><th class="text-end">Harga</th><th class="text-center">Jumlah</th><th class="text-end">Subtotal</th></tr></thead><tbody>';
                    let grandTotal = 0;
                    data.forEach(item => {
                        grandTotal += parseFloat(item.subtotal);
                        tableHtml += `
                            <tr>
                                <td>${item.nama_produk}<br><small class="text-muted">${item.variant_name || ''}</small></td>
                                <td class="text-end">Rp${new Intl.NumberFormat('id-ID').format(item.harga_saat_pesan)}</td>
                                <td class="text-center">${item.jumlah_beli}</td>
                                <td class="text-end fw-bold">Rp${new Intl.NumberFormat('id-ID').format(item.subtotal)}</td>
                            </tr>
                        `;
                    });
                    tableHtml += `</tbody><tfoot><tr><td colspan="3" class="text-end fw-bold border-0">Total</td><td class="text-end fw-bold border-0">Rp${new Intl.NumberFormat('id-ID').format(grandTotal)}</td></tr></tfoot></table>`;
                    modalContent.innerHTML = tableHtml;
                })
                .catch(error => {
                    modalSpinner.style.display = 'none';
                    modalContent.style.display = 'block';
                    modalContent.innerHTML = `<div class="alert alert-danger">Gagal memuat detail pesanan. Silakan coba lagi.</div>`;
                    console.error('Error fetching order details:', error);
                });
        });
    }
});
</script>
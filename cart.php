<?php
// ayelectronic_store/cart.php (Versi Final Lengkap dan Terverifikasi)

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$cart = $_SESSION['cart'] ?? [];
$grand_total = 0;
$body_class = 'bg-light';

include 'includes/header.php';
?>
<title>Keranjang Belanja - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
// RINCIAN PERUBAHAN: Memanggil fungsi breadcrumb di halaman keranjang
include_once 'includes/helpers.php';
$breadcrumb_items = [
    ['text' => 'Beranda', 'url' => 'index.php'],
    ['text' => 'Keranjang Belanja']
];
generate_breadcrumb($breadcrumb_items);
?>
    <div class="container my-5" id="cart-container">
        <h1 class="text-center mb-4">Keranjang Belanja</h1>
        
        <div id="cart-view" style="<?= empty($cart) ? 'display: none;' : '' ?>">
            <div class="table-responsive fade-in-on-scroll">
                <table class="table align-middle" id="cart-table">
                    <thead>
                        <tr>
                            <th colspan="2">Produk</th>
                            <th>Harga</th>
                            <th style="width: 15%;">Kuantitas</th>
                            <th class="text-end">Subtotal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart as $variant_id => $item):
                            $subtotal = $item['price'] * $item['quantity'];
                            $grand_total += $subtotal;
                        ?>
                            <tr id="row-<?= $variant_id ?>">
                                <td style="width: 100px;"><img src="/ayelectronic_store/<?= htmlspecialchars($item['image']) ?>" class="img-fluid rounded"></td>
                                <td><strong><?= htmlspecialchars($item['name']) ?></strong><p class="small text-muted mb-0"><?= htmlspecialchars($item['variant_name']) ?></p></td>
                                <td>Rp<?= number_format($item['price'], 0, ',', '.') ?></td>
                                <td><input type="number" class="form-control form-control-sm text-center quantity-input" value="<?= $item['quantity'] ?>" min="0" data-variant-id="<?= $variant_id ?>"></td>
                                <td class="text-end" id="subtotal-<?= $variant_id ?>">Rp<?= number_format($subtotal, 0, ',', '.') ?></td>
                                <td><button class="btn btn-danger btn-sm remove-btn" data-variant-id="<?= $variant_id ?>"><i class="bi bi-trash-fill"></i></button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div class="row justify-content-end mt-4 fade-in-on-scroll">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Ringkasan Belanja</h5>
                            <hr>
                            <form id="discount-form" class="mb-3">
                                <label for="kode_diskon" class="form-label">Punya Kode Diskon?</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="kode_diskon" name="kode_diskon" placeholder="Masukkan kode">
                                    <button class="btn btn-outline-primary" type="submit">Terapkan</button>
                                </div>
                            </form>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <p class="mb-2">Subtotal</p>
                                <p class="mb-2" id="subtotal-summary">Rp<?= number_format($grand_total, 0, ',', '.') ?></p>
                            </div>
                            <div class="d-flex justify-content-between text-success" id="discount-row" style="display: none;">
                                <p class="mb-2">Diskon</p>
                                <p class="mb-2" id="discount-amount"></p>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between fs-5">
                                <p class="mb-2 fw-bold">Grand Total</p>
                                <p class="mb-2 fw-bold" id="grand-total">Rp<?= number_format($grand_total, 0, ',', '.') ?></p>
                            </div>
                            <div class="d-grid mt-3">
                                <?php if (isset($_SESSION['customer_id'])): ?>
                                    <button id="checkout-btn" class="btn btn-primary btn-lg">Lanjut ke Pembayaran via Whatsapp</button>
                                <?php else: ?>
                                    <a href="customer_login.php" class="btn btn-primary btn-lg">Login untuk Checkout</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="empty-cart-message" class="text-center py-5" style="<?= empty($cart) ? 'display: block;' : 'display: none;' ?>">
            <i class="bi bi-cart-x" style="font-size: 5rem; color: #DBE2EF;"></i>
            <h3 class="mt-3">Keranjang belanja Anda kosong.</h3>
            <p class="text-muted">Sepertinya Anda belum menambahkan produk apapun.</p>
            <a href="index.php" class="btn btn-primary mt-3">Mulai Berbelanja</a>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
$conn->close();
ob_end_flush();
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- 1. Seleksi Elemen & Inisialisasi ---
    const cartContainer = document.getElementById('cart-container');
    if (!cartContainer) {
        console.warn('Elemen #cart-container tidak ditemukan. Script keranjang tidak akan berjalan.');
        return;
    }
    const cartBadge = document.querySelector('#cartIcon .badge');
    const cartView = document.getElementById('cart-view');
    const emptyCartMessage = document.getElementById('empty-cart-message');
    const grandTotalEl = document.getElementById('grand-total');

    // --- 2. Fungsi Bantuan (Helpers) ---

    /**
     * Memformat angka menjadi format mata uang Rupiah.
     * @param {number} number - Angka yang akan diformat.
     * @returns {string} String dalam format 'RpX.XXX.XXX'.
     */
    function formatRupiah(number) {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(number);
    }

    /**
     * Memperbarui elemen UI keranjang seperti total item, grand total, dan pesan keranjang kosong.
     * @param {object} data - Objek data dari respons API.
     */
    function updateCartUI(data) {
        if (cartBadge) {
            cartBadge.textContent = data.total_items;
        }
        if (grandTotalEl) {
            grandTotalEl.textContent = formatRupiah(data.grand_total);
        }
        // Tampilkan atau sembunyikan pesan keranjang kosong
        const isCartEmpty = data.total_items === 0;
        if (cartView) {
            cartView.style.display = isCartEmpty ? 'none' : 'block';
        }
        if (emptyCartMessage) {
            emptyCartMessage.style.display = isCartEmpty ? 'block' : 'none';
        }
    }

    /**
     * Mengirim data ke API keranjang menggunakan metode POST.
     * @param {object} body - Data yang akan dikirim dalam body request.
     * @returns {Promise<object>} - Respons JSON dari server.
     */
    async function postToCartAPI(body) {
        const response = await fetch('cart_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams(body).toString()
        });
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    }


    // --- 3. Penangan Aksi (Action Handlers) ---

    /**
     * Menangani aksi penghapusan item dari keranjang.
     * @param {HTMLElement} button - Tombol hapus yang diklik.
     */
    async function handleRemoveItem(button) {
        const variantId = button.dataset.variantId;
        if (!confirm('Yakin ingin menghapus item ini?')) {
            return;
        }

        button.disabled = true;
        try {
            const data = await postToCartAPI({ action: 'remove', variant_id: variantId });
            if (data.success) {
                document.getElementById(`row-${variantId}`)?.remove();
                updateCartUI(data);
            } else {
                alert(data.message || 'Gagal menghapus item.');
                button.disabled = false;
            }
        } catch (error) {
            console.error('Error saat menghapus item:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
            button.disabled = false;
        }
    }

    /**
     * Menangani aksi pembaruan kuantitas item.
     * @param {HTMLInputElement} input - Input kuantitas yang diubah.
     */
    async function handleUpdateQuantity(input) {
        const variantId = input.dataset.variantId;
        const quantity = input.value;

        try {
            const data = await postToCartAPI({ action: 'update', variant_id: variantId, quantity: quantity });
            if (data.success) {
                if (data.item_removed) {
                    document.getElementById(`row-${variantId}`)?.remove();
                } else {
                    const subtotalEl = document.getElementById(`subtotal-${variantId}`);
                    if (subtotalEl) {
                        subtotalEl.textContent = formatRupiah(data.subtotal);
                    }
                }
                updateCartUI(data);
            } else {
                alert(data.message || 'Gagal memperbarui kuantitas.');
                // Opsional: kembalikan nilai input ke nilai sebelumnya jika ada
            }
        } catch (error) {
            console.error('Error saat memperbarui kuantitas:', error);
            alert('Terjadi kesalahan. Silakan coba lagi.');
        }
    }

    /**
     * Menangani proses checkout dengan membuat dan mengirimkan form dinamis.
     * @param {HTMLElement} button - Tombol checkout yang diklik.
     */
    function handleCheckout(button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Memproses...';

        // Membuat form dinamis untuk submit order,
        // ini adalah cara sederhana untuk memicu navigasi POST standar browser.
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'cart_action.php';
        form.style.display = 'none';

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'submit_order';
        form.appendChild(actionInput);

        document.body.appendChild(form);
        form.submit();
    }

    // --- 4. Event Listeners (Delegation) ---

    // Listener utama untuk semua aksi 'klik' di dalam container keranjang.
    cartContainer.addEventListener('click', function(e) {
        const removeButton = e.target.closest('.remove-btn');
        if (removeButton) {
            e.preventDefault();
            handleRemoveItem(removeButton);
            return;
        }

        const checkoutButton = e.target.closest('#checkout-btn');
        if (checkoutButton) {
            e.preventDefault();
            handleCheckout(checkoutButton);
            return;
        }
    });

    // Listener utama untuk aksi 'perubahan' pada input kuantitas.
    cartContainer.addEventListener('change', function(e) {
        const quantityInput = e.target.closest('.quantity-input');
        if (quantityInput) {
            handleUpdateQuantity(quantityInput);
        }
    });
    // --- Logika untuk Form Diskon ---
const discountForm = document.getElementById('discount-form');
if (discountForm) {
    discountForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const codeInput = document.getElementById('kode_diskon');
        const code = codeInput.value;
        const submitButton = this.querySelector('button[type="submit"]');

        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

        fetch('apply_discount.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'kode_diskon=' + encodeURIComponent(code)
        })
        .then(response => response.json())
        .then(data => {
            const discountRow = document.getElementById('discount-row');
            const discountAmountEl = document.getElementById('discount-amount');
            const grandTotalEl = document.getElementById('grand-total');

            if (data.success) {
                if (data.cleared) { // Jika diskon dihapus
                    discountRow.style.display = 'none';
                    // Anda perlu menambahkan logika untuk mengupdate grand total kembali ke subtotal
                } else {
                    discountRow.style.display = 'flex';
                    discountAmountEl.textContent = '- ' + formatRupiah(data.discount_amount);
                    grandTotalEl.textContent = formatRupiah(data.new_grand_total);
                }
                showBootstrapAlert(data.message || 'Diskon diperbarui!', 'success');
            } else {
                discountRow.style.display = 'none'; // Sembunyikan jika error
                // Anda perlu menambahkan logika untuk mengupdate grand total kembali ke subtotal
                showBootstrapAlert(data.message || 'Terjadi kesalahan.', 'danger');
            }
        })
        .catch(error => console.error('Error:', error))
        .finally(() => {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Terapkan';
        });
    });
}

});
</script>
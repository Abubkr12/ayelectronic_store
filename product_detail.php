<?php
// ayelectronic_store/product_detail.php (Versi Final Interaktif)

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($product_id === 0) {
    header("Location: index.php");
    exit();
}

$sql_product = "SELECT p.*, c.nama_kategori, b.nama_brand FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id WHERE p.id = ?";
$stmt = $conn->prepare($sql_product);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}

$sql_variants = "SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC";
$stmt_variants = $conn->prepare($sql_variants);
$stmt_variants->bind_param("i", $product_id);
$stmt_variants->execute();
$variants = $stmt_variants->get_result()->fetch_all(MYSQLI_ASSOC);

$body_class = 'bg-light';
include 'includes/header.php';
?>
<title><?= htmlspecialchars($product['nama_produk']) ?> - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
// RINCIAN PERUBAHAN: Memanggil fungsi breadcrumb
include_once 'includes/helpers.php';
$breadcrumb_items = [
    ['text' => 'Beranda', 'url' => 'index.php'],
    ['text' => 'Kategori', 'url' => 'categories_page.php'],
    ['text' => $product['nama_kategori'], 'url' => 'category_page.php?id=' . $product['category_id']], // Nanti bisa diarahkan ke halaman kategori
    ['text' => $product['nama_produk']] // Item terakhir tidak butuh url
];
generate_breadcrumb($breadcrumb_items);
?>

    <div class="container my-5 fade-in-on-scroll">
        <div class="card shadow-sm border-0">
            <div class="card-body p-lg-5">
                <div class="row">
                    <div class="col-lg-6 mb-4 mb-lg-0">
                        <?php
                        $image_src = '/ayelectronic_store/' . htmlspecialchars($product['gambar_utama'] ?? 'assets/img/default_product.png');
                        ?>
                        <img src="<?= $image_src ?>" class="img-fluid rounded main-product-image" alt="<?= htmlspecialchars($product['nama_produk']) ?>">
                    </div>
                    <div class="col-lg-6">
                        <div id="dynamic-alert-container"></div>
                        <h1 class="product-title"><?= htmlspecialchars($product['nama_produk']) ?></h1>
                        <p class="product-category">
                            Kategori: <a href="#"><?= htmlspecialchars($product['nama_kategori'] ?? 'Tidak ada') ?></a> | 
                            Merek: <a href="#"><?= htmlspecialchars($product['nama_brand'] ?? 'Tidak ada') ?></a>
                        </p>
                        <p class="product-short-desc lead"><?= htmlspecialchars($product['deskripsi_singkat']) ?></p>
                        <hr>
                        
                        <div class="variants-section">
                            <h5 class="mb-3">Pilih Varian:</h5>
                            <div id="variant-options">
                                <?php if (!empty($variants)): foreach ($variants as $index => $variant): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input variant-radio" type="radio" name="variant_option" 
                                               id="variant-<?= $variant['id'] ?>" 
                                               value="<?= $variant['id'] ?>"
                                               data-price="<?= $variant['harga_varian'] ?>"
                                               <?= $index === 0 ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="variant-<?= $variant['id'] ?>">
                                            <strong><?= htmlspecialchars($variant['nama_varian']) ?></strong> - 
                                            <span class="text-primary fw-bold">Rp<?= number_format($variant['harga_varian'], 0, ',', '.') ?></span>
                                        </label>
                                    </div>
                                <?php endforeach; else: ?>
                                    <p class="text-muted">Tidak ada pilihan varian untuk produk ini.</p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="price-display my-4">
                            <span class="fs-3 fw-bold text-primary-futuristic" id="displayed-price">
                                Rp<?= !empty($variants) ? number_format($variants[0]['harga_varian'], 0, ',', '.') : number_format($product['harga'] ?? 0, 0, ',', '.') ?>
                            </span>
                        </div>
                        
                        <div class="d-flex align-items-center">
                            <input type="number" id="quantity-input" class="form-control me-3" value="1" min="1" style="width: 80px;">
                            <button id="add-to-cart-detail-btn" class="btn btn-primary btn-lg flex-grow-1" <?= empty($variants) ? 'disabled' : '' ?>>
                                <i class="bi bi-cart-plus-fill me-2"></i>Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="row mt-5">
                    <div class="col-12">
                        <h3>Deskripsi Lengkap</h3>
                        <p><?= nl2br(htmlspecialchars($product['deskripsi_lengkap'])) ?></p>
                        <hr>
                        <h3>Spesifikasi</h3>
                        <p><?= nl2br(htmlspecialchars($product['spesifikasi'])) ?></p>
                    </div>
                </div>
            </div>
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
    // --- Elemen-elemen yang dibutuhkan ---
    const variantRadios = document.querySelectorAll('.variant-radio');
    const displayedPrice = document.getElementById('displayed-price');
    const addToCartBtn = document.getElementById('add-to-cart-detail-btn');
    const quantityInput = document.getElementById('quantity-input');
    const cartBadge = document.querySelector('#cartIcon .badge');

    // --- Fungsi Bantuan ---
    function formatRupiah(number) {
        return 'Rp' + new Intl.NumberFormat('id-ID').format(number);
    }

    // --- FUNGSI BARU: Untuk menghitung dan memperbarui total harga ---
    function updateTotalPrice() {
        const selectedVariant = document.querySelector('input[name="variant_option"]:checked');
        // Ambil kuantitas, pastikan nilainya angka dan minimal 1
        const quantity = parseInt(quantityInput.value) || 1;

        if (selectedVariant && displayedPrice) {
            const pricePerItem = parseFloat(selectedVariant.getAttribute('data-price'));
            const totalPrice = pricePerItem * quantity;
            displayedPrice.textContent = formatRupiah(totalPrice);
        }
    }

    // --- MODIFIKASI: Event listener untuk varian sekarang memanggil fungsi update ---
    variantRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            // Saat varian diganti, set kuantitas kembali ke 1 dan update total harga
            quantityInput.value = 1;
            updateTotalPrice();
        });
    });

    // --- TAMBAHAN: Event listener untuk input kuantitas ---
    if (quantityInput) {
        // 'input' lebih responsif daripada 'change', karena langsung bereaksi saat diketik/diubah
        quantityInput.addEventListener('input', function() {
            // Jika user mengosongkan input atau memasukkan 0, anggap 1
            if (parseInt(this.value) < 1 || this.value === '') {
                this.value = 1;
            }
            updateTotalPrice(); // Panggil fungsi update setiap kali kuantitas berubah
        });
    }

    // --- Logika "Tambah ke Keranjang" (TIDAK DIUBAH, TETAP SAMA) ---
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const selectedVariant = document.querySelector('input[name="variant_option"]:checked');
            if (!selectedVariant) {
                alert('Silakan pilih varian terlebih dahulu.');
                return;
            }
            
            const variantId = selectedVariant.value;
            const quantity = quantityInput.value;

            this.disabled = true;
            this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Menambahkan...';

            fetch('cart_action.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `action=add&variant_id=${variantId}&quantity=${quantity}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (cartBadge) {
                        cartBadge.textContent = data.total_items;
                    }
                    alert(data.message);
                } else {
                    alert('Gagal: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error))
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="bi bi-cart-plus-fill me-2"></i>Tambah ke Keranjang';
            });
        });
    }

    // Panggil sekali saat halaman dimuat untuk memastikan harga awal sudah benar
    updateTotalPrice();
});

</script>
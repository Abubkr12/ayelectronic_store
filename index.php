<?php
// ayelectronic_store/index.php (Versi Final Lengkap & Terintegrasi)

session_start();
ob_start();

// 1. Panggil konfigurasi dan buat SATU koneksi database
include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}

// 2. Sertakan header (header akan menggunakan koneksi $conn yang sudah ada)
// File header ini sudah berisi doctype, head, navbar, dan sidebar
$body_class = 'home';
include_once 'includes/header.php';

// 3. Ambil data spesifik untuk halaman ini menggunakan $conn
// --- Perubahan 1: Query SQL diperbarui untuk menghitung varian ---
$sql_products = "
    SELECT 
        p.id, p.nama_produk, p.gambar_utama, c.nama_kategori, 
        pv.harga_varian, 
        COUNT(pv.id) AS variant_count
    FROM products p 
    JOIN categories c ON p.category_id = c.id
    LEFT JOIN product_variants pv ON p.id = pv.product_id
    GROUP BY p.id 
    ORDER BY p.created_at DESC 
    LIMIT 8";
$products = $conn->query($sql_products)->fetch_all(MYSQLI_ASSOC);

$sliders = $conn->query("SELECT * FROM homepage_sliders WHERE aktif = 1 ORDER BY urutan ASC")->fetch_all(MYSQLI_ASSOC);
$featured_categories = $conn->query("SELECT c.id, c.nama_kategori, c.icon_class, COUNT(p.id) AS product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY product_count DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);
$featured_brands = $conn->query("SELECT b.id, b.nama_brand, b.logo_brand, COUNT(p.id) AS product_count FROM brands b LEFT JOIN products p ON b.id = p.brand_id GROUP BY b.id ORDER BY product_count DESC LIMIT 4")->fetch_all(MYSQLI_ASSOC);

?>
<title>AYelectronic Store - Gadget & Elektronik Futuristik</title>

<main>
    <section class="hero-section vh-100 d-flex justify-content-center align-items-center" id="hero">
        <div id="heroCarousel" class="carousel slide carousel-fade h-100 w-100" data-bs-ride="carousel">
            <div class="carousel-inner h-100">
                <?php if (!empty($sliders)): foreach ($sliders as $index => $slider): ?>
                    <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                        <?php
                        $slider_image_src = '/ayelectronic_store/assets/img/default_hero_bg.jpg';
                        if (!empty($slider['gambar_url'])) {
                            $is_url = strpos($slider['gambar_url'], 'http') === 0;
                            $slider_image_src = $is_url ? htmlspecialchars($slider['gambar_url']) : '/ayelectronic_store/' . htmlspecialchars($slider['gambar_url']);
                        }
                        ?>
                        <div class="hero-slider-bg" style="background-image: url('<?= $slider_image_src ?>');"></div>
                        <div class="hero-overlay" style="background-color: rgba(17, 45, 78, <?= htmlspecialchars($slider['efek_opasitas'] ?? 0.4) ?>); backdrop-filter: blur(<?= htmlspecialchars($slider['efek_blur_px'] ?? 2) ?>px);"></div>
                        <div class="hero-content">
                            <h1><?= htmlspecialchars($slider['judul'] ?? 'Selamat Datang') ?></h1>
                            <p><?= nl2br(htmlspecialchars($slider['deskripsi'] ?? '')) ?></p>
                            <?php if (!empty($slider['tombol_teks_1'])): ?>
                                <a href="<?= htmlspecialchars($slider['tombol_link_1'] ?? '#') ?>" class="btn btn-primary btn-lg mt-3"><?= htmlspecialchars($slider['tombol_teks_1']) ?></a>
                            <?php endif; ?>
                            <?php if (!empty($slider['tombol_teks_2'])): ?>
                                <a href="<?= htmlspecialchars($slider['tombol_link_2'] ?? '#') ?>" class="btn btn-outline-light btn-lg mt-3 ms-2"><?= htmlspecialchars($slider['tombol_teks_2']) ?></a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; else: ?>
                    <div class="carousel-item active h-100">
                        <div class="hero-slider-bg" style="background-image: url('/ayelectronic_store/assets/img/default_hero_bg.jpg');"></div>
                        <div class="hero-overlay" style="background-color: rgba(17, 45, 78, 0.4); backdrop-filter: blur(2px);"></div>
                        <div class="hero-content">
                            <h1>Selamat Datang di AYelectronic Store</h1>
                            <p>Temukan gadget dan elektronik futuristik terbaik.</p>
                            <a href="product_page.php" class="btn btn-primary btn-lg mt-3">Lihat Produk</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <?php if (count($sliders) > 1): ?>
                <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span></button>
            <?php endif; ?>
        </div>
    </section>

    <div class="container py-5">
        <section id="featured-categories" class="mb-5 text-center section-padding fade-in-on-scroll">
            <h2 class="section-heading">Kategori Unggulan</h2>
            <div class="row row-cols-1 row-cols-md-4 g-4 mt-4">
                <?php if (!empty($featured_categories)): foreach ($featured_categories as $category): ?>
                    <div class="col">
                        <a href="category_page.php?id=<?= $category['id'] ?>" class="text-decoration-none">
                            <div class="card feature-card h-100">
                                <div class="card-body">
                                    <div class="icon-circle mb-3"><i class="<?= htmlspecialchars($category['icon_class'] ?? 'bi-tags-fill') ?>"></i></div>
                                    <h5 class="card-title"><?= htmlspecialchars($category['nama_kategori']) ?></h5>
                                    <p class="card-text text-muted small"><?= $category['product_count'] ?> produk</p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>

        <section id="featured-brands" class="mb-5 text-center section-padding fade-in-on-scroll">
            <h2 class="section-heading">Merk Unggulan</h2>
             <div class="row row-cols-1 row-cols-md-4 g-4 mt-4">
                <?php if (!empty($featured_brands)): foreach ($featured_brands as $brand): ?>
                    <div class="col">
                        <a href="brand_page.php?id=<?= $brand['id'] ?>" class="text-decoration-none">
                            <div class="card feature-card h-100">
                                <div class="card-body">
                                    <div class="icon-circle mb-3">
                                        <?php
                                        $logo_src = '/ayelectronic_store/assets/img/placeholder_brand.png';
                                        if (!empty($brand['logo_brand']) && file_exists('./' . $brand['logo_brand'])) {
                                            $logo_src = '/ayelectronic_store/' . htmlspecialchars($brand['logo_brand']);
                                        }
                                        ?>
                                        <img src="<?= $logo_src ?>" alt="Logo <?= htmlspecialchars($brand['nama_brand']) ?>" style="max-width: 80%; max-height: 80%; object-fit: contain;">
                                    </div>
                                    <h5 class="card-title"><?= htmlspecialchars($brand['nama_brand']) ?></h5>
                                    <p class="card-text text-muted small"><?= $brand['product_count'] ?> produk</p>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>

        <section id="products" class="mb-5 text-center section-padding fade-in-on-scroll">
            <h2 class="section-heading">Produk Pilihan</h2>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4 mt-4">
                <?php if (!empty($products)): foreach ($products as $product): ?>
                    <div class="col">
                        <div class="card product-card h-100">
                            <?php
                            $product_image_src = '/ayelectronic_store/assets/img/default_product.png';
                            if (!empty($product['gambar_utama'])) {
                                $is_url = strpos($product['gambar_utama'], 'http') === 0;
                                $product_image_src = $is_url ? htmlspecialchars($product['gambar_utama']) : '/ayelectronic_store/' . htmlspecialchars($product['gambar_utama']);
                            }
                            ?>
                            <img src="<?= $product_image_src ?>" class="card-img-top" alt="<?= htmlspecialchars($product['nama_produk']) ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?= htmlspecialchars($product['nama_produk']) ?></h5>
                                <p class="card-text text-muted small"><?= htmlspecialchars($product['nama_kategori']) ?></p>
                                <p class="card-text price mt-auto">Mulai dari Rp<?= number_format($product['harga_varian'] ?? 0, 0, ',', '.') ?></p>
                                
                                <div class="d-flex justify-content-between mt-auto">
                                    <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-outline-primary btn-sm flex-grow-1 me-2">Detail</a>
                                    
                                    <?php if ($product['variant_count'] > 1): ?>
                                        <a href="product_detail.php?id=<?= $product['id'] ?>" class="btn btn-primary btn-sm flex-grow-1">Pilih Varian</a>
                                    <?php else: ?>
                                        <button class="btn btn-primary btn-sm add-to-cart-btn flex-grow-1" data-product-id="<?= $product['id'] ?>">Beli</button>
                                    <?php endif; ?>
                                </div>
                                </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
            </div>
        </section>
    </div>
</main>
<?php
// 4. Memanggil footer
include_once 'includes/footer.php';

// 5. Menutup koneksi database di akhir
$conn->close();
ob_end_flush();
?>
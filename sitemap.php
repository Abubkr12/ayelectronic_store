<?php
// ayelectronic_store/sitemap.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';

// Ambil semua data kategori dan merk untuk ditampilkan di sitemap
$categories = $conn->query("SELECT id, nama_kategori FROM categories ORDER BY nama_kategori ASC")->fetch_all(MYSQLI_ASSOC);
$brands = $conn->query("SELECT id, nama_brand FROM brands ORDER BY nama_brand ASC")->fetch_all(MYSQLI_ASSOC);
?>
<title>Peta Situs - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Peta Situs']
    ]);
    ?>

    <div class="container my-5 fade-in-on-scroll">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-lg-5">
                        <h1 class="text-center mb-5 section-heading">Peta Situs</h1>
                        
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <h5>Halaman Utama</h5>
                                <ul class="list-unstyled sitemap-list">
                                    <li><a href="index.php">Beranda</a></li>
                                    <li><a href="products_page.php">Semua Produk</a></li>
                                    <li><a href="categories_page.php">Semua Kategori</a></li>
                                    <li><a href="brands_page.php">Semua Merk</a></li>
                                </ul>
                                <h5 class="mt-4">Halaman Informasi</h5>
                                <ul class="list-unstyled sitemap-list">
                                    <li><a href="about.php">Tentang Kami</a></li>
                                    <li><a href="contact.php">Hubungi Kami</a></li>
                                    <li><a href="faq.php">FAQ</a></li>
                                    <li><a href="privacy_policy.php">Kebijakan Privasi</a></li>
                                    <li><a href="terms.php">Syarat & Ketentuan</a></li>
                                </ul>
                            </div>

                            <div class="col-md-4 mb-4">
                                <h5>Kategori Produk</h5>
                                <ul class="list-unstyled sitemap-list">
                                    <?php if (!empty($categories)): foreach ($categories as $category): ?>
                                        <li><a href="category_page.php?id=<?= $category['id'] ?>"><?= htmlspecialchars($category['nama_kategori']) ?></a></li>
                                    <?php endforeach; else: ?>
                                        <li>Tidak ada kategori.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="col-md-4 mb-4">
                                <h5>Merk Produk</h5>
                                <ul class="list-unstyled sitemap-list">
                                     <?php if (!empty($brands)): foreach ($brands as $brand): ?>
                                        <li><a href="brand_page.php?id=<?= $brand['id'] ?>"><?= htmlspecialchars($brand['nama_brand']) ?></a></li>
                                    <?php endforeach; else: ?>
                                        <li>Tidak ada merk.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
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
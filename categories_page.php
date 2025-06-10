<?php
// ayelectronic_store/categories_page.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';

// Ambil semua data kategori beserta jumlah produk di dalamnya
$sql = "SELECT c.id, c.nama_kategori, c.deskripsi, c.icon_class, COUNT(p.id) AS product_count 
        FROM categories c 
        LEFT JOIN products p ON c.id = p.category_id 
        GROUP BY c.id, c.nama_kategori, c.deskripsi, c.icon_class
        ORDER BY c.nama_kategori ASC";
$categories = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

?>
<title>Kategori - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Kategori']
    ]);
    ?>

    <div class="container my-5">
        <h1 class="text-center mb-5 section-heading">Jelajahi Kategori Produk Kami</h1>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 fade-in-on-scroll">
            <?php if (!empty($categories)): foreach ($categories as $category): ?>
                <div class="col">
                    <a href="category_page.php?id=<?= $category['id'] ?>" class="text-decoration-none">
                        <div class="card feature-card h-100">
                            <div class="card-body">
                                <div class="icon-circle mb-3"><i class="<?= htmlspecialchars($category['icon_class'] ?? 'bi-tags-fill') ?>"></i></div>
                                <h5 class="card-title"><?= htmlspecialchars($category['nama_kategori']) ?></h5>
                                <?php if(!empty($category['deskripsi'])): ?>
                                    <p class="card-text text-muted small"><?= htmlspecialchars(substr($category['deskripsi'], 0, 50)) . '' ?></p>
                                <?php endif; ?>
                                <p class="card-text text-muted small mt-auto pt-2 border-top">
                                    <?= $category['product_count'] ?> produk
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; else: ?>
                <div class="col-12">
                    <p class="text-center text-muted">Belum ada kategori yang tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
include 'includes/footer.php';
$conn->close();
ob_end_flush();
?>
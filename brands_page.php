<?php
// ayelectronic_store/brands_page.php

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';

// Ambil semua data merk beserta jumlah produk di dalamnya
$sql = "SELECT b.id, b.nama_brand, b.logo_brand, COUNT(p.id) AS product_count 
        FROM brands b 
        LEFT JOIN products p ON b.id = p.brand_id 
        GROUP BY b.id, b.nama_brand, b.logo_brand
        ORDER BY b.nama_brand ASC";
$brands = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

?>
<title>Semua Merk - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Semua Merk']
    ]);
    ?>

    <div class="container my-5">
        <h1 class="text-center mb-5 section-heading">Jelajahi Merk Favorit Anda</h1>

        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4 fade-in-on-scroll">
            <?php if (!empty($brands)): foreach ($brands as $brand): ?>
                <div class="col">
                    <a href="brand_page.php?id=<?= $brand['id'] ?>" class="text-decoration-none">
                        <div class="card feature-card h-100">
                            <div class="card-body">
                                <div class="icon-circle mb-3 d-flex justify-content-center align-items-center bg-white p-2">
                                    <?php
                                    $logo_src = '/ayelectronic_store/assets/img/placeholder_brand.png';
                                    if (!empty($brand['logo_brand']) && file_exists('./' . $brand['logo_brand'])) {
                                        $logo_src = '/ayelectronic_store/' . htmlspecialchars($brand['logo_brand']);
                                    }
                                    ?>
                                    <img src="<?= $logo_src ?>" alt="Logo <?= htmlspecialchars($brand['nama_brand']) ?>" style="max-width: 80%; max-height: 80%; object-fit: contain;">
                                </div>
                                <h5 class="card-title"><?= htmlspecialchars($brand['nama_brand']) ?></h5>
                                <p class="card-text text-muted small mt-auto pt-2 border-top">
                                    <?= $brand['product_count'] ?> produk
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            <?php endforeach; else: ?>
                <div class="col-12">
                    <p class="text-center text-muted">Belum ada merk yang tersedia.</p>
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
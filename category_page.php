<?php
// ayelectronic_store/category_page.php (Versi Final Lengkap & Stabil)

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

$category_id_page = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($category_id_page === 0) {
    header("Location: index.php");
    exit();
}

$stmt_cat_name = $conn->prepare("SELECT nama_kategori FROM categories WHERE id = ?");
$stmt_cat_name->bind_param("i", $category_id_page);
$stmt_cat_name->execute();
$category_result = $stmt_cat_name->get_result();
if ($category_result->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$category = $category_result->fetch_assoc();
$category_name = $category['nama_kategori'];

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';

// --- LOGIKA LENGKAP UNTUK FILTER, PENCARIAN, SORTING, DAN PAGINATION ---
$search = $_GET['search'] ?? '';
$brand_id = isset($_GET['brand']) && is_numeric($_GET['brand']) ? (int)$_GET['brand'] : '';
$sort = $_GET['sort'] ?? 'latest';

$sql_from_join = " FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id LEFT JOIN product_variants pv ON p.id = pv.product_id";
$where_clauses = ["p.category_id = ?"];
$bind_params = [$category_id_page];
$bind_types = 'i';
$url_params = ['id' => $category_id_page];

if (!empty($search)) {
    $where_clauses[] = "p.nama_produk LIKE ?";
    $bind_params[] = "%" . $search . "%";
    $bind_types .= 's';
    $url_params['search'] = $search;
}
if (!empty($brand_id)) {
    $where_clauses[] = "p.brand_id = ?";
    $bind_params[] = $brand_id;
    $bind_types .= 'i';
    $url_params['brand'] = $brand_id;
}
if ($sort !== 'latest') {
    $url_params['sort'] = $sort;
}

$sql_where = " WHERE " . implode(" AND ", $where_clauses);

$sql_count = "SELECT COUNT(DISTINCT p.id) as total " . $sql_from_join . $sql_where;
$stmt_count = $conn->prepare($sql_count);
if (!empty($bind_types)) { $stmt_count->bind_param($bind_types, ...$bind_params); }
$stmt_count->execute();
$total_products = $stmt_count->get_result()->fetch_assoc()['total'];

$limit = 12;
$total_pages = ceil($total_products / $limit);
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $total_pages));
$offset = ($page - 1) * $limit;

$sql_select = "SELECT p.id, p.nama_produk, p.gambar_utama, p.harga, c.nama_kategori, COALESCE(MIN(pv.harga_varian), p.harga) AS harga_final, COUNT(DISTINCT pv.id) AS variant_count";
$sql_group_by = " GROUP BY p.id, p.nama_produk, p.gambar_utama, c.nama_kategori, p.created_at, p.harga";

$sql_order_by = " ORDER BY p.created_at DESC";
switch ($sort) {
    case 'price_asc': $sql_order_by = " ORDER BY harga_final ASC"; break;
    case 'price_desc': $sql_order_by = " ORDER BY harga_final DESC"; break;
    case 'name_asc': $sql_order_by = " ORDER BY p.nama_produk ASC"; break;
}

// --- RINCIAN PERUBAHAN: Query SQL diperbaiki dengan menyertakan $sql_order_by ---
$sql_products = $sql_select . $sql_from_join . $sql_where . $sql_group_by . $sql_order_by . " LIMIT ? OFFSET ?";
    
$stmt = $conn->prepare($sql_products);
if ($stmt === false) { die("Query Gagal: " . $conn->error); }

$all_params = $bind_params;
$all_params[] = $limit;
$all_params[] = $offset;
$all_types = $bind_types . 'ii';

if (!empty($bind_types)) {
    $stmt->bind_param($all_types, ...$all_params);
} else {
    $stmt->bind_param('ii', $limit, $offset);
}
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$brands = $conn->query("SELECT id, nama_brand FROM brands ORDER BY nama_brand ASC")->fetch_all(MYSQLI_ASSOC);
$pagination_query_string = http_build_query($url_params);

?>
<title><?= htmlspecialchars($category_name) ?> - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php generate_breadcrumb([['text' => 'Beranda', 'url' => 'index.php'],['text' => 'Kategori', 'url' => 'categories_page.php'],['text' => $category_name]]); ?>

    <div class="container my-5">
        <h1 class="text-center mb-4 section-heading">Produk Kategori: <?= htmlspecialchars($category_name) ?></h1>

        <div class="card card-body mb-4 shadow-sm">
            <form action="category_page.php" method="GET">
                <input type="hidden" name="id" value="<?= $category_id_page ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-6">
                        <label for="search" class="form-label">Cari di Kategori ini</label>
                        <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-lg-2">
                        <label for="brand" class="form-label">Merk</label>
                        <select id="brand" name="brand" class="form-select">
                            <option value="">Semua</option>
                            <?php foreach ($brands as $brd): ?>
                                <option value="<?= $brd['id'] ?>" <?= ($brand_id == $brd['id']) ? 'selected' : '' ?>><?= htmlspecialchars($brd['nama_brand']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <label for="sort" class="form-label">Urutkan</label>
                        <select id="sort" name="sort" class="form-select">
                            <option value="latest" <?= ($sort == 'latest') ? 'selected' : '' ?>>Terbaru</option>
                            <option value="price_asc" <?= ($sort == 'price_asc') ? 'selected' : '' ?>>Harga Terendah</option>
                            <option value="price_desc" <?= ($sort == 'price_desc') ? 'selected' : '' ?>>Harga Tertinggi</option>
                            <option value="name_asc" <?= ($sort == 'name_asc') ? 'selected' : '' ?>>Nama A-Z</option>
                        </select>
                    </div>
                    <div class="col-lg-2">
                        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
                    </div>
                </div>
            </form>
        </div>

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-4">
            <?php if (!empty($products)): foreach ($products as $product): ?>
                <div class="col">
                    <div class="card product-card h-100">
                        <?php
                            $product_image_src = '/ayelectronic_store/' . htmlspecialchars($product['gambar_utama'] ?? 'assets/img/default_product.png');
                        ?>
                        <img src="<?= $product_image_src ?>" class="card-img-top" alt="<?= htmlspecialchars($product['nama_produk']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['nama_produk']) ?></h5>
                            <p class="card-text text-muted small"><?= htmlspecialchars($product['nama_kategori']) ?></p>
                            <p class="card-text price mt-auto">Mulai dari Rp<?= number_format($product['harga_final'] ?? 0, 0, ',', '.') ?></p>
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
            <?php endforeach; else: ?>
                <div class="col-12">
                    <p class="text-center text-muted fs-5 mt-5">Tidak ada produk yang cocok dengan kriteria Anda.</p>
                </div>
            <?php endif; ?>
        </div>

        <nav aria-label="Page navigation" class="mt-5">
            <ul class="pagination justify-content-center">
                <?php if ($total_pages > 1): ?>
                    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&<?= $pagination_query_string ?>">Previous</a>
                    </li>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&<?= $pagination_query_string ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : '' ?>">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&<?= $pagination_query_string ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
        
    </div>
</main>

<?php
include 'includes/footer.php';
$conn->close();
ob_end_flush();
?>
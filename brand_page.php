<?php
// ayelectronic_store/brand_page.php (Versi Final Lengkap)

session_start();
ob_start();

include_once 'config/database.php';
$conn = new mysqli($host, $user, $pass, $db_name);
if ($conn->connect_error) { die("Koneksi Gagal: " . $conn->connect_error); }

// Mengambil ID Merk dari URL
$brand_id_page = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
if ($brand_id_page === 0) {
    header("Location: index.php");
    exit();
}

// Ambil nama merk untuk judul halaman
$stmt_brand_name = $conn->prepare("SELECT nama_brand FROM brands WHERE id = ?");
$stmt_brand_name->bind_param("i", $brand_id_page);
$stmt_brand_name->execute();
$brand_result = $stmt_brand_name->get_result();
if ($brand_result->num_rows === 0) {
    header("Location: index.php");
    exit();
}
$brand_page = $brand_result->fetch_assoc();
$brand_name = $brand_page['nama_brand'];

$body_class = 'bg-light';
include_once 'includes/header.php';
include_once 'includes/helpers.php';

// --- Logika Filter, Pencarian, Sorting ---
$search = $_GET['search'] ?? '';
$category_id = isset($_GET['category']) && is_numeric($_GET['category']) ? (int)$_GET['category'] : '';
$sort = $_GET['sort'] ?? 'latest';

$sql_from_join = " FROM products p LEFT JOIN categories c ON p.category_id = c.id LEFT JOIN brands b ON p.brand_id = b.id LEFT JOIN product_variants pv ON p.id = pv.product_id";
$where_clauses = ["p.brand_id = ?"];
$bind_params = [$brand_id_page];
$bind_types = 'i';
$url_params = ['id' => $brand_id_page]; 

if (!empty($search)) {
    $where_clauses[] = "p.nama_produk LIKE ?";
    $bind_params[] = "%" . $search . "%";
    $bind_types .= 's';
    $url_params['search'] = $search;
}
if (!empty($category_id)) {
    $where_clauses[] = "p.category_id = ?";
    $bind_params[] = $category_id;
    $bind_types .= 'i';
    $url_params['category'] = $category_id;
}
if ($sort !== 'latest') {
    $url_params['sort'] = $sort;
}

$sql_where = " WHERE " . implode(" AND ", $where_clauses);

$sql_count = "SELECT COUNT(DISTINCT p.id) as total " . $sql_from_join . $sql_where;
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param($bind_types, ...$bind_params);
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
$sql_limit = " LIMIT ? OFFSET ?";
$sql_products = $sql_select . $sql_from_join . $sql_where . $sql_group_by . $sql_order_by . $sql_limit;
$stmt = $conn->prepare($sql_products);
$all_params = $bind_params;
$all_params[] = $limit;
$all_params[] = $offset;
$all_types = $bind_types . 'ii';
$stmt->bind_param($all_types, ...$all_params);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$categories = $conn->query("SELECT id, nama_kategori FROM categories ORDER BY nama_kategori ASC")->fetch_all(MYSQLI_ASSOC);
$pagination_query_string = http_build_query($url_params);

?>
<title><?= htmlspecialchars($brand_name) ?> - AYelectronic Store</title>

<main style="padding-top: 100px;">
    <?php
    generate_breadcrumb([
        ['text' => 'Beranda', 'url' => 'index.php'],
        ['text' => 'Merk', 'url' => 'brands_page.php'],
        ['text' => $brand_name]
    ]);
    ?>

    <div class="container my-5">
        <h1 class="text-center mb-4 section-heading">Produk Merk: <?= htmlspecialchars($brand_name) ?></h1>

        <div class="card card-body mb-4 shadow-sm">
            <form action="brand_page.php" method="GET">
                <input type="hidden" name="id" value="<?= $brand_id_page ?>">
                <div class="row g-2 align-items-end">
                    <div class="col-lg-6">
                        <label for="search" class="form-label">Cari di Merk ini</label>
                        <input type="text" class="form-control" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-lg-2">
                        <label for="category" class="form-label">Kategori</label>
                        <select id="category" name="category" class="form-select">
                            <option value="">Semua</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= $cat['id'] ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?>><?= htmlspecialchars($cat['nama_kategori']) ?></option>
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
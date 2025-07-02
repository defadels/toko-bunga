<?php
require_once 'config/database.php';

// Get category ID from URL
$category_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$category_id) {
    header('Location: categories.php');
    exit;
}

// Get category details
$category = getCategoryById($category_id);
if (!$category) {
    header('Location: categories.php');
    exit;
}

// Get search and filters
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';
$min_price = filter_input(INPUT_GET, 'min_price', FILTER_VALIDATE_INT);
$max_price = filter_input(INPUT_GET, 'max_price', FILTER_VALIDATE_INT);
$page = max(1, filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT) ?: 1);
$per_page = 12;

// Get products in this category
$where_conditions = ['category_id = ?'];
$params = [$category_id];

if ($search) {
    $where_conditions[] = 'name LIKE ?';
    $params[] = '%' . $search . '%';
}

if ($min_price) {
    $where_conditions[] = 'price >= ?';
    $params[] = $min_price;
}

if ($max_price) {
    $where_conditions[] = 'price <= ?';
    $params[] = $max_price;
}

switch($sort) {
    case 'price_low':
        $order_by = 'p.price ASC';
        break;
    case 'price_high':
        $order_by = 'p.price DESC';
        break;
    case 'name':
        $order_by = 'p.name ASC';
        break;
    case 'oldest':
        $order_by = 'p.created_at ASC';
        break;
    default:
        $order_by = 'p.created_at DESC';
        break;
}

// Get products and total count
$offset = ($page - 1) * $per_page;
$products = getProductsWithFilters($where_conditions, $params, $order_by, $per_page, $offset);
$total_products = getTotalProductsWithFilters($where_conditions, $params);
$total_pages = ceil($total_products / $per_page);

// Page info
$page_title = htmlspecialchars($category['name']) . ' - Kategori Produk';
$meta_description = 'Jelajahi koleksi ' . htmlspecialchars($category['name']) . ' terbaik di Toko Bunga Online. ' . htmlspecialchars($category['description'] ?? '');
$canonical_url = 'category.php?id=' . $category_id;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <meta name="description" content="<?php echo $meta_description; ?>">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <script>
        // Set global variables for JavaScript
        var userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
        var userRole = '<?php echo $_SESSION['role'] ?? ''; ?>';
    </script>
    
    <?php include 'includes/header.php'; ?>

<main class="main-content">
    <!-- Category Header -->
    <section class="category-header">
        <div class="container">
            <div class="category-header-content">
                <nav class="breadcrumb">
                    <a href="index.php">Beranda</a>
                    <span class="breadcrumb-separator">›</span>
                    <a href="categories.php">Kategori</a>
                    <span class="breadcrumb-separator">›</span>
                    <span class="breadcrumb-current"><?php echo htmlspecialchars($category['name']); ?></span>
                </nav>
                
                <div class="category-info">
                    <h1 class="category-title"><?php echo htmlspecialchars($category['name']); ?></h1>
                    
                    <?php if (!empty($category['description'])): ?>
                        <p class="category-description"><?php echo htmlspecialchars($category['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="category-stats">
                        <span class="product-count"><?php echo $total_products; ?> Produk Ditemukan</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search and Filters -->
    <section class="filters-section">
        <div class="container">
            <div class="filters-container">
                <form method="GET" action="" class="filters-form">
                    <input type="hidden" name="id" value="<?php echo $category_id; ?>">
                    
                    <div class="search-filter">
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Cari produk dalam kategori ini..." class="search-input">
                    </div>
                    
                    <div class="price-filter">
                        <label>Harga:</label>
                        <input type="number" name="min_price" value="<?php echo $min_price; ?>" 
                               placeholder="Min" class="price-input">
                        <span>-</span>
                        <input type="number" name="max_price" value="<?php echo $max_price; ?>" 
                               placeholder="Max" class="price-input">
                    </div>
                    
                    <div class="sort-filter">
                        <label for="sort">Urutkan:</label>
                        <select name="sort" id="sort" class="sort-select">
                            <option value="newest" <?php echo $sort === 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="oldest" <?php echo $sort === 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                            <option value="name" <?php echo $sort === 'name' ? 'selected' : ''; ?>>Nama A-Z</option>
                            <option value="price_low" <?php echo $sort === 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                            <option value="price_high" <?php echo $sort === 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                    <a href="category.php?id=<?php echo $category_id; ?>" class="btn btn-outline">Reset</a>
                </form>
            </div>
        </div>
    </section>

    <!-- Products Grid -->
    <section class="products-section">
        <div class="container">
            <?php if (!empty($products)): ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="product-link">
                                <div class="product-image">
                                    <?php if (!empty($product['image']) && file_exists('assets/images/products/' . $product['image'])): ?>
                                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                                             alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="product-placeholder">
                                            <span class="product-icon">🌹</span>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($product['stock'] < 5): ?>
                                        <span class="stock-badge low-stock">Stok Terbatas</span>
                                    <?php elseif ($product['stock'] == 0): ?>
                                        <span class="stock-badge out-of-stock">Habis</span>
                                    <?php endif; ?>
                                    
                                    <div class="product-actions">
                                        <button type="button" class="btn btn-primary add-to-cart-btn" 
                                                data-product-id="<?php echo $product['id']; ?>"
                                                <?php echo $product['stock'] == 0 ? 'disabled' : ''; ?>>
                                            🛒 Tambah ke Keranjang
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="product-info">
                                    <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                    
                                    <div class="product-price">
                                        <span class="current-price">Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></span>
                                    </div>
                                    
                                    <div class="product-stock">
                                        <span class="stock-text">Stok: <?php echo $product['stock']; ?></span>
                                    </div>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav class="pagination-nav">
                        <div class="pagination">
                            <?php 
                            $base_url = "category.php?id=" . $category_id;
                            if ($search) $base_url .= "&search=" . urlencode($search);
                            if ($sort !== 'newest') $base_url .= "&sort=" . $sort;
                            if ($min_price) $base_url .= "&min_price=" . $min_price;
                            if ($max_price) $base_url .= "&max_price=" . $max_price;
                            
                            // Previous page
                            if ($page > 1): ?>
                                <a href="<?php echo $base_url; ?>&page=<?php echo $page - 1; ?>" class="pagination-btn">‹ Sebelumnya</a>
                            <?php endif; ?>
                            
                            <!-- Page numbers -->
                            <?php
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++): ?>
                                <a href="<?php echo $base_url; ?>&page=<?php echo $i; ?>" 
                                   class="pagination-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            <?php endfor; ?>
                            
                            <!-- Next page -->
                            <?php if ($page < $total_pages): ?>
                                <a href="<?php echo $base_url; ?>&page=<?php echo $page + 1; ?>" class="pagination-btn">Selanjutnya ›</a>
                            <?php endif; ?>
                        </div>
                    </nav>
                <?php endif; ?>
                
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-icon">🔍</div>
                    <h3>Tidak Ada Produk Ditemukan</h3>
                    <p>Maaf, tidak ada produk yang sesuai dengan kriteria pencarian Anda.</p>
                    <div class="empty-actions">
                        <a href="category.php?id=<?php echo $category_id; ?>" class="btn btn-primary">Reset Filter</a>
                        <a href="categories.php" class="btn btn-outline">Lihat Kategori Lain</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
</body>
</html>
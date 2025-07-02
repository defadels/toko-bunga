<?php
require_once 'config/database.php';

$pdo = getConnection();

// Pagination
$page = (int)($_GET['page'] ?? 1);
$limit = 12;
$offset = ($page - 1) * $limit;

// Filters
$category_id = (int)($_GET['category'] ?? 0);
$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'newest';

// Build query
$where_conditions = ["p.is_active = 1", "c.is_active = 1"];
$params = [];

if ($category_id > 0) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category_id;
}

if (!empty($search)) {
    $where_conditions[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$where_clause = implode(' AND ', $where_conditions);

// Sort options
$sort_options = [
    'newest' => 'p.created_at DESC',
    'oldest' => 'p.created_at ASC', 
    'price_low' => 'p.price ASC',
    'price_high' => 'p.price DESC',
    'name_asc' => 'p.name ASC',
    'name_desc' => 'p.name DESC'
];

$order_by = $sort_options[$sort] ?? $sort_options['newest'];

// Get total count
$count_sql = "SELECT COUNT(*) as total FROM products p JOIN categories c ON p.category_id = c.id WHERE $where_clause";
$count_stmt = $pdo->prepare($count_sql);
$count_stmt->execute($params);
$total_products = $count_stmt->fetch()['total'];
$total_pages = ceil($total_products / $limit);

// Get products
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE $where_clause 
        ORDER BY $order_by 
        LIMIT $limit OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// Get categories for filter
$cat_stmt = $pdo->prepare("SELECT * FROM categories WHERE is_active = 1 ORDER BY name");
$cat_stmt->execute();
$categories = $cat_stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .products-container {
            padding: 2rem 0;
            min-height: 80vh;
        }
        
        .filters-section {
            background: #f8f9fa;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        
        .filters {
            display: flex;
            gap: 1rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .filter-select {
            padding: 8px 12px;
            border: 2px solid #ddd;
            border-radius: 6px;
            background: white;
        }
        
        .search-form {
            flex: 1;
            max-width: 400px;
        }
        
        .search-input {
            width: 100%;
            padding: 10px 16px;
            border: 2px solid #ddd;
            border-radius: 6px;
            font-size: 16px;
        }
        
        .products-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        .results-info {
            color: #666;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 3rem;
        }
        
        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #333;
            border-radius: 4px;
        }
        
        .pagination .current {
            background: #e91e63;
            color: white;
            border-color: #e91e63;
        }
        
        .pagination a:hover {
            background: #f5f5f5;
        }
        
        .no-products {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .filters {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form {
                max-width: none;
            }
            
            .products-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="filters-section">
        <div class="container">
            <form method="GET" class="filters">
                <div class="search-form">
                    <input type="text" name="search" class="search-input" 
                           placeholder="Cari produk..." 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="filter-group">
                    <label>Kategori:</label>
                    <select name="category" class="filter-select">
                        <option value="">Semua Kategori</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" 
                                    <?php echo $category_id == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>Urutkan:</label>
                    <select name="sort" class="filter-select">
                        <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Terbaru</option>
                        <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Terlama</option>
                        <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>Harga Terendah</option>
                        <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>Harga Tertinggi</option>
                        <option value="name_asc" <?php echo $sort == 'name_asc' ? 'selected' : ''; ?>>Nama A-Z</option>
                        <option value="name_desc" <?php echo $sort == 'name_desc' ? 'selected' : ''; ?>>Nama Z-A</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                
                <?php if ($category_id > 0 || !empty($search)): ?>
                    <a href="products.php" class="btn btn-outline">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="container products-container">
        <div class="products-header">
            <div class="results-info">
                Menampilkan <?php echo count($products); ?> dari <?php echo $total_products; ?> produk
                <?php if (!empty($search)): ?>
                    untuk pencarian "<?php echo htmlspecialchars($search); ?>"
                <?php endif; ?>
            </div>
        </div>

        <?php if (empty($products)): ?>
            <div class="no-products">
                <h3>Tidak ada produk ditemukan</h3>
                <p>Coba ubah kata kunci pencarian atau filter yang digunakan.</p>
                <a href="products.php" class="btn btn-primary">Lihat Semua Produk</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php foreach($products as $product): ?>
                <div class="product-card">
                    <div class="product-image">
                        <img src="assets/images/products/<?php echo htmlspecialchars($product['image']); ?>" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <div class="product-overlay">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Lihat Detail</a>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                        <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                        <p class="product-description"><?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...</p>
                        <div class="product-price"><?php echo formatRupiah($product['price']); ?></div>
                        <div class="product-actions">
                            <button class="btn btn-outline add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                Tambah ke Keranjang
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>">« Sebelumnya</a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <?php if ($i == $page): ?>
                        <span class="current"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($page < $total_pages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>">Selanjutnya »</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 
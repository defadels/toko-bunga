<div class="sidebar">
    <h2>🌺 Admin Panel</h2>
    <ul>
        <li><a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">📊 Dashboard</a></li>
        
        <li><a href="products.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">📦 Kelola Produk</a></li>
        <li><a href="products-add.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'products-add.php' ? 'active' : ''; ?>">➕ Tambah Produk</a></li>
        
        <li><a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">📂 Kelola Kategori</a></li>
        
        <li><a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : ''; ?>">🛒 Kelola Pesanan</a></li>
        
        <li><a href="customers.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : ''; ?>">👥 Data Pelanggan</a></li>
        
        <?php if (isAdmin()): ?>
        <li><a href="users.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">👤 Kelola Admin/Petugas</a></li>
        <?php endif; ?>
        
        <li><a href="reports.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">📈 Laporan Penjualan</a></li>
        
        <li style="margin-top: 2rem; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 1rem;">
            <a href="../index.php">🌐 Lihat Website</a>
        </li>
        <li><a href="../logout.php">🚪 Logout</a></li>
    </ul>
</div> 
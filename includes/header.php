<header class="header">
    <div class="container">
        <div class="header-content">
            <a href="index.php" class="logo">🌺 Toko Bunga</a>
            
            <nav>
                <ul class="nav-menu">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="products.php">Produk</a></li>
                    <li><a href="categories.php">Kategori</a></li>
                    <li><a href="about.php">Tentang Kami</a></li>
                    <li><a href="contact.php">Kontak</a></li>
                </ul>
            </nav>
            
            <div class="user-actions">
                <div class="search-box">
                    <input type="text" id="search-input" placeholder="Cari bunga..." autocomplete="off">
                    <button class="search-btn">🔍</button>
                    <div id="search-suggestions" class="search-suggestions" style="display: none;"></div>
                </div>
                
                <?php if(isLoggedIn()): ?>
                    <a href="cart.php" class="cart-icon">
                        🛒
                        <span class="cart-count" id="cart-count">0</span>
                    </a>
                    <div class="user-menu">
                        <span>Halo, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</span>
                        <a href="profile.php" class="btn btn-outline">Profil</a>
                        <a href="orders.php" class="btn btn-outline">Pesanan</a>
                        <?php if(isPetugas()): ?>
                            <a href="admin/" class="btn btn-secondary">Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-outline">Logout</a>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="register.php" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header> 
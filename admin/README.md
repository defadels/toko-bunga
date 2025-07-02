# Admin Dashboard - Toko Bunga

Panel administrasi untuk mengelola toko bunga online.

## 🔐 Akses Login

**Admin:**
- Username: `admin`
- Password: `password`

**Petugas:**
- Username: `petugas1` 
- Password: `password`

## 📁 Struktur Halaman

### 1. Dashboard (`dashboard.php`)
- Statistik overview
- Pesanan terbaru
- Alert stok menipis
- Metrics penjualan

### 2. Manajemen User (`users.php`)
- CRUD admin/petugas
- Role management
- Status aktivasi

### 3. Kategori (`categories.php`)
- CRUD kategori produk
- Upload gambar kategori
- Status aktif/nonaktif

### 4. Produk (`products.php`, `products-add.php`, `products-edit.php`)
- CRUD produk lengkap
- Manajemen stok
- Featured products
- Upload gambar

### 5. Pelanggan (`customers.php`)
- Data pelanggan
- Statistik pembelian
- Status aktivitas

### 6. Pesanan (`orders.php`, `orders-detail.php`)
- Kelola status pesanan:
  - Pending → Confirmed → Processing → Shipped → Delivered
  - Cancelled (bisa dari status manapun)
- Filter berdasarkan status & tanggal
- Detail lengkap pesanan

### 7. Laporan (`reports.php`)
- Penjualan berdasarkan periode
- Produk terlaris
- Statistik per hari
- Export data

## 🎨 Styling

Semua halaman menggunakan CSS konsisten dari:
- `assets/admin.css` - CSS khusus admin
- `../assets/css/style.css` - CSS utama aplikasi

## 🔧 Konfigurasi

- Database config: `../config/database.php`
- Sidebar navigation: `includes/sidebar.php`

## 📱 Responsive

Semua halaman admin telah dioptimasi untuk:
- Desktop (1200px+)
- Tablet (768px - 1199px) 
- Mobile (< 768px)

## 🛡️ Security

- Role-based access control
- Session management
- Input validation & sanitization
- SQL injection protection (prepared statements) 
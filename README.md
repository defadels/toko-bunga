# Toko Bunga Online

Aplikasi toko online bunga berbasis web yang dibangun menggunakan HTML5, CSS3, JavaScript (Vanilla), dan PHP dengan database MySQL/MariaDB. Aplikasi ini dibuat sesuai dengan standar HTML5 dan W3C validator.

## 🌸 Fitur Utama

### Frontend (Pelanggan)
- **Home Page** - Halaman utama dengan featured products dan kategori
- **Produk** - Daftar produk dengan fitur pencarian dan filter
- **Detail Produk** - Informasi lengkap produk
- **Kategori Produk** - Produk berdasarkan kategori
- **Tentang Kami** - Informasi perusahaan
- **Kontak** - Informasi kontak dan lokasi
- **Keranjang** - Manajemen keranjang belanja
- **Checkout** - Proses pemesanan
- **Daftar Pesanan** - Riwayat pesanan pelanggan
- **Cek Pesanan** - Status pesanan
- **Update Profil** - Ubah data pelanggan

### Backend (Admin/Petugas)
- **Dashboard** - Statistik dan overview toko
- **CRUD Admin/Petugas** - Kelola user admin dan petugas
- **CRUD Kategori Produk** - Manajemen kategori
- **Data Pelanggan** - Informasi pelanggan
- **CRUD Produk** - Manajemen produk
- **Kelola Pesanan** - Status pesanan (pending, proses, selesai, batal)
- **Detail Pesanan** - Informasi lengkap pesanan
- **Laporan Penjualan** - Report dan analitik

### Fitur Khusus
- ✅ **Live Search** - Pencarian real-time dengan suggestion
- ✅ **Responsive Design** - Tampilan mobile-friendly
- ✅ **CRUD Lengkap** - Create, Read, Update, Delete
- ✅ **User Authentication** - Login/register dengan role-based access
- ✅ **Shopping Cart** - Keranjang belanja terintegrasi
- ✅ **Order Management** - Manajemen pesanan lengkap

## 🛠️ Teknologi yang Digunakan

- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 7.4+
- **Database**: MySQL/MariaDB
- **Server**: Apache/Nginx (XAMPP, Laragon, WAMP, LAMP)

## 📋 Persyaratan Sistem

- PHP 7.4 atau lebih tinggi
- MySQL 5.7+ atau MariaDB 10.3+
- Apache/Nginx Web Server
- PDO Extension untuk PHP

## 🚀 Instalasi

### 1. Download/Clone Project
```bash
git clone [repository-url]
# atau download dan extract ke folder web server
```

### 2. Setup Database
1. Buat database baru dengan nama `toko_bunga`
2. Import file `database.sql` ke database:
   ```sql
   mysql -u root -p toko_bunga < database.sql
   ```
   Atau melalui phpMyAdmin:
   - Buka phpMyAdmin
   - Pilih database `toko_bunga`
   - Import file `database.sql`

### 3. Konfigurasi Database
Edit file `config/database.php` sesuai setting database Anda:
```php
define('DB_HOST', 'localhost');     // Host database
define('DB_NAME', 'toko_bunga');    // Nama database
define('DB_USER', 'root');          // Username database
define('DB_PASS', '');              // Password database
```

### 4. Setup Folder Images
Buat folder berikut untuk menyimpan gambar:
```
assets/
└── images/
    ├── products/
    ├── categories/
    └── hero-flowers.jpg
```

### 5. Set Permissions (Linux/Mac)
```bash
chmod 755 assets/images/products/
chmod 755 assets/images/categories/
```

### 6. Akses Aplikasi
- **Frontend**: `http://localhost/toko-bunga/`
- **Admin**: `http://localhost/toko-bunga/admin/`

## 👥 Default User Accounts

### Admin
- **Username**: `admin`
- **Password**: `password`
- **Role**: Administrator (full access)

### Petugas
- **Username**: `petugas1`
- **Password**: `password`
- **Role**: Petugas (limited admin access)

### Pelanggan
- **Username**: `pelanggan1`
- **Password**: `password`
- **Role**: Customer

> **Note**: Ganti password default setelah instalasi untuk keamanan.

## 📁 Struktur Folder

```
toko-bunga/
├── admin/                  # Admin panel
│   ├── includes/          # Admin includes
│   └── dashboard.php      # Admin dashboard
├── api/                   # API endpoints
│   ├── search.php         # Live search API
│   ├── cart.php           # Cart operations
│   └── cart-count.php     # Cart counter
├── assets/
│   ├── css/
│   │   └── style.css      # Main stylesheet
│   ├── js/
│   │   └── main.js        # JavaScript functions
│   └── images/            # Image uploads
├── config/
│   └── database.php       # Database configuration
├── includes/
│   ├── header.php         # Header component
│   └── footer.php         # Footer component
├── index.php              # Homepage
├── products.php           # Products listing
├── login.php              # Login page
├── register.php           # Registration page
├── cart.php               # Shopping cart
├── database.sql           # Database schema
└── README.md              # This file
```

## 🔧 Konfigurasi Tambahan

### Enable Error Reporting (Development)
Tambahkan di awal file PHP untuk debugging:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Upload Limits
Untuk upload gambar produk yang lebih besar, edit `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
max_execution_time = 300
```

## 📝 Cara Penggunaan

### Untuk Admin/Petugas:
1. Login ke `/admin/` dengan akun admin
2. Kelola kategori dan produk
3. Monitor pesanan masuk
4. Update status pesanan
5. Lihat laporan penjualan

### Untuk Pelanggan:
1. Browse produk di homepage
2. Gunakan fitur search dan filter
3. Tambahkan produk ke keranjang
4. Lakukan checkout
5. Track status pesanan

## 🎨 Validasi W3C

Aplikasi ini telah dirancang mengikuti standar HTML5. Untuk validasi:

1. Kunjungi [W3C Markup Validator](https://validator.w3.org/)
2. Masukkan URL halaman atau upload file HTML
3. Pastikan tidak ada error HTML

## 📊 Database Schema

### Tabel Utama:
- **users** - Data pengguna (admin, petugas, pelanggan)
- **categories** - Kategori produk
- **products** - Data produk bunga
- **orders** - Pesanan pelanggan
- **order_items** - Detail item pesanan
- **cart** - Keranjang belanja

### Relasi Database:
- One-to-many: categories → products
- One-to-many: users → orders
- One-to-many: orders → order_items
- Many-to-one: cart → users, products

## 🔒 Keamanan

- Password di-hash menggunakan `password_hash()`
- Prepared statements untuk mencegah SQL injection
- Session-based authentication
- Role-based access control
- Input validation dan sanitization

## 🚨 Troubleshooting

### Database Connection Error
- Periksa konfigurasi di `config/database.php`
- Pastikan MySQL/MariaDB running
- Verifikasi username/password database

### Images Not Loading
- Periksa path folder `assets/images/`
- Set permission yang benar
- Pastikan file gambar exist

### Live Search Not Working
- Periksa file `api/search.php`
- Cek JavaScript console untuk error
- Verifikasi AJAX request

## 📞 Support

Jika mengalami masalah:
1. Periksa log error di browser console
2. Cek PHP error log
3. Pastikan semua file ada dan permission benar
4. Verifikasi database connection

## 📄 Lisensi

Project ini dibuat untuk keperluan edukasi dan pembelajaran pengembangan web.

---

**Happy Coding! 🌸** 
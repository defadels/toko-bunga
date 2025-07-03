-- Database: toko_bunga
CREATE DATABASE IF NOT EXISTS toko_bunga;
USE toko_bunga;

-- Table: users (untuk admin, petugas, dan pelanggan)
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('admin', 'petugas', 'pelanggan') DEFAULT 'pelanggan',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: categories (kategori produk)
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table: products (produk bunga)
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(12,2) NOT NULL,
    stock INT DEFAULT 0,
    image VARCHAR(255),
    weight DECIMAL(8,2) DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Table: orders (pesanan)
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(12,2) NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    shipping_address TEXT NOT NULL,
    shipping_cost DECIMAL(10,2) DEFAULT 0,
    recipient_name VARCHAR(100) NOT NULL,
    recipient_phone VARCHAR(20) NOT NULL,
    city VARCHAR(50),
    postal_code VARCHAR(10),
    payment_method ENUM('transfer', 'cod', 'ewallet') NOT NULL,
    payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending',
    status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table: order_items (detail item pesanan)
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(12,2) NOT NULL,
    total DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Table: cart (keranjang belanja)
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Insert sample data
INSERT INTO users (username, email, password, full_name, phone, address, role) VALUES
('admin', 'admin@toko-bunga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', '081234567890', 'Jl. Admin No. 1', 'admin'),
('petugas1', 'petugas1@toko-bunga.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Petugas Satu', '081234567891', 'Jl. Petugas No. 1', 'petugas'),
('pelanggan1', 'pelanggan1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Santoso', '082345678901', 'Jl. Pelanggan No. 1', 'pelanggan'),
('pelanggan2', 'pelanggan2@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Siti Nurhaliza', '083456789012', 'Jl. Pelanggan No. 2', 'pelanggan'),
('pelanggan3', 'pelanggan3@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmad Rahman', '084567890123', 'Jl. Pelanggan No. 3', 'pelanggan');

INSERT INTO categories (name, description, image) VALUES
('Bunga Mawar', 'Koleksi bunga mawar segar untuk berbagai acara', 'mawar.jpg'),
('Bunga Tulip', 'Bunga tulip import berkualitas tinggi', 'tulip.jpg'),
('Bunga Anggrek', 'Anggrek exotic untuk dekorasi dan hadiah', 'anggrek.jpg'),
('Buket Pernikahan', 'Buket bunga spesial untuk hari pernikahan', 'wedding.jpg'),
('Bunga Papan', 'Bunga papan untuk ucapan dan belasungkawa', 'papan.jpg');

INSERT INTO products (category_id, name, description, price, stock, image, weight, is_featured) VALUES
(1, 'Mawar Merah Premium', 'Buket mawar merah premium dengan 12 tangkai bunga segar', 150000, 25, 'mawar-merah.jpg', 0.5, 1),
(1, 'Mawar Putih Elegant', 'Mawar putih elegant cocok untuk acara formal', 135000, 30, 'mawar-putih.jpg', 0.5, 1),
(1, 'Mawar Pink Romantis', 'Buket mawar pink untuk ungkapan cinta', 140000, 20, 'mawar-pink.jpg', 0.5, 0),
(2, 'Tulip Holland Orange', 'Tulip orange import langsung dari Holland', 200000, 15, 'tulip-orange.jpg', 0.3, 1),
(2, 'Tulip Multi Color', 'Tulip beragam warna dalam satu buket', 180000, 18, 'tulip-multi.jpg', 0.3, 0),
(3, 'Anggrek Bulan Putih', 'Anggrek bulan putih dalam pot cantik', 250000, 12, 'anggrek-putih.jpg', 1.0, 1),
(3, 'Anggrek Dendrobium', 'Anggrek dendrobium ungu yang eksotis', 220000, 10, 'anggrek-ungu.jpg', 1.0, 0),
(4, 'Buket Pengantin Klasik', 'Buket pengantin dengan mawar dan baby breath', 350000, 8, 'buket-pengantin.jpg', 0.8, 1),
(4, 'Buket Pengantin Modern', 'Buket pengantin modern dengan lily dan anggrek', 400000, 6, 'buket-modern.jpg', 0.8, 0),
(5, 'Bunga Papan Ucapan', 'Bunga papan untuk ucapan selamat', 500000, 5, 'papan-ucapan.jpg', 5.0, 0),
(5, 'Bunga Papan Duka Cita', 'Bunga papan untuk belasungkawa', 450000, 5, 'papan-duka.jpg', 5.0, 0),
(1, 'Mawar Rainbow', 'Mawar dengan warna pelangi yang unik', 175000, 15, 'mawar-rainbow.jpg', 0.5, 1);

INSERT INTO orders (user_id, order_number, total_amount, subtotal, shipping_address, shipping_cost, recipient_name, recipient_phone, city, postal_code, payment_method, payment_status, status) VALUES
(3, 'ORD-2024-001', 165000, 150000, 'Jl. Pelanggan No. 1, Jakarta', 15000, 'Budi Santoso', '082345678901', 'jakarta', '12345', 'transfer', 'paid', 'delivered'),
(4, 'ORD-2024-002', 355000, 350000, 'Jl. Pelanggan No. 2, Bandung', 20000, 'Siti Nurhaliza', '083456789012', 'bandung', '54321', 'cod', 'pending', 'shipped'),
(5, 'ORD-2024-003', 515000, 500000, 'Jl. Pelanggan No. 3, Surabaya', 25000, 'Ahmad Rahman', '084567890123', 'surabaya', '67890', 'ewallet', 'paid', 'processing'),
(3, 'ORD-2024-004', 195000, 200000, 'Jl. Pelanggan No. 1, Jakarta', 15000, 'Budi Santoso', '082345678901', 'jakarta', '12345', 'transfer', 'pending', 'confirmed'),
(4, 'ORD-2024-005', 270000, 250000, 'Jl. Pelanggan No. 2, Bandung', 20000, 'Siti Nurhaliza', '083456789012', 'bandung', '54321', 'transfer', 'pending', 'pending');

INSERT INTO order_items (order_id, product_id, quantity, price, total) VALUES
(1, 1, 1, 150000, 150000),
(2, 8, 1, 350000, 350000),
(3, 10, 1, 500000, 500000),
(4, 4, 1, 200000, 200000),
(5, 6, 1, 250000, 250000); 
-- Script untuk migrasi database tabel orders
-- Jalankan script ini jika Anda sudah memiliki tabel orders sebelumnya

-- Backup tabel orders lama (opsional)
-- CREATE TABLE orders_backup AS SELECT * FROM orders;

-- Tambahkan kolom baru ke tabel orders
ALTER TABLE orders 
ADD COLUMN IF NOT EXISTS subtotal DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER total_amount,
ADD COLUMN IF NOT EXISTS recipient_name VARCHAR(100) NOT NULL DEFAULT '' AFTER shipping_cost,
ADD COLUMN IF NOT EXISTS recipient_phone VARCHAR(20) NOT NULL DEFAULT '' AFTER recipient_name,
ADD COLUMN IF NOT EXISTS city VARCHAR(50) AFTER recipient_phone,
ADD COLUMN IF NOT EXISTS postal_code VARCHAR(10) AFTER city,
ADD COLUMN IF NOT EXISTS payment_status ENUM('pending', 'paid', 'failed') DEFAULT 'pending' AFTER payment_method;

-- Tambahkan kolom total ke tabel order_items
ALTER TABLE order_items 
ADD COLUMN IF NOT EXISTS total DECIMAL(12,2) NOT NULL DEFAULT 0 AFTER price;

-- Update data yang sudah ada
-- Set subtotal = total_amount - shipping_cost untuk data lama
UPDATE orders SET subtotal = total_amount - shipping_cost WHERE subtotal = 0;

-- Update total di order_items untuk data lama
UPDATE order_items SET total = price * quantity WHERE total = 0;

-- Update recipient info dari user data untuk pesanan yang sudah ada
UPDATE orders o 
JOIN users u ON o.user_id = u.id 
SET 
    o.recipient_name = u.full_name,
    o.recipient_phone = COALESCE(u.phone, ''),
    o.city = 'jakarta',
    o.postal_code = '12345'
WHERE o.recipient_name = '' OR o.recipient_name IS NULL;

-- Verifikasi hasil migrasi
SELECT 'Orders table structure:' as info;
DESCRIBE orders;

SELECT 'Order items table structure:' as info;
DESCRIBE order_items;

SELECT 'Sample data after migration:' as info;
SELECT id, order_number, recipient_name, recipient_phone, city, subtotal, shipping_cost, total_amount, payment_status 
FROM orders 
LIMIT 5; 
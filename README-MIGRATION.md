# Migrasi Database untuk Sistem Checkout

## 🚨 PENTING - Baca Sebelum Melanjutkan

Sistem checkout yang baru memerlukan beberapa perubahan pada struktur database. Ikuti langkah-langkah berikut untuk memperbarui database Anda.

## 📋 Perubahan yang Dilakukan

### 1. Tabel `orders` - Field Baru:
- `subtotal` - Subtotal sebelum ongkir
- `recipient_name` - Nama penerima paket
- `recipient_phone` - Telepon penerima 
- `city` - Kota tujuan pengiriman
- `postal_code` - Kode pos
- `payment_status` - Status pembayaran (pending/paid/failed)

### 2. Tabel `order_items` - Field Baru:
- `total` - Total per item (price × quantity)

## 🔧 Cara Melakukan Migrasi

### Pilihan 1: Database Baru (Recommended)
Jika Anda ingin memulai dengan database bersih:

```sql
-- Hapus database lama (hati-hati!)
DROP DATABASE IF EXISTS toko_bunga;

-- Import database baru
mysql -u root -p < database.sql
```

### Pilihan 2: Update Database Existing
Jika Anda ingin mempertahankan data yang sudah ada:

```sql
-- Backup database terlebih dahulu (WAJIB!)
mysqldump -u root -p toko_bunga > backup_toko_bunga.sql

-- Jalankan script migrasi
mysql -u root -p toko_bunga < database/migrate-orders.sql
```

## ⚠️ Backup Data

**SANGAT PENTING:** Selalu backup database sebelum melakukan migrasi!

```bash
# Backup database
mysqldump -u root -p toko_bunga > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore jika ada masalah
mysql -u root -p toko_bunga < backup_20241225_120000.sql
```

## 🧪 Testing Setelah Migrasi

1. **Cek struktur tabel:**
   ```sql
   DESCRIBE orders;
   DESCRIBE order_items;
   ```

2. **Cek data sample:**
   ```sql
   SELECT id, order_number, recipient_name, payment_status FROM orders LIMIT 5;
   ```

3. **Test fitur checkout:**
   - Login sebagai pelanggan
   - Tambah produk ke keranjang
   - Lakukan checkout
   - Cek apakah pesanan tersimpan dengan benar

## 🐛 Troubleshooting

### Error: Column already exists
Jika mendapat error kolom sudah ada, abaikan - script menggunakan `IF NOT EXISTS`.

### Error: Data truncated
Jika ada error data terpotong, periksa panjang data di field baru.

### Error: Foreign key constraint
Pastikan tidak ada data orphan di tabel terkait.

## 📱 Testing Manual

1. **Admin Panel:**
   - Buka `admin/orders.php`
   - Cek apakah kolom "Status Bayar" muncul
   - Test filter berdasarkan status pembayaran
   - Buka detail pesanan, pastikan info pengiriman lengkap

2. **Customer Portal:**
   - Login sebagai pelanggan
   - Tambah produk ke keranjang  
   - Klik checkout
   - Isi form dan submit
   - Cek halaman konfirmasi

## 🎯 Verifikasi Sukses

Migrasi berhasil jika:
- ✅ Halaman checkout dapat diakses tanpa error
- ✅ Form checkout dapat disubmit
- ✅ Pesanan tersimpan dengan data lengkap
- ✅ Admin dapat melihat detail pesanan
- ✅ Status pembayaran dapat diupdate

## 📞 Support

Jika mengalami masalah:
1. Cek error log PHP dan MySQL
2. Pastikan semua file telah diupload
3. Periksa konfigurasi database di `config/database.php`
4. Restore backup dan coba lagi

---

**Catatan:** Setelah migrasi berhasil, sistem checkout akan berfungsi penuh dengan fitur:
- Formulir pengiriman lengkap
- Multiple metode pembayaran  
- Tracking status pesanan dan pembayaran
- Instruksi pembayaran otomatis 
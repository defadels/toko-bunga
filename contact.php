<?php
require_once 'config/database.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Semua field wajib diisi kecuali nomor telepon.';
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } else {
        // Here you could save to database or send email
        // For now, we'll just show success message
        $success = 'Terima kasih! Pesan Anda telah dikirim. Kami akan merespon dalam 1x24 jam.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .contact-container {
            padding: 2rem 0;
        }
        
        .contact-hero {
            background: linear-gradient(135deg, #e8f5e8, #f1f8e9);
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .contact-hero h1 {
            font-size: 3rem;
            color: #2e7d32;
            margin-bottom: 1rem;
        }
        
        .contact-hero p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .contact-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 3rem;
        }
        
        .contact-form {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .contact-form h2 {
            color: #2e7d32;
            margin-bottom: 1.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #e91e63;
            box-shadow: 0 0 0 3px rgba(233, 30, 99, 0.1);
        }
        
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }
        
        .contact-info {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .contact-info h2 {
            color: #2e7d32;
            margin-bottom: 1.5rem;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .info-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            width: 40px;
            text-align: center;
        }
        
        .info-details h3 {
            color: #2e7d32;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }
        
        .info-details p {
            color: #666;
            margin: 0;
        }
        
        .social-links {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .social-link {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #e91e63, #f06292);
            color: white;
            text-decoration: none;
            border-radius: 50%;
            font-size: 1.2rem;
            transition: transform 0.3s ease;
        }
        
        .social-link:hover {
            transform: translateY(-3px);
        }
        
        .map-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .map-placeholder {
            width: 100%;
            height: 300px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 1.1rem;
            margin-top: 1rem;
        }
        
        @media (max-width: 768px) {
            .contact-content {
                grid-template-columns: 1fr;
            }
            
            .contact-hero h1 {
                font-size: 2rem;
            }
            
            .social-links {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="contact-hero">
        <div class="container">
            <h1>📞 Hubungi Kami</h1>
            <p>Kami siap membantu Anda dengan pertanyaan, saran, atau permintaan khusus untuk kebutuhan bunga Anda.</p>
        </div>
    </div>

    <div class="container contact-container">
        <div class="contact-content">
            <div class="contact-form">
                <h2>Kirim Pesan</h2>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nama Lengkap *</label>
                        <input type="text" id="name" name="name" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Nomor Telepon</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subjek *</label>
                        <select id="subject" name="subject" class="form-control" required>
                            <option value="">Pilih Subjek</option>
                            <option value="Pertanyaan Produk" <?php echo ($_POST['subject'] ?? '') == 'Pertanyaan Produk' ? 'selected' : ''; ?>>Pertanyaan Produk</option>
                            <option value="Custom Order" <?php echo ($_POST['subject'] ?? '') == 'Custom Order' ? 'selected' : ''; ?>>Custom Order</option>
                            <option value="Keluhan" <?php echo ($_POST['subject'] ?? '') == 'Keluhan' ? 'selected' : ''; ?>>Keluhan</option>
                            <option value="Saran" <?php echo ($_POST['subject'] ?? '') == 'Saran' ? 'selected' : ''; ?>>Saran</option>
                            <option value="Lainnya" <?php echo ($_POST['subject'] ?? '') == 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Pesan *</label>
                        <textarea id="message" name="message" class="form-control" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Kirim Pesan
                    </button>
                </form>
            </div>

            <div class="contact-info">
                <h2>Informasi Kontak</h2>
                
                <div class="info-item">
                    <div class="info-icon">📍</div>
                    <div class="info-details">
                        <h3>Alamat</h3>
                        <p>Jl. Bunga Raya No. 123<br>Jakarta Selatan 12345</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">📞</div>
                    <div class="info-details">
                        <h3>Telepon</h3>
                        <p>(021) 1234-5678<br>WhatsApp: 0812-3456-7890</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">📧</div>
                    <div class="info-details">
                        <h3>Email</h3>
                        <p>info@tokobunga.com<br>support@tokobunga.com</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">🕒</div>
                    <div class="info-details">
                        <h3>Jam Operasional</h3>
                        <p>Senin - Sabtu: 08:00 - 20:00<br>Minggu: 09:00 - 18:00</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#" class="social-link" title="Facebook">📘</a>
                    <a href="#" class="social-link" title="Instagram">📷</a>
                    <a href="#" class="social-link" title="Twitter">🐦</a>
                    <a href="#" class="social-link" title="WhatsApp">💬</a>
                </div>
            </div>
        </div>

        <div class="map-section">
            <h2>Lokasi Kami</h2>
            <p style="color: #666; margin-bottom: 1rem;">Kunjungi toko fisik kami untuk melihat langsung koleksi bunga segar</p>
            <div class="map-placeholder">
                🗺️ Peta Google Maps<br>
                <small>(Integrasi dengan Google Maps API)</small>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 
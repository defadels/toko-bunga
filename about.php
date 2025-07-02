<?php require_once 'config/database.php'; ?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Toko Bunga Online</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .about-container {
            padding: 2rem 0;
        }
        
        .about-hero {
            background: linear-gradient(135deg, #e8f5e8, #f1f8e9);
            padding: 4rem 0;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .about-hero h1 {
            font-size: 3rem;
            color: #2e7d32;
            margin-bottom: 1rem;
        }
        
        .about-hero p {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .about-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            margin-bottom: 4rem;
        }
        
        .about-text h2 {
            color: #2e7d32;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .about-text p {
            color: #666;
            line-height: 1.8;
            margin-bottom: 1rem;
        }
        
        .about-image img {
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .values-section {
            background: white;
            padding: 4rem 0;
            margin: 3rem 0;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }
        
        .value-card {
            text-align: center;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
            background: #f8f9fa;
        }
        
        .value-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .value-card h3 {
            color: #2e7d32;
            margin-bottom: 1rem;
        }
        
        .team-section {
            text-align: center;
            padding: 3rem 0;
        }
        
        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .team-member {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .team-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #e91e63, #f06292);
            margin: 0 auto 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
        }
        
        @media (max-width: 768px) {
            .about-content {
                grid-template-columns: 1fr;
                text-align: center;
            }
            
            .about-hero h1 {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="about-hero">
        <div class="container">
            <h1>🌸 Tentang Kami</h1>
            <p>Menyebarkan kebahagiaan melalui keindahan bunga segar berkualitas tinggi untuk setiap momen spesial dalam hidup Anda.</p>
        </div>
    </div>

    <div class="container about-container">
        <div class="about-content">
            <div class="about-text">
                <h2>Cerita Kami</h2>
                <p>
                    Toko Bunga Online didirikan dengan visi untuk menyebarkan kebahagiaan dan keindahan melalui 
                    bunga-bunga segar berkualitas tinggi. Sejak tahun 2020, kami telah melayani ribuan pelanggan 
                    di seluruh Indonesia dengan komitmen memberikan produk terbaik dan pelayanan yang memuaskan.
                </p>
                <p>
                    Kami memahami bahwa bunga bukan sekadar tanaman, tetapi simbol kasih sayang, ungkapan perasaan, 
                    dan cara untuk membuat momen spesial menjadi lebih berkesan. Oleh karena itu, kami selalu 
                    berusaha menghadirkan bunga-bunga terfresh langsung dari kebun pilihan.
                </p>
                <p>
                    Tim ahli kami terdiri dari florist berpengalaman yang memahami seni merangkai bunga dan 
                    selalu siap membantu Anda menciptakan arrangement yang sempurna untuk setiap acara.
                </p>
            </div>
            <div class="about-image">
                <img src="assets/images/about-us.jpg" alt="Tim Toko Bunga" 
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAwIiBoZWlnaHQ9IjMwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiBmaWxsPSIjZjhmOWZhIi8+PHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzY2NiIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZHk9Ii4zZW0iPkZvdG8gVGltIEthbWk8L3RleHQ+PC9zdmc+'">
            </div>
        </div>

        <div class="values-section">
            <div class="container">
                <h2 class="section-title">Nilai-Nilai Kami</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">🌸</div>
                        <h3>Kualitas Terjamin</h3>
                        <p>Kami hanya menjual bunga segar pilihan terbaik dengan standar kualitas tinggi yang telah teruji.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">❤️</div>
                        <h3>Pelayanan Tulus</h3>
                        <p>Setiap pelanggan adalah keluarga bagi kami. Kami melayani dengan sepenuh hati dan penuh perhatian.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">🚚</div>
                        <h3>Pengiriman Tepat Waktu</h3>
                        <p>Kami memahami pentingnya waktu. Pengiriman selalu tepat waktu dan dalam kondisi terbaik.</p>
                    </div>
                    
                    <div class="value-card">
                        <div class="value-icon">🎨</div>
                        <h3>Kreativitas Tinggi</h3>
                        <p>Tim florist kami selalu berinovasi menciptakan arrangement unik dan menarik sesuai keinginan Anda.</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="team-section">
            <div class="container">
                <h2 class="section-title">Tim Kami</h2>
                <p style="color: #666; margin-bottom: 2rem;">Bertemu dengan tim profesional yang siap melayani Anda</p>
                
                <div class="team-grid">
                    <div class="team-member">
                        <div class="team-avatar">🌺</div>
                        <h3>Sarah Putri</h3>
                        <p style="color: #e91e63; margin-bottom: 0.5rem;">Founder & CEO</p>
                        <p style="color: #666; font-size: 0.9rem;">10+ tahun pengalaman di industri bunga</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="team-avatar">🌹</div>
                        <h3>Ahmad Florist</h3>
                        <p style="color: #e91e63; margin-bottom: 0.5rem;">Head Florist</p>
                        <p style="color: #666; font-size: 0.9rem;">Spesialis wedding & event decoration</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="team-avatar">🌻</div>
                        <h3>Maya Customer Care</h3>
                        <p style="color: #e91e63; margin-bottom: 0.5rem;">Customer Service</p>
                        <p style="color: #666; font-size: 0.9rem;">Siap membantu 24/7 untuk Anda</p>
                    </div>
                    
                    <div class="team-member">
                        <div class="team-avatar">🌷</div>
                        <h3>Budi Delivery</h3>
                        <p style="color: #e91e63; margin-bottom: 0.5rem;">Delivery Manager</p>
                        <p style="color: #666; font-size: 0.9rem;">Memastikan pengiriman tepat waktu</p>
                    </div>
                </div>
            </div>
        </div>

        <div style="background: linear-gradient(135deg, #e8f5e8, #f1f8e9); padding: 3rem; border-radius: 15px; text-align: center;">
            <h2 style="color: #2e7d32; margin-bottom: 1rem;">Mari Berkreasi Bersama</h2>
            <p style="color: #666; margin-bottom: 2rem;">
                Apakah Anda memiliki ide arrangement khusus? Tim kami siap mewujudkan impian floral Anda!
            </p>
            <a href="contact.php" class="btn btn-primary">Hubungi Kami</a>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        const userLoggedIn = <?php echo isLoggedIn() ? 'true' : 'false'; ?>;
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html> 
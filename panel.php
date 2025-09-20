<?php
session_start();
require 'db.php';

function isLogin(){
    return isset($_SESSION['oturum']);
}

// Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
if(!isLogin()){
    header('Location: giris-yap.php');
    exit();
}

// Kullanıcı bilgileri
$kullanici_rol = $_SESSION['rol'] ?? 'kullanici';
$kullanici_adi = $_SESSION['ad'] ?? 'Misafir';
$kullanici_id = $_SESSION['user_id'] ?? 0;

// Sepetteki ürün sayısını al
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) as sepet_sayisi FROM sepet_yeni WHERE user_id = ?");
    $stmt->execute([$kullanici_id]);
    $sepet_sayisi = $stmt->fetch()['sepet_sayisi'];
} catch (PDOException $e) {
    $sepet_sayisi = 0;
}

// Toplam ürün sayısını al
try {
    $urun_count = $pdo->query("SELECT COUNT(*) FROM urunler")->fetchColumn();
} catch (PDOException $e) {
    $urun_count = 0;
}

// Son siparişleri al
try {
    $stmt = $pdo->prepare("SELECT * FROM siparisler_yeni WHERE user_id = ? ORDER BY siparis_tarihi DESC LIMIT 3");
    $stmt->execute([$kullanici_id]);
    $son_siparisler = $stmt->fetchAll();
} catch (PDOException $e) {
    $son_siparisler = [];
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Paneli - AFET KUTUSU</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .dashboard-page {
            background: linear-gradient(135deg, #d2e3fc 0%, #a8c7fa 50%, #1a73e8 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Header Styles */
        .header {
            background: linear-gradient(135deg, rgba(210, 227, 252, 0.95), rgba(255, 255, 255, 0.95));
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(26, 115, 232, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 1.6rem;
            font-weight: 800;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.1), rgba(255, 165, 100, 0.2));
            border: 2px solid transparent;
            background-clip: padding-box;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            min-width: fit-content;
            white-space: nowrap;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.6s ease;
        }

        .logo:hover::before {
            left: 100%;
        }

        .logo:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 25px rgba(255, 140, 66, 0.3);
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.15), rgba(255, 165, 100, 0.3));
        }

        .logo-text {
            color: #000000;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            letter-spacing: -0.5px;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: row;
            gap: 0.5rem;
            align-items: center;
            white-space: nowrap;
        }

        .logo i {
            font-size: 2.2rem;
            color: #dc3545;
            animation: heartbeat 2s ease-in-out infinite;
            filter: drop-shadow(0 2px 4px rgba(220, 53, 69, 0.3));
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 2rem;
            height: 100%;
        }

        .nav-links {
            display: flex;
            gap: 1.5rem;
            list-style: none;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .nav-link {
            color: var(--medium-gray);
            text-decoration: none;
            font-weight: 500;
            padding: 12px 20px;
            border-radius: 15px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #d2e3fc, #1a73e8);
            color: white;
            border: none;
            cursor: pointer;
            white-space: nowrap;
            min-width: fit-content;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 115, 232, 0.4);
            background: linear-gradient(135deg, #1a73e8, #1557b0);
            color: white;
            text-decoration: none;
        }

        .nav-link.active {
            color: white;
            background: linear-gradient(135deg, #1a73e8, #1557b0);
        }

        /* Çıkış butonu için özel stil */
        .nav-link[href="cikis-yap.php"] {
            background: rgba(255, 99, 99, 0.1);
            color: #ff6363;
            border: 1px solid rgba(255, 99, 99, 0.3);
        }

        .nav-link[href="cikis-yap.php"]:hover {
            background: rgba(255, 99, 99, 0.2);
            color: #ff4757;
            border: 1px solid rgba(255, 99, 99, 0.5);
            box-shadow: 0 8px 25px rgba(255, 99, 99, 0.4);
        }

        /* Admin Panel butonu için özel stil */
        .nav-link[href="admin-panel.php"] {
            border: 2px solid #000000;
        }

        .nav-link[href="admin-panel.php"]:hover {
            border: 2px solid #000000;
        }

        .dashboard-container {
            padding: 2rem 0;
        }

        .welcome-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .free-products-notice {
            background: linear-gradient(135deg, #e8f5e8, #c8e6c9);
            border: 2px solid #4caf50;
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: gentle-pulse 3s ease-in-out infinite;
            box-shadow: 0 4px 15px rgba(76, 175, 80, 0.2);
        }

        .free-products-notice i {
            color: #4caf50;
            font-size: 1.5rem;
            animation: bounce 2s ease-in-out infinite;
        }

        .free-products-notice span {
            color: #2e7d32;
            font-size: 1.1rem;
            line-height: 1.6;
        }

        @keyframes gentle-pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.02); }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
            40% { transform: translateY(-5px); }
            60% { transform: translateY(-3px); }
        }

        @media (max-width: 768px) {
            .free-products-notice {
                flex-direction: column;
                text-align: center;
                gap: 0.5rem;
            }
            
            .free-products-notice span {
                font-size: 1rem;
            }
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: linear-gradient(135deg, rgba(255, 200, 150, 0.6), rgba(255, 165, 100, 0.5));
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.2);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 140, 66, 0.3);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 25px rgba(255, 140, 66, 0.3);
            border-color: rgba(255, 140, 66, 0.4);
            background: linear-gradient(135deg, rgba(255, 200, 150, 0.7), rgba(255, 165, 100, 0.6));
        }

        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.2));
            transform: scale(1.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        /* Yeni Afet Hazırlık Kartları Stilleri */
        .stat-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 0.8rem;
            color: #1a252f;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .stat-description {
            font-size: 0.95rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            line-height: 1.5;
            font-weight: 500;
        }

        .stat-status {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(0, 0, 0, 0.1);
        }

        .emergency-kit .stat-status {
            color: #ffffff;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: 2px solid #e74c3c;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); }
            50% { box-shadow: 0 6px 20px rgba(231, 76, 60, 0.5); }
            100% { box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3); }
        }
        
        @keyframes pulse-orange {
            0% { box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3); }
            50% { box-shadow: 0 6px 20px rgba(243, 156, 18, 0.5); }
            100% { box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3); }
        }
        
        @keyframes pulse-blue {
            0% { box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3); }
            50% { box-shadow: 0 6px 20px rgba(52, 152, 219, 0.5); }
            100% { box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3); }
        }
        
        @keyframes pulse-green {
            0% { box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); }
            50% { box-shadow: 0 6px 20px rgba(39, 174, 96, 0.5); }
            100% { box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3); }
        }

        .water-storage .stat-status {
            color: #ffffff;
            background: linear-gradient(135deg, #f39c12, #e67e22);
            border: 2px solid #f39c12;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
            box-shadow: 0 4px 15px rgba(243, 156, 18, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: pulse-orange 2s infinite;
        }

        .communication .stat-status {
            color: #ffffff;
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: 2px solid #3498db;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: pulse-blue 2s infinite;
        }

        .safety-zone .stat-status {
            color: #ffffff;
            background: linear-gradient(135deg, #27ae60, #229954);
            border: 2px solid #27ae60;
            font-weight: 700;
            font-size: 1rem;
            padding: 0.8rem 1.5rem;
            box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            animation: pulse-green 2s infinite;
        }

        .stat-card:hover .stat-status {
            transform: scale(1.05);
        }

        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .stat-title {
                font-size: 1.1rem;
            }
            
            .stat-description {
                font-size: 0.9rem;
            }
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
            background: linear-gradient(135deg, #d2e3fc, #1a73e8);
            color: white;
            text-decoration: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(26, 115, 232, 0.2);
        }

        .action-btn:hover {
            background: linear-gradient(135deg, #1a73e8, #1557b0);
            color: white;
            text-decoration: none;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 115, 232, 0.4);
        }

        .action-btn i {
            margin-right: 0.5rem;
        }

        .recent-orders {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(210, 227, 252, 0.1));
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(26, 115, 232, 0.1);
            border: 1px solid rgba(26, 115, 232, 0.1);
        }

        .recent-orders h3 {
            color: #1a73e8;
            margin-bottom: 1rem;
        }

        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid rgba(26, 115, 232, 0.1);
            transition: all 0.3s ease;
        }

        .order-item:hover {
            background: rgba(210, 227, 252, 0.1);
            border-radius: 10px;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: bold;
        }

        .status-beklemede {
            background: linear-gradient(135deg, #d2e3fc, #1a73e8);
            color: white;
        }

        .status-hazirlaniyor {
            background: linear-gradient(135deg, #1a73e8, #1557b0);
            color: white;
        }

        .status-kargoda {
            background: #d4edda;
            color: #155724;
        }

        .status-teslim {
            background: #f8d7da;
            color: #721c24;
        }

        /* Deprem Rehberi Stilleri */
        .earthquake-guide {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(210, 227, 252, 0.1));
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(26, 115, 232, 0.1);
            border: 1px solid rgba(26, 115, 232, 0.1);
            margin-top: 2rem;
        }

        .earthquake-guide h3 {
            color: #1a73e8;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }

        .guide-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .guide-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #1a73e8;
            transition: all 0.3s ease;
        }

        .guide-section:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 115, 232, 0.15);
        }

        .guide-section h4 {
            color: #1a73e8;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .guide-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .guide-section li {
            padding: 0.5rem 0;
            border-bottom: 1px solid rgba(26, 115, 232, 0.1);
            position: relative;
            padding-left: 1.5rem;
        }

        .guide-section li:before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #1a73e8;
            font-weight: bold;
        }

        .guide-section li:last-child {
            border-bottom: none;
        }

        .emergency-numbers {
            grid-column: 1 / -1;
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #ff4757;
        }

        .emergency-numbers h4 {
            color: white;
            margin-bottom: 1rem;
        }

        .numbers-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 1rem;
        }

        .number-item {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
            transition: all 0.3s ease;
        }

        .number-item:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: scale(1.05);
        }

        .number-item strong {
            display: block;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }

        .number-item span {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .guide-content {
                grid-template-columns: 1fr;
            }
            
            .numbers-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body class="dashboard-page">
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <?php if($kullanici_rol === 'admin'): ?>
            <div class="logo">
                <i class="fas fa-heart"></i>
                <div class="logo-text">
                    <div>AFET</div>
                    <div>KUTUSU</div>
                </div>
            </div>
            <?php else: ?>
            <a href="index.php" class="logo">
                <i class="fas fa-heart"></i>
                <div class="logo-text">
                    <div>AFET</div>
                    <div>KUTUSU</div>
                </div>
            </a>
            <?php endif; ?>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="panel.php" class="nav-link active"><i class="fas fa-tachometer-alt"></i> <?php echo ($kullanici_rol === 'admin') ? 'Ana Sayfa' : 'Panel'; ?></a></li>
                    <li><a href="urunler.php" class="nav-link"><i class="fas fa-box"></i> Ürünler</a></li>
                    <?php if($kullanici_rol !== 'admin'): ?>
                    <li><a href="sepet.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Sepet</a></li>
                    <li><a href="siparisler.php" class="nav-link"><i class="fas fa-list"></i> Siparişlerim</a></li>
                    <?php endif; ?>
                    <li><a href="profil.php" class="nav-link"><i class="fas fa-user"></i> Profil</a></li>
                    <?php if($kullanici_rol === 'admin'): ?>
                    <li><a href="admin-panel.php" class="nav-link"><i class="fas fa-cog"></i> Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="cikis-yap.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Dashboard Content -->
    <div class="dashboard-container">
        <div class="container">
            <!-- Welcome Section -->
            <div class="welcome-section">
                <h1>Hoş Geldiniz!</h1>
                <p>Afet durumlarına hazırlıklı olmak için gerekli ürünleri inceleyebilir ve sipariş verebilirsiniz.</p>
                <div class="free-products-notice">
                    <i class="fa fa-gift"></i>
                    <span>🎉 <strong>Müjde!</strong> Tüm afet hazırlık ürünlerimiz tamamen <strong>ücretsizdir</strong>. Toplumsal dayanışma ruhuyla, herkesin afet durumlarına hazırlıklı olması için bu hizmeti sunuyoruz. Güvenliğiniz bizim önceliğimizdir! 💙</span>
                </div>
            </div>

            <!-- Afet Hazırlık Kartları -->
            <div class="stats-grid">
                <div class="stat-card emergency-kit">
                    <div class="stat-icon" style="color: #ff6b6b;">
                        <i class="fa fa-medkit"></i>
                    </div>
                    <div class="stat-title">🏥 Acil Durum Çantası</div>
                    <div class="stat-description">72 saatlik temel ihtiyaçlarınızı karşılayacak malzemeler</div>
                    <div class="stat-status">
                        <i class="fa fa-check-circle"></i>
                        Hazırlanmalı
                    </div>
                </div>
                
                <div class="stat-card water-storage">
                    <div class="stat-icon" style="color: #4ecdc4;">
                        <i class="fa fa-tint"></i>
                    </div>
                    <div class="stat-title">💧 Su Rezervi</div>
                    <div class="stat-description">Kişi başı günlük 4 litre su depolanması önerilir</div>
                    <div class="stat-status">
                        <i class="fa fa-exclamation-triangle"></i>
                        Kritik Öncelik
                    </div>
                </div>
                
                <div class="stat-card communication">
                    <div class="stat-icon" style="color: #45b7d1;">
                        <i class="fa fa-phone"></i>
                    </div>
                    <div class="stat-title">📞 İletişim Planı</div>
                    <div class="stat-description">Aile üyeleriyle buluşma noktası ve iletişim bilgileri</div>
                    <div class="stat-status">
                        <i class="fa fa-users"></i>
                        Aile Planı
                    </div>
                </div>
                
                <div class="stat-card safety-zone">
                    <div class="stat-icon" style="color: #96ceb4;">
                        <i class="fa fa-shield-alt"></i>
                    </div>
                    <div class="stat-title">🛡️ Güvenli Alanlar</div>
                    <div class="stat-description">Evinizdeki ve çevrenizdeki güvenli noktaları belirleyin</div>
                    <div class="stat-status">
                        <i class="fa fa-map-marker-alt"></i>
                        Konum Belirleme
                    </div>
                </div>
            </div>



            <!-- Deprem Rehberi -->
            <div class="earthquake-guide">
                <h3><i class="fa fa-exclamation-triangle"></i> Deprem Rehberi</h3>
                <div class="guide-content">
                    <div class="guide-section">
                        <h4><i class="fa fa-clock"></i> Deprem Öncesi Hazırlık</h4>
                        <ul>
                            <li>Acil durum çantası hazırlayın (su, yiyecek, ilk yardım malzemeleri)</li>
                            <li>Evinizdeki güvenli noktaları belirleyin</li>
                            <li>Aile üyeleriyle buluşma noktası kararlaştırın</li>
                            <li>Acil durum iletişim numaralarını kaydedin</li>
                        </ul>
                    </div>
                    
                    <div class="guide-section">
                        <h4><i class="fa fa-shield"></i> Deprem Anında</h4>
                        <ul>
                            <li><strong>Çök, Kapan, Tutun</strong> - Masanın altına saklanın</li>
                            <li>Cam ve ağır eşyalardan uzak durun</li>
                            <li>Asansör kullanmayın</li>
                            <li>Sakin kalın ve panik yapmayın</li>
                        </ul>
                    </div>
                    
                    <div class="guide-section">
                        <h4><i class="fa fa-heart"></i> Deprem Sonrası</h4>
                        <ul>
                            <li>Yaralıları kontrol edin ve ilk yardım uygulayın</li>
                            <li>Gaz, elektrik ve su vanalarını kontrol edin</li>
                            <li>Hasar tespiti yapın</li>
                            <li>Resmi kaynaklardan bilgi alın</li>
                        </ul>
                    </div>
                    
                    <div class="emergency-numbers">
                        <h4><i class="fa fa-phone"></i> Acil Durum Numaraları</h4>
                        <div class="numbers-grid">
                            <div class="number-item">
                                <strong>112</strong>
                                <span>Acil Çağrı</span>
                            </div>
                            <div class="number-item">
                                <strong>110</strong>
                                <span>İtfaiye</span>
                            </div>
                            <div class="number-item">
                                <strong>155</strong>
                                <span>Polis</span>
                            </div>
                            <div class="number-item">
                                <strong>156</strong>
                                <span>Jandarma</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
</body>
</html>
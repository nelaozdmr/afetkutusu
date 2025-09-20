<?php
session_start();
include 'db.php';

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['oturum'])) {
    header('Location: giris-yap.php');
    exit();
}

// Kullanıcı bilgileri
$kullanici_rol = $_SESSION['rol'] ?? 'kullanici';
$kullanici_adi = $_SESSION['ad'] ?? 'Misafir';
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['kullanici_id']) ? $_SESSION['kullanici_id'] : 1);

// Sepete ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urun_id'])) {
    $urun_id = intval($_POST['urun_id']);
    
    // Sepette zaten varsa miktar artır, yoksa yeni kayıt ekle
    $stmt = $pdo->prepare("SELECT * FROM sepet_yeni WHERE user_id = ? AND urun_id = ?");
    $stmt->execute([$user_id, $urun_id]);
    $varmi = $stmt->fetch();
    
    if ($varmi) {
        // Miktar artır
        $stmt = $pdo->prepare("UPDATE sepet_yeni SET miktar = miktar + 1 WHERE id = ?");
        $stmt->execute([$varmi['id']]);
    } else {
        // Yeni kayıt
        $stmt = $pdo->prepare("INSERT INTO sepet_yeni (user_id, urun_id, miktar) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $urun_id]);
    }
    
    // Başarı mesajı için session kullan
    $_SESSION['sepet_mesaj'] = 'Ürün sepete eklendi!';
    header("Location: urunler.php");
    exit;
}

// Veritabanından ürünleri çek
$stmt = $pdo->query("SELECT * FROM urunler ORDER BY id DESC");
$urunler = $stmt->fetchAll();

// Sepetteki ürün sayısını al
$stmt = $pdo->prepare("SELECT COUNT(*) as sepet_sayisi FROM sepet_yeni WHERE user_id = ?");
$stmt->execute([$user_id]);
$sepet_sayisi = $stmt->fetch()['sepet_sayisi'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ürünlerimiz - AFET KUTUSU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-blue: #1a73e8;
            --light-blue: #d2e3fc;
            --dark-blue: #1557b0;
            --success-green: #10b981;
            --warning-orange: #f59e0b;
            --danger-red: #ef4444;
            --light-gray: #f8fafc;
            --medium-gray: #64748b;
            --dark-gray: #334155;
            --white: #ffffff;
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #d2e3fc 0%, #a8c7fa 50%, #1a73e8 100%);
            min-height: 100vh;
            color: #2c3e50;
            line-height: 1.6;
        }

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
            flex-wrap: nowrap;
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
            border-color: rgba(255, 99, 99, 0.5);
            color: #dc2626;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .nav-links {
                gap: 1rem;
            }
            
            .nav-link {
                padding: 10px 16px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 768px) {
            .nav-links {
                gap: 0.5rem;
            }
            
            .nav-link {
                padding: 6px 8px;
                font-size: 0.8rem;
            }
            
            .nav-link i {
                font-size: 0.8rem;
            }
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.9), rgba(210, 227, 252, 0.3));
            backdrop-filter: blur(10px);
            padding: 3rem 0;
            margin-bottom: 2rem;
        }

        @media (max-width: 1024px) {
            .header-content {
                padding: 0 1rem;
            }
            
            .nav-links {
                gap: 0.5rem;
            }
            
            .nav-link {
                padding: 8px 12px;
                font-size: 0.9rem;
                gap: 6px;
            }
        }

        @media (max-width: 768px) {
            .logo-text {
                font-size: 1.2rem;
            }
            
            .nav-links {
                gap: 0.3rem;
            }
            
            .nav-link {
                padding: 6px 8px;
                font-size: 0.8rem;
                gap: 4px;
            }
            
            .nav-link i {
                font-size: 0.8rem;
            }
        }

        .page-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 20px;
        }

        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 2rem;
        }

        .header-left {
            flex: 1;
        }

        .header-right {
            flex-shrink: 0;
        }

        .page-title {
            display: flex;
            align-items: center;
            gap: 15px;
            color: #0355cc;
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .btn-add-product {
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            padding: 12px 24px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            border: none;
            cursor: pointer;
        }

        .btn-add-product:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-add-product i {
            font-size: 1rem;
        }

        /* Filtreleme Bölümü */
        .filters-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            display: flex;
            gap: 20px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            font-size: 1rem;
        }

        .search-box input {
            width: 100%;
            padding: 12px 15px 12px 45px;
            border: 2px solid rgba(26, 115, 232, 0.1);
            border-radius: 15px;
            font-size: 1rem;
            background: rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: #1a73e8;
            background: white;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
        }

        .filter-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 20px;
            border: 2px solid rgba(26, 115, 232, 0.2);
            background: rgba(255, 255, 255, 0.8);
            color: #1a73e8;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .filter-btn:hover {
            background: rgba(26, 115, 232, 0.1);
            transform: translateY(-2px);
        }

        .filter-btn.active {
            background: #1a73e8;
            color: white;
            border-color: #1a73e8;
        }

        .product-stats {
            display: flex;
            gap: 15px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 15px;
            background: rgba(26, 115, 232, 0.1);
            border-radius: 12px;
            color: #1a73e8;
            font-weight: 600;
        }

        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }

        .product-card {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            transition: all 0.4s ease;
            position: relative;
            border: 1px solid rgba(26,115,232,0.08);
            display: flex;
            flex-direction: column;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
            border-color: rgba(26,115,232,0.2);
        }

        .product-icon-container {
            position: relative;
            height: 250px;
            overflow: hidden;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-icon {
            font-size: 4rem;
            transition: all 0.4s ease;
            opacity: 0.8;
        }

        .product-card:hover .product-icon {
            transform: scale(1.1);
            opacity: 1;
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: white;
            color: #333333;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 200, 81, 0.4);
            border: 2px solid #00c851;
            display: flex;
            align-items: center;
            gap: 5px;
            animation: pulse-green 2s infinite;
        }

        .product-badge i {
            color: #333333 !important;
        }

        .product-badge * {
            color: #333333 !important;
        }

        @keyframes pulse-green {
            0% {
                box-shadow: 0 4px 15px rgba(0, 200, 81, 0.4);
            }
            50% {
                box-shadow: 0 6px 20px rgba(0, 255, 65, 0.6);
            }
            100% {
                box-shadow: 0 4px 15px rgba(0, 200, 81, 0.4);
            }
        }

        .product-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(26, 115, 232, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .product-card:hover .product-overlay {
            opacity: 1;
        }

        .btn-quick-view {
            background: white;
            color: #1a73e8;
            border: none;
            padding: 15px;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .btn-quick-view:hover {
            transform: scale(1.1);
            background: #1a73e8;
            color: white;
        }

        .product-content {
            padding: 25px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .product-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        .product-description {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
            font-size: 1rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            flex: 1;
        }

        .product-meta {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .product-stock {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .stock-available {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            border: 1px solid rgba(40, 167, 69, 0.2);
        }

        .product-category {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 600;
            background: rgba(26, 115, 232, 0.1);
            color: #1a73e8;
            border: 1px solid rgba(26, 115, 232, 0.2);
        }





        .success-message {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                gap: 1rem;
                padding: 0 1rem;
            }

            .main-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }

            .nav-link {
                padding: 8px 15px;
                font-size: 0.875rem;
            }

            .page-container {
                padding: 20px 15px;
            }

            .page-header {
                padding: 20px;
            }

            .header-top {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .page-title {
                font-size: 1.8rem;
            }

            .filters-section {
                flex-direction: column;
                gap: 15px;
                padding: 20px;
            }

            .search-box {
                min-width: auto;
                width: 100%;
            }

            .filter-buttons {
                justify-content: center;
            }

            .filter-btn {
                padding: 8px 15px;
                font-size: 0.9rem;
            }

            .products-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .product-content {
                padding: 20px;
            }

            .product-title {
                font-size: 1.2rem;
            }

            .product-actions {
                flex-direction: column;
                gap: 10px;
            }

            .btn-view-details {
                width: 100%;
                height: 45px;
            }
        }

        @media (max-width: 480px) {
            .page-title {
                font-size: 1.5rem;
            }

            .product-image-container {
                height: 200px;
            }

            .product-content {
                padding: 15px;
            }

            .filter-buttons {
                grid-template-columns: repeat(2, 1fr);
                display: grid;
                gap: 8px;
            }

            .filter-btn {
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <?php if ($kullanici_rol === 'admin'): ?>
                <div class="logo">
                    <i class="fas fa-heart" style="color: #dc3545;"></i>
                    <div class="logo-text">
                        <div>AFET</div>
                        <div>KUTUSU</div>
                    </div>
                </div>
            <?php else: ?>
                <a href="index.php" class="logo">
                    <i class="fas fa-heart" style="color: #dc3545;"></i>
                    <div class="logo-text">
                        <div>AFET</div>
                        <div>KUTUSU</div>
                    </div>
                </a>
            <?php endif; ?>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="panel.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> <?php echo ($kullanici_rol === 'admin') ? 'Ana Sayfa' : 'Panel'; ?></a></li>
                    <li><a href="urunler.php" class="nav-link active"><i class="fas fa-box"></i> Ürünler</a></li>
                    <?php if ($kullanici_rol !== 'admin'): ?>
                    <li><a href="sepet.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Sepet</a></li>
                    <li><a href="siparisler.php" class="nav-link"><i class="fas fa-list"></i> Siparişlerim</a></li>
                    <?php endif; ?>
                    <li><a href="profil.php" class="nav-link"><i class="fas fa-user"></i> Profil</a></li>
                    <?php if ($kullanici_rol === 'admin'): ?>
                        <li><a href="admin-panel.php" class="nav-link"><i class="fas fa-cog"></i> Admin Panel</a></li>
                    <?php endif; ?>
                    <li><a href="cikis-yap.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-container">




        <!-- Başarı Mesajı -->
        <?php if (isset($_SESSION['sepet_mesaj'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['sepet_mesaj'] ?>
            </div>
            <?php unset($_SESSION['sepet_mesaj']); ?>
        <?php endif; ?>

        <!-- Ürünler Grid -->
         <?php if (count($urunler) > 0): ?>
             <div class="products-grid" id="productsGrid">
                 <?php foreach ($urunler as $urun): ?>
                     <div class="product-card" data-category="<?= strtolower(htmlspecialchars($urun['kategori'] ?? 'genel')) ?>">
                         <div class="product-icon-container">
                             <?php
                             // Ürün adına göre simge belirleme
                             $icon = 'fas fa-box'; // varsayılan
                             $iconColor = '#007bff'; // varsayılan mavi
                             
                             if (stripos($urun['urun_adi'], 'gıda') !== false || stripos($urun['urun_adi'], 'yiyecek') !== false) {
                                 $icon = 'fas fa-utensils';
                                 $iconColor = '#28a745';
                             } elseif (stripos($urun['urun_adi'], 'su') !== false) {
                                 $icon = 'fas fa-tint';
                                 $iconColor = '#17a2b8';
                             } elseif (stripos($urun['urun_adi'], 'battaniye') !== false) {
                                 $icon = 'fas fa-bed';
                                 $iconColor = '#6f42c1';
                             } elseif (stripos($urun['urun_adi'], 'çadır') !== false) {
                                 $icon = 'fas fa-campground';
                                 $iconColor = '#fd7e14';
                             } elseif (stripos($urun['urun_adi'], 'fener') !== false) {
                                 $icon = 'fas fa-bolt';
                                 $iconColor = '#ffc107';
                             } elseif (stripos($urun['urun_adi'], 'hijyen') !== false) {
                                 $icon = 'fas fa-soap';
                                 $iconColor = '#20c997';
                             } elseif (stripos($urun['urun_adi'], 'ısıtıcı') !== false) {
                                 $icon = 'fas fa-fire';
                                 $iconColor = '#dc3545';
                             } elseif (stripos($urun['urun_adi'], 'yağmurluk') !== false) {
                                 $icon = 'fas fa-tshirt';
                                 $iconColor = '#6c757d';
                             } elseif (stripos($urun['urun_adi'], 'düdük') !== false) {
                                 $icon = 'fas fa-bullhorn';
                                 $iconColor = '#e83e8c';
                             } elseif (stripos($urun['urun_adi'], 'ayakkabı') !== false) {
                                 $icon = 'fas fa-shoe-prints';
                                 $iconColor = '#8B4513';
                             } elseif (stripos($urun['urun_adi'], 'çorap') !== false) {
                                 $icon = 'fas fa-socks';
                                 $iconColor = '#FF6347';
                             } elseif (stripos($urun['urun_adi'], 'mont') !== false) {
                                 $icon = 'fas fa-user-tie';
                                 $iconColor = '#2E8B57';
                             } elseif (stripos($urun['urun_adi'], 'pantolon') !== false) {
                                 $icon = 'fas fa-tshirt';
                                 $iconColor = '#4169E1';
                             }
                             ?>
                             <div class="product-icon" style="color: <?= $iconColor ?>">
                                 <i class="<?= $icon ?>"></i>
                             </div>
                             <div class="product-badge" style="color: #333333 !important;">
                                 <i class="fas fa-gift" style="color: #333333 !important;"></i> 
                                 <span style="color: #333333 !important;">ÜCRETSİZ</span>
                             </div>
                             <div class="product-overlay">
                                 <button class="btn-quick-view" onclick="showProductDetails(<?= $urun['id'] ?>)">
                                     <i class="fas fa-eye"></i>
                                 </button>
                             </div>
                         </div>
                         
                         <div class="product-content">
                             <h3 class="product-title"><?= htmlspecialchars($urun['urun_adi']) ?></h3>
                             <p class="product-description"><?= htmlspecialchars($urun['urun_aciklama']) ?></p>
                             
                             <div class="product-meta">
                                 <span class="product-stock stock-available">
                                     <i class="fas fa-check-circle"></i> Stokta Var
                                 </span>
                                 <span class="product-category">
                                     <i class="fas fa-tag"></i> <?= ucfirst(htmlspecialchars($urun['kategori'] ?? 'Genel')) ?>
                                 </span>
                             </div>
                             
                             <div class="product-actions">
                                 <form method="POST" action="sepet.php" class="add-to-cart-form">
                                     <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">
                                     <input type="hidden" name="miktar" value="1">
                                     <button type="submit" class="btn-add-cart">
                                         <i class="fas fa-shopping-cart"></i>
                                         <span>Sepete Ekle</span>
                                     </button>
                                 </form>
                             </div>

                         </div>
                     </div>
                 <?php endforeach; ?>
             </div>
         <?php else: ?>
             <div style="text-align: center; padding: 60px 20px; background: rgba(255,255,255,0.95); border-radius: 20px; backdrop-filter: blur(20px);">
                 <i class="fas fa-cube" style="font-size: 4rem; color: #ccc; margin-bottom: 20px;"></i>
                 <h3 style="color: #666; margin-bottom: 10px;">Henüz Ürün Eklenmedi</h3>
                 <p style="color: #999;">Şu anda sistemde kayıtlı ürün bulunmamaktadır.</p>
             </div>
         <?php endif; ?>
        </div>
    </div>



    <!-- Scripts -->
    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        function showProductDetails(urunId) {
            // Ürün detay sayfasına yönlendir
            window.location.href = 'urun-detay.php?id=' + urunId;
        }

        // Filtreleme ve arama işlevselliği
        function filterProducts() {
            const searchInput = document.getElementById('searchInput');
            const searchTerm = searchInput.value.toLowerCase();
            const activeFilter = document.querySelector('.filter-btn.active').textContent.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(card => {
                const title = card.querySelector('.product-title').textContent.toLowerCase();
                const description = card.querySelector('.product-description').textContent.toLowerCase();
                const category = card.dataset.category;
                
                const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                const matchesCategory = activeFilter === 'tümü' || category.includes(activeFilter.replace('ı', 'i'));
                
                if (matchesSearch && matchesCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function filterByCategory(category) {
            // Aktif buton stilini güncelle
            document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            const productCards = document.querySelectorAll('.product-card');
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            
            productCards.forEach(card => {
                const title = card.querySelector('.product-title').textContent.toLowerCase();
                const description = card.querySelector('.product-description').textContent.toLowerCase();
                const cardCategory = card.dataset.category;
                
                const matchesSearch = title.includes(searchTerm) || description.includes(searchTerm);
                const matchesCategory = category === 'all' || cardCategory.includes(category);
                
                if (matchesSearch && matchesCategory) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }



        // Sayfa yüklendiğinde animasyon
        window.addEventListener('load', function() {
            const cards = document.querySelectorAll('.product-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.6s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Sepete ekle butonu animasyonu
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButtons = document.querySelectorAll('.btn-add-cart');
            
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Buton animasyonu
                    this.style.transform = 'scale(0.95)';
                    
                    setTimeout(() => {
                        this.style.transform = '';
                    }, 150);

                    // Başarı mesajı için kısa bir gecikme
                    setTimeout(() => {
                        // Form submit edilecek, bu yüzden sayfa yenilenecek
                        // Başarı mesajı sepet.php sayfasında gösterilecek
                    }, 200);
                });

                // Hover efekti için ek animasyon
                button.addEventListener('mouseenter', function() {
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.style.transform = 'scale(1.1) rotate(5deg)';
                    }
                });

                button.addEventListener('mouseleave', function() {
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.style.transform = '';
                    }
                });
            });
        });
    </script>

    <style>
        /* Ürünler Sayfası Özel Stilleri */
        .products-page {
            background-color: var(--light-gray);
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-blue), var(--light-blue));
            color: var(--white);
            padding: 3rem 0;
            margin-bottom: 3rem;
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
            opacity: 0.3;
        }

        .page-header-content {
            position: relative;
            z-index: 2;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .page-title-section .page-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }

        .page-title-section .page-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            margin-bottom: 0;
        }

        .page-stats {
            display: flex;
            gap: 2rem;
        }

        .page-stats .stat-item {
            background: rgba(255,255,255,0.2);
            padding: 1.5rem;
            border-radius: 15px;
            display: flex;
            align-items: center;
            gap: 1rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
            min-width: 150px;
        }

        .page-stats .stat-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .page-stats .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .page-stats .stat-content p {
            margin-bottom: 0;
            opacity: 0.9;
        }

        /* Ürün Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .product-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 8px 25px var(--shadow);
            overflow: hidden;
            transition: all 0.4s ease;
            position: relative;
        }

        .product-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px var(--shadow-lg);
        }

        .product-image-container {
            position: relative;
            height: 250px;
            overflow: hidden;
            background: var(--light-gray);
        }

        .product-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .product-card:hover .product-image {
            transform: scale(1.05);
        }

        .product-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--success);
            color: var(--white);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }

        .product-content {
            padding: 2rem;
        }

        .product-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .product-description {
            color: var(--gray);
            font-size: 1rem;
            line-height: 1.6;
            margin-bottom: 2rem;
            height: 3.2rem;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .product-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .add-to-cart-form {
            flex: 1;
        }

        .btn-add-cart {
            width: 100%;
            padding: 12px 20px;
            background: linear-gradient(135deg, #ff8c42, #ff6b1a);
            color: white;
            border: none;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(255, 140, 66, 0.3);
            position: relative;
            overflow: hidden;
        }

        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 140, 66, 0.4);
            background: linear-gradient(135deg, #ff9a56, #ff7a2e);
        }

        .btn-add-cart:active {
            transform: translateY(0);
            box-shadow: 0 2px 10px rgba(255, 140, 66, 0.3);
        }

        .btn-add-cart i {
            font-size: 16px;
            transition: transform 0.3s ease;
        }

        .btn-add-cart:hover i {
            transform: scale(1.1);
        }

        .btn-add-cart::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }

        .btn-add-cart:hover::before {
            left: 100%;
        }





        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 8px 25px var(--shadow);
            margin: 2rem auto;
            max-width: 500px;
        }

        .empty-icon {
            font-size: 4rem;
            color: var(--soft-blue);
            margin-bottom: 2rem;
        }

        .empty-state h3 {
            color: var(--dark-gray);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: var(--gray);
        }

        /* Footer Stilleri */
        .main-footer {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
            color: var(--white);
            padding: 3rem 0 1rem;
            margin-top: 4rem;
            position: relative;
            overflow: hidden;
        }

        .main-footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="footerGrain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.05)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23footerGrain)"/></svg>');
            opacity: 0.3;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            position: relative;
            z-index: 2;
        }

        .footer-section {
            padding: 1rem;
        }

        .footer-section h4 {
            color: var(--white);
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(255,255,255,0.2);
            position: relative;
        }

        .footer-section h4::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 40px;
            height: 2px;
            background: linear-gradient(90deg, #ff8a1d, #ffa726);
        }

        .footer-section p {
            color: rgba(255,255,255,0.9);
            line-height: 1.6;
            margin-bottom: 1rem;
            font-size: 0.95rem;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-section ul li {
            margin-bottom: 0.75rem;
        }

        .footer-section ul li a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .footer-section ul li a:hover {
            color: #ff8a1d;
            transform: translateX(5px);
            background: rgba(255,255,255,0.1);
            padding-left: 0.5rem;
        }

        .footer-section ul li a::before {
            content: '→';
            opacity: 0;
            transition: all 0.3s ease;
        }

        .footer-section ul li a:hover::before {
            opacity: 1;
        }

        .footer-section p i {
            color: #ff8a1d;
            margin-right: 0.75rem;
            width: 20px;
            text-align: center;
        }

        .footer-bottom {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(255,255,255,0.2);
            text-align: center;
            position: relative;
            z-index: 2;
        }

        .footer-bottom p {
            color: rgba(255,255,255,0.8);
            margin: 0;
            font-size: 0.9rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .products-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .page-header-content {
                flex-direction: column;
                text-align: center;
            }
            
            .page-stats {
                flex-direction: column;
                width: 100%;
            }
            
            .page-stats .stat-item {
                justify-content: center;
            }
            
            .page-title-section .page-title {
                font-size: 2rem;
            }
            
            .product-content {
                padding: 1.5rem;
            }

            .main-footer {
                padding: 2rem 0 1rem;
            }

            .footer-content {
                grid-template-columns: 1fr;
                gap: 1.5rem;
                text-align: center;
            }

            .footer-section {
                padding: 0.5rem;
            }

            .footer-section ul li a {
                justify-content: center;
            }
        }
    </style>
</body>
</html>

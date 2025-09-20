<?php
session_start();
include 'db.php';

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['oturum'])) {
    header('Location: giris-yap.php');
    exit();
}

// Ürün ID kontrolü
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: urunler.php');
    exit();
}

$urun_id = intval($_GET['id']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['kullanici_id']) ? $_SESSION['kullanici_id'] : 1);
$kullanici_rol = $_SESSION['rol'] ?? 'kullanici';

// Sepete ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urun_id'])) {
    $urun_id_post = intval($_POST['urun_id']);
    
    // Sepette zaten varsa miktar artır, yoksa yeni kayıt ekle
    $stmt = $pdo->prepare("SELECT * FROM sepet_yeni WHERE user_id = ? AND urun_id = ?");
    $stmt->execute([$user_id, $urun_id_post]);
    $varmi = $stmt->fetch();
    
    if ($varmi) {
        // Miktar artır
        $stmt = $pdo->prepare("UPDATE sepet_yeni SET miktar = miktar + 1 WHERE id = ?");
        $stmt->execute([$varmi['id']]);
    } else {
        // Yeni kayıt
        $stmt = $pdo->prepare("INSERT INTO sepet_yeni (user_id, urun_id, miktar) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $urun_id_post]);
    }
    
    // Başarı mesajı için session kullan
    $_SESSION['sepet_mesaj'] = 'Ürün sepete eklendi!';
    header("Location: urun-detay.php?id=" . $urun_id_post);
    exit;
}

// Ürün bilgilerini çek
$stmt = $pdo->prepare("SELECT * FROM urunler WHERE id = ?");
$stmt->execute([$urun_id]);
$urun = $stmt->fetch();

if (!$urun) {
    header('Location: urunler.php');
    exit();
}

// Sepetteki ürün sayısını al
$stmt = $pdo->prepare("SELECT COUNT(*) as sepet_sayisi FROM sepet_yeni WHERE user_id = ?");
$stmt->execute([$user_id]);
$sepet_sayisi = $stmt->fetch()['sepet_sayisi'];

// Benzer ürünleri çek (aynı kategoriden)
$stmt = $pdo->prepare("SELECT * FROM urunler WHERE kategori = ? AND id != ? ORDER BY RAND() LIMIT 4");
$stmt->execute([$urun['kategori'], $urun_id]);
$benzer_urunler = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($urun['urun_adi']) ?> - AFET KUTUSU</title>
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
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: #1a73e8;
            font-weight: 700;
            font-size: 1.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 15px;
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.1), rgba(255, 165, 100, 0.2));
            border: 2px solid rgba(255, 140, 66, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .logo::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.4), transparent);
            transition: left 0.5s ease;
        }

        .logo:hover::before {
            left: 100%;
        }

        .logo:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 8px 25px rgba(255, 140, 66, 0.3);
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.15), rgba(255, 165, 100, 0.3));
            border-color: rgba(255, 140, 66, 0.4);
        }

        .logo-text {
            background: linear-gradient(135deg, #1a73e8, #4285f4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            white-space: nowrap;
        }

        .logo i {
            font-size: 1.75rem;
            color: #e74c3c;
            animation: heartbeat 2s ease-in-out infinite;
            filter: drop-shadow(0 2px 4px rgba(231, 76, 60, 0.3));
        }

        @keyframes heartbeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .main-nav {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .nav-links {
            display: flex;
            gap: 0.5rem;
            list-style: none;
        }

        .nav-link {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(255, 255, 255, 0.7);
            color: #475569;
            border: 1px solid rgba(255, 255, 255, 0.3);
            cursor: pointer;
            white-space: nowrap;
            min-width: fit-content;
        }

        .nav-link:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.9);
            color: #1a73e8;
        }

        .nav-link.active {
            color: #1a73e8;
            background: rgba(26, 115, 232, 0.1);
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

        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .breadcrumb {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 15px 25px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .breadcrumb-list {
            display: flex;
            align-items: center;
            gap: 10px;
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .breadcrumb-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .breadcrumb-item a:hover {
            color: #1a73e8;
        }

        .breadcrumb-item.active {
            color: #1a73e8;
            font-weight: 600;
        }

        .breadcrumb-separator {
            color: #94a3b8;
        }

        .product-detail {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .product-detail-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: start;
        }

        .product-image-section {
            position: relative;
        }

        .product-main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.3s ease;
        }

        .product-main-image:hover {
            transform: scale(1.02);
        }

        .product-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .product-info-section {
            padding: 20px 0;
        }

        .product-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .product-category {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #1a73e8, #4285f4);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 25px;
        }

        .product-description {
            font-size: 1.1rem;
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 30px;
            padding: 25px;
            background: rgba(248, 250, 252, 0.8);
            border-radius: 15px;
            border-left: 4px solid #1a73e8;
        }

        .product-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .meta-item {
            background: rgba(248, 250, 252, 0.8);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .meta-item i {
            font-size: 2rem;
            color: #1a73e8;
            margin-bottom: 10px;
        }

        .meta-item h4 {
            font-size: 1.1rem;
            color: #1e293b;
            margin-bottom: 5px;
        }

        .meta-item p {
            color: #64748b;
            margin: 0;
        }

        .product-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-add-cart {
            flex: 1;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
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

        .btn-add-cart:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .btn-back {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
            border: 2px solid rgba(100, 116, 139, 0.2);
            padding: 18px 30px;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-back:hover {
            background: rgba(100, 116, 139, 0.2);
            color: #475569;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(100, 116, 139, 0.2);
        }

        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            padding: 15px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .related-products {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .section-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 30px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }

        .section-title i {
            color: #1a73e8;
        }

        .related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .related-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .related-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .related-card-image {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .related-card-content {
            padding: 20px;
        }

        .related-card-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 10px;
            line-height: 1.3;
        }

        .related-card-category {
            color: #64748b;
            font-size: 0.9rem;
            margin-bottom: 15px;
        }

        .related-card-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #1a73e8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }

        .related-card-link:hover {
            color: #1557b0;
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

            .product-detail {
                padding: 25px;
            }

            .product-detail-content {
                grid-template-columns: 1fr;
                gap: 30px;
            }

            .product-title {
                font-size: 2rem;
            }

            .product-actions {
                flex-direction: column;
            }

            .related-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Modern Header -->
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <i class="fas fa-heart"></i>
                <span class="logo-text">AFET KUTUSU</span>
            </a>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="panel.php" class="nav-link"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                    <li><a href="urunler.php" class="nav-link active"><i class="fas fa-cube"></i> Ürünler</a></li>
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
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <ul class="breadcrumb-list">
                <li class="breadcrumb-item">
                    <a href="panel.php"><i class="fas fa-home"></i> Ana Sayfa</a>
                </li>
                <li class="breadcrumb-separator">
                    <i class="fas fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item">
                    <a href="urunler.php">Ürünler</a>
                </li>
                <li class="breadcrumb-separator">
                    <i class="fas fa-chevron-right"></i>
                </li>
                <li class="breadcrumb-item active">
                    <?= htmlspecialchars($urun['urun_adi']) ?>
                </li>
            </ul>
        </nav>

        <!-- Başarı Mesajı -->
        <?php if (isset($_SESSION['sepet_mesaj'])): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?= $_SESSION['sepet_mesaj'] ?>
            </div>
            <?php unset($_SESSION['sepet_mesaj']); ?>
        <?php endif; ?>

        <!-- Ürün Detayı -->
        <div class="product-detail">
            <div class="product-detail-content">
                <div class="product-image-section">
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
                    <div class="product-main-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(240,240,240,0.9)); border-radius: 20px;">
                        <i class="<?= $icon ?>" style="font-size: 8rem; color: <?= $iconColor ?>; text-shadow: 0 4px 8px rgba(0,0,0,0.1);"></i>
                    </div>
                    <div class="product-badge">
                        <i class="fas fa-gift"></i> ÜCRETSİZ
                    </div>
                </div>

                <div class="product-info-section">
                    <h1 class="product-title"><?= htmlspecialchars($urun['urun_adi']) ?></h1>
                    
                    <div class="product-category">
                        <i class="fas fa-tag"></i>
                        <?= ucfirst(htmlspecialchars($urun['kategori'] ?? 'Genel')) ?>
                    </div>

                    <div class="product-description">
                        <?= nl2br(htmlspecialchars($urun['urun_aciklama'])) ?>
                    </div>

                    <div class="product-meta">
                        <div class="meta-item">
                            <i class="fas fa-check-circle"></i>
                            <h4>Stok Durumu</h4>
                            <p>Stokta Var</p>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-shipping-fast"></i>
                            <h4>Teslimat</h4>
                            <p>Ücretsiz Kargo</p>
                        </div>
                        <div class="meta-item">
                            <i class="fas fa-shield-alt"></i>
                            <h4>Garanti</h4>
                            <p>Kalite Garantisi</p>
                        </div>
                    </div>

                    <div class="product-actions">
                        <form method="post" style="flex: 1;">
                            <input type="hidden" name="urun_id" value="<?= $urun['id'] ?>">
                            <button type="submit" class="btn-add-cart">
                                <i class="fas fa-shopping-cart"></i>
                                <span>Sepete Ekle</span>
                            </button>
                        </form>
                        <a href="urunler.php" class="btn-back">
                            <i class="fas fa-arrow-left"></i>
                            Geri Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Benzer Ürünler -->
        <?php if (!empty($benzer_urunler)): ?>
        <div class="related-products">
            <h2 class="section-title">
                <i class="fas fa-heart"></i>
                Benzer Ürünler
            </h2>
            
            <div class="related-grid">
                <?php foreach ($benzer_urunler as $benzer): ?>
                <div class="related-card">
                    <?php
                    // Benzer ürün için simge belirleme
                    $benzer_icon = 'fas fa-box'; // varsayılan
                    $benzer_iconColor = '#007bff'; // varsayılan mavi
                    
                    if (stripos($benzer['urun_adi'], 'gıda') !== false || stripos($benzer['urun_adi'], 'yiyecek') !== false) {
                        $benzer_icon = 'fas fa-utensils';
                        $benzer_iconColor = '#28a745';
                    } elseif (stripos($benzer['urun_adi'], 'su') !== false) {
                        $benzer_icon = 'fas fa-tint';
                        $benzer_iconColor = '#17a2b8';
                    } elseif (stripos($benzer['urun_adi'], 'battaniye') !== false) {
                        $benzer_icon = 'fas fa-bed';
                        $benzer_iconColor = '#6f42c1';
                    } elseif (stripos($benzer['urun_adi'], 'çadır') !== false) {
                        $benzer_icon = 'fas fa-campground';
                        $benzer_iconColor = '#fd7e14';
                    } elseif (stripos($benzer['urun_adi'], 'fener') !== false) {
                        $benzer_icon = 'fas fa-bolt';
                        $benzer_iconColor = '#ffc107';
                    } elseif (stripos($benzer['urun_adi'], 'hijyen') !== false) {
                        $benzer_icon = 'fas fa-soap';
                        $benzer_iconColor = '#20c997';
                    } elseif (stripos($benzer['urun_adi'], 'ısıtıcı') !== false) {
                        $benzer_icon = 'fas fa-fire';
                        $benzer_iconColor = '#dc3545';
                    } elseif (stripos($benzer['urun_adi'], 'yağmurluk') !== false) {
                        $benzer_icon = 'fas fa-tshirt';
                        $benzer_iconColor = '#6c757d';
                    } elseif (stripos($benzer['urun_adi'], 'düdük') !== false) {
                        $benzer_icon = 'fas fa-bullhorn';
                        $benzer_iconColor = '#e83e8c';
                    } elseif (stripos($benzer['urun_adi'], 'ayakkabı') !== false) {
                        $benzer_icon = 'fas fa-shoe-prints';
                        $benzer_iconColor = '#8B4513';
                    } elseif (stripos($benzer['urun_adi'], 'çorap') !== false) {
                        $benzer_icon = 'fas fa-socks';
                        $benzer_iconColor = '#FF6347';
                    } elseif (stripos($benzer['urun_adi'], 'mont') !== false) {
                        $benzer_icon = 'fas fa-user-tie';
                        $benzer_iconColor = '#2E8B57';
                    } elseif (stripos($benzer['urun_adi'], 'pantolon') !== false) {
                        $benzer_icon = 'fas fa-tshirt';
                        $benzer_iconColor = '#4169E1';
                    }
                    ?>
                    <div class="related-card-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(240,240,240,0.9)); border-radius: 15px;">
                        <i class="<?= $benzer_icon ?>" style="font-size: 3rem; color: <?= $benzer_iconColor ?>; text-shadow: 0 2px 4px rgba(0,0,0,0.1);"></i>
                    </div>
                    <div class="related-card-content">
                        <h3 class="related-card-title"><?= htmlspecialchars($benzer['urun_adi']) ?></h3>
                        <p class="related-card-category">
                            <?= ucfirst(htmlspecialchars($benzer['kategori'] ?? 'Genel')) ?>
                        </p>
                        <a href="urun-detay.php?id=<?= $benzer['id'] ?>" class="related-card-link">
                            Detayları Gör <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
        // Sayfa yüklendiğinde animasyonlar
        document.addEventListener('DOMContentLoaded', function() {
            const productDetail = document.querySelector('.product-detail');
            const relatedCards = document.querySelectorAll('.related-card');
            
            // Ana ürün detayı animasyonu
            productDetail.style.opacity = '0';
            productDetail.style.transform = 'translateY(30px)';
            setTimeout(() => {
                productDetail.style.transition = 'all 0.6s ease';
                productDetail.style.opacity = '1';
                productDetail.style.transform = 'translateY(0)';
            }, 100);
            
            // Benzer ürünler animasyonu
            relatedCards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, 300 + (index * 100));
            });
        });
    </script>
</body>
</html>
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

// Ürün ekleme işlemi
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
    
    header("Location: sepet.php");
    exit;
}

// Ürün silme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sil_id'])) {
    $sil_id = intval($_POST['sil_id']);
    
    $stmt = $pdo->prepare("DELETE FROM sepet_yeni WHERE id = ? AND user_id = ?");
    $stmt->execute([$sil_id, $user_id]);
    
    header("Location: sepet.php");
    exit;
}

// Miktar güncelleme işlemi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['guncelle_id']) && isset($_POST['yeni_miktar'])) {
    $guncelle_id = intval($_POST['guncelle_id']);
    $yeni_miktar = intval($_POST['yeni_miktar']);
    
    if ($yeni_miktar > 0) {
        $stmt = $pdo->prepare("UPDATE sepet_yeni SET miktar = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$yeni_miktar, $guncelle_id, $user_id]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM sepet_yeni WHERE id = ? AND user_id = ?");
        $stmt->execute([$guncelle_id, $user_id]);
    }
    
    header("Location: sepet.php");
    exit;
}

// Sepetteki ürünleri çek (ürün bilgileriyle birlikte)
$stmt = $pdo->prepare("
    SELECT s.id as sepet_id, u.urun_adi, u.urun_aciklama, u.urun_fiyat, u.urun_foto, s.miktar,
           (u.urun_fiyat * s.miktar) as toplam_fiyat
    FROM sepet_yeni s 
    JOIN urunler u ON s.urun_id = u.id 
    WHERE s.user_id = ?
    ORDER BY s.eklenme_tarihi DESC
");
$stmt->execute([$user_id]);
$sepetUrunler = $stmt->fetchAll();

// Toplam tutarı hesapla
$toplam_tutar = array_sum(array_column($sepetUrunler, 'toplam_fiyat'));
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepetim - AFET KUTUSU</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        :root {
            --primary-blue: #1a73e8;
            --light-blue: #d2e3fc;
            --soft-blue: #dbeafe;
            --white: #ffffff;
            --light-gray: #f8fafc;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --medium-gray: #64748b;
            --success-green: #10b981;
            --danger-red: #ef4444;
            --warning-orange: #f59e0b;
            --border-gray: #e2e8f0;
            --shadow: 0 4px 20px rgba(26, 115, 232, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--light-blue) 0%, var(--white) 100%);
            min-height: 100vh;
            color: var(--text-dark);
        }

        /* Modern Header Styles */
        .header {
            background: linear-gradient(135deg, rgba(210, 227, 252, 0.95), rgba(255, 255, 255, 0.95));
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(26, 115, 232, 0.1);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
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
            border-bottom: 1px solid rgba(26, 115, 232, 0.1);
            box-shadow: 0 2px 20px rgba(26, 115, 232, 0.05);
        }

        .page-header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            text-align: center;
        }

        .page-title {
            font-size: 2.8rem;
            font-weight: 700;
            color: #1a73e8;
            margin-bottom: 0.75rem;
            background: linear-gradient(135deg, #1a73e8, #4285f4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            color: #5f6368;
            font-size: 1.2rem;
            font-weight: 400;
        }

        /* Main Content */
        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem 4rem;
        }

        /* Cart Container */
        .cart-container {
            display: grid;
            grid-template-columns: 1fr 350px;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        /* Cart Items */
        .cart-items {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid rgba(26, 115, 232, 0.1);
        }

        .cart-header {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            padding: 1.5rem;
            font-size: 1.2rem;
            font-weight: 600;
            box-shadow: 0 2px 10px rgba(255, 140, 66, 0.2);
        }

        .cart-item {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-gray);
            display: grid;
            grid-template-columns: 80px 1fr auto auto;
            gap: 1rem;
            align-items: center;
            transition: background-color 0.3s ease;
        }

        .cart-item:hover {
            background: var(--light-gray);
        }

        .cart-item:last-child {
            border-bottom: none;
        }

        .product-image {
            width: 80px;
            height: 80px;
            border-radius: 8px;
            object-fit: cover;
            border: 2px solid var(--border-gray);
        }

        .product-info h4 {
            color: var(--text-dark);
            font-size: 1.1rem;
            margin-bottom: 0.5rem;
        }

        .product-info p {
            color: var(--text-gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }

        .product-price {
            color: var(--primary-blue);
            font-weight: 600;
            font-size: 1rem;
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--light-gray);
            border-radius: 8px;
            padding: 0.5rem;
        }

        .quantity-btn {
            background: var(--primary-blue);
            color: var(--white);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color 0.3s ease;
        }

        .quantity-btn:hover {
            background: var(--light-blue);
        }

        .quantity-input {
            width: 50px;
            text-align: center;
            border: 1px solid var(--border-gray);
            border-radius: 4px;
            padding: 0.25rem;
            font-weight: 500;
        }

        .remove-btn {
            background: var(--danger-red);
            color: var(--white);
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .remove-btn:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }

        /* Order Summary */
        .order-summary {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 1.5rem;
            height: fit-content;
            position: sticky;
            top: 100px;
            border: 1px solid rgba(26, 115, 232, 0.1);
        }

        .summary-header {
            font-size: 1.3rem;
            font-weight: 600;
            color: #1a73e8;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(26, 115, 232, 0.2);
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.75rem;
            color: var(--text-gray);
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-dark);
            padding-top: 1rem;
            border-top: 2px solid var(--border-gray);
            margin-top: 1rem;
        }

        .checkout-btn {
            width: 100%;
            background: linear-gradient(135deg, #34a853, #137333);
            color: white;
            border: none;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1.5rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(52, 168, 83, 0.3);
        }

        .checkout-btn:hover {
            background: linear-gradient(135deg, #137333, #0d652d);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 168, 83, 0.4);
        }

        .continue-shopping {
            width: 100%;
            background: transparent;
            color: #ff8c00;
            border: 2px solid #ff8c00;
            padding: 0.75rem;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            margin-top: 1rem;
            transition: all 0.3s ease;
            text-decoration: none;
            display: block;
            text-align: center;
        }

        .continue-shopping:hover {
            background: #ff8c00;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
        }

        /* Empty Cart */
        .empty-cart {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 3rem;
            text-align: center;
            grid-column: 1 / -1;
            border: 1px solid rgba(26, 115, 232, 0.1);
        }

        .empty-cart-icon {
            font-size: 4rem;
            color: var(--text-gray);
            margin-bottom: 1rem;
        }

        .empty-cart h3 {
            color: var(--text-dark);
            margin-bottom: 1rem;
        }

        .empty-cart p {
            color: var(--text-gray);
            margin-bottom: 2rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .header-content {
                padding: 0 1rem;
                flex-direction: column;
                gap: 1rem;
            }

            .nav-links {
                gap: 1rem;
            }

            .page-header-content {
                padding: 0 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .main-content {
                padding: 0 1rem 2rem;
            }

            .cart-container {
                grid-template-columns: 1fr;
                gap: 1rem;
            }

            .cart-item {
                grid-template-columns: 1fr;
                gap: 1rem;
                text-align: center;
            }

            .product-image {
                justify-self: center;
            }

            .quantity-controls {
                justify-self: center;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .cart-item {
            animation: fadeInUp 0.5s ease forwards;
        }

        .cart-item:nth-child(2) { animation-delay: 0.1s; }
        .cart-item:nth-child(3) { animation-delay: 0.2s; }
        .cart-item:nth-child(4) { animation-delay: 0.3s; }
    </style>
</head>
<body>
    <!-- Header -->
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
                    <li><a href="panel.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> <?php echo ($kullanici_rol === 'admin') ? 'Ana Sayfa' : 'Panel'; ?></a></li>
                    <li><a href="urunler.php" class="nav-link"><i class="fas fa-box"></i> Ürünler</a></li>
                    <?php if ($kullanici_rol !== 'admin'): ?>
                    <li><a href="sepet.php" class="nav-link active"><i class="fas fa-shopping-cart"></i> Sepet</a></li>
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

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-content">
            <h1 class="page-title">🛒 Sepetim</h1>
            <p class="page-subtitle">Afet durumları için hazırladığınız ürünleri gözden geçirin</p>
        </div>
    </div>

    <!-- Main Content -->
    <main class="main-content">
        <?php if (empty($sepetUrunler)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">🛒</div>
                <h3>Sepetiniz Boş</h3>
                <p>Henüz sepetinize ürün eklemediniz. Afet durumları için gerekli ürünleri keşfetmek için ürünler sayfasını ziyaret edin.</p>
                <a href="urunler.php" class="checkout-btn">Ürünleri Keşfet</a>
            </div>
        <?php else: ?>
            <!-- Cart Container -->
            <div class="cart-container">
                <!-- Cart Items -->
                <div class="cart-items">
                    <div class="cart-header">
                        <i class="fa fa-shopping-cart"></i> Sepetinizdeki Ürünler (<?= count($sepetUrunler) ?> ürün)
                    </div>
                    
                    <?php foreach ($sepetUrunler as $index => $urun): ?>
                        <div class="cart-item" style="animation-delay: <?= $index * 0.1 ?>s;">
                            <?php
                            // Sepet ürünü için simge belirleme
                            $sepet_icon = 'fas fa-box'; // varsayılan
                            $sepet_iconColor = '#007bff'; // varsayılan mavi
                            
                            if (stripos($urun['urun_adi'], 'gıda') !== false || stripos($urun['urun_adi'], 'yiyecek') !== false) {
                                $sepet_icon = 'fas fa-utensils';
                                $sepet_iconColor = '#28a745';
                            } elseif (stripos($urun['urun_adi'], 'su') !== false) {
                                $sepet_icon = 'fas fa-tint';
                                $sepet_iconColor = '#17a2b8';
                            } elseif (stripos($urun['urun_adi'], 'battaniye') !== false) {
                                $sepet_icon = 'fas fa-bed';
                                $sepet_iconColor = '#6f42c1';
                            } elseif (stripos($urun['urun_adi'], 'çadır') !== false) {
                                $sepet_icon = 'fas fa-campground';
                                $sepet_iconColor = '#fd7e14';
                            } elseif (stripos($urun['urun_adi'], 'fener') !== false) {
                                $sepet_icon = 'fas fa-bolt';
                                $sepet_iconColor = '#ffc107';
                            } elseif (stripos($urun['urun_adi'], 'hijyen') !== false) {
                                $sepet_icon = 'fas fa-soap';
                                $sepet_iconColor = '#20c997';
                            } elseif (stripos($urun['urun_adi'], 'ısıtıcı') !== false) {
                                $sepet_icon = 'fas fa-fire';
                                $sepet_iconColor = '#dc3545';
                            } elseif (stripos($urun['urun_adi'], 'yağmurluk') !== false) {
                                $sepet_icon = 'fas fa-tshirt';
                                $sepet_iconColor = '#6c757d';
                            } elseif (stripos($urun['urun_adi'], 'düdük') !== false) {
                                $sepet_icon = 'fas fa-bullhorn';
                                $sepet_iconColor = '#e83e8c';
                            } elseif (stripos($urun['urun_adi'], 'ayakkabı') !== false) {
                                $sepet_icon = 'fas fa-shoe-prints';
                                $sepet_iconColor = '#8B4513';
                            } elseif (stripos($urun['urun_adi'], 'çorap') !== false) {
                                $sepet_icon = 'fas fa-socks';
                                $sepet_iconColor = '#FF6347';
                            } elseif (stripos($urun['urun_adi'], 'mont') !== false) {
                                $sepet_icon = 'fas fa-user-tie';
                                $sepet_iconColor = '#2E8B57';
                            } elseif (stripos($urun['urun_adi'], 'pantolon') !== false) {
                                $sepet_icon = 'fas fa-tshirt';
                                $sepet_iconColor = '#4169E1';
                            }
                            ?>
                            <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(240,240,240,0.9)); border-radius: 10px;">
                                <i class="<?= $sepet_icon ?>" style="font-size: 2.5rem; color: <?= $sepet_iconColor ?>; text-shadow: 0 2px 4px rgba(0,0,0,0.1);"></i>
                            </div>
                            
                            <div class="product-info">
                                <h4><?= htmlspecialchars($urun['urun_adi']) ?></h4>
                                <p><?= htmlspecialchars($urun['urun_aciklama']) ?></p>
                                <div class="product-price">
                                    <?= $urun['urun_fiyat'] == 0 ? 'Ücretsiz' : number_format($urun['urun_fiyat'], 2) . ' ₺' ?>
                                </div>
                            </div>
                            
                            <div class="quantity-controls">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="guncelle_id" value="<?= $urun['sepet_id'] ?>">
                                    <input type="hidden" name="yeni_miktar" value="<?= max(1, $urun['miktar'] - 1) ?>">
                                    <button type="submit" class="quantity-btn">-</button>
                                </form>
                                
                                <input type="number" value="<?= $urun['miktar'] ?>" class="quantity-input" readonly>
                                
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="guncelle_id" value="<?= $urun['sepet_id'] ?>">
                                    <input type="hidden" name="yeni_miktar" value="<?= $urun['miktar'] + 1 ?>">
                                    <button type="submit" class="quantity-btn">+</button>
                                </form>
                            </div>
                            
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="sil_id" value="<?= $urun['sepet_id'] ?>">
                                <button type="submit" class="remove-btn" onclick="return confirm('Bu ürünü sepetten çıkarmak istediğinizden emin misiniz?')">
                                    <i class="fa fa-trash"></i> Kaldır
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="order-summary">
                    <div class="summary-header">
                        <i class="fa fa-calculator"></i> Sipariş Özeti
                    </div>
                    
                    <div class="summary-row">
                        <span>Ürün Sayısı:</span>
                        <span><?= count($sepetUrunler) ?> adet</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Toplam Miktar:</span>
                        <span><?= array_sum(array_column($sepetUrunler, 'miktar')) ?> adet</span>
                    </div>
                    
                    <div class="summary-row">
                        <span>Kargo:</span>
                        <span class="text-success">Ücretsiz</span>
                    </div>
                    
                    <div class="summary-total">
                        <span>Toplam Tutar:</span>
                        <span><?= $toplam_tutar == 0 ? 'Ücretsiz' : number_format($toplam_tutar, 2) . ' ₺' ?></span>
                    </div>
                    
                    <form method="POST" action="siparis-ver.php" style="display: inline;">
                        <button type="submit" class="checkout-btn">
                            <i class="fa fa-credit-card"></i> Sipariş Ver
                        </button>
                    </form>
                    
                    <a href="urunler.php" class="continue-shopping">
                        <i class="fa fa-arrow-left"></i> Alışverişe Devam Et
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="js/jquery-3.4.1.min.js"></script>
    <script src="js/bootstrap.js"></script>
    <script>
        // Smooth animations on page load
        document.addEventListener('DOMContentLoaded', function() {
            const cartItems = document.querySelectorAll('.cart-item');
            cartItems.forEach((item, index) => {
                setTimeout(() => {
                    item.style.opacity = '1';
                    item.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });

        // Quantity update with animation
        document.querySelectorAll('.quantity-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const cartItem = this.closest('.cart-item');
                cartItem.style.transform = 'scale(0.98)';
                setTimeout(() => {
                    cartItem.style.transform = 'scale(1)';
                }, 150);
            });
        });

        // Remove item with animation
        document.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (confirm('Bu ürünü sepetten çıkarmak istediğinizden emin misiniz?')) {
                    const cartItem = this.closest('.cart-item');
                    cartItem.style.animation = 'fadeOut 0.3s ease forwards';
                } else {
                    e.preventDefault();
                }
            });
        });

        // Add fadeOut animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes fadeOut {
                to {
                    opacity: 0;
                    transform: translateX(-100%);
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>


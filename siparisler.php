<?php
session_start();
include 'db.php';

// Kullanıcı kontrolü
if (isset($_SESSION['kullanici_id'])) {
    $user_id = $_SESSION['kullanici_id'];
} elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
} else {
    // Test kullanıcısı olarak giriş yap
    $_SESSION['oturum'] = true;
    $_SESSION['kullanici_id'] = 1;
    $_SESSION['ad'] = 'test_user';
    $user_id = 1;
}

// Kullanıcı bilgileri
$kullanici_rol = $_SESSION['rol'] ?? 'kullanici';
$kullanici_adi = $_SESSION['ad'] ?? 'Misafir';

// Sipariş filtreleme
$durum_filter = isset($_GET['durum']) ? $_GET['durum'] : '';
$tarih_filter = isset($_GET['tarih']) ? $_GET['tarih'] : '';

// Tablo varlığını kontrol et
$table_exists = false;
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'siparisler_yeni'");
    $table_exists = $stmt->rowCount() > 0;
} catch (PDOException $e) {
    $table_exists = false;
}

// Siparişleri çek - sadece tablo varsa
$siparisler = [];
if ($table_exists) {
    try {
        $sql = "
            SELECT s.id AS siparis_id, s.siparis_tarihi, s.siparis_durumu AS durum, s.toplam_fiyat,
                   GROUP_CONCAT(CONCAT(u.urun_adi, ' (', su.miktar, ' adet)') SEPARATOR ', ') AS urunler,
                   COUNT(su.urun_id) AS urun_sayisi
            FROM siparisler_yeni s
            LEFT JOIN siparis_urunler_yeni su ON s.id = su.siparis_id
            LEFT JOIN urunler u ON su.urun_id = u.id
            WHERE s.user_id = ?
        ";
        
        $params = [$user_id];

        if ($durum_filter) {
            $sql .= " AND s.siparis_durumu = ?";
            $params[] = $durum_filter;
        }

        if ($tarih_filter) {
            $sql .= " AND DATE(s.siparis_tarihi) = ?";
            $params[] = $tarih_filter;
        }

        $sql .= " GROUP BY s.id ORDER BY s.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $siparisler = $stmt->fetchAll();
    } catch (PDOException $e) {
        // Sütun hatası varsa boş dizi döndür
        $siparisler = [];
        $table_exists = false;
    }
}

// Sipariş durumları
$durum_renkleri = [
    'Beklemede' => 'warning',
    'Onaylandı' => 'info', 
    'Hazırlanıyor' => 'primary',
    'Kargoda' => 'secondary',
    'Teslim Edildi' => 'success',
    'İptal Edildi' => 'danger'
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Siparişlerim - AFET KUTUSU</title>
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

        .container {
            margin-top: 2rem;
        }

        .page-header {
            background: linear-gradient(135deg, #d2e3fc, #1a73e8);
            color: white;
            padding: 2rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 25px rgba(26, 115, 232, 0.3);
        }

        .page-header h1 {
            margin: 0;
            font-size: 2.5rem;
            font-weight: 300;
        }

        .page-header .subtitle {
            opacity: 0.9;
            margin-top: 0.5rem;
        }

        .filter-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-left: 4px solid #1a73e8;
        }

        .filter-card h5 {
            color: #1a73e8;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .form-control {
            border: 2px solid #d2e3fc;
            border-radius: 12px;
            padding: 12px 15px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            border-color: #1a73e8;
            box-shadow: 0 0 0 3px rgba(26, 115, 232, 0.1);
            background: white;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d2e3fc, #1a73e8);
            border: none;
            border-radius: 25px;
            padding: 12px 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 115, 232, 0.4);
            background: linear-gradient(135deg, #1a73e8, #1557b0);
        }

        .btn-outline-secondary {
            border: 2px solid #6c757d;
            border-radius: 10px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .order-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            margin-bottom: 1.5rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-left: 4px solid #1a73e8;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .order-header {
            background: linear-gradient(135deg, #d2e3fc, #f8f9fa);
            padding: 1.5rem;
            border-radius: 20px 20px 0 0;
            border-bottom: 1px solid #dee2e6;
        }

        .order-body {
            padding: 1.5rem;
        }

        .order-number {
            font-size: 1.2rem;
            font-weight: bold;
            color: #1a73e8;
        }

        .order-date {
            color: #6c757d;
            font-size: 0.9rem;
        }

        .order-total {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--dark-color);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-warning {
            background: linear-gradient(135deg, var(--warning-color), #e0a800);
            color: #212529;
        }

        .badge-info {
            background: linear-gradient(135deg, var(--info-color), #138496);
            color: white;
        }

        .badge-primary {
            background: linear-gradient(135deg, var(--primary-color), #0d47a1);
            color: white;
        }

        .badge-secondary {
            background: linear-gradient(135deg, #6c757d, #545b62);
            color: white;
        }

        .badge-success {
            background: linear-gradient(135deg, var(--success-color), #1e7e34);
            color: white;
        }

        .badge-danger {
            background: linear-gradient(135deg, var(--danger-color), #bd2130);
            color: white;
        }

        .product-list {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }

        .product-item {
            color: #495057;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            background: white;
            border-radius: 8px;
            border-left: 3px solid var(--secondary-color);
        }

        .product-item:last-child {
            margin-bottom: 0;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }

        .empty-state h3 {
            color: var(--dark-color);
            margin-bottom: 1rem;
        }

        .empty-state p {
            color: #6c757d;
            margin-bottom: 2rem;
        }

        .stats-row {
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }
            
            .order-header {
                padding: 1rem;
            }
            
            .order-body {
                padding: 1rem;
            }
            
            .filter-card {
                padding: 1rem;
            }
        }
        /* Page Container */
        .page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            backdrop-filter: blur(20px);
            box-shadow: var(--shadow-lg);
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .page-title i {
            color: var(--primary-blue);
        }

        .page-subtitle {
            color: var(--medium-gray);
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        /* Filter Section */
        .filter-section {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .filter-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--dark-gray);
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            font-weight: 500;
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .form-control {
            padding: 0.75rem;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.9);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            justify-content: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--light-blue));
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
        }

        .btn-secondary {
            background: var(--medium-gray);
            color: white;
        }

        .btn-secondary:hover {
            background: var(--dark-gray);
            transform: translateY(-2px);
        }

        /* Order Cards */
        .orders-grid {
            display: grid;
            gap: 1.5rem;
        }

        .order-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 1.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .order-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .order-id {
            font-weight: 700;
            color: #ff8c42;
            font-size: 1.1rem;
        }

        .order-date {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        .order-status {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-pending {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            border: 1px solid #ff6b35;
        }

        .status-beklemede {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            border: 1px solid #ff6b35;
        }

        .status-hazirlaniyor {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            border: 1px solid #ff6b35;
        }

        .status-confirmed {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success-green);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .status-shipped {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            border: 1px solid #ff6b35;
        }

        .status-kargoda {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            border: 1px solid #ff6b35;
        }

        .status-delivered {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            border: 1px solid #ff6b35;
        }

        .status-teslim-edildi {
            background: linear-gradient(135deg, #ff8c42, #ff6b35);
            color: white;
            border: 1px solid #ff6b35;
        }

        .status-cancelled {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger-red);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .order-products {
            margin-top: 1rem;
        }

        .product-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            margin-bottom: 0.5rem;
        }

        .product-name {
            font-weight: 500;
            color: var(--dark-gray);
        }

        .product-quantity {
            color: var(--medium-gray);
            font-size: 0.9rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 20px;
            border: 2px dashed rgba(37, 99, 235, 0.3);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--primary-blue);
            margin-bottom: 1rem;
            opacity: 0.7;
        }

        .empty-state h3 {
            color: var(--dark-gray);
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: var(--medium-gray);
            margin-bottom: 2rem;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .page-container {
                margin: 1rem;
                padding: 1rem;
            }

            .page-title {
                font-size: 2rem;
            }

            .filter-form {
                grid-template-columns: 1fr;
            }

            .order-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
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
                    <span class="logo-text">AFET KUTUSU</span>
                </div>
            <?php else: ?>
                <a href="index.php" class="logo">
                    <i class="fas fa-heart" style="color: #dc3545;"></i>
                    <span class="logo-text">AFET KUTUSU</span>
                </a>
            <?php endif; ?>
            <nav class="main-nav">
                <ul class="nav-links">
                    <li><a href="panel.php" class="nav-link"><i class="fas fa-home"></i> <?php echo ($kullanici_rol === 'admin') ? 'Ana Sayfa' : 'Panel'; ?></a></li>
                    <li><a href="urunler.php" class="nav-link"><i class="fas fa-cube"></i> Ürünler</a></li>
                    <?php if ($kullanici_rol !== 'admin'): ?>
                    <li><a href="sepet.php" class="nav-link"><i class="fas fa-shopping-cart"></i> Sepet</a></li>
                    <li><a href="siparisler.php" class="nav-link active"><i class="fas fa-list"></i> Siparişlerim</a></li>
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
    <!-- end header section -->

    <!-- Page Container -->
    <div class="page-container">
        <h1 class="page-title">
            <i class="fas fa-list-alt"></i>
            Siparişlerim
        </h1>
        <p class="page-subtitle">
            Tüm siparişlerinizi buradan takip edebilirsiniz
        </p>

        <!-- Filter Section -->
        <div class="filter-section">
            <h3 class="filter-title">
                <i class="fas fa-filter"></i>
                Filtreler
            </h3>
            <form method="get" class="filter-form">
                <div class="form-group">
                    <label class="form-label">Sipariş Durumu</label>
                    <select name="durum" class="form-control">
                        <option value="">Tümü</option>
                        <option value="Beklemede" <?= $durum_filter == 'Beklemede' ? 'selected' : '' ?>>Beklemede</option>
                        <option value="Onaylandı" <?= $durum_filter == 'Onaylandı' ? 'selected' : '' ?>>Onaylandı</option>
                        <option value="Hazırlanıyor" <?= $durum_filter == 'Hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                        <option value="Kargoda" <?= $durum_filter == 'Kargoda' ? 'selected' : '' ?>>Kargoda</option>
                        <option value="Teslim Edildi" <?= $durum_filter == 'Teslim Edildi' ? 'selected' : '' ?>>Teslim Edildi</option>
                        <option value="İptal Edildi" <?= $durum_filter == 'İptal Edildi' ? 'selected' : '' ?>>İptal Edildi</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Sipariş Tarihi</label>
                    <input type="date" name="tarih" class="form-control" value="<?= $tarih_filter ?>">
                </div>
                <div class="form-group" style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-danger" style="background-color: #dc3545; border-color: #dc3545; color: white;">
                        <i class="fas fa-search"></i> Filtrele
                    </button>
                    <a href="siparisler.php" class="btn btn-success" style="background-color: #28a745; border-color: #28a745; color: white; text-decoration: none;">
                        <i class="fas fa-refresh"></i> Temizle
                    </a>
                </div>
            </form>
        </div>

        <?php if (count($siparisler) > 0): ?>
            <!-- Siparişler -->
            <div class="orders-grid">
                <?php foreach ($siparisler as $siparis): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div>
                                <div class="order-id">
                                    <i class="fas fa-hashtag"></i> Sipariş #<?= $siparis['siparis_id'] ?>
                                </div>
                                <div class="order-date">
                                    <i class="fas fa-calendar"></i> <?= date('d.m.Y H:i', strtotime($siparis['siparis_tarihi'])) ?>
                                </div>
                            </div>
                            <div class="order-status status-<?= strtolower(str_replace([' ', 'ı', 'ş', 'ğ', 'ü', 'ö', 'ç'], ['-', 'i', 's', 'g', 'u', 'o', 'c'], $siparis['durum'])) ?>">
                                <?= $siparis['durum'] ?>
                            </div>
                        </div>
                        <div class="order-products">
                            <h4 style="color: var(--dark-gray); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fas fa-box"></i> Sipariş İçeriği
                            </h4>
                            <?php 
                            $urunler = explode(', ', $siparis['urunler']);
                            foreach ($urunler as $urun): 
                            ?>
                                <div class="product-item">
                                    <span class="product-name">
                                        <i class="fas fa-check-circle" style="color: var(--success-green);"></i>
                                        <?= htmlspecialchars($urun) ?>
                                    </span>
                                    <span class="product-quantity">ÜCRETSİZ</span>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if ($siparis['durum'] == 'Kargoda'): ?>
                                <div style="background: rgba(37, 99, 235, 0.1); border: 1px solid rgba(37, 99, 235, 0.3); border-radius: 10px; padding: 1rem; margin-top: 1rem; color: var(--primary-blue);">
                                    <i class="fas fa-truck"></i> <strong>Kargo Takip:</strong> Siparişiniz kargoya verilmiştir. Takip numaranız: KRG<?= $siparis['siparis_id'] ?>2024
                                </div>
                            <?php elseif ($siparis['durum'] == 'Hazırlanıyor'): ?>
                                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 10px; padding: 1rem; margin-top: 1rem; color: var(--warning-orange);">
                                    <i class="fas fa-cog fa-spin"></i> <strong>Hazırlanıyor:</strong> Siparişiniz şu anda hazırlanmaktadır.
                                </div>
                            <?php elseif ($siparis['durum'] == 'Teslim Edildi'): ?>
                                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); border-radius: 10px; padding: 1rem; margin-top: 1rem; color: var(--success-green);">
                                    <i class="fas fa-check-circle"></i> <strong>Teslim Edildi:</strong> Siparişiniz başarıyla teslim edilmiştir!
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- Boş Durum -->
            <div class="empty-state">
                <i class="fas fa-shopping-bag"></i>
                <h3>Henüz Sipariş Vermediniz</h3>
                <p>Afet yardım ürünlerinden faydalanmak için sipariş verebilirsiniz.</p>
                <a href="urunler.php" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Ürünleri İncele
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Sayfa yüklendiğinde animasyon
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.order-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                
                setTimeout(() => {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
</body>
</html>

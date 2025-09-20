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

// Admin kontrolü
$kullanici_rol = $_SESSION['rol'] ?? 'kullanici';
$kullanici_adi = $_SESSION['ad'] ?? 'Misafir';

if($kullanici_rol != 'admin') {
    header('Location: panel.php');
    exit();
}

// Sipariş durumu güncelleme
if(isset($_POST['siparis_id']) && isset($_POST['yeni_durum'])){
    $siparis_id = intval($_POST['siparis_id']);
    $yeni_durum = trim($_POST['yeni_durum']);
    
    $izinli_durumlar = ['Beklemede', 'Onaylandı', 'Hazırlanıyor', 'Kargoda', 'Teslim Edildi'];
    
    if(in_array($yeni_durum, $izinli_durumlar)){
        try {
            $stmt = $pdo->prepare("UPDATE siparisler_yeni SET siparis_durumu = ? WHERE id = ?");
            $stmt->execute([$yeni_durum, $siparis_id]);
            $basari = "Sipariş durumu başarıyla güncellendi.";
        } catch(PDOException $e) {
            $hata = "Sipariş durumu güncellenirken hata oluştu.";
        }
    } else {
        $hata = "Geçersiz sipariş durumu.";
    }
    header("Location: admin-panel.php");
    exit();
}

// Ürün silme
if(isset($_POST['sil_id'])){
    $silId = intval($_POST['sil_id']);
    $stmt = $pdo->prepare("DELETE FROM urunler WHERE id = ?");
    $stmt->execute([$silId]);
    header("Location: admin-panel.php");
    exit();
}

// Ürün ekleme
$hata = '';
$basari = '';

if(isset($_POST['urun_ekle'])){
    $isim = trim($_POST['isim']);
    $kategori = trim($_POST['kategori']);
    $aciklama = trim($_POST['aciklama']);
    $stok_durumu = trim($_POST['stok_durumu']);
    $resim = $_FILES['resim'] ?? null;

    if($isim == '' || $kategori == '' || $stok_durumu == ''){
        $hata = "İsim, kategori ve stok durumu zorunludur.";
    } else {
        $resimAdi = null;
        if($resim && $resim['error'] == 0){
            $izinli = ['jpg','jpeg','png','gif'];
            $uzanti = strtolower(pathinfo($resim['name'], PATHINFO_EXTENSION));
            if(in_array($uzanti, $izinli)){
                if(!is_dir("uploads")) mkdir("uploads",0777,true);
                $resimAdi = uniqid() . '.' . $uzanti;
                move_uploaded_file($resim['tmp_name'], 'uploads/' . $resimAdi);
            } else {
                $hata = "Sadece JPG, PNG, GIF formatlarında resim yükleyebilirsiniz.";
            }
        }

        if(!$hata){
            $stmt = $pdo->prepare("INSERT INTO urunler (urun_adi, urun_aciklama, urun_fiyat, urun_foto, stok_durumu, kategori) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$isim, $aciklama, 0.00, $resimAdi, $stok_durumu, $kategori]);
            $basari = "Ürün başarıyla eklendi.";
        }
    }
}

// Verileri çek
try {
    $users = $pdo->query("SELECT * FROM users")->fetchAll();
    $user_count = count($users);
} catch (PDOException $e) {
    $users = [];
    $user_count = 0;
}

try {
    $urunler = $pdo->query("SELECT * FROM urunler")->fetchAll();
    $product_count = count($urunler);
} catch (PDOException $e) {
    $urunler = [];
    $product_count = 0;
}

// Sipariş verilerini çek
try {
    $siparisler = $pdo->query("
        SELECT s.*, u.username, u.email, u.telefon,
               GROUP_CONCAT(CONCAT(ur.urun_adi, ' (', su.miktar, ')') SEPARATOR ', ') as urunler
        FROM siparisler_yeni s
        LEFT JOIN users u ON s.user_id = u.id
        LEFT JOIN siparis_urunler_yeni su ON s.id = su.siparis_id
        LEFT JOIN urunler ur ON su.urun_id = ur.id
        GROUP BY s.id
        ORDER BY s.siparis_tarihi DESC
    ")->fetchAll();
    $order_count = count($siparisler);
} catch (PDOException $e) {
    $siparisler = [];
    $order_count = 0;
}



// Stok durumu
$low_stock = 0;
foreach($urunler as $urun) {
    if(($urun['stok_durumu'] ?? 'Yok') === 'Yok') {
        $low_stock++;
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - AFET KUTUSU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #d2e3fc 0%, #a8c7fa 50%, #1a73e8 100%);
            min-height: 100vh;
            color: #2c3e50;
            line-height: 1.6;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            padding: 1rem 2rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #0355cc;
            font-size: 1.5rem;
            font-weight: 600;
            border: 3px solid #0355cc;
            border-radius: 15px;
            padding: 10px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(3, 85, 204, 0.2);
            transition: all 0.3s ease;
        }

        .admin-logo:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(3, 85, 204, 0.3);
        }

        .admin-logo i {
            color: #e74c3c;
            font-size: 1.8rem;
        }

        .admin-user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0355cc 0%, #0d47a1 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .admin-container {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            min-height: calc(100vh - 80px);
        }

        .admin-sidebar {
            width: 280px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            margin: 20px;
            border-radius: 20px;
            padding: 30px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            height: fit-content;
            position: sticky;
            top: 100px;
        }

        .nav-item {
            margin: 8px 20px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px 20px;
            color: #666;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-link:hover,
        .nav-link.active {
            background: linear-gradient(135deg, #ff8c00 0%, #ff6600 100%);
            color: white;
            transform: translateX(5px);
            text-decoration: none;
        }

        .nav-link i {
            width: 20px;
            text-align: center;
        }

        .admin-content {
            flex: 1;
            padding: 20px;
        }

        .content-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            display: none;
        }

        .content-section.active {
            display: block;
            animation: slideUp 0.5s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transition: left 0.5s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .stat-card.users:hover { border-left: 4px solid #3498db; }
        .stat-card.products:hover { border-left: 4px solid #2ecc71; }
        .stat-card.stock:hover { border-left: 4px solid #f39c12; }
        .stat-card.messages:hover { border-left: 4px solid #e74c3c; }
        .stat-card.orders:hover { border-left: 4px solid #9b59b6; }

        .stat-card.users { border-left-color: #3498db; }
        .stat-card.products { border-left-color: #2ecc71; }
        .stat-card.stock { border-left-color: #f39c12; }
        .stat-card.messages { border-left-color: #e74c3c; }
        .stat-card.orders { border-left-color: #9b59b6; }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .stat-icon.users { background: #3498db; }
        .stat-icon.products { background: #2ecc71; }
        .stat-icon.stock { background: #f39c12; }
        .stat-icon.messages { background: #e74c3c; }
        .stat-icon.orders { background: #9b59b6; }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            transition: all 0.3s ease;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .stat-progress {
            margin: 15px 0 10px 0;
            height: 4px;
            background: #f1f3f4;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #0355cc, #0d47a1);
            border-radius: 2px;
            transition: width 1.5s ease-in-out;
        }

        .stat-card.users .progress-bar {
            background: linear-gradient(90deg, #3498db, #2980b9);
        }

        .stat-card.products .progress-bar {
            background: linear-gradient(90deg, #2ecc71, #27ae60);
        }

        .stat-card.stock .progress-bar {
            background: linear-gradient(90deg, #f39c12, #e67e22);
        }

        .stat-card.messages .progress-bar {
            background: linear-gradient(90deg, #e74c3c, #c0392b);
        }

        .stat-card.orders .progress-bar {
            background: linear-gradient(90deg, #9b59b6, #8e44ad);
        }

        .stat-footer {
            margin-top: 10px;
            color: #7f8c8d;
            font-size: 0.8rem;
        }

        .stat-footer i {
            margin-right: 5px;
        }

        .form-container {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #0355cc;
            background: white;
            box-shadow: 0 0 0 3px rgba(3, 85, 204, 0.1);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #0355cc 0%, #0d47a1 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(3, 85, 204, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-danger {
            background: #e74c3c;
            color: white;
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-1px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #0355cc;
            color: #0355cc;
        }

        .btn-outline:hover {
            background: #0355cc;
            color: white;
        }

        .data-table {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .table {
            width: 100%;
            margin: 0;
        }

        .table th {
            background: #f8f9fa;
            padding: 15px;
            font-weight: 600;
            color: #333;
            border: none;
            border-bottom: 1px solid #e1e5e9;
        }

        .table td {
            padding: 15px;
            border: none;
            border-bottom: 1px solid #f1f3f4;
        }

        .table tr:hover {
            background: #f8f9fa;
        }

        .product-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }

        .no-image {
            width: 50px;
            height: 50px;
            background: #f1f3f4;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h5 {
            margin-bottom: 10px;
            color: #495057;
        }

        .messages-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
        }

        .message-user {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #495057;
        }

        .message-user i {
            color: #007bff;
            font-size: 1.2rem;
        }

        .message-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.85rem;
            color: #6c757d;
        }

        .message-content {
            line-height: 1.6;
        }

        .message-email {
            margin-bottom: 10px;
            font-size: 0.9rem;
        }

        .message-text {
            font-size: 0.95rem;
            color: #495057;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-primary { background: #0355cc; color: white; }
        .badge-success { background: #2ecc71; color: white; }
        .badge-warning { background: #f39c12; color: white; }
        .badge-danger { background: #e74c3c; color: white; }
        .badge-secondary { background: #95a5a6; color: white; }

        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .message-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border: 1px solid #e9ecef;
            transition: transform 0.2s ease;
        }

        .message-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .message-sender {
            font-weight: 600;
            color: #333;
        }

        .logout-btn {
            background: rgba(255, 99, 99, 0.1);
            color: #ff6363;
            border: 1px solid rgba(255, 99, 99, 0.3);
        }

        .logout-btn:hover {
            background: rgba(255, 99, 99, 0.2);
            color: #ff4757;
            border: 1px solid rgba(255, 99, 99, 0.5);
        }

        @media (max-width: 1200px) {
            .admin-container {
                margin: 0 10px;
            }
            
            .admin-sidebar {
                width: 250px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .admin-header {
                padding: 1rem;
            }
            
            .admin-container {
                flex-direction: column;
                margin: 0;
            }
            
            .admin-sidebar {
                width: 100%;
                margin: 10px;
                position: static;
                border-radius: 15px;
                padding: 20px 0;
            }
            
            .admin-content {
                margin: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .header-content {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
            
            .admin-user-info {
                justify-content: center;
            }
            
            .data-table {
                overflow-x: auto;
            }
            
            .table {
                min-width: 600px;
            }
            
            .form-container {
                padding: 20px;
            }
            
            .nav-link {
                padding: 12px 15px;
                font-size: 14px;
            }
            
            .stat-card {
                padding: 20px;
            }
            
            .stat-number {
                font-size: 1.8rem;
            }
        }

        @media (max-width: 480px) {
            .admin-header {
                padding: 0.8rem;
            }
            
            .admin-logo {
                font-size: 1.2rem;
            }
            
            .stats-grid {
                gap: 10px;
            }
            
            .stat-card {
                padding: 15px;
            }
            
            .stat-number {
                font-size: 1.5rem;
            }
            
            .form-container {
                padding: 15px;
                margin-bottom: 15px;
            }
            
            .btn {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .section-title {
                font-size: 1.3rem;
                margin-bottom: 15px;
            }
            
            .messages-grid {
                grid-template-columns: 1fr;
            }
            
            .message-card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <div class="admin-header">
        <div class="header-content">
            <div class="admin-logo">
                <i class="fas fa-heart"></i>
                <span>Admin Paneli</span>
            </div>
            <div class="admin-user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($kullanici_adi, 0, 1)); ?>
                </div>
                <span>Hoş geldiniz, <?php echo htmlspecialchars($kullanici_adi); ?></span>
                <a href="panel.php" class="btn btn-outline">
                    <i class="fas fa-home"></i> Ana Sayfa
                </a>
                <a href="cikis-yap.php" class="btn logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Çıkış
                </a>
            </div>
        </div>
    </div>

    <div class="admin-container">
        <!-- Sidebar -->
        <div class="admin-sidebar">
            <nav>
                <div class="nav-item">
                    <a class="nav-link active" href="#" onclick="showTab('dashboard')">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" onclick="showTab('products')">
                        <i class="fas fa-box"></i>
                        <span>Ürün Yönetimi</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" onclick="showTab('users')">
                        <i class="fas fa-users"></i>
                        <span>Kullanıcılar</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a class="nav-link" href="#" onclick="showTab('orders')">
                        <i class="fas fa-shopping-cart"></i>
                        <span>Sipariş Yönetimi</span>
                    </a>
                </div>

            </nav>
        </div>

        <!-- Main Content -->
        <div class="admin-content">
            <!-- Alerts -->
            <?php if($hata): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($hata) ?>
                </div>
            <?php endif; ?>

            <?php if($basari): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($basari) ?>
                </div>
            <?php endif; ?>

            <!-- Dashboard Tab -->
            <div class="content-section active" id="dashboard">
                <div class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    <span>Dashboard</span>
                </div>
                
                <div class="stats-grid">
                    <div class="stat-card users" onclick="showTab('users')">
                        <div class="stat-header">
                            <div>
                                <div class="stat-number" data-count="<?= $user_count ?>">0</div>
                                <div class="stat-label">Toplam Kullanıcı</div>
                            </div>
                            <div class="stat-icon users">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" style="width: <?= min(100, ($user_count / 50) * 100) ?>%"></div>
                        </div>
                        <div class="stat-footer">
                            <small><i class="fas fa-arrow-up"></i> Aktif kullanıcılar</small>
                        </div>
                    </div>
                    
                    <div class="stat-card products" onclick="showTab('products')">
                        <div class="stat-header">
                            <div>
                                <div class="stat-number" data-count="<?= $product_count ?>">0</div>
                                <div class="stat-label">Toplam Ürün</div>
                            </div>
                            <div class="stat-icon products">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" style="width: <?= min(100, ($product_count / 20) * 100) ?>%"></div>
                        </div>
                        <div class="stat-footer">
                            <small><i class="fas fa-plus"></i> Ürün kataloğu</small>
                        </div>
                    </div>
                    
                    <div class="stat-card stock" onclick="showTab('products')">
                        <div class="stat-header">
                            <div>
                                <div class="stat-number" data-count="<?= $low_stock ?>">0</div>
                                <div class="stat-label">Düşük Stok</div>
                            </div>
                            <div class="stat-icon stock">
                                <i class="fas fa-exclamation-triangle"></i>
                            </div>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" style="width: <?= $low_stock > 0 ? min(100, ($low_stock / 5) * 100) : 0 ?>%"></div>
                        </div>
                        <div class="stat-footer">
                            <small><i class="fas fa-warning"></i> Dikkat gerekli</small>
                        </div>
                    </div>
                    
                    <div class="stat-card orders" onclick="showTab('orders')">
                        <div class="stat-header">
                            <div>
                                <div class="stat-number" data-count="<?= $order_count ?>">0</div>
                                <div class="stat-label">Toplam Sipariş</div>
                            </div>
                            <div class="stat-icon orders">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                        <div class="stat-progress">
                            <div class="progress-bar" style="width: <?= min(100, ($order_count / 10) * 100) ?>%"></div>
                        </div>
                        <div class="stat-footer">
                            <small><i class="fas fa-chart-line"></i> Sipariş takibi</small>
                        </div>
                    </div>

                </div>

                <div class="form-container">
                    <h5 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-server"></i> Sistem Durumu
                    </h5>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                        <div>
                            <strong>Veritabanı Bağlantısı:</strong>
                            <span class="badge badge-success">Aktif</span>
                        </div>
                        <div>
                            <strong>Sistem Durumu:</strong>
                            <span class="badge badge-success">Çalışıyor</span>
                        </div>
                    </div>
                </div>


            </div>

            <!-- Products Tab -->
            <div class="content-section" id="products">
                <div class="section-title">
                    <i class="fas fa-box"></i>
                    <span>Ürün Yönetimi</span>
                </div>
                
                <div class="form-container">
                    <h5 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-plus"></i> Yeni Ürün Ekle
                    </h5>
                    <form method="POST" enctype="multipart/form-data">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                            <div class="form-group">
                                <label class="form-label" for="isim">Ürün Adı</label>
                                <input type="text" class="form-control" id="isim" name="isim" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="kategori">Kategori</label>
                                <select class="form-control" id="kategori" name="kategori" required>
                                    <option value="">Kategori Seçin</option>
                                    <option value="Gıda">Gıda</option>
                                    <option value="Su">Su</option>
                                    <option value="İlk Yardım">İlk Yardım</option>
                                    <option value="Araçlar">Araçlar</option>
                                    <option value="Giyim">Giyim</option>
                                    <option value="Elektronik">Elektronik</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="stok_durumu">Stok Durumu</label>
                                <select class="form-control" id="stok_durumu" name="stok_durumu" required>
                                    <option value="">Stok Durumu Seçin</option>
                                    <option value="Var">Var</option>
                                    <option value="Yok">Yok</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="resim">Ürün Resmi</label>
                            <input type="file" class="form-control" id="resim" name="resim" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="aciklama">Açıklama</label>
                            <textarea class="form-control" id="aciklama" name="aciklama" rows="3"></textarea>
                        </div>
                        <button type="submit" name="urun_ekle" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ürün Ekle
                        </button>
                    </form>
                </div>

                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Resim</th>
                                <th>Ürün Adı</th>
                                <th>Kategori</th>
                                <th>Stok Durumu</th>
                                <th>İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($urunler as $urun): ?>
                            <tr>
                                <td>
                                    <?php
                                    // Ürün adına göre simge belirleme
                                    $admin_icon = 'fas fa-box'; // varsayılan
                                    $admin_iconColor = '#007bff'; // varsayılan mavi
                                    
                                    if (stripos($urun['urun_adi'], 'gıda') !== false || stripos($urun['urun_adi'], 'yiyecek') !== false) {
                                        $admin_icon = 'fas fa-utensils';
                                        $admin_iconColor = '#28a745';
                                    } elseif (stripos($urun['urun_adi'], 'su') !== false) {
                                        $admin_icon = 'fas fa-tint';
                                        $admin_iconColor = '#17a2b8';
                                    } elseif (stripos($urun['urun_adi'], 'battaniye') !== false) {
                                        $admin_icon = 'fas fa-bed';
                                        $admin_iconColor = '#6f42c1';
                                    } elseif (stripos($urun['urun_adi'], 'çadır') !== false) {
                                        $admin_icon = 'fas fa-campground';
                                        $admin_iconColor = '#fd7e14';
                                    } elseif (stripos($urun['urun_adi'], 'fener') !== false) {
                                        $admin_icon = 'fas fa-bolt';
                                        $admin_iconColor = '#ffc107';
                                    } elseif (stripos($urun['urun_adi'], 'hijyen') !== false) {
                                        $admin_icon = 'fas fa-soap';
                                        $admin_iconColor = '#20c997';
                                    } elseif (stripos($urun['urun_adi'], 'ısıtıcı') !== false) {
                                        $admin_icon = 'fas fa-fire';
                                        $admin_iconColor = '#dc3545';
                                    } elseif (stripos($urun['urun_adi'], 'yağmurluk') !== false) {
                                        $admin_icon = 'fas fa-tshirt';
                                        $admin_iconColor = '#6c757d';
                                    } elseif (stripos($urun['urun_adi'], 'düdük') !== false) {
                                        $admin_icon = 'fas fa-bullhorn';
                                        $admin_iconColor = '#e83e8c';
                                    } elseif (stripos($urun['urun_adi'], 'ayakkabı') !== false) {
                                        $admin_icon = 'fas fa-shoe-prints';
                                        $admin_iconColor = '#8B4513';
                                    } elseif (stripos($urun['urun_adi'], 'çorap') !== false) {
                                        $admin_icon = 'fas fa-socks';
                                        $admin_iconColor = '#FF6347';
                                    } elseif (stripos($urun['urun_adi'], 'mont') !== false) {
                                        $admin_icon = 'fas fa-user-tie';
                                        $admin_iconColor = '#2E8B57';
                                    } elseif (stripos($urun['urun_adi'], 'pantolon') !== false) {
                                        $admin_icon = 'fas fa-tshirt';
                                        $admin_iconColor = '#4169E1';
                                    }
                                    ?>
                                    <div class="product-image" style="display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(240,240,240,0.9)); border-radius: 8px; width: 60px; height: 60px;">
                                        <i class="<?= $admin_icon ?>" style="font-size: 1.8rem; color: <?= $admin_iconColor ?>; text-shadow: 0 2px 4px rgba(0,0,0,0.1);"></i>
                                    </div>
                                </td>
                                <td><?= htmlspecialchars($urun['urun_adi'] ?? '') ?></td>
                                <td>
                                    <span class="badge badge-secondary">
                                        <?= htmlspecialchars($urun['kategori'] ?? '') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-success">Var</span>
                                </td>
                                <td>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="sil_id" value="<?= $urun['id'] ?>">
                                        <button type="submit" class="btn btn-danger" 
                                                style="padding: 8px 12px; font-size: 0.8rem;"
                                                onclick="return confirm('Bu ürünü silmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Users Tab -->
            <div class="content-section" id="users">
                <div class="section-title">
                    <i class="fas fa-users"></i>
                    <span>Kullanıcı Yönetimi</span>
                </div>
                
                <div class="data-table">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Kullanıcı Adı</th>
                                <th>E-posta</th>
                                <th>Telefon</th>
                                <th>Rol</th>
                                <th>Kayıt Tarihi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['id'] ?? '') ?></td>
                                <td>
                                    <i class="fas fa-user-circle text-primary"></i>
                                    <?= htmlspecialchars($user['username'] ?? '') ?>
                                </td>
                                <td><?= htmlspecialchars($user['email'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['telefon'] ?? '') ?></td>
                                <td>
                                    <?php 
                                    $rol = $user['rol'] ?? 'kullanici';
                                    $badge_class = $rol == 'admin' ? 'badge-primary' : 'badge-secondary';
                                    ?>
                                    <span class="badge <?= $badge_class ?>">
                                        <?= $rol == 'admin' ? 'Admin' : 'Kullanıcı' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $kayit_tarihi = $user['kayit_tarihi'] ?? null;
                                    if ($kayit_tarihi) {
                                        $tarih = new DateTime($kayit_tarihi);
                                        echo $tarih->format('d.m.Y H:i');
                                    } else {
                                        echo 'Bilinmiyor';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Orders Tab -->
            <div class="content-section" id="orders">
                <div class="section-title">
                    <i class="fas fa-shopping-cart"></i>
                    <span>Sipariş Yönetimi</span>
                </div>

                <div class="form-container">
                    <h5 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-list"></i> Tüm Siparişler
                    </h5>
                    
                    <?php if (empty($siparisler)): ?>
                        <div style="text-align: center; padding: 40px; color: #666; background: #f8f9fa; border-radius: 12px; border: 2px dashed #ddd;">
                            <i class="fas fa-shopping-cart" style="font-size: 48px; margin-bottom: 15px; color: #ccc;"></i>
                            <h4>Henüz Sipariş Yok</h4>
                            <p>Sistem henüz hiç sipariş almamış.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Sipariş #</th>
                                        <th>Müşteri</th>
                                        <th>Ürünler</th>
                                        <th>Tutar</th>
                                        <th>Durum</th>
                                        <th>Tarih</th>
                                        <th>İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($siparisler as $siparis): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?= htmlspecialchars($siparis['id']) ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?= htmlspecialchars($siparis['username'] ?? 'Bilinmiyor') ?></strong><br>
                                                <small class="text-muted"><?= htmlspecialchars($siparis['email'] ?? '') ?></small><br>
                                                <small class="text-muted"><?= htmlspecialchars($siparis['telefon'] ?? '') ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars($siparis['urunler'] ?? 'Ürün bilgisi yok') ?></small>
                                        </td>
                                        <td>
                                            <strong style="color: #28a745;">ÜCRETSIZ</strong>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="siparis_id" value="<?= $siparis['id'] ?>">
                                                <select name="yeni_durum" onchange="this.form.submit()" class="form-control form-control-sm" style="width: auto; display: inline-block;">
                                                    <option value="Beklemede" <?= $siparis['siparis_durumu'] == 'Beklemede' ? 'selected' : '' ?>>Beklemede</option>
                                                    <option value="Onaylandı" <?= $siparis['siparis_durumu'] == 'Onaylandı' ? 'selected' : '' ?>>Onaylandı</option>
                                                    <option value="Hazırlanıyor" <?= $siparis['siparis_durumu'] == 'Hazırlanıyor' ? 'selected' : '' ?>>Hazırlanıyor</option>
                                                    <option value="Kargoda" <?= $siparis['siparis_durumu'] == 'Kargoda' ? 'selected' : '' ?>>Kargoda</option>
                                                    <option value="Teslim Edildi" <?= $siparis['siparis_durumu'] == 'Teslim Edildi' ? 'selected' : '' ?>>Teslim Edildi</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td>
                                            <?php 
                                            $tarih = new DateTime($siparis['siparis_tarihi']);
                                            echo $tarih->format('d.m.Y H:i');
                                            ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" onclick="showOrderDetails(<?= $siparis['id'] ?>)">
                                                <i class="fas fa-eye"></i> Detay
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Tab navigation
        function showTab(tabName) {
            // Hide all content sections
            const sections = document.querySelectorAll('.content-section');
            sections.forEach(section => {
                section.classList.remove('active');
            });
            
            // Remove active class from all nav items
            const navItems = document.querySelectorAll('.nav-link');
            navItems.forEach(item => {
                item.classList.remove('active');
            });
            
            // Show selected section
            const selectedSection = document.getElementById(tabName);
            if (selectedSection) {
                selectedSection.classList.add('active');
            }
            
            // Add active class to clicked nav item
            const activeNavItem = document.querySelector(`[onclick="showTab('${tabName}')"]`);
            if (activeNavItem) {
                activeNavItem.classList.add('active');
            }
        }

        // Number animation function
        function animateNumber(element, target, duration = 2000) {
            const start = 0;
            const increment = target / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                element.textContent = Math.floor(current);
            }, 16);
        }

        // Initialize dashboard on page load
        document.addEventListener('DOMContentLoaded', function() {
            showTab('dashboard');
            
            // Animate stat numbers
            setTimeout(() => {
                const statNumbers = document.querySelectorAll('.stat-number[data-count]');
                statNumbers.forEach(element => {
                    const target = parseInt(element.getAttribute('data-count'));
                    animateNumber(element, target);
                });
            }, 500);
            
            // Auto hide alerts
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(alert => {
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 300);
                });
            }, 5000);

            // Add hover effects to stat cards
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-8px) scale(1.02)';
                });
                
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
        });

        // Quick actions
        function quickAddProduct() {
            showTab('products');
            document.getElementById('isim').focus();
        }

        function quickViewUsers() {
            showTab('users');
        }

        function quickViewMessages() {
            showTab('messages');
        }

        // Sipariş detaylarını göster
        function showOrderDetails(orderId) {
            // Modal oluştur
            const modal = document.createElement('div');
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex;
                justify-content: center;
                align-items: center;
                z-index: 1000;
            `;
            
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: white;
                padding: 30px;
                border-radius: 12px;
                max-width: 600px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            `;
            
            modalContent.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                    <h4 style="margin: 0; color: #333;"><i class="fas fa-shopping-cart"></i> Sipariş Detayları #${orderId}</h4>
                    <button onclick="this.closest('.modal').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
                </div>
                <div style="text-align: center; padding: 40px; color: #666;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 32px; margin-bottom: 15px;"></i>
                    <p>Sipariş detayları yükleniyor...</p>
                </div>
            `;
            
            modal.className = 'modal';
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Modal dışına tıklanınca kapat
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
            
            // AJAX ile sipariş detaylarını getir
            fetch(`get_order_details.php?id=${orderId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const order = data.order;
                        modalContent.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                                <h4 style="margin: 0; color: #333;"><i class="fas fa-shopping-cart"></i> Sipariş Detayları #${orderId}</h4>
                                <button onclick="this.closest('.modal').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
                                <div>
                                    <h6 style="color: #666; margin-bottom: 10px;"><i class="fas fa-user"></i> Müşteri Bilgileri</h6>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                        <p style="margin: 5px 0;"><strong>Ad:</strong> ${order.username || 'Bilinmiyor'}</p>
                                        <p style="margin: 5px 0;"><strong>E-posta:</strong> ${order.email || 'Bilinmiyor'}</p>
                                        <p style="margin: 5px 0;"><strong>Telefon:</strong> ${order.telefon || 'Bilinmiyor'}</p>
                                    </div>
                                </div>
                                
                                <div>
                                    <h6 style="color: #666; margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Sipariş Bilgileri</h6>
                                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                        <p style="margin: 5px 0;"><strong>Durum:</strong> <span style="color: #007bff;">${order.siparis_durumu}</span></p>
                                        <p style="margin: 5px 0;"><strong>Tarih:</strong> ${new Date(order.siparis_tarihi).toLocaleString('tr-TR')}</p>
                                        <p style="margin: 5px 0;"><strong>Tutar:</strong> <span style="color: #28a745; font-weight: bold;">ÜCRETSIZ</span></p>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <h6 style="color: #666; margin-bottom: 15px;"><i class="fas fa-box"></i> Sipariş Edilen Ürünler</h6>
                                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px;">
                                    ${order.urunler || 'Ürün bilgisi bulunamadı'}
                                </div>
                            </div>
                            
                            <div style="margin-top: 25px; text-align: center;">
                                <button onclick="this.closest('.modal').remove()" style="background: #6c757d; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer;">
                                    <i class="fas fa-times"></i> Kapat
                                </button>
                            </div>
                        `;
                    } else {
                        modalContent.innerHTML = `
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                                <h4 style="margin: 0; color: #333;"><i class="fas fa-shopping-cart"></i> Sipariş Detayları #${orderId}</h4>
                                <button onclick="this.closest('.modal').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
                            </div>
                            <div style="text-align: center; padding: 40px; color: #dc3545;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 32px; margin-bottom: 15px;"></i>
                                <p>Sipariş detayları yüklenirken hata oluştu.</p>
                                <button onclick="this.closest('.modal').remove()" style="background: #6c757d; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; margin-top: 15px;">
                                    Kapat
                                </button>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    modalContent.innerHTML = `
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #f0f0f0; padding-bottom: 15px;">
                            <h4 style="margin: 0; color: #333;"><i class="fas fa-shopping-cart"></i> Sipariş Detayları #${orderId}</h4>
                            <button onclick="this.closest('.modal').remove()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #999;">&times;</button>
                        </div>
                        <div style="text-align: center; padding: 40px; color: #dc3545;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 32px; margin-bottom: 15px;"></i>
                            <p>Sipariş detayları yüklenirken hata oluştu.</p>
                            <button onclick="this.closest('.modal').remove()" style="background: #6c757d; color: white; border: none; padding: 10px 25px; border-radius: 6px; cursor: pointer; margin-top: 15px;">
                                Kapat
                            </button>
                        </div>
                    `;
                });
        }
    </script>
</body>
</html>
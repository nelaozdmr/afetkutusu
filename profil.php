<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Oturum kontrolü
if (!isset($_SESSION['oturum'])) {
    header("Location: giris-yap.php");
    exit();
}

// Kullanıcı bilgilerini al
$kullanici = isset($_SESSION['ad']) ? $_SESSION['ad'] : null;
$kullanici_rol = $_SESSION['rol'] ?? 'kullanici';
$kullanici_adi = $_SESSION['ad'] ?? 'Misafir';

// Veritabanı bağlantısı
try {
    $host = 'localhost';
    $port = '3307';
    $db   = 'afetkutusu';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Kullanıcı bilgilerini veritabanından al
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$kullanici]);
    $user = $stmt->fetch();
    
    if ($user) {
        $kullanici_bilgileri = [
            $user['username'],
            $user['password'],
            $user['ad'],
            $user['soyad'],
            $user['tc'],
            $user['telefon'],
            $user['adres'],
            $user['evhasar'],
            $user['ailesayisi'],
            $user['dogum_tarihi'],
            $user['kan_grubu'],
            $user['kronik_hastalik'],
            $user['kullandigi_ilaclar']
        ];
    }
} catch (PDOException $e) {
    // Veritabanı bağlantısı başarısız olursa users.txt dosyasını kullan
    $dosya = __DIR__ . "/users.txt";
    $kullanici_bilgileri = [];

    $lines = file($dosya, FILE_IGNORE_NEW_LINES);
    foreach ($lines as $line) {
        $parts = explode("|", $line);
        if ($parts[0] == $kullanici) {
            $kullanici_bilgileri = $parts;
            break;
        }
    }
}

// Form işlemleri
$success_message = '';
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Şifre değiştirme formu
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'sifre_degistir') {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($kullanici_bilgileri)) {
            $error_message = "Kullanıcı bilgileri bulunamadı!";
        } 
        else if ($new_password == $confirm_password) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE username = ?");
                $stmt->execute([$new_password, $kullanici]);
                
                if ($stmt->rowCount() > 0) {
                    $success_message = "Şifreniz başarıyla değiştirildi!";
                } else {
                    $error_message = "Şifre değiştirilemedi.";
                }
            } catch (PDOException $e) {
                $error_message = "Veritabanı hatası: " . $e->getMessage();
            }
        } else {
            $error_message = "Yeni şifreler eşleşmiyor!";
        }
    }
    
    // Profil güncelleme formu
    if (isset($_POST['form_type']) && $_POST['form_type'] == 'profil_guncelle') {
        $ad = $_POST['ad'];
        $soyad = $_POST['soyad'];
        $tc = $_POST['tc'];
        $telefon = $_POST['telefon'];
        $adres = $_POST['adres'];
        $ev_hasar = $_POST['ev_hasar'];
        $aile_sayisi = $_POST['aile_sayisi'];
        $dogum_tarihi = $_POST['dogum_tarihi'];
        $kan_grubu = $_POST['kan_grubu'];
        $kronik_hastalik = $_POST['kronik_hastalik'];
        $kullandigi_ilaclar = $_POST['kullandigi_ilaclar'];
        
        // PDO bağlantısı varsa veritabanını güncelle
        if (isset($pdo)) {
            try {
                $stmt = $pdo->prepare("UPDATE users SET ad = ?, soyad = ?, tc = ?, telefon = ?, adres = ?, evhasar = ?, ailesayisi = ?, dogum_tarihi = ?, kan_grubu = ?, kronik_hastalik = ?, kullandigi_ilaclar = ? WHERE username = ?");
                $stmt->execute([$ad, $soyad, $tc, $telefon, $adres, $ev_hasar, $aile_sayisi, $dogum_tarihi, $kan_grubu, $kronik_hastalik, $kullandigi_ilaclar, $kullanici]);
            
            if ($stmt->rowCount() > 0) {
                $success_message = "Profiliniz başarıyla güncellendi!";
                // Güncellenmiş bilgileri tekrar al
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
                $stmt->execute([$kullanici]);
                $user = $stmt->fetch();
                
                if ($user) {
                    $kullanici_bilgileri = [
                        $user['username'],
                        $user['password'],
                        $user['ad'],
                        $user['soyad'],
                        $user['tc'],
                        $user['telefon'],
                        $user['adres'],
                        $user['evhasar'],
                        $user['ailesayisi'],
                        $user['dogum_tarihi'],
                        $user['kan_grubu'],
                        $user['kronik_hastalik'],
                        $user['kullandigi_ilaclar']
                    ];
                }
            } else {
                $error_message = "Profil güncellenemedi.";
            }
        } catch (PDOException $e) {
            $error_message = "Veritabanı hatası: " . $e->getMessage();
        }
        } else {
            // PDO bağlantısı yoksa dosya tabanlı güncelleme
            $dosya = __DIR__ . "/users.txt";
            $lines = file($dosya, FILE_IGNORE_NEW_LINES);
            $updated = false;
            
            for ($i = 0; $i < count($lines); $i++) {
                $parts = explode("|", $lines[$i]);
                if ($parts[0] == $kullanici) {
                    $lines[$i] = $kullanici . "|" . $parts[1] . "|" . $ad . "|" . $soyad . "|" . $tc . "|" . $telefon . "|" . $adres . "|" . $ev_hasar . "|" . $aile_sayisi . "|" . $dogum_tarihi . "|" . $kan_grubu . "|" . $kronik_hastalik . "|" . $kullandigi_ilaclar;
                    $updated = true;
                    
                    // Güncellenmiş bilgileri diziye al
                    $kullanici_bilgileri = [
                        $kullanici,
                        $parts[1], // şifre
                        $ad,
                        $soyad,
                        $tc,
                        $telefon,
                        $adres,
                        $ev_hasar,
                        $aile_sayisi,
                        $dogum_tarihi,
                        $kan_grubu,
                        $kronik_hastalik,
                        $kullandigi_ilaclar
                    ];
                    break;
                }
            }
            
            if ($updated) {
                file_put_contents($dosya, implode("\n", $lines));
                $success_message = "Profiliniz başarıyla güncellendi!";
            } else {
                $error_message = "Profil güncellenemedi.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - AFET KUTUSU</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

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
        }

        .container {
            max-width: 100%;
            margin: 0;
            padding: 0;
        }

        /* Header */
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
            padding: 12px 20px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255, 140, 66, 0.1), rgba(255, 165, 100, 0.2));
            border: 2px solid transparent;
            background-clip: padding-box;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
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
            color: #ff4757;
            border: 1px solid rgba(255, 99, 99, 0.5);
            box-shadow: 0 8px 25px rgba(255, 99, 99, 0.4);
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 25px;
            justify-items: stretch;
            max-width: 100%;
            margin: 25px 10px 0 10px;
            padding: 0;
        }

        @media (max-width: 1200px) {
            .main-content {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }

        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 15px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
        }

        .card-title {
            color: #000000;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .card-title i {
            color: #000000;
        }

        /* Profile Info Styles */
        .profile-info {
            margin-top: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid rgba(0, 0, 0, 0.1);
        }

        .profile-avatar {
            font-size: 60px;
            color: #000000;
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.1), rgba(0, 0, 0, 0.05));
            border-radius: 50%;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .profile-basic h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #000000;
        }

        .profile-basic .username {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }

        .profile-details {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .detail-row .full-width {
            grid-column: 1 / -1;
        }

        .detail-item {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            padding: 15px;
            background: rgba(0, 0, 0, 0.03);
            border-radius: 12px;
            border-left: 4px solid #000000;
        }

        .detail-item i {
            font-size: 18px;
            color: #000000;
            margin-top: 2px;
            min-width: 18px;
        }

        .detail-content {
            flex: 1;
        }

        .detail-content label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
        }

        .detail-content span {
            display: block;
            font-size: 14px;
            color: #000000;
            font-weight: 500;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-hasarsız {
            background: #d4edda;
            color: #155724;
        }

        .status-az-hasarlı {
            background: #fff3cd;
            color: #856404;
        }

        .status-orta-hasarlı {
            background: #ffeaa7;
            color: #b8860b;
        }

        .status-ağır-hasarlı {
            background: #f8d7da;
            color: #721c24;
        }

        .status-yıkık {
            background: #f5c6cb;
            color: #721c24;
        }

        .status-belirtilmemis {
            background: #e2e3e5;
            color: #6c757d;
        }

        @media (max-width: 768px) {
            .detail-row {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 12px;
        }

        .form-group label {
            display: block;
            margin-bottom: 4px;
            color: #000000;
            font-weight: 500;
            font-size: 12px;
        }

        .form-control {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d2e3fc;
            border-radius: 8px;
            font-size: 12px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control:focus {
            outline: none;
            border-color: #1a73e8;
            box-shadow: 0 0 0 1px rgba(26, 115, 232, 0.1);
            background: white;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 16px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #d2e3fc, #1a73e8);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 115, 232, 0.4);
            background: linear-gradient(135deg, #1a73e8, #1557b0);
        }

        .btn-success {
            background: linear-gradient(135deg, #81c784, #66bb6a);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(129, 199, 132, 0.4);
            background: linear-gradient(135deg, #66bb6a, #4caf50);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(129, 199, 132, 0.1);
            border: 1px solid rgba(129, 199, 132, 0.3);
            color: #2e7d32;
        }

        .alert-danger {
            background: rgba(239, 83, 80, 0.1);
            border: 1px solid rgba(239, 83, 80, 0.3);
            color: #c62828;
        }

        /* Info Display */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            background: rgba(210, 227, 252, 0.5);
            padding: 15px;
            border-radius: 12px;
            border-left: 4px solid #1a73e8;
        }

        .info-label {
            font-size: 12px;
            color: #000000;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 14px;
            color: #000000;
            font-weight: 600;
        }

        /* Responsive */
        @media (max-width: 1200px) {
            .card {
                max-width: 100% !important;
                width: 100% !important;
            }
        }

        @media (max-width: 768px) {
            .header-content {
                flex-direction: column;
                text-align: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .nav-buttons {
                justify-content: center;
            }

            .container {
                padding: 15px;
            }

            .card {
                padding: 12px;
                max-width: 100% !important;
                margin: 0 !important;
                width: 100% !important;
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

        .card {
            animation: fadeInUp 0.6s ease forwards;
        }

        .card:nth-child(2) {
            animation-delay: 0.1s;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
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
                        <li><a href="siparisler.php" class="nav-link"><i class="fas fa-list"></i> Siparişlerim</a></li>
                        <?php endif; ?>
                        <li><a href="profil.php" class="nav-link active"><i class="fas fa-user"></i> Profil</a></li>
                        <?php if ($kullanici_rol === 'admin'): ?>
                            <li><a href="admin-panel.php" class="nav-link"><i class="fas fa-cog"></i> Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="cikis-yap.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Alert Messages -->
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Profil Bilgilerini Görüntüleme -->
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-user-circle"></i>
                    Profilim
                </h3>
                
                <div class="profile-info">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="profile-basic">
                            <h4><?= htmlspecialchars(($kullanici_bilgileri[2] ?? '') . ' ' . ($kullanici_bilgileri[3] ?? '')) ?></h4>
                            <p class="username">@<?= htmlspecialchars($kullanici ?? '') ?></p>
                        </div>
                    </div>
                    
                    <div class="profile-details">
                        <div class="detail-row">
                            <div class="detail-item">
                                <i class="fas fa-id-card"></i>
                                <div class="detail-content">
                                    <label>TC Kimlik No</label>
                                    <span><?= htmlspecialchars($kullanici_bilgileri[4] ?? 'Belirtilmemiş') ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-phone"></i>
                                <div class="detail-content">
                                    <label>Telefon</label>
                                    <span><?= htmlspecialchars($kullanici_bilgileri[5] ?? 'Belirtilmemiş') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item full-width">
                                <i class="fas fa-map-marker-alt"></i>
                                <div class="detail-content">
                                    <label>Adres</label>
                                    <span><?= htmlspecialchars($kullanici_bilgileri[6] ?? 'Belirtilmemiş') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item">
                                <i class="fas fa-home"></i>
                                <div class="detail-content">
                                    <label>Ev Hasar Durumu</label>
                                    <span class="status-badge status-<?= strtolower(str_replace(' ', '-', $kullanici_bilgileri[7] ?? 'belirtilmemis')) ?>">
                                        <?= htmlspecialchars($kullanici_bilgileri[7] ?? 'Belirtilmemiş') ?>
                                    </span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-users"></i>
                                <div class="detail-content">
                                    <label>Aile Sayısı</label>
                                    <span><?= htmlspecialchars($kullanici_bilgileri[8] ?? 'Belirtilmemiş') ?> kişi</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item">
                                <i class="fas fa-birthday-cake"></i>
                                <div class="detail-content">
                                    <label>Doğum Tarihi</label>
                                    <span><?= htmlspecialchars($kullanici_bilgileri[9] ?? 'Belirtilmemiş') ?></span>
                                </div>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tint"></i>
                                <div class="detail-content">
                                    <label>Kan Grubu</label>
                                    <span class="blood-type"><?= htmlspecialchars($kullanici_bilgileri[10] ?? 'Bilinmiyor') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item full-width">
                                <i class="fas fa-heartbeat"></i>
                                <div class="detail-content">
                                    <label>Kronik Hastalıklar</label>
                                    <span><?= htmlspecialchars($kullanici_bilgileri[11] ?? 'Belirtilmemiş') ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="detail-row">
                            <div class="detail-item full-width">
                                <i class="fas fa-pills"></i>
                                <div class="detail-content">
                                    <label>Kullandığı İlaçlar</label>
                                    <span><?= htmlspecialchars($kullanici_bilgileri[12] ?? 'Belirtilmemiş') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profil Düzenleme -->
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-user-edit"></i>
                    Profil Düzenle
                </h3>

                <!-- Güncelleme Formu -->
                <form method="post">
                    <input type="hidden" name="form_type" value="profil_guncelle">
                    
                    <div class="info-grid">
                        <div class="form-group">
                            <label for="ad">Ad</label>
                            <input type="text" class="form-control" id="ad" name="ad" 
                                   value="<?= htmlspecialchars($kullanici_bilgileri[2] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="soyad">Soyad</label>
                            <input type="text" class="form-control" id="soyad" name="soyad" 
                                   value="<?= htmlspecialchars($kullanici_bilgileri[3] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="tc">TC Kimlik No</label>
                            <input type="text" class="form-control" id="tc" name="tc" 
                                   value="<?= htmlspecialchars($kullanici_bilgileri[4] ?? '') ?>" maxlength="11">
                        </div>
                        <div class="form-group">
                            <label for="telefon">Telefon</label>
                            <input type="tel" class="form-control" id="telefon" name="telefon" 
                                   value="<?= htmlspecialchars($kullanici_bilgileri[5] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="ev_hasar">Ev Hasar Durumu</label>
                            <select class="form-control" id="ev_hasar" name="ev_hasar">
                                <option value="">Seçiniz</option>
                                <option value="Hasarsız" <?= ($kullanici_bilgileri[7] ?? '') == 'Hasarsız' ? 'selected' : '' ?>>Hasarsız</option>
                                <option value="Az Hasarlı" <?= ($kullanici_bilgileri[7] ?? '') == 'Az Hasarlı' ? 'selected' : '' ?>>Az Hasarlı</option>
                                <option value="Orta Hasarlı" <?= ($kullanici_bilgileri[7] ?? '') == 'Orta Hasarlı' ? 'selected' : '' ?>>Orta Hasarlı</option>
                                <option value="Ağır Hasarlı" <?= ($kullanici_bilgileri[7] ?? '') == 'Ağır Hasarlı' ? 'selected' : '' ?>>Ağır Hasarlı</option>
                                <option value="Yıkık" <?= ($kullanici_bilgileri[7] ?? '') == 'Yıkık' ? 'selected' : '' ?>>Yıkık</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="aile_sayisi">Aile Sayısı</label>
                            <input type="number" class="form-control" id="aile_sayisi" name="aile_sayisi" 
                                   value="<?= htmlspecialchars($kullanici_bilgileri[8] ?? '') ?>" min="1">
                        </div>
                        <div class="form-group">
                            <label for="dogum_tarihi">Doğum Tarihi</label>
                            <input type="date" class="form-control" id="dogum_tarihi" name="dogum_tarihi" 
                                   value="<?= htmlspecialchars($kullanici_bilgileri[9] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label for="kan_grubu">Kan Grubu</label>
                            <select class="form-control" id="kan_grubu" name="kan_grubu">
                                <option value="Bilinmiyor" <?= ($kullanici_bilgileri[10] ?? '') == 'Bilinmiyor' ? 'selected' : '' ?>>Bilinmiyor</option>
                                <option value="A+" <?= ($kullanici_bilgileri[10] ?? '') == 'A+' ? 'selected' : '' ?>>A+</option>
                                <option value="A-" <?= ($kullanici_bilgileri[10] ?? '') == 'A-' ? 'selected' : '' ?>>A-</option>
                                <option value="B+" <?= ($kullanici_bilgileri[10] ?? '') == 'B+' ? 'selected' : '' ?>>B+</option>
                                <option value="B-" <?= ($kullanici_bilgileri[10] ?? '') == 'B-' ? 'selected' : '' ?>>B-</option>
                                <option value="AB+" <?= ($kullanici_bilgileri[10] ?? '') == 'AB+' ? 'selected' : '' ?>>AB+</option>
                                <option value="AB-" <?= ($kullanici_bilgileri[10] ?? '') == 'AB-' ? 'selected' : '' ?>>AB-</option>
                                <option value="0+" <?= ($kullanici_bilgileri[10] ?? '') == '0+' ? 'selected' : '' ?>>0+</option>
                                <option value="0-" <?= ($kullanici_bilgileri[10] ?? '') == '0-' ? 'selected' : '' ?>>0-</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="adres">Adres</label>
                        <textarea class="form-control" id="adres" name="adres" rows="3"><?= htmlspecialchars($kullanici_bilgileri[6] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="kronik_hastalik">Kronik Hastalıklar</label>
                        <textarea class="form-control" id="kronik_hastalik" name="kronik_hastalik" rows="2" 
                                  placeholder="Varsa kronik hastalıklarınızı yazınız"><?= htmlspecialchars($kullanici_bilgileri[11] ?? '') ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="kullandigi_ilaclar">Kullandığı İlaçlar</label>
                        <textarea class="form-control" id="kullandigi_ilaclar" name="kullandigi_ilaclar" rows="2" 
                                  placeholder="Düzenli kullandığınız ilaçları yazınız"><?= htmlspecialchars($kullanici_bilgileri[12] ?? '') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Profili Güncelle
                    </button>
                </form>
            </div>

            <!-- Şifre Değiştirme -->
            <div class="card">
                <h3 class="card-title">
                    <i class="fas fa-lock"></i>
                    Şifre Değiştir
                </h3>

                <form method="post">
                    <input type="hidden" name="form_type" value="sifre_degistir">
                    
                    <div class="form-group">
                        <label for="current_password">Mevcut Şifre</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label for="new_password">Yeni Şifre</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Yeni Şifre (Tekrar)</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                    </div>

                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check"></i> Şifreyi Değiştir
                    </button>
                </form>
            </div>

            <!-- Aile Bilgileri Kartı -->
            <div class="card">
            <h3 class="card-title">
                <i class="fas fa-users"></i>
                Aile Bilgileri
            </h3>

            <!-- Aile Üyesi Ekleme Formu -->
            <div class="family-add-section" style="margin-bottom: 25px; padding: 20px; background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border-radius: 12px;">
                <h4 style="color: #000000; margin-bottom: 15px;">Yeni Aile Üyesi Ekle</h4>
                <form id="familyForm" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_ad" style="margin-bottom: 5px;">Ad</label>
                        <input type="text" id="family_ad" name="ad" class="form-control" style="padding: 8px;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_soyad" style="margin-bottom: 5px;">Soyad</label>
                        <input type="text" id="family_soyad" name="soyad" class="form-control" style="padding: 8px;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_yakinlik" style="margin-bottom: 5px;">Yakınlık Derecesi</label>
                        <select id="family_yakinlik" name="yakinlik_derecesi" class="form-control" style="padding: 8px;" required>
                            <option value="">Seçiniz</option>
                            <option value="Anne">Anne</option>
                            <option value="Baba">Baba</option>
                            <option value="Eş">Eş</option>
                            <option value="Çocuk">Çocuk</option>
                            <option value="Kardeş">Kardeş</option>
                            <option value="Büyükanne">Büyükanne</option>
                            <option value="Büyükbaba">Büyükbaba</option>
                            <option value="Diğer">Diğer</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_cinsiyet" style="margin-bottom: 5px;">Cinsiyet</label>
                        <select id="family_cinsiyet" name="cinsiyet" class="form-control" style="padding: 8px;" required>
                            <option value="">Seçiniz</option>
                            <option value="Erkek">Erkek</option>
                            <option value="Kadın">Kadın</option>
                        </select>
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_tc" style="margin-bottom: 5px;">TC Kimlik No</label>
                        <input type="text" id="family_tc" name="tc_kimlik" class="form-control" style="padding: 8px;" maxlength="11">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_telefon" style="margin-bottom: 5px;">Telefon</label>
                        <input type="tel" id="family_telefon" name="telefon" class="form-control" style="padding: 8px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_dogum" style="margin-bottom: 5px;">Doğum Tarihi</label>
                        <input type="date" id="family_dogum" name="dogum_tarihi" class="form-control" style="padding: 8px;">
                    </div>
                    <div class="form-group" style="margin-bottom: 12px;">
                        <label for="family_kan" style="margin-bottom: 5px;">Kan Grubu</label>
                        <select id="family_kan" name="kan_grubu" class="form-control" style="padding: 8px;">
                            <option value="Bilinmiyor">Bilinmiyor</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                            <option value="0+">0+</option>
                            <option value="0-">0-</option>
                        </select>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 12px;">
                        <label for="family_kronik" style="margin-bottom: 5px;">Kronik Hastalıklar</label>
                        <textarea id="family_kronik" name="kronik_hastalik" class="form-control" style="padding: 8px;" rows="2" placeholder="Varsa kronik hastalıkları yazınız"></textarea>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1; margin-bottom: 12px;">
                        <label for="family_ilac" style="margin-bottom: 5px;">Kullandığı İlaçlar</label>
                        <textarea id="family_ilac" name="kullandigi_ilaclar" class="form-control" style="padding: 8px;" rows="2" placeholder="Düzenli kullandığı ilaçları yazınız"></textarea>
                    </div>
                    <div class="form-group" style="grid-column: 1 / -1;">
                        <button type="submit" class="btn btn-success" style="width: 200px;">
                            <i class="fas fa-plus"></i> Aile Üyesi Ekle
                        </button>
                    </div>
                </form>
            </div>

            <!-- Aile Üyeleri Listesi -->
            <div class="family-list-section">
                <h4 style="color: #000000; margin-bottom: 15px;">Kayıtlı Aile Üyeleri</h4>
                <div id="familyList" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 15px;">
                    <!-- Aile üyeleri buraya yüklenecek -->
                </div>
            </div>
        </div>
        </div>
    </div>

    <script>
        // Aile üyeleri yönetimi
        let familyMembers = [];

        // Sayfa yüklendiğinde aile üyelerini getir
        document.addEventListener('DOMContentLoaded', function() {
            loadFamilyMembers();
        });

        // Aile üyelerini yükle
        function loadFamilyMembers() {
            fetch('aile_uyeleri_api.php?action=list')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        familyMembers = data.data;
                        displayFamilyMembers();
                    } else {
                        console.error('Aile üyeleri yüklenemedi:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                });
        }

        // Aile üyelerini görüntüle
        function displayFamilyMembers() {
            const familyList = document.getElementById('familyList');
            
            if (familyMembers.length === 0) {
                familyList.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #666; background: #f8f9fa; border-radius: 12px; border: 2px dashed #ddd;">
                        <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px; color: #ccc;"></i>
                        <p style="margin: 0; font-size: 16px;">Henüz aile üyesi eklenmemiş</p>
                        <p style="margin: 5px 0 0 0; font-size: 14px;">Yukarıdaki formu kullanarak aile üyelerinizi ekleyebilirsiniz</p>
                    </div>
                `;
                return;
            }

            familyList.innerHTML = familyMembers.map(member => `
                <div class="family-member-card" style="
                    background: white;
                    border: 1px solid #e0e0e0;
                    border-radius: 12px;
                    padding: 20px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
                    transition: transform 0.2s ease, box-shadow 0.2s ease;
                " onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 4px 16px rgba(0,0,0,0.15)'" 
                   onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 2px 8px rgba(0,0,0,0.1)'">
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                        <div style="flex: 1;">
                            <h5 style="margin: 0 0 5px 0; color: #1976d2; font-size: 18px;">
                                <i class="fas fa-user" style="margin-right: 8px;"></i>
                                ${member.ad} ${member.soyad}
                            </h5>
                            <span style="
                                background: linear-gradient(135deg, #64b5f6, #42a5f5);
                                color: white;
                                padding: 4px 12px;
                                border-radius: 20px;
                                font-size: 12px;
                                font-weight: 500;
                            ">${member.yakinlik_derecesi}</span>
                        </div>
                        
                        <button onclick="deleteFamilyMember(${member.id})" style="
                            background: #f44336;
                            color: white;
                            border: none;
                            border-radius: 50%;
                            width: 32px;
                            height: 32px;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            transition: background 0.2s ease;
                        " onmouseover="this.style.background='#d32f2f'" onmouseout="this.style.background='#f44336'">
                            <i class="fas fa-trash" style="font-size: 12px;"></i>
                        </button>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; font-size: 14px;">
                        ${member.cinsiyet ? `
                            <div>
                                <strong style="color: #666;">Cinsiyet:</strong><br>
                                <span style="color: #333;">${member.cinsiyet}</span>
                            </div>
                        ` : ''}
                        
                        ${member.dogum_tarihi ? `
                            <div>
                                <strong style="color: #666;">Doğum Tarihi:</strong><br>
                                <span style="color: #333;">${new Date(member.dogum_tarihi).toLocaleDateString('tr-TR')}</span>
                            </div>
                        ` : ''}
                        
                        ${member.telefon ? `
                            <div>
                                <strong style="color: #666;">Telefon:</strong><br>
                                <span style="color: #333;">${member.telefon}</span>
                            </div>
                        ` : ''}
                        
                        ${member.kan_grubu && member.kan_grubu !== 'Bilinmiyor' ? `
                            <div>
                                <strong style="color: #666;">Kan Grubu:</strong><br>
                                <span style="color: #333;">${member.kan_grubu}</span>
                            </div>
                        ` : ''}
                        
                        ${member.tc_kimlik ? `
                            <div>
                                <strong style="color: #666;">TC Kimlik:</strong><br>
                                <span style="color: #333;">${member.tc_kimlik}</span>
                            </div>
                        ` : ''}
                    </div>

                    ${member.kronik_hastalik || member.kullandigi_ilaclar ? `
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #eee;">
                            ${member.kronik_hastalik ? `
                                <div style="margin-bottom: 10px;">
                                    <strong style="color: #666; font-size: 13px;">Kronik Hastalıklar:</strong><br>
                                    <span style="color: #333; font-size: 13px;">${member.kronik_hastalik}</span>
                                </div>
                            ` : ''}
                            
                            ${member.kullandigi_ilaclar ? `
                                <div>
                                    <strong style="color: #666; font-size: 13px;">Kullandığı İlaçlar:</strong><br>
                                    <span style="color: #333; font-size: 13px;">${member.kullandigi_ilaclar}</span>
                                </div>
                            ` : ''}
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        // Aile üyesi ekleme formu
        document.getElementById('familyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            fetch('aile_uyeleri_api.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    showAlert('Aile üyesi başarıyla eklendi!', 'success');
                    this.reset();
                    loadFamilyMembers();
                } else {
                    showAlert('Hata: ' + result.message, 'error');
                }
            })
            .catch(error => {
                console.error('Hata:', error);
                showAlert('Bir hata oluştu!', 'error');
            });
        });

        // Aile üyesi silme
        function deleteFamilyMember(id) {
            if (confirm('Bu aile üyesini silmek istediğinizden emin misiniz?')) {
                fetch(`aile_uyeleri_api.php?action=delete&id=${id}`, {
                    method: 'DELETE'
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showAlert('Aile üyesi başarıyla silindi!', 'success');
                        loadFamilyMembers();
                    } else {
                        showAlert('Hata: ' + result.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Hata:', error);
                    showAlert('Bir hata oluştu!', 'error');
                });
            }
        }

        // Alert gösterme fonksiyonu
        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'}`;
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                ${message}
            `;
            
            const container = document.querySelector('.container');
            const header = container.querySelector('.header');
            container.insertBefore(alertDiv, header.nextSibling);
            
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    alertDiv.remove();
                }, 300);
            }, 3000);
        }

        // Form validation
        document.querySelector('form[method="post"]').addEventListener('submit', function(e) {
            const formType = this.querySelector('input[name="form_type"]').value;
            
            if (formType === 'sifre_degistir') {
                const newPassword = this.querySelector('input[name="new_password"]').value;
                const confirmPassword = this.querySelector('input[name="confirm_password"]').value;
                
                if (newPassword !== confirmPassword) {
                    e.preventDefault();
                    alert('Yeni şifreler eşleşmiyor!');
                    return false;
                }
                
                if (newPassword.length < 6) {
                    e.preventDefault();
                    alert('Şifre en az 6 karakter olmalıdır!');
                    return false;
                }
            }
        });

        // Auto-hide alerts
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            });
        }, 5000);
    </script>
</body>
</html>


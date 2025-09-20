<?php
session_start();
require_once 'db.php';

echo "<h2>👤 Admin Kullanıcı Session Kontrolü</h2>";

// Session bilgilerini göster
echo "<h3>📋 Mevcut Session Bilgileri</h3>";
echo "<div style='background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";
echo "</div>";

// Kullanıcı durumu
if (isset($_SESSION['oturum']) && $_SESSION['oturum']) {
    echo "<p style='color: green;'>✅ Kullanıcı giriş yapmış</p>";
    
    $kullanici_id = $_SESSION['kullanici_id'] ?? null;
    $kullanici_adi = $_SESSION['ad'] ?? 'Bilinmiyor';
    $kullanici_rol = $_SESSION['rol'] ?? 'Bilinmiyor';
    
    echo "<p><strong>👤 Kullanıcı ID:</strong> $kullanici_id</p>";
    echo "<p><strong>📝 Kullanıcı Adı:</strong> $kullanici_adi</p>";
    echo "<p><strong>🔑 Rol:</strong> $kullanici_rol</p>";
    
    if ($kullanici_rol === 'admin') {
        echo "<p style='color: green; font-weight: bold;'>🔐 Admin yetkilerine sahipsiniz!</p>";
        
        // Admin paneline erişim kontrolü
        echo "<h3>🛠️ Admin Panel Erişimi</h3>";
        echo "<p><a href='admin-panel.php' style='background: #dc3545; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔧 Admin Panel'e Git</a></p>";
        
        // Admin işlemleri
        echo "<h3>📊 Admin İstatistikleri</h3>";
        
        try {
            // Toplam kullanıcı sayısı
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
            $total_users = $stmt->fetch()['total'];
            echo "<p>👥 <strong>Toplam Kullanıcı:</strong> $total_users</p>";
            
            // Toplam sipariş sayısı
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM siparisler_yeni");
            $total_orders = $stmt->fetch()['total'];
            echo "<p>📦 <strong>Toplam Sipariş:</strong> $total_orders</p>";
            
            // Toplam ürün sayısı
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM urunler");
            $total_products = $stmt->fetch()['total'];
            echo "<p>🛍️ <strong>Toplam Ürün:</strong> $total_products</p>";
            
            // Admin kullanıcıları
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE rol = 'admin'");
            $admin_count = $stmt->fetch()['total'];
            echo "<p>🔑 <strong>Admin Sayısı:</strong> $admin_count</p>";
            
        } catch (PDOException $e) {
            echo "<p style='color: red;'>❌ Veritabanı hatası: " . $e->getMessage() . "</p>";
        }
        
    } else {
        echo "<p style='color: orange;'>⚠️ Normal kullanıcı yetkileriniz var</p>";
    }
    
} else {
    echo "<p style='color: red;'>❌ Kullanıcı giriş yapmamış</p>";
    echo "<p><a href='giris-yap.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔑 Giriş Yap</a></p>";
}

echo "<hr>";
echo "<h3>🔗 Hızlı Erişim</h3>";
echo "<p><a href='panel.php' style='background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>🏠 Panel</a>";
echo "<a href='siparisler.php' style='background: #17a2b8; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>📦 Siparişler</a>";
echo "<a href='urunler.php' style='background: #ffc107; color: black; padding: 8px 16px; text-decoration: none; border-radius: 5px;'>🛍️ Ürünler</a></p>";

if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    echo "<p><a href='admin-panel.php' style='background: #dc3545; color: white; padding: 8px 16px; text-decoration: none; border-radius: 5px;'>🔧 Admin Panel</a></p>";
}
?>
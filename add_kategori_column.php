<?php
require_once 'db.php';

try {
    echo "<h2>🔧 Veritabanı Güncelleniyor...</h2>";
    
    // Önce kategori sütunu var mı kontrol et
    $stmt = $pdo->query("SHOW COLUMNS FROM urunler LIKE 'kategori'");
    $kategoriVar = $stmt->rowCount() > 0;
    
    if (!$kategoriVar) {
        // Kategori sütunu ekle
        $sql = "ALTER TABLE urunler ADD COLUMN kategori VARCHAR(50) DEFAULT 'genel' AFTER urun_foto";
        $pdo->exec($sql);
        echo "<div style='color: green; margin: 10px 0;'>✅ Kategori sütunu başarıyla eklendi</div>";
    } else {
        echo "<div style='color: orange; margin: 10px 0;'>⚠️ Kategori sütunu zaten mevcut</div>";
    }
    
    // Mevcut ürünlerin kategorilerini güncelle
    $updates = [
        ['pattern' => 'gıda', 'kategori' => 'gida'],
        ['pattern' => 'su', 'kategori' => 'su'],
        ['pattern' => 'battaniye', 'kategori' => 'barınma'],
        ['pattern' => 'çadır', 'kategori' => 'barınma'],
        ['pattern' => 'fener', 'kategori' => 'aydınlatma'],
        ['pattern' => 'hijyen', 'kategori' => 'hijyen'],
        ['pattern' => 'ısıtıcı', 'kategori' => 'ısınma'],
        ['pattern' => 'yağmurluk', 'kategori' => 'giyim'],
        ['pattern' => 'düdük', 'kategori' => 'güvenlik']
    ];
    
    foreach ($updates as $update) {
        $stmt = $pdo->prepare("UPDATE urunler SET kategori = ? WHERE urun_adi LIKE ?");
        $stmt->execute([$update['kategori'], '%' . $update['pattern'] . '%']);
        $affected = $stmt->rowCount();
        if ($affected > 0) {
            echo "<div style='color: blue; margin: 5px 0;'>📝 {$update['pattern']} içeren {$affected} ürün '{$update['kategori']}' kategorisine atandı</div>";
        }
    }
    
    echo "<br><h3>🎉 Veritabanı güncelleme tamamlandı!</h3>";
    echo "<p><a href='add_giyim_urunleri.php' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Şimdi Giyim Ürünlerini Ekle</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>
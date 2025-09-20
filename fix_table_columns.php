<?php
require_once 'db.php';

try {
    echo "<h2>Veritabanı Tablo Yapısı Kontrolü</h2>";
    
    // siparisler_yeni tablosunun yapısını kontrol et
    $stmt = $pdo->query("DESCRIBE siparisler_yeni");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>siparisler_yeni tablosu sütunları:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li><strong>" . $column['Field'] . "</strong> - " . $column['Type'] . "</li>";
    }
    echo "</ul>";
    
    // teslimat_adresi sütunu var mı kontrol et
    $hasCorrectColumn = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'teslimat_adresi') {
            $hasCorrectColumn = true;
            break;
        }
    }
    
    if (!$hasCorrectColumn) {
        echo "<h3>❌ Sorun tespit edildi!</h3>";
        echo "<p>teslimat_adresi sütunu bulunamadı. Düzeltiliyor...</p>";
        
        // Tabloyu yeniden oluştur
        $pdo->exec("DROP TABLE IF EXISTS siparis_urunler_yeni");
        $pdo->exec("DROP TABLE IF EXISTS siparisler_yeni");
        
        // Doğru yapıyla yeniden oluştur
        $sql = "CREATE TABLE siparisler_yeni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            toplam_fiyat DECIMAL(10,2) NOT NULL,
            teslimat_adresi TEXT NOT NULL,
            telefon VARCHAR(15) NOT NULL,
            siparis_durumu ENUM('Beklemede', 'Onaylandı', 'Kargoda', 'Teslim Edildi') DEFAULT 'Beklemede',
            siparis_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $pdo->exec($sql);
        
        $sql2 = "CREATE TABLE siparis_urunler_yeni (
            id INT AUTO_INCREMENT PRIMARY KEY,
            siparis_id INT NOT NULL,
            urun_id INT NOT NULL,
            miktar INT NOT NULL,
            FOREIGN KEY (siparis_id) REFERENCES siparisler_yeni(id) ON DELETE CASCADE
        )";
        $pdo->exec($sql2);
        
        echo "<h3>✅ Tablolar düzeltildi!</h3>";
        echo "<p>Artık sipariş verme işlemi çalışacaktır.</p>";
    } else {
        echo "<h3>✅ Tablo yapısı doğru!</h3>";
        echo "<p>teslimat_adresi sütunu mevcut.</p>";
    }
    
    echo "<br><p><a href='sepet.php'>Sepet sayfasına git</a> | <a href='siparisler.php'>Siparişler sayfasına git</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>
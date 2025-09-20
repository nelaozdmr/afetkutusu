<?php
require_once 'db.php';

try {
    // Sepet tablosu
    $sql1 = "CREATE TABLE IF NOT EXISTS sepet_yeni (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        urun_id INT NOT NULL,
        miktar INT DEFAULT 1,
        eklenme_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql1);
    echo "✅ sepet_yeni tablosu oluşturuldu<br>";

    // Siparişler tablosu
    $sql2 = "CREATE TABLE IF NOT EXISTS siparisler_yeni (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        toplam_fiyat DECIMAL(10,2) NOT NULL,
        teslimat_adresi TEXT NOT NULL,
        telefon VARCHAR(15) NOT NULL,
        siparis_durumu ENUM('Beklemede', 'Onaylandı', 'Kargoda', 'Teslim Edildi') DEFAULT 'Beklemede',
        siparis_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql2);
    echo "✅ siparisler_yeni tablosu oluşturuldu<br>";

    // Sipariş ürünleri tablosu
    $sql3 = "CREATE TABLE IF NOT EXISTS siparis_urunler_yeni (
        id INT AUTO_INCREMENT PRIMARY KEY,
        siparis_id INT NOT NULL,
        urun_id INT NOT NULL,
        miktar INT NOT NULL,
        FOREIGN KEY (siparis_id) REFERENCES siparisler_yeni(id) ON DELETE CASCADE
    )";
    $pdo->exec($sql3);
    echo "✅ siparis_urunler_yeni tablosu oluşturuldu<br>";

    echo "<br><h3>🎉 Tüm tablolar başarıyla oluşturuldu!</h3>";
    echo "<p><a href='sepet.php'>Sepet sayfasına git</a> | <a href='siparisler.php'>Siparişler sayfasına git</a></p>";

} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>
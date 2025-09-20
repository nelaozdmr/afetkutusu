<?php
require_once 'db.php';

echo "<h2>🔧 Stok Durumu Sütunu Ekleme</h2>";

try {
    // Önce sütunun var olup olmadığını kontrol et
    $sql_check = "SHOW COLUMNS FROM urunler LIKE 'stok_durumu'";
    $stmt = $pdo->query($sql_check);
    $column_exists = $stmt->rowCount() > 0;
    
    if ($column_exists) {
        echo "<p style='color: green;'>✅ stok_durumu sütunu zaten mevcut</p>";
    } else {
        // Sütunu ekle
        $sql_add = "ALTER TABLE urunler ADD COLUMN stok_durumu VARCHAR(50) DEFAULT 'Stokta'";
        $pdo->exec($sql_add);
        echo "<p style='color: green;'>✅ stok_durumu sütunu başarıyla eklendi</p>";
    }
    
    // Tablo yapısını tekrar göster
    echo "<h3>📋 Güncellenmiş Tablo Yapısı:</h3>";
    $sql = "DESCRIBE urunler";
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Sütun Adı</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    
    foreach ($columns as $column) {
        $highlight = ($column['Field'] == 'stok_durumu') ? 'style="background-color: yellow;"' : '';
        echo "<tr $highlight>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>🎉 İşlem Tamamlandı!</h3>";
    echo "<p>Artık giyim ürünlerini ekleyebilirsiniz.</p>";
    
} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>

<p><a href="force_add_giyim.php">Giyim Ürünlerini Tekrar Ekle</a> | <a href="urunler.php">Ürünler Sayfasına Git</a></p>
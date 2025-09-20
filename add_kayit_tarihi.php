<?php
require_once 'db.php';

try {
    echo "<h2>Users Tablosuna kayit_tarihi Alanı Ekleme</h2>";
    
    // Önce alanın var olup olmadığını kontrol et
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'kayit_tarihi'");
    $column_exists = $stmt->rowCount() > 0;
    
    if ($column_exists) {
        echo "<p style='color: orange;'>⚠️ kayit_tarihi alanı zaten mevcut!</p>";
    } else {
        // kayit_tarihi alanını ekle
        $sql = "ALTER TABLE users ADD COLUMN kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER kullandigi_ilaclar";
        $pdo->exec($sql);
        echo "<p style='color: green;'>✅ kayit_tarihi alanı başarıyla eklendi!</p>";
        
        // Mevcut kullanıcılar için kayıt tarihini güncelle (bugünün tarihi olarak)
        $update_sql = "UPDATE users SET kayit_tarihi = CURRENT_TIMESTAMP WHERE kayit_tarihi IS NULL";
        $pdo->exec($update_sql);
        echo "<p style='color: blue;'>📅 Mevcut kullanıcıların kayıt tarihleri güncellendi!</p>";
    }
    
    // Güncel tablo yapısını göster
    echo "<h3>Güncel Users Tablosu Yapısı:</h3>";
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Sütun</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    foreach ($columns as $column) {
        $highlight = ($column['Field'] == 'kayit_tarihi') ? 'style="background-color: yellow;"' : '';
        echo "<tr $highlight>";
        echo "<td>" . $column['Field'] . "</td>";
        echo "<td>" . $column['Type'] . "</td>";
        echo "<td>" . $column['Null'] . "</td>";
        echo "<td>" . $column['Key'] . "</td>";
        echo "<td>" . $column['Default'] . "</td>";
        echo "<td>" . $column['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Örnek kullanıcı verilerini göster
    echo "<h3>Örnek Kullanıcı Verileri (kayit_tarihi ile):</h3>";
    $stmt = $pdo->query("SELECT id, username, email, kayit_tarihi FROM users LIMIT 3");
    $users = $stmt->fetchAll();
    
    if (!empty($users)) {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Kullanıcı Adı</th><th>Email</th><th>Kayıt Tarihi</th></tr>";
        foreach ($users as $user) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($user['id']) . "</td>";
            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
            echo "<td>" . htmlspecialchars($user['kayit_tarihi'] ?? 'Yok') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
}
?>
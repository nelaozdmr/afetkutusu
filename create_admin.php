<?php
require_once 'db.php';

try {
    // Admin kullanıcısı var mı kontrol et
    $stmt = $pdo->prepare("SELECT * FROM users WHERE rol = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin) {
        echo "Admin kullanıcısı zaten mevcut:<br>";
        echo "Email: " . $admin['email'] . "<br>";
        echo "Kullanıcı Adı: " . $admin['username'] . "<br>";
        echo "Rol: " . $admin['rol'] . "<br>";
    } else {
        // Admin kullanıcısı oluştur
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, rol) VALUES (?, ?, ?, ?)");
        $result = $stmt->execute(['admin', 'admin@afetkutusu.com', 'admin123', 'admin']);
        
        if ($result) {
            echo "Admin kullanıcısı başarıyla oluşturuldu!<br>";
            echo "Email: admin@afetkutusu.com<br>";
            echo "Şifre: admin123<br>";
            echo "Rol: admin<br>";
        } else {
            echo "Admin kullanıcısı oluşturulamadı!";
        }
    }
    
    // Tüm kullanıcıları listele
    echo "<br><h3>Tüm Kullanıcılar:</h3>";
    $stmt = $pdo->prepare("SELECT id, username, email, rol FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll();
    
    echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
    echo "<tr><th>ID</th><th>Kullanıcı Adı</th><th>Email</th><th>Rol</th></tr>";
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . $user['username'] . "</td>";
        echo "<td>" . $user['email'] . "</td>";
        echo "<td>" . $user['rol'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
} catch (PDOException $e) {
    echo "Hata: " . $e->getMessage();
}
?>
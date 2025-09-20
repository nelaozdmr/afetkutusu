<?php
require_once 'db.php';

echo "<h2>🔧 Veritabanı Düzeltme ve Ürün Ekleme</h2>";

try {
    // 1. Önce tabloyu kontrol et
    echo "<h3>📋 Tablo Yapısı Kontrolü</h3>";
    $stmt = $pdo->query("DESCRIBE urunler");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $columnNames = [];
    foreach ($columns as $column) {
        $columnNames[] = $column['Field'];
        echo "📝 {$column['Field']} - {$column['Type']}<br>";
    }
    echo "<br>";
    
    // 2. Eksik sütunları kontrol et ve ekle
    echo "<h3>🔨 Eksik Sütunları Ekleme</h3>";
    $requiredColumns = [
        'kategori' => "ALTER TABLE urunler ADD COLUMN kategori VARCHAR(50) DEFAULT 'genel'",
        'simge' => "ALTER TABLE urunler ADD COLUMN simge VARCHAR(100) DEFAULT 'fas fa-box'",
        'renk' => "ALTER TABLE urunler ADD COLUMN renk VARCHAR(20) DEFAULT '#007bff'"
    ];
    
    foreach ($requiredColumns as $columnName => $sql) {
        if (!in_array($columnName, $columnNames)) {
            try {
                $pdo->exec($sql);
                echo "✅ $columnName sütunu eklendi<br>";
            } catch (Exception $e) {
                echo "⚠️ $columnName sütunu eklenirken hata: " . $e->getMessage() . "<br>";
            }
        } else {
            echo "✅ $columnName sütunu zaten mevcut<br>";
        }
    }
    echo "<br>";
    
    // 3. Mevcut ürün sayısını kontrol et
    echo "<h3>📊 Mevcut Ürün Durumu</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as toplam FROM urunler");
    $toplam = $stmt->fetch()['toplam'];
    echo "📦 Toplam ürün sayısı: <strong>$toplam</strong><br><br>";
    
    // 4. Giyim ürünlerini ekle
    echo "<h3>👕 Giyim Ürünleri Ekleme</h3>";
    
    $giyimUrunleri = [
        [
            'urun_adi' => 'Acil Durum Ayakkabısı',
            'urun_aciklama' => 'Su geçirmez, dayanıklı acil durum ayakkabısı. Çeşitli bedenlerde mevcut.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'ayakkabi.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var',
            'simge' => 'fas fa-shoe-prints',
            'renk' => '#8B4513'
        ],
        [
            'urun_adi' => 'Termal Çorap (3 Çift)',
            'urun_aciklama' => 'Soğuk havalarda ayakları sıcak tutan termal çorap seti. 3 çift.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'corap.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var',
            'simge' => 'fas fa-socks',
            'renk' => '#FF6347'
        ],
        [
            'urun_adi' => 'Acil Durum Montu',
            'urun_aciklama' => 'Su geçirmez, rüzgar geçirmez acil durum montu. Soğuk ve yağışlı havalarda koruma sağlar.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'mont.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var',
            'simge' => 'fas fa-user-tie',
            'renk' => '#2E8B57'
        ],
        [
            'urun_adi' => 'Dayanıklı Pantolon',
            'urun_aciklama' => 'Yırtılmaya dayanıklı, rahat acil durum pantolonu. Çeşitli bedenlerde.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'pantolon.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var',
            'simge' => 'fas fa-tshirt',
            'renk' => '#4169E1'
        ]
    ];
    
    $eklenenSayi = 0;
    
    foreach ($giyimUrunleri as $urun) {
        // Aynı isimde ürün var mı kontrol et
        $stmt = $pdo->prepare("SELECT COUNT(*) as var_mi FROM urunler WHERE urun_adi = ?");
        $stmt->execute([$urun['urun_adi']]);
        $varMi = $stmt->fetch()['var_mi'];
        
        if ($varMi == 0) {
            // Ürünü ekle - sütun adlarını kontrol ederek
            if (in_array('simge', $columnNames) && in_array('renk', $columnNames)) {
                $stmt = $pdo->prepare("INSERT INTO urunler (urun_adi, urun_aciklama, urun_fiyat, urun_foto, kategori, stok_durumu, simge, renk) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $urun['urun_adi'],
                    $urun['urun_aciklama'],
                    $urun['urun_fiyat'],
                    $urun['urun_foto'],
                    $urun['kategori'],
                    $urun['stok_durumu'],
                    $urun['simge'],
                    $urun['renk']
                ]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO urunler (urun_adi, urun_aciklama, urun_fiyat, urun_foto, kategori, stok_durumu) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $urun['urun_adi'],
                    $urun['urun_aciklama'],
                    $urun['urun_fiyat'],
                    $urun['urun_foto'],
                    $urun['kategori'],
                    $urun['stok_durumu']
                ]);
            }
            
            echo "<div style='color: green; margin: 5px 0;'>✅ " . $urun['urun_adi'] . " eklendi</div>";
            $eklenenSayi++;
        } else {
            echo "<div style='color: orange; margin: 5px 0;'>⚠️ " . $urun['urun_adi'] . " zaten mevcut</div>";
        }
    }
    
    // 5. Son durum
    echo "<br><h3>📈 İşlem Sonucu</h3>";
    $stmt = $pdo->query("SELECT COUNT(*) as yeni_toplam FROM urunler");
    $yeniToplam = $stmt->fetch()['yeni_toplam'];
    
    echo "<p><strong>$eklenenSayi</strong> yeni giyim ürünü eklendi.</p>";
    echo "<p>Yeni toplam ürün sayısı: <strong>$yeniToplam</strong></p>";
    echo "<p><a href='urunler.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ürünler Sayfasını Görüntüle</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Veritabanı Hatası: " . $e->getMessage();
} catch (Exception $e) {
    echo "❌ Genel Hata: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
</style>
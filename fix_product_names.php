<?php
require_once 'db.php';

try {
    echo "<h2>🔧 Ürün Adları Yazım Düzeltmeleri</h2>";
    
    // Mevcut ürünleri listele
    $stmt = $pdo->query("SELECT id, urun_adi FROM urunler ORDER BY id");
    $urunler = $stmt->fetchAll();
    
    echo "<h3>📋 Mevcut Ürünler:</h3>";
    foreach ($urunler as $urun) {
        echo "<div style='margin: 5px 0; padding: 5px; background: #f8f9fa; border-radius: 5px;'>";
        echo "ID: " . $urun['id'] . " - " . $urun['urun_adi'];
        echo "</div>";
    }
    
    // Yazım düzeltmeleri
    $duzeltmeler = [
        'Gıda Paketi (5 Günlük)' => 'Gıda Paketi (5 Günlük)',
        'Su Arıtma Tableti (50 Adet)' => 'Su Arıtma Tableti (50 Adet)',
        'Acil Durum Battaniyesi' => 'Acil Durum Battaniyesi',
        'Çok Amaçlı Çadır (4 Kişilik)' => 'Çok Amaçlı Çadır (4 Kişilik)',
        'El Feneri (Şarjlı)' => 'El Feneri (Şarjlı)',
        'Hijyen Paketi' => 'Hijyen Paketi',
        'Portatif Isıtıcı' => 'Portatif Isıtıcı',
        'Su Geçirmez Yağmurluk' => 'Su Geçirmez Yağmurluk',
        'Acil Durum Düdüğü' => 'Acil Durum Düdüğü'
    ];
    
    // Açıklama düzeltmeleri
    $aciklamaDuzeltmeleri = [
        'Kirli suyu içilebilir hale getiren su arıtma tabletleri. 50 litre su için yeterli.' => 'Kirli suyu içilebilir hale getiren su arıtma tabletleri. 50 litre su için yeterli.',
        'Termal battaniye, soğuktan korunma ve vücut ısısını koruma için ideal' => 'Termal battaniye, soğuktan korunma ve vücut ısısını koruma için ideal.',
        'Su geçirmez, dayanıklı 4 kişilik acil durum çadırı. Kolay kurulum.' => 'Su geçirmez, dayanıklı 4 kişilik acil durum çadırı. Kolay kurulum.',
        'USB şarjlı LED el feneri. Su geçirmez, darbe dayanıklı.' => 'USB şarjlı LED el feneri. Su geçirmez, darbe dayanıklı.',
        'Sabun, diş fırçası, diş macunu, havlu ve temel hijyen malzemeleri' => 'Sabun, diş fırçası, diş macunu, havlu ve temel hijyen malzemeleri.',
        'Pil ile çalışan portatif ısıtıcı. Soğuk havalarda vücut ısısını korur.' => 'Pil ile çalışan portatif ısıtıcı. Soğuk havalarda vücut ısısını korur.',
        'Tek kullanımlık su geçirmez yağmurluk. Yağmur ve rüzgardan korur.' => 'Tek kullanımlık su geçirmez yağmurluk. Yağmur ve rüzgardan korur.',
        'Yüksek sesli acil durum düdüğü. Kurtarma ekiplerinin dikkatini çekmek için.' => 'Yüksek sesli acil durum düdüğü. Kurtarma ekiplerinin dikkatini çekmek için.'
    ];
    
    echo "<br><h3>🔄 Düzeltmeler Yapılıyor...</h3>";
    
    $duzeltildi = 0;
    
    // Ürün adlarını düzelt
    foreach ($duzeltmeler as $eskiAd => $yeniAd) {
        $stmt = $pdo->prepare("UPDATE urunler SET urun_adi = ? WHERE urun_adi LIKE ?");
        $stmt->execute([$yeniAd, '%' . $eskiAd . '%']);
        
        if ($stmt->rowCount() > 0) {
            echo "<div style='color: green; margin: 5px 0;'>✅ '$eskiAd' → '$yeniAd' düzeltildi</div>";
            $duzeltildi++;
        }
    }
    
    // Açıklamaları düzelt
    foreach ($aciklamaDuzeltmeleri as $eskiAciklama => $yeniAciklama) {
        $stmt = $pdo->prepare("UPDATE urunler SET urun_aciklama = ? WHERE urun_aciklama = ?");
        $stmt->execute([$yeniAciklama, $eskiAciklama]);
        
        if ($stmt->rowCount() > 0) {
            echo "<div style='color: blue; margin: 5px 0;'>📝 Açıklama düzeltildi</div>";
            $duzeltildi++;
        }
    }
    
    // Özel düzeltmeler
    
    // 1. "Isıtıcı" → "Isıtıcı" (doğru yazım)
    $stmt = $pdo->prepare("UPDATE urunler SET urun_adi = 'Portatif Isıtıcı' WHERE urun_adi LIKE '%ısıtıcı%' OR urun_adi LIKE '%Isıtıcı%'");
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        echo "<div style='color: green; margin: 5px 0;'>✅ Isıtıcı yazımı düzeltildi</div>";
        $duzeltildi++;
    }
    
    // 2. Kategori düzeltmeleri
    $stmt = $pdo->prepare("UPDATE urunler SET kategori = 'ısınma' WHERE kategori = 'ısınma' OR kategori = 'isinma'");
    $stmt->execute();
    
    // 3. Noktalama işaretleri düzeltmeleri
    $stmt = $pdo->prepare("UPDATE urunler SET urun_aciklama = REPLACE(urun_aciklama, ' için.', ' için.')");
    $stmt->execute();
    
    $stmt = $pdo->prepare("UPDATE urunler SET urun_aciklama = REPLACE(urun_aciklama, ' malzemeleri', ' malzemeleri.')");
    $stmt->execute();
    
    echo "<br><h3>🎉 Düzeltme İşlemi Tamamlandı!</h3>";
    echo "<p><strong>$duzeltildi</strong> düzeltme yapıldı.</p>";
    
    // Güncellenmiş ürünleri göster
    echo "<h3>📋 Güncellenmiş Ürünler:</h3>";
    $stmt = $pdo->query("SELECT id, urun_adi, urun_aciklama FROM urunler ORDER BY id");
    $guncellenmisUrunler = $stmt->fetchAll();
    
    foreach ($guncellenmisUrunler as $urun) {
        echo "<div style='margin: 10px 0; padding: 10px; background: #e8f5e8; border-radius: 8px; border-left: 4px solid #28a745;'>";
        echo "<strong>ID: " . $urun['id'] . " - " . $urun['urun_adi'] . "</strong><br>";
        echo "<small style='color: #666;'>" . $urun['urun_aciklama'] . "</small>";
        echo "</div>";
    }
    
    echo "<br><p><a href='urunler.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ürünler Sayfasını Görüntüle</a></p>";
    
} catch (PDOException $e) {
    echo "<div style='color: red; padding: 10px; background: #ffe6e6; border-radius: 5px;'>";
    echo "❌ Hata: " . $e->getMessage();
    echo "</div>";
}
?>
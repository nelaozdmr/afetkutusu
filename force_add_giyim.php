<?php
require_once 'db.php';

echo "<h2>👕 Giyim Ürünleri Zorla Ekleme</h2>";

try {
    // Önce mevcut giyim ürünlerini sil
    echo "<h3>🗑️ Mevcut Giyim Ürünlerini Temizleme</h3>";
    $stmt = $pdo->prepare("DELETE FROM urunler WHERE kategori = 'giyim'");
    $stmt->execute();
    $silinenSayi = $stmt->rowCount();
    echo "🗑️ $silinenSayi adet giyim ürünü silindi<br><br>";
    
    // Giyim ürünlerini ekle
    echo "<h3>➕ Yeni Giyim Ürünleri Ekleme</h3>";
    
    $giyimUrunleri = [
        [
            'urun_adi' => 'Acil Durum Ayakkabısı',
            'urun_aciklama' => 'Su geçirmez, dayanıklı acil durum ayakkabısı. Çeşitli bedenlerde mevcut.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'ayakkabi.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Termal Çorap (3 Çift)',
            'urun_aciklama' => 'Soğuk havalarda ayakları sıcak tutan termal çorap seti. 3 çift.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'corap.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Acil Durum Montu',
            'urun_aciklama' => 'Su geçirmez, rüzgar geçirmez acil durum montu. Soğuk ve yağışlı havalarda koruma sağlar.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'mont.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Dayanıklı Pantolon',
            'urun_aciklama' => 'Yırtılmaya dayanıklı, rahat acil durum pantolonu. Çeşitli bedenlerde.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'pantolon.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var'
        ]
    ];
    
    $eklenenSayi = 0;
    
    foreach ($giyimUrunleri as $urun) {
        try {
            // Ürünü ekle
            $stmt = $pdo->prepare("INSERT INTO urunler (urun_adi, urun_aciklama, urun_fiyat, urun_foto, kategori, stok_durumu) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $urun['urun_adi'],
                $urun['urun_aciklama'],
                $urun['urun_fiyat'],
                $urun['urun_foto'],
                $urun['kategori'],
                $urun['stok_durumu']
            ]);
            
            echo "<div style='color: green; margin: 5px 0;'>✅ " . $urun['urun_adi'] . " başarıyla eklendi (ID: " . $pdo->lastInsertId() . ")</div>";
            $eklenenSayi++;
        } catch (Exception $e) {
            echo "<div style='color: red; margin: 5px 0;'>❌ " . $urun['urun_adi'] . " eklenirken hata: " . $e->getMessage() . "</div>";
        }
    }
    
    echo "<br><h3>📊 İşlem Sonucu</h3>";
    echo "<p><strong>$eklenenSayi</strong> giyim ürünü başarıyla eklendi.</p>";
    
    // Kontrol et
    $stmt = $pdo->query("SELECT COUNT(*) as giyim_sayi FROM urunler WHERE kategori = 'giyim'");
    $giyimSayi = $stmt->fetch()['giyim_sayi'];
    echo "<p>Veritabanında şu anda <strong>$giyimSayi</strong> giyim ürünü var.</p>";
    
    // Son eklenen giyim ürünlerini listele
    echo "<h4>📋 Eklenen Giyim Ürünleri</h4>";
    $stmt = $pdo->query("SELECT id, urun_adi, kategori FROM urunler WHERE kategori = 'giyim' ORDER BY id DESC");
    $giyimListesi = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($giyimListesi) > 0) {
        echo "<ul>";
        foreach ($giyimListesi as $urun) {
            echo "<li>ID: {$urun['id']} - {$urun['urun_adi']} (Kategori: {$urun['kategori']})</li>";
        }
        echo "</ul>";
    }
    
    echo "<br><p><a href='urunler.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ürünler Sayfasını Görüntüle</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Veritabanı Hatası: " . $e->getMessage();
} catch (Exception $e) {
    echo "❌ Genel Hata: " . $e->getMessage();
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3, h4 { color: #333; }
ul { margin: 10px 0; }
li { margin: 5px 0; }
</style>
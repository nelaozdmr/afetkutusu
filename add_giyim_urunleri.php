<?php
require_once 'db.php';

try {
    echo "<h2>👕 Giyim Ürünleri Ekleniyor...</h2>";
    
    // Önce mevcut ürünleri kontrol et
    $stmt = $pdo->query("SELECT COUNT(*) as toplam FROM urunler");
    $mevcutSayi = $stmt->fetch()['toplam'];
    
    echo "<p>📊 Mevcut ürün sayısı: <strong>$mevcutSayi</strong></p>";
    
    // Giyim ürünleri
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
        // Aynı isimde ürün var mı kontrol et
        $stmt = $pdo->prepare("SELECT COUNT(*) as var_mi FROM urunler WHERE urun_adi = ?");
        $stmt->execute([$urun['urun_adi']]);
        $varMi = $stmt->fetch()['var_mi'];
        
        if ($varMi == 0) {
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
            
            echo "<div style='color: green; margin: 5px 0;'>✅ " . $urun['urun_adi'] . " eklendi</div>";
            $eklenenSayi++;
        } else {
            echo "<div style='color: orange; margin: 5px 0;'>⚠️ " . $urun['urun_adi'] . " zaten mevcut</div>";
        }
    }
    
    echo "<br><h3>🎉 İşlem Tamamlandı!</h3>";
    echo "<p><strong>$eklenenSayi</strong> yeni giyim ürünü eklendi.</p>";
    echo "<p><a href='urunler.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ürünler Sayfasını Görüntüle</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>
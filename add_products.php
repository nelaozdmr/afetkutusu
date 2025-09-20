<?php
require_once 'db.php';

try {
    echo "<h2>🏥 Afet Yardım Ürünleri Ekleniyor...</h2>";
    
    // Önce mevcut ürünleri kontrol et
    $stmt = $pdo->query("SELECT COUNT(*) as toplam FROM urunler");
    $mevcutSayi = $stmt->fetch()['toplam'];
    
    echo "<p>📊 Mevcut ürün sayısı: <strong>$mevcutSayi</strong></p>";
    
    // Afet yardım ürünleri
    $urunler = [
        [
            'urun_adi' => 'Acil Durum Gıda Paketi',
            'urun_aciklama' => 'Konserve yiyecekler, bisküvi, su ve temel gıda maddeleri içeren 7 günlük acil durum paketi',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'gida_paketi.jpg',
            'kategori' => 'gida',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Su Arıtma Tableti (50 Adet)',
            'urun_aciklama' => 'Kirli suyu içilebilir hale getiren su arıtma tabletleri. 50 litre su için yeterli.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'su.jpg',
            'kategori' => 'su',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Acil Durum Battaniyesi',
            'urun_aciklama' => 'Termal battaniye, soğuktan korunma ve vücut ısısını koruma için ideal',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'battaniye.jpg',
            'kategori' => 'barınma',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Çok Amaçlı Çadır (4 Kişilik)',
            'urun_aciklama' => 'Su geçirmez, dayanıklı 4 kişilik acil durum çadırı. Kolay kurulum.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'cadir.jpg',
            'kategori' => 'barınma',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'El Feneri (Şarjlı)',
            'urun_aciklama' => 'USB şarjlı LED el feneri. Su geçirmez, darbe dayanıklı.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'el_feneri.jpg',
            'kategori' => 'aydınlatma',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Hijyen Paketi',
            'urun_aciklama' => 'Sabun, diş fırçası, diş macunu, havlu ve temel hijyen malzemeleri',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'hijyen_paketi.jpg',
            'kategori' => 'hijyen',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Portatif Isıtıcı',
            'urun_aciklama' => 'Pil ile çalışan portatif ısıtıcı. Soğuk havalarda vücut ısısını korur.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'isitici.jpg',
            'kategori' => 'ısınma',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Su Geçirmez Yağmurluk',
            'urun_aciklama' => 'Tek kullanımlık su geçirmez yağmurluk. Yağmur ve rüzgardan korur.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'yagmurluk.jpg',
            'kategori' => 'giyim',
            'stok_durumu' => 'Var'
        ],
        [
            'urun_adi' => 'Acil Durum Düdüğü',
            'urun_aciklama' => 'Yüksek sesli acil durum düdüğü. Kurtarma ekiplerinin dikkatini çekmek için.',
            'urun_fiyat' => 0.00,
            'urun_foto' => 'duduk.jpg',
            'kategori' => 'güvenlik',
            'stok_durumu' => 'Var'
        ]
    ];
    
    $eklenenSayi = 0;
    
    foreach ($urunler as $urun) {
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
    echo "<p><strong>$eklenenSayi</strong> yeni ürün eklendi.</p>";
    echo "<p><a href='urunler.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Ürünler Sayfasını Görüntüle</a></p>";
    
} catch (PDOException $e) {
    echo "❌ Hata: " . $e->getMessage();
}
?>
<?php
session_start();
include 'db.php';

// Hata raporlamayı aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Kullanıcı girişi kontrolü
if (!isset($_SESSION['oturum'])) {
    header('Location: giris-yap.php');
    exit();
}

// POST isteği kontrolü - sadece POST ile erişilebilir
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<script>alert('Bu sayfaya doğrudan erişilemez!'); window.location.href='sepet.php';</script>";
    exit();
}

// Kullanıcı bilgilerini al
$kullanici = isset($_SESSION['ad']) ? $_SESSION['ad'] : null;
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : (isset($_SESSION['kullanici_id']) ? $_SESSION['kullanici_id'] : 1);

// Kullanıcı adres bilgilerini veritabanından al
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username=?");
    $stmt->execute([$kullanici]);
    $user_info = $stmt->fetch();
    
    if (!$user_info) {
        echo "<script>alert('Kullanıcı bilgileri bulunamadı! Lütfen profil sayfasından bilgilerinizi güncelleyin.'); window.location.href='profil.php';</script>";
        exit;
    }
    
    // Adres bilgisi kontrolü
    if (empty($user_info['adres']) || empty($user_info['telefon'])) {
        echo "<script>alert('Sipariş verebilmek için adres ve telefon bilgilerinizi profil sayfasından güncellemeniz gerekiyor.'); window.location.href='profil.php';</script>";
        exit;
    }
    
} catch (PDOException $e) {
    echo "<script>alert('Veritabanı hatası: " . $e->getMessage() . "'); window.location.href='sepet.php';</script>";
    exit;
}

// Sepetteki ürünleri al
$stmt = $pdo->prepare("SELECT * FROM sepet_yeni WHERE user_id = ?");
$stmt->execute([$user_id]);
$sepet = $stmt->fetchAll();

if (count($sepet) == 0) {
    echo "<script>alert('Sepetiniz boş!'); window.location.href='sepet.php';</script>";
    exit;
}

// Toplam tutarı hesapla
$stmt = $pdo->prepare("
    SELECT SUM(u.urun_fiyat * s.miktar) as toplam
    FROM sepet_yeni s 
    JOIN urunler u ON s.urun_id = u.id 
    WHERE s.user_id = ?
");
$stmt->execute([$user_id]);
$toplam_result = $stmt->fetch();
$toplam_tutar = $toplam_result['toplam'] ?? 0;

try {
    // Önce tabloların varlığını kontrol et
    $stmt = $pdo->query("SHOW TABLES LIKE 'siparisler_yeni'");
    if ($stmt->rowCount() == 0) {
        echo "<script>
            alert('Sipariş tablosu bulunamadı!\\n\\nLütfen fix_all_columns.sql dosyasını phpMyAdmin\\'de çalıştırın.\\n\\nAdımlar:\\n1. http://localhost/phpmyadmin\\n2. afetkutusu veritabanını seç\\n3. SQL sekmesi\\n4. fix_all_columns.sql içeriğini yapıştır\\n5. Git butonuna tıkla');
            window.location.href='sepet.php';
        </script>";
        exit;
    }
    
    $stmt = $pdo->query("SHOW TABLES LIKE 'siparis_urunler_yeni'");
    if ($stmt->rowCount() == 0) {
        echo "<script>
            alert('Sipariş ürünleri tablosu bulunamadı!\\n\\nLütfen fix_all_columns.sql dosyasını phpMyAdmin\\'de çalıştırın.');
            window.location.href='sepet.php';
        </script>";
        exit;
    }

    // PDO transaction başlat
    $pdo->beginTransaction();
    
    // 1. siparisler_yeni tablosuna yeni kayıt ekle (ücretsiz olarak)
    $stmt = $pdo->prepare("INSERT INTO siparisler_yeni (user_id, toplam_fiyat, teslimat_adresi, telefon) VALUES (?, ?, ?, ?)");
    $stmt->execute([$user_id, 0.00, $user_info['adres'], $user_info['telefon']]);

    // Eklenen siparişin ID'sini al
    $siparis_id = $pdo->lastInsertId();

    // 2. siparis_urunler_yeni tablosuna her ürünü ekle
    foreach ($sepet as $item) {
        $urun_id = $item['urun_id'];
        $miktar = $item['miktar'];

        $stmt = $pdo->prepare("INSERT INTO siparis_urunler_yeni (siparis_id, urun_id, miktar) VALUES (?, ?, ?)");
        $stmt->execute([$siparis_id, $urun_id, $miktar]);
    }

    // 3. Kullanıcının sepetini temizle
    $stmt = $pdo->prepare("DELETE FROM sepet_yeni WHERE user_id = ?");
    $stmt->execute([$user_id]);
    
    // Transaction'ı commit et
    $pdo->commit();

    // Başarı mesajı ile yönlendir (sevgi ile oluşturuluyor mesajı)
    echo "<script>
        alert('Siparişiniz sevgi ile oluşturuluyor... ❤️\\n\\nSipariş No: #" . $siparis_id . "\\nTutar: ÜCRETSIZ\\n\\nTeslimat Adresi: " . $user_info['adres'] . "\\nTelefon: " . $user_info['telefon'] . "\\n\\nAfet yardım ürünleriniz sevgi ile hazırlanıp size ulaştırılacaktır.');
        window.location.href='siparisler.php';
    </script>";
    exit;
    
} catch (PDOException $e) {
    // Transaction'ı geri al
    $pdo->rollBack();
    
    echo "<script>
        alert('Sipariş oluşturulurken hata oluştu:\\n" . addslashes($e->getMessage()) . "\\n\\nDetay: " . addslashes($e->getFile()) . " satır " . $e->getLine() . "\\n\\nLütfen tekrar deneyin.');
        window.location.href='sepet.php';
    </script>";
    exit;
}
?>

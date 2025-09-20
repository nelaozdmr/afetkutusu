<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
header('Content-Type: application/json');

// Oturum kontrolü
if (!isset($_SESSION['oturum'])) {
    echo json_encode(['success' => false, 'message' => 'Oturum bulunamadı']);
    exit();
}

// Kullanıcı ID'sini al - önce session'dan, yoksa veritabanından
$kullanici_id = $_SESSION['kullanici_id'] ?? null;

if (!$kullanici_id) {
    $kullanici_adi = $_SESSION['ad'];
    
    // Veritabanı bağlantısı - db.php dosyasını kullan
    require_once 'db.php';
    
    // Kullanıcı ID'sini veritabanından al
    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$kullanici_adi]);
        $user = $stmt->fetch();
        
        if ($user) {
            $kullanici_id = $user['id'];
            $_SESSION['kullanici_id'] = $kullanici_id; // Session'a kaydet
        } else {
            echo json_encode(['success' => false, 'message' => 'Kullanıcı bulunamadı']);
            exit();
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Veritabanı hatası: ' . $e->getMessage()]);
        exit();
    }
} else {
    // Veritabanı bağlantısı - db.php dosyasını kullan
    require_once 'db.php';
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        // Aile üyelerini listele
        try {
            $stmt = $pdo->prepare("SELECT * FROM aile_uyeleri WHERE kullanici_id = ? ORDER BY yakinlik_derecesi, ad");
            $stmt->execute([$kullanici_id]);
            $aile_uyeleri = $stmt->fetchAll();
            
            echo json_encode(['success' => true, 'data' => $aile_uyeleri]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Aile üyeleri getirilemedi: ' . $e->getMessage()]);
        }
        break;

    case 'add':
        // Yeni aile üyesi ekle
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = file_get_contents('php://input');
            $data = json_decode($input, true);
            
            // JSON decode başarısız olursa boş array kullan
            if ($data === null) {
                $data = [];
            }
            
            // Gerekli alanları kontrol et
            if (empty($data['ad']) || empty($data['soyad']) || empty($data['yakinlik_derecesi']) || empty($data['cinsiyet'])) {
                echo json_encode(['success' => false, 'message' => 'Gerekli alanlar eksik (ad, soyad, yakınlık derecesi, cinsiyet)']);
                break;
            }
            
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO aile_uyeleri 
                    (kullanici_id, ad, soyad, tc_kimlik, telefon, dogum_tarihi, yakinlik_derecesi, 
                     cinsiyet, kan_grubu, kronik_hastalik, kullandigi_ilaclar, ozel_notlar, 
                     acil_durum_kisi, acil_durum_telefon) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $kullanici_id,
                    $data['ad'],
                    $data['soyad'],
                    !empty($data['tc_kimlik']) ? $data['tc_kimlik'] : null,
                    !empty($data['telefon']) ? $data['telefon'] : null,
                    !empty($data['dogum_tarihi']) ? $data['dogum_tarihi'] : null,
                    $data['yakinlik_derecesi'],
                    $data['cinsiyet'],
                    !empty($data['kan_grubu']) ? $data['kan_grubu'] : 'Bilinmiyor',
                    !empty($data['kronik_hastalik']) ? $data['kronik_hastalik'] : null,
                    !empty($data['kullandigi_ilaclar']) ? $data['kullandigi_ilaclar'] : null,
                    !empty($data['ozel_notlar']) ? $data['ozel_notlar'] : null,
                    !empty($data['acil_durum_kisi']) ? $data['acil_durum_kisi'] : null,
                    !empty($data['acil_durum_telefon']) ? $data['acil_durum_telefon'] : null
                ]);
                
                echo json_encode(['success' => true, 'message' => 'Aile üyesi başarıyla eklendi']);
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo json_encode(['success' => false, 'message' => 'Bu TC kimlik numarası zaten kayıtlı']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Aile üyesi eklenemedi: ' . $e->getMessage()]);
                }
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz HTTP method']);
        }
        break;

    case 'update':
        // Aile üyesi güncelle
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            
            try {
                $stmt = $pdo->prepare("
                    UPDATE aile_uyeleri SET 
                    ad = ?, soyad = ?, tc_kimlik = ?, telefon = ?, dogum_tarihi = ?, 
                    yakinlik_derecesi = ?, cinsiyet = ?, kan_grubu = ?, kronik_hastalik = ?, 
                    kullandigi_ilaclar = ?, ozel_notlar = ?, acil_durum_kisi = ?, acil_durum_telefon = ?
                    WHERE id = ? AND kullanici_id = ?
                ");
                
                $stmt->execute([
                    $data['ad'],
                    $data['soyad'],
                    $data['tc_kimlik'] ?: null,
                    $data['telefon'] ?: null,
                    $data['dogum_tarihi'] ?: null,
                    $data['yakinlik_derecesi'],
                    $data['cinsiyet'],
                    $data['kan_grubu'] ?: 'Bilinmiyor',
                    $data['kronik_hastalik'] ?: null,
                    $data['kullandigi_ilaclar'] ?: null,
                    $data['ozel_notlar'] ?: null,
                    $data['acil_durum_kisi'] ?: null,
                    $data['acil_durum_telefon'] ?: null,
                    $data['id'],
                    $kullanici_id
                ]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Aile üyesi başarıyla güncellendi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Güncellenecek kayıt bulunamadı']);
                }
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    echo json_encode(['success' => false, 'message' => 'Bu TC kimlik numarası zaten kayıtlı']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Aile üyesi güncellenemedi: ' . $e->getMessage()]);
                }
            }
        }
        break;

    case 'delete':
        // Aile üyesi sil
        if ($_SERVER['REQUEST_METHOD'] === 'DELETE' || $_SERVER['REQUEST_METHOD'] === 'POST') {
            // GET parametresinden ID al (DELETE request için)
            $id = $_GET['id'] ?? null;
            
            // POST body'den ID al (POST request için)
            if (!$id && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $id = $data['id'] ?? null;
            }
            
            if (!$id) {
                echo json_encode(['success' => false, 'message' => 'ID parametresi eksik']);
                break;
            }
            
            try {
                $stmt = $pdo->prepare("DELETE FROM aile_uyeleri WHERE id = ? AND kullanici_id = ?");
                $stmt->execute([$id, $kullanici_id]);
                
                if ($stmt->rowCount() > 0) {
                    echo json_encode(['success' => true, 'message' => 'Aile üyesi başarıyla silindi']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Silinecek kayıt bulunamadı']);
                }
            } catch (PDOException $e) {
                echo json_encode(['success' => false, 'message' => 'Aile üyesi silinemedi: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Geçersiz HTTP method']);
        }
        break;

    case 'get':
        // Tek aile üyesi getir
        $id = $_GET['id'] ?? 0;
        try {
            $stmt = $pdo->prepare("SELECT * FROM aile_uyeleri WHERE id = ? AND kullanici_id = ?");
            $stmt->execute([$id, $kullanici_id]);
            $aile_uyesi = $stmt->fetch();
            
            if ($aile_uyesi) {
                echo json_encode(['success' => true, 'data' => $aile_uyesi]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Aile üyesi bulunamadı']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Aile üyesi getirilemedi: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Geçersiz işlem']);
        break;
}
?>
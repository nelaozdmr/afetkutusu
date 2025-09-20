<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

// Kullanıcı giriş kontrolü
if(!isset($_SESSION['oturum']) || $_SESSION['rol'] != 'admin') {
    echo json_encode(['success' => false, 'error' => 'Yetkisiz erişim']);
    exit();
}

// Sipariş ID kontrolü
if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'error' => 'Geçersiz sipariş ID']);
    exit();
}

$siparis_id = intval($_GET['id']);

try {
    // Sipariş detaylarını getir
    $stmt = $pdo->prepare("
        SELECT 
            s.id,
            s.siparis_durumu,
            s.siparis_tarihi,
            u.username,
            u.email,
            u.telefon,
            GROUP_CONCAT(
                CONCAT(ur.isim, ' (', su.miktar, ' adet)')
                SEPARATOR ', '
            ) as urunler
        FROM siparisler_yeni s
        LEFT JOIN users u ON s.kullanici_id = u.id
        LEFT JOIN siparis_urunler_yeni su ON s.id = su.siparis_id
        LEFT JOIN urunler ur ON su.urun_id = ur.id
        WHERE s.id = ?
        GROUP BY s.id
    ");
    
    $stmt->execute([$siparis_id]);
    $siparis = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if($siparis) {
        echo json_encode([
            'success' => true,
            'order' => $siparis
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Sipariş bulunamadı'
        ]);
    }
    
} catch(PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Veritabanı hatası: ' . $e->getMessage()
    ]);
}
?>
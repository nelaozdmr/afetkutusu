<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isim = $_POST['isim'];
    $kategori = $_POST['kategori'];
    $aciklama = $_POST['aciklama'];
    $stok = intval($_POST['stok']);
    $resim = $_FILES['resim'];

    // Resim yükleme işlemi
    if ($resim['error'] === 0) {
        $uploads_dir = 'uploads/';
        $dosya_adi = basename($resim['name']);
        $hedef_yol = $uploads_dir . $dosya_adi;

        $uzanti = strtolower(pathinfo($hedef_yol, PATHINFO_EXTENSION));
        $izinli = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($uzanti, $izinli)) {
            move_uploaded_file($resim['tmp_name'], $hedef_yol);

            // Veritabanına kaydet
            $stmt = $pdo->prepare("INSERT INTO urunler (urun_adi, urun_aciklama, urun_fiyat, urun_foto, stok_durumu) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$isim, $aciklama, 0.00, $dosya_adi, 'Var']);

            header("Location: panel.php");
            exit;
        } else {
            echo "Geçersiz resim formatı. Sadece jpg, jpeg, png, gif izinlidir.";
        }
    } else {
        echo "Resim yüklenemedi.";
    }
}
?>

<?php
session_start();
require_once 'db.php';

$success = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ad = $_POST['ad'];
    $soyad = $_POST['soyad'];
    $telefon = $_POST['telefon'];
    $adres = $_POST['adres'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    try {
        // E-posta kontrolü
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->fetch()) {
            $error = "Bu e-posta adresi zaten kayıtlı!";
        } else {
            // Yeni kullanıcı ekle
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, ad, soyad, telefon, adres) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $username = $ad . '_' . $soyad . '_' . rand(100, 999);
            
            if ($stmt->execute([$username, $email, $password, $ad, $soyad, $telefon, $adres])) {
                $success = "Kayıt başarılı! Giriş yapabilirsiniz.";
            } else {
                $error = "Kayıt sırasında bir hata oluştu!";
            }
        }
    } catch (PDOException $e) {
        $error = "Veritabanı hatası: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - AFET KUTUSU</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #d2e3fc 0%, #a8c7fa 50%, #1a73e8 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            text-align: center;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            color: #0355cc;
            font-size: 2.5rem;
            margin-bottom: 10px;
        }

        .title {
            color: #333;
            font-size: 1.8rem;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .form-row {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
            flex: 1;
        }

        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            outline: none;
            border-color: #0355cc;
            background: white;
            box-shadow: 0 0 0 3px rgba(3, 85, 204, 0.1);
        }

        .btn-register {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #0355cc 0%, #0d47a1 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(3, 85, 204, 0.3);
        }

        .btn-register:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #c33;
        }

        .success-message {
            background: #efe;
            color: #3c3;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #3c3;
        }

        .footer-links {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }

        .footer-links a {
            color: #666;
            text-decoration: none;
            margin: 0 15px;
            font-size: 14px;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #0355cc;
        }

        .back-link {
            position: absolute;
            top: 30px;
            left: 30px;
            background: linear-gradient(135deg, #ff8c00 0%, #ff7700 100%);
            color: white;
            text-decoration: none;
            font-size: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            padding: 12px 20px;
            border-radius: 25px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(255, 140, 0, 0.3);
        }

        .back-link:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 140, 0, 0.4);
            text-decoration: none;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 30px 20px;
                margin: 10px;
            }
            
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .back-link {
                position: relative;
                top: auto;
                left: auto;
                margin-bottom: 20px;
                color: #666;
            }
        }
    </style>
</head>
<body>
    <a href="index.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Ana Sayfaya Dön
    </a>

    <div class="register-container">
        <h1 class="title" style="color: #dc3545;">❤️ Hesap Oluşturun</h1>

        <?php if($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <input type="text" name="ad" class="form-control" required 
                           placeholder="Adınız" value="<?= isset($_POST['ad']) ? htmlspecialchars($_POST['ad']) : '' ?>">
                </div>
                <div class="form-group">
                    <input type="text" name="soyad" class="form-control" required 
                           placeholder="Soyadınız" value="<?= isset($_POST['soyad']) ? htmlspecialchars($_POST['soyad']) : '' ?>">
                </div>
            </div>

            <div class="form-group">
                <input type="email" name="email" class="form-control" required 
                       placeholder="E-posta adresiniz" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>

            <div class="form-group">
                <input type="tel" name="telefon" class="form-control" required 
                       placeholder="Telefon numaranız" value="<?= isset($_POST['telefon']) ? htmlspecialchars($_POST['telefon']) : '' ?>">
            </div>

            <div class="form-group">
                <textarea name="adres" class="form-control" rows="3" required 
                          placeholder="Adresiniz" style="resize: vertical;"><?= isset($_POST['adres']) ? htmlspecialchars($_POST['adres']) : '' ?></textarea>
            </div>

            <div class="form-group">
                <input type="password" name="password" class="form-control" required 
                       placeholder="Şifreniz (en az 6 karakter)" minlength="6">
            </div>

            <button type="submit" class="btn-register">
                <i class="fas fa-user-plus"></i> Hesap Oluştur
            </button>
        </form>

        <div class="footer-links">
            <a href="giris-yap.php">Zaten hesabınız var mı? Giriş yapın</a>
        </div>
    </div>

    <script>
        // Telefon formatı
        document.querySelector('input[name="telefon"]').addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0 && value.length <= 11) {
                if (value.length <= 4) {
                    this.value = value;
                } else if (value.length <= 7) {
                    this.value = value.replace(/(\d{4})(\d{1,3})/, '$1 $2');
                } else if (value.length <= 9) {
                    this.value = value.replace(/(\d{4})(\d{3})(\d{1,2})/, '$1 $2 $3');
                } else {
                    this.value = value.replace(/(\d{4})(\d{3})(\d{2})(\d{1,2})/, '$1 $2 $3 $4');
                }
            }
        });

        // Form animasyonları
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });
    </script>
</body>
</html>
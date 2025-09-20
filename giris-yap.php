<?php
session_start();
require_once 'db.php';

// Eğer zaten giriş yapılmışsa panel.php'ye yönlendir
if (isset($_SESSION["oturum"])) {
    header('Location: panel.php');
    exit();
}

// Giriş kontrolü
$error = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email=?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && $password === $user['password']) {
        $_SESSION['oturum'] = true;
        $_SESSION['ad'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['rol'] = $user['rol'] ?? 'kullanici';
        $_SESSION['user_id'] = $user['id'];

        header("Location: panel.php");
        exit();
    } else {
        $error = "Hatalı e-posta veya şifre!";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş Yap - AFET KUTUSU</title>
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

        .login-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 400px;
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

        .form-group {
            margin-bottom: 20px;
            text-align: left;
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

        .btn-login {
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

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(3, 85, 204, 0.3);
        }

        .btn-login:active {
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

        .admin-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e1e5e9;
        }

        .admin-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-admin {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-admin:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(220, 53, 69, 0.3);
            color: white;
            text-decoration: none;
        }

        .btn-admin:active {
            transform: translateY(0);
        }

        .admin-icon {
            font-size: 16px;
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
            .login-container {
                padding: 30px 20px;
                margin: 10px;
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
        <i class="fas fa-arrow-left"></i>
        Ana Sayfaya Dön
    </a>

    <div class="login-container">
        <h1 class="title" style="color: #dc3545;">❤️ Giriş Yap</h1>

        <?php if($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" class="form-control" 
                       placeholder="E-posta adresiniz" required>
            </div>

            <div class="form-group">
                <input type="password" name="password" class="form-control" 
                       placeholder="Şifreniz" required>
            </div>

            <button type="submit" class="btn-login">
                <i class="fas fa-sign-in-alt"></i>
                Giriş Yap
            </button>
        </form>

        <div class="admin-section">
            <div class="admin-label">
                <i class="fas fa-crown"></i>
                Yönetici Girişi
            </div>
            <a href="admin-panel.php" class="btn-admin">
                <i class="fas fa-user-shield admin-icon"></i>
                Admin Paneline Git
            </a>
        </div>

        <div class="footer-links">
            <a href="kayit-ol.php">Kayıt Ol</a>
            <a href="index.php">Ana Sayfa</a>
        </div>
    </div>

    <script>
        // Form animasyonları
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('focus', function() {
                this.style.transform = 'scale(1.02)';
            });
            
            input.addEventListener('blur', function() {
                this.style.transform = 'scale(1)';
            });
        });

        // Enter tuşu ile form gönderme
        document.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>

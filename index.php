<?php
session_start();
function isLogin(){
  if (isset($_SESSION["oturum"])) {
    return true;
  }
  return false;
}
$kullanici_rol = $_SESSION['rol'] ?? 'kullanici';
?>
<html>

<head>
  <meta charset="utf-8">
  <style>
    .LGLeeN-keyboard-shortcuts-view {
      display: -webkit-box;
      display: -webkit-flex;
      display: -moz-box;
      display: -ms-flexbox;
      display: flex
    }

    .LGLeeN-keyboard-shortcuts-view table,
    .LGLeeN-keyboard-shortcuts-view tbody,
    .LGLeeN-keyboard-shortcuts-view td,
    .LGLeeN-keyboard-shortcuts-view tr {
      background: inherit;
      border: none;
      margin: 0;
      padding: 0
    }

    .LGLeeN-keyboard-shortcuts-view table {
      display: table
    }

    .LGLeeN-keyboard-shortcuts-view tr {
      display: table-row
    }

    .LGLeeN-keyboard-shortcuts-view td {
      -moz-box-sizing: border-box;
      box-sizing: border-box;
      display: table-cell;
      color: #000;
      padding: 6px;
      vertical-align: middle;
      white-space: nowrap
    }

    .LGLeeN-keyboard-shortcuts-view td:first-child {
      text-align: end
    }

    .LGLeeN-keyboard-shortcuts-view td kbd {
      background-color: #e8eaed;
      border-radius: 2px;
      border: none;
      -moz-box-sizing: border-box;
      box-sizing: border-box;
      color: inherit;
      display: inline-block;
      font-family: Google Sans Text, Roboto, Arial, sans-serif;
      line-height: 16px;
      margin: 0 2px;
      min-height: 20px;
      min-width: 20px;
      padding: 2px 4px;
      position: relative;
      text-align: center
    }
  </style>
  <style>
    .gm-control-active>img {
      -webkit-box-sizing: content-box;
      box-sizing: content-box;
      display: none;
      left: 50%;
      pointer-events: none;
      position: absolute;
      top: 50%;
      -webkit-transform: translate(-50%, -50%);
      -ms-transform: translate(-50%, -50%);
      transform: translate(-50%, -50%)
    }

    .gm-control-active>img:nth-child(1) {
      display: block
    }

    .gm-control-active:focus>img:nth-child(1),
    .gm-control-active:hover>img:nth-child(1),
    .gm-control-active:active>img:nth-child(1),
    .gm-control-active:disabled>img:nth-child(1) {
      display: none
    }

    .gm-control-active:focus>img:nth-child(2),
    .gm-control-active:hover>img:nth-child(2) {
      display: block
    }

    .gm-control-active:active>img:nth-child(3) {
      display: block
    }

    .gm-control-active:disabled>img:nth-child(4) {
      display: block
    }

    sentinel {}
  </style>
  <link type="text/css" rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700|Google+Sans:400,500,700|Google+Sans+Text:400&amp;lang=tr">
  <link type="text/css" rel="stylesheet"
    href="https://fonts.googleapis.com/css?family=Google+Sans+Text:400&amp;text=%E2%86%90%E2%86%92%E2%86%91%E2%86%93&amp;lang=tr">
  <style>
    .gm-ui-hover-effect {
      opacity: .6
    }

    .gm-ui-hover-effect:hover {
      opacity: 1
    }

    .gm-ui-hover-effect>span {
      background-color: #000
    }

    @media (forced-colors:active),
    (prefers-contrast:more) {
      .gm-ui-hover-effect>span {
        background-color: ButtonText
      }
    }

    sentinel {}
  </style>
  <style>
    .gm-style .gm-style-cc a,
    .gm-style .gm-style-cc button,
    .gm-style .gm-style-cc span,
    .gm-style .gm-style-mtc div {
      font-size: 10px;
      -webkit-box-sizing: border-box;
      box-sizing: border-box
    }

    .gm-style .gm-style-cc a,
    .gm-style .gm-style-cc button,
    .gm-style .gm-style-cc span {
      outline-offset: 3px
    }

    sentinel {}
  </style>
  <style>
    @media print {

      .gm-style .gmnoprint,
      .gmnoprint {
        display: none
      }
    }

    @media screen {

      .gm-style .gmnoscreen,
      .gmnoscreen {
        display: none
      }
    }
  </style>
  <style>
    .dismissButton {
      background-color: #fff;
      border: 1px solid #dadce0;
      color: #1a73e8;
      border-radius: 4px;
      font-family: Roboto, sans-serif;
      font-size: 14px;
      height: 36px;
      cursor: pointer;
      padding: 0 24px
    }

    .dismissButton:hover {
      border: 1px solid #1a73e8;
    }

    .dismissButton:focus {
      background-color: rgba(66, 133, 244, .12);
      border: 1px solid #d2e3fc;
      outline: 0
    }

    .dismissButton:focus:not(:focus-visible) {
      background-color: #fff;
      border: 1px solid #dadce0;
      outline: none
    }

    .dismissButton:focus-visible {
      background-color: rgba(66, 133, 244, .12);
      border: 1px solid #d2e3fc;
      outline: 0
    }

    .dismissButton:hover:focus {
      background-color: rgba(66, 133, 244, .16);
      border: 1px solid #d2e2fd
    }

    .dismissButton:hover:focus:not(:focus-visible) {
      background-color: rgba(66, 133, 244, .04);
      border: 1px solid #d2e3fc
    }

    .dismissButton:hover:focus-visible {
      background-color: rgba(66, 133, 244, .16);
      border: 1px solid #d2e2fd
    }

    .dismissButton:active {
      background-color: rgba(66, 133, 244, .16);
      border: 1px solid #d2e2fd;
      -webkit-box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 1px 3px 1px rgba(60, 64, 67, .15);
      box-shadow: 0 1px 2px 0 rgba(60, 64, 67, .3), 0 1px 3px 1px rgba(60, 64, 67, .15)
    }

    .dismissButton:disabled {
      background-color: #fff;
      border: 1px solid #f1f3f4;
      color: #3c4043
    }

    sentinel {}
  </style>
  <style>
    .gm-style-moc {
      background-color: rgba(0, 0, 0, .45);
      pointer-events: none;
      text-align: center;
      -webkit-transition: opacity ease-in-out;
      transition: opacity ease-in-out
    }

    .gm-style-mot {
      color: white;
      font-family: Roboto, Arial, sans-serif;
      font-size: 22px;
      margin: 0;
      position: relative;
      top: 50%;
      transform: translateY(-50%);
      -webkit-transform: translateY(-50%);
      -ms-transform: translateY(-50%)
    }

    sentinel {}
  </style>
  <style>
    .gm-style img {
      max-width: none;
    }

    .gm-style {
      font: 400 11px Roboto, Arial, sans-serif;
      text-decoration: none;
    }
  </style>
  <style>
    input{
      color: #000 !important;
    }
    .contact_section input.message-box{
      height: 50px !important;
    }
    .slider_section .detail-box a{
      background-color: #0355cc !important;
      border-color: #0355cc !important;
    }
    .slider_section .detail-box a:hover{
      color: #000 !important;
      background-color: #fff !important;
    }
    .info_items .item .img-box{
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }
    html{
      scroll-behavior: smooth;
    }
  </style>
  <!-- Basic -->
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <!-- Mobile Metas -->
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <!-- Site Metas -->
  <meta name="keywords" content="">
  <meta name="description" content="">
  <meta name="author" content="Nazife Ela Özdemir">

  <title>AFET KUTUSU - Afet Durumlarında Güvenilir Çözümler</title>

  <!-- slider stylesheet -->
  <link rel="stylesheet" type="text/css"
    href="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/assets/owl.carousel.min.css">
  <!-- bootstrap core css -->
  <link rel="stylesheet" type="text/css" href="css/bootstrap.css">
  <!-- font awesome style -->
  <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
  <!-- Font Awesome CDN for better icon support -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Custom styles for this template -->
  <link href="css/style.css" rel="stylesheet">
  <!-- responsive style -->
  <link href="css/responsive.css" rel="stylesheet">
  
  <!-- Ultra Modern Navbar Styles -->
  <style>
    /* Ultra Modern Header Styles */
    .modern-header {
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.95) 0%, 
            rgba(240, 248, 255, 0.95) 50%, 
            rgba(230, 245, 255, 0.95) 100%);
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        padding: 1.2rem 0;
        position: sticky;
        top: 0;
        z-index: 1000;
        box-shadow: 
            0 8px 32px rgba(26, 115, 232, 0.12),
            0 2px 8px rgba(0, 0, 0, 0.04);
        transition: all 0.3s ease;
    }

    .modern-header:hover {
        box-shadow: 
            0 12px 40px rgba(26, 115, 232, 0.15),
            0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .modern-header-content {
        max-width: 1400px;
        margin: 0 auto;
        padding: 0 2.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 1.8rem;
        font-weight: 900;
        text-decoration: none;
        padding: 16px 28px;
        border-radius: 30px;
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.9) 0%, 
            rgba(240, 248, 255, 0.8) 50%, 
            rgba(220, 240, 255, 0.9) 100%);
        border: 2px solid rgba(26, 115, 232, 0.1);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 
            0 4px 20px rgba(26, 115, 232, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }

    .logo::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, 
            transparent, 
            rgba(255, 255, 255, 0.6), 
            transparent);
        transition: left 0.8s ease;
    }

    .logo:hover::before {
        left: 100%;
    }

    .logo:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow: 
            0 12px 35px rgba(26, 115, 232, 0.25),
            inset 0 1px 0 rgba(255, 255, 255, 0.9);
        background: linear-gradient(135deg, 
            rgba(255, 255, 255, 0.95) 0%, 
            rgba(235, 245, 255, 0.9) 50%, 
            rgba(210, 235, 255, 0.95) 100%);
        border-color: rgba(26, 115, 232, 0.2);
    }

    .logo-text {
        color: #1a1a1a;
        font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        letter-spacing: -0.8px;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        background: linear-gradient(135deg, #1a1a1a, #333333);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        white-space: nowrap;
    }

    .logo i {
        font-size: 2.4rem;
        color: #dc3545;
        animation: heartbeat 2.5s ease-in-out infinite;
        filter: drop-shadow(0 3px 6px rgba(220, 53, 69, 0.4));
        transition: all 0.3s ease;
    }

    .logo:hover i {
        transform: scale(1.1);
        filter: drop-shadow(0 4px 8px rgba(220, 53, 69, 0.5));
    }

    @keyframes heartbeat {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.08); }
    }

    .modern-nav {
        display: flex;
        align-items: center;
        gap: 2.5rem;
    }

    .modern-nav-links {
        display: flex;
        gap: 1rem;
        list-style: none;
        margin: 0;
        padding: 0;
        background: rgba(255, 255, 255, 0.4);
        border-radius: 50px;
        padding: 8px;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .modern-nav-link {
        color: #4a5568;
        text-decoration: none;
        font-weight: 600;
        padding: 14px 22px;
        border-radius: 35px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.95rem;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }

    .modern-nav-link::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 0;
        height: 100%;
        background: linear-gradient(135deg, #1a73e8, #4285f4);
        transition: width 0.4s ease;
        border-radius: 35px;
        z-index: -1;
    }

    .modern-nav-link:hover::before {
        width: 100%;
    }

    .modern-nav-link:hover {
        color: white;
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 8px 25px rgba(26, 115, 232, 0.3);
        text-decoration: none;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .modern-nav-link i {
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .modern-nav-link:hover i {
        transform: scale(1.1);
    }

    .modern-nav-link.active {
        color: white;
        background: linear-gradient(135deg, #1a73e8, #1557b0);
        box-shadow: 
            0 6px 20px rgba(26, 115, 232, 0.4),
            inset 0 1px 0 rgba(255, 255, 255, 0.2);
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    .modern-nav-link.active::before {
        width: 100%;
    }

    .modern-nav-link.active:hover {
        background: linear-gradient(135deg, #1a73e8, #1557b0);
        transform: translateY(-2px) scale(1.02);
    }

    /* Çıkış butonu için özel stil */
    .modern-nav-link[href="cikis-yap.php"] {
        background: linear-gradient(135deg, rgba(255, 99, 99, 0.1), rgba(255, 120, 120, 0.1));
        color: #e53e3e;
        border: 1px solid rgba(255, 99, 99, 0.3);
        margin-left: 1rem;
    }

    .modern-nav-link[href="cikis-yap.php"]::before {
        background: linear-gradient(135deg, #e53e3e, #c53030);
    }

    .modern-nav-link[href="cikis-yap.php"]:hover {
        color: white;
        border-color: rgba(255, 99, 99, 0.5);
        box-shadow: 0 8px 25px rgba(229, 62, 62, 0.4);
    }

    /* Giriş butonu için özel stil */
    .modern-nav-link[href="giris-yap.php"] {
        background: linear-gradient(135deg, rgba(72, 187, 120, 0.1), rgba(56, 178, 172, 0.1));
        color: #38a169;
        border: 1px solid rgba(72, 187, 120, 0.3);
        margin-left: 1rem;
    }

    .modern-nav-link[href="giris-yap.php"]::before {
        background: linear-gradient(135deg, #38a169, #2f855a);
    }

    .modern-nav-link[href="giris-yap.php"]:hover {
        color: white;
        border-color: rgba(72, 187, 120, 0.5);
        box-shadow: 0 8px 25px rgba(56, 161, 105, 0.4);
    }

    /* Mobile responsive */
    @media (max-width: 1024px) {
        .modern-nav-links {
            gap: 0.5rem;
            padding: 6px;
        }
        
        .modern-nav-link {
            padding: 12px 18px;
            font-size: 0.9rem;
        }
        
        .modern-header-content {
            padding: 0 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .modern-header-content {
            flex-direction: column;
            gap: 1.5rem;
            padding: 0 1rem;
        }
        
        .modern-nav-links {
            flex-wrap: wrap;
            justify-content: center;
            gap: 0.3rem;
            padding: 4px;
        }
        
        .modern-nav-link {
            padding: 10px 16px;
            font-size: 0.85rem;
        }
        
        .logo {
            font-size: 1.6rem;
            padding: 14px 24px;
        }
        
        .logo i {
            font-size: 2rem;
        }
    }

    @media (max-width: 480px) {
        .modern-nav-links {
            flex-direction: column;
            width: 100%;
            gap: 0.2rem;
        }
        
        .modern-nav-link {
            justify-content: center;
            width: 100%;
        }
        
        .modern-header {
            padding: 1rem 0;
        }
    }
  </style>

  <script type="text/javascript" charset="UTF-8"
    src="https://maps.googleapis.com/maps-api-v3/api/js/55/4/intl/tr_ALL/common.js"></script>
  <script type="text/javascript" charset="UTF-8"
    src="https://maps.googleapis.com/maps-api-v3/api/js/55/4/intl/tr_ALL/util.js"></script>
  <script type="text/javascript" charset="UTF-8"
    src="https://maps.googleapis.com/maps-api-v3/api/js/55/4/intl/tr_ALL/map.js"></script>
  <script type="text/javascript" charset="UTF-8"
    src="https://maps.googleapis.com/maps-api-v3/api/js/55/4/intl/tr_ALL/onion.js"></script>
  <script type="text/javascript" charset="UTF-8"
    src="https://maps.googleapis.com/maps-api-v3/api/js/55/4/intl/tr_ALL/controls.js"></script>
</head>

<body>
  <div class="hero_area">
    <!-- Modern Header -->
    <header class="modern-header">
        <div class="modern-header-content">
            <span class="logo" style="cursor: default; pointer-events: none;">
                <i class="fas fa-heart" style="color: #dc3545;"></i>
                <span class="logo-text">AFET KUTUSU</span>
            </span>
            <nav class="modern-nav">
                <ul class="modern-nav-links">
                    <li><a href="index.php" class="modern-nav-link active"><i class="fas fa-home"></i> Ana Sayfa</a></li>
                    <li><a href="#hakkimizda" class="modern-nav-link"><i class="fas fa-info-circle"></i> Hakkımızda</a></li>
                    <li><a href="#kuruluslar" class="modern-nav-link"><i class="fas fa-building"></i> Kuruluşlar</a></li>
                    <li><a href="#bize-ulas" class="modern-nav-link"><i class="fas fa-envelope"></i> Bize Ulaş</a></li>
                    <?php if(isLogin()): ?>
                        <li><a href="urunler.php" class="modern-nav-link"><i class="fas fa-cube"></i> Ürünler</a></li>
                        <?php if ($kullanici_rol !== 'admin'): ?>
                        <li><a href="sepet.php" class="modern-nav-link"><i class="fas fa-shopping-cart"></i> Sepet</a></li>
                        <li><a href="siparisler.php" class="modern-nav-link"><i class="fas fa-list"></i> Siparişlerim</a></li>
                        <?php endif; ?>
                        <li><a href="profil.php" class="modern-nav-link"><i class="fas fa-user"></i> Profil</a></li>
                        <?php if ($kullanici_rol === 'admin'): ?>
                            <li><a href="admin-panel.php" class="modern-nav-link"><i class="fas fa-cog"></i> Admin Panel</a></li>
                        <?php endif; ?>
                        <li><a href="cikis-yap.php" class="modern-nav-link"><i class="fas fa-sign-out-alt"></i> Çıkış</a></li>
                    <?php else: ?>
                        <li><a href="giris-yap.php" class="modern-nav-link"><i class="fas fa-sign-in-alt"></i> Giriş Yap</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <!-- end header section -->
    <!-- slider section -->
    <section class="slider_section ">
      <div class="container ">
        <div class="row" style="
    height: 28rem;
    display: flex;
    flex-direction: column;
    text-align: center;
    margin-top: 6rem;
">
          <div class="col-md-6 ">
            <div class="detail-box">
              <h1>Afet Yardım</h1>
              <p>Biz, depremlerin yarattığı acil durumların farkındayız ve topluluklarımızın bu zorlu zamanlarda dayanışma içinde olması gerektiğine inanıyoruz. Depremler, birçok kişinin hayatını etkileyebilecek ciddi felaketlere neden olabilir ve bu nedenle biz, bu zor durumlarla başa çıkmak için bir araya gelmiş bir grup gönüllüyüz.</p>
            <a href="kayit-ol.php" style="
    background: #0355cc;
    border-color: #0355cc;
">Üye Ol</a>
              <a target="_blank" href="https://ahbap.org/bagisci-ol">Bağış Yap 🤍</a>
            </div>
          </div>

        </div>
      </div>
    </section>
    <!-- end slider section -->
  </div>

  <!-- feature section -->
  <section class="feature_section">
    <div class="container">
      <div class="feature_container">
        <div class="box">
          <div class="img-box">

            <!--?xml version="1.0" encoding="utf-8"?-->


            <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
            <svg height="800px" width="800px" version="1.1" id="_x32_" xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 512 512" xml:space="preserve">
              <style type="text/css">
                .st0 {
                  fill: #000000;
                }
              </style>
              <g>
                <path class="st0" d="M496.771,326.221c-18.201-13.151-35.677-32.849-51.318-55.047c-23.496-33.29-42.933-72.082-56.371-102.445
		c-6.726-15.181-11.966-28.274-15.516-37.527c-1.773-4.634-3.126-8.297-4.027-10.813c-0.452-1.25-0.791-2.206-1.018-2.854
		c-0.221-0.633-0.324-0.926-0.324-0.926l-2.792-8.164l-8.528,1.25c-24.163,3.516-47.023,4.928-68.085,4.921
		c-40.708,0.007-74.737-5.23-98.492-10.459c-11.879-2.611-21.184-5.208-27.476-7.142c-3.141-0.956-5.531-1.758-7.109-2.309
		c-0.79-0.273-1.375-0.486-1.754-0.625l-0.404-0.148l-0.084-0.022l-10.544-4.053l-3.148,10.85l-0.102,0.368
		c-2.295,7.958-44.217,146.209-129.078,255.369L0,370.089l300.752,52.053l2.732-1.096L512,337.24L496.771,326.221z M188.911,381.724
		l-1.027-43.632c-32.874-3.464-48.446-67.485-48.446-67.485l-28.608,97.598l-73.671-12.754
		c40.098-54.797,69.927-114.14,90.077-161.198c9.139-21.36,16.285-40.116,21.537-54.783
		c30.164,125.312,96.144,218.35,127.53,257.377L188.911,381.724z M300.435,399.892c-22.08-25.361-108.575-132.462-139.442-281.767
		c22.276,6.502,68.063,17.24,127.799,17.256c19.499,0,40.538-1.25,62.63-4.075c5.149,13.938,17.087,44.617,34.456,79.364
		c11.809,23.625,26.123,49.096,42.61,72.479c12.699,17.984,26.678,34.702,41.984,48.412L300.435,399.892z"></path>
              </g>
            </svg>
          </div>
          <h5 class="name">Çadır Yardımı</h5>
        </div>
        <div class="box active">
          <div class="img-box">

            <!--?xml version="1.0" encoding="utf-8"?-->
            <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->
            <svg width="800px" height="800px" viewBox="0 0 1024 1024" fill="#000000" class="icon" version="1.1"
              xmlns="http://www.w3.org/2000/svg">
              <path
                d="M791.942 1023.906H152.072a7.94 7.94 0 0 1-5.78-2.484 7.946 7.946 0 0 1-2.21-5.876l31.994-703.862a8 8 0 0 1 4.412-6.794c3.446-1.726 34.19-16.84 51.568-16.84 9.888 0 18.878 4.498 27.572 8.842 7.358 3.678 14.302 7.154 20.41 7.154 6.108 0 13.06-3.476 20.416-7.154 8.702-4.344 17.692-8.842 27.574-8.842 9.88 0 18.872 4.498 27.572 8.842 7.358 3.678 14.308 7.154 20.416 7.154s13.06-3.476 20.418-7.154c8.702-4.344 17.692-8.842 27.572-8.842s18.872 4.49 27.572 8.842c7.358 3.678 14.318 7.154 20.426 7.154s13.06-3.476 20.416-7.154c8.694-4.344 17.684-8.842 27.572-8.842s18.872 4.498 27.572 8.842c7.358 3.678 14.31 7.154 20.418 7.154s13.06-3.476 20.418-7.154c8.702-4.344 17.684-8.842 27.572-8.842s18.872 4.498 27.572 8.842c7.358 3.678 14.31 7.154 20.418 7.154s13.06-3.476 20.418-7.154c8.702-4.344 17.684-8.842 27.572-8.842 17.386 0 48.116 15.114 51.566 16.84a7.98 7.98 0 0 1 4.406 6.794l31.992 703.862a7.91 7.91 0 0 1-2.202 5.876 7.904 7.904 0 0 1-5.772 2.484z m-631.496-15.998h623.12l-31.398-690.732c-10.56-4.936-30.212-13.13-40.21-13.13-6.11 0-13.06 3.476-20.418 7.154-8.702 4.344-17.684 8.842-27.572 8.842s-18.872-4.498-27.572-8.842c-7.358-3.678-14.308-7.154-20.418-7.154-6.108 0-13.058 3.476-20.418 7.154-8.702 4.344-17.684 8.842-27.572 8.842s-18.872-4.498-27.572-8.842c-7.358-3.678-14.308-7.154-20.418-7.154-6.108 0-13.058 3.476-20.416 7.154-8.694 4.344-17.684 8.842-27.572 8.842-9.888 0-18.878-4.498-27.582-8.842-7.358-3.678-14.308-7.154-20.416-7.154s-13.06 3.476-20.416 7.154c-8.702 4.344-17.692 8.842-27.574 8.842-9.88 0-18.872-4.498-27.572-8.842-7.358-3.678-14.31-7.154-20.416-7.154-6.11 0-13.06 3.476-20.418 7.154-8.702 4.344-17.692 8.842-27.572 8.842s-18.872-4.49-27.564-8.842c-7.358-3.678-14.31-7.154-20.418-7.154-9.154 0-27.94 7.374-40.218 13.122l-31.398 690.74z"
                fill=""></path>
              <path
                d="M759.948 991.912H184.066a7.994 7.994 0 0 1-7.998-7.998 7.994 7.994 0 0 1 7.998-7.998h575.88a7.994 7.994 0 0 1 7.998 7.998 7.992 7.992 0 0 1-7.996 7.998zM532.916 272.052a7.99 7.99 0 0 1-7.514-10.732l75.828-208.332C609.4 30.518 628.24 13.608 654.282 5.36c25.322-8.006 54.488-6.85 82.248 3.248 59.8 21.746 75.016 77.32 59.144 120.976l-33.698 136.394a7.978 7.978 0 0 1-9.684 5.842 8.004 8.004 0 0 1-5.844-9.686l33.948-137.206c12.996-35.882 2.92-82.28-49.334-101.292-24.464-8.896-50.006-9.944-71.954-3.024-21.2 6.708-36.414 20.152-42.852 37.844l-75.828 208.332a8 8 0 0 1-7.512 5.264z"
                fill=""></path>
              <path
                d="M748.668 165.702a7.97 7.97 0 0 1-6.748-3.71c-0.11-0.156-13.482-20.168-49.146-33.126-35.648-12.974-58.754-6.256-59.004-6.202-4.108 1.274-8.67-1.062-9.982-5.288a7.972 7.972 0 0 1 5.218-9.974c1.108-0.368 28.134-8.56 69.236 6.436 41.162 14.958 56.55 38.578 57.174 39.578a7.996 7.996 0 0 1-2.452 11.038 8.08 8.08 0 0 1-4.296 1.248zM715.832 255.9a7.976 7.976 0 0 1-6.75-3.71c-0.11-0.156-13.48-20.168-49.128-33.142-35.712-12.99-58.754-6.248-59.004-6.186-4.062 1.28-8.67-1.054-9.982-5.282a7.976 7.976 0 0 1 5.216-9.982c1.124-0.344 28.12-8.53 69.236 6.42 41.146 14.974 56.536 38.594 57.16 39.594a7.994 7.994 0 0 1-6.748 12.288zM216.052 272.052a7.998 7.998 0 0 1-7.912-6.866L176.146 41.234a8 8 0 0 1 6.788-9.052c4.39-0.664 8.428 2.414 9.052 6.788l31.994 223.954a8 8 0 0 1-7.928 9.128z"
                fill=""></path>
              <path
                d="M264.042 272.052a7.994 7.994 0 0 1-7.772-6.146c-0.398-1.664-40.084-167.082-78.708-221.158a7.994 7.994 0 0 1 1.858-11.154 7.986 7.986 0 0 1 11.154 1.858c40.438 56.614 79.61 219.83 81.258 226.75a7.996 7.996 0 0 1-7.79 9.85zM312.032 272.052a7.994 7.994 0 0 1-7.764-6.124c-35.812-148.296-78.648-221-79.078-221.72a8 8 0 0 1 2.756-10.966 7.988 7.988 0 0 1 10.966 2.75c1.804 3 44.444 75.204 80.898 226.188a7.986 7.986 0 0 1-5.898 9.646 7.734 7.734 0 0 1-1.88 0.226z"
                fill=""></path>
              <path
                d="M224.066 80.094c-0.64 0-1.296-0.078-1.946-0.242a7.988 7.988 0 0 1-5.82-9.694l8-31.994a7.958 7.958 0 0 1 9.694-5.818 7.986 7.986 0 0 1 5.818 9.694l-7.998 31.994a7.988 7.988 0 0 1-7.748 6.06zM504 272.052a7.994 7.994 0 0 1-7.998-7.998v-55.988h-143.97v55.988c0 4.422-3.578 7.998-7.998 7.998s-7.998-3.576-7.998-7.998v-63.986a7.994 7.994 0 0 1 7.998-7.998H504a7.994 7.994 0 0 1 7.998 7.998v63.986a7.994 7.994 0 0 1-7.998 7.998z"
                fill=""></path>
              <path
                d="M456.01 272.052a7.994 7.994 0 0 1-7.998-7.998c0-13.232-10.764-23.994-23.996-23.994s-23.996 10.762-23.996 23.994a7.994 7.994 0 0 1-7.998 7.998 7.992 7.992 0 0 1-7.998-7.998c0-22.05 17.942-39.992 39.992-39.992s39.992 17.942 39.992 39.992a7.994 7.994 0 0 1-7.998 7.998zM344.034 192.07a7.996 7.996 0 0 1-5.656-13.654l15.998-15.996a7.996 7.996 0 1 1 11.31 11.31l-15.998 15.996a7.964 7.964 0 0 1-5.654 2.344z"
                fill=""></path>
              <path
                d="M488.004 176.074h-127.974c-4.42 0-7.998-3.578-7.998-7.998s3.578-8 7.998-8h127.974c4.42 0 7.998 3.578 7.998 8s-3.578 7.998-7.998 7.998z"
                fill=""></path>
              <path
                d="M504 192.07a7.98 7.98 0 0 1-5.656-2.344l-15.998-15.996a7.996 7.996 0 1 1 11.31-11.31l15.998 15.996A7.996 7.996 0 0 1 504 192.07zM376.402 779.614a8.02 8.02 0 0 1-5.312-2.016c-32.446-28.838-51.052-70.236-51.052-113.618 0-83.794 68.172-151.976 151.968-151.976 11.348 0 22.66 1.258 33.626 3.726a7.994 7.994 0 0 1 6.044 9.558c-0.976 4.312-5.294 7.032-9.56 6.046a137.7 137.7 0 0 0-30.11-3.328c-74.978 0-135.972 60.988-135.972 135.972a136.104 136.104 0 0 0 45.678 101.65 8.006 8.006 0 0 1-5.31 13.986zM472.006 815.95a152.81 152.81 0 0 1-33.79-3.782 7.994 7.994 0 0 1-6.022-9.576c0.976-4.31 5.25-7.044 9.568-6.014a136.964 136.964 0 0 0 30.244 3.374c74.97 0 135.972-61.002 135.972-135.97a136.184 136.184 0 0 0-45.302-101.34 8 8 0 0 1-0.64-11.292c2.984-3.296 8.06-3.562 11.294-0.624a152.126 152.126 0 0 1 50.644 113.258c0 83.792-68.174 151.966-151.968 151.966z"
                fill=""></path>
              <path
                d="M504 703.972a7.98 7.98 0 0 1-5.656-2.344l-63.986-63.988a7.996 7.996 0 1 1 11.31-11.308l63.988 63.986a7.996 7.996 0 0 1-5.656 13.654z"
                fill=""></path>
              <path
                d="M440.014 639.984a7.994 7.994 0 0 1-5.656-13.652l159.966-159.974a7.996 7.996 0 1 1 11.31 11.31l-159.966 159.974a7.97 7.97 0 0 1-5.654 2.342zM360.03 847.942a7.994 7.994 0 0 1-5.654-13.652l143.97-143.97a7.996 7.996 0 1 1 11.31 11.31l-143.97 143.968a7.976 7.976 0 0 1-5.656 2.344zM759.948 320.042a7.988 7.988 0 0 1-6.404-3.202 8.006 8.006 0 0 1 1.608-11.192l63.986-47.99a7.99 7.99 0 0 1 11.202 1.602 8.006 8.006 0 0 1-1.608 11.192l-63.988 47.99c-1.44 1.078-3.124 1.6-4.796 1.6zM807.938 991.912a8.176 8.176 0 0 1-3.578-0.844 8.008 8.008 0 0 1-3.578-10.732l31.994-63.986c1.984-3.966 6.794-5.53 10.732-3.578a8.002 8.002 0 0 1 3.578 10.732l-31.994 63.988a8.016 8.016 0 0 1-7.154 4.42z"
                fill=""></path>
              <path
                d="M850.584 943.906a7.992 7.992 0 0 1-6.654-3.562l-10.654-15.98a7.996 7.996 0 0 1 2.218-11.092c3.624-2.452 8.624-1.482 11.092 2.218l10.652 15.982a8 8 0 0 1-6.654 12.434zM871.908 975.916a8.01 8.01 0 0 1-7.966-7.452L815.952 264.6a8.008 8.008 0 0 1 7.436-8.53c4.204-0.266 8.216 3.024 8.53 7.436l47.99 703.864a8.02 8.02 0 0 1-7.436 8.53c-0.188 0-0.376 0.016-0.564 0.016z"
                fill=""></path>
              <path
                d="M791.942 1023.906a8.004 8.004 0 0 1-6.86-3.89 7.976 7.976 0 0 1 2.75-10.966l79.984-47.99a7.994 7.994 0 1 1 8.214 13.716l-79.982 47.99c-1.28 0.766-2.702 1.14-4.106 1.14z"
                fill=""></path>
              <path
                d="M823.934 272.052h-31.992c-4.422 0-8-3.576-8-7.998a7.994 7.994 0 0 1 8-7.998h31.992a7.994 7.994 0 0 1 7.998 7.998 7.992 7.992 0 0 1-7.998 7.998z"
                fill=""></path>
            </svg>
          </div>
          <h5 class="name">Gıda Yardımı</h5>
        </div>
        <div class="box">
          <div class="img-box">

            <!--?xml version="1.0" encoding="iso-8859-1"?-->
            <!-- Uploaded to: SVG Repo, www.svgrepo.com, Generator: SVG Repo Mixer Tools -->

            <svg fill="#000000" height="800px" width="800px" version="1.1" id="Capa_1"
              xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
              viewBox="0 0 413.021 413.021" xml:space="preserve">
              <g>
                <path d="M409.271,187.254L143.918,34.052c-2.32-1.34-5.18-1.34-7.5,0L3.75,110.648c-2.32,1.34-3.75,3.816-3.75,6.495v102.129
		c0,2.679,1.43,5.156,3.75,6.495l265.354,153.202c1.16,0.67,2.455,1.005,3.75,1.005s2.59-0.335,3.75-1.005l132.667-76.598
		c2.32-1.34,3.75-3.816,3.75-6.495V193.749C413.021,191.07,411.592,188.594,409.271,187.254z M398.021,266.038l-117.667,67.911
		v-8.211l117.667-67.92V266.038z M151.744,96.875l-26.168-0.006V57.632l10.477-6.049L151.744,96.875z M110.576,95.286l-37.663-7.249
		l37.663-21.745V95.286z M110.572,110.56l-0.008,10.934l-79.666-9.199l22.174-12.802L110.572,110.56z M15,155.689l250.354,144.524
		v8.21L15,163.899V155.689z M265.354,282.893L15,138.369v-8.236l250.354,144.542V282.893z M15,181.219l250.354,144.524v8.21
		L15,189.429V181.219z M398.021,240.498l-117.667,67.92v-8.21l117.667-67.92V240.498z M398.021,214.968l-117.667,67.92v-8.213
		l117.667-67.936V214.968z M272.854,261.685L42.596,128.745l74.601,8.614c2.121,0.245,4.252-0.427,5.849-1.849
		s2.51-3.458,2.512-5.596l0.013-18.046l36.708,0.009c0.001,0,0.001,0,0.002,0c2.426,0,4.702-1.174,6.109-3.15
		c1.407-1.977,1.771-4.512,0.978-6.805L153.84,57.101l236.682,136.648L272.854,261.685z M15,206.749l250.354,144.524v8.211
		L15,214.942V206.749z M280.354,359.484v-8.216l117.667-67.911v8.189L280.354,359.484z"></path>
                <path d="M184.416,124.655c1.274,0,2.565-0.325,3.748-1.009l22.094-12.776c3.585-2.073,4.812-6.661,2.737-10.247
		c-2.073-3.587-6.66-4.811-10.247-2.738l-22.094,12.776c-3.585,2.073-4.812,6.661-2.737,10.247
		C179.307,123.312,181.826,124.655,184.416,124.655z"></path>
              </g>
            </svg>
          </div>
          <h5 class="name">Kıyafet Yardımı</h5>
        </div>
      </div>
    </div>
  </section>

  <!-- end feature section -->

  <!-- about section -->

  <section class="about_section layout_padding-bottom" id="hakkimizda">
    <div class="container">
      <div class="row">
        <div class="col-lg-5 col-md-6">
          <div class="detail-box">
            <h2>Hakkımızda</h2>
            <p>Deprem Yardımı adı altında kurulan bu platform, depremzedelerin ihtiyaçlarını karşılamak, acil yardım sağlamak ve topluluklar arasında dayanışmayı teşvik etmek amacıyla faaliyet göstermektedir. Amacımız, depremlerin neden olduğu acil durumlarla baş etmekte yardımcı olmak ve etkilenen bölgelerdeki insanlara destek olmaktır.</p>

            <h2 style="margin-top: 3rem;">MİSYONUMUZ:</h2>

    <p>Deprem Yardımı olarak misyonumuz, depremzedelerin acil ihtiyaçlarını karşılamak, güvenli barınma, gıda, su ve tıbbi yardım gibi temel ihtiyaçları sağlamak ve toplulukları bir araya getirerek dayanışmayı desteklemektir. Ayrıca, deprem öncesi eğitim ve hazırlık konusunda toplulukları bilinçlendirmek ve direncini artırmak için çaba gösteriyoruz.</p>

    <h2 style="margin-top: 3rem;">VİZYONUMUZ:</h2>

    <p>Deprem Yardımı olarak vizyonumuz, depremlerin etkilediği bölgelerde hızlı ve etkili yardım sağlayarak toplulukları güçlendirmek ve dayanıklılıklarını artırmaktır. Bu süreçte, insanların hayatlarını yeniden inşa etmelerine destek olmak ve uzun vadeli sürdürülebilir çözümler sunmak amacındayız.</p>

    <p>Biz, gönüllülerden oluşan bir ekip olarak, depremlerin yarattığı acil durumlarla mücadelede topluluklar arasında bir köprü oluşturmak için bir araya geldik. Siz de bize katılın, çünkü birlikte daha güçlüyüz.</p>

          </div>
        </div>
        <div class="col-lg-7 col-md-6">
          <div class="img-box">
            <img
              src="https://cdn1.ntv.com.tr/gorsel/pS97WfqAxEGMR6WOaieH3w.jpg?width=1000&amp;mode=both&amp;scale=both&amp;v=1580363405895"
              alt="">
          </div>
          <div class="img-box" style="margin-top: 3rem;">
            <img
              src="https://iasbh.tmgrup.com.tr/15ca41/0/0/0/0/2048/1536?u=https://isbh.tmgrup.com.tr/sb/album/2023/02/10/deprem-bolgesine-yardim-seferberligi-kahramanmarasta-kurulan-cadir-kentte-yeni-hayat-basladi-1676017814478.jfif"
              alt="">
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end about section -->


  <section class="about_section layout_padding-bottom" id="kuruluslar">
    <div class="container">
      <div class="row">
        <div class="col-sm-12">
          <div class="detail-box">
            <h2>Kuruluşlar</h2>

            <p>Depremler, aniden ortaya çıkabilen doğal afetlerdir ve hızlı müdahale gerektirebilir. Acil durumlarla başa çıkmak ve yardım almak için aşağıdaki kuruluşlara başvurabilirsiniz:</p>

            <div class="row">
              <div class="col-md-6" style="display: flex;flex-direction: column;align-items: center;justify-content: center;min-height: 25rem;padding: 1rem;border: 2px dashed #0355cc;text-align: center;">
                <img height="200px" src="https://www.afad.gov.tr/kurumlar/afad.gov.tr/Kurumsal-Kimlik/Logolar/PNG/AFAD-Logo-Renkli.png" alt="">
                <p><strong>AFAD (Afet ve Acil Durum Yönetimi Başkanlığı):</strong> Türkiye'de AFAD, deprem gibi acil durumlarla başa çıkmak için koordinasyon sağlayan bir kuruluştur. AFAD'ın 112 Acil Çağrı Merkezi'ne başvurarak yardım talebinde bulunabilirsiniz.</p>
              </div>
              <div class="col-md-6" style="display: flex;flex-direction: column;align-items: center;justify-content: center;min-height: 25rem;padding: 1rem;border: 2px dashed #cc0303;text-align: center;">
                <img height="200px" src="https://www.gazeteipekyol.com/upload/125714-1694120231.jpg" alt="">
                <p><strong>Türk Kızılayı:</strong> Türk Kızılayı, depremzedelere yardım sağlayan önemli bir kuruluştur. Acil durumlarda barınma, gıda, su ve temel ihtiyaç malzemeleri konusunda destek alabilirsiniz.</p>
              </div>
            </div>

            <p style="margin-top: 3rem;">Unutmayın ki, deprem anında sakin olmak ve yetkililere hızlı bir şekilde ulaşmak önemlidir. Acil durum planınızı bilmeniz ve ailenizle iletişim kurmanız da hayati öneme sahiptir.</p>

          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- professional section -->



  <!-- end professional section -->

  <!-- service section -->



  <!-- end service section -->



  <!-- contact section -->




  <!-- info section -->
  <section class="info_section " id="bize-ulas">
    <div class="container">
      <h4>BİZE ULAŞ</h4>
      <div class="row">
        <div class="col-lg-10 mx-auto">
          <div class="info_items">
            <div class="row">
              <div class="col-md-4">
                <a href="">
                  <div class="item ">
                    <div class="img-box ">
                      <i class="fa fa-map-marker" aria-hidden="true"></i>
                    </div>
                    <div class="detail-box">
                      <h5>
                        Konum
                      </h5>
                      <p>
                        Türkiye
                      </p>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-md-4">
                <a href="">
                  <div class="item ">
                    <div class="img-box ">
                      <i class="fa fa-phone" aria-hidden="true"></i>
                    </div>
                    <div class="detail-box">
                      <h5>
                        Telefon
                      </h5>
                      <p>
                        +90 555 123 45 67
                      </p>
                    </div>
                  </div>
                </a>
              </div>
              <div class="col-md-4">
                <a href="">
                  <div class="item ">
                    <div class="img-box">
                      <i class="fa fa-envelope-o" aria-hidden="true"></i>
                    </div>
                    <div class="detail-box">
                      <h5>
                        E-posta
                      </h5>
                      <p>
                        info@afetyardim.com
                      </p>
                    </div>
                  </div>
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- end info section -->

  <!-- footer section -->
  <section class="footer_section">
    <div class="container">
      <p>
        &copy; <span id="displayYear"></span> Tüm Hakları Saklıdır
        <a href="https://html.design/">Afet Yardım</a>
      </p>
    </div>
  </section>
  <!-- footer section -->

  <!-- jQery -->
  <script type="text/javascript" src="js/jquery-3.4.1.min.js"></script>
  <!-- popper js -->
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous">
  </script>
  <!-- bootstrap js -->
  <script type="text/javascript" src="js/bootstrap.js"></script>
  <!-- owl slider -->
  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/OwlCarousel2/2.3.4/owl.carousel.min.js">
  </script>
  <!-- custom js -->
  <script type="text/javascript" src="js/custom.js"></script>


</body>

</html>
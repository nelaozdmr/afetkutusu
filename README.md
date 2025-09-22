# 🏠 Afet Kutusu - Afet Sonrası Yardım Organizasyonun Optimize Edilmesi

Afet sonrası durumlarında ihtiyaçları ücretsiz koordineli bir şekilde karşılamak için tasarlanmış kapsamlı bir web uygulaması.

> **Not:** Bu proje, afet mağdurlarının temel ihtiyaçlarını karşılamak amacıyla geliştirilmiş bir yardım organizasyon sistemidir.

**** WEB SİTESİNİN TANITIM VİDEOSU:https://drive.google.com/file/d/1Ped3yisuywvig-65xbidAyjkjt-J-78G/view?usp=drive_web

## 🚀 Özellikler

### 👥 Kullanıcı Yönetimi
- Kullanıcı kayıt ve giriş sistemi
- Profil yönetimi
- Aile üyesi ekleme/düzenleme/silme
- Session tabanlı güvenlik

### 🛒 E-Ticaret Sistemi
- Ürün katalogu
- Sepet yönetimi
- Sipariş sistemi
- Kategori bazlı ürün filtreleme

### 👨‍💼 Admin Paneli
- Ürün yönetimi
- Kullanıcı yönetimi
- Sipariş takibi
- İstatistikler

## 🛠️ Teknolojiler

- **Backend:** PHP 7.4+
- **Veritabanı:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Framework:** Bootstrap 4
- **Sunucu:** Apache/Nginx

## 📋 Gereksinimler

- PHP 7.4 veya üzeri
- MySQL 5.7 veya üzeri
- Apache/Nginx web sunucusu
- XAMPP (geliştirme için önerilen)

## 🔧 Kurulum

1. **Projeyi klonlayın:**
   ```bash
   git clone https://github.com/kullaniciadi/afetkutusuu.git
   cd afetkutusuu
   ```

2. **Veritabanını kurun:**
   - MySQL'de yeni bir veritabanı oluşturun
   - `db.php` dosyasındaki veritabanı bilgilerini güncelleyin
   - SQL dosyalarını çalıştırın:
     ```sql
     -- Gerekli tablolar otomatik oluşturulacaktır
     ```

3. **Web sunucusunu başlatın:**
   ```bash
   # XAMPP kullanıyorsanız
   php -S localhost:8000
   
   # Veya Apache/Nginx ile
   # Proje klasörünü web root'a kopyalayın
   ```

4. **Uygulamaya erişin:**
   - Tarayıcınızda `http://localhost:8000` adresine gidin

## 📁 Proje Yapısı

```
afetkutusuu/
├── css/                    # Stil dosyaları
├── js/                     # JavaScript dosyaları
├── uploads/                # Yüklenen dosyalar
├── Data/                   # Veri dosyaları
├── fonts/                  # Font dosyaları
├── index.php              # Ana sayfa
├── profil.php             # Kullanıcı profili
├── urunler.php            # Ürün listesi
├── sepet.php              # Sepet sayfası
├── admin-panel.php        # Admin paneli
├── db.php                 # Veritabanı bağlantısı
└── aile_uyeleri_api.php   # Aile üyesi API'si
```

## 🔐 Güvenlik

- Session tabanlı kimlik doğrulama
- SQL injection koruması
- XSS koruması
- Admin yetkisi kontrolü

## 📱 Responsive Tasarım

- Mobil uyumlu arayüz
- Bootstrap 4 grid sistemi
- Tüm cihazlarda uyumlu

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/yeni-ozellik`)
3. Değişikliklerinizi commit edin (`git commit -am 'Yeni özellik eklendi'`)
4. Branch'inizi push edin (`git push origin feature/yeni-ozellik`)
5. Pull Request oluşturun


## 📞 İletişim

- **Geliştirici:** [Nazife Ela Özdemir]
- **E-posta:** [nazifeelao@gmail.com]
- **GitHub:** [github.com/nelaozdmr]


---

⭐ Bu projeyi beğendiyseniz yıldız vermeyi unutmayın!

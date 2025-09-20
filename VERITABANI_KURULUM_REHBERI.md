# VERİTABANI KURULUM REHBERİ

## 1054 Unknown Column Hatalarını Çözmek İçin

### Adım 1: phpMyAdmin'e Giriş
1. Tarayıcınızda `http://localhost/phpmyadmin` adresine gidin
2. Kullanıcı adı: `root` (şifre genellikle boş)

### Adım 2: Veritabanını Seçin
1. Sol menüden `afetkutusu` veritabanını seçin
2. Eğer yoksa, yeni veritabanı oluşturun

### Adım 3: SQL Dosyalarını Çalıştırın
Aşağıdaki dosyaları sırasıyla çalıştırın:

#### 3.1. fix_all_columns.sql
- Bu dosya tüm eksik sütunları ve tabloları oluşturacak
- phpMyAdmin'de "SQL" sekmesine gidin
- `fix_all_columns.sql` dosyasının içeriğini kopyalayıp yapıştırın
- "Git" butonuna tıklayın

#### 3.2. siparis_tablolari.sql (İsteğe bağlı)
- Sipariş tablolarını oluşturmak için
- Aynı şekilde SQL sekmesinde çalıştırın

#### 3.3. aile_uyeleri_tablo.sql (İsteğe bağlı)
- Aile üyeleri tablosunu oluşturmak için

### Adım 4: Tabloları Kontrol Edin
1. `test_tables.php` dosyasını tarayıcıda açın: `http://localhost/proje/afetkutusuu/test_tables.php`
2. Tüm tabloların ve sütunların doğru oluşturulduğunu kontrol edin

### Çözülen Hatalar:
- ✅ admin-panel.php - Ürün ekleme sütun hatası
- ✅ urun-ekle.php - Ürün ekleme sütun hatası
- ✅ kayit-ol.php - Kullanıcı kayıt sütun hatası
- ✅ siparisler.php - Sipariş durumu sütun hatası
- ✅ create_admin.php - Rol sütunu hatası
- ✅ profil.php - Kullanıcı profil sütunları hatası

### Önemli Notlar:
- `fix_all_columns.sql` dosyası tüm eksik sütunları otomatik olarak ekleyecek
- Mevcut verileriniz korunacak
- Eğer tablolar zaten varsa, sadece eksik sütunlar eklenecek

### Test Etme:
Kurulum tamamlandıktan sonra şu sayfaları test edin:
- Admin Panel: `http://localhost/proje/afetkutusuu/admin-panel.php`
- Kayıt Ol: `http://localhost/proje/afetkutusuu/kayit-ol.php`
- Siparişler: `http://localhost/proje/afetkutusuu/siparisler.php`
- Profil: `http://localhost/proje/afetkutusuu/profil.php`
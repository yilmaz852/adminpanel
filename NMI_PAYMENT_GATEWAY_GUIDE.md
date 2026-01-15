# NMI Payment Gateway - Kurulum ve Kullanım Kılavuzu

## 📋 Genel Bakış

WooCommerce için basit ve etkili NMI (Network Merchants Inc.) ödeme gateway entegrasyonu. Bu modül aşağıdaki özellikleri içerir:

- ✅ Kredi kartı ödemeleri
- ✅ İade (refund) işlemleri
- ✅ İşlem logları
- ✅ Test modu desteği
- ✅ Güvenli API iletişimi

## 🚀 Kurulum

### 1. NMI Hesabı Gereksinimleri

NMI ödeme gateway'ini kullanmak için:
1. NMI hesabı açın (https://nmi.com)
2. API erişim bilgilerinizi (Security Key) alın
3. Test modu için test API anahtarınızı hazırlayın

### 2. Gateway Aktivasyonu

1. B2B Admin Panel'e giriş yapın
2. **Settings → Payment Gateways** menüsüne gidin
3. **Enable NMI Gateway** kutucuğunu işaretleyin
4. API bilgilerinizi girin:
   - **API Username (Security Key)**: NMI hesabınızdan aldığınız security key
   - **API Password**: (Opsiyonel) Eğer hesabınız gerektiriyorsa
5. **Test Mode** kutucuğunu test için işaretleyin
6. **Save Settings** butonuna tıklayın

### 3. Gateway Yapılandırması

#### Temel Ayarlar

- **Title**: Müşterilere gösterilecek ödeme yöntemi başlığı
  - Örnek: "Kredi Kartı (NMI)"
  
- **Description**: Ödeme yöntemi açıklaması
  - Örnek: "Kredi kartınızla güvenli ödeme yapın."

#### API Ayarları

- **API Username (Security Key)**: NMI hesabınızdan aldığınız benzersiz security key
- **API Password**: Bazı hesaplar için gerekli opsiyonel şifre

#### Test Modu

Test modunu aktif ettiğinizde:
- Gerçek para transferi olmaz
- Test kartları kullanabilirsiniz
- API istekleri test sunucusuna gider

**Test Kartları:**
```
Kart Numarası: 4111111111111111
CVV: 123
Expiry: Herhangi bir gelecek tarih (örn: 12/25)
```

## 💳 Ödeme İşlemi

### Müşteri Perspektifi

1. Alışveriş sepetine ürün eklenir
2. Checkout sayfasında "Credit Card (NMI)" seçeneği görünür
3. Kart bilgileri girilir:
   - Kart numarası
   - Son kullanma tarihi (MM/YY)
   - CVV kodu
4. "Place Order" butonuna tıklanır
5. Ödeme işlenir ve onaylanır

### Admin Perspektifi

Ödeme tamamlandığında:
- Sipariş otomatik olarak "Processing" durumuna geçer
- Transaction ID sipariş notlarına eklenir
- İşlem logu `wp_nmi_transaction_logs` tablosuna kaydedilir

## 🔄 İade (Refund) İşlemi

### WooCommerce Üzerinden İade

1. **WooCommerce → Orders** menüsüne gidin
2. İade yapmak istediğiniz siparişi açın
3. **Refund** butonuna tıklayın
4. İade miktarını ve nedenini girin
5. **Refund via NMI Gateway** seçeneğini seçin
6. İade işlemini onaylayın

### İşlem Süreci

- NMI API'ye refund isteği gönderilir
- Başarılı ise sipariş notlarına eklenir
- İade transaction ID kaydedilir
- Transaction log tablosuna yazılır

## 📊 Transaction Logları

### Log Görüntüleme

1. **Settings → Payment Gateways** sayfasına gidin
2. Sayfanın altında "Transaction Logs" bölümünü görürsünüz
3. Son 50 işlem gösterilir

### Log Bilgileri

Her log kaydı şunları içerir:
- **Order ID**: Sipariş numarası (tıklanabilir link)
- **Type**: İşlem tipi (Payment veya Refund)
- **Transaction ID**: NMI transaction ID
- **Amount**: İşlem tutarı
- **Status**: Durum (Completed veya Failed)
- **Date**: İşlem tarihi
- **Actions**: Detayları görüntüleme butonu

### Detaylı Log Görüntüleme

"View" butonuna tıklayarak:
- Tüm işlem detaylarını
- Ham API yanıtını
- Hata mesajlarını görebilirsiniz

## 🔐 Güvenlik

### API İletişimi

- Tüm API istekleri HTTPS üzerinden yapılır
- Test modunda SSL doğrulaması devre dışı bırakılır (test kolaylığı için)
- Prod modda SSL zorunludur

### Kart Bilgileri

- Kart bilgileri sunucuda saklanmaz
- Direkt NMI API'ye gönderilir
- PCI-DSS compliance sorumluluğu NMI'dadır

### Kullanıcı Erişimi

- Settings sayfası sadece admin kullanıcılara açıktır
- `manage_options` capability gereklidir

## 🛠️ Teknik Detaylar

### Database Şeması

```sql
CREATE TABLE wp_nmi_transaction_logs (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    order_id bigint(20) NOT NULL,
    transaction_type varchar(20) NOT NULL,
    transaction_id varchar(100) DEFAULT '',
    amount decimal(10,2) NOT NULL,
    status varchar(20) NOT NULL,
    raw_response text,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY order_id (order_id)
);
```

### NMI API Endpoints

**Production:**
```
https://secure.nmi.com/api/transact.php
```

**Test Mode:**
```
https://secure.nmi.com/api/transact.php
```

### API Request Parametreleri

**Payment (Sale):**
```php
'security_key' => 'your_key',
'type' => 'sale',
'ccnumber' => '4111111111111111',
'ccexp' => '1225', // MMYY
'cvv' => '123',
'amount' => '10.00',
'firstname' => 'John',
'lastname' => 'Doe',
'address1' => '123 Main St',
'city' => 'New York',
'state' => 'NY',
'zip' => '10001',
'country' => 'US',
'email' => 'john@example.com',
'orderid' => '12345'
```

**Refund:**
```php
'security_key' => 'your_key',
'type' => 'refund',
'transactionid' => 'original_transaction_id',
'amount' => '10.00'
```

### API Response

**Success:**
```
response=1&responsetext=SUCCESS&transactionid=1234567890
```

**Failure:**
```
response=2&responsetext=DECLINE&transactionid=
```

## 🐛 Hata Ayıklama

### Ödeme Başarısız Oluyorsa

1. **API Credentials Kontrolü**
   - Security Key doğru mu?
   - Test/Production modu doğru mu?

2. **Log Kontrolü**
   - Transaction Logs'ta raw response'u inceleyin
   - NMI'dan gelen hata mesajını okuyun

3. **Yaygın Hatalar**
   - `INVALID SECURITY KEY`: API key yanlış
   - `DECLINE`: Kart reddedildi (test kartı kullanmayı deneyin)
   - `INVALID CARD NUMBER`: Kart numarası geçersiz

### İade Başarısız Oluyorsa

1. **Transaction ID Kontrolü**
   - Sipariş için geçerli transaction ID var mı?
   - Order notes'ta transaction ID'yi kontrol edin

2. **Zaman Aşımı**
   - Bazı işlemler için 24-48 saat bekleme süresi olabilir

## 📞 Destek

### NMI Desteği
- Website: https://nmi.com/support
- Phone: NMI müşteri hizmetleri numarası

### Teknik Sorunlar
- Transaction logs'u inceleyin
- WooCommerce → Status → Logs kısmına bakın
- Raw API response'u kontrol edin

## 📈 İyileştirme Önerileri

Gelecekte eklenebilecek özellikler:

1. **Tokenization**: Kart bilgilerini tokenize ederek sakla
2. **Recurring Payments**: Abonelik ödemeleri
3. **3D Secure**: Ekstra güvenlik katmanı
4. **Partial Refunds**: Kısmi iade desteği (şu an desteklenmektedir)
5. **Webhooks**: NMI'dan otomatik bildirimler
6. **Multi-Currency**: Çoklu para birimi desteği

## 📝 Versiyon Notları

### v1.0 (2026-01-15)
- ✅ İlk sürüm yayınlandı
- ✅ Temel ödeme işleme
- ✅ İade desteği
- ✅ Transaction logging
- ✅ Test modu
- ✅ Admin settings sayfası

## 🎯 Sonuç

Bu NMI Payment Gateway entegrasyonu, basit ve etkili bir ödeme çözümü sunar. Karmaşık özellikler yerine, temel gereksinimlere odaklanarak:

- ✅ Ödeme alma
- ✅ İade yapma
- ✅ Log görüntüleme

işlevlerini başarıyla yerine getirir.

---

**Not:** Bu gateway'i production ortamında kullanmadan önce:
1. Test modda kapsamlı testler yapın
2. NMI hesabınızın production için aktif olduğundan emin olun
3. SSL sertifikası yüklü olduğunu doğrulayın
4. PCI-DSS uyumluluğu için NMI dokümantasyonunu inceleyin

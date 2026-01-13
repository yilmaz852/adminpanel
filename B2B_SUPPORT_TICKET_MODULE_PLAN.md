# B2B Support Ticket Modülü - Detaylı Plan

## 📋 Modül Özellikleri

### 🎯 Temel Özellikler:

**Müşteri Tarafı:**
1. Ticket oluşturma (sipariş ile veya siparişsiz)
2. Kendi ticketlarını görüntüleme
3. Ticket detayına mesaj ekleme
4. Dosya ekleme (attachment)
5. Ticket durumu takibi

**Personel/Admin Tarafı:**
1. Tüm ticketları görüntüleme
2. Filtreleme (durum, öncelik, kategori, müşteri)
3. Ticket'a atanma/atama
4. Ticket yanıtlama (public + internal notes)
5. Durum güncelleme
6. Sipariş bilgilerini otomatik gösterme
7. İstatistikler (açık/kapalı/ortalama çözüm süresi)

### 📊 Veritabanı Tabloları:

**1. wp_b2b_support_tickets:**
- ticket_id (PRIMARY KEY, AUTO_INCREMENT)
- ticket_number (unique, örn: #TK-00001)
- customer_id (user_id)
- assigned_agent_id (user_id, NULL if unassigned)
- order_id (NULL if not order-related)
- subject (VARCHAR 255)
- category (enum: order, product, delivery, billing, general)
- priority (enum: low, normal, high, urgent)
- status (enum: new, open, pending, resolved, closed)
- created_at
- updated_at
- resolved_at (NULL until resolved)
- closed_at (NULL until closed)

**2. wp_b2b_support_replies:**
- reply_id (PRIMARY KEY, AUTO_INCREMENT)
- ticket_id (FOREIGN KEY)
- user_id (who wrote the reply)
- message (TEXT)
- is_internal (BOOLEAN - internal note or public)
- attachment_url (VARCHAR 255, NULL)
- created_at

**3. wp_b2b_support_attachments:**
- attachment_id (PRIMARY KEY, AUTO_INCREMENT)
- ticket_id
- reply_id (NULL if attached to ticket, not reply)
- file_name
- file_path
- file_size
- file_type
- uploaded_by (user_id)
- uploaded_at

### 🎨 UI Komponenleri:

**Admin Panel Menü:**
- "Support" menü öğesi (icon: life-ring)
  - Submenu: Tickets, Statistics

**Tickets Liste Sayfası:**
- Filtreler: Durum, Öncelik, Kategori, Atanan kişi
- Arama: Ticket numarası, müşteri adı, konu
- Tablo: Ticket #, Müşteri, Konu, Kategori, Öncelik, Durum, Atanan, Son Güncelleme
- Pagination

**Ticket Detay Sayfası:**
- Üst bölüm: Ticket bilgileri (durum, öncelik, kategori, müşteri, sipariş)
- Sipariş bilgileri (eğer varsa): Order #, Ürünler, Tutar, Tarih
- Mesaj geçmişi (timeline)
- Yanıt formu (public/internal toggle)
- Dosya yükleme
- Durum değiştirme
- Atama değiştirme

**Müşteri Paneli:**
- "Destek" menü öğesi
- Ticket listesi (sadece kendi ticketları)
- Yeni ticket oluşturma butonu
- Ticket detay (mesaj gönderme, dosya ekleme)

### 🔧 İmplementasyon Adımları:

1. ✅ Veritabanı tablolarını oluştur
2. ✅ URL rewrite rules ekle
3. ✅ AJAX handler'ları ekle
4. ✅ Admin ticket listesi sayfası
5. ✅ Admin ticket detay sayfası
6. ✅ Müşteri ticket listesi
7. ✅ Müşteri ticket oluşturma
8. ✅ Email bildirimleri
9. ✅ File upload sistemi
10. ✅ İstatistikler sayfası

### 📧 Email Bildirimleri:

**Müşteriye:**
- Ticket oluşturuldu
- Ticket'a yeni yanıt geldi
- Ticket durumu değişti (çözüldü/kapatıldı)

**Personele:**
- Yeni ticket oluşturuldu
- Müşteriden yanıt geldi
- Ticket'a atandın

### 🔐 Güvenlik:

- Nonce verification (wp_nonce_field)
- Capability checks (manage_woocommerce for admin)
- Dosya upload güvenliği (allowed file types, size limit)
- SQL injection prevention (prepared statements)
- XSS protection (esc_html, esc_attr)
- Müşteri sadece kendi ticketlarını görebilir

### 📈 Performans:

- Index'ler (ticket_number, customer_id, status, created_at)
- Pagination (20 ticket/sayfa)
- Lazy loading for attachments
- Caching (ticket count, statistics)

## 🚀 Başlangıç Kodu

Tüm özellikler adminpanel.php dosyasına eklenecek.

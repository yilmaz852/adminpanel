# Mesajlaşma ve Notlar Sistemi - Kullanım Kılavuzu

## Genel Bakış
B2B Admin Paneline, ekip iletişimi için grup mesajlaşma ve önemli notları paylaşma sistemi eklenmiştir.

## Özellikler

### 1. Mesajlaşma Grupları Yönetimi
**URL:** `/b2b-panel/messaging/groups`

**Yetenekler:**
- Özel isimlerle mesajlaşma grupları oluşturma
- Gruplara birden fazla kullanıcı atama
- Mevcut grupları düzenleme (isim veya üyeleri değiştirme)
- Grupları silme (tüm mesajlar da silinir)
- Grup başına üye sayısını görüntüleme

**Nasıl Kullanılır:**
1. Kenar çubuğunda Messaging'e gidin
2. "Manage Groups" butonuna tıklayın
3. Grup oluşturmak için "New Group" tıklayın
4. Grup adını girin ve üyeleri seçin
5. "Save Group" tıklayın

### 2. Mesajlaşma Arayüzü
**URL:** `/b2b-panel/messaging`

**Yetenekler:**
- Gerçek zamanlı grup mesajlaşma (her 5 saniyede otomatik yenilenir)
- Atandığınız tüm grupları görüntüleme
- Grup üyelerine mesaj gönderme
- Mesaj geçmişini görme
- Kendi mesajlarınız ve diğerlerinin mesajları arasında görsel ayrım
- Tüm mesajlarda zaman damgası
- Kenar çubuğunda okunmamış mesaj sayacı rozeti

**Nasıl Kullanılır:**
1. Kenar çubuğunda Messaging'e gidin
2. Sol panelden bir grup seçin
3. Metin alanına mesajınızı yazın
4. "Send" tıklayın veya Enter tuşuna basın
5. Mesajlar anında görünür ve otomatik olarak yenilenir

**Mesaj Özellikleri:**
- Sizin mesajlarınız sağda mavi renkte görünür
- Diğerlerinin mesajları solda beyaz renkte görünür
- Her mesaj gönderen adını ve zaman damgasını gösterir
- Son mesaja otomatik kaydırma
- Mesajlaşma sayfasını açtığınızda okunmamış rozeti temizlenir

### 3. Notlar Sistemi
**URL:** `/b2b-panel/notes`

**Yetenekler (Sadece Admin):**
- Başlık ve içerikle not oluşturma
- Görünürlük ayarlama:
  - **Genel**: Tüm kullanıcılara görünür
  - **Gruba özel**: Sadece belirli mesajlaşma grubu üyelerine görünür
- Mevcut notları düzenleme
- Notları silme
- Yazar ve oluşturma tarihini görüntüleme

**Nasıl Kullanılır:**
1. Kenar çubuğunda Notes'a gidin
2. "New Note" butonuna tıklayın (sadece admin)
3. Başlık ve içerik girin
4. Görünürlük seçin:
   - Tüm kullanıcılar için "General (Everyone)" seçin
   - Görünürlüğü sınırlamak için belirli bir grup seçin
5. "Save Note" tıklayın

**Not Görünümü:**
- Genel notlar sarı/amber arka plana sahiptir
- Gruba özel notlar mavi arka plana sahiptir
- Yazar, görünürlük ve oluşturma tarihini gösterir
- Izgara düzeni ekran boyutuna uyarlanır

### 4. Dashboard Entegrasyonu

**Notlar Widget'ı:**
- Kullanıcıyla ilgili en son 3 notu görüntüler
- Tüm notları görüntülemek için hızlı bağlantı
- Dashboard'un alt kısmında görünür
- Sadece kullanıcının görme izni olan notları gösterir

## İzinler

### Admin Kullanıcılar
- Mesajlaşma gruplarını oluşturabilir/düzenleyebilir/silebilir
- Tüm mesajlaşma gruplarını görebilir
- Notları oluşturabilir/düzenleyebilir/silebilir
- Not görünürlüğünü ayarlayabilir

### Normal Kullanıcılar
- Sadece atandıkları mesajlaşma gruplarını görebilir
- Gruplarında mesaj gönderebilir/alabilir
- Kendilerine görünür notları görüntüleyebilir (genel veya grupları)
- Not oluşturamazlar

## Teknik Detaylar

### Veri Depolama
- Mesajlaşma grupları: `b2b_messaging_groups` seçeneği
- Grup başına mesajlar: `b2b_messages_{group_id}` seçeneği
- Notlar: `b2b_notes` seçeneği
- Okunmamış sayısı: `b2b_unread_messages` kullanıcı meta'sı

### Mesaj Limitleri
- Grup başına 500'e kadar mesaj saklanır (en eskiler otomatik silinir)
- Mesajlar her 5 saniyede yenilenir
- Dosya eki yok (sadece metin)

### Güvenlik
- Tüm AJAX istekleri kullanıcı kimlik doğrulamasını kontrol eder
- Kullanıcılar sadece atandıkları gruplara erişebilir
- Sadece adminler grupları ve notları yönetebilir
- Tüm girdiler temizlenir

## URL Referansı

| Sayfa | URL | Erişim |
|-------|-----|--------|
| Mesajlaşma Grupları | `/b2b-panel/messaging/groups` | Sadece Admin |
| Mesajlaşma | `/b2b-panel/messaging` | Tüm kullanıcılar (kendi grupları) |
| Notlar | `/b2b-panel/notes` | Tüm kullanıcılar (görünürlüğe göre) |

## İpuçları

1. **Departman veya ekibe göre gruplar oluşturun** (örn: "Satış Ekibi", "Depo Personeli")
2. **Şirket çapında duyurular için genel notları kullanın**
3. **Ekibe özel bilgiler için grup notlarını kullanın**
4. **Yeni mesajlardan haberdar olmak için okunmamış rozetini kontrol edin**
5. **Mesaj geçmişini temiz tutun** - 500'den sonra eski mesajlar otomatik silinir

## Kullanım Senaryoları

### Örnek 1: Depo Ekibi İletişimi
1. Admin "Depo Ekibi" adında bir grup oluşturur
2. Depo personelini gruba ekler
3. Ekip üyeleri günlük işler hakkında mesajlaşır
4. Admin depo prosedürleri için gruba özel not bırakır

### Örnek 2: Genel Duyuru
1. Admin yeni bir not oluşturur
2. "General (Everyone)" görünürlüğünü seçer
3. Tatil günleri veya önemli duyuruları yazar
4. Tüm kullanıcılar dashboard'da ve Notes sayfasında görür

### Örnek 3: Satış Toplantısı
1. Satış ekibi kendi grubunda mesajlaşır
2. Toplantı saati ve yeri paylaşılır
3. Admin toplantı gündemi için gruba özel not bırakır
4. Sadece satış ekibi bu bilgileri görür

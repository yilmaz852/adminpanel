# NMI Payment Gateway - Quick Start Guide

## 🚀 5 Dakikada Kurulum

### 1. Menüye Eriş
```
Admin Panel → Settings → Payment Gateways
```
veya direkt URL:
```
https://yourdomain.com/b2b-panel/settings/payments
```

### 2. Gateway'i Aktifleştir
- [x] **Enable NMI Gateway** kutucuğunu işaretle
- [x] **Test Mode** kutucuğunu işaretle (test için)

### 3. API Bilgilerini Gir
```
API Username (Security Key): [NMI hesabınızdan alın]
API Password: [opsiyonel]
```

### 4. Save Settings

### 5. Test Et
Test kartı ile checkout yapın:
```
Kart No: 4111 1111 1111 1111
CVV: 123
Expiry: 12/25 (herhangi bir gelecek tarih)
```

---

## 📋 Özellikler

✅ **Ödeme Alma**
- Kredi kartı ödemeleri
- Güvenli işlem
- Otomatik sipariş tamamlama

✅ **İade Yapma**
- WooCommerce admin panelinden
- Tam veya kısmi iade
- Otomatik loglama

✅ **Log Görme**
- Settings sayfasında transaction logs
- Son 50 işlem
- Detay görüntüleme

---

## 🔐 Güvenlik

✅ Kart numarası validasyonu  
✅ CVV kontrolü  
✅ SKT format ve tarih kontrolü  
✅ SSL zorunlu  
✅ CSRF koruması  
✅ Güvenli database işlemleri

---

## 📞 Destek

**Dokümantasyon:**
- `NMI_PAYMENT_GATEWAY_GUIDE.md` - Detaylı Türkçe kılavuz
- `NMI_PAYMENT_GATEWAY_IMPLEMENTATION_SUMMARY.md` - Teknik detaylar

**NMI Destek:**
- https://nmi.com/support

---

## ⚠️ Production Öncesi

1. ✅ Test modda tüm işlemleri test et
2. ✅ NMI production API key al
3. ✅ Test mode'u kapat
4. ✅ Küçük bir gerçek ödeme test et
5. ✅ İade işlemini test et

---

**Sürüm:** v1.0  
**Tarih:** 2026-01-15  
**Durum:** ✅ Production Ready

# Project Analysis: Order Edit Implementation

## Mevcut Durum Analizi (Current Status Analysis)

### 1. Assembly Sistemi (Assembly System)

**Ürün Seviyesi (Product Level):**
- ✅ Her ürünün kendi assembly ayarı var: `_assembly_enabled` (yes/no)
- ✅ Her ürünün kendi assembly fiyatı: `_assembly_price` (individual price per product)
- ✅ Assembly tax ayarı: `_assembly_tax` (included/excluded)
- **Konum:** Product Edit Page (line ~7546)

**Sales Agent Sipariş Oluşturma (Sales Agent Order Creation):**
- ✅ Ürün bazında assembly seçimi yapılıyor (line ~12760)
- ✅ Her ürün için farklı assembly ücreti uygulanabiliyor (line ~12830)
- ✅ Toplam assembly fee hesaplanıyor ve order'a ekleniyor
- **Konum:** Sales Agent Order Creation (line ~12808-12840)

**Order Edit Page - Mevcut Problem:**
- ❌ Sabit $50 assembly fee kullanılıyor (B2B_ASSEMBLY_FEE_AMOUNT constant)
- ❌ Ürünün kendi assembly fiyatı (_assembly_price) kullanılmıyor
- ❌ Toplu assembly uygulama yok
- ❌ Assembly tax ayarı dikkate alınmıyor
- **Konum:** Order Edit Page (line ~5248-5375)

### 2. Tax Exempt Sistemi (Tax Exemption System)

**Müşteri Seviyesi (Customer Level):**
- ✅ `b2b_tax_exempt` meta: Müşteri tax exempt mi? (1/0)
- ✅ `b2b_tax_id`: Tax ID numarası
- ✅ `b2b_tax_certificate`: Certificate bilgisi
- ✅ `b2b_tax_notes`: Notlar
- ✅ WooCommerce tax calculation'a hook'lanmış (line ~104-142)
- **Konum:** Customer Edit (line ~8372), Tax Hooks (line ~104-142)

**Order Edit Page - Eksik:**
- ❌ Sipariş sahibi müşterinin tax exempt durumu gösterilmiyor
- ❌ Tax exempt durumu değiştirilemez (sadece view olması yeterli)
- **Konum:** N/A - Eklenmesi gerekiyor

### 3. Refund Sistemi (Refund System)

**NMI Gateway Entegrasyonu:**
- ✅ `WC_NMI_Gateway` sınıfı var (line ~14677)
- ✅ `process_refund()` metodu mevcut (line ~15001)
- ✅ Order notes'a kaydediliyor
- **Konum:** NMI Gateway Class (line ~14668-15050)

**Order Edit Page - Mevcut Durum:**
- ✅ Refund bölümü eklendi (line ~5690-5750)
- ✅ Sadece NMI ödemeler için görünüyor
- ✅ Server-side validation var
- ✅ Max refund amount kontrolü yapılıyor
- **Durum:** TAMAMLANDI ✅

### 4. Recalculate (Yeniden Hesaplama)

**Mevcut Durum:**
- ✅ Form submit edildiğinde `$order->calculate_totals()` çağrılıyor (line ~5424)
- ✅ Otomatik hesaplama yapılıyor
- ❌ "Recalculate" butonu yok (kullanıcı save yapıyor, otomatik hesaplanıyor)
- **Öneri:** Save butonu yanında ayrı bir "Recalculate Totals" butonu eklenebilir

## İyileştirmeler (Improvements Needed)

### Priority 1: Assembly System Düzeltmesi

**Problem:**
Current implementation uses fixed $50 assembly fee. Should use product-specific assembly prices.

**Çözüm:**
1. Order item'larda ürün ID'sini al
2. Her ürün için `_assembly_price` meta'sını oku
3. Assembly checkbox işaretliyse, o ürünün assembly price'ını kullan
4. Tax ayarına göre tax ekle/ekleme

**Kod Değişikliği:**
```php
// Mevcut (Yanlış):
$assembly_fee_total += B2B_ASSEMBLY_FEE_AMOUNT * $item->get_quantity();

// Olması Gereken (Doğru):
$product_id = $item->get_product_id();
$assembly_price = floatval(get_post_meta($product_id, '_assembly_price', true));
$assembly_tax_included = get_post_meta($product_id, '_assembly_tax', true) === 'yes';

if ($assembly_price > 0) {
    $item_assembly_total = $assembly_price * $item->get_quantity();
    if ($assembly_tax_included) {
        // Tax hesaplama (müşteri tax exempt değilse)
        $customer_id = $order->get_customer_id();
        $is_tax_exempt = get_user_meta($customer_id, 'b2b_tax_exempt', true) == 1;
        if (!$is_tax_exempt) {
            // Tax rate'i al ve ekle
            $tax_rate = 0.08; // veya dinamik al
            $item_assembly_total *= (1 + $tax_rate);
        }
    }
    $assembly_fee_total += $item_assembly_total;
}
```

### Priority 2: Tax Exempt Display

**Eklenmesi Gereken:**
Payment Info bölümünün altına:
```php
<!-- Tax Exemption Status -->
<?php 
$customer_id = $order->get_customer_id();
$is_tax_exempt = get_user_meta($customer_id, 'b2b_tax_exempt', true) == 1;
if ($customer_id > 0):
?>
<div style="background:white;border:1px solid #e5e7eb;border-radius:8px;padding:25px;margin-bottom:25px">
    <h3>Tax Exemption Status</h3>
    <div style="padding:12px;background:<?= $is_tax_exempt ? '#f0fdf4' : '#fef3c7' ?>;border-radius:6px">
        <i class="fa-solid fa-<?= $is_tax_exempt ? 'check-circle' : 'info-circle' ?>"></i>
        Customer is <?= $is_tax_exempt ? '<strong style="color:#10b981">TAX EXEMPT</strong>' : '<strong>NOT tax exempt</strong>' ?>
    </div>
</div>
<?php endif; ?>
```

### Priority 3: Bulk Assembly Toggle

**Özellik:**
"Apply Assembly to All" butonu ekle (Sales Agent'taki gibi)

**UI:**
Items tablosunun üstüne:
```html
<button type="button" onclick="toggleAllAssembly()" class="button secondary">
    <i class="fa-solid fa-wrench"></i> Toggle Assembly for All
</button>

<script>
function toggleAllAssembly() {
    const checkboxes = document.querySelectorAll('input[name*="[assembly]"]');
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
}
</script>
```

### Priority 4: Recalculate Button

**Özellik:**
Separate "Recalculate Totals" button

**UI:**
Save butonunun yanına:
```html
<button type="submit" name="recalculate_only" class="button secondary" style="...">
    <i class="fa-solid fa-calculator"></i>
    Recalculate Totals
</button>
```

**Backend:**
```php
if (isset($_POST['recalculate_only'])) {
    $order->calculate_totals();
    $order->save();
    wp_redirect(home_url('/b2b-panel/orders/edit?id=' . $order_id . '&recalculated=1'));
    exit;
}
```

## Özetleme (Summary)

### ✅ Tamamlanan Özellikler:
- Order edit full-page interface
- Editable prices, quantities
- Custom fees
- Shipping & tax override
- Order notes history
- Payment info display
- **NMI Refund processing** ✅
- Basic assembly checkbox (but with wrong price)

### ❌ Düzeltilmesi Gerekenler:
1. **Assembly system:** Ürün bazlı fiyat kullanımı
2. **Tax calculation:** Assembly için tax hesaplama
3. **Tax exempt:** Müşteri tax exempt durumu gösterilmesi
4. **Bulk actions:** Toplu assembly toggle
5. **Recalculate button:** Ayrı hesaplama butonu

### 📋 Yapılacaklar Listesi (TODO):

**Immediate (Critical):**
- [ ] Fix assembly to use product-specific prices (`_assembly_price`)
- [ ] Add tax calculation for assembly (if `_assembly_tax` = yes and customer not exempt)
- [ ] Display customer tax exempt status

**Important:**
- [ ] Add "Toggle Assembly for All" button
- [ ] Add separate "Recalculate Totals" button
- [ ] Show assembly price in table header tooltip (per product, not fixed $50)

**Nice to Have:**
- [ ] Show product's assembly price in items table
- [ ] Highlight which products have assembly available
- [ ] Assembly cost breakdown in totals section

## Bağlantılar (Connections)

**Assembly Flow:**
```
Product (_assembly_enabled, _assembly_price, _assembly_tax)
    ↓
Order Item (_assembly_enabled meta)
    ↓
Order (Assembly Fee - calculated from product prices)
    ↓
Tax Calculation (if assembly_tax=yes AND customer not tax exempt)
```

**Tax Exempt Flow:**
```
Customer (b2b_tax_exempt meta)
    ↓
WooCommerce Hooks (woocommerce_customer_is_vat_exempt)
    ↓
Order Tax Calculation
    ↓
Assembly Tax (if enabled for product)
```

**Current vs Expected:**
- **Current:** Fixed $50 assembly fee for all products
- **Expected:** Each product's own assembly price + tax calculation
- **Reference:** Sales Agent Order Creation (line 12808-12840) - uses correct method!

---

**Son Güncelleme:** 2026-01-21  
**Durum:** Analysis Complete - Ready for Implementation

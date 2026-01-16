# Envanter Planlama Modülü (Inventory Planning Module)

## 📋 Genel Bakış (Overview)

Bu dokümant, B2B Admin Panel için kapsamlı bir envanter planlama sistemi tasarımını içermektedir. Mevcut stok yönetimi özelliklerinin üzerine inşa edilecek bu modül, işletmelerin stok seviyelerini optimize etmesine ve envanter yönetimini otomatikleştirmesine yardımcı olacaktır.

**Mevcut Özellikler (Current Features):**
- ✅ Quick Edit Stock - Toplu stok güncelleme
- ✅ Stock Status Filters - Stok durumu filtreleme
- ✅ CSV Import/Export - Toplu veri aktarımı
- ✅ Stock Logging - Stok değişiklik kayıtları

**Eklenecek Özellikler (Features to Add):**
- 🔄 Otomatik Düşük Stok Uyarıları
- 📊 Stok Tahmin Motoru
- 🔔 Gerçek Zamanlı Bildirimler
- 📈 Envanter Raporlama Dashboard'u
- 🎯 Minimum/Maximum Stok Seviyeleri
- 📦 Tedarikçi Yönetimi
- 🔄 Otomatik Sipariş Önerileri

---

## 🎯 Modül 1: Düşük Stok Yönetimi (Low Stock Management)

### 1.1 Dashboard Widget - Düşük Stok

**Özellikler:**
- Top 10 düşük stok ürün listesi
- Gerçek zamanlı güncelleme
- Hızlı stok ekleme özelliği
- Kritik seviye göstergeleri

**UI Tasarımı:**
```
┌────────────────────────────────────────┐
│  ⚠️  Düşük Stok Uyarıları              │
├────────────────────────────────────────┤
│  🔴 Kritik (< 5)                       │
│  • Product A - 2 adet kaldı   [+Stock] │
│  • Product B - 3 adet kaldı   [+Stock] │
│                                         │
│  🟡 Uyarı (< 20)                       │
│  • Product C - 15 adet        [+Stock] │
│  • Product D - 18 adet        [+Stock] │
│                                         │
│  [Tümünü Gör] [Ayarlar]                │
└────────────────────────────────────────┘
```

**Implementasyon:**

```php
// Database: Minimum stok seviyeleri için yeni tablo
CREATE TABLE IF NOT EXISTS {$wpdb->prefix}b2b_inventory_settings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    product_id BIGINT NOT NULL,
    min_stock INT DEFAULT 10,
    max_stock INT DEFAULT 100,
    reorder_point INT DEFAULT 20,
    reorder_quantity INT DEFAULT 50,
    supplier_id BIGINT,
    lead_time_days INT DEFAULT 7,
    safety_stock INT DEFAULT 5,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY product_id (product_id),
    INDEX idx_min_stock (min_stock),
    INDEX idx_product (product_id)
);

// Function: Düşük stok listesi
function b2b_get_low_stock_products($limit = 10) {
    global $wpdb;
    
    $query = "
        SELECT 
            p.ID,
            p.post_title as name,
            pm_stock.meta_value as current_stock,
            inv.min_stock,
            inv.reorder_point,
            CASE 
                WHEN CAST(pm_stock.meta_value AS SIGNED) < 5 THEN 'critical'
                WHEN CAST(pm_stock.meta_value AS SIGNED) < inv.min_stock THEN 'warning'
                ELSE 'normal'
            END as status
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id 
            AND pm_stock.meta_key = '_stock'
        LEFT JOIN {$wpdb->prefix}b2b_inventory_settings inv ON p.ID = inv.product_id
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND pm_stock.meta_value IS NOT NULL
        AND CAST(pm_stock.meta_value AS SIGNED) < inv.min_stock
        ORDER BY CAST(pm_stock.meta_value AS SIGNED) ASC
        LIMIT %d
    ";
    
    return $wpdb->get_results($wpdb->prepare($query, $limit));
}

// AJAX Handler: Quick add stock
add_action('wp_ajax_b2b_quick_add_stock', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $product_id = intval($_POST['product_id']);
    $add_quantity = intval($_POST['quantity']);
    
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Product not found');
    }
    
    $current_stock = $product->get_stock_quantity();
    $new_stock = $current_stock + $add_quantity;
    
    $product->set_stock_quantity($new_stock);
    $product->save();
    
    // Log the change
    b2b_adm_add_log($product_id, 'stock', $current_stock, $new_stock, 'Quick Add Stock from Dashboard');
    
    wp_send_json_success([
        'new_stock' => $new_stock,
        'product_id' => $product_id
    ]);
});
```

### 1.2 Bildirim Sistemi (Notification System)

**Email Bildirimleri:**
- Kritik stok seviyesi (< 5)
- Düşük stok uyarısı (< minimum)
- Yeniden sipariş zamanı (reorder point)

**Implementasyon:**

```php
// Cron job: Her 6 saatte bir kontrol
add_action('b2b_check_low_stock', 'b2b_send_low_stock_notifications');

function b2b_send_low_stock_notifications() {
    $low_stock_products = b2b_get_low_stock_products(999);
    
    if (empty($low_stock_products)) {
        return;
    }
    
    $critical = array_filter($low_stock_products, fn($p) => $p->status === 'critical');
    $warning = array_filter($low_stock_products, fn($p) => $p->status === 'warning');
    
    if (!empty($critical) || !empty($warning)) {
        $admin_email = get_option('admin_email');
        $subject = '⚠️ Düşük Stok Uyarısı - ' . count($critical) . ' Kritik';
        
        $message = '<h2>Envanter Uyarısı</h2>';
        
        if (!empty($critical)) {
            $message .= '<h3 style="color:red;">🔴 Kritik Seviye (' . count($critical) . ' ürün)</h3>';
            $message .= '<ul>';
            foreach ($critical as $product) {
                $message .= sprintf(
                    '<li><strong>%s</strong> - %d adet kaldı (Min: %d)</li>',
                    $product->name,
                    $product->current_stock,
                    $product->min_stock
                );
            }
            $message .= '</ul>';
        }
        
        if (!empty($warning)) {
            $message .= '<h3 style="color:orange;">🟡 Uyarı Seviyesi (' . count($warning) . ' ürün)</h3>';
            $message .= '<ul>';
            foreach (array_slice($warning, 0, 10) as $product) {
                $message .= sprintf(
                    '<li><strong>%s</strong> - %d adet (Min: %d)</li>',
                    $product->name,
                    $product->current_stock,
                    $product->min_stock
                );
            }
            $message .= '</ul>';
        }
        
        wp_mail($admin_email, $subject, $message, ['Content-Type: text/html; charset=UTF-8']);
    }
}

// Schedule the cron
if (!wp_next_scheduled('b2b_check_low_stock')) {
    wp_schedule_event(time(), 'sixhourly', 'b2b_check_low_stock');
}
```

---

## 🎯 Modül 2: Stok Tahmin Motoru (Stock Forecasting Engine)

### 2.1 Satış Bazlı Tahmin

**Algoritma:**
- Son 30 günlük satış verileri
- Ortalama günlük satış hesaplama
- Mevcut stok / günlük satış = tahmini gün
- Yeniden sipariş önerisi

**Implementasyon:**

```php
// Function: Stok tahmin analizi
function b2b_forecast_stock($product_id, $days_ahead = 30) {
    global $wpdb;
    
    // Son 30 günlük satış verisi
    $sales_data = $wpdb->get_var($wpdb->prepare("
        SELECT SUM(meta_value) as total_sold
        FROM {$wpdb->prefix}woocommerce_order_items oi
        JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id
        JOIN {$wpdb->posts} o ON oi.order_id = o.ID
        WHERE oim.meta_key = '_product_id'
        AND oim.meta_value = %d
        AND o.post_status IN ('wc-completed', 'wc-processing')
        AND o.post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ", $product_id));
    
    $total_sold = intval($sales_data);
    $daily_avg = $total_sold / 30;
    
    $product = wc_get_product($product_id);
    $current_stock = $product->get_stock_quantity();
    
    $inventory_settings = $wpdb->get_row($wpdb->prepare("
        SELECT * FROM {$wpdb->prefix}b2b_inventory_settings 
        WHERE product_id = %d
    ", $product_id));
    
    $lead_time = $inventory_settings->lead_time_days ?? 7;
    $safety_stock = $inventory_settings->safety_stock ?? 5;
    
    // Tahmini tükenme günü
    $days_until_stockout = $daily_avg > 0 ? $current_stock / $daily_avg : 999;
    
    // Yeniden sipariş gerekli mi?
    $needs_reorder = $days_until_stockout < ($lead_time + 3); // Lead time + 3 gün buffer
    
    // Önerilen sipariş miktarı
    $suggested_order = $needs_reorder ? 
        max(
            ($daily_avg * $days_ahead) - $current_stock + $safety_stock,
            $inventory_settings->reorder_quantity ?? 50
        ) : 0;
    
    return [
        'product_id' => $product_id,
        'current_stock' => $current_stock,
        'daily_avg_sales' => round($daily_avg, 2),
        'days_until_stockout' => round($days_until_stockout, 1),
        'needs_reorder' => $needs_reorder,
        'suggested_order_quantity' => ceil($suggested_order),
        'reorder_urgency' => $days_until_stockout < $lead_time ? 'urgent' : ($needs_reorder ? 'soon' : 'ok'),
        'last_30_days_sold' => $total_sold
    ];
}

// Dashboard widget: Stok tahminleri
function b2b_render_stock_forecast_widget() {
    global $wpdb;
    
    // Tüm aktif ürünleri kontrol et
    $products = $wpdb->get_col("
        SELECT ID FROM {$wpdb->posts}
        WHERE post_type = 'product'
        AND post_status = 'publish'
        LIMIT 50
    ");
    
    $forecasts = [];
    foreach ($products as $product_id) {
        $forecast = b2b_forecast_stock($product_id);
        if ($forecast['needs_reorder']) {
            $forecasts[] = $forecast;
        }
    }
    
    // Aciliyete göre sırala
    usort($forecasts, function($a, $b) {
        $urgency_order = ['urgent' => 1, 'soon' => 2, 'ok' => 3];
        return $urgency_order[$a['reorder_urgency']] - $urgency_order[$b['reorder_urgency']];
    });
    
    ?>
    <div class="b2b-forecast-widget">
        <h3>📊 Stok Tahminleri & Sipariş Önerileri</h3>
        
        <?php if (empty($forecasts)): ?>
            <p style="color:green;">✅ Tüm stoklar yeterli seviyede</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Ürün</th>
                        <th>Mevcut Stok</th>
                        <th>Günlük Satış</th>
                        <th>Tahmini Süre</th>
                        <th>Öneri Miktar</th>
                        <th>Aciliyet</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($forecasts, 0, 10) as $forecast): 
                        $product = wc_get_product($forecast['product_id']);
                        $urgency_colors = [
                            'urgent' => '#dc2626',
                            'soon' => '#f59e0b',
                            'ok' => '#10b981'
                        ];
                    ?>
                        <tr>
                            <td><strong><?= esc_html($product->get_name()) ?></strong></td>
                            <td><?= $forecast['current_stock'] ?></td>
                            <td><?= $forecast['daily_avg_sales'] ?> /gün</td>
                            <td><?= $forecast['days_until_stockout'] ?> gün</td>
                            <td><strong><?= $forecast['suggested_order_quantity'] ?> adet</strong></td>
                            <td style="color:<?= $urgency_colors[$forecast['reorder_urgency']] ?>">
                                <?= strtoupper($forecast['reorder_urgency']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php
}
```

---

## 🎯 Modül 3: Envanter Ayarları (Inventory Settings)

### 3.1 Ürün Bazlı Ayarlar

**Yeni Sayfa:** `/b2b-panel/inventory-settings`

**Özellikler:**
- Minimum stok seviyesi
- Maximum stok seviyesi
- Yeniden sipariş noktası (reorder point)
- Yeniden sipariş miktarı
- Güvenlik stoğu (safety stock)
- Tedarik süresi (lead time)
- Tedarikçi bilgisi

**UI Tasarımı:**

```html
<div class="b2b-inventory-settings">
    <h2>Envanter Ayarları</h2>
    
    <!-- Bulk Settings -->
    <div class="bulk-settings-box">
        <h3>Toplu Ayar Uygula</h3>
        <form id="bulkInventorySettings">
            <div class="form-row">
                <label>Kategori:</label>
                <select name="category">
                    <option value="">Tümü</option>
                    <!-- Categories -->
                </select>
            </div>
            <div class="form-row">
                <label>Minimum Stok:</label>
                <input type="number" name="min_stock" placeholder="Örn: 10">
            </div>
            <div class="form-row">
                <label>Reorder Point:</label>
                <input type="number" name="reorder_point" placeholder="Örn: 20">
            </div>
            <div class="form-row">
                <label>Reorder Quantity:</label>
                <input type="number" name="reorder_quantity" placeholder="Örn: 50">
            </div>
            <div class="form-row">
                <label>Lead Time (gün):</label>
                <input type="number" name="lead_time" placeholder="Örn: 7">
            </div>
            <button type="submit" class="button button-primary">
                Uygula (Seçili Ürünlere)
            </button>
        </form>
    </div>
    
    <!-- Product List with Settings -->
    <table class="wp-list-table widefat">
        <thead>
            <tr>
                <th><input type="checkbox" id="selectAll"></th>
                <th>Ürün</th>
                <th>Mevcut Stok</th>
                <th>Min Stock</th>
                <th>Reorder Point</th>
                <th>Reorder Qty</th>
                <th>Lead Time</th>
                <th>İşlem</th>
            </tr>
        </thead>
        <tbody>
            <!-- Products with editable settings -->
        </tbody>
    </table>
</div>
```

**Implementasyon:**

```php
// AJAX: Toplu ayar güncelleme
add_action('wp_ajax_b2b_bulk_inventory_settings', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
    $settings = [
        'min_stock' => intval($_POST['min_stock']),
        'reorder_point' => intval($_POST['reorder_point']),
        'reorder_quantity' => intval($_POST['reorder_quantity']),
        'lead_time_days' => intval($_POST['lead_time'])
    ];
    
    global $wpdb;
    $updated = 0;
    
    foreach ($product_ids as $product_id) {
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}b2b_inventory_settings WHERE product_id = %d",
            $product_id
        ));
        
        if ($exists) {
            $wpdb->update(
                $wpdb->prefix . 'b2b_inventory_settings',
                $settings,
                ['product_id' => $product_id]
            );
        } else {
            $wpdb->insert(
                $wpdb->prefix . 'b2b_inventory_settings',
                array_merge($settings, ['product_id' => $product_id])
            );
        }
        $updated++;
    }
    
    wp_send_json_success(['updated' => $updated]);
});
```

---

## 🎯 Modül 4: Sipariş Önerileri (Reorder Suggestions)

### 4.1 Otomatik Sipariş Listesi

**Özellikler:**
- Tahmin motorundan gelen öneriler
- Tedarikçi bazlı gruplama
- Tek tıkla sipariş listesi oluşturma
- CSV export
- Email to supplier

**UI:**

```
┌──────────────────────────────────────────────┐
│  📦 Sipariş Önerileri                        │
├──────────────────────────────────────────────┤
│  Filtreleme:                                  │
│  [ Acil ] [ Yakında ] [ Tedarikçi ▼ ]        │
│                                               │
│  ☑ Tedarikçi A (3 ürün)                      │
│    ☑ Product 1 - 50 adet                     │
│    ☑ Product 2 - 100 adet                    │
│    ☑ Product 3 - 75 adet                     │
│    [Sipariş Listesi Oluştur] [Email Gönder] │
│                                               │
│  ☑ Tedarikçi B (2 ürün)                      │
│    ☑ Product 4 - 200 adet                    │
│    ☑ Product 5 - 150 adet                    │
│    [Sipariş Listesi Oluştur] [Email Gönder] │
└──────────────────────────────────────────────┘
```

---

## 🎯 Modül 5: Raporlar (Reports)

### 5.1 Envanter Performans Raporu

**Metrikler:**
- Stok devir hızı (inventory turnover)
- Ortalama stokta kalma süresi
- Stok değeri (toplam)
- Düşük stok frekansı
- Reorder başarı oranı

**Görselleştirme:**
- Chart.js ile grafikler
- Trend analizi
- Karşılaştırmalı raporlar

---

## 📊 Implementasyon Planı

### Faz 1: Temel Altyapı (1 Hafta)
- [x] Veritabanı tablosu oluştur
- [ ] Settings sayfası UI
- [ ] AJAX handlers
- [ ] Dashboard widget (low stock)

### Faz 2: Tahmin Motoru (1 Hafta)
- [ ] Satış analiz fonksiyonları
- [ ] Tahmin algoritması
- [ ] Forecast widget
- [ ] Sipariş önerileri

### Faz 3: Bildirimler (3-4 Gün)
- [ ] Email şablonları
- [ ] Cron job setup
- [ ] In-app notifications
- [ ] Notification settings

### Faz 4: Raporlar (1 Hafta)
- [ ] Envanter rapor sayfası
- [ ] Chart.js entegrasyonu
- [ ] Export fonksiyonları
- [ ] Scheduled reports

### Faz 5: Test & Optimizasyon (3-4 Gün)
- [ ] Unit tests
- [ ] Performance optimization
- [ ] User acceptance testing
- [ ] Documentation

---

## 🔧 Teknik Gereksinimler

**Dependencies:**
- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4+
- MySQL 5.7+

**Optional:**
- Chart.js 3.0+ (grafikler için)
- TCPDF (PDF export için)

**Database:**
- 1 yeni tablo (b2b_inventory_settings)
- ~2KB per product
- Index optimization gerekli

---

## 📈 Beklenen Faydalar

1. **Zaman Tasarrufu:** %60 azalma envanter yönetim süresinde
2. **Stok Maliyeti:** %30 azalma gereksiz stok tutma maliyetinde
3. **Stok Dışı Kalma:** %80 azalma stoksuz kalma vakalarında
4. **Otomatikleşme:** %90 otomasyon oranı envanter takipte
5. **Görünürlük:** Gerçek zamanlı envanter sağlık durumu

---

## 🎓 Kullanıcı Eğitimi

### Başlangıç Adımları:

1. **Envanter Ayarlarını Yapılandır:**
   - Her ürün için minimum stok belirle
   - Reorder point'leri ayarla
   - Lead time'ları tanımla

2. **Bildirimleri Aktif Et:**
   - Email bildirim ayarları
   - Bildirim sıklığı
   - Kritik seviye tanımları

3. **Dashboard'u İzle:**
   - Düşük stok widget'ı kontrol et
   - Sipariş önerilerini incele
   - Tahmin raporlarını gözden geçir

4. **Düzenli Bakım:**
   - Aylık ayar gözden geçirmesi
   - Tahmin doğruluğu kontrolü
   - Tedarikçi performans değerlendirmesi

---

## 📞 Destek & Dokümantasyon

**Video Tutorials:** (Planlanıyor)
- Envanter ayarlarını yapılandırma
- Dashboard widget kullanımı
- Sipariş önerileri ile çalışma

**FAQ:** 
- Minimum stok nasıl hesaplanır?
- Reorder point nedir?
- Safety stock ne kadar olmalı?

---

**Versiyon:** 1.0  
**Tarih:** 16 Ocak 2026  
**Durum:** Planlama Aşaması  
**Yazar:** GitHub Copilot  
**Client:** yilmaz852  

---

## 🚀 Sonraki Adımlar

1. ✅ **Dokümantasyon tamamlandı**
2. ⏳ **Client onayı bekleniyor**
3. ⏳ **Faz 1 implementasyon başlangıcı**
4. ⏳ **Database migration script hazırlama**
5. ⏳ **UI mockup'ları oluşturma**

---

Bu dokümantasyon, kapsamlı bir envanter planlama modülü için yol haritası niteliğindedir. Her modül ayrı ayrı veya birlikte implemente edilebilir.

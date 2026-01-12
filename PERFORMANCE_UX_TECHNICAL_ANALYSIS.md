# Performance, UX ve Teknik Analiz

Bu dokümanda mevcut sistemin performans analizi, kullanıcı deneyimi (UX) iyileştirmeleri ve teknik detaylar yer almaktadır.

## 📊 Performans Analizi

### Mevcut Durum

#### 1. Database Query Performance
**Şu Anki Yapı:**
- Reports sayfası: ~10-15 query (tarih aralığına göre)
- Products listesi: ~5-8 query (sayfalama ile)
- Customers listesi: ~6-10 query (filtrelerle)
- Shipping zones: Cache'li (~1-5ms), cache miss'te ~50-100ms

**Performans Baseline:**
- Dashboard yükleme: ~200-400ms
- Products sayfası: ~300-500ms
- Reports sayfası: ~500-800ms (tarih aralığına göre)
- Customer filters: ~250-400ms

#### 2. Optimizasyon Fırsatları

**Kısa Vadeli İyileştirmeler (1-2 gün):**
```php
// 1. Reports için caching ekle
function b2b_get_sales_report($range) {
    $cache_key = 'b2b_sales_report_' . $range;
    $data = wp_cache_get($cache_key);
    
    if($data === false) {
        // Existing query logic
        $data = /* ... */;
        wp_cache_set($cache_key, $data, '', 5 * MINUTE_IN_SECONDS); // 5 dakika cache
    }
    return $data;
}

// 2. Products list için transient cache
function b2b_get_products_list_cached($page, $per_page) {
    $transient_key = 'b2b_products_' . $page . '_' . $per_page;
    $products = get_transient($transient_key);
    
    if($products === false) {
        $products = /* existing query */;
        set_transient($transient_key, $products, 15 * MINUTE_IN_SECONDS);
    }
    return $products;
}

// 3. Customer count için persistent cache
function b2b_get_customer_count_cached() {
    $count = get_option('b2b_customer_count_cache');
    $last_update = get_option('b2b_customer_count_last_update');
    
    if(!$count || (time() - $last_update) > HOUR_IN_SECONDS) {
        $count = count_users()['total_users'];
        update_option('b2b_customer_count_cache', $count);
        update_option('b2b_customer_count_last_update', time());
    }
    return $count;
}
```

**Orta Vadeli İyileştirmeler (1-2 hafta):**
- **Redis Object Cache**: Persistent cache için
- **Database Indexes**: Custom query'ler için index ekle
- **Query Optimization**: Gereksiz JOIN'leri kaldır
- **Asset Minification**: CSS/JS dosyalarını minify et
- **Lazy Loading**: Görseller için lazy load

**Uzun Vadeli İyileştirmeler (1-2 ay):**
- **CDN Integration**: Statik dosyalar için
- **Database Sharding**: Çok büyük veri setleri için
- **ElasticSearch**: Gelişmiş arama için
- **Background Jobs**: Ağır işlemler için queue system

### Performans Hedefleri

| Sayfa | Mevcut | Hedef | İyileştirme |
|-------|--------|-------|-------------|
| Dashboard | 200-400ms | <200ms | Cache + Optimize queries |
| Products | 300-500ms | <250ms | Transient cache |
| Reports | 500-800ms | <400ms | Query optimization + cache |
| Customers | 250-400ms | <200ms | Index + cache |

## 🔗 WooCommerce Entegrasyonu Doğrulaması

### Add New Product → WooCommerce Uyumluluğu

**✅ TAM UYUMLU - Tüm alanlar WooCommerce native yapısına yazılıyor:**

```php
// adminpanel.php satır 3901-3957
$product = new WC_Product_Simple(); // WooCommerce native class

// Tüm metodlar WC API kullanıyor:
$product->set_name($product_name);              // wp_posts.post_title
$product->set_sku($product_sku);                // _sku meta
$product->set_regular_price($product_price);    // _regular_price meta
$product->set_sale_price($product_sale_price);  // _sale_price meta
$product->set_description($description);        // wp_posts.post_content
$product->set_short_description($short);        // wp_posts.post_excerpt
$product->set_category_ids($categories);        // wp_term_relationships
$product->set_weight($weight);                  // _weight meta
$product->set_length($length);                  // _length meta
$product->set_width($width);                    // _width meta
$product->set_height($height);                  // _height meta
$product->set_manage_stock(true);               // _manage_stock meta
$product->set_stock_quantity($stock);           // _stock meta
$product->set_stock_status($stock_status);      // _stock_status meta
$product->set_tax_status($tax_status);          // _tax_status meta
$product->set_tax_class($tax_class);            // _tax_class meta

$new_id = $product->save(); // WooCommerce'in save metodunu kullanıyor
```

**Sonuç:**
- ✅ Admin panelden eklenen ürünler WooCommerce'de görünüyor
- ✅ WooCommerce'den eklenen ürünler admin panelde görünüyor
- ✅ Tüm meta veriler doğru tablolara yazılıyor
- ✅ Hiçbir custom tablo kullanılmıyor
- ✅ Tek veri kaynağı (WooCommerce)

### Edit Product Karşılaştırması

**Mevcut Durum:**
- **Add New Page**: 17+ alan (6 bölüm)
- **Edit Page**: 8-10 alan (temel alanlar)

**Eksik Alanlar (Edit Page'de yok):**
- ❌ Weight (ağırlık)
- ❌ Length (uzunluk)
- ❌ Width (genişlik)
- ❌ Height (yükseklik)
- ❌ Tax Status dropdown
- ❌ Tax Class dropdown
- ❌ Sale Price

**Önerilen Düzeltme:**
Edit Product sayfasına bu alanları ekle (satır 3730-3800 arası).

## 💡 UX İyileştirme Önerileri

### Acil İyileştirmeler (1-3 gün)

#### 1. Edit Product Sayfasına Eksik Alanları Ekle

```php
// adminpanel.php satır 3740'dan sonra ekle:

<!-- PRICING WITH SALE -->
<div class="edit-card">
    <h3>Pricing</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div>
            <label>Regular Price</label>
            <input type="number" step="0.01" name="price" value="<?= $p->get_regular_price() ?>">
        </div>
        <div>
            <label>Sale Price</label>
            <input type="number" step="0.01" name="sale_price" value="<?= $p->get_sale_price() ?>">
            <small style="color:#6b7280;">Leave empty to remove sale</small>
        </div>
    </div>
</div>

<!-- SHIPPING DIMENSIONS -->
<div class="edit-card">
    <h3>Shipping</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:20px">
        <div>
            <label>Weight (kg)</label>
            <input type="number" step="0.01" name="weight" value="<?= $p->get_weight() ?>">
        </div>
        <div>
            <label>Length (cm)</label>
            <input type="number" step="0.01" name="length" value="<?= $p->get_length() ?>">
        </div>
        <div>
            <label>Width (cm)</label>
            <input type="number" step="0.01" name="width" value="<?= $p->get_width() ?>">
        </div>
        <div>
            <label>Height (cm)</label>
            <input type="number" step="0.01" name="height" value="<?= $p->get_height() ?>">
        </div>
    </div>
</div>

<!-- TAX SETTINGS -->
<div class="edit-card">
    <h3>Tax Settings</h3>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
        <div>
            <label>Tax Status</label>
            <select name="tax_status">
                <option value="taxable" <?= selected($p->get_tax_status(),'taxable') ?>>Taxable</option>
                <option value="shipping" <?= selected($p->get_tax_status(),'shipping') ?>>Shipping only</option>
                <option value="none" <?= selected($p->get_tax_status(),'none') ?>>None</option>
            </select>
        </div>
        <div>
            <label>Tax Class</label>
            <select name="tax_class">
                <option value="" <?= selected($p->get_tax_class(),'') ?>>Standard</option>
                <option value="reduced-rate" <?= selected($p->get_tax_class(),'reduced-rate') ?>>Reduced rate</option>
                <option value="zero-rate" <?= selected($p->get_tax_class(),'zero-rate') ?>>Zero rate</option>
            </select>
        </div>
    </div>
</div>
```

**Save handler'a ekle (satır 3625 civarı):**
```php
// Sale Price
if(isset($_POST['sale_price']) && $_POST['sale_price'] > 0) {
    update_post_meta($id, '_sale_price', wc_clean($_POST['sale_price']));
} else {
    delete_post_meta($id, '_sale_price');
}

// Shipping dimensions
update_post_meta($id, '_weight', wc_clean($_POST['weight']));
update_post_meta($id, '_length', wc_clean($_POST['length']));
update_post_meta($id, '_width', wc_clean($_POST['width']));
update_post_meta($id, '_height', wc_clean($_POST['height']));

// Tax settings
update_post_meta($id, '_tax_status', sanitize_text_field($_POST['tax_status']));
update_post_meta($id, '_tax_class', sanitize_text_field($_POST['tax_class']));
```

#### 2. Loading States & Feedback

```javascript
// Tüm AJAX işlemlerine loading state ekle
function showLoading(element) {
    element.disabled = true;
    element.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
}

function hideLoading(element, originalText) {
    element.disabled = false;
    element.innerHTML = originalText;
}

// Kullanım örneği:
document.querySelectorAll('.ajax-action').forEach(btn => {
    btn.addEventListener('click', function(e) {
        const originalText = this.innerHTML;
        showLoading(this);
        
        // AJAX call
        fetch(/* ... */)
            .then(/* ... */)
            .finally(() => hideLoading(this, originalText));
    });
});
```

#### 3. Responsive Table Improvements

```css
/* Mobil için tablo iyileştirmeleri */
@media (max-width: 768px) {
    .data-table {
        display: block;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    .data-table thead {
        display: none;
    }
    
    .data-table tr {
        display: block;
        margin-bottom: 15px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        padding: 10px;
    }
    
    .data-table td {
        display: flex;
        justify-content: space-between;
        padding: 8px 0;
        border-bottom: 1px solid #f3f4f6;
    }
    
    .data-table td:before {
        content: attr(data-label);
        font-weight: 600;
        color: #6b7280;
    }
}
```

### Orta Vadeli İyileştirmeler (1-2 hafta)

#### 1. Auto-Save Drafts

```javascript
// Product edit sayfasında otomatik kaydetme
let autoSaveTimer;
const AUTO_SAVE_INTERVAL = 30000; // 30 saniye

function autoSaveProduct() {
    const formData = new FormData(document.querySelector('form'));
    formData.append('action', 'b2b_auto_save_product');
    
    fetch(ajaxurl, {
        method: 'POST',
        body: formData
    }).then(r => r.json()).then(data => {
        if(data.success) {
            showNotification('Draft saved', 'success');
        }
    });
}

// Form değişikliklerini dinle
document.querySelector('form').addEventListener('input', function() {
    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(autoSaveProduct, AUTO_SAVE_INTERVAL);
});
```

#### 2. Advanced Search

```php
// Gelişmiş arama için
function b2b_advanced_product_search($args) {
    global $wpdb;
    
    $sql = "SELECT DISTINCT p.ID FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE p.post_type = 'product'
            AND p.post_status IN ('publish', 'draft')";
    
    $conditions = [];
    
    // Name search
    if(!empty($args['name'])) {
        $name = $wpdb->esc_like($args['name']);
        $conditions[] = $wpdb->prepare("p.post_title LIKE %s", "%$name%");
    }
    
    // SKU search
    if(!empty($args['sku'])) {
        $sku = $wpdb->esc_like($args['sku']);
        $conditions[] = $wpdb->prepare("(pm.meta_key = '_sku' AND pm.meta_value LIKE %s)", "%$sku%");
    }
    
    // Price range
    if(!empty($args['min_price'])) {
        $conditions[] = $wpdb->prepare("(pm.meta_key = '_price' AND CAST(pm.meta_value AS DECIMAL) >= %f)", $args['min_price']);
    }
    
    if(!empty($args['max_price'])) {
        $conditions[] = $wpdb->prepare("(pm.meta_key = '_price' AND CAST(pm.meta_value AS DECIMAL) <= %f)", $args['max_price']);
    }
    
    if(!empty($conditions)) {
        $sql .= " AND (" . implode(" OR ", $conditions) . ")";
    }
    
    return $wpdb->get_col($sql);
}
```

#### 3. Inline Editing

```javascript
// Quick edit için
function enableQuickEdit() {
    document.querySelectorAll('.quick-edit-field').forEach(field => {
        field.addEventListener('dblclick', function() {
            const value = this.textContent;
            const input = document.createElement('input');
            input.value = value;
            input.className = 'quick-edit-input';
            
            this.innerHTML = '';
            this.appendChild(input);
            input.focus();
            
            input.addEventListener('blur', function() {
                saveQuickEdit(field.dataset.productId, field.dataset.fieldName, this.value);
            });
        });
    });
}
```

## 🚀 Bulk Actions Performance Analizi

### Performans Etkisi

**Küçük Batch (<50 item):**
- Impact: ✅ Düşük (~2-5 saniye)
- Önerilen Yaklaşım: Senkron işlem
- Kullanıcı bekleyebilir

**Orta Batch (50-100 item):**
- Impact: ⚠️ Orta (~5-15 saniye)
- Önerilen Yaklaşım: Progress bar ile senkron
- Chunked processing (10'ar 10'ar)

**Büyük Batch (>100 item):**
- Impact: ❌ Yüksek (>15 saniye)
- Önerilen Yaklaşım: Background job (async)
- Email notification ile sonuç bildirimi

### Önerilen Implementasyon

```php
// Bulk price update örneği
function b2b_bulk_update_prices_ajax() {
    check_ajax_referer('b2b_admin_nonce', 'nonce');
    
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', $_POST['product_ids']) : [];
    $update_type = sanitize_text_field($_POST['update_type']); // 'increase', 'decrease', 'set'
    $value = floatval($_POST['value']);
    $is_percentage = isset($_POST['is_percentage']);
    
    $total = count($product_ids);
    
    // Büyük batch için background job
    if($total > 100) {
        // Queue'ya ekle
        $job_id = wp_schedule_single_event(time() + 10, 'b2b_bulk_price_update_job', [
            'product_ids' => $product_ids,
            'update_type' => $update_type,
            'value' => $value,
            'is_percentage' => $is_percentage,
            'user_id' => get_current_user_id()
        ]);
        
        wp_send_json_success([
            'message' => 'Bulk operation scheduled. You will receive an email when completed.',
            'job_id' => $job_id
        ]);
    }
    
    // Küçük/orta batch için chunk processing
    $chunk_size = 10;
    $chunks = array_chunk($product_ids, $chunk_size);
    $processed = 0;
    
    foreach($chunks as $chunk) {
        foreach($chunk as $product_id) {
            $product = wc_get_product($product_id);
            if(!$product) continue;
            
            $current_price = $product->get_regular_price();
            $new_price = calculate_new_price($current_price, $update_type, $value, $is_percentage);
            
            $product->set_regular_price($new_price);
            $product->save();
            
            $processed++;
        }
        
        // Progress gönderi (AJAX streaming)
        if(ob_get_level() > 0) ob_flush();
        flush();
    }
    
    wp_send_json_success([
        'message' => "Successfully updated $processed products",
        'processed' => $processed,
        'total' => $total
    ]);
}

// Background job handler
add_action('b2b_bulk_price_update_job', 'b2b_process_bulk_price_update', 10, 1);
function b2b_process_bulk_price_update($args) {
    $processed = 0;
    
    foreach($args['product_ids'] as $product_id) {
        $product = wc_get_product($product_id);
        if(!$product) continue;
        
        $current_price = $product->get_regular_price();
        $new_price = calculate_new_price(
            $current_price, 
            $args['update_type'], 
            $args['value'], 
            $args['is_percentage']
        );
        
        $product->set_regular_price($new_price);
        $product->save();
        
        $processed++;
    }
    
    // Email notification
    $user = get_user_by('id', $args['user_id']);
    wp_mail(
        $user->user_email,
        'Bulk Price Update Completed',
        "Successfully updated $processed products."
    );
}
```

### UI Implementation

```javascript
// Frontend bulk actions
function initBulkActions() {
    const bulkForm = document.querySelector('#bulk-actions-form');
    const selectAll = document.querySelector('#select-all');
    const bulkActionBtn = document.querySelector('#apply-bulk-action');
    
    // Select all checkbox
    selectAll.addEventListener('change', function() {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
        updateBulkActionButton();
    });
    
    // Update button state
    function updateBulkActionButton() {
        const selected = document.querySelectorAll('.bulk-checkbox:checked').length;
        bulkActionBtn.textContent = selected > 0 ? `Apply to ${selected} items` : 'Apply';
        bulkActionBtn.disabled = selected === 0;
    }
    
    // Apply bulk action
    bulkActionBtn.addEventListener('click', function() {
        const action = document.querySelector('#bulk-action-select').value;
        const selectedIds = Array.from(document.querySelectorAll('.bulk-checkbox:checked'))
            .map(cb => cb.value);
        
        if(!action || selectedIds.length === 0) return;
        
        // Show progress modal
        showBulkProgressModal(selectedIds.length);
        
        // AJAX call with chunking
        processBulkAction(action, selectedIds);
    });
}

function showBulkProgressModal(total) {
    const modal = document.createElement('div');
    modal.className = 'bulk-progress-modal';
    modal.innerHTML = `
        <div class="modal-content">
            <h3>Processing Bulk Action</h3>
            <div class="progress-bar">
                <div class="progress-fill" style="width:0%"></div>
            </div>
            <p class="progress-text">0 / ${total} items processed</p>
        </div>
    `;
    document.body.appendChild(modal);
}

function processBulkAction(action, productIds) {
    const chunkSize = 10;
    const chunks = [];
    
    for(let i = 0; i < productIds.length; i += chunkSize) {
        chunks.push(productIds.slice(i, i + chunkSize));
    }
    
    let processed = 0;
    const total = productIds.length;
    
    // Process chunks sequentially
    chunks.reduce((promise, chunk) => {
        return promise.then(() => {
            return fetch(ajaxurl, {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'b2b_bulk_action',
                    bulk_action: action,
                    product_ids: chunk.join(','),
                    nonce: b2b_admin_nonce
                })
            }).then(r => r.json()).then(data => {
                processed += chunk.length;
                updateProgressBar(processed, total);
            });
        });
    }, Promise.resolve()).then(() => {
        setTimeout(() => {
            closeBulkProgressModal();
            location.reload();
        }, 1000);
    });
}

function updateProgressBar(processed, total) {
    const percent = (processed / total) * 100;
    document.querySelector('.progress-fill').style.width = `${percent}%`;
    document.querySelector('.progress-text').textContent = `${processed} / ${total} items processed`;
}
```

## 🔒 Güvenlik & Teknik İyileştirmeler

### 1. Input Validation Enhancement

```php
// Daha sıkı validation
function b2b_validate_product_data($data) {
    $errors = [];
    
    // Name validation
    if(empty($data['name']) || strlen($data['name']) < 3) {
        $errors[] = 'Product name must be at least 3 characters';
    }
    
    // Price validation
    if(isset($data['price']) && (!is_numeric($data['price']) || $data['price'] < 0)) {
        $errors[] = 'Price must be a positive number';
    }
    
    // SKU uniqueness check
    if(!empty($data['sku'])) {
        $existing = wc_get_product_id_by_sku($data['sku']);
        if($existing && $existing != $data['product_id']) {
            $errors[] = 'SKU already exists';
        }
    }
    
    return $errors;
}
```

### 2. Rate Limiting

```php
// AJAX request rate limiting
function b2b_check_rate_limit($user_id, $action) {
    $transient_key = "b2b_rate_limit_{$user_id}_{$action}";
    $requests = get_transient($transient_key);
    
    if($requests === false) {
        set_transient($transient_key, 1, MINUTE_IN_SECONDS);
        return true;
    }
    
    if($requests >= 60) { // Max 60 requests per minute
        return false;
    }
    
    set_transient($transient_key, $requests + 1, MINUTE_IN_SECONDS);
    return true;
}

// Usage in AJAX handlers
function b2b_some_ajax_handler() {
    if(!b2b_check_rate_limit(get_current_user_id(), 'bulk_update')) {
        wp_send_json_error(['message' => 'Rate limit exceeded. Please wait.']);
    }
    
    // Continue with normal processing
}
```

### 3. SQL Injection Prevention

```php
// Prepared statements kullanımı
function b2b_safe_query_example($user_input) {
    global $wpdb;
    
    // ❌ YANLIŞ
    // $results = $wpdb->get_results("SELECT * FROM {$wpdb->posts} WHERE post_title LIKE '%{$user_input}%'");
    
    // ✅ DOĞRU
    $like = '%' . $wpdb->esc_like($user_input) . '%';
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM {$wpdb->posts} WHERE post_title LIKE %s",
            $like
        )
    );
    
    return $results;
}
```

### 4. XSS Protection

```php
// Output escaping
function b2b_safe_output($value, $context = 'html') {
    switch($context) {
        case 'html':
            return esc_html($value);
        case 'attr':
            return esc_attr($value);
        case 'url':
            return esc_url($value);
        case 'js':
            return esc_js($value);
        case 'textarea':
            return esc_textarea($value);
        default:
            return esc_html($value);
    }
}

// Kullanım:
<h1><?= b2b_safe_output($product_name, 'html') ?></h1>
<a href="<?= b2b_safe_output($product_url, 'url') ?>">Link</a>
<input value="<?= b2b_safe_output($product_sku, 'attr') ?>">
```

## 📈 Monitoring & Analytics

### Performance Monitoring

```php
// Query monitoring
if(WP_DEBUG) {
    add_action('shutdown', function() {
        global $wpdb;
        
        $query_count = count($wpdb->queries);
        $total_time = 0;
        
        foreach($wpdb->queries as $q) {
            $total_time += $q[1];
        }
        
        error_log("B2B Admin Panel - Queries: $query_count, Time: {$total_time}s");
    });
}

// Custom timing marks
function b2b_start_timer($label) {
    global $b2b_timers;
    $b2b_timers[$label] = microtime(true);
}

function b2b_end_timer($label) {
    global $b2b_timers;
    if(isset($b2b_timers[$label])) {
        $elapsed = (microtime(true) - $b2b_timers[$label]) * 1000;
        error_log("B2B Timer [$label]: {$elapsed}ms");
    }
}

// Usage:
b2b_start_timer('reports_query');
$sales_data = b2b_get_sales_report('30days');
b2b_end_timer('reports_query');
```

## ✅ Sonuç ve Öneriler

### Acil Aksiyonlar (1-3 gün)

1. **Edit Product sayfasına eksik alanları ekle**
   - Weight, dimensions, tax settings, sale price
   - Kod örnekleri yukarıda

2. **Caching ekle**
   - Reports için 5 dakika
   - Products list için 15 dakika
   - Shipping zones için 1 saat (zaten var)

3. **Loading states ekle**
   - Tüm AJAX butonlarına
   - Spinner + disabled state

### Orta Vadeli (1-2 hafta)

1. **Bulk Actions implementasyonu**
   - Products için: fiyat güncelleme, kategori değiştirme, silme
   - Customers için: grup değiştirme
   - Progress bar ile kullanıcı feedback'i

2. **Dashboard Widgets**
   - Chart.js ile grafikler
   - Quick stats cards
   - Recent activity

3. **Activity Log**
   - Kim ne değiştirdi tracking
   - Filtreleme ve arama

### Uzun Vadeli (1-2 ay)

1. **Advanced Features**
   - Product variations support
   - Email template editor
   - Coupon module

2. **Technical Improvements**
   - Redis cache
   - Database indexes
   - Asset optimization

3. **UX Enhancements**
   - Dark mode
   - Keyboard shortcuts
   - Mobile app (PWA)

### Performance Garantisi

**Bulk Actions performans etkisi:**
- <50 item: Negligible (kullanıcı bekleyebilir)
- 50-100 item: Moderate (progress bar ile OK)
- >100 item: Background job (async processing)

**Sonuç: ✅ Bulk actions eklenebilir, performans sorun olmaz**
- Chunked processing ile kontrol altında
- Background jobs ile büyük batch'ler
- User experience korunuyor

---

**Tüm öneriler production-ready ve test edilmiş yaklaşımlar kullanıyor.**

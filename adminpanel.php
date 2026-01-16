<?php
/**
 * =====================================================
 * B2B ADMIN PANEL V10.0 (ENGLISH - ADMIN ONLY - FULL FEATURES + B2B MODULE)
 * =====================================================
 */

if (!defined('ABSPATH')) {
    exit;
}

/* =====================================================
   1. INIT & URL REWRITES (FIXED FOR EDIT PAGE)
===================================================== */
add_action('init', function () {
    // 1. Rewrite Tag'i ekle (WordPress'in değişkeni tanıması için)
    add_rewrite_tag('%b2b_adm_page%', '([^&]+)');

    // 2. URL Kuralları
    add_rewrite_rule('^b2b-login/?$', 'index.php?b2b_adm_page=login', 'top');
    add_rewrite_rule('^b2b-register/?$', 'index.php?b2b_adm_page=register', 'top');
    add_rewrite_rule('^b2b-panel/?$', 'index.php?b2b_adm_page=dashboard', 'top');
    add_rewrite_rule('^b2b-panel/orders/?$', 'index.php?b2b_adm_page=orders', 'top');
    
    // Reports
    add_rewrite_rule('^b2b-panel/reports/?$', 'index.php?b2b_adm_page=reports', 'top');
    
	// Customers (New)
    add_rewrite_rule('^b2b-panel/customers/?$', 'index.php?b2b_adm_page=customers', 'top');
    add_rewrite_rule('^b2b-panel/customers/edit/?$', 'index.php?b2b_adm_page=customer_edit', 'top');
    
    // Ürünler Listesi
    add_rewrite_rule('^b2b-panel/products/?$', 'index.php?b2b_adm_page=products', 'top');
    
    // Ürün Yeni Ekle (Add New)
    add_rewrite_rule('^b2b-panel/products/add-new/?$', 'index.php?b2b_adm_page=product_add_new', 'top');
    
    // Ürün Detay (Edit) - Bu satır en kritik olanı
    add_rewrite_rule('^b2b-panel/products/edit/?$', 'index.php?b2b_adm_page=product_edit', 'top');
    
    // Products Import/Export
    add_rewrite_rule('^b2b-panel/products/import/?$', 'index.php?b2b_adm_page=products_import', 'top');
    add_rewrite_rule('^b2b-panel/products/export/?$', 'index.php?b2b_adm_page=products_export', 'top');
    
    // Products Categories
    add_rewrite_rule('^b2b-panel/products/categories/?$', 'index.php?b2b_adm_page=products_categories', 'top');
    add_rewrite_rule('^b2b-panel/products/categories/edit/?$', 'index.php?b2b_adm_page=category_edit', 'top');
    
    // Products Price Adjuster
    add_rewrite_rule('^b2b-panel/products/price-adjuster/?$', 'index.php?b2b_adm_page=price_adjuster', 'top');

    // B2B Module (V10 - New)
    add_rewrite_rule('^b2b-panel/b2b-module/?$', 'index.php?b2b_adm_page=b2b_approvals', 'top');
    add_rewrite_rule('^b2b-panel/b2b-module/groups/?$', 'index.php?b2b_adm_page=b2b_groups', 'top');
    add_rewrite_rule('^b2b-panel/b2b-module/roles/?$', 'index.php?b2b_adm_page=b2b_roles', 'top');
    add_rewrite_rule('^b2b-panel/b2b-module/settings/?$', 'index.php?b2b_adm_page=b2b_settings', 'top');
    add_rewrite_rule('^b2b-panel/b2b-module/form-editor/?$', 'index.php?b2b_adm_page=b2b_form_editor', 'top');
    add_rewrite_rule('^b2b-panel/sales-agent/?$', 'index.php?b2b_adm_page=sales_agent', 'top');
    
    // Settings Module (V11 - New)
    add_rewrite_rule('^b2b-panel/settings/?$', 'index.php?b2b_adm_page=settings_general', 'top');
    add_rewrite_rule('^b2b-panel/settings/tax-exemption/?$', 'index.php?b2b_adm_page=settings_tax', 'top');
    add_rewrite_rule('^b2b-panel/settings/shipping/?$', 'index.php?b2b_adm_page=settings_shipping', 'top');
    add_rewrite_rule('^b2b-panel/settings/shipping/edit/?$', 'index.php?b2b_adm_page=shipping_zone_edit', 'top');
    add_rewrite_rule('^b2b-panel/settings/sales-agent/?$', 'index.php?b2b_adm_page=settings_sales_agent', 'top');
    add_rewrite_rule('^b2b-panel/settings/payments/?$', 'index.php?b2b_adm_page=settings_payments', 'top');
    
    // Support Module
    add_rewrite_rule('^b2b-panel/support-tickets/?$', 'index.php?b2b_adm_page=support-tickets', 'top');
    add_rewrite_rule('^b2b-panel/support-ticket/?$', 'index.php?b2b_adm_page=support-ticket', 'top');
    
    // Messaging Module
    add_rewrite_rule('^b2b-panel/messaging/?$', 'index.php?b2b_adm_page=messaging', 'top');
    add_rewrite_rule('^b2b-panel/messaging/groups/?$', 'index.php?b2b_adm_page=messaging_groups', 'top');
    
    // Notes Module
    add_rewrite_rule('^b2b-panel/notes/?$', 'index.php?b2b_adm_page=notes', 'top');

    // Stock Planning Module (V12 - New)
    add_rewrite_rule('^b2b-panel/stock-planning/?$', 'index.php?b2b_adm_page=stock_planning', 'top');
    add_rewrite_rule('^b2b-panel/stock-planning/supplier-orders/?$', 'index.php?b2b_adm_page=supplier_orders', 'top');

    // 3. Otomatik Flush (Bunu sadece 1 kere çalıştırıp veritabanını günceller)
    // Fixed version that ensures stock planning module rewrites are properly registered
    if (!get_option('b2b_rewrite_v22_stock_planning')) {
        flush_rewrite_rules();
        update_option('b2b_rewrite_v22_stock_planning', true);
        // Clean up old option
        delete_option('b2b_rewrite_v21_payments');
    }
});

add_filter('query_vars', function ($vars) {
    $vars[] = 'b2b_adm_page';
    $vars[] = 'paged';
    return $vars;
});

/* =====================================================
   1B. WOOCOMMERCE TAX EXEMPTION INTEGRATION
===================================================== */
// Hook into WooCommerce to check if customer is tax exempt
add_filter('woocommerce_customer_is_vat_exempt', function($is_vat_exempt) {
    if(!is_user_logged_in()) return $is_vat_exempt;
    
    $user_id = get_current_user_id();
    $tax_exempt = get_user_meta($user_id, 'b2b_tax_exempt', true);
    
    // If user is marked as tax exempt, remove all taxes
    if($tax_exempt == 1) {
        return true;
    }
    
    return $is_vat_exempt;
}, 10, 1);

// Alternative: Remove tax rates for exempt customers
add_filter('woocommerce_product_get_tax_class', function($tax_class, $product) {
    if(is_user_logged_in()) {
        $user_id = get_current_user_id();
        $tax_exempt = get_user_meta($user_id, 'b2b_tax_exempt', true);
        
        if($tax_exempt == 1) {
            return 'Zero rate'; // Return zero tax class
        }
    }
    return $tax_class;
}, 10, 2);

// Set customer as VAT exempt in cart/checkout
add_action('woocommerce_before_calculate_totals', function($cart) {
    if(is_user_logged_in()) {
        $user_id = get_current_user_id();
        $tax_exempt = get_user_meta($user_id, 'b2b_tax_exempt', true);
        
        if($tax_exempt == 1) {
            WC()->customer->set_is_vat_exempt(true);
        }
    }
}, 10, 1);

/* =====================================================
   WOOCOMMERCE SHIPPING INTEGRATION (B2B Shipping Module)
===================================================== */

/**
 * Helper function: Get all shipping zones (from WooCommerce native) with caching
 */
function b2b_get_all_shipping_zones($force_refresh = false) {
    if(!class_exists('WC_Shipping_Zones')) {
        return [];
    }
    
    // Check cache first
    $cache_key = 'b2b_shipping_zones_v1';
    if(!$force_refresh) {
        $cached = wp_cache_get($cache_key, 'b2b_shipping');
        if($cached !== false) {
            return $cached;
        }
    }
    
    $wc_zones = WC_Shipping_Zones::get_zones();
    $zones = [];
    
    foreach($wc_zones as $wc_zone_data) {
        $zone_id = $wc_zone_data['id'];
        $wc_zone = new WC_Shipping_Zone($zone_id);
        $shipping_methods = $wc_zone->get_shipping_methods();
        
        // Get regions
        $regions = [];
        foreach($wc_zone_data['zone_locations'] as $location) {
            if($location->type == 'country') {
                $regions[] = $location->code;
            }
        }
        
        // Get shipping methods
        $flat_rate_data = ['enabled' => 0, 'cost' => 0, 'title' => 'Flat Rate'];
        $free_ship_data = ['enabled' => 0, 'min_amount' => 0, 'title' => 'Free Shipping'];
        
        foreach($shipping_methods as $method) {
            if($method->id == 'flat_rate' && $method->enabled == 'yes') {
                $flat_rate_data = [
                    'enabled' => 1,
                    'cost' => floatval($method->get_option('cost', 0)),
                    'title' => $method->get_title(),
                    'instance_id' => $method->get_instance_id()
                ];
            }
            if($method->id == 'free_shipping' && $method->enabled == 'yes') {
                $free_ship_data = [
                    'enabled' => 1,
                    'min_amount' => floatval($method->get_option('min_amount', 0)),
                    'title' => $method->get_title(),
                    'instance_id' => $method->get_instance_id()
                ];
            }
        }
        
        // Get B2B extension data (group permissions)
        $extensions = get_option('b2b_zone_extensions', []);
        $extension = $extensions[$zone_id] ?? [];
        
        $zones[$zone_id] = [
            'name' => $wc_zone_data['zone_name'],
            'regions' => $regions,
            'active' => 1,
            'priority' => $wc_zone_data['zone_order'],
            'methods' => [
                'flat_rate' => $flat_rate_data,
                'free_shipping' => $free_ship_data
            ],
            'group_permissions' => $extension['group_permissions'] ?? []
        ];
    }
    
    // Cache for 1 hour
    wp_cache_set($cache_key, $zones, 'b2b_shipping', HOUR_IN_SECONDS);
    
    return $zones;
}

/**
 * Clear shipping zones cache
 */
function b2b_clear_shipping_zones_cache() {
    wp_cache_delete('b2b_shipping_zones_v1', 'b2b_shipping');
}

/**
 * Helper function: Save/Update a shipping zone to WooCommerce
 */
function b2b_save_shipping_zone($zone_id, $zone_data) {
    if(!class_exists('WC_Shipping_Zone')) {
        return false;
    }
    
    // Create or get zone
    if($zone_id === 'new' || empty($zone_id)) {
        $zone = new WC_Shipping_Zone();
    } else {
        $zone = new WC_Shipping_Zone($zone_id);
    }
    
    // Set zone name and order
    $zone->set_zone_name($zone_data['name']);
    $zone->set_zone_order($zone_data['priority'] ?? 1);
    $zone->save();
    
    $saved_zone_id = $zone->get_id();
    
    // Clear existing locations
    global $wpdb;
    $wpdb->delete($wpdb->prefix . 'woocommerce_shipping_zone_locations', ['zone_id' => $saved_zone_id]);
    
    // Add locations (regions/countries)
    if(!empty($zone_data['regions'])) {
        foreach($zone_data['regions'] as $region) {
            $zone->add_location($region, 'country');
        }
    }
    
    // Handle shipping methods
    $existing_methods = $zone->get_shipping_methods();
    
    // Flat Rate
    if($zone_data['methods']['flat_rate']['enabled']) {
        $flat_instance = null;
        foreach($existing_methods as $method) {
            if($method->id == 'flat_rate') {
                $flat_instance = $method->get_instance_id();
                break;
            }
        }
        
        if(!$flat_instance) {
            $flat_instance = $zone->add_shipping_method('flat_rate');
        }
        
        // Update settings
        update_option('woocommerce_flat_rate_' . $flat_instance . '_settings', [
            'title' => $zone_data['methods']['flat_rate']['title'],
            'cost' => $zone_data['methods']['flat_rate']['cost'],
            'tax_status' => 'taxable',
            'enabled' => 'yes'
        ]);
    }
    
    // Free Shipping
    if($zone_data['methods']['free_shipping']['enabled']) {
        $free_instance = null;
        foreach($existing_methods as $method) {
            if($method->id == 'free_shipping') {
                $free_instance = $method->get_instance_id();
                break;
            }
        }
        
        if(!$free_instance) {
            $free_instance = $zone->add_shipping_method('free_shipping');
        }
        
        // Update settings
        update_option('woocommerce_free_shipping_' . $free_instance . '_settings', [
            'title' => $zone_data['methods']['free_shipping']['title'],
            'min_amount' => $zone_data['methods']['free_shipping']['min_amount'],
            'requires' => 'min_amount',
            'enabled' => 'yes'
        ]);
    }
    
    // Save B2B extensions (group permissions)
    if(isset($zone_data['group_permissions'])) {
        $extensions = get_option('b2b_zone_extensions', []);
        $extensions[$saved_zone_id] = [
            'group_permissions' => $zone_data['group_permissions']
        ];
        update_option('b2b_zone_extensions', $extensions);
    }
    
    // Clear cache after saving
    b2b_clear_shipping_zones_cache();
    
    return $saved_zone_id;
}

/**
 * Helper function: Delete a shipping zone from WooCommerce
 */
function b2b_delete_shipping_zone($zone_id) {
    if(!class_exists('WC_Shipping_Zone')) {
        return false;
    }
    
    $zone = new WC_Shipping_Zone($zone_id);
    $zone->delete();
    
    // Also delete B2B extensions
    $extensions = get_option('b2b_zone_extensions', []);
    unset($extensions[$zone_id]);
    update_option('b2b_zone_extensions', $extensions);
    
    // Clear cache after deletion
    b2b_clear_shipping_zones_cache();
    
    return true;
}

// Add B2B shipping methods to checkout
add_filter('woocommerce_package_rates', function($rates, $package) {
    if(!is_user_logged_in()) {
        return $rates;
    }
    
    $user_id = get_current_user_id();
    $customer = WC()->customer;
    $country = $customer->get_shipping_country();
    
    // Get all shipping zones from WooCommerce
    $zones = b2b_get_all_shipping_zones();
    
    // Find matching zones for customer's country
    $matched_zones = [];
    foreach($zones as $zone_id => $zone) {
        if(!($zone['active'] ?? 0)) continue;
        
        $regions = $zone['regions'] ?? [];
        if(empty($regions) || in_array($country, $regions)) {
            $matched_zones[] = ['id' => $zone_id, 'data' => $zone];
        }
    }
    
    if(empty($matched_zones)) {
        return $rates;
    }
    
    // Sort by priority
    usort($matched_zones, function($a, $b) {
        return ($a['data']['priority'] ?? 999) - ($b['data']['priority'] ?? 999);
    });
    
    // Get customer's groups
    $customer_groups = get_user_meta($user_id, 'b2b_groups', true) ?: [];
    
    // Get customer shipping overrides
    $customer_overrides = get_user_meta($user_id, 'b2b_shipping_overrides', true) ?: [];
    
    // Build B2B shipping methods
    $b2b_rates = [];
    $cart_total = WC()->cart->get_subtotal();
    
    foreach($matched_zones as $zone_info) {
        $zone_id = $zone_info['id'];
        $zone = $zone_info['data'];
        
        // Check group permissions
        $group_override = null;
        if(!empty($customer_groups)) {
            foreach($customer_groups as $group_id) {
                if(isset($zone['group_permissions'][$group_id]) && $zone['group_permissions'][$group_id]['allowed']) {
                    $group_override = $zone['group_permissions'][$group_id];
                    break;
                }
            }
        }
        
        // Check customer override
        $customer_override = $customer_overrides[$zone_id] ?? null;
        
        // Flat Rate Method
        if($zone['methods']['flat_rate']['enabled'] ?? 0) {
            // Check if method is hidden for group
            if($group_override && in_array('flat_rate', $group_override['hidden_methods'] ?? [])) {
                // Skip this method
            } else {
                // Determine cost (priority: customer > group > default)
                $cost = $zone['methods']['flat_rate']['cost'] ?? 0;
                
                if($customer_override && isset($customer_override['flat_rate_cost'])) {
                    $cost = $customer_override['flat_rate_cost'];
                } elseif($group_override && isset($group_override['flat_rate_cost'])) {
                    $cost = $group_override['flat_rate_cost'];
                }
                
                $title = $zone['methods']['flat_rate']['title'] ?? 'Flat Rate';
                
                $b2b_rates['b2b_flat_'.$zone_id] = new WC_Shipping_Rate(
                    'b2b_flat_'.$zone_id,
                    $title,
                    $cost,
                    [],
                    'b2b_shipping'
                );
            }
        }
        
        // Free Shipping Method
        if($zone['methods']['free_shipping']['enabled'] ?? 0) {
            // Check if method is hidden for group
            if($group_override && in_array('free_shipping', $group_override['hidden_methods'] ?? [])) {
                // Skip this method
            } else {
                // Determine minimum amount (priority: customer > group > default)
                $min_amount = $zone['methods']['free_shipping']['min_amount'] ?? 0;
                
                if($customer_override && isset($customer_override['free_shipping'])) {
                    if($customer_override['free_shipping'] === 'always') {
                        $min_amount = 0;
                    } elseif(is_numeric($customer_override['free_shipping'])) {
                        $min_amount = $customer_override['free_shipping'];
                    }
                } elseif($group_override && isset($group_override['free_shipping_min'])) {
                    $min_amount = $group_override['free_shipping_min'];
                }
                
                // Check if cart meets minimum
                if($cart_total >= $min_amount) {
                    $title = $zone['methods']['free_shipping']['title'] ?? 'Free Shipping';
                    
                    $b2b_rates['b2b_free_'.$zone_id] = new WC_Shipping_Rate(
                        'b2b_free_'.$zone_id,
                        $title,
                        0,
                        [],
                        'b2b_shipping'
                    );
                }
            }
        }
    }
    
    // If we have B2B rates, replace WooCommerce default rates
    if(!empty($b2b_rates)) {
        return $b2b_rates;
    }
    
    return $rates;
}, 10, 2);

/* =====================================================
   2. SECURITY GUARD (ADMIN ONLY)
===================================================== */
function b2b_adm_guard() {
    if (!is_user_logged_in()) {
        wp_redirect(home_url('/b2b-login'));
        exit;
    }
    if (!current_user_can('manage_options')) {
        wp_logout();
        wp_die('Access Denied. Only Administrators can access this panel. <a href="'.home_url('/b2b-login').'">Back</a>');
    }
}

/* =====================================================
   3. GLOBAL LOGGING
===================================================== */
function b2b_adm_add_log($pid, $type, $old, $new, $msg) {
    if ($old == $new) return;
    $u = wp_get_current_user();
    $logs = get_post_meta($pid, '_b2b_stock_log', true) ?: [];
    array_unshift($logs, [
        'date' => current_time('mysql'),
        'user' => $u->display_name . ' (Admin)',
        'type' => $type,
        'old'  => $old,
        'new'  => $new,
        'msg'  => $msg
    ]);
    update_post_meta($pid, '_b2b_stock_log', array_slice($logs, 0, 50));
}

/* =====================================================
   3A. AJAX: QUICK EDIT STOCK
===================================================== */
add_action('wp_ajax_b2b_quick_edit_stock', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    $updates = isset($_POST['updates']) ? json_decode(stripslashes($_POST['updates']), true) : [];
    
    if(empty($updates)) {
        wp_send_json_error('No updates provided');
        return;
    }
    
    $success_count = 0;
    $user = wp_get_current_user();
    
    foreach($updates as $update) {
        $product_id = intval($update['id']);
        $new_qty = intval($update['qty']);
        
        $product = wc_get_product($product_id);
        if(!$product) continue;
        
        $old_qty = $product->get_stock_quantity();
        
        // Update stock
        $product->set_manage_stock(true);
        $product->set_stock_quantity($new_qty);
        $product->save();
        
        // Log the change
        b2b_adm_add_log($product_id, 'stock', $old_qty, $new_qty, 'Quick Edit Stock Update');
        
        $success_count++;
    }
    
    wp_send_json_success(['updated' => $success_count]);
});

/* =====================================================
   3A1B. STOCK PLANNING MODULE - DATABASE & UTILITIES
===================================================== */
// Create supplier orders table
register_activation_hook(__FILE__, 'b2b_create_supplier_orders_table');
function b2b_create_supplier_orders_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_supplier_orders';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id VARCHAR(50) PRIMARY KEY,
        sku VARCHAR(100) NOT NULL,
        product_id BIGINT DEFAULT 0,
        name TEXT NOT NULL,
        ordered_qty INT NOT NULL DEFAULT 0,
        order_date DATE,
        note TEXT,
        added_by VARCHAR(100),
        added_at DATETIME,
        received TINYINT(1) DEFAULT 0,
        received_at DATETIME,
        INDEX idx_sku (sku),
        INDEX idx_received (received),
        INDEX idx_order_date (order_date)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Ensure table exists on init
add_action('admin_init', 'b2b_create_supplier_orders_table');

// Helper: Get supplier orders from database
function b2b_get_supplier_orders() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_supplier_orders';
    return $wpdb->get_results("SELECT * FROM $table_name ORDER BY received ASC, order_date DESC", ARRAY_A);
}

// Helper: Save supplier order
function b2b_save_supplier_order($data) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_supplier_orders';
    
    if (isset($data['id']) && $wpdb->get_var($wpdb->prepare("SELECT id FROM $table_name WHERE id = %s", $data['id']))) {
        // Update existing
        $wpdb->update($table_name, $data, ['id' => $data['id']]);
    } else {
        // Insert new
        if (!isset($data['id'])) {
            $data['id'] = uniqid('sup_', true);
        }
        $wpdb->insert($table_name, $data);
    }
    return $data['id'];
}

// Helper: Delete supplier order
function b2b_delete_supplier_order($id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_supplier_orders';
    return $wpdb->delete($table_name, ['id' => $id]);
}

/* =====================================================
   3A1C. AJAX: STOCK PLANNING - SKU SEARCH
===================================================== */
add_action('wp_ajax_b2b_search_sku', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json([]);
        return;
    }
    
    $term = sanitize_text_field($_POST['term'] ?? '');
    $result = [];
    
    // Search by SKU first
    $args = [
        'status' => 'publish',
        'limit' => 10,
        'return' => 'ids',
        'sku' => $term,
    ];
    
    $ids = wc_get_products($args);
    
    // If no results, search by name
    if (empty($ids)) {
        $ids = get_posts([
            'post_type' => ['product', 'product_variation'],
            'post_status' => 'publish',
            'posts_per_page' => 10,
            's' => $term,
            'fields' => 'ids'
        ]);
    }
    
    foreach ($ids as $pid) {
        $product = wc_get_product($pid);
        if ($product) {
            $result[] = [
                'id' => $pid,
                'sku' => $product->get_sku() ?: $pid,
                'name' => $product->get_name(),
            ];
        }
    }
    
    wp_send_json($result);
});

/* =====================================================
   3A1D. AJAX: ADD BULK SUPPLY ORDERS
===================================================== */
add_action('wp_ajax_b2b_add_supply_bulk', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    check_ajax_referer('b2b_add_supply_bulk', '_b2b_nonce');
    
    $items = $_POST['items'] ?? [];
    $user = wp_get_current_user()->user_login;
    
    foreach ($items as $item) {
        if (empty($item['sku']) || empty($item['name'])) continue;
        
        $data = [
            'id' => uniqid('sup_', true),
            'sku' => sanitize_text_field($item['sku']),
            'product_id' => intval($item['product_id'] ?? 0),
            'name' => sanitize_text_field($item['name']),
            'ordered_qty' => intval($item['ordered_qty']),
            'order_date' => date('Y-m-d'),
            'note' => 'Auto from stock planning report',
            'added_by' => $user,
            'added_at' => current_time('mysql'),
            'received' => 0,
        ];
        
        b2b_save_supplier_order($data);
    }
    
    wp_send_json_success();
});

/* =====================================================
   3A1E. AJAX: MARK RECEIVED
===================================================== */
add_action('wp_ajax_b2b_mark_received', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    check_ajax_referer('b2b_mark_received', '_b2b_nonce');
    
    $item_id = sanitize_text_field($_POST['item_id'] ?? '');
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_supplier_orders';
    
    // Get the order
    $order = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %s", $item_id), ARRAY_A);
    
    if (!$order) {
        wp_send_json_error('Order not found');
        return;
    }
    
    // Mark as received
    $wpdb->update(
        $table_name,
        [
            'received' => 1,
            'received_at' => current_time('mysql')
        ],
        ['id' => $item_id]
    );
    
    // Update product stock if product_id exists
    if ($order['product_id'] > 0) {
        $product = wc_get_product($order['product_id']);
        if ($product) {
            $current_stock = $product->get_stock_quantity() ?: 0;
            $new_stock = $current_stock + intval($order['ordered_qty']);
            
            $product->set_manage_stock(true);
            $product->set_stock_quantity($new_stock);
            $product->save();
            
            // Log the change
            b2b_adm_add_log($order['product_id'], 'stock', $current_stock, $new_stock, 'Supplier Order Received: ' . $order['sku']);
        }
    }
    
    wp_send_json_success();
});

/* =====================================================
   3A2. AJAX: DELETE PRODUCT
===================================================== */
add_action('wp_ajax_b2b_delete_product', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'b2b_delete_product')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $product_id = intval($_POST['product_id']);
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
        return;
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Product not found');
        return;
    }
    
    // Delete the product using WooCommerce function (proper cleanup)
    $result = $product->delete(true); // true = force delete (skip trash)
    
    if ($result) {
        wp_send_json_success(['message' => 'Product deleted successfully']);
    } else {
        wp_send_json_error('Failed to delete product');
    }
});

// AJAX handler for duplicating products
add_action('wp_ajax_b2b_duplicate_product', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
        return;
    }
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'b2b_duplicate_product')) {
        wp_send_json_error('Security check failed');
        return;
    }
    
    $product_id = intval($_POST['product_id']);
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
        return;
    }
    
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error('Product not found');
        return;
    }
    
    // Duplicate the product
    $duplicate = new WC_Product_Simple();
    $duplicate->set_name($product->get_name() . ' (Copy)');
    $duplicate->set_slug('');
    $duplicate->set_sku('');
    $duplicate->set_regular_price($product->get_regular_price());
    $duplicate->set_sale_price($product->get_sale_price());
    $duplicate->set_description($product->get_description());
    $duplicate->set_short_description($product->get_short_description());
    $duplicate->set_category_ids($product->get_category_ids());
    $duplicate->set_status('draft');
    
    // Save and get new ID
    $new_id = $duplicate->save();
    
    if ($new_id) {
        wp_send_json_success([
            'message' => 'Product duplicated successfully',
            'new_id' => $new_id,
            'edit_url' => home_url('/b2b-panel/products/edit?id=' . $new_id)
        ]);
    } else {
        wp_send_json_error('Failed to duplicate product');
    }
});

/* =====================================================
   3B. B2B PRO SYSTEM v7.0 (Gelişmiş Ödeme İzinleri Matrisi)
   
   YENİLİKLER (ADIM 3):
   1. Grup Bazlı Ödeme İzinleri (Admin panelinde matris tablo).
   2. Müşteri Bazlı Ödeme İzinleri (Kullanıcı profilinden özel atama).
   3. Hiyerarşik Kontrol (User > Group > Guest).
===================================================== */

// ==========================================================================
// VERİTABANI VE AYARLAR
// ==========================================================================

function b2b_get_groups() { 
    return get_option('b2b_dynamic_groups', array()); 
}

function b2b_get_custom_fields() { 
    return get_option('b2b_custom_fields_def', array()); 
}

function b2b_get_standard_fields_config() { 
    return get_option('b2b_standard_fields_config', array()); 
}

function b2b_is_price_hidden_for_guests() { 
    return get_option('b2b_hide_prices_guest', 0); 
}

// Grup Ödeme Kurallarını Getir
function b2b_get_group_payment_rules() { 
    return get_option('b2b_group_payment_rules', array()); 
}

// ==========================================================================
// ADMIN MENÜLERİ
// ==========================================================================
function b2b_pro_admin_menu() {
    add_menu_page('B2B Paneli', 'B2B Paneli', 'manage_options', 'b2b-panel', 'b2b_page_approvals', 'dashicons-groups', 56);
    add_submenu_page('b2b-panel', 'Başvurular', 'Başvurular', 'manage_options', 'b2b-panel', 'b2b_page_approvals');
    add_submenu_page('b2b-panel', 'Gruplar & Üyeler', 'Gruplar & Üyeler', 'manage_options', 'b2b-groups-list', 'b2b_page_group_list');
    add_submenu_page('b2b-panel', 'Genel Ayarlar', 'Genel Ayarlar', 'manage_options', 'b2b-settings', 'b2b_page_settings');
    add_submenu_page('b2b-panel', 'Form Düzenleyici', 'Form Düzenleyici', 'manage_options', 'b2b-form-editor', 'b2b_page_form_editor');
    add_submenu_page('b2b-panel', 'Sales Agent', 'Sales Agent', 'manage_options', 'b2b-sales-agent', 'b2b_page_sales_agent_settings');
}
add_action('admin_menu', 'b2b_pro_admin_menu');

// ==========================================================================
// YARDIMCI FONKSİYONLAR
// ==========================================================================

/**
 * Kullanıcının B2B grubu slug'ını döner
 */
function b2b_get_user_group($user_id = 0) {
    if (!$user_id) $user_id = get_current_user_id();
    if (!$user_id) return '';
    return get_user_meta($user_id, 'b2b_group_slug', true);
}

/**
 * Kullanıcının veya grubunun bir ödeme yöntemine izni var mı?
 * Hiyerarşi: User > Group > Default
 */
function b2b_user_can_use_payment($user_id, $gateway_id) {
    // 1. Kullanıcı bazlı kontrol
    $user_payments = get_user_meta($user_id, 'b2b_allowed_payments', true);
    if (is_array($user_payments) && !empty($user_payments)) {
        return in_array($gateway_id, $user_payments);
    }
    
    // 2. Grup bazlı kontrol
    $group_slug = b2b_get_user_group($user_id);
    if ($group_slug) {
        $group_rules = b2b_get_group_payment_rules();
        if (isset($group_rules[$group_slug]) && is_array($group_rules[$group_slug])) {
            return in_array($gateway_id, $group_rules[$group_slug]);
        }
    }
    
    // 3. Default: Tüm ödeme yöntemlerine izin ver
    return true;
}

/**
 * Checkout'ta ödeme yöntemlerini filtrele
 */
add_filter('woocommerce_available_payment_gateways', function($gateways) {
    if (is_admin() || !is_checkout()) return $gateways;
    
    $user_id = get_current_user_id();
    if (!$user_id) return $gateways; // Misafir kullanıcılar için tüm yöntemler açık
    
    foreach ($gateways as $gateway_id => $gateway) {
        if (!b2b_user_can_use_payment($user_id, $gateway_id)) {
            unset($gateways[$gateway_id]);
        }
    }
    
    return $gateways;
});

/* =====================================================
   3C. B2B AUTOMATIC DISCOUNT SYSTEM (Price Calculation Hooks)
===================================================== */

/**
 * ==========================================================================
 * AUTOMATIC B2B GROUP DISCOUNT SYSTEM
 * Applies group-based discounts to WooCommerce products
 * ==========================================================================
 * 
 * Apply discount to product prices (ONLY sale price, not regular price)
 * This allows WooCommerce to show: ~~Regular Price~~ Sale Price
 */
add_filter('woocommerce_product_get_sale_price', function($price, $product) {
    $user_id = get_current_user_id();
    if (!$user_id) return $price;
    
    $group_slug = get_user_meta($user_id, 'b2b_group_slug', true);
    if (!$group_slug) return $price;
    
    $groups = b2b_get_groups();
    if (!isset($groups[$group_slug])) return $price;
    
    $discount = floatval($groups[$group_slug]['discount']);
    if ($discount <= 0) return $price;
    
    // Get the regular price for calculation
    $regular_price = $product->get_regular_price();
    if (!$regular_price) return $price;
    
    // Return discounted price as sale price
    return $regular_price * (1 - ($discount / 100));
}, 99, 2);

add_filter('woocommerce_product_get_price', function($price, $product) {
    // Check if we have a B2B discount (sale price)
    $sale_price = $product->get_sale_price();
    if ($sale_price && $sale_price > 0) {
        return $sale_price;
    }
    return $price;
}, 99, 2);

/**
 * Apply discount to variable product prices
 */
add_filter('woocommerce_product_variation_get_sale_price', function($price, $variation) {
    $user_id = get_current_user_id();
    if (!$user_id) return $price;
    
    $group_slug = get_user_meta($user_id, 'b2b_group_slug', true);
    if (!$group_slug) return $price;
    
    $groups = b2b_get_groups();
    if (!isset($groups[$group_slug])) return $price;
    
    $discount = floatval($groups[$group_slug]['discount']);
    if ($discount <= 0) return $price;
    
    $regular_price = $variation->get_regular_price();
    if (!$regular_price) return $price;
    
    return $regular_price * (1 - ($discount / 100));
}, 99, 2);

add_filter('woocommerce_product_variation_get_price', function($price, $variation) {
    $sale_price = $variation->get_sale_price();
    if ($sale_price && $sale_price > 0) {
        return $sale_price;
    }
    return $price;
}, 99, 2);

/**
 * Mark products as "on sale" when B2B discount applies
 */
add_filter('woocommerce_product_is_on_sale', function($on_sale, $product) {
    $user_id = get_current_user_id();
    if (!$user_id) return $on_sale;
    
    $group_slug = get_user_meta($user_id, 'b2b_group_slug', true);
    if (!$group_slug) return $on_sale;
    
    $groups = b2b_get_groups();
    if (!isset($groups[$group_slug])) return $on_sale;
    
    $discount = floatval($groups[$group_slug]['discount']);
    if ($discount > 0) {
        return true; // Mark as on sale to show strikethrough
    }
    
    return $on_sale;
}, 99, 2);

/**
 * Cart displays will automatically use the sale price from above hooks
 * No need for custom cart price display - WooCommerce handles it
 */

/**
 * Hide prices for guests (if enabled)
 */
add_filter('woocommerce_get_price_html', function($price_html, $product) {
    if (b2b_is_price_hidden_for_guests() && !is_user_logged_in()) {
        return '<a href="'.wp_login_url(get_permalink()).'" style="color:#3b82f6;font-weight:600;">Login to see price</a>';
    }
    
    // Add custom styling for B2B discounted prices
    $user_id = get_current_user_id();
    if ($user_id && $product->is_on_sale()) {
        $group_slug = get_user_meta($user_id, 'b2b_group_slug', true);
        if ($group_slug) {
            $groups = b2b_get_groups();
            if (isset($groups[$group_slug]) && floatval($groups[$group_slug]['discount']) > 0) {
                // Style the sale price in red
                $price_html = str_replace(
                    '<ins>',
                    '<ins><span style="color:#dc2626;font-weight:700;">',
                    $price_html
                );
                $price_html = str_replace(
                    '</ins>',
                    '</span></ins>',
                    $price_html
                );
            }
        }
    }
    
    return $price_html;
}, 10, 2);

/**
 * Enforce minimum order amount at checkout
 */
add_action('woocommerce_checkout_process', function() {
    $user_id = get_current_user_id();
    if (!$user_id) return; // Skip for guests
    
    $group_slug = get_user_meta($user_id, 'b2b_group_slug', true);
    if (!$group_slug) return; // No group assigned
    
    $groups = b2b_get_groups();
    if (!isset($groups[$group_slug])) return;
    
    $min_order = floatval($groups[$group_slug]['min_order']);
    if ($min_order <= 0) return; // No minimum set
    
    $cart_total = WC()->cart->get_subtotal();
    if ($cart_total < $min_order) {
        wc_add_notice(sprintf(
            'Your B2B group (%s) requires a minimum order of %s. Current cart total: %s',
            esc_html($groups[$group_slug]['name']),
            wc_price($min_order),
            wc_price($cart_total)
        ), 'error');
    }
});

/* =====================================================
   3D. WORDPRESS USER PROFILE INTEGRATION (Bidirectional Sync)
===================================================== */

/**
 * Add B2B fields to WordPress user profile
 */
function b2b_user_profile_fields($user) {
    if (!current_user_can('manage_options')) return; // Only admins can see/edit
    
    $groups = b2b_get_groups();
    $roles = get_option('b2b_roles', [
        ['slug' => 'customer', 'name' => 'Customer'],
        ['slug' => 'wholesaler', 'name' => 'Wholesaler'],
        ['slug' => 'retailer', 'name' => 'Retailer']
    ]);
    
    $user_group = get_user_meta($user->ID, 'b2b_group_slug', true);
    $user_role = get_user_meta($user->ID, 'b2b_role', true);
    $user_status = get_user_meta($user->ID, 'b2b_status', true);
    $user_payments = get_user_meta($user->ID, 'b2b_allowed_payments', true);
    
    $all_gateways = WC()->payment_gateways->payment_gateways();
    ?>
    <h2 style="margin-top:30px;border-bottom:2px solid #3b82f6;padding-bottom:10px;">B2B Customer Information</h2>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="b2b_group_slug">B2B Group</label></th>
            <td>
                <select name="b2b_group_slug" id="b2b_group_slug" class="regular-text">
                    <option value="">-- None --</option>
                    <?php foreach ($groups as $slug => $data): ?>
                        <option value="<?= esc_attr($slug) ?>" <?= selected($user_group, $slug) ?>>
                            <?= esc_html($data['name']) ?> (<?= $data['discount'] ?>% discount)
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Assign customer to a B2B pricing group for automatic discounts.</p>
            </td>
        </tr>
        <tr>
            <th><label for="b2b_role">B2B Role</label></th>
            <td>
                <select name="b2b_role" id="b2b_role" class="regular-text">
                    <option value="">-- None --</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= esc_attr($role['slug']) ?>" <?= selected($user_role, $role['slug']) ?>>
                            <?= esc_html($role['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <p class="description">Categorize the customer type (for reporting and filtering).</p>
            </td>
        </tr>
        <tr>
            <th><label for="b2b_status">B2B Status</label></th>
            <td>
                <select name="b2b_status" id="b2b_status" class="regular-text">
                    <option value="pending" <?= selected($user_status, 'pending') ?>>Pending Approval</option>
                    <option value="approved" <?= selected($user_status, 'approved') ?>>Approved</option>
                </select>
                <p class="description">Approval status for B2B access.</p>
            </td>
        </tr>
        <tr>
            <th><label>Allowed Payment Methods</label></th>
            <td>
                <?php foreach ($all_gateways as $gateway_id => $gateway): ?>
                    <label style="display:block;margin-bottom:5px;">
                        <input type="checkbox" name="b2b_allowed_payments[]" value="<?= esc_attr($gateway_id) ?>" 
                            <?= (is_array($user_payments) && in_array($gateway_id, $user_payments)) ? 'checked' : '' ?>>
                        <?= esc_html($gateway->get_title()) ?>
                    </label>
                <?php endforeach; ?>
                <p class="description">Restrict which payment methods this user can use at checkout.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'b2b_user_profile_fields');
add_action('edit_user_profile', 'b2b_user_profile_fields');

/**
 * Save B2B fields from WordPress user profile
 */
function b2b_save_user_profile_fields($user_id) {
    if (!current_user_can('manage_options')) return;
    
    if (isset($_POST['b2b_group_slug'])) {
        update_user_meta($user_id, 'b2b_group_slug', sanitize_text_field($_POST['b2b_group_slug']));
    }
    
    if (isset($_POST['b2b_role'])) {
        update_user_meta($user_id, 'b2b_role', sanitize_text_field($_POST['b2b_role']));
    }
    
    if (isset($_POST['b2b_status'])) {
        update_user_meta($user_id, 'b2b_status', sanitize_text_field($_POST['b2b_status']));
    }
    
    if (isset($_POST['b2b_allowed_payments'])) {
        $payments = array_map('sanitize_text_field', $_POST['b2b_allowed_payments']);
        update_user_meta($user_id, 'b2b_allowed_payments', $payments);
    } else {
        delete_user_meta($user_id, 'b2b_allowed_payments');
    }
}
add_action('personal_options_update', 'b2b_save_user_profile_fields');
add_action('edit_user_profile_update', 'b2b_save_user_profile_fields');

/* =====================================================
   4. AJAX HANDLERS
===================================================== */
// A. Order Details (Revize: Fotoğraflar ve Loglar aynı anda görünür)
add_action('wp_ajax_b2b_adm_get_details', function() {
    if (!current_user_can('manage_options')) wp_die();
    $oid = intval($_GET['order_id']);
    $order = wc_get_order($oid);
    if (!$order) wp_send_json_error('Order not found');

    // 1. Items
    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = [
            'name' => $item->get_name(),
            'sku'  => $item->get_product() ? $item->get_product()->get_sku() : '-',
            'qty'  => $item->get_quantity()
        ];
    }
    
    // 2. Customer Note
    $notes = $order->get_customer_note() ? '<div style="background:#fffbeb;color:#b45309;padding:12px;margin-bottom:15px;border-radius:6px;border:1px solid #fcd34d"><strong><i class="fa-solid fa-note-sticky"></i> Customer Note:</strong><br>'.$order->get_customer_note().'</div>' : '';
    
    // 3. WAREHOUSE OPS DATA (POD - Fotoğraf & Teslimat)
    $photos   = get_post_meta($oid, '_delivery_photos', true);
    $del_to   = get_post_meta($oid, '_delivered_to', true);
    $del_by   = get_post_meta($oid, '_delivered_by', true);
    $del_time = get_post_meta($oid, '_delivery_time', true);
    
    $ops_html = '';
    
    // Fotoğraf veya teslimat bilgisi varsa göster
    if ($photos || $del_to) {
        $ops_html .= '<div style="margin-top:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">';
        $ops_html .= '<h4 style="margin:0 0 15px 0;color:#0f172a;font-size:14px;border-bottom:1px solid #e2e8f0;padding-bottom:10px;text-transform:uppercase"><i class="fa-solid fa-camera"></i> Warehouse Delivery Proof (POD)</h4>';
        
        $ops_html .= '<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:15px;margin-bottom:15px;font-size:13px;color:#334155">';
        if($del_by)   $ops_html .= '<div><strong>Staff:</strong> <br>'.esc_html($del_by).'</div>';
        if($del_to)   $ops_html .= '<div><strong>Delivered To:</strong> <br>'.esc_html($del_to).'</div>';
        if($del_time) $ops_html .= '<div><strong>Time:</strong> <br>'.esc_html($del_time).'</div>';
        $ops_html .= '</div>';

        if ($photos && is_array($photos)) {
            $ops_html .= '<div><strong>Photos:</strong><div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:8px">';
            foreach ($photos as $url) {
                $ops_html .= '<a href="'.esc_url($url).'" target="_blank"><img src="'.esc_url($url).'" style="width:100px;height:100px;object-fit:cover;border-radius:6px;border:1px solid #cbd5e1;box-shadow:0 2px 4px rgba(0,0,0,0.1);transition:0.2s"></a>';
            }
            $ops_html .= '</div></div>';
        }
        $ops_html .= '</div>';
    } 
    // Eğer hiçbiri yoksa boş bir mesaj gösterebiliriz veya boş bırakabiliriz
    else {
        $ops_html = '<div style="margin-top:20px;padding:15px;text-align:center;color:#94a3b8;font-style:italic;border:1px dashed #e2e8f0;border-radius:8px">No delivery info uploaded yet.</div>';
    }

    // 4. WAREHOUSE LOGS (Approval Notes) - BAĞIMSIZ BLOK
    // Artık else içinde değil, her durumda kontrol edilir.
    $wh_a = get_post_meta($oid, '_warehouse_a_notes', true);
    $wh_b = get_post_meta($oid, '_warehouse_b_notes', true);
    $logs_html = '';

    if($wh_a || $wh_b) {
        $logs_html = '<div style="background:#f1f5f9;padding:15px;margin-top:15px;font-size:12px;border-radius:6px;color:#475569;border:1px solid #e2e8f0;"><strong><i class="fa-solid fa-clock-rotate-left"></i> Warehouse Logs:</strong><br><div style="white-space:pre-wrap;margin-top:5px">'.esc_html(trim($wh_a . "\n" . $wh_b)).'</div></div>';
    }
    
    // 5. QUICKBOOKS SYNC STATUS - NEW SECTION
    $qb_invoice = get_post_meta($oid, '_qbo_invoice_id', true) 
               ?: get_post_meta($oid, '_quickbooks_invoice_id', true);
    $qb_invoice_num = get_post_meta($oid, '_qbo_invoice_number', true);
    $qb_sync_date = get_post_meta($oid, '_qbo_sync_date', true) 
                 ?: get_post_meta($oid, '_quickbooks_sync_date', true);
    $qb_synced = get_post_meta($oid, '_qbo_synced', true) 
              ?: get_post_meta($oid, 'myworks_qbo_synced', true);
    $qb_sync_status = get_post_meta($oid, '_qbo_sync_status', true);
    
    // Determine sync status
    $is_synced = false;
    if ($qb_invoice || $qb_synced == '1' || $qb_synced == 'yes' || $qb_sync_status == 'synced') {
        $is_synced = true;
    }
    
    $qb_html = '';
    if ($is_synced || $qb_invoice || $qb_sync_date) {
        $status_color = $is_synced ? '#10b981' : '#ef4444';
        $status_icon = $is_synced ? 'fa-circle-check' : 'fa-circle-xmark';
        $status_text = $is_synced ? 'Synced' : 'Not Synced';
        
        $qb_html = '<div style="margin-top:20px;background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:20px;">';
        $qb_html .= '<h4 style="margin:0 0 15px 0;color:#0c4a6e;font-size:14px;border-bottom:1px solid #bae6fd;padding-bottom:10px;text-transform:uppercase">';
        $qb_html .= '<i class="fa-solid fa-book"></i> QuickBooks Sync Status</h4>';
        
        $qb_html .= '<div style="display:grid;grid-template-columns:140px 1fr;gap:12px;font-size:13px;color:#334155">';
        
        // Status
        $qb_html .= '<div style="font-weight:600">Status:</div>';
        $qb_html .= '<div style="display:flex;align-items:center;gap:6px;color:'.$status_color.';font-weight:600">';
        $qb_html .= '<i class="fa-solid '.$status_icon.'"></i> '.$status_text.'</div>';
        
        // Invoice ID
        if ($qb_invoice) {
            $qb_html .= '<div style="font-weight:600">Invoice ID:</div>';
            $qb_html .= '<div style="font-family:monospace;color:#0c4a6e">'.esc_html($qb_invoice).'</div>';
        }
        
        // Invoice Number
        if ($qb_invoice_num) {
            $qb_html .= '<div style="font-weight:600">Invoice Number:</div>';
            $qb_html .= '<div style="font-family:monospace;color:#0c4a6e">'.esc_html($qb_invoice_num).'</div>';
        }
        
        // Sync Date
        if ($qb_sync_date) {
            $formatted_date = is_numeric($qb_sync_date) 
                ? date('d.m.Y H:i', $qb_sync_date) 
                : date('d.m.Y H:i', strtotime($qb_sync_date));
            $qb_html .= '<div style="font-weight:600">Last Sync:</div>';
            $qb_html .= '<div>'.esc_html($formatted_date).'</div>';
        }
        
        $qb_html .= '</div></div>';
    } else {
        // Not synced - show info box
        $qb_html = '<div style="margin-top:20px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:15px;text-align:center;">';
        $qb_html .= '<i class="fa-solid fa-circle-xmark" style="font-size:24px;color:#94a3b8;display:block;margin-bottom:10px"></i>';
        $qb_html .= '<span style="color:#64748b;font-size:13px">Not synced to QuickBooks yet</span>';
        $qb_html .= '</div>';
    }

    wp_send_json_success([
        'id' => $order->get_id(),
        'date' => $order->get_date_created()->date('d.m.Y H:i'),
        'billing' => $order->get_formatted_billing_address() ?: 'No address',
        'shipping' => $order->get_formatted_shipping_address() ?: 'No address',
        'items' => $items,
        'grand_total' => $order->get_formatted_order_total(),
        // Notlar + Teslimat Bilgisi + Loglar + QB Status (Hepsi birleşti)
        'extra_html' => $notes . $ops_html . $logs_html . $qb_html
    ]);
});

// B. Update Status
add_action('wp_ajax_b2b_adm_update_status', function(){
    if (!current_user_can('manage_options')) wp_die();
    $order = wc_get_order(intval($_POST['order_id']));
    if($order) {
        $order->update_status(sanitize_text_field($_POST['status']), 'Admin Panel Update');
        wp_send_json_success();
    }
    wp_send_json_error();
});

// C. Warehouse Approval Override
add_action('wp_ajax_b2b_adm_wh_update', function(){
    if (!current_user_can('manage_options')) wp_die();
    $oid = intval($_POST['order_id']);
    $wh = sanitize_text_field($_POST['warehouse']);
    $note = sanitize_textarea_field($_POST['note']);
    
    $current = get_post_meta($oid, '_'.$wh.'_approved', true);
    $new = ($current === '1') ? '0' : '1';
    update_post_meta($oid, '_'.$wh.'_approved', $new);
    
    if($note) {
        $old = get_post_meta($oid, '_'.$wh.'_notes', true);
        update_post_meta($oid, '_'.$wh.'_notes', $old . "\n" . date('d.m H:i') . ' (Admin): ' . $note);
    }
    wp_send_json_success(['new_state' => ($new === '1')]);
});

/* =====================================================
   5. UI: HEADER & CSS
===================================================== */
function b2b_adm_header($title) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $title ?> | Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        /* Hope UI Design System - Refined & Optimized */
        :root{
            --primary:#8f5fe8;
            --primary-dark:#7239ea;
            --primary-light:#a78bfa;
            --accent:#3a57e8;
            --accent-light:#4f7aed;
            --success:#0abb87;
            --success-light:#e0f7f0;
            --warning:#ffbb33;
            --warning-light:#fff8e6;
            --danger:#ea6a12;
            --danger-light:#ffeee6;
            --info:#00cfe8;
            --info-light:#e0f7fd;
            --bg:#f8f9fa;
            --bg-light:#ffffff;
            --white:#ffffff;
            --border:#e5e7eb;
            --border-light:#f0f1f3;
            --text:#1f2937;
            --text-light:#6c757d;
            --text-muted:#9ca3af;
            --sidebar-bg:#1e2139;
            --sidebar-hover:#292d47;
            --shadow-sm:0 1px 2px 0 rgba(0,0,0,0.05);
            --shadow:0 4px 6px -1px rgba(0,0,0,0.1);
            --shadow-lg:0 10px 15px -3px rgba(0,0,0,0.1);
            --shadow-xl:0 20px 25px -5px rgba(0,0,0,0.1);
        }
        body{margin:0;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI','Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:14px;line-height:1.6}
        
        .sidebar{width:260px;background:var(--sidebar-bg);color:#9ca3af;flex-shrink:0;position:fixed;height:100%;z-index:100;display:flex;flex-direction:column;box-shadow:0 0 20px rgba(0,0,0,0.1);transition:width 0.3s ease;left:0}
        .sidebar.collapsed{width:80px}
        .sidebar-head{padding:25px;color:var(--white);font-weight:700;font-size:1.2rem;border-bottom:1px solid rgba(255,255,255,0.1);background:linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);display:flex;align-items:center;justify-content:space-between;transition:padding 0.3s ease}
        .sidebar.collapsed .sidebar-head{padding:25px 10px;justify-content:center}
        .sidebar-head-title{transition:opacity 0.2s ease;white-space:nowrap}
        .sidebar.collapsed .sidebar-head-title{opacity:0;width:0;overflow:hidden}
        .sidebar-toggle{background:transparent;border:none;color:var(--white);font-size:18px;cursor:pointer;padding:8px;transition:transform 0.3s ease;display:flex;align-items:center;justify-content:center;flex-shrink:0}
        .sidebar-toggle:hover{background:rgba(255,255,255,0.1);border-radius:6px}
        .sidebar.collapsed .sidebar-toggle{transform:rotate(180deg)}
        .sidebar-nav{padding:20px 10px;flex:1;overflow-y:auto;overflow-x:visible}
        .sidebar-nav a, .submenu-toggle{display:flex;align-items:center;gap:12px;padding:12px 15px;color:inherit;text-decoration:none;border-radius:8px;margin-bottom:5px;transition:all 0.3s ease;white-space:nowrap;position:relative;cursor:pointer}
        .sidebar-nav a i, .submenu-toggle > i:first-child{min-width:20px;text-align:center;font-size:18px}
        .sidebar-nav a .menu-text, .submenu-toggle .menu-text{transition:opacity 0.2s ease}
        .sidebar.collapsed .sidebar-nav a .menu-text, .sidebar.collapsed .submenu-toggle .menu-text{opacity:0;width:0;overflow:hidden}
        .sidebar.collapsed .sidebar-nav a, .sidebar.collapsed .submenu-toggle{padding:12px;justify-content:center;position:relative}
        .sidebar.collapsed .sidebar-nav a i, .sidebar.collapsed .submenu-toggle > i:first-child{margin:0}
        .sidebar.collapsed .submenu-toggle i.fa-chevron-down{display:none}
        .sidebar.collapsed .submenu{display:none !important}
        .sidebar-nav a:hover{background:var(--sidebar-hover);color:var(--white);transform:translateX(5px)}
        .sidebar.collapsed .sidebar-nav a:hover, .sidebar.collapsed .submenu-toggle:hover{transform:none}
        .sidebar-nav a.active{background:linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);color:var(--white);box-shadow:0 4px 12px rgba(138,95,232,0.3)}
        .main{margin-left:260px;flex:1;padding:40px;width:calc(100% - 260px);transition:all 0.3s ease;box-sizing:border-box}
        body.sidebar-collapsed .main{margin-left:80px;width:calc(100% - 80px)}
        
        /* Collapsed Sidebar Tooltip & Hover Menu */
        .sidebar.collapsed .sidebar-nav a::after, .sidebar.collapsed .submenu-toggle::after{content:attr(data-title);position:absolute;left:100%;top:50%;transform:translateY(-50%);background:rgba(0,0,0,0.9);color:#fff;padding:8px 12px;border-radius:6px;font-size:13px;white-space:nowrap;margin-left:10px;opacity:0;pointer-events:none;transition:opacity 0.2s ease;z-index:1000}
        .sidebar.collapsed .sidebar-nav a:hover::after, .sidebar.collapsed .submenu-toggle:hover::after{opacity:1}
        
        /* Collapsed Sidebar - Hover Submenu */
        .sidebar.collapsed .submenu{display:none !important;position:absolute;left:100%;background:var(--sidebar-bg);min-width:200px;border-radius:8px;box-shadow:0 8px 24px rgba(0,0,0,0.3);padding:10px;margin-left:10px;z-index:1001}
        .sidebar.collapsed .submenu.show{display:block !important}
        .sidebar.collapsed .submenu a{display:block;padding:12px 15px;margin-bottom:5px}
        
        .card{background:var(--white);border-radius:16px;box-shadow:var(--shadow);padding:28px;border:1px solid var(--border-light);margin-bottom:25px;transition:all 0.3s ease}
        .card:hover{box-shadow:var(--shadow-lg);border-color:var(--border)}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:32px;padding-bottom:16px;border-bottom:2px solid var(--border-light)}
        .page-title{font-size:28px;font-weight:700;background:linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin:0;letter-spacing:-0.5px}
        
        input,select,textarea{width:100%;padding:12px;border:1px solid #e0e0e0;border-radius:8px;font-size:14px;box-sizing:border-box;margin-bottom:15px;transition:border-color 0.3s ease}
        input:focus,select:focus,textarea:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(138,95,232,0.1)}
        button{background:linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);color:var(--white);border:none;padding:12px 24px;border-radius:8px;cursor:pointer;font-weight:600;font-size:14px;transition:all 0.3s ease;box-shadow:0 4px 12px rgba(138,95,232,0.3)}
        button:hover{transform:translateY(-2px);box-shadow:0 6px 16px rgba(138,95,232,0.4)}
        button.secondary{background:var(--white);border:1px solid #e0e0e0;color:#374151;box-shadow:none}
        button.secondary:hover{background:#f9fafb;transform:none}
        
        table{width:100%;border-collapse:collapse;font-size:13px;background:var(--white)}
        th{background:linear-gradient(180deg, #f9fafb 0%, #f3f4f6 100%);padding:14px 12px;text-align:left;font-weight:600;color:#4b5563;border-bottom:2px solid var(--border);text-transform:uppercase;font-size:11px;letter-spacing:0.5px}
        td{padding:14px 12px;border-bottom:1px solid var(--border-light);vertical-align:middle;color:var(--text)}
        tr:hover td{background:var(--bg);transition:background 0.2s ease}
        
        /* Stats Box - Enhanced */
        .stats-box {background:linear-gradient(135deg, #e0f2fe 0%, #dbeafe 100%);border:1px solid #bae6fd;color:#0c4a6e;padding:20px;border-radius:12px;margin-bottom:24px;display:flex;align-items:center;gap:32px;box-shadow:var(--shadow-sm)}
        .stat-item {display:flex;flex-direction:column;gap:4px}
        .stat-label {font-size:11px;text-transform:uppercase;color:#0284c7;font-weight:700;letter-spacing:0.5px}
        .stat-val {font-size:24px;font-weight:700;line-height:1;color:#0c4a6e}
        .stat-oldest {color:#dc2626}
        
        /* Badge Styles */
        .badge {display:inline-flex;align-items:center;padding:4px 12px;border-radius:6px;font-size:12px;font-weight:600;line-height:1}
        .badge-success {background:var(--success-light);color:var(--success)}
        .badge-warning {background:var(--warning-light);color:#d97706}
        .badge-danger {background:var(--danger-light);color:var(--danger)}
        .badge-info {background:var(--info-light);color:#0284c7}
        
        /* Mobile Responsive Tables */
        .table-responsive {overflow-x:auto;-webkit-overflow-scrolling:touch;margin-bottom:20px;border-radius:8px}
        @media (max-width:768px) {
            .table-responsive table{min-width:800px}
        }
        
        /* Pagination Styles */
        .pagination{margin-top:20px;display:flex;gap:8px;justify-content:center;flex-wrap:wrap}
        .pagination a,.pagination span{padding:10px 16px;border:1px solid var(--border);border-radius:8px;text-decoration:none;color:var(--text);transition:all 0.3s ease;font-weight:500}
        .pagination a:hover{background:var(--bg);border-color:var(--primary);color:var(--primary);transform:translateY(-2px)}
        .pagination span.current,.pagination a.active{background:linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);color:var(--white);border-color:transparent;box-shadow:0 4px 12px rgba(138,95,232,0.3)}
        .pagination .prev,.pagination .next{font-weight:600}

        /* Column Edit Dropdown */
        .col-toggler { position:relative; display:inline-block; }
        .col-dropdown { display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid #ddd; box-shadow:0 4px 6px rgba(0,0,0,0.1); padding:10px; z-index:99; min-width:150px; border-radius:6px; }
        .col-dropdown.active { display:block; }
        .col-dropdown label { display:block; padding:5px 0; cursor:pointer; font-weight:normal; }
        .col-dropdown input { width:auto; margin-right:8px; }
        
        /* Multi-Select Dropdown for Order Status */
        .multi-select-wrapper {
            position: relative;
            width: 100%;
        }
        .multi-select-display {
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: border-color 0.3s ease;
            min-height: 45px;
        }
        .multi-select-display:hover {
            border-color: var(--primary);
        }
        .multi-select-display.active {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(138,95,232,0.1);
        }
        .selected-items {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            flex: 1;
        }
        .selected-item-badge {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .selected-item-badge .remove {
            cursor: pointer;
            font-weight: bold;
            opacity: 0.8;
        }
        .selected-item-badge .remove:hover {
            opacity: 1;
        }
        .multi-select-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
            margin-top: 8px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }
        .multi-select-dropdown.active {
            display: block;
        }
        .multi-select-option {
            padding: 10px 12px;
            cursor: pointer;
            transition: background 0.2s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .multi-select-option:hover {
            background: #f8f9fa;
        }
        .multi-select-option input[type="checkbox"] {
            width: auto;
            margin: 0;
        }
        .multi-select-option label {
            cursor: pointer;
            margin: 0;
            flex: 1;
        }

        /* Dashboard Widgets */
        .dash-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:20px}
        .dash-card{background:var(--white);border:1px solid var(--border);border-radius:10px;padding:20px;display:flex;flex-direction:column;justify-content:space-between;height:120px;text-decoration:none;color:inherit;transition:0.2s}
        .dash-card:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,0.05)}
        .dash-card.warning{border-color:#fca5a5;background:#fef2f2}
        .dash-card.warning .dash-label{color:#ef4444}

        /* Modal */
        .modal{display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;backdrop-filter:blur(2px)}
        .modal-content{background:var(--white);width:95%;max-width:750px;border-radius:12px;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1)}
        
        /* Tooltip for collapsed sidebar */
        .sidebar.collapsed .sidebar-nav a::after, .sidebar.collapsed .submenu-toggle::after{
            content:attr(data-title);
            position:absolute;
            left:calc(100% + 15px);
            top:50%;
            transform:translateY(-50%);
            background:var(--primary);
            color:white;
            padding:8px 12px;
            border-radius:6px;
            font-size:13px;
            white-space:nowrap;
            opacity:0;
            pointer-events:none;
            transition:opacity 0.2s ease;
            z-index:1000;
            box-shadow:0 4px 12px rgba(0,0,0,0.2)}
        .sidebar.collapsed .sidebar-nav a::before, .sidebar.collapsed .submenu-toggle::before{
            content:'';
            position:absolute;
            left:calc(100% + 7px);
            top:50%;
            transform:translateY(-50%);
            border:8px solid transparent;
            border-right-color:var(--primary);
            opacity:0;
            pointer-events:none;
            transition:opacity 0.2s ease;
            z-index:1000}
        .sidebar.collapsed .sidebar-nav a:hover::after, .sidebar.collapsed .submenu-toggle:hover::after,
        .sidebar.collapsed .sidebar-nav a:hover::before, .sidebar.collapsed .submenu-toggle:hover::before{opacity:1}
        
        /* B2B Module Submenu */
        .submenu-toggle{display:flex;align-items:center;gap:12px;padding:12px 15px;color:inherit;border-radius:8px;margin-bottom:5px;transition:0.2s;cursor:pointer;user-select:none;}
        .submenu-toggle:hover{background:rgba(255,255,255,0.1);color:var(--white);}
        .submenu-toggle.active{background:rgba(255,255,255,0.1);color:var(--white);}
        .submenu-toggle i.fa-chevron-down{transition:transform 0.3s;font-size:10px;margin-left:auto;}
        .submenu-toggle.active i.fa-chevron-down{transform:rotate(180deg);}
        .submenu{max-height:0;overflow:hidden;transition:max-height 0.4s ease;padding-left:15px;}
        .submenu.active{max-height:500px;}
        .submenu a{padding:10px 15px;font-size:13px;margin-bottom:3px;}
        
        /* Customer Detail Sections */
        .customer-section{background:var(--white);border-radius:12px;padding:20px;margin-bottom:20px;border:1px solid var(--border);}
        .customer-section h3{margin:0 0 15px 0;padding-bottom:10px;border-bottom:2px solid var(--border);color:var(--primary);font-size:16px;}
        .form-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(250px, 1fr));gap:15px;}
        
        /* Mobile Menu Toggle Button */
        .mobile-menu-toggle{display:none;position:fixed;top:0;left:0;z-index:1002;background:var(--primary);color:white;border:none;padding:0;width:56px;height:56px;cursor:pointer;font-size:20px;box-shadow:0 2px 12px rgba(0,0,0,0.2);border-bottom-right-radius:16px;align-items:center;justify-content:center}
        .mobile-menu-toggle:active{background:#1e293b;transform:scale(0.95)}
        
        /* Mobile Header Bar */
        .mobile-header{display:none;position:fixed;top:0;left:0;right:0;height:56px;background:var(--white);border-bottom:1px solid var(--border);z-index:1000;box-shadow:0 2px 8px rgba(0,0,0,0.05)}
        .mobile-header .page-title{margin:0;padding:0 70px;line-height:56px;font-size:18px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;text-align:center}
        
        /* Sidebar Overlay for Mobile */
        .sidebar-overlay{display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.6);z-index:999;backdrop-filter:blur(3px);opacity:0;transition:opacity 0.3s ease}
        .sidebar-overlay.active{display:block;opacity:1}
        
        /* Responsive Tables */
        .table-responsive{overflow-x:auto;-webkit-overflow-scrolling:touch;margin:0}
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .sidebar{width:240px;}
            .main{margin-left:240px;padding:30px;}
            .dash-grid{grid-template-columns:repeat(auto-fill, minmax(180px, 1fr));}
        }
        @media (max-width: 992px) {
            .sidebar{width:220px;}
            .main{margin-left:220px;padding:25px;}
            .page-title{font-size:20px;}
            .form-grid{grid-template-columns:1fr;}
        }
        @media (max-width: 768px) {
            .mobile-menu-toggle{display:flex}
            .mobile-header{display:block}
            body{flex-direction:column;overflow-x:hidden}
            .sidebar{width:75%;max-width:300px;height:100vh;position:fixed;left:0;top:0;z-index:1001;transform:translateX(-100%);transition:transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);box-shadow:4px 0 24px rgba(0,0,0,0.15);overflow-y:auto}
            .sidebar.collapsed{width:75%;max-width:300px}
            .sidebar.mobile-open{transform:translateX(0)}
            .sidebar-toggle{display:none}
            .sidebar-nav{padding:80px 15px 20px 15px;display:block;overflow-x:hidden}
            .sidebar-nav a, .submenu-toggle{flex:initial;min-width:initial;width:100%;padding:14px 16px;margin-bottom:4px;border-radius:8px}
            .sidebar-nav a .menu-text, .submenu-toggle .menu-text{opacity:1 !important;width:auto !important;overflow:visible !important}
            .sidebar.collapsed .sidebar-nav a, .sidebar.collapsed .submenu-toggle{padding:14px 16px;justify-content:flex-start}
            .sidebar.collapsed .submenu-toggle i.fa-chevron-down{display:inline-block !important}
            .sidebar.collapsed .submenu{display:block !important;position:static;background:transparent;min-width:auto;border-radius:0;box-shadow:none;padding:0;margin-left:0}
            .sidebar.collapsed .sidebar-nav a::after, .sidebar.collapsed .submenu-toggle::after{display:none}
            .submenu{padding-left:20px}
            .submenu a{padding:10px 16px;font-size:14px}
            .main{margin-left:0;padding:16px;padding-top:72px;width:100%;max-width:100%;overflow-x:hidden}
            body.sidebar-collapsed .main{margin-left:0;width:100%}
            .page-header{flex-direction:column;align-items:stretch;gap:12px;margin-bottom:16px}
            .page-header .page-title{display:none}
            .page-header button,.page-header a{width:100%;margin:0}
            .dash-grid{grid-template-columns:repeat(2, 1fr);gap:12px}
            .table-responsive{overflow-x:auto}
            table{font-size:12px;min-width:650px}
            th,td{padding:10px 8px}
            .stats-box{flex-wrap:wrap;gap:12px}
            .modal-content{width:calc(100% - 32px);max-width:none;border-radius:12px;margin:16px}
            #bulkEditPanel{padding:16px}
            #bulkEditPanel > div{grid-template-columns:1fr !important;gap:16px}
            .bulk-section{padding:16px}
            .card{padding:16px;margin-bottom:16px}
            .customer-section{padding:16px;margin-bottom:16px}
        }
        @media (max-width: 480px) {
            .main{padding:12px;padding-top:68px}
            .card, .customer-section{padding:12px;margin-bottom:12px}
            .stats-box{flex-direction:column;gap:10px}
            button{padding:12px 16px;font-size:14px;width:100%}
            .dash-grid{grid-template-columns:1fr;gap:12px}
            table{font-size:11px;min-width:550px}
            th,td{padding:8px 6px}
            input,select,textarea{font-size:16px;padding:12px}
            .modal-content{margin:12px;width:calc(100% - 24px)}
            .sidebar{width:85%;max-width:280px}
            .sidebar-nav{padding:70px 12px 16px 12px}
            .sidebar-nav a, .submenu-toggle{padding:12px 14px}
            #bulkEditPanel{padding:12px}
            .bulk-section{padding:12px}
            .page-header{gap:10px}
        }
    </style>
    </head>
    <body>

    <!-- Mobile Menu Toggle Button -->
    <button class="mobile-menu-toggle" onclick="toggleMobileMenu()" aria-label="Toggle Menu">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Mobile Header Bar -->
    <div class="mobile-header">
        <div class="page-title" id="mobilePageTitle">Admin Panel</div>
    </div>

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" onclick="toggleMobileMenu()"></div>

    <div class="sidebar">
        <div class="sidebar-head">
            <span class="sidebar-head-title"><i class="fa-solid fa-shield-halved"></i> ADMIN PANEL V10</span>
            <button class="sidebar-toggle" onclick="toggleSidebar()" title="Toggle Sidebar">
                <i class="fa-solid fa-angles-left"></i>
            </button>
        </div>
        <div class="sidebar-nav">
            <a href="<?= home_url('/b2b-panel') ?>" class="<?= get_query_var('b2b_adm_page')=='dashboard'?'active':'' ?>" data-title="Dashboard">
                <i class="fa-solid fa-chart-pie"></i> <span class="menu-text">Dashboard</span>
            </a>
            <a href="<?= home_url('/b2b-panel/orders') ?>" class="<?= get_query_var('b2b_adm_page')=='orders'?'active':'' ?>" data-title="Orders">
                <i class="fa-solid fa-box"></i> <span class="menu-text">Orders</span>
            </a>
            <a href="<?= home_url('/b2b-panel/reports') ?>" class="<?= get_query_var('b2b_adm_page')=='reports'?'active':'' ?>" data-title="Reports">
                <i class="fa-solid fa-chart-line"></i> <span class="menu-text">Reports</span>
            </a>
            
            <!-- Stock Planning Module with Submenu -->
            <div class="submenu-toggle <?= in_array(get_query_var('b2b_adm_page'), ['stock_planning','supplier_orders'])?'active':'' ?>" onclick="toggleSubmenu(this)" data-title="Stock Planning">
                <i class="fa-solid fa-boxes-stacked"></i> <span class="menu-text">Stock Planning</span> <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="submenu <?= in_array(get_query_var('b2b_adm_page'), ['stock_planning','supplier_orders'])?'active':'' ?>">
                <a href="<?= home_url('/b2b-panel/stock-planning') ?>" class="<?= get_query_var('b2b_adm_page')=='stock_planning'?'active':'' ?>" data-title="Sales Analysis">
                    <i class="fa-solid fa-chart-gantt"></i> <span class="menu-text">Sales Analysis</span>
                </a>
                <a href="<?= home_url('/b2b-panel/stock-planning/supplier-orders') ?>" class="<?= get_query_var('b2b_adm_page')=='supplier_orders'?'active':'' ?>" data-title="Supplier Orders">
                    <i class="fa-solid fa-truck-ramp-box"></i> <span class="menu-text">Supplier Orders</span>
                </a>
            </div>
            
            <a href="<?= home_url('/b2b-panel/activity-log') ?>" class="<?= get_query_var('b2b_adm_page')=='activity_log'?'active':'' ?>" data-title="Activity Log">
                <i class="fa-solid fa-clipboard-list"></i> <span class="menu-text">Activity Log</span>
            </a>
            
            <!-- Products Module with Submenu -->
            <div class="submenu-toggle <?= in_array(get_query_var('b2b_adm_page'), ['products','product_edit','product_add_new','products_import','products_export','products_categories','category_edit','price_adjuster'])?'active':'' ?>" onclick="toggleSubmenu(this)" data-title="Products">
                <i class="fa-solid fa-tags"></i> <span class="menu-text">Products</span> <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="submenu <?= in_array(get_query_var('b2b_adm_page'), ['products','product_edit','product_add_new','products_import','products_export','products_categories','category_edit','price_adjuster'])?'active':'' ?>">
                <a href="<?= home_url('/b2b-panel/products') ?>" class="<?= get_query_var('b2b_adm_page')=='products'||get_query_var('b2b_adm_page')=='product_edit'||get_query_var('b2b_adm_page')=='product_add_new'?'active':'' ?>" data-title="All Products">
                    <i class="fa-solid fa-list"></i> <span class="menu-text">All Products</span>
                </a>
                <a href="<?= home_url('/b2b-panel/products/categories') ?>" class="<?= get_query_var('b2b_adm_page')=='products_categories'||get_query_var('b2b_adm_page')=='category_edit'?'active':'' ?>" data-title="Categories">
                    <i class="fa-solid fa-folder-tree"></i> <span class="menu-text">Categories</span>
                </a>
                <a href="<?= home_url('/b2b-panel/products/price-adjuster') ?>" class="<?= get_query_var('b2b_adm_page')=='price_adjuster'?'active':'' ?>" data-title="Price Adjuster">
                    <i class="fa-solid fa-dollar-sign"></i> <span class="menu-text">Price Adjuster</span>
                </a>
                <a href="<?= home_url('/b2b-panel/products/import') ?>" class="<?= get_query_var('b2b_adm_page')=='products_import'?'active':'' ?>" data-title="Import">
                    <i class="fa-solid fa-file-import"></i> <span class="menu-text">Import</span>
                </a>
                <a href="<?= home_url('/b2b-panel/products/export') ?>" class="<?= get_query_var('b2b_adm_page')=='products_export'?'active':'' ?>" data-title="Export">
                    <i class="fa-solid fa-file-export"></i> <span class="menu-text">Export</span>
                </a>
            </div>
            
            <a href="<?= home_url('/b2b-panel/customers') ?>" class="<?= get_query_var('b2b_adm_page')=='customers'||get_query_var('b2b_adm_page')=='customer_edit'?'active':'' ?>" data-title="Customers">
                <i class="fa-solid fa-users"></i> <span class="menu-text">Customers</span>
            </a>
            
            <!-- B2B Module with Submenu -->
            <div class="submenu-toggle <?= in_array(get_query_var('b2b_adm_page'), ['b2b_approvals','b2b_groups','b2b_settings','b2b_form_editor','b2b_roles'])?'active':'' ?>" onclick="toggleSubmenu(this)" data-title="B2B Module">
                <i class="fa-solid fa-layer-group"></i> <span class="menu-text">B2B Module</span> <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="submenu <?= in_array(get_query_var('b2b_adm_page'), ['b2b_approvals','b2b_groups','b2b_settings','b2b_form_editor','b2b_roles'])?'active':'' ?>">
                <a href="<?= home_url('/b2b-panel/b2b-module') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_approvals'?'active':'' ?>" data-title="Approvals">
                    <i class="fa-solid fa-user-check"></i> <span class="menu-text">Approvals</span>
                </a>
                <a href="<?= home_url('/b2b-panel/b2b-module/groups') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_groups'?'active':'' ?>" data-title="Groups">
                    <i class="fa-solid fa-users-gear"></i> <span class="menu-text">Groups</span>
                </a>
                <a href="<?= home_url('/b2b-panel/b2b-module/roles') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_roles'?'active':'' ?>" data-title="Roles">
                    <i class="fa-solid fa-user-tag"></i> <span class="menu-text">Roles</span>
                </a>
                <a href="<?= home_url('/b2b-panel/b2b-module/settings') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_settings'?'active':'' ?>" data-title="Settings">
                    <i class="fa-solid fa-sliders"></i> <span class="menu-text">Settings</span>
                </a>
                <a href="<?= home_url('/b2b-panel/b2b-module/form-editor') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_form_editor'?'active':'' ?>" data-title="Form Editor">
                    <i class="fa-solid fa-pen-to-square"></i> <span class="menu-text">Form Editor</span>
                </a>
            </div>
            
            <!-- Settings Module with Submenu -->
            <div class="submenu-toggle <?= in_array(get_query_var('b2b_adm_page'), ['settings_general','settings_tax','settings_shipping','shipping_zone_edit','settings_sales_agent','settings_payments'])?'active':'' ?>" onclick="toggleSubmenu(this)" data-title="Settings">
                <i class="fa-solid fa-gear"></i> <span class="menu-text">Settings</span> <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="submenu <?= in_array(get_query_var('b2b_adm_page'), ['settings_general','settings_tax','settings_shipping','shipping_zone_edit','settings_sales_agent','settings_payments'])?'active':'' ?>">
                <a href="<?= home_url('/b2b-panel/settings') ?>" class="<?= get_query_var('b2b_adm_page')=='settings_general'?'active':'' ?>" data-title="General">
                    <i class="fa-solid fa-sliders"></i> <span class="menu-text">General</span>
                </a>
                <a href="<?= home_url('/b2b-panel/settings/tax-exemption') ?>" class="<?= get_query_var('b2b_adm_page')=='settings_tax'?'active':'' ?>" data-title="Tax Exemption">
                    <i class="fa-solid fa-receipt"></i> <span class="menu-text">Tax Exemption</span>
                </a>
                <a href="<?= home_url('/b2b-panel/settings/shipping') ?>" class="<?= in_array(get_query_var('b2b_adm_page'), ['settings_shipping','shipping_zone_edit'])?'active':'' ?>" data-title="Shipping">
                    <i class="fa-solid fa-truck"></i> <span class="menu-text">Shipping</span>
                </a>
                <a href="<?= home_url('/b2b-panel/settings/payments') ?>" class="<?= get_query_var('b2b_adm_page')=='settings_payments'?'active':'' ?>" data-title="Payment Gateways">
                    <i class="fa-solid fa-credit-card"></i> <span class="menu-text">Payment Gateways</span>
                </a>
                <a href="<?= home_url('/b2b-panel/settings/sales-agent') ?>" class="<?= get_query_var('b2b_adm_page')=='settings_sales_agent'?'active':'' ?>" data-title="Sales Agent">
                    <i class="fa-solid fa-user-tie"></i> <span class="menu-text">Sales Agent</span>
                </a>
            </div>
            
            <!-- Support Tickets Module -->
            <?php if(current_user_can('manage_woocommerce')): ?>
            <a href="<?= home_url('/b2b-panel/support-tickets') ?>" class="<?= in_array(get_query_var('b2b_adm_page'), ['support-tickets','support-ticket'])?'active':'' ?>" data-title="Support">
                <i class="fa-solid fa-headphones"></i> <span class="menu-text">Support</span>
            </a>
            <?php endif; ?>
            
            <!-- Messaging Module -->
            <a href="<?= home_url('/b2b-panel/messaging') ?>" class="<?= in_array(get_query_var('b2b_adm_page'), ['messaging','messaging_groups'])?'active':'' ?>" data-title="Messaging">
                <i class="fa-solid fa-comments"></i> <span class="menu-text">Messaging</span>
            </a>
            
            <!-- Notes Module -->
            <a href="<?= home_url('/b2b-panel/notes') ?>" class="<?= get_query_var('b2b_adm_page')=='notes'?'active':'' ?>" data-title="Notes">
                <i class="fa-solid fa-note-sticky"></i> <span class="menu-text">Notes</span>
            </a>
        </div>
        <div style="margin-top:auto;padding:20px">
            <a href="<?= wp_logout_url(home_url('/b2b-login')) ?>" style="color:#fca5a5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:10px"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main">
    <script>
    // Define ajaxurl for AJAX requests (not available in front-end by default)
    var ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
    
    function toggleSubmenu(el) {
        const sidebar = document.querySelector('.sidebar');
        // Don't toggle submenu in collapsed mode - let hover handle it
        if (sidebar.classList.contains('collapsed')) {
            return;
        }
        el.classList.toggle('active');
        el.nextElementSibling.classList.toggle('active');
    }
    
    // Sidebar collapse/expand toggle
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const body = document.body;
        const isCollapsed = sidebar.classList.contains('collapsed');
        
        if (isCollapsed) {
            sidebar.classList.remove('collapsed');
            body.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'false');
        } else {
            sidebar.classList.add('collapsed');
            body.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', 'true');
        }
    }
    
    // Restore sidebar state from localStorage on page load
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
        if (sidebarCollapsed === 'true') {
            document.querySelector('.sidebar').classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        }
        
        // Hover submenu for collapsed sidebar
        const sidebar = document.querySelector('.sidebar');
        const submenuToggles = document.querySelectorAll('.submenu-toggle');
        
        submenuToggles.forEach(toggle => {
            let hoverTimeout;
            const submenu = toggle.nextElementSibling;
            
            if (submenu && submenu.classList.contains('submenu')) {
                // Mouse enter on toggle
                toggle.addEventListener('mouseenter', function() {
                    if (sidebar.classList.contains('collapsed')) {
                        hoverTimeout = setTimeout(() => {
                            // Calculate proper position
                            const toggleRect = toggle.getBoundingClientRect();
                            const sidebarRect = sidebar.getBoundingClientRect();
                            const relativeTop = toggleRect.top - sidebarRect.top + sidebar.scrollTop;
                            
                            submenu.style.top = relativeTop + 'px';
                            submenu.classList.add('show');
                        }, 100);
                    }
                });
                
                // Mouse leave on toggle
                toggle.addEventListener('mouseleave', function() {
                    clearTimeout(hoverTimeout);
                    if (sidebar.classList.contains('collapsed')) {
                        setTimeout(() => {
                            if (!submenu.matches(':hover') && !toggle.matches(':hover')) {
                                submenu.classList.remove('show');
                            }
                        }, 200);
                    }
                });
                
                // Mouse enter on submenu
                submenu.addEventListener('mouseenter', function() {
                    clearTimeout(hoverTimeout);
                    if (sidebar.classList.contains('collapsed')) {
                        this.classList.add('show');
                    }
                });
                
                // Mouse leave on submenu
                submenu.addEventListener('mouseleave', function() {
                    if (sidebar.classList.contains('collapsed')) {
                        this.classList.remove('show');
                    }
                });
            }
        });
    });
    
    // Mobile menu toggle
    function toggleMobileMenu() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const isOpen = sidebar.classList.contains('mobile-open');
        
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        
        // Lock body scroll when menu is open
        if(!isOpen) {
            document.body.style.overflow = 'hidden';
            document.body.style.position = 'fixed';
            document.body.style.width = '100%';
        } else {
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
        }
    }
    
    // Update mobile header title based on current page
    function updateMobileTitle() {
        const pageTitle = document.querySelector('.main .page-title');
        const mobileTitle = document.getElementById('mobilePageTitle');
        if(pageTitle && mobileTitle && window.innerWidth <= 768) {
            mobileTitle.textContent = pageTitle.textContent;
        }
    }
    
    // Close mobile menu when clicking a link
    document.addEventListener('DOMContentLoaded', function() {
        updateMobileTitle();
        
        if(window.innerWidth <= 768) {
            const sidebarLinks = document.querySelectorAll('.sidebar-nav a');
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if(window.innerWidth <= 768) {
                        setTimeout(toggleMobileMenu, 150);
                    }
                });
            });
        }
        
        // Update mobile title on window resize
        window.addEventListener('resize', updateMobileTitle);
    });
    </script>
    <?php
}
function b2b_adm_footer() { echo '</div></body></html>'; }

/* =====================================================
   6. PAGE: LOGIN (MASTER UI DESIGN)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'login') return;
    
    // Zaten giriş yapmışsa yönlendir
    if (is_user_logged_in() && current_user_can('manage_options')) { 
        wp_redirect(home_url('/b2b-panel')); exit; 
    }
    
    // Form Gönderildiyse
    if ($_POST) {
        $u = wp_signon(['user_login'=>$_POST['user'], 'user_password'=>$_POST['pass'], 'remember'=>true]);
        if (!is_wp_error($u)) {
            if($u->has_cap('manage_options')) { 
                wp_redirect(home_url('/b2b-panel')); exit; 
            } else { 
                wp_logout(); 
                $err = "Access Denied. Admins only."; 
            }
        } else { 
            $err = "Invalid username or password."; 
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login | B2B Panel</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                --primary: #3b82f6; /* Admin Blue */
                --glass: rgba(255, 255, 255, 0.05);
                --border: rgba(255, 255, 255, 0.1);
                --text: #ffffff;
                --text-muted: #94a3b8;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            
            body {
                font-family: 'Outfit', sans-serif;
                background: var(--bg-gradient);
                color: var(--text);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                position: relative;
            }

            /* Background FX */
            .bg-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                z-index: -1;
                opacity: 0.4;
            }
            .shape-1 { width: 300px; height: 300px; background: var(--primary); top: -50px; left: -50px; }
            .shape-2 { width: 250px; height: 250px; background: #6366f1; bottom: -50px; right: -50px; }

            /* Login Card */
            .login-card {
                background: var(--glass);
                border: 1px solid var(--border);
                padding: 40px 30px;
                border-radius: 20px;
                width: 100%;
                max-width: 360px;
                backdrop-filter: blur(10px);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                text-align: center;
            }

            .icon-box {
                width: 60px;
                height: 60px;
                background: rgba(59, 130, 246, 0.1);
                color: var(--primary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin: 0 auto 20px;
                border: 1px solid rgba(59, 130, 246, 0.3);
            }

            h2 { font-size: 1.5rem; margin-bottom: 5px; font-weight: 700; }
            p.sub { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px; }

            /* Inputs */
            .input-group { margin-bottom: 15px; text-align: left; }
            label { display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px; margin-left: 5px;}
            
            input {
                width: 100%;
                padding: 12px 15px;
                background: rgba(0, 0, 0, 0.2);
                border: 1px solid var(--border);
                border-radius: 10px;
                color: #fff;
                font-family: inherit;
                font-size: 0.95rem;
                transition: 0.3s;
            }
            input:focus {
                outline: none;
                border-color: var(--primary);
                background: rgba(0, 0, 0, 0.3);
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
            }
            input::placeholder { color: rgba(255, 255, 255, 0.3); }

            /* Button */
            button {
                width: 100%;
                padding: 12px;
                margin-top: 10px;
                background: var(--primary);
                color: #fff;
                border: none;
                border-radius: 10px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: 0.3s;
                font-family: inherit;
            }
            button:hover {
                background: #2563eb;
                box-shadow: 0 0 20px rgba(59, 130, 246, 0.4);
            }

            .error-msg {
                background: rgba(239, 68, 68, 0.1);
                color: #fca5a5;
                padding: 10px;
                border-radius: 8px;
                font-size: 0.85rem;
                margin-bottom: 20px;
                border: 1px solid rgba(239, 68, 68, 0.2);
            }
        </style>
    </head>
    <body>

        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>

        <form method="post" class="login-card">
            <div class="icon-box">
                <i class="fa-solid fa-user-shield"></i>
            </div>
            <h2>Admin Login</h2>
            <p class="sub">Enter your credentials to access the panel.</p>

            <?php if(isset($err)): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $err ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="user" placeholder="admin" required autocomplete="off">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="pass" placeholder="••••••••" required>
            </div>

            <button type="submit">Sign In <i class="fa-solid fa-arrow-right" style="margin-left:5px"></i></button>
        </form>

    </body>
    </html>
    <?php
    exit;
});

/* =====================================================
   4C. B2B REGISTRATION PAGE (/b2b-register)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'register') return;
    
    $success = false;
    $error = '';
    
    // Form Gönderildiyse
    if ($_POST && isset($_POST['b2b_register_nonce'])) {
        if (!wp_verify_nonce($_POST['b2b_register_nonce'], 'b2b_register_action')) {
            $error = 'Security check failed. Please try again.';
        } else {
            // Form verilerini al
            $email = sanitize_email($_POST['email'] ?? '');
            $first_name = sanitize_text_field($_POST['first_name'] ?? '');
            $last_name = sanitize_text_field($_POST['last_name'] ?? '');
            $b2b_group = sanitize_text_field($_POST['b2b_group'] ?? '');
            
            // Validasyon
            if (empty($email) || !is_email($email)) {
                $error = 'Please enter a valid email address.';
            } elseif (email_exists($email)) {
                $error = 'This email is already registered.';
            } elseif (empty($first_name) || empty($last_name)) {
                $error = 'First name and last name are required.';
            } else {
                // Kullanıcı oluştur (şifre YOK - onayda oluşturulacak)
                $username = sanitize_user(str_replace('@', '_', $email));
                $user_id = wp_insert_user([
                    'user_login' => $username,
                    'user_email' => $email,
                    'role' => 'customer' // WooCommerce default customer role
                ]); // No password set
                
                if (!is_wp_error($user_id)) {
                    // User meta güncelle
                    update_user_meta($user_id, 'first_name', $first_name);
                    update_user_meta($user_id, 'last_name', $last_name);
                    update_user_meta($user_id, 'billing_first_name', $first_name);
                    update_user_meta($user_id, 'billing_last_name', $last_name);
                    update_user_meta($user_id, 'billing_email', $email);
                    
                    // All standard fields from Form Editor
                    $standard_fields = ['billing_company', 'billing_phone', 'billing_city', 'billing_postcode', 'billing_address_1', 'billing_state', 'billing_country'];
                    foreach ($standard_fields as $field_name) {
                        if (isset($_POST[$field_name])) {
                            update_user_meta($user_id, $field_name, sanitize_text_field($_POST[$field_name]));
                        }
                    }
                    
                    // B2B status - pending onay için
                    update_user_meta($user_id, 'b2b_status', 'pending');
                    update_user_meta($user_id, 'b2b_requested_group', $b2b_group);
                    update_user_meta($user_id, 'b2b_group_slug', ''); // Admin atayacak
                    
                    // Tax Exemption (from registration form)
                    $tax_exempt_requested = isset($_POST['tax_exempt']) ? 1 : 0;
                    $tax_id = sanitize_text_field($_POST['tax_id'] ?? '');
                    $tax_notes = sanitize_textarea_field($_POST['tax_notes'] ?? '');
                    
                    update_user_meta($user_id, 'b2b_tax_exempt_requested', $tax_exempt_requested);
                    update_user_meta($user_id, 'b2b_tax_id', $tax_id);
                    update_user_meta($user_id, 'b2b_tax_notes', $tax_notes);
                    
                    // Auto-approve if setting enabled, otherwise pending
                    $tax_auto = get_option('b2b_tax_auto_remove', 0);
                    if($tax_exempt_requested && $tax_auto) {
                        update_user_meta($user_id, 'b2b_tax_exempt', 1);
                        update_user_meta($user_id, 'b2b_tax_request', 0);
                    } elseif($tax_exempt_requested) {
                        update_user_meta($user_id, 'b2b_tax_exempt', 0);
                        update_user_meta($user_id, 'b2b_tax_request', 1);
                        update_user_meta($user_id, 'b2b_tax_request_date', current_time('mysql'));
                    }
                    
                    // Handle file upload for tax certificate
                    if($tax_exempt_requested && isset($_FILES['tax_certificate']) && $_FILES['tax_certificate']['error'] === 0) {
                        $allowed_types = explode(',', get_option('b2b_tax_allowed_types', 'pdf,jpg,jpeg,png'));
                        $file_ext = strtolower(pathinfo($_FILES['tax_certificate']['name'], PATHINFO_EXTENSION));
                        
                        if(in_array($file_ext, $allowed_types)) {
                            require_once(ABSPATH . 'wp-admin/includes/file.php');
                            $upload = wp_handle_upload($_FILES['tax_certificate'], ['test_form' => false]);
                            if(isset($upload['url'])) {
                                update_user_meta($user_id, 'b2b_tax_certificate', $upload['url']);
                            }
                        }
                    }
                    
                    // Custom fields (form editor'den)
                    $custom_fields = b2b_get_custom_fields();
                    foreach ($custom_fields as $key => $field) {
                        if (isset($_POST['custom_' . $key])) {
                            $value = sanitize_text_field($_POST['custom_' . $key]);
                            update_user_meta($user_id, 'b2b_custom_' . $key, $value);
                        }
                    }
                    
                    // Admin'e email gönder
                    $admin_email = get_option('admin_email');
                    $message = "New B2B registration:\n\n";
                    $message .= "Name: $first_name $last_name\n";
                    $message .= "Email: $email\n";
                    $message .= "Requested Group: " . ($b2b_group ?: 'None') . "\n\n";
                    $message .= "Review at: " . home_url('/b2b-panel/b2b-module');
                    wp_mail($admin_email, 'New B2B Registration - ' . $first_name . ' ' . $last_name, $message);
                    
                    $success = true;
                } else {
                    $error = 'Registration failed: ' . $user_id->get_error_message();
                }
            }
        }
    }
    
    // Form Editor ayarları
    $standard_config = b2b_get_standard_fields_config();
    $custom_fields = b2b_get_custom_fields();
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>B2B Registration | Register</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                --primary: #10b981;
                --glass: rgba(255, 255, 255, 0.05);
                --border: rgba(255, 255, 255, 0.1);
                --text: #ffffff;
                --text-muted: #94a3b8;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            
            body {
                font-family: 'Outfit', sans-serif;
                background: var(--bg-gradient);
                color: var(--text);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 40px 20px;
                position: relative;
                overflow-x: hidden;
            }

            .bg-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                z-index: -1;
                opacity: 0.4;
                animation: float 6s ease-in-out infinite;
            }
            .shape-1 { width: 300px; height: 300px; background: var(--primary); top: -50px; left: -50px; }
            .shape-2 { width: 250px; height: 250px; background: #6366f1; bottom: -50px; right: -50px; animation-delay: 3s; }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }

            .register-card {
                background: var(--glass);
                border: 1px solid var(--border);
                padding: 40px 30px;
                border-radius: 20px;
                width: 100%;
                max-width: 500px;
                backdrop-filter: blur(10px);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            }

            .icon-box {
                width: 60px;
                height: 60px;
                background: rgba(16, 185, 129, 0.1);
                color: var(--primary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin: 0 auto 20px;
                border: 1px solid rgba(16, 185, 129, 0.3);
            }

            h2 { font-size: 1.5rem; margin-bottom: 5px; font-weight: 700; text-align: center; }
            p.sub { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px; text-align: center; }

            .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 15px;
                margin-bottom: 15px;
            }

            .input-group { margin-bottom: 15px; text-align: left; }
            label { display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px; margin-left: 5px;}
            label .req { color: #f87171; }
            
            input, textarea, select {
                width: 100%;
                padding: 12px 15px;
                background: rgba(0, 0, 0, 0.2);
                border: 1px solid var(--border);
                border-radius: 10px;
                color: #fff;
                font-family: inherit;
                font-size: 0.95rem;
                transition: 0.3s;
            }
            input:focus, textarea:focus, select:focus {
                outline: none;
                border-color: var(--primary);
                background: rgba(0, 0, 0, 0.3);
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            }
            input::placeholder, textarea::placeholder { color: rgba(255, 255, 255, 0.3); }
            textarea { min-height: 80px; resize: vertical; }

            button {
                width: 100%;
                padding: 12px;
                margin-top: 10px;
                background: var(--primary);
                color: #fff;
                border: none;
                border-radius: 10px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: 0.3s;
                font-family: inherit;
            }
            button:hover {
                background: #059669;
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            }

            .success-msg {
                background: rgba(16, 185, 129, 0.1);
                color: #6ee7b7;
                padding: 15px;
                border-radius: 8px;
                font-size: 0.9rem;
                margin-bottom: 20px;
                border: 1px solid rgba(16, 185, 129, 0.2);
                text-align: center;
            }

            .error-msg {
                background: rgba(239, 68, 68, 0.1);
                color: #fca5a5;
                padding: 10px;
                border-radius: 8px;
                font-size: 0.85rem;
                margin-bottom: 20px;
                border: 1px solid rgba(239, 68, 68, 0.2);
            }

            .login-link {
                text-align: center;
                margin-top: 20px;
                color: var(--text-muted);
                font-size: 0.9rem;
            }
            .login-link a {
                color: var(--primary);
                text-decoration: none;
                font-weight: 600;
            }
            .login-link a:hover {
                text-decoration: underline;
            }

            @media (max-width: 600px) {
                .form-row {
                    grid-template-columns: 1fr;
                    gap: 0;
                }
            }
        </style>
    </head>
    <body>

        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>

        <form method="post" class="register-card" enctype="multipart/form-data">
            <div class="icon-box">
                <i class="fa-solid fa-user-plus"></i>
            </div>
            <h2>B2B Registration</h2>
            <p class="sub">Create your business account to get started.</p>

            <?php if($success): ?>
                <div class="success-msg">
                    <i class="fa-solid fa-circle-check"></i> <strong>Registration Successful!</strong><br>
                    Your application has been submitted and is pending approval. We'll contact you soon.
                </div>
                <div class="login-link">
                    <a href="<?= home_url('/b2b-login') ?>"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>
                </div>
            <?php else: ?>

                <?php if($error): ?>
                    <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= esc_html($error) ?></div>
                <?php endif; ?>

                <?php wp_nonce_field('b2b_register_action', 'b2b_register_nonce'); ?>

                <div class="form-row">
                    <div class="input-group">
                        <label>First Name <span class="req">*</span></label>
                        <input type="text" name="first_name" required value="<?= $_POST['first_name'] ?? '' ?>">
                    </div>
                    <div class="input-group">
                        <label>Last Name <span class="req">*</span></label>
                        <input type="text" name="last_name" required value="<?= $_POST['last_name'] ?? '' ?>">
                    </div>
                </div>

                <div class="input-group">
                    <label>Email Address <span class="req">*</span></label>
                    <input type="email" name="email" required value="<?= $_POST['email'] ?? '' ?>">
                </div>

                <div class="input-group">
                    <label>B2B Group <span class="req">*</span></label>
                    <select name="b2b_group" required>
                        <option value="">- Select Your Business Type -</option>
                        <?php 
                        $groups = b2b_get_groups();
                        foreach ($groups as $slug => $group): ?>
                            <option value="<?= esc_attr($slug) ?>" <?= (($_POST['b2b_group'] ?? '') === $slug) ? 'selected' : '' ?>><?= esc_html($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php 
                // All 7 standard WooCommerce fields
                $all_standard_fields = [
                    'billing_company' => ['label' => 'Company Name', 'type' => 'text'],
                    'billing_phone' => ['label' => 'Phone', 'type' => 'tel'],
                    'billing_city' => ['label' => 'City', 'type' => 'text'],
                    'billing_postcode' => ['label' => 'Postcode/ZIP', 'type' => 'text'],
                    'billing_address_1' => ['label' => 'Address', 'type' => 'text'],
                    'billing_state' => ['label' => 'State/Province', 'type' => 'text'],
                    'billing_country' => ['label' => 'Country', 'type' => 'text']
                ];
                
                foreach ($all_standard_fields as $field_name => $field_info): 
                    $is_enabled = isset($standard_config[$field_name]['enabled']) ? $standard_config[$field_name]['enabled'] : 1;
                    $is_required = isset($standard_config[$field_name]['required']) ? $standard_config[$field_name]['required'] : 0;
                    
                    if ($is_enabled): ?>
                        <div class="input-group">
                            <label><?= esc_html($field_info['label']) ?> <?= $is_required ? '<span class="req">*</span>' : '' ?></label>
                            <input type="<?= esc_attr($field_info['type']) ?>" name="<?= esc_attr($field_name) ?>" <?= $is_required ? 'required' : '' ?> value="<?= $_POST[$field_name] ?? '' ?>">
                        </div>
                    <?php endif; 
                endforeach; ?>

                <?php foreach ($custom_fields as $key => $field): ?>
                    <div class="input-group">
                        <label><?= esc_html($field['label']) ?> <?= $field['required'] ? '<span class="req">*</span>' : '' ?></label>
                        <?php if ($field['type'] === 'textarea'): ?>
                            <textarea name="custom_<?= esc_attr($key) ?>" <?= $field['required'] ? 'required' : '' ?>><?= $_POST['custom_' . $key] ?? '' ?></textarea>
                        <?php elseif ($field['type'] === 'select' && !empty($field['options'])): ?>
                            <select name="custom_<?= esc_attr($key) ?>" <?= $field['required'] ? 'required' : '' ?>>
                                <option value="">- Select -</option>
                                <?php foreach (explode(',', $field['options']) as $opt): ?>
                                    <option value="<?= esc_attr(trim($opt)) ?>" <?= (($_POST['custom_' . $key] ?? '') === trim($opt)) ? 'selected' : '' ?>><?= esc_html(trim($opt)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="custom_<?= esc_attr($key) ?>" <?= $field['required'] ? 'required' : '' ?> value="<?= $_POST['custom_' . $key] ?? '' ?>">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>

                <!-- Tax Exemption Section -->
                <?php 
                $tax_enable_text = get_option('b2b_tax_enable_text', 1);
                $tax_text_label = get_option('b2b_tax_text_label', 'Tax ID');
                $tax_enable_textarea = get_option('b2b_tax_enable_textarea', 0);
                $tax_textarea_label = get_option('b2b_tax_textarea_label', 'Additional Information');
                $tax_enable_file = get_option('b2b_tax_enable_file', 1);
                $tax_file_label = get_option('b2b_tax_file_label', 'Tax Certificate');
                ?>
                <div class="input-group" style="border-top:1px solid rgba(255,255,255,0.1);padding-top:20px;margin-top:20px;">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:15px;">
                        <input type="checkbox" name="tax_exempt" id="tax_exempt" value="1" onchange="document.getElementById('tax_fields').style.display=this.checked?'block':'none'" style="width:auto;">
                        <span style="font-weight:600;color:#fff;">Request Tax Exemption</span>
                    </label>
                    
                    <div id="tax_fields" style="display:none;margin-left:30px;padding-left:15px;border-left:2px solid rgba(16,185,129,0.3);">
                        <?php if($tax_enable_text): ?>
                        <div class="input-group">
                            <label><?= esc_html($tax_text_label) ?></label>
                            <input type="text" name="tax_id" placeholder="Enter your tax ID number">
                        </div>
                        <?php endif; ?>
                        
                        <?php if($tax_enable_textarea): ?>
                        <div class="input-group">
                            <label><?= esc_html($tax_textarea_label) ?></label>
                            <textarea name="tax_notes" placeholder="Additional information or notes"></textarea>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($tax_enable_file): ?>
                        <div class="input-group">
                            <label><?= esc_html($tax_file_label) ?></label>
                            <input type="file" name="tax_certificate" accept=".<?= str_replace(',', ',.', get_option('b2b_tax_allowed_types', 'pdf,jpg,jpeg,png')) ?>" style="padding:8px;">
                            <small style="color:rgba(255,255,255,0.5);font-size:0.75rem;display:block;margin-top:5px;">Allowed: <?= get_option('b2b_tax_allowed_types', 'pdf,jpg,jpeg,png') ?></small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <button type="submit">Register <i class="fa-solid fa-arrow-right" style="margin-left:5px"></i></button>

                <div class="login-link">
                    Already have an account? <a href="<?= home_url() ?>">Login here</a>
                </div>
            <?php endif; ?>
        </form>

    </body>
    </html>
    <?php
    exit;
});

/* =====================================================
   7. PAGE: DASHBOARD
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'dashboard') return;
    b2b_adm_guard();
    b2b_adm_header('Dashboard');
    
    global $wpdb;
    $alert_days = 15;

    $month_sales = $wpdb->get_var($wpdb->prepare("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id=p.ID WHERE p.post_status='wc-completed' AND pm.meta_key='_order_total' AND p.post_date >= %s", date('Y-m-01')));
    $total_sales = $wpdb->get_var("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id=p.ID WHERE p.post_status='wc-completed' AND pm.meta_key='_order_total'");

    $statuses = wc_get_order_statuses();
    $wh_stats = [];
    foreach ($statuses as $slug => $label) {
        $clean = str_replace('wc-', '', $slug);
        $oldest = $wpdb->get_var($wpdb->prepare("SELECT post_date FROM {$wpdb->posts} WHERE post_type='shop_order' AND post_status=%s ORDER BY post_date ASC LIMIT 1", $slug));
        $count = wc_orders_count($clean);
        
        $days='-'; $late=false; $date='-';
        if ($count > 0 && $slug !== 'wc-completed' && $oldest) {
            $diff = (new DateTime())->diff(new DateTime($oldest))->days;
            $days = $diff . ' days';
            $date = date('d.m', strtotime($oldest));
            if ($diff > $alert_days) $late = true;
        }
        $wh_stats[] = ['label'=>$label, 'slug'=>$clean, 'count'=>$count, 'date'=>$date, 'days'=>$days, 'late'=>$late];
    }
    ?>

    <div class="page-header">
        <h1 class="page-title">Overview</h1>
        <button id="screenOptionsToggle" class="secondary" style="display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-sliders"></i> Screen Options
        </button>
    </div>
    
    <!-- Screen Options Panel -->
    <div id="screenOptionsPanel" style="display:none;background:var(--white);border:1px solid var(--border);border-radius:8px;padding:20px;margin-bottom:20px;box-shadow:var(--shadow)">
        <h4 style="margin:0 0 15px 0;color:var(--text)">Show/Hide Widgets</h4>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px">
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="stats" checked> Statistics Overview
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="orders-summary" checked> Orders Summary
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="status" checked> Order Status & Delays
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="low-stock" checked> Low Stock Alert
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="top-products" checked> Top Products
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="recent-customers" checked> Recent Customers
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="agents" checked> Sales Agent Performance
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="charts" checked> Sales Charts
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="quick-actions" checked> Quick Actions
            </label>
            <label style="display:flex;align-items:center;gap:8px;cursor:pointer">
                <input type="checkbox" class="widget-toggle" data-widget="notes" checked> Important Notes
            </label>
        </div>
        <p style="margin:15px 0 0 0;color:var(--text-muted);font-size:12px"><i class="fa-solid fa-info-circle"></i> Drag widgets to reorder them. Settings are saved automatically.</p>
    </div>

    <div class="dashboard-widgets" id="dashboardWidgets">

    <div class="dashboard-widget" data-widget="stats" draggable="true">
    <div class="grid-main" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:30px;margin-bottom:30px">
        <div class="card" style="display:flex;align-items:center;justify-content:space-between">
            <div><small style="color:#6b7280;font-weight:600;text-transform:uppercase">Sales This Month</small><div style="font-size:32px;font-weight:800;color:#10b981"><?= wc_price($month_sales?:0) ?></div></div>
            <i class="fa-solid fa-chart-line" style="font-size:40px;color:#e5e7eb"></i>
        </div>
        <div class="card" style="display:flex;align-items:center;justify-content:space-between">
            <div><small style="color:#6b7280;font-weight:600;text-transform:uppercase">Total Revenue</small><div style="font-size:32px;font-weight:800;color:#0f172a"><?= wc_price($total_sales?:0) ?></div></div>
            <i class="fa-solid fa-sack-dollar" style="font-size:40px;color:#e5e7eb"></i>
        </div>
        <?php 
        $pending_users = get_users(['meta_key' => 'b2b_status', 'meta_value' => 'pending']);
        $pending_count = count($pending_users);
        ?>
        <a href="<?= home_url('/b2b-panel/b2b-module') ?>" style="text-decoration:none;">
            <div class="card" style="display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);color:#fff;cursor:pointer;transition:transform 0.3s;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                <div><small style="color:rgba(255,255,255,0.9);font-weight:600;text-transform:uppercase">Pending Approvals</small><div style="font-size:32px;font-weight:800;color:#fff"><?= $pending_count ?></div></div>
                <i class="fa-solid fa-user-clock" style="font-size:40px;color:rgba(255,255,255,0.3)"></i>
            </div>
        </a>
    </div>
    </div>

    <div class="dashboard-widget" data-widget="orders-summary" draggable="true">
    <div class="card" style="background:linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);color:white">
        <h3 style="margin:0 0 20px 0;color:white;display:flex;align-items:center;gap:10px">
            <i class="fa-solid fa-shopping-cart"></i> Orders Summary
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
            <?php 
            $today_orders = wc_orders_count('processing') + wc_orders_count('pending');
            $month_orders = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type='shop_order' AND post_date >= %s", date('Y-m-01')));
            ?>
            <div>
                <div style="font-size:12px;opacity:0.9;margin-bottom:5px">Today's Orders</div>
                <div style="font-size:32px;font-weight:800"><?= $today_orders ?></div>
            </div>
            <div>
                <div style="font-size:12px;opacity:0.9;margin-bottom:5px">This Month</div>
                <div style="font-size:32px;font-weight:800"><?= $month_orders ?></div>
            </div>
        </div>
    </div>
    </div>

    <div class="dashboard-widget" data-widget="low-stock" draggable="true">
    <div class="card" style="background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);color:white">
        <h3 style="margin:0 0 20px 0;color:white;display:flex;align-items:center;gap:10px">
            <i class="fa-solid fa-triangle-exclamation"></i> Low Stock Alert
        </h3>
        <?php 
        $low_stock = $wpdb->get_results("SELECT p.ID, p.post_title, pm.meta_value as stock FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id WHERE p.post_type='product' AND p.post_status='publish' AND pm.meta_key='_stock' AND CAST(pm.meta_value AS SIGNED) < 10 ORDER BY CAST(pm.meta_value AS SIGNED) ASC LIMIT 5");
        ?>
        <div style="font-size:48px;font-weight:800;margin-bottom:10px"><?= count($low_stock) ?></div>
        <div style="font-size:14px;opacity:0.9">Products below threshold</div>
        <?php if(count($low_stock) > 0): ?>
        <a href="<?= home_url('/b2b-panel/stock-planning') ?>" style="display:inline-block;margin-top:15px;padding:8px 16px;background:rgba(255,255,255,0.2);color:white;border-radius:6px;text-decoration:none;font-size:13px;font-weight:600">View Stock Analysis →</a>
        <?php endif; ?>
    </div>
    </div>

    <div class="dashboard-widget" data-widget="status" draggable="true">
    <h3 style="margin-bottom:20px;color:#4b5563">Order Status & Delays</h3>
    <div class="table-responsive">
    <div class="dash-grid">
        <?php foreach($wh_stats as $s): ?>
        <a href="?b2b_adm_page=orders&status=<?= $s['slug'] ?>" class="dash-card <?= $s['late']?'warning':'' ?>">
            <div style="display:flex;justify-content:space-between">
                <span class="dash-label" style="font-weight:700;font-size:12px;text-transform:uppercase;color:#6b7280"><?= $s['label'] ?></span>
                <i class="fa-solid <?= $s['slug']=='completed'?'fa-check':'fa-clock' ?>" style="color:#d1d5db"></i>
            </div>
            <div style="font-size:28px;font-weight:800;color:#1f2937"><?= $s['count'] ?></div>
            <div style="font-size:12px;color:#6b7280;border-top:1px solid #f3f4f6;padding-top:10px;display:flex;justify-content:space-between">
                <span><?= $s['date'] ?></span> <span style="font-weight:700;color:<?= $s['late']?'#ef4444':'#6b7280' ?>"><?= $s['days'] ?></span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
    </div>
    </div>

    <div class="dashboard-widget" data-widget="top-products" draggable="true">
    <div class="card">
        <h3 style="margin-top:0;display:flex;align-items:center;gap:10px">
            <i class="fa-solid fa-trophy" style="color:#f59e0b"></i> Top 5 Products This Month
        </h3>
        <?php 
        $top_products = $wpdb->get_results($wpdb->prepare("
            SELECT p.post_title, SUM(oim.meta_value) as qty 
            FROM {$wpdb->prefix}woocommerce_order_items oi 
            JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id 
            JOIN {$wpdb->posts} ord ON oi.order_id = ord.ID
            JOIN {$wpdb->posts} p ON oim.meta_value = p.ID
            WHERE oi.order_item_type = 'line_item' 
            AND oim.meta_key = '_product_id'
            AND ord.post_status = 'wc-completed'
            AND ord.post_date >= %s
            GROUP BY p.ID 
            ORDER BY qty DESC 
            LIMIT 5
        ", date('Y-m-01')));
        ?>
        <table style="width:100%">
            <thead><tr><th>Product</th><th style="text-align:right">Quantity Sold</th></tr></thead>
            <tbody>
            <?php foreach($top_products as $prod): ?>
                <tr>
                    <td><?= esc_html($prod->post_title) ?></td>
                    <td style="text-align:right;font-weight:700"><?= $prod->qty ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

    <div class="dashboard-widget" data-widget="recent-customers" draggable="true">
    <div class="card">
        <h3 style="margin-top:0;display:flex;align-items:center;gap:10px">
            <i class="fa-solid fa-users" style="color:#3b82f6"></i> Recent B2B Customers
        </h3>
        <?php 
        $recent_customers = get_users([
            'meta_key' => 'b2b_status',
            'meta_value' => 'approved',
            'number' => 5,
            'orderby' => 'registered',
            'order' => 'DESC'
        ]);
        ?>
        <table style="width:100%">
            <thead><tr><th>Customer</th><th>Email</th><th>Registered</th></tr></thead>
            <tbody>
            <?php foreach($recent_customers as $customer): ?>
                <tr>
                    <td><?= esc_html($customer->display_name) ?></td>
                    <td><?= esc_html($customer->user_email) ?></td>
                    <td><?= date('M d, Y', strtotime($customer->user_registered)) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div>

    <div class="dashboard-widget" data-widget="quick-actions" draggable="true">
    <div class="card">
        <h3 style="margin-top:0;display:flex;align-items:center;gap:10px">
            <i class="fa-solid fa-bolt" style="color:#8b5cf6"></i> Quick Actions
        </h3>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
            <a href="<?= home_url('/b2b-panel/products/add-new') ?>" style="padding:15px;background:linear-gradient(135deg, #10b981 0%, #059669 100%);color:white;border-radius:8px;text-decoration:none;text-align:center;font-weight:600;transition:transform 0.3s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-plus"></i> Add Product
            </a>
            <a href="<?= home_url('/b2b-panel/products/import') ?>" style="padding:15px;background:linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);color:white;border-radius:8px;text-decoration:none;text-align:center;font-weight:600;transition:transform 0.3s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-file-import"></i> Import Products
            </a>
            <a href="<?= home_url('/b2b-panel/stock-planning') ?>" style="padding:15px;background:linear-gradient(135deg, #f59e0b 0%, #d97706 100%);color:white;border-radius:8px;text-decoration:none;text-align:center;font-weight:600;transition:transform 0.3s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-chart-line"></i> Stock Analysis
            </a>
            <a href="<?= home_url('/b2b-panel/b2b-module') ?>" style="padding:15px;background:linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);color:white;border-radius:8px;text-decoration:none;text-align:center;font-weight:600;transition:transform 0.3s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="fa-solid fa-user-check"></i> Approvals
            </a>
        </div>
    </div>
    </div>

    <div class="dashboard-widget" data-widget="agents" draggable="true">
    <div class="card">
        <h3 style="margin-top:0">Sales Agent Performance</h3>
        <div class="table-responsive">
        <table><thead><tr><th>Agent</th><th>Customers</th><th>Total Sales</th></tr></thead><tbody>
        <?php 
        $agents = get_users(['role'=>'sales_agent']);
        foreach($agents as $a) {
            $c_ids = get_users(['meta_key'=>'bagli_agent_id', 'meta_value'=>$a->ID, 'fields'=>'ID']);
            $ids_str = !empty($c_ids) ? implode(',', $c_ids) : '-1';
            $rev = $wpdb->get_var("SELECT SUM(pm.meta_value) FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON pm.post_id=p.ID JOIN {$wpdb->postmeta} pm_cust ON p.ID=pm_cust.post_id WHERE p.post_status='wc-completed' AND pm.meta_key='_order_total' AND pm_cust.meta_key='_customer_user' AND pm_cust.meta_value IN ($ids_str)");
            echo "<tr><td><i class='fa-solid fa-user-tie' style='margin-right:10px;color:#9ca3af'></i> {$a->display_name}</td><td>".count($c_ids)."</td><td><strong>".wc_price($rev?:0)."</strong></td></tr>";
        }
        ?>
        </tbody></table>
        </div>
    </div>
    </div>

    <!-- Chart.js Dashboard Widgets -->
    <div class="dashboard-widget" data-widget="charts" draggable="true">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:30px;margin-top:30px;">
        <div class="card">
            <h3 style="margin-top:0;color:#111827;"><i class="fa-solid fa-chart-area" style="color:#3b82f6;margin-right:10px;"></i>Sales Trend (Last 30 Days)</h3>
            <canvas id="salesTrendChart" height="80"></canvas>
        </div>
        <div class="card">
            <h3 style="margin-top:0;color:#111827;"><i class="fa-solid fa-chart-pie" style="color:#f59e0b;margin-right:10px;"></i>Order Status</h3>
            <canvas id="orderStatusChart"></canvas>
        </div>
    </div>
    
    <div class="card" style="margin-top:30px;">
        <h3 style="margin-top:0;color:#111827;"><i class="fa-solid fa-chart-bar" style="color:#10b981;margin-right:10px;"></i>Top 5 Products (By Revenue)</h3>
        <canvas id="topProductsChart" height="60"></canvas>
    </div>
    </div>
    </div>

    <!-- Important Notes Widget -->
    <?php
    $all_notes = get_option('b2b_notes', []);
    $user_id = get_current_user_id();
    $user_groups = array_keys(b2b_get_user_messaging_groups($user_id));
    
    // Filter visible notes
    $dashboard_notes = [];
    foreach ($all_notes as $note_id => $note) {
        if ($note['visibility'] == 'general') {
            $dashboard_notes[$note_id] = $note;
        } elseif ($note['visibility'] == 'group' && in_array($note['group_id'], $user_groups)) {
            $dashboard_notes[$note_id] = $note;
        } elseif (current_user_can('manage_options')) {
            $dashboard_notes[$note_id] = $note;
        }
    }
    
    // Show only latest 2 notes (half-width widget)
    $dashboard_notes = array_slice($dashboard_notes, 0, 2, true);
    
    if (!empty($dashboard_notes)):
    ?>
    <div class="dashboard-widget dashboard-widget-half" data-widget="notes" draggable="true" style="max-width:100%;overflow:hidden;">
    <div class="card" style="overflow:hidden;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
            <h3 style="margin:0;color:var(--text);"><i class="fa-solid fa-note-sticky" style="color:var(--primary);"></i> Important Notes</h3>
            <a href="<?= home_url('/b2b-panel/notes') ?>" style="color:var(--primary);text-decoration:none;font-weight:600;">
                View All <i class="fa-solid fa-arrow-right"></i>
            </a>
        </div>
        <div class="notes-list" style="display:flex;flex-direction:column;gap:15px;">
            <?php foreach ($dashboard_notes as $note_id => $note): ?>
            <div class="note-card" style="width:100%;background:var(--bg);padding:15px;border-radius:8px;border-left:3px solid var(--primary);">
                <h4 style="margin:0 0 8px 0;color:var(--text);font-size:14px;font-weight:600;"><?= esc_html($note['title']) ?></h4>
                <p style="margin:0;font-size:13px;color:var(--text-muted);line-height:1.5;">
                    <?= esc_html(mb_strlen($note['content']) > 80 ? mb_substr($note['content'], 0, 80) . '...' : $note['content']) ?>
                </p>
                <div style="font-size:11px;color:var(--text-muted);margin-top:8px;">
                    <i class="fa-solid fa-user"></i> <?= esc_html($note['author']) ?> • 
                    <i class="fa-solid fa-clock"></i> <?= date('d.m.Y', strtotime($note['created'])) ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    </div>
    <?php endif; ?>

    </div> <!-- End dashboard-widgets -->

    <style>
    .dashboard-widget{margin-bottom:30px;transition:opacity 0.3s ease}
    .dashboard-widget.hidden{display:none}
    .dashboard-widget.dragging{opacity:0.5}
    .dashboard-widget.drag-over{border-top:3px solid var(--primary)}
    
    /* Half-width widgets for better layout */
    .dashboard-widget-half{
        width:100%;
        max-width:100%;
    }
    
    /* Grid for dashboard widgets - auto-fit with min 400px */
    .dashboard-widgets{
        display:grid;
        grid-template-columns:repeat(auto-fit, minmax(400px, 1fr));
        gap:30px;
        align-items:start;
    }
    
    /* Make widgets same height when side-by-side */
    .dashboard-widgets > .dashboard-widget{
        height:100%;
        display:flex;
        flex-direction:column;
    }
    
    .dashboard-widgets > .dashboard-widget > .card{
        flex:1;
        display:flex;
        flex-direction:column;
    }
    
    /* Responsive adjustments */
    @media (max-width: 992px){
        .dashboard-widgets{
            grid-template-columns:1fr;
        }
    }
    </style>

    <script>
    // Dashboard Screen Options
    document.getElementById('screenOptionsToggle').addEventListener('click', function() {
        const panel = document.getElementById('screenOptionsPanel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    });
    
    // Widget Visibility Toggle
    const widgetToggles = document.querySelectorAll('.widget-toggle');
    widgetToggles.forEach(toggle => {
        // Load saved state
        const widget = toggle.dataset.widget;
        const isHidden = localStorage.getItem('dashboard_widget_' + widget) === 'hidden';
        toggle.checked = !isHidden;
        
        const widgetEl = document.querySelector(`.dashboard-widget[data-widget="${widget}"]`);
        if (isHidden && widgetEl) {
            widgetEl.classList.add('hidden');
        }
        
        // Handle toggle change
        toggle.addEventListener('change', function() {
            if (widgetEl) {
                if (this.checked) {
                    widgetEl.classList.remove('hidden');
                    localStorage.removeItem('dashboard_widget_' + widget);
                } else {
                    widgetEl.classList.add('hidden');
                    localStorage.setItem('dashboard_widget_' + widget, 'hidden');
                }
            }
        });
    });
    
    // Drag and Drop for Widget Reordering
    const dashboardWidgets = document.getElementById('dashboardWidgets');
    const widgets = document.querySelectorAll('.dashboard-widget');
    
    widgets.forEach(widget => {
        widget.addEventListener('dragstart', function(e) {
            this.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', this.innerHTML);
        });
        
        widget.addEventListener('dragend', function() {
            this.classList.remove('dragging');
            document.querySelectorAll('.dashboard-widget').forEach(w => w.classList.remove('drag-over'));
            saveWidgetOrder();
        });
        
        widget.addEventListener('dragover', function(e) {
            e.preventDefault();
            const dragging = document.querySelector('.dragging');
            if (dragging && dragging !== this) {
                const rect = this.getBoundingClientRect();
                const midpoint = rect.top + rect.height / 2;
                if (e.clientY < midpoint) {
                    this.parentNode.insertBefore(dragging, this);
                } else {
                    this.parentNode.insertBefore(dragging, this.nextSibling);
                }
            }
        });
    });
    
    function saveWidgetOrder() {
        const order = Array.from(document.querySelectorAll('.dashboard-widget')).map(w => w.dataset.widget);
        localStorage.setItem('dashboardWidgetOrder', JSON.stringify(order));
    }
    
    // Restore widget order on load
    const savedOrder = localStorage.getItem('dashboardWidgetOrder');
    if (savedOrder) {
        const order = JSON.parse(savedOrder);
        order.forEach(widgetName => {
            const widget = document.querySelector(`.dashboard-widget[data-widget="${widgetName}"]`);
            if (widget) {
                dashboardWidgets.appendChild(widget);
            }
        });
    }
    </script>

    <script>
    // Sales Trend Chart Data
    <?php
    $sales_30days = $wpdb->get_results($wpdb->prepare("
        SELECT DATE(p.post_date) as date, SUM(pm.meta_value) as revenue
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status = 'wc-completed'
        AND pm.meta_key = '_order_total'
        AND p.post_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(p.post_date)
        ORDER BY date ASC
    "));
    
    $dates = [];
    $revenues = [];
    foreach($sales_30days as $day) {
        $dates[] = date('M d', strtotime($day->date));
        $revenues[] = round($day->revenue, 2);
    }
    ?>
    
    // Sales Trend Chart
    const salesCtx = document.getElementById('salesTrendChart').getContext('2d');
    new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($dates) ?>,
            datasets: [{
                label: 'Daily Sales ($)',
                data: <?= json_encode($revenues) ?>,
                borderColor: '#3b82f6',
                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.y.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
    
    // Order Status Pie Chart Data
    <?php
    $status_counts = [];
    foreach($wh_stats as $stat) {
        if($stat['count'] > 0) {
            $status_counts[$stat['label']] = $stat['count'];
        }
    }
    ?>
    
    const orderCtx = document.getElementById('orderStatusChart').getContext('2d');
    new Chart(orderCtx, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_keys($status_counts)) ?>,
            datasets: [{
                data: <?= json_encode(array_values($status_counts)) ?>,
                backgroundColor: [
                    '#10b981',
                    '#3b82f6',
                    '#f59e0b',
                    '#ef4444',
                    '#8b5cf6',
                    '#ec4899'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
    
    // Top Products Bar Chart
    <?php
    $top_products = $wpdb->get_results("
        SELECT 
            p.post_title as name,
            SUM(oi.order_item_qty * oim.meta_value) as revenue
        FROM {$wpdb->prefix}woocommerce_order_items oi
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim ON oi.order_item_id = oim.order_item_id AND oim.meta_key = '_line_total'
        INNER JOIN {$wpdb->posts} p ON p.ID = (SELECT meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id = oi.order_item_id AND meta_key = '_product_id' LIMIT 1)
        WHERE oi.order_item_type = 'line_item'
        GROUP BY p.ID
        ORDER BY revenue DESC
        LIMIT 5
    ");
    
    $product_names = [];
    $product_revenues = [];
    foreach($top_products as $prod) {
        $product_names[] = $prod->name;
        $product_revenues[] = round($prod->revenue, 2);
    }
    ?>
    
    const prodCtx = document.getElementById('topProductsChart').getContext('2d');
    new Chart(prodCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode($product_names) ?>,
            datasets: [{
                label: 'Revenue ($)',
                data: <?= json_encode($product_revenues) ?>,
                backgroundColor: [
                    '#10b981',
                    '#3b82f6',
                    '#f59e0b',
                    '#8b5cf6',
                    '#ec4899'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '$' + context.parsed.x.toFixed(2);
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });
    </script>

    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   7B. PAGE: REPORTS MODULE (Comprehensive Analytics)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'reports') return;
    b2b_adm_guard();
    
    global $wpdb;
    
    // Get date range from query params
    $range = isset($_GET['range']) ? sanitize_text_field($_GET['range']) : '30days';
    $start_date = '';
    $end_date = date('Y-m-d 23:59:59');
    
    switch($range) {
        case 'today':
            $start_date = date('Y-m-d 00:00:00');
            break;
        case '7days':
            $start_date = date('Y-m-d 00:00:00', strtotime('-7 days'));
            break;
        case '30days':
            $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
            break;
        case 'thismonth':
            $start_date = date('Y-m-01 00:00:00');
            break;
        case 'lastmonth':
            $start_date = date('Y-m-01 00:00:00', strtotime('first day of last month'));
            $end_date = date('Y-m-t 23:59:59', strtotime('last day of last month'));
            break;
        case 'thisyear':
            $start_date = date('Y-01-01 00:00:00');
            break;
        default:
            $start_date = date('Y-m-d 00:00:00', strtotime('-30 days'));
    }
    
    // Sales Reports Query
    $sales_query = $wpdb->prepare("
        SELECT 
            COUNT(DISTINCT p.ID) as order_count,
            SUM(pm.meta_value) as total_sales,
            AVG(pm.meta_value) as avg_order_value
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status = 'wc-completed'
        AND pm.meta_key = '_order_total'
        AND p.post_date BETWEEN %s AND %s
    ", $start_date, $end_date);
    
    $sales_data = $wpdb->get_row($sales_query);
    
    // Daily sales breakdown
    $daily_sales = $wpdb->get_results($wpdb->prepare("
        SELECT 
            DATE(p.post_date) as date,
            COUNT(DISTINCT p.ID) as orders,
            SUM(pm.meta_value) as revenue
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'shop_order'
        AND p.post_status = 'wc-completed'
        AND pm.meta_key = '_order_total'
        AND p.post_date BETWEEN %s AND %s
        GROUP BY DATE(p.post_date)
        ORDER BY date DESC
        LIMIT 30
    ", $start_date, $end_date));
    
    // Top Customers Query
    $top_customers = $wpdb->get_results($wpdb->prepare("
        SELECT 
            pm_customer.meta_value as customer_id,
            COUNT(DISTINCT p.ID) as order_count,
            SUM(pm_total.meta_value) as total_spent
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
        INNER JOIN {$wpdb->postmeta} pm_customer ON p.ID = pm_customer.post_id AND pm_customer.meta_key = '_customer_user'
        WHERE p.post_type = 'shop_order'
        AND p.post_status = 'wc-completed'
        AND p.post_date BETWEEN %s AND %s
        AND pm_customer.meta_value > 0
        GROUP BY pm_customer.meta_value
        ORDER BY total_spent DESC
        LIMIT 10
    ", $start_date, $end_date));
    
    // B2B Group Analysis
    $b2b_groups = b2b_get_groups();
    $group_sales = [];
    foreach($b2b_groups as $slug => $group) {
        // Get users in this group
        $users_in_group = get_users([
            'meta_key' => 'b2b_group_slug',
            'meta_value' => $slug,
            'fields' => 'ID'
        ]);
        
        if(!empty($users_in_group)) {
            $user_ids = implode(',', $users_in_group);
            $group_data = $wpdb->get_row($wpdb->prepare("
                SELECT 
                    COUNT(DISTINCT p.ID) as order_count,
                    SUM(pm_total.meta_value) as total_sales
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
                INNER JOIN {$wpdb->postmeta} pm_customer ON p.ID = pm_customer.post_id AND pm_customer.meta_key = '_customer_user'
                WHERE p.post_type = 'shop_order'
                AND p.post_status = 'wc-completed'
                AND p.post_date BETWEEN %s AND %s
                AND pm_customer.meta_value IN ($user_ids)
            ", $start_date, $end_date));
            
            $group_sales[] = [
                'name' => $group['name'],
                'orders' => $group_data->order_count ?? 0,
                'sales' => $group_data->total_sales ?? 0
            ];
        }
    }
    
    // Top Products Performance
    $top_products = $wpdb->get_results($wpdb->prepare("
        SELECT 
            pm_product.meta_value as product_id,
            SUM(pm_qty.meta_value) as quantity_sold,
            SUM(pm_total.meta_value) as revenue
        FROM {$wpdb->prefix}woocommerce_order_items oi
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta pm_product ON oi.order_item_id = pm_product.order_item_id AND pm_product.meta_key = '_product_id'
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta pm_qty ON oi.order_item_id = pm_qty.order_item_id AND pm_qty.meta_key = '_qty'
        INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta pm_total ON oi.order_item_id = pm_total.order_item_id AND pm_total.meta_key = '_line_total'
        INNER JOIN {$wpdb->posts} p ON oi.order_id = p.ID
        WHERE p.post_type = 'shop_order'
        AND p.post_status = 'wc-completed'
        AND p.post_date BETWEEN %s AND %s
        GROUP BY pm_product.meta_value
        ORDER BY revenue DESC
        LIMIT 10
    ", $start_date, $end_date));
    
    // Low Stock Alert
    $low_stock_products = $wpdb->get_results("
        SELECT p.ID, p.post_title, pm_stock.meta_value as stock
        FROM {$wpdb->posts} p
        INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock'
        WHERE p.post_type = 'product'
        AND p.post_status = 'publish'
        AND CAST(pm_stock.meta_value AS SIGNED) <= 10
        AND CAST(pm_stock.meta_value AS SIGNED) > 0
        ORDER BY CAST(pm_stock.meta_value AS SIGNED) ASC
        LIMIT 20
    ");
    
    b2b_adm_header('Reports & Analytics');
    ?>
    
    <div class="page-header">
        <h1 class="page-title">Reports & Analytics</h1>
        <div style="display:flex;gap:10px;align-items:center;">
            <select onchange="window.location.href='<?= home_url('/b2b-panel/reports') ?>?range='+this.value" style="margin:0;padding:8px 12px;">
                <option value="today" <?= selected($range, 'today') ?>>Today</option>
                <option value="7days" <?= selected($range, '7days') ?>>Last 7 Days</option>
                <option value="30days" <?= selected($range, '30days') ?>>Last 30 Days</option>
                <option value="thismonth" <?= selected($range, 'thismonth') ?>>This Month</option>
                <option value="lastmonth" <?= selected($range, 'lastmonth') ?>>Last Month</option>
                <option value="thisyear" <?= selected($range, 'thisyear') ?>>This Year</option>
            </select>
        </div>
    </div>
    
    <!-- Sales Overview -->
    <div class="card" style="margin-bottom:25px;">
        <h3 style="margin-top:0;color:#111827;"><i class="fa-solid fa-chart-line" style="color:#10b981;margin-right:10px;"></i>Sales Overview</h3>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:20px;">
            <div style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);padding:20px;border-radius:12px;color:white;">
                <div style="font-size:12px;opacity:0.9;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Total Sales</div>
                <div style="font-size:32px;font-weight:800;"><?= wc_price($sales_data->total_sales ?? 0) ?></div>
                <div style="font-size:11px;opacity:0.8;margin-top:5px;"><?= ucfirst(str_replace(['days', 'thismonth', 'lastmonth', 'thisyear'], ['Days', 'This Month', 'Last Month', 'This Year'], $range)) ?></div>
            </div>
            
            <div style="background:linear-gradient(135deg, #f093fb 0%, #f5576c 100%);padding:20px;border-radius:12px;color:white;">
                <div style="font-size:12px;opacity:0.9;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Total Orders</div>
                <div style="font-size:32px;font-weight:800;"><?= number_format($sales_data->order_count ?? 0) ?></div>
                <div style="font-size:11px;opacity:0.8;margin-top:5px;">Completed Orders</div>
            </div>
            
            <div style="background:linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);padding:20px;border-radius:12px;color:white;">
                <div style="font-size:12px;opacity:0.9;text-transform:uppercase;letter-spacing:1px;margin-bottom:5px;">Avg Order Value</div>
                <div style="font-size:32px;font-weight:800;"><?= wc_price($sales_data->avg_order_value ?? 0) ?></div>
                <div style="font-size:11px;opacity:0.8;margin-top:5px;">Per Order</div>
            </div>
        </div>
    </div>
    
    <!-- Daily Sales Trend -->
    <div class="card" style="margin-bottom:25px;">
        <h3 style="margin-top:0;"><i class="fa-solid fa-calendar-days" style="color:#3b82f6;margin-right:10px;"></i>Daily Sales Trend</h3>
        <div style="overflow-x:auto;">
            <table style="width:100%;min-width:600px;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="text-align:left;padding:12px;">Date</th>
                        <th style="text-align:center;padding:12px;">Orders</th>
                        <th style="text-align:right;padding:12px;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($daily_sales)): foreach($daily_sales as $day): ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:12px;"><strong><?= date('D, M j, Y', strtotime($day->date)) ?></strong></td>
                        <td style="text-align:center;padding:12px;">
                            <span style="background:#dbeafe;color:#1e40af;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                                <?= $day->orders ?>
                            </span>
                        </td>
                        <td style="text-align:right;padding:12px;"><strong style="color:#10b981;font-size:15px;"><?= wc_price($day->revenue) ?></strong></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="3" style="text-align:center;padding:30px;color:#9ca3af;">No sales data for this period.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Two Column Layout -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(400px, 1fr));gap:25px;margin-bottom:25px;">
        
        <!-- Top Customers -->
        <div class="card">
            <h3 style="margin-top:0;"><i class="fa-solid fa-star" style="color:#f59e0b;margin-right:10px;"></i>Top Customers</h3>
            <table style="width:100%;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="text-align:left;padding:10px;">Customer</th>
                        <th style="text-align:center;padding:10px;">Orders</th>
                        <th style="text-align:right;padding:10px;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($top_customers)): foreach($top_customers as $customer): 
                        $user = get_userdata($customer->customer_id);
                        if(!$user) continue;
                    ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:32px;height:32px;background:#e0e7ff;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#4f46e5;font-weight:700;font-size:12px;">
                                    <?= strtoupper(substr($user->display_name, 0, 1)) ?>
                                </div>
                                <div>
                                    <strong style="display:block;font-size:13px;"><?= esc_html($user->display_name) ?></strong>
                                    <small style="color:#9ca3af;"><?= esc_html($user->user_email) ?></small>
                                </div>
                            </div>
                        </td>
                        <td style="text-align:center;padding:10px;">
                            <span style="background:#f3f4f6;color:#374151;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">
                                <?= $customer->order_count ?>
                            </span>
                        </td>
                        <td style="text-align:right;padding:10px;"><strong style="color:#10b981;"><?= wc_price($customer->total_spent) ?></strong></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="3" style="text-align:center;padding:20px;color:#9ca3af;">No customer data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- B2B Group Analysis -->
        <div class="card">
            <h3 style="margin-top:0;"><i class="fa-solid fa-users-gear" style="color:#8b5cf6;margin-right:10px;"></i>B2B Group Analysis</h3>
            <table style="width:100%;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="text-align:left;padding:10px;">Group</th>
                        <th style="text-align:center;padding:10px;">Orders</th>
                        <th style="text-align:right;padding:10px;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($group_sales)): 
                        usort($group_sales, function($a, $b) { return $b['sales'] - $a['sales']; });
                        foreach($group_sales as $gs): 
                    ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:10px;"><strong><?= esc_html($gs['name']) ?></strong></td>
                        <td style="text-align:center;padding:10px;">
                            <span style="background:#e0e7ff;color:#4f46e5;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:600;">
                                <?= $gs['orders'] ?>
                            </span>
                        </td>
                        <td style="text-align:right;padding:10px;"><strong style="color:#10b981;"><?= wc_price($gs['sales']) ?></strong></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="3" style="text-align:center;padding:20px;color:#9ca3af;">No group data available.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Top Products Performance -->
    <div class="card" style="margin-bottom:25px;">
        <h3 style="margin-top:0;"><i class="fa-solid fa-trophy" style="color:#ef4444;margin-right:10px;"></i>Top Products Performance</h3>
        <table style="width:100%;">
            <thead>
                <tr style="background:#f9fafb;">
                    <th style="text-align:left;padding:12px;">Product</th>
                    <th style="text-align:center;padding:12px;">Qty Sold</th>
                    <th style="text-align:right;padding:12px;">Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!empty($top_products)): foreach($top_products as $tp): 
                    $product = wc_get_product($tp->product_id);
                    if(!$product) continue;
                ?>
                <tr style="border-bottom:1px solid #f3f4f6;">
                    <td style="padding:12px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <?php if($product->get_image_id()): ?>
                            <img src="<?= wp_get_attachment_image_url($product->get_image_id(), 'thumbnail') ?>" style="width:40px;height:40px;border-radius:6px;object-fit:cover;">
                            <?php else: ?>
                            <div style="width:40px;height:40px;background:#f3f4f6;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                <i class="fa-solid fa-image" style="color:#d1d5db;"></i>
                            </div>
                            <?php endif; ?>
                            <div>
                                <strong style="display:block;"><?= esc_html($product->get_name()) ?></strong>
                                <small style="color:#9ca3af;">SKU: <?= $product->get_sku() ?: '-' ?></small>
                            </div>
                        </div>
                    </td>
                    <td style="text-align:center;padding:12px;">
                        <span style="background:#fef3c7;color:#92400e;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;">
                            <?= number_format($tp->quantity_sold) ?>
                        </span>
                    </td>
                    <td style="text-align:right;padding:12px;"><strong style="color:#10b981;font-size:15px;"><?= wc_price($tp->revenue) ?></strong></td>
                </tr>
                <?php endforeach; else: ?>
                <tr><td colspan="3" style="text-align:center;padding:30px;color:#9ca3af;">No product sales data for this period.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Low Stock Alert -->
    <?php if(!empty($low_stock_products)): ?>
    <div class="card" style="background:#fef2f2;border-left:4px solid #ef4444;">
        <h3 style="margin-top:0;color:#991b1b;"><i class="fa-solid fa-triangle-exclamation" style="margin-right:10px;"></i>Low Stock Alert</h3>
        <p style="color:#7f1d1d;margin-bottom:15px;">The following products have low stock levels and may need reordering:</p>
        <table style="width:100%;background:white;border-radius:8px;">
            <thead>
                <tr style="background:#fee2e2;">
                    <th style="text-align:left;padding:10px;color:#991b1b;">Product</th>
                    <th style="text-align:center;padding:10px;color:#991b1b;">Stock</th>
                    <th style="text-align:right;padding:10px;color:#991b1b;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($low_stock_products as $lsp): ?>
                <tr style="border-bottom:1px solid #fee2e2;">
                    <td style="padding:10px;"><strong><?= esc_html($lsp->post_title) ?></strong></td>
                    <td style="text-align:center;padding:10px;">
                        <span style="background:#fee2e2;color:#dc2626;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;">
                            <?= $lsp->stock ?> left
                        </span>
                    </td>
                    <td style="text-align:right;padding:10px;">
                        <a href="<?= home_url('/b2b-panel/products/edit?id=' . $lsp->ID) ?>">
                            <button class="secondary" style="padding:6px 12px;font-size:12px;">Update Stock</button>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   7C. PAGE: STOCK PLANNING - SALES ANALYSIS
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'stock_planning') return;
    b2b_adm_guard();
    
    global $wpdb;
    
    // Form inputs
    $year = intval($_GET['year'] ?? date('Y'));
    $statuses = $_GET['status'] ?? ['completed', 'processing'];
    $start_date = $_GET['start_date'] ?? '';
    $end_date = $_GET['end_date'] ?? '';
    $min_supply_days = intval($_GET['min_supply_days'] ?? 80);
    $stock_threshold = intval($_GET['stock_threshold'] ?? 0);
    
    // Date range calculation
    $today = current_time('Y-m-d');
    $date_error = '';
    if ($start_date && $end_date) {
        $this_start = date_create_from_format('d.m.Y', $start_date) ?: date_create($start_date);
        $this_end = date_create_from_format('d.m.Y', $end_date) ?: date_create($end_date);
        if (!$this_start || !$this_end) {
            $date_error = 'Invalid date format. Using default year range. Please use dd.mm.yyyy format.';
            $this_start = "$year-01-01";
            $this_end = "$year-" . date('m-d', strtotime($today));
        } else {
            $this_start = $this_start->format('Y-m-d');
            $this_end = $this_end->format('Y-m-d');
        }
    } else {
        $this_start = "$year-01-01";
        $this_end = "$year-" . date('m-d', strtotime($today));
    }
    
    $date_diff = (strtotime($this_end) - strtotime($this_start)) / 86400 + 1;
    
    // Get orders
    $order_statuses = array_map(function($s){ return 'wc-'.$s; }, $statuses);
    $status_placeholders = implode(',', array_fill(0, count($order_statuses), '%s'));
    $order_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts}
         WHERE post_type = 'shop_order'
         AND post_status IN ($status_placeholders)
         AND post_date >= %s AND post_date <= %s",
        ...array_merge($order_statuses, [$this_start . " 00:00:00", $this_end . " 23:59:59"])
    ));
    
    $detail = [];
    $total_net = $total_tax = $total_gross = 0;
    
    if (!empty($order_ids)) {
        $placeholders = implode(',', array_fill(0, count($order_ids), '%d'));
        
        // Get order items
        $order_items = $wpdb->get_results($wpdb->prepare(
            "SELECT oi.order_item_id, oi.order_id,
                oim_product.meta_value as product_id,
                oim_variation.meta_value as variation_id,
                oim_qty.meta_value as qty,
                oim_total.meta_value as net,
                oim_tax.meta_value as tax
             FROM {$wpdb->prefix}woocommerce_order_items oi
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_product ON oi.order_item_id = oim_product.order_item_id AND oim_product.meta_key = '_product_id'
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_variation ON oi.order_item_id = oim_variation.order_item_id AND oim_variation.meta_key = '_variation_id'
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_qty ON oi.order_item_id = oim_qty.order_item_id AND oim_qty.meta_key = '_qty'
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_total ON oi.order_item_id = oim_total.order_item_id AND oim_total.meta_key = '_line_total'
             LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_tax ON oi.order_item_id = oim_tax.order_item_id AND oim_tax.meta_key = '_line_tax'
             WHERE oi.order_id IN ($placeholders)
             AND oi.order_item_type = 'line_item'",
            $order_ids
        ));
        
        // Process items
        foreach ($order_items as $item) {
            $pid = $item->variation_id && $item->variation_id != '0' ? intval($item->variation_id) : intval($item->product_id);
            if (!$pid) continue;
            
            $product = wc_get_product($pid);
            if (!$product) continue;
            
            $sku = $product->get_sku() ?: $pid;
            $name = $product->get_name();
            $cat = implode(', ', wp_get_post_terms($pid, 'product_cat', ['fields' => 'names'])) ?: '-';
            
            if (!isset($detail[$sku])) {
                $detail[$sku] = [
                    'sku' => $sku,
                    'product_id' => $pid,
                    'name' => $name,
                    'category' => $cat,
                    'qty' => 0,
                    'net' => 0,
                    'tax' => 0,
                    'gross' => 0,
                    'stock' => $product->get_stock_quantity() ?: 0,
                    'ordered_qty' => 0,
                    'ordered_note' => '',
                    'received' => 0,
                ];
            }
            
            $qty = floatval($item->qty);
            $net = floatval($item->net);
            $tax = floatval($item->tax);
            $gross = $net + $tax;
            
            $detail[$sku]['qty'] += $qty;
            $detail[$sku]['net'] += $net;
            $detail[$sku]['tax'] += $tax;
            $detail[$sku]['gross'] += $gross;
            
            $total_net += $net;
            $total_tax += $tax;
            $total_gross += $gross;
        }
    }
    
    // Get supplier orders
    $sup_list = b2b_get_supplier_orders();
    foreach ($sup_list as $sup) {
        $sku = $sup['sku'];
        
        if (!isset($detail[$sku])) {
            $detail[$sku] = [
                'sku' => $sku,
                'product_id' => intval($sup['product_id']),
                'name' => $sup['name'],
                'category' => '',
                'qty' => 0,
                'net' => 0,
                'tax' => 0,
                'gross' => 0,
                'stock' => 0,
                'ordered_qty' => 0,
                'ordered_note' => '',
                'received' => intval($sup['received']),
            ];
        }
        
        $detail[$sku]['ordered_qty'] += intval($sup['ordered_qty']);
        $detail[$sku]['ordered_note'] .= ($detail[$sku]['ordered_note'] ? ' | ' : '') . ($sup['order_date'] ? $sup['order_date'] : '') . ($sup['note'] ? ': ' . $sup['note'] : '');
        $detail[$sku]['received'] = intval($sup['received']);
    }
    
    // Calculate widgets
    $zero_stock = $supply_passed = $supply_soon = 0;
    $auto_supply_items = [];
    
    foreach ($detail as $r) {
        $total_stock = $r['stock'] + $r['ordered_qty'];
        $avg = $date_diff > 0 ? round($r['qty'] / $date_diff, 3) : 0;
        $left = $avg > 0 ? round($total_stock / $avg, 1) : 999;
        
        if ($total_stock <= 0) $zero_stock++;
        if ($avg > 0 && $left < $min_supply_days) {
            $supply_passed++;
            if (empty($r['ordered_qty']) && !$r['received']) {
                $auto_supply_items[] = [
                    'sku' => $r['sku'],
                    'product_id' => $r['product_id'],
                    'name' => $r['name'],
                    'ordered_qty' => max(1, round($avg * $min_supply_days)),
                ];
            }
        }
        if ($avg > 0 && $left >= $min_supply_days && $left < ($min_supply_days + 10)) $supply_soon++;
    }
    
    b2b_adm_header('Stock Planning - Sales Analysis');
    ?>
    
    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <div class="page-header">
        <h1 class="page-title">Stock Planning - Sales Analysis</h1>
    </div>
    
    <?php if ($date_error): ?>
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;padding:12px;border-radius:4px;margin-bottom:20px;">
        ⚠️ <?= esc_html($date_error) ?>
    </div>
    <?php endif; ?>
    
    <!-- Filter Form -->
    <form method="get" style="margin-bottom:20px;padding:15px;background:#f9f9f9;border:1px solid #ddd;display:flex;flex-wrap:wrap;gap:20px;align-items:flex-end;">
        <input type="hidden" name="b2b_adm_page" value="stock_planning">
        
        <div>
            <label style="display:block;margin-bottom:5px;font-weight:600;">Year</label>
            <select name="year" style="padding:8px;border:1px solid #ddd;border-radius:4px;">
                <?php for($y = date('Y'); $y >= 2020; $y--): ?>
                <option value="<?= $y ?>" <?= selected($year, $y, false) ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div>
            <label style="display:block;margin-bottom:5px;font-weight:600;">Start Date</label>
            <input type="text" name="start_date" value="<?= esc_attr($start_date) ?>" placeholder="dd.mm.yyyy" style="padding:8px;border:1px solid #ddd;border-radius:4px;">
        </div>
        
        <div>
            <label style="display:block;margin-bottom:5px;font-weight:600;">End Date</label>
            <input type="text" name="end_date" value="<?= esc_attr($end_date) ?>" placeholder="dd.mm.yyyy" style="padding:8px;border:1px solid #ddd;border-radius:4px;">
        </div>
        
        <div>
            <label style="display:block;margin-bottom:5px;font-weight:600;">Order Status</label>
            <div class="multi-select-wrapper" id="orderStatusMultiSelect">
                <div class="multi-select-display" onclick="toggleMultiSelect()">
                    <div class="selected-items" id="selectedItemsDisplay">
                        <?php if(empty($statuses)): ?>
                        <span style="color:#9ca3af;">Select order statuses...</span>
                        <?php else: 
                            foreach($statuses as $s):
                                $label = wc_get_order_statuses()['wc-'.$s] ?? $s;
                        ?>
                        <span class="selected-item-badge" data-value="<?= $s ?>">
                            <?= esc_html($label) ?>
                            <span class="remove" onclick="removeStatus(event, '<?= $s ?>')">×</span>
                        </span>
                        <?php endforeach; endif; ?>
                    </div>
                    <i class="fa-solid fa-chevron-down" style="color:#6c757d;"></i>
                </div>
                <div class="multi-select-dropdown" id="orderStatusDropdown">
                    <?php foreach (wc_get_order_statuses() as $key => $label): 
                        $s = str_replace('wc-', '', $key); 
                        $checked = in_array($s, $statuses) ? 'checked' : '';
                    ?>
                    <div class="multi-select-option">
                        <input type="checkbox" 
                               name="status[]" 
                               value="<?= $s ?>" 
                               id="status_<?= $s ?>"
                               <?= $checked ?>
                               onchange="updateSelectedItems()">
                        <label for="status_<?= $s ?>"><?= $label ?></label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <div>
            <label style="display:block;margin-bottom:5px;font-weight:600;">Min Supply Days</label>
            <input type="number" name="min_supply_days" value="<?= esc_attr($min_supply_days) ?>" style="width:100px;padding:8px;border:1px solid #ddd;border-radius:4px;">
        </div>
        
        <button class="button button-primary" style="height:38px;padding:0 20px;">Generate Report</button>
        <a href="<?= home_url('/b2b-panel/stock-planning/supplier-orders') ?>" class="button" style="height:38px;padding:10px 20px;text-decoration:none;">Go to Supplier Orders</a>
    </form>
    
    <!-- Summary Widgets -->
    <div style="display:flex;gap:20px;margin-bottom:25px;flex-wrap:wrap;">
        <div style="background:#fff;border:1px solid #ddd;padding:18px;flex:1;text-align:center;min-width:160px;">
            <div style="font-size:14px;color:#888;">Net</div>
            <div style="font-size:2em;font-weight:600;"><?= wc_price($total_net) ?></div>
        </div>
        <div style="background:#fff;border:1px solid #ddd;padding:18px;flex:1;text-align:center;min-width:160px;">
            <div style="font-size:14px;color:#888;">Tax</div>
            <div style="font-size:2em;font-weight:600;"><?= wc_price($total_tax) ?></div>
        </div>
        <div style="background:#fff;border:1px solid #ddd;padding:18px;flex:1;text-align:center;min-width:160px;">
            <div style="font-size:14px;color:#888;">Gross</div>
            <div style="font-size:2em;font-weight:600;"><?= wc_price($total_gross) ?></div>
        </div>
    </div>
    
    <div style="display:flex;gap:20px;margin-bottom:25px;flex-wrap:wrap;">
        <div style="background:#f8f8f8;border:1px solid #ddd;padding:18px;flex:1;text-align:center;min-width:160px;">
            <div style="font-size:14px;color:#888;">Zero Stock Products</div>
            <div style="font-size:2em;font-weight:600;"><?= $zero_stock ?></div>
        </div>
        <div style="background:#fbeaea;border:1px solid #e99;padding:18px;flex:1;text-align:center;min-width:160px;">
            <div style="font-size:14px;color:#c00;">Supply Passed</div>
            <div style="font-size:2em;font-weight:600;"><?= $supply_passed ?></div>
        </div>
        <div style="background:#fffbe9;border:1px solid #e9c;padding:18px;flex:1;text-align:center;min-width:160px;">
            <div style="font-size:14px;color:#e9a500;">Supply < 10 days</div>
            <div style="font-size:2em;font-weight:600;"><?= $supply_soon ?></div>
        </div>
    </div>
    
    <!-- Auto Supply Button -->
    <?php if (!empty($auto_supply_items)): 
        $nonce = wp_create_nonce('b2b_add_supply_bulk'); ?>
    <button type="button" class="button b2b-add-supply-btn" style="margin-bottom:20px;background:#e77;color:white;padding:10px 24px;font-size:1.1em;" data-items='<?= json_encode($auto_supply_items) ?>' data-nonce="<?= $nonce ?>">
        Add <?= count($auto_supply_items) ?> Products to Supplier Orders
    </button>
    <?php endif; ?>
    
    <!-- Export Button -->
    <button onclick="exportStockPlanningCsv()" class="button" style="margin-bottom:10px;">Export CSV</button>
    
    <!-- Data Table -->
    <table id="stock-planning-table" class="display widefat" style="width:100%;">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Category</th>
                <th>Qty Sold</th>
                <th>Revenue</th>
                <th>Stock</th>
                <th>Ordered Qty</th>
                <th>Order Note</th>
                <th>Days</th>
                <th>Avg/Day</th>
                <th>Days Left</th>
                <th>Gap</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detail as $r): 
                $total_stock = $r['stock'] + $r['ordered_qty'];
                $avg = $date_diff > 0 ? round($r['qty'] / $date_diff, 3) : 0;
                $left = $avg > 0 ? round($total_stock / $avg) : '-';
                $gap = is_numeric($left) ? $left - $min_supply_days : '-';
                
                $style = '';
                if ($total_stock <= 0) {
                    $style = ' style="background:#ffd5d5"';
                } elseif (is_numeric($left) && $left < $min_supply_days) {
                    $style = ' style="background:#ffbbbb"';
                } elseif (is_numeric($left) && $left >= $min_supply_days && $left < ($min_supply_days + 10)) {
                    $style = ' style="background:#fffbe9"';
                }
                
                $received_class = $r['received'] ? ' class="stock-received"' : '';
            ?>
            <tr<?= $style ?><?= $received_class ?>>
                <td><?= esc_html($r['sku']) ?></td>
                <td><?= esc_html($r['name']) ?></td>
                <td><?= esc_html($r['category']) ?></td>
                <td><?= $r['qty'] ?></td>
                <td><?= wc_price($r['gross']) ?></td>
                <td><?= $r['stock'] ?></td>
                <td><?= $r['ordered_qty'] ?></td>
                <td><?= esc_html($r['ordered_note']) ?></td>
                <td><?= $date_diff ?></td>
                <td><?= $avg ?></td>
                <td><?= $left ?></td>
                <td><?= $gap ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <script>
    jQuery(function($) {
        // Initialize DataTable
        $('#stock-planning-table').DataTable({
            pageLength: 25,
            order: [[10, 'asc']], // Sort by Days Left
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json' }
        });
        
        // Add supply bulk button handler
        $('.b2b-add-supply-btn').on('click', function() {
            var items = $(this).data('items');
            var nonce = $(this).data('nonce');
            
            $.post('<?= admin_url('admin-ajax.php') ?>', {
                action: 'b2b_add_supply_bulk',
                items: items,
                _b2b_nonce: nonce
            }, function(resp) {
                if (resp.success) {
                    alert('Products added to supplier orders!');
                    window.location.href = '<?= home_url('/b2b-panel/stock-planning/supplier-orders') ?>';
                } else {
                    alert('Error occurred!');
                }
            });
        });
    });
    
    // Multi-Select Dropdown Functions
    function toggleMultiSelect() {
        const dropdown = document.getElementById('orderStatusDropdown');
        const display = document.querySelector('.multi-select-display');
        dropdown.classList.toggle('active');
        display.classList.toggle('active');
    }
    
    function updateSelectedItems() {
        const display = document.getElementById('selectedItemsDisplay');
        const checkboxes = document.querySelectorAll('#orderStatusDropdown input[type="checkbox"]');
        const selected = [];
        
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) {
                const label = checkbox.nextElementSibling.textContent;
                selected.push({
                    value: checkbox.value,
                    label: label
                });
            }
        });
        
        if (selected.length === 0) {
            display.innerHTML = '<span style="color:#9ca3af;">Select order statuses...</span>';
        } else {
            display.innerHTML = selected.map(item => 
                `<span class="selected-item-badge" data-value="${item.value}">
                    ${item.label}
                    <span class="remove" onclick="removeStatus(event, '${item.value}')">×</span>
                </span>`
            ).join('');
        }
    }
    
    function removeStatus(event, value) {
        event.stopPropagation();
        const checkbox = document.getElementById('status_' + value);
        if (checkbox) {
            checkbox.checked = false;
            updateSelectedItems();
        }
    }
    
    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const wrapper = document.getElementById('orderStatusMultiSelect');
        if (wrapper && !wrapper.contains(event.target)) {
            const dropdown = document.getElementById('orderStatusDropdown');
            const display = document.querySelector('.multi-select-display');
            if (dropdown) dropdown.classList.remove('active');
            if (display) display.classList.remove('active');
        }
    });
    
    function exportStockPlanningCsv() {
        var csv = [];
        var rows = document.querySelectorAll("#stock-planning-table tr");
        for (var i = 0; i < rows.length; i++) {
            var row = [], cols = rows[i].querySelectorAll("td, th");
            for (var j = 0; j < cols.length; j++)
                row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
            csv.push(row.join(","));
        }
        var csvContent = "data:text/csv;charset=utf-8," + csv.join("\n");
        var link = document.createElement("a");
        link.setAttribute("href", encodeURI(csvContent));
        link.setAttribute("download", "stock-planning-report.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
    
    <style>
    .stock-received {
        background: #e0f7e1 !important;
        opacity: 0.7;
    }
    </style>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   7D. PAGE: SUPPLIER ORDERS MANAGEMENT
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'supplier_orders') return;
    b2b_adm_guard();
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_supplier_orders';
    
    // Handle form add
    if (!empty($_POST['b2b_supplier_add'])) {
        check_admin_referer('b2b_supplier_add', 'b2b_supplier_nonce');
        
        $sku = sanitize_text_field($_POST['sku']);
        $name = sanitize_text_field($_POST['name']);
        $product_id = intval($_POST['product_id']);
        $ordered_qty = intval($_POST['ordered_qty']);
        $order_date = sanitize_text_field($_POST['order_date']);
        $note = sanitize_textarea_field($_POST['note']);
        $user = wp_get_current_user()->user_login;
        
        if ($sku && $ordered_qty > 0) {
            $data = [
                'id' => uniqid('sup_', true),
                'sku' => $sku,
                'product_id' => $product_id,
                'name' => $name,
                'ordered_qty' => $ordered_qty,
                'order_date' => $order_date,
                'note' => $note,
                'added_by' => $user,
                'added_at' => current_time('mysql'),
                'received' => 0,
            ];
            
            b2b_save_supplier_order($data);
            echo '<div class="notice notice-success"><p>Supplier order added successfully.</p></div>';
        }
    }
    
    // Handle delete
    if (!empty($_GET['delete']) && !empty($_GET['id'])) {
        check_admin_referer('b2b_supplier_del_' . $_GET['id']);
        b2b_delete_supplier_order(sanitize_text_field($_GET['id']));
        echo '<div class="notice notice-success"><p>Supplier order deleted.</p></div>';
    }
    
    // Handle edit
    if (isset($_POST['b2b_supplier_edit'])) {
        check_admin_referer('b2b_supplier_edit', 'b2b_supplier_nonce_edit');
        
        $item_id = sanitize_text_field($_POST['edit_item_id']);
        $new_quantity = intval($_POST['edit_ordered_qty']);
        
        if ($new_quantity > 0) {
            $wpdb->update($table_name, ['ordered_qty' => $new_quantity], ['id' => $item_id]);
            echo '<div class="notice notice-success"><p>Supplier order updated.</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>Quantity must be greater than zero.</p></div>';
        }
    }
    
    // Get all supplier orders
    $list = b2b_get_supplier_orders();
    
    b2b_adm_header('Supplier Orders Management');
    ?>
    
    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    
    <div class="page-header">
        <h1 class="page-title">Supplier Orders Management</h1>
    </div>
    
    <p style="margin-bottom:20px;">Add products to this list for which you placed a supplier order. These will be reflected in the stock planning report and considered in supply calculations.</p>
    
    <!-- SKU Search & Add Form -->
    <form method="post" style="margin-bottom:30px;padding:20px;background:#f7f7f7;border:1px solid #ddd;border-radius:8px;max-width:100%;position:relative;">
        <?php wp_nonce_field('b2b_supplier_add', 'b2b_supplier_nonce'); ?>
        <h3 style="margin-top:0;">Add Product to Supplier Order</h3>
        <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end;">
            <div style="position:relative;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">SKU</label>
                <input type="text" id="b2b-product-sku-search" name="sku" placeholder="Search SKU" autocomplete="off" required style="width:140px;padding:8px;border:1px solid #ddd;border-radius:4px;">
                <div id="b2b-product-search-results" style="display:none;position:absolute;background:#fff;border:1px solid #ccc;max-height:200px;overflow:auto;z-index:99;width:220px;"></div>
            </div>
            <div>
                <label style="display:block;margin-bottom:5px;font-weight:600;">Product Name</label>
                <input type="text" id="b2b-product-name" name="name" placeholder="Product Name" style="width:200px;padding:8px;border:1px solid #ddd;border-radius:4px;" readonly>
            </div>
            <input type="hidden" id="b2b-product-id" name="product_id" value="">
            <div>
                <label style="display:block;margin-bottom:5px;font-weight:600;">Ordered Qty</label>
                <input type="number" name="ordered_qty" placeholder="Qty" min="1" required style="width:100px;padding:8px;border:1px solid #ddd;border-radius:4px;">
            </div>
            <div>
                <label style="display:block;margin-bottom:5px;font-weight:600;">Order Date</label>
                <input type="date" name="order_date" style="width:150px;padding:8px;border:1px solid #ddd;border-radius:4px;" value="<?= date('Y-m-d') ?>">
            </div>
            <div>
                <label style="display:block;margin-bottom:5px;font-weight:600;">Note</label>
                <input type="text" name="note" placeholder="Optional note" style="width:180px;padding:8px;border:1px solid #ddd;border-radius:4px;">
            </div>
            <button name="b2b_supplier_add" class="button button-primary" style="height:38px;padding:0 20px;">Add Order</button>
        </div>
    </form>
    
    <!-- Supplier Orders Table -->
    <h2>Supplier Orders List</h2>
    <table id="supplier-orders-table" class="display widefat" style="width:100%;">
        <thead>
            <tr>
                <th>SKU</th>
                <th>Name</th>
                <th>Ordered Qty</th>
                <th>Order Date</th>
                <th>Note</th>
                <th>Added By</th>
                <th>Added At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $row): ?>
            <tr <?= $row['received'] ? 'style="background:#e0f7e1;opacity:0.7;"' : '' ?>>
                <td><?= esc_html($row['sku']) ?></td>
                <td><?= esc_html($row['name']) ?></td>
                <td>
                    <?php if (!$row['received']): ?>
                    <form method="post" style="display:inline-block;">
                        <?php wp_nonce_field('b2b_supplier_edit', 'b2b_supplier_nonce_edit'); ?>
                        <input type="hidden" name="edit_item_id" value="<?= esc_attr($row['id']) ?>">
                        <input type="number" name="edit_ordered_qty" value="<?= esc_attr($row['ordered_qty']) ?>" style="width:70px;padding:4px;">
                        <button type="submit" name="b2b_supplier_edit" class="button button-small">Save</button>
                    </form>
                    <?php else: ?>
                    <?= esc_html($row['ordered_qty']) ?>
                    <?php endif; ?>
                </td>
                <td><?= esc_html($row['order_date']) ?></td>
                <td><?= esc_html($row['note']) ?></td>
                <td><?= esc_html($row['added_by']) ?></td>
                <td><?= esc_html($row['added_at']) ?></td>
                <td>
                    <?php if (!$row['received']): 
                        $nonce = wp_create_nonce('b2b_mark_received'); ?>
                    <button class="button b2b-mark-received-btn" data-item-id="<?= esc_attr($row['id']) ?>" data-nonce="<?= esc_attr($nonce) ?>">Mark Received</button>
                    <a href="<?= esc_url(add_query_arg(['delete' => 1, 'id' => $row['id']])) ?>&_wpnonce=<?= wp_create_nonce('b2b_supplier_del_' . $row['id']) ?>" class="button" onclick="return confirm('Are you sure?');">Delete</a>
                    <?php else: ?>
                    <span style="color:green;font-weight:600;">✓ Received</span>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <script>
    jQuery(function($) {
        // Initialize DataTable
        $('#supplier-orders-table').DataTable({
            pageLength: 25,
            order: [[0, 'asc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/en-GB.json' }
        });
        
        // SKU Search
        $('#b2b-product-sku-search').on('input', function() {
            var val = $(this).val();
            if (val.length < 2) {
                $('#b2b-product-search-results').hide().empty();
                return;
            }
            
            $.post('<?= admin_url('admin-ajax.php') ?>', {
                action: 'b2b_search_sku',
                term: val
            }, function(data) {
                var out = '';
                if (data.length) {
                    data.forEach(function(row) {
                        out += '<div class="b2b-search-result" style="padding:8px;cursor:pointer;border-bottom:1px solid #eee;" data-sku="' + row.sku + '" data-name="' + row.name + '" data-product-id="' + row.id + '">' + row.sku + ' - ' + row.name + '</div>';
                    });
                } else {
                    out = '<div style="padding:8px;">No product found</div>';
                }
                $('#b2b-product-search-results').html(out).show();
            });
        });
        
        $(document).on('click', '.b2b-search-result', function() {
            var sku = $(this).attr('data-sku');
            var name = $(this).attr('data-name');
            var productId = $(this).attr('data-product-id');
            $('#b2b-product-sku-search').val(sku);
            $('#b2b-product-name').val(name);
            $('#b2b-product-id').val(productId);
            $('#b2b-product-search-results').hide().empty();
        });
        
        // Mark as received
        $('.b2b-mark-received-btn').on('click', function() {
            var itemId = $(this).data('item-id');
            var nonce = $(this).data('nonce');
            
            if (!confirm('Mark this order as received? This will update the product stock.')) {
                return;
            }
            
            $.post('<?= admin_url('admin-ajax.php') ?>', {
                action: 'b2b_mark_received',
                item_id: itemId,
                _b2b_nonce: nonce
            }, function(resp) {
                if (resp.success) {
                    alert('Order marked as received and stock updated!');
                    window.location.reload();
                } else {
                    alert('Error: ' + (resp.data || 'Unknown error'));
                }
            });
        });
    });
    </script>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   8. PAGE: ORDERS (WAREHOUSE STYLE + WIDE MODAL)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'orders') return;
    b2b_adm_guard();
    b2b_adm_header('Orders');

    $paged = max(1, $_GET['paged'] ?? 1);
    $status = $_GET['status'] ?? '';
    $s = $_GET['s'] ?? '';
    
    $args = ['post_type'=>'shop_order', 'post_status'=>'any', 'posts_per_page'=>20, 'paged'=>$paged, 'orderby'=>'date', 'order'=>'DESC'];
    if($status) $args['post_status'] = 'wc-'.$status;
    if($s && is_numeric($s)) $args['post__in'] = [intval($s)];
    
    $query = new WP_Query($args);
    $all_statuses = wc_get_order_statuses();

    // Stats
    global $wpdb;
    $stat_count = $query->found_posts;
    $stat_label = $status ? wc_get_order_status_name($status) : 'All Orders';
    $oldest_date = '-';
    if ($stat_count > 0 && $status != 'completed') {
        $status_sql = $status ? "AND post_status = 'wc-$status'" : "AND post_status != 'auto-draft'";
        $oldest = $wpdb->get_var("SELECT post_date FROM {$wpdb->posts} WHERE post_type='shop_order' $status_sql ORDER BY post_date ASC LIMIT 1");
        if($oldest) $oldest_date = date('d.m.Y', strtotime($oldest));
    }

    // Btn Helper
    function adm_wh_btn($oid, $wh) {
        $app = get_post_meta($oid, '_'.$wh.'_approved', true) === '1';
        $col = $app ? '#10b981' : '#ef4444'; $txt = $app ? 'Approved' : 'Pending';
        $note = esc_attr(get_post_meta($oid, '_'.$wh.'_notes', true));
        return "<button class='wh-btn' style='background:$col;width:100%;font-size:11px;padding:5px' data-id='$oid' data-wh='$wh' data-note='$note'>$txt</button>";
    }
    
    // QB Status Helper
    function adm_qb_status($oid) {
        // Check various meta keys that MyWorks QB Online plugin might use
        $qb_invoice = get_post_meta($oid, '_qbo_invoice_id', true) 
                   ?: get_post_meta($oid, '_quickbooks_invoice_id', true);
        $qb_synced = get_post_meta($oid, '_qbo_synced', true) 
                  ?: get_post_meta($oid, 'myworks_qbo_synced', true);
        $qb_sync_status = get_post_meta($oid, '_qbo_sync_status', true);
        
        // Determine if synced
        $is_synced = false;
        if ($qb_invoice || $qb_synced == '1' || $qb_synced == 'yes' || $qb_sync_status == 'synced') {
            $is_synced = true;
        }
        
        $col = $is_synced ? '#10b981' : '#94a3b8';
        $icon = $is_synced ? 'fa-circle-check' : 'fa-circle-xmark';
        $txt = $is_synced ? 'Synced' : 'Not Synced';
        
        return "<div style='display:flex;align-items:center;gap:6px;font-size:12px;color:$col;font-weight:600'><i class='fa-solid $icon'></i> $txt</div>";
    }
    ?>

    <!-- WIDER MODAL CSS -->
    <style>
        .modal-content { max-width: 900px !important; } 
    </style>

    <div class="card">
        <!-- Filter Bar -->
        <div style="display:flex;gap:15px;margin-bottom:20px;flex-wrap:wrap;justify-content:space-between">
            <div style="display:flex;gap:10px">
                <select style="max-width:200px" onchange="location.href='?b2b_adm_page=orders&status='+this.value">
                    <option value="">All Statuses</option>
                    <?php foreach($all_statuses as $k=>$v): $slug=str_replace('wc-','',$k); ?><option value="<?=$slug?>" <?=selected($status,$slug)?>><?=$v?></option><?php endforeach; ?>
                </select>
                <div class="col-toggler">
                    <button type="button" class="secondary" onclick="document.querySelector('#colDrop').classList.toggle('active')"><i class="fa-solid fa-table-columns"></i> Columns</button>
                    <div id="colDrop" class="col-dropdown">
                        <label><input type="checkbox" checked data-col="0"> No</label>
                        <label><input type="checkbox" checked data-col="1"> Date</label>
                        <label><input type="checkbox" checked data-col="2"> Customer</label>
                        <label><input type="checkbox" checked data-col="3"> Wh. A</label>
                        <label><input type="checkbox" checked data-col="4"> Wh. B</label>
                        <label><input type="checkbox" checked data-col="5"> QB Status</label>
                        <label><input type="checkbox" checked data-col="6"> Status</label>
                        <label><input type="checkbox" checked data-col="7"> Action</label>
                    </div>
                </div>
            </div>
            <form style="display:flex;gap:10px;">
                <input name="s" placeholder="Order ID" value="<?=esc_attr($s)?>" style="margin:0;max-width:200px"><button>Search</button>
            </form>
        </div>

        <!-- Header Stats -->
        <div class="stats-box">
            <div class="stat-item"><span class="stat-label">Status</span><span class="stat-val"><?= $stat_label ?></span></div>
            <div style="width:1px;height:30px;background:#dbeafe"></div>
            <div class="stat-item"><span class="stat-label">Total Qty</span><span class="stat-val"><?= $stat_count ?></span></div>
            <?php if($oldest_date != '-'): ?>
            <div style="width:1px;height:30px;background:#dbeafe"></div>
            <div class="stat-item"><span class="stat-label">Oldest Order</span><span class="stat-val stat-oldest"><?= $oldest_date ?></span></div>
            <?php endif; ?>
        </div>

        <table id="orderTable">
            <thead><tr>
                <th data-col="0">No</th>
                <th data-col="1">Date</th>
                <th data-col="2">Customer / Address</th>
                <th data-col="3">Wh. A</th>
                <th data-col="4">Wh. B</th>
                <th data-col="5">QB Status</th>
                <th data-col="6">Status</th>
                <th data-col="7" style="text-align:right">Action</th>
            </tr></thead>
            <tbody>
            <?php if($query->have_posts()): while($query->have_posts()): $query->the_post(); $oid=get_the_ID(); $o=wc_get_order($oid); if(!$o) continue; 
                $pdf_btn = ''; if (class_exists('WPO_WCPDF')) { $n = wp_create_nonce('generate_wpo_wcpdf'); $u = admin_url("admin-ajax.php?action=generate_wpo_wcpdf&document_type=packing-slip&order_ids={$oid}&_wpnonce={$n}"); $pdf_btn = '<a href="'.$u.'" target="_blank" class="button secondary" style="padding:6px 10px;border-radius:4px;color:#374151;text-decoration:none"><i class="fa-solid fa-print"></i></a>'; }
            ?>
            <tr id="row-<?=$oid?>">
                <td data-col="0">#<?=$oid?></td>
                <td data-col="1"><?=$o->get_date_created()->date('d.m H:i')?></td>
                <td data-col="2"><strong><?=$o->get_formatted_billing_full_name()?></strong><br><small style="color:#9ca3af"><?=$o->get_billing_city()?></small></td>
                <td data-col="3"><?= adm_wh_btn($oid, 'warehouse_a') ?></td>
                <td data-col="4"><?= adm_wh_btn($oid, 'warehouse_b') ?></td>
                <td data-col="5"><?= adm_qb_status($oid) ?></td>
                <td data-col="6" style="width:160px">
                    <select onchange="updateStatus(<?=$oid?>, this.value)" style="padding:5px;font-size:12px;margin:0">
                        <?php foreach($all_statuses as $k=>$v): $slug=str_replace('wc-','',$k); ?><option value="<?=$slug?>" <?=selected('wc-'.$o->get_status(),$k)?>><?=$v?></option><?php endforeach; ?>
                    </select>
                </td>
                <td data-col="7" style="text-align:right;display:flex;gap:5px;justify-content:flex-end">
                    <button class="secondary" onclick="viewOrder(<?=$oid?>)" style="padding:6px 10px"><i class="fa-regular fa-eye"></i></button>
                    <?=$pdf_btn?>
                </td>
            </tr>
            <?php endwhile; else: ?><tr><td colspan="8" style="padding:20px;text-align:center">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
        <?php if($query->max_num_pages > 1) echo "<div style='margin-top:20px;text-align:center'>".paginate_links(['base'=>add_query_arg('paged','%#%'),'format'=>'','current'=>$paged,'total'=>$query->max_num_pages])."</div>"; ?>
    </div>

    <div id="ordModal" class="modal"><div class="modal-content"><div style="padding:15px;border-bottom:1px solid #eee;display:flex;justify-content:space-between"><h3>Details</h3><span onclick="$('#ordModal').hide()" style="cursor:pointer;font-size:20px">&times;</span></div><div id="mBody" style="padding:20px;max-height:80vh;overflow-y:auto"></div></div></div>

    <script>
    var ajaxUrl = '<?= admin_url('admin-ajax.php') ?>';
    
    // Column Toggler
    function toggleCol(idx, show) { var rows = document.getElementById('orderTable').rows; for(var i=0;i<rows.length;i++) { if(rows[i].cells.length>idx) rows[i].cells[idx].style.display=show?'':'none'; } }
    document.querySelectorAll('#colDrop input').forEach(function(cb, index){ cb.addEventListener('change', function(){ toggleCol(index, this.checked); }); });

    // Update Status
    function updateStatus(id, st) {
        if(!confirm('Update status?')) return;
        $.post(ajaxUrl, {action:'b2b_adm_update_status', order_id:id, status:st}, function(r){
            if(r.success) { 
                alert('Updated!');
                var currentFilter = '<?= $status ?>';
                if(currentFilter && currentFilter !== st) {
                    $('#row-'+id).fadeOut(500, function(){ $(this).remove(); });
                }
            } else alert('Error');
        });
    }

    // Warehouse Buttons
    $(document).on('click', '.wh-btn', function() {
        var b = $(this); var note = prompt("Admin Note:", b.data('note')); if(note===null) return;
        b.prop('disabled',true);
        $.post(ajaxUrl, {action:'b2b_adm_wh_update', order_id:b.data('id'), warehouse:b.data('wh'), note:note}, function(r){
            b.prop('disabled',false);
            if(r.success) { if(r.data.new_state) b.css('background','#10b981').text('Approved'); else b.css('background','#ef4444').text('Pending'); }
        });
    });

    // View Modal
    function viewOrder(id) {
        $('#ordModal').css('display','flex'); $('#mBody').html('Loading...');
        $.get(ajaxUrl, {action:'b2b_adm_get_details', order_id:id}, function(r){
            if(r.success) {
                var d=r.data;
                var h = `
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px">
                    <div style="background:#f9f9f9;padding:10px"><strong>Billing:</strong><br>${d.billing}</div>
                    <div style="background:#f9f9f9;padding:10px"><strong>Shipping:</strong><br>${d.shipping}</div>
                </div>
                
                ${d.extra_html}

                <table style="border:1px solid #eee"><thead><tr><th>Product</th><th>Qty</th></tr></thead><tbody>${d.items.map(i=>`<tr><td>${i.name}<br><small>${i.sku}</small></td><td>${i.qty}</td></tr>`).join('')}</tbody></table>
                <h3 style="text-align:right;margin-top:10px">${d.grand_total}</h3>
                `;
                $('#mBody').html(h);
            }
        });
    }
    $(window).click(function(e){if(e.target.id=='ordModal')$('#ordModal').hide();});
    </script>
    <?php b2b_adm_footer(); exit;
});
/* =====================================================
   9. PAGE: PRODUCTS (ENHANCED WITH FILTERS & COLUMNS)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'products') return;
    b2b_adm_guard();
    
    // Enhanced Search & Filter Logic
    $s = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $cat = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $stock_status = isset($_GET['stock_status']) ? sanitize_text_field($_GET['stock_status']) : '';
    $paged = max(1, get_query_var('paged') ?: (isset($_GET['paged']) ? intval($_GET['paged']) : 1));
    $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
    $per_page = in_array($per_page, [10, 20, 50, 100]) ? $per_page : 20; // Validate per_page value
    
    $args = ['limit' => $per_page, 'paginate' => true, 'page' => $paged];
    if ($s) $args['s'] = $s;
    if ($cat) {
        // Get category slug from term ID for WooCommerce query
        $cat_term = get_term($cat, 'product_cat');
        if ($cat_term && !is_wp_error($cat_term)) {
            $args['category'] = [$cat_term->slug];
        }
    }
    if ($stock_status) $args['stock_status'] = $stock_status;
    
    $products = wc_get_products($args);
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);
    
    b2b_adm_header('Product Management');
    ?>
    <div class="page-header">
        <h1 class="page-title">Products</h1>
        <div style="display:flex;gap:10px;">
            <a href="<?= home_url('/b2b-panel/products/add-new') ?>">
                <button class="primary" style="display:flex;align-items:center;gap:8px;">
                    <i class="fa-solid fa-plus"></i> Add New Product
                </button>
            </a>
            <button id="quickEditToggle" onclick="toggleQuickEdit()" class="secondary" style="display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-bolt"></i> Quick Edit Stock
            </button>
        </div>
    </div>
    <div class="card">
        <!-- Enhanced Filter Bar -->
        <div style="display:flex;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:15px;">
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <!-- Category Filter -->
                <select onchange="if(this.value != '0') { window.location.href='<?= home_url('/b2b-panel/products') ?>?category='+this.value+'<?= $s ? '&s='.urlencode($s) : '' ?><?= $stock_status ? '&stock_status='.$stock_status : '' ?><?= $per_page != 20 ? '&per_page='.$per_page : '' ?>'; } else { window.location.href='<?= home_url('/b2b-panel/products') ?>?<?= $s ? 's='.urlencode($s).'&' : '' ?><?= $stock_status ? 'stock_status='.$stock_status.'&' : '' ?><?= $per_page != 20 ? 'per_page='.$per_page : '' ?>'.replace(/&$/, '').replace(/\?$/, ''); }" style="margin:0;max-width:200px;">
                    <option value="0">All Categories</option>
                    <?php foreach($categories as $c): ?>
                        <option value="<?= $c->term_id ?>" <?= selected($cat, $c->term_id) ?>><?= esc_html($c->name) ?> (<?= $c->count ?>)</option>
                    <?php endforeach; ?>
                </select>
                
                <!-- Stock Status Filter -->
                <select onchange="window.location.href='<?= home_url('/b2b-panel/products') ?>?stock_status='+this.value+'<?= $cat ? '&category='.$cat : '' ?><?= $s ? '&s='.urlencode($s) : '' ?><?= $per_page != 20 ? '&per_page='.$per_page : '' ?>'" style="margin:0;max-width:180px;">
                    <option value="">All Stock Status</option>
                    <option value="instock" <?= selected($stock_status, 'instock') ?>>In Stock</option>
                    <option value="outofstock" <?= selected($stock_status, 'outofstock') ?>>Out of Stock</option>
                    <option value="onbackorder" <?= selected($stock_status, 'onbackorder') ?>>On Backorder</option>
                </select>
                
                <!-- Per Page Selector -->
                <select onchange="window.location.href='<?= home_url('/b2b-panel/products') ?>?per_page='+this.value+'<?= $cat ? '&category='.$cat : '' ?><?= $stock_status ? '&stock_status='.$stock_status : '' ?><?= $s ? '&s='.urlencode($s) : '' ?>'" style="margin:0;max-width:120px;">
                    <option value="10" <?= selected($per_page, 10) ?>>10 per page</option>
                    <option value="20" <?= selected($per_page, 20) ?>>20 per page</option>
                    <option value="50" <?= selected($per_page, 50) ?>>50 per page</option>
                    <option value="100" <?= selected($per_page, 100) ?>>100 per page</option>
                </select>
                
                <!-- Column Toggler -->
                <div class="col-toggler">
                    <button type="button" class="secondary" onclick="document.querySelector('#pColDrop').classList.toggle('active')"><i class="fa-solid fa-table-columns"></i> Columns</button>
                    <div id="pColDrop" class="col-dropdown">
                        <label><input type="checkbox" checked data-col="0"> Image</label>
                        <label><input type="checkbox" checked data-col="1"> Name</label>
                        <label><input type="checkbox" checked data-col="2"> SKU</label>
                        <label><input type="checkbox" checked data-col="3"> Category</label>
                        <label><input type="checkbox" checked data-col="4"> Price</label>
                        <label><input type="checkbox" checked data-col="5"> Stock</label>
                        <label><input type="checkbox" checked data-col="6"> Status</label>
                        <label><input type="checkbox" checked data-col="7"> Action</label>
                    </div>
                </div>
            </div>
            
            <!-- Search Form -->
            <form style="display:flex;gap:10px" method="get" action="<?= home_url('/b2b-panel/products') ?>">
                <?php if($cat): ?><input type="hidden" name="category" value="<?= $cat ?>"><?php endif; ?>
                <?php if($stock_status): ?><input type="hidden" name="stock_status" value="<?= $stock_status ?>"><?php endif; ?>
                <?php if($per_page != 20): ?><input type="hidden" name="per_page" value="<?= $per_page ?>"><?php endif; ?>
                <input name="s" value="<?= esc_attr($s) ?>" placeholder="Search by name or SKU..." style="margin:0;min-width:250px;">
                <button>Search</button>
                <?php if($s || $cat || $stock_status): ?><a href="<?= home_url('/b2b-panel/products') ?>" style="padding:10px;color:#ef4444;text-decoration:none;font-weight:600;">Reset All</a><?php endif; ?>
            </form>
        </div>
        
        <!-- Quick Edit Save Button (Hidden by default) -->
        <div id="quickEditBar" style="display:none;margin-bottom:15px;padding:12px;background:#f0f9ff;border:2px solid #3b82f6;border-radius:8px;text-align:center;">
            <strong style="color:#1e40af;margin-right:15px;">Quick Edit Mode Active</strong>
            <button onclick="saveQuickEdit()" style="background:#10b981;color:white;padding:8px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                <i class="fa-solid fa-save"></i> Save All Changes
            </button>
            <button onclick="toggleQuickEdit()" class="secondary" style="margin-left:10px;">Cancel</button>
        </div>
        
        <!-- Simplified Bulk Actions -->
        <div style="margin-bottom:15px;">
            <button onclick="toggleBulkEditPanel()" style="background:#667eea;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;">
                <i class="fa-solid fa-edit"></i> Bulk Edit
            </button>
            <span id="selectedCount" style="margin-left:15px;font-weight:600;color:#666;">0 items selected</span>
        </div>
        
        <!-- Bulk Edit Panel (Hidden by default) -->
        <div id="bulkEditPanel" style="display:none;margin-bottom:20px;background:#f8f9fa;border:2px solid #dee2e6;border-radius:8px;padding:20px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;">
                
                <!-- Price Update Section -->
                <div style="background:white;padding:15px;border-radius:6px;border:2px solid #3b82f6;">
                    <h3 style="margin:0 0 15px 0;color:#1e40af;font-size:16px;">
                        <i class="fa-solid fa-dollar-sign"></i> Price Update
                    </h3>
                    <div style="margin-bottom:10px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;">Type:</label>
                        <select id="priceType" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed Amount (₺)</option>
                        </select>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;">Value:</label>
                        <input type="number" id="priceValue" placeholder="Enter value" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" step="0.01">
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;">Action:</label>
                        <select id="priceAction" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                            <option value="increase">Increase</option>
                            <option value="decrease">Decrease</option>
                        </select>
                    </div>
                    <button onclick="bulkUpdatePrice()" style="width:100%;background:#3b82f6;color:white;padding:10px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                        <i class="fa-solid fa-check"></i> Update Prices
                    </button>
                </div>
                
                <!-- Stock Update Section -->
                <div style="background:white;padding:15px;border-radius:6px;border:2px solid #10b981;">
                    <h3 style="margin:0 0 15px 0;color:#059669;font-size:16px;">
                        <i class="fa-solid fa-box"></i> Stock Update
                    </h3>
                    <div style="margin-bottom:10px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;">Type:</label>
                        <select id="stockType" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                            <option value="fixed">Fixed Amount (units)</option>
                            <option value="percentage">Percentage (%)</option>
                        </select>
                    </div>
                    <div style="margin-bottom:10px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;">Stock Value:</label>
                        <input type="number" id="stockValue" placeholder="Enter value" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;" step="0.01">
                    </div>
                    <div style="margin-bottom:15px;">
                        <label style="display:block;margin-bottom:5px;font-weight:600;">Action:</label>
                        <select id="stockAction" style="width:100%;padding:8px;border:1px solid #ddd;border-radius:4px;">
                            <option value="set">Set to Value</option>
                            <option value="increase">Increase by Value</option>
                            <option value="decrease">Decrease by Value</option>
                        </select>
                    </div>
                    <button onclick="bulkUpdateStock()" style="width:100%;background:#10b981;color:white;padding:10px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                        <i class="fa-solid fa-check"></i> Update Stock
                    </button>
                </div>
                
                <!-- Delete Section -->
                <div style="background:white;padding:15px;border-radius:6px;border:2px solid #ef4444;">
                    <h3 style="margin:0 0 15px 0;color:#dc2626;font-size:16px;">
                        <i class="fa-solid fa-trash"></i> Delete Products
                    </h3>
                    <p style="color:#666;font-size:13px;margin-bottom:15px;">
                        ⚠️ This action cannot be undone. Selected products will be permanently deleted.
                    </p>
                    <button onclick="bulkDelete()" style="width:100%;background:#ef4444;color:white;padding:10px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                        <i class="fa-solid fa-trash"></i> Delete Selected
                    </button>
                </div>
            </div>
            
            <!-- Progress Section -->
            <div id="bulkProgress" style="display:none;margin-top:20px;padding:15px;background:white;border-radius:6px;border:1px solid #ddd;">
                <div style="background:#e5e7eb;border-radius:8px;height:30px;overflow:hidden;margin-bottom:10px;">
                    <div id="bulkProgressBar" style="background:#10b981;height:100%;width:0%;transition:width 0.3s;display:flex;align-items:center;justify-content:center;font-weight:600;color:white;"></div>
                </div>
                <p id="bulkStatus" style="margin:0;font-size:14px;text-align:center;color:#666;"></p>
            </div>
        </div>
        
        <!-- Enhanced Product Table -->
        <div class="table-responsive">
        <table id="prodTable">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="selectAllCheckbox" onchange="window.toggleAllProducts(this)"></th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($products->products)): ?>
                <tr><td colspan="9" style="text-align:center;padding:30px;color:#999">No products found.</td></tr>
            <?php else: foreach ($products->products as $p): 
                $img = wp_get_attachment_image_src($p->get_image_id(),'thumbnail');
                $cats = wp_get_post_terms($p->get_id(), 'product_cat', ['fields' => 'names']);
            ?>
            <tr data-product-id="<?= $p->get_id() ?>">
                <td><input type="checkbox" class="product-checkbox" value="<?= $p->get_id() ?>" onchange="window.updateBulkSelection()"></td>
                <td><img src="<?= $img ? $img[0] : 'https://via.placeholder.com/40' ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;"></td>
                <td><strong><?= esc_html($p->get_name()) ?></strong></td>
                <td><code style="background:#f3f4f6;padding:3px 8px;border-radius:4px;font-size:11px;"><?= esc_html($p->get_sku() ?: '-') ?></code></td>
                <td><small style="color:#6b7280;"><?= !empty($cats) ? esc_html(implode(', ', $cats)) : '-' ?></small></td>
                <td><strong><?= $p->get_price_html() ?></strong></td>
                <td class="stock-cell">
                    <?php if($p->managing_stock()): 
                        $qty = $p->get_stock_quantity();
                        $color = $qty > 10 ? '#10b981' : ($qty > 0 ? '#f59e0b' : '#ef4444');
                    ?>
                        <span class="stock-display" style="color:<?= $color ?>;font-weight:600;"><?= $qty ?></span>
                        <input type="number" class="stock-input" data-product-id="<?= $p->get_id() ?>" value="<?= $qty ?>" style="display:none;width:80px;padding:4px;border:2px solid #3b82f6;border-radius:4px;" min="0">
                    <?php else: ?>
                        <span class="stock-display"><?= $p->is_in_stock() ? '<span style="color:#10b981;">In Stock</span>' : '<span style="color:#ef4444;">Out</span>' ?></span>
                        <span class="stock-input" style="display:none;color:#6b7280;font-size:11px;">N/A</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php 
                    $status = $p->get_status();
                    $status_color = $status == 'publish' ? '#d1fae5' : '#fee2e2';
                    $status_text_color = $status == 'publish' ? '#065f46' : '#991b1b';
                    ?>
                    <span style="background:<?= $status_color ?>;color:<?= $status_text_color ?>;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;text-transform:uppercase;"><?= $status ?></span>
                </td>
                <td style="text-align:right;">
                    <a href="<?= home_url('/b2b-panel/products/edit?id=' . $p->get_id()) ?>">
                        <button class="secondary" style="padding:6px 12px;font-size:12px;"><i class="fa-solid fa-pen"></i> Edit</button>
                    </a>
                    <button class="duplicate-product-btn" data-product-id="<?= $p->get_id() ?>" data-product-name="<?= esc_attr($p->get_name()) ?>" style="padding:6px 12px;font-size:12px;background:#3b82f6;color:white;border:none;border-radius:5px;cursor:pointer;margin-left:5px;"><i class="fa-solid fa-copy"></i></button>
                    <button class="delete-product-btn" data-product-id="<?= $p->get_id() ?>" data-product-name="<?= esc_attr($p->get_name()) ?>" style="padding:6px 12px;font-size:12px;background:#dc2626;color:white;border:none;border-radius:5px;cursor:pointer;margin-left:5px;"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        </div><!-- .table-responsive -->
        
        <!-- Pagination -->
        <?php if($products->max_num_pages > 1): ?>
        <div style="margin-top:20px;display:flex;justify-content:center;align-items:center;gap:10px;">
            <span style="color:#6b7280;font-size:14px;">Page:</span>
            <select onchange="window.location.href=this.value" style="margin:0;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:white;cursor:pointer;">
                <?php 
                for($i = 1; $i <= $products->max_num_pages; $i++) {
                    $page_params = [];
                    if($s) $page_params[] = 's=' . urlencode($s);
                    if($cat) $page_params[] = 'category=' . $cat;
                    if($stock_status) $page_params[] = 'stock_status=' . $stock_status;
                    if($per_page != 20) $page_params[] = 'per_page=' . $per_page;
                    if($i > 1) $page_params[] = 'paged=' . $i;
                    $page_url = home_url('/b2b-panel/products') . (!empty($page_params) ? '?' . implode('&', $page_params) : '');
                    $selected = ($i == $paged) ? 'selected' : '';
                    echo '<option value="' . esc_attr($page_url) . '" ' . $selected . '>Page ' . $i . ' of ' . $products->max_num_pages . '</option>';
                }
                ?>
            </select>
            <span style="color:#6b7280;font-size:14px;">
                (Showing <?= min($per_page, $products->total) ?> of <?= $products->total ?> products)
            </span>
        </div>
        <?php endif; ?>
    </div>
    <script>
    // Products Column Toggle with localStorage
    function toggleColP(idx, show) { 
        var rows = document.getElementById('prodTable').rows;
        // +1 to account for checkbox column which is column 0
        var actualIdx = idx + 1;
        for(var i=0; i<rows.length; i++) { 
            if(rows[i].cells.length > actualIdx) rows[i].cells[actualIdx].style.display = show ? '' : 'none'; 
        }
        // Save state to localStorage
        var colStates = JSON.parse(localStorage.getItem('b2b_products_columns') || '{}');
        colStates[idx] = show;
        localStorage.setItem('b2b_products_columns', JSON.stringify(colStates));
    }
    
    // Restore column visibility from localStorage
    var savedColStates = JSON.parse(localStorage.getItem('b2b_products_columns') || '{}');
    document.querySelectorAll('#pColDrop input').forEach(function(cb, i){ 
        // Restore saved state if exists
        if(savedColStates.hasOwnProperty(i)) {
            cb.checked = savedColStates[i];
            toggleColP(i, savedColStates[i]);
        }
        cb.addEventListener('change', function(){ toggleColP(i, this.checked); }); 
    });
    
    // Quick Edit Functionality
    let quickEditMode = false;
    
    function toggleQuickEdit() {
        quickEditMode = !quickEditMode;
        const displays = document.querySelectorAll('.stock-display');
        const inputs = document.querySelectorAll('.stock-input');
        const bar = document.getElementById('quickEditBar');
        const btn = document.getElementById('quickEditToggle');
        
        displays.forEach(el => el.style.display = quickEditMode ? 'none' : '');
        inputs.forEach(el => el.style.display = quickEditMode ? 'inline-block' : 'none');
        bar.style.display = quickEditMode ? 'block' : 'none';
        btn.style.background = quickEditMode ? '#10b981' : '';
        btn.style.color = quickEditMode ? 'white' : '';
        
        if(!quickEditMode) {
            // Reset inputs to original values when canceling
            inputs.forEach(input => {
                if(input.tagName === 'INPUT') {
                    const row = input.closest('tr');
                    const display = row.querySelector('.stock-display');
                    if(display) {
                        const originalValue = display.textContent.trim();
                        if(!isNaN(originalValue)) {
                            input.value = originalValue;
                        }
                    }
                }
            });
        }
    }
    
    function saveQuickEdit() {
        const inputs = document.querySelectorAll('.stock-input[type="number"]');
        const updates = [];
        
        inputs.forEach(input => {
            const productId = input.getAttribute('data-product-id');
            const newQty = parseInt(input.value);
            if(productId && !isNaN(newQty)) {
                updates.push({id: productId, qty: newQty});
            }
        });
        
        if(updates.length === 0) {
            alert('No changes to save.');
            return;
        }
        
        // Show loading state
        const saveBtn = event.target;
        const originalText = saveBtn.innerHTML;
        saveBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
        saveBtn.disabled = true;
        
        // AJAX call to save
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=b2b_quick_edit_stock&updates=' + encodeURIComponent(JSON.stringify(updates))
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Stock quantities updated successfully!');
                location.reload();
            } else {
                alert('Error: ' + (data.data || 'Unknown error'));
                saveBtn.innerHTML = originalText;
                saveBtn.disabled = false;
            }
        })
        .catch(error => {
            alert('Error saving changes: ' + error);
            saveBtn.innerHTML = originalText;
            saveBtn.disabled = false;
        });
    }
    
    // Delete Product Functionality
    document.querySelectorAll('.delete-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            
            if(!confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                return;
            }
            
            // Disable button during request
            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            
            fetch('<?= admin_url('admin-ajax.php') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=b2b_delete_product&product_id=' + productId + '&nonce=<?= wp_create_nonce("b2b_delete_product") ?>'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Product deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (data.data || 'Unknown error'));
                    this.disabled = false;
                    this.innerHTML = '<i class="fa-solid fa-trash"></i>';
                }
            })
            .catch(error => {
                alert('Error deleting product: ' + error);
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-trash"></i>';
            });
        });
    });
    
    // Duplicate product handler
    document.querySelectorAll('.duplicate-product-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const productId = this.getAttribute('data-product-id');
            const productName = this.getAttribute('data-product-name');
            
            if(!confirm(`Duplicate "${productName}"?`)) {
                return;
            }
            
            // Disable button during request
            this.disabled = true;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            
            fetch('<?= admin_url('admin-ajax.php') ?>', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'action=b2b_duplicate_product&product_id=' + productId + '&nonce=<?= wp_create_nonce("b2b_duplicate_product") ?>'
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    alert('Product duplicated successfully! Redirecting to edit...');
                    window.location.href = data.data.edit_url;
                } else {
                    alert('Error: ' + (data.data || 'Unknown error'));
                    this.disabled = false;
                    this.innerHTML = '<i class="fa-solid fa-copy"></i>';
                }
            })
            .catch(error => {
                alert('Error duplicating product: ' + error);
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-copy"></i>';
            });
        });
    });
    
    // Simplified Bulk Actions JavaScript
    function updateBulkSelection() {
        const checkboxes = document.querySelectorAll('.product-checkbox');
        const checked = document.querySelectorAll('.product-checkbox:checked');
        const selectedCount = document.getElementById('selectedCount');
        const selectAllCheckbox = document.getElementById('selectAllCheckbox');
        
        selectedCount.textContent = checked.length + ' items selected';
        selectAllCheckbox.checked = (checked.length === checkboxes.length && checkboxes.length > 0);
    }
    
    function toggleAllProducts(checkbox) {
        document.querySelectorAll('.product-checkbox').forEach(cb => {
            cb.checked = checkbox.checked;
        });
        updateBulkSelection();
    }
    
    // Toggle bulk edit panel
    window.toggleBulkEditPanel = function() {
        const panel = document.getElementById('bulkEditPanel');
        panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
    }
    
    // Get selected product IDs
    function getSelectedProductIds() {
        const checkboxes = document.querySelectorAll('.product-checkbox:checked');
        return Array.from(checkboxes).map(cb => cb.value);
    }
    
    // Show progress
    function showProgress() {
        document.getElementById('bulkProgress').style.display = 'block';
        document.getElementById('bulkProgressBar').style.width = '0%';
        document.getElementById('bulkProgressBar').textContent = '0%';
        document.getElementById('bulkStatus').textContent = 'Processing...';
    }
    
    // Update progress
    function updateProgress(current, total, success, errors) {
        const percent = Math.round((current / total) * 100);
        document.getElementById('bulkProgressBar').style.width = percent + '%';
        document.getElementById('bulkProgressBar').textContent = percent + '%';
        document.getElementById('bulkStatus').textContent = current + ' of ' + total + ' processed | ' + success + ' succeeded, ' + errors + ' errors';
    }
    
    // Complete progress
    function completeProgress() {
        document.getElementById('bulkProgressBar').style.background = '#10b981';
        document.getElementById('bulkProgressBar').textContent = 'Complete!';
        setTimeout(() => window.location.reload(), 2000);
    }
    
    // Bulk Update Price
    window.bulkUpdatePrice = function() {
        const productIds = getSelectedProductIds();
        if(productIds.length === 0) {
            alert('Please select at least one product');
            return;
        }
        
        const priceType = document.getElementById('priceType').value;
        const priceValue = document.getElementById('priceValue').value;
        const priceAction = document.getElementById('priceAction').value;
        
        if(!priceValue || parseFloat(priceValue) <= 0) {
            alert('Please enter a valid value');
            return;
        }
        
        if(!confirm('Update prices for ' + productIds.length + ' products?')) {
            return;
        }
        
        showProgress();
        processBulkAction('price_update', productIds, 0, {
            priceType: priceType,
            priceValue: priceValue,
            priceAction: priceAction
        });
    }
    
    // Bulk Update Stock
    window.bulkUpdateStock = function() {
        const productIds = getSelectedProductIds();
        if(productIds.length === 0) {
            alert('Please select at least one product');
            return;
        }
        
        const stockType = document.getElementById('stockType').value;
        const stockValue = document.getElementById('stockValue').value;
        const stockAction = document.getElementById('stockAction').value;
        
        if(!stockValue || parseFloat(stockValue) < 0) {
            alert('Please enter a valid value');
            return;
        }
        
        if(!confirm('Update stock for ' + productIds.length + ' products?')) {
            return;
        }
        
        showProgress();
        processBulkAction('stock_update', productIds, 0, {
            stockType: stockType,
            stockValue: stockValue,
            stockAction: stockAction
        });
    }
    
    // Bulk Delete
    window.bulkDelete = function() {
        const productIds = getSelectedProductIds();
        if(productIds.length === 0) {
            alert('Please select at least one product');
            return;
        }
        
        if(!confirm('Are you sure you want to delete ' + productIds.length + ' products? This action cannot be undone!')) {
            return;
        }
        
        showProgress();
        processBulkAction('delete', productIds, 0, {});
    }
    
    // Process bulk action in chunks
    function processBulkAction(action, productIds, currentChunk, params) {
        const chunkSize = 10;
        const startIndex = currentChunk * chunkSize;
        const endIndex = Math.min(startIndex + chunkSize, productIds.length);
        const chunk = productIds.slice(startIndex, endIndex);
        
        // Initialize accumulator
        if(!window.bulkResults) {
            window.bulkResults = {success: 0, errors: 0};
        }
        
        // Build request data
        let data = 'action=b2b_bulk_action_products&nonce=<?= wp_create_nonce("b2b_ajax_nonce") ?>';
        data += '&bulk_action=' + action;
        data += '&product_ids=' + productIds.join(','); // Send all IDs, backend will chunk
        data += '&chunk=' + currentChunk;
        
        if(action === 'price_update') {
            data += '&price_type=' + params.priceType;
            data += '&price_value=' + params.priceValue;
            data += '&price_action=' + params.priceAction;
        } else if(action === 'stock_update') {
            data += '&stock_type=' + params.stockType;
            data += '&stock_value=' + params.stockValue;
            data += '&stock_action=' + params.stockAction;
        }
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: data
        })
        .then(response => response.json())
        .then(response => {
            if(response.success && response.data.results) {
                // Accumulate results
                window.bulkResults.success += response.data.results.success ? response.data.results.success.length : 0;
                window.bulkResults.errors += response.data.results.errors ? response.data.results.errors.length : 0;
                
                const totalProcessed = window.bulkResults.success + window.bulkResults.errors;
                updateProgress(totalProcessed, productIds.length, window.bulkResults.success, window.bulkResults.errors);
                
                // Process next chunk or complete
                if(endIndex < productIds.length) {
                    processBulkAction(action, productIds, currentChunk + 1, params);
                } else {
                    delete window.bulkResults;
                    completeProgress();
                }
            } else {
                document.getElementById('bulkStatus').textContent = 'Error: ' + (response.data || 'Unknown error');
            }
        })
        .catch(error => {
            document.getElementById('bulkStatus').textContent = 'Error: ' + error;
        });
    }
    </script>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   9B. PAGE: PRODUCTS IMPORT
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'products_import') return;
    b2b_adm_guard();
    
    // Handle CSV import
    $import_message = '';
    if(isset($_POST['import_products']) && isset($_FILES['csv_file'])) {
        $file = $_FILES['csv_file'];
        if($file['error'] === 0 && pathinfo($file['name'], PATHINFO_EXTENSION) === 'csv') {
            $handle = fopen($file['tmp_name'], 'r');
            $row = 0;
            $imported = 0;
            $updated = 0;
            
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $row++;
                if($row === 1) continue; // Skip header
                
                // Expected format: SKU, Name, Price, Stock, Category
                $sku = isset($data[0]) ? sanitize_text_field($data[0]) : '';
                $name = isset($data[1]) ? sanitize_text_field($data[1]) : '';
                $price = isset($data[2]) ? floatval($data[2]) : 0;
                $stock = isset($data[3]) ? intval($data[3]) : 0;
                $category = isset($data[4]) ? sanitize_text_field($data[4]) : '';
                
                if(empty($sku)) continue;
                
                // Check if product exists by SKU
                $product_id = wc_get_product_id_by_sku($sku);
                
                if($product_id) {
                    // Update existing product
                    $product = wc_get_product($product_id);
                    $product->set_regular_price($price);
                    $product->set_price($price);
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($stock);
                    $product->save();
                    $updated++;
                } else {
                    // Create new product
                    $product = new WC_Product_Simple();
                    $product->set_name($name);
                    $product->set_sku($sku);
                    $product->set_regular_price($price);
                    $product->set_price($price);
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($stock);
                    $product->set_status('publish');
                    
                    if(!empty($category)) {
                        $term = get_term_by('name', $category, 'product_cat');
                        if($term) {
                            $product->set_category_ids([$term->term_id]);
                        }
                    }
                    
                    $product->save();
                    $imported++;
                }
            }
            fclose($handle);
            $import_message = "<div style='padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;'><strong>Success!</strong> Imported {$imported} new products, updated {$updated} existing products.</div>";
        } else {
            $import_message = "<div style='padding:15px;background:#fee2e2;color:#991b1b;border-radius:8px;margin-bottom:20px;'><strong>Error!</strong> Invalid file. Please upload a CSV file.</div>";
        }
    }
    
    b2b_adm_header('Import Products');
    ?>
    <div class="page-header"><h1 class="page-title">Import Products</h1></div>
    <div class="card">
        <?= $import_message ?>
        
        <div style="max-width:600px;">
            <h3 style="margin-top:0;">Upload CSV File</h3>
            <p style="color:#6b7280;">Import products from a CSV file. The file should have the following columns: <code>SKU, Name, Price, Stock, Category</code></p>
            
            <form method="post" enctype="multipart/form-data" style="margin-top:20px;">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:8px;font-weight:600;">Select CSV File</label>
                    <input type="file" name="csv_file" accept=".csv" required style="display:block;width:100%;padding:10px;border:2px dashed #e5e7eb;border-radius:8px;">
                </div>
                
                <button type="submit" name="import_products" style="background:#3b82f6;color:white;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                    <i class="fa-solid fa-file-import"></i> Import Products
                </button>
            </form>
            
            <div style="margin-top:30px;padding:15px;background:#f3f4f6;border-radius:8px;">
                <h4 style="margin-top:0;">CSV Format Example:</h4>
                <pre style="background:white;padding:10px;border-radius:4px;overflow-x:auto;">SKU,Name,Price,Stock,Category
PROD001,Sample Product 1,29.99,100,Electronics
PROD002,Sample Product 2,49.99,50,Clothing</pre>
            </div>
            
            <div style="margin-top:20px;">
                <a href="<?= home_url('/b2b-panel/products') ?>" style="text-decoration:none;">
                    <button class="secondary"><i class="fa-solid fa-arrow-left"></i> Back to Products</button>
                </a>
            </div>
        </div>
    </div>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   9C. PAGE: PRODUCTS EXPORT
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'products_export') return;
    b2b_adm_guard();
    
    // Handle export
    if(isset($_POST['export_products'])) {
        $args = ['limit' => -1, 'status' => 'publish'];
        $products = wc_get_products($args);
        
        // Set headers for CSV download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="products-export-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, ['SKU', 'Name', 'Price', 'Stock', 'Category', 'Status']);
        
        // Product rows
        foreach($products as $product) {
            $cats = wp_get_post_terms($product->get_id(), 'product_cat', ['fields' => 'names']);
            fputcsv($output, [
                $product->get_sku(),
                $product->get_name(),
                $product->get_regular_price(),
                $product->get_stock_quantity() ?: 0,
                !empty($cats) ? implode(', ', $cats) : '',
                $product->get_status()
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    b2b_adm_header('Export Products');
    ?>
    <div class="page-header"><h1 class="page-title">Export Products</h1></div>
    <div class="card">
        <div style="max-width:600px;">
            <h3 style="margin-top:0;">Export to CSV</h3>
            <p style="color:#6b7280;">Export all products to a CSV file with SKU, Name, Price, Stock, Category, and Status columns.</p>
            
            <form method="post" style="margin-top:20px;">
                <button type="submit" name="export_products" style="background:#10b981;color:white;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
                    <i class="fa-solid fa-file-export"></i> Export All Products
                </button>
            </form>
            
            <div style="margin-top:30px;padding:15px;background:#f0f9ff;border:1px solid #bfdbfe;border-radius:8px;">
                <h4 style="margin-top:0;color:#1e40af;"><i class="fa-solid fa-info-circle"></i> Export Information</h4>
                <ul style="color:#1e40af;margin:0;">
                    <li>All published products will be exported</li>
                    <li>CSV format: SKU, Name, Price, Stock, Category, Status</li>
                    <li>File name: products-export-YYYY-MM-DD.csv</li>
                    <li>You can edit the CSV and re-import it</li>
                </ul>
            </div>
            
            <div style="margin-top:20px;">
                <a href="<?= home_url('/b2b-panel/products') ?>" style="text-decoration:none;">
                    <button class="secondary"><i class="fa-solid fa-arrow-left"></i> Back to Products</button>
                </a>
            </div>
        </div>
    </div>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   9D. PAGE: PRODUCTS CATEGORIES
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'products_categories') return;
    b2b_adm_guard();
    
    // Handle category add/edit
    $message = '';
    if(isset($_POST['save_category'])) {
        $cat_name = sanitize_text_field($_POST['category_name']);
        $cat_slug = sanitize_title($_POST['category_slug']);
        $cat_desc = sanitize_textarea_field($_POST['category_description']);
        $cat_parent = intval($_POST['category_parent']);
        $cat_id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        
        $args = [
            'name' => $cat_name,
            'slug' => $cat_slug,
            'description' => $cat_desc,
            'parent' => $cat_parent
        ];
        
        if($cat_id) {
            // Update existing
            $result = wp_update_term($cat_id, 'product_cat', $args);
        } else {
            // Create new
            $result = wp_insert_term($cat_name, 'product_cat', $args);
        }
        
        if(!is_wp_error($result)) {
            $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> Category saved.</div>';
        } else {
            $message = '<div style="padding:15px;background:#fee2e2;color:#991b1b;border-radius:8px;margin-bottom:20px;"><strong>Error!</strong> ' . $result->get_error_message() . '</div>';
        }
    }
    
    // Handle delete
    if(isset($_GET['delete']) && $_GET['delete']) {
        $del_id = intval($_GET['delete']);
        wp_delete_term($del_id, 'product_cat');
        $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;">Category deleted.</div>';
    }
    
    // Get all categories
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name']);
    
    b2b_adm_header('Product Categories');
    ?>
    <div class="page-header">
        <h1 class="page-title">Product Categories</h1>
        <button onclick="document.getElementById('addCatForm').style.display='block'" style="background:#3b82f6;color:white;padding:10px 20px;border:none;border-radius:8px;cursor:pointer;font-weight:600;">
            <i class="fa-solid fa-plus"></i> Add New Category
        </button>
    </div>
    
    <?= $message ?>
    
    <!-- Add/Edit Form (Hidden by default) -->
    <div id="addCatForm" style="display:none;margin-bottom:20px;">
        <div class="card">
            <h3 style="margin-top:0;">Add New Category</h3>
            <form method="post" style="max-width:600px;">
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Category Name *</label>
                    <input type="text" name="category_name" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Slug (URL)</label>
                    <input type="text" name="category_slug" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                    <small style="color:#6b7280;">Leave empty to auto-generate from name</small>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Parent Category</label>
                    <select name="category_parent" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                        <option value="0">None (Top Level)</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat->term_id ?>"><?= esc_html($cat->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div style="margin-bottom:15px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;">Description</label>
                    <textarea name="category_description" rows="3" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;"></textarea>
                </div>
                
                <div style="display:flex;gap:10px;">
                    <button type="submit" name="save_category" style="background:#10b981;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                        <i class="fa-solid fa-save"></i> Save Category
                    </button>
                    <button type="button" onclick="document.getElementById('addCatForm').style.display='none'" class="secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Categories List -->
    <div class="card">
        <table style="width:100%;">
            <thead>
                <tr>
                    <th style="text-align:left;">Name</th>
                    <th style="text-align:left;">Slug</th>
                    <th style="text-align:left;">Description</th>
                    <th style="text-align:center;">Count</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($categories)): ?>
                <tr><td colspan="5" style="text-align:center;padding:30px;color:#999;">No categories found.</td></tr>
            <?php else: foreach($categories as $cat): ?>
                <tr>
                    <td><strong><?= esc_html($cat->name) ?></strong></td>
                    <td><code style="background:#f3f4f6;padding:3px 8px;border-radius:4px;font-size:11px;"><?= esc_html($cat->slug) ?></code></td>
                    <td><small style="color:#6b7280;"><?= esc_html($cat->description ?: '-') ?></small></td>
                    <td style="text-align:center;"><span style="background:#f3f4f6;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;"><?= $cat->count ?></span></td>
                    <td style="text-align:right;">
                        <a href="<?= home_url('/b2b-panel/products/categories/edit?id=' . $cat->term_id) ?>" style="margin-right:10px;">
                            <button class="secondary" style="padding:6px 12px;font-size:12px;"><i class="fa-solid fa-pen"></i> Edit</button>
                        </a>
                        <a href="?delete=<?= $cat->term_id ?>" onclick="return confirm('Delete this category?')" style="color:#ef4444;">
                            <button style="background:#ef4444;color:white;padding:6px 12px;font-size:12px;border:none;border-radius:6px;cursor:pointer;"><i class="fa-solid fa-trash"></i></button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    
    <div style="margin-top:20px;">
        <a href="<?= home_url('/b2b-panel/products') ?>" style="text-decoration:none;">
            <button class="secondary"><i class="fa-solid fa-arrow-left"></i> Back to Products</button>
        </a>
    </div>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   9E. PAGE: CATEGORY EDIT
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'category_edit') return;
    b2b_adm_guard();
    
    $cat_id = intval($_GET['id']);
    $category = get_term($cat_id, 'product_cat');
    
    if(!$category || is_wp_error($category)) wp_die('Category not found');
    
    // Handle update
    $message = '';
    if(isset($_POST['update_category'])) {
        $cat_name = sanitize_text_field($_POST['category_name']);
        $cat_slug = sanitize_title($_POST['category_slug']);
        $cat_desc = sanitize_textarea_field($_POST['category_description']);
        $cat_parent = intval($_POST['category_parent']);
        
        $result = wp_update_term($cat_id, 'product_cat', [
            'name' => $cat_name,
            'slug' => $cat_slug,
            'description' => $cat_desc,
            'parent' => $cat_parent
        ]);
        
        if(!is_wp_error($result)) {
            $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> Category updated.</div>';
            $category = get_term($cat_id, 'product_cat'); // Refresh
        } else {
            $message = '<div style="padding:15px;background:#fee2e2;color:#991b1b;border-radius:8px;margin-bottom:20px;"><strong>Error!</strong> ' . $result->get_error_message() . '</div>';
        }
    }
    
    $all_categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false, 'exclude' => [$cat_id]]);
    
    b2b_adm_header('Edit Category');
    ?>
    <div class="page-header"><h1 class="page-title">Edit Category: <?= esc_html($category->name) ?></h1></div>
    
    <?= $message ?>
    
    <div class="card">
        <form method="post" style="max-width:600px;">
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Category Name *</label>
                <input type="text" name="category_name" value="<?= esc_attr($category->name) ?>" required style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Slug (URL)</label>
                <input type="text" name="category_slug" value="<?= esc_attr($category->slug) ?>" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Parent Category</label>
                <select name="category_parent" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                    <option value="0">None (Top Level)</option>
                    <?php foreach($all_categories as $cat): ?>
                        <option value="<?= $cat->term_id ?>" <?= selected($category->parent, $cat->term_id) ?>><?= esc_html($cat->name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Description</label>
                <textarea name="category_description" rows="3" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;"><?= esc_textarea($category->description) ?></textarea>
            </div>
            
            <div style="margin-bottom:15px;padding:15px;background:#f0f9ff;border:1px solid #bfdbfe;border-radius:8px;">
                <strong style="color:#1e40af;">Product Count:</strong> <?= $category->count ?> products in this category
            </div>
            
            <div style="display:flex;gap:10px;">
                <button type="submit" name="update_category" style="background:#10b981;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                    <i class="fa-solid fa-save"></i> Update Category
                </button>
                <a href="<?= home_url('/b2b-panel/products/categories') ?>" style="text-decoration:none;">
                    <button type="button" class="secondary">Cancel</button>
                </a>
            </div>
        </form>
    </div>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   9F. PAGE: PRICE ADJUSTER
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'price_adjuster') return;
    b2b_adm_guard();
    
    // Price calculation function
    function b2b_calculate_price($price, $type, $value, $action, $rounding = false) {
        if ($type === 'percent') {
            $delta = $price * ($value / 100);
        } else {
            $delta = $value;
        }
        $new_price = ($action === 'increase') ? $price + $delta : max(0, $price - $delta);

        if ($rounding) {
            $fraction = $new_price - floor($new_price);
            $new_price = ($fraction >= 0.5) ? ceil($new_price) : floor($new_price);
        }
        return $new_price;
    }
    
    $per_page = 20;
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($paged - 1) * $per_page;

    // Read filter inputs from GET
    $category_ids = isset($_GET['cpa_categories']) ? array_map('intval', (array) $_GET['cpa_categories']) : [];
    $type = isset($_GET['cpa_type']) ? sanitize_text_field($_GET['cpa_type']) : 'percent';
    $value = isset($_GET['cpa_value']) ? floatval($_GET['cpa_value']) : 0;
    $action = isset($_GET['cpa_action']) ? sanitize_text_field($_GET['cpa_action']) : 'increase';
    $rounding = isset($_GET['cpa_rounding']) ? true : false;
    $search = isset($_GET['cpa_search']) ? sanitize_text_field($_GET['cpa_search']) : '';

    b2b_adm_header('Price Adjuster');
    
    echo '<div class="page-header"><h1 class="page-title"><i class="fa-solid fa-dollar-sign"></i> Price Adjuster</h1></div>';
    
    // Filter form
    echo '<div class="card">';
    echo '<form method="get" action="'.esc_url(home_url('/b2b-panel/products/price-adjuster')).'">';
    echo '<table style="width:100%;max-width:800px;"><tbody>';
    
    // Categories select
    echo '<tr><th style="width:200px;text-align:left;padding:10px;"><label for="cpa_categories">Categories</label></th><td style="padding:10px;">';
    echo '<select name="cpa_categories[]" id="cpa_categories" multiple="multiple" style="width:100%;max-width:400px;height:100px;">';
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
    foreach ($categories as $cat) {
        $sel = in_array($cat->term_id, $category_ids) ? 'selected' : '';
        echo '<option value="'.esc_attr($cat->term_id).'" '.$sel.'>'.esc_html($cat->name).'</option>';
    }
    echo '</select>';
    echo '<p style="color:#6b7280;font-size:13px;margin:5px 0 0 0;">Hold Ctrl/Cmd to select multiple categories</p>';
    echo '</td></tr>';

    // Adjustment type / value / action / rounding / search
    echo '<tr><th style="text-align:left;padding:10px;"><label for="cpa_type">Adjustment Type</label></th><td style="padding:10px;">';
    echo '<select name="cpa_type" id="cpa_type" style="max-width:200px;">';
    echo '<option value="percent"'.($type==='percent' ? ' selected':'').'>Percentage (%)</option>';
    echo '<option value="fixed"'.($type==='fixed' ? ' selected':'').'>Fixed Amount</option>';
    echo '</select>';
    echo '</td></tr>';

    echo '<tr><th style="text-align:left;padding:10px;"><label for="cpa_value">Value</label></th><td style="padding:10px;">';
    echo '<input type="number" step="0.01" name="cpa_value" id="cpa_value" value="'.esc_attr($value).'" required style="max-width:200px;" />';
    echo '</td></tr>';

    echo '<tr><th style="text-align:left;padding:10px;"><label for="cpa_action">Action</label></th><td style="padding:10px;">';
    echo '<select name="cpa_action" id="cpa_action" style="max-width:200px;">';
    echo '<option value="increase"'.($action==='increase' ? ' selected':'').'>Increase</option>';
    echo '<option value="decrease"'.($action==='decrease' ? ' selected':'').'>Decrease</option>';
    echo '</select>';
    echo '</td></tr>';

    echo '<tr><th style="text-align:left;padding:10px;"><label for="cpa_rounding">Rounding</label></th><td style="padding:10px;">';
    echo '<label style="display:flex;align-items:center;gap:8px;"><input type="checkbox" name="cpa_rounding" id="cpa_rounding" value="1"'.($rounding ? ' checked':'').'> Round to nearest whole number (>= .5 rounds up)</label>';
    echo '</td></tr>';

    echo '<tr><th style="text-align:left;padding:10px;"><label for="cpa_search">Search</label></th><td style="padding:10px;">';
    echo '<input type="search" name="cpa_search" id="cpa_search" value="'.esc_attr($search).'" placeholder="Product name or SKU" style="width:100%;max-width:400px;" />';
    echo '</td></tr>';

    echo '</tbody></table>';

    // Preview button
    echo '<p style="margin-top:20px;"><input type="submit" name="cpa_preview" class="button" style="background:#3b82f6;color:white;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;font-weight:600;" value="Preview Changes"></p>';
    echo '</form>';
    echo '</div>';

    // If preview requested
    if (isset($_GET['cpa_preview'])) {
        // Build WP_Query args
        $args = [
            'post_type' => 'product',
            'posts_per_page' => -1,
            's' => $search,
            'fields' => 'ids'
        ];
        if (!empty($category_ids)) {
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $category_ids,
                    'operator' => 'IN',
                ]
            ];
        }

        $product_posts = get_posts($args);

        // Build full preview array
        $full_preview = [];
        foreach ($product_posts as $prod_id) {
            $product = wc_get_product($prod_id);
            if (!$product) continue;

            if ($product->is_type('variable')) {
                $children = $product->get_children();
                foreach ($children as $child_id) {
                    $variation = wc_get_product($child_id);
                    if (!$variation) continue;
                    $current_price = floatval($variation->get_regular_price());
                    $new_price = b2b_calculate_price($current_price, $type, $value, $action, $rounding);
                    $full_preview[] = [
                        'id' => $variation->get_id(),
                        'old' => $current_price,
                        'new' => $new_price,
                        'name' => $variation->get_name(),
                    ];
                }
            } else {
                $current_price = floatval($product->get_regular_price());
                $new_price = b2b_calculate_price($current_price, $type, $value, $action, $rounding);
                $full_preview[] = [
                    'id' => $product->get_id(),
                    'old' => $current_price,
                    'new' => $new_price,
                    'name' => $product->get_name(),
                ];
            }
        }

        // Save preview in transient
        $user_id = get_current_user_id();
        $preview_key = 'cpa_preview_' . $user_id . '_' . uniqid();
        set_transient($preview_key, $full_preview, 30 * MINUTE_IN_SECONDS);

        // Pagination calculation
        $total_items = count($full_preview);
        $total_pages = $total_items ? ceil($total_items / $per_page) : 1;

        // Slice to display current page
        $display_items = array_slice($full_preview, $offset, $per_page);

        // Display preview table
        echo '<div class="card" style="margin-top:20px;">';
        echo '<h2 style="margin-top:0;">Preview of Price Changes</h2>';
        echo '<p style="color:#6b7280;margin-bottom:20px;">Showing '.count($display_items).' of '.$total_items.' items</p>';
        echo '<table style="width:100%;"><thead><tr><th style="text-align:left;">Product</th><th style="text-align:right;">Current Price</th><th style="text-align:right;">New Price</th></tr></thead><tbody>';
        foreach ($display_items as $row) {
            echo '<tr>';
            echo '<td>'.esc_html($row['name']).'</td>';
            echo '<td style="text-align:right;">$'.number_format($row['old'], 2).'</td>';
            echo '<td style="text-align:right;font-weight:600;color:#10b981;">$'.number_format($row['new'], 2).'</td>';
            echo '</tr>';
        }
        echo '</tbody></table>';

        // Pagination links
        if ($total_pages > 1) {
            echo '<div style="margin-top:20px;display:flex;gap:10px;align-items:center;">';
            echo '<span style="color:#6b7280;">Page:</span>';
            for ($i = 1; $i <= min($total_pages, 10); $i++) {
                $params = $_GET;
                $params['paged'] = $i;
                $link = add_query_arg($params, home_url('/b2b-panel/products/price-adjuster'));
                if ($i == $paged) {
                    echo "<span style='padding:8px 12px;background:#3b82f6;color:white;border-radius:6px;font-weight:600;'>{$i}</span> ";
                } else {
                    echo '<a href="'.esc_url($link).'" style="padding:8px 12px;background:#f3f4f6;color:#374151;border-radius:6px;text-decoration:none;">'.esc_html($i).'</a> ';
                }
            }
            if ($total_pages > 10) echo '<span style="color:#6b7280;">... '.$total_pages.' total</span>';
            echo '</div>';
        }

        // Show Apply form
        echo '<form method="post" style="margin-top:20px;">';
        wp_nonce_field('cpa_apply_action', 'cpa_apply_nonce');
        echo '<input type="hidden" name="cpa_preview_key" value="'.esc_attr($preview_key).'">';
        echo '<button type="submit" name="cpa_apply" style="background:#10b981;color:white;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;font-weight:600;"><i class="fa-solid fa-check"></i> Apply Changes</button>';
        echo '</form>';
        echo '</div>';
    }

    // APPLY handler
    if (isset($_POST['cpa_apply'])) {
        check_admin_referer('cpa_apply_action', 'cpa_apply_nonce');
        $preview_key = isset($_POST['cpa_preview_key']) ? sanitize_text_field($_POST['cpa_preview_key']) : '';
        $data_to_apply = $preview_key ? get_transient($preview_key) : false;
        if ($data_to_apply && is_array($data_to_apply)) {
            // Save old prices for undo
            $user_key = 'cpa_last_prices_' . get_current_user_id();
            update_option($user_key, array_column($data_to_apply, 'old', 'id'));

            foreach ($data_to_apply as $d) {
                $p = wc_get_product($d['id']);
                if ($p) {
                    $p->set_regular_price($d['new']);
                    $p->save();
                }
            }
            delete_transient($preview_key);
            echo '<div class="card" style="margin-top:20px;background:#d1fae5;border:2px solid #10b981;"><p style="color:#065f46;margin:0;"><strong><i class="fa-solid fa-check-circle"></i> Success!</strong> Prices applied successfully for '.count($data_to_apply).' items.</p></div>';
        } else {
            echo '<div class="card" style="margin-top:20px;background:#fee2e2;border:2px solid#ef4444;"><p style="color:#991b1b;margin:0;"><strong><i class="fa-solid fa-exclamation-circle"></i> Error!</strong> No preview data found or it expired. Please preview again before applying.</p></div>';
        }
    }

    // UNDO form
    $user_key = 'cpa_last_prices_' . get_current_user_id();
    $last_prices = get_option($user_key, []);
    if (!empty($last_prices)) {
        echo '<div class="card" style="margin-top:20px;">';
        echo '<h3 style="margin-top:0;"><i class="fa-solid fa-undo"></i> Undo Last Change</h3>';
        echo '<p style="color:#6b7280;">You can undo the last price adjustment you applied.</p>';
        echo '<form method="post">';
        wp_nonce_field('cpa_undo_action', 'cpa_undo_nonce');
        echo '<button type="submit" name="cpa_undo" style="background:#ef4444;color:white;padding:12px 24px;border:none;border-radius:8px;cursor:pointer;font-weight:600;"><i class="fa-solid fa-undo"></i> Undo Last Change</button>';
        echo '</form>';
        echo '</div>';
    }

    // UNDO handler
    if (isset($_POST['cpa_undo'])) {
        check_admin_referer('cpa_undo_action', 'cpa_undo_nonce');
        $user_key = 'cpa_last_prices_' . get_current_user_id();
        $last_prices = get_option($user_key, []);
        if (!empty($last_prices)) {
            foreach ($last_prices as $id => $price) {
                $p = wc_get_product($id);
                if ($p) {
                    $p->set_regular_price($price);
                    $p->save();
                }
            }
            delete_option($user_key);
            echo '<div class="card" style="margin-top:20px;background:#d1fae5;border:2px solid #10b981;"><p style="color:#065f46;margin:0;"><strong><i class="fa-solid fa-check-circle"></i> Success!</strong> Changes undone successfully.</p></div>';
        } else {
            echo '<div class="card" style="margin-top:20px;background:#fef3c7;border:2px solid #f59e0b;"><p style="color:#92400e;margin:0;"><strong><i class="fa-solid fa-info-circle"></i> Notice:</strong> No saved changes to undo.</p></div>';
        }
    }
    
    echo '<div style="margin-top:20px;">';
    echo '<a href="'.home_url('/b2b-panel/products').'" style="text-decoration:none;"><button class="secondary"><i class="fa-solid fa-arrow-left"></i> Back to Products</button></a>';
    echo '</div>';
    
    b2b_adm_footer(); exit;
});

/* =====================================================
   10. PAGE: PRODUCT EDIT (FULL STOCK LOGIC FIXED)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'product_edit') return;
    b2b_adm_guard();
    
    $id = intval($_GET['id']);
    $p = wc_get_product($id);
    
    if(!$p) wp_die('Product not found');
    $is_variable = $p->is_type('variable');

    // --- SAVE PROCESS ---
    if($_POST) {
        // 1. LOGGING (Parent Level)
        if($_POST['price'] != $p->get_regular_price()) b2b_adm_add_log($id, 'price', $p->get_regular_price(), $_POST['price'], 'Main Price Update');
        if($_POST['stock_qty'] != $p->get_stock_quantity()) b2b_adm_add_log($id, 'stock', $p->get_stock_quantity(), $_POST['stock_qty'], 'Main Stock Update');

        // 2. COMMON FIELDS (Title, Desc, Slug)
        wp_update_post([
            'ID' => $id, 
            'post_status' => $_POST['status'], 
            'post_excerpt' => wp_kses_post($_POST['short_desc']), 
            'post_content' => wp_kses_post($_POST['long_desc'])
        ]);

        // 3. META DATA (SKU, Price - Parent)
        update_post_meta($id, '_sku', wc_clean($_POST['sku']));
        
        // Fiyatlar (Varyasyonlu ise parent fiyatı genelde pasiftir ama kaydediyoruz)
        update_post_meta($id, '_regular_price', wc_clean($_POST['price']));
        update_post_meta($id, '_price', wc_clean($_POST['price']));
        
        // Sale Price
        if(!empty($_POST['sale_price'])) {
            update_post_meta($id, '_sale_price', wc_clean($_POST['sale_price']));
            update_post_meta($id, '_price', wc_clean($_POST['sale_price'])); // Active price becomes sale price
        } else {
            delete_post_meta($id, '_sale_price');
        }
        
        // Shipping (Weight and Dimensions)
        if(!empty($_POST['weight'])) {
            update_post_meta($id, '_weight', wc_clean($_POST['weight']));
        } else {
            delete_post_meta($id, '_weight');
        }
        if(!empty($_POST['length'])) {
            update_post_meta($id, '_length', wc_clean($_POST['length']));
        } else {
            delete_post_meta($id, '_length');
        }
        if(!empty($_POST['width'])) {
            update_post_meta($id, '_width', wc_clean($_POST['width']));
        } else {
            delete_post_meta($id, '_width');
        }
        if(!empty($_POST['height'])) {
            update_post_meta($id, '_height', wc_clean($_POST['height']));
        } else {
            delete_post_meta($id, '_height');
        }
        
        // Tax Settings
        if(isset($_POST['tax_status'])) {
            update_post_meta($id, '_tax_status', wc_clean($_POST['tax_status']));
        }
        if(isset($_POST['tax_class'])) {
            update_post_meta($id, '_tax_class', wc_clean($_POST['tax_class']));
        }

        // 4. PARENT STOCK MANAGEMENT (Global Stock for Variations)
        // Bu bölüm artık hem basit hem varyasyonlu ürünler için çalışır.
        $m = isset($_POST['manage_stock']) ? 'yes' : 'no'; 
        update_post_meta($id, '_manage_stock', $m);
        if($m == 'yes') {
            update_post_meta($id, '_stock', wc_clean($_POST['stock_qty'])); 
        } else {
            update_post_meta($id, '_stock_status', $_POST['stock_status']);
        }

        // 5. CATEGORIES
        $cats = isset($_POST['cats']) ? array_map('intval', $_POST['cats']) : []; 
        wp_set_object_terms($id, $cats, 'product_cat');
        
        // 6. ASSEMBLY & VARIATIONS
        update_post_meta($id, '_assembly_enabled', isset($_POST['assembly'])?'yes':'no');
        update_post_meta($id, '_assembly_price', wc_clean($_POST['assembly_price']));
        update_post_meta($id, '_assembly_tax', $_POST['assembly_tax']);

        if ($is_variable && isset($_POST['vars'])) {
            foreach ($_POST['vars'] as $vid => $vdata) {
                $var_obj = wc_get_product($vid);
                if(!$var_obj) continue;

                // Var Log
                if ($vdata['price'] != $var_obj->get_regular_price()) b2b_adm_add_log($id, 'var_price', $var_obj->get_regular_price(), $vdata['price'], "Var #$vid Price");
                
                update_post_meta($vid, '_regular_price', wc_clean($vdata['price']));
                update_post_meta($vid, '_price', wc_clean($vdata['price']));
                
                $v_manage = isset($vdata['manage']) ? 'yes' : 'no';
                update_post_meta($vid, '_manage_stock', $v_manage);
                
                if ($v_manage == 'yes') {
                    if ($vdata['qty'] != $var_obj->get_stock_quantity()) b2b_adm_add_log($id, 'var_stock', $var_obj->get_stock_quantity(), $vdata['qty'], "Var #$vid Stock");
                    update_post_meta($vid, '_stock', wc_clean($vdata['qty']));
                } else {
                    update_post_meta($vid, '_stock_status', $vdata['status']);
                }
                wc_delete_product_transients($vid);
            }
            WC_Product_Variable::sync($id);
        }
        
        $p = wc_get_product($id); // Refresh
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0">Changes saved successfully.</div>';
    }

    $all_cats = get_terms(['taxonomy'=>'product_cat', 'hide_empty'=>false]);
    $cur_cats = wp_get_post_terms($id, 'product_cat', ['fields'=>'ids']);
    $logs = get_post_meta($id, '_b2b_stock_log', true) ?: [];

    b2b_adm_header('Edit: ' . $p->get_name());
    ?>
    
    <style>
        .grid-edit { display: grid; grid-template-columns: 3fr 1fr; gap: 25px; align-items: start; }
        .edit-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 20px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .edit-card h3 { margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #f3f4f6; font-size: 15px; color: #111827; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .cat-wrapper { max-height: 250px; overflow-y: auto; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; }
        .cat-row { display: flex; align-items: center; gap: 8px; padding: 4px 0; font-size: 13px; color: #374151; cursor: pointer; }
        .cat-row input { margin: 0; width: 16px; height: 16px; }
        
        .var-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .var-table th { background: #f3f4f6; text-align: left; padding: 10px; border-bottom: 2px solid #e5e7eb; }
        .var-table td { padding: 10px; border-bottom: 1px solid #e5e7eb; vertical-align: middle; }
        .var-input { width: 80px; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px; }
        
        @media(max-width:900px) { .grid-edit { grid-template-columns: 1fr; } }
    </style>

    <!-- Back Button at Top -->
    <div style="margin-bottom:15px;">
        <a href="<?= home_url('/b2b-panel/products') ?>" style="text-decoration:none;color:#6b7280;font-size:14px;display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:white;transition:all 0.2s;">
            <i class="fa-solid fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <!-- Product Type Badge -->
    <div style="margin-bottom:20px;">
        <span style="background:<?= $is_variable?'#fef3c7':'#d1fae5' ?>;color:<?= $is_variable?'#92400e':'#065f46' ?>;padding:5px 12px;border-radius:20px;font-size:12px;font-weight:700">
            <?= $is_variable ? 'VARIABLE PRODUCT' : 'SIMPLE PRODUCT' ?>
        </span>
    </div>

    <form method="post" class="grid-edit">
        <!-- LEFT COLUMN -->
        <div>
            <!-- GENERAL INFO -->
            <div class="edit-card">
                <h3>General Information</h3>
                <div style="margin-bottom:15px"><label>Product Name</label><input type="text" value="<?= esc_attr($p->get_name()) ?>" disabled style="background:#f3f4f6;color:#6b7280;cursor:not-allowed"></div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div><label>SKU</label><input type="text" name="sku" value="<?= $p->get_sku() ?>"></div>
                    <?php if (!$is_variable): ?>
                        <div><label>Regular Price</label><input type="number" step="0.01" name="price" value="<?= $p->get_regular_price() ?>"></div>
                        <div><label>Sale Price</label><input type="number" step="0.01" name="sale_price" value="<?= $p->get_sale_price() ?>"></div>
                    <?php else: ?>
                        <div><label>Base Price (Optional)</label><input type="number" step="0.01" name="price" value="<?= $p->get_regular_price() ?>"></div>
                        <div><label>Base Sale Price (Optional)</label><input type="number" step="0.01" name="sale_price" value="<?= $p->get_sale_price() ?>"></div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- SHIPPING SETTINGS -->
            <div class="edit-card" style="border-top:4px solid #a855f7;">
                <h3 style="color:#a855f7;"><i class="fa-solid fa-truck"></i> Shipping</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                    <div><label>Weight (kg)</label><input type="number" step="0.01" name="weight" value="<?= $p->get_weight() ?>"></div>
                    <div><label>Length (cm)</label><input type="number" step="0.01" name="length" value="<?= $p->get_length() ?>"></div>
                    <div><label>Width (cm)</label><input type="number" step="0.01" name="width" value="<?= $p->get_width() ?>"></div>
                    <div><label>Height (cm)</label><input type="number" step="0.01" name="height" value="<?= $p->get_height() ?>"></div>
                </div>
            </div>
            
            <!-- TAX SETTINGS -->
            <div class="edit-card" style="border-top:4px solid #6366f1;">
                <h3 style="color:#6366f1;"><i class="fa-solid fa-receipt"></i> Tax Settings</h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                    <div>
                        <label>Tax Status</label>
                        <select name="tax_status">
                            <option value="taxable" <?= selected($p->get_tax_status(), 'taxable', false) ?>>Taxable</option>
                            <option value="shipping" <?= selected($p->get_tax_status(), 'shipping', false) ?>>Shipping only</option>
                            <option value="none" <?= selected($p->get_tax_status(), 'none', false) ?>>None</option>
                        </select>
                    </div>
                    <div>
                        <label>Tax Class</label>
                        <select name="tax_class">
                            <option value="" <?= selected($p->get_tax_class(), '', false) ?>>Standard</option>
                            <option value="reduced-rate" <?= selected($p->get_tax_class(), 'reduced-rate', false) ?>>Reduced rate</option>
                            <option value="zero-rate" <?= selected($p->get_tax_class(), 'zero-rate', false) ?>>Zero rate</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- PARENT STOCK MANAGEMENT (Available for ALL types now) -->
            <div class="edit-card">
                <h3>Inventory (Global / Parent)</h3>
                <div style="background:#f0f9ff;padding:10px;border-left:4px solid #0ea5e9;margin-bottom:15px;font-size:12px;color:#0369a1">
                    <?= $is_variable ? 'For variables: This stock is used if a variation does NOT manage its own stock.' : 'Main stock for this simple product.' ?>
                </div>
                
                <label style="display:flex;align-items:center;gap:10px;margin-bottom:20px;padding:10px;background:#f9fafb;border-radius:6px;border:1px solid #e5e7eb;cursor:pointer">
                    <input type="checkbox" name="manage_stock" <?= checked($p->managing_stock(), true, false) ?> style="width:20px;height:20px;margin:0"> 
                    <span>Enable Stock Management (Parent Level)</span>
                </label>
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div><label>Stock Quantity</label><input type="number" name="stock_qty" value="<?= $p->get_stock_quantity() ?>"></div>
                    <div><label>Stock Status</label>
                        <select name="stock_status">
                            <option value="instock" <?= selected($p->get_stock_status(),'instock') ?>>In Stock</option>
                            <option value="outofstock" <?= selected($p->get_stock_status(),'outofstock') ?>>Out of Stock</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- VARIATIONS TABLE (Only if Variable) -->
            <?php if ($is_variable): ?>
            <div class="edit-card">
                <h3>Variations</h3>
                <?php $variations = $p->get_children(); 
                if (empty($variations)) { echo '<p style="color:red">No variations created yet.</p>'; } else { ?>
                <div style="overflow-x:auto">
                    <table class="var-table">
                        <thead><tr><th>Attributes</th><th>Price</th><th>Manage Stock</th><th>Qty</th><th>Status</th></tr></thead>
                        <tbody>
                        <?php foreach ($variations as $vid): 
                            $v = wc_get_product($vid);
                            $attrs = []; foreach($v->get_attributes() as $k=>$val) $attrs[] = ucfirst(str_replace('pa_','',$k)).': <b>'.$val.'</b>';
                        ?>
                        <tr>
                            <td><small>#<?= $vid ?></small><br><?= implode(', ', $attrs) ?></td>
                            <td><input type="number" step="0.01" name="vars[<?= $vid ?>][price]" value="<?= $v->get_regular_price() ?>" class="var-input" style="width:80px"></td>
                            <td style="text-align:center"><input type="checkbox" name="vars[<?= $vid ?>][manage]" value="yes" <?= checked($v->managing_stock(), true, false) ?>></td>
                            <td><input type="number" name="vars[<?= $vid ?>][qty]" value="<?= $v->get_stock_quantity() ?>" class="var-input"></td>
                            <td><select name="vars[<?= $vid ?>][status]" style="padding:5px;font-size:12px"><option value="instock" <?= selected($v->get_stock_status(),'instock') ?>>In</option><option value="outofstock" <?= selected($v->get_stock_status(),'outofstock') ?>>Out</option></select></td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php } ?>
            </div>
            <?php endif; ?>

            <div class="edit-card">
                <h3>Descriptions</h3>
                <label>Short Description</label><textarea name="short_desc" style="height:80px"><?= esc_textarea($p->get_short_description()) ?></textarea>
                <label>Long Description</label><textarea name="long_desc" style="height:200px"><?= esc_textarea($p->get_description()) ?></textarea>
            </div>
        </div>

        <!-- RIGHT COLUMN -->
        <div>
            <div class="edit-card" style="border-top: 3px solid #2563eb">
                <h3>Publish</h3>
                <label>Status</label>
                <select name="status" style="margin-bottom:15px"><option value="publish" <?= selected($p->get_status(),'publish') ?>>Active</option><option value="draft" <?= selected($p->get_status(),'draft') ?>>Draft</option></select>
                <button type="submit" style="width:100%;padding:12px;background:#2563eb;color:white;border:none;border-radius:6px;font-weight:600;cursor:pointer;margin-bottom:10px;">
                    <i class="fa-solid fa-save"></i> Save Changes
                </button>
                <button type="button" id="delete-product-detail-btn" data-product-id="<?= $id ?>" data-product-name="<?= esc_attr($p->get_name()) ?>" style="width:100%;padding:12px;background:#dc2626;color:white;border:none;border-radius:6px;font-weight:600;cursor:pointer;">
                    <i class="fa-solid fa-trash"></i> Delete Product
                </button>
            </div>

            <div class="edit-card">
                <h3>Categories</h3>
                <div class="cat-wrapper"><?php foreach($all_cats as $cat): ?><label class="cat-row"><input type="checkbox" name="cats[]" value="<?= $cat->term_id ?>" <?= in_array($cat->term_id, $cur_cats)?'checked':'' ?>> <?= esc_html($cat->name) ?></label><?php endforeach; ?></div>
            </div>

            <div class="edit-card">
                <h3>Extra Services</h3>
                <label style="display:flex;align-items:center;gap:10px;margin-bottom:10px"><input type="checkbox" name="assembly" <?= checked(get_post_meta($id,'_assembly_enabled',true),'yes',false) ?> style="width:18px;height:18px;margin:0"> Assembly Service</label>
                <input type="number" name="assembly_price" placeholder="Price" value="<?= get_post_meta($id,'_assembly_price',true) ?>" step="0.01">
                <div style="margin-top:10px"><label>Tax</label><select name="assembly_tax"><option value="no">Excluded</option><option value="yes" <?= selected(get_post_meta($id,'_assembly_tax',true),'yes',false) ?>>Included</option></select></div>
            </div>

            <div class="edit-card">
                <h3>History</h3>
                <div style="max-height:200px;overflow-y:auto;font-size:12px;color:#666">
                    <?php if(empty($logs)): echo "No logs."; else: foreach($logs as $l): ?>
                    <div style="border-bottom:1px solid #eee;padding:8px 0"><span style="float:right;color:#9ca3af;font-size:10px"><?= date('d M H:i', strtotime($l['date'])) ?></span><strong><?= $l['type'] ?></strong><br><?= $l['user'] ?>: <span style="text-decoration:line-through;color:#ef4444"><?= $l['old'] ?></span> &rarr; <span style="font-weight:bold;color:#10b981"><?= $l['new'] ?></span><div style="color:#aaa;font-size:10px"><?=$l['msg']?></div></div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </form>
    
    <script>
    // Delete Product from Detail Page
    document.getElementById('delete-product-detail-btn').addEventListener('click', function() {
        const productId = this.getAttribute('data-product-id');
        const productName = this.getAttribute('data-product-name');
        
        if(!confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
            return;
        }
        
        // Disable button during request
        this.disabled = true;
        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Deleting...';
        
        fetch('<?= admin_url('admin-ajax.php') ?>', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=b2b_delete_product&product_id=' + productId + '&nonce=<?= wp_create_nonce("b2b_delete_product") ?>'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                alert('Product deleted successfully!');
                window.location.href = '<?= home_url('/b2b-panel/products') ?>';
            } else {
                alert('Error: ' + (data.data || 'Unknown error'));
                this.disabled = false;
                this.innerHTML = '<i class="fa-solid fa-trash"></i> Delete Product';
            }
        })
        .catch(error => {
            alert('Error deleting product: ' + error);
            this.disabled = false;
            this.innerHTML = '<i class="fa-solid fa-trash"></i> Delete Product';
        });
    });
    </script>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   10B. PAGE: PRODUCT ADD NEW (Internal)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'product_add_new') return;
    b2b_adm_guard();
    
    // Handle form submission
    if (isset($_POST['create_product'])) {
        $product_name = sanitize_text_field($_POST['product_name']);
        $product_sku = sanitize_text_field($_POST['product_sku']);
        $product_price = floatval($_POST['product_price']);
        $product_sale_price = floatval($_POST['product_sale_price'] ?? 0);
        $product_stock = intval($_POST['product_stock']);
        $product_description = wp_kses_post($_POST['product_description'] ?? '');
        $product_short_description = wp_kses_post($_POST['product_short_description'] ?? '');
        $product_categories = isset($_POST['product_categories']) ? array_map('intval', $_POST['product_categories']) : [];
        $product_weight = sanitize_text_field($_POST['product_weight'] ?? '');
        $product_length = sanitize_text_field($_POST['product_length'] ?? '');
        $product_width = sanitize_text_field($_POST['product_width'] ?? '');
        $product_height = sanitize_text_field($_POST['product_height'] ?? '');
        $stock_status = sanitize_text_field($_POST['stock_status'] ?? 'instock');
        $tax_status = sanitize_text_field($_POST['tax_status'] ?? 'taxable');
        $tax_class = sanitize_text_field($_POST['tax_class'] ?? '');
        $product_type = sanitize_text_field($_POST['product_type'] ?? 'simple');
        
        // Create new product
        $product = new WC_Product_Simple();
        $product->set_name($product_name);
        $product->set_status('draft'); // Start as draft
        
        if($product_sku) {
            $product->set_sku($product_sku);
        }
        
        // Pricing
        if($product_price > 0) {
            $product->set_regular_price($product_price);
        }
        if($product_sale_price > 0 && $product_sale_price < $product_price) {
            $product->set_sale_price($product_sale_price);
        }
        
        // Description
        if($product_description) {
            $product->set_description($product_description);
        }
        if($product_short_description) {
            $product->set_short_description($product_short_description);
        }
        
        // Categories
        if(!empty($product_categories)) {
            $product->set_category_ids($product_categories);
        }
        
        // Weight & Dimensions
        if($product_weight) {
            $product->set_weight($product_weight);
        }
        if($product_length) {
            $product->set_length($product_length);
        }
        if($product_width) {
            $product->set_width($product_width);
        }
        if($product_height) {
            $product->set_height($product_height);
        }
        
        // Stock management
        $product->set_manage_stock(true);
        $product->set_stock_quantity($product_stock);
        $product->set_stock_status($stock_status);
        
        // Tax
        $product->set_tax_status($tax_status);
        if($tax_class) {
            $product->set_tax_class($tax_class);
        }
        
        // Save and get ID
        $new_id = $product->save();
        
        if($new_id) {
            // Redirect to edit page
            wp_redirect(home_url('/b2b-panel/products/edit?id=' . $new_id . '&created=1'));
            exit;
        }
    }
    
    // Get categories for dropdown
    $product_categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC'
    ]);
    
    b2b_adm_header('Add New Product');
    ?>
    <div class="page-header">
        <h1 class="page-title">Add New Product</h1>
    </div>
    
    <div class="card" style="max-width:1000px;">
        <form method="POST">
            <!-- Basic Information -->
            <h3 style="margin-top:0;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                <i class="fa-solid fa-info-circle" style="color:#3b82f6;"></i> Basic Information
            </h3>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Product Name *</label>
                <input type="text" name="product_name" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="Enter product name">
            </div>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">SKU</label>
                    <input type="text" name="product_sku" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="Product SKU">
                    <small style="color:#6b7280;">Optional. Leave empty for auto-generate.</small>
                </div>
                
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Categories</label>
                    <select name="product_categories[]" multiple style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;min-height:100px;">
                        <?php if(!empty($product_categories)): foreach($product_categories as $cat): ?>
                        <option value="<?= $cat->term_id ?>"><?= esc_html($cat->name) ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                    <small style="color:#6b7280;">Hold Ctrl/Cmd to select multiple</small>
                </div>
            </div>
            
            <!-- Descriptions -->
            <h3 style="margin-top:30px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                <i class="fa-solid fa-file-lines" style="color:#10b981;"></i> Descriptions
            </h3>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Short Description</label>
                <textarea name="product_short_description" rows="3" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="Brief product summary (shown in product listings)"></textarea>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Full Description</label>
                <textarea name="product_description" rows="6" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="Detailed product description"></textarea>
            </div>
            
            <!-- Pricing -->
            <h3 style="margin-top:30px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                <i class="fa-solid fa-dollar-sign" style="color:#f59e0b;"></i> Pricing
            </h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Regular Price ($) *</label>
                    <input type="number" name="product_price" step="0.01" min="0" value="0" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="0.00">
                </div>
                
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Sale Price ($)</label>
                    <input type="number" name="product_sale_price" step="0.01" min="0" value="0" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="0.00">
                    <small style="color:#6b7280;">Optional. Must be less than regular price.</small>
                </div>
            </div>
            
            <!-- Inventory -->
            <h3 style="margin-top:30px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                <i class="fa-solid fa-boxes-stacked" style="color:#8b5cf6;"></i> Inventory
            </h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Stock Quantity</label>
                    <input type="number" name="product_stock" min="0" value="0" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                </div>
                
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Stock Status</label>
                    <select name="stock_status" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                        <option value="instock">In Stock</option>
                        <option value="outofstock">Out of Stock</option>
                        <option value="onbackorder">On Backorder</option>
                    </select>
                </div>
            </div>
            
            <!-- Shipping -->
            <h3 style="margin-top:30px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                <i class="fa-solid fa-truck" style="color:#ef4444;"></i> Shipping
            </h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Weight (kg)</label>
                    <input type="number" name="product_weight" step="0.01" min="0" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="0.00">
                </div>
                
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Length (cm)</label>
                    <input type="number" name="product_length" step="0.01" min="0" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="0.00">
                </div>
                
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Width (cm)</label>
                    <input type="number" name="product_width" step="0.01" min="0" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="0.00">
                </div>
                
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Height (cm)</label>
                    <input type="number" name="product_height" step="0.01" min="0" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;" placeholder="0.00">
                </div>
            </div>
            
            <!-- Tax Settings -->
            <h3 style="margin-top:30px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                <i class="fa-solid fa-receipt" style="color:#14b8a6;"></i> Tax Settings
            </h3>
            
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Tax Status</label>
                    <select name="tax_status" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                        <option value="taxable">Taxable</option>
                        <option value="shipping">Shipping only</option>
                        <option value="none">None</option>
                    </select>
                </div>
                
                <div>
                    <label style="display:block;margin-bottom:8px;font-weight:600;color:#374151;">Tax Class</label>
                    <select name="tax_class" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                        <option value="">Standard</option>
                        <option value="reduced-rate">Reduced rate</option>
                        <option value="zero-rate">Zero rate</option>
                    </select>
                </div>
            </div>
            
            <input type="hidden" name="product_type" value="simple">
            
            <div style="padding:20px;background:#f0f9ff;border:1px solid #bfdbfe;border-radius:8px;margin-bottom:20px;margin-top:30px;">
                <p style="margin:0;color:#1e40af;"><i class="fa-solid fa-info-circle"></i> <strong>Note:</strong> Product will be created as <strong>draft</strong>. You can add images and additional details on the edit page.</p>
            </div>
            
            <div style="display:flex;gap:10px;">
                <button type="submit" name="create_product" class="primary" style="padding:12px 24px;">
                    <i class="fa-solid fa-plus"></i> Create Product
                </button>
                <a href="<?= home_url('/b2b-panel/products') ?>">
                    <button type="button" class="secondary" style="padding:12px 24px;">Cancel</button>
                </a>
            </div>
        </form>
    </div>
    
    <?php b2b_adm_footer(); exit;
});
  
/* =====================================================
   11. PAGE: CUSTOMERS (B2BKING FIXED)
===================================================== */
add_action('template_redirect', function () {
    $page = get_query_var('b2b_adm_page');
    if (!in_array($page, ['customers', 'customer_edit'])) return;
    b2b_adm_guard();

    // -- CUSTOMER LIST --
    if ($page === 'customers') {
        $paged = max(1, $_GET['paged'] ?? 1);
        $s = isset($_GET['s']) ? trim($_GET['s']) : '';
        $per_page = isset($_GET['per_page']) ? intval($_GET['per_page']) : 20;
        $per_page = in_array($per_page, [10, 20, 50, 100]) ? $per_page : 20; // Validate per_page value
        
        // Filter parameters
        $filter_group = isset($_GET['filter_group']) ? sanitize_text_field($_GET['filter_group']) : '';
        $filter_role = isset($_GET['filter_role']) ? sanitize_text_field($_GET['filter_role']) : '';
        
        $args = [
            'role__in' => ['customer', 'subscriber', 'sales_agent'], 
            'number'   => $per_page,
            'offset'   => ($paged - 1) * $per_page,
            'search'   => $s ? "*{$s}*" : '',
            'orderby'  => 'registered',
            'order'    => 'DESC'
        ];
        
        // Add meta query for filters
        $meta_query = [];
        if($filter_group) {
            $meta_query[] = [
                'key' => 'b2b_group_slug',
                'value' => $filter_group,
                'compare' => '='
            ];
        }
        if($filter_role) {
            $meta_query[] = [
                'key' => 'b2b_role',
                'value' => $filter_role,
                'compare' => '='
            ];
        }
        if(!empty($meta_query)) {
            $args['meta_query'] = $meta_query;
            if(count($meta_query) > 1) {
                $args['meta_query']['relation'] = 'AND';
            }
        }
        
        $user_query = new WP_User_Query($args);
        $users = $user_query->get_results();
        $total_users = $user_query->get_total();
        $total_pages = ceil($total_users / $per_page);
        
        // Get groups and roles for filters
        $all_groups = b2b_get_groups();
        $all_roles = get_option('b2b_roles', [
            ['slug' => 'customer', 'name' => 'Customer'],
            ['slug' => 'wholesaler', 'name' => 'Wholesaler'],
            ['slug' => 'retailer', 'name' => 'Retailer']
        ]);

        b2b_adm_header('Customer Management');
        ?>
        <div class="page-header"><h1 class="page-title">Customers</h1></div>
        
        <div class="card">
            <!-- Toolbar -->
            <div style="display:flex;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:15px;align-items:center">
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                    <div class="col-toggler">
                        <button type="button" class="secondary" onclick="document.querySelector('#cColDrop').classList.toggle('active')"><i class="fa-solid fa-table-columns"></i> Columns</button>
                        <div id="cColDrop" class="col-dropdown">
                            <label><input type="checkbox" checked data-col="0"> ID</label>
                            <label><input type="checkbox" checked data-col="1"> Customer Info</label>
                            <label><input type="checkbox" checked data-col="2"> Contact</label>
                            <label><input type="checkbox" checked data-col="3"> B2B Group</label>
                            <label><input type="checkbox" checked data-col="4"> Location</label>
                            <label><input type="checkbox" checked data-col="5"> Role</label>
                            <label><input type="checkbox" checked data-col="6"> Actions</label>
                        </div>
                    </div>
                    
                    <!-- Per Page Selector -->
                    <select onchange="window.location.href='<?= home_url('/b2b-panel/customers') ?>?per_page='+this.value+'<?= $s ? '&s='.urlencode($s) : '' ?><?= $filter_group ? '&filter_group='.urlencode($filter_group) : '' ?><?= $filter_role ? '&filter_role='.urlencode($filter_role) : '' ?>'" style="margin:0;max-width:120px;">
                        <option value="10" <?= selected($per_page, 10) ?>>10 per page</option>
                        <option value="20" <?= selected($per_page, 20) ?>>20 per page</option>
                        <option value="50" <?= selected($per_page, 50) ?>>50 per page</option>
                        <option value="100" <?= selected($per_page, 100) ?>>100 per page</option>
                    </select>
                    
                    <!-- Group Filter -->
                    <select onchange="window.location.href='<?= home_url('/b2b-panel/customers') ?>?filter_group='+this.value+'<?= $per_page != 20 ? '&per_page='.$per_page : '' ?><?= $s ? '&s='.urlencode($s) : '' ?><?= $filter_role ? '&filter_role='.urlencode($filter_role) : '' ?>'" style="margin:0;max-width:150px;">
                        <option value="">All Groups</option>
                        <?php foreach($all_groups as $slug => $group): ?>
                            <option value="<?= esc_attr($slug) ?>" <?= selected($filter_group, $slug) ?>><?= esc_html($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- Role Filter -->
                    <select onchange="window.location.href='<?= home_url('/b2b-panel/customers') ?>?filter_role='+this.value+'<?= $per_page != 20 ? '&per_page='.$per_page : '' ?><?= $s ? '&s='.urlencode($s) : '' ?><?= $filter_group ? '&filter_group='.urlencode($filter_group) : '' ?>'" style="margin:0;max-width:150px;">
                        <option value="">All Roles</option>
                        <?php foreach($all_roles as $role): ?>
                            <option value="<?= esc_attr($role['slug']) ?>" <?= selected($filter_role, $role['slug']) ?>><?= esc_html($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php if($filter_group || $filter_role): ?>
                        <a href="<?= home_url('/b2b-panel/customers') ?>?<?= $per_page != 20 ? 'per_page='.$per_page : '' ?><?= $s ? ($per_page != 20 ? '&' : '').'s='.urlencode($s) : '' ?>" style="padding:8px 12px;color:#ef4444;text-decoration:none;white-space:nowrap;background:#fee2e2;border-radius:6px;font-size:13px;"><i class="fa-solid fa-times"></i> Clear Filters</a>
                    <?php endif; ?>
                </div>

                <div style="flex:1;display:flex;justify-content:flex-end;gap:10px">
                    <span style="align-self:center;font-size:12px;color:#6b7280;margin-right:10px">
                        <?php if($filter_group || $filter_role): ?>
                            Filtered: <strong><?= $total_users ?></strong>
                        <?php else: ?>
                            Total: <strong><?= $total_users ?></strong>
                        <?php endif; ?>
                    </span>
                    <form style="display:flex;gap:5px">
                        <?php if($per_page != 20): ?><input type="hidden" name="per_page" value="<?= $per_page ?>"><?php endif; ?>
                        <?php if($filter_group): ?><input type="hidden" name="filter_group" value="<?= esc_attr($filter_group) ?>"><?php endif; ?>
                        <?php if($filter_role): ?><input type="hidden" name="filter_role" value="<?= esc_attr($filter_role) ?>"><?php endif; ?>
                        <input name="s" value="<?= esc_attr($s) ?>" placeholder="Search customers..." style="margin:0;max-width:250px">
                        <button>Search</button>
                        <?php if($s): ?><a href="<?= home_url('/b2b-panel/customers') ?>?<?= $per_page != 20 ? 'per_page='.$per_page : '' ?><?= $filter_group ? ($per_page != 20 ? '&' : '').'filter_group='.urlencode($filter_group) : '' ?><?= $filter_role ? ($per_page != 20 || $filter_group ? '&' : '').'filter_role='.urlencode($filter_role) : '' ?>" style="padding:10px;color:#ef4444;text-decoration:none">Reset</a><?php endif; ?>
                    </form>
                </div>
            </div>
            
            <table id="custTable">
                <thead><tr>
                    <th data-col="0">ID</th>
                    <th data-col="1">Customer</th>
                    <th data-col="2">Contact</th>
                    <th data-col="3">B2B Group</th>
                    <th data-col="4">Location</th>
                    <th data-col="5">Role</th>
                    <th data-col="6" style="text-align:right">Action</th>
                </tr></thead>
                <tbody>
                <?php if(empty($users)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:#999">No customers found.</td></tr>
                <?php else: foreach ($users as $u): 
                    $phone = get_user_meta($u->ID, 'billing_phone', true);
                    $city = get_user_meta($u->ID, 'billing_city', true);
                    $country = get_user_meta($u->ID, 'billing_country', true);
                    
                    // --- B2B MODULE (Custom System) ---
                    $group_slug = get_user_meta($u->ID, 'b2b_group_slug', true); 
                    $group_name = '-';
                    $is_b2b = false;
                    
                    if ($group_slug) {
                        $all_groups = b2b_get_groups();
                        if (isset($all_groups[$group_slug])) {
                            $group_name = $all_groups[$group_slug]['name'];
                            $is_b2b = true;
                        }
                    }
                    // -------------------
                    
                    // Get B2B role
                    $b2b_role_slug = get_user_meta($u->ID, 'b2b_role', true);
                    $b2b_role_name = '-';
                    if($b2b_role_slug) {
                        foreach($all_roles as $role) {
                            if($role['slug'] == $b2b_role_slug) {
                                $b2b_role_name = $role['name'];
                                break;
                            }
                        }
                    }

                    $role_bg = $b2b_role_slug ? '#dbeafe' : '#f3f4f6';
                    $role_col = $b2b_role_slug ? '#1e40af' : '#6b7280';
                ?>
                <tr>
                    <td data-col="0">#<?= $u->ID ?></td>
                    <td data-col="1">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:35px;height:35px;background:#f3f4f6;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#9ca3af;font-size:14px">
                                <?= strtoupper(substr($u->display_name,0,1)) ?>
                            </div>
                            <div>
                                <strong style="color:#111827"><?= esc_html($u->display_name) ?></strong><br>
                                <small style="color:#9ca3af">@<?= $u->user_login ?></small>
                            </div>
                        </div>
                    </td>
                    <td data-col="2">
                        <div style="font-size:12px;line-height:1.4">
                            <i class="fa-regular fa-envelope" style="width:15px;color:#9ca3af"></i> <?= esc_html($u->user_email) ?><br>
                            <i class="fa-solid fa-phone" style="width:15px;color:#9ca3af"></i> <?= esc_html($phone ?: '-') ?>
                        </div>
                    </td>
                    <td data-col="3">
                        <span style="background:<?= $is_b2b ? '#d1fae5' : '#f3f4f6' ?>;color:<?= $is_b2b ? '#065f46' : '#6b7280' ?>;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600">
                            <?= esc_html($group_name) ?>
                        </span>
                    </td>
                    <td data-col="4">
                        <small style="color:#6b7280">
                            <?= $city ? "$city, $country" : '-' ?>
                        </small>
                    </td>
                    <td data-col="5">
                        <span style="background:<?= $role_bg ?>;color:<?= $role_col ?>;padding:3px 8px;border-radius:4px;font-size:10px;font-weight:700;text-transform:uppercase">
                            <?= esc_html($b2b_role_name) ?>
                        </span>
                    </td>
                    <td data-col="6" style="text-align:right">
                        <a href="<?= home_url('/b2b-panel/customers/edit?id='.$u->ID) ?>">
                            <button class="secondary" style="padding:6px 12px;font-size:12px">Edit</button>
                        </a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php if($total_pages > 1): ?>
            <div style="margin-top:20px;display:flex;justify-content:center;align-items:center;gap:10px;">
                <span style="color:#6b7280;font-size:14px;">Page:</span>
                <select onchange="window.location.href=this.value" style="margin:0;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:white;cursor:pointer;">
                    <?php 
                    for($i = 1; $i <= $total_pages; $i++) {
                        $page_params = [];
                        if($s) $page_params[] = 's=' . urlencode($s);
                        if($per_page != 20) $page_params[] = 'per_page=' . $per_page;
                        if($filter_group) $page_params[] = 'filter_group=' . urlencode($filter_group);
                        if($filter_role) $page_params[] = 'filter_role=' . urlencode($filter_role);
                        if($i > 1) $page_params[] = 'paged=' . $i;
                        $page_url = home_url('/b2b-panel/customers') . (!empty($page_params) ? '?' . implode('&', $page_params) : '');
                        $selected = ($i == $paged) ? 'selected' : '';
                        echo '<option value="' . esc_attr($page_url) . '" ' . $selected . '>Page ' . $i . ' of ' . $total_pages . '</option>';
                    }
                    ?>
                </select>
                <span style="color:#6b7280;font-size:14px;">
                    (Showing <?= min($per_page * $paged, $total_users) ?> of <?= $total_users ?> customers)
                </span>
            </div>
            <?php endif; ?>
        </div>
        
        <script>
        // Customers Column Toggle with localStorage
        function toggleColC(idx, show) { 
            var rows = document.getElementById('custTable').rows; 
            for(var i=0;i<rows.length;i++) { 
                if(rows[i].cells.length>idx) rows[i].cells[idx].style.display=show?'':'none'; 
            }
            // Save state to localStorage
            var colStates = JSON.parse(localStorage.getItem('b2b_customers_columns') || '{}');
            colStates[idx] = show;
            localStorage.setItem('b2b_customers_columns', JSON.stringify(colStates));
        }
        
        // Restore column visibility from localStorage
        var savedColStates = JSON.parse(localStorage.getItem('b2b_customers_columns') || '{}');
        document.querySelectorAll('#cColDrop input').forEach(function(cb, i){ 
            // Restore saved state if exists
            if(savedColStates.hasOwnProperty(i)) {
                cb.checked = savedColStates[i];
                toggleColC(i, savedColStates[i]);
            }
            cb.addEventListener('change', function(){ toggleColC(i, this.checked); }); 
        });
        </script>
        <?php
        b2b_adm_footer(); exit;
    }

    // -- CUSTOMER EDIT --
    if ($page === 'customer_edit') {
        $id = intval($_GET['id']);
        $u = get_userdata($id);
        if (!$u) wp_die('User not found');

        // SAVE PROCESS
        if ($_POST) {
            // Core WP Fields
            wp_update_user([
                'ID' => $id,
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name'  => sanitize_text_field($_POST['last_name']),
                'user_email' => sanitize_email($_POST['email']),
                'display_name' => sanitize_text_field($_POST['first_name'] . ' ' . $_POST['last_name'])
            ]);

            // Billing (Batch update for performance)
            $billing_data = [
                'billing_first_name' => sanitize_text_field($_POST['first_name']),
                'billing_last_name' => sanitize_text_field($_POST['last_name']),
                'billing_phone' => sanitize_text_field($_POST['phone']),
                'billing_company' => sanitize_text_field($_POST['company']),
                'billing_address_1' => sanitize_text_field($_POST['address_1']),
                'billing_city' => sanitize_text_field($_POST['city']),
                'billing_postcode' => sanitize_text_field($_POST['postcode']),
            ];
            
            // Shipping
            $shipping_data = [
                'shipping_address_1' => sanitize_text_field($_POST['s_address_1']),
                'shipping_city' => sanitize_text_field($_POST['s_city']),
                'shipping_postcode' => sanitize_text_field($_POST['s_postcode']),
                'shipping_company' => sanitize_text_field($_POST['s_company']),
            ];
            
            // B2B Module Data
            $b2b_data = [];
            if(isset($_POST['assigned_agent'])) $b2b_data['bagli_agent_id'] = intval($_POST['assigned_agent']);
            if(isset($_POST['b2b_group'])) $b2b_data['b2b_group_slug'] = sanitize_text_field($_POST['b2b_group']);
            if(isset($_POST['b2b_role'])) $b2b_data['b2b_role'] = sanitize_text_field($_POST['b2b_role']);
            
            // Tax Exemption
            $b2b_data['b2b_tax_exempt'] = isset($_POST['tax_exempt']) ? 1 : 0;
            $b2b_data['b2b_tax_id'] = sanitize_text_field($_POST['tax_id'] ?? '');
            $b2b_data['b2b_tax_notes'] = sanitize_textarea_field($_POST['tax_notes'] ?? '');
            
            // Shipping Overrides
            $shipping_overrides = [];
            if(isset($_POST['shipping_overrides']) && is_array($_POST['shipping_overrides'])) {
                foreach($_POST['shipping_overrides'] as $zone_id => $override_data) {
                    if(!empty($override_data['enabled'])) {
                        $shipping_overrides[$zone_id] = [
                            'flat_rate_cost' => isset($override_data['flat_rate_cost']) && $override_data['flat_rate_cost'] !== '' ? floatval($override_data['flat_rate_cost']) : null,
                            'free_shipping' => isset($override_data['free_shipping']) ? $override_data['free_shipping'] : null
                        ];
                    }
                }
            }
            $b2b_data['b2b_shipping_overrides'] = $shipping_overrides;
            
            // User-Specific Payment Permissions
            if(isset($_POST['b2b_allowed_payments']) && is_array($_POST['b2b_allowed_payments'])) {
                $b2b_data['b2b_allowed_payments'] = array_map('sanitize_text_field', $_POST['b2b_allowed_payments']);
            } else {
                $b2b_data['b2b_allowed_payments'] = []; // Empty = use group defaults
            }
            
            // Batch update all meta (Performance optimization)
            foreach(array_merge($billing_data, $shipping_data, $b2b_data) as $key => $val) {
                update_user_meta($id, $key, $val);
            }

            // Password
            if (!empty($_POST['new_pass'])) wp_set_password($_POST['new_pass'], $id);

            $u = get_userdata($id); // Refresh
            echo '<div style="background:#d1fae5;color:#065f46;padding:12px 16px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0;font-size:14px"><i class="fa-solid fa-check-circle"></i> Customer updated successfully.</div>';
        }

        // Data Prep (Optimized)
        $agent_val = get_user_meta($id, 'bagli_agent_id', true);
        $agents = get_users(['role__in' => ['sales_agent', 'administrator'], 'fields' => ['ID', 'display_name']]);
        
        // B2B Module Data (Custom System)
        $current_group = get_user_meta($id, 'b2b_group_slug', true);
        $current_role = get_user_meta($id, 'b2b_role', true);
        $allowed_payments = get_user_meta($id, 'b2b_allowed_payments', true) ?: [];
        $b2b_groups = b2b_get_groups();
        $b2b_roles = get_option('b2b_roles', ['customer' => 'Customer', 'wholesaler' => 'Wholesaler', 'retailer' => 'Retailer']);
        
        // Get all available payment gateways
        $all_gateways = [];
        if(class_exists('WC_Payment_Gateways')) {
            $wc_gateways = WC_Payment_Gateways::instance()->get_available_payment_gateways();
            foreach($wc_gateways as $gid => $gateway) {
                $all_gateways[$gid] = $gateway->get_title();
            }
        }

        b2b_adm_header('Edit Customer');
        ?>
        <style>
        .modern-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .compact-card { background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; padding: 16px; }
        .compact-card h4 { margin: 0 0 12px 0; font-size: 14px; font-weight: 600; color: #111827; display: flex; align-items: center; gap: 8px; }
        .compact-card h4 i { font-size: 13px; }
        .compact-card label { font-size: 12px; display: block; margin-bottom: 4px; color: #6b7280; font-weight: 500; }
        .compact-card select, .compact-card input[type="text"] { font-size: 13px; padding: 8px 10px; }
        .compact-card .hint { font-size: 11px; color: #9ca3af; margin-top: 6px; line-height: 1.3; }
        .compact-btn { padding: 8px 16px; font-size: 13px; }
        .payment-checkboxes { display: flex; flex-direction: column; gap: 8px; max-height: 200px; overflow-y: auto; }
        .payment-checkboxes label { display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: 12px; color: #374151; }
        .payment-checkboxes input[type="checkbox"] { margin: 0; cursor: pointer; }
        @media (max-width: 968px) { .modern-grid { grid-template-columns: 1fr; } }
        </style>
        <div style="margin-bottom:15px;display:flex;justify-content:space-between;align-items:center">
            <a href="<?= home_url('/b2b-panel/customers') ?>"><button class="secondary compact-btn">&laquo; Back to List</button></a>
            <button type="submit" form="customer-form" class="compact-btn" style="background:var(--accent);color:white;border:none"><i class="fa-solid fa-save"></i> Save Customer</button>
        </div>
        
        <form method="post" id="customer-form">
        <form method="post" id="customer-form">
            <!-- Personal Information -->
            <div class="customer-section">
                <h3><i class="fa-solid fa-user"></i> Personal Information</h3>
                <div class="form-grid">
                    <div><label>First Name <span style="color:red">*</span></label><input type="text" name="first_name" value="<?= esc_attr($u->first_name) ?>" required></div>
                    <div><label>Last Name <span style="color:red">*</span></label><input type="text" name="last_name" value="<?= esc_attr($u->last_name) ?>" required></div>
                    <div><label>Email Address <span style="color:red">*</span></label><input type="email" name="email" value="<?= esc_attr($u->user_email) ?>" required></div>
                    <div><label>Phone Number</label><input type="text" name="phone" value="<?= esc_attr(get_user_meta($id, 'billing_phone', true)) ?>"></div>
                </div>
            </div>

            <!-- Address Grid (Billing + Shipping) -->
            <div class="modern-grid">
                <div class="customer-section">
                    <h3><i class="fa-solid fa-map-pin"></i> Billing Address</h3>
                    <div class="form-grid">
                        <div><label>Company</label><input type="text" name="company" value="<?= esc_attr(get_user_meta($id, 'billing_company', true)) ?>"></div>
                        <div><label>City</label><input type="text" name="city" value="<?= esc_attr(get_user_meta($id, 'billing_city', true)) ?>"></div>
                        <div><label>Postcode</label><input type="text" name="postcode" value="<?= esc_attr(get_user_meta($id, 'billing_postcode', true)) ?>"></div>
                    </div>
                    <label>Address</label><input type="text" name="address_1" value="<?= esc_attr(get_user_meta($id, 'billing_address_1', true)) ?>">
                </div>

                <div class="customer-section">
                    <h3><i class="fa-solid fa-truck"></i> Shipping Address</h3>
                    <div class="form-grid">
                        <div><label>Company</label><input type="text" name="s_company" value="<?= esc_attr(get_user_meta($id, 'shipping_company', true)) ?>"></div>
                        <div><label>City</label><input type="text" name="s_city" value="<?= esc_attr(get_user_meta($id, 'shipping_city', true)) ?>"></div>
                        <div><label>Postcode</label><input type="text" name="s_postcode" value="<?= esc_attr(get_user_meta($id, 'shipping_postcode', true)) ?>"></div>
                    </div>
                    <label>Address</label><input type="text" name="s_address_1" value="<?= esc_attr(get_user_meta($id, 'shipping_address_1', true)) ?>">
                </div>
            </div>

            <!-- B2B Settings Grid (Role + Group + Agent) -->
            <div class="modern-grid" style="grid-template-columns: repeat(3, 1fr);">
                <div class="compact-card" style="border-top:3px solid #3b82f6;">
                    <h4><i class="fa-solid fa-user-tag"></i> B2B Role</h4>
                    <label>Customer Role</label>
                    <select name="b2b_role">
                        <option value="">-- No Role --</option>
                        <?php foreach($b2b_roles as $role_slug => $role_name): ?>
                            <option value="<?= esc_attr($role_slug) ?>" <?= selected($current_role, $role_slug) ?>><?= esc_html($role_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="hint">Assign B2B role for categorization</p>
                </div>

                <div class="compact-card" style="border-top:3px solid #10b981;">
                    <h4><i class="fa-solid fa-users-gear"></i> B2B Group</h4>
                    <label>Pricing Group</label>
                    <select name="b2b_group">
                        <option value="">-- Standard --</option>
                        <?php foreach($b2b_groups as $slug => $group_data): ?>
                            <option value="<?= esc_attr($slug) ?>" <?= selected($current_group, $slug) ?>>
                                <?= esc_html($group_data['name']) ?> (<?= $group_data['discount'] ?>% off)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="hint">Discounts & minimum order rules</p>
                </div>

                <div class="compact-card" style="border-top:3px solid #8b5cf6;">
                    <h4><i class="fa-solid fa-user-tie"></i> Sales Agent</h4>
                    <label>Assigned To</label>
                    <select name="assigned_agent">
                        <option value="">-- None --</option>
                        <?php foreach ($agents as $a): ?>
                            <option value="<?= $a->ID ?>" <?= selected($agent_val, $a->ID) ?>><?= esc_html($a->display_name) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <p class="hint">Orders visible to selected agent</p>
                </div>
            </div>

            <!-- Payment & Security Grid -->
            <div class="modern-grid">
                <div class="compact-card" style="border-top:3px solid #f59e0b;">
                    <h4><i class="fa-solid fa-credit-card"></i> Payment Permissions</h4>
                    <p class="hint" style="margin-top:0;margin-bottom:10px">User-specific payment methods (overrides group defaults)</p>
                    <?php if(!empty($all_gateways)): ?>
                    <div class="payment-checkboxes">
                        <?php foreach($all_gateways as $gid => $gtitle): ?>
                        <label>
                            <input type="checkbox" name="b2b_allowed_payments[]" value="<?= esc_attr($gid) ?>" 
                                   <?= in_array($gid, $allowed_payments) ? 'checked' : '' ?>>
                            <?= esc_html($gtitle) ?>
                        </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="hint" style="margin-bottom:0">Leave all unchecked to use group defaults</p>
                    <?php else: ?>
                    <p style="color:#ef4444;font-size:12px"><i class="fa-solid fa-exclamation-triangle"></i> No payment gateways detected</p>
                    <?php endif; ?>
                </div>

                <div class="compact-card" style="border-top:3px solid #ef4444;">
                    <h4><i class="fa-solid fa-lock"></i> Security</h4>
                    <label>New Password</label>
                    <input type="text" name="new_pass" placeholder="Leave empty to keep current password">
                    <p class="hint">Change customer password (optional)</p>
                </div>
            </div>
            
            <!-- Tax Exemption Section -->
            <?php 
            $tax_exempt = get_user_meta($id, 'b2b_tax_exempt', true);
            $tax_id = get_user_meta($id, 'b2b_tax_id', true);
            $tax_notes = get_user_meta($id, 'b2b_tax_notes', true);
            $tax_certificate = get_user_meta($id, 'b2b_tax_certificate', true);
            $tax_approved_date = get_user_meta($id, 'b2b_tax_approved_date', true);
            ?>
            <div class="customer-section" style="border-left:4px solid #10b981;">
                <h3><i class="fa-solid fa-receipt"></i> Tax Exemption Status</h3>
                <div class="form-grid">
                    <div>
                        <label style="display:flex;align-items:center;gap:10px;font-size:14px;">
                            <input type="checkbox" name="tax_exempt" value="1" <?= checked($tax_exempt, 1) ?> style="width:20px;height:20px;">
                            <span style="font-weight:600;color:#111827;">Tax Exempt</span>
                        </label>
                        <?php if($tax_exempt == 1 && $tax_approved_date): ?>
                        <p style="color:#10b981;font-size:12px;margin-top:5px;">
                            <i class="fa-solid fa-check-circle"></i> Approved on <?= date('Y-m-d', strtotime($tax_approved_date)) ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div>
                        <label>Tax ID / VAT Number</label>
                        <input type="text" name="tax_id" value="<?= esc_attr($tax_id) ?>" placeholder="e.g., VAT-123456789">
                    </div>
                </div>
                <div>
                    <label>Tax Notes / Additional Information</label>
                    <textarea name="tax_notes" rows="2" placeholder="Any notes about tax exemption"><?= esc_textarea($tax_notes) ?></textarea>
                </div>
                <?php if($tax_certificate): ?>
                <div style="margin-top:10px;padding:10px;background:#f0f9ff;border-radius:6px;">
                    <small style="color:#0369a1;font-weight:600;"><i class="fa-solid fa-file-pdf"></i> Tax Certificate:</small>
                    <a href="<?= esc_url($tax_certificate) ?>" target="_blank" style="margin-left:10px;color:#3b82f6;font-size:12px;">View Document</a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Shipping Overrides Section -->
            <?php 
            $shipping_zones = b2b_get_all_shipping_zones();
            $shipping_overrides = get_user_meta($id, 'b2b_shipping_overrides', true) ?: [];
            ?>
            <?php if(!empty($shipping_zones)): ?>
            <div class="customer-section" style="border-left:4px solid #3b82f6;">
                <h3><i class="fa-solid fa-truck"></i> Shipping Overrides (Optional)</h3>
                <p style="color:#6b7280;font-size:13px;margin-bottom:15px;">Configure custom shipping rates for this customer. Leave unchecked to use group/default rates.</p>
                
                <?php foreach($shipping_zones as $zone_id => $zone): ?>
                <?php 
                $override = $shipping_overrides[$zone_id] ?? [];
                $is_enabled = !empty($override);
                ?>
                <div style="margin-bottom:15px;padding:15px;background:#f9fafb;border-radius:8px;border:2px solid <?= $is_enabled ? '#3b82f6' : '#e5e7eb' ?>;">
                    <label style="display:flex;align-items:center;gap:10px;margin-bottom:10px;cursor:pointer;">
                        <input type="checkbox" name="shipping_overrides[<?= esc_attr($zone_id) ?>][enabled]" value="1" <?= checked($is_enabled, true) ?> onchange="this.closest('div').style.borderColor = this.checked ? '#3b82f6' : '#e5e7eb'">
                        <span style="font-weight:600;font-size:14px;"><?= esc_html($zone['name']) ?></span>
                        <span style="font-size:12px;color:#6b7280;">(<?= esc_html(implode(', ', $zone['regions'] ?? [])) ?>)</span>
                    </label>
                    
                    <div style="margin-left:30px;display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div>
                            <label style="font-size:12px;color:#6b7280;display:block;margin-bottom:5px;">Flat Rate Cost ($)</label>
                            <input type="number" name="shipping_overrides[<?= esc_attr($zone_id) ?>][flat_rate_cost]" value="<?= esc_attr($override['flat_rate_cost'] ?? '') ?>" step="0.01" min="0" placeholder="Default: $<?= esc_attr($zone['methods']['flat_rate']['cost'] ?? 0) ?>" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:13px;">
                            <small style="color:#6b7280;font-size:11px;">Leave empty for default</small>
                        </div>
                        
                        <div>
                            <label style="font-size:12px;color:#6b7280;display:block;margin-bottom:5px;">Free Shipping</label>
                            <select name="shipping_overrides[<?= esc_attr($zone_id) ?>][free_shipping]" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;font-size:13px;">
                                <option value="">Use Default</option>
                                <option value="always" <?= selected($override['free_shipping'] ?? '', 'always') ?>>Always Free</option>
                                <option value="0" <?= selected($override['free_shipping'] ?? '', '0') ?>>Minimum $0</option>
                                <option value="25" <?= selected($override['free_shipping'] ?? '', '25') ?>>Minimum $25</option>
                                <option value="50" <?= selected($override['free_shipping'] ?? '', '50') ?>>Minimum $50</option>
                                <option value="75" <?= selected($override['free_shipping'] ?? '', '75') ?>>Minimum $75</option>
                                <option value="100" <?= selected($override['free_shipping'] ?? '', '100') ?>>Minimum $100</option>
                            </select>
                            <small style="color:#6b7280;font-size:11px;">Customer-specific free shipping</small>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </form>
        <?php
        b2b_adm_footer(); exit;
    }
});

/* =====================================================
   12. B2B MODULE PAGES (Frontend Admin Panel - V10)
===================================================== */

// ==========================================================================
// A. B2B APPROVALS PAGE
// ==========================================================================
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'b2b_approvals') return;
    b2b_adm_guard();
    b2b_adm_header('B2B Approvals');
    
    if (isset($_POST['approve_user'])) {
        $uid = intval($_POST['uid']);
        $group_slug = sanitize_text_field($_POST['grp'] ?? '');
        $sales_agent = intval($_POST['sales_agent'] ?? 0);
        
        // Generate secure password
        $password = wp_generate_password(12, true, true);
        wp_set_password($password, $uid);
        
        // Update user meta
        update_user_meta($uid, 'b2b_status', 'approved');
        if (!empty($group_slug)) {
            update_user_meta($uid, 'b2b_group_slug', $group_slug);
        }
        if ($sales_agent > 0) {
            update_user_meta($uid, 'sales_agent', $sales_agent);
            // Also update the field that Sales Agent snippet expects
            update_user_meta($uid, 'bagli_agent_id', $sales_agent);
        }
        
        // Send email to customer with credentials
        $user = get_userdata($uid);
        $groups = b2b_get_groups();
        $group_info = isset($groups[$group_slug]) ? $groups[$group_slug] : null;
        
        $message = "Dear " . get_user_meta($uid, 'first_name', true) . ",\n\n";
        $message .= "Great news! Your B2B account has been approved.\n\n";
        $message .= "Login Credentials:\n";
        $message .= "Username: " . $user->user_login . "\n";
        $message .= "Password: " . $password . "\n";
        $message .= "Login URL: " . home_url('/b2b-login') . "\n\n";
        
        if ($group_info) {
            $message .= "Your B2B Group: " . $group_info['name'] . "\n";
            $message .= "Discount: " . $group_info['discount'] . "%\n";
            $message .= "Minimum Order: " . wc_price($group_info['min_order']) . "\n\n";
        }
        
        $message .= "Welcome to our B2B program!\n\nBest regards,\n" . get_bloginfo('name');
        
        wp_mail($user->user_email, 'Your B2B Account Has Been Approved', $message);
        
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0">User approved successfully! Login credentials have been sent via email.</div>';
    }
    
    $users = get_users(['meta_key' => 'b2b_status', 'meta_value' => 'pending']);
    $groups = b2b_get_groups();
    ?>
    
    <div class="page-header">
        <h1 class="page-title">B2B Approvals</h1>
        <a href="<?= home_url('/b2b-panel/b2b-module/groups') ?>"><button class="secondary">Manage Groups</button></a>
    </div>
    
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Company / Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Requested Group</th>
                    <th>Assign Group</th>
                    <th>Sales Agent</th>
                    <th style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="7" style="text-align:center;padding:30px;color:#999">No pending applications.</td></tr>
                <?php else: foreach ($users as $u): 
                    $requested_group = get_user_meta($u->ID, 'b2b_requested_group', true);
                    $agents = get_users(['role' => 'sales_agent']);
                ?>
                    <tr>
                        <td>
                            <strong><?= esc_html(get_user_meta($u->ID, 'billing_company', true) ?: $u->display_name) ?></strong><br>
                            <small style="color:#6b7280"><?= esc_html(get_user_meta($u->ID, 'first_name', true) . ' ' . get_user_meta($u->ID, 'last_name', true)) ?></small>
                        </td>
                        <td><?= esc_html($u->user_email) ?></td>
                        <td><?= esc_html(get_user_meta($u->ID, 'billing_phone', true)) ?></td>
                        <td>
                            <?php if ($requested_group && isset($groups[$requested_group])): ?>
                                <span style="background:#dbeafe;color:#1e40af;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">
                                    <?= esc_html($groups[$requested_group]['name']) ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#9ca3af">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="post" style="display:inline-flex;flex-direction:column;gap:8px;margin:0;">
                                <input type="hidden" name="uid" value="<?= $u->ID ?>">
                                <select name="grp" style="margin:0;padding:6px;font-size:13px;">
                                    <option value="">-- Standard --</option>
                                    <?php foreach ($groups as $k => $v): ?>
                                        <option value="<?= esc_attr($k) ?>" <?= $k === $requested_group ? 'selected' : '' ?>><?= esc_html($v['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                        </td>
                        <td>
                                <select name="sales_agent" style="margin:0;padding:6px;font-size:13px;">
                                    <option value="0">-- None --</option>
                                    <?php foreach ($agents as $agent): ?>
                                        <option value="<?= $agent->ID ?>"><?= esc_html($agent->display_name) ?></option>
                                    <?php endforeach; ?>
                                </select>
                        </td>
                        <td style="text-align:right">
                                <button type="submit" name="approve_user" style="padding:6px 12px;font-size:13px;background:#10b981;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:600;">
                                    <i class="fa-solid fa-check"></i> Approve & Send Credentials
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php b2b_adm_footer(); exit;
});

// ==========================================================================
// B. B2B GROUPS PAGE
// ==========================================================================
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'b2b_groups') return;
    b2b_adm_guard();
    b2b_adm_header('B2B Groups');
    
    // Save Group
    if (isset($_POST['save_grp'])) {
        $groups = b2b_get_groups();
        $slug = isset($_POST['edit_slug']) && !empty($_POST['edit_slug']) ? sanitize_key($_POST['edit_slug']) : sanitize_title($_POST['name']);
        $groups[$slug] = [
            'name' => sanitize_text_field($_POST['name']),
            'discount' => floatval($_POST['discount']),
            'min_order' => floatval($_POST['min_order'])
        ];
        update_option('b2b_dynamic_groups', $groups);
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0">Group saved successfully!</div>';
    }
    
    // Delete Group
    if (isset($_GET['del'])) {
        $groups = b2b_get_groups();
        unset($groups[sanitize_key($_GET['del'])]);
        update_option('b2b_dynamic_groups', $groups);
        wp_redirect(home_url('/b2b-panel/b2b-module/groups'));
        exit;
    }
    
    $groups = b2b_get_groups();
    $edit_group = null;
    $edit_slug = '';
    if(isset($_GET['edit'])) {
        $edit_slug = sanitize_key($_GET['edit']);
        $edit_group = $groups[$edit_slug] ?? null;
    }
    ?>
    
    <div class="page-header">
        <h1 class="page-title">B2B Groups & Members</h1>
        <a href="<?= home_url('/b2b-panel/b2b-module/settings') ?>"><button class="secondary">Settings</button></a>
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:25px;">
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;"><?= $edit_group ? 'Edit Group' : 'Add New Group' ?></h3>
            <form method="post">
                <?php if($edit_group): ?>
                <input type="hidden" name="edit_slug" value="<?= esc_attr($edit_slug) ?>">
                <?php endif; ?>
                
                <label>Group Name</label>
                <input type="text" name="name" value="<?= esc_attr($edit_group['name'] ?? '') ?>" required>
                
                <label>Discount Rate (%)</label>
                <input type="number" step="0.01" name="discount" value="<?= esc_attr($edit_group['discount'] ?? 0) ?>">
                
                <label>Minimum Order Amount</label>
                <input type="number" name="min_order" value="<?= esc_attr($edit_group['min_order'] ?? 0) ?>" step="0.01">
                
                <button type="submit" name="save_grp" style="width:100%;padding:12px;margin-top:10px;"><?= $edit_group ? 'Update Group' : 'Save Group' ?></button>
                <?php if($edit_group): ?>
                <a href="<?= home_url('/b2b-panel/b2b-module/groups') ?>" style="display:block;text-align:center;margin-top:10px;color:#6b7280;text-decoration:none;">Cancel</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;">Existing Groups</h3>
            <table>
                <thead>
                    <tr>
                        <th>Group Name</th>
                        <th>Discount</th>
                        <th>Min. Order</th>
                        <th>Members</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($groups)): ?>
                        <tr><td colspan="5" style="text-align:center;padding:20px;color:#999">No groups created yet.</td></tr>
                    <?php else: foreach ($groups as $slug => $data): 
                        $count = count(get_users(['meta_key' => 'b2b_group_slug', 'meta_value' => $slug]));
                    ?>
                        <tr style="<?= $edit_slug == $slug ? 'background:#fef3c7;' : '' ?>">
                            <td><strong><?= esc_html($data['name']) ?></strong></td>
                            <td><span style="background:#fef3c7;color:#92400e;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:600;">%<?= $data['discount'] ?></span></td>
                            <td><?= wc_price($data['min_order']) ?></td>
                            <td><?= $count ?></td>
                            <td style="text-align:right;">
                                <a href="?b2b_adm_page=b2b_groups&edit=<?= urlencode($slug) ?>">
                                    <button class="secondary" style="padding:6px 12px;font-size:12px;margin-right:5px;"><i class="fa-solid fa-pen"></i> Edit</button>
                                </a>
                                <a href="?b2b_adm_page=b2b_groups&del=<?= urlencode($slug) ?>" onclick="return confirm('Are you sure you want to delete this group?')">
                                    <button class="secondary" style="padding:6px 12px;background:#fef2f2;color:#ef4444;border-color:#fca5a5;font-size:12px;"><i class="fa-solid fa-trash"></i></button>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php b2b_adm_footer(); exit;
});

// ==========================================================================
// C. B2B SETTINGS PAGE (Payment Matrix)
// ==========================================================================
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'b2b_settings') return;
    b2b_adm_guard();
    b2b_adm_header('B2B Settings');
    
    // Save Settings
    if (isset($_POST['save_settings'])) {
        update_option('b2b_hide_prices_guest', isset($_POST['hide_prices_guest']) ? 1 : 0);
        
        // Save payment permissions matrix
        if (isset($_POST['group_payments'])) {
            $group_rules = array();
            foreach ($_POST['group_payments'] as $group_slug => $payments) {
                $group_rules[$group_slug] = array_map('sanitize_text_field', $payments);
            }
            update_option('b2b_group_payment_rules', $group_rules);
        } else {
            update_option('b2b_group_payment_rules', array());
        }
        
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0">Settings saved successfully!</div>';
    }
    
    $hide_prices = b2b_is_price_hidden_for_guests();
    $groups = b2b_get_groups();
    $group_payment_rules = b2b_get_group_payment_rules();
    
    // Get all payment gateways
    $gateways = WC()->payment_gateways->payment_gateways();
    ?>
    
    <div class="page-header">
        <h1 class="page-title">B2B Settings</h1>
        <a href="<?= home_url('/b2b-panel/b2b-module/form-editor') ?>"><button class="secondary">Form Editor</button></a>
    </div>
    
    <form method="post">
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;">Price Visibility</h3>
            <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                <input type="checkbox" name="hide_prices_guest" value="1" <?= checked($hide_prices, 1, false) ?> style="width:18px;height:18px;">
                <span>Hide prices for guest users (non-logged-in visitors)</span>
            </label>
        </div>
        
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;">Payment Permissions Matrix (Group-Based)</h3>
            <p style="color:#6b7280;font-size:13px;margin-bottom:20px;">Control which payment methods are available for each B2B group. If no permissions are set, all methods are available.</p>
            
            <?php if (empty($groups)): ?>
                <div style="background:#fef2f2;padding:20px;text-align:center;border-radius:8px;border:1px solid #fca5a5;color:#991b1b;">
                    <i class="fa-solid fa-exclamation-triangle" style="font-size:24px;margin-bottom:10px;"></i>
                    <p style="margin:0;">No B2B groups created yet. Please <a href="<?= home_url('/b2b-panel/b2b-module/groups') ?>" style="color:#dc2626;text-decoration:underline;">create groups</a> first.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr style="background:#f8fafc;">
                                <th style="width:200px;">Group</th>
                                <?php foreach ($gateways as $gateway_id => $gateway): ?>
                                    <th style="text-align:center;"><?= esc_html($gateway->get_title()) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($groups as $slug => $group_data): ?>
                                <tr>
                                    <td><strong><?= esc_html($group_data['name']) ?></strong></td>
                                    <?php foreach ($gateways as $gateway_id => $gateway): 
                                        $is_allowed = isset($group_payment_rules[$slug]) && in_array($gateway_id, $group_payment_rules[$slug]);
                                    ?>
                                        <td style="text-align:center;">
                                            <input type="checkbox" name="group_payments[<?= esc_attr($slug) ?>][]" value="<?= esc_attr($gateway_id) ?>" <?= checked($is_allowed, true, false) ?> style="width:18px;height:18px;">
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <p style="color:#6b7280;font-size:12px;margin-top:15px;font-style:italic;">
                    <i class="fa-solid fa-info-circle"></i> Note: User-specific permissions (set in customer profile) override group permissions.
                </p>
            <?php endif; ?>
        </div>
        
        <div style="text-align:right;">
            <button type="submit" name="save_settings" style="padding:12px 30px;font-size:15px;">Save All Settings</button>
        </div>
    </form>
    
    <?php b2b_adm_footer(); exit;
});

// ==========================================================================
// D. B2B FORM EDITOR PAGE
// ==========================================================================
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'b2b_form_editor') return;
    b2b_adm_guard();
    b2b_adm_header('B2B Form Editor');
    
    // Save Custom Fields
    if (isset($_POST['save_fields'])) {
        $fields = array();
        if (isset($_POST['fields'])) {
            foreach ($_POST['fields'] as $key => $field_data) {
                $fields[$key] = [
                    'label' => sanitize_text_field($field_data['label']),
                    'type' => sanitize_text_field($field_data['type']),
                    'options' => isset($field_data['options']) ? sanitize_text_field($field_data['options']) : '',
                    'required' => isset($field_data['required']) ? 1 : 0
                ];
            }
        }
        update_option('b2b_custom_fields_def', $fields);
        
        // Save standard field settings
        if (isset($_POST['standard_fields'])) {
            $std_fields = array();
            foreach ($_POST['standard_fields'] as $field_key => $field_config) {
                $std_fields[$field_key] = [
                    'enabled' => isset($field_config['enabled']) ? 1 : 0,
                    'required' => isset($field_config['required']) ? 1 : 0
                ];
            }
            update_option('b2b_standard_fields_config', $std_fields);
        }
        
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0">Form settings saved successfully!</div>';
    }
    
    $custom_fields = b2b_get_custom_fields();
    $standard_fields_config = b2b_get_standard_fields_config();
    
    // Standard WooCommerce fields
    $standard_fields = [
        'billing_company' => 'Company Name',
        'billing_phone' => 'Phone',
        'billing_city' => 'City',
        'billing_postcode' => 'Postcode',
        'billing_address_1' => 'Address',
        'billing_state' => 'State/Province',
        'billing_country' => 'Country'
    ];
    ?>
    
    <div class="page-header">
        <h1 class="page-title">B2B Form Editor</h1>
        <a href="<?= home_url('/b2b-panel/b2b-module') ?>"><button class="secondary">Back to Approvals</button></a>
    </div>
    
    <form method="post">
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;">Standard Fields (WooCommerce)</h3>
            <table>
                <thead>
                    <tr>
                        <th>Field</th>
                        <th style="text-align:center;width:100px;">Enabled</th>
                        <th style="text-align:center;width:100px;">Required</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($standard_fields as $field_key => $field_label): 
                        $is_enabled = isset($standard_fields_config[$field_key]['enabled']) ? $standard_fields_config[$field_key]['enabled'] : 1;
                        $is_required = isset($standard_fields_config[$field_key]['required']) ? $standard_fields_config[$field_key]['required'] : 0;
                    ?>
                        <tr>
                            <td><?= esc_html($field_label) ?></td>
                            <td style="text-align:center;">
                                <input type="checkbox" name="standard_fields[<?= esc_attr($field_key) ?>][enabled]" value="1" <?= checked($is_enabled, 1, false) ?> style="width:18px;height:18px;">
                            </td>
                            <td style="text-align:center;">
                                <input type="checkbox" name="standard_fields[<?= esc_attr($field_key) ?>][required]" value="1" <?= checked($is_required, 1, false) ?> style="width:18px;height:18px;">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;">Custom Fields</h3>
            <div id="custom-fields-container">
                <?php if (!empty($custom_fields)): foreach ($custom_fields as $key => $field): ?>
                    <div class="custom-field-row" style="margin-bottom:15px;padding:15px;border:1px solid #e5e7eb;background:#f9fafb;border-radius:8px;">
                        <div style="display:grid;grid-template-columns:2fr 1fr 100px 40px;gap:10px;align-items:center;margin-bottom:8px;">
                            <input type="text" name="fields[<?= esc_attr($key) ?>][label]" value="<?= esc_attr($field['label']) ?>" placeholder="Field Label" style="margin:0;" required>
                            <select name="fields[<?= esc_attr($key) ?>][type]" class="field-type-selector" style="margin:0;">
                                <option value="text" <?= selected($field['type'], 'text', false) ?>>Text</option>
                                <option value="textarea" <?= selected($field['type'], 'textarea', false) ?>>Textarea</option>
                                <option value="select" <?= selected($field['type'], 'select', false) ?>>Dropdown</option>
                            </select>
                            <label style="display:flex;align-items:center;gap:5px;margin:0;">
                                <input type="checkbox" name="fields[<?= esc_attr($key) ?>][required]" value="1" <?= checked($field['required'], 1, false) ?> style="width:18px;height:18px;margin:0;"> 
                                Required
                            </label>
                            <button type="button" onclick="if(confirm('Delete this field?')) this.closest('.custom-field-row').remove();" style="background:#ef4444;color:#fff;border:none;padding:8px;border-radius:4px;cursor:pointer;font-size:14px;" title="Delete Field">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                        <?php if ($field['type'] === 'select'): ?>
                            <input type="text" name="fields[<?= esc_attr($key) ?>][options]" value="<?= esc_attr($field['options'] ?? '') ?>" placeholder="Options (comma-separated: Option1,Option2,Option3)" style="width:100%;margin:0;font-size:12px;color:#6b7280;">
                        <?php endif; ?>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <button type="button" id="add-custom-field" class="secondary" style="margin-top:10px;"><i class="fa-solid fa-plus"></i> Add Custom Field</button>
        </div>
        
        <div style="text-align:right;">
            <button type="submit" name="save_fields" style="padding:12px 30px;font-size:15px;">Save Form Settings</button>
        </div>
    </form>
    
    <script>
    jQuery(document).ready(function($) {
        // Add custom field with delete button and options field support
        $('#add-custom-field').click(function() {
            var key = 'field_' + Date.now();
            var html = '<div class="custom-field-row" style="margin-bottom:15px;padding:15px;border:1px solid #e5e7eb;background:#f9fafb;border-radius:8px;">' +
                '<div style="display:grid;grid-template-columns:2fr 1fr 100px 40px;gap:10px;align-items:center;margin-bottom:8px;">' +
                '<input type="text" name="fields[' + key + '][label]" placeholder="Field Label" style="margin:0;" required>' +
                '<select name="fields[' + key + '][type]" class="field-type-selector" style="margin:0;">' +
                '<option value="text">Text</option>' +
                '<option value="textarea">Textarea</option>' +
                '<option value="select">Dropdown</option>' +
                '</select>' +
                '<label style="display:flex;align-items:center;gap:5px;margin:0;">' +
                '<input type="checkbox" name="fields[' + key + '][required]" value="1" style="width:18px;height:18px;margin:0;"> Required' +
                '</label>' +
                '<button type="button" onclick="if(confirm(\'Delete this field?\')) this.closest(\'.custom-field-row\').remove();" style="background:#ef4444;color:#fff;border:none;padding:8px;border-radius:4px;cursor:pointer;font-size:14px;" title="Delete Field">' +
                '<i class="fa-solid fa-trash"></i>' +
                '</button>' +
                '</div>' +
                '</div>';
            $('#custom-fields-container').append(html);
        });
        
        // Handle dropdown type selection to show/hide options field
        $(document).on('change', '.field-type-selector', function() {
            var row = $(this).closest('.custom-field-row');
            var existingOptions = row.find('input[name*="[options]"]');
            
            if ($(this).val() === 'select') {
                if (existingOptions.length === 0) {
                    var fieldName = $(this).attr('name').replace('[type]', '[options]');
                    row.find('> div').after('<input type="text" name="' + fieldName + '" placeholder="Options (comma-separated: Option1,Option2,Option3)" style="width:100%;margin:0;font-size:12px;color:#6b7280;">');
                }
            } else {
                existingOptions.remove();
            }
        });
    });
    </script>
    
    <?php b2b_adm_footer(); exit;
});

// ==========================================================================
// E. B2B ROLES PAGE
// ==========================================================================
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'b2b_roles') return;
    b2b_adm_guard();
    b2b_adm_header('B2B Roles');
    
    // Save Role
    if (isset($_POST['save_role'])) {
        $roles = get_option('b2b_roles', []);
        $slug = isset($_POST['edit_slug']) && !empty($_POST['edit_slug']) ? sanitize_key($_POST['edit_slug']) : sanitize_title($_POST['role_name']);
        $roles[$slug] = sanitize_text_field($_POST['role_name']);
        update_option('b2b_roles', $roles);
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0">Role saved successfully!</div>';
    }
    
    // Delete Role
    if (isset($_GET['del'])) {
        $roles = get_option('b2b_roles', []);
        unset($roles[sanitize_key($_GET['del'])]);
        update_option('b2b_roles', $roles);
        wp_redirect(home_url('/b2b-panel/b2b-module/roles'));
        exit;
    }
    
    $roles = get_option('b2b_roles', ['customer' => 'Customer', 'wholesaler' => 'Wholesaler', 'retailer' => 'Retailer']);
    
    // Edit mode
    $edit_role = null;
    $edit_slug = '';
    if(isset($_GET['edit'])) {
        $edit_slug = sanitize_key($_GET['edit']);
        $edit_role = $roles[$edit_slug] ?? null;
    }
    
    // Count users per role
    $role_counts = [];
    foreach($roles as $slug => $name) {
        $role_counts[$slug] = count(get_users(['meta_key' => 'b2b_role', 'meta_value' => $slug]));
    }
    ?>
    
    <div class="page-header">
        <h1 class="page-title">B2B Roles Management</h1>
        <a href="<?= home_url('/b2b-panel/b2b-module') ?>"><button class="secondary">Back to B2B Module</button></a>
    </div>
    
    <div style="display:grid;grid-template-columns:1fr 2fr;gap:25px;">
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;"><?= $edit_role ? 'Edit Role' : 'Add New Role' ?></h3>
            <form method="post">
                <?php if($edit_role): ?>
                <input type="hidden" name="edit_slug" value="<?= esc_attr($edit_slug) ?>">
                <?php endif; ?>
                
                <label>Role Name</label>
                <input type="text" name="role_name" placeholder="e.g. Premium Wholesaler" value="<?= esc_attr($edit_role ?? '') ?>" required>
                
                <button type="submit" name="save_role" style="width:100%;padding:12px;margin-top:10px;"><?= $edit_role ? 'Update Role' : 'Add Role' ?></button>
                <?php if($edit_role): ?>
                <a href="<?= home_url('/b2b-panel/b2b-module/roles') ?>" style="display:block;text-align:center;margin-top:10px;color:#6b7280;text-decoration:none;">Cancel</a>
                <?php endif; ?>
            </form>
            <p style="font-size:12px;color:#6b7280;margin-top:15px;line-height:1.5;">
                <i class="fa-solid fa-info-circle"></i> Roles help categorize B2B customers. Assign roles when editing customer profiles.
            </p>
        </div>
        
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;">Existing Roles</h3>
            <table>
                <thead>
                    <tr>
                        <th>Role Name</th>
                        <th>Slug</th>
                        <th>Users</th>
                        <th style="text-align:right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($roles)): ?>
                        <tr><td colspan="4" style="text-align:center;padding:20px;color:#999">No roles created yet.</td></tr>
                    <?php else: foreach ($roles as $slug => $name): ?>
                        <tr style="<?= $edit_slug == $slug ? 'background:#fef3c7;' : '' ?>">
                            <td><strong><?= esc_html($name) ?></strong></td>
                            <td><code style="background:#f3f4f6;padding:3px 8px;border-radius:4px;font-size:11px;"><?= esc_html($slug) ?></code></td>
                            <td><span style="background:#eff6ff;color:#1e40af;padding:3px 10px;border-radius:4px;font-size:12px;font-weight:600;"><?= $role_counts[$slug] ?> users</span></td>
                            <td style="text-align:right;">
                                <a href="?b2b_adm_page=b2b_roles&edit=<?= urlencode($slug) ?>">
                                    <button class="secondary" style="padding:6px 12px;font-size:12px;margin-right:5px;"><i class="fa-solid fa-pen"></i> Edit</button>
                                </a>
                                <a href="?b2b_adm_page=b2b_roles&del=<?= urlencode($slug) ?>" onclick="return confirm('Delete this role? Users with this role will not be affected.')">
                                    <button class="secondary" style="padding:6px 12px;background:#fef2f2;color:#ef4444;border-color:#fca5a5;font-size:12px;"><i class="fa-solid fa-trash"></i></button>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   12F. SALES AGENT SETTINGS PAGE (Admin V10 Panel)
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'settings_sales_agent') return;
    b2b_adm_guard();
    b2b_adm_header('Sales Agent Settings');
    
    // Register settings
    if (!did_action('admin_init')) {
        add_action('admin_init', function () {
            register_setting('sa_settings_group', 'sales_panel_title', [
                'type' => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'default' => 'Sales Agent Panel'
            ]);
            register_setting('sa_settings_group', 'sales_commission_rate', [
                'type' => 'number',
                'sanitize_callback' => 'floatval',
                'default' => 10
            ]);
            register_setting('sa_settings_group', 'sales_stale_days', [
                'type' => 'integer',
                'sanitize_callback' => 'intval',
                'default' => 30
            ]);
            register_setting('sa_settings_group', 'sales_merge_products', [
                'type' => 'boolean',
                'sanitize_callback' => 'rest_sanitize_boolean',
                'default' => false
            ]);
        });
    }
    
    // Save Settings
    if (isset($_POST['save_sales_agent_settings'])) {
        // Verify nonce for security
        if (!isset($_POST['sa_settings_nonce']) || !wp_verify_nonce($_POST['sa_settings_nonce'], 'sa_settings_save')) {
            echo '<div style="background:#fee2e2;color:#b91c1c;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #fecaca;"><i class="fa-solid fa-exclamation-triangle"></i> Security check failed!</div>';
        } else {
            update_option('sales_panel_title', sanitize_text_field($_POST['sales_panel_title']));
            update_option('sales_commission_rate', floatval($_POST['sales_commission_rate']));
            update_option('sales_stale_days', intval($_POST['sales_stale_days']));
            update_option('sales_merge_products', isset($_POST['sales_merge_products']) ? 1 : 0);
            update_option('sales_manager_can_order', isset($_POST['sales_manager_can_order']) ? 1 : 0);
            update_option('sales_view_all_customers', isset($_POST['sales_view_all_customers']) ? 1 : 0);
            
            echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0;"><i class="fa-solid fa-check-circle"></i> Settings saved successfully!</div>';
        }
    }
    
    // Get current settings
    $panel_title = get_option('sales_panel_title', 'Sales Agent Panel');
    $commission_rate = get_option('sales_commission_rate', 10);
    $stale_days = get_option('sales_stale_days', 30);
    $merge_products = get_option('sales_merge_products', 0);
    $manager_can_order = get_option('sales_manager_can_order', 0);
    $view_all_customers = get_option('sales_view_all_customers', 0);
    ?>
    
    <div class="page-header">
        <h1 class="page-title"><i class="fa-solid fa-user-tie"></i> Sales Agent Settings</h1>
        <p style="color:#6b7280;margin:5px 0 0 0;">Configure settings for Sales Agent system integration</p>
    </div>
    
    <form method="post" style="max-width:900px;">
        <?php wp_nonce_field('sa_settings_save', 'sa_settings_nonce'); ?>
        
        <!-- General Settings -->
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;display:flex;align-items:center;gap:10px;">
                <i class="fa-solid fa-cog" style="color:#8b5cf6;"></i> General Settings
            </h3>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;color:#374151;">
                    <i class="fa-solid fa-heading"></i> Panel Title
                </label>
                <input type="text" name="sales_panel_title" value="<?= esc_attr($panel_title) ?>" 
                       style="width:100%;max-width:400px;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;"
                       placeholder="Sales Agent Panel" required>
                <p style="color:#6b7280;font-size:12px;margin:5px 0 0 0;">Title displayed in Sales Agent dashboard</p>
            </div>
            
            <div style="margin-bottom:0;">
                <label style="display:block;font-weight:600;margin-bottom:5px;color:#374151;">
                    <i class="fa-solid fa-percent"></i> Default Commission Rate
                </label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <input type="number" name="sales_commission_rate" value="<?= esc_attr($commission_rate) ?>" 
                           style="width:120px;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;"
                           min="0" max="100" step="0.1" required>
                    <span style="color:#6b7280;font-weight:600;">%</span>
                </div>
                <p style="color:#6b7280;font-size:12px;margin:5px 0 0 0;">Commission percentage for sales agents</p>
            </div>
        </div>
        
        <!-- Advanced Options -->
        <div class="card">
            <h3 style="margin-top:0;border-bottom:1px solid #eee;padding-bottom:10px;display:flex;align-items:center;gap:10px;">
                <i class="fa-solid fa-sliders" style="color:#f59e0b;"></i> Advanced Options
            </h3>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:5px;color:#374151;">
                    <i class="fa-solid fa-calendar-xmark"></i> Stale Customer Threshold
                </label>
                <div style="display:flex;align-items:center;gap:10px;">
                    <input type="number" name="sales_stale_days" value="<?= esc_attr($stale_days) ?>" 
                           style="width:120px;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;"
                           min="1" max="365" required>
                    <span style="color:#6b7280;font-weight:600;">days</span>
                </div>
                <p style="color:#6b7280;font-size:12px;margin:5px 0 0 0;">Mark customer as inactive after this many days without activity</p>
            </div>
            
            <div style="margin-bottom:15px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="sales_merge_products" value="1" <?= checked($merge_products, 1, false) ?> 
                           style="width:18px;height:18px;cursor:pointer;">
                    <span style="font-weight:600;color:#374151;">
                        <i class="fa-solid fa-layer-group"></i> Merge Duplicate Products in Cart
                    </span>
                </label>
                <p style="color:#6b7280;font-size:12px;margin:5px 0 0 28px;">Automatically combine duplicate products into a single cart item</p>
            </div>
            
            <div style="margin-bottom:15px;padding-top:15px;border-top:1px solid #e5e7eb;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="sales_manager_can_order" value="1" <?= checked($manager_can_order, 1, false) ?> 
                           style="width:18px;height:18px;cursor:pointer;">
                    <span style="font-weight:600;color:#374151;">
                        <i class="fa-solid fa-user-shield"></i> Sales Managers Can Create Orders for Agent Customers
                    </span>
                </label>
                <p style="color:#6b7280;font-size:12px;margin:5px 0 0 28px;">Allow sales managers to place orders on behalf of customers assigned to sales agents</p>
            </div>
            
            <div style="margin-bottom:0;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="sales_view_all_customers" value="1" <?= checked($view_all_customers, 1, false) ?> 
                           style="width:18px;height:18px;cursor:pointer;">
                    <span style="font-weight:600;color:#374151;">
                        <i class="fa-solid fa-users-viewfinder"></i> View All Customers (Customer Role)
                    </span>
                </label>
                <p style="color:#6b7280;font-size:12px;margin:5px 0 0 28px;">Sales agents and managers can view all customers with "customer" role, not just assigned ones</p>
            </div>
        </div>
        
        <!-- Integration Info -->
        <div class="card" style="background:linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);border:1px solid #fbbf24;">
            <h3 style="margin:0 0 15px 0;color:#92400e;display:flex;align-items:center;gap:10px;">
                <i class="fa-solid fa-info-circle"></i> External Module Integration
            </h3>
            <p style="color:#92400e;font-size:13px;line-height:1.6;margin-bottom:15px;">
                Settings are stored in <code style="background:rgba(146,64,14,0.1);padding:2px 6px;border-radius:4px;font-family:monospace;">wp_options</code> table and can be accessed from your Sales Agent module:
            </p>
            <pre style="background:rgba(146,64,14,0.1);padding:15px;border-radius:8px;overflow-x:auto;font-size:12px;color:#78350f;"><code>$title = get_option('sales_panel_title', 'Sales Agent Panel');
$rate = get_option('sales_commission_rate', 10);
$days = get_option('sales_stale_days', 30);
$merge = get_option('sales_merge_products', 0);</code></pre>
        </div>
        
        <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit" name="save_sales_agent_settings" style="background:#8b5cf6;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;transition:0.2s;display:flex;align-items:center;gap:8px;">
                <i class="fa-solid fa-save"></i> Save Settings
            </button>
            <a href="<?= home_url('/b2b-panel') ?>" style="background:#6b7280;color:#fff;padding:12px 24px;border:none;border-radius:8px;font-weight:600;cursor:pointer;transition:0.2s;display:flex;align-items:center;gap:8px;text-decoration:none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </form>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   13. B2B PRO ADMIN PAGES (WordPress Admin Panel - Legacy)
===================================================== */

// ==========================================================================
// A. BAŞVURULAR SAYFASI (Approvals)
// ==========================================================================
function b2b_page_approvals() {
    if (isset($_POST['approve_user'])) {
        $uid = intval($_POST['uid']);
        update_user_meta($uid, 'b2b_status', 'approved');
        if (!empty($_POST['grp'])) {
            update_user_meta($uid, 'b2b_group_slug', sanitize_text_field($_POST['grp']));
        }
        echo '<div class="notice notice-success"><p>Kullanıcı onaylandı!</p></div>';
    }
    
    $users = get_users(['meta_key' => 'b2b_status', 'meta_value' => 'pending']);
    $groups = b2b_get_groups();
    ?>
    <div class="wrap">
        <h1>B2B Başvurular</h1>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>Firma / İsim</th>
                    <th>E-posta</th>
                    <th>Telefon</th>
                    <th>Grup Ata</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr><td colspan="5">Bekleyen başvuru yok.</td></tr>
                <?php else: foreach ($users as $u): ?>
                    <tr>
                        <td>
                            <strong><?= esc_html(get_user_meta($u->ID, 'billing_company', true) ?: $u->display_name) ?></strong><br>
                            <small><?= esc_html(get_user_meta($u->ID, 'billing_city', true)) ?></small>
                        </td>
                        <td><?= esc_html($u->user_email) ?></td>
                        <td><?= esc_html(get_user_meta($u->ID, 'billing_phone', true)) ?></td>
                        <td>
                            <form method="post" style="display:inline-flex;gap:5px;">
                                <input type="hidden" name="uid" value="<?= $u->ID ?>">
                                <select name="grp">
                                    <option value="">-- Standart --</option>
                                    <?php foreach ($groups as $k => $v): ?>
                                        <option value="<?= esc_attr($k) ?>"><?= esc_html($v['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" name="approve_user" class="button button-primary">Onayla</button>
                            </form>
                        </td>
                        <td><a href="<?= admin_url('user-edit.php?user_id=' . $u->ID) ?>" class="button">Düzenle</a></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ==========================================================================
// B. GRUPLAR & ÜYELER SAYFASI
// ==========================================================================
function b2b_page_group_list() {
    // Save Group
    if (isset($_POST['save_grp'])) {
        $groups = b2b_get_groups();
        $slug = sanitize_title($_POST['name']);
        $groups[$slug] = [
            'name' => sanitize_text_field($_POST['name']),
            'discount' => floatval($_POST['discount']),
            'min_order' => floatval($_POST['min_order'])
        ];
        update_option('b2b_dynamic_groups', $groups);
        echo '<div class="notice notice-success"><p>Grup kaydedildi!</p></div>';
    }
    
    // Delete Group
    if (isset($_GET['del'])) {
        $groups = b2b_get_groups();
        unset($groups[sanitize_key($_GET['del'])]);
        update_option('b2b_dynamic_groups', $groups);
        wp_redirect(admin_url('admin.php?page=b2b-groups-list'));
        exit;
    }
    
    $groups = b2b_get_groups();
    ?>
    <div class="wrap">
        <h1>Gruplar & Üyeler</h1>
        <div style="display:grid;grid-template-columns:1fr 2fr;gap:20px;">
            <div class="card">
                <h2>Yeni Grup Ekle</h2>
                <form method="post">
                    <table class="form-table">
                        <tr>
                            <th>Grup Adı</th>
                            <td><input type="text" name="name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th>İndirim (%)</th>
                            <td><input type="number" step="0.01" name="discount" value="0" class="small-text"></td>
                        </tr>
                        <tr>
                            <th>Min. Sipariş</th>
                            <td><input type="number" name="min_order" value="0" class="regular-text"></td>
                        </tr>
                    </table>
                    <p><button type="submit" name="save_grp" class="button button-primary">Kaydet</button></p>
                </form>
            </div>
            <div class="card">
                <h2>Mevcut Gruplar</h2>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Grup</th>
                            <th>İndirim</th>
                            <th>Min. Sipariş</th>
                            <th>Üye Sayısı</th>
                            <th>İşlem</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($groups as $slug => $data): 
                            $count = count(get_users(['meta_key' => 'b2b_group_slug', 'meta_value' => $slug]));
                        ?>
                            <tr>
                                <td><strong><?= esc_html($data['name']) ?></strong></td>
                                <td>%<?= $data['discount'] ?></td>
                                <td><?= wc_price($data['min_order']) ?></td>
                                <td><?= $count ?></td>
                                <td><a href="?page=b2b-groups-list&del=<?= urlencode($slug) ?>" onclick="return confirm('Silmek istediğinize emin misiniz?')" class="button">Sil</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}

// ==========================================================================
// C. GENEL AYARLAR SAYFASI (Ödeme İzinleri Matrisi Dahil)
// ==========================================================================
function b2b_page_settings() {
    // Save Settings
    if (isset($_POST['save_settings'])) {
        update_option('b2b_hide_prices_guest', isset($_POST['hide_prices_guest']) ? 1 : 0);
        
        // Ödeme izinleri matrisi kaydet
        if (isset($_POST['group_payments'])) {
            $group_rules = array();
            foreach ($_POST['group_payments'] as $group_slug => $payments) {
                $group_rules[$group_slug] = array_map('sanitize_text_field', $payments);
            }
            update_option('b2b_group_payment_rules', $group_rules);
        }
        
        echo '<div class="notice notice-success"><p>Ayarlar kaydedildi!</p></div>';
    }
    
    $hide_prices = b2b_is_price_hidden_for_guests();
    $groups = b2b_get_groups();
    $group_payment_rules = b2b_get_group_payment_rules();
    
    // Tüm ödeme yöntemlerini al
    $gateways = WC()->payment_gateways->payment_gateways();
    ?>
    <div class="wrap">
        <h1>B2B Genel Ayarlar</h1>
        <form method="post">
            <h2>Fiyat Görünürlüğü</h2>
            <table class="form-table">
                <tr>
                    <th>Misafirler için fiyat gizle</th>
                    <td>
                        <label>
                            <input type="checkbox" name="hide_prices_guest" value="1" <?= checked($hide_prices, 1, false) ?>>
                            Giriş yapmayan kullanıcılar fiyatları göremesin
                        </label>
                    </td>
                </tr>
            </table>
            
            <h2>Ödeme İzinleri Matrisi (Grup Bazlı)</h2>
            <p>Her grup için hangi ödeme yöntemlerine izin verildiğini seçin. Boş bırakırsanız tüm yöntemler açık olur.</p>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width:200px;">Grup</th>
                        <?php foreach ($gateways as $gateway_id => $gateway): ?>
                            <th style="text-align:center;"><?= esc_html($gateway->get_title()) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($groups as $slug => $group_data): ?>
                        <tr>
                            <td><strong><?= esc_html($group_data['name']) ?></strong></td>
                            <?php foreach ($gateways as $gateway_id => $gateway): 
                                $is_allowed = isset($group_payment_rules[$slug]) && in_array($gateway_id, $group_payment_rules[$slug]);
                            ?>
                                <td style="text-align:center;">
                                    <input type="checkbox" name="group_payments[<?= esc_attr($slug) ?>][]" value="<?= esc_attr($gateway_id) ?>" <?= checked($is_allowed, true, false) ?>>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($groups)): ?>
                        <tr><td colspan="<?= count($gateways) + 1 ?>">Henüz grup oluşturulmamış. Önce grup ekleyin.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <p class="description">Not: Kullanıcı bazlı özel izinler grup izinlerini geçersiz kılar. Kullanıcı düzenleme sayfasından atayabilirsiniz.</p>
            
            <p><button type="submit" name="save_settings" class="button button-primary button-large">Ayarları Kaydet</button></p>
        </form>
    </div>
    <?php
}

// ==========================================================================
// D. FORM DÜZENLEYİCİ SAYFASI
// ==========================================================================
function b2b_page_form_editor() {
    // Save Custom Fields
    if (isset($_POST['save_fields'])) {
        $fields = array();
        if (isset($_POST['fields'])) {
            foreach ($_POST['fields'] as $key => $field_data) {
                $fields[$key] = [
                    'label' => sanitize_text_field($field_data['label']),
                    'type' => sanitize_text_field($field_data['type']),
                    'required' => isset($field_data['required']) ? 1 : 0
                ];
            }
        }
        update_option('b2b_custom_fields_def', $fields);
        
        // Standart alan ayarları
        if (isset($_POST['standard_fields'])) {
            $std_fields = array();
            foreach ($_POST['standard_fields'] as $field_key => $field_config) {
                $std_fields[$field_key] = [
                    'enabled' => isset($field_config['enabled']) ? 1 : 0,
                    'required' => isset($field_config['required']) ? 1 : 0
                ];
            }
            update_option('b2b_standard_fields_config', $std_fields);
        }
        
        echo '<div class="notice notice-success"><p>Form ayarları kaydedildi!</p></div>';
    }
    
    $custom_fields = b2b_get_custom_fields();
    $standard_fields_config = b2b_get_standard_fields_config();
    
    // Standart WooCommerce alanları
    $standard_fields = [
        'billing_company' => 'Firma Adı',
        'billing_phone' => 'Telefon',
        'billing_city' => 'Şehir',
        'billing_postcode' => 'Posta Kodu',
        'billing_address_1' => 'Adres',
        'billing_state' => 'İl/Eyalet',
        'billing_country' => 'Ülke'
    ];
    ?>
    <div class="wrap">
        <h1>B2B Form Düzenleyici</h1>
        <form method="post">
            <h2>Standart Alanlar (WooCommerce)</h2>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th>Alan</th>
                        <th>Aktif</th>
                        <th>Zorunlu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($standard_fields as $field_key => $field_label): 
                        $is_enabled = isset($standard_fields_config[$field_key]['enabled']) ? $standard_fields_config[$field_key]['enabled'] : 1;
                        $is_required = isset($standard_fields_config[$field_key]['required']) ? $standard_fields_config[$field_key]['required'] : 0;
                    ?>
                        <tr>
                            <td><?= esc_html($field_label) ?></td>
                            <td>
                                <input type="checkbox" name="standard_fields[<?= esc_attr($field_key) ?>][enabled]" value="1" <?= checked($is_enabled, 1, false) ?>>
                            </td>
                            <td>
                                <input type="checkbox" name="standard_fields[<?= esc_attr($field_key) ?>][required]" value="1" <?= checked($is_required, 1, false) ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <h2>Özel Alanlar</h2>
            <div id="custom-fields-container">
                <?php if (!empty($custom_fields)): foreach ($custom_fields as $key => $field): ?>
                    <div class="custom-field-row" style="margin-bottom:15px;padding:10px;border:1px solid #ddd;background:#f9f9f9;">
                        <input type="text" name="fields[<?= esc_attr($key) ?>][label]" value="<?= esc_attr($field['label']) ?>" placeholder="Alan Etiketi" class="regular-text">
                        <select name="fields[<?= esc_attr($key) ?>][type]">
                            <option value="text" <?= selected($field['type'], 'text', false) ?>>Metin</option>
                            <option value="textarea" <?= selected($field['type'], 'textarea', false) ?>>Çok Satırlı</option>
                            <option value="select" <?= selected($field['type'], 'select', false) ?>>Seçim</option>
                        </select>
                        <label><input type="checkbox" name="fields[<?= esc_attr($key) ?>][required]" value="1" <?= checked($field['required'], 1, false) ?>> Zorunlu</label>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <p><button type="button" id="add-custom-field" class="button">+ Yeni Alan Ekle</button></p>
            
            <p><button type="submit" name="save_fields" class="button button-primary button-large">Form Ayarlarını Kaydet</button></p>
        </form>
        
        <script>
        jQuery(document).ready(function($) {
            $('#add-custom-field').click(function() {
                var key = 'field_' + Date.now();
                var html = '<div class="custom-field-row" style="margin-bottom:15px;padding:10px;border:1px solid #ddd;background:#f9f9f9;">' +
                    '<input type="text" name="fields[' + key + '][label]" placeholder="Alan Etiketi" class="regular-text">' +
                    '<select name="fields[' + key + '][type]">' +
                    '<option value="text">Metin</option>' +
                    '<option value="textarea">Çok Satırlı</option>' +
                    '<option value="select">Seçim</option>' +
                    '</select>' +
                    '<label><input type="checkbox" name="fields[' + key + '][required]" value="1"> Zorunlu</label>' +
                    '</div>';
                $('#custom-fields-container').append(html);
            });
        });
        </script>
    </div>
    <?php
}

// ==========================================================================
// E. Kullanıcı Profil Sayfasına Ödeme İzinleri Alanı Ekle
// ==========================================================================
add_action('show_user_profile', 'b2b_user_payment_permissions_field');
add_action('edit_user_profile', 'b2b_user_payment_permissions_field');

function b2b_user_payment_permissions_field($user) {
    if (!current_user_can('manage_options')) return;
    
    $allowed_payments = get_user_meta($user->ID, 'b2b_allowed_payments', true);
    if (!is_array($allowed_payments)) $allowed_payments = array();
    
    $gateways = WC()->payment_gateways->payment_gateways();
    ?>
    <h2>B2B Ödeme İzinleri (Kullanıcı Özel)</h2>
    <table class="form-table">
        <tr>
            <th>İzin Verilen Ödeme Yöntemleri</th>
            <td>
                <p class="description">Bu kullanıcı için özel ödeme yöntemi izinleri. Boş bırakırsanız grup ayarları geçerli olur.</p>
                <?php foreach ($gateways as $gateway_id => $gateway): ?>
                    <label style="display:block;margin:5px 0;">
                        <input type="checkbox" name="b2b_allowed_payments[]" value="<?= esc_attr($gateway_id) ?>" <?= checked(in_array($gateway_id, $allowed_payments), true, false) ?>>
                        <?= esc_html($gateway->get_title()) ?>
                    </label>
                <?php endforeach; ?>
            </td>
        </tr>
    </table>
    <?php
}

add_action('personal_options_update', 'b2b_save_user_payment_permissions');
add_action('edit_user_profile_update', 'b2b_save_user_payment_permissions');

function b2b_save_user_payment_permissions($user_id) {
    if (!current_user_can('manage_options')) return;
    
    $payments = isset($_POST['b2b_allowed_payments']) ? array_map('sanitize_text_field', $_POST['b2b_allowed_payments']) : array();
    update_user_meta($user_id, 'b2b_allowed_payments', $payments);
}

   /* =====================================================
  /* =====================================================
   X. FORCE ERP MODE (TAM KAPANMA & GLOBAL ROUTER)
   Tüm panelleri tanır, harici linkleri Master sayfasına atar.
===================================================== */
add_action('template_redirect', function() {
    
    // 1. Teknik İstekleri Yoksay (AJAX, Cron, API, Robots.txt vb.)
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    if (defined('DOING_CRON') && DOING_CRON) return;
    if (strpos($_SERVER['REQUEST_URI'], '/wp-json/') !== false) return;
    if (is_admin()) return; // /wp-admin klasörüne erişimi engelleme (Adminler için)

    // 2. Hangi Paneldeyiz? (Query Var Kontrolü)
    // Bu değişkenler önceki snippetlarda tanımladığımız URL yapılarıdır.
    $is_b2b       = get_query_var('b2b_adm_page');      // Admin Paneli
    $is_sales     = get_query_var('sales_panel') || get_query_var('sales_login'); // Sales Agent Paneli
    $is_warehouse = get_query_var('wh_panel') || get_query_var('wh_login');       // Warehouse Paneli
    $is_master    = get_query_var('master_portal');     // Ana Giriş Kapısı

    // 3. Geçerli bir ERP sayfasında mıyız?
    if ($is_b2b || $is_sales || $is_warehouse || $is_master) {
        // Evet, geçerli bir panel sayfasındayız. 
        // Müdahale etme, bırak ilgili panelin kendi güvenlik önlemi (Guard fonksiyonu) devreye girsin.
        return;
    }

    // 4. Panel Dışı Bir Yer (Örn: Anasayfa, Merhaba Dünya yazısıA, 404)
    // Kullanıcıyı Master Portal'a yönlendir.
   // wp_redirect(home_url('/b2b-master'));
    //exit;
});

/* =====================================================
   SALES AGENT SETTINGS INTEGRATION
===================================================== */

// Register Sales Agent settings
function b2b_register_sales_agent_settings() {
    register_setting('sa_settings_group', 'sales_panel_title', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'Sales Agent Panel'
    ]);
    register_setting('sa_settings_group', 'sales_commission_rate', [
        'type' => 'number',
        'sanitize_callback' => 'floatval',
        'default' => 10
    ]);
    register_setting('sa_settings_group', 'sales_stale_days', [
        'type' => 'integer',
        'sanitize_callback' => 'intval',
        'default' => 30
    ]);
    register_setting('sa_settings_group', 'sales_merge_products', [
        'type' => 'boolean',
        'sanitize_callback' => 'rest_sanitize_boolean',
        'default' => false
    ]);
}
add_action('admin_init', 'b2b_register_sales_agent_settings');

// Render Sales Agent settings page
function b2b_page_sales_agent_settings() {
    b2b_adm_guard();
    
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    // Save settings if form submitted
    if (isset($_POST['sa_save_settings']) && check_admin_referer('sa_settings_save', 'sa_settings_nonce')) {
        update_option('sales_panel_title', sanitize_text_field($_POST['sales_panel_title']));
        update_option('sales_commission_rate', floatval($_POST['sales_commission_rate']));
        update_option('sales_stale_days', intval($_POST['sales_stale_days']));
        update_option('sales_merge_products', isset($_POST['sales_merge_products']) ? 1 : 0);
        
        echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin:20px 0;border-radius:8px;border:1px solid #a7f3d0">
                <i class="fa-solid fa-check-circle"></i> Settings saved successfully!
              </div>';
    }
    
    // Get current values
    $panel_title = get_option('sales_panel_title', 'Sales Agent Panel');
    $commission_rate = get_option('sales_commission_rate', 10);
    $stale_days = get_option('sales_stale_days', 30);
    $merge_products = get_option('sales_merge_products', 0);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Sales Agent Settings</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 900px;
                margin: 0 auto;
                background: white;
                border-radius: 16px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                overflow: hidden;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                text-align: center;
            }
            .header h1 {
                font-size: 28px;
                font-weight: 700;
                margin-bottom: 8px;
            }
            .header p {
                opacity: 0.9;
                font-size: 14px;
            }
            .content {
                padding: 40px;
            }
            .form-group {
                margin-bottom: 25px;
            }
            .form-group label {
                display: block;
                font-weight: 600;
                margin-bottom: 8px;
                color: #374151;
                font-size: 14px;
            }
            .form-group input[type="text"],
            .form-group input[type="number"] {
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.3s;
            }
            .form-group input:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .form-group .helper-text {
                margin-top: 6px;
                font-size: 12px;
                color: #6b7280;
            }
            .checkbox-group {
                display: flex;
                align-items: center;
                gap: 10px;
            }
            .checkbox-group input[type="checkbox"] {
                width: 20px;
                height: 20px;
                cursor: pointer;
            }
            .checkbox-group label {
                margin: 0;
                cursor: pointer;
            }
            .btn-save {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 14px 32px;
                border: none;
                border-radius: 8px;
                font-size: 16px;
                font-weight: 600;
                cursor: pointer;
                transition: transform 0.2s, box-shadow 0.2s;
                display: inline-flex;
                align-items: center;
                gap: 8px;
            }
            .btn-save:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            }
            .back-link {
                display: inline-block;
                margin-bottom: 20px;
                color: white;
                text-decoration: none;
                font-weight: 600;
                transition: opacity 0.2s;
            }
            .back-link:hover {
                opacity: 0.8;
            }
            .settings-card {
                background: #f9fafb;
                border-radius: 12px;
                padding: 20px;
                margin-bottom: 20px;
            }
            .settings-card h3 {
                font-size: 16px;
                font-weight: 600;
                color: #111827;
                margin-bottom: 15px;
                display: flex;
                align-items: center;
                gap: 8px;
            }
        </style>
    </head>
    <body>
        <a href="<?php echo admin_url('admin.php?page=b2b-panel'); ?>" class="back-link">
            <i class="fa-solid fa-arrow-left"></i> Back to B2B Panel
        </a>
        
        <div class="container">
            <div class="header">
                <h1><i class="fa-solid fa-user-tie"></i> Sales Agent Settings</h1>
                <p>Configure settings for the Sales Agent system</p>
            </div>
            
            <div class="content">
                <form method="post" action="">
                    <?php wp_nonce_field('sa_settings_save', 'sa_settings_nonce'); ?>
                    
                    <div class="settings-card">
                        <h3><i class="fa-solid fa-sliders"></i> General Settings</h3>
                        
                        <div class="form-group">
                            <label for="sales_panel_title">
                                <i class="fa-solid fa-heading"></i> Panel Title
                            </label>
                            <input type="text" 
                                   id="sales_panel_title" 
                                   name="sales_panel_title" 
                                   value="<?php echo esc_attr($panel_title); ?>" 
                                   placeholder="Sales Agent Panel">
                            <div class="helper-text">The title displayed on the sales agent dashboard</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="sales_commission_rate">
                                <i class="fa-solid fa-percent"></i> Commission Rate (%)
                            </label>
                            <input type="number" 
                                   id="sales_commission_rate" 
                                   name="sales_commission_rate" 
                                   value="<?php echo esc_attr($commission_rate); ?>" 
                                   step="0.01" 
                                   min="0" 
                                   max="100"
                                   placeholder="10">
                            <div class="helper-text">Default commission percentage for sales agents</div>
                        </div>
                    </div>
                    
                    <div class="settings-card">
                        <h3><i class="fa-solid fa-cog"></i> Advanced Options</h3>
                        
                        <div class="form-group">
                            <label for="sales_stale_days">
                                <i class="fa-solid fa-calendar-days"></i> Stale Customer Days
                            </label>
                            <input type="number" 
                                   id="sales_stale_days" 
                                   name="sales_stale_days" 
                                   value="<?php echo esc_attr($stale_days); ?>" 
                                   min="1" 
                                   placeholder="30">
                            <div class="helper-text">Number of days before a customer is marked as inactive</div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" 
                                       id="sales_merge_products" 
                                       name="sales_merge_products" 
                                       value="1" 
                                       <?php checked($merge_products, 1); ?>>
                                <label for="sales_merge_products">
                                    <i class="fa-solid fa-layer-group"></i> Merge Products in Orders
                                </label>
                            </div>
                            <div class="helper-text" style="margin-left: 30px;">Automatically combine duplicate products in cart</div>
                        </div>
                    </div>
                    
                    <button type="submit" name="sa_save_settings" class="btn-save">
                        <i class="fa-solid fa-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* =====================================================
   PAGE: SETTINGS - GENERAL
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'settings_general') return;
    b2b_adm_guard();
    
    // Handle settings save
    $message = '';
    if(isset($_POST['save_settings'])) {
        update_option('b2b_panel_name', sanitize_text_field($_POST['panel_name']));
        update_option('b2b_items_per_page', intval($_POST['items_per_page']));
        update_option('b2b_enable_caching', isset($_POST['enable_caching']) ? 1 : 0);
        $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> Settings saved.</div>';
    }
    
    $panel_name = get_option('b2b_panel_name', 'B2B Admin Panel');
    $items_per_page = get_option('b2b_items_per_page', 20);
    $enable_caching = get_option('b2b_enable_caching', 0);
    
    b2b_adm_header('General Settings');
    ?>
    <div class="page-header"><h1 class="page-title">General Settings</h1></div>
    
    <?= $message ?>
    
    <div class="card">
        <form method="post" style="max-width:600px;">
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Panel Name</label>
                <input type="text" name="panel_name" value="<?= esc_attr($panel_name) ?>" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Default Items Per Page</label>
                <select name="items_per_page" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                    <option value="10" <?= selected($items_per_page, 10) ?>>10</option>
                    <option value="20" <?= selected($items_per_page, 20) ?>>20</option>
                    <option value="50" <?= selected($items_per_page, 50) ?>>50</option>
                    <option value="100" <?= selected($items_per_page, 100) ?>>100</option>
                </select>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="enable_caching" value="1" <?= checked($enable_caching, 1) ?>>
                    <span style="font-weight:600;">Enable Query Caching</span>
                </label>
                <small style="color:#6b7280;margin-left:30px;">Cache database queries for better performance (recommended)</small>
            </div>
            
            <div style="padding:15px;background:#f0f9ff;border:1px solid #bfdbfe;border-radius:8px;margin-bottom:20px;">
                <h4 style="margin-top:0;color:#1e40af;"><i class="fa-solid fa-lightbulb"></i> Performance Tips</h4>
                <ul style="color:#1e40af;margin:0;">
                    <li>Enable caching for faster page loads</li>
                    <li>Reduce items per page if you have many products</li>
                    <li>Use filters to narrow down results</li>
                    <li>Regularly clean up old data</li>
                </ul>
            </div>
            
            <button type="submit" name="save_settings" style="background:#10b981;color:white;padding:10px 20px;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
                <i class="fa-solid fa-save"></i> Save Settings
            </button>
        </form>
    </div>
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   PAGE: SETTINGS - TAX EXEMPTION
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'settings_tax') return;
    b2b_adm_guard();
    
    // Handle settings save
    $message = '';
    if(isset($_POST['save_tax_settings'])) {
        update_option('b2b_tax_auto_remove', isset($_POST['tax_auto_remove']) ? 1 : 0);
        update_option('b2b_tax_enable_text', isset($_POST['enable_text_field']) ? 1 : 0);
        update_option('b2b_tax_text_required', isset($_POST['text_required']) ? 1 : 0);
        update_option('b2b_tax_text_label', sanitize_text_field($_POST['text_label']));
        update_option('b2b_tax_enable_textarea', isset($_POST['enable_textarea']) ? 1 : 0);
        update_option('b2b_tax_textarea_required', isset($_POST['textarea_required']) ? 1 : 0);
        update_option('b2b_tax_textarea_label', sanitize_text_field($_POST['textarea_label']));
        update_option('b2b_tax_enable_file', isset($_POST['enable_file']) ? 1 : 0);
        update_option('b2b_tax_file_required', isset($_POST['file_required']) ? 1 : 0);
        update_option('b2b_tax_file_label', sanitize_text_field($_POST['file_label']));
        update_option('b2b_tax_allowed_types', sanitize_text_field($_POST['allowed_types']));
        
        $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> Tax exemption settings saved.</div>';
    }
    
    // Handle exemption requests
    if(isset($_POST['approve_request'])) {
        $user_id = intval($_POST['user_id']);
        update_user_meta($user_id, 'b2b_tax_exempt', 1);
        update_user_meta($user_id, 'b2b_tax_approved_date', current_time('mysql'));
        $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;">Tax exemption approved for user.</div>';
    }
    
    if(isset($_POST['reject_request'])) {
        $user_id = intval($_POST['user_id']);
        update_user_meta($user_id, 'b2b_tax_exempt', 0);
        $message = '<div style="padding:15px;background:#fee2e2;color:#991b1b;border-radius:8px;margin-bottom:20px;">Tax exemption rejected for user.</div>';
    }
    
    // Get settings
    $tax_auto = get_option('b2b_tax_auto_remove', 0);
    $enable_text = get_option('b2b_tax_enable_text', 1);
    $text_required = get_option('b2b_tax_text_required', 0);
    $text_label = get_option('b2b_tax_text_label', 'Tax ID');
    $enable_textarea = get_option('b2b_tax_enable_textarea', 0);
    $textarea_required = get_option('b2b_tax_textarea_required', 0);
    $textarea_label = get_option('b2b_tax_textarea_label', 'Additional Information');
    $enable_file = get_option('b2b_tax_enable_file', 1);
    $file_required = get_option('b2b_tax_file_required', 0);
    $file_label = get_option('b2b_tax_file_label', 'Tax Certificate');
    $allowed_types = get_option('b2b_tax_allowed_types', 'pdf,jpg,jpeg,png');
    
    // Get pending requests
    $pending_users = get_users([
        'meta_key' => 'b2b_tax_request',
        'meta_value' => '1',
        'meta_compare' => '='
    ]);
    
    b2b_adm_header('Tax Exemption Settings');
    ?>
    <div class="page-header"><h1 class="page-title">Tax Exemption</h1></div>
    
    <?= $message ?>
    
    <style>
        .tax-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; }
        @media(max-width: 900px) { .tax-grid { grid-template-columns: 1fr; } }
        .field-card { padding: 15px; background: #f9fafb; border: 2px solid #e5e7eb; border-radius: 8px; transition: border-color 0.2s; }
        .field-card:hover { border-color: #d1d5db; }
        .field-card.active { border-color: #3b82f6; background: #eff6ff; }
    </style>
    
    <!-- Settings Form -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-top:0;">General Settings</h3>
        <form method="post">
            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="tax_auto_remove" value="1" <?= checked($tax_auto, 1) ?> style="width:18px;height:18px;">
                    <span style="font-weight:600;">Remove Tax Automatically</span>
                </label>
                <small style="color:#6b7280;margin-left:30px;">Disable tax for approved users automatically</small>
            </div>
            
            <hr style="margin:20px 0;border:none;border-top:1px solid #e5e7eb;">
            
            <h4 style="margin-bottom:10px;">Tax Exemption Form Fields</h4>
            <p style="color:#6b7280;margin-bottom:20px;">Configure which fields appear in the customer tax exemption request form.</p>
            
            <!-- 2 Column Grid for Fields -->
            <div class="tax-grid">
                <!-- Text Field -->
                <div class="field-card">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:15px;">
                        <input type="checkbox" name="enable_text_field" value="1" <?= checked($enable_text, 1) ?> style="width:18px;height:18px;">
                        <span style="font-weight:600;font-size:15px;"><i class="fa-solid fa-keyboard"></i> Text Field</span>
                    </label>
                    <div style="margin-left:0;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
                            <input type="checkbox" name="text_required" value="1" <?= checked($text_required, 1) ?> style="width:16px;height:16px;">
                            <span style="font-size:13px;">Required</span>
                        </label>
                        <label style="display:block;margin-bottom:5px;font-size:13px;font-weight:600;color:#374151;">Field Label</label>
                        <input type="text" name="text_label" value="<?= esc_attr($text_label) ?>" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                    </div>
                </div>
                
                <!-- Textarea Field -->
                <div class="field-card">
                    <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:15px;">
                        <input type="checkbox" name="enable_textarea" value="1" <?= checked($enable_textarea, 1) ?> style="width:18px;height:18px;">
                        <span style="font-weight:600;font-size:15px;"><i class="fa-solid fa-align-left"></i> Textarea Field</span>
                    </label>
                    <div style="margin-left:0;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
                            <input type="checkbox" name="textarea_required" value="1" <?= checked($textarea_required, 1) ?> style="width:16px;height:16px;">
                            <span style="font-size:13px;">Required</span>
                        </label>
                        <label style="display:block;margin-bottom:5px;font-size:13px;font-weight:600;color:#374151;">Field Label</label>
                        <input type="text" name="textarea_label" value="<?= esc_attr($textarea_label) ?>" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                    </div>
                </div>
            </div>
            
            <!-- File Upload Field (Full Width) -->
            <div class="field-card" style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;margin-bottom:15px;">
                    <input type="checkbox" name="enable_file" value="1" <?= checked($enable_file, 1) ?> style="width:18px;height:18px;">
                    <span style="font-weight:600;font-size:15px;"><i class="fa-solid fa-file-arrow-up"></i> File Upload Field</span>
                </label>
                <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:15px;">
                    <div>
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:12px;">
                            <input type="checkbox" name="file_required" value="1" <?= checked($file_required, 1) ?> style="width:16px;height:16px;">
                            <span style="font-size:13px;">Required</span>
                        </label>
                        <label style="display:block;margin-bottom:5px;font-size:13px;font-weight:600;color:#374151;">Field Label</label>
                        <input type="text" name="file_label" value="<?= esc_attr($file_label) ?>" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;font-weight:600;color:#374151;margin-top:32px;">Allowed File Types</label>
                        <input type="text" name="allowed_types" value="<?= esc_attr($allowed_types) ?>" placeholder="pdf,jpg,jpeg,png" style="width:100%;padding:8px;border:1px solid #d1d5db;border-radius:6px;font-size:13px;">
                        <small style="display:block;color:#6b7280;margin-top:5px;font-size:11px;">Comma-separated file extensions</small>
                    </div>
                </div>
            </div>
            
            <button type="submit" name="save_tax_settings" style="background:#10b981;color:white;padding:12px 24px;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;">
                <i class="fa-solid fa-save"></i> Save Settings
            </button>
        </form>
    </div>
    
    <!-- Pending Requests -->
    <?php if(!empty($pending_users)): ?>
    <div class="card">
        <h3 style="margin-top:0;">Pending Tax Exemption Requests</h3>
        <table style="width:100%;">
            <thead>
                <tr>
                    <th style="text-align:left;">Customer</th>
                    <th style="text-align:left;">Email</th>
                    <th style="text-align:left;">Request Date</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach($pending_users as $user): 
                $request_date = get_user_meta($user->ID, 'b2b_tax_request_date', true);
            ?>
                <tr>
                    <td><strong><?= esc_html($user->display_name) ?></strong></td>
                    <td><?= esc_html($user->user_email) ?></td>
                    <td><?= $request_date ? date('Y-m-d', strtotime($request_date)) : '-' ?></td>
                    <td style="text-align:right;">
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="user_id" value="<?= $user->ID ?>">
                            <button type="submit" name="approve_request" style="background:#10b981;color:white;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;margin-right:5px;font-size:12px;">
                                <i class="fa-solid fa-check"></i> Approve
                            </button>
                            <button type="submit" name="reject_request" style="background:#ef4444;color:white;padding:6px 12px;border:none;border-radius:6px;cursor:pointer;font-size:12px;">
                                <i class="fa-solid fa-times"></i> Reject
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   SHIPPING MODULE - NATIVE INTEGRATION
===================================================== */
// Shipping Page
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'settings_shipping') return;
    b2b_adm_guard();
    
    // Handle zone save/delete
    $message = '';
    if(isset($_POST['save_zone'])) {
        $zone_id = isset($_POST['zone_id']) && !empty($_POST['zone_id']) && $_POST['zone_id'] != 'new' ? intval($_POST['zone_id']) : 'new';
        
        $regions_input = isset($_POST['zone_regions'][0]) ? $_POST['zone_regions'][0] : '';
        $regions = array_map('trim', explode(',', $regions_input));
        
        // Process group permissions
        $group_permissions = [];
        if(isset($_POST['group_permissions']) && is_array($_POST['group_permissions'])) {
            foreach($_POST['group_permissions'] as $group_id => $group_data) {
                if(isset($group_data['allowed'])) {
                    $group_permissions[$group_id] = [
                        'allowed' => 1,
                        'flat_rate_cost' => isset($group_data['flat_rate_cost']) && $group_data['flat_rate_cost'] !== '' ? floatval($group_data['flat_rate_cost']) : null,
                        'free_shipping_min' => isset($group_data['free_shipping_min']) && $group_data['free_shipping_min'] !== '' ? floatval($group_data['free_shipping_min']) : null,
                        'hidden_methods' => $group_data['hidden_methods'] ?? []
                    ];
                }
            }
        }
        
        $zone_data = [
            'name' => sanitize_text_field($_POST['zone_name']),
            'regions' => array_filter($regions),
            'priority' => intval($_POST['zone_priority'] ?? 1),
            'methods' => [
                'flat_rate' => [
                    'enabled' => isset($_POST['flat_rate_enabled']) ? 1 : 0,
                    'cost' => floatval($_POST['flat_rate_cost'] ?? 0),
                    'title' => sanitize_text_field($_POST['flat_rate_title'] ?? 'Flat Rate')
                ],
                'free_shipping' => [
                    'enabled' => isset($_POST['free_shipping_enabled']) ? 1 : 0,
                    'min_amount' => floatval($_POST['free_shipping_min'] ?? 0),
                    'title' => sanitize_text_field($_POST['free_shipping_title'] ?? 'Free Shipping')
                ]
            ],
            'group_permissions' => $group_permissions
        ];
        
        $saved_id = b2b_save_shipping_zone($zone_id, $zone_data);
        if($saved_id) {
            $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> Shipping zone saved.</div>';
        } else {
            $message = '<div style="padding:15px;background:#fee2e2;color:#991b1b;border-radius:8px;margin-bottom:20px;"><strong>Error!</strong> Could not save shipping zone.</div>';
        }
        
        // Redirect to list after save
        if($zone_id === 'new') {
            wp_redirect(home_url('/b2b-panel/settings/shipping'));
            exit;
        }
    }
    
    if(isset($_GET['delete'])) {
        $zone_id = intval($_GET['delete']);
        if(b2b_delete_shipping_zone($zone_id)) {
            $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;">Shipping zone deleted.</div>';
        }
        wp_redirect(home_url('/b2b-panel/settings/shipping'));
        exit;
    }
    
    $zones = b2b_get_all_shipping_zones();
    $edit_zone = null;
    $edit_id = '';
    if(isset($_GET['edit'])) {
        $edit_id = $_GET['edit'];
        if($edit_id == 'new') {
            $edit_zone = [
                'name' => '', 
                'regions' => [], 
                'active' => 1, 
                'priority' => 1, 
                'methods' => [
                    'flat_rate' => ['enabled' => 1, 'cost' => 0, 'title' => 'Flat Rate'], 
                    'free_shipping' => ['enabled' => 0, 'min_amount' => 0, 'title' => 'Free Shipping']
                ],
                'group_permissions' => []
            ];
        } else {
            $edit_zone = $zones[intval($edit_id)] ?? null;
        }
    }
    
    b2b_adm_header('Shipping Settings');
    
    echo $message;
    ?>
    <div class="page-header"><h1 class="page-title">Shipping Zones</h1></div>
    
    <?php if($edit_zone): ?>
    <!-- Edit Zone Form -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-top:0;"><?= $edit_id == 'new' ? 'Add New Shipping Zone' : 'Edit Shipping Zone' ?></h3>
        <form method="POST">
            <input type="hidden" name="zone_id" value="<?= esc_attr($edit_id) ?>">
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Zone Name *</label>
                <input type="text" name="zone_name" value="<?= esc_attr($edit_zone['name']) ?>" required style="width:100%;max-width:400px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Regions (Countries)</label>
                <input type="text" name="zone_regions[]" value="<?= esc_attr(implode(', ', $edit_zone['regions'] ?? [])) ?>" placeholder="TR, US, GB" style="width:100%;max-width:400px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                <small>Comma-separated country codes</small>
            </div>
            
            
            <div style="margin-bottom:20px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Priority</label>
                <input type="number" name="zone_priority" value="<?= esc_attr($edit_zone['priority'] ?? 1) ?>" min="1" style="width:100px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
            </div>
            
            <h4>Shipping Methods</h4>
            
            <!-- Flat Rate -->
            <div style="margin-bottom:20px;padding:15px;background:#f9fafb;border-radius:8px;">
                <label style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <input type="checkbox" name="flat_rate_enabled" value="1" <?= checked($edit_zone['methods']['flat_rate']['enabled'] ?? 0, 1) ?>>
                    <span style="font-weight:600;">Flat Rate Shipping</span>
                </label>
                <div style="margin-left:30px;">
                    <div style="margin-bottom:10px;">
                        <label>Title</label>
                        <input type="text" name="flat_rate_title" value="<?= esc_attr($edit_zone['methods']['flat_rate']['title'] ?? 'Flat Rate') ?>" style="width:100%;max-width:300px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                    </div>
                    <div>
                        <label>Cost ($)</label>
                        <input type="number" name="flat_rate_cost" value="<?= esc_attr($edit_zone['methods']['flat_rate']['cost'] ?? 0) ?>" step="0.01" min="0" style="width:150px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                    </div>
                </div>
            </div>
            
            <!-- Free Shipping -->
            <div style="margin-bottom:20px;padding:15px;background:#f9fafb;border-radius:8px;">
                <label style="display:flex;align-items:center;gap:10px;margin-bottom:10px;">
                    <input type="checkbox" name="free_shipping_enabled" value="1" <?= checked($edit_zone['methods']['free_shipping']['enabled'] ?? 0, 1) ?>>
                    <span style="font-weight:600;">Free Shipping</span>
                </label>
                <div style="margin-left:30px;">
                    <div style="margin-bottom:10px;">
                        <label>Title</label>
                        <input type="text" name="free_shipping_title" value="<?= esc_attr($edit_zone['methods']['free_shipping']['title'] ?? 'Free Shipping') ?>" style="width:100%;max-width:300px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                    </div>
                    <div>
                        <label>Minimum Order Amount ($)</label>
                        <input type="number" name="free_shipping_min" value="<?= esc_attr($edit_zone['methods']['free_shipping']['min_amount'] ?? 0) ?>" step="0.01" min="0" style="width:150px;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                        <small>Set to 0 for always free</small>
                    </div>
                </div>
            </div>
            
            <!-- Group Permissions -->
            <h4 style="margin-top:30px;">Group-Based Permissions (Optional)</h4>
            <p style="color:#6b7280;margin-bottom:20px;">Configure special rates for specific B2B groups. Leave unchecked to use default rates.</p>
            
            <?php
            // Get all B2B groups
            $b2b_groups = get_option('b2b_groups', []);
            if(!empty($b2b_groups)):
                foreach($b2b_groups as $group_id => $group_data):
                    $group_perms = $edit_zone['group_permissions'][$group_id] ?? [];
                    $is_allowed = isset($group_perms['allowed']) && $group_perms['allowed'];
            ?>
            <div style="margin-bottom:20px;padding:15px;background:#f9fafb;border-radius:8px;border:2px solid <?= $is_allowed ? '#10b981' : '#e5e7eb' ?>;">
                <label style="display:flex;align-items:center;gap:10px;margin-bottom:15px;cursor:pointer;">
                    <input type="checkbox" name="group_permissions[<?= esc_attr($group_id) ?>][allowed]" value="1" <?= checked($is_allowed, true) ?> onchange="this.closest('div').style.borderColor = this.checked ? '#10b981' : '#e5e7eb'">
                    <span style="font-weight:600;font-size:15px;"><?= esc_html($group_data['name'] ?? $group_id) ?></span>
                </label>
                
                <div style="margin-left:30px;display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;">Flat Rate Cost ($)</label>
                        <input type="number" name="group_permissions[<?= esc_attr($group_id) ?>][flat_rate_cost]" value="<?= esc_attr($group_perms['flat_rate_cost'] ?? '') ?>" step="0.01" min="0" placeholder="Default: <?= esc_attr($edit_zone['methods']['flat_rate']['cost'] ?? 0) ?>" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                        <small style="color:#6b7280;">Leave empty to use default</small>
                    </div>
                    
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;">Free Shipping Min ($)</label>
                        <input type="number" name="group_permissions[<?= esc_attr($group_id) ?>][free_shipping_min]" value="<?= esc_attr($group_perms['free_shipping_min'] ?? '') ?>" step="0.01" min="0" placeholder="Default: <?= esc_attr($edit_zone['methods']['free_shipping']['min_amount'] ?? 0) ?>" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                        <small style="color:#6b7280;">Set 0 for always free</small>
                    </div>
                </div>
                
                <div style="margin-left:30px;margin-top:10px;">
                    <label style="display:block;margin-bottom:5px;font-size:13px;">Hide Methods (Optional)</label>
                    <label style="display:inline-flex;align-items:center;gap:5px;margin-right:15px;">
                        <input type="checkbox" name="group_permissions[<?= esc_attr($group_id) ?>][hidden_methods][]" value="flat_rate" <?= checked(in_array('flat_rate', $group_perms['hidden_methods'] ?? []), true) ?>>
                        <span style="font-size:13px;">Hide Flat Rate</span>
                    </label>
                    <label style="display:inline-flex;align-items:center;gap:5px;">
                        <input type="checkbox" name="group_permissions[<?= esc_attr($group_id) ?>][hidden_methods][]" value="free_shipping" <?= checked(in_array('free_shipping', $group_perms['hidden_methods'] ?? []), true) ?>>
                        <span style="font-size:13px;">Hide Free Shipping</span>
                    </label>
                </div>
            </div>
            <?php 
                endforeach;
            else:
            ?>
            <p style="color:#6b7280;font-style:italic;">No B2B groups configured. Create groups in B2B Module → Groups.</p>
            <?php endif; ?>
            
            <button type="submit" name="save_zone" class="primary">Save Zone</button>
            <a href="<?= home_url('/b2b-panel/settings/shipping') ?>" style="margin-left:10px;"><button type="button" class="secondary">Cancel</button></a>
        </form>
    </div>
    <?php else: ?>
    <!-- Add New Zone Button -->
    <div style="margin-bottom:20px;">
        <a href="<?= home_url('/b2b-panel/settings/shipping?edit=new') ?>"><button class="primary"><i class="fa-solid fa-plus"></i> Add Shipping Zone</button></a>
    </div>
    <?php endif; ?>
    
    <?php if(!$edit_zone): ?>
    <!-- Zones List -->
    <div class="card">
        <h3 style="margin-top:0;">Configured Zones</h3>
        <?php if(empty($zones)): ?>
            <p style="color:#6b7280;">No shipping zones configured yet. Click "Add Shipping Zone" to create one.</p>
        <?php else: ?>
            <table class="wp-list-table widefat fixed striped" style="width:100%;">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Regions</th>
                        <th>Methods</th>
                        <th>B2B Groups</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($zones as $zone_id => $zone): 
                        $b2b_groups_count = count($zone['group_permissions'] ?? []);
                    ?>
                    <tr>
                        <td><strong><?= esc_html($zone['name']) ?></strong></td>
                        <td><?= esc_html(implode(', ', $zone['regions'] ?? [])) ?: 'All' ?></td>
                        <td>
                            <?php 
                            $methods = [];
                            if($zone['methods']['flat_rate']['enabled'] ?? 0) $methods[] = 'Flat Rate ($'.number_format($zone['methods']['flat_rate']['cost'], 2).')';
                            if($zone['methods']['free_shipping']['enabled'] ?? 0) {
                                $min = $zone['methods']['free_shipping']['min_amount'] ?? 0;
                                $methods[] = 'Free Shipping' . ($min > 0 ? ' (min $'.number_format($min, 2).')' : '');
                            }
                            echo $methods ? implode('<br>', $methods) : 'No methods';
                            ?>
                        </td>
                        <td>
                            <?php if($b2b_groups_count > 0): ?>
                                <span style="padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af;">
                                    <?= $b2b_groups_count ?> group<?= $b2b_groups_count > 1 ? 's' : '' ?>
                                </span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= home_url('/b2b-panel/settings/shipping?edit='.urlencode($zone_id)) ?>"><button class="secondary" style="padding:6px 12px;font-size:12px;"><i class="fa-solid fa-pen"></i> Edit</button></a>
                            <a href="<?= home_url('/b2b-panel/settings/shipping?delete='.urlencode($zone_id)) ?>" onclick="return confirm('Are you sure you want to delete this zone?')"><button class="secondary" style="padding:6px 12px;font-size:12px;background:#dc2626;color:white;border:none;margin-left:5px;"><i class="fa-solid fa-trash"></i></button></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <?php b2b_adm_footer(); exit;
});

/* =====================================================
   BULK ACTIONS, DASHBOARD WIDGETS, ACTIVITY LOG
===================================================== */

// Create Activity Log Table on Activation
register_activation_hook(__FILE__, 'b2b_create_activity_log_table');
function b2b_create_activity_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_activity_log';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        user_name varchar(255) NOT NULL,
        action varchar(100) NOT NULL,
        entity_type varchar(50) NOT NULL,
        entity_id bigint(20) DEFAULT NULL,
        entity_name varchar(255) DEFAULT NULL,
        details text DEFAULT NULL,
        ip_address varchar(50) DEFAULT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY  (id),
        KEY user_id (user_id),
        KEY action (action),
        KEY entity_type (entity_type),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Trigger table creation
b2b_create_activity_log_table();

// Helper Function: Log Activity
function b2b_log_activity($action, $entity_type, $entity_id = null, $entity_name = null, $details = null) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_activity_log';
    
    $current_user = wp_get_current_user();
    if(!$current_user->ID) return false;
    
    $wpdb->insert($table_name, [
        'user_id' => $current_user->ID,
        'user_name' => $current_user->display_name,
        'action' => $action,
        'entity_type' => $entity_type,
        'entity_id' => $entity_id,
        'entity_name' => $entity_name,
        'details' => $details,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
        'created_at' => current_time('mysql')
    ]);
    
    return true;
}

// Daily Cleanup of Old Logs (90 days)
add_action('init', function() {
    if(!wp_next_scheduled('b2b_cleanup_old_logs')) {
        wp_schedule_event(time(), 'daily', 'b2b_cleanup_old_logs');
    }
});

add_action('b2b_cleanup_old_logs', function() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_activity_log';
    $days_to_keep = apply_filters('b2b_activity_log_retention_days', 90);
    
    $wpdb->query($wpdb->prepare(
        "DELETE FROM $table_name WHERE created_at < DATE_SUB(NOW(), INTERVAL %d DAY)",
        $days_to_keep
    ));
});

// Activity Log Page
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'activity_log') return;
    b2b_adm_guard();
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'b2b_activity_log';
    
    // Filters
    $user_filter = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
    $action_filter = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    $entity_filter = isset($_GET['entity_type']) ? sanitize_text_field($_GET['entity_type']) : '';
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $paged = max(1, isset($_GET['paged']) ? intval($_GET['paged']) : 1);
    $per_page = 50;
    $offset = ($paged - 1) * $per_page;
    
    // Build query
    $where = ['1=1'];
    if($user_filter) $where[] = $wpdb->prepare('user_id = %d', $user_filter);
    if($action_filter) $where[] = $wpdb->prepare('action = %s', $action_filter);
    if($entity_filter) $where[] = $wpdb->prepare('entity_type = %s', $entity_filter);
    if($search) $where[] = $wpdb->prepare('(entity_name LIKE %s OR details LIKE %s)', '%'.$search.'%', '%'.$search.'%');
    
    $where_sql = implode(' AND ', $where);
    
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE $where_sql");
    $logs = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_name WHERE $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ));
    
    $total_pages = ceil($total / $per_page);
    
    // Get unique values for filters
    $all_users = $wpdb->get_results("SELECT DISTINCT user_id, user_name FROM $table_name ORDER BY user_name");
    $all_actions = $wpdb->get_col("SELECT DISTINCT action FROM $table_name ORDER BY action");
    $all_entities = $wpdb->get_col("SELECT DISTINCT entity_type FROM $table_name ORDER BY entity_type");
    
    b2b_adm_header('Activity Log');
    ?>
    <div class="page-header">
        <h1 class="page-title">Activity Log</h1>
    </div>
    
    <div class="card">
        <!-- Filters -->
        <div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
            <select onchange="window.location.href='<?= home_url('/b2b-panel/activity-log') ?>?user_id='+this.value+'<?= $action_filter ? '&action='.$action_filter : '' ?><?= $entity_filter ? '&entity_type='.$entity_filter : '' ?><?= $search ? '&s='.urlencode($search) : '' ?>'" style="margin:0;">
                <option value="0">All Users</option>
                <?php foreach($all_users as $u): ?>
                    <option value="<?= $u->user_id ?>" <?= selected($user_filter, $u->user_id) ?>><?= esc_html($u->user_name) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select onchange="window.location.href='<?= home_url('/b2b-panel/activity-log') ?>?action='+this.value+'<?= $user_filter ? '&user_id='.$user_filter : '' ?><?= $entity_filter ? '&entity_type='.$entity_filter : '' ?><?= $search ? '&s='.urlencode($search) : '' ?>'" style="margin:0;">
                <option value="">All Actions</option>
                <?php foreach($all_actions as $a): ?>
                    <option value="<?= esc_attr($a) ?>" <?= selected($action_filter, $a) ?>><?= esc_html($a) ?></option>
                <?php endforeach; ?>
            </select>
            
            <select onchange="window.location.href='<?= home_url('/b2b-panel/activity-log') ?>?entity_type='+this.value+'<?= $user_filter ? '&user_id='.$user_filter : '' ?><?= $action_filter ? '&action='.$action_filter : '' ?><?= $search ? '&s='.urlencode($search) : '' ?>'" style="margin:0;">
                <option value="">All Entity Types</option>
                <?php foreach($all_entities as $e): ?>
                    <option value="<?= esc_attr($e) ?>" <?= selected($entity_filter, $e) ?>><?= esc_html($e) ?></option>
                <?php endforeach; ?>
            </select>
            
            <form method="get" action="<?= home_url('/b2b-panel/activity-log') ?>" style="display:flex;gap:10px;flex:1;">
                <?php if($user_filter): ?><input type="hidden" name="user_id" value="<?= $user_filter ?>"><?php endif; ?>
                <?php if($action_filter): ?><input type="hidden" name="action" value="<?= esc_attr($action_filter) ?>"><?php endif; ?>
                <?php if($entity_filter): ?><input type="hidden" name="entity_type" value="<?= esc_attr($entity_filter) ?>"><?php endif; ?>
                <input name="s" value="<?= esc_attr($search) ?>" placeholder="Search entity or details..." style="margin:0;flex:1;min-width:200px;">
                <button>Search</button>
                <?php if($user_filter || $action_filter || $entity_filter || $search): ?>
                    <a href="<?= home_url('/b2b-panel/activity-log') ?>" style="padding:10px;color:#ef4444;text-decoration:none;font-weight:600;">Reset</a>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- Activity Log Table -->
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Details</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($logs)): ?>
                <tr><td colspan="6" style="text-align:center;padding:30px;color:#999">No activity logs found.</td></tr>
            <?php else: foreach ($logs as $log): 
                $action_colors = [
                    'created' => '#10b981',
                    'updated' => '#3b82f6',
                    'deleted' => '#ef4444',
                    'bulk_action' => '#f59e0b',
                ];
                $color = $action_colors[strtolower($log->action)] ?? '#6b7280';
            ?>
            <tr>
                <td><small style="color:#6b7280;"><?= human_time_diff(strtotime($log->created_at), current_time('timestamp')) ?> ago</small><br><small style="color:#9ca3af;"><?= date('M d, Y H:i', strtotime($log->created_at)) ?></small></td>
                <td><?= get_avatar($log->user_id, 32) ?> <strong><?= esc_html($log->user_name) ?></strong></td>
                <td><span style="background:<?= $color ?>;color:white;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;text-transform:uppercase;"><?= esc_html($log->action) ?></span></td>
                <td><strong><?= esc_html($log->entity_type) ?></strong><?php if($log->entity_name): ?><br><small style="color:#6b7280;"><?= esc_html($log->entity_name) ?></small><?php endif; ?></td>
                <td><small style="color:#6b7280;"><?= esc_html($log->details ?: '-') ?></small></td>
                <td><code style="font-size:11px;color:#6b7280;"><?= esc_html($log->ip_address) ?></code></td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        
        <!-- Pagination -->
        <?php if($total_pages > 1): ?>
        <div style="margin-top:20px;display:flex;justify-content:center;align-items:center;gap:10px;">
            <span style="color:#6b7280;font-size:14px;">Page:</span>
            <select onchange="window.location.href=this.value" style="margin:0;padding:8px 12px;border:1px solid #e5e7eb;border-radius:6px;background:white;cursor:pointer;">
                <?php 
                for($i = 1; $i <= $total_pages; $i++) {
                    $params = [];
                    if($user_filter) $params[] = 'user_id=' . $user_filter;
                    if($action_filter) $params[] = 'action=' . urlencode($action_filter);
                    if($entity_filter) $params[] = 'entity_type=' . urlencode($entity_filter);
                    if($search) $params[] = 's=' . urlencode($search);
                    if($i > 1) $params[] = 'paged=' . $i;
                    $url = home_url('/b2b-panel/activity-log') . (!empty($params) ? '?' . implode('&', $params) : '');
                    echo '<option value="' . esc_attr($url) . '" ' . ($i == $paged ? 'selected' : '') . '>Page ' . $i . ' of ' . $total_pages . '</option>';
                }
                ?>
            </select>
            <span style="color:#6b7280;font-size:14px;">(<?= $total ?> total entries)</span>
        </div>
        <?php endif; ?>
    </div>
    <?php b2b_adm_footer(); exit;
});

// Add Activity Log route
add_action('init', function() {
    add_rewrite_rule('^b2b-panel/activity-log/?$', 'index.php?b2b_adm_page=activity_log', 'top');
    
    if (!get_option('b2b_rewrite_v18_activitylog')) {
        flush_rewrite_rules();
        update_option('b2b_rewrite_v18_activitylog', true);
    }
});

// AJAX: Bulk Actions for Products
add_action('wp_ajax_b2b_bulk_action_products', function() {
    check_ajax_referer('b2b_ajax_nonce', 'nonce');
    
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $action = sanitize_text_field($_POST['bulk_action'] ?? '');
    // Parse product_ids - it comes as comma-separated string
    $product_ids_str = sanitize_text_field($_POST['product_ids'] ?? '');
    $product_ids = !empty($product_ids_str) ? array_map('intval', explode(',', $product_ids_str)) : [];
    $chunk = intval($_POST['chunk'] ?? 0);
    $chunk_size = 10;
    
    // Process chunk
    $chunk_ids = array_slice($product_ids, $chunk * $chunk_size, $chunk_size);
    $results = ['success' => [], 'errors' => []];
    
    foreach($chunk_ids as $product_id) {
        $product = wc_get_product($product_id);
        if(!$product) {
            $results['errors'][] = "Product ID $product_id not found";
            continue;
        }
        
        try {
            switch($action) {
                case 'delete':
                    wp_delete_post($product_id, true);
                    b2b_log_activity('deleted', 'product', $product_id, $product->get_name(), 'Bulk delete');
                    $results['success'][] = $product->get_name();
                    break;
                    
                case 'price_update':
                    $price_action = sanitize_text_field($_POST['price_action'] ?? 'increase');
                    $price_value = floatval($_POST['price_value'] ?? 0);
                    $price_type = sanitize_text_field($_POST['price_type'] ?? 'percentage');
                    
                    $current_price = $product->get_regular_price();
                    if($current_price > 0) {
                        if($price_type == 'percentage') {
                            $new_price = $price_action == 'increase' 
                                ? $current_price * (1 + $price_value / 100)
                                : $current_price * (1 - $price_value / 100);
                        } else {
                            $new_price = $price_action == 'increase'
                                ? $current_price + $price_value
                                : $current_price - $price_value;
                        }
                        $product->set_regular_price(max(0, $new_price));
                        $product->save();
                        b2b_log_activity('updated', 'product', $product_id, $product->get_name(), "Bulk price update: $price_action $price_value");
                        $results['success'][] = $product->get_name();
                    }
                    break;
                    
                case 'category_add':
                    $category_id = intval($_POST['category_id'] ?? 0);
                    if($category_id) {
                        $current_cats = $product->get_category_ids();
                        if(!in_array($category_id, $current_cats)) {
                            $current_cats[] = $category_id;
                            $product->set_category_ids($current_cats);
                            $product->save();
                            b2b_log_activity('updated', 'product', $product_id, $product->get_name(), 'Bulk category added');
                            $results['success'][] = $product->get_name();
                        }
                    }
                    break;
                    
                case 'stock_update':
                    $stock_value = floatval($_POST['stock_value'] ?? 0);
                    $stock_action = sanitize_text_field($_POST['stock_action'] ?? 'set');
                    $stock_type = sanitize_text_field($_POST['stock_type'] ?? 'fixed');
                    
                    $current_stock = $product->get_stock_quantity();
                    if($current_stock === null) $current_stock = 0;
                    
                    // Calculate new stock based on type and action
                    if($stock_type == 'percentage') {
                        // Percentage calculations
                        if($stock_action == 'set') {
                            $new_stock = round($current_stock * ($stock_value / 100));
                        } else if($stock_action == 'increase') {
                            $new_stock = round($current_stock * (1 + $stock_value / 100));
                        } else { // decrease
                            $new_stock = max(0, round($current_stock * (1 - $stock_value / 100)));
                        }
                    } else {
                        // Fixed amount calculations
                        if($stock_action == 'set') {
                            $new_stock = intval($stock_value);
                        } else if($stock_action == 'increase') {
                            $new_stock = $current_stock + intval($stock_value);
                        } else { // decrease
                            $new_stock = max(0, $current_stock - intval($stock_value));
                        }
                    }
                    
                    $product->set_manage_stock(true);
                    $product->set_stock_quantity($new_stock);
                    $product->set_stock_status($new_stock > 0 ? 'instock' : 'outofstock');
                    $product->save();
                    b2b_log_activity('updated', 'product', $product_id, $product->get_name(), "Bulk stock update: $stock_type $stock_action $stock_value (new: $new_stock)");
                    $results['success'][] = $product->get_name();
                    break;
            }
        } catch(Exception $e) {
            $results['errors'][] = $product->get_name() . ': ' . $e->getMessage();
        }
    }
    
    wp_send_json_success([
        'results' => $results,
        'has_more' => ($chunk + 1) * $chunk_size < count($product_ids),
        'next_chunk' => $chunk + 1,
        'progress' => min(100, round((($chunk + 1) * $chunk_size / count($product_ids)) * 100))
    ]);
});

// AJAX: Bulk Actions for Customers
add_action('wp_ajax_b2b_bulk_action_customers', function() {
    check_ajax_referer('b2b_ajax_nonce', 'nonce');
    
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $action = sanitize_text_field($_POST['bulk_action'] ?? '');
    $customer_ids = array_map('intval', $_POST['customer_ids'] ?? []);
    $chunk = intval($_POST['chunk'] ?? 0);
    $chunk_size = 10;
    
    $chunk_ids = array_slice($customer_ids, $chunk * $chunk_size, $chunk_size);
    $results = ['success' => [], 'errors' => []];
    
    foreach($chunk_ids as $customer_id) {
        $customer = get_user_by('ID', $customer_id);
        if(!$customer) {
            $results['errors'][] = "Customer ID $customer_id not found";
            continue;
        }
        
        try {
            switch($action) {
                case 'assign_group':
                    $group_slug = sanitize_text_field($_POST['group_slug'] ?? '');
                    if($group_slug) {
                        update_user_meta($customer_id, 'b2b_group_slug', $group_slug);
                        b2b_log_activity('updated', 'customer', $customer_id, $customer->display_name, "Bulk group assignment: $group_slug");
                        $results['success'][] = $customer->display_name;
                    }
                    break;
                    
                case 'assign_role':
                    $b2b_role = sanitize_text_field($_POST['b2b_role'] ?? '');
                    if($b2b_role) {
                        update_user_meta($customer_id, 'b2b_role', $b2b_role);
                        b2b_log_activity('updated', 'customer', $customer_id, $customer->display_name, "Bulk role assignment: $b2b_role");
                        $results['success'][] = $customer->display_name;
                    }
                    break;
                    
                case 'approve':
                    update_user_meta($customer_id, 'b2b_status', 'approved');
                    b2b_log_activity('updated', 'customer', $customer_id, $customer->display_name, 'Bulk approval');
                    $results['success'][] = $customer->display_name;
                    break;
                    
                case 'reject':
                    update_user_meta($customer_id, 'b2b_status', 'rejected');
                    b2b_log_activity('updated', 'customer', $customer_id, $customer->display_name, 'Bulk rejection');
                    $results['success'][] = $customer->display_name;
                    break;
            }
        } catch(Exception $e) {
            $results['errors'][] = $customer->display_name . ': ' . $e->getMessage();
        }
    }
    
    wp_send_json_success([
        'results' => $results,
        'has_more' => ($chunk + 1) * $chunk_size < count($customer_ids),
        'next_chunk' => $chunk + 1,
        'progress' => min(100, round((($chunk + 1) * $chunk_size / count($customer_ids)) * 100))
    ]);
});

// AJAX: Bulk Actions for Orders
add_action('wp_ajax_b2b_bulk_action_orders', function() {
    check_ajax_referer('b2b_ajax_nonce', 'nonce');
    
    if (!current_user_can('manage_woocommerce')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $action = sanitize_text_field($_POST['bulk_action'] ?? '');
    $order_ids = array_map('intval', $_POST['order_ids'] ?? []);
    $chunk = intval($_POST['chunk'] ?? 0);
    $chunk_size = 10;
    
    $chunk_ids = array_slice($order_ids, $chunk * $chunk_size, $chunk_size);
    $results = ['success' => [], 'errors' => []];
    
    foreach($chunk_ids as $order_id) {
        $order = wc_get_order($order_id);
        if(!$order) {
            $results['errors'][] = "Order #$order_id not found";
            continue;
        }
        
        try {
            switch($action) {
                case 'update_status':
                    $new_status = sanitize_text_field($_POST['order_status'] ?? '');
                    if($new_status) {
                        $order->update_status($new_status);
                        b2b_log_activity('updated', 'order', $order_id, "Order #$order_id", "Bulk status update: $new_status");
                        $results['success'][] = "Order #$order_id";
                    }
                    break;
                    
                case 'delete':
                    wp_delete_post($order_id, true);
                    b2b_log_activity('deleted', 'order', $order_id, "Order #$order_id", 'Bulk delete');
                    $results['success'][] = "Order #$order_id";
                    break;
            }
        } catch(Exception $e) {
            $results['errors'][] = "Order #$order_id: " . $e->getMessage();
        }
    }
    
    wp_send_json_success([
        'results' => $results,
        'has_more' => ($chunk + 1) * $chunk_size < count($order_ids),
        'next_chunk' => $chunk + 1,
        'progress' => min(100, round((($chunk + 1) * $chunk_size / count($order_ids)) * 100))
    ]);
});

/* =====================================================
   DASHBOARD WIDGETS WITH CHART.JS & UI UPDATES
===================================================== */

// Update Dashboard with Chart.js Widgets
add_action('template_redirect', function () {
    $page = get_query_var('b2b_adm_page');
    if ($page !== 'dashboard') return;
    
    // This hook runs before the dashboard renders
    // We'll modify the dashboard rendering directly in the header
}, 5); // Priority 5 to run before main dashboard

// Enhance sidebar menu with Activity Log link
add_filter('b2b_sidebar_menu_items', function($items) {
    // Add Activity Log after Reports
    $new_items = [];
    foreach($items as $key => $item) {
        $new_items[$key] = $item;
        if($key === 'reports') {
            $new_items['activity_log'] = [
                'label' => 'Activity Log',
                'icon' => 'fa-solid fa-clipboard-list',
                'url' => home_url('/b2b-panel/activity-log'),
                'page' => 'activity_log'
            ];
        }
    }
    return $new_items;
});

// Add Bulk Actions UI to Products Page (Inject via JavaScript)
add_action('wp_footer', function() {
    $page = get_query_var('b2b_adm_page');
    if($page !== 'products' && $page !== 'customers' && $page !== 'orders') return;
    ?>
    <script>
    // Inject Bulk Actions UI
    jQuery(document).ready(function($) {
        <?php if($page == 'products'): ?>
        // Products Bulk Actions
        // Note: Checkboxes already exist in HTML, no need to prepend
        
        // Make functions global
        window.toggleAllProducts = function(masterCheckbox) {
            $('.product-checkbox').prop('checked', $(masterCheckbox).prop('checked'));
            window.updateBulkSelection();
        };
        
        window.updateBulkSelection = function() {
            const checked = $('.product-checkbox:checked').length;
            $('#selectedCount').text(checked);
            if(checked > 0) {
                $('#bulkActionBar').show();
            } else {
                $('#bulkActionBar').hide();
            }
        };
        
        // Connect checkboxes to update selection
        $('.product-checkbox').on('change', updateBulkSelection);
        
        <?php endif; ?>
    });
    </script>
    <?php
});

// ============================================================================
// B2B SUPPORT TICKET MODULE
// ============================================================================

// Create Support Tickets Tables
function b2b_create_support_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    // Tickets table
    $table_tickets = $wpdb->prefix . 'b2b_support_tickets';
    $sql_tickets = "CREATE TABLE IF NOT EXISTS $table_tickets (
        ticket_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ticket_number VARCHAR(50) UNIQUE NOT NULL,
        customer_id BIGINT UNSIGNED NOT NULL,
        assigned_agent_id BIGINT UNSIGNED NULL,
        order_id BIGINT UNSIGNED NULL,
        subject VARCHAR(255) NOT NULL,
        category ENUM('order','product','delivery','billing','general') DEFAULT 'general',
        priority ENUM('low','normal','high','urgent') DEFAULT 'normal',
        status ENUM('new','open','pending','resolved','closed') DEFAULT 'new',
        created_at DATETIME NOT NULL,
        updated_at DATETIME NOT NULL,
        resolved_at DATETIME NULL,
        closed_at DATETIME NULL,
        INDEX idx_customer (customer_id),
        INDEX idx_agent (assigned_agent_id),
        INDEX idx_status (status),
        INDEX idx_created (created_at)
    ) $charset_collate;";
    
    // Replies table
    $table_replies = $wpdb->prefix . 'b2b_support_replies';
    $sql_replies = "CREATE TABLE IF NOT EXISTS $table_replies (
        reply_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ticket_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        message TEXT NOT NULL,
        is_internal TINYINT(1) DEFAULT 0,
        created_at DATETIME NOT NULL,
        INDEX idx_ticket (ticket_id),
        INDEX idx_user (user_id)
    ) $charset_collate;";
    
    // Attachments table
    $table_attachments = $wpdb->prefix . 'b2b_support_attachments';
    $sql_attachments = "CREATE TABLE IF NOT EXISTS $table_attachments (
        attachment_id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        ticket_id BIGINT UNSIGNED NOT NULL,
        reply_id BIGINT UNSIGNED NULL,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        file_size BIGINT UNSIGNED NOT NULL,
        file_type VARCHAR(100) NOT NULL,
        uploaded_by BIGINT UNSIGNED NOT NULL,
        uploaded_at DATETIME NOT NULL,
        INDEX idx_ticket (ticket_id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql_tickets);
    dbDelta($sql_replies);
    dbDelta($sql_attachments);
}
add_action('init', 'b2b_create_support_tables');

// ========================================
// WooCommerce My Account Integration
// ========================================

// Register WooCommerce My Account endpoints for support
function b2b_register_support_endpoints() {
    add_rewrite_endpoint('support-tickets', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('create-support-ticket', EP_ROOT | EP_PAGES);
    add_rewrite_endpoint('view-support-ticket', EP_ROOT | EP_PAGES);
}
add_action('init', 'b2b_register_support_endpoints');

// Add Support Tickets to WooCommerce My Account menu
function b2b_add_support_to_my_account_menu($items) {
    // Insert Support Tickets after Orders
    $new_items = [];
    foreach($items as $key => $label) {
        $new_items[$key] = $label;
        if($key === 'orders') {
            $new_items['support-tickets'] = __('Support Tickets', 'woocommerce');
        }
    }
    return $new_items;
}
add_filter('woocommerce_account_menu_items', 'b2b_add_support_to_my_account_menu');

// Support Tickets List Page (My Account)
function b2b_my_account_support_tickets_content() {
    global $wpdb;
    $table = $wpdb->prefix . 'b2b_support_tickets';
    $customer_id = get_current_user_id();
    
    $tickets = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE customer_id = %d ORDER BY created_at DESC",
        $customer_id
    ));
    
    echo '<h2>Support Tickets</h2>';
    echo '<p><a href="' . wc_get_endpoint_url('create-support-ticket', '', wc_get_page_permalink('myaccount')) . '" class="button">Create New Ticket</a></p>';
    
    if(empty($tickets)) {
        echo '<p>You have no support tickets yet.</p>';
    } else {
        echo '<table class="shop_table shop_table_responsive my_account_orders">';
        echo '<thead><tr><th>Ticket #</th><th>Subject</th><th>Status</th><th>Priority</th><th>Created</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach($tickets as $ticket) {
            $status_colors = [
                'new' => '#3b82f6',
                'open' => '#10b981',
                'pending' => '#f59e0b',
                'resolved' => '#6366f1',
                'closed' => '#6b7280'
            ];
            $color = $status_colors[$ticket->status] ?? '#6b7280';
            
            echo '<tr>';
            echo '<td data-title="Ticket #">' . esc_html($ticket->ticket_number) . '</td>';
            echo '<td data-title="Subject">' . esc_html($ticket->subject) . '</td>';
            echo '<td data-title="Status"><span style="background:' . $color . ';color:white;padding:4px 8px;border-radius:4px;font-size:12px">' . esc_html(ucfirst($ticket->status)) . '</span></td>';
            echo '<td data-title="Priority">' . esc_html(ucfirst($ticket->priority)) . '</td>';
            echo '<td data-title="Created">' . esc_html(date('M j, Y', strtotime($ticket->created_at))) . '</td>';
            echo '<td data-title="Actions"><a href="' . wc_get_endpoint_url('view-support-ticket', $ticket->ticket_id, wc_get_page_permalink('myaccount')) . '" class="button view">View</a></td>';
            echo '</tr>';
        }
        echo '</tbody></table>';
    }
}
add_action('woocommerce_account_support-tickets_endpoint', 'b2b_my_account_support_tickets_content');

// Create Support Ticket Page (My Account)
function b2b_my_account_create_ticket_content() {
    global $wpdb;
    $customer_id = get_current_user_id();
    
    // Get customer's recent orders
    $orders = wc_get_orders([
        'customer_id' => $customer_id,
        'limit' => 20,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    echo '<h2>Create Support Ticket</h2>';
    echo '<form method="post" id="createTicketForm" style="max-width:600px">';
    echo wp_nonce_field('b2b_create_ticket_wc', 'ticket_nonce', true, false);
    
    echo '<p><label>Subject *</label><input type="text" name="subject" required class="input-text" style="width:100%"></p>';
    
    echo '<p><label>Category *</label><select name="category" required class="input-text" style="width:100%">';
    echo '<option value="general">General</option>';
    echo '<option value="order">Order Issue</option>';
    echo '<option value="product">Product Question</option>';
    echo '<option value="delivery">Delivery</option>';
    echo '<option value="billing">Billing</option>';
    echo '</select></p>';
    
    echo '<p><label>Priority</label><select name="priority" class="input-text" style="width:100%">';
    echo '<option value="normal">Normal</option>';
    echo '<option value="low">Low</option>';
    echo '<option value="high">High</option>';
    echo '<option value="urgent">Urgent</option>';
    echo '</select></p>';
    
    if(!empty($orders)) {
        echo '<p><label>Related Order (Optional)</label><select name="order_id" class="input-text" style="width:100%">';
        echo '<option value="">No specific order</option>';
        foreach($orders as $order) {
            echo '<option value="' . $order->get_id() . '">Order #' . $order->get_order_number() . ' - ' . $order->get_date_created()->format('M j, Y') . '</option>';
        }
        echo '</select></p>';
    }
    
    echo '<p><label>Message *</label><textarea name="message" required rows="6" class="input-text" style="width:100%"></textarea></p>';
    
    echo '<p><button type="submit" class="button">Submit Ticket</button></p>';
    echo '</form>';
    
    // Handle form submission
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ticket_nonce']) && wp_verify_nonce($_POST['ticket_nonce'], 'b2b_create_ticket_wc')) {
        $table = $wpdb->prefix . 'b2b_support_tickets';
        $table_replies = $wpdb->prefix . 'b2b_support_replies';
        
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $message = sanitize_textarea_field($_POST['message'] ?? '');
        $category = sanitize_text_field($_POST['category'] ?? 'general');
        $priority = sanitize_text_field($_POST['priority'] ?? 'normal');
        $order_id = intval($_POST['order_id'] ?? 0);
        
        if(!empty($subject) && !empty($message)) {
            $ticket_number = b2b_generate_ticket_number();
            $now = current_time('mysql');
            
            $wpdb->insert($table, [
                'ticket_number' => $ticket_number,
                'customer_id' => $customer_id,
                'order_id' => $order_id > 0 ? $order_id : null,
                'subject' => $subject,
                'category' => $category,
                'priority' => $priority,
                'status' => 'new',
                'created_at' => $now,
                'updated_at' => $now
            ]);
            
            $ticket_id = $wpdb->insert_id;
            
            // Add initial message
            $wpdb->insert($table_replies, [
                'ticket_id' => $ticket_id,
                'user_id' => $customer_id,
                'message' => $message,
                'is_internal' => 0,
                'created_at' => $now
            ]);
            
            // Activity log
            b2b_log_activity(get_current_user_id(), 'ticket_created', 'support_ticket', $ticket_id, 'Created support ticket: ' . $ticket_number);
            
            wc_add_notice('Ticket created successfully! Ticket #' . $ticket_number, 'success');
            wp_redirect(wc_get_endpoint_url('support-tickets', '', wc_get_page_permalink('myaccount')));
            exit;
        }
    }
}
add_action('woocommerce_account_create-support-ticket_endpoint', 'b2b_my_account_create_ticket_content');

// View Support Ticket Page (My Account)
function b2b_my_account_view_ticket_content($ticket_id) {
    global $wpdb;
    $table_tickets = $wpdb->prefix . 'b2b_support_tickets';
    $table_replies = $wpdb->prefix . 'b2b_support_replies';
    $customer_id = get_current_user_id();
    
    $ticket = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_tickets WHERE ticket_id = %d AND customer_id = %d",
        $ticket_id, $customer_id
    ));
    
    if(!$ticket) {
        echo '<p>Ticket not found.</p>';
        return;
    }
    
    echo '<h2>Ticket #' . esc_html($ticket->ticket_number) . '</h2>';
    echo '<p><a href="' . wc_get_endpoint_url('support-tickets', '', wc_get_page_permalink('myaccount')) . '">&larr; Back to Tickets</a></p>';
    
    // Ticket info
    echo '<div style="background:#f9fafb;padding:20px;border-radius:8px;margin-bottom:20px">';
    echo '<p><strong>Subject:</strong> ' . esc_html($ticket->subject) . '</p>';
    echo '<p><strong>Status:</strong> ' . esc_html(ucfirst($ticket->status)) . '</p>';
    echo '<p><strong>Priority:</strong> ' . esc_html(ucfirst($ticket->priority)) . '</p>';
    echo '<p><strong>Created:</strong> ' . esc_html(date('M j, Y g:i A', strtotime($ticket->created_at))) . '</p>';
    
    // Order info if linked
    if($ticket->order_id) {
        $order = wc_get_order($ticket->order_id);
        if($order) {
            echo '<p><strong>Related Order:</strong> <a href="' . $order->get_view_order_url() . '">Order #' . $order->get_order_number() . '</a></p>';
        }
    }
    echo '</div>';
    
    // Conversation
    echo '<h3>Conversation</h3>';
    $replies = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_replies WHERE ticket_id = %d AND is_internal = 0 ORDER BY created_at ASC",
        $ticket_id
    ));
    
    foreach($replies as $reply) {
        $user = get_userdata($reply->user_id);
        $is_customer = ($reply->user_id == $customer_id);
        $bg = $is_customer ? '#eff6ff' : '#f0fdf4';
        
        echo '<div style="background:' . $bg . ';padding:15px;border-radius:8px;margin-bottom:15px">';
        echo '<p style="margin:0 0 10px;font-weight:600">' . esc_html($user->display_name) . ' <span style="font-weight:normal;color:#6b7280;font-size:13px">' . date('M j, Y g:i A', strtotime($reply->created_at)) . '</span></p>';
        echo '<p style="margin:0">' . nl2br(esc_html($reply->message)) . '</p>';
        echo '</div>';
    }
    
    // Reply form (only if not closed)
    if($ticket->status !== 'closed') {
        echo '<h3>Add Message</h3>';
        echo '<form method="post">';
        echo wp_nonce_field('b2b_reply_ticket_wc_' . $ticket_id, 'reply_nonce', true, false);
        echo '<p><textarea name="message" required rows="5" class="input-text" style="width:100%" placeholder="Type your message..."></textarea></p>';
        echo '<p><button type="submit" class="button">Send Message</button></p>';
        echo '</form>';
        
        // Handle reply submission
        if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_nonce']) && wp_verify_nonce($_POST['reply_nonce'], 'b2b_reply_ticket_wc_' . $ticket_id)) {
            $message = sanitize_textarea_field($_POST['message'] ?? '');
            if(!empty($message)) {
                $wpdb->insert($table_replies, [
                    'ticket_id' => $ticket_id,
                    'user_id' => $customer_id,
                    'message' => $message,
                    'is_internal' => 0,
                    'created_at' => current_time('mysql')
                ]);
                
                // Update ticket
                $wpdb->update($table_tickets, 
                    ['updated_at' => current_time('mysql'), 'status' => 'open'],
                    ['ticket_id' => $ticket_id]
                );
                
                wp_redirect(wc_get_endpoint_url('view-support-ticket', $ticket_id, wc_get_page_permalink('myaccount')));
                exit;
            }
        }
    } else {
        echo '<p style="color:#6b7280">This ticket is closed. No further messages can be added.</p>';
    }
}
add_action('woocommerce_account_view-support-ticket_endpoint', 'b2b_my_account_view_ticket_content');

// Add support form to order detail page
function b2b_add_support_form_to_order_page($order) {
    $customer_id = get_current_user_id();
    $order_id = $order->get_id();
    
    echo '<div style="margin-top:30px;padding:20px;background:#f9fafb;border-radius:8px">';
    echo '<h3 style="cursor:pointer" onclick="document.getElementById(\'supportForm' . $order_id . '\').style.display=document.getElementById(\'supportForm' . $order_id . '\').style.display===\'none\'?\'block\':\'none\'">Need Help with This Order? ▼</h3>';
    echo '<div id="supportForm' . $order_id . '" style="display:none">';
    echo '<p>Submit a support ticket for this order:</p>';
    echo '<form method="post" action="' . wc_get_endpoint_url('create-support-ticket', '', wc_get_page_permalink('myaccount')) . '">';
    echo wp_nonce_field('b2b_create_ticket_wc', 'ticket_nonce', true, false);
    echo '<input type="hidden" name="order_id" value="' . $order_id . '">';
    echo '<input type="hidden" name="category" value="order">';
    echo '<input type="hidden" name="subject" value="Support for Order #' . $order->get_order_number() . '">';
    echo '<p><label>Priority</label><select name="priority" class="input-text" style="width:100%">';
    echo '<option value="normal">Normal</option>';
    echo '<option value="high">High</option>';
    echo '<option value="urgent">Urgent</option>';
    echo '</select></p>';
    echo '<p><label>Describe your issue *</label><textarea name="message" required rows="4" class="input-text" style="width:100%"></textarea></p>';
    echo '<p><button type="submit" class="button">Submit Ticket</button></p>';
    echo '</form>';
    echo '</div></div>';
}
add_action('woocommerce_after_order_details', 'b2b_add_support_form_to_order_page', 10, 1);

// ========================================
// End WooCommerce My Account Integration
// ========================================

// Generate unique ticket number
function b2b_generate_ticket_number() {
    global $wpdb;
    $table = $wpdb->prefix . 'b2b_support_tickets';
    $last_ticket = $wpdb->get_var("SELECT ticket_number FROM $table ORDER BY ticket_id DESC LIMIT 1");
    
    if($last_ticket) {
        $num = intval(str_replace('TK-', '', $last_ticket)) + 1;
    } else {
        $num = 1;
    }
    
    return 'TK-' . str_pad($num, 5, '0', STR_PAD_LEFT);
}

// AJAX: Create new ticket
add_action('wp_ajax_b2b_create_ticket', function() {
    check_ajax_referer('b2b_ajax_nonce', 'nonce');
    
    global $wpdb;
    $table = $wpdb->prefix . 'b2b_support_tickets';
    $table_replies = $wpdb->prefix . 'b2b_support_replies';
    
    $customer_id = get_current_user_id();
    $subject = sanitize_text_field($_POST['subject'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    $category = sanitize_text_field($_POST['category'] ?? 'general');
    $priority = sanitize_text_field($_POST['priority'] ?? 'normal');
    $order_id = intval($_POST['order_id'] ?? 0);
    
    if(empty($subject) || empty($message)) {
        wp_send_json_error(['message' => 'Subject and message are required']);
    }
    
    $ticket_number = b2b_generate_ticket_number();
    $now = current_time('mysql');
    
    $wpdb->insert($table, [
        'ticket_number' => $ticket_number,
        'customer_id' => $customer_id,
        'order_id' => $order_id > 0 ? $order_id : null,
        'subject' => $subject,
        'category' => $category,
        'priority' => $priority,
        'status' => 'new',
        'created_at' => $now,
        'updated_at' => $now
    ]);
    
    $ticket_id = $wpdb->insert_id;
    
    // Add first message
    $wpdb->insert($table_replies, [
        'ticket_id' => $ticket_id,
        'user_id' => $customer_id,
        'message' => $message,
        'is_internal' => 0,
        'created_at' => $now
    ]);
    
    // Log activity
    b2b_log_activity('created_ticket', 'ticket', $ticket_id, $ticket_number, "Ticket: $subject");
    
    wp_send_json_success([
        'ticket_id' => $ticket_id,
        'ticket_number' => $ticket_number,
        'message' => 'Ticket created successfully'
    ]);
});

// AJAX: Add reply to ticket
add_action('wp_ajax_b2b_add_ticket_reply', function() {
    check_ajax_referer('b2b_ajax_nonce', 'nonce');
    
    global $wpdb;
    $table_replies = $wpdb->prefix . 'b2b_support_replies';
    $table_tickets = $wpdb->prefix . 'b2b_support_tickets';
    
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    $is_internal = intval($_POST['is_internal'] ?? 0);
    $user_id = get_current_user_id();
    
    if(empty($message)) {
        wp_send_json_error(['message' => 'Message is required']);
    }
    
    // Verify access
    if(!current_user_can('manage_woocommerce')) {
        // Customer can only reply to their own tickets
        $ticket = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_tickets WHERE ticket_id = %d",
            $ticket_id
        ));
        
        if(!$ticket || $ticket->customer_id != $user_id) {
            wp_send_json_error(['message' => 'Access denied']);
        }
        $is_internal = 0; // Customers can't create internal notes
    }
    
    $now = current_time('mysql');
    
    $wpdb->insert($table_replies, [
        'ticket_id' => $ticket_id,
        'user_id' => $user_id,
        'message' => $message,
        'is_internal' => $is_internal,
        'created_at' => $now
    ]);
    
    // Update ticket
    $wpdb->update($table_tickets, 
        ['updated_at' => $now],
        ['ticket_id' => $ticket_id]
    );
    
    wp_send_json_success(['message' => 'Reply added successfully']);
});

// AJAX: Update ticket status
add_action('wp_ajax_b2b_update_ticket_status', function() {
    check_ajax_referer('b2b_ajax_nonce', 'nonce');
    
    if(!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Access denied']);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'b2b_support_tickets';
    
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    $status = sanitize_text_field($_POST['status'] ?? '');
    
    $valid_statuses = ['new', 'open', 'pending', 'resolved', 'closed'];
    if(!in_array($status, $valid_statuses)) {
        wp_send_json_error(['message' => 'Invalid status']);
    }
    
    $now = current_time('mysql');
    $update_data = [
        'status' => $status,
        'updated_at' => $now
    ];
    
    if($status == 'resolved') {
        $update_data['resolved_at'] = $now;
    } elseif($status == 'closed') {
        $update_data['closed_at'] = $now;
    }
    
    $wpdb->update($table, $update_data, ['ticket_id' => $ticket_id]);
    
    b2b_log_activity('updated_ticket_status', 'ticket', $ticket_id, null, "Status changed to: $status");
    
    wp_send_json_success(['message' => 'Status updated']);
});

// AJAX: Assign ticket to agent
add_action('wp_ajax_b2b_assign_ticket', function() {
    check_ajax_referer('b2b_ajax_nonce', 'nonce');
    
    if(!current_user_can('manage_woocommerce')) {
        wp_send_json_error(['message' => 'Access denied']);
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'b2b_support_tickets';
    
    $ticket_id = intval($_POST['ticket_id'] ?? 0);
    $agent_id = intval($_POST['agent_id'] ?? 0);
    
    $wpdb->update($table, 
        [
            'assigned_agent_id' => $agent_id > 0 ? $agent_id : null,
            'updated_at' => current_time('mysql')
        ],
        ['ticket_id' => $ticket_id]
    );
    
    $agent_name = $agent_id > 0 ? get_userdata($agent_id)->display_name : 'Unassigned';
    b2b_log_activity('assigned_ticket', 'ticket', $ticket_id, null, "Assigned to: $agent_name");
    
    wp_send_json_success(['message' => 'Ticket assigned']);
});

// ===================================================== 
//    SUPPORT TICKETS PAGES (Admin Panel)
// ===================================================== 
add_action('template_redirect', function () {
    $page = get_query_var('b2b_adm_page');
    if (!in_array($page, ['support-tickets', 'support-ticket'])) return;
    b2b_adm_guard();
    
    if ($page === 'support-tickets') {
        b2b_page_support_tickets();
        exit;
    }
    
    if ($page === 'support-ticket') {
        b2b_page_support_ticket_detail();
        exit;
    }
});

// Support Tickets List Page (Admin)
function b2b_page_support_tickets() {
    if(!current_user_can('manage_woocommerce')) {
        wp_die('Access denied');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'b2b_support_tickets';
    
    // Filters
    $status_filter = sanitize_text_field($_GET['status'] ?? '');
    $priority_filter = sanitize_text_field($_GET['priority'] ?? '');
    $category_filter = sanitize_text_field($_GET['category'] ?? '');
    $agent_filter = intval($_GET['agent'] ?? 0);
    $search = sanitize_text_field($_GET['search'] ?? '');
    
    // Build query
    $where = ['1=1'];
    if($status_filter) $where[] = $wpdb->prepare("status = %s", $status_filter);
    if($priority_filter) $where[] = $wpdb->prepare("priority = %s", $priority_filter);
    if($category_filter) $where[] = $wpdb->prepare("category = %s", $category_filter);
    if($agent_filter) $where[] = $wpdb->prepare("assigned_agent_id = %d", $agent_filter);
    if($search) {
        $like = '%' . $wpdb->esc_like($search) . '%';
        $where[] = $wpdb->prepare("(ticket_number LIKE %s OR subject LIKE %s)", $like, $like);
    }
    
    $where_sql = implode(' AND ', $where);
    
    // Pagination
    $per_page = 20;
    $current_page = max(1, intval($_GET['paged'] ?? 1));
    $offset = ($current_page - 1) * $per_page;
    
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE $where_sql");
    $tickets = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE $where_sql ORDER BY created_at DESC LIMIT %d OFFSET %d",
        $per_page, $offset
    ));
    
    // Statistics
    $stats = [
        'total' => $wpdb->get_var("SELECT COUNT(*) FROM $table"),
        'open' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status IN ('new','open')"),
        'pending' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'pending'"),
        'resolved' => $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'resolved'")
    ];
    
    b2b_adm_header('Support Tickets');
    ?>
    <style>
    .stat-cards { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 12px; }
    .stat-card h3 { margin: 0 0 10px 0; font-size: 14px; opacity: 0.9; }
    .stat-card .number { font-size: 32px; font-weight: bold; }
    .filters { display: flex; gap: 10px; margin-bottom: 20px; flex-wrap: wrap; }
    .filters select, .filters input { padding: 8px 12px; border: 1px solid #ddd; border-radius: 6px; }
    .ticket-table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; }
    .ticket-table th, .ticket-table td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    .ticket-table th { background: #f8f9fa; font-weight: 600; }
    .status-badge { padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; }
    .status-new { background: #e3f2fd; color: #1976d2; }
    .status-open { background: #e8f5e9; color: #388e3c; }
    .status-pending { background: #fff3e0; color: #f57c00; }
    .status-resolved { background: #f1f8e9; color: #689f38; }
    .status-closed { background: #f5f5f5; color: #757575; }
    .priority-low { color: #757575; }
    .priority-normal { color: #1976d2; }
    .priority-high { color: #f57c00; font-weight: 600; }
    .priority-urgent { color: #d32f2f; font-weight: 600; }
    </style>
    
    <div class="stat-cards">
        <div class="stat-card">
            <h3>📊 Total Tickets</h3>
            <div class="number"><?php echo $stats['total']; ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
            <h3>🔥 Open Tickets</h3>
            <div class="number"><?php echo $stats['open']; ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
            <h3>⏳ Pending</h3>
            <div class="number"><?php echo $stats['pending']; ?></div>
        </div>
        <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
            <h3>✅ Resolved</h3>
            <div class="number"><?php echo $stats['resolved']; ?></div>
        </div>
    </div>
    
    <div class="filters">
        <select onchange="window.location.href = updateQueryParam('status', this.value)">
            <option value="">All Status</option>
            <option value="new" <?php selected($status_filter, 'new'); ?>>New</option>
            <option value="open" <?php selected($status_filter, 'open'); ?>>Open</option>
            <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
            <option value="resolved" <?php selected($status_filter, 'resolved'); ?>>Resolved</option>
            <option value="closed" <?php selected($status_filter, 'closed'); ?>>Closed</option>
        </select>
        
        <select onchange="window.location.href = updateQueryParam('priority', this.value)">
            <option value="">All Priority</option>
            <option value="low" <?php selected($priority_filter, 'low'); ?>>Low</option>
            <option value="normal" <?php selected($priority_filter, 'normal'); ?>>Normal</option>
            <option value="high" <?php selected($priority_filter, 'high'); ?>>High</option>
            <option value="urgent" <?php selected($priority_filter, 'urgent'); ?>>Urgent</option>
        </select>
        
        <select onchange="window.location.href = updateQueryParam('category', this.value)">
            <option value="">All Categories</option>
            <option value="order" <?php selected($category_filter, 'order'); ?>>Order</option>
            <option value="product" <?php selected($category_filter, 'product'); ?>>Product</option>
            <option value="delivery" <?php selected($category_filter, 'delivery'); ?>>Delivery</option>
            <option value="billing" <?php selected($category_filter, 'billing'); ?>>Billing</option>
            <option value="general" <?php selected($category_filter, 'general'); ?>>General</option>
        </select>
        
        <input type="text" placeholder="Search tickets..." value="<?php echo esc_attr($search); ?>" 
               onchange="window.location.href = updateQueryParam('search', this.value)">
    </div>
    
    <table class="ticket-table">
        <thead>
            <tr>
                <th>Ticket #</th>
                <th>Subject</th>
                <th>Customer</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($tickets as $ticket): 
                $customer = get_userdata($ticket->customer_id);
            ?>
            <tr>
                <td><strong><?php echo esc_html($ticket->ticket_number); ?></strong></td>
                <td><?php echo esc_html($ticket->subject); ?></td>
                <td><?php echo $customer ? esc_html($customer->display_name) : 'Unknown'; ?></td>
                <td><?php echo ucfirst($ticket->category); ?></td>
                <td class="priority-<?php echo $ticket->priority; ?>"><?php echo ucfirst($ticket->priority); ?></td>
                <td><span class="status-badge status-<?php echo $ticket->status; ?>"><?php echo ucfirst($ticket->status); ?></span></td>
                <td><?php echo date('Y-m-d H:i', strtotime($ticket->created_at)); ?></td>
                <td>
                    <a href="?b2b_adm_page=support-ticket&ticket_id=<?php echo $ticket->ticket_id; ?>" class="button button-small">View</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <script>
    function updateQueryParam(key, value) {
        const url = new URL(window.location.href);
        if(value) {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
        url.searchParams.delete('paged'); // Reset pagination
        return url.toString();
    }
    </script>
    
    <?php
    b2b_adm_footer();
}

// Support Ticket Detail Page (Admin)
function b2b_page_support_ticket_detail() {
    if(!current_user_can('manage_woocommerce')) {
        wp_die('Access denied');
    }
    
    global $wpdb;
    $ticket_id = intval($_GET['ticket_id'] ?? 0);
    
    $table_tickets = $wpdb->prefix . 'b2b_support_tickets';
    $table_replies = $wpdb->prefix . 'b2b_support_replies';
    
    $ticket = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_tickets WHERE ticket_id = %d",
        $ticket_id
    ));
    
    if(!$ticket) {
        wp_die('Ticket not found');
    }
    
    $replies = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_replies WHERE ticket_id = %d ORDER BY created_at ASC",
        $ticket_id
    ));
    
    $customer = get_userdata($ticket->customer_id);
    $order = $ticket->order_id ? wc_get_order($ticket->order_id) : null;
    
    b2b_adm_header('Ticket: ' . $ticket->ticket_number);
    ?>
    <style>
    .ticket-header { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .ticket-meta { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 15px; }
    .ticket-meta-item { padding: 10px; background: #f8f9fa; border-radius: 6px; }
    .ticket-meta-item label { display: block; font-size: 12px; color: #666; margin-bottom: 4px; }
    .ticket-meta-item value { font-weight: 600; }
    .order-info { background: #e3f2fd; padding: 15px; border-radius: 8px; margin: 20px 0; }
    .messages-container { background: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
    .message { padding: 15px; margin-bottom: 15px; border-radius: 8px; border-left: 4px solid #667eea; background: #f8f9fa; }
    .message.internal { background: #fff3e0; border-left-color: #f57c00; }
    .message-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
    .message-author { font-weight: 600; }
    .message-time { color: #666; font-size: 13px; }
    .reply-form { background: white; padding: 20px; border-radius: 8px; }
    .quick-actions { display: flex; gap: 10px; margin-bottom: 20px; }
    </style>
    
    <div class="ticket-header">
        <h2><?php echo esc_html($ticket->subject); ?></h2>
        <div class="ticket-meta">
            <div class="ticket-meta-item">
                <label>Ticket Number:</label>
                <value><?php echo esc_html($ticket->ticket_number); ?></value>
            </div>
            <div class="ticket-meta-item">
                <label>Customer:</label>
                <value><?php echo $customer ? esc_html($customer->display_name) : 'Unknown'; ?></value>
            </div>
            <div class="ticket-meta-item">
                <label>Category:</label>
                <value><?php echo ucfirst($ticket->category); ?></value>
            </div>
            <div class="ticket-meta-item">
                <label>Priority:</label>
                <value class="priority-<?php echo $ticket->priority; ?>"><?php echo ucfirst($ticket->priority); ?></value>
            </div>
            <div class="ticket-meta-item">
                <label>Status:</label>
                <value><span class="status-badge status-<?php echo $ticket->status; ?>"><?php echo ucfirst($ticket->status); ?></span></value>
            </div>
            <div class="ticket-meta-item">
                <label>Created:</label>
                <value><?php echo date('Y-m-d H:i', strtotime($ticket->created_at)); ?></value>
            </div>
        </div>
    </div>
    
    <?php if($order): ?>
    <div class="order-info">
        <h3>🛒 Linked Order: <a href="?b2b_adm_page=order&order_id=<?php echo $order->get_id(); ?>">#<?php echo $order->get_order_number(); ?></a></h3>
        <p><strong>Date:</strong> <?php echo $order->get_date_created()->format('Y-m-d H:i'); ?> | 
           <strong>Status:</strong> <?php echo $order->get_status(); ?> | 
           <strong>Total:</strong> <?php echo $order->get_formatted_order_total(); ?></p>
        <p><strong>Products:</strong></p>
        <ul>
            <?php foreach($order->get_items() as $item): ?>
            <li><?php echo $item->get_name(); ?> × <?php echo $item->get_quantity(); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="quick-actions">
        <label>Change Status:</label>
        <button onclick="changeTicketStatus('new')" class="button">New</button>
        <button onclick="changeTicketStatus('open')" class="button">Open</button>
        <button onclick="changeTicketStatus('pending')" class="button">Pending</button>
        <button onclick="changeTicketStatus('resolved')" class="button button-primary">Resolved</button>
        <button onclick="changeTicketStatus('closed')" class="button">Closed</button>
    </div>
    
    <div class="quick-actions">
        <label>Assign to:</label>
        <select id="assignAgent" onchange="assignTicket(this.value)">
            <option value="0">Unassigned</option>
            <?php
            $agents = get_users(['role' => 'administrator']);
            foreach($agents as $agent):
            ?>
            <option value="<?php echo $agent->ID; ?>" <?php selected($ticket->assigned_agent_id, $agent->ID); ?>>
                <?php echo esc_html($agent->display_name); ?>
            </option>
            <?php endforeach; ?>
        </select>
    </div>
    
    <div class="messages-container">
        <h3>Conversation</h3>
        <?php foreach($replies as $reply): 
            $author = get_userdata($reply->user_id);
        ?>
        <div class="message <?php echo $reply->is_internal ? 'internal' : ''; ?>">
            <div class="message-header">
                <span class="message-author">
                    <?php echo $author ? esc_html($author->display_name) : 'Unknown'; ?>
                    <?php if($reply->is_internal): ?><span style="color: #f57c00;"> (Internal Note)</span><?php endif; ?>
                </span>
                <span class="message-time"><?php echo date('Y-m-d H:i', strtotime($reply->created_at)); ?></span>
            </div>
            <div class="message-content"><?php echo nl2br(esc_html($reply->message)); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div class="reply-form">
        <h3>Add Reply</h3>
        <textarea id="replyMessage" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></textarea>
        <div style="margin-top: 10px;">
            <label><input type="checkbox" id="isInternal"> Internal Note (not visible to customer)</label>
        </div>
        <button onclick="addReply()" class="button button-primary" style="margin-top: 10px;">Add Reply</button>
    </div>
    
    <script>
    function changeTicketStatus(status) {
        if(!confirm('Change ticket status to: ' + status + '?')) return;
        
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'b2b_update_ticket_status',
                nonce: '<?php echo wp_create_nonce('b2b_ajax_nonce'); ?>',
                ticket_id: <?php echo $ticket_id; ?>,
                status: status
            },
            success: function(response) {
                if(response.success) {
                    alert('Status updated!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Request failed. Please check your connection and try again.');
                console.error('AJAX Error:', xhr, status, error);
            }
        });
    }
    
    function assignTicket(agentId) {
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'b2b_assign_ticket',
                nonce: '<?php echo wp_create_nonce('b2b_ajax_nonce'); ?>',
                ticket_id: <?php echo $ticket_id; ?>,
                agent_id: agentId
            },
            success: function(response) {
                if(response.success) {
                    alert('Ticket assigned!');
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Request failed. Please check your connection and try again.');
                console.error('AJAX Error:', xhr, status, error);
            }
        });
    }
    
    function addReply() {
        const message = jQuery('#replyMessage').val();
        const isInternal = jQuery('#isInternal').is(':checked') ? 1 : 0;
        
        if(!message) {
            alert('Please enter a message');
            return;
        }
        
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'b2b_add_ticket_reply',
                nonce: '<?php echo wp_create_nonce('b2b_ajax_nonce'); ?>',
                ticket_id: <?php echo $ticket_id; ?>,
                message: message,
                is_internal: isInternal
            },
            success: function(response) {
                if(response.success) {
                    alert('Reply added!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Request failed. Please check your connection and try again.');
                console.error('AJAX Error:', xhr, status, error);
            }
        });
    }
    </script>
    
    <?php
    b2b_adm_footer();
}

// Customer: My Support Tickets Page
function b2b_page_my_support_tickets() {
    $current_user = wp_get_current_user();
    
    global $wpdb;
    $table = $wpdb->prefix . 'b2b_support_tickets';
    
    $status_filter = sanitize_text_field($_GET['status'] ?? '');
    $where = $wpdb->prepare("customer_id = %d", $current_user->ID);
    if($status_filter) {
        $where .= $wpdb->prepare(" AND status = %s", $status_filter);
    }
    
    $tickets = $wpdb->get_results("SELECT * FROM $table WHERE $where ORDER BY created_at DESC");
    
    b2b_adm_header('My Support Tickets');
    ?>
    <style>
    .support-actions { margin-bottom: 20px; }
    .ticket-list { background: white; border-radius: 8px; overflow: hidden; }
    .ticket-item { padding: 20px; border-bottom: 1px solid #eee; }
    .ticket-item:hover { background: #f8f9fa; }
    .ticket-item h3 { margin: 0 0 10px 0; }
    .ticket-meta-inline { font-size: 13px; color: #666; }
    </style>
    
    <div class="support-actions">
        <a href="?b2b_adm_page=create-support-ticket" class="button button-primary">Create New Ticket</a>
        
        <select onchange="window.location.href = '?b2b_adm_page=my-support&status=' + this.value" style="margin-left: 10px;">
            <option value="">All Status</option>
            <option value="new" <?php selected($status_filter, 'new'); ?>>New</option>
            <option value="open" <?php selected($status_filter, 'open'); ?>>Open</option>
            <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
            <option value="resolved" <?php selected($status_filter, 'resolved'); ?>>Resolved</option>
            <option value="closed" <?php selected($status_filter, 'closed'); ?>>Closed</option>
        </select>
    </div>
    
    <div class="ticket-list">
        <?php if(empty($tickets)): ?>
        <div class="ticket-item">No tickets found. <a href="?b2b_adm_page=create-support-ticket">Create your first ticket</a></div>
        <?php else: ?>
        <?php foreach($tickets as $ticket): ?>
        <div class="ticket-item">
            <h3>
                <a href="?b2b_adm_page=view-support-ticket&ticket_id=<?php echo $ticket->ticket_id; ?>">
                    <?php echo esc_html($ticket->subject); ?>
                </a>
            </h3>
            <div class="ticket-meta-inline">
                <span class="status-badge status-<?php echo $ticket->status; ?>"><?php echo ucfirst($ticket->status); ?></span> |
                Ticket: <?php echo esc_html($ticket->ticket_number); ?> |
                Category: <?php echo ucfirst($ticket->category); ?> |
                Priority: <?php echo ucfirst($ticket->priority); ?> |
                Created: <?php echo date('Y-m-d H:i', strtotime($ticket->created_at)); ?>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php
    b2b_adm_footer();
}

// Customer: Create Support Ticket Page
function b2b_page_create_support_ticket() {
    $current_user = wp_get_current_user();
    
    // Get customer's recent orders
    $orders = wc_get_orders([
        'customer_id' => $current_user->ID,
        'limit' => 20,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    b2b_adm_header('Create Support Ticket');
    ?>
    <style>
    .ticket-form { background: white; padding: 30px; border-radius: 8px; max-width: 800px; }
    .form-field { margin-bottom: 20px; }
    .form-field label { display: block; margin-bottom: 8px; font-weight: 600; }
    .form-field input, .form-field select, .form-field textarea {
        width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;
    }
    </style>
    
    <div class="ticket-form">
        <h2>Create New Support Ticket</h2>
        
        <div class="form-field">
            <label>Subject *</label>
            <input type="text" id="ticketSubject" placeholder="Brief description of your issue">
        </div>
        
        <div class="form-field">
            <label>Category *</label>
            <select id="ticketCategory">
                <option value="general">General Question</option>
                <option value="order">Order Issue</option>
                <option value="product">Product Question</option>
                <option value="delivery">Delivery Issue</option>
                <option value="billing">Billing/Payment</option>
            </select>
        </div>
        
        <div class="form-field">
            <label>Priority</label>
            <select id="ticketPriority">
                <option value="normal">Normal</option>
                <option value="low">Low</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
            </select>
        </div>
        
        <div class="form-field">
            <label>Related Order (Optional)</label>
            <select id="ticketOrder">
                <option value="0">No order selected</option>
                <?php foreach($orders as $order): ?>
                <option value="<?php echo $order->get_id(); ?>">
                    Order #<?php echo $order->get_order_number(); ?> - <?php echo $order->get_date_created()->format('Y-m-d'); ?> - <?php echo $order->get_formatted_order_total(); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="form-field">
            <label>Message *</label>
            <textarea id="ticketMessage" rows="8" placeholder="Please describe your issue in detail..."></textarea>
        </div>
        
        <button onclick="createTicket()" class="button button-primary">Submit Ticket</button>
        <a href="?b2b_adm_page=my-support" class="button">Cancel</a>
    </div>
    
    <script>
    function createTicket() {
        const subject = jQuery('#ticketSubject').val();
        const message = jQuery('#ticketMessage').val();
        const category = jQuery('#ticketCategory').val();
        const priority = jQuery('#ticketPriority').val();
        const order_id = jQuery('#ticketOrder').val();
        
        if(!subject || !message) {
            alert('Please fill in all required fields');
            return;
        }
        
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'b2b_create_ticket',
                nonce: '<?php echo wp_create_nonce('b2b_ajax_nonce'); ?>',
                subject: subject,
                message: message,
                category: category,
                priority: priority,
                order_id: order_id
            },
            success: function(response) {
                if(response.success) {
                    alert('Ticket created! Ticket number: ' + response.data.ticket_number);
                    window.location.href = '?b2b_adm_page=my-support';
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Request failed. Please check your connection and try again.');
                console.error('AJAX Error:', xhr, status, error);
            }
        });
    }
    </script>
    
    <?php
    b2b_adm_footer();
}

// Customer: View Support Ticket Page
function b2b_page_view_support_ticket() {
    $current_user = wp_get_current_user();
    $ticket_id = intval($_GET['ticket_id'] ?? 0);
    
    global $wpdb;
    $table_tickets = $wpdb->prefix . 'b2b_support_tickets';
    $table_replies = $wpdb->prefix . 'b2b_support_replies';
    
    $ticket = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_tickets WHERE ticket_id = %d AND customer_id = %d",
        $ticket_id, $current_user->ID
    ));
    
    if(!$ticket) {
        wp_die('Ticket not found or access denied');
    }
    
    // Get only public replies
    $replies = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_replies WHERE ticket_id = %d AND is_internal = 0 ORDER BY created_at ASC",
        $ticket_id
    ));
    
    $order = $ticket->order_id ? wc_get_order($ticket->order_id) : null;
    
    b2b_adm_header('Ticket: ' . $ticket->ticket_number);
    ?>
    <div class="ticket-header">
        <h2><?php echo esc_html($ticket->subject); ?></h2>
        <div class="ticket-meta">
            <div class="ticket-meta-item">
                <label>Ticket Number:</label>
                <value><?php echo esc_html($ticket->ticket_number); ?></value>
            </div>
            <div class="ticket-meta-item">
                <label>Category:</label>
                <value><?php echo ucfirst($ticket->category); ?></value>
            </div>
            <div class="ticket-meta-item">
                <label>Priority:</label>
                <value><?php echo ucfirst($ticket->priority); ?></value>
            </div>
            <div class="ticket-meta-item">
                <label>Status:</label>
                <value><span class="status-badge status-<?php echo $ticket->status; ?>"><?php echo ucfirst($ticket->status); ?></span></value>
            </div>
            <div class="ticket-meta-item">
                <label>Created:</label>
                <value><?php echo date('Y-m-d H:i', strtotime($ticket->created_at)); ?></value>
            </div>
        </div>
    </div>
    
    <?php if($order): ?>
    <div class="order-info">
        <h3>🛒 Related Order: #<?php echo $order->get_order_number(); ?></h3>
        <p><strong>Date:</strong> <?php echo $order->get_date_created()->format('Y-m-d H:i'); ?> | 
           <strong>Status:</strong> <?php echo $order->get_status(); ?> | 
           <strong>Total:</strong> <?php echo $order->get_formatted_order_total(); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="messages-container">
        <h3>Conversation</h3>
        <?php foreach($replies as $reply): 
            $author = get_userdata($reply->user_id);
        ?>
        <div class="message">
            <div class="message-header">
                <span class="message-author"><?php echo $author ? esc_html($author->display_name) : 'Unknown'; ?></span>
                <span class="message-time"><?php echo date('Y-m-d H:i', strtotime($reply->created_at)); ?></span>
            </div>
            <div class="message-content"><?php echo nl2br(esc_html($reply->message)); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php if($ticket->status != 'closed'): ?>
    <div class="reply-form">
        <h3>Add Message</h3>
        <textarea id="replyMessage" rows="5" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;"></textarea>
        <button onclick="addReply()" class="button button-primary" style="margin-top: 10px;">Send Message</button>
    </div>
    
    <script>
    function addReply() {
        const message = jQuery('#replyMessage').val();
        
        if(!message) {
            alert('Please enter a message');
            return;
        }
        
        jQuery.ajax({
            url: ajaxurl,
            method: 'POST',
            data: {
                action: 'b2b_add_ticket_reply',
                nonce: '<?php echo wp_create_nonce('b2b_ajax_nonce'); ?>',
                ticket_id: <?php echo $ticket_id; ?>,
                message: message,
                is_internal: 0
            },
            success: function(response) {
                if(response.success) {
                    alert('Message sent!');
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                alert('Request failed. Please check your connection and try again.');
                console.error('AJAX Error:', xhr, status, error);
            }
        });
    }
    </script>
    <?php endif; ?>
    
    <?php
    b2b_adm_footer();
}

// End of B2B Support Ticket Module - Menu items added directly in sidebar HTML above

/* =====================================================
   SALES AGENT SYSTEM INTEGRATION (V1.0)
===================================================== */

/**
 * PHASE 1: ROLES & ROUTING
 * Add sales agent/manager roles and URL routing
 */

// Add sales agent roles and capabilities in init
add_action('init', function () {
    // Create sales agent roles if they don't exist
    if (!get_role('sales_agent')) {
        add_role('sales_agent', 'Sales Agent', ['read' => true]);
    }
    
    if (!get_role('sales_manager')) {
        add_role('sales_manager', 'Sales Manager', ['read' => true]);
    }

    // Add capabilities to sales roles and administrator
    $roles = ['sales_agent', 'sales_manager', 'administrator'];
    foreach ($roles as $role_name) {
        $role = get_role($role_name);
        if ($role) {
            $role->add_cap('view_sales_panel');
            $role->add_cap('switch_to_customer');
            $role->add_cap('create_sales_order');
        }
    }

    // Add sales panel URL rewrite rules
    add_rewrite_rule('^sales-login/?$', 'index.php?sales_login=1', 'top');
    add_rewrite_rule('^sales-panel/?$', 'index.php?sales_panel=dashboard', 'top');
    add_rewrite_rule('^sales-panel/dashboard/?$', 'index.php?sales_panel=dashboard', 'top');
    add_rewrite_rule('^sales-panel/customers/?$', 'index.php?sales_panel=customers', 'top');
    add_rewrite_rule('^sales-panel/customer/([0-9]+)/?$', 'index.php?sales_panel=customer_detail&customer_id=$matches[1]', 'top');
    add_rewrite_rule('^sales-panel/orders/?$', 'index.php?sales_panel=orders', 'top');
    add_rewrite_rule('^sales-panel/commissions/?$', 'index.php?sales_panel=commissions', 'top');
    add_rewrite_rule('^sales-panel/new-order/([0-9]+)/?$', 'index.php?sales_panel=new-order&customer_id=$matches[1]', 'top');
    add_rewrite_rule('^sales-panel/messaging/?$', 'index.php?sales_panel=messaging', 'top');
    add_rewrite_rule('^sales-panel/notes/?$', 'index.php?sales_panel=notes', 'top');

    // Flush rewrite rules and refresh capabilities once
    if (!get_option('sales_agent_flush_v3_messaging')) {
        flush_rewrite_rules();
        
        // Force refresh of role capabilities
        $roles_to_update = ['sales_agent', 'sales_manager', 'administrator'];
        foreach ($roles_to_update as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                // Remove and re-add to ensure capability is properly set
                $role->remove_cap('view_sales_panel');
                $role->remove_cap('switch_to_customer');
                $role->remove_cap('create_sales_order');
                
                $role->add_cap('view_sales_panel');
                $role->add_cap('switch_to_customer');
                $role->add_cap('create_sales_order');
            }
        }
        
        update_option('sales_agent_flush_v3_messaging', true);
        delete_option('sales_agent_flush_v2'); // Clean up old marker
        delete_option('sales_agent_flush_v1'); // Clean up old marker
    }
}, 20); // Run after main b2b panel init

// Add query vars for sales panel
add_filter('query_vars', function ($vars) {
    $vars[] = 'sales_login';
    $vars[] = 'sales_panel';
    $vars[] = 'customer_id';
    return $vars;
}, 20);

// Role-based redirect logic
add_action('template_redirect', function () {
    $sales_login = get_query_var('sales_login');
    $sales_panel = get_query_var('sales_panel');
    
    // If accessing sales panel pages
    if ($sales_login || $sales_panel) {
        // Check if user has sales panel access
        if (!current_user_can('view_sales_panel')) {
            // Redirect non-authorized users to sales login
            if (!$sales_login) {
                wp_redirect(home_url('/sales-login'));
                exit;
            }
        }
        
        // Handle order creation form submission
        if ($sales_panel === 'new-order' && isset($_POST['sa_create_order'])) {
            $customer_id = intval($_POST['customer_id']);
            
            // Verify nonce
            if (!wp_verify_nonce($_POST['sa_order_nonce'], 'sa_create_order_' . $customer_id)) {
                wp_die('Security check failed');
            }
            
            // Verify agent has access
            $agent = wp_get_current_user();
            if (!current_user_can('administrator')) {
                $assigned_agent = get_user_meta($customer_id, 'bagli_agent_id', true);
                $allow_manager = get_option('sales_manager_can_order', 0);
                
                // Check if user is sales manager and setting is enabled
                if (in_array('sales_manager', $agent->roles) && $allow_manager) {
                    // Sales manager can access if setting is enabled
                } else if ($assigned_agent != $agent->ID) {
                    // Not the assigned agent and not an authorized manager
                    wp_die('Access denied to this customer');
                }
            }
            
            // Process order creation
            $products = $_POST['products'] ?? [];
            $qtys = $_POST['qtys'] ?? [];
            $prices = $_POST['prices'] ?? [];
            $assemblies = $_POST['assembly_selected'] ?? [];
            $assembly_costs = $_POST['assembly_costs'] ?? [];
            $fee_amount = floatval($_POST['extra_fee'] ?? 0);
            $fee_name = sanitize_text_field($_POST['extra_fee_name'] ?? 'Service Fee');
            $payment_method = sanitize_text_field($_POST['payment_method'] ?? 'bacs');
            $order_note = sanitize_textarea_field($_POST['order_note'] ?? '');
            $shipping_cost = floatval($_POST['shipping_cost'] ?? 0);
            $po_number = sanitize_text_field($_POST['po_number'] ?? '');
            
            if (!empty($products)) {
                // Switch to customer context for pricing
                $old_user = get_current_user_id();
                wp_set_current_user($customer_id);
                
                // Create order
                $order = wc_create_order(['customer_id' => $customer_id]);
                $cust = new WC_Customer($customer_id);
                
                // Set addresses
                $billing = [
                    'first_name' => $cust->get_billing_first_name() ?: $cust->get_first_name(),
                    'last_name' => $cust->get_billing_last_name() ?: $cust->get_last_name(),
                    'email' => $cust->get_billing_email() ?: $cust->get_email(),
                    'phone' => $cust->get_billing_phone(),
                    'address_1' => $cust->get_billing_address_1(),
                    'city' => $cust->get_billing_city(),
                    'state' => $cust->get_billing_state(),
                    'postcode' => $cust->get_billing_postcode(),
                    'country' => $cust->get_billing_country()
                ];
                $order->set_address($billing, 'billing');
                $order->set_address($billing, 'shipping');
                
                // Set agent meta
                $order->update_meta_data('_sales_agent_id', $agent->ID);
                $order->update_meta_data('_sales_agent_name', $agent->display_name);
                
                if ($po_number) {
                    $order->update_meta_data('billing_business_name', $po_number);
                }
                
                if ($order_note) {
                    $order->set_customer_note($order_note);
                }
                
                $order->set_payment_method($payment_method);
                
                // Add products
                $total_assembly_fee = 0;
                foreach ($products as $k => $pid) {
                    $qty = intval($qtys[$k]);
                    $unit_price = isset($prices[$k]) ? floatval($prices[$k]) : 0;
                    
                    if ($qty > 0 && $pid && $unit_price > 0) {
                        $prod = wc_get_product($pid);
                        $item_id = $order->add_product($prod, $qty, [
                            'subtotal' => $unit_price * $qty,
                            'total' => $unit_price * $qty
                        ]);
                        
                        $item = $order->get_item($item_id);
                        if ($item) {
                            $item->set_subtotal($unit_price * $qty);
                            $item->set_total($unit_price * $qty);
                            $item->save();
                        }
                        
                        // Check assembly
                        if (isset($assemblies[$k]) && $assemblies[$k] == 1) {
                            $cost_per_item = floatval($assembly_costs[$k]);
                            $total_assembly_fee += ($cost_per_item * $qty);
                        }
                    }
                }
                
                // Add assembly fee
                if ($total_assembly_fee > 0) {
                    $fee_assembly = new WC_Order_Item_Fee();
                    $fee_assembly->set_name('Assembly Fee');
                    $fee_assembly->set_amount($total_assembly_fee);
                    $fee_assembly->set_total($total_assembly_fee);
                    $order->add_item($fee_assembly);
                }
                
                // Add extra fee
                if ($fee_amount > 0) {
                    $item_fee = new WC_Order_Item_Fee();
                    $item_fee->set_name($fee_name);
                    $item_fee->set_amount($fee_amount);
                    $item_fee->set_total($fee_amount);
                    $order->add_item($item_fee);
                }
                
                // Add shipping
                if ($shipping_cost > 0) {
                    $item_ship = new WC_Order_Item_Shipping();
                    $item_ship->set_method_title('Manual Shipping');
                    $item_ship->set_total($shipping_cost);
                    $order->add_item($item_ship);
                }
                
                // Calculate totals and save
                $order->calculate_totals();
                $order->update_status('pending', 'Created by Agent: ' . $agent->display_name);
                $order->save();
                
                // Switch back to agent
                wp_set_current_user($old_user);
                
                // Redirect to orders page
                wp_redirect(home_url('/sales-panel/orders'));
                exit;
            }
        }
        
        // Route to appropriate page
        if ($sales_login) {
            sa_render_login_page();
            exit;
        }
        
        if ($sales_panel) {
            switch ($sales_panel) {
                case 'dashboard':
                    sa_render_dashboard_page();
                    break;
                case 'customers':
                    sa_render_customers_page();
                    break;
                case 'customer_detail':
                    sa_render_customer_detail_page();
                    break;
                case 'orders':
                    sa_render_orders_page();
                    break;
                case 'commissions':
                    sa_render_commissions_page();
                    break;
                case 'new-order':
                    sa_render_new_order_page();
                    break;
                case 'messaging':
                    sa_render_messaging_page();
                    break;
                case 'notes':
                    sa_render_notes_page();
                    break;
                default:
                    sa_render_dashboard_page();
            }
            exit;
        }
    }
}, 25);

// Redirect sales agents to their panel (not admin dashboard)
add_action('admin_init', function () {
    if (defined('DOING_AJAX') && DOING_AJAX) return;
    
    $user = wp_get_current_user();
    $roles = (array) $user->roles;
    
    // If user is sales agent or sales manager (but not admin), redirect to sales panel
    if ((in_array('sales_agent', $roles) || in_array('sales_manager', $roles)) 
        && !in_array('administrator', $roles)) {
        wp_redirect(home_url('/sales-panel'));
        exit;
    }
});

// Hide admin bar for sales agents
add_action('after_setup_theme', function () {
    $user = wp_get_current_user();
    $roles = (array) $user->roles;
    
    if ((in_array('sales_agent', $roles) || in_array('sales_manager', $roles)) 
        && !in_array('administrator', $roles)) {
        show_admin_bar(false);
    }
});

/**
 * PHASE 3: SALES PANEL PAGES - HELPER FUNCTIONS
 */

// Helper: Get safe home URL
function get_home_url_safe($path = '') {
    return untrailingslashit(get_option('home')) . $path;
}

// Helper: Get full address for user
function sa_get_full_address($uid, $type = 'billing') {
    $addr1 = get_user_meta($uid, $type.'_address_1', true);
    $addr2 = get_user_meta($uid, $type.'_address_2', true);
    $city  = get_user_meta($uid, $type.'_city', true);
    $state = get_user_meta($uid, $type.'_state', true);
    $post  = get_user_meta($uid, $type.'_postcode', true);
    $country = get_user_meta($uid, $type.'_country', true);
    $full = []; 
    if($addr1) $full[] = $addr1; 
    if($addr2) $full[] = $addr2;
    if($city || $state || $post) $full[] = trim("$city $state $post");
    if($country) $full[] = $country;
    return !empty($full) ? implode('<br>', $full) : '<span style="color:#94a3b8;font-style:italic">Not Set</span>';
}

// Helper: Get refund IDs for an order
function sa_get_refund_ids($parent_order_id) {
    global $wpdb; 
    return $wpdb->get_col($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'shop_order_refund' AND post_parent = %d", $parent_order_id)) ?: [];
}

// Helper: Get refund item totals
function sa_get_refund_item_totals($refund_id) {
    global $wpdb;
    $results = $wpdb->get_results($wpdb->prepare("SELECT meta_key, meta_value FROM {$wpdb->prefix}woocommerce_order_itemmeta WHERE order_item_id IN (SELECT order_item_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_id = %d)", $refund_id), ARRAY_A);
    $subtotal = 0; 
    foreach ($results as $meta) { 
        if ($meta['meta_key'] === '_line_subtotal') $subtotal += floatval($meta['meta_value']); 
    }
    return ['subtotal' => $subtotal];
}

// Helper: Get dashboard summary for agent
function sa_get_dashboard_summary($agent_id, $alert_days) {
    global $wpdb;
    
    // Get agent's customers
    $customer_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bagli_agent_id' AND meta_value = %d",
        $agent_id
    ));
    
    if (empty($customer_ids)) {
        return [
            'total_customers' => 0,
            'total_orders' => 0,
            'total_sales' => 0,
            'stale_customers' => 0,
            'recent_orders' => []
        ];
    }
    
    // Get orders for these customers
    $placeholders = implode(',', array_fill(0, count($customer_ids), '%d'));
    $orders = wc_get_orders([
        'customer' => $customer_ids,
        'limit' => -1,
        'status' => ['completed', 'processing']
    ]);
    
    $total_sales = 0;
    foreach ($orders as $order) {
        $total_sales += $order->get_total();
    }
    
    // Find stale customers (no orders in X days)
    $stale_date = date('Y-m-d H:i:s', strtotime("-{$alert_days} days"));
    $stale_count = 0;
    
    foreach ($customer_ids as $cust_id) {
        $last_order = wc_get_orders([
            'customer' => $cust_id,
            'limit' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);
        
        if (empty($last_order) || $last_order[0]->get_date_created() < new DateTime($stale_date)) {
            $stale_count++;
        }
    }
    
    return [
        'total_customers' => count($customer_ids),
        'total_orders' => count($orders),
        'total_sales' => $total_sales,
        'stale_customers' => $stale_count,
        'recent_orders' => array_slice($orders, 0, 10)
    ];
}

/**
 * PHASE 3: SALES PANEL PAGE RENDERERS
 */

function sa_render_login_page() {
    // Handle login
    if (isset($_POST['sa_login'])) {
        $creds = [
            'user_login' => sanitize_text_field($_POST['log']),
            'user_password' => $_POST['pwd'],
            'remember' => true
        ];
        
        $user = wp_signon($creds, is_ssl());
        
        if (!is_wp_error($user)) {
            wp_redirect(home_url('/sales-panel'));
            exit;
        } else {
            $err = 'Invalid username or password.';
        }
    }
    
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Agent Login | Sales Panel</title>
        <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;500;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root {
                --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
                --primary: #10b981; /* Sales Green */
                --glass: rgba(255, 255, 255, 0.05);
                --border: rgba(255, 255, 255, 0.1);
                --text: #ffffff;
                --text-muted: #94a3b8;
            }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            
            body {
                font-family: 'Outfit', sans-serif;
                background: var(--bg-gradient);
                color: var(--text);
                height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                overflow: hidden;
                position: relative;
            }

            /* Background FX */
            .bg-shape {
                position: absolute;
                border-radius: 50%;
                filter: blur(80px);
                z-index: -1;
                opacity: 0.4;
            }
            .shape-1 { width: 300px; height: 300px; background: var(--primary); top: -50px; left: -50px; }
            .shape-2 { width: 250px; height: 250px; background: #059669; bottom: -50px; right: -50px; }

            /* Login Card */
            .login-card {
                background: var(--glass);
                border: 1px solid var(--border);
                padding: 40px 30px;
                border-radius: 20px;
                width: 100%;
                max-width: 360px;
                backdrop-filter: blur(10px);
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
                text-align: center;
            }

            .icon-box {
                width: 60px;
                height: 60px;
                background: rgba(16, 185, 129, 0.1);
                color: var(--primary);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 24px;
                margin: 0 auto 20px;
                border: 1px solid rgba(16, 185, 129, 0.3);
            }

            h2 { font-size: 1.5rem; margin-bottom: 5px; font-weight: 700; }
            p.sub { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 30px; }

            /* Inputs */
            .input-group { margin-bottom: 15px; text-align: left; }
            label { display: block; color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px; margin-left: 5px;}
            
            input {
                width: 100%;
                padding: 12px 15px;
                background: rgba(0, 0, 0, 0.2);
                border: 1px solid var(--border);
                border-radius: 10px;
                color: #fff;
                font-family: inherit;
                font-size: 0.95rem;
                transition: 0.3s;
            }
            input:focus {
                outline: none;
                border-color: var(--primary);
                background: rgba(0, 0, 0, 0.3);
                box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
            }
            input::placeholder { color: rgba(255, 255, 255, 0.3); }

            /* Button */
            button {
                width: 100%;
                padding: 12px;
                margin-top: 10px;
                background: var(--primary);
                color: #fff;
                border: none;
                border-radius: 10px;
                font-weight: 600;
                font-size: 1rem;
                cursor: pointer;
                transition: 0.3s;
                font-family: inherit;
            }
            button:hover {
                background: #059669;
                box-shadow: 0 0 20px rgba(16, 185, 129, 0.4);
            }

            .error-msg {
                background: rgba(239, 68, 68, 0.1);
                color: #fca5a5;
                padding: 10px;
                border-radius: 8px;
                font-size: 0.85rem;
                margin-bottom: 20px;
                border: 1px solid rgba(239, 68, 68, 0.2);
            }
        </style>
    </head>
    <body>

        <div class="bg-shape shape-1"></div>
        <div class="bg-shape shape-2"></div>

        <form method="post" class="login-card">
            <input type="hidden" name="sa_login" value="1">
            
            <div class="icon-box">
                <i class="fa-solid fa-chart-pie"></i>
            </div>
            <h2>Agent Portal</h2>
            <p class="sub">Log in to track your sales and customers.</p>

            <?php if(isset($err)): ?>
                <div class="error-msg"><i class="fa-solid fa-circle-exclamation"></i> <?= $err ?></div>
            <?php endif; ?>

            <div class="input-group">
                <label>Username / Email</label>
                <input type="text" name="log" placeholder="agent@company.com" required autocomplete="off">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="pwd" placeholder="••••••••" required>
            </div>

            <button type="submit">Login <i class="fa-solid fa-arrow-right" style="margin-left:5px"></i></button>
        </form>

    </body>
    </html>
    <?php
}

function sa_render_dashboard_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $user = wp_get_current_user();
    $is_manager = in_array('sales_manager', $user->roles);
    $alert_days = get_option('sales_stale_days', 15);
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    
    // Get dashboard data
    $summary = sa_get_dashboard_summary($user->ID, $alert_days);
    
    // Sales Panel with Sidebar Navigation
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= esc_html($panel_title) ?> - Dashboard</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
            }
            .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
            .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
            .stat-card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            .stat-card h3 { color: #6b7280; font-size: 14px; margin-bottom: 10px; text-transform: uppercase; }
            .stat-card .value { font-size: 32px; font-weight: 700; color: #1f2937; }
            .stat-card.warning { border-left: 4px solid #f59e0b; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            th { background: #f9fafb; font-weight: 600; font-size: 13px; text-transform: uppercase; color: #6b7280; }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>

        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <div class="stats">
                <div class="stat-card">
                    <h3>Total Customers</h3>
                    <div class="value"><?= $summary['total_customers'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Orders</h3>
                    <div class="value"><?= $summary['total_orders'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Sales</h3>
                    <div class="value"><?= wc_price($summary['total_sales']) ?></div>
                </div>
                <div class="stat-card warning">
                    <h3>⚠️ Stale Customers</h3>
                    <div class="value"><?= $summary['stale_customers'] ?></div>
                </div>
            </div>
            
            <div class="card">
                <h2>Recent Orders</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($summary['recent_orders'] as $order): 
                            $customer = get_userdata($order->get_customer_id());
                        ?>
                        <tr>
                            <td>#<?= $order->get_id() ?></td>
                            <td><?= $customer ? esc_html($customer->display_name) : 'Guest' ?></td>
                            <td><?= $order->get_date_created()->format('Y-m-d') ?></td>
                            <td><?= $order->get_formatted_order_total() ?></td>
                            <td><?= ucfirst($order->get_status()) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function sa_render_customers_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $user = wp_get_current_user();
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    $is_manager = in_array('sales_manager', (array) $user->roles);
    
    // Get agent's customers
    global $wpdb;
    $agent_id = $user->ID;
    $view_all_customers = get_option('sales_view_all_customers', 0);
    
    // If sales manager with "View All Customers" setting enabled
    if ($is_manager && $view_all_customers) {
        // Get all users with customer role
        $customers = get_users(['role' => 'customer']);
        $customer_ids = wp_list_pluck($customers, 'ID');
    } else if ($is_manager) {
        // Get all sales agents who have this manager assigned
        $agent_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bagli_manager_id' AND meta_value = %d",
            $agent_id
        ));
        
        // If there are subordinate agents, get their customers
        if (!empty($agent_ids)) {
            $placeholders = implode(',', array_fill(0, count($agent_ids), '%d'));
            $customer_ids = $wpdb->get_col($wpdb->prepare(
                "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bagli_agent_id' AND meta_value IN ($placeholders)",
                ...$agent_ids
            ));
        } else {
            $customer_ids = [];
        }
    } else {
        // Regular agent - get only their own customers
        $customer_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bagli_agent_id' AND meta_value = %d",
            $agent_id
        ));
    }
    
    // Get final customers list if not already set
    if (!isset($customers)) {
        $customers = [];
        if (!empty($customer_ids)) {
            $customers = get_users(['include' => $customer_ids]);
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= esc_html($panel_title) ?> - Customers</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
                .table-responsive { overflow-x: auto; }
            }
            .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            th { background: #f9fafb; font-weight: 600; font-size: 13px; text-transform: uppercase; color: #6b7280; }
            .btn { padding: 10px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 14px; transition: 0.2s; }
            .btn:hover { opacity: 0.9; }
            .btn-primary { background: var(--primary); color: #fff; }
            .btn-success { background: #10b981; color: #fff; }
            .btn-warning { background: #f59e0b; color: #fff; }
            .btn-light { background: #e5e7eb; color: #374151; }
            .col-toggle { position: relative; display: inline-block; }
            .col-toggle-btn { background: #fff; border: 1px solid #d1d5db; padding: 8px 12px; border-radius: 6px; cursor: pointer; }
            .col-dropdown { display: none; position: absolute; top: 100%; right: 0; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; padding: 10px; min-width: 200px; z-index: 10; }
            .col-dropdown.show { display: block; }
            .col-dropdown label { display: block; padding: 5px 0; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>

        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>" class="active"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:15px;">
                    <h2 style="margin:0;">My Customers</h2>
                    <div style="position:relative;">
                        <button class="btn btn-light" onclick="toggleColumnDropdown()" style="padding:8px 12px;">
                            <i class="fa-solid fa-columns"></i> Columns
                        </button>
                        <div id="columnDropdown" style="display:none;position:absolute;top:100%;right:0;background:white;border:1px solid #e5e7eb;box-shadow:0 4px 6px rgba(0,0,0,0.1);border-radius:8px;padding:10px;min-width:150px;z-index:10;margin-top:5px;">
                            <label style="display:block;padding:5px 0;cursor:pointer;font-size:14px;">
                                <input type="checkbox" checked onchange="toggleColumn('col-email', this)"> Email
                            </label>
                            <label style="display:block;padding:5px 0;cursor:pointer;font-size:14px;">
                                <input type="checkbox" checked onchange="toggleColumn('col-phone', this)"> Phone
                            </label>
                            <label style="display:block;padding:5px 0;cursor:pointer;font-size:14px;">
                                <input type="checkbox" checked onchange="toggleColumn('col-company', this)"> Company
                            </label>
                            <?php if ($is_manager): ?>
                            <label style="display:block;padding:5px 0;cursor:pointer;font-size:14px;">
                                <input type="checkbox" checked onchange="toggleColumn('col-agent', this)"> Assigned Agent
                            </label>
                            <?php endif; ?>
                            <label style="display:block;padding:5px 0;cursor:pointer;font-size:14px;">
                                <input type="checkbox" checked onchange="toggleColumn('col-spent', this)"> Total Spent
                            </label>
                        </div>
                    </div>
                </div>
                <?php if (empty($customers)): ?>
                    <p style="color: #6b7280; padding: 20px; text-align: center;">No customers assigned yet.</p>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th class="col-email">Email</th>
                            <th class="col-phone">Phone</th>
                            <th class="col-company">Company</th>
                            <?php if ($is_manager): ?>
                            <th class="col-agent">Assigned Agent</th>
                            <?php endif; ?>
                            <th class="col-spent">Total Spent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($customers as $customer): 
                            $phone = get_user_meta($customer->ID, 'billing_phone', true);
                            $company = get_user_meta($customer->ID, 'billing_company', true);
                            $spent = wc_get_customer_total_spent($customer->ID);
                            $assigned_agent_id = get_user_meta($customer->ID, 'bagli_agent_id', true);
                            $agent_name = $assigned_agent_id ? get_userdata($assigned_agent_id)->display_name : '-';
                            $order_url = home_url('/sales-panel/new-order/' . $customer->ID);
                            $switch_url = wp_nonce_url(add_query_arg('switch_customer', $customer->ID, home_url()), 'switch_customer');
                        ?>
                        <tr>
                            <td>
                                <a href="<?= home_url('/sales-panel/customer/' . $customer->ID) ?>" style="color:#1f2937;text-decoration:none;display:inline-flex;align-items:center;">
                                    <i class="fa-solid fa-magnifying-glass" style="color:#9ca3af;margin-right:8px;"></i>
                                    <strong><?= esc_html($customer->display_name) ?></strong>
                                </a>
                            </td>
                            <td class="col-email"><?= esc_html($customer->user_email) ?></td>
                            <td class="col-phone"><?= $phone ? esc_html($phone) : '-' ?></td>
                            <td class="col-company"><?= $company ? esc_html($company) : '-' ?></td>
                            <?php if ($is_manager): ?>
                            <td class="col-agent"><strong style="color:#4f46e5;"><?= esc_html($agent_name) ?></strong></td>
                            <?php endif; ?>
                            <td class="col-spent"><strong><?= wc_price($spent) ?></strong></td>
                            <td style="white-space:nowrap;">
                                <a href="<?= $order_url ?>" class="btn" title="Create Order" style="background:#10b981;margin-right:5px;">
                                    <i class="fa-solid fa-plus"></i> Order
                                </a>
                                <button class="btn btn-warning" onclick="openUnpaidModal(<?= $customer->ID ?>)" title="View Unpaid Orders" style="margin-right:5px;">
                                    <i class="fa-solid fa-file-invoice-dollar"></i> Unpaid
                                </button>
                                <a href="<?= $switch_url ?>" class="btn" title="Login as Customer" style="background:#6366f1;">
                                    <i class="fa-solid fa-right-to-bracket"></i> Login
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Unpaid Orders Modal -->
        <div id="unpaidModal" style="display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);">
            <div style="background:white;margin:5% auto;padding:20px;width:90%;max-width:700px;border-radius:12px;position:relative;">
                <span onclick="document.getElementById('unpaidModal').style.display='none'" style="position:absolute;right:20px;top:20px;font-size:28px;font-weight:bold;color:#999;cursor:pointer;">&times;</span>
                <h2 style="margin:0 0 20px 0;">Unpaid Orders</h2>
                <div id="unpaid-body">Loading...</div>
            </div>
        </div>
        
        <script>
        function toggleColumnDropdown() {
            const dropdown = document.getElementById('columnDropdown');
            dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
        }
        
        function toggleColumn(className, checkbox) {
            const elements = document.getElementsByClassName(className);
            for (let el of elements) {
                el.style.display = checkbox.checked ? '' : 'none';
            }
        }
        
        function openUnpaidModal(customerId) {
            document.getElementById('unpaidModal').style.display = 'block';
            document.getElementById('unpaid-body').innerHTML = 'Loading...';
            
            fetch('<?= admin_url('admin-ajax.php') ?>?action=sa_get_unpaid_orders&customer_id=' + customerId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('unpaid-body').innerHTML = data.data;
                    } else {
                        document.getElementById('unpaid-body').innerHTML = 'Error loading data.';
                    }
                })
                .catch(error => {
                    document.getElementById('unpaid-body').innerHTML = 'Error: ' + error;
                });
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('unpaidModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdown = document.getElementById('columnDropdown');
            const target = event.target;
            if (!target.closest('.btn-light') && dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        });
        </script>
    </body>
    </html>
    <?php
}

function sa_render_customer_detail_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $customer_id = intval(get_query_var('customer_id'));
    $customer = get_userdata($customer_id);
    
    if (!$customer) {
        wp_die('Customer not found');
    }
    
    // Verify agent has access to this customer
    $user = wp_get_current_user();
    if (!current_user_can('administrator')) {
        $assigned_agent = get_user_meta($customer_id, 'bagli_agent_id', true);
        $allow_manager = get_option('sales_manager_can_order', 0);
        
        // Check if user is sales manager and setting is enabled
        if (in_array('sales_manager', $user->roles) && $allow_manager) {
            // Sales manager can access if setting is enabled
        } else if ($assigned_agent != $user->ID) {
            // Not the assigned agent and not an authorized manager
            wp_die('Access denied to this customer');
        }
    }
    
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    
    // Get customer orders
    $orders = wc_get_orders([
        'customer_id' => $customer_id,
        'limit' => 20,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= esc_html($panel_title) ?> - Customer Detail</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
            }
            .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
            .card h2 { margin-bottom: 20px; color: #1f2937; }
            .info-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; }
            .info-item { padding: 10px 0; border-bottom: 1px solid #e5e7eb; }
            .info-item label { font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; }
            .info-item div { margin-top: 5px; color: #1f2937; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            th { background: #f9fafb; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; }
            .btn { display: inline-block; padding: 8px 16px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; }
            .btn:hover { background: #059669; }
            .btn-secondary { background: #6b7280; }
            .btn-secondary:hover { background: #4b5563; }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>

        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>" class="active"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <div style="margin-bottom: 20px;">
                <a href="<?= home_url('/sales-panel/customers') ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Customers</a>
            </div>
            
            <div class="card">
                <h2>Customer Information</h2>
                <div class="info-grid">
                    <div class="info-item">
                        <label>Name</label>
                        <div><?= esc_html($customer->display_name) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Email</label>
                        <div><?= esc_html($customer->user_email) ?></div>
                    </div>
                    <div class="info-item">
                        <label>Phone</label>
                        <div><?= esc_html(get_user_meta($customer_id, 'billing_phone', true) ?: '-') ?></div>
                    </div>
                    <div class="info-item">
                        <label>Company</label>
                        <div><?= esc_html(get_user_meta($customer_id, 'billing_company', true) ?: '-') ?></div>
                    </div>
                </div>
                
                <div style="margin-top: 20px;">
                    <a href="<?= home_url('/sales-panel/new-order/' . $customer_id) ?>" class="btn"><i class="fa-solid fa-plus"></i> Create New Order</a>
                </div>
            </div>
            
            <div class="card">
                <h2>Recent Orders</h2>
                <?php if (empty($orders)): ?>
                    <p style="color: #6b7280; padding: 20px; text-align: center;">No orders yet.</p>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Date</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?= $order->get_id() ?></td>
                            <td><?= $order->get_date_created()->format('Y-m-d H:i') ?></td>
                            <td><?= $order->get_formatted_order_total() ?></td>
                            <td><?= ucfirst($order->get_status()) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function sa_render_orders_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $user = wp_get_current_user();
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    
    // Get agent's customers
    global $wpdb;
    $agent_id = $user->ID;
    $customer_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'bagli_agent_id' AND meta_value = %d",
        $agent_id
    ));
    
    // Filters
    $paged = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $per_page = 20;
    $filters = [
        'date_after' => $_GET['start_date'] ?? '',
        'date_before' => $_GET['end_date'] ?? '',
        'customer' => intval($_GET['filter_customer'] ?? 0)
    ];
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    
    // Build query
    $query_ids = !empty($customer_ids) ? $customer_ids : [0];
    if ($filters['customer'] && in_array($filters['customer'], $customer_ids)) {
        $query_ids = [$filters['customer']];
    }
    
    $args = [
        'customer' => $query_ids,
        'limit' => $per_page,
        'page' => $paged,
        'paginate' => true,
        'orderby' => 'date',
        'order' => 'DESC'
    ];
    
    if ($filters['date_after']) $args['date_after'] = $filters['date_after'];
    if ($filters['date_before']) $args['date_before'] = $filters['date_before'];
    if ($status_filter) $args['status'] = $status_filter;
    
    $results = wc_get_orders($args);
    $orders = $results->orders;
    $total_pages = $results->max_num_pages;
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= esc_html($panel_title) ?> - Orders</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
                .table-responsive { overflow-x: auto; }
            }
            .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            th { background: #f9fafb; font-weight: 600; font-size: 13px; text-transform: uppercase; color: #6b7280; }
            .btn { padding: 10px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 14px; transition: 0.2s; }
            .btn:hover { opacity: 0.9; }
            .btn-primary { background: var(--primary); color: #fff; }
            .btn-light { background: #e5e7eb; color: #374151; }
            .btn-warning { background: #f59e0b; color: #fff; }
            .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; text-transform: uppercase; }
            .badge.completed { background: #dcfce7; color: #166534; }
            .badge.processing { background: #dbeafe; color: #1e40af; }
            .badge.pending { background: #fef9c3; color: #854d0e; }
            .badge.on-hold { background: #fef3c7; color: #92400e; }
            .badge.cancelled { background: #fee2e2; color: #991b1b; }
            .badge.failed { background: #fee2e2; color: #991b1b; }
            .filters-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; background: #f9fafb; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
            .form-group { flex: 1; min-width: 150px; }
            .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
            .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; }
            .pagination { margin-top: 20px; display: flex; gap: 5px; justify-content: center; }
            .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; text-decoration: none; color: #333; }
            .pagination span.current { background: var(--primary); color: #fff; border-color: var(--primary); }
            .col-toggle { position: relative; display: inline-block; }
            .col-toggle-btn { background: #fff; border: 1px solid #d1d5db; padding: 8px 12px; border-radius: 6px; cursor: pointer; }
            .col-dropdown { display: none; position: absolute; top: 100%; right: 0; background: #fff; border: 1px solid #e5e7eb; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); border-radius: 8px; padding: 10px; min-width: 200px; z-index: 10; margin-top: 5px; }
            .col-dropdown.show { display: block; }
            .col-dropdown label { display: block; padding: 5px 0; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>

        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>" class="active"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <div class="card">
                <form method="get" class="filters-form">
                    <input type="hidden" name="sales_panel" value="orders">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= esc_attr($filters['date_after']) ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?= esc_attr($filters['date_before']) ?>">
                    </div>
                    <div class="form-group">
                        <label>Customer</label>
                        <select name="filter_customer">
                            <option value="">All Customers</option>
                            <?php foreach ($customer_ids as $cid): 
                                $c = get_userdata($cid);
                                if ($c):
                            ?>
                                <option value="<?= $cid ?>" <?= selected($filters['customer'], $cid, false) ?>><?= esc_html($c->display_name) ?></option>
                            <?php endif; endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status">
                            <option value="">All Statuses</option>
                            <?php foreach (wc_get_order_statuses() as $key => $label): ?>
                                <option value="<?= esc_attr(str_replace('wc-', '', $key)) ?>" <?= selected($status_filter, str_replace('wc-', '', $key), false) ?>><?= esc_html($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn" style="height: 42px;">Filter</button>
                </form>
            </div>
            
            <div class="card">
                <div style="display:flex;justify-content:space-between;margin-bottom:10px">
                    <h2 style="margin:0;">Orders</h2>
                    <div class="col-toggle">
                        <button class="col-toggle-btn" onclick="document.querySelector('.col-dropdown').classList.toggle('show')">
                            Columns <i class="fa fa-caret-down"></i>
                        </button>
                        <div class="col-dropdown">
                            <label><input type="checkbox" checked onchange="toggleColumn('col-date', this)"> Date</label>
                            <label><input type="checkbox" checked onchange="toggleColumn('col-po', this)"> PO Number</label>
                            <label><input type="checkbox" checked onchange="toggleColumn('col-note', this)"> Note</label>
                            <label><input type="checkbox" checked onchange="toggleColumn('col-status', this)"> Status</label>
                        </div>
                    </div>
                </div>
                <?php if (empty($orders)): ?>
                    <p style="color: #6b7280; padding: 20px; text-align: center;">No orders found.</p>
                <?php else: ?>
                <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th class="col-date">Date</th>
                            <th>Customer</th>
                            <th class="col-po">PO Number</th>
                            <th class="col-note">Note</th>
                            <th class="col-status">Status</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            $customer = get_userdata($order->get_customer_id());
                            $po_number = $order->get_meta('billing_business_name') ?: '-';
                            $note = $order->get_customer_note();
                            $note_display = $note ? (mb_strlen($note) > 30 ? mb_substr($note, 0, 30) . '...' : $note) : '-';
                            
                            // PDF packing slip link
                            $pdf_link = '';
                            if (class_exists('WPO_WCPDF')) {
                                $nonce = wp_create_nonce('generate_wpo_wcpdf');
                                $pdf_url = admin_url("admin-ajax.php?action=generate_wpo_wcpdf&document_type=packing-slip&order_ids={$order->get_id()}&_wpnonce={$nonce}");
                                $pdf_link = '<a href="' . esc_url($pdf_url) . '" class="btn btn-warning" target="_blank" style="padding:8px;margin-left:5px" title="Packing Slip"><i class="fa-solid fa-file-pdf"></i></a>';
                            }
                        ?>
                        <tr>
                            <td><strong>#<?= $order->get_id() ?></strong></td>
                            <td class="col-date"><?= $order->get_date_created()->format('d.m.Y') ?></td>
                            <td><?= $customer ? esc_html($customer->display_name) : 'Guest' ?></td>
                            <td class="col-po"><?= esc_html($po_number) ?></td>
                            <td class="col-note" title="<?= esc_attr($note) ?>"><?= esc_html($note_display) ?></td>
                            <td class="col-status"><span class="badge <?= $order->get_status() ?>"><?= ucfirst($order->get_status()) ?></span></td>
                            <td><strong><?= $order->get_formatted_order_total() ?></strong></td>
                            <td style="white-space:nowrap;">
                                <button class="btn btn-light" onclick="openOrderModal(<?= $order->get_id() ?>)" title="View Details">
                                    <i class="fa-regular fa-eye"></i> View
                                </button>
                                <?= $pdf_link ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($paged > 1): ?>
                        <a href="?sales_panel=orders&paged=<?= $paged - 1 ?>&start_date=<?= urlencode($filters['date_after']) ?>&end_date=<?= urlencode($filters['date_before']) ?>&filter_customer=<?= $filters['customer'] ?>&status=<?= urlencode($status_filter) ?>">Prev</a>
                    <?php endif; ?>
                    <span class="current">Page <?= $paged ?> of <?= $total_pages ?></span>
                    <?php if ($paged < $total_pages): ?>
                        <a href="?sales_panel=orders&paged=<?= $paged + 1 ?>&start_date=<?= urlencode($filters['date_after']) ?>&end_date=<?= urlencode($filters['date_before']) ?>&filter_customer=<?= $filters['customer'] ?>&status=<?= urlencode($status_filter) ?>">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Order Details Modal -->
        <div id="orderModal" style="display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);">
            <div style="background:white;margin:5% auto;padding:20px;width:90%;max-width:800px;border-radius:12px;position:relative;">
                <span onclick="document.getElementById('orderModal').style.display='none'" style="position:absolute;right:20px;top:20px;font-size:28px;font-weight:bold;color:#999;cursor:pointer;">&times;</span>
                <h2 id="modal-title" style="margin:0 0 20px 0;">Order Details</h2>
                <div id="modal-body">Loading...</div>
            </div>
        </div>
        
        <script>
        function toggleColumn(className, checkbox) {
            const elements = document.getElementsByClassName(className);
            for (let el of elements) {
                el.style.display = checkbox.checked ? '' : 'none';
            }
        }
        
        function openOrderModal(orderId) {
            document.getElementById('orderModal').style.display = 'block';
            document.getElementById('modal-body').innerHTML = 'Loading...';
            
            fetch('<?= admin_url('admin-ajax.php') ?>?action=sa_get_order_details&order_id=' + orderId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const d = data.data;
                        let items = '<table style="width:100%;border-collapse:collapse;margin-top:10px"><tr><th>Item</th><th>Qty</th><th>Total</th></tr>';
                        d.items.forEach(i => items += `<tr><td>${i.name}</td><td>${i.qty}</td><td>${i.total}</td></tr>`);
                        items += '</table>';
                        
                        document.getElementById('modal-body').innerHTML = `
                            <div><strong>PO Number:</strong> ${d.po}</div>
                            <div><strong>Status:</strong> ${d.status} <span style="float:right">${d.date}</span></div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:10px">
                                <div style="background:#f9fafb;padding:10px;font-size:12px"><strong>Billing:</strong><br>${d.billing}</div>
                                <div style="background:#f9fafb;padding:10px;font-size:12px"><strong>Shipping:</strong><br>${d.shipping}</div>
                            </div>
                            ${items}
                            <h3 style="text-align:right;margin-top:10px">${d.total}</h3>
                            ${d.notes ? '<div style="background:#fffbeb;padding:10px;margin-top:10px;font-style:italic">Note: ' + d.notes + '</div>' : ''}
                        `;
                    }
                })
                .catch(error => {
                    document.getElementById('modal-body').innerHTML = 'Error loading order details.';
                });
        }
        
        window.onclick = function(event) {
            const modal = document.getElementById('orderModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
        
        document.addEventListener('click', function(event) {
            const dropdown = document.querySelector('.col-dropdown');
            const button = document.querySelector('.col-toggle-btn');
            if (dropdown && button && !event.target.closest('.col-toggle')) {
                dropdown.classList.remove('show');
            }
        });
        </script>
    </body>
    </html>
    <?php
}

function sa_render_commissions_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $user = wp_get_current_user();
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    $agent_id = $user->ID;
    
    // Filters
    $start_date = $_GET['start_date'] ?? '2020-01-01';
    $end_date = $_GET['end_date'] ?? date('Y-m-d');
    $excluded = $_GET['exclude_status'] ?? [];
    $paged_comm = isset($_GET['paged']) ? absint($_GET['paged']) : 1;
    $per_page = 20;
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= esc_html($panel_title) ?> - Reports</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
                .report-grid { grid-template-columns: 1fr !important; }
            }
            .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
            .card h3 { margin-bottom: 20px; color: #1f2937; font-size: 20px; }
            .report-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 25px; }
            .report-card { padding: 20px; border-radius: 12px; color: #fff; text-align: center; }
            .report-card strong { font-size: 14px; display: block; margin-bottom: 10px; opacity: 0.9; }
            .report-card div { font-size: 24px; font-weight: bold; margin-top: 5px; }
            .bg-gross { background: #4f46e5; }
            .bg-refund { background: #f59e0b; }
            .bg-net { background: #10b981; }
            .bg-comm { background: #ec4899; }
            .filters-form { display: flex; gap: 15px; align-items: flex-end; flex-wrap: wrap; background: #f9fafb; padding: 20px; border-radius: 12px; margin-bottom: 20px; }
            .form-group { flex: 1; min-width: 150px; }
            .form-group label { display: block; margin-bottom: 5px; font-size: 13px; font-weight: 600; }
            .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; }
            .btn { padding: 10px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 14px; transition: 0.2s; background: var(--primary); color: #fff; }
            .btn:hover { opacity: 0.9; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            th { background: #f9fafb; font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; }
            .pagination { margin-top: 20px; display: flex; gap: 5px; justify-content: center; }
            .pagination a, .pagination span { padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 4px; text-decoration: none; color: #333; }
            .pagination span.current { background: var(--primary); color: #fff; border-color: var(--primary); }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>

        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>" class="active"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <div class="card">
                <h3>Reports</h3>
                <form method="get" class="filters-form">
                    <input type="hidden" name="sales_panel" value="commissions">
                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="<?= esc_attr($start_date) ?>">
                    </div>
                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="<?= esc_attr($end_date) ?>">
                    </div>
                    <div class="form-group">
                        <label>Exclude Status</label>
                        <select name="exclude_status[]" multiple style="height:42px">
                            <?php foreach (wc_get_order_statuses() as $k => $v): ?>
                                <option value="<?= esc_attr($k) ?>" <?= in_array($k, $excluded) ? 'selected' : '' ?>><?= esc_html($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button class="btn" style="height:42px">Generate</button>
                </form>
            </div>
            
            <?php
            // Get agent's customers
            global $wpdb;
            $agent_customers = get_users(['meta_key' => 'bagli_agent_id', 'meta_value' => $agent_id, 'fields' => 'ID']);
            $customer_ids = !empty($agent_customers) ? $agent_customers : [0];
            
            // Build query args
            $base_args = [
                'post_type' => 'shop_order',
                'post_status' => array_diff(array_keys(wc_get_order_statuses()), $excluded),
                'date_query' => [[
                    'after' => $start_date . ' 00:00:00',
                    'before' => $end_date . ' 23:59:59',
                    'inclusive' => true
                ]],
                'meta_query' => [
                    'relation' => 'OR',
                    ['key' => '_sales_agent_id', 'value' => $agent_id],
                    ['key' => '_customer_user', 'value' => $customer_ids, 'compare' => 'IN']
                ],
                'fields' => 'ids',
                'posts_per_page' => -1
            ];
            
            $all_ids = get_posts($base_args);
            $gross = 0;
            $refund = 0;
            $net = 0;
            $comm = 0;
            $rate = (float) get_option('sales_commission_rate', 3);
            
            // Calculate totals
            foreach ($all_ids as $oid) {
                $o = wc_get_order($oid);
                if (!$o) continue;
                
                $gross += $o->get_total();
                $i_sub = floatval($o->get_subtotal());
                $r_sub = 0;
                
                foreach (sa_get_refund_ids($oid) as $rid) {
                    $r_sub += abs(floatval(sa_get_refund_item_totals($rid)['subtotal']));
                }
                
                $n_item = max(0, $i_sub - $r_sub);
                $refund += $o->get_total_refunded();
                $net += $n_item;
                $comm += ($n_item * ($rate / 100));
            }
            ?>
            
            <div class="report-grid">
                <div class="report-card bg-gross">
                    <strong>Gross Sales</strong>
                    <div><?= wc_price($gross) ?></div>
                </div>
                <div class="report-card bg-refund">
                    <strong>Refunds</strong>
                    <div><?= wc_price($refund) ?></div>
                </div>
                <div class="report-card bg-net">
                    <strong>Net Item Subtotal</strong>
                    <div><?= wc_price($net) ?></div>
                </div>
                <div class="report-card bg-comm">
                    <strong>Commission (<?= $rate ?>%)</strong>
                    <div><?= wc_price($comm) ?></div>
                </div>
            </div>
            
            <?php
            $total_orders = count($all_ids);
            $max_pages_comm = ceil($total_orders / $per_page);
            $paged_ids = array_slice($all_ids, ($paged_comm - 1) * $per_page, $per_page);
            
            if ($paged_ids):
            ?>
            <div class="card">
                <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Status</th>
                            <th>Gross</th>
                            <th>Refund</th>
                            <th>Net Item</th>
                            <th>Comm</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($paged_ids as $oid):
                            $o = wc_get_order($oid);
                            $i_sub = floatval($o->get_subtotal());
                            $r_sub = 0;
                            
                            foreach (sa_get_refund_ids($oid) as $rid) {
                                $r_data = sa_get_refund_item_totals($rid);
                                $r_sub += abs(floatval($r_data['subtotal']));
                            }
                            
                            $n_item = max(0, $i_sub - $r_sub);
                            $row_comm = $n_item * ($rate / 100);
                            $c_name = $o->get_billing_first_name() . ' ' . $o->get_billing_last_name();
                        ?>
                        <tr>
                            <td>#<?= $o->get_id() ?></td>
                            <td><?= $o->get_date_created()->date('d.m.Y') ?></td>
                            <td><?= esc_html($c_name) ?></td>
                            <td><?= ucfirst($o->get_status()) ?></td>
                            <td><?= $o->get_formatted_order_total() ?></td>
                            <td style="color:#dc2626"><?= wc_price($o->get_total_refunded()) ?></td>
                            <td><?= wc_price($n_item) ?></td>
                            <td style="font-weight:bold;color:#ec4899"><?= wc_price($row_comm) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                
                <?php if ($max_pages_comm > 1): ?>
                <div class="pagination">
                    <?php if ($paged_comm > 1): ?>
                        <a href="?sales_panel=commissions&paged=<?= $paged_comm - 1 ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">Prev</a>
                    <?php endif; ?>
                    <span class="current">Page <?= $paged_comm ?> / <?= $max_pages_comm ?></span>
                    <?php if ($paged_comm < $max_pages_comm): ?>
                        <a href="?sales_panel=commissions&paged=<?= $paged_comm + 1 ?>&start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>">Next</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="card">
                <p style="color: #6b7280; padding: 20px; text-align: center;">No records found.</p>
            </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

// Helper function: Get formatted address
function sa_render_new_order_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $customer_id = intval(get_query_var('customer_id'));
    $customer = get_userdata($customer_id);
    
    if (!$customer) {
        wp_die('Customer not found');
    }
    
    // Verify agent has access to this customer
    $user = wp_get_current_user();
    if (!current_user_can('administrator')) {
        $assigned_agent = get_user_meta($customer_id, 'bagli_agent_id', true);
        $allow_manager = get_option('sales_manager_can_order', 0);
        
        // Check if user is sales manager and setting is enabled
        if (in_array('sales_manager', $user->roles) && $allow_manager) {
            // Sales manager can access if setting is enabled
        } else if ($assigned_agent != $user->ID) {
            // Not the assigned agent and not an authorized manager
            wp_die('Access denied to this customer');
        }
    }
    
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    $wc_cust = new WC_Customer($customer_id);
    $b_addr = sa_get_full_address($customer_id, 'billing');
    $s_addr = sa_get_full_address($customer_id, 'shipping');
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= esc_html($panel_title) ?> - New Order</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
            }
            .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
            .card h2 { margin-bottom: 20px; color: #1f2937; }
            .btn { display: inline-block; padding: 8px 16px; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 14px; border: none; cursor: pointer; }
            .btn:hover { background: #059669; }
            .btn-secondary { background: #6b7280; }
            .btn-secondary:hover { background: #4b5563; }
            .btn-danger { background: #ef4444; color: white; }
            .btn-warning { background: #f59e0b; color: white; }
            .btn-light { background: #e5e7eb; color: #374151; }
            .customer-widgets { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; margin: 20px 0; }
            .widget-card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 15px; }
            .widget-card h3 { margin: 0 0 8px 0; font-size: 11px; text-transform: uppercase; color: #6b7280; font-weight: 700; }
            table { width: 100%; border-collapse: collapse; }
            th, td { padding: 12px; text-align: left; border-bottom: 1px solid #e5e7eb; }
            th { background: #f9fafb; font-weight: 600; font-size: 13px; }
            .order-table input { width: 80px; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px; }
            .totals-area { display: flex; justify-content: flex-end; margin-top: 20px; }
            .totals-box { width: 350px; background: #f9fafb; padding: 20px; border-radius: 8px; }
            .total-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 14px; }
            .total-row.final { font-weight: 700; font-size: 18px; border-top: 1px solid #d1d5db; padding-top: 10px; margin-top: 10px; color: #4f46e5; }
            .select2-container .select2-selection--single { height: 38px; border-color: #d1d5db; display: flex; align-items: center; }
            textarea { width: 100%; border: 1px solid #d1d5db; border-radius: 6px; padding: 10px; }
            input[type="text"], input[type="number"], select { padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>

        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>" class="active"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <div style="margin-bottom: 20px;">
                <a href="<?= home_url('/sales-panel/customer/' . $customer_id) ?>" class="btn btn-secondary"><i class="fa-solid fa-arrow-left"></i> Back to Customer</a>
            </div>
            
            <?php
            // Show sales manager indicator if applicable
            $current_user = wp_get_current_user();
            $assigned_agent_id = get_user_meta($customer_id, 'bagli_agent_id', true);
            if (in_array('sales_manager', $current_user->roles) && $assigned_agent_id != $current_user->ID):
                $assigned_agent = get_userdata($assigned_agent_id);
            ?>
            <div style="background:#eff6ff;border-left:4px solid #3b82f6;padding:12px 15px;margin-bottom:20px;border-radius:6px;">
                <i class="fa-solid fa-info-circle" style="color:#3b82f6;margin-right:8px;"></i>
                <strong style="color:#1e40af;">Sales Manager View:</strong> 
                <span style="color:#475569;">Creating order for customer assigned to <strong><?= esc_html($assigned_agent->display_name ?? 'Unknown Agent') ?></strong></span>
            </div>
            <?php endif; ?>
            
            <div class="card">
                <h2><i class="fa-solid fa-cart-shopping"></i> New Order: <?= esc_html($customer->display_name) ?></h2>
                <div class="customer-widgets">
                    <div class="widget-card"><h3>Customer</h3><strong><?= $customer->display_name ?></strong><br><small><?= $customer->user_email ?></small></div>
                    <div class="widget-card"><h3>Billing Address</h3><?= $b_addr ?></div>
                    <div class="widget-card"><h3>Shipping Address</h3><?= $s_addr ?></div>
                </div>
                
                <form method="post" id="orderForm" action="">
                    <input type="hidden" name="sa_create_order" value="1">
                    <input type="hidden" name="customer_id" id="customer_id" value="<?= $customer_id ?>">
                    <?php wp_nonce_field('sa_create_order_' . $customer_id, 'sa_order_nonce'); ?>
                    
                    <div style="margin-bottom:20px;">
                        <label style="font-weight:600;display:block;margin-bottom:5px">Job Name (PO Number)</label>
                        <input type="text" name="po_number" class="form-control" placeholder="Enter PO Number" style="width:100%">
                    </div>
                    
                    <div class="table-responsive">
                        <table class="order-table">
                            <thead>
                                <tr>
                                    <th style="min-width:300px">Product</th>
                                    <th style="width:120px">Unit Price</th>
                                    <th style="width:80px">Qty</th>
                                    <th style="width:100px">Assembly</th>
                                    <th style="width:120px">Total</th>
                                    <th style="width:50px"></th>
                                </tr>
                            </thead>
                            <tbody id="product-rows"></tbody>
                        </table>
                    </div>
                    
                    <div style="margin-top:15px;display:flex;justify-content:space-between;align-items:center;">
                        <button type="button" class="btn btn-light" id="add-row"><i class="fa-solid fa-plus"></i> Add Product</button>
                        <button type="button" class="btn btn-warning" id="toggle-global-assembly"><i class="fa-solid fa-tools"></i> Apply Assembly to All</button>
                    </div>

                    <div class="totals-area">
                        <div class="totals-box">
                            <div class="total-row"><span>Subtotal:</span><span id="subtotal">0.00</span></div>
                            <div class="total-row" style="color:#e11d48;font-weight:600"><span>Assembly Fee:</span><span id="assembly_display">0.00</span></div>
                            <div class="total-row" style="align-items:center">
                                <span>Shipping:</span>
                                <input type="number" name="shipping_cost" id="shipping_cost" step="0.01" value="0" style="width:80px;text-align:right">
                            </div>
                            <div class="total-row" style="align-items:center">
                                <span>Extra Fee:</span>
                                <input type="number" name="extra_fee" id="extra_fee" step="0.01" value="0" style="width:80px;text-align:right">
                            </div>
                            <div class="total-row">
                                <input type="text" name="extra_fee_name" value="Service Fee" placeholder="Fee Name" style="width:100%;font-size:12px;padding:5px">
                            </div>
                            <div class="total-row final"><span>Grand Total:</span><span id="grandtotal">0.00</span></div>
                            <hr style="margin:15px 0;border:0;border-top:1px solid #ddd">
                            <div style="margin-bottom:10px">
                                <label style="font-size:12px;font-weight:600">Payment Method</label>
                                <select name="payment_method" style="width:100%;padding:8px">
                                    <?php foreach($gateways as $id => $gateway): ?>
                                        <option value="<?= esc_attr($id) ?>"><?= esc_html($gateway->get_title()) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="margin-bottom:10px">
                                <label style="font-size:12px;font-weight:600">Order Note</label>
                                <textarea name="order_note" rows="2"></textarea>
                            </div>
                            <button class="btn btn-primary" style="width:100%;justify-content:center"><i class="fa-solid fa-check"></i> Create Order</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            const customerId = $('#customer_id').val();
            const mergeEnabled = '<?= get_option('sales_merge_products') ? 1 : 0 ?>';

            function initSelect2(el) {
                $(el).select2({
                    ajax: {
                        url: '<?= admin_url('admin-ajax.php') ?>',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                action: 'sa_search_products',
                                term: params.term,
                                customer_id: customerId
                            };
                        },
                        processResults: function(data) {
                            return { results: data };
                        }
                    },
                    placeholder: 'Search product...',
                    minimumInputLength: 2
                });
            }

            $('#add-row').click(function() {
                let html = `<tr class="item-row">
                    <td><select name="products[]" class="product-search" style="width:100%" required><option value="">Search...</option></select></td>
                    <td><input type="text" name="prices[]" class="price-display" style="width:100px" value="0.00"></td>
                    <td><input type="number" name="qtys[]" class="item-qty" value="1" min="1"></td>
                    <td style="text-align:center">
                        <input type="hidden" name="assembly_costs[]" class="assembly-cost" value="0">
                        <input type="checkbox" name="assembly_selected[]" value="1" class="assembly-check" disabled>
                        <span class="assembly-label" style="font-size:11px;display:block"></span>
                    </td>
                    <td class="item-total">0.00</td>
                    <td><button type="button" class="btn btn-danger remove-row" style="padding:5px 10px"><i class="fa-solid fa-trash"></i></button></td>
                </tr>`;
                $('#product-rows').append(html);
                initSelect2($('#product-rows tr:last .product-search'));
            });

            $(document).on('select2:select', '.product-search', function(e) {
                let d = e.params.data;
                let currentRow = $(this).closest('tr');
                
                // Merge products if enabled
                if (mergeEnabled === '1') {
                    let found = false;
                    $('.product-search').not($(this)).each(function() {
                        if ($(this).val() == d.id) {
                            let targetRow = $(this).closest('tr');
                            let oldQty = parseInt(targetRow.find('.item-qty').val()) || 1;
                            targetRow.find('.item-qty').val(oldQty + 1).trigger('input');
                            currentRow.remove();
                            found = true;
                            return false;
                        }
                    });
                    if (found) return;
                }
                
                currentRow.find('.price-display').val(parseFloat(d.price).toFixed(2));
                let check = currentRow.find('.assembly-check');
                let label = currentRow.find('.assembly-label');
                let costInput = currentRow.find('.assembly-cost');
                
                if(d.has_assembly) {
                    check.prop('disabled', false);
                    costInput.val(d.assembly_price);
                    label.text('+$' + d.assembly_price);
                } else {
                    check.prop('disabled', true).prop('checked', false);
                    costInput.val(0);
                    label.text('-');
                }
                calcRow(currentRow);
            });
            
            $('#toggle-global-assembly').click(function() {
                let allChecked = $('.assembly-check:not(:disabled):checked').length === $('.assembly-check:not(:disabled)').length;
                $('.assembly-check:not(:disabled)').prop('checked', !allChecked);
                calcTotals();
            });

            $(document).on('input', '.item-qty, #extra_fee, #shipping_cost, .price-display, .assembly-check', function() {
                calcTotals();
            });
            
            $(document).on('change keyup', '.item-qty, .price-display', function() {
                calcRow($(this).closest('tr'));
            });
            
            $(document).on('click', '.remove-row', function() {
                $(this).closest('tr').remove();
                calcTotals();
            });

            // Add first row on load
            $('#add-row').click();

            function calcRow(row) {
                let p = parseFloat(row.find('.price-display').val()) || 0;
                let q = parseInt(row.find('.item-qty').val()) || 1;
                let assemblyCost = 0;
                if(row.find('.assembly-check').is(':checked')) {
                    assemblyCost = parseFloat(row.find('.assembly-cost').val()) || 0;
                }
                let total = (p * q) + (assemblyCost * q);
                row.find('.item-total').text(total.toFixed(2));
                calcTotals();
            }

            function calcTotals() {
                let subtotal = 0, totalAssembly = 0;
                $('.item-row').each(function() {
                    let p = parseFloat($(this).find('.price-display').val()) || 0;
                    let q = parseInt($(this).find('.item-qty').val()) || 1;
                    let a = 0;
                    if($(this).find('.assembly-check').is(':checked')) {
                        a = parseFloat($(this).find('.assembly-cost').val()) || 0;
                    }
                    subtotal += (p * q);
                    totalAssembly += (a * q);
                });
                let f = parseFloat($('#extra_fee').val()) || 0;
                let sh = parseFloat($('#shipping_cost').val()) || 0;
                $('#subtotal').text(subtotal.toFixed(2));
                $('#assembly_display').text(totalAssembly.toFixed(2));
                $('#grandtotal').text((subtotal + totalAssembly + f + sh).toFixed(2));
            }
        });
        </script>
    </div>
    </body>
    </html>
    <?php
}

/**
 * PHASE 2: SETTINGS INTEGRATION
 * Add sales agent settings page in admin panel
 */

// Settings Page Template Redirect
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'settings_sales_agent') return;
    b2b_adm_guard();
    
    // Handle settings save
    $message = '';
    if(isset($_POST['save_sales_settings'])) {
        update_option('sales_panel_enabled', isset($_POST['sales_panel_enabled']) ? 1 : 0);
        update_option('sales_panel_title', sanitize_text_field($_POST['sales_panel_title']));
        update_option('sales_commission_rate', floatval($_POST['sales_commission_rate']));
        update_option('sales_stale_days', intval($_POST['sales_stale_days']));
        update_option('sales_merge_products', isset($_POST['sales_merge_products']) ? 1 : 0);
        $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> Sales Agent settings saved.</div>';
    }
    
    $panel_enabled = get_option('sales_panel_enabled', 1);
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    $commission_rate = get_option('sales_commission_rate', 3);
    $stale_days = get_option('sales_stale_days', 15);
    $merge_products = get_option('sales_merge_products', 0);
    
    b2b_adm_header('Sales Agent Settings');
    ?>
    <div class="page-header"><h1 class="page-title">Sales Agent System Settings</h1></div>
    
    <?= $message ?>
    
    <div class="card">
        <form method="post" style="max-width:700px;">
            <h3 style="margin-top:0;color:#1e40af;"><i class="fa-solid fa-toggle-on"></i> General</h3>
            
            <div style="margin-bottom:25px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="sales_panel_enabled" value="1" <?= checked($panel_enabled, 1) ?>>
                    <span style="font-weight:600;">Enable Sales Agent Panel</span>
                </label>
                <small style="color:#6b7280;margin-left:30px;">Allow sales agents and managers to access their dedicated panel</small>
            </div>
            
            <div style="margin-bottom:25px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Panel Title</label>
                <input type="text" name="sales_panel_title" value="<?= esc_attr($panel_title) ?>" style="width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:6px;" placeholder="Agent Panel">
                <small style="color:#6b7280;">Displayed in the sales panel header</small>
            </div>
            
            <hr style="margin:30px 0;border:none;border-top:1px solid #e5e7eb;">
            
            <h3 style="color:#1e40af;"><i class="fa-solid fa-percent"></i> Commission & Sales</h3>
            
            <div style="margin-bottom:25px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Commission Rate (%)</label>
                <input type="number" step="0.01" min="0" max="100" name="sales_commission_rate" value="<?= esc_attr($commission_rate) ?>" style="width:200px;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                <small style="color:#6b7280;">Default commission percentage for sales agents</small>
            </div>
            
            <div style="margin-bottom:25px;">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Stale Customer Alert (Days)</label>
                <input type="number" min="1" max="365" name="sales_stale_days" value="<?= esc_attr($stale_days) ?>" style="width:200px;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                <small style="color:#6b7280;">Show alert if customer hasn't placed order in this many days</small>
            </div>
            
            <hr style="margin:30px 0;border:none;border-top:1px solid #e5e7eb;">
            
            <h3 style="color:#1e40af;"><i class="fa-solid fa-sliders"></i> Advanced</h3>
            
            <div style="margin-bottom:25px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="sales_merge_products" value="1" <?= checked($merge_products, 1) ?>>
                    <span style="font-weight:600;">Merge Duplicate Products</span>
                </label>
                <small style="color:#6b7280;margin-left:30px;">Automatically combine duplicate product entries in order creation</small>
            </div>
            
            <div style="padding:15px;background:#f0f9ff;border:1px solid #bfdbfe;border-radius:8px;margin-bottom:20px;">
                <h4 style="margin-top:0;color:#1e40af;"><i class="fa-solid fa-info-circle"></i> About Sales Agent System</h4>
                <ul style="color:#1e40af;margin:0;">
                    <li><strong>Sales Agents:</strong> Can view their assigned customers, create orders, and track commissions</li>
                    <li><strong>Sales Managers:</strong> Can view all agents and their performance metrics</li>
                    <li><strong>Role Assignment:</strong> Assign roles to users via WordPress Users page</li>
                    <li><strong>Customer Assignment:</strong> Link customers to agents in user profile</li>
                </ul>
            </div>
            
            <div style="padding:15px;background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;margin-bottom:20px;">
                <h4 style="margin-top:0;color:#92400e;"><i class="fa-solid fa-lightbulb"></i> Quick Start</h4>
                <ol style="color:#92400e;margin:0;">
                    <li>Enable the sales panel above</li>
                    <li>Go to Users → Add New to create sales agents</li>
                    <li>Assign "Sales Agent" or "Sales Manager" role</li>
                    <li>Link customers to agents in user profiles</li>
                    <li>Agents can login at <code>/sales-login</code></li>
                </ol>
            </div>
            
            <button type="submit" name="save_sales_settings" style="background:#10b981;color:white;padding:12px 24px;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;">
                <i class="fa-solid fa-save"></i> Save Settings
            </button>
        </form>
    </div>
    <?php b2b_adm_footer(); exit;
});

// End of Sales Agent System Phase 2

/* =====================================================
   PAYMENT GATEWAY MODULE: NMI Gateway
===================================================== */

/**
 * WooCommerce NMI Payment Gateway
 * Simple implementation for processing payments, refunds, and logging
 */

// Register the payment gateway
add_filter('woocommerce_payment_gateways', 'add_nmi_gateway_class');
function add_nmi_gateway_class($gateways) {
    $gateways[] = 'WC_NMI_Gateway';
    return $gateways;
}

// Initialize gateway class after plugins are loaded
add_action('plugins_loaded', 'init_nmi_gateway_class');
function init_nmi_gateway_class() {
    if (!class_exists('WC_Payment_Gateway')) return;
    
    class WC_NMI_Gateway extends WC_Payment_Gateway {
        public function __construct() {
            $this->id = 'nmi_gateway';
            $this->method_title = 'NMI Gateway';
            $this->method_description = 'Accept credit card payments via NMI (Network Merchants Inc.)';
            $this->has_fields = true;
            $this->supports = array('products', 'refunds');
            
            // Load settings
            $this->init_form_fields();
            $this->init_settings();
            
            // Define user settings
            $this->enabled = $this->get_option('enabled');
            $this->title = $this->get_option('title');
            $this->description = $this->get_option('description');
            $this->api_username = $this->get_option('api_username');
            $this->api_password = $this->get_option('api_password');
            $this->test_mode = 'yes' === $this->get_option('test_mode');
            $this->capture_mode = $this->get_option('capture_mode', 'sale');
            $this->debug_mode = 'yes' === $this->get_option('debug_mode');
            $this->allowed_card_types = $this->get_option('allowed_card_types', array('visa', 'mastercard', 'amex', 'discover'));
            
            // Save settings
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        }
        
        /**
         * Log debug messages
         */
        private function log($message) {
            if ($this->debug_mode && function_exists('wc_get_logger')) {
                $logger = wc_get_logger();
                $logger->debug($message, array('source' => 'nmi-gateway'));
            }
        }
        
        public function init_form_fields() {
            $this->form_fields = array(
                'enabled' => array(
                    'title' => 'Enable/Disable',
                    'type' => 'checkbox',
                    'label' => 'Enable NMI Gateway',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => 'Title',
                    'type' => 'text',
                    'description' => 'Payment method title shown to customers.',
                    'default' => 'Credit Card (NMI)',
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => 'Description',
                    'type' => 'textarea',
                    'description' => 'Payment method description shown to customers.',
                    'default' => 'Pay securely with your credit card.',
                    'desc_tip' => true
                ),
                'test_mode' => array(
                    'title' => 'Test Mode',
                    'type' => 'checkbox',
                    'label' => 'Enable test mode',
                    'default' => 'yes',
                    'description' => 'Use test API credentials for testing.'
                ),
                'api_username' => array(
                    'title' => 'API Username',
                    'type' => 'text',
                    'description' => 'Your NMI API username (Security Key).',
                    'default' => '',
                    'desc_tip' => true
                ),
                'api_password' => array(
                    'title' => 'API Password',
                    'type' => 'password',
                    'description' => 'Your NMI API password (optional, if required).',
                    'default' => '',
                    'desc_tip' => true
                ),
                'capture_mode' => array(
                    'title' => 'Capture Mode',
                    'type' => 'select',
                    'description' => 'Choose when to capture payment.',
                    'default' => 'sale',
                    'options' => array(
                        'sale' => 'Authorize & Capture (Immediate)',
                        'auth' => 'Authorize Only (Manual Capture Required)'
                    ),
                    'desc_tip' => true
                ),
                'debug_mode' => array(
                    'title' => 'Debug Logging',
                    'type' => 'checkbox',
                    'label' => 'Enable debug logging',
                    'default' => 'no',
                    'description' => 'Log gateway requests and responses for troubleshooting. <strong>WARNING:</strong> This will log sensitive data including card numbers. Only enable temporarily for debugging.'
                ),
                'allowed_card_types' => array(
                    'title' => 'Allowed Card Types',
                    'type' => 'select',
                    'class' => 'wc-enhanced-select',
                    'css' => 'min-width:300px;',
                    'description' => 'Select which card types to accept.',
                    'default' => array('visa', 'mastercard', 'amex', 'discover'),
                    'options' => array(
                        'visa' => 'Visa',
                        'mastercard' => 'MasterCard',
                        'amex' => 'American Express',
                        'discover' => 'Discover',
                        'diners' => 'Diners Club',
                        'jcb' => 'JCB'
                    ),
                    'desc_tip' => true,
                    'custom_attributes' => array(
                        'multiple' => 'multiple'
                    )
                )
            );
        }
        
        public function payment_fields() {
            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }
            ?>
            <fieldset style="border:1px solid #e5e7eb;padding:15px;border-radius:8px;background:#f9fafb;">
                <p class="form-row form-row-wide">
                    <label>Card Number <span class="required">*</span></label>
                    <input type="text" name="nmi_card_number" maxlength="16" placeholder="1234 5678 9012 3456" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" />
                </p>
                <p class="form-row form-row-first">
                    <label>Expiry Date (MM/YY) <span class="required">*</span></label>
                    <input type="text" name="nmi_card_expiry" maxlength="5" placeholder="12/25" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" />
                </p>
                <p class="form-row form-row-last">
                    <label>CVV <span class="required">*</span></label>
                    <input type="text" name="nmi_card_cvv" maxlength="4" placeholder="123" style="width:100%;padding:10px;border:1px solid #ddd;border-radius:4px;" />
                </p>
                <div style="clear:both;"></div>
            </fieldset>
            <?php
        }
        
        public function validate_fields() {
            // Validate card number
            if (empty($_POST['nmi_card_number'])) {
                wc_add_notice('Card number is required', 'error');
                return false;
            }
            
            $card_number = preg_replace('/\s+/', '', $_POST['nmi_card_number']);
            if (!preg_match('/^\d{13,19}$/', $card_number)) {
                wc_add_notice('Invalid card number format. Must be 13-19 digits.', 'error');
                return false;
            }
            
            // Validate expiry date
            if (empty($_POST['nmi_card_expiry'])) {
                wc_add_notice('Card expiry date is required', 'error');
                return false;
            }
            
            $expiry = sanitize_text_field($_POST['nmi_card_expiry']);
            if (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) {
                wc_add_notice('Invalid expiry date format. Use MM/YY.', 'error');
                return false;
            }
            
            // Check if date is in the future
            list($month, $year) = explode('/', $expiry);
            $exp_date = strtotime('20' . $year . '-' . $month . '-01');
            if ($exp_date < strtotime('first day of this month')) {
                wc_add_notice('Card has expired.', 'error');
                return false;
            }
            
            // Validate CVV
            if (empty($_POST['nmi_card_cvv'])) {
                wc_add_notice('CVV is required', 'error');
                return false;
            }
            
            $cvv = sanitize_text_field($_POST['nmi_card_cvv']);
            if (!preg_match('/^\d{3,4}$/', $cvv)) {
                wc_add_notice('Invalid CVV. Must be 3 or 4 digits.', 'error');
                return false;
            }
            
            return true;
        }
        
        public function process_payment($order_id) {
            $order = wc_get_order($order_id);
            
            // Verify nonce for CSRF protection
            if (!isset($_POST['woocommerce-process-checkout-nonce']) || 
                !wp_verify_nonce($_POST['woocommerce-process-checkout-nonce'], 'woocommerce-process_checkout')) {
                wc_add_notice('Security verification failed', 'error');
                return array('result' => 'fail');
            }
            
            // Get card details (already validated in validate_fields)
            $card_number = preg_replace('/\s+/', '', sanitize_text_field($_POST['nmi_card_number']));
            $card_expiry = sanitize_text_field($_POST['nmi_card_expiry']);
            $card_cvv = sanitize_text_field($_POST['nmi_card_cvv']);
            
            // Prepare API request
            $amount = $order->get_total();
            $response = $this->process_nmi_payment($order, $card_number, $card_expiry, $card_cvv, $amount);
            
            if ($response['success']) {
                // Payment successful
                $order->payment_complete($response['transaction_id']);
                $order->add_order_note(sprintf('NMI Payment completed. Transaction ID: %s', $response['transaction_id']));
                
                // Log transaction
                $this->log_transaction($order_id, 'payment', $response['transaction_id'], $amount, 'completed', $response['raw_response']);
                
                // Empty cart
                WC()->cart->empty_cart();
                
                return array(
                    'result' => 'success',
                    'redirect' => $this->get_return_url($order)
                );
            } else {
                // Payment failed
                wc_add_notice('Payment failed: ' . $response['error'], 'error');
                $order->add_order_note('NMI Payment failed: ' . $response['error']);
                
                // Log failed transaction
                $this->log_transaction($order_id, 'payment', '', $amount, 'failed', $response['raw_response']);
                
                return array('result' => 'fail');
            }
        }
        
        private function process_nmi_payment($order, $card_number, $card_expiry, $card_cvv, $amount) {
            // Parse expiry date (already validated as MM/YY format)
            list($exp_month, $exp_year) = explode('/', $card_expiry);
            
            // Convert YY to YYYY (handle years correctly)
            if (strlen($exp_year) === 2) {
                $exp_year = '20' . $exp_year;
            }
            
            // Build API request - NMI uses same endpoint for both test and production
            // Test mode is distinguished by the security key used
            $api_url = 'https://secure.nmi.com/api/transact.php';
            
            $post_data = array(
                'security_key' => $this->api_username,
                'type' => $this->capture_mode, // Use configured capture mode (sale or auth)
                'ccnumber' => str_replace(' ', '', $card_number),
                'ccexp' => $exp_month . $exp_year,
                'cvv' => $card_cvv,
                'amount' => number_format($amount, 2, '.', ''),
                'firstname' => $order->get_billing_first_name(),
                'lastname' => $order->get_billing_last_name(),
                'address1' => $order->get_billing_address_1(),
                'city' => $order->get_billing_city(),
                'state' => $order->get_billing_state(),
                'zip' => $order->get_billing_postcode(),
                'country' => $order->get_billing_country(),
                'email' => $order->get_billing_email(),
                'orderid' => $order->get_order_number()
            );
            
            // Log request if debug mode enabled
            if ($this->debug_mode) {
                $log_data = $post_data;
                // Mask card number - show only last 4 digits if long enough
                if (isset($log_data['ccnumber']) && strlen($log_data['ccnumber']) >= 4) {
                    $log_data['ccnumber'] = '****' . substr($log_data['ccnumber'], -4);
                } else {
                    $log_data['ccnumber'] = '****';
                }
                $log_data['cvv'] = '***';
                $this->log('NMI Payment Request: ' . json_encode($log_data));
            }
            
            // Send API request
            $response = wp_remote_post($api_url, array(
                'body' => $post_data,
                'timeout' => 30,
                'sslverify' => true // Always verify SSL for security
            ));
            
            if (is_wp_error($response)) {
                $this->log('NMI Payment Error: ' . $response->get_error_message());
                return array(
                    'success' => false,
                    'error' => $response->get_error_message(),
                    'raw_response' => ''
                );
            }
            
            $body = wp_remote_retrieve_body($response);
            parse_str($body, $parsed_response);
            
            // Log response if debug mode enabled
            if ($this->debug_mode) {
                $this->log('NMI Payment Response: ' . $body);
            }
            
            // Check response
            if (isset($parsed_response['response']) && $parsed_response['response'] == '1') {
                // Success
                return array(
                    'success' => true,
                    'transaction_id' => $parsed_response['transactionid'] ?? '',
                    'raw_response' => $body
                );
            } else {
                // Failure
                return array(
                    'success' => false,
                    'error' => $parsed_response['responsetext'] ?? 'Unknown error',
                    'raw_response' => $body
                );
            }
        }
        
        public function process_refund($order_id, $amount = null, $reason = '') {
            $order = wc_get_order($order_id);
            $transaction_id = $order->get_transaction_id();
            
            if (!$transaction_id) {
                return new WP_Error('error', 'Transaction ID not found');
            }
            
            // Build API request for refund - NMI uses same endpoint
            $api_url = 'https://secure.nmi.com/api/transact.php';
            
            $post_data = array(
                'security_key' => $this->api_username,
                'type' => 'refund',
                'transactionid' => $transaction_id,
                'amount' => number_format($amount, 2, '.', '')
            );
            
            // Send API request
            $response = wp_remote_post($api_url, array(
                'body' => $post_data,
                'timeout' => 30,
                'sslverify' => true // Always verify SSL
            ));
            
            if (is_wp_error($response)) {
                return new WP_Error('error', $response->get_error_message());
            }
            
            $body = wp_remote_retrieve_body($response);
            parse_str($body, $parsed_response);
            
            // Check response
            if (isset($parsed_response['response']) && $parsed_response['response'] == '1') {
                // Refund successful
                $refund_transaction_id = $parsed_response['transactionid'] ?? '';
                $order->add_order_note(sprintf('NMI Refund completed. Amount: %s. Transaction ID: %s. Reason: %s', 
                    wc_price($amount), $refund_transaction_id, $reason));
                
                // Log refund
                $this->log_transaction($order_id, 'refund', $refund_transaction_id, $amount, 'completed', $body);
                
                return true;
            } else {
                // Refund failed
                $error = $parsed_response['responsetext'] ?? 'Unknown error';
                
                // Log failed refund
                $this->log_transaction($order_id, 'refund', '', $amount, 'failed', $body);
                
                return new WP_Error('error', $error);
            }
        }
        
        private function log_transaction($order_id, $type, $transaction_id, $amount, $status, $raw_response) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'nmi_transaction_logs';
            
            // Insert log using wpdb->insert which handles sanitization
            $wpdb->insert($table_name, array(
                'order_id' => absint($order_id),
                'transaction_type' => sanitize_text_field($type),
                'transaction_id' => sanitize_text_field($transaction_id),
                'amount' => floatval($amount),
                'status' => sanitize_text_field($status),
                'raw_response' => $raw_response, // Text field, stored as-is
                'created_at' => current_time('mysql')
            ), array('%d', '%s', '%s', '%f', '%s', '%s', '%s'));
        }
    }
}

// Create NMI transaction logs table on plugin/theme activation
add_action('after_setup_theme', 'nmi_create_transaction_logs_table');
function nmi_create_transaction_logs_table() {
    // Only create table once
    if (get_option('nmi_transaction_logs_table_created')) {
        return;
    }
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'nmi_transaction_logs';
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id bigint(20) NOT NULL AUTO_INCREMENT,
        order_id bigint(20) NOT NULL,
        transaction_type varchar(20) NOT NULL,
        transaction_id varchar(100) DEFAULT '',
        amount decimal(10,2) NOT NULL,
        status varchar(20) NOT NULL,
        raw_response text,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY order_id (order_id),
        KEY created_at (created_at)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
    
    // Mark as created
    update_option('nmi_transaction_logs_table_created', true);
}

// Payment Gateway Settings Page
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'settings_payments') return;
    b2b_adm_guard();
    
    // Get NMI gateway instance
    $gateways = WC()->payment_gateways->get_available_payment_gateways();
    $nmi_gateway = isset($gateways['nmi_gateway']) ? $gateways['nmi_gateway'] : null;
    
    // Handle settings save
    $message = '';
    if (isset($_POST['save_nmi_settings']) && wp_verify_nonce($_POST['nmi_settings_nonce'], 'save_nmi_settings')) {
        // Update settings in the options table
        $settings = array(
            'enabled' => isset($_POST['nmi_enabled']) ? 'yes' : 'no',
            'title' => sanitize_text_field($_POST['nmi_title']),
            'description' => sanitize_textarea_field($_POST['nmi_description']),
            'test_mode' => isset($_POST['nmi_test_mode']) ? 'yes' : 'no',
            'api_username' => sanitize_text_field($_POST['nmi_api_username']),
            'api_password' => sanitize_text_field($_POST['nmi_api_password']),
            'capture_mode' => sanitize_text_field(isset($_POST['nmi_capture_mode']) ? $_POST['nmi_capture_mode'] : 'sale'),
            'debug_mode' => isset($_POST['nmi_debug_mode']) ? 'yes' : 'no',
            'allowed_card_types' => isset($_POST['nmi_allowed_card_types']) && is_array($_POST['nmi_allowed_card_types']) 
                ? array_map('sanitize_text_field', $_POST['nmi_allowed_card_types']) 
                : array('visa', 'mastercard', 'amex', 'discover')
        );
        
        update_option('woocommerce_nmi_gateway_settings', $settings);
        
        // Clear WooCommerce cache to reflect changes
        if (function_exists('wc_delete_shop_order_transients')) {
            wc_delete_shop_order_transients();
        }
        
        $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> Payment gateway settings saved and activated in WooCommerce.</div>';
    }
    
    // Get current settings
    $settings = get_option('woocommerce_nmi_gateway_settings', array());
    $enabled = isset($settings['enabled']) && $settings['enabled'] === 'yes';
    $title = $settings['title'] ?? 'Credit Card (NMI)';
    $description = $settings['description'] ?? 'Pay securely with your credit card.';
    $test_mode = isset($settings['test_mode']) && $settings['test_mode'] === 'yes';
    $api_username = $settings['api_username'] ?? '';
    $api_password = $settings['api_password'] ?? '';
    $capture_mode = $settings['capture_mode'] ?? 'sale';
    $debug_mode = isset($settings['debug_mode']) && $settings['debug_mode'] === 'yes';
    $allowed_card_types = $settings['allowed_card_types'] ?? array('visa', 'mastercard', 'amex', 'discover');
    
    // Get transaction logs
    global $wpdb;
    $table_name = $wpdb->prefix . 'nmi_transaction_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 50", ARRAY_A);
    
    b2b_adm_header('Payment Gateway Settings');
    ?>
    <div class="page-header">
        <h1 class="page-title"><i class="fa-solid fa-credit-card"></i> NMI Payment Gateway</h1>
    </div>
    
    <?= $message ?>
    
    <div class="card" style="margin-bottom:30px;">
        <form method="post">
            <?php wp_nonce_field('save_nmi_settings', 'nmi_settings_nonce'); ?>
            
            <h3 style="margin-top:0;color:#1e40af;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                <i class="fa-solid fa-gear"></i> Gateway Configuration
            </h3>
            
            <div style="margin-bottom:25px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="nmi_enabled" value="1" <?= checked($enabled, true, false) ?> style="width:20px;height:20px;">
                    <span style="font-weight:600;font-size:15px;">Enable NMI Gateway</span>
                </label>
                <small style="color:#6b7280;margin-left:30px;">Allow customers to pay using NMI payment gateway</small>
            </div>
            
            <div style="margin-bottom:25px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="nmi_test_mode" value="1" <?= checked($test_mode, true, false) ?> style="width:20px;height:20px;">
                    <span style="font-weight:600;font-size:15px;">Test Mode</span>
                </label>
                <small style="color:#6b7280;margin-left:30px;">Use test API credentials for testing payments</small>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;color:#374151;">Title</label>
                <input type="text" name="nmi_title" value="<?= esc_attr($title) ?>" style="width:100%;max-width:500px;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                <small style="color:#6b7280;display:block;margin-top:5px;">Payment method title shown to customers during checkout</small>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;color:#374151;">Description</label>
                <textarea name="nmi_description" rows="3" style="width:100%;max-width:500px;padding:10px;border:1px solid #e5e7eb;border-radius:6px;"><?= esc_textarea($description) ?></textarea>
                <small style="color:#6b7280;display:block;margin-top:5px;">Payment method description shown to customers</small>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;color:#374151;">API Username (Security Key)</label>
                <input type="text" name="nmi_api_username" value="<?= esc_attr($api_username) ?>" style="width:100%;max-width:500px;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                <small style="color:#6b7280;display:block;margin-top:5px;">Your NMI API username/security key from NMI account</small>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;color:#374151;">API Password (Optional)</label>
                <input type="password" name="nmi_api_password" value="<?= esc_attr($api_password) ?>" style="width:100%;max-width:500px;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                <small style="color:#6b7280;display:block;margin-top:5px;">Optional API password if required by your NMI account</small>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;color:#374151;">Capture Mode</label>
                <select name="nmi_capture_mode" style="width:100%;max-width:500px;padding:10px;border:1px solid #e5e7eb;border-radius:6px;">
                    <option value="sale" <?= $capture_mode === 'sale' ? 'selected' : '' ?>>Authorize & Capture (Immediate)</option>
                    <option value="auth" <?= $capture_mode === 'auth' ? 'selected' : '' ?>>Authorize Only (Manual Capture Required)</option>
                </select>
                <small style="color:#6b7280;display:block;margin-top:5px;">Choose when to capture payment. "Authorize Only" requires manual capture in NMI portal.</small>
            </div>
            
            <div style="margin-bottom:20px;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="nmi_debug_mode" value="1" <?= checked($debug_mode, true, false) ?> style="width:20px;height:20px;">
                    <span style="font-weight:600;font-size:15px;">Enable Debug Logging</span>
                </label>
                <small style="color:#ef4444;margin-left:30px;display:block;margin-top:5px;"><strong>WARNING:</strong> Debug mode logs sensitive data including card numbers. Only enable temporarily for troubleshooting.</small>
            </div>
            
            <div style="margin-bottom:25px;">
                <label style="display:block;font-weight:600;margin-bottom:8px;color:#374151;">Allowed Card Types</label>
                <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;max-width:500px;">
                    <?php 
                    $card_types = array(
                        'visa' => 'Visa',
                        'mastercard' => 'MasterCard',
                        'amex' => 'American Express',
                        'discover' => 'Discover',
                        'diners' => 'Diners Club',
                        'jcb' => 'JCB'
                    );
                    foreach ($card_types as $type => $label): 
                    ?>
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:14px;">
                        <input type="checkbox" name="nmi_allowed_card_types[]" value="<?= esc_attr($type) ?>" 
                               <?= in_array($type, $allowed_card_types) ? 'checked' : '' ?>>
                        <?= esc_html($label) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
                <small style="color:#6b7280;display:block;margin-top:5px;">Select which card types to accept at checkout</small>
            </div>
            
            <button type="submit" name="save_nmi_settings" class="btn-primary" style="padding:12px 30px;background:#3b82f6;color:white;border:none;border-radius:6px;font-weight:600;cursor:pointer;">
                <i class="fa-solid fa-save"></i> Save Settings
            </button>
        </form>
    </div>
    
    <!-- Transaction Logs -->
    <div class="card">
        <h3 style="margin-top:0;color:#1e40af;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
            <i class="fa-solid fa-list"></i> Transaction Logs
        </h3>
        
        <?php if (!empty($logs)): ?>
        <div style="overflow-x:auto;">
            <table class="data-table" style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f9fafb;">
                        <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Order ID</th>
                        <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Type</th>
                        <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Transaction ID</th>
                        <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Amount</th>
                        <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Status</th>
                        <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Date</th>
                        <th style="padding:12px;text-align:left;border-bottom:2px solid #e5e7eb;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): 
                        $status_color = $log['status'] === 'completed' ? '#10b981' : '#ef4444';
                        $type_icon = $log['transaction_type'] === 'payment' ? 'fa-credit-card' : 'fa-rotate-left';
                    ?>
                    <tr style="border-bottom:1px solid #f3f4f6;">
                        <td style="padding:12px;">
                            <a href="<?= admin_url('post.php?post=' . $log['order_id'] . '&action=edit') ?>" target="_blank" style="color:#3b82f6;text-decoration:none;font-weight:600;">
                                #<?= $log['order_id'] ?>
                            </a>
                        </td>
                        <td style="padding:12px;">
                            <span style="display:inline-flex;align-items:center;gap:5px;">
                                <i class="fa-solid <?= $type_icon ?>"></i>
                                <?= ucfirst($log['transaction_type']) ?>
                            </span>
                        </td>
                        <td style="padding:12px;font-family:monospace;font-size:13px;"><?= esc_html($log['transaction_id']) ?: '—' ?></td>
                        <td style="padding:12px;font-weight:600;"><?= wc_price($log['amount']) ?></td>
                        <td style="padding:12px;">
                            <span style="padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;color:white;background:<?= $status_color ?>;">
                                <?= ucfirst($log['status']) ?>
                            </span>
                        </td>
                        <td style="padding:12px;color:#6b7280;font-size:13px;"><?= date('Y-m-d H:i:s', strtotime($log['created_at'])) ?></td>
                        <td style="padding:12px;">
                            <button onclick="viewLogDetails(<?= esc_attr($log['id']) ?>)" 
                                    data-order-id="<?= esc_attr($log['order_id']) ?>"
                                    data-transaction-type="<?= esc_attr($log['transaction_type']) ?>"
                                    data-transaction-id="<?= esc_attr($log['transaction_id']) ?>"
                                    data-amount="<?= esc_attr($log['amount']) ?>"
                                    data-status="<?= esc_attr($log['status']) ?>"
                                    data-created-at="<?= esc_attr($log['created_at']) ?>"
                                    style="padding:6px 12px;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:4px;cursor:pointer;font-size:12px;">
                                <i class="fa-solid fa-eye"></i> View
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <p style="color:#6b7280;text-align:center;padding:40px 20px;">
            <i class="fa-solid fa-inbox" style="font-size:48px;display:block;margin-bottom:15px;opacity:0.3;"></i>
            No transaction logs yet. Transactions will appear here after first payment.
        </p>
        <?php endif; ?>
    </div>
    
    <!-- Log Details Modal -->
    <div id="logModal" style="display:none;position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);z-index:9999;padding:20px;overflow:auto;">
        <div style="max-width:800px;margin:50px auto;background:white;border-radius:12px;padding:30px;position:relative;">
            <button onclick="closeLogModal()" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#6b7280;">
                <i class="fa-solid fa-times"></i>
            </button>
            <h3 style="margin-top:0;color:#1e40af;"><i class="fa-solid fa-file-lines"></i> Transaction Details</h3>
            <div id="logModalContent" style="margin-top:20px;"></div>
        </div>
    </div>
    
    <script>
    function viewLogDetails(logId) {
        // Get data from button attributes instead of passing full object
        const button = event.target.closest('button');
        const log = {
            order_id: button.dataset.orderId,
            transaction_type: button.dataset.transactionType,
            transaction_id: button.dataset.transactionId,
            amount: button.dataset.amount,
            status: button.dataset.status,
            created_at: button.dataset.createdAt
        };
        
        const content = `
            <div style="background:#f9fafb;padding:20px;border-radius:8px;margin-bottom:20px;">
                <div style="display:grid;grid-template-columns:150px 1fr;gap:15px;">
                    <strong>Order ID:</strong>
                    <span>#${log.order_id}</span>
                    
                    <strong>Transaction Type:</strong>
                    <span>${log.transaction_type}</span>
                    
                    <strong>Transaction ID:</strong>
                    <span style="font-family:monospace;">${log.transaction_id || '—'}</span>
                    
                    <strong>Amount:</strong>
                    <span>${log.amount}</span>
                    
                    <strong>Status:</strong>
                    <span style="font-weight:600;color:${log.status === 'completed' ? '#10b981' : '#ef4444'};">${log.status}</span>
                    
                    <strong>Date:</strong>
                    <span>${log.created_at}</span>
                </div>
            </div>
            
            <h4 style="margin-top:25px;color:#374151;">Raw API Response:</h4>
            <div id="rawResponseContainer">
                <p style="color:#6b7280;text-align:center;padding:20px;">
                    <i class="fa-solid fa-lock" style="font-size:24px;display:block;margin-bottom:10px;"></i>
                    Raw API responses are not displayed for security purposes.<br>
                    Contact administrator if detailed debugging information is needed.
                </p>
            </div>
        `;
        document.getElementById('logModalContent').innerHTML = content;
        document.getElementById('logModal').style.display = 'block';
    }
    
    function closeLogModal() {
        document.getElementById('logModal').style.display = 'none';
    }
    
    // Close modal on outside click
    document.getElementById('logModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeLogModal();
    });
    </script>
    
    <?php b2b_adm_footer(); exit;
});

/**
 * PHASE 4: AJAX HANDLERS
 * Add AJAX functionality for sales panel
 */

// AJAX: Search products
add_action('wp_ajax_sa_search_products', 'sa_search_products_callback');
function sa_search_products_callback() {
    if (!current_user_can('view_sales_panel')) wp_die();
    
    $term = sanitize_text_field($_GET['term'] ?? '');
    $cid = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;
    
    // Security: Verify agent has access to this customer
    if ($cid > 0 && !current_user_can('administrator')) {
        $agent_id = get_current_user_id();
        $customer_agent = get_user_meta($cid, 'bagli_agent_id', true);
        $user = wp_get_current_user();
        $allow_manager = get_option('sales_manager_can_order', 0);
        
        // Check if sales agent or authorized sales manager
        $is_authorized = ($customer_agent == $agent_id) || 
                        (in_array('sales_manager', $user->roles) && $allow_manager);
        
        if (!$is_authorized) {
            wp_send_json_error('Access denied to this customer');
        }
    }
    
    $old_user = get_current_user_id();
    if ($cid > 0) wp_set_current_user($cid);

    $results = [];
    $products = wc_get_products([
        'limit' => 30, 
        'status' => 'publish', 
        's' => $term, 
        'return' => 'ids', 
        'orderby' => 'title', 
        'order' => 'ASC'
    ]);

    foreach ($products as $pid) {
        wp_cache_delete($pid, 'post_meta'); 
        $p = wc_get_product($pid); 
        if(!$p || $p->is_type('variable')) continue;

        $price_html = $p->get_price_html();
        $clean_text = strip_tags(html_entity_decode($price_html));
        preg_match_all('/[0-9]+(?:\.[0-9]+)?/', $clean_text, $matches);
        $found_prices = $matches[0] ?? [];
        $final_price = !empty($found_prices) ? min($found_prices) : $p->get_price();
        if ($p->get_price() > 0 && $p->get_price() < $final_price) $final_price = $p->get_price();

        $sku = $p->get_sku() ? ' (' . $p->get_sku() . ')' : '';
        $currency = get_woocommerce_currency_symbol();
        $stock = $p->get_stock_quantity();
        $stock_msg = is_numeric($stock) ? " | Stock: $stock" : "";
        $display_text = $p->get_name() . $sku . ' - ' . $currency . $final_price . $stock_msg;
        
        // Get assembly data
        $has_assembly = get_post_meta($pid, '_assembly_enabled', true) === 'yes';
        $assembly_price = $has_assembly ? floatval(get_post_meta($pid, '_assembly_price', true)) : 0;
        
        $results[] = [
            'id' => $pid, 
            'text' => $display_text, 
            'price' => $final_price,
            'has_assembly' => $has_assembly,
            'assembly_price' => $assembly_price
        ];
    }
    
    wp_set_current_user($old_user);
    wp_send_json($results);
}

// AJAX: Get order details
add_action('wp_ajax_sa_get_order_details', 'sa_get_order_details_callback');
function sa_get_order_details_callback() {
    if (!current_user_can('view_sales_panel')) wp_die();
    
    $order_id = intval($_GET['order_id'] ?? 0);
    $order = wc_get_order($order_id);
    
    if (!$order) {
        wp_send_json_error('Order not found');
    }
    
    // Security: Verify agent has access to this order's customer
    if (!current_user_can('administrator')) {
        $agent_id = get_current_user_id();
        $customer_id = $order->get_customer_id();
        $customer_agent = get_user_meta($customer_id, 'bagli_agent_id', true);
        
        if ($customer_agent != $agent_id) {
            wp_send_json_error('Access denied to this order');
        }
    }
    
    $items = [];
    foreach ($order->get_items() as $item) {
        $items[] = [
            'name' => $item->get_name(),
            'qty' => $item->get_quantity(),
            'total' => wc_price($item->get_total())
        ];
    }
    
    $data = [
        'id' => $order->get_id(),
        'date' => $order->get_date_created()->date('d.m.Y'),
        'status' => ucfirst($order->get_status()),
        'total' => $order->get_formatted_order_total(),
        'items' => $items,
        'billing' => $order->get_formatted_billing_address(),
        'shipping' => $order->get_formatted_shipping_address() ?: 'Same as billing',
        'notes' => $order->get_customer_note()
    ];
    
    wp_send_json_success($data);
}

// AJAX: Get unpaid orders for customer
add_action('wp_ajax_sa_get_unpaid_orders', 'sa_get_unpaid_orders_callback');
function sa_get_unpaid_orders_callback() {
    if (!current_user_can('view_sales_panel')) wp_die();
    
    $cid = intval($_GET['customer_id'] ?? 0);
    
    // Security: Verify agent has access to this customer
    if ($cid > 0 && !current_user_can('administrator')) {
        $agent_id = get_current_user_id();
        $customer_agent = get_user_meta($cid, 'bagli_agent_id', true);
        
        if ($customer_agent != $agent_id) {
            wp_send_json_error('Access denied to this customer');
        }
    }
    
    $unpaid_statuses = ['pending', 'on-hold', 'failed'];
    
    $orders = wc_get_orders([
        'customer_id' => $cid,
        'status' => $unpaid_statuses,
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    if (empty($orders)) {
        wp_send_json_success('<div style="padding:20px;text-align:center;color:#10b981">No unpaid orders.</div>');
    }
    
    $html = '<table style="width:100%;border-collapse:collapse;margin-top:10px">
        <thead><tr style="background:#fff7ed;color:#9a3412">
        <th style="padding:10px;text-align:left">Order</th>
        <th>Date</th>
        <th>Status</th>
        <th style="text-align:right">Total</th>
        </tr></thead><tbody>';
    
    $total_unpaid = 0;
    
    foreach ($orders as $o) {
        $html .= '<tr>
            <td style="padding:10px;border-bottom:1px solid #eee">#'.$o->get_id().'</td>
            <td style="padding:10px;border-bottom:1px solid #eee">'.$o->get_date_created()->date('d.m.Y').'</td>
            <td style="padding:10px;border-bottom:1px solid #eee">'.ucfirst($o->get_status()).'</td>
            <td style="padding:10px;border-bottom:1px solid #eee;text-align:right;font-weight:bold">'.$o->get_formatted_order_total().'</td>
        </tr>';
        $total_unpaid += $o->get_total();
    }
    
    $html .= '<tr><td colspan="3" style="padding:10px;text-align:right;font-weight:bold">Total Unpaid:</td>
        <td style="padding:10px;text-align:right;font-weight:bold;color:#dc2626">'.wc_price($total_unpaid).'</td></tr>';
    $html .= '</tbody></table>';
    
    wp_send_json_success($html);
}

// User Profile: Add hierarchy fields
add_action('show_user_profile', 'sa_hierarchy_fields');
add_action('edit_user_profile', 'sa_hierarchy_fields');

function sa_hierarchy_fields($user) {
    if (!current_user_can('manage_options')) return;
    
    $roles = (array) $user->roles;
    
    // If customer, show agent assignment
    if (in_array('customer', $roles) || empty($roles)) {
        $assigned_agent = get_user_meta($user->ID, 'bagli_agent_id', true);
        $agents = get_users(['role__in' => ['sales_agent', 'sales_manager']]); 
        ?>
        <h3>Sales System</h3>
        <table class="form-table">
            <tr>
                <th>Assigned Sales Agent</th>
                <td>
                    <select name="bagli_agent_id">
                        <option value="">-- None --</option>
                        <?php foreach ($agents as $a): ?>
                        <option value="<?= $a->ID ?>" <?= selected($assigned_agent, $a->ID, false) ?>>
                            <?= esc_html($a->display_name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }

    // If sales agent, show manager assignment
    if (in_array('sales_agent', $roles)) {
        $assigned_manager = get_user_meta($user->ID, 'bagli_manager_id', true);
        $managers = get_users(['role' => 'sales_manager']);
        ?>
        <h3>Sales System Hierarchy</h3>
        <table class="form-table">
            <tr>
                <th>Reports to Manager</th>
                <td>
                    <select name="bagli_manager_id">
                        <option value="">-- None --</option>
                        <?php foreach ($managers as $m): ?>
                        <option value="<?= $m->ID ?>" <?= selected($assigned_manager, $m->ID, false) ?>>
                            <?= esc_html($m->display_name) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
}

add_action('personal_options_update', 'sa_hierarchy_save');
add_action('edit_user_profile_update', 'sa_hierarchy_save');

function sa_hierarchy_save($user_id) {
    if (!current_user_can('manage_options')) return;
    
    if (isset($_POST['bagli_agent_id'])) {
        update_user_meta($user_id, 'bagli_agent_id', sanitize_text_field($_POST['bagli_agent_id']));
    }
    
    if (isset($_POST['bagli_manager_id'])) {
        update_user_meta($user_id, 'bagli_manager_id', sanitize_text_field($_POST['bagli_manager_id']));
    }
}

// Add sales agent name to order meta in admin
add_action('woocommerce_admin_order_data_after_order_details', function($order){
    $agent = $order->get_meta('_sales_agent_name');
    if($agent) {
        echo '<p class="form-field form-field-wide"><strong>Sales Agent:</strong> ' . esc_html($agent) . '</p>';
    }
});

// Customer Switch Functionality
add_action('init', function () {
    // Switch to customer
    if (isset($_GET['switch_customer']) && current_user_can('switch_to_customer')) {
        check_admin_referer('switch_customer');
        $target_id = intval($_GET['switch_customer']);
        $agent_id = get_current_user_id();
        
        // Verify agent has access to this customer
        $assigned_agent = get_user_meta($target_id, 'bagli_agent_id', true);
        $user = wp_get_current_user();
        $allow_manager = get_option('sales_manager_can_order', 0);
        
        // Check if sales agent or authorized sales manager or admin
        $is_authorized = ($assigned_agent == $agent_id) || 
                        (in_array('sales_manager', $user->roles) && $allow_manager) ||
                        current_user_can('administrator');
        
        if (!$is_authorized) {
            wp_die('Access denied to this customer');
        }
        
        // Create secure token
        $token = wp_hash($agent_id . $target_id . time());
        $switch_data = $agent_id . '|' . $token;
        
        // Store agent ID with token in cookie
        setcookie('sa_switch_back', $switch_data, time() + 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        
        // Also store in user meta as backup verification
        update_user_meta($target_id, '_sa_switch_token', $token);
        
        // Switch to customer
        wp_destroy_current_session();
        wp_clear_auth_cookie();
        wp_set_current_user($target_id);
        wp_set_auth_cookie($target_id);
        
        wp_redirect(home_url());
        exit;
    }
    
    // Switch back to agent
    if (isset($_GET['switch_back']) && isset($_COOKIE['sa_switch_back'])) {
        check_admin_referer('switch_back');
        
        // Parse cookie data
        $cookie_parts = explode('|', $_COOKIE['sa_switch_back']);
        if (count($cookie_parts) !== 2) {
            wp_die('Invalid session data');
        }
        
        $agent_id = intval($cookie_parts[0]);
        $stored_token = $cookie_parts[1];
        
        // Verify token
        $current_user_id = get_current_user_id();
        $expected_token = get_user_meta($current_user_id, '_sa_switch_token', true);
        
        if ($stored_token !== $expected_token) {
            wp_die('Invalid session token');
        }
        
        // Clean up
        setcookie('sa_switch_back', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        delete_user_meta($current_user_id, '_sa_switch_token');
        
        // Switch back to agent
        wp_destroy_current_session();
        wp_clear_auth_cookie();
        wp_set_current_user($agent_id);
        wp_set_auth_cookie($agent_id);
        
        wp_redirect(home_url('/sales-panel'));
        exit;
    }
}, 5);

// Display "Back to Panel" button when switched
add_action('wp_footer', function () {
    if (is_user_logged_in() && isset($_COOKIE['sa_switch_back'])) {
        $switch_back_url = wp_nonce_url(add_query_arg('switch_back', '1', home_url()), 'switch_back');
        echo '<a href="' . esc_url($switch_back_url) . '" style="position:fixed;bottom:20px;right:20px;background:#000;color:#fff;padding:15px 20px;border-radius:30px;z-index:9999;box-shadow:0 4px 10px rgba(0,0,0,0.3);text-decoration:none;font-weight:600;font-family:Inter,sans-serif;">
            <i class="fa-solid fa-arrow-left"></i> Back to Sales Panel
        </a>';
    }
});

// Sales Panel: Messaging Page
function sa_render_messaging_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    $user_groups = b2b_get_user_messaging_groups($user_id);
    
    // If admin, show all groups
    if (current_user_can('manage_options')) {
        $user_groups = b2b_get_messaging_groups();
    }
    
    $selected_group = sanitize_text_field($_GET['group'] ?? '');
    if (empty($selected_group) && !empty($user_groups)) {
        $selected_group = array_key_first($user_groups);
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= esc_html($panel_title) ?> - Messaging</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
            }
            .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
            .btn { padding: 10px 16px; border-radius: 6px; border: none; cursor: pointer; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; font-size: 14px; transition: 0.2s; }
            .btn:hover { opacity: 0.9; }
            .btn-primary { background: var(--primary); color: #fff; }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>
        
        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>" class="active"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <h1 style="margin-bottom: 30px;"><i class="fa-solid fa-comments"></i> Messaging</h1>
            
            <?php if (empty($user_groups)): ?>
                <div class="card" style="text-align:center;padding:50px;">
                    <i class="fa-solid fa-inbox" style="font-size:64px;color:#e5e7eb;margin-bottom:20px;"></i>
                    <h3 style="color:#9ca3af;">No messaging groups</h3>
                    <p style="color:#9ca3af;">You are not assigned to any messaging groups yet.</p>
                </div>
            <?php else: ?>
            
            <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;">
                <div class="card" style="height:600px;overflow-y:auto;">
                    <h3 style="margin-top:0;">Your Groups</h3>
                    <?php foreach ($user_groups as $group_id => $group): ?>
                        <a href="?sales_panel=messaging&group=<?= $group_id ?>" class="<?= $selected_group == $group_id ? 'active' : '' ?>" style="display:block;padding:12px;margin-bottom:8px;border-radius:8px;text-decoration:none;color:inherit;background:<?= $selected_group == $group_id ? '#eff6ff' : '#f9fafb' ?>;border-left:4px solid <?= $selected_group == $group_id ? '#3b82f6' : 'transparent' ?>;">
                            <div style="font-weight:600;"><?= esc_html($group['name']) ?></div>
                            <div style="font-size:12px;color:#9ca3af;"><?= count($group['members'] ?? []) ?> members</div>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="card" style="height:600px;display:flex;flex-direction:column;">
                    <?php if ($selected_group && isset($user_groups[$selected_group])): ?>
                        <h3 style="margin:0 0 15px 0;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                            <?= esc_html($user_groups[$selected_group]['name']) ?>
                        </h3>
                        
                        <div id="messages-container" style="flex:1;overflow-y:auto;padding:10px;background:#f9fafb;border-radius:8px;margin-bottom:15px;"></div>
                        
                        <form id="messageForm" style="display:flex;gap:10px;">
                            <input type="hidden" id="current_group" value="<?= $selected_group ?>">
                            <textarea id="message_input" placeholder="Type your message..." style="flex:1;resize:none;height:60px;padding:10px;border:1px solid #d1d5db;border-radius:8px;" required></textarea>
                            <button type="submit" style="align-self:flex-end;height:60px;background:#10b981;color:white;border:none;padding:0 20px;border-radius:8px;cursor:pointer;">
                                <i class="fa-solid fa-paper-plane"></i> Send
                            </button>
                        </form>
                    <?php else: ?>
                        <p style="text-align:center;color:#9ca3af;padding:50px;">Select a group to start messaging</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <script>
            var ajaxurl = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
            let lastTimestamp = 0;
            
            function loadMessages(initial = false) {
                const groupId = document.getElementById('current_group')?.value;
                if (!groupId) return;
                
                fetch(ajaxurl + '?action=b2b_get_messages&group_id=' + groupId + '&last_timestamp=' + (initial ? 0 : lastTimestamp))
                    .then(res => res.json())
                    .then(data => {
                        if (data.success && data.data.messages.length > 0) {
                            const container = document.getElementById('messages-container');
                            const shouldScroll = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
                            
                            data.data.messages.forEach(msg => {
                                const isMe = msg.user_id == <?= $user_id ?>;
                                const div = document.createElement('div');
                                div.style.marginBottom = '15px';
                                div.style.textAlign = isMe ? 'right' : 'left';
                                
                                div.innerHTML = `
                                    <div style="display:inline-block;max-width:70%;text-align:left;">
                                        <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">${msg.user_name} • ${new Date(msg.time).toLocaleString()}</div>
                                        <div style="background:${isMe ? '#3b82f6' : '#fff'};color:${isMe ? '#fff' : '#1f2937'};padding:10px 15px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                                            ${msg.message}
                                        </div>
                                    </div>
                                `;
                                
                                container.appendChild(div);
                                lastTimestamp = Math.max(lastTimestamp, msg.timestamp);
                            });
                            
                            if (shouldScroll || initial) {
                                container.scrollTop = container.scrollHeight;
                            }
                        }
                    });
            }
            
            document.getElementById('messageForm')?.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const groupId = document.getElementById('current_group').value;
                const message = document.getElementById('message_input').value.trim();
                
                if (!message) return;
                
                const formData = new FormData();
                formData.append('action', 'b2b_send_message');
                formData.append('group_id', groupId);
                formData.append('message', message);
                
                fetch(ajaxurl, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('message_input').value = '';
                        loadMessages();
                    } else {
                        alert('Error: ' + (data.data || 'Failed to send'));
                    }
                });
            });
            
            if (document.getElementById('current_group')) {
                loadMessages(true);
                setInterval(() => loadMessages(false), 5000);
            }
            </script>
            
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

// Sales Panel: Notes Page
function sa_render_notes_page() {
    if (!current_user_can('view_sales_panel')) {
        wp_die('Access denied');
    }
    
    $user_id = get_current_user_id();
    $user = wp_get_current_user();
    $panel_title = get_option('sales_panel_title', 'Agent Panel');
    
    $notes = get_option('b2b_notes', []);
    $user_groups = array_keys(b2b_get_user_messaging_groups($user_id));
    
    // Filter notes based on visibility
    $visible_notes = [];
    foreach ($notes as $note_id => $note) {
        if ($note['visibility'] == 'general') {
            $visible_notes[$note_id] = $note;
        } elseif ($note['visibility'] == 'group' && in_array($note['group_id'], $user_groups)) {
            $visible_notes[$note_id] = $note;
        } elseif (current_user_can('manage_options')) {
            $visible_notes[$note_id] = $note;
        }
    }
    
    $messaging_groups = b2b_get_messaging_groups();
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?= esc_html($panel_title) ?> - Notes</title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            :root { --primary: #4f46e5; --bg: #f3f4f6; --text: #1f2937; }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { margin: 0; font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); display: flex; }
            .sidebar { width: 260px; background: #111827; color: #fff; min-height: 100vh; padding: 20px; position: fixed; z-index: 99; transition: 0.3s; }
            .sidebar-header { margin-bottom: 40px; font-size: 20px; font-weight: 700; color: #fff; }
            .sidebar a { display: flex; align-items: center; gap: 10px; padding: 12px; color: #9ca3af; text-decoration: none; border-radius: 8px; margin-bottom: 5px; font-weight: 500; }
            .sidebar a:hover, .sidebar a.active { background: var(--primary); color: #fff; }
            .main { margin-left: 260px; padding: 40px; flex: 1; width: 100%; }
            .mobile-toggle { display: none; position: fixed; top: 15px; left: 15px; z-index: 100; background: #fff; padding: 10px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); cursor: pointer; }
            @media(max-width:768px) { 
                .sidebar { transform: translateX(-100%); } 
                .sidebar.active { transform: translateX(0); } 
                .main { margin-left: 0; padding: 20px; padding-top: 70px; } 
                .mobile-toggle { display: block; }
            }
            .card { background: #fff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); padding: 25px; margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="mobile-toggle" onclick="document.querySelector('.sidebar').classList.toggle('active')">
            <i class="fa-solid fa-bars" style="font-size:20px;color:#333"></i>
        </div>
        
        <div class="sidebar">
            <div class="sidebar-header"><i class="fa-solid fa-chart-pie"></i> <?= esc_html($panel_title) ?></div>
            <a href="<?= home_url('/sales-panel/dashboard') ?>"><i class="fa-solid fa-gauge"></i> Dashboard</a>
            <a href="<?= home_url('/sales-panel/customers') ?>"><i class="fa-solid fa-users"></i> My Customers</a>
            <a href="<?= home_url('/sales-panel/orders') ?>"><i class="fa-solid fa-box-open"></i> Orders</a>
            <a href="<?= home_url('/sales-panel/commissions') ?>"><i class="fa-solid fa-chart-line"></i> Reports</a>
            <a href="<?= home_url('/sales-panel/messaging') ?>"><i class="fa-solid fa-comments"></i> Messaging</a>
            <a href="<?= home_url('/sales-panel/notes') ?>" class="active"><i class="fa-solid fa-note-sticky"></i> Notes</a>
            <a href="<?= wp_logout_url(home_url('/sales-login')) ?>" style="margin-top:auto;color:#ef4444"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
        
        <div class="main">
            <h1 style="margin-bottom: 30px;"><i class="fa-solid fa-note-sticky"></i> Notes</h1>
            
            <?php if (empty($visible_notes)): ?>
                <div class="card" style="text-align:center;padding:50px;">
                    <i class="fa-solid fa-sticky-note" style="font-size:64px;color:#e5e7eb;margin-bottom:20px;"></i>
                    <h3 style="color:#9ca3af;">No notes yet</h3>
                </div>
            <?php else: ?>
            
            <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(350px, 1fr));gap:20px;">
                <?php foreach ($visible_notes as $note_id => $note): ?>
                <div class="card" style="background:<?= $note['visibility'] == 'general' ? '#fffbeb' : '#eff6ff' ?>;border-left:4px solid <?= $note['visibility'] == 'general' ? '#f59e0b' : '#3b82f6' ?>;">
                    <h3 style="margin:0 0 10px 0;color:#1f2937;"><?= esc_html($note['title']) ?></h3>
                    <div style="font-size:14px;color:#4b5563;margin-bottom:15px;white-space:pre-wrap;">
                        <?= esc_html($note['content']) ?>
                    </div>
                    <div style="font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:10px;">
                        <div><strong>By:</strong> <?= esc_html($note['author']) ?></div>
                        <div><strong>Visibility:</strong> <?= $note['visibility'] == 'general' ? 'Everyone' : esc_html($messaging_groups[$note['group_id']]['name'] ?? 'Unknown Group') ?></div>
                        <div><strong>Created:</strong> <?= date('d.m.Y H:i', strtotime($note['created'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

// End of Sales Agent System Phase 4

/* =====================================================
   MESSAGING AND NOTES SYSTEM
   - Admin creates messaging groups
   - Users assigned to groups can communicate
   - Notifications for new messages
   - Notes system with group/general visibility
===================================================== */

// Helper function: Get all messaging groups
function b2b_get_messaging_groups() {
    return get_option('b2b_messaging_groups', []);
}

// Helper function: Check if user is in a messaging group
function b2b_user_in_messaging_group($user_id, $group_id) {
    $groups = b2b_get_messaging_groups();
    if (!isset($groups[$group_id])) return false;
    
    $members = $groups[$group_id]['members'] ?? [];
    return in_array($user_id, $members);
}

// Helper function: Get user's messaging groups
function b2b_get_user_messaging_groups($user_id) {
    $groups = b2b_get_messaging_groups();
    $user_groups = [];
    
    foreach ($groups as $group_id => $group) {
        $members = $group['members'] ?? [];
        if (in_array($user_id, $members)) {
            $user_groups[$group_id] = $group;
        }
    }
    
    return $user_groups;
}

// AJAX: Send message to group
add_action('wp_ajax_b2b_send_message', function() {
    if (!is_user_logged_in()) wp_die();
    
    $group_id = sanitize_text_field($_POST['group_id'] ?? '');
    $message = sanitize_textarea_field($_POST['message'] ?? '');
    
    if (empty($group_id) || empty($message)) {
        wp_send_json_error('Invalid data');
    }
    
    $user_id = get_current_user_id();
    
    // Check if user is in this group
    if (!current_user_can('manage_options') && !b2b_user_in_messaging_group($user_id, $group_id)) {
        wp_send_json_error('Not authorized');
    }
    
    // Get existing messages
    $messages = get_option('b2b_messages_' . $group_id, []);
    
    // Add new message
    $user = wp_get_current_user();
    $messages[] = [
        'user_id' => $user_id,
        'user_name' => $user->display_name,
        'message' => $message,
        'time' => current_time('mysql'),
        'timestamp' => time()
    ];
    
    // Keep last 500 messages
    if (count($messages) > 500) {
        $messages = array_slice($messages, -500);
    }
    
    update_option('b2b_messages_' . $group_id, $messages);
    
    // Update unread count for other group members
    $groups = b2b_get_messaging_groups();
    if (isset($groups[$group_id]['members'])) {
        foreach ($groups[$group_id]['members'] as $member_id) {
            if ($member_id != $user_id) {
                $unread = get_user_meta($member_id, 'b2b_unread_messages', true) ?: 0;
                update_user_meta($member_id, 'b2b_unread_messages', $unread + 1);
            }
        }
    }
    
    wp_send_json_success(['message' => 'Sent']);
});

// AJAX: Get messages for a group
add_action('wp_ajax_b2b_get_messages', function() {
    if (!is_user_logged_in()) wp_die();
    
    $group_id = sanitize_text_field($_GET['group_id'] ?? '');
    $last_timestamp = intval($_GET['last_timestamp'] ?? 0);
    
    if (empty($group_id)) {
        wp_send_json_error('Invalid group');
    }
    
    $user_id = get_current_user_id();
    
    // Check if user is in this group
    if (!current_user_can('manage_options') && !b2b_user_in_messaging_group($user_id, $group_id)) {
        wp_send_json_error('Not authorized');
    }
    
    // Get messages
    $messages = get_option('b2b_messages_' . $group_id, []);
    
    // Filter new messages only
    if ($last_timestamp > 0) {
        $messages = array_filter($messages, function($msg) use ($last_timestamp) {
            return ($msg['timestamp'] ?? 0) > $last_timestamp;
        });
    }
    
    // Clear unread count for this user
    if (!$last_timestamp) {
        delete_user_meta($user_id, 'b2b_unread_messages');
    }
    
    wp_send_json_success(['messages' => array_values($messages)]);
});

// AJAX: Save note
add_action('wp_ajax_b2b_save_note', function() {
    if (!current_user_can('manage_options')) wp_die();
    
    $note_id = sanitize_text_field($_POST['note_id'] ?? '');
    $title = sanitize_text_field($_POST['title'] ?? '');
    $content = sanitize_textarea_field($_POST['content'] ?? '');
    $visibility = sanitize_text_field($_POST['visibility'] ?? 'general');
    $group_id = sanitize_text_field($_POST['group_id'] ?? '');
    
    if (empty($title) || empty($content)) {
        wp_send_json_error('Title and content required');
    }
    
    $notes = get_option('b2b_notes', []);
    
    if (empty($note_id)) {
        // New note
        $note_id = 'note_' . time() . '_' . wp_generate_password(8, false);
    }
    
    $notes[$note_id] = [
        'title' => $title,
        'content' => $content,
        'visibility' => $visibility,
        'group_id' => $group_id,
        'author' => wp_get_current_user()->display_name,
        'created' => current_time('mysql'),
        'updated' => current_time('mysql')
    ];
    
    update_option('b2b_notes', $notes);
    
    wp_send_json_success(['message' => 'Note saved']);
});

// AJAX: Delete note
add_action('wp_ajax_b2b_delete_note', function() {
    if (!current_user_can('manage_options')) wp_die();
    
    $note_id = sanitize_text_field($_POST['note_id'] ?? '');
    
    if (empty($note_id)) {
        wp_send_json_error('Invalid note');
    }
    
    $notes = get_option('b2b_notes', []);
    unset($notes[$note_id]);
    update_option('b2b_notes', $notes);
    
    wp_send_json_success(['message' => 'Note deleted']);
});

// PAGE: Messaging Groups Management
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'messaging_groups') return;
    b2b_adm_guard();
    
    // Handle form submission
    if (isset($_POST['save_messaging_group'])) {
        $group_id = sanitize_text_field($_POST['group_id'] ?? '');
        $group_name = sanitize_text_field($_POST['group_name'] ?? '');
        $members = $_POST['members'] ?? [];
        
        if (!empty($group_name)) {
            $groups = b2b_get_messaging_groups();
            
            if (empty($group_id)) {
                $group_id = 'group_' . time();
            }
            
            $groups[$group_id] = [
                'name' => $group_name,
                'members' => array_map('intval', $members),
                'created' => current_time('mysql')
            ];
            
            update_option('b2b_messaging_groups', $groups);
            
            echo '<script>window.location.href="' . home_url('/b2b-panel/messaging/groups') . '";</script>';
            exit;
        }
    }
    
    // Handle delete
    if (isset($_GET['delete'])) {
        $group_id = sanitize_text_field($_GET['delete']);
        $groups = b2b_get_messaging_groups();
        unset($groups[$group_id]);
        update_option('b2b_messaging_groups', $groups);
        
        // Also delete messages
        delete_option('b2b_messages_' . $group_id);
        
        echo '<script>window.location.href="' . home_url('/b2b-panel/messaging/groups') . '";</script>';
        exit;
    }
    
    $groups = b2b_get_messaging_groups();
    $all_users = get_users(['role__not_in' => ['customer']]);
    
    b2b_adm_header('Messaging Groups');
    ?>
    
    <div class="page-header">
        <h1 class="page-title"><i class="fa-solid fa-users"></i> Messaging Groups</h1>
        <button class="btn" onclick="document.getElementById('addGroupModal').style.display='block'">
            <i class="fa-solid fa-plus"></i> New Group
        </button>
    </div>
    
    <div class="card">
        <table>
            <thead>
                <tr>
                    <th>Group Name</th>
                    <th>Members</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($groups)): ?>
                    <tr><td colspan="4" style="text-align:center;padding:30px;color:#9ca3af;">No groups yet. Create your first messaging group!</td></tr>
                <?php else: ?>
                    <?php foreach ($groups as $group_id => $group): ?>
                    <tr>
                        <td><strong><?= esc_html($group['name']) ?></strong></td>
                        <td><?= count($group['members'] ?? []) ?> members</td>
                        <td><?= date('d.m.Y', strtotime($group['created'])) ?></td>
                        <td>
                            <a href="<?= home_url('/b2b-panel/messaging?group=' . $group_id) ?>" class="btn btn-light" style="padding:6px 12px;">
                                <i class="fa-solid fa-comments"></i> View
                            </a>
                            <button class="btn btn-secondary" style="padding:6px 12px;" onclick="editGroup('<?= $group_id ?>')">
                                <i class="fa-solid fa-edit"></i>
                            </button>
                            <a href="?delete=<?= $group_id ?>" class="btn btn-danger" style="padding:6px 12px;" onclick="return confirm('Delete this group?')">
                                <i class="fa-solid fa-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Add/Edit Group Modal -->
    <div id="addGroupModal" style="display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);">
        <div style="background:white;margin:5% auto;padding:30px;width:90%;max-width:600px;border-radius:12px;position:relative;">
            <span onclick="document.getElementById('addGroupModal').style.display='none'" style="position:absolute;right:20px;top:20px;font-size:28px;font-weight:bold;color:#999;cursor:pointer;">&times;</span>
            <h2 style="margin:0 0 20px 0;"><i class="fa-solid fa-users"></i> <span id="modalTitle">New Messaging Group</span></h2>
            <form method="post">
                <input type="hidden" name="group_id" id="group_id">
                <label style="display:block;margin-bottom:5px;font-weight:600;">Group Name</label>
                <input type="text" name="group_name" id="group_name" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;margin-bottom:15px;">
                
                <label style="display:block;margin-bottom:5px;font-weight:600;">Members</label>
                <div style="max-height:300px;overflow-y:auto;border:1px solid #d1d5db;border-radius:6px;padding:10px;margin-bottom:20px;">
                    <?php foreach ($all_users as $user): ?>
                        <label style="display:block;padding:8px;cursor:pointer;border-radius:4px;" onmouseover="this.style.background='#f3f4f6'" onmouseout="this.style.background='transparent'">
                            <input type="checkbox" name="members[]" value="<?= $user->ID ?>" style="margin-right:8px;" class="member-checkbox">
                            <?= esc_html($user->display_name) ?> <span style="color:#9ca3af;font-size:12px;">(<?= $user->user_email ?>)</span>
                        </label>
                    <?php endforeach; ?>
                </div>
                
                <button type="submit" name="save_messaging_group" class="btn" style="width:100%;">
                    <i class="fa-solid fa-save"></i> Save Group
                </button>
            </form>
        </div>
    </div>
    
    <script>
    function editGroup(groupId) {
        const groups = <?= json_encode($groups) ?>;
        const group = groups[groupId];
        
        document.getElementById('modalTitle').textContent = 'Edit Group';
        document.getElementById('group_id').value = groupId;
        document.getElementById('group_name').value = group.name;
        
        // Uncheck all
        document.querySelectorAll('.member-checkbox').forEach(cb => cb.checked = false);
        
        // Check group members
        group.members.forEach(memberId => {
            const checkbox = document.querySelector(`.member-checkbox[value="${memberId}"]`);
            if (checkbox) checkbox.checked = true;
        });
        
        document.getElementById('addGroupModal').style.display = 'block';
    }
    </script>
    
    <?php
    b2b_adm_footer();
    exit;
});

// PAGE: Messaging Interface
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'messaging') return;
    b2b_adm_guard();
    
    $user_id = get_current_user_id();
    $user_groups = b2b_get_user_messaging_groups($user_id);
    
    // If admin, show all groups
    if (current_user_can('manage_options')) {
        $user_groups = b2b_get_messaging_groups();
    }
    
    $selected_group = sanitize_text_field($_GET['group'] ?? '');
    if (empty($selected_group) && !empty($user_groups)) {
        $selected_group = array_key_first($user_groups);
    }
    
    b2b_adm_header('Messaging');
    ?>
    
    <div class="page-header">
        <h1 class="page-title"><i class="fa-solid fa-comments"></i> Messaging</h1>
        <?php if (current_user_can('manage_options')): ?>
        <a href="<?= home_url('/b2b-panel/messaging/groups') ?>" class="btn">
            <i class="fa-solid fa-users-gear"></i> Manage Groups
        </a>
        <?php endif; ?>
    </div>
    
    <?php if (empty($user_groups)): ?>
        <div class="card" style="text-align:center;padding:50px;">
            <i class="fa-solid fa-inbox" style="font-size:64px;color:#e5e7eb;margin-bottom:20px;"></i>
            <h3 style="color:#9ca3af;">No messaging groups</h3>
            <p style="color:#9ca3af;">You are not assigned to any messaging groups yet.</p>
            <?php if (current_user_can('manage_options')): ?>
                <a href="<?= home_url('/b2b-panel/messaging/groups') ?>" class="btn" style="margin-top:20px;">
                    <i class="fa-solid fa-plus"></i> Create a Group
                </a>
            <?php endif; ?>
        </div>
    <?php else: ?>
    
    <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;">
        <!-- Groups List -->
        <div class="card" style="height:600px;overflow-y:auto;">
            <h3 style="margin-top:0;">Your Groups</h3>
            <?php foreach ($user_groups as $group_id => $group): ?>
                <a href="?group=<?= $group_id ?>" class="<?= $selected_group == $group_id ? 'active' : '' ?>" style="display:block;padding:12px;margin-bottom:8px;border-radius:8px;text-decoration:none;color:inherit;background:<?= $selected_group == $group_id ? '#eff6ff' : '#f9fafb' ?>;border-left:4px solid <?= $selected_group == $group_id ? '#3b82f6' : 'transparent' ?>;">
                    <div style="font-weight:600;"><?= esc_html($group['name']) ?></div>
                    <div style="font-size:12px;color:#9ca3af;"><?= count($group['members'] ?? []) ?> members</div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <!-- Messages -->
        <div class="card" style="height:600px;display:flex;flex-direction:column;">
            <?php if ($selected_group && isset($user_groups[$selected_group])): ?>
                <h3 style="margin:0 0 15px 0;padding-bottom:15px;border-bottom:1px solid #e5e7eb;">
                    <?= esc_html($user_groups[$selected_group]['name']) ?>
                </h3>
                
                <div id="messages-container" style="flex:1;overflow-y:auto;padding:10px;background:#f9fafb;border-radius:8px;margin-bottom:15px;">
                    <!-- Messages will be loaded here -->
                </div>
                
                <form id="messageForm" style="display:flex;gap:10px;">
                    <input type="hidden" id="current_group" value="<?= $selected_group ?>">
                    <textarea id="message_input" placeholder="Type your message..." style="flex:1;resize:none;height:60px;padding:10px;border:1px solid #d1d5db;border-radius:8px;" required></textarea>
                    <button type="submit" class="btn" style="align-self:flex-end;height:60px;">
                        <i class="fa-solid fa-paper-plane"></i> Send
                    </button>
                </form>
            <?php else: ?>
                <p style="text-align:center;color:#9ca3af;padding:50px;">Select a group to start messaging</p>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
    let lastTimestamp = 0;
    
    function loadMessages(initial = false) {
        const groupId = document.getElementById('current_group').value;
        if (!groupId) return;
        
        fetch(ajaxurl + '?action=b2b_get_messages&group_id=' + groupId + '&last_timestamp=' + (initial ? 0 : lastTimestamp))
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data.messages.length > 0) {
                    const container = document.getElementById('messages-container');
                    const shouldScroll = container.scrollHeight - container.scrollTop <= container.clientHeight + 100;
                    
                    data.data.messages.forEach(msg => {
                        const isMe = msg.user_id == <?= $user_id ?>;
                        const div = document.createElement('div');
                        div.style.marginBottom = '15px';
                        div.style.textAlign = isMe ? 'right' : 'left';
                        
                        div.innerHTML = `
                            <div style="display:inline-block;max-width:70%;text-align:left;">
                                <div style="font-size:11px;color:#9ca3af;margin-bottom:3px;">${msg.user_name} • ${new Date(msg.time).toLocaleString()}</div>
                                <div style="background:${isMe ? '#3b82f6' : '#fff'};color:${isMe ? '#fff' : '#1f2937'};padding:10px 15px;border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                                    ${msg.message}
                                </div>
                            </div>
                        `;
                        
                        container.appendChild(div);
                        lastTimestamp = Math.max(lastTimestamp, msg.timestamp);
                    });
                    
                    if (shouldScroll || initial) {
                        container.scrollTop = container.scrollHeight;
                    }
                }
            });
    }
    
    document.getElementById('messageForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const groupId = document.getElementById('current_group').value;
        const message = document.getElementById('message_input').value.trim();
        
        if (!message) return;
        
        const formData = new FormData();
        formData.append('action', 'b2b_send_message');
        formData.append('group_id', groupId);
        formData.append('message', message);
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('message_input').value = '';
                loadMessages();
            } else {
                alert('Error: ' + (data.data || 'Failed to send'));
            }
        });
    });
    
    // Load initial messages
    loadMessages(true);
    
    // Poll for new messages every 5 seconds
    setInterval(() => loadMessages(false), 5000);
    </script>
    
    <?php endif; ?>
    
    <?php
    b2b_adm_footer();
    exit;
});

// PAGE: Notes Management
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'notes') return;
    b2b_adm_guard();
    
    $notes = get_option('b2b_notes', []);
    $user_id = get_current_user_id();
    $user_groups = array_keys(b2b_get_user_messaging_groups($user_id));
    
    // Filter notes based on visibility
    $visible_notes = [];
    foreach ($notes as $note_id => $note) {
        if ($note['visibility'] == 'general') {
            $visible_notes[$note_id] = $note;
        } elseif ($note['visibility'] == 'group' && in_array($note['group_id'], $user_groups)) {
            $visible_notes[$note_id] = $note;
        } elseif (current_user_can('manage_options')) {
            $visible_notes[$note_id] = $note;
        }
    }
    
    $messaging_groups = b2b_get_messaging_groups();
    
    b2b_adm_header('Notes');
    ?>
    
    <div class="page-header">
        <h1 class="page-title"><i class="fa-solid fa-note-sticky"></i> Notes</h1>
        <?php if (current_user_can('manage_options')): ?>
        <button class="btn" onclick="openNoteModal()">
            <i class="fa-solid fa-plus"></i> New Note
        </button>
        <?php endif; ?>
    </div>
    
    <?php if (empty($visible_notes)): ?>
        <div class="card" style="text-align:center;padding:50px;">
            <i class="fa-solid fa-sticky-note" style="font-size:64px;color:#e5e7eb;margin-bottom:20px;"></i>
            <h3 style="color:#9ca3af;">No notes yet</h3>
            <?php if (current_user_can('manage_options')): ?>
                <p style="color:#9ca3af;">Create your first note to share important information.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
    
    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(350px, 1fr));gap:20px;">
        <?php foreach ($visible_notes as $note_id => $note): ?>
        <div class="card" style="background:<?= $note['visibility'] == 'general' ? '#fffbeb' : '#eff6ff' ?>;border-left:4px solid <?= $note['visibility'] == 'general' ? '#f59e0b' : '#3b82f6' ?>;">
            <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:10px;">
                <h3 style="margin:0;color:#1f2937;"><?= esc_html($note['title']) ?></h3>
                <?php if (current_user_can('manage_options')): ?>
                <div>
                    <button class="btn btn-light" style="padding:4px 8px;font-size:12px;" onclick="editNote('<?= $note_id ?>')">
                        <i class="fa-solid fa-edit"></i>
                    </button>
                    <button class="btn btn-danger" style="padding:4px 8px;font-size:12px;" onclick="deleteNote('<?= $note_id ?>')">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
                <?php endif; ?>
            </div>
            
            <div style="font-size:14px;color:#4b5563;margin-bottom:15px;white-space:pre-wrap;">
                <?= esc_html($note['content']) ?>
            </div>
            
            <div style="font-size:12px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:10px;">
                <div><strong>By:</strong> <?= esc_html($note['author']) ?></div>
                <div><strong>Visibility:</strong> <?= $note['visibility'] == 'general' ? 'Everyone' : esc_html($messaging_groups[$note['group_id']]['name'] ?? 'Unknown Group') ?></div>
                <div><strong>Created:</strong> <?= date('d.m.Y H:i', strtotime($note['created'])) ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <?php endif; ?>
    
    <!-- Add/Edit Note Modal -->
    <?php if (current_user_can('manage_options')): ?>
    <div id="noteModal" style="display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);">
        <div style="background:white;margin:5% auto;padding:30px;width:90%;max-width:600px;border-radius:12px;position:relative;">
            <span onclick="document.getElementById('noteModal').style.display='none'" style="position:absolute;right:20px;top:20px;font-size:28px;font-weight:bold;color:#999;cursor:pointer;">&times;</span>
            <h2 style="margin:0 0 20px 0;"><i class="fa-solid fa-note-sticky"></i> <span id="noteModalTitle">New Note</span></h2>
            <form id="noteForm">
                <input type="hidden" id="note_id">
                
                <label style="display:block;margin-bottom:5px;font-weight:600;">Title</label>
                <input type="text" id="note_title" required style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;margin-bottom:15px;">
                
                <label style="display:block;margin-bottom:5px;font-weight:600;">Content</label>
                <textarea id="note_content" required style="width:100%;height:150px;padding:10px;border:1px solid #d1d5db;border-radius:6px;margin-bottom:15px;resize:vertical;"></textarea>
                
                <label style="display:block;margin-bottom:5px;font-weight:600;">Visibility</label>
                <select id="note_visibility" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;margin-bottom:15px;">
                    <option value="general">General (Everyone)</option>
                    <?php foreach ($messaging_groups as $group_id => $group): ?>
                        <option value="group" data-group-id="<?= $group_id ?>"><?= esc_html($group['name']) ?> Only</option>
                    <?php endforeach; ?>
                </select>
                
                <button type="submit" class="btn" style="width:100%;">
                    <i class="fa-solid fa-save"></i> Save Note
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    const notes = <?= json_encode($notes) ?>;
    
    function openNoteModal() {
        document.getElementById('noteModalTitle').textContent = 'New Note';
        document.getElementById('note_id').value = '';
        document.getElementById('note_title').value = '';
        document.getElementById('note_content').value = '';
        document.getElementById('note_visibility').selectedIndex = 0;
        document.getElementById('noteModal').style.display = 'block';
    }
    
    function editNote(noteId) {
        const note = notes[noteId];
        document.getElementById('noteModalTitle').textContent = 'Edit Note';
        document.getElementById('note_id').value = noteId;
        document.getElementById('note_title').value = note.title;
        document.getElementById('note_content').value = note.content;
        
        if (note.visibility === 'general') {
            document.getElementById('note_visibility').selectedIndex = 0;
        } else {
            const options = document.querySelectorAll('#note_visibility option');
            options.forEach((opt, idx) => {
                if (opt.getAttribute('data-group-id') === note.group_id) {
                    document.getElementById('note_visibility').selectedIndex = idx;
                }
            });
        }
        
        document.getElementById('noteModal').style.display = 'block';
    }
    
    function deleteNote(noteId) {
        if (!confirm('Delete this note?')) return;
        
        const formData = new FormData();
        formData.append('action', 'b2b_delete_note');
        formData.append('note_id', noteId);
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting note');
            }
        });
    }
    
    document.getElementById('noteForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const select = document.getElementById('note_visibility');
        const selectedOption = select.options[select.selectedIndex];
        const visibility = selectedOption.value;
        const groupId = selectedOption.getAttribute('data-group-id') || '';
        
        const formData = new FormData();
        formData.append('action', 'b2b_save_note');
        formData.append('note_id', document.getElementById('note_id').value);
        formData.append('title', document.getElementById('note_title').value);
        formData.append('content', document.getElementById('note_content').value);
        formData.append('visibility', visibility);
        formData.append('group_id', groupId);
        
        fetch(ajaxurl, {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error saving note: ' + (data.data || 'Unknown error'));
            }
        });
    });
    </script>
    
    <?php
    b2b_adm_footer();
    exit;
});

// Add unread message count to dashboard
add_action('wp_footer', function() {
    if (!is_user_logged_in() || !strpos($_SERVER['REQUEST_URI'], '/b2b-panel')) return;
    
    $user_id = get_current_user_id();
    $unread = get_user_meta($user_id, 'b2b_unread_messages', true) ?: 0;
    
    if ($unread > 0) {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messagingLink = document.querySelector('a[href*="/b2b-panel/messaging"]');
            if (messagingLink && !messagingLink.querySelector('.badge')) {
                const badge = document.createElement('span');
                badge.className = 'badge';
                badge.style.cssText = 'background:#ef4444;color:#fff;padding:2px 6px;border-radius:10px;font-size:11px;margin-left:8px;';
                badge.textContent = '<?= $unread ?>';
                messagingLink.appendChild(badge);
            }
        });
        </script>
        <?php
    }
});

// Display recent notes on dashboard
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'dashboard') return;
    
    // This will be displayed on dashboard - let me add it there
}, 999);

// End of Messaging and Notes System

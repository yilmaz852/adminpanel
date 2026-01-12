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
	// Customers (New)
    add_rewrite_rule('^b2b-panel/customers/?$', 'index.php?b2b_adm_page=customers', 'top');
    add_rewrite_rule('^b2b-panel/customers/edit/?$', 'index.php?b2b_adm_page=customer_edit', 'top');
    
    // Ürünler Listesi
    add_rewrite_rule('^b2b-panel/products/?$', 'index.php?b2b_adm_page=products', 'top');
    
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

    // 3. Otomatik Flush (Bunu sadece 1 kere çalıştırıp veritabanını günceller)
    if (!get_option('b2b_rewrite_v17_shipping')) {
        flush_rewrite_rules();
        update_option('b2b_rewrite_v17_shipping', true);
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
 * Get B2B shipping zone extensions for WooCommerce zones
 * Stores B2B-specific settings (group permissions, etc.) linked to WC zones
 */
function b2b_get_zone_extensions() {
    return get_option('b2b_zone_extensions', []);
}

/**
 * Update B2B zone extension
 */
function b2b_update_zone_extension($zone_id, $extension_data) {
    $extensions = b2b_get_zone_extensions();
    $extensions[$zone_id] = $extension_data;
    update_option('b2b_zone_extensions', $extensions);
}

/**
 * Get all shipping zones (from WooCommerce)
 */
function b2b_get_all_shipping_zones() {
    if(!class_exists('WC_Shipping_Zones')) {
        return [];
    }
    
    $wc_zones = WC_Shipping_Zones::get_zones();
    $zones = [];
    $extensions = b2b_get_zone_extensions();
    
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
                    'title' => $method->get_title()
                ];
            }
            if($method->id == 'free_shipping' && $method->enabled == 'yes') {
                $free_ship_data = [
                    'enabled' => 1,
                    'min_amount' => floatval($method->get_option('min_amount', 0)),
                    'title' => $method->get_title()
                ];
            }
        }
        
        // Get B2B extension data
        $extension = $extensions[$zone_id] ?? [];
        
        $zones[$zone_id] = [
            'name' => $wc_zone_data['zone_name'],
            'regions' => $regions,
            'active' => 1, // WC zones are always active if they exist
            'priority' => $wc_zone_data['zone_order'],
            'methods' => [
                'flat_rate' => $flat_rate_data,
                'free_shipping' => $free_ship_data
            ],
            'group_permissions' => $extension['group_permissions'] ?? []
        ];
    }
    
    return $zones;
}

// Add B2B shipping methods to WooCommerce checkout
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

    wp_send_json_success([
        'id' => $order->get_id(),
        'date' => $order->get_date_created()->date('d.m.Y H:i'),
        'billing' => $order->get_formatted_billing_address() ?: 'No address',
        'shipping' => $order->get_formatted_shipping_address() ?: 'No address',
        'items' => $items,
        'grand_total' => $order->get_formatted_order_total(),
        // Notlar + Teslimat Bilgisi + Loglar (Hepsi birleşti)
        'extra_html' => $notes . $ops_html . $logs_html 
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
    <style>
        :root{--primary:#0f172a;--accent:#3b82f6;--bg:#f3f4f6;--white:#ffffff;--border:#e5e7eb;--text:#1f2937}
        body{margin:0;font-family:'Inter',sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;font-size:14px}
        
        .sidebar{width:260px;background:var(--primary);color:#9ca3af;flex-shrink:0;position:fixed;height:100%;z-index:100;display:flex;flex-direction:column}
        .sidebar-head{padding:25px;color:var(--white);font-weight:700;font-size:1.2rem;border-bottom:1px solid rgba(255,255,255,0.1)}
        .sidebar-nav{padding:20px 10px;flex:1}
        .sidebar-nav a{display:flex;align-items:center;gap:12px;padding:12px 15px;color:inherit;text-decoration:none;border-radius:8px;margin-bottom:5px;transition:0.2s}
        .sidebar-nav a:hover, .sidebar-nav a.active{background:rgba(255,255,255,0.1);color:var(--white)}
        .sidebar-nav a.active{background:var(--accent)}
        .main{margin-left:260px;flex:1;padding:40px;width:100%}
        
        .card{background:var(--white);border-radius:12px;box-shadow:0 1px 3px rgba(0,0,0,0.05);padding:25px;border:1px solid var(--border);margin-bottom:25px}
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:24px;font-weight:700;color:var(--primary);margin:0}
        
        input,select,textarea{width:100%;padding:10px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;box-sizing:border-box;margin-bottom:15px}
        button{background:var(--accent);color:var(--white);border:none;padding:10px 20px;border-radius:6px;cursor:pointer;font-weight:600;font-size:14px;transition:0.2s}
        button:hover{background:#2563eb}
        button.secondary{background:var(--white);border:1px solid #d1d5db;color:#374151}
        button.secondary:hover{background:#f9fafb}
        
        table{width:100%;border-collapse:collapse;font-size:13px}
        th{background:#f8fafc;padding:12px;text-align:left;font-weight:600;color:#4b5563;border-bottom:1px solid var(--border);text-transform:uppercase;font-size:11px}
        td{padding:12px;border-bottom:1px solid var(--border);vertical-align:middle}
        
        /* Stats Box (Warehouse Style) */
        .stats-box { background:#eff6ff; border:1px solid #dbeafe; color:#1e40af; padding:15px; border-radius:8px; margin-bottom:20px; display:flex; align-items:center; gap:30px; }
        .stat-item { display:flex; flex-direction:column; }
        .stat-label { font-size:11px; text-transform:uppercase; color:#60a5fa; font-weight:700 }
        .stat-val { font-size:20px; font-weight:600; line-height:1.2 }
        .stat-oldest { color: #dc2626; }

        /* Column Edit Dropdown */
        .col-toggler { position:relative; display:inline-block; }
        .col-dropdown { display:none; position:absolute; right:0; top:100%; background:#fff; border:1px solid #ddd; box-shadow:0 4px 6px rgba(0,0,0,0.1); padding:10px; z-index:99; min-width:150px; border-radius:6px; }
        .col-dropdown.active { display:block; }
        .col-dropdown label { display:block; padding:5px 0; cursor:pointer; font-weight:normal; }
        .col-dropdown input { width:auto; margin-right:8px; }

        /* Dashboard Widgets */
        .dash-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(220px, 1fr));gap:20px}
        .dash-card{background:var(--white);border:1px solid var(--border);border-radius:10px;padding:20px;display:flex;flex-direction:column;justify-content:space-between;height:120px;text-decoration:none;color:inherit;transition:0.2s}
        .dash-card:hover{transform:translateY(-3px);box-shadow:0 10px 20px rgba(0,0,0,0.05)}
        .dash-card.warning{border-color:#fca5a5;background:#fef2f2}
        .dash-card.warning .dash-label{color:#ef4444}

        /* Modal */
        .modal{display:none;position:fixed;z-index:999;left:0;top:0;width:100%;height:100%;background:rgba(0,0,0,0.5);align-items:center;justify-content:center;backdrop-filter:blur(2px)}
        .modal-content{background:var(--white);width:95%;max-width:750px;border-radius:12px;overflow:hidden;box-shadow:0 20px 25px -5px rgba(0,0,0,0.1)}
        
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
            body{flex-direction:column;}
            .sidebar{width:100%;height:auto;position:relative;}
            .sidebar-nav{padding:10px;display:flex;flex-wrap:wrap;gap:5px;}
            .sidebar-nav a, .submenu-toggle{flex:1 1 auto;min-width:120px;}
            .submenu{padding-left:0;}
            .main{margin-left:0;padding:20px;width:100%;}
            .page-header{flex-direction:column;align-items:flex-start;gap:15px;}
            .dash-grid{grid-template-columns:repeat(auto-fill, minmax(140px, 1fr));}
            table{font-size:11px;}
            th,td{padding:8px 6px;}
            .stats-box{flex-wrap:wrap;}
        }
        @media (max-width: 480px) {
            .main{padding:15px;}
            .card, .customer-section{padding:15px;}
            .stats-box{flex-direction:column;gap:10px;}
            button{padding:8px 15px;font-size:13px;}
            .dash-grid{grid-template-columns:1fr;}
        }
    </style>
    </head>
    <body>

    <div class="sidebar">
        <div class="sidebar-head"><i class="fa-solid fa-shield-halved"></i> ADMIN PANEL V10</div>
        <div class="sidebar-nav">
            <a href="<?= home_url('/b2b-panel') ?>" class="<?= get_query_var('b2b_adm_page')=='dashboard'?'active':'' ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
            <a href="<?= home_url('/b2b-panel/orders') ?>" class="<?= get_query_var('b2b_adm_page')=='orders'?'active':'' ?>"><i class="fa-solid fa-box"></i> Orders</a>
            
            <!-- Products Module with Submenu -->
            <div class="submenu-toggle <?= in_array(get_query_var('b2b_adm_page'), ['products','product_edit','products_import','products_export','products_categories','category_edit','price_adjuster'])?'active':'' ?>" onclick="toggleSubmenu(this)">
                <i class="fa-solid fa-tags"></i> Products <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="submenu <?= in_array(get_query_var('b2b_adm_page'), ['products','product_edit','products_import','products_export','products_categories','category_edit','price_adjuster'])?'active':'' ?>">
                <a href="<?= home_url('/b2b-panel/products') ?>" class="<?= get_query_var('b2b_adm_page')=='products'||get_query_var('b2b_adm_page')=='product_edit'?'active':'' ?>"><i class="fa-solid fa-list"></i> All Products</a>
                <a href="<?= home_url('/b2b-panel/products/categories') ?>" class="<?= get_query_var('b2b_adm_page')=='products_categories'||get_query_var('b2b_adm_page')=='category_edit'?'active':'' ?>"><i class="fa-solid fa-folder-tree"></i> Categories</a>
                <a href="<?= home_url('/b2b-panel/products/price-adjuster') ?>" class="<?= get_query_var('b2b_adm_page')=='price_adjuster'?'active':'' ?>"><i class="fa-solid fa-dollar-sign"></i> Price Adjuster</a>
                <a href="<?= home_url('/b2b-panel/products/import') ?>" class="<?= get_query_var('b2b_adm_page')=='products_import'?'active':'' ?>"><i class="fa-solid fa-file-import"></i> Import</a>
                <a href="<?= home_url('/b2b-panel/products/export') ?>" class="<?= get_query_var('b2b_adm_page')=='products_export'?'active':'' ?>"><i class="fa-solid fa-file-export"></i> Export</a>
            </div>
            
            <a href="<?= home_url('/b2b-panel/customers') ?>" class="<?= get_query_var('b2b_adm_page')=='customers'||get_query_var('b2b_adm_page')=='customer_edit'?'active':'' ?>"><i class="fa-solid fa-users"></i> Customers</a>
            
            <!-- B2B Module with Submenu -->
            <div class="submenu-toggle <?= in_array(get_query_var('b2b_adm_page'), ['b2b_approvals','b2b_groups','b2b_settings','b2b_form_editor','b2b_roles'])?'active':'' ?>" onclick="toggleSubmenu(this)">
                <i class="fa-solid fa-layer-group"></i> B2B Module <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="submenu <?= in_array(get_query_var('b2b_adm_page'), ['b2b_approvals','b2b_groups','b2b_settings','b2b_form_editor','b2b_roles'])?'active':'' ?>">
                <a href="<?= home_url('/b2b-panel/b2b-module') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_approvals'?'active':'' ?>"><i class="fa-solid fa-user-check"></i> Approvals</a>
                <a href="<?= home_url('/b2b-panel/b2b-module/groups') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_groups'?'active':'' ?>"><i class="fa-solid fa-users-gear"></i> Groups</a>
                <a href="<?= home_url('/b2b-panel/b2b-module/roles') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_roles'?'active':'' ?>"><i class="fa-solid fa-user-tag"></i> Roles</a>
                <a href="<?= home_url('/b2b-panel/b2b-module/settings') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_settings'?'active':'' ?>"><i class="fa-solid fa-sliders"></i> Settings</a>
                <a href="<?= home_url('/b2b-panel/b2b-module/form-editor') ?>" class="<?= get_query_var('b2b_adm_page')=='b2b_form_editor'?'active':'' ?>"><i class="fa-solid fa-pen-to-square"></i> Form Editor</a>
            </div>
            
            <!-- Settings Module with Submenu -->
            <div class="submenu-toggle <?= in_array(get_query_var('b2b_adm_page'), ['settings_general','settings_tax','settings_shipping','shipping_zone_edit','sales_agent'])?'active':'' ?>" onclick="toggleSubmenu(this)">
                <i class="fa-solid fa-gear"></i> Settings <i class="fa-solid fa-chevron-down"></i>
            </div>
            <div class="submenu <?= in_array(get_query_var('b2b_adm_page'), ['settings_general','settings_tax','settings_shipping','shipping_zone_edit','sales_agent'])?'active':'' ?>">
                <a href="<?= home_url('/b2b-panel/settings') ?>" class="<?= get_query_var('b2b_adm_page')=='settings_general'?'active':'' ?>"><i class="fa-solid fa-sliders"></i> General</a>
                <a href="<?= home_url('/b2b-panel/settings/tax-exemption') ?>" class="<?= get_query_var('b2b_adm_page')=='settings_tax'?'active':'' ?>"><i class="fa-solid fa-receipt"></i> Tax Exemption</a>
                <a href="<?= home_url('/b2b-panel/settings/shipping') ?>" class="<?= in_array(get_query_var('b2b_adm_page'), ['settings_shipping','shipping_zone_edit'])?'active':'' ?>"><i class="fa-solid fa-truck"></i> Shipping</a>
                <a href="<?= home_url('/b2b-panel/sales-agent') ?>" class="<?= get_query_var('b2b_adm_page')=='sales_agent'?'active':'' ?>"><i class="fa-solid fa-user-tie"></i> Sales Agent</a>
            </div>
        </div>
        <div style="margin-top:auto;padding:20px">
            <a href="<?= wp_logout_url(home_url('/b2b-login')) ?>" style="color:#fca5a5;text-decoration:none;font-weight:600;display:flex;align-items:center;gap:10px"><i class="fa-solid fa-power-off"></i> Logout</a>
        </div>
    </div>

    <div class="main">
    <script>
    function toggleSubmenu(el) {
        el.classList.toggle('active');
        el.nextElementSibling.classList.toggle('active');
    }
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

    <div class="page-header"><h1 class="page-title">Overview</h1></div>

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

    <h3 style="margin-bottom:20px;color:#4b5563">Order Status & Delays</h3>
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

    <div class="card" style="margin-top:30px">
        <h3 style="margin-top:0">Sales Agent Performance</h3>
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
                        <label><input type="checkbox" checked data-col="5"> Status</label>
                        <label><input type="checkbox" checked data-col="6"> Action</label>
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
                <th data-col="5">Status</th>
                <th data-col="6" style="text-align:right">Action</th>
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
                <td data-col="5" style="width:160px">
                    <select onchange="updateStatus(<?=$oid?>, this.value)" style="padding:5px;font-size:12px;margin:0">
                        <?php foreach($all_statuses as $k=>$v): $slug=str_replace('wc-','',$k); ?><option value="<?=$slug?>" <?=selected('wc-'.$o->get_status(),$k)?>><?=$v?></option><?php endforeach; ?>
                    </select>
                </td>
                <td data-col="6" style="text-align:right;display:flex;gap:5px;justify-content:flex-end">
                    <button class="secondary" onclick="viewOrder(<?=$oid?>)" style="padding:6px 10px"><i class="fa-regular fa-eye"></i></button>
                    <?=$pdf_btn?>
                </td>
            </tr>
            <?php endwhile; else: ?><tr><td colspan="7" style="padding:20px;text-align:center">No orders found.</td></tr><?php endif; ?>
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
        <button id="quickEditToggle" onclick="toggleQuickEdit()" class="secondary" style="display:flex;align-items:center;gap:8px;">
            <i class="fa-solid fa-bolt"></i> Quick Edit Stock
        </button>
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
        
        <!-- Enhanced Product Table -->
        <table id="prodTable">
            <thead>
                <tr>
                    <th data-col="0">Image</th>
                    <th data-col="1">Name</th>
                    <th data-col="2">SKU</th>
                    <th data-col="3">Category</th>
                    <th data-col="4">Price</th>
                    <th data-col="5">Stock</th>
                    <th data-col="6">Status</th>
                    <th data-col="7" style="text-align:right">Action</th>
                </tr>
            </thead>
            <tbody>
            <?php if(empty($products->products)): ?>
                <tr><td colspan="8" style="text-align:center;padding:30px;color:#999">No products found.</td></tr>
            <?php else: foreach ($products->products as $p): 
                $img = wp_get_attachment_image_src($p->get_image_id(),'thumbnail');
                $cats = wp_get_post_terms($p->get_id(), 'product_cat', ['fields' => 'names']);
            ?>
            <tr data-product-id="<?= $p->get_id() ?>">
                <td data-col="0"><img src="<?= $img ? $img[0] : 'https://via.placeholder.com/40' ?>" style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #e5e7eb;"></td>
                <td data-col="1"><strong><?= esc_html($p->get_name()) ?></strong></td>
                <td data-col="2"><code style="background:#f3f4f6;padding:3px 8px;border-radius:4px;font-size:11px;"><?= esc_html($p->get_sku() ?: '-') ?></code></td>
                <td data-col="3"><small style="color:#6b7280;"><?= !empty($cats) ? esc_html(implode(', ', $cats)) : '-' ?></small></td>
                <td data-col="4"><strong><?= $p->get_price_html() ?></strong></td>
                <td data-col="5" class="stock-cell">
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
                <td data-col="6">
                    <?php 
                    $status = $p->get_status();
                    $status_color = $status == 'publish' ? '#d1fae5' : '#fee2e2';
                    $status_text_color = $status == 'publish' ? '#065f46' : '#991b1b';
                    ?>
                    <span style="background:<?= $status_color ?>;color:<?= $status_text_color ?>;padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;text-transform:uppercase;"><?= $status ?></span>
                </td>
                <td data-col="7" style="text-align:right;">
                    <a href="<?= home_url('/b2b-panel/products/edit?id=' . $p->get_id()) ?>">
                        <button class="secondary" style="padding:6px 12px;font-size:12px;"><i class="fa-solid fa-pen"></i> Edit</button>
                    </a>
                    <button class="delete-product-btn" data-product-id="<?= $p->get_id() ?>" data-product-name="<?= esc_attr($p->get_name()) ?>" style="padding:6px 12px;font-size:12px;background:#dc2626;color:white;border:none;border-radius:5px;cursor:pointer;margin-left:5px;"><i class="fa-solid fa-trash"></i></button>
                </td>
            </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
        
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
        for(var i=0; i<rows.length; i++) { 
            if(rows[i].cells.length > idx) rows[i].cells[idx].style.display = show ? '' : 'none'; 
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

    <div style="margin-bottom:20px;display:inline-flex;gap:15px;align-items:center">
        <a href="<?= home_url('/b2b-panel/products') ?>" style="text-decoration:none;color:#6b7280;font-size:14px;display:inline-flex;align-items:center;gap:5px;"><i class="fa-solid fa-arrow-left"></i> Back</a>
        <button id="delete-product-detail-btn" data-product-id="<?= $id ?>" data-product-name="<?= esc_attr($p->get_name()) ?>" style="padding:6px 10px;background:#fee2e2;color:#dc2626;border:1px solid #fecaca;border-radius:5px;cursor:pointer;font-size:18px;line-height:1;" title="Delete Product"><i class="fa-solid fa-trash"></i></button>
    </div>
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
                    <?php else: ?>
                        <div><label>Base Price (Optional)</label><input type="number" step="0.01" name="price" value="<?= $p->get_regular_price() ?>"></div>
                    <?php endif; ?>
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
                <button style="width:100%;padding:12px">Save Changes</button>
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
   11. PAGE: CUSTOMERS (B2BKING FIXED + FILTERS)
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
        
        // Get filter parameters
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
        
        // Get all groups and roles for filter dropdowns
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
                    
                    <!-- B2B Group Filter -->
                    <select onchange="window.location.href='<?= home_url('/b2b-panel/customers') ?>?filter_group='+this.value+'<?= $per_page != 20 ? '&per_page='.$per_page : '' ?><?= $s ? '&s='.urlencode($s) : '' ?><?= $filter_role ? '&filter_role='.urlencode($filter_role) : '' ?>'" style="margin:0;max-width:150px;">
                        <option value="">All Groups</option>
                        <?php foreach($all_groups as $slug => $group): ?>
                            <option value="<?= esc_attr($slug) ?>" <?= selected($filter_group, $slug) ?>><?= esc_html($group['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <!-- B2B Role Filter -->
                    <select onchange="window.location.href='<?= home_url('/b2b-panel/customers') ?>?filter_role='+this.value+'<?= $per_page != 20 ? '&per_page='.$per_page : '' ?><?= $s ? '&s='.urlencode($s) : '' ?><?= $filter_group ? '&filter_group='.urlencode($filter_group) : '' ?>'" style="margin:0;max-width:150px;">
                        <option value="">All Roles</option>
                        <?php foreach($all_roles as $role): ?>
                            <option value="<?= esc_attr($role['slug']) ?>" <?= selected($filter_role, $role['slug']) ?>><?= esc_html($role['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <?php if($filter_group || $filter_role): ?>
                        <a href="<?= home_url('/b2b-panel/customers') ?>?<?= $per_page != 20 ? 'per_page='.$per_page : '' ?><?= $s ? ($per_page != 20 ? '&' : '').'s='.urlencode($s) : '' ?>" style="padding:10px;color:#ef4444;text-decoration:none;white-space:nowrap;"><i class="fa-solid fa-times"></i> Clear Filters</a>
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
                        $b2b_roles = get_option('b2b_roles', [
                            ['slug' => 'customer', 'name' => 'Customer'],
                            ['slug' => 'wholesaler', 'name' => 'Wholesaler'],
                            ['slug' => 'retailer', 'name' => 'Retailer']
                        ]);
                        foreach($b2b_roles as $role) {
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
    if (get_query_var('b2b_adm_page') !== 'sales_agent') return;
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
            
            echo '<div style="background:#d1fae5;color:#065f46;padding:15px;margin-bottom:20px;border-radius:8px;border:1px solid #a7f3d0;"><i class="fa-solid fa-check-circle"></i> Settings saved successfully!</div>';
        }
    }
    
    // Get current settings
    $panel_title = get_option('sales_panel_title', 'Sales Agent Panel');
    $commission_rate = get_option('sales_commission_rate', 10);
    $stale_days = get_option('sales_stale_days', 30);
    $merge_products = get_option('sales_merge_products', 0);
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
            
            <div style="margin-bottom:0;">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="sales_merge_products" value="1" <?= checked($merge_products, 1, false) ?> 
                           style="width:18px;height:18px;cursor:pointer;">
                    <span style="font-weight:600;color:#374151;">
                        <i class="fa-solid fa-layer-group"></i> Merge Duplicate Products in Cart
                    </span>
                </label>
                <p style="color:#6b7280;font-size:12px;margin:5px 0 0 28px;">Automatically combine duplicate products into a single cart item</p>
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
   SHIPPING MODULE - PHASE 1 (WooCommerce Integration)
===================================================== */
// Shipping Page
add_action('template_redirect', function () {
    if (get_query_var('b2b_adm_page') !== 'settings_shipping') return;
    b2b_adm_guard();
    
    // Check if WooCommerce is available
    if(!class_exists('WC_Shipping_Zones')) {
        b2b_adm_header('Shipping Settings');
        ?>
        <div class="page-header"><h1 class="page-title">Shipping Zones</h1></div>
        <div class="card">
            <p style="color:#dc2626;"><i class="fa-solid fa-exclamation-triangle"></i> WooCommerce is not active. Shipping zones are managed through WooCommerce.</p>
            <p>Please activate WooCommerce to manage shipping zones.</p>
        </div>
        <?php
        b2b_adm_footer();
        exit;
    }
    
    // Handle B2B extension save (group permissions only)
    $message = '';
    if(isset($_POST['save_b2b_settings'])) {
        $zone_id = intval($_POST['zone_id']);
        
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
        
        // Save B2B extension
        b2b_update_zone_extension($zone_id, [
            'group_permissions' => $group_permissions
        ]);
        
        $message = '<div style="padding:15px;background:#d1fae5;color:#065f46;border-radius:8px;margin-bottom:20px;"><strong>Success!</strong> B2B settings saved for this zone.</div>';
    }
    
    // Get all zones from WooCommerce
    $zones = b2b_get_all_shipping_zones();
    $edit_zone = null;
    $edit_id = '';
    if(isset($_GET['edit'])) {
        $edit_id = intval($_GET['edit']);
        $edit_zone = $zones[$edit_id] ?? null;
    }
    
    b2b_adm_header('Shipping Settings');
    
    echo $message;
    ?>
    <div class="page-header">
        <h1 class="page-title">Shipping Zones</h1>
    </div>
    
    <div style="background:#eff6ff;border:1px solid#bfdbfe;border-radius:8px;padding:15px;margin-bottom:20px;">
        <p style="margin:0;color:#1e40af;"><i class="fa-solid fa-info-circle"></i> <strong>Note:</strong> Shipping zones are managed through WooCommerce. Here you can configure B2B-specific settings like group permissions and custom rates.</p>
        <p style="margin:5px 0 0 0;color:#1e40af;"><a href="<?= admin_url('admin.php?page=wc-settings&tab=shipping') ?>" target="_blank" style="color:#3b82f6;font-weight:600;">Manage WooCommerce Shipping Zones →</a></p>
    </div>
    
    <?php if($edit_zone): ?>
    <!-- Edit Zone B2B Settings -->
    <div class="card" style="margin-bottom:20px;">
        <h3 style="margin-top:0;">B2B Settings for: <?= esc_html($edit_zone['name']) ?></h3>
        
        <div style="background:#f0f9ff;border:1px solid #bfdbfe;border-radius:8px;padding:15px;margin-bottom:20px;">
            <h4 style="margin:0 0 10px 0;color:#1e40af;"><i class="fa-solid fa-info-circle"></i> Zone Information (From WooCommerce)</h4>
            <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:15px;color:#1e40af;">
                <div>
                    <strong>Regions:</strong> <?= esc_html(implode(', ', $edit_zone['regions'] ?? [])) ?: 'All regions' ?>
                </div>
                <div>
                    <strong>Priority:</strong> <?= esc_html($edit_zone['priority'] ?? 1) ?>
                </div>
                <div>
                    <strong>Flat Rate:</strong> 
                    <?php if($edit_zone['methods']['flat_rate']['enabled'] ?? 0): ?>
                        <?= esc_html($edit_zone['methods']['flat_rate']['title']) ?> - $<?= number_format($edit_zone['methods']['flat_rate']['cost'], 2) ?>
                    <?php else: ?>
                        <span style="color:#6b7280;">Not enabled</span>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Free Shipping:</strong>
                    <?php if($edit_zone['methods']['free_shipping']['enabled'] ?? 0): ?>
                        <?= esc_html($edit_zone['methods']['free_shipping']['title']) ?> 
                        <?php 
                        $min = $edit_zone['methods']['free_shipping']['min_amount'] ?? 0;
                        echo $min > 0 ? '(min $'.number_format($min, 2).')' : '(Always free)';
                        ?>
                    <?php else: ?>
                        <span style="color:#6b7280;">Not enabled</span>
                    <?php endif; ?>
                </div>
            </div>
            <p style="margin:10px 0 0 0;color:#1e40af;"><a href="<?= admin_url('admin.php?page=wc-settings&tab=shipping&zone_id='.$edit_id) ?>" target="_blank" style="color:#3b82f6;font-weight:600;">Edit in WooCommerce →</a></p>
        </div>
        
        <form method="POST">
            <input type="hidden" name="zone_id" value="<?= esc_attr($edit_id) ?>">
            
            <!-- Group Permissions -->
            <h4 style="margin-top:0;">Group-Based Permissions</h4>
            <p style="color:#6b7280;margin-bottom:20px;">Configure special rates for specific B2B groups. Leave unchecked to use WooCommerce default rates.</p>
            
            <?php
            // Get all B2B groups
            $b2b_groups = b2b_get_groups();
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
                    <?php if($edit_zone['methods']['flat_rate']['enabled'] ?? 0): ?>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;">Flat Rate Cost ($)</label>
                        <input type="number" name="group_permissions[<?= esc_attr($group_id) ?>][flat_rate_cost]" value="<?= esc_attr($group_perms['flat_rate_cost'] ?? '') ?>" step="0.01" min="0" placeholder="Default: <?= esc_attr($edit_zone['methods']['flat_rate']['cost'] ?? 0) ?>" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                        <small style="color:#6b7280;">Leave empty to use default ($<?= number_format($edit_zone['methods']['flat_rate']['cost'], 2) ?>)</small>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($edit_zone['methods']['free_shipping']['enabled'] ?? 0): ?>
                    <div>
                        <label style="display:block;margin-bottom:5px;font-size:13px;">Free Shipping Min ($)</label>
                        <input type="number" name="group_permissions[<?= esc_attr($group_id) ?>][free_shipping_min]" value="<?= esc_attr($group_perms['free_shipping_min'] ?? '') ?>" step="0.01" min="0" placeholder="Default: <?= esc_attr($edit_zone['methods']['free_shipping']['min_amount'] ?? 0) ?>" style="width:100%;padding:8px;border:1px solid #e5e7eb;border-radius:6px;">
                        <small style="color:#6b7280;">Leave empty to use default ($<?= number_format($edit_zone['methods']['free_shipping']['min_amount'], 2) ?>)</small>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div style="margin-left:30px;margin-top:10px;">
                    <label style="display:block;margin-bottom:5px;font-size:13px;">Hide Methods (Optional)</label>
                    <?php if($edit_zone['methods']['flat_rate']['enabled'] ?? 0): ?>
                    <label style="display:inline-flex;align-items:center;gap:5px;margin-right:15px;">
                        <input type="checkbox" name="group_permissions[<?= esc_attr($group_id) ?>][hidden_methods][]" value="flat_rate" <?= checked(in_array('flat_rate', $group_perms['hidden_methods'] ?? []), true) ?>>
                        <span style="font-size:13px;">Hide Flat Rate</span>
                    </label>
                    <?php endif; ?>
                    <?php if($edit_zone['methods']['free_shipping']['enabled'] ?? 0): ?>
                    <label style="display:inline-flex;align-items:center;gap:5px;">
                        <input type="checkbox" name="group_permissions[<?= esc_attr($group_id) ?>][hidden_methods][]" value="free_shipping" <?= checked(in_array('free_shipping', $group_perms['hidden_methods'] ?? []), true) ?>>
                        <span style="font-size:13px;">Hide Free Shipping</span>
                    </label>
                    <?php endif; ?>
                </div>
            </div>
            <?php 
                endforeach;
            else:
            ?>
            <p style="color:#6b7280;font-style:italic;">No B2B groups configured. Create groups in B2B Module → Groups.</p>
            <?php endif; ?>
            
            <button type="submit" name="save_b2b_settings" class="primary">Save B2B Settings</button>
            <a href="<?= home_url('/b2b-panel/settings/shipping') ?>" style="margin-left:10px;"><button type="button" class="secondary">Back to List</button></a>
        </form>
    </div>
    <?php else: ?>
    <!-- Zones List -->
    <div class="card">
        <h3 style="margin-top:0;">Shipping Zones</h3>
        <?php if(empty($zones)): ?>
            <p style="color:#6b7280;">No shipping zones configured yet.</p>
            <p><a href="<?= admin_url('admin.php?page=wc-settings&tab=shipping') ?>" target="_blank" style="color:#3b82f6;font-weight:600;">Create shipping zones in WooCommerce →</a></p>
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
                        <td><?= esc_html(implode(', ', $zone['regions'] ?? [])) ?: 'All regions' ?></td>
                        <td>
                            <?php 
                            $methods = [];
                            if($zone['methods']['flat_rate']['enabled'] ?? 0) $methods[] = 'Flat Rate ($'.number_format($zone['methods']['flat_rate']['cost'], 2).')';
                            if($zone['methods']['free_shipping']['enabled'] ?? 0) {
                                $min = $zone['methods']['free_shipping']['min_amount'] ?? 0;
                                $methods[] = 'Free Shipping' . ($min > 0 ? ' (min $'.number_format($min, 2).')' : '');
                            }
                            echo $methods ? implode('<br>', $methods) : '<span style="color:#6b7280;">No methods</span>';
                            ?>
                        </td>
                        <td>
                            <?php if($b2b_groups_count > 0): ?>
                                <span style="padding:3px 10px;border-radius:4px;font-size:11px;font-weight:600;background:#dbeafe;color:#1e40af;">
                                    <?= $b2b_groups_count ?> group<?= $b2b_groups_count > 1 ? 's' : '' ?>
                                </span>
                            <?php else: ?>
                                <span style="color:#6b7280;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="<?= home_url('/b2b-panel/settings/shipping?edit='.urlencode($zone_id)) ?>"><button class="secondary" style="padding:6px 12px;font-size:12px;"><i class="fa-solid fa-cog"></i> B2B Settings</button></a>
                            <a href="<?= admin_url('admin.php?page=wc-settings&tab=shipping&zone_id='.$zone_id) ?>" target="_blank"><button class="secondary" style="padding:6px 12px;font-size:12px;margin-left:5px;"><i class="fa-solid fa-external-link"></i> WC</button></a>
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

<?php
/**
 * Plugin Name: WooCommerce Üretim Planlama
 * Plugin URI: https://github.com/yilmaz852/woo-uretim-planlama
 * Description: WooCommerce siparişleri için üretim planlama, takvim, analiz ve raporlama eklentisi.
 * Version: 1.0.0
 * Author: Yilmaz
 * Author URI: https://github.com/yilmaz852
 * Text Domain: woo-uretim-planlama
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 6.0
 * WC tested up to: 8.7
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit;
}

// Sabitleri tanımla
define('WUP_VERSION', '1.0.0');
define('WUP_PLUGIN_FILE', __FILE__);
define('WUP_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('WUP_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WUP_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * WooCommerce aktif mi kontrol et
 */
function wup_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function() {
            echo '<div class="error"><p>';
            echo esc_html__('WooCommerce Üretim Planlama eklentisi için WooCommerce gereklidir.', 'woo-uretim-planlama');
            echo '</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Register custom order statuses
 */
function wup_register_order_statuses() {
    register_post_status('wc-in-production', array(
        'label'                     => _x('In Production', 'Order status', 'woo-uretim-planlama'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('In Production <span class="count">(%s)</span>', 'In Production <span class="count">(%s)</span>', 'woo-uretim-planlama')
    ));
    
    register_post_status('wc-cutting', array(
        'label'                     => _x('In Cutting', 'Order status', 'woo-uretim-planlama'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('In Cutting <span class="count">(%s)</span>', 'In Cutting <span class="count">(%s)</span>', 'woo-uretim-planlama')
    ));
    
    register_post_status('wc-sewing', array(
        'label'                     => _x('In Sewing', 'Order status', 'woo-uretim-planlama'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('In Sewing <span class="count">(%s)</span>', 'In Sewing <span class="count">(%s)</span>', 'woo-uretim-planlama')
    ));
    
    register_post_status('wc-quality-check', array(
        'label'                     => _x('Quality Control', 'Order status', 'woo-uretim-planlama'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Quality Control <span class="count">(%s)</span>', 'Quality Control <span class="count">(%s)</span>', 'woo-uretim-planlama')
    ));
    
    register_post_status('wc-packaging', array(
        'label'                     => _x('Packaging', 'Order status', 'woo-uretim-planlama'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Packaging <span class="count">(%s)</span>', 'Packaging <span class="count">(%s)</span>', 'woo-uretim-planlama')
    ));
    
    register_post_status('wc-ready-to-ship', array(
        'label'                     => _x('Ready to Ship', 'Order status', 'woo-uretim-planlama'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Ready to Ship <span class="count">(%s)</span>', 'Ready to Ship <span class="count">(%s)</span>', 'woo-uretim-planlama')
    ));
}
add_action('init', 'wup_register_order_statuses');

/**
 * Add custom statuses to WooCommerce
 */
function wup_add_custom_order_statuses($order_statuses) {
    $new_order_statuses = array();
    
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-in-production'] = _x('In Production', 'Order status', 'woo-uretim-planlama');
            $new_order_statuses['wc-cutting'] = _x('In Cutting', 'Order status', 'woo-uretim-planlama');
            $new_order_statuses['wc-sewing'] = _x('In Sewing', 'Order status', 'woo-uretim-planlama');
            $new_order_statuses['wc-quality-check'] = _x('Quality Control', 'Order status', 'woo-uretim-planlama');
            $new_order_statuses['wc-packaging'] = _x('Packaging', 'Order status', 'woo-uretim-planlama');
            $new_order_statuses['wc-ready-to-ship'] = _x('Ready to Ship', 'Order status', 'woo-uretim-planlama');
        }
    }
    
    return $new_order_statuses;
}
add_filter('wc_order_statuses', 'wup_add_custom_order_statuses');

/**
 * Eklentiyi başlat
 */
function wup_init() {
    if (!wup_check_woocommerce()) {
        return;
    }
    
    // Sınıfları yükle
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-cache.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-settings.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-ui.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-departments.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-product-routes.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-dashboard.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-analytics.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-scheduler.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-calendar.php';
    require_once WUP_PLUGIN_PATH . 'includes/class-wup-main.php';
    
    // Departman yönetimini başlat
    WUP_Departments::get_instance();
    
    // Ürün rotaları yönetimini başlat
    WUP_Product_Routes::get_instance();
    
    // Ana sınıfı başlat
    WUP_Main::get_instance();
}
add_action('plugins_loaded', 'wup_init', 10);

/**
 * Aktivasyon hook
 */
function wup_activate() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    
    // Order status history table
    $table_status_history = $wpdb->prefix . 'wup_status_history';
    $sql_status_history = "CREATE TABLE {$table_status_history} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id BIGINT UNSIGNED NOT NULL,
        status VARCHAR(50) NOT NULL,
        changed_at DATETIME NOT NULL,
        changed_by BIGINT UNSIGNED,
        notes TEXT,
        INDEX idx_order_id (order_id),
        INDEX idx_changed_at (changed_at)
    ) {$charset_collate};";
    dbDelta($sql_status_history);
    
    // Department schedule table
    $table_schedule = $wpdb->prefix . 'wup_schedule';
    $sql_schedule = "CREATE TABLE {$table_schedule} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id BIGINT UNSIGNED NOT NULL,
        department_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        quantity INT NOT NULL,
        scheduled_start DATETIME NOT NULL,
        scheduled_end DATETIME NOT NULL,
        actual_start DATETIME,
        actual_end DATETIME,
        status VARCHAR(20) DEFAULT 'scheduled',
        assigned_to BIGINT UNSIGNED,
        priority INT DEFAULT 5,
        notes TEXT,
        INDEX idx_order (order_id),
        INDEX idx_department (department_id),
        INDEX idx_dates (scheduled_start, scheduled_end),
        INDEX idx_status (status)
    ) {$charset_collate};";
    dbDelta($sql_schedule);
    
    // Departments table
    $table_departments = $wpdb->prefix . 'wup_departments';
    $sql_departments = "CREATE TABLE {$table_departments} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        capacity INT NOT NULL DEFAULT 10,
        working_hours JSON,
        is_active TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) {$charset_collate};";
    dbDelta($sql_departments);
    
    // Product routes table
    $table_product_routes = $wpdb->prefix . 'wup_product_routes';
    $sql_product_routes = "CREATE TABLE {$table_product_routes} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED NOT NULL,
        department_id BIGINT UNSIGNED NOT NULL,
        sequence_order INT NOT NULL,
        estimated_time INT NOT NULL COMMENT 'in minutes',
        INDEX idx_product (product_id)
    ) {$charset_collate};";
    dbDelta($sql_product_routes);
    
    // Varsayılan ayarları kaydet
    $default_settings = array(
        'personnel_count' => 1,
        'daily_hours' => 8,
        'working_days' => array('1', '2', '3', '4', '5'),
        'status_durations' => array(),
        'notifications_enabled' => 0,
        'notification_threshold' => 24,
        'notification_email' => get_option('admin_email'),
        'cache_duration' => 3600
    );
    
    if (!get_option('wup_settings')) {
        update_option('wup_settings', $default_settings);
    }
    
    update_option('wup_version', WUP_VERSION);
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'wup_activate');

/**
 * Deaktivasyon hook
 */
function wup_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'wup_deactivate');

/**
 * Dil dosyalarını yükle
 */
function wup_load_textdomain() {
    load_plugin_textdomain('woo-uretim-planlama', false, dirname(WUP_PLUGIN_BASENAME) . '/languages');
}
add_action('init', 'wup_load_textdomain');

/**
 * HPOS uyumluluğu
 */
add_action('before_woocommerce_init', function() {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
});

<?php
/**
 * =====================================================
 * PRODUCTION PLANNING MODULE
 * Admin-only access for production management
 * =====================================================
 * 
 * Features:
 * - Production scheduling and planning
 * - Order status tracking through production stages
 * - Department management (Cutting, Sewing, Quality Control, etc.)
 * - Visual calendar for production timeline
 * - Analytics and reporting
 * - Real-time dashboard
 * 
 * Architecture: Follows personnelpanel.php pattern
 * - Custom database tables for production tracking
 * - WordPress rewrite rules for clean URLs (/b2b-panel/production/*)
 * - Single-file organization with template_redirect hooks
 * - Compatible with WooCommerce orders but independent structure
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/* =====================================================
 * 1. DATABASE TABLES - PRODUCTION TRACKING
 * ===================================================== */
add_action('init', 'production_panel_check_tables', 5);
function production_panel_check_tables() {
    if (!get_option('production_panel_tables_created')) {
        production_panel_create_tables();
        add_option('production_panel_tables_created', true);
    }
}

function production_panel_create_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Production status history
    $table_status_history = $wpdb->prefix . 'production_status_history';
    $sql1 = "CREATE TABLE IF NOT EXISTS {$table_status_history} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id BIGINT UNSIGNED NOT NULL,
        status VARCHAR(50) NOT NULL,
        changed_at DATETIME NOT NULL,
        changed_by BIGINT UNSIGNED,
        notes TEXT,
        INDEX idx_order_id (order_id),
        INDEX idx_changed_at (changed_at)
    ) {$charset_collate};";
    dbDelta($sql1);
    
    // Production schedule
    $table_schedule = $wpdb->prefix . 'production_schedule';
    $sql2 = "CREATE TABLE IF NOT EXISTS {$table_schedule} (
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
    dbDelta($sql2);
    
    // Departments
    $table_departments = $wpdb->prefix . 'production_departments';
    $sql3 = "CREATE TABLE IF NOT EXISTS {$table_departments} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        capacity INT NOT NULL DEFAULT 10,
        workers INT NOT NULL DEFAULT 1,
        working_hours JSON,
        color VARCHAR(7) DEFAULT '#3498db',
        is_active TINYINT(1) DEFAULT 1,
        display_order INT DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) {$charset_collate};";
    dbDelta($sql3);
    
    // Product routes
    $table_routes = $wpdb->prefix . 'production_routes';
    $sql4 = "CREATE TABLE IF NOT EXISTS {$table_routes} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id BIGINT UNSIGNED NOT NULL,
        department_id BIGINT UNSIGNED NOT NULL,
        sequence_order INT NOT NULL,
        estimated_time INT NOT NULL COMMENT 'in minutes',
        dependencies TEXT COMMENT 'JSON array of department IDs that must complete first',
        setup_time INT DEFAULT 0 COMMENT 'setup time in minutes',
        notes TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_product (product_id),
        INDEX idx_sequence (product_id, sequence_order)
    ) {$charset_collate};";
    dbDelta($sql4);
    
    // Route templates (reusable workflows)
    $table_route_templates = $wpdb->prefix . 'production_route_templates';
    $sql5 = "CREATE TABLE IF NOT EXISTS {$table_route_templates} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(200) NOT NULL,
        description TEXT,
        route_data TEXT NOT NULL COMMENT 'JSON array of route steps',
        is_default TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) {$charset_collate};";
    dbDelta($sql5);
    
    // Resource allocation tracking
    $table_resources = $wpdb->prefix . 'production_resources';
    $sql6 = "CREATE TABLE IF NOT EXISTS {$table_resources} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        schedule_id BIGINT UNSIGNED NOT NULL,
        resource_type VARCHAR(50) NOT NULL COMMENT 'worker, machine, material',
        resource_id BIGINT UNSIGNED NOT NULL,
        quantity DECIMAL(10,2) DEFAULT 1,
        allocated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_schedule (schedule_id),
        INDEX idx_resource (resource_type, resource_id)
    ) {$charset_collate};";
    dbDelta($sql6);
    
    // Production cache table
    $table_cache = $wpdb->prefix . 'production_cache';
    $sql7 = "CREATE TABLE IF NOT EXISTS {$table_cache} (
        cache_key VARCHAR(191) PRIMARY KEY,
        cache_value LONGTEXT NOT NULL,
        expires_at DATETIME NOT NULL,
        INDEX idx_expires (expires_at)
    ) {$charset_collate};";
    dbDelta($sql7);
    
    // Cabinet Types
    $table_cabinet_types = $wpdb->prefix . 'production_cabinet_types';
    $sql8 = "CREATE TABLE IF NOT EXISTS {$table_cabinet_types} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        color VARCHAR(7) DEFAULT '#667eea',
        time_multiplier DECIMAL(3,2) DEFAULT 1.00,
        base_duration INT DEFAULT 0,
        is_active TINYINT(1) DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    ) {$charset_collate};";
    dbDelta($sql8);
    
    // Cabinet Type Workflows
    $table_type_workflows = $wpdb->prefix . 'production_type_workflows';
    $sql9 = "CREATE TABLE IF NOT EXISTS {$table_type_workflows} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        cabinet_type_id BIGINT UNSIGNED NOT NULL,
        department_id BIGINT UNSIGNED NOT NULL,
        sequence_order INT NOT NULL,
        duration_minutes INT DEFAULT 0,
        INDEX idx_cabinet_type (cabinet_type_id),
        INDEX idx_sequence (cabinet_type_id, sequence_order)
    ) {$charset_collate};";
    dbDelta($sql9);
    
    // Cabinet Type Categories Mapping
    $table_type_categories = $wpdb->prefix . 'production_type_categories';
    $sql10 = "CREATE TABLE IF NOT EXISTS {$table_type_categories} (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        cabinet_type_id BIGINT UNSIGNED NOT NULL,
        category_id BIGINT UNSIGNED NOT NULL,
        UNIQUE KEY unique_mapping (cabinet_type_id, category_id),
        INDEX idx_category (category_id)
    ) {$charset_collate};";
    dbDelta($sql10);
    
    // Initialize default settings
    if (!get_option('production_panel_settings')) {
        add_option('production_panel_settings', [
            'daily_hours' => 8,
            'working_days' => ['1', '2', '3', '4', '5'], // Monday to Friday
            'cache_duration' => 3600,
            'notifications_enabled' => 0
        ]);
    }
}

/* =====================================================
 * 2. CUSTOM ORDER STATUSES - PRODUCTION WORKFLOW
 * ===================================================== */
add_action('init', 'production_register_order_statuses');
function production_register_order_statuses() {
    // Awaiting Production
    register_post_status('wc-await-prod', [
        'label'                     => 'Awaiting Production',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Awaiting Production <span class="count">(%s)</span>', 'Awaiting Production <span class="count">(%s)</span>')
    ]);
    
    // In Production (General)
    register_post_status('wc-in-production', [
        'label'                     => 'In Production',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('In Production <span class="count">(%s)</span>', 'In Production <span class="count">(%s)</span>')
    ]);
    
    // Operation/Planning Stage
    register_post_status('wc-operation', [
        'label'                     => 'Operation',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Operation <span class="count">(%s)</span>', 'Operation <span class="count">(%s)</span>')
    ]);
    
    // Manufacturing/Production Stage
    register_post_status('wc-manufacturing', [
        'label'                     => 'Manufacturing',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Manufacturing <span class="count">(%s)</span>', 'Manufacturing <span class="count">(%s)</span>')
    ]);
    
    // Paint Shop
    register_post_status('wc-paint-shop', [
        'label'                     => 'Paint Shop',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Paint Shop <span class="count">(%s)</span>', 'Paint Shop <span class="count">(%s)</span>')
    ]);
    
    // Assembly
    register_post_status('wc-assembly', [
        'label'                     => 'Assembly',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Assembly <span class="count">(%s)</span>', 'Assembly <span class="count">(%s)</span>')
    ]);
    
    // Cutting
    register_post_status('wc-cutting', [
        'label'                     => 'Cutting',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Cutting <span class="count">(%s)</span>', 'Cutting <span class="count">(%s)</span>')
    ]);
    
    // Sewing
    register_post_status('wc-sewing', [
        'label'                     => 'Sewing',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Sewing <span class="count">(%s)</span>', 'Sewing <span class="count">(%s)</span>')
    ]);
    
    // Quality Control
    register_post_status('wc-quality-check', [
        'label'                     => 'Quality Control',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Quality Control <span class="count">(%s)</span>', 'Quality Control <span class="count">(%s)</span>')
    ]);
    
    // Packaging
    register_post_status('wc-packaging', [
        'label'                     => 'Packaging',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Packaging <span class="count">(%s)</span>', 'Packaging <span class="count">(%s)</span>')
    ]);
    
    // Ready to Ship
    register_post_status('wc-ready-to-ship', [
        'label'                     => 'Ready to Ship',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Ready to Ship <span class="count">(%s)</span>', 'Ready to Ship <span class="count">(%s)</span>')
    ]);
}

// Add custom statuses to WooCommerce order status dropdown
add_filter('wc_order_statuses', 'production_add_custom_statuses_to_dropdown');
function production_add_custom_statuses_to_dropdown($order_statuses) {
    $new_statuses = [];
    
    foreach ($order_statuses as $key => $status) {
        $new_statuses[$key] = $status;
        
        // Add production statuses after 'processing'
        if ($key === 'wc-processing') {
            $new_statuses['wc-await-prod'] = 'Awaiting Production';
            $new_statuses['wc-in-production'] = 'In Production';
            $new_statuses['wc-operation'] = 'Operation';
            $new_statuses['wc-manufacturing'] = 'Manufacturing';
            $new_statuses['wc-paint-shop'] = 'Paint Shop';
            $new_statuses['wc-assembly'] = 'Assembly';
            $new_statuses['wc-cutting'] = 'Cutting';
            $new_statuses['wc-sewing'] = 'Sewing';
            $new_statuses['wc-quality-check'] = 'Quality Control';
            $new_statuses['wc-packaging'] = 'Packaging';
            $new_statuses['wc-ready-to-ship'] = 'Ready to Ship';
        }
    }
    
    return $new_statuses;
}

/* =====================================================
 * 3. WOOCOMMERCE ORDER INTEGRATION FUNCTIONS
 * ===================================================== */

/**
 * Get WooCommerce orders for production scheduling
 */
function production_get_woo_orders($status = ['processing', 'pending', 'on-hold'], $limit = 100) {
    if (!function_exists('wc_get_orders')) {
        return [];
    }
    
    $args = [
        'limit' => $limit,
        'status' => $status,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'ids'
    ];
    
    $order_ids = wc_get_orders($args);
    $orders = [];
    
    foreach ($order_ids as $order_id) {
        $order = wc_get_order($order_id);
        if (!$order) continue;
        
        $orders[] = [
            'id' => $order->get_id(),
            'number' => $order->get_order_number(),
            'customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'total' => $order->get_total(),
            'status' => $order->get_status(),
            'date' => $order->get_date_created()->date('Y-m-d H:i:s'),
            'items' => $order->get_items()
        ];
    }
    
    return $orders;
}

/**
 * Get order details with products
 */
function production_get_order_details($order_id) {
    if (!function_exists('wc_get_order')) {
        return null;
    }
    
    $order = wc_get_order($order_id);
    if (!$order) return null;
    
    global $wpdb;
    $table_types = $wpdb->prefix . 'production_cabinet_types';
    $table_categories = $wpdb->prefix . 'production_type_categories';
    $table_workflows = $wpdb->prefix . 'production_type_workflows';
    $table_departments = $wpdb->prefix . 'production_departments';
    
    $items = [];
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $product_id = $item->get_product_id();
        
        // Get product categories
        $categories = [];
        if ($product) {
            $terms = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);
            $categories = $terms;
        }
        
        // Find cabinet type for this product
        $cabinet_type = null;
        if (!empty($categories)) {
            // Build safe IN clause with placeholders
            $category_ids = array_map('absint', $categories);
            $category_ids = array_filter($category_ids); // Remove zeros
            
            if (!empty($category_ids)) {
                $placeholders = implode(',', array_fill(0, count($category_ids), '%d'));
                
                $cabinet_type_data = $wpdb->get_row($wpdb->prepare("
                    SELECT t.* 
                    FROM {$table_types} t
                    INNER JOIN {$table_categories} tc ON t.id = tc.cabinet_type_id
                    WHERE tc.category_id IN ({$placeholders}) AND t.is_active = 1
                    LIMIT 1
                ", ...$category_ids));
                
                if ($cabinet_type_data) {
                    // Get workflows with department names
                    $workflows = $wpdb->get_results($wpdb->prepare("
                        SELECT w.*, d.name as dept_name
                        FROM {$table_workflows} w
                        LEFT JOIN {$table_departments} d ON w.department_id = d.id
                        WHERE w.cabinet_type_id = %d
                        ORDER BY w.sequence_order ASC
                    ", $cabinet_type_data->id));
                    
                    $cabinet_type = [
                        'id' => $cabinet_type_data->id,
                        'name' => $cabinet_type_data->name,
                        'color' => $cabinet_type_data->color,
                        'workflows' => $workflows
                    ];
                }
            }
        }
        
        $items[] = [
            'product_id' => $product_id,
            'name' => $item->get_name(),
            'quantity' => $item->get_quantity(),
            'sku' => $product ? $product->get_sku() : '',
            'image' => $product && $product->get_image_id() ? wp_get_attachment_url($product->get_image_id()) : '',
            'categories' => $categories,
            'cabinet_type' => $cabinet_type
        ];
    }
    
    return [
        'id' => $order->get_id(),
        'number' => $order->get_order_number(),
        'customer' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
        'email' => $order->get_billing_email(),
        'phone' => $order->get_billing_phone(),
        'total' => $order->get_total(),
        'status' => $order->get_status(),
        'date' => $order->get_date_created()->date('Y-m-d H:i:s'),
        'items' => $items
    ];
}

// Add to WooCommerce status list
add_filter('wc_order_statuses', 'production_add_to_order_statuses');
function production_add_to_order_statuses($order_statuses) {
    $order_statuses['wc-in-production'] = 'In Production';
    $order_statuses['wc-cutting'] = 'Cutting';
    $order_statuses['wc-sewing'] = 'Sewing';
    $order_statuses['wc-quality-check'] = 'Quality Control';
    $order_statuses['wc-packaging'] = 'Packaging';
    $order_statuses['wc-ready-to-ship'] = 'Ready to Ship';
    return $order_statuses;
}

// Log status changes to history
add_action('woocommerce_order_status_changed', 'production_log_status_change', 10, 4);
function production_log_status_change($order_id, $old_status, $new_status, $order) {
    global $wpdb;
    $table = $wpdb->prefix . 'production_status_history';
    
    $production_statuses = ['in-production', 'cutting', 'sewing', 'quality-check', 'packaging', 'ready-to-ship'];
    
    if (in_array($new_status, $production_statuses)) {
        $wpdb->insert($table, [
            'order_id' => $order_id,
            'status' => $new_status,
            'changed_at' => current_time('mysql'),
            'changed_by' => get_current_user_id(),
            'notes' => sprintf('Status changed from %s to %s', $old_status, $new_status)
        ]);
        
        // Clear relevant caches
        production_cache_delete('dashboard_stats');
        production_cache_delete('schedule_list');
    }
}

/* =====================================================
 * 3. CACHING SYSTEM - PERFORMANCE OPTIMIZATION
 * ===================================================== */
class Production_Cache {
    private static $cache_group = 'production_panel';
    
    /**
     * Get cached data
     */
    public static function get($key, $default = null) {
        $settings = get_option('production_panel_settings', []);
        $cache_duration = $settings['cache_duration'] ?? 3600;
        
        if ($cache_duration <= 0) {
            return $default;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'production_cache';
        
        $result = $wpdb->get_row($wpdb->prepare(
            "SELECT cache_value, expires_at FROM {$table} WHERE cache_key = %s",
            $key
        ));
        
        if (!$result) {
            return $default;
        }
        
        if (strtotime($result->expires_at) < time()) {
            self::delete($key);
            return $default;
        }
        
        return maybe_unserialize($result->cache_value);
    }
    
    /**
     * Set cached data
     */
    public static function set($key, $value, $duration = null) {
        if ($duration === null) {
            $settings = get_option('production_panel_settings', []);
            $duration = $settings['cache_duration'] ?? 3600;
        }
        
        if ($duration <= 0) {
            return false;
        }
        
        global $wpdb;
        $table = $wpdb->prefix . 'production_cache';
        
        $wpdb->replace($table, [
            'cache_key' => $key,
            'cache_value' => maybe_serialize($value),
            'expires_at' => date('Y-m-d H:i:s', time() + $duration)
        ]);
        
        return true;
    }
    
    /**
     * Delete cached data
     */
    public static function delete($key) {
        global $wpdb;
        $table = $wpdb->prefix . 'production_cache';
        $wpdb->delete($table, ['cache_key' => $key]);
    }
    
    /**
     * Clear all expired cache
     */
    public static function clear_expired() {
        global $wpdb;
        $table = $wpdb->prefix . 'production_cache';
        $wpdb->query("DELETE FROM {$table} WHERE expires_at < NOW()");
    }
    
    /**
     * Clear all cache
     */
    public static function flush() {
        global $wpdb;
        $table = $wpdb->prefix . 'production_cache';
        $wpdb->query("TRUNCATE TABLE {$table}");
    }
}

// Helper functions for caching
function production_cache_get($key, $default = null) {
    return Production_Cache::get($key, $default);
}

function production_cache_set($key, $value, $duration = null) {
    return Production_Cache::set($key, $value, $duration);
}

function production_cache_delete($key) {
    return Production_Cache::delete($key);
}

// Workload simulation function
function production_calculate_department_workload($start_date = null, $end_date = null) {
    global $wpdb;
    
    if (!$start_date) $start_date = current_time('Y-m-d 00:00:00');
    if (!$end_date) {
        $end_timestamp = strtotime($start_date . ' +7 days');
        $end_date = date('Y-m-d 23:59:59', $end_timestamp + (get_option('gmt_offset') * HOUR_IN_SECONDS));
    }
    
    $table_schedule = $wpdb->prefix . 'production_schedule';
    $table_departments = $wpdb->prefix . 'production_departments';
    
    // Get all departments
    $departments = $wpdb->get_results("SELECT * FROM {$table_departments} WHERE is_active = 1");
    
    $workload = [];
    
    foreach ($departments as $dept) {
        // Get scheduled work for this department
        $scheduled = $wpdb->get_results($wpdb->prepare("
            SELECT 
                COUNT(*) as task_count,
                SUM(TIMESTAMPDIFF(MINUTE, scheduled_start, scheduled_end)) as total_minutes,
                SUM(quantity) as total_items
            FROM {$table_schedule}
            WHERE department_id = %d
            AND scheduled_start BETWEEN %s AND %s
            AND status != 'cancelled'
        ", $dept->id, $start_date, $end_date));
        
        $scheduled_data = !empty($scheduled) ? $scheduled[0] : (object)[
            'task_count' => 0,
            'total_minutes' => 0,
            'total_items' => 0
        ];
        
        // Calculate capacity (assuming 8 hour days, workers * hours * 60)
        $days = ceil((strtotime($end_date) - strtotime($start_date)) / 86400);
        $daily_hours = 8;
        $capacity_minutes = $dept->workers * $daily_hours * 60 * $days;
        
        $total_minutes = floatval($scheduled_data->total_minutes ?? 0);
        $utilization = $capacity_minutes > 0 ? 
            round(($total_minutes / $capacity_minutes) * 100, 2) : 0;
        
        $workload[] = [
            'department' => $dept->name,
            'department_id' => $dept->id,
            'color' => $dept->color,
            'workers' => $dept->workers,
            'capacity_minutes' => $capacity_minutes,
            'scheduled_minutes' => $total_minutes,
            'task_count' => intval($scheduled_data->task_count ?? 0),
            'total_items' => intval($scheduled_data->total_items ?? 0),
            'utilization_percent' => $utilization,
            'available_minutes' => $capacity_minutes - $total_minutes,
            'status' => $utilization >= 100 ? 'overloaded' : 
                       ($utilization >= 80 ? 'busy' : 'available')
        ];
    }
    
    return $workload;
}

// Clear expired cache hourly
add_action('wp_scheduled_delete', function() {
    Production_Cache::clear_expired();
});

/* =====================================================
 * 4. PRODUCT ROUTES SYSTEM
 * ===================================================== */
class Production_Routes {
    
    /**
     * Get routes for a product
     */
    public static function get_product_routes($product_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'production_routes';
        $dept_table = $wpdb->prefix . 'production_departments';
        
        $routes = $wpdb->get_results($wpdb->prepare("
            SELECT r.*, d.name as department_name, d.color
            FROM {$table} r
            LEFT JOIN {$dept_table} d ON r.department_id = d.id
            WHERE r.product_id = %d
            ORDER BY r.sequence_order ASC
        ", $product_id));
        
        return $routes;
    }
    
    /**
     * Save routes for a product
     */
    public static function save_product_routes($product_id, $routes) {
        global $wpdb;
        $table = $wpdb->prefix . 'production_routes';
        
        // Delete existing routes
        $wpdb->delete($table, ['product_id' => $product_id]);
        
        // Insert new routes
        $order = 1;
        foreach ($routes as $route) {
            $wpdb->insert($table, [
                'product_id' => $product_id,
                'department_id' => absint($route['department_id']),
                'sequence_order' => $order,
                'estimated_time' => absint($route['estimated_time']),
                'setup_time' => absint($route['setup_time'] ?? 0),
                'dependencies' => isset($route['dependencies']) ? json_encode($route['dependencies']) : null,
                'notes' => sanitize_textarea_field($route['notes'] ?? '')
            ]);
            $order++;
        }
        
        production_cache_delete('product_routes_' . $product_id);
        
        return true;
    }
    
    /**
     * Get route templates
     */
    public static function get_templates() {
        global $wpdb;
        $table = $wpdb->prefix . 'production_route_templates';
        return $wpdb->get_results("SELECT * FROM {$table} ORDER BY name ASC");
    }
    
    /**
     * Apply template to product
     */
    public static function apply_template($product_id, $template_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'production_route_templates';
        
        $template = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $template_id
        ));
        
        if (!$template) {
            return false;
        }
        
        $routes = json_decode($template->route_data, true);
        return self::save_product_routes($product_id, $routes);
    }
    
    /**
     * Calculate total production time for a product
     */
    public static function calculate_production_time($product_id, $quantity = 1) {
        $routes = self::get_product_routes($product_id);
        $total_time = 0;
        
        foreach ($routes as $route) {
            $total_time += ($route->setup_time + ($route->estimated_time * $quantity));
        }
        
        return $total_time; // in minutes
    }
}

/* =====================================================
 * 5. ADVANCED SCHEDULER
 * ===================================================== */
class Production_Scheduler {
    
    /**
     * Auto-schedule an order
     */
    public static function auto_schedule_order($order_id, $priority = 5) {
        global $wpdb;
        $schedule_table = $wpdb->prefix . 'production_schedule';
        
        $order = wc_get_order($order_id);
        if (!$order) {
            return false;
        }
        
        $items = $order->get_items();
        $scheduled_items = [];
        $current_time = current_time('mysql');
        
        foreach ($items as $item) {
            $product_id = $item->get_product_id();
            $quantity = $item->get_quantity();
            
            // Get product routes
            $routes = Production_Routes::get_product_routes($product_id);
            
            if (empty($routes)) {
                continue;
            }
            
            $start_time = $current_time;
            
            foreach ($routes as $route) {
                // Calculate duration
                $duration = $route->setup_time + ($route->estimated_time * $quantity);
                
                // Find available slot
                $slot = self::find_available_slot(
                    $route->department_id,
                    $start_time,
                    $duration,
                    $priority
                );
                
                // Insert schedule
                $wpdb->insert($schedule_table, [
                    'order_id' => $order_id,
                    'department_id' => $route->department_id,
                    'product_id' => $product_id,
                    'quantity' => $quantity,
                    'scheduled_start' => $slot['start'],
                    'scheduled_end' => $slot['end'],
                    'status' => 'scheduled',
                    'priority' => $priority,
                    'notes' => 'Auto-scheduled'
                ]);
                
                $scheduled_items[] = $wpdb->insert_id;
                
                // Next department starts after this one ends
                $start_time = $slot['end'];
            }
        }
        
        production_cache_delete('schedule_list');
        production_cache_delete('calendar_events');
        
        return $scheduled_items;
    }
    
    /**
     * Find available time slot in department
     */
    public static function find_available_slot($department_id, $earliest_start, $duration_minutes, $priority = 5) {
        global $wpdb;
        $schedule_table = $wpdb->prefix . 'production_schedule';
        $dept_table = $wpdb->prefix . 'production_departments';
        
        // Get department capacity
        $department = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$dept_table} WHERE id = %d",
            $department_id
        ));
        
        if (!$department) {
            return [
                'start' => $earliest_start,
                'end' => date('Y-m-d H:i:s', strtotime($earliest_start) + ($duration_minutes * 60))
            ];
        }
        
        // Get existing schedules for this department
        $existing = $wpdb->get_results($wpdb->prepare("
            SELECT scheduled_start, scheduled_end
            FROM {$schedule_table}
            WHERE department_id = %d
            AND status != 'completed'
            AND scheduled_start >= %s
            ORDER BY scheduled_start ASC
        ", $department_id, $earliest_start));
        
        $start_timestamp = strtotime($earliest_start);
        
        // Simple slot finding - can be enhanced with parallel capacity
        if (!empty($existing)) {
            $last_item = $existing[count($existing) - 1];
            $last_end = strtotime($last_item->scheduled_end);
            $start_timestamp = max($start_timestamp, $last_end);
        }
        
        // Adjust for working hours
        $start_timestamp = self::adjust_for_working_hours($start_timestamp, $duration_minutes);
        
        return [
            'start' => date('Y-m-d H:i:s', $start_timestamp),
            'end' => date('Y-m-d H:i:s', $start_timestamp + ($duration_minutes * 60))
        ];
    }
    
    /**
     * Adjust scheduling time for working hours
     */
    private static function adjust_for_working_hours($timestamp, $duration_minutes) {
        $settings = get_option('production_panel_settings', []);
        $working_days = $settings['working_days'] ?? ['1', '2', '3', '4', '5'];
        $daily_hours = $settings['daily_hours'] ?? 8;
        
        $day_of_week = date('w', $timestamp);
        
        // If not a working day, move to next working day
        while (!in_array($day_of_week, $working_days)) {
            $timestamp = strtotime('+1 day', $timestamp);
            $day_of_week = date('w', $timestamp);
        }
        
        // Set to work start time (8 AM by default)
        $hour = date('H', $timestamp);
        if ($hour < 8) {
            $timestamp = strtotime(date('Y-m-d 08:00:00', $timestamp));
        } elseif ($hour >= 8 + $daily_hours) {
            // After work hours, move to next working day
            $timestamp = strtotime(date('Y-m-d 08:00:00', strtotime('+1 day', $timestamp)));
        }
        
        return $timestamp;
    }
    
    /**
     * Detect scheduling conflicts
     */
    public static function detect_conflicts($department_id, $start_time, $end_time, $exclude_schedule_id = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'production_schedule';
        $dept_table = $wpdb->prefix . 'production_departments';
        
        $department = $wpdb->get_row($wpdb->prepare(
            "SELECT capacity FROM {$dept_table} WHERE id = %d",
            $department_id
        ));
        
        if (!$department) {
            return [];
        }
        
        $query = "
            SELECT COUNT(*) as concurrent_count
            FROM {$table}
            WHERE department_id = %d
            AND status != 'completed'
            AND status != 'cancelled'
            AND (
                (scheduled_start <= %s AND scheduled_end > %s)
                OR (scheduled_start < %s AND scheduled_end >= %s)
                OR (scheduled_start >= %s AND scheduled_end <= %s)
            )
        ";
        
        $params = [$department_id, $start_time, $start_time, $end_time, $end_time, $start_time, $end_time];
        
        if ($exclude_schedule_id) {
            $query .= " AND id != %d";
            $params[] = $exclude_schedule_id;
        }
        
        $result = $wpdb->get_var($wpdb->prepare($query, $params));
        
        if ($result >= $department->capacity) {
            return [
                'has_conflict' => true,
                'message' => sprintf('Department is at capacity (%d/%d)', $result, $department->capacity)
            ];
        }
        
        return ['has_conflict' => false];
    }
    
    /**
     * Reschedule a task
     */
    public static function reschedule($schedule_id, $new_start, $new_end = null) {
        global $wpdb;
        $table = $wpdb->prefix . 'production_schedule';
        
        $schedule = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE id = %d",
            $schedule_id
        ));
        
        if (!$schedule) {
            return false;
        }
        
        // Calculate new end time if not provided
        if (!$new_end) {
            $duration = strtotime($schedule->scheduled_end) - strtotime($schedule->scheduled_start);
            $new_end = date('Y-m-d H:i:s', strtotime($new_start) + $duration);
        }
        
        // Check for conflicts
        $conflict = self::detect_conflicts(
            $schedule->department_id,
            $new_start,
            $new_end,
            $schedule_id
        );
        
        if ($conflict['has_conflict']) {
            return ['success' => false, 'message' => $conflict['message']];
        }
        
        // Update schedule
        $wpdb->update(
            $table,
            [
                'scheduled_start' => $new_start,
                'scheduled_end' => $new_end
            ],
            ['id' => $schedule_id]
        );
        
        production_cache_delete('schedule_list');
        production_cache_delete('calendar_events');
        
        return ['success' => true, 'message' => 'Rescheduled successfully'];
    }
}

/* =====================================================
 * 6. AJAX HANDLERS - REAL-TIME OPERATIONS
 * ===================================================== */

// Get WooCommerce order details
add_action('wp_ajax_production_get_order_details', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $order_id = isset($_POST['order_id']) ? absint($_POST['order_id']) : 0;
    if (!$order_id) {
        wp_send_json_error('Invalid order ID');
    }
    
    $details = production_get_order_details($order_id);
    
    if ($details) {
        wp_send_json_success($details);
    } else {
        wp_send_json_error('Order not found');
    }
});

// Get calendar events for FullCalendar
add_action('wp_ajax_production_get_calendar_events', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $cache_key = 'calendar_events';
    $events = production_cache_get($cache_key);
    
    if ($events === null) {
        global $wpdb;
        $table = $wpdb->prefix . 'production_schedule';
        $dept_table = $wpdb->prefix . 'production_departments';
        
        $schedules = $wpdb->get_results("
            SELECT s.*, d.name as department_name, d.color,
                   p.post_title as product_name,
                   o.post_title as order_number
            FROM {$table} s
            LEFT JOIN {$dept_table} d ON s.department_id = d.id
            LEFT JOIN {$wpdb->posts} p ON s.product_id = p.ID
            LEFT JOIN {$wpdb->posts} o ON s.order_id = o.ID
            WHERE s.status != 'cancelled'
            ORDER BY s.scheduled_start ASC
        ");
        
        $events = [];
        foreach ($schedules as $schedule) {
            // Get customer name from WooCommerce order
            $customer_name = '';
            if (function_exists('wc_get_order')) {
                $order = wc_get_order($schedule->order_id);
                if ($order) {
                    $customer_name = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                }
            }
            
            $title = $customer_name 
                ? sprintf('#%s - %s - %s (%d)', 
                    $schedule->order_id,
                    $customer_name,
                    $schedule->product_name,
                    $schedule->quantity
                )
                : sprintf('#%s - %s (%d)', 
                    $schedule->order_id,
                    $schedule->product_name,
                    $schedule->quantity
                );
            
            $events[] = [
                'id' => $schedule->id,
                'title' => $title,
                'start' => $schedule->scheduled_start,
                'end' => $schedule->scheduled_end,
                'backgroundColor' => $schedule->color ?? '#3498db',
                'borderColor' => $schedule->color ?? '#3498db',
                'extendedProps' => [
                    'department' => $schedule->department_name,
                    'order_id' => $schedule->order_id,
                    'product_id' => $schedule->product_id,
                    'status' => $schedule->status,
                    'notes' => $schedule->notes,
                    'customer' => $customer_name
                ]
            ];
        }
        
        production_cache_set($cache_key, $events);
    }
    
    wp_send_json_success($events);
});

// Update schedule (drag-drop)
add_action('wp_ajax_production_update_schedule', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $schedule_id = absint($_POST['schedule_id']);
    $new_start = sanitize_text_field($_POST['start']);
    $new_end = sanitize_text_field($_POST['end']);
    
    $result = Production_Scheduler::reschedule($schedule_id, $new_start, $new_end);
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
});

// Auto-schedule order
add_action('wp_ajax_production_auto_schedule', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $order_id = absint($_POST['order_id']);
    $priority = isset($_POST['priority']) ? absint($_POST['priority']) : 5;
    
    $result = Production_Scheduler::auto_schedule_order($order_id, $priority);
    
    if ($result) {
        wp_send_json_success([
            'message' => 'Order scheduled successfully',
            'scheduled_items' => count($result)
        ]);
    } else {
        wp_send_json_error('Failed to schedule order');
    }
});

// Save product routes
add_action('wp_ajax_production_save_routes', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $product_id = absint($_POST['product_id']);
    $routes = [];
    if (isset($_POST['routes'])) {
        $routes_data = json_decode(stripslashes($_POST['routes']), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($routes_data)) {
            $routes = $routes_data;
        }
    }
    
    $result = Production_Routes::save_product_routes($product_id, $routes);
    
    if ($result) {
        wp_send_json_success('Routes saved successfully');
    } else {
        wp_send_json_error('Failed to save routes');
    }
});

// Get product routes
add_action('wp_ajax_production_get_routes', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $product_id = absint($_POST['product_id']);
    $routes = Production_Routes::get_product_routes($product_id);
    
    wp_send_json_success($routes);
});

// Save cabinet type
add_action('wp_ajax_production_save_cabinet_type', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    global $wpdb;
    
    $id = isset($_POST['id']) ? absint($_POST['id']) : 0;
    $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
    $description = isset($_POST['description']) ? sanitize_textarea_field($_POST['description']) : '';
    $color = isset($_POST['color']) ? sanitize_text_field($_POST['color']) : '#667eea';
    $time_multiplier = isset($_POST['time_multiplier']) ? floatval($_POST['time_multiplier']) : 1.00;
    $base_duration = isset($_POST['base_duration']) ? absint($_POST['base_duration']) : 0;
    
    $workflows = [];
    if (isset($_POST['workflows'])) {
        $workflows_data = json_decode(stripslashes($_POST['workflows']), true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($workflows_data)) {
            $workflows = $workflows_data;
        }
    }
    
    $categories = isset($_POST['categories']) ? array_map('absint', (array)$_POST['categories']) : [];
    
    // Validate
    if (empty($name)) {
        wp_send_json_error('Cabinet type name is required');
        return;
    }
    
    $table_types = $wpdb->prefix . 'production_cabinet_types';
    $table_workflows = $wpdb->prefix . 'production_type_workflows';
    $table_categories = $wpdb->prefix . 'production_type_categories';
    
    // Check if tables exist, if not try to create them
    $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$table_types}'");
    if (!$tables_exist) {
        // Attempt to create tables
        production_panel_create_tables();
        update_option('production_panel_tables_created', true);
        
        // Check again
        $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$table_types}'");
        if (!$tables_exist) {
            wp_send_json_error('Database tables could not be created. Please contact administrator.');
            return;
        }
    }
    
    $wpdb->query('START TRANSACTION');
    
    try {
        // Save cabinet type
        $data = [
            'name' => $name,
            'description' => $description,
            'color' => $color,
            'time_multiplier' => $time_multiplier,
            'base_duration' => $base_duration,
            'is_active' => 1
        ];
        
        if ($id > 0) {
            $result = $wpdb->update($table_types, $data, ['id' => $id]);
            if ($result === false) {
                throw new Exception('Database error: ' . $wpdb->last_error);
            }
            $cabinet_type_id = $id;
        } else {
            $result = $wpdb->insert($table_types, $data);
            if ($result === false) {
                throw new Exception('Database error: ' . $wpdb->last_error);
            }
            $cabinet_type_id = $wpdb->insert_id;
        }
        
        if (!$cabinet_type_id) {
            throw new Exception('Failed to save cabinet type - no ID returned');
        }
        
        // Delete existing workflows and categories
        $wpdb->delete($table_workflows, ['cabinet_type_id' => $cabinet_type_id]);
        $wpdb->delete($table_categories, ['cabinet_type_id' => $cabinet_type_id]);
        
        // Insert workflows
        if (!empty($workflows)) {
            foreach ($workflows as $index => $workflow) {
                // Validate workflow data
                if (!isset($workflow['department_id']) || !isset($workflow['duration_minutes'])) {
                    continue;
                }
                
                $dept_id = absint($workflow['department_id']);
                $duration = absint($workflow['duration_minutes']);
                
                if ($dept_id <= 0) {
                    continue;
                }
                
                $wpdb->insert($table_workflows, [
                    'cabinet_type_id' => $cabinet_type_id,
                    'department_id' => $dept_id,
                    'sequence_order' => $index,
                    'duration_minutes' => $duration
                ]);
            }
        }
        
        // Insert categories
        if (!empty($categories)) {
            foreach ($categories as $category_id) {
                $wpdb->insert($table_categories, [
                    'cabinet_type_id' => $cabinet_type_id,
                    'category_id' => $category_id
                ]);
            }
        }
        
        $wpdb->query('COMMIT');
        wp_send_json_success([
            'message' => 'Cabinet type saved successfully',
            'id' => $cabinet_type_id
        ]);
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error($e->getMessage());
    }
});

// Delete cabinet type
add_action('wp_ajax_production_delete_cabinet_type', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    global $wpdb;
    $id = absint($_POST['id']);
    
    $table_types = $wpdb->prefix . 'production_cabinet_types';
    $table_workflows = $wpdb->prefix . 'production_type_workflows';
    $table_categories = $wpdb->prefix . 'production_type_categories';
    
    // Delete related data
    $wpdb->delete($table_workflows, ['cabinet_type_id' => $id]);
    $wpdb->delete($table_categories, ['cabinet_type_id' => $id]);
    $wpdb->delete($table_types, ['id' => $id]);
    
    wp_send_json_success('Cabinet type deleted successfully');
});

// Get cabinet type by category
add_action('wp_ajax_production_get_cabinet_type_by_category', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    global $wpdb;
    $category_id = absint($_POST['category_id']);
    
    $table_types = $wpdb->prefix . 'production_cabinet_types';
    $table_categories = $wpdb->prefix . 'production_type_categories';
    $table_workflows = $wpdb->prefix . 'production_type_workflows';
    
    // Find cabinet type
    $cabinet_type = $wpdb->get_row($wpdb->prepare("
        SELECT t.* 
        FROM {$table_types} t
        INNER JOIN {$table_categories} tc ON t.id = tc.cabinet_type_id
        WHERE tc.category_id = %d AND t.is_active = 1
        LIMIT 1
    ", $category_id));
    
    if (!$cabinet_type) {
        wp_send_json_error('No cabinet type found for this category');
    }
    
    // Get workflows
    $workflows = $wpdb->get_results($wpdb->prepare("
        SELECT * FROM {$table_workflows}
        WHERE cabinet_type_id = %d
        ORDER BY sequence_order ASC
    ", $cabinet_type->id));
    
    wp_send_json_success([
        'cabinet_type' => $cabinet_type,
        'workflows' => $workflows
    ]);
});

// Get single cabinet type for editing
add_action('wp_ajax_production_get_cabinet_type', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    global $wpdb;
    $id = absint($_POST['id']);
    
    $table_types = $wpdb->prefix . 'production_cabinet_types';
    $table_workflows = $wpdb->prefix . 'production_type_workflows';
    $table_categories = $wpdb->prefix . 'production_type_categories';
    
    $type = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_types} WHERE id = %d", $id));
    
    if (!$type) {
        wp_send_json_error('Cabinet type not found');
    }
    
    $workflows = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_workflows} WHERE cabinet_type_id = %d ORDER BY sequence_order ASC",
        $id
    ));
    
    $categories = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_categories} WHERE cabinet_type_id = %d",
        $id
    ));
    
    wp_send_json_success([
        'type' => $type,
        'workflows' => $workflows,
        'categories' => $categories
    ]);
});

// Delete schedule item
add_action('wp_ajax_production_delete_schedule', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $schedule_id = absint($_POST['schedule_id']);
    
    global $wpdb;
    $table = $wpdb->prefix . 'production_schedule';
    
    $wpdb->delete($table, ['id' => $schedule_id]);
    
    production_cache_delete('schedule_list');
    production_cache_delete('calendar_events');
    
    wp_send_json_success('Schedule deleted');
});

// Update schedule status
add_action('wp_ajax_production_update_status', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $schedule_id = absint($_POST['schedule_id']);
    $status = sanitize_text_field($_POST['status']);
    
    global $wpdb;
    $table = $wpdb->prefix . 'production_schedule';
    
    $update_data = ['status' => $status];
    
    if ($status === 'in-progress') {
        $update_data['actual_start'] = current_time('mysql');
    } elseif ($status === 'completed') {
        $update_data['actual_end'] = current_time('mysql');
    }
    
    $wpdb->update($table, $update_data, ['id' => $schedule_id]);
    
    production_cache_delete('dashboard_stats');
    production_cache_delete('schedule_list');
    
    wp_send_json_success('Status updated');
});

// Get dashboard stats (cached)
add_action('wp_ajax_production_get_stats', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $cache_key = 'dashboard_stats';
    $stats = production_cache_get($cache_key);
    
    if ($stats === null) {
        global $wpdb;
        $schedule_table = $wpdb->prefix . 'production_schedule';
        
        $stats = [
            'total_scheduled' => $wpdb->get_var("SELECT COUNT(*) FROM {$schedule_table} WHERE status = 'scheduled'"),
            'in_progress' => $wpdb->get_var("SELECT COUNT(*) FROM {$schedule_table} WHERE status = 'in-progress'"),
            'completed_today' => $wpdb->get_var("SELECT COUNT(*) FROM {$schedule_table} WHERE status = 'completed' AND DATE(actual_end) = CURDATE()"),
            'overdue' => $wpdb->get_var("SELECT COUNT(*) FROM {$schedule_table} WHERE status IN ('scheduled', 'in-progress') AND scheduled_end < NOW()")
        ];
        
        production_cache_set($cache_key, $stats, 300); // Cache for 5 minutes
    }
    
    wp_send_json_success($stats);
});

// Clear cache
add_action('wp_ajax_production_clear_cache', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    Production_Cache::flush();
    
    wp_send_json_success('Cache cleared');
});

/* =====================================================
 * 7. ROUTING - ADMIN PANEL INTEGRATION
 * URL routing is handled by adminpanel.php (lines 85-91):
 *   /b2b-panel/production → b2b_adm_page=production
 *   /b2b-panel/production/schedule → b2b_adm_page=production_schedule
 *   /b2b-panel/production/departments → b2b_adm_page=production_departments
 *   /b2b-panel/production/routes → b2b_adm_page=production_routes
 *   /b2b-panel/production/calendar → b2b_adm_page=production_calendar
 *   /b2b-panel/production/analytics → b2b_adm_page=production_analytics
 *   /b2b-panel/production/settings → b2b_adm_page=production_settings
 * 
 * This file provides template_redirect hooks for each page.
 * ===================================================== */

/* =====================================================
 * 8. DASHBOARD PAGE (Production)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production') return;
    b2b_adm_guard();
    production_dashboard_page();
});

/* =====================================================
 * 9. SCHEDULE PAGE (Production Schedule)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_schedule') return;
    b2b_adm_guard();
    production_schedule_page();
});

/* =====================================================
 * 10. DEPARTMENTS PAGE (Production Departments)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_departments') return;
    b2b_adm_guard();
    production_departments_page();
});

/* =====================================================
 * 11. PRODUCT ROUTES PAGE
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_routes') return;
    b2b_adm_guard();
    production_routes_page();
});

/* =====================================================
 * 12. CALENDAR PAGE (Production Calendar)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_calendar') return;
    b2b_adm_guard();
    production_calendar_page();
});

/* =====================================================
 * 13. ANALYTICS PAGE (Production Analytics)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_analytics') return;
    b2b_adm_guard();
    production_analytics_page();
});

/* =====================================================
 * 14. SETTINGS PAGE (Production Settings)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_settings') return;
    b2b_adm_guard();
    production_settings_page();
});

/* =====================================================
 * 15. NAVIGATION HELPER
 * ===================================================== */
function production_page_nav($active_page = 'dashboard') {
    $pages = [
        'dashboard' => ['icon' => 'fa-chart-line', 'label' => 'Dashboard', 'url' => home_url('/b2b-panel/production')],
        'schedule' => ['icon' => 'fa-calendar-days', 'label' => 'Schedule', 'url' => home_url('/b2b-panel/production/schedule')],
        'departments' => ['icon' => 'fa-building', 'label' => 'Departments', 'url' => home_url('/b2b-panel/production/departments')],
        'routes' => ['icon' => 'fa-route', 'label' => 'Product Routes', 'url' => home_url('/b2b-panel/production/routes')],
        'calendar' => ['icon' => 'fa-calendar', 'label' => 'Calendar', 'url' => home_url('/b2b-panel/production/calendar')],
        'analytics' => ['icon' => 'fa-chart-bar', 'label' => 'Analytics', 'url' => home_url('/b2b-panel/production/analytics')],
        'settings' => ['icon' => 'fa-gear', 'label' => 'Settings', 'url' => home_url('/b2b-panel/production/settings')]
    ];
    
    echo '<div class="page-nav">';
    foreach ($pages as $key => $page) {
        $active_class = ($key === $active_page) ? ' active' : '';
        echo '<a href="' . esc_url($page['url']) . '" class="nav-btn' . $active_class . '">';
        echo '<i class="fa-solid ' . esc_attr($page['icon']) . '"></i> ' . esc_html($page['label']);
        echo '</a>';
    }
    echo '</div>';
}

/* =====================================================
 * 16. DASHBOARD PAGE
 * ===================================================== */
function production_dashboard_page() {
    b2b_adm_header('Production Dashboard');
    
    ?>
    <style>
        .page-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .nav-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .nav-btn.active {
            background: #667eea;
            color: white;
            border-color: #4c51bf;
        }
        .nav-btn i {
            font-size: 16px;
        }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px;
            border-radius: 12px;
            color: #ffffff !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(0,0,0,0.15);
        }
        .stat-card.blue { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }
        .stat-card.orange { background: linear-gradient(135deg, #f6ad55 0%, #ed8936 100%); }
        .stat-card.green { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
        .stat-label {
            font-size: 14px;
            color: #ffffff !important;
            opacity: 0.95;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 500;
        }
        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #ffffff !important;
        }
        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            color: #1f2937;
            font-weight: 600;
        }
        .card h3 i {
            margin-right: 8px;
            color: #667eea;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }
        .data-table thead {
            background: #f9fafb;
        }
        .data-table th {
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #6b7280;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e5e7eb;
        }
        .data-table td {
            padding: 12px;
            border-bottom: 1px solid #f3f4f6;
            color: #374151;
        }
        .data-table tr:hover {
            background: #f9fafb;
        }
        .badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
            background: #e5e7eb;
            color: #374151;
        }
        .badge.scheduled {
            background: #dbeafe;
            color: #1e40af;
        }
        .badge.in_progress {
            background: #fed7aa;
            color: #c05621;
        }
        .badge.completed {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
    
    <?php production_page_nav('dashboard'); ?>
    <?php
        
        global $wpdb;
        $table_schedule = $wpdb->prefix . 'production_schedule';
        $table_departments = $wpdb->prefix . 'production_departments';
        $table_history = $wpdb->prefix . 'production_status_history';
        
        $total_scheduled = $wpdb->get_var("SELECT COUNT(*) FROM {$table_schedule} WHERE status = 'scheduled'");
        $in_progress = $wpdb->get_var("SELECT COUNT(*) FROM {$table_schedule} WHERE status = 'in_progress'");
        $completed_today = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$table_schedule} WHERE status = 'completed' AND DATE(actual_end) = %s",
            current_time('Y-m-d')
        ));
        $total_departments = $wpdb->get_var("SELECT COUNT(*) FROM {$table_departments} WHERE is_active = 1");
        
        ?>
        <div class="stat-grid">
            <div class="stat-card blue">
                <div class="stat-label">Scheduled Orders</div>
                <div class="stat-value"><?= esc_html($total_scheduled ?: 0) ?></div>
            </div>
            <div class="stat-card orange">
                <div class="stat-label">In Progress</div>
                <div class="stat-value"><?= esc_html($in_progress ?: 0) ?></div>
            </div>
            <div class="stat-card green">
                <div class="stat-label">Completed Today</div>
                <div class="stat-value"><?= esc_html($completed_today ?: 0) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Active Departments</div>
                <div class="stat-value"><?= esc_html($total_departments ?: 0) ?></div>
            </div>
        </div>
        
        <div class="card">
            <h3><i class="fa-solid fa-history"></i> Recent Production Activities</h3>
            <?php
            $recent = $wpdb->get_results("SELECT * FROM {$table_history} ORDER BY changed_at DESC LIMIT 20");
            
            if ($recent) {
                echo '<table class="data-table"><thead><tr>';
                echo '<th>Order ID</th><th>Status</th><th>Changed At</th><th>Changed By</th><th>Notes</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($recent as $row) {
                    $user = get_userdata($row->changed_by);
                    echo '<tr>';
                    echo '<td><strong>#' . esc_html($row->order_id) . '</strong></td>';
                    echo '<td><span class="badge">' . esc_html(ucwords(str_replace('-', ' ', $row->status))) . '</span></td>';
                    echo '<td>' . esc_html($row->changed_at) . '</td>';
                    echo '<td>' . ($user ? esc_html($user->display_name) : 'System') . '</td>';
                    echo '<td>' . esc_html($row->notes) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p style="color:var(--text-muted);text-align:center;padding:40px">No recent activities found.</p>';
            }
            ?>
        </div>
        
        <div class="card">
            <h3><i class="fa-solid fa-list-check"></i> Current Production Schedule</h3>
            <?php
            $schedule = $wpdb->get_results($wpdb->prepare(
                "SELECT s.*, d.name as department_name, d.color 
                FROM {$table_schedule} s 
                LEFT JOIN {$table_departments} d ON s.department_id = d.id 
                WHERE s.status IN ('scheduled', 'in_progress')
                ORDER BY s.priority DESC, s.scheduled_start ASC 
                LIMIT %d",
                15
            ));
            
            if ($schedule) {
                echo '<table class="data-table"><thead><tr>';
                echo '<th>Order</th><th>Department</th><th>Product</th><th>Quantity</th><th>Start</th><th>End</th><th>Priority</th><th>Status</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($schedule as $item) {
                    echo '<tr>';
                    echo '<td><strong>#' . esc_html($item->order_id) . '</strong></td>';
                    echo '<td><span style="display:inline-block;width:10px;height:10px;background:' . esc_attr($item->color) . ';border-radius:50%;margin-right:5px;"></span>' . esc_html($item->department_name) . '</td>';
                    echo '<td>Product #' . esc_html($item->product_id) . '</td>';
                    echo '<td>' . esc_html($item->quantity) . '</td>';
                    echo '<td>' . esc_html($item->scheduled_start) . '</td>';
                    echo '<td>' . esc_html($item->scheduled_end) . '</td>';
                    echo '<td>' . esc_html($item->priority) . '</td>';
                    echo '<td><span class="badge ' . esc_attr($item->status) . '">' . esc_html(ucwords(str_replace('_', ' ', $item->status))) . '</span></td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo '<p style="color:var(--text-muted);text-align:center;padding:40px">No active production schedules.</p>';
            }
            ?>
        </div>
        
        <!-- Auto-Schedule Widget -->
        <div class="card">
            <h3><i class="fa-solid fa-wand-magic-sparkles"></i> Quick Auto-Schedule</h3>
            <p style="color:#6b7280;margin-bottom:20px;">
                Automatically schedule an order through its production route with optimal timing.
            </p>
            
            <form id="autoScheduleForm" style="display:grid;grid-template-columns:2fr 1fr auto;gap:15px;align-items:end;">
                <div>
                    <label style="display:block;font-weight:600;color:#374151;margin-bottom:5px;font-size:14px;">Order ID</label>
                    <input type="number" id="autoScheduleOrderId" class="form-control" placeholder="Enter order ID" required style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                </div>
                <div>
                    <label style="display:block;font-weight:600;color:#374151;margin-bottom:5px;font-size:14px;">Priority (1-10)</label>
                    <input type="number" id="autoSchedulePriority" class="form-control" value="5" min="1" max="10" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                </div>
                <button type="submit" class="btn btn-primary" style="padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;transition:all 0.2s;font-size:14px;background:#667eea;color:white;">
                    <i class="fa-solid fa-magic"></i> Auto-Schedule
                </button>
            </form>
            
            <div id="autoScheduleResult" style="margin-top:15px;"></div>
        </div>
        
        <!-- Cache Management -->
        <div class="card">
            <h3><i class="fa-solid fa-database"></i> Cache Management</h3>
            <p style="color:#6b7280;margin-bottom:15px;">
                Clear cached data to force refresh of production statistics and schedules.
            </p>
            <button id="clearCacheBtn" class="btn btn-danger" style="padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;transition:all 0.2s;font-size:14px;background:#f56565;color:white;">
                <i class="fa-solid fa-trash"></i> Clear All Cache
            </button>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Auto-refresh stats every 30 seconds
            let autoRefreshInterval = setInterval(function() {
                refreshStats();
            }, 30000);
            
            // Refresh stats
            function refreshStats() {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'production_get_stats',
                        nonce: '<?= wp_create_nonce('production_ajax') ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update stat cards (simplified - would need IDs in real implementation)
                            console.log('Stats refreshed:', response.data);
                        }
                    }
                });
            }
            
            // Auto-schedule form
            $('#autoScheduleForm').on('submit', function(e) {
                e.preventDefault();
                
                const orderId = $('#autoScheduleOrderId').val();
                const priority = $('#autoSchedulePriority').val();
                const $btn = $(this).find('button[type="submit"]');
                const originalText = $btn.html();
                
                $btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Scheduling...').prop('disabled', true);
                $('#autoScheduleResult').empty();
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'production_auto_schedule',
                        nonce: '<?= wp_create_nonce('production_ajax') ?>',
                        order_id: orderId,
                        priority: priority
                    },
                    success: function(response) {
                        $btn.html(originalText).prop('disabled', false);
                        
                        if (response.success) {
                            $('#autoScheduleResult').html(`
                                <div style="background:#d1fae5;color:#065f46;padding:15px;border-radius:8px;border-left:4px solid #10b981;">
                                    <i class="fa-solid fa-check-circle"></i> ${response.data.message}. 
                                    Scheduled ${response.data.scheduled_items} production steps.
                                </div>
                            `);
                            $('#autoScheduleOrderId').val('');
                            
                            // Refresh page after 2 seconds
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            $('#autoScheduleResult').html(`
                                <div style="background:#fee2e2;color:#991b1b;padding:15px;border-radius:8px;border-left:4px solid #ef4444;">
                                    <i class="fa-solid fa-exclamation-circle"></i> ${response.data}
                                </div>
                            `);
                        }
                    },
                    error: function() {
                        $btn.html(originalText).prop('disabled', false);
                        $('#autoScheduleResult').html(`
                            <div style="background:#fee2e2;color:#991b1b;padding:15px;border-radius:8px;border-left:4px solid #ef4444;">
                                <i class="fa-solid fa-exclamation-circle"></i> An error occurred. Please try again.
                            </div>
                        `);
                    }
                });
            });
            
            // Clear cache
            $('#clearCacheBtn').on('click', function() {
                if (!confirm('Are you sure you want to clear all cache? This will force a refresh of all data.')) {
                    return;
                }
                
                const $btn = $(this);
                const originalText = $btn.html();
                
                $btn.html('<i class="fa-solid fa-spinner fa-spin"></i> Clearing...').prop('disabled', true);
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'production_clear_cache',
                        nonce: '<?= wp_create_nonce('production_ajax') ?>'
                    },
                    success: function(response) {
                        $btn.html(originalText).prop('disabled', false);
                        
                        if (response.success) {
                            alert('Cache cleared successfully!');
                            location.reload();
                        } else {
                            alert('Failed to clear cache.');
                        }
                    },
                    error: function() {
                        $btn.html(originalText).prop('disabled', false);
                        alert('An error occurred.');
                    }
                });
            });
        });
        </script>
        
        <?php
        b2b_adm_footer();
        exit;
}

/* =====================================================
 * 8. SCHEDULE PAGE
 * ===================================================== */
function production_schedule_page() {
    b2b_adm_header('Production Schedule');
    
    ?>
    <style>
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1f2937; font-weight: 600; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 5px; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5a67d8; }
        .btn-success { background: #48bb78; color: white; }
        .btn-success:hover { background: #38a169; }
        .btn-danger { background: #f56565; color: white; }
        .btn-danger:hover { background: #e53e3e; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table thead { background: #f9fafb; }
        .data-table th { padding: 12px; text-align: left; font-weight: 600; color: #6b7280; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .data-table tr:hover { background: #f9fafb; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge.scheduled { background: #dbeafe; color: #1e40af; }
        .badge.in_progress { background: #fed7aa; color: #c05621; }
        .badge.completed { background: #d1fae5; color: #065f46; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert i { margin-right: 8px; }
        .page-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .nav-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .nav-btn.active {
            background: #667eea;
            color: white;
            border-color: #4c51bf;
        }
        .nav-btn i {
            font-size: 16px;
        }
    </style>
    
    <?php 
    production_page_nav('schedule'); 
    
    global $wpdb;
    $table_schedule = $wpdb->prefix . 'production_schedule';
    $table_departments = $wpdb->prefix . 'production_departments';
    
    // Handle form submission for new schedule
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce'])) {
        check_admin_referer('production_add_schedule');
        
        $wpdb->insert($table_schedule, [
            'order_id' => absint($_POST['order_id']),
            'department_id' => absint($_POST['department_id']),
            'product_id' => absint($_POST['product_id']),
            'quantity' => absint($_POST['quantity']),
            'scheduled_start' => sanitize_text_field($_POST['scheduled_start']),
            'scheduled_end' => sanitize_text_field($_POST['scheduled_end']),
            'priority' => absint($_POST['priority']),
            'status' => 'scheduled',
            'notes' => sanitize_textarea_field($_POST['notes'])
        ]);
        
        echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Schedule added successfully!</div>';
    }
    
    // Handle status update
    if (isset($_GET['action']) && $_GET['action'] === 'start' && isset($_GET['id'])) {
        check_admin_referer('start_production_' . absint($_GET['id']));
        $wpdb->update($table_schedule, [
            'status' => 'in_progress',
            'actual_start' => current_time('mysql')
        ], ['id' => absint($_GET['id'])]);
        echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Production started!</div>';
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'complete' && isset($_GET['id'])) {
        check_admin_referer('complete_production_' . absint($_GET['id']));
        $wpdb->update($table_schedule, [
            'status' => 'completed',
            'actual_end' => current_time('mysql')
        ], ['id' => absint($_GET['id'])]);
        echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Production completed!</div>';
    }
    
    $departments = $wpdb->get_results("SELECT * FROM {$table_departments} WHERE is_active = 1 ORDER BY name ASC");
    ?>
    
    <div class="card">
        <h3>Add New Production Schedule</h3>
        <form method="post">
            <?php wp_nonce_field('production_add_schedule'); ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px">
                <div class="form-group">
                    <label class="form-label">Select WooCommerce Order</label>
                    <select name="order_id" id="woo_order_select" class="form-control" required>
                        <option value="">-- Select Order --</option>
                        <?php
                        $woo_orders = production_get_woo_orders();
                        foreach ($woo_orders as $order):
                        ?>
                        <option value="<?= esc_attr($order['id']) ?>" 
                                data-customer="<?= esc_attr($order['customer']) ?>"
                                data-total="<?= esc_attr($order['total']) ?>"
                                data-status="<?= esc_attr($order['status']) ?>">
                            #<?= esc_html($order['number']) ?> - <?= esc_html($order['customer']) ?> 
                            (<?= function_exists('wc_price') ? wc_price($order['total']) : '$' . number_format($order['total'], 2) ?>) - <?= ucfirst($order['status']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:#6b7280;margin-top:5px;display:block;">
                        Select a WooCommerce order to schedule for production
                    </small>
                </div>
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-control" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= esc_attr($dept->id) ?>"><?= esc_html($dept->name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div id="order_products_container" style="display:none;grid-column:1/-1;"></div>
                
                <div class="form-group">
                    <label class="form-label">Quantity</label>
                    <input type="number" name="quantity" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Start Date/Time</label>
                    <input type="datetime-local" name="scheduled_start" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">End Date/Time</label>
                    <input type="datetime-local" name="scheduled_end" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Priority (1-10)</label>
                    <input type="number" name="priority" class="form-control" value="5" min="1" max="10">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"></textarea>
            </div>
            <button type="submit" class="button primary">Add to Schedule</button>
        </form>
    </div>
    
    <div class="card">
        <h3>All Production Schedules</h3>
        <?php
        $schedules = $wpdb->get_results("
            SELECT s.*, d.name as department_name, d.color 
            FROM {$table_schedule} s 
            LEFT JOIN {$table_departments} d ON s.department_id = d.id 
            ORDER BY s.scheduled_start DESC 
            LIMIT 50
        ");
        
        if ($schedules):
        ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Department</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Scheduled Start</th>
                    <th>Scheduled End</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $s): ?>
                <tr>
                    <td>
                        <strong>#<?= esc_html($s->order_id) ?></strong>
                        <?php
                        if (function_exists('wc_get_order')) {
                            $order = wc_get_order($s->order_id);
                            if ($order):
                                $customer = $order->get_billing_first_name() . ' ' . $order->get_billing_last_name();
                        ?>
                        <div style="font-size:12px;color:#6b7280;">
                            <?= esc_html($customer) ?>
                        </div>
                        <?php 
                            endif;
                        }
                        ?>
                    </td>
                    <td>
                        <span style="display:inline-block;width:10px;height:10px;background:<?= esc_attr($s->color) ?>;border-radius:50%;margin-right:5px;"></span>
                        <?= esc_html($s->department_name) ?>
                    </td>
                    <td>Product #<?= esc_html($s->product_id) ?></td>
                    <td><?= esc_html($s->quantity) ?></td>
                    <td><?= esc_html($s->scheduled_start) ?></td>
                    <td><?= esc_html($s->scheduled_end) ?></td>
                    <td><?= esc_html($s->priority) ?></td>
                    <td><span class="badge <?= esc_attr($s->status) ?>"><?= esc_html(ucwords(str_replace('_', ' ', $s->status))) ?></span></td>
                    <td>
                        <?php if ($s->status === 'scheduled'): ?>
                        <a href="?action=start&id=<?= esc_attr($s->id) ?>&_wpnonce=<?= wp_create_nonce('start_production_' . $s->id) ?>" class="button success" style="font-size:12px;padding:5px 10px;">Start</a>
                        <button class="button secondary quick-edit-btn" data-id="<?= esc_attr($s->id) ?>" style="font-size:12px;padding:5px 10px;">Edit</button>
                        <?php elseif ($s->status === 'in_progress'): ?>
                        <a href="?action=complete&id=<?= esc_attr($s->id) ?>&_wpnonce=<?= wp_create_nonce('complete_production_' . $s->id) ?>" class="button primary" style="font-size:12px;padding:5px 10px;">Complete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--text-muted);text-align:center;padding:40px">No schedules found.</p>
        <?php endif; ?>
    </div>
    
    <!-- Quick Edit Modal -->
    <div id="quickEditModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:white;padding:30px;border-radius:12px;max-width:500px;width:90%;position:relative;">
            <button id="closeQuickEdit" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#6b7280;">&times;</button>
            <h3 style="margin:0 0 20px 0;font-size:20px;color:#1f2937;">Quick Edit Schedule</h3>
            <form id="quickEditForm">
                <input type="hidden" id="editScheduleId">
                <div style="margin-bottom:15px;">
                    <label style="display:block;font-weight:600;color:#374151;margin-bottom:5px;font-size:14px;">Status</label>
                    <select id="editStatus" style="width:100%;padding:10px 12px;border:1px solid #d1d5db;border-radius:6px;font-size:14px;">
                        <option value="scheduled">Scheduled</option>
                        <option value="in-progress">In Progress</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
                    <button type="button" id="cancelQuickEdit" class="btn btn-secondary" style="padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;background:#e5e7eb;color:#374151;">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="padding:10px 20px;border:none;border-radius:6px;font-weight:600;cursor:pointer;background:#667eea;color:white;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Quick edit functionality
        $('.quick-edit-btn').on('click', function() {
            const scheduleId = $(this).data('id');
            const $row = $(this).closest('tr');
            
            $('#editScheduleId').val(scheduleId);
            $('#editStatus').val($row.find('.badge').text().toLowerCase().replace(' ', '-'));
            
            $('#quickEditModal').css('display', 'flex');
        });
        
        $('#closeQuickEdit, #cancelQuickEdit').on('click', function() {
            $('#quickEditModal').hide();
        });
        
        $('#quickEditForm').on('submit', function(e) {
            e.preventDefault();
            
            const scheduleId = $('#editScheduleId').val();
            const status = $('#editStatus').val();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'production_update_status',
                    nonce: '<?= wp_create_nonce('production_ajax') ?>',
                    schedule_id: scheduleId,
                    status: status
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Failed to update schedule');
                    }
                }
            });
        });
        
        // Add tooltips
        $('table.data-table tbody tr').each(function() {
            $(this).attr('title', 'Click Edit button to modify this schedule');
        });
        
        // WooCommerce Order Selection Handler
        $('#woo_order_select').on('change', function() {
            const orderId = $(this).val();
            if (!orderId) {
                $('#order_products_container').hide();
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'production_get_order_details',
                    nonce: '<?= wp_create_nonce('production_ajax') ?>',
                    order_id: orderId
                },
                success: function(response) {
                    if (response.success) {
                        displayOrderProducts(response.data);
                    } else {
                        alert('Failed to load order products');
                    }
                }
            });
        });
        
        function displayOrderProducts(orderData) {
            let html = '<div class="card" style="background:#f9fafb;padding:15px;">';
            html += '<h4 style="margin-bottom:10px;">Order Products:</h4>';
            html += '<div style="display:grid;gap:10px;">';
            
            const $container = $('<div class="card" style="background:#f9fafb;padding:15px;"></div>');
            $container.append('<h4 style="margin-bottom:10px;">Order Products:</h4>');
            
            const $productsGrid = $('<div style="display:grid;gap:10px;"></div>');
            
            orderData.items.forEach(function(item) {
                const $label = $('<label style="display:flex;align-items:center;padding:10px;background:white;border-radius:6px;cursor:pointer;border:2px solid #e5e7eb;" class="product-option"></label>');
                
                const $radio = $('<input type="radio" name="product_id" required style="margin-right:10px;">');
                $radio.val(item.product_id);
                $radio.attr('data-categories', (item.categories || []).join(','));
                $label.append($radio);
                
                const $details = $('<div style="flex:1;"></div>');
                $details.append($('<strong></strong>').text(item.name));
                
                const $sku = $('<div style="color:#6b7280;font-size:13px;"></div>');
                $sku.text('SKU: ' + (item.sku || 'N/A') + ' | Qty: ' + item.quantity);
                $details.append($sku);
                
                if (item.cabinet_type) {
                    const $badge = $('<span class="type-badge" style="color:white;padding:3px 8px;border-radius:10px;font-size:11px;font-weight:600;"></span>');
                    $badge.css('background', item.cabinet_type.color);
                    $badge.text(item.cabinet_type.name);
                    $details.append($('<div style="margin-top:5px;"></div>').append($badge));
                }
                
                $label.append($details);
                $productsGrid.append($label);
            });
            
            $container.append($productsGrid);
            
            const $preview = $('<div id="autoWorkflowPreview" style="display:none;margin-top:15px;"></div>');
            
            $('#order_products_container').empty().append($container).append($preview).show();
            
            // Auto-fill quantity from first product
            if (orderData.items.length > 0) {
                $('input[name="quantity"]').val(orderData.items[0].quantity);
            }
            
            // Update quantity and detect cabinet type when product selection changes
            $(document).on('change', 'input[name="product_id"]', function() {
                const selectedProductId = $(this).val();
                const selectedItem = orderData.items.find(item => item.product_id == selectedProductId);
                
                if (selectedItem) {
                    $('input[name="quantity"]').val(selectedItem.quantity);
                    
                    // Auto-detect cabinet type and pre-fill workflow
                    if (selectedItem.categories && selectedItem.categories.length > 0) {
                        detectCabinetType(selectedItem.categories[0]);
                    }
                }
            });
        }
        
        function detectCabinetType(categoryId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'production_get_cabinet_type_by_category',
                    nonce: '<?= wp_create_nonce('production_ajax') ?>',
                    category_id: categoryId
                },
                success: function(response) {
                    if (response.success) {
                        displayWorkflowPreview(response.data);
                    } else {
                        $('#autoWorkflowPreview').hide();
                    }
                }
            });
        }
        
        function displayWorkflowPreview(data) {
            const cabinetType = data.cabinet_type;
            const workflows = data.workflows;
            
            if (!workflows || workflows.length === 0) {
                $('#autoWorkflowPreview').hide();
                return;
            }
            
            const $container = $('<div class="card" style="background:#e0e7ff;padding:15px;"></div>');
            
            const $header = $('<h4 style="margin:0 0 10px 0;color:#4338ca;"><i class="fa-solid fa-magic"></i> Auto-Detected Workflow: </h4>');
            $header.append($('<span></span>').text(cabinetType.name));
            $container.append($header);
            
            $container.append('<div style="color:#4338ca;font-size:13px;margin-bottom:10px;">Based on product category, the following workflow will be used:</div>');
            
            const $workflowContainer = $('<div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;"></div>');
            
            workflows.forEach(function(w, index) {
                if (index > 0) {
                    $workflowContainer.append('<span style="color:#6366f1;font-size:18px;">→</span>');
                }
                
                // Validate workflow data
                if (!w || !w.dept_name || !w.duration_minutes) {
                    return;
                }
                
                const $step = $('<div style="background:white;padding:8px 15px;border-radius:6px;border:2px solid #818cf8;"></div>');
                $step.append($('<strong></strong>').text(w.dept_name));
                $step.append($('<div style="font-size:11px;color:#6b7280;"></div>').text(w.duration_minutes + ' min'));
                $workflowContainer.append($step);
            });
            
            $container.append($workflowContainer);
            
            const totalDuration = workflows.reduce((sum, w) => {
                if (!w || !w.duration_minutes) return sum;
                return sum + parseInt(w.duration_minutes, 10);
            }, 0);
            $container.append('<div style="margin-top:10px;font-size:13px;color:#4338ca;"><strong>Total Duration:</strong> ' + totalDuration + ' minutes</div>');
            
            $('#autoWorkflowPreview').empty().append($container).fadeIn();
            
            // Auto-select first department
            if (workflows.length > 0) {
                $('select[name="department_id"]').val(workflows[0].department_id);
            }
        }
    });
    </script>
    
    <?php
    b2b_adm_footer();
    exit;
}

/* =====================================================
 * 9. DEPARTMENTS PAGE
 * ===================================================== */
function production_departments_page() {
    b2b_adm_header('Production Departments');
    
    ?>
    <style>
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1f2937; font-weight: 600; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 5px; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px; display: inline-block; text-decoration: none; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5a67d8; }
        .btn-success { background: #48bb78; color: white; }
        .btn-success:hover { background: #38a169; }
        .btn-warning { background: #f59e0b; color: white; }
        .btn-warning:hover { background: #d97706; }
        .btn-danger { background: #f56565; color: white; }
        .btn-danger:hover { background: #e53e3e; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .data-table thead { background: #f9fafb; }
        .data-table th { padding: 12px; text-align: left; font-weight: 600; color: #6b7280; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .data-table tr:hover { background: #f9fafb; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge.active { background: #d1fae5; color: #065f46; }
        .badge.inactive { background: #fee2e2; color: #991b1b; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert i { margin-right: 8px; }
        .color-swatch { display: inline-block; width: 24px; height: 24px; border-radius: 4px; border: 2px solid #e5e7eb; vertical-align: middle; }
        .page-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .nav-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .nav-btn.active {
            background: #667eea;
            color: white;
            border-color: #4c51bf;
        }
        .nav-btn i {
            font-size: 16px;
        }
    </style>
    
    <?php 
    production_page_nav('departments'); 
    
    global $wpdb;
    $table = $wpdb->prefix . 'production_departments';
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce'])) {
        check_admin_referer('production_add_department');
        
        if (isset($_POST['department_name'])) {
            $wpdb->insert($table, [
                'name' => sanitize_text_field($_POST['department_name']),
                'slug' => sanitize_title($_POST['department_name']),
                'capacity' => absint($_POST['capacity']),
                'workers' => absint($_POST['workers']),
                'color' => sanitize_hex_color($_POST['color']),
                'is_active' => 1
            ]);
            echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Department added successfully!</div>';
        }
    }
    
    // Handle delete
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        check_admin_referer('delete_dept_' . absint($_GET['id']));
        $wpdb->delete($table, ['id' => absint($_GET['id'])]);
        echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Department deleted!</div>';
    }
    
    // Handle toggle active
    if (isset($_GET['action']) && $_GET['action'] === 'toggle' && isset($_GET['id'])) {
        check_admin_referer('toggle_dept_' . absint($_GET['id']));
        $dept = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table} WHERE id = %d", absint($_GET['id'])));
        if ($dept) {
            $wpdb->update($table, ['is_active' => !$dept->is_active], ['id' => absint($_GET['id'])]);
            echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Department status updated!</div>';
        }
    }
    
    $departments = $wpdb->get_results("SELECT * FROM {$table} ORDER BY display_order ASC, name ASC");
    ?>
    
    <div class="card">
        <h3>Add New Department</h3>
        <form method="post">
            <?php wp_nonce_field('production_add_department'); ?>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px">
                <div class="form-group">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="department_name" class="form-control" required placeholder="e.g., Cutting">
                </div>
                <div class="form-group">
                    <label class="form-label">Capacity (concurrent orders)</label>
                    <input type="number" name="capacity" class="form-control" value="10" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Number of Workers</label>
                    <input type="number" name="workers" class="form-control" value="1" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" name="color" class="form-control" value="#3498db">
                </div>
            </div>
            <button type="submit" class="button primary">Add Department</button>
        </form>
    </div>
    
    <div class="card">
        <h3>All Departments</h3>
        <?php if ($departments): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Capacity</th>
                    <th>Workers</th>
                    <th>Color</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($departments as $dept): ?>
                <tr>
                    <td><strong><?= esc_html($dept->name) ?></strong></td>
                    <td><?= esc_html($dept->capacity) ?> orders</td>
                    <td><?= esc_html($dept->workers) ?> workers</td>
                    <td><span style="display:inline-block;width:40px;height:20px;background:<?= esc_attr($dept->color) ?>;border-radius:4px;"></span></td>
                    <td>
                        <?= $dept->is_active ? '<span class="badge completed">Active</span>' : '<span class="badge">Inactive</span>' ?>
                    </td>
                    <td>
                        <a href="?action=toggle&id=<?= esc_attr($dept->id) ?>&_wpnonce=<?= wp_create_nonce('toggle_dept_' . $dept->id) ?>" class="button secondary" style="font-size:12px;padding:5px 10px;">
                            <?= $dept->is_active ? 'Deactivate' : 'Activate' ?>
                        </a>
                        <a href="?action=delete&id=<?= esc_attr($dept->id) ?>&_wpnonce=<?= wp_create_nonce('delete_dept_' . $dept->id) ?>" class="button" style="font-size:12px;padding:5px 10px;background:#ef4444;color:white;" onclick="return confirm('Delete this department?')">Delete</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--text-muted);text-align:center;padding:40px">No departments found. Add one above.</p>
        <?php endif; ?>
    </div>
    
    <?php if ($departments): ?>
    <!-- Workload Simulation Section -->
    <div class="card">
        <h3><i class="fa-solid fa-chart-line"></i> İş Yükü Simülasyonu</h3>
        <p style="color:#6b7280;margin-bottom:20px;">Personel değişikliklerinin iş yüküne etkisini simüle edin. İşçi sayılarını değiştirip "Simüle Et" butonuna tıklayın.</p>
        
        <table class="data-table" id="workload-simulation-table">
            <thead>
                <tr>
                    <th>Departman</th>
                    <th>Mevcut İşçi</th>
                    <th>Yeni İşçi</th>
                    <th>Sipariş</th>
                    <th>Mevcut İş Yükü</th>
                    <th>Simüle İş Yükü</th>
                    <th>Fark</th>
                </tr>
            </thead>
            <tbody id="simulation-tbody">
                <?php 
                $workload = production_calculate_department_workload();
                foreach ($workload as $wl): 
                    $dept = array_filter($departments, fn($d) => $d->id == $wl['department_id']);
                    $dept = reset($dept);
                    if (!$dept) continue;
                    
                    $current_hours = floor($wl['scheduled_minutes'] / 60);
                    $current_mins = $wl['scheduled_minutes'] % 60;
                ?>
                <tr data-dept-id="<?= esc_attr($wl['department_id']) ?>">
                    <td><strong><?= esc_html($wl['department']) ?></strong></td>
                    <td class="current-workers"><?= esc_html($dept->workers) ?></td>
                    <td>
                        <input type="number" 
                               class="form-control new-workers-input" 
                               value="<?= esc_attr($dept->workers) ?>" 
                               min="1" 
                               max="100"
                               data-original="<?= esc_attr($dept->workers) ?>"
                               style="width:80px;padding:5px;text-align:center;">
                    </td>
                    <td class="order-count"><?= esc_html($wl['task_count']) ?></td>
                    <td class="current-workload"><?= $current_hours ?> saat <?= $current_mins ?> dk</td>
                    <td class="simulated-workload" style="font-weight:600;">-</td>
                    <td class="workload-diff">-</td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <div style="margin-top:20px;display:flex;gap:10px;">
            <button type="button" id="simulate-btn" class="btn btn-primary">
                <i class="fa-solid fa-play"></i> Simüle Et
            </button>
            <button type="button" id="reset-simulation-btn" class="btn btn-secondary" style="background:#6b7280;">
                <i class="fa-solid fa-rotate-left"></i> Sıfırla
            </button>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Simulate button click
        $('#simulate-btn').on('click', function() {
            const $tbody = $('#simulation-tbody');
            const $rows = $tbody.find('tr');
            
            $rows.each(function() {
                const $row = $(this);
                const currentWorkers = parseInt($row.find('.current-workers').text(), 10);
                const newWorkers = parseInt($row.find('.new-workers-input').val(), 10);
                const currentWorkloadText = $row.find('.current-workload').text();
                
                // Parse current workload (e.g., "59 saat 0 dk")
                const workloadMatch = currentWorkloadText.match(/(\d+)\s*saat\s*(\d+)\s*dk/);
                if (!workloadMatch) return;
                
                const currentHours = parseInt(workloadMatch[1], 10);
                const currentMins = parseInt(workloadMatch[2], 10);
                const currentTotalMins = (currentHours * 60) + currentMins;
                
                // Calculate simulated workload (proportional to worker ratio)
                const simulatedTotalMins = currentWorkers > 0 ? 
                    Math.round(currentTotalMins * (currentWorkers / newWorkers)) : currentTotalMins;
                
                const simHours = Math.floor(simulatedTotalMins / 60);
                const simMins = simulatedTotalMins % 60;
                
                // Calculate difference
                const diffMins = currentTotalMins - simulatedTotalMins;
                const diffHours = Math.floor(Math.abs(diffMins) / 60);
                const diffRemainingMins = Math.abs(diffMins) % 60;
                
                // Update display
                $row.find('.simulated-workload').text(simHours + ' saat ' + simMins + ' dk');
                
                let diffText = '';
                let diffColor = '#6b7280';
                
                if (diffMins > 0) {
                    diffText = '-' + diffHours + ' saat ' + diffRemainingMins + ' dk';
                    diffColor = '#10b981'; // Green (improvement)
                } else if (diffMins < 0) {
                    diffText = '+' + diffHours + ' saat ' + diffRemainingMins + ' dk';
                    diffColor = '#ef4444'; // Red (worse)
                } else {
                    diffText = 'Değişiklik yok';
                }
                
                $row.find('.workload-diff').text(diffText).css('color', diffColor).css('font-weight', '600');
            });
        });
        
        // Reset button click
        $('#reset-simulation-btn').on('click', function() {
            const $tbody = $('#simulation-tbody');
            const $rows = $tbody.find('tr');
            
            $rows.each(function() {
                const $row = $(this);
                const $input = $row.find('.new-workers-input');
                const original = parseInt($input.attr('data-original'), 10);
                
                $input.val(original);
                $row.find('.simulated-workload').text('-');
                $row.find('.workload-diff').text('-').css('color', '#6b7280');
            });
        });
        
        // Auto-simulate on input change
        $('.new-workers-input').on('change', function() {
            $('#simulate-btn').trigger('click');
        });
    });
    </script>
    <?php endif; ?>
    
    <?php
    b2b_adm_footer();
    exit;
}

/* =====================================================
 * 17. PRODUCT ROUTES PAGE
 * ===================================================== */
function production_routes_page() {
    b2b_adm_header('Cabinet Types & Routes');
    
    global $wpdb;
    $dept_table = $wpdb->prefix . 'production_departments';
    $types_table = $wpdb->prefix . 'production_cabinet_types';
    $workflows_table = $wpdb->prefix . 'production_type_workflows';
    $categories_table = $wpdb->prefix . 'production_type_categories';
    
    // Ensure tables exist
    $tables_exist = $wpdb->get_var("SHOW TABLES LIKE '{$types_table}'");
    if (!$tables_exist) {
        production_panel_create_tables();
        update_option('production_panel_tables_created', true);
    }
    
    $departments = $wpdb->get_results("SELECT * FROM {$dept_table} WHERE is_active = 1 ORDER BY name ASC");
    $cabinet_types = $wpdb->get_results("SELECT * FROM {$types_table} WHERE is_active = 1 ORDER BY name ASC");
    
    // Get WooCommerce categories
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'orderby' => 'name'
    ]);
    
    ?>
    <style>
        /* Navigation Styles */
        .page-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }
        .nav-btn:hover {
            background: #e5e7eb;
            color: #1f2937;
            transform: translateY(-1px);
        }
        .nav-btn.active {
            background: #667eea;
            color: white;
        }
        .nav-btn.active:hover {
            background: #5a67d8;
        }
        .nav-btn i {
            font-size: 14px;
        }
        
        /* Card and Form Styles */
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1f2937; font-weight: 600; }
        .card h3 i { margin-right: 8px; color: #667eea; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 5px; font-size: 14px; }
        .form-control, .form-select { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5a67d8; }
        .btn-success { background: #48bb78; color: white; }
        .btn-success:hover { background: #38a169; }
        .btn-danger { background: #f56565; color: white; }
        .btn-danger:hover { background: #e53e3e; }
        .btn-secondary { background: #6b7280; color: white; }
        .btn-secondary:hover { background: #4b5563; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .type-badge { display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600; color: white; }
        .workflow-preview { color: #6b7280; font-size: 13px; }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .data-table thead { background: #f9fafb; }
        .data-table th { padding: 12px; text-align: left; font-weight: 600; color: #6b7280; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .data-table tr:hover { background: #f9fafb; }
        .workflow-step { background: #f9fafb; padding: 12px; border-radius: 8px; margin-bottom: 10px; border-left: 3px solid #667eea; display: flex; align-items: center; gap: 10px; }
        .workflow-step-number { background: #667eea; color: white; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
        .workflow-step-content { flex: 1; display: grid; grid-template-columns: 2fr 1fr auto; gap: 10px; align-items: center; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; display: none; }
        .alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert.error { background: #fee2e2; color: #991b1b; border-left: 4px solid #ef4444; }
        .alert i { margin-right: 8px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .range-value { display: inline-block; min-width: 40px; text-align: center; font-weight: 600; color: #667eea; }
        .category-select { height: 150px; }
        @media (max-width: 768px) { 
            .form-grid { grid-template-columns: 1fr; }
            .workflow-step-content { grid-template-columns: 1fr; }
        }
    </style>
    
    <?php production_page_nav('routes'); ?>
    
    <div id="alertBox" class="alert"></div>
    
    <div class="card">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="margin:0;"><i class="fa-solid fa-layer-group"></i> Cabinet Types</h3>
            <button type="button" id="addNewTypeBtn" class="btn btn-primary">
                <i class="fa-solid fa-plus"></i> Add Cabinet Type
            </button>
        </div>
        
        <?php if (empty($cabinet_types)): ?>
            <p style="color:#6b7280;text-align:center;padding:40px;">No cabinet types configured yet. Click "Add Cabinet Type" to create one.</p>
        <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Workflow</th>
                    <th>Duration</th>
                    <th>Categories</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cabinet_types as $type): 
                    $workflows = $wpdb->get_results($wpdb->prepare(
                        "SELECT w.*, d.name as dept_name 
                        FROM {$workflows_table} w 
                        LEFT JOIN {$dept_table} d ON w.department_id = d.id 
                        WHERE w.cabinet_type_id = %d 
                        ORDER BY w.sequence_order ASC", 
                        $type->id
                    ));
                    
                    $workflow_names = array_map(function($w) { return $w->dept_name; }, $workflows);
                    $total_duration = array_sum(array_map(function($w) { return $w->duration_minutes; }, $workflows));
                    
                    $assigned_categories = $wpdb->get_results($wpdb->prepare(
                        "SELECT category_id FROM {$categories_table} WHERE cabinet_type_id = %d",
                        $type->id
                    ));
                    $category_count = count($assigned_categories);
                ?>
                <tr>
                    <td>
                        <span class="type-badge" style="background:<?= esc_attr($type->color) ?>">
                            <?= esc_html($type->name) ?>
                        </span>
                    </td>
                    <td>
                        <div class="workflow-preview">
                            <?= !empty($workflow_names) ? implode(' → ', array_map('esc_html', $workflow_names)) : 'No workflow defined' ?>
                        </div>
                    </td>
                    <td><?= esc_html($total_duration) ?> min</td>
                    <td><?= $category_count ?> categor<?= $category_count === 1 ? 'y' : 'ies' ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary edit-type-btn" data-id="<?= esc_attr($type->id) ?>">
                            <i class="fa-solid fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger delete-type-btn" data-id="<?= esc_attr($type->id) ?>">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
    
    <!-- Cabinet Type Form -->
    <div class="card" id="cabinetTypeForm" style="display:none;">
        <h3><i class="fa-solid fa-edit"></i> <span id="formTitle">Add Cabinet Type</span></h3>
        
        <form id="typeForm">
            <input type="hidden" id="typeId" name="id" value="0">
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Type Name *</label>
                    <input type="text" id="typeName" name="name" class="form-control" placeholder="e.g., Base Cabinet" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Color</label>
                    <input type="color" id="typeColor" name="color" class="form-control" value="#667eea">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea id="typeDescription" name="description" class="form-control" rows="2" placeholder="Optional description"></textarea>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Time Multiplier: <span class="range-value" id="multiplierValue">1.00</span></label>
                    <input type="range" id="timeMultiplier" name="time_multiplier" class="form-control" min="0.5" max="3" step="0.1" value="1.0" style="padding:0;">
                </div>
                <div class="form-group">
                    <label class="form-label">Base Duration (min)</label>
                    <input type="number" id="baseDuration" name="base_duration" class="form-control" value="0" min="0">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Product Categories</label>
                <select id="typeCategories" name="categories[]" class="form-control category-select" multiple>
                    <?php foreach ($categories as $category): ?>
                    <option value="<?= esc_attr($category->term_id) ?>"><?= esc_html($category->name) ?></option>
                    <?php endforeach; ?>
                </select>
                <small style="color:#6b7280;">Hold Ctrl/Cmd to select multiple categories</small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Department Workflow</label>
                <div id="workflowSteps"></div>
                <button type="button" id="addWorkflowStepBtn" class="btn btn-success btn-sm" style="margin-top:10px;">
                    <i class="fa-solid fa-plus"></i> Add Step
                </button>
            </div>
            
            <div style="display:flex;gap:10px;margin-top:20px;">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-save"></i> Save Cabinet Type
                </button>
                <button type="button" id="cancelFormBtn" class="btn btn-secondary">
                    <i class="fa-solid fa-times"></i> Cancel
                </button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        const departments = <?= json_encode($departments) ?>;
        let workflowCounter = 0;
        
        // Show alert
        function showAlert(message, type = 'success') {
            $('#alertBox').removeClass('success error').addClass(type).html(`<i class="fa-solid fa-${type === 'success' ? 'check' : 'exclamation'}-circle"></i> ${message}`).fadeIn();
            setTimeout(() => $('#alertBox').fadeOut(), 5000);
        }
        
        // Time multiplier slider
        $('#timeMultiplier').on('input', function() {
            $('#multiplierValue').text(parseFloat($(this).val()).toFixed(2));
        });
        
        // Add new type
        $('#addNewTypeBtn').on('click', function() {
            resetForm();
            $('#formTitle').text('Add Cabinet Type');
            $('#cabinetTypeForm').slideDown();
            $('html, body').animate({ scrollTop: $('#cabinetTypeForm').offset().top - 100 }, 500);
        });
        
        // Cancel form
        $('#cancelFormBtn').on('click', function() {
            $('#cabinetTypeForm').slideUp();
            resetForm();
        });
        
        // Reset form
        function resetForm() {
            $('#typeForm')[0].reset();
            $('#typeId').val('0');
            $('#workflowSteps').empty();
            $('#multiplierValue').text('1.00');
            workflowCounter = 0;
        }
        
        // Add workflow step
        $('#addWorkflowStepBtn').on('click', function() {
            addWorkflowStep();
        });
        
        function addWorkflowStep(deptId = '', duration = '') {
            workflowCounter++;
            const stepHtml = `
            <div class="workflow-step" data-step="${workflowCounter}">
                <span class="workflow-step-number">${workflowCounter}</span>
                <div class="workflow-step-content">
                    <select name="workflow_dept_${workflowCounter}" class="form-select" required>
                        <option value="">Select Department</option>
                        ${departments.map(d => `<option value="${d.id}" ${d.id == deptId ? 'selected' : ''}>${d.name}</option>`).join('')}
                    </select>
                    <input type="number" name="workflow_duration_${workflowCounter}" class="form-control" placeholder="Duration (min)" value="${duration}" min="0" required>
                    <button type="button" class="btn btn-danger btn-sm remove-workflow-step"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>`;
            $('#workflowSteps').append(stepHtml);
            updateWorkflowNumbers();
        }
        
        // Remove workflow step
        $(document).on('click', '.remove-workflow-step', function() {
            $(this).closest('.workflow-step').remove();
            updateWorkflowNumbers();
        });
        
        // Update workflow step numbers
        function updateWorkflowNumbers() {
            $('.workflow-step').each(function(index) {
                $(this).find('.workflow-step-number').text(index + 1);
            });
        }
        
        // Save form
        $('#typeForm').on('submit', function(e) {
            e.preventDefault();
            
            const workflows = [];
            $('.workflow-step').each(function() {
                const step = $(this).data('step');
                const deptId = $(`[name="workflow_dept_${step}"]`).val();
                const duration = $(`[name="workflow_duration_${step}"]`).val();
                if (deptId && duration) {
                    workflows.push({ department_id: deptId, duration_minutes: duration });
                }
            });
            
            const formData = {
                action: 'production_save_cabinet_type',
                nonce: '<?= wp_create_nonce('production_ajax') ?>',
                id: $('#typeId').val(),
                name: $('#typeName').val(),
                description: $('#typeDescription').val(),
                color: $('#typeColor').val(),
                time_multiplier: $('#timeMultiplier').val(),
                base_duration: $('#baseDuration').val(),
                workflows: JSON.stringify(workflows),
                categories: $('#typeCategories').val() || []
            };
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        showAlert(response.data.message, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert(response.data || 'Failed to save', 'error');
                    }
                },
                error: function() {
                    showAlert('Network error occurred', 'error');
                }
            });
        });
        
        // Edit type
        $(document).on('click', '.edit-type-btn', function() {
            const typeId = $(this).data('id');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'production_get_cabinet_type',
                    nonce: '<?= wp_create_nonce('production_ajax') ?>',
                    id: typeId
                },
                success: function(response) {
                    if (response.success) {
                        const type = response.data.type;
                        const workflows = response.data.workflows;
                        const categories = response.data.categories;
                        
                        $('#formTitle').text('Edit Cabinet Type');
                        $('#typeId').val(type.id);
                        $('#typeName').val(type.name);
                        $('#typeDescription').val(type.description || '');
                        $('#typeColor').val(type.color);
                        $('#timeMultiplier').val(type.time_multiplier);
                        $('#multiplierValue').text(parseFloat(type.time_multiplier).toFixed(2));
                        $('#baseDuration').val(type.base_duration);
                        
                        $('#typeCategories').val(categories.map(c => c.category_id));
                        
                        $('#workflowSteps').empty();
                        workflowCounter = 0;
                        workflows.forEach(w => {
                            addWorkflowStep(w.department_id, w.duration_minutes);
                        });
                        
                        $('#cabinetTypeForm').slideDown();
                        $('html, body').animate({ scrollTop: $('#cabinetTypeForm').offset().top - 100 }, 500);
                    }
                }
            });
        });
        
        // Delete type
        $(document).on('click', '.delete-type-btn', function() {
            if (!confirm('Are you sure you want to delete this cabinet type?')) return;
            
            const typeId = $(this).data('id');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'production_delete_cabinet_type',
                    nonce: '<?= wp_create_nonce('production_ajax') ?>',
                    id: typeId
                },
                success: function(response) {
                    if (response.success) {
                        showAlert(response.data, 'success');
                        setTimeout(() => location.reload(), 1000);
                    } else {
                        showAlert(response.data || 'Failed to delete', 'error');
                    }
                }
            });
        });
    });
    </script>
    
    <?php
    b2b_adm_footer();
    exit;
}

// Ajax handler for product search
add_action('wp_ajax_search_products', function() {
    check_ajax_referer('production_ajax', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Unauthorized');
    }
    
    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    
    $args = [
        'post_type' => 'product',
        'posts_per_page' => 10,
        's' => $query,
        'post_status' => 'publish'
    ];
    
    $products_query = new WP_Query($args);
    $products = [];
    
    if ($products_query->have_posts()) {
        while ($products_query->have_posts()) {
            $products_query->the_post();
            $products[] = [
                'id' => get_the_ID(),
                'name' => get_the_title()
            ];
        }
        wp_reset_postdata();
    }
    
    wp_send_json_success($products);
});

/* =====================================================
 * 18. CALENDAR PAGE
 * ===================================================== */
function production_calendar_page() {
    b2b_adm_header('Production Calendar');
    ?>
    <style>
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1f2937; font-weight: 600; }
        .card h3 i { margin-right: 8px; color: #667eea; }
        .card h4 { color: #6b7280; margin: 10px 0; }
        .card p { color: #6b7280; line-height: 1.6; }
        .page-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .nav-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .nav-btn.active {
            background: #667eea;
            color: white;
            border-color: #4c51bf;
        }
        .nav-btn i {
            font-size: 16px;
        }
    </style>
    
    <?php production_page_nav('calendar'); ?>
    
    <div class="card">
        <h3><i class="fa-solid fa-calendar"></i> Production Timeline Calendar</h3>
        <p style="color:#6b7280;margin-bottom:20px">
            Visual timeline for production scheduling with drag-and-drop functionality. Click on an event to view details or drag to reschedule.
        </p>
        
        <!-- Calendar filters -->
        <div style="margin-bottom:20px;display:flex;gap:15px;flex-wrap:wrap;align-items:center;">
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="showScheduled" checked> Scheduled
            </label>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="showInProgress" checked> In Progress
            </label>
            <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" id="showCompleted"> Completed
            </label>
            <button id="refreshCalendar" class="btn btn-sm btn-primary" style="margin-left:auto;">
                <i class="fa-solid fa-refresh"></i> Refresh
            </button>
        </div>
        
        <div id="productionCalendar" style="background:white;padding:20px;border-radius:8px;"></div>
    </div>
    
    <!-- Event Details Modal -->
    <div id="eventModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
        <div style="background:white;padding:30px;border-radius:12px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;position:relative;">
            <button id="closeModal" style="position:absolute;top:15px;right:15px;background:none;border:none;font-size:24px;cursor:pointer;color:#6b7280;">&times;</button>
            <h3 id="modalTitle" style="margin:0 0 20px 0;font-size:20px;color:#1f2937;"></h3>
            <div id="modalContent"></div>
            <div style="margin-top:20px;display:flex;gap:10px;justify-content:flex-end;">
                <button id="modalClose" class="btn btn-primary">Close</button>
                <button id="modalDelete" class="btn btn-danger">Delete Schedule</button>
            </div>
        </div>
    </div>
    
    <!-- FullCalendar CSS -->
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" 
          integrity="sha384-EbYhZurJHqBaY0z+a/Xmvr+QiQdqE7N6n2f3rQa1xQlzQF9zxR2K7XgF5qLmYn5F" 
          crossorigin="anonymous">
    
    <!-- FullCalendar JS -->
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js" 
            integrity="sha384-5L7zHqHqDe1p9W8DqJqE+3Y7Y3pF3Y7Yt3zFqJ3qFqE3qFqE3qFqE3qFqE3qFqE3" 
            crossorigin="anonymous"></script>
    
    <!-- Note: SRI hashes above are examples. In production, use actual hashes from:
         https://www.jsdelivr.com/package/npm/fullcalendar or generate using openssl dgst -sha384 -->
    
    <script>
    jQuery(document).ready(function($) {
        const calendarEl = document.getElementById('productionCalendar');
        let calendar;
        let currentEvent = null;
        
        // Initialize FullCalendar
        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'timeGridWeek',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            slotMinTime: '07:00:00',
            slotMaxTime: '19:00:00',
            height: 'auto',
            editable: true,
            droppable: true,
            eventResizableFromStart: true,
            events: function(info, successCallback, failureCallback) {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'production_get_calendar_events',
                        nonce: '<?= wp_create_nonce('production_ajax') ?>'
                    },
                    success: function(response) {
                        if (response.success) {
                            successCallback(response.data);
                        } else {
                            failureCallback();
                        }
                    },
                    error: function() {
                        failureCallback();
                    }
                });
            },
            eventClick: function(info) {
                currentEvent = info.event;
                showEventModal(info.event);
            },
            eventDrop: function(info) {
                updateEventTime(info.event);
            },
            eventResize: function(info) {
                updateEventTime(info.event);
            },
            eventDidMount: function(info) {
                // Add tooltip
                $(info.el).attr('title', info.event.extendedProps.department + ' - ' + info.event.extendedProps.status);
            }
        });
        
        calendar.render();
        
        // Show event details modal
        function showEventModal(event) {
            const props = event.extendedProps;
            
            let statusBadge = '';
            if (props.status === 'scheduled') statusBadge = '<span style="background:#dbeafe;color:#1e40af;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">Scheduled</span>';
            else if (props.status === 'in-progress') statusBadge = '<span style="background:#fed7aa;color:#c05621;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">In Progress</span>';
            else if (props.status === 'completed') statusBadge = '<span style="background:#d1fae5;color:#065f46;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:600;">Completed</span>';
            
            $('#modalTitle').html(event.title);
            $('#modalContent').html(`
                <div style="display:grid;gap:15px;">
                    <div>
                        <strong style="color:#6b7280;">Status:</strong><br>
                        ${statusBadge}
                    </div>
                    <div>
                        <strong style="color:#6b7280;">Department:</strong><br>
                        <span style="display:inline-block;width:12px;height:12px;background:${event.backgroundColor};border-radius:50%;margin-right:5px;"></span>
                        ${props.department}
                    </div>
                    <div>
                        <strong style="color:#6b7280;">Scheduled Time:</strong><br>
                        ${event.start.toLocaleString()} - ${event.end.toLocaleString()}
                    </div>
                    <div>
                        <strong style="color:#6b7280;">Order ID:</strong><br>
                        #${props.order_id}
                    </div>
                    <div>
                        <strong style="color:#6b7280;">Product ID:</strong><br>
                        #${props.product_id}
                    </div>
                    ${props.notes ? `<div><strong style="color:#6b7280;">Notes:</strong><br>${props.notes}</div>` : ''}
                </div>
            `);
            
            $('#eventModal').css('display', 'flex');
        }
        
        // Update event time after drag/resize
        function updateEventTime(event) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'production_update_schedule',
                    nonce: '<?= wp_create_nonce('production_ajax') ?>',
                    schedule_id: event.id,
                    start: event.start.toISOString(),
                    end: event.end.toISOString()
                },
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        showNotification('Schedule updated successfully', 'success');
                    } else {
                        // Revert if failed
                        event.revert();
                        showNotification(response.data || 'Failed to update schedule', 'error');
                    }
                },
                error: function() {
                    event.revert();
                    showNotification('Failed to update schedule', 'error');
                }
            });
        }
        
        // Close modal
        $('#closeModal, #modalClose').on('click', function() {
            $('#eventModal').hide();
            currentEvent = null;
        });
        
        // Delete event
        $('#modalDelete').on('click', function() {
            if (!currentEvent || !confirm('Are you sure you want to delete this schedule?')) {
                return;
            }
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'production_delete_schedule',
                    nonce: '<?= wp_create_nonce('production_ajax') ?>',
                    schedule_id: currentEvent.id
                },
                success: function(response) {
                    if (response.success) {
                        currentEvent.remove();
                        $('#eventModal').hide();
                        showNotification('Schedule deleted successfully', 'success');
                    } else {
                        showNotification('Failed to delete schedule', 'error');
                    }
                }
            });
        });
        
        // Refresh calendar
        $('#refreshCalendar').on('click', function() {
            calendar.refetchEvents();
            showNotification('Calendar refreshed', 'success');
        });
        
        // Filter events by status
        $('#showScheduled, #showInProgress, #showCompleted').on('change', function() {
            calendar.refetchEvents();
        });
        
        // Show notification
        function showNotification(message, type) {
            const bgColor = type === 'success' ? '#d1fae5' : '#fee2e2';
            const textColor = type === 'success' ? '#065f46' : '#991b1b';
            
            const notification = $(`
                <div style="position:fixed;top:20px;right:20px;background:${bgColor};color:${textColor};padding:15px 20px;border-radius:8px;box-shadow:0 4px 6px rgba(0,0,0,0.1);z-index:10000;animation:slideIn 0.3s ease;">
                    ${message}
                </div>
            `);
            
            $('body').append(notification);
            
            setTimeout(() => {
                notification.fadeOut(() => notification.remove());
            }, 3000);
        }
    });
    </script>
    
    <style>
    @keyframes slideIn {
        from { transform: translateX(400px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    
    /* FullCalendar customizations */
    .fc-event {
        cursor: pointer;
        border: none !important;
        padding: 2px 4px;
    }
    
    .fc-event:hover {
        opacity: 0.9;
        transform: translateY(-1px);
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }
    
    .fc-toolbar-title {
        font-size: 1.5em !important;
        font-weight: 600 !important;
        color: #1f2937;
    }
    
    .fc-button {
        background: #667eea !important;
        border-color: #667eea !important;
        text-transform: capitalize !important;
    }
    
    .fc-button:hover {
        background: #5a67d8 !important;
        border-color: #5a67d8 !important;
    }
    
    .fc-button-active {
        background: #4c51bf !important;
        border-color: #4c51bf !important;
    }
    </style>
    
    <?php
    b2b_adm_footer();
    exit;
}

/* =====================================================
 * 11. ANALYTICS PAGE
 * ===================================================== */
function production_analytics_page() {
    b2b_adm_header('Production Analytics');
    
    ?>
    <style>
        .stat-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .stat-card { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; border-radius: 12px; color: #ffffff !important; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .stat-card.blue { background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%); }
        .stat-card.green { background: linear-gradient(135deg, #48bb78 0%, #38a169 100%); }
        .stat-label { font-size: 14px; color: #ffffff !important; opacity: 0.95; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 500; }
        .stat-value { font-size: 36px; font-weight: 700; color: #ffffff !important; }
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1f2937; font-weight: 600; }
        .card h3 i { margin-right: 8px; color: #667eea; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: #f9fafb; }
        .data-table th { padding: 12px; text-align: left; font-weight: 600; color: #6b7280; font-size: 13px; text-transform: uppercase; border-bottom: 2px solid #e5e7eb; }
        .data-table td { padding: 12px; border-bottom: 1px solid #f3f4f6; color: #374151; }
        .data-table tr:hover { background: #f9fafb; }
        .color-swatch { display: inline-block; width: 16px; height: 16px; border-radius: 50%; margin-right: 8px; vertical-align: middle; }
        .page-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .nav-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .nav-btn.active {
            background: #667eea;
            color: white;
            border-color: #4c51bf;
        }
        .nav-btn i {
            font-size: 16px;
        }
    </style>
    
    <?php 
    production_page_nav('analytics'); 
    
    global $wpdb;
    $table_schedule = $wpdb->prefix . 'production_schedule';
    $table_departments = $wpdb->prefix . 'production_departments';
    
    // Calculate analytics
    $total_completed = $wpdb->get_var("SELECT COUNT(*) FROM {$table_schedule} WHERE status = 'completed'");
    $avg_time = $wpdb->get_var("
        SELECT AVG(TIMESTAMPDIFF(MINUTE, actual_start, actual_end)) 
        FROM {$table_schedule} 
        WHERE status = 'completed' AND actual_start IS NOT NULL AND actual_end IS NOT NULL
    ");
    
    $dept_performance = $wpdb->get_results("
        SELECT d.name, d.color, 
            COUNT(s.id) as total_orders,
            SUM(CASE WHEN s.status = 'completed' THEN 1 ELSE 0 END) as completed_orders,
            AVG(TIMESTAMPDIFF(MINUTE, s.actual_start, s.actual_end)) as avg_time
        FROM {$table_departments} d
        LEFT JOIN {$table_schedule} s ON d.id = s.department_id
        WHERE d.is_active = 1
        GROUP BY d.id
        ORDER BY completed_orders DESC
    ");
    
    ?>
    
    <div class="stat-grid">
        <div class="stat-card green">
            <div class="stat-label">Total Completed Orders</div>
            <div class="stat-value"><?= esc_html($total_completed ?: 0) ?></div>
        </div>
        <div class="stat-card blue">
            <div class="stat-label">Average Production Time</div>
            <div class="stat-value"><?= $avg_time ? round($avg_time) . ' min' : 'N/A' ?></div>
        </div>
    </div>
    
    <div class="card">
        <h3><i class="fa-solid fa-chart-bar"></i> Department Performance</h3>
        <?php if ($dept_performance): ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Department</th>
                    <th>Total Orders</th>
                    <th>Completed Orders</th>
                    <th>Completion Rate</th>
                    <th>Avg Production Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dept_performance as $perf): ?>
                <tr>
                    <td>
                        <span style="display:inline-block;width:10px;height:10px;background:<?= esc_attr($perf->color) ?>;border-radius:50%;margin-right:5px;"></span>
                        <strong><?= esc_html($perf->name) ?></strong>
                    </td>
                    <td><?= esc_html($perf->total_orders ?: 0) ?></td>
                    <td><?= esc_html($perf->completed_orders ?: 0) ?></td>
                    <td>
                        <?php 
                        $rate = $perf->total_orders > 0 ? round(($perf->completed_orders / $perf->total_orders) * 100) : 0;
                        echo esc_html($rate) . '%';
                        ?>
                    </td>
                    <td><?= $perf->avg_time ? round($perf->avg_time) . ' min' : 'N/A' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:var(--text-muted);text-align:center;padding:40px">No data available yet.</p>
        <?php endif; ?>
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}

/* =====================================================
 * 12. REPORTS PAGE
 * ===================================================== */
function production_reports_page() {
    b2b_adm_header('Production Reports');
    
    // Handle CSV export
    if (isset($_GET['export']) && $_GET['export'] === 'csv') {
        check_admin_referer('export_production_report');
        
        global $wpdb;
        $table = $wpdb->prefix . 'production_schedule';
        $data = $wpdb->get_results("
            SELECT s.*, d.name as department_name 
            FROM {$table} s 
            LEFT JOIN {$wpdb->prefix}production_departments d ON s.department_id = d.id 
            ORDER BY s.scheduled_start DESC 
            LIMIT 1000
        ", ARRAY_A);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="production-report-' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
        exit();
    }
    
    ?>
    
    <div class="card">
        <h3><i class="fa-solid fa-file-export"></i> Export Production Report</h3>
        <p style="color:var(--text-muted);margin-bottom:20px">
            Download production data in CSV format for analysis in Excel or other tools.
        </p>
        
        <a href="?export=csv&_wpnonce=<?= wp_create_nonce('export_production_report') ?>" class="button primary">
            <i class="fa-solid fa-download"></i> Export to CSV
        </a>
    </div>
    
    <div class="card">
        <h3><i class="fa-solid fa-chart-pie"></i> Report Summary</h3>
        <p style="color:var(--text-muted)">
            Advanced reporting features with charts and graphs can be added here.
        </p>
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}

/* =====================================================
 * 13. SETTINGS PAGE
 * ===================================================== */
function production_settings_page() {
    b2b_adm_header('Production Settings');
    
    ?>
    <style>
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1f2937; font-weight: 600; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px; }
        .form-control { width: 100%; max-width: 400px; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .btn { padding: 12px 24px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5a67d8; }
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert i { margin-right: 8px; }
        input[type="checkbox"] { width: 18px; height: 18px; cursor: pointer; }
        label { cursor: pointer; color: #374151; }
        .page-nav {
            background: white;
            padding: 15px 20px;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .nav-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: #f3f4f6;
            color: #374151;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        .nav-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.3);
        }
        .nav-btn.active {
            background: #667eea;
            color: white;
            border-color: #4c51bf;
        }
        .nav-btn i {
            font-size: 16px;
        }
    </style>
    
    <?php 
    production_page_nav('settings'); 
    
    // Handle settings save
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce'])) {
        check_admin_referer('production_save_settings');
        
        $settings = [
            'daily_hours' => absint($_POST['daily_hours']),
            'working_days' => isset($_POST['working_days']) ? array_map('sanitize_text_field', $_POST['working_days']) : [],
            'cache_duration' => absint($_POST['cache_duration']),
            'notifications_enabled' => isset($_POST['notifications_enabled']) ? 1 : 0
        ];
        
        update_option('production_panel_settings', $settings);
        echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Settings saved successfully!</div>';
    }
    
    $settings = get_option('production_panel_settings', [
        'daily_hours' => 8,
        'working_days' => ['1', '2', '3', '4', '5'],
        'cache_duration' => 3600,
        'notifications_enabled' => 0
    ]);
    
    ?>
    
    <div class="card">
        <h3>Production Settings</h3>
        <form method="post">
            <?php wp_nonce_field('production_save_settings'); ?>
            
            <div class="form-group">
                <label class="form-label">Daily Working Hours</label>
                <input type="number" name="daily_hours" class="form-control" value="<?= esc_attr($settings['daily_hours']) ?>" min="1" max="24">
            </div>
            
            <div class="form-group">
                <label class="form-label">Working Days</label>
                <div style="display:flex;gap:15px;flex-wrap:wrap;">
                    <?php
                    $days = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '0' => 'Sunday'];
                    foreach ($days as $value => $label):
                    ?>
                    <label style="display:flex;align-items:center;gap:8px;">
                        <input type="checkbox" name="working_days[]" value="<?= esc_attr($value) ?>" <?= in_array($value, $settings['working_days']) ? 'checked' : '' ?>>
                        <?= esc_html($label) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Cache Duration (seconds)</label>
                <input type="number" name="cache_duration" class="form-control" value="<?= esc_attr($settings['cache_duration']) ?>" min="0">
                <small style="color:var(--text-muted)">How long to cache production data. Set to 0 to disable caching.</small>
            </div>
            
            <div class="form-group">
                <label style="display:flex;align-items:center;gap:10px;">
                    <input type="checkbox" name="notifications_enabled" value="1" <?= $settings['notifications_enabled'] ? 'checked' : '' ?>>
                    Enable production notifications
                </label>
            </div>
            
            <button type="submit" class="button primary">Save Settings</button>
        </form>
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}

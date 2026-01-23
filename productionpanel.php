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
    register_post_status('wc-in-production', [
        'label'                     => 'In Production',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('In Production <span class="count">(%s)</span>', 'In Production <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-cutting', [
        'label'                     => 'Cutting',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Cutting <span class="count">(%s)</span>', 'Cutting <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-sewing', [
        'label'                     => 'Sewing',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Sewing <span class="count">(%s)</span>', 'Sewing <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-quality-check', [
        'label'                     => 'Quality Control',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Quality Control <span class="count">(%s)</span>', 'Quality Control <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-packaging', [
        'label'                     => 'Packaging',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Packaging <span class="count">(%s)</span>', 'Packaging <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-ready-to-ship', [
        'label'                     => 'Ready to Ship',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Ready to Ship <span class="count">(%s)</span>', 'Ready to Ship <span class="count">(%s)</span>')
    ]);
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
    
    $items = [];
    foreach ($order->get_items() as $item) {
        $product = $item->get_product();
        $items[] = [
            'product_id' => $item->get_product_id(),
            'name' => $item->get_name(),
            'quantity' => $item->get_quantity(),
            'sku' => $product ? $product->get_sku() : '',
            'image' => $product && $product->get_image_id() ? wp_get_attachment_url($product->get_image_id()) : ''
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
            
            orderData.items.forEach(function(item) {
                html += '<label style="display:flex;align-items:center;padding:10px;background:white;border-radius:6px;cursor:pointer;border:2px solid #e5e7eb;" class="product-option">';
                html += '<input type="radio" name="product_id" value="' + item.product_id + '" required style="margin-right:10px;">';
                html += '<div style="flex:1;">';
                html += '<strong>' + item.name + '</strong>';
                html += '<div style="color:#6b7280;font-size:13px;">SKU: ' + (item.sku || 'N/A') + ' | Qty: ' + item.quantity + '</div>';
                html += '</div>';
                html += '</label>';
            });
            
            html += '</div></div>';
            $('#order_products_container').html(html).show();
            
            // Auto-fill quantity from first product
            if (orderData.items.length > 0) {
                $('input[name="quantity"]').val(orderData.items[0].quantity);
            }
            
            // Update quantity when product selection changes
            $(document).on('change', 'input[name="product_id"]', function() {
                const selectedProductId = $(this).val();
                const selectedItem = orderData.items.find(item => item.product_id == selectedProductId);
                if (selectedItem) {
                    $('input[name="quantity"]').val(selectedItem.quantity);
                }
            });
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
    
    <?php
    b2b_adm_footer();
    exit;
}

/* =====================================================
 * 17. PRODUCT ROUTES PAGE
 * ===================================================== */
function production_routes_page() {
    b2b_adm_header('Product Routes');
    
    ?>
    <style>
        .card { background: white; padding: 25px; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .card h3 { margin: 0 0 20px 0; font-size: 18px; color: #1f2937; font-weight: 600; }
        .card h3 i { margin-right: 8px; color: #667eea; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; font-weight: 600; color: #374151; margin-bottom: 5px; font-size: 14px; }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; }
        .form-select { width: 100%; padding: 10px 12px; border: 1px solid #d1d5db; border-radius: 6px; font-size: 14px; background: white; }
        .btn { padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px; }
        .btn-primary { background: #667eea; color: white; }
        .btn-primary:hover { background: #5a67d8; }
        .btn-success { background: #48bb78; color: white; }
        .btn-success:hover { background: #38a169; }
        .btn-danger { background: #f56565; color: white; }
        .btn-danger:hover { background: #e53e3e; }
        .btn-sm { padding: 6px 12px; font-size: 12px; }
        .route-step {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 3px solid #667eea;
            position: relative;
        }
        .route-step-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .route-step-number {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
        }
        .route-step-content {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 10px;
            align-items: end;
        }
        .product-search {
            position: relative;
            margin-bottom: 20px;
        }
        .product-search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            max-height: 300px;
            overflow-y: auto;
            z-index: 100;
            display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .product-search-results.active {
            display: block;
        }
        .product-result {
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f3f4f6;
        }
        .product-result:hover {
            background: #f9fafb;
        }
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
        .alert { padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; }
        .alert.success { background: #d1fae5; color: #065f46; border-left: 4px solid #10b981; }
        .alert.info { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }
        .alert i { margin-right: 8px; }
        .total-time {
            background: #f0fdf4;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 2px solid #10b981;
        }
        .total-time-value {
            font-size: 32px;
            font-weight: 700;
            color: #065f46;
        }
        .total-time-label {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
    
    <?php 
    production_page_nav('routes');
    
    global $wpdb;
    $dept_table = $wpdb->prefix . 'production_departments';
    $departments = $wpdb->get_results("SELECT * FROM {$dept_table} WHERE is_active = 1 ORDER BY name ASC");
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_routes'])) {
        check_admin_referer('production_save_routes');
        
        $product_id = absint($_POST['product_id']);
        $routes = [];
        
        if (isset($_POST['routes'])) {
            foreach ($_POST['routes'] as $route_data) {
                $routes[] = [
                    'department_id' => absint($route_data['department_id']),
                    'estimated_time' => absint($route_data['estimated_time']),
                    'setup_time' => absint($route_data['setup_time'] ?? 0),
                    'notes' => sanitize_textarea_field($route_data['notes'] ?? '')
                ];
            }
        }
        
        Production_Routes::save_product_routes($product_id, $routes);
        echo '<div class="alert success"><i class="fa-solid fa-check-circle"></i> Product routes saved successfully!</div>';
    }
    
    // Get selected product if provided
    $selected_product_id = isset($_GET['product_id']) ? absint($_GET['product_id']) : 0;
    $selected_product = null;
    $existing_routes = [];
    
    if ($selected_product_id) {
        $selected_product = wc_get_product($selected_product_id);
        $existing_routes = Production_Routes::get_product_routes($selected_product_id);
    }
    ?>
    
    <div class="card">
        <h3><i class="fa-solid fa-route"></i> Product Route Configuration</h3>
        <p style="color:#6b7280;margin-bottom:20px;">
            Define the production workflow for products. Specify which departments a product must pass through and the time required for each step.
        </p>
        
        <!-- Product Search -->
        <div class="product-search">
            <label class="form-label">Select Product</label>
            <input type="text" id="productSearch" class="form-control" placeholder="Search for a product..." 
                value="<?= $selected_product ? esc_attr($selected_product->get_name()) : '' ?>">
            <div id="productSearchResults" class="product-search-results"></div>
        </div>
        
        <div id="routeConfigSection" style="<?= $selected_product_id ? '' : 'display:none' ?>">
            <form method="post" id="routeForm">
                <?php wp_nonce_field('production_save_routes'); ?>
                <input type="hidden" name="product_id" id="selectedProductId" value="<?= esc_attr($selected_product_id) ?>">
                <input type="hidden" name="save_routes" value="1">
                
                <div id="routeSteps">
                    <?php if (!empty($existing_routes)): ?>
                        <?php foreach ($existing_routes as $index => $route): ?>
                        <div class="route-step">
                            <div class="route-step-header">
                                <span class="route-step-number"><?= $index + 1 ?></span>
                                <strong>Step <?= $index + 1 ?></strong>
                            </div>
                            <div class="route-step-content">
                                <div class="form-group">
                                    <label class="form-label">Department</label>
                                    <select name="routes[<?= $index ?>][department_id]" class="form-select" required>
                                        <option value="">Select Department</option>
                                        <?php foreach ($departments as $dept): ?>
                                        <option value="<?= esc_attr($dept->id) ?>" <?= selected($route->department_id, $dept->id, false) ?>>
                                            <?= esc_html($dept->name) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Time per Unit (min)</label>
                                    <input type="number" name="routes[<?= $index ?>][estimated_time]" class="form-control" value="<?= esc_attr($route->estimated_time) ?>" min="1" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Setup Time (min)</label>
                                    <input type="number" name="routes[<?= $index ?>][setup_time]" class="form-control" value="<?= esc_attr($route->setup_time) ?>" min="0">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Notes</label>
                                    <input type="text" name="routes[<?= $index ?>][notes]" class="form-control" value="<?= esc_attr($route->notes ?? '') ?>" placeholder="Optional notes">
                                </div>
                                <div style="padding-top:28px;">
                                    <button type="button" class="btn btn-danger btn-sm remove-step">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <div style="margin:20px 0;">
                    <button type="button" id="addStepBtn" class="btn btn-success">
                        <i class="fa-solid fa-plus"></i> Add Production Step
                    </button>
                </div>
                
                <?php if (!empty($existing_routes)): ?>
                <div class="total-time">
                    <div class="total-time-value">
                        <?php
                        $total_time = 0;
                        foreach ($existing_routes as $route) {
                            $total_time += $route->estimated_time + $route->setup_time;
                        }
                        echo $total_time;
                        ?> min
                    </div>
                    <div class="total-time-label">Total Production Time (per unit)</div>
                </div>
                <?php endif; ?>
                
                <div style="margin-top:20px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i> Save Routes
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <?php if (!$selected_product_id): ?>
    <div class="card">
        <h3><i class="fa-solid fa-info-circle"></i> Recent Products with Routes</h3>
        <?php
        global $wpdb;
        $routes_table = $wpdb->prefix . 'production_routes';
        $products_with_routes = $wpdb->get_results("
            SELECT DISTINCT product_id, COUNT(*) as route_count
            FROM {$routes_table}
            GROUP BY product_id
            ORDER BY product_id DESC
            LIMIT 10
        ");
        
        if ($products_with_routes):
        ?>
        <table style="width:100%;border-collapse:collapse;margin-top:15px;">
            <thead style="background:#f9fafb;">
                <tr>
                    <th style="padding:10px;text-align:left;border-bottom:2px solid #e5e7eb;">Product</th>
                    <th style="padding:10px;text-align:left;border-bottom:2px solid #e5e7eb;">Routes Count</th>
                    <th style="padding:10px;text-align:left;border-bottom:2px solid #e5e7eb;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products_with_routes as $item): 
                    $product = wc_get_product($item->product_id);
                    if (!$product) continue;
                ?>
                <tr>
                    <td style="padding:10px;border-bottom:1px solid #f3f4f6;"><?= esc_html($product->get_name()) ?></td>
                    <td style="padding:10px;border-bottom:1px solid #f3f4f6;"><?= esc_html($item->route_count) ?> steps</td>
                    <td style="padding:10px;border-bottom:1px solid #f3f4f6;">
                        <a href="?product_id=<?= esc_attr($item->product_id) ?>" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-edit"></i> Edit Routes
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color:#6b7280;text-align:center;padding:40px;">No products with routes yet. Search for a product above to get started.</p>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <script>
    jQuery(document).ready(function($) {
        let stepCounter = <?= count($existing_routes) ?>;
        
        // Product search
        let searchTimeout;
        $('#productSearch').on('input', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val();
            
            if (query.length < 2) {
                $('#productSearchResults').removeClass('active').empty();
                return;
            }
            
            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'search_products',
                        nonce: '<?= wp_create_nonce('production_ajax') ?>',
                        query: query
                    },
                    success: function(response) {
                        if (response.success && response.data.length > 0) {
                            let html = '';
                            response.data.forEach(product => {
                                html += `<div class="product-result" data-id="${product.id}">
                                    <strong>${product.name}</strong><br>
                                    <small style="color:#6b7280;">ID: ${product.id}</small>
                                </div>`;
                            });
                            $('#productSearchResults').html(html).addClass('active');
                        } else {
                            $('#productSearchResults').html('<div style="padding:15px;color:#6b7280;">No products found</div>').addClass('active');
                        }
                    }
                });
            }, 300);
        });
        
        // Select product from search results
        $(document).on('click', '.product-result', function() {
            const productId = $(this).data('id');
            const productName = $(this).find('strong').text();
            
            $('#productSearch').val(productName);
            $('#selectedProductId').val(productId);
            $('#productSearchResults').removeClass('active');
            
            // Reload page with product_id
            window.location.href = '?product_id=' + productId;
        });
        
        // Close search results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('.product-search').length) {
                $('#productSearchResults').removeClass('active');
            }
        });
        
        // Add new step
        $('#addStepBtn').on('click', function() {
            const stepHtml = `
            <div class="route-step">
                <div class="route-step-header">
                    <span class="route-step-number">${stepCounter + 1}</span>
                    <strong>Step ${stepCounter + 1}</strong>
                </div>
                <div class="route-step-content">
                    <div class="form-group">
                        <label class="form-label">Department</label>
                        <select name="routes[${stepCounter}][department_id]" class="form-select" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                            <option value="<?= esc_attr($dept->id) ?>"><?= esc_html($dept->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Time per Unit (min)</label>
                        <input type="number" name="routes[${stepCounter}][estimated_time]" class="form-control" min="1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Setup Time (min)</label>
                        <input type="number" name="routes[${stepCounter}][setup_time]" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <input type="text" name="routes[${stepCounter}][notes]" class="form-control" placeholder="Optional notes">
                    </div>
                    <div style="padding-top:28px;">
                        <button type="button" class="btn btn-danger btn-sm remove-step">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>`;
            
            $('#routeSteps').append(stepHtml);
            stepCounter++;
            $('#routeConfigSection').show();
            updateStepNumbers();
        });
        
        // Remove step
        $(document).on('click', '.remove-step', function() {
            $(this).closest('.route-step').remove();
            updateStepNumbers();
        });
        
        // Update step numbers
        function updateStepNumbers() {
            $('.route-step').each(function(index) {
                $(this).find('.route-step-number').text(index + 1);
                $(this).find('.route-step-header strong').text('Step ' + (index + 1));
            });
        }
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

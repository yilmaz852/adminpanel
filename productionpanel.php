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
 * Architecture: Follows adminpanel.php pattern
 * - Custom database tables for production tracking
 * - WordPress rewrite rules for clean URLs
 * - Single-file organization with template_redirect hooks
 * - Compatible with WooCommerce orders
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit();

/* =====================================================
 * 1. DATABASE TABLES - PRODUCTION TRACKING
 * ===================================================== */
// Check and create tables on init
add_action('init', 'production_panel_check_tables', 5);
function production_panel_check_tables() {
    // Check if tables exist
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
        INDEX idx_product (product_id)
    ) {$charset_collate};";
    dbDelta($sql4);
    
    // Default settings
    if (!get_option('production_panel_settings')) {
        add_option('production_panel_settings', [
            'daily_hours' => 8,
            'working_days' => ['1', '2', '3', '4', '5'],
            'cache_duration' => 3600,
            'notifications_enabled' => 0
        ]);
    }
}

/* =====================================================
 * 2. CUSTOM ORDER STATUSES FOR PRODUCTION
 * ===================================================== */
add_action('init', 'production_panel_register_statuses');
function production_panel_register_statuses() {
    if (!class_exists('WooCommerce')) {
        return;
    }
    
    register_post_status('wc-in-production', [
        'label' => 'In Production',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('In Production <span class="count">(%s)</span>', 'In Production <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-cutting', [
        'label' => 'Cutting',
        'public' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Cutting <span class="count">(%s)</span>', 'Cutting <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-sewing', [
        'label' => 'Sewing',
        'public' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Sewing <span class="count">(%s)</span>', 'Sewing <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-quality-check', [
        'label' => 'Quality Control',
        'public' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Quality Control <span class="count">(%s)</span>', 'Quality Control <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-packaging', [
        'label' => 'Packaging',
        'public' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Packaging <span class="count">(%s)</span>', 'Packaging <span class="count">(%s)</span>')
    ]);
    
    register_post_status('wc-ready-to-ship', [
        'label' => 'Ready to Ship',
        'public' => true,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Ready to Ship <span class="count">(%s)</span>', 'Ready to Ship <span class="count">(%s)</span>')
    ]);
}

// Add to WooCommerce order statuses
add_filter('wc_order_statuses', 'production_panel_add_statuses');
function production_panel_add_statuses($statuses) {
    $new_statuses = [];
    foreach ($statuses as $key => $status) {
        $new_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_statuses['wc-in-production'] = 'In Production';
            $new_statuses['wc-cutting'] = 'Cutting';
            $new_statuses['wc-sewing'] = 'Sewing';
            $new_statuses['wc-quality-check'] = 'Quality Control';
            $new_statuses['wc-packaging'] = 'Packaging';
            $new_statuses['wc-ready-to-ship'] = 'Ready to Ship';
        }
    }
    return $new_statuses;
}

// Log status changes
add_action('woocommerce_order_status_changed', 'production_panel_log_status_change', 10, 4);
function production_panel_log_status_change($order_id, $old_status, $new_status, $order) {
    global $wpdb;
    if ($old_status === $new_status) return;
    
    $table = $wpdb->prefix . 'production_status_history';
    $wpdb->insert($table, [
        'order_id' => $order_id,
        'status' => $new_status,
        'changed_at' => current_time('mysql', true),
        'changed_by' => get_current_user_id(),
        'notes' => sprintf('Changed from %s', $old_status)
    ]);
}

/* =====================================================
 * 3. REWRITE RULES FOR CLEAN URLS
 * ===================================================== */
add_action('init', 'production_panel_rewrite_rules');
function production_panel_rewrite_rules() {
    add_rewrite_rule('^production-panel/?$', 'index.php?production_panel=dashboard', 'top');
    add_rewrite_rule('^production-panel/schedule/?$', 'index.php?production_panel=schedule', 'top');
    add_rewrite_rule('^production-panel/calendar/?$', 'index.php?production_panel=calendar', 'top');
    add_rewrite_rule('^production-panel/departments/?$', 'index.php?production_panel=departments', 'top');
    add_rewrite_rule('^production-panel/department/add/?$', 'index.php?production_panel=department_add', 'top');
    add_rewrite_rule('^production-panel/department/edit/([0-9]+)/?$', 'index.php?production_panel=department_edit&dept_id=$matches[1]', 'top');
    add_rewrite_rule('^production-panel/department/delete/([0-9]+)/?$', 'index.php?production_panel=department_delete&dept_id=$matches[1]', 'top');
    add_rewrite_rule('^production-panel/analytics/?$', 'index.php?production_panel=analytics', 'top');
    add_rewrite_rule('^production-panel/reports/?$', 'index.php?production_panel=reports', 'top');
    add_rewrite_rule('^production-panel/settings/?$', 'index.php?production_panel=settings', 'top');
    add_rewrite_rule('^production-panel/export/?$', 'index.php?production_panel=export', 'top');
}

add_filter('query_vars', function($vars) {
    $vars[] = 'production_panel';
    $vars[] = 'dept_id';
    return $vars;
});

/* =====================================================
 * 4. TEMPLATE ROUTING
 * ===================================================== */
add_action('template_redirect', 'production_panel_route');
function production_panel_route() {
    $page = get_query_var('production_panel');
    if (!$page) return;
    
    // Admin-only access
    if (!current_user_can('manage_options')) {
        wp_die('Access denied. Admin only.');
    }
    
    switch ($page) {
        case 'dashboard':
            production_panel_dashboard();
            break;
        case 'schedule':
            production_panel_schedule();
            break;
        case 'calendar':
            production_panel_calendar();
            break;
        case 'departments':
            production_panel_departments();
            break;
        case 'department_add':
            production_panel_department_add();
            break;
        case 'department_edit':
            production_panel_department_edit();
            break;
        case 'department_delete':
            production_panel_department_delete();
            break;
        case 'analytics':
            production_panel_analytics();
            break;
        case 'reports':
            production_panel_reports();
            break;
        case 'settings':
            production_panel_settings();
            break;
        case 'export':
            production_panel_export();
            break;
    }
    exit;
}

/* =====================================================
 * 5. HELPER FUNCTIONS
 * ===================================================== */
function production_panel_header($title = 'Production Panel') {
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?php echo esc_html($title); ?> - <?php echo esc_html(get_bloginfo('name')); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <?php wp_head(); ?>
        <style>
            :root {
                --primary: #0f172a;
                --accent: #3b82f6;
                --success: #10b981;
                --warning: #f59e0b;
                --danger: #ef4444;
                --bg: #f8fafc;
                --white: #ffffff;
                --border: #e5e7eb;
                --text: #1e293b;
                --shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', sans-serif;
                background: var(--bg);
                color: var(--text);
                line-height: 1.6;
            }
            .container { max-width: 1400px; margin: 0 auto; padding: 20px; }
            .header {
                background: var(--white);
                border-bottom: 1px solid var(--border);
                padding: 15px 0;
                margin-bottom: 30px;
            }
            .header-content {
                max-width: 1400px;
                margin: 0 auto;
                padding: 0 20px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 24px;
                font-weight: 700;
                color: var(--primary);
            }
            .nav {
                display: flex;
                gap: 15px;
            }
            .nav a {
                color: var(--text);
                text-decoration: none;
                padding: 8px 16px;
                border-radius: 6px;
                transition: all 0.2s;
                font-weight: 500;
            }
            .nav a:hover, .nav a.active {
                background: var(--accent);
                color: var(--white);
            }
            .card {
                background: var(--white);
                border-radius: 8px;
                padding: 24px;
                box-shadow: var(--shadow);
                margin-bottom: 20px;
            }
            .card-title {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 16px;
                color: var(--primary);
            }
            .btn {
                display: inline-block;
                padding: 10px 20px;
                border-radius: 6px;
                font-weight: 500;
                text-decoration: none;
                border: none;
                cursor: pointer;
                transition: all 0.2s;
            }
            .btn-primary {
                background: var(--accent);
                color: var(--white);
            }
            .btn-primary:hover {
                background: #2563eb;
            }
            .btn-success {
                background: var(--success);
                color: var(--white);
            }
            .btn-danger {
                background: var(--danger);
                color: var(--white);
            }
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            .stat-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 24px;
                border-radius: 8px;
                box-shadow: var(--shadow);
            }
            .stat-value {
                font-size: 32px;
                font-weight: 700;
                margin: 10px 0;
            }
            .stat-label {
                font-size: 14px;
                opacity: 0.9;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
            }
            .table th,
            .table td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid var(--border);
            }
            .table th {
                background: #f9fafb;
                font-weight: 600;
            }
            .status-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 13px;
                font-weight: 500;
            }
            .status-scheduled { background: #dbeafe; color: #1e40af; }
            .status-in-progress { background: #fef3c7; color: #92400e; }
            .status-completed { background: #d1fae5; color: #065f46; }
            .form-group {
                margin-bottom: 20px;
            }
            .form-group label {
                display: block;
                margin-bottom: 8px;
                font-weight: 500;
            }
            .form-control {
                width: 100%;
                padding: 10px;
                border: 1px solid var(--border);
                border-radius: 6px;
                font-size: 14px;
            }
            .alert {
                padding: 12px 16px;
                border-radius: 6px;
                margin-bottom: 20px;
            }
            .alert-success {
                background: #d1fae5;
                color: #065f46;
            }
            .alert-error {
                background: #fee2e2;
                color: #991b1b;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <div class="header-content">
                <h1><i class="fas fa-industry"></i> <?php echo esc_html($title); ?></h1>
                <nav class="nav">
                    <a href="/production-panel" class="<?php echo get_query_var('production_panel') === 'dashboard' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="/production-panel/schedule" class="<?php echo get_query_var('production_panel') === 'schedule' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> Schedule
                    </a>
                    <a href="/production-panel/calendar" class="<?php echo get_query_var('production_panel') === 'calendar' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar"></i> Calendar
                    </a>
                    <a href="/production-panel/departments" class="<?php echo get_query_var('production_panel') === 'departments' ? 'active' : ''; ?>">
                        <i class="fas fa-building"></i> Departments
                    </a>
                    <a href="/production-panel/analytics" class="<?php echo get_query_var('production_panel') === 'analytics' ? 'active' : ''; ?>">
                        <i class="fas fa-chart-bar"></i> Analytics
                    </a>
                    <a href="/production-panel/settings" class="<?php echo get_query_var('production_panel') === 'settings' ? 'active' : ''; ?>">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                    <a href="<?php echo admin_url(); ?>">
                        <i class="fas fa-arrow-left"></i> Back to Admin
                    </a>
                </nav>
            </div>
        </div>
    <?php
}

function production_panel_footer() {
    ?>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}

/* =====================================================
 * 6. DASHBOARD PAGE
 * ===================================================== */
function production_panel_dashboard() {
    global $wpdb;
    production_panel_header('Dashboard');
    
    // Get statistics
    $table = $wpdb->prefix . 'production_schedule';
    $total_scheduled = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'scheduled'");
    $in_progress = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'in_progress'");
    $completed = $wpdb->get_var("SELECT COUNT(*) FROM {$table} WHERE status = 'completed'");
    
    $dept_table = $wpdb->prefix . 'production_departments';
    $total_departments = $wpdb->get_var("SELECT COUNT(*) FROM {$dept_table} WHERE is_active = 1");
    
    ?>
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-label">Scheduled Orders</div>
                <div class="stat-value"><?php echo esc_html($total_scheduled ?: 0); ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-label">In Progress</div>
                <div class="stat-value"><?php echo esc_html($in_progress ?: 0); ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-label">Completed</div>
                <div class="stat-value"><?php echo esc_html($completed ?: 0); ?></div>
            </div>
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-label">Active Departments</div>
                <div class="stat-value"><?php echo esc_html($total_departments ?: 0); ?></div>
            </div>
        </div>
        
        <div class="card">
            <h2 class="card-title">Recent Production Activities</h2>
            <?php
            $history_table = $wpdb->prefix . 'production_status_history';
            $recent = $wpdb->get_results("
                SELECT * FROM {$history_table} 
                ORDER BY changed_at DESC 
                LIMIT 10
            ");
            
            if ($recent) {
                echo '<table class="table">';
                echo '<thead><tr>';
                echo '<th>Order ID</th><th>Status</th><th>Changed At</th><th>Notes</th>';
                echo '</tr></thead><tbody>';
                
                foreach ($recent as $row) {
                    echo '<tr>';
                    echo '<td>#' . esc_html($row->order_id) . '</td>';
                    echo '<td><span class="status-badge status-in-progress">' . esc_html($row->status) . '</span></td>';
                    echo '<td>' . esc_html($row->changed_at) . '</td>';
                    echo '<td>' . esc_html($row->notes) . '</td>';
                    echo '</tr>';
                }
                
                echo '</tbody></table>';
            } else {
                echo '<p>No recent activities.</p>';
            }
            ?>
        </div>
    </div>
    <?php
    production_panel_footer();
}

/* =====================================================
 * 7. SCHEDULE PAGE
 * ===================================================== */
function production_panel_schedule() {
    global $wpdb;
    production_panel_header('Production Schedule');
    
    $table = $wpdb->prefix . 'production_schedule';
    $schedules = $wpdb->get_results("
        SELECT s.*, d.name as department_name 
        FROM {$table} s
        LEFT JOIN {$wpdb->prefix}production_departments d ON s.department_id = d.id
        ORDER BY s.scheduled_start DESC
        LIMIT 50
    ");
    
    ?>
    <div class="container">
        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h2 class="card-title" style="margin: 0;">Production Schedule</h2>
                <a href="#" class="btn btn-primary"><i class="fas fa-plus"></i> Add Schedule</a>
            </div>
            
            <?php if ($schedules): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Department</th>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Scheduled Start</th>
                        <th>Scheduled End</th>
                        <th>Status</th>
                        <th>Priority</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td>#<?php echo esc_html($schedule->order_id); ?></td>
                        <td><?php echo esc_html($schedule->department_name); ?></td>
                        <td><?php echo esc_html($schedule->product_id); ?></td>
                        <td><?php echo esc_html($schedule->quantity); ?></td>
                        <td><?php echo esc_html($schedule->scheduled_start); ?></td>
                        <td><?php echo esc_html($schedule->scheduled_end); ?></td>
                        <td><span class="status-badge status-<?php echo esc_attr($schedule->status); ?>"><?php echo esc_html($schedule->status); ?></span></td>
                        <td><?php echo esc_html($schedule->priority); ?></td>
                        <td>
                            <a href="#" class="btn btn-sm">Edit</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No scheduled production found.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    production_panel_footer();
}

/* =====================================================
 * 8. DEPARTMENTS PAGE
 * ===================================================== */
function production_panel_departments() {
    global $wpdb;
    production_panel_header('Departments');
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        check_admin_referer('production_add_department');
        
        if (isset($_POST['department_name'])) {
        
            $table = $wpdb->prefix . 'production_departments';
            $wpdb->insert($table, [
                'name' => sanitize_text_field($_POST['department_name']),
                'slug' => sanitize_title($_POST['department_name']),
                'capacity' => absint($_POST['capacity']),
                'workers' => absint($_POST['workers']),
                'color' => sanitize_hex_color($_POST['color']),
                'is_active' => 1
            ]);
            
            echo '<div class="container"><div class="alert alert-success">Department added successfully!</div></div>';
        }
    }
    
    $table = $wpdb->prefix . 'production_departments';
    $departments = $wpdb->get_results("SELECT * FROM {$table} ORDER BY display_order ASC, name ASC");
    
    ?>
    <div class="container">
        <div class="card">
            <h2 class="card-title">Add New Department</h2>
            <form method="post">
                <?php wp_nonce_field('production_add_department'); ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div class="form-group">
                        <label>Department Name</label>
                        <input type="text" name="department_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Capacity</label>
                        <input type="number" name="capacity" class="form-control" value="10" min="1">
                    </div>
                    <div class="form-group">
                        <label>Workers</label>
                        <input type="number" name="workers" class="form-control" value="1" min="1">
                    </div>
                    <div class="form-group">
                        <label>Color</label>
                        <input type="color" name="color" class="form-control" value="#3498db">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Add Department</button>
            </form>
        </div>
        
        <div class="card">
            <h2 class="card-title">All Departments</h2>
            <?php if ($departments): ?>
            <table class="table">
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
                        <td><?php echo esc_html($dept->name); ?></td>
                        <td><?php echo esc_html($dept->capacity); ?></td>
                        <td><?php echo esc_html($dept->workers); ?></td>
                        <td><span style="display:inline-block;width:30px;height:20px;background:<?php echo esc_attr($dept->color); ?>;border-radius:4px;"></span></td>
                        <td><?php echo $dept->is_active ? '<span class="status-badge status-completed">Active</span>' : '<span class="status-badge">Inactive</span>'; ?></td>
                        <td>
                            <a href="/production-panel/department/edit/<?php echo esc_attr($dept->id); ?>" class="btn btn-sm">Edit</a>
                            <a href="/production-panel/department/delete/<?php echo esc_attr($dept->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this department?')">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p>No departments found.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php
    production_panel_footer();
}

/* =====================================================
 * 9. CALENDAR PAGE
 * ===================================================== */
function production_panel_calendar() {
    production_panel_header('Production Calendar');
    ?>
    <div class="container">
        <div class="card">
            <h2 class="card-title">Production Calendar</h2>
            <div id="calendar-container">
                <p>Calendar integration will be added here. This would typically use FullCalendar.js or a similar library to display scheduled production tasks.</p>
            </div>
        </div>
    </div>
    <?php
    production_panel_footer();
}

/* =====================================================
 * 10. ANALYTICS PAGE
 * ===================================================== */
function production_panel_analytics() {
    production_panel_header('Analytics');
    ?>
    <div class="container">
        <div class="card">
            <h2 class="card-title">Production Analytics</h2>
            <p>Analytics charts and reports will be displayed here.</p>
        </div>
    </div>
    <?php
    production_panel_footer();
}

/* =====================================================
 * 11. SETTINGS PAGE
 * ===================================================== */
function production_panel_settings() {
    production_panel_header('Settings');
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['_wpnonce'])) {
        check_admin_referer('production_save_settings');
        
        $settings = [
            'daily_hours' => absint($_POST['daily_hours']),
            'working_days' => isset($_POST['working_days']) ? array_map('sanitize_text_field', $_POST['working_days']) : [],
            'cache_duration' => absint($_POST['cache_duration']),
            'notifications_enabled' => isset($_POST['notifications_enabled']) ? 1 : 0
        ];
        
        update_option('production_panel_settings', $settings);
        echo '<div class="container"><div class="alert alert-success">Settings saved successfully!</div></div>';
    }
    
    $settings = get_option('production_panel_settings', [
        'daily_hours' => 8,
        'working_days' => ['1', '2', '3', '4', '5'],
        'cache_duration' => 3600,
        'notifications_enabled' => 0
    ]);
    
    ?>
    <div class="container">
        <div class="card">
            <h2 class="card-title">Production Panel Settings</h2>
            <form method="post">
                <?php wp_nonce_field('production_save_settings'); ?>
                
                <div class="form-group">
                    <label>Daily Working Hours</label>
                    <input type="number" name="daily_hours" class="form-control" value="<?php echo esc_attr($settings['daily_hours']); ?>" min="1" max="24">
                </div>
                
                <div class="form-group">
                    <label>Working Days</label>
                    <div>
                        <?php
                        $days = ['1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday', '6' => 'Saturday', '0' => 'Sunday'];
                        foreach ($days as $value => $label) {
                            $checked = in_array($value, $settings['working_days']) ? 'checked' : '';
                            echo '<label style="display:inline-block;margin-right:15px;">';
                            echo '<input type="checkbox" name="working_days[]" value="' . esc_attr($value) . '" ' . $checked . '> ';
                            echo esc_html($label);
                            echo '</label>';
                        }
                        ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Cache Duration (seconds)</label>
                    <input type="number" name="cache_duration" class="form-control" value="<?php echo esc_attr($settings['cache_duration']); ?>" min="60">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="notifications_enabled" <?php checked($settings['notifications_enabled'], 1); ?>>
                        Enable Notifications
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
    <?php
    production_panel_footer();
}

/* =====================================================
 * 12. EXPORT & REPORTS
 * ===================================================== */
function production_panel_export() {
    if (!current_user_can('manage_options')) {
        wp_die('Access denied');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'production_schedule';
    $data = $wpdb->get_results("SELECT * FROM {$table} ORDER BY scheduled_start DESC", ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="production-schedule-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
    }
    
    fclose($output);
    exit;
}

function production_panel_reports() {
    production_panel_header('Reports');
    ?>
    <div class="container">
        <div class="card">
            <h2 class="card-title">Production Reports</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <div>
                    <h3>Export Options</h3>
                    <a href="/production-panel/export" class="btn btn-primary" style="display: block; margin: 10px 0;">
                        <i class="fas fa-download"></i> Export to CSV
                    </a>
                </div>
                <div>
                    <h3>Quick Stats</h3>
                    <p>Reports and analytics will be generated here.</p>
                </div>
            </div>
        </div>
    </div>
    <?php
    production_panel_footer();
}

function production_panel_department_add() {
    wp_redirect('/production-panel/departments');
    exit;
}

function production_panel_department_edit() {
    $dept_id = get_query_var('dept_id');
    // Implementation for editing department
    wp_redirect('/production-panel/departments');
    exit;
}

function production_panel_department_delete() {
    global $wpdb;
    $dept_id = get_query_var('dept_id');
    
    if ($dept_id && current_user_can('manage_options')) {
        $table = $wpdb->prefix . 'production_departments';
        $wpdb->delete($table, ['id' => absint($dept_id)]);
    }
    
    wp_redirect('/production-panel/departments');
    exit;
}

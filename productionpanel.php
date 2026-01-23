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
        INDEX idx_product (product_id)
    ) {$charset_collate};";
    dbDelta($sql4);
    
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
    }
}

/* =====================================================
 * 3. ROUTING - ADMIN PANEL INTEGRATION
 * URL routing is handled by adminpanel.php (lines 85-91):
 *   /b2b-panel/production → b2b_adm_page=production
 *   /b2b-panel/production/schedule → b2b_adm_page=production_schedule
 *   /b2b-panel/production/departments → b2b_adm_page=production_departments
 *   /b2b-panel/production/calendar → b2b_adm_page=production_calendar
 *   /b2b-panel/production/analytics → b2b_adm_page=production_analytics
 *   /b2b-panel/production/settings → b2b_adm_page=production_settings
 * 
 * This file provides template_redirect hooks for each page.
 * ===================================================== */

/* =====================================================
 * 4. DASHBOARD PAGE (Production)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production') return;
    b2b_adm_guard();
    production_dashboard_page();
});

/* =====================================================
 * 5. SCHEDULE PAGE (Production Schedule)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_schedule') return;
    b2b_adm_guard();
    production_schedule_page();
});

/* =====================================================
 * 6. DEPARTMENTS PAGE (Production Departments)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_departments') return;
    b2b_adm_guard();
    production_departments_page();
});

/* =====================================================
 * 7. CALENDAR PAGE (Production Calendar)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_calendar') return;
    b2b_adm_guard();
    production_calendar_page();
});

/* =====================================================
 * 8. ANALYTICS PAGE (Production Analytics)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_analytics') return;
    b2b_adm_guard();
    production_analytics_page();
});

/* =====================================================
 * 9. SETTINGS PAGE (Production Settings)
 * ===================================================== */
add_action('template_redirect', function() {
    if (get_query_var('b2b_adm_page') !== 'production_settings') return;
    b2b_adm_guard();
    production_settings_page();
});

/* =====================================================
 * 7. NAVIGATION HELPER
 * ===================================================== */
function production_page_nav($active_page = 'dashboard') {
    $pages = [
        'dashboard' => ['icon' => 'fa-chart-line', 'label' => 'Dashboard', 'url' => home_url('/b2b-panel/production')],
        'schedule' => ['icon' => 'fa-calendar-days', 'label' => 'Schedule', 'url' => home_url('/b2b-panel/production/schedule')],
        'departments' => ['icon' => 'fa-building', 'label' => 'Departments', 'url' => home_url('/b2b-panel/production/departments')],
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
 * 8. DASHBOARD PAGE
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
    
    <?php production_page_nav('schedule'); ?>
    
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
                    <label class="form-label">Order ID</label>
                    <input type="number" name="order_id" class="form-control" required>
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
                <div class="form-group">
                    <label class="form-label">Product ID</label>
                    <input type="number" name="product_id" class="form-control" required>
                </div>
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
                    <td><strong>#<?= esc_html($s->order_id) ?></strong></td>
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
    
    <?php production_page_nav('departments'); ?>
    
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
 * 10. CALENDAR PAGE
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
        <h3><i class="fa-solid fa-calendar"></i> Production Timeline</h3>
        <p style="color:var(--text-muted);margin-bottom:20px">
            Visual calendar view for production scheduling. Integration with FullCalendar.js can be added here.
        </p>
        
        <div style="background:var(--bg);padding:60px;text-align:center;border-radius:8px;">
            <i class="fa-solid fa-calendar" style="font-size:64px;color:var(--text-muted);margin-bottom:20px;"></i>
            <h4 style="color:var(--text-muted)">Calendar View Placeholder</h4>
            <p style="color:var(--text-muted);margin-top:10px">
                This section will display a visual calendar with drag-and-drop production scheduling.
            </p>
        </div>
    </div>
    
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
    
    <?php production_page_nav('analytics'); ?>
    
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
    
    <?php production_page_nav('settings'); ?>
    
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

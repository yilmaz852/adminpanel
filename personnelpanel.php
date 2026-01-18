<?php
/**
 * =====================================================
 * B2B PERSONNEL MANAGEMENT MODULE
 * Admin-only access for employee management
 * =====================================================
 * 
 * Features:
 * - Personnel CRUD operations
 * - Department management
 * - Employee details (position, email, phone, salary, start date)
 * - Table list view with search and filters
 * - Admin-only access control
 * 
 * Architecture: Follows adminpanel.php pattern
 * - WordPress Custom Post Type (b2b_personel)
 * - Post Meta for employee details
 * - Taxonomy for departments
 * - Template redirect for custom URLs
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/* =====================================================
 * 1. REGISTER CUSTOM POST TYPE & TAXONOMY
 * ===================================================== */
add_action('init', 'b2b_register_personnel_post_type');
function b2b_register_personnel_post_type() {
    register_post_type('b2b_personel', [
        'labels' => [
            'name'               => 'Personnel',
            'singular_name'      => 'Personnel',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Personnel',
            'edit_item'          => 'Edit Personnel',
            'view_item'          => 'View Personnel',
            'search_items'       => 'Search Personnel',
            'not_found'          => 'No personnel found',
            'not_found_in_trash' => 'No personnel in trash'
        ],
        'public'              => false,
        'show_ui'             => false, // We use custom panel
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => ['title'],
        'has_archive'         => false,
        'rewrite'             => false,
    ]);

    register_taxonomy('b2b_departman', 'b2b_personel', [
        'label'             => 'Departments',
        'hierarchical'      => true,
        'show_ui'           => false, // We use custom panel
        'show_admin_column' => false,
        'query_var'         => true,
        'rewrite'           => false,
    ]);
}

/* =====================================================
 * 2. REWRITE RULES FOR CLEAN URLS
 * ===================================================== */
add_action('init', 'b2b_personnel_rewrite_rules');
function b2b_personnel_rewrite_rules() {
    add_rewrite_rule('^personnel-panel/?$', 'index.php?personnel_panel=list', 'top');
    add_rewrite_rule('^personnel-panel/add/?$', 'index.php?personnel_panel=add', 'top');
    add_rewrite_rule('^personnel-panel/edit/([0-9]+)/?$', 'index.php?personnel_panel=edit&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/delete/([0-9]+)/?$', 'index.php?personnel_panel=delete&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/view/([0-9]+)/?$', 'index.php?personnel_panel=view&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/departments/?$', 'index.php?personnel_panel=departments', 'top');
    add_rewrite_rule('^personnel-panel/department-delete/([0-9]+)/?$', 'index.php?personnel_panel=department_delete&department_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/export/?$', 'index.php?personnel_panel=export', 'top');
    add_rewrite_rule('^personnel-panel/bulk-delete/?$', 'index.php?personnel_panel=bulk_delete', 'top');
    add_rewrite_rule('^personnel-panel/attendance/?$', 'index.php?personnel_panel=attendance', 'top');
    add_rewrite_rule('^personnel-panel/clock-in/([0-9]+)/?$', 'index.php?personnel_panel=clock_in&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/clock-out/([0-9]+)/?$', 'index.php?personnel_panel=clock_out&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/activity/?$', 'index.php?personnel_panel=activity', 'top');
    add_rewrite_rule('^personnel-panel/add-note/([0-9]+)/?$', 'index.php?personnel_panel=add_note&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/delete-note/([0-9]+)/?$', 'index.php?personnel_panel=delete_note&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/upload-document/([0-9]+)/?$', 'index.php?personnel_panel=upload_document&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/delete-document/([0-9]+)/?$', 'index.php?personnel_panel=delete_document&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/clock-in-form/([0-9]+)/?$', 'index.php?personnel_panel=clock_in_form&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/clock-out-form/([0-9]+)/?$', 'index.php?personnel_panel=clock_out_form&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/process-clock-in/?$', 'index.php?personnel_panel=process_clock_in', 'top');
    add_rewrite_rule('^personnel-panel/process-clock-out/?$', 'index.php?personnel_panel=process_clock_out', 'top');
    add_rewrite_rule('^personnel-panel/edit-attendance/([0-9]+)/([0-9]+)/?$', 'index.php?personnel_panel=edit_attendance&personnel_id=$matches[1]&attendance_index=$matches[2]', 'top');
    add_rewrite_rule('^personnel-panel/update-attendance/?$', 'index.php?personnel_panel=update_attendance', 'top');
    add_rewrite_rule('^personnel-panel/delete-attendance/([0-9]+)/([0-9]+)/?$', 'index.php?personnel_panel=delete_attendance&personnel_id=$matches[1]&attendance_index=$matches[2]', 'top');
    add_rewrite_rule('^personnel-panel/reports/?$', 'index.php?personnel_panel=reports', 'top');
    add_rewrite_rule('^personnel-panel/upload-photo/([0-9]+)/?$', 'index.php?personnel_panel=upload_photo&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/process-photo-upload/?$', 'index.php?personnel_panel=process_photo_upload', 'top');
    add_rewrite_rule('^personnel-panel/delete-photo/([0-9]+)/?$', 'index.php?personnel_panel=delete_photo&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/print-view/([0-9]+)/?$', 'index.php?personnel_panel=print_view&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/enhanced-audit/([0-9]+)/?$', 'index.php?personnel_panel=enhanced_audit&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/metrics/([0-9]+)/?$', 'index.php?personnel_panel=metrics&personnel_id=$matches[1]', 'top');
    // PTO Management Routes
    add_rewrite_rule('^personnel-panel/request-leave/([0-9]+)/?$', 'index.php?personnel_panel=request_leave&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/process-leave-request/?$', 'index.php?personnel_panel=process_leave_request', 'top');
    add_rewrite_rule('^personnel-panel/leave-approvals/?$', 'index.php?personnel_panel=leave_approvals', 'top');
    add_rewrite_rule('^personnel-panel/approve-leave/([a-z0-9_]+)/?$', 'index.php?personnel_panel=approve_leave&leave_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/deny-leave/([a-z0-9_]+)/?$', 'index.php?personnel_panel=deny_leave&leave_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/leave-calendar/?$', 'index.php?personnel_panel=leave_calendar', 'top');
    add_rewrite_rule('^personnel-panel/leave-history/([0-9]+)/?$', 'index.php?personnel_panel=leave_history&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/leave-accounting-export/?$', 'index.php?personnel_panel=leave_accounting_export', 'top');
    // Payroll Payment Routes
    add_rewrite_rule('^personnel-panel/payroll-payments/?$', 'index.php?personnel_panel=payroll_payments', 'top');
    add_rewrite_rule('^personnel-panel/add-payment/([0-9]+)/?$', 'index.php?personnel_panel=add_payment&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/edit-payment/([^/]+)/?$', 'index.php?personnel_panel=edit_payment&payment_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/delete-payment/([^/]+)/?$', 'index.php?personnel_panel=delete_payment&payment_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/process-payment/?$', 'index.php?personnel_panel=process_payment', 'top');
    add_rewrite_rule('^personnel-panel/payment-history/([0-9]+)/?$', 'index.php?personnel_panel=payment_history&personnel_id=$matches[1]', 'top');
    add_rewrite_rule('^personnel-panel/payroll-accounting-export/?$', 'index.php?personnel_panel=payroll_accounting_export', 'top');
}

add_filter('query_vars', 'b2b_personnel_query_vars');
function b2b_personnel_query_vars($vars) {
    $vars[] = 'personnel_panel';
    $vars[] = 'personnel_id';
    $vars[] = 'department_id';
    $vars[] = 'attendance_index';
    $vars[] = 'leave_id';
    $vars[] = 'payment_id';
    return $vars;
}

/* =====================================================
 * 3. TEMPLATE REDIRECT - ROUTE HANDLER
 * ===================================================== */
add_action('template_redirect', 'b2b_personnel_template_redirect');
function b2b_personnel_template_redirect() {
    $panel = get_query_var('personnel_panel');
    
    if (!$panel) return;

    // Admin-only access control
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_redirect(home_url('/b2b-panel'));
        exit;
    }

    // Route to appropriate handler
    switch ($panel) {
        case 'list':
            b2b_personnel_list_page();
            break;
        case 'add':
            b2b_personnel_add_page();
            break;
        case 'edit':
            b2b_personnel_edit_page();
            break;
        case 'delete':
            b2b_personnel_delete_action();
            break;
        case 'departments':
            b2b_personnel_departments_page();
            break;
        case 'department_delete':
            b2b_personnel_department_delete();
            break;
        case 'export':
            b2b_personnel_export_csv();
            break;
        case 'bulk_delete':
            b2b_personnel_bulk_delete();
            break;
        case 'view':
            b2b_personnel_view_page();
            break;
        case 'attendance':
            b2b_personnel_attendance_page();
            break;
        case 'clock_in':
            b2b_personnel_clock_in();
            break;
        case 'clock_out':
            b2b_personnel_clock_out();
            break;
        case 'activity':
            b2b_personnel_activity_page();
            break;
        case 'add_note':
            b2b_personnel_add_note();
            break;
        case 'delete_note':
            b2b_personnel_delete_note();
            break;
        case 'upload_document':
            b2b_personnel_upload_document();
            break;
        case 'delete_document':
            b2b_personnel_delete_document();
            break;
        case 'clock_in_form':
            b2b_personnel_clock_in_form();
            break;
        case 'clock_out_form':
            b2b_personnel_clock_out_form();
            break;
        case 'process_clock_in':
            b2b_personnel_process_clock_in();
            break;
        case 'process_clock_out':
            b2b_personnel_process_clock_out();
            break;
        case 'edit_attendance':
            b2b_personnel_edit_attendance_form();
            break;
        case 'update_attendance':
            b2b_personnel_update_attendance();
            break;
        case 'delete_attendance':
            b2b_personnel_delete_attendance();
            break;
        case 'reports':
            b2b_personnel_reports_page();
            break;
        case 'upload_photo':
            b2b_personnel_upload_photo();
            break;
        case 'process_photo_upload':
            b2b_personnel_process_photo_upload();
            break;
        case 'delete_photo':
            b2b_personnel_delete_photo();
            break;
        case 'print_view':
            b2b_personnel_print_view();
            break;
        case 'enhanced_audit':
            b2b_personnel_enhanced_audit();
            break;
        case 'metrics':
            b2b_personnel_metrics();
            break;
        case 'request_leave':
            b2b_personnel_request_leave();
            break;
        case 'process_leave_request':
            b2b_personnel_process_leave_request();
            break;
        case 'leave_approvals':
            b2b_personnel_leave_approvals();
            break;
        case 'approve_leave':
            b2b_personnel_approve_leave();
            break;
        case 'deny_leave':
            b2b_personnel_deny_leave();
            break;
        case 'leave_calendar':
            b2b_personnel_leave_calendar();
            break;
        case 'leave_history':
            b2b_personnel_leave_history();
            break;
        case 'leave_accounting_export':
            b2b_personnel_leave_accounting_export();
            break;
        case 'payroll_payments':
            b2b_personnel_payroll_payments();
            break;
        case 'add_payment':
            $personnel_id = get_query_var('personnel_id');
            b2b_personnel_add_payment($personnel_id);
            break;
        case 'edit_payment':
            $payment_id = get_query_var('payment_id');
            b2b_personnel_edit_payment($payment_id);
            break;
        case 'delete_payment':
            $payment_id = get_query_var('payment_id');
            b2b_personnel_delete_payment($payment_id);
            break;
        case 'process_payment':
            b2b_personnel_process_payment();
            break;
        case 'payment_history':
            $personnel_id = get_query_var('personnel_id');
            b2b_personnel_payment_history($personnel_id);
            break;
        case 'payroll_accounting_export':
            b2b_personnel_payroll_accounting_export();
            break;
        default:
            wp_redirect(home_url('/personnel-panel'));
            exit;
    }
    exit;
}

/* =====================================================
 * 4. PERSONNEL LIST PAGE (WITH ENHANCED FEATURES)
 * ===================================================== */
function b2b_personnel_list_page() {
    // Handle search and filters
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $department = isset($_GET['department']) ? sanitize_text_field($_GET['department']) : '';
    $paged = max(1, get_query_var('paged', 1));
    $per_page = 20;
    
    // Query personnel
    $args = [
        'post_type'      => 'b2b_personel',
        'posts_per_page' => $per_page,
        'paged'          => $paged,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ];
    
    if ($search) {
        $args['s'] = $search;
    }
    
    if ($department) {
        $args['tax_query'] = [[
            'taxonomy' => 'b2b_departman',
            'field'    => 'slug',
            'terms'    => $department,
        ]];
    }
    
    $personnel_query = new WP_Query($args);
    
    // Get departments for filter
    $departments = get_terms([
        'taxonomy'   => 'b2b_departman',
        'hide_empty' => false,
    ]);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Personel Yönetimi - Admin Panel</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            
            /* Header */
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            
            /* Container */
            .container {
                max-width: 1400px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            
            /* Toolbar */
            .toolbar {
                background: white;
                padding: 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
                flex-wrap: wrap;
                gap: 1rem;
            }
            .search-filters {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                flex: 1;
            }
            .search-box, .filter-select {
                padding: 0.5rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .search-box { min-width: 250px; }
            .add-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                background: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 500;
                font-size: 0.875rem;
            }
            .add-btn:hover { background: #2563eb; }
            
            /* Table */
            .table-container {
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                background: #f9fafb;
                padding: 0.75rem 1rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.875rem;
                color: #374151;
                border-bottom: 1px solid #e5e7eb;
            }
            td {
                padding: 1rem;
                border-bottom: 1px solid #e5e7eb;
                font-size: 0.875rem;
            }
            tr:last-child td { border-bottom: none; }
            tr:hover { background: #f9fafb; }
            
            /* Actions */
            .actions {
                display: flex;
                gap: 0.5rem;
            }
            .btn {
                padding: 0.375rem 0.75rem;
                border-radius: 4px;
                text-decoration: none;
                font-size: 0.75rem;
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
            }
            .btn-edit {
                background: #3b82f6;
                color: white;
            }
            .btn-edit:hover { background: #2563eb; }
            .btn-delete {
                background: #ef4444;
                color: white;
            }
            .btn-delete:hover { background: #dc2626; }
            
            /* Empty state */
            .empty-state {
                text-align: center;
                padding: 3rem;
                color: #6b7280;
            }
            .empty-state i {
                font-size: 3rem;
                margin-bottom: 1rem;
                opacity: 0.5;
            }
            
            /* Badge */
            .badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                background: #dbeafe;
                color: #1e40af;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 500;
            }
            
            /* Table Responsive Wrapper */
            .table-responsive {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            /* Mobile responsive */
            @media (max-width: 768px) {
                .header { flex-direction: column; gap: 1rem; }
                .toolbar { flex-direction: column; align-items: stretch; }
                .search-filters { flex-direction: column; }
                .search-box { width: 100%; }
                table { font-size: 0.75rem; }
                th, td { padding: 0.5rem; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-users"></i> Personnel Management</h1>
            <a href="<?= home_url('/b2b-panel') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Admin Panel
            </a>
        </div>
        
        <div class="container">
            <div class="toolbar">
                <form method="GET" class="search-filters">
                    <input type="text" name="s" class="search-box" placeholder="Search personnel..." value="<?= esc_attr($search) ?>">
                    <select name="department" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Departments</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= esc_attr($dept->slug) ?>" <?= selected($department, $dept->slug, false) ?>>
                                <?= esc_html($dept->name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-edit"><i class="fas fa-search"></i> Search</button>
                </form>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <a href="<?= home_url('/personnel-panel/attendance') ?>" class="add-btn" style="background: #f59e0b;">
                        <i class="fas fa-clock"></i> Attendance
                    </a>
                    <a href="<?= home_url('/personnel-panel/leave-calendar') ?>" class="add-btn" style="background: #0ea5e9;">
                        <i class="fas fa-calendar-alt"></i> Leave Calendar
                    </a>
                    <a href="<?= home_url('/personnel-panel/leave-approvals') ?>" class="add-btn" style="background: #14b8a6;">
                        <i class="fas fa-check-circle"></i> Leave Approvals
                    </a>
                    <a href="<?= home_url('/personnel-panel/payroll-payments') ?>" class="add-btn" style="background: #10b981;">
                        <i class="fas fa-money-check-alt"></i> Payroll Payments
                    </a>
                    <a href="<?= home_url('/personnel-panel/activity') ?>" class="add-btn" style="background: #8b5cf6;">
                        <i class="fas fa-history"></i> Activity Log
                    </a>
                    <a href="<?= home_url('/personnel-panel/export') ?>" class="add-btn" style="background: #10b981;">
                        <i class="fas fa-file-csv"></i> Export CSV
                    </a>
                    <a href="<?= home_url('/personnel-panel/departments') ?>" class="add-btn" style="background: #6366f1;">
                        <i class="fas fa-building"></i> Departments
                    </a>
                    <a href="<?= home_url('/personnel-panel/add') ?>" class="add-btn">
                        <i class="fas fa-plus"></i> Add New Personnel
                    </a>
                </div>
            </div>
            
            <!-- Bulk Actions Bar -->
            <div id="bulkActionsBar" style="display:none; background:white; padding:1rem 1.5rem; border-radius:8px; margin-bottom:1rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <form method="POST" action="<?= home_url('/personnel-panel/bulk-delete') ?>" onsubmit="return confirm('Are you sure you want to delete selected personnel?');">
                    <div style="display: flex; align-items: center; gap:1rem; flex-wrap:wrap;">
                        <span style="font-weight:600; color:#374151;"><span id="selectedCount">0</span> selected</span>
                        <button type="submit" class="btn btn-delete" style="padding:0.5rem 1rem;">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        <button type="button" class="btn" style="background:#6b7280; color:white; padding:0.5rem 1rem;" onclick="clearSelection()">
                            <i class="fas fa-times"></i> Clear Selection
                        </button>
                    </div>
                    <input type="hidden" name="selected_ids" id="selectedIdsInput">
                </form>
            </div>
            
            <div class="table-responsive">
            <div class="table-container">
                <?php if ($personnel_query->have_posts()): ?>
                    <form id="personnelTableForm">
                    <table>
                        <thead>
                            <tr>
                                <th style="width:40px;">
                                    <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                                </th>
                                <th>Full Name</th>
                                <th>Department</th>
                                <th>Position</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Salary</th>
                                <th>Start Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($personnel_query->have_posts()): $personnel_query->the_post(); ?>
                                <?php
                                $id = get_the_ID();
                                $gorev = get_post_meta($id, '_gorev', true);
                                $eposta = get_post_meta($id, '_eposta', true);
                                $telefon = get_post_meta($id, '_telefon', true);
                                $maas = get_post_meta($id, '_maas', true);
                                $baslangic = get_post_meta($id, '_baslangic_tarihi', true);
                                $depts = get_the_terms($id, 'b2b_departman');
                                $dept_name = $depts && !is_wp_error($depts) ? $depts[0]->name : '-';
                                
                                // Check today's attendance status
                                $attendance = get_post_meta($id, '_attendance', true) ?: [];
                                $today = date('Y-m-d');
                                $clocked_in = false;
                                foreach (array_reverse($attendance) as $record) {
                                    if (strpos($record['date'], $today) === 0) {
                                        $clocked_in = ($record['type'] === 'clock_in');
                                    }
                                }
                                $photo_url = get_post_meta($id, '_photo_url', true);
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="personnel-checkbox" value="<?= $id ?>" onchange="updateBulkActions()">
                                    </td>
                                    <td>
                                        <div style="display:flex;align-items:center;gap:10px;">
                                            <?php if ($photo_url): ?>
                                                <img src="<?= esc_url($photo_url) ?>" alt="<?= esc_attr(get_the_title()) ?>" 
                                                     style="width:40px;height:40px;border-radius:50%;object-fit:cover;">
                                            <?php else: ?>
                                                <div style="width:40px;height:40px;border-radius:50%;background:#6366f1;display:flex;align-items:center;justify-content:center;">
                                                    <i class="fas fa-user" style="font-size:16px;color:#fff;"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <a href="<?= home_url('/personnel-panel/view/' . $id) ?>" style="color:#3b82f6;font-weight:600;text-decoration:none;">
                                                    <?= get_the_title() ?>
                                                </a>
                                                <?php if ($clocked_in): ?>
                                                    <span style="background:#10b981;color:#fff;padding:2px 8px;border-radius:12px;font-size:11px;margin-left:8px;">
                                                        <i class="fas fa-circle" style="font-size:6px;"></i> Active
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge"><?= esc_html($dept_name) ?></span></td>
                                    <td><?= esc_html($gorev ?: '-') ?></td>
                                    <td><?= esc_html($eposta ?: '-') ?></td>
                                    <td><?= esc_html($telefon ?: '-') ?></td>
                                    <td><?= $maas ? '$' . number_format($maas, 0, '.', ',') : '-' ?></td>
                                    <td><?= $baslangic ? date('m/d/Y', strtotime($baslangic)) : '-' ?></td>
                                    <td>
                                        <div class="actions" style="display:flex;gap:5px;flex-wrap:wrap;">
                                            <a href="<?= home_url('/personnel-panel/view/' . $id) ?>" class="btn btn-edit" style="background:#6366f1;" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/edit/' . $id) ?>" class="btn btn-edit" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/clock-in-form/' . $id) ?>" class="btn btn-edit" style="background:#10b981;" title="Clock In">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/clock-out-form/' . $id) ?>" class="btn btn-delete" title="Clock Out">
                                                <i class="fas fa-sign-out-alt"></i>
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/request-leave/' . $id) ?>" class="btn btn-edit" style="background:#0ea5e9;" title="Request Leave">
                                                <i class="fas fa-calendar-check"></i>
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/add-payment/' . $id) ?>" class="btn btn-edit" style="background:#10b981;" title="Add Payment">
                                                <i class="fas fa-dollar-sign"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                    </form>
                    
                    <!-- Pagination -->
                    <?php if ($personnel_query->max_num_pages > 1): ?>
                    <div style="margin-top:20px; display:flex; justify-content:center; align-items:center; gap:10px; padding: 1rem;">
                        <span style="color:#6b7280; font-size:14px;">Page:</span>
                        <select onchange="window.location.href=this.value" style="padding:8px 16px; border:1px solid #d1d5db; border-radius:6px; background:white; cursor:pointer;">
                            <?php for ($i = 1; $i <= $personnel_query->max_num_pages; $i++): ?>
                                <?php
                                $page_url = add_query_arg(['paged' => $i, 's' => $search, 'department' => $department], home_url('/personnel-panel'));
                                ?>
                                <option value="<?= esc_url($page_url) ?>" <?= selected($paged, $i, false) ?>>
                                    Page <?= $i ?> of <?= $personnel_query->max_num_pages ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <span style="color:#6b7280; font-size:14px;">
                            (Showing <?= min($per_page, $personnel_query->found_posts) ?> of <?= $personnel_query->found_posts ?> personnel)
                        </span>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-users"></i>
                        <p>No personnel added yet.</p>
                        <p style="margin-top: 1rem;">
                            <a href="<?= home_url('/personnel-panel/add') ?>" class="add-btn">
                                <i class="fas fa-plus"></i> Add First Personnel
                            </a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
            </div>
            
            <script>
            // Bulk Actions Functions
            function toggleSelectAll(checkbox) {
                const checkboxes = document.querySelectorAll('.personnel-checkbox');
                checkboxes.forEach(cb => cb.checked = checkbox.checked);
                updateBulkActions();
            }
            
            function updateBulkActions() {
                const checkboxes = document.querySelectorAll('.personnel-checkbox:checked');
                const count = checkboxes.length;
                const bulkBar = document.getElementById('bulkActionsBar');
                const countSpan = document.getElementById('selectedCount');
                const idsInput = document.getElementById('selectedIdsInput');
                
                if (count > 0) {
                    bulkBar.style.display = 'block';
                    countSpan.textContent = count;
                    const ids = Array.from(checkboxes).map(cb => cb.value);
                    idsInput.value = ids.join(',');
                } else {
                    bulkBar.style.display = 'none';
                }
            }
            
            function clearSelection() {
                document.querySelectorAll('.personnel-checkbox').forEach(cb => cb.checked = false);
                document.getElementById('selectAll').checked = false;
                updateBulkActions();
            }
            </script>
        </div>
    </body>
    </html>
    <?php
    wp_reset_postdata();
}

/* =====================================================
 * 5. ADD/EDIT PERSONNEL PAGE
 * ===================================================== */
function b2b_personnel_add_page() {
    b2b_personnel_form_page(0);
}

function b2b_personnel_edit_page() {
    $id = get_query_var('personnel_id');
    if (!$id || get_post_type($id) !== 'b2b_personel') {
        wp_redirect('/personnel-panel');
        exit;
    }
    b2b_personnel_form_page($id);
}

function b2b_personnel_form_page($personnel_id = 0) {
    $is_edit = $personnel_id > 0;
    $success = false;
    $error = '';
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['personnel_submit'])) {
        $name = sanitize_text_field($_POST['name']);
        $gorev = sanitize_text_field($_POST['gorev']);
        $eposta = sanitize_email($_POST['eposta']);
        $telefon = sanitize_text_field($_POST['telefon']);
        $maas = floatval($_POST['maas']);
        $baslangic = sanitize_text_field($_POST['baslangic_tarihi']);
        $department = intval($_POST['department']);
        
        // Phase 1: Emergency Contact & Status
        $emergency_name = sanitize_text_field($_POST['emergency_contact_name'] ?? '');
        $emergency_rel = sanitize_text_field($_POST['emergency_contact_relationship'] ?? '');
        $emergency_phone = sanitize_text_field($_POST['emergency_contact_phone'] ?? '');
        $employment_status = sanitize_text_field($_POST['employment_status'] ?? 'active');
        $employee_id = sanitize_text_field($_POST['employee_id'] ?? '');
        
        // Phase 2: Pay Classification
        $pay_type = sanitize_text_field($_POST['pay_type'] ?? '');
        $pay_rate = floatval($_POST['pay_rate'] ?? 0);
        $flsa_status = sanitize_text_field($_POST['flsa_status'] ?? '');
        $reports_to = intval($_POST['reports_to'] ?? 0);
        $termination_date = sanitize_text_field($_POST['termination_date'] ?? '');
        $rehire_date = sanitize_text_field($_POST['rehire_date'] ?? '');
        
        // Phase 3: Time Tracking
        $meal_break = isset($_POST['meal_break']) ? 1 : 0;
        $meal_break_duration = intval($_POST['meal_break_duration'] ?? 30);
        $rest_break = isset($_POST['rest_break']) ? 1 : 0;
        $rest_break_duration = intval($_POST['rest_break_duration'] ?? 15);
        $vacation_balance = floatval($_POST['vacation_balance'] ?? 0);
        $sick_leave_balance = floatval($_POST['sick_leave_balance'] ?? 0);
        $pto_accrual_rate = floatval($_POST['pto_accrual_rate'] ?? 0);
        
        // Phase 4: Payroll
        $w4_filing_status = sanitize_text_field($_POST['w4_filing_status'] ?? '');
        $w4_allowances = intval($_POST['w4_allowances'] ?? 0);
        $w4_additional = floatval($_POST['w4_additional_withholding'] ?? 0);
        $w4_year = intval($_POST['w4_year'] ?? date('Y'));
        $bank_name = sanitize_text_field($_POST['bank_name'] ?? '');
        $routing_number = sanitize_text_field($_POST['routing_number'] ?? '');
        $account_number = sanitize_text_field($_POST['account_number'] ?? '');
        $account_type = sanitize_text_field($_POST['account_type'] ?? '');
        $health_insurance = floatval($_POST['health_insurance_deduction'] ?? 0);
        $k401_contribution = floatval($_POST['401k_contribution'] ?? 0);
        $k401_type = sanitize_text_field($_POST['401k_type'] ?? 'percent');
        
        // Phase 5: Compliance
        $work_authorization = sanitize_text_field($_POST['work_authorization'] ?? '');
        $citizenship_status = sanitize_text_field($_POST['citizenship_status'] ?? '');
        $i9_completion_date = sanitize_text_field($_POST['i9_completion_date'] ?? '');
        $i9_document_type = sanitize_text_field($_POST['i9_document_type'] ?? '');
        $i9_expiration_date = sanitize_text_field($_POST['i9_expiration_date'] ?? '');
        $ssn = sanitize_text_field($_POST['ssn'] ?? '');
        
        if (empty($name)) {
            $error = 'Full Name field is required.';
        } else {
            $post_data = [
                'post_title'  => $name,
                'post_type'   => 'b2b_personel',
                'post_status' => 'publish',
            ];
            
            if ($is_edit) {
                $post_data['ID'] = $personnel_id;
                $result = wp_update_post($post_data);
            } else {
                $result = wp_insert_post($post_data);
                $personnel_id = $result;
                // Generate employee ID for new personnel
                if (empty($employee_id)) {
                    $employee_id = 'EMP-' . str_pad($personnel_id, 5, '0', STR_PAD_LEFT);
                }
            }
            
            if ($result && !is_wp_error($result)) {
                // Track field changes for edit
                $changes = [];
                if ($is_edit) {
                    $old_values = [
                        'position' => get_post_meta($personnel_id, '_gorev', true),
                        'email' => get_post_meta($personnel_id, '_eposta', true),
                        'phone' => get_post_meta($personnel_id, '_telefon', true),
                        'salary' => get_post_meta($personnel_id, '_maas', true),
                        'start_date' => get_post_meta($personnel_id, '_baslangic_tarihi', true),
                        'pay_type' => get_post_meta($personnel_id, '_pay_type', true),
                        'pay_rate' => get_post_meta($personnel_id, '_pay_rate', true),
                        'employment_status' => get_post_meta($personnel_id, '_employment_status', true),
                    ];
                    
                    $new_values = [
                        'position' => $gorev,
                        'email' => $eposta,
                        'phone' => $telefon,
                        'salary' => $maas,
                        'start_date' => $baslangic,
                        'pay_type' => $pay_type,
                        'pay_rate' => $pay_rate,
                        'employment_status' => $employment_status,
                    ];
                    
                    foreach ($old_values as $field => $old_value) {
                        if ($old_value !== $new_values[$field]) {
                            $changes[$field] = [
                                'before' => $old_value,
                                'after' => $new_values[$field]
                            ];
                        }
                    }
                }
                
                // Basic fields
                update_post_meta($personnel_id, '_gorev', $gorev);
                update_post_meta($personnel_id, '_eposta', $eposta);
                update_post_meta($personnel_id, '_telefon', $telefon);
                update_post_meta($personnel_id, '_maas', $maas);
                update_post_meta($personnel_id, '_baslangic_tarihi', $baslangic);
                
                // Phase 1 fields
                update_post_meta($personnel_id, '_emergency_contact_name', $emergency_name);
                update_post_meta($personnel_id, '_emergency_contact_relationship', $emergency_rel);
                update_post_meta($personnel_id, '_emergency_contact_phone', $emergency_phone);
                update_post_meta($personnel_id, '_employment_status', $employment_status);
                update_post_meta($personnel_id, '_employee_id', $employee_id);
                
                // Phase 2 fields
                update_post_meta($personnel_id, '_pay_type', $pay_type);
                update_post_meta($personnel_id, '_pay_rate', $pay_rate);
                update_post_meta($personnel_id, '_flsa_status', $flsa_status);
                update_post_meta($personnel_id, '_reports_to', $reports_to);
                update_post_meta($personnel_id, '_termination_date', $termination_date);
                update_post_meta($personnel_id, '_rehire_date', $rehire_date);
                
                // Auto-update status based on termination date
                if (!empty($termination_date) && strtotime($termination_date) <= time()) {
                    update_post_meta($personnel_id, '_employment_status', 'inactive');
                }
                
                // Phase 3 fields
                update_post_meta($personnel_id, '_meal_break', $meal_break);
                update_post_meta($personnel_id, '_meal_break_duration', $meal_break_duration);
                update_post_meta($personnel_id, '_rest_break', $rest_break);
                update_post_meta($personnel_id, '_rest_break_duration', $rest_break_duration);
                update_post_meta($personnel_id, '_vacation_balance', $vacation_balance);
                update_post_meta($personnel_id, '_sick_leave_balance', $sick_leave_balance);
                update_post_meta($personnel_id, '_pto_accrual_rate', $pto_accrual_rate);
                
                // Phase 4 fields
                update_post_meta($personnel_id, '_w4_filing_status', $w4_filing_status);
                update_post_meta($personnel_id, '_w4_allowances', $w4_allowances);
                update_post_meta($personnel_id, '_w4_additional_withholding', $w4_additional);
                update_post_meta($personnel_id, '_w4_year', $w4_year);
                update_post_meta($personnel_id, '_bank_name', $bank_name);
                // Encrypt sensitive banking info
                if (!empty($routing_number)) {
                    update_post_meta($personnel_id, '_routing_number', wp_hash_password($routing_number));
                }
                if (!empty($account_number)) {
                    update_post_meta($personnel_id, '_account_number', wp_hash_password($account_number));
                }
                update_post_meta($personnel_id, '_account_type', $account_type);
                update_post_meta($personnel_id, '_health_insurance_deduction', $health_insurance);
                update_post_meta($personnel_id, '_401k_contribution', $k401_contribution);
                update_post_meta($personnel_id, '_401k_type', $k401_type);
                
                // Phase 5 fields
                update_post_meta($personnel_id, '_work_authorization', $work_authorization);
                update_post_meta($personnel_id, '_citizenship_status', $citizenship_status);
                update_post_meta($personnel_id, '_i9_completion_date', $i9_completion_date);
                update_post_meta($personnel_id, '_i9_document_type', $i9_document_type);
                update_post_meta($personnel_id, '_i9_expiration_date', $i9_expiration_date);
                // Encrypt SSN
                if (!empty($ssn)) {
                    update_post_meta($personnel_id, '_ssn', wp_hash_password($ssn));
                }
                
                if ($department) {
                    wp_set_object_terms($personnel_id, $department, 'b2b_departman');
                }
                
                // Log activity
                if ($is_edit) {
                    $details = 'Personnel information updated';
                    if (!empty($changes)) {
                        $details .= ' (' . count($changes) . ' field' . (count($changes) > 1 ? 's' : '') . ' changed)';
                    }
                    b2b_log_personnel_activity($personnel_id, 'personnel_edited', $details, $changes);
                } else {
                    b2b_log_personnel_activity($personnel_id, 'personnel_added', 'Personnel added to system');
                }
                
                $success = true;
            } else {
                $error = 'An error occurred. Please try again.';
            }
        }
    }
    
    // Get current data for edit
    if ($is_edit) {
        $post = get_post($personnel_id);
        $name = $post->post_title;
        $gorev = get_post_meta($personnel_id, '_gorev', true);
        $eposta = get_post_meta($personnel_id, '_eposta', true);
        $telefon = get_post_meta($personnel_id, '_telefon', true);
        $maas = get_post_meta($personnel_id, '_maas', true);
        $baslangic = get_post_meta($personnel_id, '_baslangic_tarihi', true);
        $terms = get_the_terms($personnel_id, 'b2b_departman');
        $current_dept = $terms && !is_wp_error($terms) ? $terms[0]->term_id : 0;
        
        // Phase 1
        $emergency_name = get_post_meta($personnel_id, '_emergency_contact_name', true);
        $emergency_rel = get_post_meta($personnel_id, '_emergency_contact_relationship', true);
        $emergency_phone = get_post_meta($personnel_id, '_emergency_contact_phone', true);
        $employment_status = get_post_meta($personnel_id, '_employment_status', true) ?: 'active';
        $employee_id = get_post_meta($personnel_id, '_employee_id', true);
        
        // Phase 2
        $pay_type = get_post_meta($personnel_id, '_pay_type', true);
        $pay_rate = get_post_meta($personnel_id, '_pay_rate', true);
        $flsa_status = get_post_meta($personnel_id, '_flsa_status', true);
        $reports_to = get_post_meta($personnel_id, '_reports_to', true);
        $termination_date = get_post_meta($personnel_id, '_termination_date', true);
        $rehire_date = get_post_meta($personnel_id, '_rehire_date', true);
        
        // Phase 3
        $meal_break = get_post_meta($personnel_id, '_meal_break', true);
        $meal_break_duration = get_post_meta($personnel_id, '_meal_break_duration', true) ?: 30;
        $rest_break = get_post_meta($personnel_id, '_rest_break', true);
        $rest_break_duration = get_post_meta($personnel_id, '_rest_break_duration', true) ?: 15;
        $vacation_balance = get_post_meta($personnel_id, '_vacation_balance', true) ?: 0;
        $sick_leave_balance = get_post_meta($personnel_id, '_sick_leave_balance', true) ?: 0;
        $pto_accrual_rate = get_post_meta($personnel_id, '_pto_accrual_rate', true) ?: 0;
        
        // Phase 4
        $w4_filing_status = get_post_meta($personnel_id, '_w4_filing_status', true);
        $w4_allowances = get_post_meta($personnel_id, '_w4_allowances', true) ?: 0;
        $w4_additional = get_post_meta($personnel_id, '_w4_additional_withholding', true) ?: 0;
        $w4_year = get_post_meta($personnel_id, '_w4_year', true) ?: date('Y');
        $bank_name = get_post_meta($personnel_id, '_bank_name', true);
        $routing_number = ''; // Don't retrieve encrypted data
        $account_number = ''; // Don't retrieve encrypted data
        $account_type = get_post_meta($personnel_id, '_account_type', true);
        $health_insurance = get_post_meta($personnel_id, '_health_insurance_deduction', true) ?: 0;
        $k401_contribution = get_post_meta($personnel_id, '_401k_contribution', true) ?: 0;
        $k401_type = get_post_meta($personnel_id, '_401k_type', true) ?: 'percent';
        
        // Phase 5
        $work_authorization = get_post_meta($personnel_id, '_work_authorization', true);
        $citizenship_status = get_post_meta($personnel_id, '_citizenship_status', true);
        $i9_completion_date = get_post_meta($personnel_id, '_i9_completion_date', true);
        $i9_document_type = get_post_meta($personnel_id, '_i9_document_type', true);
        $i9_expiration_date = get_post_meta($personnel_id, '_i9_expiration_date', true);
        $ssn = ''; // Don't retrieve encrypted SSN
    } else {
        $name = $gorev = $eposta = $telefon = $baslangic = '';
        $maas = 0;
        $current_dept = 0;
        
        // Initialize all new fields for add mode
        $emergency_name = $emergency_rel = $emergency_phone = '';
        $employment_status = 'active';
        $employee_id = '';
        $pay_type = $flsa_status = '';
        $pay_rate = 0;
        $reports_to = 0;
        $termination_date = $rehire_date = '';
        $meal_break = $rest_break = 0;
        $meal_break_duration = 30;
        $rest_break_duration = 15;
        $vacation_balance = $sick_leave_balance = $pto_accrual_rate = 0;
        $w4_filing_status = '';
        $w4_allowances = 0;
        $w4_additional = 0;
        $w4_year = date('Y');
        $bank_name = $routing_number = $account_number = $account_type = '';
        $health_insurance = $k401_contribution = 0;
        $k401_type = 'percent';
        $work_authorization = $citizenship_status = '';
        $i9_completion_date = $i9_document_type = $i9_expiration_date = '';
        $ssn = '';
    }
    
    // Get departments
    $departments = get_terms([
        'taxonomy'   => 'b2b_departman',
        'hide_empty' => false,
    ]);
    
    // Get all active personnel for Reports-To dropdown
    $all_personnel = get_posts([
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $is_edit ? 'Edit Personnel' : 'Add New Personnel' ?> - Admin Panel</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            
            .container {
                max-width: 800px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            
            .form-card {
                background: white;
                border-radius: 8px;
                padding: 2rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            
            .alert {
                padding: 1rem;
                border-radius: 6px;
                margin-bottom: 1.5rem;
            }
            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #6ee7b7;
            }
            .alert-error {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #fca5a5;
            }
            
            .form-group {
                margin-bottom: 1.5rem;
            }
            label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                font-size: 0.875rem;
                color: #374151;
            }
            input[type="text"],
            input[type="email"],
            input[type="tel"],
            input[type="number"],
            input[type="date"],
            select {
                width: 100%;
                padding: 0.625rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            input:focus, select:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            
            .form-actions {
                display: flex;
                gap: 1rem;
                margin-top: 2rem;
            }
            .btn {
                padding: 0.625rem 1.5rem;
                border: none;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .btn-primary {
                background: #3b82f6;
                color: white;
            }
            .btn-primary:hover { background: #2563eb; }
            .btn-secondary {
                background: #e5e7eb;
                color: #374151;
            }
            .btn-secondary:hover { background: #d1d5db; }
            
            .form-section {
                margin-bottom: 2rem;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                overflow: hidden;
            }
            .section-header {
                background: #f9fafb;
                padding: 1rem 1.5rem;
                cursor: pointer;
                display: flex;
                justify-content: space-between;
                align-items: center;
                user-select: none;
            }
            .section-header:hover {
                background: #f3f4f6;
            }
            .section-header h3 {
                font-size: 1rem;
                font-weight: 600;
                color: #374151;
                margin: 0;
            }
            .section-toggle {
                color: #6b7280;
                transition: transform 0.2s;
            }
            .section-content {
                padding: 1.5rem;
                display: none;
            }
            .section-content.active {
                display: block;
            }
            .section-content .form-group:last-child {
                margin-bottom: 0;
            }
            .form-row {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            
            @media (max-width: 768px) {
                .header { flex-direction: column; gap: 1rem; }
                .form-card { padding: 1.5rem; }
                .form-actions { flex-direction: column; }
                .form-row { grid-template-columns: 1fr; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-user-plus"></i> <?= $is_edit ? 'Edit Personnel' : 'Add New Personnel' ?></h1>
            <a href="<?= home_url('/personnel-panel') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Personnel List
            </a>
        </div>
        
        <div class="container">
            <div class="form-card">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        Personnel successfully <?= $is_edit ? 'updated' : 'added' ?>!
                        <a href="<?= home_url('/personnel-panel') ?>" style="margin-left: 1rem; color: #065f46; text-decoration: underline;">
                            Return to list
                        </a>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?= esc_html($error) ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <!-- Section 1: Basic Information -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-user"></i> Basic Information</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content active">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Full Name *</label>
                                <input type="text" name="name" value="<?= esc_attr($name) ?>" required>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-envelope"></i> Email</label>
                                    <input type="email" name="eposta" value="<?= esc_attr($eposta) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-phone"></i> Phone</label>
                                    <input type="tel" name="telefon" value="<?= esc_attr($telefon) ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-id-badge"></i> Employee ID</label>
                                    <input type="text" name="employee_id" value="<?= esc_attr($employee_id) ?>" placeholder="Auto-generated if empty">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-toggle-on"></i> Employment Status</label>
                                    <select name="employment_status">
                                        <option value="active" <?= selected($employment_status, 'active', false) ?>>Active</option>
                                        <option value="inactive" <?= selected($employment_status, 'inactive', false) ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 2: Employment Details -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-briefcase"></i> Employment Details</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content active">
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-building"></i> Department</label>
                                    <select name="department">
                                        <option value="">Select...</option>
                                        <?php foreach ($departments as $dept): ?>
                                            <option value="<?= $dept->term_id ?>" <?= selected($current_dept, $dept->term_id, false) ?>>
                                                <?= esc_html($dept->name) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-briefcase"></i> Position / Job Title</label>
                                    <input type="text" name="gorev" value="<?= esc_attr($gorev) ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-user-tie"></i> Reports To (Manager)</label>
                                <select name="reports_to">
                                    <option value="0">None</option>
                                    <?php foreach ($all_personnel as $person): 
                                        if ($is_edit && $person->ID == $personnel_id) continue; // Skip self
                                    ?>
                                        <option value="<?= $person->ID ?>" <?= selected($reports_to, $person->ID, false) ?>>
                                            <?= esc_html($person->post_title) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-plus"></i> Hire Date</label>
                                    <input type="date" name="baslangic_tarihi" value="<?= esc_attr($baslangic) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-minus"></i> Termination Date</label>
                                    <input type="date" name="termination_date" value="<?= esc_attr($termination_date) ?>">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-check"></i> Re-hire Date (if applicable)</label>
                                <input type="date" name="rehire_date" value="<?= esc_attr($rehire_date) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 3: Pay & Classification -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-dollar-sign"></i> Pay & Classification</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-money-bill-wave"></i> Pay Type</label>
                                    <select name="pay_type">
                                        <option value="">Select...</option>
                                        <option value="hourly" <?= selected($pay_type, 'hourly', false) ?>>Hourly</option>
                                        <option value="salaried" <?= selected($pay_type, 'salaried', false) ?>>Salaried</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-coins"></i> Pay Rate ($/hr or Annual)</label>
                                    <input type="number" name="pay_rate" value="<?= esc_attr($pay_rate) ?>" step="0.01">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-dollar-sign"></i> Base Salary ($)</label>
                                    <input type="number" name="maas" value="<?= esc_attr($maas) ?>" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-balance-scale"></i> FLSA Status</label>
                                    <select name="flsa_status">
                                        <option value="">Select...</option>
                                        <option value="exempt" <?= selected($flsa_status, 'exempt', false) ?>>Exempt (No OT)</option>
                                        <option value="non-exempt" <?= selected($flsa_status, 'non-exempt', false) ?>>Non-Exempt (OT Eligible)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 4: Time & Attendance Settings -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-clock"></i> Time & Attendance Settings</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="meal_break" value="1" <?= checked($meal_break, 1, false) ?>>
                                        <i class="fas fa-utensils"></i> Meal Break (30+ min, unpaid)
                                    </label>
                                    <input type="number" name="meal_break_duration" value="<?= esc_attr($meal_break_duration) ?>" placeholder="Duration in minutes">
                                </div>
                                
                                <div class="form-group">
                                    <label>
                                        <input type="checkbox" name="rest_break" value="1" <?= checked($rest_break, 1, false) ?>>
                                        <i class="fas fa-coffee"></i> Rest Break (15 min, paid)
                                    </label>
                                    <input type="number" name="rest_break_duration" value="<?= esc_attr($rest_break_duration) ?>" placeholder="Duration in minutes">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-umbrella-beach"></i> Vacation Balance (hours)</label>
                                    <input type="number" name="vacation_balance" value="<?= esc_attr($vacation_balance) ?>" step="0.5">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-thermometer"></i> Sick Leave Balance (hours)</label>
                                    <input type="number" name="sick_leave_balance" value="<?= esc_attr($sick_leave_balance) ?>" step="0.5">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-chart-line"></i> PTO Accrual Rate (hours per pay period)</label>
                                <input type="number" name="pto_accrual_rate" value="<?= esc_attr($pto_accrual_rate) ?>" step="0.01">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 5: Tax Information (W-4 & SSN) -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-file-invoice-dollar"></i> Tax Information (W-4 & SSN)</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-users"></i> W-4 Filing Status</label>
                                    <select name="w4_filing_status">
                                        <option value="">Select...</option>
                                        <option value="single" <?= selected($w4_filing_status, 'single', false) ?>>Single</option>
                                        <option value="married_joint" <?= selected($w4_filing_status, 'married_joint', false) ?>>Married Filing Jointly</option>
                                        <option value="married_separate" <?= selected($w4_filing_status, 'married_separate', false) ?>>Married Filing Separately</option>
                                        <option value="head_of_household" <?= selected($w4_filing_status, 'head_of_household', false) ?>>Head of Household</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-hashtag"></i> Number of Allowances</label>
                                    <input type="number" name="w4_allowances" value="<?= esc_attr($w4_allowances) ?>" min="0">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-plus-circle"></i> Additional Withholding ($)</label>
                                    <input type="number" name="w4_additional_withholding" value="<?= esc_attr($w4_additional) ?>" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-alt"></i> Tax Year</label>
                                    <input type="number" name="w4_year" value="<?= esc_attr($w4_year) ?>" min="2020" max="2030">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-shield-alt"></i> Social Security Number (SSN)</label>
                                <input type="text" name="ssn" value="<?= esc_attr($ssn) ?>" placeholder="XXX-XX-XXXX (encrypted storage)" maxlength="11">
                                <?php if ($is_edit): ?>
                                    <small style="color: #6b7280;">Leave empty to keep existing SSN</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 6: Direct Deposit & Banking -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-university"></i> Direct Deposit & Banking</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-group">
                                <label><i class="fas fa-landmark"></i> Bank Name</label>
                                <input type="text" name="bank_name" value="<?= esc_attr($bank_name) ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-route"></i> Routing Number (9 digits)</label>
                                    <input type="text" name="routing_number" value="<?= esc_attr($routing_number) ?>" placeholder="XXXXXXXXX" maxlength="9">
                                    <?php if ($is_edit): ?>
                                        <small style="color: #6b7280;">Leave empty to keep existing</small>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-credit-card"></i> Account Number</label>
                                    <input type="text" name="account_number" value="<?= esc_attr($account_number) ?>" placeholder="Encrypted storage">
                                    <?php if ($is_edit): ?>
                                        <small style="color: #6b7280;">Leave empty to keep existing</small>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-wallet"></i> Account Type</label>
                                <select name="account_type">
                                    <option value="">Select...</option>
                                    <option value="checking" <?= selected($account_type, 'checking', false) ?>>Checking</option>
                                    <option value="savings" <?= selected($account_type, 'savings', false) ?>>Savings</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 7: Deductions -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-calculator"></i> Deductions</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-group">
                                <label><i class="fas fa-heart"></i> Health Insurance Deduction ($)</label>
                                <input type="number" name="health_insurance_deduction" value="<?= esc_attr($health_insurance) ?>" step="0.01">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-piggy-bank"></i> 401(k) Contribution</label>
                                    <input type="number" name="401k_contribution" value="<?= esc_attr($k401_contribution) ?>" step="0.01">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-percent"></i> 401(k) Type</label>
                                    <select name="401k_type">
                                        <option value="percent" <?= selected($k401_type, 'percent', false) ?>>Percentage (%)</option>
                                        <option value="dollar" <?= selected($k401_type, 'dollar', false) ?>>Dollar Amount ($)</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 8: Compliance (I-9 & Work Authorization) -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-file-contract"></i> Compliance (I-9 & Work Authorization)</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-passport"></i> Work Authorization</label>
                                    <select name="work_authorization">
                                        <option value="">Select...</option>
                                        <option value="citizen" <?= selected($work_authorization, 'citizen', false) ?>>US Citizen</option>
                                        <option value="green_card" <?= selected($work_authorization, 'green_card', false) ?>>Permanent Resident (Green Card)</option>
                                        <option value="work_visa" <?= selected($work_authorization, 'work_visa', false) ?>>Work Visa</option>
                                        <option value="other" <?= selected($work_authorization, 'other', false) ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-flag-usa"></i> Citizenship Status</label>
                                    <select name="citizenship_status">
                                        <option value="">Select...</option>
                                        <option value="us_citizen" <?= selected($citizenship_status, 'us_citizen', false) ?>>US Citizen</option>
                                        <option value="permanent_resident" <?= selected($citizenship_status, 'permanent_resident', false) ?>>Permanent Resident</option>
                                        <option value="work_visa" <?= selected($citizenship_status, 'work_visa', false) ?>>Work Visa</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-calendar-check"></i> I-9 Completion Date</label>
                                    <input type="date" name="i9_completion_date" value="<?= esc_attr($i9_completion_date) ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-id-card"></i> I-9 Document Type</label>
                                    <input type="text" name="i9_document_type" value="<?= esc_attr($i9_document_type) ?>" placeholder="e.g., Passport, Driver's License">
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label><i class="fas fa-calendar-times"></i> Document Expiration Date</label>
                                <input type="date" name="i9_expiration_date" value="<?= esc_attr($i9_expiration_date) ?>">
                            </div>
                        </div>
                    </div>
                    
                    <!-- Section 9: Emergency Contact -->
                    <div class="form-section">
                        <div class="section-header" onclick="toggleSection(this)">
                            <h3><i class="fas fa-phone-alt"></i> Emergency Contact</h3>
                            <i class="fas fa-chevron-down section-toggle"></i>
                        </div>
                        <div class="section-content">
                            <div class="form-group">
                                <label><i class="fas fa-user"></i> Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" value="<?= esc_attr($emergency_name) ?>">
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label><i class="fas fa-users"></i> Relationship</label>
                                    <select name="emergency_contact_relationship">
                                        <option value="">Select...</option>
                                        <option value="spouse" <?= selected($emergency_rel, 'spouse', false) ?>>Spouse</option>
                                        <option value="parent" <?= selected($emergency_rel, 'parent', false) ?>>Parent</option>
                                        <option value="sibling" <?= selected($emergency_rel, 'sibling', false) ?>>Sibling</option>
                                        <option value="friend" <?= selected($emergency_rel, 'friend', false) ?>>Friend</option>
                                        <option value="other" <?= selected($emergency_rel, 'other', false) ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label><i class="fas fa-phone"></i> Emergency Contact Phone</label>
                                    <input type="tel" name="emergency_contact_phone" value="<?= esc_attr($emergency_phone) ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="personnel_submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> <?= $is_edit ? 'Update' : 'Save' ?>
                        </button>
                        <a href="<?= home_url('/personnel-panel') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <script>
            function toggleSection(header) {
                const content = header.nextElementSibling;
                const toggle = header.querySelector('.section-toggle');
                
                content.classList.toggle('active');
                toggle.style.transform = content.classList.contains('active') ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        </script>
    </body>
    </html>
    <?php
}

/* =====================================================
 * 6. DELETE PERSONNEL ACTION
 * ===================================================== */
function b2b_personnel_delete_action() {
    $id = get_query_var('personnel_id');
    
    if ($id && get_post_type($id) === 'b2b_personel') {
        wp_delete_post($id, true);
    }
    
    wp_redirect('/personnel-panel');
    exit;
}

/* =====================================================
 * 7. DEPARTMENT MANAGEMENT PAGE
 * ===================================================== */
function b2b_personnel_departments_page() {
    $success = '';
    $error = '';
    
    // Handle add department
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_department'])) {
        $name = sanitize_text_field($_POST['department_name']);
        $slug = sanitize_title($_POST['department_name']);
        
        if (!empty($name)) {
            $result = wp_insert_term($name, 'b2b_departman', [
                'slug' => $slug
            ]);
            
            if (!is_wp_error($result)) {
                $success = 'Department added successfully!';
            } else {
                $error = 'Could not add department: ' . $result->get_error_message();
            }
        } else {
            $error = 'Department name cannot be empty.';
        }
    }
    
    // Get all departments
    $departments = get_terms([
        'taxonomy'   => 'b2b_departman',
        'hide_empty' => false,
    ]);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Department Management - Admin Panel</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            
            .container {
                max-width: 1000px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            
            .alert {
                padding: 1rem;
                border-radius: 6px;
                margin-bottom: 1.5rem;
            }
            .alert-success {
                background: #d1fae5;
                color: #065f46;
                border: 1px solid #6ee7b7;
            }
            .alert-error {
                background: #fee2e2;
                color: #991b1b;
                border: 1px solid #fca5a5;
            }
            
            .card {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .card h2 {
                font-size: 1.25rem;
                margin-bottom: 1rem;
                color: #111827;
            }
            
            .form-row {
                display: flex;
                gap: 1rem;
                align-items: flex-end;
            }
            .form-group {
                flex: 1;
            }
            .form-group label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                font-size: 0.875rem;
                color: #374151;
            }
            .form-group input {
                width: 100%;
                padding: 0.625rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            
            .btn {
                padding: 0.625rem 1.25rem;
                border: none;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
            }
            .btn-primary {
                background: #3b82f6;
                color: white;
            }
            .btn-primary:hover {
                background: #2563eb;
            }
            .btn-danger {
                background: #ef4444;
                color: white;
            }
            .btn-danger:hover {
                background: #dc2626;
            }
            
            .table-container {
                overflow-x: auto;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                background: #f9fafb;
                padding: 0.75rem 1rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.875rem;
                color: #374151;
                border-bottom: 1px solid #e5e7eb;
            }
            td {
                padding: 1rem;
                border-bottom: 1px solid #e5e7eb;
                font-size: 0.875rem;
            }
            tr:last-child td { border-bottom: none; }
            tr:hover { background: #f9fafb; }
            
            .empty-state {
                text-align: center;
                padding: 3rem;
                color: #6b7280;
            }
            .empty-state i {
                font-size: 3rem;
                margin-bottom: 1rem;
                opacity: 0.5;
            }
            
            @media (max-width: 768px) {
                .header { flex-direction: column; gap: 1rem; }
                .form-row { flex-direction: column; align-items: stretch; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-building"></i> Department Management</h1>
            <a href="<?= home_url('/personnel-panel') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Personnel List
            </a>
        </div>
        
        <div class="container">
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?= esc_html($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?= esc_html($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <h2>Add New Department</h2>
                <form method="POST">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="department_name">Department Name</label>
                            <input type="text" id="department_name" name="department_name" placeholder="e.g: Sales, Production, Logistics" required>
                        </div>
                        <button type="submit" name="add_department" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </form>
            </div>
            
            <div class="card">
                <h2>Existing Departments</h2>
                <div class="table-container">
                    <?php if ($departments && count($departments) > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Department Name</th>
                                    <th>Slug</th>
                                    <th style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($departments as $dept): ?>
                                    <tr>
                                        <td><strong><?= esc_html($dept->name) ?></strong></td>
                                        <td><?= esc_html($dept->slug) ?></td>
                                        <td>
                                            <a href="<?= home_url('/personnel-panel/department-delete/' . $dept->term_id) ?>" 
                                               class="btn btn-danger" 
                                               onclick="return confirm('Are you sure you want to delete this department?')"
                                               style="padding: 0.375rem 0.75rem; font-size: 0.75rem;">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-building"></i>
                            <p>No departments added yet.</p>
                            <p style="margin-top: 0.5rem; font-size: 0.875rem;">
                                Yukarıdaki formu kullanarak ilk departmanı ekleyin.
                            </p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * 8. DELETE DEPARTMENT ACTION
 * ===================================================== */
function b2b_personnel_department_delete() {
    $id = get_query_var('department_id');
    
    if ($id) {
        wp_delete_term($id, 'b2b_departman');
    }
    
    wp_redirect(home_url('/personnel-panel/departments'));
    exit;
}

/* =====================================================
 * 9. EXPORT PERSONNEL TO CSV
 * ===================================================== */
function b2b_personnel_export_csv() {
    // Query all personnel
    $personnel = get_posts([
        'post_type'      => 'b2b_personel',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ]);
    
    // Set headers for CSV download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=personnel_' . date('Y-m-d') . '.csv');
    
    // Create output stream
    $output = fopen('php://output', 'w');
    
    // Add BOM for Excel UTF-8 support
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    // Add CSV headers
    fputcsv($output, ['Full Name', 'Department', 'Position', 'Email', 'Phone', 'Salary', 'Start Date']);
    
    // Add data rows
    foreach ($personnel as $person) {
        $id = $person->ID;
        $depts = get_the_terms($id, 'b2b_departman');
        $dept_name = $depts && !is_wp_error($depts) ? $depts[0]->name : '';
        
        fputcsv($output, [
            $person->post_title,
            $dept_name,
            get_post_meta($id, '_gorev', true),
            get_post_meta($id, '_eposta', true),
            get_post_meta($id, '_telefon', true),
            get_post_meta($id, '_maas', true),
            get_post_meta($id, '_baslangic_tarihi', true),
        ]);
    }
    
    fclose($output);
    exit;
}

/* =====================================================
 * 10. BULK DELETE PERSONNEL
 * ===================================================== */
function b2b_personnel_bulk_delete() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_ids'])) {
        $ids = explode(',', sanitize_text_field($_POST['selected_ids']));
        
        foreach ($ids as $id) {
            $id = intval($id);
            if ($id > 0) {
                wp_delete_post($id, true);
                // Log activity
                b2b_log_personnel_activity($id, 'personnel_deleted', 'Personnel deleted');
            }
        }
    }
    
    wp_redirect(home_url('/personnel-panel'));
    exit;
}

/* =====================================================
 * 11. PERSONNEL DETAIL VIEW PAGE
 * ===================================================== */
function b2b_personnel_view_page() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $person = get_post($personnel_id);
    
    if (!$person || $person->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    // Get basic personnel data
    $gorev = get_post_meta($personnel_id, '_gorev', true);
    $eposta = get_post_meta($personnel_id, '_eposta', true);
    $telefon = get_post_meta($personnel_id, '_telefon', true);
    $maas = get_post_meta($personnel_id, '_maas', true);
    $baslangic = get_post_meta($personnel_id, '_baslangic_tarihi', true);
    $depts = get_the_terms($personnel_id, 'b2b_departman');
    $dept_name = $depts && !is_wp_error($depts) ? $depts[0]->name : 'N/A';
    
    // Get Phase 1 data (Critical Compliance)
    $employee_id = get_post_meta($personnel_id, '_employee_id', true);
    $employment_status = get_post_meta($personnel_id, '_employment_status', true);
    $emergency_name = get_post_meta($personnel_id, '_emergency_contact_name', true);
    $emergency_relationship = get_post_meta($personnel_id, '_emergency_contact_relationship', true);
    $emergency_phone = get_post_meta($personnel_id, '_emergency_contact_phone', true);
    
    // Get Phase 2 data (Employment Basics)
    $pay_type = get_post_meta($personnel_id, '_pay_type', true);
    $pay_rate = get_post_meta($personnel_id, '_pay_rate', true);
    $flsa_status = get_post_meta($personnel_id, '_flsa_status', true);
    $reports_to = get_post_meta($personnel_id, '_reports_to', true);
    $termination_date = get_post_meta($personnel_id, '_termination_date', true);
    $rehire_date = get_post_meta($personnel_id, '_rehire_date', true);
    
    // Get manager name if exists
    $manager_name = 'N/A';
    if ($reports_to) {
        $manager = get_post($reports_to);
        if ($manager) {
            $manager_name = $manager->post_title;
        }
    }
    
    // Get Phase 3 data (Time Tracking)
    $meal_break = get_post_meta($personnel_id, '_meal_break', true);
    $meal_break_duration = get_post_meta($personnel_id, '_meal_break_duration', true);
    $rest_break = get_post_meta($personnel_id, '_rest_break', true);
    $rest_break_duration = get_post_meta($personnel_id, '_rest_break_duration', true);
    $vacation_balance = get_post_meta($personnel_id, '_vacation_balance', true);
    $sick_leave_balance = get_post_meta($personnel_id, '_sick_leave_balance', true);
    $pto_accrual_rate = get_post_meta($personnel_id, '_pto_accrual_rate', true);
    
    // Get Phase 4 data (Payroll)
    $w4_filing_status = get_post_meta($personnel_id, '_w4_filing_status', true);
    $w4_allowances = get_post_meta($personnel_id, '_w4_allowances', true);
    $w4_additional = get_post_meta($personnel_id, '_w4_additional_withholding', true);
    $w4_year = get_post_meta($personnel_id, '_w4_year', true);
    $bank_name = get_post_meta($personnel_id, '_bank_name', true);
    $account_type = get_post_meta($personnel_id, '_account_type', true);
    $health_insurance = get_post_meta($personnel_id, '_health_insurance_deduction', true);
    $k401_contribution = get_post_meta($personnel_id, '_401k_contribution', true);
    $k401_type = get_post_meta($personnel_id, '_401k_type', true);
    
    // Get Phase 5 data (Advanced Compliance)
    $work_authorization = get_post_meta($personnel_id, '_work_authorization', true);
    $citizenship_status = get_post_meta($personnel_id, '_citizenship_status', true);
    $i9_completion_date = get_post_meta($personnel_id, '_i9_completion_date', true);
    $i9_document_type = get_post_meta($personnel_id, '_i9_document_type', true);
    $i9_expiration_date = get_post_meta($personnel_id, '_i9_expiration_date', true);
    
    // Get notes, documents, attendance
    $notes = get_post_meta($personnel_id, '_notes', true) ?: [];
    $documents = get_post_meta($personnel_id, '_documents', true) ?: [];
    $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
    $activity = b2b_get_personnel_activity($personnel_id, 10);
    
    // Calculate today's status
    $today = date('Y-m-d');
    $clocked_in = false;
    $clock_in_time = '';
    foreach (array_reverse($attendance) as $record) {
        if (strpos($record['date'], $today) === 0 && $record['type'] === 'clock_in') {
            $clocked_in = true;
            $clock_in_time = date('g:i A', strtotime($record['date']));
            break;
        }
        if (strpos($record['date'], $today) === 0 && $record['type'] === 'clock_out') {
            break;
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>View Personnel - <?= esc_html($person->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .add-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                background: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 500;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
            }
            .add-btn:hover { background: #2563eb; }
            .container {
                max-width: 1400px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-user"></i> View Personnel</h1>
            <a href="<?= home_url('/b2b-panel') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Admin Panel
            </a>
        </div>
        <div class="container">
    
    <div class="main-content">
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <div style="display:flex;align-items:center;gap:20px;">
                <?php
                $photo_url = get_post_meta($personnel_id, '_photo_url', true);
                if ($photo_url): ?>
                    <img src="<?= esc_url($photo_url) ?>" alt="<?= esc_attr($person->post_title) ?>" 
                         style="width:150px;height:150px;border-radius:50%;object-fit:cover;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                <?php else: ?>
                    <div style="width:150px;height:150px;border-radius:50%;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);display:flex;align-items:center;justify-content:center;box-shadow:0 4px 6px rgba(0,0,0,0.1);">
                        <i class="fas fa-user" style="font-size:60px;color:#fff;"></i>
                    </div>
                <?php endif; ?>
                <div>
                    <h1 style="margin:0 0 5px 0;font-size:28px;color:#111827;"><?= esc_html($person->post_title) ?></h1>
                    <p style="margin:0 0 10px 0;color:#6b7280;"><?= esc_html($gorev) ?> • <?= esc_html($dept_name) ?></p>
                    <a href="<?= home_url('/personnel-panel/upload-photo/' . $personnel_id) ?>" class="add-btn" style="background:#8b5cf6;font-size:13px;padding:6px 12px;">
                        <i class="fas fa-camera"></i> <?= $photo_url ? 'Change Photo' : 'Upload Photo' ?>
                    </a>
                </div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="<?= home_url('/personnel-panel/metrics/' . $personnel_id) ?>" class="add-btn" style="background:#10b981;">
                    <i class="fas fa-chart-line"></i> Metrics
                </a>
                <a href="<?= home_url('/personnel-panel/enhanced-audit/' . $personnel_id) ?>" class="add-btn" style="background:#8b5cf6;">
                    <i class="fas fa-history"></i> Audit Log
                </a>
                <a href="<?= home_url('/personnel-panel/edit/' . $personnel_id) ?>" class="add-btn" style="background:#6366f1;">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <a href="<?= home_url('/personnel-panel') ?>" class="add-btn" style="background:#6b7280;">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div style="border-bottom:2px solid #e5e7eb;margin-bottom:20px;">
            <div style="display:flex;gap:20px;">
                <button class="tab-btn active" onclick="showTab('info')" style="padding:12px 20px;border:none;background:none;cursor:pointer;border-bottom:3px solid #3b82f6;color:#3b82f6;font-weight:600;">
                    <i class="fas fa-user"></i> Information
                </button>
                <button class="tab-btn" onclick="showTab('notes')" style="padding:12px 20px;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;color:#6b7280;font-weight:600;">
                    <i class="fas fa-sticky-note"></i> Notes (<?= count($notes) ?>)
                </button>
                <button class="tab-btn" onclick="showTab('documents')" style="padding:12px 20px;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;color:#6b7280;font-weight:600;">
                    <i class="fas fa-file"></i> Documents (<?= count($documents) ?>)
                </button>
                <button class="tab-btn" onclick="showTab('attendance')" style="padding:12px 20px;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;color:#6b7280;font-weight:600;">
                    <i class="fas fa-clock"></i> Attendance
                </button>
                <button class="tab-btn" onclick="showTab('activity')" style="padding:12px 20px;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;color:#6b7280;font-weight:600;">
                    <i class="fas fa-history"></i> Activity
                </button>
                <button class="tab-btn" onclick="showTab('payments')" style="padding:12px 20px;border:none;background:none;cursor:pointer;border-bottom:3px solid transparent;color:#6b7280;font-weight:600;">
                    <i class="fas fa-money-bill-wave"></i> Payments
                </button>
            </div>
        </div>

        <!-- Tab Content: Information -->
        <div id="tab-info" class="tab-content">
            <!-- Basic Information Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-user"></i> Basic Information
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Full Name</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($person->post_title) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Employee ID</label>
                        <p style="margin:0;font-size:16px;color:#111827;font-family:monospace;background:#f3f4f6;padding:4px 8px;border-radius:4px;display:inline-block;"><?= esc_html($employee_id ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Email</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($eposta) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Phone</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($telefon) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Employment Status</label>
                        <p style="margin:0;font-size:16px;">
                            <?php if ($employment_status === 'active'): ?>
                                <span style="background:#10b981;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
                                    <i class="fas fa-check-circle"></i> Active
                                </span>
                            <?php else: ?>
                                <span style="background:#ef4444;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
                                    <i class="fas fa-times-circle"></i> Inactive
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Clock Status</label>
                        <p style="margin:0;font-size:16px;">
                            <?php if ($clocked_in): ?>
                                <span style="background:#3b82f6;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
                                    <i class="fas fa-circle" style="font-size:8px;"></i> Clocked In (<?= $clock_in_time ?>)
                                </span>
                            <?php else: ?>
                                <span style="background:#6b7280;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
                                    <i class="fas fa-circle" style="font-size:8px;"></i> Not Clocked In
                                </span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Employment Details Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-briefcase"></i> Employment Details
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Department</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($dept_name) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Position/Title</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($gorev) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Reports To</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($manager_name) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Hire Date</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($baslangic ?: 'N/A') ?></p>
                    </div>
                    <?php if ($termination_date): ?>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Termination Date</label>
                        <p style="margin:0;font-size:16px;color:#ef4444;"><?= esc_html($termination_date) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($rehire_date): ?>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Re-hire Date</label>
                        <p style="margin:0;font-size:16px;color:#10b981;"><?= esc_html($rehire_date) ?></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Pay & Classification Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-dollar-sign"></i> Pay & Classification
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Pay Type</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?php if ($pay_type === 'hourly'): ?>
                                <span style="background:#3b82f6;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">Hourly</span>
                            <?php elseif ($pay_type === 'salaried'): ?>
                                <span style="background:#8b5cf6;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">Salaried</span>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Pay Rate</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?php if ($pay_rate): ?>
                                $<?= number_format((float)$pay_rate, 2) ?><?= $pay_type === 'hourly' ? '/hr' : '/year' ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Base Salary</label>
                        <p style="margin:0;font-size:16px;color:#111827;font-weight:600;">$<?= number_format((float)$maas, 2) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">FLSA Status</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?php if ($flsa_status === 'exempt'): ?>
                                <span style="background:#10b981;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">Exempt</span>
                            <?php elseif ($flsa_status === 'non-exempt'): ?>
                                <span style="background:#f59e0b;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">Non-Exempt (OT Eligible)</span>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Time & Attendance Settings Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-clock"></i> Time & Attendance Settings
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Meal Break</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?= $meal_break ? 'Yes (' . esc_html($meal_break_duration) . ' min)' : 'No' ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Rest Break</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?= $rest_break ? 'Yes (' . esc_html($rest_break_duration) . ' min)' : 'No' ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Vacation Balance</label>
                        <p style="margin:0;font-size:16px;color:#111827;font-weight:600;color:#3b82f6;">
                            <?= esc_html($vacation_balance ?: '0') ?> hours
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Sick Leave Balance</label>
                        <p style="margin:0;font-size:16px;color:#111827;font-weight:600;color:#10b981;">
                            <?= esc_html($sick_leave_balance ?: '0') ?> hours
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">PTO Accrual Rate</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?= esc_html($pto_accrual_rate ?: '0') ?> hrs/pay period
                        </p>
                    </div>
                </div>
            </div>

            <!-- Tax Information Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-file-invoice-dollar"></i> Tax Information (W-4)
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Filing Status</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html(ucwords(str_replace('_', ' ', $w4_filing_status)) ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Allowances</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($w4_allowances ?: '0') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Additional Withholding</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?= $w4_additional ? '$' . number_format((float)$w4_additional, 2) : 'None' ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Tax Year</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($w4_year ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">SSN</label>
                        <p style="margin:0;font-size:16px;color:#6b7280;font-family:monospace;">
                            <i class="fas fa-lock"></i> ***-**-**** (Encrypted)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Payroll Information Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-university"></i> Payroll & Banking
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Bank Name</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($bank_name ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Account Type</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html(ucfirst($account_type) ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Routing Number</label>
                        <p style="margin:0;font-size:16px;color:#6b7280;font-family:monospace;">
                            <i class="fas fa-lock"></i> ********* (Encrypted)
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Account Number</label>
                        <p style="margin:0;font-size:16px;color:#6b7280;font-family:monospace;">
                            <i class="fas fa-lock"></i> ********** (Encrypted)
                        </p>
                    </div>
                </div>
            </div>

            <!-- Deductions Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-calculator"></i> Deductions
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Health Insurance</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?= $health_insurance ? '$' . number_format((float)$health_insurance, 2) : 'None' ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">401(k) Contribution</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?php if ($k401_contribution): ?>
                                <?= esc_html($k401_contribution) ?><?= $k401_type === 'percent' ? '%' : '$' ?>
                            <?php else: ?>
                                None
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Compliance & I-9 Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-file-contract"></i> Compliance & I-9
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Work Authorization</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?php if ($work_authorization): ?>
                                <?php
                                $auth_labels = [
                                    'citizen' => 'US Citizen',
                                    'green_card' => 'Permanent Resident',
                                    'work_visa' => 'Work Visa',
                                    'other' => 'Other'
                                ];
                                ?>
                                <span style="background:#10b981;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
                                    <?= esc_html($auth_labels[$work_authorization] ?? $work_authorization) ?>
                                </span>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Citizenship Status</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html(ucwords(str_replace('_', ' ', $citizenship_status)) ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">I-9 Completion Date</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($i9_completion_date ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">I-9 Document Type</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($i9_document_type ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Document Expiration</label>
                        <p style="margin:0;font-size:16px;color:#111827;">
                            <?php if ($i9_expiration_date): ?>
                                <?php
                                $exp_date = strtotime($i9_expiration_date);
                                $days_until = floor(($exp_date - time()) / (60*60*24));
                                if ($days_until < 0):
                                ?>
                                    <span style="background:#ef4444;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
                                        <i class="fas fa-exclamation-triangle"></i> Expired: <?= esc_html($i9_expiration_date) ?>
                                    </span>
                                <?php elseif ($days_until < 30): ?>
                                    <span style="background:#f59e0b;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
                                        <i class="fas fa-clock"></i> Expires Soon: <?= esc_html($i9_expiration_date) ?> (<?= $days_until ?> days)
                                    </span>
                                <?php else: ?>
                                    <?= esc_html($i9_expiration_date) ?>
                                <?php endif; ?>
                            <?php else: ?>
                                N/A
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact Section -->
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;border-bottom:2px solid #e5e7eb;padding-bottom:10px;">
                    <i class="fas fa-phone-alt"></i> Emergency Contact
                </h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Contact Name</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($emergency_name ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Relationship</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html(ucfirst($emergency_relationship) ?: 'N/A') ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;font-weight:600;">Emergency Phone</label>
                        <p style="margin:0;font-size:16px;color:#111827;font-weight:600;">
                            <?= esc_html($emergency_phone ?: 'N/A') ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tab Content: Notes -->
        <div id="tab-notes" class="tab-content" style="display:none;">
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h2 style="margin:0;font-size:20px;color:#111827;">Notes & Comments</h2>
                    <button onclick="document.getElementById('addNoteForm').style.display='block'" class="add-btn">
                        <i class="fas fa-plus"></i> Add Note
                    </button>
                </div>

                <!-- Add Note Form -->
                <div id="addNoteForm" style="display:none;background:#f9fafb;padding:20px;border-radius:8px;margin-bottom:20px;">
                    <form method="POST" action="<?= home_url('/personnel-panel/add-note/' . $personnel_id) ?>">
                        <textarea name="note_content" rows="4" style="width:100%;padding:12px;border:1px solid #d1d5db;border-radius:8px;font-size:14px;resize:vertical;" placeholder="Enter note..." required></textarea>
                        <div style="display:flex;gap:10px;margin-top:10px;">
                            <button type="submit" class="add-btn" style="background:#10b981;">Save Note</button>
                            <button type="button" onclick="document.getElementById('addNoteForm').style.display='none'" class="add-btn" style="background:#6b7280;">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Notes List -->
                <?php if (empty($notes)): ?>
                    <p style="color:#6b7280;text-align:center;padding:40px 0;">No notes yet. Add your first note above.</p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:15px;">
                        <?php foreach (array_reverse($notes) as $index => $note): ?>
                            <div style="background:#f9fafb;padding:15px;border-radius:8px;border-left:4px solid #3b82f6;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:8px;">
                                    <div>
                                        <span style="font-weight:600;color:#111827;"><?= esc_html($note['author']) ?></span>
                                        <span style="color:#6b7280;font-size:13px;margin-left:10px;"><?= date('M d, Y g:i A', strtotime($note['date'])) ?></span>
                                    </div>
                                    <a href="<?= home_url('/personnel-panel/delete-note/' . $personnel_id . '?note_index=' . $index) ?>" onclick="return confirm('Delete this note?')" style="color:#ef4444;"><i class="fas fa-trash"></i></a>
                                </div>
                                <p style="margin:0;color:#374151;line-height:1.6;"><?= nl2br(esc_html($note['content'])) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab Content: Documents -->
        <div id="tab-documents" class="tab-content" style="display:none;">
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <h2 style="margin:0;font-size:20px;color:#111827;">Attached Documents</h2>
                    <button onclick="document.getElementById('uploadDocForm').style.display='block'" class="add-btn">
                        <i class="fas fa-upload"></i> Upload Document
                    </button>
                </div>

                <!-- Upload Form -->
                <div id="uploadDocForm" style="display:none;background:#f9fafb;padding:20px;border-radius:8px;margin-bottom:20px;">
                    <form method="POST" action="<?= home_url('/personnel-panel/upload-document/' . $personnel_id) ?>" enctype="multipart/form-data">
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;font-weight:600;">Document Type</label>
                            <select name="doc_type" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;" required>
                                <option value="Contract">Employment Contract</option>
                                <option value="Certificate">Certificate</option>
                                <option value="ID">ID Copy</option>
                                <option value="Training">Training Certificate</option>
                                <option value="Review">Performance Review</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div style="margin-bottom:15px;">
                            <label style="display:block;margin-bottom:5px;font-weight:600;">Select File (Max 5MB)</label>
                            <input type="file" name="document_file" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;" required>
                        </div>
                        <div style="display:flex;gap:10px;">
                            <button type="submit" class="add-btn" style="background:#10b981;">Upload</button>
                            <button type="button" onclick="document.getElementById('uploadDocForm').style.display='none'" class="add-btn" style="background:#6b7280;">Cancel</button>
                        </div>
                    </form>
                </div>

                <!-- Documents List -->
                <?php if (empty($documents)): ?>
                    <p style="color:#6b7280;text-align:center;padding:40px 0;">No documents uploaded yet.</p>
                <?php else: ?>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:15px;">
                        <?php foreach ($documents as $index => $doc): ?>
                            <div style="background:#f9fafb;padding:15px;border-radius:8px;border:1px solid #e5e7eb;">
                                <div style="display:flex;justify-content:space-between;align-items:start;margin-bottom:10px;">
                                    <div style="background:#3b82f6;color:#fff;width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:18px;">
                                        <i class="fas fa-file-<?= $doc['type'] === 'pdf' ? 'pdf' : 'alt' ?>"></i>
                                    </div>
                                    <a href="<?= home_url('/personnel-panel/delete-document/' . $personnel_id . '?doc_index=' . $index) ?>" onclick="return confirm('Delete this document?')" style="color:#ef4444;"><i class="fas fa-trash"></i></a>
                                </div>
                                <h4 style="margin:0 0 5px 0;font-size:14px;font-weight:600;color:#111827;"><?= esc_html($doc['doc_type']) ?></h4>
                                <p style="margin:0 0 10px 0;font-size:13px;color:#6b7280;"><?= esc_html($doc['filename']) ?></p>
                                <div style="display:flex;justify-content:space-between;align-items:center;font-size:12px;color:#6b7280;">
                                    <span><?= date('M d, Y', strtotime($doc['upload_date'])) ?></span>
                                    <a href="<?= esc_url($doc['url']) ?>" download style="color:#3b82f6;"><i class="fas fa-download"></i> Download</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab Content: Attendance -->
        <div id="tab-attendance" class="tab-content" style="display:none;">
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;">Attendance History</h2>

                <?php if (empty($attendance)): ?>
                    <p style="color:#6b7280;text-align:center;padding:40px 0;">No attendance records yet.</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                                    <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Date & Time</th>
                                    <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Action</th>
                                    <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Hours</th>
                                    <th style="padding:12px;text-align:center;font-weight:600;color:#374151;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Group by date for hours calculation
                                $grouped = [];
                                foreach (array_reverse($attendance) as $record) {
                                    $date = date('Y-m-d', strtotime($record['date']));
                                    $grouped[$date][] = $record;
                                }
                                
                                $index = count($attendance) - 1;
                                foreach (array_reverse($attendance) as $record): 
                                    $is_clock_in = $record['type'] === 'clock_in';
                                ?>
                                    <tr style="border-bottom:1px solid #e5e7eb;">
                                        <td style="padding:12px;color:#111827;"><?= date('M d, Y g:i A', strtotime($record['date'])) ?></td>
                                        <td style="padding:12px;">
                                            <?php if ($is_clock_in): ?>
                                                <span style="background:#10b981;color:#fff;padding:4px 12px;border-radius:20px;font-size:13px;">
                                                    <i class="fas fa-sign-in-alt"></i> Clock In
                                                </span>
                                            <?php else: ?>
                                                <span style="background:#ef4444;color:#fff;padding:4px 12px;border-radius:20px;font-size:13px;">
                                                    <i class="fas fa-sign-out-alt"></i> Clock Out
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td style="padding:12px;color:#6b7280;">
                                            <?php
                                            // Calculate hours if this is a clock out
                                            if (!$is_clock_in) {
                                                $date = date('Y-m-d', strtotime($record['date']));
                                                if (isset($grouped[$date]) && count($grouped[$date]) >= 2) {
                                                    $clock_in_time = null;
                                                    foreach ($grouped[$date] as $r) {
                                                        if ($r['type'] === 'clock_in' && strtotime($r['date']) < strtotime($record['date'])) {
                                                            $clock_in_time = strtotime($r['date']);
                                                        }
                                                    }
                                                    if ($clock_in_time) {
                                                        $hours = round((strtotime($record['date']) - $clock_in_time) / 3600, 2);
                                                        echo $hours . ' hours';
                                                    }
                                                }
                                            }
                                            ?>
                                        </td>
                                        <td style="padding:12px;text-align:center;">
                                            <a href="<?= home_url('/personnel-panel/edit-attendance/' . $personnel_id . '/' . $index) ?>" style="color:#3b82f6;margin-right:10px;" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/delete-attendance/' . $personnel_id . '/' . $index) ?>" onclick="return confirm('Delete this attendance record?')" style="color:#ef4444;" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php 
                                    $index--;
                                endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab Content: Activity -->
        <div id="tab-activity" class="tab-content" style="display:none;">
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;">Activity Log</h2>

                <?php if (empty($activity)): ?>
                    <p style="color:#6b7280;text-align:center;padding:40px 0;">No activity recorded yet.</p>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:15px;">
                        <?php foreach ($activity as $log): ?>
                            <div style="display:flex;gap:15px;padding:15px;background:#f9fafb;border-radius:8px;border-left:4px solid <?= b2b_activity_color($log['action']) ?>;">
                                <div style="background:<?= b2b_activity_color($log['action']) ?>;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <i class="fas fa-<?= b2b_activity_icon($log['action']) ?>"></i>
                                </div>
                                <div style="flex:1;">
                                    <h4 style="margin:0 0 5px 0;font-size:14px;font-weight:600;color:#111827;"><?= esc_html(ucwords(str_replace('_', ' ', $log['action']))) ?></h4>
                                    <p style="margin:0 0 5px 0;color:#374151;font-size:14px;"><?= esc_html($log['details']) ?></p>
                                    <p style="margin:0;font-size:13px;color:#6b7280;"><?= date('M d, Y g:i A', strtotime($log['date'])) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tab Content: Payments -->
        <div id="tab-payments" class="tab-content" style="display:none;">
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:25px;">
                    <h2 style="margin:0;font-size:20px;color:#111827;">Payment History</h2>
                    <a href="<?= home_url('/personnel-panel/add-payment/' . $personnel_id) ?>" style="background:#10b981;color:#fff;padding:10px 20px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:14px;font-weight:600;">
                        <i class="fas fa-plus"></i> Add Payment
                    </a>
                </div>

                <?php
                $current_month = date('Y-m');
                $accrual = b2b_calculate_monthly_accrual($personnel_id, $current_month);
                $payments = b2b_calculate_monthly_payments($personnel_id, $current_month);
                $balance = b2b_get_payment_balance($personnel_id);
                ?>

                <!-- Summary Cards -->
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px;">
                    <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:12px;padding:20px;color:#fff;">
                        <div style="font-size:13px;opacity:0.9;margin-bottom:5px;">Accrued (This Month)</div>
                        <div style="font-size:28px;font-weight:700;">$<?= number_format($accrual, 2) ?></div>
                    </div>
                    <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:12px;padding:20px;color:#fff;">
                        <div style="font-size:13px;opacity:0.9;margin-bottom:5px;">Paid (This Month)</div>
                        <div style="font-size:28px;font-weight:700;">$<?= number_format($payments, 2) ?></div>
                    </div>
                    <div style="background:linear-gradient(135deg,<?= $balance > 0 ? '#ef4444,#dc2626' : '#10b981,#059669' ?>);border-radius:12px;padding:20px;color:#fff;">
                        <div style="font-size:13px;opacity:0.9;margin-bottom:5px;">Balance</div>
                        <div style="font-size:28px;font-weight:700;">$<?= number_format($balance, 2) ?></div>
                    </div>
                </div>

                <!-- Payment History Table -->
                <?php
                $payment_records = get_post_meta($personnel_id, '_payment_records', true) ?: [];
                if (empty($payment_records)): ?>
                    <p style="color:#6b7280;text-align:center;padding:40px 0;">No payments recorded yet.</p>
                <?php else:
                    // Sort by date (newest first)
                    usort($payment_records, function($a, $b) {
                        return strtotime($b['payment_date']) - strtotime($a['payment_date']);
                    });
                ?>
                    <div style="overflow-x:auto;">
                        <table style="width:100%;border-collapse:collapse;">
                            <thead>
                                <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                                    <th style="padding:12px;text-align:left;font-size:13px;color:#6b7280;font-weight:600;">Date</th>
                                    <th style="padding:12px;text-align:left;font-size:13px;color:#6b7280;font-weight:600;">Type</th>
                                    <th style="padding:12px;text-align:left;font-size:13px;color:#6b7280;font-weight:600;">Amount</th>
                                    <th style="padding:12px;text-align:left;font-size:13px;color:#6b7280;font-weight:600;">Method</th>
                                    <th style="padding:12px;text-align:left;font-size:13px;color:#6b7280;font-weight:600;">Reference</th>
                                    <th style="padding:12px;text-align:left;font-size:13px;color:#6b7280;font-weight:600;">Balance After</th>
                                    <th style="padding:12px;text-align:center;font-size:13px;color:#6b7280;font-weight:600;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_records as $payment): 
                                    $payment_type = isset($payment['payment_type']) ? $payment['payment_type'] : 'salary';
                                    $type_colors = [
                                        'salary' => '#3b82f6',
                                        'bonus' => '#10b981',
                                        'commission' => '#8b5cf6',
                                        'allowance' => '#f59e0b',
                                        'reimbursement' => '#14b8a6',
                                        'adjustment' => '#6b7280',
                                        'other' => '#6b7280'
                                    ];
                                    $type_color = $type_colors[$payment_type] ?? '#6b7280';
                                ?>
                                    <tr style="border-bottom:1px solid #e5e7eb;">
                                        <td style="padding:12px;font-size:14px;color:#374151;"><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                        <td style="padding:12px;font-size:14px;">
                                            <span style="background:<?= $type_color ?>;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;">
                                                <?= ucfirst($payment_type) ?>
                                            </span>
                                        </td>
                                        <td style="padding:12px;font-size:14px;color:#374151;font-weight:600;">$<?= number_format($payment['amount'], 2) ?></td>
                                        <td style="padding:12px;font-size:14px;color:#374151;"><?= esc_html(ucwords(str_replace('_', ' ', $payment['payment_method']))) ?></td>
                                        <td style="padding:12px;font-size:14px;color:#374151;"><?= esc_html($payment['reference_number']) ?></td>
                                        <td style="padding:12px;font-size:14px;color:#374151;">$<?= number_format($payment['balance_after'], 2) ?></td>
                                        <td style="padding:12px;text-align:center;">
                                            <a href="<?= home_url('/personnel-panel/edit-payment/' . $payment['id']) ?>" style="color:#3b82f6;margin-right:10px;text-decoration:none;" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/delete-payment/' . $payment['id']) ?>" 
                                               onclick="return confirm('Are you sure you want to delete this payment? The balance will be recalculated.');" 
                                               style="color:#ef4444;text-decoration:none;" title="Delete">
                                                <i class="fas fa-trash"></i>
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
    </div>

    <script>
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.style.borderBottom = '3px solid transparent';
            btn.style.color = '#6b7280';
        });
        
        // Show selected tab
        document.getElementById('tab-' + tabName).style.display = 'block';
        event.target.style.borderBottom = '3px solid #3b82f6';
        event.target.style.color = '#3b82f6';
    }
    </script>

    <!-- Quick Actions Bar -->
    <div style="position:fixed;right:20px;top:50%;transform:translateY(-50%);display:flex;flex-direction:column;gap:12px;z-index:1000;">
        <?php if ($clocked_in): ?>
            <a href="<?= home_url('/personnel-panel/clock-out-form/' . $personnel_id) ?>" 
               class="quick-action-btn" 
               style="width:56px;height:56px;background:#ef4444;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 6px rgba(0,0,0,0.2);transition:all 0.3s;"
               title="Clock Out"
               onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
               onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.2)';">
                <i class="fas fa-sign-out-alt" style="font-size:20px;"></i>
            </a>
        <?php else: ?>
            <a href="<?= home_url('/personnel-panel/clock-in-form/' . $personnel_id) ?>" 
               class="quick-action-btn" 
               style="width:56px;height:56px;background:#10b981;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 6px rgba(0,0,0,0.2);transition:all 0.3s;"
               title="Clock In"
               onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
               onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.2)';">
                <i class="fas fa-sign-in-alt" style="font-size:20px;"></i>
            </a>
        <?php endif; ?>
        <a href="<?= home_url('/personnel-panel/request-leave/' . $personnel_id) ?>" 
           class="quick-action-btn" 
           style="width:56px;height:56px;background:#0ea5e9;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 6px rgba(0,0,0,0.2);transition:all 0.3s;"
           title="Request Leave"
           onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.2)';">
            <i class="fas fa-calendar-check" style="font-size:20px;"></i>
        </a>
        <a href="<?= home_url('/personnel-panel/add-payment/' . $personnel_id) ?>" 
           class="quick-action-btn" 
           style="width:56px;height:56px;background:#10b981;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 6px rgba(0,0,0,0.2);transition:all 0.3s;"
           title="Add Payment"
           onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.2)';">
            <i class="fas fa-dollar-sign" style="font-size:20px;"></i>
        </a>
        <a href="#" 
           onclick="document.querySelector('.tab-btn:nth-child(2)').click();setTimeout(function(){document.querySelector('#addNoteForm textarea').focus();},100);return false;"
           class="quick-action-btn" 
           style="width:56px;height:56px;background:#8b5cf6;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 6px rgba(0,0,0,0.2);transition:all 0.3s;"
           title="Add Note"
           onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.2)';">
            <i class="fas fa-sticky-note" style="font-size:20px;"></i>
        </a>
        <a href="#" 
           onclick="document.querySelector('.tab-btn:nth-child(3)').click();setTimeout(function(){document.querySelector('#uploadDocForm input[type=file]').click();},100);return false;"
           class="quick-action-btn" 
           style="width:56px;height:56px;background:#06b6d4;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 6px rgba(0,0,0,0.2);transition:all 0.3s;"
           title="Upload Document"
           onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.2)';">
            <i class="fas fa-file-upload" style="font-size:20px;"></i>
        </a>
        <a href="<?= home_url('/personnel-panel/print-view/' . $personnel_id) ?>" 
           target="_blank"
           class="quick-action-btn" 
           style="width:56px;height:56px;background:#f59e0b;color:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;text-decoration:none;box-shadow:0 4px 6px rgba(0,0,0,0.2);transition:all 0.3s;"
           title="Print View"
           onmouseover="this.style.transform='scale(1.1)';this.style.boxShadow='0 6px 12px rgba(0,0,0,0.3)';"
           onmouseout="this.style.transform='scale(1)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.2)';">
            <i class="fas fa-print" style="font-size:20px;"></i>
        </a>
    </div>

    <style>
    @media (max-width: 768px) {
        .quick-action-btn {
            width: 48px !important;
            height: 48px !important;
        }
        .quick-action-btn i {
            font-size: 16px !important;
        }
        div[style*="position:fixed;right:20px"] {
            right: 10px !important;
            gap: 8px !important;
        }
    }
    </style>

    </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * 12. ATTENDANCE DASHBOARD PAGE
 * ===================================================== */
function b2b_personnel_attendance_page() {
    // Get all personnel
    $args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ];
    $personnel = get_posts($args);
    
    // Get selected date (default today)
    $selected_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Attendance Dashboard</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .add-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                background: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 500;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
            }
            .add-btn:hover { background: #2563eb; }
            .container {
                max-width: 1400px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                background: #f9fafb;
                padding: 0.75rem 1rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.875rem;
                color: #374151;
                border-bottom: 1px solid #e5e7eb;
            }
            td {
                padding: 1rem;
                border-bottom: 1px solid #e5e7eb;
                font-size: 0.875rem;
            }
            tr:hover { background: #f9fafb; }
            .badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 500;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-clock"></i> Attendance Dashboard</h1>
            <a href="<?= home_url('/b2b-panel') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Admin Panel
            </a>
        </div>
        <div class="container">
    
    <div class="main-content">
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
            <h1 style="margin:0;font-size:28px;color:#111827;">Attendance Dashboard</h1>
            <div style="display:flex;gap:10px;">
                <a href="<?= home_url('/personnel-panel/reports') ?>" class="add-btn" style="background:#8b5cf6;">
                    <i class="fas fa-chart-bar"></i> Reports
                </a>
                <a href="<?= home_url('/personnel-panel') ?>" class="add-btn" style="background:#6b7280;">
                    <i class="fas fa-arrow-left"></i> Back to Personnel
                </a>
            </div>
        </div>

        <!-- Date Selector -->
        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
            <form method="GET" style="display:flex;gap:15px;align-items:end;flex-wrap:wrap;">
                <div style="flex:1;max-width:300px;">
                    <label style="display:block;margin-bottom:5px;font-weight:600;color:#374151;">Select Date</label>
                    <input type="date" name="date" value="<?= esc_attr($selected_date) ?>" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
                </div>
                <button type="submit" class="add-btn">View Attendance</button>
                <a href="?date=<?= date('Y-m-d') ?>" class="add-btn" style="background:#6b7280;">Today</a>
            </form>
        </div>

        <!-- Attendance Summary -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px;">
            <?php
            $total = count($personnel);
            $present = 0;
            $absent = 0;
            
            foreach ($personnel as $person) {
                $attendance = get_post_meta($person->ID, '_attendance', true) ?: [];
                $is_present = false;
                foreach ($attendance as $record) {
                    if (strpos($record['date'], $selected_date) === 0 && $record['type'] === 'clock_in') {
                        $is_present = true;
                        break;
                    }
                }
                if ($is_present) $present++; else $absent++;
            }
            ?>
            
            <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:12px;padding:20px;color:#fff;">
                <div style="font-size:32px;font-weight:700;margin-bottom:5px;"><?= $total ?></div>
                <div style="font-size:14px;opacity:0.9;">Total Personnel</div>
            </div>
            
            <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:12px;padding:20px;color:#fff;">
                <div style="font-size:32px;font-weight:700;margin-bottom:5px;"><?= $present ?></div>
                <div style="font-size:14px;opacity:0.9;">Present Today</div>
            </div>
            
            <div style="background:linear-gradient(135deg,#ef4444 0%,#dc2626 100%);border-radius:12px;padding:20px;color:#fff;">
                <div style="font-size:32px;font-weight:700;margin-bottom:5px;"><?= $absent ?></div>
                <div style="font-size:14px;opacity:0.9;">Absent Today</div>
            </div>
            
            <div style="background:linear-gradient(135deg,#f59e0b 0%,#d97706 100%);border-radius:12px;padding:20px;color:#fff;">
                <div style="font-size:32px;font-weight:700;margin-bottom:5px;"><?= $total > 0 ? round(($present / $total) * 100) : 0 ?>%</div>
                <div style="font-size:14px;opacity:0.9;">Attendance Rate</div>
            </div>
        </div>

        <!-- Personnel Attendance List -->
        <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;">Personnel Status</h2>
            
            <div class="table-responsive">
                <table style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f9fafb;border-bottom:2px solid #e5e7eb;">
                            <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Name</th>
                            <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Department</th>
                            <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Status</th>
                            <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Clock In</th>
                            <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Clock Out</th>
                            <th style="padding:12px;text-align:left;font-weight:600;color:#374151;">Hours</th>
                            <th style="padding:12px;text-align:center;font-weight:600;color:#374151;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($personnel as $person): 
                            $attendance = get_post_meta($person->ID, '_attendance', true) ?: [];
                            $clock_in_time = '';
                            $clock_out_time = '';
                            $hours_worked = 0;
                            $is_clocked_in = false;
                            
                            foreach ($attendance as $record) {
                                if (strpos($record['date'], $selected_date) === 0) {
                                    if ($record['type'] === 'clock_in') {
                                        $clock_in_time = date('g:i A', strtotime($record['date']));
                                        $is_clocked_in = true;
                                    } elseif ($record['type'] === 'clock_out') {
                                        $clock_out_time = date('g:i A', strtotime($record['date']));
                                        $is_clocked_in = false;
                                        // Calculate hours
                                        if ($clock_in_time) {
                                            $in = strtotime($selected_date . ' ' . $clock_in_time);
                                            $out = strtotime($selected_date . ' ' . $clock_out_time);
                                            $hours_worked = round(($out - $in) / 3600, 2);
                                        }
                                    }
                                }
                            }
                            
                            $depts = get_the_terms($person->ID, 'b2b_departman');
                            $dept_name = $depts && !is_wp_error($depts) ? $depts[0]->name : 'N/A';
                        ?>
                            <tr style="border-bottom:1px solid #e5e7eb;">
                                <td style="padding:12px;">
                                    <a href="<?= home_url('/personnel-panel/view/' . $person->ID) ?>" style="color:#3b82f6;font-weight:500;">
                                        <?= esc_html($person->post_title) ?>
                                    </a>
                                </td>
                                <td style="padding:12px;color:#6b7280;"><?= esc_html($dept_name) ?></td>
                                <td style="padding:12px;">
                                    <?php if ($clock_in_time && !$clock_out_time): ?>
                                        <span style="background:#10b981;color:#fff;padding:4px 12px;border-radius:20px;font-size:13px;">
                                            <i class="fas fa-circle" style="font-size:8px;"></i> Present
                                        </span>
                                    <?php elseif ($clock_in_time && $clock_out_time): ?>
                                        <span style="background:#3b82f6;color:#fff;padding:4px 12px;border-radius:20px;font-size:13px;">
                                            <i class="fas fa-check-circle"></i> Completed
                                        </span>
                                    <?php else: ?>
                                        <span style="background:#6b7280;color:#fff;padding:4px 12px;border-radius:20px;font-size:13px;">
                                            <i class="fas fa-times-circle"></i> Absent
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding:12px;color:#111827;"><?= $clock_in_time ?: '-' ?></td>
                                <td style="padding:12px;color:#111827;"><?= $clock_out_time ?: '-' ?></td>
                                <td style="padding:12px;color:#111827;font-weight:600;"><?= $hours_worked > 0 ? $hours_worked . ' hrs' : '-' ?></td>
                                <td style="padding:12px;text-align:center;">
                                    <?php if (!$clock_in_time): ?>
                                        <a href="<?= home_url('/personnel-panel/clock-in-form/' . $person->ID) ?>" class="btn btn-edit" style="background:#10b981;padding:0.375rem 0.75rem;font-size:0.75rem;">
                                            <i class="fas fa-sign-in-alt"></i> Clock In
                                        </a>
                                    <?php elseif ($is_clocked_in): ?>
                                        <a href="<?= home_url('/personnel-panel/clock-out-form/' . $person->ID) ?>" class="btn btn-delete" style="padding:0.375rem 0.75rem;font-size:0.75rem;">
                                            <i class="fas fa-sign-out-alt"></i> Clock Out
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#6b7280;font-size:13px;">Completed</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * 13. CLOCK IN/OUT ACTIONS
 * ===================================================== */
function b2b_personnel_clock_in() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');
    
    $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
    $attendance[] = [
        'type' => 'clock_in',
        'date' => date('Y-m-d H:i:s'),
        'user_id' => get_current_user_id()
    ];
    update_post_meta($personnel_id, '_attendance', $attendance);
    
    // Log activity
    b2b_log_personnel_activity($personnel_id, 'clock_in', 'Clocked in');
    
    wp_redirect(home_url('/personnel-panel/attendance?date=' . $date));
    exit;
}

function b2b_personnel_clock_out() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : date('Y-m-d');
    
    $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
    $attendance[] = [
        'type' => 'clock_out',
        'date' => date('Y-m-d H:i:s'),
        'user_id' => get_current_user_id()
    ];
    update_post_meta($personnel_id, '_attendance', $attendance);
    
    // Log activity
    b2b_log_personnel_activity($personnel_id, 'clock_out', 'Clocked out');
    
    wp_redirect(home_url('/personnel-panel/attendance?date=' . $date));
    exit;
}

/* =====================================================
 * 14. ACTIVITY LOG PAGE
 * ===================================================== */
function b2b_personnel_activity_page() {
    // Get filter parameters
    $filter_action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
    $filter_date = isset($_GET['date']) ? sanitize_text_field($_GET['date']) : '';
    
    // Get all activity
    $all_activity = [];
    $args = ['post_type' => 'b2b_personel', 'posts_per_page' => -1];
    $personnel = get_posts($args);
    
    foreach ($personnel as $person) {
        $activity = get_post_meta($person->ID, '_activity_log', true) ?: [];
        foreach ($activity as $log) {
            $log['personnel_id'] = $person->ID;
            $log['personnel_name'] = $person->post_title;
            $all_activity[] = $log;
        }
    }
    
    // Sort by date (newest first)
    usort($all_activity, function($a, $b) {
        $a_date = isset($a['date']) ? $a['date'] : '';
        $b_date = isset($b['date']) ? $b['date'] : '';
        return strtotime($b_date) - strtotime($a_date);
    });
    
    // Apply filters
    if ($filter_action) {
        $all_activity = array_filter($all_activity, function($log) use ($filter_action) {
            return $log['action'] === $filter_action;
        });
    }
    if ($filter_date) {
        $all_activity = array_filter($all_activity, function($log) use ($filter_date) {
            return strpos($log['date'], $filter_date) === 0;
        });
    }
    
    // Pagination
    $per_page = 50;
    $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $total_items = count($all_activity);
    $total_pages = ceil($total_items / $per_page);
    $offset = ($page - 1) * $per_page;
    $activity_page = array_slice($all_activity, $offset, $per_page);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Activity Log</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .add-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.625rem 1.25rem;
                background: #3b82f6;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-weight: 500;
                font-size: 0.875rem;
                border: none;
                cursor: pointer;
            }
            .add-btn:hover { background: #2563eb; }
            .container {
                max-width: 1400px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                background: #f9fafb;
                padding: 0.75rem 1rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.875rem;
                color: #374151;
                border-bottom: 1px solid #e5e7eb;
            }
            td {
                padding: 1rem;
                border-bottom: 1px solid #e5e7eb;
                font-size: 0.875rem;
            }
            tr:hover { background: #f9fafb; }
            .badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 500;
            }
            select, input {
                padding: 0.5rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-history"></i> Activity Log</h1>
            <a href="<?= home_url('/b2b-panel') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Admin Panel
            </a>
        </div>
        <div class="container">
    
    <div class="main-content">
    
    <div class="main-content">
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h1 style="margin:0;font-size:28px;color:#111827;">Activity Log</h1>
            <a href="<?= home_url('/personnel-panel') ?>" class="add-btn" style="background:#6b7280;">
                <i class="fas fa-arrow-left"></i> Back to Personnel
            </a>
        </div>

        <!-- Filters -->
        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
            <form method="GET" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;">
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:600;color:#374151;">Action Type</label>
                    <select name="action" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
                        <option value="">All Actions</option>
                        <option value="personnel_added" <?= $filter_action === 'personnel_added' ? 'selected' : '' ?>>Personnel Added</option>
                        <option value="personnel_edited" <?= $filter_action === 'personnel_edited' ? 'selected' : '' ?>>Personnel Edited</option>
                        <option value="personnel_deleted" <?= $filter_action === 'personnel_deleted' ? 'selected' : '' ?>>Personnel Deleted</option>
                        <option value="clock_in" <?= $filter_action === 'clock_in' ? 'selected' : '' ?>>Clock In</option>
                        <option value="clock_out" <?= $filter_action === 'clock_out' ? 'selected' : '' ?>>Clock Out</option>
                        <option value="note_added" <?= $filter_action === 'note_added' ? 'selected' : '' ?>>Note Added</option>
                        <option value="document_uploaded" <?= $filter_action === 'document_uploaded' ? 'selected' : '' ?>>Document Uploaded</option>
                    </select>
                </div>
                <div>
                    <label style="display:block;margin-bottom:5px;font-weight:600;color:#374151;">Date</label>
                    <input type="date" name="date" value="<?= esc_attr($filter_date) ?>" style="width:100%;padding:10px;border:1px solid #d1d5db;border-radius:8px;">
                </div>
                <div style="display:flex;align-items:end;gap:10px;">
                    <button type="submit" class="add-btn">Filter</button>
                    <a href="<?= home_url('/personnel-panel/activity') ?>" class="add-btn" style="background:#6b7280;">Reset</a>
                </div>
            </form>
        </div>

        <!-- Summary -->
        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
            <p style="margin:0;color:#6b7280;">Showing <?= number_format(count($activity_page)) ?> of <?= number_format($total_items) ?> activity records</p>
        </div>

        <!-- Activity List -->
        <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
            <?php if (empty($activity_page)): ?>
                <p style="color:#6b7280;text-align:center;padding:40px 0;">No activity found.</p>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:15px;">
                    <?php foreach ($activity_page as $log): ?>
                        <div style="display:flex;gap:15px;padding:15px;background:#f9fafb;border-radius:8px;border-left:4px solid <?= b2b_activity_color($log['action']) ?>;">
                            <div style="background:<?= b2b_activity_color($log['action']) ?>;color:#fff;width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                <i class="fas fa-<?= b2b_activity_icon($log['action']) ?>"></i>
                            </div>
                            <div style="flex:1;">
                                <h4 style="margin:0 0 5px 0;font-size:14px;font-weight:600;color:#111827;">
                                    <a href="<?= home_url('/personnel-panel/view/' . $log['personnel_id']) ?>" style="color:#3b82f6;">
                                        <?= esc_html($log['personnel_name']) ?>
                                    </a>
                                    • <?= esc_html(ucwords(str_replace('_', ' ', $log['action']))) ?>
                                </h4>
                                <p style="margin:0 0 5px 0;color:#374151;font-size:14px;"><?= esc_html($log['details']) ?></p>
                                <p style="margin:0;font-size:13px;color:#6b7280;">
                                    <?= date('M d, Y g:i A', strtotime($log['date'])) ?>
                                    <?php if (isset($log['user_id'])): 
                                        $user = get_userdata($log['user_id']);
                                        $user_name = $user ? $user->display_name : 'System';
                                    ?>
                                        • by <?= esc_html($user_name) ?>
                                    <?php endif; ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div style="margin-top:20px;display:flex;justify-content:center;align-items:center;gap:10px;">
                        <span style="color:#6b7280;font-size:14px;">Page:</span>
                        <select onchange="window.location.href=this.value" style="padding:8px 12px;border:1px solid #d1d5db;border-radius:8px;background:#fff;cursor:pointer;">
                            <?php for ($i = 1; $i <= $total_pages; $i++): 
                                $page_url = add_query_arg(['paged' => $i, 'action' => $filter_action, 'date' => $filter_date], home_url('/personnel-panel/activity'));
                            ?>
                                <option value="<?= esc_url($page_url) ?>" <?= $i === $page ? 'selected' : '' ?>>
                                    Page <?= $i ?> of <?= $total_pages ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * 15. NOTE MANAGEMENT
 * ===================================================== */
function b2b_personnel_add_note() {
    $personnel_id = intval(get_query_var('personnel_id'));
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['note_content'])) {
        $notes = get_post_meta($personnel_id, '_notes', true) ?: [];
        $current_user = wp_get_current_user();
        
        $notes[] = [
            'content' => sanitize_textarea_field($_POST['note_content']),
            'author' => $current_user->display_name,
            'user_id' => get_current_user_id(),
            'date' => current_time('mysql')
        ];
        
        update_post_meta($personnel_id, '_notes', $notes);
        
        // Log activity
        b2b_log_personnel_activity($personnel_id, 'note_added', 'Note added: ' . substr(sanitize_textarea_field($_POST['note_content']), 0, 50) . '...');
    }
    
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id));
    exit;
}

function b2b_personnel_delete_note() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $note_index = isset($_GET['note_index']) ? intval($_GET['note_index']) : -1;
    
    if ($note_index >= 0) {
        $notes = get_post_meta($personnel_id, '_notes', true) ?: [];
        if (isset($notes[$note_index])) {
            unset($notes[$note_index]);
            $notes = array_values($notes); // Re-index
            update_post_meta($personnel_id, '_notes', $notes);
            
            // Log activity
            b2b_log_personnel_activity($personnel_id, 'note_deleted', 'Note was deleted');
        }
    }
    
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id));
    exit;
}

/* =====================================================
 * 16. DOCUMENT MANAGEMENT
 * ===================================================== */
function b2b_personnel_upload_document() {
    $personnel_id = intval(get_query_var('personnel_id'));
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['document_file']) && isset($_POST['doc_type'])) {
        $file = $_FILES['document_file'];
        
        // Validate file
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        if ($file['error'] === 0 && in_array($file['type'], $allowed_types) && $file['size'] <= $max_size) {
            $upload_dir = wp_upload_dir();
            $personnel_dir = $upload_dir['basedir'] . '/personnel-documents/' . $personnel_id;
            
            if (!file_exists($personnel_dir)) {
                wp_mkdir_p($personnel_dir);
            }
            
            $filename = sanitize_file_name($file['name']);
            $unique_filename = wp_unique_filename($personnel_dir, $filename);
            $file_path = $personnel_dir . '/' . $unique_filename;
            
            if (move_uploaded_file($file['tmp_name'], $file_path)) {
                $documents = get_post_meta($personnel_id, '_documents', true) ?: [];
                
                $documents[] = [
                    'filename' => $unique_filename,
                    'doc_type' => sanitize_text_field($_POST['doc_type']),
                    'upload_date' => current_time('mysql'),
                    'user_id' => get_current_user_id(),
                    'url' => $upload_dir['baseurl'] . '/personnel-documents/' . $personnel_id . '/' . $unique_filename,
                    'size' => $file['size'],
                    'type' => pathinfo($unique_filename, PATHINFO_EXTENSION)
                ];
                
                update_post_meta($personnel_id, '_documents', $documents);
                
                // Log activity
                b2b_log_personnel_activity($personnel_id, 'document_uploaded', 'Document uploaded: ' . $unique_filename);
            }
        }
    }
    
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id));
    exit;
}

function b2b_personnel_delete_document() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $doc_index = isset($_GET['doc_index']) ? intval($_GET['doc_index']) : -1;
    
    if ($doc_index >= 0) {
        $documents = get_post_meta($personnel_id, '_documents', true) ?: [];
        if (isset($documents[$doc_index])) {
            // Delete physical file
            $upload_dir = wp_upload_dir();
            $file_path = $upload_dir['basedir'] . '/personnel-documents/' . $personnel_id . '/' . $documents[$doc_index]['filename'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
            
            unset($documents[$doc_index]);
            $documents = array_values($documents); // Re-index
            update_post_meta($personnel_id, '_documents', $documents);
            
            // Log activity
            b2b_log_personnel_activity($personnel_id, 'document_deleted', 'Document was deleted');
        }
    }
    
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id));
    exit;
}

/* =====================================================
 * 17. ACTIVITY LOGGING HELPERS
 * ===================================================== */
function b2b_log_personnel_activity($personnel_id, $action, $details, $changes = null) {
    $activity = get_post_meta($personnel_id, '_activity_log', true) ?: [];
    
    $log_entry = [
        'action' => $action,
        'details' => $details,
        'user_id' => get_current_user_id(),
        'date' => current_time('mysql')
    ];
    
    // Add field-level changes if provided
    if ($changes && is_array($changes)) {
        $log_entry['changes'] = $changes;
    }
    
    $activity[] = $log_entry;
    
    // Keep only last 100 entries
    if (count($activity) > 100) {
        $activity = array_slice($activity, -100);
    }
    
    update_post_meta($personnel_id, '_activity_log', $activity);
}

function b2b_get_personnel_activity($personnel_id, $limit = 10) {
    $activity = get_post_meta($personnel_id, '_activity_log', true) ?: [];
    return array_slice(array_reverse($activity), 0, $limit);
}

function b2b_activity_color($action) {
    $colors = [
        'personnel_added' => '#10b981',
        'personnel_edited' => '#3b82f6',
        'personnel_deleted' => '#ef4444',
        'clock_in' => '#10b981',
        'clock_out' => '#f59e0b',
        'note_added' => '#8b5cf6',
        'note_deleted' => '#6b7280',
        'document_uploaded' => '#06b6d4',
        'document_deleted' => '#6b7280',
    ];
    return $colors[$action] ?? '#6b7280';
}

function b2b_activity_icon($action) {
    $icons = [
        'personnel_added' => 'user-plus',
        'personnel_edited' => 'user-edit',
        'personnel_deleted' => 'user-times',
        'clock_in' => 'sign-in-alt',
        'clock_out' => 'sign-out-alt',
        'note_added' => 'sticky-note',
        'note_deleted' => 'trash',
        'document_uploaded' => 'file-upload',
        'document_deleted' => 'file-times',
    ];
    return $icons[$action] ?? 'info-circle';
}

/* =====================================================
 * 18. FLUSH REWRITE RULES ON ACTIVATION
 * ===================================================== */
register_activation_hook(__FILE__, 'b2b_personnel_flush_rewrites');
function b2b_personnel_flush_rewrites() {
    b2b_register_personnel_post_type();
    b2b_personnel_rewrite_rules();
    flush_rewrite_rules();
}

/* =====================================================
 * 19. CLOCK IN/OUT FORMS WITH MANUAL TIME ENTRY
 * ===================================================== */
function b2b_personnel_clock_in_form() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $person = get_post($personnel_id);
    
    if (!$person || $person->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Clock In - <?= esc_html($person->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .container {
                max-width: 600px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            .form-card {
                background: white;
                border-radius: 8px;
                padding: 2rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .form-group {
                margin-bottom: 1.5rem;
            }
            label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                font-size: 0.875rem;
                color: #374151;
            }
            input[type="date"],
            input[type="number"],
            select {
                width: 100%;
                padding: 0.625rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            input:focus, select:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            .time-inputs {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            .btn {
                padding: 0.625rem 1.5rem;
                border: none;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .btn-primary {
                background: #10b981;
                color: white;
            }
            .btn-primary:hover { background: #059669; }
            .btn-secondary {
                background: #e5e7eb;
                color: #374151;
            }
            .btn-secondary:hover { background: #d1d5db; }
            .form-actions {
                display: flex;
                gap: 1rem;
                margin-top: 2rem;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-sign-in-alt"></i> Clock In</h1>
            <a href="<?= home_url('/personnel-panel/attendance') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Attendance
            </a>
        </div>
        
        <div class="container">
            <div class="form-card">
                <h2 style="margin-bottom:1rem;font-size:1.25rem;">Clock In: <?= esc_html($person->post_title) ?></h2>
                <p style="color:#6b7280;margin-bottom:1.5rem;">Enter the clock in time for this personnel.</p>
                
                <form method="POST" action="<?= home_url('/personnel-panel/process-clock-in') ?>">
                    <input type="hidden" name="personnel_id" value="<?= $personnel_id ?>">
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" name="clock_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Time (24-hour format)</label>
                        <div class="time-inputs">
                            <div>
                                <label style="font-size:0.75rem;color:#6b7280;">Hour (0-23)</label>
                                <input type="number" name="clock_hour" min="0" max="23" value="<?= date('H') ?>" required>
                            </div>
                            <div>
                                <label style="font-size:0.75rem;color:#6b7280;">Minute (0-59)</label>
                                <input type="number" name="clock_minute" min="0" max="59" value="<?= date('i') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Clock In
                        </button>
                        <a href="<?= home_url('/personnel-panel/attendance') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function b2b_personnel_clock_out_form() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $person = get_post($personnel_id);
    
    if (!$person || $person->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Clock Out - <?= esc_html($person->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .container {
                max-width: 600px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            .form-card {
                background: white;
                border-radius: 8px;
                padding: 2rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .form-group {
                margin-bottom: 1.5rem;
            }
            label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                font-size: 0.875rem;
                color: #374151;
            }
            input[type="date"],
            input[type="number"] {
                width: 100%;
                padding: 0.625rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            input:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            .time-inputs {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            .btn {
                padding: 0.625rem 1.5rem;
                border: none;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .btn-primary {
                background: #ef4444;
                color: white;
            }
            .btn-primary:hover { background: #dc2626; }
            .btn-secondary {
                background: #e5e7eb;
                color: #374151;
            }
            .btn-secondary:hover { background: #d1d5db; }
            .form-actions {
                display: flex;
                gap: 1rem;
                margin-top: 2rem;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-sign-out-alt"></i> Clock Out</h1>
            <a href="<?= home_url('/personnel-panel/attendance') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Attendance
            </a>
        </div>
        
        <div class="container">
            <div class="form-card">
                <h2 style="margin-bottom:1rem;font-size:1.25rem;">Clock Out: <?= esc_html($person->post_title) ?></h2>
                <p style="color:#6b7280;margin-bottom:1.5rem;">Enter the clock out time for this personnel.</p>
                
                <form method="POST" action="<?= home_url('/personnel-panel/process-clock-out') ?>">
                    <input type="hidden" name="personnel_id" value="<?= $personnel_id ?>">
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" name="clock_date" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Time (24-hour format)</label>
                        <div class="time-inputs">
                            <div>
                                <label style="font-size:0.75rem;color:#6b7280;">Hour (0-23)</label>
                                <input type="number" name="clock_hour" min="0" max="23" value="<?= date('H') ?>" required>
                            </div>
                            <div>
                                <label style="font-size:0.75rem;color:#6b7280;">Minute (0-59)</label>
                                <input type="number" name="clock_minute" min="0" max="59" value="<?= date('i') ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Clock Out
                        </button>
                        <a href="<?= home_url('/personnel-panel/attendance') ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function b2b_personnel_process_clock_in() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $personnel_id = intval($_POST['personnel_id']);
        $date = sanitize_text_field($_POST['clock_date']);
        $hour = str_pad(intval($_POST['clock_hour']), 2, '0', STR_PAD_LEFT);
        $minute = str_pad(intval($_POST['clock_minute']), 2, '0', STR_PAD_LEFT);
        
        $datetime = $date . ' ' . $hour . ':' . $minute . ':00';
        
        $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
        $attendance[] = [
            'type' => 'clock_in',
            'date' => $datetime,
            'user_id' => get_current_user_id()
        ];
        update_post_meta($personnel_id, '_attendance', $attendance);
        
        b2b_log_personnel_activity($personnel_id, 'clock_in', 'Clocked in at ' . date('g:i A', strtotime($datetime)));
    }
    
    wp_redirect(home_url('/personnel-panel/attendance'));
    exit;
}

function b2b_personnel_process_clock_out() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $personnel_id = intval($_POST['personnel_id']);
        $date = sanitize_text_field($_POST['clock_date']);
        $hour = str_pad(intval($_POST['clock_hour']), 2, '0', STR_PAD_LEFT);
        $minute = str_pad(intval($_POST['clock_minute']), 2, '0', STR_PAD_LEFT);
        
        $datetime = $date . ' ' . $hour . ':' . $minute . ':00';
        
        $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
        $attendance[] = [
            'type' => 'clock_out',
            'date' => $datetime,
            'user_id' => get_current_user_id()
        ];
        update_post_meta($personnel_id, '_attendance', $attendance);
        
        b2b_log_personnel_activity($personnel_id, 'clock_out', 'Clocked out at ' . date('g:i A', strtotime($datetime)));
    }
    
    wp_redirect(home_url('/personnel-panel/attendance'));
    exit;
}

/* =====================================================
 * 20. EDIT/DELETE ATTENDANCE RECORDS
 * ===================================================== */
function b2b_personnel_edit_attendance_form() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $attendance_index = intval(get_query_var('attendance_index'));
    $person = get_post($personnel_id);
    
    if (!$person || $person->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
    if (!isset($attendance[$attendance_index])) {
        wp_redirect(home_url('/personnel-panel/view/' . $personnel_id));
        exit;
    }
    
    $record = $attendance[$attendance_index];
    $datetime = strtotime($record['date']);
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Attendance - <?= esc_html($person->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .container {
                max-width: 600px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            .form-card {
                background: white;
                border-radius: 8px;
                padding: 2rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .form-group {
                margin-bottom: 1.5rem;
            }
            label {
                display: block;
                margin-bottom: 0.5rem;
                font-weight: 500;
                font-size: 0.875rem;
                color: #374151;
            }
            input[type="date"],
            input[type="number"],
            select {
                width: 100%;
                padding: 0.625rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            input:focus, select:focus {
                outline: none;
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
            .time-inputs {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
            .btn {
                padding: 0.625rem 1.5rem;
                border: none;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
            }
            .btn-primary {
                background: #3b82f6;
                color: white;
            }
            .btn-primary:hover { background: #2563eb; }
            .btn-secondary {
                background: #e5e7eb;
                color: #374151;
            }
            .btn-secondary:hover { background: #d1d5db; }
            .form-actions {
                display: flex;
                gap: 1rem;
                margin-top: 2rem;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-edit"></i> Edit Attendance Record</h1>
            <a href="<?= home_url('/personnel-panel/view/' . $personnel_id) ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Personnel
            </a>
        </div>
        
        <div class="container">
            <div class="form-card">
                <h2 style="margin-bottom:1rem;font-size:1.25rem;">Edit Attendance: <?= esc_html($person->post_title) ?></h2>
                
                <form method="POST" action="<?= home_url('/personnel-panel/update-attendance') ?>">
                    <input type="hidden" name="personnel_id" value="<?= $personnel_id ?>">
                    <input type="hidden" name="attendance_index" value="<?= $attendance_index ?>">
                    
                    <div class="form-group">
                        <label><i class="fas fa-clipboard-check"></i> Type</label>
                        <select name="attendance_type" required>
                            <option value="clock_in" <?= $record['type'] === 'clock_in' ? 'selected' : '' ?>>Clock In</option>
                            <option value="clock_out" <?= $record['type'] === 'clock_out' ? 'selected' : '' ?>>Clock Out</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" name="clock_date" value="<?= date('Y-m-d', $datetime) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-clock"></i> Time (24-hour format)</label>
                        <div class="time-inputs">
                            <div>
                                <label style="font-size:0.75rem;color:#6b7280;">Hour (0-23)</label>
                                <input type="number" name="clock_hour" min="0" max="23" value="<?= date('H', $datetime) ?>" required>
                            </div>
                            <div>
                                <label style="font-size:0.75rem;color:#6b7280;">Minute (0-59)</label>
                                <input type="number" name="clock_minute" min="0" max="59" value="<?= date('i', $datetime) ?>" required>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Attendance
                        </button>
                        <a href="<?= home_url('/personnel-panel/view/' . $personnel_id) ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function b2b_personnel_update_attendance() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $personnel_id = intval($_POST['personnel_id']);
        $attendance_index = intval($_POST['attendance_index']);
        $type = sanitize_text_field($_POST['attendance_type']);
        $date = sanitize_text_field($_POST['clock_date']);
        $hour = str_pad(intval($_POST['clock_hour']), 2, '0', STR_PAD_LEFT);
        $minute = str_pad(intval($_POST['clock_minute']), 2, '0', STR_PAD_LEFT);
        
        $datetime = $date . ' ' . $hour . ':' . $minute . ':00';
        
        $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
        if (isset($attendance[$attendance_index])) {
            $attendance[$attendance_index] = [
                'type' => $type,
                'date' => $datetime,
                'user_id' => get_current_user_id()
            ];
            update_post_meta($personnel_id, '_attendance', $attendance);
            
            b2b_log_personnel_activity($personnel_id, 'attendance_edited', 'Attendance record updated');
        }
    }
    
    wp_redirect(home_url('/personnel-panel/view/' . intval($_POST['personnel_id'])));
    exit;
}

function b2b_personnel_delete_attendance() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $attendance_index = intval(get_query_var('attendance_index'));
    
    $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
    if (isset($attendance[$attendance_index])) {
        unset($attendance[$attendance_index]);
        $attendance = array_values($attendance);
        update_post_meta($personnel_id, '_attendance', $attendance);
        
        b2b_log_personnel_activity($personnel_id, 'attendance_deleted', 'Attendance record deleted');
    }
    
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id));
    exit;
}

/* =====================================================
 * 21. COMPREHENSIVE REPORTS PAGE
 * ===================================================== */
function b2b_personnel_reports_page() {
    // Get selected month and year
    $selected_month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
    $selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    
    // Get all personnel
    $personnel = get_posts([
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ]);
    
    // Calculate working days in selected month
    $first_day = strtotime("$selected_year-$selected_month-01");
    $last_day = strtotime(date('Y-m-t', $first_day));
    $working_days = 0;
    
    for ($d = $first_day; $d <= $last_day; $d = strtotime('+1 day', $d)) {
        $day_of_week = date('N', $d);
        if ($day_of_week < 6) { // Monday-Friday
            $working_days++;
        }
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Attendance Reports</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .container {
                max-width: 1400px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            .card {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                margin-bottom: 1.5rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th {
                background: #f9fafb;
                padding: 0.75rem 1rem;
                text-align: left;
                font-weight: 600;
                font-size: 0.875rem;
                color: #374151;
                border-bottom: 1px solid #e5e7eb;
            }
            td {
                padding: 1rem;
                border-bottom: 1px solid #e5e7eb;
                font-size: 0.875rem;
            }
            tr:hover { background: #f9fafb; }
            select {
                padding: 0.5rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .btn {
                padding: 0.625rem 1.25rem;
                border: none;
                border-radius: 6px;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                text-decoration: none;
                background: #3b82f6;
                color: white;
            }
            .btn:hover { background: #2563eb; }
            .badge {
                display: inline-block;
                padding: 0.25rem 0.75rem;
                border-radius: 9999px;
                font-size: 0.75rem;
                font-weight: 500;
            }
            .badge-success { background: #d1fae5; color: #065f46; }
            .badge-warning { background: #fef3c7; color: #92400e; }
            .badge-danger { background: #fee2e2; color: #991b1b; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-chart-bar"></i> Attendance Reports</h1>
            <a href="<?= home_url('/personnel-panel') ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Personnel
            </a>
        </div>
        
        <div class="container">
            <!-- Period Selector -->
            <div class="card">
                <form method="GET" style="display:flex;gap:1rem;align-items:end;flex-wrap:wrap;">
                    <div>
                        <label style="display:block;margin-bottom:0.5rem;font-weight:500;color:#374151;">Month</label>
                        <select name="month">
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>" <?= $m == $selected_month ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:0.5rem;font-weight:500;color:#374151;">Year</label>
                        <select name="year">
                            <?php for ($y = date('Y') - 2; $y <= date('Y') + 1; $y++): ?>
                                <option value="<?= $y ?>" <?= $y == $selected_year ? 'selected' : '' ?>>
                                    <?= $y ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn">
                        <i class="fas fa-filter"></i> View Report
                    </button>
                </form>
            </div>
            
            <!-- Summary Stats -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;margin-bottom:1.5rem;">
                <div style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:8px;padding:1.5rem;color:#fff;">
                    <div style="font-size:2rem;font-weight:700;margin-bottom:0.5rem;"><?= $working_days ?></div>
                    <div style="font-size:0.875rem;opacity:0.9;">Working Days in Month</div>
                </div>
                <div style="background:linear-gradient(135deg,#10b981 0%,#059669 100%);border-radius:8px;padding:1.5rem;color:#fff;">
                    <div style="font-size:2rem;font-weight:700;margin-bottom:0.5rem;"><?= count($personnel) ?></div>
                    <div style="font-size:0.875rem;opacity:0.9;">Total Personnel</div>
                </div>
            </div>
            
            <!-- Detailed Report Table -->
            <div class="card">
                <h2 style="margin-bottom:1rem;font-size:1.25rem;">Monthly Report - <?= date('F Y', $first_day) ?></h2>
                
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Personnel Name</th>
                                <th>Department</th>
                                <th>Base Salary</th>
                                <th>Days Worked</th>
                                <th>Attendance Rate</th>
                                <th>Total Hours</th>
                                <th>Calculated Salary</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $person):
                                $person_id = $person->ID;
                                $base_salary = floatval(get_post_meta($person_id, '_maas', true));
                                $attendance = get_post_meta($person_id, '_attendance', true) ?: [];
                                
                                // Get department
                                $depts = get_the_terms($person_id, 'b2b_departman');
                                $dept_name = $depts && !is_wp_error($depts) ? $depts[0]->name : 'N/A';
                                
                                // Calculate days worked and total hours
                                $days_worked = [];
                                $total_hours = 0;
                                
                                foreach ($attendance as $record) {
                                    $record_time = strtotime($record['date']);
                                    if ($record_time >= $first_day && $record_time <= $last_day) {
                                        $day = date('Y-m-d', $record_time);
                                        if ($record['type'] === 'clock_in') {
                                            $days_worked[$day] = ['in' => $record_time];
                                        } elseif ($record['type'] === 'clock_out' && isset($days_worked[$day]['in'])) {
                                            $days_worked[$day]['out'] = $record_time;
                                            $hours = ($record_time - $days_worked[$day]['in']) / 3600;
                                            $total_hours += $hours;
                                        }
                                    }
                                }
                                
                                $days_count = count($days_worked);
                                $attendance_rate = $working_days > 0 ? ($days_count / $working_days) * 100 : 0;
                                $calculated_salary = $base_salary * ($attendance_rate / 100);
                                
                                // Determine badge color
                                if ($attendance_rate >= 90) {
                                    $badge_class = 'badge-success';
                                } elseif ($attendance_rate >= 70) {
                                    $badge_class = 'badge-warning';
                                } else {
                                    $badge_class = 'badge-danger';
                                }
                            ?>
                                <tr>
                                    <td>
                                        <a href="<?= home_url('/personnel-panel/view/' . $person_id) ?>" style="color:#3b82f6;font-weight:500;">
                                            <?= esc_html($person->post_title) ?>
                                        </a>
                                    </td>
                                    <td><?= esc_html($dept_name) ?></td>
                                    <td style="font-weight:600;">$<?= number_format($base_salary, 2) ?></td>
                                    <td><?= $days_count ?> / <?= $working_days ?> days</td>
                                    <td>
                                        <span class="badge <?= $badge_class ?>">
                                            <?= round($attendance_rate, 1) ?>%
                                        </span>
                                    </td>
                                    <td><?= round($total_hours, 2) ?> hrs</td>
                                    <td style="font-weight:600;color:#10b981;">$<?= number_format($calculated_salary, 2) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="background:#f9fafb;font-weight:600;">
                                <td colspan="2">TOTAL</td>
                                <td>
                                    $<?php
                                    $total_base = 0;
                                    foreach ($personnel as $p) {
                                        $total_base += floatval(get_post_meta($p->ID, '_maas', true));
                                    }
                                    echo number_format($total_base, 2);
                                    ?>
                                </td>
                                <td colspan="3"></td>
                                <td style="color:#10b981;">
                                    $<?php
                                    $total_calculated = 0;
                                    foreach ($personnel as $p) {
                                        $pid = $p->ID;
                                        $base_sal = floatval(get_post_meta($pid, '_maas', true));
                                        $att = get_post_meta($pid, '_attendance', true) ?: [];
                                        $dw = [];
                                        foreach ($att as $r) {
                                            $rt = strtotime($r['date']);
                                            if ($rt >= $first_day && $rt <= $last_day) {
                                                $d = date('Y-m-d', $rt);
                                                if ($r['type'] === 'clock_in') {
                                                    $dw[$d] = true;
                                                }
                                            }
                                        }
                                        $dc = count($dw);
                                        $ar = $working_days > 0 ? ($dc / $working_days) : 0;
                                        $total_calculated += $base_sal * $ar;
                                    }
                                    echo number_format($total_calculated, 2);
                                    ?>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            
            <!-- Weekly Breakdown -->
            <div class="card">
                <h2 style="margin-bottom:1rem;font-size:1.25rem;">Weekly Breakdown</h2>
                <p style="color:#6b7280;margin-bottom:1rem;">Average attendance per week in selected month</p>
                
                <?php
                // Calculate weekly stats
                $weeks = [];
                for ($d = $first_day; $d <= $last_day; $d = strtotime('+1 day', $d)) {
                    $week_num = date('W', $d);
                    if (!isset($weeks[$week_num])) {
                        $weeks[$week_num] = ['start' => $d, 'end' => $d, 'working_days' => 0];
                    }
                    $weeks[$week_num]['end'] = $d;
                    if (date('N', $d) < 6) {
                        $weeks[$week_num]['working_days']++;
                    }
                }
                ?>
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1rem;">
                    <?php foreach ($weeks as $week_num => $week_data): 
                        // Calculate attendance for this week
                        $week_attendance = 0;
                        $week_total = 0;
                        
                        foreach ($personnel as $p) {
                            $att = get_post_meta($p->ID, '_attendance', true) ?: [];
                            $present_days = [];
                            foreach ($att as $r) {
                                $rt = strtotime($r['date']);
                                if ($rt >= $week_data['start'] && $rt <= $week_data['end'] && $r['type'] === 'clock_in') {
                                    $present_days[date('Y-m-d', $rt)] = true;
                                }
                            }
                            $week_attendance += count($present_days);
                            $week_total += $week_data['working_days'];
                        }
                        
                        $week_rate = $week_total > 0 ? ($week_attendance / $week_total) * 100 : 0;
                    ?>
                        <div style="background:#f9fafb;border-radius:8px;padding:1rem;border-left:4px solid #3b82f6;">
                            <div style="font-size:0.875rem;color:#6b7280;margin-bottom:0.5rem;">
                                Week <?= $week_num ?>
                            </div>
                            <div style="font-size:1.5rem;font-weight:700;color:#111827;margin-bottom:0.5rem;">
                                <?= round($week_rate, 1) ?>%
                            </div>
                            <div style="font-size:0.75rem;color:#6b7280;">
                                <?= date('M d', $week_data['start']) ?> - <?= date('M d', $week_data['end']) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * PHOTO UPLOAD FUNCTIONALITY
 * ===================================================== */

/**
 * Photo Upload Form
 */
function b2b_personnel_upload_photo() {
    $personnel_id = get_query_var('personnel_id');
    $post = get_post($personnel_id);
    
    if (!$post || $post->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    $name = get_the_title($personnel_id);
    $current_photo = get_post_meta($personnel_id, '_photo_url', true);
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Upload Photo - <?= esc_html($name) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 2rem;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: white;
                border-radius: 12px;
                padding: 2rem;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            }
            h1 {
                color: #1f2937;
                margin-bottom: 1.5rem;
                font-size: 1.75rem;
                display: flex;
                align-items: center;
                gap: 0.75rem;
            }
            .current-photo {
                text-align: center;
                margin-bottom: 2rem;
                padding: 1.5rem;
                background: #f9fafb;
                border-radius: 8px;
            }
            .current-photo img {
                width: 150px;
                height: 150px;
                border-radius: 50%;
                object-fit: cover;
                border: 4px solid #667eea;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .current-photo .no-photo {
                width: 150px;
                height: 150px;
                margin: 0 auto;
                border-radius: 50%;
                background: #e5e7eb;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 4rem;
                color: #9ca3af;
            }
            .form-group {
                margin-bottom: 1.5rem;
            }
            label {
                display: block;
                margin-bottom: 0.5rem;
                color: #374151;
                font-weight: 600;
            }
            input[type="file"] {
                width: 100%;
                padding: 0.75rem;
                border: 2px dashed #d1d5db;
                border-radius: 8px;
                background: #f9fafb;
                cursor: pointer;
                transition: all 0.3s;
            }
            input[type="file"]:hover {
                border-color: #667eea;
                background: #f3f4f6;
            }
            .file-info {
                margin-top: 0.5rem;
                font-size: 0.875rem;
                color: #6b7280;
            }
            .buttons {
                display: flex;
                gap: 1rem;
                margin-top: 2rem;
            }
            button {
                flex: 1;
                padding: 0.875rem 1.5rem;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s;
            }
            .btn-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            }
            .btn-secondary {
                background: #f3f4f6;
                color: #374151;
            }
            .btn-secondary:hover {
                background: #e5e7eb;
            }
            .btn-danger {
                background: #ef4444;
                color: white;
            }
            .btn-danger:hover {
                background: #dc2626;
                transform: translateY(-2px);
                box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>
                <i class="fas fa-camera"></i>
                Upload Photo - <?= esc_html($name) ?>
            </h1>
            
            <div class="current-photo">
                <?php if ($current_photo): ?>
                    <img src="<?= esc_url($current_photo) ?>" alt="Current photo">
                    <p style="margin-top:1rem;color:#6b7280;">Current Photo</p>
                <?php else: ?>
                    <div class="no-photo">
                        <i class="fas fa-user"></i>
                    </div>
                    <p style="margin-top:1rem;color:#6b7280;">No photo uploaded</p>
                <?php endif; ?>
            </div>
            
            <form action="<?= home_url('/personnel-panel/process-photo-upload') ?>" method="POST" enctype="multipart/form-data">
                <?php wp_nonce_field('upload_photo_' . $personnel_id, 'photo_nonce'); ?>
                <input type="hidden" name="personnel_id" value="<?= $personnel_id ?>">
                
                <div class="form-group">
                    <label for="photo_file">
                        <i class="fas fa-image"></i> Select Photo
                    </label>
                    <input type="file" id="photo_file" name="photo_file" accept="image/jpeg,image/jpg,image/png,image/gif" required>
                    <div class="file-info">
                        <i class="fas fa-info-circle"></i>
                        Supported formats: JPG, JPEG, PNG, GIF | Max size: 2MB
                    </div>
                </div>
                
                <div class="buttons">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Upload Photo
                    </button>
                    <a href="<?= home_url('/personnel-panel/view/' . $personnel_id) ?>" class="btn-secondary" style="display:flex;align-items:center;justify-content:center;text-decoration:none;">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
            
            <?php if ($current_photo): ?>
                <form action="<?= home_url('/personnel-panel/delete-photo/' . $personnel_id) ?>" method="POST" style="margin-top:1rem;">
                    <?php wp_nonce_field('delete_photo_' . $personnel_id, 'delete_photo_nonce'); ?>
                    <button type="submit" class="btn-danger" onclick="return confirm('Are you sure you want to delete this photo?');" style="width:100%;">
                        <i class="fas fa-trash"></i> Delete Current Photo
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

/**
 * Process Photo Upload
 */
function b2b_personnel_process_photo_upload() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    $personnel_id = intval($_POST['personnel_id']);
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['photo_nonce'], 'upload_photo_' . $personnel_id)) {
        wp_die('Security check failed');
    }
    
    $post = get_post($personnel_id);
    if (!$post || $post->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['photo_file']) || $_FILES['photo_file']['error'] !== UPLOAD_ERR_OK) {
        wp_redirect(home_url('/personnel-panel/upload-photo/' . $personnel_id . '?error=upload'));
        exit;
    }
    
    $file = $_FILES['photo_file'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    $file_type = mime_content_type($file['tmp_name']);
    
    if (!in_array($file_type, $allowed_types)) {
        wp_redirect(home_url('/personnel-panel/upload-photo/' . $personnel_id . '?error=type'));
        exit;
    }
    
    // Validate file size (2MB max)
    if ($file['size'] > 2 * 1024 * 1024) {
        wp_redirect(home_url('/personnel-panel/upload-photo/' . $personnel_id . '?error=size'));
        exit;
    }
    
    // Create upload directory if it doesn't exist
    $upload_dir = wp_upload_dir();
    $personnel_dir = $upload_dir['basedir'] . '/personnel-photos/' . $personnel_id;
    
    if (!file_exists($personnel_dir)) {
        wp_mkdir_p($personnel_dir);
    }
    
    // Delete old photo if exists
    $old_photo = get_post_meta($personnel_id, '_photo_url', true);
    if ($old_photo) {
        $old_photo_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $old_photo);
        if (file_exists($old_photo_path)) {
            @unlink($old_photo_path);
        }
    }
    
    // Generate unique filename
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $new_filename = 'photo-' . time() . '.' . $file_ext;
    $new_filepath = $personnel_dir . '/' . $new_filename;
    
    // Move uploaded file
    if (move_uploaded_file($file['tmp_name'], $new_filepath)) {
        // Resize image to 300x300 thumbnail
        $image = wp_get_image_editor($new_filepath);
        if (!is_wp_error($image)) {
            $image->resize(300, 300, true);
            $image->save($new_filepath);
        }
        
        // Save photo URL to post meta
        $photo_url = $upload_dir['baseurl'] . '/personnel-photos/' . $personnel_id . '/' . $new_filename;
        update_post_meta($personnel_id, '_photo_url', $photo_url);
        update_post_meta($personnel_id, '_photo_upload_date', current_time('mysql'));
        update_post_meta($personnel_id, '_photo_uploaded_by', get_current_user_id());
        
        // Log activity
        $activity_log = get_post_meta($personnel_id, '_activity_log', true);
        if (!is_array($activity_log)) {
            $activity_log = [];
        }
        $activity_log[] = [
            'action' => 'photo_uploaded',
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'details' => 'Photo uploaded: ' . $new_filename
        ];
        // Keep only last 100 activities
        $activity_log = array_slice($activity_log, -100);
        update_post_meta($personnel_id, '_activity_log', $activity_log);
        
        wp_redirect(home_url('/personnel-panel/view/' . $personnel_id . '?photo=uploaded'));
        exit;
    } else {
        wp_redirect(home_url('/personnel-panel/upload-photo/' . $personnel_id . '?error=move'));
        exit;
    }
}

/**
 * Delete Photo
 */
function b2b_personnel_delete_photo() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    $personnel_id = get_query_var('personnel_id');
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['delete_photo_nonce'], 'delete_photo_' . $personnel_id)) {
        wp_die('Security check failed');
    }
    
    $post = get_post($personnel_id);
    if (!$post || $post->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    // Get photo URL
    $photo_url = get_post_meta($personnel_id, '_photo_url', true);
    
    if ($photo_url) {
        // Delete physical file
        $upload_dir = wp_upload_dir();
        $photo_path = str_replace($upload_dir['baseurl'], $upload_dir['basedir'], $photo_url);
        
        if (file_exists($photo_path)) {
            @unlink($photo_path);
        }
        
        // Delete meta
        delete_post_meta($personnel_id, '_photo_url');
        delete_post_meta($personnel_id, '_photo_upload_date');
        delete_post_meta($personnel_id, '_photo_uploaded_by');
        
        // Log activity
        $activity_log = get_post_meta($personnel_id, '_activity_log', true);
        if (!is_array($activity_log)) {
            $activity_log = [];
        }
        $activity_log[] = [
            'action' => 'photo_deleted',
            'user_id' => get_current_user_id(),
            'timestamp' => current_time('mysql'),
            'details' => 'Photo deleted'
        ];
        $activity_log = array_slice($activity_log, -100);
        update_post_meta($personnel_id, '_activity_log', $activity_log);
    }
    
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id . '?photo=deleted'));
    exit;
}

/* =====================================================
 * PRINT-FRIENDLY VIEW
 * ===================================================== */
function b2b_personnel_print_view() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $person = get_post($personnel_id);
    
    if (!$person || $person->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    // Get all personnel data
    $gorev = get_post_meta($personnel_id, '_gorev', true);
    $eposta = get_post_meta($personnel_id, '_eposta', true);
    $telefon = get_post_meta($personnel_id, '_telefon', true);
    $maas = get_post_meta($personnel_id, '_maas', true);
    $baslangic = get_post_meta($personnel_id, '_baslangic_tarihi', true);
    $depts = get_the_terms($personnel_id, 'b2b_departman');
    $dept_name = $depts && !is_wp_error($depts) ? $depts[0]->name : 'N/A';
    $employee_id = get_post_meta($personnel_id, '_employee_id', true);
    $employment_status = get_post_meta($personnel_id, '_employment_status', true);
    $emergency_name = get_post_meta($personnel_id, '_emergency_contact_name', true);
    $emergency_relationship = get_post_meta($personnel_id, '_emergency_contact_relationship', true);
    $emergency_phone = get_post_meta($personnel_id, '_emergency_contact_phone', true);
    $pay_type = get_post_meta($personnel_id, '_pay_type', true);
    $pay_rate = get_post_meta($personnel_id, '_pay_rate', true);
    $flsa_status = get_post_meta($personnel_id, '_flsa_status', true);
    $reports_to = get_post_meta($personnel_id, '_reports_to', true);
    $termination_date = get_post_meta($personnel_id, '_termination_date', true);
    $rehire_date = get_post_meta($personnel_id, '_rehire_date', true);
    $photo_url = get_post_meta($personnel_id, '_photo_url', true);
    
    $manager_name = 'N/A';
    if ($reports_to) {
        $manager = get_post($reports_to);
        if ($manager) {
            $manager_name = $manager->post_title;
        }
    }
    
    $notes = get_post_meta($personnel_id, '_notes', true) ?: [];
    $documents = get_post_meta($personnel_id, '_documents', true) ?: [];
    $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Personnel Report - <?= esc_html($person->post_title) ?></title>
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: Arial, sans-serif;
                background: #fff;
                color: #000;
                padding: 20px;
            }
            .print-header {
                text-align: center;
                margin-bottom: 30px;
                border-bottom: 3px solid #000;
                padding-bottom: 20px;
            }
            .print-header h1 {
                font-size: 24px;
                margin-bottom: 5px;
            }
            .print-header p {
                color: #666;
                font-size: 14px;
            }
            .personnel-photo {
                text-align: center;
                margin-bottom: 20px;
            }
            .personnel-photo img {
                width: 120px;
                height: 120px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid #000;
            }
            .section {
                margin-bottom: 30px;
                page-break-inside: avoid;
            }
            .section h2 {
                font-size: 18px;
                border-bottom: 2px solid #000;
                padding-bottom: 8px;
                margin-bottom: 15px;
            }
            .info-grid {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
                margin-bottom: 10px;
            }
            .info-item {
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
            }
            .info-item label {
                display: block;
                font-weight: bold;
                font-size: 12px;
                color: #666;
                margin-bottom: 5px;
            }
            .info-item p {
                font-size: 14px;
            }
            .notes-list, .docs-list, .attendance-list {
                list-style: none;
            }
            .notes-list li, .docs-list li, .attendance-list li {
                padding: 10px;
                background: #f9f9f9;
                border: 1px solid #ddd;
                margin-bottom: 10px;
            }
            .no-print {
                margin-top: 30px;
                text-align: center;
            }
            .no-print button {
                padding: 12px 30px;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 16px;
                cursor: pointer;
            }
            .no-print button:hover {
                background: #2563eb;
            }
            @media print {
                body {
                    padding: 0;
                }
                .no-print {
                    display: none !important;
                }
                .section {
                    page-break-inside: avoid;
                }
            }
        </style>
    </head>
    <body>
        <div class="print-header">
            <h1>PERSONNEL INFORMATION REPORT</h1>
            <p>Generated on <?= esc_html(date('F d, Y g:i A')) ?></p>
        </div>

        <?php if ($photo_url): ?>
        <div class="personnel-photo">
            <img src="<?= esc_url($photo_url) ?>" alt="<?= esc_attr($person->post_title) ?>">
        </div>
        <?php endif; ?>

        <div class="section">
            <h2>Basic Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Full Name</label>
                    <p><?= esc_html($person->post_title) ?></p>
                </div>
                <div class="info-item">
                    <label>Employee ID</label>
                    <p><?= esc_html($employee_id ?: 'N/A') ?></p>
                </div>
                <div class="info-item">
                    <label>Email</label>
                    <p><?= esc_html($eposta) ?></p>
                </div>
                <div class="info-item">
                    <label>Phone</label>
                    <p><?= esc_html($telefon) ?></p>
                </div>
                <div class="info-item">
                    <label>Department</label>
                    <p><?= esc_html($dept_name) ?></p>
                </div>
                <div class="info-item">
                    <label>Position</label>
                    <p><?= esc_html($gorev) ?></p>
                </div>
                <div class="info-item">
                    <label>Employment Status</label>
                    <p><?= esc_html(ucfirst($employment_status ?: 'N/A')) ?></p>
                </div>
                <div class="info-item">
                    <label>Hire Date</label>
                    <p><?= esc_html($baslangic ?: 'N/A') ?></p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Employment Details</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Reports To</label>
                    <p><?= esc_html($manager_name) ?></p>
                </div>
                <div class="info-item">
                    <label>Pay Type</label>
                    <p><?= esc_html(ucfirst($pay_type ?: 'N/A')) ?></p>
                </div>
                <div class="info-item">
                    <label>Pay Rate</label>
                    <p><?= $pay_rate ? esc_html('$' . number_format((float)$pay_rate, 2)) : 'N/A' ?></p>
                </div>
                <div class="info-item">
                    <label>Base Salary</label>
                    <p><?= $maas ? esc_html('$' . number_format((float)$maas, 2)) : 'N/A' ?></p>
                </div>
                <div class="info-item">
                    <label>FLSA Status</label>
                    <p><?= esc_html(ucfirst($flsa_status ?: 'N/A')) ?></p>
                </div>
                <?php if ($termination_date): ?>
                <div class="info-item">
                    <label>Termination Date</label>
                    <p><?= esc_html($termination_date) ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <h2>Emergency Contact</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Contact Name</label>
                    <p><?= esc_html($emergency_name ?: 'N/A') ?></p>
                </div>
                <div class="info-item">
                    <label>Relationship</label>
                    <p><?= esc_html($emergency_relationship ?: 'N/A') ?></p>
                </div>
                <div class="info-item">
                    <label>Phone</label>
                    <p><?= esc_html($emergency_phone ?: 'N/A') ?></p>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>Notes (<?= count($notes) ?>)</h2>
            <?php if (!empty($notes)): ?>
                <ul class="notes-list">
                    <?php foreach (array_slice(array_reverse($notes), 0, 10) as $note): ?>
                        <li>
                            <strong><?= date('M d, Y g:i A', strtotime($note['date'])) ?></strong><br>
                            <?= esc_html($note['note']) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No notes recorded.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Documents (<?= count($documents) ?>)</h2>
            <?php if (!empty($documents)): ?>
                <ul class="docs-list">
                    <?php foreach ($documents as $doc): ?>
                        <li>
                            <strong><?= esc_html($doc['name']) ?></strong><br>
                            Uploaded: <?= date('M d, Y', strtotime($doc['date'])) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No documents uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="section">
            <h2>Recent Attendance (Last 10 Records)</h2>
            <?php if (!empty($attendance)): ?>
                <ul class="attendance-list">
                    <?php foreach (array_slice(array_reverse($attendance), 0, 10) as $record): ?>
                        <li>
                            <strong><?= esc_html(ucwords(str_replace('_', ' ', $record['type']))) ?></strong> - 
                            <?= date('M d, Y g:i A', strtotime($record['date'])) ?>
                            <?php if (!empty($record['notes'])): ?>
                                <br>Note: <?= esc_html($record['notes']) ?>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>No attendance records.</p>
            <?php endif; ?>
        </div>

        <div class="no-print">
            <button onclick="window.print()">Print This Page</button>
            <button onclick="window.close()" style="background:#6b7280;">Close</button>
        </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * ENHANCED AUDIT LOG WITH FIELD-LEVEL CHANGES
 * ===================================================== */
function b2b_personnel_enhanced_audit() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $person = get_post($personnel_id);
    
    if (!$person || $person->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    // Get filters
    $filter_action = isset($_GET['action_filter']) ? sanitize_text_field($_GET['action_filter']) : '';
    $filter_date = isset($_GET['date_filter']) ? sanitize_text_field($_GET['date_filter']) : '';
    
    // Get activity log
    $all_activity = get_post_meta($personnel_id, '_activity_log', true) ?: [];
    $activity = array_reverse($all_activity);
    
    // Apply filters
    if ($filter_action) {
        $activity = array_filter($activity, function($log) use ($filter_action) {
            return $log['action'] === $filter_action;
        });
    }
    
    if ($filter_date) {
        $activity = array_filter($activity, function($log) use ($filter_date) {
            return strpos($log['date'], $filter_date) === 0;
        });
    }
    
    // Get unique actions for filter
    $unique_actions = array_unique(array_column($all_activity, 'action'));
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enhanced Audit Log - <?= esc_html($person->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .container {
                max-width: 1200px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            .filter-bar {
                background: white;
                padding: 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .filter-bar form {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                align-items: center;
            }
            .filter-bar select,
            .filter-bar input {
                padding: 0.5rem 1rem;
                border: 1px solid #d1d5db;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .filter-bar button {
                padding: 0.5rem 1.5rem;
                background: #3b82f6;
                color: white;
                border: none;
                border-radius: 6px;
                font-size: 0.875rem;
                cursor: pointer;
            }
            .filter-bar button:hover { background: #2563eb; }
            .audit-log {
                background: white;
                border-radius: 8px;
                padding: 1.5rem;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .log-entry {
                padding: 1rem;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                margin-bottom: 1rem;
                border-left: 4px solid #3b82f6;
            }
            .log-entry h3 {
                font-size: 1rem;
                margin-bottom: 0.5rem;
                color: #111827;
            }
            .log-entry p {
                font-size: 0.875rem;
                color: #6b7280;
                margin-bottom: 0.5rem;
            }
            .log-entry .changes {
                background: #f9fafb;
                padding: 0.75rem;
                border-radius: 4px;
                margin-top: 0.5rem;
                font-family: monospace;
                font-size: 0.875rem;
            }
            .log-entry .changes .before {
                color: #ef4444;
            }
            .log-entry .changes .after {
                color: #10b981;
            }
            .empty-state {
                text-align: center;
                padding: 3rem;
                color: #6b7280;
            }
            @media (max-width: 768px) {
                .header { flex-direction: column; gap: 1rem; }
                .filter-bar form { flex-direction: column; }
                .filter-bar select,
                .filter-bar input { width: 100%; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-history"></i> Enhanced Audit Log - <?= esc_html($person->post_title) ?></h1>
            <a href="<?= home_url('/personnel-panel/view/' . $personnel_id) ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Personnel
            </a>
        </div>

        <div class="container">
            <div class="filter-bar">
                <form method="GET">
                    <label style="font-weight:600;font-size:0.875rem;">Filter by Action:</label>
                    <select name="action_filter">
                        <option value="">All Actions</option>
                        <?php foreach ($unique_actions as $action): ?>
                            <option value="<?= esc_attr($action) ?>" <?= selected($filter_action, $action, false) ?>>
                                <?= esc_html(ucwords(str_replace('_', ' ', $action))) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <label style="font-weight:600;font-size:0.875rem;">Filter by Date:</label>
                    <input type="date" name="date_filter" value="<?= esc_attr($filter_date) ?>">
                    <button type="submit"><i class="fas fa-filter"></i> Apply Filters</button>
                    <a href="<?= home_url('/personnel-panel/enhanced-audit/' . $personnel_id) ?>" style="padding:0.5rem 1rem;background:#6b7280;color:white;text-decoration:none;border-radius:6px;font-size:0.875rem;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </form>
            </div>

            <div class="audit-log">
                <h2 style="margin-bottom:1.5rem;font-size:1.25rem;color:#111827;">
                    Activity Log (<?= count($activity) ?> entries)
                </h2>

                <?php if (empty($activity)): ?>
                    <div class="empty-state">
                        <i class="fas fa-history" style="font-size:3rem;margin-bottom:1rem;opacity:0.5;"></i>
                        <p>No activity found matching your filters.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($activity as $log): ?>
                        <div class="log-entry">
                            <h3>
                                <i class="fas fa-<?= b2b_activity_icon($log['action']) ?>"></i>
                                <?= esc_html(ucwords(str_replace('_', ' ', $log['action']))) ?>
                            </h3>
                            <p><strong>Date:</strong> <?= date('F d, Y g:i A', strtotime($log['date'])) ?></p>
                            <p><strong>User:</strong> 
                                <?php
                                $user = get_userdata($log['user_id']);
                                echo $user ? esc_html($user->display_name) : 'Unknown';
                                ?>
                            </p>
                            <p><strong>Details:</strong> <?= esc_html($log['details']) ?></p>
                            
                            <?php if (isset($log['changes']) && is_array($log['changes'])): ?>
                                <div class="changes">
                                    <strong>Field Changes:</strong><br>
                                    <?php foreach ($log['changes'] as $field => $change): ?>
                                        <div style="margin-top:0.5rem;">
                                            <strong><?= esc_html(ucwords(str_replace('_', ' ', $field))) ?>:</strong><br>
                                            <span class="before">- Before: <?= esc_html($change['before'] ?: 'N/A') ?></span><br>
                                            <span class="after">+ After: <?= esc_html($change['after'] ?: 'N/A') ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * PERFORMANCE METRICS DASHBOARD
 * ===================================================== */
function b2b_personnel_metrics() {
    $personnel_id = intval(get_query_var('personnel_id'));
    $person = get_post($personnel_id);
    
    if (!$person || $person->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    // Get attendance data
    $attendance = get_post_meta($personnel_id, '_attendance', true) ?: [];
    $vacation_balance = floatval(get_post_meta($personnel_id, '_vacation_balance', true));
    $sick_leave_balance = floatval(get_post_meta($personnel_id, '_sick_leave_balance', true));
    $pto_accrual_rate = floatval(get_post_meta($personnel_id, '_pto_accrual_rate', true));
    
    // Calculate metrics
    $total_days = 0;
    $present_days = 0;
    $overtime_hours = 0;
    $pto_used = 0;
    
    $daily_records = [];
    foreach ($attendance as $record) {
        $date = substr($record['date'], 0, 10);
        if (!isset($daily_records[$date])) {
            $daily_records[$date] = [];
        }
        $daily_records[$date][] = $record;
    }
    
    foreach ($daily_records as $date => $records) {
        $total_days++;
        $clock_in = null;
        $clock_out = null;
        
        foreach ($records as $record) {
            if ($record['type'] === 'clock_in') {
                $clock_in = strtotime($record['date']);
            } elseif ($record['type'] === 'clock_out' && $clock_in) {
                $clock_out = strtotime($record['date']);
            }
        }
        
        if ($clock_in && $clock_out) {
            $present_days++;
            $hours_worked = ($clock_out - $clock_in) / 3600;
            if ($hours_worked > 8) {
                $overtime_hours += ($hours_worked - 8);
            }
        }
    }
    
    // Calculate PTO used (simplified - based on sick leave decrease)
    $pto_used = max(0, 80 - $vacation_balance - $sick_leave_balance);
    
    // Calculate attendance rate
    $attendance_rate = $total_days > 0 ? ($present_days / $total_days) * 100 : 0;
    
    // Calculate PTO usage percentage (assuming 80 hours annual total)
    $total_pto = 80;
    $pto_usage_percent = ($pto_used / $total_pto) * 100;
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Performance Metrics - <?= esc_html($person->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Inter', -apple-system, sans-serif;
                background: #f3f4f6;
                color: #1f2937;
            }
            .header {
                background: white;
                border-bottom: 1px solid #e5e7eb;
                padding: 1rem 2rem;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .header h1 {
                font-size: 1.5rem;
                color: #111827;
            }
            .back-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: #6b7280;
                color: white;
                text-decoration: none;
                border-radius: 6px;
                font-size: 0.875rem;
            }
            .back-btn:hover { background: #4b5563; }
            .container {
                max-width: 1200px;
                margin: 2rem auto;
                padding: 0 1rem;
            }
            .metrics-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 1.5rem;
                margin-bottom: 2rem;
            }
            .metric-card {
                background: white;
                padding: 1.5rem;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .metric-card h3 {
                font-size: 0.875rem;
                color: #6b7280;
                margin-bottom: 0.5rem;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            .metric-value {
                font-size: 2rem;
                font-weight: bold;
                margin-bottom: 1rem;
            }
            .progress-bar {
                width: 100%;
                height: 12px;
                background: #e5e7eb;
                border-radius: 6px;
                overflow: hidden;
                margin-bottom: 0.5rem;
            }
            .progress-fill {
                height: 100%;
                border-radius: 6px;
                transition: width 0.3s ease;
            }
            .metric-label {
                font-size: 0.875rem;
                color: #6b7280;
            }
            .summary-card {
                background: white;
                padding: 2rem;
                border-radius: 12px;
                box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            }
            .summary-card h2 {
                font-size: 1.25rem;
                margin-bottom: 1.5rem;
                color: #111827;
            }
            .summary-row {
                display: flex;
                justify-content: space-between;
                padding: 1rem;
                border-bottom: 1px solid #e5e7eb;
            }
            .summary-row:last-child {
                border-bottom: none;
            }
            @media (max-width: 768px) {
                .header { flex-direction: column; gap: 1rem; }
                .metrics-grid {
                    grid-template-columns: 1fr;
                }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1><i class="fas fa-chart-line"></i> Performance Metrics - <?= esc_html($person->post_title) ?></h1>
            <a href="<?= home_url('/personnel-panel/view/' . $personnel_id) ?>" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Personnel
            </a>
        </div>

        <div class="container">
            <div class="metrics-grid">
                <!-- Attendance Rate -->
                <div class="metric-card">
                    <h3><i class="fas fa-calendar-check"></i> Attendance Rate</h3>
                    <div class="metric-value" style="color:<?= $attendance_rate >= 90 ? '#10b981' : ($attendance_rate >= 75 ? '#f59e0b' : '#ef4444') ?>;">
                        <?= number_format($attendance_rate, 1) ?>%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?= min(100, $attendance_rate) ?>%;background:<?= $attendance_rate >= 90 ? '#10b981' : ($attendance_rate >= 75 ? '#f59e0b' : '#ef4444') ?>;"></div>
                    </div>
                    <div class="metric-label">
                        <?= $present_days ?> of <?= $total_days ?> days present
                    </div>
                </div>

                <!-- Overtime Hours -->
                <div class="metric-card">
                    <h3><i class="fas fa-clock"></i> Overtime Hours</h3>
                    <div class="metric-value" style="color:<?= $overtime_hours > 40 ? '#ef4444' : ($overtime_hours > 20 ? '#f59e0b' : '#10b981') ?>;">
                        <?= number_format($overtime_hours, 1) ?>h
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?= min(100, ($overtime_hours / 80) * 100) ?>%;background:<?= $overtime_hours > 40 ? '#ef4444' : ($overtime_hours > 20 ? '#f59e0b' : '#10b981') ?>;"></div>
                    </div>
                    <div class="metric-label">
                        Total overtime this period
                    </div>
                </div>

                <!-- PTO Usage -->
                <div class="metric-card">
                    <h3><i class="fas fa-umbrella-beach"></i> PTO Usage</h3>
                    <div class="metric-value" style="color:<?= $pto_usage_percent > 80 ? '#ef4444' : ($pto_usage_percent > 50 ? '#f59e0b' : '#10b981') ?>;">
                        <?= number_format($pto_usage_percent, 1) ?>%
                    </div>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width:<?= min(100, $pto_usage_percent) ?>%;background:<?= $pto_usage_percent > 80 ? '#ef4444' : ($pto_usage_percent > 50 ? '#f59e0b' : '#10b981') ?>;"></div>
                    </div>
                    <div class="metric-label">
                        <?= number_format($pto_used, 1) ?> of <?= $total_pto ?> hours used
                    </div>
                </div>
            </div>

            <div class="summary-card">
                <h2><i class="fas fa-list-ul"></i> Detailed Summary</h2>
                <div class="summary-row">
                    <span style="font-weight:600;">Total Days Tracked:</span>
                    <span><?= $total_days ?> days</span>
                </div>
                <div class="summary-row">
                    <span style="font-weight:600;">Present Days:</span>
                    <span style="color:#10b981;font-weight:600;"><?= $present_days ?> days</span>
                </div>
                <div class="summary-row">
                    <span style="font-weight:600;">Absent Days:</span>
                    <span style="color:#ef4444;font-weight:600;"><?= $total_days - $present_days ?> days</span>
                </div>
                <div class="summary-row">
                    <span style="font-weight:600;">Overtime Hours:</span>
                    <span style="color:#f59e0b;font-weight:600;"><?= esc_html(number_format($overtime_hours, 1)) ?> hours</span>
                </div>
                <div class="summary-row">
                    <span style="font-weight:600;">Vacation Balance:</span>
                    <span><?= esc_html(number_format($vacation_balance, 1)) ?> hours</span>
                </div>
                <div class="summary-row">
                    <span style="font-weight:600;">Sick Leave Balance:</span>
                    <span><?= esc_html(number_format($sick_leave_balance, 1)) ?> hours</span>
                </div>
                <div class="summary-row">
                    <span style="font-weight:600;">PTO Accrual Rate:</span>
                    <span><?= esc_html(number_format($pto_accrual_rate, 2)) ?> hours/pay period</span>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

/* =====================================================
 * PTO MANAGEMENT SYSTEM FUNCTIONS
 * ===================================================== */

// Leave Request Form
function b2b_personnel_request_leave() {
    $personnel_id = get_query_var('personnel_id');
    $personnel = get_post($personnel_id);
    
    if (!$personnel || $personnel->post_type !== 'b2b_personel') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    $vacation_balance = floatval(get_post_meta($personnel_id, '_vacation_balance', true));
    $sick_leave_balance = floatval(get_post_meta($personnel_id, '_sick_leave_balance', true));
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Request Leave - <?= esc_html($personnel->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
            .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
            .header h1 { font-size: 28px; color: #1f2937; }
            .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; transition: all 0.3s; }
            .btn-back { background: #6b7280; color: white; }
            .btn-back:hover { background: #4b5563; }
            .form-section { background: #f9fafb; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
            .form-section h2 { font-size: 18px; color: #374151; margin-bottom: 15px; display: flex; align-items: center; gap: 10px; }
            .form-group { margin-bottom: 20px; }
            .form-group label { display: block; font-weight: 600; color: #374151; margin-bottom: 8px; }
            .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; }
            .form-group input:focus, .form-group select:focus, .form-group textarea:focus { outline: none; border-color: #8b5cf6; }
            .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
            .balance-info { background: #eff6ff; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            .balance-info h3 { font-size: 16px; color: #1e40af; margin-bottom: 10px; }
            .balance-item { display: flex; justify-content: space-between; margin-bottom: 8px; }
            .balance-item strong { color: #1f2937; }
            .balance-item span { color: #059669; font-weight: 600; }
            .btn-submit { background: linear-gradient(135deg, #8b5cf6, #6366f1); color: white; border: none; padding: 15px 40px; font-size: 16px; font-weight: 600; cursor: pointer; width: 100%; border-radius: 10px; }
            .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(139, 92, 246, 0.3); }
            .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; }
            .alert-info { background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-umbrella-beach"></i> Request Time Off</h1>
                <a href="<?= esc_url(home_url('/personnel-panel/view/' . $personnel_id)) ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            
            <div class="alert alert-info">
                <strong><i class="fas fa-info-circle"></i> Note:</strong> Your request will be sent to your manager for approval. Balance will be deducted upon approval.
            </div>
            
            <div class="balance-info">
                <h3><i class="fas fa-wallet"></i> Current Balances</h3>
                <div class="balance-item">
                    <strong>Vacation Hours:</strong>
                    <span><?= esc_html(number_format($vacation_balance, 1)) ?> hours</span>
                </div>
                <div class="balance-item">
                    <strong>Sick Leave Hours:</strong>
                    <span><?= esc_html(number_format($sick_leave_balance, 1)) ?> hours</span>
                </div>
            </div>
            
            <form method="POST" action="<?= esc_url(home_url('/personnel-panel/process-leave-request')) ?>">
                <?php wp_nonce_field('request_leave_' . $personnel_id); ?>
                <input type="hidden" name="personnel_id" value="<?= esc_attr($personnel_id) ?>">
                
                <div class="form-section">
                    <h2><i class="fas fa-clipboard-list"></i> Leave Details</h2>
                    
                    <div class="form-group">
                        <label for="leave_type">Leave Type *</label>
                        <select name="leave_type" id="leave_type" required>
                            <option value="">Select leave type...</option>
                            <option value="vacation">Vacation</option>
                            <option value="sick">Sick Leave</option>
                            <option value="personal">Personal</option>
                            <option value="unpaid">Unpaid</option>
                        </select>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Start Date *</label>
                            <input type="date" name="start_date" id="start_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date *</label>
                            <input type="date" name="end_date" id="end_date" required min="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="reason">Reason / Notes</label>
                        <textarea name="reason" id="reason" rows="4" placeholder="Please provide details about your leave request..."></textarea>
                    </div>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Leave Request
                </button>
            </form>
        </div>
        
        <script>
        document.getElementById('start_date').addEventListener('change', function() {
            document.getElementById('end_date').min = this.value;
        });
        </script>
    </body>
    </html>
    <?php
}

// Process Leave Request
function b2b_personnel_process_leave_request() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_redirect(home_url('/personnel-panel'));
        exit;
    }
    
    $personnel_id = isset($_POST['personnel_id']) ? intval($_POST['personnel_id']) : 0;
    
    if (!wp_verify_nonce($_POST['_wpnonce'], 'request_leave_' . $personnel_id)) {
        wp_die('Security check failed');
    }
    
    $leave_type = sanitize_text_field($_POST['leave_type']);
    $start_date = sanitize_text_field($_POST['start_date']);
    $end_date = sanitize_text_field($_POST['end_date']);
    $reason = sanitize_textarea_field($_POST['reason']);
    
    // Calculate total days
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    $interval = $start->diff($end);
    $total_days = $interval->days + 1;
    
    // Get current balances
    $vacation_balance = floatval(get_post_meta($personnel_id, '_vacation_balance', true));
    $sick_leave_balance = floatval(get_post_meta($personnel_id, '_sick_leave_balance', true));
    
    // Create leave request object
    $leave_request = [
        'id' => uniqid('leave_'),
        'employee_id' => $personnel_id,
        'leave_type' => $leave_type,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'total_days' => $total_days,
        'reason' => $reason,
        'status' => 'pending',
        'requested_date' => current_time('mysql'),
        'requested_by' => get_current_user_id(),
        'approved_by' => null,
        'approval_date' => null,
        'manager_notes' => '',
        'balance_before' => $leave_type === 'vacation' ? $vacation_balance : ($leave_type === 'sick' ? $sick_leave_balance : 0),
        'balance_after' => null,
        'pay_impact' => $leave_type === 'unpaid' ? ($total_days * 8) : 0,
        'gl_code' => null,
        'posted_to_accounting' => false,
        'accounting_entry_id' => null
    ];
    
    // Get existing leave requests
    $leave_requests = get_post_meta($personnel_id, '_leave_requests', true);
    if (!is_array($leave_requests)) {
        $leave_requests = [];
    }
    
    // Add new request
    $leave_requests[] = $leave_request;
    update_post_meta($personnel_id, '_leave_requests', $leave_requests);
    
    // Log activity
    b2b_log_personnel_activity($personnel_id, 'leave_requested', "Requested $leave_type leave from $start_date to $end_date ($total_days days)");
    
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id . '?msg=leave_requested'));
    exit;
}

// Leave Approvals Dashboard
function b2b_personnel_leave_approvals() {
    // Get all personnel with pending leave requests
    $personnel_args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC'
    ];
    
    $all_personnel = get_posts($personnel_args);
    $pending_requests = [];
    
    foreach ($all_personnel as $person) {
        $leave_requests = get_post_meta($person->ID, '_leave_requests', true);
        if (is_array($leave_requests)) {
            foreach ($leave_requests as $request) {
                if ($request['status'] === 'pending') {
                    $request['employee_name'] = $person->post_title;
                    $request['employee_id'] = $person->ID;
                    $pending_requests[] = $request;
                }
            }
        }
    }
    
    // Sort by requested date (newest first)
    usort($pending_requests, function($a, $b) {
        return strtotime($b['requested_date']) - strtotime($a['requested_date']);
    });
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Leave Approvals</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
            .container { max-width: 1200px; margin: 0 auto; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
            .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
            .header h1 { font-size: 28px; color: #1f2937; }
            .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; transition: all 0.3s; }
            .btn-back { background: #6b7280; color: white; }
            .btn-back:hover { background: #4b5563; }
            .request-card { background: #f9fafb; padding: 20px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid #f59e0b; }
            .request-header { display: flex; justify-content: space-between; align-items: start; margin-bottom: 15px; }
            .request-header h3 { font-size: 18px; color: #1f2937; }
            .request-header .badge { padding: 6px 12px; border-radius: 20px; font-size: 13px; font-weight: 600; }
            .badge-pending { background: #fef3c7; color: #92400e; }
            .request-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 15px; }
            .detail-item { display: flex; flex-direction: column; gap: 4px; }
            .detail-item label { font-size: 12px; color: #6b7280; font-weight: 600; text-transform: uppercase; }
            .detail-item span { font-size: 15px; color: #1f2937; }
            .request-reason { background: white; padding: 12px; border-radius: 6px; margin-bottom: 15px; font-style: italic; color: #4b5563; }
            .action-buttons { display: flex; gap: 10px; }
            .btn-approve { background: #10b981; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
            .btn-approve:hover { background: #059669; }
            .btn-deny { background: #ef4444; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; font-weight: 600; }
            .btn-deny:hover { background: #dc2626; }
            .empty-state { text-align: center; padding: 60px 20px; color: #6b7280; }
            .empty-state i { font-size: 64px; margin-bottom: 20px; color: #d1d5db; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-tasks"></i> Leave Approvals</h1>
                <a href="<?= esc_url(home_url('/personnel-panel')) ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Personnel
                </a>
            </div>
            
            <?php if (empty($pending_requests)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <h2>No Pending Requests</h2>
                    <p>All leave requests have been processed.</p>
                </div>
            <?php else: ?>
                <?php foreach ($pending_requests as $request): ?>
                    <div class="request-card">
                        <div class="request-header">
                            <h3><i class="fas fa-user"></i> <?= esc_html($request['employee_name']) ?></h3>
                            <span class="badge badge-pending">Pending</span>
                        </div>
                        
                        <div class="request-details">
                            <div class="detail-item">
                                <label>Leave Type</label>
                                <span><?= esc_html(ucfirst($request['leave_type'])) ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Start Date</label>
                                <span><?= esc_html(date('M d, Y', strtotime($request['start_date']))) ?></span>
                            </div>
                            <div class="detail-item">
                                <label>End Date</label>
                                <span><?= esc_html(date('M d, Y', strtotime($request['end_date']))) ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Total Days</label>
                                <span><?= esc_html($request['total_days']) ?> days</span>
                            </div>
                            <div class="detail-item">
                                <label>Requested On</label>
                                <span><?= esc_html(date('M d, Y', strtotime($request['requested_date']))) ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($request['reason'])): ?>
                            <div class="request-reason">
                                <strong>Reason:</strong> <?= esc_html($request['reason']) ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="action-buttons">
                            <form method="POST" action="<?= esc_url(home_url('/personnel-panel/approve-leave/' . $request['id'])) ?>" style="display:inline;">
                                <?php wp_nonce_field('approve_leave_' . $request['id']); ?>
                                <button type="submit" class="btn-approve">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                            </form>
                            <form method="POST" action="<?= esc_url(home_url('/personnel-panel/deny-leave/' . $request['id'])) ?>" style="display:inline;">
                                <?php wp_nonce_field('deny_leave_' . $request['id']); ?>
                                <button type="submit" class="btn-deny">
                                    <i class="fas fa-times"></i> Deny
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

// Approve Leave
function b2b_personnel_approve_leave() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_redirect(home_url('/personnel-panel/leave-approvals'));
        exit;
    }
    
    $leave_id = get_query_var('leave_id');
    
    if (!wp_verify_nonce($_POST['_wpnonce'], 'approve_leave_' . $leave_id)) {
        wp_die('Security check failed');
    }
    
    // Find and update the leave request
    $personnel_args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1
    ];
    
    $all_personnel = get_posts($personnel_args);
    
    foreach ($all_personnel as $person) {
        $leave_requests = get_post_meta($person->ID, '_leave_requests', true);
        if (is_array($leave_requests)) {
            foreach ($leave_requests as $key => $request) {
                if ($request['id'] === $leave_id) {
                    // Update request status
                    $leave_requests[$key]['status'] = 'approved';
                    $leave_requests[$key]['approved_by'] = get_current_user_id();
                    $leave_requests[$key]['approval_date'] = current_time('mysql');
                    
                    // Deduct balance
                    if ($request['leave_type'] === 'vacation') {
                        $current_balance = floatval(get_post_meta($person->ID, '_vacation_balance', true));
                        $new_balance = $current_balance - ($request['total_days'] * 8);
                        update_post_meta($person->ID, '_vacation_balance', $new_balance);
                        $leave_requests[$key]['balance_after'] = $new_balance;
                    } elseif ($request['leave_type'] === 'sick') {
                        $current_balance = floatval(get_post_meta($person->ID, '_sick_leave_balance', true));
                        $new_balance = $current_balance - ($request['total_days'] * 8);
                        update_post_meta($person->ID, '_sick_leave_balance', $new_balance);
                        $leave_requests[$key]['balance_after'] = $new_balance;
                    }
                    
                    update_post_meta($person->ID, '_leave_requests', $leave_requests);
                    
                    // Log activity
                    b2b_log_personnel_activity($person->ID, 'leave_approved', "Leave request approved: {$request['leave_type']} from {$request['start_date']} to {$request['end_date']}");
                    
                    wp_redirect(home_url('/personnel-panel/leave-approvals?msg=approved'));
                    exit;
                }
            }
        }
    }
    
    wp_redirect(home_url('/personnel-panel/leave-approvals'));
    exit;
}

// Deny Leave
function b2b_personnel_deny_leave() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        wp_redirect(home_url('/personnel-panel/leave-approvals'));
        exit;
    }
    
    $leave_id = get_query_var('leave_id');
    
    if (!wp_verify_nonce($_POST['_wpnonce'], 'deny_leave_' . $leave_id)) {
        wp_die('Security check failed');
    }
    
    // Find and update the leave request
    $personnel_args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1
    ];
    
    $all_personnel = get_posts($personnel_args);
    
    foreach ($all_personnel as $person) {
        $leave_requests = get_post_meta($person->ID, '_leave_requests', true);
        if (is_array($leave_requests)) {
            foreach ($leave_requests as $key => $request) {
                if ($request['id'] === $leave_id) {
                    // Update request status
                    $leave_requests[$key]['status'] = 'denied';
                    $leave_requests[$key]['approved_by'] = get_current_user_id();
                    $leave_requests[$key]['approval_date'] = current_time('mysql');
                    
                    update_post_meta($person->ID, '_leave_requests', $leave_requests);
                    
                    // Log activity
                    b2b_log_personnel_activity($person->ID, 'leave_denied', "Leave request denied: {$request['leave_type']} from {$request['start_date']} to {$request['end_date']}");
                    
                    wp_redirect(home_url('/personnel-panel/leave-approvals?msg=denied'));
                    exit;
                }
            }
        }
    }
    
    wp_redirect(home_url('/personnel-panel/leave-approvals'));
    exit;
}

// Leave Calendar View
function b2b_personnel_leave_calendar() {
    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    
    // Get all approved leave requests for the month
    $personnel_args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1
    ];
    
    $all_personnel = get_posts($personnel_args);
    $calendar_data = [];
    
    foreach ($all_personnel as $person) {
        $leave_requests = get_post_meta($person->ID, '_leave_requests', true);
        if (is_array($leave_requests)) {
            foreach ($leave_requests as $request) {
                if ($request['status'] === 'approved') {
                    $start = new DateTime($request['start_date']);
                    $end = new DateTime($request['end_date']);
                    $end->modify('+1 day');
                    
                    $period = new DatePeriod($start, new DateInterval('P1D'), $end);
                    
                    foreach ($period as $date) {
                        if ($date->format('n') == $month && $date->format('Y') == $year) {
                            $day_key = $date->format('Y-m-d');
                            if (!isset($calendar_data[$day_key])) {
                                $calendar_data[$day_key] = [];
                            }
                            $calendar_data[$day_key][] = [
                                'name' => $person->post_title,
                                'type' => $request['leave_type']
                            ];
                        }
                    }
                }
            }
        }
    }
    
    $first_day = new DateTime("$year-$month-01");
    $last_day = new DateTime($first_day->format('Y-m-t'));
    $days_in_month = $last_day->format('j');
    $start_day_of_week = $first_day->format('w');
    
    $prev_month = $month - 1;
    $prev_year = $year;
    if ($prev_month < 1) {
        $prev_month = 12;
        $prev_year--;
    }
    
    $next_month = $month + 1;
    $next_year = $year;
    if ($next_month > 12) {
        $next_month = 1;
        $next_year++;
    }
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Leave Calendar - <?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
            .container { max-width: 1400px; margin: 0 auto; background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); }
            .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #e5e7eb; }
            .header h1 { font-size: 28px; color: #1f2937; }
            .btn { padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600; transition: all 0.3s; }
            .btn-back { background: #6b7280; color: white; }
            .btn-back:hover { background: #4b5563; }
            .calendar-nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
            .calendar-nav h2 { font-size: 24px; color: #1f2937; }
            .nav-buttons { display: flex; gap: 10px; }
            .nav-buttons a { padding: 8px 16px; background: #8b5cf6; color: white; border-radius: 6px; text-decoration: none; }
            .nav-buttons a:hover { background: #7c3aed; }
            .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; }
            .calendar-header { background: #8b5cf6; color: white; padding: 15px; text-align: center; font-weight: 600; border-radius: 8px; }
            .calendar-day { background: #f9fafb; padding: 10px; border-radius: 8px; min-height: 120px; position: relative; }
            .calendar-day.empty { background: #e5e7eb; }
            .calendar-day .day-number { font-weight: 600; color: #1f2937; margin-bottom: 8px; }
            .leave-tag { padding: 4px 8px; border-radius: 4px; font-size: 11px; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .leave-vacation { background: #dbeafe; color: #1e40af; }
            .leave-sick { background: #fee2e2; color: #991b1b; }
            .leave-personal { background: #fed7aa; color: #9a3412; }
            .leave-unpaid { background: #e5e7eb; color: #374151; }
            .legend { display: flex; gap: 20px; justify-content: center; margin-top: 20px; padding-top: 20px; border-top: 2px solid #e5e7eb; }
            .legend-item { display: flex; align-items: center; gap: 8px; }
            .legend-color { width: 20px; height: 20px; border-radius: 4px; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-calendar-alt"></i> Leave Calendar</h1>
                <a href="<?= esc_url(home_url('/personnel-panel')) ?>" class="btn btn-back">
                    <i class="fas fa-arrow-left"></i> Back to Personnel
                </a>
            </div>
            
            <div class="calendar-nav">
                <div class="nav-buttons">
                    <a href="?month=<?= $prev_month ?>&year=<?= $prev_year ?>">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                </div>
                <h2><?= date('F Y', mktime(0, 0, 0, $month, 1, $year)) ?></h2>
                <div class="nav-buttons">
                    <a href="?month=<?= $next_month ?>&year=<?= $next_year ?>">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                </div>
            </div>
            
            <div class="calendar-grid">
                <div class="calendar-header">Sunday</div>
                <div class="calendar-header">Monday</div>
                <div class="calendar-header">Tuesday</div>
                <div class="calendar-header">Wednesday</div>
                <div class="calendar-header">Thursday</div>
                <div class="calendar-header">Friday</div>
                <div class="calendar-header">Saturday</div>
                
                <?php
                // Empty cells for days before month starts
                for ($i = 0; $i < $start_day_of_week; $i++) {
                    echo '<div class="calendar-day empty"></div>';
                }
                
                // Days of the month
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date_key = sprintf('%04d-%02d-%02d', $year, $month, $day);
                    $has_leave = isset($calendar_data[$date_key]);
                    
                    echo '<div class="calendar-day">';
                    echo '<div class="day-number">' . $day . '</div>';
                    
                    if ($has_leave) {
                        foreach ($calendar_data[$date_key] as $leave) {
                            $class = 'leave-' . esc_attr($leave['type']);
                            echo '<div class="leave-tag ' . $class . '">' . esc_html($leave['name']) . '</div>';
                        }
                    }
                    
                    echo '</div>';
                }
                ?>
            </div>
            
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background:#dbeafe;"></div>
                    <span>Vacation</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background:#fee2e2;"></div>
                    <span>Sick Leave</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background:#fed7aa;"></div>
                    <span>Personal</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background:#e5e7eb;"></div>
                    <span>Unpaid</span>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

// Leave History (placeholder - can be added to detail view tabs)
function b2b_personnel_leave_history() {
    $personnel_id = get_query_var('personnel_id');
    wp_redirect(home_url('/personnel-panel/view/' . $personnel_id . '#leave-history'));
    exit;
}

// Leave Accounting Export (placeholder for future accounting integration)
function b2b_personnel_leave_accounting_export() {
    // Get all approved but not posted leave requests
    $personnel_args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1
    ];
    
    $all_personnel = get_posts($personnel_args);
    $export_data = [];
    
    foreach ($all_personnel as $person) {
        $leave_requests = get_post_meta($person->ID, '_leave_requests', true);
        if (is_array($leave_requests)) {
            foreach ($leave_requests as $request) {
                if ($request['status'] === 'approved' && !$request['posted_to_accounting']) {
                    $export_data[] = [
                        'Leave ID' => $request['id'],
                        'Employee' => $person->post_title,
                        'Employee ID' => $person->ID,
                        'Leave Type' => ucfirst($request['leave_type']),
                        'Start Date' => $request['start_date'],
                        'End Date' => $request['end_date'],
                        'Total Days' => $request['total_days'],
                        'Pay Impact' => $request['pay_impact'],
                        'Approved Date' => $request['approval_date']
                    ];
                }
            }
        }
    }
    
    if (empty($export_data)) {
        wp_redirect(home_url('/personnel-panel?msg=no_leaves_to_export'));
        exit;
    }
    
    // Export as CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=leave-accounting-export-' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, array_keys($export_data[0]));
    
    // CSV data
    foreach ($export_data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// ========================================
// PAYROLL PAYMENT TRACKING SYSTEM
// ========================================

// Payroll Payments Dashboard
function b2b_personnel_payroll_payments() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    // Get current month
    $current_month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : date('Y-m');
    
    // Get all personnel
    $personnel_args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1,
        'meta_key' => '_employee_status',
        'meta_value' => 'active'
    ];
    
    $all_personnel = get_posts($personnel_args);
    
    $payroll_data = [];
    $total_accrued = 0;
    $total_paid = 0;
    $total_balance = 0;
    
    foreach ($all_personnel as $person) {
        $accrued = b2b_calculate_monthly_accrual($person->ID, $current_month);
        $paid = b2b_calculate_monthly_payments($person->ID, $current_month);
        $balance = b2b_get_payment_balance($person->ID);
        
        $payroll_data[] = [
            'id' => $person->ID,
            'name' => $person->post_title,
            'accrued' => $accrued,
            'paid' => $paid,
            'balance' => $balance
        ];
        
        $total_accrued += $accrued;
        $total_paid += $paid;
        $total_balance += $balance;
    }
    
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payroll Payments Dashboard</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 1400px;
                margin: 0 auto;
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 30px;
            }
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #e5e7eb;
            }
            h1 { color: #1f2937; font-size: 28px; }
            .summary-cards {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
            }
            .summary-card {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 20px;
                border-radius: 10px;
                color: white;
            }
            .summary-card h3 { font-size: 14px; opacity: 0.9; margin-bottom: 10px; }
            .summary-card .amount { font-size: 28px; font-weight: bold; }
            .summary-card.green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
            .summary-card.red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
            .btn {
                padding: 10px 20px;
                border-radius: 8px;
                text-decoration: none;
                color: white;
                font-weight: 500;
                display: inline-block;
                transition: all 0.3s;
            }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
            .btn-primary { background: #10b981; }
            .btn-secondary { background: #6366f1; }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
            }
            th {
                background: #f3f4f6;
                font-weight: 600;
                color: #374151;
            }
            tr:hover { background: #f9fafb; }
            .balance-positive { color: #ef4444; font-weight: bold; }
            .balance-zero { color: #10b981; font-weight: bold; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1><i class="fas fa-money-check-alt"></i> Payroll Payments Dashboard</h1>
                <div>
                    <a href="<?= home_url('/personnel-panel') ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Personnel
                    </a>
                    <a href="<?= home_url('/personnel-panel/payroll-accounting-export') ?>" class="btn btn-primary">
                        <i class="fas fa-file-export"></i> Export for Accounting
                    </a>
                </div>
            </div>
            
            <div class="summary-cards">
                <div class="summary-card">
                    <h3>Total Accrued (This Month)</h3>
                    <div class="amount">$<?= number_format($total_accrued, 2) ?></div>
                </div>
                <div class="summary-card green">
                    <h3>Total Paid (This Month)</h3>
                    <div class="amount">$<?= number_format($total_paid, 2) ?></div>
                </div>
                <div class="summary-card red">
                    <h3>Outstanding Balance</h3>
                    <div class="amount">$<?= number_format($total_balance, 2) ?></div>
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Accrued (This Month)</th>
                        <th>Paid (This Month)</th>
                        <th>Balance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($payroll_data as $data): ?>
                        <tr>
                            <td><?= esc_html($data['name']) ?></td>
                            <td>$<?= number_format($data['accrued'], 2) ?></td>
                            <td>$<?= number_format($data['paid'], 2) ?></td>
                            <td class="<?= $data['balance'] > 0 ? 'balance-positive' : 'balance-zero' ?>">
                                $<?= number_format($data['balance'], 2) ?>
                            </td>
                            <td>
                                <a href="<?= home_url('/personnel-panel/add-payment/' . $data['id']) ?>" class="btn btn-primary" style="padding: 6px 12px; font-size: 14px;">
                                    <i class="fas fa-money-bill-wave"></i> Add Payment
                                </a>
                                <a href="<?= home_url('/personnel-panel/payment-history/' . $data['id']) ?>" class="btn btn-secondary" style="padding: 6px 12px; font-size: 14px;">
                                    <i class="fas fa-history"></i> History
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Calculate monthly salary accrual based on attendance
function b2b_calculate_monthly_accrual($personnel_id, $month) {
    $salary = floatval(get_post_meta($personnel_id, '_salary', true));
    $pay_type = get_post_meta($personnel_id, '_pay_type', true);
    $pay_rate = floatval(get_post_meta($personnel_id, '_pay_rate', true));
    
    if (empty($salary) && empty($pay_rate)) {
        return 0;
    }
    
    // Get attendance for the month
    $attendance = get_post_meta($personnel_id, '_attendance', true);
    if (!is_array($attendance)) {
        return 0;
    }
    
    $days_worked = 0;
    $hours_worked = 0;
    $overtime_hours = 0;
    
    foreach ($attendance as $record) {
        $record_date = date('Y-m', strtotime($record['date']));
        if ($record_date === $month) {
            $days_worked++;
            if (isset($record['clock_in']) && isset($record['clock_out'])) {
                $in = strtotime($record['clock_in']);
                $out = strtotime($record['clock_out']);
                $hours = ($out - $in) / 3600;
                $hours_worked += $hours;
                if ($hours > 8) {
                    $overtime_hours += ($hours - 8);
                }
            }
        }
    }
    
    // Calculate accrual
    if ($pay_type === 'hourly') {
        $regular_pay = $hours_worked * $pay_rate;
        $overtime_pay = $overtime_hours * $pay_rate * 1.5;
        return $regular_pay + $overtime_pay;
    } else {
        // Salaried - calculate based on days worked
        $working_days = date('t', strtotime($month . '-01'));
        return ($salary / $working_days) * $days_worked;
    }
}

// Calculate total payments made in a month
function b2b_calculate_monthly_payments($personnel_id, $month) {
    $payments = get_post_meta($personnel_id, '_payment_records', true);
    if (!is_array($payments)) {
        return 0;
    }
    
    $total = 0;
    foreach ($payments as $payment) {
        $payment_month = date('Y-m', strtotime($payment['payment_date']));
        if ($payment_month === $month) {
            $total += floatval($payment['amount']);
        }
    }
    
    return $total;
}

// Get current payment balance for an employee
function b2b_get_payment_balance($personnel_id) {
    $balance = floatval(get_post_meta($personnel_id, '_payment_balance', true));
    return $balance;
}

// Add Payment Form
function b2b_personnel_add_payment($personnel_id) {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    $personnel = get_post($personnel_id);
    if (!$personnel) {
        wp_die('Personnel not found');
    }
    
    $current_balance = b2b_get_payment_balance($personnel_id);
    $current_accrual = b2b_calculate_monthly_accrual($personnel_id, date('Y-m'));
    
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Add Payment - <?= esc_html($personnel->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 30px;
            }
            h1 { color: #1f2937; margin-bottom: 20px; }
            .info-box {
                background: #f3f4f6;
                padding: 15px;
                border-radius: 8px;
                margin-bottom: 20px;
            }
            .info-box p { margin: 5px 0; }
            .form-group {
                margin-bottom: 20px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: 600;
                color: #374151;
            }
            input, select, textarea {
                width: 100%;
                padding: 10px;
                border: 1px solid #d1d5db;
                border-radius: 8px;
                font-size: 14px;
            }
            .btn {
                padding: 12px 24px;
                border-radius: 8px;
                text-decoration: none;
                color: white;
                font-weight: 500;
                display: inline-block;
                transition: all 0.3s;
                border: none;
                cursor: pointer;
            }
            .btn-primary { background: #10b981; }
            .btn-secondary { background: #6366f1; }
            .btn:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><i class="fas fa-money-bill-wave"></i> Add Payment for <?= esc_html($personnel->post_title) ?></h1>
            
            <div class="info-box">
                <p><strong>Current Balance:</strong> $<?= number_format($current_balance, 2) ?></p>
                <p><strong>Accrued This Month:</strong> $<?= number_format($current_accrual, 2) ?></p>
            </div>
            
            <form method="POST" action="<?= home_url('/personnel-panel/process-payment') ?>">
                <input type="hidden" name="personnel_id" value="<?= $personnel_id ?>">
                
                <div class="form-group">
                    <label>Payment Date</label>
                    <input type="date" name="payment_date" required value="<?= date('Y-m-d') ?>">
                </div>
                
                <div class="form-group">
                    <label>Transaction Type (İşlem Türü)</label>
                    <select name="transaction_type" id="transaction_type" required onchange="updateCategories()">
                        <option value="">Select...</option>
                        <option value="income">Income (Gelir)</option>
                        <option value="expense" selected>Expense (Gider)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Category (Kategori)</label>
                    <select name="category" id="category" required>
                        <option value="">Select transaction type first...</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Amount ($)</label>
                    <input type="number" name="amount" step="0.01" required min="0">
                </div>
                
                <div class="form-group" id="payment_method_field">
                    <label>Payment Method</label>
                    <select name="payment_method" id="payment_method">
                        <option value="">Select...</option>
                        <option value="cash">Cash</option>
                        <option value="check">Check</option>
                        <option value="direct_deposit">Direct Deposit</option>
                        <option value="wire_transfer">Wire Transfer</option>
                    </select>
                </div>
                
                <script>
                const incomeCategories = {
                    'accrued_salary': 'Accrued Salary (Maaş Tahakkuku)',
                    'bonus': 'Bonus',
                    'commission': 'Commission',
                    'allowance': 'Allowance (Ödenek)',
                    'overtime_pay': 'Overtime Pay (Mesai Ücreti)',
                    'other_income': 'Other Income (Diğer Gelir)'
                };
                
                const expenseCategories = {
                    'salary_payment': 'Salary Payment (Maaş Ödemesi)',
                    'deduction': 'Deduction (Kesinti)',
                    'advance_payment': 'Advance Payment (Avans)',
                    'tax_withholding': 'Tax Withholding (Vergi Kesintisi)',
                    'insurance_deduction': 'Insurance Deduction (Sigorta Kesintisi)',
                    'other_expense': 'Other Expense (Diğer Gider)'
                };
                
                function updateCategories() {
                    const transactionType = document.getElementById('transaction_type').value;
                    const categorySelect = document.getElementById('category');
                    const paymentMethodField = document.getElementById('payment_method_field');
                    const paymentMethodSelect = document.getElementById('payment_method');
                    
                    categorySelect.innerHTML = '<option value="">Select...</option>';
                    
                    if (transactionType === 'income') {
                        Object.keys(incomeCategories).forEach(key => {
                            const option = document.createElement('option');
                            option.value = key;
                            option.textContent = incomeCategories[key];
                            categorySelect.appendChild(option);
                        });
                        paymentMethodField.style.display = 'none';
                        paymentMethodSelect.removeAttribute('required');
                    } else if (transactionType === 'expense') {
                        Object.keys(expenseCategories).forEach(key => {
                            const option = document.createElement('option');
                            option.value = key;
                            option.textContent = expenseCategories[key];
                            categorySelect.appendChild(option);
                        });
                        paymentMethodField.style.display = 'block';
                        paymentMethodSelect.setAttribute('required', 'required');
                    }
                }
                
                // Initialize on page load
                document.addEventListener('DOMContentLoaded', function() {
                    updateCategories();
                });
                </script>
                
                <div class="form-group">
                    <label>Reference Number (Check #, Transaction ID, etc.)</label>
                    <input type="text" name="reference_number">
                </div>
                
                <div class="form-group">
                    <label>Notes</label>
                    <textarea name="notes" rows="3"></textarea>
                </div>
                
                <div style="display: flex; gap: 10px;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Record Payment
                    </button>
                    <a href="<?= home_url('/personnel-panel/payroll-payments') ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Process Payment/Transaction
function b2b_personnel_process_payment() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    $personnel_id = intval($_POST['personnel_id']);
    $amount = floatval($_POST['amount']);
    $payment_date = sanitize_text_field($_POST['payment_date']);
    $transaction_type = sanitize_text_field($_POST['transaction_type']); // income or expense
    $category = sanitize_text_field($_POST['category']);
    $payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';
    $reference_number = sanitize_text_field($_POST['reference_number']);
    $notes = sanitize_textarea_field($_POST['notes']);
    
    // Calculate signed amount (positive for income, negative for expense)
    $amount_signed = ($transaction_type === 'income') ? $amount : -$amount;
    
    // Get current balance
    $current_balance = b2b_get_payment_balance($personnel_id);
    
    // Create transaction record
    $payment = [
        'id' => uniqid('txn_'),
        'employee_id' => $personnel_id,
        'date' => $payment_date,
        'transaction_type' => $transaction_type,
        'category' => $category,
        'amount' => $amount,
        'amount_signed' => $amount_signed,
        'payment_method' => $payment_method,
        'reference_number' => $reference_number,
        'month' => date('Y-m', strtotime($payment_date)),
        'balance_before' => $current_balance,
        'balance_after' => $current_balance + $amount_signed,
        'notes' => $notes,
        'description' => $notes,
        // Accounting fields
        'debit_credit' => ($transaction_type === 'income') ? 'debit' : 'credit',
        'expense_category' => 'Payroll Expense',
        'gl_code' => null,
        'posted_to_accounting' => false,
        'accounting_entry_id' => null,
        'recorded_by' => get_current_user_id(),
        'recorded_date' => current_time('mysql'),
        // Accounting integration fields
        'expense_category' => 'Payroll Expense',
        'gl_code' => null,
        'posted_to_accounting' => false,
        'accounting_entry_id' => null
    ];
    
    // Save payment record
    $payments = get_post_meta($personnel_id, '_payment_records', true);
    if (!is_array($payments)) {
        $payments = [];
    }
    $payments[] = $payment;
    update_post_meta($personnel_id, '_payment_records', $payments);
    
    // Update balance
    $new_balance = $current_balance + $accrued - $amount;
    update_post_meta($personnel_id, '_payment_balance', $new_balance);
    
    // Log activity
    b2b_log_personnel_activity($personnel_id, 'payment_added', "Payment of $" . number_format($amount, 2) . " recorded");
    
    wp_redirect(home_url('/personnel-panel/payroll-payments?msg=payment_added'));
    exit;
}

// Payment History
function b2b_personnel_payment_history($personnel_id) {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    $personnel = get_post($personnel_id);
    if (!$personnel) {
        wp_die('Personnel not found');
    }
    
    $payments = get_post_meta($personnel_id, '_payment_records', true);
    if (!is_array($payments)) {
        $payments = [];
    }
    
    // Sort by date descending
    usort($payments, function($a, $b) {
        return strtotime($b['payment_date']) - strtotime($a['payment_date']);
    });
    
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Payment History - <?= esc_html($personnel->post_title) ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 20px;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                background: white;
                border-radius: 15px;
                box-shadow: 0 20px 60px rgba(0,0,0,0.3);
                padding: 30px;
            }
            h1 { color: #1f2937; margin-bottom: 20px; }
            .btn {
                padding: 10px 20px;
                border-radius: 8px;
                text-decoration: none;
                color: white;
                font-weight: 500;
                display: inline-block;
                transition: all 0.3s;
                background: #6366f1;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #e5e7eb;
            }
            th {
                background: #f3f4f6;
                font-weight: 600;
                color: #374151;
            }
            tr:hover { background: #f9fafb; }
        </style>
    </head>
    <body>
        <div class="container">
            <h1><i class="fas fa-history"></i> Payment History - <?= esc_html($personnel->post_title) ?></h1>
            <a href="<?= home_url('/personnel-panel/payroll-payments') ?>" class="btn">
                <i class="fas fa-arrow-left"></i> Back to Payroll
            </a>
            
            <?php if (empty($payments)): ?>
                <p>No payment records found.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Balance After</th>
                            <th>Recorded By</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                            <?php 
                            $payment_type = isset($payment['payment_type']) ? $payment['payment_type'] : 'salary';
                            $type_colors = [
                                'salary' => '#3b82f6',
                                'bonus' => '#10b981',
                                'commission' => '#8b5cf6',
                                'allowance' => '#f59e0b',
                                'reimbursement' => '#14b8a6',
                                'adjustment' => '#6b7280',
                                'other' => '#6b7280'
                            ];
                            $type_color = $type_colors[$payment_type] ?? '#6b7280';
                            ?>
                            <tr>
                                <td><?= date('M d, Y', strtotime($payment['payment_date'])) ?></td>
                                <td><span style="background:<?= $type_color ?>;color:#fff;padding:4px 8px;border-radius:4px;font-size:12px;font-weight:600;"><?= ucfirst($payment_type) ?></span></td>
                                <td>$<?= number_format($payment['amount'], 2) ?></td>
                                <td><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></td>
                                <td><?= esc_html($payment['reference_number'] ?? '-') ?></td>
                                <td>$<?= number_format($payment['balance_after'] ?? 0, 2) ?></td>
                                <td><?php 
                                    $user = get_userdata($payment['recorded_by']);
                                    echo $user ? esc_html($user->display_name) : 'System';
                                ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Payroll Accounting Export
function b2b_personnel_payroll_accounting_export() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized access');
    }
    
    // Get all payment records not yet posted to accounting
    $personnel_args = [
        'post_type' => 'b2b_personel',
        'posts_per_page' => -1
    ];
    
    $all_personnel = get_posts($personnel_args);
    $export_data = [];
    
    foreach ($all_personnel as $person) {
        $payments = get_post_meta($person->ID, '_payment_records', true);
        if (is_array($payments)) {
            foreach ($payments as $payment) {
                if (!$payment['posted_to_accounting']) {
                    $export_data[] = [
                        'Payment ID' => $payment['id'],
                        'Employee' => $person->post_title,
                        'Employee ID' => $person->ID,
                        'Payment Date' => $payment['payment_date'],
                        'Amount' => $payment['amount'],
                        'Payment Method' => ucfirst(str_replace('_', ' ', $payment['payment_method'])),
                        'Reference Number' => $payment['reference_number'] ?? '',
                        'Expense Category' => $payment['expense_category'],
                        'Month' => $payment['month']
                    ];
                }
            }
        }
    }
    
    if (empty($export_data)) {
        wp_redirect(home_url('/personnel-panel/payroll-payments?msg=no_payments_to_export'));
        exit;
    }
    
    // Export as CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=payroll-accounting-export-' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, array_keys($export_data[0]));
    
    // CSV data
    foreach ($export_data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}


/* =====================================================
 * EDIT PAYMENT
 * ===================================================== */
function b2b_personnel_edit_payment($payment_id) {
    if (!$payment_id) {
        wp_redirect(home_url("/personnel-panel"));
        exit;
    }
    
    // Find the payment across all personnel
    $args = ["post_type" => "b2b_personel", "posts_per_page" => -1];
    $personnel_list = get_posts($args);
    
    $found_personnel_id = null;
    $payment_to_edit = null;
    
    foreach ($personnel_list as $person) {
        $payment_records = get_post_meta($person->ID, "_payment_records", true) ?: [];
        foreach ($payment_records as $payment) {
            if ($payment["id"] === $payment_id) {
                $found_personnel_id = $person->ID;
                $payment_to_edit = $payment;
                break 2;
            }
        }
    }
    
    if (!$payment_to_edit) {
        wp_redirect(home_url("/personnel-panel"));
        exit;
    }
    
    $personnel_name = get_the_title($found_personnel_id);
    
    // Handle form submission
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["update_payment"])) {
        $payment_date = sanitize_text_field($_POST["payment_date"]);
        $payment_type = sanitize_text_field($_POST["payment_type"]);
        $amount = floatval($_POST["amount"]);
        $payment_method = sanitize_text_field($_POST["payment_method"]);
        $reference_number = sanitize_text_field($_POST["reference_number"]);
        $notes = sanitize_textarea_field($_POST["notes"]);
        
        // Get all payment records
        $payment_records = get_post_meta($found_personnel_id, "_payment_records", true) ?: [];
        
        // Find and update the payment
        foreach ($payment_records as $key => $payment) {
            if ($payment["id"] === $payment_id) {
                // Recalculate balance
                $current_balance = b2b_get_payment_balance($found_personnel_id);
                $old_amount = $payment["amount"];
                $balance_diff = $amount - $old_amount;
                
                $payment_records[$key]["payment_date"] = $payment_date;
                $payment_records[$key]["payment_type"] = $payment_type;
                $payment_records[$key]["amount"] = $amount;
                $payment_records[$key]["payment_method"] = $payment_method;
                $payment_records[$key]["reference_number"] = $reference_number;
                $payment_records[$key]["notes"] = $notes;
                $payment_records[$key]["balance_after"] = $payment["balance_after"] - $balance_diff;
                
                break;
            }
        }
        
        update_post_meta($found_personnel_id, "_payment_records", $payment_records);
        
        // Recalculate and update balance
        $new_balance = $current_balance + ($payment_to_edit["amount"] - $amount);
        update_post_meta($found_personnel_id, "_payment_balance", $new_balance);
        
        // Log activity
        b2b_log_personnel_activity($found_personnel_id, "payment_edited", "Payment record edited: $" . number_format($amount, 2));
        
        wp_redirect(home_url("/personnel-panel/view/" . $found_personnel_id));
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Edit Payment - Personnel Panel</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; background: #f3f4f6; }
            .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
            .card { background: #fff; border-radius: 12px; padding: 30px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
            h1 { font-size: 24px; color: #111827; margin-bottom: 10px; }
            .subtitle { color: #6b7280; margin-bottom: 30px; }
            .form-group { margin-bottom: 20px; }
            label { display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 14px; }
            input, select, textarea { width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 14px; }
            textarea { resize: vertical; min-height: 80px; }
            .btn-group { display: flex; gap: 15px; margin-top: 30px; }
            .btn { padding: 12px 24px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
            .btn-primary { background: #3b82f6; color: #fff; }
            .btn-secondary { background: #6b7280; color: #fff; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card">
                <h1><i class="fas fa-edit"></i> Edit Payment</h1>
                <p class="subtitle">Personnel: <?= esc_html($personnel_name) ?></p>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Payment Date *</label>
                        <input type="date" name="payment_date" value="<?= esc_attr($payment_to_edit["payment_date"]) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Type *</label>
                        <select name="payment_type" required>
                            <?php
                            $current_type = isset($payment_to_edit["payment_type"]) ? $payment_to_edit["payment_type"] : 'salary';
                            $payment_types = [
                                'salary' => 'Salary',
                                'bonus' => 'Bonus',
                                'commission' => 'Commission',
                                'allowance' => 'Allowance',
                                'reimbursement' => 'Reimbursement',
                                'adjustment' => 'Adjustment',
                                'other' => 'Other'
                            ];
                            foreach ($payment_types as $value => $label) {
                                $selected = ($current_type === $value) ? 'selected' : '';
                                echo "<option value=\"$value\" $selected>$label</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Amount *</label>
                        <input type="number" name="amount" step="0.01" min="0" value="<?= esc_attr($payment_to_edit["amount"]) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Payment Method *</label>
                        <select name="payment_method" required>
                            <option value="cash" <?= $payment_to_edit["payment_method"] === "cash" ? "selected" : "" ?>>Cash</option>
                            <option value="check" <?= $payment_to_edit["payment_method"] === "check" ? "selected" : "" ?>>Check</option>
                            <option value="direct_deposit" <?= $payment_to_edit["payment_method"] === "direct_deposit" ? "selected" : "" ?>>Direct Deposit</option>
                            <option value="wire_transfer" <?= $payment_to_edit["payment_method"] === "wire_transfer" ? "selected" : "" ?>>Wire Transfer</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Reference Number</label>
                        <input type="text" name="reference_number" value="<?= esc_attr($payment_to_edit["reference_number"]) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes"><?= esc_textarea($payment_to_edit["notes"]) ?></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="update_payment" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Payment
                        </button>
                        <a href="<?= home_url("/personnel-panel/view/" . $found_personnel_id) ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

/* =====================================================
 * DELETE PAYMENT
 * ===================================================== */
function b2b_personnel_delete_payment($payment_id) {
    if (!$payment_id) {
        wp_redirect(home_url("/personnel-panel"));
        exit;
    }
    
    // Find the payment across all personnel
    $args = ["post_type" => "b2b_personel", "posts_per_page" => -1];
    $personnel_list = get_posts($args);
    
    $found_personnel_id = null;
    $payment_to_delete = null;
    
    foreach ($personnel_list as $person) {
        $payment_records = get_post_meta($person->ID, "_payment_records", true) ?: [];
        foreach ($payment_records as $key => $payment) {
            if ($payment["id"] === $payment_id) {
                $found_personnel_id = $person->ID;
                $payment_to_delete = $payment;
                break 2;
            }
        }
    }
    
    if (!$payment_to_delete) {
        wp_redirect(home_url("/personnel-panel"));
        exit;
    }
    
    // Get all payment records
    $payment_records = get_post_meta($found_personnel_id, "_payment_records", true) ?: [];
    
    // Remove the payment
    $payment_records = array_filter($payment_records, function($payment) use ($payment_id) {
        return $payment["id"] !== $payment_id;
    });
    
    // Reindex array
    $payment_records = array_values($payment_records);
    
    update_post_meta($found_personnel_id, "_payment_records", $payment_records);
    
    // Recalculate balance
    $current_balance = b2b_get_payment_balance($found_personnel_id);
    $new_balance = $current_balance + $payment_to_delete["amount"];
    update_post_meta($found_personnel_id, "_payment_balance", $new_balance);
    
    // Log activity
    b2b_log_personnel_activity($found_personnel_id, "payment_deleted", "Payment record deleted: $" . number_format($payment_to_delete["amount"], 2));
    
    wp_redirect(home_url("/personnel-panel/view/" . $found_personnel_id));
    exit;
}

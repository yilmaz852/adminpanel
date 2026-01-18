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
}

add_filter('query_vars', 'b2b_personnel_query_vars');
function b2b_personnel_query_vars($vars) {
    $vars[] = 'personnel_panel';
    $vars[] = 'personnel_id';
    $vars[] = 'department_id';
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
                                ?>
                                <tr>
                                    <td>
                                        <input type="checkbox" class="personnel-checkbox" value="<?= $id ?>" onchange="updateBulkActions()">
                                    </td>
                                    <td>
                                        <a href="<?= home_url('/personnel-panel/view/' . $id) ?>" style="color:#3b82f6;font-weight:600;text-decoration:none;">
                                            <?= get_the_title() ?>
                                        </a>
                                        <?php if ($clocked_in): ?>
                                            <span style="background:#10b981;color:#fff;padding:2px 8px;border-radius:12px;font-size:11px;margin-left:8px;">
                                                <i class="fas fa-circle" style="font-size:6px;"></i> Active
                                            </span>
                                        <?php endif; ?>
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
                                            <?php if (!$clocked_in): ?>
                                                <a href="<?= home_url('/personnel-panel/clock-in/' . $id) ?>" class="btn btn-edit" style="background:#10b981;" title="Clock In">
                                                    <i class="fas fa-sign-in-alt"></i>
                                                </a>
                                            <?php else: ?>
                                                <a href="<?= home_url('/personnel-panel/clock-out/' . $id) ?>" class="btn btn-delete" title="Clock Out">
                                                    <i class="fas fa-sign-out-alt"></i>
                                                </a>
                                            <?php endif; ?>
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
            }
            
            if ($result && !is_wp_error($result)) {
                update_post_meta($personnel_id, '_gorev', $gorev);
                update_post_meta($personnel_id, '_eposta', $eposta);
                update_post_meta($personnel_id, '_telefon', $telefon);
                update_post_meta($personnel_id, '_maas', $maas);
                update_post_meta($personnel_id, '_baslangic_tarihi', $baslangic);
                
                if ($department) {
                    wp_set_object_terms($personnel_id, $department, 'b2b_departman');
                }
                
                // Log activity
                if ($is_edit) {
                    b2b_log_personnel_activity($personnel_id, 'personnel_edited', 'Personnel information updated');
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
    } else {
        $name = $gorev = $eposta = $telefon = $baslangic = '';
        $maas = 0;
        $current_dept = 0;
    }
    
    // Get departments
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
            
            @media (max-width: 768px) {
                .header { flex-direction: column; gap: 1rem; }
                .form-card { padding: 1.5rem; }
                .form-actions { flex-direction: column; }
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
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Ad Soyad *</label>
                        <input type="text" name="name" value="<?= esc_attr($name) ?>" required>
                    </div>
                    
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
                        <label><i class="fas fa-briefcase"></i> Position / Title</label>
                        <input type="text" name="gorev" value="<?= esc_attr($gorev) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> E-posta</label>
                        <input type="email" name="eposta" value="<?= esc_attr($eposta) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone</label>
                        <input type="tel" name="telefon" value="<?= esc_attr($telefon) ?>">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-money-bill-wave"></i> Salary ($)</label>
                        <input type="number" name="maas" value="<?= esc_attr($maas) ?>" step="0.01">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Start Date</label>
                        <input type="date" name="baslangic_tarihi" value="<?= esc_attr($baslangic) ?>">
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
    
    // Get personnel data
    $gorev = get_post_meta($personnel_id, '_gorev', true);
    $eposta = get_post_meta($personnel_id, '_eposta', true);
    $telefon = get_post_meta($personnel_id, '_telefon', true);
    $maas = get_post_meta($personnel_id, '_maas', true);
    $baslangic = get_post_meta($personnel_id, '_baslangic_tarihi', true);
    $depts = get_the_terms($personnel_id, 'b2b_departman');
    $dept_name = $depts && !is_wp_error($depts) ? $depts[0]->name : 'N/A';
    
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
            <div>
                <h1 style="margin:0 0 5px 0;font-size:28px;color:#111827;"><?= esc_html($person->post_title) ?></h1>
                <p style="margin:0;color:#6b7280;"><?= esc_html($gorev) ?> • <?= esc_html($dept_name) ?></p>
            </div>
            <div style="display:flex;gap:10px;">
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
            </div>
        </div>

        <!-- Tab Content: Information -->
        <div id="tab-info" class="tab-content">
            <div style="background:#fff;border-radius:12px;padding:30px;box-shadow:0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin:0 0 20px 0;font-size:20px;color:#111827;">Employee Details</h2>
                <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:20px;">
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Full Name</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($person->post_title) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Department</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($dept_name) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Position/Title</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($gorev) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Email</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($eposta) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Phone</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($telefon) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Salary</label>
                        <p style="margin:0;font-size:16px;color:#111827;">$<?= number_format((float)$maas, 2) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Start Date</label>
                        <p style="margin:0;font-size:16px;color:#111827;"><?= esc_html($baslangic) ?></p>
                    </div>
                    <div>
                        <label style="display:block;margin-bottom:5px;color:#6b7280;font-size:14px;">Status</label>
                        <p style="margin:0;font-size:16px;">
                            <?php if ($clocked_in): ?>
                                <span style="background:#10b981;color:#fff;padding:4px 12px;border-radius:20px;font-size:14px;">
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
                                    </tr>
                                <?php endforeach; ?>
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
        <div class="content-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h1 style="margin:0;font-size:28px;color:#111827;">Attendance Dashboard</h1>
            <a href="<?= home_url('/personnel-panel') ?>" class="add-btn" style="background:#6b7280;">
                <i class="fas fa-arrow-left"></i> Back to Personnel
            </a>
        </div>

        <!-- Date Selector -->
        <div style="background:#fff;border-radius:12px;padding:20px;box-shadow:0 1px 3px rgba(0,0,0,0.1);margin-bottom:20px;">
            <form method="GET" style="display:flex;gap:15px;align-items:end;">
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
                                        <a href="<?= home_url('/personnel-panel/clock-in/' . $person->ID . '?date=' . $selected_date) ?>" class="btn btn-edit" style="background:#10b981;">
                                            <i class="fas fa-sign-in-alt"></i> Clock In
                                        </a>
                                    <?php elseif ($is_clocked_in): ?>
                                        <a href="<?= home_url('/personnel-panel/clock-out/' . $person->ID . '?date=' . $selected_date) ?>" class="btn btn-delete" style="background:#ef4444;">
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
        return strtotime($b['date']) - strtotime($a['date']);
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
function b2b_log_personnel_activity($personnel_id, $action, $details) {
    $activity = get_post_meta($personnel_id, '_activity_log', true) ?: [];
    
    $activity[] = [
        'action' => $action,
        'details' => $details,
        'user_id' => get_current_user_id(),
        'date' => current_time('mysql')
    ];
    
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

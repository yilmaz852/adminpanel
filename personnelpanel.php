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
    add_rewrite_rule('^personnel-panel/departments/?$', 'index.php?personnel_panel=departments', 'top');
    add_rewrite_rule('^personnel-panel/department-delete/([0-9]+)/?$', 'index.php?personnel_panel=department_delete&department_id=$matches[1]', 'top');
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
        default:
            wp_redirect(home_url('/personnel-panel'));
            exit;
    }
    exit;
}

/* =====================================================
 * 4. PERSONNEL LIST PAGE
 * ===================================================== */
function b2b_personnel_list_page() {
    // Handle search and filters
    $search = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
    $department = isset($_GET['department']) ? sanitize_text_field($_GET['department']) : '';
    
    // Query personnel
    $args = [
        'post_type'      => 'b2b_personel',
        'posts_per_page' => 50,
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
                <div style="display: flex; gap: 0.5rem;">
                    <a href="<?= home_url('/personnel-panel/departments') ?>" class="add-btn" style="background: #10b981;">
                        <i class="fas fa-building"></i> Departments
                    </a>
                    <a href="<?= home_url('/personnel-panel/add') ?>" class="add-btn">
                        <i class="fas fa-plus"></i> Add New Personnel
                    </a>
                </div>
            </div>
            
            <div class="table-container">
                <?php if ($personnel_query->have_posts()): ?>
                    <table>
                        <thead>
                            <tr>
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
                                ?>
                                <tr>
                                    <td><strong><?= get_the_title() ?></strong></td>
                                    <td><span class="badge"><?= esc_html($dept_name) ?></span></td>
                                    <td><?= esc_html($gorev ?: '-') ?></td>
                                    <td><?= esc_html($eposta ?: '-') ?></td>
                                    <td><?= esc_html($telefon ?: '-') ?></td>
                                    <td><?= $maas ? '$' . number_format($maas, 0, '.', ',') : '-' ?></td>
                                    <td><?= $baslangic ? date('m/d/Y', strtotime($baslangic)) : '-' ?></td>
                                    <td>
                                        <div class="actions">
                                            <a href="<?= home_url('/personnel-panel/edit/' . $id) ?>" class="btn btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="<?= home_url('/personnel-panel/delete/' . $id) ?>" 
                                               class="btn btn-delete" 
                                               onclick="return confirm('Are you sure you want to delete this personnel?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
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
            <a href="/personnel-panel" class="back-btn">
                <i class="fas fa-arrow-left"></i> Personel Listesi
            </a>
        </div>
        
        <div class="container">
            <div class="form-card">
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i> 
                        Personel başarıyla <?= $is_edit ? 'güncellendi' : 'eklendi' ?>!
                        <a href="/personnel-panel" style="margin-left: 1rem; color: #065f46; text-decoration: underline;">
                            Listeye dön
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
            <a href="/personnel-panel" class="back-btn">
                <i class="fas fa-arrow-left"></i> Personel Listesine Dön
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
    
    wp_redirect('/personnel-panel/departments');
    exit;
}

/* =====================================================
 * 7. FLUSH REWRITE RULES ON ACTIVATION
 * ===================================================== */
register_activation_hook(__FILE__, 'b2b_personnel_flush_rewrites');
function b2b_personnel_flush_rewrites() {
    b2b_register_personnel_post_type();
    b2b_personnel_rewrite_rules();
    flush_rewrite_rules();
}

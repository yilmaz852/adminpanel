<?php
/**
 * Customer Order Panel
 * B2B Customer Portal for Cabinet Orders
 * 
 * Architecture: Follows adminpanel.php hybrid approach
 * - WooCommerce database integration
 * - Custom routing with WordPress rewrite rules
 * - Single-file organization with template_redirect hooks
 * 
 * Version: 1.0.0
 * Created: January 17, 2026
 */

if (!defined('ABSPATH')) exit;

/* =====================================================
   1. ROUTING & URL REWRITE RULES
===================================================== */
add_action('init', function () {
    // Register query var
    add_rewrite_tag('%customer_panel%', '([^&]+)');
    
    // Customer panel routes
    add_rewrite_rule('^customer-login/?$', 'index.php?customer_panel=login', 'top');
    add_rewrite_rule('^customer-panel/?$', 'index.php?customer_panel=dashboard', 'top');
    add_rewrite_rule('^customer-panel/new-order/?$', 'index.php?customer_panel=new-order', 'top');
    add_rewrite_rule('^customer-panel/new-order/category/([^/]+)/?$', 'index.php?customer_panel=products&category=$matches[1]', 'top');
    add_rewrite_rule('^customer-panel/cart/?$', 'index.php?customer_panel=cart', 'top');
    add_rewrite_rule('^customer-panel/checkout/?$', 'index.php?customer_panel=checkout', 'top');
    add_rewrite_rule('^customer-panel/orders/?$', 'index.php?customer_panel=orders', 'top');
    add_rewrite_rule('^customer-panel/order/([0-9]+)/?$', 'index.php?customer_panel=order-detail&order_id=$matches[1]', 'top');
    add_rewrite_rule('^customer-panel/favorites/?$', 'index.php?customer_panel=favorites', 'top');
    add_rewrite_rule('^customer-panel/account/?$', 'index.php?customer_panel=account', 'top');
    
    // Add query vars
    add_rewrite_tag('%category%', '([^&]+)');
    add_rewrite_tag('%order_id%', '([0-9]+)');
});

/* =====================================================
   2. AUTHENTICATION & GUARDS
===================================================== */
function customer_panel_guard() {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_redirect(home_url('/customer-login'));
        exit;
    }
    
    $user = wp_get_current_user();
    
    // Prevent admin and sales agents from accessing customer panel
    if (current_user_can('manage_options') || in_array('sales_agent', $user->roles)) {
        wp_die('Access denied. This panel is for customers only.');
    }
    
    return $user;
}

/* =====================================================
   3. HEADER & FOOTER TEMPLATES
===================================================== */
function customer_panel_header($title = 'Customer Portal') {
    $user = wp_get_current_user();
    ?>
    <!DOCTYPE html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo('charset'); ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
        <title><?php echo esc_html($title); ?> - <?php bloginfo('name'); ?></title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <?php wp_head(); ?>
        <style>
        :root {
            --primary:#0f172a;
            --primary-light:#1e293b;
            --accent:#3b82f6;
            --accent-hover:#2563eb;
            --success:#10b981;
            --warning:#f59e0b;
            --error:#ef4444;
            --bg:#f8fafc;
            --white:#ffffff;
            --border:#e5e7eb;
            --text:#1e293b;
            --text-muted:#64748b;
            --shadow:0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg:0 10px 15px -3px rgba(0,0,0,0.1);
        }
        
        * {margin:0;padding:0;box-sizing:border-box}
        
        body {
            font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;
            background:var(--bg);
            color:var(--text);
            line-height:1.6;
            display:flex;
            min-height:100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position:fixed;
            top:0;
            left:0;
            width:270px;
            height:100vh;
            background:var(--primary);
            color:var(--white);
            overflow-y:auto;
            z-index:1000;
            transition:all 0.3s ease;
        }
        
        .sidebar-header {
            padding:25px 20px;
            border-bottom:1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h1 {
            font-size:20px;
            font-weight:700;
            display:flex;
            align-items:center;
            gap:10px;
        }
        
        .sidebar-nav {
            padding:20px 0;
        }
        
        .nav-item {
            margin:0 15px 5px;
        }
        
        .nav-link {
            display:flex;
            align-items:center;
            gap:12px;
            padding:12px 15px;
            color:rgba(255,255,255,0.8);
            text-decoration:none;
            border-radius:8px;
            transition:all 0.2s ease;
            font-size:14px;
            font-weight:500;
        }
        
        .nav-link:hover {
            background:rgba(255,255,255,0.1);
            color:var(--white);
        }
        
        .nav-link.active {
            background:var(--accent);
            color:var(--white);
        }
        
        .nav-link i {
            width:20px;
            text-align:center;
        }
        
        .sidebar-footer {
            position:absolute;
            bottom:0;
            left:0;
            right:0;
            padding:20px;
            border-top:1px solid rgba(255,255,255,0.1);
        }
        
        .user-info {
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px;
            background:rgba(255,255,255,0.05);
            border-radius:8px;
            margin-bottom:10px;
        }
        
        .user-avatar {
            width:40px;
            height:40px;
            border-radius:50%;
            background:var(--accent);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            font-size:16px;
        }
        
        .user-details {
            flex:1;
            min-width:0;
        }
        
        .user-name {
            font-weight:600;
            font-size:14px;
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }
        
        .user-email {
            font-size:11px;
            color:rgba(255,255,255,0.6);
            white-space:nowrap;
            overflow:hidden;
            text-overflow:ellipsis;
        }
        
        /* Main Content */
        .main {
            margin-left:270px;
            flex:1;
            padding:40px;
            width:calc(100% - 270px);
        }
        
        .page-header {
            margin-bottom:30px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            flex-wrap:wrap;
            gap:15px;
        }
        
        .page-title {
            font-size:28px;
            font-weight:700;
            color:var(--primary);
        }
        
        .card {
            background:var(--white);
            border-radius:12px;
            padding:25px;
            box-shadow:var(--shadow);
            margin-bottom:20px;
        }
        
        /* Buttons */
        button, .btn {
            display:inline-flex;
            align-items:center;
            gap:8px;
            padding:12px 24px;
            background:var(--accent);
            color:var(--white);
            border:none;
            border-radius:8px;
            font-weight:600;
            font-size:14px;
            cursor:pointer;
            text-decoration:none;
            transition:all 0.2s ease;
        }
        
        button:hover, .btn:hover {
            background:var(--accent-hover);
            transform:translateY(-1px);
            box-shadow:var(--shadow-lg);
        }
        
        .btn-secondary {
            background:var(--white);
            color:var(--text);
            border:1px solid var(--border);
        }
        
        .btn-secondary:hover {
            background:var(--bg);
            transform:none;
        }
        
        .btn-success {
            background:var(--success);
        }
        
        .btn-large {
            padding:16px 32px;
            font-size:16px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform:translateX(-100%);
            }
            
            .sidebar.mobile-open {
                transform:translateX(0);
            }
            
            .main {
                margin-left:0;
                width:100%;
                padding:20px;
                padding-top:70px;
            }
            
            .mobile-menu-toggle {
                display:flex !important;
            }
        }
        
        .mobile-menu-toggle {
            display:none;
            position:fixed;
            top:15px;
            left:15px;
            z-index:1001;
            background:var(--primary);
            color:var(--white);
            border:none;
            width:45px;
            height:45px;
            border-radius:8px;
            align-items:center;
            justify-content:center;
            cursor:pointer;
        }
        
        .sidebar-overlay {
            display:none;
            position:fixed;
            top:0;
            left:0;
            right:0;
            bottom:0;
            background:rgba(0,0,0,0.5);
            z-index:999;
        }
        
        .sidebar-overlay.active {
            display:block;
        }
        </style>
    </head>
    <body>
        
        <!-- Mobile Menu Toggle -->
        <button class="mobile-menu-toggle" onclick="toggleCustomerMenu()">
            <i class="fa-solid fa-bars"></i>
        </button>
        
        <!-- Sidebar Overlay -->
        <div class="sidebar-overlay" onclick="toggleCustomerMenu()"></div>
        
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="sidebar-header">
                <h1>
                    <i class="fa-solid fa-store"></i>
                    Customer Portal
                </h1>
            </div>
            
            <nav class="sidebar-nav">
                <div class="nav-item">
                    <a href="<?= home_url('/customer-panel') ?>" class="nav-link <?= get_query_var('customer_panel')=='dashboard'?'active':'' ?>">
                        <i class="fa-solid fa-chart-pie"></i>
                        Dashboard
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= home_url('/customer-panel/new-order') ?>" class="nav-link <?= get_query_var('customer_panel')=='new-order'?'active':'' ?>">
                        <i class="fa-solid fa-cart-plus"></i>
                        New Order
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= home_url('/customer-panel/orders') ?>" class="nav-link <?= get_query_var('customer_panel')=='orders'?'active':'' ?>">
                        <i class="fa-solid fa-box"></i>
                        My Orders
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= home_url('/customer-panel/favorites') ?>" class="nav-link <?= get_query_var('customer_panel')=='favorites'?'active':'' ?>">
                        <i class="fa-solid fa-heart"></i>
                        Favorites
                    </a>
                </div>
                
                <div class="nav-item">
                    <a href="<?= home_url('/customer-panel/account') ?>" class="nav-link <?= get_query_var('customer_panel')=='account'?'active':'' ?>">
                        <i class="fa-solid fa-user"></i>
                        My Account
                    </a>
                </div>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar">
                        <?= strtoupper(substr($user->display_name, 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <div class="user-name"><?= esc_html($user->display_name) ?></div>
                        <div class="user-email"><?= esc_html($user->user_email) ?></div>
                    </div>
                </div>
                <a href="<?= wp_logout_url(home_url('/customer-login')) ?>" class="nav-link" style="color:#ef4444">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    Logout
                </a>
            </div>
        </div>
        
        <div class="main">
        
        <script>
        function toggleCustomerMenu() {
            document.querySelector('.sidebar').classList.toggle('mobile-open');
            document.querySelector('.sidebar-overlay').classList.toggle('active');
        }
        </script>
    <?php
}

function customer_panel_footer() {
    ?>
        </div>
        <?php wp_footer(); ?>
    </body>
    </html>
    <?php
}

/* =====================================================
   4. PAGE: LOGIN
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('customer_panel') !== 'login') return;
    
    // Redirect if already logged in
    if (is_user_logged_in()) {
        $user = wp_get_current_user();
        if (!current_user_can('manage_options') && !in_array('sales_agent', $user->roles)) {
            wp_redirect(home_url('/customer-panel'));
            exit;
        }
    }
    
    // Handle login
    $error = '';
    if ($_POST && isset($_POST['customer_login'])) {
        $creds = [
            'user_login' => $_POST['username'],
            'user_password' => $_POST['password'],
            'remember' => true
        ];
        
        $user = wp_signon($creds, false);
        
        if (!is_wp_error($user)) {
            if (!current_user_can('manage_options') && !in_array('sales_agent', $user->roles)) {
                wp_redirect(home_url('/customer-panel'));
                exit;
            } else {
                wp_logout();
                $error = 'Access denied. This portal is for customers only.';
            }
        } else {
            $error = 'Invalid username or password.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Customer Login - <?php bloginfo('name'); ?></title>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
        * {margin:0;padding:0;box-sizing:border-box}
        body {
            font-family:'Inter',sans-serif;
            background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:20px;
        }
        .login-card {
            background:white;
            border-radius:16px;
            padding:40px;
            width:100%;
            max-width:420px;
            box-shadow:0 20px 60px rgba(0,0,0,0.3);
        }
        .logo {
            text-align:center;
            margin-bottom:30px;
        }
        .logo i {
            font-size:48px;
            color:#667eea;
        }
        h1 {
            text-align:center;
            font-size:28px;
            margin-bottom:10px;
            color:#1e293b;
        }
        .subtitle {
            text-align:center;
            color:#64748b;
            margin-bottom:30px;
        }
        .form-group {
            margin-bottom:20px;
        }
        label {
            display:block;
            margin-bottom:8px;
            font-weight:600;
            color:#1e293b;
        }
        input {
            width:100%;
            padding:12px 15px;
            border:1px solid #e5e7eb;
            border-radius:8px;
            font-size:14px;
            transition:border-color 0.2s;
        }
        input:focus {
            outline:none;
            border-color:#667eea;
        }
        button {
            width:100%;
            padding:14px;
            background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color:white;
            border:none;
            border-radius:8px;
            font-size:16px;
            font-weight:600;
            cursor:pointer;
            transition:transform 0.2s;
        }
        button:hover {
            transform:translateY(-2px);
        }
        .error {
            background:#fee2e2;
            color:#dc2626;
            padding:12px;
            border-radius:8px;
            margin-bottom:20px;
            font-size:14px;
        }
        </style>
    </head>
    <body>
        <div class="login-card">
            <div class="logo">
                <i class="fa-solid fa-store"></i>
            </div>
            <h1>Customer Portal</h1>
            <p class="subtitle">Welcome back! Please login to continue.</p>
            
            <?php if ($error): ?>
                <div class="error">
                    <i class="fa-solid fa-circle-exclamation"></i> <?= esc_html($error) ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <input type="hidden" name="customer_login" value="1">
                
                <div class="form-group">
                    <label>Username or Email</label>
                    <input type="text" name="username" required autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" required autocomplete="current-password">
                </div>
                
                <button type="submit">
                    <i class="fa-solid fa-arrow-right"></i> Login
                </button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
});

/* =====================================================
   5. PAGE: DASHBOARD
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('customer_panel') !== 'dashboard') return;
    
    $user = customer_panel_guard();
    customer_panel_header('Dashboard');
    
    // Get customer stats
    $customer_id = $user->ID;
    $total_orders = wc_get_orders([
        'customer_id' => $customer_id,
        'return' => 'count'
    ]);
    
    $recent_orders = wc_get_orders([
        'customer_id' => $customer_id,
        'limit' => 5,
        'orderby' => 'date',
        'order' => 'DESC'
    ]);
    
    // Calculate total spent
    global $wpdb;
    $total_spent = $wpdb->get_var($wpdb->prepare("
        SELECT SUM(meta_value)
        FROM {$wpdb->postmeta}
        WHERE post_id IN (
            SELECT ID FROM {$wpdb->posts}
            WHERE post_type = 'shop_order'
            AND post_author = %d
            AND post_status IN ('wc-completed', 'wc-processing')
        )
        AND meta_key = '_order_total'
    ", $customer_id));
    
    ?>
    <div class="page-header">
        <h1 class="page-title">Welcome, <?= esc_html($user->display_name) ?>!</h1>
        <a href="<?= home_url('/customer-panel/new-order') ?>" class="btn btn-large">
            <i class="fa-solid fa-cart-plus"></i> Start New Order
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;margin-bottom:30px">
        <div class="card" style="background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);color:white">
            <div style="font-size:14px;opacity:0.9;margin-bottom:5px">Total Orders</div>
            <div style="font-size:36px;font-weight:800"><?= $total_orders ?></div>
        </div>
        
        <div class="card" style="background:linear-gradient(135deg, #f093fb 0%, #f5576c 100%);color:white">
            <div style="font-size:14px;opacity:0.9;margin-bottom:5px">Total Spent</div>
            <div style="font-size:36px;font-weight:800"><?= wc_price($total_spent ?: 0) ?></div>
        </div>
        
        <div class="card" style="background:linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);color:white">
            <div style="font-size:14px;opacity:0.9;margin-bottom:5px">Quick Actions</div>
            <div style="margin-top:10px">
                <a href="<?= home_url('/customer-panel/new-order') ?>" style="color:white;font-size:14px;text-decoration:none">
                    <i class="fa-solid fa-cart-plus"></i> New Order
                </a>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="card">
        <h2 style="margin-bottom:20px">Recent Orders</h2>
        
        <?php if (empty($recent_orders)): ?>
            <div style="text-align:center;padding:40px;color:#64748b">
                <i class="fa-solid fa-box-open" style="font-size:48px;margin-bottom:15px;opacity:0.5"></i>
                <p>No orders yet</p>
                <a href="<?= home_url('/customer-panel/new-order') ?>" class="btn" style="margin-top:15px">
                    Place Your First Order
                </a>
            </div>
        <?php else: ?>
            <table style="width:100%;border-collapse:collapse">
                <thead>
                    <tr style="background:#f8fafc">
                        <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:#64748b">ORDER #</th>
                        <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:#64748b">DATE</th>
                        <th style="padding:12px;text-align:left;font-size:12px;font-weight:600;color:#64748b">STATUS</th>
                        <th style="padding:12px;text-align:right;font-size:12px;font-weight:600;color:#64748b">TOTAL</th>
                        <th style="padding:12px;text-align:right;font-size:12px;font-weight:600;color:#64748b">ACTION</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr style="border-bottom:1px solid #f1f5f9">
                        <td style="padding:12px;font-weight:600">#<?= $order->get_id() ?></td>
                        <td style="padding:12px;color:#64748b"><?= $order->get_date_created()->date('M d, Y') ?></td>
                        <td style="padding:12px">
                            <span style="padding:4px 12px;border-radius:6px;font-size:12px;font-weight:600;background:#dbeafe;color:#1e40af">
                                <?= wc_get_order_status_name($order->get_status()) ?>
                            </span>
                        </td>
                        <td style="padding:12px;text-align:right;font-weight:600"><?= $order->get_formatted_order_total() ?></td>
                        <td style="padding:12px;text-align:right">
                            <a href="<?= home_url('/customer-panel/order/' . $order->get_id()) ?>" class="btn-secondary" style="padding:6px 12px;font-size:12px">
                                View
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div style="margin-top:20px;text-align:center">
                <a href="<?= home_url('/customer-panel/orders') ?>" class="btn-secondary">
                    View All Orders
                </a>
            </div>
        <?php endif; ?>
    </div>
    
    <?php
    customer_panel_footer();
    exit;
});

/* =====================================================
   6. PAGE: NEW ORDER - CATEGORY SELECTION
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('customer_panel') !== 'new-order') return;
    
    $user = customer_panel_guard();
    customer_panel_header('New Order');
    
    // Get product categories
    $categories = get_terms([
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => 0  // Top-level categories only
    ]);
    
    ?>
    <div class="page-header">
        <h1 class="page-title">Start New Order</h1>
    </div>
    
    <div class="card">
        <h2 style="margin-bottom:10px">Select Product Category</h2>
        <p style="color:#64748b;margin-bottom:30px">Choose a category to browse available products</p>
        
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(250px,1fr));gap:20px">
            <?php foreach ($categories as $category): 
                $thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);
                $image = $thumbnail_id ? wp_get_attachment_url($thumbnail_id) : '';
            ?>
            <a href="<?= home_url('/customer-panel/new-order/category/' . $category->slug) ?>" style="text-decoration:none">
                <div class="card" style="height:100%;transition:transform 0.2s,box-shadow 0.2s;cursor:pointer" onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 10px 30px rgba(0,0,0,0.15)'" onmouseout="this.style.transform='';this.style.boxShadow=''">
                    <?php if ($image): ?>
                        <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($category->name) ?>" style="width:100%;height:150px;object-fit:cover;border-radius:8px;margin-bottom:15px">
                    <?php else: ?>
                        <div style="width:100%;height:150px;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%);border-radius:8px;margin-bottom:15px;display:flex;align-items:center;justify-content:center">
                            <i class="fa-solid fa-box" style="font-size:48px;color:rgba(255,255,255,0.8)"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h3 style="margin-bottom:8px;color:#1e293b"><?= esc_html($category->name) ?></h3>
                    <p style="font-size:14px;color:#64748b;margin-bottom:12px"><?= $category->count ?> products</p>
                    <div style="color:#3b82f6;font-weight:600;font-size:14px">
                        Browse Products <i class="fa-solid fa-arrow-right"></i>
                    </div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php
    customer_panel_footer();
    exit;
});

/* =====================================================
   7. PAGE: PRODUCT BROWSING
===================================================== */
add_action('template_redirect', function () {
    if (get_query_var('customer_panel') !== 'products') return;
    
    $user = customer_panel_guard();
    
    // Get category from URL
    $category_slug = get_query_var('category');
    if (empty($category_slug)) {
        wp_redirect(home_url('/customer-panel/new-order'));
        exit;
    }
    
    $category = get_term_by('slug', $category_slug, 'product_cat');
    if (!$category) {
        wp_redirect(home_url('/customer-panel/new-order'));
        exit;
    }
    
    // Get filters from query string
    $paged = max(1, intval($_GET['paged'] ?? 1));
    $per_page = 12;
    $search = sanitize_text_field($_GET['s'] ?? '');
    $orderby = sanitize_text_field($_GET['orderby'] ?? 'title');
    $order = sanitize_text_field($_GET['order'] ?? 'ASC');
    $view = sanitize_text_field($_GET['view'] ?? 'grid');
    
    // Price filter
    $min_price = isset($_GET['min_price']) ? floatval($_GET['min_price']) : 0;
    $max_price = isset($_GET['max_price']) ? floatval($_GET['max_price']) : 999999;
    
    // Stock filter
    $in_stock_only = isset($_GET['in_stock']);
    
    // Build query args
    $args = [
        'post_type' => 'product',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'orderby' => $orderby,
        'order' => $order,
        'tax_query' => [
            [
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $category->term_id,
                'include_children' => true
            ]
        ],
        'post_status' => 'publish'
    ];
    
    // Add search
    if (!empty($search)) {
        $args['s'] = $search;
    }
    
    // Add price filter via meta query
    if ($min_price > 0 || $max_price < 999999) {
        $args['meta_query'] = [
            [
                'key' => '_price',
                'value' => [$min_price, $max_price],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ]
        ];
    }
    
    // Stock filter
    if ($in_stock_only) {
        if (!isset($args['meta_query'])) {
            $args['meta_query'] = [];
        }
        $args['meta_query'][] = [
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '='
        ];
    }
    
    $query = new WP_Query($args);
    
    customer_panel_header('Browse Products');
    ?>
    
    <style>
    .filters-bar {
        background:var(--white);
        padding:20px;
        border-radius:12px;
        margin-bottom:20px;
        box-shadow:var(--shadow);
        display:flex;
        gap:15px;
        flex-wrap:wrap;
        align-items:center;
    }
    .filter-group {
        display:flex;
        align-items:center;
        gap:8px;
    }
    .filter-group label {
        font-size:14px;
        font-weight:600;
        color:var(--text);
    }
    .filter-group input,
    .filter-group select {
        padding:8px 12px;
        border:1px solid var(--border);
        border-radius:6px;
        font-size:14px;
    }
    .filter-group input[type="checkbox"] {
        width:auto;
        margin:0;
    }
    .products-header {
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
        flex-wrap:wrap;
        gap:15px;
    }
    .view-toggle {
        display:flex;
        gap:5px;
    }
    .view-btn {
        padding:8px 12px;
        border:1px solid var(--border);
        background:var(--white);
        border-radius:6px;
        cursor:pointer;
        transition:all 0.2s;
    }
    .view-btn.active {
        background:var(--accent);
        color:var(--white);
        border-color:var(--accent);
    }
    .products-grid {
        display:grid;
        grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));
        gap:20px;
        margin-bottom:30px;
    }
    .products-list {
        display:flex;
        flex-direction:column;
        gap:15px;
        margin-bottom:30px;
    }
    .product-card {
        background:var(--white);
        border-radius:12px;
        overflow:hidden;
        box-shadow:var(--shadow);
        transition:all 0.2s;
        display:flex;
        flex-direction:column;
    }
    .product-card:hover {
        transform:translateY(-5px);
        box-shadow:var(--shadow-lg);
    }
    .product-image {
        width:100%;
        height:200px;
        object-fit:cover;
        background:#f8fafc;
    }
    .product-info {
        padding:15px;
        flex:1;
        display:flex;
        flex-direction:column;
    }
    .product-title {
        font-weight:600;
        font-size:16px;
        margin-bottom:8px;
        color:var(--text);
    }
    .product-price {
        font-size:20px;
        font-weight:700;
        color:var(--accent);
        margin-bottom:10px;
    }
    .product-stock {
        font-size:12px;
        margin-bottom:15px;
    }
    .stock-in {
        color:var(--success);
    }
    .stock-out {
        color:var(--error);
    }
    .product-actions {
        margin-top:auto;
    }
    
    /* List view specific */
    .product-card.list-view {
        flex-direction:row;
    }
    .product-card.list-view .product-image {
        width:200px;
        height:150px;
    }
    .product-card.list-view .product-info {
        flex:1;
    }
    
    @media (max-width: 768px) {
        .filters-bar {
            flex-direction:column;
            align-items:stretch;
        }
        .filter-group {
            flex-direction:column;
            align-items:stretch;
        }
        .products-grid {
            grid-template-columns:repeat(auto-fill, minmax(150px, 1fr));
            gap:15px;
        }
        .product-card.list-view {
            flex-direction:column;
        }
        .product-card.list-view .product-image {
            width:100%;
            height:150px;
        }
    }
    </style>
    
    <div class="page-header">
        <div>
            <nav style="margin-bottom:10px;font-size:14px;color:var(--text-muted)">
                <a href="<?= home_url('/customer-panel') ?>" style="color:var(--text-muted)">Dashboard</a>
                <span> / </span>
                <a href="<?= home_url('/customer-panel/new-order') ?>" style="color:var(--text-muted)">New Order</a>
                <span> / </span>
                <span style="color:var(--text)"><?= esc_html($category->name) ?></span>
            </nav>
            <h1 class="page-title"><?= esc_html($category->name) ?></h1>
            <?php if ($category->description): ?>
                <p style="color:var(--text-muted);margin-top:5px"><?= esc_html($category->description) ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Filters Bar -->
    <form method="get" class="filters-bar">
        <div class="filter-group">
            <label>
                <i class="fa-solid fa-search"></i> Search:
            </label>
            <input type="text" name="s" value="<?= esc_attr($search) ?>" placeholder="Product name..." style="width:200px">
        </div>
        
        <div class="filter-group">
            <label>
                <i class="fa-solid fa-dollar-sign"></i> Price:
            </label>
            <input type="number" name="min_price" value="<?= $min_price > 0 ? $min_price : '' ?>" placeholder="Min" style="width:80px">
            <span>-</span>
            <input type="number" name="max_price" value="<?= $max_price < 999999 ? $max_price : '' ?>" placeholder="Max" style="width:80px">
        </div>
        
        <div class="filter-group">
            <label>
                <input type="checkbox" name="in_stock" value="1" <?= $in_stock_only ? 'checked' : '' ?>>
                In Stock Only
            </label>
        </div>
        
        <div class="filter-group">
            <label>Sort:</label>
            <select name="orderby" style="width:120px">
                <option value="title" <?= $orderby == 'title' ? 'selected' : '' ?>>Name</option>
                <option value="date" <?= $orderby == 'date' ? 'selected' : '' ?>>Date</option>
                <option value="price" <?= $orderby == 'price' ? 'selected' : '' ?>>Price</option>
                <option value="popularity" <?= $orderby == 'popularity' ? 'selected' : '' ?>>Popularity</option>
            </select>
            <select name="order" style="width:80px">
                <option value="ASC" <?= $order == 'ASC' ? 'selected' : '' ?>>↑</option>
                <option value="DESC" <?= $order == 'DESC' ? 'selected' : '' ?>>↓</option>
            </select>
        </div>
        
        <button type="submit" class="btn" style="padding:8px 16px">
            <i class="fa-solid fa-filter"></i> Apply
        </button>
        
        <a href="<?= home_url('/customer-panel/new-order/category/' . $category_slug) ?>" class="btn-secondary" style="padding:8px 16px">
            Clear
        </a>
    </form>
    
    <!-- Products Header -->
    <div class="products-header">
        <div>
            <strong><?= $query->found_posts ?></strong> products found
        </div>
        <div class="view-toggle">
            <a href="?<?= http_build_query(array_merge($_GET, ['view' => 'grid'])) ?>" class="view-btn <?= $view == 'grid' ? 'active' : '' ?>">
                <i class="fa-solid fa-grid"></i> Grid
            </a>
            <a href="?<?= http_build_query(array_merge($_GET, ['view' => 'list'])) ?>" class="view-btn <?= $view == 'list' ? 'active' : '' ?>">
                <i class="fa-solid fa-list"></i> List
            </a>
        </div>
    </div>
    
    <?php if ($query->have_posts()): ?>
        <div class="products-<?= $view ?>">
            <?php while ($query->have_posts()): $query->the_post();
                $product = wc_get_product(get_the_ID());
                $image = wp_get_attachment_url($product->get_image_id());
                $stock_status = $product->get_stock_status();
            ?>
            <div class="product-card <?= $view == 'list' ? 'list-view' : '' ?>">
                <?php if ($image): ?>
                    <img src="<?= esc_url($image) ?>" alt="<?= esc_attr($product->get_name()) ?>" class="product-image">
                <?php else: ?>
                    <div class="product-image" style="display:flex;align-items:center;justify-content:center;background:linear-gradient(135deg, #667eea 0%, #764ba2 100%)">
                        <i class="fa-solid fa-box" style="font-size:48px;color:rgba(255,255,255,0.5)"></i>
                    </div>
                <?php endif; ?>
                
                <div class="product-info">
                    <div class="product-title"><?= esc_html($product->get_name()) ?></div>
                    <div class="product-price"><?= $product->get_price_html() ?></div>
                    
                    <?php if ($stock_status == 'instock'): ?>
                        <div class="product-stock stock-in">
                            <i class="fa-solid fa-check-circle"></i> In Stock
                        </div>
                    <?php else: ?>
                        <div class="product-stock stock-out">
                            <i class="fa-solid fa-times-circle"></i> Out of Stock
                        </div>
                    <?php endif; ?>
                    
                    <div class="product-actions">
                        <?php if ($stock_status == 'instock'): ?>
                            <button class="btn" style="width:100%;justify-content:center" onclick="alert('Cart feature coming in Phase 3!')">
                                <i class="fa-solid fa-cart-plus"></i> Add to Cart
                            </button>
                        <?php else: ?>
                            <button class="btn-secondary" style="width:100%;justify-content:center" disabled>
                                Out of Stock
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($query->max_num_pages > 1): ?>
            <div style="margin-top:30px;display:flex;justify-content:center;align-items:center;gap:10px">
                <span style="color:var(--text-muted);font-size:14px">Page:</span>
                <select onchange="window.location.href=this.value" style="padding:10px;border:1px solid var(--border);border-radius:8px">
                    <?php for ($i = 1; $i <= $query->max_num_pages; $i++): 
                        $page_params = array_merge($_GET, ['paged' => $i]);
                        $page_url = home_url('/customer-panel/new-order/category/' . $category_slug . '?' . http_build_query($page_params));
                    ?>
                        <option value="<?= esc_url($page_url) ?>" <?= $paged == $i ? 'selected' : '' ?>>
                            Page <?= $i ?> of <?= $query->max_num_pages ?>
                        </option>
                    <?php endfor; ?>
                </select>
                <span style="color:var(--text-muted);font-size:14px">
                    (Showing <?= min($per_page * $paged, $query->found_posts) ?> of <?= $query->found_posts ?> products)
                </span>
            </div>
        <?php endif; ?>
        
    <?php else: ?>
        <div class="card">
            <div style="text-align:center;padding:60px 20px">
                <i class="fa-solid fa-search" style="font-size:64px;color:#e5e7eb;margin-bottom:20px"></i>
                <h2 style="margin-bottom:10px">No Products Found</h2>
                <p style="color:#64748b">Try adjusting your filters or search terms.</p>
                <a href="<?= home_url('/customer-panel/new-order/category/' . $category_slug) ?>" class="btn" style="margin-top:20px">
                    Clear Filters
                </a>
            </div>
        </div>
    <?php endif; ?>
    
    <?php
    wp_reset_postdata();
    customer_panel_footer();
    exit;
});

// Placeholder pages (to be implemented in next phases)
add_action('template_redirect', function () {
    $page = get_query_var('customer_panel');
    if (!in_array($page, ['cart', 'checkout', 'orders', 'order-detail', 'favorites', 'account'])) return;
    
    customer_panel_guard();
    customer_panel_header(ucfirst(str_replace('-', ' ', $page)));
    ?>
    <div class="page-header">
        <h1 class="page-title"><?= ucfirst(str_replace('-', ' ', $page)) ?></h1>
    </div>
    
    <div class="card">
        <div style="text-align:center;padding:60px 20px">
            <i class="fa-solid fa-hammer" style="font-size:64px;color:#e5e7eb;margin-bottom:20px"></i>
            <h2 style="margin-bottom:10px">Coming Soon</h2>
            <p style="color:#64748b">This page is under construction and will be available soon.</p>
            <a href="<?= home_url('/customer-panel') ?>" class="btn" style="margin-top:20px">
                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
    <?php
    customer_panel_footer();
    exit;
});

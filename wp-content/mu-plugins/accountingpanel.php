<?php
/**
 * =====================================================
 * B2B ACCOUNTING MODULE
 * Admin-only access for income/expense tracking
 * =====================================================
 * 
 * Features:
 * - Income & Expense tracking
 * - Tax calculations (Sales Tax, Payroll Tax)
 * - Financial reports (P&L, Cash Flow)
 * - Personnel payroll integration
 * - Category management
 * 
 * Architecture: Follows personnelpanel.php pattern
 * - WordPress Custom Post Type (acc_transaction)
 * - Post Meta for transaction details
 * - Template redirect for custom URLs
 * - Integrates with B2B Admin Panel (not WordPress admin)
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/* =====================================================
 * 1. REGISTER CUSTOM POST TYPE
 * ===================================================== */
add_action('init', 'b2b_register_accounting_post_type');
function b2b_register_accounting_post_type() {
    register_post_type('acc_transaction', [
        'labels' => [
            'name'               => 'Transactions',
            'singular_name'      => 'Transaction',
            'add_new'            => 'Add New',
            'add_new_item'       => 'Add New Transaction',
            'edit_item'          => 'Edit Transaction',
            'view_item'          => 'View Transaction',
            'search_items'       => 'Search Transactions',
            'not_found'          => 'No transactions found',
            'not_found_in_trash' => 'No transactions in trash'
        ],
        'public'              => false,
        'show_ui'             => false, // We use custom B2B panel
        'capability_type'     => 'post',
        'hierarchical'        => false,
        'supports'            => ['title'],
        'has_archive'         => false,
        'rewrite'             => false,
    ]);
}

/* =====================================================
 * 2. REWRITE RULES FOR CLEAN URLS
 * ===================================================== */
add_action('init', 'b2b_accounting_rewrite_rules');
function b2b_accounting_rewrite_rules() {
    add_rewrite_rule('^accounting-panel/?$', 'index.php?accounting_panel=dashboard', 'top');
    add_rewrite_rule('^accounting-panel/dashboard/?$', 'index.php?accounting_panel=dashboard', 'top');
    add_rewrite_rule('^accounting-panel/transactions/?$', 'index.php?accounting_panel=transactions', 'top');
    add_rewrite_rule('^accounting-panel/add-transaction/?$', 'index.php?accounting_panel=add_transaction', 'top');
    add_rewrite_rule('^accounting-panel/edit-transaction/([0-9]+)/?$', 'index.php?accounting_panel=edit_transaction&transaction_id=$matches[1]', 'top');
    add_rewrite_rule('^accounting-panel/delete-transaction/([0-9]+)/?$', 'index.php?accounting_panel=delete_transaction&transaction_id=$matches[1]', 'top');
    add_rewrite_rule('^accounting-panel/process-transaction/?$', 'index.php?accounting_panel=process_transaction', 'top');
    add_rewrite_rule('^accounting-panel/reports/?$', 'index.php?accounting_panel=reports', 'top');
    add_rewrite_rule('^accounting-panel/profit-loss/?$', 'index.php?accounting_panel=profit_loss', 'top');
    add_rewrite_rule('^accounting-panel/cash-flow/?$', 'index.php?accounting_panel=cash_flow', 'top');
    add_rewrite_rule('^accounting-panel/tax-summary/?$', 'index.php?accounting_panel=tax_summary', 'top');
    add_rewrite_rule('^accounting-panel/categories/?$', 'index.php?accounting_panel=categories', 'top');
    add_rewrite_rule('^accounting-panel/settings/?$', 'index.php?accounting_panel=settings', 'top');
}

add_filter('query_vars', 'b2b_accounting_query_vars');
function b2b_accounting_query_vars($vars) {
    $vars[] = 'accounting_panel';
    $vars[] = 'transaction_id';
    return $vars;
}

/* =====================================================
 * 3. TEMPLATE REDIRECT - ROUTE HANDLER
 * ===================================================== */
add_action('template_redirect', 'b2b_accounting_template_redirect');
function b2b_accounting_template_redirect() {
    $panel = get_query_var('accounting_panel');
    
    if (!$panel) return;

    // Admin-only access control
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_redirect(home_url('/b2b-panel'));
        exit;
    }

    // Route to appropriate handler
    switch ($panel) {
        case 'dashboard':
            b2b_accounting_dashboard_page();
            break;
        case 'transactions':
            b2b_accounting_transactions_page();
            break;
        case 'add_transaction':
            b2b_accounting_add_transaction_page();
            break;
        case 'edit_transaction':
            b2b_accounting_edit_transaction_page();
            break;
        case 'delete_transaction':
            b2b_accounting_delete_transaction();
            break;
        case 'process_transaction':
            b2b_accounting_process_transaction();
            break;
        case 'reports':
            b2b_accounting_reports_page();
            break;
        case 'profit_loss':
            b2b_accounting_profit_loss_page();
            break;
        case 'cash_flow':
            b2b_accounting_cash_flow_page();
            break;
        case 'tax_summary':
            b2b_accounting_tax_summary_page();
            break;
        case 'categories':
            b2b_accounting_categories_page();
            break;
        case 'settings':
            b2b_accounting_settings_page();
            break;
        default:
            wp_redirect(home_url('/accounting-panel/dashboard'));
            exit;
    }
    
    exit;
}

/* =====================================================
 * 4. PAGE HANDLERS
 * ===================================================== */

/**
 * Dashboard Page
 */
function b2b_accounting_dashboard_page() {
    // Get current month stats
    $start_date = date('Y-m-01');
    $end_date = date('Y-m-t');
    $stats = b2b_accounting_get_dashboard_stats($start_date, $end_date);
    
    // Use B2B admin panel header
    b2b_adm_header('Accounting Dashboard');
    ?>
    
    <div class="page-header">
        <h1 class="page-title">📊 Accounting Dashboard</h1>
        <p style="color: #6b7280; margin: 5px 0 0 0;">Income & Expense tracking for your B2B business</p>
    </div>
        
    <!-- Summary Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin: 30px 0;">
        <!-- Total Income -->
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Income (This Month)</div>
                <div style="font-size: 32px; font-weight: bold;">$<?= number_format($stats['total_income'], 2) ?></div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;"><?= $stats['income_count'] ?> transactions</div>
        </div>
        
        <!-- Total Expenses -->
        <div style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Total Expenses (This Month)</div>
                <div style="font-size: 32px; font-weight: bold;">$<?= number_format($stats['total_expenses'], 2) ?></div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;"><?= $stats['expense_count'] ?> transactions</div>
        </div>
        
        <!-- Net Income -->
        <?php $net_income = $stats['total_income'] - $stats['total_expenses']; ?>
        <div style="background: linear-gradient(135deg, <?= $net_income >= 0 ? '#3b82f6, #2563eb' : '#f59e0b, #d97706' ?>); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Net Income (This Month)</div>
                <div style="font-size: 32px; font-weight: bold;">$<?= number_format($net_income, 2) ?></div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;"><?= $net_income >= 0 ? 'Profit' : 'Loss' ?></div>
        </div>
        
        <!-- Personnel Payroll -->
        <div style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); padding: 25px; border-radius: 12px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                <div style="font-size: 14px; opacity: 0.9; margin-bottom: 8px;">Personnel Payroll (Pending)</div>
                <div style="font-size: 32px; font-weight: bold;">$<?= number_format($stats['personnel_balance'], 2) ?></div>
                <div style="font-size: 12px; opacity: 0.8; margin-top: 8px;">From personnel module</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="card" style="margin: 30px 0;">
        <h2 style="margin: 0 0 20px 0; font-size: 20px;">Quick Actions</h2>
        <div style="display: flex; gap: 15px; flex-wrap: wrap;">
            <a href="<?= home_url('/accounting-panel/add-transaction') ?>" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                ➕ Add Transaction
            </a>
            <a href="<?= home_url('/accounting-panel/transactions') ?>" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                📋 View All Transactions
            </a>
            <a href="<?= home_url('/accounting-panel/profit-loss') ?>" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                📊 Profit & Loss Report
            </a>
            <a href="<?= home_url('/accounting-panel/tax-summary') ?>" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                💰 Tax Summary
            </a>
        </div>
    </div>
    
    <!-- Recent Transactions -->
    <div class="card">
        <h2 style="margin: 0 0 20px 0; font-size: 20px;">Recent Transactions</h2>
        <?php
        $recent_transactions = b2b_accounting_get_recent_transactions(10);
        if (!empty($recent_transactions)) {
            ?>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 2px solid #e5e7eb;">
                        <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Date</th>
                        <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Type</th>
                        <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Category</th>
                        <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Description</th>
                        <th style="text-align: right; padding: 12px 8px; font-weight: 600; color: #6b7280;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_transactions as $txn): ?>
                    <tr style="border-bottom: 1px solid #f3f4f6;">
                        <td style="padding: 12px 8px;"><?= date('M d, Y', strtotime($txn['date'])) ?></td>
                        <td style="padding: 12px 8px;">
                            <span style="display: inline-block; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; background: <?= $txn['type'] === 'income' ? '#d1fae5' : '#fee2e2' ?>; color: <?= $txn['type'] === 'income' ? '#065f46' : '#991b1b' ?>;">
                                <?= ucfirst($txn['type']) ?>
                            </span>
                        </td>
                        <td style="padding: 12px 8px;"><?= esc_html($txn['category']) ?></td>
                        <td style="padding: 12px 8px;"><?= esc_html($txn['description']) ?></td>
                        <td style="padding: 12px 8px; text-align: right; font-weight: 600; color: <?= $txn['type'] === 'income' ? '#059669' : '#dc2626' ?>;">
                            <?= $txn['type'] === 'income' ? '+' : '-' ?>$<?= number_format($txn['amount'], 2) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php
        } else {
            echo '<p style="color: #6b7280; text-align: center; padding: 40px 0;">No transactions yet. Add your first transaction to get started!</p>';
        }
        ?>
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}

/**
 * Placeholder pages (to be implemented)
 */
function b2b_accounting_transactions_page() {
    b2b_adm_header('All Transactions');
    echo '<div class="page-header"><h1 class="page-title">All Transactions</h1></div>';
    echo '<div class="card"><p>Transaction list page - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_add_transaction_page() {
    b2b_adm_header('Add Transaction');
    echo '<div class="page-header"><h1 class="page-title">Add Transaction</h1></div>';
    echo '<div class="card"><p>Add transaction form - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_edit_transaction_page() {
    b2b_adm_header('Edit Transaction');
    echo '<div class="page-header"><h1 class="page-title">Edit Transaction</h1></div>';
    echo '<div class="card"><p>Edit transaction form - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_delete_transaction() {
    wp_redirect(home_url('/accounting-panel/transactions'));
}

function b2b_accounting_process_transaction() {
    wp_redirect(home_url('/accounting-panel/transactions'));
}

function b2b_accounting_reports_page() {
    b2b_adm_header('Reports');
    echo '<div class="page-header"><h1 class="page-title">Reports</h1></div>';
    echo '<div class="card"><p>Reports overview - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_profit_loss_page() {
    b2b_adm_header('Profit & Loss Report');
    echo '<div class="page-header"><h1 class="page-title">Profit & Loss Report</h1></div>';
    echo '<div class="card"><p>P&L report - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_cash_flow_page() {
    b2b_adm_header('Cash Flow Report');
    echo '<div class="page-header"><h1 class="page-title">Cash Flow Report</h1></div>';
    echo '<div class="card"><p>Cash flow report - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_tax_summary_page() {
    b2b_adm_header('Tax Summary');
    echo '<div class="page-header"><h1 class="page-title">Tax Summary</h1></div>';
    echo '<div class="card"><p>Tax summary - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_categories_page() {
    b2b_adm_header('Categories');
    echo '<div class="page-header"><h1 class="page-title">Categories</h1></div>';
    echo '<div class="card"><p>Category management - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

function b2b_accounting_settings_page() {
    b2b_adm_header('Settings');
    echo '<div class="page-header"><h1 class="page-title">Settings</h1></div>';
    echo '<div class="card"><p>Settings page - Implementation in progress</p></div>';
    b2b_adm_footer();
    exit;
}

/* =====================================================
 * 5. HELPER FUNCTIONS
 * ===================================================== */

/**
 * Get dashboard statistics
 */
function b2b_accounting_get_dashboard_stats($start_date, $end_date) {
    global $wpdb;
    
    $income = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) as count, COALESCE(SUM(CAST(pm1.meta_value AS DECIMAL(10,2))), 0) as total 
        FROM {$wpdb->postmeta} pm1
        INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = '_acc_type' AND pm2.meta_value = 'income'
        INNER JOIN {$wpdb->postmeta} pm3 ON pm1.post_id = pm3.post_id AND pm3.meta_key = '_acc_date'
        WHERE pm1.meta_key = '_acc_amount' AND pm3.meta_value BETWEEN %s AND %s",
        $start_date, $end_date
    ));
    
    $expenses = $wpdb->get_row($wpdb->prepare(
        "SELECT COUNT(*) as count, COALESCE(SUM(CAST(pm1.meta_value AS DECIMAL(10,2))), 0) as total 
        FROM {$wpdb->postmeta} pm1
        INNER JOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id AND pm2.meta_key = '_acc_type' AND pm2.meta_value = 'expense'
        INNER JOIN {$wpdb->postmeta} pm3 ON pm1.post_id = pm3.post_id AND pm3.meta_key = '_acc_date'
        WHERE pm1.meta_key = '_acc_amount' AND pm3.meta_value BETWEEN %s AND %s",
        $start_date, $end_date
    ));
    
    // Get personnel balance (integration with personnel module)
    // TODO: Add personnel integration to get total pending payroll
    $personnel_balance = 0;
    
    return [
        'total_income' => floatval($income->total ?? 0),
        'income_count' => intval($income->count ?? 0),
        'total_expenses' => floatval($expenses->total ?? 0),
        'expense_count' => intval($expenses->count ?? 0),
        'personnel_balance' => $personnel_balance,
    ];
}

/**
 * Get recent transactions
 */
function b2b_accounting_get_recent_transactions($limit = 10) {
    global $wpdb;
    
    $posts = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID FROM {$wpdb->posts} p
        WHERE p.post_type = 'acc_transaction' AND p.post_status = 'publish'
        ORDER BY p.post_date DESC LIMIT %d",
        $limit
    ));
    
    $transactions = [];
    foreach ($posts as $post) {
        $transactions[] = [
            'id' => $post->ID,
            'type' => get_post_meta($post->ID, '_acc_type', true),
            'category' => get_post_meta($post->ID, '_acc_category', true),
            'amount' => floatval(get_post_meta($post->ID, '_acc_amount', true)),
            'date' => get_post_meta($post->ID, '_acc_date', true),
            'description' => get_post_meta($post->ID, '_acc_description', true),
        ];
    }
    
    return $transactions;
}

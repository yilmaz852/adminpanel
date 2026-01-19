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

// Configuration constants
define('B2B_ACCOUNTING_SYNC_BATCH_SIZE', 100);

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
    add_rewrite_rule('^accounting-panel/sync-orders/?$', 'index.php?accounting_panel=sync_orders', 'top');
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
        case 'sync_orders':
            b2b_accounting_sync_orders_handler();
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
    
    // Check if orders were synced
    $synced_count = isset($_GET['synced']) ? intval($_GET['synced']) : 0;
    ?>
    
    <?php if ($synced_count > 0): ?>
    <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> Successfully synced <?= esc_html($synced_count) ?> order(s) to accounting records.
    </div>
    <?php endif; ?>
    
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
            <a href="<?= home_url('/accounting-panel/sync-orders') ?>" style="display: inline-block; padding: 12px 24px; background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: 500; box-shadow: 0 2px 4px rgba(0,0,0,0.1);" onclick="return confirm('This will import all completed WooCommerce orders that are not yet synced. Continue?');">
                🔄 Sync Old Orders
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
    
    // Check for success/error messages
    $success = isset($_GET['success']) ? sanitize_text_field($_GET['success']) : '';
    $error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
    
    // Get all transactions
    $transactions = b2b_accounting_get_recent_transactions(100);
    ?>
    
    <div class="page-header">
        <h1 class="page-title">📋 All Transactions</h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="<?= home_url('/accounting-panel/dashboard') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #6b7280; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <a href="<?= home_url('/accounting-panel/add-transaction') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #10b981; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">
                <i class="fas fa-plus"></i> Add Transaction
            </a>
        </div>
    </div>
    
    <?php if ($success === 'transaction_added'): ?>
    <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> Transaction successfully added!
    </div>
    <?php elseif ($success === 'transaction_updated'): ?>
    <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> Transaction successfully updated!
    </div>
    <?php elseif ($success === 'transaction_deleted'): ?>
    <div style="background: #d1fae5; border: 1px solid #6ee7b7; color: #065f46; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-check-circle"></i> Transaction successfully deleted!
    </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
    <div style="background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 20px;">
        <i class="fas fa-exclamation-circle"></i> Error: <?= esc_html($error) ?>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <?php if (!empty($transactions)): ?>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 2px solid #e5e7eb;">
                    <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Date</th>
                    <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Type</th>
                    <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Category</th>
                    <th style="text-align: left; padding: 12px 8px; font-weight: 600; color: #6b7280;">Description</th>
                    <th style="text-align: right; padding: 12px 8px; font-weight: 600; color: #6b7280;">Amount</th>
                    <th style="text-align: center; padding: 12px 8px; font-weight: 600; color: #6b7280;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $txn): ?>
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
                    <td style="padding: 12px 8px; text-align: center;">
                        <div style="display: flex; gap: 0.5rem; justify-content: center;">
                            <a href="<?= home_url('/accounting-panel/edit-transaction/' . absint($txn['id'])) ?>" style="display: inline-flex; align-items: center; padding: 0.375rem 0.75rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 4px; font-size: 0.75rem;">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="<?= home_url('/accounting-panel/delete-transaction/' . absint($txn['id'])) ?>" onclick="return confirm('Are you sure you want to delete this transaction?')" style="display: inline-flex; align-items: center; padding: 0.375rem 0.75rem; background: #ef4444; color: white; text-decoration: none; border-radius: 4px; font-size: 0.75rem;">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="color: #6b7280; text-align: center; padding: 40px 0;">No transactions yet. Add your first transaction to get started!</p>
        <?php endif; ?>
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}

function b2b_accounting_add_transaction_page() {
    b2b_adm_header('Add Transaction');
    ?>
    
    <div class="page-header">
        <h1 class="page-title">💰 Add Transaction</h1>
        <a href="<?= home_url('/accounting-panel/dashboard') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #6b7280; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">
            <i class="fas fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>
    
    <div class="card">
        <form method="POST" action="<?= home_url('/accounting-panel/process-transaction') ?>">
            <input type="hidden" name="action" value="add">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-exchange-alt"></i> Transaction Type *
                    </label>
                    <select name="type" required style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                        <option value="">Select Type...</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-tag"></i> Category *
                    </label>
                    <input type="text" name="category" required placeholder="e.g., Sales Revenue, Office Supplies" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-dollar-sign"></i> Amount *
                    </label>
                    <input type="number" name="amount" required step="0.01" min="0" placeholder="0.00" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                </div>
                
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-calendar"></i> Date *
                    </label>
                    <input type="date" name="date" required value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                </div>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea name="description" rows="3" placeholder="Enter transaction details..." style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem; resize: vertical;"></textarea>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                    <i class="fas fa-hashtag"></i> Reference Number
                </label>
                <input type="text" name="reference" placeholder="e.g., INV-001, REF-123" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="<?= home_url('/accounting-panel/dashboard') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-weight: 500; border: 1px solid #e5e7eb;">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                    <i class="fas fa-save"></i> Save Transaction
                </button>
            </div>
        </form>
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}

function b2b_accounting_edit_transaction_page() {
    $transaction_id = intval(get_query_var('transaction_id'));
    $transaction = get_post($transaction_id);
    
    if (!$transaction || $transaction->post_type !== 'acc_transaction') {
        wp_redirect(home_url('/accounting-panel/transactions'));
        exit;
    }
    
    // Get transaction data
    $type = get_post_meta($transaction_id, '_acc_type', true);
    $category = get_post_meta($transaction_id, '_acc_category', true);
    $amount = get_post_meta($transaction_id, '_acc_amount', true);
    $date = get_post_meta($transaction_id, '_acc_date', true);
    $description = get_post_meta($transaction_id, '_acc_description', true);
    $reference = get_post_meta($transaction_id, '_acc_reference', true);
    
    b2b_adm_header('Edit Transaction');
    ?>
    
    <div class="page-header">
        <h1 class="page-title">✏️ Edit Transaction</h1>
        <a href="<?= home_url('/accounting-panel/transactions') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; background: #6b7280; color: white; text-decoration: none; border-radius: 6px; font-size: 0.875rem;">
            <i class="fas fa-arrow-left"></i> Back to Transactions
        </a>
    </div>
    
    <div class="card">
        <form method="POST" action="<?= home_url('/accounting-panel/process-transaction') ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="transaction_id" value="<?= absint($transaction_id) ?>">
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-exchange-alt"></i> Transaction Type *
                    </label>
                    <select name="type" required style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                        <option value="">Select Type...</option>
                        <option value="income" <?= selected($type, 'income', false) ?>>Income</option>
                        <option value="expense" <?= selected($type, 'expense', false) ?>>Expense</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-tag"></i> Category *
                    </label>
                    <input type="text" name="category" required placeholder="e.g., Sales Revenue, Office Supplies" value="<?= esc_attr($category) ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.5rem;">
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-dollar-sign"></i> Amount *
                    </label>
                    <input type="number" name="amount" required step="0.01" min="0" placeholder="0.00" value="<?= esc_attr($amount) ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                </div>
                
                <div>
                    <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                        <i class="fas fa-calendar"></i> Date *
                    </label>
                    <input type="date" name="date" required value="<?= esc_attr($date) ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
                </div>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                    <i class="fas fa-align-left"></i> Description
                </label>
                <textarea name="description" rows="3" placeholder="Enter transaction details..." style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem; resize: vertical;"><?= esc_textarea($description) ?></textarea>
            </div>
            
            <div style="margin-bottom: 1.5rem;">
                <label style="display: block; font-weight: 600; margin-bottom: 0.5rem; color: #374151;">
                    <i class="fas fa-hashtag"></i> Reference Number
                </label>
                <input type="text" name="reference" placeholder="e.g., INV-001, REF-123" value="<?= esc_attr($reference) ?>" style="width: 100%; padding: 0.75rem; border: 1px solid #e0e0e0; border-radius: 6px; font-size: 0.875rem;">
            </div>
            
            <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                <a href="<?= home_url('/accounting-panel/transactions') ?>" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: #f3f4f6; color: #374151; text-decoration: none; border-radius: 6px; font-weight: 500; border: 1px solid #e5e7eb;">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1.5rem; background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; border-radius: 6px; font-weight: 500; cursor: pointer;">
                    <i class="fas fa-save"></i> Update Transaction
                </button>
            </div>
        </form>
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}

function b2b_accounting_delete_transaction() {
    // Check permission
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_redirect(home_url('/b2b-panel'));
        exit;
    }
    
    $transaction_id = intval(get_query_var('transaction_id'));
    
    if ($transaction_id) {
        // Verify it's a transaction before deleting
        $post = get_post($transaction_id);
        if ($post && $post->post_type === 'acc_transaction') {
            wp_delete_post($transaction_id, true);
        }
    }
    
    wp_redirect(add_query_arg('success', 'transaction_deleted', home_url('/accounting-panel/transactions')));
    exit;
}

function b2b_accounting_process_transaction() {
    // Check if user has permission
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_redirect(home_url('/b2b-panel'));
        exit;
    }
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        $action = sanitize_text_field($_POST['action']);
        
        if ($action === 'add') {
            // Validate and sanitize input
            $type = sanitize_text_field($_POST['type'] ?? '');
            $category = sanitize_text_field($_POST['category'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $date = sanitize_text_field($_POST['date'] ?? date('Y-m-d'));
            $description = sanitize_textarea_field($_POST['description'] ?? '');
            $reference = sanitize_text_field($_POST['reference'] ?? '');
            
            // Validate required fields
            if (empty($type) || empty($category) || $amount <= 0) {
                wp_redirect(add_query_arg('error', 'missing_fields', home_url('/accounting-panel/add-transaction')));
                exit;
            }
            
            // Create transaction
            $transaction_data = [
                'type' => $type,
                'category' => $category,
                'amount' => $amount,
                'date' => $date,
                'description' => $description,
                'reference' => $reference,
                'source' => 'manual',
            ];
            
            $txn_id = b2b_accounting_create_transaction($transaction_data);
            
            if ($txn_id) {
                wp_redirect(add_query_arg('success', 'transaction_added', home_url('/accounting-panel/transactions')));
                exit;
            } else {
                wp_redirect(add_query_arg('error', 'create_failed', home_url('/accounting-panel/add-transaction')));
                exit;
            }
        } elseif ($action === 'edit') {
            // Handle edit transaction
            $transaction_id = intval($_POST['transaction_id'] ?? 0);
            $type = sanitize_text_field($_POST['type'] ?? '');
            $category = sanitize_text_field($_POST['category'] ?? '');
            $amount = floatval($_POST['amount'] ?? 0);
            $date = sanitize_text_field($_POST['date'] ?? date('Y-m-d'));
            $description = sanitize_textarea_field($_POST['description'] ?? '');
            $reference = sanitize_text_field($_POST['reference'] ?? '');
            
            // Validate required fields
            if (!$transaction_id || empty($type) || empty($category) || $amount <= 0) {
                wp_redirect(add_query_arg('error', 'missing_fields', home_url('/accounting-panel/edit-transaction/' . $transaction_id)));
                exit;
            }
            
            // Update transaction meta
            update_post_meta($transaction_id, '_acc_type', $type);
            update_post_meta($transaction_id, '_acc_category', $category);
            update_post_meta($transaction_id, '_acc_amount', $amount);
            update_post_meta($transaction_id, '_acc_date', $date);
            update_post_meta($transaction_id, '_acc_description', $description);
            update_post_meta($transaction_id, '_acc_reference', $reference);
            
            // Update post title
            wp_update_post([
                'ID' => $transaction_id,
                'post_title' => sprintf(
                    '%s - %s - $%s',
                    ucfirst($type),
                    $category,
                    number_format($amount, 2)
                ),
            ]);
            
            wp_redirect(add_query_arg('success', 'transaction_updated', home_url('/accounting-panel/transactions')));
            exit;
        }
    }
    
    // Default redirect
    wp_redirect(home_url('/accounting-panel/transactions'));
    exit;
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

/**
 * Create accounting transaction (used by integrations)
 */
function b2b_accounting_create_transaction($data) {
    // Validate required fields
    if (empty($data['type']) || empty($data['amount']) || empty($data['date'])) {
        return false;
    }
    
    // Create transaction post
    $post_id = wp_insert_post([
        'post_type' => 'acc_transaction',
        'post_status' => 'publish',
        'post_title' => sprintf(
            '%s - %s - $%s',
            ucfirst($data['type']),
            $data['category'] ?? 'Other',
            number_format($data['amount'], 2)
        ),
    ]);
    
    if (!$post_id || is_wp_error($post_id)) {
        return false;
    }
    
    // Save transaction meta
    update_post_meta($post_id, '_acc_type', $data['type']); // 'income' or 'expense'
    update_post_meta($post_id, '_acc_category', $data['category'] ?? 'Other');
    update_post_meta($post_id, '_acc_amount', floatval($data['amount']));
    update_post_meta($post_id, '_acc_date', $data['date']);
    update_post_meta($post_id, '_acc_description', $data['description'] ?? '');
    update_post_meta($post_id, '_acc_reference', $data['reference'] ?? '');
    update_post_meta($post_id, '_acc_source', $data['source'] ?? 'manual'); // 'order', 'payroll', 'manual'
    update_post_meta($post_id, '_acc_source_id', $data['source_id'] ?? '');
    
    // Additional fields for detailed tracking
    if (!empty($data['gross_amount'])) {
        update_post_meta($post_id, '_acc_gross_amount', floatval($data['gross_amount']));
    }
    if (!empty($data['net_amount'])) {
        update_post_meta($post_id, '_acc_net_amount', floatval($data['net_amount']));
    }
    if (!empty($data['tax_amount'])) {
        update_post_meta($post_id, '_acc_tax_amount', floatval($data['tax_amount']));
    }
    if (!empty($data['shipping_amount'])) {
        update_post_meta($post_id, '_acc_shipping_amount', floatval($data['shipping_amount']));
    }
    if (!empty($data['refund_amount'])) {
        update_post_meta($post_id, '_acc_refund_amount', floatval($data['refund_amount']));
    }
    
    return $post_id;
}

/* =====================================================
 * 6. ORDER INTEGRATION - WooCommerce Orders
 * ===================================================== */

/**
 * Hook: When WooCommerce order is completed, create income transaction
 */
add_action('woocommerce_order_status_completed', 'b2b_accounting_sync_order_income', 10, 1);
function b2b_accounting_sync_order_income($order_id) {
    // Check if already synced
    if (get_post_meta($order_id, '_acc_synced', true)) {
        return;
    }
    
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    
    // Skip if this is a refund object (not an order)
    if ($order->get_type() === 'shop_order_refund') {
        return;
    }
    
    // Get order financial details
    $total = $order->get_total();
    $subtotal = $order->get_subtotal();
    $tax = $order->get_total_tax();
    $shipping = $order->get_shipping_total();
    
    // Safely get refund amount (only works on order objects, not refunds)
    $refund = 0;
    if (method_exists($order, 'get_total_refunded')) {
        $refund = $order->get_total_refunded();
    }
    
    // Create accounting transaction
    $transaction_data = [
        'type' => 'income',
        'category' => 'Sales Revenue',
        'amount' => $total - $refund, // Net amount after refunds
        'date' => $order->get_date_completed() ? $order->get_date_completed()->date('Y-m-d') : date('Y-m-d'),
        'description' => sprintf('Order #%d - %s', $order_id, $order->get_billing_first_name() . ' ' . $order->get_billing_last_name()),
        'reference' => 'ORDER-' . $order_id,
        'source' => 'order',
        'source_id' => $order_id,
        'gross_amount' => $total,
        'net_amount' => $subtotal,
        'tax_amount' => $tax,
        'shipping_amount' => $shipping,
        'refund_amount' => $refund,
    ];
    
    $txn_id = b2b_accounting_create_transaction($transaction_data);
    
    if ($txn_id) {
        // Mark order as synced
        update_post_meta($order_id, '_acc_synced', true);
        update_post_meta($order_id, '_acc_transaction_id', $txn_id);
    }
}

/**
 * Hook: When order is refunded, update accounting transaction
 */
add_action('woocommerce_order_refunded', 'b2b_accounting_handle_order_refund', 10, 2);
function b2b_accounting_handle_order_refund($order_id, $refund_id) {
    $order = wc_get_order($order_id);
    if (!$order) {
        return;
    }
    
    $refund_amount = $order->get_total_refunded();
    
    // Create refund transaction (negative income or positive expense)
    $transaction_data = [
        'type' => 'expense',
        'category' => 'Refunds',
        'amount' => $refund_amount,
        'date' => date('Y-m-d'),
        'description' => sprintf('Refund for Order #%d', $order_id),
        'reference' => 'REFUND-' . $refund_id,
        'source' => 'order',
        'source_id' => $order_id,
        'refund_amount' => $refund_amount,
    ];
    
    b2b_accounting_create_transaction($transaction_data);
}

/* =====================================================
 * 7. PAYROLL INTEGRATION - Personnel Payments
 * ===================================================== */

/**
 * Hook: When personnel payment is made, create expense transaction
 * This hooks into the personnel module's payment creation
 */
add_action('personnel_payment_created', 'b2b_accounting_sync_payroll_expense', 10, 2);
function b2b_accounting_sync_payroll_expense($payment_id, $payment_data) {
    // Check if already synced
    if (isset($payment_data['posted_to_accounting']) && $payment_data['posted_to_accounting']) {
        return;
    }
    
    // Get personnel name
    $personnel_id = $payment_data['personnel_id'] ?? 0;
    $personnel_name = 'Unknown Employee';
    if ($personnel_id) {
        $person = get_post($personnel_id);
        if ($person) {
            $personnel_name = $person->post_title;
        }
    }
    
    // Determine transaction type and category
    $transaction_type = $payment_data['transaction_type'] ?? 'expense';
    $payment_type = $payment_data['payment_type'] ?? 'salary';
    
    // Map payment types to accounting categories
    $category_map = [
        'salary' => 'Salary Payments',
        'bonus' => 'Bonus Payments',
        'commission' => 'Commission Payments',
        'overtime' => 'Overtime Pay',
        'advance' => 'Advance Payments',
        'deduction' => 'Payroll Deductions',
        'reimbursement' => 'Expense Reimbursements',
    ];
    
    $acc_category = $category_map[$payment_type] ?? 'Payroll Expense';
    
    // For income transactions (like deductions collected), use income category
    if ($transaction_type === 'income') {
        $acc_category = 'Payroll Deductions Recovered';
    }
    
    // Create accounting transaction
    $transaction_data = [
        'type' => $transaction_type,
        'category' => $acc_category,
        'amount' => abs(floatval($payment_data['amount'] ?? 0)),
        'date' => $payment_data['date'] ?? date('Y-m-d'),
        'description' => sprintf(
            '%s - %s',
            $personnel_name,
            $payment_data['notes'] ?? ucfirst($payment_type) . ' payment'
        ),
        'reference' => 'PAYROLL-' . $payment_id,
        'source' => 'payroll',
        'source_id' => $payment_id,
    ];
    
    $txn_id = b2b_accounting_create_transaction($transaction_data);
    
    if ($txn_id) {
        // Mark payment as synced (would need to update the payment record)
        // This is stored in the payment_records meta, so we'd need to update the array
        // For now, we just create the transaction
        return $txn_id;
    }
    
    return false;
}

/**
 * Manually sync existing orders (one-time sync utility)
 */
function b2b_accounting_sync_existing_orders($limit = 50) {
    global $wpdb;
    
    // Get completed orders that haven't been synced yet
    // Using direct SQL query to avoid HPOS compatibility issues
    $order_ids = $wpdb->get_col($wpdb->prepare(
        "SELECT p.ID 
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_acc_synced'
        WHERE p.post_type = 'shop_order' 
        AND p.post_status = 'wc-completed'
        AND pm.meta_id IS NULL
        ORDER BY p.post_date DESC
        LIMIT %d",
        $limit
    ));
    
    $synced_count = 0;
    
    foreach ($order_ids as $order_id) {
        b2b_accounting_sync_order_income($order_id);
        $synced_count++;
    }
    
    return $synced_count;
}

/**
 * Manually sync existing personnel payments (one-time sync utility)
 */
function b2b_accounting_sync_existing_payroll($limit = 100) {
    global $wpdb;
    
    // Get unsync payments from personnel_transactions table
    $table_name = $wpdb->prefix . 'personnel_transactions';
    $payments = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$table_name} 
        WHERE id NOT IN (
            SELECT CAST(meta_value AS UNSIGNED) 
            FROM {$wpdb->prefix}personnel_data 
            WHERE meta_key = '_acc_synced'
        )
        ORDER BY date DESC
        LIMIT %d",
        $limit
    ), ARRAY_A);
    
    $synced_count = 0;
    
    foreach ($payments as $payment) {
        b2b_accounting_sync_payroll_expense($payment['id'], $payment);
        $synced_count++;
    }
    
    return $synced_count;
}

/**
 * Handler for sync orders action (called from dashboard button)
 */
function b2b_accounting_sync_orders_handler() {
    // Admin check
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        wp_redirect(home_url('/b2b-panel'));
        exit;
    }
    
    // Sync orders in batches
    $synced = b2b_accounting_sync_existing_orders(B2B_ACCOUNTING_SYNC_BATCH_SIZE);
    
    // Redirect back to dashboard with success message
    wp_redirect(add_query_arg('synced', $synced, home_url('/accounting-panel/dashboard')));
    exit;
}

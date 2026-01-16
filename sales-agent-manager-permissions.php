<?php
/**
 * Plugin Name: Sales Agent Manager Permissions
 * Plugin URI: https://github.com/yilmaz852/adminpanel
 * Description: Adds permission settings for sales managers to access agent customers and view all customers
 * Version: 1.0.0
 * Author: Admin Panel Team
 * Author URI: https://github.com/yilmaz852
 * Text Domain: sales-agent-manager
 * Requires PHP: 7.4
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Sales Agent Manager Permissions Plugin
 */
class Sales_Agent_Manager_Permissions {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Hook into admin menu
        add_action('admin_menu', array($this, 'add_settings_submenu'), 99);
        
        // Register settings
        add_action('admin_init', array($this, 'register_settings'));
    }
    
    /**
     * Add settings submenu under Sales Agent settings
     */
    public function add_settings_submenu() {
        // Check if we're on the sales agent settings page and add our fields there via hook
        add_action('admin_footer', array($this, 'inject_settings_to_sales_agent_page'));
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('sales_agent_settings', 'sales_manager_can_order');
        register_setting('sales_agent_settings', 'sales_view_all_customers');
    }
    
    /**
     * Inject settings into sales agent settings page using JavaScript
     */
    public function inject_settings_to_sales_agent_page() {
        $screen = get_current_screen();
        
        // Check if we're on the sales agent settings page
        if (!$screen || strpos($_SERVER['REQUEST_URI'], '/b2b-panel/settings/sales-agent') === false) {
            return;
        }
        
        $manager_can_order = get_option('sales_manager_can_order', 0);
        $view_all_customers = get_option('sales_view_all_customers', 0);
        
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Find the "Merge Duplicate Products" checkbox parent div
            var mergeProductsDiv = $('input[name="sales_merge_products"]').closest('div').parent();
            
            if (mergeProductsDiv.length > 0) {
                // Create HTML for new settings
                var managerPermissionsHTML = `
                    <div style="margin-bottom:15px;padding-top:15px;border-top:1px solid #e5e7eb;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="checkbox" name="sales_manager_can_order" value="1" 
                                   <?php echo checked($manager_can_order, 1, false); ?>
                                   style="width:18px;height:18px;cursor:pointer;">
                            <span style="font-weight:600;color:#374151;">
                                <i class="fa-solid fa-user-shield"></i> Sales Managers Can Create Orders for Agent Customers
                            </span>
                        </label>
                        <p style="color:#6b7280;font-size:12px;margin:5px 0 0 28px;">Allow sales managers to place orders on behalf of customers assigned to sales agents</p>
                    </div>
                    
                    <div style="margin-bottom:0;">
                        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                            <input type="checkbox" name="sales_view_all_customers" value="1"
                                   <?php echo checked($view_all_customers, 1, false); ?>
                                   style="width:18px;height:18px;cursor:pointer;">
                            <span style="font-weight:600;color:#374151;">
                                <i class="fa-solid fa-users-viewfinder"></i> View All Customers (Customer Role)
                            </span>
                        </label>
                        <p style="color:#6b7280;font-size:12px;margin:5px 0 0 28px;">Sales agents and managers can view all customers with "customer" role, not just assigned ones</p>
                    </div>
                `;
                
                // Insert after the merge products div
                mergeProductsDiv.append(managerPermissionsHTML);
                
                console.log('Sales Agent Manager Permissions: Settings injected successfully');
            } else {
                console.warn('Sales Agent Manager Permissions: Could not find merge products checkbox');
            }
        });
        </script>
        <?php
    }
}

// Initialize the plugin
new Sales_Agent_Manager_Permissions();

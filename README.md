# B2B Admin Panel V9.0

A comprehensive WordPress/WooCommerce B2B Admin Panel with full features for managing orders, products, and customers.

## Features

### 🔐 Authentication & Security
- Admin-only access with role-based security
- Secure login page with modern glassmorphic UI
- Session management and automatic redirects
- Password reset capabilities

### 📊 Dashboard
- Monthly and total sales metrics
- Order status tracking with visual indicators
- Delay warnings for old orders (15+ days)
- Sales agent performance tracking
- Customer count per agent

### 📦 Order Management
- Advanced filtering by status
- Search by order ID
- Warehouse approval system (A & B)
- Order details modal showing:
  - Customer information
  - Billing & shipping addresses
  - Customer notes
  - Warehouse delivery proof (POD) with photos
  - Warehouse approval logs
  - Product items with quantities
- Status update functionality
- PDF packing slip generation (if WPO_WCPDF plugin installed)
- Column visibility toggle

### 🛍️ Product Management
- Product listing with search
- Stock status display
- Product edit interface with:
  - Price and SKU management
  - Parent-level stock control
  - Variation management (for variable products)
  - Category assignment
  - Assembly service options
  - Complete change history logging

### 👥 Customer Management
- Customer listing with search
- B2BKing integration for B2B groups
- Sales agent assignment
- Customer edit interface with:
  - Personal information
  - Billing and shipping addresses
  - B2B group assignment
  - Password management

## Installation

1. Upload `adminpanel.php` to your WordPress installation
2. Activate it as a plugin or include it in your theme's `functions.php`
3. Visit `/b2b-login` to access the admin panel

## URL Structure

- Login: `/b2b-login`
- Dashboard: `/b2b-panel`
- Orders: `/b2b-panel/orders`
- Products: `/b2b-panel/products`
- Product Edit: `/b2b-panel/products/edit?id={product_id}`
- Customers: `/b2b-panel/customers`
- Customer Edit: `/b2b-panel/customers/edit?id={user_id}`

## Requirements

- WordPress 5.0+
- WooCommerce 4.0+
- PHP 7.4+
- Administrator role for access

## Optional Integrations

- **B2BKing**: For B2B customer groups and pricing
- **WPO WCPDF**: For PDF packing slip generation

## AJAX Actions

The panel uses the following AJAX actions:

1. `b2b_adm_get_details` - Fetch order details
2. `b2b_adm_update_status` - Update order status
3. `b2b_adm_wh_update` - Update warehouse approval status

## Logging System

All significant changes are logged automatically:
- Price updates
- Stock changes
- Variation updates

Logs are stored in the `_b2b_stock_log` meta field (limited to 50 entries per product).

## Security Notes

- Only users with `manage_options` capability can access the panel
- All inputs are sanitized and validated
- SQL queries use prepared statements
- AJAX requests are verified for admin capabilities

## Customization

### Colors
The panel uses CSS variables defined in the `:root` selector:
- `--primary`: #0f172a (dark blue)
- `--accent`: #3b82f6 (blue)
- `--bg`: #f3f4f6 (light gray)
- `--white`: #ffffff
- `--border`: #e5e7eb
- `--text`: #1f2937

### Warehouse Approval
The system supports two warehouses (A & B). Each order can be independently approved by each warehouse. Notes are logged with timestamps.

### ERP Mode Router
The panel includes an optional ERP mode router that can redirect all non-panel traffic to a master portal (currently commented out).

## Support

For issues or questions, please contact the development team.

## Version History

- **V9.0**: Complete rewrite with modern UI, B2BKing integration, and enhanced warehouse management
- Previous versions: V1.0 - V8.0 (various incremental improvements)

## License

Proprietary - All rights reserved

# WooCommerce Üretim Planlama (Production Planning)

A comprehensive production planning and scheduling plugin for WooCommerce that provides advanced order management, department tracking, analytics, and visual calendar features.

## Features

### 🎯 Core Features

- **Production Planning Dashboard** - Real-time overview of production status and key metrics
- **Department Management** - Create and manage production departments with capacity settings
- **Production Scheduling** - Auto-schedule orders with drag-and-drop interface
- **Visual Calendar** - Interactive calendar with FullCalendar integration
- **Analytics & Reporting** - Comprehensive production analytics and exportable reports
- **Product Routes** - Define production workflows for different products
- **Custom Order Statuses** - Track orders through production stages

### 📊 Custom Order Statuses

The plugin adds the following custom WooCommerce order statuses:

- `In Production` - Order is in production
- `In Cutting` - Cutting department
- `In Sewing` - Sewing department
- `Quality Control` - Quality check phase
- `Packaging` - Packaging phase
- `Ready to Ship` - Ready for shipment

### 🏭 Department Management

Create custom departments with:
- Department name and color coding
- Worker capacity
- Working hours configuration
- Status associations
- Base duration settings

### 📅 Production Calendar

Visual calendar features:
- Drag-and-drop scheduling
- Color-coded by status and department
- Filter by department, status, date range
- Event details and quick actions
- Touch-friendly for tablets

### 📈 Analytics

Track and analyze:
- Average production time per department
- On-time delivery rate
- Department efficiency metrics
- Production trends (daily/weekly/monthly)
- Bottleneck identification
- Export to CSV/PDF

## Requirements

- **WordPress**: 5.8 or higher
- **PHP**: 7.4 or higher
- **WooCommerce**: 6.0 or higher
- **MySQL**: 5.6 or higher (for JSON column support)

## Installation

### Method 1: WordPress Admin

1. Download the plugin ZIP file
2. Go to WordPress Admin → Plugins → Add New
3. Click "Upload Plugin" and select the ZIP file
4. Click "Install Now" and then "Activate"

### Method 2: Manual Installation

1. Upload the `woo-uretim-planlama` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Production Planning → Settings to configure

## Database Tables

The plugin creates the following database tables on activation:

### `wp_wup_status_history`
Tracks order status changes over time.

```sql
- id: Primary key
- order_id: WooCommerce order ID
- status: Order status
- changed_at: Timestamp
- changed_by: User ID who made the change
- notes: Additional notes
```

### `wp_wup_schedule`
Stores department scheduling information.

```sql
- id: Primary key
- order_id: WooCommerce order ID
- department_id: Department ID
- product_id: Product ID
- quantity: Order quantity
- scheduled_start: Start date/time
- scheduled_end: End date/time
- actual_start: Actual start (when set)
- actual_end: Actual end (when completed)
- status: Schedule status
- assigned_to: Assigned user ID
- priority: Priority level (1-10)
- notes: Additional notes
```

### `wp_wup_departments`
Stores department configurations.

```sql
- id: Primary key
- name: Department name
- slug: Unique slug
- capacity: Daily capacity
- working_hours: JSON working hours config
- is_active: Active status
- display_order: Sort order
- created_at: Creation timestamp
```

### `wp_wup_product_routes`
Defines production routes for products.

```sql
- id: Primary key
- product_id: WooCommerce product ID
- department_id: Department ID
- sequence_order: Order in production flow
- estimated_time: Estimated time in minutes
```

## Configuration

### Basic Settings

1. Navigate to **Production Planning → Settings**
2. Configure:
   - Personnel count
   - Daily working hours
   - Working days
   - Status durations
   - Notification settings
   - Cache duration

### Department Setup

1. Go to **Production Planning → Departments**
2. Click "Add New Department"
3. Configure:
   - Department name
   - Number of workers
   - Base duration (minutes)
   - Associated order statuses
   - Color for visual identification

### Product Routes

1. Edit a WooCommerce product
2. Find the "Production Route" meta box
3. Add departments in sequence
4. Set estimated time for each department

## Usage

### Viewing the Dashboard

1. Navigate to **Production Planning → Dashboard**
2. View real-time statistics:
   - Orders in production
   - Department utilization
   - Completion rates
   - Bottlenecks

### Scheduling Orders

1. Go to **Production Planning → Schedule**
2. Drag unscheduled orders to department slots
3. Or use "Auto Schedule" for automatic assignment
4. Adjust priorities and dates as needed

### Using the Calendar

1. Navigate to **Production Planning → Calendar**
2. View scheduled production tasks
3. Drag events to reschedule
4. Click events for details
5. Filter by department or status

### Viewing Analytics

1. Go to **Production Planning → Analytics**
2. Select date range and filters
3. View charts and metrics:
   - Department performance
   - Production trends
   - Efficiency metrics
4. Export reports to CSV/PDF

## Admin Menu Structure

```
Production Planning
├── Dashboard (Status overview)
├── Schedule (Production scheduling)
├── Calendar (Visual calendar)
├── Departments (Department management)
├── Analytics (Reports and charts)
└── Settings (Plugin configuration)
```

## Hooks & Filters

### Actions

```php
// After plugin initialization
do_action('wup_init');

// After order status change
do_action('wup_status_changed', $order_id, $old_status, $new_status);

// After schedule creation
do_action('wup_schedule_created', $schedule_id);

// After department created
do_action('wup_department_created', $department_id);
```

### Filters

```php
// Modify default departments
$departments = apply_filters('wup_default_departments', $departments);

// Modify order statuses
$statuses = apply_filters('wup_order_statuses', $statuses);

// Modify calendar events
$events = apply_filters('wup_calendar_events', $events);

// Modify analytics data
$data = apply_filters('wup_analytics_data', $data);
```

## JavaScript API

### Calendar

```javascript
// Refresh calendar
wupCalendar.refreshCalendar();

// Add event
wupCalendar.addEvent(eventData);

// Remove event
wupCalendar.removeEvent(eventId);
```

### Scheduler

```javascript
// Auto schedule
wupScheduler.autoSchedule();

// Optimize schedule
wupScheduler.optimizeSchedule();
```

### Analytics

```javascript
// Refresh data
wupAnalytics.refreshData();

// Export data
wupAnalytics.exportData();
```

## Security

The plugin implements WordPress security best practices:

- **Nonce verification** for all AJAX requests
- **Capability checks** (`manage_woocommerce`, `manage_options`)
- **Data sanitization** on all inputs
- **Output escaping** using `esc_html()`, `esc_attr()`, `esc_url()`
- **SQL injection prevention** with `$wpdb->prepare()`
- **XSS protection** on all user-generated content

## Performance

- **Caching** using WordPress Transients API
- **Query optimization** with proper indexes
- **Lazy loading** for large datasets
- **AJAX** for real-time updates without page reloads
- **Pagination** on all list views

## Localization

The plugin is translation-ready with text domain `woo-uretim-planlama`.

To translate:
1. Use PoEdit or similar tool
2. Create PO/MO files for your language
3. Place in `/languages/` folder
4. Format: `woo-uretim-planlama-{locale}.mo`

## Troubleshooting

### Plugin doesn't activate

**Solution**: Ensure WooCommerce is installed and activated first.

### Database tables not created

**Solution**: Deactivate and reactivate the plugin. Check database user permissions.

### Calendar not loading

**Solution**: Check browser console for JavaScript errors. Ensure jQuery is loaded.

### Charts not displaying

**Solution**: Verify Chart.js CDN is accessible. Check for JavaScript conflicts.

## Support

For issues and feature requests:
- GitHub: [https://github.com/yilmaz852/adminpanel](https://github.com/yilmaz852/adminpanel)
- Documentation: See inline code comments

## Changelog

### Version 1.0.0
- Initial release
- Complete production planning system
- Department management
- Visual calendar with FullCalendar
- Analytics and reporting
- Custom order statuses
- Product routes
- Auto-scheduling algorithm

## Credits

- **Author**: Yilmaz
- **FullCalendar**: [https://fullcalendar.io/](https://fullcalendar.io/)
- **Chart.js**: [https://www.chartjs.org/](https://www.chartjs.org/)

## License

GPL v2 or later - [https://www.gnu.org/licenses/gpl-2.0.html](https://www.gnu.org/licenses/gpl-2.0.html)

---

Made with ❤️ for production management

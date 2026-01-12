# Visual Guide: Zones and Customer Filters

## 1. Shipping Zones Flow

### Before (Old System - Import Based)
```
┌─────────────────┐
│  WooCommerce    │
│  Shipping Zones │
└────────┬────────┘
         │
         │ Manual Import
         ▼
┌─────────────────┐
│   B2B Admin     │
│   Panel Zones   │  ← Separate copy of data
│   (Duplicated)  │
└─────────────────┘
```

### After (New System - Direct Reading)
```
┌─────────────────────────────────────┐
│  WooCommerce Shipping Zones         │
│  (Single Source of Truth)           │
│  • Zone name, regions                │
│  • Flat rate, free shipping         │
└────────┬────────────────────────────┘
         │
         │ Direct Read (via WC_Shipping_Zones)
         ▼
┌─────────────────────────────────────┐
│  B2B Admin Panel                    │
│  • Displays WC zones                │
│  • Link to edit in WC               │
└────────┬────────────────────────────┘
         │
         │ Stores B2B Extensions
         ▼
┌─────────────────────────────────────┐
│  b2b_zone_extensions                │
│  (Linked by Zone ID)                │
│  • Group permissions                │
│  • Custom rates per group           │
└─────────────────────────────────────┘
```

## 2. Shipping Zones Admin Panel UI

### Zones List Page
```
┌─────────────────────────────────────────────────────────┐
│ Shipping Zones                                          │
├─────────────────────────────────────────────────────────┤
│ ℹ️ Note: Shipping zones are managed through            │
│   WooCommerce. Here you can configure B2B-specific     │
│   settings like group permissions and custom rates.     │
│   [Manage WooCommerce Shipping Zones →]                │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Shipping Zones                                          │
├──────────┬─────────┬──────────────┬─────────┬──────────┤
│ Name     │ Regions │ Methods      │ B2B Grps│ Actions  │
├──────────┼─────────┼──────────────┼─────────┼──────────┤
│ US Zone  │ US      │ Flat: $10    │ 2 grps  │[B2B ⚙️][WC]│
│          │         │ Free: $50+   │         │          │
├──────────┼─────────┼──────────────┼─────────┼──────────┤
│ Europe   │ GB, DE  │ Flat: $15    │ -       │[B2B ⚙️][WC]│
│          │         │ Free: $100+  │         │          │
└──────────┴─────────┴──────────────┴─────────┴──────────┘
```

### B2B Settings Page (for a zone)
```
┌─────────────────────────────────────────────────────────┐
│ B2B Settings for: US Zone                              │
├─────────────────────────────────────────────────────────┤
│ ℹ️ Zone Information (From WooCommerce)                 │
│   Regions: US                                           │
│   Priority: 1                                           │
│   Flat Rate: Standard Shipping - $10.00                │
│   Free Shipping: Free Shipping (min $50.00)            │
│   [Edit in WooCommerce →]                              │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│ Group-Based Permissions                                 │
├─────────────────────────────────────────────────────────┤
│                                                         │
│ ☑️ Wholesale Group                                      │
│   ┌────────────────────┬────────────────────┐         │
│   │ Flat Rate Cost ($) │ Free Shipping Min  │         │
│   │ [5.00]             │ [25]               │         │
│   │ Default: $10.00    │ Default: $50.00    │         │
│   └────────────────────┴────────────────────┘         │
│   Hide Methods: ☐ Flat Rate  ☐ Free Shipping          │
│                                                         │
│ ☑️ Retail Group                                         │
│   ┌────────────────────┬────────────────────┐         │
│   │ Flat Rate Cost ($) │ Free Shipping Min  │         │
│   │ [8.00]             │ [35]               │         │
│   │ Default: $10.00    │ Default: $50.00    │         │
│   └────────────────────┴────────────────────┘         │
│   Hide Methods: ☐ Flat Rate  ☐ Free Shipping          │
│                                                         │
│ [Save B2B Settings]  [Back to List]                    │
└─────────────────────────────────────────────────────────┘
```

## 3. Customer Filters

### Customers Page (With Filters)
```
┌─────────────────────────────────────────────────────────────────┐
│ Customers                                                       │
├─────────────────────────────────────────────────────────────────┤
│ [Columns ▼] [20/page ▼] [All Groups ▼] [All Roles ▼]          │
│                                                    Filtered: 25 │
│                                        [Search...] [🔍]         │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│ ID │ Customer        │ Contact         │ B2B Group │ Role      │
├────┼─────────────────┼─────────────────┼───────────┼───────────┤
│ 42 │ John Smith      │ john@email.com  │ Wholesale │ Wholesaler│
│    │ @jsmith         │ 555-0123        │           │           │
├────┼─────────────────┼─────────────────┼───────────┼───────────┤
│ 89 │ Jane Doe        │ jane@email.com  │ Wholesale │ Retailer  │
│    │ @jdoe           │ 555-0456        │           │           │
└────┴─────────────────┴─────────────────┴───────────┴───────────┘
```

### Filter Behavior Examples

**Example 1: Filter by Group**
```
Before: 150 customers total
Action: Select "Wholesale" from Groups dropdown
After:  Filtered: 45 customers
        Shows only customers in Wholesale group
        "Clear Filters" button appears
```

**Example 2: Filter by Role**
```
Before: 150 customers total
Action: Select "Retailer" from Roles dropdown
After:  Filtered: 60 customers
        Shows only customers with Retailer role
        "Clear Filters" button appears
```

**Example 3: Combined Filters**
```
Before: 150 customers total
Action: Select "Wholesale" group AND "Retailer" role
After:  Filtered: 15 customers
        Shows only customers that are:
        - In Wholesale group
        - AND have Retailer role
        "Clear Filters" button appears
```

**Example 4: Filter + Search**
```
Before: 150 customers total
Action: 1. Select "Wholesale" group (→ 45 results)
        2. Search "smith" (→ 5 results)
After:  Shows 5 customers named Smith in Wholesale group
        Both search and filter active
```

## 4. Data Flow Diagram

### Shipping at Checkout
```
Customer Cart
     │
     ▼
┌────────────────────────────┐
│ WooCommerce calculates     │
│ package_rates              │
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ B2B Filter Hook            │
│ (woocommerce_package_rates)│
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ b2b_get_all_shipping_zones()│
│ • Reads from WooCommerce    │
│ • Loads B2B extensions      │
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ Match customer country     │
│ Get customer groups        │
└──────────┬─────────────────┘
           │
           ▼
┌────────────────────────────┐
│ Apply B2B rates if:        │
│ • Customer in B2B group    │
│ • Group has zone perms     │
│ Otherwise: WC default      │
└──────────┬─────────────────┘
           │
           ▼
Display Shipping Options
```

### Customer Filtering Flow
```
Admin clicks filter dropdown
         │
         ▼
┌──────────────────────┐
│ onChange event fires │
└─────────┬────────────┘
          │
          ▼
┌──────────────────────────┐
│ Build URL with params:   │
│ ?filter_group=wholesale  │
│ &filter_role=retailer    │
│ &per_page=20             │
│ &s=search_term           │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ WP_User_Query with:      │
│ • meta_query for group   │
│ • meta_query for role    │
│ • search for keywords    │
└─────────┬────────────────┘
          │
          ▼
┌──────────────────────────┐
│ Display filtered results │
│ Show count & filters     │
└──────────────────────────┘
```

## 5. Code Structure

### Key Functions
```
Shipping Zones:
├── b2b_get_zone_extensions()
│   └── Returns: Array of B2B settings keyed by zone_id
├── b2b_update_zone_extension($zone_id, $data)
│   └── Saves B2B settings for a zone
└── b2b_get_all_shipping_zones()
    └── Returns: WC zones + B2B extensions merged

Customer Filters:
├── Customer List Page
│   ├── GET params: filter_group, filter_role
│   ├── Meta query building
│   └── Filter UI rendering
└── Pagination
    └── URL building with all params
```

### Database Schema
```
wp_options:
  └── b2b_zone_extensions (array)
       └── [zone_id] (integer)
            └── group_permissions (array)
                 └── [group_slug] (string)
                      ├── allowed: 1
                      ├── flat_rate_cost: 5.00 (nullable)
                      ├── free_shipping_min: 25.00 (nullable)
                      └── hidden_methods: ['flat_rate']

wp_usermeta:
  ├── b2b_group_slug (string) ← Used for group filter
  └── b2b_role (string) ← Used for role filter
```

## 6. User Journey Examples

### Journey 1: Admin Configures Shipping
1. Admin creates zone "Europe" in WooCommerce
   - Adds countries: GB, DE, FR
   - Adds Flat Rate: €15
   - Adds Free Shipping: €100 minimum
   - Saves

2. Admin opens B2B Admin Panel → Settings → Shipping
   - Sees "Europe" zone appear automatically
   - No import needed!

3. Admin clicks "B2B Settings" on Europe zone
   - Enables Wholesale group
   - Sets Flat Rate cost: €10 (instead of €15)
   - Sets Free Shipping min: €50 (instead of €100)
   - Saves

4. Result:
   - Regular customers: €15 flat, free at €100
   - Wholesale customers: €10 flat, free at €50

### Journey 2: Admin Filters Customers
1. Admin goes to Customers page
   - Sees 150 customers

2. Admin wants to email all wholesale customers
   - Selects "Wholesale" from Groups filter
   - List narrows to 45 customers
   - Exports list (if export feature exists)

3. Admin wants wholesale customers who are retailers
   - Keeps "Wholesale" group selected
   - Selects "Retailer" from Roles filter
   - List narrows to 15 customers

4. Admin wants to find a specific customer
   - Keeps both filters active
   - Types "smith" in search
   - Finds customer immediately

## 7. Error Handling

### Shipping Zones
```
If WooCommerce not active:
┌─────────────────────────────────┐
│ ⚠️ WooCommerce is not active.   │
│    Shipping zones are managed   │
│    through WooCommerce.         │
│                                 │
│    Please activate WooCommerce  │
│    to manage shipping zones.    │
└─────────────────────────────────┘

If no zones exist:
┌─────────────────────────────────┐
│ No shipping zones configured.   │
│                                 │
│ [Create zones in WooCommerce →] │
└─────────────────────────────────┘
```

### Customer Filters
```
If no customers match filters:
┌─────────────────────────────────┐
│ No customers found.             │
│                                 │
│ Current filters:                │
│ • Group: Wholesale              │
│ • Role: Retailer                │
│                                 │
│ [Clear Filters]                 │
└─────────────────────────────────┘
```

---

## Quick Reference

### Shipping Zones
- **Manage zones**: WooCommerce → Settings → Shipping
- **B2B settings**: Admin Panel → Settings → Shipping
- **Data storage**: `b2b_zone_extensions` option

### Customer Filters
- **Location**: Admin Panel → Customers
- **Filters**: Groups dropdown, Roles dropdown
- **Clear**: Click "Clear Filters" or select "All"
- **Combine**: Any filter + search + per-page

### Support
- Zones sync automatically (no manual refresh)
- Filters maintain state during pagination
- All URLs are bookmarkable

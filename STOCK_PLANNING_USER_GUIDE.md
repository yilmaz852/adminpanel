# Stock Planning Module - User Guide

## 📋 Overview

The Stock Planning Module is a comprehensive inventory management system integrated into the B2B Admin Panel. It helps you track sales, forecast stock needs, manage supplier orders, and automate inventory updates.

**Version:** V12  
**Release Date:** January 16, 2026  
**Module Type:** Admin-only (requires `manage_options` capability)

---

## 🎯 Key Features

### 1. Sales Analysis Report
- Real-time stock level monitoring
- Sales velocity calculation (daily average)
- Stock forecasting (days until stockout)
- Automatic reorder suggestions
- Color-coded alerts for stock levels

### 2. Supplier Order Management
- Create purchase orders for suppliers
- Track ordered quantities
- Mark orders as received
- Automatic inventory updates
- Edit/Delete orders before receiving

### 3. Two-Report System
- **Report 1:** Sales analysis with current stock + ordered stock
- **Report 2:** Supplier orders (open and closed forms)
- Seamless integration between both reports

---

## 📍 Accessing the Module

### Navigation

From the admin panel sidebar:

```
Stock Planning ▼
├─ Sales Analysis
└─ Supplier Orders
```

**URLs:**
- Sales Analysis: `/b2b-panel/stock-planning`
- Supplier Orders: `/b2b-panel/stock-planning/supplier-orders`

---

## 📊 Sales Analysis Report

### Overview

The Sales Analysis page provides a comprehensive view of your product sales, stock levels, and reorder needs.

### How to Use

#### 1. Generate Report

**Filters Available:**
- **Year:** Select the year for analysis
- **Start Date:** Custom start date (format: dd.mm.yyyy)
- **End Date:** Custom end date (format: dd.mm.yyyy)
- **Order Status:** Select which order statuses to include (completed, processing, etc.)
- **Min Supply Days:** Threshold for low stock alerts (default: 80 days)

**Example:**
```
Year: 2026
Start Date: 01.01.2026
End Date: 16.01.2026
Order Status: ☑ Completed, ☑ Processing
Min Supply Days: 80
```

Click **"Generate Report"** to run the analysis.

#### 2. Understanding the Widgets

**Revenue Summary:**
```
┌─────────────────┬─────────────────┬─────────────────┐
│ Net             │ Tax             │ Gross           │
│ $45,230.50      │ $4,523.05       │ $49,753.55      │
└─────────────────┴─────────────────┴─────────────────┘
```

**Stock Alerts:**
```
┌─────────────────┬─────────────────┬─────────────────┐
│ Zero Stock      │ Supply Passed   │ Supply < 10 days│
│ 5 products      │ 12 products     │ 8 products      │
└─────────────────┴─────────────────┴─────────────────┘
```

- **Zero Stock:** Products completely out of stock
- **Supply Passed:** Products below minimum supply days threshold
- **Supply < 10 days:** Products that will run out within 10 days

#### 3. Reading the Data Table

**Column Descriptions:**

| Column | Description | Example |
|--------|-------------|---------|
| **SKU** | Product stock keeping unit | PROD-001 |
| **Name** | Product name | Blue T-Shirt |
| **Category** | Product category | Clothing |
| **Qty Sold** | Total quantity sold in period | 45 |
| **Revenue** | Total revenue from product | $899.50 |
| **Stock** | Current stock quantity | 20 |
| **Ordered Qty** | Quantity on order from supplier | 100 |
| **Order Note** | Supplier order details | 2026-01-10: Restock |
| **Days** | Number of days analyzed | 16 |
| **Avg/Day** | Average daily sales | 2.813 |
| **Days Left** | Days until stockout | 42 |
| **Gap** | Days above/below minimum | -38 |

**Color Coding:**

🔴 **Red Background:** Critical - Zero stock or Days Left < Min Supply Days  
🟡 **Yellow Background:** Warning - Days Left between Min Supply Days and Min+10  
🟢 **Green Background:** Received - Supplier order has been received  

#### 4. Auto-Add to Supplier Orders

If products need reordering, you'll see a button:

```
[Add 12 Products to Supplier Orders]
```

**What it does:**
- Automatically creates supplier orders for products below minimum stock
- Calculates recommended order quantity based on:
  - Average daily sales
  - Minimum supply days threshold
  - Current stock level

**Process:**
1. Click the button
2. Confirm the action
3. Products are added to Supplier Orders page
4. Redirect to Supplier Orders for review

#### 5. Export to CSV

Click **"Export CSV"** to download the report as a spreadsheet.

**File includes:**
- All columns from the table
- Current filters applied
- Timestamp in filename

---

## 📦 Supplier Orders Management

### Overview

The Supplier Orders page lets you create, track, and manage purchase orders to suppliers.

### Adding a Supplier Order

#### Step 1: Search for Product

1. Click in the **"SKU"** field
2. Start typing the product SKU or name
3. Select from the autocomplete dropdown

```
┌────────────────────────────────┐
│ SKU Search...                  │
├────────────────────────────────┤
│ PROD-001 - Blue T-Shirt        │
│ PROD-002 - Red T-Shirt         │
│ PROD-003 - Green T-Shirt       │
└────────────────────────────────┘
```

#### Step 2: Fill Order Details

**Fields:**
- **SKU:** Auto-filled from search
- **Product Name:** Auto-filled from search (read-only)
- **Ordered Qty:** Quantity you're ordering **(required)**
- **Order Date:** Date you placed the order (default: today)
- **Note:** Optional note (e.g., "Bulk restock", "Supplier: ABC Corp")

#### Step 3: Add Order

Click **"Add Order"** to save the supplier order.

**Result:**
- Order appears in the table below
- Product shows in Sales Analysis with "Ordered Qty"
- "Days Left" calculation updated to include ordered stock

### Managing Supplier Orders

#### Editing Quantity

For **open orders** (not yet received):

1. Locate the order in the table
2. Change the quantity in the inline form
3. Click **"Save"**

```
[70] [Save]  → Changes to →  [100] [Save]
```

#### Marking as Received

When supplier delivers the order:

1. Click **"Mark Received"** button
2. Confirm the action

**What happens:**
- ✅ Order status changes to "Received"
- ✅ Product stock automatically increased by ordered quantity
- ✅ Row background turns green with lower opacity
- ✅ Can no longer edit or delete
- ✅ Activity log entry created
- ✅ "Ordered Qty" in Sales Analysis updates

**Example:**
```
Before:
Product Stock: 20
Ordered Qty: 100
[Mark Received]

After:
Product Stock: 120 (20 + 100)
Ordered Qty: 0
✓ Received
```

#### Deleting Orders

For **open orders only**:

1. Click **"Delete"** button
2. Confirm deletion

**Note:** Cannot delete received orders (historical record).

### Table Features

The Supplier Orders table uses DataTables for advanced functionality:

**Features:**
- **Search:** Filter by any column value
- **Sort:** Click column headers to sort
- **Pagination:** Navigate through pages (25 per page)
- **Entries:** Shows "Showing X to Y of Z entries"

---

## 🔄 Workflow Examples

### Example 1: Daily Stock Check

**Scenario:** Check which products need reordering

1. Go to **Stock Planning → Sales Analysis**
2. Use default filters (current year, completed orders)
3. Click **Generate Report**
4. Check the **"Supply Passed"** widget
5. Review red-highlighted rows in the table
6. Click **"Add X Products to Supplier Orders"**
7. Go to Supplier Orders page
8. Review and adjust quantities
9. Place actual orders with suppliers
10. Update order dates and notes

### Example 2: Receiving Supplier Delivery

**Scenario:** Supplier delivered 100 units of PROD-001

1. Go to **Stock Planning → Supplier Orders**
2. Find the order for PROD-001
3. Verify quantity matches delivery (edit if needed)
4. Click **"Mark Received"**
5. Confirm the action
6. Check product page - stock should be updated
7. Row turns green to indicate completion

### Example 3: Monthly Stock Analysis

**Scenario:** Analyze January sales and plan February orders

1. Go to **Stock Planning → Sales Analysis**
2. Set filters:
   - Start Date: 01.01.2026
   - End Date: 31.01.2026
   - Order Status: Completed, Processing
   - Min Supply Days: 60 (2 months buffer)
3. Generate Report
4. Export to CSV for records
5. Review "Avg/Day" to understand velocity
6. Use "Gap" column to prioritize reorders
7. Click Auto-Add button for critical items
8. Manually add additional items if needed

---

## 📈 Understanding Stock Metrics

### Days Left Calculation

```
Days Left = (Current Stock + Ordered Qty) / Average Daily Sales
```

**Example:**
- Current Stock: 50 units
- Ordered Qty: 100 units
- Average Daily Sales: 3 units/day
- Days Left: (50 + 100) / 3 = **50 days**

### Gap Calculation

```
Gap = Days Left - Min Supply Days
```

**Interpretation:**
- **Negative Gap (-38):** Need to reorder ASAP (38 days short)
- **Zero Gap (0):** At minimum threshold
- **Positive Gap (+25):** Above threshold (25 days extra buffer)

### Average Daily Sales

```
Avg Daily Sales = Total Qty Sold / Number of Days in Period
```

**Example:**
- Period: 16 days (Jan 1-16)
- Total Sold: 45 units
- Avg/Day: 45 / 16 = **2.813 units/day**

---

## ⚙️ Settings & Configuration

### Adjusting Min Supply Days

**Default:** 80 days

**How to change:**
1. On Sales Analysis page
2. Modify "Min Supply Days" field
3. Click "Generate Report"

**Recommendations:**
- **Fast-moving products:** 30-60 days
- **Regular products:** 60-90 days
- **Slow-moving products:** 90-180 days
- **Seasonal products:** Adjust based on season

---

## 🎨 Visual Indicators

### Stock Level Colors

| Color | Meaning | Condition |
|-------|---------|-----------|
| 🔴 Red | Critical | Stock ≤ 0 OR Days Left < Min Supply Days |
| 🟡 Yellow | Warning | Min Supply Days ≤ Days Left < Min + 10 |
| ⚪ White | Normal | Days Left ≥ Min Supply Days + 10 |
| 🟢 Green | Received | Supplier order received and processed |

### Status Icons

| Icon/Text | Meaning |
|-----------|---------|
| ✓ Received | Order completed and stock updated |
| [Mark Received] | Button to receive order |
| [Save] | Button to save quantity edit |
| [Delete] | Button to remove open order |

---

## 🐛 Troubleshooting

### Issue: Product not found in SKU search

**Possible causes:**
- Product is draft/unpublished
- SKU is incorrect
- Product is a variation (search by parent SKU)

**Solution:**
- Verify product is published in WooCommerce
- Check SKU spelling
- Search by product name instead

### Issue: Stock not updating after "Mark Received"

**Check:**
- Product has "Manage stock" enabled
- Product ID was correctly captured during order creation
- Check Activity Log for stock update entry

**Solution:**
- Verify product stock management settings
- If needed, manually update stock and note in Activity Log

### Issue: Days Left shows "-"

**Cause:** Average daily sales is 0 (no sales in period)

**Solution:**
- Extend date range to include more sales data
- Manually estimate reorder needs
- Consider marking as slow-moving product

### Issue: Auto-add button not appearing

**Cause:** No products meet the criteria (all above Min Supply Days)

**Solution:**
- Lower Min Supply Days threshold
- Check if all products have ordered stock already
- Manually add orders if needed

---

## 📝 Best Practices

### 1. Regular Monitoring
- ✅ Check Sales Analysis **weekly**
- ✅ Review Supplier Orders **daily** during receiving
- ✅ Export monthly reports for records
- ✅ Update Min Supply Days based on seasons

### 2. Order Management
- ✅ Add orders **immediately** after placing with supplier
- ✅ Include supplier name in **Notes** field
- ✅ Mark received **same day** as delivery
- ✅ Verify quantities before marking received

### 3. Data Accuracy
- ✅ Ensure all orders have correct status (completed/processing)
- ✅ Keep product SKUs consistent
- ✅ Enable stock management on all products
- ✅ Use realistic Min Supply Days thresholds

### 4. Analysis Tips
- ✅ Use **longer date ranges** for accurate averages (30-90 days)
- ✅ Compare **multiple periods** to spot trends
- ✅ Consider **seasonal variations** in calculations
- ✅ Review **Gap column** to prioritize reorders

---

## 🔒 Security & Permissions

### Access Requirements

**Required Capability:** `manage_options`

**Typical Roles with Access:**
- Administrator

**Not Accessible by:**
- Shop Manager
- Customer
- Subscriber

### Data Protection

- ✅ All forms use WordPress nonce verification
- ✅ AJAX requests require admin authentication
- ✅ SQL queries use prepared statements
- ✅ Input sanitization on all user data
- ✅ Activity logging for audit trails

---

## 📚 Related Documentation

- [INVENTORY_PLANNING_MODULE.md](./INVENTORY_PLANNING_MODULE.md) - Technical specification
- [PRODUCTS_MODULE_GUIDE.md](./PRODUCTS_MODULE_GUIDE.md) - Product management features
- [FUTURE_DEVELOPMENT_SUGGESTIONS.md](./FUTURE_DEVELOPMENT_SUGGESTIONS.md) - Planned enhancements

---

## 🆘 Support

### Common Questions

**Q: Can I delete a received order?**  
A: No, received orders are historical records and cannot be deleted.

**Q: What happens if I mark an order as received twice?**  
A: The system prevents this. Once marked, the button is replaced with "✓ Received".

**Q: Can I partial receive an order?**  
A: Yes! Edit the quantity before marking as received. Create a second order for the remainder.

**Q: How does this affect regular WooCommerce stock?**  
A: Marking orders as received directly updates WooCommerce product stock using the native API.

**Q: Can I bulk receive multiple orders?**  
A: Not yet - this feature is planned for future versions.

---

## 🚀 Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl + F` | Focus on table search |
| `Tab` | Navigate form fields |
| `Enter` | Submit form (when focused) |
| `Esc` | Close autocomplete dropdown |

---

## 📊 Report Examples

### Example Output - Sales Analysis

```
Summary:
• Net: $12,450.00
• Tax: $1,245.00
• Gross: $13,695.00
• Zero Stock: 3
• Supply Passed: 8
• Supply Soon: 5

Top Alert Products:
1. PROD-001 - Blue T-Shirt: 2 days left (CRITICAL)
2. PROD-003 - Red Pants: 15 days left (NEEDS REORDER)
3. PROD-007 - Green Jacket: 85 days left (WARNING)
```

### Example Output - Supplier Orders

```
Open Orders: 8
Received Orders: 42

Recent Orders:
• PROD-001 - Ordered 100 units on 2026-01-15 [Mark Received]
• PROD-003 - Ordered 200 units on 2026-01-14 [Mark Received]
• PROD-005 - Received 150 units on 2026-01-13 ✓
```

---

**Last Updated:** January 16, 2026  
**Module Version:** V12  
**Author:** GitHub Copilot  
**Client:** yilmaz852

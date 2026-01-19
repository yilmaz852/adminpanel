# Customer Order Panel - Comprehensive Design Plan
## Cabinet Wholesale B2B Customer Portal

---

## 🎯 Project Overview

**Purpose**: Create a modern, intuitive B2B customer portal for cabinet wholesalers where customers can browse products, configure cabinet orders, and place orders efficiently.

**Target Users**: B2B customers (retailers, contractors, designers) ordering cabinets and components

**Key Requirements**:
- Integration with WooCommerce My Account
- New "New Order" menu for streamlined ordering
- Modern, professional UI/UX
- Mobile-responsive design
- Category-based product browsing
- Efficient cart/order management

---

## 🔍 Industry Research - Cabinet B2B Portals

### Best Practices from Leading Cabinet Suppliers:

**1. Product Organization Patterns:**
- **Primary Selection**: Cabinet Type/Style (Kitchen, Bathroom, Office, etc.)
- **Secondary Selection**: Specific Products within category
- **Configuration**: Size, finish, hardware options
- **Add-ons**: Matching components, accessories

**2. Common Navigation Structures:**
```
Home Dashboard
├── New Order ⭐ (Primary CTA)
├── Order History
├── Quotes/Estimates
├── My Account
├── Favorites/Saved Configurations
└── Support/Resources
```

**3. Ordering Flow Patterns:**

**Pattern A - Category First (Recommended for Cabinets):**
```
1. Select Cabinet Category (Kitchen Base, Wall, Tall, etc.)
2. View Products in Grid/List with filters
3. Click product → Configure (size, finish, hardware)
4. Add to cart
5. Continue shopping or checkout
```

**Pattern B - Quick Order (For Repeat Customers):**
```
1. SKU/Product quick search
2. Add directly to cart with basic options
3. Bulk add from CSV/Excel
```

---

## 🎨 UI/UX Design Recommendations

### Modern Design Principles:

**Color Scheme** (Professional Cabinet Industry):
- Primary: Deep Navy/Charcoal (#1e293b, #0f172a)
- Accent: Warm Wood Tones (#d4a574, #8b6f47)
- Success: Forest Green (#047857)
- Background: Light Gray/Cream (#f8f9fa, #fefefe)

**Typography**:
- Headers: Inter, Montserrat (Modern, Clean)
- Body: System UI, Roboto (Readable)
- Sizes: Large CTAs (16-18px), Clear hierarchy

**Layout Style**:
- Clean, spacious (plenty of white space)
- Card-based product displays
- Sticky header with cart summary
- Sidebar filters (desktop) / Drawer filters (mobile)

---

## 📋 Detailed Panel Structure

### 1. Main Navigation Menu

```
┌─────────────────────────────────────────┐
│  [Logo]  Customer Portal    [Cart: 5] 👤 │
├─────────────────────────────────────────┤
│                                         │
│  📦 New Order ⭐                        │
│  📊 Dashboard                           │
│  📜 Orders                              │
│  ❤️  Favorites                          │
│  💬 Messages                            │
│  👤 My Account                          │
│  ⚙️  Settings                           │
│  🚪 Logout                              │
│                                         │
└─────────────────────────────────────────┘
```

### 2. New Order Page - Product Selection Flow

#### **Step 1: Category Selection (Landing)**

```
┌─────────────────────────────────────────────────────┐
│  New Order > Select Category                       │
├─────────────────────────────────────────────────────┤
│                                                     │
│  [Search: "Search products or SKU..."]             │
│                                                     │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │ Kitchen  │ │ Bathroom │ │  Office  │          │
│  │ Cabinets │ │ Cabinets │ │ Cabinets │          │
│  │   [→]    │ │   [→]    │ │   [→]    │          │
│  └──────────┘ └──────────┘ └──────────┘          │
│                                                     │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐          │
│  │   Base   │ │   Wall   │ │   Tall   │          │
│  │ Cabinets │ │ Cabinets │ │ Cabinets │          │
│  │   [→]    │ │   [→]    │ │   [→]    │          │
│  └──────────┘ └──────────┘ └──────────┘          │
│                                                     │
│  Or: [Quick Order by SKU]                         │
└─────────────────────────────────────────────────────┘
```

#### **Step 2: Product List with Filters**

```
┌─────────────────────────────────────────────────────────────┐
│  New Order > Kitchen Cabinets > Base Cabinets             │
├──────────┬──────────────────────────────────────────────────┤
│ Filters  │  Products (24 items)         [Grid] [List]      │
│          │  ──────────────────────────────────────────────  │
│ Category │                                                  │
│ ☑ Base   │  ┌─────────────┐ ┌─────────────┐ ┌──────────┐  │
│ ☐ Wall   │  │  [Image]    │ │  [Image]    │ │ [Image]  │  │
│ ☐ Tall   │  │             │ │             │ │          │  │
│          │  │ Base 18"    │ │ Base 24"    │ │ Base 30" │  │
│ Width    │  │ SKU: BC-18  │ │ SKU: BC-24  │ │ BC-30    │  │
│ ☐ 12"    │  │             │ │             │ │          │  │
│ ☑ 18"    │  │ $245.00     │ │ $295.00     │ │ $345.00  │  │
│ ☐ 24"    │  │ In Stock    │ │ In Stock    │ │ Low Stock│  │
│ ☐ 30"    │  │             │ │             │ │          │  │
│          │  │ [Configure] │ │ [Configure] │ │[Configure]│  │
│ Finish   │  └─────────────┘ └─────────────┘ └──────────┘  │
│ ☐ White  │                                                  │
│ ☑ Wood   │  [Load More...]                                 │
│ ☐ Black  │                                                  │
│          │                                                  │
└──────────┴──────────────────────────────────────────────────┘
```

#### **Step 3: Product Configuration Modal/Page**

```
┌─────────────────────────────────────────────────┐
│  Configure: Base Cabinet 18"          [Close] │
├─────────────────────────────────────────────────┤
│                                                 │
│  [Large Product Image]        SKU: BC-18       │
│  [Thumbnail] [Thumbnail]                       │
│                                                 │
│  ┌─ Specifications ─────────────────────────┐  │
│  │ Width: 18"                              │  │
│  │ Height: 34.5"                           │  │
│  │ Depth: 24"                              │  │
│  └──────────────────────────────────────────┘  │
│                                                 │
│  Finish: [Dropdown: Select Finish ▼]          │
│  ☐ Natural Oak  ☑ White Shaker  ☐ Gray       │
│                                                 │
│  Hardware: [Dropdown: Select Hardware ▼]      │
│  ☐ Brushed Nickel  ☑ Chrome  ☐ Black         │
│                                                 │
│  Quantity: [- 1 +]                            │
│                                                 │
│  Price: $245.00 × 1 = $245.00                 │
│                                                 │
│  [Add to Cart]  [Add & Configure Another]     │
│                                                 │
└─────────────────────────────────────────────────┘
```

#### **Step 4: Cart & Checkout**

```
┌─────────────────────────────────────────────────────┐
│  Shopping Cart (5 items)                  [Continue]│
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌───────────────────────────────────────────────┐ │
│  │ ☑ Base Cabinet 18" - White Shaker            │ │
│  │    SKU: BC-18-WS | Qty: [2] | $245 | $490   │ │
│  │    [Edit] [Remove]                           │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  ┌───────────────────────────────────────────────┐ │
│  │ ☑ Wall Cabinet 30" - Natural Oak             │ │
│  │    SKU: WC-30-NO | Qty: [3] | $195 | $585   │ │
│  │    [Edit] [Remove]                           │ │
│  └───────────────────────────────────────────────┘ │
│                                                     │
│  ──────────────────────────────────────────────── │
│  Subtotal:                              $1,075.00 │
│  Tax (8%):                                 $86.00 │
│  Shipping:                                 $50.00 │
│  ──────────────────────────────────────────────── │
│  Total:                                 $1,211.00 │
│                                                     │
│  [Continue Shopping]  [Proceed to Checkout]       │
│                                                     │
└─────────────────────────────────────────────────────┘
```

---

## 🛠️ Technical Implementation Plan

### Phase 1: Foundation (Week 1-2)
1. Create customer panel route structure
2. Implement authentication & user verification
3. Set up base layout with navigation
4. Integrate WooCommerce My Account pages

### Phase 2: Product Browsing (Week 3-4)
1. Build category selection page
2. Create product listing with filters
3. Implement search functionality
4. Add pagination/infinite scroll

### Phase 3: Order Configuration (Week 5-6)
1. Product configuration modal/page
2. Variant selection (size, finish, hardware)
3. Quantity selector
4. Price calculator with dynamic updates

### Phase 4: Cart & Checkout (Week 7-8)
1. Shopping cart functionality
2. Cart editing (update quantities, remove items)
3. Order summary
4. Checkout integration with WooCommerce

### Phase 5: Additional Features (Week 9-10)
1. Favorites/Saved configurations
2. Quick reorder from history
3. Bulk order from CSV
4. Order tracking

### Phase 6: Polish & Testing (Week 11-12)
1. Mobile optimization
2. Performance optimization
3. User testing & feedback
4. Bug fixes & refinements

---

## 📱 Mobile-First Considerations

### Mobile Navigation:
```
[☰ Menu]  Customer Portal  [🛒 5]

Drawer Menu:
├── 📦 New Order ⭐
├── 📊 Dashboard
├── 📜 Orders
├── ❤️ Favorites
└── More...
```

### Mobile Product Card:
```
┌─────────────────────┐
│    [Product Image]  │
│                     │
│  Base Cabinet 18"   │
│  SKU: BC-18         │
│  $245.00            │
│  In Stock ✓         │
│                     │
│  [Quick Add]        │
└─────────────────────┘
```

---

## 🎯 Key Features Prioritization

### Must-Have (MVP):
✅ User authentication
✅ Product browsing by category
✅ Product configuration (variants)
✅ Add to cart
✅ Cart management
✅ Checkout integration
✅ Order history
✅ Mobile responsive

### Should-Have (Phase 2):
⭐ Advanced filters
⭐ Product search
⭐ Favorites/Wishlist
⭐ Quick reorder
⭐ Price tiers (volume discounts)

### Nice-to-Have (Future):
💡 AR/3D product visualization
💡 Design tool integration
💡 Bulk upload from spreadsheet
💡 Custom configuration save/share
💡 Real-time stock updates
💡 Live chat support

---

## 🎨 Visual Design Mockup Structure

### Color Palette:
```
Primary Dark:    #0f172a (Navy)
Primary Light:   #3b82f6 (Blue)
Accent:          #d4a574 (Wood Tone)
Success:         #10b981 (Green)
Warning:         #f59e0b (Amber)
Error:           #ef4444 (Red)
Background:      #f8fafc (Light Gray)
White:           #ffffff
Text:            #1e293b (Dark)
Text Muted:      #64748b (Gray)
```

### Component Library:
- Buttons: Rounded (8px), gradient or solid
- Cards: White bg, subtle shadow, rounded corners (12px)
- Inputs: Border, focus states, clear labels
- Modals: Centered, overlay backdrop
- Toast notifications: Top-right, auto-dismiss

---

## 📊 Database Schema Additions

### New Tables Needed:

```sql
-- Customer favorites/wishlist
CREATE TABLE customer_favorites (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    product_id BIGINT,
    configuration JSON,
    created_at DATETIME
);

-- Saved configurations
CREATE TABLE saved_configurations (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT,
    name VARCHAR(255),
    products JSON,
    created_at DATETIME,
    updated_at DATETIME
);

-- Quick order history (for SKU quick add)
CREATE TABLE customer_order_frequency (
    user_id BIGINT,
    product_id BIGINT,
    order_count INT,
    last_ordered DATETIME,
    PRIMARY KEY (user_id, product_id)
);
```

---

## 🚀 Recommended Product Selection Flow

### **Recommended: Category-First Approach**

**Why?**
- Cabinets are organized by type and purpose
- Customers think in terms of project needs (kitchen vs bathroom)
- Easier to filter and find specific products
- Better for browsing and discovery

**Flow:**
```
1. Dashboard → Click "New Order"
2. Select Category (Kitchen Base, Wall, Tall, etc.)
3. View filtered products with sidebar filters
4. Click product → Configure options
5. Add to cart
6. Continue shopping OR proceed to checkout
```

**Alternative for Experienced Users:**
- Quick Order: Direct SKU entry with autocomplete
- Reorder: One-click from order history
- Bulk Order: CSV/Excel upload

---

## 📝 Content & Copy Recommendations

### CTA Buttons:
- "Start New Order" (instead of just "Order")
- "Configure & Add to Cart" (clear action)
- "Continue Shopping" / "Checkout Now" (clear choices)

### Product Info:
- Show: SKU, Price, Stock status, Lead time
- Highlight: Specs (dimensions), Materials, Finish options
- Include: Product images, Technical drawings if available

### Help Text:
- Tooltips for complex options
- "Need help?" link to support
- Product comparison tool

---

## 🔐 Security & Permissions

### Customer Access Control:
- Verify B2B customer status
- Check pricing tier/discount group
- Enforce minimum order quantities if applicable
- Hide products not available to customer tier

### Price Display:
- Show customer-specific pricing
- Display volume discounts
- Show tax calculation (if applicable)
- Indicate shipping costs upfront

---

## 📈 Success Metrics

### Track:
- Order completion rate
- Average cart value
- Time to complete order
- Mobile vs desktop usage
- Most ordered products
- Drop-off points in funnel

---

## 🎬 Next Steps

1. **Review & Approve** this plan
2. **Create wireframes** for key pages
3. **Design mockups** in Figma/Adobe XD
4. **Develop prototype** (static HTML/CSS)
5. **User testing** with real customers
6. **Begin implementation** in phases

---

## 📞 Questions for Stakeholder

Before starting implementation:

1. **Product Configuration**: What variants do cabinets typically have? (Size, Finish, Hardware, other?)
2. **Pricing**: Are there volume discounts? Customer-specific pricing?
3. **Inventory**: Real-time stock display needed?
4. **Customization**: Can customers request custom modifications?
5. **Quotes**: Do some orders need approval/quotes before purchase?
6. **Payment**: Payment on order or NET terms for B2B?
7. **Shipping**: Calculated at checkout or fixed rates?
8. **Minimums**: Any minimum order quantities or values?

---

**Document Version**: 1.0  
**Created**: January 17, 2026  
**Status**: Planning Phase - Awaiting Approval  
**Next Update**: After stakeholder review


# Sales Agent Integration Plan

## Overview
Integrate sales agent dashboard system into existing B2B admin panel with role-based access control.

## Requirements
1. **Administrators** → See full B2B admin panel (current functionality)
2. **Sales Agent** → See sales-specific dashboard
3. **Sales Manager** → See sales-specific dashboard with manager features
4. **Settings Integration** → Add sales agent configuration in admin settings

## Architecture

### Role-Based Routing
```
User Login
    ↓
Check Role
    ├─→ Administrator → /b2b-panel (full admin)
    ├─→ Sales Agent → /sales-panel (sales dashboard)
    └─→ Sales Manager → /sales-panel (sales dashboard)
```

### URL Structure
- Admin Panel: `/b2b-panel/*` (existing)
- Sales Panel: `/sales-panel/*` (new)
- Sales Login: `/sales-login` (new)

## Implementation Phases

### Phase 1: Core Role System (Priority 1)
**Files to modify:** `adminpanel.php`

**Changes:**
1. Add sales agent roles in init action
   - `sales_agent` role
   - `sales_manager` role
   - Capabilities: `view_sales_panel`, `switch_to_customer`, `create_sales_order`

2. Add sales panel URL rewrite rules
   ```php
   add_rewrite_rule('^sales-login/?$', 'index.php?sales_login=1', 'top');
   add_rewrite_rule('^sales-panel/?$', 'index.php?sales_panel=dashboard', 'top');
   add_rewrite_rule('^sales-panel/customers/?$', 'index.php?sales_panel=customers', 'top');
   // ... other sales routes
   ```

3. Add query vars for sales panel

4. Add role-based redirect in template_redirect
   - Detect if user has sales_agent or sales_manager role
   - Redirect to appropriate panel based on role

### Phase 2: Settings Integration (Priority 2)
**Location:** Settings submenu in b2b-panel

**Settings to add:**
- Sales panel enabled/disabled
- Commission rate (%)
- Sales panel title
- Stale alert days
- Merge duplicate products option

**Implementation:**
- Add new page: `/b2b-panel/settings/sales-agent`
- Add menu item in settings submenu
- Save options with `update_option()`

### Phase 3: Sales Panel Pages (Priority 3)
**Pages to integrate:**
1. Sales Login Page
2. Sales Dashboard (overview, stats)
3. Sales Customers List
4. Sales Customer Detail
5. Sales Orders List
6. Sales Commissions
7. New Order Creation

**Key Features:**
- Dashboard with sales metrics
- Customer assignment tracking
- Order creation for customers
- Commission tracking
- Manager hierarchy (for sales managers)

### Phase 4: Helper Functions & AJAX (Priority 4)
**AJAX Handlers:**
- `sa_search_products` - Product search for order creation
- `sa_get_order_details` - Order details popup
- `sa_get_unpaid_orders` - Unpaid orders alert
- `sa_create_order` - Create order for customer
- `sa_switch_user` - Switch to customer view

**Helper Functions:**
- Customer hierarchy management
- Sales statistics calculation
- Commission calculations

## Integration Strategy

### Option A: Single File Integration (Recommended for MVP)
**Pros:**
- Simpler to implement
- All code in one place
- Easier to debug

**Cons:**
- Large file size (~10,000 lines total)

### Option B: Modular Integration
**Pros:**
- Better code organization
- Easier to maintain long-term

**Cons:**
- More files to manage
- Requires file inclusion logic

**Decision:** Start with Option A, refactor to Option B later if needed

## Code Organization

### Location in adminpanel.php
Insert sales agent code after support ticket module (around line 9220)

### Structure:
```
// ... existing B2B admin code ...

/* =====================================================
   SALES AGENT SYSTEM INTEGRATION (V1.0)
===================================================== */

// 1. Roles & Init
// 2. Settings Page
// 3. Template Redirect & Routing
// 4. Sales Panel Pages
// 5. AJAX Handlers
// 6. Helper Functions

// ... continue with existing code ...
```

## Database Requirements
**User Meta:**
- `bagli_agent_id` - Customer's assigned agent
- `bagli_manager_id` - Agent's assigned manager

**Options:**
- `sales_panel_title` - Panel display title
- `sales_commission_rate` - Commission percentage
- `sales_stale_days` - Days before customer marked stale
- `sales_merge_products` - Merge duplicate products flag
- `sales_agent_flush_v45` - Rewrite rules flush marker

## Security Considerations
1. Capability checks on all sales panel pages
2. AJAX nonce verification
3. User switching validation
4. Order creation permission checks

## Testing Checklist
- [ ] Administrator can access full admin panel
- [ ] Sales agent redirected to sales panel
- [ ] Sales manager redirected to sales panel
- [ ] Settings page accessible from admin panel
- [ ] Sales panel pages load correctly
- [ ] AJAX functions work properly
- [ ] Customer assignment works
- [ ] Order creation works
- [ ] Commission calculations accurate

## Rollout Plan
1. Add roles and routing (Phase 1)
2. Test role-based redirects
3. Add settings page (Phase 2)
4. Test settings save/load
5. Integrate sales panel pages (Phase 3)
6. Test all sales panel features
7. Add AJAX handlers (Phase 4)
8. Full integration testing
9. Deploy to production

## Estimated Implementation Time
- Phase 1: 30 minutes
- Phase 2: 20 minutes
- Phase 3: 60 minutes
- Phase 4: 30 minutes
- Testing: 30 minutes
**Total: ~3 hours**

## Next Steps
1. Begin Phase 1 implementation
2. Commit after each phase
3. Test thoroughly
4. Document any issues
5. Create user guide

---

**Status:** Planning Complete - Ready for Implementation
**Date:** 2026-01-14
**Author:** Copilot AI Agent

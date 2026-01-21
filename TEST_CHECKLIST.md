# Order Edit Page - Testing Checklist

## Date: 2026-01-21
## Status: Ready for Testing

---

## Test Scenarios

### Priority 1 (Critical) Tests

#### 1. Assembly Fee Calculation
- [ ] Test product WITH assembly price ($25)
  - Add to order, enable assembly
  - Verify order shows $25 assembly fee (not $50)
  
- [ ] Test product WITH assembly price ($75)
  - Add to order, enable assembly
  - Verify order shows $75 assembly fee
  
- [ ] Test product WITHOUT assembly price
  - Verify "N/A" shows in assembly column
  - Cannot check assembly checkbox

- [ ] Test multiple products with different assembly prices
  - Product A: $20, qty 2 → $40
  - Product B: $30, qty 3 → $90
  - Total assembly fee should be $130

#### 2. Tax Calculation for Assembly
- [ ] Tax exempt customer + assembly tax enabled
  - Assembly fee should NOT include tax
  
- [ ] Non-exempt customer + assembly tax enabled
  - Assembly fee should include tax (8% or WC rate)
  
- [ ] Non-exempt customer + assembly tax disabled
  - Assembly fee should NOT include tax
  
- [ ] Multiple products with different tax settings
  - Product A: tax enabled, $20 → $21.60 (with 8% tax)
  - Product B: tax disabled, $30 → $30.00 (no tax)

#### 3. Tax Exempt Display
- [ ] Tax exempt customer
  - Green banner shows "TAX EXEMPT"
  
- [ ] Non-exempt customer
  - Yellow banner shows "NOT tax exempt"
  
- [ ] Guest order (no customer)
  - Tax section should not display

#### 4. Assembly Column Header
- [ ] Verify header shows "Assembly" with tooltip "per product pricing"
- [ ] No fixed "$50/item" text

---

### Priority 2 (UX) Tests

#### 5. Toggle All Assembly Button
- [ ] Click "Toggle Assembly for All"
  - All checkboxes should check
  
- [ ] Click again
  - All checkboxes should uncheck
  
- [ ] Manually check some, then click toggle
  - Should check all remaining

#### 6. Recalculate Button
- [ ] Change item price, click Recalculate
  - Totals update
  - Green success message shows
  - Stay on edit page
  
- [ ] Enable assembly, click Recalculate
  - Assembly fee adds to total
  - Success message shows
  
- [ ] Click Save Changes
  - Redirects to orders list
  - "Updated" message shows

#### 7. Assembly Price Display
- [ ] Product with assembly
  - Shows "$XX.XX/item" below checkbox
  
- [ ] Product without assembly
  - Shows "N/A" in gray

#### 8. Success Message
- [ ] After recalculate
  - Green banner: "Order totals recalculated successfully!"

---

## Edge Cases

#### 9. Mixed Products
- [ ] Order with 3 products:
  - Product A: No assembly → N/A
  - Product B: Assembly $15, tax yes → Calculate with tax
  - Product C: Assembly $25, tax no → No tax added

#### 10. Tax Rate Caching
- [ ] Add 5 products with same tax class
  - Should only call WC_Tax::get_rates() once per unique tax class
  - Check performance

#### 11. Quantity Changes
- [ ] Product: Assembly $20, qty 1 → $20
- [ ] Change qty to 5, recalculate → $100
- [ ] Change qty to 0 → Item removed, no assembly fee

---

## Browser Compatibility

- [ ] Chrome/Edge
- [ ] Firefox
- [ ] Safari

---

## Security

- [ ] Nonce verification works
- [ ] All inputs sanitized
- [ ] No SQL injection possible
- [ ] XSS protection working

---

## Performance

- [ ] Page loads in < 2 seconds
- [ ] Recalculate processes in < 1 second
- [ ] No console errors
- [ ] No PHP warnings/errors

---

## Accessibility

- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] Color contrast sufficient
- [ ] Focus indicators visible

---

## Documentation

- [ ] ORDER_EDIT_FIX_SUMMARY.md is accurate
- [ ] PROJECT_ANALYSIS_ORDER_EDIT.md referenced correctly
- [ ] Code comments clear
- [ ] Constants documented

---

## Rollback Plan

If issues found:
1. Revert to commit before changes
2. Document issues
3. Fix and retest
4. Redeploy

---

**Tested By:** _________________  
**Date:** _________________  
**Sign-off:** _________________

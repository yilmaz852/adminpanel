# Personnel Panel Refactoring Guide

## Overview
This document provides a systematic approach to refactoring personnelpanel.php to use the B2B admin panel layout functions (`b2b_adm_header()` and `b2b_adm_footer()`) instead of rendering complete HTML pages.

## Completed (2/21)
✅ `b2b_personnel_list_page()` - Personnel Management list page  
✅ `b2b_personnel_form_page()` - Add/Edit Personnel form page

## Remaining Functions (19/21)

### Pattern for Refactoring

Each function follows this transformation:

#### BEFORE:
```php
function example_function() {
    // PHP logic here
    $data = get_some_data();
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Page Title</title>
        <style>
            /* CSS */
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Page Title</h1>
            <a href="...">Back</a>
        </div>
        <div class="container">
            <!-- Actual content -->
        </div>
    </body>
    </html>
    <?php
}
```

#### AFTER:
```php
function example_function() {
    // PHP logic here
    $data = get_some_data();
    
    b2b_adm_header('Page Title');
    ?>
    
    <div class="page-header">
        <h1 class="page-title">Page Title</h1>
    </div>
    
    <div class="card">
        <!-- Actual content -->
    </div>
    
    <?php
    b2b_adm_footer();
    exit;
}
```

### Key Steps for Each Function:

1. **Identify the function** - Find where it starts and ends
2. **Locate the PHP close tag** `?>` before `<!DOCTYPE html>`
3. **Replace opening section**:
   - Remove: `?>` through `<body>` and header/container divs
   - Add: `b2b_adm_header('Page Title');` with appropriate title
   - Add: Page header and card wrapper divs
4. **Keep content** - Preserve all the actual page content
5. **Replace closing section**:
   - Remove: `</div>` (container), `</body>`, `</html>`, `<?php`
   - Add: Close card div, `b2b_adm_footer();`, `exit;`

### Remaining Functions List:

| # | Function Name | Page Title | Lines |
|---|--------------|------------|-------|
| 1 | `b2b_personnel_departments_page` | Department Management | ~1435-1680 |
| 2 | `b2b_personnel_view_page` | Personnel Details | ~1985-2891 |
| 3 | `b2b_personnel_attendance_page` | Attendance | ~2912-3167 |
| 4 | `b2b_personnel_activity_page` | Activity Log | ~3262-3459 |
| 5 | `b2b_personnel_clock_in_form` | Clock In | ~3673-3827 |
| 6 | `b2b_personnel_clock_out_form` | Clock Out | ~3841-3994 |
| 7 | `b2b_personnel_edit_attendance_form` | Edit Attendance | ~4069-4231 |
| 8 | `b2b_personnel_reports_page` | Reports | ~4309-4638 |
| 9 | `b2b_personnel_upload_photo` | Upload Photo | ~4661-4841 |
| 10 | `b2b_personnel_print_view` | Print View | ~5054-5318 |
| 11 | `b2b_personnel_enhanced_audit` | Enhanced Audit | ~5359-5555 |
| 12 | `b2b_personnel_metrics` | Personnel Metrics | ~5625-5823 |
| 13 | `b2b_personnel_request_leave` | Request Leave | ~5845-5951 |
| 14 | `b2b_personnel_leave_approvals` | Leave Approvals | ~6055-6161 |
| 15 | `b2b_personnel_leave_calendar` | Leave Calendar | ~6334-6448 |
| 16 | `b2b_personnel_payroll_payments` | Payroll Payments | ~6561-6706 |
| 17 | `b2b_personnel_add_payment` | Add Payment | ~6801-6988 |
| 18 | `b2b_personnel_payment_history` | Payment History | ~7088-7195 |
| 19 | `b2b_personnel_bulk_salary_accrual` | Bulk Salary Accrual | ~7300-7509 |
| 20 | `b2b_personnel_edit_payment` | Edit Payment | ~7677-7772 |

### Notes:

- **Custom Styles**: If a function has unique CSS in its `<style>` tag, keep those styles but move them inline or to a separate `<style>` block after the header call
- **JavaScript**: Keep all `<script>` blocks in the content area
- **Forms**: Preserve all form structure and functionality
- **Alerts**: Keep success/error message displays
- **Card Wrapping**: Most content should be wrapped in `.card` divs for consistent styling

### Testing:

After refactoring each function, test:
1. Page loads without errors
2. Navigation works (back buttons, links)
3. Forms submit correctly
4. Data displays properly
5. Styles look correct

### Backup:

A backup file has been created: `personnelpanel.php.backup`

To restore if needed:
```bash
cp personnelpanel.php.backup personnelpanel.php
```

## Automation Strategy

Due to the varied structure of each function (different HTML layouts, styling, and content organization), manual refactoring or semi-automated approach is recommended for reliability.

### Recommended Approach:

1. Work in batches of 3-5 functions at a time
2. Test after each batch
3. Commit progress regularly
4. Use the view and edit tools systematically

### Completion Estimate:

- Per function: ~10-15 minutes (review, edit, test)
- Total remaining: 19 functions × 12 minutes ≈ 4 hours
- With automation/tooling: 2-3 hours

## Progress Tracking:

Update the progress report after each batch is completed and tested.

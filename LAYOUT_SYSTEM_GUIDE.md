# Nyalife HMS - Layout System Guide

## Overview

This guide explains the centralized layout system implemented to ensure consistent UI across all views in the Nyalife HMS application.

## Problem Solved

Previously, views had inconsistent:
- Hardcoded Bootstrap padding classes (`py-5 px-5`)
- Inline styles in view files
- Different container structures
- Inconsistent mobile responsiveness
- Sidebar/main content alignment issues

## Solution: Layout System CSS

A new `layout-system.css` file provides:

### 1. **Centralized Container Management**
- Overrides problematic Bootstrap padding classes
- Standardizes container padding across all views
- Responsive padding adjustments for mobile/tablet/desktop

### 2. **Sidebar Integration**
- Proper main content margin adjustment for sidebar
- Mobile sidebar overlay system
- Smooth transitions between sidebar states

### 3. **View-Specific Standardization**
- Dashboard views use `.page-wrapper` class
- Appointments/Consultations have consistent padding
- All views follow the same responsive breakpoints

## Usage Guidelines

### For New Views

1. **Use Standard Container Classes:**
   ```php
   <div class="container-fluid page-wrapper">
       <!-- Your content -->
   </div>
   ```

2. **Avoid Hardcoded Padding:**
   ❌ Don't use: `<div class="container-fluid py-5 px-5">`
   ✅ Use instead: `<div class="container-fluid page-wrapper">`

3. **Use Utility Classes:**
   - `.page-wrapper` - Standard page container
   - `.card-container` - Card wrapper with consistent spacing
   - `.section-spacing` - Section spacing utility

### For Existing Views

1. **Replace Hardcoded Padding:**
   - Find: `container-fluid py-5 px-5`
   - Replace: `container-fluid page-wrapper`

2. **Remove Inline Styles:**
   - Move inline styles to appropriate CSS files
   - Use CSS classes instead

3. **Use Page-Specific Classes:**
   - Add page class to main container: `appointments-page`, `consultations-page`, etc.
   - This enables view-specific styling if needed

## Responsive Breakpoints

The layout system uses standard Bootstrap breakpoints:

- **Mobile:** ≤576px - Minimal padding, stacked layouts
- **Tablet:** 577px-768px - Medium padding
- **Small Desktop:** 769px-991px - Sidebar adjustments
- **Desktop:** ≥992px - Full layout with sidebar

## Files Modified

1. **Created:**
   - `assets/css/layout-system.css` - Central layout system

2. **Updated:**
   - `includes/views/layouts/default.php` - Added layout-system.css
   - `includes/views/dashboard/doctor.php` - Replaced hardcoded padding
   - `includes/views/dashboard/patient.php` - Replaced hardcoded padding
   - `includes/views/dashboard/pharmacist.php` - Replaced hardcoded padding
   - `includes/views/dashboard/lab_technician.php` - Replaced hardcoded padding
   - `includes/views/dashboard/nurse.php` - Replaced hardcoded padding

## CSS Loading Order

The layout system CSS is loaded in this order (important for specificity):

1. Bootstrap CSS
2. **layout-system.css** ← Loads here (overrides Bootstrap)
3. nyalife-theme.css
4. Other custom CSS files

## Maintenance

### Adding New Views

When creating new views:
1. Use `.page-wrapper` class for main container
2. Add page-specific class if needed (e.g., `my-new-page`)
3. Follow responsive patterns from existing views
4. Test on mobile (400px width) and desktop

### Modifying Layout

To modify the layout system:
1. Edit `assets/css/layout-system.css`
2. Test across all view types
3. Ensure mobile responsiveness is maintained
4. Run `npm run build` to compile changes

## Benefits

✅ **Consistency:** All views use the same layout system
✅ **Maintainability:** Single source of truth for layout rules
✅ **Responsive:** Automatic mobile optimization
✅ **Flexibility:** Easy to adjust spacing globally
✅ **Performance:** Overrides prevent layout shifts

## Testing Checklist

- [ ] Mobile view (400px width) - Content visible, no overflow
- [ ] Tablet view (768px width) - Proper spacing
- [ ] Desktop view (≥992px) - Sidebar alignment correct
- [ ] All dashboard views - Consistent padding
- [ ] Appointments/Consultations - Proper layout
- [ ] Sidebar toggle - Smooth transitions
- [ ] No horizontal scroll on mobile


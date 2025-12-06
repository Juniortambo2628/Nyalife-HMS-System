# Card Header Buttons Mobile Optimization

## Overview

Optimized buttons in card headers to better fit on mobile devices. Buttons like "View All" in card headers were too large and not properly positioned on small screens.

## Problem

- Buttons in card headers were too large on mobile
- Buttons were not properly positioned relative to headings
- Card headers with `d-flex justify-content-between` were not wrapping properly
- Text and buttons were competing for space

## Solution

### Mobile (≤576px)

**Button Sizing:**
- Padding: `5px 10px` (down from default `6px 12px` for `.btn-sm`)
- Font size: `11px` (down from `12px`)
- Line height: `1.2` for better fit
- `white-space: nowrap` to prevent button text wrapping

**Card Header Layout:**
- Added `flex-wrap: wrap` to allow wrapping if needed
- Added `gap: 0.5rem` for spacing between elements
- Headings: `flex: 1` and `min-width: 0` to allow text wrapping
- Buttons: `flex-shrink: 0` and `margin-left: auto` to stay on right
- Headings: `word-wrap: break-word` to prevent overflow

**Card Header Padding:**
- Reduced to `1rem 1rem` on mobile (from `1.25rem 2.5rem`)

### Tablet (577px - 768px)

**Button Sizing:**
- Padding: `6px 12px`
- Font size: `11px`

**Card Header Padding:**
- `1rem 1.25rem`

### Small Desktop (769px - 991px)

**Button Sizing:**
- Padding: `6px 14px`
- Font size: `12px`

## CSS Changes

### Mobile Styles (≤576px)

```css
/* Card header buttons - specific mobile styles */
.card-header .btn,
.card-header .btn-sm,
.card-header a.btn {
    padding: 5px 10px;
    font-size: 11px;
    white-space: nowrap;
    min-width: auto;
    line-height: 1.2;
}

/* Ensure card headers with buttons wrap properly */
.card-header.d-flex {
    flex-wrap: wrap;
    gap: 0.5rem;
}

.card-header.d-flex.justify-content-between {
    align-items: center;
}

.card-header.d-flex.justify-content-between h5,
.card-header.d-flex.justify-content-between h6 {
    margin-bottom: 0;
    flex: 1;
    min-width: 0;
    word-wrap: break-word;
}

.card-header.d-flex.justify-content-between .btn {
    margin-left: auto;
    flex-shrink: 0;
}
```

## Files Modified

1. **`assets/css/nyalife-theme.css`**
   - Added mobile-specific styles for card header buttons
   - Enhanced flex layout for card headers
   - Added responsive breakpoints for tablet and small desktop

## Key Features

✅ **Smaller Buttons:** Reduced padding and font size on mobile
✅ **Better Layout:** Flex properties ensure proper positioning
✅ **Text Wrapping:** Headings wrap while buttons stay compact
✅ **Responsive:** Different sizes for mobile, tablet, and desktop
✅ **Touch-Friendly:** Buttons remain easily tappable
✅ **No Overflow:** Headings wrap properly, buttons don't shrink

## Responsive Breakpoints

- **Mobile (≤576px):** 5px 10px padding, 11px font
- **Tablet (577px - 768px):** 6px 12px padding, 11px font
- **Small Desktop (769px - 991px):** 6px 14px padding, 12px font
- **Desktop (≥992px):** Default sizes maintained

## Benefits

✅ **Better Fit:** Buttons fit properly in card headers on mobile
✅ **Improved Layout:** Headings and buttons coexist without overflow
✅ **Consistent Design:** Maintains visual hierarchy
✅ **Better UX:** Easier to tap buttons on mobile
✅ **Responsive:** Adapts to all screen sizes

## Testing

Test on:
- [x] Mobile (400px width) - Buttons fit properly
- [x] Tablet (768px width) - Balanced sizing
- [x] Desktop (≥992px) - Full design maintained


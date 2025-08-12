# HD Tickets CSS Architecture

This document describes the consolidated and optimized CSS architecture for HD Tickets sports events application.

## Overview

The CSS architecture has been completely redesigned with a focus on:
- **Performance**: CSS containment, content-visibility, and critical CSS
- **Maintainability**: Modular structure with clear naming conventions
- **Accessibility**: WCAG compliance and user preference support
- **Mobile-first**: Touch-friendly responsive design
- **Theming**: Consistent design system with CSS custom properties

## File Structure

```
resources/css/
├── app.css                 # Main entry point with consolidated styles
├── critical.css            # Critical above-the-fold CSS for inlining
├── variables/
│   └── hd-variables.css    # All CSS custom properties with hd- prefix
├── components/
│   ├── hd-layout.css       # Layout system with CSS Grid and performance optimizations
│   ├── hd-buttons.css      # Button components with all variants
│   ├── hd-cards.css        # Card components with responsive design
│   └── hd-forms.css        # Form elements with accessibility features
└── utils/
    └── hd-utils.css        # Utility classes following hd- naming convention
```

## Naming Convention

All new classes use the `hd-` prefix to avoid conflicts and maintain consistency:

- **Components**: `.hd-card`, `.hd-btn`, `.hd-form`
- **Modifiers**: `.hd-btn--primary`, `.hd-card--elevated`
- **States**: `.hd-btn--loading`, `.hd-card--interactive`
- **Utilities**: `.hd-container`, `.hd-grid`, `.hd-flex`

## CSS Custom Properties (Variables)

All design tokens are centralized in `variables/hd-variables.css`:

### Color System
- **Primary**: `--hd-primary`, `--hd-primary-50` to `--hd-primary-900`
- **Semantic**: `--hd-success`, `--hd-warning`, `--hd-error`, `--hd-info`
- **Neutral**: `--hd-gray-50` to `--hd-gray-900`
- **Text**: `--hd-text-primary`, `--hd-text-secondary`, `--hd-text-muted`
- **Background**: `--hd-bg-primary`, `--hd-bg-secondary`, `--hd-bg-card`

### Typography
- **Fonts**: `--hd-font-sans`, `--hd-font-serif`, `--hd-font-mono`
- **Sizes**: `--hd-text-xs` to `--hd-text-9xl`
- **Weights**: `--hd-font-thin` to `--hd-font-black`

### Spacing & Layout
- **Spacing**: `--hd-spacing-0` to `--hd-spacing-96`
- **Radius**: `--hd-radius-none` to `--hd-radius-full`
- **Shadows**: `--hd-shadow-xs` to `--hd-shadow-2xl`

## Performance Optimizations

### CSS Containment
All components use appropriate CSS containment:
```css
.hd-card {
  contain: layout style paint;
}
```

### Content Visibility
Large content areas use `content-visibility: auto`:
```css
.hd-section__body {
  content-visibility: auto;
  contain-intrinsic-size: 0 200px;
}
```

### Critical CSS
Essential above-the-fold styles are in `critical.css` for inlining in HTML `<head>`.

### CSS Layers
Organized cascade with `@layer`:
- `variables`: CSS custom properties
- `base`: Reset and base styles
- `components`: Component styles
- `utilities`: Utility classes
- `mobile`: Mobile-specific optimizations

## Component Architecture

### Layout System (`hd-layout.css`)
- CSS Grid-based layout with performance optimizations
- Mobile-first responsive design
- Sidebar with overlay behavior on mobile
- Safe area support for iOS devices

### Button System (`hd-buttons.css`)
- Multiple variants: primary, secondary, outline, ghost, danger
- Size variants: xs, sm, base, lg, xl
- Loading states with spinner animations
- Touch-friendly 44px minimum height
- Button groups and floating action buttons

### Card System (`hd-cards.css`)
- Base cards with elevation variants
- Interactive cards with hover effects
- Media cards with image handling
- Grid layouts with responsive behavior
- Skeleton loading states
- Special HD Tickets variants (ticket cards, stat cards)

### Form System (`hd-forms.css`)
- Accessible form controls
- Validation states with error/success styling
- Mobile-optimized with 16px font size (prevents iOS zoom)
- Custom checkbox/radio styling
- File input styling
- Input groups and addons

## Mobile Optimizations

### Touch-Friendly Design
- Minimum 44px touch targets
- Enhanced button spacing on mobile
- Swipe gestures support
- Pull-to-refresh indicators

### Responsive Breakpoints
- Mobile: `max-width: 640px`
- Tablet: `641px - 1023px`
- Desktop: `min-width: 1024px`
- Large desktop: `min-width: 1280px`

### Mobile-Specific Features
- Table to card transformation
- Mobile navigation patterns
- Safe area insets for iOS
- Touch action optimization

## Accessibility Features

### User Preferences
- `prefers-reduced-motion`: Disables animations
- `prefers-contrast: high`: Enhanced contrast borders
- `prefers-color-scheme: dark`: Automatic dark mode

### Focus Management
- Custom focus rings with proper contrast
- Skip links for keyboard navigation
- Screen reader only content (`.hd-sr-only`)

### Semantic Structure
- Proper heading hierarchy
- ARIA-compliant form labels
- Color-independent status indicators

## Dark Mode Support

Automatic dark mode with CSS custom properties:
```css
@media (prefers-color-scheme: dark) {
  :root {
    --hd-bg-primary: var(--hd-slate-900);
    --hd-text-primary: var(--hd-slate-100);
  }
}
```

Manual toggle with class:
```html
<body class="hd-dark">
```

## Usage Examples

### Basic Layout
```html
<div class="hd-layout">
  <header class="hd-header">
    <div class="hd-header__content">
      <!-- Header content -->
    </div>
  </header>
  <main class="hd-main">
    <div class="hd-main__content">
      <!-- Main content -->
    </div>
  </main>
</div>
```

### Card with Actions
```html
<div class="hd-card hd-card--interactive">
  <div class="hd-card__header">
    <h3 class="hd-card__title">Ticket Title</h3>
    <p class="hd-card__subtitle">Event details</p>
  </div>
  <div class="hd-card__body">
    <p class="hd-card__description">Event description...</p>
  </div>
  <div class="hd-card__footer">
    <div class="hd-card__actions">
      <button class="hd-btn hd-btn--primary">Buy Ticket</button>
      <button class="hd-btn hd-btn--secondary">View Details</button>
    </div>
  </div>
</div>
```

### Form with Validation
```html
<form class="hd-form">
  <div class="hd-form-group">
    <label class="hd-label hd-label--required" for="email">Email</label>
    <input 
      type="email" 
      id="email" 
      class="hd-input" 
      placeholder="Enter your email"
      required
    >
    <div class="hd-form-error" id="email-error" role="alert">
      Please enter a valid email address
    </div>
  </div>
  <button type="submit" class="hd-btn hd-btn--primary hd-btn--full">
    Submit
  </button>
</form>
```

## Migration Guide

### From Old Classes to New
- `.dashboard-card` → Still works (compatibility layer) or use `.hd-card`
- `.btn-primary` → Still works (compatibility layer) or use `.hd-btn hd-btn--primary`
- `.form-input` → Still works (compatibility layer) or use `.hd-input`

### Recommended Migration Steps
1. Update new components to use `hd-` classes
2. Gradually replace old classes in existing templates
3. Remove Tailwind dependency when fully migrated
4. Optimize by removing unused compatibility styles

## Performance Tips

### Critical CSS
Inline `critical.css` in the HTML `<head>` for optimal initial rendering.

### Component Loading
Components can be loaded on-demand using CSS containment and content-visibility.

### Media Queries Optimization
Use consolidated breakpoints to reduce CSS complexity:
- Mobile-first approach
- Consolidated breakpoints (3 main: mobile, tablet, desktop)
- Container queries where supported

## Browser Support

- **Modern browsers**: Full support with all optimizations
- **Legacy browsers**: Graceful degradation with CSS custom properties fallbacks
- **Mobile browsers**: Enhanced with touch optimizations and safe area support

## Maintenance Guidelines

### Adding New Components
1. Create component file in `components/` directory
2. Use `hd-` prefix for all classes
3. Include responsive design and accessibility features
4. Add dark mode support
5. Include performance optimizations (containment, content-visibility)
6. Update this documentation

### Modifying Variables
1. Update `variables/hd-variables.css`
2. Test across all components
3. Update critical.css if needed
4. Test dark mode and high contrast

### Performance Testing
1. Measure CSS bundle size
2. Test critical rendering path
3. Validate mobile performance
4. Check accessibility compliance

For questions or contributions, please refer to the project documentation or create an issue.

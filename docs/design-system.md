# HD Tickets Design System

## Overview

The HD Tickets Design System provides a comprehensive set of design tokens, components, and utilities for building consistent user interfaces across the sports events ticket platform.

## Design Principles

### 1. **Sports-Focused**
- Designed specifically for sports events ticketing
- Sports-specific colors, icons, and components
- Event status indicators and ticket availability states

### 2. **Accessibility First**
- WCAG 2.1 AA compliant
- High contrast mode support
- Keyboard navigation friendly
- Screen reader optimized

### 3. **Performance-Oriented**
- CSS containment for optimal rendering
- Lazy loading support with content-visibility
- Minimal animation for better performance
- Mobile-first responsive design

### 4. **Consistent & Scalable**
- Design tokens for consistent styling
- Modular component architecture
- Dark mode support throughout
- Print-friendly styles

## Design Tokens

### Colors

#### Brand Colors
```css
--hd-primary: #2563eb     /* Main brand blue */
--hd-secondary: #7c3aed   /* Secondary purple */
--hd-accent: #06b6d4      /* Accent cyan */
```

#### Sports Event Colors
```css
--hd-sport-football: #1f77b4
--hd-sport-rugby: #ff7f0e
--hd-sport-cricket: #2ca02c
--hd-sport-tennis: #d62728
```

#### Status Colors
```css
--hd-success: #10b981     /* Available tickets */
--hd-warning: #f59e0b     /* Limited tickets */
--hd-error: #ef4444       /* Sold out */
--hd-info: #3b82f6        /* Information */
```

#### Ticket Status Colors
```css
--hd-ticket-available: var(--hd-success)
--hd-ticket-limited: var(--hd-warning)
--hd-ticket-sold-out: var(--hd-error)
--hd-ticket-on-hold: var(--hd-gray-500)
```

### Typography

#### Font Family
- Primary: Inter (with system fallbacks)
- Monospace: System monospace fonts

#### Font Sizes
```css
--hd-text-xs: 0.75rem    /* 12px */
--hd-text-sm: 0.875rem   /* 14px */
--hd-text-base: 1rem     /* 16px */
--hd-text-lg: 1.125rem   /* 18px */
--hd-text-xl: 1.25rem    /* 20px */
--hd-text-2xl: 1.5rem    /* 24px */
--hd-text-3xl: 1.875rem  /* 30px */
--hd-text-4xl: 2.25rem   /* 36px */
```

#### Font Weights
```css
--hd-font-light: 300
--hd-font-normal: 400
--hd-font-medium: 500
--hd-font-semibold: 600
--hd-font-bold: 700
```

### Spacing

Based on 4px grid system:
```css
--hd-spacing-1: 0.25rem   /* 4px */
--hd-spacing-2: 0.5rem    /* 8px */
--hd-spacing-3: 0.75rem   /* 12px */
--hd-spacing-4: 1rem      /* 16px */
--hd-spacing-5: 1.25rem   /* 20px */
--hd-spacing-6: 1.5rem    /* 24px */
--hd-spacing-8: 2rem      /* 32px */
--hd-spacing-10: 2.5rem   /* 40px */
--hd-spacing-12: 3rem     /* 48px */
```

### Border Radius
```css
--hd-radius-sm: 0.125rem  /* 2px */
--hd-radius: 0.25rem      /* 4px */
--hd-radius-md: 0.375rem  /* 6px */
--hd-radius-lg: 0.5rem    /* 8px */
--hd-radius-xl: 0.75rem   /* 12px */
--hd-radius-2xl: 1rem     /* 16px */
--hd-radius-full: 9999px  /* Full circle */
```

## Components

### Buttons

#### Basic Usage
```html
<button class="hd-btn hd-btn--primary">Primary Button</button>
<button class="hd-btn hd-btn--secondary">Secondary Button</button>
<button class="hd-btn hd-btn--outline">Outline Button</button>
<button class="hd-btn hd-btn--ghost">Ghost Button</button>
```

#### Sizes
```html
<button class="hd-btn hd-btn--primary hd-btn--xs">Extra Small</button>
<button class="hd-btn hd-btn--primary hd-btn--sm">Small</button>
<button class="hd-btn hd-btn--primary">Default</button>
<button class="hd-btn hd-btn--primary hd-btn--lg">Large</button>
<button class="hd-btn hd-btn--primary hd-btn--xl">Extra Large</button>
```

#### Status Variants
```html
<button class="hd-btn hd-btn--success">Success</button>
<button class="hd-btn hd-btn--warning">Warning</button>
<button class="hd-btn hd-btn--danger">Danger</button>
```

#### States
```html
<button class="hd-btn hd-btn--primary hd-btn--loading">
    <span class="hd-btn__text">Loading...</span>
</button>
<button class="hd-btn hd-btn--primary" disabled>Disabled</button>
```

### Cards

#### Basic Card
```html
<div class="hd-card">
    <div class="hd-card__header">
        <h3 class="hd-card__title">Card Title</h3>
        <p class="hd-card__subtitle">Card subtitle</p>
    </div>
    <div class="hd-card__body">
        <p class="hd-card__description">Card content goes here</p>
    </div>
    <div class="hd-card__footer">
        <div class="hd-card__actions">
            <button class="hd-btn hd-btn--primary">Action</button>
        </div>
    </div>
</div>
```

#### Card Variants
```html
<div class="hd-card hd-card--elevated">Elevated Card</div>
<div class="hd-card hd-card--interactive">Interactive Card</div>
<div class="hd-card hd-card--outline">Outline Card</div>
<div class="hd-card hd-ticket-card">Sports Ticket Card</div>
<div class="hd-card hd-stat-card">Statistics Card</div>
```

#### Card Grids
```html
<div class="hd-card-grid">
    <div class="hd-card">Card 1</div>
    <div class="hd-card">Card 2</div>
    <div class="hd-card">Card 3</div>
</div>
```

### Forms

#### Basic Form Elements
```html
<div class="hd-form">
    <div class="hd-form-group">
        <label class="hd-label hd-label--required">Event Name</label>
        <input type="text" class="hd-input" placeholder="Enter event name">
        <div class="hd-form-help">Choose a descriptive name</div>
    </div>
    
    <div class="hd-form-group">
        <label class="hd-label">Sport Category</label>
        <select class="hd-input hd-select">
            <option>Select category</option>
            <option>Football</option>
            <option>Rugby</option>
            <option>Cricket</option>
            <option>Tennis</option>
        </select>
    </div>
</div>
```

#### Form States
```html
<input type="text" class="hd-input hd-input--success">
<div class="hd-form-success">
    <i class="fas fa-check-circle"></i>
    Input is valid
</div>

<input type="text" class="hd-input hd-input--error">
<div class="hd-form-error">
    <i class="fas fa-exclamation-circle"></i>
    This field is required
</div>
```

#### Checkboxes and Radios
```html
<div class="hd-form-control">
    <input type="checkbox" class="hd-checkbox" id="premium">
    <label class="hd-label" for="premium">Premium Event</label>
</div>

<div class="hd-form-control">
    <input type="radio" class="hd-radio" id="public" name="visibility">
    <label class="hd-label" for="public">Public Event</label>
</div>
```

### Navigation

#### Horizontal Navigation
```html
<nav class="hd-nav">
    <div class="hd-nav__item">
        <a href="#" class="hd-nav__link hd-nav__link--active">
            <i class="hd-nav__icon fas fa-home"></i>
            Dashboard
        </a>
    </div>
    <div class="hd-nav__item">
        <a href="#" class="hd-nav__link">
            <i class="hd-nav__icon fas fa-ticket-alt"></i>
            Events
        </a>
    </div>
</nav>
```

#### Vertical Navigation
```html
<nav class="hd-nav hd-nav--vertical">
    <div class="hd-nav__item">
        <a href="#" class="hd-nav__link">
            <i class="hd-nav__icon fas fa-chart-bar"></i>
            Analytics
        </a>
    </div>
</nav>
```

### Badges

#### Basic Badges
```html
<span class="hd-badge">Default</span>
<span class="hd-badge hd-badge--primary">Primary</span>
<span class="hd-badge hd-badge--success">Success</span>
<span class="hd-badge hd-badge--warning">Warning</span>
<span class="hd-badge hd-badge--error">Error</span>
```

#### Sports Badges
```html
<span class="hd-badge hd-badge--sport-football">Football</span>
<span class="hd-badge hd-badge--sport-rugby">Rugby</span>
<span class="hd-badge hd-badge--sport-cricket">Cricket</span>
<span class="hd-badge hd-badge--sport-tennis">Tennis</span>
```

#### Ticket Status Badges
```html
<span class="hd-badge hd-badge--ticket-available">Available</span>
<span class="hd-badge hd-badge--ticket-limited">Limited</span>
<span class="hd-badge hd-badge--ticket-sold-out">Sold Out</span>
<span class="hd-badge hd-badge--ticket-on-hold">On Hold</span>
```

### Alerts

#### Basic Alerts
```html
<div class="hd-alert hd-alert--info">
    <div class="hd-alert__icon">
        <i class="fas fa-info-circle"></i>
    </div>
    <div class="hd-alert__content">
        <div class="hd-alert__title">Information</div>
        <div class="hd-alert__description">This is an informational message.</div>
    </div>
    <button class="hd-alert__close">
        <i class="fas fa-times"></i>
    </button>
</div>
```

#### Alert Types
```html
<div class="hd-alert hd-alert--success">Success Alert</div>
<div class="hd-alert hd-alert--warning">Warning Alert</div>
<div class="hd-alert hd-alert--error">Error Alert</div>
```

### Tables

#### Basic Table
```html
<table class="hd-table">
    <thead class="hd-table__head">
        <tr class="hd-table__row">
            <th class="hd-table__header">Event</th>
            <th class="hd-table__header">Date</th>
            <th class="hd-table__header hd-table__cell--numeric">Price</th>
        </tr>
    </thead>
    <tbody class="hd-table__body">
        <tr class="hd-table__row">
            <td class="hd-table__cell">Premier League Final</td>
            <td class="hd-table__cell">2024-05-25</td>
            <td class="hd-table__cell hd-table__cell--numeric">£150.00</td>
        </tr>
    </tbody>
</table>
```

### Modals

#### Basic Modal
```html
<div class="hd-modal">
    <div class="hd-modal__backdrop"></div>
    <div class="hd-modal__container">
        <div class="hd-modal__header">
            <h2 class="hd-modal__title">Modal Title</h2>
            <button class="hd-modal__close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="hd-modal__body">
            Modal content goes here.
        </div>
        <div class="hd-modal__footer">
            <button class="hd-btn hd-btn--secondary">Cancel</button>
            <button class="hd-btn hd-btn--primary">Confirm</button>
        </div>
    </div>
</div>
```

## Sports Event Specific Components

### Event Status
```html
<div class="hd-event-status hd-event-status--live">
    <div class="hd-event-status__dot"></div>
    Live
</div>

<div class="hd-event-status hd-event-status--upcoming">
    <div class="hd-event-status__dot"></div>
    Upcoming
</div>

<div class="hd-event-status hd-event-status--completed">
    <div class="hd-event-status__dot"></div>
    Completed
</div>
```

### Price Display
```html
<div class="hd-price">
    <span class="hd-price__currency">£</span>
    <span class="hd-price__amount">150.00</span>
    <span class="hd-price__original">£200.00</span>
    <span class="hd-price__discount">25% off</span>
</div>
```

### Venue Information
```html
<div class="hd-venue">
    <div class="hd-venue__icon">
        <i class="fas fa-map-marker-alt"></i>
    </div>
    <div class="hd-venue__content">
        <div class="hd-venue__name">Wembley Stadium</div>
        <div class="hd-venue__address">London, England</div>
    </div>
</div>
```

### Seat Information
```html
<div class="hd-seat-info">
    <div class="hd-seat-info__icon">
        <i class="fas fa-chair"></i>
    </div>
    <div class="hd-seat-info__details">
        Section A, Row 5, Seats 12-13
    </div>
</div>
```

## Layout System

### Grid System
```html
<div class="hd-grid hd-grid--2">Two columns</div>
<div class="hd-grid hd-grid--3">Three columns</div>
<div class="hd-grid hd-grid--4">Four columns</div>
<div class="hd-grid hd-grid--auto-fill">Auto-fill columns</div>
```

### Flexbox Layouts
```html
<div class="hd-flex hd-flex--between">Space between</div>
<div class="hd-flex hd-flex--center">Centered</div>
<div class="hd-flex hd-flex--column">Column layout</div>
<div class="hd-flex hd-flex--wrap">Wrapping flex</div>
```

### Container
```html
<div class="hd-container">
    Responsive container with max-width
</div>
```

### Sections
```html
<div class="hd-section">
    <div class="hd-section__header">
        <h2 class="hd-section__title">Section Title</h2>
        <p class="hd-section__subtitle">Section subtitle</p>
    </div>
    <div class="hd-section__body">
        Section content
    </div>
    <div class="hd-section__footer">
        Section footer
    </div>
</div>
```

## Utility Classes

### Text Utilities
```css
.hd-text-left, .hd-text-center, .hd-text-right
.hd-text-primary, .hd-text-secondary, .hd-text-muted
.hd-font-light, .hd-font-normal, .hd-font-medium, .hd-font-semibold, .hd-font-bold
.hd-text-xs, .hd-text-sm, .hd-text-base, .hd-text-lg, .hd-text-xl
```

### Spacing Utilities
```css
.hd-m-0, .hd-m-1, .hd-m-2, .hd-m-3, .hd-m-4, .hd-m-5, .hd-m-6
.hd-p-0, .hd-p-1, .hd-p-2, .hd-p-3, .hd-p-4, .hd-p-5, .hd-p-6
.hd-mx-auto, .hd-my-4
```

### Visibility Utilities
```css
.hd-hidden                /* Hide completely */
.hd-sr-only              /* Screen reader only */
.hd-hidden-sm-down       /* Hide on small screens and below */
.hd-hidden-md-up         /* Hide on medium screens and up */
.hd-print-hidden         /* Hide when printing */
```

### Performance Utilities
```css
.hd-contain-strict       /* CSS containment: strict */
.hd-contain-layout       /* CSS containment: layout */
.hd-contain-paint        /* CSS containment: paint */
.hd-content-visibility-auto  /* Content visibility optimization */
.hd-gpu-accelerate       /* Force GPU acceleration */
```

## Dark Mode Support

The design system includes comprehensive dark mode support. All components automatically adapt to dark mode using either:

1. System preference: `@media (prefers-color-scheme: dark)`
2. Manual toggle: `.hd-dark` class on root element

### Usage
```html
<!-- Auto dark mode based on system preference -->
<html>...</html>

<!-- Manual dark mode -->
<html class="hd-dark">...</html>
```

## Responsive Design

### Breakpoints
```css
--hd-screen-xs: 375px
--hd-screen-sm: 640px
--hd-screen-md: 768px
--hd-screen-lg: 1024px
--hd-screen-xl: 1280px
--hd-screen-2xl: 1536px
```

### Mobile-First Approach
All components are designed mobile-first with progressive enhancement for larger screens.

## Accessibility Features

### Focus Management
- Visible focus indicators on all interactive elements
- Keyboard navigation support
- Proper tab order

### Screen Reader Support
- Semantic HTML structure
- ARIA labels and roles where appropriate
- Hidden content for screen readers (`.hd-sr-only`)

### High Contrast Mode
- Automatic adaptation to high contrast preferences
- Enhanced border visibility
- Increased shadow contrast

### Reduced Motion
- Respects `prefers-reduced-motion` preference
- Disables animations for users who prefer reduced motion

## Performance Optimizations

### CSS Containment
- Layout containment for better rendering performance
- Paint containment for independent repainting
- Content visibility for off-screen elements

### Lazy Loading Support
- Content visibility for performance
- Intrinsic sizing for skeleton loading

### Mobile Optimizations
- Touch-friendly interaction targets (44px minimum)
- Optimized animations for mobile devices
- Reduced complexity on smaller screens

## Browser Support

### Modern Browsers
- Chrome 88+
- Firefox 85+
- Safari 14+
- Edge 88+

### CSS Features Used
- CSS Custom Properties (CSS Variables)
- CSS Grid and Flexbox
- CSS Containment
- Content Visibility (with fallbacks)

## Implementation Guide

### Getting Started

1. Import the design system CSS:
```css
@import './design-system.css';
```

2. Use design tokens in your custom CSS:
```css
.my-component {
    background: var(--hd-primary);
    padding: var(--hd-spacing-4);
    border-radius: var(--hd-radius-lg);
}
```

3. Apply component classes in your HTML:
```html
<button class="hd-btn hd-btn--primary">Get Started</button>
```

### Best Practices

1. **Use Design Tokens**: Always use CSS custom properties instead of hardcoded values
2. **Follow Naming Conventions**: Use BEM methodology for custom components
3. **Mobile-First**: Design for mobile devices first, then enhance for desktop
4. **Accessibility**: Test with keyboard navigation and screen readers
5. **Performance**: Use CSS containment for better performance
6. **Dark Mode**: Test all components in both light and dark themes

### Customization

#### Extending the System
```css
/* Custom component extending the system */
.my-ticket-card {
    @extend .hd-card;
    @extend .hd-ticket-card;
    /* Custom styles */
    background: linear-gradient(var(--hd-primary), var(--hd-secondary));
}
```

#### Custom Themes
```css
/* Sport-specific theme */
.football-theme {
    --hd-primary: var(--hd-sport-football);
    --hd-accent: #1a5490;
}
```

## Testing

### Accessibility Testing
- Use axe-core for automated accessibility testing
- Test with screen readers (NVDA, VoiceOver, JAWS)
- Verify keyboard navigation
- Check color contrast ratios

### Cross-Browser Testing
- Test in all supported browsers
- Verify CSS feature support
- Test responsive behavior
- Validate print styles

### Performance Testing
- Measure rendering performance
- Check CSS containment effectiveness
- Verify lazy loading behavior
- Monitor bundle size impact

## Style Guide

Access the interactive style guide at `/style-guide` to see all components with live examples and code snippets.

The style guide includes:
- Color palettes with copy-to-clipboard functionality
- Typography specimens
- Interactive component examples
- Code snippets for all components
- Responsive behavior demonstrations
- Dark mode toggle for testing

## Maintenance

### Versioning
The design system follows semantic versioning:
- Major: Breaking changes to component APIs
- Minor: New components or features
- Patch: Bug fixes and small improvements

### Updates
- Components are backwards compatible within major versions
- Design tokens can be updated without breaking changes
- New components follow established patterns

### Support
For questions or issues with the design system:
1. Check the documentation
2. Review the style guide
3. Create an issue in the project repository
4. Contact the design system team
